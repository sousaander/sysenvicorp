<?php

namespace App\Controllers;

use App\Core\Connection;
use App\Models\ClientesModel;
use App\Models\ProjetosModel;
use App\Models\TarefasModel;
use App\Models\UsuarioModel;
use App\Models\NotificacoesModel;
use App\Models\EmpresaModel;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Dompdf\Dompdf;
use Dompdf\Options;

class ProjetosController extends BaseController
{
    private $model;
    private $clientesModel;
    private $tarefasModel;
    private $usuarioModel;
    private $notificacoesModel;
    private $empresaModel;

    /**
     * Mapeia ações para as permissões necessárias.
     * O BaseController usará este mapa para verificar o acesso.
     * @var array
     */
    protected $requiredPermissions = [
        // Ações gerais do projeto
        'index'                 => 'projetos_view',
        'detalhe'               => 'projetos_view',
        'arquivados'            => 'projetos_view',
        'novo'                  => 'projetos_create',
        'editar'                => 'projetos_edit',
        'excluir'               => 'projetos_delete',
        'restaurar'             => 'projetos_edit',
        'cancelados'            => 'projetos_view',
        'relatorioTarefasPdf'   => 'projetos_view',

        // Ações de AJAX/API
        'getTarefa'             => 'projetos_view',
        'getProjetoDados'       => 'projetos_view',
        'getComentariosTarefa'  => 'projetos_view',
        'concluirTarefaAjax'    => 'projetos_tarefas_manage',
        'toggleChecklistItem'   => 'projetos_tarefas_manage',
        'deleteChecklistItem'   => 'projetos_tarefas_manage',

        // Ações de Orçamento
        'salvarItemOrcamento'   => 'projetos_orcamento_manage',
        'excluirItemOrcamento'  => 'projetos_orcamento_manage',

        // Ações de ART/RRT
        'salvarArt'             => 'projetos_art_manage',
        'excluirArt'            => 'projetos_art_manage',

        // Ações de Documentos (CDT, Mapas, Arquivos)
        'salvarCDT'             => 'projetos_docs_manage',
        'excluirCDT'            => 'projetos_docs_manage',
        'salvarMapa'            => 'projetos_docs_manage',
        'excluirMapa'           => 'projetos_docs_manage',
        'salvarArquivo'         => 'projetos_docs_manage',
        'excluirArquivo'        => 'projetos_docs_manage',

        // Ações de Tarefas (e seus componentes como comentários, checklists, etc.)
        'salvarTarefa'          => 'projetos_tarefas_manage',
        'excluirTarefa'         => 'projetos_tarefas_manage',
        'salvarComentario'      => 'projetos_tarefas_manage',
        'atualizarComentario'   => 'projetos_tarefas_manage',
        'excluirComentario'     => 'projetos_tarefas_manage',
        // Adicione outras ações de tarefa aqui conforme necessário
    ];

    public function __construct()
    {
        parent::__construct();
        $this->model = new ProjetosModel(); // Já estava correto
        $this->clientesModel = new ClientesModel(); // Já estava correto
        $this->tarefasModel = new TarefasModel();
        $this->usuarioModel = new UsuarioModel();
        $this->notificacoesModel = new NotificacoesModel();
        $this->empresaModel = new EmpresaModel();
    }

    public function index()
    {
        // Coleta dados do modelo
        $summary = $this->model->getProjetosSummary();

        // Coleta filtros da URL
        $filtros = [
            'status' => filter_input(INPUT_GET, 'status', FILTER_SANITIZE_SPECIAL_CHARS),
            'responsavel' => filter_input(INPUT_GET, 'responsavel', FILTER_SANITIZE_SPECIAL_CHARS),
            'orderBy' => filter_input(INPUT_GET, 'orderBy', FILTER_SANITIZE_SPECIAL_CHARS) ?: 'id',
            'orderDir' => filter_input(INPUT_GET, 'orderDir', FILTER_SANITIZE_SPECIAL_CHARS) ?: 'DESC',
        ];

        // Lógica de Paginação
        $paginaAtual = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1;
        $itensPorPagina = 5; // Define quantos projetos por página
        $offset = ($paginaAtual - 1) * $itensPorPagina;

        // Busca os projetos da página atual
        $projetos = $this->model->getProjetos($filtros, $itensPorPagina, $offset);
        // Conta o total de projetos para calcular o total de páginas
        $totalProjetos = $this->model->getProjetosCount($filtros);
        $totalPaginas = ceil($totalProjetos / $itensPorPagina);

        // Os dados do resumo são mesclados aqui para a página principal
        $data = array_merge($summary, [
            'pageTitle' => 'Projetos - Cronogramas e Entregas',
            'projetos' => $projetos,
            'paginaAtual' => $paginaAtual,
            'totalPaginas' => $totalPaginas,
            'filtros' => $filtros, // Passa os filtros ativos para a view
            'baseUrl' => '/projetos', // Define a URL base para a paginação e links
        ]);

        $this->renderView('projetos/index', $data);
    }

    /**
     * Exibe os detalhes de um projeto para edição.
     * Agora funciona como um dashboard para o projeto, com submenus.
     * @param int $id O ID do projeto.
     * @param string $submenu O submenu a ser exibido (default: 'resumo').
     */
    public function detalhe(int $id, string $submenu = 'resumo')
    {
        $projeto = $this->model->getProjetoById($id);

        if (!$projeto) {
            $this->setFlashMessage('error', 'Projeto não encontrado.');
            header('Location: ' . BASE_URL . '/projetos');
            exit();
        }

        // Carrega os dados necessários para o submenu específico
        $submenuData = []; // Garante que a variável sempre exista
        switch ($submenu) {
            case 'dados_gerais':
                // Reutiliza o form.php para edição
                // A view 'projetos/form' espera a variável $clientes diretamente.
                // Adicionamos ao array $data principal para garantir consistência.
                $data['clientes'] = $this->clientesModel->getAllClientes();
                $submenuView = 'projetos/form';
                break;
            case 'tarefas':
                $filtros = [
                    'status' => filter_input(INPUT_GET, 'status_tarefa'),
                    'responsavel_id' => filter_input(INPUT_GET, 'responsavel_id', FILTER_VALIDATE_INT)
                ];

                // Paginação de Logs
                $pageLogs = filter_input(INPUT_GET, 'page_logs', FILTER_VALIDATE_INT) ?: 1;
                $limitLogs = 5; // Quantidade de logs por página
                $offsetLogs = ($pageLogs - 1) * $limitLogs;

                // Busca tarefas e usuários para o modal
                $submenuData['tarefas'] = $this->tarefasModel->getTarefasByProjetoId($id, $filtros);
                $submenuData['usuarios'] = $this->usuarioModel->getListaUsuarios();
                $submenuData['filtros'] = $filtros;
                $submenuData['logs'] = $this->tarefasModel->getLogs($id, $limitLogs, $offsetLogs);
                $totalLogs = $this->tarefasModel->getLogsCount($id);
                $submenuData['total_pages_logs'] = ceil($totalLogs / $limitLogs);
                $submenuData['current_page_logs'] = $pageLogs;
                $submenuData['tags'] = $this->tarefasModel->getTagsByProjetoId($id);
                $submenuView = 'projetos/submenus/tarefas';
                break;
            case 'orcamento':
                // Lógica para buscar dados do orçamento
                $submenuData['itens_orcamento'] = $this->model->getOrcamentoByProjetoId($id);
                $submenuData['summary'] = $this->model->getOrcamentoSummary($id);
                $submenuView = 'projetos/submenus/orcamento';
                break;
            case 'cronograma':
                // Busca as tarefas do projeto para montar o cronograma
                $tarefasCronograma = $this->tarefasModel->getTarefasByProjetoId($id);
                $cronogramaTasks = [];
                foreach ($tarefasCronograma as $t) {
                    // apenas tarefas com data de início ou fim podem ser mapeadas
                    if (empty($t['data_inicio']) && empty($t['data_fim'])) {
                        continue;
                    }
                    $task = [
                        'id' => 't' . $t['id'],
                        'name' => $t['titulo'],
                        'start' => $t['data_inicio'] ?: $t['data_fim'],
                        'end' => $t['data_fim'] ?: $t['data_inicio'],
                        // progresso simples baseado no status
                        'progress' => ($t['status'] === 'Concluída' ? 100 : ($t['status'] === 'Em Andamento' ? 50 : 0)),
                    ];

                    // adiciona dependências se existirem
                    $deps = $this->tarefasModel->getDependencias($t['id']);
                    if (!empty($deps)) {
                        $depIds = array_map(function ($d) {
                            return 't' . $d['dependencia_id'];
                        }, $deps);
                        $task['dependencies'] = implode(',', $depIds);
                    }

                    $cronogramaTasks[] = $task;
                }

                $submenuData['cronograma_tasks'] = $cronogramaTasks;
                $submenuData['tasks_json'] = json_encode($cronogramaTasks);
                $submenuView = 'projetos/submenus/cronograma';
                break;
            case 'cdt':
                $submenuData['documentos'] = $this->model->getCDTByProjetoId($id);
                $submenuView = 'projetos/submenus/cdt';
                break;
            case 'mapas':
                $submenuData['mapas'] = $this->model->getMapasByProjetoId($id);
                $submenuView = 'projetos/submenus/mapas';
                break;
            case 'arquivos':
                $submenuData['arquivos'] = $this->model->getArquivosByProjetoId($id);
                $submenuView = 'projetos/submenus/arquivos';
                break;
            case 'art':
                $submenuData['arts'] = $this->model->getArtByProjetoId($id);
                $submenuView = 'projetos/submenus/art';
                break;
            case 'resumo':
            default:
                // Busca os dados agregados para o dashboard de resumo do projeto
                $submenuData['summaryDetails'] = $this->model->getProjectDetailsSummary($id);
                $submenuData['timeline'] = $this->model->getTimelineByProjectId($id);

                // Cálculo dinâmico do Progresso Geral baseado nas tarefas
                $todasTarefas = $this->tarefasModel->getTarefasByProjetoId($id);
                $totalTarefas = is_array($todasTarefas) ? count($todasTarefas) : 0;
                $tarefasConcluidas = 0;

                if ($totalTarefas > 0) {
                    foreach ($todasTarefas as $tarefa) {
                        // Verifica status de conclusão (ajuste conforme os status usados no seu sistema)
                        if (in_array($tarefa['status'], ['Concluída', 'Concluído', 'Finalizado'])) {
                            $tarefasConcluidas++;
                        }
                    }
                    $submenuData['summaryDetails']['progresso_calculado'] = round(($tarefasConcluidas / $totalTarefas) * 100);
                } else {
                    // Se não houver tarefas, assume 100% se o projeto estiver concluído, caso contrário 0%
                    $submenuData['summaryDetails']['progresso_calculado'] = ($projeto['status'] === 'Concluído') ? 100 : 0;
                }

                $submenuView = 'projetos/submenus/resumo';
                break;
        }

        // Mescla os dados já definidos (como 'clientes') com os dados padrão.
        $data = array_merge($data ?? [], [
            'pageTitle' => 'Projeto: ' . htmlspecialchars($projeto['nome']),
            'projeto' => $projeto,
            'submenu' => $submenu,
            'submenuView' => $submenuView,
            'submenuData' => $submenuData, // Passa os dados específicos do submenu para a view
        ]);
        $this->renderView('projetos/detalhe', $data);
    }

    /**
     * Exibe o formulário para adicionar um novo projeto.
     */
    public function novo()
    {
        $clientes = $this->clientesModel->getAllClientes();
        $data = [
            'pageTitle' => 'Novo Projeto',
            'projeto' => [
                'numero_projeto' => $this->model->getNextProjectNumber(),
                'status' => 'Planejado'
            ],
            'clientes' => $clientes
        ];
        $this->renderView('projetos/form', $data); // Assumindo que a view se chamará 'form.php'
    }

    /**
     * Salva um item do orçamento de um projeto.
     */
    public function salvarItemOrcamento()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/projetos');
            exit();
        }

        // Combina POST e FILES para o model
        $dados = array_merge($_POST, ['comprovante' => $_FILES['comprovante'] ?? null]);
        $projeto_id = $dados['projeto_id'];

        if ($this->model->salvarItemOrcamento($dados)) {
            $this->setFlashMessage('success', 'Item do orçamento salvo com sucesso!');
        } else {
            $this->setFlashMessage('error', 'Erro ao salvar o item do orçamento.');
        }

        header('Location: ' . BASE_URL . '/projetos/detalhe/' . $projeto_id . '/orcamento');
        exit();
    }

    /**
     * Exclui um item do orçamento de um projeto.
     * @param int $item_id O ID do item a ser excluído.
     * @param int $projeto_id O ID do projeto para redirecionamento.
     */
    public function excluirItemOrcamento(int $item_id, int $projeto_id)
    {
        if ($this->model->excluirItemOrcamento($item_id)) {
            $this->setFlashMessage('success', 'Item do orçamento excluído com sucesso!');
        } else {
            $this->setFlashMessage('error', 'Erro ao excluir o item do orçamento.');
        }
        header('Location: ' . BASE_URL . '/projetos/detalhe/' . $projeto_id . '/orcamento');
        exit();
    }

    /**
     * Salva um registro de ART/RRT de um projeto.
     */
    public function salvarArt()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/projetos');
            exit();
        }

        $dados = array_merge($_POST, [
            'documento_art' => $_FILES['documento_art'] ?? null,
            'boleto' => $_FILES['boleto'] ?? null,
            'comprovante_pgto' => $_FILES['comprovante_pgto'] ?? null,
        ]);
        $projeto_id = $dados['projeto_id'];

        if ($this->model->salvarArt($dados)) {
            $this->setFlashMessage('success', 'Registro de ART/RRT salvo com sucesso!');
        } else {
            $this->setFlashMessage('error', 'Erro ao salvar o registro de ART/RRT.');
        }

        header('Location: ' . BASE_URL . '/projetos/detalhe/' . $projeto_id . '/art');
        exit();
    }

    /**
     * Exclui um registro de ART/RRT.
     */
    public function excluirArt(int $art_id, int $projeto_id)
    {
        if ($this->model->excluirArt($art_id)) {
            $this->setFlashMessage('success', 'Registro de ART/RRT excluído com sucesso!');
        } else {
            $this->setFlashMessage('error', 'Erro ao excluir o registro.');
        }
        header('Location: ' . BASE_URL . '/projetos/detalhe/' . $projeto_id . '/art');
        exit();
    }

    /**
     * Salva um documento técnico (CDT) de um projeto.
     */
    public function salvarCDT()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/projetos');
            exit();
        }

        $dados = array_merge($_POST, ['documento' => $_FILES['documento'] ?? null]);
        $projeto_id = $dados['projeto_id'];

        // Validação básica
        if (empty($dados['nome_documento']) || empty($dados['tipo_documento']) || (empty($dados['id']) && empty($dados['documento']['name']))) {
            $this->setFlashMessage('error', 'Nome, Tipo e Arquivo são obrigatórios para um novo documento.');
            header('Location: ' . BASE_URL . '/projetos/detalhe/' . $projeto_id . '/cdt');
            exit();
        }

        if ($this->model->salvarCDT($dados)) {
            $this->setFlashMessage('success', 'Documento salvo com sucesso no CDT!');
        } else {
            $this->setFlashMessage('error', 'Erro ao salvar o documento.');
        }

        header('Location: ' . BASE_URL . '/projetos/detalhe/' . $projeto_id . '/cdt');
        exit();
    }

    /**
     * Exclui um documento técnico (CDT).
     */
    public function excluirCDT(int $cdt_id, int $projeto_id)
    {
        if ($this->model->excluirCDT($cdt_id)) {
            $this->setFlashMessage('success', 'Documento excluído com sucesso do CDT!');
        } else {
            $this->setFlashMessage('error', 'Erro ao excluir o documento.');
        }
        header('Location: ' . BASE_URL . '/projetos/detalhe/' . $projeto_id . '/cdt');
        exit();
    }

    /**
     * Salva um mapa (CM) de um projeto.
     */
    public function salvarMapa()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/projetos');
            exit();
        }

        $dados = array_merge($_POST, ['mapa_arquivo' => $_FILES['mapa_arquivo'] ?? null]);
        $projeto_id = $dados['projeto_id'];

        // Validação básica
        if (empty($dados['nome_mapa']) || empty($dados['categoria_mapa']) || (empty($dados['id']) && empty($dados['mapa_arquivo']['name']))) {
            $this->setFlashMessage('error', 'Nome, Categoria e Arquivo são obrigatórios para um novo mapa.');
            header('Location: ' . BASE_URL . '/projetos/detalhe/' . $projeto_id . '/mapas');
            exit();
        }

        if ($this->model->salvarMapa($dados)) {
            $this->setFlashMessage('success', 'Mapa salvo com sucesso!');
        } else {
            $this->setFlashMessage('error', 'Erro ao salvar o mapa.');
        }

        header('Location: ' . BASE_URL . '/projetos/detalhe/' . $projeto_id . '/mapas');
        exit();
    }

    /**
     * Exclui um mapa (CM).
     */
    public function excluirMapa(int $mapa_id, int $projeto_id)
    {
        if ($this->model->excluirMapa($mapa_id)) {
            $this->setFlashMessage('success', 'Mapa excluído com sucesso!');
        } else {
            $this->setFlashMessage('error', 'Erro ao excluir o mapa.');
        }
        header('Location: ' . BASE_URL . '/projetos/detalhe/' . $projeto_id . '/mapas');
        exit();
    }

    /**
     * Salva um arquivo geral do projeto.
     */
    public function salvarArquivo()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/projetos');
            exit();
        }

        $dados = array_merge($_POST, ['arquivo' => $_FILES['arquivo'] ?? null]);
        $projeto_id = $dados['projeto_id'];

        // Validação básica
        if (empty($dados['nome_arquivo']) || empty($dados['categoria']) || (empty($dados['id']) && empty($dados['arquivo']['name']))) {
            $this->setFlashMessage('error', 'Nome, Categoria e Arquivo são obrigatórios.');
            header('Location: ' . BASE_URL . '/projetos/detalhe/' . $projeto_id . '/arquivos');
            exit();
        }

        if ($this->model->salvarArquivo($dados)) {
            $this->setFlashMessage('success', 'Arquivo salvo com sucesso!');
        } else {
            $this->setFlashMessage('error', 'Erro ao salvar o arquivo.');
        }

        header('Location: ' . BASE_URL . '/projetos/detalhe/' . $projeto_id . '/arquivos');
        exit();
    }

    /**
     * Exclui um arquivo geral do projeto.
     */
    public function excluirArquivo(int $arquivo_id, int $projeto_id)
    {
        if ($this->model->excluirArquivo($arquivo_id)) {
            $this->setFlashMessage('success', 'Arquivo excluído com sucesso!');
        } else {
            $this->setFlashMessage('error', 'Erro ao excluir o arquivo.');
        }
        header('Location: ' . BASE_URL . '/projetos/detalhe/' . $projeto_id . '/arquivos');
        exit();
    }

    /**
     * Busca os detalhes de uma tarefa via AJAX.
     */
    public function getTarefa(int $id)
    {
        header('Content-Type: application/json');
        $tarefa = $this->tarefasModel->getTarefaById($id);
        if ($tarefa) {
            // Formata datas para exibição
            $tarefa['data_inicio_formatada'] = $tarefa['data_inicio'] ? date('d/m/Y', strtotime($tarefa['data_inicio'])) : '-';
            $tarefa['data_fim_formatada'] = $tarefa['data_fim'] ? date('d/m/Y', strtotime($tarefa['data_fim'])) : '-';
            echo json_encode(['success' => true, 'data' => $tarefa]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Tarefa não encontrada.']);
        }
        exit;
    }

    /**
     * Busca os detalhes de um projeto via AJAX (para o formulário de orçamento).
     */
    public function getProjetoDados(int $id)
    {
        header('Content-Type: application/json');
        $projeto = $this->model->getProjetoById($id);
        if ($projeto) {
            echo json_encode([
                'success' => true,
                'data' => [
                    'id' => $projeto['id'],
                    'nome' => $projeto['nome'] ?? $projeto['nome_projeto'] ?? '',
                    'cliente_nome' => $projeto['cliente_nome'] ?? '',
                    'cliente_sigla' => $projeto['cliente_sigla'] ?? '',
                    'cliente_id' => $projeto['cliente_id'] ?? '',
                    'responsavel_nome' => $projeto['responsavel_nome'] ?? '',
                    'responsavel_id' => $projeto['responsavel_id'] ?? '',
                    'tipo_servico' => $projeto['tipo_servico'] ?? $projeto['tipo'] ?? '',
                    'status' => $projeto['status'] ?? '',
                ]
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Projeto não encontrado.']);
        }
        exit;
    }

    /**
     * Conclui uma tarefa via AJAX (Dashboard).
     */
    public function concluirTarefaAjax()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método inválido.']);
            exit;
        }

        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $usuarioId = $this->session->get('user_id');

        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID da tarefa inválido.']);
            exit;
        }

        // Verifica dependências antes de concluir
        if (!$this->tarefasModel->checkDependenciasConcluidas($id)) {
            echo json_encode(['success' => false, 'message' => 'Não é possível concluir esta tarefa pois ela possui dependências pendentes.']);
            exit;
        }

        if ($this->tarefasModel->updateStatus($id, 'Concluída', $usuarioId)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erro ao atualizar status.']);
        }
        exit;
    }

    /**
     * Salva uma tarefa do projeto.
     */
    public function salvarTarefa()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/projetos');
            exit();
        }

        $dados = $_POST;
        $projeto_id = $dados['projeto_id'];

        // Busca tarefa antiga para comparar responsável (se for edição)
        $tarefaAntiga = null;
        if (!empty($dados['id'])) {
            $tarefaAntiga = $this->tarefasModel->getTarefaById($dados['id']);
        }

        // Validação de Dependências: Se tentar iniciar ou concluir, verifica se há bloqueios
        if (!empty($dados['id']) && in_array($dados['status'], ['Em Andamento', 'Concluída'])) {
            if (!$this->tarefasModel->checkDependenciasConcluidas($dados['id'])) {
                $this->setFlashMessage('error', 'Não é possível alterar o status desta tarefa pois ela possui dependências pendentes.');
                header('Location: ' . BASE_URL . '/projetos/detalhe/' . $projeto_id . '/tarefas');
                exit();
            }
        }

        $usuarioId = $this->session->get('user_id');
        if ($this->tarefasModel->salvar($dados, $usuarioId)) {
            $this->setFlashMessage('success', 'Tarefa salva com sucesso!');

            // Lógica de Notificação por E-mail
            $novoResponsavelId = !empty($dados['responsavel_id']) ? $dados['responsavel_id'] : null;

            // Se há um responsável e (é nova tarefa OU o responsável mudou)
            if ($novoResponsavelId && (!$tarefaAntiga || $tarefaAntiga['responsavel_id'] != $novoResponsavelId)) {
                $usuarioResponsavel = $this->usuarioModel->getUsuario($novoResponsavelId);
                $projeto = $this->model->getProjetoById($projeto_id);

                if ($usuarioResponsavel && !empty($usuarioResponsavel['email'])) {
                    $assunto = "Nova Tarefa Atribuída: " . $dados['titulo'];
                    $corpo = "Olá " . htmlspecialchars($usuarioResponsavel['nome']) . ",<br><br>";
                    $corpo .= "Uma nova tarefa foi atribuída a você no projeto <strong>" . htmlspecialchars($projeto['nome']) . "</strong>.<br><br>";
                    $corpo .= "<strong>Tarefa:</strong> " . htmlspecialchars($dados['titulo']) . "<br>";
                    $corpo .= "<strong>Prioridade:</strong> " . htmlspecialchars($dados['prioridade']) . "<br>";
                    if (!empty($dados['data_fim'])) {
                        $corpo .= "<strong>Prazo:</strong> " . date('d/m/Y', strtotime($dados['data_fim'])) . "<br>";
                    }
                    $corpo .= "<br>Acesse o sistema para mais detalhes.";

                    $this->enviarNotificacaoEmail($usuarioResponsavel['email'], $assunto, $corpo);

                    // Notificação no Sistema
                    $this->notificacoesModel->criarNotificacao(
                        (int)$novoResponsavelId,
                        'Nova Tarefa Atribuída',
                        "Você foi atribuído à tarefa '{$dados['titulo']}' no projeto.",
                        BASE_URL . "/projetos/detalhe/{$projeto_id}/tarefas"
                    );
                }
            }
        } else {
            $this->setFlashMessage('error', 'Erro ao salvar tarefa.');
        }

        header('Location: ' . BASE_URL . '/projetos/detalhe/' . $projeto_id . '/tarefas');
        exit();
    }

    public function excluirTarefa(int $id, int $projeto_id)
    {
        $usuarioId = $this->session->get('user_id');
        if ($this->tarefasModel->excluir($id, $usuarioId)) {
            $this->setFlashMessage('success', 'Tarefa excluída com sucesso!');
        } else {
            $this->setFlashMessage('error', 'Erro ao excluir tarefa.');
        }
        header('Location: ' . BASE_URL . '/projetos/detalhe/' . $projeto_id . '/tarefas');
        exit();
    }

    /**
     * Retorna os comentários de uma tarefa via AJAX.
     */
    public function getComentariosTarefa(int $tarefaId)
    {
        header('Content-Type: application/json');
        $comentarios = $this->tarefasModel->getComentariosByTarefaId($tarefaId);
        echo json_encode(['success' => true, 'data' => $comentarios]);
        exit;
    }

    /**
     * Salva um comentário via AJAX.
     */
    public function salvarComentario()
    {
        header('Content-Type: application/json');
        $tarefaId = filter_input(INPUT_POST, 'tarefa_id', FILTER_VALIDATE_INT);
        $comentario = filter_input(INPUT_POST, 'comentario', FILTER_SANITIZE_SPECIAL_CHARS);
        $usuarioId = $this->session->get('user_id');

        if ($tarefaId && $comentario && $usuarioId) {
            if ($this->tarefasModel->salvarComentario($tarefaId, $usuarioId, $comentario)) {

                // Notificação de comentário para o responsável da tarefa
                $tarefa = $this->tarefasModel->getTarefaById($tarefaId);
                // Se a tarefa tem responsável e quem comentou NÃO é o responsável
                if ($tarefa && !empty($tarefa['responsavel_email']) && $tarefa['responsavel_id'] != $usuarioId) {
                    $assunto = "Novo Comentário na Tarefa: " . $tarefa['titulo'];
                    $corpo = "Olá " . htmlspecialchars($tarefa['responsavel_nome']) . ",<br><br>";
                    $corpo .= "Houve um novo comentário na tarefa <strong>" . htmlspecialchars($tarefa['titulo']) . "</strong> do projeto <strong>" . htmlspecialchars($tarefa['projeto_nome']) . "</strong>.<br><br>";
                    $corpo .= "<strong>Comentário:</strong><br><em>" . nl2br(htmlspecialchars($comentario)) . "</em><br><br>";
                    $corpo .= "Acesse o sistema para responder.";

                    $this->enviarNotificacaoEmail($tarefa['responsavel_email'], $assunto, $corpo);

                    // Notificação no Sistema
                    $this->notificacoesModel->criarNotificacao(
                        (int)$tarefa['responsavel_id'],
                        'Novo Comentário',
                        "Novo comentário na tarefa '{$tarefa['titulo']}': " . mb_substr($comentario, 0, 50) . "...",
                        BASE_URL . "/projetos/detalhe/{$tarefa['projeto_id']}/tarefas"
                    );
                }

                echo json_encode(['success' => true]);
                exit;
            }
        }
        echo json_encode(['success' => false, 'message' => 'Erro ao salvar comentário.']);
        exit;
    }

    /**
     * Atualiza um comentário via AJAX.
     */
    public function atualizarComentario()
    {
        header('Content-Type: application/json');
        $comentarioId = filter_input(INPUT_POST, 'comentario_id', FILTER_VALIDATE_INT);
        $novoTexto = filter_input(INPUT_POST, 'texto', FILTER_SANITIZE_SPECIAL_CHARS);
        $usuarioId = $this->session->get('user_id');

        if ($comentarioId && $novoTexto && $usuarioId) {
            if ($this->tarefasModel->atualizarComentario($comentarioId, $novoTexto, $usuarioId)) {
                echo json_encode(['success' => true]);
                exit;
            }
        }
        echo json_encode(['success' => false, 'message' => 'Erro ao atualizar comentário ou permissão negada.']);
        exit;
    }

    /**
     * Exclui um comentário via AJAX.
     */
    public function excluirComentario(int $comentarioId)
    {
        header('Content-Type: application/json');
        $usuarioId = $this->session->get('user_id');

        if ($comentarioId && $usuarioId) {
            if ($this->tarefasModel->excluirComentario($comentarioId, $usuarioId)) {
                echo json_encode(['success' => true]);
                exit;
            }
        }
        echo json_encode(['success' => false, 'message' => 'Erro ao excluir comentário ou permissão negada.']);
        exit;
    }

    /**
     * Método auxiliar para envio de e-mail usando PHPMailer.
     */
    private function enviarNotificacaoEmail($destinatario, $assunto, $corpo)
    {
        // Verifica se as constantes de e-mail estão definidas (normalmente em config.php)
        if (!defined('MAIL_HOST')) return false;

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = MAIL_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = MAIL_USERNAME;
            $mail->Password   = MAIL_PASSWORD;
            $mail->SMTPSecure = (defined('MAIL_ENCRYPTION') && MAIL_ENCRYPTION === 'ssl') ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = MAIL_PORT;
            $mail->CharSet    = 'UTF-8';

            $mail->setFrom(MAIL_FROM_ADDRESS, MAIL_FROM_NAME);
            $mail->addAddress($destinatario);

            $mail->isHTML(true);
            $mail->Subject = $assunto;
            $mail->Body    = $corpo;

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Erro ao enviar notificação por e-mail: {$mail->ErrorInfo}");
            return false;
        }
    }

    /**
     * Gera o relatório de tarefas em PDF agrupado por Status e Responsável.
     */
    public function relatorioTarefasPdf(int $projetoId)
    {
        $projeto = $this->model->getProjetoById($projetoId);
        if (!$projeto) {
            $this->setFlashMessage('error', 'Projeto não encontrado.');
            header('Location: ' . BASE_URL . '/projetos');
            exit();
        }

        // Busca todas as tarefas (sem filtros de paginação)
        $tarefas = $this->tarefasModel->getTarefasByProjetoId($projetoId);

        // Agrupamento manual por Status e depois por Responsável
        $tarefasAgrupadas = [];
        foreach ($tarefas as $tarefa) {
            $status = $tarefa['status'] ?: 'Sem Status';
            $responsavel = $tarefa['responsavel_nome'] ?: 'Não Atribuído';

            if (!isset($tarefasAgrupadas[$status])) {
                $tarefasAgrupadas[$status] = [];
            }
            if (!isset($tarefasAgrupadas[$status][$responsavel])) {
                $tarefasAgrupadas[$status][$responsavel] = [];
            }
            $tarefasAgrupadas[$status][$responsavel][] = $tarefa;
        }

        $empresa = $this->empresaModel->getDadosEmpresa();

        $data = [
            'projeto' => $projeto,
            'tarefasAgrupadas' => $tarefasAgrupadas,
            'dataGeracao' => date('d/m/Y H:i:s'),
            'empresa' => $empresa
        ];

        ob_start();
        $this->renderPartial('projetos/relatorios/tarefas_pdf', $data);
        $html = ob_get_clean();

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream("relatorio_tarefas_projeto_{$projetoId}.pdf", ["Attachment" => false]);
        exit();
    }

    // --- Métodos para Checklist ---

    public function getChecklist(int $tarefaId)
    {
        header('Content-Type: application/json');
        $checklist = $this->tarefasModel->getChecklistByTarefaId($tarefaId);
        echo json_encode(['success' => true, 'data' => $checklist]);
        exit;
    }

    public function salvarChecklistItem()
    {
        header('Content-Type: application/json');
        $usuarioId = $this->session->get('user_id');
        $tarefaId = filter_input(INPUT_POST, 'tarefa_id', FILTER_VALIDATE_INT);
        $descricao = filter_input(INPUT_POST, 'descricao', FILTER_SANITIZE_SPECIAL_CHARS);

        if ($tarefaId && $descricao) {
            if ($this->tarefasModel->addChecklistItem($tarefaId, $descricao, $usuarioId)) {
                echo json_encode(['success' => true]);
                exit;
            }
        }
        echo json_encode(['success' => false]);
        exit;
    }

    public function toggleChecklistItem()
    {
        header('Content-Type: application/json');
        $usuarioId = $this->session->get('user_id');
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $status = filter_input(INPUT_POST, 'status', FILTER_VALIDATE_INT);
        echo json_encode(['success' => $this->tarefasModel->toggleChecklistItem($id, $status, $usuarioId)]);
        exit;
    }

    public function excluirChecklistItem(int $id)
    {
        header('Content-Type: application/json');
        $usuarioId = $this->session->get('user_id');
        echo json_encode(['success' => $this->tarefasModel->deleteChecklistItem($id, $usuarioId)]);
        exit;
    }

    // --- Métodos para Dependências ---

    public function getDependencias(int $tarefaId)
    {
        header('Content-Type: application/json');
        $dependencias = $this->tarefasModel->getDependencias($tarefaId);
        echo json_encode(['success' => true, 'data' => $dependencias]);
        exit;
    }

    public function salvarDependencia()
    {
        header('Content-Type: application/json');
        $tarefaId = filter_input(INPUT_POST, 'tarefa_id', FILTER_VALIDATE_INT);
        $dependenciaId = filter_input(INPUT_POST, 'dependencia_id', FILTER_VALIDATE_INT);

        if ($tarefaId && $dependenciaId) {
            if ($this->tarefasModel->addDependencia($tarefaId, $dependenciaId)) {
                echo json_encode(['success' => true]);
                exit;
            }
        }
        echo json_encode(['success' => false, 'message' => 'Erro ao adicionar dependência.']);
        exit;
    }

    public function excluirDependencia(int $id)
    {
        header('Content-Type: application/json');
        echo json_encode(['success' => $this->tarefasModel->removeDependencia($id)]);
        exit;
    }

    // --- Métodos para Tags ---

    public function salvarTag()
    {
        header('Content-Type: application/json');
        $dados = [
            'id' => filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT),
            'projeto_id' => filter_input(INPUT_POST, 'projeto_id', FILTER_VALIDATE_INT),
            'nome' => filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_SPECIAL_CHARS),
            'cor' => filter_input(INPUT_POST, 'cor', FILTER_SANITIZE_SPECIAL_CHARS),
        ];

        if ($this->tarefasModel->salvarTag($dados)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erro ao salvar tag.']);
        }
        exit;
    }

    public function excluirTag(int $id)
    {
        header('Content-Type: application/json');
        echo json_encode(['success' => $this->tarefasModel->excluirTag($id)]);
        exit;
    }

    // ... (outros métodos)


    /**
     * Salva um novo projeto ou atualiza um existente.
     */
    public function salvar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/projetos');
            exit();
        }

        $dados = $_POST;
        $id = !empty($dados['id']) ? (int)$dados['id'] : null;

        // Converte orcamento do formato brasileiro (1.234,56 → 1234.56)
        if (!empty($dados['orcamento'])) {
            $str = trim($dados['orcamento']);
            $str = preg_replace('/[^\d\-,.]/', '', $str);
            if (strpos($str, '.') !== false && strpos($str, ',') !== false) {
                $str = str_replace('.', '', $str);
                $str = str_replace(',', '.', $str);
            } elseif (strpos($str, ',') !== false) {
                $str = str_replace(',', '.', $str);
            }
            $dados['orcamento'] = (float) $str;
        }
        $numero_projeto = trim($dados['numero_projeto'] ?? '');

        // Validação básica de campos obrigatórios
        if (empty(trim($dados['nome'])) || empty(trim($dados['cliente_id']))) {
            $this->setFlashMessage('error', 'Nome do projeto e Cliente são campos obrigatórios.');
            // Em caso de falha, redireciona de volta para a página de origem.
            $fallbackUrl = $id ? (BASE_URL . '/projetos/editar/' . $id) : (BASE_URL . '/projetos');
            header('Location: ' . $fallbackUrl);
            exit();
        }

        // Validação de duplicidade do número do projeto
        if (!empty($numero_projeto) && $this->model->numeroProjetoExiste($numero_projeto, $id)) {
            $this->setFlashMessage('error', "O código de projeto '{$numero_projeto}' já está em uso por outro registro.");
            // Se for novo, volta para 'novo', se for edição, volta para 'editar'
            $fallbackUrl = $id ? (BASE_URL . '/projetos/editar/' . $id) : (BASE_URL . '/projetos/novo');
            header('Location: ' . $fallbackUrl);
            exit();
        }

        // Validação de Coordenadas (Latitude: -90 a 90, Longitude: -180 a 180)
        $lat = $this->parseCoordinate($dados['latitude'] ?? '');
        $lng = $this->parseCoordinate($dados['longitude'] ?? '');

        if (($lat !== null && ($lat < -90 || $lat > 90)) || ($lng !== null && ($lng < -180 || $lng > 180))) {
            $this->setFlashMessage('error', 'As coordenadas informadas são inválidas. Latitude deve ser entre -90 e 90, e Longitude entre -180 e 180.');
            $fallbackUrl = $id ? (BASE_URL . '/projetos/editar/' . $id) : (BASE_URL . '/projetos');
            header('Location: ' . $fallbackUrl);
            exit();
        }

        // Atualiza os dados processados para o salvamento
        $dados['latitude'] = $lat;
        $dados['longitude'] = $lng;

        try {
            // O método salvarProjeto agora deve retornar o ID do projeto salvo/atualizado ou false em caso de erro.
            $savedId = $this->model->salvarProjeto($dados);

            if ($savedId) {
                $this->setFlashMessage('success', 'Projeto salvo com sucesso!');
                // Se for uma edição, redireciona para a página de detalhes do projeto.
                // Se for uma criação, redireciona para a lista de projetos.
                $redirectUrl = $id ? (BASE_URL . '/projetos/detalhe/' . $savedId) : (BASE_URL . '/projetos');
                header('Location: ' . $redirectUrl);
                exit();
            } else {
                $this->setFlashMessage('error', 'Ocorreu um erro ao salvar o projeto.');
            }
        } catch (\PDOException $e) {
            // Captura o erro do banco de dados e o exibe na tela para depuração.
            $this->setFlashMessage('error', 'Erro de Banco de Dados: ' . $e->getMessage());
        }

        // Em caso de falha, redireciona de volta para a página de origem.
        $fallbackUrl = $id ? (BASE_URL . '/projetos/editar/' . $id) : (BASE_URL . '/projetos');
        header('Location: ' . $fallbackUrl);
        exit();
    }

    /**
     * Exibe o formulário para editar um projeto existente.
     * @param int $id O ID do projeto.
     */
    public function editar(int $id)
    {
        // Busca o projeto específico no banco de dados
        $projeto = $this->model->getProjetoById($id);

        // Se o projeto não for encontrado, redireciona com uma mensagem de erro
        if (!$projeto) {
            $this->setFlashMessage('error', 'Projeto não encontrado.');
            header('Location: ' . BASE_URL . '/projetos');
            exit();
        }

        // O formulário de edição precisa da lista de clientes para o dropdown
        $clientes = $this->clientesModel->getAllClientes();

        // Prepara os dados para enviar para a view
        $data = [
            'pageTitle' => 'Editar Projeto',
            'projeto'   => $projeto,
            'clientes'  => $clientes
        ];

        // Renderiza a view do formulário, passando os dados do projeto e dos clientes
        $this->renderView('projetos/form', $data);
    }

    /**
     * Exclui um projeto.
     * @param int $id O ID do projeto.
     */
    public function excluir(int $id)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $resultado = $this->model->excluirProjeto($id);

            if ($resultado === 'deleted') {
                $this->setFlashMessage('success', 'Projeto excluído permanentemente com sucesso!');
            } elseif ($resultado === 'archived') {
                $this->setFlashMessage('info', 'O projeto foi arquivado (movido para "Cancelado") pois possui tarefas ou registros financeiros vinculados.');
            } else {
                $erro = $this->model->getLastError() ?? 'Erro ao processar a solicitação.';
                $this->setFlashMessage('error', $erro);
            }
        }
        header('Location: ' . BASE_URL . '/projetos');
        exit();
    }

    /**
     * Restaura um projeto cancelado.
     * @param int $id O ID do projeto.
     */
    public function restaurar(int $id)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $this->model->restaurarProjeto($id)) {
            $this->setFlashMessage('success', 'Projeto restaurado com sucesso!');
        } else {
            $this->setFlashMessage('error', 'Erro ao restaurar o projeto.');
        }
        header('Location: ' . BASE_URL . '/projetos/cancelados');
        exit();
    }

    /**
     * Exibe a lista de projetos arquivados (concluídos).
     */
    public function arquivados()
    {
        // Coleta filtros da URL (para permitir filtrar por responsável ou sub-status)
        $filtros = [
            'status' => filter_input(INPUT_GET, 'status', FILTER_SANITIZE_SPECIAL_CHARS),
            'responsavel' => filter_input(INPUT_GET, 'responsavel', FILTER_SANITIZE_SPECIAL_CHARS),
        ];

        // Default: mostra ambos os status arquivados
        if (empty($filtros['status']) || $filtros['status'] === 'Todos Ativos') {
            $filtros['status'] = ['Concluído', 'Cancelado'];
        }

        // Lógica de Paginação
        $paginaAtual = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1;
        $itensPorPagina = 10; // Aumentamos para 10 itens por página na listagem de arquivados
        $offset = ($paginaAtual - 1) * $itensPorPagina;

        // Busca os projetos arquivados no modelo
        $projetos = $this->model->getProjetos($filtros, $itensPorPagina, $offset);
        $totalProjetos = $this->model->getProjetosCount($filtros);
        $totalPaginas = ceil($totalProjetos / $itensPorPagina);

        // Reutiliza o resumo geral (não zerar os cards) para manter as contagens informativas
        $summary = $this->model->getProjetosSummary();

        // Busca os clientes para o formulário da modal
        $clientes = $this->clientesModel->getAllClientes();

        // Prepara os dados para a view, mantendo os cards com valores reais
        $data = array_merge($summary, [
            'pageTitle' => 'Projetos Arquivados',
            'projetos' => $projetos,
            'paginaAtual' => $paginaAtual,
            'totalPaginas' => $totalPaginas,
            'filtros' => $filtros,
            'baseUrl' => '/projetos/arquivados', // Define a URL base para a paginação e links
            'clientes' => $clientes, // Adiciona os clientes para a modal
        ]);

        $this->renderView('projetos/index', $data);
    }

    /**
     * Exibe a lista de projetos cancelados.
     */
    public function cancelados()
    {
        // Coleta filtros da URL (para permitir filtrar por responsável ou sub-status)
        $filtros = [
            'status' => filter_input(INPUT_GET, 'status', FILTER_SANITIZE_SPECIAL_CHARS),
            'responsavel' => filter_input(INPUT_GET, 'responsavel', FILTER_SANITIZE_SPECIAL_CHARS),
        ];

        // Default: mostra apenas projetos cancelados
        if (empty($filtros['status']) || $filtros['status'] === 'Todos Ativos') {
            $filtros['status'] = 'Cancelado';
        }

        // Lógica de Paginação
        $paginaAtual = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1;
        $itensPorPagina = 10;
        $offset = ($paginaAtual - 1) * $itensPorPagina;

        // Busca os projetos cancelados no modelo
        $projetos = $this->model->getProjetos($filtros, $itensPorPagina, $offset);
        $totalProjetos = $this->model->getProjetosCount($filtros);
        $totalPaginas = ceil($totalProjetos / $itensPorPagina);

        // Reutiliza o resumo geral
        $summary = $this->model->getProjetosSummary();

        // Busca os clientes para o formulário da modal
        $clientes = $this->clientesModel->getAllClientes();

        // Prepara os dados para a view
        $data = array_merge($summary, [
            'pageTitle' => 'Projetos Cancelados',
            'projetos' => $projetos,
            'paginaAtual' => $paginaAtual,
            'totalPaginas' => $totalPaginas,
            'filtros' => $filtros,
            'baseUrl' => '/projetos/cancelados',
            'clientes' => $clientes,
        ]);

        $this->renderView('projetos/index', $data);
    }

    /**
     * Converte coordenadas de GMS (Graus, Minutos, Segundos) para Graus Decimais.
     * Suporta formatos como: 2°39'11.35", -23.55, 23°33'S
     */
    private function parseCoordinate($coord): ?float
    {
        $coord = trim((string)$coord);
        if (empty($coord)) return null;

        // Se já for um número decimal puro, retorna como float
        if (is_numeric(str_replace(',', '.', $coord))) {
            return (float)str_replace(',', '.', $coord);
        }

        // Expressão regular para capturar Graus, Minutos, Segundos e Direção (Opcional)
        // Suporta os símbolos comuns de graus, minutos e segundos.
        $pattern = '/^([+-]?\d+)[°º\s]+(?:(\d+)\'[\s]*)?(?:([\d.]+)"[\s]*)?([NSEW])?$/i';

        if (preg_match($pattern, $coord, $matches)) {
            $degrees = (float)$matches[1];
            $minutes = isset($matches[2]) ? (float)$matches[2] : 0;
            $seconds = isset($matches[3]) ? (float)$matches[3] : 0;
            $direction = isset($matches[4]) ? strtoupper($matches[4]) : '';

            $decimal = abs($degrees) + ($minutes / 60) + ($seconds / 3600);
            
            // Se a direção for Sul (S) ou Oeste (W), ou se o valor original for negativo, o resultado é negativo.
            if ($direction === 'S' || $direction === 'W' || $degrees < 0) {
                $decimal *= -1;
            }
            return (float)$decimal;
        }

        return null;
    }
}
