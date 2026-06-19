<?php

namespace App\Controllers;

use App\Core\Connection;
use App\Models\ClientesModel;
use App\Models\ProjetosModel;
use App\Models\ContratosModel;
use App\Models\FinancialModel;

class ClientesController extends BaseController
{
    private $model;
    private $projetosModel;
    private $contratosModel;
    private $financialModel;

    /**
     * Mapeia ações para as permissões necessárias.
     * O BaseController usará este mapa para verificar o acesso.
     * @var array
     */
    protected $requiredPermissions = [
        'index' => 'clientes_view',
        'detalhe' => 'clientes_view',
        'salvar' => 'clientes_manage',
        'novo' => 'clientes_manage',
        'excluir' => 'clientes_delete',
        'restaurar' => 'clientes_manage',
        'limparHistorico' => 'clientes_interacoes_manage',
        'registrarInteracao' => 'clientes_interacoes_manage',
        'getFormForEdit' => 'clientes_edit',
        'getFormForNew' => 'clientes_create',
        'addCategoria' => 'config_clientes_manage',
        'getSegmentosAjax' => 'clientes_view',
        'addSegmentoAjax' => 'config_clientes_manage',
        'consultarCnpj' => 'clientes_view',
        'buscarCnpjAjax' => 'clientes_view',
    ];

    public function __construct()
    {
        parent::__construct();
        $this->model = new ClientesModel();
        $this->projetosModel = new ProjetosModel();
        $this->contratosModel = new ContratosModel();
        $this->financialModel = new FinancialModel();
    }

    public function index()
    {
        // Coleta dados do modelo
        $summary = $this->model->getClientesSummary();
        $funilVendas = $this->model->getFunilVendasSummary();
        $todosClientes = $this->model->getAllClientes(); // Para o modal de interação
        $categorias = $this->model->getCategorias(); // Busca as categorias

        // Coleta filtros da URL
        $filtros = [
            'busca' => filter_input(INPUT_GET, 'busca', FILTER_SANITIZE_SPECIAL_CHARS),
            'status' => filter_input(INPUT_GET, 'status', FILTER_SANITIZE_SPECIAL_CHARS),
        ];

        // Lógica de Paginação
        $paginaAtual = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1;
        $itensPorPagina = 5; // Define quantos clientes por página
        $offset = ($paginaAtual - 1) * $itensPorPagina;

        // Busca os clientes da página atual
        $clientes = $this->model->getClientes($filtros, $itensPorPagina, $offset);
        // Conta o total de clientes para calcular o total de páginas
        $totalClientes = $this->model->getClientesCount($filtros);
        $totalPaginas = ceil($totalClientes / $itensPorPagina);

        // Gera um token CSRF para os formulários da página
        $csrf_token = bin2hex(random_bytes(32));
        $this->session->set('csrf_token', $csrf_token);

        // Adiciona dados necessários para o formulário de criação no modal
        // (mesmo que inicialmente vazio)
        $data = array_merge([
            'pageTitle' => 'Gestão de Clientes',
            'clientes' => $clientes,
            'todosClientes' => $todosClientes,
            'paginaAtual' => $paginaAtual,
            'totalPaginas' => $totalPaginas,
            'filtros' => $filtros,
            'cliente' => null, // Garante que a variável exista para o form.php
            'categorias' => $categorias, // Passa as categorias para a view
            'csrf_token' => $csrf_token,
        ], $summary, $funilVendas);

        $this->renderView('clientes/index', $data);
    }

    /**
     * Exibe os detalhes de um cliente para edição.
     * @param int $id O ID do cliente.
     */
    public function detalhe($id)
    {
        $id = (int) $id;
        $cliente = $this->model->getClienteById($id);

        if (!$cliente) {
            $this->setFlashMessage('error', 'Cliente não encontrado.');
            header('Location: ' . BASE_URL . '/clientes');
            exit();
        }

        $interacoes = $this->model->getInteracoesByClienteId($id);
        $todosClientes = $this->model->getAllClientes(); // Para o modal de interação
        
        // Busca dados relacionados de outros módulos
        $projetos = $this->projetosModel->getProjetosByClienteId($id);
        $contratos = $this->contratosModel->getContratosByClienteId($id, $cliente['cnpj_cpf'] ?? null);
        
        // Busca histórico financeiro (Receitas)
        $paginaAtualFin = filter_input(INPUT_GET, 'page_fin', FILTER_VALIDATE_INT) ?: 1;
        $itensPorPagina = 10;
        $offset = ($paginaAtualFin - 1) * $itensPorPagina;
        $historicoFinanceiro = $this->financialModel->getTransacoesPorPessoaId($id, 'R', $itensPorPagina, $offset) ?: [];
        $totalFin = $this->financialModel->getCountTransacoesPorPessoaId($id, 'R') ?: 0;
        
        // Verifica se o cliente merece o selo de "Bom Pagador"
        $pontualidade = $this->financialModel->getStatusPontualidadeCliente($id);

        // Gera um token CSRF para os formulários da página
        $csrf_token = bin2hex(random_bytes(32));
        $this->session->set('csrf_token', $csrf_token);

        $data = [
            'pageTitle' => 'Detalhes do Cliente',
            'cliente' => $cliente,
            'interacoes' => $interacoes,
            'todosClientes' => $todosClientes, // Passa para o modal
            'projetos' => $projetos,
            'contratos' => $contratos,
            'historicoFinanceiro' => $historicoFinanceiro,
            'isBomPagador' => $pontualidade['is_bom_pagador'],
            'paginaAtualFin' => $paginaAtualFin,
            'totalPaginasFin' => ceil($totalFin / $itensPorPagina),
            'isDetalhePage' => true, // Flag para a view de detalhes
            'csrf_token' => $csrf_token,
        ];
        $this->renderView('clientes/detalhe', $data);
    }

    /**
     * Exibe o formulário para adicionar um novo cliente/lead.
     */
    public function novo()
    {
        $csrf_token = $this->generateCsrfToken();
        $categorias = $this->model->getCategorias();

        $data = [
            'pageTitle' => 'Novo Cliente / Lead',
            'cliente' => null,
            'categorias' => $categorias,
            'segmentos' => [],
            'csrf_token' => $csrf_token,
            'isEdit' => false
        ];

        $this->renderView('clientes/form', $data);
    }

    /**
     * Busca os dados de um cliente e retorna o HTML do formulário para edição via AJAX.
     * @param int $id O ID do cliente.
     */
    public function getFormForEdit(int $id)
    {
        $id = (int)$id;
        $cliente = $this->model->getClienteById($id);

        if (!$cliente) {
            $this->setFlashMessage('error', 'Cliente não encontrado.');
            header('Location: ' . BASE_URL . '/clientes');
            exit();
        }

        $csrf_token = $this->generateCsrfToken();
        $categorias = $this->model->getCategorias();
        $segmentos = $cliente['categoria_id'] ? $this->model->getSegmentosByCategoriaId($cliente['categoria_id']) : [];

        $data = [
            'pageTitle' => 'Editar Cliente',
            'cliente' => $cliente,
            'categorias' => $categorias,
            'segmentos' => $segmentos,
            'csrf_token' => $csrf_token,
            'isEdit' => true
        ];

        $this->renderView('clientes/form', $data);
    }

    /**
     * Retorna o HTML do formulário para um novo cliente via AJAX.
     */
    public function getFormForNew()
    {
        $csrf_token = $this->generateCsrfToken();

        // Busca listas para os selects do formulário
        $categorias = $this->model->getCategorias();

        $data = [
            'cliente' => null, // Garante que o formulário esteja no modo de criação
            'categorias' => $categorias,
            'segmentos' => [], // Começa com segmentos vazios
            'csrf_token' => $csrf_token,
        ];

        $this->renderPartial('clientes/form', $data);
    }

    /**
     * Salva um novo cliente ou atualiza um existente.
     */
    public function salvar()
    {
        // Verifica se é uma requisição AJAX
        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
            || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/clientes');
            exit();
        }

        // Validação de CSRF usando o pool de tokens do BaseController
        $postToken = $_POST['csrf_token'] ?? '';
        if (!$this->validateCsrfToken($postToken)) {
            if ($isAjax) {
                header('Content-Type: application/json');
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Token CSRF inválido ou expirado. Por favor, recarregue a página.']);
                exit();
            }
            $this->setFlashMessage('error', 'Token CSRF inválido.');
            header('Location: ' . BASE_URL . '/clientes');
            exit();
        }

        // Processa o endereço principal para a coluna flat 'endereco' (usada em buscas)
        $end = $_POST['enderecos']['principal'] ?? [];
        $enderecoFlat = null;
        if (!empty($end['logradouro'])) {
            $enderecoFlat = $end['logradouro'];
            if (!empty($end['numero'])) $enderecoFlat .= ", " . $end['numero'];
            if (!empty($end['cidade'])) $enderecoFlat .= " - " . $end['cidade'];
            if (!empty($end['estado'])) $enderecoFlat .= "/" . $end['estado'];
        }

        // Coleta e organiza os dados do formulário
        $dados = [
            'id' => filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: null,
            'tipo_cliente' => $_POST['tipo_cliente'] ?? null,
            'nome' => $_POST['nome'] ?? null,
            'email' => $_POST['contatos']['principal']['email'] ?? null,
            'telefone' => $_POST['contatos']['principal']['telefone'] ?? null,
            'contato_principal' => $_POST['contatos']['responsavel']['nome'] ?? null,
            'endereco' => $enderecoFlat,
            'nome_fantasia' => $_POST['nome_fantasia'] ?? null,
            'sigla' => !empty($_POST['sigla']) ? strtoupper(trim($_POST['sigla'])) : null,
            'cnpj_cpf' => $_POST['cnpj_cpf'] ?? null,
            'rg' => $_POST['rg'] ?? null,
            'inscricao_estadual' => $_POST['inscricao_estadual'] ?? null,
            'ie_isento' => isset($_POST['ie_isento']) ? 1 : 0,
            'inscricao_municipal' => $_POST['inscricao_municipal'] ?? null,
            'data_nascimento' => !empty($_POST['data_nascimento']) ? $_POST['data_nascimento'] : null,
            'categoria_id' => !empty($_POST['categoria_id']) ? (int)$_POST['categoria_id'] : null,
            'segmento' => $_POST['segmento'] ?? null,
            'classificacao' => $_POST['classificacao'] ?? null,
            'origem_cliente' => $_POST['origem_cliente'] ?? null,
            'observacoes_iniciais' => $_POST['observacoes_iniciais'] ?? null,
            'motivo_inativacao' => $_POST['motivo_inativacao'] ?? null,
            'data_inativacao' => !empty($_POST['data_inativacao']) ? $_POST['data_inativacao'] : null,
            'status' => $_POST['status'] ?? null,
            // Agrupa os dados em arrays para os campos JSON
            'enderecos' => $_POST['enderecos'] ?? [],
            'contatos' => $_POST['contatos'] ?? [],
            'financeiro' => $_POST['financeiro'] ?? [],
            'comercial' => $_POST['comercial'] ?? [],
            // A parte de upload de arquivos precisaria de uma lógica separada com $_FILES
        ];

        // Validação de Campos Obrigatórios
        if (empty(trim($dados['nome'] ?? ''))) {
            $msg = 'O campo Nome é obrigatório.';
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $msg]);
                exit();
            }
            $this->setFlashMessage('error', $msg);
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/clientes'));
            exit();
        }

        // Adiciona a validação de CPF/CNPJ
        $tipoClienteValidacao = $dados['tipo_cliente'] ?? '';
        if (!empty($dados['cnpj_cpf']) && !$this->validarCpfCnpj((string)($dados['cnpj_cpf'] ?? ''), (string)$tipoClienteValidacao)) {
            // Determina o tipo de documento para a mensagem de erro
            $tipoDocumento = ($tipoClienteValidacao === 'Fisica' || strlen(preg_replace('/[^0-9]/', '', $dados['cnpj_cpf'])) === 11) ? 'CPF' : 'CNPJ';
            $message = "O {$tipoDocumento} informado é inválido.";

            if ($isAjax) {
                header('Content-Type: application/json');
                http_response_code(400); // Bad Request
                echo json_encode(['success' => false, 'message' => $message]);
                exit();
            } else {
                $this->setFlashMessage('error', $message);
                // Redireciona de volta para a página anterior para não perder o contexto do formulário
                header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/clientes'));
                exit();
            }
        }


        try {
            $savedId = $this->model->salvarCliente($dados);
            if ($savedId !== false && $savedId !== null) {
                error_log("Cliente salvo com sucesso - ID: " . $savedId);

                // Log de Auditoria
                $action = !empty($dados['id']) ? 'UPDATE' : 'CREATE';
                $description = !empty($dados['id']) ? "Atualizou cliente ID {$savedId}: {$dados['nome']}" : "Cadastrou novo cliente: {$dados['nome']}";
                $this->logAction($action, $description, 'Clientes', $savedId);

                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => true, 'message' => 'Cliente salvo com sucesso!', 'data' => ['id' => $savedId, 'nome' => $dados['nome']]]);
                    exit();
                } else {
                    $this->setFlashMessage('success', 'Cliente salvo com sucesso!');
                }
            } else {
                error_log("salvarCliente retornou false para ID: " . ($dados['id'] ?? 'novo'));
                $msgErro = method_exists($this->model, 'getLastError') ? $this->model->getLastError() : 'Ocorreu um erro desconhecido ao salvar o cliente.';
                if ($isAjax) {
                    header('Content-Type: application/json');
                    http_response_code(500);
                    echo json_encode(['success' => false, 'message' => $msgErro]);
                    exit();
                } else {
                    $this->setFlashMessage('error', $msgErro);
                }
            }
        } catch (\PDOException $e) {
            error_log("Exceção PDOException ao salvar cliente: " . $e->getMessage());
            if ($isAjax) {
                header('Content-Type: application/json');
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Erro de Banco de Dados: ' . $e->getMessage()]);
                exit();
            }
            $this->setFlashMessage('error', 'Erro de Banco de Dados: ' . $e->getMessage());
        }

        // Redirecionamento padrão para requisições não-AJAX
        $redirectUrl = ($savedId !== false && $savedId !== null) ? BASE_URL . '/clientes/detalhe/' . $savedId : BASE_URL . '/clientes';
        header('Location: ' . $redirectUrl);
        exit();
    }

    /**
     * Endpoint para adicionar uma nova categoria de cliente via AJAX.
     */
    public function addCategoria()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método inválido.']);
            exit();
        }

        $nome = trim($_POST['nome'] ?? ''); // Usar trim para limpar espaços em branco

        if (empty($nome)) {
            echo json_encode(['success' => false, 'message' => 'O nome da categoria é obrigatório.']);
            exit();
        }

        $newId = $this->model->addCategoria($nome);

        if ($newId) {
            echo json_encode([
                'success' => true,
                'message' => 'Categoria adicionada!',
                'data' => ['id' => $newId, 'nome' => $nome]
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erro ao adicionar categoria. Pode já existir.']);
        }
        exit();
    }

    /**
     * Endpoint para buscar segmentos de uma categoria via AJAX.
     * @param int $categoriaId
     */
    public function getSegmentosAjax(int $categoriaId)
    {
        header('Content-Type: application/json');
        if ($categoriaId <= 0) {
            echo json_encode(['success' => false, 'data' => []]);
            exit();
        }

        $segmentos = $this->model->getSegmentosByCategoriaId($categoriaId);

        echo json_encode(['success' => true, 'data' => $segmentos]);
        exit();
    }

    /**
     * Endpoint para adicionar um novo segmento via AJAX.
     */
    public function addSegmentoAjax()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método inválido.']);
            exit();
        }

        $nome = trim($_POST['nome'] ?? ''); // Usar trim para limpar espaços em branco
        $categoriaId = filter_input(INPUT_POST, 'categoria_id', FILTER_VALIDATE_INT);

        if (empty($nome) || empty($categoriaId)) {
            echo json_encode(['success' => false, 'message' => 'Nome do segmento e ID da categoria são obrigatórios.']);
            exit();
        }

        $newId = $this->model->addSegmento($nome, $categoriaId);

        if ($newId) {
            echo json_encode([
                'success' => true,
                'message' => 'Segmento adicionado!',
                'data' => [
                    'id' => $newId,
                    'nome' => $nome,
                    'categoria_id' => $categoriaId
                ]
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erro ao adicionar segmento. Pode já existir para esta categoria.']);
        }
        exit();
    }

    /**
     * Exclui um cliente.
     * @param int $id O ID do cliente a ser excluído.
     */
    public function excluir(int $id)
    {
        // 1. Valida o método da requisição
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->setFlashMessage('error', 'Operação inválida.');
            header('Location: ' . BASE_URL . '/clientes');
            exit();
        }

        // 2. Validação do token CSRF
        if (!isset($_POST['csrf_token']) || !hash_equals($this->session->get('csrf_token'), $_POST['csrf_token'])) {
            $this->setFlashMessage('error', 'Erro de validação de segurança (CSRF). Por favor, tente novamente.');
            header('Location: ' . BASE_URL . '/clientes');
            exit();
        }

        // Se o ID não veio pela rota (argumento zerado), tenta pegar do POST
        if ($id <= 0) {
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: 0;
        }

        if ($id <= 0) {
            $this->setFlashMessage('error', 'ID de cliente inválido.');
        } else {
            // Ao ARQUIVAR, não precisamos bloquear se houver projetos ou contratos,
            // pois o registro será mantido (apenas inativado).

            // 3. Tenta ARQUIVAR o cliente
            if ($this->model->arquivarCliente($id)) {
                $this->setFlashMessage('success', 'Cliente arquivado com sucesso!');
                $this->logAction('DELETE', "Arquivou cliente ID {$id}", 'Clientes', $id);
            } else {
                $this->setFlashMessage('error', 'Erro ao arquivar o cliente.');
            }
        }

        header('Location: ' . BASE_URL . '/clientes');
        exit();
    }

    /**
     * Restaura um cliente inativo.
     */
    public function restaurar(int $id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->setFlashMessage('error', 'Operação inválida.');
            header('Location: ' . BASE_URL . '/clientes');
            exit();
        }

        if ($this->model->restaurarCliente($id)) {
            $this->setFlashMessage('success', 'Cliente restaurado com sucesso!');
        } else {
            $this->setFlashMessage('error', 'Erro ao restaurar o cliente.');
        }

        header('Location: ' . BASE_URL . '/clientes/detalhe/' . $id);
        exit();
    }

    /**
     * Salva uma nova interação com o cliente.
     */
    public function registrarInteracao()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/clientes');
            exit();
        }

        $dados = $_POST;

        // Adiciona o ID do usuário logado aos dados
        if ($this->session->get('user_id')) {
            $dados['usuario_id'] = $this->session->get('user_id');
        }

        // TODO: Adicionar validação dos dados aqui.

        if ($this->model->registrarInteracao($dados)) {
            $this->setFlashMessage('success', 'Interação registrada com sucesso!');
        } else {
            $this->setFlashMessage('error', 'Erro ao registrar a interação.');
        }

        // Redireciona de volta para a página de detalhes se possível
        if (!empty($dados['cliente_id'])) {
            header('Location: ' . BASE_URL . '/clientes/detalhe/' . $dados['cliente_id']);
        } else {
            header('Location: ' . BASE_URL . '/clientes');
        }
        exit();
    }

    /**
     * Endpoint para buscar dados de um CNPJ via API externa.
     * Atua como um proxy para evitar problemas de CORS no frontend.
     * @param string $cnpj O CNPJ a ser consultado (apenas números).
     */
    public function consultarCnpj(string $cnpj)
    {
        header('Content-Type: application/json');

        // Remove qualquer formatação do CNPJ
        $cnpjLimpo = preg_replace('/[^0-9]/', '', $cnpj);

        if (strlen($cnpjLimpo) !== 14) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'CNPJ inválido.']);
            exit();
        }

        // URL da API pública (BrasilAPI)
        $apiUrl = "https://brasilapi.com.br/api/cnpj/v1/{$cnpjLimpo}";

        // Usando cURL para fazer a requisição do servidor para a API externa
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10); // Timeout de 10 segundos
        $response = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Verifica o código de status da resposta da API externa
        if ($httpCode === 200) {
            // Sucesso: repassa a resposta JSON para o nosso frontend
            http_response_code(200);
            echo $response;
        } elseif ($httpCode === 429) {
            // Erro de "Too Many Requests"
            http_response_code(429);
            echo json_encode(['success' => false, 'message' => 'Muitas solicitações foram feitas. Por favor, aguarde um minuto e tente novamente.']);
        } else {
            // Outros erros da API externa (ex: 404 CNPJ não encontrado, 500 erro de servidor)
            // Tenta decodificar a mensagem de erro da API, se houver
            $errorData = json_decode($response, true);
            $errorMessage = $errorData['message'] ?? 'Não foi possível consultar o CNPJ. Verifique o número ou tente mais tarde.';

            http_response_code($httpCode); // Repassa o código de erro original
            echo json_encode(['success' => false, 'message' => $errorMessage]);
        }

        exit(); // Garante que nada mais seja executado
    }

    /**
     * Limpa o histórico de interações de um cliente.
     * @param int $id O ID do cliente.
     */
    public function limparHistorico(int $id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->setFlashMessage('error', 'Operação inválida.');
            header('Location: ' . BASE_URL . '/clientes/detalhe/' . $id);
            exit();
        }

        // Validação do token CSRF
        if (!isset($_POST['csrf_token']) || !hash_equals($this->session->get('csrf_token'), $_POST['csrf_token'])) {
            $this->setFlashMessage('error', 'Erro de validação de segurança (CSRF). Por favor, tente novamente.');
            header('Location: ' . BASE_URL . '/clientes/detalhe/' . $id);
            exit();
        }

        if ($this->model->limparHistoricoInteracoes($id)) {
            $this->setFlashMessage('success', 'Histórico de interações limpo com sucesso!');
        } else {
            $this->setFlashMessage('error', 'Erro ao limpar o histórico de interações.');
        }

        header('Location: ' . BASE_URL . '/clientes/detalhe/' . $id);
        exit();
    }

    /**
     * Valida um número de CPF ou CNPJ.
     * @param string $cpfCnpj O número a ser validado.
     * @param string $tipo 'Fisica' para CPF, 'Juridica' para CNPJ.
     * @return bool Retorna true se for válido, false caso contrário.
     */
    private function validarCpfCnpj(?string $cpfCnpj, ?string $tipo): bool
    {
        if (empty($cpfCnpj)) {
            return false;
        }

        // Limpa o valor, deixando apenas números
        $valor = preg_replace('/[^0-9]/', '', $cpfCnpj);

        // Se o tipo não for informado, tenta deduzir pelo tamanho
        if (empty($tipo)) {
            $tipo = strlen($valor) === 11 ? 'Fisica' : (strlen($valor) === 14 ? 'Juridica' : '');
        }

        // Normaliza o tipo para garantir a comparação correta (ex: 'fisica' -> 'Fisica')
        $tipo = ucfirst(strtolower($tipo));

        if ($tipo === 'Fisica') { // CPF
            if (strlen($valor) != 11 || preg_match('/(\d)\1{10}/', $valor)) {
                return false;
            }
            for ($t = 9; $t < 11; $t++) {
                for ($d = 0, $c = 0; $c < $t; $c++) {
                    $d += $valor[$c] * (($t + 1) - $c);
                }
                $d = ((10 * $d) % 11) % 10;
                if ($valor[$c] != $d) {
                    return false;
                }
            }
            return true;
        } elseif ($tipo === 'Juridica') { // CNPJ
            if (strlen($valor) != 14 || preg_match('/(\d)\1{13}/', $valor)) {
                return false;
            }
            for ($t = 12; $t < 14; $t++) {
                for ($d = 0, $p = $t - 7, $c = 0; $c < $t; $c++) {
                    $d += $valor[$c] * $p;
                    $p = ($p == 2) ? 9 : $p - 1;
                }
                $d = ((10 * $d) % 11) % 10;
                if ($valor[$c] != $d) {
                    return false;
                }
            }
            return true;
        }

        return false; // Tipo inválido
    }
}
