<?php

namespace dokuwiki\plugin\swarmzapierstructwebhook\webhooks;

abstract class AbstractWebhook
{
    abstract public function run();

    /**
     * @return AbstractWebhook
     */
    public static function getWebhook()
    {
        global $INPUT;

        if ($INPUT->server->str('HTTP_USER_AGENT') === 'Zapier') {
            return new Zapier();
        }

        throw new \RuntimeException('Unknown webhook');
    }
}
