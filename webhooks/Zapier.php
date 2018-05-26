<?php

namespace dokuwiki\plugin\swarmzapierstructwebhook\webhooks;

use dokuwiki\plugin\struct\meta\Schema;

class Zapier extends AbstractWebhook
{
    public function run()
    {
        global $conf, $INPUT;

        if ($conf['allowdebug']) {
            dbglog($_SERVER);
        }

        /** @var null|\helper_plugin_swarmzapierstructwebhook $helper */
        $helper = plugin_load('helper', 'swarmzapierstructwebhook');
        if (!$helper) {
            http_status(422, 'swarmzapierstructwebhook plugin not active at this server');
            return;
        }
        $storedSecret = $helper->getConf('hook secret');
        if (!empty($storedSecret)) {
            $requestSecret = $INPUT->server->str('X_HOOK_SECRET');
            if (empty($requestSecret)) {
                http_status(401, 'Header X_HOOK_SECRET missing!');
                return;
            }

            if ($requestSecret !== $storedSecret) {
                http_status(403, 'Header X_HOOK_SECRET not identical with configured secret!');
                return;
            }
        }

        $body = file_get_contents('php://input');

        $ok = $this->handleWebhookPayload($body);

        if ($ok) {
            http_status(202);
        }
    }

    /**
     * Stores the webhook's payload to the struct table
     *
     * FIXME: don't set http status here
     *
     * @param string $json the original webhooks payload as json
     *
     * @return bool false if there was an error, http status has already been set, true if everything was ok
     */
    protected function handleWebhookPayload($json)
    {
        /** @var null|\helper_plugin_struct $struct */
        $struct = plugin_load('helper', 'struct');
        if (!$struct) {
            http_status(422, 'struct plugin not active at this server');
            return false;
        }

        /** @var \helper_plugin_swarmzapierstructwebhook $helper */
        $helper = plugin_load('helper', 'swarmzapierstructwebhook');

        $lookupData = $this->extractDataFromPayload(json_decode($json, true));
        $lookupData['json'] = $json;

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
            http_status(500, $errorMessage);
            return false;
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
        $checkinID = $data['id'];
        $locationName = $data['venue']['name'];

        /** @var \helper_plugin_swarmzapierstructwebhook $helper */
        $helper = plugin_load('helper', 'swarmzapierstructwebhook');
        $dateTime = $helper->getDateTimeInstance($data['createdAt'], $data['timeZoneOffset']);

        $lookupData = [
            'date' => $dateTime->format('Y-m-d'),
            'time' => $dateTime->format(\DateTime::ATOM),
            'checkinid' => $checkinID,
            'locname' => $locationName,
            'service' => 'Zapier',
        ];
        if (!empty($data['shout'])) {
            $lookupData['shout'] = $data['shout'];
        }
        return $lookupData;
    }
}
