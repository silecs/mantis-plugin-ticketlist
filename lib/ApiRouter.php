<?php

namespace ticketlist;

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
            $this->response->action->httpCode = $e->code;
            $this->setResult(['error' => $e->getMessage()]);
        } catch (\Throwable $e) {
            $this->response->action->httpCode = 500;
            $this->setResult(['error' => $e->getMessage()]);
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
                $this->setAction(new api\ListAction());
                return $this->response->action->run($id);
            }
            $this->setAction(new api\ListAllAction());
            return $this->response->action->run(Request::readProjectId());
        }

        if ($verb === 'PUT') {
            $this->setAction(new api\ListSaveAction());
            return $this->response->action->run(Request::readBody());
        }

        if ($verb === 'DELETE') {
            // TODO Implement DELETE /list
        }
    
        return $this->returnError(400, "This HTTP verb is not accepted for /list.");
    }

    private function dispatchProject()
    {
        if (Request::readRequestVerb() !== 'GET') {
            return $this->returnError(400, "The only HTTP verb accepted for /project is GET.");
        }
        $id = (int) ($_GET['id'] ?? '');
        if ($id <= 0) {
            return $this->returnError(400, "Missing parameter: id");
        }
        $this->setAction(new api\ProjectAction());
        return $this->response->action->run($id);
    }

    private function dispatchTicket()
    {
        if (Request::readRequestVerb() !== 'GET') {
            return $this->returnError(400, "The only HTTP verb accepted for /ticket is GET.");
        }
        $id = ($_GET['id'] ?? '');
        $ids = array_filter(array_map('intval', explode(',', $id)));
        if (!$ids) {
            return $this->returnError(400, "Missing parameter (comma separated integer list): id");
        }
        $this->setAction(new api\TicketAction());
        return $this->response->action->run($ids);
    }

    private function dispatchTicketTime()
    {
        if (Request::readRequestVerb() !== 'GET') {
            return $this->returnError(400, "The only HTTP verb accepted for /ticket is GET.");
        }
        $id = ($_GET['id'] ?? '');
        $ids = array_filter(array_map('intval', explode(',', $id)));
        if (!$ids) {
            return $this->returnError(400, "Missing parameter (comma separated integer list): id");
        }
        $this->setAction(new api\TicketTimeAction());
        return $this->response->action->run($ids, Request::readProjectId());
    }

    private function returnError(int $code, $message)
    {
        $error = new api\ErrorAction();
        $error->httpCode = $code;
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
}
