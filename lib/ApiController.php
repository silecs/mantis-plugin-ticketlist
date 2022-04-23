<?php

namespace ticketlist;

class ApiController
{
    private $action;

    public function run(string $actionId): void
    {
        $result = $this->dispatch(trim($actionId, "/"));

        header("Cache-Control: no-store, no-cache, must-revalidate");
        header('Content-Type: application/json; charset="UTF-8"');
        if ($this->action->httpCode !== 400) {
            http_response_code($this->action->httpCode);
        }
        echo json_encode($result);
    }

    private function dispatch(string $actionId)
    {
        switch ($actionId) {
            case "list":
                return $this->dispatchList();
            case "project":
                return $this->dispatchProject();
            case "ticket":
                return $this->dispatchTicket();
            case "ticket/time":
                return $this->dispatchTicketTime();
        }
        $this->action = new api\ErrorAction();
        $this->action->httpCode = 404;
        return $this->action->run("No process matches this parameter: action.");
    }

    private function dispatchList()
    {
        $id = (int) ($_GET['id'] ?? '');
        $verb = self::readRequestVerb();

        if ($verb === 'GET') {
            if ($id > 0) {
                $this->action = new api\ListAction();
                return $this->action->run($id);
            }
            $this->action = new api\ListAllAction();
            return $this->action->run(self::readProjectId());
        }

        if ($verb === 'PUT') {
            // TODO
        }

        if ($verb === 'DELETE') {
            // TODO
        }
    
        $this->action = new api\ErrorAction();
        $this->action->httpCode = 400;
        return $this->action->run("This HTTP verb is not accepted for /list.");
    }

    private function dispatchProject()
    {
        if (self::readRequestVerb() !== 'GET') {
            $this->action = new api\ErrorAction();
            $this->action->httpCode = 400;
            return $this->action->run("The only HTTP verb accepted for /project is GET.");
        }
        $id = (int) ($_GET['id'] ?? '');
        if ($id <= 0) {
            $this->action = new api\ErrorAction();
            $this->action->httpCode = 400;
            return $this->action->run("Missing parameter: id");
        }
        $this->action = new api\ProjectAction();
        return $this->action->run($id);
    }

    private function dispatchTicket()
    {
        if (self::readRequestVerb() !== 'GET') {
            $this->action = new api\ErrorAction();
            $this->action->httpCode = 400;
            return $this->action->run("The only HTTP verb accepted for /ticket is GET.");
        }
        $id = ($_GET['id'] ?? '');
        $ids = array_filter(array_map('intval', explode(',', $id)));
        if (!$ids) {
            $this->action = new api\ErrorAction();
            $this->action->httpCode = 400;
            return $this->action->run("Missing parameter (comma separated integer list): id");
        }
        $this->action = new api\TicketAction();
        return $this->action->run($ids);
    }

    private function dispatchTicketTime()
    {
        if (self::readRequestVerb() !== 'GET') {
            $this->action = new api\ErrorAction();
            $this->action->httpCode = 400;
            return $this->action->run("The only HTTP verb accepted for /ticket is GET.");
        }
        $id = ($_GET['id'] ?? '');
        $ids = array_filter(array_map('intval', explode(',', $id)));
        if (!$ids) {
            $this->action = new api\ErrorAction();
            $this->action->httpCode = 400;
            return $this->action->run("Missing parameter (comma separated integer list): id");
        }
        $this->action = new api\TicketTimeAction();
        return $this->action->run($ids, self::readProjectId());
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

        $projectId = (int) $projectStr;
        access_ensure_project_level(config_get('view_summary_threshold'), $projectId);
        return $projectId;
    }
}
