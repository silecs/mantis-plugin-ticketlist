<?php

use ticketlist\ActionOnIssue;
use ticketlist\IssueList;

require_api('lang_api.php');
require_api('version_api.php');

auth_ensure_user_authenticated();
access_ensure_project_level(config_get('manage_site_threshold'));
helper_begin_long_process();

if (isset($_POST['ids'])) {
    $issues = IssueList::fromString($_POST['ids']);
} elseif (isset($_GET['ids'])) {
    $issues = IssueList::fromString($_GET['ids']);
} else {
    echo "The bug list is missing. Go away.";
    return;
}
$projectIds = $issues->getProjectIds();
if (!$projectIds) {
    echo "The bug list is missing. Go away.";
    return;
}

if (!empty($_POST['confirm'])) {
    $action = new ActionOnIssue();
    $action->setClose(true);
    if (count($projectIds) === 1) {
        // A single project is shared by all the issues.
        $fixedInVersion = $_POST['fixedinversion'] ?? "";
        if ($fixedInVersion) {
            $versionId = (int) version_get_id($fixedInVersion, $projectIds[0]);
            if (!$versionId) {
                $versionId = (int) version_add($projectIds[0], $fixedInVersion, VERSION_RELEASED);
            }
            if ($versionId > 0) {
                $action->setFixedInVersion($fixedInVersion);
            }
        }
    }

    $issues->applyBulkAction($action);
    if ($issues->getFailedActions()) {
        bug_group_action_print_top();
        bug_group_action_print_results($issues->getFailedActions());
        bug_group_action_print_bottom();
    } else {
        print_header_redirect(plugin_page('index') . '&ids=' . $issues->listBugIds());
    }
    return;
}
?>

<?php bug_group_action_print_top(); ?>

<div class="col-md-12 col-xs-12">
    <?php
    if (count($projectIds) > 1) {
        echo '<div class="alert alert-warning"> <p class="bold">' . lang_get('multiple_projects') . '</p> </div>';
    }
    ?>
    <div id="action-group-div" class="form-container">
        <form method="post" action="<?= plugin_page('close') ?>">
            <input type="hidden" name="ids" value="<?= $issues->listBugIds() ?>" />
            <div class="widget-box widget-color-blue2">
                <div class="widget-header widget-header-small">
                    <h4 class="widget-title lighter">
                    <?= lang_get('close_bugs_conf_msg') ?>
                    </h4>
                </div>
                <div class="widget-body">
                    <?php if (count($projectIds) === 1) { ?>
                    <div class="widget-toolbox padding-8 clearfix">
                        <?= lang_get('fixed_in_version_bugs_conf_msg') ?>
                        <input type="text" name="fixedinversion" value="<?= date('Y-m-d') ?>" />
                    </div>
                    <?php } ?>
                    <div class="widget-toolbox padding-8 clearfix">
                        <button type="submit" class="btn btn-primary btn-white btn-round" name="confirm" value="1">
                            <?= lang_get('close_group_bugs_button') ?>
                        </button>
                    </div>
                    <div class="widget-main no-padding">
                        <div class="table-responsive">
                            <table class="table table-bordered table-condensed table-striped">
                                <tbody>
                                    <tr class="spacer"></tr>
                                    <?php bug_group_action_print_bug_list($issues->getBugIds()); ?>
                                    <tr class="spacer"></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<?php bug_group_action_print_bottom(); ?>
