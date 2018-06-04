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
        $htmlIFTTT = '<h2>IFTTT</h2>';
        $htmlIFTTT .= '<ol>';
        $htmlIFTTT .= '<li>';
        $iftttFormHref = 'https://ifttt.com/create/if-any-new-check-in-then-make-a-web-request?sid=5';
        $htmlIFTTT .= 'Go to <a href="' . $iftttFormHref . '">the relevant IFTTT form</a>';
        $htmlIFTTT .= '</li>';
        $htmlIFTTT .= '<li>';
        $htmlIFTTT .= 'Enter the following Data in the Form:';
        $htmlIFTTT .= '<ul>';
        $htmlIFTTT .= '<li>';
        $webhookURL = DOKU_URL . 'lib/plugins/swarmwebhook/webhook.php';
        $htmlIFTTT .= '<strong>URL</strong>: <code>' . $webhookURL . '</code>';
        $htmlIFTTT .= '</li>';
        $htmlIFTTT .= '<li>';
        $htmlIFTTT .= '<strong>Method</strong>: POST';
        $htmlIFTTT .= '</li>';
        $htmlIFTTT .= '<li>';
        $htmlIFTTT .= '<strong>Content Type</strong>: application/json';
        $htmlIFTTT .= '</li>';
        $iftttBody = '
{
"ts": "{{CheckinDate}}",
"shout": "{{Shout}}",
"VenueName": "{{VenueName}}",
"VenueUrl": "{{VenueUrl}}",
"VenueMapImageUrl": "{{VenueMapImageUrl}}",
"secret": "'. $secret . '"
}';
        $htmlIFTTT .= '<li>';
        $htmlIFTTT .= '<strong>Body</strong>: <pre>' . $iftttBody . '</pre>';
        $htmlIFTTT .= '</li>';
        $htmlIFTTT .= '</ul>';
        $htmlIFTTT .= '</li>';
        $htmlIFTTT .= '<li>';
        $htmlIFTTT .= 'Submit the form';
        $htmlIFTTT .= '</li>';
        $htmlIFTTT .= '<li>';
        $htmlIFTTT .= 'Done ✅';
        $htmlIFTTT .= '</li>';
        $htmlIFTTT .= '</ol>';

        echo $htmlIFTTT;
    }
}
