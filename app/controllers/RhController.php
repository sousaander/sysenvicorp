<?php

namespace App\Controllers;

use App\Core\Connection;
use App\Models\RhModel;

use Dompdf\Dompdf;

class RhController extends BaseController
{
    private $rhModel;

    public function __construct()
    {
        parent::__construct(); // Garante que a sessão seja inicializada
        $this->rhModel = new RhModel();
    }

    public function index()
    {
        // Coleta dados do modelo
        $filtros = [
            'nome' => filter_input(INPUT_GET, 'nome', FILTER_SANITIZE_SPECIAL_CHARS),
            'setor' => filter_input(INPUT_GET, 'setor', FILTER_SANITIZE_SPECIAL_CHARS)
        ];

        $summary = $this->rhModel->getRhSummaryData();
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

        $data = array_merge([
            'pageTitle' => 'Recursos Humanos - Gestão de Pessoas',
            'aniversariantes' => $aniversariantes,
            'funcionarios' => $funcionarios,
            'filtros' => $filtros, // Envia os filtros de volta para a view
            'paginaAtual' => $paginaAtual,
            'totalPaginas' => $totalPaginas,
        ], $summary);

        $this->renderView('rh/index', $data);
    }

    // Exemplo de outra ação
    public function registroFuncionario()
    {
        $data = ['pageTitle' => 'RH - Registro de Funcionário'];
        $this->renderView('rh/registro_funcionario', $data);
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

        // Coleta e valida os dados do $_POST
        $dadosFuncionario = $_POST;
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

        // Agora, a chamada real ao Model para salvar no banco de dados.
        $resultado = $this->rhModel->salvarFuncionario($dadosFuncionario);

        if ($resultado) {
            $mensagem = $id ? 'Funcionário atualizado com sucesso!' : 'Funcionário cadastrado com sucesso!';
            $this->setFlashMessage('success', $mensagem);
            // Redireciona para a lista principal, que é um comportamento mais seguro
            header('Location: ' . BASE_URL . '/rh');
        } else {
            // Busca a mensagem de erro do model, se houver, para diagnóstico
            $erroDetalhado = method_exists($this->rhModel, 'getLastError') ? $this->rhModel->getLastError() : null;

            // Personaliza a mensagem de erro para o usuário final.
            if ($erroDetalhado && strpos($erroDetalhado, 'E-mail já cadastrado') !== false) {
                $mensagemErro = 'Não foi possível salvar. O e-mail informado já está em uso por outro usuário.';
            } else {
                $mensagemErro = 'Erro ao salvar funcionário.' . ($erroDetalhado ? " Detalhe: $erroDetalhado" : '');
            }
            $this->setFlashMessage('error', $mensagemErro);
            // Armazena os dados do post na sessão para repreencher o formulário
            $this->session->set('form_data', $dadosFuncionario);
            // Volta para o formulário em caso de erro, mantendo os dados se possível
            $redirectUrl = $id ? '/rh/editar/' . $id : '/rh/registroFuncionario';
            header('Location: ' . BASE_URL . $redirectUrl);
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

        // Reutiliza a view de registro, passando os dados do funcionário
        $data = [
            'pageTitle' => 'Editar Funcionário',
            'funcionario' => $funcionario
        ];
        $this->renderView('rh/registro_funcionario', $data);
    }

    /**
     * Exclui um funcionário.
     * @param int $id O ID do funcionário.
     */
    public function excluir(int $id)
    {
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

        if (!$funcionario) {
            $this->setFlashMessage('error', 'Funcionário não encontrado.');
            // Fecha a aba/janela se o funcionário não for encontrado
            echo "<script>window.close();</script>";
            exit();
        }

        $data = [
            'pageTitle' => 'Ficha Cadastral - ' . $funcionario['nome'],
            'funcionario' => $funcionario
        ];
        $this->renderView('rh/ficha_cadastral_pdf', $data);
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
        // Busca os dados calculados do model
        $resultados = $this->rhModel->getFolhaCalculada($mes, $ano);

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
        $holerites = $this->rhModel->getDadosHolerite($mes, $ano, $funcionario_id);

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

        $data = [
            'pageTitle' => "Lançamentos da Folha - $mes/$ano",
            'funcionarios' => $funcionarios,
            'mes' => $mes,
            'ano' => $ano
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

        // Armazena os lançamentos na sessão, separados por competência.
        $this->session->set("lancamentos_folha.{$ano}.{$mes}", $lancamentos);

        $this->setFlashMessage('success', "Eventos para a folha de $mes/$ano salvos com sucesso!");
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

        $encargos = $this->rhModel->getEncargosCalculados($mes, $ano);

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

        $dadosExportacao = $this->rhModel->getDadosExportacaoContabil($mes, $ano);

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
        $data = [
            'pageTitle' => 'RH - Cálculo de Rescisão'
        ];
        $this->renderView('layouts/calculo_rescisao', $data);
    }

    /**
     * Gera o espelho da folha de pagamento para impressão.
     * @param int $mes
     * @param int $ano
     */
    public function espelhoFolha(int $mes, int $ano)
    {
        // Busca os dados consolidados do model
        $dadosEspelho = $this->rhModel->getDadosEspelhoFolha($mes, $ano);

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
        $dadosRelatorio = $this->rhModel->getDadosEspelhoFolha($mes, $ano);

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
        $dompdf = new Dompdf();
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

        // Renderiza a view do PDF em uma variável
        ob_start();
        $this->renderPartial('rh/relatorio_ferias_pdf', ['calculo' => $calculo]);
        $html = ob_get_clean();

        // Gera o PDF
        $dompdf = new Dompdf();
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

        // Renderiza a view do PDF em uma variável
        ob_start();
        $this->renderPartial('rh/recibo_ferias_pdf', ['calculo' => $calculo]);
        $html = ob_get_clean();

        // Gera o PDF
        $dompdf = new Dompdf();
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
}
