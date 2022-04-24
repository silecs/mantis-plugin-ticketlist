<?php

namespace ticketlist\api;

use DbQuery;
use ticketlist\HttpException;

\require_api('authentication_api.php'); // auth_* functions

/**
 * Response to PUT /list
 */
class PutList implements Action
{
    public int $httpCode = 200;

    public function run(\stdClass $toSave)
    {
        $tableName = plugin_table('persistent');
        db_query("CREATE UNIQUE INDEX IF NOT EXISTS persistent_name_u ON {$tableName} (`project_id`, `name`)");

        // TODO Validate the list to save (projectId, etc).

        if ($toSave->id > 0) {
            return $this->update($toSave);
        }
        return $this->create($toSave);
    }

    private function create(\stdClass $toSave): array
    {
        return self::save($toSave, null);
    }

    private function update(\stdClass $toSave): array
    {
        $dbRecord = self::getDbRecord($toSave->id);
        if ($dbRecord->projectId !== $toSave->projectId) {
            throw new HttpException(400, "Wrong project ID.");
        }
        if ($dbRecord->lastUpdate !== $toSave->lastUpdate) {
            return [
                'status' => 'need-confirm',
                'message' => "Cette liste a été modifiée par ailleurs.",
                'content' => $dbRecord,
            ];
        }
        return self::save($toSave, $dbRecord);
    }

    private static function save(\stdClass $toSave, ?\stdClass $dbRecord): array
    {
        $tableName = plugin_table('persistent');
        $sql = "REPLACE INTO {$tableName} (id, project_id, `name`, ids, history, author_id, last_update)"
            . " VALUES (:id, :project_id, :name, :ids, :history, :author_id, NOW())";
        $query = new DbQuery($sql);
        $query->execute([
            'id' => $toSave->id,
            'project_id' => $toSave->projectId,
            'name' => $toSave->name,
            'ids' => $toSave->ids,
            'history' => self::addHistory($dbRecord),
            'author_id' => (int) auth_get_current_user_id(),
        ]);

        // TODO Check that the SQL was executed without any error.

        if ($toSave->id > 0) {
            $newId = $toSave->id;
            $message = "La modification est enregistrée sur le serveur.";
         } else {
            $newId = (int) db_insert_id('persistent');
            $message = "Cette nouvelle liste est enregistrée sur le serveur.";
         }
        return [
            'status' => 'success',
            'message' => $message,
            'content' => self::getDbRecord($newId),
        ];
    }

    private static function addHistory(?\stdClass $record): string
    {
        if (empty($record)) {
            return '[]';
        }
        $current = [
            'name' => $record->name,
            'ids' => $record->ids,
            'author_id' => $record->authorId,
            'lastUpdate' => $record->lastUpdate,
        ];
        if (empty($record->history) || $record->history === '[]') {
            $history = [$current];
        } else {
            $history = \json_decode($record->history, true);
            array_push($history, $current);
        }
        return \json_encode($history, \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE);
    }

    private static function getDbRecord(int $id): ?object
    {
        $tableName = plugin_table('persistent');
        $query = new DbQuery("SELECT * FROM {$tableName} WHERE id = {$id} LIMIT 1");
        $row = $query->fetch();
        return (object) [
            'id' => empty($row['id']) ? null : (int) $row['id'],
            'projectId' => (int) $row['project_id'],
            'name' => $row['name'],
            'ids' => $row['ids'],
            'history' => $row['history'],
            'authorId' => (int) $row['author_id'],
            'lastUpdate' => $row['last_update'],
        ];
    }
}
