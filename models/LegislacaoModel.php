<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class LegislacaoModel extends Model
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

    // ========================
    // VERSÕES DE LEGISLAÇÃO
    // ========================

    public function getVersoes(?string $modulo = null): array
    {
        try {
            if (!$this->tableExists('legislacao_versoes')) return [];
            $sql = "SELECT * FROM legislacao_versoes";
            $params = [];
            if ($modulo) {
                $sql .= " WHERE modulo = ?";
                $params[] = $modulo;
            }
            $sql .= " ORDER BY data_vigencia DESC, modulo ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\PDOException) {
            return [];
        }
    }

    public function getVersaoById(int $id): ?array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM legislacao_versoes WHERE id = ?");
            $stmt->execute([$id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (\PDOException) {
            return null;
        }
    }

    public function getVersaoVigente(string $modulo): ?array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM legislacao_versoes
                WHERE modulo = ?
                  AND (data_vigencia IS NULL OR data_vigencia <= CURDATE())
                  AND (data_revogacao IS NULL OR data_revogacao > CURDATE())
                ORDER BY data_vigencia DESC
                LIMIT 1
            ");
            $stmt->execute([$modulo]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (\PDOException) {
            return null;
        }
    }

    public function salvarVersao(array $dados): bool
    {
        try {
            if (!$this->tableExists('legislacao_versoes')) return false;

            if (!empty($dados['id'])) {
                $stmt = $this->db->prepare("
                    UPDATE legislacao_versoes SET
                        modulo = ?, titulo = ?, descricao = ?,
                        tipo_ato = ?, numero_ato = ?, orgao_emissor = ?,
                        data_publicacao = ?, data_vigencia = ?, data_revogacao = ?,
                        arquivo_anexo = ?, resumo_mudancas = ?, impacto_esperado = ?,
                        versao = ?, obrigatorio = ?
                    WHERE id = ?
                ");
                return $stmt->execute([
                    $dados['modulo'], $dados['titulo'], $dados['descricao'] ?? null,
                    $dados['tipo_ato'] ?? null, $dados['numero_ato'] ?? null, $dados['orgao_emissor'] ?? null,
                    $dados['data_publicacao'] ?? null, $dados['data_vigencia'] ?? null, $dados['data_revogacao'] ?? null,
                    $dados['arquivo_anexo'] ?? null, $dados['resumo_mudancas'] ?? null, $dados['impacto_esperado'] ?? null,
                    $dados['versao'] ?? null, $dados['obrigatorio'] ?? 0,
                    $dados['id']
                ]);
            } else {
                $stmt = $this->db->prepare("
                    INSERT INTO legislacao_versoes
                    (modulo, titulo, descricao, tipo_ato, numero_ato, orgao_emissor,
                     data_publicacao, data_vigencia, data_revogacao,
                     arquivo_anexo, resumo_mudancas, impacto_esperado, versao, obrigatorio)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                return $stmt->execute([
                    $dados['modulo'], $dados['titulo'], $dados['descricao'] ?? null,
                    $dados['tipo_ato'] ?? null, $dados['numero_ato'] ?? null, $dados['orgao_emissor'] ?? null,
                    $dados['data_publicacao'] ?? null, $dados['data_vigencia'] ?? null, $dados['data_revogacao'] ?? null,
                    $dados['arquivo_anexo'] ?? null, $dados['resumo_mudancas'] ?? null, $dados['impacto_esperado'] ?? null,
                    $dados['versao'] ?? null, $dados['obrigatorio'] ?? 0
                ]);
            }
        } catch (\PDOException) {
            return false;
        }
    }

    public function excluirVersao(int $id): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM legislacao_versoes WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (\PDOException) {
            return false;
        }
    }

    public function getModulosLegislacao(): array
    {
        return [
            'icms' => 'ICMS',
            'pis' => 'PIS',
            'cofins' => 'COFINS',
            'ipi' => 'IPI',
            'iss' => 'ISS',
            'simples' => 'Simples Nacional',
            'trabalhistas' => 'Trabalhistas',
            'previdenciario' => 'Previdenciário',
            'contabil' => 'Contábil',
        ];
    }

    public function getTiposAto(): array
    {
        return [
            'Lei' => 'Lei',
            'Lei Complementar' => 'Lei Complementar',
            'Decreto' => 'Decreto',
            'Instrução Normativa' => 'Instrução Normativa',
            'Portaria' => 'Portaria',
            'Resolução' => 'Resolução',
            'Convênio' => 'Convênio',
            'Ato Declaratório' => 'Ato Declaratório',
            'Solução de Consulta' => 'Solução de Consulta',
            'Parecer Normativo' => 'Parecer Normativo',
        ];
    }

    // ========================
    // LOG DE ATUALIZAÇÕES
    // ========================

    public function registrarAtualizacao(string $tipo, string $acao, string $descricao, ?int $entidadeId = null, ?array $dadosAnteriores = null, ?array $dadosNovos = null, ?int $usuarioId = null, string $origem = 'manual'): bool
    {
        try {
            if (!$this->tableExists('log_atualizacao_fiscal')) return false;
            $stmt = $this->db->prepare("
                INSERT INTO log_atualizacao_fiscal
                (tipo, acao, descricao, entidade_id, dados_anteriores, dados_novos, usuario_id, origem)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            return $stmt->execute([
                $tipo, $acao, $descricao, $entidadeId,
                $dadosAnteriores ? json_encode($dadosAnteriores) : null,
                $dadosNovos ? json_encode($dadosNovos) : null,
                $usuarioId, $origem
            ]);
        } catch (\PDOException) {
            return false;
        }
    }

    public function getLogAtualizacoes(?string $tipo = null, int $limite = 100): array
    {
        try {
            if (!$this->tableExists('log_atualizacao_fiscal')) return [];
            $sql = "SELECT l.*, u.nome as usuario_nome
                    FROM log_atualizacao_fiscal l
                    LEFT JOIN usuarios u ON l.usuario_id = u.id";
            $params = [];
            if ($tipo) {
                $sql .= " WHERE l.tipo = ?";
                $params[] = $tipo;
            }
            $sql .= " ORDER BY l.created_at DESC LIMIT ?";
            $params[] = $limite;

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\PDOException) {
            return [];
        }
    }

    // ========================
    // VIGÊNCIA
    // ========================

    public function getProximasAlteracoes(int $dias = 60): array
    {
        try {
            if (!$this->tableExists('legislacao_versoes')) return [];
            $stmt = $this->db->prepare("
                SELECT * FROM legislacao_versoes
                WHERE data_vigencia BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY)
                   OR (data_revogacao BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY))
                ORDER BY data_vigencia ASC
            ");
            $stmt->execute([$dias, $dias]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\PDOException) {
            return [];
        }
    }
}
