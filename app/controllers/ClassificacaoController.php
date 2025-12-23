<?php

namespace App\Controllers;

use App\Core\Connection;
use App\Models\FinancialModel;

class ClassificacaoController extends BaseController
{
    private $financialModel;

    public function __construct()
    {
        parent::__construct();
        $this->financialModel = new FinancialModel();
    }

    /**
     * Exibe a lista de classificações cadastradas.
     */
    public function index()
    {
        // Usamos o método já existente que busca todas as classificações
        $classificacoes = $this->financialModel->getClassificacoes();

        $data = [
            'pageTitle' => 'Gerenciar Categorias Financeiras',
            'classificacoes' => $classificacoes,
        ];

        $this->renderView('financeiro/classificacao/index', $data);
    }

    /**
     * Exibe o formulário para nova classificação ou para edição de uma existente.
     * @param int|null $id O ID da classificação para edição.
     */
    public function form(int $id = null)
    {
        $classificacao = null;
        if ($id) {
            $classificacao = $this->financialModel->getClassificacaoById($id);
            if (!$classificacao) {
                $this->setFlashMessage('error', 'Categoria não encontrada.');
                header('Location: ' . BASE_URL . '/classificacao');
                exit();
            }
        }

        $data = [
            'pageTitle' => $id ? 'Editar Categoria' : 'Nova Categoria',
            'classificacao' => $classificacao,
        ];

        $this->renderView('financeiro/classificacao/form', $data);
    }

    /**
     * Processa o formulário de cadastro/edição.
     */
    public function salvar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/classificacao');
            exit();
        }

        $dados = [
            'id' => filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: null,
            'nome' => filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_SPECIAL_CHARS),
            'tipo' => filter_input(INPUT_POST, 'tipo', FILTER_SANITIZE_SPECIAL_CHARS),
        ];

        if (empty($dados['nome'])) {
            $this->setFlashMessage('error', 'O nome da categoria é obrigatório.');
            header('Location: ' . BASE_URL . '/classificacao/form/' . ($dados['id'] ?? ''));
            exit();
        }

        if ($this->financialModel->salvarClassificacao($dados)) {
            $this->setFlashMessage('success', 'Categoria salva com sucesso!');
        } else {
            $this->setFlashMessage('error', 'Erro ao salvar a categoria.');
        }

        header('Location: ' . BASE_URL . '/classificacao');
        exit();
    }

    /**
     * Exclui uma classificação.
     */
    public function excluir(int $id)
    {
        $this->financialModel->excluirClassificacao($id);
        $this->setFlashMessage('success', 'Categoria excluída com sucesso.');
        header('Location: ' . BASE_URL . '/classificacao');
        exit();
    }
}
