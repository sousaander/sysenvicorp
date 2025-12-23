<?php

namespace App\Models;
use App\Core\Model;
use PDO;

class ClientesModel extends Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Busca dados resumidos sobre a base de clientes.
     */
    public function getClientesSummary()
    {
        try {
            $totalAtivos = $this->db->query("SELECT COUNT(*) FROM clientes WHERE status = 'Ativo'")->fetchColumn();
            $novosMes = $this->db->query("SELECT COUNT(*) FROM clientes WHERE dataCriacao >= DATE_FORMAT(NOW(), '%Y-%m-01')")->fetchColumn();
            $propostasPendentes = $this->db->query("SELECT COUNT(*) FROM clientes WHERE status = 'Proposta Enviada'")->fetchColumn();
            $riscoPerda = $this->db->query("SELECT COUNT(*) FROM clientes WHERE status = 'Risco de Perda'")->fetchColumn();

            return [
                'totalAtivos' => (int) $totalAtivos,
                'novosMes' => (int) $novosMes,
                'propostasPendentes' => (int) $propostasPendentes,
                'riscoPerda' => (int) $riscoPerda,
            ];
        } catch (\PDOException $e) {
            error_log("Erro ao buscar resumo de clientes: " . $e->getMessage());
            return ['totalAtivos' => 0, 'novosMes' => 0, 'propostasPendentes' => 0, 'riscoPerda' => 0];
        }
    }

    /**
     * Busca uma lista de clientes, com suporte para filtros e paginação.
     * @param array $filtros Filtros de busca (para uso futuro)
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getClientes(array $filtros = [], int $limit = 5, int $offset = 0): array
    {
        try {
            $sql = "SELECT id, nome, contato_principal, data_ultima_interacao, status FROM clientes WHERE 1=1";
            $params = [];

            if (!empty($filtros['busca'])) {
                // A busca agora inclui nome, cnpj_cpf e a cidade extraída do JSON de endereço
                $sql .= " AND (nome LIKE :busca OR cnpj_cpf LIKE :busca OR JSON_UNQUOTE(JSON_EXTRACT(enderecos_json, '$.principal.cidade')) LIKE :busca)";
                $params[':busca'] = '%' . $filtros['busca'] . '%';
            }

            $sql .= " ORDER BY id DESC LIMIT :limit OFFSET :offset";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':limit', $limit, \PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, \PDO::PARAM_INT);
            foreach ($params as $key => &$val) {
                $stmt->bindParam($key, $val);
            }
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Erro ao buscar lista de clientes: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Conta o número total de clientes que correspondem a um filtro.
     * @param array $filtros
     * @return int
     */
    public function getClientesCount(array $filtros = []): int
    {
        $sql = "SELECT COUNT(*) FROM clientes WHERE 1=1";
        $params = [];

        if (!empty($filtros['busca'])) {
            $sql .= " AND (nome LIKE :busca OR cnpj_cpf LIKE :busca OR JSON_UNQUOTE(JSON_EXTRACT(enderecos_json, '$.principal.cidade')) LIKE :busca)";
            $params[':busca'] = '%' . $filtros['busca'] . '%';
        }

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => &$val) {
            $stmt->bindParam($key, $val);
        }

        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Busca um cliente específico pelo ID.
     * @param int $id O ID do cliente.
     * @return array|null
     */
    public function getClienteById(int $id): ?array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM clientes WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
        } catch (\PDOException $e) {
            error_log("Erro ao buscar cliente por ID: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Busca todos os clientes para preencher listas de seleção.
     * @return array
     */
    public function getAllClientes(): array
    {
        try {
            $stmt = $this->db->query("SELECT id, nome FROM clientes ORDER BY nome ASC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Erro ao buscar todos os clientes: " . $e->getMessage());
            return []; // Retorna vazio em caso de erro para não quebrar a aplicação
        }
    }

    /**
     * Busca todas as categorias de clientes para preencher listas de seleção.
     * @return array
     */
    public function getCategorias(): array
    {
        try {
            // Assumindo que você criará uma tabela `cliente_categorias` com `id` e `nome`
            $stmt = $this->db->query("SELECT id, nome FROM cliente_categorias ORDER BY nome ASC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            // Se a tabela não existir, retorna um array vazio para não quebrar o formulário
            error_log("Erro ao buscar categorias de clientes (tabela 'cliente_categorias' pode não existir): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Adiciona uma nova categoria de cliente e retorna seu ID.
     * @param string $nome
     * @return int|false
     */
    public function addCategoria(string $nome)
    {
        try {
            $stmt = $this->db->prepare("INSERT INTO cliente_categorias (nome) VALUES (?)");
            $stmt->execute([$nome]);
            return (int)$this->db->lastInsertId();
        } catch (\PDOException $e) {
            error_log("Erro ao adicionar categoria de cliente: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Busca todos os segmentos de uma categoria específica.
     * @param int $categoriaId
     * @return array
     */
    public function getSegmentosByCategoriaId(int $categoriaId): array
    {
        try {
            $stmt = $this->db->prepare("SELECT id, nome FROM segmentos WHERE categoria_id = ? ORDER BY nome ASC");
            $stmt->execute([$categoriaId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Erro ao buscar segmentos por categoria: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Adiciona um novo segmento a uma categoria e retorna seu ID.
     * @param string $nome
     * @param int $categoriaId
     * @return int|false
     */
    public function addSegmento(string $nome, int $categoriaId)
    {
        if (empty($nome) || empty($categoriaId)) {
            return false;
        }

        try {
            $stmt = $this->db->prepare("INSERT INTO segmentos (nome, categoria_id) VALUES (?, ?)");
            $stmt->execute([$nome, $categoriaId]);
            return (int)$this->db->lastInsertId();
        } catch (\PDOException $e) {
            error_log("Erro ao adicionar segmento: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Busca todas as categorias com seus respectivos segmentos.
     * @return array
     */
    public function getCategoriasComSegmentos(): array
    {
        $sql = "SELECT 
                    c.id as categoria_id, c.nome as categoria_nome,
                    s.id as segmento_id, s.nome as segmento_nome
                FROM cliente_categorias c
                LEFT JOIN segmentos s ON c.id = s.categoria_id
                ORDER BY c.nome, s.nome";
        try {
            $stmt = $this->db->query($sql);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Agrupa os segmentos dentro de cada categoria
            $categorias = [];
            foreach ($results as $row) {
                if (!isset($categorias[$row['categoria_id']])) {
                    $categorias[$row['categoria_id']] = [
                        'id' => $row['categoria_id'],
                        'nome' => $row['categoria_nome'],
                        'segmentos' => []
                    ];
                }
                if ($row['segmento_id']) {
                    $categorias[$row['categoria_id']]['segmentos'][] = [
                        'id' => $row['segmento_id'],
                        'nome' => $row['segmento_nome']
                    ];
                }
            }
            return array_values($categorias);
        } catch (\PDOException $e) {
            error_log("Erro ao buscar categorias com segmentos: " . $e->getMessage());
            return [];
        }
    }

    public function updateCategoria(int $id, string $nome): bool {
        $stmt = $this->db->prepare("UPDATE cliente_categorias SET nome = ? WHERE id = ?");
        return $stmt->execute([$nome, $id]);
    }

    public function deleteCategoria(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM cliente_categorias WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function updateSegmento(int $id, string $nome): bool {
        $stmt = $this->db->prepare("UPDATE segmentos SET nome = ? WHERE id = ?");
        return $stmt->execute([$nome, $id]);
    }

    public function deleteSegmento(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM segmentos WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Salva um novo cliente ou atualiza um existente no banco de dados.
     *
     * @param array $dados Os dados do cliente vindos do formulário.
     * @return bool Retorna true em caso de sucesso, false em caso de falha.
     */
    public function salvarCliente(array $dados): bool
    {
        // Sanitiza e prepara os dados
        $id = !empty($dados['id']) ? (int) $dados['id'] : null;

        // Dados da tabela principal
        $nome = trim($dados['nome'] ?? ''); // Razão Social / Nome Completo
        $cnpj_cpf = preg_replace('/\D/', '', trim($dados['cnpj_cpf'] ?? ''));
        $status = trim($dados['status'] ?? 'Potencial');
        $tipo_cliente = trim($dados['tipo_cliente'] ?? 'Juridica');
        $nome_fantasia = trim($dados['nome_fantasia'] ?? '');
        $rg = trim($dados['rg'] ?? '');
        $inscricao_estadual = trim($dados['inscricao_estadual'] ?? '');
        $ie_isento = isset($dados['ie_isento']) ? 1 : 0;
        $inscricao_municipal = trim($dados['inscricao_municipal'] ?? '');
        $data_nascimento = !empty($dados['data_nascimento']) ? $dados['data_nascimento'] : null;
        $categoria_id = !empty($dados['categoria_id']) ? (int)$dados['categoria_id'] : null; // Agora usamos o ID
        $segmento = trim($dados['segmento'] ?? ''); // Novo campo
        $classificacao = trim($dados['classificacao'] ?? 'Bronze');
        $origem_cliente = trim($dados['origem_cliente'] ?? '');
        $observacoes_iniciais = trim($dados['observacoes_iniciais'] ?? '');
        $motivo_inativacao = trim($dados['motivo_inativacao'] ?? '');
        $data_inativacao = !empty($dados['data_inativacao']) ? $dados['data_inativacao'] : null;

        // Dados para colunas JSON
        $enderecos_json = isset($dados['enderecos']) ? json_encode($dados['enderecos']) : null;
        $contatos_json = isset($dados['contatos']) ? json_encode($dados['contatos']) : null;

        // Campos legados para manter compatibilidade (se necessário)
        $contato_principal = $dados['contatos']['principal']['nome'] ?? '';
        $email = $dados['contatos']['principal']['email'] ?? '';
        $telefone = $dados['contatos']['principal']['telefone'] ?? '';
        $endereco_principal = $dados['enderecos']['principal']['logradouro'] ?? ''; // Apenas para compatibilidade

        try {
            if ($id) {
                // UPDATE: Atualiza um cliente existente
                $sql = "UPDATE clientes SET 
                            nome = :nome, cnpj_cpf = :cnpj_cpf, status = :status, tipo_cliente = :tipo_cliente, nome_fantasia = :nome_fantasia, rg = :rg,
                            inscricao_estadual = :ie, ie_isento = :ie_isento, inscricao_municipal = :im, data_nascimento = :data_nascimento,
                            categoria_id = :categoria_id, segmento = :segmento, classificacao = :classificacao, origem_cliente = :origem, observacoes_iniciais = :obs,
                            motivo_inativacao = :motivo_inativacao, data_inativacao = :data_inativacao,
                            enderecos_json = :end_json, contatos_json = :cont_json, financeiro_json = :fin_json, comercial_json = :com_json,
                            contato_principal = :contato_principal, email = :email, telefone = :telefone, endereco = :endereco,
                            dataAtualizacao = NOW() 
                        WHERE id = :id";
                $stmt = $this->db->prepare($sql);
                $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
            } else {
                // INSERT: Cria um novo cliente
                $sql = "INSERT INTO clientes (nome, cnpj_cpf, status, tipo_cliente, nome_fantasia, rg, inscricao_estadual, ie_isento, inscricao_municipal, data_nascimento, categoria_id, segmento, classificacao, origem_cliente, observacoes_iniciais, motivo_inativacao, data_inativacao, enderecos_json, contatos_json, financeiro_json, comercial_json, contato_principal, email, telefone, endereco, dataCriacao, dataAtualizacao) 
                        VALUES (:nome, :cnpj_cpf, :status, :tipo_cliente, :nome_fantasia, :rg, :ie, :ie_isento, :im, :data_nascimento, :categoria_id, :segmento, :classificacao, :origem, :obs, :motivo_inativacao, :data_inativacao, :end_json, :cont_json, :fin_json, :com_json, :contato_principal, :email, :telefone, :endereco, NOW(), NOW())";
                $stmt = $this->db->prepare($sql);
            }

            $stmt->bindParam(':nome', $nome);
            $stmt->bindParam(':cnpj_cpf', $cnpj_cpf);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':tipo_cliente', $tipo_cliente);
            $stmt->bindParam(':nome_fantasia', $nome_fantasia);
            $stmt->bindParam(':rg', $rg);
            $stmt->bindParam(':ie', $inscricao_estadual);
            $stmt->bindParam(':ie_isento', $ie_isento, \PDO::PARAM_INT);
            $stmt->bindParam(':im', $inscricao_municipal);
            $stmt->bindParam(':data_nascimento', $data_nascimento);
            $stmt->bindValue(':categoria_id', $categoria_id, \PDO::PARAM_INT); // Bind do ID
            $stmt->bindParam(':segmento', $segmento);
            $stmt->bindParam(':classificacao', $classificacao);
            $stmt->bindParam(':origem', $origem_cliente);
            $stmt->bindParam(':obs', $observacoes_iniciais);
            $stmt->bindParam(':motivo_inativacao', $motivo_inativacao);
            $stmt->bindParam(':data_inativacao', $data_inativacao);
            $stmt->bindParam(':end_json', $enderecos_json);
            $stmt->bindParam(':cont_json', $contatos_json);
            // Bind dos campos legados
            $stmt->bindParam(':contato_principal', $contato_principal);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':telefone', $telefone);
            $stmt->bindParam(':endereco', $endereco_principal);

            return $stmt->execute();
        } catch (\PDOException $e) {
            // Lança a exceção para que o Controller possa capturá-la
            // e exibir uma mensagem de erro mais detalhada.
            throw $e;
        }
    }

    /**
     * Exclui um cliente do banco de dados.
     *
     * @param int $id O ID do cliente a ser excluído.
     * @return bool Retorna true em caso de sucesso, false em caso de falha.
     */
    public function excluirCliente(int $id): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM clientes WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (\PDOException $e) {
            error_log("Erro ao excluir cliente: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Busca dados para o funil de vendas rápido.
     * @return array
     */
    public function getFunilVendasSummary(): array
    {
        try {
            $leadsCount = $this->db->query("SELECT COUNT(*) FROM clientes WHERE status = 'Lead'")->fetchColumn();
            $propostasEnviadasCount = $this->db->query("SELECT COUNT(*) FROM clientes WHERE status = 'Proposta Enviada'")->fetchColumn();
            $fechamentoCount = $this->db->query("SELECT COUNT(*) FROM clientes WHERE status = 'Ativo'")->fetchColumn();

            $totalFunil = $leadsCount + $propostasEnviadasCount + $fechamentoCount;

            return [
                'leadsCount' => $leadsCount,
                'propostasEnviadasCount' => $propostasEnviadasCount,
                'fechamentoCount' => $fechamentoCount,
                'leadsPercent' => $totalFunil > 0 ? round(($leadsCount / $totalFunil) * 100) : 0,
                'propostasEnviadasPercent' => $totalFunil > 0 ? round(($propostasEnviadasCount / $totalFunil) * 100) : 0,
                'fechamentoPercent' => $totalFunil > 0 ? round(($fechamentoCount / $totalFunil) * 100) : 0,
            ];
        } catch (\PDOException $e) {
            error_log("Erro ao buscar dados do funil de vendas: " . $e->getMessage());
            // Retorna zerado em caso de erro
            return ['leadsCount' => 0, 'propostasEnviadasCount' => 0, 'fechamentoCount' => 0, 'leadsPercent' => 0, 'propostasEnviadasPercent' => 0, 'fechamentoPercent' => 0];
        }
    }

    /**
     * Registra uma nova interação com um cliente e atualiza a data da última interação.
     *
     * @param array $dados Os dados da interação.
     * @return bool
     */
    public function registrarInteracao(array $dados): bool
    {
        $cliente_id = (int)$dados['cliente_id'];
        $data_interacao = $dados['data_interacao'] ?? date('Y-m-d H:i:s');
        $tipo_interacao = trim($dados['tipo_interacao'] ?? '');
        $descricao = trim($dados['descricao'] ?? '');
        // Futuramente, pegar o ID do usuário logado na sessão
        $usuario_id = 1; // Mock

        if (empty($cliente_id) || empty($tipo_interacao) || empty($descricao)) {
            return false;
        }

        try {
            $this->db->beginTransaction();

            // 1. Insere na tabela de interações
            $sqlInteracao = "INSERT INTO cliente_interacoes (cliente_id, data_interacao, tipo_interacao, descricao, usuario_id) VALUES (?, ?, ?, ?, ?)";
            $stmtInteracao = $this->db->prepare($sqlInteracao);
            $stmtInteracao->execute([$cliente_id, $data_interacao, $tipo_interacao, $descricao, $usuario_id]);

            // 2. Atualiza a data da última interação na tabela de clientes
            $sqlCliente = "UPDATE clientes SET data_ultima_interacao = ? WHERE id = ?";
            $stmtCliente = $this->db->prepare($sqlCliente);
            $stmtCliente->execute([$data_interacao, $cliente_id]);

            return $this->db->commit();
        } catch (\PDOException $e) {
            $this->db->rollBack();
            error_log("Erro ao registrar interação: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Busca todas as interações de um cliente específico.
     *
     * @param int $cliente_id O ID do cliente.
     * @return array
     */
    public function getInteracoesByClienteId(int $cliente_id): array
    {
        try {
            $sql = "SELECT * FROM cliente_interacoes WHERE cliente_id = ? ORDER BY data_interacao DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$cliente_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Erro ao buscar interações do cliente: " . $e->getMessage());
            return [];
        }
    }
}
