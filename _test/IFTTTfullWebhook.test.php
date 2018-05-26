<?php

namespace dokuwiki\plugin\swarmzapierstructwebhook\test;

/**
 * General tests for the swarmzapierstructwebhook plugin
 *
 * @group plugin_swarmzapierstructwebhook
 * @group plugins
 */
class IFTTTfullWebhook extends \DokuWikiTest
{
    /** @var array alway enable the needed plugins */
    protected $pluginsEnabled = ['swarmzapierstructwebhook', 'struct', 'sqlite'];

    public function test_parseTimeIntoTimestamp()
    {
        $inputJSON = '{
        "ts":"May 25, 2018 at 04:32PM",
     "shout":"",
     "VenueName":"Stiftung Für Effektiven Altruismus",
     "VenueUrl":"https://4sq.com/2kfnC4d",
     "VenueMapImageUrl":"http://maps.google.com/maps/api/staticmap?center=52.509712,13.324584&zoom=16&size=710x440&maptype=roadmap&sensor=false&markers=color:red%7C52.509712,13.324584"
     }';

        $IFTTT = new mock\IFTTT();

        $IFTTT->run($inputJSON);

        /** @var \remote_plugin_struct $remote */
        $remote = plugin_load('remote', 'struct');
        $rows = $remote->getAggregationData(
            ['swarm'],
            ['*']
        );

        $expectedRows = [
            'swarm.date' => '2018-05-25',
            'swarm.json' => $inputJSON,
            'swarm.locname' => 'Stiftung Für Effektiven Altruismus',
            'swarm.checkinid' => 'May 25, 2018 at 04:32PM',
            'swarm.shout' => '',
            'swarm.time' => '2018-05-25 16:32',
            'swarm.service' => 'IFTTT',
        ];

//        $this->assertTrue($actualOK, 'single event, initially creating the schema');
        $this->assertEquals($rows[0], $expectedRows);
    }
}