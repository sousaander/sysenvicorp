<?php

namespace App\Controllers;

use App\Core\Connection;
use App\Models\NotaFiscalModel;
use App\Models\ClientesModel; // Assuming we might need clients for invoice

class NotaFiscalController extends BaseController
{
    private $notaFiscalModel;
    private $clientesModel; // To get a list of clients for the form

    public function __construct()
    {
        parent::__construct();
        $this->notaFiscalModel = new NotaFiscalModel();
        $this->clientesModel = new ClientesModel();
    }

    /**
     * Exibe a lista de notas fiscais.
     */
    public function index()
    {
        $notas = $this->notaFiscalModel->getAllNotasFiscais();

        $data = [
            'pageTitle' => 'Notas Fiscais',
            'notas' => $notas,
        ];

        $this->renderView('financeiro/nota_fiscal/index', $data);
    }

    /**
     * Exibe o formulário para nova nota fiscal ou para edição de uma existente.
     * @param int|null $id O ID da nota fiscal para edição.
     */
    public function form(int $id = null)
    {
        $nota = null;
        if ($id) {
            $nota = $this->notaFiscalModel->getNotaFiscalById($id);
            if (!$nota) {
                $this->setFlashMessage('error', 'Nota Fiscal não encontrada.');
                header('Location: ' . BASE_URL . '/notaFiscal');
                exit();
            }
        }

        // Get clients for the dropdown
        $clientes = $this->clientesModel->getClientesSummary(); // Using summary for simplicity, could be a dedicated method

        $data = [
            'pageTitle' => $id ? 'Editar Nota Fiscal' : 'Emitir Nova Nota Fiscal',
            'nota' => $nota,
            'clientes' => $clientes, // Pass clients to the view
        ];

        $this->renderView('financeiro/nota_fiscal/form', $data);
    }

    /**
     * Processa o formulário de cadastro/edição de nota fiscal.
     */
    public function salvar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/notaFiscal');
            exit();
        }

        $dados = [
            'id' => filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: null,
            'numero' => filter_input(INPUT_POST, 'numero', FILTER_SANITIZE_SPECIAL_CHARS),
            'data_emissao' => filter_input(INPUT_POST, 'data_emissao'),
            'data_vencimento' => filter_input(INPUT_POST, 'data_vencimento'),
            'valor_total' => filter_input(INPUT_POST, 'valor_total', FILTER_VALIDATE_FLOAT),
            'cliente' => filter_input(INPUT_POST, 'cliente', FILTER_SANITIZE_SPECIAL_CHARS), // Assuming client name for now
            'status' => filter_input(INPUT_POST, 'status', FILTER_SANITIZE_SPECIAL_CHARS),
            'observacoes' => filter_input(INPUT_POST, 'observacoes', FILTER_SANITIZE_SPECIAL_CHARS),
        ];

        // Basic validation
        if (empty($dados['numero']) || empty($dados['data_emissao']) || $dados['valor_total'] === false) {
            $this->setFlashMessage('error', 'Por favor, preencha todos os campos obrigatórios.');
            header('Location: ' . BASE_URL . '/notaFiscal/form/' . ($dados['id'] ?? ''));
            exit();
        }

        if ($this->notaFiscalModel->salvarNotaFiscal($dados)) {
            $this->setFlashMessage('success', 'Nota Fiscal salva com sucesso!');
        } else {
            $this->setFlashMessage('error', 'Erro ao salvar a Nota Fiscal.');
        }

        header('Location: ' . BASE_URL . '/notaFiscal');
        exit();
    }

    /**
     * Exclui uma nota fiscal.
     * @param int $id O ID da nota fiscal a ser excluída.
     */
    public function excluir(int $id)
    {
        if ($this->notaFiscalModel->excluirNotaFiscal($id)) {
            $this->setFlashMessage('success', 'Nota Fiscal excluída com sucesso.');
        } else {
            $this->setFlashMessage('error', 'Erro ao excluir a Nota Fiscal.');
        }

        header('Location: ' . BASE_URL . '/notaFiscal');
        exit();
    }
}
