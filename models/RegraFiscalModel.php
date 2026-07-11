<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class RegraFiscalModel extends Model
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

    public function getRegras(?int $produtoId = null): array
    {
        try {
            if (!$this->tableExists('regras_fiscais')) return [];
            $sql = "
                SELECT r.*, p.nome as produto_nome, p.codigo as produto_codigo
                FROM regras_fiscais r
                LEFT JOIN produtos p ON r.produto_id = p.id
            ";
            $params = [];
            if ($produtoId) {
                $sql .= " WHERE r.produto_id = ? OR r.produto_id IS NULL";
                $params[] = $produtoId;
            }
            $sql .= " ORDER BY r.regime_tributario, r.produto_id IS NOT NULL DESC, p.nome ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\PDOException) {
            return [];
        }
    }

    public function getRegraById(int $id): ?array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT r.*, p.nome as produto_nome, p.codigo as produto_codigo
                FROM regras_fiscais r
                LEFT JOIN produtos p ON r.produto_id = p.id
                WHERE r.id = ?
            ");
            $stmt->execute([$id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (\PDOException) {
            return null;
        }
    }

    public function getRegrasPorProduto(int $produtoId): array
    {
        try {
            if (!$this->tableExists('regras_fiscais')) return [];
            $stmt = $this->db->prepare("
                SELECT * FROM regras_fiscais
                WHERE (produto_id = ? OR produto_id IS NULL)
                  AND ativo = 1
                  AND (data_vigencia_inicio IS NULL OR data_vigencia_inicio <= CURDATE())
                  AND (data_vigencia_fim IS NULL OR data_vigencia_fim >= CURDATE())
                ORDER BY produto_id IS NOT NULL DESC
            ");
            $stmt->execute([$produtoId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\PDOException) {
            return [];
        }
    }

    public function salvarRegra(array $dados): bool
    {
        try {
            if (!$this->tableExists('regras_fiscais')) return false;

            if (!empty($dados['id'])) {
                $stmt = $this->db->prepare("
                    UPDATE regras_fiscais SET
                        produto_id = ?, tipo_entidade = ?, regime_tributario = ?,
                        cfop = ?, cst_icms = ?, csosn = ?, cst_ipi = ?, cst_pis = ?, cst_cofins = ?,
                        aliquota_icms = ?, aliquota_ipi = ?, aliquota_pis = ?, aliquota_cofins = ?, aliquota_iss = ?,
                        reducao_base_icms = ?, margem_st = ?, base_calculo = ?,
                        enquadramento = ?, ncm_obrigatorio = ?, cest_obrigatorio = ?,
                        beneficio_fiscal = ?, uf_origem = ?, uf_destino = ?,
                        data_vigencia_inicio = ?, data_vigencia_fim = ?, ativo = ?
                    WHERE id = ?
                ");
                return $stmt->execute([
                    $dados['produto_id'] ?? null,
                    $dados['tipo_entidade'] ?? 'produto',
                    $dados['regime_tributario'] ?? null,
                    $dados['cfop'] ?? null,
                    $dados['cst_icms'] ?? null,
                    $dados['csosn'] ?? null,
                    $dados['cst_ipi'] ?? null,
                    $dados['cst_pis'] ?? null,
                    $dados['cst_cofins'] ?? null,
                    $dados['aliquota_icms'] ?? 0,
                    $dados['aliquota_ipi'] ?? 0,
                    $dados['aliquota_pis'] ?? 0,
                    $dados['aliquota_cofins'] ?? 0,
                    $dados['aliquota_iss'] ?? 0,
                    $dados['reducao_base_icms'] ?? 0,
                    $dados['margem_st'] ?? 0,
                    $dados['base_calculo'] ?? null,
                    $dados['enquadramento'] ?? null,
                    $dados['ncm_obrigatorio'] ?? 1,
                    $dados['cest_obrigatorio'] ?? 0,
                    $dados['beneficio_fiscal'] ?? null,
                    $dados['uf_origem'] ?? null,
                    $dados['uf_destino'] ?? null,
                    $dados['data_vigencia_inicio'] ?? null,
                    $dados['data_vigencia_fim'] ?? null,
                    $dados['ativo'] ?? 1,
                    $dados['id']
                ]);
            } else {
                $stmt = $this->db->prepare("
                    INSERT INTO regras_fiscais
                    (produto_id, tipo_entidade, regime_tributario,
                     cfop, cst_icms, csosn, cst_ipi, cst_pis, cst_cofins,
                     aliquota_icms, aliquota_ipi, aliquota_pis, aliquota_cofins, aliquota_iss,
                     reducao_base_icms, margem_st, base_calculo,
                     enquadramento, ncm_obrigatorio, cest_obrigatorio,
                     beneficio_fiscal, uf_origem, uf_destino,
                     data_vigencia_inicio, data_vigencia_fim, ativo, criado_por)
                    VALUES (?, ?, ?,
                            ?, ?, ?, ?, ?, ?,
                            ?, ?, ?, ?, ?,
                            ?, ?, ?,
                            ?, ?, ?,
                            ?, ?, ?,
                            ?, ?, ?, ?)
                ");
                return $stmt->execute([
                    $dados['produto_id'] ?? null,
                    $dados['tipo_entidade'] ?? 'produto',
                    $dados['regime_tributario'] ?? null,
                    $dados['cfop'] ?? null,
                    $dados['cst_icms'] ?? null,
                    $dados['csosn'] ?? null,
                    $dados['cst_ipi'] ?? null,
                    $dados['cst_pis'] ?? null,
                    $dados['cst_cofins'] ?? null,
                    $dados['aliquota_icms'] ?? 0,
                    $dados['aliquota_ipi'] ?? 0,
                    $dados['aliquota_pis'] ?? 0,
                    $dados['aliquota_cofins'] ?? 0,
                    $dados['aliquota_iss'] ?? 0,
                    $dados['reducao_base_icms'] ?? 0,
                    $dados['margem_st'] ?? 0,
                    $dados['base_calculo'] ?? null,
                    $dados['enquadramento'] ?? null,
                    $dados['ncm_obrigatorio'] ?? 1,
                    $dados['cest_obrigatorio'] ?? 0,
                    $dados['beneficio_fiscal'] ?? null,
                    $dados['uf_origem'] ?? null,
                    $dados['uf_destino'] ?? null,
                    $dados['data_vigencia_inicio'] ?? null,
                    $dados['data_vigencia_fim'] ?? null,
                    $dados['ativo'] ?? 1,
                    $dados['criado_por'] ?? null
                ]);
            }
        } catch (\PDOException) {
            return false;
        }
    }

    public function excluirRegra(int $id): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM regras_fiscais WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (\PDOException) {
            return false;
        }
    }

    public function getRegimesTributarios(): array
    {
        return [
            'simples_nacional' => 'Simples Nacional',
            'lucro_presumido' => 'Lucro Presumido',
            'lucro_real' => 'Lucro Real',
            'mei' => 'MEI',
        ];
    }

    public function getOpcoesCFOP(): array
    {
        return [
            '1.101' => '1.101 - Compra p/ industrialização',
            '1.102' => '1.102 - Compra p/ comercialização',
            '1.111' => '1.111 - Compra p/ industrialização (mesma UF)',
            '1.201' => '1.201 - Devolução de venda de industrialização',
            '1.202' => '1.202 - Devolução de venda de comércio',
            '5.101' => '5.101 - Venda de industrialização',
            '5.102' => '5.102 - Venda de comércio',
            '5.201' => '5.201 - Venda de mercadoria adquirida',
            '5.901' => '5.901 - Remessa p/ industrialização',
            '5.902' => '5.902 - Retorno de industrialização',
            '6.101' => '6.101 - Venda de industrialização (outra UF)',
            '6.102' => '6.102 - Venda de comércio (outra UF)',
            '2.101' => '2.101 - Devolução de compra (industrial)',
            '2.102' => '2.102 - Devolução de compra (comércio)',
            '1.503' => '1.503 - Transferência de estoque',
        ];
    }

    public function getOpcoesCST(): array
    {
        return [
            '00' => '00 - Tributada integralmente',
            '10' => '10 - Tributada com cobrança ST',
            '20' => '20 - Base de cálculo reduzida',
            '30' => '30 - Isenta ou não tributada (ST)',
            '40' => '40 - Isenta',
            '41' => '41 - Não tributada',
            '50' => '50 - Suspensão',
            '51' => '51 - Diferimento',
            '60' => '60 - ICMS cobrado anteriormente por ST',
            '70' => '70 - Redução + ST',
            '90' => '90 - Outras',
        ];
    }

    public function getOpcoesCSOSN(): array
    {
        return [
            '101' => '101 - Tributada pelo Simples (permitido crédito)',
            '102' => '102 - Tributada pelo Simples (sem crédito)',
            '103' => '103 - Isenta no Simples',
            '201' => '201 - Tributada pelo Simples + ST',
            '202' => '202 - Tributada pelo Simples (ST reduzida)',
            '203' => '203 - Isenta no Simples + ST',
            '300' => '300 - Imune',
            '400' => '400 - Não tributada',
            '500' => '500 - ST integral',
            '900' => '900 - Outras no Simples',
        ];
    }

    public function getOpcoesPISCOFINS(): array
    {
        return [
            '01' => '01 - Entrada tributada',
            '02' => '02 - Saída tributada',
            '03' => '03 - Entrada isenta',
            '04' => '04 - Saída isenta',
            '05' => '05 - Alíquota zero',
            '06' => '06 - Suspensão',
            '07' => '07 - Sem incidência',
            '08' => '08 - Crédito presumido',
            '09' => '09 - Substituição tributária',
            '49' => '49 - Outras operações',
            '50' => '50 - Entrada tributada (monofásico)',
            '51' => '51 - Saída tributada (monofásico)',
            '70' => '70 - Operação com direito a crédito',
            '71' => '71 - Operação sem direito a crédito',
            '98' => '98 - Não incidência',
            '99' => '99 - Outras',
        ];
    }
}
