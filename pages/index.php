<?php

access_ensure_project_level(config_get('view_summary_threshold'));

layout_page_header("Liste de tickets");
layout_page_begin();
?>

<h1>Listes de tickets</h1>

<div id="ticketlist-container"></div>
<noscript>Cette page ne fonctionne pas si JavaScript est désactivé dans le navigateur.</noscript>

<?php
layout_page_end();
