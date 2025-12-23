<?php

namespace App\Controllers;

use App\Core\Connection;
use App\Models\PerfilModel;

class PerfilController extends BaseController
{
    private $perfilModel;

    public function __construct()
    {
        parent::__construct();
        $this->perfilModel = new PerfilModel();
    }

    /**
     * Exibe a lista de perfis de acesso cadastrados.
     */
    public function index()
    {
        $perfis = $this->perfilModel->getAll();

        // Gera um token CSRF para os formulários da página
        $csrf_token = bin2hex(random_bytes(32));
        $this->session->set('csrf_token', $csrf_token);

        $data = [
            'pageTitle' => 'Gerenciar Perfis de Acesso',
            'perfis' => $perfis,
            'perfis_json' => json_encode($perfis), // Envia os dados em JSON para o JavaScript
            'csrf_token' => $csrf_token, // Envia o token para a view
        ];

        $this->renderView('perfil/index', $data);
    }

    /**
     * Exibe o formulário para um novo perfil ou para edição de um existente.
     * @param int|null $id O ID do perfil para edição.
     */
    public function form(int $id = null)
    {
        $perfil = null;
        if ($id) {
            $perfil = $this->perfilModel->getById($id);
            if (!$perfil) {
                $this->setFlashMessage('error', 'Perfil não encontrado.');
                header('Location: ' . BASE_URL . '/perfil');
                exit();
            }
        }

        $data = [
            'pageTitle' => $id ? 'Editar Perfil de Acesso' : 'Novo Perfil de Acesso',
            'perfil' => $perfil,
        ];

        $this->renderView('perfil/form', $data);
    }

    /**
     * Processa o formulário de cadastro/edição.
     */
    public function salvar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/perfil');
            exit();
        }

        $dados = [
            'id' => filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: null,
            'nome_perfil' => filter_input(INPUT_POST, 'nome_perfil', FILTER_SANITIZE_SPECIAL_CHARS),
            // CORREÇÃO: Usar htmlspecialchars manualmente para preservar quebras de linha.
            // filter_input com FILTER_SANITIZE_*_CHARS converte as quebras de linha, o que é indesejado.
            // htmlspecialchars é mais seguro e não afeta \r\n.
            'descricao' => isset($_POST['descricao']) ? htmlspecialchars($_POST['descricao'], ENT_QUOTES, 'UTF-8') : null,
        ];

        if ($this->perfilModel->salvar($dados)) {
            $this->setFlashMessage('success', 'Perfil salvo com sucesso!');
        } else {
            $this->setFlashMessage('error', 'Erro ao salvar o perfil.');
        }

        header('Location: ' . BASE_URL . '/perfil');
        exit();
    }

    /**
     * Exclui um perfil (apenas via POST com CSRF token).
     */
    public function excluir($id = null)
    {
        // Somente POST para exclusão
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->setFlashMessage('error', 'Operação inválida. Use o botão de exclusão.');
            header('Location: ' . BASE_URL . '/perfil');
            exit();
        }

        // Valida CSRF
        $token = $_POST['csrf_token'] ?? '';
        if (empty($token) || $token !== $this->session->get('csrf_token')) {
            $this->setFlashMessage('error', 'Token CSRF inválido.');
            header('Location: ' . BASE_URL . '/perfil');
            exit();
        }

        $postedId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        if (!$postedId) {
            $this->setFlashMessage('error', 'ID inválido.');
            header('Location: ' . BASE_URL . '/perfil');
            exit();
        }

        $this->perfilModel->excluir($postedId);
        $this->setFlashMessage('success', 'Perfil excluído com sucesso.');
        header('Location: ' . BASE_URL . '/perfil');
        exit();
    }
}
