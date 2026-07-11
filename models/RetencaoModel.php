<?php

namespace App\Models;

use App\Core\Model;

class RetencaoModel extends Model
{
    public function getByNotaFiscal(int $notaFiscalId): array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM retencoes_impostos WHERE nota_fiscal_id = :nfi");
            $stmt->execute([':nfi' => $notaFiscalId]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\PDOException $e) {
            error_log("Erro ao buscar retenções: " . $e->getMessage());
            return [];
        }
    }

    public function salvar(array $dados): int|false
    {
        try {
            if (!empty($dados['id'])) {
                $stmt = $this->db->prepare("UPDATE retencoes_impostos SET nota_fiscal_id=:nfi, tipo_retencao=:tipo, base_calculo=:base, aliquota=:aliq, valor=:valor, codigo_receita=:cr, competencia=:comp WHERE id=:id");
                $stmt->execute([
                    ':nfi' => $dados['nota_fiscal_id'],
                    ':tipo' => $dados['tipo_retencao'],
                    ':base' => $dados['base_calculo'],
                    ':aliq' => $dados['aliquota'],
                    ':valor' => $dados['valor'],
                    ':cr' => $dados['codigo_receita'] ?? null,
                    ':comp' => $dados['competencia'] ?? null,
                    ':id' => $dados['id'],
                ]);
                return $dados['id'];
            }
            $stmt = $this->db->prepare("INSERT INTO retencoes_impostos (nota_fiscal_id, tipo_retencao, base_calculo, aliquota, valor, codigo_receita, competencia) VALUES (:nfi, :tipo, :base, :aliq, :valor, :cr, :comp)");
            $stmt->execute([
                ':nfi' => $dados['nota_fiscal_id'],
                ':tipo' => $dados['tipo_retencao'],
                ':base' => $dados['base_calculo'],
                ':aliq' => $dados['aliquota'],
                ':valor' => $dados['valor'],
                ':cr' => $dados['codigo_receita'] ?? null,
                ':comp' => $dados['competencia'] ?? null,
            ]);
            return (int)$this->db->lastInsertId();
        } catch (\PDOException $e) {
            error_log("Erro ao salvar retenção: " . $e->getMessage());
            return false;
        }
    }

    public function excluir(int $id): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM retencoes_impostos WHERE id = :id");
            return $stmt->execute([':id' => $id]);
        } catch (\PDOException $e) {
            return false;
        }
    }

    public function getTotalPorTipo(string $dataInicio, string $dataFim): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT r.tipo_retencao, SUM(r.valor) AS total
                FROM retencoes_impostos r
                JOIN notas_fiscais n ON n.id = r.nota_fiscal_id
                WHERE n.emissao BETWEEN :di AND :df
                GROUP BY r.tipo_retencao
            ");
            $stmt->execute([':di' => $dataInicio, ':df' => $dataFim]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\PDOException $e) {
            return [];
        }
    }
}
