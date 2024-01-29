<?php

namespace ticketlist;

use ticketlist\models\Liste;

class ApiRouter
{
    public Response $response;

    public function __construct()
    {
        $this->response = new Response();
    }

    public function run(string $actionId): Response
    {
        try {
            $this->setResult($this->dispatch($actionId));
        } catch (HttpException $e) {
            $this->setResult($this->returnError($e->getCode(), ['error' => $e->getMessage()]));
        } catch (\Throwable $e) {
            $this->setResult($this->returnError(500, ['error' => $e->getMessage()]));
        }
        return $this->response;
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
        return $this->returnError(400, "No process matches this parameter: action.");
    }

    private function dispatchList()
    {
        $id = (int) ($_GET['id'] ?? '');
        $verb = Request::readRequestVerb();

        if ($verb === 'GET') {
            if ($id > 0) {
                $this->setAction(new api\GetList());
                return $this->response->action->run($id);
            }
            $name = \rawurldecode($_GET['name'] ?? '');
            if ($name) {
                $this->setAction(new api\GetListByName());
                return $this->response->action->run($name, Request::readProjectId());
            }
            $this->setAction(new api\GetListAll());
            return $this->response->action->run(Request::readProjectId());
        }

        if ($verb === 'PUT') {
            $this->setAction(new api\PutList());
            $liste = new Liste(Request::readBody());
            return $this->response->action->run($liste);
        }

        if ($verb === 'DELETE') {
            $this->setAction(new api\DeleteList());
            $liste = new Liste(Request::readBody());
            return $this->response->action->run($liste);
        }
    
        return $this->returnError(405, "This HTTP verb is not accepted for /list.");
    }

    private function dispatchProject()
    {
        if (Request::readRequestVerb() !== 'GET') {
            return $this->returnError(405, "The only HTTP verb accepted for /project is GET.");
        }
        $id = (int) ($_GET['id'] ?? '');
        if ($id <= 0) {
            return $this->returnError(400, "Missing parameter: id");
        }
        $this->setAction(new api\GetProject());
        return $this->response->action->run($id);
    }

    private function dispatchTicket()
    {
        if (Request::readRequestVerb() !== 'GET') {
            return $this->returnError(405, "The only HTTP verb accepted for /ticket is GET.");
        }
        $id = ($_GET['id'] ?? '');
        $ids = array_filter(array_map('intval', explode(',', $id)));
        if (!$ids) {
            return $this->returnError(400, "Missing parameter (comma separated integer list): id");
        }
        $dateStart = self::readUrlDate('dateStart');
        $dateEnd = self::readUrlDate('dateEnd');
        $this->setAction(new api\GetTicket());
        return $this->response->action->run($ids, Request::readProjectId(), $dateStart, $dateEnd);
    }

    private function dispatchTicketTime()
    {
        if (Request::readRequestVerb() !== 'GET') {
            return $this->returnError(405, "The only HTTP verb accepted for /ticket is GET.");
        }
        $id = ($_GET['id'] ?? '');
        $ids = array_filter(array_map('intval', explode(',', $id)));
        if (!$ids) {
            return $this->returnError(400, "Missing parameter (comma separated integer list): id");
        }
        $dateStart = self::readUrlDate('dateStart');
        $dateEnd = self::readUrlDate('dateEnd');
        $this->setAction(new api\GetTicketTime());
        return $this->response->action->run($ids, Request::readProjectId(), $dateStart, $dateEnd);
    }

    private function returnError(int $code, $message)
    {
        $error = new api\ErrorAction($code);
        $this->setAction($error);
        return $this->response->action->run($message);
    }

    private function setAction(api\Action $a): void
    {
        $this->response->action = $a;
    }
    private function setResult($r): void
    {
        $this->response->result = $r;
    }

    private static function readUrlDate(string $name): int
    {
        $date = $_GET[$name] ?? '';
        $m = [];
        if (!preg_match('/^(\d{4})-(\d\d)-(\d\d)$/', $date, $m)) {
            return 0;
        }
        return mktime(12, 0, 0, (int) $m[2], (int) $m[3], (int) $m[1]);
    }
}
