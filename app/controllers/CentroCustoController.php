<?php

namespace App\Controllers;

use App\Models\FinancialModel;

class CentroCustoController extends BaseController
{
    private $financialModel;

    public function __construct()
    {
        parent::__construct();
        // Reutilizaremos o FinancialModel, pois ele já tem os métodos de acesso ao banco.
        $this->financialModel = new FinancialModel();
    }

    /**
     * Exibe a lista de centros de custo cadastrados.
     */
    public function index()
    {
        $centrosCusto = $this->financialModel->getCentrosCusto();

        $data = [
            'pageTitle' => 'Gerenciar Centros de Custo',
            'centrosCusto' => $centrosCusto,
        ];

        $this->renderView('financeiro/centro_custo/index', $data);
    }

    /**
     * Exibe o formulário para novo centro de custo ou para edição de um existente.
     * @param int|null $id O ID para edição.
     */
    public function form(int $id = null)
    {
        $centroCusto = null;
        if ($id) {
            $centroCusto = $this->financialModel->getCentroCustoById($id);
            if (!$centroCusto) {
                $this->setFlashMessage('error', 'Centro de Custo não encontrado.');
                header('Location: ' . BASE_URL . '/centrocusto');
                exit();
            }
        }

        $data = [
            'pageTitle' => $id ? 'Editar Centro de Custo' : 'Novo Centro de Custo',
            'centroCusto' => $centroCusto,
        ];

        $this->renderView('financeiro/centro_custo/form', $data);
    }

    /**
     * Processa o formulário de cadastro/edição.
     */
    public function salvar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/centrocusto');
            exit();
        }

        $dados = [
            'id' => filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: null,
            'nome' => filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_SPECIAL_CHARS),
        ];

        if (empty($dados['nome'])) {
            $this->setFlashMessage('error', 'O nome do centro de custo é obrigatório.');
            header('Location: ' . BASE_URL . '/centrocusto/form/' . ($dados['id'] ?? ''));
            exit();
        }

        if ($this->financialModel->salvarCentroCusto($dados)) {
            $this->setFlashMessage('success', 'Centro de Custo salvo com sucesso!');
        } else {
            $this->setFlashMessage('error', 'Erro ao salvar o Centro de Custo. O nome pode já existir.');
        }

        header('Location: ' . BASE_URL . '/centrocusto');
        exit();
    }

    /**
     * Exclui um centro de custo.
     */
    public function excluir(int $id)
    {
        if ($this->financialModel->excluirCentroCusto($id)) {
            $this->setFlashMessage('success', 'Centro de Custo excluído com sucesso.');
        } else {
            $this->setFlashMessage('error', 'Erro ao excluir. Verifique se o centro de custo não está em uso.');
        }
        header('Location: ' . BASE_URL . '/centrocusto');
        exit();
    }
}