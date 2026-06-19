<?php

namespace App\Controllers;

use App\Core\Connection;
use App\Models\RhModel;
use App\Models\EmpresaModel;
use App\Models\TreinamentosModel;

use Dompdf\Dompdf;
use Dompdf\Options;

class RhController extends BaseController
{
    private $rhModel;
    private $empresaModel;
    private $treinamentosModel;

    public function __construct()
    {
        parent::__construct(); // Garante que a sessão seja inicializada
        $this->rhModel = new RhModel();
        $this->empresaModel = new EmpresaModel();
        $this->treinamentosModel = new TreinamentosModel();
    }

    public function registroFuncionario()
    {
        $csrf_token = $this->generateCsrfToken();
        $data = [
            'pageTitle' => 'Novo Funcionário',
            'csrf_token' => $csrf_token
        ];
        $this->renderView('rh/registro_funcionario', $data);
    }

    public function index()
    {
        // Coleta dados do modelo
        $filtros = [
            'nome' => filter_input(INPUT_GET, 'nome', FILTER_SANITIZE_SPECIAL_CHARS),
            'setor' => filter_input(INPUT_GET, 'setor', FILTER_SANITIZE_SPECIAL_CHARS)
        ];

        $summary = $this->rhModel->getRhSummaryData();

        // Busca a quantidade de funcionários atualmente em férias
        $funcionariosFerias = $this->rhModel->getFuncionariosEmFeriasCount();

        // Busca retornos próximos
        $retornosBreve = $this->rhModel->getRetornosFeriasBreve(7);

        // Lógica de Meta de Contratação (Exemplo: meta de 5 por mês)
        $metaContratacao = 5;
        $percentualContratacao = ($summary['novasContratacoesMes'] / $metaContratacao) * 100;
        $percentualContratacao = min($percentualContratacao, 100); // Limita a 100%

        // Busca a lista de funcionários em férias
        $listaFuncionariosFerias = $this->rhModel->getFuncionariosEmFeriasList();

        // Busca a distribuição real por setores
        $summary['setores'] = $this->rhModel->getFuncionariosPorSetor();

        // Busca o próximo treinamento corporativo do banco de dados
        $proximo = $this->treinamentosModel->getProximoTreinamento();
        if ($proximo) {
            $summary['proximoTreinamento'] = $proximo;
        }

        // Busca a contagem real de treinamentos para o KPI
        $summary['totalTreinamentos'] = $this->treinamentosModel->getTreinamentosCount();

        $aniversariantes = $this->rhModel->getAniversariantesSemana();

        // Lógica de Paginação
        $paginaAtual = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1;
        $itensPorPagina = 3; // Define quantos funcionários por página
        $offset = ($paginaAtual - 1) * $itensPorPagina;

        // Busca os funcionários da página atual
        $funcionarios = $this->rhModel->getFuncionarios($filtros, $itensPorPagina, $offset);
        // Conta o total de funcionários para calcular o total de páginas
        $totalFuncionarios = $this->rhModel->getFuncionariosCount($filtros);
        $totalPaginas = ceil($totalFuncionarios / $itensPorPagina);

        $csrf_token = $this->generateCsrfToken();

        $data = array_merge([
            'pageTitle' => 'Recursos Humanos - Gestão de Pessoas',
            'aniversariantes' => $aniversariantes,
            'funcionariosFerias' => $funcionariosFerias,
            'listaFuncionariosFerias' => $listaFuncionariosFerias,
            'funcionarios' => $funcionarios,
            'filtros' => $filtros, // Envia os filtros de volta para a view
            'paginaAtual' => $paginaAtual,
            'totalPaginas' => $totalPaginas,
            'retornosBreve' => $retornosBreve,
            'metaContratacao' => $metaContratacao,
            'csrf_token' => $csrf_token,
            'percentualContratacao' => $percentualContratacao
        ], $summary);

        $this->renderView('rh/index', $data);
    }
    /**
     * Salva um novo funcionário.
     */
    public function salvar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/rh');
            exit();
        }

        // Validação de CSRF para segurança
        $postToken = $_POST['csrf_token'] ?? '';
        if (!$this->validateCsrfToken($postToken)) {
            $this->setFlashMessage('error', 'Token de segurança inválido ou expirado.');
            header('Location: ' . BASE_URL . '/rh');
            exit();
        }

        // Coleta e valida os dados do $_POST
        $dadosFuncionario = $_POST;

        // Remove a máscara do campo celular antes de salvar (mantém apenas números)
        if (!empty($dadosFuncionario['celular'])) {
            $dadosFuncionario['celular'] = preg_replace('/\D/', '', $dadosFuncionario['celular']);
        } else {
            $dadosFuncionario['celular'] = null;
        }

        // Garante que o ID seja um inteiro se estiver presente (correção para edição)
        if (!empty($dadosFuncionario['id'])) {
            $dadosFuncionario['id'] = (int)$dadosFuncionario['id'];
        }
        $id = $dadosFuncionario['id'] ?? null;

        // Validação de campos obrigatórios no servidor
        if (empty(trim($dadosFuncionario['nome'])) || empty(trim($dadosFuncionario['cargo'])) || empty(trim($dadosFuncionario['setor'])) || empty(trim($dadosFuncionario['email']))) {
            $this->setFlashMessage('error', 'Erro ao salvar: Os campos Nome, Cargo, Setor e E-mail são obrigatórios.');
            // Armazena os dados do post na sessão para repreencher o formulário
            $this->session->set('form_data', $dadosFuncionario);
            // Volta para o formulário em caso de erro
            $redirectUrl = $id ? '/rh/editar/' . $id : '/rh/registroFuncionario';
            header('Location: ' . BASE_URL . $redirectUrl);
            exit();
        }

        try {
            // --- LÓGICA DE UPLOAD DE ARQUIVOS ---
            $funcionarioAntigo = $id ? $this->rhModel->getFuncionarioById($id) : null;

            // Foto
            if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
                $fotoDir = ROOT_PATH . '/storage/fotos_funcionarios/';
                if (!is_dir($fotoDir)) mkdir($fotoDir, 0775, true);
                
                $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
                $newFotoFilename = "foto_" . ($id ?? 'new') . "_" . time() . '.' . $ext;
                
                if (move_uploaded_file($_FILES['foto']['tmp_name'], $fotoDir . $newFotoFilename)) {
                    $dadosFuncionario['foto_path'] = $newFotoFilename;
                    if ($funcionarioAntigo && !empty($funcionarioAntigo['foto_path'])) {
                        @unlink($fotoDir . $funcionarioAntigo['foto_path']);
                    }
                }
            }

            // Documentos
            $documentosDir = ROOT_PATH . '/storage/documentos_pessoais/';
            if (!is_dir($documentosDir)) mkdir($documentosDir, 0775, true);

            $docs = ['anexo_rg', 'anexo_cnh', 'anexo_titulo', 'anexo_reservista'];
            foreach ($docs as $key) {
                if (isset($_FILES[$key]) && $_FILES[$key]['error'] === UPLOAD_ERR_OK) {
                    $ext = strtolower(pathinfo($_FILES[$key]['name'], PATHINFO_EXTENSION));
                    $prefix = str_replace('anexo_', '', $key);
                    $newFile = "func_{$prefix}_" . ($id ?? 'new') . "_" . time() . ".{$ext}";
                    
                    if (move_uploaded_file($_FILES[$key]['tmp_name'], $documentosDir . $newFile)) {
                        $dadosFuncionario["{$key}_path"] = $newFile;
                        if ($funcionarioAntigo && !empty($funcionarioAntigo["{$key}_path"])) {
                            @unlink($documentosDir . $funcionarioAntigo["{$key}_path"]);
                        }
                    }
                }
            }

            $resultado = $this->rhModel->salvarFuncionario($dadosFuncionario);

            if ($resultado) {
                $this->setFlashMessage('success', $id ? 'Funcionário atualizado!' : 'Funcionário cadastrado!');
                $this->logAction($id ? 'UPDATE' : 'CREATE', ($id ? "Atualizou" : "Cadastrou") . " funcionário: {$dadosFuncionario['nome']}", 'RH', $id ?: $resultado);
                header('Location: ' . BASE_URL . '/rh');
            } else {
                throw new \Exception($this->rhModel->getLastError() ?: 'Erro desconhecido no banco de dados.');
            }

        } catch (\Exception $e) {
            $this->setFlashMessage('error', 'Erro: ' . $e->getMessage());
            $this->session->set('form_data', $dadosFuncionario);
            header('Location: ' . BASE_URL . ($id ? "/rh/editar/$id" : "/rh/registroFuncionario"));
        }
        exit();
    }

    /**
     * Exibe os detalhes de um funcionário específico.
     * @param int $id O ID do funcionário.
     */
    public function detalhe(int $id)
    {
        // Agora busca os dados do funcionário através do Model
        $funcionario = $this->rhModel->getFuncionarioById($id);

        if (!$funcionario) {
            $this->setFlashMessage('error', 'Funcionário não encontrado.');
            header('Location: ' . BASE_URL . '/rh');
            exit();
        }

        $data = ['pageTitle' => 'Detalhes do Funcionário', 'funcionario' => $funcionario];
        $this->renderView('rh/detalhe', $data);
    }

    /**
     * Exibe o formulário para editar um funcionário.
     * @param int $id O ID do funcionário.
     */
    public function editar(int $id)
    {
        $funcionario = $this->rhModel->getFuncionarioById($id);

        if (!$funcionario) {
            $this->setFlashMessage('error', 'Funcionário não encontrado.');
            header('Location: ' . BASE_URL . '/rh');
            exit();
        }

        $csrf_token = $this->generateCsrfToken();

        // Reutiliza a view de registro, passando os dados do funcionário
        $data = [
            'pageTitle' => 'Editar Funcionário',
            'funcionario' => $funcionario,
            'csrf_token' => $csrf_token
        ];
        $this->renderView('rh/registro_funcionario', $data);
    }

    /**
     * Exclui um funcionário.
     * @param int $id O ID do funcionário.
     */
    public function excluir(int $id)
    {
        // Se o ID não veio pela rota (argumento zerado), tenta pegar do POST
        if ($id <= 0) {
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: 0;
        }

        // Validação para garantir que o ID é válido
        if ($id <= 0) {
            $this->setFlashMessage('error', 'ID de funcionário inválido.');
            header('Location: ' . BASE_URL . '/rh');
            exit();
        }

        if ($this->rhModel->excluirFuncionario($id)) {
            $this->setFlashMessage('success', 'Funcionário excluído com sucesso!');
        } else {
            $this->setFlashMessage('error', 'Erro ao excluir o funcionário.');
        }

        header('Location: ' . BASE_URL . '/rh');
        exit();
    }

    /**
     * Gera a ficha cadastral de um funcionário para impressão/PDF.
     * @param int $id O ID do funcionário.
     */
    public function fichaCadastral(int $id)
    {
        $funcionario = $this->rhModel->getFuncionarioById($id);
        $empresa = $this->empresaModel->getDadosEmpresa();

        if (!$funcionario) {
            $this->setFlashMessage('error', 'Funcionário não encontrado.');
            // Redireciona de volta para a lista de RH
            header('Location: ' . BASE_URL . '/rh');
            exit();
        }

        $data = [
            'pageTitle' => 'Ficha Cadastral - ' . $funcionario['nome'],
            'funcionario' => $funcionario,
            'empresa' => $empresa
        ];

        // 1. Captura o HTML da view do relatório em uma variável
        ob_start();
        // Usamos renderPartial para não incluir o layout principal do sistema
        $this->renderPartial('rh/ficha_cadastral_pdf', $data);
        $html = ob_get_clean();

        // 2. Configura e instancia o Dompdf
        $options = new Options();
        // Habilita o carregamento de imagens e CSS locais ou remotos
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);

        // 3. Carrega o HTML no Dompdf
        $dompdf->loadHtml($html);

        // 4. Define o tamanho e a orientação do papel
        $dompdf->setPaper('A4', 'portrait');

        // 5. Renderiza o HTML como PDF
        $dompdf->render();

        // 6. Envia o PDF gerado para o navegador para ser exibido
        $dompdf->stream("ficha_cadastral_" . $funcionario['id'] . ".pdf", ["Attachment" => false]);
        exit();
    }

    /**
     * Exibe o dashboard da Folha de Pagamento.
     */
    public function folhaDePagamento()
    {
        $data = ['pageTitle' => 'RH - Folha de Pagamento'];
        $this->renderView('rh/folha_de_pagamento', $data);
    }

    /**
     * Processa o cálculo da folha de pagamento para um determinado mês/ano.
     */
    public function calcularFolha()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/rh/folhaDePagamento');
            exit();
        }

        $mes = filter_input(INPUT_POST, 'mes', FILTER_VALIDATE_INT);
        $ano = filter_input(INPUT_POST, 'ano', FILTER_VALIDATE_INT);

        if (!$mes || !$ano) {
            $this->setFlashMessage('error', 'Mês ou ano inválido para o cálculo.');
            header('Location: ' . BASE_URL . '/rh/folhaDePagamento');
            exit();
        }

        if ($this->rhModel->calcularFolhaDePagamento($mes, $ano)) {
            $this->setFlashMessage('success', "Folha de pagamento para $mes/$ano calculada com sucesso! Exibindo resultados.");
            // Redireciona para a nova tela de visualização
            header('Location: ' . BASE_URL . "/rh/verFolha/$mes/$ano");
        } else {
            $this->setFlashMessage('error', "Erro ao calcular a folha de pagamento para $mes/$ano.");
            header('Location: ' . BASE_URL . '/rh/folhaDePagamento');
        }

        exit();
    }

    /**
     * Exibe os resultados de uma folha de pagamento já calculada.
     * @param int $mes
     * @param int $ano
     */
    public function verFolha(int $mes, int $ano)
    {
        // Busca os dados calculados do model, agora usando os lançamentos do BD
        $lancamentos = $this->rhModel->getLancamentos($mes, $ano);
        $resultados = $this->rhModel->getFolhaCalculada($mes, $ano, $lancamentos);

        $data = [
            'pageTitle' => "Resultados da Folha - $mes/$ano",
            'resultados' => $resultados,
            'mes' => $mes,
            'ano' => $ano
        ];

        $this->renderView('rh/folha_resultado', $data);
    }

    /**
     * Exibe o(s) holerite(s) para uma competência.
     * Se o ID do funcionário for fornecido, exibe o individual.
     * Caso contrário, exibe todos (para geração em lote).
     *
     * @param int $mes
     * @param int $ano
     * @param int|null $funcionario_id
     */
    public function holerite(int $mes, int $ano, int $funcionario_id = null)
    {
        // Busca os dados do(s) holerite(s) no model
        // Busca os lançamentos do banco de dados para passar para o cálculo
        $lancamentos = $this->rhModel->getLancamentos($mes, $ano);
        $holerites = $this->rhModel->getDadosHolerite($mes, $ano, $funcionario_id, $lancamentos);

        $data = [
            'pageTitle' => $funcionario_id ? "Holerite Individual - $mes/$ano" : "Holerites em Lote - $mes/$ano",
            'holerites' => $holerites,
            'mes' => $mes,
            'ano' => $ano
        ];

        // Renderiza a view diretamente para garantir uma impressão limpa, sem o template principal.
        extract($data);
        require ROOT_PATH . '/views/rh/holerite.php';
        exit();
    }

    /**
     * Exibe a tela para selecionar um funcionário e gerar sua ficha cadastral.
     */
    public function relatorioFichaCadastral()
    {
        // Busca todos os funcionários ativos para o select
        $funcionarios = $this->rhModel->getFuncionarios([], 999, 0); // Removido filtro de status para simplificar e garantir que funcione

        $data = [
            'pageTitle' => 'RH - Relatório de Ficha Cadastral',
            'funcionarios' => $funcionarios
        ];
        $this->renderView('rh/relatorio_ficha_cadastral', $data);
    }

    /**
     * Exibe a página de relatórios e indicadores de RH.
     */
    public function relatorios()
    {
        $data = ['pageTitle' => 'RH - Relatórios e Indicadores'];
        $this->renderView('rh/relatorios', $data);
    }

    /**
     * Exibe a tela para lançamento de eventos da folha (horas extras, faltas).
     */
    public function lancamentos()
    {
        // O formulário agora envia via POST, então pegamos os dados do POST.
        $mes = filter_input(INPUT_POST, 'mes', FILTER_VALIDATE_INT);
        $ano = filter_input(INPUT_POST, 'ano', FILTER_VALIDATE_INT);

        if (!$mes || !$ano) {
            $this->setFlashMessage('error', 'Mês ou ano inválido para o lançamento.');
            header('Location: ' . BASE_URL . '/rh/folhaDePagamento');
            exit();
        }

        // Busca funcionários ativos para a lista de lançamento
        $funcionarios = $this->rhModel->getFuncionarios(['status' => 'Ativo']);

        // Busca lançamentos existentes no banco para preencher o formulário
        $lancamentosExistentes = $this->rhModel->getLancamentos($mes, $ano);

        $data = [
            'pageTitle' => "Lançamentos da Folha - $mes/$ano",
            'funcionarios' => $funcionarios,
            'mes' => $mes,
            'ano' => $ano,
            'lancamentos' => $lancamentosExistentes,
        ];

        $this->renderView('rh/lancamentos', $data);
    }

    /**
     * Salva os eventos da folha (simulação).
     */
    public function salvarLancamentos()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/rh/folhaDePagamento');
            exit();
        }

        $mes = $_POST['mes'];
        $ano = $_POST['ano'];
        $lancamentos = $_POST['lancamentos'];

        // Salva os lançamentos no banco de dados através do model
        if ($this->rhModel->salvarLancamentosBD($lancamentos, (int)$mes, (int)$ano)) {
            $this->setFlashMessage('success', "Eventos para a folha de $mes/$ano salvos com sucesso!");
        } else {
            $this->setFlashMessage('error', "Erro ao salvar os eventos da folha.");
        }

        header('Location: ' . BASE_URL . '/rh/folhaDePagamento');
        exit();
    }

    /**
     * Exibe a tela de resumo de encargos sociais.
     */
    public function encargos()
    {
        $mes = filter_input(INPUT_POST, 'mes', FILTER_VALIDATE_INT);
        $ano = filter_input(INPUT_POST, 'ano', FILTER_VALIDATE_INT);

        if (!$mes || !$ano) {
            $this->setFlashMessage('error', 'Mês ou ano inválido para ver os encargos.');
            header('Location: ' . BASE_URL . '/rh/folhaDePagamento');
            exit();
        }

        // Busca os lançamentos do banco de dados para passar para o cálculo
        $lancamentos = $this->rhModel->getLancamentos($mes, $ano);
        $encargos = $this->rhModel->getEncargosCalculados($mes, $ano, $lancamentos);

        $data = [
            'pageTitle' => "Encargos Sociais - $mes/$ano",
            'encargos' => $encargos,
            'mes' => $mes,
            'ano' => $ano
        ];
        $this->renderView('rh/encargos', $data);
    }

    /**
     * Gera e força o download de um arquivo CSV com os dados consolidados da folha
     * para integração com a contabilidade.
     *
     * @param int $mes
     * @param int $ano
     */
    public function exportarFolhaContabil(int $mes, int $ano)
    {
        if (!$mes || !$ano) {
            $this->setFlashMessage('error', 'Mês ou ano inválido para exportação.');
            header('Location: ' . BASE_URL . '/rh/folhaDePagamento');
            exit();
        }

        // Busca os lançamentos do banco de dados para passar para o cálculo
        $lancamentos = $this->rhModel->getLancamentos($mes, $ano);
        $dadosExportacao = $this->rhModel->getDadosExportacaoContabil($mes, $ano, $lancamentos);

        if (empty($dadosExportacao)) {
            $this->setFlashMessage('error', 'Não há dados para exportar para esta competência.');
            header('Location: ' . BASE_URL . "/rh/verFolha/$mes/$ano");
            exit();
        }

        $nomeArquivo = "export_contabil_folha_{$mes}_{$ano}.csv";

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $nomeArquivo);

        // Cria um ponteiro para o output
        $output = fopen('php://output', 'w');

        // Escreve o cabeçalho do CSV
        fputcsv($output, ['Competencia', 'Tipo', 'Descricao', 'Valor', 'ContaDebito', 'ContaCredito'], ';');

        // Escreve os dados
        foreach ($dadosExportacao as $linha) {
            fputcsv($output, $linha, ';');
        }

        fclose($output);
        exit();
    }

    /**
     * Exibe a tela para cálculo de rescisão.
     */
    public function calculoRescisao()
    {
        // Busca todos os funcionários ativos para o select
        $funcionarios = $this->rhModel->getFuncionarios(['status' => 'Ativo'], 999, 0);

        $data = [
            'pageTitle' => 'RH - Cálculo de Rescisão',
            'funcionarios' => $funcionarios,
        ];
        $this->renderView('rh/calculo_rescisao', $data);
    }

    /**
     * Processa o cálculo de rescisão e exibe os resultados.
     */
    public function processarCalculoRescisao()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/rh/calculoRescisao');
            exit();
        }

        // Coleta e valida os dados do formulário
        $dadosFormulario = [
            'funcionario_id' => filter_input(INPUT_POST, 'funcionario_id', FILTER_VALIDATE_INT),
            'data_admissao' => filter_input(INPUT_POST, 'data_admissao'),
            'data_desligamento' => filter_input(INPUT_POST, 'data_desligamento'),
            'data_aviso_previo' => filter_input(INPUT_POST, 'data_aviso_previo'),
            'motivo_rescisao' => filter_input(INPUT_POST, 'motivo_rescisao', FILTER_SANITIZE_SPECIAL_CHARS),
            'aviso_previo' => filter_input(INPUT_POST, 'aviso_previo', FILTER_SANITIZE_SPECIAL_CHARS),
            'ferias_vencidas' => filter_input(INPUT_POST, 'ferias_vencidas', FILTER_SANITIZE_SPECIAL_CHARS) === 'sim',
            'data_fim_contrato' => filter_input(INPUT_POST, 'data_fim_contrato'),
            'tipo_contrato' => filter_input(INPUT_POST, 'tipo_contrato', FILTER_SANITIZE_SPECIAL_CHARS),
            'adiantamento_salarial' => filter_input(INPUT_POST, 'adiantamento_salarial', FILTER_SANITIZE_SPECIAL_CHARS),
            'adiantamento_13' => filter_input(INPUT_POST, 'adiantamento_13', FILTER_SANITIZE_SPECIAL_CHARS),
            'horas_extras_50_qtd' => filter_input(INPUT_POST, 'horas_extras_50_qtd', FILTER_VALIDATE_FLOAT),
            'horas_extras_100_qtd' => filter_input(INPUT_POST, 'horas_extras_100_qtd', FILTER_VALIDATE_FLOAT),
            'comissoes' => filter_input(INPUT_POST, 'comissoes', FILTER_SANITIZE_SPECIAL_CHARS),
            'gratificacoes' => filter_input(INPUT_POST, 'gratificacoes', FILTER_SANITIZE_SPECIAL_CHARS),
            'adicional_noturno' => filter_input(INPUT_POST, 'adicional_noturno', FILTER_SANITIZE_SPECIAL_CHARS),
            'adicional_periculosidade' => filter_input(INPUT_POST, 'adicional_periculosidade', FILTER_SANITIZE_SPECIAL_CHARS),
            'dsr' => filter_input(INPUT_POST, 'dsr', FILTER_SANITIZE_SPECIAL_CHARS),
            'ajuste_saldo_devedor' => filter_input(INPUT_POST, 'ajuste_saldo_devedor', FILTER_SANITIZE_SPECIAL_CHARS),
            'outros_descontos' => filter_input(INPUT_POST, 'outros_descontos', FILTER_SANITIZE_SPECIAL_CHARS),
            'cod_afastamento' => filter_input(INPUT_POST, 'cod_afastamento', FILTER_SANITIZE_SPECIAL_CHARS),
            'remuneracao_mes_anterior' => filter_input(INPUT_POST, 'remuneracao_mes_anterior', FILTER_SANITIZE_SPECIAL_CHARS),
            'pensao_trct_percent' => filter_input(INPUT_POST, 'pensao_trct_percent', FILTER_VALIDATE_FLOAT),
            'pensao_fgts_percent' => filter_input(INPUT_POST, 'pensao_fgts_percent', FILTER_VALIDATE_FLOAT),
            'pensao_alimenticia_valor' => filter_input(INPUT_POST, 'pensao_alimenticia_valor', FILTER_SANITIZE_SPECIAL_CHARS),
            'dependentes' => filter_input(INPUT_POST, 'dependentes', FILTER_VALIDATE_INT) ?? 0,
            'categoria_trabalhador' => filter_input(INPUT_POST, 'categoria_trabalhador', FILTER_SANITIZE_SPECIAL_CHARS),
            'codigo_sindical' => filter_input(INPUT_POST, 'codigo_sindical', FILTER_SANITIZE_SPECIAL_CHARS),
            'cnpj_sindicato' => filter_input(INPUT_POST, 'cnpj_sindicato', FILTER_SANITIZE_SPECIAL_CHARS),
            'nome_sindicato' => filter_input(INPUT_POST, 'nome_sindicato', FILTER_SANITIZE_SPECIAL_CHARS),
        ];

        // Validação básica
        if (!$dadosFormulario['funcionario_id'] || !$dadosFormulario['data_desligamento'] || !$dadosFormulario['motivo_rescisao']) {
            $this->setFlashMessage('error', 'Dados inválidos para o cálculo de rescisão. Preencha todos os campos obrigatórios.');
            header('Location: ' . BASE_URL . '/rh/calculoRescisao');
            exit();
        }

        // Chama o model para realizar o cálculo
        $calculo = $this->rhModel->calcularValoresRescisao($dadosFormulario);

        if (!$calculo) {
            $this->setFlashMessage('error', 'Não foi possível calcular a rescisão. Verifique os dados do funcionário e as regras de cálculo.');
            header('Location: ' . BASE_URL . '/rh/calculoRescisao');
            exit();
        }

        $this->renderView('rh/calculo_rescisao_resultado', ['pageTitle' => 'Resultado do Cálculo de Rescisão', 'calculo' => $calculo]);
    }

    /**
     * Retorna a remuneração do mês anterior de um funcionário via AJAX.
     */
    public function getRemuneracaoAjax()
    {
        header('Content-Type: application/json');

        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

        if (!$id) {
            echo json_encode(['valor' => '']);
            exit;
        }

        $valor = $this->rhModel->getRemuneracaoAnterior($id);

        echo json_encode(['valor' => number_format($valor, 2, ',', '.')]);
        exit;
    }

    /**
     * Gera o Aviso Prévio em PDF.
     */
    public function gerarAvisoPrevioPdf()
    {
        // Validação básica para garantir que os dados foram passados
        if (empty($_GET['calculo'])) {
            $this->setFlashMessage('error', 'Dados insuficientes para gerar o Aviso Prévio.');
            header('Location: ' . BASE_URL . '/rh/calculoRescisao');
            exit();
        }

        // Decodifica os dados do cálculo passados via GET
        $calculo = json_decode(urldecode($_GET['calculo']), true);

        // Busca dados adicionais da empresa para o cabeçalho do PDF
        $empresa = $this->empresaModel->getDadosEmpresa();

        $data = [
            'pageTitle' => 'Aviso Prévio',
            'calculo' => $calculo,
            'empresa' => $empresa,
        ];

        ob_start();
        $this->renderPartial('rh/aviso_previo_pdf', $data);
        $html = ob_get_clean();

        $dompdf = new Dompdf(['isRemoteEnabled' => true]);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $nomeArquivo = "Aviso_Previo_" . preg_replace('/[^A-Za-z0-9]/', '_', $calculo['funcionario']['nome']) . ".pdf";
        $dompdf->stream($nomeArquivo, ["Attachment" => false]);
        exit();
    }

    /**
     * Gera o Termo de Rescisão de Contrato de Trabalho (TRCT) em PDF.
     */
    public function gerarTrctPdf()
    {
        // Validação básica para garantir que os dados foram passados
        if (empty($_GET['calculo'])) {
            $this->setFlashMessage('error', 'Dados insuficientes para gerar o TRCT.');
            header('Location: ' . BASE_URL . '/rh/calculoRescisao');
            exit();
        }

        // Decodifica os dados do cálculo passados via GET
        $calculo = json_decode(urldecode($_GET['calculo']), true);

        // Busca dados adicionais da empresa para o cabeçalho do PDF
        $empresa = $this->empresaModel->getDadosEmpresa();

        $data = [
            'pageTitle' => 'Termo de Rescisão de Contrato de Trabalho',
            'calculo' => $calculo,
            'empresa' => $empresa,
        ];

        // Renderiza a view do PDF em uma variável
        ob_start();
        $this->renderPartial('rh/trct_pdf', $data);
        $html = ob_get_clean();

        // Configura e instancia o Dompdf
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $nomeArquivo = "TRCT_" . preg_replace('/[^A-Za-z0-9]/', '_', $calculo['funcionario']['nome']) . ".pdf";
        $dompdf->stream($nomeArquivo, ["Attachment" => false]);
        exit();
    }

    /**
     * Gera o espelho da folha de pagamento para impressão.
     * @param int $mes
     * @param int $ano
     */
    public function espelhoFolha(int $mes, int $ano)
    {
        // Busca os dados consolidados do model
        // Busca os lançamentos do banco de dados para passar para o cálculo
        $lancamentos = $this->rhModel->getLancamentos($mes, $ano);
        $dadosEspelho = $this->rhModel->getDadosEspelhoFolha($mes, $ano, $lancamentos);

        $data = [
            'pageTitle' => "Espelho da Folha de Pagamento - $mes/$ano",
            'dados' => $dadosEspelho,
            'mes' => $mes,
            'ano' => $ano
        ];

        // Renderiza a view do espelho diretamente, sem o template principal,
        // para garantir uma impressão limpa.
        extract($data);
        require ROOT_PATH . '/views/rh/espelho_folha.php';
        exit();
    }

    /**
     * Gera um relatório PDF dos resultados da folha de pagamento.
     * @param int $mes
     * @param int $ano
     */
    public function exportarFolhaPdf(int $mes, int $ano)
    {
        // Reutiliza a mesma lógica de busca de dados do espelho da folha
        $lancamentos = $this->rhModel->getLancamentos($mes, $ano);
        $dadosRelatorio = $this->rhModel->getDadosEspelhoFolha($mes, $ano, $lancamentos);

        $data = [
            'pageTitle' => "Resultados da Folha de Pagamento - $mes/$ano",
            'dados' => $dadosRelatorio,
            'mes' => $mes,
            'ano' => $ano
        ];

        // Para exportação em PDF, renderizamos a view diretamente
        // sem o template principal, para que apenas o conteúdo da folha seja exibido.
        extract($data); // Torna as variáveis do array $data acessíveis na view
        require ROOT_PATH . '/views/rh/folha_resultado_pdf.php';
        exit(); // Garante que nenhum outro conteúdo (como o template principal) seja renderizado
    }

    /**
     * Exibe a tela para cálculo de férias.
     */
    public function calculoFerias()
    {
        // Busca todos os funcionários ativos para o select
        $funcionarios = $this->rhModel->getFuncionarios(['status' => 'Ativo'], 999, 0);

        $data = [
            'pageTitle' => 'RH - Cálculo de Férias',
            'funcionarios' => $funcionarios,
        ];
        $this->renderView('rh/calculo_ferias', $data);
    }

    /**
     * Processa o cálculo de férias e exibe os resultados.
     */
    public function processarCalculoFerias()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/rh/calculoFerias');
            exit();
        }

        $funcionarioId = filter_input(INPUT_POST, 'funcionario_id', FILTER_VALIDATE_INT);
        $dataInicio = filter_input(INPUT_POST, 'data_inicio');
        $diasFerias = filter_input(INPUT_POST, 'dias_ferias', FILTER_VALIDATE_INT);

        if (!$funcionarioId || !$dataInicio || !$diasFerias) {
            $this->setFlashMessage('error', 'Dados inválidos para o cálculo de férias.');
            header('Location: ' . BASE_URL . '/rh/calculoFerias');
            exit();
        }

        $calculo = $this->rhModel->calcularValoresFerias($funcionarioId, $dataInicio, $diasFerias);

        if (!$calculo) {
            $this->setFlashMessage('error', 'Não foi possível calcular as férias. Verifique os dados do funcionário.');
            header('Location: ' . BASE_URL . '/rh/calculoFerias');
            exit();
        }

        // Após o cálculo bem-sucedido, registra no histórico
        if (!$this->rhModel->registrarCalculoFerias($calculo)) {
            // Loga um erro, mas não impede o usuário de ver o resultado
            error_log("Falha ao registrar cálculo de férias no histórico para o funcionário ID: " . $funcionarioId);
        }

        $data = [
            'pageTitle' => 'Resultado do Cálculo de Férias',
            'calculo' => $calculo,
        ];

        $this->renderView('rh/calculo_ferias_resultado', $data);
    }

    /**
     * Gera o Aviso de Férias em PDF.
     */
    public function gerarAvisoFeriasPdf()
    {
        $funcionarioId = filter_input(INPUT_GET, 'funcionario_id', FILTER_VALIDATE_INT);
        $dataInicio = filter_input(INPUT_GET, 'data_inicio');
        $diasFerias = filter_input(INPUT_GET, 'dias_ferias', FILTER_VALIDATE_INT);

        $dados = $this->rhModel->getDadosParaAvisoFerias($funcionarioId, $dataInicio, $diasFerias);

        if (!$dados) {
            $this->setFlashMessage('error', 'Dados insuficientes para gerar o aviso de férias.');
            header('Location: ' . BASE_URL . '/rh/calculoFerias');
            exit();
        }

        // Renderiza a view do PDF em uma variável
        ob_start();
        $this->renderPartial('rh/aviso_ferias_pdf', ['dados' => $dados]);
        $html = ob_get_clean();

        // Gera o PDF
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream("aviso_de_ferias_" . $dados['funcionario']['nome'] . ".pdf", ["Attachment" => false]);
        exit();
    }

    /**
     * Gera o Relatório de Cálculo de Férias em PDF.
     */
    public function gerarRelatorioFeriasPdf()
    {
        $calculo = json_decode(urldecode($_GET['calculo']), true);
        $empresa = $this->empresaModel->getDadosEmpresa();

        // Renderiza a view do PDF em uma variável
        ob_start();
        $this->renderPartial('rh/relatorio_ferias_pdf', ['calculo' => $calculo, 'empresa' => $empresa]);
        $html = ob_get_clean();

        // Gera o PDF
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream("relatorio_ferias_" . $calculo['funcionario']['nome'] . ".pdf", ["Attachment" => false]);
        exit();
    }

    /**
     * Gera o Recibo de Pagamento de Férias em PDF.
     */
    public function gerarReciboFeriasPdf()
    {
        $calculo = json_decode(urldecode($_GET['calculo']), true);
        $empresa = $this->empresaModel->getDadosEmpresa();

        // Renderiza a view do PDF em uma variável
        ob_start();
        $this->renderPartial('rh/recibo_ferias_pdf', ['calculo' => $calculo, 'empresa' => $empresa]);
        $html = ob_get_clean();

        // Gera o PDF
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream("recibo_pagamento_ferias_" . $calculo['funcionario']['nome'] . ".pdf", ["Attachment" => false]);
        exit();
    }

    /**
     * Exibe o histórico de cálculos de férias realizados.
     */
    public function historicoFerias()
    {
        // Lógica de Paginação
        $paginaAtual = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1;
        $itensPorPagina = 10;
        $offset = ($paginaAtual - 1) * $itensPorPagina;

        $historico = $this->rhModel->getHistoricoFerias($itensPorPagina, $offset);
        $totalRegistros = $this->rhModel->getHistoricoFeriasCount();
        $totalPaginas = ceil($totalRegistros / $itensPorPagina);

        $data = [
            'pageTitle' => 'Histórico de Cálculo de Férias',
            'historico' => $historico,
            'paginaAtual' => $paginaAtual,
            'totalPaginas' => $totalPaginas,
        ];

        $this->renderView('rh/historico_ferias', $data);
    }

    /**
     * Exibe o formulário para editar um registro do histórico de férias.
     * @param int $id O ID do registro no histórico.
     */
    public function editarHistoricoFerias(int $id)
    {
        $registro = $this->rhModel->getHistoricoFeriasById($id);

        if (!$registro) {
            $this->setFlashMessage('error', 'Registro de histórico não encontrado.');
            header('Location: ' . BASE_URL . '/rh/historicoFerias');
            exit();
        }

        // Busca todos os funcionários para o select
        $funcionarios = $this->rhModel->getFuncionarios(['status' => 'Ativo'], 999, 0);

        $data = [
            'pageTitle' => 'Editar Cálculo de Férias',
            'registro' => $registro,
            'funcionarios' => $funcionarios,
        ];

        $this->renderView('rh/editar_historico_ferias', $data);
    }

    /**
     * Processa a atualização de um registro do histórico de férias.
     */
    public function atualizarHistoricoFerias()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/rh/historicoFerias');
            exit();
        }

        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $funcionarioId = filter_input(INPUT_POST, 'funcionario_id', FILTER_VALIDATE_INT);
        $dataInicio = filter_input(INPUT_POST, 'data_inicio');
        $diasFerias = filter_input(INPUT_POST, 'dias_ferias', FILTER_VALIDATE_INT);

        if (!$id || !$funcionarioId || !$dataInicio || !$diasFerias) {
            $this->setFlashMessage('error', 'Dados inválidos para a atualização.');
            header('Location: ' . BASE_URL . '/rh/editarHistoricoFerias/' . $id);
            exit();
        }

        // Recalcula os valores com os novos dados
        $novoCalculo = $this->rhModel->calcularValoresFerias($funcionarioId, $dataInicio, $diasFerias);

        if (!$novoCalculo) {
            $this->setFlashMessage('error', 'Não foi possível recalcular as férias. Verifique os dados do funcionário.');
            header('Location: ' . BASE_URL . '/rh/editarHistoricoFerias/' . $id);
            exit();
        }

        if ($this->rhModel->atualizarRegistroHistorico($id, $novoCalculo)) {
            $this->setFlashMessage('success', 'Registro do histórico de férias atualizado com sucesso!');
        } else {
            $this->setFlashMessage('error', 'Erro ao atualizar o registro no histórico.');
        }

        header('Location: ' . BASE_URL . '/rh/historicoFerias');
        exit();
    }

    /**
     * Exclui um registro do histórico de férias.
     * @param int $id O ID do registro no histórico.
     */
    public function excluirHistoricoFerias(int $id)
    {
        if ($this->rhModel->excluirRegistroHistorico($id)) {
            $this->setFlashMessage('success', 'Registro do histórico excluído com sucesso!');
        } else {
            $this->setFlashMessage('error', 'Erro ao excluir o registro do histórico.');
        }

        header('Location: ' . BASE_URL . '/rh/historicoFerias');
        exit();
    }

    /**
     * Exibe a tela de Gestão de Treinamentos.
     */
    public function treinamentos()
    {
        // Recupera os treinamentos do banco de dados corporativo
        $paginaAtual = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1;
        $itensPorPagina = 10;
        $offset = ($paginaAtual - 1) * $itensPorPagina;

        $treinamentos = $this->treinamentosModel->getAllTreinamentos($itensPorPagina, $offset);
        $totalRegistros = $this->treinamentosModel->getTreinamentosCount();
        $totalPaginas = ceil($totalRegistros / $itensPorPagina);

        $data = [
            'pageTitle' => 'Gestão de Treinamentos',
            'treinamentos' => $treinamentos,
            'paginaAtual' => $paginaAtual,
            'totalPaginas' => $totalPaginas
        ];
        $this->renderView('rh/treinamentos', $data);
    }

    /**
     * Salva um novo treinamento (corporativo).
     */
    public function salvarTreinamento()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/rh/treinamentos');
            exit();
        }

        $dados = [
            'id' => !empty($_POST['id']) ? (int)$_POST['id'] : null,
            'nome_treinamento' => filter_input(INPUT_POST, 'nome_treinamento', FILTER_SANITIZE_SPECIAL_CHARS),
            'descricao' => filter_input(INPUT_POST, 'descricao', FILTER_SANITIZE_SPECIAL_CHARS) ?? '',
            'data_prevista' => filter_input(INPUT_POST, 'data_prevista'),
            'instrutor' => filter_input(INPUT_POST, 'instrutor', FILTER_SANITIZE_SPECIAL_CHARS),
            'local' => filter_input(INPUT_POST, 'local', FILTER_SANITIZE_SPECIAL_CHARS) ?? '',
            'status' => filter_input(INPUT_POST, 'status', FILTER_SANITIZE_SPECIAL_CHARS) ?? 'Agendado',
        ];

        if (empty($dados['nome_treinamento']) || empty($dados['data_prevista'])) {
            $this->setFlashMessage('error', 'Nome e Data Prevista são obrigatórios.');
            header('Location: ' . BASE_URL . '/rh/treinamentos');
            exit();
        }

        if ($this->treinamentosModel->salvarTreinamento($dados)) {
            $this->setFlashMessage('success', 'Treinamento salvo com sucesso!');
        } else {
            $this->setFlashMessage('error', 'Erro ao agendar treinamento.');
        }

        header('Location: ' . BASE_URL . '/rh/treinamentos');
        exit();
    }

    /**
     * Exclui um treinamento agendado.
     * @param int $id
     */
    public function excluirTreinamento(int $id)
    {
        if ($this->treinamentosModel->excluirTreinamento($id)) {
            $this->setFlashMessage('success', 'Treinamento excluído com sucesso!');
        } else {
            $this->setFlashMessage('error', 'Erro ao excluir treinamento.');
        }
        header('Location: ' . BASE_URL . '/rh/treinamentos');
        exit();
    }

    /**
     * Gera o relatório de funcionários em PDF.
     */
    public function gerarRelatorioFuncionariosPdf()
    {
        // Pega o status da URL, com 'Todos' como valor padrão.
        $status = $_GET['status'] ?? 'Todos';

        // Busca os dados no banco de dados usando o método do model.
        $funcionarios = $this->rhModel->getFuncionariosRelatorio($status);
        $empresa = $this->empresaModel->getDadosEmpresa();

        // Prepara os dados para a view do PDF.
        $data = [
            'funcionarios' => $funcionarios,
            'filtroStatus' => $status,
            'empresa' => $empresa
        ];

        // Renderiza a view HTML do PDF em uma variável.
        ob_start();
        $this->renderPartial('rh/relatorio_funcionarios_pdf', $data);
        $html = ob_get_clean();

        // Configuração e inicialização do Dompdf.
        $options = new Options();
        $options->set('isRemoteEnabled', true); // Permite carregar imagens/CSS externos, se necessário.
        $dompdf = new Dompdf($options);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait'); // Define o papel como A4 em modo retrato.
        $dompdf->render();

        // Envia o PDF para o navegador para ser exibido (não para download forçado).
        $dompdf->stream("relatorio_funcionarios_" . date('Y-m-d') . ".pdf", ["Attachment" => false]);
        exit(); // Garante que o script pare após enviar o PDF.
    }
}
