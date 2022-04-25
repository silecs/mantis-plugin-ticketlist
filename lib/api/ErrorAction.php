<?php

namespace ticketlist\api;

class ErrorAction extends Action
{
    public function __construct(int $code = 500)
    {
        $this->httpCode = $code;
    }

    public function run($errorResponse)
    {
        return $errorResponse;
    }
}
