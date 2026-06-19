<?php

namespace App\Controllers;

use App\Models\ProjetosModel;
use App\Models\UsuarioModel;
use App\Core\Connection;
use App\Models\PerfilModel;
use App\Models\FinancialModel;
use App\Models\EmpresaModel;
use App\Models\ClientesModel;
use App\Models\FornecedoresModel;
// Importa as classes da biblioteca Dompdf
use Dompdf\Dompdf;
use Dompdf\Options;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class FinanceiroController extends BaseController
{
    private $financeiroModel;
    private $empresaModel;
    private $usuarioModel;
    private $projetosModel;
    private $perfilModel;
    private $clientesModel;
    private $fornecedoresModel;

    /**
     * Mapeia ações para as permissões necessárias.
     * O BaseController usará este mapa para verificar o acesso.
     * @var array
     */
    protected $requiredPermissions = [
        'index' => 'financeiro_dashboard_view',
        'movimentacoes' => 'financeiro_lancamentos_view',
        'pagar' => 'financeiro_lancamentos_view',
        'receber' => 'financeiro_lancamentos_view',
        'detalhe' => 'financeiro_lancamentos_view',
        'novo' => 'financeiro_lancamentos_create',
        'salvar' => 'financeiro_lancamentos_create',
        'editar' => 'financeiro_lancamentos_edit',
        'excluir' => 'financeiro_lancamentos_delete',
        'excluirEmMassa' => 'financeiro_lancamentos_delete',
        'liquidarEmMassa' => 'financeiro_lancamentos_edit',
        'bloquear' => 'financeiro_lancamentos_edit',
        'desbloquear' => 'financeiro_lancamentos_edit',
        'realizarTransferencia' => 'financeiro_transferencias_create',
        'relatorio' => 'financeiro_reports_view',
        'exportarRelatorioPdf' => 'financeiro_reports_view',
        'balanco' => 'financeiro_reports_view',
        'dre' => 'financeiro_reports_view',
        'exportarDrePdf' => 'financeiro_reports_view',
        'exportarBalancoPdf' => 'financeiro_reports_view',
        'enviarBalancoEmail' => 'financeiro_reports_view',
        'salvarMeta' => 'financeiro_reports_view',
        'baixarModelo' => 'financeiro_import_manage',
        'processarImportacao' => 'financeiro_import_manage',        
        'prestacaoContas' => 'financeiro_prestacao_contas_view',
        'salvarPrestacaoContas' => 'financeiro_prestacao_contas_submit',
        'aprovacaoPrestacaoContas' => 'financeiro_prestacao_contas_approve',
        'processarAprovacao' => 'financeiro_prestacao_contas_approve',
        'addClassificacao' => 'financeiro_lancamentos_create',
        'addCentroCusto' => 'financeiro_lancamentos_create',
        'processarAprovacaoEmMassa' => 'financeiro_prestacao_contas_approve',
        'processarReprovacaoEmMassa' => 'financeiro_prestacao_contas_approve',
        'addPrestacaoCategoria' => 'financeiro_prestacao_contas_submit', // Atrelado ao envio
        'searchClassificacoesAjax' => 'financeiro_lancamentos_view', // Permissão para buscar classificações
        'searchCentrosCustoAjax' => 'financeiro_lancamentos_view', // Permissão para buscar centros de custo
        'editarPrestacaoContas' => 'financeiro_prestacao_contas_edit_own',
        'excluirPrestacaoContas' => 'financeiro_prestacao_contas_delete_own',
        'relatorioPrestacaoContasProjeto' => 'financeiro_reports_view',
        'relatorioCombustivel' => 'financeiro_reports_view',
        'exportarRelatorioCombustivelPdf' => 'financeiro_reports_view',
        'exportarPrestacaoContasZip' => 'financeiro_reports_view',
        'prestacoesAprovadas' => 'financeiro_prestacao_contas_view_all',
        'pontualidade' => 'financeiro_reports_view', // New permission for the new card
    ];

    public function __construct()
    {
        parent::__construct();
        
        // Injeção de dependência para a conexão com o banco de dados
        $this->financeiroModel = new FinancialModel();
        $this->empresaModel = new EmpresaModel();
        $this->usuarioModel = new UsuarioModel();
        $this->projetosModel = new ProjetosModel();
        $this->perfilModel = new PerfilModel();
        $this->clientesModel = new ClientesModel();
        $this->fornecedoresModel = new FornecedoresModel();
    }

    public function index()
    {
        // Coleta os filtros da URL
        $filtros = [
            'tipo' => filter_input(INPUT_GET, 'tipo', FILTER_SANITIZE_SPECIAL_CHARS),
            'periodo' => filter_input(INPUT_GET, 'periodo', FILTER_SANITIZE_SPECIAL_CHARS) ?: 'recente',
            'data_unica' => filter_input(INPUT_GET, 'data_unica'),
            'mes_ano' => filter_input(INPUT_GET, 'mes_ano'),
            'data_inicio' => filter_input(INPUT_GET, 'data_inicio'),
            'data_fim' => filter_input(INPUT_GET, 'data_fim'),
            'mes_referencia' => filter_input(INPUT_GET, 'mes_referencia', FILTER_SANITIZE_SPECIAL_CHARS) ?: date('Y-m'),
        ];

        // Lógica de Paginação para o fluxo de caixa
        $paginaAtual = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1;
        $itensPorPagina = 5; // 5 itens por página, conforme solicitado
        $offset = ($paginaAtual - 1) * $itensPorPagina;

        // Coleta dados do modelo
        $fluxoCaixa = $this->financeiroModel->getResumoFluxoCaixa($filtros, $itensPorPagina, $offset);
        // Anexa informação sobre transferência (id da transação parceira) para uso nas views
        foreach ($fluxoCaixa as &$t) {
            $t['transfer_partner_id'] = null;
            if (!empty($t['documento_vinculado'])) {
                $t['transfer_partner_id'] = $this->financeiroModel->encontrarIdParceiroTransferenciaPorDocumento($t['documento_vinculado']);
            }
        }
        unset($t);

        $totalTransacoes = $this->financeiroModel->getContagemFluxoCaixa($filtros);
        $totalPaginas = ceil($totalTransacoes / $itensPorPagina);

        $contasPagar = $this->financeiroModel->getContasPagarMes($filtros['mes_referencia']);
        $contasReceber = $this->financeiroModel->getContasReceberMes($filtros['mes_referencia']);
        $listaContasPagar = $this->financeiroModel->getListaContasPagarMes(30, $filtros['mes_referencia']);
        $listaContasReceber = $this->financeiroModel->getListaContasReceberMes(30, $filtros['mes_referencia']);
        $saldoAtual = $this->financeiroModel->getSaldoAtual();
        $saldosBancos = $this->financeiroModel->getSaldosBancos();
        $bancos = $this->financeiroModel->getBancos(); // Busca a lista de bancos para o modal de relatório

        // Mapa de bancos por ID para uso nas views
        $bancosMap = [];
        foreach ($bancos as $b) {

            $bancosMap[$b['id']] = $b['nome'];
        }

        // Anexa nome do banco e info do parceiro de transferência para cada transação
        foreach ($fluxoCaixa as &$t) {
            $t['banco_nome'] = $bancosMap[$t['banco_id']] ?? 'N/A';
            $t['partner_banco_nome'] = null;
            if (!empty($t['documento_vinculado'])) {
                $t['transfer_partner_id'] = $this->financeiroModel->encontrarIdParceiroTransferenciaPorDocumento($t['documento_vinculado']);
                if (!empty($t['transfer_partner_id'])) {
                    $partner = $this->financeiroModel->getTransacaoPorId($t['transfer_partner_id']);
                    if ($partner) {
                        $t['partner_banco_nome'] = $bancosMap[$partner['banco_id']] ?? null;
                    }
                }
            } else {
                $t['transfer_partner_id'] = null;
            }
        }
        unset($t);

        // Busca um resumo das contas a receber atrasadas (contagem e valor total)
        $resumoAtrasadas = $this->financeiroModel->getResumoContasReceberAtrasadas();
        $resumoAtrasadasPagar = $this->financeiroModel->getResumoContasPagarAtrasadas();

        $proximoVencimento = $this->financeiroModel->getProximoVencimentoPagar();
        $ultimaAtualizacaoSaldo = date('Y-m-d H:i:s'); // Define data atual como referência de atualização

        // Determina o número de meses para o gráfico com base no filtro
        // Define o padrão como 'future_12' (Projeção 12 meses) se o filtro for 'recente' (abertura da página)
        $periodoSelecionado = ($filtros['periodo'] === 'recente') ? 'future_12' : $filtros['periodo'];
        $monthlySummary = [];
        $chartTitle = "Receitas vs. Despesas";

        if ($periodoSelecionado === 'future_6') {
            $monthlySummary = $this->financeiroModel->getResumoMensalFuturoParaGrafico(6);
            $chartTitle = "Projeção (Próximos 6 Meses)";
        } elseif ($periodoSelecionado === 'future_12') {
            $monthlySummary = $this->financeiroModel->getResumoMensalFuturoParaGrafico(12);
            $chartTitle = "Projeção (Próximos 12 Meses)";
        } else {
            $months = (int)$periodoSelecionado;
            if ($months <= 0) $months = 6;
            $periodoSelecionado = (string)$months; // Normaliza para string
            $monthlySummary = $this->financeiroModel->getResumoMensalParaGrafico($months, $filtros['mes_referencia']);
            $chartTitle = "Receitas vs. Despesas (Últimos $months Meses)";
        }

        $expenseSummary = $this->financeiroModel->getResumoDespesasPorCategoria($filtros['mes_referencia']);
        $costCenterSummary = $this->financeiroModel->getResumoDespesasPorCentroCusto($filtros['mes_referencia']);

        // Dados para Visão Geral (Próximos 12 Meses)
        $previsaoRecebimento = $this->financeiroModel->getPrevisaoRecebimento(12);
        $previsaoPagamento = $this->financeiroModel->getPrevisaoPagamento(12);
        $totalDespesasAno = $this->financeiroModel->getTotalDespesasAnoCorrente();
        $totalDespesasPagasAno = $this->financeiroModel->getTotalDespesasPagasAnoCorrente();
        $totalReceitasAno = $this->financeiroModel->getTotalReceitasAnoCorrente();
        $lucratividadeAno = $totalReceitasAno - $totalDespesasAno;
        $projecaoFinanceira = $saldoAtual + $previsaoRecebimento - $previsaoPagamento;

        // Busca projetos com orçamento estourado
        $analiseClientesPagamentos = $this->analiseClientesPagamentos($filtros['mes_referencia']); // Agora passa o mês de referência
        $projetosEstourados = $this->projetosModel->getProjetosComOrcamentoEstourado();

        // Busca movimentações automáticas via webhook através do Model
        $movimentacoesWebhook = $this->financeiroModel->getUltimasMovimentacoesWebhook(5);

        $data = [
            'pageTitle'          => 'Financeiro - Fluxo de Caixa',
            'fluxoCaixa'         => $fluxoCaixa,
            'contasPagarTotal'   => $contasPagar,
            'contasReceberTotal' => $contasReceber,
            'listaContasPagar'   => $listaContasPagar,
            'listaContasReceber' => $listaContasReceber,
            'saldoAtual'         => $saldoAtual,
            'saldosBancos'       => $saldosBancos,
            'paginaAtual'        => $paginaAtual,
            'totalPaginas'       => $totalPaginas,
            'filtros'            => $filtros, // Envia os filtros para a view
            'bancos'             => $bancos, // Passa a lista de bancos para a view
            'resumoAtrasadas'    => $resumoAtrasadas, // Contém 'count' e 'valor'
            'resumoAtrasadasPagar' => $resumoAtrasadasPagar, // Contém 'count' e 'valor' para pagar
            'ultimaAtualizacaoSaldo' => $ultimaAtualizacaoSaldo,
            'proximoVencimento'  => $proximoVencimento,
            'monthlySummaryJson' => json_encode($monthlySummary),
            'expenseSummaryJson' => json_encode($expenseSummary),
            'costCenterSummaryJson' => json_encode($costCenterSummary),
            'periodoSelecionado' => $periodoSelecionado,
            'chartTitle'         => $chartTitle,
            'previsaoRecebimento' => $previsaoRecebimento,
            'previsaoPagamento'   => $previsaoPagamento,
            'totalDespesasAno'    => $totalDespesasAno,
            'totalDespesasPagasAno' => $totalDespesasPagasAno,
            'lucratividadeAno'    => $lucratividadeAno,
            'projecaoFinanceira'  => $projecaoFinanceira,
            'projetosEstourados'  => $projetosEstourados,
            'movimentacoesWebhook' => $movimentacoesWebhook,
            'analiseClientesPagamentos' => $analiseClientesPagamentos, // Pass data to view
        ];

        $this->renderView('financeiro/index', $data);
    }

    /**
     * Exibe o relatório detalhado de pontualidade e comportamento de pagamento dos clientes.
     */
    public function pontualidade()
    {
        $mesReferencia = filter_input(INPUT_GET, 'mes_referencia', FILTER_SANITIZE_SPECIAL_CHARS) ?: date('Y-m');
        
        // Reutiliza a lógica estatística detalhada que já existe no model/helper
        $analise = $this->analiseClientesPagamentos($mesReferencia);

        $data = [
            'pageTitle' => 'Análise de Pontualidade de Clientes',
            'analiseClientesPagamentos' => $analise,
            'mesReferencia' => $mesReferencia,
        ];

        $this->renderView('financeiro/pontualidade', $data);
    }

    /**
     * Exibe a página de relatórios financeiros com opções de filtro.
     */
    public function relatorio()
    {
        // Coleta os filtros da URL
        $filtros = [
            'tipo_relatorio' => filter_input(INPUT_GET, 'tipo_relatorio', FILTER_SANITIZE_SPECIAL_CHARS) ?: 'geral', // 'geral' ou 'banco'
            'banco_id' => filter_input(INPUT_GET, 'banco_id', FILTER_VALIDATE_INT),
            'classificacao_id' => filter_input(INPUT_GET, 'classificacao_id', FILTER_VALIDATE_INT),
            'centro_custo_id' => filter_input(INPUT_GET, 'centro_custo_id', FILTER_VALIDATE_INT),
            'periodo' => filter_input(INPUT_GET, 'periodo', FILTER_SANITIZE_SPECIAL_CHARS) ?: 'recente',
            'status' => filter_input(INPUT_GET, 'status', FILTER_SANITIZE_SPECIAL_CHARS),
            'data_unica' => filter_input(INPUT_GET, 'data_unica'),
            'mes_ano' => filter_input(INPUT_GET, 'mes_ano') ?: (filter_input(INPUT_GET, 'periodo') === 'mes' ? date('Y-m') : null),
            'data_inicio' => filter_input(INPUT_GET, 'data_inicio'),
            'data_fim' => filter_input(INPUT_GET, 'data_fim'),
        ];

        // Se um banco for selecionado, garantimos o tipo correto para o cabeçalho do PDF
        if (!empty($filtros['banco_id'])) $filtros['tipo_relatorio'] = 'banco';

        // Lógica de Paginação
        $paginaAtual = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1;
        $itensPorPagina = 50; // Define a quantidade de registros por página
        $offset = ($paginaAtual - 1) * $itensPorPagina;

        // Busca as transações com base nos filtros (agora sempre busca algo por padrão)
        $transacoes = $this->financeiroModel->getTransacoesParaRelatorio($filtros, $itensPorPagina, $offset);
        $totalRegistros = $this->financeiroModel->getContagemTransacoesParaRelatorio($filtros);

        $bancos = $this->financeiroModel->getBancos();
        $classificacoes = $this->financeiroModel->getClassificacoes();
        $centrosCusto = $this->financeiroModel->getCentrosCusto();

        $data = [
            'pageTitle' => 'Relatório de Movimentações Financeiras',
            'transacoes' => $transacoes,
            'filtros' => $filtros,
            'bancos' => $bancos,
            'classificacoes' => $classificacoes,
            'centrosCusto' => $centrosCusto,
            'paginaAtual' => $paginaAtual,
            'totalPaginas' => ceil($totalRegistros / $itensPorPagina),
            'totalRegistros' => $totalRegistros
        ];

        $this->renderView('financeiro/relatorio', $data);
    }

    /**
     * Exibe a página de Balanço Financeiro detalhado (mês a mês).
     */
    public function balanco()
    {
        $ano = filter_input(INPUT_GET, 'ano', FILTER_VALIDATE_INT) ?: date('Y');

        // Busca o saldo atual real (bancos)
        $saldoAtual = $this->financeiroModel->getSaldoAtual();

        // Busca o detalhamento mensal para o ano selecionado
        $balancoMensal = $this->financeiroModel->getBalancoMensal($ano);

        // Busca totais pendentes para projeção (apenas o que falta pagar/receber)
        $meses = 12; // Mantém projeção futura fixa para o card de "Projeção"
        $previsaoRecebimento = $this->financeiroModel->getPrevisaoRecebimento($meses);
        $previsaoPagamento = $this->financeiroModel->getPrevisaoPagamento($meses);
        $saldoProjetado = $saldoAtual + $previsaoRecebimento - $previsaoPagamento;

        // Busca a meta mensal da configuração (Padrão: 10.000)
        $config = $this->getFinanceiroConfig();
        $metaMensal = $config['meta_mensal'] ?? 10000.00;

        // --- LÓGICA PARA AS LINHAS DE SALDO ACUMULADO ---
        $saldoInicioAno = $this->financeiroModel->getSaldoInicioAno($ano);
        $chartCumulativeReal = [];
        $chartCumulativeProjected = [];
        $cumulativeReal = $saldoInicioAno;
        $cumulativeProjected = $saldoInicioAno;

        if (!empty($balancoMensal)) {
            foreach ($balancoMensal as $m) {
                $resultadoReal = $m['receitas_realizadas'] - $m['despesas_realizadas'];
                $resultadoProjetado = $m['receitas_previstas'] - $m['despesas_previstas'];

                $cumulativeReal += $resultadoReal;
                $cumulativeProjected += $resultadoProjetado;

                $chartCumulativeReal[] = $cumulativeReal;
                $chartCumulativeProjected[] = $cumulativeProjected;
            }
        }

        $data = [
            'pageTitle' => 'Balanço Financeiro Detalhado',
            'saldoAtual' => $saldoAtual,
            'balancoMensal' => $balancoMensal,
            'saldoProjetado' => $saldoProjetado,
            'previsaoRecebimento' => $previsaoRecebimento,
            'previsaoPagamento' => $previsaoPagamento,
            'anoSelecionado' => $ano,
            'metaMensal' => $metaMensal,
            'saldoInicioAno' => $saldoInicioAno,
            'chartCumulativeReal' => $chartCumulativeReal,
            'chartCumulativeProjected' => $chartCumulativeProjected,
        ];

        $this->renderView('financeiro/balanco', $data);
    }

    /**
     * Exibe a página de DRE (Demonstrativo de Resultado do Exercício).
     */
    public function dre()
    {
        $ano = filter_input(INPUT_GET, 'ano', FILTER_VALIDATE_INT) ?: date('Y');
        $mes = filter_input(INPUT_GET, 'mes', FILTER_VALIDATE_INT) ?: null;
        $regime = filter_input(INPUT_GET, 'regime', FILTER_SANITIZE_SPECIAL_CHARS) ?: 'competencia';

        $dreData = $this->financeiroModel->getDadosDRE($ano, $regime, $mes);

        $data = [
            'pageTitle' => 'Demonstrativo de Resultado (DRE)',
            'dreData' => $dreData,
            'anoSelecionado' => $ano,
            'mesSelecionado' => $mes,
            'regimeSelecionado' => $regime,
        ];

        $this->renderView('financeiro/dre', $data);
    }

    /**
     * Gera o DRE em PDF.
     */
    public function exportarDrePdf()
    {
        $ano = filter_input(INPUT_GET, 'ano', FILTER_VALIDATE_INT) ?: date('Y');
        $mes = filter_input(INPUT_GET, 'mes', FILTER_VALIDATE_INT) ?: null;
        $regime = filter_input(INPUT_GET, 'regime', FILTER_SANITIZE_SPECIAL_CHARS) ?: 'competencia';

        $dreData = $this->financeiroModel->getDadosDRE($ano, $regime, $mes);

        // Busca dados da empresa
        $infoEmpresa = $this->empresaModel->getDadosEmpresa();

        $data = [
            'pageTitle' => 'Demonstrativo de Resultado (DRE) - ' . $ano,
            'dreData' => $dreData,
            'anoSelecionado' => $ano,
            'mesSelecionado' => $mes,
            'dataGeracao' => date('d/m/Y H:i:s'),
            'regime' => $regime,
            'empresa' => $infoEmpresa,
        ];

        ob_start();
        $this->renderPartial('financeiro/dre_pdf', $data);
        $html = ob_get_clean();

        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', '300');
        ini_set('pcre.backtrack_limit', '5000000');
        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream("dre_" . $ano . ".pdf", ["Attachment" => false]);
        exit();
    }

    /**
     * Gera o balanço financeiro detalhado em PDF.
     */
    public function exportarBalancoPdf()
    {
        $ano = filter_input(INPUT_GET, 'ano', FILTER_VALIDATE_INT) ?: date('Y');

        // Busca os mesmos dados da visualização em tela
        $saldoAtual = $this->financeiroModel->getSaldoAtual();
        $balancoMensal = $this->financeiroModel->getBalancoMensal($ano);

        $meses = 12;
        $previsaoRecebimento = $this->financeiroModel->getPrevisaoRecebimento($meses);
        $previsaoPagamento = $this->financeiroModel->getPrevisaoPagamento($meses);
        $saldoProjetado = $saldoAtual + $previsaoRecebimento - $previsaoPagamento;

        // Busca dados da empresa
        $infoEmpresa = $this->empresaModel->getDadosEmpresa();

        $data = [
            'pageTitle' => 'Balanço Financeiro - ' . $ano,
            'saldoAtual' => $saldoAtual,
            'balancoMensal' => $balancoMensal,
            'saldoProjetado' => $saldoProjetado,
            'anoSelecionado' => $ano,
            'dataGeracao' => date('d/m/Y H:i:s'),
            'empresa' => $infoEmpresa,
        ];

        ob_start();
        $this->renderPartial('financeiro/balanco_pdf', $data);
        $html = ob_get_clean();

        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', '300');
        ini_set('pcre.backtrack_limit', '5000000');
        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $dompdf->stream("balanco_financeiro_" . $ano . ".pdf", ["Attachment" => false]);
        exit();
    }

    /**
     * Gera o PDF do balanço e envia por e-mail.
     */
    public function enviarBalancoEmail()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/financeiro/balanco');
            exit();
        }

        $emailDestino = filter_input(INPUT_POST, 'email_destino', FILTER_VALIDATE_EMAIL);
        $ano = filter_input(INPUT_POST, 'ano', FILTER_VALIDATE_INT) ?: date('Y');

        if (!$emailDestino) {
            $this->setFlashMessage('error', 'E-mail de destino inválido.');
            header('Location: ' . BASE_URL . '/financeiro/balanco?ano=' . $ano);
            exit();
        }

        // 1. Busca os dados (mesma lógica do balanco e exportarBalancoPdf)
        $saldoAtual = $this->financeiroModel->getSaldoAtual();
        $balancoMensal = $this->financeiroModel->getBalancoMensal($ano);
        $meses = 12;
        $previsaoRecebimento = $this->financeiroModel->getPrevisaoRecebimento($meses);
        $previsaoPagamento = $this->financeiroModel->getPrevisaoPagamento($meses);
        $saldoProjetado = $saldoAtual + $previsaoRecebimento - $previsaoPagamento;

        // Busca dados da empresa
        $infoEmpresa = $this->empresaModel->getDadosEmpresa();

        $data = [
            'pageTitle' => 'Balanço Financeiro - ' . $ano,
            'saldoAtual' => $saldoAtual,
            'balancoMensal' => $balancoMensal,
            'saldoProjetado' => $saldoProjetado,
            'anoSelecionado' => $ano,
            'dataGeracao' => date('d/m/Y H:i:s'),
            'empresa' => $infoEmpresa,
        ];

        // 2. Gera o PDF em memória
        ob_start();
        $this->renderPartial('financeiro/balanco_pdf', $data);
        $html = ob_get_clean();

        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', '300');
        ini_set('pcre.backtrack_limit', '5000000');
        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $pdfContent = $dompdf->output(); // Obtém o conteúdo binário do PDF

        // 3. Envia o E-mail e valida resultado
        $assunto = "Balanço Financeiro Detalhado - " . $ano;
        $corpo = "Olá,<br><br>Segue em anexo o balanço financeiro detalhado referente ao ano de <strong>{$ano}</strong>.<br><br>Atenciosamente,<br>SysEnviCorp";

        if ($this->enviarEmailComAnexo($emailDestino, $assunto, $corpo, $pdfContent, "balanco_financeiro_{$ano}.pdf")) {
            $this->setFlashMessage('success', 'Balanço enviado com sucesso para ' . $emailDestino);
        } else {
            $this->setFlashMessage('error', 'O PDF foi gerado, mas houve uma falha técnica ao enviar o e-mail. Verifique as configurações de SMTP.');
        }

        header('Location: ' . BASE_URL . '/financeiro/balanco?ano=' . $ano);
        exit();
    }

    /**
     * Salva a meta mensal definida pelo usuário.
     */
    public function salvarMeta()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $meta = $_POST['meta_mensal'] ?? '0';

            // Sanitiza valor monetário (ex: 1.500,00 -> 1500.00)
            $meta = str_replace('.', '', $meta);
            $meta = str_replace(',', '.', $meta);
            $meta = (float) $meta;

            $config = $this->getFinanceiroConfig();
            $config['meta_mensal'] = $meta;

            $this->saveFinanceiroConfig($config);

            $this->setFlashMessage('success', 'Meta mensal atualizada com sucesso!');

            $ano = filter_input(INPUT_POST, 'ano', FILTER_VALIDATE_INT) ?: date('Y');
            header('Location: ' . BASE_URL . '/financeiro/balanco?ano=' . $ano);
            exit();
        }

        // Redirecionamento de segurança caso a requisição não seja POST
        header('Location: ' . BASE_URL . '/financeiro/balanco');
        exit();
    }

    private function getFinanceiroConfig()
    {
        $configFile = ROOT_PATH . '/storage/config/financeiro.json';
        if (file_exists($configFile)) {
            return json_decode(file_get_contents($configFile), true) ?? [];
        }
        return [];
    }

    private function saveFinanceiroConfig(array $config)
    {
        $dir = ROOT_PATH . '/storage/config';
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        file_put_contents($dir . '/financeiro.json', json_encode($config, JSON_PRETTY_PRINT));
    }

    /**
     * Método auxiliar privado para envio de e-mail com anexo (string).
     */
    private function enviarEmailComAnexo($destinatario, $assunto, $corpo, $anexoConteudo, $nomeAnexo)
    {
        if (!defined('MAIL_HOST')) return false;

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = defined('MAIL_HOST') ? MAIL_HOST : 'localhost';
            $mail->SMTPAuth   = true;
            $mail->Username   = defined('MAIL_USERNAME') ? MAIL_USERNAME : '';
            $mail->Password   = defined('MAIL_PASSWORD') ? MAIL_PASSWORD : '';
            $mail->SMTPSecure = (defined('MAIL_ENCRYPTION') && MAIL_ENCRYPTION === 'ssl') ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = defined('MAIL_PORT') ? MAIL_PORT : 587;
            $mail->CharSet    = 'UTF-8';

            $fromEmail = defined('MAIL_FROM_ADDRESS') ? MAIL_FROM_ADDRESS : 'contato@envicorp.com.br';
            $fromName = defined('MAIL_FROM_NAME') ? MAIL_FROM_NAME : 'SysEnviCorp';

            $mail->setFrom($fromEmail, $fromName);
            $mail->addAddress($destinatario);

            $mail->isHTML(true);
            $mail->Subject = $assunto;
            $mail->Body    = $corpo;

            // Adiciona o anexo a partir da string (conteúdo do PDF)
            $mail->addStringAttachment($anexoConteudo, $nomeAnexo, 'base64', 'application/pdf');

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Erro ao enviar e-mail de balanço: {$mail->ErrorInfo}");
            return false;
        }
    }

    /**
     * Exibe a página com todas as movimentações paginadas.
     */
    public function movimentacoes()
    {
        // Filtros simples compatíveis com a listagem
        $filtros = [
            'tipo' => filter_input(INPUT_GET, 'tipo', FILTER_SANITIZE_SPECIAL_CHARS),
            'status' => filter_input(INPUT_GET, 'status', FILTER_SANITIZE_SPECIAL_CHARS),
            'data_inicio' => filter_input(INPUT_GET, 'data_inicio'),
            'data_fim' => filter_input(INPUT_GET, 'data_fim'),
            'periodo' => filter_input(INPUT_GET, 'periodo', FILTER_SANITIZE_SPECIAL_CHARS) ?: 'recente',
            'ordem' => filter_input(INPUT_GET, 'ordem', FILTER_SANITIZE_SPECIAL_CHARS),
            'direcao' => filter_input(INPUT_GET, 'direcao', FILTER_SANITIZE_SPECIAL_CHARS),
        ];

        $paginaAtual = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1;
        $itensPorPagina = 10;
        $offset = ($paginaAtual - 1) * $itensPorPagina;

        $fluxoCaixa = $this->financeiroModel->getResumoFluxoCaixa($filtros, $itensPorPagina, $offset);
        // Mapa de bancos por ID
        $bancos = $this->financeiroModel->getBancos();
        $bancosMap = [];
        foreach ($bancos as $b) {
            $bancosMap[$b['id']] = $b;
        }

        // Anexa nome do banco e info do parceiro de transferência para uso nas views
        foreach ($fluxoCaixa as &$t) {
            $t['banco_nome'] = $bancosMap[$t['banco_id']]['nome'] ?? 'N/A';
            $t['banco_color'] = $bancosMap[$t['banco_id']]['cor'] ?? '#FFFFFF';
            $t['transfer_partner_id'] = null;
            if (!empty($t['documento_vinculado'])) {
                $t['transfer_partner_id'] = $this->financeiroModel->encontrarIdParceiroTransferenciaPorDocumento($t['documento_vinculado']);
                if (!empty($t['transfer_partner_id'])) {
                    $partner = $this->financeiroModel->getTransacaoPorId($t['transfer_partner_id']);
                    if ($partner) {
                        $t['partner_banco_nome'] = $bancosMap[$partner['banco_id']] ?? null;
                    }
                }
            }
        }
        unset($t);

        $totalTransacoes = $this->financeiroModel->getContagemFluxoCaixa($filtros);
        $totalPaginas = $totalTransacoes ? ceil($totalTransacoes / $itensPorPagina) : 1;

        $saldosBancos = $this->financeiroModel->getSaldosBancos();

        $data = [
            'pageTitle' => 'Movimentações de Caixa',
            'fluxoCaixa' => $fluxoCaixa,
            'paginaAtual' => $paginaAtual,
            'totalPaginas' => $totalPaginas,
            'filtros' => $filtros,
            'saldosBancos' => $saldosBancos,
            'bancos' => $bancos,
        ];

        $this->renderView('financeiro/movimentacoes', $data);
    }

    /**
     * Gera os dados estatísticos para a análise de comportamento de pagamento dos clientes.
     */
    private function analiseClientesPagamentos(?string $mesReferencia = null): array
    {
        try {
            $db = \App\Core\Connection::getInstance();
            
            // Define o contexto temporal da análise
            $mesRef = $mesReferencia ?: date('Y-m');
            $anoRef = date('Y', strtotime($mesRef . '-01'));
            $dataLimite = $anoRef . '-12-31'; // Analisa o ano completo de referência
            $hoje = date('Y-m-d');

            // 1. Total de clientes analisados (Performance ACUMULADA até o mês selecionado)
            // Considera quem teve qualquer receita vencendo ou paga até o limite do período
            $stmtTotal = $db->prepare("SELECT COUNT(DISTINCT cliente_id) FROM transacoes WHERE tipo = 'R' AND cliente_id > 0 AND status != 'Cancelado' AND (documento_vinculado IS NULL OR documento_vinculado NOT LIKE 'transfer_%') AND (vencimento <= :limite_v OR data_pagamento <= :limite_p)");
            $stmtTotal->execute([':limite_v' => $dataLimite, ':limite_p' => $dataLimite]);
            $totalClientes = $stmtTotal->fetchColumn();
            
            // 1.1 Clientes que pagam SEMPRE exatamente no dia (nem antes, nem depois)
            $sqlSempreEmDia = "
                SELECT COUNT(*) FROM (
                    SELECT cliente_id
                    FROM transacoes
                    WHERE tipo = 'R' AND status = 'Pago' AND cliente_id > 0 
                    AND (documento_vinculado IS NULL OR documento_vinculado NOT LIKE 'transfer_%')
                    AND data_pagamento <= :limite AND status != 'Cancelado'
                    GROUP BY cliente_id
                    HAVING SUM(CASE WHEN data_pagamento != vencimento THEN 1 ELSE 0 END) = 0
                ) as t";
            $stmtSempre = $db->prepare($sqlSempreEmDia);
            $stmtSempre->execute([':limite' => $dataLimite]);
            $clientesSempreEmDia = $stmtSempre->fetchColumn();

            // 2. Estatísticas de Atraso e Adiantamento
            // Unifica dívidas abertas com atrasos históricos até a data limite.
            $stmtStats = $db->prepare("
                SELECT 
                    COUNT(CASE WHEN (status = 'Pago' AND data_pagamento > vencimento) OR (status IN ('Pendente', 'Atrasado') AND vencimento < :hoje1) THEN 1 END) as qtd_atraso,
                    COUNT(CASE WHEN data_pagamento < vencimento THEN 1 END) as qtd_adiantado,
                    SUM(CASE WHEN (status = 'Pago' AND data_pagamento > vencimento) OR (status IN ('Pendente', 'Atrasado') AND vencimento < :hoje2) THEN valor ELSE 0 END) as valor_atraso,
                    SUM(CASE WHEN data_pagamento < vencimento THEN valor ELSE 0 END) as valor_adiantado,
                    AVG(CASE 
                        WHEN status = 'Pago' AND data_pagamento > vencimento THEN DATEDIFF(data_pagamento, vencimento) 
                        WHEN status IN ('Pendente', 'Atrasado') AND vencimento < :hoje_v THEN DATEDIFF(:hoje_v2, vencimento)
                    END) as media_dias_atraso,
                    AVG(CASE WHEN data_pagamento < vencimento THEN DATEDIFF(vencimento, data_pagamento) END) as media_dias_adiantado
                FROM transacoes 
                WHERE tipo = 'R' AND status != 'Cancelado' AND cliente_id > 0
                AND (documento_vinculado IS NULL OR documento_vinculado NOT LIKE 'transfer_%')
                AND (vencimento <= :limite OR data_pagamento <= :limite2)
            ");
            $stmtStats->execute([':limite' => $dataLimite, ':limite2' => $dataLimite, ':hoje1' => $hoje, ':hoje2' => $hoje, ':hoje_v' => $hoje, ':hoje_v2' => $hoje]);
            $stats = $stmtStats->fetch(\PDO::FETCH_ASSOC) ?: [];

            // 3. Principais devedores (Histórico acumulado de atrasos no ano de referência)
            $stmtDev = $db->prepare("
                SELECT COALESCE(cli.nome, 'Cliente não identificado') as nome, 
                       cli.id as cliente_id,
                       SUM(CASE WHEN (m.status = 'Pago' AND m.data_pagamento > m.vencimento) OR (m.status IN ('Pendente', 'Atrasado') AND m.vencimento < :hoje) THEN m.valor ELSE 0 END) as valor_atraso,
                       SUM(CASE WHEN m.status = 'Pago' AND m.data_pagamento < m.vencimento THEN m.valor ELSE 0 END) as valor_adiantamento,
                       ROUND(AVG(CASE 
                           WHEN m.status = 'Pago' AND m.data_pagamento > m.vencimento THEN DATEDIFF(m.data_pagamento, m.vencimento) 
                           WHEN m.status IN ('Pendente', 'Atrasado') AND m.vencimento < :hoje2 THEN DATEDIFF(:hoje3, m.vencimento)
                       END)) as dias_atraso
                FROM transacoes m
                LEFT JOIN clientes cli ON m.cliente_id = cli.id
                WHERE m.tipo = 'R' AND m.status != 'Cancelado' AND m.cliente_id > 0
                AND (m.documento_vinculado IS NULL OR m.documento_vinculado NOT LIKE 'transfer_%')
                AND (m.vencimento <= :limite OR m.data_pagamento <= :limite2)
                GROUP BY cli.id
                HAVING valor_atraso > 0
                ORDER BY valor_atraso DESC
                LIMIT 5
            ");
            $stmtDev->execute([':hoje' => $hoje, ':hoje2' => $hoje, ':hoje3' => $hoje, ':limite' => $dataLimite, ':limite2' => $dataLimite]);
            $topDevedores = $stmtDev->fetchAll(\PDO::FETCH_ASSOC) ?: [];

            // 4. Principais adiantadores (histórico de antecipação)
            $stmtAdi = $db->prepare("
                SELECT COALESCE(cli.nome, 'Cliente não identificado') as nome, 
                       cli.id as cliente_id,
                       SUM(m.valor) as valor_adiantamento,
                       ROUND(AVG(DATEDIFF(m.vencimento, m.data_pagamento))) as dias_adiantamento
                FROM transacoes m
                LEFT JOIN clientes cli ON m.cliente_id = cli.id
                WHERE m.tipo = 'R' 
                AND m.status = 'Pago'
                AND m.data_pagamento IS NOT NULL
                AND (m.documento_vinculado IS NULL OR m.documento_vinculado NOT LIKE 'transfer_%')
                AND m.data_pagamento < m.vencimento 
                AND (m.vencimento <= :limite OR m.data_pagamento <= :limite2)
                GROUP BY cli.id
                ORDER BY valor_adiantamento DESC
                LIMIT 5");
            $stmtAdi->execute([':limite' => $dataLimite, ':limite2' => $dataLimite]);
            $topAdiantadores = $stmtAdi->fetchAll(\PDO::FETCH_ASSOC) ?: [];

            return [
                'total_clientes' => (int)$totalClientes,
                'inadimplencia_qtd' => (int)($stats['qtd_atraso'] ?? 0),
                'antecipacao_qtd' => (int)($stats['qtd_adiantado'] ?? 0),
                'pontualidade_qtd' => (int)$clientesSempreEmDia,
                'inadimplencia_pct' => $totalClientes > 0 ? round(($stats['qtd_atraso'] / $totalClientes) * 100, 1) : 0,
                'antecipacao_pct' => $totalClientes > 0 ? round(($stats['qtd_adiantado'] / $totalClientes) * 100, 1) : 0,
                'pontualidade_pct' => $totalClientes > 0 ? round(($clientesSempreEmDia / $totalClientes) * 100, 1) : 0,
                'impacto_bruto_atraso' => (float)($stats['valor_atraso'] ?? 0),
                'impacto_adiantamento' => (float)($stats['valor_adiantado'] ?? 0),
                'ticket_medio_atraso' => ($stats['qtd_atraso'] > 0) ? ($stats['valor_atraso'] / $stats['qtd_atraso']) : 0,
                'ticket_medio_adiantado' => ($stats['qtd_adiantado'] > 0) ? ($stats['valor_adiantado'] / $stats['qtd_adiantado']) : 0,
                'atraso_medio_dias' => round($stats['media_dias_atraso'] ?? 0, 1),
                'antecipacao_media_dias' => round($stats['media_dias_adiantado'] ?? 0, 1),
                'maiores_devedores' => $topDevedores,
                'principais_adiantadores' => $topAdiantadores
            ];
        } catch (\Exception $e) {
            error_log("Erro na análise de clientes: " . $e->getMessage());
            return [];
        }
    }
    /**
     * Gera o relatório financeiro em formato PDF.
     */
    public function exportarRelatorioPdf()
    {
        // Coleta os filtros da URL
        $filtros = [
            'tipo_relatorio' => filter_input(INPUT_GET, 'tipo_relatorio', FILTER_SANITIZE_SPECIAL_CHARS) ?: 'geral',
            'banco_id' => filter_input(INPUT_GET, 'banco_id', FILTER_VALIDATE_INT),
            'classificacao_id' => filter_input(INPUT_GET, 'classificacao_id', FILTER_VALIDATE_INT),
            'centro_custo_id' => filter_input(INPUT_GET, 'centro_custo_id', FILTER_VALIDATE_INT),
            'periodo' => filter_input(INPUT_GET, 'periodo', FILTER_SANITIZE_SPECIAL_CHARS) ?: 'recente',
            'status' => filter_input(INPUT_GET, 'status', FILTER_SANITIZE_SPECIAL_CHARS),
            'data_unica' => filter_input(INPUT_GET, 'data_unica'),
            'mes_ano' => filter_input(INPUT_GET, 'mes_ano') ?: (filter_input(INPUT_GET, 'periodo') === 'mes' ? date('Y-m') : null),
            'data_inicio' => filter_input(INPUT_GET, 'data_inicio'),
            'data_fim' => filter_input(INPUT_GET, 'data_fim'),
        ];

        // Ajusta metadados do tipo para o PDF
        if (!empty($filtros['banco_id'])) {
            $filtros['tipo_relatorio'] = 'banco';
        }

        $transacoes = $this->financeiroModel->getTransacoesParaRelatorio($filtros);
        $bancos = $this->financeiroModel->getBancos();
        $bancoSelecionado = null;
        if (!empty($filtros['banco_id'])) {
            foreach ($bancos as $banco) {
                if ($banco['id'] == $filtros['banco_id']) {
                    $bancoSelecionado = $banco['nome'];
                    break;
                }
            }
        }

        $categoriaSelecionada = null;
        if (!empty($filtros['classificacao_id'])) {
            $cat = $this->financeiroModel->getClassificacaoPorId($filtros['classificacao_id']);
            $categoriaSelecionada = $cat['nome'] ?? null;
        }

        $centroCustoSelecionado = null;
        if (!empty($filtros['centro_custo_id'])) {
            $cc = $this->financeiroModel->getCentroCustoPorId($filtros['centro_custo_id']);
            $centroCustoSelecionado = $cc['nome'] ?? null;
        }

        // Busca dados da empresa
        $infoEmpresa = $this->empresaModel->getDadosEmpresa();

        $data = [
            'pageTitle' => 'Extrato Financeiro',
            'transacoes' => $transacoes,
            'filtros' => $filtros,
            'bancoSelecionado' => $bancoSelecionado,
            'categoriaSelecionada' => $categoriaSelecionada,
            'centroCustoSelecionado' => $centroCustoSelecionado,
            'dataGeracao' => date('d/m/Y H:i:s'),
            'empresa' => $infoEmpresa,
        ];

        // 1. Captura o HTML da view do relatório em uma variável
        ob_start();
        $this->renderPartial('financeiro/relatorio_pdf', $data);
        $html = ob_get_clean();

        // 2. Configura e instancia o Dompdf
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', '300');
        ini_set('pcre.backtrack_limit', '5000000');
        $options = new \Dompdf\Options();
        // Habilita o carregamento de imagens e CSS remotos, se necessário.
        // É uma boa prática, embora nosso CSS seja inline.
        $options->set('isRemoteEnabled', true);
        $dompdf = new \Dompdf\Dompdf($options);

        // 3. Carrega o HTML no Dompdf
        $dompdf->loadHtml($html);

        // 4. (Opcional) Define o tamanho e a orientação do papel
        $dompdf->setPaper('A4', 'portrait'); // 'portrait' (retrato) ou 'landscape' (paisagem)

        // 5. Renderiza o HTML como PDF
        $dompdf->render();

        // 6. Envia o PDF gerado para o navegador para download
        // O 'Attachment' => false faz com que o PDF seja aberto no navegador.
        $dompdf->stream("extrato_financeiro_" . date('Ymd_His') . ".pdf", ["Attachment" => false]);
        exit(); // Garante que o script pare após enviar o PDF
    }

    /**
     * Exibe o formulário para uma nova transação (despesa ou receita).
     */
    public function novo()
    {
        // Verifica se um tipo foi passado via GET (ex: /financeiro/novo?tipo=P)
        $tipoPreSelecionado = filter_input(INPUT_GET, 'tipo', FILTER_SANITIZE_SPECIAL_CHARS);

        $bancos = $this->financeiroModel->getBancos();
        $classificacoes = $this->financeiroModel->getClassificacoes(); // Busca todas por padrão
        $centrosCusto = $this->financeiroModel->getCentrosCusto(); // Busca os centros de custo
        $clientes = $this->clientesModel->getAllClientes(); // Busca clientes
        $fornecedores = $this->fornecedoresModel->getAllFornecedores(); // Busca fornecedores
        $data = [
            'pageTitle' => 'Nova Movimentação de Caixa',
            'transacao' => null, // Formulário vazio
            'bancos' => $bancos,
            'classificacoes' => $classificacoes,
            'centrosCusto' => $centrosCusto, // Passa para a view
            'clientes' => $clientes,
            'fornecedores' => $fornecedores,
        ];
        $this->renderView('financeiro/form', $data);
    }

    /**
     * Exibe o formulário para editar uma movimentação existente.
     * @param int $id O ID da transação.
     */
    public function editar($id)
    {
        $transacao = $this->financeiroModel->getTransacaoPorId($id);

        if (!$transacao) {
            $this->setFlashMessage('error', 'Transação não encontrada.');
            header('Location: ' . BASE_URL . '/financeiro');
            exit();
        }

        // **INÍCIO DA NOVA VALIDAÇÃO**
        // Verifica se é uma transferência. A edição de transferências é bloqueada
        // para manter a integridade dos dados, pois envolve a alteração de duas
        // transações vinculadas (entrada e saída). O fluxo recomendado é excluir
        // a transferência original e criar uma nova.
        // CORREÇÃO: Utiliza a função helper 'get_transfer_type' para uma verificação
        // consistente e centralizada, igual a que é usada nas views.
        $isTransfer = get_transfer_type($transacao);

        if ($isTransfer) {
            $this->setFlashMessage(
                'info',
                'Edição de transferências não é permitida. Para corrigir, por favor, exclua a transferência original (isso removerá tanto a saída quanto a entrada) e crie uma nova.'
            );
            // Redireciona para a página de onde o usuário veio.
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/financeiro/movimentacoes'));
            exit();
        }
        // **FIM DA NOVA VALIDAÇÃO**

        $bancos = $this->financeiroModel->getBancos();
        $classificacoes = $this->financeiroModel->getClassificacoes($transacao['tipo'] ?? null);
        $centrosCusto = $this->financeiroModel->getCentrosCusto();
        $clientes = $this->clientesModel->getAllClientes();
        $fornecedores = $this->fornecedoresModel->getAllFornecedores();

        $data = [
            'pageTitle' => 'Editar Movimentação',
            'transacao' => $transacao,
            'bancos' => $bancos,
            'classificacoes' => $classificacoes,
            'centrosCusto' => $centrosCusto,
            'clientes' => $clientes,
            'fornecedores' => $fornecedores,
        ];

        $this->renderView('financeiro/form', $data);
    }

    /**
     * Exibe o formulário para editar uma transação existente.
     * @param int $id O ID da transação.
     */
    public function detalhe(int $id)
    {
        $transacao = $this->financeiroModel->getTransacaoPorId($id);
        $bancos = $this->financeiroModel->getBancos();
        $classificacoes = $this->financeiroModel->getClassificacoes($transacao['tipo'] ?? null);
        $centrosCusto = $this->financeiroModel->getCentrosCusto(); // Busca os centros de custo
        $clientes = $this->clientesModel->getAllClientes();
        $fornecedores = $this->fornecedoresModel->getAllFornecedores();

        if (!$transacao) {
            $this->setFlashMessage('error', 'Transação não encontrada.');
            header('Location: ' . BASE_URL . '/financeiro');
            exit();
        }

        // Verifica se é uma transferência usando a função helper para consistência.
        $isTransfer = get_transfer_type($transacao);
        if ($isTransfer) {
            $this->setFlashMessage('info', 'Transferências não podem ser editadas. Para corrigir, por favor, exclua a transferência original e crie uma nova.');
            // Redireciona para a página de onde o usuário veio ou para o dashboard financeiro
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/financeiro'));
            exit();
        }

        // Determina a URL de "Voltar" com base no tipo da transação
        if ($transacao['tipo'] === 'P') {
            $urlVoltar = BASE_URL . '/financeiro/pagar';
        } else {
            $urlVoltar = BASE_URL . '/financeiro/receber';
        }

        $data = [
            'pageTitle' => 'Detalhe da Transação',
            'transacao' => $transacao,
            'bancos' => $bancos,
            'classificacoes' => $classificacoes,
            'centrosCusto' => $centrosCusto, // Passa para a view
            'clientes' => $clientes,
            'fornecedores' => $fornecedores,
            'urlVoltar' => $urlVoltar,
        ];
        $this->renderView('financeiro/form', $data); // Reutiliza a view do formulário para edição
    }

    /**
     * Processa o formulário de cadastro/edição e salva a transação.
     */
    /**
     * Processa o formulário de cadastro/edição e salva a transação.
     */
    public function salvar()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validação de CSRF
            if (!isset($_POST['csrf_token']) || !$this->validateCsrfToken($_POST['csrf_token'])) {
                $this->setFlashMessage('error', 'Token de segurança inválido. Tente novamente.');
                header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/financeiro'));
                exit();
            }

            // TRAVA DE SEGURANÇA: Evita processamento duplo se o usuário clicar rápido ou o servidor estiver lento.
            // Geramos um token baseado nos dados essenciais da transação para identificar requisições idênticas.
            $coreData = [
                'tipo' => $_POST['tipo'] ?? '',
                'descricao' => $_POST['descricao'] ?? '',
                'valor' => $_POST['valor'] ?? '',
                'vencimento' => $_POST['vencimento'] ?? '',
                'repetir' => $_POST['repetir'] ?? '',
                'tipo_repeticao' => $_POST['tipo_repeticao'] ?? '',
                'parcelas' => $_POST['parcelas'] ?? '',
                'fornecedor_id' => $_POST['fornecedor_id'] ?? '',
                'cliente_id' => $_POST['cliente_id'] ?? '',
                'centro_custo_id' => $_POST['centro_custo_id'] ?? '',
                'classificacao_id' => $_POST['classificacao_id'] ?? '',
            ];
            $requestToken = md5(json_encode($coreData) . $this->session->get('user_id'));
            $lastToken = $this->session->get('last_submit_token');
            $lastTime = $this->session->get('last_submit_time');

            if ($lastToken === $requestToken && (time() - $lastTime) < 30) {
                error_log("DEBUG FINANCEIRO: Requisição duplicada bloqueada. Token: $requestToken");
                $this->setFlashMessage('info', 'Esta operação já está sendo processada. Por favor, aguarde.');
                header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/financeiro'));
                exit();
            }
            error_log("DEBUG FINANCEIRO: Requisição processada. Salvando token: $requestToken");
            $this->session->set('last_submit_token', $requestToken);
            $this->session->set('last_submit_time', time());

            // Tratamento especial para o campo 'valor' que vem formatado
            $valorFormatado = $_POST['valor'] ?? '0';
            $valorFormatado = trim($valorFormatado);
            // Converte valores monetários em formatos pt-BR (ex: 1.234,56) ou en (ex: 1234.56) para float
            $valorFloat = (function ($str) {
                $str = trim($str);
                // Remove caracteres inválidos, preservando dígitos, ponto, vírgula e sinal negativo
                $str = preg_replace('/[^\d\-,.]/', '', $str);
                if ($str === '') return 0.0;
                if (strpos($str, '.') !== false && strpos($str, ',') !== false) {
                    // Formato pt-BR: 1.234,56 -> 1234.56
                    $str = str_replace('.', '', $str);
                    $str = str_replace(',', '.', $str);
                } elseif (strpos($str, ',') !== false) {
                    // 1234,56 -> 1234.56
                    $str = str_replace(',', '.', $str);
                } else {
                    // Formato en: 1234.56 ou com milhares com múltiplos pontos => garante somente o último como decimal
                    if (substr_count($str, '.') > 1) {
                        $parts = explode('.', $str);
                        $decimal = array_pop($parts);
                        $str = implode('', $parts) . '.' . $decimal;
                    }
                }
                return (float) $str;
            })($valorFormatado);

            // Coleta e sanitiza os dados do formulário (evitando filter_input para ID devido ao multipart/form-data)
            $id = (isset($_POST['id']) && $_POST['id'] !== '') ? (int)$_POST['id'] : null;
            
            $existingTransacao = null;
            if ($id) {
                $existingTransacao = $this->financeiroModel->getTransacaoPorId($id);
            }

            $dados = [
                'id' => $id,
                'tipo' => filter_input(INPUT_POST, 'tipo', FILTER_SANITIZE_SPECIAL_CHARS),
                'descricao' => filter_input(INPUT_POST, 'descricao', FILTER_SANITIZE_SPECIAL_CHARS),
                'valor' => $valorFloat,
                'vencimento' => filter_input(INPUT_POST, 'vencimento'),
                'data_pagamento' => filter_input(INPUT_POST, 'data_pagamento') ?: null, // Novo campo
                'dataEmissao' => filter_input(INPUT_POST, 'dataEmissao') ?: null,
                'status' => filter_input(INPUT_POST, 'status', FILTER_SANITIZE_SPECIAL_CHARS),
                'documentoVinculado' => filter_input(INPUT_POST, 'documentoVinculado', FILTER_SANITIZE_SPECIAL_CHARS) ?: null,
                'observacoes' => filter_input(INPUT_POST, 'observacoes', FILTER_SANITIZE_SPECIAL_CHARS) ?: null,
                'banco_id' => (isset($_POST['banco_id']) && $_POST['banco_id'] !== '') ? (int)$_POST['banco_id'] : null,
                'classificacao_id' => (isset($_POST['classificacao_id']) && $_POST['classificacao_id'] !== '') ? (int)$_POST['classificacao_id'] : null,
                'prestacao_categoria_id' => (isset($_POST['prestacao_categoria_id']) && $_POST['prestacao_categoria_id'] !== '') ? (int)$_POST['prestacao_categoria_id'] : null,
                'centro_custo_id' => (isset($_POST['centro_custo_id']) && $_POST['centro_custo_id'] !== '') ? (int)$_POST['centro_custo_id'] : null,
                'cliente_id' => (isset($_POST['cliente_id']) && $_POST['cliente_id'] !== '') ? (int)$_POST['cliente_id'] : null,
                'fornecedor_id' => (isset($_POST['fornecedor_id']) && $_POST['fornecedor_id'] !== '') ? (int)$_POST['fornecedor_id'] : null,
                'juros' => (function ($str) {
                    return (float)str_replace(['.', ','], ['', '.'], $str);
                })($_POST['juros'] ?? '0'),
                'desconto' => (function ($str) {
                    return (float)str_replace(['.', ','], ['', '.'], $str);
                })($_POST['desconto'] ?? '0'),
                'iss_percentual' => (function ($str) {
                    // Garante que o ISS só seja calculado para Receitas
                    if (($_POST['tipo'] ?? '') !== 'R') {
                        return 0.0;
                    }
                    return (float)str_replace(['.', ','], ['', '.'], $str);
                })($_POST['iss_percentual'] ?? '0'),
                'forma_pagamento' => filter_input(INPUT_POST, 'forma_pagamento', FILTER_SANITIZE_SPECIAL_CHARS) ?: null,
                'contrato_parcela_id' => filter_input(INPUT_POST, 'contrato_parcela_id', FILTER_VALIDATE_INT) ?: null,

                'repetir' => filter_has_var(INPUT_POST, 'repetir') ? true : false,
                'tipo_repeticao' => filter_input(INPUT_POST, 'tipo_repeticao', FILTER_SANITIZE_SPECIAL_CHARS) ?: 'parcelamento',
                'parcelas' => filter_input(INPUT_POST, 'parcelas', FILTER_VALIDATE_INT) ?: 1,
            ];

            // --- INÍCIO: Lógica de Upload de Anexo ---
            $anexoPath = null;
            if (isset($_FILES['anexo']) && $_FILES['anexo']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = ROOT_PATH . '/storage/financeiro_anexos/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0775, true);
                }
                $fileInfo = pathinfo($_FILES['anexo']['name']);
                $extension = strtolower($fileInfo['extension']);
                $safeFilename = preg_replace('/[^A-Za-z0-9_\-]/', '_', $fileInfo['filename']);
                $prefix = 'anexo_' . strtolower($dados['tipo'] ?? 'geral');
                $newFilename = $prefix . '_' . time() . '.' . $extension;
                $destination = $uploadDir . $newFilename;

                if (move_uploaded_file($_FILES['anexo']['tmp_name'], $destination)) {
                    $anexoPath = $newFilename;

                    // Se um novo anexo foi enviado, ele tem prioridade e sobrescreve o campo 'documentoVinculado'
                    $dados['documentoVinculado'] = $anexoPath;

                    // Se for uma edição, apaga o anexo antigo se existir e for diferente do novo
                    if ($dados['id']) {
                        $transacaoExistente = $this->financeiroModel->getTransacaoPorId($dados['id']);
                        if (!empty($transacaoExistente['documentoVinculado']) && $transacaoExistente['documentoVinculado'] !== $anexoPath) {
                            $oldFilePath = ROOT_PATH . '/storage/financeiro_anexos/' . $transacaoExistente['documentoVinculado'];
                            if (file_exists($oldFilePath)) {
                                @unlink($oldFilePath);
                            }
                        }
                    }
                } else {
                    $this->setFlashMessage('error', 'Erro ao salvar o arquivo de anexo.');
                    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/financeiro'));
                    exit();
                }
            }
            // --- FIM: Lógica de Upload de Anexo ---

            // Validação básica
            if ($dados['tipo'] && $dados['descricao'] && $dados['valor'] > 0 && $dados['vencimento'] && $dados['status']) {

                // LOG DE DEPURAÇÃO: Verifique o arquivo debug.log na raiz do projeto ou o log do PHP
                error_log("DEBUG FINANCEIRO: Tentando salvar transação. Cliente ID: " . ($dados['cliente_id'] ?? 'NULO'));

                // VERIFICAÇÃO DE DUPLICIDADE
                // Se for um novo registro (sem ID), verifica se já existe um igual criado recentemente
                if (empty($id) && $this->financeiroModel->verificarDuplicidade(
                    $dados,
                    $dados['repetir'], // Passa o flag 'repetir'
                    $dados['tipo_repeticao'], // Passa o tipo de repetição
                    $dados['parcelas'] // Passa o número de parcelas
                )) {
                    $this->setFlashMessage('error', 'Operação bloqueada: Uma movimentação idêntica foi registrada recentemente.');

                    $redirectUrl = BASE_URL . '/financeiro';
                    if ($dados['tipo'] === 'P') $redirectUrl = BASE_URL . '/financeiro/pagar';
                    if ($dados['tipo'] === 'R') $redirectUrl = BASE_URL . '/financeiro/receber';
                    header('Location: ' . $redirectUrl);
                    exit();
                }

                // Verifica se é uma repetição/parcelamento (apenas para novos registros)
                if (empty($id) && $dados['repetir'] && $dados['parcelas'] > 1) {
                    try {
                        error_log("DEBUG FINANCEIRO: Iniciando processamento de parcelamento/recorrência. Parcelas: {$dados['parcelas']}");
                        $this->financeiroModel->iniciarTransacao();

                        // Define o valor de cada lançamento com base no tipo de repetição
                        if ($dados['tipo_repeticao'] === 'recorrencia') {
                            $valorParcela = round($dados['valor'], 2); // Valor integral repetido
                        } else {
                            $valorParcela = round($dados['valor'] / $dados['parcelas'], 2); // Valor dividido (Parcelamento)
                        }

                        $vencimentoBase = new \DateTime($dados['vencimento']);
                        $descricaoOriginal = trim($dados['descricao']);

                        for ($i = 1; $i <= $dados['parcelas']; $i++) {
                            // Criamos uma cópia dos dados para cada parcela para evitar contaminação de memória no loop
                            $dadosParcela = $dados;
                            $dadosParcela['valor'] = $valorParcela;
                            $dadosParcela['vencimento'] = $vencimentoBase->format('Y-m-d');

                            // Ajusta a descrição para indicar se é parcela ou recorrência
                            $sufixo = ($dados['tipo_repeticao'] === 'recorrencia') ? " (Recorrência $i/{$dados['parcelas']})" : " ($i/{$dados['parcelas']})";
                            $dadosParcela['descricao'] = $descricaoOriginal . $sufixo;
                            
                            error_log("DEBUG FINANCEIRO: Salvando parcela $i. Desc: {$dadosParcela['descricao']}, Valor: {$dadosParcela['valor']}, Venc: {$dadosParcela['vencimento']}");

                            $savedId = $this->financeiroModel->salvarTransacao($dadosParcela);
                            if ($savedId === false) {
                                throw new \Exception("Falha ao salvar a parcela $i.");
                            }

                            // Recupera o ID real para sincronização (evita erro com retornos booleanos)
                            error_log("DEBUG FINANCEIRO: Parcela $i salva com ID: $savedId");
                            $transacaoIdReal = is_numeric($savedId) ? (int)$savedId : ($id ?? 0);
                            if ($transacaoIdReal && $dadosParcela['status'] === 'Pago') {
                                $this->sincronizarDespesaProjeto($transacaoIdReal);
                            }

                            // Avança 1 mês para a próxima parcela
                            $vencimentoBase->modify('+1 month');
                        }

                        $this->financeiroModel->confirmarTransacao();
                        $this->setFlashMessage('success', "Transação parcelada em {$dados['parcelas']}x salva com sucesso!");
                        error_log("DEBUG FINANCEIRO: Parcelamento/recorrência concluído com sucesso.");
                    } catch (\Exception $e) {
                        $this->financeiroModel->desfazerTransacao();
                        $this->setFlashMessage('error', 'Erro ao processar parcelamento: ' . $e->getMessage());
                        error_log("DEBUG FINANCEIRO: Erro no parcelamento: " . $e->getMessage());
                    }
                } else {
                    // Salvamento Único (Padrão)
                    $resSalvar = $this->financeiroModel->salvarTransacao($dados);
                    if ($resSalvar) {
                        $transacaoId = is_numeric($resSalvar) ? (int)$resSalvar : ($id ?? 0);

                        // Lógica de atualização em lote para recorrências (apenas na edição)
                        if ($id && $existingTransacao) {
                            // Determine if centro_custo_id needs to be updated for future parcels
                            // It should be updated if the value changed OR if the explicit checkbox is checked.
                            $shouldUpdateFutureCc = ($dados['centro_custo_id'] !== $existingTransacao['centro_custo_id']) || 
                                (isset($_POST['atualizar_futuras']) && $_POST['atualizar_futuras'] === 'on');
                            
                            // Determine if valor needs to be updated for future parcels
                            // It should be updated if the value changed OR if the explicit checkbox is checked.
                            $shouldUpdateFutureValue = ($dados['valor'] !== $existingTransacao['valor']) ||
                                (isset($_POST['atualizar_valor_futuras']) && $_POST['atualizar_valor_futuras'] === 'on');

                            if ($shouldUpdateFutureCc || $shouldUpdateFutureValue) {
                            $this->financeiroModel->atualizarParcelasFuturas(
                                $id,
                                $shouldUpdateFutureCc ? $dados['centro_custo_id'] : null,
                                $dados['descricao'],
                            );
                            }
                        }

                        if ($transacaoId) {
                            $this->sincronizarDespesaProjeto($transacaoId);
                        }

                        $message = $id ? 'Transação atualizada com sucesso!' : 'Transação cadastrada com sucesso!';
                        $this->setFlashMessage('success', $message);
                    } else {
                        $erro = $this->financeiroModel->getLastError() ?? 'Erro desconhecido.';
                        $this->setFlashMessage('error', 'Ocorreu um erro ao salvar a transação: ' . $erro);
                    }
                }
            } else {
                $this->setFlashMessage('error', 'Dados inválidos. Por favor, preencha todos os campos obrigatórios.');
                error_log("Dados inválidos recebidos no formulário financeiro.");
            }
        }

        // Redireciona para a página correta (pagar ou receber) após salvar
        $redirectUrl = BASE_URL . '/financeiro';
        if (isset($dados['tipo'])) {
            if ($dados['tipo'] === 'P') $redirectUrl = BASE_URL . '/financeiro/pagar';
            if ($dados['tipo'] === 'R') $redirectUrl = BASE_URL . '/financeiro/receber';
        }

        header('Location: ' . $redirectUrl);
        exit();
    }


    /**
     * Exclui uma transação.
     * @param int $id O ID da transação a ser excluída.
     */
    public function excluir($id)
    {
        // Garante que a requisição seja do tipo POST para segurança
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->setFlashMessage('error', 'Operação inválida.');
            header('Location: ' . $_SERVER['HTTP_REFERER'] ?? BASE_URL . '/financeiro');
            exit();
        }

        $id = (int)$id;
        // Fallback para extrair ID da URL se o argumento vier zerado
        if ($id <= 0) {
            $uriSegments = explode('/', trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/'));
            $lastSegment = end($uriSegments);
            if (is_numeric($lastSegment)) {
                $id = (int)$lastSegment;
            }
        }

        // Validação básica do ID
        if ($id <= 0) {
            $this->setFlashMessage('error', 'ID de transação inválido.');
            header('Location: ' . $_SERVER['HTTP_REFERER'] ?? BASE_URL . '/financeiro');
            exit();
        }

        if ($this->financeiroModel->excluirTransacao($id)) {
            // Se a transação estava vinculada ao orçamento de um projeto, remove o item correspondente
            $this->projetosModel->removerDespesaDeTransacao($id);
            
            $this->setFlashMessage('success', 'Transação excluída com sucesso!');
        } else {
            $this->setFlashMessage('error', 'Erro ao excluir a transação.');
        }

        // Redireciona de volta para a página anterior (pagar ou receber)
        header('Location: ' . $_SERVER['HTTP_REFERER'] ?? BASE_URL . '/financeiro');
        exit();
    }

    /**
     * Exclui múltiplas transações em massa.
     */
    public function excluirEmMassa()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->setFlashMessage('error', 'Operação inválida.');
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/financeiro'));
            exit();
        }

        $ids = $_POST['transacao_ids'] ?? [];

        if (empty($ids)) {
            $this->setFlashMessage('info', 'Nenhuma transação foi selecionada para exclusão.');
            header('Location: ' . $_SERVER['HTTP_REFERER'] ?? BASE_URL . '/financeiro');
            exit();
        }

        if ($this->financeiroModel->excluirTransacoes($ids)) {
            // Remove do orçamento dos projetos para cada transação excluída
            foreach ($ids as $id) {
                $this->projetosModel->removerDespesaDeTransacao((int)$id);
            }
            $this->setFlashMessage('success', count($ids) . ' transações foram excluídas com sucesso!');
        } else {
            $this->setFlashMessage('error', 'Erro ao excluir as transações selecionadas.');
        }

        header('Location: ' . $_SERVER['HTTP_REFERER'] ?? BASE_URL . '/financeiro');
        exit();
    }

    /**
     * Liquida múltiplas transações em massa (marca como Pago/Recebido).
     */
    public function liquidarEmMassa()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->setFlashMessage('error', 'Operação inválida.');
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/financeiro'));
            exit();
        }

        $ids = $_POST['transacao_ids'] ?? [];
        $dataPagamento = date('Y-m-d'); // Usa a data atual

        if (empty($ids)) {
            $this->setFlashMessage('info', 'Nenhuma transação foi selecionada.');
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/financeiro'));
            exit();
        }

        if ($this->financeiroModel->liquidarTransacoes($ids, $dataPagamento)) {
            // Sincroniza com o orçamento dos projetos vinculados
            foreach ($ids as $id) {
                $this->sincronizarDespesaProjeto((int)$id);
            }
            $this->setFlashMessage('success', count($ids) . ' transações foram liquidadas com sucesso!');
        } else {
            $this->setFlashMessage('error', 'Erro ao liquidar as transações selecionadas.');
        }

        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/financeiro'));
        exit();
    }

    /**
     * Bloqueia uma transação (define status como Cancelado).
     * @param int $id O ID da transação.
     */
    public function bloquear(int $id)
    {
        if ($id <= 0) {
            $this->setFlashMessage('error', 'ID de transação inválido.');
            header('Location: ' . $_SERVER['HTTP_REFERER'] ?? BASE_URL . '/financeiro/movimentacoes');
            exit();
        }

        if ($this->financeiroModel->bloquearTransacao($id)) {
            $this->setFlashMessage('success', 'Transação bloqueada com sucesso! Ela não será mais contabilizada.');
        } else {
            $this->setFlashMessage('error', 'Erro ao bloquear a transação.');
        }

        header('Location: ' . $_SERVER['HTTP_REFERER'] ?? BASE_URL . '/financeiro/movimentacoes');
        exit();
    }

    /**
     * Desbloqueia uma transação (define status como Pendente).
     * @param int $id O ID da transação.
     */
    public function desbloquear(int $id)
    {
        if ($id <= 0) {
            $this->setFlashMessage('error', 'ID de transação inválido.');
            header('Location: ' . $_SERVER['HTTP_REFERER'] ?? BASE_URL . '/financeiro/movimentacoes');
            exit();
        }

        if ($this->financeiroModel->desbloquearTransacao($id)) {
            $this->setFlashMessage('success', 'Transação desbloqueada com sucesso! Status alterado para Pendente.');
        } else {
            $this->setFlashMessage('error', 'Erro ao desbloquear a transação.');
        }

        header('Location: ' . $_SERVER['HTTP_REFERER'] ?? BASE_URL . '/financeiro/movimentacoes');
        exit();
    }

    /**
     * Endpoint para adicionar uma nova classificação via AJAX.
     */
    public function addClassificacao()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método inválido.']);
            exit();
        }

        $nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_SPECIAL_CHARS);
        $tipo = filter_input(INPUT_POST, 'tipo', FILTER_SANITIZE_SPECIAL_CHARS);

        if (empty($nome) || empty($tipo)) {
            echo json_encode(['success' => false, 'message' => 'Nome e tipo são obrigatórios.']);
            exit();
        }

        $newId = $this->financeiroModel->adicionarClassificacao($nome, $tipo);

        if ($newId) {
            echo json_encode([
                'success' => true,
                'message' => 'Classificação adicionada!',
                'data' => ['id' => $newId, 'nome' => $nome]
            ]);
        } else {
            $msg = $this->financeiroModel->getUltimoErro() ?? 'Erro ao adicionar classificação. Pode já existir.';
            echo json_encode(['success' => false, 'message' => $msg]);
        }
        exit();
    }

    /**
     * Endpoint para adicionar um novo centro de custo via AJAX.
     */
    public function addCentroCusto()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método inválido.']);
            exit();
        }

        $nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_SPECIAL_CHARS);

        if (empty($nome)) {
            echo json_encode(['success' => false, 'message' => 'O nome do centro de custo é obrigatório.']);
            exit();
        }

        $newId = $this->financeiroModel->adicionarCentroCusto($nome);

        if ($newId) {
            echo json_encode([
                'success' => true,
                'message' => 'Centro de Custo adicionado!',
                'data' => ['id' => $newId, 'nome' => $nome]
            ]);
        } else {
            $msg = $this->financeiroModel->getUltimoErro() ?? 'Erro ao adicionar centro de custo. Pode já existir.';
            echo json_encode(['success' => false, 'message' => $msg]);
        }
        exit();
    }
    /**
     * Exibe a lista de Contas a Pagar.
     */
    public function pagar()
    {
        $paginaAtual = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1;

        $filtros = [
            'status' => filter_input(INPUT_GET, 'status', FILTER_SANITIZE_SPECIAL_CHARS),
            'valor' => filter_input(INPUT_GET, 'valor_filtro'),
            'mes' => filter_input(INPUT_GET, 'mes_filtro'),
            'descricao' => filter_input(INPUT_GET, 'descricao_filtro', FILTER_SANITIZE_SPECIAL_CHARS),
        ];

        // Lógica inteligente para filtro de data:
        // Se estiver filtrando contas PAGAS, busca pela Data de Pagamento.
        // Caso contrário (Pendente/Atrasado/Todos), busca pela Data de Vencimento.
        $dataFiltro = filter_input(INPUT_GET, 'data_filtro');
        if ($dataFiltro) {
            if ($filtros['status'] === 'Pago') {
                $filtros['data_pagamento'] = $dataFiltro;
            } else {
                $filtros['data'] = $dataFiltro;
            }
        }

        if (!empty($filtros['valor'])) {
            $filtros['valor'] = (float)str_replace(['.', ','], ['', '.'], $filtros['valor']);
        }

        $itensPorPagina = 50; // Aumentado para facilitar a visualização
        $offset = ($paginaAtual - 1) * $itensPorPagina;

        // Ordenação: Vencidos no topo para listas pendentes/gerais
        $orderBy = 't.vencimento';
        $orderDir = 'DESC';

        if ($filtros['status'] === 'Pago') {
            $orderBy = 't.data_pagamento';
            $orderDir = 'DESC';
        } else {
            $orderBy = "CASE 
                WHEN t.status = 'Atrasado' OR (t.status = 'Pendente' AND t.vencimento < CURDATE()) THEN 0 
                WHEN t.status = 'Pendente' THEN 1 
                ELSE 2 
            END, t.vencimento";
            $orderDir = 'ASC';
        }

        $transacoes = $this->financeiroModel->getTransacoes('P', $orderBy, $orderDir, $itensPorPagina, $offset, $filtros);
        $totalTransacoes = $this->financeiroModel->getContagemTransacoes('P', $filtros);
        $totalPaginas = ceil($totalTransacoes / $itensPorPagina);

        // Calcula o total dos valores exibidos na página atual
        $totalPagina = array_sum(array_column($transacoes, 'valor'));

        $titulo = 'Pagamentos'; // Título padrão
        if ($filtros['status'] === 'Pendente' || $filtros['status'] === 'Atrasado') {
            $titulo = 'Contas a Pagar';
        }
        if ($filtros['status'] === 'Pago') $titulo = 'Contas Pagas';

        // Busca dados para o formulário do modal
        $bancos = $this->financeiroModel->getBancos();
        $classificacoes = $this->financeiroModel->getClassificacoes('P');
        $centrosCusto = $this->financeiroModel->getCentrosCusto();

        $data = [
            'pageTitle' => $titulo,
            'transacoes' => $transacoes,
            'tipo' => 'P',
            'paginaAtual' => $paginaAtual,
            'totalPaginas' => $totalPaginas,
            'bancos' => $bancos,
            'classificacoes' => $classificacoes,
            'centrosCusto' => $centrosCusto,
            'filtros' => $filtros,
            'totalPagina' => $totalPagina, // Passa o total para a view
        ];
        $this->renderView('financeiro/lista_transacoes', $data);
    }

    /**
     * Endpoint AJAX para adicionar uma nova categoria de prestação de contas.
     */
    public function addPrestacaoCategoria()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método inválido.']);
            exit();
        }

        $nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_SPECIAL_CHARS);

        if (empty($nome)) {
            echo json_encode(['success' => false, 'message' => 'O nome da categoria é obrigatório.']);
            exit();
        }

        $newId = $this->financeiroModel->addPrestacaoCategoria($nome);

        if ($newId) {
            echo json_encode(['success' => true, 'data' => ['id' => $newId, 'nome' => $nome]]);
        } else {
            $msg = $this->financeiroModel->getUltimoErro() ?? 'Erro ao adicionar categoria.';
            echo json_encode(['success' => false, 'message' => $msg]);
        }
        exit();
    }
    /**
     * Exibe a lista de Contas a Receber.
     */
    public function receber()
    {
        $paginaAtual = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1;

        $filtros = [
            'status' => filter_input(INPUT_GET, 'status', FILTER_SANITIZE_SPECIAL_CHARS),
            'valor' => filter_input(INPUT_GET, 'valor_filtro'),
            'mes' => filter_input(INPUT_GET, 'mes_filtro'),
            'descricao' => filter_input(INPUT_GET, 'descricao_filtro', FILTER_SANITIZE_SPECIAL_CHARS),
        ];

        $dataFiltro = filter_input(INPUT_GET, 'data_filtro');
        if ($dataFiltro) {
            if ($filtros['status'] === 'Pago') {
                $filtros['data_pagamento'] = $dataFiltro;
            } else {
                $filtros['data'] = $dataFiltro;
            }
        }

        if (!empty($filtros['valor'])) {
            $filtros['valor'] = (float)str_replace(['.', ','], ['', '.'], $filtros['valor']);
        }

        $itensPorPagina = 50; // Aumentado para facilitar a visualização
        $offset = ($paginaAtual - 1) * $itensPorPagina;

        // Ordenação: Vencidos no topo para listas pendentes/gerais
        $orderBy = 't.vencimento';
        $orderDir = 'DESC';

        if ($filtros['status'] === 'Pago') {
            $orderBy = 't.data_pagamento';
            $orderDir = 'DESC';
        } else {
            $orderBy = "CASE 
                WHEN t.status = 'Atrasado' OR (t.status = 'Pendente' AND t.vencimento < CURDATE()) THEN 0 
                WHEN t.status = 'Pendente' THEN 1 
                ELSE 2 
            END, t.vencimento";
            $orderDir = 'ASC';
        }

        $transacoes = $this->financeiroModel->getTransacoes('R', $orderBy, $orderDir, $itensPorPagina, $offset, $filtros);
        $totalTransacoes = $this->financeiroModel->getContagemTransacoes('R', $filtros);
        $totalPaginas = ceil($totalTransacoes / $itensPorPagina);

        // Calcula o total dos valores exibidos na página atual
        $totalPagina = array_sum(array_column($transacoes, 'valor'));

        $titulo = 'Recebimentos'; // Título padrão
        if ($filtros['status'] === 'Pendente' || $filtros['status'] === 'Atrasado') {
            $titulo = 'Contas a Receber';
        }
        if ($filtros['status'] === 'Pago') $titulo = 'Contas Recebidas';

        // Busca dados para o formulário do modal
        $bancos = $this->financeiroModel->getBancos();
        $classificacoes = $this->financeiroModel->getClassificacoes('R');
        $centrosCusto = $this->financeiroModel->getCentrosCusto();

        $data = [
            'pageTitle' => $titulo,
            'transacoes' => $transacoes,
            'tipo' => 'R',
            'paginaAtual' => $paginaAtual,
            'totalPaginas' => $totalPaginas,
            'bancos' => $bancos,
            'classificacoes' => $classificacoes,
            'centrosCusto' => $centrosCusto,
            'filtros' => $filtros,
            'totalPagina' => $totalPagina, // Passa o total para a view
        ];
        $this->renderView('financeiro/lista_transacoes', $data);
    }

    /**
     * Processa o formulário de transferência entre contas.
     */
    public function realizarTransferencia()
    {
        // Verifica se o método da requisição é POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            // Redireciona se não for POST
            header('Location: ' . BASE_URL . '/financeiro');
            exit;
        }

        // Coleta e sanitiza os dados do formulário
        $contaOrigemId = filter_input(INPUT_POST, 'conta_origem', FILTER_VALIDATE_INT);
        $contaDestinoId = filter_input(INPUT_POST, 'conta_destino', FILTER_VALIDATE_INT);
        // Normaliza valor monetário suportando formatos pt-BR e en
        $valorFormatado = $_POST['valor'] ?? '0';
        $valor = (function ($str) {
            $str = trim($str);
            $str = preg_replace('/[^\d\-,.]/', '', $str);
            if ($str === '') return 0.0;
            if (strpos($str, '.') !== false && strpos($str, ',') !== false) {
                // pt-BR 1.234,56 -> 1234.56
                $str = str_replace('.', '', $str);
                $str = str_replace(',', '.', $str);
            } elseif (strpos($str, ',') !== false) {
                // 1234,56 -> 1234.56
                $str = str_replace(',', '.', $str);
            } else {
                // en: 1234.56 ou 1.234.567 -> garante último ponto como decimal
                if (substr_count($str, '.') > 1) {
                    $parts = explode('.', $str);
                    $decimal = array_pop($parts);
                    $str = implode('', $parts) . '.' . $decimal;
                }
            }
            return (float) $str;
        })($valorFormatado);
        $data = filter_input(INPUT_POST, 'data_transferencia');

        // Validação básica dos dados
        if (!$contaOrigemId || !$contaDestinoId || !$valor || !$data || $valor <= 0) {
            $this->setFlashMessage('error', 'Dados inválidos. Por favor, preencha todos os campos corretamente.');
            header('Location: ' . BASE_URL . '/financeiro');
            exit;
        }

        if ($contaOrigemId === $contaDestinoId) {
            $this->setFlashMessage('error', 'A conta de origem não pode ser a mesma que a de destino.');
            header('Location: ' . BASE_URL . '/financeiro');
            exit;
        }

        try {
            // Tenta executar a transferência usando o modelo já instanciado
            // Assumindo que você adicionará o método 'criarTransferencia' ao seu FinancialModel
            $resultado = $this->financeiroModel->criarTransferencia($contaOrigemId, $contaDestinoId, $valor, $data, 'Transferência');

            if ($resultado) {
                $this->setFlashMessage('success', 'Transferência realizada com sucesso!');
            } else {
                $this->setFlashMessage('error', 'Não foi possível realizar a transferência.');
            }
        } catch (\Exception $e) {
            // Em caso de erro no modelo (ex: falha na transação)
            $this->setFlashMessage('error', 'Erro ao processar a transferência: ' . $e->getMessage());
        }

        // Redireciona de volta para a página financeira
        header('Location: ' . BASE_URL . '/financeiro');
        exit;
    }

    /**
     * Gera e baixa um modelo de planilha CSV para importação.
     */
    public function baixarModelo()
    {
        // Define headers para download de CSV
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=modelo_importacao_financeiro.csv');

        // Abre output stream
        $output = fopen('php://output', 'w');

        // Adiciona BOM para compatibilidade com Excel (UTF-8)
        fputs($output, "\xEF\xBB\xBF");

        // Cabeçalhos das colunas
        $headers = [
            'ID (Opcional p/ Atualizar)',
            'Tipo (R=Receita/P=Despesa)',
            'Descricao',
            'Valor (0,00)',
            'Vencimento (AAAA-MM-DD)',
            'Status (Pendente/Pago)',
            'Data Pagamento (AAAA-MM-DD)',
            'Banco (Nome)',
            'Categoria (Nome)',
            'Centro Custo (Nome)',
            'Observacoes'
        ];
        fputcsv($output, $headers, ';');

        // Linha de exemplo
        $example = [
            '', // ID Opcional
            'P',
            'Exemplo: Compra de Material',
            '150,00',
            date('Y-m-d'),
            'Pago',
            date('Y-m-d'),
            'Caixa Principal',
            'Despesas Administrativas',
            'Administrativo',
            'Importação via sistema'
        ];
        fputcsv($output, $example, ';');

        // Adiciona linhas de referência com as opções cadastradas para facilitar o copy-paste
        // Pula linhas para separar visualmente
        fputcsv($output, [], ';');
        fputcsv($output, [], ';');

        // Cabeçalho das opções (Deixamos a coluna Descrição [índice 1] vazia para o importador ignorar essas linhas)
        $headerRef = array_fill(0, 11, '');
        $headerRef[7] = '--- BANCOS CADASTRADOS ---';
        $headerRef[8] = '--- CATEGORIAS ---';
        $headerRef[9] = '--- CENTROS DE CUSTO ---';
        fputcsv($output, $headerRef, ';');

        $bancos = $this->financeiroModel->getBancos();
        $categorias = $this->financeiroModel->getClassificacoes();
        $centros = $this->financeiroModel->getCentrosCusto();
        $maxRows = max(count($bancos), count($categorias), count($centros));

        for ($i = 0; $i < $maxRows; $i++) {
            $row = array_fill(0, 11, '');
            $row[7] = isset($bancos[$i]) ? $bancos[$i]['nome'] : '';
            $row[8] = isset($categorias[$i]) ? $categorias[$i]['nome'] : '';
            $row[9] = isset($centros[$i]) ? $centros[$i]['nome'] : '';
            fputcsv($output, $row, ';');
        }

        fclose($output);
        exit();
    }

    /**
     * Processa o upload e importação do arquivo CSV.
     */
    public function processarImportacao()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/financeiro/movimentacoes');
            exit();
        }

        if (isset($_FILES['arquivo_csv']) && $_FILES['arquivo_csv']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['arquivo_csv']['tmp_name'];
            $handle = fopen($file, "r");

            if ($handle !== FALSE) {
                // Pula o BOM se existir
                $bom = fread($handle, 3);
                if ($bom !== "\xEF\xBB\xBF") {
                    rewind($handle);
                }

                // Lê o cabeçalho (descarta)
                fgetcsv($handle, 1000, ";");

                // Carrega dados auxiliares para mapeamento (Nome -> ID)
                $bancos = $this->financeiroModel->getBancos();
                $categorias = $this->financeiroModel->getClassificacoes();
                $centros = $this->financeiroModel->getCentrosCusto();

                // Funções auxiliares de busca (Case Insensitive)
                $findId = function ($name, $list) {
                    $name = trim($name);
                    if (empty($name)) return null;
                    foreach ($list as $item) {
                        if (strcasecmp($item['nome'], $name) === 0) return $item['id'];
                    }
                    return null;
                };

                $sucessos = 0;
                $erros = 0;
                $errosDetalhados = [];

                while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
                    // Validação básica de colunas (agora precisa de pelo menos 5, ID + 4 obrigatórios)
                    if (count($data) < 5) continue;

                    // Mapeamento dos dados
                    $id = trim($data[0] ?? '');
                    $id = !empty($id) && is_numeric($id) ? (int)$id : null;

                    $tipo = strtoupper(trim($data[1] ?? ''));
                    $tipo = ($tipo === 'R') ? 'R' : 'P'; // Default P se inválido

                    $descricao = trim($data[2] ?? '');
                    if (empty($descricao)) {
                        // Ignora linhas sem descrição (linhas vazias ou de referência no final do arquivo)
                        continue;
                    }

                    // Tratamento de valor (suporta formatos variados)
                    $valorStr = $data[3] ?? '0';
                    // Remove caracteres não numéricos exceto ponto e vírgula
                    $valorStr = preg_replace('/[^\d.,]/', '', $valorStr);

                    if (strpos($valorStr, ',') !== false && strpos($valorStr, '.') !== false) {
                        // Formato com milhar e decimal (ex: 1.234,56) -> assume pt-BR
                        $valorStr = str_replace('.', '', $valorStr);
                        $valorStr = str_replace(',', '.', $valorStr);
                    } elseif (strpos($valorStr, ',') !== false) {
                        // Apenas vírgula (ex: 1234,56) -> assume decimal pt-BR
                        $valorStr = str_replace(',', '.', $valorStr);
                    }
                    // Se tiver apenas ponto, assume formato internacional (1234.56)

                    $valor = (float) $valorStr;

                    // Tratamento de Data de Vencimento (Aceita DD/MM/AAAA ou AAAA-MM-DD)
                    $vencimentoRaw = trim($data[4] ?? '');
                    $vencimento = date('Y-m-d'); // Fallback padrão

                    if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})/', $vencimentoRaw, $matches)) {
                        $vencimento = "{$matches[3]}-{$matches[2]}-{$matches[1]}";
                    } elseif (preg_match('/^\d{4}-\d{2}-\d{2}$/', $vencimentoRaw)) {
                        $vencimento = $vencimentoRaw;
                    }

                    $status = ucfirst(strtolower(trim($data[5] ?? '')));
                    if (!in_array($status, ['Pendente', 'Pago', 'Atrasado', 'Cancelado'])) {
                        $status = 'Pendente';
                    }

                    // Tratamento de Data de Pagamento
                    $dataPagamentoRaw = trim($data[6] ?? '');
                    $dataPagamento = null;
                    if (!empty($dataPagamentoRaw)) {
                        if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})/', $dataPagamentoRaw, $matches)) {
                            $dataPagamento = "{$matches[3]}-{$matches[2]}-{$matches[1]}";
                        } elseif (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dataPagamentoRaw)) {
                            $dataPagamento = $dataPagamentoRaw;
                        }
                    }

                    $bancoId = $findId($data[7] ?? '', $bancos);
                    $categoriaId = $findId($data[8] ?? '', $categorias);
                    $centroCustoId = $findId($data[9] ?? '', $centros);
                    $observacoes = $data[10] ?? '';

                    $dadosTransacao = [
                        'id' => $id,
                        'tipo' => $tipo,
                        'descricao' => $descricao,
                        'valor' => $valor,
                        'vencimento' => $vencimento,
                        'status' => $status,
                        'data_pagamento' => $dataPagamento,
                        'banco_id' => $bancoId,
                        'classificacao_id' => $categoriaId,
                        'centro_custo_id' => $centroCustoId,
                        'observacoes' => $observacoes,
                        // Campos padrão
                        'juros' => 0,
                        'desconto' => 0,
                        'forma_pagamento' => null,
                        'dataEmissao' => date('Y-m-d'),
                        'documentoVinculado' => null,
                        'contrato_parcela_id' => null
                    ];

                    if ($this->financeiroModel->salvarTransacao($dadosTransacao)) {
                        $sucessos++;
                    } else {
                        $erros++;
                        $msgErro = $this->financeiroModel->getLastError() ?? 'Erro desconhecido';
                        if (count($errosDetalhados) < 3) { // Limita a 3 mensagens para não poluir a tela
                            $errosDetalhados[] = "Linha " . ($sucessos + $erros + 1) . ": " . $msgErro;
                        }
                    }
                }
                fclose($handle);

                if ($sucessos > 0) {
                    $msg = "$sucessos transações importadas com sucesso.";
                    if ($erros > 0) {
                        $msg .= " ($erros falhas). Motivos: " . implode('; ', $errosDetalhados);
                        if ($erros > 3) $msg .= "...";
                    }
                    $this->setFlashMessage('success', $msg);
                } else {
                    $msg = "Nenhuma transação importada.";
                    if (!empty($errosDetalhados)) {
                        $msg .= " Motivos: " . implode('; ', $errosDetalhados);
                    }
                    $this->setFlashMessage('error', $msg);
                }
            } else {
                $this->setFlashMessage('error', 'Erro ao ler o arquivo CSV.');
            }
        } else {
            $this->setFlashMessage('error', 'Nenhum arquivo enviado ou erro no upload.');
        }

        header('Location: ' . BASE_URL . '/financeiro/movimentacoes');
        exit();
    }

    /**
     * Exibe a página para prestação de contas de despesas de projeto.
     */
    public function prestacaoContas()
    {
        $projetosModel = new \App\Models\ProjetosModel();
        $projetos = $projetosModel->getAllProjetosParaSelect();

        // Se não há projetos ativos, não é possível criar prestação de contas
        if (empty($projetos)) {
            $this->setFlashMessage('error', 'Não há projetos ativos disponíveis para prestação de contas.');
            header('Location: ' . BASE_URL . '/financeiro');
            exit();
        }

        // Também precisamos das categorias de despesa.
        $categoriasPrestacao = $this->financeiroModel->getPrestacaoCategorias();

        $bancos = $this->financeiroModel->getBancos();

        $centrosCusto = $this->financeiroModel->getCentrosCusto();

        // Busca solicitações do usuário que estão 'Em Análise' ou 'Reprovado'
        $usuarioLogadoId = $this->session->get('user_id');
        $filtros = [
            'status' => ['Em Análise', 'Reprovado'],
            'descricao' => 'Prestação de Contas: %',
            'usuario_id' => $usuarioLogadoId
        ];
        
        // Lógica de Paginação para Solicitações Pendentes
        $paginaAtual = filter_input(INPUT_GET, 'page_pendentes', FILTER_VALIDATE_INT) ?: 1;
        $itensPorPagina = 5;
        $offset = ($paginaAtual - 1) * $itensPorPagina;

        $minhasPrestacoes = $this->financeiroModel->getTransacoes('P', 'created_at', 'DESC', $itensPorPagina, $offset, $filtros);
        $totalPrestacoes = $this->financeiroModel->getContagemTransacoes('P', $filtros);
        $totalPaginas = (int)ceil($totalPrestacoes / $itensPorPagina);

        $data = [
            'pageTitle' => 'Prestação de Contas por Projeto',
            'projetos' => $projetos,
            'categorias' => $categoriasPrestacao, // Nome genérico para a view
            'bancos' => $bancos,
            'centrosCusto' => $centrosCusto,
            'minhasPrestacoes' => $minhasPrestacoes,
            'paginaAtual' => $paginaAtual,
            'totalPaginas' => $totalPaginas,
            'transacao' => null // Modo criação
        ];

        $this->renderView('financeiro/prestacao_contas', $data);
    }

    /**
     * Exibe o formulário de prestação de contas preenchido para edição.
     * @param int $id
     */
    public function editarPrestacaoContas(int $id)
    {
        $transacao = $this->financeiroModel->getTransacaoPorId($id);
        $usuarioLogadoId = $this->session->get('user_id');

        // Validação de propriedade: Apenas o criador ou um aprovador pode editar
        // Assumimos que se o campo usuario_id for nulo (legado), a verificação é ignorada ou tratada conforme regra de negócio
        if ($transacao && !empty($transacao['usuario_id']) && $transacao['usuario_id'] != $usuarioLogadoId) {
            // Se não for o dono, verifica se tem permissão de aprovação (admin/financeiro) para permitir edição excepcional
            if (!$this->session->hasPermission('financeiro_prestacao_contas_approve')) {
                $this->setFlashMessage('error', 'Você não tem permissão para editar uma prestação de contas criada por outro usuário.');
                header('Location: ' . BASE_URL . '/financeiro/prestacaoContas');
                exit();
            }
        }

        // Permite edição apenas se estiver 'Em Análise' ou 'Reprovado' (para correção)
        if (!$transacao || !in_array($transacao['status'], ['Em Análise', 'Reprovado'])) {
            $this->setFlashMessage('error', 'Esta prestação de contas já foi processada (Aprovada ou Paga) e não pode ser editada.');
            header('Location: ' . BASE_URL . '/financeiro/prestacaoContas');
            exit();
        }

        if (preg_match('/Projeto ID: (\d+)\./', $transacao['observacoes'] ?? '', $matches)) {
            // FIX: Extrai o ID do projeto da string de observações e o adiciona ao array da transação
            // para que o formulário possa pré-selecionar o projeto correto.
            $transacao['projeto_id'] = $matches[1];
            $transacao['observacoes_limpa'] = trim(str_replace($matches[0], '', $transacao['observacoes'] ?? ''));
        } else {
            // Garante que as chaves existam mesmo que o padrão não seja encontrado, evitando erros.
            $transacao['projeto_id'] = null;
            $transacao['observacoes_limpa'] = $transacao['observacoes'] ?? '';
        }

        // Extrai o Fornecedor das observações (formato: "Fornecedor: {nome} | ")
        if (preg_match('/Fornecedor: (.*?) \| /', $transacao['observacoes_limpa'], $matchesFornecedor)) {
            $transacao['fornecedor'] = $matchesFornecedor[1];
            $transacao['observacoes_limpa'] = trim(str_replace($matchesFornecedor[0], '', $transacao['observacoes_limpa']));
        } else {
            $transacao['fornecedor'] = '';
        }

        // Extrai o Local das observações
        if (preg_match('/Local: (.*?) \| /', $transacao['observacoes_limpa'], $matchesLocal)) {
            $transacao['local_despesa'] = $matchesLocal[1];
            $transacao['observacoes_limpa'] = trim(str_replace($matchesLocal[0], '', $transacao['observacoes_limpa']));
        } else {
            $transacao['local_despesa'] = '';
        }

        // Extrai o Nº da Nota Fiscal das observações
        if (preg_match('/NF: (.*?) \| /', $transacao['observacoes_limpa'], $matchesNf)) {
            $transacao['numero_nota_fiscal'] = $matchesNf[1];
            $transacao['observacoes_limpa'] = trim(str_replace($matchesNf[0], '', $transacao['observacoes_limpa']));
        } else {
            $transacao['numero_nota_fiscal'] = '';
        }

        // Extrai a Placa das observações
        if (preg_match('/Placa: (.*?) \| /', $transacao['observacoes_limpa'], $matchesPlaca)) {
            $transacao['placa_veiculo'] = $matchesPlaca[1];
            $transacao['observacoes_limpa'] = trim(str_replace($matchesPlaca[0], '', $transacao['observacoes_limpa']));
        } else {
            $transacao['placa_veiculo'] = '';
        }

        // Extrai o Hodômetro das observações
        if (preg_match('/Hodômetro: (.*?) \| /', $transacao['observacoes_limpa'], $matchesHodometro)) {
            $transacao['hodometro'] = $matchesHodometro[1];
            $transacao['observacoes_limpa'] = trim(str_replace($matchesHodometro[0], '', $transacao['observacoes_limpa']));
        } else {
            $transacao['hodometro'] = '';
        }

        // Extrai os Litros das observações
        if (preg_match('/Litros: (.*?) \| /', $transacao['observacoes_limpa'], $matchesLitros)) {
            $transacao['litros'] = $matchesLitros[1];
            $transacao['observacoes_limpa'] = trim(str_replace($matchesLitros[0], '', $transacao['observacoes_limpa']));
        } else {
            $transacao['litros'] = '';
        }

        // Limpa o prefixo da descrição
        $transacao['descricao_limpa'] = str_replace('Prestação de Contas: ', '', $transacao['descricao']);

        // Carrega os dados para os selects do formulário
        $projetosModel = new \App\Models\ProjetosModel();
        $projetos = $projetosModel->getAllProjetosParaSelect();

        if (!empty($transacao['projeto_id'])) { // 'projeto_id' agora vem das observações
            $projetoAtualId = (int)$transacao['projeto_id'];
            $projetoExiste = false;
            foreach ($projetos as $p) {
                if ((int)$p['id'] === $projetoAtualId) {
                    $projetoExiste = true;
                    break;
                }
            }
            if (!$projetoExiste) {
                $projAtual = $projetosModel->getProjetoById($projetoAtualId);
                if ($projAtual) {
                    $projetos[] = $projAtual;
                }
            }
        }

        $categoriasPrestacao = $this->financeiroModel->getPrestacaoCategorias();
        $bancos = $this->financeiroModel->getBancos();
        
        // Correção: Permite selecionar qualquer centro de custo
        $centrosCusto = $this->financeiroModel->getCentrosCusto();

        $usuarioLogadoId = $this->session->get('user_id');
        $filtros = [
            'status' => ['Em Análise', 'Reprovado'],
            'descricao' => 'Prestação de Contas: %',
            'usuario_id' => $usuarioLogadoId
        ];

        // Lógica de Paginação para Solicitações Pendentes
        $paginaAtual = filter_input(INPUT_GET, 'page_pendentes', FILTER_VALIDATE_INT) ?: 1;
        $itensPorPagina = 5;
        $offset = ($paginaAtual - 1) * $itensPorPagina;

        $minhasPrestacoes = $this->financeiroModel->getTransacoes('P', 'created_at', 'DESC', $itensPorPagina, $offset, $filtros);
        $totalPrestacoes = $this->financeiroModel->getContagemTransacoes('P', $filtros);
        $totalPaginas = (int)ceil($totalPrestacoes / $itensPorPagina);

        $data = [
            'pageTitle' => 'Editar Prestação de Contas',
            'projetos' => $projetos,
            'categorias' => $categoriasPrestacao,
            'bancos' => $bancos,
            'centrosCusto' => $centrosCusto,
            'transacao' => $transacao,
            'minhasPrestacoes' => $minhasPrestacoes,
            'paginaAtual' => $paginaAtual,
            'totalPaginas' => $totalPaginas
        ];

        $this->renderView('financeiro/prestacao_contas', $data);
    }

    /**
     * Salva uma nova prestação de contas (despesa de projeto).
     */
    public function salvarPrestacaoContas()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/financeiro/prestacaoContas');
            exit();
        }

        // Verifica se o POST está vazio em uma requisição POST, o que indica que o upload excedeu o limite do servidor (post_max_size)
        // Se $_POST estiver vazio, mesmo que $_FILES tenha algo, os dados textuais foram perdidos (comum em estouro de post_max_size).
        if (empty($_POST)) {
            $this->setFlashMessage('error', 'Dados do formulário não recebidos. O arquivo anexo pode ser muito grande (limite do servidor) ou houve falha na conexão.');
            header('Location: ' . BASE_URL . '/financeiro/prestacaoContas');
            exit();
        }

        $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
        $usuarioLogadoId = $this->session->get('user_id');

        // Se for edição, verifica se ainda pode ser editado
        if ($id) {
            $existing = $this->financeiroModel->getTransacaoPorId($id);

            // Validação de propriedade na ação de salvar
            if ($existing && !empty($existing['usuario_id']) && $existing['usuario_id'] != $usuarioLogadoId) {
                if (!$this->session->hasPermission('financeiro_prestacao_contas_approve')) {
                    $this->setFlashMessage('error', 'Você não tem permissão para alterar esta prestação de contas.');
                    header('Location: ' . BASE_URL . '/financeiro/prestacaoContas');
                    exit();
                }
            }

            // Permite edição apenas se estiver 'Em Análise' ou 'Reprovado'
            if (!$existing || !in_array($existing['status'], ['Em Análise', 'Reprovado'])) {
                $this->setFlashMessage('error', 'Esta prestação de contas já foi aprovada e não pode ser editada.');
                header('Location: ' . BASE_URL . '/financeiro/prestacaoContas');
                exit();
            }
        }

        // Tratamento de valor
        $valorFormatado = trim($_POST['valor'] ?? '0');
        $valorFloat = (function ($str) {
            $str = preg_replace('/[^\d\-,.]/', '', $str);
            if ($str === '') return 0.0;
            if (strpos($str, '.') !== false && strpos($str, ',') !== false) {
                // Formato pt-BR: 1.234,56 -> 1234.56
                $str = str_replace('.', '', $str);
                $str = str_replace(',', '.', $str);
            } elseif (strpos($str, ',') !== false) {
                // 1234,56 -> 1234.56
                $str = str_replace(',', '.', $str);
            } else {
                // Formato en ou milhares com múltiplos pontos
                if (substr_count($str, '.') > 1) {
                    $parts = explode('.', $str);
                    $decimal = array_pop($parts);
                    $str = implode('', $parts) . '.' . $decimal;
                }
            }
            return (float) $str;
        })($valorFormatado);

        // Coleta e sanitiza os dados do formulário
        // Nota: Evitando filter_input devido a incompatibilidades conhecidas com multipart/form-data em alguns servidores.
        $projetoIdRaw = $_POST['projeto_id'] ?? null;
        $projetoId = is_null($projetoIdRaw) ? 0 : (int) trim((string) $projetoIdRaw);

        $dados = [
            'projeto_id' => $projetoId,
            'descricao' => isset($_POST['descricao']) ? trim(htmlspecialchars($_POST['descricao'], ENT_QUOTES, 'UTF-8')) : '',
            'valor' => $valorFloat,
            'data_despesa' => $_POST['data_despesa'] ?? null,
            'prestacao_categoria_id' => (isset($_POST['prestacao_categoria_id']) && $_POST['prestacao_categoria_id'] !== '') ? (int)$_POST['prestacao_categoria_id'] : null,
            'banco_id' => (isset($_POST['banco_id']) && $_POST['banco_id'] !== '') ? (int)$_POST['banco_id'] : null,
            'centro_custo_id' => (isset($_POST['centro_custo_id']) && $_POST['centro_custo_id'] !== '') ? (int)$_POST['centro_custo_id'] : null,
            'fornecedor' => isset($_POST['fornecedor']) ? trim(htmlspecialchars($_POST['fornecedor'], ENT_QUOTES, 'UTF-8')) : '',
            'local_despesa' => isset($_POST['local_despesa']) ? trim(htmlspecialchars($_POST['local_despesa'], ENT_QUOTES, 'UTF-8')) : '',
            'placa_veiculo' => isset($_POST['placa_veiculo']) ? trim(htmlspecialchars($_POST['placa_veiculo'], ENT_QUOTES, 'UTF-8')) : '',
            'hodometro' => isset($_POST['hodometro']) ? trim(htmlspecialchars($_POST['hodometro'], ENT_QUOTES, 'UTF-8')) : '',
            'litros' => isset($_POST['litros']) ? trim(htmlspecialchars($_POST['litros'], ENT_QUOTES, 'UTF-8')) : '',
            'numero_nota_fiscal' => isset($_POST['numero_nota_fiscal']) ? trim(htmlspecialchars($_POST['numero_nota_fiscal'], ENT_QUOTES, 'UTF-8')) : '',
            'observacoes' => isset($_POST['observacoes']) ? trim(htmlspecialchars($_POST['observacoes'], ENT_QUOTES, 'UTF-8')) : '',
            'forma_pagamento' => isset($_POST['forma_pagamento']) ? trim(htmlspecialchars($_POST['forma_pagamento'], ENT_QUOTES, 'UTF-8')) : '',
        ];

        // Validação básica
        $erros = [];
        if ($dados['projeto_id'] < 0) { // Mudança: permitir ID 0
            $erros[] = 'Projeto (obrigatório)';
        } else {
            // Verifica se o projeto existe e não está cancelado/concluído
            $projModel = new \App\Models\ProjetosModel();
            $proj = $projModel->getProjetoById($dados['projeto_id']);
            if (!$proj || in_array($proj['status'], ['Cancelado', 'Concluído'])) {
                $erros[] = 'Projeto (inválido ou finalizado)';
            }
        }
        if (empty($dados['descricao'])) $erros[] = 'Descrição';
        if ($dados['valor'] <= 0) $erros[] = 'Valor';
        if (empty($dados['data_despesa'])) $erros[] = 'Data da Despesa';
        if (empty($dados['prestacao_categoria_id']) || $dados['prestacao_categoria_id'] <= 0) $erros[] = 'Categoria da Despesa';

        // Validação específica para Combustível
        if (!empty($dados['prestacao_categoria_id'])) {
            $categorias = $this->financeiroModel->getPrestacaoCategorias();
            $nomeCategoria = '';
            foreach ($categorias as $cat) {
                if ($cat['id'] == $dados['prestacao_categoria_id']) {
                    $nomeCategoria = $cat['nome'];
                    break;
                }
            }

            if (stripos($nomeCategoria, 'combustível') !== false || stripos($nomeCategoria, 'combustivel') !== false || stripos($nomeCategoria, 'abastecimento') !== false) {
                if (empty($dados['placa_veiculo'])) $erros[] = 'Placa do Veículo';
                if (empty($dados['litros'])) $erros[] = 'Litros';
                if (empty($dados['hodometro'])) $erros[] = 'Hodômetro';
            }
        }

        if (!empty($erros)) {
            $this->setFlashMessage('error', 'Por favor, preencha os campos obrigatórios: ' . implode(', ', $erros));
            header('Location: ' . BASE_URL . '/financeiro/prestacaoContas');
            exit();
        }

        // Lógica de upload do comprovante
        $comprovantePath = null;
        if (isset($_FILES['comprovante']) && $_FILES['comprovante']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = ROOT_PATH . '/storage/comprovantes_prestacao/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0775, true);
            }
            $fileInfo = pathinfo($_FILES['comprovante']['name']);
            $extension = strtolower($fileInfo['extension']);
            $safeFilename = preg_replace('/[^A-Za-z0-9_\-]/', '_', $fileInfo['filename']);
            $newFilename = 'prestacao_proj' . $dados['projeto_id'] . '_' . time() . '.' . $extension;
            $destination = $uploadDir . $newFilename;

            if (move_uploaded_file($_FILES['comprovante']['tmp_name'], $destination)) {
                $comprovantePath = $newFilename;

                // Se for uma edição e um novo comprovante foi enviado, apaga o antigo.
                if ($id) {
                    $transacaoExistente = $this->financeiroModel->getTransacaoPorId($id);
                    if (!empty($transacaoExistente['documentoVinculado']) && $transacaoExistente['documentoVinculado'] !== $comprovantePath) {
                        $oldFilePath = $uploadDir . $transacaoExistente['documentoVinculado'];
                        if (file_exists($oldFilePath)) {
                            @unlink($oldFilePath);
                        }
                    }
                }
            } else {
                $this->setFlashMessage('error', 'Erro ao salvar o arquivo de comprovante.');
                header('Location: ' . BASE_URL . '/financeiro/prestacaoContas');
                exit();
            }
        }

        // Se for edição e não enviou novo arquivo, mantém o antigo
        if ($id && !$comprovantePath) {
            $existing = $this->financeiroModel->getTransacaoPorId($id);
            $comprovantePath = $existing['documentoVinculado'] ?? null;
        }

        $infoExtra = "";
        if (!empty($dados['fornecedor'])) $infoExtra .= "Fornecedor: " . $dados['fornecedor'] . " | ";
        if (!empty($dados['local_despesa'])) $infoExtra .= "Local: " . $dados['local_despesa'] . " | ";
        if (!empty($dados['numero_nota_fiscal'])) $infoExtra .= "NF: " . $dados['numero_nota_fiscal'] . " | ";
        if (!empty($dados['placa_veiculo'])) $infoExtra .= "Placa: " . $dados['placa_veiculo'] . " | ";
        if (!empty($dados['hodometro'])) $infoExtra .= "Hodômetro: " . $dados['hodometro'] . " | ";
        if (!empty($dados['litros'])) $infoExtra .= "Litros: " . $dados['litros'] . " | ";

        // Monta os dados para salvar como uma transação financeira (despesa)
        $dadosTransacao = [
            'id' => $id,
            'tipo' => 'P', // Despesa
            'descricao' => "Prestação de Contas: " . $dados['descricao'],
            'valor' => $dados['valor'],
            'vencimento' => $dados['data_despesa'], // A data da despesa é o vencimento
            'status' => 'Em Análise', // Status inicial para aprovação
            'observacoes' => "Projeto ID: {$dados['projeto_id']}. " . $infoExtra . ($dados['observacoes'] ?? ''),
            'documentoVinculado' => $comprovantePath, // Salva o path do comprovante
            'classificacao_id' => null, // Categoria principal fica nula até a aprovação
            'prestacao_categoria_id' => $dados['prestacao_categoria_id'] ?: null, // Salva a categoria da prestação
            'centro_custo_id' => $dados['centro_custo_id'] ?: null,
            'data_pagamento' => null,
            'dataEmissao' => date('Y-m-d'),
            'juros' => 0,
            'desconto' => 0,
            'forma_pagamento' => $dados['forma_pagamento'] ?: null,
            'banco_id' => $dados['banco_id'] ?: null,
            'contrato_parcela_id' => null,
            'usuario_id' => $usuarioLogadoId, // Salva o ID do usuário criador
        ];

        // VERIFICAÇÃO DE DUPLICIDADE (Prevenção de duplo clique/envio)
        if (empty($id) && $this->financeiroModel->verificarDuplicidade($dadosTransacao)) {
            $this->setFlashMessage('error', 'Solicitação duplicada detectada! Uma prestação idêntica foi enviada recentemente.');
            header('Location: ' . BASE_URL . '/financeiro/prestacaoContas');
            exit();
        }

        $savedId = $this->financeiroModel->salvarTransacao($dadosTransacao);

        if ($savedId) {
            $msg = $id ? 'Prestação de contas atualizada com sucesso.' : 'Prestação de contas enviada com sucesso para aprovação.';
            $this->setFlashMessage('success', $msg);

            // --- INÍCIO: LÓGICA DE NOTIFICAÇÃO POR E-MAIL ---
            // Envia e-mail apenas para novas submissões, não para edições.
            if (!$id) {
                // Coleta dados para o e-mail
                $usuario = $this->usuarioModel->getUsuario($usuarioLogadoId);
                $projeto = $this->projetosModel->getProjetoById($dados['projeto_id']);

                $emailData = [
                    'solicitante_nome' => $usuario['nome'] ?? 'Usuário desconhecido',
                    'projeto_nome' => $projeto['nome'] ?? 'Projeto não encontrado',
                    'descricao' => $dados['descricao'],
                    'valor' => $dados['valor'],
                    'data_despesa' => $dados['data_despesa'],
                ];

                $this->enviarEmailNotificacaoAprovacao($emailData);
            }
            // --- FIM: LÓGICA DE NOTIFICAÇÃO POR E-MAIL ---
        } else {
            $err = $this->financeiroModel->getUltimoErro();
            // log detalhado para administrador/developer
            error_log('salvarPrestacaoContas falhou: ' . ($err ?: 'erro desconhecido'));
            error_log('Dados enviados: ' . print_r($dadosTransacao, true));

            $userMsg = 'Ocorreu um erro ao enviar a prestação de contas.';
            if ($err) {
                // not showing raw SQL errors to users in production; keep basic info
                $userMsg .= ' (' . htmlentities($err) . ')';
            }
            $this->setFlashMessage('error', $userMsg);
        }

        header('Location: ' . BASE_URL . '/financeiro/prestacaoContas');
        exit();
    }

    /**
     * Exclui uma prestação de contas (específico para o fluxo de reembolso).
     * @param int $id
     */
    public function excluirPrestacaoContas()
    {
        // Garante que a requisição seja do tipo POST para segurança (evita exclusão via GET/CSRF)
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->setFlashMessage('error', 'Operação inválida.');
            header('Location: ' . BASE_URL . '/financeiro/prestacaoContas');
            exit();
        }

        // Validação de CSRF para segurança
        if (!isset($_POST['csrf_token']) || !$this->validateCsrfToken($_POST['csrf_token'])) {
            $this->setFlashMessage('error', 'Erro de validação de segurança (CSRF). Por favor, tente novamente.');
            header('Location: ' . BASE_URL . '/financeiro/prestacaoContas');
            exit();
        }

        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

        if ($id <= 0) {
            $this->setFlashMessage('error', 'ID inválido.');
            header('Location: ' . BASE_URL . '/financeiro/prestacaoContas');
            exit();
        }

        $transacao = $this->financeiroModel->getTransacaoPorId($id);
        $usuarioLogadoId = $this->session->get('user_id');

        if (!$transacao) {
            $this->setFlashMessage('error', 'Solicitação não encontrada.');
            header('Location: ' . BASE_URL . '/financeiro/prestacaoContas');
            exit();
        }

        // Verifica propriedade e status
        if (($transacao['usuario_id'] ?? null) != $usuarioLogadoId && !$this->session->hasPermission('financeiro_prestacao_contas_approve')) {
            $this->setFlashMessage('error', 'Você não tem permissão para excluir esta solicitação.');
        } elseif (!in_array($transacao['status'], ['Em Análise', 'Reprovado'])) {
            $this->setFlashMessage('error', 'Não é possível excluir solicitações já aprovadas ou pagas.');
        } else {
            if ($this->financeiroModel->excluirTransacao($id)) {
                $this->setFlashMessage('success', 'Solicitação excluída com sucesso.');
            } else {
                $this->setFlashMessage('error', 'Erro ao excluir solicitação.');
            }
        }

        header('Location: ' . BASE_URL . '/financeiro/prestacaoContas');
        exit();
    }

    /**
     * Endpoint AJAX para buscar classificações por query.
     */
    public function searchClassificacoesAjax()
    {
        header('Content-Type: application/json');
        $query = filter_input(INPUT_GET, 'query', FILTER_SANITIZE_SPECIAL_CHARS);
        $tipo = filter_input(INPUT_GET, 'tipo', FILTER_SANITIZE_SPECIAL_CHARS);

        if (empty($query) || strlen($query) < 3) {
            echo json_encode(['success' => false, 'message' => 'A query deve ter pelo menos 3 caracteres.']);
            exit();
        }

        $classificacoes = $this->financeiroModel->searchClassificacoes($query, $tipo, 20); // Limite de 20 resultados

        echo json_encode(['success' => true, 'data' => $classificacoes]);
        exit();
    }

    /**
     * Endpoint AJAX para buscar centros de custo por query.
     */
    public function searchCentrosCustoAjax()
    {
        header('Content-Type: application/json');
        $query = filter_input(INPUT_GET, 'query', FILTER_SANITIZE_SPECIAL_CHARS);

        if (empty($query) || strlen($query) < 3) {
            echo json_encode(['success' => false, 'message' => 'A query deve ter pelo menos 3 caracteres.']);
            exit();
        }

        $centrosCusto = $this->financeiroModel->searchCentrosCusto($query, 20); // Limite de 20 resultados

        echo json_encode(['success' => true, 'data' => $centrosCusto]);
        exit();
    }

    /**
     * Exibe a lista de Contas a Pagar.
     */

    /**
     * Exibe a lista de prestações de contas aguardando aprovação.
     */
    public function aprovacaoPrestacaoContas()
    {
        // Busca transações com status 'Em Análise'
        // e que sejam especificamente "Prestações de Contas"
        $filtros = [
            'status' => 'Em Análise',
            'descricao' => 'Prestação de Contas: %'
        ];
        
        // Lógica de Paginação
        $paginaAtual = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1;
        $itensPorPagina = 20;
        $offset = ($paginaAtual - 1) * $itensPorPagina;

        // Ordena por data de vencimento (data da despesa) para mostrar as mais antigas primeiro. Adiciona JOIN para pegar nome da categoria.
        $transacoes = $this->financeiroModel->getTransacoes('P', 'vencimento', 'ASC', $itensPorPagina, $offset, $filtros);

        $totalTransacoes = $this->financeiroModel->getContagemTransacoes('P', $filtros);
        $totalPaginas = ceil($totalTransacoes / $itensPorPagina);

        $data = [
            'pageTitle' => 'Aprovação de Prestações de Contas',
            'transacoes' => $transacoes,
            'paginaAtual' => $paginaAtual,
            'totalPaginas' => $totalPaginas,
        ];

        $this->renderView('financeiro/aprovacao_prestacao', $data);
    }

    /**
     * Exibe a lista de prestações de contas que já foram aprovadas.
     * Filtra por status 'Pendente' (Aprovado, a pagar) ou 'Pago'.
     */
    public function prestacoesAprovadas()
    {
        $mesFiltro = filter_input(INPUT_GET, 'mes', FILTER_SANITIZE_SPECIAL_CHARS);

        // Filtros fixos para buscar apenas prestações de contas aprovadas
        $filtros = [
            'descricao' => 'Prestação de Contas: %',
            'status' => ['Pendente', 'Pago']
        ];

        if ($mesFiltro) {
            $filtros['mes'] = $mesFiltro;
        }

        // Lógica de Paginação
        $paginaAtual = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1;
        $itensPorPagina = 20;
        $offset = ($paginaAtual - 1) * $itensPorPagina;

        // Busca as transações (ordenadas das mais recentes para as mais antigas)
        $transacoes = $this->financeiroModel->getTransacoes('P', 'vencimento', 'DESC', $itensPorPagina, $offset, $filtros);
        
        $totalTransacoes = $this->financeiroModel->getContagemTransacoes('P', $filtros);
        $totalPaginas = ceil($totalTransacoes / $itensPorPagina);

        $data = [
            'pageTitle' => 'Histórico de Prestações Aprovadas',
            'transacoes' => $transacoes,
            'paginaAtual' => $paginaAtual,
            'totalPaginas' => $totalPaginas,
            'mesFiltro' => $mesFiltro,
        ];

        $this->renderView('financeiro/prestacao_aprovadas', $data);
    }

    /**
     * Processa a aprovação ou reprovação de uma prestação de contas.
     */
    public function processarAprovacao()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/financeiro/aprovacaoPrestacaoContas');
            exit();
        }

        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $acao = isset($_POST['acao']) ? trim($_POST['acao']) : '';
        $motivo = isset($_POST['motivo_reprovacao']) ? htmlspecialchars($_POST['motivo_reprovacao'], ENT_QUOTES, 'UTF-8') : '';

        // Permitimos id == 0 porque há casos em que o banco retorna essa chave.
        // O importante é que seja um inteiro não-negativo e que a ação seja válida.
        if ($id === false || $id < 0 || !in_array($acao, ['aprovar', 'reprovar'])) {
            // Mensagem de erro detalhada para diagnóstico
            $msg = 'Ação inválida.';
            if (empty($_POST)) {
                $msg .= ' (Nenhum dado recebido. Possível erro de redirecionamento ou limite de post)';
            } else {
                $rawId = $_POST['id'] ?? null;
                $msg .= " (ID Recebido: " . var_export($rawId, true) . ", Ação: '$acao')";
            }
            $this->setFlashMessage('error', $msg);
            header('Location: ' . BASE_URL . '/financeiro/aprovacaoPrestacaoContas');
            exit();
        }

        if ($acao === 'aprovar') {
            if ($this->financeiroModel->aprovarPrestacaoDeContas($id)) {
                $this->setFlashMessage('success', 'Prestação de contas aprovada com sucesso.');
            } else {
                $this->setFlashMessage('error', 'Erro ao aprovar a solicitação.');
            }
        } elseif ($acao === 'reprovar') {
            if ($this->financeiroModel->atualizarStatus($id, 'Reprovado', $motivo)) {
                $this->setFlashMessage('success', 'Prestação de contas reprovada com sucesso.');
            } else {
                $this->setFlashMessage('error', 'Erro ao reprovar a solicitação.');
            }
        }

        // --- INÍCIO: LÓGICA DE NOTIFICAÇÃO PARA O SOLICITANTE ---
        $transacao = $this->financeiroModel->getTransacaoPorId($id);
        if ($transacao && !empty($transacao['usuario_id'])) {
            $solicitante = $this->usuarioModel->getUsuario($transacao['usuario_id']);

            if ($solicitante && !empty($solicitante['email'])) {
                $this->enviarEmailResultadoPrestacao(
                    $solicitante['email'],
                    $solicitante['nome'],
                    $transacao,
                    $acao, // 'aprovar' ou 'reprovar'
                    $motivo // motivo da reprovação
                );

                // Notificação Interna no Sistema
                $statusTxt = ($acao === 'aprovar') ? 'Aprovada' : 'Reprovada';
                $this->notificacoesModel->criarNotificacao(
                    (int)$transacao['usuario_id'],
                    "Prestação de Contas {$statusTxt}",
                    "Sua solicitação de R$ " . number_format($transacao['valor'], 2, ',', '.') . " foi {$statusTxt}.",
                    BASE_URL . "/financeiro/prestacaoContas"
                );
            }
        }

        header('Location: ' . BASE_URL . '/financeiro/aprovacaoPrestacaoContas');
        exit();
    }

    /**
     * Gera um relatório PDF das prestações de contas aprovadas de um projeto.
     */
    public function relatorioPrestacaoContasProjeto()
    {
        // filter_input com FILTER_VALIDATE_INT pode retornar 0, que é um ID válido em alguns casos, mas !0 é true.
        // A verificação deve ser mais estrita para não falhar com o ID 0.
        $projetoId = filter_input(INPUT_GET, 'relatorio_projeto_id', FILTER_VALIDATE_INT);

        if ($projetoId === false || $projetoId === null) {
            $this->setFlashMessage('error', 'Projeto não especificado.');
            header('Location: ' . BASE_URL . '/financeiro/prestacaoContas');
            exit();
        }

        $projetosModel = new \App\Models\ProjetosModel();
        $projeto = $projetosModel->getProjetoById($projetoId);

        if (!$projeto) {
            $this->setFlashMessage('error', 'Projeto não encontrado.');
            header('Location: ' . BASE_URL . '/financeiro/prestacaoContas');
            exit();
        }

        $transacoes = $this->financeiroModel->getPrestacoesPorProjeto($projetoId);

        // Processa as transações para extrair dados das observações
        foreach ($transacoes as &$t) {
            $obs = $t['observacoes'] ?? '';
            $t['local_despesa'] = '';
            $t['fornecedor'] = '';

            if (preg_match('/Local: (.*?) \| /', $obs, $matches)) {
                $t['local_despesa'] = $matches[1];
            }
            if (preg_match('/Fornecedor: (.*?) \| /', $obs, $matches)) {
                $t['fornecedor'] = $matches[1];
            }

            // Limpa a descrição para o relatório
            $t['descricao'] = str_replace('Prestação de Contas: ', '', $t['descricao']);
        }
        unset($t);

        $empresa = $this->empresaModel->getDadosEmpresa();

        $data = [
            'pageTitle' => 'Relatório de Prestações de Contas - ' . $projeto['nome'],
            'projeto' => $projeto,
            'transacoes' => $transacoes,
            'empresa' => $empresa,
            'dataGeracao' => date('d/m/Y H:i:s'),
        ];

        ob_start();
        $this->renderPartial('financeiro/relatorio_prestacao_projeto_pdf', $data);
        $html = ob_get_clean();

        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', '300');
        ini_set('pcre.backtrack_limit', '5000000');
        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream("prestacao_contas_projeto_{$projetoId}.pdf", ["Attachment" => false]);
        exit();
    }

    /**
     * Exporta um arquivo ZIP contendo o CSV das prestações de contas e os comprovantes anexos.
     */
    public function exportarPrestacaoContasZip()
    {
        // Tenta obter o ID de várias fontes usando $_REQUEST (mais robusto que filter_input)
        $rawId = $_REQUEST['relatorio_projeto_id'] ?? $_REQUEST['projeto_id'] ?? null;
        $projetoId = filter_var($rawId, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);

        // Verifica se o ID foi realmente fornecido e é um inteiro válido
        if ($projetoId === null || $projetoId === false) {
            $this->setFlashMessage('error', 'Projeto não especificado.');
            header('Location: ' . BASE_URL . '/financeiro/prestacaoContas');
            exit();
        }

        $projetosModel = new \App\Models\ProjetosModel();
        $projeto = $projetosModel->getProjetoById($projetoId);

        if (!$projeto) {
            $this->setFlashMessage('error', 'Projeto não encontrado.');
            header('Location: ' . BASE_URL . '/financeiro/prestacaoContas');
            exit();
        }

        $transacoes = $this->financeiroModel->getPrestacoesPorProjeto($projetoId);

        if (empty($transacoes)) {
            $this->setFlashMessage('info', 'Nenhuma prestação de contas aprovada encontrada para este projeto.');
            header('Location: ' . BASE_URL . '/financeiro/prestacaoContas');
            exit();
        }

        // Configuração do ZIP
        $zip = new \ZipArchive();
        
        // Sanitiza o nome do projeto (remove acentos e caracteres inválidos)
        $nomeLimpo = $projeto['nome'];
        if (function_exists('iconv')) {
            $nomeLimpo = iconv('UTF-8', 'ASCII//TRANSLIT', $nomeLimpo);
        }
        $projectNameSafe = preg_replace('/[^A-Za-z0-9_\-]/', '_', $nomeLimpo);
        $projectNameSafe = preg_replace('/_+/', '_', $projectNameSafe); // Remove múltiplos underscores
        $projectNameSafe = trim($projectNameSafe, '_');

        // Formato: Prestacao_Proj{ID}_{NOME}_{DATA}.zip
        $zipName = 'Prestacao_Proj' . $projeto['id'] . '_' . $projectNameSafe . '_' . date('Ymd_His') . '.zip';
        $tempZipPath = sys_get_temp_dir() . '/' . $zipName;

        if ($zip->open($tempZipPath, \ZipArchive::CREATE) !== TRUE) {
            $this->setFlashMessage('error', 'Não foi possível criar o arquivo ZIP temporário.');
            header('Location: ' . BASE_URL . '/financeiro/prestacaoContas');
            exit();
        }

        // Geração do CSV
        // Cabeçalho compatível com Excel (separado por ponto e vírgula)
        $csvHeader = ['ID', 'Data', 'Descrição', 'Categoria', 'Valor (R$)', 'Status', 'Fornecedor', 'Local', 'NF', 'Placa', 'Hodometro', 'Litros', 'Nome do Arquivo Comprovante', 'URL do Comprovante'];
        
        $fp = fopen('php://temp', 'r+');
        fputs($fp, "\xEF\xBB\xBF"); // BOM para UTF-8 no Excel
        fputcsv($fp, $csvHeader, ';');

        $uploadDir = ROOT_PATH . '/storage/comprovantes_prestacao/';

        foreach ($transacoes as $t) {
            // Processamento de dados das observações (igual ao relatório PDF)
            $obs = $t['observacoes'] ?? '';
            $local = ''; $fornecedor = ''; $nf = ''; $placa = ''; $hodometro = ''; $litros = '';
            
            if (preg_match('/Local: (.*?) \| /', $obs, $matches)) $local = $matches[1];
            if (preg_match('/Fornecedor: (.*?) \| /', $obs, $matches)) $fornecedor = $matches[1];
            if (preg_match('/NF: (.*?) \| /', $obs, $matches)) $nf = $matches[1];
            if (preg_match('/Placa: (.*?) \| /', $obs, $matches)) $placa = $matches[1];
            if (preg_match('/Hodômetro: (.*?) \| /', $obs, $matches)) $hodometro = $matches[1];
            if (preg_match('/Litros: (.*?) \| /', $obs, $matches)) $litros = $matches[1];

            $descricao = str_replace('Prestação de Contas: ', '', $t['descricao']);
            
            $nomeArquivoComprovante = '';
            $urlComprovante = '';
            
            // Adiciona o comprovante ao ZIP se existir
            if (!empty($t['documentoVinculado'])) {
                $filePath = $uploadDir . $t['documentoVinculado'];
                if (file_exists($filePath)) {
                    $ext = pathinfo($filePath, PATHINFO_EXTENSION);
                    // Renomeia o arquivo dentro do ZIP para facilitar identificação: ID_Descricao_Valor.ext
                    $descSafe = substr(preg_replace('/[^A-Za-z0-9_\-]/', '_', $descricao), 0, 30);
                    $nomeNoZip = 'comprovantes/' . $t['id'] . '_' . $descSafe . '.' . $ext;
                    
                    $zip->addFile($filePath, $nomeNoZip);
                    $nomeArquivoComprovante = $nomeNoZip;
                    $urlComprovante = BASE_URL . '/storage/comprovantes_prestacao/' . $t['documentoVinculado'];
                }
            }

            fputcsv($fp, [$t['id'], date('d/m/Y', strtotime($t['data'])), $descricao, $t['nome_classificacao'] ?? '-', number_format($t['valor'], 2, ',', '.'), $t['status'], $fornecedor, $local, $nf, $placa, $hodometro, $litros, $nomeArquivoComprovante, $urlComprovante], ';');
        }

        rewind($fp);
        $csvContent = stream_get_contents($fp);
        fclose($fp);

        $zip->addFromString('Relatorio_Despesas.csv', $csvContent);
        $zip->close();

        // Limpa qualquer buffer de saída para evitar corromper o arquivo binário
        if (ob_get_level()) ob_end_clean();

        // Envia o arquivo ZIP para download
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $zipName . '"');
        header('Content-Length: ' . filesize($tempZipPath));
        readfile($tempZipPath);
        
        unlink($tempZipPath); // Remove o arquivo temporário
        exit();
    }

    /**
     * Exibe a página de filtro para o relatório de combustível.
     */
    public function relatorioCombustivel()
    {
        $placa = filter_input(INPUT_GET, 'placa', FILTER_SANITIZE_SPECIAL_CHARS);
        $dataInicio = filter_input(INPUT_GET, 'data_inicio');
        $dataFim = filter_input(INPUT_GET, 'data_fim');

        $transacoes = $this->financeiroModel->getRelatorioCombustivel($placa, $dataInicio, $dataFim);

        $data = [
            'pageTitle' => 'Relatório de Combustível',
            'transacoes' => $transacoes,
            'filtros' => [
                'placa' => $placa,
                'data_inicio' => $dataInicio,
                'data_fim' => $dataFim
            ]
        ];

        $this->renderView('financeiro/relatorio_combustivel', $data);
    }

    /**
     * Gera o PDF do relatório de combustível.
     */
    public function exportarRelatorioCombustivelPdf()
    {
        $placa = filter_input(INPUT_GET, 'placa', FILTER_SANITIZE_SPECIAL_CHARS);
        $dataInicio = filter_input(INPUT_GET, 'data_inicio');
        $dataFim = filter_input(INPUT_GET, 'data_fim');

        $transacoes = $this->financeiroModel->getRelatorioCombustivel($placa, $dataInicio, $dataFim);
        $empresa = $this->empresaModel->getDadosEmpresa();

        $data = [
            'pageTitle' => 'Relatório de Despesas com Combustível',
            'transacoes' => $transacoes,
            'filtros' => ['placa' => $placa, 'data_inicio' => $dataInicio, 'data_fim' => $dataFim],
            'empresa' => $empresa,
            'dataGeracao' => date('d/m/Y H:i:s')
        ];

        ob_start();
        $this->renderPartial('financeiro/relatorio_combustivel_pdf', $data);
        $html = ob_get_clean();

        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', '300');
        ini_set('pcre.backtrack_limit', '5000000');
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream("relatorio_combustivel_" . date('Y-m-d') . ".pdf", ["Attachment" => false]);
        exit();
    }

    /**
     * Processa a reprovação em massa de múltiplas prestações de contas.
     */
    public function processarReprovacaoEmMassa()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/financeiro/aprovacaoPrestacaoContas');
            exit();
        }

        $ids = $_POST['transacao_ids'] ?? [];
        $motivo = trim($_POST['motivo_reprovacao'] ?? '');

        if (empty($ids) || empty($motivo)) {
            $this->setFlashMessage('error', 'Nenhuma despesa foi selecionada ou o motivo da reprovação não foi informado.');
            header('Location: ' . BASE_URL . '/financeiro/aprovacaoPrestacaoContas');
            exit();
        }

        $novoStatus = 'Reprovado';

        if ($this->financeiroModel->atualizarStatusEmMassa($ids, $novoStatus, $motivo)) {
            $this->setFlashMessage('success', count($ids) . ' prestações de contas foram reprovadas com sucesso.');

            // Notifica cada solicitante individualmente
            foreach ($ids as $id) {
                $transacao = $this->financeiroModel->getTransacaoPorId($id);
                if ($transacao && !empty($transacao['usuario_id'])) {
                    $solicitante = $this->usuarioModel->getUsuario($transacao['usuario_id']);

                    if ($solicitante && !empty($solicitante['email'])) {
                        $this->enviarEmailResultadoPrestacao(
                            $solicitante['email'],
                            $solicitante['nome'],
                            $transacao,
                            'reprovar',
                            $motivo
                        );
                    }
                }
            }
        } else {
            $this->setFlashMessage('error', 'Erro ao processar a reprovação em massa.');
        }

        header('Location: ' . BASE_URL . '/financeiro/aprovacaoPrestacaoContas');
        exit();
    }

    /**
     * Processa a aprovação em massa de múltiplas prestações de contas.
     */
    public function processarAprovacaoEmMassa()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/financeiro/aprovacaoPrestacaoContas');
            exit();
        }

        $ids = $_POST['transacao_ids'] ?? [];

        if (empty($ids)) {
            $this->setFlashMessage('info', 'Nenhuma despesa foi selecionada para aprovação.');
            header('Location: ' . BASE_URL . '/financeiro/aprovacaoPrestacaoContas');
            exit();
        }

        // O novo status para itens aprovados é 'Pendente' (aguardando pagamento)
        $novoStatus = 'Pendente';

        $sucesso = true;
        $erros = 0;

        if ($this->financeiroModel->atualizarStatusEmMassa($ids, $novoStatus)) {
            // --- LÓGICA DE NOTIFICAÇÃO EM MASSA ---
            // Itera sobre cada ID para buscar os detalhes e notificar o solicitante.
            foreach ($ids as $id) {
                $transacao = $this->financeiroModel->getTransacaoPorId($id);
                if ($transacao && !empty($transacao['usuario_id'])) {
                    $solicitante = $this->usuarioModel->getUsuario($transacao['usuario_id']);

                    if ($solicitante && !empty($solicitante['email'])) {
                        $this->enviarEmailResultadoPrestacao(
                            $solicitante['email'],
                            $solicitante['nome'],
                            $transacao,
                            'aprovar', // Ação é 'aprovar'
                            ''        // Motivo é vazio para aprovação
                        );
                    }
                }
            }
        } else {
            $sucesso = false;
            $erros++;
        }

        if ($sucesso) {
            $this->setFlashMessage('success', count($ids) . ' prestações de contas foram aprovadas com sucesso.');
        } else {
            $msg = 'Erro ao processar a aprovação em massa.';
            if ($erros > 0) $msg .= " ($erros falhas)";
            $this->setFlashMessage('error', $msg);
        }

        header('Location: ' . BASE_URL . '/financeiro/aprovacaoPrestacaoContas');
        exit();
    }

    /**
     * Envia um e-mail para o solicitante com o resultado da aprovação/reprovação.
     * @param string $emailDestino
     * @param string $nomeSolicitante
     * @param array $transacao
     * @param string $resultado 'aprovar' ou 'reprovar'
     * @param string $motivo
     * @return bool
     */
    private function enviarEmailResultadoPrestacao(string $emailDestino, string $nomeSolicitante, array $transacao, string $resultado, string $motivo): bool
    {
        if (!defined('MAIL_HOST')) {
            error_log("Configurações de e-mail (MAIL_HOST) não definidas.");
            return false;
        }

        $isAprovado = ($resultado === 'aprovar');
        $assunto = 'Resultado da sua Prestação de Contas: ' . ($isAprovado ? 'Aprovada' : 'Reprovada');

        $corpo = "<p>Olá, " . htmlspecialchars($nomeSolicitante) . ".</p>";
        $corpo .= "<p>Sua prestação de contas foi analisada. Veja os detalhes abaixo:</p>";
        $corpo .= "<ul>";
        $corpo .= "<li><strong>Descrição:</strong> " . htmlspecialchars(str_replace('Prestação de Contas: ', '', $transacao['descricao'])) . "</li>";
        $corpo .= "<li><strong>Valor:</strong> R$ " . number_format($transacao['valor'], 2, ',', '.') . "</li>";
        $corpo .= "<li><strong>Data da Despesa:</strong> " . date('d/m/Y', strtotime($transacao['vencimento'])) . "</li>";
        $corpo .= "</ul>";

        if ($isAprovado) {
            $corpo .= "<p style='color: green; font-weight: bold;'>Status: APROVADA</p>";
            $corpo .= "<p>A despesa foi aprovada e seguirá para o fluxo de pagamento.</p>";
            $linkAcao = BASE_URL . "/financeiro/prestacaoContas";
            $textoLink = "Clique aqui para ver suas solicitações";
        } else {
            $corpo .= "<p style='color: red; font-weight: bold;'>Status: REPROVADA</p>";
            $corpo .= "<p><strong>Motivo:</strong> " . nl2br(htmlspecialchars($motivo)) . "</p>";
            $corpo .= "<p>Por favor, acesse o sistema para corrigir a solicitação e reenviá-la para análise.</p>";
            $linkAcao = BASE_URL . "/financeiro/editarPrestacaoContas/" . $transacao['id'];
            $textoLink = "Clique aqui para corrigir a despesa";
        }

        $corpo .= "<p><a href='" . $linkAcao . "'>{$textoLink}</a></p>";
        $corpo .= "<br><p>Atenciosamente,<br>SysEnviCorp</p>";

        $mail = new PHPMailer(true);
        try {
            // Configurações do servidor
            $mail->isSMTP();
            $mail->Host       = defined('MAIL_HOST') ? MAIL_HOST : 'localhost';
            $mail->SMTPAuth   = true;
            $mail->Username   = defined('MAIL_USERNAME') ? MAIL_USERNAME : '';
            $mail->Password   = defined('MAIL_PASSWORD') ? MAIL_PASSWORD : '';
            $mail->SMTPSecure = (defined('MAIL_ENCRYPTION') && MAIL_ENCRYPTION === 'ssl') ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = defined('MAIL_PORT') ? MAIL_PORT : 587;
            $mail->CharSet    = 'UTF-8';

            // Destinatários
            $fromEmail = defined('MAIL_FROM_ADDRESS') ? MAIL_FROM_ADDRESS : 'noreply@sysenvicorp.com';
            $fromName = defined('MAIL_FROM_NAME') ? MAIL_FROM_NAME : 'SysEnviCorp';

            $mail->setFrom($fromEmail, $fromName);
            $mail->addAddress($emailDestino, $nomeSolicitante);

            // Conteúdo
            $mail->isHTML(true);
            $mail->Subject = $assunto;
            $mail->Body = $corpo;

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Erro ao enviar e-mail de resultado de prestação de contas: {$mail->ErrorInfo}");
            return false;
        }
    }

    /**
     * Envia um e-mail de notificação para o aprovador sobre uma nova prestação de contas.
     * @param array $dadosEmail Dados para compor o e-mail.
     * @return bool Retorna true em sucesso, false em falha.
     */
    private function enviarEmailNotificacaoAprovacao(array $dadosEmail): bool
    {
        if (!defined('MAIL_HOST')) {
            error_log("Configurações de e-mail (MAIL_HOST) não definidas.");
            return false;
        }

        $destinatarios = [];

        // 1. Tenta usar a constante definida (caso exista no config.php)
        if (defined('FINANCEIRO_APROVADOR_EMAIL') && !empty(FINANCEIRO_APROVADOR_EMAIL)) {
            $destinatarios = array_merge($destinatarios, (array)FINANCEIRO_APROVADOR_EMAIL);
        } else {
            // 2. Se não definida, busca automaticamente usuários com permissão de aprovação
            $perfis = $this->perfilModel->getAll();
            $perfisAprovadores = [];

            foreach ($perfis as $p) {
                $permissoes = json_decode($p['permissoes'] ?? '[]', true);
                // Verifica se tem permissão específica ou é admin total (*)
                if (is_array($permissoes) && (in_array('financeiro_prestacao_contas_approve', $permissoes) || in_array('*', $permissoes))) {
                    $perfisAprovadores[] = $p['perfil_id'];
                }
            }

            if (!empty($perfisAprovadores)) {
                $usuarios = $this->usuarioModel->getListaUsuarios();
                foreach ($usuarios as $u) {
                    // Verifica se o usuário tem um dos perfis aprovadores, está ativo e tem e-mail
                    if (in_array($u['perfil_id'], $perfisAprovadores) && !empty($u['email']) && strtolower($u['status']) === 'ativo') {
                        $destinatarios[] = $u['email'];
                    }
                }
            }
        }

        // Remove duplicatas
        $destinatarios = array_unique($destinatarios);

        if (empty($destinatarios)) {
            error_log("Nenhum destinatário encontrado para notificação de aprovação (Constante não definida e nenhum usuário com permissão encontrado).");
            return false;
        }

        $mail = new PHPMailer(true);
        try {
            // Configurações do servidor
            $mail->isSMTP();
            $mail->Host       = defined('MAIL_HOST') ? MAIL_HOST : 'localhost';
            $mail->SMTPAuth   = true;
            $mail->Username   = defined('MAIL_USERNAME') ? MAIL_USERNAME : '';
            $mail->Password   = defined('MAIL_PASSWORD') ? MAIL_PASSWORD : '';
            $mail->SMTPSecure = (defined('MAIL_ENCRYPTION') && MAIL_ENCRYPTION === 'ssl') ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = defined('MAIL_PORT') ? MAIL_PORT : 587;
            $mail->CharSet    = 'UTF-8';

            // Destinatários
            $fromEmail = defined('MAIL_FROM_ADDRESS') ? MAIL_FROM_ADDRESS : 'noreply@sysenvicorp.com';
            $fromName = defined('MAIL_FROM_NAME') ? MAIL_FROM_NAME : 'SysEnviCorp';

            $mail->setFrom($fromEmail, $fromName);
            
            foreach ($destinatarios as $email) {
                $mail->addAddress($email);
            }

            // Conteúdo
            $mail->isHTML(true);
            $mail->Subject = 'Nova Prestação de Contas para Aprovação';

            // Monta o corpo do e-mail
            $valorFormatado = number_format($dadosEmail['valor'], 2, ',', '.');
            $dataFormatada = date('d/m/Y', strtotime($dadosEmail['data_despesa']));

            $corpo = "<p>Olá,</p><p>Uma nova prestação de contas foi enviada para sua aprovação:</p><ul><li><strong>Solicitante:</strong> " . htmlspecialchars($dadosEmail['solicitante_nome']) . "</li><li><strong>Projeto:</strong> " . htmlspecialchars($dadosEmail['projeto_nome']) . "</li><li><strong>Descrição:</strong> " . htmlspecialchars($dadosEmail['descricao']) . "</li><li><strong>Valor:</strong> R$ {$valorFormatado}</li><li><strong>Data da Despesa:</strong> {$dataFormatada}</li></ul><p>Por favor, acesse o sistema para revisar e aprovar/reprovar a solicitação.</p><p><a href='" . BASE_URL . "/financeiro/aprovacaoPrestacaoContas'>Clique aqui para ir para a tela de aprovação.</a></p><br><p>Atenciosamente,<br>SysEnviCorp</p>";
            $mail->Body = $corpo;

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Erro ao enviar e-mail de notificação de prestação de contas: {$mail->ErrorInfo}");
            return false;
        }
    }

    /**
     * Sincroniza a despesa paga com o orçamento do projeto, se aplicável.
     */
    private function sincronizarDespesaProjeto(int $transacaoId)
    {
        $transacao = $this->financeiroModel->getTransacaoPorId($transacaoId);
        
        if (!$transacao) return;

        // Se é uma Despesa (P) e está Paga, deve estar no orçamento
        if ($transacao['tipo'] === 'P' && $transacao['status'] === 'Pago') {
            // Verifica vínculo com projeto nas observações (Padrão: "Projeto ID: {id}")
            if (!empty($transacao['observacoes']) && preg_match('/Projeto ID: (\d+)/', $transacao['observacoes'], $matches)) {
                $projetoId = (int)$matches[1];
                $data = !empty($transacao['data_pagamento']) ? $transacao['data_pagamento'] : ($transacao['vencimento'] ?? date('Y-m-d'));
                $categoria = $transacao['nome_classificacao'] ?? 'Despesa Geral';
                
                $this->projetosModel->registrarDespesaDeTransacao($projetoId, $transacaoId, $transacao['descricao'], (float)$transacao['valor'], $data, $categoria);
            }
        } else {
            // Se não for (P) ou não estiver Paga (ex: Pendente, Cancelado), remove do orçamento do projeto
            $this->projetosModel->removerDespesaDeTransacao($transacaoId);
        }
    }
}

if (!function_exists('generate_unique_color')) {
    /**
     * Gera uma cor hexadecimal única com base em um valor de semente (seed).
     *
     * @param int $seed Valor de semente para gerar a cor.
     * @return string Cor hexadecimal no formato #RRGGBB.
     */
    function generate_unique_color(int $seed): string
    {
        mt_srand($seed);
        $r = mt_rand(100, 255); // Red component
        $g = mt_rand(100, 255); // Green component
        $b = mt_rand(100, 255); // Blue component
        return sprintf("#%02x%02x%02x", $r, $g, $b);
    }
}
