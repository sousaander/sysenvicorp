<?php

namespace App\Controllers;

use App\Models\RelatorioModel;
use App\Models\LegislacaoModel;

class RelatorioController extends BaseController
{
    protected $requiredPermissions = [
        'index'   => 'relatorios_view',
        'listar'  => 'relatorios_view',
        'form'    => 'relatorios_manage',
        'salvar'  => 'relatorios_manage',
        'excluir' => 'relatorios_manage',
        'preview' => 'relatorios_view',
        'exportar' => 'relatorios_export',
        'executar' => 'relatorios_export',
    ];

    private RelatorioModel $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new RelatorioModel();
    }

    public function index(): void
    {
        $modulo = filter_input(INPUT_GET, 'modulo', FILTER_SANITIZE_SPECIAL_CHARS);
        $modelos = $this->model->getModelos($modulo);
        $this->renderView('relatorios/index', [
            'pageTitle' => 'Modelos de Relatórios',
            'modelos' => $modelos,
            'moduloAtual' => $modulo,
            'modulos' => $this->model->getModulosDisponiveis(),
        ]);
    }

    public function listar(): void
    {
        $this->index();
    }

    public function form(?int $id = null): void
    {
        $modelo = $id ? $this->model->getModeloById($id) : null;
        $colunas = [];
        if ($modelo) {
            $colunas = $this->model->getColunasDisponiveis($modelo['modulo']);
        }

        $this->renderView('relatorios/form', [
            'pageTitle' => $id ? 'Editar Modelo' : 'Novo Modelo de Relatório',
            'modelo' => $modelo,
            'modulos' => $this->model->getModulosDisponiveis(),
            'colunasDisponiveis' => $colunas,
        ]);
    }

    public function salvar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/relatorios');
            exit();
        }

        $modulo = filter_input(INPUT_POST, 'modulo', FILTER_SANITIZE_SPECIAL_CHARS);
        $colunasSelecionadas = $_POST['colunas'] ?? [];

        $config = [
            'colunas' => $colunasSelecionadas,
            'filtros' => [
                'periodo_padrao' => filter_input(INPUT_POST, 'periodo_padrao', FILTER_SANITIZE_SPECIAL_CHARS) ?: 'mensal',
                'ordenar_por' => filter_input(INPUT_POST, 'ordenar_por', FILTER_SANITIZE_SPECIAL_CHARS) ?: 'data',
                'ordenacao' => filter_input(INPUT_POST, 'ordenacao', FILTER_SANITIZE_SPECIAL_CHARS) ?: 'DESC',
            ],
            'agrupamento' => filter_input(INPUT_POST, 'agrupamento', FILTER_SANITIZE_SPECIAL_CHARS) ?: null,
            'limite' => (int)(filter_input(INPUT_POST, 'limite', FILTER_VALIDATE_INT) ?: 500),
        ];

        $dados = [
            'id' => filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: null,
            'nome' => filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_SPECIAL_CHARS),
            'descricao' => filter_input(INPUT_POST, 'descricao', FILTER_SANITIZE_SPECIAL_CHARS) ?: null,
            'modulo' => $modulo,
            'tipo' => filter_input(INPUT_POST, 'tipo', FILTER_SANITIZE_SPECIAL_CHARS) ?: 'personalizado',
            'config' => json_encode($config),
            'colunas_personalizadas' => implode(',', $colunasSelecionadas),
            'parametros_personalizados' => null,
            'rodape' => filter_input(INPUT_POST, 'rodape', FILTER_SANITIZE_SPECIAL_CHARS) ?: null,
            'orientacao' => filter_input(INPUT_POST, 'orientacao', FILTER_SANITIZE_SPECIAL_CHARS) ?: 'retrato',
            'formato_padrao' => filter_input(INPUT_POST, 'formato_padrao', FILTER_SANITIZE_SPECIAL_CHARS) ?: 'pdf',
            'ativo' => isset($_POST['ativo']) ? 1 : 0,
            'criado_por' => $this->session->get('user_id'),
        ];

        if ($this->model->salvarModelo($dados)) {
            $this->logAction('RELATORIO', 'Modelo salvo: ' . $dados['nome'], 'Relatórios', $dados['id']);
            $labelModel = new LegislacaoModel();
            $labelModel->registrarAtualizacao('relatorio', $dados['id'] ? 'atualizar' : 'criar', 'Modelo de relatório salvo', $dados['id']);
            $this->setFlashMessage('success', 'Modelo de relatório salvo!');
        } else {
            $this->setFlashMessage('error', 'Erro ao salvar modelo.');
        }

        header('Location: ' . BASE_URL . '/relatorios');
        exit();
    }

    public function excluir(int $id): void
    {
        if ($this->model->excluirModelo($id)) {
            $this->logAction('RELATORIO', 'Modelo excluído #' . $id, 'Relatórios', $id);
            $this->setFlashMessage('success', 'Modelo excluído.');
        } else {
            $this->setFlashMessage('error', 'Erro ao excluir modelo.');
        }
        header('Location: ' . BASE_URL . '/relatorios');
        exit();
    }

    public function preview(int $id): void
    {
        $modelo = $this->model->getModeloById($id);
        if (!$modelo) {
            $this->setFlashMessage('error', 'Modelo não encontrado.');
            header('Location: ' . BASE_URL . '/relatorios');
            exit();
        }

        $filtros = [
            'data_inicio' => filter_input(INPUT_GET, 'data_inicio'),
            'data_fim' => filter_input(INPUT_GET, 'data_fim'),
            'categoria' => filter_input(INPUT_GET, 'categoria', FILTER_SANITIZE_SPECIAL_CHARS),
            'origem' => filter_input(INPUT_GET, 'origem', FILTER_SANITIZE_SPECIAL_CHARS),
            'tipo' => filter_input(INPUT_GET, 'tipo', FILTER_SANITIZE_SPECIAL_CHARS),
            'status' => filter_input(INPUT_GET, 'status', FILTER_SANITIZE_SPECIAL_CHARS),
        ];

        $dados = $this->model->executarRelatorio($id, $filtros);

        $this->renderView('relatorios/preview', [
            'pageTitle' => 'Preview: ' . $modelo['nome'],
            'modelo' => $modelo,
            'dados' => $dados,
            'config' => json_decode($modelo['config'], true) ?: [],
            'filtros' => $filtros,
        ]);
    }

    public function executar(): void
    {
        $modeloId = filter_input(INPUT_POST, 'modelo_id', FILTER_VALIDATE_INT)
                 ?? filter_input(INPUT_GET, 'modelo_id', FILTER_VALIDATE_INT);
        if (!$modeloId) {
            $this->setFlashMessage('error', 'Modelo não especificado.');
            header('Location: ' . BASE_URL . '/relatorios');
            exit();
        }

        $filtros = [
            'data_inicio' => $_POST['data_inicio'] ?? $_GET['data_inicio'] ?? null,
            'data_fim' => $_POST['data_fim'] ?? $_GET['data_fim'] ?? null,
            'categoria' => $_POST['categoria'] ?? $_GET['categoria'] ?? null,
            'origem' => $_POST['origem'] ?? $_GET['origem'] ?? null,
            'tipo' => $_POST['tipo'] ?? $_GET['tipo'] ?? null,
            'status' => $_POST['status'] ?? $_GET['status'] ?? null,
        ];

        header('Location: ' . BASE_URL . '/relatorios/preview/' . $modeloId . '?' . http_build_query(array_filter($filtros)));
        exit();
    }

    public function exportar(int $id): void
    {
        $modelo = $this->model->getModeloById($id);
        if (!$modelo) {
            $this->setFlashMessage('error', 'Modelo não encontrado.');
            header('Location: ' . BASE_URL . '/relatorios');
            exit();
        }

        $formatosPermitidos = ['csv', 'pdf', 'xlsx'];
        $formato = filter_input(INPUT_GET, 'formato', FILTER_SANITIZE_SPECIAL_CHARS) ?: 'csv';
        if (!in_array($formato, $formatosPermitidos)) $formato = 'csv';

        $dados = $this->model->executarRelatorio($id, $_GET);
        $config = json_decode($modelo['config'], true) ?: [];
        $colunas = $config['colunas'] ?? [];

        if ($formato === 'csv') {
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $this->sanitizeFilename($modelo['nome']) . '.csv"');
            $output = fopen('php://output', 'w');
            fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

            if (!empty($colunas)) {
                fputcsv($output, $colunas, ';');
            } elseif (!empty($dados)) {
                fputcsv($output, array_keys($dados[0]), ';');
            }

            foreach ($dados as $row) {
                fputcsv($output, $row, ';');
            }
            fclose($output);
            exit();
        }

        $this->setFlashMessage('info', 'Formato ' . strtoupper($formato) . ' em desenvolvimento.');
        header('Location: ' . BASE_URL . '/relatorios/preview/' . $id);
        exit();
    }

    private function sanitizeFilename(string $name): string
    {
        $name = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $name);
        return substr($name, 0, 50);
    }
}
