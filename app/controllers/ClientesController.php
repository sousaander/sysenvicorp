<?php

namespace App\Controllers;

use App\Core\Connection;
use App\Models\ClientesModel;

class ClientesController extends BaseController
{
    private $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new ClientesModel();
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

        // Adiciona dados necessários para o formulário de criação no modal
        // (mesmo que inicialmente vazio)
        $data = array_merge([
            'pageTitle' => 'Clientes - CRM e Propostas',
            'clientes' => $clientes,
            'todosClientes' => $todosClientes,
            'paginaAtual' => $paginaAtual,
            'totalPaginas' => $totalPaginas,
            'filtros' => $filtros,
            'cliente' => null, // Garante que a variável exista para o form.php
            'categorias' => $categorias, // Passa as categorias para a view
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

        $data = [
            'pageTitle' => 'Detalhes do Cliente',
            'cliente' => $cliente,
            'interacoes' => $interacoes,
            'todosClientes' => $todosClientes, // Passa para o modal
            'isDetalhePage' => true, // Flag para a view de detalhes
        ];
        $this->renderView('clientes/detalhe', $data);
    }

    /**
     * Exibe o formulário para adicionar um novo cliente/lead.
     */
    public function novo()
    {
        // Em vez de renderizar uma nova página, redireciona para o index
        // com um parâmetro para abrir o modal de novo cliente.
        header('Location: ' . BASE_URL . '/clientes?action=novo');
        exit();
    }

    /**
     * Busca os dados de um cliente e retorna o HTML do formulário para edição via AJAX.
     * @param int $id O ID do cliente.
     */
    public function getFormForEdit(int $id)
    {
        $cliente = $this->model->getClienteById($id);

        if (!$cliente) {
            http_response_code(404);
            echo "Cliente não encontrado.";
            exit();
        }

        // Busca listas para os selects do formulário
        $categorias = $this->model->getCategorias();
        $segmentos = $cliente['categoria_id'] ? $this->model->getSegmentosByCategoriaId($cliente['categoria_id']) : [];

        $data = [
            'cliente' => $cliente,
            'categorias' => $categorias,
            'segmentos' => $segmentos, // Passa os segmentos iniciais
        ];

        // Renderiza apenas o formulário, sem o template principal
        $this->renderPartial('clientes/form', $data);
    }

    /**
     * Retorna o HTML do formulário para um novo cliente via AJAX.
     */
    public function getFormForNew()
    {
        // Busca listas para os selects do formulário
        $categorias = $this->model->getCategorias();

        $data = [
            'cliente' => null, // Garante que o formulário esteja no modo de criação
            'categorias' => $categorias,
            'segmentos' => [], // Começa com segmentos vazios
        ];

        $this->renderPartial('clientes/form', $data);
    }

    /**
     * Salva um novo cliente ou atualiza um existente.
     */
    public function salvar()
    {
        // Verifica se é uma requisição AJAX
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';


        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/clientes');
            exit();
        }

        // Coleta e organiza os dados do formulário
        $dados = [
            'id' => filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: null,
            'tipo_cliente' => filter_input(INPUT_POST, 'tipo_cliente', FILTER_SANITIZE_SPECIAL_CHARS),
            'nome' => filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_SPECIAL_CHARS),
            'nome_fantasia' => filter_input(INPUT_POST, 'nome_fantasia', FILTER_SANITIZE_SPECIAL_CHARS),
            'cnpj_cpf' => filter_input(INPUT_POST, 'cnpj_cpf', FILTER_SANITIZE_SPECIAL_CHARS),
            'rg' => filter_input(INPUT_POST, 'rg', FILTER_SANITIZE_SPECIAL_CHARS),
            'inscricao_estadual' => filter_input(INPUT_POST, 'inscricao_estadual', FILTER_SANITIZE_SPECIAL_CHARS),
            'ie_isento' => filter_input(INPUT_POST, 'ie_isento', FILTER_VALIDATE_INT),
            'inscricao_municipal' => filter_input(INPUT_POST, 'inscricao_municipal', FILTER_SANITIZE_SPECIAL_CHARS),
            'data_nascimento' => filter_input(INPUT_POST, 'data_nascimento'),
            'categoria_id' => filter_input(INPUT_POST, 'categoria_id', FILTER_VALIDATE_INT), // ID da categoria
            'segmento' => filter_input(INPUT_POST, 'segmento', FILTER_SANITIZE_SPECIAL_CHARS), // Novo campo segmento
            'classificacao' => filter_input(INPUT_POST, 'classificacao', FILTER_SANITIZE_SPECIAL_CHARS),
            'origem_cliente' => filter_input(INPUT_POST, 'origem_cliente', FILTER_SANITIZE_SPECIAL_CHARS),
            'observacoes_iniciais' => filter_input(INPUT_POST, 'observacoes_iniciais', FILTER_SANITIZE_SPECIAL_CHARS),
            'status' => filter_input(INPUT_POST, 'status', FILTER_SANITIZE_SPECIAL_CHARS),
            // Agrupa os dados em arrays para os campos JSON
            'enderecos' => $_POST['enderecos'] ?? [],
            'contatos' => $_POST['contatos'] ?? [],
            'financeiro' => $_POST['financeiro'] ?? [],
            'comercial' => $_POST['comercial'] ?? [],
            // A parte de upload de arquivos precisaria de uma lógica separada com $_FILES
        ];

        try {
            if ($this->model->salvarCliente($dados)) {
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => true, 'message' => 'Cliente salvo com sucesso!']);
                    exit();
                } else {
                    $this->setFlashMessage('success', 'Cliente salvo com sucesso!');
                }
            } else {
                if ($isAjax) {
                    header('Content-Type: application/json');
                    http_response_code(500);
                    echo json_encode(['success' => false, 'message' => 'Ocorreu um erro desconhecido ao salvar o cliente.']);
                    exit();
                } else {
                    $this->setFlashMessage('error', 'Ocorreu um erro desconhecido ao salvar o cliente.');
                }
            }
        } catch (\PDOException $e) {
            if ($isAjax) {
                header('Content-Type: application/json');
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Erro de Banco de Dados: ' . $e->getMessage()]);
                exit();
            }
            $this->setFlashMessage('error', 'Erro de Banco de Dados: ' . $e->getMessage());
        }

        // Redirecionamento padrão para requisições não-AJAX
        $redirectUrl = isset($dados['id']) ? BASE_URL . '/clientes/detalhe/' . $dados['id'] : BASE_URL . '/clientes';
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
        if ($id <= 0) {
            $this->setFlashMessage('error', 'ID de cliente inválido.');
            header('Location: ' . BASE_URL . '/clientes');
            exit();
        }

        if ($this->model->excluirCliente($id)) {
            $this->setFlashMessage('success', 'Cliente excluído com sucesso!');
        } else {
            $this->setFlashMessage('error', 'Erro ao excluir o cliente.');
        }

        header('Location: ' . BASE_URL . '/clientes');
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

        // TODO: Adicionar validação dos dados aqui.

        if ($this->model->registrarInteracao($dados)) {
            $this->setFlashMessage('success', 'Interação registrada com sucesso!');
        } else {
            $this->setFlashMessage('error', 'Erro ao registrar a interação.');
        }

        header('Location: ' . BASE_URL . '/clientes');
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
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Repassa a resposta da API para o nosso frontend
        http_response_code($httpCode);
        echo $response;
        exit();
    }
}
