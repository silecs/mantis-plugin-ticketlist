<?php
/*
 * @license http://www.gnu.org/licenses/gpl-3.0.html  GNU GPL v3
 */

access_ensure_global_level(config_get('manage_site_threshold'));

if (empty($_GET['ids'])) {
    $ids = [];
} else {
    $ids = array_filter(
        array_map(
            function($id) { return (int) preg_replace('/^\D*/', '', $id); },
            preg_split('/[,\n\s]+/', $_GET['ids'])
        )
    );

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
    $sql = "SELECT b.id, b.status, b.summary FROM {bug} b "
        . "WHERE id in (" . join(',', $ids) . ") ORDER BY id ASC";
    $result = db_query($sql);
    ?>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>status</th>
                <th>summary</th>
            </tr>
        </thead>
        <tbody>
    <?php
    foreach ($result as $row) {
        echo "<tr>"
            . "<td>" . string_get_bug_view_link($row['id'], null, false) . "</td>"
            . '<td bgcolor="' . get_status_color($row['status']) . '">' . get_enum_element('status', $row['status']) . "</td>"
            . "<td>" . string_display($row['summary']) . "</td>"
            . "</tr>";
    }
    ?>
        </tbody>
    </table>

    <h2>Non validés</h2>
    <?php
    $sql = "SELECT b.id, b.status, b.summary FROM {bug} b "
        . "WHERE id in (" . join(',', $ids) . ") AND status <> 85 ORDER BY id ASC";
    $result = db_query($sql);
    ?>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>status</th>
                <th>summary</th>
            </tr>
        </thead>
        <tbody>
    <?php
    foreach ($result as $row) {
        echo "<tr>"
            . "<td>" . string_get_bug_view_link($row['id'], null, false) . "</td>"
            . '<td bgcolor="' . get_status_color($row['status']) . '">' . get_enum_element('status', $row['status']) . "</td>"
            . "<td>" . string_display($row['summary']) . "</td>"
            . "</tr>";
    }
    ?>
        </tbody>
    </table>
    <?php
}
?>

<?php
html_page_bottom();
