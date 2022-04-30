<?php

namespace ticketlist\api;

use DbQuery;

/**
 * Response to GET /ticket/time/2029,5044
 */
class GetTicketTime extends Action
{
    public function run(array $ids, int $projectId)
    {
        $idList = join(',', $ids);
        $query = new DbQuery();
        $sql = "SELECT sum(n.time_tracking) AS total FROM {bug} JOIN {bugnote} n ON bug.id = n.bug_id WHERE bug.id in ($idList)";
        if ($projectId > 0) {
            $sql .= " AND bug.project_id = {$projectId}";
        }
        $query->sql($sql);
        $rows = $query->fetch_all();
        if (!$rows) {
            return [
                'minutes' => 0,
                'time' => "",
            ];
        }
        $result = [
            'minutes' => (int) $rows[0]['total'],
            'time' => db_minutes_to_hhmm((int) $rows[0]['total']),
        ];

        if ($projectId > 0) {
            $query->sql = "SELECT * FROM {project_version} WHERE project_id = {$projectId} ORDER BY id DESC LIMIT 1";
            $rows = $query->fetch_all();
            if ($rows) {
                $result["release"] = [
                    'name' => $rows[0]['version'],
                    'description' => $rows[0]['description'],
                    'publicationTimestamp' => (int) $rows[0]['date_order'],
                ];

                $query->sql("$sql AND date_submitted > {$result['release']['publicationTimestamp']}");
                $rows = $query->fetch_all();
                $result['minutesSinceRelease'] = (int) $rows[0]['total'];
                $result['timeSinceRelease'] = db_minutes_to_hhmm((int) $row['total']);
            }
        }

        return $result;
    }
}
