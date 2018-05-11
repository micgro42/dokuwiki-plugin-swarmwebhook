<?php

namespace dokuwiki\plugin\swarmzapierstructwebhook;

class Webhook
{
    public function run()
    {
        /** @var \helper_plugin_struct $struct */
        $struct = plugin_load('helper', 'struct');
        if (!$struct) {
            http_status(422, 'struct plugin not active at server');
            return;
        }

        global $conf, $INPUT;

        if ($conf['debug']) {
            dbglog($_SERVER);
        }

        /** @var \helper_plugin_swarmzapierstructwebhook $helper */
        $helper = plugin_load('helper', 'swarmzapierstructwebhook');
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
        $data = json_decode($body, true);

        $timestamp = $data['createdAt'];
        $checkinID = $data['id'];
        $date = date('Y-m-d', $timestamp); // FIXME: use timezone offset?
        $locationName = $data['venue']['name'];

        $lookupData = [
            'date' => $date,
            'time' => date_iso8601($timestamp),
            'checkinid' => $checkinID,
            'locname' => $locationName,
            'json' => $body,
        ];
        if (!empty($data['shout'])) {
            $lookupData['shout'] = $data['shout'];
        }

        try {
            $helper->deleteCheckinFromLookup($checkinID);
            $helper->saveDataToLookup($lookupData);
        } catch (\Exception $e) {
            dbglog($e->getMessage());
        }

        http_status(202);
    }
}
