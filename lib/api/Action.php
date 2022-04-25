<?php

namespace ticketlist\api;

abstract class Action
{
    protected int $httpCode = 200;

    public function getHttpCode(): int
    {
        return $this->httpCode;
    }
}
