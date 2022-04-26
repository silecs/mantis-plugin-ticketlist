<?php

access_ensure_project_level(config_get('view_summary_threshold'));

layout_page_header("Liste de tickets");
layout_page_begin();
?>

<h1>Listes de tickets</h1>

<div id="ticketlist-container"></div>
<hr>

<?php
layout_page_end();
