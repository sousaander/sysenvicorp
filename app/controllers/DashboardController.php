<?php

namespace App\Controllers;

use App\Core\Connection;
use App\Models\FinancialModel;
use App\Models\ProjetosModel;
use App\Models\LicencasOperacaoModel;
use App\Models\ClientesModel;
use App\Models\ContratosModel; // 1. Importar o ContratosModel

class DashboardController extends BaseController
{
    private $financialModel;
    private $projetosModel;
    private $licencasModel;
    private $clientesModel;
    private $contratosModel; // 2. Declarar a propriedade

    public function __construct()
    {
        parent::__construct();
        $this->financialModel = new FinancialModel();
        $this->projetosModel = new ProjetosModel();
        $this->licencasModel = new LicencasOperacaoModel();
        $this->clientesModel = new ClientesModel();
        $this->contratosModel = new ContratosModel();
    }

    public function index()
    {
        // 1. Coleta os dados resumidos de cada modelo
        $projetosSummary = $this->projetosModel->getProjetosSummary();
        $licencasSummary = $this->licencasModel->getLicensesSummary();
        $clientesSummary = $this->clientesModel->getClientesSummary();
        $contratosSummary = $this->contratosModel->getContratosSummary(); // 4. Buscar o resumo dos contratos

        // 2. Coleta dados para os gráficos
        $monthlySummary = $this->financialModel->getMonthlySummaryForChart();
        $expenseByCategory = $this->financialModel->getExpenseSummaryByCategory();

        // 3. Busca os projetos recentes (5 primeiros projetos ativos)
        $projetos = $this->projetosModel->getProjetos([], 5, 0);

        // 4. Monta o array de dados para a view, agora com os dados de contratos
        $data = [
            'pageTitle' => 'Dashboard - Visão Geral',
            'projetosAtivos' => $projetosSummary['totalEmAndamento'] ?? 0,
            'licencasAVencer' => $licencasSummary['vencimento30Dias'] ?? 0,
            // Adicionamos os dados do resumo de contratos
            'contratosVigentes' => $contratosSummary['totalVigentes'] ?? 0,
            'contratosPendentes' => $contratosSummary['comPendenciaDocs'] ?? 0,
            'novosClientesMes' => $clientesSummary['novosMes'] ?? 0,
            'monthlySummary' => $monthlySummary,
            'expenseByCategory' => $expenseByCategory,
            'projetos' => $projetos, // Adiciona a lista de projetos para a view
        ];

        // 5. Renderiza a view do dashboard com os dados dinâmicos
        $this->renderView('dashboard', $data);
    }
}
