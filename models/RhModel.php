<?php

namespace App\Models;

use App\Core\Model;
use PDO;
use PDOException;
use App\Models\EmpresaModel;

class RhModel extends Model
{
    /**
     * Armazena a última mensagem de erro do model para diagnóstico.
     * @var string|null
     */
    private $lastError = null;

    /**
     * Valor da dedução por dependente para cálculo do IRRF.
     * Este valor é definido pela Receita Federal e deve ser atualizado anualmente. (Tabela de 2024)
     */
    private const DEDUCAO_DEPENDENTE_IRRF = 189.59;

    /**
     * Valor da cota do Salário Família por dependente. (Tabela de 2024)
     */
    private const COTA_SALARIO_FAMILIA = 62.04;

    /**
     * Limite de remuneração para ter direito ao Salário Família. (Tabela de 2024)
     */
    private const LIMITE_RENDA_SALARIO_FAMILIA = 1819.26;

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
            // CORREÇÃO: As queries agora usam a tabela 'usuarios' como fonte da verdade para o status 'Ativo',
            // garantindo consistência com o resto do sistema.
            $totalFuncionarios = $this->db->query("
                SELECT COUNT(c.colaborador_id) 
                FROM colaboradores c
                JOIN usuarios u ON c.colaborador_id = u.id -- INNER JOIN é intencional
                WHERE LOWER(u.status) = 'ativo' -- Comparação case-insensitive para robustez
            ")->fetchColumn();
            $novasContratacoesMes = $this->db->query("
                SELECT COUNT(c.colaborador_id) 
                FROM colaboradores c
                JOIN usuarios u ON c.colaborador_id = u.id
                WHERE LOWER(u.status) = 'ativo' AND c.data_admissao >= DATE_FORMAT(NOW(), '%Y-%m-01')
            ")->fetchColumn();

            // A lógica do próximo treinamento foi movida para o RhController,
            // que agora chama getProximoTreinamento() diretamente.

            return [
                'totalFuncionarios' => (int) $totalFuncionarios,
                'novasContratacoesMes' => (int) $novasContratacoesMes,
            ];
        } catch (PDOException $e) {
            error_log("Erro ao buscar resumo de RH: " . $e->getMessage());
            return ['totalFuncionarios' => 0, 'novasContratacoesMes' => 0];
        }
    }

    /**
     * Conta o número de funcionários que estão atualmente em período de férias.
     * Utiliza a tabela de histórico de férias para o cálculo.
     * @return int
     */
    public function getFuncionariosEmFeriasCount(): int
    {
        $hoje = date('Y-m-d');
        // A query verifica se a data de hoje está entre o início das férias e a data final,
        // que é calculada somando os dias de férias à data de início.
        // Usamos DISTINCT para não contar o mesmo funcionário duas vezes se ele tiver múltiplos registros de férias sobrepostos (improvável, mas seguro).
        $sql = "SELECT COUNT(DISTINCT funcionario_id) 
                FROM ferias_historico 
                WHERE :hoje BETWEEN data_inicio_ferias AND DATE_ADD(data_inicio_ferias, INTERVAL (dias_ferias - 1) DAY)";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':hoje', $hoje);
            $stmt->execute();
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Erro ao contar funcionários em férias: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Conta quantos funcionários retornam de férias nos próximos X dias.
     * @param int $dias
     * @return int
     */
    public function getRetornosFeriasBreve(int $dias = 7): int
    {
        $sql = "SELECT COUNT(*) 
                FROM ferias_historico 
                WHERE DATE_ADD(data_inicio_ferias, INTERVAL (dias_ferias) DAY) 
                BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL :dias DAY)";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':dias', $dias, PDO::PARAM_INT);
            $stmt->execute();
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            return 0;
        }
    }

    /**
     * Busca a lista de funcionários que estão atualmente em período de férias.
     * @return array
     */
    public function getFuncionariosEmFeriasList(): array
    {
        $hoje = date('Y-m-d');
        // A query busca o nome, data de início e dias de férias para calcular o retorno.
        $sql = "SELECT 
                    funcionario_nome, 
                    data_inicio_ferias,
                    dias_ferias
                FROM ferias_historico 
                WHERE :hoje BETWEEN data_inicio_ferias AND DATE_ADD(data_inicio_ferias, INTERVAL (dias_ferias - 1) DAY)
                ORDER BY funcionario_nome ASC";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':hoje', $hoje);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar lista de funcionários em férias: " . $e->getMessage());
            return [];
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
        // CORREÇÃO: A query agora busca o status da tabela 'usuarios' para garantir consistência.
        $sql = "SELECT 
                    c.colaborador_id as id, 
                    c.nome, 
                    c.data_admissao,
                    JSON_UNQUOTE(JSON_EXTRACT(c.beneficios_json, '$.cargo')) as cargo, 
                    JSON_UNQUOTE(JSON_EXTRACT(c.beneficios_json, '$.setor')) as setor, 
                    u.status as status 
                FROM colaboradores c
                JOIN usuarios u ON c.colaborador_id = u.id
                WHERE 1=1";
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
            $sql .= " AND LOWER(u.status) = LOWER(:status)";
            $params[':status'] = $filtros['status']; // O valor do filtro também será comparado em minúsculas
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
        // CORREÇÃO: A contagem agora usa a tabela 'usuarios' para o status, mantendo consistência.
        $sql = "SELECT COUNT(c.colaborador_id) 
                FROM colaboradores c
                JOIN usuarios u ON c.colaborador_id = u.id
                WHERE 1=1";
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
            $sql .= " AND LOWER(u.status) = LOWER(:status)";
            $params[':status'] = $filtros['status']; // O valor do filtro também será comparado em minúsculas
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
            $sql = "SELECT c.*, u.email, u.status, c.colaborador_id as id 
                    FROM colaboradores c
                    LEFT JOIN usuarios u ON c.colaborador_id = u.id
                    WHERE c.colaborador_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            $funcionario = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($funcionario) { // Garante que o funcionário foi encontrado
                // Expande o JSON com dados extras
                if (!empty($funcionario['beneficios_json'])) {
                    $dadosExtras = json_decode($funcionario['beneficios_json'], true);
                    if (is_array($dadosExtras)) {
                        $funcionario = array_merge($funcionario, $dadosExtras);
                    }
                }
                // Mapeia o campo 'telefone' do banco para 'celular' para o formulário
                if (isset($funcionario['telefone'])) {
                    $funcionario['celular'] = $funcionario['telefone'];
                }

                // Adiciona a URL completa da foto, se existir
                if (!empty($funcionario['foto_path'])) {
                    $funcionario['foto_url'] = BASE_URL . '/storage/fotos_funcionarios/' . $funcionario['foto_path'];
                } else {
                    $funcionario['foto_url'] = null; // Garante que a chave exista
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
     * Exclui um funcionário e seu usuário correspondente de forma transacional.
     * @param int $id
     * @return bool
     */
    public function excluirFuncionario(int $id): bool
    {
        // Inicia uma transação para garantir que ambas as exclusões ocorram ou nenhuma ocorra.
        $this->db->beginTransaction();

        try {
            // 1. Exclui da tabela 'colaboradores'.
            // A ordem é importante se não houver 'ON DELETE CASCADE' na FK.
            // Excluir da tabela filha primeiro.
            $stmtColaborador = $this->db->prepare("DELETE FROM colaboradores WHERE colaborador_id = ?");
            $stmtColaborador->execute([$id]);

            // 2. Exclui da tabela 'usuarios'.
            $stmtUsuario = $this->db->prepare("DELETE FROM usuarios WHERE id = ?");
            $stmtUsuario->execute([$id]);

            // Se ambas as exclusões foram bem-sucedidas, confirma a transação.
            return $this->db->commit();
        } catch (PDOException $e) {
            // Se ocorrer qualquer erro, desfaz a transação.
            $this->db->rollBack();
            error_log("Erro ao excluir funcionário: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Calcula a folha de pagamento e persiste os resultados.
     *
     * @param int $mes
     * @param int $ano
     * @return bool
     */
    public function calcularFolhaDePagamento(int $mes, int $ano): bool
    {
        try {
            $this->ensureFolhaResultadosTable();
            $lancamentos = $this->getLancamentos($mes, $ano);
            $resultados = $this->getFolhaCalculada($mes, $ano, $lancamentos);

            if (empty($resultados)) {
                error_log("Nenhum resultado de folha para $mes/$ano.");
                return false;
            }

            $this->db->beginTransaction();

            $sql = "INSERT INTO folha_pagamento_resultados
                    (colaborador_id, mes, ano, salario_bruto, inss, irrf, salario_familia,
                     outros_descontos, valor_liquido, fgts, base_calculo_inss, base_calculo_irrf, status)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'calculado')
                    ON DUPLICATE KEY UPDATE
                        salario_bruto = VALUES(salario_bruto),
                        inss = VALUES(inss),
                        irrf = VALUES(irrf),
                        salario_familia = VALUES(salario_familia),
                        outros_descontos = VALUES(outros_descontos),
                        valor_liquido = VALUES(valor_liquido),
                        fgts = VALUES(fgts),
                        base_calculo_inss = VALUES(base_calculo_inss),
                        base_calculo_irrf = VALUES(base_calculo_irrf),
                        status = 'calculado'";

            $stmt = $this->db->prepare($sql);

            foreach ($resultados as $r) {
                $fgts = $r['salario_bruto'] * 0.08;
                $baseIrrf = ($r['componentes']['base_inss'] ?? $r['salario_bruto']) - $r['inss'];
                $stmt->execute([
                    $r['id'], $mes, $ano,
                    $r['salario_bruto'], $r['inss'], $r['irrf'], $r['salario_familia'],
                    $r['outros_descontos'], $r['salario_liquido'],
                    $fgts,
                    $r['componentes']['base_inss'] ?? $r['salario_bruto'],
                    $baseIrrf > 0 ? $baseIrrf : 0
                ]);
            }

            // Gera as provisões contábeis
            $this->gerarProvisoesContabeis($mes, $ano, $resultados);

            $this->db->commit();
            error_log("Folha de pagamento $mes/$ano calculada e persistida com sucesso.");
            return true;
        } catch (PDOException $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            error_log("Erro ao calcular folha: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Garante que a tabela folha_pagamento_resultados existe.
     */
    private function ensureFolhaResultadosTable(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS folha_pagamento_resultados (
            id INT AUTO_INCREMENT PRIMARY KEY,
            colaborador_id INT,
            mes INT NOT NULL,
            ano INT NOT NULL,
            salario_bruto DECIMAL(15,2) DEFAULT 0,
            inss DECIMAL(15,2) DEFAULT 0,
            irrf DECIMAL(15,2) DEFAULT 0,
            salario_familia DECIMAL(15,2) DEFAULT 0,
            outros_descontos DECIMAL(15,2) DEFAULT 0,
            valor_liquido DECIMAL(15,2) DEFAULT 0,
            fgts DECIMAL(15,2) DEFAULT 0,
            base_calculo_inss DECIMAL(15,2) DEFAULT 0,
            base_calculo_irrf DECIMAL(15,2) DEFAULT 0,
            data_pagamento DATE,
            status VARCHAR(20) DEFAULT 'calculado',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (colaborador_id) REFERENCES colaboradores(colaborador_id) ON DELETE SET NULL,
            UNIQUE KEY uk_folha_mes_ano (colaborador_id, mes, ano)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        try {
            $this->db->exec($sql);
        } catch (PDOException $e) {
            error_log("Erro ao criar folha_pagamento_resultados: " . $e->getMessage());
        }
    }

    /**
     * Gera provisões contábeis (13º, férias, FGTS, INSS, IRRF) baseadas na folha calculada.
     */
    public function gerarProvisoesContabeis(int $mes, int $ano, array $resultados = []): bool
    {
        try {
            if (empty($resultados)) {
                $resultados = $this->getFolhaCalculada($mes, $ano, $this->getLancamentos($mes, $ano));
            }

            $this->ensureProvisoesTable();

            $totalBruto = array_sum(array_column($resultados, 'salario_bruto'));
            $totalINSS = array_sum(array_column($resultados, 'inss'));
            $totalIRRF = array_sum(array_column($resultados, 'irrf'));
            $totalFGTS = $totalBruto * 0.08;
            $totalLiquido = array_sum(array_column($resultados, 'salario_liquido'));

            $this->db->beginTransaction();

            $sql = "INSERT INTO provisoes_contabeis
                    (tipo_provisao, mes_competencia, ano_competencia, valor_provisionado, status, data_contabilizacao)
                    VALUES (?, ?, ?, ?, 'provisionado', ?)
                    ON DUPLICATE KEY UPDATE
                        valor_provisionado = VALUES(valor_provisionado),
                        status = 'provisionado'";

            $stmt = $this->db->prepare($sql);
            $dataContabil = sprintf('%04d-%02d-01', $ano, $mes);

            $provisoes = [
                ['fgts', $totalFGTS],
                ['inss', $totalINSS],
                ['irrf', $totalIRRF],
                ['13_salario', $totalBruto / 12],
                ['ferias', ($totalBruto + $totalBruto / 3) / 12],
            ];

            foreach ($provisoes as [$tipo, $valor]) {
                if ($valor > 0) {
                    $stmt->execute([$tipo, $mes, $ano, round($valor, 2), $dataContabil]);
                }
            }

            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            error_log("Erro ao gerar provisões: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Garante que a tabela provisoes_contabeis existe.
     */
    private function ensureProvisoesTable(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS provisoes_contabeis (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tipo_provisao ENUM('13_salario','ferias','fgts','inss','irrf','rescisao') NOT NULL,
            colaborador_id INT,
            mes_competencia INT NOT NULL,
            ano_competencia INT NOT NULL,
            valor_provisionado DECIMAL(15,2) DEFAULT 0,
            valor_pago DECIMAL(15,2) DEFAULT 0,
            data_contabilizacao DATE,
            status VARCHAR(20) DEFAULT 'provisionado',
            observacoes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (colaborador_id) REFERENCES colaboradores(colaborador_id) ON DELETE SET NULL,
            UNIQUE KEY uk_provisao (tipo_provisao, mes_competencia, ano_competencia, colaborador_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        try {
            $this->db->exec($sql);
        } catch (PDOException $e) {
            error_log("Erro ao criar provisoes_contabeis: " . $e->getMessage());
        }
    }

    /**
     * Retorna as provisões contábeis para integração.
     */
    public function getProvisoesContabeis(int $mes, int $ano): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM provisoes_contabeis
                WHERE mes_competencia = ? AND ano_competencia = ?
                ORDER BY tipo_provisao ASC
            ");
            $stmt->execute([$mes, $ano]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException) {
            return [];
        }
    }

    /**
     * Retorna os resultados persistidos da folha de pagamento.
     */
    public function getResultadosFolha(int $mes, int $ano): array
    {
        try {
            $this->ensureFolhaResultadosTable();
            $stmt = $this->db->prepare("
                SELECT f.*, c.nome as colaborador_nome
                FROM folha_pagamento_resultados f
                LEFT JOIN colaboradores c ON f.colaborador_id = c.colaborador_id
                WHERE f.mes = ? AND f.ano = ?
                ORDER BY c.nome ASC
            ");
            $stmt->execute([$mes, $ano]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException) {
            return [];
        }
    }

    /**
     * Atualiza o status de pagamento da folha.
     */
    public function confirmarPagamentoFolha(int $mes, int $ano, string $dataPagamento): bool
    {
        try {
            $this->ensureFolhaResultadosTable();
            $stmt = $this->db->prepare("
                UPDATE folha_pagamento_resultados
                SET status = 'pago', data_pagamento = ?
                WHERE mes = ? AND ano = ?
            ");
            return $stmt->execute([$dataPagamento, $mes, $ano]);
        } catch (PDOException) {
            return false;
        }
    }

    /**
     * Simula a busca dos resultados de uma folha de pagamento já calculada.
     *
     * @param int $mes
     * @param int $ano
     * @return array
     */
    public function getFolhaCalculada(int $mes, int $ano, array $lancamentos = []): array
    {
        // 1. Buscar todos os funcionários ativos do banco de dados.
        $sql = "SELECT 
                    c.colaborador_id as id, 
                    c.nome, 
                    c.salario,
                    c.beneficios_json,
                    JSON_UNQUOTE(JSON_EXTRACT(c.beneficios_json, '$.carga_horaria')) as carga_horaria
                FROM colaboradores c
                JOIN usuarios u ON c.colaborador_id = u.id
                WHERE u.status = 'Ativo'";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $funcionarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar funcionários para cálculo da folha: " . $e->getMessage());
            return [];
        }

        $resultados = [];

        // 2. Iterar sobre cada funcionário e calcular os valores da folha.
        foreach ($funcionarios as $funcionario) {
            if (empty($funcionario['salario']) || $funcionario['salario'] <= 0) {
                continue; // Pula funcionários sem salário definido para evitar erros.
            }

            $salarioBase = (float) $funcionario['salario'];
            $lancamentoFuncionario = $lancamentos[$funcionario['id']] ?? null;

            // Calcula valores dos eventos variáveis (lançamentos)
            $valorHorasExtras50 = 0;
            $valorHorasExtras100 = 0;
            $valorFaltas = 0;
            $outrosDescontosForm = 0.00;

            if ($lancamentoFuncionario) {
                $cargaHorariaSemanal = (int)($funcionario['carga_horaria'] ?? 44);
                $divisorMensal = ($cargaHorariaSemanal > 0) ? (($cargaHorariaSemanal / 6) * 30) : 220;
                $valorHora = $salarioBase / $divisorMensal;

                if (!empty($lancamentoFuncionario['horas_extras_50'])) {
                    $qtd = (float)$lancamentoFuncionario['horas_extras_50'];
                    $valorHorasExtras50 = round($valorHora * 1.5 * $qtd, 2);
                }
                if (!empty($lancamentoFuncionario['horas_extras_100'])) {
                    $qtd = (float)$lancamentoFuncionario['horas_extras_100'];
                    $valorHorasExtras100 = round($valorHora * 2.0 * $qtd, 2);
                }
                if (!empty($lancamentoFuncionario['faltas'])) {
                    $dias = (int)$lancamentoFuncionario['faltas'];
                    $valorFaltas = round(($salarioBase / 30) * $dias, 2);
                }
                if (!empty($lancamentoFuncionario['outros_descontos'])) {
                    $outrosDescontosForm = (float)$lancamentoFuncionario['outros_descontos'];
                }
            }

            // Remuneração Bruta = Salário Base + Horas Extras
            $remuneracaoBruta = $salarioBase + $valorHorasExtras50 + $valorHorasExtras100;
            // Base de cálculo para o INSS = Salário Base - Faltas + Horas Extras
            $baseCalculoInss = $salarioBase - $valorFaltas + $valorHorasExtras50 + $valorHorasExtras100;

            // 3. Calcular INSS
            $inss = $this->calcularINSS($baseCalculoInss);

            // 4. Calcular Salário Família
            $dadosExtras = json_decode($funcionario['beneficios_json'] ?? '[]', true);
            $dependentes = $dadosExtras['dependentes'] ?? [];
            $numDependentesSalarioFamilia = 0;
            if (is_array($dependentes)) {
                foreach ($dependentes as $dep) {
                    if (!empty($dep['nome']) && !empty($dep['nascimento'])) {
                        try {
                            $nascimento = new \DateTime($dep['nascimento']);
                            $hoje = new \DateTime();
                            $idade = $hoje->diff($nascimento)->y;
                            if ($idade < 14) { // Regra: filhos até 14 anos
                                $numDependentesSalarioFamilia++;
                            }
                        } catch (\Exception $e) {
                            // Ignora dependente com data de nascimento inválida.
                        }
                    }
                }
            }

            $salarioFamilia = 0;
            if ($baseCalculoInss <= self::LIMITE_RENDA_SALARIO_FAMILIA && $numDependentesSalarioFamilia > 0) { // Usa a base do INSS para a regra
                $salarioFamilia = $numDependentesSalarioFamilia * self::COTA_SALARIO_FAMILIA;
            }

            // 5. Calcular IRRF
            $numDependentesIRRF = is_array($dependentes) ? count(array_filter($dependentes, fn($d) => !empty($d['nome']))) : 0;
            $baseCalculoIrrf = $baseCalculoInss - $inss; // Base do IRRF é o bruto menos o INSS.
            $irrf = $this->calcularIRRF($baseCalculoIrrf, $numDependentesIRRF);

            // 6. Outros Descontos (mockado por enquanto, viria de lançamentos)
            $totalOutrosDescontos = $valorFaltas + $outrosDescontosForm;

            // 7. Calcular Salário Líquido
            $totalProventos = $remuneracaoBruta + $salarioFamilia;
            $totalDescontos = $inss + $irrf + $totalOutrosDescontos;
            $salarioLiquido = $totalProventos - $totalDescontos;

            // 8. Montar o array de resultado para este funcionário
            $resultados[] = [
                'id' => $funcionario['id'],
                'nome' => $funcionario['nome'],
                'salario_bruto' => $remuneracaoBruta,
                'inss' => $inss,
                'irrf' => $irrf,
                'outros_descontos' => $totalOutrosDescontos,
                'salario_familia' => $salarioFamilia,
                'salario_liquido' => $salarioLiquido,
                'numero_dependentes' => $numDependentesIRRF, // Adiciona para uso no holerite
                // Componentes individuais para uso no holerite
                'componentes' => [
                    'salario_base' => $salarioBase,
                    'he_50' => $valorHorasExtras50,
                    'he_100' => $valorHorasExtras100,
                    'faltas' => $valorFaltas,
                    'outros_descontos_form' => $outrosDescontosForm,
                    'base_inss' => $baseCalculoInss,
                ]
            ];
        }

        return $resultados;
    }

    /**
     * Simula a busca dos dados para a geração de um ou mais holerites.
     *
     * @param int $mes
     * @param int $ano
     * @param int|null $funcionario_id
     * @return array
     */
    public function getDadosHolerite(int $mes, int $ano, int $funcionario_id = null, array $lancamentos = []): array
    {
        // 1. Pega os dados já calculados da folha.
        $folhaCalculada = $this->getFolhaCalculada($mes, $ano, $lancamentos);

        $holerites = [];
        $competencia = str_pad($mes, 2, '0', STR_PAD_LEFT) . '/' . $ano;

        // Busca dados da empresa
        $empresaModel = new EmpresaModel();
        $empresaData = $empresaModel->getDadosEmpresa();
        $infoEmpresa = [
            'nome' => $empresaData['razao_social'] ?? '',
            'cnpj' => $empresaData['cnpj'] ?? ''
        ];

        // 2. Itera sobre os resultados da folha para montar a estrutura do holerite.
        foreach ($folhaCalculada as $funcionario) {
            if ($funcionario_id !== null && $funcionario['id'] != $funcionario_id) {
                continue;
            }

            $dadosCadastrais = $this->getFuncionarioById($funcionario['id']);
            $componentes = $funcionario['componentes'];

            // Monta os proventos.
            $proventos = [];
            $proventos[] = ['codigo' => '101', 'descricao' => 'Salário Base', 'valor' => $componentes['salario_base']];
            if ($componentes['he_50'] > 0) $proventos[] = ['codigo' => '105', 'descricao' => 'Horas Extras 50%', 'valor' => $componentes['he_50']];
            if ($componentes['he_100'] > 0) $proventos[] = ['codigo' => '106', 'descricao' => 'Horas Extras 100%', 'valor' => $componentes['he_100']];
            if ($funcionario['salario_familia'] > 0) {
                $cotas = $funcionario['salario_familia'] / self::COTA_SALARIO_FAMILIA;
                $proventos[] = ['codigo' => '110', 'descricao' => "Salário Família ({$cotas} cota(s))", 'valor' => $funcionario['salario_familia']];
            }

            // Monta os descontos.
            $descontos = [];
            if ($funcionario['inss'] > 0) $descontos[] = ['codigo' => '201', 'descricao' => 'INSS sobre Salário', 'valor' => $funcionario['inss']];
            if ($funcionario['irrf'] > 0) $descontos[] = ['codigo' => '202', 'descricao' => 'IRRF sobre Salário', 'valor' => $funcionario['irrf']];
            if ($componentes['faltas'] > 0) $descontos[] = ['codigo' => '210', 'descricao' => 'Faltas', 'valor' => $componentes['faltas']];
            if ($componentes['outros_descontos_form'] > 0) $descontos[] = ['codigo' => '205', 'descricao' => 'Outros Descontos', 'valor' => $componentes['outros_descontos_form']];

            // Monta as bases de cálculo.
            $baseIrrf = $componentes['base_inss'] - $funcionario['inss'] - ($funcionario['numero_dependentes'] * self::DEDUCAO_DEPENDENTE_IRRF);
            $bases = [
                'base_inss' => $componentes['base_inss'],
                'base_fgts' => $funcionario['salario_bruto'],
                'fgts_mes' => $funcionario['salario_bruto'] * 0.08,
                'base_irrf' => $baseIrrf > 0 ? $baseIrrf : 0,
            ];

            // Monta a estrutura final do holerite.
            $holerites[] = [
                'info_empresa' => $infoEmpresa,
                'competencia' => $competencia,
                'info_funcionario' => [
                    'nome' => $funcionario['nome'],
                    'cargo' => $dadosCadastrais['cargo'] ?? 'N/A',
                    'setor' => $dadosCadastrais['setor'] ?? 'N/A',
                ],
                'proventos' => $proventos,
                'descontos' => $descontos,
                'totais' => [
                    'bruto' => array_sum(array_column($proventos, 'valor')),
                    'descontos' => array_sum(array_column($descontos, 'valor')),
                    'liquido' => $funcionario['salario_liquido'],
                ],
                'bases' => $bases,
            ];
        }

        return $holerites;
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
    public function getEncargosCalculados(int $mes, int $ano, array $lancamentos = []): array
    {
        // Correção: Utiliza a folha já calculada para obter os salários brutos corretos.
        $folhaCalculada = $this->getFolhaCalculada($mes, $ano, $lancamentos);

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
    public function getDadosExportacaoContabil(int $mes, int $ano, array $lancamentos = []): array
    {
        // Reutiliza a lógica de getFolhaCalculada para obter os totais por funcionário
        $folhaCalculada = $this->getFolhaCalculada($mes, $ano, $lancamentos);

        $totalBruto = array_sum(array_column($folhaCalculada, 'salario_bruto'));
        $totalSalarioFamilia = array_sum(array_column($folhaCalculada, 'salario_familia'));
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
            ['competencia' => $competencia, 'tipo' => 'Provisão', 'descricao' => 'Salários a Pagar', 'valor' => $totalLiquido, 'conta_debito' => '2.1.1.01.001', 'conta_credito' => '1.1.1.01.001'], // O líquido já inclui o Sal. Família

            // Lançamento da provisão dos encargos
            ['competencia' => $competencia, 'tipo' => 'Provisão', 'descricao' => 'INSS a Recolher', 'valor' => $totalINSS - $totalSalarioFamilia, 'conta_debito' => '2.1.1.01.001', 'conta_credito' => '2.1.2.01.002'], // O valor do Sal. Família é abatido do INSS a pagar
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
            // CORREÇÃO: Usar 'Colaborador' para manter consistência com o resto do sistema
            $perfilNome = 'Colaborador';
            $permissoesBasicas = json_encode(['dashboard_view', 'clientes_view', 'projetos_view', 'financeiro_dashboard_view', 'licencas_operacao_view']);

            $stmtPerfil = $this->db->prepare("SELECT perfil_id, permissoes FROM perfis_acesso WHERE nome_perfil = :nome_perfil");
            $stmtPerfil->execute([':nome_perfil' => $perfilNome]);
            $perfil = $stmtPerfil->fetch(PDO::FETCH_ASSOC);

            if ($perfil) {
                $perfil_id = $perfil['perfil_id'];
                if (empty($perfil['permissoes']) || $perfil['permissoes'] === 'null') {
                    $this->db->prepare("UPDATE perfis_acesso SET permissoes = ? WHERE perfil_id = ?")->execute([$permissoesBasicas, $perfil_id]);
                }
            } else {
                $stmtInsPerfil = $this->db->prepare("INSERT INTO perfis_acesso (nome_perfil, descricao, permissoes) VALUES (:nome_perfil, 'Perfil padrão para funcionários', :permissoes)");
                $stmtInsPerfil->execute([':nome_perfil' => $perfilNome, ':permissoes' => $permissoesBasicas]);
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
     * Salva os lançamentos da folha (horas extras, faltas, etc.) no banco de dados.
     * Utiliza INSERT ... ON DUPLICATE KEY UPDATE para eficiência.
     *
     * @param array $lancamentos Dados dos lançamentos, vindo do formulário.
     * @param int $mes Mês da competência.
     * @param int $ano Ano da competência.
     * @return bool Retorna true em sucesso, false em falha.
     */
    public function salvarLancamentosBD(array $lancamentos, int $mes, int $ano): bool
    {
        $sql = "INSERT INTO folha_lancamentos (colaborador_id, mes, ano, horas_extras_50, horas_extras_100, faltas, outros_descontos) 
                VALUES (:colaborador_id, :mes, :ano, :he50, :he100, :faltas, :outros_desc)
                ON DUPLICATE KEY UPDATE 
                    horas_extras_50 = VALUES(horas_extras_50),
                    horas_extras_100 = VALUES(horas_extras_100),
                    faltas = VALUES(faltas),
                    outros_descontos = VALUES(outros_descontos)";

        try {
            $this->db->beginTransaction();
            $stmt = $this->db->prepare($sql);

            foreach ($lancamentos as $colaborador_id => $dados) {
                $stmt->execute([
                    ':colaborador_id' => $colaborador_id,
                    ':mes' => $mes,
                    ':ano' => $ano,
                    ':he50' => (float)($dados['horas_extras_50'] ?? 0),
                    ':he100' => (float)($dados['horas_extras_100'] ?? 0),
                    ':faltas' => (int)($dados['faltas'] ?? 0),
                    ':outros_desc' => (float)($dados['outros_descontos'] ?? 0),
                ]);
            }

            return $this->db->commit();
        } catch (PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log("Erro ao salvar lançamentos da folha: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Busca os lançamentos da folha de uma competência específica no banco de dados.
     *
     * @param int $mes Mês da competência.
     * @param int $ano Ano da competência.
     * @return array Retorna um array de lançamentos, indexado pelo ID do colaborador.
     */
    public function getLancamentos(int $mes, int $ano): array
    {
        $sql = "SELECT * FROM folha_lancamentos WHERE mes = :mes AND ano = :ano";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':mes' => $mes, ':ano' => $ano]);
        // O PDO::FETCH_KEY_PAIR não funciona aqui pois temos mais de 2 colunas.
        // Então, buscamos tudo e reindexamos com PHP.
        return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), null, 'colaborador_id');
    }

    /**
     * Busca e consolida os dados para o relatório "Espelho da Folha".
     *
     * @param int $mes
     * @param int $ano
     * @return array
     */
    public function getDadosEspelhoFolha(int $mes, int $ano, array $lancamentos = []): array
    {
        // Reutiliza a busca da folha calculada
        $funcionarios = $this->getFolhaCalculada($mes, $ano, $lancamentos);

        // Busca também os encargos calculados para adicionar ao relatório
        $encargos = $this->getEncargosCalculados($mes, $ano);

        // Calcula os totais
        $totais = [
            'salario_bruto' => array_sum(array_column($funcionarios, 'salario_bruto')),
            'salario_familia' => array_sum(array_column($funcionarios, 'salario_familia')),
            'inss' => array_sum(array_column($funcionarios, 'inss')),
            'irrf' => array_sum(array_column($funcionarios, 'irrf')),
            'outros_descontos' => array_sum(array_column($funcionarios, 'outros_descontos')),
            'salario_liquido' => array_sum(array_column($funcionarios, 'salario_liquido')),
        ];

        // Busca dados da empresa dinamicamente
        $empresaModel = new EmpresaModel();
        $empresaData = $empresaModel->getDadosEmpresa();

        $info_empresa = [
            'nome' => $empresaData['razao_social'] ?? '',
            'cnpj' => $empresaData['cnpj'] ?? '',
            'endereco' => $empresaData['endereco'] ?? ''
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
            // Adicionado alias 'id' para consistência com o frontend que espera 'id' nos selects
            $stmt = $this->db->query("SELECT cargo_id, cargo_id as id, nome_cargo FROM cargos ORDER BY nome_cargo ASC");
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
                descricao TEXT NULL,
                permissoes TEXT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

            $this->db->exec($sql);

            // Verifica se a coluna 'permissoes' existe e a adiciona se necessário (migração automática)
            $check = $this->db->query("SHOW COLUMNS FROM perfis_acesso LIKE 'permissoes'");
            if ($check && $check->rowCount() === 0) {
                $this->db->exec("ALTER TABLE perfis_acesso ADD COLUMN permissoes TEXT NULL AFTER descricao");
            }
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

        // Usa o método de cálculo de INSS correto
        $inss = $this->calcularINSS($totalBruto);

        // Conta os dependentes para o cálculo do IRRF
        $dependentes = $funcionario['dependentes'] ?? [];
        $numDependentes = is_array($dependentes) ? count(array_filter($dependentes, fn($d) => !empty($d['nome']))) : 0;

        // Usa o método de cálculo de IRRF correto
        $baseIrrf = $totalBruto - $inss;
        // A dedução por dependentes é aplicada dentro do método calcularIRRF.
        $irrf = $this->calcularIRRF($baseIrrf, $numDependentes);

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

        // Busca dados da empresa dinamicamente
        $empresaModel = new EmpresaModel();
        $empresaData = $empresaModel->getDadosEmpresa();

        $empresa = [
            'razao_social' => $empresaData['razao_social'] ?? '',
            'cnpj' => $empresaData['cnpj'] ?? '',
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

    /**
     * Salva um novo treinamento na tabela específica do RH.
     * @param array $dados
     * @return bool
     */
    public function salvarTreinamento(array $dados): bool
    {
        // Usamos a tabela 'rh_treinamentos' para esta funcionalidade simplificada do RH.
        if (!empty($dados['id'])) {
            $sql = "UPDATE rh_treinamentos SET nome = :nome, data = :data, participantes = :participantes, instrutor = :instrutor WHERE id = :id";
        } else {
            $sql = "INSERT INTO rh_treinamentos (nome, data, participantes, instrutor) VALUES (:nome, :data, :participantes, :instrutor)";
        }

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':nome', $dados['nome']);
            $stmt->bindValue(':data', $dados['data']);
            $stmt->bindValue(':participantes', $dados['participantes'] ?? '');
            $stmt->bindValue(':instrutor', $dados['instrutor']);
            if (!empty($dados['id'])) {
                $stmt->bindValue(':id', $dados['id'], PDO::PARAM_INT);
            }
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erro ao salvar treinamento de RH: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Exclui um treinamento.
     * @param int $id
     * @return bool
     */
    public function excluirTreinamento(int $id): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM rh_treinamentos WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Erro ao excluir treinamento de RH: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Busca todos os treinamentos da tabela específica do RH.
     * @return array
     */
    public function getAllTreinamentos(): array
    {
        $sql = "SELECT id, nome, data, participantes, instrutor FROM rh_treinamentos ORDER BY data ASC";
        try {
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar todos os treinamentos de RH: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Busca o próximo treinamento agendado na tabela específica do RH.
     * @return array|null
     */
    public function getProximoTreinamento(): ?array
    {
        // Busca o próximo treinamento com data futura ou de hoje
        $sql = "SELECT nome, participantes 
                FROM rh_treinamentos 
                WHERE data >= CURDATE()
                ORDER BY data ASC 
                LIMIT 1";
        try {
            $stmt = $this->db->query($sql);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null; // Retorna o resultado ou null se não encontrar
        } catch (PDOException $e) {
            error_log("Erro ao buscar próximo treinamento de RH: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Busca lista de funcionários para relatório com filtro de status.
     * @param string|null $status 'Ativo', 'Inativo' ou 'Todos'
     * @return array
     */
    public function getFuncionariosRelatorio(?string $status = 'Todos'): array
    {
        $sql = "SELECT 
                    c.colaborador_id as id, 
                    c.nome, 
                    JSON_UNQUOTE(JSON_EXTRACT(c.beneficios_json, '$.cpf')) as cpf,
                    c.data_admissao,
                    JSON_UNQUOTE(JSON_EXTRACT(c.beneficios_json, '$.cargo')) as cargo, 
                    JSON_UNQUOTE(JSON_EXTRACT(c.beneficios_json, '$.setor')) as setor, 
                    u.status as status,
                    u.email
                FROM colaboradores c
                JOIN usuarios u ON c.colaborador_id = u.id
                WHERE 1=1";

        $params = [];
        if ($status && $status !== 'Todos') {
            $sql .= " AND LOWER(u.status) = LOWER(:status)";
            $params[':status'] = $status;
        }

        $sql .= " ORDER BY c.nome ASC";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar funcionários para relatório: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Busca a distribuição de funcionários ativos por setor.
     * @return array
     */
    public function getFuncionariosPorSetor(): array
    {
        try {
            // Extrai o setor do JSON, trata valores vazios e conta os ativos
            $sql = "SELECT 
                        COALESCE(NULLIF(JSON_UNQUOTE(JSON_EXTRACT(c.beneficios_json, '$.setor')), ''), 'Não Definido') as nome, 
                        COUNT(c.colaborador_id) as qtd
                    FROM colaboradores c
                    JOIN usuarios u ON c.colaborador_id = u.id
                    WHERE LOWER(u.status) = 'ativo'
                    GROUP BY nome
                    ORDER BY qtd DESC";
            
            return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar distribuição por setor: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Calcula os valores devidos na rescisão de um contrato de trabalho.
     * @param array $dados Os dados do formulário de rescisão.
     * @return array|null Um array com os valores calculados ou null em caso de erro.
     */
    public function calcularValoresRescisao(array $dados): ?array // Refatorado para maior precisão
    {
        $funcionario = $this->getFuncionarioById($dados['funcionario_id']);
        if (!$funcionario || empty($funcionario['salario']) || empty($funcionario['data_admissao'])) {
            error_log("Erro ao calcular rescisão: Funcionário ID {$dados['funcionario_id']} não encontrado ou sem salário/data de admissão.");
            return null;
        }

        $salarioBase = (float) $funcionario['salario'];
        $dataAdmissao = new \DateTime($funcionario['data_admissao']);
        $dataDesligamento = new \DateTime($dados['data_desligamento']);
        $motivo = $dados['motivo_rescisao'];
        $dependentes = (int)($dados['dependentes'] ?? 0);

        $proventos = [];
        $descontos = [];
        $referencias = [
            'dias_saldo_salario' => 0,
            'avos_13_proporcional' => 0,
            'avos_ferias_proporcionais' => 0,
            'dias_aviso_previo' => 0,
        ];

        // --- 1. CÁLCULO DE DATAS E PERÍODOS ---

        // Calcula os dias de aviso prévio proporcional (Lei 12.506/2011)
        $anosServicoCompletos = $dataAdmissao->diff($dataDesligamento)->y;
        $diasAvisoProporcional = 3 * $anosServicoCompletos;
        $diasAvisoTotal = min(30 + $diasAvisoProporcional, 90);

        // Armazena o total de dias de aviso, independentemente do tipo (trabalhado ou indenizado)
        $referencias['dias_aviso_previo'] = $diasAvisoTotal;

        // A data projetada do fim do contrato considera a projeção do aviso prévio indenizado.
        $dataProjetadaFimContrato = clone $dataDesligamento;
        if ($dados['aviso_previo'] === 'indenizado' && $motivo === 'demissao_sem_justa_causa') {
            $dataProjetadaFimContrato->modify("+" . $diasAvisoTotal . " days");
        }

        // --- 2. CÁLCULO DAS VERBAS RESCISÓRIAS (PROVENTOS) ---

        // 1. Saldo de Salário
        $diasTrabalhadosMes = (int) $dataDesligamento->format('d');
        $saldoSalario = ($salarioBase / 30) * $diasTrabalhadosMes;
        $proventos[] = ['descricao' => 'Saldo de Salário (' . $diasTrabalhadosMes . ' dias)', 'valor' => $saldoSalario];
        $referencias['dias_saldo_salario'] = $diasTrabalhadosMes;

        // 2. Aviso Prévio Indenizado
        $valorAvisoPrevio = 0;
        if ($dados['aviso_previo'] === 'indenizado' && $motivo === 'demissao_sem_justa_causa') {
            $valorAvisoPrevio = ($salarioBase / 30) * $diasAvisoTotal;
            $proventos[] = ['descricao' => 'Aviso Prévio Indenizado (' . $diasAvisoTotal . ' dias)', 'valor' => $valorAvisoPrevio];
        }

        // 3. Férias Vencidas + 1/3
        $periodosFeriasVencidas = $this->calcularPeriodosFeriasVencidas($dataAdmissao, $dataDesligamento, $dados['funcionario_id']);
        $feriasVencidas = 0;
        $tercoFeriasVencidas = 0;
        if ($dados['ferias_vencidas'] || $periodosFeriasVencidas > 0) {
            $numPeriodosVencidos = max((int)$dados['ferias_vencidas'], $periodosFeriasVencidas);
            $feriasVencidas = $salarioBase * $numPeriodosVencidos;
            $tercoFeriasVencidas = $feriasVencidas / 3;
            $proventos[] = ['descricao' => 'Férias Vencidas (' . $numPeriodosVencidos . ' períodos)', 'valor' => $feriasVencidas];
            $proventos[] = ['descricao' => '1/3 sobre Férias Vencidas', 'valor' => $tercoFeriasVencidas];
        }

        // 4. Férias Proporcionais + 1/3
        $feriasProporcionais = 0;
        $tercoFeriasProporcionais = 0;
        if ($motivo !== 'demissao_com_justa_causa') {
            // Encontra a data do último aniversário de admissão antes do fim do contrato projetado
            $ultimoAniversario = clone $dataAdmissao;
            while ((clone $ultimoAniversario)->modify('+1 year') <= $dataProjetadaFimContrato) {
                $ultimoAniversario->modify('+1 year');
            }
            // Calcula os meses proporcionais a partir do último aniversário
            $intervaloProporcional = $ultimoAniversario->diff($dataProjetadaFimContrato);
            $mesesProporcionais = $intervaloProporcional->m;
            if ($intervaloProporcional->d >= 15) {
                $mesesProporcionais++;
            }
            // Se o intervalo de anos for maior que 0, significa que completou 12 meses no período
            if ($intervaloProporcional->y > 0) {
                $mesesProporcionais += 12 * $intervaloProporcional->y;
            }

            $feriasProporcionais = ($salarioBase / 12) * $mesesProporcionais;
            $tercoFeriasProporcionais = $feriasProporcionais / 3;
            $proventos[] = ['descricao' => 'Férias Proporcionais (' . $mesesProporcionais . '/12 avos)', 'valor' => $feriasProporcionais];
            $proventos[] = ['descricao' => '1/3 sobre Férias Proporcionais', 'valor' => $tercoFeriasProporcionais];
            $referencias['avos_ferias_proporcionais'] = $mesesProporcionais;
        }

        // 5. 13º Salário Proporcional
        $decimoTerceiro = 0;
        if ($motivo !== 'demissao_com_justa_causa') {
            $mesFinal = (int) $dataProjetadaFimContrato->format('m');
            $diaFinal = (int) $dataProjetadaFimContrato->format('d');
            $avos13 = ($diaFinal >= 15) ? $mesFinal : $mesFinal - 1;

            if ($dataAdmissao->format('Y') == $dataProjetadaFimContrato->format('Y')) {
                $avos13 = $mesFinal - (int)$dataAdmissao->format('m') + 1;
                if ((int)$dataAdmissao->format('d') > 15) $avos13--;
                if ($diaFinal < 15) $avos13--;
            }
            $avos13 = max(0, $avos13);

            if ($avos13 > 0) {
                $decimoTerceiro = ($salarioBase / 12) * $avos13;
                $proventos[] = ['descricao' => '13º Salário Proporcional (' . $avos13 . '/12 avos)', 'valor' => $decimoTerceiro];
                $referencias['avos_13_proporcional'] = $avos13;
            }
        }

        // 6. Outras Verbas Variáveis (do formulário)
        $valorTotalHorasExtras = 0.0;
        $cargaHorariaSemanal = (int)($funcionario['carga_horaria'] ?? 44);
        $divisorMensal = ($cargaHorariaSemanal > 0) ? (($cargaHorariaSemanal / 6) * 30) : 220;
        $valorHora = $salarioBase / $divisorMensal;

        if (!empty($dados['horas_extras_50_qtd']) && $dados['horas_extras_50_qtd'] > 0) {
            $qtd_he_50 = (float)$dados['horas_extras_50_qtd'];
            $valor_he_50 = $valorHora * 1.5 * $qtd_he_50;
            $proventos[] = ['descricao' => "Horas Extras 50% ({$qtd_he_50}h)", 'valor' => $valor_he_50];
            $valorTotalHorasExtras += $valor_he_50;
        }
        if (!empty($dados['horas_extras_100_qtd']) && $dados['horas_extras_100_qtd'] > 0) {
            $qtd_he_100 = (float)$dados['horas_extras_100_qtd'];
            $valor_he_100 = $valorHora * 2.0 * $qtd_he_100;
            $proventos[] = ['descricao' => "Horas Extras 100% ({$qtd_he_100}h)", 'valor' => $valor_he_100];
            $valorTotalHorasExtras += $valor_he_100;
        }

        $outrasVariaveis = [
            'comissoes' => 'Comissão',
            'gratificacoes' => 'Gratificação',
            'adicional_noturno' => 'Adicional Noturno',
            'adicional_periculosidade' => 'Adicional de Periculosidade',
            'dsr' => 'Descanso Semanal Remunerado (DSR)',
            'ajuste_saldo_devedor' => 'Ajuste do Saldo Devedor',
        ];

        $totalOutrasVerbasSalariais = 0;
        foreach ($outrasVariaveis as $key => $descricao) {
            if (!empty($dados[$key])) {
                $valor = (float)str_replace(['.', ','], ['', '.'], $dados[$key]);
                if ($valor > 0) {
                    $proventos[] = ['descricao' => $descricao, 'valor' => $valor];
                    $totalOutrasVerbasSalariais += $valor;
                }
            }
        }

        // --- 3. CÁLCULO DE IMPOSTOS E DESCONTOS ---

        // 3.1. INSS
        $baseCalculoInssSalario = $saldoSalario + $valorTotalHorasExtras + $totalOutrasVerbasSalariais;
        $inssSalario = $this->calcularINSS($baseCalculoInssSalario);
        if ($inssSalario > 0) $descontos[] = ['descricao' => 'INSS sobre Saldo de Salário', 'valor' => $inssSalario];

        $inss13 = $this->calcularINSS($decimoTerceiro);
        if ($inss13 > 0) $descontos[] = ['descricao' => 'INSS sobre 13º Salário', 'valor' => $inss13];

        // 3.2. IRRF
        // IRRF sobre 13º (tributação exclusiva)
        $baseIrrf13 = $decimoTerceiro - $inss13;
        $irrf13 = $this->calcularIRRF($baseIrrf13, $dependentes);
        if ($irrf13 > 0) $descontos[] = ['descricao' => 'IRRF sobre 13º Salário', 'valor' => $irrf13];

        // IRRF sobre demais verbas (salário + férias)
        $baseIrrfOutros = ($baseCalculoInssSalario - $inssSalario) + $feriasVencidas + $tercoFeriasVencidas + $feriasProporcionais + $tercoFeriasProporcionais;
        $irrfOutros = $this->calcularIRRF($baseIrrfOutros, $dependentes);
        if ($irrfOutros > 0) $descontos[] = ['descricao' => 'IRRF', 'valor' => $irrfOutros];

        // 3.3. Outros Descontos
        if ($dados['aviso_previo'] === 'nao_cumprido_empregado' && $motivo === 'pedido_demissao') {
            $descontos[] = ['descricao' => 'Desconto Aviso Prévio não cumprido (30 dias)', 'valor' => $salarioBase];
        }
        if (!empty($dados['adiantamento_salarial'])) {
            $valorAdiantamento = (float)str_replace(['.', ','], ['', '.'], $dados['adiantamento_salarial']);
            if ($valorAdiantamento > 0) {
                $descontos[] = ['descricao' => 'Adiantamento Salarial', 'valor' => $valorAdiantamento];
            }
        }
        if (!empty($dados['adiantamento_13'])) {
            $valorAdiantamento13 = (float)str_replace(['.', ','], ['', '.'], $dados['adiantamento_13']);
            if ($valorAdiantamento13 > 0) {
                $descontos[] = ['descricao' => 'Adiantamento de 13º Salário', 'valor' => $valorAdiantamento13];
            }
        }
        if (!empty($dados['outros_descontos'])) {
            $valorOutrosDescontos = (float)str_replace(['.', ','], ['', '.'], $dados['outros_descontos']);
            if ($valorOutrosDescontos > 0) {
                $descontos[] = ['descricao' => 'Outros Descontos', 'valor' => $valorOutrosDescontos];
            }
        }
        if (!empty($dados['pensao_alimenticia_valor'])) {
            $valorPensao = (float)str_replace(['.', ','], ['', '.'], $dados['pensao_alimenticia_valor']);
            if ($valorPensao > 0) {
                $descontos[] = ['descricao' => 'Pensão Alimentícia', 'valor' => $valorPensao];
            }
        }

        // --- 4. TOTALIZAÇÃO ---
        $totalProventos = array_sum(array_column($proventos, 'valor'));
        $totalDescontos = array_sum(array_column($descontos, 'valor'));

        // Multa FGTS (valor informativo, não entra no líquido a pagar)
        $multaFgts = 0;
        if ($motivo === 'demissao_sem_justa_causa') {
            $intervaloTotal = $dataAdmissao->diff($dataProjetadaFimContrato);
            $mesesFgts = $intervaloTotal->y * 12 + $intervaloTotal->m;
            if ($intervaloTotal->d > 0) $mesesFgts++;
            // Simplificação: base FGTS = salário base. Em um caso real, seria a média das remunerações.
            $saldoEstimadoFgts = $salarioBase * $mesesFgts * 0.08;
            $multaFgts = $saldoEstimadoFgts * 0.40;
        }

        $remuneracaoAnterior = 0;
        if (!empty($dados['remuneracao_mes_anterior'])) {
            $remuneracaoAnterior = (float)str_replace(['.', ','], ['', '.'], $dados['remuneracao_mes_anterior']);
        }

        return [
            'funcionario' => [
                'id' => $funcionario['id'],
                'nome' => $funcionario['nome'],
                'data_admissao' => $funcionario['data_admissao'],
                'data_desligamento' => $dados['data_desligamento'],
                'data_aviso_previo' => $dados['data_aviso_previo'],
                'motivo' => $dados['motivo_rescisao'],
                'aviso_previo' => $dados['aviso_previo'],
                'tipo_contrato' => $dados['tipo_contrato'] ?? 'Indeterminado',
                'cod_afastamento' => $dados['cod_afastamento'] ?? '',
                'remuneracao_mes_anterior' => $remuneracaoAnterior,
                'pensao_trct_percent' => $dados['pensao_trct_percent'] ?? 0,
                'pensao_fgts_percent' => $dados['pensao_fgts_percent'] ?? 0,
                'horas_extras_50_qtd' => $dados['horas_extras_50_qtd'] ?? 0,
                'dependentes' => $dados['dependentes'] ?? 0,
                'horas_extras_100_qtd' => $dados['horas_extras_100_qtd'] ?? 0,
                'pensao_alimenticia_valor' => $dados['pensao_alimenticia_valor'] ?? 0,
                'adicional_periculosidade' => $dados['adicional_periculosidade'] ?? 0,
                'dsr' => $dados['dsr'] ?? 0,
                'categoria_trabalhador' => $dados['categoria_trabalhador'] ?? '',
                'codigo_sindical' => $dados['codigo_sindical'] ?? '',
                'cnpj_sindicato' => $dados['cnpj_sindicato'] ?? '',
                'nome_sindicato' => $dados['nome_sindicato'] ?? '',
            ],
            'referencias' => $referencias,
            'verbas' => ['proventos' => $proventos, 'descontos' => $descontos],
            'totais' => ['total_proventos' => $totalProventos, 'total_descontos' => $totalDescontos, 'total_liquido' => $totalProventos - $totalDescontos],
            'outros' => ['multa_fgts' => $multaFgts],
            'full_funcionario_data' => $funcionario // Adiciona todos os dados do funcionário para uso no PDF
        ];
    }

    /**
     * Busca a remuneração do mês anterior para fins de rescisão.
     * Tenta buscar na folha de pagamento (mock), senão usa o salário base.
     * @param int $funcionarioId
     * @return float
     */
    public function getRemuneracaoAnterior(int $funcionarioId): float
    {
        // Define o mês anterior
        $mesAnterior = (int)date('m', strtotime('-1 month'));
        $anoAnterior = (int)date('Y', strtotime('-1 month'));

        // Busca na folha calculada (simulação de histórico)
        $folha = $this->getFolhaCalculada($mesAnterior, $anoAnterior);

        foreach ($folha as $registro) {
            if ($registro['id'] == $funcionarioId) {
                return (float)$registro['salario_bruto'];
            }
        }

        // Se não encontrar na folha (ex: recém-admitido ou dados mock limitados),
        // retorna o salário contratual atual.
        $funcionario = $this->getFuncionarioById($funcionarioId);
        return $funcionario ? (float)$funcionario['salario'] : 0.0;
    }

    /**
     * Calcula o número de períodos de férias vencidas (30 dias) que o funcionário possui.
     * Um período de férias é considerado vencido se não foi gozado dentro do período concessivo
     * (12 meses após o término do período aquisitivo) e a data de desligamento já ultrapassou
     * o fim desse período concessivo.
     *
     * @param \DateTime $dataAdmissao Data de admissão do funcionário.
     * @param \DateTime $dataDesligamento Data de desligamento do funcionário.
     * @param int $funcionarioId ID do funcionário.
     * @return int O número de períodos de férias vencidas.
     */
    private function calcularPeriodosFeriasVencidas(
        \DateTime $dataAdmissao,
        \DateTime $dataDesligamento,
        int $funcionarioId
    ): int {
        // 1. Obter o total de dias de férias já gozadas pelo funcionário.
        $sqlFeriasTiradas = "SELECT data_inicio_ferias, dias_ferias FROM ferias_historico WHERE funcionario_id = ? ORDER BY data_inicio_ferias ASC";
        $stmtFeriasTiradas = $this->db->prepare($sqlFeriasTiradas);
        $stmtFeriasTiradas->execute([$funcionarioId]);
        $feriasTiradasRegistros = $stmtFeriasTiradas->fetchAll(PDO::FETCH_ASSOC);
        $totalDiasGozados = array_sum(array_column($feriasTiradasRegistros, 'dias_ferias'));

        $dataInicioAquisitivo = clone $dataAdmissao;
        $periodosVencidos = 0;

        // 2. Loop para cada período aquisitivo completo até a data de desligamento.
        while ($dataInicioAquisitivo < $dataDesligamento) {
            // O período aquisitivo termina 12 meses após o início.
            $dataFimAquisitivo = (clone $dataInicioAquisitivo)->modify('+12 months -1 day');

            // Se o período aquisitivo ainda não terminou na data de desligamento, não pode haver férias vencidas para ele.
            if ($dataFimAquisitivo >= $dataDesligamento) {
                break;
            }

            // O período concessivo termina 12 meses após o fim do período aquisitivo.
            $dataFimConcessivo = (clone $dataFimAquisitivo)->modify('+12 months');

            // 3. Verifica se o período concessivo terminou ANTES da data de desligamento.
            // Se sim, o funcionário deveria ter tirado as férias.
            if ($dataFimConcessivo < $dataDesligamento) {
                // Se ainda há dias de férias gozadas para abater, abate 30 dias.
                if ($totalDiasGozados >= 30) {
                    $totalDiasGozados -= 30;
                } else {
                    // Se não há dias suficientes para abater, este período está vencido.
                    $periodosVencidos++;
                }
            }

            // 4. Avança para o próximo período aquisitivo.
            $dataInicioAquisitivo->modify('+1 year');
        }

        return $periodosVencidos;
    }

    /**
     * Calcula o valor da contribuição ao INSS com base no salário bruto.
     * Utiliza a tabela progressiva de 2024.
     *
     * @param float $baseCalculo O salário bruto do funcionário.
     * @return float O valor do desconto de INSS.
     */
    private function calcularINSS(float $baseCalculo): float
    {
        // Tabela INSS 2024 (cálculo progressivo por parcela a deduzir)
        $faixas = [
            ['limite' => 1412.00, 'aliquota' => 0.075, 'parcela' => 0],
            ['limite' => 2666.68, 'aliquota' => 0.09,  'parcela' => 21.18],
            ['limite' => 4000.03, 'aliquota' => 0.12,  'parcela' => 101.18],
            ['limite' => 7786.02, 'aliquota' => 0.14,  'parcela' => 181.18],
        ];

        $tetoINSS = 908.85;

        if ($baseCalculo > $faixas[3]['limite']) {
            return $tetoINSS;
        }

        $inss = 0.0;
        foreach ($faixas as $faixa) {
            if ($baseCalculo <= $faixa['limite']) {
                $inss = ($baseCalculo * $faixa['aliquota']) - $faixa['parcela'];
                break;
            }
        }

        return round($inss, 2);
    }

    /**
     * Calcula o valor do Imposto de Renda Retido na Fonte (IRRF).
     *
     * @param float $baseCalculo Base de cálculo (Salário Bruto - INSS).
     * @param int $numDependentes Número de dependentes para dedução.
     * @return float O valor do imposto a ser retido.
     */
    private function calcularIRRF(float $baseCalculo, int $numDependentes): float
    {
        $deducaoDependentes = $numDependentes * self::DEDUCAO_DEPENDENTE_IRRF;
        $baseIrrf = $baseCalculo - $deducaoDependentes;

        // Tabela Progressiva IRRF (a partir de Fev/2024)
        if ($baseIrrf <= 2259.20) return 0.0;
        if ($baseIrrf <= 2826.65) $irrf = ($baseIrrf * 0.075) - 169.44;
        elseif ($baseIrrf <= 3751.05) $irrf = ($baseIrrf * 0.15) - 381.44;
        elseif ($baseIrrf <= 4664.68) $irrf = ($baseIrrf * 0.225) - 662.77;
        else $irrf = ($baseIrrf * 0.275) - 896.00;

        return $irrf > 0 ? round($irrf, 2) : 0.0;
    }
}
