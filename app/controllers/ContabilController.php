<?php

namespace App\Controllers;

use App\Models\ContabilModel;
use App\Models\BancoModel;

class ContabilController extends BaseController
{
    protected $requiredPermissions = [
        'index'         => 'contabil_dashboard_view',
        'planocontas'   => 'contabil_planocontas_view',
        'planocontaForm' => 'contabil_planocontas_manage',
        'salvarPlanoConta' => 'contabil_planocontas_manage',
        'excluirPlanoConta' => 'contabil_planocontas_manage',
        'lancamentos'   => 'contabil_lancamentos_view',
        'lancamentoForm' => 'contabil_lancamentos_create',
        'salvarLancamento' => 'contabil_lancamentos_create',
        'excluirLancamento' => 'contabil_lancamentos_delete',
        'integrar'      => 'contabil_integrar',
        'integrarFinanceiro' => 'contabil_integrar',
        'integrarFolha' => 'contabil_integrar',
        'integrarContratos' => 'contabil_integrar',
        'demonstracoes' => 'contabil_demonstracoes_view',
        'balanco'       => 'contabil_demonstracoes_view',
        'dre'           => 'contabil_demonstracoes_view',
        'fluxocaixa'    => 'contabil_demonstracoes_view',
        'conciliacoes'  => 'contabil_conciliacao_view',
        'conciliacaoForm' => 'contabil_conciliacao_manage',
        'salvarConciliacao' => 'contabil_conciliacao_manage',
        'verConciliacao' => 'contabil_conciliacao_view',
        'conciliarItem' => 'contabil_conciliacao_manage',
        'finalizarConciliacao' => 'contabil_conciliacao_manage',
        'parametros'    => 'contabil_parametros_view',
        'salvarParametros' => 'contabil_parametros_manage',
    ];

    private ContabilModel $model;
    private ?BancoModel $bancoModel = null;

    public function __construct()
    {
        parent::__construct();
        $this->model = new ContabilModel();
    }

    // ========================
    // DASHBOARD
    // ========================

    public function index(): void
    {
        $resumo = $this->model->getResumo();
        $this->renderView('contabil/index', [
            'pageTitle' => 'Parâmetros Contábeis',
            'resumo' => $resumo,
        ]);
    }

    // ========================
    // PLANO DE CONTAS
    // ========================

    public function planocontas(): void
    {
        $contas = $this->model->getPlanoContasTree();
        $this->renderView('contabil/plano_contas_list', [
            'pageTitle' => 'Plano de Contas',
            'contas' => $contas,
        ]);
    }

    public function planocontaForm(?int $id = null): void
    {
        $conta = $id ? $this->model->getPlanoContaById($id) : null;
        $contasPai = $this->model->getPlanosContas();

        $this->renderView('contabil/plano_contas_form', [
            'pageTitle' => $id ? 'Editar Conta Contábil' : 'Nova Conta Contábil',
            'conta' => $conta,
            'contasPai' => $contasPai,
        ]);
    }

    public function salvarPlanoConta(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/contabil/planocontas');
            exit();
        }

        $dados = [
            'id' => filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: null,
            'codigo' => filter_input(INPUT_POST, 'codigo', FILTER_SANITIZE_SPECIAL_CHARS),
            'nome' => filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_SPECIAL_CHARS),
            'tipo' => filter_input(INPUT_POST, 'tipo', FILTER_SANITIZE_SPECIAL_CHARS) ?: 'analitico',
            'natureza' => filter_input(INPUT_POST, 'natureza', FILTER_SANITIZE_SPECIAL_CHARS) ?: 'devedora',
            'conta_pai_id' => filter_input(INPUT_POST, 'conta_pai_id', FILTER_VALIDATE_INT) ?: null,
            'ativo' => isset($_POST['ativo']) ? 1 : 0,
        ];

        if (empty($dados['codigo']) || empty($dados['nome'])) {
            $this->setFlashMessage('error', 'Código e Nome são obrigatórios.');
            header('Location: ' . BASE_URL . '/contabil/planocontaForm/' . ($dados['id'] ?? ''));
            exit();
        }

        if ($this->model->salvarPlanoConta($dados)) {
            $this->logAction('CREATE', 'Conta contábil criada/atualizada: ' . $dados['codigo'] . ' - ' . $dados['nome'], 'Contábil', $dados['id']);
            $this->setFlashMessage('success', 'Conta contábil salva com sucesso!');
        } else {
            $this->setFlashMessage('error', 'Erro ao salvar conta contábil.');
        }

        header('Location: ' . BASE_URL . '/contabil/planocontas');
        exit();
    }

    public function excluirPlanoConta(int $id): void
    {
        if ($this->model->excluirPlanoConta($id)) {
            $this->logAction('DELETE', 'Conta contábil excluída #' . $id, 'Contábil', $id);
            $this->setFlashMessage('success', 'Conta contábil excluída.');
        } else {
            $this->setFlashMessage('error', 'Erro ao excluir. Verifique se não há contas filhas ou lançamentos vinculados.');
        }
        header('Location: ' . BASE_URL . '/contabil/planocontas');
        exit();
    }

    // ========================
    // LANÇAMENTOS CONTÁBEIS
    // ========================

    public function lancamentos(): void
    {
        $filters = [
            'data_inicio' => filter_input(INPUT_GET, 'data_inicio', FILTER_SANITIZE_SPECIAL_CHARS) ?: null,
            'data_fim' => filter_input(INPUT_GET, 'data_fim', FILTER_SANITIZE_SPECIAL_CHARS) ?: null,
            'origem' => filter_input(INPUT_GET, 'origem', FILTER_SANITIZE_SPECIAL_CHARS) ?: null,
        ];
        $lancamentos = $this->model->getLancamentos(100, 0, $filters);

        $this->renderView('contabil/lancamentos', [
            'pageTitle' => 'Lançamentos Contábeis',
            'lancamentos' => $lancamentos,
            'filters' => $filters,
        ]);
    }

    public function lancamentoForm(?int $id = null): void
    {
        $lancamento = $id ? $this->model->getLancamentoById($id) : null;
        $contas = $this->model->getPlanosContas();

        $this->renderView('contabil/lancamento_form', [
            'pageTitle' => $id ? 'Editar Lançamento' : 'Novo Lançamento Contábil',
            'lancamento' => $lancamento,
            'contas' => $contas,
        ]);
    }

    public function salvarLancamento(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/contabil/lancamentos');
            exit();
        }

        $dados = [
            'id' => filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: null,
            'descricao' => filter_input(INPUT_POST, 'descricao', FILTER_SANITIZE_SPECIAL_CHARS),
            'valor' => filter_input(INPUT_POST, 'valor', FILTER_VALIDATE_FLOAT),
            'tipo' => filter_input(INPUT_POST, 'tipo', FILTER_SANITIZE_SPECIAL_CHARS) ?: 'debito',
            'categoria' => filter_input(INPUT_POST, 'categoria', FILTER_SANITIZE_SPECIAL_CHARS) ?: null,
            'data_lancamento' => filter_input(INPUT_POST, 'data_lancamento'),
            'conta' => filter_input(INPUT_POST, 'conta', FILTER_SANITIZE_SPECIAL_CHARS) ?: null,
            'centro_custo' => filter_input(INPUT_POST, 'centro_custo', FILTER_SANITIZE_SPECIAL_CHARS) ?: null,
            'debito_conta_id' => filter_input(INPUT_POST, 'debito_conta_id', FILTER_VALIDATE_INT) ?: null,
            'credito_conta_id' => filter_input(INPUT_POST, 'credito_conta_id', FILTER_VALIDATE_INT) ?: null,
            'origem' => 'manual',
            'observacoes' => filter_input(INPUT_POST, 'observacoes', FILTER_SANITIZE_SPECIAL_CHARS) ?: null,
            'usuario_id' => $this->session->get('user_id'),
        ];

        if (empty($dados['descricao']) || $dados['valor'] === false || empty($dados['data_lancamento'])) {
            $this->setFlashMessage('error', 'Descrição, Valor e Data são obrigatórios.');
            header('Location: ' . BASE_URL . '/contabil/lancamentoForm/' . ($dados['id'] ?? ''));
            exit();
        }

        if ($this->model->salvarLancamento($dados)) {
            $this->logAction('CREATE', 'Lançamento contábil: ' . $dados['descricao'], 'Contábil');
            $this->setFlashMessage('success', 'Lançamento salvo com sucesso!');
        } else {
            $this->setFlashMessage('error', 'Erro ao salvar lançamento.');
        }

        header('Location: ' . BASE_URL . '/contabil/lancamentos');
        exit();
    }

    public function excluirLancamento(int $id): void
    {
        if ($this->model->excluirLancamento($id)) {
            $this->logAction('DELETE', 'Lançamento contábil excluído #' . $id, 'Contábil', $id);
            $this->setFlashMessage('success', 'Lançamento excluído.');
        } else {
            $this->setFlashMessage('error', 'Erro ao excluir lançamento.');
        }
        header('Location: ' . BASE_URL . '/contabil/lancamentos');
        exit();
    }

    // ========================
    // INTEGRAÇÃO AUTOMÁTICA
    // ========================

    public function integrar(): void
    {
        $this->renderView('contabil/integrar', [
            'pageTitle' => 'Integração Automática',
        ]);
    }

    public function integrarFinanceiro(): void
    {
        $mes = filter_input(INPUT_POST, 'mes', FILTER_VALIDATE_INT) ?: (int)date('m');
        $ano = filter_input(INPUT_POST, 'ano', FILTER_VALIDATE_INT) ?: (int)date('Y');

        $result = $this->model->integrarTransacoesFinanceiras($mes, $ano);
        $this->logAction('INTEGRATE', 'Integração financeira: ' . $result['message'], 'Contábil');
        $this->setFlashMessage($result['importados'] > 0 ? 'success' : 'info', $result['message']);
        header('Location: ' . BASE_URL . '/contabil/integrar');
        exit();
    }

    public function integrarFolha(): void
    {
        $mes = filter_input(INPUT_POST, 'mes', FILTER_VALIDATE_INT) ?: (int)date('m');
        $ano = filter_input(INPUT_POST, 'ano', FILTER_VALIDATE_INT) ?: (int)date('Y');

        $result = $this->model->integrarFolhaPagamento($mes, $ano);
        $this->logAction('INTEGRATE', 'Integração folha: ' . $result['message'], 'Contábil');
        $this->setFlashMessage($result['importados'] > 0 ? 'success' : 'info', $result['message']);
        header('Location: ' . BASE_URL . '/contabil/integrar');
        exit();
    }

    public function integrarContratos(): void
    {
        $mes = filter_input(INPUT_POST, 'mes', FILTER_VALIDATE_INT) ?: (int)date('m');
        $ano = filter_input(INPUT_POST, 'ano', FILTER_VALIDATE_INT) ?: (int)date('Y');

        $result = $this->model->integrarContratos($mes, $ano);
        $this->logAction('INTEGRATE', 'Integração contratos: ' . $result['message'], 'Contábil');
        $this->setFlashMessage($result['importados'] > 0 ? 'success' : 'info', $result['message']);
        header('Location: ' . BASE_URL . '/contabil/integrar');
        exit();
    }

    // ========================
    // DEMONSTRAÇÕES CONTÁBEIS
    // ========================

    public function demonstracoes(): void
    {
        $this->renderView('contabil/demonstracoes', [
            'pageTitle' => 'Demonstrações Contábeis',
        ]);
    }

    public function balanco(): void
    {
        $ano = filter_input(INPUT_GET, 'ano', FILTER_VALIDATE_INT) ?: (int)date('Y');
        $data = $this->model->getBalancoPatrimonial($ano);

        $totalAtivo = array_sum(array_column($data['ativo'] ?? [], 'saldo'));
        $totalPassivo = array_sum(array_column($data['passivo'] ?? [], 'saldo'));
        $totalPL = array_sum(array_column($data['patrimonio_liquido'] ?? [], 'saldo'));

        $this->renderView('contabil/balanco', [
            'pageTitle' => 'Balanço Patrimonial',
            'data' => $data,
            'anoSelecionado' => $ano,
            'totalAtivo' => $totalAtivo,
            'totalPassivo' => $totalPassivo,
            'totalPL' => $totalPL,
        ]);
    }

    public function dre(): void
    {
        $ano = filter_input(INPUT_GET, 'ano', FILTER_VALIDATE_INT) ?: (int)date('Y');
        $data = $this->model->getDRE($ano);

        $this->renderView('contabil/dre', [
            'pageTitle' => 'Demonstração de Resultado (DRE)',
            'data' => $data,
            'anoSelecionado' => $ano,
        ]);
    }

    public function fluxocaixa(): void
    {
        $ano = filter_input(INPUT_GET, 'ano', FILTER_VALIDATE_INT) ?: (int)date('Y');
        $data = $this->model->getFluxoCaixa($ano);

        $this->renderView('contabil/fluxo_caixa', [
            'pageTitle' => 'Fluxo de Caixa',
            'data' => $data,
            'anoSelecionado' => $ano,
        ]);
    }

    // ========================
    // CONCILIAÇÃO BANCÁRIA
    // ========================

    public function conciliacoes(): void
    {
        $conciliacoes = $this->model->getConciliacoes();
        $this->renderView('contabil/conciliacoes', [
            'pageTitle' => 'Conciliação Bancária',
            'conciliacoes' => $conciliacoes,
        ]);
    }

    public function conciliacaoForm(?int $id = null): void
    {
        $this->bancoModel = $this->bancoModel ?? new BancoModel();
        $bancos = $this->bancoModel->getAll();

        $conciliacao = null;
        $itens = [];
        if ($id) {
            $conciliacao = $this->model->getConciliacaoById($id);
            $itens = $this->model->getItensConciliacao($id);
            if (!$conciliacao) {
                $this->setFlashMessage('error', 'Conciliação não encontrada.');
                header('Location: ' . BASE_URL . '/contabil/conciliacoes');
                exit();
            }
        }

        $this->renderView('contabil/conciliacao_form', [
            'pageTitle' => $id ? 'Editar Conciliação' : 'Nova Conciliação Bancária',
            'conciliacao' => $conciliacao,
            'bancos' => $bancos,
            'itens' => $itens,
        ]);
    }

    public function salvarConciliacao(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/contabil/conciliacoes');
            exit();
        }

        $dados = [
            'id' => filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: null,
            'banco_id' => filter_input(INPUT_POST, 'banco_id', FILTER_VALIDATE_INT),
            'periodo_inicio' => filter_input(INPUT_POST, 'periodo_inicio'),
            'periodo_fim' => filter_input(INPUT_POST, 'periodo_fim'),
            'saldo_extrato' => (float)(filter_input(INPUT_POST, 'saldo_extrato', FILTER_VALIDATE_FLOAT) ?: 0),
            'saldo_sistema' => (float)(filter_input(INPUT_POST, 'saldo_sistema', FILTER_VALIDATE_FLOAT) ?: 0),
            'diferenca' => 0,
            'status' => 'aberta',
            'observacoes' => filter_input(INPUT_POST, 'observacoes', FILTER_SANITIZE_SPECIAL_CHARS) ?: null,
            'usuario_id' => $this->session->get('user_id'),
        ];
        $dados['diferenca'] = $dados['saldo_extrato'] - $dados['saldo_sistema'];

        if (empty($dados['banco_id']) || empty($dados['periodo_inicio']) || empty($dados['periodo_fim'])) {
            $this->setFlashMessage('error', 'Banco, período inicial e final são obrigatórios.');
            header('Location: ' . BASE_URL . '/contabil/conciliacaoForm/' . ($dados['id'] ?? ''));
            exit();
        }

        $id = $this->model->salvarConciliacao($dados);

        if ($id) {
            if (!$dados['id']) {
                $transacoes = $this->model->getTransacoesParaConciliacao(
                    $dados['banco_id'], $dados['periodo_inicio'], $dados['periodo_fim']
                );
                foreach ($transacoes as $t) {
                    $this->model->adicionarItemConciliacao([
                        'conciliacao_id' => $id,
                        'transacao_id' => $t['id'],
                        'tipo' => 'sistema',
                        'data_operacao' => $t['data_pagamento'] ?? $t['vencimento'],
                        'descricao' => $t['descricao'],
                        'valor' => $t['valor'],
                    ]);
                }
            }
            $this->logAction('RECONCILE', 'Conciliação bancária #' . $id, 'Contábil', $id);
            $this->setFlashMessage('success', 'Conciliação salva com sucesso!');
        } else {
            $this->setFlashMessage('error', 'Erro ao salvar conciliação.');
        }

        header('Location: ' . BASE_URL . '/contabil/conciliacoes');
        exit();
    }

    public function verConciliacao(int $id): void
    {
        $conciliacao = $this->model->getConciliacaoById($id);
        $itens = $this->model->getItensConciliacao($id);

        if (!$conciliacao) {
            $this->setFlashMessage('error', 'Conciliação não encontrada.');
            header('Location: ' . BASE_URL . '/contabil/conciliacoes');
            exit();
        }

        $this->renderView('contabil/conciliacao_view', [
            'pageTitle' => 'Conciliação Bancária',
            'conciliacao' => $conciliacao,
            'itens' => $itens,
        ]);
    }

    public function conciliarItem(int $id): void
    {
        if ($this->model->conciliarItem($id)) {
            $this->setFlashMessage('success', 'Item conciliado com sucesso.');
        } else {
            $this->setFlashMessage('error', 'Erro ao conciliar item.');
        }
        header('Location: ' . $_SERVER['HTTP_REFERER'] ?? BASE_URL . '/contabil/conciliacoes');
        exit();
    }

    public function finalizarConciliacao(int $id): void
    {
        if ($this->model->finalizarConciliacao($id)) {
            $this->logAction('RECONCILE', 'Conciliação finalizada #' . $id, 'Contábil', $id);
            $this->setFlashMessage('success', 'Conciliação finalizada com sucesso!');
        } else {
            $this->setFlashMessage('error', 'Erro ao finalizar conciliação.');
        }
        header('Location: ' . BASE_URL . '/contabil/conciliacoes');
        exit();
    }

    // ========================
    // PARÂMETROS CONTÁBEIS
    // ========================

    public function parametros(): void
    {
        $parametros = $this->model->getParametros();
        $this->renderView('contabil/parametros', [
            'pageTitle' => 'Parâmetros Contábeis',
            'parametros' => $parametros,
        ]);
    }

    public function salvarParametros(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/contabil/parametros');
            exit();
        }

        $parametros = $_POST['parametros'] ?? [];

        if ($this->model->salvarParametros($parametros)) {
            $this->logAction('UPDATE', 'Parâmetros contábeis atualizados', 'Contábil');
            $this->setFlashMessage('success', 'Parâmetros contábeis salvos com sucesso!');
        } else {
            $this->setFlashMessage('error', 'Erro ao salvar parâmetros.');
        }

        header('Location: ' . BASE_URL . '/contabil/parametros');
        exit();
    }
}
