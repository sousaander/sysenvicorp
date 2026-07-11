<?php

namespace App\Models;

use App\Core\Model;

class FiscalModel extends Model
{
    private function tableExists(string $table): bool
    {
        try {
            $this->db->query("SELECT 1 FROM {$table} LIMIT 0");
            return true;
        } catch (\PDOException) {
            return false;
        }
    }

    public function getResumo(): array
    {
        try {
            if (!$this->tableExists('notas_fiscais')) {
                return ['total_notas' => 0, 'total_lancamentos' => 0, 'total_receitas' => 0, 'total_despesas' => 0];
            }
            $stmt = $this->db->query("
                SELECT
                    (SELECT COUNT(*) FROM notas_fiscais) AS total_notas,
                    (SELECT COUNT(*) FROM lancamentos_contabeis) AS total_lancamentos,
                    (SELECT COALESCE(SUM(valor), 0) FROM notas_fiscais WHERE tipo = 'Entrada') AS total_receitas,
                    (SELECT COALESCE(SUM(valor), 0) FROM notas_fiscais WHERE tipo = 'Saida') AS total_despesas
            ");
            return $stmt->fetch(\PDO::FETCH_ASSOC) ?: [];
        } catch (\PDOException) {
            return ['total_notas' => 0, 'total_lancamentos' => 0, 'total_receitas' => 0, 'total_despesas' => 0];
        }
    }

    public function getLancamentos(): array
    {
        try {
            if (!$this->tableExists('lancamentos_contabeis')) {
                return [];
            }
            $stmt = $this->db->query("
                SELECT id, descricao, valor, tipo, data_lancamento, categoria
                FROM lancamentos_contabeis
                ORDER BY data_lancamento DESC
                LIMIT 100
            ");
            return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\PDOException) {
            return [];
        }
    }

    public function getNotasFiscais(): array
    {
        try {
            if (!$this->tableExists('notas_fiscais')) {
                return [];
            }
            $stmt = $this->db->query("
                SELECT id, numero, tipo, valor, emissao, cliente_fornecedor, status
                FROM notas_fiscais
                ORDER BY emissao DESC
                LIMIT 100
            ");
            return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\PDOException) {
            return [];
        }
    }
}
