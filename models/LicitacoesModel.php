<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class LicitacoesModel extends Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Busca dados resumidos sobre as licitações.
     */
    public function getLicitacoesSummary()
    {
        // Mock de dados resumidos:
        return [
            'totalLicitacoes' => 45,
            'emAndamento' => 8, // Licitações com propostas enviadas ou em análise
            'propostasVencer' => 4, // Propostas que precisam ser enviadas em breve
            'ganhasMes' => 2,
        ];
    }

    /**
     * Busca uma lista de licitações com prazos críticos (próximas datas de envio/abertura).
     */
    public function getCriticalLicitacoesList()
    {
        // Mock de lista de licitações:
        return [
            ['id' => 'LC-2025/11', 'objeto' => 'Serviços de consultoria ambiental', 'cliente' => 'Prefeitura de SP', 'prazoEnvio' => '05/11/2025', 'status' => 'Proposta Pendente'],
            ['id' => 'CP-2025/08', 'objeto' => 'Fornecimento de equipamentos de medição', 'cliente' => 'Petrocorp S.A.', 'prazoEnvio' => '12/11/2025', 'status' => 'Em Análise'],
            ['id' => 'CC-2026/01', 'objeto' => 'Construção de estação de tratamento', 'cliente' => 'Secretaria Estadual', 'prazoEnvio' => '20/01/2026', 'status' => 'Proposta Enviada'],
            ['id' => 'PR-2025/09', 'objeto' => 'Projeto de reflorestamento área X', 'cliente' => 'Cia. Mineração', 'prazoEnvio' => '10/12/2025', 'status' => 'Em Elaboração'],
        ];
    }
}
