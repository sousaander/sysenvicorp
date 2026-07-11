<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class RelatorioModel extends Model
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

    public function getModelos(?string $modulo = null): array
    {
        try {
            if (!$this->tableExists('modelos_relatorios')) return [];
            $sql = "SELECT * FROM modelos_relatorios";
            $params = [];
            if ($modulo) {
                $sql .= " WHERE modulo = ?";
                $params[] = $modulo;
            }
            $sql .= " ORDER BY modulo, nome ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\PDOException) {
            return [];
        }
    }

    public function getModeloById(int $id): ?array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM modelos_relatorios WHERE id = ?");
            $stmt->execute([$id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (\PDOException) {
            return null;
        }
    }

    public function salvarModelo(array $dados): bool
    {
        try {
            if (!$this->tableExists('modelos_relatorios')) return false;
            $configJson = is_string($dados['config'] ?? '') ? $dados['config'] : json_encode($dados['config'] ?? []);

            if (!empty($dados['id'])) {
                $stmt = $this->db->prepare("
                    UPDATE modelos_relatorios SET
                        nome = ?, descricao = ?, modulo = ?, tipo = ?,
                        config = ?, colunas_personalizadas = ?, parametros_personalizados = ?,
                        rodape = ?, orientacao = ?, formato_padrao = ?, ativo = ?
                    WHERE id = ?
                ");
                return $stmt->execute([
                    $dados['nome'], $dados['descricao'] ?? null, $dados['modulo'], $dados['tipo'] ?? 'personalizado',
                    $configJson, $dados['colunas_personalizadas'] ?? null, $dados['parametros_personalizados'] ?? null,
                    $dados['rodape'] ?? null, $dados['orientacao'] ?? 'retrato', $dados['formato_padrao'] ?? 'pdf',
                    $dados['ativo'] ?? 1, $dados['id']
                ]);
            } else {
                $stmt = $this->db->prepare("
                    INSERT INTO modelos_relatorios
                    (nome, descricao, modulo, tipo, config, colunas_personalizadas, parametros_personalizados,
                     rodape, orientacao, formato_padrao, ativo, criado_por)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                return $stmt->execute([
                    $dados['nome'], $dados['descricao'] ?? null, $dados['modulo'], $dados['tipo'] ?? 'personalizado',
                    $configJson, $dados['colunas_personalizadas'] ?? null, $dados['parametros_personalizados'] ?? null,
                    $dados['rodape'] ?? null, $dados['orientacao'] ?? 'retrato', $dados['formato_padrao'] ?? 'pdf',
                    $dados['ativo'] ?? 1, $dados['criado_por'] ?? null
                ]);
            }
        } catch (\PDOException) {
            return false;
        }
    }

    public function excluirModelo(int $id): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM modelos_relatorios WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (\PDOException) {
            return false;
        }
    }

    public function getModulosDisponiveis(): array
    {
        return [
            'contabil' => 'Contábil',
            'fiscal' => 'Fiscal',
            'estoque' => 'Estoque',
            'financeiro' => 'Financeiro',
            'rh' => 'RH',
        ];
    }

    public function getColunasDisponiveis(string $modulo): array
    {
        $colunas = [
            'contabil' => [
                'data_lancamento' => 'Data',
                'descricao' => 'Descrição',
                'categoria' => 'Categoria',
                'conta_debito' => 'Conta Débito',
                'conta_credito' => 'Conta Crédito',
                'valor' => 'Valor',
                'tipo' => 'Tipo',
                'origem' => 'Origem',
                'centro_custo' => 'Centro de Custo',
                'conciliado' => 'Conciliado',
                'created_at' => 'Criado em',
            ],
            'fiscal' => [
                'numero' => 'Número NF',
                'tipo' => 'Tipo (Entrada/Saída)',
                'emissao' => 'Data Emissão',
                'cliente_fornecedor' => 'Cliente/Fornecedor',
                'cnpj_cpf' => 'CNPJ/CPF',
                'valor' => 'Valor',
                'status' => 'Status',
                'chave_acesso' => 'Chave de Acesso',
            ],
            'estoque' => [
                'produto' => 'Produto',
                'codigo' => 'Código',
                'categoria' => 'Categoria',
                'quantidade' => 'Quantidade',
                'custo_medio' => 'Custo Médio',
                'valor_total' => 'Valor Total',
                'ultima_mov' => 'Última Movimentação',
            ],
            'financeiro' => [
                'data' => 'Data',
                'descricao' => 'Descrição',
                'categoria' => 'Categoria',
                'tipo' => 'Tipo (Pagar/Receber)',
                'valor' => 'Valor',
                'status' => 'Status',
                'vencimento' => 'Vencimento',
                'pagamento' => 'Data Pagamento',
                'centro_custo' => 'Centro de Custo',
            ],
            'rh' => [
                'nome' => 'Nome',
                'cpf' => 'CPF',
                'cargo' => 'Cargo',
                'departamento' => 'Departamento',
                'salario' => 'Salário',
                'admissao' => 'Data Admissão',
                'status' => 'Status',
            ],
        ];
        return $colunas[$modulo] ?? [];
    }

    public function executarRelatorio(int $modeloId, array $filtros = []): array
    {
        $modelo = $this->getModeloById($modeloId);
        if (!$modelo) return [];

        $config = json_decode($modelo['config'], true) ?: [];
        $modulo = $modelo['modulo'];

        switch ($modulo) {
            case 'contabil':
                return $this->executarContabil($config, $filtros);
            case 'fiscal':
                return $this->executarFiscal($config, $filtros);
            case 'estoque':
                return $this->executarEstoque($config, $filtros);
            case 'financeiro':
                return $this->executarFinanceiro($config, $filtros);
            default:
                return [];
        }
    }

    private function executarContabil(array $config, array $filtros): array
    {
        try {
            $sql = "
                SELECT l.*, 
                       dc.nome as debito_conta_nome, dc.codigo as debito_conta_codigo,
                       cc.nome as credito_conta_nome, cc.codigo as credito_conta_codigo
                FROM lancamentos_contabeis l
                LEFT JOIN plano_contas dc ON l.debito_conta_id = dc.id
                LEFT JOIN plano_contas cc ON l.credito_conta_id = cc.id
                WHERE 1=1
            ";
            $params = [];

            if (!empty($filtros['data_inicio'])) {
                $sql .= " AND l.data_lancamento >= ?";
                $params[] = $filtros['data_inicio'];
            }
            if (!empty($filtros['data_fim'])) {
                $sql .= " AND l.data_lancamento <= ?";
                $params[] = $filtros['data_fim'];
            }
            if (!empty($filtros['categoria'])) {
                $sql .= " AND l.categoria = ?";
                $params[] = $filtros['categoria'];
            }
            if (!empty($filtros['origem'])) {
                $sql .= " AND l.origem = ?";
                $params[] = $filtros['origem'];
            }

            $sql .= " ORDER BY l.data_lancamento DESC LIMIT 1000";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\PDOException) {
            return [];
        }
    }

    private function executarFiscal(array $config, array $filtros): array
    {
        try {
            if (!$this->tableExists('notas_fiscais')) return [];
            $sql = "SELECT * FROM notas_fiscais WHERE 1=1";
            $params = [];

            if (!empty($filtros['data_inicio'])) {
                $sql .= " AND emissao >= ?";
                $params[] = $filtros['data_inicio'];
            }
            if (!empty($filtros['data_fim'])) {
                $sql .= " AND emissao <= ?";
                $params[] = $filtros['data_fim'];
            }
            if (!empty($filtros['tipo'])) {
                $sql .= " AND tipo = ?";
                $params[] = $filtros['tipo'];
            }

            $sql .= " ORDER BY emissao DESC LIMIT 1000";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\PDOException) {
            return [];
        }
    }

    private function executarEstoque(array $config, array $filtros): array
    {
        try {
            if (!$this->tableExists('estoque_saldo')) return [];
            $sql = "
                SELECT p.id, p.nome, p.codigo, p.categoria,
                       COALESCE(es.quantidade, 0) as quantidade,
                       COALESCE(es.custo_medio, 0) as custo_medio,
                       COALESCE(es.valor_total, 0) as valor_total
                FROM produtos p
                LEFT JOIN estoque_saldo es ON p.id = es.produto_id
                WHERE p.ativo = 1
            ";
            $params = [];

            if (!empty($filtros['categoria'])) {
                $sql .= " AND p.categoria = ?";
                $params[] = $filtros['categoria'];
            }

            $sql .= " ORDER BY p.nome ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\PDOException) {
            return [];
        }
    }

    private function executarFinanceiro(array $config, array $filtros): array
    {
        try {
            if (!$this->tableExists('transacoes')) return [];
            $sql = "SELECT * FROM transacoes WHERE 1=1";
            $params = [];

            if (!empty($filtros['data_inicio'])) {
                $sql .= " AND vencimento >= ?";
                $params[] = $filtros['data_inicio'];
            }
            if (!empty($filtros['data_fim'])) {
                $sql .= " AND vencimento <= ?";
                $params[] = $filtros['data_fim'];
            }
            if (!empty($filtros['tipo'])) {
                $sql .= " AND tipo = ?";
                $params[] = $filtros['tipo'];
            }
            if (!empty($filtros['status'])) {
                $sql .= " AND status = ?";
                $params[] = $filtros['status'];
            }

            $sql .= " ORDER BY vencimento DESC LIMIT 1000";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\PDOException) {
            return [];
        }
    }
}
