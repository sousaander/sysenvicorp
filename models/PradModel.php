<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class PradModel extends Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Busca dados resumidos sobre a carteira de Planos de Recuperação.
     */
    public function getPradSummary()
    {
        return [
            'totalAtivos' => 0,
            'relatoriosVencendo30dias' => 0,
            'areasMonitoradas' => 0.0,
            'statusCritico' => 0,
        ];
    }

    /**
     * Busca uma lista de PRADs com status crítico ou prazo de relatório iminente.
     */
    public function getCriticalPradList(): array
    {
        return [];
    }
}
