<?php

namespace ticketlist;

class Response
{
    public api\Action $action;

    public $result;

    public function __construct()
    {
        $this->action = new api\ErrorAction();
    }
}
