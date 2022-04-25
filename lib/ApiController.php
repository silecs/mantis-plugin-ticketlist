<?php

namespace ticketlist;

class ApiController
{
    public function run(string $actionId): void
    {
        $router = new ApiRouter();
        $response = $router->run(trim($actionId, "/"));

        header("Cache-Control: no-store, no-cache, must-revalidate");
        header('Content-Type: application/json; charset="UTF-8"');
        if ($response->action->getHttpCode() !== 200) {
            http_response_code($response->action->getHttpCode());
        }
        echo json_encode($response->result);
    }
}
