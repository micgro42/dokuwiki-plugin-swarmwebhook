<?php

namespace dokuwiki\plugin\swarmwebhook\test;

/**
 * General tests for the swarmwebhook plugin
 *
 * @group plugin_swarmwebhook
 * @group plugins
 */
class ZapierHandlePayload extends \DokuWikiTest
{
    /** @var array alway enable the needed plugins */
    protected $pluginsEnabled = ['swarmwebhook', 'struct', 'sqlite'];


    /**
     * Do not refactor these with a DataProvider -> struct database state would depend on order of execution
     */
    public function test_handleWebhookPayload_initial_single()
    {
        $inputJSON = '{"createdAt": "1525849118", "like": "False", "isMayor": "True", "editableUntil": "1525935518000", "posts": {"count": "0", "textCount": "0"}, "comments": {"count": "0"}, "photos": {"count": "0", "items": ""}, "likes": {"count": "0", "groups": ""}, "venue": {"stats": {"tipCount": "2", "checkinsCount": "1552", "usersCount": "24"}, "name": "CosmoCode", "venueRatingBlacklisted": "True", "url": "http://www.cosmocode.de", "contact": {"twitter": "cosmocode"}, "location": {"city": "Berlin", "labeledLatLngs": "label: display\nlat: 52.5341728565\nlng: 13.4235969339", "cc": "DE", "country": "Germany", "postalCode": "10405", "state": "Berlin", "formattedAddress": "Prenzlauer Allee 36 (Marienburger Strasse),10405 Berlin", "crossStreet": "Marienburger Strasse", "address": "Prenzlauer Allee 36", "lat": "52.5341728565", "lng": "13.4235969339"}, "beenHere": {"lastCheckinExpiredAt": "0"}, "verified": "False", "id": "4b4ca6c8f964a520f8b826e3", "categories": "icon: {u\'prefix\': u\'https://ss3.4sqi.net/img/categories_v2/building/default_\', u\'suffix\': u\'.png\'}\nid: 4bf58dd8d48988d124941735\nname: Office\npluralName: Offices\nprimary: True\nshortName: Office"}, "type": "checkin", "id": "5af29c1e6fd626002c38730b", "timeZoneOffset": "120", "source": {"url": "https://www.swarmapp.com", "name": "Swarm for Android"}}';
        $webhook = new mock\Zapier();

        $webhook->run($inputJSON);

        /** @var \remote_plugin_struct $remote */
        $remote = plugin_load('remote', 'struct');
        $rows = $remote->getAggregationData(
            ['swarm'],
            ['*']
        );

        $expectedRows = [
            'swarm.date' => '2018-05-09',
            'swarm.json' => $inputJSON,
            'swarm.locname' => 'CosmoCode',
            'swarm.checkinid' => '5af29c1e6fd626002c38730b',
            'swarm.shout' => '',
            'swarm.time' => '2018-05-09 08:58',
            'swarm.service' => 'Zapier',
        ];

        $this->assertEquals($rows[0], $expectedRows, 'single event, initially creating the schema');
    }

    /**
     * Do not refactor these with a DataProvider -> struct database state would depend on order of execution
     */
    public function test_handleWebhookPayload_initial_double()
    {
        $inputJSON = '{"createdAt": "1525849118", "like": "False", "isMayor": "True", "editableUntil": "1525935518000", "posts": {"count": "0", "textCount": "0"}, "comments": {"count": "0"}, "photos": {"count": "0", "items": ""}, "likes": {"count": "0", "groups": ""}, "venue": {"stats": {"tipCount": "2", "checkinsCount": "1552", "usersCount": "24"}, "name": "CosmoCode", "venueRatingBlacklisted": "True", "url": "http://www.cosmocode.de", "contact": {"twitter": "cosmocode"}, "location": {"city": "Berlin", "labeledLatLngs": "label: display\nlat: 52.5341728565\nlng: 13.4235969339", "cc": "DE", "country": "Germany", "postalCode": "10405", "state": "Berlin", "formattedAddress": "Prenzlauer Allee 36 (Marienburger Strasse),10405 Berlin", "crossStreet": "Marienburger Strasse", "address": "Prenzlauer Allee 36", "lat": "52.5341728565", "lng": "13.4235969339"}, "beenHere": {"lastCheckinExpiredAt": "0"}, "verified": "False", "id": "4b4ca6c8f964a520f8b826e3", "categories": "icon: {u\'prefix\': u\'https://ss3.4sqi.net/img/categories_v2/building/default_\', u\'suffix\': u\'.png\'}\nid: 4bf58dd8d48988d124941735\nname: Office\npluralName: Offices\nprimary: True\nshortName: Office"}, "type": "checkin", "id": "5af29c1e6fd626002c38730b", "timeZoneOffset": "120", "source": {"url": "https://www.swarmapp.com", "name": "Swarm for Android"}}';
        $webhook = new mock\Zapier();

        $actualOK = $webhook->handleWebhookPayload($inputJSON);
        $actualOK = $actualOK && $webhook->handleWebhookPayload($inputJSON);

        /** @var \remote_plugin_struct $remote */
        $remote = plugin_load('remote', 'struct');
        $rows = $remote->getAggregationData(
            ['swarm'],
            ['*']
        );

        $expectedRows = [
            'swarm.date' => '2018-05-09',
            'swarm.json' => $inputJSON,
            'swarm.locname' => 'CosmoCode',
            'swarm.checkinid' => '5af29c1e6fd626002c38730b',
            'swarm.shout' => '',
            'swarm.time' => '2018-05-09 08:58',
            'swarm.service' => 'Zapier',
        ];

        $this->assertTrue($actualOK, 'single event, initially creating the schema');
        $this->assertCount(1, $rows, 'saving a payload twice should only create one row');
        $this->assertEquals($rows[0], $expectedRows);
    }
}