<?php

namespace App\Controllers;

use App\Core\Connection;
use App\Models\BancoModel;

class BancoController extends BaseController
{
    private $bancoModel;

    public function __construct()
    {
        parent::__construct();
        $this->bancoModel = new BancoModel();
    }

    /**
     * Exibe a lista de bancos cadastrados.
     */
    public function index()
    {
        $bancos = $this->bancoModel->getAll();

        $data = [
            'pageTitle' => 'Gestão de Bancos e Contas',
            'bancos' => $bancos,
        ];

        $this->renderView('banco/index', $data);
    }

    /**
     * Exibe o formulário para um novo banco ou para edição de um existente.
     * @param int|null $id O ID do banco para edição.
     */
    public function form(int $id = null)
    {
        $banco = null;
        if ($id) {
            $banco = $this->bancoModel->getById($id);
            if (!$banco) {
                $this->setFlashMessage('error', 'Banco não encontrado.');
                header('Location: ' . BASE_URL . '/banco');
                exit();
            }
        }

        $data = [
            'pageTitle' => $id ? 'Editar Banco' : 'Novo Banco',
            'banco' => $banco,
        ];

        $this->renderView('banco/form', $data);
    }

    /**
     * Processa o formulário de cadastro/edição.
     */
    public function salvar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/banco');
            exit();
        }

        $dados = [
            'id' => filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: null,
            'nome' => filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_SPECIAL_CHARS),
            'tipo' => filter_input(INPUT_POST, 'tipo', FILTER_SANITIZE_SPECIAL_CHARS),
            'saldo_inicial' => filter_input(INPUT_POST, 'saldo_inicial', FILTER_VALIDATE_FLOAT, FILTER_FLAG_ALLOW_FRACTION),
        ];

        if (empty($dados['nome']) || $dados['saldo_inicial'] === false) {
            $this->setFlashMessage('error', 'Dados inválidos. Nome e Saldo Inicial são obrigatórios.');
            header('Location: ' . BASE_URL . '/banco/form/' . ($dados['id'] ?? ''));
            exit();
        }

        if ($this->bancoModel->salvar($dados)) {
            $this->setFlashMessage('success', 'Banco salvo com sucesso!');
        } else {
            $this->setFlashMessage('error', 'Erro ao salvar o banco.');
        }

        header('Location: ' . BASE_URL . '/banco');
        exit();
    }

    /**
     * Exclui um banco.
     * @param int $id O ID do banco a ser excluído.
     */
    public function excluir(int $id)
    {
        if ($this->bancoModel->excluir($id)) {
            $this->setFlashMessage('success', 'Banco excluído com sucesso.');
        } else {
            $this->setFlashMessage('error', 'Erro ao excluir o banco. Verifique se ele não está associado a transações.');
        }

        header('Location: ' . BASE_URL . '/banco');
        exit();
    }
}
