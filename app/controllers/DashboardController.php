<?php

namespace App\Controllers;

use App\Core\Connection;
use App\Models\FinancialModel;
use App\Models\ProjetosModel;
use App\Models\LicencasOperacaoModel;
use App\Models\ClientesModel;
use App\Models\ContratosModel;
use App\Models\TarefasModel;
use App\Models\NotificacoesModel;
use App\Models\PropostaModel;
use App\Models\EmpresaModel;

class DashboardController extends BaseController
{
    private $financialModel;
    private $projetosModel;
    private $licencasModel;
    private $clientesModel;
    private $contratosModel;
    private $tarefasModel;
    private $notificacoesModel;
    private $propostaModel;
    private $empresaModel;

    public function __construct()
    {
        parent::__construct();
        $this->financialModel = new FinancialModel();
        $this->projetosModel = new ProjetosModel();
        $this->licencasModel = new LicencasOperacaoModel();
        $this->clientesModel = new ClientesModel();
        $this->contratosModel = new ContratosModel();
        $this->tarefasModel = new TarefasModel();
        $this->notificacoesModel = new NotificacoesModel();
        $this->propostaModel = new PropostaModel();
        $this->empresaModel = new EmpresaModel();
    }

    public function index()
    {
        $userId = $this->session->get('user_id') ?? 0;

        // --- VERIFICAÇÃO DE AVISOS DO SISTEMA (Manutenção ou Atualizações) ---
        $aviso = $this->empresaModel->getAvisoAtivo();
        $alertaSistema = null;

        // Verifica se há aviso ativo e se o usuário ainda não o visualizou nesta sessão
        if ($aviso && !$this->session->get('aviso_visto_' . $aviso['id'])) {
            $alertaSistema = $aviso;
            // Armazena na sessão que o aviso foi visto para não incomodar em cada refresh
            $this->session->set('aviso_visto_' . $aviso['id'], true);
        }

        // 1. Coleta os dados resumidos
        $projetosSummary = $this->projetosModel->getProjetosSummary();
        $licencasSummary = $this->licencasModel->getLicensesSummary();
        $clientesSummary = $this->clientesModel->getClientesSummary();
        $contratosSummary = $this->contratosModel->getContratosSummary();

        // 2. Coleta dados para os gráficos
        $monthlySummary = $this->financialModel->getResumoMensalParaGrafico(6);
        $statusDistribution = $this->projetosModel->getProjetosCountByStatus();

        // 3. Busca os projetos recentes (5 primeiros projetos ativos)
        $projetos = $this->projetosModel->getProjetos([], 5, 0);
        $projetosComLocalizacao = $this->projetosModel->getProjetosComLocalizacao();

        // --- GERAÇÃO AUTOMÁTICA DE AVISOS DE VIGÊNCIA ---
        // Busca avisos não lidas atuais para evitar duplicados
        $avisosPendentes = $this->notificacoesModel->getNaoLidas($userId);
        $titulosAvisos = array_column($avisosPendentes, 'titulo');

        if ($licencasSummary['vencimento30Dias'] > 0 && !in_array('Licenças a Vencer', $titulosAvisos)) {
            $this->notificacoesModel->criarNotificacao(
                $userId,
                'Licenças a Vencer',
                "Existem {$licencasSummary['vencimento30Dias']} licenças expirando nos próximos 30 dias.",
                BASE_URL . "/licencasOperacao"
            );
        }

        if ($contratosSummary['vencendo30dias'] > 0 && !in_array('Contratos a Vencer', $titulosAvisos)) {
            $this->notificacoesModel->criarNotificacao(
                $userId,
                'Contratos a Vencer',
                "Existem {$contratosSummary['vencendo30dias']} contratos próximos ao vencimento.",
                BASE_URL . "/contratos/vigencia"
            );
        }

        // 4. Busca dados do usuário (Tarefas e Notificações)
        $tarefasPendentes = $this->tarefasModel->getCountTarefasPendentesByUsuario($userId);
        $minhasTarefas = $this->tarefasModel->getTarefasPendentesByUsuario($userId, 5);

        // Contagem para o menu lateral
        $contagemPropostasPendentes = $this->propostaModel->getCountPropostasPendentes();

        // Propostas aguardando aprovação do diretor (apenas admin pode aprovar)
        $isAdmin = $this->session->isAdmin();
        $propostasPendentesDiretor = [];
        if ($isAdmin) {
            $propostasPendentesDiretor = $this->propostaModel->getPropostasPendentesDiretor(5);
        }

        $empresa = $this->empresaModel->getDadosEmpresa();
        $userEmail = $this->session->get('user_email', '');

        $data = [
            'pageTitle' => 'Dashboard - Visão Geral',
            'alerta_sistema' => $alertaSistema,
            'projetosAtivos' => $projetosSummary['totalEmAndamento'] ?? 0,
            'licencasAVencer' => $licencasSummary['vencimento30Dias'] ?? 0,
            'contratosVigentes' => $contratosSummary['totalVigentes'] ?? 0,
            'novosClientesMes' => $clientesSummary['novosMes'] ?? 0,
            'tarefasPendentes' => $tarefasPendentes,
            'minhasTarefas' => $minhasTarefas,
            'projetos' => $projetos,
            'projetosComLocalizacao' => $projetosComLocalizacao,
            'monthlySummary' => $monthlySummary,
            'statusDistribution' => $statusDistribution,
            'contagemPropostasPendentes' => $contagemPropostasPendentes,
            'propostasPendentesDiretor' => $propostasPendentesDiretor,
            'isAdmin' => $isAdmin,
            'empresa' => $empresa,
            'userEmail' => $userEmail,
        ];

        // 5. Renderiza a view do dashboard com os dados dinâmicos
        $this->renderView('dashboard', $data);
    }
}
