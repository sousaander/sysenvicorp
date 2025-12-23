<?php

namespace App\Controllers;

use App\Core\Connection;
use App\Models\LicencasOperacaoModel;

class LicencasOperacaoController extends BaseController
{
    private $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new LicencasOperacaoModel();
    }

    public function index()
    {
        // Coleta dados do modelo
        $summary = $this->model->getLicensesSummary();
        $criticalList = $this->model->getCriticalLicensesList();

        $data = array_merge([
            'pageTitle' => 'Licenças de Operação - Conformidade',
            'criticalList' => $criticalList,
        ], $summary);

        $this->renderView('licencasOperacao/index', $data);
    }

    // Exemplo de outra ação
    public function detalheLicenca()
    {
        // Lógica para carregar detalhes de uma licença específica
        $data = ['pageTitle' => 'Licenças - Detalhes da Licença'];
        $this->renderView('licencasOperacao/detalhe', $data);
    }
}
