<?php

namespace ticketlist\models;

use DbQuery;

\require_api('authentication_api.php'); // auth_* functions

class Liste
{
    public int $id = 0;
    public int $projectId = 0;
    public string $name = '';
    public string $ids = '';
    public string $history = '';
    public int $authorId = 0;
    public string $lastUpdate = '';

    public function __construct(array $row)
    {
        if ($row) {
            $this->id = empty($row['id']) ? 0 : (int) $row['id'];
            $this->projectId = (int) ($row['projectId'] ?? $row['project_id']);
            $this->name = $row['name'];
            $this->ids = $row['ids'];
            $this->history = $row['history'] ?? '';
            $this->authorId = (int) ($row['authorId'] ?? $row['author_id'] ?? 0);
            $this->lastUpdate = $row['lastUpdate'] ?? $row['last_update'];
        }
    }

    public static function findByPk(int $id): self
    {
        $tableName = plugin_table('persistent');
        $query = new DbQuery("SELECT * FROM {$tableName} WHERE id = {$id} LIMIT 1");
        $row = $query->fetch();
        if ($row === false) {
            throw new \Exception("Record not found in the DB.");
        }
        return new self($row);
    }

    public function save(): bool
    {
        $this->authorId = (int) auth_get_current_user_id();
        $this->lastUpdate = date("Y-m-d H:i:s");
        if ($this->id) {
            $this->history = self::addHistory(self::findByPk($this->id));
        } else {
            $this->history = '[]';
        }

        $tableName = plugin_table('persistent');
        $sql = "REPLACE INTO {$tableName} (id, project_id, `name`, ids, history, author_id, last_update)"
            . " VALUES (:id, :project_id, :name, :ids, :history, :author_id, :last_update)";
        $query = new DbQuery($sql);
        $result = $query->execute([
            'id' => $this->id ?: null,
            'project_id' => $this->projectId,
            'name' => $this->name,
            'ids' => $this->ids,
            'history' => $this->history,
            'author_id' => $this->authorId,
            'last_update' => $this->lastUpdate,
        ]);
        if ($result !== false && !$this->id) {
            $this->id = (int) db_insert_id('persistent');
        }
        return $result !== false;
    }

    private static function addHistory(?Liste $record): string
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
            if (count($history) > 10) {
                $history = array_slice($history, -10);
            }
            array_push($history, $current);
        }
        return \json_encode($history, \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE);
    }

    public function __set($name, $value) {
        throw new \Exception("Invalid property '$name'");
    }
}
