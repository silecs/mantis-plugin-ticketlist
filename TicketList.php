<?php

require_once __DIR__ . '/EventHooks.php';

/**
 * Overloads some of the parent's methods in order to define the plugin.
 *
 * @author François Gannaz <francois.gannaz@silecs.info>
 */
class TicketListPlugin extends MantisPlugin
{
    use EventHooks;

    /**
     * Init the plugin attributes.
     */
    public function register()
    {
        $this->name = 'Ticket List';
        $this->description = "Plugin that displays the state of a list of issues.";
        $this->page = 'index';

        $this->version = '2.1';
        $this->requires = [
            'MantisCore' => '2.0.0',
        ];

        $this->author = 'François Gannaz / Silecs';
        $this->contact = 'francois.gannaz@silecs.info';
        $this->url = '';
    }

    public function init()
    {
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
        // See the trait EventHooks for the hook methods.
        return [
            'EVENT_CORE_HEADERS' => 'onBeforeOutput',
            'EVENT_MENU_MAIN' => 'onMenu',
            'EVENT_LAYOUT_RESOURCES' => 'addHtmlHeadContent',
        ];
    }

    /**
     * Define the SQL schema of new tables.
     *
     * This is not mentionned in the official documentation,
     * but appears in the parent class, MantisPlugin.
     *
     * I guess it is called at install time, and not for upgrades.
     * But I haven't reverse engineered its usage.
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
                ],
            ],
            [
                'CreateIndexSQL',
                ['persistent_name_u', plugin_table('persistent'), 'project_id, name', ['UNIQUE']],
            ]
        ];
    }
}
