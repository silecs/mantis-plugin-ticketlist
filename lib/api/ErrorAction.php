<?php

namespace ticketlist\api;

class ErrorAction implements Action
{
    public int $httpCode = 500;

    public function run($errorResponse)
    {
        return $errorResponse;
    }
}
