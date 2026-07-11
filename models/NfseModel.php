<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class NfseModel extends Model
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

    public function getAll(array $filtros = []): array
    {
        try {
            if (!$this->tableExists('nfse')) return [];
            $sql = "SELECT n.*, c.nome as cliente_nome_display
                    FROM nfse n
                    LEFT JOIN clientes c ON n.cliente_id = c.id
                    WHERE 1=1";
            $params = [];

            if (!empty($filtros['status'])) {
                $sql .= " AND n.status = ?"; $params[] = $filtros['status'];
            }
            if (!empty($filtros['data_inicio'])) {
                $sql .= " AND n.data_emissao >= ?"; $params[] = $filtros['data_inicio'];
            }
            if (!empty($filtros['data_fim'])) {
                $sql .= " AND n.data_emissao <= ?"; $params[] = $filtros['data_fim'];
            }
            if (!empty($filtros['cliente'])) {
                $sql .= " AND (n.cliente_nome LIKE ? OR n.cliente_cpf_cnpj LIKE ?)";
                $params[] = "%{$filtros['cliente']}%"; $params[] = "%{$filtros['cliente']}%";
            }

            $sql .= " ORDER BY n.data_emissao DESC LIMIT 500";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\PDOException) {
            return [];
        }
    }

    public function getById(int $id): ?array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT n.*, 
                       (SELECT COUNT(*) FROM nfse_itens WHERE nfse_id = n.id) as total_itens
                FROM nfse n WHERE n.id = ?
            ");
            $stmt->execute([$id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (\PDOException) {
            return null;
        }
    }

    public function getItens(int $nfseId): array
    {
        try {
            if (!$this->tableExists('nfse_itens')) return [];
            $stmt = $this->db->prepare("SELECT * FROM nfse_itens WHERE nfse_id = ? ORDER BY id");
            $stmt->execute([$nfseId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\PDOException) {
            return [];
        }
    }

    public function salvar(array $dados): bool
    {
        try {
            if (!$this->tableExists('nfse')) return false;

            $fields = [
                'numero','serie','natureza_operacao','servico_id','servico_descricao',
                'servico_codigo_tributacao','servico_codigo_cnae','servico_aliquota_iss',
                'servico_valor_iss','servico_base_calculo','servico_valor_liquido',
                'servico_valor_pis','servico_valor_cofins','servico_valor_inss','servico_valor_ir','servico_valor_csll',
                'servico_outras_retencoes','servico_desconto_condicionado','servico_desconto_incondicionado',
                'servico_valor_total','valor_total',
                'cliente_id','cliente_nome','cliente_cpf_cnpj','cliente_ie','cliente_email',
                'cliente_endereco','cliente_numero','cliente_complemento','cliente_bairro',
                'cliente_codigo_municipio','cliente_municipio','cliente_uf','cliente_cep','cliente_telefone',
                'data_emissao','data_competencia','data_vencimento',
                'regime_especial_tributacao','optante_simples_nacional','incentivo_fiscal',
                'status','observacoes','usuario_emissao','empresa_emitente_id',
                'xml_file','pdf_file','protocolo','codigo_verificacao',
                'rps_numero','rps_serie','rps_tipo',
            ];

            if (!empty($dados['id'])) {
                $sets = [];
                $params = [];
                foreach ($fields as $f) {
                    if (array_key_exists($f, $dados)) {
                        $sets[] = "$f = ?";
                        $params[] = $dados[$f];
                    }
                }
                $params[] = $dados['id'];
                $sql = "UPDATE nfse SET " . implode(', ', $sets) . " WHERE id = ?";
                return $this->db->prepare($sql)->execute($params);
            } else {
                $cols = []; $vals = [];
                foreach ($fields as $f) {
                    if (array_key_exists($f, $dados)) {
                        $cols[] = $f;
                        $vals[] = $dados[$f];
                    }
                }
                $placeholders = rtrim(str_repeat('?,', count($vals)), ',');
                $sql = "INSERT INTO nfse (" . implode(',', $cols) . ") VALUES ($placeholders)";
                return $this->db->prepare($sql)->execute($vals);
            }
        } catch (\PDOException) {
            return false;
        }
    }

    public function salvarItem(array $item): bool
    {
        try {
            if (!empty($item['id'])) {
                $stmt = $this->db->prepare("UPDATE nfse_itens SET descricao=?,codigo_tributacao=?,aliquota_iss=?,valor_unitario=?,quantidade=?,valor_total=?,desconto=?,retido=? WHERE id=?");
                return $stmt->execute([$item['descricao'],$item['codigo_tributacao']??null,$item['aliquota_iss']??0,$item['valor_unitario']??0,$item['quantidade']??1,$item['valor_total']??0,$item['desconto']??0,$item['retido']??0,$item['id']]);
            } else {
                $stmt = $this->db->prepare("INSERT INTO nfse_itens (nfse_id,descricao,codigo_tributacao,aliquota_iss,valor_unitario,quantidade,valor_total,desconto,retido) VALUES (?,?,?,?,?,?,?,?,?)");
                return $stmt->execute([$item['nfse_id'],$item['descricao'],$item['codigo_tributacao']??null,$item['aliquota_iss']??0,$item['valor_unitario']??0,$item['quantidade']??1,$item['valor_total']??0,$item['desconto']??0,$item['retido']??0]);
            }
        } catch (\PDOException) {
            return false;
        }
    }

    public function excluirItem(int $id): bool
    {
        try { $stmt = $this->db->prepare("DELETE FROM nfse_itens WHERE id=?"); return $stmt->execute([$id]); } catch (\PDOException) { return false; }
    }

    public function excluir(int $id): bool
    {
        try { $stmt = $this->db->prepare("DELETE FROM nfse WHERE id=?"); return $stmt->execute([$id]); } catch (\PDOException) { return false; }
    }

    public function atualizarStatus(int $id, string $status, ?string $protocolo = null): bool
    {
        try {
            $stmt = $this->db->prepare("UPDATE nfse SET status=?, protocolo=COALESCE(?,protocolo) WHERE id=?");
            return $stmt->execute([$status, $protocolo, $id]);
        } catch (\PDOException) { return false; }
    }

    public function getDb(): \PDO
    {
        return $this->db;
    }

    public function getProximoNumero(string $serie = '1'): int
    {
        try {
            $stmt = $this->db->prepare("SELECT COALESCE(MAX(numero),0)+1 FROM nfse WHERE serie=?");
            $stmt->execute([$serie]);
            return (int)$stmt->fetchColumn();
        } catch (\PDOException) { return 1; }
    }

    public function getResumo(): array
    {
        try {
            $resumo = ['total' => 0, 'autorizadas' => 0, 'pendentes' => 0, 'canceladas' => 0, 'valor_total' => 0];
            if (!$this->tableExists('nfse')) return $resumo;
            $stmt = $this->db->query("SELECT COUNT(*) FROM nfse"); $resumo['total'] = (int)$stmt->fetchColumn();
            $stmt = $this->db->query("SELECT COUNT(*) FROM nfse WHERE status='Autorizada'"); $resumo['autorizadas'] = (int)$stmt->fetchColumn();
            $stmt = $this->db->query("SELECT COUNT(*) FROM nfse WHERE status='Pendente'"); $resumo['pendentes'] = (int)$stmt->fetchColumn();
            $stmt = $this->db->query("SELECT COUNT(*) FROM nfse WHERE status='Cancelada'"); $resumo['canceladas'] = (int)$stmt->fetchColumn();
            $stmt = $this->db->query("SELECT COALESCE(SUM(valor_total),0) FROM nfse WHERE status='Autorizada'"); $resumo['valor_total'] = (float)$stmt->fetchColumn();
            return $resumo;
        } catch (\PDOException) { return $resumo; }
    }
}
