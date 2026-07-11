<?php

namespace App\Models;

use App\Core\Model;
use PDO;
use PDOException;

class FinancialModel extends Model
{
    /**
     * Armazena a última mensagem de erro.
     */
    private $ultimoErro = null;

    /**
     * Cache interno para saber se a tabela 'transacoes' possui a coluna usuario_id.
     * @var bool|null
     */
    private $hasUsuarioColumn = null;
    
    /**
     * Cache interno para saber se a tabela 'transacoes' possui a coluna prestacao_categoria_id.
     * @var bool|null
     */
    private $hasPrestacaoCategoriaColumn = null;

    /**
     * Cache interno para saber se a tabela 'transacoes' possui a coluna valor_pago.
     * @var bool|null
     */
    private $hasValorPagoColumn = null;

    public function __construct()
    {
        parent::__construct();
        $this->ensureWebhookColumns();
        $this->ensureValorPagoColumn();
        $this->ensurePagamentosParciaisTable();
    }

    private function ensurePagamentosParciaisTable(): void
    {
        try {
            $stmt = $this->db->query("SHOW TABLES LIKE 'pagamentos_parciais'");
            if (!$stmt->fetch()) {
                $this->db->exec("CREATE TABLE pagamentos_parciais (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    transacao_id INT NOT NULL,
                    valor DECIMAL(15,2) NOT NULL DEFAULT 0.00,
                    data_pagamento DATE NOT NULL,
                    forma_pagamento VARCHAR(50) NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (transacao_id) REFERENCES transacoes(id) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

                $this->db->exec("INSERT INTO pagamentos_parciais (transacao_id, valor, data_pagamento, forma_pagamento, created_at)
                    SELECT id, valor_pago, COALESCE(data_pagamento, vencimento), forma_pagamento, created_at
                    FROM transacoes WHERE valor_pago > 0");
            }
        } catch (PDOException $e) {
            error_log("Erro ao criar tabela pagamentos_parciais: " . $e->getMessage());
        }
    }

    public function inserirPagamentoParcial(int $transacaoId, float $valor, string $dataPagamento, ?string $formaPagamento = null): int|false
    {
        try {
            $stmt = $this->db->prepare("INSERT INTO pagamentos_parciais (transacao_id, valor, data_pagamento, forma_pagamento) VALUES (?, ?, ?, ?)");
            $stmt->execute([$transacaoId, $valor, $dataPagamento, $formaPagamento]);
            return (int)$this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Erro ao inserir pagamento parcial: " . $e->getMessage());
            return false;
        }
    }

    public function getPagamentosParciais(int $transacaoId): array
    {
        try {
            $stmt = $this->db->prepare("SELECT id, valor, data_pagamento, forma_pagamento, created_at FROM pagamentos_parciais WHERE transacao_id = ? ORDER BY data_pagamento ASC, id ASC");
            $stmt->execute([$transacaoId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar pagamentos parciais: " . $e->getMessage());
            return [];
        }
    }

    public function editarPagamentoParcial(int $id, float $valor, string $dataPagamento, ?string $formaPagamento = null): array
    {
        try {
            $stmt = $this->db->prepare("SELECT transacao_id FROM pagamentos_parciais WHERE id = ?");
            $stmt->execute([$id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row) return ['success' => false, 'message' => 'Registro não encontrado.'];

            $this->db->prepare("UPDATE pagamentos_parciais SET valor = ?, data_pagamento = ?, forma_pagamento = ? WHERE id = ?")
                ->execute([$valor, $dataPagamento, $formaPagamento, $id]);

            $novoTotal = $this->recalcularValorPago($row['transacao_id']);
            $transacao = $this->getTransacaoPorId($row['transacao_id']);
            $saldoRestante = ($transacao['valor'] ?? 0) - $novoTotal;

            return [
                'success' => true,
                'message' => 'Pagamento atualizado.',
                'valor_pago' => $novoTotal,
                'saldo_restante' => round($saldoRestante, 2),
                'status' => $transacao['status'] ?? 'Pago Parcial',
            ];
        } catch (PDOException $e) {
            error_log("Erro ao editar pagamento parcial: " . $e->getMessage());
            return ['success' => false, 'message' => 'Erro ao salvar.'];
        }
    }

    public function excluirPagamentoParcial(int $id): array
    {
        try {
            $stmt = $this->db->prepare("SELECT transacao_id FROM pagamentos_parciais WHERE id = ?");
            $stmt->execute([$id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row) return ['success' => false, 'message' => 'Registro não encontrado.'];

            $this->db->prepare("DELETE FROM pagamentos_parciais WHERE id = ?")->execute([$id]);

            $novoTotal = $this->recalcularValorPago($row['transacao_id']);
            $transacao = $this->getTransacaoPorId($row['transacao_id']);
            $saldoRestante = ($transacao['valor'] ?? 0) - $novoTotal;

            return [
                'success' => true,
                'message' => 'Pagamento excluído.',
                'valor_pago' => $novoTotal,
                'saldo_restante' => round($saldoRestante, 2),
                'status' => $transacao['status'] ?? 'Pendente',
            ];
        } catch (PDOException $e) {
            error_log("Erro ao excluir pagamento parcial: " . $e->getMessage());
            return ['success' => false, 'message' => 'Erro ao excluir.'];
        }
    }

    public function limparPagamentosParciais(int $transacaoId): void
    {
        try {
            $this->db->prepare("DELETE FROM pagamentos_parciais WHERE transacao_id = ?")->execute([$transacaoId]);
        } catch (PDOException $e) {
            error_log("Erro ao limpar pagamentos parciais: " . $e->getMessage());
        }
    }

    public function recalcularValorPago(int $transacaoId): float
    {
        try {
            $stmt = $this->db->prepare("SELECT COALESCE(SUM(valor), 0) FROM pagamentos_parciais WHERE transacao_id = ?");
            $stmt->execute([$transacaoId]);
            $total = (float)$stmt->fetchColumn();

            $transacao = $this->getTransacaoPorId($transacaoId);
            $valorOriginal = (float)($transacao['valor'] ?? 0);
            $novoStatus = $total >= $valorOriginal ? 'Pago' : ($total > 0 ? 'Pago Parcial' : 'Pendente');

            $stmtUpd = $this->db->prepare("UPDATE transacoes SET valor_pago = ?, status = ? WHERE id = ?");
            $stmtUpd->execute([$total, $novoStatus, $transacaoId]);
            return $total;
        } catch (PDOException $e) {
            error_log("Erro ao recalcular valor_pago: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Garante a existência das colunas necessárias para a integração de Webhooks na tabela transacoes.
     */
    private function ensureWebhookColumns(): void
    {
        try {
            // Coluna webhook_id para controle de duplicidade (deve ser única)
            $stmt = $this->db->query("SHOW COLUMNS FROM transacoes LIKE 'webhook_id'");
            if (!$stmt->fetch()) {
                $this->db->exec("ALTER TABLE transacoes ADD COLUMN webhook_id VARCHAR(255) NULL UNIQUE AFTER status");
            }

            // Coluna categoria (texto) para registrar o tipo vindo do banco antes da classificação manual
            $stmtCat = $this->db->query("SHOW COLUMNS FROM transacoes LIKE 'categoria'");
            if (!$stmtCat->fetch()) {
                $this->db->exec("ALTER TABLE transacoes ADD COLUMN categoria VARCHAR(100) NULL AFTER tipo");
            }

            // Coluna iss_percentual para persistência de cálculos de receitas
            $stmtIss = $this->db->query("SHOW COLUMNS FROM transacoes LIKE 'iss_percentual'");
            if (!$stmtIss->fetch()) {
                $this->db->exec("ALTER TABLE transacoes ADD COLUMN iss_percentual DECIMAL(5,2) DEFAULT 0.00 AFTER desconto");
            }
        } catch (PDOException $e) {
            error_log("Erro ao atualizar schema para webhooks: " . $e->getMessage());
        }
    }

    /**
     * Verifica e retorna se a coluna usuario_id existe em transacoes.
     * Faz uma consulta SHOW COLUMNS e memoiza o resultado.
     * @return bool
     */
    private function ensureUsuarioColumn(): bool
    {
        if ($this->hasUsuarioColumn === null) {
            try {
                $stmt = $this->db->query("SHOW COLUMNS FROM transacoes LIKE 'usuario_id'");
                $this->hasUsuarioColumn = (bool) $stmt->fetch();
            } catch (PDOException $e) {
                // Se houver qualquer erro (por exemplo tabela inexistente), assumimos que não existe
                error_log('Erro ao verificar coluna usuario_id: ' . $e->getMessage());
                $this->hasUsuarioColumn = false;
            }
        }
        return $this->hasUsuarioColumn;
    }

    /**
     * Verifica e retorna se a coluna prestacao_categoria_id existe em transacoes.
     */
    private function ensurePrestacaoCategoriaColumn(): bool
    {
        if ($this->hasPrestacaoCategoriaColumn === null) {
            try {
                $stmt = $this->db->query("SHOW COLUMNS FROM transacoes LIKE 'prestacao_categoria_id'");
                $this->hasPrestacaoCategoriaColumn = (bool) $stmt->fetch();
            } catch (PDOException $e) {
                $this->hasPrestacaoCategoriaColumn = false;
            }
        }
        return $this->hasPrestacaoCategoriaColumn;
    }

    /**
     * Garante a existência da coluna valor_pago na tabela transacoes.
     */
    private function ensureValorPagoColumn(): void
    {
        if ($this->hasValorPagoColumn === null) {
            try {
                $stmt = $this->db->query("SHOW COLUMNS FROM transacoes LIKE 'valor_pago'");
                if (!$stmt->fetch()) {
                    $this->db->exec("ALTER TABLE transacoes ADD COLUMN valor_pago DECIMAL(15,2) NOT NULL DEFAULT 0.00 AFTER valor");
                    $this->db->exec("UPDATE transacoes SET valor_pago = valor WHERE status = 'Pago'");
                }
                $this->hasValorPagoColumn = true;
            } catch (PDOException $e) {
                error_log("Erro ao garantir coluna valor_pago: " . $e->getMessage());
                $this->hasValorPagoColumn = false;
            }
        }
    }

    /**
     * Retorna a última mensagem de erro capturada.
     * @return string|null
     */
    public function getUltimoErro(): ?string
    {
        return $this->ultimoErro;
    }

    /**
     * Busca dados resumidos de fluxo de caixa (ex: últimas 5 transações).
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getResumoFluxoCaixa(array $filtros = [], int $limit = 5, int $offset = 0): array
    {
        // Busca fluxos incluindo transferências (podem ser filtradas externamente)
        $sql = "SELECT t.id, t.descricao, t.valor, t.valor_pago, t.juros, t.desconto, t.tipo, t.status, t.vencimento as data, t.vencimento, t.data_pagamento, t.banco_id, t.centro_custo_id, t.documento_vinculado, t.observacoes,
                       b.nome as banco_nome, b.cor as banco_color, cc.nome as nome_centro_custo
                FROM transacoes t
                LEFT JOIN bancos b ON t.banco_id = b.id
                LEFT JOIN centros_custo cc ON t.centro_custo_id = cc.id
                WHERE 1=1";
        $params = [];
        $ordenarPor = "t.data_pagamento DESC, t.created_at DESC"; // Ordenação padrão

        // Constrói a query com base nos filtros
        if (!empty($filtros['tipo'])) {
            $sql .= " AND t.tipo = :tipo";
            $params[':tipo'] = $filtros['tipo'];
        }

        if (!empty($filtros['status'])) {
            $sql .= " AND t.status = :status";
            $params[':status'] = $filtros['status'];
        }

        if (!empty($filtros['periodo'])) {
            switch ($filtros['periodo']) {
                case 'dia':
                    if (!empty($filtros['data_unica'])) {
                        $sql .= " AND DATE(t.vencimento) = :data_unica";
                        $params[':data_unica'] = $filtros['data_unica'];
                        $ordenarPor = "t.vencimento ASC, t.id ASC";
                    }
                    break;
                case 'mes':
                    if (!empty($filtros['mes_ano'])) {
                        $sql .= " AND DATE_FORMAT(t.vencimento, '%Y-%m') = :mes_ano";
                        $params[':mes_ano'] = $filtros['mes_ano'];
                        $ordenarPor = "t.vencimento ASC, t.id ASC";
                    }
                    break;
                case 'intervalo':
                    if (!empty($filtros['data_inicio']) && !empty($filtros['data_fim'])) {
                        $sql .= " AND t.vencimento BETWEEN :data_inicio AND :data_fim";
                        $params[':data_inicio'] = $filtros['data_inicio'];
                        $params[':data_fim'] = $filtros['data_fim'];
                        $ordenarPor = "t.vencimento ASC, t.id ASC";
                    }
                    break;
            }
        }

        if (!empty($filtros['ordem'])) {
            $direcao = (!empty($filtros['direcao']) && strtoupper($filtros['direcao']) === 'ASC') ? 'ASC' : 'DESC';
            if ($filtros['ordem'] === 'data') {
                $ordenarPor = "t.vencimento $direcao, t.id $direcao";
            }
            if ($filtros['ordem'] === 'pago_em') {
                $ordenarPor = "t.data_pagamento $direcao, t.created_at $direcao";
            }
        }

        $sql .= " GROUP BY t.id";
        $sql .= " ORDER BY {$ordenarPor} LIMIT :limit OFFSET :offset";

        try {
            $stmt = $this->db->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Em caso de erro real, retorne um array vazio e logue o erro
            error_log("Erro ao buscar fluxo de caixa: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Conta o número total de transações.
     * @return int
     */
    public function getContagemFluxoCaixa(array $filtros = []): int
    {
        // Conta transações (inclui transferências por padrão)
        $sql = "SELECT COUNT(*) FROM transacoes t WHERE 1=1";
        $params = [];

        // Constrói a query com base nos filtros
        if (!empty($filtros['tipo'])) {
            $sql .= " AND t.tipo = :tipo";
            $params[':tipo'] = $filtros['tipo'];
        }

        if (!empty($filtros['status'])) {
            $sql .= " AND t.status = :status";
            $params[':status'] = $filtros['status'];
        }

        if (!empty($filtros['periodo'])) {
            switch ($filtros['periodo']) {
                case 'dia':
                    if (!empty($filtros['data_unica'])) {
                        $sql .= " AND DATE(t.vencimento) = :data_unica";
                        $params[':data_unica'] = $filtros['data_unica'];
                    }
                    break;
                case 'mes':
                    if (!empty($filtros['mes_ano'])) {
                        $sql .= " AND DATE_FORMAT(t.vencimento, '%Y-%m') = :mes_ano";
                        $params[':mes_ano'] = $filtros['mes_ano'];
                    }
                    break;
                case 'intervalo':
                    if (!empty($filtros['data_inicio']) && !empty($filtros['data_fim'])) {
                        $sql .= " AND t.vencimento BETWEEN :data_inicio AND :data_fim";
                        $params[':data_inicio'] = $filtros['data_inicio'];
                        $params[':data_fim'] = $filtros['data_fim'];
                    }
                    break;
                case 'recente':
                    break;
            }
        }

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Erro ao contar transações do fluxo de caixa: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Busca transações para relatórios, com filtros flexíveis e sem paginação.
     * Inclui nome do banco e classificação.
     * @param array $filtros Filtros de busca.
     * @return array
     */
    public function getTransacoesParaRelatorio(array $filtros = [], ?int $limit = null, ?int $offset = null): array
    {
        $hasPrestacaoCol = $this->ensurePrestacaoCategoriaColumn();
        $categoriaExpr = "COALESCE(NULLIF(tc.nome, ''), " . ($hasPrestacaoCol ? "NULLIF(pc.nome, ''), " : "") . "NULLIF(t.categoria, ''), 'Sem Categoria')";

        $sql = "SELECT 
                    t.id, 
                    t.descricao, 
                    t.valor, 
                    t.tipo, 
                    t.status, 
                    t.data_pagamento,
                    t.vencimento as data,
                    t.banco_id,
                    t.centro_custo_id,
                    t.documento_vinculado,
                    t.observacoes,
                    b.nome as nome_banco,
                    $categoriaExpr as nome_classificacao,
                    cc.nome as nome_centro_custo
                FROM transacoes t
                LEFT JOIN bancos b ON t.banco_id = b.id
                LEFT JOIN transacao_classificacoes tc ON t.classificacao_id = tc.id
                LEFT JOIN centros_custo cc ON t.centro_custo_id = cc.id";
        
        if ($hasPrestacaoCol) {
            $sql .= " LEFT JOIN prestacao_categorias pc ON t.prestacao_categoria_id = pc.id";
        }
        
        $sql .= " WHERE 1=1";
        $params = [];
        $orderBy = "t.vencimento DESC, t.created_at DESC";

        // Filtro por banco
        if (!empty($filtros['banco_id'])) {
            $sql .= " AND t.banco_id = :banco_id";
            $params[':banco_id'] = $filtros['banco_id'];
        }

        // Filtro por status
        if (!empty($filtros['status'])) {
            if ($filtros['status'] === 'Pendente') {
                $sql .= " AND (t.status = 'Pendente' OR t.status = 'Atrasado')";
            } else {
                $sql .= " AND t.status = :status";
                $params[':status'] = $filtros['status'];
            }
        }

        // Filtro por classificação
        if (!empty($filtros['classificacao_id'])) {
            // Correção: Verifica tanto a classificação financeira quanto a de prestação de contas
            $sql .= " AND (t.classificacao_id = :class_id_1 OR t.prestacao_categoria_id = :class_id_2)";
            $params[':class_id_1'] = $filtros['classificacao_id'];
            $params[':class_id_2'] = $filtros['classificacao_id'];
        }

        // Filtro por centro de custo
        if (!empty($filtros['centro_custo_id'])) {
            $sql .= " AND t.centro_custo_id = :centro_custo_id";
            $params[':centro_custo_id'] = $filtros['centro_custo_id'];
        }

        // Filtro por período
        if (!empty($filtros['periodo'])) {
            switch ($filtros['periodo']) {
                case 'dia':
                    if (!empty($filtros['data_unica'])) {
                        // Correção: Nomes de parâmetros únicos para evitar falha no PDO
                        $sql .= " AND (DATE(t.vencimento) = :data_unica_v OR DATE(t.data_pagamento) = :data_unica_p)";
                        $params[':data_unica_v'] = $filtros['data_unica'];
                        $params[':data_unica_p'] = $filtros['data_unica'];
                    }
                    break;
                case 'mes':
                    if (!empty($filtros['mes_ano'])) {
                        // Correção: Nomes de parâmetros únicos
                        $sql .= " AND (DATE_FORMAT(t.vencimento, '%Y-%m') = :mes_ano_v OR DATE_FORMAT(t.data_pagamento, '%Y-%m') = :mes_ano_p)";
                        $params[':mes_ano_v'] = $filtros['mes_ano'];
                        $params[':mes_ano_p'] = $filtros['mes_ano'];
                    }
                    break;
                case 'intervalo':
                    if (!empty($filtros['data_inicio']) && !empty($filtros['data_fim'])) {
                        // Correção: Nomes de parâmetros únicos e agrupamento lógico
                        $sql .= " AND (
                            (t.vencimento BETWEEN :data_inicio_v AND :data_fim_v) OR 
                            (t.data_pagamento BETWEEN :data_inicio_p AND :data_fim_p)
                        )";
                        $params[':data_inicio_v'] = $filtros['data_inicio'];
                        $params[':data_fim_v'] = $filtros['data_fim'];
                        $params[':data_inicio_p'] = $filtros['data_inicio'];
                        $params[':data_fim_p'] = $filtros['data_fim'];
                    }
                    break;
                case 'recente':
                default:
                    if (empty($filtros['mes_ano']) && empty($filtros['data_inicio'])) {
                        $sql .= " AND (t.vencimento >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) 
                                    OR t.data_pagamento >= DATE_SUB(CURDATE(), INTERVAL 30 DAY))";
                    }
                    // No caso recente, apenas mantemos a ordenação DESC definida no início
                    $orderBy = "t.vencimento DESC, t.created_at DESC";
                    break;
            }
        }

        $sql .= " GROUP BY t.id";
        $sql .= " ORDER BY {$orderBy}";

        if ($limit !== null && $offset !== null) {
            $sql .= " LIMIT :limit OFFSET :offset";
        }

        try {
            $stmt = $this->db->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            if ($limit !== null) {
                $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            }
            if ($offset !== null) {
                $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            }
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar transações para relatório: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Conta o total de transações para o relatório com base nos filtros.
     */
    public function getContagemTransacoesParaRelatorio(array $filtros = []): int
    {
        $sql = "SELECT COUNT(*)
                FROM transacoes t
                WHERE 1=1";
        $params = [];

        // Filtro por banco
        if (!empty($filtros['banco_id'])) {
            $sql .= " AND t.banco_id = :banco_id";
            $params[':banco_id'] = $filtros['banco_id'];
        }

        // Filtro por status
        if (!empty($filtros['status'])) {
            if ($filtros['status'] === 'Pendente') {
                $sql .= " AND (t.status = 'Pendente' OR t.status = 'Atrasado')";
            } else {
                $sql .= " AND t.status = :status";
                $params[':status'] = $filtros['status'];
            }
        }

        // Filtro por classificação
        if (!empty($filtros['classificacao_id'])) {
            $sql .= " AND (t.classificacao_id = :class_id_1 OR t.prestacao_categoria_id = :class_id_2)";
            $params[':class_id_1'] = $filtros['classificacao_id'];
            $params[':class_id_2'] = $filtros['classificacao_id'];
        }

        // Filtro por centro de custo
        if (!empty($filtros['centro_custo_id'])) {
            $sql .= " AND t.centro_custo_id = :centro_custo_id";
            $params[':centro_custo_id'] = $filtros['centro_custo_id'];
        }

        // Filtro por período
        if (!empty($filtros['periodo'])) {
            switch ($filtros['periodo']) {
                case 'dia':
                    if (!empty($filtros['data_unica'])) {
                        $sql .= " AND (DATE(t.vencimento) = :data_unica_v OR DATE(t.data_pagamento) = :data_unica_p)";
                        $params[':data_unica_v'] = $filtros['data_unica'];
                        $params[':data_unica_p'] = $filtros['data_unica'];
                    }
                    break;
                case 'mes':
                    if (!empty($filtros['mes_ano'])) {
                        $sql .= " AND (DATE_FORMAT(t.vencimento, '%Y-%m') = :mes_ano_v OR DATE_FORMAT(t.data_pagamento, '%Y-%m') = :mes_ano_p)";
                        $params[':mes_ano_v'] = $filtros['mes_ano'];
                        $params[':mes_ano_p'] = $filtros['mes_ano'];
                    }
                    break;
                case 'intervalo':
                    if (!empty($filtros['data_inicio']) && !empty($filtros['data_fim'])) {
                        $sql .= " AND (
                            (t.vencimento BETWEEN :data_inicio_v AND :data_fim_v) OR 
                            (t.data_pagamento BETWEEN :data_inicio_p AND :data_fim_p)
                        )";
                        $params[':data_inicio_v'] = $filtros['data_inicio'];
                        $params[':data_fim_v'] = $filtros['data_fim'];
                        $params[':data_inicio_p'] = $filtros['data_inicio'];
                        $params[':data_fim_p'] = $filtros['data_fim'];
                    }
                    break;
                case 'recente':
                default:
                    if (empty($filtros['mes_ano']) && empty($filtros['data_inicio'])) {
                        $sql .= " AND (t.vencimento >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) 
                                    OR t.data_pagamento >= DATE_SUB(CURDATE(), INTERVAL 30 DAY))";
                    }
                    break;
            }
        }

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Erro ao contar transações para relatório: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Calcula o total a receber (receitas pendentes) para os próximos N meses.
     * @param int $meses
     * @return float
     */
    public function getPrevisaoRecebimento(int $meses = 12): float
    {
        try {
            $sql = "SELECT SUM(valor - COALESCE(valor_pago, 0)) FROM transacoes 
                    WHERE tipo = 'R' 
                    AND status IN ('Pendente', 'Atrasado', 'Em Análise', 'Pago Parcial') 
                    AND status != 'Cancelado'
                    AND (documento_vinculado IS NULL OR documento_vinculado NOT LIKE 'transfer_%')
                    AND vencimento <= DATE_ADD(CURDATE(), INTERVAL :meses MONTH)";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':meses', $meses, PDO::PARAM_INT);
            $stmt->execute();
            return (float) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Erro ao buscar previsão de recebimento: " . $e->getMessage());
            return 0.0;
        }
    }

    /**
     * Calcula o total a pagar (despesas pendentes) para os próximos N meses.
     * @param int $meses
     * @return float
     */
    public function getPrevisaoPagamento(int $meses = 12): float
    {
        try {
            $sql = "SELECT SUM(valor - COALESCE(valor_pago, 0)) FROM transacoes 
                    WHERE tipo = 'P' 
                    AND status IN ('Pendente', 'Atrasado', 'Em Análise', 'Pago Parcial') 
                    AND status != 'Cancelado'
                    AND (documento_vinculado IS NULL OR documento_vinculado NOT LIKE 'transfer_%')
                    AND vencimento <= DATE_ADD(CURDATE(), INTERVAL :meses MONTH)";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':meses', $meses, PDO::PARAM_INT);
            $stmt->execute();
            return (float) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Erro ao buscar previsão de pagamento: " . $e->getMessage());
            return 0.0;
        }
    }

    /**
     * Calcula o custo total (Realizado + Previsto) do ano corrente (Orçado).
     * @return float
     */
    public function getTotalDespesasAnoCorrente(): float
    {
        try {
            $sql = "SELECT SUM(valor) FROM transacoes 
                    WHERE tipo = 'P' 
                    AND status != 'Cancelado' 
                    AND (documento_vinculado IS NULL OR documento_vinculado NOT LIKE 'transfer_%')
                    AND YEAR(vencimento) = YEAR(CURDATE())";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return (float) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Erro ao buscar total despesas ano corrente: " . $e->getMessage());
            return 0.0;
        }
    }

    /**
     * Calcula o total de despesas efetivamente pagas no ano corrente (Realizado).
     * @return float
     */
    public function getTotalDespesasPagasAnoCorrente(): float
    {
        try {
            $sql = "SELECT SUM(CASE WHEN status = 'Pago Parcial' THEN COALESCE(valor_pago, 0) ELSE valor + COALESCE(juros, 0) - COALESCE(desconto, 0) END) FROM transacoes 
                    WHERE tipo = 'P' 
                    AND status IN ('Pago', 'Pago Parcial')
                    AND (documento_vinculado IS NULL OR documento_vinculado NOT LIKE 'transfer_%')
                    AND YEAR(data_pagamento) = YEAR(CURDATE())";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return (float) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Erro ao buscar total despesas pagas ano corrente: " . $e->getMessage());
            return 0.0;
        }
    }

    /**
     * Calcula a receita total recebida no ano corrente.
     * @return float
     */
    public function getTotalReceitasAnoCorrente(): float
    {
        try {
            $sql = "SELECT SUM(CASE WHEN status = 'Pago Parcial' THEN COALESCE(valor_pago, 0) ELSE valor + COALESCE(juros, 0) - COALESCE(desconto, 0) END) FROM transacoes 
                    WHERE tipo = 'R' 
                    AND status IN ('Pago', 'Pago Parcial')
                    AND (documento_vinculado IS NULL OR documento_vinculado NOT LIKE 'transfer_%')
                    AND YEAR(data_pagamento) = YEAR(CURDATE())";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return (float) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Erro ao buscar total receitas ano corrente: " . $e->getMessage());
            return 0.0;
        }
    }

    /**
     * Busca um resumo mensal de receitas e despesas pagas para o gráfico do dashboard.
     * @param int $meses Número de meses a serem considerados (padrão: 6).
     * @return array
     */
    public function getResumoMensalParaGrafico(int $meses = 6, ?string $mesReferencia = null): array
    {
        try {
            // Regime de Caixa: Considera apenas a data de pagamento para o que foi efetivamente realizado.
            $mesRef = $mesReferencia ? $mesReferencia . '-01' : date('Y-m-01');
            $dateRef = "data_pagamento";
            
            $sql = "
                SELECT 
                    DATE_FORMAT($dateRef, '%Y-%m') as mes,
                    SUM(CASE WHEN tipo = 'R' THEN (CASE WHEN status = 'Pago Parcial' THEN COALESCE(valor_pago, 0) ELSE valor + COALESCE(juros, 0) - COALESCE(desconto, 0) END) ELSE 0 END) as receitas,
                    SUM(CASE WHEN tipo = 'P' THEN (CASE WHEN status = 'Pago Parcial' THEN COALESCE(valor_pago, 0) ELSE valor + COALESCE(juros, 0) - COALESCE(desconto, 0) END) ELSE 0 END) as despesas
                FROM transacoes
                WHERE 
                    status IN ('Pago', 'Pago Parcial') AND
                    data_pagamento IS NOT NULL AND data_pagamento != '0000-00-00' AND
                    $dateRef <= LAST_DAY(:mes_ref) AND $dateRef >= DATE_SUB(:mes_ref_start, INTERVAL :meses MONTH) AND
                    (documento_vinculado IS NULL OR documento_vinculado NOT LIKE 'transfer_%') AND
                    tipo IN ('R', 'P')
                GROUP BY mes
                ORDER BY mes ASC;
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':meses', $meses, PDO::PARAM_INT);
            $stmt->bindValue(':mes_ref', $mesRef);
            $stmt->bindValue(':mes_ref_start', $mesRef);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar resumo mensal para gráfico: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Busca um resumo de despesas por centro de custo para o gráfico do dashboard.
     * Considera apenas as despesas efetivamente pagas no período selecionado (Regime de Caixa).
     * @return array
     */
    public function getResumoDespesasPorCentroCusto(?string $mes = null): array
    {
        try {
            $mesRef = (!empty($mes)) ? trim($mes) : date('Y-m');
            $dateRef = "t.data_pagamento";

            $sql = "
                SELECT 
                    COALESCE(NULLIF(TRIM(cc.nome), ''), 'Sem Centro de Custo') as label,
                    SUM(CASE WHEN t.status = 'Pago Parcial' THEN COALESCE(t.valor_pago, 0) ELSE COALESCE(t.valor, 0) + COALESCE(t.juros, 0) - COALESCE(t.desconto, 0) END) as total
                FROM transacoes t
                LEFT JOIN centros_custo cc ON t.centro_custo_id = cc.id
                WHERE 
                    t.tipo = 'P' AND 
                    t.status IN ('Pago', 'Pago Parcial') AND
                    t.data_pagamento IS NOT NULL AND t.data_pagamento != '0000-00-00' AND
                    DATE_FORMAT($dateRef, '%Y-%m') = :mes AND
                    (t.documento_vinculado IS NULL OR t.documento_vinculado NOT LIKE 'transfer_%')
                GROUP BY label
                ORDER BY total DESC
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':mes', $mesRef);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar resumo de despesas por centro de custo: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Busca um resumo mensal de receitas e despesas futuras (projeção) para o gráfico.
     * Considera transações não canceladas (Pendente, Pago, Atrasado) a partir do início do mês atual.
     * @param int $meses Número de meses a serem considerados.
     * @return array
     */
    public function getResumoMensalFuturoParaGrafico(int $meses = 6): array
    {
        try {
            $sql = "
                SELECT 
                    CASE 
                        WHEN vencimento < DATE_FORMAT(CURDATE(), '%Y-%m-01') THEN DATE_FORMAT(CURDATE(), '%Y-%m')
                        ELSE DATE_FORMAT(vencimento, '%Y-%m')
                    END as mes,
                    SUM(CASE WHEN tipo = 'R' THEN (valor - COALESCE(valor_pago, 0)) ELSE 0 END) as receitas,
                    SUM(CASE WHEN tipo = 'P' THEN (valor - COALESCE(valor_pago, 0)) ELSE 0 END) as despesas
                FROM transacoes
                WHERE 
                    status IN ('Pendente', 'Atrasado', 'Em Análise', 'Pago Parcial') AND
                    vencimento <= DATE_ADD(CURDATE(), INTERVAL :meses MONTH) AND
                    (documento_vinculado IS NULL OR documento_vinculado NOT LIKE 'transfer_%') AND
                    tipo IN ('R', 'P')
                GROUP BY mes
                ORDER BY mes ASC;
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':meses', $meses, PDO::PARAM_INT);
            $stmt->execute();
            $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Garantir que todos os meses do intervalo solicitado apareçam (Gap Filling)
            $finalResults = [];
            for ($i = 0; $i < $meses; $i++) {
                $mesRef = date('Y-m', strtotime("first day of +$i month"));
                $found = array_filter($resultados, fn($item) => $item['mes'] === $mesRef);
                if (!empty($found)) {
                    $finalResults[] = array_values($found)[0];
                } else {
                    $finalResults[] = ['mes' => $mesRef, 'receitas' => 0.0, 'despesas' => 0.0];
                }
            }
            return $finalResults;
        } catch (PDOException $e) {
            error_log("Erro ao buscar resumo futuro para gráfico: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Busca um resumo de despesas por categoria para o gráfico do dashboard.
     * Considera apenas as despesas efetivamente pagas no período selecionado (Regime de Caixa).
     * @return array
     */
    public function getResumoDespesasPorCategoria(?string $mes = null): array
    {
        try {
            $mesRef = (!empty($mes)) ? trim($mes) : date('Y-m');
            $dateRef = "t.data_pagamento";

            $hasPrestacaoCol = $this->ensurePrestacaoCategoriaColumn();
            $labelExpr = "COALESCE(NULLIF(TRIM(tc.nome), ''), " . ($hasPrestacaoCol ? "NULLIF(TRIM(pc.nome), ''), " : "") . "NULLIF(TRIM(t.categoria), ''), 'Sem Categoria')";

            $sql = "
                SELECT 
                    $labelExpr as label,
                    SUM(CASE WHEN t.status = 'Pago Parcial' THEN COALESCE(t.valor_pago, 0) ELSE COALESCE(t.valor, 0) + COALESCE(t.juros, 0) - COALESCE(t.desconto, 0) END) as total
                FROM transacoes t
                LEFT JOIN transacao_classificacoes tc ON t.classificacao_id = tc.id";
            
            if ($hasPrestacaoCol) {
                $sql .= " LEFT JOIN prestacao_categorias pc ON t.prestacao_categoria_id = pc.id";
            }

            $sql .= " WHERE 
                    t.tipo = 'P' AND 
                    t.status IN ('Pago', 'Pago Parcial') AND
                    t.data_pagamento IS NOT NULL AND t.data_pagamento != '0000-00-00' AND
                    DATE_FORMAT($dateRef, '%Y-%m') = :mes AND
                    (t.documento_vinculado IS NULL OR t.documento_vinculado NOT LIKE 'transfer_%')
                GROUP BY 1
                ORDER BY total DESC
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':mes', $mesRef);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar resumo de despesas por categoria: " . $e->getMessage());
            return [];
        }
    }


    /**
     * Busca o total de Contas a Pagar no mês.
     */
    public function getContasPagarMes(?string $mesReferencia = null)
    {
        try {
            $mesRef = $mesReferencia ?: date('Y-m');
            // Ignora transferências internas
            $stmt = $this->db->prepare(
                "SELECT SUM((valor + COALESCE(juros, 0) - COALESCE(desconto, 0)) - COALESCE(valor_pago, 0)) as total FROM transacoes 
                 WHERE tipo = 'P' AND status IN ('Pendente', 'Atrasado', 'Pago Parcial') 
                 AND (documento_vinculado IS NULL OR documento_vinculado NOT LIKE 'transfer_%')
                 AND DATE_FORMAT(vencimento, '%Y-%m') = :mes"
            );
            $stmt->bindValue(':mes', $mesRef);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'] ?? 0.0;
        } catch (PDOException $e) {
            error_log("Erro ao buscar contas a pagar: " . $e->getMessage());
            return 0.0;
        }
    }

    /**
     * Busca o total de Contas a Receber no mês.
     */
    public function getContasReceberMes(?string $mesReferencia = null)
    {
        try {
            $mesRef = $mesReferencia ?: date('Y-m');
            // Ignora transferências internas
            $stmt = $this->db->prepare(
                "SELECT SUM((valor + COALESCE(juros, 0) - COALESCE(desconto, 0)) - COALESCE(valor_pago, 0)) as total FROM transacoes 
                 WHERE tipo = 'R' AND status IN ('Pendente', 'Atrasado', 'Pago Parcial') 
                 AND (documento_vinculado IS NULL OR documento_vinculado NOT LIKE 'transfer_%')
                 AND DATE_FORMAT(vencimento, '%Y-%m') = :mes"
            );
            $stmt->bindValue(':mes', $mesRef);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'] ?? 0.0;
        } catch (PDOException $e) {
            error_log("Erro ao buscar contas a receber: " . $e->getMessage());
            return 0.0;
        }
    }

    /**
     * Busca a lista de Contas a Pagar no mês (para detalhamento no dashboard).
     */
    public function getListaContasPagarMes(int $limit = 5, ?string $mesReferencia = null): array
    {
        try {
            $mesRef = $mesReferencia ?: date('Y-m');
            $sql = "SELECT id, descricao, valor, valor_pago, juros, desconto, vencimento, status 
                    FROM transacoes 
                    WHERE tipo = 'P' AND status IN ('Pendente', 'Atrasado', 'Pago Parcial') 
                    AND (documento_vinculado IS NULL OR documento_vinculado NOT LIKE 'transfer_%')
                    AND DATE_FORMAT(vencimento, '%Y-%m') = :mes
                    ORDER BY vencimento ASC
                    LIMIT :limit";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':mes', $mesRef);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar lista contas a pagar: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Busca a lista de Contas a Receber no mês (para detalhamento no dashboard).
     */
    public function getListaContasReceberMes(int $limit = 5, ?string $mesReferencia = null): array
    {
        try {
            $mesRef = $mesReferencia ?: date('Y-m');
            $sql = "SELECT id, descricao, valor, valor_pago, juros, desconto, vencimento, status 
                    FROM transacoes 
                    WHERE tipo = 'R' AND status IN ('Pendente', 'Atrasado', 'Pago Parcial') 
                    AND (documento_vinculado IS NULL OR documento_vinculado NOT LIKE 'transfer_%')
                    AND DATE_FORMAT(vencimento, '%Y-%m') = :mes
                    ORDER BY vencimento ASC
                    LIMIT :limit";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':mes', $mesRef);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar lista contas a receber: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Calcula o saldo atual, somando todas as receitas 'Pagas' e subtraindo todas as despesas 'Pagas'.
     */
    public function getSaldoAtual()
    {
        try {
            $stmt = $this->db->prepare("
                SELECT (
                    (SELECT COALESCE(SUM(saldo_inicial), 0) FROM bancos WHERE ativo = 1) +
                    (SELECT COALESCE(SUM(CASE WHEN status = 'Pago Parcial' THEN valor_pago ELSE valor + COALESCE(juros, 0) - COALESCE(desconto, 0) END), 0) FROM transacoes WHERE tipo = 'R' AND status IN ('Pago', 'Pago Parcial') AND banco_id IS NOT NULL AND (documento_vinculado IS NULL OR documento_vinculado NOT LIKE 'transfer_%')) -
                    (SELECT COALESCE(SUM(CASE WHEN status = 'Pago Parcial' THEN valor_pago ELSE valor + COALESCE(juros, 0) - COALESCE(desconto, 0) END), 0) FROM transacoes WHERE tipo = 'P' AND status IN ('Pago', 'Pago Parcial') AND banco_id IS NOT NULL AND (documento_vinculado IS NULL OR documento_vinculado NOT LIKE 'transfer_%')) +
                    (SELECT COALESCE(SUM(valor), 0) FROM transacoes WHERE documento_vinculado LIKE 'transfer_in:%' AND status = 'Pago' AND banco_id IS NOT NULL) -
                    (SELECT COALESCE(SUM(valor), 0) FROM transacoes WHERE documento_vinculado LIKE 'transfer_out:%' AND status = 'Pago' AND banco_id IS NOT NULL)
                )
                AS saldo_atual
            ");
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['saldo_atual'] ?? 0.0;
        } catch (PDOException $e) {
            error_log("Erro ao buscar saldo atual: " . $e->getMessage());
            return 0.0;
        }
    }

    /**
     * Busca a lista de todos os bancos cadastrados.
     * @return array
     */
    public function getBancos(): array
    {
        try {
            $stmt = $this->db->query("SELECT id, nome FROM bancos WHERE ativo = 1 ORDER BY nome ASC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar bancos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Busca a lista de todos os centros de custo.
     * @return array
     */
    public function getCentrosCusto(): array
    {
        try {
            $stmt = $this->db->query("SELECT id, nome FROM centros_custo ORDER BY nome ASC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar centros de custo: " . $e->getMessage());
            return [];
        }
    }


    /**
     * Calcula e retorna o saldo atual de cada banco.
     * O saldo é: Saldo Inicial + (Receitas Pagas) - (Despesas Pagas)
     * @return array
     */
    public function getSaldosBancos(): array
    {
        try {
            $sql = "
                SELECT 
                    MIN(b.id) as id,
                    b.nome,
                    MAX(b.logo) as logo,
                    MAX(b.tipo) as tipo,
                    SUM(b.saldo_inicial 
                        + COALESCE((SELECT SUM(CASE WHEN t.status = 'Pago Parcial' THEN t.valor_pago ELSE t.valor + COALESCE(t.juros, 0) - COALESCE(t.desconto, 0) END) FROM transacoes t WHERE t.banco_id = b.id AND t.tipo = 'R' AND t.status IN ('Pago', 'Pago Parcial') AND (t.documento_vinculado IS NULL OR t.documento_vinculado NOT LIKE 'transfer_%')), 0)
                        - COALESCE((SELECT SUM(CASE WHEN t.status = 'Pago Parcial' THEN t.valor_pago ELSE t.valor + COALESCE(t.juros, 0) - COALESCE(t.desconto, 0) END) FROM transacoes t WHERE t.banco_id = b.id AND t.tipo = 'P' AND t.status IN ('Pago', 'Pago Parcial') AND (t.documento_vinculado IS NULL OR t.documento_vinculado NOT LIKE 'transfer_%')), 0)
                        + COALESCE((SELECT SUM(t.valor) FROM transacoes t WHERE t.banco_id = b.id AND t.documento_vinculado LIKE 'transfer_in:%' AND t.status = 'Pago'), 0)
                        - COALESCE((SELECT SUM(t.valor) FROM transacoes t WHERE t.banco_id = b.id AND t.documento_vinculado LIKE 'transfer_out:%' AND t.status = 'Pago'), 0)
                    ) as saldo_atual
                FROM bancos b
                WHERE b.ativo = 1
                GROUP BY b.nome
                ORDER BY b.nome ASC
            ";
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar saldos dos bancos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Busca a lista de categorias de transações.
     * @param string|null $tipo Filtra por 'R' (Receita) ou 'P' (Despesa).
     * @return array
     */
    public function getClassificacoes(?string $tipo = null): array
    {
        try {
            if ($tipo) {
                // Se um tipo for especificado (R ou P), busca as categorias daquele tipo + as gerais (tipo IS NULL)
                $sql = "SELECT id, nome, tipo FROM transacao_classificacoes WHERE tipo = ? OR tipo IS NULL ORDER BY nome ASC";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$tipo]);
            } else {
                // Se nenhum tipo for especificado (ex: na tela de nova transação), busca TODAS as classificações
                $stmt = $this->db->query("SELECT id, nome, tipo FROM transacao_classificacoes ORDER BY nome ASC");
            }
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar categorias: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Busca categorias de transações filtradas por uma query.
     * @param string $query Termo de busca.
     * @param string|null $tipo Filtra por 'R' (Receita) ou 'P' (Despesa).
     * @param int $limit Limite de resultados.
     * @return array
     */
    public function searchClassificacoes(string $query, ?string $tipo = null, int $limit = 10): array
    {
        try {
            $sql = "SELECT id, nome, tipo FROM transacao_classificacoes WHERE nome LIKE :query";
            $params = [':query' => '%' . $query . '%'];

            if ($tipo) {
                $sql .= " AND (tipo = :tipo OR tipo IS NULL)";
                $params[':tipo'] = $tipo;
            }
            $sql .= " ORDER BY nome ASC LIMIT :limit";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar classificações por query: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Busca centros de custo filtrados por uma query.
     * @param string $query Termo de busca.
     * @param int $limit Limite de resultados.
     * @return array
     */
    public function searchCentrosCusto(string $query, int $limit = 10): array
    {
        $sql = "SELECT id, nome FROM centros_custo WHERE nome LIKE :query ORDER BY nome ASC LIMIT :limit";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':query', '%' . $query . '%');
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Adiciona uma nova categoria e retorna seu ID.
     * @param string $nome
     * @param string|null $tipo
     * @return int|false
     */
    public function adicionarClassificacao(string $nome, ?string $tipo)
    {
        try {
            // Verifica se já existe uma categoria com o mesmo nome
            $stmtCheck = $this->db->prepare("SELECT id FROM transacao_classificacoes WHERE nome = ?");
            $stmtCheck->execute([$nome]);
            if ($stmtCheck->fetch()) {
                $this->ultimoErro = "Já existe uma categoria com este nome.";
                return false;
            }

            $stmt = $this->db->prepare("INSERT INTO transacao_classificacoes (nome, tipo) VALUES (?, ?)");
            $stmt->execute([$nome, $tipo]);
            return (int)$this->db->lastInsertId();
        } catch (PDOException $e) {
            $this->ultimoErro = $e->getMessage();
            error_log("Erro ao adicionar categoria: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Adiciona um novo centro de custo e retorna seu ID.
     * @param string $nome
     * @return int|false
     */
    public function adicionarCentroCusto(string $nome)
    {
        try {
            // Verifica se já existe um centro de custo com este nome
            $stmtCheck = $this->db->prepare("SELECT id FROM centros_custo WHERE nome = ?");
            $stmtCheck->execute([$nome]);
            if ($stmtCheck->fetch()) {
                $this->ultimoErro = "Já existe um centro de custo com este nome.";
                return false;
            }

            $stmt = $this->db->prepare("INSERT INTO centros_custo (nome) VALUES (?)");
            $stmt->execute([$nome]);
            return (int)$this->db->lastInsertId();
        } catch (PDOException $e) {
            $this->ultimoErro = $e->getMessage();
            error_log("Erro ao adicionar centro de custo: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Busca um centro de custo pelo ID.
     */
    public function getCentroCustoPorId(int $id): ?array
    {
        try {
            $stmt = $this->db->prepare("SELECT id, nome FROM centros_custo WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            error_log("Erro ao buscar centro de custo por ID: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Alias para getCentroCustoPorId para manter compatibilidade com o controller.
     */
    public function getCentroCustoById(int $id): ?array
    {
        return $this->getCentroCustoPorId($id);
    }

    /**
     * Salva (insere ou atualiza) um centro de custo.
     */
    public function salvarCentroCusto(array $dados): bool
    {
        try {
            // Verifica duplicidade
            $sqlCheck = "SELECT id FROM centros_custo WHERE nome = :nome";
            $paramsCheck = [':nome' => $dados['nome']];
            if (!empty($dados['id'])) {
                $sqlCheck .= " AND id != :id";
                $paramsCheck[':id'] = $dados['id'];
            }
            $stmtCheck = $this->db->prepare($sqlCheck);
            $stmtCheck->execute($paramsCheck);
            if ($stmtCheck->fetch()) {
                $this->ultimoErro = "Já existe um centro de custo com este nome.";
                return false;
            }

            $sql = $dados['id']
                ? "UPDATE centros_custo SET nome = :nome WHERE id = :id"
                : "INSERT INTO centros_custo (nome) VALUES (:nome)";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':nome', $dados['nome']);
            if ($dados['id']) {
                $stmt->bindValue(':id', $dados['id'], PDO::PARAM_INT);
            }
            return $stmt->execute();
        } catch (PDOException $e) {
            $this->ultimoErro = $e->getMessage();
            error_log("Erro ao salvar centro de custo: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Exclui um centro de custo.
     */
    public function excluirCentroCusto(int $id): bool
    {
        try {
            // Verifica se o centro de custo está em uso
            $stmtCheck = $this->db->prepare("SELECT COUNT(*) FROM transacoes WHERE centro_custo_id = ?");
            $stmtCheck->execute([$id]);
            if ($stmtCheck->fetchColumn() > 0) {
                return false; // Impede a exclusão se estiver em uso
            }
            $stmt = $this->db->prepare("DELETE FROM centros_custo WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Erro ao excluir centro de custo: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Busca uma categoria específica pelo ID.
     * @param int $id
     * @return array|null
     */
    public function getClassificacaoPorId(int $id): ?array
    {
        try {
            $stmt = $this->db->prepare("SELECT id, nome, tipo FROM transacao_classificacoes WHERE id = ?");
            $stmt->execute([$id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (PDOException $e) {
            error_log("Erro ao buscar categoria por ID: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Alias para getClassificacaoPorId para manter compatibilidade com o controller.
     */
    public function getClassificacaoById(int $id): ?array
    {
        return $this->getClassificacaoPorId($id);
    }

    /**
     * Salva (insere ou atualiza) uma categoria.
     * @param array $dados
     * @return bool
     */
    public function salvarClassificacao(array $dados): bool
    {
        try {
            // Verifica duplicidade de nome
            $sqlCheck = "SELECT id FROM transacao_classificacoes WHERE nome = :nome";
            $paramsCheck = [':nome' => $dados['nome']];
            if (!empty($dados['id'])) {
                $sqlCheck .= " AND id != :id";
                $paramsCheck[':id'] = $dados['id'];
            }
            $stmtCheck = $this->db->prepare($sqlCheck);
            $stmtCheck->execute($paramsCheck);
            if ($stmtCheck->fetch()) {
                $this->ultimoErro = "Já existe uma categoria com este nome.";
                return false;
            }

            $sql = $dados['id']
                ? "UPDATE transacao_classificacoes SET nome = :nome, tipo = :tipo WHERE id = :id"
                : "INSERT INTO transacao_classificacoes (nome, tipo) VALUES (:nome, :tipo)";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':nome', $dados['nome']);
            $stmt->bindValue(':tipo', $dados['tipo'] ?: null);
            if ($dados['id']) {
                $stmt->bindValue(':id', $dados['id'], PDO::PARAM_INT);
            }
            return $stmt->execute();
        } catch (PDOException $e) {
            $this->ultimoErro = $e->getMessage();
            error_log("Erro ao salvar categoria: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Exclui uma categoria.
     * @param int $id
     * @return bool
     */
    public function excluirClassificacao(int $id): bool
    {
        // A constraint da FK na tabela 'transacoes' (ON DELETE SET NULL) garantirá
        // que as transações associadas não sejam perdidas, apenas desvinculadas.
        try {
            $stmt = $this->db->prepare("DELETE FROM transacao_classificacoes WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            $this->ultimoErro = "Erro ao excluir categoria: " . $e->getMessage();
            error_log($this->ultimoErro);
            return false;
        }
    }

    /**
     * Busca os detalhes de uma transação específica pelo ID.
     * @param int $id O ID da transação.
     * @return array|null Retorna os dados da transação ou null se não encontrada.
     */
    /**
     * Busca os detalhes de uma transação específica pelo ID.
     * @param int $id O ID da transação.
     * @return array|null Retorna os dados da transação ou null se não encontrada.
     */
    public function getTransacaoPorId(int $id): ?array
    {
        try {
            // Adicionados: juros, desconto, forma_pagamento, data_pagamento, prestacao_categoria_id.
            $cols = "id, tipo, descricao, valor, valor_pago, vencimento, data_emissao as dataEmissao, status, documento_vinculado as documentoVinculado, observacoes, banco_id, classificacao_id, prestacao_categoria_id, centro_custo_id, juros, desconto, iss_percentual, forma_pagamento, data_pagamento, cliente_id, fornecedor_id, contrato_parcela_id";
            if ($this->ensureUsuarioColumn()) {
                $cols .= ", usuario_id";
            }
            $stmt = $this->db->prepare("SELECT " . $cols . " FROM transacoes WHERE id = ?");

            $stmt->execute([$id]);
            $transacao = $stmt->fetch(PDO::FETCH_ASSOC);
            return $transacao ?: null;
        } catch (PDOException $e) {
            error_log("Erro ao buscar transação por ID: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Dado o valor do campo documento_vinculado de uma transação de transferência
     * retorna o ID da transação parceira (entrada/saída) ou null se não encontrada.
     * Exemplo: se documento_vinculado = 'transfer_out:trf_5f2a', procura 'transfer_in:trf_5f2a'
     */
    public function encontrarIdParceiroTransferenciaPorDocumento(string $documento): ?int
    {
        try {
            if (strpos($documento, 'transfer_out:') === 0) {
                $key = substr($documento, strlen('transfer_out:'));
                $partnerDoc = 'transfer_in:' . $key;
            } elseif (strpos($documento, 'transfer_in:') === 0) {
                $key = substr($documento, strlen('transfer_in:'));
                $partnerDoc = 'transfer_out:' . $key;
            } else {
                return null;
            }

            $stmt = $this->db->prepare('SELECT id FROM transacoes WHERE documento_vinculado = :doc LIMIT 1');
            $stmt->execute([':doc' => $partnerDoc]);
            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            return $res ? (int) $res['id'] : null;
        } catch (PDOException $e) {
            error_log('Erro ao buscar parceiro de transferência: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Salva ou atualiza uma transação no banco de dados.
     * @param array $dados Os dados da transação.
     * @return int|false Retorna o ID da transação em sucesso, false em falha.
     */
    /**
     * Salva ou atualiza uma transação no banco de dados.
     * @param array $dados Os dados da transação.
     * @return int|false Retorna o ID da transação em sucesso, false em falha.
     */
    public function salvarTransacao(array $dados)
    {
        $id = $dados['id'] ?? null;

        if ($id) {
            // Lógica de UPDATE com os novos campos
            $sql = "UPDATE transacoes SET 
                        tipo = :tipo,
                        descricao = :descricao,
                        valor = :valor,
                        valor_pago = :valor_pago,
                        vencimento = :vencimento,
                        data_pagamento = :data_pagamento,
                        data_emissao = :dataEmissao,
                        status = :status,
                        documento_vinculado = :documentoVinculado,
                        observacoes = :observacoes,
                        banco_id = :banco_id,
                        classificacao_id = :classificacao_id,
                        prestacao_categoria_id = :prestacao_categoria_id,
                        centro_custo_id = :centro_custo_id,
                        contrato_parcela_id = :contrato_parcela_id,
                        juros = :juros,
                        desconto = :desconto,
                        iss_percentual = :iss_percentual,
                        forma_pagamento = :forma_pagamento,
                        cliente_id = :cliente_id,
                        fornecedor_id = :fornecedor_id
                    WHERE id = :id";
        } else {
            // Lógica de INSERT com os novos campos (inclui usuario_id apenas se tabela suportar)
            if ($this->ensureUsuarioColumn()) {
                $sql = "INSERT INTO transacoes (tipo, descricao, valor, valor_pago, vencimento, data_pagamento, data_emissao, status, documento_vinculado, observacoes, banco_id, classificacao_id, prestacao_categoria_id, centro_custo_id, contrato_parcela_id, juros, desconto, iss_percentual, forma_pagamento, usuario_id, cliente_id, fornecedor_id, created_at) 
                        VALUES (:tipo, :descricao, :valor, :valor_pago, :vencimento, :data_pagamento, :dataEmissao, :status, :documentoVinculado, :observacoes, :banco_id, :classificacao_id, :prestacao_categoria_id, :centro_custo_id, :contrato_parcela_id, :juros, :desconto, :iss_percentual, :forma_pagamento, :usuario_id, :cliente_id, :fornecedor_id, NOW())";
            } else {
                $sql = "INSERT INTO transacoes (tipo, descricao, valor, valor_pago, vencimento, data_pagamento, data_emissao, status, documento_vinculado, observacoes, banco_id, classificacao_id, prestacao_categoria_id, centro_custo_id, contrato_parcela_id, juros, desconto, iss_percentual, forma_pagamento, cliente_id, fornecedor_id, created_at) 
                        VALUES (:tipo, :descricao, :valor, :valor_pago, :vencimento, :data_pagamento, :dataEmissao, :status, :documentoVinculado, :observacoes, :banco_id, :classificacao_id, :prestacao_categoria_id, :centro_custo_id, :contrato_parcela_id, :juros, :desconto, :iss_percentual, :forma_pagamento, :cliente_id, :fornecedor_id, NOW())";
            }
        }

        try {
            $stmt = $this->db->prepare($sql);

            $stmt->bindValue(':tipo', $dados['tipo'] ?? null);
            $stmt->bindValue(':descricao', $dados['descricao'] ?? null);
            $stmt->bindValue(':valor', $dados['valor'] ?? null);
            $stmt->bindValue(':valor_pago', $dados['valor_pago'] ?? 0);
            $stmt->bindValue(':vencimento', $dados['vencimento'] ?? null);
            $stmt->bindValue(':data_pagamento', (isset($dados['data_pagamento']) && $dados['data_pagamento'] !== '' ? $dados['data_pagamento'] : null), PDO::PARAM_STR);
            $stmt->bindValue(':dataEmissao', (isset($dados['dataEmissao']) && $dados['dataEmissao'] !== '' ? $dados['dataEmissao'] : null), PDO::PARAM_STR);
            $stmt->bindValue(':status', $dados['status'] ?? null);
            $stmt->bindValue(':documentoVinculado', (isset($dados['documentoVinculado']) && $dados['documentoVinculado'] !== '' ? $dados['documentoVinculado'] : null), PDO::PARAM_STR);
            $stmt->bindValue(':observacoes', (isset($dados['observacoes']) && $dados['observacoes'] !== '' ? $dados['observacoes'] : null), PDO::PARAM_STR);
            $stmt->bindValue(':banco_id', (isset($dados['banco_id']) && $dados['banco_id'] !== '' ? $dados['banco_id'] : null), PDO::PARAM_INT);
            $stmt->bindValue(':classificacao_id', (isset($dados['classificacao_id']) && $dados['classificacao_id'] !== '' ? $dados['classificacao_id'] : null), PDO::PARAM_INT);
            $stmt->bindValue(':prestacao_categoria_id', (isset($dados['prestacao_categoria_id']) && $dados['prestacao_categoria_id'] !== '' ? $dados['prestacao_categoria_id'] : null), PDO::PARAM_INT);
            $stmt->bindValue(':centro_custo_id', (isset($dados['centro_custo_id']) && $dados['centro_custo_id'] !== '' ? $dados['centro_custo_id'] : null), PDO::PARAM_INT);
            $stmt->bindValue(':contrato_parcela_id', (isset($dados['contrato_parcela_id']) && $dados['contrato_parcela_id'] !== '' ? $dados['contrato_parcela_id'] : null), PDO::PARAM_INT);

            // Novos campos vinculados
            $stmt->bindValue(':juros', (isset($dados['juros']) && $dados['juros'] !== '' ? $dados['juros'] : 0));
            $stmt->bindValue(':desconto', (isset($dados['desconto']) && $dados['desconto'] !== '' ? $dados['desconto'] : 0));
            $stmt->bindValue(':iss_percentual', (isset($dados['iss_percentual']) && $dados['iss_percentual'] !== '' ? $dados['iss_percentual'] : 0));
            $stmt->bindValue(':forma_pagamento', (isset($dados['forma_pagamento']) && $dados['forma_pagamento'] !== '' ? $dados['forma_pagamento'] : null), PDO::PARAM_STR);
            $stmt->bindValue(':cliente_id', (isset($dados['cliente_id']) && $dados['cliente_id'] !== '' ? $dados['cliente_id'] : null), PDO::PARAM_INT);
            $stmt->bindValue(':fornecedor_id', (isset($dados['fornecedor_id']) && $dados['fornecedor_id'] !== '' ? $dados['fornecedor_id'] : null), PDO::PARAM_INT);

            if ($id) {
                $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            } else {
                if ($this->ensureUsuarioColumn()) {
                    $stmt->bindValue(':usuario_id', (isset($dados['usuario_id']) && $dados['usuario_id'] !== '' ? $dados['usuario_id'] : null), PDO::PARAM_INT);
                }
            }

            if ($stmt->execute()) {
                // Determine what to return. prefer the provided id on update, otherwise
                // return the last insert id if it's a positive integer. Some environments
                // (or misconfigured tables) can return "0" as lastInsertId which is
                // technically a failure when used as a boolean but nonetheless means the
                // INSERT succeeded and a row exists with id=0. We still want to treat
                // that as success to avoid showing an error to users.
                if ($id) {
                    return (int)$id;
                }
                $last = (int)$this->db->lastInsertId();
                if ($last > 0) {
                    return $last;
                }
                // log suspicious case where an insert created id 0 or lastInsertId is
                // empty/zero so developers can investigate schema issues.
                error_log('salvarTransacao: inserção retornou lastInsertId=0, verificando esquema.');
                // return true rather than false so callers know the operation succeeded.
                return true;
            }
            $this->ultimoErro = "Não foi possível persistir os dados no banco.";
            return false;
        } catch (PDOException $e) {
            $this->ultimoErro = $e->getMessage();
            error_log("Erro ao salvar transação: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Busca uma lista de transações, com opção de filtro por tipo.
     * @param string|null $tipo 'P' para despesas, 'R' para receitas.
     * @param string $ordenarPor Coluna para ordenação.
     * @param string $direcaoOrdenacao Direção da ordenação (ASC ou DESC).
     * @param int|null $limit
     * @param int|null $offset
     * @param array $filtros Array de filtros (status, data, valor, mes).
     * @return array
     */
    public function getTransacoes(?string $tipo = null, string $ordenarPor = 'vencimento', string $direcaoOrdenacao = 'DESC', ?int $limit = null, ?int $offset = null, array $filtros = []): array
    {
        try {
            $sql = "SELECT 
                        t.id, t.tipo, t.descricao, t.valor, t.valor_pago, t.vencimento, t.data_emissao, t.status,
                        t.documento_vinculado as documentoVinculado, t.observacoes, t.banco_id, t.classificacao_id, t.created_at,
                        t.centro_custo_id, t.juros, t.desconto, t.forma_pagamento, t.data_pagamento";
            
            // Verifica se as novas colunas existem antes de tentar selecioná-las para evitar crash
            $sql .= ", t.cliente_id, t.fornecedor_id";
            
            $sql .= " ,
                        b.nome as nome_banco,
                        cli.nome as nome_cliente,
                        forn.nome as nome_fornecedor,
                        COALESCE(NULLIF(tc.nome, ''), " . ($this->ensurePrestacaoCategoriaColumn() ? "NULLIF(pc.nome, ''), " : "") . "NULLIF(t.categoria, ''), 'Sem Categoria') as nome_classificacao,
                        cc.nome as nome_centro_custo";

            if ($this->ensurePrestacaoCategoriaColumn()) {
                // pc.nome já está sendo usado no COALESCE acima
            }
            if ($this->ensureUsuarioColumn()) {
                $sql .= ", t.usuario_id, u.nome as nome_usuario";
            }
            $sql .= " FROM transacoes t
                    LEFT JOIN bancos b ON t.banco_id = b.id
                    LEFT JOIN clientes cli ON t.cliente_id = cli.id
                    LEFT JOIN fornecedores forn ON t.fornecedor_id = forn.id
                    LEFT JOIN centros_custo cc ON t.centro_custo_id = cc.id
                    LEFT JOIN transacao_classificacoes tc ON t.classificacao_id = tc.id";
            
            if ($this->ensurePrestacaoCategoriaColumn()) {
                $sql .= " LEFT JOIN prestacao_categorias pc ON t.prestacao_categoria_id = pc.id";
            }
            
            if ($this->ensureUsuarioColumn()) {
                $sql .= "
                    LEFT JOIN usuarios u ON t.usuario_id = u.id";
            }

            $conditions = [];
            $params = [];

            if ($tipo) {
                $conditions[] = "t.tipo = :tipo";
                $params[':tipo'] = $tipo;
            }

            if (!empty($filtros['status'])) {
                $status = $filtros['status'];
                // Se o status for 'Atrasado', busca tanto as com status 'Atrasado'
                // quanto as 'Pendente' com data de vencimento passada.
                if (is_array($status)) {
                    if (!empty($status)) {
                        // Cria placeholders nomeados para cada status no array
                        $inQuery = [];
                        foreach ($status as $k => $v) {
                            $key = ":status_" . $k;
                            $inQuery[] = $key;
                            $params[$key] = $v;
                        }
                        $conditions[] = "t.status IN (" . implode(',', $inQuery) . ")";
                    }
                } elseif ($status === 'Atrasado') {
                    $conditions[] = "(t.status = 'Atrasado' OR (t.status = 'Pendente' AND t.vencimento < CURDATE()) OR (t.status = 'Pago Parcial' AND t.vencimento < CURDATE()))";
                } elseif ($status === 'Pendente') {
                    $conditions[] = "(t.status = 'Pendente' OR t.status = 'Pago Parcial')";
                } else {
                    $conditions[] = "t.status = :status";
                    $params[':status'] = $status;
                }
            }

            if (!empty($filtros['data'])) {
                $conditions[] = "t.vencimento = :data";
                $params[':data'] = $filtros['data'];
            }

            if (!empty($filtros['data_pagamento'])) {
                $conditions[] = "t.data_pagamento = :data_pagamento";
                $params[':data_pagamento'] = $filtros['data_pagamento'];
            }

            if (!empty($filtros['valor'])) {
                $conditions[] = "t.valor = :valor";
                $params[':valor'] = $filtros['valor'];
            }

            if (!empty($filtros['mes'])) {
                $conditions[] = "DATE_FORMAT(t.vencimento, '%Y-%m') = :mes";
                $params[':mes'] = $filtros['mes'];
            }

            if (!empty($filtros['descricao'])) {
                $conditions[] = "t.descricao LIKE :descricao";
                if (strpos($filtros['descricao'], '%') !== false) {
                    $params[':descricao'] = $filtros['descricao'];
                } else {
                    $params[':descricao'] = '%' . $filtros['descricao'] . '%';
                }
            }

            if ($this->ensureUsuarioColumn() && !empty($filtros['usuario_id'])) {
                $conditions[] = "t.usuario_id = :usuario_id";
                $params[':usuario_id'] = $filtros['usuario_id'];
            }

            if (!empty($conditions)) {
                $sql .= " WHERE " . implode(" AND ", $conditions);
            }

            $sql .= " GROUP BY t.id";
            $sql .= " ORDER BY {$ordenarPor} {$direcaoOrdenacao}"; // A ordenação deve ser segura e não vir de input direto do usuário

            if ($limit !== null && $offset !== null) {
                $sql .= " LIMIT :limit OFFSET :offset";
            }

            $stmt = $this->db->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            if ($limit !== null) {
                $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            }
            if ($offset !== null) {
                $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            }

            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar transações: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Conta o número total de transações, com filtro opcional por tipo.
     * @param string|null $tipo
     * @param array $filtros
     * @return int
     */
    public function getContagemTransacoes(?string $tipo = null, array $filtros = []): int
    {
        try {
            $sql = "SELECT COUNT(*) FROM transacoes t";

            $conditions = [];
            $params = [];

            if ($tipo) {
                $conditions[] = "t.tipo = :tipo";
                $params[':tipo'] = $tipo;
            }

            if (!empty($filtros['status'])) {
                $status = $filtros['status'];
                if (is_array($status)) {
                    if (!empty($status)) {
                        $inQuery = [];
                        foreach ($status as $k => $v) {
                            $key = ":status_" . $k;
                            $inQuery[] = $key;
                            $params[$key] = $v;
                        }
                        $conditions[] = "t.status IN (" . implode(',', $inQuery) . ")";
                    }
                } elseif ($status === 'Atrasado') {
                    $conditions[] = "(t.status = 'Atrasado' OR (t.status = 'Pendente' AND t.vencimento < CURDATE()) OR (t.status = 'Pago Parcial' AND t.vencimento < CURDATE()))";
                } elseif ($status === 'Pendente') {
                    $conditions[] = "(t.status = 'Pendente' OR t.status = 'Pago Parcial')";
                } else {
                    $conditions[] = "t.status = :status";
                    $params[':status'] = $status;
                }
            }

            if (!empty($filtros['data'])) {
                $conditions[] = "t.vencimento = :data";
                $params[':data'] = $filtros['data'];
            }

            if (!empty($filtros['data_pagamento'])) {
                $conditions[] = "t.data_pagamento = :data_pagamento";
            }

            if (!empty($filtros['valor'])) {
                $conditions[] = "t.valor = :valor";
            }

            if (!empty($filtros['mes'])) {
                $conditions[] = "DATE_FORMAT(t.vencimento, '%Y-%m') = :mes";
                $params[':mes'] = $filtros['mes'];
            }

            if (!empty($filtros['descricao'])) {
                $conditions[] = "t.descricao LIKE :descricao";
                // Se o valor do filtro já contém um wildcard (%), usa-o como está (para buscas "começa com").
                // Caso contrário, envolve com wildcards para uma busca "contém".
                if (strpos($filtros['descricao'], '%') !== false) {
                    $params[':descricao'] = $filtros['descricao'];
                } else {
                    $params[':descricao'] = '%' . $filtros['descricao'] . '%';
                }
            }

            if ($this->ensureUsuarioColumn() && !empty($filtros['usuario_id'])) {
                $conditions[] = "t.usuario_id = :usuario_id";
                $params[':usuario_id'] = $filtros['usuario_id'];
            }

            if (!empty($conditions)) {
                $sql .= " WHERE " . implode(" AND ", $conditions);
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Erro ao contar transações: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Aprova uma prestação de contas, mapeando sua categoria temporária para a categoria financeira final.
     * @param int $id O ID da transação (prestação de contas).
     * @return bool
     */
    public function aprovarPrestacaoDeContas(int $id): bool
    {
        $this->db->beginTransaction();
        try {
            // 1. Buscar a transação e o nome da categoria de prestação atual
            $stmt = $this->db->prepare("
                SELECT t.prestacao_categoria_id, pc.nome as nome_categoria 
                FROM transacoes t
                LEFT JOIN prestacao_categorias pc ON t.prestacao_categoria_id = pc.id
                WHERE t.id = ?
            ");
            $stmt->execute([$id]);
            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            $prestacaoCategoriaId = $res['prestacao_categoria_id'] ?? null;
            $nomeCategoria = $res['nome_categoria'] ?? null;

            $targetClassificacaoId = null;
            if ($prestacaoCategoriaId) {
                // 2.a. Tenta buscar pelo ID mapeado (target_classificacao_id), ignorando erro se coluna não existir
                try {
                    $stmtMap = $this->db->prepare("SELECT target_classificacao_id FROM prestacao_categorias WHERE id = ?");
                    $stmtMap->execute([$prestacaoCategoriaId]);
                    $targetClassificacaoId = $stmtMap->fetchColumn();
                } catch (\Exception $e) { /* Ignora se coluna não existir */ }

                // 2.b. Se não achou mapeamento, busca pelo NOME na tabela financeira ou CRIA uma nova
                if (!$targetClassificacaoId && $nomeCategoria) {
                    $stmtFind = $this->db->prepare("SELECT id FROM transacao_classificacoes WHERE nome = ? LIMIT 1");
                    $stmtFind->execute([$nomeCategoria]);
                    $targetClassificacaoId = $stmtFind->fetchColumn();
                    
                    if (!$targetClassificacaoId) {
                        $stmtCreate = $this->db->prepare("INSERT INTO transacao_classificacoes (nome, tipo) VALUES (?, 'P')");
                        $stmtCreate->execute([$nomeCategoria]);
                        $targetClassificacaoId = $this->db->lastInsertId();
                    }
                }
            }

            // 3. Atualizar a transação: status para 'Pendente' e o classificacao_id mapeado
            $stmtUpdate = $this->db->prepare(
                "UPDATE transacoes 
                 SET status = 'Pendente', classificacao_id = COALESCE(:target_id, classificacao_id)
                 WHERE id = :id"
            );
            $stmtUpdate->execute([
                ':target_id' => $targetClassificacaoId,
                ':id' => $id
            ]);

            return $this->db->commit();
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Erro ao aprovar prestação de contas: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Exclui uma transação do banco de dados.
     * @param int $id O ID da transação a ser excluída.
     * @return bool Retorna true em sucesso, false em falha.
     */
    public function excluirTransacao(int $id): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM transacoes WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Erro ao excluir transação: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Exclui múltiplas transações do banco de dados.
     * @param array $ids Os IDs das transações a serem excluídas.
     * @return bool Retorna true em sucesso, false em falha.
     */
    public function excluirTransacoes(array $ids): bool
    {
        if (empty($ids)) {
            return false;
        }

        // Garante que todos os IDs são inteiros
        $ids = array_map('intval', $ids);

        try {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $stmt = $this->db->prepare("DELETE FROM transacoes WHERE id IN ($placeholders)");
            return $stmt->execute($ids);
        } catch (PDOException $e) {
            error_log("Erro ao excluir transações em massa: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Liquida (marca como Pago) múltiplas transações.
     * @param array $ids
     * @param string $dataPagamento
     * @return bool
     */
    public function liquidarTransacoes(array $ids, string $dataPagamento): bool
    {
        if (empty($ids)) {
            return false;
        }
        $ids = array_map('intval', $ids);
        try {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $sql = "UPDATE transacoes SET status = 'Pago', valor_pago = valor, data_pagamento = ? WHERE id IN ($placeholders)";
            $params = array_merge([$dataPagamento], $ids);
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Erro ao liquidar transações em massa: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Registra uma baixa parcial em uma transação, atualizando valor_pago e status.
     * @param int $id ID da transação
     * @param float $valorPago Valor pago nesta parcela
     * @param string $dataPagamento Data do pagamento
     * @param string $formaPagamento Forma de pagamento
     * @return array ['success' => bool, 'status' => string, 'message' => string]
     */
    public function registrarBaixaParcial(int $id, float $valorPago, string $dataPagamento, string $formaPagamento = ''): array
    {
        try {
            $transacao = $this->getTransacaoPorId($id);
            if (!$transacao) {
                return ['success' => false, 'status' => 'error', 'message' => 'Transação não encontrada.'];
            }

            $novoValorPago = ($transacao['valor_pago'] ?? 0) + $valorPago;
            $valorOriginal = $transacao['valor'] ?? 0;

            if ($novoValorPago > $valorOriginal) {
                return ['success' => false, 'status' => 'error', 'message' => 'Valor pago excede o valor original da transação.'];
            }

            if ($novoValorPago >= $valorOriginal) {
                $novoStatus = 'Pago';
                $novoValorPago = $valorOriginal;
            } else {
                $novoStatus = 'Pago Parcial';
            }

            $sql = "UPDATE transacoes SET 
                        valor_pago = :valor_pago,
                        status = :status,
                        data_pagamento = :data_pagamento,
                        forma_pagamento = COALESCE(:forma_pagamento, forma_pagamento)
                    WHERE id = :id";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':valor_pago', $novoValorPago);
            $stmt->bindValue(':status', $novoStatus);
            $stmt->bindValue(':data_pagamento', $dataPagamento);
            $stmt->bindValue(':forma_pagamento', $formaPagamento ?: null);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);

            if ($stmt->execute()) {
                $saldoRestante = $valorOriginal - $novoValorPago;
                return [
                    'success' => true,
                    'status' => 'success',
                    'message' => "Baixa parcial registrada. Saldo restante: R$ " . number_format($saldoRestante, 2, ',', '.'),
                    'novo_status' => $novoStatus,
                    'valor_pago' => $novoValorPago,
                    'saldo_restante' => $saldoRestante
                ];
            }
            return ['success' => false, 'status' => 'error', 'message' => 'Erro ao registrar baixa parcial.'];
        } catch (PDOException $e) {
            error_log("Erro ao registrar baixa parcial: " . $e->getMessage());
            return ['success' => false, 'status' => 'error', 'message' => 'Erro no banco de dados: ' . $e->getMessage()];
        }
    }

    /**
     * Atualiza o status de uma transação.
     * @param int $id O ID da transação.
     * @param string $status O novo status.
     * @param string|null $motivo Motivo opcional (para reprovação).
     * @return bool
     */
    public function atualizarStatus(int $id, string $status, ?string $motivo = null): bool
    {
        try {
            $sql = "UPDATE transacoes SET status = :status";
            $params = [':status' => $status, ':id' => $id];

            if ($motivo) {
                // Concatena o motivo às observações existentes
                $sql .= ", observacoes = CONCAT(COALESCE(observacoes, ''), :motivo)";
                $params[':motivo'] = "\n[REPROVADO em " . date('d/m/Y') . "]: " . $motivo;
            }

            $sql .= " WHERE id = :id";


            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Erro ao atualizar status da transação: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Atualiza o status de múltiplas transações de uma vez.
     * @param array $ids
     * @param string $novoStatus
     * @param string|null $motivo Motivo opcional (para reprovação).
     * @return bool
     */
    public function atualizarStatusEmMassa(array $ids, string $novoStatus, ?string $motivo = null): bool
    {
        if (empty($ids)) {
            return false;
        }
        $ids = array_map('intval', $ids);
        try {
            $params = [$novoStatus];
            $placeholders = implode(',', array_fill(0, count($ids), '?'));

            if ($motivo) {
                $motivoFormatado = "\n[REPROVADO em " . date('d/m/Y') . "]: " . $motivo;
                $sql = "UPDATE transacoes SET status = ?, observacoes = CONCAT(COALESCE(observacoes, ''), ?) WHERE id IN ($placeholders)";
                array_push($params, $motivoFormatado);
            } else {
                $sql = "UPDATE transacoes SET status = ? WHERE id IN ($placeholders)";
            }

            $params = array_merge($params, $ids);
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Erro ao atualizar status em massa: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Bloqueia (Cancela) uma transação, definindo seu status como 'Cancelado'.
     * Isso faz com que ela não seja contabilizada nos saldos e relatórios.
     * @param int $id O ID da transação.
     * @return bool
     */
    public function bloquearTransacao(int $id): bool
    {
        try {
            $stmt = $this->db->prepare("UPDATE transacoes SET status = 'Cancelado' WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Erro ao bloquear transação: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Desbloqueia uma transação, definindo seu status como 'Pendente'.
     * @param int $id O ID da transação.
     * @return bool
     */
    public function desbloquearTransacao(int $id): bool
    {
        try {
            $stmt = $this->db->prepare("UPDATE transacoes SET status = 'Pendente' WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Erro ao desbloquear transação: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Busca a data do próximo vencimento de contas a pagar.
     * @return string|null A data do próximo vencimento ou null se não houver.
     */
    public function getProximoVencimentoPagar(): ?string
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT MIN(vencimento) as proximo_vencimento 
                 FROM transacoes 
                 WHERE tipo = 'P' 
                 AND status IN ('Pendente', 'Atrasado', 'Pago Parcial') 
                 AND vencimento >= CURDATE()"
            );
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['proximo_vencimento'] ?? null;
        } catch (PDOException $e) {
            error_log("Erro ao buscar próximo vencimento a pagar: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Conta o número de contas a receber que estão com status 'Atrasado'.
     * @return int
     */
    public function getContagemContasReceberAtrasadas(): int
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT COUNT(*) 
                 FROM transacoes 
                 WHERE tipo = 'R' 
                 AND (status = 'Atrasado' OR (status = 'Pago Parcial' AND vencimento < CURDATE()))"
            );
            $stmt->execute();
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Erro ao contar contas a receber atrasadas: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Busca a data da última transação paga (receita ou despesa).
     * @return string|null A data da última atualização ou null se não houver.
     */
    public function getUltimaAtualizacaoSaldo(): ?string
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT MAX(data_pagamento) as ultima_atualizacao 
                 FROM transacoes 
                 WHERE status IN ('Pago', 'Pago Parcial') AND data_pagamento IS NOT NULL"
            );
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['ultima_atualizacao'] ?? null;
        } catch (PDOException $e) {
            error_log("Erro ao buscar a data da última atualização de saldo: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Retorna um resumo (contagem e valor total) das contas a pagar que estão atrasadas.
     * Uma conta é considerada atrasada se seu status for 'Pendente' e a data de vencimento for anterior à data atual.
     *
     * @return array Um array associativo com as chaves 'count' e 'valor'.
     */
    public function getResumoContasPagarAtrasadas(): array
    {
        $query = "
            SELECT 
                COUNT(id) as count,
                COALESCE(SUM(valor - COALESCE(valor_pago, 0)), 0) as valor
            FROM transacoes 
            WHERE tipo = 'P' 
              AND (status = 'Pendente' OR status = 'Pago Parcial')
              AND vencimento < CURDATE()
        ";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            return [
                'count' => (int) ($result['count'] ?? 0),
                'valor' => (float) ($result['valor'] ?? 0.0),
            ];
        } catch (\PDOException $e) {
            error_log("Erro ao buscar resumo de contas a pagar atrasadas: " . $e->getMessage());
            return ['count' => 0, 'valor' => 0.0];
        }
    }

    /**
     * Retorna um resumo (contagem e valor total) das contas a receber que estão atrasadas.
     * Uma conta é considerada atrasada se seu status for 'Pendente' e a data de vencimento for anterior à data atual.
     *
     * @return array Um array associativo com as chaves 'count' e 'valor'.
     */
    public function getResumoContasReceberAtrasadas(): array
    {
        $query = "
            SELECT 
                COUNT(id) as count,
                COALESCE(SUM(valor - COALESCE(valor_pago, 0)), 0) as valor
            FROM transacoes 
            WHERE tipo = 'R' 
              AND (status = 'Pendente' OR status = 'Pago Parcial')
              AND vencimento < CURDATE()
        ";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            return [
                'count' => (int) ($result['count'] ?? 0),
                'valor' => (float) ($result['valor'] ?? 0.0),
            ];
        } catch (\PDOException $e) {
            error_log("Erro ao buscar resumo de contas a receber atrasadas: " . $e->getMessage());
            return ['count' => 0, 'valor' => 0.0];
        }
    }

    /**
     * Busca transações associadas a um fornecedor (pessoa_id).
     * @param int $pessoaId
     * @param string $tipo 'P' para despesas (compras), 'R' para receitas.
     * @param int $limit Limite de registros (padrão 10).
     * @return array
     */
    public function getTransacoesPorPessoaId(int $pessoaId, string $tipo, int $limit = 10, int $offset = 0): array
    {
        // CORREÇÃO: A query agora busca transações diretamente pelo pessoa_id,
        // tornando-a mais abrangente e não dependente de um contrato.
            $sql = "SELECT 
                        t.id,
                        t.descricao,
                        t.valor,
                        t.valor_pago,
                        t.vencimento,
                        t.data_pagamento,
                        t.status
                    FROM transacoes t";
            
            // Tenta usar cliente_id para Receitas e fornecedor_id para Despesas
            $colVinculo = ($tipo === 'R') ? 'cliente_id' : 'fornecedor_id';
            
            $sql .= " WHERE t.{$colVinculo} = :pessoa_id AND t.tipo = :tipo
                ORDER BY t.vencimento DESC 
                LIMIT :limit OFFSET :offset";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':pessoa_id', $pessoaId, PDO::PARAM_INT);
            $stmt->bindValue(':tipo', $tipo, PDO::PARAM_STR);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Erro ao buscar transações por pessoa_id: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Conta o total de transações de uma pessoa para paginação.
     */
    public function getCountTransacoesPorPessoaId(int $pessoaId, string $tipo): int
    {
        try {
            $colVinculo = ($tipo === 'R') ? 'cliente_id' : 'fornecedor_id';
            $sql = "SELECT COUNT(*) FROM transacoes WHERE {$colVinculo} = :pessoa_id AND tipo = :tipo";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':pessoa_id', $pessoaId, PDO::PARAM_INT);
            $stmt->bindValue(':tipo', $tipo, PDO::PARAM_STR);
            $stmt->execute();
            return (int) $stmt->fetchColumn();
        } catch (\PDOException $e) {
            error_log("Erro ao contar transações por pessoa_id: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Cria duas movimentações (saída e entrada) para registrar uma transferência entre contas.
     * Utiliza uma transação para garantir a atomicidade da operação.
     *
     * @param int $contaOrigemId ID da conta de origem.
     * @param int $contaDestinoId ID da conta de destino.
     * @param float $valor O valor a ser transferido.
     * @param string $data A data da transferência (formato Y-m-d).
     * @return bool Retorna true em caso de sucesso, false em caso de falha.
     * @throws \Exception Em caso de falha na validação ou execução da query.
     */
    public function criarTransferencia(int $contaOrigemId, int $contaDestinoId, float $valor, string $data, string $tipoMovimentacao = 'Transferência'): bool
    {
        // Busca os nomes das contas para a descrição
        $contaOrigem = $this->getBancoPorId($contaOrigemId);
        $contaDestino = $this->getBancoPorId($contaDestinoId);

        if (!$contaOrigem || !$contaDestino) {
            throw new \Exception("Conta de origem ou destino não encontrada.");
        }

        $descricaoSaida = "Transferência para " . $contaDestino['nome'];
        $descricaoEntrada = "Transferência de " . $contaOrigem['nome'];

        // Gera uma chave única para vincular ambas as transações
        $key = uniqid('trf_');

        // Inicia a transação
        $this->db->beginTransaction();

        try {
            // 1. Cria a movimentação de SAÍDA na conta de origem
            $sql = "INSERT INTO transacoes (banco_id, descricao, valor, tipo, vencimento, data_pagamento, status, documento_vinculado, observacoes, created_at) 
                    VALUES (:banco_id, :descricao, :valor, :tipo, :data, :data_pagamento, 'Pago', :documento_vinculado, :observacoes, NOW())";

            $stmtOut = $this->db->prepare($sql);
            $stmtOut->execute([
                ':banco_id' => $contaOrigemId,
                ':descricao' => $descricaoSaida,
                ':valor' => $valor,
                ':tipo' => $tipoMovimentacao,
                ':data' => $data,
                ':data_pagamento' => $data,
                ':documento_vinculado' => 'transfer_out:' . $key,
                ':observacoes' => 'transferencia_out'
            ]);

            // 2. Cria a movimentação de ENTRADA na conta de destino
            $stmtIn = $this->db->prepare($sql);
            $stmtIn->execute([
                ':banco_id' => $contaDestinoId,
                ':descricao' => $descricaoEntrada,
                ':valor' => $valor,
                ':tipo' => $tipoMovimentacao,
                ':data' => $data,
                ':data_pagamento' => $data,
                ':documento_vinculado' => 'transfer_in:' . $key,
                ':observacoes' => 'transferencia_in'
            ]);

            $this->db->commit();
            return true;
        } catch (\PDOException $e) {
            $this->db->rollBack();
            error_log('Erro na transferência: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Busca uma conta bancária pelo ID.
     * (Este é um método auxiliar para o criarTransferencia).
     */
    public function getBancoPorId(int $id)
    {
        $stmt = $this->db->prepare("SELECT * FROM bancos WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Verifica se existem transações pendentes ou atrasadas para uma entidade.
     * @param int $id ID da entidade.
     * @param string $coluna Nome da coluna (fornecedor_id ou cliente_id).
     * @param string|null $tipo Opcional: Filtra por tipo 'P' (Despesa) ou 'R' (Receita).
     * @return bool
     */
    public function temTransacoesPendentes(int $id, string $coluna = 'fornecedor_id', ?string $tipo = null): bool
    {
        try {
            $sql = "SELECT COUNT(*) FROM transacoes WHERE {$coluna} = :id AND status IN ('Pendente', 'Atrasado')";
            if ($tipo) {
                $sql .= " AND tipo = :tipo";
            }
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            if ($tipo) {
                $stmt->bindValue(':tipo', $tipo);
            }
            $stmt->execute();
            return (int)$stmt->fetchColumn() > 0;
        } catch (\PDOException $e) {
            error_log("Erro ao verificar pendências financeiras ($coluna): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Inicia uma transação no banco de dados.
     */
    public function iniciarTransacao()
    {
        return $this->db->beginTransaction();
    }

    /**
     * Confirma uma transação.
     */
    public function confirmarTransacao()
    {
        return $this->db->commit();
    }

    /**
     * Desfaz uma transação.
     */
    public function desfazerTransacao()
    {
        return $this->db->rollBack();
    }

    /**
     * Verifica se existe uma transação idêntica criada recentemente (evitar duplicidade).
     * Verifica tipo, descrição, valor e vencimento criados no último minuto.
     * @param array $dados
     * @param bool $isRecurring Indica se a transação original era para ser recorrente.
     * @param string|null $recurringType Tipo de repetição ('parcelamento' ou 'recorrencia').
     * @param int|null $numInstallments Número total de parcelas/repetições.
     * @return bool
     */
    public function verificarDuplicidade(array $dados, bool $isRecurring = false, ?string $recurringType = null, ?int $numInstallments = null): bool
    {
        $descricao = trim($dados['descricao'] ?? '');
        $vencimento = $dados['vencimento'] ?? '';
        $valor = $dados['valor'] ?? 0;
        
        // Para parcelamento, o valor salvo na primeira linha é o valor total dividido pelas parcelas.
        $valorComparacao = $valor;
        if ($isRecurring && $numInstallments > 1 && $recurringType === 'parcelamento') {
            // Arredonda para 2 casas para bater com o tipo DECIMAL(15,2) do banco
            $valorComparacao = round($valor / $numInstallments, 2);
        } else {
            $valorComparacao = round($valor, 2);
        }

        // Verificação estrita de duplicidade usando o operador <=> (null-safe equality).
        // Isso garante que comparamos corretamente valores que podem ser nulos,
        // evitando que um lançamento sem fornecedor bloqueie um lançamento com fornecedor (ou vice-versa).
        $sql = "SELECT COUNT(*) FROM transacoes 
                WHERE tipo = :tipo 
                AND ABS(valor - :valor) < 0.01 
                AND vencimento = :vencimento
                AND status != 'Cancelado'
                AND (banco_id <=> :banco_id)
                AND (fornecedor_id <=> :fornecedor_id)
                AND (cliente_id <=> :cliente_id)
                AND (centro_custo_id <=> :centro_custo_id)
                AND (classificacao_id <=> :classificacao_id)";

        $params = [
            ':tipo' => $dados['tipo'],
            ':valor' => $valorComparacao,
            ':vencimento' => $vencimento,
            ':banco_id' => $dados['banco_id'] ?? null,
            ':fornecedor_id' => $dados['fornecedor_id'] ?? null,
            ':cliente_id' => $dados['cliente_id'] ?? null,
            ':centro_custo_id' => $dados['centro_custo_id'] ?? null,
            ':classificacao_id' => $dados['classificacao_id'] ?? null,
        ];

        if ($isRecurring && $numInstallments > 1) {
            // Procura pela descrição exata da primeira parcela gerada pelo controller
            $sufixo = ($recurringType === 'recorrencia') ? " (Recorrência 1/{$numInstallments})" : " (1/{$numInstallments})";
            $sql .= " AND descricao = :descricaoCompleta";
            $params[':descricaoCompleta'] = $descricao . $sufixo;
        } else {
            $sql .= " AND (descricao = :desc OR descricao LIKE CONCAT(:desc, ' (%/%)') OR descricao LIKE CONCAT(:desc, ' (Recorrência %/%)'))";
            $params[':desc'] = $descricao;
        }

        // Janela de segurança ampliada para 5 minutos para lidar com latência de rede/servidor
        // e possíveis diferenças de fuso horário entre PHP e Banco de Dados.
        // Usamos uma margem maior para garantir que o registro anterior seja detectado, 
        // mesmo se o processo de inserção em lote tenha sido demorado.
        $sql .= " AND created_at > DATE_SUB(NOW(), INTERVAL 5 MINUTE)";

        try {
            $stmt = $this->db->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();

            return (int)$stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Erro ao verificar duplicidade: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Busca transações de combustível baseadas na presença de dados de veículo nas observações.
     * @param string|null $placa
     * @param string|null $dataInicio
     * @param string|null $dataFim
     * @return array
     */
    public function getRelatorioCombustivel(?string $placa = null, ?string $dataInicio = null, ?string $dataFim = null): array
    {
        try {
            $sql = "SELECT t.*, tc.nome as nome_classificacao 
                    FROM transacoes t
                    LEFT JOIN transacao_classificacoes tc ON t.classificacao_id = tc.id
                    WHERE t.observacoes LIKE '%Placa:%'";

            $params = [];

            if ($placa) {
                $sql .= " AND t.observacoes LIKE :placa";
                $params[':placa'] = "%Placa: " . $placa . "%";
            }

            if ($dataInicio) {
                $sql .= " AND t.vencimento >= :dataInicio";
                $params[':dataInicio'] = $dataInicio;
            }

            if ($dataFim) {
                $sql .= " AND t.vencimento <= :dataFim";
                $params[':dataFim'] = $dataFim;
            }

            $sql .= " ORDER BY t.vencimento DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Processa os resultados para extrair os dados das observações
            foreach ($resultados as &$row) {
                $obs = $row['observacoes'];
                $row['placa_veiculo'] = preg_match('/Placa: (.*?) \| /', $obs, $m) ? $m[1] : '';
                $row['litros'] = preg_match('/Litros: (.*?) \| /', $obs, $m) ? (float)$m[1] : 0;
                $row['hodometro'] = preg_match('/Hodômetro: (.*?) \| /', $obs, $m) ? (int)$m[1] : 0;
            }

            return $resultados;
        } catch (PDOException $e) {
            error_log("Erro ao buscar relatório de combustível: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Calcula o saldo no início de um ano específico.
     * @param int $ano
     * @return float
     */
    public function getSaldoInicioAno(int $ano): float
    {
        try {
            $dataInicioAno = "$ano-01-01";
            $sql = "
                SELECT (
                    (SELECT COALESCE(SUM(saldo_inicial), 0) FROM bancos) +
                    (SELECT COALESCE(SUM(CASE WHEN status = 'Pago Parcial' THEN valor_pago ELSE valor + COALESCE(juros, 0) - COALESCE(desconto, 0) END), 0) FROM transacoes WHERE tipo = 'R' AND status IN ('Pago', 'Pago Parcial') AND data_pagamento < :data_inicio) -
                    (SELECT COALESCE(SUM(CASE WHEN status = 'Pago Parcial' THEN valor_pago ELSE valor + COALESCE(juros, 0) - COALESCE(desconto, 0) END), 0) FROM transacoes WHERE tipo = 'P' AND status IN ('Pago', 'Pago Parcial') AND data_pagamento < :data_inicio)
                ) AS saldo_inicio_ano
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':data_inicio', $dataInicioAno);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (float)($result['saldo_inicio_ano'] ?? 0.0);
        } catch (PDOException $e) {
            error_log("Erro ao buscar saldo no início do ano: " . $e->getMessage());
            return 0.0;
        }
    }

    /**
     * Busca o balanço mensal (receitas e despesas) para um ano específico.
     * Agrupa por mês e soma os valores (considerando todas as transações não canceladas).
     * @param int $ano
     * @return array
     */
    public function getBalancoMensal(int $ano): array
    {
        try {
            $sql = "SELECT
                        mes,
                        SUM(receitas_previstas) as receitas_previstas,
                        SUM(despesas_previstas) as despesas_previstas,
                        SUM(receitas_realizadas) as receitas_realizadas,
                        SUM(despesas_realizadas) as despesas_realizadas
                    FROM (
                        -- Previstas (Regime de Competência - baseadas na data de vencimento)
                        SELECT
                            DATE_FORMAT(vencimento, '%Y-%m') as mes,
                            SUM(CASE WHEN tipo = 'R' AND (documento_vinculado IS NULL OR documento_vinculado NOT LIKE 'transfer_%') THEN (valor - COALESCE(valor_pago, 0)) ELSE 0 END) as receitas_previstas,
                            SUM(CASE WHEN tipo = 'P' AND (documento_vinculado IS NULL OR documento_vinculado NOT LIKE 'transfer_%') THEN (valor - COALESCE(valor_pago, 0)) ELSE 0 END) as despesas_previstas,
                            0 as receitas_realizadas,
                            0 as despesas_realizadas
                        FROM transacoes
                        WHERE
                            status != 'Cancelado' AND
                            YEAR(vencimento) = :ano_previstas AND
                            (documento_vinculado IS NULL OR documento_vinculado NOT LIKE 'transfer_%')
                        GROUP BY mes

                        UNION ALL

                        -- Realizadas (Regime de Caixa - baseadas na data de pagamento)
                        SELECT
                            DATE_FORMAT(data_pagamento, '%Y-%m') as mes,
                            0 as receitas_previstas,
                            0 as despesas_previstas,
                            SUM(CASE WHEN tipo = 'R' AND (documento_vinculado IS NULL OR documento_vinculado NOT LIKE 'transfer_%') THEN (CASE WHEN status = 'Pago Parcial' THEN COALESCE(valor_pago, 0) ELSE valor + COALESCE(juros, 0) - COALESCE(desconto, 0) END) ELSE 0 END) as receitas_realizadas,
                            SUM(CASE WHEN tipo = 'P' AND (documento_vinculado IS NULL OR documento_vinculado NOT LIKE 'transfer_%') THEN (CASE WHEN status = 'Pago Parcial' THEN COALESCE(valor_pago, 0) ELSE valor + COALESCE(juros, 0) - COALESCE(desconto, 0) END) ELSE 0 END) as despesas_realizadas
                        FROM transacoes
                        WHERE
                            status IN ('Pago', 'Pago Parcial') AND
                            data_pagamento IS NOT NULL AND
                            (documento_vinculado IS NULL OR documento_vinculado NOT LIKE 'transfer_%') AND
                            YEAR(data_pagamento) = :ano_realizadas
                        GROUP BY mes
                    ) AS subquery_union
                    GROUP BY mes
                    ORDER BY mes ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':ano_previstas', $ano, PDO::PARAM_INT);
            $stmt->bindValue(':ano_realizadas', $ano, PDO::PARAM_INT);
            $stmt->execute();
            $dbResults = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Reindexa os resultados pelo mês para fácil acesso
            $dataByMonth = [];
            foreach ($dbResults as $row) {
                $dataByMonth[$row['mes']] = $row;
            }

            // Preenche os meses faltantes com zeros para garantir os 12 meses
            $finalResults = [];
            for ($m = 1; $m <= 12; $m++) {
                $monthStr = sprintf('%04d-%02d', $ano, $m);
                if (isset($dataByMonth[$monthStr])) {
                    $finalResults[] = $dataByMonth[$monthStr];
                } else {
                    $finalResults[] = [
                        'mes' => $monthStr,
                        'receitas_previstas' => 0,
                        'despesas_previstas' => 0,
                        'receitas_realizadas' => 0,
                        'despesas_realizadas' => 0
                    ];
                }
            }

            return $finalResults;
        } catch (PDOException $e) {
            error_log("Erro ao buscar balanço mensal: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Busca dados para o DRE (Demonstrativo de Resultado do Exercício).
     * Agrupa por categoria e tipo para um determinado ano (Regime de Competência).
     * @param int $ano
     * @return array
     */
    public function getDadosDRE(int $ano, string $regime = 'competencia', ?int $mes = null): array
    {
        try {
            $dateField = ($regime === 'caixa') ? 't.data_pagamento' : 't.vencimento';
            $statusCondition = "t.status != 'Cancelado'";

            if ($regime === 'caixa') {
                $statusCondition .= " AND t.status IN ('Pago', 'Pago Parcial')";
                $statusCondition .= " AND t.data_pagamento IS NOT NULL AND t.data_pagamento != '0000-00-00'";
            }

            // Define o período atual e o período anterior para comparação
            $periodCondition = "YEAR({$dateField}) = :ano";
            $params = [':ano' => $ano];

            if ($mes) {
                $periodCondition .= " AND MONTH({$dateField}) = :mes";
                $params[':mes'] = $mes;
                
                // Período anterior (Mês anterior)
                $prevMonthDate = date('Y-m-d', strtotime("$ano-$mes-01 -1 month"));
                $prevAno = (int)date('Y', strtotime($prevMonthDate));
                $prevMes = (int)date('m', strtotime($prevMonthDate));
                $prevCondition = "YEAR({$dateField}) = :prev_ano AND MONTH({$dateField}) = :prev_mes";
                $prevParams = [':prev_ano' => $prevAno, ':prev_mes' => $prevMes];
            } else {
                // Período anterior (Ano anterior)
                $prevCondition = "YEAR({$dateField}) = :prev_ano";
                $prevParams = [':prev_ano' => $ano - 1];
            }

            // Melhoria Sênior: Unifica a lógica de identificação de categoria (Classificação, Prestação ou Texto Direto)
            $hasPrestacaoCol = $this->ensurePrestacaoCategoriaColumn();
            $categoriaExpr = "COALESCE(NULLIF(TRIM(tc.nome), ''), " . ($hasPrestacaoCol ? "NULLIF(TRIM(pc.nome), ''), " : "") . "NULLIF(TRIM(t.categoria), ''), 'Sem Categoria')";

            $valorExpr = ($regime === 'caixa')
                ? "SUM(CASE WHEN t.status = 'Pago Parcial' THEN COALESCE(t.valor_pago, 0) ELSE t.valor + COALESCE(t.juros, 0) - COALESCE(t.desconto, 0) END) as total"
                : "SUM(t.valor) as total";

            $baseSql = "
                SELECT $categoriaExpr as categoria, t.tipo, $valorExpr
                FROM transacoes t
                LEFT JOIN transacao_classificacoes tc ON t.classificacao_id = tc.id " .
                ($hasPrestacaoCol ? " LEFT JOIN prestacao_categorias pc ON t.prestacao_categoria_id = pc.id " : "") .
                " WHERE 
                    {$statusCondition} AND
                    %CONDITION% AND
                    (t.documento_vinculado IS NULL OR t.documento_vinculado NOT LIKE 'transfer_%') AND
                    t.tipo IN ('R', 'P')
                GROUP BY 1, t.tipo
            ";

            // Busca Período Atual
            $sqlAtual = str_replace('%CONDITION%', $periodCondition, $baseSql);
            $stmt = $this->db->prepare($sqlAtual);
            foreach ($params as $k => $v) $stmt->bindValue($k, $v);
            $stmt->execute();
            $resultAtual = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Busca Período Anterior
            $sqlPrev = str_replace('%CONDITION%', $prevCondition, $baseSql);
            $stmtPrev = $this->db->prepare($sqlPrev);
            foreach ($prevParams as $k => $v) $stmtPrev->bindValue($k, $v);
            $stmtPrev->execute();
            $resultPrev = $stmtPrev->fetchAll(PDO::FETCH_ASSOC);

            // Mapeia resultados anteriores para facilitar o match
            $mapPrev = [];
            foreach ($resultPrev as $row) {
                $mapPrev[$row['tipo']][$row['categoria']] = (float)$row['total'];
            }

            $dre = [
                'receitas' => [],
                'despesas' => [],
                'total_receitas' => 0.0,
                'total_despesas' => 0.0,
                'resultado' => 0.0
            ];

            foreach ($resultAtual as $row) {
                $valor = (float)$row['total'];
                $valorPrev = $mapPrev[$row['tipo']][$row['categoria']] ?? 0.0;
                
                $item = [
                    'categoria' => $row['categoria'], 
                    'valor' => $valor,
                    'valor_anterior' => $valorPrev,
                    'variacao' => ($valorPrev > 0) ? (($valor - $valorPrev) / $valorPrev) * 100 : ($valor > 0 ? 100 : 0)
                ];

                if ($row['tipo'] === 'R') {
                    $dre['receitas'][] = $item;
                    $dre['total_receitas'] += $valor;
                } elseif ($row['tipo'] === 'P') {
                    $dre['despesas'][] = $item;
                    $dre['total_despesas'] += $valor;
                }
            }

            $dre['resultado'] = $dre['total_receitas'] - $dre['total_despesas'];

            return $dre;
        } catch (PDOException $e) {
            error_log("Erro ao buscar dados do DRE: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Busca prestações de contas aprovadas (Pendente ou Pago) de um projeto específico.
     * Baseia-se na string "Projeto ID: {id}" armazenada nas observações.
     * @param int $projetoId
     * @return array
     */
    public function getPrestacoesPorProjeto(int $projetoId): array
    {
        try {
            // O ponto após o ID é importante para evitar matches parciais (ex: ID 1 vs ID 10)
            // Assumindo formato "Projeto ID: {id}. " conforme salvo no controller
            $term = "Projeto ID: " . $projetoId . ".%";
            $sql = "SELECT 
                        t.id, 
                        t.descricao, 
                        t.valor, 
                        t.vencimento as data, 
                        t.status,
                        t.observacoes,
                        t.documento_vinculado as documentoVinculado,
                        tc.nome as nome_classificacao
                    FROM transacoes t
                    LEFT JOIN transacao_classificacoes tc ON t.classificacao_id = tc.id
                    WHERE t.observacoes LIKE :term 
                    AND t.status IN ('Pendente', 'Pago', 'Pago Parcial')
                    ORDER BY t.vencimento ASC";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':term', $term);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar prestações por projeto: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Busca a lista de categorias de prestação de contas.
     * @return array
     */
    public function getPrestacaoCategorias(): array
    {
        try {
            $stmt = $this->db->query("SELECT id, nome FROM prestacao_categorias ORDER BY nome ASC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar categorias de prestação de contas: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Adiciona uma nova categoria de prestação de contas.
     * @param string $nome
     * @return int|false
     */
    public function addPrestacaoCategoria(string $nome)
    {
        try {
            $stmtCheck = $this->db->prepare("SELECT id FROM prestacao_categorias WHERE nome = ?");
            $stmtCheck->execute([$nome]);
            if ($stmtCheck->fetch()) {
                $this->ultimoErro = "Já existe uma categoria com este nome.";
                return false;
            }
            $stmt = $this->db->prepare("INSERT INTO prestacao_categorias (nome) VALUES (?)");
            $stmt->execute([$nome]);
            return (int)$this->db->lastInsertId();
        } catch (PDOException $e) {
            $this->ultimoErro = "Erro ao adicionar categoria: " . $e->getMessage();
            return false;
        }
    }

    /**
     * Busca as últimas movimentações processadas via Webhook.
     * @param int $limit
     * @return array
     */
    public function getUltimasMovimentacoesWebhook(int $limit): array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM transacoes WHERE webhook_id IS NOT NULL ORDER BY created_at DESC LIMIT :limit");
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar movimentações webhook: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Analisa a pontualidade de um cliente específico no ano atual.
     * @param int $clienteId
     * @return array [is_bom_pagador => bool, score => float]
     */
    public function getStatusPontualidadeCliente(int $clienteId): array
    {
        try {
            $anoAtual = date('Y');
            $hoje = date('Y-m-d');

            $sql = "SELECT 
                        COUNT(CASE WHEN (status IN ('Pago', 'Pago Parcial') AND data_pagamento > vencimento) OR (status IN ('Pendente', 'Atrasado') AND vencimento < :hoje) THEN 1 END) as total_atrasos,
                        COUNT(CASE WHEN status IN ('Pago', 'Pago Parcial') AND data_pagamento < vencimento THEN 1 END) as total_antecipacoes,
                        COUNT(*) as total_transacoes
                    FROM transacoes 
                    WHERE tipo = 'R' AND status != 'Cancelado' AND cliente_id = :cliente_id
                    AND YEAR(vencimento) = :ano";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':hoje' => $hoje, ':cliente_id' => $clienteId, ':ano' => $anoAtual]);
            $res = $stmt->fetch(PDO::FETCH_ASSOC);

            // Critério: Bom Pagador se antecipações >= atrasos e tiver pelo menos 2 transações pagas no histórico
            $isBomPagador = ($res['total_antecipacoes'] >= $res['total_atrasos'] && $res['total_antecipacoes'] > 0);
            
            return ['is_bom_pagador' => $isBomPagador, 'stats' => $res];
        } catch (PDOException $e) {
            error_log("Erro ao verificar pontualidade do cliente: " . $e->getMessage());
            return ['is_bom_pagador' => false];
        }
    }

    /**
     * Atualiza o centro de custo de todas as parcelas futuras de uma recorrência.
     * @param int $id ID da transação atual de referência.
     * @param int|null $novoCentroCustoId Novo ID do centro de custo.
     * @param string $descricaoReferencia Descrição original da parcela sendo editada.
     * @param float|null $novoValor Novo valor para as parcelas futuras (opcional).
     * @return int Quantidade de registros afetados.
     */
    public function atualizarParcelasFuturas(int $id, ?int $novoCentroCustoId, string $descricaoReferencia, ?float $novoValor = null): int
    {
        try {
            $updateFields = [];
            $pattern = '/^(.*?)\s\((?:Recorrência\s)?(\d+)\/(\d+)\)$/';
            
            // Regex para extrair a base da descrição de padrões como "Aluguel (2/12)" ou "Internet (Recorrência 1/24)"
            if (preg_match($pattern, $descricaoReferencia, $matches)) {
                $baseDesc = $matches[1];
                $total = $matches[3];

                // Busca a transação atual para pegar a data de vencimento e o tipo (segurança)
                $stmtRef = $this->db->prepare("SELECT vencimento, tipo FROM transacoes WHERE id = ?");
                $stmtRef->execute([$id]);
                $ref = $stmtRef->fetch(PDO::FETCH_ASSOC);
                
                if (!$ref) {
                    return 0;
                }

                if ($novoCentroCustoId !== null) {
                    $updateFields[] = "centro_custo_id = :cc_id";
                }
                if ($novoValor !== null) {
                    $updateFields[] = "valor = :novo_valor";
                }

                if (empty($updateFields)) {
                    return 0;
                }

                // Atualiza todas as transações que:
                // 1. Têm a mesma base de descrição e mesmo total de parcelas
                // 2. São do mesmo tipo (Receita ou Despesa)
                // 3. Têm vencimento estritamente POSTERIOR ao da transação atual
                // 4. Não estão Pagas nem Canceladas (preservando o histórico realizado)
                $sql = "UPDATE transacoes
                        SET " . implode(', ', $updateFields) . "
                        WHERE tipo = :tipo 
                        AND status NOT IN ('Pago', 'Cancelado')
                        AND vencimento > :vencimento
                        AND (descricao LIKE :pat1 OR descricao LIKE :pat2)";
                
                $stmt = $this->db->prepare($sql);
                $params = [];
                if ($novoCentroCustoId !== null) { $params[':cc_id'] = $novoCentroCustoId; }
                $params[':tipo'] = $ref['tipo'];
                $params[':vencimento'] = $ref['vencimento'];
                $params[':pat1'] = $baseDesc . ' (%/' . $total . ')';
                $params[':pat2'] = $baseDesc . ' (Recorrência %/' . $total . ')';

                if ($novoValor !== null) {
                    $params[':novo_valor'] = $novoValor;
                }
                $stmt->execute($params);
                return $stmt->rowCount();
            }
            return 0;
        } catch (PDOException $e) {
            error_log("Erro ao atualizar parcelas futuras: " . $e->getMessage());
            return 0;
        }
    }
}
