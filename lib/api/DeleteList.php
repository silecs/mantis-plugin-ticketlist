<?php

namespace ticketlist\api;

use DbQuery;
use ticketlist\HttpException;
use ticketlist\models\Liste;

\require_api('authentication_api.php'); // auth_* functions

/**
 * Response to DELETE /list
 */
class DeleteList implements Action
{
    public int $httpCode = 200;

    public function run(Liste $toDelete)
    {
        $this->validate($toDelete);
        return $this->delete((int) $toDelete->id);
    }

    private function delete(int $id): array
    {
        $tableName = plugin_table('persistent');
        $query = new DbQuery("DELETE FROM {$tableName} WHERE id = $id");
        if ($query->execute() === false) {
            throw new HttpException(500, "Erreur SQL lors de la suppression.");
        }
        return [
            'status' => 'success',
            'message' => "La liste est supprimée sur le serveur.",
        ];
    }

    private function validate(Liste $toDelete): void
    {
        if (!\access_has_project_level(\config_get('view_summary_threshold'), $toDelete->projectId)) {
            throw new HttpException(403, "Vous n'avez pas l'autorisation d'accéder au projet auquel appartient cette liste.");
        }
        $dbRecord = Liste::findByPk($toDelete->id);
        if ($dbRecord->id === 0) {
            throw new HttpException(410, "La liste n'est pas sur le serveur, elle a déjà été supprimée.");
        }
        if ($dbRecord->projectId !== $toDelete->projectId) {
            throw new HttpException(400, "Wrong project ID.");
        }
        if ($dbRecord->lastUpdate !== $toDelete->lastUpdate) {
            throw new HttpException(409, "La liste du serveur est plus récente que la liste en cours. Rechargez la liste avant de la supprimer.");
        }
    }
}
