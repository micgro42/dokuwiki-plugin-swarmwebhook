<?php
// phpcs:disable PSR1.Files.SideEffects
if (!defined('DOKU_INC')) {
    define('DOKU_INC', realpath(dirname(__FILE__) . '/../../../') . '/');
}
define('NOSESSION', 1);
require_once(DOKU_INC . 'inc/init.php');
if (!defined('DOKU_TESTING')) {
    // Main
    $hook = \dokuwiki\plugin\swarmwebhook\webhooks\AbstractWebhook::getWebhook();
    $hook->run(file_get_contents('php://input'));
}
