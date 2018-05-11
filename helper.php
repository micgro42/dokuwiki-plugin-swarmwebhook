<?php
use dokuwiki\plugin\struct\meta\AccessTable;
use dokuwiki\plugin\struct\meta\StructException;

/**
 * DokuWiki Plugin swarmzapierstructwebhook (Helper Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Michael Große <mic.grosse@googlemail.com>
 */

class helper_plugin_swarmzapierstructwebhook extends DokuWiki_Plugin
{

    /**
     * Extract the data to be saved from the payload
     *
     * @param array $data
     *
     * @return array
     */
    public function extractDataFromPayload(array $data)
    {
        $checkinID = $data['id'];
        $locationName = $data['venue']['name'];

        $dateTime = $this->getDateTimeInstance($data['createdAt'], $data['timeZoneOffset']);

        $lookupData = [
            'date' => $dateTime->format('Y-m-d'),
            'time' => $dateTime->format(DateTime::ATOM),
            'checkinid' => $checkinID,
            'locname' => $locationName,
        ];
        if (!empty($data['shout'])) {
            $lookupData['shout'] = $data['shout'];
        }
        return $lookupData;
    }

    /**
     * Transforms a timestamp and the timezone offset as provided in the payload into an DateTimeInterface instance
     *
     * @param int $timestamp
     * @param int $payloadTimezoneOffset offset to UTC in minutes
     *
     * @return DateTimeInterface
     */
    protected function getDateTimeInstance($timestamp, $payloadTimezoneOffset)
    {
        $tzSign = $payloadTimezoneOffset >= 0 ? '+' : '-';
        $offsetInHours = $payloadTimezoneOffset / 60;
        $tz = $tzSign . str_pad($offsetInHours * 100, 4, '0', STR_PAD_LEFT);
        $dateTime = new DateTime('now', new DateTimeZone($tz));
        $dateTime->setTimestamp($timestamp);
        return $dateTime;
    }

    /**
     * @param array $data associative array in the form of [columnname => columnvalue]
     */
    public function saveDataToLookup(array $data)
    {
        $access = AccessTable::byTableName('swarm', 0, 0);
        if (!$access->getSchema()->isEditable()) {
            throw new StructException('lookup save error: no permission for schema');
        }
        $validator = $access->getValidator($data);
        if (!$validator->validate()) {
            throw new StructException("Validation failed:\n%s", implode("\n", $validator->getErrors()));
        }
        if (!$validator->saveData()) {
            throw new StructException('No data saved');
        }
    }

    /**
     * Deletes a checkin from the lookup
     *
     * @param string $checkinid
     */
    public function deleteCheckinFromLookup($checkinid)
    {
        $tablename = 'swarm';

        /** @var remote_plugin_struct $remote */
        $remote = plugin_load('remote', 'struct');
        $rows = $remote->getAggregationData(
            [$tablename],
            ['%rowid%'],
            [['logic'=> 'and', 'condition' => "checkinid = $checkinid"]]
        );

        $pids = array_column($rows, '%rowid%');

        if (empty($pids)) {
            return;
        }
        foreach ($pids as $pid) { // should only be a single entry
            $schemadata = AccessTable::byTableName($tablename, $pid);
            if (!$schemadata->getSchema()->isEditable()) {
                throw new StructException('lookup delete error: no permission for schema');
            }
            $schemadata->clearData();
        }
    }
}
