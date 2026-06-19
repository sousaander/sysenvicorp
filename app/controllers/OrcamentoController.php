<?php

namespace App\Controllers;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Dompdf\Dompdf;
use Dompdf\Options;
use App\Models\PropostaModel;
use App\Models\ProjetosModel;
use App\Models\ClientesModel;
use App\Models\ContratosModel;
use App\Models\UsuarioModel;
use App\Models\EmpresaModel;
use App\Models\PerfilModel;
use App\Models\NotificacoesModel;

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
        'comercial'             => 'orcamento_view',
        'updateStatusAjax'      => 'comercial_propostas_view',
        'getOrcamentosAjax'     => 'comercial_propostas_view',
        'getContratosAjax'      => 'comercial_propostas_view',
        'getProjectDetailsAjax' => 'comercial_propostas_view',
        'getProximoNumeroAjax'  => 'comercial_propostas_view',
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
            $action = $this->getCurrentActionName();
            if ($action !== 'aprovarPropostaPublica' && !$this->session->isAuthenticated()) {
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

            $data = [
                'pageTitle' => 'Propostas Comerciais',
                'propostas' => $propostas, // 'proposta.php' utiliza $propostas
                'statusLabels' => $statusLabels,
                'paginaAtual' => $paginaAtual,
                'totalPaginas' => $totalPaginas,
                'csrf_token' => $this->generateCsrfToken(), // Token CSRF necessário
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

            $data = [
                'pageTitle' => 'Propostas',
                'orcamentos' => $propostas, // 'lista.php' utiliza $orcamentos
                'statusLabels' => $statusLabels, // 'lista.php' utiliza $statusLabels
                'paginaAtual' => $paginaAtual,
                'totalPaginas' => $totalPaginas,
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
            
            // Mock de condições de pagamento para o formulário (ajuste conforme seu banco)
            $condicoes = [
                ['id' => 1, 'descricao' => '50% entrada e 50% na entrega'],
                ['id' => 2, 'descricao' => '100% após a conclusão'],
                ['id' => 3, 'descricao' => 'Parcelado em 3x'],
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
            ];

            // Se a requisição for via AJAX (da modal), renderiza só o formulário.
            $view = 'orcamento/formulario';
            isset($_GET['ajax']) && $_GET['ajax'] == 1 ? $this->renderPartial($view, $data) : $this->renderView($view, $data);
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
            $pageTitle = 'Editar Proposta';
            // Decodifica os JSONs para o formulário
            $proposta = $this->prepareOrcamentoData($proposta);

            // Busca a lista de projetos para o dropdown
            $projetos = $this->projetosModel->getAllProjetosParaSelect();
            $clientes = $this->clientesModel->getAllClientes(); // Para a seção "Criar do Zero"
            $contratos = $this->contratosModel->getContratos([], 999, 0);
            // Busca todos os usuários ativos para seleção (integração com o organograma da empresa)
            $usuarios = $this->usuarioModel->getListaUsuarios('Ativo');
            
            // Mock de condições de pagamento para o formulário (ajuste conforme seu banco)
            $condicoes = [
                ['id' => 1, 'descricao' => '50% entrada e 50% na entrega'],
                ['id' => 2, 'descricao' => '100% após a conclusão'],
                ['id' => 3, 'descricao' => 'Parcelado em 3x'],
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
                ['id' => 1, 'descricao' => '50% entrada e 50% na entrega'],
                ['id' => 2, 'descricao' => '100% após a conclusão'],
                ['id' => 3, 'descricao' => 'Parcelado em 3x'],
            ];

            $data = [
                'pageTitle' => 'Clonar Proposta',
                'orc' => $propostaClonada, // 'formulario.php' utiliza $orc
                'projetos' => $projetos,
                'clientes' => $clientes,
                'contratos' => $contratos,
                'usuarios' => $usuarios,
                'condicoes' => $condicoes,
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
                'representante' => trim($_POST['representante'] ?? ''),
                'email_cliente' => trim($_POST['email_cliente'] ?? ''),
                'municipio' => trim($_POST['municipio'] ?? ''),
                'area' => trim($_POST['area'] ?? ''),
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
                'motivo_alteracao' => trim($_POST['motivo_alteracao'] ?? '') ?: 'Alteração via formulário',

                // Campos de cálculo (já tratados como string no POST, precisam de conversão)
                'total_servicos' => $this->parseDecimal($_POST['subtotal'] ?? $_POST['total_servicos'] ?? '0'),
                'total_materiais' => $this->parseDecimal($_POST['total_materiais'] ?? '0'),
                'impostos_valor' => $this->parseDecimal($_POST['impostos_valor'] ?? '0'),
                'descontos_valor' => $this->parseDecimal($_POST['descontos_valor'] ?? '0'),
                'valor_total' => $this->parseDecimal($_POST['valor_total'] ?? '0'),

                // Itens dinâmicos (JSON)
                'servicos' => $itens_processados,
                'materiais' => [], // Unificado em serviços para este formulário
                'custos_extras' => [],
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
            }

            // Se a proposta for marcada como 'Enviada', gera um token de aprovação
            if ($dados['status'] === 'Enviada' && (empty($propostaAtual) || empty($propostaAtual['token_aprovacao']))) { // Apenas para novas propostas enviadas ou se o token não existe
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

            $data = [
                'pageTitle' => 'Visualizar Proposta',
                'orc' => $proposta, // 'ver.php' utiliza $orc
                'historico' => $historico,
                'statusLabels' => $statusLabels
            ];
            $this->renderView('orcamento/ver', $data);
        }

        /** Gera PDF de uma proposta usando DOMPDF */
        public function pdf($id)
        {
            $id = (int)$id;
            $proposta = $this->propostaModel->getPropostaById($id);
            if (!$proposta) {
                $this->setFlashMessage('error', 'Proposta não encontrada.');
                header('Location: ' . BASE_URL . '/orcamento/index');
                exit();
            }

            // Aumenta recursos para processamento do PDF
            ini_set('memory_limit', '512M');
            set_time_limit(300);

            $proposta = $this->prepareOrcamentoData($proposta);
            $empresa = $this->empresaModel->getDadosEmpresa();

            // Gera o HTML a partir da view de PDF
            ob_start();
            $data = ['proposta_pdf' => $proposta, 'empresa' => $empresa]; // Passa os dados para a view
            $this->renderPartial('orcamento/proposta_pdf', $data);
            $html = ob_get_clean();

            // Configurações do Dompdf
            $options = new Options();
            $options->set('isRemoteEnabled', true);
            $options->set('defaultFont', 'Helvetica');
            
            $dompdf = new Dompdf($options);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            // Envia para o navegador
            $dompdf->stream('proposta_' . $id . '.pdf', ['Attachment' => false]);
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

            // Aumenta recursos para processamento do PDF no anexo
            ini_set('memory_limit', '512M');
            set_time_limit(300);

            // 1. Gerar o HTML do PDF
            ob_start();
            $data = ['proposta_pdf' => $proposta, 'empresa' => $empresa];
            $this->renderPartial('orcamento/proposta_pdf', $data);
            $html = ob_get_clean();

            // 2. Gerar o PDF em memória
            $options = new Options();
            $options->set('isRemoteEnabled', true);
            $options->set('isHtml5ParserEnabled', true);
            $options->set('defaultFont', 'Helvetica');

            $dompdf = new Dompdf($options);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            
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
                $mail->setFrom(MAIL_FROM_ADDRESS, MAIL_FROM_NAME);
                $mail->addAddress($_POST['email_destinatario']);
                $mail->addReplyTo(MAIL_FROM_ADDRESS, MAIL_FROM_NAME);

                // Anexo
                $nomeArquivo = 'proposta_' . str_pad($id, 4, '0', STR_PAD_LEFT) . '.pdf';
                $mail->addStringAttachment($pdfOutput, $nomeArquivo);

                // Conteúdo do E-mail
                $mail->isHTML(false); // E-mail como texto plano
                $mail->Subject = $_POST['email_assunto'];
                $mail->Body    = $_POST['email_corpo'];

                $mail->send();
                $this->setFlashMessage('success', 'E-mail enviado com sucesso!');
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

            // Se não tiver token, gera um novo e salva no banco
            if (empty($proposta['token_aprovacao'])) {
                $token = $this->propostaModel->generateApprovalToken();
                $validade = date('Y-m-d H:i:s', strtotime('+7 days'));
                
                // Atualiza diretamente no banco usando a conexão do modelo
                $stmt = $this->propostaModel->getDbConnection()->prepare("UPDATE orcamento_proposta SET token_aprovacao = ?, token_validade = ? WHERE id = ?");
                if ($stmt->execute([$token, $validade, $id])) {
                    $proposta['token_aprovacao'] = $token;
                } else {
                    echo json_encode(['success' => false, 'message' => 'Erro ao registrar token de segurança.']);
                    exit;
                }
            }

            // Constrói a URL pública baseada na rota existente no controlador
            $link = BASE_URL . "/orcamento/aprovarPropostaPublica/" . $proposta['token_aprovacao'];
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
                $this->renderPartial('orcamento/aprovacao_publica', ['pageTitle' => 'Erro de Aprovação', 'message' => 'Token inválido.']);
                exit();
            }

            // Normaliza os dados para a visualização (mapeia colunas do banco para nomes amigáveis)
            $proposta = $this->prepareOrcamentoData($proposta);

            // Verifica a validade do token
            $tokenValidade = new \DateTime($proposta['token_validade']);
            $agora = new \DateTime();

            if ($agora > $tokenValidade) {
                $this->setFlashMessage('error', 'O link de aprovação expirou. Por favor, solicite um novo.');
                $this->renderPartial('orcamento/aprovacao_publica', ['pageTitle' => 'Erro de Aprovação', 'message' => 'Link expirado.']);
                exit();
            }

            // Verifica se a proposta já foi aprovada/rejeitada — exibe a página normalmente
            if ($proposta['status'] === 'Aprovada' || $proposta['status'] === 'Rejeitada') {
                $this->renderPartial('orcamento/aprovacao_publica', [
                    'pageTitle' => 'Proposta #' . ($proposta['numero_proposta'] ?? $proposta['id']),
                    'proposta'  => $proposta,
                    'token'     => $token,
                ]);
                exit();
            }

            // Se for POST, processa a aprovação/rejeição
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $acao = filter_input(INPUT_POST, 'acao', FILTER_SANITIZE_SPECIAL_CHARS);
                $motivo = filter_input(INPUT_POST, 'motivo', FILTER_SANITIZE_SPECIAL_CHARS);

                if ($acao === 'aprovar') {
                    $aceiteNome = trim(filter_input(INPUT_POST, 'aceite_nome', FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
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
                    // Para rejeitar, precisamos de um método no model que atualize o status e invalide o token
                    if ($this->propostaModel->updateProposalStatus($proposta['id'], 'Rejeitada', 'Rejeitada pelo cliente via link: ' . $motivo)) {
                        $this->setFlashMessage('info', 'Proposta rejeitada. Agradecemos seu feedback.');
                        $this->renderPartial('orcamento/aprovacao_publica', ['pageTitle' => 'Proposta Rejeitada', 'message' => 'Proposta rejeitada.']);
                        exit();
                    } else {
                        $this->setFlashMessage('error', 'Erro ao rejeitar a proposta. Tente novamente.');
                    }
                }
            }

            // Exibe a página de aprovação/rejeição
            $data = [
                'pageTitle' => 'Aprovar Proposta',
                'proposta' => $proposta,
                'token' => $token,
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

            // Define se cria o contrato: true se não houver duplicidade ou se o usuário confirmou 'sim'
            $criarContrato = ($confirmacaoDuplicado !== 'nao');

            $result = $this->propostaModel->updateProposalStatus($id, $status, $motivo, $usuario_id, $criarContrato);
            error_log("DEBUG: Resultado do updateProposalStatus: " . ($result ? 'SUCESSO' : 'FALHA'));

            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Status atualizado com sucesso.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Erro ao atualizar status.']);
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
         * Exibe a página de Orçamento-Comercial.
         */
        public function comercial()
        {
            $data = ['pageTitle' => 'Orçamento - Comercial'];
            $this->renderView('orcamento/comercial', $data);
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
            $proposta['representante'] = html_entity_decode($proposta['representante'] ?? '', ENT_QUOTES, 'UTF-8');
            $proposta['email_cliente'] = $proposta['email_cliente'] ?? '';
            $proposta['municipio'] = html_entity_decode($proposta['municipio'] ?? '', ENT_QUOTES, 'UTF-8');
            $proposta['area'] = html_entity_decode($proposta['area'] ?? '', ENT_QUOTES, 'UTF-8');
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
    }
