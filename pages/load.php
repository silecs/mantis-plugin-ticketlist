<?php

access_ensure_project_level(config_get('view_summary_threshold'));

require_once dirname(__DIR__) . '/lib/Persistent.php';

$id = (int) ($_GET['id'] ?? 0);
if (!$id) {
    header("Location: " . plugin_page('list'));
    return;
}

$data = Persistent::find($id);
if (!$data) {
    header("Location: " . plugin_page('list'));
    return;
}

// AJAX
$accept = apache_request_headers()["Accept"] ?? '';
if (strpos($accept, 'application/json') !== false) {
    echo "true";
    return;
}

// HTTP redirection to a HTML page
$url = plugin_page('list')
    . '&ids=' . rawurlencode($data['ids'])
    . ($data['name'] ? "&title=" . rawurlencode($data['name']) : "")
    . "&keeporder=1";
header("Location: $url");
