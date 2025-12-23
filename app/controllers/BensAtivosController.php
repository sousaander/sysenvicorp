<?php

namespace App\Controllers;

use App\Core\Connection;
use App\Models\BensAtivosModel;

class BensAtivosController extends BaseController
{
    private $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new BensAtivosModel(Connection::getInstance());
    }

    public function index()
    {
        // Coleta dados do modelo
        $summary = $this->model->getAssetsSummary();
        $maintenanceList = $this->model->getAssetsMaintenanceList();

        $data = array_merge([
            'pageTitle' => 'Bens e Ativos - Inventário e Manutenção',
            'maintenanceList' => $maintenanceList,
        ], $summary);

        $this->renderView('bensAtivos/index', $data);
    }

    // Exemplo de outra ação
    public function detalheAtivo()
    {
        // Lógica para carregar detalhes de um ativo específico
        $data = ['pageTitle' => 'Bens e Ativos - Detalhes do Ativo'];
        $this->renderView('bensAtivos/detalhe', $data);
    }
}
