<?php

auth_ensure_user_authenticated();

if (access_has_project_level(config_get('view_summary_threshold'))) {
    require __DIR__ . '/_index-rw.php';
} else {
    require __DIR__ . '/_index-ro.php';
}
