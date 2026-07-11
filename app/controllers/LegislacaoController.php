<?php

namespace App\Controllers;

use App\Models\LegislacaoModel;

class LegislacaoController extends BaseController
{
    protected $requiredPermissions = [
        'index'            => 'legislacao_view',
        'listar'           => 'legislacao_view',
        'form'             => 'legislacao_manage',
        'salvar'           => 'legislacao_manage',
        'excluir'          => 'legislacao_manage',
        'vigente'          => 'legislacao_view',
        'proximas'         => 'legislacao_view',
        'log'              => 'legislacao_view',
        'importar'         => 'legislacao_manage',
    ];

    private LegislacaoModel $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new LegislacaoModel();
    }

    public function index(): void
    {
        $modulo = filter_input(INPUT_GET, 'modulo', FILTER_SANITIZE_SPECIAL_CHARS);
        $versoes = $this->model->getVersoes($modulo);
        $this->renderView('legislacao/index', [
            'pageTitle' => 'Legislação e Versões',
            'versoes' => $versoes,
            'moduloAtual' => $modulo,
            'modulos' => $this->model->getModulosLegislacao(),
        ]);
    }

    public function listar(): void
    {
        $this->index();
    }

    public function form(?int $id = null): void
    {
        $versao = $id ? $this->model->getVersaoById($id) : null;
        $this->renderView('legislacao/form', [
            'pageTitle' => $id ? 'Editar Versão' : 'Nova Versão Legislativa',
            'versao' => $versao,
            'modulos' => $this->model->getModulosLegislacao(),
            'tiposAto' => $this->model->getTiposAto(),
        ]);
    }

    public function salvar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/legislacao');
            exit();
        }

        $dados = [
            'id' => filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: null,
            'modulo' => filter_input(INPUT_POST, 'modulo', FILTER_SANITIZE_SPECIAL_CHARS),
            'titulo' => filter_input(INPUT_POST, 'titulo', FILTER_SANITIZE_SPECIAL_CHARS),
            'descricao' => filter_input(INPUT_POST, 'descricao', FILTER_SANITIZE_SPECIAL_CHARS) ?: null,
            'tipo_ato' => filter_input(INPUT_POST, 'tipo_ato', FILTER_SANITIZE_SPECIAL_CHARS) ?: null,
            'numero_ato' => filter_input(INPUT_POST, 'numero_ato', FILTER_SANITIZE_SPECIAL_CHARS) ?: null,
            'orgao_emissor' => filter_input(INPUT_POST, 'orgao_emissor', FILTER_SANITIZE_SPECIAL_CHARS) ?: null,
            'data_publicacao' => filter_input(INPUT_POST, 'data_publicacao'),
            'data_vigencia' => filter_input(INPUT_POST, 'data_vigencia'),
            'data_revogacao' => filter_input(INPUT_POST, 'data_revogacao'),
            'arquivo_anexo' => filter_input(INPUT_POST, 'arquivo_anexo', FILTER_SANITIZE_SPECIAL_CHARS) ?: null,
            'resumo_mudancas' => filter_input(INPUT_POST, 'resumo_mudancas', FILTER_SANITIZE_SPECIAL_CHARS) ?: null,
            'impacto_esperado' => filter_input(INPUT_POST, 'impacto_esperado', FILTER_SANITIZE_SPECIAL_CHARS) ?: null,
            'versao' => filter_input(INPUT_POST, 'versao', FILTER_SANITIZE_SPECIAL_CHARS) ?: null,
            'obrigatorio' => isset($_POST['obrigatorio']) ? 1 : 0,
        ];

        if (empty($dados['modulo']) || empty($dados['titulo'])) {
            $this->setFlashMessage('error', 'Módulo e título são obrigatórios.');
            header('Location: ' . BASE_URL . '/legislacao/form/' . ($dados['id'] ?? ''));
            exit();
        }

        if ($this->model->salvarVersao($dados)) {
            $this->logAction('LEGISLACAO', 'Versão legislativa salva: ' . $dados['titulo'], 'Fiscal', $dados['id']);
            $this->model->registrarAtualizacao('legislacao', $dados['id'] ? 'atualizar' : 'criar', 'Versão legislativa salva', $dados['id'], null, $dados, $this->session->get('user_id'));
            $this->setFlashMessage('success', 'Versão salva com sucesso!');
        } else {
            $this->setFlashMessage('error', 'Erro ao salvar versão.');
        }

        header('Location: ' . BASE_URL . '/legislacao');
        exit();
    }

    public function excluir(int $id): void
    {
        if ($this->model->excluirVersao($id)) {
            $this->logAction('LEGISLACAO', 'Versão excluída #' . $id, 'Fiscal', $id);
            $this->setFlashMessage('success', 'Versão excluída.');
        } else {
            $this->setFlashMessage('error', 'Erro ao excluir versão.');
        }
        header('Location: ' . BASE_URL . '/legislacao');
        exit();
    }

    public function vigente(string $modulo): void
    {
        $versao = $this->model->getVersaoVigente($modulo);
        $this->renderView('legislacao/vigente', [
            'pageTitle' => 'Legislação Vigente - ' . ($this->model->getModulosLegislacao()[$modulo] ?? $modulo),
            'versao' => $versao,
            'modulo' => $modulo,
            'modulos' => $this->model->getModulosLegislacao(),
        ]);
    }

    public function proximas(): void
    {
        $dias = filter_input(INPUT_GET, 'dias', FILTER_VALIDATE_INT) ?: 60;
        $alteracoes = $this->model->getProximasAlteracoes($dias);
        $this->renderView('legislacao/proximas', [
            'pageTitle' => 'Próximas Alterações Legislativas',
            'alteracoes' => $alteracoes,
            'dias' => $dias,
        ]);
    }

    public function log(): void
    {
        $tipo = filter_input(INPUT_GET, 'tipo', FILTER_SANITIZE_SPECIAL_CHARS);
        $registros = $this->model->getLogAtualizacoes($tipo);
        $this->renderView('legislacao/log', [
            'pageTitle' => 'Log de Atualizações',
            'registros' => $registros,
            'tipos' => ['regra', 'obrigacao', 'legislacao', 'relatorio'],
        ]);
    }

    public function importar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $modulo = filter_input(INPUT_POST, 'modulo', FILTER_SANITIZE_SPECIAL_CHARS);
            $dadosJson = filter_input(INPUT_POST, 'dados_json');

            if ($modulo && $dadosJson) {
                $itens = json_decode($dadosJson, true);
                if (is_array($itens)) {
                    $importados = 0;
                    foreach ($itens as $item) {
                        $item['modulo'] = $modulo;
                        if ($this->model->salvarVersao($item)) {
                            $importados++;
                        }
                    }
                    $this->logAction('LEGISLACAO', "Importação: $importados registros de $modulo", 'Fiscal');
                    $this->model->registrarAtualizacao('legislacao', 'importar', "Importados $importados registros de $modulo via JSON", null, null, ['modulo' => $modulo, 'quantidade' => $importados], $this->session->get('user_id'), 'manual');
                    $this->setFlashMessage('success', "$importados registros importados!");
                } else {
                    $this->setFlashMessage('error', 'JSON inválido.');
                }
            }
            header('Location: ' . BASE_URL . '/legislacao');
            exit();
        }

        $this->renderView('legislacao/importar', [
            'pageTitle' => 'Importar Legislação',
            'modulos' => $this->model->getModulosLegislacao(),
        ]);
    }
}
