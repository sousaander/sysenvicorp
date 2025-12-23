<?php

namespace App\Controllers;

use App\Core\Connection;
use App\Models\UsuarioModel;

class AuthController extends BaseController
{
    private $usuarioModel;

    public function __construct()
    {
        parent::__construct();
        $this->usuarioModel = new UsuarioModel();
    }

    /**
     * Exibe a página de login.
     */
    public function login()
    {
        // Se o usuário já estiver logado, redireciona para o dashboard
        if ($this->session->isAuthenticated()) {
            header('Location: ' . BASE_URL . '/');
            exit();
        }

        // Garante que um token CSRF seja gerado e passado para a view de login
        $csrf_token = bin2hex(random_bytes(32));
        $this->session->set('csrf_token', $csrf_token);

        // Preserva parâmetro `next` se fornecido para redirecionar após o login
        $next = $_GET['next'] ?? null;

        $data = [
            'pageTitle' => 'Login - SysEnviCorp',
            'csrf_token' => $csrf_token,
            'next' => $next,
        ];

        // Renderiza a view de login sem o template principal
        $this->renderPartial('auth/login', $data);
    }

    /**
     * Processa a submissão do formulário de login.
     */
    public function processLogin()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/auth/login');
            exit();
        }

        // Validação de CSRF
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $this->session->get('csrf_token')) {
            $this->setFlashMessage('error', 'Erro de validação de segurança. Tente novamente.');
            header('Location: ' . BASE_URL . '/auth/login');
            exit();
        }

        $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        $senha = $_POST['senha'] ?? '';

        if (!$email || empty($senha)) {
            $this->setFlashMessage('error', 'E-mail ou senha inválidos.');
            header('Location: ' . BASE_URL . '/auth/login');
            exit();
        }

        // Verifica as credenciais
        $usuario = $this->usuarioModel->findByEmail($email);
        $next = $_POST['next'] ?? null;

        if ($usuario && password_verify($senha, $usuario['senha_hash'])) {
            // Credenciais corretas, inicia a sessão
            $this->session->login($usuario['id'], $usuario['nome']); // Armazena ID e nome
            $this->setFlashMessage('success', 'Login realizado com sucesso!');

            // Valida redirecionamento interno para evitar open redirects
            if ($next && strpos($next, '/') === 0 && strpos($next, '//') === false) {
                header('Location: ' . BASE_URL . $next);
            } else {
                header('Location: ' . BASE_URL . '/'); // Redireciona para o Dashboard
            }
            exit();
        } else {
            // Credenciais incorretas
            $this->setFlashMessage('error', 'E-mail ou senha incorretos.');
            $redirect = '/auth/login';
            if ($next) {
                $redirect .= '?next=' . urlencode($next);
            }
            header('Location: ' . BASE_URL . $redirect);
            exit();
        }
    }

    /**
     * Realiza o logout do usuário.
     */
    public function logout()
    {
        $this->session->destroy();
        // Não precisa de flash message aqui, apenas redireciona
        header('Location: ' . BASE_URL . '/auth/login');
        exit();
    }

    /**
     * Exibe a página de registro.
     */
    public function register()
    {
        // Se o usuário já estiver logado, redireciona para o dashboard
        if ($this->session->isAuthenticated()) {
            header('Location: ' . BASE_URL . '/');
            exit();
        }

        // Garante que um token CSRF seja gerado e passado para a view de registro
        $csrf_token = bin2hex(random_bytes(32));
        $this->session->set('csrf_token', $csrf_token);

        $data = [
            'pageTitle' => 'Cadastro - SysEnviCorp',
            'csrf_token' => $csrf_token,
        ];

        // Renderiza a view de registro sem o template principal
        $this->renderPartial('auth/register', $data);
    }

    /**
     * Processa a submissão do formulário de registro.
     */
    public function processRegister()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/auth/register');
            exit();
        }

        // Validação de CSRF
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $this->session->get('csrf_token')) {
            $this->setFlashMessage('error', 'Erro de validação de segurança. Tente novamente.');
            header('Location: ' . BASE_URL . '/auth/register');
            exit();
        }

        $nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_SPECIAL_CHARS);
        $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        $senha = $_POST['senha'] ?? '';
        $senha_confirm = $_POST['senha_confirm'] ?? '';

        // Validações
        if (!$nome || !$email || empty($senha) || $senha !== $senha_confirm) {
            $this->setFlashMessage('error', 'Dados inválidos ou senhas não conferem.');
            header('Location: ' . BASE_URL . '/auth/register');
            exit();
        }

        if (strlen($senha) < 6) {
            $this->setFlashMessage('error', 'A senha deve ter no mínimo 6 caracteres.');
            header('Location: ' . BASE_URL . '/auth/register');
            exit();
        }

        // Verifica se o e-mail já existe
        if ($this->usuarioModel->findByEmail($email)) {
            $this->setFlashMessage('error', 'Este e-mail já está cadastrado.');
            header('Location: ' . BASE_URL . '/auth/register');
            exit();
        }

        // Cria o usuário
        if ($this->usuarioModel->createUser($nome, $email, $senha)) {
            $this->setFlashMessage('success', 'Cadastro realizado com sucesso! Faça login para continuar.');
            header('Location: ' . BASE_URL . '/auth/login');
        } else {
            $this->setFlashMessage('error', 'Ocorreu um erro ao realizar o cadastro. Tente novamente.');
            header('Location: ' . BASE_URL . '/auth/register');
        }
        exit();
    }
}
