<?php

namespace App\Controllers;

use App\Core\Connection;
use App\Models\LicitacoesModel;

class LicitacoesController extends BaseController
{
    private $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new LicitacoesModel();
    }

    public function index()
    {
        // Coleta dados do modelo
        $summary = $this->model->getLicitacoesSummary();
        $criticalList = $this->model->getCriticalLicitacoesList();

        $data = array_merge([
            'pageTitle' => 'Licitações - Propostas e Prazos',
            'criticalList' => $criticalList,
        ], $summary);

        $this->renderView('licitacoes/index', $data);
    }

    // Exemplo de outra ação
    public function detalheProposta()
    {
        // Lógica para carregar detalhes de uma licitação específica
        $data = ['pageTitle' => 'Licitações - Detalhes da Proposta'];
        $this->renderView('licitacoes/detalhe', $data);
    }
}
