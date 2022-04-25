<?php

namespace ticketlist\api;

use DbQuery;

use ticketlist\HttpException;

/**
 * Response to GET /project/4
 */
class GetProject extends Action
{
    public function run(int $id)
    {
        $query = new DbQuery();
        $query->sql("SELECT id, name FROM {project} WHERE id = {$id}");
        $row = $query->fetch();

        if (!$row) {
            throw new HttpException(404, "Liste non trouvée.");
        }
        return $row;
    }
}
