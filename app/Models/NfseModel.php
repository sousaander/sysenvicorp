<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class NfseModel extends Model
{
    const TABLE = 'nfse';

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
            error_log("Erro ao listar NFS-e: " . $e->getMessage());
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
            error_log("Erro ao buscar NFS-e: " . $e->getMessage());
            return null;
        }
    }

    public function salvar(array $dados): int|false
    {
        try {
            $campos = [
                'numero', 'serie', 'codigo_verificacao', 'chave_acesso',
                'link_download_pdf', 'link_download_xml',
                'tipo_documento', 'regime_especial_tributacao',
                'optante_simples_nacional', 'incentivo_fiscal', 'natureza_operacao',
                'servico_id', 'servico_descricao', 'servico_codigo_tributacao',
                'servico_codigo_cnae', 'servico_aliquota_iss', 'servico_valor_iss',
                'servico_base_calculo', 'servico_valor_liquido',
                'servico_valor_pis', 'servico_valor_cofins', 'servico_valor_inss',
                'servico_valor_ir', 'servico_valor_csll', 'servico_outras_retencoes',
                'servico_desconto_condicionado', 'servico_desconto_incondicionado',
                'servico_valor_total', 'valor_total',
                'cliente_id', 'cliente_nome', 'cliente_cpf_cnpj', 'cliente_ie',
                'cliente_email', 'cliente_endereco', 'cliente_numero',
                'cliente_complemento', 'cliente_bairro', 'cliente_codigo_municipio',
                'cliente_municipio', 'cliente_uf', 'cliente_cep', 'cliente_telefone',
                'data_emissao', 'data_competencia', 'data_vencimento',
                'status', 'protocolo', 'justificativa_cancelamento',
                'xml_file', 'pdf_file',
                'rps_numero', 'rps_serie', 'rps_tipo', 'rps_substituido_id',
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
            error_log("Erro ao salvar NFS-e: " . $e->getMessage());
            return false;
        }
    }

    public function excluir(int $id): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM " . self::TABLE . " WHERE id = :id");
            return $stmt->execute([':id' => $id]);
        } catch (\PDOException $e) {
            error_log("Erro ao excluir NFS-e: " . $e->getMessage());
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
            error_log("Erro ao atualizar status NFS-e: " . $e->getMessage());
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
}
