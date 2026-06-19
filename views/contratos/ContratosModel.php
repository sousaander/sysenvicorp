<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class ContratosModel extends Model
{
    public function __construct()
    {
        parent::__construct();
        $this->ensureColumnsExist();
    }

    /**
     * Garante que todas as colunas necessárias existam na tabela 'contratos'.
     * Isso sincroniza o banco de dados com as novas funcionalidades do Model.
     */
    private function ensureColumnsExist()
    {
        $columnsToCheck = [
            'titulo' => 'VARCHAR(255)',
            'numero_contrato' => 'VARCHAR(50)',
            'numero_contrato_cliente' => 'VARCHAR(100)',
            'base_referencia' => 'VARCHAR(255)',
            'foro_eleicao' => 'VARCHAR(255)',
            'lei_aplicavel' => 'VARCHAR(100)',
            'resolucao_disputas' => 'VARCHAR(100)',
            'local_assinatura' => 'VARCHAR(255)',
            'cliente_id' => 'INT',
            'pessoa_id' => 'INT',
            'proposta_id' => 'INT',
            'projeto_id' => 'INT',
            'duracao_meses' => 'INT',
            'objeto' => 'TEXT',
            'tipo' => 'VARCHAR(100)',
            'status' => 'VARCHAR(50)',
            'data_inicio' => 'DATE',
            'vencimento' => 'DATE',
            'valor' => 'DECIMAL(15,2)',
            'contratante_nome' => 'VARCHAR(255)',
            'contratante_documento' => 'VARCHAR(50)',
            'contratante_endereco' => 'TEXT',
            'contratante_telefone' => 'VARCHAR(20)',
            'contratante_email' => 'VARCHAR(255)',
            'contratante_representante' => 'VARCHAR(255)',
            'contratante_rg_cpf_rep' => 'VARCHAR(20)',
            'contratado_nome' => 'VARCHAR(255)',
            'contratado_documento' => 'VARCHAR(50)',
            'contratado_endereco' => 'TEXT',
            'contratado_telefone' => 'VARCHAR(20)',
            'contratado_email' => 'VARCHAR(255)',
            'contratado_representante' => 'VARCHAR(255)',
            'contratado_rg_cpf_rep' => 'VARCHAR(20)',
            'forma_pagamento' => 'VARCHAR(255)',
            'pix_tipo_chave' => 'VARCHAR(50)',
            'dados_bancarios' => 'VARCHAR(255)',
            'condicao_pagamento' => 'VARCHAR(100)',
            'dia_vencimento' => 'INT',
            'valor_sinal' => 'DECIMAL(15,2)',
            'numero_parcelas' => 'INT',
            'multa_atraso' => 'DECIMAL(5,2)',
            'juros_mora' => 'DECIMAL(5,2)',
            'correcao_monetaria' => 'VARCHAR(50)',
            'prazo_carencia_multa' => 'INT',
            'penalidade_descumprimento' => 'TEXT',
            'multa_rescisao_antecipada' => 'TEXT',
            'observacoes_financeiras' => 'TEXT',
            'confidencialidade_tags' => 'JSON',
            'prazo_sigilo' => 'VARCHAR(50)',
            'penalidade_violacao_sigilo' => 'VARCHAR(255)',
            'dpo_encarregado' => 'VARCHAR(255)',
            'transferencia_internacional' => 'TINYINT(1) DEFAULT 0',
            'subcontratacao_dados' => 'TINYINT(1) DEFAULT 0',
            'base_legal_lgpd' => 'VARCHAR(100)',
            'lgpd_conformidade' => 'TINYINT(1) DEFAULT 0',
            'clausula_confidencialidade' => 'TEXT',
            'aviso_previo_rescisao' => 'VARCHAR(50)',
            'rescisao_descumprimento' => 'VARCHAR(50)',
            'nao_concorrencia' => 'VARCHAR(50)',
            'indenizacao_rescisao' => 'TEXT',
            'causas_rescisao_imotivada' => 'TEXT',
            'causas_justa_causa' => 'TEXT',
            'obrigacoes_pos_encerramento' => 'TEXT',
            'responsabilidades_contratante' => 'TEXT',
            'responsabilidades_contratado' => 'TEXT',
            'criterios_aceite' => 'TEXT',
            'renovacao_automatica' => 'VARCHAR(100)',
            'clausulas_adicionais' => 'TEXT',
            'assinatura_tipo' => 'VARCHAR(100)',
            'numero_vias' => 'VARCHAR(50)',
            'observacoes' => 'TEXT',
            'documento_path' => 'VARCHAR(255)',
            'clausula_lgpd' => "VARCHAR(20) DEFAULT 'N/A'",
            'risco_contratual' => "VARCHAR(20) DEFAULT 'Baixo'",
            'parecer_juridico' => 'TEXT',
            'dataCriacao' => 'DATETIME',
            'token_assinatura' => 'VARCHAR(64) UNIQUE NULL',
            'token_assinatura_validade' => 'DATETIME NULL',
            'assinado_em' => 'DATETIME NULL',
            'assinado_ip' => 'VARCHAR(45) NULL'
        ];

        foreach ($columnsToCheck as $col => $def) {
            try {
                $stmt = $this->db->query("SHOW COLUMNS FROM contratos LIKE '$col'");
                if ($stmt->rowCount() == 0) {
                    $this->db->exec("ALTER TABLE contratos ADD COLUMN $col $def");
                }
            } catch (\PDOException $e) {
                error_log("Erro ao verificar/adicionar coluna $col na tabela contratos: " . $e->getMessage());
            }
        }
    }

    /**
     * Salva o token de assinatura digital.
     */
    public function saveSignatureToken(int $id, string $token, string $validade): bool
    {
        try {
            $stmt = $this->db->prepare("UPDATE contratos SET token_assinatura = ?, token_assinatura_validade = ? WHERE id = ?");
            return $stmt->execute([$token, $validade, $id]);
        } catch (\PDOException $e) {
            error_log("Erro ao salvar token de assinatura: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Busca um contrato pelo token de assinatura.
     */
    public function getContratoByToken(string $token): ?array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM contratos WHERE token_assinatura = ?");
            $stmt->execute([$token]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (\PDOException $e) {
            return null;
        }
    }

    /**
     * Registra a assinatura digital do contrato.
     */
    public function marcarComoAssinado(int $id, string $ip): bool
    {
        try {
            $stmt = $this->db->prepare("UPDATE contratos SET status = 'Em Vigência', assinado_em = NOW(), assinado_ip = ?, token_assinatura = NULL WHERE id = ?");
            return $stmt->execute([$ip, $id]);
        } catch (\PDOException $e) {
            return false;
        }
    }

    /**
     * Atualiza apenas o status do contrato.
     */
    public function atualizarStatus(int $id, string $status): bool
    {
        try {
            $stmt = $this->db->prepare("UPDATE contratos SET status = ? WHERE id = ?");
            return $stmt->execute([$status, $id]);
        } catch (\PDOException $e) {
            return false;
        }
    }

    /**
     * Busca as configurações do módulo de contratos (incluindo modelo padrão).
     */
    public function getSettings(): array
    {
        $file = ROOT_PATH . '/storage/config/contratos_settings.json';
        $default = [
            'modelo_padrao' => '',
            'modelo_responsabilidades_contratante' => '',
            'modelo_responsabilidades_contratado' => '',
            'modelo_clausulas_adicionais' => ''
        ];
        if (file_exists($file)) {
            $data = json_decode(file_get_contents($file), true);
            return is_array($data) ? array_merge($default, $data) : $default;
        }
        return $default;
    }

    /**
     * Salva as configurações do módulo de contratos.
     */
    public function saveSettings(array $settings): bool
    {
        $dir = ROOT_PATH . '/storage/config';
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        return file_put_contents($dir . '/contratos_settings.json', json_encode($settings, JSON_PRETTY_PRINT)) !== false;
    }

    /**
     * Retorna o próximo número sequencial de contrato para o ano atual.
     * Formato: CTR-YYYY-NNNN
     */
    public function getNextContractNumber(): string
    {
        $year = date('Y');
        $prefix = "CTR-$year-";

        try {
            // Busca o maior número após o prefixo para o ano atual
            // O prefixo 'CTR-YYYY-' tem 9 caracteres, o número começa na posição 10
            $stmt = $this->db->prepare("SELECT MAX(CAST(SUBSTRING(numero_contrato, 10) AS UNSIGNED)) FROM contratos WHERE numero_contrato LIKE :prefix");
            $stmt->execute([':prefix' => $prefix . '%']);
            $lastNumber = (int)$stmt->fetchColumn();
            
            return $prefix . str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        } catch (\PDOException $e) {
            error_log("Erro ao gerar próximo número de contrato: " . $e->getMessage());
            return $prefix . "001";
        }
    }

    /**
     * Verifica se um número de contrato já existe no banco de dados.
     * @param string $numero
     * @param int|null $excludeId ID a ser ignorado (útil na edição)
     * @return bool
     */
    public function numeroContratoExiste(string $numero, ?int $excludeId = null): bool
    {
        try {
            $sql = "SELECT COUNT(*) FROM contratos WHERE numero_contrato = :numero";
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
            error_log("Erro ao verificar existência do número de contrato: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verifica se um número de contrato do cliente já existe para um determinado cliente.
     * @param string $numeroCliente
     * @param int $clienteId
     * @param int|null $excludeId ID a ser ignorado (útil na edição)
     * @return bool
     */
    public function numeroContratoClienteExiste(string $numeroCliente, int $clienteId, ?int $excludeId = null): bool
    {
        try {
            $sql = "SELECT COUNT(*) FROM contratos WHERE numero_contrato_cliente = :numeroCliente AND cliente_id = :clienteId";
            if ($excludeId) {
                $sql .= " AND id != :excludeId";
            }
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':numeroCliente', $numeroCliente);
            $stmt->bindValue(':clienteId', $clienteId, \PDO::PARAM_INT);
            if ($excludeId) {
                $stmt->bindValue(':excludeId', $excludeId, \PDO::PARAM_INT);
            }
            $stmt->execute();
            return (int)$stmt->fetchColumn() > 0;
        } catch (\PDOException $e) {
            error_log("Erro ao verificar existência do número de contrato do cliente: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Busca dados resumidos sobre a carteira de contratos.
     */
    public function getContratosSummary()
    {
        try {
            $totalVigentes = $this->db->query("SELECT COUNT(*) FROM contratos WHERE status = 'Em Vigência'")->fetchColumn();
            $totalVencidos = $this->db->query("SELECT COUNT(*) FROM contratos WHERE status = 'Em Vigência' AND vencimento < CURDATE()")->fetchColumn();
            $vencendo30dias = $this->db->query("SELECT COUNT(*) FROM contratos WHERE status = 'Em Vigência' AND vencimento BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)")->fetchColumn();
            $comPendenciaDocs = $this->db->query("SELECT COUNT(*) FROM contratos WHERE status = 'Pendência Assinatura'")->fetchColumn();
            $valorTotalAnual = $this->db->query("SELECT SUM(c.valor) FROM contratos c WHERE c.status = 'Em Vigência'")->fetchColumn();

            return [
                'totalVigentes' => $totalVigentes,
                'totalVencidos' => $totalVencidos,
                'vencendo30dias' => $vencendo30dias,
                'comPendenciaDocs' => $comPendenciaDocs,
                'valorTotalAnual' => 'R$ ' . number_format($valorTotalAnual ?? 0, 2, ',', '.'),
            ];
        } catch (\PDOException $e) {
            error_log("Erro ao buscar resumo de contratos: " . $e->getMessage());
            return ['totalVigentes' => 0, 'totalVencidos' => 0, 'vencendo30dias' => 0, 'comPendenciaDocs' => 0, 'valorTotalAnual' => 'R$ 0,00'];
        }
    }

    /**
     * Busca uma lista de contratos, com suporte para filtros e paginação.
     * @param array $filtros Filtros de busca (para uso futuro)
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getContratos(array $filtros = [], int $limit = 5, int $offset = 0): array
    {
        try {
            $sql = "SELECT 
                        c.id, 
                        c.numero_contrato,
                        c.numero_contrato_cliente,
                        c.base_referencia,
                        c.titulo,
                        c.objeto,
                        c.cliente_id,
                        c.contratante_email,
                        c.tipo, 
                        COALESCE(c.contratado_nome, f.nome, p.razao_social, cli.nome) as parteContratada, 
                        c.valor, 
                        c.vencimento,
                        c.documento_path, 
                        c.status
                    FROM contratos c
                    LEFT JOIN clientes cli ON c.cliente_id = cli.id
                    LEFT JOIN fornecedores f ON c.pessoa_id = f.id
                    LEFT JOIN pessoas p ON c.pessoa_id = p.pessoa_id AND p.tipo = 'Fornecedor'
                    -- WHERE clause para filtros futuros
                    GROUP BY c.id
                    ORDER BY c.id DESC
                    LIMIT :limit OFFSET :offset";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':limit', $limit, \PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, \PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Erro ao buscar lista de contratos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Verifica se um contrato idêntico foi salvo nos últimos segundos para evitar duplicidade por duplo clique.
     */
    public function verificarDuplicidadeRecente(array $dados, int $segundos = 5): bool
    {
        try {
            $sql = "SELECT COUNT(*) FROM contratos 
                    WHERE titulo = :titulo 
                    AND contratante_documento = :doc 
                    AND valor = :valor 
                    AND dataCriacao >= DATE_SUB(NOW(), INTERVAL :seg SECOND)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':titulo', $dados['titulo'] ?? '');
            $stmt->bindValue(':doc', $dados['contratante_documento'] ?? '');
            $stmt->bindValue(':valor', $dados['valor'] ?? 0);
            $stmt->bindValue(':seg', $segundos, \PDO::PARAM_INT);
            $stmt->execute();
            
            return (int)$stmt->fetchColumn() > 0;
        } catch (\PDOException $e) {
            return false;
        }
    }

    /**
     * Conta o número total de contratos que correspondem a um filtro.
     * @param array $filtros
     * @return int
     */
    public function getContratosCount(array $filtros = []): int
    {
        $sql = "SELECT COUNT(*) FROM contratos c";
        $params = [];

        // Lógica de filtro (para uso futuro)

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Busca uma lista de contratos associados a uma pessoa (fornecedor).
     * @param int $pessoa_id
     * @return array
     */
    public function getContratosByPessoaId(int $pessoa_id): array
    {
        try {
            $sql = "SELECT 
                        id, 
                        numero_contrato,
                        numero_contrato_cliente,
                        objeto, 
                        valor, 
                        vencimento,
                        documento_path, 
                        status
                    FROM contratos
                    WHERE pessoa_id = :pessoa_id
                    ORDER BY vencimento DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':pessoa_id', $pessoa_id, \PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Erro ao buscar contratos por pessoa_id: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Busca uma lista de contratos associados a um cliente.
     * @param int $clienteId
     * @param string|null $documento Opcional: Busca também por correspondência de documento (CNPJ/CPF)
     * @return array
     */
    public function getContratosByClienteId(int $clienteId, ?string $documento = null): array
    {
        try {
            // Se o documento não foi passado, tenta buscar o CNPJ_CPF do cliente para garantir o vínculo por nome/doc
            if (empty($documento)) {
                $stmtCli = $this->db->prepare("SELECT cnpj_cpf FROM clientes WHERE id = ?");
                $stmtCli->execute([$clienteId]);
                $documento = $stmtCli->fetchColumn();
            }

            $sql = "SELECT 
                        id, numero_contrato, numero_contrato_cliente, base_referencia, titulo, objeto, valor, vencimento, status
                    FROM contratos
                    WHERE cliente_id = :cliente_id";
            
            $params = [':cliente_id' => $clienteId];

            if (!empty($documento)) {
                $docLimpo = preg_replace('/\D/', '', $documento);
                if (!empty($docLimpo)) {
                    // Busca por cliente_id OR pelo documento contratante (ignorando formatação como pontos e traços)
                    $sql .= " OR (REPLACE(REPLACE(REPLACE(contratante_documento, '.', ''), '-', ''), '/', '') = :docLimpo)";
                    $params[':docLimpo'] = $docLimpo;
                }
            }

            $sql .= " ORDER BY vencimento DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Erro ao buscar contratos por cliente_id: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Busca um contrato específico pelo ID.
     * @param int $id O ID do contrato.
     * @return array|null
     */
    public function getContratoById(int $id): ?array
    {
        try {
            // Busca o contrato e faz JOIN com tabelas relacionadas para fallback de nomes
            $sql = "SELECT c.*,
                           proj.nome as projeto_nome,
                           cli.sigla as cliente_sigla,
                           cli.nome as fallback_cliente, 
                           COALESCE(f.nome, p.razao_social) as fallback_fornecedor
                    FROM contratos c
                    LEFT JOIN clientes cli ON c.cliente_id = cli.id
                    LEFT JOIN projetos proj ON c.projeto_id = proj.id
                    LEFT JOIN fornecedores f ON c.pessoa_id = f.id
                    LEFT JOIN pessoas p ON c.pessoa_id = p.pessoa_id AND p.tipo = 'Fornecedor'
                    WHERE c.id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($result) {
                // Lógica de Preenchimento Automático para campos legados (NULL no banco novo)
                if (empty($result['titulo']) && !empty($result['objeto'])) {
                    $result['titulo'] = mb_substr($result['objeto'], 0, 50) . '...';
                }
                
                if (empty($result['contratado_nome']) && !empty($result['fallback_fornecedor'])) {
                    $result['contratado_nome'] = $result['fallback_fornecedor'];
                }
                
                if (empty($result['contratante_nome']) && !empty($result['fallback_cliente'])) {
                    $result['contratante_nome'] = $result['fallback_cliente'];
                }
            }
            return $result ?: null;
        } catch (\PDOException $e) {
            error_log("Erro ao buscar contrato por ID: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Busca um contrato específico pelo ID com todos os dados detalhados.
     * @param int $id O ID do contrato.
     * @return array|null
     */
    public function getContratoDetalhadoById(int $id): ?array
    {
        try {
            $sql = "SELECT c.*, 
                           proj.nome as projeto_nome,
                           COALESCE(c.contratado_nome, f.nome, p.razao_social, cli.nome) as parteContratada
                    FROM contratos c
                    LEFT JOIN clientes cli ON c.cliente_id = cli.id
                    LEFT JOIN projetos proj ON c.projeto_id = proj.id
                    LEFT JOIN fornecedores f ON c.pessoa_id = f.id
                    LEFT JOIN pessoas p ON c.pessoa_id = p.pessoa_id AND p.tipo = 'Fornecedor'
                    WHERE c.id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (\PDOException $e) {
            error_log("Erro ao buscar contrato detalhado por ID: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Busca todos os aditivos de um contrato específico.
     * @param int $contrato_id
     * @return array
     */
    public function getAditivosByContratoId(int $contrato_id): array
    {
        try {
            $sql = "SELECT * FROM contratos_aditivos WHERE contrato_id = ? ORDER BY data_aditivo DESC, id DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$contrato_id]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Erro ao buscar aditivos do contrato: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Busca um aditivo específico pelo ID.
     * @param int $id
     * @return array|null
     */
    public function getAditivoById(int $id): ?array
    {
        try {
            $sql = "SELECT * FROM contratos_aditivos WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
        } catch (\PDOException $e) {
            error_log("Erro ao buscar aditivo por ID: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Salva um novo aditivo no banco de dados.
     * @param array $dados
     * @return bool
     */
    public function salvarAditivo(array $dados): bool
    {
        $this->db->beginTransaction();

        try {
            // 1. Inserir o aditivo
            $sqlAditivo = "INSERT INTO contratos_aditivos 
                    (contrato_id, tipo_aditivo, data_aditivo, descricao, valor_alteracao, novo_vencimento, documento_path) 
                VALUES 
                    (:contrato_id, :tipo_aditivo, :data_aditivo, :descricao, :valor_alteracao, :novo_vencimento, :documento_path)";

            $stmtAditivo = $this->db->prepare($sqlAditivo);
            $stmtAditivo->bindValue(':contrato_id', $dados['contrato_id'], \PDO::PARAM_INT);
            $stmtAditivo->bindValue(':tipo_aditivo', $dados['tipo_aditivo']);
            $stmtAditivo->bindValue(':data_aditivo', $dados['data_aditivo']);
            $stmtAditivo->bindValue(':descricao', $dados['descricao']);
            $stmtAditivo->bindValue(':valor_alteracao', $dados['valor_alteracao'], $dados['valor_alteracao'] !== null ? \PDO::PARAM_STR : \PDO::PARAM_NULL);
            $stmtAditivo->bindValue(':novo_vencimento', $dados['novo_vencimento'], $dados['novo_vencimento'] !== null ? \PDO::PARAM_STR : \PDO::PARAM_NULL);
            $stmtAditivo->bindValue(':documento_path', $dados['documento_path'] ?? null, \PDO::PARAM_STR);

            if (!$stmtAditivo->execute()) {
                $this->db->rollBack();
                return false;
            }

            // 2. Atualizar o contrato principal, se necessário
            $updates = [];
            $params = [':contrato_id' => $dados['contrato_id']];

            if (!empty($dados['novo_vencimento'])) {
                $updates[] = "vencimento = :novo_vencimento";
                $params[':novo_vencimento'] = $dados['novo_vencimento'];
            }
            if (!empty($dados['valor_alteracao'])) {
                // Adiciona o valor do aditivo ao valor existente do contrato
                $updates[] = "valor = valor + :valor_alteracao";
                $params[':valor_alteracao'] = $dados['valor_alteracao'];
            }

            if (!empty($updates)) {
                $sqlContrato = "UPDATE contratos SET " . implode(', ', $updates) . " WHERE id = :contrato_id";
                $stmtContrato = $this->db->prepare($sqlContrato);
                $stmtContrato->execute($params);
            }

            $this->db->commit();
            return true;
        } catch (\PDOException $e) {
            $this->db->rollBack();
            error_log("Erro ao salvar aditivo: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Salva um novo contrato ou atualiza um existente no banco de dados.
     * @param array $dados Os dados do contrato a serem salvos.
     * @return int|bool Retorna o ID do contrato em caso de sucesso, false em caso de falha.
     */
    public function salvarContrato(array $dados)
    {
        // Sanitiza e prepara os dados
        $id = !empty($dados['id']) ? (int)$dados['id'] : null;

        $projeto_id = !empty($dados['projeto_id']) ? (int)$dados['projeto_id'] : null;
        $proposta_id = !empty($dados['proposta_id']) ? (int)$dados['proposta_id'] : null;
        $titulo = trim($dados['titulo'] ?? '');
        $foro_eleicao = trim($dados['foro_eleicao'] ?? '');
        $local_assinatura = trim($dados['local_assinatura'] ?? '');
        $forma_pagamento = trim($dados['forma_pagamento'] ?? '');
        $observacoes = trim($dados['observacoes'] ?? '');
        $objeto = trim($dados['objeto'] ?? ''); // 'titulo' na sua tabela antiga
        $tipo = $dados['tipo'] ?? null;
        $status = $dados['status'] ?? 'Em Vigência';
        $data_inicio = !empty($dados['data_inicio']) ? $dados['data_inicio'] : null;
        $vencimento = !empty($dados['vencimento']) ? $dados['vencimento'] : null;
        $valor = !empty($dados['valor']) ? (float)$dados['valor'] : 0.0;
        $documento_path = $dados['documento_path'] ?? null;

        // Dados das Partes (Capturados pelo Wizard)
        $contratante_nome = trim($dados['contratante_nome'] ?? '');
        $contratante_documento = trim($dados['contratante_documento'] ?? '');
        $contratante_endereco = trim($dados['contratante_endereco'] ?? '');
        $contratante_email = trim($dados['contratante_email'] ?? '');
        
        $contratado_nome = trim($dados['contratado_nome'] ?? '');
        $contratado_documento = trim($dados['contratado_documento'] ?? '');
        $contratado_endereco = trim($dados['contratado_endereco'] ?? '');
        $contratado_email = trim($dados['contratado_email'] ?? '');

        try {
            if ($id) {
                // UPDATE: Atualiza um contrato existente
                // Adiciona a atualização do documento apenas se um novo foi enviado
                $documentoSql = $documento_path ? ", documento_path = :documento_path" : "";

                $sql = "UPDATE contratos SET 
                            titulo = :titulo, numero_contrato = :numero_contrato, numero_contrato_cliente = :numero_contrato_cliente,
                            foro_eleicao = :foro_eleicao, lei_aplicavel = :lei_aplicavel, resolucao_disputas = :resolucao_disputas, local_assinatura = :local_assinatura, 
                            cliente_id = :cliente_id, pessoa_id = :pessoa_id, projeto_id = :projeto_id, proposta_id = :proposta_id,
                            duracao_meses = :duracao_meses, objeto = :objeto, tipo = :tipo, status = :status, 
                            data_inicio = :data_inicio, vencimento = :vencimento, valor = :valor,
                            contratante_nome = :contratante_nome, contratante_documento = :contratante_documento, contratante_endereco = :contratante_endereco, 
                            contratante_email = :contratante_email, contratante_telefone = :contratante_telefone, contratante_representante = :contratante_representante, 
                            contratante_rg_cpf_rep = :contratante_rg_cpf_rep,
                            contratado_nome = :contratado_nome, contratado_documento = :contratado_documento, contratado_endereco = :contratado_endereco, 
                            contratado_email = :contratado_email, contratado_telefone = :contratado_telefone, contratado_representante = :contratado_representante, 
                            contratado_rg_cpf_rep = :contratado_rg_cpf_rep, pix_tipo_chave = :pix_tipo_chave,
                            forma_pagamento = :forma_pagamento, dados_bancarios = :dados_bancarios, condicao_pagamento = :condicao_pagamento, dia_vencimento = :dia_vencimento, 
                            valor_sinal = :valor_sinal, numero_parcelas = :numero_parcelas, multa_atraso = :multa_atraso, juros_mora = :juros_mora, 
                            correcao_monetaria = :correcao_monetaria, prazo_carencia_multa = :prazo_carencia_multa, penalidade_descumprimento = :penalidade_descumprimento, 
                            multa_rescisao_antecipada = :multa_rescisao_antecipada, observacoes_financeiras = :observacoes_financeiras,
                            confidencialidade_tags = :confidencialidade_tags, prazo_sigilo = :prazo_sigilo, 
                            penalidade_violacao_sigilo = :penalidade_violacao_sigilo, dpo_encarregado = :dpo_encarregado,
                            transferencia_internacional = :transferencia_internacional, subcontratacao_dados = :subcontratacao_dados,
                            base_legal_lgpd = :base_legal_lgpd, lgpd_conformidade = :lgpd_conformidade, clausula_confidencialidade = :clausula_confidencialidade, 
                            aviso_previo_rescisao = :aviso_previo_rescisao, rescisao_descumprimento = :rescisao_descumprimento, nao_concorrencia = :nao_concorrencia, 
                            indenizacao_rescisao = :indenizacao_rescisao, causas_rescisao_imotivada = :causas_rescisao_imotivada, causas_justa_causa = :causas_justa_causa,
                            obrigacoes_pos_encerramento = :obrigacoes_pos_encerramento, responsabilidades_contratante = :responsabilidades_contratante,
                            responsabilidades_contratado = :responsabilidades_contratado, criterios_aceite = :criterios_aceite, renovacao_automatica = :renovacao_automatica,
                            clausulas_adicionais = :clausulas_adicionais, assinatura_tipo = :assinatura_tipo, numero_vias = :numero_vias,
                            observacoes = :observacoes {$documentoSql}
                        WHERE id = :id";
                $stmt = $this->db->prepare($sql);
                $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
            } else {
                // INSERT: Cria um novo contrato
                $sql = "INSERT INTO contratos (
                            titulo, numero_contrato, numero_contrato_cliente, base_referencia, foro_eleicao, lei_aplicavel, resolucao_disputas, local_assinatura, cliente_id, pessoa_id, projeto_id, proposta_id,
                            duracao_meses, objeto, tipo, status, data_inicio, vencimento, valor, 
                            contratante_nome, contratante_documento, contratante_endereco, contratante_email, contratante_telefone, contratante_representante, contratante_rg_cpf_rep,
                            contratado_nome, contratado_documento, contratado_endereco, contratado_email, contratado_telefone, contratado_representante, contratado_rg_cpf_rep,
                            forma_pagamento, pix_tipo_chave, dados_bancarios, condicao_pagamento, dia_vencimento, valor_sinal, numero_parcelas, multa_atraso, juros_mora, 
                            correcao_monetaria, prazo_carencia_multa, penalidade_descumprimento, multa_rescisao_antecipada, observacoes_financeiras, confidencialidade_tags, 
                            prazo_sigilo, penalidade_violacao_sigilo, dpo_encarregado, transferencia_internacional, subcontratacao_dados,
                            base_legal_lgpd, lgpd_conformidade, clausula_confidencialidade, aviso_previo_rescisao, 
                            rescisao_descumprimento, nao_concorrencia, indenizacao_rescisao, causas_rescisao_imotivada, causas_justa_causa, 
                            obrigacoes_pos_encerramento, responsabilidades_contratante, responsabilidades_contratado,
                            criterios_aceite, renovacao_automatica,
                            clausulas_adicionais, assinatura_tipo, numero_vias,
                            observacoes, documento_path, dataCriacao
                        ) VALUES (
                            :titulo, :numero_contrato, :numero_contrato_cliente, :base_referencia, :foro_eleicao, :lei_aplicavel, :resolucao_disputas, :local_assinatura, :cliente_id, :pessoa_id, :projeto_id, :proposta_id,
                            :duracao_meses, :objeto, :tipo, :status, :data_inicio, :vencimento, :valor, 
                            :contratante_nome, :contratante_documento, :contratante_endereco, :contratante_email, :contratante_telefone, :contratante_representante, :contratante_rg_cpf_rep,
                            :contratado_nome, :contratado_documento, :contratado_endereco, :contratado_email, :contratado_telefone, :contratado_representante, :contratado_rg_cpf_rep,
                            :forma_pagamento, :pix_tipo_chave, :dados_bancarios, :condicao_pagamento, :dia_vencimento, :valor_sinal, :numero_parcelas, :multa_atraso, :juros_mora, 
                            :correcao_monetaria, :prazo_carencia_multa, :penalidade_descumprimento, :multa_rescisao_antecipada, :observacoes_financeiras, :confidencialidade_tags, 
                            :prazo_sigilo, :penalidade_violacao_sigilo, :dpo_encarregado, :transferencia_internacional, :subcontratacao_dados,
                            :base_legal_lgpd, :lgpd_conformidade, :clausula_confidencialidade, :aviso_previo_rescisao, 
                            :rescisao_descumprimento, :nao_concorrencia, :indenizacao_rescisao, :causas_rescisao_imotivada, :causas_justa_causa, 
                            :obrigacoes_pos_encerramento, :responsabilidades_contratante, :responsabilidades_contratado,
                            :criterios_aceite, :renovacao_automatica,
                            :clausulas_adicionais, :assinatura_tipo, :numero_vias,
                            :observacoes, :documento_path, NOW()
                        )";
                $stmt = $this->db->prepare($sql);
            }

            $stmt->bindParam(':titulo', $titulo);
            $stmt->bindValue(':numero_contrato', $dados['numero_contrato'] ?? null);
            $stmt->bindValue(':numero_contrato_cliente', $dados['numero_contrato_cliente'] ?? null);
            $stmt->bindValue(':base_referencia', $dados['base_referencia'] ?? null);
            $stmt->bindParam(':foro_eleicao', $foro_eleicao);
            $stmt->bindValue(':lei_aplicavel', $dados['lei_aplicavel'] ?? null);
            $stmt->bindValue(':resolucao_disputas', $dados['resolucao_disputas'] ?? null);
            $stmt->bindParam(':local_assinatura', $local_assinatura);
            $stmt->bindValue(':cliente_id', !empty($dados['cliente_id']) ? (int)$dados['cliente_id'] : null, \PDO::PARAM_INT);
            $stmt->bindValue(':pessoa_id', !empty($dados['pessoa_id']) ? (int)$dados['pessoa_id'] : null, \PDO::PARAM_INT);
            $stmt->bindValue(':projeto_id', $projeto_id, $projeto_id ? \PDO::PARAM_INT : \PDO::PARAM_NULL);
            $stmt->bindValue(':proposta_id', $proposta_id, $proposta_id ? \PDO::PARAM_INT : \PDO::PARAM_NULL);
            $stmt->bindValue(':duracao_meses', $dados['duracao_meses'] ?? null, \PDO::PARAM_INT);
            $stmt->bindParam(':objeto', $objeto);
            $stmt->bindParam(':tipo', $tipo);
            $stmt->bindParam(':status', $status);
            $stmt->bindValue(':data_inicio', $data_inicio, $data_inicio ? \PDO::PARAM_STR : \PDO::PARAM_NULL);
            $stmt->bindValue(':vencimento', $vencimento, $vencimento ? \PDO::PARAM_STR : \PDO::PARAM_NULL);
            $stmt->bindValue(':valor', $valor, \PDO::PARAM_STR); // Usar STR para decimais
            
            $stmt->bindParam(':contratante_nome', $contratante_nome);
            $stmt->bindParam(':contratante_documento', $contratante_documento);
            $stmt->bindParam(':contratante_endereco', $contratante_endereco);
            $stmt->bindValue(':contratante_telefone', $dados['contratante_telefone'] ?? null);
            $stmt->bindParam(':contratante_email', $contratante_email);
            $stmt->bindValue(':contratante_representante', $dados['contratante_representante'] ?? null);
            $stmt->bindValue(':contratante_rg_cpf_rep', $dados['contratante_rg_cpf_rep'] ?? null);

            $stmt->bindParam(':contratado_nome', $contratado_nome);
            $stmt->bindParam(':contratado_documento', $contratado_documento);
            $stmt->bindParam(':contratado_endereco', $contratado_endereco);
            $stmt->bindValue(':contratado_telefone', $dados['contratado_telefone'] ?? null);
            $stmt->bindParam(':contratado_email', $contratado_email);
            $stmt->bindValue(':contratado_representante', $dados['contratado_representante'] ?? null);
            $stmt->bindValue(':contratado_rg_cpf_rep', $dados['contratado_rg_cpf_rep'] ?? null);

            $stmt->bindValue(':pix_tipo_chave', $dados['pix_tipo_chave'] ?? null);
            $stmt->bindParam(':forma_pagamento', $forma_pagamento);
            $stmt->bindValue(':dados_bancarios', $dados['dados_bancarios'] ?? null);
            $stmt->bindValue(':condicao_pagamento', $dados['condicao_pagamento'] ?? null);
            $stmt->bindValue(':dia_vencimento', $dados['dia_vencimento'] ?? null, \PDO::PARAM_INT);
            $stmt->bindValue(':valor_sinal', $dados['valor_sinal'] ?? 0.0);
            $stmt->bindValue(':numero_parcelas', $dados['numero_parcelas'] ?? null, \PDO::PARAM_INT);
            $stmt->bindValue(':multa_atraso', $dados['multa_atraso'] ?? 0.0);
            $stmt->bindValue(':juros_mora', $dados['juros_mora'] ?? 0.0);
            $stmt->bindValue(':correcao_monetaria', $dados['correcao_monetaria'] ?? null);
            $stmt->bindValue(':prazo_carencia_multa', $dados['prazo_carencia_multa'] ?? null, \PDO::PARAM_INT);
            $stmt->bindValue(':penalidade_descumprimento', $dados['penalidade_descumprimento'] ?? null);
            $stmt->bindValue(':multa_rescisao_antecipada', $dados['multa_rescisao_antecipada'] ?? null);
            $stmt->bindValue(':observacoes_financeiras', $dados['observacoes_financeiras'] ?? null);
            $stmt->bindValue(':confidencialidade_tags', $dados['confidencialidade_tags'] ?? null);
            $stmt->bindValue(':prazo_sigilo', $dados['prazo_sigilo'] ?? null);
            $stmt->bindValue(':penalidade_violacao_sigilo', $dados['penalidade_violacao_sigilo'] ?? null);
            $stmt->bindValue(':dpo_encarregado', $dados['dpo_encarregado'] ?? null);
            $stmt->bindValue(':transferencia_internacional', $dados['transferencia_internacional'] ?? 0, \PDO::PARAM_INT);
            $stmt->bindValue(':subcontratacao_dados', $dados['subcontratacao_dados'] ?? 0, \PDO::PARAM_INT);
            $stmt->bindValue(':base_legal_lgpd', $dados['base_legal_lgpd'] ?? null);
            $stmt->bindValue(':lgpd_conformidade', $dados['lgpd_conformidade'] ?? 0, \PDO::PARAM_INT);
            $stmt->bindValue(':clausula_confidencialidade', $dados['clausula_confidencialidade'] ?? null);
            $stmt->bindValue(':aviso_previo_rescisao', $dados['aviso_previo_rescisao'] ?? null);
            $stmt->bindValue(':rescisao_descumprimento', $dados['rescisao_descumprimento'] ?? null);
            $stmt->bindValue(':nao_concorrencia', $dados['nao_concorrencia'] ?? null);
            $stmt->bindValue(':indenizacao_rescisao', $dados['indenizacao_rescisao'] ?? null);
            $stmt->bindValue(':causas_rescisao_imotivada', $dados['causas_rescisao_imotivada'] ?? null);
            $stmt->bindValue(':causas_justa_causa', $dados['causas_justa_causa'] ?? null);
            $stmt->bindValue(':obrigacoes_pos_encerramento', $dados['obrigacoes_pos_encerramento'] ?? null);
            $stmt->bindValue(':responsabilidades_contratante', $dados['responsabilidades_contratante'] ?? null);
            $stmt->bindValue(':responsabilidades_contratado', $dados['responsabilidades_contratado'] ?? null);
            $stmt->bindValue(':criterios_aceite', $dados['criterios_aceite'] ?? null);
            $stmt->bindValue(':renovacao_automatica', $dados['renovacao_automatica'] ?? null);
            $stmt->bindValue(':clausulas_adicionais', $dados['clausulas_adicionais'] ?? null);
            $stmt->bindValue(':assinatura_tipo', $dados['assinatura_tipo'] ?? null);
            $stmt->bindValue(':numero_vias', $dados['numero_vias'] ?? null);
            $stmt->bindParam(':observacoes', $observacoes);

            // Faz o bind do documento para INSERT ou para UPDATE (se houver um novo)
            if ($id && $documento_path) { // Apenas para UPDATE se houver novo doc
                $stmt->bindValue(':documento_path', $documento_path, \PDO::PARAM_STR);
            } elseif (!$id) { // Para INSERT, faz o bind (pode ser nulo)
                $stmt->bindValue(':documento_path', $documento_path, $documento_path ? \PDO::PARAM_STR : \PDO::PARAM_NULL);
            }

            if ($stmt->execute()) {
                return $id ?: (int)$this->db->lastInsertId();
            }
            
            return false;
        } catch (\PDOException $e) {
            // Lança a exceção para que o Controller possa capturá-la
            // e exibir uma mensagem de erro mais detalhada.
            throw $e;
        }
    }

    /**
     * Duplica as parcelas financeiras de um contrato para outro.
     * @param int $idOriginal
     * @param int $idNovo
     * @return bool
     */
    public function duplicarParcelas(int $idOriginal, int $idNovo): bool
    {
        try {
            $parcelas = $this->getParcelasByContratoId($idOriginal);
            if (empty($parcelas)) return true;

            // Prepara a query de inserção. Note que status volta para 'Pendente' 
            // e transacao_id é ignorado (NULL) por segurança na cópia.
            $sql = "INSERT INTO contrato_parcelas (contrato_id, descricao, valor, data_vencimento, status, transacao_id) 
                    VALUES (:contrato_id, :descricao, :valor, :data_vencimento, 'Pendente', NULL)";
            $stmt = $this->db->prepare($sql);

            foreach ($parcelas as $p) {
                $stmt->execute([
                    ':contrato_id' => $idNovo,
                    ':descricao' => $p['descricao'],
                    ':valor' => $p['valor'],
                    ':data_vencimento' => $p['data_vencimento']
                ]);
            }
            return true;
        } catch (\PDOException $e) {
            error_log("Erro ao duplicar parcelas do contrato: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Exclui um aditivo e reverte suas alterações no contrato principal.
     * @param int $aditivoId
     * @return bool
     */
    public function excluirAditivo(int $aditivoId): bool
    {
        $aditivo = $this->getAditivoById($aditivoId);
        if (!$aditivo) {
            return false; // Aditivo não existe
        }

        $this->db->beginTransaction();
        try {
            // 1. Reverter as alterações no contrato principal
            $updates = [];
            $params = [':contrato_id' => $aditivo['contrato_id']];

            if (!empty($aditivo['valor_alteracao'])) {
                $updates[] = "valor = valor - :valor_alteracao";
                $params[':valor_alteracao'] = $aditivo['valor_alteracao'];
            }

            // Se o vencimento foi alterado por este aditivo, precisamos encontrar o vencimento anterior
            $vencimentoFinalContrato = $this->getUltimoVencimentoContrato($aditivo['contrato_id'], $aditivoId);
            $updates[] = "vencimento = :vencimento_final";
            $params[':vencimento_final'] = $vencimentoFinalContrato;

            if (!empty($updates)) {
                $sqlContrato = "UPDATE contratos SET " . implode(', ', $updates) . " WHERE id = :contrato_id";
                $this->db->prepare($sqlContrato)->execute($params);
            }

            // 2. Excluir o registro do aditivo
            $stmtDelete = $this->db->prepare("DELETE FROM contratos_aditivos WHERE id = :id");
            $stmtDelete->execute([':id' => $aditivoId]);

            // Opcional: Excluir o arquivo físico do aditivo
            if (!empty($aditivo['documento_path'])) {
                $filePath = ROOT_PATH . '/storage/contratos/aditivos/' . $aditivo['documento_path'];
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }

            $this->db->commit();
            return true;
        } catch (\PDOException $e) {
            $this->db->rollBack();
            error_log("Erro na transação ao excluir aditivo: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Busca o último vencimento válido de um contrato, com base nos aditivos.
     * @param int $contratoId
     * @param int|null $excludeAditivoId ID de um aditivo a ser ignorado na busca (útil para exclusão).
     * @return string
     */
    private function getUltimoVencimentoContrato(int $contratoId, ?int $excludeAditivoId = null): string
    {
        $excludeSql = $excludeAditivoId ? "AND id != " . (int)$excludeAditivoId : "";
        $stmt = $this->db->query("SELECT novo_vencimento FROM contratos_aditivos WHERE contrato_id = {$contratoId} AND novo_vencimento IS NOT NULL {$excludeSql} ORDER BY data_aditivo DESC, id DESC LIMIT 1");
        $ultimoVencimentoAditivo = $stmt->fetchColumn();

        if ($ultimoVencimentoAditivo) {
            return $ultimoVencimentoAditivo;
        }

        // Se nenhum aditivo define o vencimento, busca o do contrato original
        $stmtContrato = $this->db->query("SELECT vencimento FROM contratos WHERE id = {$contratoId}");
        return $stmtContrato->fetchColumn();
    }

    /**
     * Busca contratos com base no seu status de vigência.
     * @param string $categoria Categoria de vigência ('vencidos', 'vencendo_30', etc.)
     * @return array
     */
    public function getContratosPorVigencia(string $categoria): array
    {
        $whereClause = "";
        switch ($categoria) {
            case 'vencidos':
                $whereClause = "c.vencimento < CURDATE() AND c.status = 'Em Vigência'";
                break;
            case 'vencendo_30':
                $whereClause = "c.vencimento BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY) AND c.status = 'Em Vigência'";
                break;
            case 'vencendo_60':
                $whereClause = "c.vencimento BETWEEN DATE_ADD(CURDATE(), INTERVAL 31 DAY) AND DATE_ADD(CURDATE(), INTERVAL 60 DAY) AND c.status = 'Em Vigência'";
                break;
            case 'vencendo_90':
                $whereClause = "c.vencimento BETWEEN DATE_ADD(CURDATE(), INTERVAL 61 DAY) AND DATE_ADD(CURDATE(), INTERVAL 90 DAY) AND c.status = 'Em Vigência'";
                break;
            case 'vigencia_longa':
                $whereClause = "c.vencimento > DATE_ADD(CURDATE(), INTERVAL 90 DAY) AND c.status = 'Em Vigência'";
                break;
            default:
                return []; // Categoria inválida
        }

        try {
            $sql = "SELECT 
                        c.id, 
                        c.numero_contrato,
                        c.numero_contrato_cliente,
                        c.tipo, 
                        COALESCE(cli.nome, forn.razao_social) as parteContratada, 
                        c.valor, 
                        c.data_inicio,
                        c.vencimento,
                        DATEDIFF(c.vencimento, CURDATE()) as dias_para_vencer,
                        c.status
                    FROM contratos c
                    LEFT JOIN clientes cli ON c.cliente_id = cli.id
                    LEFT JOIN pessoas forn ON c.pessoa_id = forn.pessoa_id AND forn.tipo = 'Fornecedor'
                    WHERE {$whereClause}
                    ORDER BY c.vencimento ASC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Erro ao buscar contratos por vigência ({$categoria}): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Busca todas as obrigações de um contrato.
     * @param int $contrato_id
     * @return array
     */
    public function getObrigacoesByContratoId(int $contrato_id): array
    {
        try {
            $sql = "SELECT * FROM contrato_obrigacoes WHERE contrato_id = ? ORDER BY data_prevista ASC, id ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$contrato_id]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Erro ao buscar obrigações do contrato: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Salva uma nova obrigação contratual.
     * @param array $dados
     * @return bool
     */
    public function salvarObrigacao(array $dados): bool
    {
        $sql = "INSERT INTO contrato_obrigacoes (contrato_id, descricao, tipo_clausula, responsavel, data_prevista, status) 
                VALUES (:contrato_id, :descricao, :tipo_clausula, :responsavel, :data_prevista, 'Pendente')";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':contrato_id', $dados['contrato_id'], \PDO::PARAM_INT);
            $stmt->bindValue(':descricao', $dados['descricao']);
            $stmt->bindValue(':tipo_clausula', $dados['tipo_clausula']);
            $stmt->bindValue(':responsavel', $dados['responsavel'] ?: null);
            $stmt->bindValue(':data_prevista', $dados['data_prevista'] ?: null);
            return $stmt->execute();
        } catch (\PDOException $e) {
            error_log("Erro ao salvar obrigação: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Atualiza o status de uma obrigação.
     * @param int $id
     * @param string $status
     * @return bool
     */
    public function updateStatusObrigacao(int $id, string $status): bool
    {
        $sql = "UPDATE contrato_obrigacoes SET status = :status WHERE id = :id";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':status', $status);
            $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
            return $stmt->execute();
        } catch (\PDOException $e) {
            error_log("Erro ao atualizar status da obrigação: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Exclui uma obrigação.
     * @param int $id
     * @return bool
     */
    public function excluirObrigacao(int $id): bool
    {
        $sql = "DELETE FROM contrato_obrigacoes WHERE id = :id";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
            return $stmt->execute();
        } catch (\PDOException $e) {
            error_log("Erro ao excluir obrigação: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Busca uma lista de contratos ativos para a tela de gestão de obrigações.
     * @return array
     */
    public function getContratosAtivosParaObrigacoes(): array
    {
        try {
            $sql = "SELECT 
                        c.id, 
                        c.numero_contrato,
                        c.numero_contrato_cliente,
                        c.objeto,
                        c.tipo, 
                        COALESCE(cli.nome, forn.razao_social) as parteContratada, 
                        c.vencimento,
                        (SELECT COUNT(*) FROM contrato_obrigacoes co WHERE co.contrato_id = c.id) as total_obrigacoes,
                        (SELECT COUNT(*) FROM contrato_obrigacoes co WHERE co.contrato_id = c.id AND co.status = 'Concluída') as obrigacoes_concluidas
                    FROM contratos c
                    LEFT JOIN clientes cli ON c.cliente_id = cli.id
                    LEFT JOIN pessoas forn ON c.pessoa_id = forn.pessoa_id AND forn.tipo = 'Fornecedor'
                    WHERE c.status = 'Em Vigência'
                    GROUP BY c.id
                    ORDER BY c.vencimento ASC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Erro ao buscar contratos ativos para obrigações: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Busca uma lista de contratos ativos para a tela de gestão financeira.
     * @return array
     */
    public function getContratosAtivosParaFinanceiro(): array
    {
        try {
            $sql = "SELECT 
                        c.id, 
                        c.objeto,
                        c.tipo, 
                        COALESCE(cli.nome, forn.razao_social) as parteContratada, 
                        c.vencimento,
                        (SELECT SUM(cp.valor) FROM contrato_parcelas cp WHERE cp.contrato_id = c.id) as valor_previsto
                    FROM contratos c
                    LEFT JOIN clientes cli ON c.cliente_id = cli.id
                    LEFT JOIN pessoas forn ON c.pessoa_id = forn.pessoa_id AND forn.tipo = 'Fornecedor'
                    WHERE c.status = 'Em Vigência'
                    GROUP BY c.id
                    ORDER BY c.vencimento ASC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Erro ao buscar contratos ativos para financeiro: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Busca todas as parcelas de um contrato.
     * @param int $contrato_id
     * @return array
     */
    public function getParcelasByContratoId(int $contrato_id): array
    {
        try {
            $sql = "SELECT * FROM contrato_parcelas WHERE contrato_id = ? ORDER BY data_vencimento ASC, id ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$contrato_id]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Erro ao buscar parcelas do contrato: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Busca uma parcela específica pelo ID.
     * @param int $id
     * @return array|null
     */
    public function getParcelaById(int $id): ?array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM contrato_parcelas WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
        } catch (\PDOException $e) {
            error_log("Erro ao buscar parcela por ID: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Salva uma nova parcela de contrato.
     * @param array $dados
     * @return bool
     */
    public function salvarParcela(array $dados): bool
    {
        $this->db->beginTransaction();
        try {
            // 1. Inserir a nova parcela
            $sqlParcela = "INSERT INTO contrato_parcelas (contrato_id, descricao, valor, data_vencimento, status) 
                           VALUES (:contrato_id, :descricao, :valor, :data_vencimento, 'Pendente')";
            $stmt = $this->db->prepare($sqlParcela);
            $stmt->bindValue(':contrato_id', $dados['contrato_id'], \PDO::PARAM_INT);
            $stmt->bindValue(':descricao', $dados['descricao']);
            $stmt->bindValue(':valor', $dados['valor']);
            $stmt->bindValue(':data_vencimento', $dados['data_vencimento']);

            if (!$stmt->execute()) {
                $this->db->rollBack();
                return false;
            }

            // 2. Atualizar o valor total do contrato principal
            $sqlSoma = "SELECT SUM(valor) FROM contrato_parcelas WHERE contrato_id = :contrato_id";
            $stmtSoma = $this->db->prepare($sqlSoma);
            $stmtSoma->execute([':contrato_id' => $dados['contrato_id']]);
            $novoValorTotal = $stmtSoma->fetchColumn();

            $sqlContrato = "UPDATE contratos SET valor = :valor WHERE id = :contrato_id";
            $this->db->prepare($sqlContrato)->execute([':valor' => $novoValorTotal, ':contrato_id' => $dados['contrato_id']]);

            return $this->db->commit();
        } catch (\PDOException $e) {
            $this->db->rollBack();
            error_log("Erro ao salvar parcela: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Vincula uma transação financeira a uma parcela de contrato.
     * @param int $parcelaId
     * @param int $transacaoId
     * @return bool
     */
    public function vincularTransacao(int $parcelaId, int $transacaoId): bool
    {
        $sql = "UPDATE contrato_parcelas SET transacao_id = :transacao_id, status = 'Lançada' WHERE id = :id";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':transacao_id', $transacaoId, \PDO::PARAM_INT);
            $stmt->bindValue(':id', $parcelaId, \PDO::PARAM_INT);
            return $stmt->execute();
        } catch (\PDOException $e) {
            error_log("Erro ao vincular transação à parcela: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Busca os contratos ativos para a tela de Compliance, incluindo os campos jurídicos.
     *
     * @return array
     */
    public function getContratosParaCompliance(): array
    {
        // Esta query é similar a outras que buscam contratos, mas seleciona os novos campos.
        // Ela junta com clientes e fornecedores para obter o nome da 'parteContratada'.
        $sql = "SELECT 
                    c.id, 
                    c.objeto, 
                    c.tipo,
                    c.clausula_lgpd,
                    c.risco_contratual,
                    COALESCE(cli.nome, forn.razao_social) as parteContratada
                FROM contratos c
                LEFT JOIN clientes cli ON c.cliente_id = cli.id
                LEFT JOIN pessoas forn ON c.pessoa_id = forn.pessoa_id AND forn.tipo = 'Fornecedor'
                WHERE c.status = 'Em Vigência'
                ORDER BY c.data_inicio DESC";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Erro ao buscar contratos para compliance: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Salva os dados de compliance (cláusula LGPD, risco, parecer) de um contrato.
     *
     * @param array $dados
     * @return bool
     */
    public function salvarDadosCompliance(array $dados): bool
    {
        $sql = "UPDATE contratos SET
                    clausula_lgpd = :clausula_lgpd,
                    risco_contratual = :risco_contratual,
                    parecer_juridico = :parecer_juridico
                WHERE id = :id";
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($dados);
        } catch (\PDOException $e) {
            error_log("Erro ao salvar dados de compliance: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Conta o número de contratos agrupados por status.
     * @return array
     */
    public function getContratosCountByStatus(): array
    {
        $sql = "SELECT status, COUNT(*) as total 
                FROM contratos 
                GROUP BY status 
                ORDER BY total DESC";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Erro ao contar contratos por status: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Conta o número de contratos agrupados por tipo.
     * @return array
     */
    public function getContratosCountByType(): array
    {
        $sql = "SELECT tipo, COUNT(*) as total 
                FROM contratos 
                GROUP BY tipo 
                ORDER BY total DESC";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Erro ao contar contratos por tipo: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Soma os valores dos contratos, agrupados por tipo.
     * @return array
     */
    public function getContratosSumValorByType(): array
    {
        $sql = "SELECT tipo, SUM(valor) as total_valor 
                FROM contratos 
                WHERE status = 'Em Vigência'
                GROUP BY tipo 
                ORDER BY total_valor DESC";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Erro ao somar valores de contratos por tipo: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Retorna um resumo do status de todas as obrigações contratuais.
     * @return array
     */
    public function getObrigacoesSummary(): array
    {
        $sql = "SELECT status, COUNT(*) as total FROM contrato_obrigacoes GROUP BY status";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Erro ao buscar resumo de obrigações: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Busca todos os contratos vigentes com os dados necessários para relatórios.
     * @return array
     */
    public function getTodosContratosParaRelatorio(): array
    {
        try {
            $sql = "SELECT 
                        c.id, 
                        c.objeto,
                        c.tipo, 
                        COALESCE(cli.nome, forn.razao_social) as parteContratada, 
                        c.valor,
                        c.data_inicio,
                        c.vencimento,
                        c.status
                    FROM contratos c
                    LEFT JOIN clientes cli ON c.cliente_id = cli.id
                    LEFT JOIN pessoas forn ON c.pessoa_id = forn.pessoa_id AND forn.tipo = 'Fornecedor'
                    WHERE c.status = 'Em Vigência'
                    GROUP BY c.id
                    ORDER BY c.vencimento ASC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Erro ao buscar todos os contratos para relatório: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Exclui um contrato do banco de dados.
     *
     * @param int $id O ID do contrato a ser excluído.
     * @return bool Retorna true em caso de sucesso, false em caso de falha.
     */
    public function excluirContrato(int $id): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM contratos WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (\PDOException $e) {
            error_log("Erro ao excluir contrato: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Atualiza o caminho do documento de um contrato específico.
     *
     * @param int $contratoId O ID do contrato a ser atualizado.
     * @param string $documentoPath O novo nome do arquivo do documento.
     * @return bool Retorna true em caso de sucesso, false em caso de falha.
     */
    public function updateDocumentoPath(int $contratoId, string $documentoPath): bool
    {
        // Primeiro, busca o caminho do documento antigo para poder excluí-lo depois.
        $stmt = $this->db->prepare("SELECT documento_path FROM contratos WHERE id = :id");
        $stmt->execute(['id' => $contratoId]);
        $contratoAntigo = $stmt->fetch();

        // Atualiza o banco de dados com o novo caminho.
        $stmtUpdate = $this->db->prepare("UPDATE contratos SET documento_path = :documento_path WHERE id = :id");
        $success = $stmtUpdate->execute([
            'documento_path' => $documentoPath,
            'id' => $contratoId
        ]);

        // Se a atualização foi bem-sucedida e existia um arquivo antigo, remove-o do servidor.
        if ($success && $contratoAntigo && !empty($contratoAntigo['documento_path'])) {
            $oldFilePath = ROOT_PATH . '/storage/contratos/' . $contratoAntigo['documento_path'];
            if (file_exists($oldFilePath)) {
                unlink($oldFilePath);
            }
        }

        return $success;
    }

    /**
     * Remove o documento de um contrato, apagando o arquivo físico e limpando o campo no banco.
     *
     * @param int $contratoId O ID do contrato.
     * @return bool Retorna true em caso de sucesso, false em caso de falha.
     */
    public function removerDocumento(int $contratoId): bool
    {
        // Busca o caminho do documento para poder excluí-lo.
        $stmt = $this->db->prepare("SELECT documento_path FROM contratos WHERE id = :id");
        $stmt->execute(['id' => $contratoId]);
        $contrato = $stmt->fetch();

        if ($contrato && !empty($contrato['documento_path'])) {
            $filePath = ROOT_PATH . '/storage/contratos/' . $contrato['documento_path'];

            // Apaga o arquivo físico se ele existir.
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        // Atualiza o banco de dados, definindo o caminho do documento como NULL.
        $stmtUpdate = $this->db->prepare("UPDATE contratos SET documento_path = NULL WHERE id = :id");
        return $stmtUpdate->execute(['id' => $contratoId]);
    }

    /**
     * Busca contratos com base em filtros de compliance específicos.
     *
     * @param array $filtros Ex: ['clausula_lgpd' => 'Não', 'risco_contratual' => 'Alto']
     * @return array
     */
    public function getContratosPorFiltroCompliance(array $filtros): array
    {
        $sql = "SELECT 
                    c.id, 
                    c.objeto, 
                    c.tipo, 
                    COALESCE(cli.nome, forn.razao_social) as parteContratada,
                    c.clausula_lgpd,
                    c.risco_contratual
                FROM contratos c
                LEFT JOIN clientes cli ON c.cliente_id = cli.id
                LEFT JOIN pessoas forn ON c.pessoa_id = forn.pessoa_id AND forn.tipo = 'Fornecedor'
                WHERE c.status = 'Em Vigência'";

        if (!empty($filtros['clausula_lgpd'])) {
            $sql .= " AND c.clausula_lgpd = :clausula_lgpd";
        }
        if (!empty($filtros['risco_contratual'])) {
            $sql .= " AND c.risco_contratual = :risco_contratual";
        }
        // Adicionar mais filtros aqui se necessário

        $sql .= " ORDER BY c.id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($filtros);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Conta o número de contratos associados a um cliente específico.
     * @param int $clienteId
     * @return int
     */
    public function countByClienteId(int $clienteId): int
    {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM contratos WHERE cliente_id = ?");
            $stmt->execute([$clienteId]);
            return (int)$stmt->fetchColumn();
        } catch (\PDOException $e) {
            return 0;
        }
    }

    /**
     * Verifica se um documento já existe em Clientes ou Fornecedores.
     */
    public function buscarEntidadePorDocumento(string $doc): ?array
    {
        $doc = preg_replace('/\D/', '', $doc);
        
        // Busca em clientes
        try {
            $stmt = $this->db->prepare("SELECT id, 'Cliente' as tipo_entidade, nome as nome_entidade, email, endereco, telefone FROM clientes WHERE cnpj_cpf = ? LIMIT 1");
            $stmt->execute([$doc]);
            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($res) return $res;
        } catch (\Exception $e) {
            error_log("buscarEntidadePorDocumento (clientes): " . $e->getMessage());
        }

        // Busca na nova tabela de fornecedores (com suporte a JSON)
        try {
            $stmtForn = $this->db->prepare("SELECT 'Fornecedor' as tipo_entidade, nome as nome_entidade, endereco_json, contato_json FROM fornecedores WHERE cnpj_cpf = ? LIMIT 1");
            $stmtForn->execute([$doc]);
            $resForn = $stmtForn->fetch(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("buscarEntidadePorDocumento (fornecedores): " . $e->getMessage());
            $resForn = null;
        }
        
        if ($resForn) {
            $contato = json_decode($resForn['contato_json'] ?? '', true) ?: [];
            $enderecoArr = json_decode($resForn['endereco_json'] ?? '', true) ?: [];
            
            $resForn['email'] = $contato['email_principal'] ?? $contato['email'] ?? '';
            $resForn['telefone'] = $contato['telefone_principal'] ?? $contato['telefone'] ?? '';
            
            $addrPieces = [];
            $logradouroCompleto = [];
            if (!empty($enderecoArr['descricao_tipo_de_logradouro'])) $logradouroCompleto[] = $enderecoArr['descricao_tipo_de_logradouro'];
            if (!empty($enderecoArr['logradouro'])) $logradouroCompleto[] = $enderecoArr['logradouro'];
            
            if (!empty($logradouroCompleto)) {
                $logrStr = implode(' ', $logradouroCompleto);
                if (!empty($enderecoArr['numero']) && $enderecoArr['numero'] !== 'S/N') {
                    $logrStr .= ', ' . $enderecoArr['numero'];
                }
                $addrPieces[] = $logrStr;
            } elseif (!empty($enderecoArr['numero']) && $enderecoArr['numero'] !== 'S/N') {
                $addrPieces[] = $enderecoArr['numero'];
            }
            if (!empty($enderecoArr['complemento'])) $addrPieces[] = $enderecoArr['complemento'];
            if (!empty($enderecoArr['bairro'])) $addrPieces[] = $enderecoArr['bairro'];
            $cityState = [];
            if (!empty($enderecoArr['cidade'])) $cityState[] = $enderecoArr['cidade'];
            if (!empty($enderecoArr['estado'])) $cityState[] = $enderecoArr['estado'];
            if (!empty($cityState)) $addrPieces[] = implode(' - ', $cityState);
            if (!empty($enderecoArr['cep'])) $addrPieces[] = "CEP: " . $enderecoArr['cep'];
            
            $resForn['endereco'] = !empty($addrPieces) ? implode(', ', $addrPieces) : '';
            return $resForn;
        }

        // Fallback para tabela legada 'pessoas'
        try {
            $stmtPessoa = $this->db->prepare("SELECT 'Fornecedor' as tipo_entidade, razao_social as nome_entidade FROM pessoas WHERE (cnpj_cpf = ? OR cnpj = ?) AND tipo = 'Fornecedor' LIMIT 1");
            $stmtPessoa->execute([$doc, $doc]);
            $res = $stmtPessoa->fetch(PDO::FETCH_ASSOC);
            if ($res) {
                $res['email'] = ''; $res['endereco'] = '';
                return $res;
            }
        } catch (\Exception $e) {
            error_log("buscarEntidadePorDocumento (pessoas legada): " . $e->getMessage());
        }
        return null;
    }

    /**
     * Cria um novo contrato automaticamente a partir de uma proposta aprovada.
     * 
     * @param int $propostaId
     * @return bool
     */
    public function criarContratoDeProposta(int $propostaId): bool
    {
        try {
            // 1. Busca os dados completos da proposta
            $propostaModel = new PropostaModel();
            $proposta = $propostaModel->getPropostaById($propostaId);
            
            if (!$proposta) return false;

            // 2. Verifica se já existe um contrato para esta proposta (evita duplicidade)
            $tagRef = "[Ref: Proposta #" . ($proposta['numero_proposta'] ?? $proposta['id']) . "]";
            $stmtCheck = $this->db->prepare("SELECT id FROM contratos WHERE objeto LIKE :ref LIMIT 1");
            $stmtCheck->execute([':ref' => "%$tagRef%"]);
            if ($stmtCheck->fetch()) return true; // Já existe, ignora para não duplicar

            // 3. Busca dados da empresa (Contratada) para o cabeçalho do contrato
            $empresaModel = new EmpresaModel();
            $empresa = $empresaModel->getDadosEmpresa();

            // 4. Mapeia os dados da Proposta para os campos do Contrato
            $dadosContrato = [
                'titulo' => $proposta['nome_proposta'] ?? 'Contrato de Prestação de Serviços',
                'projeto_id' => $proposta['projeto_id'] ?? null,
                'proposta_id' => $propostaId,
                'objeto' => ($proposta['descricao'] ?? '') . "\n\n" . ($proposta['objetivo'] ?? '') . "\n\n" . $tagRef,
                'tipo' => 'Venda',
                'status' => 'Em Elaboração', // Inicia em elaboração para revisão jurídica final
                'valor' => $proposta['total_final'] ?? 0,
                'forma_pagamento' => $proposta['forma_pagamento'] ?? '',
                'data_inicio' => date('Y-m-d'),
                'observacoes' => "Gerado automaticamente via Módulo Comercial.\nProposta: " . ($proposta['numero_proposta'] ?? $proposta['id']),
                
                // Dados do Cliente (Contratante)
                'contratante_nome' => $proposta['cliente_nome'] ?? '',
                'contratante_documento' => $proposta['cliente_documento'] ?? '',
                'contratante_endereco' => $proposta['cliente_endereco'] ?? '',
                'contratante_email' => $proposta['cliente_email'] ?? '',

                // Dados da Sua Empresa (Contratado)
                'contratado_nome' => $empresa['razao_social'] ?? '',
                'contratado_documento' => $empresa['cnpj'] ?? '',
                'contratado_endereco' => $empresa['endereco'] ?? '',
                'contratado_email' => $empresa['email'] ?? '',
            ];

            return $this->salvarContrato($dadosContrato);
        } catch (\Exception $e) {
            error_log("Erro ao criar contrato automático: " . $e->getMessage());
            return false;
        }
    }
}
