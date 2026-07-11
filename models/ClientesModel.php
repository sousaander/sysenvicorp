<?php

namespace App\Models;

use App\Core\Model;
use PDO;
use PDOException;

class ClientesModel extends Model
{
    private $lastError = null;

    public function __construct()
    {
        parent::__construct();
        $this->ensureTablesExist();
        $this->ensureColumnsExist();
    }

    public function getLastError()
    {
        return $this->lastError;
    }

    private function ensureTablesExist()
    {
        // 1. Garante a tabela principal de clientes (caso não exista)
        $sqlClientes = "CREATE TABLE IF NOT EXISTS clientes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nome VARCHAR(255) NOT NULL,
            cnpj_cpf VARCHAR(20),
            status VARCHAR(20) DEFAULT 'Ativo',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        $this->db->exec($sqlClientes);

        // 2. Garante tabelas auxiliares para categorias e segmentos
        $this->db->exec("CREATE TABLE IF NOT EXISTS categorias_clientes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nome VARCHAR(100) NOT NULL,
            UNIQUE KEY unique_cat_nome (nome)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        $this->db->exec("CREATE TABLE IF NOT EXISTS segmentos_clientes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            categoria_id INT NOT NULL,
            nome VARCHAR(100) NOT NULL,
            FOREIGN KEY (categoria_id) REFERENCES categorias_clientes(id) ON DELETE CASCADE,
            UNIQUE KEY unique_segmento_cat (categoria_id, nome)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        $sql = "CREATE TABLE IF NOT EXISTS clientes_interacoes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            cliente_id INT NOT NULL,
            usuario_id INT,
            tipo_interacao VARCHAR(50) NOT NULL,
            descricao TEXT,
            data_interacao DATETIME NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
            FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

        $this->db->exec($sql);

        // 4. Popula com dados iniciais se as tabelas estiverem vazias
        $this->seedInitialData();
    }

    private function seedInitialData()
    {
        try {
            // Verifica se a tabela de categorias existe e está vazia
            $check = $this->db->query("SELECT COUNT(*) FROM categorias_clientes");
            if ($check && $check->fetchColumn() == 0) {
                $categorias = [
                    'Comércio' => ['Varejo', 'Atacado', 'E-commerce'],
                    'Serviços' => ['Consultoria', 'TI', 'Saúde', 'Educação', 'Marketing'],
                    'Indústria' => ['Manufatura', 'Construção Civil', 'Alimentos', 'Têxtil'],
                    'Setor Público' => ['Municipal', 'Estadual', 'Federal']
                ];

                foreach ($categorias as $catNome => $segmentos) {
                    // Insere Categoria
                    $stmt = $this->db->prepare("INSERT INTO categorias_clientes (nome) VALUES (?)");
                    $stmt->execute([$catNome]);
                    $catId = $this->db->lastInsertId();

                    // Insere Segmentos da Categoria
                    $stmtSeg = $this->db->prepare("INSERT INTO segmentos_clientes (categoria_id, nome) VALUES (?, ?)");
                    foreach ($segmentos as $segNome) {
                        $stmtSeg->execute([$catId, $segNome]);
                    }
                }
            }
        } catch (PDOException $e) {
            // Ignora erros de seed para não bloquear o sistema caso as tabelas ainda não tenham sido criadas corretamente
        }
    }

    private function ensureColumnsExist()
    {
        // Lista de colunas novas que precisam existir na tabela clientes
        $columnsToCheck = [
            'tipo_cliente' => 'VARCHAR(20)',
            'nome_fantasia' => 'VARCHAR(255)',
            'sigla' => 'VARCHAR(20)',
            'rg' => 'VARCHAR(20)',
            'inscricao_estadual' => 'VARCHAR(50)',
            'ie_isento' => 'TINYINT(1) DEFAULT 0',
            'inscricao_municipal' => 'VARCHAR(50)',
            'data_nascimento' => 'DATE',
            'categoria_id' => 'INT',
            'segmento' => 'VARCHAR(100)',
            'classificacao' => 'VARCHAR(50)',
            'origem_cliente' => 'VARCHAR(100)',
            'observacoes_iniciais' => 'TEXT',
            'motivo_inativacao' => 'VARCHAR(255)',
            'data_inativacao' => 'DATE',
            'enderecos_json' => 'TEXT',
            'contatos_json' => 'TEXT',
            'financeiro_json' => 'TEXT',
            'comercial_json' => 'TEXT',
            'contato_principal' => 'VARCHAR(255)',
            'email' => 'VARCHAR(255)',
            'telefone' => 'VARCHAR(50)',
            'endereco' => 'TEXT'
        ];

        foreach ($columnsToCheck as $col => $type) {
            $exists = false;
            try {
                // MELHORIA: Usar SHOW COLUMNS é mais robusto que SELECT
                $stmt = $this->db->query("SHOW COLUMNS FROM clientes LIKE '$col'");
                if ($stmt && $stmt->rowCount() > 0) {
                    $exists = true;
                }
            } catch (PDOException $e) {
                // Ignora erros e assume que não existe
            }

            if (!$exists) {
                $sqlAdd = "ALTER TABLE clientes ADD COLUMN $col $type";
                if (stripos($type, 'DEFAULT') === false && stripos($type, 'NOT NULL') === false) {
                    $sqlAdd .= " DEFAULT NULL";
                }
                try {
                    $this->db->exec($sqlAdd);
                } catch (PDOException $e) {
                    error_log("Erro ao adicionar coluna $col: " . $e->getMessage());
                }
            }
        }
    }

    /**
     * Salva um novo cliente ou atualiza um existente, tratando os campos JSON.
     *
     * @param array $dados Os dados do cliente vindos do controller.
     * @return int|false Retorna o ID do cliente salvo/atualizado ou false em caso de falha.
     */
    public function salvarCliente(array $dados)
    {
        // 1. Prepara os dados JSON para o banco de dados
        $dados['enderecos_json'] = isset($dados['enderecos']) ? json_encode($dados['enderecos']) : '[]';
        $dados['contatos_json'] = isset($dados['contatos']) ? json_encode($dados['contatos']) : '[]';
        $dados['financeiro_json'] = isset($dados['financeiro']) ? json_encode($dados['financeiro']) : '[]';
        $dados['comercial_json'] = isset($dados['comercial']) ? json_encode($dados['comercial']) : '[]';

        // 2. Popula campos legados/denormalizados para compatibilidade com outras views
        $contatos = $dados['contatos'] ?? [];
        $dados['contato_principal'] = $contatos['responsavel']['nome'] ?? null;
        $dados['email'] = $contatos['principal']['email'] ?? null;
        $dados['telefone'] = $contatos['principal']['telefone'] ?? null;
        $dados['endereco'] = $this->formatarEndereco($dados['enderecos']['principal'] ?? []);

        // 3. Define a query SQL (INSERT ou UPDATE)
        $isUpdate = !empty($dados['id']);

        if ($isUpdate) {
            $sql = "UPDATE clientes SET 
                        tipo_cliente = :tipo_cliente, nome = :nome, 
                        nome_fantasia = :nome_fantasia, sigla = :sigla,
                        cnpj_cpf = :cnpj_cpf, rg = :rg, inscricao_estadual = :inscricao_estadual, 
                        ie_isento = :ie_isento, inscricao_municipal = :inscricao_municipal, 
                        data_nascimento = :data_nascimento, categoria_id = :categoria_id, 
                        segmento = :segmento, classificacao = :classificacao, origem_cliente = :origem_cliente, 
                        observacoes_iniciais = :observacoes_iniciais, motivo_inativacao = :motivo_inativacao, 
                        data_inativacao = :data_inativacao, status = :status, 
                        enderecos_json = :enderecos_json, contatos_json = :contatos_json, 
                        financeiro_json = :financeiro_json, comercial_json = :comercial_json,
                        contato_principal = :contato_principal, email = :email, 
                        telefone = :telefone, endereco = :endereco
                    WHERE id = :id";
        } else {
            // Remove o ID para garantir que o auto-incremento funcione
            unset($dados['id']);
            $sql = "INSERT INTO clientes (
                        tipo_cliente, nome, nome_fantasia, sigla, cnpj_cpf, rg, inscricao_estadual, 
                        ie_isento, inscricao_municipal, data_nascimento, categoria_id, 
                        segmento, classificacao, origem_cliente, observacoes_iniciais, 
                        motivo_inativacao, data_inativacao, status, enderecos_json, 
                        contatos_json, financeiro_json, comercial_json,
                        contato_principal, email, telefone, endereco
                    ) VALUES (
                        :tipo_cliente, :nome, :nome_fantasia, :sigla, :cnpj_cpf, :rg, :inscricao_estadual, 
                        :ie_isento, :inscricao_municipal, :data_nascimento, :categoria_id, 
                        :segmento, :classificacao, :origem_cliente, :observacoes_iniciais, 
                        :motivo_inativacao, :data_inativacao, :status, :enderecos_json, 
                        :contatos_json, :financeiro_json, :comercial_json,
                        :contato_principal, :email, :telefone, :endereco
                    )";
        }

        try {
            $stmt = $this->db->prepare($sql);

            // 4. Faz o bind dos parâmetros
            $stmt->bindValue(':tipo_cliente', $dados['tipo_cliente']);
            $stmt->bindValue(':nome', $dados['nome']);
            $stmt->bindValue(':nome_fantasia', $dados['nome_fantasia']);
            $stmt->bindValue(':sigla', $dados['sigla']);
            $stmt->bindValue(':cnpj_cpf', $dados['cnpj_cpf']);
            $stmt->bindValue(':rg', $dados['rg']);
            $stmt->bindValue(':inscricao_estadual', $dados['inscricao_estadual']);
            $stmt->bindValue(':ie_isento', $dados['ie_isento'], PDO::PARAM_INT);
            $stmt->bindValue(':inscricao_municipal', $dados['inscricao_municipal']);
            $stmt->bindValue(':data_nascimento', $dados['data_nascimento'] ?: null, $dados['data_nascimento'] ? PDO::PARAM_STR : PDO::PARAM_NULL);

            if (!empty($dados['categoria_id'])) {
                $stmt->bindValue(':categoria_id', $dados['categoria_id'], PDO::PARAM_INT);
            } else {
                $stmt->bindValue(':categoria_id', null, PDO::PARAM_NULL);
            }

            $stmt->bindValue(':segmento', $dados['segmento']);
            $stmt->bindValue(':classificacao', $dados['classificacao']);
            $stmt->bindValue(':origem_cliente', $dados['origem_cliente']);
            $stmt->bindValue(':observacoes_iniciais', $dados['observacoes_iniciais']);
            $stmt->bindValue(':motivo_inativacao', $dados['motivo_inativacao']);
            $stmt->bindValue(':data_inativacao', $dados['data_inativacao'] ?: null, $dados['data_inativacao'] ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $stmt->bindValue(':status', $dados['status']);

            // Bind dos campos JSON
            $stmt->bindValue(':enderecos_json', $dados['enderecos_json']);
            $stmt->bindValue(':contatos_json', $dados['contatos_json']);
            $stmt->bindValue(':financeiro_json', $dados['financeiro_json']);
            $stmt->bindValue(':comercial_json', $dados['comercial_json']);

            // Bind dos campos legados
            $stmt->bindValue(':contato_principal', $dados['contato_principal']);
            $stmt->bindValue(':email', $dados['email']);
            $stmt->bindValue(':telefone', $dados['telefone']);
            $stmt->bindValue(':endereco', $dados['endereco']);

            if ($isUpdate) {
                $stmt->bindValue(':id', $dados['id'], PDO::PARAM_INT);
            }

            if ($stmt->execute()) {
                return $isUpdate ? $dados['id'] : (int)$this->db->lastInsertId();
            }
            return false;
        } catch (PDOException $e) {
            $this->lastError = "Erro de Banco de Dados: " . $e->getMessage();
            // Loga o erro para depuração
            error_log("Erro ao salvar cliente no ClientesModel: " . $e->getMessage());
            // Retorna false para indicar falha
            return false;
        }
    }

    /**
     * Formata um array de endereço em uma string única para o campo legado.
     * @param array $endereco
     * @return string
     */
    private function formatarEndereco(array $endereco): string
    {
        if (empty($endereco) || empty($endereco['logradouro'])) {
            return '';
        }

        $linha1 = [];
        if (!empty($endereco['logradouro'])) $linha1[] = $endereco['logradouro'];
        if (!empty($endereco['numero']))     $linha1[] = $endereco['numero'];
        if (!empty($endereco['complemento'])) $linha1[] = $endereco['complemento'];

        $partes = [implode(', ', $linha1)];
        if (!empty($endereco['bairro'])) $partes[] = $endereco['bairro'];

        $cidadeUf = '';
        if (!empty($endereco['cidade'])) $cidadeUf .= $endereco['cidade'];
        if (!empty($endereco['estado'])) $cidadeUf .= '/' . $endereco['estado'];
        if (!empty($cidadeUf)) $partes[] = $cidadeUf;

        return implode(' - ', $partes);
    }

    /**
     * Busca um cliente pelo ID.
     * @param int $id
     * @return array|false
     */
    public function getClienteById(int $id)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM clientes WHERE id = :id");
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar cliente por ID: " . $e->getMessage());
            return false;
        }
    }

    public function getClientes(array $filtros = [], int $limit = 10, int $offset = 0): array
    {
        try {
            $sql = "SELECT * FROM clientes WHERE 1=1";
            $params = [];

            if (!empty($filtros['busca'])) {
                $sql .= " AND (nome LIKE :busca OR cnpj_cpf LIKE :busca OR email LIKE :busca)";
                $params[':busca'] = '%' . $filtros['busca'] . '%';
            }
            if (!empty($filtros['status'])) {
                $sql .= " AND status = :status";
                $params[':status'] = $filtros['status'];
            }

            $sql .= " ORDER BY nome ASC LIMIT :limit OFFSET :offset";

            $stmt = $this->db->prepare($sql);
            foreach ($params as $key => $val) {
                $stmt->bindValue($key, $val);
            }
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar clientes: " . $e->getMessage());
            return [];
        }
    }

    public function getClientesCount(array $filtros = []): int
    {
        try {
            $sql = "SELECT COUNT(*) FROM clientes WHERE 1=1";
            $params = [];

            if (!empty($filtros['busca'])) {
                $sql .= " AND (nome LIKE :busca OR cnpj_cpf LIKE :busca OR email LIKE :busca)";
                $params[':busca'] = '%' . $filtros['busca'] . '%';
            }
            if (!empty($filtros['status'])) {
                $sql .= " AND status = :status";
                $params[':status'] = $filtros['status'];
            }

            $stmt = $this->db->prepare($sql);
            foreach ($params as $key => $val) {
                $stmt->bindValue($key, $val);
            }
            $stmt->execute();
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Erro ao contar clientes: " . $e->getMessage());
            return 0;
        }
    }

    public function getAllClientes(): array
    {
        try {
            $stmt = $this->db->query("SELECT 
                        id, nome, sigla, email, contato_principal, 
                        endereco, telefone, cnpj_cpf, nome_fantasia, 
                        financeiro_json, comercial_json, enderecos_json 
                    FROM clientes 
                    WHERE status = 'Ativo' 
                    ORDER BY nome ASC");

            $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($clientes as &$cliente) {
                $cliente['bairro'] = '';
                $cliente['municipio'] = '';
                $cliente['logradouro'] = '';
                $cliente['numero'] = '';
                $cliente['complemento'] = '';
                $cliente['estado'] = '';

                $enderecos = [];
                if (!empty($cliente['enderecos_json'])) {
                    $json = json_decode($cliente['enderecos_json'], true);
                    if (is_array($json)) {
                        $enderecos = $json;
                    }
                }

                $principal = $enderecos['principal'] ?? [];
                if (is_array($principal)) {
                    $cliente['bairro'] = $principal['bairro'] ?? '';
                    $cliente['municipio'] = $principal['cidade'] ?? '';
                    $cliente['logradouro'] = $principal['logradouro'] ?? '';
                    $cliente['numero'] = $principal['numero'] ?? '';
                    $cliente['complemento'] = $principal['complemento'] ?? '';
                    $cliente['estado'] = $principal['estado'] ?? '';

                    if (empty($cliente['endereco'])) {
                        $cliente['endereco'] = $this->formatarEndereco($principal);
                    }
                }
            }
            unset($cliente);

            return $clientes;
        } catch (PDOException $e) {
            error_log("Erro ao buscar todos os clientes: " . $e->getMessage());
            return [];
        }
    }

    public function getInteracoesByClienteId(int $id): array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM clientes_interacoes WHERE cliente_id = :id ORDER BY data_interacao DESC");
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getClientesSummary()
    {
        return [
            'totalAtivos' => $this->getClientesCount(['status' => 'Ativo']),
            'novosMes' => 0,
            'propostasPendentes' => 0,
            'riscoPerda' => 0
        ];
    }

    public function getFunilVendasSummary()
    {
        return [];
    }

    public function getCategorias()
    {
        try {
            // Tenta buscar categorias se a tabela existir
            $stmt = $this->db->query("SELECT * FROM categorias_clientes ORDER BY nome ASC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getSegmentosByCategoriaId(int $categoriaId)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM segmentos_clientes WHERE categoria_id = :id ORDER BY nome ASC");
            $stmt->bindValue(':id', $categoriaId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function registrarInteracao(array $dados): bool
    {
        $sql = "INSERT INTO clientes_interacoes (cliente_id, usuario_id, tipo_interacao, descricao, data_interacao) 
                VALUES (:cliente_id, :usuario_id, :tipo_interacao, :descricao, :data_interacao)";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':cliente_id', $dados['cliente_id'], PDO::PARAM_INT);
            $stmt->bindValue(':usuario_id', $dados['usuario_id'] ?? null, PDO::PARAM_INT);
            $stmt->bindValue(':tipo_interacao', $dados['tipo_interacao']);
            $stmt->bindValue(':descricao', $dados['descricao']);
            $stmt->bindValue(':data_interacao', $dados['data_interacao']);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erro ao registrar interação: " . $e->getMessage());
            return false;
        }
    }

    public function limparHistoricoInteracoes(int $clienteId): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM clientes_interacoes WHERE cliente_id = :cliente_id");
            $stmt->bindValue(':cliente_id', $clienteId, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erro ao limpar histórico de interações: " . $e->getMessage());
            return false;
        }
    }

    public function arquivarCliente(int $id): bool
    {
        try {
            $sql = "UPDATE clientes SET status = 'Inativo', data_inativacao = NOW() WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erro ao arquivar cliente: " . $e->getMessage());
            return false;
        }
    }

    public function restaurarCliente(int $id): bool
    {
        try {
            $sql = "UPDATE clientes SET status = 'Ativo', data_inativacao = NULL, motivo_inativacao = NULL WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erro ao restaurar cliente: " . $e->getMessage());
            return false;
        }
    }

    public function addCategoria(string $nome): ?int
    {
        try {
            // Verifica se já existe para evitar duplicidade
            $stmtCheck = $this->db->prepare("SELECT id FROM categorias_clientes WHERE nome = ?");
            $stmtCheck->execute([$nome]);
            $exists = $stmtCheck->fetchColumn();
            if ($exists) return (int)$exists;

            $stmt = $this->db->prepare("INSERT INTO categorias_clientes (nome) VALUES (?)");
            if ($stmt->execute([$nome])) {
                return (int)$this->db->lastInsertId();
            }
            return null;
        } catch (PDOException $e) {
            if ($e->errorInfo[1] == 1062) {
                // Se falhou por duplicidade em condição de corrida, busca o ID existente
                $stmtCheck = $this->db->prepare("SELECT id FROM categorias_clientes WHERE nome = ?");
                $stmtCheck->execute([$nome]);
                return (int)$stmtCheck->fetchColumn() ?: null;
            }
            error_log("Erro ao adicionar categoria: " . $e->getMessage());
            return null;
        }
    }

    public function addSegmento(string $nome, int $categoriaId): ?int
    {
        try {
            // Verifica se já existe para evitar duplicidade na mesma categoria
            $stmtCheck = $this->db->prepare("SELECT id FROM segmentos_clientes WHERE nome = ? AND categoria_id = ?");
            $stmtCheck->execute([$nome, $categoriaId]);
            $exists = $stmtCheck->fetchColumn();
            if ($exists) return (int)$exists;

            $stmt = $this->db->prepare("INSERT INTO segmentos_clientes (nome, categoria_id) VALUES (?, ?)");
            if ($stmt->execute([$nome, $categoriaId])) {
                return (int)$this->db->lastInsertId();
            }
            return null;
        } catch (PDOException $e) {
            if ($e->errorInfo[1] == 1062) {
                // Se falhou por duplicidade em condição de corrida, busca o ID existente
                $stmtCheck = $this->db->prepare("SELECT id FROM segmentos_clientes WHERE nome = ? AND categoria_id = ?");
                $stmtCheck->execute([$nome, $categoriaId]);
                return (int)$stmtCheck->fetchColumn() ?: null;
            }
            error_log("Erro ao adicionar segmento: " . $e->getMessage());
            return null;
        }
    }

    public function getCategoriasComSegmentos(): array
    {
        try {
            $categorias = $this->getCategorias();
            $segmentos = $this->db->query("SELECT * FROM segmentos_clientes ORDER BY nome ASC")->fetchAll(PDO::FETCH_ASSOC);

            $categoriasById = [];
            foreach ($categorias as $cat) {
                $categoriasById[$cat['id']] = $cat;
                $categoriasById[$cat['id']]['segmentos'] = [];
            }

            foreach ($segmentos as $seg) {
                if (isset($categoriasById[$seg['categoria_id']])) {
                    $categoriasById[$seg['categoria_id']]['segmentos'][] = $seg;
                }
            }
            return array_values($categoriasById);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function updateCategoria(int $id, string $nome): bool
    {
        try {
            $stmt = $this->db->prepare("UPDATE categorias_clientes SET nome = ? WHERE id = ?");
            return $stmt->execute([$nome, $id]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function deleteCategoria(int $id): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM categorias_clientes WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function updateSegmento(int $id, string $nome): bool
    {
        try {
            $stmt = $this->db->prepare("UPDATE segmentos_clientes SET nome = ? WHERE id = ?");
            return $stmt->execute([$nome, $id]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function deleteSegmento(int $id): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM segmentos_clientes WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            return false;
        }
    }
}
