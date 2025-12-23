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
        // Mock de dados resumidos:
        return [
            'totalAtivos' => 24, // PRADs em execução ou monitoramento
            'relatoriosVencendo30dias' => 5, // Próxima entrega de relatório/laudo em 30 dias
            'areasMonitoradas' => 125.5, // Área total (em hectares)
            'statusCritico' => 2, // PRADs com atraso no cronograma ou não conformidade no monitoramento
        ];
    }

    /**
     * Busca uma lista de PRADs com status crítico ou prazo de relatório iminente.
     */
    public function getCriticalPradList()
    {
        // Mock de lista de PRADs:
        return [
            ['id' => 'PRAD-2025/01', 'cliente' => 'Mineração Rocha Forte', 'localizacao' => 'MG', 'etapaAtual' => 'Monitoramento (Ano 3)', 'proximoRelatorio' => '15/11/2025', 'status' => 'Relatório Iminente'],
            ['id' => 'PRAD-2025/08', 'cliente' => 'Construtora Litoral', 'localizacao' => 'BA', 'etapaAtual' => 'Plantio', 'proximoRelatorio' => '30/10/2025', 'status' => 'Atrasado'],
            ['id' => 'PRAD-2025/12', 'cliente' => 'Fazenda Santa Maria', 'localizacao' => 'SP', 'etapaAtual' => 'Manutenção (Pós-plantio)', 'proximoRelatorio' => '20/01/2026', 'status' => 'Em Conformidade'],
            ['id' => 'PRAD-2025/19', 'cliente' => 'Prefeitura Municipal Z', 'localizacao' => 'RJ', 'etapaAtual' => 'Fechamento', 'proximoRelatorio' => '05/12/2025', 'status' => 'Relatório Iminente'],
        ];
    }
}
