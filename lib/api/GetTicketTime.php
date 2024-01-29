<?php

namespace ticketlist\api;

use DbQuery;

/**
 * Response to GET /ticket/time/2029,5044
 */
class GetTicketTime extends Action
{
    public function run(array $ids, int $projectId, string $dateStart, string $dateEnd)
    {
        $idList = join(',', $ids);
        $query = new DbQuery();
        $sql = "SELECT sum(n.time_tracking) AS total FROM {bug} bug JOIN {bugnote} n ON bug.id = n.bug_id WHERE bug.id IN ($idList)";
        if ($projectId > 0) {
            $sql .= " AND bug.project_id = {$projectId}";
        }

        // dates
        if ($dateStart && $dateEnd) {
            $sql .= " AND n.date_submitted BETWEEN {$dateStart} AND {$dateEnd}";
        } elseif ($dateStart && !$dateEnd) {
            $sql .= " AND n.date_submitted >= {$dateStart}";
        } elseif (!$dateStart && $dateEnd) {
            $sql .= " AND n.date_submitted <= {$dateEnd}";
        }

        $query->sql($sql);
        $rows = $query->fetch_all();
        if (empty($rows)) {
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
            $result["release"] = self::fetchLastRelease($projectId, $sql);
        }

        return $result;
    }

    private static function fetchLastRelease(int $projectId, string $sql): ?array
    {
        $query = new DbQuery();
        $query->sql("SELECT * FROM {project_version} WHERE project_id = {$projectId} ORDER BY id DESC LIMIT 1");
        $rows = $query->fetch_all();
        if (!$rows) {
            return null;
        }
        $ts = (int) $rows[0]['date_order'];
        $result = [
            'id' => (int) $rows[0]['id'],
            'name' => $rows[0]['version'],
            'description' => $rows[0]['description'],
            'publicationTimestamp' => $ts,
        ];

        // If reusing the same $query, no error, but fetch_all() would return [].
        $query2 = new DbQuery();
        $query2->sql("$sql AND n.date_submitted > {$ts}");
        $rows = $query2->fetch_all();
        if (empty($rows)) {
            $result['minutesSinceRelease'] = 0;
            $result['timeSinceRelease'] = '00:00';
        } else {
            $result['minutesSinceRelease'] = (int) $rows[0]['total'];
            $result['timeSinceRelease'] = db_minutes_to_hhmm((int) $rows[0]['total']);
        }
        return $result;
    }
}
