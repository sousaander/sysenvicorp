<?php

namespace App\Models;

use App\Core\Model;
use PDO;
use PDOException;

class FornecedoresModel extends Model
{
    private $lastError = null;

    public function __construct()
    {
        parent::__construct();
        // Garante que a tabela exista para evitar erros na primeira execução
        $this->ensureTableExists();
        $this->ensureColumnsExist();
    }

    /**
     * Retorna o último erro ocorrido.
     */
    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    /**
     * Garante que a tabela de fornecedores e suas colunas JSON existam.
     */
    private function ensureTableExists()
    {
        $sql = "CREATE TABLE IF NOT EXISTS fornecedores (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nome VARCHAR(255) NOT NULL,
            nome_fantasia VARCHAR(255),
            cnpj_cpf VARCHAR(20),
            tipo_pessoa ENUM('Fisica', 'Juridica') DEFAULT 'Juridica',
            status VARCHAR(20) DEFAULT 'Ativo',
            categoria_fornecimento VARCHAR(100),
            inscricao_estadual VARCHAR(50),
            inscricao_municipal VARCHAR(50),
            ie_isento TINYINT(1) DEFAULT 0,
            
            endereco_json JSON,
            contato_json JSON,
            dados_financeiros_json JSON,
            info_comerciais_json JSON,
            documentacao_json JSON,
            
            motivo_inativacao VARCHAR(255),
            data_inativacao DATE,
            
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

        try {
            $this->db->exec($sql);
            
            // Cria a tabela de ocorrências se não existir
            $sqlOcorrencias = "CREATE TABLE IF NOT EXISTS fornecedor_ocorrencias (
                id INT AUTO_INCREMENT PRIMARY KEY,
                fornecedor_id INT NOT NULL,
                data_ocorrencia DATE,
                tipo VARCHAR(50),
                descricao TEXT,
                responsavel VARCHAR(100),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (fornecedor_id) REFERENCES fornecedores(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
            $this->db->exec($sqlOcorrencias);

        } catch (PDOException $e) {
            error_log("Erro ao criar tabelas de fornecedores: " . $e->getMessage());
        }
    }

    /**
     * Garante que as colunas necessárias existam na tabela, criando-as se faltarem.
     * Isso evita erros de "Column not found" em bancos de dados legados ou desatualizados.
     */
    private function ensureColumnsExist()
    {
        $columnsToCheck = [
            'nome_fantasia' => 'VARCHAR(255)',
            'cnpj_cpf' => 'VARCHAR(20)',
            'tipo_pessoa' => "ENUM('Fisica', 'Juridica') DEFAULT 'Juridica'",
            'categoria_fornecimento' => 'VARCHAR(100)',
            'inscricao_estadual' => 'VARCHAR(50)',
            'inscricao_municipal' => 'VARCHAR(50)',
            'ie_isento' => 'TINYINT(1) DEFAULT 0',
            'endereco_json' => 'JSON',
            'contato_json' => 'JSON',
            'dados_financeiros_json' => 'JSON',
            'info_comerciais_json' => 'JSON',
            'documentacao_json' => 'JSON',
            'motivo_inativacao' => 'VARCHAR(255)',
            'data_inativacao' => 'DATE',
            'status' => "VARCHAR(20) DEFAULT 'Ativo'"
        ];

        foreach ($columnsToCheck as $col => $def) {
            try {
                $stmt = $this->db->query("SHOW COLUMNS FROM fornecedores LIKE '$col'");
                if ($stmt->rowCount() == 0) {
                    $this->db->exec("ALTER TABLE fornecedores ADD COLUMN $col $def");
                }
            } catch (PDOException $e) {
                // Silencia erros menores para não interromper o fluxo se a coluna já existir de outra forma
            }
        }
    }

    /**
     * Verifica se um CNPJ/CPF já existe no banco de dados para outro fornecedor.
     * @param string $cnpj
     * @param int|null $excludeId ID a ser ignorado (útil na edição)
     * @return bool
     */
    public function cnpjExiste(string $cnpj, ?int $excludeId = null): bool
    {
        try {
            $sql = "SELECT COUNT(*) FROM fornecedores WHERE cnpj_cpf = :cnpj";
            if ($excludeId) {
                $sql .= " AND id != :excludeId";
            }
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':cnpj', $cnpj);
            if ($excludeId) {
                $stmt->bindValue(':excludeId', $excludeId, PDO::PARAM_INT);
            }
            $stmt->execute();
            return (int)$stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Erro ao verificar existência do CNPJ fornecedor: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Busca dados resumidos sobre a base de fornecedores.
     */
    public function getFornecedoresSummary()
    {
        try {
            $totalAtivos = $this->db->query("SELECT COUNT(*) FROM fornecedores WHERE status = 'Ativo'")->fetchColumn();
            $emHomologacao = $this->db->query("SELECT COUNT(*) FROM fornecedores WHERE status = 'Em Homologação'")->fetchColumn();
            
            // Busca quantos fornecedores têm a flag 'risco_alto' no JSON de info comerciais (exemplo de uso de JSON)
            // Nota: Em MySQL 5.7+ pode-se usar JSON_EXTRACT
            $riscoAlto = 0;
            // Mockando risco por enquanto ou implementando lógica simples
            
            return [
                'totalAtivos' => (int) $totalAtivos,
                'contratoVencer30' => 0, // Implementar se houver vínculo direto
                'pendenciaDocs' => (int) $emHomologacao,
                'riscoAlto' => 0,
            ];
        } catch (PDOException $e) {
            error_log("Erro ao buscar resumo de fornecedores: " . $e->getMessage());
            return ['totalAtivos' => 0, 'contratoVencer30' => 0, 'pendenciaDocs' => 0, 'riscoAlto' => 0];
        }
    }

    /**
     * Busca uma lista de fornecedores com filtros e paginação.
     */
    public function getFornecedores(array $filtros = [], int $limit = 10, int $offset = 0): array
    {
        try {
            $sql = "SELECT 
                        id, 
                        nome, 
                        nome_fantasia,
                        cnpj_cpf,
                        cnpj_cpf as cnpj, 
                        status, 
                        categoria_fornecimento,
                        categoria_fornecimento as categoria,
                        endereco_json,
                        contato_json
                    FROM fornecedores
                    WHERE 1=1";
            
            $params = [];

            if (!empty($filtros['busca'])) {
                $term = '%' . $filtros['busca'] . '%';
                $sql .= " AND (nome LIKE :busca_nome OR nome_fantasia LIKE :busca_fantasia OR cnpj_cpf LIKE :busca_cnpj)";
                $params[':busca_nome'] = $term;
                $params[':busca_fantasia'] = $term;
                $params[':busca_cnpj'] = $term;
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
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            
            $stmt->execute();
            $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Processamento PHP do JSON para garantir compatibilidade e exibição na View
            foreach ($resultados as &$row) {
                $end = json_decode($row['endereco_json'] ?? '{}', true);
                $cont = json_decode($row['contato_json'] ?? '{}', true);
                $row['cidade']   = $end['cidade'] ?? '—';
                $row['uf']       = $end['uf'] ?? '';
                $row['telefone'] = $cont['telefone_comercial'] ?? '—';
                $row['email']    = $cont['email_principal'] ?? '—';
            }
            return $resultados;

        } catch (PDOException $e) {
            error_log("Erro ao buscar lista de fornecedores: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Conta o número total de fornecedores.
     */
    public function getFornecedoresCount(array $filtros = []): int
    {
        try {
            $sql = "SELECT COUNT(id) FROM fornecedores WHERE 1=1";
            $params = [];

            if (!empty($filtros['busca'])) {
                $term = '%' . $filtros['busca'] . '%';
                $sql .= " AND (nome LIKE :busca_nome OR nome_fantasia LIKE :busca_fantasia OR cnpj_cpf LIKE :busca_cnpj)";
                $params[':busca_nome'] = $term;
                $params[':busca_fantasia'] = $term;
                $params[':busca_cnpj'] = $term;
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
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Erro ao contar fornecedores: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Busca todos os fornecedores para listas de seleção.
     */
    public function getAllFornecedores(): array
    {
        try {
            $stmt = $this->db->query("SELECT id, nome FROM fornecedores WHERE status = 'Ativo' ORDER BY nome ASC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar todos os fornecedores: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Busca um fornecedor específico pelo ID.
     */
    public function getFornecedorById(int $id): ?array
    {
        try {
            // Seleciona * e também faz aliases para manter compatibilidade com views que esperam 'nome' ou 'cnpj'
            $sql = "SELECT *, nome as razao_social, cnpj_cpf as cnpj FROM fornecedores WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            error_log("Erro ao buscar fornecedor por ID: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Salva (cria ou atualiza) um fornecedor.
     */
    public function salvarFornecedor(array $dados): bool
    {
        $id = !empty($dados['id']) ? (int)$dados['id'] : null;

        try {
            if ($id) {
                // UPDATE
                $sql = "UPDATE fornecedores SET 
                            nome = :nome,
                            nome_fantasia = :nome_fantasia,
                            cnpj_cpf = :cnpj_cpf,
                            tipo_pessoa = :tipo_pessoa,
                            status = :status,
                            categoria_fornecimento = :categoria_fornecimento,
                            inscricao_estadual = :inscricao_estadual,
                            inscricao_municipal = :inscricao_municipal,
                            ie_isento = :ie_isento,
                            endereco_json = :endereco_json,
                            contato_json = :contato_json,
                            dados_financeiros_json = :dados_financeiros_json,
                            info_comerciais_json = :info_comerciais_json,
                            documentacao_json = :documentacao_json,
                            motivo_inativacao = :motivo_inativacao,
                            data_inativacao = :data_inativacao
                        WHERE id = :id";
                $stmt = $this->db->prepare($sql);
                $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            } else {
                // INSERT
                $sql = "INSERT INTO fornecedores (
                            nome, nome_fantasia, cnpj_cpf, tipo_pessoa, status, 
                            categoria_fornecimento, inscricao_estadual, inscricao_municipal, ie_isento,
                            endereco_json, contato_json, dados_financeiros_json, info_comerciais_json, documentacao_json,
                            motivo_inativacao, data_inativacao
                        ) VALUES (
                            :nome, :nome_fantasia, :cnpj_cpf, :tipo_pessoa, :status, 
                            :categoria_fornecimento, :inscricao_estadual, :inscricao_municipal, :ie_isento,
                            :endereco_json, :contato_json, :dados_financeiros_json, :info_comerciais_json, :documentacao_json,
                            :motivo_inativacao, :data_inativacao
                        )";
                $stmt = $this->db->prepare($sql);
            }

            // Bind parameters
            $stmt->bindValue(':nome', $dados['nome']); // Controller envia como 'nome'
            $stmt->bindValue(':nome_fantasia', $dados['nome_fantasia'] ?? null);
            $stmt->bindValue(':cnpj_cpf', $dados['cnpj_cpf'] ?? null);
            $stmt->bindValue(':tipo_pessoa', $dados['tipo_pessoa'] ?? 'Juridica');
            $stmt->bindValue(':status', $dados['status'] ?? 'Ativo');
            $stmt->bindValue(':categoria_fornecimento', $dados['categoria_fornecimento'] ?? null);
            $stmt->bindValue(':inscricao_estadual', $dados['inscricao_estadual'] ?? null);
            $stmt->bindValue(':inscricao_municipal', $dados['inscricao_municipal'] ?? null);
            $stmt->bindValue(':ie_isento', isset($dados['ie_isento']) ? $dados['ie_isento'] : 0, PDO::PARAM_INT);
            
            // JSON Fields (Controller já envia strings JSON ou null)
            $stmt->bindValue(':endereco_json', $dados['endereco_json'] ?? null);
            $stmt->bindValue(':contato_json', $dados['contato_json'] ?? null);
            $stmt->bindValue(':dados_financeiros_json', $dados['dados_financeiros_json'] ?? null);
            $stmt->bindValue(':info_comerciais_json', $dados['info_comerciais_json'] ?? null);
            $stmt->bindValue(':documentacao_json', $dados['documentacao_json'] ?? null);
            
            $stmt->bindValue(':motivo_inativacao', $dados['motivo_inativacao'] ?? null);
            $stmt->bindValue(':data_inativacao', !empty($dados['data_inativacao']) ? $dados['data_inativacao'] : null);

            return $stmt->execute();

        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            error_log("Erro ao salvar fornecedor: " . $e->getMessage());
            // Lança a exceção para que o controller possa capturá-la e mostrar o erro
            throw $e;
        }
    }

    /**
     * Arquiva um fornecedor (Soft Delete).
     */
    public function arquivarFornecedor(int $id): bool
    {
        try {
            $sql = "UPDATE fornecedores SET status = 'Inativo', data_inativacao = CURDATE(), motivo_inativacao = 'Arquivado pelo sistema' WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Erro ao arquivar fornecedor: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Restaura um fornecedor inativo.
     */
    public function restaurarFornecedor(int $id): bool
    {
        try {
            $sql = "UPDATE fornecedores SET status = 'Ativo', data_inativacao = NULL, motivo_inativacao = NULL WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Erro ao restaurar fornecedor: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Busca ocorrências vinculadas a um fornecedor.
     */
    public function getOcorrenciasByFornecedorId(int $fornecedorId): array
    {
        try {
            $sql = "SELECT * FROM fornecedor_ocorrencias WHERE fornecedor_id = ? ORDER BY data_ocorrencia DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$fornecedorId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar ocorrências: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Registra uma nova ocorrência.
     */
    public function salvarOcorrencia(array $dados): bool
    {
        try {
            $sql = "INSERT INTO fornecedor_ocorrencias (fornecedor_id, data_ocorrencia, tipo, descricao, responsavel) 
                    VALUES (:fornecedor_id, :data_ocorrencia, :tipo, :descricao, :responsavel)";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':fornecedor_id' => $dados['fornecedor_id'],
                ':data_ocorrencia' => $dados['data_ocorrencia'],
                ':tipo' => $dados['tipo'],
                ':descricao' => $dados['descricao'],
                ':responsavel' => $dados['responsavel']
            ]);
        } catch (PDOException $e) {
            error_log("Erro ao salvar ocorrência: " . $e->getMessage());
            return false;
        }
    }
}
