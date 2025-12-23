<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class LicencasOperacaoModel extends Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Busca dados resumidos sobre as licenças.
     */
    public function getLicensesSummary()
    {
        // Mock de dados resumidos:
        return [
            'totalLicencas' => 18,
            'vencimento30Dias' => 2, // Licenças que vencem nos próximos 30 dias
            'vencidas' => 0,
            'emRenovacao' => 3,
        ];
    }

    /**
     * Busca uma lista de licenças críticas (vencimento próximo ou status especial).
     */
    public function getCriticalLicensesList()
    {
        // Mock de lista de licenças:
        return [
            ['id' => 'LA-001', 'nome' => 'Licença Ambiental Principal', 'orgao' => 'IBAMA', 'vencimento' => '20/12/2025', 'status' => 'Pendente Renovação'],
            ['id' => 'LS-015', 'nome' => 'Licença Sanitária Sede', 'orgao' => 'ANVISA', 'vencimento' => '05/11/2025', 'status' => 'Vencendo'],
            ['id' => 'LFG-112', 'nome' => 'Licença de Funcionamento Geral', 'orgao' => 'Prefeitura', 'vencimento' => '15/07/2026', 'status' => 'Ativa'],
            ['id' => 'CT-023', 'nome' => 'Certificado Técnico', 'orgao' => 'CREA', 'vencimento' => '20/01/2026', 'status' => 'Ativa'],
        ];
    }
}
