<?php

namespace dokuwiki\plugin\swarmwebhook\webhooks;

use DateTime;
use dokuwiki\plugin\struct\meta\Schema;
use dokuwiki\plugin\swarmwebhook\meta\Response;

class IFTTT extends AbstractWebhook
{
    const IFTTT_TIME_FORMAT = 'F d, Y \a\t h:iA';

    public function run($json)
    {
        global $conf, $INPUT;

        if ($conf['allowdebug']) {
            dbglog($_SERVER);
        }

        // check that we have helper
        $webhookData = json_decode($json, true);

        $verificationResult = $this->verifyRequest($webhookData);
        if ($verificationResult !== true) {
            http_status($verificationResult->code, $verificationResult->content);
            return;
        }

        $ok = $this->handleWebhookPayload($webhookData, $json);

        if ($ok !== true) {
            http_status($verificationResult->code, $verificationResult->content);
            return;
        }

        http_status(202);
    }

    /**
     * @param array $webhookData
     *
     * @return true|Response
     *
     */
    protected function verifyRequest(array $webhookData)
    {
        /** @var null|\helper_plugin_swarmwebhook $helper */
        $helper = plugin_load('helper', 'swarmwebhook');
        $storedSecret = $helper->getConf('hook secret');
        if (empty($storedSecret)) {
            return true;
        }

        if (empty($webhookData['secret'])) {
            return new Response(401, 'Header X_HOOK_SECRET missing!');
        }

        if ($webhookData['secret'] !== $storedSecret) {
            return new Response(403, 'Header X_HOOK_SECRET not identical with configured secret!');
        }

        return true;
    }

    /**
     * @param array $webhookData
     * @param string $json
     *
     * @return true|Response
     */
    protected function handleWebhookPayload(array $webhookData, $json)
    {
        $lookupData = $this->extractDataFromPayload($webhookData);
        $lookupData['json'] = $json;
        $lookupData['service'] = 'IFTTT';


        /** @var \helper_plugin_swarmwebhook $helper */
        $helper = plugin_load('helper', 'swarmwebhook');
        try {
            $schemas = Schema::getAll('lookup');
            if (!in_array('swarm', $schemas)) {
                $helper->createNewSwarmSchema();
            }

            $helper->deleteCheckinFromLookup($lookupData['checkinid']);
            $helper->saveDataToLookup($lookupData);
        } catch (\Exception $e) { // FIXME: catch more specific exceptions!
            $errorMessage = $e->getMessage();
            dbglog($errorMessage);
            return new Response(500, $errorMessage);
        }

        return true;
    }

    /**
     * Extract the data to be saved from the payload
     *
     * @param array $data
     *
     * @return array
     */
    protected function extractDataFromPayload(array $data)
    {
        $checkinID = $data['ts'];
        $locationName = $data['VenueName'];

        // gues time zone
        $nowTS = time();

        $dateTime = $this->parseTimeIntoTimestamp($data['ts'], $nowTS);

        $lookupData = [
            'date' => $dateTime->format('Y-m-d'),
            'time' => $dateTime->format(\DateTime::ATOM),
            'checkinid' => $checkinID,
            'locname' => $locationName,
        ];
        if (!empty($data['shout'])) {
            $lookupData['shout'] = $data['shout'];
        }
        return $lookupData;
    }

    /**
     * @param $timestring
     * @param $nowTS
     *
     * @return \DateTime
     */
    protected function parseTimeIntoTimestamp($timestring, $nowTS)
    {
        //May 25, 2018 at 04:32PM

        $guessedTZOffset = $this->guessTZOffset($timestring, $nowTS);
        $timeZone = new \DateTimeZone($guessedTZOffset);
        $dateTime = DateTime::createFromFormat(self::IFTTT_TIME_FORMAT, $timestring, $timeZone);

        return $dateTime;
    }

    protected function guessTZOffset($timestring, $nowTS)
    {
        $dateTime = DateTime::createFromFormat(self::IFTTT_TIME_FORMAT, $timestring, new \DateTimeZone('+0000'));
        if ($dateTime === false) {
            dbglog(DateTime::getLastErrors());
            $dateTime = new DateTime('now');
        }
        $guessedOffset = round(($dateTime->getTimestamp() - $nowTS)/3600)*100;
        $sign = $guessedOffset > 0 ? '+' : '';

        return $sign . (string)$guessedOffset;
    }
}
