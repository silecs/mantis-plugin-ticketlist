<?php
/*
 * @license http://www.gnu.org/licenses/gpl-3.0.html  GNU GPL v3
 */

access_ensure_project_level(config_get('view_summary_threshold'));

$projectId = helper_get_current_project();
$isAdmin = (bool) access_has_global_level(config_get('manage_site_threshold'));

$data = parseQueryParameters($_GET ?? []);

// redirect to a cleaner URL
if ($data['ids'] && strpos($_GET['ids'], "\n") !== false) {
    $url = plugin_page('list')
        . '&ids=' . join(',', $data['ids'])
        . ($data['title'] ? "&title=" . rawurlencode($data['title']) : "")
        . ($data['keepOrder'] ? "&keeporder=1" : "");
    header("Location: $url");
    exit();
}

$sqlSort = $data['keepOrder'] ?
    " ORDER BY find_in_set(b.id, '" . join(",", $data['ids']) . "') ASC"
    : " ORDER BY b.id ASC";

layout_page_header($data['title'] ? "tickets {$data['title']}" : "tickets list");
layout_page_begin();
?>
<h1>
    Liste de tickets <em><?= htmlspecialchars($data['title'] ?: '') ?></em>
</h1>

<div class="blocks-container">

<section class="widget-box widget-color-blue2 block" id="select-tickets">
    <div class="widget-header widget-header-small">
        <h2>Sélection</h2>
    </div>
    <div class="widget-body widget-main">
        <form action="<?= plugin_page('list') ?>" method="get" class="form">
            <input type="hidden" name="page" value="TicketList/list" />
            <div class="form-group">
                <label class="control-label">Tickets #</label>
                <textarea class="form-control" name="ids" cols="10" rows="20" placeholder="un numéro par ligne"><?= htmlspecialchars(join("\n", $data['ids'])); ?></textarea>
            </div>
            <div class="form-group">
                <label class="control-label">Titre de la liste</label>
                <input class="form-control" type="text" name="title" value="<?= htmlspecialchars($data['title']) ?>" />
            </div>
            <div class="checkbox">
                <label>
                    <input type="checkbox" name="keeporder" value="1" <?= $data['keepOrder'] ? 'checked="checked"' : "" ?> />
                    Conserver l'ordre des tickets
                </label>
            </div>
            <button type="submit" class="btn btn-default">OK</button>
        </form>
    </div>
</section>

<?php
if ($data['ids']) {
    ?>
    <section class="widget-box block">
        <div class="widget-header widget-header-small">
            <h2>Tickets listés (<?= count($data['ids']) ?>)</h2>
        </div>
        <div class="widget-body widget-main">
            <form method="post" action="bug_actiongroup_page.php">
                <?php
                $sql = "SELECT b.id, b.status, b.summary FROM {bug} b"
                    . " WHERE b.id in (" . join(',', $data['ids']) . ")"
                    . ($isAdmin ? "" : " AND b.project_id = " . (int) $projectId)
                    . $sqlSort;
                echo tableOfTickets(db_query($sql), $isAdmin);
                ?>
                <?php if ($isAdmin) { ?>
                <input type="hidden" name="action" value="CLOSE" />
                <input type="checkbox" class="checkall" />
                <button type="submit">Fermer les tickets sélectionnés</button>
                <?php } ?>
            </form>
            <?php
            $sql = "SELECT sum(time_tracking) AS totaltime FROM bugnote WHERE bug_id IN (" . join(',', $data['ids']) . ")";
            $totaltime = (int) db_result(db_query($sql));
            if ((int) helper_get_current_project() === 28) {
                $totaltimeSinceUpgrade = (int) db_result(db_query($sql . " AND date_submitted > (SELECT MAX(date_submitted) FROM bugnote WHERE bug_id = 1875)"));
            }
            ?>
        </div>
        <div class="widget-toolbox padding-8" style="margin-top: 2em">
            Temps total consacré à ces tickets : <strong><?= db_minutes_to_hhmm($totaltime) ?></strong>
            <?= $totaltime && isset($totaltimeSinceUpgrade) ? " dont <strong>" . db_minutes_to_hhmm($totaltimeSinceUpgrade) . "</strong> depuis la dernière note dans #1875 (montée de version)" : "" ?>
        </div>
    </section>

    <section class="widget-box block">
        <div class="widget-header widget-header-small">
            <h2>Non validés</h2>
        </div>
        <div class="widget-body widget-main">
            <?php
            $sql = "SELECT b.id, b.status, b.summary FROM {bug} b "
                . "WHERE b.id in (" . join(',', $data['ids']) . ") AND b.status NOT IN (85, 90)"
                . ($isAdmin ? "" : " AND b.project_id = " . (int) $projectId)
                . $sqlSort;
            echo tableOfTickets(db_query($sql));
            ?>
        </div>
    </section>

    <section class="widget-box block">
        <div class="widget-header widget-header-small">
            <h2>Non finis</h2>
        </div>
        <div class="widget-body widget-main">
            <?php
            $sql = "SELECT b.id, b.status, b.summary FROM {bug} b "
                . "WHERE b.id in (" . join(',', $data['ids']) . ") AND b.status < 80"
                . ($isAdmin ? "" : " AND b.project_id = " . (int) $projectId)
                . $sqlSort;
            echo tableOfTickets(db_query($sql));
            ?>
        </div>
    </section>
    <?php
}
?>

</div>

<?php
layout_page_end();

function tableOfTickets($rows, $selectable = false) {
    if (db_num_rows($rows) === 0) {
        return "<p>Aucun.</p>";
    }
    $htmlSel = ($selectable ? "<th></th>" : "");
    $html = <<<"EOHTML"
<table class="buglist table table-bordered table-condensed table-hover">
    <thead>
        <tr>
            $htmlSel
            <th>ID</th>
            <th>status</th>
            <th>summary</th>
        </tr>
    </thead>
    <tbody>
EOHTML;
    foreach ($rows as $row) {
        $html .= '<tr class="status-' . $row['status'] . '-bg">'
            . ($selectable ? '<td><input type="checkbox" name="bug_arr[]" value="' . (int) $row['id'] . '"></td>' : "")
            . "<td>" . string_get_bug_view_link($row['id'], null, false) . "</td>"
            . '<td>' . get_enum_element('status', $row['status']) . "</td>"
            . "<td>" . string_display($row['summary']) . "</td>"
            . "</tr>";
    }
    $html .= "
    </tbody>
</table>
";
    return $html;
}

function parseQueryParameters(array $in): array
{
    if (empty($in['ids'])) {
        $ids = [];
    } else {
        $ids = array_filter(
            array_map(
                function($id) { return (int) preg_replace('/^\D*/', '', $id); },
                preg_split('/[,\n\s]+/', $in['ids'])
            )
        );
    }

    return [
        'ids' => $ids,
        'title' => $in['title'] ?? "",
        'keepOrder' => !empty($in['keeporder']),
    ];
}