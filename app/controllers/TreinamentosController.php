<?php

namespace App\Controllers;

use App\Core\Connection;
use App\Models\TreinamentosModel;

class TreinamentosController extends BaseController
{
    private $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new TreinamentosModel();
    }

    /**
     * Exibe a lista de treinamentos.
     */
    public function index()
    {
        $paginaAtual = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1;
        $itensPorPagina = 10;
        $offset = ($paginaAtual - 1) * $itensPorPagina;

        $treinamentos = $this->model->getAllTreinamentos($itensPorPagina, $offset);
        $totalRegistros = $this->model->getTreinamentosCount();
        $totalPaginas = ceil($totalRegistros / $itensPorPagina);

        $data = [
            'pageTitle' => 'Gestão de Treinamentos',
            'treinamentos' => $treinamentos,
            'paginaAtual' => $paginaAtual,
            'totalPaginas' => $totalPaginas,
        ];

        $this->renderView('treinamentos/index', $data);
    }

    /**
     * Exibe o formulário para um novo treinamento.
     */
    public function novo()
    {
        $data = [
            'pageTitle' => 'Novo Treinamento',
            'treinamento' => null,
        ];
        $this->renderView('treinamentos/form', $data);
    }

    /**
     * Exibe o formulário para editar um treinamento.
     * @param int $id
     */
    public function editar(int $id)
    {
        $treinamento = $this->model->getTreinamentoById($id);
        if (!$treinamento) {
            $this->setFlashMessage('error', 'Treinamento não encontrado.');
            header('Location: ' . BASE_URL . '/treinamentos');
            exit();
        }

        $data = [
            'pageTitle' => 'Editar Treinamento',
            'treinamento' => $treinamento,
        ];
        $this->renderView('treinamentos/form', $data);
    }

    /**
     * Salva um treinamento (novo ou existente).
     */
    public function salvar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/treinamentos');
            exit();
        }

        $dados = [
            'id' => filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: null,
            'nome_treinamento' => filter_input(INPUT_POST, 'nome_treinamento', FILTER_SANITIZE_SPECIAL_CHARS),
            'descricao' => filter_input(INPUT_POST, 'descricao', FILTER_SANITIZE_SPECIAL_CHARS),
            'data_prevista' => filter_input(INPUT_POST, 'data_prevista'),
            'instrutor' => filter_input(INPUT_POST, 'instrutor', FILTER_SANITIZE_SPECIAL_CHARS),
            'local' => filter_input(INPUT_POST, 'local', FILTER_SANITIZE_SPECIAL_CHARS),
            'status' => filter_input(INPUT_POST, 'status', FILTER_SANITIZE_SPECIAL_CHARS),
        ];

        if (empty($dados['nome_treinamento']) || empty($dados['data_prevista'])) {
            $this->setFlashMessage('error', 'Nome e Data Prevista são obrigatórios.');
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            exit();
        }

        if ($this->model->salvarTreinamento($dados)) {
            $this->setFlashMessage('success', 'Treinamento salvo com sucesso!');
        } else {
            $this->setFlashMessage('error', 'Erro ao salvar o treinamento.');
        }

        header('Location: ' . BASE_URL . '/treinamentos');
        exit();
    }

    /**
     * Exclui um treinamento.
     * @param int $id
     */
    public function excluir(int $id)
    {
        if ($this->model->excluirTreinamento($id)) {
            $this->setFlashMessage('success', 'Treinamento excluído com sucesso!');
        } else {
            $this->setFlashMessage('error', 'Erro ao excluir o treinamento.');
        }

        header('Location: ' . BASE_URL . '/treinamentos');
        exit();
    }
}