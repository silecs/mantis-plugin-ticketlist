<?php

require_once __DIR__ . '/ActionOnIssue.php';

require_api('access_api.php'); // access_* functions
require_api('bug_api.php'); // bug_* functions

class IssueList
{
    private $bugs = [];

    /**
     * @var int[] bug.id
     */
    private $failures = [];

    protected function  __construct(array $bugIds)
    {
        $this->loadIssuesFromIds($bugIds);
    }

    public static function fromArray(array $bugIds)
    {
        $bugIds = array_filter(array_map('intval', $bugIds));
        return new self($bugIds);
    }

    public static function fromString(string $idlist)
    {
        $bugIds = array_filter(array_map('intval', preg_split('/[\s,]+/s', $idlist)));
        return new self($bugIds);
    }

    /**
     * @return int[]
     */
    public function getBugIds(): array
    {
        $ids = [];
        foreach ($this->bugs as $bug) {
            $ids[] = $bug->id;
        }
        sort($ids, SORT_NUMERIC);
        return $ids;
    }

    /**
     * @return int[]
     */
    public function getProjectIds(): array
    {
        $pids = [];
        foreach ($this->bugs as $bug) {
            $pids[$bug->project_id] = true;
        }
        return array_keys($pids);
    }

    public function listBugIds(): string
    {
        return join(",", $this->getBugIds());
    }

    public function applyBulkAction(ActionOnIssue $action): void
    {
        foreach ($this->bugs as $bug) {
            if (!$action->applyTo($bug)) {
                $this->failures[$bug->id] = lang_get($action->getLastFailure());
            }
        }
    }

    public function getFailedActions(): array
    {
        return $this->failures;
    }

    private function loadIssuesFromIds(array $bugIds): void
    {
        bug_cache_array_rows($bugIds);
        foreach ($bugIds as $id) {
            bug_ensure_exists($id);
            access_ensure_bug_level(config_get('view_bug_threshold'), $id);
            $this->bugs[$id] = bug_get($id, true);
        }
    }
}
