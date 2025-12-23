<?php

namespace App\Controllers;

use App\Core\Connection;
use App\Models\ContratosModel;
use App\Models\FornecedoresModel;
use App\Models\FinancialModel;

class FornecedoresController extends BaseController
{
    private $model;
    private $contratosModel;
    private $financialModel;

    public function __construct()
    {
        parent::__construct();
        $this->contratosModel = new ContratosModel();
        $this->financialModel = new FinancialModel();
        $this->model = new FornecedoresModel();
    }

    public function index()
    {
        // Coleta filtros da URL
        $filtros = [
            'busca' => filter_input(INPUT_GET, 'busca', FILTER_SANITIZE_SPECIAL_CHARS),
            'status' => filter_input(INPUT_GET, 'status', FILTER_SANITIZE_SPECIAL_CHARS),
        ];

        // Se nenhum filtro de status for aplicado, define o padrão como 'Ativo'
        if (!isset($_GET['status'])) {
            $filtros['status'] = 'Ativo';
        }

        // Lógica de Paginação
        $paginaAtual = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1;
        $itensPorPagina = 10;
        $offset = ($paginaAtual - 1) * $itensPorPagina;

        // Coleta dados do modelo
        $summary = $this->model->getFornecedoresSummary();
        $fornecedores = $this->model->getFornecedores($filtros, $itensPorPagina, $offset);
        $totalFornecedores = $this->model->getFornecedoresCount($filtros);
        $totalPaginas = ceil($totalFornecedores / $itensPorPagina);

        $data = array_merge([
            'pageTitle' => 'Fornecedores - Gestão e Conformidade',
            'fornecedores' => $fornecedores,
            'fornecedor' => null, // Garante que a variável exista para o form.php no modal
            'paginaAtual' => $paginaAtual,
            'totalPaginas' => $totalPaginas,
            'filtros' => $filtros,
        ], $summary);

        $this->renderView('fornecedores/index', $data);
    }

    /**
     * Exibe o formulário para adicionar um novo fornecedor.
     */
    public function novo()
    {
        // Redireciona para a página de índice com um parâmetro para abrir o modal
        header('Location: ' . BASE_URL . '/fornecedores?action=novo');
        exit();
    }

    /**
     * Exibe os detalhes de um fornecedor.
     * @param int $id O ID do fornecedor.
     */
    public function detalhe(int $id)
    {
        $fornecedor = $this->model->getFornecedorById($id);

        if (!$fornecedor) {
            $this->setFlashMessage('error', 'Fornecedor não encontrado.');
            header('Location: ' . BASE_URL . '/fornecedores');
            exit();
        }

        // Busca dados relacionados
        $contratos = $this->contratosModel->getContratosByPessoaId($id);
        $historicoCompras = $this->financialModel->getTransacoesByPessoaId($id, 'P'); // 'P' para despesas
        $ocorrencias = $this->model->getOcorrenciasByFornecedorId($id);

        $data = [
            'pageTitle' => 'Detalhes do Fornecedor',
            'fornecedor' => $fornecedor,
            'contratos' => $contratos,
            'historicoCompras' => $historicoCompras,
            'ocorrencias' => $ocorrencias,
        ];

        $this->renderView('fornecedores/detalhe', $data);
    }

    /**
     * Salva um novo fornecedor ou atualiza um existente.
     */
    public function salvar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/fornecedores');
            exit();
        }

        // Coleta e sanitiza os dados do formulário
        $dados = [
            'id' => filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: null,
            'nome' => filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_SPECIAL_CHARS),
            'cnpj' => filter_input(INPUT_POST, 'cnpj', FILTER_SANITIZE_SPECIAL_CHARS), // Este é o campo CNPJ/CPF
            'status' => filter_input(INPUT_POST, 'status', FILTER_SANITIZE_SPECIAL_CHARS),
            'tipo_pessoa' => filter_input(INPUT_POST, 'tipo_pessoa', FILTER_SANITIZE_SPECIAL_CHARS),
            'nome_fantasia' => filter_input(INPUT_POST, 'nome_fantasia', FILTER_SANITIZE_SPECIAL_CHARS),
            'categoria_fornecimento' => filter_input(INPUT_POST, 'categoria_fornecimento', FILTER_SANITIZE_SPECIAL_CHARS),
            'ie_isento' => filter_input(INPUT_POST, 'ie_isento', FILTER_VALIDATE_INT),
            'motivo_inativacao' => filter_input(INPUT_POST, 'motivo_inativacao', FILTER_SANITIZE_SPECIAL_CHARS),
            'data_inativacao' => filter_input(INPUT_POST, 'data_inativacao'),
            // Campos JSON
            'endereco' => $_POST['endereco'] ?? [], // Array de endereço
            'inscricao_estadual' => filter_input(INPUT_POST, 'inscricao_estadual', FILTER_SANITIZE_SPECIAL_CHARS),
            'inscricao_municipal' => filter_input(INPUT_POST, 'inscricao_municipal', FILTER_SANITIZE_SPECIAL_CHARS),
            'contato' => $_POST['contato'] ?? [],
            'dados_financeiros' => $_POST['dados_financeiros'] ?? [],
            'info_comerciais' => $_POST['info_comerciais'] ?? [],
        ];

        try {
            if ($this->model->salvarFornecedor($dados)) {
                $message = $dados['id'] ? 'Fornecedor atualizado com sucesso!' : 'Fornecedor cadastrado com sucesso!';
                $this->setFlashMessage('success', $message);
            } else {
                $this->setFlashMessage('error', 'Ocorreu um erro desconhecido ao salvar o fornecedor.');
            }
        } catch (\PDOException $e) {
            // Captura o erro do banco de dados e exibe uma mensagem mais informativa.
            $this->setFlashMessage('error', 'Erro de Banco de Dados: ' . $e->getMessage());
        }

        header('Location: ' . BASE_URL . '/fornecedores');
        exit();
    }

    /**
     * Salva uma nova ocorrência para um fornecedor.
     */
    public function salvarOcorrencia()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/fornecedores');
            exit();
        }

        $fornecedor_id = filter_input(INPUT_POST, 'fornecedor_id', FILTER_VALIDATE_INT);

        if (!$fornecedor_id) {
            $this->setFlashMessage('error', 'ID do fornecedor inválido.');
            header('Location: ' . BASE_URL . '/fornecedores');
            exit();
        }

        $dados = [
            'fornecedor_id' => $fornecedor_id,
            'data_ocorrencia' => filter_input(INPUT_POST, 'data_ocorrencia'),
            'tipo' => filter_input(INPUT_POST, 'tipo', FILTER_SANITIZE_SPECIAL_CHARS),
            'descricao' => filter_input(INPUT_POST, 'descricao', FILTER_SANITIZE_SPECIAL_CHARS),
            'responsavel' => filter_input(INPUT_POST, 'responsavel', FILTER_SANITIZE_SPECIAL_CHARS),
        ];

        if ($this->model->salvarOcorrencia($dados)) {
            $this->setFlashMessage('success', 'Ocorrência registrada com sucesso!');
        } else {
            $this->setFlashMessage('error', 'Erro ao registrar a ocorrência.');
        }

        header('Location: ' . BASE_URL . '/fornecedores/detalhe/' . $fornecedor_id);
        exit();
    }

    /**
     * Busca o HTML do formulário para um novo fornecedor (usado via AJAX).
     */
    public function getFormForNew()
    {
        $data = [
            'fornecedor' => null, // Garante que o formulário esteja no modo de criação
        ];

        // Renderiza apenas o formulário, sem o template principal
        $this->renderPartial('fornecedores/form', $data);
    }
}
