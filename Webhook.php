<?php

namespace dokuwiki\plugin\swarmzapierstructwebhook;

use dokuwiki\plugin\struct\meta\Schema;

class Webhook
{
    public function run()
    {
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
        /** @var \helper_plugin_struct $struct */
        $struct = plugin_load('helper', 'struct');
        if (!$struct) {
            http_status(422, 'struct plugin not active at this server');
            return false;
        }

        /** @var \helper_plugin_swarmzapierstructwebhook $helper */
        $helper = plugin_load('helper', 'swarmzapierstructwebhook');

        $lookupData = $helper->extractDataFromPayload(json_decode($json, true));
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
}
