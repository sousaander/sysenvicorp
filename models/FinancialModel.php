<?php

namespace App\Models;

use App\Core\Model;
use PDO;
use PDOException;

class FinancialModel extends Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Busca dados resumidos de fluxo de caixa (ex: últimas 5 transações).
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getFluxoCaixaSummary(array $filtros = [], int $limit = 5, int $offset = 0): array
    {
        // Busca fluxos incluindo transferências (podem ser filtradas externamente)
        $sql = "SELECT id, descricao, valor, tipo, status, vencimento as data, banco_id, documento_vinculado, observacoes
                FROM transacoes
                WHERE 1=1";
        $params = [];
        $orderBy = "created_at DESC, id DESC"; // Ordenação padrão

        // Constrói a query com base nos filtros
        if (!empty($filtros['tipo'])) {
            $sql .= " AND tipo = :tipo";
            $params[':tipo'] = $filtros['tipo'];
        }

        if (!empty($filtros['periodo'])) {
            switch ($filtros['periodo']) {
                case 'dia':
                    if (!empty($filtros['data_unica'])) {
                        $sql .= " AND DATE(vencimento) = :data_unica";
                        $params[':data_unica'] = $filtros['data_unica'];
                        $orderBy = "vencimento ASC, id ASC";
                    }
                    break;
                case 'mes':
                    if (!empty($filtros['mes_ano'])) {
                        $sql .= " AND DATE_FORMAT(vencimento, '%Y-%m') = :mes_ano";
                        $params[':mes_ano'] = $filtros['mes_ano'];
                        $orderBy = "vencimento ASC, id ASC";
                    }
                    break;
                case 'intervalo':
                    if (!empty($filtros['data_inicio']) && !empty($filtros['data_fim'])) {
                        $sql .= " AND vencimento BETWEEN :data_inicio AND :data_fim";
                        $params[':data_inicio'] = $filtros['data_inicio'];
                        $params[':data_fim'] = $filtros['data_fim'];
                        $orderBy = "vencimento ASC, id ASC";
                    }
                    break;
            }
        }

        $sql .= " ORDER BY {$orderBy} LIMIT :limit OFFSET :offset";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            foreach ($params as $key => &$val) {
                $stmt->bindParam($key, $val);
            }
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
    public function getFluxoCaixaCount(array $filtros = []): int
    {
        // Conta transações (inclui transferências por padrão)
        $sql = "SELECT COUNT(*) FROM transacoes WHERE 1=1";
        $params = [];

        // Constrói a query com base nos filtros
        if (!empty($filtros['tipo'])) {
            $sql .= " AND tipo = :tipo";
            $params[':tipo'] = $filtros['tipo'];
        }

        if (!empty($filtros['periodo'])) {
            switch ($filtros['periodo']) {
                case 'dia':
                    if (!empty($filtros['data_unica'])) {
                        $sql .= " AND DATE(vencimento) = :data_unica";
                        $params[':data_unica'] = $filtros['data_unica'];
                    }
                    break;
                case 'mes':
                    if (!empty($filtros['mes_ano'])) {
                        $sql .= " AND DATE_FORMAT(vencimento, '%Y-%m') = :mes_ano";
                        $params[':mes_ano'] = $filtros['mes_ano'];
                    }
                    break;
                case 'intervalo':
                    if (!empty($filtros['data_inicio']) && !empty($filtros['data_fim'])) {
                        $sql .= " AND vencimento BETWEEN :data_inicio AND :data_fim";
                        $params[':data_inicio'] = $filtros['data_inicio'];
                        $params[':data_fim'] = $filtros['data_fim'];
                    }
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
    public function getTransacoesParaRelatorio(array $filtros = []): array
    {
        $sql = "SELECT 
                    t.id, 
                    t.descricao, 
                    t.valor, 
                    t.tipo, 
                    t.status, 
                    t.vencimento as data,
                    t.banco_id,
                    t.documento_vinculado,
                    t.observacoes,
                    b.nome as nome_banco,
                    tc.nome as nome_classificacao
                FROM transacoes t
                LEFT JOIN bancos b ON t.banco_id = b.id
                LEFT JOIN transacao_classificacoes tc ON t.classificacao_id = tc.id
                WHERE 1=1";
        $params = [];
        $orderBy = "t.vencimento ASC, t.created_at ASC";

        // Filtro por banco
        if (!empty($filtros['banco_id'])) {
            $sql .= " AND t.banco_id = :banco_id";
            $params[':banco_id'] = $filtros['banco_id'];
        }

        // Filtro por período
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
            }
        }

        $sql .= " ORDER BY {$orderBy}";

        try {
            $stmt = $this->db->prepare($sql);
            foreach ($params as $key => &$val) {
                $stmt->bindParam($key, $val);
            }
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar transações para relatório: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Busca um resumo mensal de receitas e despesas pagas para o gráfico do dashboard.
     * @param int $months Número de meses a serem considerados (padrão: 6).
     * @return array
     */
    public function getMonthlySummaryForChart(int $months = 6): array
    {
        try {
            // Esta query agrupa as transações por mês/ano e soma os valores de receitas e despesas 'Pagas'.
            $sql = "
                SELECT 
                    DATE_FORMAT(vencimento, '%Y-%m') as mes,
                    SUM(CASE WHEN tipo = 'R' THEN valor ELSE 0 END) as receitas, -- Apenas Receitas
                    SUM(CASE WHEN tipo = 'P' THEN valor ELSE 0 END) as despesas  -- Apenas Despesas
                FROM transacoes
                WHERE 
                    status = 'Pago' AND
                    vencimento >= DATE_SUB(CURDATE(), INTERVAL :months MONTH) AND
                    tipo IN ('R', 'P') -- Ignora 'Transferência'
                GROUP BY mes
                ORDER BY mes ASC;
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':months', $months, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar resumo mensal para gráfico: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Busca um resumo de despesas por categoria para o gráfico do dashboard.
     * Considera apenas as despesas pagas no mês atual.
     * @return array
     */
    public function getExpenseSummaryByCategory(): array
    {
        try {
            // Agrupa as despesas por nome da classificação e soma os valores.
            $sql = "
                SELECT 
                    COALESCE(tc.nome, 'Sem Categoria') as categoria,
                    SUM(t.valor) as total
                FROM transacoes t
                LEFT JOIN transacao_classificacoes tc ON t.classificacao_id = tc.id
                WHERE 
                    t.tipo = 'P' AND 
                    t.status = 'Pago' AND
                    t.vencimento >= DATE_FORMAT(CURDATE(), '%Y-%m-01') AND
                    t.vencimento <= LAST_DAY(CURDATE()) AND
                    t.data_pagamento >= DATE_FORMAT(CURDATE(), '%Y-%m-01') AND
                    t.data_pagamento <= LAST_DAY(CURDATE()) AND
                    t.tipo != 'Transferência' -- Garante que transferências não contem como despesa
                GROUP BY categoria
                ORDER BY total DESC;
            ";
            $stmt = $this->db->prepare($sql);
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
    public function getContasPagarMes()
    {
        try {
            // Ignora transferências
            $stmt = $this->db->prepare(
                "SELECT SUM(valor) as total FROM transacoes 
                 WHERE tipo = 'P' AND status IN ('Pendente', 'Atrasado') AND vencimento >= CURDATE() AND vencimento <= LAST_DAY(CURDATE())"
            );
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
    public function getContasReceberMes()
    {
        try {
            // Ignora transferências
            $stmt = $this->db->prepare(
                "SELECT SUM(valor) as total FROM transacoes 
                 WHERE tipo = 'R' AND status IN ('Pendente', 'Atrasado') AND vencimento >= CURDATE() AND vencimento <= LAST_DAY(CURDATE())"
            );
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'] ?? 0.0;
        } catch (PDOException $e) {
            error_log("Erro ao buscar contas a receber: " . $e->getMessage());
            return 0.0;
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
                    (SELECT COALESCE(SUM(saldo_inicial), 0) FROM bancos) +
                    (SELECT COALESCE(SUM(valor), 0) FROM transacoes WHERE tipo = 'R' AND status = 'Pago' AND banco_id IS NOT NULL) -
                    (SELECT COALESCE(SUM(valor), 0) FROM transacoes WHERE tipo = 'P' AND status = 'Pago' AND banco_id IS NOT NULL)
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
            $stmt = $this->db->query("SELECT id, nome FROM bancos ORDER BY nome ASC");
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
                    b.id,
                    b.nome,
                    b.tipo,
                    (b.saldo_inicial 
                        + COALESCE((SELECT SUM(t.valor) FROM transacoes t WHERE t.banco_id = b.id AND t.tipo = 'R' AND t.status = 'Pago'), 0)
                        - COALESCE((SELECT SUM(t.valor) FROM transacoes t WHERE t.banco_id = b.id AND t.tipo = 'P' AND t.status = 'Pago'), 0)
                        + COALESCE((SELECT SUM(t.valor) FROM transacoes t WHERE t.banco_id = b.id AND t.documento_vinculado LIKE 'transfer_in:%' AND t.status = 'Pago'), 0)
                        - COALESCE((SELECT SUM(t.valor) FROM transacoes t WHERE t.banco_id = b.id AND t.documento_vinculado LIKE 'transfer_out:%' AND t.status = 'Pago'), 0)
                    ) as saldo_atual
                FROM bancos b
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
     * Adiciona uma nova categoria e retorna seu ID.
     * @param string $nome
     * @param string|null $tipo
     * @return int|false
     */
    public function addClassificacao(string $nome, ?string $tipo)
    {
        try {
            $stmt = $this->db->prepare("INSERT INTO transacao_classificacoes (nome, tipo) VALUES (?, ?)");
            $stmt->execute([$nome, $tipo]);
            return (int)$this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Erro ao adicionar categoria: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Adiciona um novo centro de custo e retorna seu ID.
     * @param string $nome
     * @return int|false
     */
    public function addCentroCusto(string $nome)
    {
        try {
            $stmt = $this->db->prepare("INSERT INTO centros_custo (nome) VALUES (?)");
            $stmt->execute([$nome]);
            return (int)$this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Erro ao adicionar centro de custo: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Busca um centro de custo pelo ID.
     */
    public function getCentroCustoById(int $id): ?array
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
     * Salva (insere ou atualiza) um centro de custo.
     */
    public function salvarCentroCusto(array $dados): bool
    {
        $sql = $dados['id']
            ? "UPDATE centros_custo SET nome = :nome WHERE id = :id"
            : "INSERT INTO centros_custo (nome) VALUES (:nome)";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':nome', $dados['nome']);
            if ($dados['id']) {
                $stmt->bindValue(':id', $dados['id'], PDO::PARAM_INT);
            }
            return $stmt->execute();
        } catch (PDOException $e) {
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
    public function getClassificacaoById(int $id): ?array
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
     * Salva (insere ou atualiza) uma categoria.
     * @param array $dados
     * @return bool
     */
    public function salvarClassificacao(array $dados): bool
    {
        $sql = $dados['id']
            ? "UPDATE transacao_classificacoes SET nome = :nome, tipo = :tipo WHERE id = :id"
            : "INSERT INTO transacao_classificacoes (nome, tipo) VALUES (:nome, :tipo)";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':nome', $dados['nome']);
            $stmt->bindValue(':tipo', $dados['tipo'] ?: null);
            if ($dados['id']) {
                $stmt->bindValue(':id', $dados['id'], PDO::PARAM_INT);
            }
            return $stmt->execute();
        } catch (PDOException $e) {
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
        $stmt = $this->db->prepare("DELETE FROM transacao_classificacoes WHERE id = ?");
        return $stmt->execute([$id]);
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
    public function getTransacaoById(int $id): ?array
    {
        try {
            // Adicionados: juros, desconto, forma_pagamento, data_pagamento
            $stmt = $this->db->prepare("SELECT id, tipo, descricao, valor, vencimento, data_emissao as dataEmissao, status, documento_vinculado as documentoVinculado, observacoes, banco_id, classificacao_id, centro_custo_id, juros, desconto, forma_pagamento, data_pagamento FROM transacoes WHERE id = ?");
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
    public function findTransferPartnerIdByDocument(string $documento): ?int
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
                        vencimento = :vencimento, 
                        data_pagamento = :data_pagamento,
                        data_emissao = :dataEmissao, 
                        status = :status, 
                        documento_vinculado = :documentoVinculado, 
                        observacoes = :observacoes, 
                        banco_id = :banco_id,
                        classificacao_id = :classificacao_id,
                        centro_custo_id = :centro_custo_id,
                        contrato_parcela_id = :contrato_parcela_id,
                        juros = :juros,
                        desconto = :desconto,
                        forma_pagamento = :forma_pagamento
                    WHERE id = :id";
        } else {
            // Lógica de INSERT com os novos campos
            $sql = "INSERT INTO transacoes (tipo, descricao, valor, vencimento, data_pagamento, data_emissao, status, documento_vinculado, observacoes, banco_id, classificacao_id, centro_custo_id, contrato_parcela_id, juros, desconto, forma_pagamento, created_at) 
                    VALUES (:tipo, :descricao, :valor, :vencimento, :data_pagamento, :dataEmissao, :status, :documentoVinculado, :observacoes, :banco_id, :classificacao_id, :centro_custo_id, :contrato_parcela_id, :juros, :desconto, :forma_pagamento, NOW())";
        }

        try {
            $stmt = $this->db->prepare($sql);

            $stmt->bindValue(':tipo', $dados['tipo']);
            $stmt->bindValue(':descricao', $dados['descricao']);
            $stmt->bindValue(':valor', $dados['valor']);
            $stmt->bindValue(':vencimento', $dados['vencimento']);
            $stmt->bindValue(':data_pagamento', $dados['data_pagamento'] ?: null, PDO::PARAM_STR);
            $stmt->bindValue(':dataEmissao', $dados['dataEmissao'] ?: null, PDO::PARAM_STR);
            $stmt->bindValue(':status', $dados['status']);
            $stmt->bindValue(':documentoVinculado', $dados['documentoVinculado'] ?: null, PDO::PARAM_STR);
            $stmt->bindValue(':observacoes', $dados['observacoes'] ?: null, PDO::PARAM_STR);
            $stmt->bindValue(':banco_id', $dados['banco_id'] ?: null, PDO::PARAM_INT);
            $stmt->bindValue(':classificacao_id', $dados['classificacao_id'] ?: null, PDO::PARAM_INT);
            $stmt->bindValue(':centro_custo_id', $dados['centro_custo_id'] ?: null, PDO::PARAM_INT);
            $stmt->bindValue(':contrato_parcela_id', $dados['contrato_parcela_id'] ?: null, PDO::PARAM_INT);
            
            // Novos campos vinculados
            $stmt->bindValue(':juros', $dados['juros'] ?: 0);
            $stmt->bindValue(':desconto', $dados['desconto'] ?: 0);
            $stmt->bindValue(':forma_pagamento', $dados['forma_pagamento'] ?: null, PDO::PARAM_STR);

            if ($id) $stmt->bindValue(':id', $id, PDO::PARAM_INT);

            if ($stmt->execute()) {
                return $id ?: (int)$this->db->lastInsertId();
            }
            return false;
        } catch (PDOException $e) {
            error_log("Erro ao salvar transação: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Busca uma lista de transações, com opção de filtro por tipo.
     * @param string|null $tipo 'P' para despesas, 'R' para receitas.
     * @param string $orderBy Coluna para ordenação.
     * @param string $orderDir Direção da ordenação (ASC ou DESC).
     * @param int|null $limit
     * @param int|null $offset
     * @return array
     */
    public function getTransacoes(?string $tipo = null, string $orderBy = 'vencimento', string $orderDir = 'DESC', ?int $limit = null, ?int $offset = null): array
    {
        try {
            $sql = "SELECT t.*, b.nome as nome_banco, tc.nome as nome_classificacao, cc.nome as nome_centro_custo
                    FROM transacoes t
                    LEFT JOIN bancos b ON t.banco_id = b.id
                    LEFT JOIN centros_custo cc ON t.centro_custo_id = cc.id
                    LEFT JOIN transacao_classificacoes tc ON t.classificacao_id = tc.id";
            $params = [];
            if ($tipo) {
                $sql .= " WHERE t.tipo = :tipo";
                $params[':tipo'] = $tipo;
            }
            $sql .= " ORDER BY {$orderBy} {$orderDir}"; // A ordenação deve ser segura e não vir de input direto do usuário

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
     * @return int
     */
    public function getTransacoesCount(?string $tipo = null): int
    {
        try {
            $sql = "SELECT COUNT(*) FROM transacoes";
            $params = [];
            if ($tipo) {
                $sql .= " WHERE tipo = :tipo";
                $params[':tipo'] = $tipo;
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
                 AND status IN ('Pendente', 'Atrasado') 
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
    public function getContasReceberAtrasadasCount(): int
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT COUNT(*) 
                 FROM transacoes 
                 WHERE tipo = 'R' 
                 AND status = 'Atrasado'"
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
                 WHERE status = 'Pago' AND data_pagamento IS NOT NULL"
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
     * Retorna um resumo (contagem e valor total) das contas a receber que estão atrasadas.
     * Uma conta é considerada atrasada se seu status for 'Pendente' e a data de vencimento for anterior à data atual.
     *
     * @return array Um array associativo com as chaves 'count' e 'valor'.
     */
    public function getContasReceberAtrasadasSummary(): array
    {
        $query = "
            SELECT 
                COUNT(id) as count,
                COALESCE(SUM(valor), 0) as valor
            FROM transacoes 
            WHERE tipo = 'R' 
              AND status = 'Pendente' 
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
     * @return array
     */
    public function getTransacoesByPessoaId(int $pessoaId, string $tipo): array
    {
        // CORREÇÃO: A query agora busca transações diretamente pelo pessoa_id,
        // tornando-a mais abrangente e não dependente de um contrato.
        $sql = "SELECT 
                    t.id,
                    t.descricao,
                    t.valor,
                    t.vencimento,
                    t.status
                FROM transacoes t
                WHERE t.pessoa_id = :pessoa_id AND t.tipo = :tipo
                ORDER BY t.vencimento DESC
                LIMIT 10"; // Limita a 10 para a tela de detalhes

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':pessoa_id' => $pessoaId, ':tipo' => $tipo]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Erro ao buscar transações por pessoa_id: " . $e->getMessage());
            return [];
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
        $contaOrigem = $this->getBancoById($contaOrigemId);
        $contaDestino = $this->getBancoById($contaDestinoId);

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
    public function getBancoById(int $id)
    {
        $stmt = $this->db->prepare("SELECT * FROM bancos WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
}
