<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class PopsModel extends Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Busca dados resumidos sobre os POPs.
     */
    public function getPopsSummary()
    {
        // Mock de dados resumidos:
        return [
            'totalPops' => 65,
            'emRevisao' => 7,
            'expirados' => 1, // POPs que passaram do prazo de revisão
            'novosMes' => 5,
        ];
    }

    /**
     * Busca uma lista de POPs críticos (em revisão ou expirados).
     */
    public function getCriticalPopsList()
    {
        // Mock de lista de POPs:
        return [
            ['id' => 'POP-001', 'titulo' => 'Procedimento de Descarte de Resíduos Químicos', 'setor' => 'Operacional', 'ultimaRevisao' => '15/01/2024', 'proximaRevisao' => '15/01/2026', 'status' => 'Expirado'],
            ['id' => 'POP-012', 'titulo' => 'Checklist de Inspeção de Campo (Fase II)', 'setor' => 'Projetos', 'ultimaRevisao' => '10/05/2025', 'proximaRevisao' => '10/05/2026', 'status' => 'Em Revisão'],
            ['id' => 'POP-045', 'titulo' => 'Processo de Contratação de Fornecedores', 'setor' => 'RH/Financeiro', 'ultimaRevisao' => '01/08/2025', 'proximaRevisao' => '01/08/2026', 'status' => 'Ativo'],
            ['id' => 'POP-060', 'titulo' => 'Instruções de Segurança em Altura', 'setor' => 'Segurança', 'ultimaRevisao' => '20/09/2025', 'proximaRevisao' => '20/09/2026', 'status' => 'Ativo'],
        ];
    }
}
