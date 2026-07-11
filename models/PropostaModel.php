<?php

namespace App\Models;

use App\Core\Model;
use PDO;
use App\Models\ContratosModel;

class PropostaModel extends Model
{
    public function __construct()
    {
        parent::__construct();
        $this->ensureTableAndColumnsExist();
    }

    /**
     * Retorna a conexão com o banco de dados
     */
    public function getDbConnection()
    {
        return $this->db;
    }

    /** @var string Armazena a última mensagem de erro. */
    private $lastError = '';

    /**
     * Garante que a tabela 'orcamento_proposta' e suas colunas existam.
     * Também cria a tabela de histórico se não existir.
     */
    private function ensureTableAndColumnsExist()
    {
        // Tabela principal
        $sqlProposta = "CREATE TABLE IF NOT EXISTS orcamento_proposta (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tipo_criacao VARCHAR(50) DEFAULT 'from_scratch', -- 'from_scratch' ou 'vinculado_projeto'
            projeto_id INT NULL,
            cliente_id INT NULL,
            cliente_sigla VARCHAR(50) NULL,
            cliente_documento VARCHAR(30) NULL,
            cliente_telefone VARCHAR(30) NULL,
            cliente_logradouro VARCHAR(255) NULL,
            cliente_numero VARCHAR(50) NULL,
            cliente_complemento VARCHAR(255) NULL,
            cliente_bairro VARCHAR(255) NULL,
            cliente_municipio VARCHAR(255) NULL,
            cliente_endereco TEXT NULL,
            nome_proposta VARCHAR(255) NOT NULL,
            descricao TEXT NULL, -- Descrição geral da proposta
            objetivo TEXT NULL,
            data_proposta DATE NOT NULL,
            validade INT DEFAULT 30, -- Validade em dias
            responsavel_interno INT NULL,
            contrato_id INT NULL,
            
            -- Novos campos para cálculo detalhado
            numero_proposta VARCHAR(20) UNIQUE NULL, -- ORC-YYYY-NNNN
            servicos_json JSON NULL,
            materiais_json JSON NULL,
            custos_extras_json JSON NULL,
            impostos_valor DECIMAL(15,2) DEFAULT 0.00,
            descontos_valor DECIMAL(15,2) DEFAULT 0.00,
            forma_pagamento TEXT NULL,
            prazo_execucao VARCHAR(255) NULL,
            garantias TEXT NULL,

            total_servicos DECIMAL(15,2) DEFAULT 0.00,
            total_materiais DECIMAL(15,2) DEFAULT 0.00,
            total_final DECIMAL(15,2) DEFAULT 0.00,
            
            status ENUM('Rascunho', 'Enviada', 'Aprovada', 'Rejeitada', 'Cancelada') DEFAULT 'Rascunho',
            anexos JSON NULL,
            
            token_aprovacao VARCHAR(64) UNIQUE NULL,
            token_validade DATETIME NULL,

            latitude DECIMAL(10,8) NULL,
            longitude DECIMAL(11,8) NULL,

            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (projeto_id) REFERENCES projetos(id) ON DELETE SET NULL,
            FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE SET NULL,
            FOREIGN KEY (responsavel_interno) REFERENCES usuarios(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

        $sqlHistorico = "CREATE TABLE IF NOT EXISTS orcamento_proposta_historico (
            id INT AUTO_INCREMENT PRIMARY KEY,
            proposta_id INT NOT NULL,
            versao INT NOT NULL,
            usuario_id INT NULL,
            data_revisao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            motivo_alteracao TEXT NULL,
            dados_proposta_json JSON NOT NULL,
            FOREIGN KEY (proposta_id) REFERENCES orcamento_proposta(id) ON DELETE CASCADE,
            FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

        try {
            $this->db->exec($sqlProposta);
            $this->db->exec($sqlHistorico);

            // Adicionar colunas se não existirem (para migração de versões antigas)
            $columnsToAdd = [
                'numero_proposta' => "VARCHAR(20) UNIQUE NULL",
                'tipo_criacao' => "VARCHAR(50) DEFAULT 'from_scratch'",
                'contrato_id' => "INT NULL",
                'servicos_json' => "JSON NULL",
                'materiais_json' => "JSON NULL",
                'custos_extras_json' => "JSON NULL",
                'impostos_valor' => "DECIMAL(15,2) DEFAULT 0.00",
                'descontos_valor' => "DECIMAL(15,2) DEFAULT 0.00",
                'desconto_tipo' => "VARCHAR(20) DEFAULT 'percentual'",
                'condicao_pagamento' => "VARCHAR(255) NULL",
                'forma_pagamento' => "TEXT NULL",
                'prazo_execucao' => "VARCHAR(255) NULL",
                'garantias' => "TEXT NULL",
                'objetivo' => "TEXT NULL",
                'token_aprovacao' => "VARCHAR(64) UNIQUE NULL",
                'token_validade' => "DATETIME NULL",
                'aprovado_por' => "VARCHAR(255) NULL",
                'aprovado_em' => "DATETIME NULL",
                'aceite_ip' => "VARCHAR(45) NULL",
                'aceite_em' => "DATETIME NULL",
                'aceite_nome' => "VARCHAR(255) NULL",
                'created_at' => "TIMESTAMP DEFAULT CURRENT_TIMESTAMP",
                'cliente_telefone' => "VARCHAR(30) NULL",
                'cliente_sigla' => "VARCHAR(50) NULL",
                'cliente_documento' => "VARCHAR(30) NULL",
                'cliente_logradouro' => "VARCHAR(255) NULL",
                'cliente_numero' => "VARCHAR(50) NULL",
                'cliente_complemento' => "VARCHAR(255) NULL",
                'cliente_bairro' => "VARCHAR(255) NULL",
                'cliente_municipio' => "VARCHAR(255) NULL",
                'cliente_endereco' => "TEXT NULL",
                'updated_at' => "TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP",
                'versao_documento' => "VARCHAR(50) NULL",
                'latitude' => "DECIMAL(10,8) NULL",
                'longitude' => "DECIMAL(11,8) NULL",
                'cronograma_data' => "LONGTEXT NULL",
                'pix_tipo_chave' => "VARCHAR(50) NULL",
                'pix_chave' => "VARCHAR(255) NULL",
                'dados_bancarios' => "TEXT NULL",
                'contextualizacao_json' => "LONGTEXT NULL",
                'equipe_json' => "LONGTEXT NULL",
                'cliente_uf' => "VARCHAR(2) NULL",
                'aprovacao_diretor_status' => "ENUM('nao_solicitado','pendente','aprovado','rejeitado') DEFAULT 'nao_solicitado'",
                'aprovado_diretor_por' => "INT NULL",
                'aprovado_diretor_em' => "DATETIME NULL",
                'justificativa_rejeicao' => "TEXT NULL",
                'enviado_para_diretor_em' => "DATETIME NULL",
                'enviado_diretor_por' => "INT NULL",

                // Assinatura da proposta (Contratada)
                'assinatura_tipo' => "VARCHAR(20) DEFAULT 'imagem'",
                'assinatura_elaborador_responsavel' => "TINYINT(1) DEFAULT 0",
                'assinatura_imagem' => "LONGTEXT NULL",
                'assinatura_certificado_nome' => "VARCHAR(255) NULL",
                'assinatura_certificado_cpf' => "VARCHAR(20) NULL",
                'assinatura_certificado_path' => "VARCHAR(255) NULL",
                'assinatura_certificado_senha' => "TEXT NULL",
                'assinatura_certificado_validade' => "DATETIME NULL",

                // Assinatura do Elaborador (Responsável Técnico)
                'assinatura_elaborador_tipo' => "VARCHAR(20) DEFAULT 'imagem'",
                'assinatura_elaborador_imagem' => "LONGTEXT NULL",
                'assinatura_elaborador_certificado_nome' => "VARCHAR(255) NULL",
                'assinatura_elaborador_certificado_cpf' => "VARCHAR(20) NULL",
                'assinatura_elaborador_certificado_path' => "VARCHAR(255) NULL",
                'assinatura_elaborador_certificado_senha' => "TEXT NULL",
                'assinatura_elaborador_certificado_validade' => "DATETIME NULL",

                'assinatura_data' => "DATETIME NULL",
            ];
            foreach ($columnsToAdd as $col => $def) {
                $stmt = $this->db->query("SHOW COLUMNS FROM orcamento_proposta LIKE '$col'");
                if ($stmt->rowCount() == 0) {
                    $this->db->exec("ALTER TABLE orcamento_proposta ADD COLUMN $col $def");
                }
            }

            // Adicionar data_revisao à tabela de histórico se não existir (migração)
            $stmt = $this->db->query("SHOW COLUMNS FROM orcamento_proposta_historico LIKE 'data_revisao'");
            if ($stmt->rowCount() == 0) {
                $this->db->exec("ALTER TABLE orcamento_proposta_historico ADD COLUMN data_revisao TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER usuario_id");
            }

            // Adicionar motivo_alteracao à tabela de histórico
            $stmt = $this->db->query("SHOW COLUMNS FROM orcamento_proposta_historico LIKE 'motivo_alteracao'");
            if ($stmt->rowCount() == 0) {
                $this->db->exec("ALTER TABLE orcamento_proposta_historico ADD COLUMN motivo_alteracao TEXT NULL AFTER data_revisao");
            }

            // Tabela para categorias dinâmicas de itens
            $this->db->exec("CREATE TABLE IF NOT EXISTS orcamento_item_categorias (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nome VARCHAR(100) NOT NULL UNIQUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

            // Tabela para unidades dinâmicas de itens
            $this->db->exec("CREATE TABLE IF NOT EXISTS orcamento_item_unidades (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nome VARCHAR(50) NOT NULL UNIQUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

            // Popula categorias padrão se a tabela estiver vazia
            $checkCat = $this->db->query("SELECT COUNT(*) FROM orcamento_item_categorias");
            if ($checkCat && $checkCat->fetchColumn() == 0) {
                $defaultCats = [
                    'Planejamento / Coordenação',
                    'Serviços de Campo',
                    'Custos Reembolsáveis',
                    'Elaboração de Peças Técnicas',
                    'Outros'
                ];
                $stmtIns = $this->db->prepare("INSERT INTO orcamento_item_categorias (nome) VALUES (?)");
                foreach ($defaultCats as $cat) {
                    $stmtIns->execute([$cat]);
                }
            }

            // Popula unidades padrão se a tabela estiver vazia
            $checkUn = $this->db->query("SELECT COUNT(*) FROM orcamento_item_unidades");
            if ($checkUn && $checkUn->fetchColumn() == 0) {
                $defaultUns = ['H/D', 'UN', 'Ticket', 'Diária', 'Litros', 'Peça', 'M²', 'KM', 'HR'];
                $stmtIns = $this->db->prepare("INSERT INTO orcamento_item_unidades (nome) VALUES (?)");
                foreach ($defaultUns as $un) {
                    $stmtIns->execute([$un]);
                }
            }

        } catch (PDOException $e) {
            error_log("Erro ao criar/atualizar tabelas de propostas: " . $e->getMessage());
        }
    }

    /**
     * Retorna o próximo número sequencial de proposta para o ano atual.
     * Formato solicitado: NNNN-YY-SIGLA (ex: 0001-26-ENV)
     */
    public function getNextProposalNumber(?int $clienteId): string
    {
        $yearShort = date('y');

        // Busca a sigla do cliente
        $sigla = 'SYS'; // Fallback caso não encontre sigla
        if ($clienteId) {
            $stmtCli = $this->db->prepare("SELECT sigla FROM clientes WHERE id = ?");
            $stmtCli->execute([$clienteId]);
            $res = $stmtCli->fetchColumn();
            if ($res) {
                // Pega as 3 primeiras letras em maiúsculo
                $sigla = substr(strtoupper(trim($res)), 0, 3);
            }
        }
        // Garante que a sigla tenha 3 caracteres (preenche com X se necessário)
        $sigla = str_pad($sigla, 3, 'X');

        // Padrão de busca para o ano atual e a sigla específica do cliente: ____-YY-SIGLA
        // Isso permite que a numeração seja sequencial e isolada por cliente.
        $pattern = "____-{$yearShort}-{$sigla}";

        // Busca o maior número sequencial para este cliente específico no ano corrente
        $sql = "SELECT MAX(CAST(SUBSTRING(numero_proposta, 1, 4) AS UNSIGNED)) 
                FROM orcamento_proposta 
                WHERE numero_proposta LIKE :pattern";
        
        $params = [':pattern' => $pattern];
        if ($clienteId) {
            $sql .= " AND cliente_id = :clienteId";
            $params[':clienteId'] = $clienteId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $lastNumber = (int)$stmt->fetchColumn();
        $nextSeq = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);

        return "{$nextSeq}-{$yearShort}-{$sigla}";
    }

    /**
     * Busca categorias de itens salvas no banco
     */
    public function getItemCategorias(bool $onlyNames = true): array
    {
        try {
            if ($onlyNames) {
                $stmt = $this->db->query("SELECT nome FROM orcamento_item_categorias ORDER BY nome ASC");
                return $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
            }
            $stmt = $this->db->query("SELECT id, nome FROM orcamento_item_categorias ORDER BY nome ASC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            return [];
        }
    }

    public function addItemCategoria(string $nome): bool
    {
        try {
            $stmt = $this->db->prepare("INSERT IGNORE INTO orcamento_item_categorias (nome) VALUES (?)");
            return $stmt->execute([$nome]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function updateItemCategoria(int $id, string $nome): bool
    {
        try {
            $stmt = $this->db->prepare("UPDATE orcamento_item_categorias SET nome = ? WHERE id = ?");
            return $stmt->execute([$nome, $id]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function deleteItemCategoria(int $id): bool
    {
        $this->lastError = '';
        try {
            // Busca o nome da categoria antes de deletar
            $stmtNome = $this->db->prepare("SELECT nome FROM orcamento_item_categorias WHERE id = ?");
            $stmtNome->execute([$id]);
            $nome = $stmtNome->fetchColumn();

            if ($nome) {
                // Verifica se o nome aparece em algum JSON de serviços
                $stmtCheck = $this->db->prepare("SELECT COUNT(*) FROM orcamento_proposta WHERE servicos_json LIKE ?");
                $stmtCheck->execute(['%"categoria":"' . $nome . '"%']);
                if ((int)$stmtCheck->fetchColumn() > 0) {
                    $this->lastError = "Não é possível excluir: esta categoria está sendo usada em orçamentos existentes.";
                    return false;
                }
            }

            $stmt = $this->db->prepare("DELETE FROM orcamento_item_categorias WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    /**
     * Busca unidades de medida salvas no banco
     */
    public function getItemUnidades(bool $onlyNames = true): array
    {
        try {
            if ($onlyNames) {
                $stmt = $this->db->query("SELECT nome FROM orcamento_item_unidades ORDER BY nome ASC");
                return $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
            }
            $stmt = $this->db->query("SELECT id, nome FROM orcamento_item_unidades ORDER BY nome ASC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            return [];
        }
    }

    public function addItemUnidade(string $nome): bool
    {
        try {
            $stmt = $this->db->prepare("INSERT IGNORE INTO orcamento_item_unidades (nome) VALUES (?)");
            return $stmt->execute([$nome]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function updateItemUnidade(int $id, string $nome): bool
    {
        try {
            $stmt = $this->db->prepare("UPDATE orcamento_item_unidades SET nome = ? WHERE id = ?");
            return $stmt->execute([$nome, $id]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function deleteItemUnidade(int $id): bool
    {
        $this->lastError = '';
        try {
            // Busca o nome da unidade antes de deletar
            $stmtNome = $this->db->prepare("SELECT nome FROM orcamento_item_unidades WHERE id = ?");
            $stmtNome->execute([$id]);
            $nome = $stmtNome->fetchColumn();

            if ($nome) {
                // Verifica se o nome aparece em algum JSON de serviços
                $stmtCheck = $this->db->prepare("SELECT COUNT(*) FROM orcamento_proposta WHERE servicos_json LIKE ?");
                $stmtCheck->execute(['%"unidade":"' . $nome . '"%']);
                if ((int)$stmtCheck->fetchColumn() > 0) {
                    $this->lastError = "Não é possível excluir: esta unidade está sendo usada em orçamentos existentes.";
                    return false;
                }
            }

            $stmt = $this->db->prepare("DELETE FROM orcamento_item_unidades WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    /**
     * Retorna o total de propostas cadastradas para a paginação.
     */
    public function getPropostasCount(): int
    {
        try {
            $stmt = $this->db->query("SELECT COUNT(*) FROM orcamento_proposta");
            return (int)$stmt->fetchColumn();
        } catch (\PDOException $e) {
            error_log('Erro ao contar propostas: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Conta o número de propostas com status 'Enviada'.
     * @return int
     */
    public function getCountPropostasPendentes(): int
    {
        try {
            $stmt = $this->db->query("SELECT COUNT(*) FROM orcamento_proposta WHERE status = 'Enviada'");
            return (int)$stmt->fetchColumn();
        } catch (\PDOException $e) {
            error_log('Erro ao contar propostas pendentes: ' . $e->getMessage());
            return 0;
        }
    }

    public function getPropostas(int $limit = 50, int $offset = 0): array
    {
        try {
            $sql = "SELECT p.*, p.id as id, p.nome_proposta as titulo, p.total_final as valor_total, p.responsavel_interno as responsavel_interno_id,
                           c.nome as cliente_nome, c.telefone as cliente_telefone, c.email as cliente_email, proj.nome as projeto_nome, u.nome as responsavel_nome,
                           du.nome as diretor_nome
                    FROM orcamento_proposta p
                    LEFT JOIN clientes c ON p.cliente_id = c.id
                    LEFT JOIN projetos proj ON p.projeto_id = proj.id
                    LEFT JOIN usuarios u ON p.responsavel_interno = u.id
                    LEFT JOIN usuarios du ON p.aprovado_diretor_por = du.id
                    ORDER BY p.id DESC LIMIT :limit OFFSET :offset";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log('Erro ao buscar propostas: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Busca propostas com dados básicos apenas (otimizado para lista)
     */
    public function getPropostasBasic(int $limit = 50, int $offset = 0): array
    {
        try {
            $sql = "SELECT p.id, p.numero_proposta, p.nome_proposta, p.status, p.total_final,
                           p.created_at, p.updated_at,
                           c.nome as cliente_nome, c.telefone as cliente_telefone,
                           proj.nome as projeto_nome
                    FROM orcamento_proposta p
                    LEFT JOIN clientes c ON p.cliente_id = c.id
                    LEFT JOIN projetos proj ON p.projeto_id = proj.id
                    ORDER BY p.id DESC LIMIT :limit OFFSET :offset";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log('Erro ao buscar propostas básicas: ' . $e->getMessage());
            return [];
        }
    }

    public function getPropostaById(int $id): ?array
    {
        try {
            $sql = "SELECT p.*, p.id as id, p.responsavel_interno as responsavel_interno_id, ct.numero_contrato as contrato_numero,
                           COALESCE(p.cliente_sigla, c.sigla) as cliente_sigla, c.nome as cliente_nome, c.nome_fantasia as cliente_nome_fantasia, c.contato_principal as cliente_contato, 
                           COALESCE(p.cliente_documento, c.cnpj_cpf) as cliente_documento,
                           COALESCE(p.cliente_telefone, c.telefone) as cliente_telefone,
                           COALESCE(p.cliente_endereco, c.endereco) as cliente_endereco,
                           p.cliente_logradouro, p.cliente_numero, p.cliente_complemento, p.cliente_bairro, p.cliente_municipio, p.cliente_uf,
                           proj.nome as projeto_nome, u.nome as responsavel_nome,
                            du.nome as diretor_nome,
                            eu.nome as enviado_diretor_nome
                     FROM orcamento_proposta p
                     LEFT JOIN clientes c ON p.cliente_id = c.id
                     LEFT JOIN contratos ct ON p.contrato_id = ct.id
                     LEFT JOIN projetos proj ON p.projeto_id = proj.id
                     LEFT JOIN usuarios u ON p.responsavel_interno = u.id
                     LEFT JOIN usuarios du ON p.aprovado_diretor_por = du.id
                     LEFT JOIN usuarios eu ON p.enviado_diretor_por = eu.id
                    WHERE p.id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            return $res ?: null;
        } catch (\PDOException $e) {
            error_log('Erro ao buscar proposta por id: ' . $e->getMessage());
            return null;
        }
    }

    public function salvarProposta(array $dados): bool
    {
        $maxRetries = 3;
        $attempt = 0;

        while ($attempt < $maxRetries) {
            try {
                // Extrai o usuário logado para o histórico
                $usuario_id = $_SESSION['user_id'] ?? null;
                $motivo_alteracao = $dados['motivo_alteracao'] ?? 'Alteração via formulário';

                $this->db->beginTransaction();

                $id = !empty($dados['id']) ? (int)$dados['id'] : null;
                
                // Processa coordenadas (Suporte a GMS ou Decimal)
                $lat = $this->parseCoordinate($dados['latitude'] ?? null);
                $lng = $this->parseCoordinate($dados['longitude'] ?? null);
                $propostaAtual = null;

                // Lógica antecipada para identificação do cliente_id (necessária para gerar o número da proposta)
                $clienteId = !empty($dados['cliente_id']) ? (int)$dados['cliente_id'] : (!empty($dados['cliente_id_scratch']) ? (int)$dados['cliente_id_scratch'] : null);
                if (($dados['creation_type'] ?? '') === 'from_project' && !empty($dados['projeto_id'])) {
                    $projetoModel = new \App\Models\ProjetosModel();
                    $projeto = $projetoModel->getProjetoById((int)$dados['projeto_id']);
                    $clienteId = $projeto['cliente_id'] ?? $clienteId;
                }

                if ($id) {
                    // Antes de atualizar, salva o estado atual no histórico
                    $propostaAtual = $this->getPropostaById($id);
                    if ($propostaAtual) {
                        $this->salvarHistorico($propostaAtual, $usuario_id, $motivo_alteracao);
                    }

                    $sql = "UPDATE orcamento_proposta SET 
                                numero_proposta = :numero_proposta, tipo_criacao = :tipo_criacao, projeto_id = :projeto_id, cliente_id = :cliente_id, nome_proposta = :nome_proposta, 
                                descricao = :descricao, objetivo = :objetivo, data_proposta = :data_proposta, validade = :validade, responsavel_interno = :responsavel_interno,
                                contrato_id = :contrato_id, servicos_json = :servicos_json, materiais_json = :materiais_json, custos_extras_json = :custos_extras_json,
                                impostos_valor = :impostos_valor, descontos_valor = :descontos_valor,
                                desconto_tipo = :desconto_tipo,
                                cliente_telefone = :cliente_telefone, cliente_sigla = :cliente_sigla, representante = :representante, email_cliente = :email_cliente, cliente_documento = :cliente_documento,
                                cliente_logradouro = :cliente_logradouro, cliente_numero = :cliente_numero, cliente_complemento = :cliente_complemento,
                                cliente_bairro = :cliente_bairro, cliente_municipio = :cliente_municipio, cliente_uf = :cliente_uf, cliente_endereco = :cliente_endereco,
                                condicao_pagamento = :condicao_pagamento, forma_pagamento = :forma_pagamento, prazo_execucao = :prazo_execucao, garantias = :garantias,
                                total_servicos = :total_servicos, total_materiais = :total_materiais, total_final = :total_final,
                                status = :status, anexos = :anexos,
                                token_aprovacao = :token_aprovacao, token_validade = :token_validade,
                                versao_documento = :versao_documento,
                                pix_tipo_chave = :pix_tipo_chave,
                                pix_chave = :pix_chave, dados_bancarios = :dados_bancarios,
                                cronograma_data = :cronograma_data,
                                contextualizacao_json = :contextualizacao_json,
                                equipe_json = :equipe_json,
                                latitude = :latitude, longitude = :longitude,
                                assinatura_tipo = :assinatura_tipo,
                                assinatura_elaborador_responsavel = :assinatura_elaborador_responsavel,
                                assinatura_imagem = :assinatura_imagem,
                                assinatura_certificado_nome = :assinatura_certificado_nome,
                                assinatura_certificado_cpf = :assinatura_certificado_cpf,
                                assinatura_certificado_path = :assinatura_certificado_path,
                                assinatura_certificado_senha = :assinatura_certificado_senha,
                                assinatura_certificado_validade = :assinatura_certificado_validade,
                                assinatura_elaborador_tipo = :assinatura_elaborador_tipo,
                                assinatura_elaborador_imagem = :assinatura_elaborador_imagem,
                                assinatura_elaborador_certificado_nome = :assinatura_elaborador_certificado_nome,
                                assinatura_elaborador_certificado_cpf = :assinatura_elaborador_certificado_cpf,
                                assinatura_elaborador_certificado_path = :assinatura_elaborador_certificado_path,
                                assinatura_elaborador_certificado_senha = :assinatura_elaborador_certificado_senha,
                                assinatura_elaborador_certificado_validade = :assinatura_elaborador_certificado_validade
                            WHERE id = :id";
                    $stmt = $this->db->prepare($sql);
                    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
                    $stmt->bindValue(':numero_proposta', $dados['numero_proposta']);
                } else {
                    // Para nova proposta, gera o número sequencial
                    // Em caso de retry por colisão (erro 1062), o attempt > 0 forçará a geração de um novo número
                    if (empty($dados['numero_proposta']) || $attempt > 0) {
                        $dados['numero_proposta'] = $this->getNextProposalNumber($clienteId);
                    }

                    $sql = "INSERT INTO orcamento_proposta (
                                numero_proposta, tipo_criacao, projeto_id, cliente_id, contrato_id, nome_proposta, descricao, objetivo, data_proposta, validade, 
                                responsavel_interno, servicos_json, materiais_json, custos_extras_json,
                                impostos_valor, descontos_valor, desconto_tipo, cliente_telefone, cliente_sigla, representante, email_cliente, cliente_documento,
                                cliente_logradouro, cliente_numero, cliente_complemento,
                                cliente_bairro, cliente_municipio, cliente_uf, cliente_endereco,
                                condicao_pagamento, forma_pagamento, prazo_execucao, garantias,
                                total_servicos, total_materiais, total_final, status, anexos, cronograma_data,
                                contextualizacao_json, equipe_json,
                                token_aprovacao, token_validade,
                                versao_documento, latitude, longitude,
                                pix_tipo_chave, pix_chave, dados_bancarios,
                                assinatura_tipo, assinatura_elaborador_responsavel,
                                assinatura_imagem, assinatura_certificado_nome, assinatura_certificado_cpf,
                                assinatura_certificado_path, assinatura_certificado_senha, assinatura_certificado_validade,
                                assinatura_elaborador_tipo, assinatura_elaborador_imagem,
                                assinatura_elaborador_certificado_nome, assinatura_elaborador_certificado_cpf,
                                assinatura_elaborador_certificado_path, assinatura_elaborador_certificado_senha,
                                assinatura_elaborador_certificado_validade
                            ) VALUES (
                                :numero_proposta, :tipo_criacao, :projeto_id, :cliente_id, :contrato_id, :nome_proposta, :descricao, :objetivo, :data_proposta, :validade, 
                                :responsavel_interno, :servicos_json, :materiais_json, :custos_extras_json,
                                :impostos_valor, :descontos_valor, :desconto_tipo, :cliente_telefone, :cliente_sigla, :representante, :email_cliente, :cliente_documento,
                                :cliente_logradouro, :cliente_numero, :cliente_complemento,
                                :cliente_bairro, :cliente_municipio, :cliente_uf, :cliente_endereco,
                                :condicao_pagamento, :forma_pagamento, :prazo_execucao, :garantias,
                                :total_servicos, :total_materiais, :total_final, :status, :anexos, :cronograma_data,
                                :contextualizacao_json, :equipe_json,
                                :token_aprovacao, :token_validade, :versao_documento, :latitude, :longitude,
                                :pix_tipo_chave, :pix_chave, :dados_bancarios,
                                :assinatura_tipo, :assinatura_elaborador_responsavel,
                                :assinatura_imagem, :assinatura_certificado_nome, :assinatura_certificado_cpf,
                                :assinatura_certificado_path, :assinatura_certificado_senha, :assinatura_certificado_validade,
                                :assinatura_elaborador_tipo, :assinatura_elaborador_imagem,
                                :assinatura_elaborador_certificado_nome, :assinatura_elaborador_certificado_cpf,
                                :assinatura_elaborador_certificado_path, :assinatura_elaborador_certificado_senha,
                                :assinatura_elaborador_certificado_validade
                            )";
                    $stmt = $this->db->prepare($sql);
                    $stmt->bindValue(':numero_proposta', $dados['numero_proposta']);
                }

                $stmt->bindValue(':tipo_criacao', ($dados['creation_type'] ?? '') === 'from_project' ? 'vinculado_projeto' : 'from_scratch');
                $stmt->bindValue(':projeto_id', !empty($dados['projeto_id']) ? (int)$dados['projeto_id'] : null, PDO::PARAM_INT);
                $stmt->bindValue(':cliente_id', $clienteId, $clienteId ? PDO::PARAM_INT : PDO::PARAM_NULL);
                $stmt->bindValue(':contrato_id', !empty($dados['contrato_id']) ? (int)$dados['contrato_id'] : null, PDO::PARAM_INT);

                $stmt->bindValue(':nome_proposta', $dados['titulo'] ?? '');
                $stmt->bindValue(':descricao', $dados['descricao_geral'] ?? null);
                $stmt->bindValue(':objetivo', $dados['objetivo'] ?? null);
                $stmt->bindValue(':data_proposta', !empty($dados['data_proposta']) ? $dados['data_proposta'] : null);
                $stmt->bindValue(':validade', $dados['validade_proposta'] ?? null);
                $stmt->bindValue(':responsavel_interno', !empty($dados['responsavel_interno_id']) ? (int)$dados['responsavel_interno_id'] : null, PDO::PARAM_INT);

                // Novos campos de detalhe de custos
                $stmt->bindValue(':servicos_json', json_encode($dados['servicos'] ?? []));
                $stmt->bindValue(':materiais_json', json_encode($dados['materiais'] ?? []));
                $stmt->bindValue(':custos_extras_json', json_encode($dados['custos_extras'] ?? []));
                $stmt->bindValue(':impostos_valor', $dados['impostos_valor'] ?? 0.00);
                $stmt->bindValue(':descontos_valor', $dados['descontos_valor'] ?? 0.00);
                $stmt->bindValue(':desconto_tipo', $dados['desconto_tipo'] ?? 'percentual');
                $stmt->bindValue(':cliente_telefone', $dados['cliente_telefone'] ?? null);
                $stmt->bindValue(':cliente_sigla', $dados['cliente_sigla'] ?? null);
                $stmt->bindValue(':representante', $dados['representante'] ?? null);
                $stmt->bindValue(':email_cliente', $dados['email_cliente'] ?? null);
                $stmt->bindValue(':cliente_documento', $dados['cliente_documento'] ?? null);
                $stmt->bindValue(':cliente_logradouro', $dados['cliente_logradouro'] ?? null);
                $stmt->bindValue(':cliente_numero', $dados['cliente_numero'] ?? null);
                $stmt->bindValue(':cliente_complemento', $dados['cliente_complemento'] ?? null);
                $stmt->bindValue(':cliente_bairro', $dados['cliente_bairro'] ?? null);
                $stmt->bindValue(':cliente_municipio', $dados['cliente_municipio'] ?? null);
                $stmt->bindValue(':cliente_uf', $dados['cliente_uf'] ?? null);
                $stmt->bindValue(':cliente_endereco', $dados['cliente_endereco'] ?? null);
                $stmt->bindValue(':condicao_pagamento', $dados['condicao_pagamento'] ?? null);
                $stmt->bindValue(':forma_pagamento', $dados['forma_pagamento'] ?? null);
                $stmt->bindValue(':prazo_execucao', $dados['prazo_execucao'] ?? null);
                $stmt->bindValue(':garantias', $dados['garantias'] ?? null);

                $stmt->bindValue(':pix_tipo_chave', $dados['pix_tipo_chave'] ?? null);
                $stmt->bindValue(':pix_chave', $dados['pix_chave'] ?? null);
                $stmt->bindValue(':dados_bancarios', $dados['dados_bancarios'] ?? null);

                $stmt->bindValue(':total_servicos', $dados['total_servicos'] ?? 0.00);
                $stmt->bindValue(':total_materiais', $dados['total_materiais'] ?? 0.00);

                $stmt->bindValue(':cronograma_data', $dados['cronograma_data'] ?? null);
                $stmt->bindValue(':contextualizacao_json', $dados['contextualizacao_json'] ?? null);
                $stmt->bindValue(':equipe_json', $dados['equipe_json'] ?? null);
                $stmt->bindValue(':latitude', $lat);
                $stmt->bindValue(':longitude', $lng);

                // Converte o valor total formatado para float
                $totalFinal = $dados['valor_total'] ?? 0.00;
                $stmt->bindValue(':total_final', $totalFinal);

                $stmt->bindValue(':status', !empty($dados['status']) ? $dados['status'] : 'Rascunho'); // Status pode ser alterado por aprovação
                $stmt->bindValue(':anexos', null); // Placeholder para futura implementação de anexos
                
                // Token de aprovação (gerado se status for 'Enviada' e não houver token ou se o existente expirou)
                $tokenAusente = empty($propostaAtual['token_aprovacao']);
                $tokenExpirado = !$tokenAusente && !empty($propostaAtual['token_validade']) && strtotime($propostaAtual['token_validade']) < time();
                $tokenAprovacao = ($dados['status'] === 'Enviada' && ($tokenAusente || $tokenExpirado)) ? $this->generateApprovalToken() : ($propostaAtual['token_aprovacao'] ?? null);
                $tokenValidade = ($tokenAprovacao && ($tokenAusente || $tokenExpirado)) ? date('Y-m-d H:i:s', strtotime('+7 days')) : ($propostaAtual['token_validade'] ?? null);
                $stmt->bindValue(':token_aprovacao', $tokenAprovacao);
                $stmt->bindValue(':token_validade', $tokenValidade);
                $stmt->bindValue(':versao_documento', $dados['versao_documento'] ?? null);
                $stmt->bindValue(':assinatura_tipo', $dados['assinatura_tipo'] ?? 'imagem');
                $stmt->bindValue(':assinatura_elaborador_responsavel', !empty($dados['assinatura_elaborador_responsavel']) ? 1 : 0, PDO::PARAM_INT);
                $stmt->bindValue(':assinatura_imagem', $dados['assinatura_imagem'] ?? null);
                $stmt->bindValue(':assinatura_certificado_nome', $dados['assinatura_certificado_nome'] ?? null);
                $stmt->bindValue(':assinatura_certificado_cpf', $dados['assinatura_certificado_cpf'] ?? null);
                $stmt->bindValue(':assinatura_certificado_path', $dados['assinatura_certificado_path'] ?? null);
                $stmt->bindValue(':assinatura_certificado_senha', $dados['assinatura_certificado_senha'] ?? null);
                $stmt->bindValue(':assinatura_certificado_validade', $dados['assinatura_certificado_validade'] ?? null);
                $stmt->bindValue(':assinatura_elaborador_tipo', $dados['assinatura_elaborador_tipo'] ?? 'imagem');
                $stmt->bindValue(':assinatura_elaborador_imagem', $dados['assinatura_elaborador_imagem'] ?? null);
                $stmt->bindValue(':assinatura_elaborador_certificado_nome', $dados['assinatura_elaborador_certificado_nome'] ?? null);
                $stmt->bindValue(':assinatura_elaborador_certificado_cpf', $dados['assinatura_elaborador_certificado_cpf'] ?? null);
                $stmt->bindValue(':assinatura_elaborador_certificado_path', $dados['assinatura_elaborador_certificado_path'] ?? null);
                $stmt->bindValue(':assinatura_elaborador_certificado_senha', $dados['assinatura_elaborador_certificado_senha'] ?? null);
                $stmt->bindValue(':assinatura_elaborador_certificado_validade', $dados['assinatura_elaborador_certificado_validade'] ?? null);

                $success = $stmt->execute();

                if ($success) {
                    // Sincronização: Se a proposta está aprovada e vinculada a um projeto,
                    // atualiza o orçamento previsto do projeto e sincroniza os itens.
                    $novoStatus = $dados['status'] ?? '';
                    $oldStatus = $propostaAtual['status'] ?? '';
                    $projetoId = $id ? ($propostaAtual['projeto_id'] ?? null) : (!empty($dados['projeto_id']) ? (int)$dados['projeto_id'] : null);

                    $estaAprovada = ($novoStatus === 'Aprovada' || $oldStatus === 'Aprovada');
                    if ($projetoId && $estaAprovada) {
                        $sqlSync = "UPDATE projetos SET orcamento = :novo_valor WHERE id = :projeto_id";
                        $stmtSync = $this->db->prepare($sqlSync);
                        $stmtSync->bindValue(':novo_valor', $totalFinal);
                        $stmtSync->bindValue(':projeto_id', $projetoId, PDO::PARAM_INT);
                        $stmtSync->execute();

                        $projetosModel = new \App\Models\ProjetosModel();
                        $projetosModel->syncOrcamentoFromProposta(
                            $projetoId,
                            $dados['servicos'] ?? [],
                            $dados['materiais'] ?? [],
                            $dados['custos_extras'] ?? [],
                            $id ?: 0,
                            $dados['data_proposta'] ?? null
                        );
                    }

                    $this->db->commit();
                    return true;
                } else {
                    $this->db->rollBack();
                    return false;
                }
            } catch (\PDOException $e) {
                if ($this->db->inTransaction()) {
                    $this->db->rollBack();
                }

                // Se o erro for 1062 (Duplicate entry) e for um registro novo, tentamos novamente.
                if ($e->errorInfo[1] == 1062 && empty($dados['id'])) {
                    $attempt++;
                    if ($attempt < $maxRetries) {
                        usleep(100000); // Pausa de 100ms
                        continue;
                    }
                }
                
                error_log('Erro ao salvar proposta: ' . $e->getMessage());
                return false;
            }
        }
        return false;
    }

    /**
     * Converte coordenadas de GMS (Graus, Minutos, Segundos) para Graus Decimais.
     * Robusto para aceitar diversos formatos de entrada do usuário.
     */
    private function parseCoordinate($coord): ?float
    {
        if ($coord === null || $coord === '') return null;
        $coord = trim((string)$coord);

        // Se já for decimal puro
        if (is_numeric(str_replace(',', '.', $coord))) {
            return (float)str_replace(',', '.', $coord);
        }

        // Regex para formatos complexos (ex: 12°34'56"S)
        $pattern = '/^([+-]?\d+)[°º\s]+(?:(\d+)\'[\s]*)?(?:([\d.]+)"[\s]*)?([NSEW])?$/i';

        if (preg_match($pattern, $coord, $matches)) {
            $degrees = (float)$matches[1];
            $minutes = isset($matches[2]) ? (float)$matches[2] : 0;
            $seconds = isset($matches[3]) ? (float)$matches[3] : 0;
            $direction = isset($matches[4]) ? strtoupper($matches[4]) : '';

            $decimal = abs($degrees) + ($minutes / 60) + ($seconds / 3600);
            
            if ($direction === 'S' || $direction === 'W' || $degrees < 0) {
                $decimal *= -1;
            }
            return (float)$decimal;
        }

        return null;
    }

    /**
     * Verifica se uma proposta com dados semelhantes foi criada recentemente.
     * Isso ajuda a prevenir duplicações acidentais por duplo clique ou reenvio.
     *
     * @param array $dados Os dados da proposta a serem verificados.
     * @param int $tempoLimiteSegundos Tempo limite em segundos para considerar uma duplicação recente.
     * @return bool True se uma duplicação recente for encontrada, false caso contrário.
     */
    public function verificarDuplicidadeRecente(array $dados, int $tempoLimiteSegundos = 10): bool
    {
        try {
            $sql = "SELECT COUNT(*) FROM orcamento_proposta
                    WHERE nome_proposta = :nome_proposta
                    AND cliente_id = :cliente_id
                    AND total_final = :total_final
                    AND created_at >= DATE_SUB(NOW(), INTERVAL :tempo_limite SECOND)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':nome_proposta', $dados['titulo']);
            $stmt->bindValue(':cliente_id', $dados['cliente_id']);
            $stmt->bindValue(':total_final', $dados['valor_total']);
            $stmt->bindValue(':tempo_limite', $tempoLimiteSegundos, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Erro ao verificar duplicidade recente de proposta: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Salva um snapshot da proposta na tabela de histórico.
     * @param array $proposta Os dados da proposta a serem versionados.
     * @param int|null $usuario_id ID do usuário que fez a alteração.
     * @param string $motivo Motivo da alteração.
     * @return bool
     */
    private function salvarHistorico(array $proposta, ?int $usuario_id, string $motivo): bool
    {
        try {
            error_log("DEBUG: Iniciando salvarHistorico - Proposta ID: " . $proposta['id'] . ", Usuario: $usuario_id");

            $stmtVer = $this->db->prepare("SELECT MAX(versao) FROM orcamento_proposta_historico WHERE proposta_id = ?");
            $stmtVer->execute([$proposta['id']]);
            $ultimaVersao = (int)$stmtVer->fetchColumn();
            $novaVersao = $ultimaVersao + 1;

            error_log("DEBUG: Nova versão do histórico: $novaVersao");

            $sql = "INSERT INTO orcamento_proposta_historico (proposta_id, versao, usuario_id, motivo_alteracao, dados_proposta_json) VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);

            $dadosJson = json_encode($proposta);
            if ($dadosJson === false) {
                error_log("DEBUG: Erro ao codificar JSON dos dados da proposta");
                return false;
            }

            error_log("DEBUG: JSON dos dados codificado com sucesso");

            $result = $stmt->execute([$proposta['id'], $novaVersao, $usuario_id, $motivo, $dadosJson]);
            error_log("DEBUG: INSERT histórico executado - Sucesso: " . ($result ? 'SIM' : 'NAO'));

            return $result;
        } catch (\PDOException $e) {
            error_log('Erro ao salvar histórico da proposta: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            // Não lança exception, apenas retorna false
            return false;
        }
    }

    /**
     * Busca o histórico de versões de uma proposta.
     * @param int $proposta_id
     * @return array
     */
    public function limparHistorico(int $propostaId): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM orcamento_proposta_historico WHERE proposta_id = ?");
            return $stmt->execute([$propostaId]);
        } catch (\PDOException $e) {
            error_log('Erro ao limpar histórico da proposta: ' . $e->getMessage());
            return false;
        }
    }

    public function getHistoricoByPropostaId(int $proposta_id): array
    {
        $stmt = $this->db->prepare("SELECT h.*, u.nome as usuario_nome FROM orcamento_proposta_historico h LEFT JOIN usuarios u ON h.usuario_id = u.id WHERE h.proposta_id = ? ORDER BY h.versao DESC");
        $stmt->execute([$proposta_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Busca um registro de histórico específico pelo seu ID.
     * @param int $historico_id
     * @return array|null
     */
    public function getHistoricoById(int $historico_id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM orcamento_proposta_historico WHERE id = ?");
        $stmt->execute([$historico_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Busca um registro de histórico por ID da proposta e número da versão.
     * @param int $proposta_id
     * @param int $versao
     * @return array|null
     */
    public function getHistoricoByPropostaIdEVersao(int $proposta_id, int $versao): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM orcamento_proposta_historico WHERE proposta_id = ? AND versao = ?");
        $stmt->execute([$proposta_id, $versao]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function excluirProposta(int $id): bool
    {
        $this->lastError = ''; // Reseta o erro

        if ($id <= 0) {
            $this->lastError = 'ID inválido fornecido para exclusão.';
            return false;
        }

        try {
            // Verificar se a proposta já foi aprovada
            $stmtCheck = $this->db->prepare("SELECT status, contrato_id FROM orcamento_proposta WHERE id = ?");
            $stmtCheck->execute([$id]);
            $current = $stmtCheck->fetch(PDO::FETCH_ASSOC);

            if ($current && $current['status'] === 'Aprovada') {
                $this->lastError = 'Não é possível excluir uma proposta que já foi aprovada.';
                return false;
            }

            if (!empty($current['contrato_id'])) {
                $this->lastError = 'Não é possível excluir a proposta. Existe um contrato vinculado a ela.';
                return false;
            }

            // 1. Verificar se existem projetos vinculados a esta proposta
            $stmtCheckProjetos = $this->db->prepare("SELECT COUNT(*) FROM projetos WHERE orcamento_id = ?");
            $stmtCheckProjetos->execute([$id]);
            if ($stmtCheckProjetos->fetchColumn() > 0) {
                $this->lastError = 'Não é possível excluir a proposta. Existem projetos vinculados a ela.';
                return false;
            }

            // Inicia a transação para garantir que a exclusão seja atômica
            $this->db->beginTransaction();

            // 2. Remover o histórico de revisões vinculado à proposta
            $stmtHist = $this->db->prepare("DELETE FROM orcamento_proposta_historico WHERE proposta_id = ?");
            $stmtHist->execute([$id]);

            // 3. Remover o registro principal da proposta
            $stmt = $this->db->prepare("DELETE FROM orcamento_proposta WHERE id = :id LIMIT 1");
            $success = $stmt->execute([':id' => $id]);
            $this->db->commit();
            return $success;
        } catch (\PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log('Erro ao excluir proposta: ' . $e->getMessage());
            $this->lastError = 'Erro interno ao excluir a proposta: ' . $e->getMessage();
            return false;
        }
    }

    /**
     * Gera um token único para aprovação da proposta.
     * @return string
     */
    public function generateApprovalToken(): string
    {
        return bin2hex(random_bytes(32)); // 64 caracteres hexadecimais
    }

    /**
     * Busca uma proposta pelo token de aprovação.
     * @param string $token
     * @return array|null
     */
    public function getPropostaByToken(string $token): ?array
    {
        try {
            $sql = "SELECT p.*, p.id as id, p.garantias, ct.numero_contrato as contrato_numero,
                           COALESCE(p.cliente_sigla, c.sigla) as cliente_sigla, c.nome as cliente_nome, c.nome_fantasia as cliente_nome_fantasia, c.contato_principal as cliente_contato, 
                           COALESCE(p.cliente_documento, c.cnpj_cpf) as cliente_documento,
                           COALESCE(p.cliente_telefone, c.telefone) as cliente_telefone,
                           COALESCE(p.cliente_endereco, c.endereco) as cliente_endereco,
                           p.cliente_logradouro, p.cliente_numero, p.cliente_complemento, p.cliente_bairro, p.cliente_municipio, p.cliente_uf,
                           proj.nome as projeto_nome, u.nome as responsavel_nome
                    FROM orcamento_proposta p
                    LEFT JOIN clientes c ON p.cliente_id = c.id
                    LEFT JOIN contratos ct ON p.contrato_id = ct.id
                    LEFT JOIN projetos proj ON p.projeto_id = proj.id
                    LEFT JOIN usuarios u ON p.responsavel_interno = u.id
                    WHERE p.token_aprovacao = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$token]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (\PDOException $e) {
            error_log('Erro ao buscar proposta por token: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Aprova uma proposta pelo token, atualizando seu status e invalidando o token.
     * @param int $propostaId
     * @param string $motivo Motivo da aprovação (ex: 'Aprovada pelo cliente via link').
     * @return bool
     */
    public function approveProposalByToken(int $propostaId, string $motivo, string $aceiteIp = '', string $aceiteNome = ''): bool
    {
        try {
            $this->db->beginTransaction();
            $propostaAtual = $this->getPropostaById($propostaId);
            if ($propostaAtual) {
                $this->salvarHistorico($propostaAtual, null, $motivo); // Usuário nulo para aprovação externa
            }

            // Garante que a proposta tenha um número oficial antes de converter em contrato/projeto
            $numeroProposta = $propostaAtual['numero_proposta'] ?? $this->getNextProposalNumber($propostaAtual['cliente_id']);

            $stmt = $this->db->prepare("UPDATE orcamento_proposta SET status = 'Aprovada', numero_proposta = ?, aprovado_em = NOW(), aceite_em = NOW(), aceite_ip = ?, aceite_nome = ? WHERE id = ?");
            $success = $stmt->execute([$numeroProposta, $aceiteIp, $aceiteNome ?: null, $propostaId]);

            $projetoIdCriado = null;
            if ($success && $propostaAtual && empty($propostaAtual['projeto_id'])) {
                $propostaAtual['numero_proposta'] = $numeroProposta;
                $projetoIdCriado = $this->criarProjetoParaPropostaAprovada($propostaAtual);
            } elseif ($success && !empty($propostaAtual['projeto_id'])) {
                $projetoIdCriado = $propostaAtual['projeto_id'];
            }

            $this->db->commit();

            // Sincroniza os itens da proposta para o orçamento do projeto (fora da transação)
            if ($success && !empty($projetoIdCriado)) {
                try {
                    $projetosModel = new \App\Models\ProjetosModel();
                    $servicos = json_decode($propostaAtual['servicos_json'] ?? '[]', true) ?: [];
                    $materiais = json_decode($propostaAtual['materiais_json'] ?? '[]', true) ?: [];
                    $custosExtras = json_decode($propostaAtual['custos_extras_json'] ?? '[]', true) ?: [];
                    $projetosModel->syncOrcamentoFromProposta(
                        $projetoIdCriado,
                        $servicos,
                        $materiais,
                        $custosExtras,
                        $propostaId,
                        $propostaAtual['data_proposta'] ?? null
                    );
                } catch (\Exception $e) {
                    error_log("Aviso: syncOrcamentoFromProposta falhou para proposta #{$propostaId}: " . $e->getMessage());
                }
            }
            error_log("DEBUG: Proposta #{$propostaId} aprovada via link.");

            // Geração de contrato fora da transação — erro aqui não desfaz a aprovação
            if ($success) {
                try {
                    error_log("DEBUG: Iniciando criação de contrato para proposta #{$propostaId}.");
                    $contratosModel = new ContratosModel();
                    $propostaFinal = $this->getPropostaById($propostaId);

                    // Verifica se o cliente já possui contratos ativos — se sim, apenas loga aviso e não cria automático
                    $clienteId = $propostaFinal['cliente_id'] ?? null;
                    $pularCriacao = false;
                    if (!empty($clienteId)) {
                        $contratosExistentes = $contratosModel->getContratosByClienteId((int)$clienteId);
                        if (count($contratosExistentes) > 0) {
                            error_log("Aviso: Cliente #{$clienteId} já possui " . count($contratosExistentes) . " contrato(s). Contrato automático NÃO gerado para proposta #{$propostaId}. Vincule manualmente na edição da proposta.");
                            $pularCriacao = true;
                        }
                    }

                    if (!$pularCriacao) {
                        $contratoId = $contratosModel->criarContratoDeProposta($propostaId, $propostaFinal);
                        if ($contratoId && is_numeric($contratoId)) {
                            // Atualiza a proposta com o ID do contrato gerado
                            $this->db->prepare("UPDATE orcamento_proposta SET contrato_id = ? WHERE id = ?")->execute([$contratoId, $propostaId]);
                            error_log("DEBUG: Contrato automático #{$contratoId} criado e vinculado à proposta #{$propostaId}.");
                        } else {
                            error_log("Aviso: Contrato automático não gerado para proposta #{$propostaId} (criarContratoDeProposta retornou false).");
                        }
                    }
                } catch (\Exception $ce) {
                    error_log('Aviso: contrato automático não gerado para proposta ' . $propostaId . ': ' . $ce->getMessage());
                }
            }

            return $success;
        } catch (\PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log('Erro ao aprovar proposta por token: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Atualiza o status de uma proposta e registra no histórico.
     * @param int $propostaId
     * @param string $newStatus
     * @param string $motivo Motivo da alteração.
     * @param int|null $usuario_id ID do usuário que fez a alteração.
     * @param bool $criarContrato Define se deve gerar o contrato automático (se status for Aprovada).
     * @return bool
     */
    public function updateProposalStatus(int $propostaId, string $newStatus, string $motivo, ?int $usuario_id = null, bool $criarContrato = true): bool
    {
        try {
            error_log("DEBUG: Iniciando updateProposalStatus - ID: $propostaId, Status: $newStatus, Usuario: $usuario_id");

            $propostaAtual = $this->getPropostaById($propostaId);
            if (!$propostaAtual) {
                error_log("DEBUG: Proposta não encontrada - ID: $propostaId");
                return false;
            }

            error_log("DEBUG: Proposta encontrada - Status atual: " . $propostaAtual['status']);

            // Tentar salvar histórico, mas não falhar se não conseguir
            try {
                $this->salvarHistorico($propostaAtual, $usuario_id, $motivo);
                error_log("DEBUG: Histórico salvo com sucesso");
            } catch (\Exception $e) {
                error_log("DEBUG: Erro ao salvar histórico (continuando): " . $e->getMessage());
            }

            $sql = "UPDATE orcamento_proposta SET status = :status";

            // Se a proposta for marcada como 'Aprovada', registra data de aprovação e quem aprovou
            if ($newStatus === 'Aprovada') {
                $sql .= ", aprovado_em = NOW()";
                if ($usuario_id) {
                    $sql .= ", aprovado_por = :aprovado_por";
                    error_log("DEBUG: Registrando data e responsável da aprovação");
                } else {
                    error_log("DEBUG: Registrando apenas data de aprovação (sem usuário)");
                }
            }

            // Se a proposta for marcada como 'Enviada', gera/renova token se não existir ou se expirou
            $tokenExpirado = !empty($propostaAtual['token_validade']) && strtotime($propostaAtual['token_validade']) < time();
            if ($newStatus === 'Enviada' && (empty($propostaAtual['token_aprovacao']) || $tokenExpirado)) {
                $sql .= ", token_aprovacao = :token, token_validade = :validade";
                error_log("DEBUG: Gerando/renovando token para proposta enviada");
            }

            $sql .= " WHERE id = :id";
            error_log("DEBUG: SQL gerado: $sql");

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':status', $newStatus);
            
            // Obter nome do usuário para preencher aprovado_por se aprovando
            if ($newStatus === 'Aprovada' && $usuario_id) {
                try {
                    $stmtUser = $this->db->prepare("SELECT nome FROM usuarios WHERE id = ?");
                    $stmtUser->execute([$usuario_id]);
                    $user = $stmtUser->fetch(PDO::FETCH_ASSOC);
                    $aprovado_por = $user ? $user['nome'] : "Usuário #{$usuario_id}";
                    $stmt->bindValue(':aprovado_por', $aprovado_por);
                    error_log("DEBUG: Aprovado por: $aprovado_por");
                } catch (\Exception $e) {
                    error_log("DEBUG: Erro ao buscar nome do usuário: " . $e->getMessage());
                }
            }
            if ($newStatus === 'Enviada' && (empty($propostaAtual['token_aprovacao']) || $tokenExpirado)) {
                $token = $this->generateApprovalToken();
                $validade = date('Y-m-d H:i:s', strtotime('+7 days'));
                $stmt->bindValue(':token', $token);
                $stmt->bindValue(':validade', $validade);
                error_log("DEBUG: Token gerado: $token, Validade: $validade");
            }
            $stmt->bindValue(':id', $propostaId, PDO::PARAM_INT);

            $success = $stmt->execute();
            error_log("DEBUG: UPDATE executado - Sucesso: " . ($success ? 'SIM' : 'NAO'));

            // Gatilho: Se o novo status for 'Aprovada', gera o projeto e o contrato
            if ($success && $newStatus === 'Aprovada') {
                if ($propostaAtual && empty($propostaAtual['projeto_id'])) {
                    $projetoId = $this->criarProjetoParaPropostaAprovada($propostaAtual);
                } else {
                    $projetoId = $propostaAtual['projeto_id'] ?? null;
                }
                if ($criarContrato) {
                    error_log("DEBUG: Gerando contrato para proposta aprovada");
                    $contratosModel = new ContratosModel();
                    $contratoId = $contratosModel->criarContratoDeProposta($propostaId);
                    if ($contratoId && is_numeric($contratoId)) {
                        $this->db->prepare("UPDATE orcamento_proposta SET contrato_id = ? WHERE id = ?")->execute([$contratoId, $propostaId]);
                    }
                }
                // Sincroniza os itens da proposta para o orçamento do projeto
                if (!empty($projetoId)) {
                    $projetosModel = new \App\Models\ProjetosModel();
                    $servicos = json_decode($propostaAtual['servicos_json'] ?? '[]', true) ?: [];
                    $materiais = json_decode($propostaAtual['materiais_json'] ?? '[]', true) ?: [];
                    $custosExtras = json_decode($propostaAtual['custos_extras_json'] ?? '[]', true) ?: [];
                    $projetosModel->syncOrcamentoFromProposta(
                        $projetoId,
                        $servicos,
                        $materiais,
                        $custosExtras,
                        $propostaId,
                        $propostaAtual['data_proposta'] ?? null
                    );
                }
            }

            return $success;
        } catch (\PDOException $e) {
            error_log('Erro ao atualizar status da proposta: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            return false;
        }
    }

    /**
     * Cria um novo projeto a partir de uma proposta aprovada,
     * transferindo as coordenadas geográficas.
     * @param array $proposta Os dados da proposta.
     * @return int|null Retorna o ID do projeto criado ou null em caso de falha.
     */
    private function criarProjetoParaPropostaAprovada(array $proposta): ?int
    {
        try {
            $projetosModel = new \App\Models\ProjetosModel();
            $numProjeto = $projetosModel->getNextProjectNumber();
            
            // Busca o nome do responsável se existir
            $responsavelId = $proposta['responsavel_interno_id'] ?? $proposta['responsavel_interno'] ?? null;
            $responsavelNome = $proposta['responsavel_nome'] ?? null;

            if (empty($responsavelNome) && !empty($responsavelId)) {
                $stmtU = $this->db->prepare("SELECT nome FROM usuarios WHERE id = ? LIMIT 1");
                $stmtU->execute([$responsavelId]);
                $responsavelNome = $stmtU->fetchColumn();
            }

            if (empty($responsavelNome)) {
                $responsavelNome = 'Não Atribuído';
            }

            $dadosProjeto = [
                'numero_projeto' => $numProjeto,
                'nome'           => $proposta['nome_proposta'] ?? $proposta['titulo'] ?? 'Projeto Gerado de Proposta',
                'cliente_id'     => $proposta['cliente_id'] ?? null,
                'data_inicial'   => date('Y-m-d'),
                'orcamento'      => (float)($proposta['total_final'] ?? $proposta['valor_total'] ?? 0),
                'orcamento_id'   => $proposta['numero_proposta'] ?? $proposta['id'] ?? '',
                'responsavel'    => $responsavelNome,
                'tipo_servico'   => 'Comercial', // Preenche campo obrigatório
                'status'         => 'Planejado',
                'latitude'       => (isset($proposta['latitude']) && $proposta['latitude'] !== '') ? $proposta['latitude'] : null,
                'longitude'      => (isset($proposta['longitude']) && $proposta['longitude'] !== '') ? $proposta['longitude'] : null,
                'observacoes'    => "Criado automaticamente a partir da proposta aprovada: " . ($proposta['numero_proposta'] ?? $proposta['id'] ?? '')
            ];

            $projetoId = $projetosModel->salvarProjeto($dadosProjeto);
            if ($projetoId) {
                // Atualiza o projeto_id na proposta para vincular
                $stmtLink = $this->db->prepare("UPDATE orcamento_proposta SET projeto_id = ? WHERE id = ?");
                $stmtLink->execute([$projetoId, $proposta['id']]);

                // Notifica o Gestor Comercial se a proposta tiver coordenadas (geolocalizada)
                if (!empty($proposta['latitude']) && !empty($proposta['longitude'])) {
                    $this->notificarGestorComercialConversaoGeolocalizada($proposta, (int)$projetoId);
                }

                return (int)$projetoId;
            }
        } catch (\Exception $e) {
            error_log("Erro ao criar projeto para proposta aprovada: " . $e->getMessage());
        }
        return null;
    }

    /**
     * Notifica os Gestores Comerciais quando uma proposta geolocalizada é convertida em projeto.
     */
    private function notificarGestorComercialConversaoGeolocalizada(array $proposta, int $projetoId): void
    {
        try {
            $notificacoesModel = new \App\Models\NotificacoesModel();
            $perfilModel = new \App\Models\PerfilModel();
            $usuarioModel = new \App\Models\UsuarioModel();

            // 1. Identifica perfis que têm permissão comercial ou administrativa total
            $perfis = $perfilModel->getAll();
            $perfisIds = [];
            foreach ($perfis as $p) {
                $permissoes = json_decode($p['permissoes'] ?? '[]', true);
                if (is_array($permissoes) && (in_array('comercial_propostas_view', $permissoes) || in_array('*', $permissoes))) {
                    $perfisIds[] = $p['perfil_id'];
                }
            }

            if (empty($perfisIds)) return;

            // 2. Notifica usuários ativos pertencentes a esses perfis
            $usuarios = $usuarioModel->getListaUsuarios('Ativo');
            $numRef = $proposta['numero_proposta'] ?? $proposta['id'];
            
            foreach ($usuarios as $u) {
                if (in_array($u['perfil_id'], $perfisIds)) {
                    $notificacoesModel->criarNotificacao(
                        (int)$u['id'],
                        'Nova Oportunidade no Mapa',
                        "A proposta geolocalizada #{$numRef} foi aprovada e convertida no Projeto #{$projetoId}. Confira a nova localização no Dashboard.",
                        BASE_URL . "/projetos/detalhe/{$projetoId}"
                    );
                }
            }
        } catch (\Exception $e) {
            error_log("Erro em notificarGestorComercialConversaoGeolocalizada: " . $e->getMessage());
        }
    }

    /**
     * Envia uma proposta para aprovação do diretor.
     */
    public function enviarParaDiretor(int $propostaId, int $usuarioId): bool
    {
        try {
            $stmt = $this->db->prepare(
                "UPDATE orcamento_proposta SET 
                    aprovacao_diretor_status = 'pendente',
                    enviado_para_diretor_em = NOW(),
                    enviado_diretor_por = ?,
                    justificativa_rejeicao = NULL
                 WHERE id = ? AND status IN ('Rascunho', 'Enviada')"
            );
            return $stmt->execute([$usuarioId, $propostaId]);
        } catch (\PDOException $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    /**
     * Diretor aprova a proposta.
     */
    public function aprovarDiretor(int $propostaId, int $diretorId): bool
    {
        try {
            $stmt = $this->db->prepare(
                "UPDATE orcamento_proposta SET 
                    aprovacao_diretor_status = 'aprovado',
                    aprovado_diretor_por = ?,
                    aprovado_diretor_em = NOW(),
                    justificativa_rejeicao = NULL
                 WHERE id = ? AND aprovacao_diretor_status = 'pendente'"
            );
            return $stmt->execute([$diretorId, $propostaId]);
        } catch (\PDOException $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    /**
     * Diretor rejeita a proposta com justificativa.
     */
    public function rejeitarDiretor(int $propostaId, int $diretorId, string $justificativa): bool
    {
        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare(
                "UPDATE orcamento_proposta SET 
                    aprovacao_diretor_status = 'rejeitado',
                    aprovado_diretor_por = ?,
                    aprovado_diretor_em = NOW(),
                    justificativa_rejeicao = ?
                 WHERE id = ? AND aprovacao_diretor_status = 'pendente'"
            );
            $stmt->execute([$diretorId, $justificativa, $propostaId]);

            // Salva histórico
            $propostaAtual = $this->getPropostaById($propostaId);
            if ($propostaAtual) {
                $this->salvarHistorico($propostaAtual, $diretorId, 'Rejeitada pelo diretor: ' . $justificativa);
            }

            $this->db->commit();
            return true;
        } catch (\PDOException $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    /**
     * Retorna propostas pendentes de aprovação do diretor.
     */
    public function getPropostasPendentesDiretor(int $limit = 50, int $offset = 0): array
    {
        try {
            $sql = "SELECT p.*, p.id as id, p.nome_proposta as titulo, p.total_final as valor_total,
                           c.nome as cliente_nome, u.nome as responsavel_nome
                    FROM orcamento_proposta p
                    LEFT JOIN clientes c ON p.cliente_id = c.id
                    LEFT JOIN usuarios u ON p.enviado_diretor_por = u.id
                    WHERE p.aprovacao_diretor_status = 'pendente'
                    ORDER BY p.enviado_para_diretor_em DESC
                    LIMIT :limit OFFSET :offset";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log('Erro ao buscar propostas pendentes: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Retorna a contagem de propostas pendentes de aprovação do diretor.
     */
    public function getCountPropostasPendentesDiretor(): int
    {
        try {
            $stmt = $this->db->query("SELECT COUNT(*) FROM orcamento_proposta WHERE aprovacao_diretor_status = 'pendente'");
            return (int)$stmt->fetchColumn();
        } catch (\PDOException $e) {
            return 0;
        }
    }

    /**
     * Retorna a última mensagem de erro ocorrida no modelo.
     *
     * @return string
     */
    public function getLastError(): string
    {
        return $this->lastError;
    }
}
