<?php

access_ensure_project_level(config_get('view_summary_threshold'));

require_once dirname(__DIR__) . '/lib/Persistent.php';

$id = (int) ($_GET['id'] ?? 0);
if (!$id) {
    header("Location: " . plugin_page('list'));
    exit();
}

$data = Persistent::find($id);
if (!$data) {
    header("Location: " . plugin_page('list'));
    exit();
}

$url = plugin_page('list')
    . '&ids=' . rawurlencode($data['ids'])
    . ($data['title'] ? "&title=" . rawurlencode($data['title']) : "")
    . "&keeporder=1";
header("Location: $url");
exit();
