<?php

namespace App\Models;

use App\Core\Model;
use PDO;
use PDOException;

class ContabilModel extends Model
{
    private function tableExists(string $table): bool
    {
        try {
            $this->db->query("SELECT 1 FROM {$table} LIMIT 0");
            return true;
        } catch (PDOException) {
            return false;
        }
    }

    private function columnExists(string $table, string $column): bool
    {
        try {
            $stmt = $this->db->query("SHOW COLUMNS FROM {$table} LIKE '{$column}'");
            return (bool)$stmt->fetch();
        } catch (PDOException) {
            return false;
        }
    }

    public function ensureColumns(): void
    {
        if (!$this->columnExists('lancamentos_contabeis', 'debito_conta_id')) {
            $this->db->exec("ALTER TABLE lancamentos_contabeis
                ADD COLUMN debito_conta_id INT AFTER conta,
                ADD COLUMN credito_conta_id INT AFTER debito_conta_id,
                ADD COLUMN origem ENUM('manual','financeiro','folha','estoque','contrato') DEFAULT 'manual' AFTER credito_conta_id,
                ADD COLUMN origem_id INT AFTER origem,
                ADD COLUMN conciliado TINYINT(1) DEFAULT 0 AFTER observacoes,
                ADD COLUMN usuario_id INT AFTER conciliado");
        }
    }

    // ========================
    // PLANO DE CONTAS
    // ========================

    public function getPlanosContas(): array
    {
        try {
            if (!$this->tableExists('plano_contas')) return [];
            $stmt = $this->db->query("
                SELECT p.*, c.nome as conta_pai_nome
                FROM plano_contas p
                LEFT JOIN plano_contas c ON p.conta_pai_id = c.id
                ORDER BY p.codigo ASC
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException) {
            return [];
        }
    }

    public function getPlanoContasTree(): array
    {
        $contas = $this->getPlanosContas();
        $tree = [];
        $grouped = [];

        foreach ($contas as $c) {
            $grouped[$c['conta_pai_id'] ?? 0][] = $c;
        }

        $this->buildTree(0, $grouped, $tree, 0);
        return $tree;
    }

    private function buildTree(int $parentId, array &$grouped, array &$result, int $level): void
    {
        if (!isset($grouped[$parentId])) return;
        foreach ($grouped[$parentId] as $c) {
            $c['nivel'] = $level;
            $result[] = $c;
            $this->buildTree((int)$c['id'], $grouped, $result, $level + 1);
        }
    }

    public function getPlanoContaById(int $id): ?array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM plano_contas WHERE id = ?");
            $stmt->execute([$id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (PDOException) {
            return null;
        }
    }

    public function salvarPlanoConta(array $dados): bool
    {
        try {
            if (!empty($dados['id'])) {
                $sql = "UPDATE plano_contas SET codigo=?, nome=?, tipo=?, natureza=?, conta_pai_id=?, ativo=? WHERE id=?";
                $stmt = $this->db->prepare($sql);
                return $stmt->execute([
                    $dados['codigo'], $dados['nome'], $dados['tipo'],
                    $dados['natureza'], $dados['conta_pai_id'] ?: null,
                    $dados['ativo'] ?? 1, $dados['id']
                ]);
            }
            $sql = "INSERT INTO plano_contas (codigo, nome, tipo, natureza, conta_pai_id, ativo) VALUES (?,?,?,?,?,?)";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                $dados['codigo'], $dados['nome'], $dados['tipo'],
                $dados['natureza'], $dados['conta_pai_id'] ?: null,
                $dados['ativo'] ?? 1
            ]);
        } catch (PDOException) {
            return false;
        }
    }

    public function excluirPlanoConta(int $id): bool
    {
        try {
            $check = $this->db->prepare("SELECT COUNT(*) FROM plano_contas WHERE conta_pai_id = ?");
            $check->execute([$id]);
            if ($check->fetchColumn() > 0) return false;

            $check2 = $this->db->prepare("SELECT COUNT(*) FROM lancamentos_contabeis WHERE debito_conta_id = ? OR credito_conta_id = ?");
            $check2->execute([$id, $id]);
            if ($check2->fetchColumn() > 0) return false;

            $stmt = $this->db->prepare("DELETE FROM plano_contas WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (PDOException) {
            return false;
        }
    }

    // ========================
    // LANÇAMENTOS CONTÁBEIS
    // ========================

    public function getLancamentos(int $limit = 100, int $offset = 0, array $filters = []): array
    {
        try {
            if (!$this->tableExists('lancamentos_contabeis')) return [];

            $sql = "SELECT l.*, d.nome as debito_conta_nome, c.nome as credito_conta_nome, u.nome as usuario_nome
                    FROM lancamentos_contabeis l
                    LEFT JOIN plano_contas d ON l.debito_conta_id = d.id
                    LEFT JOIN plano_contas c ON l.credito_conta_id = c.id
                    LEFT JOIN usuarios u ON l.usuario_id = u.id
                    WHERE 1=1";

            $params = [];
            if (!empty($filters['data_inicio'])) {
                $sql .= " AND l.data_lancamento >= ?";
                $params[] = $filters['data_inicio'];
            }
            if (!empty($filters['data_fim'])) {
                $sql .= " AND l.data_lancamento <= ?";
                $params[] = $filters['data_fim'];
            }
            if (!empty($filters['origem'])) {
                $sql .= " AND l.origem = ?";
                $params[] = $filters['origem'];
            }
            if (isset($filters['conciliado'])) {
                $sql .= " AND l.conciliado = ?";
                $params[] = $filters['conciliado'];
            }

            $sql .= " ORDER BY l.data_lancamento DESC, l.id DESC LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException) {
            return [];
        }
    }

    public function getLancamentoById(int $id): ?array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT l.*, d.nome as debito_conta_nome, c.nome as credito_conta_nome
                FROM lancamentos_contabeis l
                LEFT JOIN plano_contas d ON l.debito_conta_id = d.id
                LEFT JOIN plano_contas c ON l.credito_conta_id = c.id
                WHERE l.id = ?
            ");
            $stmt->execute([$id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (PDOException) {
            return null;
        }
    }

    public function salvarLancamento(array $dados): bool
    {
        try {
            $this->ensureColumns();

            if (!empty($dados['id'])) {
                $sql = "UPDATE lancamentos_contabeis SET
                        descricao=?, valor=?, tipo=?, categoria=?, data_lancamento=?,
                        conta=?, centro_custo=?, debito_conta_id=?, credito_conta_id=?,
                        origem=?, origem_id=?, observacoes=?, conciliado=?, usuario_id=?
                        WHERE id=?";
                $stmt = $this->db->prepare($sql);
                return $stmt->execute([
                    $dados['descricao'], $dados['valor'], $dados['tipo'] ?? 'debito',
                    $dados['categoria'] ?? null, $dados['data_lancamento'],
                    $dados['conta'] ?? null, $dados['centro_custo'] ?? null,
                    $dados['debito_conta_id'] ?: null, $dados['credito_conta_id'] ?: null,
                    $dados['origem'] ?? 'manual', $dados['origem_id'] ?? null,
                    $dados['observacoes'] ?? null, $dados['conciliado'] ?? 0,
                    $dados['usuario_id'] ?? null, $dados['id']
                ]);
            }

            $sql = "INSERT INTO lancamentos_contabeis
                    (descricao, valor, tipo, categoria, data_lancamento, conta, centro_custo,
                     debito_conta_id, credito_conta_id, origem, origem_id, observacoes, conciliado, usuario_id)
                    VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                $dados['descricao'], $dados['valor'], $dados['tipo'] ?? 'debito',
                $dados['categoria'] ?? null, $dados['data_lancamento'],
                $dados['conta'] ?? null, $dados['centro_custo'] ?? null,
                $dados['debito_conta_id'] ?: null, $dados['credito_conta_id'] ?: null,
                $dados['origem'] ?? 'manual', $dados['origem_id'] ?? null,
                $dados['observacoes'] ?? null, $dados['conciliado'] ?? 0,
                $dados['usuario_id'] ?? null
            ]);
        } catch (PDOException) {
            return false;
        }
    }

    public function excluirLancamento(int $id): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM lancamentos_contabeis WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (PDOException) {
            return false;
        }
    }

    // ========================
    // LANÇAMENTOS AUTOMÁTICOS
    // ========================

    public function integrarTransacoesFinanceiras(?int $mes = null, ?int $ano = null): array
    {
        $mes = $mes ?? (int)date('m');
        $ano = $ano ?? (int)date('Y');
        $importados = 0;

        try {
            $this->ensureColumns();
            if (!$this->tableExists('transacoes') || !$this->tableExists('plano_contas')) {
                return ['importados' => 0, 'message' => 'Tabelas necessárias não encontradas'];
            }

            $stmt = $this->db->prepare("
                SELECT t.*, tc.nome as categoria_nome,
                       COALESCE(tc.tipo, t.tipo) as tipo_transacao
                FROM transacoes t
                LEFT JOIN transacao_classificacoes tc ON t.classificacao_id = tc.id
                WHERE MONTH(t.vencimento) = ? AND YEAR(t.vencimento) = ?
                  AND t.status IN ('Pago', 'Pago Parcial', 'Pendente')
                  AND t.tipo IN ('R', 'P')
                  AND (t.documento_vinculado IS NULL OR t.documento_vinculado NOT LIKE 'transfer_%')
                ORDER BY t.vencimento ASC
            ");
            $stmt->execute([$mes, $ano]);
            $transacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $contasCache = $this->getContasCache();

            foreach ($transacoes as $t) {
                $jaExiste = $this->db->prepare(
                    "SELECT COUNT(*) FROM lancamentos_contabeis WHERE origem = 'financeiro' AND origem_id = ?"
                );
                $jaExiste->execute([$t['id']]);
                if ($jaExiste->fetchColumn() > 0) continue;

                $contaDebito = $t['tipo'] === 'R'
                    ? ($contasCache['1.1.1.01.001'] ?? null)
                    : ($contasCache['3.1.1.01.001'] ?? null);
                $contaCredito = $t['tipo'] === 'R'
                    ? ($contasCache['3.1.1.01.001'] ?? null)
                    : ($contasCache['1.1.1.01.001'] ?? null);

                $this->db->prepare("
                    INSERT INTO lancamentos_contabeis
                    (descricao, valor, tipo, categoria, data_lancamento, debito_conta_id, credito_conta_id,
                     origem, origem_id, observacoes, conciliado, usuario_id)
                    VALUES (?, ?, ?, ?, ?, ?, ?, 'financeiro', ?, ?, 0, ?)
                ")->execute([
                    $t['descricao'] ?: 'Lançamento automático - ' . ($t['categoria_nome'] ?? 'Financeiro'),
                    $t['valor'],
                    $t['tipo'] === 'R' ? 'credito' : 'debito',
                    $t['categoria_nome'] ?? ($t['categoria'] ?? 'Financeiro'),
                    $t['vencimento'],
                    $contaDebito,
                    $contaCredito,
                    $t['id'],
                    'Integrado automaticamente do módulo financeiro. Transação #' . $t['id'],
                    $t['usuario_id'] ?? null
                ]);
                $importados++;
            }
        } catch (PDOException $e) {
            return ['importados' => $importados, 'message' => 'Erro: ' . $e->getMessage()];
        }

        return ['importados' => $importados, 'message' => "$importados lançamentos integrados com sucesso."];
    }

    public function integrarFolhaPagamento(?int $mes = null, ?int $ano = null): array
    {
        $mes = $mes ?? (int)date('m');
        $ano = $ano ?? (int)date('Y');
        $importados = 0;

        try {
            $this->ensureColumns();
            if (!$this->tableExists('folha_pagamento')) {
                return ['importados' => 0, 'message' => 'Tabela folha_pagamento não encontrada'];
            }

            $stmt = $this->db->prepare("
                SELECT f.*, c.nome as colaborador_nome
                FROM folha_pagamento f
                LEFT JOIN colaboradores c ON f.colaborador_id = c.id
                WHERE MONTH(f.data_pagamento) = ? AND YEAR(f.data_pagamento) = ?
                  AND f.status = 'pago'
                ORDER BY f.data_pagamento ASC
            ");
            $stmt->execute([$mes, $ano]);
            $folhas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $contasCache = $this->getContasCache();

            foreach ($folhas as $f) {
                $jaExiste = $this->db->prepare(
                    "SELECT COUNT(*) FROM lancamentos_contabeis WHERE origem = 'folha' AND origem_id = ?"
                );
                $jaExiste->execute([$f['id']]);
                if ($jaExiste->fetchColumn() > 0) continue;

                $contaDespesa = $contasCache['3.1.1.01.002'] ?? null;
                $contaBanco = $contasCache['1.1.1.01.001'] ?? null;

                $this->db->prepare("
                    INSERT INTO lancamentos_contabeis
                    (descricao, valor, tipo, categoria, data_lancamento, debito_conta_id, credito_conta_id,
                     origem, origem_id, observacoes, conciliado)
                    VALUES (?, ?, 'debito', 'Folha de Pagamento', ?, ?, ?, 'folha', ?, ?, 0)
                ")->execute([
                    'Folha de Pagamento - ' . $mes . '/' . $ano . ' - ' . ($f['colaborador_nome'] ?? 'Colaborador'),
                    $f['valor_liquido'] ?? $f['valor'] ?? 0,
                    $f['data_pagamento'],
                    $contaDespesa,
                    $contaBanco,
                    $f['id'],
                    'Integrado automaticamente da folha de pagamento.'
                ]);
                $importados++;
            }
        } catch (PDOException $e) {
            return ['importados' => $importados, 'message' => 'Erro: ' . $e->getMessage()];
        }

        return ['importados' => $importados, 'message' => "$importados lançamentos integrados da folha."];
    }

    public function integrarContratos(?int $mes = null, ?int $ano = null): array
    {
        $mes = $mes ?? (int)date('m');
        $ano = $ano ?? (int)date('Y');
        $importados = 0;

        try {
            $this->ensureColumns();
            if (!$this->tableExists('contrato_parcelas')) {
                return ['importados' => 0, 'message' => 'Tabela contrato_parcelas não encontrada'];
            }

            $stmt = $this->db->prepare("
                SELECT cp.*, c.titulo as contrato_titulo, c.valor as contrato_valor
                FROM contrato_parcelas cp
                LEFT JOIN contratos c ON cp.contrato_id = c.id
                WHERE MONTH(cp.data_vencimento) = ? AND YEAR(cp.data_vencimento) = ?
                  AND cp.status = 'pago'
                ORDER BY cp.data_vencimento ASC
            ");
            $stmt->execute([$mes, $ano]);
            $parcelas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $contasCache = $this->getContasCache();

            foreach ($parcelas as $p) {
                $jaExiste = $this->db->prepare(
                    "SELECT COUNT(*) FROM lancamentos_contabeis WHERE origem = 'contrato' AND origem_id = ?"
                );
                $jaExiste->execute([$p['id']]);
                if ($jaExiste->fetchColumn() > 0) continue;

                $contaReceita = $contasCache['3.1.1.01.001'] ?? null;
                $contaBanco = $contasCache['1.1.1.01.001'] ?? null;

                $this->db->prepare("
                    INSERT INTO lancamentos_contabeis
                    (descricao, valor, tipo, categoria, data_lancamento, debito_conta_id, credito_conta_id,
                     origem, origem_id, observacoes, conciliado)
                    VALUES (?, ?, 'credito', 'Contratos', ?, ?, ?, 'contrato', ?, ?, 0)
                ")->execute([
                    'Receita de Contrato - ' . ($p['contrato_titulo'] ?? 'Contrato #' . $p['contrato_id']),
                    $p['valor'],
                    $p['data_vencimento'],
                    $contaBanco,
                    $contaReceita,
                    $p['id'],
                    'Integrado automaticamente via módulo de Contratos.'
                ]);
                $importados++;
            }
        } catch (PDOException $e) {
            return ['importados' => $importados, 'message' => 'Erro: ' . $e->getMessage()];
        }

        return ['importados' => $importados, 'message' => "$importados lançamentos integrados de contratos."];
    }

    public function integrarEstoque(?int $limite = 500): array
    {
        $importados = 0;

        try {
            $this->ensureColumns();
            if (!$this->tableExists('estoque_movimentos') || !$this->tableExists('plano_contas')) {
                return ['importados' => 0, 'message' => 'Tabelas necessárias não encontradas'];
            }

            $contasCache = $this->getContasCache();
            $contaEstoque = $contasCache['1.1.3.01.001'] ?? null;
            $contaCmv = $contasCache['3.2.1.01.001'] ?? null;
            $contaFornecedor = $contasCache['2.1.1.01.001'] ?? null;
            $contaCaixa = $contasCache['1.1.1.01.001'] ?? null;

            $stmt = $this->db->prepare("
                SELECT m.*, p.nome as produto_nome, p.codigo as produto_codigo
                FROM estoque_movimentos m
                LEFT JOIN produtos p ON m.produto_id = p.id
                WHERE m.id NOT IN (
                    SELECT origem_id FROM lancamentos_contabeis WHERE origem = 'estoque' AND origem_id IS NOT NULL
                )
                ORDER BY m.data_movimento ASC
                LIMIT ?
            ");
            $stmt->execute([$limite]);
            $movimentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($movimentos as $m) {
                $descricao = ($m['tipo_movimento'] === 'entrada' ? 'Entrada' : 'Saída')
                    . ' - ' . ($m['produto_nome'] ?? 'Produto #' . $m['produto_id']);

                $valorTotal = abs((float)$m['valor_total']);
                if ($valorTotal <= 0) continue;

                if ($m['tipo_movimento'] === 'entrada') {
                    $contaDebito = $contaEstoque;
                    $contaCredito = $m['documento'] ? $contaFornecedor : $contaCaixa;
                    $tipo = 'debito';
                } else {
                    $contaDebito = $contaCmv;
                    $contaCredito = $contaEstoque;
                    $tipo = 'debito';
                }

                $this->db->prepare("
                    INSERT INTO lancamentos_contabeis
                    (descricao, valor, tipo, categoria, data_lancamento, debito_conta_id, credito_conta_id,
                     origem, origem_id, observacoes, conciliado)
                    VALUES (?, ?, ?, 'Estoque', ?, ?, ?, 'estoque', ?, ?, 0)
                ")->execute([
                    $descricao,
                    $valorTotal,
                    $tipo,
                    $m['data_movimento'],
                    $contaDebito,
                    $contaCredito,
                    $m['id'],
                    'Integrado automaticamente do módulo de Estoque. Movimento #' . $m['id']
                ]);
                $importados++;
            }
        } catch (PDOException $e) {
            return ['importados' => $importados, 'message' => 'Erro: ' . $e->getMessage()];
        }

        return ['importados' => $importados, 'message' => "$importados movimentos de estoque integrados."];
    }

    public function getMappingContabil(): array
    {
        $mappings = [];
        try {
            if (!$this->tableExists('conta_contabil_mapping')) return [];
            $stmt = $this->db->query("
                SELECT m.*, c.codigo as conta_codigo, c.nome as conta_nome
                FROM conta_contabil_mapping m
                LEFT JOIN plano_contas c ON m.conta_contabil_id = c.id
                ORDER BY m.origem, m.tipo_origem
            ");
            $mappings = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException) {
        }
        return $mappings;
    }

    public function salvarMappingContabil(array $dados): bool
    {
        try {
            if (!$this->tableExists('conta_contabil_mapping')) return false;

            if (!empty($dados['id'])) {
                $stmt = $this->db->prepare("
                    UPDATE conta_contabil_mapping SET
                        origem = ?, tipo_origem = ?, conta_contabil_id = ?,
                        natureza = ?, ativo = ?, updated_at = NOW()
                    WHERE id = ?
                ");
                return $stmt->execute([
                    $dados['origem'], $dados['tipo_origem'], $dados['conta_contabil_id'],
                    $dados['natureza'] ?? 'debito', $dados['ativo'] ?? 1, $dados['id']
                ]);
            } else {
                $stmt = $this->db->prepare("
                    INSERT INTO conta_contabil_mapping
                    (origem, tipo_origem, conta_contabil_id, natureza, ativo, created_at, updated_at)
                    VALUES (?, ?, ?, ?, ?, NOW(), NOW())
                ");
                return $stmt->execute([
                    $dados['origem'], $dados['tipo_origem'], $dados['conta_contabil_id'],
                    $dados['natureza'] ?? 'debito', $dados['ativo'] ?? 1
                ]);
            }
        } catch (PDOException) {
            return false;
        }
    }

    private function getContasCache(): array
    {
        $cache = [];
        try {
            $stmt = $this->db->query("SELECT codigo, id FROM plano_contas WHERE ativo = 1");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $cache[$row['codigo']] = (int)$row['id'];
            }
        } catch (PDOException) {
        }
        return $cache;
    }

    // ========================
    // DEMONSTRAÇÕES CONTÁBEIS
    // ========================

    public function getBalancoPatrimonial(?int $ano = null): array
    {
        $ano = $ano ?? (int)date('Y');
        try {
            if (!$this->tableExists('lancamentos_contabeis') || !$this->tableExists('plano_contas')) {
                return ['ativo' => [], 'passivo' => [], 'patrimonio_liquido' => []];
            }

            $stmt = $this->db->prepare("
                SELECT
                    pc.id, pc.codigo, pc.nome, pc.natureza, pc.tipo, pc.conta_pai_id,
                    COALESCE(SUM(CASE WHEN l.tipo = 'debito' THEN l.valor ELSE 0 END), 0) as total_debito,
                    COALESCE(SUM(CASE WHEN l.tipo = 'credito' THEN l.valor ELSE 0 END), 0) as total_credito
                FROM plano_contas pc
                LEFT JOIN lancamentos_contabeis l ON (pc.id = l.debito_conta_id OR pc.id = l.credito_conta_id)
                    AND YEAR(l.data_lancamento) = ?
                WHERE pc.ativo = 1
                GROUP BY pc.id, pc.codigo, pc.nome, pc.natureza, pc.tipo, pc.conta_pai_id
                ORDER BY pc.codigo ASC
            ");
            $stmt->execute([$ano]);
            $contas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $resultado = [];
            foreach ($contas as $c) {
                $saldo = ($c['natureza'] === 'devedora')
                    ? ($c['total_debito'] - $c['total_credito'])
                    : ($c['total_credito'] - $c['total_debito']);

                if (strpos($c['codigo'], '1.') === 0) {
                    $resultado['ativo'][] = $c + ['saldo' => $saldo];
                } elseif (strpos($c['codigo'], '2.') === 0) {
                    $resultado['passivo'][] = $c + ['saldo' => $saldo];
                } elseif (strpos($c['codigo'], '3.') === 0) {
                    $resultado['patrimonio_liquido'][] = $c + ['saldo' => $saldo];
                } else {
                    $resultado['outras'][] = $c + ['saldo' => $saldo];
                }
            }

            return $resultado;
        } catch (PDOException) {
            return ['ativo' => [], 'passivo' => [], 'patrimonio_liquido' => []];
        }
    }

    public function getDRE(?int $ano = null): array
    {
        $ano = $ano ?? (int)date('Y');
        try {
            if (!$this->tableExists('lancamentos_contabeis') || !$this->tableExists('plano_contas')) {
                return ['receitas' => 0, 'despesas' => 0, 'resultado' => 0];
            }

            $stmt = $this->db->prepare("
                SELECT
                    pc.codigo, pc.nome,
                    COALESCE(SUM(CASE WHEN l.tipo = 'credito' THEN l.valor ELSE 0 END), 0) as total_credito,
                    COALESCE(SUM(CASE WHEN l.tipo = 'debito' THEN l.valor ELSE 0 END), 0) as total_debito
                FROM plano_contas pc
                JOIN lancamentos_contabeis l ON (pc.id = l.debito_conta_id OR pc.id = l.credito_conta_id)
                    AND YEAR(l.data_lancamento) = ?
                WHERE pc.ativo = 1 AND (pc.codigo LIKE '3.%' OR pc.codigo LIKE '4.%')
                GROUP BY pc.id
                ORDER BY pc.codigo ASC
            ");
            $stmt->execute([$ano]);
            $contas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $receitas = 0;
            $despesas = 0;

            foreach ($contas as $c) {
                if ($c['codigo'] === '3.1.1.01.001' || strpos($c['codigo'], '3.1') === 0) {
                    $receitas += $c['total_credito'] - $c['total_debito'];
                } elseif (strpos($c['codigo'], '3.2') === 0 || strpos($c['codigo'], '4.') === 0) {
                    $despesas += $c['total_debito'] - $c['total_credito'];
                }
            }

            return [
                'receitas' => $receitas,
                'despesas' => $despesas,
                'resultado' => $receitas - $despesas
            ];
        } catch (PDOException) {
            return ['receitas' => 0, 'despesas' => 0, 'resultado' => 0];
        }
    }

    public function getFluxoCaixa(?int $ano = null): array
    {
        $ano = $ano ?? (int)date('Y');
        try {
            if (!$this->tableExists('lancamentos_contabeis')) return [];

            $stmt = $this->db->prepare("
                SELECT
                    DATE_FORMAT(data_lancamento, '%Y-%m') as mes,
                    SUM(CASE WHEN tipo = 'credito' THEN valor ELSE 0 END) as entradas,
                    SUM(CASE WHEN tipo = 'debito' THEN valor ELSE 0 END) as saidas
                FROM lancamentos_contabeis
                WHERE YEAR(data_lancamento) = ?
                GROUP BY DATE_FORMAT(data_lancamento, '%Y-%m')
                ORDER BY mes ASC
            ");
            $stmt->execute([$ano]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

            $fluxo = [];
            $acumulado = 0;
            for ($m = 1; $m <= 12; $m++) {
                $mesStr = sprintf('%04d-%02d', $ano, $m);
                $found = false;
                foreach ($results as $r) {
                    if ($r['mes'] === $mesStr) {
                        $entradas = (float)$r['entradas'];
                        $saidas = (float)$r['saidas'];
                        $acumulado += $entradas - $saidas;
                        $fluxo[] = [
                            'mes' => $mesStr,
                            'entradas' => $entradas,
                            'saidas' => $saidas,
                            'saldo' => $entradas - $saidas,
                            'acumulado' => $acumulado
                        ];
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    $fluxo[] = [
                        'mes' => $mesStr,
                        'entradas' => 0,
                        'saidas' => 0,
                        'saldo' => 0,
                        'acumulado' => $acumulado
                    ];
                }
            }

            return $fluxo;
        } catch (PDOException) {
            return [];
        }
    }

    // ========================
    // CONCILIAÇÃO BANCÁRIA
    // ========================

    public function getConciliacoes(int $limit = 50): array
    {
        try {
            if (!$this->tableExists('conciliacao_bancaria')) return [];
            $stmt = $this->db->query("
                SELECT c.*, b.nome as banco_nome, u.nome as usuario_nome
                FROM conciliacao_bancaria c
                LEFT JOIN bancos b ON c.banco_id = b.id
                LEFT JOIN usuarios u ON c.usuario_id = u.id
                ORDER BY c.created_at DESC
                LIMIT $limit
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException) {
            return [];
        }
    }

    public function getConciliacaoById(int $id): ?array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT c.*, b.nome as banco_nome
                FROM conciliacao_bancaria c
                LEFT JOIN bancos b ON c.banco_id = b.id
                WHERE c.id = ?
            ");
            $stmt->execute([$id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (PDOException) {
            return null;
        }
    }

    public function getItensConciliacao(int $conciliacaoId): array
    {
        try {
            if (!$this->tableExists('conciliacao_itens')) return [];
            $stmt = $this->db->prepare("
                SELECT ci.*, t.descricao as transacao_descricao, t.valor as transacao_valor,
                       t.status as transacao_status, t.data_pagamento
                FROM conciliacao_itens ci
                LEFT JOIN transacoes t ON ci.transacao_id = t.id
                WHERE ci.conciliacao_id = ?
                ORDER BY ci.data_operacao ASC
            ");
            $stmt->execute([$conciliacaoId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException) {
            return [];
        }
    }

    public function salvarConciliacao(array $dados): int
    {
        try {
            if (!empty($dados['id'])) {
                $sql = "UPDATE conciliacao_bancaria SET
                        banco_id=?, periodo_inicio=?, periodo_fim=?, saldo_extrato=?,
                        saldo_sistema=?, diferenca=?, status=?, observacoes=?
                        WHERE id=?";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([
                    $dados['banco_id'], $dados['periodo_inicio'], $dados['periodo_fim'],
                    $dados['saldo_extrato'], $dados['saldo_sistema'], $dados['diferenca'],
                    $dados['status'], $dados['observacoes'] ?? null, $dados['id']
                ]);
                return (int)$dados['id'];
            }

            $sql = "INSERT INTO conciliacao_bancaria
                    (banco_id, periodo_inicio, periodo_fim, saldo_extrato, saldo_sistema, diferenca, status, observacoes, usuario_id)
                    VALUES (?,?,?,?,?,?,?,?,?)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $dados['banco_id'], $dados['periodo_inicio'], $dados['periodo_fim'],
                $dados['saldo_extrato'] ?? 0, $dados['saldo_sistema'] ?? 0,
                $dados['diferenca'] ?? 0, $dados['status'] ?? 'aberta',
                $dados['observacoes'] ?? null, $dados['usuario_id'] ?? null
            ]);
            return (int)$this->db->lastInsertId();
        } catch (PDOException) {
            return 0;
        }
    }

    public function adicionarItemConciliacao(array $dados): bool
    {
        try {
            $sql = "INSERT INTO conciliacao_itens
                    (conciliacao_id, transacao_id, tipo, data_operacao, descricao, valor, status_conciliacao, observacoes)
                    VALUES (?,?,?,?,?,?,?,?)";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                $dados['conciliacao_id'], $dados['transacao_id'] ?? null,
                $dados['tipo'], $dados['data_operacao'], $dados['descricao'],
                $dados['valor'], $dados['status_conciliacao'] ?? 'pendente',
                $dados['observacoes'] ?? null
            ]);
        } catch (PDOException) {
            return false;
        }
    }

    public function conciliarItem(int $itemId): bool
    {
        try {
            $stmt = $this->db->prepare("UPDATE conciliacao_itens SET status_conciliacao = 'conciliado' WHERE id = ?");
            return $stmt->execute([$itemId]);
        } catch (PDOException) {
            return false;
        }
    }

    public function finalizarConciliacao(int $conciliacaoId): bool
    {
        try {
            $stmt = $this->db->prepare("
                UPDATE conciliacao_bancaria
                SET status = 'conciliada', conciliada_em = NOW()
                WHERE id = ? AND status = 'aberta'
            ");
            return $stmt->execute([$conciliacaoId]);
        } catch (PDOException) {
            return false;
        }
    }

    public function getTransacoesParaConciliacao(int $bancoId, string $dataInicio, string $dataFim): array
    {
        try {
            if (!$this->tableExists('transacoes')) return [];
            $stmt = $this->db->prepare("
                SELECT id, descricao, valor, tipo, vencimento, data_pagamento, status
                FROM transacoes
                WHERE banco_id = ?
                  AND (vencimento BETWEEN ? AND ? OR data_pagamento BETWEEN ? AND ?)
                  AND status != 'Cancelado'
                ORDER BY vencimento ASC
            ");
            $stmt->execute([$bancoId, $dataInicio, $dataFim, $dataInicio, $dataFim]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException) {
            return [];
        }
    }

    // ========================
    // PARÂMETROS CONTÁBEIS
    // ========================

    public function getParametros(): array
    {
        try {
            if (!$this->tableExists('parametros_contabeis')) return [];
            $stmt = $this->db->query("SELECT * FROM parametros_contabeis ORDER BY chave ASC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException) {
            return [];
        }
    }

    public function getParametro(string $chave): ?string
    {
        try {
            $stmt = $this->db->prepare("SELECT valor FROM parametros_contabeis WHERE chave = ?");
            $stmt->execute([$chave]);
            $result = $stmt->fetchColumn();
            return $result !== false ? (string)$result : null;
        } catch (PDOException) {
            return null;
        }
    }

    public function salvarParametro(string $chave, string $valor, string $descricao = ''): bool
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO parametros_contabeis (chave, valor, descricao)
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE valor = VALUES(valor), descricao = VALUES(descricao)
            ");
            return $stmt->execute([$chave, $valor, $descricao]);
        } catch (PDOException) {
            return false;
        }
    }

    public function salvarParametros(array $parametros): bool
    {
        $success = true;
        foreach ($parametros as $chave => $valor) {
            if (!$this->salvarParametro($chave, $valor)) {
                $success = false;
            }
        }
        return $success;
    }

    // ========================
    // RESUMO / DASHBOARD
    // ========================

    public function getResumo(): array
    {
        try {
            $totalContas = 0;
            $totalLancamentos = 0;
            $totalReceitas = 0;
            $totalDespesas = 0;
            $totalConciliacoes = 0;

            if ($this->tableExists('plano_contas')) {
                $stmt = $this->db->query("SELECT COUNT(*) FROM plano_contas WHERE ativo = 1");
                $totalContas = (int)$stmt->fetchColumn();
            }
            if ($this->tableExists('lancamentos_contabeis')) {
                $stmt = $this->db->query("SELECT COUNT(*) FROM lancamentos_contabeis");
                $totalLancamentos = (int)$stmt->fetchColumn();

                $stmt = $this->db->query("SELECT COALESCE(SUM(valor),0) FROM lancamentos_contabeis WHERE tipo = 'credito'");
                $totalReceitas = (float)$stmt->fetchColumn();

                $stmt = $this->db->query("SELECT COALESCE(SUM(valor),0) FROM lancamentos_contabeis WHERE tipo = 'debito'");
                $totalDespesas = (float)$stmt->fetchColumn();
            }
            if ($this->tableExists('conciliacao_bancaria')) {
                $stmt = $this->db->query("SELECT COUNT(*) FROM conciliacao_bancaria WHERE status = 'aberta'");
                $totalConciliacoes = (int)$stmt->fetchColumn();
            }

            return [
                'total_contas' => $totalContas,
                'total_lancamentos' => $totalLancamentos,
                'total_receitas' => $totalReceitas,
                'total_despesas' => $totalDespesas,
                'total_conciliacoes' => $totalConciliacoes,
            ];
        } catch (PDOException) {
            return [
                'total_contas' => 0, 'total_lancamentos' => 0,
                'total_receitas' => 0, 'total_despesas' => 0,
                'total_conciliacoes' => 0
            ];
        }
    }
}
