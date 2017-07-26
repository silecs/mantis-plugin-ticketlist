<?php
/*
 * @license http://www.gnu.org/licenses/gpl-3.0.html  GNU GPL v3
 */

access_ensure_project_level(config_get('view_summary_threshold'));

$projectId = helper_get_current_project();
$isAdmin = (boolean) access_has_global_level(config_get('manage_site_threshold'));

if (empty($_GET['ids'])) {
    $ids = [];
} else {
    $ids = array_filter(
        array_map(
            function($id) { return (int) preg_replace('/^\D*/', '', $id); },
            preg_split('/[,\n\s]+/', $_GET['ids'])
        )
    );
    // redirect to a cleaner URL
    if ($ids && strpos($_GET['ids'], "\n") !== false) {
        header("Location: " . plugin_page('list') . '&ids=' . join(',', $ids));
        exit();
    }
}


html_page_top();
?>
<h1>
    Liste de tickets
</h1>

<div>
    <form action="<?= plugin_page('list') ?>" method="get">
        <p>
            <input type="hidden" name="page" value="TicketList/list" />
            <label>Tickets #</label><br />
            <textarea name="ids" cols="10" rows="20"><?= htmlspecialchars(join("\n", $ids)); ?></textarea>
            <button type="submit">OK</button>
        </p>
    </form>
</div>

<?php
if ($ids) {
    ?>
    <p>
        <a href="<?= plugin_page('list') ?>&amp;ids=<?= join(',', $ids) ?>">lien vers cette page</a>
    </p>

    <h2>Tickets listés</h2>
    <form method="post" action="bug_actiongroup_page.php">
    <?php
    $sql = "SELECT b.id, b.status, b.summary FROM {bug} b"
        . " WHERE b.id in (" . join(',', $ids) . ")"
        . ($isAdmin ? "" : " AND b.project_id = " . (int) $projectId)
        . " ORDER BY b.id ASC";
    echo tableOfTickets(db_query($sql), true);
    ?>
        <input type="hidden" name="action" value="CLOSE" />
        <button type="submit">Fermer les tickets sélectionnés</button>
    </form>

    <h2>Non validés</h2>
    <?php
    $sql = "SELECT b.id, b.status, b.summary FROM {bug} b "
        . "WHERE b.id in (" . join(',', $ids) . ") AND b.status <> 85"
        . ($isAdmin ? "" : " AND b.project_id = " . (int) $projectId)
        . " ORDER BY b.id ASC";
    echo tableOfTickets(db_query($sql));
    ?>

    <h2>Non finis</h2>
    <?php
    $sql = "SELECT b.id, b.status, b.summary FROM {bug} b "
        . "WHERE b.id in (" . join(',', $ids) . ") AND b.status < 80"
        . ($isAdmin ? "" : " AND b.project_id = " . (int) $projectId)
        . " ORDER BY b.id ASC";
    echo tableOfTickets(db_query($sql));
}
?>

<?php
html_page_bottom();

function tableOfTickets($rows, $selectable = false) {
    if (db_num_rows($rows) === 0) {
        return "<p>Aucun.</p>";
    }
    $html = "
<table>
    <thead>
        <tr>" . ($selectable ? "<th></th>" : "") . "
            <th>ID</th>
            <th>status</th>
            <th>summary</th>
        </tr>
    </thead>
    <tbody>
";
    foreach ($rows as $row) {
        $html .= "<tr>"
            . ($selectable ? '<td><input type="checkbox" name="bug_arr[]" value="' . (int) $row['id'] . '"></td>' : "")
            . "<td>" . string_get_bug_view_link($row['id'], null, false) . "</td>"
            . '<td bgcolor="' . get_status_color($row['status']) . '">' . get_enum_element('status', $row['status']) . "</td>"
            . "<td>" . string_display($row['summary']) . "</td>"
            . "</tr>";
    }
    $html .= "
    </tbody>
</table>
";
    return $html;
}
