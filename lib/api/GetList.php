<?php

namespace ticketlist\api;

use DbQuery;

use ticketlist\HttpException;

/**
 * Response to GET /list/8
 */
class GetList extends Action
{
    public function run(int $id)
    {
        $query = new DbQuery();
        $tableName = plugin_table('persistent');
        $query->sql("SELECT * FROM {$tableName} WHERE id = {$id}");
        $row = $query->fetch();

        $result = null;
        if (!$row) {
            throw new HttpException(404, "Liste non trouvée.");
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
