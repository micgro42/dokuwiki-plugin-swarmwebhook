<?php

namespace dokuwiki\plugin\swarmzapierstructwebhook\webhooks;

abstract class AbstractWebhook
{
    abstract public function run($json);

    /**
     * @return AbstractWebhook
     */
    public static function getWebhook()
    {
        global $INPUT;

        if ($INPUT->server->str('HTTP_USER_AGENT') === 'Zapier') {
            return new Zapier();
        }

        // TODO: we currently have no positive key for IFTTT. Find one.
        // That would be better than just having it as default.
        return new IFTTT();
    }
}
