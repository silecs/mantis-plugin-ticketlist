<?php

namespace ticketlist;

class Request
{
    public static function readBody(): string
    {
        $raw = file_get_contents('php://input');
        return \json_decode($raw, false, 8, JSON_THROW_ON_ERROR);
    }

    public static function readRequestVerb(): string
    {
        return strtoupper(
            $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] ?? $_SERVER['REQUEST_METHOD'] ?? 'GET'
        );
    }

    public static function readProjectId(): int
    {
        if (!isset($_GET['projectId'])) {
            throw new HttpException(400, "Parameter 'projectId' is missing.");
        }
        $projectId = (int) $_GET['projectId'];
        access_ensure_project_level(config_get('view_summary_threshold'), $projectId);
        return $projectId;
    }
}
