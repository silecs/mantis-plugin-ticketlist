<?php

require_once dirname(__DIR__) . '/lib/ApiController.php';

$apiController = new ApiController($_GET['action'] ?? '');
$apiController->run();
exit();
