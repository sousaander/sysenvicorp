<?php

namespace App\Controllers;

use App\Models\FiscalModel;
use App\Models\AliquotaModel;
use App\Models\SpedFiscalModel;
use App\Models\RetencaoModel;

class FiscalController extends BaseController
{
    protected $requiredPermissions = [
        'index' => 'fiscal_dashboard_view',
        'lancamentos' => 'fiscal_lancamentos_view',
        'notas' => 'fiscal_notas_view',
        'relatorios' => 'fiscal_relatorios_view',
        'parametros' => 'fiscal_relatorios_view',
        'sped' => 'fiscal_relatorios_view',
        'retencoes' => 'fiscal_relatorios_view',
    ];

    private FiscalModel $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new FiscalModel();
    }

    public function index()
    {
        $resumo = $this->model->getResumo();
        $this->renderView('fiscal/index', [
            'pageTitle' => 'Fiscal e Contábil',
            'resumo' => $resumo,
        ]);
    }

    public function lancamentos()
    {
        $lancamentos = $this->model->getLancamentos();
        $this->renderView('fiscal/lancamentos', [
            'pageTitle' => 'Lançamentos Contábeis',
            'lancamentos' => $lancamentos,
        ]);
    }

    public function notas()
    {
        $notaFiscalModel = new \App\Models\NotaFiscalModel();
        $filtros = [];
        if (!empty($_GET['status'])) $filtros['status'] = $_GET['status'];
        if (!empty($_GET['tipo'])) $filtros['tipo'] = $_GET['tipo'];
        $notas = $notaFiscalModel->getAll($filtros);

        $this->renderView('fiscal/notas', [
            'pageTitle' => 'Notas Fiscais',
            'notas' => $notas,
            'filtros' => $filtros,
        ]);
    }

    public function relatorios()
    {
        $this->renderView('fiscal/relatorios', [
            'pageTitle' => 'Relatórios Fiscais',
        ]);
    }

    public function parametros()
    {
        $aliquotaModel = new AliquotaModel();
        $aliquotas = $aliquotaModel->getAll();

        $empresaModel = new \App\Models\EmpresaModel();
        $empresa = $empresaModel->getDadosEmpresa();

        $this->renderView('fiscal/parametros', [
            'pageTitle' => 'Parâmetros Fiscais',
            'aliquotas' => $aliquotas,
            'empresa' => $empresa,
        ]);
    }

    public function sped()
    {
        $dataInicio = $_GET['data_inicio'] ?? date('Y-m-01');
        $dataFim = $_GET['data_fim'] ?? date('Y-m-t');
        $tipo = $_GET['tipo'] ?? 'fiscal';

        $spedModel = new SpedFiscalModel();

        if (isset($_GET['exportar'])) {
            $blocos = $tipo === 'contabil'
                ? $spedModel->gerarSpedContabil($dataInicio, $dataFim)
                : $spedModel->gerarSpedFiscal($dataInicio, $dataFim);
            $txt = $spedModel->exportarTxt($blocos);

            header('Content-Type: text/plain; charset=utf-8');
            header('Content-Disposition: attachment; filename="sped_' . $tipo . '_' . $dataInicio . '_' . $dataFim . '.txt"');
            echo $txt;
            exit();
        }

        $this->renderView('fiscal/sped', [
            'pageTitle' => 'SPED',
            'dataInicio' => $dataInicio,
            'dataFim' => $dataFim,
            'tipo' => $tipo,
        ]);
    }

    public function retencoes()
    {
        $dataInicio = $_GET['data_inicio'] ?? date('Y-m-01');
        $dataFim = $_GET['data_fim'] ?? date('Y-m-t');

        $retencaoModel = new RetencaoModel();
        $totais = $retencaoModel->getTotalPorTipo($dataInicio, $dataFim);

        $this->renderView('fiscal/retencoes', [
            'pageTitle' => 'Retenções de Impostos',
            'totais' => $totais,
            'dataInicio' => $dataInicio,
            'dataFim' => $dataFim,
        ]);
    }
}
