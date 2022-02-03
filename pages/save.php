<?php

access_ensure_project_level(config_get('view_summary_threshold'));

require_once dirname(__DIR__) . '/lib/Persistent.php';

$contentType = apache_request_headers()["Content-Type"] ?? '';
if (strpos($contentType, 'application/json') === false) {
    return;
}

$json = file_get_contents('php://input'); // read the HTTP request body
$data = json_decode($json, true);
if (empty($data['name']) || !isset($data['ids']) || !is_array($data['ids'])) {
    return;
}

$ids = array_filter(array_map('intval', $data['ids']));
if (empty($ids)) {
    Persistent::delete($data['name']);
} else {
    Persistent::save($data['name'], join(",", $data['ids']));
}
echo "true";
