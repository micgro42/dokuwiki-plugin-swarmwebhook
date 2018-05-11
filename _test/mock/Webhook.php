<?php

namespace dokuwiki\plugin\swarmzapierstructwebhook\test\mock;

class Webhook extends \dokuwiki\plugin\swarmzapierstructwebhook\Webhook
{
    public function handleWebhookPayload($json)
    {
        return parent::handleWebhookPayload($json);
    }
}