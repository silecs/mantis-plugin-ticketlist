<?php

trait EventHooks
{
    public function onBeforeOutput(): void
    {
        global $g_bypass_headers;
        if ($this->isPluginRequested("index")) {
            $this->addHttpHeaders();
        }
        if ($this->isPluginRequested("api")) {
            // Hidden global setting, found by reverse engineering this junk of Mantis.
            $g_bypass_headers = true;
        }
    }

    /**
     * Add entries to the menu on the page "Summary".
     */
    public function onMenu(): array
    {
        return [
            [
                'title' => "Lister des tickets",
                'url' => plugin_page('index'),
                'access_level' => ANYBODY,
                'icon' => 'fa-list'
            ],
        ];
    }

    public function addHtmlHeadContent(): string
    {
        if (!$this->isPluginRequested("index")) {
            return '';
        }
        $cssPath = htmlspecialchars(plugin_file('ticketlist.css'));
        $jsPath = htmlspecialchars(plugin_file('main.js')); // json_encode(plugin_page('list'));
        $projectId = (int) helper_get_current_project();
        if ($projectId > 0) {
            $record = project_get_row($projectId);
            $project = ['id' => (int) $record['id'], 'name' => $record['name']];
        } else {
            $project = ['id' => 0, 'name' => "Tous les projets"];
        }
        $project['accessLevel'] = (int) access_get_project_level($project['id'], auth_get_current_user_id());
        $data = htmlspecialchars(json_encode($project), ENT_NOQUOTES);
        return <<<EOHTML
            <link rel="stylesheet" type="text/css" href="{$cssPath}" />
            <script id="ticket-list-data" type="application/json">{$data}</script>
            <script src="{$jsPath}"></script>
            EOHTML;
    }

    /**
     * Add Content Security Policy headers for our script.
     */
    protected function addHttpHeaders(): void
    {
        $hash = hash_file('sha256', __DIR__ . '/files/main.js');
        http_csp_add('script-src', "'sha256-{$hash}'");
    }

    protected function isPluginRequested(string $page = ''): bool
    {
        if (strpos($_SERVER['REQUEST_URI'], "plugin.php") === false) {
            return false;
        }
        $pageRequested = $_GET['page'] ?? '';
        if ($page) {
            return ($pageRequested === "TicketList/$page")
                || (strpos($pageRequested, "TicketList/$page/") === 0);
        } else {
            return (strncmp($page, 'TicketList', 10) === 0);
        }
    }
}
