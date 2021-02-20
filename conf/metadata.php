<?php

/**
 * Options for the swarmwebhook plugin
 *
 * @author Michael Große <mic.grosse@googlemail.com>
 */

$meta['service']  = array('multichoice','_choices' => array('IFTTT','Zapier'));
$meta['hook_secret'] = array('password');
