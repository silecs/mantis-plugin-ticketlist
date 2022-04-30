<?php

namespace ticketlist;

use BugData;

require_api('access_api.php'); // access_* functions
require_api('bug_api.php');
require_api('config_api.php');
require_api('email_api.php');
require_api('helper_api.php');

class ActionOnIssue
{
    /**
     * @var bool
     */
    private $close = false;

    /**
     * @var string
     */
    private $fixedInVersion = '';

    private $bugStatusClosed = 0;
    private $updateBugThreshold = 0;
    private $resolvedBugThreshold = 0;
    private $lastFailure = '';

    public function __construct()
    {
        $this->bugStatusClosed = config_get('bug_closed_status_threshold');
        $this->updateBugThreshold = config_get('update_bug_threshold');
        $this->resolvedBugThreshold = config_get('bug_resolved_status_threshold');
    }

    public function setClose(bool $close)
    {
        $this->close = $close;
    }

    public function setFixedInVersion(string $versionName)
    {
        $this->fixedInVersion = $versionName;
    }

    public function applyTo(BugData $bug): bool
    {
        $this->lastFailure = '';
        $this->fixGlobalState($bug);
        return $this->updateFixedInVersion($bug) && $this->close($bug);
    }

    public function getLastFailure(): string
    {
        return $this->lastFailure;
    }

    private function fixGlobalState(BugData $bug): void
    {
        if ($bug->project_id != helper_get_current_project()) {
            // Mantis has global state that needs many arcane workarounds.
            global $g_project_override;
            $g_project_override = $bug->project_id;
            config_flush_cache();
        }
    }

    private function close(BugData $bug): bool
    {
        if (!$this->close) {
            return true;
        }
        if (!access_can_close_bug($bug)) {
            $this->lastFailure = 'bug_actiongroup_access';
            return false;
        }
        if ($bug->status >= $this->bugStatusClosed || !bug_check_workflow($bug->status, $this->bugStatusClosed)) {
            $this->lastFailure = 'bug_actiongroup_status';
            return false;
        }
        bug_close($bug->id, "", false);
        return true;
    }

    private function updateFixedInVersion(BugData $bug): bool
    {
        if ($this->fixedInVersion === '') {
            return true;
        }
        if (!access_has_bug_level($this->updateBugThreshold, $bug->id)) {
            $this->lastFailure = 'bug_actiongroup_access';
            return false;
        }
        if ($bug->status < $this->resolvedBugThreshold) {
            bug_set_field($bug->id, 'status', $this->resolvedBugThreshold);
        }
        bug_set_field($bug->id, 'fixed_in_version', $this->fixedInVersion);
        return true;
    }

    private function notify(BugData $bug): bool
    {
        email_bug_updated($bug->id);
        helper_call_custom_function('issue_update_notify', [$bug->id]);
        return true;
    }
}
