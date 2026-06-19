<?php

namespace App\Controllers;

use App\Models\LicitacoesModel;
use Dompdf\Dompdf;
use Dompdf\Options;
use App\Core\IAClient;
use App\Models\ConfigIAModel;
use App\Models\CaptacaoIAModel;

/**
 * Controller responsável por gerenciar as operações do módulo de Licitações.
 */
class LicitacoesController extends BaseController
{
    /**
     * @var LicitacoesModel
     */
    protected $model;

    /**
     * @var IAClient
     */
    protected $iaClient;

    /**
     * @var ConfigIAModel
     */
    protected $configIAModel;

    /**
     * @var CaptacaoIAModel
     */
    protected $captacaoIAModel;

    /**
     * Mapeia as ações para as permissões de acesso necessárias.
     */
    protected $requiredPermissions = [
        'index'     => 'licitacoes_view',
        'dashboard' => 'licitacoes_view',
        'editais'   => 'licitacoes_view',
        'relatorioPdf' => 'licitacoes_view',
        'agenteIA'  => 'licitacoes_manage',
        'relatorios' => 'licitacoes_view',
        'novo'      => 'licitacoes_manage',
        'editar'    => 'licitacoes_manage',
        'salvar'    => 'licitacoes_manage',
        'excluir'   => 'licitacoes_manage',
        'detalhe'   => 'licitacoes_view',
        'captacoes' => 'licitacoes_view',
        'favoritar' => 'licitacoes_manage',
        'ignorar'   => 'licitacoes_manage',
        'salvarConfigIA' => 'licitacoes_manage',
        'iaConfig' => 'licitacoes_view',
        'iaStatus' => 'licitacoes_view',
        'api'       => 'licitacoes_view',
        'favoritarCaptacao' => 'licitacoes_manage',
        'ignorarCaptacao' => 'licitacoes_manage',
        'converter' => 'licitacoes_manage',
    ];

    public function __construct()
    {
        parent::__construct();
        $this->model = new LicitacoesModel();
        $this->iaClient = new IAClient();
        $this->configIAModel = new ConfigIAModel();
        $this->captacaoIAModel = new CaptacaoIAModel();
    }

    /**
     * Exibe a listagem detalhada de licitações com filtros.
     */
    public function index()
    {
        $filtros = [
            'busca' => filter_input(INPUT_GET, 'busca', FILTER_SANITIZE_SPECIAL_CHARS),
            'status' => filter_input(INPUT_GET, 'status', FILTER_SANITIZE_SPECIAL_CHARS),
        ];

        $paginaAtual = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1;
        $itensPorPagina = 10;
        $offset = ($paginaAtual - 1) * $itensPorPagina;

        $licitacoes = $this->model->listar($filtros, $itensPorPagina, $offset);
        $totalRegistros = (int)$this->model->contarListagem($filtros);
        $totalPaginas = ceil($totalRegistros / $itensPorPagina);

        $kpis = $this->model->getKpis();
        $kpis['total_filtrado'] = $totalRegistros;

        $this->renderView('licitacoes/index', [
            'pageTitle'  => 'Listagem de Licitações',
            'licitacoes' => $licitacoes,
            'filtros'    => $filtros,
            'kpis'       => $kpis,
            'paginaAtual'=> $paginaAtual,
            'totalPaginas'=> $totalPaginas
        ]);
    }

    /**
     * Exibe o Dashboard com indicadores (KPIs) e análise visual.
     * 🔹 ATUALIZADO: Agora inclui dados do Agente IA
     */
    public function dashboard()
    {
        $kpis = $this->model->getKpis();
        $volumeMensal = $this->model->getVolumeMensal();
        
        // 🔹 NOVO: Busca dados do Agente IA
        $aiConfig = $this->configIAModel->get();
        $ultimasCaptacoes = $this->captacaoIAModel->getUltimas(3);
        $totalCaptadas = $this->captacaoIAModel->getCount(['nao_ignoradas' => true]);
        $contagemCaptacoesIA = $this->captacaoIAModel->getContagemNaoLidas();

        $this->renderView('licitacoes/dashboard', [
            'pageTitle'  => 'Dashboard de Licitações',
            'kpis'       => $kpis,
            'aiConfig'   => $aiConfig,
            'volumeMensal' => $volumeMensal,
            'ultimasCaptacoes' => $ultimasCaptacoes,
            'totalCaptadas' => $totalCaptadas,
            'contagemCaptacoesIA' => $contagemCaptacoesIA
        ]);
    }

    /**
     * Configurações do Agente de Inteligência Artificial.
     * 🔹 ATUALIZADO: Integração com API Python
     */
    public function agenteIA()
    {
        // 🔹 Verifica se é requisição AJAX para configurar
        if ($this->isAjax() && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->agenteIAConfigAjax();
            return;
        }

        $config = $this->configIAModel->get();
        $portaisAtivos = json_decode($config['portais'] ?? '[]', true) ?: [];
        $statusIA = $this->iaClient->getStatus();
        $iaAlive = $this->iaClient->isAlive();

        $this->renderView('licitacoes/agente_ia', [
            'pageTitle'     => 'Configuração do Agente IA',
            'config'        => $config,
            'portaisAtivos' => $portaisAtivos,
            'statusIA'      => $statusIA,
            'iaAlive'       => $iaAlive
        ]);
    }

    /**
     * 🔹 NOVO: Método de despacho para rotas do tipo /licitacoes/api/...
     * Mapeia chamadas como /licitacoes/api/ia-status para o método correto.
     */
    public function api($subAction = null)
    {
        if ($subAction === 'ia-status') {
            return $this->iaStatus();
        }
        if ($subAction === 'ia-config') {
            return $this->iaConfig();
        }
        
        header('Content-Type: application/json');
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Endpoint de API não encontrado.']);
        exit;
    }

    /**
     * 🔹 NOVO: Endpoint AJAX para configurar o Agente IA
     */
    private function agenteIAConfigAjax()
    {
        header('Content-Type: application/json');
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        $config = [
            'ativo' => $input['ativo'] ?? true,
            'portais' => $input['portais'] ?? [],
            'palavras_chave' => $input['palavras_chave'] ?? '',
            'sound_alerts_enabled' => $input['sound_alerts_enabled'] ?? true,
            'notification_sound' => $input['notification_sound'] ?? 'ping',
            'daily_email_summary_enabled' => $input['daily_email_summary_enabled'] ?? true
        ];
        
        // Salva no banco
        $this->configIAModel->save($config);
        
        // Envia para o agente Python
        $result = $this->iaClient->updateConfig($config);
        
        echo json_encode(['success' => true, 'result' => $result]);
        exit;
    }

    /**
     * 🔹 NOVO: Endpoint API para obter configuração (AJAX)
     */
    public function iaConfig()
    {
        header('Content-Type: application/json');
        $config = $this->configIAModel->get();
        echo json_encode($config);
        exit;
    }

    /**
     * 🔹 NOVO: Endpoint API para obter status do agente Python
     */
    public function iaStatus()
    {
        header('Content-Type: application/json');
        $status = $this->iaClient->getStatus();
        echo json_encode($status ?: ['ativo' => false, 'error' => 'Python agent offline']);
        exit;
    }

    /**
     * Salva as configurações do Agente IA (método original via POST form).
     * 🔹 ATUALIZADO: Agora também sincroniza com Python
     */
    public function salvarConfigIA()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/licitacoes/agenteIA');
            exit;
        }

        $dados = [
            'portais'        => json_encode($_POST['portais'] ?? []),
            'palavras_chave' => filter_input(INPUT_POST, 'palavras_chave', FILTER_SANITIZE_SPECIAL_CHARS),
            'ativo'          => isset($_POST['ativo']) ? 1 : 0,
            'sound_alerts_enabled' => isset($_POST['sound_alerts_enabled']) ? 1 : 0,
            'notification_sound' => filter_input(INPUT_POST, 'notification_sound', FILTER_SANITIZE_SPECIAL_CHARS) ?: 'ping',
            'daily_email_summary_enabled' => isset($_POST['daily_email_summary_enabled']) ? 1 : 0
        ];

        if ($this->configIAModel->save($dados)) {
            // 🔹 Sincroniza com agente Python
            $this->iaClient->updateConfig([
                'ativo' => (bool)$dados['ativo'],
                'portais' => json_decode($dados['portais'], true),
                'palavras_chave' => $dados['palavras_chave'],
                'sound_alerts_enabled' => (bool)$dados['sound_alerts_enabled'],
                'notification_sound' => $dados['notification_sound'],
                'daily_email_summary_enabled' => (bool)$dados['daily_email_summary_enabled']
            ]);
            
            $this->setFlashMessage('success', 'Parâmetros do Agente IA atualizados com sucesso!');
        } else {
            $this->setFlashMessage('error', 'Falha ao atualizar parâmetros.');
        }

        header('Location: ' . BASE_URL . '/licitacoes/agenteIA');
        exit;
    }

    /**
     * Exibe o formulário para nova licitação.
     */
    public function novo()
    {
        // 🔹 Verifica se há dados de conversão de captação
        $captacaoData = $_SESSION['captacao_converter'] ?? null;
        if ($captacaoData) {
            unset($_SESSION['captacao_converter']);
        }
        
        $this->renderView('licitacoes/form', [
            'pageTitle' => 'Nova Licitação',
            'lic'       => null,
            'captacao_data' => $captacaoData,
            'csrf_token' => $this->generateCsrfToken()
        ]);
    }

    /**
     * Exibe o formulário de edição de uma licitação.
     */
    public function editar($id)
    {
        $lic = $this->model->getById((int)$id);
        if (!$lic) {
            $this->setFlashMessage('error', 'Licitação não encontrada.');
            header('Location: ' . BASE_URL . '/licitacoes');
            exit;
        }

        $this->renderView('licitacoes/form', [
            'pageTitle' => 'Editar Licitação',
            'lic'       => $lic,
            'csrf_token' => $this->generateCsrfToken()
        ]);
    }

    /**
     * Processa o salvamento ou atualização de uma licitação.
     * 🔹 ATUALIZADO: Suporte para conversão de captação
     */
    public function salvar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/licitacoes');
            exit;
        }

        if (!isset($_POST['csrf_token']) || !$this->validateCsrfToken($_POST['csrf_token'])) {
            $this->setFlashMessage('error', 'Erro de validação de segurança (CSRF).');
            header('Location: ' . BASE_URL . '/licitacoes');
            exit;
        }

        $dados = $_POST;

        // 🔹 Verifica se veio de uma conversão de captação
        $captacaoId = $dados['captacao_id'] ?? null;
        unset($dados['captacao_id']);

        if (isset($dados['acao'])) {
            if ($dados['acao'] === 'publicar') {
                $dados['status'] = 'publicada';
            } elseif ($dados['acao'] === 'rascunho') {
                $dados['status'] = 'rascunho';
            }
        }

        $requiredFields = ['numero', 'modalidade', 'orgao', 'objeto', 'responsavel', 'dt_sessao'];
        foreach ($requiredFields as $field) {
            if (empty(trim($dados[$field] ?? ''))) {
                $this->setFlashMessage('error', "O campo '{$field}' é obrigatório.");
                header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/licitacoes'));
                exit;
            }
        }

        if (strlen(trim($dados['objeto'] ?? '')) < 50) {
            $this->setFlashMessage('error', "O objeto da licitação deve ter pelo menos 50 caracteres.");
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/licitacoes'));
            exit;
        }

        // Upload do Edital
        if (isset($_FILES['edital_arquivo']) && $_FILES['edital_arquivo']['name'] !== '') {
            if ($_FILES['edital_arquivo']['error'] !== UPLOAD_ERR_OK) {
                $this->setFlashMessage('error', 'Erro no upload do arquivo.');
                header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/licitacoes'));
                exit;
            }

            $maxFileSize = 5 * 1024 * 1024;
            if ($_FILES['edital_arquivo']['size'] > $maxFileSize) {
                $this->setFlashMessage('error', 'O arquivo do edital é muito grande. O limite máximo permitido é 5MB.');
                header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/licitacoes'));
                exit;
            }

            $uploadDir = ROOT_PATH . '/storage/licitacoes/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0775, true);
            }

            $fileInfo = pathinfo($_FILES['edital_arquivo']['name']);
            $extension = isset($fileInfo['extension']) ? strtolower($fileInfo['extension']) : '';

            if ($extension !== 'pdf') {
                $this->setFlashMessage('error', 'Apenas arquivos PDF são permitidos.');
                header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/licitacoes'));
                exit;
            }

            $newFilename = 'edital_' . time() . '_' . uniqid() . '.' . $extension;
            if (move_uploaded_file($_FILES['edital_arquivo']['tmp_name'], $uploadDir . $newFilename)) {
                $dados['edital_path'] = $newFilename;
            }
        }

        foreach (['dt_abertura', 'dt_entrega'] as $dateField) {
            $dados[$dateField] = !empty($dados[$dateField]) ? $dados[$dateField] : null;
        }

        $dados['valor_estimado'] = !empty($dados['valor_estimado']) ? (float)$dados['valor_estimado'] : 0.0;

        if (isset($dados['categorias'])) {
            $dados['categorias'] = trim($dados['categorias']);
            if (empty($dados['categorias'])) {
                $dados['categorias'] = null;
            }
        } else {
            $dados['categorias'] = null;
        }

        unset($dados['csrf_token'], $dados['acao'], $dados['valor_estimado_db'], $dados['valor_visual']);

        if (array_key_exists('id', $dados) && empty($dados['id'])) {
            unset($dados['id']);
        }

        try {
            $licitacaoId = $this->model->salvar($dados);
            if ($licitacaoId) {
                // 🔹 Se veio de uma captação, marca como convertida
                if ($captacaoId) {
                    $this->captacaoIAModel->marcarConvertido($captacaoId, $licitacaoId);
                }
                $this->setFlashMessage('success', 'Registro processado com sucesso.');
            } else {
                $errorMessage = (method_exists($this->model, 'getLastError') && $this->model->getLastError()) 
                    ? $this->model->getLastError() 
                    : 'Falha na persistência dos dados.';
                $this->setFlashMessage('error', $errorMessage);
                error_log("LicitacoesController Error: " . $errorMessage);
            }
        } catch (\PDOException $e) {
            $this->setFlashMessage('error', 'Erro técnico de banco de dados.');
            error_log("LicitacoesController PDOException: " . $e->getMessage());
        }

        header('Location: ' . BASE_URL . '/licitacoes');
        exit;
    }

    /**
     * Gera um relatório consolidado em PDF dos editais de um determinado mês.
     */
    public function relatorioPdf()
    {
        $mes = filter_input(INPUT_GET, 'mes', FILTER_VALIDATE_INT) ?: (int)date('m');
        $ano = filter_input(INPUT_GET, 'ano', FILTER_VALIDATE_INT) ?: (int)date('Y');
        $categoria = filter_input(INPUT_GET, 'categoria', FILTER_SANITIZE_SPECIAL_CHARS);

        $dados = $this->model->getDadosRelatorioMensal($mes, $ano, $categoria);
        
        $dados['categoria_filtro'] = $categoria;
        
        // Busca dados da empresa
        $empresaModel = new \App\Models\EmpresaModel();
        $dados['empresa'] = $empresaModel->getDadosEmpresa();

        ob_start();
        $this->renderPartial('licitacoes/relatorio_mensal_pdf', $dados);
        $html = ob_get_clean();

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        $dompdf->stream("Relatorio_Editais_{$ano}_{$mes}.pdf", ["Attachment" => false]);
        exit;
    }

    /**
     * Exibe a página principal de relatórios do módulo de licitações.
     */
    public function relatorios()
    {
        $this->renderView('licitacoes/relatorios', [
            'pageTitle' => 'Relatórios de Licitações'
        ]);
    }

    /**
     * Exibe a listagem de licitações que possuem arquivos de edital anexados.
     */
    public function editais()
    {
        $filtros = [
            'busca' => filter_input(INPUT_GET, 'busca', FILTER_SANITIZE_SPECIAL_CHARS),
            'com_edital' => true
        ];

        $paginaAtual = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1;
        $itensPorPagina = 10;
        $offset = ($paginaAtual - 1) * $itensPorPagina;

        $editais = $this->model->listar($filtros, $itensPorPagina, $offset);
        $totalRegistros = (int)$this->model->contarListagem($filtros);
        $totalPaginas = ceil($totalRegistros / $itensPorPagina);

        $this->renderView('licitacoes/editais', [
            'pageTitle'  => 'Repositório de Editais',
            'editais'    => $editais,
            'filtros'    => $filtros,
            'paginaAtual'=> $paginaAtual,
            'totalPaginas'=> $totalPaginas
        ]);
    }

    /**
     * Exibe os detalhes de uma licitação.
     */
    public function detalhe($id)
    {
        $lic = $this->model->getById((int)$id);
        if (!$lic) {
            $this->setFlashMessage('error', 'Licitação não encontrada.');
            header('Location: ' . BASE_URL . '/licitacoes');
            exit;
        }

        $this->renderView('licitacoes/detalhe', [
            'pageTitle' => 'Detalhamento do Protocolo',
            'lic'       => $lic
        ]);
    }

    /**
     * Exclui uma licitação do sistema.
     */
    public function excluir($id)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($this->model->excluir((int)$id)) {
                $this->setFlashMessage('success', 'Registro removido com sucesso.');
            } else {
                $this->setFlashMessage('error', 'Erro ao remover registro.');
            }
        }
        header('Location: ' . BASE_URL . '/licitacoes');
        exit;
    }

    /**
     * Lista as oportunidades capturadas pelo Radar de Inteligência Artificial.
     * 🔹 ATUALIZADO: Usa o novo model de captações
     */
    public function captacoes()
    {
        // Marca todas as captações como visualizadas
        $this->captacaoIAModel->marcarTodasComoLidas();
        
        $filtros = [];
        if (!empty($_GET['apenas_favoritas'])) {
            $filtros['apenas_favoritas'] = true;
        }
        $filtros['nao_ignoradas'] = true;
        
        $page = $_GET['page'] ?? 1;
        $limit = 20;
        $offset = ($page - 1) * $limit;
        
        $captacoes = $this->captacaoIAModel->getAll($filtros, $limit, $offset);
        $total = $this->captacaoIAModel->getCount($filtros);

        $this->renderView('licitacoes/captacoes', [
            'pageTitle' => 'Radar de Oportunidades IA',
            'captacoes' => $captacoes,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'totalPages' => ceil($total / $limit)
        ]);
    }

    /**
     * 🔹 NOVO: Alterna o status de favorito de uma captação.
     */
    public function favoritarCaptacao($id)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->captacaoIAModel->favoritar((int)$id, true);
            $this->setFlashMessage('success', 'Oportunidade favoritada!');
        }
        header('Location: ' . BASE_URL . '/licitacoes/captacoes');
        exit;
    }

    /**
     * 🔹 NOVO: Ignora uma oportunidade do Radar IA.
     */
    public function ignorarCaptacao($id)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->captacaoIAModel->ignorar((int)$id, true);
            $this->setFlashMessage('info', 'Oportunidade ignorada.');
        }
        header('Location: ' . BASE_URL . '/licitacoes/captacoes');
        exit;
    }

    /**
     * 🔹 ATUALIZADO: Converte uma captação em licitação.
     */
    public function converter($id)
    {
        $captacao = $this->captacaoIAModel->getById((int)$id);
        
        if (!$captacao) {
            $this->setFlashMessage('error', 'Oportunidade não encontrada.');
            header('Location: ' . BASE_URL . '/licitacoes/captacoes');
            exit;
        }
        
        // Prepara dados para o formulário de nova licitação
        $_SESSION['captacao_converter'] = [
            'orgao' => $captacao['orgao_externo'],
            'objeto' => $captacao['objeto'],
            'numero' => $captacao['numero_edital'],
            'valor_estimado' => $captacao['valor_estimado'],
            'dt_sessao' => $captacao['data_sessao'],
            'captacao_id' => $captacao['id']
        ];
        
        header('Location: ' . BASE_URL . '/licitacoes/novo');
        exit;
    }

    /**
     * 🔹 Método mantido para compatibilidade (redireciona para o novo)
     */
    public function favoritar($id)
    {
        return $this->favoritarCaptacao($id);
    }

    /**
     * 🔹 Método mantido para compatibilidade (redireciona para o novo)
     */
    public function ignorar($id)
    {
        return $this->ignorarCaptacao($id);
    }

    /**
     * Rotina automática para atualizar status de licitações vencidas.
     */
    public function cronCheckVencimento()
    {
        $token = filter_input(INPUT_GET, 'token', FILTER_SANITIZE_SPECIAL_CHARS);
        $isCli = php_sapi_name() === 'cli';
        $isValidToken = defined('CRON_TOKEN') && !empty(CRON_TOKEN) && $token === CRON_TOKEN;

        if (!$isCli && !$isValidToken) {
            http_response_code(403);
            die('Acesso negado. Token de segurança inválido.');
        }

        $count = $this->model->updateExpiredLicitacoesStatus();

        if ($isCli) {
            echo "Rotina executada: {$count} licitacoes marcadas como vencidas.\n";
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'updated_count' => $count]);
        }
        exit;
    }

    /**
     * 🔹 Método auxiliar para verificar se é requisição AJAX
     */
    private function isAjax()
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    }
}