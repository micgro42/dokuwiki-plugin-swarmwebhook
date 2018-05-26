<?php

namespace dokuwiki\plugin\swarmzapierstructwebhook\test;

/**
 * General tests for the swarmzapierstructwebhook plugin
 *
 * @group plugin_swarmzapierstructwebhook
 * @group plugins
 */
class IFTTTparseTime extends \DokuWikiTest
{
    /** @var array alway enable the needed plugins */
    protected $pluginsEnabled = ['swarmzapierstructwebhook'];

    public function test_parseTimeIntoTimestamp()
    {
        $IFTTT = new mock\IFTTT();

        $actualDateTime = $IFTTT->parseTimeIntoTimestamp('May 24, 2018 at 06:02PM', '1527178045');

        $this->assertEquals('2018-05-24', $actualDateTime->format('Y-m-d'));
        $this->assertEquals('2018-05-24T18:02:00+02:00', $actualDateTime->format(\DateTime::ATOM));
    }
}