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
        $timestamp = $data['createdAt'];
        $checkinID = $data['id'];
        $date = date('Y-m-d', $timestamp); // FIXME: use timezone offset?
        $locationName = $data['venue']['name'];

        $lookupData = [
            'date' => $date,
            'time' => date_iso8601($timestamp),
            'checkinid' => $checkinID,
            'locname' => $locationName,
        ];
        if (!empty($data['shout'])) {
            $lookupData['shout'] = $data['shout'];
        }
        return $lookupData;
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
