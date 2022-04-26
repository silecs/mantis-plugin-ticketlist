<?php

namespace ticketlist\api;

use DbQuery;
use MantisEnum;

/**
 * Response to GET /ticket/2029,5044
 */
class GetTicket extends Action
{
    public function run(array $ids, int $projectId)
    {
        $idList = join(',', $ids);
        $sql = <<<EOSQL
            SELECT b.id, b.status, b.summary, b.project_id
            FROM {bug} b
            WHERE b.id in ($idList)
            ORDER BY find_in_set(b.id, '$idList') ASC
            EOSQL;
        $query = new DbQuery();
        $query->sql($sql);
        $rows = $query->fetch_all() ?: [];

        return self::formatResults($rows, $projectId);
    }

    private static function formatResults(array $rows, int $projectId): array
    {
        if (!$rows) {
            return ['tickets' => '', 'message' => ''];
        }

        $tickets = [];
        $unauthorized = [];
        $wrongProject = [];
        $toFrStatus = MantisEnum::getAssocArrayIndexedByValues(lang_get('status_enum_string'));
        $accessLevel = \config_get('view_summary_threshold');
        foreach ($rows as $row) {
            if (!\access_has_bug_level($accessLevel, (int) $row['id'])) {
                $unauthorized[] = (int) $row['id'];
                continue;
            }
            if ($projectId > 0 && (int) $row['project_id'] !== $projectId) {
                $wrongProject[] = (int) $row['id'];
                continue;
            }
            $tickets[] = [
                'id' => (int) $row['id'],
                'status' => (int) $row['status'],
                'statusTxt' => $toFrStatus[(int) $row['status']],
                'summary' => $row['summary'],
                'link' => \string_get_bug_view_link((int) $row['id'], null, false)
            ];
        }

        $messages = [];
        if ($unauthorized) {
            $messages[] = "Ces tickets n'apparaîssent pas car non autorisés : "
                . join(" ", $unauthorized) . ".";
        }
        if ($wrongProject) {
            $messages[] = "Ces tickets n'apparaîssent pas car hors appartenant à un autre projet : "
                . join(" ", $wrongProject) . ".";
        }
        return [
            'tickets' => $tickets,
            'message' => ($messages ? join("\n", $messages) : ""),
        ];
    }
}
