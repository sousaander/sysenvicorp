<?php

namespace App\Controllers;

use App\Core\Connection;
use App\Models\RhModel;
use App\Models\UsuarioModel;

class UsuarioController extends BaseController
{
    private $model;
    private $rhModel;

    public function __construct()
    {
        parent::__construct(); // Chama o construtor do BaseController
        $this->model = new UsuarioModel();
        $this->rhModel = new RhModel();
    }

    public function index()
    {
        // Coleta dados do modelo
        $usuarios = $this->model->getListaUsuarios();
        $perfis = $this->model->getPerfisDeAcesso();
        $cargos = $this->rhModel->getAllCargos(); // Busca a lista de cargos

        // Gera um token CSRF para os formulários da página
        $csrf_token = bin2hex(random_bytes(32));
        $this->session->set('csrf_token', $csrf_token);

        $data = [
            'pageTitle' => 'Usuários e Permissões',
            'usuarios' => $usuarios,
            'perfis' => $perfis,
            'cargos' => $cargos, // Envia os cargos para a view
            'usuarios_json' => json_encode($usuarios), // Envia os dados em JSON para o JavaScript
            'csrf_token' => $csrf_token // Envia o token para a view
        ];

        $this->renderView('usuario/index', $data);
    }

    // Simula a adição de um novo usuário
    public function novo()
    {
        // Lógica de formulário e salvamento de novo usuário
        $usuarios = $this->model->getListaUsuarios();
        $perfis = $this->model->getPerfisDeAcesso();
        $cargos = $this->rhModel->getAllCargos();

        $data = [
            'pageTitle' => 'Usuários e Permissões',
            'usuarios' => $usuarios,
            'perfis' => $perfis,
            'cargos' => $cargos,
            'showModal' => true
        ];
        $this->renderView('usuario/index', $data);
    }

    // Salva um novo usuário ou atualiza um existente
    public function salvar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/usuario');
            exit();
        }

        // Validação de CSRF para o formulário de novo usuário (apenas uma checagem)
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $this->session->get('csrf_token')) {
            $this->setFlashMessage('error', 'Erro de validação de segurança (CSRF). Por favor, tente novamente.');
            header('Location: ' . BASE_URL . '/usuario');
            exit();
        }

        $dados = $_POST;

        // Padroniza o status para ter a primeira letra maiúscula (ex: 'Ativo', 'Inativo')
        if (isset($dados['status'])) {
            $dados['status'] = ucfirst(strtolower($dados['status']));
        }

        if ($this->model->salvarUsuario($dados)) {
            $this->setFlashMessage('success', 'Usuário salvo com sucesso!');
        } else {
            $this->setFlashMessage('error', 'Erro ao salvar o usuário.');
        }

        header('Location: ' . BASE_URL . '/usuario');
        exit();
    }

    public function editar($id)
    {
        $usuario = $this->model->getUsuario($id); // Fetch the user from the database

        if ($usuario) {
            // Carrega os dados necessários para os selects do formulário
            $cargos = $this->rhModel->getAllCargos();
            $perfis = $this->model->getPerfisDeAcesso();

            $data = [
                'usuario' => $usuario,
                'cargos' => $cargos,
                'perfis' => $perfis
            ];
            // Load the edit form, passing the user data
            $this->renderView('usuario/form_edit', $data);  // Assuming you have a form_edit.php
        } else {
            // Handle the case where the user is not found (e.g., show an error message)
            $this->setFlashMessage('error', 'Usuário não encontrado.');
            header('Location: ' . BASE_URL . '/usuario');
            exit();
        }
    }

    public function atualizar($id)
    {
        // CSRF token validation
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $this->session->get('csrf_token')) {
            echo "Erro: Requisição inválida (CSRF).";
            // Em produção, seria melhor redirecionar com uma mensagem de erro
            return;
        }
        $dados = $_POST;

        // Garante que os IDs sejam tratados como inteiros
        $dados['cargo_id'] = filter_var($dados['cargo_id'], FILTER_VALIDATE_INT);
        $dados['perfil_id'] = filter_var($dados['perfil_id'], FILTER_VALIDATE_INT);

        // Padroniza o status para ter a primeira letra maiúscula
        if (isset($dados['status'])) {
            $dados['status'] = ucfirst(strtolower($dados['status']));
        }

        if ($this->model->atualizarUsuario($id, $dados)) {
            $this->setFlashMessage('success', 'Usuário atualizado com sucesso!');
        } else {
            $this->setFlashMessage('error', 'Erro ao atualizar o usuário.');
        }

        // Redirect back to the user list (or wherever you want to go)
        header('Location: ' . BASE_URL . '/usuario');
        exit();
    }

    public function toggleStatus(int $id)
    {
        $usuario = $this->model->getUsuario($id);

        if ($usuario) {
            // Alterna o status
            $novoStatus = ($usuario['status'] === 'Ativo' || $usuario['status'] === 'ativo') ? 'Inativo' : 'Ativo';

            // Atualiza o status no banco de dados
            if ($this->model->atualizarStatus($id, $novoStatus)) {
                $this->setFlashMessage('success', 'Status do usuário alterado com sucesso.');
            } else {
                $this->setFlashMessage('error', 'Erro ao alterar o status do usuário.');
            }
        } else {
            $this->setFlashMessage('error', 'Usuário não encontrado.');
        }

        header('Location: ' . BASE_URL . '/usuario');
        exit();
    }

    /**
     * Exibe a página de perfil do usuário logado com o formulário para edição.
     */
    public function perfil()
    {
        // Garante que apenas usuários logados acessem esta página
        if (!$this->session->isAuthenticated()) {
            $this->setFlashMessage('error', 'Você precisa estar logado para acessar esta página.');
            // Redireciona para a página de login e preserva a URL atual no parâmetro `next` para retorno após o login
            $redirect = '/auth/login';
            $current = $_SERVER['REQUEST_URI'] ?? null;
            if ($current) {
                $redirect .= '?next=' . urlencode($current);
            }
            header('Location: ' . BASE_URL . $redirect);
            exit();
        }

        $usuarioId = $this->session->get('user_id');
        // Usaremos o método getUsuario que já existe no seu Model
        $usuario = $this->model->getUsuario($usuarioId);

        if (!$usuario) {
            $this->setFlashMessage('error', 'Usuário não encontrado.');
            header('Location: ' . BASE_URL); // Redireciona para a página inicial
            exit();
        }

        // Gera um token CSRF para o formulário de perfil
        $csrf_token = bin2hex(random_bytes(32));
        $this->session->set('csrf_token', $csrf_token);

        $data = [
            'pageTitle' => 'Meu Perfil',
            'usuario' => $usuario,
            'csrf_token' => $csrf_token, // Envia o token para a view
        ];

        $this->renderView('usuario/perfil', $data); // Assumindo que a view está em views/usuario/perfil.php
    }

    /**
     * Processa a atualização dos dados do perfil do usuário logado.
     */
    public function salvarPerfil()
    {
        // Garante que a requisição seja POST e que o usuário esteja logado
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$this->session->isAuthenticated()) {
            header('Location: ' . BASE_URL . '/usuario/perfil');
            exit();
        }

        // Validação de CSRF para o formulário de perfil
        if (!isset($_POST['csrf_token']) || !hash_equals($this->session->get('csrf_token'), $_POST['csrf_token'])) {
            $this->setFlashMessage('error', 'Erro de validação de segurança (CSRF). Por favor, tente novamente.');
            header('Location: ' . BASE_URL . '/usuario/perfil');
            exit();
        }

        $usuarioId = $this->session->get('user_id');
        $dados = [
            'id' => $usuarioId,
            'nome' => filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_SPECIAL_CHARS),
            'email' => filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL),
            'senha' => $_POST['senha'] ?? null,
            'confirmar_senha' => $_POST['confirmar_senha'] ?? null,
        ];

        // Validação básica
        if (empty($dados['nome']) || !$dados['email']) {
            $this->setFlashMessage('error', 'Nome e e-mail são obrigatórios e o e-mail deve ser válido.');
            header('Location: ' . BASE_URL . '/usuario/perfil');
            exit();
        }

        // Validação da senha (se o usuário decidiu alterar)
        if (!empty($dados['senha'])) {
            if (strlen($dados['senha']) < 6) {
                $this->setFlashMessage('error', 'A nova senha deve ter no mínimo 6 caracteres.');
                header('Location: ' . BASE_URL . '/usuario/perfil');
                exit();
            }
            if ($dados['senha'] !== $dados['confirmar_senha']) {
                $this->setFlashMessage('error', 'As senhas não coincidem.');
                header('Location: ' . BASE_URL . '/usuario/perfil');
                exit();
            }
        }

        // Tratamento de upload/remoção de foto de perfil
        $storageDir = ROOT_PATH . '/storage/users';
        if (!is_dir($storageDir)) {
            @mkdir($storageDir, 0755, true);
        }

        // Remover foto se solicitado
        if (!empty($_POST['remover_foto'])) {
            $possibleExt = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
            foreach ($possibleExt as $ext) {
                $f = $storageDir . "/user_{$usuarioId}." . $ext;
                if (file_exists($f)) {
                    @unlink($f);
                }
            }
        }

        // Processa novo upload
        if (!empty($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['foto'];
            $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
            if (!array_key_exists($file['type'], $allowed)) {
                $this->setFlashMessage('error', 'Tipo de imagem não suportado. Use JPEG, PNG, GIF ou WEBP.');
                header('Location: ' . BASE_URL . '/usuario/perfil');
                exit();
            }
            if ($file['size'] > 2 * 1024 * 1024) { // 2MB
                $this->setFlashMessage('error', 'A imagem deve ter no máximo 2MB.');
                header('Location: ' . BASE_URL . '/usuario/perfil');
                exit();
            }

            $ext = $allowed[$file['type']];
            $dest = $storageDir . "/user_{$usuarioId}." . $ext;

            // Remove versões anteriores com outras extensões
            foreach (['jpg', 'jpeg', 'png', 'webp', 'gif'] as $e) {
                $old = $storageDir . "/user_{$usuarioId}." . $e;
                if (file_exists($old) && $old !== $dest) {
                    @unlink($old);
                }
            }

            if (!move_uploaded_file($file['tmp_name'], $dest)) {
                $this->setFlashMessage('error', 'Erro ao salvar a imagem de perfil. Tente novamente.');
                header('Location: ' . BASE_URL . '/usuario/perfil');
                exit();
            }
        }

        if ($this->model->atualizarPerfil($dados)) {
            $this->setFlashMessage('success', 'Perfil atualizado com sucesso!');
        } else {
            $this->setFlashMessage('error', 'Ocorreu um erro ao atualizar o perfil. Verifique se o e-mail já não está em uso por outro usuário.');
        }

        header('Location: ' . BASE_URL . '/usuario/perfil');
        exit();
    }
}
