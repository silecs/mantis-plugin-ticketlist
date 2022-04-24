<?php

namespace ticketlist\api;

use DbQuery;

/**
 * Response to GET /project/4
 */
class GetProject implements Action
{
    public int $httpCode = 200;

    public function run(int $id)
    {
        $query = new DbQuery();
        $query->sql("SELECT id, name FROM {project} WHERE id = {$id}");
        $row = $query->fetch();

        if (!$row) {
            http_response_code(404);
            $row = null;
        }
        return $row;
    }
}
