<?php

namespace ticketlist\api;

use DbQuery;

/**
 * Response to GET /list
 */
class GetListAll extends Action
{
    public function run(int $projectId)
    {
        $query = new DbQuery();
        $tableName = plugin_table('persistent');
        $query->sql("SELECT * FROM {$tableName} WHERE project_id = {$projectId} ORDER BY name ASC");
        $rows = $query->fetch_all();
        if (!$rows) {
            $rows = [];
        }
        return $rows;
    }
}
