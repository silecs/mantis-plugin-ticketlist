<?php

$apiController = new ticketlist\ApiController();
$apiController->run($_GET['action'] ?? '');
