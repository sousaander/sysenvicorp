<?php

namespace App\Models;

use PDO;

class BensAtivosModel
{
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Busca dados resumidos de bens e ativos.
     */
    public function getAssetsSummary()
    {
        // Mock de dados resumidos:
        return [
            'totalAtivos' => 450,
            'valorTotalEstimado' => 1250000.00,
            'manutencaoPendente' => 12,
            'ativosDepreciacao' => 85,
        ];
    }

    /**
     * Busca uma lista de ativos que precisam de manutenção ou inspeção.
     */
    public function getAssetsMaintenanceList()
    {
        // Mock de lista de ativos:
        return [
            ['id' => 101, 'nome' => 'Servidor Principal (Rack 1)', 'local' => 'TI', 'proximaManutencao' => '25/11/2025', 'status' => 'Urgente'],
            ['id' => 205, 'nome' => 'Veículo VTR-03 (Hilux)', 'local' => 'Campo', 'proximaManutencao' => '05/12/2025', 'status' => 'Programada'],
            ['id' => 312, 'nome' => 'Máquina de Solda C-45', 'local' => 'Manutenção', 'proximaManutencao' => '10/01/2026', 'status' => 'Recomendada'],
        ];
    }
}
