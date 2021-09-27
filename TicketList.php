<?php
/**
 * @license http://www.gnu.org/licenses/gpl-3.0.html  GNU GPL v3
 */

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

        $this->version = '2.0';
        $this->requires = [
            'MantisCore' => '2.0.0',
        ];

        $this->author = 'François Gannaz / Silecs';
        $this->contact = 'francois.gannaz@silecs.info';
        $this->url = '';

        $this->nonce = crypto_generate_uri_safe_nonce(16);
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
        if ($this->isPluginRequested("list")) {
            $this->addHttpHeaders();
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
                'url' => plugin_page('list'),
                'access_level' => ANYBODY,
                'icon' => 'fa-list'
            ],
        ];
    }

    public function addHtmlHeadContent(): string
    {
        if (!$this->isPluginRequested("list")) {
            return '';
        }
        $cssPath = plugin_file('ticketlist.css');
        return <<<EOHTML
<link rel="stylesheet" type="text/css" href="{$cssPath}" />
<script type="text/javascript" nonce="{$this->nonce}">
window.addEventListener('load', function() {
    var ca = document.querySelector('input.checkall')
    if (ca === null) {
        return;
    }
    ca.addEventListener(
        'click',
        function(e) {
            e.target.parentNode
                .querySelectorAll('input[type=checkbox][value]')
                .forEach(function(c) {
                    c.click();
                });
        }
    );
});
</script>
EOHTML
        ;
    }

    /**
     * Add Content Security Policy headers for our script.
     */
    private function addHttpHeaders(): void
    {
        http_csp_add('script-src', "'nonce-{$this->nonce}'");
    }

    private function isPluginRequested(string $page = ''): bool
    {
        if (strpos($_SERVER['REQUEST_URI'], "plugin.php") === false) {
            return false;
        }
        $pageRequested = $_GET['page'] ?? '';
        if ($page) {
            return ($pageRequested === "TicketList/$page");
        } else {
            return (strncmp($page, 'TicketList', 10) === 0);
        }
    }
}
