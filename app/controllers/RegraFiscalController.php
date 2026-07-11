<?php

namespace App\Controllers;

use App\Models\RegraFiscalModel;
use App\Models\LegislacaoModel;

class RegraFiscalController extends BaseController
{
    protected $requiredPermissions = [
        'index'       => 'regras_fiscais_view',
        'listar'      => 'regras_fiscais_view',
        'form'        => 'regras_fiscais_manage',
        'salvar'      => 'regras_fiscais_manage',
        'excluir'     => 'regras_fiscais_manage',
        'aplicar'     => 'regras_fiscais_manage',
        'historico'   => 'regras_fiscais_view',
    ];

    private RegraFiscalModel $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new RegraFiscalModel();
    }

    public function index(): void
    {
        $regras = $this->model->getRegras();
        $this->renderView('regras_fiscais/index', [
            'pageTitle' => 'Regras Fiscais',
            'regras' => $regras,
        ]);
    }

    public function listar(): void
    {
        $this->index();
    }

    public function form(?int $id = null): void
    {
        $regra = $id ? $this->model->getRegraById($id) : null;

        try {
            $produtos = [];
            if (class_exists(\App\Models\EstoqueModel::class)) {
                $estModel = new \App\Models\EstoqueModel();
                $produtos = $estModel->getProdutos();
            }
        } catch (\Throwable) {
            $produtos = [];
        }

        $this->renderView('regras_fiscais/form', [
            'pageTitle' => $id ? 'Editar Regra Fiscal' : 'Nova Regra Fiscal',
            'regra' => $regra,
            'produtos' => $produtos,
            'regimes' => $this->model->getRegimesTributarios(),
            'cfop' => $this->model->getOpcoesCFOP(),
            'cst' => $this->model->getOpcoesCST(),
            'csosn' => $this->model->getOpcoesCSOSN(),
            'pisCofins' => $this->model->getOpcoesPISCOFINS(),
        ]);
    }

    public function salvar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/regrasFiscais');
            exit();
        }

        $dados = [
            'id' => filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: null,
            'produto_id' => filter_input(INPUT_POST, 'produto_id', FILTER_VALIDATE_INT) ?: null,
            'tipo_entidade' => filter_input(INPUT_POST, 'tipo_entidade', FILTER_SANITIZE_SPECIAL_CHARS) ?: 'produto',
            'regime_tributario' => filter_input(INPUT_POST, 'regime_tributario', FILTER_SANITIZE_SPECIAL_CHARS) ?: null,
            'cfop' => filter_input(INPUT_POST, 'cfop', FILTER_SANITIZE_SPECIAL_CHARS) ?: null,
            'cst_icms' => filter_input(INPUT_POST, 'cst_icms', FILTER_SANITIZE_SPECIAL_CHARS) ?: null,
            'csosn' => filter_input(INPUT_POST, 'csosn', FILTER_SANITIZE_SPECIAL_CHARS) ?: null,
            'cst_ipi' => filter_input(INPUT_POST, 'cst_ipi', FILTER_SANITIZE_SPECIAL_CHARS) ?: null,
            'cst_pis' => filter_input(INPUT_POST, 'cst_pis', FILTER_SANITIZE_SPECIAL_CHARS) ?: null,
            'cst_cofins' => filter_input(INPUT_POST, 'cst_cofins', FILTER_SANITIZE_SPECIAL_CHARS) ?: null,
            'aliquota_icms' => (float)(filter_input(INPUT_POST, 'aliquota_icms', FILTER_VALIDATE_FLOAT) ?: 0),
            'aliquota_ipi' => (float)(filter_input(INPUT_POST, 'aliquota_ipi', FILTER_VALIDATE_FLOAT) ?: 0),
            'aliquota_pis' => (float)(filter_input(INPUT_POST, 'aliquota_pis', FILTER_VALIDATE_FLOAT) ?: 0),
            'aliquota_cofins' => (float)(filter_input(INPUT_POST, 'aliquota_cofins', FILTER_VALIDATE_FLOAT) ?: 0),
            'aliquota_iss' => (float)(filter_input(INPUT_POST, 'aliquota_iss', FILTER_VALIDATE_FLOAT) ?: 0),
            'reducao_base_icms' => (float)(filter_input(INPUT_POST, 'reducao_base_icms', FILTER_VALIDATE_FLOAT) ?: 0),
            'margem_st' => (float)(filter_input(INPUT_POST, 'margem_st', FILTER_VALIDATE_FLOAT) ?: 0),
            'base_calculo' => filter_input(INPUT_POST, 'base_calculo', FILTER_VALIDATE_FLOAT) ?: null,
            'enquadramento' => filter_input(INPUT_POST, 'enquadramento', FILTER_SANITIZE_SPECIAL_CHARS) ?: null,
            'ncm_obrigatorio' => isset($_POST['ncm_obrigatorio']) ? 1 : 0,
            'cest_obrigatorio' => isset($_POST['cest_obrigatorio']) ? 1 : 0,
            'beneficio_fiscal' => filter_input(INPUT_POST, 'beneficio_fiscal', FILTER_SANITIZE_SPECIAL_CHARS) ?: null,
            'uf_origem' => filter_input(INPUT_POST, 'uf_origem', FILTER_SANITIZE_SPECIAL_CHARS) ?: null,
            'uf_destino' => filter_input(INPUT_POST, 'uf_destino', FILTER_SANITIZE_SPECIAL_CHARS) ?: null,
            'data_vigencia_inicio' => filter_input(INPUT_POST, 'data_vigencia_inicio'),
            'data_vigencia_fim' => filter_input(INPUT_POST, 'data_vigencia_fim'),
            'ativo' => isset($_POST['ativo']) ? 1 : 0,
            'criado_por' => $this->session->get('user_id'),
        ];

        if ($this->model->salvarRegra($dados)) {
            $this->logAction('REGRAS_FISCAIS', 'Regra fiscal salva: ' . ($dados['cfop'] ?? 'N/A'), 'Fiscal');
            $labelModel = new LegislacaoModel();
            $labelModel->registrarAtualizacao('regra', $dados['id'] ? 'atualizar' : 'criar', 'Regra fiscal salva', $dados['id'], null, $dados, $this->session->get('user_id'));
            $this->setFlashMessage('success', 'Regra fiscal salva com sucesso!');
        } else {
            $this->setFlashMessage('error', 'Erro ao salvar regra fiscal.');
        }

        header('Location: ' . BASE_URL . '/regrasFiscais');
        exit();
    }

    public function excluir(int $id): void
    {
        if ($this->model->excluirRegra($id)) {
            $this->logAction('REGRAS_FISCAIS', 'Regra fiscal excluída #' . $id, 'Fiscal', $id);
            $this->setFlashMessage('success', 'Regra fiscal excluída.');
        } else {
            $this->setFlashMessage('error', 'Erro ao excluir regra fiscal.');
        }
        header('Location: ' . BASE_URL . '/regrasFiscais');
        exit();
    }

    public function aplicar(): void
    {
        $regraId = filter_input(INPUT_POST, 'regra_id', FILTER_VALIDATE_INT);
        $produtoId = filter_input(INPUT_POST, 'produto_id', FILTER_VALIDATE_INT);

        if ($regraId && $produtoId) {
            $regra = $this->model->getRegraById($regraId);
            if ($regra) {
                $regra['id'] = null;
                $regra['produto_id'] = $produtoId;
                if ($this->model->salvarRegra($regra)) {
                    $this->logAction('REGRAS_FISCAIS', "Regra #$regraId aplicada ao produto #$produtoId", 'Fiscal');
                    $this->setFlashMessage('success', 'Regra fiscal aplicada ao produto.');
                } else {
                    $this->setFlashMessage('error', 'Erro ao aplicar regra.');
                }
            }
        }
        header('Location: ' . BASE_URL . '/regrasFiscais');
        exit();
    }

    public function historico(): void
    {
        $labelModel = new LegislacaoModel();
        $log = $labelModel->getLogAtualizacoes('regra');
        $this->renderView('regras_fiscais/historico', [
            'pageTitle' => 'Histórico de Regras Fiscais',
            'log' => $log,
        ]);
    }
}
