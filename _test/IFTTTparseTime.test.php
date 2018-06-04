<?php

namespace dokuwiki\plugin\swarmwebhook\test;

/**
 * General tests for the swarmwebhook plugin
 *
 * @group plugin_swarmwebhook
 * @group plugins
 */
class IFTTTparseTime extends \DokuWikiTest
{
    /** @var array alway enable the needed plugins */
    protected $pluginsEnabled = ['swarmwebhook'];

    public function test_parseTimeIntoTimestamp()
    {
        $IFTTT = new mock\IFTTT();

        $actualDateTime = $IFTTT->parseTimeIntoDateTime('May 24, 2018 at 06:02PM', '1527178045');

        $this->assertEquals('2018-05-24', $actualDateTime->format('Y-m-d'));
        $this->assertEquals('2018-05-24T18:02:00+02:00', $actualDateTime->format(\DateTime::ATOM));
    }
}