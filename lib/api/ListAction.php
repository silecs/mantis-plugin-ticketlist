<?php

namespace ticketlist\api;

use DbQuery;

/**
 * Response to GET /list/8
 */
class ListAction implements Action
{
    public int $httpCode = 200;

    public function run(int $id)
    {
        $query = new DbQuery();
        $tableName = plugin_table('persistent');
        $query->sql("SELECT * FROM {$tableName} WHERE id = {$id}");
        $row = $query->fetch();

        $result = null;
        if (!$row) {
            $this->httpCode = 404;
            return null;
        }
        return [
            'id' => (int) $row['id'],
            'name' => $row['name'],
            'projectId' => (int) $row['project_id'],
            'authorId' => (int) $row['author_id'],
            'ids' => $row['ids'],
            'lastUpdate' => $row['last_update'],
        ];
    }
}
