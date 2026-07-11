<?php

namespace App\Models;

use App\Core\Model;
use PDO;
use PDOException;

class EstoqueModel extends Model
{
    // ========================
    // PRODUTOS
    // ========================

    private function tableExists(string $table): bool
    {
        try {
            $this->db->query("SELECT 1 FROM {$table} LIMIT 0");
            return true;
        } catch (PDOException) {
            return false;
        }
    }

    public function getProdutos(): array
    {
        try {
            if (!$this->tableExists('produtos')) return [];
            $stmt = $this->db->query("
                SELECT p.*, COALESCE(s.quantidade, 0) as saldo_quantidade,
                       COALESCE(s.custo_medio, 0) as saldo_custo_medio,
                       COALESCE(s.valor_total, 0) as saldo_valor_total
                FROM produtos p
                LEFT JOIN estoque_saldo s ON p.id = s.produto_id
                ORDER BY p.nome ASC
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException) {
            return [];
        }
    }

    public function getProdutoById(int $id): ?array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT p.*, COALESCE(s.quantidade, 0) as saldo_quantidade,
                       COALESCE(s.custo_medio, 0) as saldo_custo_medio
                FROM produtos p
                LEFT JOIN estoque_saldo s ON p.id = s.produto_id
                WHERE p.id = ?
            ");
            $stmt->execute([$id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (PDOException) {
            return null;
        }
    }

    public function salvarProduto(array $dados): bool
    {
        try {
            if (!empty($dados['id'])) {
                $sql = "UPDATE produtos SET codigo=?, nome=?, descricao=?, categoria=?, unidade=?,
                        ncm=?, cest=?, aliquota_icms=?, aliquota_ipi=?, aliquota_pis=?, aliquota_cofins=?,
                        custo_aquisicao=?, despesas_acessorias=?, margem_lucro=?, preco_venda=?, ativo=?
                        WHERE id=?";
                $stmt = $this->db->prepare($sql);
                return $stmt->execute([
                    $dados['codigo'], $dados['nome'], $dados['descricao'] ?? null,
                    $dados['categoria'] ?? null, $dados['unidade'] ?? 'UN',
                    $dados['ncm'] ?? null, $dados['cest'] ?? null,
                    $dados['aliquota_icms'] ?? 0, $dados['aliquota_ipi'] ?? 0,
                    $dados['aliquota_pis'] ?? 0, $dados['aliquota_cofins'] ?? 0,
                    $dados['custo_aquisicao'] ?? 0, $dados['despesas_acessorias'] ?? 0,
                    $dados['margem_lucro'] ?? 0, $dados['preco_venda'] ?? 0,
                    $dados['ativo'] ?? 1, $dados['id']
                ]);
            }

            $sql = "INSERT INTO produtos (codigo, nome, descricao, categoria, unidade, ncm, cest,
                    aliquota_icms, aliquota_ipi, aliquota_pis, aliquota_cofins,
                    custo_aquisicao, despesas_acessorias, margem_lucro, preco_venda, ativo)
                    VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                $dados['codigo'], $dados['nome'], $dados['descricao'] ?? null,
                $dados['categoria'] ?? null, $dados['unidade'] ?? 'UN',
                $dados['ncm'] ?? null, $dados['cest'] ?? null,
                $dados['aliquota_icms'] ?? 0, $dados['aliquota_ipi'] ?? 0,
                $dados['aliquota_pis'] ?? 0, $dados['aliquota_cofins'] ?? 0,
                $dados['custo_aquisicao'] ?? 0, $dados['despesas_acessorias'] ?? 0,
                $dados['margem_lucro'] ?? 0, $dados['preco_venda'] ?? 0,
                $dados['ativo'] ?? 1
            ]);

            if ($result) {
                $id = (int)$this->db->lastInsertId();
                $this->initSaldoProduto($id);
            }
            return $result;
        } catch (PDOException) {
            return false;
        }
    }

    public function excluirProduto(int $id): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM produtos WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (PDOException) {
            return false;
        }
    }

    public function getCategorias(): array
    {
        try {
            $stmt = $this->db->query("SELECT DISTINCT categoria FROM produtos WHERE categoria IS NOT NULL AND categoria != '' ORDER BY categoria ASC");
            return $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
        } catch (PDOException) {
            return [];
        }
    }

    // ========================
    // MOVIMENTAÇÕES DE ESTOQUE
    // ========================

    public function getMovimentos(int $produtoId = null, int $limit = 100): array
    {
        try {
            if (!$this->tableExists('estoque_movimentos')) return [];
            $sql = "SELECT m.*, p.nome as produto_nome, p.codigo as produto_codigo
                    FROM estoque_movimentos m
                    JOIN produtos p ON m.produto_id = p.id
                    WHERE 1=1";
            $params = [];
            if ($produtoId) {
                $sql .= " AND m.produto_id = ?";
                $params[] = $produtoId;
            }
            $sql .= " ORDER BY m.data_movimento DESC, m.id DESC LIMIT ?";
            $params[] = $limit;

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException) {
            return [];
        }
    }

    /**
     * Registra entrada em estoque e atualiza custo médio.
     */
    public function registrarEntrada(int $produtoId, float $quantidade, float $valorUnitario, string $data, string $documento = null, string $observacoes = null, int $usuarioId = null): bool
    {
        try {
            $this->db->beginTransaction();

            $valorTotal = round($quantidade * $valorUnitario, 2);

            $stmt = $this->db->prepare("
                INSERT INTO estoque_movimentos (produto_id, tipo_movimento, quantidade, valor_unitario, valor_total, documento, data_movimento, observacoes, usuario_id)
                VALUES (?, 'entrada', ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$produtoId, $quantidade, $valorUnitario, $valorTotal, $documento, $data, $observacoes, $usuarioId]);

            $this->atualizarCustoMedio($produtoId);

            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            error_log("Erro ao registrar entrada: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Registra saída de estoque (custo médio).
     */
    public function registrarSaida(int $produtoId, float $quantidade, string $data, string $documento = null, string $observacoes = null, int $usuarioId = null): bool
    {
        try {
            $saldo = $this->getSaldoProduto($produtoId);
            if (!$saldo || $saldo['quantidade'] < $quantidade) {
                return false;
            }

            $this->db->beginTransaction();

            $valorTotal = round($quantidade * $saldo['custo_medio'], 2);

            $stmt = $this->db->prepare("
                INSERT INTO estoque_movimentos (produto_id, tipo_movimento, quantidade, valor_unitario, valor_total, documento, data_movimento, observacoes, usuario_id)
                VALUES (?, 'saida', ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$produtoId, -$quantidade, $saldo['custo_medio'], $valorTotal, $documento, $data, $observacoes, $usuarioId]);

            $this->atualizarSaldoAposMovimento($produtoId);

            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            error_log("Erro ao registrar saída: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Ajuste manual de estoque (positivo ou negativo).
     */
    public function registrarAjuste(int $produtoId, float $quantidade, float $valorUnitario, string $data, string $observacoes = null, int $usuarioId = null): bool
    {
        try {
            $this->db->beginTransaction();

            $valorTotal = round(abs($quantidade) * $valorUnitario, 2);
            $tipo = $quantidade > 0 ? 'entrada' : 'saida';

            $stmt = $this->db->prepare("
                INSERT INTO estoque_movimentos (produto_id, tipo_movimento, quantidade, valor_unitario, valor_total, data_movimento, observacoes, usuario_id)
                VALUES (?, 'ajuste', ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$produtoId, $quantidade, $valorUnitario, $valorTotal, $data, $observacoes, $usuarioId]);

            $this->atualizarCustoMedio($produtoId);

            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            return false;
        }
    }

    /**
     * Atualiza o custo médio ponderado do produto.
     */
    private function atualizarCustoMedio(int $produtoId): void
    {
        $stmt = $this->db->prepare("
            SELECT
                COALESCE(SUM(CASE WHEN quantidade > 0 THEN quantidade ELSE 0 END), 0) as qtd_entrada,
                COALESCE(SUM(CASE WHEN quantidade > 0 THEN valor_total ELSE 0 END), 0) as val_entrada
            FROM estoque_movimentos
            WHERE produto_id = ? AND tipo_movimento IN ('entrada', 'ajuste') AND quantidade > 0
        ");
        $stmt->execute([$produtoId]);
        $entradas = $stmt->fetch(PDO::FETCH_ASSOC);

        $qtdEntrada = (float)($entradas['qtd_entrada'] ?? 0);
        $valEntrada = (float)($entradas['val_entrada'] ?? 0);

        $custoMedio = $qtdEntrada > 0 ? round($valEntrada / $qtdEntrada, 4) : 0;

        $stmtSaidas = $this->db->prepare("
            SELECT COALESCE(SUM(ABS(quantidade)), 0) as qtd_saida
            FROM estoque_movimentos
            WHERE produto_id = ? AND tipo_movimento IN ('saida', 'ajuste') AND quantidade < 0
        ");
        $stmtSaidas->execute([$produtoId]);
        $qtdSaida = (float)$stmtSaidas->fetchColumn();

        $saldoQtd = $qtdEntrada - $qtdSaida;
        $saldoValor = round($saldoQtd * $custoMedio, 2);

        $sql = "INSERT INTO estoque_saldo (produto_id, quantidade, custo_medio, valor_total)
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE quantidade = VALUES(quantidade), custo_medio = VALUES(custo_medio), valor_total = VALUES(valor_total)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$produtoId, $saldoQtd, $custoMedio, $saldoValor]);
    }

    private function atualizarSaldoAposMovimento(int $produtoId): void
    {
        $this->atualizarCustoMedio($produtoId);
    }

    private function initSaldoProduto(int $produtoId): void
    {
        try {
            $sql = "INSERT IGNORE INTO estoque_saldo (produto_id, quantidade, custo_medio, valor_total) VALUES (?, 0, 0, 0)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$produtoId]);
        } catch (PDOException) {
        }
    }

    private function getSaldoProduto(int $produtoId): ?array
    {
        try {
            $this->initSaldoProduto($produtoId);
            $stmt = $this->db->prepare("SELECT * FROM estoque_saldo WHERE produto_id = ?");
            $stmt->execute([$produtoId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (PDOException) {
            return null;
        }
    }

    public function getSaldoAll(): array
    {
        try {
            if (!$this->tableExists('estoque_saldo')) return [];
            $stmt = $this->db->query("
                SELECT s.*, p.nome, p.codigo, p.categoria, p.unidade
                FROM estoque_saldo s
                JOIN produtos p ON s.produto_id = p.id
                WHERE s.quantidade != 0
                ORDER BY p.nome ASC
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException) {
            return [];
        }
    }

    // ========================
    // INVENTÁRIO FÍSICO
    // ========================

    public function getInventarios(): array
    {
        try {
            if (!$this->tableExists('inventario')) return [];
            $stmt = $this->db->query("
                SELECT i.*, u.nome as usuario_nome
                FROM inventario i
                LEFT JOIN usuarios u ON i.usuario_id = u.id
                ORDER BY i.created_at DESC
                LIMIT 50
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException) {
            return [];
        }
    }

    public function getInventarioById(int $id): ?array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT i.*, u.nome as usuario_nome
                FROM inventario i
                LEFT JOIN usuarios u ON i.usuario_id = u.id
                WHERE i.id = ?
            ");
            $stmt->execute([$id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (PDOException) {
            return null;
        }
    }

    public function getItensInventario(int $inventarioId): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT ii.*, p.nome as produto_nome, p.codigo as produto_codigo, p.unidade
                FROM inventario_itens ii
                JOIN produtos p ON ii.produto_id = p.id
                WHERE ii.inventario_id = ?
                ORDER BY p.nome ASC
            ");
            $stmt->execute([$inventarioId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException) {
            return [];
        }
    }

    public function criarInventario(string $data, string $tipo = 'total', string $observacoes = null, int $usuarioId = null): int
    {
        try {
            $sql = "INSERT INTO inventario (data_inventario, tipo, status, observacoes, usuario_id) VALUES (?, ?, 'aberto', ?, ?)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$data, $tipo, $observacoes, $usuarioId]);
            $inventarioId = (int)$this->db->lastInsertId();

            if ($tipo === 'total') {
                $produtos = $this->getProdutos();
                $sqlItem = "INSERT INTO inventario_itens (inventario_id, produto_id, quantidade_sistema, custo_medio)
                            VALUES (?, ?, ?, ?)";
                $stmtItem = $this->db->prepare($sqlItem);

                foreach ($produtos as $p) {
                    if ($p['saldo_quantidade'] > 0 || $tipo === 'total') {
                        $stmtItem->execute([$inventarioId, $p['id'], $p['saldo_quantidade'] ?? 0, $p['saldo_custo_medio'] ?? 0]);
                    }
                }
            }

            return $inventarioId;
        } catch (PDOException) {
            return 0;
        }
    }

    public function atualizarContagemInventario(int $itemId, float $quantidadeContada, string $observacoes = null): bool
    {
        try {
            $stmt = $this->db->prepare("
                UPDATE inventario_itens
                SET quantidade_contada = ?, diferenca = quantidade_contada - quantidade_sistema,
                    valor_diferenca = (quantidade_contada - quantidade_sistema) * custo_medio,
                    observacoes = COALESCE(?, observacoes)
                WHERE id = ?
            ");
            return $stmt->execute([$quantidadeContada, $observacoes, $itemId]);
        } catch (PDOException) {
            return false;
        }
    }

    public function finalizarInventario(int $inventarioId): array
    {
        $ajustes = [];
        try {
            $itens = $this->getItensInventario($inventarioId);

            foreach ($itens as $item) {
                $diferenca = (float)($item['diferenca'] ?? 0);
                if (abs($diferenca) < 0.001) continue;

                $produtoId = (int)$item['produto_id'];
                $custoMedio = (float)$item['custo_medio'];

                if ($diferenca > 0) {
                    $this->registrarEntrada($produtoId, $diferenca, $custoMedio, date('Y-m-d'), 'Ajuste Inventário',
                        "Ajuste positivo - Inventário #$inventarioId");
                } else {
                    $this->registrarSaida($produtoId, abs($diferenca), date('Y-m-d'), 'Ajuste Inventário',
                        "Ajuste negativo - Inventário #$inventarioId");
                }

                $ajustes[] = [
                    'produto_id' => $produtoId,
                    'diferenca' => $diferenca,
                    'valor' => abs($diferenca) * $custoMedio
                ];
            }

            $stmt = $this->db->prepare("UPDATE inventario SET status = 'finalizado' WHERE id = ?");
            $stmt->execute([$inventarioId]);
        } catch (PDOException $e) {
            error_log("Erro ao finalizar inventário: " . $e->getMessage());
        }

        return $ajustes;
    }

    // ========================
    // RESUMO
    // ========================

    public function getResumo(): array
    {
        try {
            $totalProdutos = 0;
            $totalItens = 0;
            $valorEstoque = 0;
            $inventariosAbertos = 0;

            if ($this->tableExists('produtos')) {
                $stmt = $this->db->query("SELECT COUNT(*) FROM produtos WHERE ativo = 1");
                $totalProdutos = (int)$stmt->fetchColumn();
            }
            if ($this->tableExists('estoque_saldo')) {
                $stmt = $this->db->query("SELECT COALESCE(SUM(quantidade), 0) FROM estoque_saldo");
                $totalItens = (float)$stmt->fetchColumn();
                $stmt = $this->db->query("SELECT COALESCE(SUM(valor_total), 0) FROM estoque_saldo");
                $valorEstoque = (float)$stmt->fetchColumn();
            }
            if ($this->tableExists('inventario')) {
                $stmt = $this->db->query("SELECT COUNT(*) FROM inventario WHERE status = 'aberto'");
                $inventariosAbertos = (int)$stmt->fetchColumn();
            }

            return [
                'total_produtos' => $totalProdutos,
                'total_itens' => $totalItens,
                'valor_estoque' => $valorEstoque,
                'inventarios_abertos' => $inventariosAbertos,
            ];
        } catch (PDOException) {
            return ['total_produtos' => 0, 'total_itens' => 0, 'valor_estoque' => 0, 'inventarios_abertos' => 0];
        }
    }
}
