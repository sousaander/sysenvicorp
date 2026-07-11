<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class CteModel extends Model
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
            if (!$this->tableExists('conhecimento_transporte')) return [];
            $sql = "SELECT c.* FROM conhecimento_transporte c WHERE 1=1";
            $params = [];
            if (!empty($filtros['status'])) { $sql .= " AND c.status=?"; $params[] = $filtros['status']; }
            if (!empty($filtros['data_inicio'])) { $sql .= " AND c.data_emissao >= ?"; $params[] = $filtros['data_inicio']; }
            if (!empty($filtros['data_fim'])) { $sql .= " AND c.data_emissao <= ?"; $params[] = $filtros['data_fim']; }
            if (!empty($filtros['tomador'])) { $sql .= " AND (c.tomador_nome LIKE ? OR c.tomador_cpf_cnpj LIKE ?)"; $params[] = "%{$filtros['tomador']}%"; $params[] = "%{$filtros['tomador']}%"; }
            $sql .= " ORDER BY c.data_emissao DESC LIMIT 500";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\PDOException) { return []; }
    }

    public function getById(int $id): ?array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM conhecimento_transporte WHERE id=?");
            $stmt->execute([$id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (\PDOException) { return null; }
    }

    public function getNotasFiscaisVinculadas(int $cteId): array
    {
        try {
            if (!$this->tableExists('cte_notas_fiscais')) return [];
            $stmt = $this->db->prepare("
                SELECT v.*, nf.numero as nf_numero, nf.chave_acesso as nf_chave,
                       ns.numero as nfse_numero, ns.codigo_verificacao as nfse_codigo
                FROM cte_notas_fiscais v
                LEFT JOIN notas_fiscais nf ON v.nota_fiscal_id = nf.id
                LEFT JOIN nfse ns ON v.nfse_id = ns.id
                WHERE v.cte_id = ?
            ");
            $stmt->execute([$cteId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\PDOException) { return []; }
    }

    public function getComponentesValor(int $cteId): array
    {
        try {
            if (!$this->tableExists('cte_componentes_valor')) return [];
            $stmt = $this->db->prepare("SELECT * FROM cte_componentes_valor WHERE cte_id=?");
            $stmt->execute([$cteId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\PDOException) { return []; }
    }

    public function salvar(array $dados): bool
    {
        try {
            if (!$this->tableExists('conhecimento_transporte')) return false;
            $fields = [
                'numero','serie','cfop','natureza_operacao','tipo_servico','forma_pagamento',
                'modal','tipo_cte','tomador_id','tomador_nome','tomador_cpf_cnpj','tomador_ie','tomador_email',
                'tomador_endereco','tomador_municipio','tomador_uf','tomador_cep',
                'remetente_id','remetente_nome','remetente_cpf_cnpj','remetente_endereco','remetente_municipio','remetente_uf',
                'destinatario_id','destinatario_nome','destinatario_cpf_cnpj','destinatario_endereco','destinatario_municipio','destinatario_uf',
                'expedidor_nome','recebedor_nome',
                'valor_mercadorias','valor_frete','valor_recebido','valor_total',
                'base_calculo_icms','valor_icms','aliquota_icms','perc_red_base_calc_icms',
                'base_calculo_pis','valor_pis','base_calculo_cofins','valor_cofins',
                'data_emissao','data_prevista_entrega','status','observacoes',
                'usuario_emissao','empresa_emitente_id','xml_file','dacte_file','protocolo',
            ];

            if (!empty($dados['id'])) {
                $sets = []; $params = [];
                foreach ($fields as $f) { if (array_key_exists($f, $dados)) { $sets[] = "$f=?"; $params[] = $dados[$f]; } }
                $params[] = $dados['id'];
                return $this->db->prepare("UPDATE conhecimento_transporte SET " . implode(',', $sets) . " WHERE id=?")->execute($params);
            } else {
                $cols = []; $vals = [];
                foreach ($fields as $f) { if (array_key_exists($f, $dados)) { $cols[] = $f; $vals[] = $dados[$f]; } }
                $placeholders = rtrim(str_repeat('?,', count($vals)), ',');
                return $this->db->prepare("INSERT INTO conhecimento_transporte (" . implode(',', $cols) . ") VALUES ($placeholders)")->execute($vals);
            }
        } catch (\PDOException) { return false; }
    }

    public function salvarNotaFiscalVinculada(int $cteId, ?int $notaFiscalId, ?int $nfseId, ?string $chaveAcesso, float $valor): bool
    {
        try {
            $stmt = $this->db->prepare("INSERT INTO cte_notas_fiscais (cte_id,nota_fiscal_id,nfse_id,chave_acesso,valor) VALUES (?,?,?,?,?)");
            return $stmt->execute([$cteId, $notaFiscalId, $nfseId, $chaveAcesso, $valor]);
        } catch (\PDOException) { return false; }
    }

    public function salvarComponenteValor(int $cteId, string $nome, float $valor): bool
    {
        try {
            $stmt = $this->db->prepare("INSERT INTO cte_componentes_valor (cte_id,nome,valor) VALUES (?,?,?)");
            return $stmt->execute([$cteId, $nome, $valor]);
        } catch (\PDOException) { return false; }
    }

    public function excluir(int $id): bool
    {
        try { $stmt = $this->db->prepare("DELETE FROM conhecimento_transporte WHERE id=?"); return $stmt->execute([$id]); } catch (\PDOException) { return false; }
    }

    public function atualizarStatus(int $id, string $status, ?string $protocolo = null): bool
    {
        try {
            $stmt = $this->db->prepare("UPDATE conhecimento_transporte SET status=?, protocolo=COALESCE(?,protocolo) WHERE id=?");
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
            $stmt = $this->db->prepare("SELECT COALESCE(MAX(numero),0)+1 FROM conhecimento_transporte WHERE serie=?");
            $stmt->execute([$serie]);
            return (int)$stmt->fetchColumn();
        } catch (\PDOException) { return 1; }
    }

    public function getResumo(): array
    {
        try {
            $resumo = ['total'=>0,'autorizadas'=>0,'pendentes'=>0,'canceladas'=>0,'valor_total'=>0];
            if (!$this->tableExists('conhecimento_transporte')) return $resumo;
            $stmt = $this->db->query("SELECT COUNT(*) FROM conhecimento_transporte"); $resumo['total'] = (int)$stmt->fetchColumn();
            $stmt = $this->db->query("SELECT COUNT(*) FROM conhecimento_transporte WHERE status='Autorizada'"); $resumo['autorizadas'] = (int)$stmt->fetchColumn();
            $stmt = $this->db->query("SELECT COUNT(*) FROM conhecimento_transporte WHERE status='Pendente'"); $resumo['pendentes'] = (int)$stmt->fetchColumn();
            $stmt = $this->db->query("SELECT COUNT(*) FROM conhecimento_transporte WHERE status='Cancelada'"); $resumo['canceladas'] = (int)$stmt->fetchColumn();
            $stmt = $this->db->query("SELECT COALESCE(SUM(valor_total),0) FROM conhecimento_transporte WHERE status='Autorizada'"); $resumo['valor_total'] = (float)$stmt->fetchColumn();
            return $resumo;
        } catch (\PDOException) { return $resumo; }
    }
}
