<?php

require_api('helper_api.php');
require_api('authentication_api.php'); // auth_* functions

class Persistent
{
    public static function find(int $id): array
    {
        $tableName = plugin_table('persistent');
        $sql = sprintf("SELECT * FROM {$tableName} WHERE id = $id");
        $it = db_query($sql);
        return db_fetch_array($it);
    }

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

    public static function delete(string $name): void
    {
        $tableName = plugin_table('persistent');
        $projectId = (int) helper_get_current_project();
        $sql = "DELETE FROM {$tableName} WHERE project_id = {$projectId} AND `name` = " . db_param();
        db_query($sql, [$name]);
    }

    public static function save(string $name, string $ids): void
    {
        db_query("CREATE UNIQUE INDEX IF NOT EXISTS persistent_name_u ON plugin_TicketList_persistent (`project_id`, `name`)");

        $tableName = plugin_table('persistent');
        $projectId = (int) helper_get_current_project();
        $authorId = (int) auth_get_current_user_id();
        $sql = sprintf(
            "REPLACE INTO {$tableName} (project_id, `name`, ids, author_id, last_update)"
                . " VALUES ($projectId, %s, %s, $authorId, NOW())",
            db_param(),
            db_param()
        );
        db_query($sql, [$name, $ids]);
    }
}
