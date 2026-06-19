<?php

namespace App\Controllers;

use App\Models\JuridicoModel;
use App\Models\ClientesModel;

/**
 * Controller responsável por gerenciar as operações do módulo Jurídico.
 */
class JuridicoController extends BaseController
{
    private $model;

    /**
     * Mapeia as ações para as permissões de acesso necessárias.
     */
    protected $requiredPermissions = [
        'index'     => 'juridico_dashboard_view',
        'dashboard' => 'juridico_dashboard_view',
        'processos' => 'juridico_processos_view',
        'novo'      => 'juridico_processos_manage',
        'detalhe'   => 'juridico_processos_view',
        'salvar'    => 'juridico_processos_manage',
        'editar'    => 'juridico_processos_manage',
        'excluir'   => 'juridico_processos_manage',
    ];

    public function __construct()
    {
        parent::__construct();
        $this->model = new JuridicoModel();
    }

    /**
     * Ponto de entrada padrão que redireciona para o Dashboard.
     */
    public function index()
    {
        return $this->dashboard();
    }

    /**
     * Exibe o Dashboard Jurídico.
     * Resolve o erro 404 da ação 'dashboard'.
     */
    public function dashboard()
    {
        $tipoFiltro = filter_input(INPUT_GET, 'tipo', FILTER_SANITIZE_SPECIAL_CHARS) ?: 'todos';
        
        $prazosProximos = $this->model->getPrazosProximos(7);
        $prazosHoje = 0;
        foreach ($prazosProximos as $p) {
            if ($p['data_prazo'] === date('Y-m-d')) $prazosHoje++;
        }

        $data = [
            'pageTitle'            => 'Dashboard Jurídico',
            'kpis'                 => $this->model->getDashboardKpis(),
            'prazosProximos'       => $prazosProximos,
            'prazosHoje'           => $prazosHoje,
            'processos'            => $this->model->getProcessos(['status' => 'Ativo']),
            'distribuicaoTipos'    => $this->model->getDistribuicaoPorTipo(),
            'responsaveis'         => $this->model->getResponsaveisCarga(),
            'andamentosRecentes'   => $this->model->getAndamentosRecentes(),
            'audiencias'           => $this->model->getAudienciasProximas(),
            'dataAtualizacao'      => date('Y-m-d H:i:s'),
            'tipoFiltro'           => $tipoFiltro
        ];

        // Renderiza a view 'index' dentro da pasta 'juridico'
        $this->renderView('juridico/index', $data);
    }

    /**
     * Exibe a listagem de processos.
     */
    public function processos()
    {
        $filtros = [
            'busca' => filter_input(INPUT_GET, 'busca', FILTER_SANITIZE_SPECIAL_CHARS),
            'status' => filter_input(INPUT_GET, 'status', FILTER_SANITIZE_SPECIAL_CHARS),
        ];

        $paginaAtual = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1;
        $itensPorPagina = 10;
        $offset = ($paginaAtual - 1) * $itensPorPagina;

        $processos = $this->model->getProcessos($filtros, $itensPorPagina, $offset);
        $totalRegistros = $this->model->getProcessosCount($filtros);
        $totalPaginas = ceil($totalRegistros / $itensPorPagina);

        $data = [
            'pageTitle'    => 'Listagem de Processos',
            'processos'    => $processos,
            'filtros'      => $filtros,
            'paginaAtual'  => $paginaAtual,
            'totalPaginas' => $totalPaginas
        ];

        $this->renderView('juridico/processos', $data);
    }

    /**
     * Exibe o formulário para cadastro de um novo processo.
     */
    public function novo()
    {
        $clientesModel = new ClientesModel();
        $data = [
            'pageTitle' => 'Novo Processo Jurídico',
            'proc'      => null, // Padrão 'proc' para a view
            'clientes'  => $clientesModel->getAllClientes(),
            'csrf_token' => $this->generateCsrfToken()
        ];
        $this->renderView('juridico/form', $data);
    }

    /**
     * Exibe os detalhes de um processo jurídico específico.
     * @param int $id O ID do processo.
     */
    public function detalhe($id)
    {
        $id = (int)$id;
        $processo = $this->model->getProcessoById($id);

        if (!$processo) {
            $this->setFlashMessage('error', 'Processo não encontrado.');
            header('Location: ' . BASE_URL . '/juridico/processos');
            exit();
        }

        $data = [
            'pageTitle' => 'Detalhes do Processo: ' . $processo['numero'],
            'proc'      => $processo
        ];

        $this->renderView('juridico/detalhe', $data);
    }

    /**
     * Exibe o formulário de edição de um processo.
     */
    public function editar($id)
    {
        $id = (int)$id;
        $processo = $this->model->getProcessoById($id);

        if (!$processo) {
            $this->setFlashMessage('error', 'Processo não encontrado.');
            header('Location: ' . BASE_URL . '/juridico/processos');
            exit();
        }

        $clientesModel = new \App\Models\ClientesModel();
        $data = [
            'pageTitle' => 'Editar Processo Jurídico',
            'proc'      => $processo,
            'clientes'  => $clientesModel->getAllClientes(),
            'csrf_token' => $this->generateCsrfToken()
        ];

        $this->renderView('juridico/form', $data);
    }

    /**
     * Processa o salvamento de um processo jurídico.
     */
    public function salvar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/juridico/processos');
            exit();
        }

        if (!isset($_POST['csrf_token']) || !$this->validateCsrfToken($_POST['csrf_token'])) {
            $this->setFlashMessage('error', 'Erro de validação de segurança (CSRF).');
            header('Location: ' . BASE_URL . '/juridico/processos');
            exit();
        }

        // Sanitização sênior dos inputs
        $dados = [
            'id'                => filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: null,
            'numero_cnj'        => filter_input(INPUT_POST, 'numero', FILTER_SANITIZE_SPECIAL_CHARS),
            'tipo'              => filter_input(INPUT_POST, 'tipo', FILTER_SANITIZE_SPECIAL_CHARS),
            'status'            => filter_input(INPUT_POST, 'status', FILTER_SANITIZE_SPECIAL_CHARS) ?: 'Ativo',
            'fase'              => filter_input(INPUT_POST, 'fase', FILTER_SANITIZE_SPECIAL_CHARS) ?: 'Inicial',
            'cliente_id'        => filter_input(INPUT_POST, 'cliente_id', FILTER_VALIDATE_INT) ?: null,
            'parte_contraria'   => filter_input(INPUT_POST, 'parte_contraria', FILTER_SANITIZE_SPECIAL_CHARS),
            'tribunal'          => filter_input(INPUT_POST, 'tribunal', FILTER_SANITIZE_SPECIAL_CHARS),
            'vara_camara'       => filter_input(INPUT_POST, 'vara_camara', FILTER_SANITIZE_SPECIAL_CHARS),
            'objeto'            => filter_input(INPUT_POST, 'objeto', FILTER_SANITIZE_SPECIAL_CHARS),
            'data_distribuicao' => filter_input(INPUT_POST, 'data_distribuicao'),
            'observacoes'       => filter_input(INPUT_POST, 'observacoes', FILTER_SANITIZE_SPECIAL_CHARS),
        ];

        // Tratamento de valor monetário (ex: 1.250,50 -> 1250.50)
        $valorRaw = $_POST['valor_causa'] ?? '0';
        $valorRaw = str_replace('.', '', $valorRaw);
        $valorRaw = str_replace(',', '.', $valorRaw);
        $dados['valor_causa'] = (float)$valorRaw;

        if ($this->model->salvarProcesso($dados)) {
            $id = $dados['id'];
            $this->setFlashMessage('success', $id ? 'Processo atualizado com sucesso!' : 'Processo cadastrado com sucesso!');
        } else {
            $this->setFlashMessage('error', 'Ocorreu um erro ao salvar o processo.');
        }

        header('Location: ' . BASE_URL . '/juridico/processos');
        exit();
    }

    /**
     * Processa a exclusão de um processo jurídico.
     * Exige requisição POST e token CSRF válido.
     * @param int $id O ID do processo.
     */
    public function excluir($id)
    {
        // Segurança: Impede exclusão via GET
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->setFlashMessage('error', 'Operação inválida.');
            header('Location: ' . BASE_URL . '/juridico/processos');
            exit();
        }

        // Validação de CSRF
        if (!isset($_POST['csrf_token']) || !$this->validateCsrfToken($_POST['csrf_token'])) {
            $this->setFlashMessage('error', 'Erro de segurança (CSRF).');
            header('Location: ' . BASE_URL . '/juridico/processos');
            exit();
        }

        $id = (int)$id;
        if ($id > 0 && $this->model->excluirProcesso($id)) {
            $this->setFlashMessage('success', 'Processo excluído com sucesso!');
        } else {
            $this->setFlashMessage('error', 'Ocorreu um erro ao excluir o processo.');
        }

        header('Location: ' . BASE_URL . '/juridico/processos');
        exit();
    }
}