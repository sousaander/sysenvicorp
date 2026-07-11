<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class NotaFiscalModel extends Model
{
    public function getAll(array $filtros = []): array
    {
        try {
            $sql = "SELECT * FROM notas_fiscais WHERE 1=1";
            $params = [];

            if (!empty($filtros['status'])) {
                $sql .= " AND status = :status";
                $params[':status'] = $filtros['status'];
            }
            if (!empty($filtros['tipo'])) {
                $sql .= " AND tipo = :tipo";
                $params[':tipo'] = $filtros['tipo'];
            }
            if (!empty($filtros['data_inicio'])) {
                $sql .= " AND emissao >= :data_inicio";
                $params[':data_inicio'] = $filtros['data_inicio'];
            }
            if (!empty($filtros['data_fim'])) {
                $sql .= " AND emissao <= :data_fim";
                $params[':data_fim'] = $filtros['data_fim'];
            }
            $sql .= " ORDER BY emissao DESC LIMIT 500";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\PDOException $e) {
            error_log("Erro ao listar notas fiscais: " . $e->getMessage());
            return [];
        }
    }

    public function getById(int $id): ?array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM notas_fiscais WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (\PDOException $e) {
            error_log("Erro ao buscar nota fiscal: " . $e->getMessage());
            return null;
        }
    }

    public function salvar(array $dados): int|false
    {
        try {
            $campos = [
                'numero', 'serie', 'chave_acesso', 'cfop', 'natureza_operacao',
                'finalidade', 'tipo', 'cliente_id', 'cliente_fornecedor', 'cnpj_cpf',
                'cliente_ie', 'cliente_endereco', 'cliente_municipio_ibge', 'cliente_uf',
                'emissao', 'valor', 'itens_json', 'status',
                'base_calculo_icms', 'valor_icms', 'base_calculo_pis', 'valor_pis',
                'base_calculo_cofins', 'valor_cofins', 'valor_iss',
                'valor_irrf', 'valor_inss', 'valor_csll',
                'retencao_pis', 'retencao_cofins', 'retencao_csll', 'retencao_iss', 'retencao_inss', 'retencao_irrf',
                'protocolo', 'xml_file', 'danfe_file', 'justificativa_cancelamento',
                'usuario_emissao', 'empresa_emitente_id', 'observacoes'
            ];

            if (!empty($dados['id'])) {
                $sets = [];
                foreach ($campos as $c) {
                    if (array_key_exists($c, $dados)) {
                        $sets[] = "$c = :$c";
                    }
                }
                $sets[] = "updated_at = NOW()";
                $sql = "UPDATE notas_fiscais SET " . implode(', ', $sets) . " WHERE id = :id";
            } else {
                $sets = [];
                foreach ($campos as $c) {
                    if (array_key_exists($c, $dados)) {
                        $sets[] = $c;
                    }
                }
                $cols = implode(', ', $sets);
                $vals = ':' . implode(', :', $sets);
                $sql = "INSERT INTO notas_fiscais ($cols) VALUES ($vals)";
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
            error_log("Erro ao salvar nota fiscal: " . $e->getMessage());
            return false;
        }
    }

    public function excluir(int $id): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM notas_fiscais WHERE id = :id");
            return $stmt->execute([':id' => $id]);
        } catch (\PDOException $e) {
            error_log("Erro ao excluir nota fiscal: " . $e->getMessage());
            return false;
        }
    }

    public function atualizarStatus(int $id, string $status, ?string $protocolo = null): bool
    {
        try {
            $sql = "UPDATE notas_fiscais SET status = :status";
            $params = [':id' => $id, ':status' => $status];
            if ($protocolo) {
                $sql .= ", protocolo = :protocolo";
                $params[':protocolo'] = $protocolo;
            }
            $sql .= " WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } catch (\PDOException $e) {
            error_log("Erro ao atualizar status: " . $e->getMessage());
            return false;
        }
    }

    public function getProximoNumero(string $serie = '1'): int
    {
        try {
            $stmt = $this->db->prepare("SELECT COALESCE(MAX(CAST(numero AS UNSIGNED)), 0) + 1 AS prox FROM notas_fiscais WHERE serie = :serie");
            $stmt->execute([':serie' => $serie]);
            return (int)$stmt->fetchColumn();
        } catch (\PDOException $e) {
            return 1;
        }
    }
}
