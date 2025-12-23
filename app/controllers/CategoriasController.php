<?php

namespace App\Controllers;

use App\Core\Connection;
use App\Models\ClientesModel;

class CategoriasController extends BaseController
{
    private $clientesModel;

    public function __construct()
    {
        parent::__construct();
        $this->clientesModel = new ClientesModel();
    }

    public function index()
    {
        $data = [
            'pageTitle' => 'Gerenciar Categorias e Segmentos',
            'categorias' => $this->clientesModel->getCategoriasComSegmentos(),
        ];

        $this->renderView('configuracoes/categorias', $data);
    }

    private function sendJsonResponse(bool $success, string $message)
    {
        header('Content-Type: application/json');
        echo json_encode(['success' => $success, 'message' => $message]);
        exit();
    }

    public function salvarCategoria()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendJsonResponse(false, 'Método inválido.');
        }

        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $nome = trim($_POST['nome'] ?? ''); // Usar trim para limpar espaços em branco

        if (!$id || empty($nome)) {
            $this->sendJsonResponse(false, 'Dados inválidos.');
        }

        if ($this->clientesModel->updateCategoria($id, $nome)) {
            $this->sendJsonResponse(true, 'Categoria atualizada com sucesso!');
        } else {
            $this->sendJsonResponse(false, 'Erro ao atualizar a categoria.');
        }
    }

    public function excluirCategoria(int $id)
    {
        if ($this->clientesModel->deleteCategoria($id)) {
            $this->setFlashMessage('success', 'Categoria excluída com sucesso! Clientes associados foram desvinculados e segmentos foram removidos.');
        } else {
            $this->setFlashMessage('error', 'Erro ao excluir a categoria.');
        }
        header('Location: ' . BASE_URL . '/categorias');
        exit();
    }

    public function salvarSegmento()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendJsonResponse(false, 'Método inválido.');
        }

        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $nome = trim($_POST['nome'] ?? ''); // Usar trim para limpar espaços em branco

        if (!$id || empty($nome)) {
            $this->sendJsonResponse(false, 'Dados inválidos.');
        }

        if ($this->clientesModel->updateSegmento($id, $nome)) {
            $this->sendJsonResponse(true, 'Segmento atualizado com sucesso!');
        } else {
            $this->sendJsonResponse(false, 'Erro ao atualizar o segmento.');
        }
    }

    public function excluirSegmento(int $id)
    {
        if ($this->clientesModel->deleteSegmento($id)) {
            $this->setFlashMessage('success', 'Segmento excluído com sucesso!');
        } else {
            $this->setFlashMessage('error', 'Erro ao excluir o segmento.');
        }
        header('Location: ' . BASE_URL . '/categorias');
        exit();
    }
}
