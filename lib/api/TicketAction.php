<?php

namespace ticketlist\api;

use DbQuery;
use MantisEnum;

/**
 * Response to GET /ticket/2029,5044
 */
class TicketAction implements Action
{
    public int $httpCode = 200;

    public function run(array $ids)
    {
        $idList = join(',', $ids);
        $sql = <<<EOSQL
            SELECT b.id, b.status, b.summary
            FROM {bug} b
            WHERE b.id in ($idList)
            ORDER BY find_in_set(b.id, '$idList') ASC
            EOSQL;
        $query = new DbQuery();
        $query->sql($sql);
        $rows = $query->fetch_all();

        $toFrStatus = MantisEnum::getAssocArrayIndexedByValues(lang_get('status_enum_string'));
        $result = [];
        $accessLevel = config_get('view_summary_threshold');
        if ($rows) {
            foreach ($rows as $row) {
                if (!access_has_bug_level($accessLevel, (int) $row['id'])) {
                    // TODO Add a message in the response about the unauthorized bug_id.
                    continue;
                }
                $result[] = [
                    'id' => (int) $row['id'],
                    'status' => (int) $row['status'],
                    'statusTxt' => $toFrStatus[(int) $row['status']],
                    'summary' => $row['summary'],
                    'link' => string_get_bug_view_link((int) $row['id'], null, false)
                ];
            }
        }
        return $result;
    }
}
