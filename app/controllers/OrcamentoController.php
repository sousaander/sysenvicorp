<?php

namespace App\Controllers;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Dompdf\Dompdf;
use Dompdf\Options;
use setasign\Fpdi\Fpdi;
use App\Core\Connection;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use App\Models\PropostaModel;
use App\Models\ProjetosModel;
use App\Models\ClientesModel;
use App\Models\ContratosModel;
use App\Models\UsuarioModel;
use App\Models\EmpresaModel;
use App\Models\PerfilModel;
use App\Models\NotificacoesModel;
use App\Models\BancoModel;

/**
 * Controlador para a seção de Orçamentos do sistema.
 */
class OrcamentoController extends BaseController
{
    /** @var \App\Models\PropostaModel|null */
    private $propostaModel;
    /** @var \App\Models\ProjetosModel|null */
    private $projetosModel;
    /** @var \App\Models\ContratosModel|null */
    private $contratosModel;
    /** @var \App\Models\ClientesModel|null */
    private $clientesModel;
    /** @var \App\Models\UsuarioModel|null */
    private $usuarioModel;
    /** @var \App\Models\PerfilModel|null */
    private $perfilModel;
    /** @var \App\Models\EmpresaModel|null */
    private $empresaModel;
    /** @var \App\Models\NotificacoesModel|null */
    private $notificacoesModel;
    /** @var \App\Models\BancoModel|null */
    private $bancoModel;

    /**
     * Mapeia ações para as permissões necessárias.
     * Garante que apenas usuários autorizados vejam as telas.
     */
    protected $requiredPermissions = [
        'index'                 => 'comercial_propostas_view',
        'propostas'             => 'comercial_propostas_view',
        'ver'                   => 'comercial_propostas_view',
        'editar'                => 'projetos_orcamento_manage',
        'novo'                  => 'projetos_orcamento_manage',
        'salvar'                => 'projetos_orcamento_manage',
        'clonar'                => 'projetos_orcamento_manage',
        'excluir'               => 'projetos_orcamento_manage',
        'pdf'                   => 'comercial_propostas_view',
        'historico'             => 'comercial_propostas_view',
        'verHistoricoDetalhe'   => 'comercial_propostas_view',
        'gerarLinkPublico'      => 'comercial_propostas_view',
        'enviarEmail'           => 'orcamento_send',
        'updateStatusAjax'      => 'comercial_propostas_view',
        'getOrcamentosAjax'     => 'comercial_propostas_view',
        'getContratosAjax'      => 'comercial_propostas_view',
        'getProjectDetailsAjax' => 'comercial_propostas_view',
        'getProximoNumeroAjax'  => 'comercial_propostas_view',
        'enviarParaDiretorAjax' => 'comercial_propostas_view',
        'aprovarDiretorAjax'    => 'comercial_propostas_view',
        'rejeitarDiretorAjax'   => 'comercial_propostas_view',
        'getDiretorModalAjax'   => 'comercial_propostas_view',
        'addItemCategoriaAjax'  => 'projetos_orcamento_manage',
        'addItemUnidadeAjax'    => 'projetos_orcamento_manage',
        'gerenciarItens'        => 'projetos_orcamento_manage',
        'updateItemCategoriaAjax' => 'projetos_orcamento_manage',
        'deleteItemCategoriaAjax' => 'projetos_orcamento_manage',
        'updateItemUnidadeAjax'   => 'projetos_orcamento_manage',
        'deleteItemUnidadeAjax'   => 'projetos_orcamento_manage',
    ];

        public function __construct()
        {
            parent::__construct();

            // Garante que apenas a ação pública de aprovação seja acessível sem login
            $action = strtolower($this->getCurrentActionName());
            if ($action !== 'aprovarpropostapublica' && !$this->session->isAuthenticated()) {
                header('Location: ' . BASE_URL . '/auth/login?next=' . urlencode($_SERVER['REQUEST_URI']));
                exit();
            }

            $this->propostaModel = new PropostaModel();
            $this->projetosModel = new ProjetosModel();
            $this->contratosModel = new ContratosModel();
            $this->clientesModel = new ClientesModel();
            $this->usuarioModel = new UsuarioModel();
            $this->perfilModel = new PerfilModel();
            $this->empresaModel = new EmpresaModel();
            $this->notificacoesModel = new NotificacoesModel();
            $this->bancoModel = new BancoModel();
        }

        /**
         * Lista as propostas existentes.
         * Alias para a ação 'propostas' que outras partes do sistema utilizam.
         */
        public function propostas()
        {
            // Lógica de Paginação
            $paginaAtual = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1;
            $itensPorPagina = 10;
            $offset = ($paginaAtual - 1) * $itensPorPagina;

            // Busca propostas com dados completos (compatibilidade mantida)
            $propostas = $this->propostaModel->getPropostas($itensPorPagina, $offset);
            $totalPropostas = $this->propostaModel->getPropostasCount();
            $totalPaginas = ceil($totalPropostas / $itensPorPagina);

            // Filtra propostas com ID inválido (0 ou negativo)
            $propostas = array_filter($propostas, function($p) {
                return isset($p['id']) && (int)$p['id'] > 0;
            });

            $propostas = array_map([$this, 'prepareOrcamentoData'], $propostas);

            $statusLabels = [
                'Rascunho' => ['label' => 'Rascunho', 'cor' => 'gray'],
                'Enviada'  => ['label' => 'Enviada', 'cor' => 'sky'],
                'Aprovada' => ['label' => 'Aprovada', 'cor' => 'emerald'],
                'Rejeitada'=> ['label' => 'Rejeitada', 'cor' => 'red'],
            ];

            $diretorStatusLabels = [
                'nao_solicitado' => ['label' => 'Não Enviado', 'cor' => 'gray'],
                'pendente'       => ['label' => 'Pendente', 'cor' => 'amber'],
                'aprovado'       => ['label' => 'Aprovado', 'cor' => 'emerald'],
                'rejeitado'      => ['label' => 'Rejeitado', 'cor' => 'red'],
            ];

            $data = [
                'pageTitle' => 'Propostas Comerciais',
                'propostas' => $propostas, // 'proposta.php' utiliza $propostas
                'statusLabels' => $statusLabels,
                'diretorStatusLabels' => $diretorStatusLabels,
                'paginaAtual' => $paginaAtual,
                'totalPaginas' => $totalPaginas,
                'csrf_token' => $this->generateCsrfToken(), // Token CSRF necessário
                'isAdmin' => $this->session->isAdmin(),
            ];

            // Renderiza view independente (sem template principal)
            extract($data);
            require ROOT_PATH . '/views/orcamento/proposta.php';
        }

        /**
         * Lista as propostas existentes.
         */
        public function index()
        {
            // Lógica de Paginação
            $paginaAtual = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1;
            $itensPorPagina = 10;
            $offset = ($paginaAtual - 1) * $itensPorPagina;

            // Busca propostas com dados completos (compatibilidade mantida)
            $propostas = $this->propostaModel->getPropostas($itensPorPagina, $offset);
            $totalPropostas = $this->propostaModel->getPropostasCount();
            $totalPaginas = ceil($totalPropostas / $itensPorPagina);

            // Filtra propostas com ID inválido (0 ou negativo)
            $propostas = array_filter($propostas, function($p) {
                return isset($p['id']) && (int)$p['id'] > 0;
            });

            $propostas = array_map([$this, 'prepareOrcamentoData'], $propostas);

            $statusLabels = [
                'Rascunho' => ['label' => 'Rascunho', 'cor' => 'gray'],
                'Enviada'  => ['label' => 'Enviada', 'cor' => 'sky'],
                'Aprovada' => ['label' => 'Aprovada', 'cor' => 'emerald'],
                'Rejeitada'=> ['label' => 'Rejeitada', 'cor' => 'red'],
            ];

            $diretorStatusLabels = [
                'nao_solicitado' => ['label' => 'Não Enviado', 'cor' => 'gray'],
                'pendente'       => ['label' => 'Pendente', 'cor' => 'amber'],
                'aprovado'       => ['label' => 'Aprovado', 'cor' => 'emerald'],
                'rejeitado'      => ['label' => 'Rejeitado', 'cor' => 'red'],
            ];

            $empresa = $this->empresaModel->getDadosEmpresa();
            $userEmail = $this->session->get('user_email', '');
            $data = [
                'pageTitle' => 'Propostas',
                'orcamentos' => $propostas,
                'statusLabels' => $statusLabels,
                'diretorStatusLabels' => $diretorStatusLabels,
                'paginaAtual' => $paginaAtual,
                'totalPaginas' => $totalPaginas,
                'isAdmin' => $this->session->isAdmin(),
                'empresa' => $empresa,
                'userEmail' => $userEmail,
            ];
            $this->renderView('orcamento/lista', $data);
        }

        /**
         * Exibe o formulário para criar uma nova proposta.
         */
        public function novo()
        {
            // Se um ID foi passado via GET (como em links legados novo?id=123),
            // redireciona para a ação de editar para garantir que os dados sejam carregados.
            $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
            if ($id) {
                header('Location: ' . BASE_URL . '/orcamento/editar/' . $id);
                exit();
            }

            $proposta = [];
            $pageTitle = 'Nova Proposta';

            // Define o usuário logado como responsável padrão para novas propostas
            $proposta['responsavel_interno_id'] = $this->session->get('user_id');

            // Busca a lista de projetos para o dropdown
            $projetos = $this->projetosModel->getAllProjetosParaSelect();
            $clientes = $this->clientesModel->getAllClientes(); // Para a seção "Criar do Zero"
            $contratos = $this->contratosModel->getContratos([], 999, 0);
            // Busca todos os usuários ativos para seleção (integração com o organograma da empresa)
            $usuarios = $this->usuarioModel->getListaUsuarios('Ativo');

            $condicoes = [
                ['id' => 1, 'descricao' => '30 dias após aprovação'],
                ['id' => 2, 'descricao' => '50% na aprovação / 50% na entrega'],
                ['id' => 3, 'descricao' => 'À vista'],
                ['id' => 4, 'descricao' => '100% após a conclusão'],
                ['id' => 5, 'descricao' => 'Parcelado (negociar)'],
                ['id' => 6, 'descricao' => 'Conforme contrato'],
            ];

            $data = [
                'pageTitle' => $pageTitle,
                'orc' => $proposta, // 'formulario.php' utiliza $orc
                'projetos' => $projetos,
                'clientes' => $clientes,
                'contratos' => $contratos,
                'usuarios' => $usuarios, // Responsáveis internos
                'condicoes' => $condicoes,
                'categorias' => $this->propostaModel->getItemCategorias(),
                'unidades' => $this->propostaModel->getItemUnidades(),
                'bancos' => $this->bancoModel->getAll(),
                'isAdmin' => $this->session->isAdmin(),
            ];

            // Se a requisição for via AJAX (da modal), renderiza só o formulário.
            $view = 'orcamento/formulario';
            isset($_GET['ajax']) && $_GET['ajax'] == 1 ? $this->renderPartial($view, $data) : $this->renderView($view, $data);
        }

        /**
         * Verifica se a proposta está bloqueada para edição.
         * Propostas aprovadas ficam bloqueadas, liberadas apenas para administradores.
         */
        private function isPropostaLocked(array $proposta): bool
        {
            return ($proposta['status'] ?? '') === 'Aprovada' && !$this->session->isAdmin();
        }

        /**
         * Exibe o formulário para editar uma proposta existente.
         * @param int $id O ID da proposta a ser editada.
         */
        public function editar($id)
        {
            $id = (int)$id;
            $proposta = $this->propostaModel->getPropostaById($id);
            if (!$proposta) {
                $this->setFlashMessage('error', 'Proposta não encontrada.');
                header('Location: ' . BASE_URL . '/orcamento/index');
                exit();
            }

            if ($this->isPropostaLocked($proposta)) {
                if (isset($_GET['ajax']) && $_GET['ajax'] == 1) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Proposta aprovada bloqueada para edição.']);
                    exit();
                }
                $this->setFlashMessage('error', 'Esta proposta foi aprovada e está bloqueada para edição. Apenas administradores podem editá-la.');
                header('Location: ' . BASE_URL . '/orcamento/ver/' . $id);
                exit();
            }

            $pageTitle = 'Editar Proposta';
            // Decodifica os JSONs para o formulário
            $proposta = $this->prepareOrcamentoData($proposta);

            // Busca a lista de projetos para o dropdown
            $projetos = $this->projetosModel->getAllProjetosParaSelect();
            $clientes = $this->clientesModel->getAllClientes(); // Para a seção "Criar do Zero"
            $contratos = $this->contratosModel->getContratos([], 999, 0);
            // Busca todos os usuários ativos para seleção (integração com o organograma da empresa)
            $usuarios = $this->usuarioModel->getListaUsuarios('Ativo');

            $condicoes = [
                ['id' => 1, 'descricao' => '30 dias após aprovação'],
                ['id' => 2, 'descricao' => '50% na aprovação / 50% na entrega'],
                ['id' => 3, 'descricao' => 'À vista'],
                ['id' => 4, 'descricao' => '100% após a conclusão'],
                ['id' => 5, 'descricao' => 'Parcelado (negociar)'],
                ['id' => 6, 'descricao' => 'Conforme contrato'],
            ];

            $data = [
                'pageTitle' => $pageTitle,
                'orc' => $proposta, // 'formulario.php' utiliza $orc
                'projetos' => $projetos,
                'clientes' => $clientes,
                'contratos' => $contratos,
                'usuarios' => $usuarios, // Responsáveis internos
                'condicoes' => $condicoes,
                'categorias' => $this->propostaModel->getItemCategorias(),
                'unidades' => $this->propostaModel->getItemUnidades(),
                'bancos' => $this->bancoModel->getAll(),
                'isAdmin' => $this->session->isAdmin(),
            ];

            // Se a requisição for via AJAX (da modal), renderiza só o formulário.
            $view = 'orcamento/formulario';
            isset($_GET['ajax']) && $_GET['ajax'] == 1 ? $this->renderPartial($view, $data) : $this->renderView($view, $data);
        }

        /**
         * Carrega o formulário com os dados de uma proposta existente para clonagem.
         * @param int $id O ID da proposta a ser clonada.
         */
        public function clonar($id)
        {
            $id = (int)$id;
            $propostaOriginal = $this->propostaModel->getPropostaById($id);

            if (!$propostaOriginal) {
                $this->setFlashMessage('error', 'Proposta original não encontrada para clonagem.');
                header('Location: ' . BASE_URL . '/orcamento/index');
                exit();
            }

            // Prepara os dados para a nova proposta clonada
            $propostaClonada = $this->prepareOrcamentoData($propostaOriginal);
            unset($propostaClonada['id']); // Remove o ID para criar uma nova
            $propostaClonada['nome_proposta'] = $propostaOriginal['nome_proposta'] . ' (Cópia)';
            $propostaClonada['titulo'] = $propostaClonada['nome_proposta'];
            $propostaClonada['status'] = 'Rascunho'; // Define o status inicial
            $propostaClonada['token_aprovacao'] = null; // Remove token de aprovação
            $propostaClonada['token_validade'] = null; // Remove validade do token

            // Ao clonar, define o usuário atual como o novo responsável pelo rascunho
            $propostaClonada['responsavel_interno_id'] = $this->session->get('user_id');

            $projetos = $this->projetosModel->getAllProjetosParaSelect();
            $clientes = $this->clientesModel->getAllClientes(); // Para a seção "Criar do Zero"
            $contratos = $this->contratosModel->getContratos([], 999, 0);
            // Busca todos os usuários ativos para seleção (integração com o organograma da empresa)
            $usuarios = $this->usuarioModel->getListaUsuarios('Ativo');
            $condicoes = [
                ['id' => 1, 'descricao' => '30 dias após aprovação'],
                ['id' => 2, 'descricao' => '50% na aprovação / 50% na entrega'],
                ['id' => 3, 'descricao' => 'À vista'],
                ['id' => 4, 'descricao' => '100% após a conclusão'],
                ['id' => 5, 'descricao' => 'Parcelado (negociar)'],
                ['id' => 6, 'descricao' => 'Conforme contrato'],
            ];

            $data = [
                'pageTitle' => 'Clonar Proposta',
                'orc' => $propostaClonada, // 'formulario.php' utiliza $orc
                'projetos' => $projetos,
                'clientes' => $clientes,
                'contratos' => $contratos,
                'usuarios' => $usuarios,
                'condicoes' => $condicoes,
                'bancos' => $this->bancoModel->getAll(),
                'isAdmin' => $this->session->isAdmin(),
            ];

            // Renderiza o formulário dentro da modal (ou em página cheia, se acessado diretamente)
            $view = 'orcamento/formulario';
            isset($_GET['ajax']) && $_GET['ajax'] == 1 ? $this->renderPartial($view, $data) : $this->renderView($view, $data);
        }

        /**
         * Exclui uma proposta permanentemente.
         * @param int $id O ID da proposta a ser excluída.
         */
        public function excluir($id = null)
        {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $this->setFlashMessage('error', 'Operação inválida.');
                header('Location: ' . BASE_URL . '/orcamento/index');
                exit();
            }

            // Validação de CSRF
            if (!isset($_POST['csrf_token']) || !$this->validateCsrfToken($_POST['csrf_token'])) {
                $this->setFlashMessage('error', 'Erro de validação de segurança (CSRF).');
                header('Location: ' . BASE_URL . '/orcamento/index');
                exit();
            }

            // Tentamos obter o ID de todas as fontes possíveis.
            // Usamos $_POST/$_GET diretamente para melhor compatibilidade com diferentes servidores.
            $idRaw = $id ?? ($_POST['id'] ?? ($_GET['id'] ?? null));
            $idRaw = is_string($idRaw) ? trim($idRaw) : $idRaw;

            $idFinal = filter_var($idRaw, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

            // Fallback robusto pela URI: procura por um número precedido por barra,
            // permitindo ou não uma barra final (ex: /42 ou /42/)
            if (!$idFinal || $idFinal <= 0) {
                $path = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
                if (preg_match('/\/(\d+)\/?$/', $path, $matches)) {
                    $idFinal = (int)$matches[1];
                }
            }

            if (!$idFinal || $idFinal <= 0) {
                error_log('OrcamentoController::excluir: ID inválido recebido para exclusão: ' . var_export($idRaw, true));
                $this->setFlashMessage('error', 'ID de proposta inválido para exclusão.');
                header('Location: ' . BASE_URL . '/orcamento/index');
                exit();
            }

            // Verifica se a proposta está bloqueada (aprovada e usuário não é admin)
            $proposta = $this->propostaModel->getPropostaById($idFinal);
            if ($proposta && $this->isPropostaLocked($proposta)) {
                $this->setFlashMessage('error', 'Proposta aprovada não pode ser excluída. Apenas administradores podem gerenciá-la.');
                header('Location: ' . BASE_URL . '/orcamento/index');
                exit();
            }

            if ($this->propostaModel->excluirProposta($idFinal)) {
                $this->setFlashMessage('success', 'Proposta excluída com sucesso.');
            } else {
                $errorMessage = $this->propostaModel->getLastError() ?? 'Erro ao excluir a proposta.';
                $this->setFlashMessage('error', $errorMessage);
            }

            header('Location: ' . BASE_URL . '/orcamento/index');
            exit();
        }

        /** Salva uma proposta (criação/atualização) */
        public function salvar()
        {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                header('Location: ' . BASE_URL . '/orcamento/index');
                exit();
            }

            // Validação centralizada e automática
            if (!isset($_POST['csrf_token']) || !$this->validateCsrfToken($_POST['csrf_token'])) {
                $this->setFlashMessage('error', 'Erro de validação de segurança (CSRF).');
                header('Location: ' . BASE_URL . '/orcamento/index');
                exit();
            }
            
            // Unifica os itens da tabela dinâmica para o formato do banco (servicos_json)
            $itens_processados = [];
            if (isset($_POST['item_descricao'])) {
                foreach ($_POST['item_descricao'] as $i => $desc) {
                    if (empty($desc)) continue;
                    
                    $cat = $_POST['item_categoria'][$i] ?? 'Outros';
                    $isLegend = ($cat === 'Legenda');
                    
                    $qtd = $isLegend ? 0 : (float)($_POST['item_quantidade'][$i] ?? 1);
                    $vlr = $isLegend ? 0 : $this->parseDecimal($_POST['item_valor'][$i] ?? '0');
                    $desc_item = $isLegend ? 0 : (float)($_POST['item_desconto'][$i] ?? 0);
                    
                    $itens_processados[] = [
                        'nome' => $desc,
                        'categoria' => $cat,
                        'descricao' => $_POST['item_detalhes'][$i] ?? '',
                        'unidade' => $isLegend ? '—' : ($_POST['item_unidade'][$i] ?? 'un'),
                        'quantidade' => $qtd,
                        'valor_unitario' => $vlr,
                        'desconto' => $desc_item,
                        'subtotal' => ($qtd * $vlr) * (1 - ($desc_item / 100))
                    ];
                }
            }

            $id = isset($_POST['id']) ? (int)$_POST['id'] : null;

            // Sanitiza e coleta todos os dados do formulário
            $dados = [
                'id' => $id,
                'creation_type' => trim($_POST['creation_type'] ?? 'from_scratch'),
                'projeto_id' => filter_input(INPUT_POST, 'projeto_id', FILTER_VALIDATE_INT) ?: null,
                'contrato_id' => filter_input(INPUT_POST, 'contrato_id', FILTER_VALIDATE_INT) ?: null,
                'cliente_id' => filter_input(INPUT_POST, 'cliente_id', FILTER_VALIDATE_INT) ?: null,
                'cliente_id_scratch' => filter_input(INPUT_POST, 'cliente_id_scratch', FILTER_VALIDATE_INT) ?: null,
                'nome_projeto_scratch' => trim($_POST['nome_projeto_scratch'] ?? ''),
                'titulo' => trim($_POST['titulo'] ?? $_POST['titulo_proposta'] ?? ''),
                'descricao_geral' => trim($_POST['descricao_geral'] ?? ''),
                'objetivo' => trim($_POST['objetivo'] ?? $_POST['observacoes'] ?? ''),
                'status' => trim($_POST['status'] ?? 'Rascunho'),
                'validade_proposta' => filter_input(INPUT_POST, 'validade_proposta', FILTER_VALIDATE_INT) ?: filter_input(INPUT_POST, 'validade_dias', FILTER_VALIDATE_INT) ?: 30,
                'data_proposta' => !empty($_POST['data_proposta']) ? $_POST['data_proposta'] : date('Y-m-d'),
                'responsavel_interno_id' => !empty($_POST['responsavel_interno_id']) ? (int)$_POST['responsavel_interno_id'] : null,
                'numero_proposta' => trim($_POST['codigo'] ?? $_POST['numero_proposta'] ?? ''),
                'cliente_telefone' => trim($_POST['cliente_telefone'] ?? ''),
                'cliente_sigla' => trim($_POST['cliente_sigla'] ?? ''),
                'cliente_documento' => trim($_POST['cliente_documento'] ?? ''),
                'representante' => trim($_POST['representante'] ?? ''),
                'email_cliente' => trim($_POST['email_cliente'] ?? ''),
                'municipio' => trim($_POST['municipio'] ?? ''),
                'area' => trim($_POST['area'] ?? ''),
                'cliente_logradouro' => trim($_POST['cliente_logradouro'] ?? ''),
                'cliente_numero' => trim($_POST['cliente_numero'] ?? ''),
                'cliente_complemento' => trim($_POST['cliente_complemento'] ?? ''),
                'cliente_bairro' => trim($_POST['cliente_bairro'] ?? ''),
                'cliente_municipio' => trim($_POST['cliente_municipio'] ?? ''),
                'cliente_uf' => trim($_POST['cliente_uf'] ?? ''),
                'cliente_endereco' => trim((string)trim($_POST['cliente_logradouro'] ?? '')
                    . ' ' . trim($_POST['cliente_numero'] ?? '')
                    . (!empty($_POST['cliente_complemento']) ? ' ' . trim($_POST['cliente_complemento']) : '')
                    . (!empty($_POST['cliente_bairro']) ? ', ' . trim($_POST['cliente_bairro']) : '')
                    . (!empty($_POST['cliente_municipio']) ? ', ' . trim($_POST['cliente_municipio']) : '')
                    . (!empty($_POST['cliente_uf']) ? ' - ' . trim($_POST['cliente_uf']) : '')),
                'condicao_pagamento' => trim($_POST['condicao_pagamento'] ?? ''),
                'forma_pagamento' => trim($_POST['forma_pagamento'] ?? $_POST['condicao_pagamento'] ?? ''),
                'pix_tipo_chave' => trim($_POST['pix_tipo_chave'] ?? ''),
                'pix_chave' => trim($_POST['pix_chave'] ?? ''),
                'dados_bancarios' => trim($_POST['dados_bancarios'] ?? ''),
                'prazo_execucao' => trim($_POST['prazo_execucao'] ?? $_POST['prazo_entrega'] ?? ''),
                'garantias' => trim($_POST['garantias'] ?? ''),
                'versao_documento' => trim($_POST['versao_documento'] ?? ''),
                'latitude' => trim($_POST['latitude'] ?? ''),
                'longitude' => trim($_POST['longitude'] ?? ''),
                'cronograma_data' => !empty($_POST['cronograma_data']) ? $_POST['cronograma_data'] : null,
                'contextualizacao_json' => $_POST['contextualizacao_json'] ?? null,
                'equipe_json' => $_POST['equipe_json'] ?? null,
                'motivo_alteracao' => trim($_POST['motivo_alteracao'] ?? '') ?: 'Alteração via formulário',

                // Campos de cálculo (já tratados como string no POST, precisam de conversão)
                'total_servicos' => $this->parseDecimal($_POST['subtotal'] ?? $_POST['total_servicos'] ?? '0'),
                'total_materiais' => $this->parseDecimal($_POST['total_materiais'] ?? '0'),
                'impostos_valor' => $this->parseDecimal($_POST['impostos_valor'] ?? '0'),
                'descontos_valor' => $this->parseDecimal($_POST['descontos_valor'] ?? '0'),
                'desconto_tipo' => trim($_POST['desconto_tipo'] ?? 'percentual'),
                'valor_total' => $this->parseDecimal($_POST['valor_total'] ?? '0'),

                // Itens dinâmicos (JSON)
                'servicos' => $itens_processados,
                'materiais' => [], // Unificado em serviços para este formulário
                'custos_extras' => [],

                // Assinatura (Contratada)
                'assinatura_tipo' => trim($_POST['assinatura_tipo'] ?? 'imagem'),
                'assinatura_elaborador_responsavel' => !empty($_POST['assinatura_elaborador_responsavel']) ? 1 : 0,
                'assinatura_imagem' => !empty($_POST['assinatura_imagem_remover']) ? null : ($_POST['assinatura_imagem'] ?? null),
                'assinatura_certificado_nome' => trim($_POST['assinatura_certificado_nome'] ?? ''),
                'assinatura_certificado_cpf' => trim($_POST['assinatura_certificado_cpf'] ?? ''),
                'assinatura_certificado_path' => $_POST['assinatura_certificado_path'] ?? null,
                'assinatura_certificado_senha' => !empty($_POST['assinatura_certificado_senha']) ? self::encrypt($_POST['assinatura_certificado_senha']) : null,
                'assinatura_certificado_validade' => $_POST['assinatura_certificado_validade'] ?? null,

                // Assinatura do Elaborador (Responsável Técnico)
                'assinatura_elaborador_tipo' => trim($_POST['assinatura_elaborador_tipo'] ?? 'imagem'),
                'assinatura_elaborador_imagem' => !empty($_POST['assinatura_elaborador_imagem_remover']) ? null : ($_POST['assinatura_elaborador_imagem'] ?? null),
                'assinatura_elaborador_certificado_nome' => trim($_POST['assinatura_elaborador_certificado_nome'] ?? ''),
                'assinatura_elaborador_certificado_cpf' => trim($_POST['assinatura_elaborador_certificado_cpf'] ?? ''),
                'assinatura_elaborador_certificado_path' => $_POST['assinatura_elaborador_certificado_path'] ?? null,
                'assinatura_elaborador_certificado_senha' => !empty($_POST['assinatura_elaborador_certificado_senha']) ? self::encrypt($_POST['assinatura_elaborador_certificado_senha']) : null,
                'assinatura_elaborador_certificado_validade' => $_POST['assinatura_elaborador_certificado_validade'] ?? null,
            ];

            // Se for uma nova proposta, verifica duplicidade recente usando os dados já processados
            if (empty($id)) {
                // Garante que o cliente_id esteja preenchido se vier de um projeto para a verificação ser precisa
                if ($dados['creation_type'] === 'from_project' && !empty($dados['projeto_id'])) {
                    $projeto = $this->projetosModel->getProjetoById((int)$dados['projeto_id']);
                    $dados['cliente_id'] = $projeto['cliente_id'] ?? $dados['cliente_id'];
                }

                if ($this->propostaModel->verificarDuplicidadeRecente($dados)) {
                    $this->setFlashMessage('error', 'Parece que esta proposta já foi salva recentemente. Evitando duplicação.');
                    header('Location: ' . BASE_URL . '/orcamento/index');
                    exit();
                }
            }

            // Validação básica
            if (empty($dados['titulo']) || empty($dados['data_proposta'])) {
                $this->setFlashMessage('error', 'Título e Data da Proposta são obrigatórios.');
                $redirectUrl = $id ? '/orcamento/editar/' . $id : '/orcamento/novo';
                header('Location: ' . BASE_URL . $redirectUrl);
                exit();
            }

            $propostaAtual = null;
            if ($id) {
                $propostaAtual = $this->propostaModel->getPropostaById($id);
                if (!$propostaAtual) {
                    $this->setFlashMessage('error', 'Proposta para edição não encontrada.');
                    header('Location: ' . BASE_URL . '/orcamento/index');
                    exit();
                }

                if ($this->isPropostaLocked($propostaAtual)) {
                    $this->setFlashMessage('error', 'Proposta aprovada não pode ser alterada. Apenas administradores podem editá-la.');
                    header('Location: ' . BASE_URL . '/orcamento/index');
                    exit();
                }
            }

            // Se a proposta for marcada como 'Enviada', gera/renova o token de aprovação
            $tokenAusente = empty($propostaAtual) || empty($propostaAtual['token_aprovacao']);
            $tokenExpirado = !$tokenAusente && !empty($propostaAtual['token_validade']) && strtotime($propostaAtual['token_validade']) < time();
            if (($dados['status'] === 'Enviada' || $dados['status'] === 'Rascunho') && ($tokenAusente || $tokenExpirado)) {
                $dados['token_aprovacao'] = $this->propostaModel->generateApprovalToken();
                $diasValidade = defined('PROPOSTA_VALIDADE_LINK') ? PROPOSTA_VALIDADE_LINK : 7;
                $dados['token_validade'] = date('Y-m-d H:i:s', strtotime("+{$diasValidade} days"));
            }

            if ($this->propostaModel->salvarProposta($dados)) {
                $message = $id ? 'Proposta atualizada com sucesso.' : 'Proposta criada com sucesso.';
                $this->setFlashMessage('success', $message);
            } else {
                $this->setFlashMessage('error', 'Erro ao salvar proposta.');
            }

            header('Location: ' . BASE_URL . '/orcamento/index');
            exit();
        }

        /** Visualiza uma proposta */
        public function ver($id)
        {
            $id = (int)$id;
            $proposta = $this->propostaModel->getPropostaById($id);
            if (!$proposta) {
                $this->setFlashMessage('error', 'Proposta não encontrada.');
                header('Location: ' . BASE_URL . '/orcamento/index');
                exit();
            }

            $proposta = $this->prepareOrcamentoData($proposta);
            $historico = $this->propostaModel->getHistoricoByPropostaId($id);
            
            $proposta['razao_social'] = $proposta['cliente_nome'];
            $proposta['nome_fantasia'] = $proposta['cliente_nome_fantasia'];

            // Formatação do CNPJ/CPF do cliente
            $doc = preg_replace('/\D/', '', $proposta['cliente_documento'] ?? '');
            if (strlen($doc) === 11) {
                $proposta['cnpj_cpf'] = substr($doc, 0, 3) . '.' . substr($doc, 3, 3) . '.' . substr($doc, 6, 3) . '-' . substr($doc, 9);
            } elseif (strlen($doc) === 14) {
                $proposta['cnpj_cpf'] = substr($doc, 0, 2) . '.' . substr($doc, 2, 3) . '.' . substr($doc, 5, 3) . '/' . substr($doc, 8, 4) . '-' . substr($doc, 12);
            } else {
                $proposta['cnpj_cpf'] = $proposta['cliente_documento'] ?: '—';
            }

            // Formatação do telefone para o padrão (XX) XXXXX-XXXX ou (XX) XXXX-XXXX
            $tel = preg_replace('/\D/', '', $proposta['cliente_telefone'] ?? '');
            if (strlen($tel) === 11) {
                $proposta['telefone'] = '(' . substr($tel, 0, 2) . ') ' . substr($tel, 2, 5) . '-' . substr($tel, 7);
            } elseif (strlen($tel) === 10) {
                $proposta['telefone'] = '(' . substr($tel, 0, 2) . ') ' . substr($tel, 2, 4) . '-' . substr($tel, 6);
            } else {
                $proposta['telefone'] = $proposta['cliente_telefone'] ?: '—';
            }

            $proposta['endereco'] = $proposta['cliente_endereco'];
            $proposta['total'] = $proposta['total_final'] ?? 0;
            $proposta['subtotal'] = (float)$proposta['total_servicos'] + (float)$proposta['total_materiais'];
            $proposta['criado_em'] = $proposta['created_at'] ?? ($proposta['data_proposta'] ?? date('Y-m-d H:i:s'));
            $proposta['validade_dias'] = $proposta['validade'];
            $proposta['prazo_entrega'] = $proposta['prazo_execucao'];
            $proposta['condicao_pagamento'] = $proposta['forma_pagamento'];
            $proposta['impostos_perc'] = ($proposta['subtotal'] > 0) ? ($proposta['impostos_valor'] / ($proposta['subtotal'] - $proposta['descontos_valor'])) * 100 : 0;

            if (!empty($proposta['data_proposta']) && !empty($proposta['validade'])) {
                $date = new \DateTime($proposta['data_proposta']);
                $date->modify("+{$proposta['validade']} days");
                $proposta['data_validade'] = $date->format('Y-m-d');
            }

            $statusLabels = [
                'Rascunho' => ['label' => 'Rascunho', 'cor' => 'gray'],
                'Enviada'  => ['label' => 'Enviada', 'cor' => 'sky'],
                'Aprovada' => ['label' => 'Aprovada', 'cor' => 'emerald'],
                'Rejeitada'=> ['label' => 'Rejeitada', 'cor' => 'red'],
            ];

            $empresa = $this->empresaModel->getDadosEmpresa();
            $userEmail = $this->session->get('user_email', '');
            $data = [
                'pageTitle' => 'Visualizar Proposta',
                'orc' => $proposta,
                'historico' => $historico,
                'statusLabels' => $statusLabels,
                'isAdmin' => $this->session->isAdmin(),
                'empresa' => $empresa,
                'userEmail' => $userEmail,
            ];
            $this->renderView('orcamento/ver', $data);
        }

        /** Gera PDF de uma proposta usando DOMPDF com cronograma em paisagem */
        public function pdf($id)
        {
            $id = (int)$id;
            $proposta = $this->propostaModel->getPropostaById($id);
            if (!$proposta) {
                $this->setFlashMessage('error', 'Proposta não encontrada.');
                header('Location: ' . BASE_URL . '/orcamento/index');
                exit();
            }

            ini_set('memory_limit', '512M');
            set_time_limit(300);

            $proposta = $this->prepareOrcamentoData($proposta);
            if (!empty($proposta['token_aprovacao'])) {
                $proposta['qr_code_base64'] = $this->generateQrCodeBase64($proposta['token_aprovacao']);
            }
            $empresa = $this->empresaModel->getDadosEmpresa();

            // Gera o HTML completo a partir da view de PDF
            ob_start();
            $data = ['proposta_pdf' => $proposta, 'empresa' => $empresa];
            $data['qr_code_base64'] = $proposta['qr_code_base64'] ?? null;
            $this->renderPartial('orcamento/proposta_pdf', $data);
            $html = ob_get_clean();

            // Divide o HTML nos marcadores do cronograma
            $markerStart = '<!--CRONOGRAMA_START-->';
            $markerEnd   = '<!--CRONOGRAMA_END-->';
            $posStart = strpos($html, $markerStart);
            $posEnd   = strpos($html, $markerEnd);

            if ($posStart !== false && $posEnd !== false) {
                // Parte A: tudo antes do cronograma (capa, instruções, escopo, equipe)
                $partA = substr($html, 0, $posStart);
                // Conteúdo do cronograma (entre os marcadores)
                $cronogramaHtml = substr($html, $posStart + strlen($markerStart), $posEnd - $posStart - strlen($markerStart));
                // Parte B: tudo após o cronograma (itens, condições, assinatura)
                $partB = substr($html, $posEnd + strlen($markerEnd));

                // Extrai o <style> da parte A para usar no documento do cronograma
                preg_match('/<style>(.*?)<\/style>/s', $partA, $styleMatch);
                $styleContent = $styleMatch[1] ?? '';
                // Remove @page :first do CSS extraído (margem zero não deve ser aplicada ao cronograma)
                $styleContent = preg_replace('/@page\s+:first\s*\{[^}]*\}/s', '', $styleContent);

                // Monta documento HTML completo apenas com o cronograma (landscape)
                // O cabeçalho e rodapé serão desenhados via FPDF durante o merge
                // para evitar problemas de position:fixed do DOMPDF em paisagem.
                $cronogramaDoc = '<!doctype html><html><head><meta charset="utf-8"><style>'
                    . $styleContent
                    . "\n@page { size: A4 landscape; margin: 40mm 2cm 30mm 2cm; }"
                    . '</style></head><body>'
                    . $cronogramaHtml
                    . '</body></html>';

                // Extrai cabeçalho e rodapé do HTML original para usar na Parte B
                $dom = new \DOMDocument();
                @$dom->loadHTML('<?xml encoding="utf-8" ?>' . $html, LIBXML_NOWARNING | LIBXML_NOERROR);
                $xpath = new \DOMXPath($dom);
                $headerNode = $xpath->query("//*[contains(@class, 'pdf-header')]")->item(0);
                $footerNode = $xpath->query("//*[contains(@class, 'pdf-footer')]")->item(0);
                $partBHeaderHtml = $headerNode ? $dom->saveHTML($headerNode) : '';
                $partBFooterHtml = $footerNode ? $dom->saveHTML($footerNode) : '';

                // Constrói documento completo da Parte B com cabeçalho, rodapé e conteúdo
                $partBDoc = '<!doctype html><html><head><meta charset="utf-8"><style>'
                    . $styleContent
                    . "\n@page { margin: 3cm 2cm 2cm 3cm; size: A4 portrait; }"
                    . '</style></head><body>'
                    . $partBHeaderHtml . "\n"
                    . $partBFooterHtml . "\n"
                    . $partB
                    . '</body></html>';

                // Opções do DOMPDF
                $options = new Options();
                $options->setIsRemoteEnabled(false);
                $options->setIsPhpEnabled(true);
                $options->setIsHtml5ParserEnabled(true);
                $options->setDefaultFont('Helvetica');

                // --- GERA PDF A (retrato, sem cronograma) ---
                $pdfAPath = $this->renderPdfToFile($partA, 'A4', 'portrait', $options, null);

                // --- GERA PDF CRONOGRAMA (paisagem) ---
                $pdfCPath = $this->renderPdfToFile($cronogramaDoc, 'A4', 'landscape', $options, null);

                // --- GERA PDF B (retrato, pós-cronograma) ---
                $pdfBPath = $this->renderPdfToFile($partBDoc, 'A4', 'portrait', $options, null);

                // Carrega logo da empresa
                $logoPath = defined('ROOT_PATH') ? ROOT_PATH . '/public/assets/images/logo.png' : null;
                if ($logoPath && !file_exists($logoPath)) $logoPath = null;

                // --- MERGE usando FPDI ---
                $mergedPdf = $this->mergePdfs([$pdfAPath, $pdfCPath, $pdfBPath], $proposta, $empresa, $logoPath);

                // Limpa arquivos temporários
                foreach ([$pdfAPath, $pdfCPath, $pdfBPath] as $p) {
                    if (file_exists($p)) @unlink($p);
                }

                // Assina o PDF com certificado digital, se configurado
                $mergedPdf = $this->signPdfIfConfigured($mergedPdf, $proposta);

                // Envia o PDF mesclado
                header('Content-Type: application/pdf');
                header('Content-Disposition: inline; filename="proposta_' . $id . '.pdf"');
                echo $mergedPdf;
            } else {
                // Fallback: sem cronograma, gera PDF normal (retrato)
                $options = new Options();
                $options->setIsRemoteEnabled(false);
                $options->setIsPhpEnabled(true);
                $options->setIsHtml5ParserEnabled(true);
                $options->setDefaultFont('Helvetica');

                $dompdf = new Dompdf($options);
                $dompdf->loadHtml($html);
                $dompdf->setPaper('A4', 'portrait');
                $dompdf->setCallbacks($this->buildPdfPageCallbacks(
                    $proposta['qr_code_base64'] ?? null
                ));
                $dompdf->render();
                $pdfContent = $dompdf->output();
                $pdfContent = $this->signPdfIfConfigured($pdfContent, $proposta);
                header('Content-Type: application/pdf');
                header('Content-Disposition: inline; filename="proposta_' . $id . '.pdf"');
                echo $pdfContent;
            }
        }

        /**
         * Renderiza HTML em PDF, salva em arquivo temporário e retorna o caminho.
         */
        private function renderPdfToFile(string $html, string $paper, string $orientation, Options $options, ?array $callbacks): string
        {
            $dompdf = new Dompdf($options);
            $dompdf->loadHtml($html);
            $dompdf->setPaper($paper, $orientation);
            if ($callbacks) {
                $dompdf->setCallbacks($callbacks);
            }
            $dompdf->render();

            $tmpPath = tempnam(sys_get_temp_dir(), 'pdf_');
            file_put_contents($tmpPath, $dompdf->output());
            return $tmpPath;
        }

        /**
         * Mescla múltiplos PDFs em um único documento usando FPDI.
         * @param string[] $pdfPaths Array de caminhos para arquivos PDF.
         * @return string Conteúdo do PDF mesclado.
         */
        private function mergePdfs(array $pdfPaths, array $proposta = [], array $empresa = [], ?string $logoPath = null): string
        {
            $fpdi = new \setasign\Fpdi\Fpdi();
            $totalDisplayPages = 0;
            $mergedPageNumber = 1;

            // Prepara dados para cabeçalho/rodapé do cronograma
            $dataEmissao = $this->fmtDate($proposta['data_proposta'] ?? '');
            $validadeDias = $proposta['validade'] ?? $proposta['validade_proposta'] ?? $proposta['validade_dias'] ?? 0;
            $dataValidade = $proposta['data_validade'] ?? '';
            if (!$dataValidade && $validadeDias > 0 && !empty($proposta['data_proposta'])) {
                $d = new \DateTime($proposta['data_proposta']);
                $d->modify("+{$validadeDias} days");
                $dataValidade = $d->format('d/m/Y');
            } elseif ($dataValidade && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dataValidade)) {
                $dataValidade = $this->fmtDate($dataValidade);
            }
            $responsavel = $proposta['responsavel_nome'] ?? '';
            $codigo = $proposta['codigo'] ?? $proposta['numero_proposta'] ?? '';
            $titulo = $proposta['titulo'] ?? $proposta['nome_proposta'] ?? '';
            $versao = $proposta['versao_documento'] ?? '';
            $contratoNum = $proposta['contrato_numero'] ?? $proposta['contrato_id'] ?? '';
            $empresaNome = $empresa['razao_social'] ?? 'ENVICORP ENGENHARIA E NEGOCIOS LTDA';
            $empresaCnpj = $empresa['cnpj'] ?? '49.787.357/0001-50';
            $empresaEnd = $empresa['endereco'] ?? 'Avenida dos Oitis, 5941';
            $empresaEmail = $empresa['email'] ?? 'contato@envicorp.com.br';
            $headerBrand = [0, 0x8A, 0xF2]; // #008AF2
            $headerText = [0x11, 0x18, 0x27]; // #111827
            $mutedText = [0x6B, 0x72, 0x80]; // #6B7280

            // Pré‑calcula o total de páginas para o formato "X de Y" (instância separada)
            $totalPages = 0;
            $counter = new \setasign\Fpdi\Fpdi();
            foreach ($pdfPaths as $pdfPath) {
                if (file_exists($pdfPath)) {
                    $totalPages += $counter->setSourceFile($pdfPath);
                }
            }
            unset($counter);

            $lastPdfPath = end($pdfPaths);

            foreach ($pdfPaths as $pdfPath) {
                if (!file_exists($pdfPath)) continue;

                $pageCount = $fpdi->setSourceFile($pdfPath);
                for ($i = 1; $i <= $pageCount; $i++) {
                    $templateId = $fpdi->importPage($i);
                    $size = $fpdi->getTemplateSize($templateId);

                    $fpdi->addPage($size['orientation'], [$size['width'], $size['height']]);
                    $fpdi->useTemplate($templateId);

                    $isLandscape = $size['width'] > $size['height'];

                    // Desenha cabeçalho e rodapé nas páginas do cronograma (landscape)
                    if ($isLandscape) {
                        $w = $size['width'];
                        $h = $size['height'];
                        $ml = 20;
                        $mr = $w - 20;

                        // ── Cabeçalho (mesmo layout do PDF em retrato) ──
                        $hY1 = 6;  // y da 1ª linha (mais ao topo, igual retrato)
                        $hY2 = 10; // y da 2ª linha (4mm de espaçamento)
                        $hY3 = 14; // y da 3ª linha
                        $lineY = 28;
                        $fpdi->SetDrawColor(...$headerBrand);
                        $fpdi->SetLineWidth(0.6);
                        $fpdi->Line($ml, $lineY, $mr, $lineY);

                        // Logo (opcional)
                        $textX = $ml;
                        if ($logoPath && file_exists($logoPath)) {
                            $fpdi->Image($logoPath, $ml, $hY1, 14);
                            $textX = $ml + 19;
                        }

                        // ── Linha 1: label + código na mesma linha ──
                        $label = 'PROPOSTA TÉCNICA ORÇAMENTÁRIA';
                        $fpdi->SetFont('Helvetica', 'B', 7);
                        $labelW = $fpdi->GetStringWidth($label);
                        $fpdi->SetTextColor(...$headerBrand);
                        $fpdi->SetXY($textX, $hY1);
                        $fpdi->Cell($labelW, 5, mb_convert_encoding($label, 'ISO-8859-1', 'UTF-8'), 0, 0);
                        $fpdi->SetTextColor(0x0C, 0x44, 0x7C); // brand_dark
                        $fpdi->Cell(0, 5, mb_convert_encoding($codigo, 'ISO-8859-1', 'UTF-8'), 0, 1);

                        // ── Linha 2: título ──
                        $fpdi->SetFont('Helvetica', 'B', 9);
                        $fpdi->SetTextColor(...$headerText);
                        $fpdi->SetX($textX);
                        $fpdi->Cell(0, 6, mb_convert_encoding($titulo, 'ISO-8859-1', 'UTF-8'), 0, 1);

                        // ── Badge de contrato (padronizado com .header-badge do retrato) ──
                        // Conversão fiel ao CSS (.header-badge / .header-badge-green):
                        //   px → pt  : Dompdf renderiza 1px CSS = 0.75pt
                        //   pt → mm  : 1pt = 0.352778mm
                        // font-size: 8px  → 6pt   | padding: 1px 5px | border-radius: 10px
                        if ($contratoNum) {
                            $badgeText = mb_convert_encoding('CONTRATO ' . $contratoNum, 'ISO-8859-1', 'UTF-8');
                            $pxToMm = 0.75 * 0.352778; // 1px CSS → mm
                            $fontSizePt = 8 * 0.75;    // 8px → 6pt (mesmo tamanho do retrato)
                            $fpdi->SetFont('Helvetica', 'B', $fontSizePt);
                            $tw = $fpdi->GetStringWidth($badgeText);
                            $padX = 8 * $pxToMm;                    // ≈1.32mm — padding horizontal (5px)
                            $padY = 2 * $pxToMm;                    // ≈0.26mm — padding vertical (1px)
                            $lineH = $fontSizePt * 0.352778 * 1.15; // altura da linha de texto a 6pt
                            $bw = $tw + $padX * 2;                  // largura sempre acompanha o texto:
                                                                     // comporta qualquer tamanho de contrato
                                                                     // (ex.: CTR-2026-001) sem cortar
                            $bh = $lineH + $padY * 2;                // altura = texto + padding (igual retrato)
                            $badgeY = $hY2 + 7;
                            $r = min(10 * $pxToMm, $bw / 2, $bh / 2); // border-radius 10px
                            // ── Fill com cantos arredondados ──
                            $fpdi->SetFillColor(0xEA, 0xF3, 0xDE);
                            $fpdi->Rect($textX, $badgeY + $r, $bw, $bh - 2*$r, 'F');
                            $fpdi->Rect($textX + $r, $badgeY, $bw - 2*$r, $bh, 'F');
                            $step = 0.12;
                            for ($d = 0; $d < $r; $d += $step) {
                                $cd = sqrt(max(0, $r*$r - ($r - $d)*($r - $d)));
                                // top-left / top-right
                                $fpdi->Rect($textX + $r - $cd, $badgeY + $d, $cd, $step, 'F');
                                $fpdi->Rect($textX + $bw - $r, $badgeY + $d, $cd, $step, 'F');
                                // bottom-left / bottom-right
                                $fpdi->Rect($textX + $r - $cd, $badgeY + $bh - $d - $step, $cd, $step, 'F');
                                $fpdi->Rect($textX + $bw - $r, $badgeY + $bh - $d - $step, $cd, $step, 'F');
                            }
                            // ── Borda com cantos arredondados ──
                            $fpdi->SetDrawColor(0xC0, 0xDD, 0x97);
                            $fpdi->SetLineWidth(0.3);
                            $fpdi->Line($textX + $r, $badgeY, $textX + $bw - $r, $badgeY);
                            $fpdi->Line($textX + $r, $badgeY + $bh, $textX + $bw - $r, $badgeY + $bh);
                            $fpdi->Line($textX, $badgeY + $r, $textX, $badgeY + $bh - $r);
                            $fpdi->Line($textX + $bw, $badgeY + $r, $textX + $bw, $badgeY + $bh - $r);
                            $seg = 6;
                            foreach ([[$textX+$r,$badgeY+$r,180,270],[$textX+$bw-$r,$badgeY+$r,270,360],[$textX+$bw-$r,$badgeY+$bh-$r,0,90],[$textX+$r,$badgeY+$bh-$r,90,180]] as $c) {
                                for ($i = 0; $i < $seg; $i++) {
                                    $a1 = deg2rad($c[2] + ($c[3]-$c[2])*$i/$seg);
                                    $a2 = deg2rad($c[2] + ($c[3]-$c[2])*($i+1)/$seg);
                                    $fpdi->Line($c[0]+$r*cos($a1), $c[1]+$r*sin($a1), $c[0]+$r*cos($a2), $c[1]+$r*sin($a2));
                                }
                            }
                            // ── Texto (centralizado verticalmente na badge) ──
                            $fpdi->SetTextColor(0x3B, 0x6D, 0x11);
                            $fpdi->SetFont('Helvetica', 'B', $fontSizePt);
                            $fpdi->SetXY($textX + $padX, $badgeY + ($bh - $lineH) / 2);
                            $fpdi->Cell($tw, $lineH, $badgeText, 0, 0);
                        }

                        // ── Metadados à direita (label regular, valor em negrito) ──
                        // Padronizado com o cabeçalho em retrato: Helvetica 8pt (9px no CSS)
                        $metaLeft = $mr - 72;
                        $rowH = 4;
                        $lblEmitida = mb_convert_encoding('Emitida em ', 'ISO-8859-1', 'UTF-8');
                        $lblValida  = mb_convert_encoding('Válida até ', 'ISO-8859-1', 'UTF-8');
                        $lblElab    = mb_convert_encoding('Elaborado por ', 'ISO-8859-1', 'UTF-8');
                        if ($dataEmissao) {
                            $fpdi->SetFont('Helvetica', '', 7);
                            $fpdi->SetTextColor(...$mutedText);
                            $w1 = $fpdi->GetStringWidth($lblEmitida);
                            $fpdi->SetFont('Helvetica', 'B', 7);
                            $fpdi->SetTextColor(...$headerText);
                            $w2 = $fpdi->GetStringWidth($dataEmissao);
                            $startX = $metaLeft + (72 - $w1 - $w2);
                            $fpdi->SetFont('Helvetica', '', 7);
                            $fpdi->SetTextColor(...$mutedText);
                            $fpdi->SetXY($startX, $hY1);
                            $fpdi->Cell($w1, $rowH, $lblEmitida, 0, 0);
                            $fpdi->SetFont('Helvetica', 'B', 7);
                            $fpdi->SetTextColor(...$headerText);
                            $fpdi->Cell($w2, $rowH, $dataEmissao, 0, 1);
                        }
                        if ($dataValidade) {
                            $fpdi->SetFont('Helvetica', '', 7);
                            $fpdi->SetTextColor(...$mutedText);
                            $w1 = $fpdi->GetStringWidth($lblValida);
                            $fpdi->SetFont('Helvetica', 'B', 7);
                            $fpdi->SetTextColor(...$headerBrand);
                            $w2 = $fpdi->GetStringWidth($dataValidade);
                            $startX = $metaLeft + (72 - $w1 - $w2);
                            $fpdi->SetFont('Helvetica', '', 7);
                            $fpdi->SetTextColor(...$mutedText);
                            $fpdi->SetXY($startX, $hY2);
                            $fpdi->Cell($w1, $rowH, $lblValida, 0, 0);
                            $fpdi->SetFont('Helvetica', 'B', 7);
                            $fpdi->SetTextColor(...$headerBrand);
                            $fpdi->Cell($w2, $rowH, $dataValidade, 0, 1);
                            $fpdi->SetTextColor(...$mutedText);
                        }
                        if ($responsavel) {
                            $fpdi->SetFont('Helvetica', '', 7);
                            $fpdi->SetTextColor(...$mutedText);
                            $w1 = $fpdi->GetStringWidth($lblElab);
                            $respEnc = mb_convert_encoding($responsavel, 'ISO-8859-1', 'UTF-8');
                            $fpdi->SetFont('Helvetica', 'B', 7);
                            $fpdi->SetTextColor(...$headerText);
                            $w2 = $fpdi->GetStringWidth($respEnc);
                            $startX = $metaLeft + (72 - $w1 - $w2);
                            $fpdi->SetFont('Helvetica', '', 7);
                            $fpdi->SetTextColor(...$mutedText);
                            $fpdi->SetXY($startX, $hY3);
                            $fpdi->Cell($w1, $rowH, $lblElab, 0, 0);
                            $fpdi->SetFont('Helvetica', 'B', 7);
                            $fpdi->SetTextColor(...$headerText);
                            $fpdi->Cell($w2, $rowH, $respEnc, 0, 1);
                        }

                        // ── Rodapé ao pé da página (igual retrato) ──
                        $fpdi->SetAutoPageBreak(false);
                        $footerH = 24;
                        $footerY = $h - $footerH;
                        // Fundo suave
                        $fpdi->SetFillColor(0xF9, 0xFA, 0xFB);
                        $fpdi->Rect(0, $footerY, $w, $footerH + 8, 'F');

                        // Linha superior
                        $fpdi->SetDrawColor(0xE5, 0xE7, 0xEB);
                        $fpdi->SetLineWidth(0.3);
                        $fpdi->Line($ml, $footerY, $mr, $footerY);

                        // Coluna esquerda: empresa / CNPJ / endereço
                        $fpdi->SetTextColor(...$headerText);
                        $fpdi->SetFont('Helvetica', '', 7);
                        $fpdi->SetXY($ml, $footerY + 3);
                        $fpdi->Cell(0, 5, mb_convert_encoding($empresaNome, 'ISO-8859-1', 'UTF-8'), 0, 1);
                        $fpdi->SetX($ml);
                        $fpdi->Cell(0, 5, mb_convert_encoding('CNPJ ' . $empresaCnpj, 'ISO-8859-1', 'UTF-8'), 0, 1);
                        $fpdi->SetX($ml);
                        $fpdi->SetTextColor(...$mutedText);
                        $fpdi->Cell(0, 5, mb_convert_encoding($empresaEnd . ' | ' . $empresaEmail, 'ISO-8859-1', 'UTF-8'), 0, 1);

                        // Versão à direita
                        if ($versao) {
                            $fpdi->SetFont('Helvetica', 'B', 7);
                            $fpdi->SetTextColor(...$mutedText);
                            $fpdi->SetXY($mr - 60, $footerY + 3);
                            $fpdi->MultiCell(60, 5, mb_convert_encoding($versao, 'ISO-8859-1', 'UTF-8'), 0, 'R');
                        }
                        $fpdi->SetAutoPageBreak(true, 20);
                    }

                    // Numeração de página "X de Y" (exceto capa = página 1 e instruções = última página)
                    $isLastPage = ($pdfPath === $lastPdfPath && $i === $pageCount);
                    if ($mergedPageNumber >= 2 && !$isLastPage) {
                        $displayNum = $mergedPageNumber - 1;
                        $label = $displayNum . ' de ' . ($totalPages - 2);
                        $fpdi->SetFont('Helvetica', '', 8);
                        $fpdi->SetTextColor(0x6B, 0x72, 0x80);
                        $textWidth = $fpdi->GetStringWidth($label);
                        $marginRight = $isLandscape ? 20 : 20;
                        $marginTop = $isLandscape ? 40 : 25;
                        $x = $size['width'] - $textWidth - $marginRight;
                        $y = $marginTop;
                        $fpdi->Text($x, $y, $label);
                    }
                    $mergedPageNumber++;
                }
            }

            return $fpdi->Output('S');
        }

        private function fmtDate(?string $date): string
        {
            if (!$date) return '';
            $d = date_create($date);
            return $d ? $d->format('d/m/Y') : '';
        }

        /**
         * Constrói os callbacks end_document para o Dompdf da proposta.
         * Oculta o cabeçalho/rodapé fixos na página 2 (instruções de aceite).
         * A numeração de páginas é aplicada posteriormente no merge com FPDI.
         */
        private function buildPdfPageCallbacks(?string $qrCodeBase64): array
        {
            return [
                [
                    'event' => 'end_document',
                    'f' => function (int $pageNumber, int $pageCount, $canvas, $fontMetrics) use ($qrCodeBase64) {
                        $h = $canvas->get_height();
                        $w = $canvas->get_width();

                        // Oculta o cabeçalho fixo na página 2 (instruções de aceite).
                        // A capa (pág. 1) mantém o cabeçalho visível como parte do design.
                        if ($pageNumber === 2) {
                            $canvas->filled_rectangle(0, 0, $w, 80, [1, 1, 1]);
                        }

                        // Oculta o rodapé fixo na página 2 (instruções) — a capa (pág. 1) mantém a imagem visível.
                        if ($pageNumber === 2) {
                            $canvas->filled_rectangle(0, $h - 28, $w, 28, [1, 1, 1]);
                        }
                    },
                ],
            ];
        }

        /**
         * Gera um QR Code em Base64.
         * Tenta: 1) chillerlan/php-qrcode (local), 2) API externa via cURL, 3) API externa via file_get_contents.
         */
        private function generateQrCodeBase64(string $token): ?string
        {
            $qrUrl = BASE_URL . '/orcamento/aprovarPropostaPublica/' . urlencode($token);

            // 1) Tenta chillerlan/php-qrcode (biblioteca local, sem dependência externa)
            if (class_exists('chillerlan\QRCode\QRCode')) {
                try {
                    $options = new QROptions([
                        'outputType'  => QRCode::OUTPUT_IMAGE_PNG,
                        'eccLevel'    => QRCode::ECC_M,
                        'scale'       => 6,
                        'imageBase64' => false,
                    ]);
                    $qrcode = new QRCode($options);
                    $imageData = $qrcode->render($qrUrl);
                    return base64_encode($imageData);
                } catch (\Throwable $e) {
                    error_log('QRCode chillerlan failed: ' . $e->getMessage());
                }
            }

            // 2) Tenta API externa via cURL
            if (function_exists('curl_init')) {
                $imageUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=120x120&data=' . urlencode($qrUrl);
                $ch = curl_init($imageUrl);
                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT        => 5,
                    CURLOPT_SSL_VERIFYPEER => false,
                ]);
                $content = curl_exec($ch);
                curl_close($ch);
                if ($content) return base64_encode($content);
            }

            // 3) Tenta API externa via file_get_contents (requer allow_url_fopen)
            if (ini_get('allow_url_fopen')) {
                $imageUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=120x120&data=' . urlencode($qrUrl);
                $content = @file_get_contents($imageUrl, false, stream_context_create([
                    'http'  => ['timeout' => 5],
                    'https' => ['timeout' => 5],
                ]));
                if ($content) return base64_encode($content);
            }

            return null;
        }

        /** Exibe o histórico de uma proposta */
        public function historico($id)
        {
            $id = (int)$id;
            $proposta = $this->propostaModel->getPropostaById($id);
            if (!$proposta) {
                $this->setFlashMessage('error', 'Proposta não encontrada.');
                header('Location: ' . BASE_URL . '/orcamento/index');
                exit();
            }

            $proposta = $this->prepareOrcamentoData($proposta);
            $historico = $this->propostaModel->getHistoricoByPropostaId($id);

            $data = [
                'pageTitle' => 'Histórico da Proposta #' . $id,
                'proposta' => $proposta,
                'historico' => $historico
            ];

            $this->renderView('orcamento/proposta_historico', $data);
        }

        /**
         * Mostra os detalhes de uma versão do histórico, comparando com a próxima.
         * @param int $historicoId O ID do registro em propostas_historico.
         */
        public function verHistoricoDetalhe($historicoId)
        {
            $historicoId = (int)$historicoId;
            $versaoAntiga = $this->propostaModel->getHistoricoById($historicoId);

            if (!$versaoAntiga) {
                $this->setFlashMessage('error', 'Versão do histórico não encontrada.');
                header('Location: ' . BASE_URL . '/orcamento/index');
                exit();
            }

            // Decodifica o JSON para um array
            $dadosAntigos = $this->prepareOrcamentoData(json_decode($versaoAntiga['dados_proposta_json'], true) ?? []);

            // Encontra a próxima versão para comparação
            $versaoNova = $this->propostaModel->getHistoricoByPropostaIdEVersao($versaoAntiga['proposta_id'], $versaoAntiga['versao'] + 1); // Busca a próxima versão

            if ($versaoNova) {
                // Se encontrou uma versão mais nova no histórico, usa ela
                $dadosNovos = $this->prepareOrcamentoData(json_decode($versaoNova['dados_proposta_json'], true));
                $tituloComparacao = "Comparando v{$versaoAntiga['versao']} com v{$versaoNova['versao']}";
            } else {
                // Se não, compara com a versão atual da proposta na tabela principal
                $dadosNovos = $this->prepareOrcamentoData($this->propostaModel->getPropostaById($versaoAntiga['proposta_id']));
                $tituloComparacao = "Comparando v{$versaoAntiga['versao']} com a Versão Atual";
            }

            // Campos que queremos comparar (expandido para incluir os novos campos)
            $camposParaComparar = [
                'nome_proposta', 'descricao', 'objetivo', 'data_proposta', 'validade',
                'total_servicos', 'total_materiais', 'impostos_valor', 'descontos_valor', 'total_final',
                'forma_pagamento', 'prazo_execucao', 'garantias', 'condicoes', 'status',
                'servicos_json', 'materiais_json', 'custos_extras_json'
            ];
            $diferencas = [];

            // Para usar a biblioteca jfcherng/php-diff, você precisa instalá-la via Composer:
            // composer require jfcherng/php-diff
            // E descomentar o bloco abaixo.
            // Por padrão, vamos fazer uma comparação simples.

            // Configurações para o renderizador de diff
            $data = [
                'pageTitle' => 'Detalhes da Revisão',
                'tituloComparacao' => $tituloComparacao,
                'proposta' => $dadosNovos, // Para referência
                'diferencas' => $diferencas // Temporariamente desativado para evitar erro se a lib não estiver instalada
            ];

            $this->renderView('orcamento/proposta_historico_detalhe', $data);
        }

        /**
         * Envia a proposta por e-mail com o PDF em anexo.
         * @param int $id O ID da proposta.
         */
        public function enviarEmail($id)
        {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                header('Location: ' . BASE_URL . '/orcamento/ver/' . $id);
                exit();
            }

            // Validação de CSRF
            if (!isset($_POST['csrf_token']) || !$this->validateCsrfToken($_POST['csrf_token'])) {
                $this->setFlashMessage('error', 'Erro de validação de segurança (CSRF).');
                header('Location: ' . BASE_URL . '/orcamento/ver/' . $id);
                exit();
            }

            $id = (int)$id;
            $proposta = $this->propostaModel->getPropostaById($id);
            if (!$proposta) {
                $this->setFlashMessage('error', 'Proposta não encontrada para envio.');
                header('Location: ' . BASE_URL . '/orcamento/index');
                exit();
            }

            $proposta = $this->prepareOrcamentoData($proposta);
            $empresa = $this->empresaModel->getDadosEmpresa();

            $userName = $this->session->get('user_name', 'Usuário');
            $userEmail = $this->session->get('user_email', MAIL_FROM_ADDRESS);
            $userCargo = $this->session->get('user_cargo', '');
            $remetenteNome = $userName;

            // Aumenta recursos para processamento do PDF no anexo
            ini_set('memory_limit', '512M');
            set_time_limit(300);

            // 1. Gerar o HTML do PDF
            if (!empty($proposta['token_aprovacao'])) {
                $proposta['qr_code_base64'] = $this->generateQrCodeBase64($proposta['token_aprovacao']);
            }
            ob_start();
            $data = ['proposta_pdf' => $proposta, 'empresa' => $empresa];
            $data['qr_code_base64'] = $proposta['qr_code_base64'] ?? null;
            $this->renderPartial('orcamento/proposta_pdf', $data);
            $html = ob_get_clean();

            // 2. Gerar o PDF em memória
            $options = new Options();
            $options->setIsRemoteEnabled(false);
            $options->setIsPhpEnabled(true);
            $options->setIsHtml5ParserEnabled(true);
            $options->setDefaultFont('Helvetica');

            $dompdf = new Dompdf($options);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->setCallbacks($this->buildPdfPageCallbacks(
                $proposta['qr_code_base64'] ?? null,
                $proposta['versao_documento'] ?? ''
            ));

            try {
                $dompdf->render();

                $pdfOutput = $dompdf->output();
            } catch (\Exception $e) {
                error_log("Falha na renderização do PDF para e-mail: " . $e->getMessage());
                $this->setFlashMessage('error', 'Falha técnica ao gerar o anexo PDF.');
                header('Location: ' . BASE_URL . '/orcamento/ver/' . $id);
                exit();
            }

            // 3. Configurar e enviar o e-mail com PHPMailer
            $mail = new PHPMailer(true);
            try {
                // Configurações do servidor SMTP (vindas de settings.php)
                $mail->isSMTP();
                // Se o erro persistir, mude para 2 para ver o log detalhado no navegador
                $mail->SMTPDebug = 0; 
                $mail->Host       = MAIL_HOST;
                $mail->SMTPAuth   = true;
                $mail->Username   = MAIL_USERNAME;
                $mail->Password   = MAIL_PASSWORD;

                // Ajuste na detecção de criptografia para ser mais robusto
                $encryption = defined('MAIL_ENCRYPTION') ? strtolower(MAIL_ENCRYPTION) : '';
                if ($encryption === 'ssl') {
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // Porta 465
                } elseif ($encryption === 'tls') {
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Porta 587
                }

                $mail->Port       = MAIL_PORT;
                $mail->CharSet    = 'UTF-8';

                // Remetente e Destinatário
                $mail->setFrom(MAIL_FROM_ADDRESS, $remetenteNome);
                $mail->addAddress($_POST['email_destinatario']);
                $mail->addReplyTo($userEmail, $remetenteNome);

                // Anexo
                $nomeArquivo = 'proposta_' . str_pad($id, 4, '0', STR_PAD_LEFT) . '.pdf';
                $mail->addStringAttachment($pdfOutput, $nomeArquivo);

                // Conteúdo do E-mail
                $corpoTexto = $_POST['email_corpo'];
                $logoPath = $empresa['logo_path'] ?? '';
                $logoCid = '';
                if ($logoPath) {
                    $logoFile = ROOT_PATH . '/public/uploads/logos/' . $logoPath;
                    if (file_exists($logoFile)) {
                        $logoCid = 'logo_empresa';
                        $mail->addEmbeddedImage($logoFile, $logoCid);
                    }
                }

                $nomeFantasia = htmlspecialchars($empresa['nome_fantasia'] ?? $empresa['razao_social'] ?? '');
                $cargoExibicao = $userCargo ? htmlspecialchars($userCargo) . ' - ' : '';
                $assinaturaHtml = '<div style="border-top:1px solid #ccc; margin-top:20px; padding-top:15px;">';
                if ($logoCid) {
                    $assinaturaHtml .= '<img src="cid:' . $logoCid . '" alt="' . $nomeFantasia . '" style="height:40px; width:auto; margin-bottom:8px;"><br>';
                }
                $assinaturaHtml .= '<strong style="font-size:14px; color:#333;">' . $nomeFantasia . '</strong><br>';
                $assinaturaHtml .= '<span style="font-size:13px; color:#555;">' . $cargoExibicao . htmlspecialchars($userName) . '</span><br>';
                $assinaturaHtml .= '<span style="font-size:11px; color:#999;">' . htmlspecialchars($userEmail) . '</span>';
                $assinaturaHtml .= '</div>';

                $mail->isHTML(true);
                $mail->Subject = $_POST['email_assunto'];
                $mail->Body    = nl2br(htmlspecialchars($corpoTexto)) . $assinaturaHtml;

                $mail->send();
                $this->propostaModel->updateProposalStatus($id, 'Enviada', '');
                $this->setFlashMessage('success', 'E-mail enviado com sucesso!');

                $propostaTitulo = $proposta['titulo'] ?? $proposta['nome_proposta'] ?? 'Sem título';
                $this->clientesModel->registrarInteracao([
                    'cliente_id' => $proposta['cliente_id'],
                    'usuario_id' => $this->session->get('user_id'),
                    'tipo_interacao' => 'E-mail',
                    'descricao' => "Proposta #{$id} - {$propostaTitulo} enviada por e-mail para {$_POST['email_destinatario']}",
                    'data_interacao' => date('Y-m-d H:i:s')
                ]);
            } catch (Exception $e) {
                $errorInfo = $mail->ErrorInfo;
                error_log("Erro de Envio de E-mail (Proposta #{$id}): " . $errorInfo);
                
                $msg = "Erro ao conectar ao servidor de e-mail da HostGator (Titan).";
                
                if (strpos($errorInfo, 'Connection refused') !== false) {
                    $msg .= " A conexão foi recusada pelo servidor. Tente usar a porta 587 com TLS ou verifique se o host '" . MAIL_HOST . "' está correto no arquivo settings.php.";
                } else {
                    $msg .= " Detalhes: " . $errorInfo;
                }

                $this->setFlashMessage('error', $msg);
            }

            header('Location: ' . BASE_URL . '/orcamento/ver/' . $id);
            exit();
        }

        /**
         * Envia uma proposta para aprovação do diretor via AJAX.
         */
        public function enviarParaDiretorAjax($id)
        {
            header('Content-Type: application/json');
            $id = (int)$id;

            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                echo json_encode(['success' => false, 'message' => 'Método inválido.']);
                exit();
            }

            if (!isset($_POST['csrf_token']) || !$this->validateCsrfToken($_POST['csrf_token'])) {
                echo json_encode(['success' => false, 'message' => 'Erro de validação de segurança (CSRF).']);
                exit();
            }

            $proposta = $this->propostaModel->getPropostaById($id);
            if (!$proposta) {
                echo json_encode(['success' => false, 'message' => 'Proposta não encontrada.']);
                exit();
            }

            if ($proposta['aprovacao_diretor_status'] === 'pendente') {
                echo json_encode(['success' => false, 'message' => 'Esta proposta já foi enviada para aprovação do diretor e está pendente.']);
                exit();
            }

            if ($proposta['aprovacao_diretor_status'] === 'aprovado') {
                echo json_encode(['success' => false, 'message' => 'Esta proposta já foi aprovada pelo diretor.']);
                exit();
            }

            $usuarioId = $this->session->get('user_id');
            if ($this->propostaModel->enviarParaDiretor($id, $usuarioId)) {
                // Notifica diretores (usuários com permissão comercial_propostas_view)
                $this->notificarDiretores($proposta);
                echo json_encode(['success' => true, 'message' => 'Proposta enviada para aprovação do diretor com sucesso!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Erro ao enviar proposta para aprovação.']);
            }
            exit();
        }

        /**
         * Notifica os diretores sobre uma proposta pendente de aprovação.
         */
        private function notificarDiretores(array $proposta): void
        {
            try {
                $perfis = $this->perfilModel->getAll();
                $perfisIds = [];
                foreach ($perfis as $p) {
                    $permissoes = json_decode($p['permissoes'] ?? '[]', true);
                    if (is_array($permissoes) && (in_array('comercial_propostas_view', $permissoes) || in_array('*', $permissoes))) {
                        $perfisIds[] = $p['perfil_id'];
                    }
                }

                if (empty($perfisIds)) return;

                $usuarios = $this->usuarioModel->getListaUsuarios('Ativo');
                $numRef = $proposta['numero_proposta'] ?? $proposta['id'];

                foreach ($usuarios as $u) {
                    if (in_array($u['perfil_id'], $perfisIds)) {
                        $this->notificacoesModel->criarNotificacao(
                            (int)$u['id'],
                            'Proposta Aguardando Aprovação',
                            "A proposta #{$numRef} - {$proposta['nome_proposta']} foi enviada para aprovação do diretor.",
                            BASE_URL . "/orcamento/ver/{$proposta['id']}"
                        );
                    }
                }
            } catch (\Exception $e) {
                error_log('Erro ao notificar diretores: ' . $e->getMessage());
            }
        }

        /**
         * Retorna o modal de aprovação do diretor via AJAX.
         */
        public function getDiretorModalAjax($id)
        {
            $id = (int)$id;
            $proposta = $this->propostaModel->getPropostaById($id);
            if (!$proposta) {
                echo '<p class="text-red-500 p-4">Proposta não encontrada.</p>';
                exit();
            }

            $proposta = $this->prepareOrcamentoData($proposta);

            // Busca dados do cliente
            $clienteEmail = '';
            $clienteTelefone = '';
            if (!empty($proposta['cliente_id'])) {
                $cliente = $this->clientesModel->getClienteById((int)$proposta['cliente_id']);
                $clienteEmail = $cliente['email'] ?? '';
                $clienteTelefone = $cliente['telefone'] ?? '';
            }

            $data = [
                'proposta' => $proposta,
                'cliente_email' => $clienteEmail,
                'cliente_telefone' => $clienteTelefone,
                'csrf_token' => $this->generateCsrfToken(),
                'isAdmin' => $this->session->isAdmin(),
            ];
            $this->renderPartial('orcamento/diretor_modal', $data);
        }

        /**
         * Diretor aprova a proposta via AJAX.
         */
        public function aprovarDiretorAjax($id)
        {
            header('Content-Type: application/json');
            $id = (int)$id;

            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                echo json_encode(['success' => false, 'message' => 'Método inválido.']);
                exit();
            }

            if (!isset($_POST['csrf_token']) || !$this->validateCsrfToken($_POST['csrf_token'])) {
                echo json_encode(['success' => false, 'message' => 'Erro de validação de segurança (CSRF).']);
                exit();
            }

            $proposta = $this->propostaModel->getPropostaById($id);
            if (!$proposta) {
                echo json_encode(['success' => false, 'message' => 'Proposta não encontrada.']);
                exit();
            }

            if ($proposta['aprovacao_diretor_status'] !== 'pendente') {
                echo json_encode(['success' => false, 'message' => 'Esta proposta não está pendente de aprovação.']);
                exit();
            }

            $diretorId = $this->session->get('user_id');
            if ($this->propostaModel->aprovarDiretor($id, $diretorId)) {
                // Salva histórico
                $motivo = 'Aprovada pelo diretor';
                $propostaAtual = $this->propostaModel->getPropostaById($id);
                if ($propostaAtual) {
                    // Usa updateProposalStatus com um motivo específico
                    $this->propostaModel->updateProposalStatus($id, $propostaAtual['status'], $motivo, $diretorId, false);
                }

                echo json_encode([
                    'success' => true,
                    'message' => 'Proposta aprovada com sucesso!',
                    'enviar_cliente' => true,
                    'proposta_id' => $id,
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Erro ao aprovar proposta.']);
            }
            exit();
        }

        /**
         * Diretor rejeita a proposta com justificativa via AJAX.
         */
        public function rejeitarDiretorAjax($id)
        {
            header('Content-Type: application/json');
            $id = (int)$id;

            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                echo json_encode(['success' => false, 'message' => 'Método inválido.']);
                exit();
            }

            if (!isset($_POST['csrf_token']) || !$this->validateCsrfToken($_POST['csrf_token'])) {
                echo json_encode(['success' => false, 'message' => 'Erro de validação de segurança (CSRF).']);
                exit();
            }

            $justificativa = trim($_POST['justificativa'] ?? '');
            if (empty($justificativa)) {
                echo json_encode(['success' => false, 'message' => 'A justificativa é obrigatória para rejeitar a proposta.']);
                exit();
            }

            $proposta = $this->propostaModel->getPropostaById($id);
            if (!$proposta) {
                echo json_encode(['success' => false, 'message' => 'Proposta não encontrada.']);
                exit();
            }

            if ($proposta['aprovacao_diretor_status'] !== 'pendente') {
                echo json_encode(['success' => false, 'message' => 'Esta proposta não está pendente de aprovação.']);
                exit();
            }

            $diretorId = $this->session->get('user_id');
            if ($this->propostaModel->rejeitarDiretor($id, $diretorId, $justificativa)) {
                // Notifica o usuário que enviou
                if (!empty($proposta['enviado_diretor_por'])) {
                    $this->notificacoesModel->criarNotificacao(
                        (int)$proposta['enviado_diretor_por'],
                        'Proposta Rejeitada pelo Diretor',
                        "A proposta #{$proposta['numero_proposta']} foi rejeitada. Motivo: {$justificativa}",
                        BASE_URL . "/orcamento/editar/{$id}"
                    );
                }

                echo json_encode(['success' => true, 'message' => 'Proposta rejeitada e retornada para edição.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Erro ao rejeitar proposta.']);
            }
            exit();
        }

        /**
         * Gera ou recupera o link de aprovação pública de uma proposta.
         * @param int $id
         */
        public function gerarLinkPublico($id)
        {
            header('Content-Type: application/json');
            $id = (int)$id;
            $proposta = $this->propostaModel->getPropostaById($id);

            if (!$proposta) {
                echo json_encode(['success' => false, 'message' => 'Proposta não encontrada.']);
                exit;
            }

            // Se não tiver token, ou se o token atual já expirou, gera um novo
            $tokenExpirado = !empty($proposta['token_validade']) && strtotime($proposta['token_validade']) < time();
            if (empty($proposta['token_aprovacao']) || $tokenExpirado) {
                $token = $this->propostaModel->generateApprovalToken();
                $validade = date('Y-m-d H:i:s', strtotime('+7 days'));
                
                // Atualiza diretamente no banco usando a conexão do modelo
                $stmt = $this->propostaModel->getDbConnection()->prepare("UPDATE orcamento_proposta SET token_aprovacao = ?, token_validade = ? WHERE id = ?");
                if ($stmt->execute([$token, $validade, $id])) {
                    $proposta['token_aprovacao'] = $token;
                    $proposta['token_validade'] = $validade;
                } else {
                    echo json_encode(['success' => false, 'message' => 'Erro ao registrar token de segurança.']);
                    exit;
                }
            }

            // Constrói a URL pública baseada na rota existente no controlador
            $link = BASE_URL . "/orcamento/aprovarPropostaPublica/" . $proposta['token_aprovacao'];

            $origem = $_GET['origem'] ?? 'link';
            $usuarioId = $this->session->get('user_id');
            $propostaTitulo = $proposta['titulo'] ?? $proposta['nome_proposta'] ?? 'Sem título';

            if ($origem === 'whatsapp') {
                $tipo = 'WhatsApp';
                $descricao = "Link da proposta #{$id} - {$propostaTitulo} enviado via WhatsApp";
            } else {
                $tipo = 'Link';
                $descricao = "Link público gerado para a proposta #{$id} - {$propostaTitulo}";
            }

            $this->clientesModel->registrarInteracao([
                'cliente_id' => $proposta['cliente_id'],
                'usuario_id' => $usuarioId,
                'tipo_interacao' => $tipo,
                'descricao' => $descricao,
                'data_interacao' => date('Y-m-d H:i:s')
            ]);

            $this->propostaModel->updateProposalStatus($id, 'Enviada', '');

            echo json_encode(['success' => true, 'link' => $link]);
            exit;
        }

        /**
         * Exibe a tela de gerenciamento de categorias e unidades.
         */
        public function gerenciarItens()
        {
            $data = [
                'pageTitle' => 'Gerenciar Atributos de Itens',
                'categorias' => $this->propostaModel->getItemCategorias(false),
                'unidades' => $this->propostaModel->getItemUnidades(false),
            ];
            $this->renderView('orcamento/gerenciar_itens', $data);
        }

        /**
         * Retorna o próximo número de proposta em JSON (para AJAX).
         * @param int $clienteId
         */
        public function getProximoNumeroAjax($clienteId)
        {
            header('Content-Type: application/json');
            $clienteId = (int)$clienteId;
            
            if ($clienteId <= 0) {
                echo json_encode(['success' => false, 'message' => 'ID de cliente inválido.']);
                exit;
            }

            $numero = $this->propostaModel->getNextProposalNumber($clienteId);
            echo json_encode(['success' => true, 'numero' => $numero]);
            exit;
        }

        /**
         * Adiciona uma nova categoria de item via AJAX.
         */
        public function addItemCategoriaAjax()
        {
            header('Content-Type: application/json');
            $nome = trim($_POST['nome'] ?? '');
            if (empty($nome)) {
                echo json_encode(['success' => false, 'message' => 'Nome inválido.']);
                exit;
            }
            
            $success = $this->propostaModel->addItemCategoria($nome);
            echo json_encode(['success' => $success]);
            exit;
        }

        /**
         * Atualiza uma categoria de item via AJAX.
         */
        public function updateItemCategoriaAjax()
        {
            header('Content-Type: application/json');
            $id = (int)($_POST['id'] ?? 0);
            $nome = trim($_POST['nome'] ?? '');
            if ($id <= 0 || empty($nome)) {
                echo json_encode(['success' => false, 'message' => 'Dados inválidos.']);
                exit;
            }
            $success = $this->propostaModel->updateItemCategoria($id, $nome);
            echo json_encode(['success' => $success]);
            exit;
        }

        /**
         * Exclui uma categoria de item via AJAX.
         */
        public function deleteItemCategoriaAjax()
        {
            header('Content-Type: application/json');
            $id = (int)($_POST['id'] ?? 0);
            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'ID inválido.']);
                exit;
            }
            $success = $this->propostaModel->deleteItemCategoria($id);
            if ($success) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => $this->propostaModel->getLastError() ?: 'Erro ao excluir categoria.']);
            }
            exit;
        }

        /**
         * Adiciona uma nova unidade de medida via AJAX.
         */
        public function addItemUnidadeAjax()
        {
            header('Content-Type: application/json');
            $nome = trim($_POST['nome'] ?? '');
            if (empty($nome)) {
                echo json_encode(['success' => false, 'message' => 'Nome inválido.']);
                exit;
            }
            
            $success = $this->propostaModel->addItemUnidade($nome);
            echo json_encode(['success' => $success]);
            exit;
        }

        /**
         * Atualiza uma unidade de medida via AJAX.
         */
        public function updateItemUnidadeAjax()
        {
            header('Content-Type: application/json');
            $id = (int)($_POST['id'] ?? 0);
            $nome = trim($_POST['nome'] ?? '');
            if ($id <= 0 || empty($nome)) {
                echo json_encode(['success' => false, 'message' => 'Dados inválidos.']);
                exit;
            }
            $success = $this->propostaModel->updateItemUnidade($id, $nome);
            echo json_encode(['success' => $success]);
            exit;
        }

        /**
         * Exclui uma unidade de medida via AJAX.
         */
        public function deleteItemUnidadeAjax()
        {
            header('Content-Type: application/json');
            $id = (int)($_POST['id'] ?? 0);
            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'ID inválido.']);
                exit;
            }
            $success = $this->propostaModel->deleteItemUnidade($id);
            if ($success) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => $this->propostaModel->getLastError() ?: 'Erro ao excluir unidade.']);
            }
            exit;
        }

        /**
         * Permite ao cliente aprovar uma proposta usando um token único.
         * Esta é uma rota pública, não requer autenticação.
         * @param string $token O token de aprovação.
         */
        public function aprovarPropostaPublica(string $token)
        {
            $proposta = $this->propostaModel->getPropostaByToken($token);

            if (!$proposta) {
                $this->setFlashMessage('error', 'Token de aprovação inválido ou proposta não encontrada.');
                $this->renderPartial('orcamento/aprovacao_publica', [
                    'pageTitle' => 'Erro de Aprovação',
                    'message'   => 'Token inválido.',
                    'empresa'   => $this->empresaModel->getDadosEmpresa(),
                ]);
                exit();
            }

            // Normaliza os dados para a visualização (mapeia colunas do banco para nomes amigáveis)
            $proposta = $this->prepareOrcamentoData($proposta);
            $empresa  = $this->empresaModel->getDadosEmpresa();

            // CSRF token derivado do token de aprovação (proteção para formulários públicos)
            $csrf_token = hash_hmac('sha256', $token . 'csrf_public_approval', SECRET_KEY);

            // Verifica PRIMEIRO se a proposta já foi aprovada/rejeitada
            if ($proposta['status'] === 'Aprovada' || $proposta['status'] === 'Rejeitada') {
                $this->renderPartial('orcamento/aprovacao_publica', [
                    'pageTitle'  => 'Proposta #' . ($proposta['numero_proposta'] ?? $proposta['id']),
                    'proposta'   => $proposta,
                    'token'      => $token,
                    'empresa'    => $empresa,
                    'csrf_token' => $csrf_token,
                ]);
                exit();
            }

            // Verifica a validade do token (apenas para propostas ainda pendentes)
            $tokenValidade = new \DateTime($proposta['token_validade']);
            $agora = new \DateTime();

            if ($agora > $tokenValidade) {
                $this->setFlashMessage('error', 'O link de aprovação expirou. Por favor, solicite um novo.');
                $this->renderPartial('orcamento/aprovacao_publica', [
                    'pageTitle' => 'Erro de Aprovação',
                    'message'   => 'Link expirado.',
                    'empresa'   => $empresa,
                ]);
                exit();
            }

            // Se for POST, processa a aprovação/rejeição
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // Valida CSRF
                $postCsrf = $_POST['csrf_token'] ?? '';
                if (!hash_equals($csrf_token, $postCsrf)) {
                    $this->setFlashMessage('error', 'Erro de validação de segurança. Recarregue a página e tente novamente.');
                    $this->renderPartial('orcamento/aprovacao_publica', [
                        'pageTitle'  => 'Aprovar Proposta',
                        'proposta'   => $proposta,
                        'token'      => $token,
                        'empresa'    => $empresa,
                        'csrf_token' => $csrf_token,
                    ]);
                    exit();
                }

                $acao  = filter_input(INPUT_POST, 'acao', FILTER_SANITIZE_SPECIAL_CHARS);
                $motivo = filter_input(INPUT_POST, 'motivo', FILTER_SANITIZE_SPECIAL_CHARS);

                if ($acao === 'aprovar') {
                    $aceiteNome = trim(filter_input(INPUT_POST, 'aceite_nome', FILTER_SANITIZE_SPECIAL_CHARS) ?? '');

                    // Validação server-side do nome de aceite
                    if (empty($aceiteNome)) {
                        $this->setFlashMessage('error', 'Informe seu nome completo para confirmar a aprovação.');
                        $this->renderPartial('orcamento/aprovacao_publica', [
                            'pageTitle'  => 'Aprovar Proposta',
                            'proposta'   => $proposta,
                            'token'      => $token,
                            'empresa'    => $empresa,
                            'csrf_token' => $csrf_token,
                        ]);
                        exit();
                    }

                    $aceiteIp   = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '';
                    $aceiteIp   = trim(explode(',', $aceiteIp)[0]);
                    
                    // Store the original proposal ID for re-fetching
                    $originalPropostaId = $proposta['id'];

                    if ($this->propostaModel->approveProposalByToken($proposta['id'], 'Aprovada pelo cliente via link', $aceiteIp, $aceiteNome)) {
                        // Re-fetch the proposal to ensure all fields are up-to-date for the email notification
                        $proposta = $this->propostaModel->getPropostaById($originalPropostaId);
                        // Send email notification to administrators
                        $this->sendAdminProposalApprovalNotification($proposta, $aceiteNome, $aceiteIp);

                        // Registra notificação interna para disparar o alerta sonoro no dashboard
                        $this->notificacoesModel->criarNotificacao(
                            (int)$proposta['responsavel_interno_id'],
                            'Proposta Aprovada',
                            "A proposta #{$proposta['id']} foi aprovada via link público por {$aceiteNome}.",
                            BASE_URL . "/orcamento/ver/" . $proposta['id']
                        );

                        $numeroProposta = htmlspecialchars($proposta['numero_proposta'] ?? $proposta['id']);
                        echo <<<HTML
    <!DOCTYPE html>
    <html lang="pt-BR">
    <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proposta Aprovada</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
    body {
        background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
        min-height: 100vh;
        display: flex; align-items: center; justify-content: center;
        font-family: Inter, ui-sans-serif, system-ui, sans-serif;
        margin: 0;
    }
    .card {
        background: #fff; border-radius: 1.5rem;
        box-shadow: 0 24px 60px rgba(15,23,42,.1);
        padding: 3rem 2.5rem; text-align: center;
        max-width: 440px; width: 90%;
        animation: popIn .5s cubic-bezier(.34,1.56,.64,1) both;
    }
    @keyframes popIn {
        from { opacity:0; transform:scale(.85); }
        to   { opacity:1; transform:scale(1); }
    }
    .icon-wrap {
        width: 80px; height: 80px; border-radius: 50%;
        background: #d1fae5; color: #059669;
        display: flex; align-items: center; justify-content: center;
        font-size: 2.2rem; margin: 0 auto 1.5rem;
        animation: scalePop .6s .2s cubic-bezier(.34,1.56,.64,1) both;
    }
    @keyframes scalePop {
        from { transform:scale(0); opacity:0; }
        to   { transform:scale(1); opacity:1; }
    }
    h1 { font-size: 1.6rem; font-weight: 800; color: #065f46; margin-bottom: .5rem; }
    p  { color: #6b7280; font-size: .95rem; line-height: 1.6; margin-bottom: 1.75rem; }
    .progress-bar {
        height: 4px; background: #e5e7eb; border-radius: 99px; overflow: hidden;
    }
    .progress-fill {
        height: 100%; background: #10b981; border-radius: 99px;
        width: 100%;
        animation: drain 4s linear forwards;
    }
    @keyframes drain { from { width:100%; } to { width:0%; } }
    .countdown { font-size: .78rem; color: #9ca3af; margin-top: .65rem; }
    </style>
    </head>
    <body>
    <div class="card">
        <div class="icon-wrap"><i class="fas fa-check"></i></div>
        <h1>Proposta Aprovada!</h1>
        <p>
            A proposta <strong>#{$numeroProposta}</strong> foi aprovada com sucesso.<br>
            Nossa equipe entrará em contato em breve.
        </p>
        <div class="progress-bar"><div class="progress-fill"></div></div>
        <div class="countdown">Esta janela fechará automaticamente em <span id="cd">4</span>s</div>
    </div>
    <script>
    var s = 4;
    var t = setInterval(function() {
        s--;
        document.getElementById('cd').textContent = s;
        if (s <= 0) {
            clearInterval(t);
            window.close();
            document.querySelector('.card').innerHTML =
                '<div style="color:#065f46;font-size:1rem;font-weight:600"><i class="fas fa-check-circle" style="font-size:2rem;display:block;margin-bottom:1rem"></i>Proposta aprovada!<br><span style="font-weight:400;font-size:.875rem;color:#6b7280">Você já pode fechar esta aba.</span></div>';
        }
    }, 1000);
    </script>
    </body>
    </html>
    HTML;
                        exit();
                    } else {
                        error_log("Erro ao aprovar proposta no model: " . $this->propostaModel->getLastError());
                        $this->setFlashMessage('error', 'Erro ao aprovar a proposta. Tente novamente.');
                    }
                } elseif ($acao === 'rejeitar') {
                    if ($this->propostaModel->updateProposalStatus($proposta['id'], 'Rejeitada', 'Rejeitada pelo cliente via link: ' . $motivo)) {
                        $this->setFlashMessage('info', 'Proposta rejeitada. Agradecemos seu feedback.');
                        $this->renderPartial('orcamento/aprovacao_publica', [
                            'pageTitle' => 'Proposta Rejeitada',
                            'message'   => 'Proposta rejeitada.',
                            'empresa'   => $empresa,
                        ]);
                        exit();
                    } else {
                        $this->setFlashMessage('error', 'Erro ao rejeitar a proposta. Tente novamente.');
                    }
                }
            }

            // Exibe a página de aprovação/rejeição
            $data = [
                'pageTitle'  => 'Aprovar Proposta',
                'proposta'   => $proposta,
                'token'      => $token,
                'empresa'    => $empresa,
                'csrf_token' => $csrf_token,
            ];
            $this->renderPartial('orcamento/aprovacao_publica', $data);
        }

        /**
         * Envia um e-mail de notificação para o administrador sobre uma proposta aprovada.
         * @param array $proposta Dados da proposta aprovada.
         * @param string $aceiteNome Nome de quem aprovou.
         * @param string $aceiteIp IP de quem aprovou.
         * @return bool
         */
        private function sendAdminProposalApprovalNotification(array $proposta, string $aceiteNome, string $aceiteIp): bool
        {
            // Certifique-se de que as constantes de e-mail estão definidas
            if (!defined('MAIL_HOST')) {
                error_log("Configurações de e-mail (MAIL_HOST) não definidas.");
                return false;
            }

            $destinatarios = [];

            // Tenta usar a constante definida (caso exista no config.php)
            // Exemplo: define('PROPOSTA_APROVADOR_EMAIL', 'admin@seusistema.com');
            if (defined('PROPOSTA_APROVADOR_EMAIL') && !empty(PROPOSTA_APROVADOR_EMAIL)) {
                $destinatarios = array_merge($destinatarios, (array)PROPOSTA_APROVADOR_EMAIL);
            } else {
                // Se não definida, busca automaticamente usuários com permissão de gerenciar propostas ou admin total
                $perfis = $this->perfilModel->getAll();
                $perfisAprovadores = [];

                foreach ($perfis as $p) {
                    $permissoes = json_decode($p['permissoes'] ?? '[]', true);
                    // Verifica se tem permissão específica ou é admin total (*)
                    // Usando 'comercial_propostas_view' como uma permissão relevante para quem deve ser notificado
                    if (is_array($permissoes) && (in_array('comercial_propostas_view', $permissoes) || in_array('*', $permissoes))) {
                        $perfisAprovadores[] = $p['perfil_id'];
                    }
                }

                if (!empty($perfisAprovadores)) {
                    $usuarios = $this->usuarioModel->getListaUsuarios();
                    foreach ($usuarios as $u) {
                        // Verifica se o usuário tem um dos perfis aprovadores, está ativo e tem e-mail
                        if (in_array($u['perfil_id'], $perfisAprovadores) && !empty($u['email']) && strtolower($u['status']) === 'ativo') {
                            $destinatarios[] = $u['email'];
                        }
                    }
                }
            }

            // Remove duplicatas
            $destinatarios = array_unique($destinatarios);

            if (empty($destinatarios)) {
                error_log("Nenhum destinatário encontrado para notificação de aprovação de proposta (Constante não definida e nenhum usuário com permissão encontrado).");
                return false;
            }

            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = defined('MAIL_HOST') ? MAIL_HOST : 'localhost';
                $mail->SMTPAuth   = true;
                $mail->Username   = defined('MAIL_USERNAME') ? MAIL_USERNAME : '';
                $mail->Password   = defined('MAIL_PASSWORD') ? MAIL_PASSWORD : '';
                $mail->SMTPSecure = (defined('MAIL_ENCRYPTION') && MAIL_ENCRYPTION === 'ssl') ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = defined('MAIL_PORT') ? MAIL_PORT : 587;
                $mail->CharSet    = 'UTF-8';

                $fromEmail = defined('MAIL_FROM_ADDRESS') ? MAIL_FROM_ADDRESS : 'noreply@sysenvicorp.com';
                $fromName = defined('MAIL_FROM_NAME') ? MAIL_FROM_NAME : 'SysEnviCorp';

                $mail->setFrom($fromEmail, $fromName);
                
                foreach ($destinatarios as $email) {
                    $mail->addAddress($email);
                }

                $mail->isHTML(true);
                $mail->Subject = 'Proposta Aprovada pelo Cliente: #' . ($proposta['numero_proposta'] ?? $proposta['id']);

                $valorFormatado = number_format($proposta['total_final'], 2, ',', '.');
                $linkProposta = BASE_URL . "/orcamento/ver/" . $proposta['id'];

                $corpo = "<p>Olá,</p><p>Uma proposta foi aprovada pelo cliente:</p><ul><li><strong>Proposta:</strong> #" . htmlspecialchars($proposta['numero_proposta'] ?? $proposta['id']) . " - " . htmlspecialchars($proposta['nome_proposta']) . "</li><li><strong>Cliente:</strong> " . htmlspecialchars($proposta['cliente_nome']) . "</li><li><strong>Valor Total:</strong> R$ {$valorFormatado}</li><li><strong>Aprovado por:</strong> " . htmlspecialchars($aceiteNome) . "</li><li><strong>IP de Aceite:</strong> " . htmlspecialchars($aceiteIp) . "</li><li><strong>Data/Hora de Aceite:</strong> " . date('d/m/Y H:i:s') . "</li></ul><p>Para mais detalhes, acesse a proposta no sistema:</p><p><a href='{$linkProposta}'>Ver Proposta no Sistema</a></p><br><p>Atenciosamente,<br>SysEnviCorp</p>";
                $mail->Body = $corpo;

                $mail->send();
                return true;
            } catch (Exception $e) {
                error_log("Erro ao enviar e-mail de notificação de aprovação de proposta: {$mail->ErrorInfo}");
                return false;
            }
        }

        /**
         * Atualiza o status de uma proposta via AJAX.
         * @param int $id ID da proposta.
         */
        public function updateStatusAjax($id)
        {
            header('Content-Type: application/json');
            $id = (int) $id; // Garantir que seja inteiro
            error_log("DEBUG: updateStatusAjax chamado - ID: $id (tipo: " . gettype($id) . "), Método: " . $_SERVER['REQUEST_METHOD']);

            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                error_log("DEBUG: Método inválido");
                echo json_encode(['success' => false, 'message' => 'Método inválido.']);
                exit();
            }

            // Validação de CSRF obrigatória para produção
            if (!isset($_POST['csrf_token']) || !$this->validateCsrfToken($_POST['csrf_token'])) {
                echo json_encode(['success' => false, 'message' => 'Erro de validação de segurança (CSRF).']);
                exit();
            }

            $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_SPECIAL_CHARS);
            $motivo = filter_input(INPUT_POST, 'motivo', FILTER_SANITIZE_SPECIAL_CHARS) ?: 'Alteração de status via AJAX';
            $confirmacaoDuplicado = filter_input(INPUT_POST, 'confirmacao_duplicado', FILTER_SANITIZE_SPECIAL_CHARS); // 'sim', 'nao' ou null
            $usuario_id = $this->session->get('user_id');

            error_log("DEBUG: Dados recebidos - Status: $status, Motivo: $motivo, Usuario: $usuario_id");

            if (empty($status)) {
                error_log("DEBUG: Status vazio");
                echo json_encode(['success' => false, 'message' => 'Status é obrigatório.']);
                exit();
            }

            // Verifica se a proposta está bloqueada (aprovada e usuário não é admin)
            $propostaAtual = $this->propostaModel->getPropostaById($id);
            if ($propostaAtual && $this->isPropostaLocked($propostaAtual)) {
                echo json_encode(['success' => false, 'message' => 'Proposta aprovada está bloqueada para alterações de status. Apenas administradores podem modificá-la.']);
                exit();
            }

            if ($status === 'Aprovada' && $confirmacaoDuplicado === null) {
                $proposta = $this->propostaModel->getPropostaById($id);
                if ($proposta && !empty($proposta['cliente_id'])) {
                    $contratosExistentes = $this->contratosModel->getContratosByClienteId((int)$proposta['cliente_id']);
                    $qtdContratos = count($contratosExistentes);
                    
                    if ($qtdContratos > 0) {
                        // Interrompe o fluxo e solicita confirmação do frontend
                        echo json_encode([
                            'success' => true,
                            'confirmacao_necessaria' => true,
                            'cliente_nome' => $proposta['cliente_nome'],
                            'contratos' => $contratosExistentes,
                            'message' => "O cliente '{$proposta['cliente_nome']}' já possui {$qtdContratos} contrato(s) ativo(s)."
                        ]);
                        exit();
                    }
                }
            }

            // Verifica se o usuário optou por vincular um contrato existente
            $contratoVinculadoId = null;
            if ($confirmacaoDuplicado && strpos($confirmacaoDuplicado, 'vincular_') === 0) {
                $contratoVinculadoId = (int) substr($confirmacaoDuplicado, 9);
                $confirmacaoDuplicado = 'nao'; // Não cria novo contrato
            }

            // Define se cria o contrato: true se não houver duplicidade ou se o usuário confirmou 'sim'
            $criarContrato = ($confirmacaoDuplicado !== 'nao');

            $result = $this->propostaModel->updateProposalStatus($id, $status, $motivo, $usuario_id, $criarContrato);
            error_log("DEBUG: Resultado do updateProposalStatus: " . ($result ? 'SUCESSO' : 'FALHA'));

            if ($result) {
                // Se o usuário optou por vincular um contrato existente, atualiza o vinculo
                if ($contratoVinculadoId) {
                    $db = Connection::getInstance();
                    $db->prepare("UPDATE orcamento_proposta SET contrato_id = ? WHERE id = ?")->execute([$contratoVinculadoId, $id]);
                    error_log("DEBUG: Proposta #{$id} vinculada ao contrato existente #{$contratoVinculadoId}.");
                }
                echo json_encode(['success' => true, 'message' => 'Status atualizado com sucesso.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Erro ao atualizar status.']);
            }
            exit();
        }

        /**
         * Limpa todo o histórico de eventos de uma proposta via AJAX.
         */
        public function limparHistoricoAjax($id)
        {
            header('Content-Type: application/json');
            $id = (int)$id;

            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                echo json_encode(['success' => false, 'message' => 'Método inválido.']);
                exit();
            }

            if (!isset($_POST['csrf_token']) || !$this->validateCsrfToken($_POST['csrf_token'])) {
                echo json_encode(['success' => false, 'message' => 'Erro de validação de segurança (CSRF).']);
                exit();
            }

            $proposta = $this->propostaModel->getPropostaById($id);
            if (!$proposta) {
                echo json_encode(['success' => false, 'message' => 'Proposta não encontrada.']);
                exit();
            }

            $result = $this->propostaModel->limparHistorico($id);

            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Histórico de eventos limpo com sucesso.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Erro ao limpar histórico de eventos.']);
            }
            exit();
        }

        /**
         * Retorna os orçamentos de um projeto em JSON (para AJAX).
         */
        public function getOrcamentosAjax()
        {
            header('Content-Type: application/json');

            if (empty($_GET['projeto_id'])) {
                echo json_encode(['success' => false, 'orcamentos' => [], 'projeto_orcamento_id' => null]);
                exit();
            }

            $projeto_id = (int)$_GET['projeto_id'];
            $orcamentos = $this->projetosModel->getOrcamentosParaSelect($projeto_id);

            // Busca o projeto para obter o campo orcamento_id (código do orçamento do projeto)
            $projeto = $this->projetosModel->getProjetoById($projeto_id);
            $projetoOrcamentoId = $projeto['orcamento_id'] ?? null;

            echo json_encode([
                'success' => true,
                'orcamentos' => $orcamentos,
                'projeto_orcamento_id' => $projetoOrcamentoId
            ]);
            exit();
        }

        /**
         * Retorna os contratos de um cliente em JSON (para AJAX).
         */
        public function getContratosAjax($cliente_id = null)
        {
            header('Content-Type: application/json');

            // Garante que o ID seja um inteiro e trata strings vazias (comum em chamadas AJAX)
            $rawId = $cliente_id ?? $_GET['cliente_id'] ?? 0;
            $id = (int) (is_numeric($rawId) ? $rawId : 0);

            if ($id <= 0) {
                echo json_encode(['success' => false, 'contratos' => [], 'message' => 'ID de cliente inválido']);
                exit();
            }

            $contratos = $this->contratosModel->getContratosByClienteId($id);

            echo json_encode([
                'success' => true,
                'contratos' => $contratos
            ]);
            exit();
        }

        /**
         * Retorna os detalhes de um projeto em JSON (para AJAX).
         * @param int $id O ID do projeto.
         */
        public function getProjectDetailsAjax(int $id)
        {
            header('Content-Type: application/json');

            if (empty($id)) {
                echo json_encode(['success' => false, 'message' => 'ID do projeto não fornecido.']);
                exit();
            }

            $projeto = $this->projetosModel->getProjetoById($id);

            echo json_encode([
                'success' => $projeto ? true : false,
                'data' => $projeto
            ]);
            exit();
        }

        /**
         * Prepara e decodifica os dados de uma proposta para as views.
         * @param array $proposta
         * @return array
         */
        private function prepareOrcamentoData(array $proposta): array
        {
            // Mapeamento de compatibilidade
            $proposta['numero'] = $proposta['numero_proposta'] ?? ($proposta['id'] ?? '');
            $proposta['total']  = $proposta['total_final'] ?? $proposta['valor_total'] ?? 0;

            // Garante o ID do responsável para o select do organograma
            $proposta['responsavel_interno_id'] = $proposta['responsavel_interno_id'] ?? $proposta['responsavel_interno'] ?? null;

            // Mapeamento de campos de texto para compatibilidade entre formulários e visualização
            $proposta['titulo'] = html_entity_decode($proposta['nome_proposta'] ?? '', ENT_QUOTES, 'UTF-8');
            $proposta['descricao_geral'] = html_entity_decode($proposta['descricao'] ?? '', ENT_QUOTES, 'UTF-8');
            $proposta['observacoes'] = html_entity_decode($proposta['objetivo'] ?? '', ENT_QUOTES, 'UTF-8');

            // Normalização de nomes de campos para a view formulario.php
            // Mantemos o valor bruto do desconto para o rótulo e o calculado para o financeiro
            $proposta['desconto_valor'] = $proposta['descontos_valor'] ?? 0; 
            $proposta['validade_dias'] = $proposta['validade'] ?? 30;
            $proposta['prazo_execucao'] = html_entity_decode($proposta['prazo_execucao'] ?? '', ENT_QUOTES, 'UTF-8');
            $proposta['cliente_telefone'] = $proposta['cliente_telefone'] ?? '';
            $proposta['cliente_sigla'] = $proposta['cliente_sigla'] ?? '';
            $proposta['cliente_documento'] = $proposta['cliente_documento'] ?? '';
            $proposta['representante'] = html_entity_decode($proposta['representante'] ?? '', ENT_QUOTES, 'UTF-8');
            $proposta['email_cliente'] = $proposta['email_cliente'] ?? '';
            $proposta['municipio'] = html_entity_decode($proposta['municipio'] ?? '', ENT_QUOTES, 'UTF-8');
            $proposta['area'] = html_entity_decode($proposta['area'] ?? '', ENT_QUOTES, 'UTF-8');
            $proposta['cliente_logradouro'] = html_entity_decode($proposta['cliente_logradouro'] ?? '', ENT_QUOTES, 'UTF-8');
            $proposta['cliente_numero'] = html_entity_decode($proposta['cliente_numero'] ?? '', ENT_QUOTES, 'UTF-8');
            $proposta['cliente_complemento'] = html_entity_decode($proposta['cliente_complemento'] ?? '', ENT_QUOTES, 'UTF-8');
            $proposta['cliente_bairro'] = html_entity_decode($proposta['cliente_bairro'] ?? '', ENT_QUOTES, 'UTF-8');
            $proposta['cliente_municipio'] = html_entity_decode($proposta['cliente_municipio'] ?? '', ENT_QUOTES, 'UTF-8');
            $proposta['cliente_uf'] = $proposta['cliente_uf'] ?? '';
            // Fallback: busca UF do cliente se a proposta não tiver
            if (empty($proposta['cliente_uf']) && !empty($proposta['cliente_id'])) {
                try {
                    $stmt = $this->propostaModel->getDbConnection()->prepare(
                        "SELECT enderecos_json FROM clientes WHERE id = ?"
                    );
                    $stmt->execute([(int)$proposta['cliente_id']]);
                    $row = $stmt->fetch(\PDO::FETCH_ASSOC);
                    if ($row && !empty($row['enderecos_json'])) {
                        $enderecos = json_decode($row['enderecos_json'], true);
                        $proposta['cliente_uf'] = $enderecos['principal']['estado'] ?? '';
                    }
                } catch (\Exception $e) {
                    error_log('Erro ao buscar UF do cliente: ' . $e->getMessage());
                }
            }
            $proposta['cliente_endereco'] = html_entity_decode($proposta['cliente_endereco'] ?? '', ENT_QUOTES, 'UTF-8');

            if (empty($proposta['cliente_endereco'])) {
                $enderecoPecas = array_filter([
                    $proposta['cliente_logradouro'],
                    $proposta['cliente_numero'] ? 'n.º ' . $proposta['cliente_numero'] : '',
                    $proposta['cliente_complemento'],
                ]);
                $proposta['cliente_endereco'] = trim(implode(' ', $enderecoPecas));
                if (!empty($proposta['cliente_bairro'])) {
                    $proposta['cliente_endereco'] .= (!empty($proposta['cliente_endereco']) ? ', ' : '') . $proposta['cliente_bairro'];
                }
                if (!empty($proposta['cliente_municipio'])) {
                    $proposta['cliente_endereco'] .= (!empty($proposta['cliente_endereco']) ? ', ' : '') . $proposta['cliente_municipio'];
                }
                if (!empty($proposta['cliente_uf'])) {
                    $proposta['cliente_endereco'] .= (!empty($proposta['cliente_endereco']) ? ' - ' : '') . $proposta['cliente_uf'];
                }
            }

            $proposta['condicao_pagamento'] = html_entity_decode($proposta['condicao_pagamento'] ?? '', ENT_QUOTES, 'UTF-8');
            $proposta['forma_pagamento'] = html_entity_decode($proposta['forma_pagamento'] ?? '', ENT_QUOTES, 'UTF-8');
            $proposta['garantias'] = html_entity_decode($proposta['garantias'] ?? '', ENT_QUOTES, 'UTF-8');
            
            $proposta['pix_tipo_chave'] = $proposta['pix_tipo_chave'] ?? '';
            $proposta['pix_chave'] = $proposta['pix_chave'] ?? '';
            $proposta['dados_bancarios'] = $proposta['dados_bancarios'] ?? '';

            // Cálculos auxiliares para o PDF
            $proposta['subtotal'] = (float)($proposta['total_servicos'] ?? 0) + (float)($proposta['total_materiais'] ?? 0);
            $proposta['impostos_perc'] = ($proposta['subtotal'] > 0) ? round(($proposta['impostos_valor'] / $proposta['subtotal']) * 100, 2) : 0;

            // Decodificação de itens
            $servicos = isset($proposta['servicos_json']) ? json_decode($proposta['servicos_json'], true) : [];
            $materiais = isset($proposta['materiais_json']) ? json_decode($proposta['materiais_json'], true) : [];
            $extras = isset($proposta['custos_extras_json']) ? json_decode($proposta['custos_extras_json'], true) : [];

            // Unifica as listas para o formulário (o front-end trabalha com lista única de itens)
            $listaUnificada = array_merge(is_array($servicos) ? $servicos : [], is_array($materiais) ? $materiais : [], is_array($extras) ? $extras : []);
            
            // Mapeia para o formato de 'itens' que o formulario.php espera
            $itens = [];
            foreach (($listaUnificada ?: []) as $s) {
                $itens[] = [
                    'categoria' => $s['categoria'] ?? 'Outros',
                    'descricao' => $s['nome'] ?? $s['descricao'] ?? '',
                    'detalhes' => $s['descricao'] ?? $s['detalhes'] ?? '',
                    'unidade' => $s['unidade'] ?? 'un',
                    'quantidade' => $s['quantidade'] ?? 0,
                    'valor_unit' => $s['valor_unitario'] ?? $s['valor_unit'] ?? 0,
                    'desconto_item' => $s['desconto'] ?? 0,
                    'total_item' => $s['subtotal'] ?? 0
                ];
            }
            $proposta['itens'] = $itens;

            $proposta['custos_extras'] = is_array($extras) ? $extras : [];

            // Extrai textos do cronograma
            $cronoData = !empty($proposta['cronograma_data'])
                ? (is_array($proposta['cronograma_data'])
                    ? $proposta['cronograma_data']
                    : json_decode($proposta['cronograma_data'], true))
                : [];
            $proposta['cronograma_texto_intro'] = $cronoData['texto_intro'] ?? '';
            $proposta['cronograma_texto_footer'] = $cronoData['texto_footer'] ?? '';

            // Decodifica contextualizacao e equipe
            $ctxRaw = !empty($proposta['contextualizacao_json'])
                ? (is_array($proposta['contextualizacao_json'])
                    ? $proposta['contextualizacao_json']
                    : json_decode($proposta['contextualizacao_json'], true))
                : [];
            if (is_array($ctxRaw) && array_key_exists('linhas', $ctxRaw)) {
                $proposta['contextualizacao_ocultar_vazias'] = $ctxRaw['ocultar_vazias'] ?? true;
                $proposta['contextualizacao'] = $ctxRaw['linhas'] ?? [];
                $proposta['contextualizacao_texto_intro'] = $ctxRaw['texto_intro'] ?? '';
            } else {
                $proposta['contextualizacao_ocultar_vazias'] = true;
                $proposta['contextualizacao'] = $ctxRaw;
                $proposta['contextualizacao_texto_intro'] = '';
            }
            $eqRaw = !empty($proposta['equipe_json'])
                ? (is_array($proposta['equipe_json'])
                    ? $proposta['equipe_json']
                    : json_decode($proposta['equipe_json'], true))
                : [];
            if (is_array($eqRaw) && array_key_exists('membros', $eqRaw)) {
                $proposta['equipe'] = $eqRaw['membros'] ?? [];
                $proposta['equipe_texto_intro'] = $eqRaw['texto_intro'] ?? '';
            } else {
                $proposta['equipe'] = $eqRaw;
                $proposta['equipe_texto_intro'] = '';
            }

            return $proposta;
        }

        /**
         * Processamento básico de dados para lista (otimizado para performance)
         */
        private function prepareOrcamentoDataBasic(array $proposta): array
        {
            // Mapeamento mínimo necessário para a lista
            $proposta['numero'] = $proposta['numero_proposta'] ?? ($proposta['id'] ?? '');
            $proposta['total']  = $proposta['total_final'] ?? 0;
            $proposta['titulo'] = $proposta['nome_proposta'] ?? '';

            return $proposta;
        }

        /**
         * Converte uma string de valor monetário (formatada ou não) em float.
         */
        private function parseDecimal($valor): float
        {
            if (empty($valor)) return 0.0;
            if (is_numeric($valor)) return (float)$valor;

            $str = trim((string)$valor);
            $str = preg_replace('/[^\d\-,.]/', '', $str);
            
            if (strpos($str, '.') !== false && strpos($str, ',') !== false) {
                // Formato pt-BR: 1.234,56 -> 1234.56
                $str = str_replace('.', '', $str);
                $str = str_replace(',', '.', $str);
            } elseif (strpos($str, ',') !== false) {
                // 1234,56 -> 1234.56
                $str = str_replace(',', '.', $str);
            } else {
                if (substr_count($str, '.') > 1) {
                    $parts = explode('.', $str);
                    $decimal = array_pop($parts);
                    $str = implode('', $parts) . '.' . $decimal;
                }
            }
            return (float) $str;
        }

        /**
         * AJAX: Faz upload do certificado A1 (.pfx) e retorna o caminho + dados extraídos.
         */
        public function uploadCertificado()
        {
            header('Content-Type: application/json');

            if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_FILES['certificado_file']) || empty($_POST['certificado_senha'])) {
                echo json_encode(['success' => false, 'error' => 'Arquivo e senha são obrigatórios.']);
                exit();
            }

            $file = $_FILES['certificado_file'];
            $password = $_POST['certificado_senha'];

            if ($file['error'] !== UPLOAD_ERR_OK) {
                echo json_encode(['success' => false, 'error' => 'Erro no upload do arquivo.']);
                exit();
            }

            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, ['pfx', 'p12'])) {
                echo json_encode(['success' => false, 'error' => 'Formato inválido. Use .pfx ou .p12.']);
                exit();
            }

            $pfxContent = file_get_contents($file['tmp_name']);
            $result = \App\Helpers\CertificadoDigitalHelper::lerCertificado($pfxContent, $password);

            if (!$result['success']) {
                echo json_encode(['success' => false, 'error' => $result['error']]);
                exit();
            }

            // Salva o arquivo em disco
            $uploadDir = \App\Helpers\CertificadoDigitalHelper::getUploadDir();
            $filename = 'cert_' . bin2hex(random_bytes(8)) . '.' . $ext;
            $destPath = $uploadDir . '/' . $filename;

            if (!move_uploaded_file($file['tmp_name'], $destPath)) {
                echo json_encode(['success' => false, 'error' => 'Falha ao salvar o arquivo.']);
                exit();
            }

            $data = $result['data'];
            echo json_encode([
                'success' => true,
                'data' => [
                    'path' => $destPath,
                    'filename' => $filename,
                    'nome' => $data['nome'],
                    'documento' => $data['documento'],
                    'cpf' => $data['cpf'],
                    'cnpj' => $data['cnpj'],
                    'empresa' => $data['empresa'],
                    'validade_de' => $data['validade_de'],
                    'validade_ate' => $data['validade_ate'],
                    'expirado' => $data['expirado'],
                    'icp_brasil' => $data['icp_brasil'],
                    'emissor' => $data['emissor'],
                ],
            ]);
            exit();
        }

        /**
         * Criptografa um valor (senha do certificado) usando AES-256.
         */
        private static function encrypt(string $value): string
        {
            $key = defined('APP_ENCRYPT_KEY') ? APP_ENCRYPT_KEY : 'sysenvicorp_default_key_change_me_32b!';
            $key = str_pad(substr($key, 0, 32), 32, "\0");
            $iv = openssl_random_pseudo_bytes(16);
            $encrypted = openssl_encrypt($value, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
            return base64_encode($iv . $encrypted);
        }

        /**
         * Descriptografa um valor.
         */
        public static function decrypt(string $encrypted): string
        {
            $key = defined('APP_ENCRYPT_KEY') ? APP_ENCRYPT_KEY : 'sysenvicorp_default_key_change_me_32b!';
            $key = str_pad(substr($key, 0, 32), 32, "\0");
            $data = base64_decode($encrypted);
            $iv = substr($data, 0, 16);
            $encrypted = substr($data, 16);
            return openssl_decrypt($encrypted, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv) ?: '';
        }

        /**
         * Se a proposta tiver certificado A1 configurado, assina o PDF.
         * Suporta certificado da contratada e/ou do elaborador.
         */
        private function signPdfIfConfigured(string $pdfContent, array $proposta): string
        {
            $certsToSign = [];

            // Certificado da contratada
            if (!empty($proposta['assinatura_certificado_path']) && file_exists($proposta['assinatura_certificado_path']) && !empty($proposta['assinatura_certificado_senha'])) {
                $certsToSign[] = [
                    'path' => $proposta['assinatura_certificado_path'],
                    'senha' => $proposta['assinatura_certificado_senha'],
                    'nome' => $proposta['assinatura_certificado_nome'] ?? 'Contratada',
                    'tipo' => 'Contratada',
                ];
            }

            // Certificado do elaborador
            if (!empty($proposta['assinatura_elaborador_certificado_path']) && file_exists($proposta['assinatura_elaborador_certificado_path']) && !empty($proposta['assinatura_elaborador_certificado_senha'])) {
                $certsToSign[] = [
                    'path' => $proposta['assinatura_elaborador_certificado_path'],
                    'senha' => $proposta['assinatura_elaborador_certificado_senha'],
                    'nome' => $proposta['assinatura_elaborador_certificado_nome'] ?? 'Responsável Técnico',
                    'tipo' => 'Responsável Técnico',
                ];
            }

            if (empty($certsToSign)) {
                return $pdfContent;
            }

            try {
                $local = $proposta['cliente_municipio'] ?? 'Brasil';
                $currentPdf = $pdfContent;

                foreach ($certsToSign as $cert) {
                    $pfxContent = file_get_contents($cert['path']);
                    $password = self::decrypt($cert['senha']);

                    $currentPdf = \App\Helpers\CertificadoDigitalHelper::assinarPdf(
                        $currentPdf,
                        $pfxContent,
                        $password,
                        [
                            'name' => $cert['nome'],
                            'reason' => 'Assinatura Digital - ' . $cert['tipo'],
                            'location' => $local,
                        ]
                    );
                }

                return $currentPdf;
            } catch (\Exception $e) {
                error_log('Erro ao assinar PDF com certificado: ' . $e->getMessage());
                return $pdfContent;
            }
        }

        /**
         * AJAX: Lê um certificado A1 (.pfx) e retorna os dados extraídos.
         */
        public function lerCertificado()
        {
            header('Content-Type: application/json');

            if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_FILES['certificado_file']) || empty($_POST['certificado_senha'])) {
                echo json_encode(['success' => false, 'error' => 'Arquivo e senha são obrigatórios.']);
                exit();
            }

            $file = $_FILES['certificado_file'];
            $password = $_POST['certificado_senha'];

            if ($file['error'] !== UPLOAD_ERR_OK) {
                echo json_encode(['success' => false, 'error' => 'Erro no upload do arquivo.']);
                exit();
            }

            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, ['pfx', 'p12'])) {
                echo json_encode(['success' => false, 'error' => 'Formato inválido. Use arquivos .pfx ou .p12.']);
                exit();
            }

            $pfxContent = file_get_contents($file['tmp_name']);
            $result = \App\Helpers\CertificadoDigitalHelper::lerCertificado($pfxContent, $password);

            if ($result['success']) {
                $data = $result['data'];
                echo json_encode([
                    'success' => true,
                    'data' => [
                        'nome' => $data['nome'],
                        'documento' => $data['documento'],
                        'cpf' => $data['cpf'],
                        'cnpj' => $data['cnpj'],
                        'empresa' => $data['empresa'],
                        'validade_de' => $data['validade_de'],
                        'validade_ate' => $data['validade_ate'],
                        'expirado' => $data['expirado'],
                        'icp_brasil' => $data['icp_brasil'],
                        'emissor' => $data['emissor'],
                    ],
                ]);
            } else {
                echo json_encode(['success' => false, 'error' => $result['error']]);
            }
            exit();
        }
    }