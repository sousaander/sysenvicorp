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
            'nome_titular' => filter_input(INPUT_POST, 'nome_titular', FILTER_SANITIZE_SPECIAL_CHARS),
            'tipo' => filter_input(INPUT_POST, 'tipo', FILTER_SANITIZE_SPECIAL_CHARS) ?: 'corrente',
            'banco_codigo' => filter_input(INPUT_POST, 'banco_codigo', FILTER_SANITIZE_SPECIAL_CHARS),
            'agencia' => filter_input(INPUT_POST, 'agencia', FILTER_SANITIZE_SPECIAL_CHARS),
            'agencia_dv' => filter_input(INPUT_POST, 'agencia_dv', FILTER_SANITIZE_SPECIAL_CHARS),
            'conta' => filter_input(INPUT_POST, 'conta', FILTER_SANITIZE_SPECIAL_CHARS),
            'conta_dv' => filter_input(INPUT_POST, 'conta_dv', FILTER_SANITIZE_SPECIAL_CHARS),
            'pix_tipo' => filter_input(INPUT_POST, 'pix_tipo', FILTER_SANITIZE_SPECIAL_CHARS),
            'pix_chave' => filter_input(INPUT_POST, 'pix_chave', FILTER_SANITIZE_SPECIAL_CHARS),
            'saldo_inicial' => filter_input(INPUT_POST, 'saldo_inicial', FILTER_VALIDATE_FLOAT, FILTER_FLAG_ALLOW_FRACTION),
            'limite_credito' => filter_input(INPUT_POST, 'limite_credito', FILTER_VALIDATE_FLOAT, FILTER_FLAG_ALLOW_FRACTION) ?: 0.00,
            'cor' => filter_input(INPUT_POST, 'cor', FILTER_SANITIZE_SPECIAL_CHARS) ?: '#10b981',
            'ativo' => isset($_POST['ativo']) ? 1 : 0,
            'observacoes' => filter_input(INPUT_POST, 'observacoes', FILTER_SANITIZE_SPECIAL_CHARS),
        ];

        if (empty($dados['nome']) || $dados['saldo_inicial'] === false) {
            $this->setFlashMessage('error', 'Dados inválidos. Nome e Saldo Inicial são obrigatórios.');
            header('Location: ' . BASE_URL . '/banco/form/' . ($dados['id'] ?? ''));
            exit();
        }

        // Lógica de upload de logo
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            // Validação de arquivo
            $maxSize = 5 * 1024 * 1024; // 5MB
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            
            if ($_FILES['logo']['size'] > $maxSize) {
                $this->setFlashMessage('error', 'Arquivo de logo muito grande. Máximo: 5MB.');
                header('Location: ' . BASE_URL . '/banco/form/' . ($dados['id'] ?? ''));
                exit();
            }

            $ext = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowedExts)) {
                $this->setFlashMessage('error', 'Tipo de arquivo não permitido. Use: PNG, JPG, GIF, WebP.');
                header('Location: ' . BASE_URL . '/banco/form/' . ($dados['id'] ?? ''));
                exit();
            }

            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $_FILES['logo']['tmp_name']);
            finfo_close($finfo);
            
            if (!in_array($mimeType, $allowedTypes)) {
                $this->setFlashMessage('error', 'Tipo de arquivo inválido. O arquivo não é uma imagem válida.');
                header('Location: ' . BASE_URL . '/banco/form/' . ($dados['id'] ?? ''));
                exit();
            }

            $uploadDir = ROOT_PATH . '/public/uploads/bancos/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0775, true);
            }

            // Se for edição, deletar logo antiga
            if ($dados['id']) {
                $bancoAntigo = $this->bancoModel->getById($dados['id']);
                if ($bancoAntigo && !empty($bancoAntigo['logo'])) {
                    $logoAntiga = $uploadDir . $bancoAntigo['logo'];
                    if (file_exists($logoAntiga)) {
                        unlink($logoAntiga);
                    }
                }
            }

            $newFilename = 'banco_' . uniqid() . '.' . $ext;
            if (move_uploaded_file($_FILES['logo']['tmp_name'], $uploadDir . $newFilename)) {
                $dados['logo'] = $newFilename;
            } else {
                $this->setFlashMessage('error', 'Erro ao fazer upload da logo. Tente novamente.');
                header('Location: ' . BASE_URL . '/banco/form/' . ($dados['id'] ?? ''));
                exit();
            }
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
