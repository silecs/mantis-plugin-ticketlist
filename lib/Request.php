<?php

namespace ticketlist;

class Request
{
    public static function readBody()
    {
        $raw = file_get_contents('php://input');
        return \json_decode($raw, true, 8, JSON_THROW_ON_ERROR);
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
        if (!\access_has_project_level(\config_get('view_summary_threshold'), $projectId)) {
            throw new HttpException(403, "Vous n'avez pas l'autorisation d'accéder à ce projet.");
        }
        return $projectId;
    }
}
