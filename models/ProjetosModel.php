<?php

namespace App\Models;

// Adiciona o 'use' para a classe Model base
use App\Core\Model;
// A classe PDO já está no escopo global, não precisa de 'use'.

class ProjetosModel extends Model
{
    private $lastError = null;

    public function __construct()
    {
        parent::__construct();
        $this->ensureColumnsExist();
    }

    public function getLastError()
    {
        return $this->lastError;
    }

    /**
     * Garante que as colunas de latitude e longitude existam na tabela projetos.
     * Isso evita o erro 1054 quando o banco de dados não foi atualizado manualmente.
     */
    private function ensureColumnsExist()
    {
        $columnsToCheck = [
            'latitude' => 'DECIMAL(10,8) NULL',
            'longitude' => 'DECIMAL(11,8) NULL',
            'numero_projeto' => 'VARCHAR(20) UNIQUE NULL AFTER id'
        ];

        foreach ($columnsToCheck as $col => $def) {
            try {
                $stmt = $this->db->query("SHOW COLUMNS FROM projetos LIKE '$col'");
                if ($stmt->rowCount() == 0) {
                    // Adiciona as colunas ao final da tabela
                    $this->db->exec("ALTER TABLE projetos ADD COLUMN $col $def");
                }
            } catch (\PDOException $e) {
                error_log("Erro ao sincronizar schema da tabela projetos ($col): " . $e->getMessage());
            }
        }
    }

    /**
     * Retorna o próximo número sequencial de projeto para o ano atual.
     * Formato: PRJ-YYYY-NNN (ex: PRJ-2026-001)
     */
    public function getNextProjectNumber(): string
    {
        $year = date('Y');
        $prefix = "PRJ-{$year}-";

        try {
            // O prefixo 'PRJ-YYYY-' tem 9 caracteres, o número sequencial começa na posição 10.
            // Usamos CAST para garantir a ordenação numérica correta.
            $stmt = $this->db->prepare("
                SELECT MAX(CAST(SUBSTRING(numero_projeto, 10) AS UNSIGNED)) 
                FROM projetos 
                WHERE numero_projeto LIKE :prefix
            ");
            $stmt->execute([':prefix' => $prefix . '%']);
            $lastNumber = (int)$stmt->fetchColumn();
            
            return $prefix . str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        } catch (\PDOException $e) {
            error_log("Erro ao gerar próximo número de projeto: " . $e->getMessage());
            return $prefix . "001";
        }
    }

    /**
     * Verifica se um número de projeto já existe no banco de dados.
     * @param string $numero
     * @param int|null $excludeId ID a ser ignorado (útil na edição)
     * @return bool
     */
    public function numeroProjetoExiste(string $numero, ?int $excludeId = null): bool
    {
        try {
            $sql = "SELECT COUNT(*) FROM projetos WHERE numero_projeto = :numero";
            if ($excludeId) {
                $sql .= " AND id != :excludeId";
            }
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':numero', $numero);
            if ($excludeId) {
                $stmt->bindValue(':excludeId', $excludeId, \PDO::PARAM_INT);
            }
            $stmt->execute();
            return (int)$stmt->fetchColumn() > 0;
        } catch (\PDOException $e) {
            error_log("Erro ao verificar existência do número de projeto: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Busca dados resumidos sobre a carteira de projetos.
     */
    public function getProjetosSummary()
    {
        try {
            // Considera como "ativos" todos os projetos que não estão Concluídos nem Cancelados
            $totalEmAndamento = $this->db->query("SELECT COUNT(*) FROM projetos WHERE status NOT IN ('Concluído', 'Cancelado')")->fetchColumn();

            // Projetos atrasados: data final prevista no passado e não concluídos/cancelados
            $projetosAtrasados = $this->db->query("SELECT COUNT(*) FROM projetos WHERE data_fim_prevista < CURDATE() AND status NOT IN ('Concluído', 'Cancelado')")->fetchColumn();

            // Marcos a vencer nos próximos 7 dias (inclui também projetos não concluídos)
            $proximoMarcoVencer = $this->db->query("SELECT COUNT(*) FROM projetos WHERE data_fim_prevista BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY) AND status NOT IN ('Concluído', 'Cancelado')")->fetchColumn();

            // Faturamento previsto do mês: soma do campo orcamento para projetos ativos
            $faturamentoPrevistoMes = $this->db->query("SELECT SUM(orcamento) FROM projetos WHERE status NOT IN ('Concluído', 'Cancelado')")->fetchColumn();

            return [
                'totalEmAndamento' => (int) $totalEmAndamento,
                'projetosAtrasados' => (int) $projetosAtrasados,
                'faturamentoPrevistoMes' => 'R$ ' . number_format($faturamentoPrevistoMes ?? 0, 2, ',', '.'),
                'proximoMarcoVencer' => (int) $proximoMarcoVencer,
            ];
        } catch (\PDOException $e) {
            error_log("Erro ao buscar resumo de projetos: " . $e->getMessage());
            return ['totalEmAndamento' => 0, 'projetosAtrasados' => 0, 'faturamentoPrevistoMes' => 'R$ 0,00', 'proximoMarcoVencer' => 0];
        }
    }

    /**
     * Busca uma lista de projetos, com suporte para filtros e paginação.
     * @param array $filtros Filtros de busca (para uso futuro)
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getProjetos(array $filtros = [], int $limit = 5, int $offset = 0): array
    {
        try {
            // Monta a query base
            $sql = "SELECT p.id, p.numero_projeto, p.nome, c.nome as cliente_nome, p.responsavel, p.data_inicial, p.data_fim_prevista, p.status, p.tipo_servico,
                           (SELECT COUNT(*) FROM projetos_tarefas WHERE projeto_id = p.id) as total_tarefas,
                           (SELECT COUNT(*) FROM projetos_tarefas WHERE projeto_id = p.id AND status = 'Concluída') as tarefas_concluidas
                    FROM projetos p
                    LEFT JOIN clientes c ON p.cliente_id = c.id";

            $where = [];
            $params = [];

            // Se foi passado um filtro de status, aplica-o
            if (!empty($filtros['status']) && !in_array($filtros['status'], ['Todos', 'Todos Ativos'])) {
                $where[] = "p.status = :status";
                $params[':status'] = $filtros['status'];
            } elseif (empty($filtros['status']) || $filtros['status'] === 'Todos Ativos') {
                // Comportamento padrão ou quando "Todos Ativos" é selecionado:
                // Mostra todos os projetos, exceto os arquivados.
                $where[] = "p.status NOT IN ('Concluído', 'Cancelado')";
            }
            // Se for 'Todos', não adiciona filtro de status (mostra tudo)

            if (!empty($filtros['responsavel'])) {
                $where[] = "p.responsavel LIKE :responsavel";
                $params[':responsavel'] = '%' . $filtros['responsavel'] . '%';
            }

            if (!empty($where)) {
                $sql .= ' WHERE ' . implode(' AND ', $where);
            }

            // Lógica de Ordenação
            $allowedSorts = ['id', 'numero_projeto', 'nome', 'cliente_nome', 'responsavel', 'data_inicial', 'data_fim_prevista', 'status'];
            $orderBy = in_array($filtros['orderBy'] ?? '', $allowedSorts) ? $filtros['orderBy'] : 'id';
            $orderDir = strtoupper($filtros['orderDir'] ?? '') === 'ASC' ? 'ASC' : 'DESC';

            $sortMap = [
                'id' => 'p.id',
                'numero_projeto' => 'p.numero_projeto',
                'nome' => 'p.nome',
                'cliente_nome' => 'c.nome',
                'responsavel' => 'p.responsavel',
                'data_inicial' => 'p.data_inicial',
                'data_fim_prevista' => 'p.data_fim_prevista',
                'status' => 'p.status'
            ];
            $orderBySql = $sortMap[$orderBy] ?? 'p.id';

            $sql .= " ORDER BY {$orderBySql} {$orderDir} LIMIT :limit OFFSET :offset";

            $stmt = $this->db->prepare($sql);
            foreach ($params as $k => $v) {
                $stmt->bindValue($k, $v);
            }
            $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Erro ao buscar lista de projetos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Conta o número total de projetos que correspondem a um filtro.
     * @param array $filtros
     * @return int
     */
    public function getProjetosCount(array $filtros = []): int
    {
        $sql = "SELECT COUNT(*) FROM projetos p";
        $where = [];
        $params = [];

        // A lógica de filtro deve ser idêntica à de getProjetos()
        if (!empty($filtros['status']) && !in_array($filtros['status'], ['Todos', 'Todos Ativos'])) {
            $where[] = "p.status = :status";
            $params[':status'] = $filtros['status'];
        } elseif (empty($filtros['status']) || $filtros['status'] === 'Todos Ativos') {
            // Comportamento padrão ou quando "Todos Ativos" é selecionado.
            $where[] = "p.status NOT IN ('Concluído', 'Cancelado')";
        }

        if (!empty($filtros['responsavel'])) {
            $where[] = "p.responsavel LIKE :responsavel";
            $params[':responsavel'] = '%' . $filtros['responsavel'] . '%';
        }

        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Busca um projeto específico pelo ID.
     * @param int $id O ID do projeto.
     * @return array|null
     */
    public function getProjetoById(int $id): ?array
    {
        try {
            $sql = "SELECT p.*, c.nome as cliente_nome, c.sigla as cliente_sigla 
                    FROM projetos p
                    LEFT JOIN clientes c ON p.cliente_id = c.id
                    WHERE p.id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (\PDOException $e) {
            error_log("Erro ao buscar projeto por ID: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Busca todos os projetos para uso em um dropdown/select.
     * Retorna apenas projetos ativos (não concluídos nem cancelados).
     * @return array
     */
    public function getAllProjetosParaSelect(): array
    {
        try {
            $sql = "SELECT p.id, p.nome, c.nome as cliente_nome, c.sigla as cliente_sigla, p.tipo_servico as tipo_servico, p.responsavel as responsavel
                    FROM projetos p
                    LEFT JOIN clientes c ON p.cliente_id = c.id
                    WHERE p.status NOT IN ('Concluído', 'Cancelado')
                    ORDER BY p.nome ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $resultado = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            return $resultado;
        } catch (\PDOException $e) {
            error_log("Erro ao buscar projetos para select: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Busca projetos associados a um cliente específico.
     * @param int $clienteId
     * @return array
     */
    public function getProjetosByClienteId(int $clienteId): array
    {
        try {
            $sql = "SELECT id, nome, status FROM projetos WHERE cliente_id = :cliente_id ORDER BY id DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':cliente_id', $clienteId, \PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Erro ao buscar projetos do cliente: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Busca projetos que possuem coordenadas geográficas preenchidas.
     * Usado para plotagem no mapa do Dashboard.
     * @return array
     */
    public function getProjetosComLocalizacao(): array
    {
        try {
            $sql = "
                SELECT 
                    p.id, p.nome, p.latitude, p.longitude, p.status, p.tipo_servico, 
                    c.nome as cliente_nome, 'projeto' as item_tipo, p.dataCriacao as created_at
                FROM projetos p
                LEFT JOIN clientes c ON p.cliente_id = c.id
                WHERE p.latitude IS NOT NULL 
                  AND p.longitude IS NOT NULL 
                  AND p.latitude != 0
                  AND p.status != 'Cancelado'
                
                UNION ALL
                
                SELECT 
                    op.id, op.nome_proposta as nome, op.latitude, op.longitude, op.status, 
                    'Comercial' as tipo_servico, 
                    COALESCE(c.nome, 'Prospect / Cliente não identificado') as cliente_nome, 
                    'proposta' as item_tipo, op.created_at
                FROM orcamento_proposta op
                LEFT JOIN clientes c ON op.cliente_id = c.id
                WHERE op.latitude IS NOT NULL 
                  AND op.longitude IS NOT NULL 
                  AND op.latitude != 0
                  AND op.status != 'Cancelada'
                  AND op.projeto_id IS NULL
            ";
            
            $stmt = $this->db->query($sql);
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Erro ao buscar projetos com localização: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Retorna a contagem de projetos agrupada por status para o Dashboard.
     */
    public function getProjetosCountByStatus(): array
    {
        try {
            $sql = "SELECT status, COUNT(*) as total 
                    FROM projetos 
                    WHERE status != 'Cancelado'
                    GROUP BY status";
            $stmt = $this->db->query($sql);
            $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            $summary = [];
            foreach ($results as $row) {
                $summary[$row['status']] = (int) $row['total'];
            }
            return $summary;
        } catch (\PDOException $e) {
            error_log("Erro ao buscar contagem de projetos por status: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Busca todos os orçamentos de um projeto específico.
     * @param int $projeto_id
     * @return array
     */
    public function getOrcamentosParaSelect(int $projeto_id): array
    {
        try {
            $sql = "SELECT id, descricao, valor_previsto
                    FROM projetos_orcamento
                    WHERE projeto_id = :projeto_id
                    ORDER BY descricao ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['projeto_id' => $projeto_id]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Erro ao buscar orçamentos do projeto: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Busca dados detalhados para o resumo de um projeto específico.
     * @param int $id O ID do projeto.
     * @return array|null
     */
    public function getProjectDetailsSummary(int $id): ?array
    {
        try {
            // Busca dados básicos do projeto para cálculos
            $stmt = $this->db->prepare("SELECT orcamento, data_fim_prevista, status FROM projetos WHERE id = ?");
            $stmt->execute([$id]);
            $projeto = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$projeto) {
                return [];
            }

            // Resumo Financeiro (reutilizando a lógica existente)
            $orcamentoSummary = $this->getOrcamentoSummary($id);

            // Contagem de ARTs
            $stmtArt = $this->db->prepare("SELECT COUNT(*) FROM projetos_art WHERE projeto_id = ?");
            $stmtArt->execute([$id]);
            $artCount = $stmtArt->fetchColumn();

            // ATUALIZAÇÃO: A contagem de documentos agora soma CDT e Arquivos Gerais
            $stmtCdt = $this->db->prepare("SELECT COUNT(*) FROM projetos_cdt WHERE projeto_id = ?");
            $stmtCdt->execute([$id]);
            $stmtArquivos = $this->db->prepare("SELECT COUNT(*) FROM projetos_arquivos WHERE projeto_id = ?");
            $stmtArquivos->execute([$id]);
            $docsCount = $stmtCdt->fetchColumn() + $stmtArquivos->fetchColumn();

            $stmtMapas = $this->db->prepare("SELECT COUNT(*) FROM projetos_mapas WHERE projeto_id = ?");
            $stmtMapas->execute([$id]);
            $mapasCount = $stmtMapas->fetchColumn();

            // --- Novos Cálculos para o Dashboard ---

            // 1. Dias Restantes
            $diasRestantes = 'N/A';
            if (!empty($projeto['data_fim_prevista'])) {
                $dataFim = new \DateTime($projeto['data_fim_prevista']);
                $hoje = new \DateTime();
                $hoje->setTime(0, 0, 0);
                $dataFim->setTime(0, 0, 0);

                if ($projeto['status'] === 'Concluído') {
                    $diasRestantes = 'Concluído';
                } else {
                    $intervalo = $hoje->diff($dataFim);
                    // Se dataFim < hoje, inverte o sinal para mostrar negativo (atrasado)
                    $dias = (int)$intervalo->format('%r%a');
                    $diasRestantes = $dias;
                }
            }

            // 2. Orçamento Gasto %
            $orcamentoTotal = (float)($projeto['orcamento'] ?? 0);
            $gastoReal = (float)($orcamentoSummary['despesa_real'] ?? 0);
            $orcamentoGastoPercent = 0;
            if ($orcamentoTotal > 0) {
                $orcamentoGastoPercent = round(($gastoReal / $orcamentoTotal) * 100, 1);
            }

            // 3. Faturamento Realizado
            $faturamentoRealizado = (float)($orcamentoSummary['receita_real'] ?? 0);

            return [
                'orcamento' => $orcamentoSummary,
                'art_count' => (int) $artCount,
                'documentos_count' => (int) $docsCount,
                'mapas_count' => (int) $mapasCount,
                'dias_restantes' => $diasRestantes,
                'orcamento_gasto_percent' => $orcamentoGastoPercent,
                'faturamento_realizado' => 'R$ ' . number_format($faturamentoRealizado, 2, ',', '.'),
                'progresso_calculado' => 0 // Será sobrescrito pelo Controller com base nas tarefas
            ];
        } catch (\PDOException $e) {
            error_log("Erro ao buscar detalhes do resumo do projeto: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Adiciona um novo evento à linha do tempo do projeto.
     * @param int $projeto_id
     * @param string $evento
     * @param string $descricao
     * @param int|null $usuario_id
     * @return bool
     */
    public function addTimelineEvent(int $projeto_id, string $evento, string $descricao, ?int $usuario_id = null): bool
    {
        // Se o usuário não for passado, tenta pegar da sessão
        if ($usuario_id === null && isset($_SESSION['user_id'])) {
            $usuario_id = $_SESSION['user_id'];
        }

        try {
            $sql = "INSERT INTO projetos_timeline (projeto_id, usuario_id, evento, descricao) VALUES (?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$projeto_id, $usuario_id, $evento, $descricao]);
        } catch (\PDOException $e) {
            error_log("Erro ao adicionar evento na timeline: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Busca a linha do tempo de um projeto, juntando o nome do usuário.
     * @param int $projeto_id
     * @return array
     */
    public function getTimelineByProjectId(int $projeto_id): array
    {
        try {
            $sql = "SELECT t.*, u.nome as usuario_nome 
                    FROM projetos_timeline t
                    LEFT JOIN usuarios u ON t.usuario_id = u.id
                    WHERE t.projeto_id = ? 
                    ORDER BY t.data_evento DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$projeto_id]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Erro ao buscar timeline do projeto: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Salva um novo projeto ou atualiza um existente no banco de dados.
     *
     * @param array $dados Os dados do projeto vindos do formulário.
     * @return bool Retorna true em caso de sucesso, false em caso de falha.
     */
    public function salvarProjeto(array $dados): bool
    {
        $maxRetries = 3;
        $attempt = 0;

        while ($attempt < $maxRetries) {
            try {
                // Busca o estado atual do projeto antes de salvar (para detectar mudanças)
                $projetoAtual = !empty($dados['id']) ? $this->getProjetoById((int)$dados['id']) : null;

                // Sanitiza e prepara os dados
                $id = !empty($dados['id']) ? (int) $dados['id'] : null;
                
                // Se for novo projeto e não veio número, gera um. 
                // Se houver colisão de concorrência, o catch 1062 incrementará o attempt e gerará um novo número na próxima volta.
                $numero_projeto = !empty($dados['numero_projeto']) ? trim($dados['numero_projeto']) : ($id ? ($projetoAtual['numero_projeto'] ?? '') : $this->getNextProjectNumber());
                
                $nome = trim($dados['nome'] ?? '');
                $tipo_servico = trim($dados['tipo_servico'] ?? '');
                $cliente_id = !empty($dados['cliente_id']) ? (int) $dados['cliente_id'] : null;
                $empreendimento = trim($dados['empreendimento'] ?? '');
                $data_inicial = !empty($dados['data_inicial']) ? $dados['data_inicial'] : null;
                $data_fim_prevista = !empty($dados['data_fim_prevista']) ? $dados['data_fim_prevista'] : null;
                $orcamento = !empty($dados['orcamento']) ? (float) $dados['orcamento'] : null;
                $orcamento_id = trim($dados['orcamento_id'] ?? '');
                $area_id = trim($dados['area_id'] ?? '');
                $tamanho_ha = !empty($dados['tamanho_ha']) ? (float) $dados['tamanho_ha'] : null;
                $produto_entregue = trim($dados['produto_entregue'] ?? '');
                $responsavel_elaboracao = trim($dados['responsavel_elaboracao'] ?? '');
                $responsavel = trim($dados['responsavel'] ?? ''); // Responsável Técnico
                $responsavel_execucao = trim($dados['responsavel_execucao'] ?? '');
                $status = $dados['status'] ?? 'Planejado';
                $observacoes = trim($dados['observacoes'] ?? '');
                $latitude = !empty($dados['latitude']) ? (float)$dados['latitude'] : null;
                $longitude = !empty($dados['longitude']) ? (float)$dados['longitude'] : null;

                if ($id) {
                    $sql = "UPDATE projetos 
                            SET numero_projeto = :numero_projeto, nome = :nome, tipo_servico = :tipo_servico, cliente_id = :cliente_id, empreendimento = :empreendimento,
                                data_inicial = :data_inicial, data_fim_prevista = :data_fim_prevista, orcamento = :orcamento, orcamento_id = :orcamento_id,
                                area_id = :area_id, tamanho_ha = :tamanho_ha, produto_entregue = :produto_entregue,
                                responsavel_elaboracao = :responsavel_elaboracao, responsavel = :responsavel, responsavel_execucao = :responsavel_execucao,
                                status = :status, observacoes = :observacoes, latitude = :latitude, longitude = :longitude
                            WHERE id = :id";
                    $stmt = $this->db->prepare($sql);
                    $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
                } else {
                    $sql = "INSERT INTO projetos (
                                numero_projeto, nome, tipo_servico, cliente_id, empreendimento, data_inicial, data_fim_prevista,
                                orcamento, orcamento_id, area_id, tamanho_ha, produto_entregue,
                                responsavel_elaboracao, responsavel, responsavel_execucao,
                                status, observacoes, latitude, longitude, dataCriacao
                            ) VALUES (
                                :numero_projeto, :nome, :tipo_servico, :cliente_id, :empreendimento, :data_inicial, :data_fim_prevista,
                                :orcamento, :orcamento_id, :area_id, :tamanho_ha, :produto_entregue,
                                :responsavel_elaboracao, :responsavel, :responsavel_execucao,
                                :status, :observacoes, :latitude, :longitude, NOW()
                            )";
                    $stmt = $this->db->prepare($sql);
                }

                $stmt->bindParam(':numero_projeto', $numero_projeto);
                $stmt->bindParam(':nome', $nome);
                $stmt->bindParam(':tipo_servico', $tipo_servico);
                $stmt->bindValue(':cliente_id', $cliente_id, $cliente_id ? \PDO::PARAM_INT : \PDO::PARAM_NULL);
                $stmt->bindParam(':empreendimento', $empreendimento);
                $stmt->bindValue(':data_inicial', $data_inicial, $data_inicial ? \PDO::PARAM_STR : \PDO::PARAM_NULL);
                $stmt->bindValue(':data_fim_prevista', $data_fim_prevista, $data_fim_prevista ? \PDO::PARAM_STR : \PDO::PARAM_NULL);
                $stmt->bindValue(':orcamento', $orcamento, $orcamento ? \PDO::PARAM_STR : \PDO::PARAM_NULL);
                $stmt->bindParam(':orcamento_id', $orcamento_id);
                $stmt->bindParam(':area_id', $area_id);
                $stmt->bindValue(':tamanho_ha', $tamanho_ha, $tamanho_ha ? \PDO::PARAM_STR : \PDO::PARAM_NULL);
                $stmt->bindParam(':produto_entregue', $produto_entregue);
                $stmt->bindParam(':responsavel_elaboracao', $responsavel_elaboracao);
                $stmt->bindParam(':responsavel', $responsavel);
                $stmt->bindParam(':responsavel_execucao', $responsavel_execucao);
                $stmt->bindParam(':status', $status);
                $stmt->bindParam(':observacoes', $observacoes);
                $stmt->bindValue(':latitude', $latitude);
                $stmt->bindValue(':longitude', $longitude);

                $success = $stmt->execute();

                if ($success) {
                    $projeto_id = $id ?: $this->db->lastInsertId();
                    if (!$id) {
                        $this->addTimelineEvent($projeto_id, 'PROJETO_CRIADO', "Projeto '{$nome}' foi criado.");
                    } else if ($projetoAtual && $projetoAtual['status'] !== $status) {
                        $descricao = "Status do projeto alterado de '{$projetoAtual['status']}' para '{$status}'.";
                        $this->addTimelineEvent($projeto_id, 'STATUS_ALTERADO', $descricao);

                        // Sincronização Sênior: Se o projeto foi cancelado via formulário, cancela a proposta vinculada
                        if ($status === 'Cancelado' || $status === 'Cancelado') {
                            $this->db->prepare("UPDATE orcamento_proposta SET status = 'Cancelada' WHERE projeto_id = ? AND status = 'Aprovada'")
                                     ->execute([$projeto_id]);

                            // Suspende financeiro: Transações pendentes vinculadas ao projeto são canceladas
                            $this->db->prepare("UPDATE transacoes SET status = 'Cancelado' WHERE observacoes LIKE ? AND status IN ('Pendente', 'Atrasado')")
                                     ->execute([$projeto_id]);
                        }
                    }
                    return (int)$projeto_id;
                }

                return false;

            } catch (\PDOException $e) {
                // Se o erro for 1062 (Duplicate entry) e não tivermos ID (ou seja, é um novo registro)
                // e o erro for especificamente no numero_projeto, tentamos novamente.
                if ($e->errorInfo[1] == 1062 && empty($dados['id'])) {
                    $attempt++;
                    if ($attempt >= $maxRetries) throw $e;
                    usleep(100000); // Pequena pausa de 100ms antes de tentar novamente
                    continue;
                }
                throw $e;
            }
        }

        return false;
    }

    /**
     * Exclui ou arquiva um projeto.
     * Se o projeto tiver dependências (tarefas, orçamento), ele é arquivado (status = 'Cancelado').
     * Caso contrário, é excluído permanentemente.
     * @param int $id O ID do projeto.
     * @return string|false Retorna 'deleted', 'archived' ou false em caso de erro.
     */
    public function excluirProjeto(int $id)
    {
        try {
            // Verifica se existem tarefas vinculadas
            $stmtTarefas = $this->db->prepare("SELECT COUNT(*) FROM projetos_tarefas WHERE projeto_id = ?");
            $stmtTarefas->execute([$id]);
            $hasTarefas = $stmtTarefas->fetchColumn() > 0;

            // Verifica se existem itens no orçamento vinculados
            $stmtOrcamento = $this->db->prepare("SELECT COUNT(*) FROM projetos_orcamento WHERE projeto_id = ?");
            $stmtOrcamento->execute([$id]);
            $hasOrcamento = $stmtOrcamento->fetchColumn() > 0;

            // Verifica se existem contratos vinculados
            $stmtContratos = $this->db->prepare("SELECT COUNT(*) FROM contratos WHERE projeto_id = ?");
            $stmtContratos->execute([$id]);
            $hasContratos = $stmtContratos->fetchColumn() > 0;

            // Verifica se existem propostas vinculadas
            $stmtPropostas = $this->db->prepare("SELECT COUNT(*) FROM orcamento_proposta WHERE projeto_id = ?");
            $stmtPropostas->execute([$id]);
            $hasPropostas = $stmtPropostas->fetchColumn() > 0;

            if ($hasTarefas || $hasOrcamento || $hasContratos || $hasPropostas) {
                // Arquiva o projeto mudando seu status para 'Cancelado'
                $stmt = $this->db->prepare("UPDATE projetos SET status = 'Cancelado' WHERE id = ?");
                if ($stmt->execute([$id])) {
                    // Sincronização Sênior: Se o projeto foi arquivado por exclusão, cancela a proposta vinculada
                    $this->db->prepare("UPDATE orcamento_proposta SET status = 'Cancelada' WHERE projeto_id = ? AND status = 'Aprovada'")
                             ->execute([$id]);

                    // Suspende financeiro: Transações pendentes vinculadas ao projeto são canceladas
                    $this->db->prepare("UPDATE transacoes SET status = 'Cancelado' WHERE observacoes LIKE ? AND status IN ('Pendente', 'Atrasado')")
                             ->execute(["%Projeto ID: $id%"]);

                    $this->addTimelineEvent($id, 'PROJETO_ARQUIVADO', "Projeto arquivado (status alterado para Cancelado) devido a dependências existentes.");
                    return 'archived';
                }
                $this->lastError = "Falha ao arquivar o projeto.";
                return false;
            } else {
                // Exclui o projeto permanentemente
                // O banco de dados está configurado com ON DELETE CASCADE para as tabelas filhas
                // (art, cdt, mapas, arquivos, timeline, etc.), então basta excluir o pai.
                $stmt = $this->db->prepare("DELETE FROM projetos WHERE id = ?");
                return $stmt->execute([$id]) ? 'deleted' : false;
            }
        } catch (\PDOException $e) {
            error_log("Erro ao excluir projeto: " . $e->getMessage());
            $this->lastError = "Erro interno ao processar a exclusão do projeto: " . $e->getMessage();
            return false;
        }
    }

    /**
     * Restaura um projeto cancelado, alterando seu status para 'Em Execução'.
     * @param int $id O ID do projeto.
     * @return bool
     */
    public function restaurarProjeto(int $id): bool
    {
        try {
            $stmt = $this->db->prepare("UPDATE projetos SET status = 'Em Execução' WHERE id = ?");
            if ($stmt->execute([$id])) {
                // Sincronização Sênior: Se o projeto foi restaurado, volta a proposta para 'Aprovada'
                $this->db->prepare("UPDATE orcamento_proposta SET status = 'Aprovada' WHERE projeto_id = ? AND status = 'Cancelada'")
                         ->execute([$id]);

                // Reativa financeiro: Transações canceladas anteriormente pelo projeto voltam a ser pendentes
                $this->db->prepare("UPDATE transacoes SET status = 'Pendente' WHERE observacoes LIKE ? AND status = 'Cancelado'")
                         ->execute(["%Projeto ID: $id%"]);

                $this->addTimelineEvent($id, 'PROJETO_RESTAURADO', "Projeto restaurado (status alterado para Em Execução).");
                return true;
            }
            return false;
        } catch (\PDOException $e) {
            error_log("Erro ao restaurar projeto: " . $e->getMessage());
            return false;
        }
    }

    // --- MÉTODOS PARA O ORÇAMENTO DO PROJETO ---

    /**
     * Busca todos os itens de orçamento de um projeto específico.
     * @param int $projeto_id
     * @return array
     */
    public function getOrcamentoByProjetoId(int $projeto_id): array
    {
        $sql = "SELECT * FROM projetos_orcamento WHERE projeto_id = :projeto_id ORDER BY data_prevista ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['projeto_id' => $projeto_id]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Calcula os totais (previsto e real) para receitas e despesas de um projeto.
     * @param int $projeto_id
     * @return array
     */
    public function getOrcamentoSummary(int $projeto_id): array
    {
        $sql = "SELECT 
                    SUM(CASE WHEN tipo = 'Receita' THEN valor_previsto ELSE 0 END) as total_receita_prevista,
                    SUM(CASE WHEN tipo = 'Receita' THEN valor_real ELSE 0 END) as total_receita_real,
                    SUM(CASE WHEN tipo = 'Despesa' THEN valor_previsto ELSE 0 END) as total_despesa_prevista,
                    SUM(CASE WHEN tipo = 'Despesa' THEN valor_real ELSE 0 END) as total_despesa_real
                FROM projetos_orcamento 
                WHERE projeto_id = :projeto_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['projeto_id' => $projeto_id]);
        $summary = $stmt->fetch(\PDO::FETCH_ASSOC);

        return [
            'receita_prevista' => $summary['total_receita_prevista'] ?? 0,
            'receita_real' => $summary['total_receita_real'] ?? 0,
            'despesa_prevista' => $summary['total_despesa_prevista'] ?? 0,
            'despesa_real' => $summary['total_despesa_real'] ?? 0,
        ];
    }

    /**
     * Salva (insere ou atualiza) um item no orçamento do projeto.
     * @param array $dados
     * @return bool
     */
    public function salvarItemOrcamento(array $dados): bool
    {
        // --- Lógica de Upload de Arquivo ---
        if (isset($dados['comprovante']) && $dados['comprovante']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = ROOT_PATH . '/public/uploads/comprovantes/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0775, true);
            }

            $fileInfo = pathinfo($dados['comprovante']['name']);
            $extension = strtolower($fileInfo['extension']);

            $safeFilename = preg_replace('/[^A-Za-z0-9_\-]/', '_', $fileInfo['filename']);
            $newFilename = 'comprovante_proj' . $dados['projeto_id'] . '_' . $safeFilename . '_' . time() . '.' . $extension;
            $destination = $uploadDir . $newFilename;

            if (move_uploaded_file($dados['comprovante']['tmp_name'], $destination)) {
                $dados['comprovante_path'] = $newFilename;
            } else {
                return false;
            }
        }

        if (empty($dados['id'])) { // Inserir novo item
            $sql = "INSERT INTO projetos_orcamento (projeto_id, descricao, tipo, categoria, valor_previsto, data_prevista, observacoes, comprovante_path) 
                    VALUES (:projeto_id, :descricao, :tipo, :categoria, :valor_previsto, :data_prevista, :observacoes, :comprovante_path)";
            $stmt = $this->db->prepare($sql);
        } else { // Atualizar item existente
            $sql = "UPDATE projetos_orcamento SET 
                        descricao = :descricao, tipo = :tipo, categoria = :categoria, 
                        valor_previsto = :valor_previsto, valor_real = :valor_real, 
                        data_prevista = :data_prevista, data_real = :data_real, 
                        status = :status, observacoes = :observacoes"
                . (isset($dados['comprovante_path']) ? ", comprovante_path = :comprovante_path" : "") .
                " WHERE id = :id AND projeto_id = :projeto_id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $dados['id'], \PDO::PARAM_INT);
            $stmt->bindValue(':valor_real', !empty($dados['valor_real']) ? $dados['valor_real'] : null);
            $stmt->bindValue(':data_real', !empty($dados['data_real']) ? $dados['data_real'] : null);
            $stmt->bindValue(':status', $dados['status']);
        }

        $stmt->bindValue(':projeto_id', $dados['projeto_id'], \PDO::PARAM_INT);
        $stmt->bindValue(':descricao', $dados['descricao']);
        $stmt->bindValue(':tipo', $dados['tipo']);
        $stmt->bindValue(':categoria', $dados['categoria']);
        $stmt->bindValue(':valor_previsto', $dados['valor_previsto']);
        $stmt->bindValue(':data_prevista', $dados['data_prevista']);
        $stmt->bindValue(':observacoes', $dados['observacoes'] ?? null);

        $stmt->bindValue(':comprovante_path', $dados['comprovante_path'] ?? null, \PDO::PARAM_STR);

        $success = $stmt->execute();

        if ($success && empty($dados['id'])) {
            $descricao = "Novo item de orçamento '{$dados['descricao']}' adicionado.";
            $this->addTimelineEvent((int)$dados['projeto_id'], 'ORCAMENTO_ADICIONADO', $descricao);
        }

        return $success;
    }

    /**
     * Registra ou atualiza uma despesa realizada no orçamento do projeto vinculada a uma transação.
     * 
     * @param int $projetoId
     * @param int $transacaoId
     * @param string $descricao
     * @param float $valorReal
     * @param string $dataReal
     * @param string $categoria
     * @return bool
     */
    public function registrarDespesaDeTransacao(int $projetoId, int $transacaoId, string $descricao, float $valorReal, string $dataReal, string $categoria): bool
    {
        try {
            $tagVinculo = "[Transação ID: $transacaoId]";
            
            // Verifica se já existe pelo vínculo na observação
            $stmt = $this->db->prepare("SELECT id FROM projetos_orcamento WHERE projeto_id = ? AND observacoes LIKE ?");
            $stmt->execute([$projetoId, "%$tagVinculo%"]);
            $existing = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if ($existing) {
                // Atualiza registro existente
                $sql = "UPDATE projetos_orcamento SET valor_real = :valor, data_real = :data, status = 'Realizado', descricao = :desc, categoria = :cat WHERE id = :id";
                $stmtUpd = $this->db->prepare($sql);
                return $stmtUpd->execute([':valor' => $valorReal, ':data' => $dataReal, ':desc' => $descricao, ':cat' => $categoria, ':id' => $existing['id']]);
            } else {
                // Insere novo (Valor previsto = 0 pois é uma despesa não planejada explicitamente neste momento)
                $sql = "INSERT INTO projetos_orcamento (projeto_id, descricao, tipo, categoria, valor_previsto, data_prevista, valor_real, data_real, status, observacoes) VALUES (:pid, :desc, 'Despesa', :cat, 0, :data, :valor, :data, 'Realizado', :obs)";
                $stmtIns = $this->db->prepare($sql);
                return $stmtIns->execute([':pid' => $projetoId, ':desc' => $descricao, ':cat' => $categoria, ':data' => $dataReal, ':valor' => $valorReal, ':obs' => "Gerado automaticamente via Financeiro. $tagVinculo"]);
            }
        } catch (\PDOException $e) {
            error_log("Erro ao registrar despesa de transação no projeto: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Remove a despesa do orçamento do projeto vinculada a uma transação específica.
     * 
     * @param int $transacaoId
     * @return bool
     */
    public function removerDespesaDeTransacao(int $transacaoId): bool
    {
        try {
            $tagVinculo = "[Transação ID: $transacaoId]";
            $stmt = $this->db->prepare("DELETE FROM projetos_orcamento WHERE observacoes LIKE ?");
            return $stmt->execute(["%$tagVinculo%"]);
        } catch (\PDOException $e) {
            error_log("Erro ao remover despesa de transação no projeto: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Exclui um item do orçamento e seu comprovante.
     * @param int $id
     * @return bool
     */
    public function excluirItemOrcamento(int $id): bool
    {
        $stmt = $this->db->prepare("SELECT comprovante_path FROM projetos_orcamento WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $item = $stmt->fetch(\PDO::FETCH_ASSOC);

        $stmtDelete = $this->db->prepare("DELETE FROM projetos_orcamento WHERE id = :id");
        $success = $stmtDelete->execute(['id' => $id]);

        if ($success && $item && !empty($item['comprovante_path'])) {
            $filePath = ROOT_PATH . '/public/uploads/comprovantes/' . $item['comprovante_path'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        return $success;
    }

    /**
     * Busca projetos onde o valor realizado de despesas excede o orçamento total do projeto.
     * @return array
     */
    public function getProjetosComOrcamentoEstourado(): array
    {
        try {
            $sql = "
                SELECT 
                    p.id, 
                    p.nome, 
                    p.orcamento, 
                    COALESCE(SUM(po.valor_real), 0) as total_gasto
                FROM projetos p
                LEFT JOIN projetos_orcamento po ON p.id = po.projeto_id AND po.tipo = 'Despesa' AND po.status = 'Realizado'
                WHERE p.status NOT IN ('Concluído', 'Cancelado')
                GROUP BY p.id
                HAVING total_gasto > p.orcamento AND p.orcamento > 0
            ";
            return $this->db->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Erro ao buscar projetos com orçamento estourado: " . $e->getMessage());
            return [];
        }
    }

    // --- MÉTODOS PARA ART/RRT DO PROJETO ---

    /**
     * Busca todas as ARTs/RRTs de um projeto específico.
     * @param int $projeto_id
     * @return array
     */
    public function getArtByProjetoId(int $projeto_id): array
    {
        $sql = "SELECT * FROM projetos_art WHERE projeto_id = :projeto_id ORDER BY data_emissao DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['projeto_id' => $projeto_id]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Salva (insere ou atualiza) um registro de ART/RRT.
     * @param array $dados
     * @return bool
     */
    public function salvarArt(array $dados): bool
    {
        // Função auxiliar para upload de arquivos
        $uploadFile = function ($file, $prefix) use ($dados) {
            if (isset($file) && $file['error'] === UPLOAD_ERR_OK) {
                $uploadDir = ROOT_PATH . '/public/uploads/art/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0775, true);

                $fileInfo = pathinfo($file['name']);
                $extension = strtolower($fileInfo['extension']);
                $safeFilename = preg_replace('/[^A-Za-z0-9_\-]/', '_', $fileInfo['filename']);
                $newFilename = $prefix . '_proj' . $dados['projeto_id'] . '_' . time() . '.' . $extension;
                $destination = $uploadDir . $newFilename;

                if (move_uploaded_file($file['tmp_name'], $destination)) {
                    return $newFilename;
                }
            }
            return null;
        };

        // Processa o upload para cada tipo de arquivo
        $dados['documento_path'] = $uploadFile($dados['documento_art'] ?? null, 'art');
        $dados['boleto_path'] = $uploadFile($dados['boleto'] ?? null, 'boleto');
        $dados['comprovante_pgto_path'] = $uploadFile($dados['comprovante_pgto'] ?? null, 'comprovante');

        // Busca dados antigos para não sobrescrever caminhos de arquivo existentes com nulo
        $artAntiga = [];
        if (!empty($dados['id'])) {
            $stmt = $this->db->prepare("SELECT documento_path, boleto_path, comprovante_pgto_path FROM projetos_art WHERE id = ?");
            $stmt->execute([$dados['id']]);
            $artAntiga = $stmt->fetch(\PDO::FETCH_ASSOC);
        }

        if (empty($dados['id'])) { // Inserir novo
            $sql = "INSERT INTO projetos_art (projeto_id, tipo, numero, responsavel_tecnico, data_emissao, status, valor, documento_path, boleto_path, comprovante_pgto_path) 
                    VALUES (:projeto_id, :tipo, :numero, :responsavel_tecnico, :data_emissao, :status, :valor, :documento_path, :boleto_path, :comprovante_pgto_path)";
            $stmt = $this->db->prepare($sql);
        } else { // Atualizar existente
            $updateFields = "tipo = :tipo, numero = :numero, responsavel_tecnico = :responsavel_tecnico, data_emissao = :data_emissao, status = :status, valor = :valor";
            if ($dados['documento_path']) $updateFields .= ", documento_path = :documento_path";
            if ($dados['boleto_path']) $updateFields .= ", boleto_path = :boleto_path";
            if ($dados['comprovante_pgto_path']) $updateFields .= ", comprovante_pgto_path = :comprovante_pgto_path";

            $sql = "UPDATE projetos_art SET 
                        {$updateFields}
                    WHERE id = :id AND projeto_id = :projeto_id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $dados['id'], \PDO::PARAM_INT);
        }

        $stmt->bindValue(':projeto_id', $dados['projeto_id'], \PDO::PARAM_INT);
        $stmt->bindValue(':tipo', $dados['tipo']);
        $stmt->bindValue(':numero', $dados['numero']);
        $stmt->bindValue(':responsavel_tecnico', $dados['responsavel_tecnico']);
        $stmt->bindValue(':data_emissao', $dados['data_emissao']);
        $stmt->bindValue(':status', $dados['status']);
        $stmt->bindValue(':valor', !empty($dados['valor']) ? $dados['valor'] : null);

        // Faz o bind dos caminhos dos arquivos, usando o valor antigo se um novo não foi enviado (na edição)
        $stmt->bindValue(':documento_path', $dados['documento_path'] ?? $artAntiga['documento_path'] ?? null, \PDO::PARAM_STR);
        $stmt->bindValue(':boleto_path', $dados['boleto_path'] ?? $artAntiga['boleto_path'] ?? null, \PDO::PARAM_STR);
        $stmt->bindValue(':comprovante_pgto_path', $dados['comprovante_pgto_path'] ?? $artAntiga['comprovante_pgto_path'] ?? null, \PDO::PARAM_STR);

        $success = $stmt->execute();

        if ($success && empty($dados['id'])) {
            $descricao = "Novo registro de {$dados['tipo']} nº {$dados['numero']} adicionado.";
            $this->addTimelineEvent((int)$dados['projeto_id'], 'ART_ADICIONADA', $descricao);
        }

        return $success;
    }

    /**
     * Exclui um registro de ART/RRT e seu documento.
     * @param int $id
     * @return bool
     */
    public function excluirArt(int $id): bool
    {
        $stmt = $this->db->prepare("SELECT documento_path, boleto_path, comprovante_pgto_path FROM projetos_art WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $item = $stmt->fetch(\PDO::FETCH_ASSOC);

        $stmtDelete = $this->db->prepare("DELETE FROM projetos_art WHERE id = :id");
        $success = $stmtDelete->execute(['id' => $id]);

        if ($success && $item) {
            $uploadDir = ROOT_PATH . '/public/uploads/art/';
            // Apaga todos os arquivos associados que existirem
            if (!empty($item['documento_path']) && file_exists($uploadDir . $item['documento_path'])) unlink($uploadDir . $item['documento_path']);
            if (!empty($item['boleto_path']) && file_exists($uploadDir . $item['boleto_path'])) unlink($uploadDir . $item['boleto_path']);
            if (!empty($item['comprovante_pgto_path']) && file_exists($uploadDir . $item['comprovante_pgto_path'])) unlink($uploadDir . $item['comprovante_pgto_path']);
        }
        return true;
    }

    // --- MÉTODOS PARA CDT (CONTROLE DE DOCUMENTOS TÉCNICOS) ---

    /**
     * Busca todos os documentos técnicos de um projeto.
     * @param int $projeto_id
     * @return array
     */
    public function getCDTByProjetoId(int $projeto_id): array
    {
        $sql = "SELECT cdt.*, u.nome as usuario_nome 
                FROM projetos_cdt cdt
                LEFT JOIN usuarios u ON cdt.usuario_id = u.id
                WHERE cdt.projeto_id = :projeto_id 
                ORDER BY cdt.tipo_documento, cdt.data_upload DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['projeto_id' => $projeto_id]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Salva (insere ou atualiza) um documento técnico.
     * @param array $dados
     * @return bool
     */
    public function salvarCDT(array $dados): bool
    {
        // --- Lógica de Upload de Arquivo ---
        if (isset($dados['documento']) && $dados['documento']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = ROOT_PATH . '/public/uploads/cdt/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0775, true);
            }

            $fileInfo = pathinfo($dados['documento']['name']);
            $extension = strtolower($fileInfo['extension']);

            $safeFilename = preg_replace('/[^A-Za-z0-9_\-]/', '_', $fileInfo['filename']);
            $newFilename = 'cdt_proj' . $dados['projeto_id'] . '_' . $safeFilename . '_' . time() . '.' . $extension;
            $destination = $uploadDir . $newFilename;

            if (move_uploaded_file($dados['documento']['tmp_name'], $destination)) {
                $dados['documento_path'] = $newFilename;
            } else {
                error_log("Falha ao mover arquivo de CDT para o destino.");
                return false;
            }
        }

        // Se não há ID, é um INSERT
        if (empty($dados['id'])) {
            $sql = "INSERT INTO projetos_cdt (projeto_id, nome_documento, tipo_documento, versao, data_validade, observacoes, documento_path, usuario_id) 
                    VALUES (:projeto_id, :nome_documento, :tipo_documento, :versao, :data_validade, :observacoes, :documento_path, :usuario_id)";
            $stmt = $this->db->prepare($sql);
        } else {
            // Se há ID, é um UPDATE
            $updateDocPath = isset($dados['documento_path']) ? ", documento_path = :documento_path" : "";
            $sql = "UPDATE projetos_cdt SET 
                        nome_documento = :nome_documento, tipo_documento = :tipo_documento, versao = :versao, 
                        data_validade = :data_validade, observacoes = :observacoes {$updateDocPath}
                    WHERE id = :id AND projeto_id = :projeto_id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $dados['id'], \PDO::PARAM_INT);
        }

        $stmt->bindValue(':projeto_id', $dados['projeto_id'], \PDO::PARAM_INT);
        $stmt->bindValue(':nome_documento', $dados['nome_documento']);
        $stmt->bindValue(':tipo_documento', $dados['tipo_documento']);
        $stmt->bindValue(':versao', $dados['versao'] ?: 1, \PDO::PARAM_INT);
        $stmt->bindValue(':data_validade', !empty($dados['data_validade']) ? $dados['data_validade'] : null);
        $stmt->bindValue(':observacoes', $dados['observacoes'] ?? null);
        if (isset($dados['documento_path'])) $stmt->bindValue(':documento_path', $dados['documento_path']);
        if (empty($dados['id'])) $stmt->bindValue(':usuario_id', $_SESSION['user_id'] ?? null, \PDO::PARAM_INT);

        $success = $stmt->execute();

        if ($success && empty($dados['id'])) {
            $descricao = "Novo documento '{$dados['nome_documento']}' (Tipo: {$dados['tipo_documento']}) foi adicionado ao CDT.";
            $this->addTimelineEvent((int)$dados['projeto_id'], 'CDT_ADICIONADO', $descricao);
        }

        return $success;
    }

    /**
     * Exclui um documento técnico e seu arquivo físico.
     * @param int $id
     * @return bool
     */
    public function excluirCDT(int $id): bool
    {
        $stmt = $this->db->prepare("SELECT documento_path FROM projetos_cdt WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $item = $stmt->fetch(\PDO::FETCH_ASSOC);

        $stmtDelete = $this->db->prepare("DELETE FROM projetos_cdt WHERE id = :id");
        if ($stmtDelete->execute(['id' => $id]) && $item && !empty($item['documento_path'])) {
            $filePath = ROOT_PATH . '/public/uploads/cdt/' . $item['documento_path'];
            if (file_exists($filePath)) unlink($filePath);
        }
        return true;
    }

    // --- MÉTODOS PARA CM (CONTROLE DE MAPAS) ---

    /**
     * Busca todos os mapas de um projeto.
     * @param int $projeto_id
     * @return array
     */
    public function getMapasByProjetoId(int $projeto_id): array
    {
        $sql = "SELECT mapa.*, u.nome as usuario_nome 
                FROM projetos_mapas mapa
                LEFT JOIN usuarios u ON mapa.usuario_id = u.id
                WHERE mapa.projeto_id = :projeto_id 
                ORDER BY mapa.categoria_mapa, mapa.data_upload DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['projeto_id' => $projeto_id]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Salva (insere ou atualiza) um mapa.
     * @param array $dados
     * @return bool
     */
    public function salvarMapa(array $dados): bool
    {
        // --- Lógica de Upload de Arquivo ---
        if (isset($dados['mapa_arquivo']) && $dados['mapa_arquivo']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = ROOT_PATH . '/public/uploads/mapas/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0775, true);
            }

            $fileInfo = pathinfo($dados['mapa_arquivo']['name']);
            $extension = strtolower($fileInfo['extension']);

            $safeFilename = preg_replace('/[^A-Za-z0-9_\-]/', '_', $fileInfo['filename']);
            $newFilename = 'mapa_proj' . $dados['projeto_id'] . '_' . $safeFilename . '_' . time() . '.' . $extension;
            $destination = $uploadDir . $newFilename;

            if (move_uploaded_file($dados['mapa_arquivo']['tmp_name'], $destination)) {
                $dados['mapa_path'] = $newFilename;
                $dados['tipo_arquivo'] = $extension;
            } else {
                error_log("Falha ao mover arquivo de mapa para o destino.");
                return false;
            }
        }

        // Se não há ID, é um INSERT
        if (empty($dados['id'])) {
            $sql = "INSERT INTO projetos_mapas (projeto_id, nome_mapa, categoria_mapa, versao, observacoes, mapa_path, tipo_arquivo, usuario_id) 
                    VALUES (:projeto_id, :nome_mapa, :categoria_mapa, :versao, :observacoes, :mapa_path, :tipo_arquivo, :usuario_id)";
            $stmt = $this->db->prepare($sql);
        } else {
            // Se há ID, é um UPDATE (simplificado, pode ser expandido)
            $updateDocPath = isset($dados['mapa_path']) ? ", mapa_path = :mapa_path, tipo_arquivo = :tipo_arquivo" : "";
            $sql = "UPDATE projetos_mapas SET nome_mapa = :nome_mapa, categoria_mapa = :categoria_mapa, versao = :versao, observacoes = :observacoes {$updateDocPath}
                    WHERE id = :id AND projeto_id = :projeto_id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $dados['id'], \PDO::PARAM_INT);
        }

        $stmt->bindValue(':projeto_id', $dados['projeto_id'], \PDO::PARAM_INT);
        $stmt->bindValue(':nome_mapa', $dados['nome_mapa']);
        $stmt->bindValue(':categoria_mapa', $dados['categoria_mapa']);
        $stmt->bindValue(':versao', $dados['versao'] ?: 1, \PDO::PARAM_INT);
        $stmt->bindValue(':observacoes', $dados['observacoes'] ?? null);
        if (isset($dados['mapa_path'])) $stmt->bindValue(':mapa_path', $dados['mapa_path']);
        if (isset($dados['tipo_arquivo'])) $stmt->bindValue(':tipo_arquivo', $dados['tipo_arquivo']);
        if (empty($dados['id'])) $stmt->bindValue(':usuario_id', $_SESSION['user_id'] ?? null, \PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Exclui um mapa e seu arquivo físico.
     * @param int $id
     * @return bool
     */
    public function excluirMapa(int $id): bool
    {
        $stmt = $this->db->prepare("SELECT mapa_path FROM projetos_mapas WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $item = $stmt->fetch(\PDO::FETCH_ASSOC);

        $stmtDelete = $this->db->prepare("DELETE FROM projetos_mapas WHERE id = :id");
        if ($stmtDelete->execute(['id' => $id]) && $item && !empty($item['mapa_path'])) {
            $filePath = ROOT_PATH . '/public/uploads/mapas/' . $item['mapa_path'];
            if (file_exists($filePath)) unlink($filePath);
        }
        return true;
    }

    // --- MÉTODOS PARA ARQUIVOS GERAIS DO PROJETO ---

    /**
     * Busca todos os arquivos gerais de um projeto.
     * @param int $projeto_id
     * @return array
     */
    public function getArquivosByProjetoId(int $projeto_id): array
    {
        $sql = "SELECT arq.*, u.nome as usuario_nome 
                FROM projetos_arquivos arq
                LEFT JOIN usuarios u ON arq.usuario_id = u.id
                WHERE arq.projeto_id = :projeto_id 
                ORDER BY arq.categoria, arq.data_upload DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['projeto_id' => $projeto_id]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Salva um arquivo geral do projeto.
     * @param array $dados
     * @return bool
     */
    public function salvarArquivo(array $dados): bool
    {
        // --- Lógica de Upload de Arquivo ---
        if (isset($dados['arquivo']) && $dados['arquivo']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = ROOT_PATH . '/public/uploads/projetos_arquivos/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0775, true);
            }

            $fileInfo = pathinfo($dados['arquivo']['name']);
            $extension = strtolower($fileInfo['extension']);

            $safeFilename = preg_replace('/[^A-Za-z0-9_\-.]/', '_', $fileInfo['filename']);
            $newFilename = 'proj' . $dados['projeto_id'] . '_' . $safeFilename . '_' . time() . '.' . $extension;
            $destination = $uploadDir . $newFilename;

            if (move_uploaded_file($dados['arquivo']['tmp_name'], $destination)) {
                $dados['arquivo_path'] = $newFilename;
            } else {
                error_log("Falha ao mover arquivo geral do projeto para o destino.");
                return false;
            }
        } else {
            // Se não houver arquivo no upload, não podemos continuar (para novos registros)
            if (empty($dados['id'])) return false;
        }

        // Se não há ID, é um INSERT
        if (empty($dados['id'])) {
            $sql = "INSERT INTO projetos_arquivos (projeto_id, nome_arquivo, categoria, versao, descricao, arquivo_path, usuario_id) 
                    VALUES (:projeto_id, :nome_arquivo, :categoria, :versao, :descricao, :arquivo_path, :usuario_id)";
            $stmt = $this->db->prepare($sql);
        } else {
            // Se há ID, é um UPDATE (não permite alterar o arquivo, apenas os dados)
            $sql = "UPDATE projetos_arquivos SET nome_arquivo = :nome_arquivo, categoria = :categoria, versao = :versao, descricao = :descricao
                    WHERE id = :id AND projeto_id = :projeto_id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $dados['id'], \PDO::PARAM_INT);
        }

        $stmt->bindValue(':projeto_id', $dados['projeto_id'], \PDO::PARAM_INT);
        $stmt->bindValue(':nome_arquivo', $dados['nome_arquivo']);
        $stmt->bindValue(':categoria', $dados['categoria']);
        $stmt->bindValue(':versao', $dados['versao'] ?: '1.0');
        $stmt->bindValue(':descricao', $dados['descricao'] ?? null);
        if (empty($dados['id'])) {
            $stmt->bindValue(':arquivo_path', $dados['arquivo_path']);
            $stmt->bindValue(':usuario_id', $_SESSION['user_id'] ?? null, \PDO::PARAM_INT);
        }

        return $stmt->execute();
    }

    /**
     * Exclui um arquivo geral e seu arquivo físico.
     * @param int $id
     * @return bool
     */
    public function excluirArquivo(int $id): bool
    {
        $stmt = $this->db->prepare("SELECT arquivo_path FROM projetos_arquivos WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $item = $stmt->fetch(\PDO::FETCH_ASSOC);

        $stmtDelete = $this->db->prepare("DELETE FROM projetos_arquivos WHERE id = :id");
        if ($stmtDelete->execute(['id' => $id]) && $item && !empty($item['arquivo_path'])) {
            $filePath = ROOT_PATH . '/public/uploads/projetos_arquivos/' . $item['arquivo_path'];
            if (file_exists($filePath)) unlink($filePath);
        }
        return true;
    }
}
