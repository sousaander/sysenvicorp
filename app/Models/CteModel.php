<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class CteModel extends Model
{
    const TABLE = 'conhecimento_transporte';

    public function getAll(array $filtros = []): array
    {
        try {
            $sql = "SELECT * FROM " . self::TABLE . " WHERE 1=1";
            $params = [];

            if (!empty($filtros['status'])) {
                $sql .= " AND status = :status";
                $params[':status'] = $filtros['status'];
            }
            if (!empty($filtros['data_inicio'])) {
                $sql .= " AND data_emissao >= :data_inicio";
                $params[':data_inicio'] = $filtros['data_inicio'];
            }
            if (!empty($filtros['data_fim'])) {
                $sql .= " AND data_emissao <= :data_fim";
                $params[':data_fim'] = $filtros['data_fim'];
            }
            $sql .= " ORDER BY data_emissao DESC, numero DESC LIMIT 500";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\PDOException $e) {
            error_log("Erro ao listar CT-e: " . $e->getMessage());
            return [];
        }
    }

    public function getById(int $id): ?array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM " . self::TABLE . " WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (\PDOException $e) {
            error_log("Erro ao buscar CT-e: " . $e->getMessage());
            return null;
        }
    }

    public function salvar(array $dados): int|false
    {
        try {
            $campos = [
                'numero', 'serie', 'chave_acesso', 'modelo', 'cfop', 'natureza_operacao',
                'tipo_servico', 'forma_pagamento', 'modal', 'tipo_cte',
                'tomador_id', 'tomador_nome', 'tomador_cpf_cnpj', 'tomador_ie',
                'tomador_email', 'tomador_endereco', 'tomador_municipio', 'tomador_uf', 'tomador_cep',
                'remetente_id', 'remetente_nome', 'remetente_cpf_cnpj',
                'remetente_endereco', 'remetente_municipio', 'remetente_uf',
                'destinatario_id', 'destinatario_nome', 'destinatario_cpf_cnpj',
                'destinatario_endereco', 'destinatario_municipio', 'destinatario_uf',
                'expedidor_nome', 'recebedor_nome',
                'valor_mercadorias', 'valor_frete', 'valor_recebido', 'valor_total',
                'base_calculo_icms', 'valor_icms', 'aliquota_icms',
                'base_calculo_pis', 'valor_pis', 'base_calculo_cofins', 'valor_cofins',
                'perc_red_base_calc_icms',
                'data_emissao', 'data_prevista_entrega',
                'status', 'protocolo', 'justificativa_cancelamento',
                'xml_file', 'dacte_file',
                'observacoes', 'usuario_emissao', 'empresa_emitente_id',
            ];

            if (!empty($dados['id'])) {
                $sets = [];
                foreach ($campos as $c) {
                    if (array_key_exists($c, $dados)) {
                        $sets[] = "$c = :$c";
                    }
                }
                $sets[] = "updated_at = NOW()";
                $sql = "UPDATE " . self::TABLE . " SET " . implode(', ', $sets) . " WHERE id = :id";
            } else {
                $cols = [];
                foreach ($campos as $c) {
                    if (array_key_exists($c, $dados)) {
                        $cols[] = $c;
                    }
                }
                $sql = "INSERT INTO " . self::TABLE . " (" . implode(', ', $cols) . ") VALUES (:" . implode(', :', $cols) . ")";
            }

            $stmt = $this->db->prepare($sql);
            foreach ($campos as $c) {
                if (array_key_exists($c, $dados)) {
                    $stmt->bindValue(":$c", $dados[$c]);
                }
            }
            if (!empty($dados['id'])) {
                $stmt->bindValue(':id', $dados['id'], \PDO::PARAM_INT);
            }
            $stmt->execute();

            if (!empty($dados['id'])) {
                return $dados['id'];
            }
            return (int)$this->db->lastInsertId();
        } catch (\PDOException $e) {
            error_log("Erro ao salvar CT-e: " . $e->getMessage());
            return false;
        }
    }

    public function excluir(int $id): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM " . self::TABLE . " WHERE id = :id");
            return $stmt->execute([':id' => $id]);
        } catch (\PDOException $e) {
            error_log("Erro ao excluir CT-e: " . $e->getMessage());
            return false;
        }
    }

    public function atualizarStatus(int $id, string $status, ?string $protocolo = null): bool
    {
        try {
            $sql = "UPDATE " . self::TABLE . " SET status = :status";
            $params = [':id' => $id, ':status' => $status];
            if ($protocolo) {
                $sql .= ", protocolo = :protocolo";
                $params[':protocolo'] = $protocolo;
            }
            $sql .= " WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } catch (\PDOException $e) {
            error_log("Erro ao atualizar status CT-e: " . $e->getMessage());
            return false;
        }
    }

    public function getProximoNumero(string $serie = '1'): int
    {
        try {
            $stmt = $this->db->prepare("SELECT COALESCE(MAX(CAST(numero AS UNSIGNED)), 0) + 1 AS prox FROM " . self::TABLE . " WHERE serie = :serie");
            $stmt->execute([':serie' => $serie]);
            return (int)$stmt->fetchColumn();
        } catch (\PDOException $e) {
            return 1;
        }
    }

    public function getNotasFiscais(int $cteId): array
    {
        try {
            $stmt = $this->db->prepare("SELECT cnf.*, nf.cliente_fornecedor, nf.valor AS nf_valor, nf.chave_acesso AS nf_chave
                FROM cte_notas_fiscais cnf
                LEFT JOIN notas_fiscais nf ON nf.id = cnf.nota_fiscal_id
                WHERE cnf.cte_id = :cte_id
                ORDER BY cnf.id");
            $stmt->execute([':cte_id' => $cteId]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\PDOException $e) {
            error_log("Erro ao listar notas fiscais do CT-e: " . $e->getMessage());
            return [];
        }
    }
}
