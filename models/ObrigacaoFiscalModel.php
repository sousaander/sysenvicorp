<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class ObrigacaoFiscalModel extends Model
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
    // OBRIGAÇÕES FISCAIS
    // ========================

    public function getObrigacoes(): array
    {
        try {
            if (!$this->tableExists('obrigacoes_fiscais')) return [];
            $stmt = $this->db->query("
                SELECT o.*,
                    (SELECT COUNT(*) FROM calendario_fiscal c WHERE c.obrigacao_id = o.id AND c.ano = YEAR(CURDATE()) AND c.status = 'pendente') as pendentes,
                    (SELECT COUNT(*) FROM calendario_fiscal c WHERE c.obrigacao_id = o.id AND c.ano = YEAR(CURDATE()) AND c.status = 'atrasado') as atrasados,
                    (SELECT COUNT(*) FROM calendario_fiscal c WHERE c.obrigacao_id = o.id AND c.ano = YEAR(CURDATE())) as total_periodos
                FROM obrigacoes_fiscais o
                ORDER BY o.orgao, o.periodicidade, o.nome
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\PDOException) {
            return [];
        }
    }

    public function getObrigacaoById(int $id): ?array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM obrigacoes_fiscais WHERE id = ?");
            $stmt->execute([$id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (\PDOException) {
            return null;
        }
    }

    public function salvarObrigacao(array $dados): bool
    {
        try {
            if (!$this->tableExists('obrigacoes_fiscais')) return false;

            if (!empty($dados['id'])) {
                $stmt = $this->db->prepare("
                    UPDATE obrigacoes_fiscais SET
                        nome = ?, descricao = ?, orgao = ?, periodicidade = ?,
                        dia_vencimento = ?, mes_referencia = ?, forma_entrega = ?,
                        base_legal = ?, obrigatorio = ?, ativo = ?
                    WHERE id = ?
                ");
                return $stmt->execute([
                    $dados['nome'], $dados['descricao'] ?? null, $dados['orgao'], $dados['periodicidade'],
                    $dados['dia_vencimento'], $dados['mes_referencia'] ?? null, $dados['forma_entrega'] ?? null,
                    $dados['base_legal'] ?? null, $dados['obrigatorio'] ?? 1, $dados['ativo'] ?? 1,
                    $dados['id']
                ]);
            } else {
                $stmt = $this->db->prepare("
                    INSERT INTO obrigacoes_fiscais
                    (nome, descricao, orgao, periodicidade, dia_vencimento, mes_referencia,
                     forma_entrega, base_legal, obrigatorio, ativo)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                return $stmt->execute([
                    $dados['nome'], $dados['descricao'] ?? null, $dados['orgao'], $dados['periodicidade'],
                    $dados['dia_vencimento'], $dados['mes_referencia'] ?? null, $dados['forma_entrega'] ?? null,
                    $dados['base_legal'] ?? null, $dados['obrigatorio'] ?? 1, $dados['ativo'] ?? 1
                ]);
            }
        } catch (\PDOException) {
            return false;
        }
    }

    public function excluirObrigacao(int $id): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM obrigacoes_fiscais WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (\PDOException) {
            return false;
        }
    }

    // ========================
    // CALENDÁRIO FISCAL
    // ========================

    public function gerarCalendario(int $ano): int
    {
        $gerados = 0;
        try {
            if (!$this->tableExists('obrigacoes_fiscais') || !$this->tableExists('calendario_fiscal')) return 0;

            $obrigacoes = $this->db->query("SELECT * FROM obrigacoes_fiscais WHERE ativo = 1")->fetchAll(PDO::FETCH_ASSOC);

            foreach ($obrigacoes as $o) {
                $meses = $this->getMesesPeriodicidade($o['periodicidade'], $ano, $o['mes_referencia']);
                foreach ($meses as $mes) {
                    $jaExiste = $this->db->prepare(
                        "SELECT COUNT(*) FROM calendario_fiscal WHERE obrigacao_id = ? AND ano = ? AND mes = ?"
                    );
                    $jaExiste->execute([$o['id'], $ano, $mes]);
                    if ($jaExiste->fetchColumn() > 0) continue;

                    $dataVencimento = $this->calcularDataVencimento($ano, $mes, (int)$o['dia_vencimento']);

                    $this->db->prepare("
                        INSERT INTO calendario_fiscal (obrigacao_id, ano, mes, data_vencimento, status)
                        VALUES (?, ?, ?, ?, 'pendente')
                    ")->execute([$o['id'], $ano, $mes, $dataVencimento]);
                    $gerados++;
                }
            }
        } catch (\PDOException) {
        }
        return $gerados;
    }

    public function getCalendario(int $ano, ?int $mes = null, ?string $orgao = null): array
    {
        try {
            if (!$this->tableExists('calendario_fiscal')) return [];
            $sql = "
                SELECT c.*, o.nome as obrigacao_nome, o.orgao, o.periodicidade,
                       o.forma_entrega, o.base_legal, o.dia_vencimento
                FROM calendario_fiscal c
                JOIN obrigacoes_fiscais o ON c.obrigacao_id = o.id
                WHERE c.ano = ?
            ";
            $params = [$ano];
            if ($mes) { $sql .= " AND c.mes = ?"; $params[] = $mes; }
            if ($orgao) { $sql .= " AND o.orgao = ?"; $params[] = $orgao; }
            $sql .= " ORDER BY c.data_vencimento ASC, o.nome ASC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\PDOException) {
            return [];
        }
    }

    public function atualizarStatusCalendario(int $id, string $status, ?string $observacoes = null, ?int $usuarioId = null): bool
    {
        try {
            $stmt = $this->db->prepare("
                UPDATE calendario_fiscal SET
                    status = ?,
                    data_entrega = IF(? = 'entregue', CURDATE(), data_entrega),
                    observacoes = COALESCE(?, observacoes),
                    entregue_por = IF(? = 'entregue', ?, entregue_por)
                WHERE id = ?
            ");
            return $stmt->execute([$status, $status, $observacoes, $status, $usuarioId, $id]);
        } catch (\PDOException) {
            return false;
        }
    }

    // ========================
    // ALERTAS
    // ========================

    public function getAlertas(?int $usuarioId = null, bool $apenasNaoLidos = false): array
    {
        try {
            if (!$this->tableExists('alertas_fiscais')) return [];
            $sql = "SELECT a.* FROM alertas_fiscais a WHERE 1=1";
            $params = [];

            if ($usuarioId) {
                $sql .= " AND (a.usuario_id IS NULL OR a.usuario_id = ?)";
                $params[] = $usuarioId;
            }
            if ($apenasNaoLidos) {
                $sql .= " AND a.lido = 0";
            }
            $sql .= " ORDER BY a.prioridade ASC, a.created_at DESC LIMIT 50";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\PDOException) {
            return [];
        }
    }

    public function gerarAlertasVencimento(): int
    {
        $gerados = 0;
        try {
            if (!$this->tableExists('calendario_fiscal') || !$this->tableExists('alertas_fiscais')) return 0;

            $stmt = $this->db->query("
                SELECT c.*, o.nome as obrigacao_nome, o.orgao
                FROM calendario_fiscal c
                JOIN obrigacoes_fiscais o ON c.obrigacao_id = o.id
                WHERE c.status IN ('pendente', 'atrasado')
                  AND c.data_vencimento <= DATE_ADD(CURDATE(), INTERVAL 15 DAY)
                  AND c.id NOT IN (
                      SELECT calendario_id FROM alertas_fiscais
                      WHERE tipo IN ('vencimento_proximo', 'atrasado')
                        AND calendario_id IS NOT NULL
                        AND created_at >= DATE_SUB(CURDATE(), INTERVAL 1 DAY)
                  )
                ORDER BY c.data_vencimento ASC
            ");
            $calendarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($calendarios as $c) {
                $diasRestantes = (strtotime($c['data_vencimento']) - time()) / 86400;
                $diasRestantes = (int)ceil($diasRestantes);

                if ($diasRestantes < 0) {
                    $tipo = 'atrasado';
                    $prioridade = 'critica';
                    $titulo = 'ATRASADO: ' . $c['obrigacao_nome'];
                    $mensagem = "O prazo da obrigação {$c['obrigacao_nome']} venceu há " . abs($diasRestantes) . " dia(s).";
                } elseif ($diasRestantes <= 5) {
                    $tipo = 'vencimento_proximo';
                    $prioridade = 'alta';
                    $titulo = 'Vence em breve: ' . $c['obrigacao_nome'];
                    $mensagem = "A obrigação {$c['obrigacao_nome']} vence em $diasRestantes dia(s) em {$c['data_vencimento']}.";
                } elseif ($diasRestantes <= 15) {
                    $tipo = 'vencimento_proximo';
                    $prioridade = 'media';
                    $titulo = 'Atenção: ' . $c['obrigacao_nome'];
                    $mensagem = "A obrigação {$c['obrigacao_nome']} vence em $diasRestantes dia(s).";
                } else {
                    continue;
                }

                $this->db->prepare("
                    INSERT INTO alertas_fiscais (calendario_id, tipo, titulo, mensagem, prioridade)
                    VALUES (?, ?, ?, ?, ?)
                ")->execute([$c['id'], $tipo, $titulo, $mensagem, $prioridade]);
                $gerados++;
            }
        } catch (\PDOException) {
        }
        return $gerados;
    }

    public function marcarAlertaComoLido(int $id): bool
    {
        try {
            $stmt = $this->db->prepare("UPDATE alertas_fiscais SET lido = 1 WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (\PDOException) {
            return false;
        }
    }

    // ========================
    // DASHBOARD
    // ========================

    public function getResumo(): array
    {
        try {
            $resumo = [
                'total_obrigacoes' => 0,
                'pendentes' => 0,
                'entregues' => 0,
                'atrasados' => 0,
                'alertas_nao_lidos' => 0,
                'proximos_vencimentos' => [],
            ];

            if ($this->tableExists('obrigacoes_fiscais')) {
                $stmt = $this->db->query("SELECT COUNT(*) FROM obrigacoes_fiscais WHERE ativo = 1");
                $resumo['total_obrigacoes'] = (int)$stmt->fetchColumn();
            }

            if ($this->tableExists('calendario_fiscal')) {
                $ano = (int)date('Y');
                $stmt = $this->db->prepare("SELECT COUNT(*) FROM calendario_fiscal WHERE ano = ? AND status = 'pendente'");
                $stmt->execute([$ano]);
                $resumo['pendentes'] = (int)$stmt->fetchColumn();

                $stmt = $this->db->prepare("SELECT COUNT(*) FROM calendario_fiscal WHERE ano = ? AND status = 'entregue'");
                $stmt->execute([$ano]);
                $resumo['entregues'] = (int)$stmt->fetchColumn();

                $stmt = $this->db->prepare("SELECT COUNT(*) FROM calendario_fiscal WHERE ano = ? AND status = 'atrasado'");
                $stmt->execute([$ano]);
                $resumo['atrasados'] = (int)$stmt->fetchColumn();

                $stmt = $this->db->prepare("
                    SELECT c.data_vencimento, o.nome, o.orgao
                    FROM calendario_fiscal c
                    JOIN obrigacoes_fiscais o ON c.obrigacao_id = o.id
                    WHERE c.status IN ('pendente','atrasado')
                      AND c.data_vencimento <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
                    ORDER BY c.data_vencimento ASC
                    LIMIT 10
                ");
                $stmt->execute();
                $resumo['proximos_vencimentos'] = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
            }

            if ($this->tableExists('alertas_fiscais')) {
                $stmt = $this->db->query("SELECT COUNT(*) FROM alertas_fiscais WHERE lido = 0");
                $resumo['alertas_nao_lidos'] = (int)$stmt->fetchColumn();
            }

            return $resumo;
        } catch (\PDOException) {
            return ['total_obrigacoes' => 0, 'pendentes' => 0, 'entregues' => 0, 'atrasados' => 0, 'alertas_nao_lidos' => 0, 'proximos_vencimentos' => []];
        }
    }

    // ========================
    // HELPERS
    // ========================

    private function getMesesPeriodicidade(string $periodicidade, int $ano, ?int $mesReferencia): array
    {
        switch ($periodicidade) {
            case 'mensal': return range(1, 12);
            case 'bimestral': return [1, 3, 5, 7, 9, 11];
            case 'trimestral': return [1, 4, 7, 10];
            case 'semestral': return [1, 7];
            case 'anual': return $mesReferencia ? [$mesReferencia] : [1];
            case 'eventual': return $mesReferencia ? [$mesReferencia] : [12];
            default: return [];
        }
    }

    private function calcularDataVencimento(int $ano, int $mes, int $dia): string
    {
        $data = "{$ano}-{$mes}-{$dia}";
        $timestamp = strtotime($data);
        if ($timestamp === false) {
            $ultimoDia = date('t', strtotime("{$ano}-{$mes}-01"));
            $data = "{$ano}-{$mes}-{$ultimoDia}";
        }
        return $data;
    }
}
