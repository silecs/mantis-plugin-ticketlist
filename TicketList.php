<?php

/**
 * @author François Gannaz <francois.gannaz@silecs.info>
 */
class TicketListPlugin extends MantisPlugin
{
    /**
     * @var string
     */
    public $nonce;

    /**
     * Init the plugin attributes.
     */
    public function register()
    {
        $this->name = 'Ticket List';
        $this->description = "Plugin that displays the state of a list of tickets.";
        $this->page = 'list';

        $this->version = '2.1';
        $this->requires = [
            'MantisCore' => '2.0.0',
        ];

        $this->author = 'François Gannaz / Silecs';
        $this->contact = 'francois.gannaz@silecs.info';
        $this->url = '';

        $this->nonce = crypto_generate_uri_safe_nonce(16);

        // Autoload classes whose FQDN is ticketlist\**
        spl_autoload_register(function ($className) {
            if (strncmp($className, 'ticketlist\\', 11) === 0) {
                $path = str_replace('\\', '/', substr($className, 11));
                require __DIR__ . "/lib/{$path}.php";
            }
        });
    }

    /**
     * Declare hooks on Mantis events.
     *
     * @return array
     */
    public function hooks()
    {
        // Event hooks must be public methods of this plugin object.
        // They will be called from an external function.
        return [
            'EVENT_CORE_HEADERS' => 'onBeforeOutput',
            'EVENT_MENU_MAIN' => 'onMenu',
            'EVENT_LAYOUT_RESOURCES' => 'addHtmlHeadContent',
        ];
    }

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
     * Undocumented method that defines the SQL schema of new tables.
     */
    public function schema() {
        return [
            // operations
            [
                // first operation
                'CreateTableSQL',
                [
                    plugin_table('persistent'), // the table name
                    <<<EOTEXT
                    id                 I       NOTNULL UNSIGNED AUTOINCREMENT PRIMARY,
                    project_id         I       DEFAULT NULL UNSIGNED,
                    name               C(255)  NOTNULL DEFAULT '',
                    ids                C(255)  NOTNULL DEFAULT '',
                    history            XL      NOTNULL,
                    author_id          I       DEFAULT NULL UNSIGNED,
                    last_update        T
                    EOTEXT
                ]
            ],
        ];
    }

    /**
     * Add Content Security Policy headers for our script.
     */
    private function addHttpHeaders(): void
    {
        $hash = hash_file('sha256', __DIR__ . '/files/main.js');
        http_csp_add('script-src', "'sha256-{$hash}'");
    }

    private function isPluginRequested(string $page = ''): bool
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
