<?php

namespace dokuwiki\plugin\swarmwebhook\test;

/**
 * General tests for the swarmwebhook plugin
 *
 * @group plugin_swarmwebhook
 * @group plugins
 */
class ZapierExtractData extends \DokuWikiTest
{
    /** @var array alway enable the needed plugins */
    protected $pluginsEnabled = ['swarmwebhook'];

    public function dataProvider()
    {
        return [
            [
                '{"createdAt": "1525849118", "like": "False", "isMayor": "True", "editableUntil": "1525935518000", "posts": {"count": "0", "textCount": "0"}, "comments": {"count": "0"}, "photos": {"count": "0", "items": ""}, "likes": {"count": "0", "groups": ""}, "venue": {"stats": {"tipCount": "2", "checkinsCount": "1552", "usersCount": "24"}, "name": "CosmoCode", "venueRatingBlacklisted": "True", "url": "http://www.cosmocode.de", "contact": {"twitter": "cosmocode"}, "location": {"city": "Berlin", "labeledLatLngs": "label: display\nlat: 52.5341728565\nlng: 13.4235969339", "cc": "DE", "country": "Germany", "postalCode": "10405", "state": "Berlin", "formattedAddress": "Prenzlauer Allee 36 (Marienburger Strasse),10405 Berlin", "crossStreet": "Marienburger Strasse", "address": "Prenzlauer Allee 36", "lat": "52.5341728565", "lng": "13.4235969339"}, "beenHere": {"lastCheckinExpiredAt": "0"}, "verified": "False", "id": "4b4ca6c8f964a520f8b826e3", "categories": "icon: {u\'prefix\': u\'https://ss3.4sqi.net/img/categories_v2/building/default_\', u\'suffix\': u\'.png\'}\nid: 4bf58dd8d48988d124941735\nname: Office\npluralName: Offices\nprimary: True\nshortName: Office"}, "type": "checkin", "id": "5af29c1e6fd626002c38730b", "timeZoneOffset": "120", "source": {"url": "https://www.swarmapp.com", "name": "Swarm for Android"}}',
                [
                    'date' => '2018-05-09',
                    'time' => '2018-05-09T08:58:38+02:00',
                    'checkinid' => '5af29c1e6fd626002c38730b',
                    'locname' => 'CosmoCode',
                    'service' => 'Zapier',
                ],
                'normal event without shout',
            ],
            [
                '{"entities": "", "createdAt": "1526051437", "like": "False", "isMayor": "False", "editableUntil": "1526137837000", "sticker": {"bonusStatus": "Use once per week. Recharges Sunday at midnight.", "group": {"index": "85", "name": "collectible"}, "name": "Baggs", "unlockText": "They\'re out of milk, your cart\'s wheel is busted, and that lady has way more than 10 items! Here\'s hoping Baggs doesn\'t dump your eggs on the ground.", "image": {"prefix": "https://irs1.4sqi.net/img/sticker/", "name": "/groceries_2a2425.png", "sizes": "60,94,150,300"}, "pickerPosition": {"index": "13", "page": "3"}, "points": "2", "teaseText": "Check in at food & drink shops to unlock this sticker.", "bonusText": "Use at Food & Drink Shops for a bonus.", "id": "55563bd52beaa0fbc4d1dc3f", "stickerType": "unlockable"}, "posts": {"count": "0", "textCount": "0"}, "comments": {"count": "0"}, "photos": {"count": "0", "items": ""}, "shout": "Reiswaffeln \ud83c\udf5a", "likes": {"count": "0", "groups": ""}, "venue": {"stats": {"tipCount": "0", "checkinsCount": "368", "usersCount": "140"}, "name": "EDEKA Rhinstra\u00dfe", "url": "http://www.kaisers.de", "allowMenuUrlEdit": "True", "contact": {"facebookName": "Kaiser\'s Berlin", "facebookUsername": "KaisersBerlin", "facebook": "352765558110244", "formattedPhone": "0208 37770", "phone": "020837770"}, "location": {"city": "Berlin", "labeledLatLngs": "label: display\nlat: 52.5135124211\nlng: 13.5182151518", "cc": "DE", "country": "Germany", "postalCode": "10315", "state": "Berlin", "formattedAddress": "Rhinstr. 17,10315 Berlin", "address": "Rhinstr. 17", "lat": "52.5135124211", "lng": "13.5182151518"}, "beenHere": {"lastCheckinExpiredAt": "0"}, "verified": "True", "id": "4d79bd5b7418a14366cfc05b", "categories": "icon: {u\'prefix\': u\'https://ss3.4sqi.net/img/categories_v2/shops/food_grocery_\', u\'suffix\': u\'.png\'}\nid: 52f2ab2ebcbc57f1066b8b46\nname: Supermarket\npluralName: Supermarkets\nprimary: True\nshortName: Supermarket"}, "type": "checkin", "id": "5af5b26d898bdc002c7a17db", "timeZoneOffset": "120", "source": {"url": "https://www.swarmapp.com", "name": "Swarm for Android"}}',
                [
                    'date' => '2018-05-11',
                    'time' => '2018-05-11T17:10:37+02:00',
                    'checkinid' => '5af5b26d898bdc002c7a17db',
                    'locname' => 'EDEKA Rhinstraße',
                    'shout' => 'Reiswaffeln 🍚',
                    'service' => 'Zapier',
                ],
                'normal event with shout and sticker',
            ]
        ];
    }

    /**
     * @dataProvider dataProvider
     *
     * @param $inputJSON
     * @param $expectedExtractedData
     * @param $msg
     */
    public function test_extractDataFromPayload($inputJSON, $expectedExtractedData, $msg)
    {
        $zapierWebhook = new mock\Zapier();
        $inputArray = json_decode($inputJSON, true);

        $actualExtractedData = $zapierWebhook->extractDataFromPayload($inputArray);

        $this->assertEquals($expectedExtractedData, $actualExtractedData, $msg);
    }
}