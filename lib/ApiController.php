<?php

class ApiController
{
    private string $action;

    public function __construct(string $action)
    {
        $this->action = trim($action, "/");
    }

    public function run(): void
    {
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header('Content-Type: application/json; charset="UTF-8"');

        switch ($this->action) {
            case "list":
                $id = (int) ($_GET['id'] ?? '');
                $verb = self::readRequestVerb();
                if ($verb === 'GET') {
                    if ($id > 0) {
                        $this->getList($id);
                    } else {
                        $this->getListAll(self::readProjectId());
                    }
                } elseif ($verb === 'PUT') {
                    // TODO
                } elseif ($verb === 'DELETE') {
                    // TODO
                } else {
                    http_response_code(400);
                    echo '"This HTTP verb is not accepted for /list."';
                    return;
                }
                break;
            case "project":
                if (self::readRequestVerb() !== 'GET') {
                    http_response_code(400);
                    echo '"Only GET verb is accepted for /project."';
                    return;
                }
                $id = (int) ($_GET['id'] ?? '');
                if ($id <= 0) {
                    http_response_code(400);
                    echo '"Missing parameter: id"';
                    return;
                }
                $this->getProject($id);
                break;
            case "ticket":
                if (self::readRequestVerb() !== 'GET') {
                    http_response_code(400);
                    echo '"Only GET verb is accepted for /ticket."';
                    return;
                }
                $id = ($_GET['id'] ?? '');
                $ids = array_filter(array_map('intval', explode(',', $id)));
                if (!$ids) {
                    http_response_code(400);
                    echo '"Missing parameter (comma separated integer list): id"';
                    return;
                }
                $this->getTicket($ids);
                break;
            default:
                http_response_code(404);
                echo '"No process matches this parameter: action."';
                return;
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

        $result = null;
        if (!$row) {
            http_response_code(404);
        } else {
            $result = [
                'id' => (int) $row['id'],
                'name' => $row['name'],
                'projectId' => (int) $row['project_id'],
                'authorId' => (int) $row['author_id'],
                'ids' => $row['ids'],
                'lastUpdate' => $row['last_update'],
            ];
            self::checkPermission($result['projectId']);
        }
        echo json_encode($result);
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

    /**
     * Response to GET /project/4
     */
    private function getProject(int $id): void
    {
        self::checkPermission($id);

        $query = new DbQuery();
        $query->sql("SELECT id, name FROM {project} WHERE id = {$id}");
        $row = $query->fetch();

        if (!$row) {
            http_response_code(404);
            $row = null;
        }
        echo json_encode($row);
    }

    /**
     * Response to GET /ticket/2029,5044
     */
    private function getTicket(array $ids): void
    {
        $idList = join(',', $ids);
        $sql = <<<EOSQL
            SELECT b.id, b.status, b.summary
            FROM {bug} b
            WHERE b.id in ($idList)
            ORDER BY find_in_set(b.id, '$idList') ASC
            EOSQL;
        $query = new DbQuery();
        $query->sql($sql);
        $rows = $query->fetch_all();

        $toFrStatus = MantisEnum::getAssocArrayIndexedByValues(lang_get('status_enum_string'));
        $result = [];
        $accessLevel = config_get('view_summary_threshold');
        if ($rows) {
            foreach ($rows as $row) {
                if (!access_has_bug_level($accessLevel, (int) $row['id'])) {
                    // TODO Add a message in the response about the unauthorized bug_id.
                    continue;
                }
                $result[] = [
                    'id' => (int) $row['id'],
                    'status' => (int) $row['status'],
                    'statusTxt' => $toFrStatus[(int) $row['status']],
                    'summary' => $row['summary'],
                    'link' => string_get_bug_view_link((int) $row['id'], null, false)
                ];
            }
        }
        echo json_encode($result);
    }

    private static function readRequestVerb(): string
    {
        return strtoupper(
            $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] ?? $_SERVER['REQUEST_METHOD'] ?? 'GET'
        );
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
