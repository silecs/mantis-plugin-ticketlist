<?php

namespace ticketlist\api;

use DbQuery;
use ticketlist\HttpException;
use ticketlist\models\Liste;

/**
 * Response to PUT /list
 */
class PutList implements Action
{
    public int $httpCode = 200;

    public function run(Liste $toSave)
    {
        $tableName = plugin_table('persistent');
        db_query("CREATE UNIQUE INDEX IF NOT EXISTS persistent_name_u ON {$tableName} (`project_id`, `name`)");

        $warning = $this->validate($toSave);
        if ($warning) {
            return $warning;
        }
        return $this->save($toSave);
    }

    private static function save(Liste $toSave): array
    {
        if ($toSave->id > 0) {
            $message = "La modification est enregistrée sur le serveur.";
        } else {
            $message = "Cette nouvelle liste est enregistrée sur le serveur.";
        }
        if (!$toSave->save()) {
            throw new HttpException(500, "Erreur SQL lors de l'enregistrement.");
        }
        return [
            'status' => 'success',
            'message' => $message,
            'content' => $toSave,
        ];
    }

    private function validate(Liste $toSave): array
    {
        if (!\access_has_project_level(\config_get('view_summary_threshold'), $toSave->projectId)) {
            throw new HttpException(403, "Vous n'avez pas l'autorisation d'accéder au projet auquel appartient cette liste.");
        }
        if ($toSave->id) {
            try {
                $dbRecord = Liste::findByPk($toSave->id);
            } catch (\Throwable $e) {
                throw new HttpException(404, "Cette liste n'existe pas sur le serveur.");
            }
            if ($dbRecord->projectId !== $toSave->projectId) {
                throw new HttpException(400, "Wrong project ID.");
            }
            if ($dbRecord->lastUpdate !== $toSave->lastUpdate) {
                return [
                    'status' => 'need-confirm',
                    'message' => "Cette liste a été modifiée par ailleurs.",
                    'content' => $dbRecord,
                ];
            }
        }
        return [];
    }
}
