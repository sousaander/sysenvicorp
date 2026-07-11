<?php

namespace App\Models;

use App\Core\Model;

class AliquotaModel extends Model
{
    public function getAll(): array
    {
        try {
            $stmt = $this->db->query("SELECT * FROM aliquotas_fiscais ORDER BY uf, municipio");
            return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\PDOException $e) {
            return [];
        }
    }

    public function getById(int $id): ?array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM aliquotas_fiscais WHERE id = :id");
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
        } catch (\PDOException $e) {
            return null;
        }
    }

    public function salvar(array $dados): int|false
    {
        try {
            if (!empty($dados['id'])) {
                $stmt = $this->db->prepare("UPDATE aliquotas_fiscais SET uf=:uf, municipio=:municipio, codigo_servico=:cs, aliquota_iss=:iss, cnae=:cnae, cst_pis=:cst_pis, cst_cofins=:cst_cofins, cfop_padrao=:cfop, regime_tributario=:regime, ativo=:ativo WHERE id=:id");
                $stmt->execute([
                    ':uf' => $dados['uf'], ':municipio' => $dados['municipio'],
                    ':cs' => $dados['codigo_servico'], ':iss' => $dados['aliquota_iss'],
                    ':cnae' => $dados['cnae'], ':cst_pis' => $dados['cst_pis'],
                    ':cst_cofins' => $dados['cst_cofins'], ':cfop' => $dados['cfop_padrao'],
                    ':regime' => $dados['regime_tributario'], ':ativo' => $dados['ativo'] ?? 1,
                    ':id' => $dados['id'],
                ]);
                return $dados['id'];
            }
            $stmt = $this->db->prepare("INSERT INTO aliquotas_fiscais (uf, municipio, codigo_servico, aliquota_iss, cnae, cst_pis, cst_cofins, cfop_padrao, regime_tributario, ativo) VALUES (:uf, :municipio, :cs, :iss, :cnae, :cst_pis, :cst_cofins, :cfop, :regime, :ativo)");
            $stmt->execute([
                ':uf' => $dados['uf'], ':municipio' => $dados['municipio'],
                ':cs' => $dados['codigo_servico'], ':iss' => $dados['aliquota_iss'],
                ':cnae' => $dados['cnae'], ':cst_pis' => $dados['cst_pis'],
                ':cst_cofins' => $dados['cst_cofins'], ':cfop' => $dados['cfop_padrao'],
                ':regime' => $dados['regime_tributario'], ':ativo' => $dados['ativo'] ?? 1,
            ]);
            return (int)$this->db->lastInsertId();
        } catch (\PDOException $e) {
            error_log("Erro ao salvar alíquota: " . $e->getMessage());
            return false;
        }
    }

    public function excluir(int $id): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM aliquotas_fiscais WHERE id = :id");
            return $stmt->execute([':id' => $id]);
        } catch (\PDOException $e) {
            return false;
        }
    }

    public function getPorUF(string $uf): array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM aliquotas_fiscais WHERE uf = :uf AND ativo = 1");
            $stmt->execute([':uf' => $uf]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\PDOException $e) {
            return [];
        }
    }
}
