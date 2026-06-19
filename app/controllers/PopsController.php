<?php

namespace App\Controllers;

use App\Core\Connection;
use App\Models\PopsModel;

class PopsController extends BaseController
{
    private $model;

    /**
     * Mapeia ações para as permissões necessárias.
     * O BaseController usará este mapa para verificar o acesso.
     * @var array
     */
    protected $requiredPermissions = [
        'index' => 'pops_view',
        'visualizarPop' => 'pops_view',
        'novo' => 'pops_manage',
        'salvar' => 'pops_manage',
        'editar' => 'pops_manage',
        'excluir' => 'pops_manage',
    ];

    public function __construct()
    {
        parent::__construct();
        $this->model = new PopsModel();
    }

    public function index()
    {
        // Coleta dados do modelo
        $summary = $this->model->getPopsSummary();
        $criticalList = $this->model->getCriticalPopsList();

        $data = array_merge([
            'pageTitle' => 'POPs - Procedimentos Operacionais Padrão',
            'criticalList' => $criticalList,
        ], $summary);

        $this->renderView('pops/index', $data);
    }

    // Exemplo de outra ação
    public function visualizarPop()
    {
        // Lógica para carregar e exibir o documento POP específico
        $data = ['pageTitle' => 'POPs - Visualizar Documento'];
        $this->renderView('pops/visualizar', $data);
    }
}
