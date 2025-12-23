<?php

namespace App\Models;

use App\Core\Model;
use PDO;
use PDOException;
use App\Models\TreinamentosModel; // Importa o novo model
class RhModel extends Model
{
    /**
     * Armazena a última mensagem de erro do model para diagnóstico.
     * @var string|null
     */
    private $lastError = null;

    public function __construct()
    {
        parent::__construct();
        // Garante que a tabela de perfis exista ao instanciar o model.
        // Isso evita DDL (Data Definition Language) dentro de transações.
        $this->ensurePerfisTableExists();
    }

    /**
     * Busca um resumo dos dados de RH (total de funcionários, férias, etc.).
     */
    public function getRhSummaryData()
    {
        try {
            // LÓGICA RESTAURADA: A contagem de funcionários volta a usar a fonte original
            // para garantir consistência com a lista.
            $totalFuncionarios = $this->db->query("SELECT COUNT(*) FROM colaboradores WHERE JSON_UNQUOTE(JSON_EXTRACT(beneficios_json, '$.status')) = 'Ativo'")->fetchColumn();
            $funcionariosFerias = $this->db->query("SELECT COUNT(*) FROM colaboradores WHERE status_ferias = 'Em Férias' AND CURDATE() BETWEEN data_inicio_ferias AND data_fim_ferias")->fetchColumn();
            $novasContratacoesMes = $this->db->query("SELECT COUNT(*) FROM colaboradores WHERE data_admissao >= DATE_FORMAT(NOW(), '%Y-%m-01')")->fetchColumn();

            // Busca o próximo treinamento dinamicamente
            $treinamentosModel = new TreinamentosModel();
            $proximoTreinamento = $treinamentosModel->getProximoTreinamento();
            $proximoTreinamentoFormatado = 'Nenhum agendado';
            if ($proximoTreinamento) {
                $proximoTreinamentoFormatado = $proximoTreinamento['nome_treinamento'] . ' (' . date('d/m/Y', strtotime($proximoTreinamento['data_prevista'])) . ')';
            }

            return [
                'totalFuncionarios' => (int) $totalFuncionarios,
                'funcionariosFerias' => (int) $funcionariosFerias,
                'novasContratacoesMes' => (int) $novasContratacoesMes,
                'proximoTreinamento' => $proximoTreinamentoFormatado,
            ];
        } catch (PDOException $e) {
            error_log("Erro ao buscar resumo de RH: " . $e->getMessage());
            return ['totalFuncionarios' => 0, 'funcionariosFerias' => 0, 'novasContratacoesMes' => 0, 'proximoTreinamento' => 'N/A'];
        }
    }

    /**
     * Busca a lista de aniversariantes da semana.
     */
    public function getAniversariantesSemana()
    {
        try {
            // Query para buscar aniversariantes da semana atual (considerando a semana começando na segunda-feira)
            // e que estejam com status 'Ativo'.
            $sql = "SELECT 
                        nome, 
                        DATE_FORMAT(data_nascimento, '%d/%m') as data, 
                        JSON_UNQUOTE(JSON_EXTRACT(beneficios_json, '$.setor')) as setor
                    FROM 
                        colaboradores 
                    WHERE 
                        JSON_UNQUOTE(JSON_EXTRACT(beneficios_json, '$.status')) = 'Ativo' AND
                        WEEK(data_nascimento, 1) = WEEK(CURDATE(), 1)
                    ORDER BY 
                        MONTH(data_nascimento), DAY(data_nascimento)";
            return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar aniversariantes da semana: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Busca a lista de todos os funcionários ativos.
     * @param array $filtros Filtros de busca (ex: ['nome' => '...', 'setor' => '...'])
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getFuncionarios(array $filtros = [], int $limit = 5, int $offset = 0): array
    {
        // LÓGICA RESTAURADA: A busca de funcionários volta a usar a query original e funcional.
        $sql = "SELECT c.colaborador_id as id, c.nome, JSON_UNQUOTE(JSON_EXTRACT(c.beneficios_json, '$.cargo')) as cargo, JSON_UNQUOTE(JSON_EXTRACT(c.beneficios_json, '$.setor')) as setor, JSON_UNQUOTE(JSON_EXTRACT(c.beneficios_json, '$.status')) as status FROM colaboradores c WHERE 1=1";
        $params = [];

        if (!empty($filtros['nome'])) {
            $sql .= " AND c.nome LIKE :nome";
            $params[':nome'] = '%' . $filtros['nome'] . '%';
        }

        if (!empty($filtros['setor'])) {
            $sql .= " AND JSON_UNQUOTE(JSON_EXTRACT(c.beneficios_json, '$.setor')) LIKE :setor";
            $params[':setor'] = '%' . $filtros['setor'] . '%';
        }

        if (!empty($filtros['status'])) {
            $sql .= " AND JSON_UNQUOTE(JSON_EXTRACT(c.beneficios_json, '$.status')) = :status";
            $params[':status'] = $filtros['status'];
        }

        $sql .= " ORDER BY c.nome ASC LIMIT :limit OFFSET :offset";

        try {
            $stmt = $this->db->prepare($sql);
            // CORREÇÃO: A forma mais segura de lidar com filtros dinâmicos e paginação
            // é associar cada parâmetro individualmente. bindParam para os filtros
            // e bindValue para os valores de LIMIT e OFFSET, que devem ser tratados como inteiros.
            // A chamada a execute() deve ser feita SEM argumentos neste caso.
            foreach ($params as $key => &$val) {
                $stmt->bindParam($key, $val);
            }
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar funcionários com paginação: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Conta o número total de funcionários que correspondem a um filtro.
     * @param array $filtros
     * @return int
     */
    public function getFuncionariosCount(array $filtros = []): int
    {
        // LÓGICA RESTAURADA: A contagem de funcionários volta a usar a query original.
        $sql = "SELECT COUNT(*) FROM colaboradores WHERE 1=1";
        $params = [];

        if (!empty($filtros['nome'])) {
            $sql .= " AND nome LIKE :nome";
            $params[':nome'] = '%' . $filtros['nome'] . '%';
        }

        if (!empty($filtros['setor'])) {
            $sql .= " AND JSON_UNQUOTE(JSON_EXTRACT(beneficios_json, '$.setor')) LIKE :setor";
            $params[':setor'] = '%' . $filtros['setor'] . '%';
        }

        if (!empty($filtros['status'])) {
            $sql .= " AND JSON_UNQUOTE(JSON_EXTRACT(beneficios_json, '$.status')) = :status";
            $params[':status'] = $filtros['status'];
        }

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Erro ao contar funcionários: " . $e->getMessage());
            return 0;
        }
    }



    /**
     * Busca um funcionário específico pelo ID.
     * @param int $id
     * @return array|null
     */
    public function getFuncionarioById(int $id): ?array
    {
        try {
            // Junta as tabelas para buscar dados do colaborador e do usuário de uma só vez.
            $sql = "SELECT c.*, u.email, c.colaborador_id as id 
                    FROM colaboradores c
                    LEFT JOIN usuarios u ON c.colaborador_id = u.id
                    WHERE c.colaborador_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            $funcionario = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($funcionario && !empty($funcionario['beneficios_json'])) {
                $dadosExtras = json_decode($funcionario['beneficios_json'], true);
                if (is_array($dadosExtras)) {
                    $funcionario = array_merge($funcionario, $dadosExtras);
                }
            }

            return $funcionario ?: null;
        } catch (PDOException $e) {
            error_log("Erro ao buscar funcionário por ID: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Verifica se um e-mail já está em uso por outro usuário.
     * @param string $email O e-mail a ser verificado.
     * @param int|null $excludeId O ID do usuário a ser ignorado na verificação (útil na edição).
     * @return bool Retorna true se o e-mail já existe, false caso contrário.
     */
    public function emailExists(string $email, ?int $excludeId = null): bool
    {
        // Lança uma exceção personalizada em vez de retornar um booleano,
        // para que o Controller possa capturar uma mensagem de erro específica.
        // Isso centraliza a lógica de verificação de e-mail aqui.
        if (empty($email)) {
            throw new PDOException("O e-mail não pode ser vazio.");
        }

        $sql = "SELECT COUNT(*) FROM usuarios WHERE email = :email";
        if ($excludeId) {
            $sql .= " AND id != :excludeId";
        }
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':email', $email);
        if ($excludeId) $stmt->bindValue(':excludeId', $excludeId, PDO::PARAM_INT);
        $stmt->execute();
        if ($stmt->fetchColumn() > 0) {
            throw new PDOException("E-mail já cadastrado.");
        }
        return false; // Retorna false se o e-mail não existe
    }

    /**
     * Exclui um funcionário (simulação).
     * Em um cenário real, executaria um DELETE no banco de dados.
     * @param int $id
     * @return bool
     */
    public function excluirFuncionario(int $id): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM colaboradores WHERE colaborador_id = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Erro ao excluir funcionário: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Simula o cálculo da folha de pagamento para um determinado mês/ano.
     * Em um cenário real, esta função buscaria os funcionários, seus salários,
     * buscaria os eventos (horas extras, faltas) e aplicaria as regras de cálculo.
     *
     * @param int $mes
     * @param int $ano
     * @return bool
     */
    public function calcularFolhaDePagamento(int $mes, int $ano): bool
    {
        // Apenas simula que o cálculo foi feito.
        // Em um sistema real, aqui ocorreria a inserção dos resultados em uma tabela `folha_pagamento_resultados`.
        error_log("Simulação: Calculando folha de pagamento para $mes/$ano.");
        return true;
    }

    /**
     * Simula a busca dos resultados de uma folha de pagamento já calculada.
     *
     * @param int $mes
     * @param int $ano
     * @return array
     */
    public function getFolhaCalculada(int $mes, int $ano): array
    {
        // Mock de resultados
        return [
            ['id' => 1, 'nome' => 'Carlos Silva', 'salario_bruto' => 8500.00, 'inss' => 900.00, 'irrf' => 1256.40, 'outros_descontos' => 150.00, 'salario_liquido' => 6193.60],
            ['id' => 2, 'nome' => 'Mariana Costa', 'salario_bruto' => 4500.00, 'inss' => 482.57, 'irrf' => 302.80, 'outros_descontos' => 50.00, 'salario_liquido' => 3664.63],
            ['id' => 4, 'nome' => 'Beatriz Almeida', 'salario_bruto' => 3500.00, 'inss' => 340.78, 'irrf' => 134.70, 'outros_descontos' => 25.00, 'salario_liquido' => 2999.52],
        ];
    }

    /**
     * Simula a busca dos dados para a geração de um ou mais holerites.
     *
     * @param int $mes
     * @param int $ano
     * @param int|null $funcionario_id
     * @return array
     */
    public function getDadosHolerite(int $mes, int $ano, int $funcionario_id = null): array
    {
        // Mock de dados de holerite
        $holeriteExemplo = [
            'info_empresa' => ['nome' => 'EnviCorp Soluções Ambientais', 'cnpj' => '12.345.678/0001-99'],
            'competencia' => str_pad($mes, 2, '0', STR_PAD_LEFT) . '/' . $ano,
            'info_funcionario' => ['nome' => 'Carlos Silva', 'cargo' => 'Gerente de Projetos', 'setor' => 'Projetos'],
            'proventos' => [
                ['codigo' => '101', 'descricao' => 'Salário Base', 'valor' => 8000.00],
                ['codigo' => '105', 'descricao' => 'Horas Extras 50%', 'valor' => 500.00],
            ],
            'descontos' => [
                ['codigo' => '201', 'descricao' => 'INSS sobre Salário', 'percentual' => 11.00, 'valor' => 900.00],
                ['codigo' => '202', 'descricao' => 'IRRF sobre Salário', 'percentual' => 27.50, 'valor' => 1256.40],
                ['codigo' => '205', 'descricao' => 'Vale Transporte', 'percentual' => 6.00, 'valor' => 150.00],
            ],
            'totais' => [
                'bruto' => 8500.00,
                'descontos' => 2306.40,
                'liquido' => 6193.60,
            ],
            'bases' => [
                'base_inss' => 8181.81, // Exemplo
                'base_fgts' => 8500.00,
                'fgts_mes' => 680.00,
                'base_irrf' => 7281.81, // Exemplo
            ]
        ];

        if ($funcionario_id) {
            // Se um ID foi fornecido, retorna apenas um holerite (mock)
            return [$holeriteExemplo];
        }

        // Se nenhum ID foi fornecido, retorna uma lista de holerites (mock para lote)
        $holerite2 = $holeriteExemplo;
        $holerite2['info_funcionario']['nome'] = 'Mariana Costa';
        $holerite2['info_funcionario']['cargo'] = 'Analista Financeiro';

        return [$holeriteExemplo, $holerite2];
    }

    /**
     * Simula a busca e consolidação dos encargos de uma folha de pagamento.
     * Em um cenário real, esta função somaria os valores de INSS, FGTS e IRRF
     * da tabela de holerites para a competência especificada.
     *
     * @param int $mes
     * @param int $ano
     * @return array
     */
    public function getEncargosCalculados(int $mes, int $ano): array
    {
        // Correção: Utiliza a folha já calculada para obter os salários brutos corretos.
        $folhaCalculada = $this->getFolhaCalculada($mes, $ano);

        $totalINSS = 0;
        $totalFGTS = 0;
        $totalIRRF = 0;
        $totalBruto = 0;

        // Itera sobre os resultados da folha calculada para somar os encargos.
        foreach ($folhaCalculada as $funcionario) {
            $salarioBruto = $funcionario['salario_bruto'];
            $totalBruto += $salarioBruto;
            $totalINSS += $funcionario['inss'];
            $totalIRRF += $funcionario['irrf'];
            $totalFGTS += $salarioBruto * 0.08;
        }

        return [
            'total_inss' => $totalINSS,
            'total_fgts' => $totalFGTS,
            'total_irrf' => $totalIRRF,
            'base_fgts' => $totalBruto,
            'competencia' => str_pad($mes, 2, '0', STR_PAD_LEFT) . '/' . $ano,
            'vencimento_fgts' => '07/' . str_pad($mes + 1, 2, '0', STR_PAD_LEFT) . '/' . $ano, // Simulação
            'vencimento_inss' => '20/' . str_pad($mes + 1, 2, '0', STR_PAD_LEFT) . '/' . $ano, // Simulação
        ];
    }

    /**
     * Consolida os dados da folha de pagamento para exportação contábil.
     * Em um cenário real, as contas contábeis seriam configuráveis.
     *
     * @param int $mes
     * @param int $ano
     * @return array
     */
    public function getDadosExportacaoContabil(int $mes, int $ano): array
    {
        // Reutiliza a lógica de getFolhaCalculada para obter os totais por funcionário
        $folhaCalculada = $this->getFolhaCalculada($mes, $ano);

        $totalBruto = array_sum(array_column($folhaCalculada, 'salario_bruto'));
        $totalINSS = array_sum(array_column($folhaCalculada, 'inss'));
        $totalIRRF = array_sum(array_column($folhaCalculada, 'irrf'));
        $totalOutrosDescontos = array_sum(array_column($folhaCalculada, 'outros_descontos'));
        $totalLiquido = array_sum(array_column($folhaCalculada, 'salario_liquido'));

        // O FGTS não é descontado do funcionário, é um encargo da empresa.
        // A base é o salário bruto.
        $totalFGTS = $totalBruto * 0.08;

        $competencia = str_pad($mes, 2, '0', STR_PAD_LEFT) . '/' . $ano;

        // Monta a estrutura para exportação, simulando lançamentos contábeis
        $exportData = [
            // Lançamento da despesa com salários
            ['competencia' => $competencia, 'tipo' => 'Despesa', 'descricao' => 'Salários e Ordenados', 'valor' => $totalBruto, 'conta_debito' => '3.1.1.01.001', 'conta_credito' => '2.1.1.01.001'],

            // Lançamento da provisão para pagamento dos salários líquidos
            ['competencia' => $competencia, 'tipo' => 'Provisão', 'descricao' => 'Salários a Pagar', 'valor' => $totalLiquido, 'conta_debito' => '2.1.1.01.001', 'conta_credito' => '1.1.1.01.001'],

            // Lançamento da provisão dos encargos
            ['competencia' => $competencia, 'tipo' => 'Provisão', 'descricao' => 'INSS a Recolher', 'valor' => $totalINSS, 'conta_debito' => '2.1.1.01.001', 'conta_credito' => '2.1.2.01.002'],
            ['competencia' => $competencia, 'tipo' => 'Provisão', 'descricao' => 'IRRF a Recolher', 'valor' => $totalIRRF, 'conta_debito' => '2.1.1.01.001', 'conta_credito' => '2.1.2.01.003'],

            // Lançamento da despesa com FGTS
            ['competencia' => $competencia, 'tipo' => 'Despesa', 'descricao' => 'Despesa com FGTS', 'valor' => $totalFGTS, 'conta_debito' => '3.1.1.01.002', 'conta_credito' => '2.1.2.01.001'],

            // Lançamento da provisão do FGTS
            ['competencia' => $competencia, 'tipo' => 'Provisão', 'descricao' => 'FGTS a Recolher', 'valor' => $totalFGTS, 'conta_debito' => '2.1.2.01.001', 'conta_credito' => '2.1.2.01.001'],
        ];

        return $exportData;
    }

    /**
     * Salva um novo funcionário ou atualiza um existente na tabela 'colaboradores'.
     *
     * @param array $dados Os dados do funcionário vindos do formulário.
     * @return bool Retorna true em caso de sucesso, false em caso de falha.
     */
    public function salvarFuncionario(array $dados): bool
    {
        // Validações básicas de entrada para garantir a integridade dos dados.
        if (empty(trim($dados['nome'] ?? ''))) {
            error_log("Erro de validação ao salvar funcionário: Nome é obrigatório.");
            return false;
        }
        if (empty(trim($dados['email'] ?? ''))) {
            error_log("Erro de validação ao salvar funcionário: E-mail é obrigatório.");
            return false;
        }
        if (empty(trim($dados['cargo'] ?? ''))) {
            error_log("Erro de validação ao salvar funcionário: Cargo é obrigatório.");
            return false;
        }

        // 1. Separa os dados que têm colunas dedicadas na tabela 'colaboradores'
        $id = !empty($dados['id']) ? (int)$dados['id'] : null;
        $nome = trim($dados['nome'] ?? '');
        $data_nascimento = !empty($dados['data_nascimento']) ? $dados['data_nascimento'] : null;
        $data_admissao = !empty($dados['data_admissao']) ? $dados['data_admissao'] : null;
        $email = trim($dados['email'] ?? ''); // Captura o e-mail em uma variável dedicada
        $telefone = !empty(trim($dados['celular'] ?? '')) ? trim($dados['celular']) : null;
        $endereco = !empty(trim($dados['endereco'] ?? '')) ? trim($dados['endereco']) : null;
        $salario = !empty($dados['salario']) ? (float)str_replace([','], ['.'], $dados['salario']) : null;

        // Define um status padrão para o funcionário, já que o formulário não envia este campo.
        $status = 'Ativo';

        try {
            // Validação de e-mail duplicado movida para dentro do try-catch,
            // mas ANTES de iniciar a transação.
            $this->emailExists($email, $id);

            // Inicia a transação APÓS as validações principais.
            // Isso evita o erro "no active transaction" se uma validação falhar.
            $this->db->beginTransaction();

            // DEBUG: Log para verificar os dados de entrada da operação.
            error_log("Tentando salvar funcionário - ID: " . ($id ?: 'NOVO'));
            error_log("Dados principais: Nome: $nome, Email: $email");

            $usuario_id = $id;

            // --- Lógica para garantir que Cargo e Perfil existam (para INSERT e UPDATE) ---

            // 1. Garante que o CARGO exista e obtém o ID
            $cargoNome = $dados['cargo'] ?? 'Não Definido';
            $stmtCargo = $this->db->prepare("SELECT cargo_id FROM cargos WHERE nome_cargo = :nome_cargo");
            $stmtCargo->execute([':nome_cargo' => $cargoNome]);
            $cargo_id = $stmtCargo->fetchColumn();
            if (!$cargo_id) {
                $stmtInsCargo = $this->db->prepare("INSERT INTO cargos (nome_cargo) VALUES (:nome_cargo)");
                $stmtInsCargo->execute([':nome_cargo' => $cargoNome]);
                $cargo_id = $this->db->lastInsertId();
            }

            // 2. Garante que o PERFIL exista e obtém o ID
            $perfilNome = 'Usuário Básico'; // Perfil padrão para funcionários

            $stmtPerfil = $this->db->prepare("SELECT perfil_id FROM perfis_acesso WHERE nome_perfil = :nome_perfil");
            $stmtPerfil->execute([':nome_perfil' => $perfilNome]);
            $perfil_id = $stmtPerfil->fetchColumn();
            if (!$perfil_id) {
                $stmtInsPerfil = $this->db->prepare("INSERT INTO perfis_acesso (nome_perfil) VALUES (:nome_perfil)");
                $stmtInsPerfil->execute([':nome_perfil' => $perfilNome]);
                $perfil_id = $this->db->lastInsertId();
            }

            // --- ETAPA 1: ATUALIZAR OU CRIAR O USUÁRIO ---
            if ($id) {
                // UPDATE: Atualiza o usuário e o colaborador existentes
                $sqlUsuario = "UPDATE usuarios SET nome = :nome, email = :email, cargo_id = :cargo_id, status = :status WHERE id = :id";
                $stmtUsuario = $this->db->prepare($sqlUsuario);
                $stmtUsuario->bindValue(':nome', $nome);
                $stmtUsuario->bindValue(':email', $email); // Usa a variável dedicada
                $stmtUsuario->bindValue(':cargo_id', $cargo_id, PDO::PARAM_INT);
                $stmtUsuario->bindValue(':status', $dados['status'] ?? 'Ativo');
                $stmtUsuario->bindValue(':id', $id, PDO::PARAM_INT);
                $stmtUsuario->execute();
                if ($stmtUsuario->rowCount() === 0) {
                    error_log("AVISO: Nenhuma linha afetada na atualização do usuário ID: $id. Isso pode ocorrer se os dados forem idênticos.");
                }
            } else {
                // 3. INSERT: Cria um novo usuário com os IDs corretos
                $senhaPadrao = 'Mudar@123'; // Senha temporária segura
                $senhaHash = password_hash($senhaPadrao, PASSWORD_DEFAULT);

                $sqlUsuario = "INSERT INTO usuarios (nome, email, senha_hash, cargo_id, perfil_id, status) VALUES (:nome, :email, :senha_hash, :cargo_id, :perfil_id, :status)";
                $stmtUsuario = $this->db->prepare($sqlUsuario);
                $stmtUsuario->bindValue(':nome', $nome);
                $stmtUsuario->bindValue(':email', $email); // Usa a variável dedicada
                $stmtUsuario->bindValue(':senha_hash', $senhaHash); // Campo obrigatório
                $stmtUsuario->bindValue(':cargo_id', $cargo_id, PDO::PARAM_INT);
                $stmtUsuario->bindValue(':perfil_id', $perfil_id, PDO::PARAM_INT);
                $stmtUsuario->bindValue(':status', $status);
                $stmtUsuario->execute();
                $usuario_id = $this->db->lastInsertId(); // Pega o ID do usuário recém-criado
            }

            // 2. Prepara o JSON com os dados restantes do formulário.
            // Adiciona o status ao array de dados para que ele seja salvo no JSON.
            unset($dados['id'], $dados['nome'], $dados['data_nascimento'], $dados['data_admissao'], $dados['email'], $dados['celular'], $dados['endereco'], $dados['salario']);
            $dados['status'] = $status;
            $beneficios_json = json_encode($dados);

            // --- ETAPA 2: ATUALIZAR OU CRIAR O COLABORADOR ---
            if ($id) {
                $sqlColaborador = "UPDATE colaboradores SET nome = :nome, data_nascimento = :data_nascimento, data_admissao = :data_admissao, telefone = :telefone, endereco = :endereco, salario = :salario, beneficios_json = :beneficios_json WHERE colaborador_id = :id";
            } else {
                $sqlColaborador = "INSERT INTO colaboradores (colaborador_id, nome, data_nascimento, data_admissao, telefone, endereco, salario, beneficios_json) VALUES (:id, :nome, :data_nascimento, :data_admissao, :telefone, :endereco, :salario, :beneficios_json)";
            }
            $stmt = $this->db->prepare($sqlColaborador);

            // Associa os parâmetros para a query de colaborador (INSERT ou UPDATE)
            $stmt->bindValue(':id', $usuario_id, PDO::PARAM_INT);
            $stmt->bindParam(':nome', $nome);
            $stmt->bindValue(':data_nascimento', $data_nascimento, $data_nascimento === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $stmt->bindValue(':data_admissao', $data_admissao, $data_admissao === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $stmt->bindValue(':telefone', $telefone, $telefone === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $stmt->bindValue(':endereco', $endereco, $endereco === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $stmt->bindValue(':salario', $salario, $salario === null ? PDO::PARAM_NULL : PDO::PARAM_STR); // Salário como string para PDO lidar com decimal
            $stmt->bindValue(':beneficios_json', $beneficios_json, PDO::PARAM_STR);
            $stmt->execute();
            if ($stmt->rowCount() === 0) {
                // Para INSERT, rowCount pode ser 0 em alguns drivers se não retornar linhas. Para UPDATE, é um aviso útil.
                error_log("AVISO: Nenhuma linha afetada na operação do colaborador para o ID: $usuario_id.");
            }

            // Confirma a transação e verifica se o commit foi bem-sucedido.
            $this->db->commit();
            error_log("SUCESSO: Funcionário salvo com sucesso - ID final: " . ($usuario_id ?: $id));
            return true;
        } catch (PDOException $e) {
            // Se algo deu errado, desfaz a transação
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            // Log detalhado do erro, incluindo o stack trace para diagnóstico completo.
            $this->lastError = $e->getMessage();
            error_log("ERRO CRÍTICO ao salvar funcionário: " . $this->lastError);
            error_log("Stack trace: " . $e->getTraceAsString());
            return false;
        }
    }

    /**
     * Retorna a última mensagem de erro ocorrida no model.
     * @return string|null
     */
    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    /**
     * Busca e consolida os dados para o relatório "Espelho da Folha".
     *
     * @param int $mes
     * @param int $ano
     * @return array
     */
    public function getDadosEspelhoFolha(int $mes, int $ano): array
    {
        // Reutiliza a busca da folha calculada
        $funcionarios = $this->getFolhaCalculada($mes, $ano);

        // Busca também os encargos calculados para adicionar ao relatório
        $encargos = $this->getEncargosCalculados($mes, $ano);

        // Calcula os totais
        $totais = [
            'salario_bruto' => array_sum(array_column($funcionarios, 'salario_bruto')),
            'inss' => array_sum(array_column($funcionarios, 'inss')),
            'irrf' => array_sum(array_column($funcionarios, 'irrf')),
            'outros_descontos' => array_sum(array_column($funcionarios, 'outros_descontos')),
            'salario_liquido' => array_sum(array_column($funcionarios, 'salario_liquido')),
        ];

        // Mock de informações da empresa
        $info_empresa = [
            'nome' => 'EnviCorp Soluções Ambientais',
            'cnpj' => '12.345.678/0001-99',
            'endereco' => 'Rua Exemplo, 123 - Centro, Cidade - UF'
        ];

        // Monta a estrutura final para o relatório
        return [
            'info_empresa' => $info_empresa,
            'competencia' => str_pad($mes, 2, '0', STR_PAD_LEFT) . '/' . $ano,
            'data_emissao' => date('d/m/Y'),
            'funcionarios' => $funcionarios,
            'totais' => $totais,
            'encargos' => $encargos // Adiciona os encargos aos dados do relatório
        ];
    }

    /**
     * Busca todos os cargos para preencher listas de seleção.
     * @return array
     */
    public function getAllCargos(): array
    {
        try {
            $stmt = $this->db->query("SELECT cargo_id, nome_cargo FROM cargos ORDER BY nome_cargo ASC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar todos os cargos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Garante que a tabela `perfis` exista no banco. Se não existir, tenta criá-la.
     * Este método é pensado para facilitar ambientes de desenvolvimento onde o schema
     * ainda não foi criado. Em produção, prefira executar migrations/DDL apropriadas.
     *
     * @return void
     */
    private function ensurePerfisTableExists(): void
    {
        // CORREÇÃO: O nome da tabela deve ser 'perfis_acesso' para corresponder à FK.
        try {
            $sql = "CREATE TABLE IF NOT EXISTS perfis_acesso (
                perfil_id INT AUTO_INCREMENT PRIMARY KEY,
                nome_perfil VARCHAR(150) NOT NULL UNIQUE,
                descricao TEXT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

            $this->db->exec($sql);
        } catch (PDOException $e) {
            error_log('Não foi possível garantir existência da tabela perfis_acesso: ' . $e->getMessage());
        }
    }

    /**
     * Calcula os valores devidos para as férias de um funcionário.
     * @param int $funcionarioId
     * @param string $dataInicio
     * @param int $diasFerias
     * @return array|null
     */
    public function calcularValoresFerias(int $funcionarioId, string $dataInicio, int $diasFerias): ?array
    {
        $funcionario = $this->getFuncionarioById($funcionarioId);
        if (!$funcionario || empty($funcionario['salario'])) {
            return null;
        }

        $salarioBase = (float) $funcionario['salario'];
        $valorFerias = $salarioBase;
        $tercoConstitucional = $valorFerias / 3;
        $totalBruto = $valorFerias + $tercoConstitucional;

        // Cálculo simples de INSS (tabela 2024 - simplificado)
        $inss = 0;
        if ($totalBruto <= 1412.00) $inss = $totalBruto * 0.075;
        elseif ($totalBruto <= 2666.68) $inss = $totalBruto * 0.09;
        elseif ($totalBruto <= 4000.03) $inss = $totalBruto * 0.12;
        elseif ($totalBruto <= 7786.02) $inss = $totalBruto * 0.14;
        else $inss = 908.85; // Teto

        // Cálculo simples de IRRF (tabela 2024 - simplificado)
        $baseIrrf = $totalBruto - $inss;
        $irrf = 0;
        if ($baseIrrf > 2112.00 && $baseIrrf <= 2826.65) $irrf = ($baseIrrf * 0.075) - 158.40;
        elseif ($baseIrrf > 2826.65 && $baseIrrf <= 3751.05) $irrf = ($baseIrrf * 0.15) - 370.40;
        elseif ($baseIrrf > 3751.05 && $baseIrrf <= 4664.68) $irrf = ($baseIrrf * 0.225) - 651.73;
        elseif ($baseIrrf > 4664.68) $irrf = ($baseIrrf * 0.275) - 884.96;

        $totalDescontos = $inss + $irrf;
        $valorLiquido = $totalBruto - $totalDescontos;

        $dataFim = date('Y-m-d', strtotime($dataInicio . " + " . ($diasFerias - 1) . " days"));

        return [
            'funcionario' => [
                'id' => $funcionario['id'],
                'nome' => $funcionario['nome'],
                'cargo' => $funcionario['cargo'],
                'setor' => $funcionario['setor'],
            ],
            'periodo' => [
                'data_inicio' => $dataInicio,
                'data_fim' => $dataFim,
                'dias' => $diasFerias,
            ],
            'valores' => [
                'salario_base' => $salarioBase,
                'valor_ferias' => $valorFerias,
                'terco_constitucional' => $tercoConstitucional,
                'total_bruto' => $totalBruto,
                'inss' => $inss,
                'irrf' => $irrf,
                'total_descontos' => $totalDescontos,
                'valor_liquido' => $valorLiquido,
            ]
        ];
    }

    /**
     * Busca os dados necessários para gerar o aviso de férias.
     * @param int $funcionarioId
     * @param string $dataInicio
     * @param int $diasFerias
     * @return array|null
     */
    public function getDadosParaAvisoFerias(int $funcionarioId, string $dataInicio, int $diasFerias): ?array
    {
        $funcionario = $this->getFuncionarioById($funcionarioId);
        if (!$funcionario) {
            return null;
        }

        // Busca dados da empresa (mock, idealmente viria do EmpresaModel)
        $empresa = [
            'razao_social' => 'EnviCorp Soluções Ambientais LTDA',
            'cnpj' => '12.345.678/0001-99',
        ];

        $dataFim = date('Y-m-d', strtotime($dataInicio . " + " . ($diasFerias - 1) . " days"));
        $dataRetorno = date('Y-m-d', strtotime($dataFim . " + 1 day"));

        return [
            'empresa' => $empresa,
            'funcionario' => [
                'nome' => $funcionario['nome'],
                'cargo' => $funcionario['cargo'],
                'setor' => $funcionario['setor'],
                'data_admissao' => $funcionario['data_admissao'],
            ],
            'periodo' => [
                'data_inicio' => $dataInicio,
                'data_fim' => $dataFim,
                'data_retorno' => $dataRetorno,
                'dias' => $diasFerias,
            ],
            'data_comunicacao' => date('Y-m-d'),
        ];
    }

    /**
     * Registra um cálculo de férias bem-sucedido no histórico.
     * @param array $calculo O array completo retornado por calcularValoresFerias.
     * @return bool
     */
    public function registrarCalculoFerias(array $calculo): bool
    {
        $sql = "INSERT INTO ferias_historico 
                    (funcionario_id, funcionario_nome, data_inicio_ferias, dias_ferias, valor_bruto, valor_liquido, json_completo) 
                VALUES 
                    (:funcionario_id, :funcionario_nome, :data_inicio_ferias, :dias_ferias, :valor_bruto, :valor_liquido, :json_completo)";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':funcionario_id', $calculo['funcionario']['id'], PDO::PARAM_INT);
            $stmt->bindValue(':funcionario_nome', $calculo['funcionario']['nome']);
            $stmt->bindValue(':data_inicio_ferias', $calculo['periodo']['data_inicio']);
            $stmt->bindValue(':dias_ferias', $calculo['periodo']['dias'], PDO::PARAM_INT);
            $stmt->bindValue(':valor_bruto', $calculo['valores']['total_bruto']);
            $stmt->bindValue(':valor_liquido', $calculo['valores']['valor_liquido']);
            $stmt->bindValue(':json_completo', json_encode($calculo));

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erro ao registrar histórico de férias: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Busca o histórico de cálculos de férias com paginação.
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getHistoricoFerias(int $limit, int $offset): array
    {
        $sql = "SELECT * FROM ferias_historico ORDER BY data_calculo DESC LIMIT :limit OFFSET :offset";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar histórico de férias: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Conta o total de registros no histórico de férias.
     * @return int
     */
    public function getHistoricoFeriasCount(): int
    {
        try {
            return (int) $this->db->query("SELECT COUNT(*) FROM ferias_historico")->fetchColumn();
        } catch (PDOException $e) {
            error_log("Erro ao contar histórico de férias: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Busca um registro específico do histórico de férias pelo ID.
     * @param int $id
     * @return array|null
     */
    public function getHistoricoFeriasById(int $id): ?array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM ferias_historico WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            error_log("Erro ao buscar registro de histórico de férias por ID: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Atualiza um registro no histórico de férias.
     * @param int $id O ID do registro a ser atualizado.
     * @param array $novoCalculo O array com os novos dados calculados.
     * @return bool
     */
    public function atualizarRegistroHistorico(int $id, array $novoCalculo): bool
    {
        $sql = "UPDATE ferias_historico SET
                    funcionario_id = :funcionario_id,
                    funcionario_nome = :funcionario_nome,
                    data_inicio_ferias = :data_inicio_ferias,
                    dias_ferias = :dias_ferias,
                    valor_bruto = :valor_bruto,
                    valor_liquido = :valor_liquido,
                    json_completo = :json_completo
                WHERE id = :id";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->bindValue(':funcionario_id', $novoCalculo['funcionario']['id'], PDO::PARAM_INT);
            $stmt->bindValue(':funcionario_nome', $novoCalculo['funcionario']['nome']);
            $stmt->bindValue(':data_inicio_ferias', $novoCalculo['periodo']['data_inicio']);
            $stmt->bindValue(':dias_ferias', $novoCalculo['periodo']['dias'], PDO::PARAM_INT);
            $stmt->bindValue(':valor_bruto', $novoCalculo['valores']['total_bruto']);
            $stmt->bindValue(':valor_liquido', $novoCalculo['valores']['valor_liquido']);
            $stmt->bindValue(':json_completo', json_encode($novoCalculo));

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erro ao atualizar registro de histórico de férias: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Exclui um registro do histórico de férias.
     * @param int $id O ID do registro a ser excluído.
     * @return bool
     */
    public function excluirRegistroHistorico(int $id): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM ferias_historico WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Erro ao excluir registro de histórico de férias: " . $e->getMessage());
            return false;
        }
    }
}
