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
     * Init the plugin attributes.
     */
    function register()
    {
        $this->name = 'Ticket List';
        $this->description = "Plugin that displays the state of a list of tickets.";
        $this->page = 'list';

        $this->version = '1.0';
        $this->requires = [
            'MantisCore' => '1.3.0, < 2.0',
        ];

        $this->author = 'François Gannaz / Silecs';
        $this->contact = 'francois.gannaz@silecs.info';
        $this->url = '';
    }

    /**
     * Declare hooks on Mantis events.
     *
     * @return array
     */
    public function hooks()
    {
        return [
            'EVENT_MENU_SUMMARY' => 'onMenuSummary',
        ];
    }

    /**
     * Add entries to the menu on the page "Summary".
     *
     * @return array
     */
    public function onMenuSummary()
    {
        return [
            '<a href="' . plugin_page('list') . '">Lister des tickets</a>',
        ];
    }
}
