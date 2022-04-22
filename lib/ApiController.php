<?php

class ApiController
{
    private string $path;

    private int $projectId;

    public function __construct(string $path)
    {
        $this->path = trim($path, "/");
        $this->projectId = self::readProjectId();
        header('Content-Type: application/json; charset="UTF-8"');
    }

    public function run(): void
    {
        if (preg_match('#^list/(\d+)$#', $this->path, $m)) {
            $id = (int) $m[1];
            $this->getList($id);
        } elseif (preg_match('#^list/all$#', $this->path)) {
            $this->getListAll($this->projectId);
        }
    }

    /**
     * Response to GET /list/8
     */
    private function getList(int $id): void
    {
        $query = new DbQuery();
        $tableName = plugin_table('persistent');
        $query->sql("SELECT * FROM {$tableName} WHERE id = {$id}");
        $row = $query->fetch();

        self::checkPermission((int) $row['projectId']);

        if (!$row) {
            http_response_code(404);
            $row = null;
        }
        echo json_encode($row);
    }

    /**
     * Response to GET /list/all
     */
    private function getListAll(int $projectId): void
    {
        self::checkPermission($projectId);

        $query = new DbQuery();
        $tableName = plugin_table('persistent');
        $query->sql("SELECT * FROM {$tableName} WHERE project_id = {$projectId}");
        $rows = $query->fetch_all();
        if (!$rows) {
            $rows = [];
        }
        echo json_encode($rows);
    }

    private static function readProjectId(): int
    {
        $projectStr = $_GET['projectId'] ?? '';
        if ($projectStr === '') {
            return (int) helper_get_current_project();
        }
        return $projectId = (int) $projectStr;
    }

    private static function checkPermission(int $projectId): void
    {
        access_ensure_project_level(config_get('view_summary_threshold'), $projectId);
    }
}
