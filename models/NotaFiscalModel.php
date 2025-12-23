<?php

namespace App\Models;

use App\Core\Model;
use PDO;
use PDOException;

class NotaFiscalModel extends Model
{
    public function __construct()
    {
        parent::__construct();
    }

    // Mock data for now, in a real app this would interact with a 'notas_fiscais' table
    private $mockNotas = [
        ['id' => 1, 'numero' => 'NF-2023-0001', 'data_emissao' => '2023-10-20', 'data_vencimento' => '2023-11-20', 'valor_total' => 1500.00, 'cliente' => 'Cliente A', 'status' => 'Emitida', 'observacoes' => 'Serviços de consultoria'],
        ['id' => 2, 'numero' => 'NF-2023-0002', 'data_emissao' => '2023-10-25', 'data_vencimento' => '2023-11-25', 'valor_total' => 250.50, 'cliente' => 'Cliente B', 'status' => 'Paga', 'observacoes' => 'Venda de produto'],
        ['id' => 3, 'numero' => 'NF-2023-0003', 'data_emissao' => '2023-11-01', 'data_vencimento' => '2023-12-01', 'valor_total' => 3000.00, 'cliente' => 'Cliente C', 'status' => 'Cancelada', 'observacoes' => 'Serviços não realizados'],
        ['id' => 4, 'numero' => 'NF-2023-0004', 'data_emissao' => '2023-11-10', 'data_vencimento' => '2023-12-10', 'valor_total' => 750.00, 'cliente' => 'Cliente D', 'status' => 'Emitida', 'observacoes' => 'Manutenção'],
    ];

    public function getAllNotasFiscais(): array
    {
        // In a real application, this would query the database
        return $this->mockNotas;
    }

    public function getNotaFiscalById(int $id): ?array
    {
        // In a real application, this would query the database
        foreach ($this->mockNotas as $nota) {
            if ($nota['id'] == $id) {
                return $nota;
            }
        }
        return null;
    }

    public function salvarNotaFiscal(array $dados): bool
    {
        // In a real application, this would insert/update into 'notas_fiscais' table
        // For mock, we just simulate success
        error_log("Simulando salvar/atualizar nota fiscal: " . json_encode($dados));
        return true;
    }

    public function excluirNotaFiscal(int $id): bool
    {
        // In a real application, this would delete from 'notas_fiscais' table
        // For mock, we just simulate success
        error_log("Simulando exclusão de nota fiscal com ID: " . $id);
        return true;
    }
}
