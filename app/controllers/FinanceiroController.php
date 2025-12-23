<?php

namespace App\Controllers;

use App\Core\Connection;
use App\Models\FinancialModel;
// Importa as classes da biblioteca Dompdf
use Dompdf\Dompdf;
use Dompdf\Options;

class FinanceiroController extends BaseController
{
    private $financeiroModel;

    public function __construct()
    {
        parent::__construct();
        // Injeção de dependência para a conexão com o banco de dados
        $this->financeiroModel = new FinancialModel();
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
        ];

        // Lógica de Paginação para o fluxo de caixa
        $paginaAtual = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1;
        $itensPorPagina = 5; // 5 itens por página, conforme solicitado
        $offset = ($paginaAtual - 1) * $itensPorPagina;

        // Coleta dados do modelo
        $fluxoCaixa = $this->financeiroModel->getFluxoCaixaSummary($filtros, $itensPorPagina, $offset);
        // Anexa informação sobre transferência (id da transação parceira) para uso nas views
        foreach ($fluxoCaixa as &$t) {
            $t['transfer_partner_id'] = null;
            if (!empty($t['documento_vinculado'])) {
                $t['transfer_partner_id'] = $this->financeiroModel->findTransferPartnerIdByDocument($t['documento_vinculado']);
            }
        }
        unset($t);

        $totalTransacoes = $this->financeiroModel->getFluxoCaixaCount($filtros);
        $totalPaginas = ceil($totalTransacoes / $itensPorPagina);

        $contasPagar = $this->financeiroModel->getContasPagarMes();
        $contasReceber = $this->financeiroModel->getContasReceberMes();
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
                $t['transfer_partner_id'] = $this->financeiroModel->findTransferPartnerIdByDocument($t['documento_vinculado']);
                if (!empty($t['transfer_partner_id'])) {
                    $partner = $this->financeiroModel->getTransacaoById($t['transfer_partner_id']);
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
        $resumoAtrasadas = $this->financeiroModel->getContasReceberAtrasadasSummary();

        $proximoVencimento = $this->financeiroModel->getProximoVencimentoPagar();
        $ultimaAtualizacaoSaldo = $this->financeiroModel->getUltimaAtualizacaoSaldo();

        // Dados para os gráficos
        $monthlySummary = $this->financeiroModel->getMonthlySummaryForChart();
        $expenseSummary = $this->financeiroModel->getExpenseSummaryByCategory();

        $data = [
            'pageTitle'          => 'Financeiro - Fluxo de Caixa',
            'fluxoCaixa'         => $fluxoCaixa,
            'contasPagarTotal'   => $contasPagar,
            'contasReceberTotal' => $contasReceber,
            'saldoAtual'         => $saldoAtual,
            'saldosBancos'       => $saldosBancos,
            'paginaAtual'        => $paginaAtual,
            'totalPaginas'       => $totalPaginas,
            'filtros'            => $filtros, // Envia os filtros para a view
            'bancos'             => $bancos, // Passa a lista de bancos para a view
            'resumoAtrasadas'    => $resumoAtrasadas, // Contém 'count' e 'valor'
            'ultimaAtualizacaoSaldo' => $ultimaAtualizacaoSaldo,
            'proximoVencimento'  => $proximoVencimento,
            'monthlySummaryJson' => json_encode($monthlySummary),
            'expenseSummaryJson' => json_encode($expenseSummary),
        ];

        $this->renderView('financeiro/index', $data);
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
            'periodo' => filter_input(INPUT_GET, 'periodo', FILTER_SANITIZE_SPECIAL_CHARS) ?: 'recente', // 'dia', 'mes', 'intervalo'
            'data_unica' => filter_input(INPUT_GET, 'data_unica'),
            'mes_ano' => filter_input(INPUT_GET, 'mes_ano'),
            'data_inicio' => filter_input(INPUT_GET, 'data_inicio'),
            'data_fim' => filter_input(INPUT_GET, 'data_fim'),
        ];

        $transacoes = [];
        // Se houver filtros aplicados (além do padrão 'recente' e 'geral'), busca os dados
        // Ou se o tipo de relatório for por banco e um banco for selecionado
        if (
            ($filtros['periodo'] !== 'recente' || $filtros['tipo_relatorio'] === 'banco' && $filtros['banco_id']) ||
            (isset($_GET['data_unica']) || isset($_GET['mes_ano']) || isset($_GET['data_inicio']))
        ) {
            $transacoes = $this->financeiroModel->getTransacoesParaRelatorio($filtros);
        }

        $bancos = $this->financeiroModel->getBancos();

        $data = [
            'pageTitle' => 'Relatório de Movimentações Financeiras',
            'transacoes' => $transacoes,
            'filtros' => $filtros,
            'bancos' => $bancos,
        ];

        $this->renderView('financeiro/relatorio', $data);
    }

    /**
     * Exibe a página com todas as movimentações paginadas.
     */
    public function movimentacoes()
    {
        // Filtros simples compatíveis com a listagem
        $filtros = [
            'tipo' => filter_input(INPUT_GET, 'tipo', FILTER_SANITIZE_SPECIAL_CHARS),
            'data_inicio' => filter_input(INPUT_GET, 'data_inicio'),
            'data_fim' => filter_input(INPUT_GET, 'data_fim'),
            'periodo' => filter_input(INPUT_GET, 'periodo', FILTER_SANITIZE_SPECIAL_CHARS) ?: 'recente',
        ];

        $paginaAtual = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1;
        $itensPorPagina = 10;
        $offset = ($paginaAtual - 1) * $itensPorPagina;

        $fluxoCaixa = $this->financeiroModel->getFluxoCaixaSummary($filtros, $itensPorPagina, $offset);
        // Mapa de bancos por ID
        $bancos = $this->financeiroModel->getBancos();
        $bancosMap = [];
        foreach ($bancos as $b) {
            $bancosMap[$b['id']] = $b['nome'];
        }

        // Anexa nome do banco e info do parceiro de transferência para uso nas views
        foreach ($fluxoCaixa as &$t) {
            $t['banco_nome'] = $bancosMap[$t['banco_id']] ?? 'N/A';
            $t['partner_banco_nome'] = null;
            $t['transfer_partner_id'] = null;
            if (!empty($t['documento_vinculado'])) {
                $t['transfer_partner_id'] = $this->financeiroModel->findTransferPartnerIdByDocument($t['documento_vinculado']);
                if (!empty($t['transfer_partner_id'])) {
                    $partner = $this->financeiroModel->getTransacaoById($t['transfer_partner_id']);
                    if ($partner) {
                        $t['partner_banco_nome'] = $bancosMap[$partner['banco_id']] ?? null;
                    }
                }
            }
        }
        unset($t);

        $totalTransacoes = $this->financeiroModel->getFluxoCaixaCount($filtros);
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
     * Gera o relatório financeiro em formato PDF.
     */
    public function exportarRelatorioPdf()
    {
        // Coleta os filtros da URL
        $filtros = [
            'tipo_relatorio' => filter_input(INPUT_GET, 'tipo_relatorio', FILTER_SANITIZE_SPECIAL_CHARS) ?: 'geral',
            'banco_id' => filter_input(INPUT_GET, 'banco_id', FILTER_VALIDATE_INT),
            'periodo' => filter_input(INPUT_GET, 'periodo', FILTER_SANITIZE_SPECIAL_CHARS) ?: 'recente',
            'data_unica' => filter_input(INPUT_GET, 'data_unica'),
            'mes_ano' => filter_input(INPUT_GET, 'mes_ano'),
            'data_inicio' => filter_input(INPUT_GET, 'data_inicio'),
            'data_fim' => filter_input(INPUT_GET, 'data_fim'),
        ];

        $transacoes = $this->financeiroModel->getTransacoesParaRelatorio($filtros);
        $bancos = $this->financeiroModel->getBancos();
        $bancoSelecionado = null;
        if ($filtros['banco_id']) {
            foreach ($bancos as $banco) {
                if ($banco['id'] == $filtros['banco_id']) {
                    $bancoSelecionado = $banco['nome'];
                    break;
                }
            }
        }

        $data = [
            'pageTitle' => 'Extrato Financeiro',
            'transacoes' => $transacoes,
            'filtros' => $filtros,
            'bancoSelecionado' => $bancoSelecionado,
            'dataGeracao' => date('d/m/Y H:i:s'),
        ];

        // 1. Captura o HTML da view do relatório em uma variável
        ob_start();
        $this->renderPartial('financeiro/relatorio_pdf', $data);
        $html = ob_get_clean();

        // 2. Configura e instancia o Dompdf
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
        $data = [
            'pageTitle' => 'Nova Movimentação de Caixa',
            'transacao' => null, // Formulário vazio
            'bancos' => $bancos,
            'classificacoes' => $classificacoes,
            'centrosCusto' => $centrosCusto, // Passa para a view
        ];
        $this->renderView('financeiro/form', $data);
    }

    /**
     * Exibe o formulário para editar uma movimentação existente.
     * @param int $id O ID da transação.
     */
    public function editar($id)
    {
        $transacao = $this->financeiroModel->getTransacaoById($id);

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

        $data = [
            'pageTitle' => 'Editar Movimentação',
            'transacao' => $transacao,
            'bancos' => $bancos,
            'classificacoes' => $classificacoes,
            'centrosCusto' => $centrosCusto,
        ];

        $this->renderView('financeiro/form', $data);
    }

    /**
     * Exibe o formulário para editar uma transação existente.
     * @param int $id O ID da transação.
     */
    public function detalhe(int $id)
    {
        $transacao = $this->financeiroModel->getTransacaoById($id);
        $bancos = $this->financeiroModel->getBancos();
        $classificacoes = $this->financeiroModel->getClassificacoes($transacao['tipo'] ?? null);
        $centrosCusto = $this->financeiroModel->getCentrosCusto(); // Busca os centros de custo

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

            // Coleta e sanitiza os dados do formulário
            $dados = [
                'id' => filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: null,
                'tipo' => filter_input(INPUT_POST, 'tipo', FILTER_SANITIZE_SPECIAL_CHARS),
                'descricao' => filter_input(INPUT_POST, 'descricao', FILTER_SANITIZE_SPECIAL_CHARS),
                'valor' => $valorFloat,
                'vencimento' => filter_input(INPUT_POST, 'vencimento'),
                'data_pagamento' => filter_input(INPUT_POST, 'data_pagamento') ?: null, // Novo campo
                'dataEmissao' => filter_input(INPUT_POST, 'dataEmissao') ?: null,
                'status' => filter_input(INPUT_POST, 'status', FILTER_SANITIZE_SPECIAL_CHARS),
                'documentoVinculado' => filter_input(INPUT_POST, 'documentoVinculado', FILTER_SANITIZE_SPECIAL_CHARS) ?: null,
                'observacoes' => filter_input(INPUT_POST, 'observacoes', FILTER_SANITIZE_SPECIAL_CHARS) ?: null,
                'banco_id' => filter_input(INPUT_POST, 'banco_id', FILTER_VALIDATE_INT) ?: null,
                'classificacao_id' => filter_input(INPUT_POST, 'classificacao_id', FILTER_VALIDATE_INT) ?: null,
                'centro_custo_id' => filter_input(INPUT_POST, 'centro_custo_id', FILTER_VALIDATE_INT) ?: null,
                'juros' => (function($str) { return (float)str_replace(['.', ','], ['', '.'], $str); })($_POST['juros'] ?? '0'),
                'desconto' => (function($str) { return (float)str_replace(['.', ','], ['', '.'], $str); })($_POST['desconto'] ?? '0'),
                'forma_pagamento' => filter_input(INPUT_POST, 'forma_pagamento', FILTER_SANITIZE_SPECIAL_CHARS) ?: null,
            ];

            // Validação básica
            if ($dados['tipo'] && $dados['descricao'] && $dados['valor'] > 0 && $dados['vencimento'] && $dados['status']) {
                if ($this->financeiroModel->salvarTransacao($dados)) {
                    $message = $dados['id'] ? 'Transação atualizada com sucesso!' : 'Transação cadastrada com sucesso!';
                    $this->setFlashMessage('success', $message);
                } else {
                    $this->setFlashMessage('error', 'Ocorreu um erro ao salvar a transação. Tente novamente.');
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
    public function excluir(int $id)
    {
        // Validação básica do ID
        if ($id <= 0) {
            $this->setFlashMessage('error', 'ID de transação inválido.');
            header('Location: ' . $_SERVER['HTTP_REFERER'] ?? BASE_URL . '/financeiro');
            exit();
        }

        if ($this->financeiroModel->excluirTransacao($id)) {
            $this->setFlashMessage('success', 'Transação excluída com sucesso!');
        } else {
            $this->setFlashMessage('error', 'Erro ao excluir a transação.');
        }

        // Redireciona de volta para a página anterior (pagar ou receber)
        header('Location: ' . $_SERVER['HTTP_REFERER'] ?? BASE_URL . '/financeiro');
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

        $newId = $this->financeiroModel->addClassificacao($nome, $tipo);

        if ($newId) {
            echo json_encode([
                'success' => true,
                'message' => 'Classificação adicionada!',
                'data' => ['id' => $newId, 'nome' => $nome]
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erro ao adicionar classificação. Pode já existir.']);
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

        $newId = $this->financeiroModel->addCentroCusto($nome);

        if ($newId) {
            echo json_encode([
                'success' => true,
                'message' => 'Centro de Custo adicionado!',
                'data' => ['id' => $newId, 'nome' => $nome]
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erro ao adicionar centro de custo. Pode já existir.']);
        }
        exit();
    }
    /**
     * Exibe a lista de Contas a Pagar.
     */
    public function pagar()
    {
        $paginaAtual = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1;
        $itensPorPagina = 10; // Ou o número que preferir
        $offset = ($paginaAtual - 1) * $itensPorPagina;

        $transacoes = $this->financeiroModel->getTransacoes('P', 'vencimento', 'DESC', $itensPorPagina, $offset);
        $totalTransacoes = $this->financeiroModel->getTransacoesCount('P');
        $totalPaginas = ceil($totalTransacoes / $itensPorPagina);

        // Busca dados para o formulário do modal
        $bancos = $this->financeiroModel->getBancos();
        $classificacoes = $this->financeiroModel->getClassificacoes('P');
        $centrosCusto = $this->financeiroModel->getCentrosCusto();

        $data = [
            'pageTitle' => 'Contas a Pagar',
            'transacoes' => $transacoes,
            'tipo' => 'P',
            'paginaAtual' => $paginaAtual,
            'totalPaginas' => $totalPaginas,
            'bancos' => $bancos,
            'classificacoes' => $classificacoes,
            'centrosCusto' => $centrosCusto,
        ];
        $this->renderView('financeiro/lista_transacoes', $data);
    }

    /**
     * Exibe a lista de Contas a Receber.
     */
    public function receber()
    {
        $paginaAtual = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1;
        $itensPorPagina = 10; // Ou o número que preferir
        $offset = ($paginaAtual - 1) * $itensPorPagina;

        $transacoes = $this->financeiroModel->getTransacoes('R', 'vencimento', 'DESC', $itensPorPagina, $offset);
        $totalTransacoes = $this->financeiroModel->getTransacoesCount('R');
        $totalPaginas = ceil($totalTransacoes / $itensPorPagina);

        // Busca dados para o formulário do modal
        $bancos = $this->financeiroModel->getBancos();
        $classificacoes = $this->financeiroModel->getClassificacoes('R');
        $centrosCusto = $this->financeiroModel->getCentrosCusto();

        $data = [
            'pageTitle' => 'Contas a Receber',
            'transacoes' => $transacoes,
            'tipo' => 'R',
            'paginaAtual' => $paginaAtual,
            'totalPaginas' => $totalPaginas,
            'bancos' => $bancos,
            'classificacoes' => $classificacoes,
            'centrosCusto' => $centrosCusto,
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
}
