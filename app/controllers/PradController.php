<?php

namespace App\Controllers;

use App\Core\Connection;
use App\Models\PradModel;

class PradController extends BaseController
{
    private $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new PradModel();
    }

    public function index()
    {
        // Coleta dados do modelo
        $summary = $this->model->getPradSummary();
        $criticalList = $this->model->getCriticalPradList();

        $data = array_merge([
            'pageTitle' => 'PRAD - Planos de Recuperação',
            'criticalList' => $criticalList,
        ], $summary);

        $this->renderView('prad/index', $data);
    }

    // Exemplo de outra ação
    public function detalhePrad()
    {
        // Lógica para carregar detalhes de um PRAD específico
        $data = ['pageTitle' => 'PRAD - Detalhes do Plano'];
        $this->renderView('prad/detalhe', $data);
    }
}
