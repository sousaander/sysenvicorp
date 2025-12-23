<?php

namespace App\Models;

// Adiciona o 'use' para a classe Model base
use App\Core\Model;
// A classe PDO já está no escopo global, não precisa de 'use'.

class ProjetosModel extends Model
{
    public function __construct()
    {
        parent::__construct();
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
            $sql = "SELECT p.id, p.nome, c.nome as cliente_nome, p.responsavel, p.data_inicial, p.status
                    FROM projetos p
                    LEFT JOIN clientes c ON p.cliente_id = c.id";

            $where = [];
            $params = [];

            // Se foi passado um filtro de status, aplica-o
            if (!empty($filtros['status']) && !in_array($filtros['status'], ['Todos', 'Todos Ativos'])) {
                $where[] = "p.status = :status";
                $params[':status'] = $filtros['status'];
            } else {
                // Comportamento padrão ou quando "Todos Ativos" é selecionado:
                // Mostra todos os projetos, exceto os arquivados.
                $where[] = "p.status NOT IN ('Concluído', 'Cancelado')";
            }

            if (!empty($where)) {
                $sql .= ' WHERE ' . implode(' AND ', $where);
            }

            $sql .= " ORDER BY p.id DESC LIMIT :limit OFFSET :offset";

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
        } else {
            // Comportamento padrão ou quando "Todos Ativos" é selecionado.
            $where[] = "p.status NOT IN ('Concluído', 'Cancelado')";
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
            // CORREÇÃO: Fazer JOIN com a tabela de clientes para buscar o nome.
            $sql = "SELECT p.*, c.nome as cliente_nome 
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
            $sql = "SELECT p.id, p.nome, c.nome as cliente_nome
                    FROM projetos p
                    LEFT JOIN clientes c ON p.cliente_id = c.id
                    WHERE p.status NOT IN ('Concluído', 'Cancelado')
                    ORDER BY p.nome ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Erro ao buscar projetos para select: " . $e->getMessage());
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

            return [
                'orcamento' => $orcamentoSummary,
                'art_count' => (int) $artCount,
                'documentos_count' => (int) $docsCount,
                'mapas_count' => (int) $mapasCount,
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
        // Busca o estado atual do projeto antes de salvar (para detectar mudanças)
        $projetoAtual = !empty($dados['id']) ? $this->getProjetoById((int)$dados['id']) : null;

        // Sanitiza e prepara os dados
        $id = !empty($dados['id']) ? (int) $dados['id'] : null;
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

        try {
            if ($id) {
                // UPDATE: Atualiza um projeto existente
                $sql = "UPDATE projetos 
                        SET nome = :nome, tipo_servico = :tipo_servico, cliente_id = :cliente_id, empreendimento = :empreendimento,
                            data_inicial = :data_inicial, data_fim_prevista = :data_fim_prevista, orcamento = :orcamento, orcamento_id = :orcamento_id,
                            area_id = :area_id, tamanho_ha = :tamanho_ha, produto_entregue = :produto_entregue,
                            responsavel_elaboracao = :responsavel_elaboracao, responsavel = :responsavel, responsavel_execucao = :responsavel_execucao,
                            status = :status, observacoes = :observacoes
                        WHERE id = :id";
                $stmt = $this->db->prepare($sql);
                $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
            } else {
                // INSERT: Cria um novo projeto
                $sql = "INSERT INTO projetos (
                            nome, tipo_servico, cliente_id, empreendimento, data_inicial, data_fim_prevista,
                            orcamento, orcamento_id, area_id, tamanho_ha, produto_entregue,
                            responsavel_elaboracao, responsavel, responsavel_execucao,
                            status, observacoes, dataCriacao
                        ) VALUES (
                            :nome, :tipo_servico, :cliente_id, :empreendimento, :data_inicial, :data_fim_prevista,
                            :orcamento, :orcamento_id, :area_id, :tamanho_ha, :produto_entregue,
                            :responsavel_elaboracao, :responsavel, :responsavel_execucao,
                            :status, :observacoes, NOW()
                        )";
                $stmt = $this->db->prepare($sql);
            }

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

            $success = $stmt->execute();

            if ($success) {
                $projeto_id = $id ?: $this->db->lastInsertId();
                if (!$id) {
                    // Evento de criação do projeto
                    $this->addTimelineEvent($projeto_id, 'PROJETO_CRIADO', "Projeto '{$nome}' foi criado.");
                } else if ($projetoAtual && $projetoAtual['status'] !== $status) {
                    // Evento de mudança de status
                    $descricao = "Status do projeto alterado de '{$projetoAtual['status']}' para '{$status}'.";
                    $this->addTimelineEvent($projeto_id, 'STATUS_ALTERADO', $descricao);
                }
            }

            return $success;
        } catch (\PDOException $e) {
            // Lança a exceção para que o Controller possa capturá-la
            // e exibir uma mensagem de erro mais detalhada.
            throw $e;
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
