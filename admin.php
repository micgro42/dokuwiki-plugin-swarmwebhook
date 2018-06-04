<?php

class admin_plugin_swarmwebhook extends DokuWiki_Admin_Plugin
{

    public function forAdminOnly()
    {
        return true;
    }

    public function handle()
    {
    }


    public function html()
    {
        echo '<h1>Instructions to create the swarm webhook with IFTTT</h1>';
        $secret = $this->getConf('hook_secret');
        if (empty($secret)) {
            $exampleSecret = md5(time());
            $settingsID = 'plugin____swarmwebhook____plugin_settings_name';
            $configHRef = DOKU_REL . DOKU_SCRIPT . '?do=admin&page=config#' . $settingsID;
            $configLink = '<a href="' . $configHRef . '">' . $this->getLang('configuration') . '</a>';
            $secretNeededMsg = sprintf(
                $this->getLang('secret needed'),
                $configLink,
                '<code>' . $exampleSecret . '</code>'
            );
            echo '<p>' . $secretNeededMsg . '</p>';
            return;
        }

        echo $this->iftttInstructionsHTML();
    }

    /**
     * Get the instructions for IFTTT
     *
     * @return string
     */
    protected function iftttInstructionsHTML()
    {
        $secret = $this->getConf('hook_secret');

        $html = $this->locale_xhtml('ifttt_instructions');

        $html = str_replace('DOKU_URL', DOKU_URL, $html);
        $html = str_replace('$secret', hsc($secret), $html);

        return $html;
    }
}
