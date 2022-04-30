<?php

namespace ticketlist\api;

use DbQuery;

use ticketlist\HttpException;

/**
 * Response to GET /list?name=X
 */
class GetListByName extends Action
{
    public function run(string $name, int $projectId)
    {
        $query = new DbQuery();
        $tableName = plugin_table('persistent');
        $query->sql("SELECT * FROM {$tableName} WHERE name = :name AND project_id = {$projectId} LIMIT 1");
        $query->execute(['name' => $name]);
        $row = $query->fetch();

        if (!$row) {
            throw new HttpException(404, "Liste non trouvée. SELECT * FROM {$tableName} WHERE name = '$name' AND project_id = {$projectId} LIMIT 1");
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
