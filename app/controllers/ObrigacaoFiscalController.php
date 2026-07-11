<?php

namespace App\Controllers;

use App\Models\ObrigacaoFiscalModel;
use App\Models\LegislacaoModel;

class ObrigacaoFiscalController extends BaseController
{
    protected $requiredPermissions = [
        'index'            => 'obrigacoes_fiscais_view',
        'listar'           => 'obrigacoes_fiscais_view',
        'form'             => 'obrigacoes_fiscais_manage',
        'salvar'           => 'obrigacoes_fiscais_manage',
        'excluir'          => 'obrigacoes_fiscais_manage',
        'calendario'       => 'obrigacoes_fiscais_view',
        'gerarCalendario'  => 'obrigacoes_fiscais_manage',
        'atualizarStatus'  => 'obrigacoes_fiscais_manage',
        'alertas'          => 'obrigacoes_fiscais_view',
        'marcarLido'       => 'obrigacoes_fiscais_view',
        'gerarAlertas'     => 'obrigacoes_fiscais_manage',
        'dashboard'        => 'obrigacoes_fiscais_view',
    ];

    private ObrigacaoFiscalModel $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new ObrigacaoFiscalModel();
    }

    public function index(): void
    {
        $obrigacoes = $this->model->getObrigacoes();
        $this->renderView('obrigacoes_fiscais/index', [
            'pageTitle' => 'Obrigações Fiscais',
            'obrigacoes' => $obrigacoes,
        ]);
    }

    public function listar(): void
    {
        $this->index();
    }

    public function form(?int $id = null): void
    {
        $obrigacao = $id ? $this->model->getObrigacaoById($id) : null;
        $this->renderView('obrigacoes_fiscais/form', [
            'pageTitle' => $id ? 'Editar Obrigação' : 'Nova Obrigação Fiscal',
            'obrigacao' => $obrigacao,
        ]);
    }

    public function salvar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/obrigacoesFiscais');
            exit();
        }

        $dados = [
            'id' => filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: null,
            'nome' => filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_SPECIAL_CHARS),
            'descricao' => filter_input(INPUT_POST, 'descricao', FILTER_SANITIZE_SPECIAL_CHARS) ?: null,
            'orgao' => filter_input(INPUT_POST, 'orgao', FILTER_SANITIZE_SPECIAL_CHARS),
            'periodicidade' => filter_input(INPUT_POST, 'periodicidade', FILTER_SANITIZE_SPECIAL_CHARS),
            'dia_vencimento' => (int)filter_input(INPUT_POST, 'dia_vencimento', FILTER_VALIDATE_INT),
            'mes_referencia' => filter_input(INPUT_POST, 'mes_referencia', FILTER_VALIDATE_INT) ?: null,
            'forma_entrega' => filter_input(INPUT_POST, 'forma_entrega', FILTER_SANITIZE_SPECIAL_CHARS) ?: null,
            'base_legal' => filter_input(INPUT_POST, 'base_legal', FILTER_SANITIZE_SPECIAL_CHARS) ?: null,
            'obrigatorio' => isset($_POST['obrigatorio']) ? 1 : 0,
            'ativo' => isset($_POST['ativo']) ? 1 : 0,
        ];

        if (empty($dados['nome']) || $dados['dia_vencimento'] < 1 || $dados['dia_vencimento'] > 31) {
            $this->setFlashMessage('error', 'Nome e dia de vencimento (1-31) são obrigatórios.');
            header('Location: ' . BASE_URL . '/obrigacoesFiscais/form/' . ($dados['id'] ?? ''));
            exit();
        }

        if ($this->model->salvarObrigacao($dados)) {
            $this->logAction('OBRIGACOES_FISCAIS', 'Obrigação salva: ' . $dados['nome'], 'Fiscal', $dados['id']);
            $labelModel = new LegislacaoModel();
            $labelModel->registrarAtualizacao('obrigacao', $dados['id'] ? 'atualizar' : 'criar', 'Obrigação fiscal salva', $dados['id']);
            $this->setFlashMessage('success', 'Obrigação fiscal salva!');
        } else {
            $this->setFlashMessage('error', 'Erro ao salvar obrigação.');
        }

        header('Location: ' . BASE_URL . '/obrigacoesFiscais');
        exit();
    }

    public function excluir(int $id): void
    {
        if ($this->model->excluirObrigacao($id)) {
            $this->logAction('OBRIGACOES_FISCAIS', 'Obrigação excluída #' . $id, 'Fiscal', $id);
            $this->setFlashMessage('success', 'Obrigação excluída.');
        } else {
            $this->setFlashMessage('error', 'Erro ao excluir obrigação.');
        }
        header('Location: ' . BASE_URL . '/obrigacoesFiscais');
        exit();
    }

    public function calendario(): void
    {
        $ano = filter_input(INPUT_GET, 'ano', FILTER_VALIDATE_INT) ?: (int)date('Y');
        $mes = filter_input(INPUT_GET, 'mes', FILTER_VALIDATE_INT) ?: null;
        $orgao = filter_input(INPUT_GET, 'orgao', FILTER_SANITIZE_SPECIAL_CHARS) ?: null;

        $calendario = $this->model->getCalendario($ano, $mes, $orgao);
        $resumo = $this->model->getResumo();

        $this->renderView('obrigacoes_fiscais/calendario', [
            'pageTitle' => 'Calendário Fiscal - ' . $ano,
            'calendario' => $calendario,
            'resumo' => $resumo,
            'ano' => $ano,
            'mes' => $mes,
            'orgao' => $orgao,
        ]);
    }

    public function gerarCalendario(): void
    {
        $ano = filter_input(INPUT_POST, 'ano', FILTER_VALIDATE_INT) ?: (int)date('Y');
        $gerados = $this->model->gerarCalendario($ano);
        $this->logAction('CALENDARIO_FISCAL', "Calendário $ano gerado: $gerados períodos", 'Fiscal');
        $this->setFlashMessage('success', "$gerados períodos gerados para $ano.");
        header('Location: ' . BASE_URL . '/obrigacoesFiscais/calendario?ano=' . $ano);
        exit();
    }

    public function atualizarStatus(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/obrigacoesFiscais/calendario');
            exit();
        }

        $id = (int)filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_SPECIAL_CHARS);
        $observacoes = filter_input(INPUT_POST, 'observacoes', FILTER_SANITIZE_SPECIAL_CHARS) ?: null;

        if ($id && in_array($status, ['pendente', 'entregue', 'atrasado', 'dispensado'])) {
            $this->model->atualizarStatusCalendario($id, $status, $observacoes, $this->session->get('user_id'));
            $this->logAction('CALENDARIO_FISCAL', "Status calendario #$id atualizado para $status", 'Fiscal', $id);
            $this->setFlashMessage('success', 'Status atualizado.');
        }

        header('Location: ' . BASE_URL . '/obrigacoesFiscais/calendario');
        exit();
    }

    public function alertas(): void
    {
        $this->model->gerarAlertasVencimento();
        $alertas = $this->model->getAlertas($this->session->get('user_id'));

        $this->renderView('obrigacoes_fiscais/alertas', [
            'pageTitle' => 'Alertas Fiscais',
            'alertas' => $alertas,
        ]);
    }

    public function marcarLido(int $id): void
    {
        $this->model->marcarAlertaComoLido($id);
        header('Location: ' . BASE_URL . '/obrigacoesFiscais/alertas');
        exit();
    }

    public function gerarAlertas(): void
    {
        $gerados = $this->model->gerarAlertasVencimento();
        $this->setFlashMessage('success', "$gerados novos alertas gerados.");
        header('Location: ' . BASE_URL . '/obrigacoesFiscais/alertas');
        exit();
    }

    public function dashboard(): void
    {
        $resumo = $this->model->getResumo();
        $alertasRecentes = $this->model->getAlertas($this->session->get('user_id'), true);

        $this->renderView('obrigacoes_fiscais/dashboard', [
            'pageTitle' => 'Dashboard de Obrigações Fiscais',
            'resumo' => $resumo,
            'alertas' => $alertasRecentes,
        ]);
    }
}
