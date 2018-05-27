<?php


namespace dokuwiki\plugin\swarmwebhook\meta;

class Response
{
    public $code;
    public $content;

    public function __construct($code, $content = '')
    {
        $this->code = $code;
        $this->content = $content;
    }
}
