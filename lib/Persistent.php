<?php

require_api('helper_api.php');
require_api('authentication_api.php'); // auth_* functions

class Persistent
{
    public static function getNames(int $projectId): array
    {
        if (!$projectId) {
            return [];
        }
        $tableName = plugin_table('persistent');
        $sql = "SELECT id, name, UNIX_TIMESTAMP(last_update) as last_update_ts FROM {$tableName} WHERE project_id = {$projectId} ORDER BY name ASC}";
        $query = db_query($sql);
        $result = [];
        foreach ($query as $row) {
            $result[$row['id']] = [$row['name'], $row['last_update_ts']];
        }
        return $result;
    }

    public static function save(string $name, string $ids): void
    {
        $tableName = plugin_table('persistent');
        $projectId = (int) helper_get_current_project();
        $authorId = (int) auth_get_current_user_id();
        $sql = sprintf("REPLACE INTO {$tableName} VALUES (NULL, $projectId, %s, %s, $authorId, NOW())", db_param(), db_param());
        db_query($sql, [$name, $ids]);
    }
}
