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
    function register()
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
        return [
            'EVENT_CORE_HEADERS' => 'addHttpHeaders',
            'EVENT_MENU_MAIN' => 'onMenu',
            'EVENT_LAYOUT_RESOURCES' => 'addHtmlHeadContent',
        ];
    }

    /**
     * Add Content Security Policy headers for our script.
     */
    function addHttpHeaders(): void
    {
        http_csp_add('script-src', "'nonce-{$this->nonce}'");
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

    function addHtmlHeadContent(): string
    {
        $page = $_GET['page'] ?? '';
        if ($page !== 'TicketList/list') {
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
}
