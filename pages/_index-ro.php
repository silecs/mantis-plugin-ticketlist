<?php

use ticketlist\models\Liste;

require_once dirname(__DIR__) . '/lib/models/Liste.php';

// Project
$projectId = helper_get_current_project();
if ($projectId === ALL_PROJECTS) {
    $projectId = (int) array_key_first(user_get_all_accessible_projects());
}
if (!$projectId) {
    die("No project selected");
}

// Lists
$tableName = plugin_table('persistent');
$query = new DbQuery("SELECT * FROM {$tableName} WHERE project_id = {$projectId} ORDER BY name ASC");
$lists = $query->fetch_all();

// Selected list
$listId = (int) ($_GET['id'] ?? 0);
if ($listId > 0) {
    $list = Liste::findByPk($listId);
    if ($list && $list->projectId !== $projectId) {
        $list = null;
    }
    $bugIds = join(",", array_filter(array_map('intval', preg_split('/[\s,]+/s', $list->ids))));
} else {
    $list = null;
    $bugIds = "0";
}

layout_page_header("Liste de tickets");
layout_page_begin();
?>
<h1>Listes de tickets</h1>
<div style="display: flex; flex-direction: horizontal; gap: 1ex;">
    <div id="lists-table" style="margin: 3px 0">
        <table class="table table-striped table-bordered table-condensed">
            <thead>
                <tr>
                    <th style="cursor: pointer;">Mir@bel2 - listes</th>
                    <th style="cursor: pointer;">Dernière modification</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $baseUrl = plugin_page('index');
            foreach ($lists as $row) {
                $l = new Liste($row);
                ?>
                <tr>
                    <td><a href="<?= $baseUrl . "&amp;id={$l->id}" ?>"><?= htmlspecialchars($l->name) ?></a></td>
                    <td><?= $l->lastUpdate ?></td>
                </tr>
                <?php
            }
            ?>    
            </tbody>
        </table>
    </div>
    <?php if ($list) { ?>
    <section id="issues-main-table" class="widget-box block tickets-block">
        <div class="widget-header widget-header-small">
            <h2><?= htmlspecialchars($list->name) ?></h2>
        </div>
        <div class="widget-body widget-main">
            <table class="buglist table table-bordered table-condensed table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>statut</th>
                        <th>résumé</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $toFrStatus = MantisEnum::getAssocArrayIndexedByValues(lang_get('status_enum_string'));
                $sql = <<<EOSQL
                    SELECT b.id, b.status, b.summary
                    FROM {bug} b
                    WHERE b.id IN ({$bugIds})
                    ORDER BY find_in_set(b.id, '{$bugIds}') ASC
                    EOSQL;
                $query = new DbQuery();
                $query->sql($sql);
                $rows = $query->fetch_all() ?: [];
                foreach ($rows as $row) {
                    ?>
                    <tr class="status-<?= $row['status'] ?>-bg">
                        <td><a href="/view.php?id=<?= $row['id'] ?>">5485</a></td>
                        <td><?= htmlspecialchars($toFrStatus[$row['status']] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['summary']) ?></td>
                    </tr>
                    <?php
                }
                ?>
                </tbody>
            </table>
            <div class="actions" style="text-align: right;"></div></div><div class="widget-toolbox padding-8" style="margin-top: 2em;">
                <?php
                $query = new DbQuery();
                $sql = <<<EOSQL
                    SELECT sum(n.time_tracking) AS total
                    FROM {bug} bug
                        JOIN {bugnote} n ON bug.id = n.bug_id
                    WHERE bug.id IN ({$bugIds}) AND bug.project_id = {$projectId}
                    EOSQL;
                $query->sql($sql);
                $rows = $query->fetch_all();
                ?>                
                <div>Temps total consacré à ces tickets : <?= db_minutes_to_hhmm((int) $rows[0]['total']) ?></div>
            </div>
        </div>
    </section>
    <?php } ?>
</div>
<?php
layout_page_end();
