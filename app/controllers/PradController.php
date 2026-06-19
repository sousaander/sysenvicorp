<?php

namespace App\Controllers;

use App\Core\Connection;
use App\Models\PradModel;

class PradController extends BaseController
{
    private $model;

    /**
     * Mapeia ações para as permissões necessárias.
     * O BaseController usará este mapa para verificar o acesso.
     * @var array
     */
    protected $requiredPermissions = [
        'index' => 'prad_view',
        'detalhePrad' => 'prad_view',
        'novo' => 'prad_manage',
        'salvar' => 'prad_manage',
        'editar' => 'prad_manage',
        'excluir' => 'prad_manage',
    ];

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
