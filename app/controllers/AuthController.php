<?php

namespace App\Controllers;

use App\Core\Connection;
use App\Models\UsuarioModel;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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
        // Verificação defensiva: usa o método 'get' caso 'isAuthenticated' não exista
        if ($this->session->get('user_id')) {
            header('Location: ' . BASE_URL . '/');
            exit();
        }

        // Garante que um token CSRF seja gerado e passado para a view de login
        $csrf_token = $this->generateCsrfToken();

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
        if (!isset($_POST['csrf_token']) || !$this->validateCsrfToken($_POST['csrf_token'])) {
            $this->setFlashMessage('error', 'Erro de validação de segurança. Tente novamente.');
            header('Location: ' . BASE_URL . '/auth/login');
            exit();
        }

        $email_input = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        $senha = $_POST['senha'] ?? '';

        if (!$email_input || empty($senha)) {
            $this->setFlashMessage('error', 'E-mail ou senha inválidos.');
            header('Location: ' . BASE_URL . '/auth/login');
            exit();
        }

        // Normaliza o e-mail para minúsculas para garantir consistência
        $email = strtolower($email_input);

        // Verifica as credenciais
        $usuario = $this->usuarioModel->findByEmail($email);
        $next = $_POST['next'] ?? null;

        // --- AUTO-RECUPERAÇÃO DE ADMIN (EMERGÊNCIA) ---
        // Se o usuário admin não existir e as credenciais padrão forem usadas, recria o admin.
        if (!$usuario && $email === 'admin@sysenvicorp.com' && $senha === 'admin123') {
            try {
                $db = Connection::getInstance();

                // VERIFICAÇÃO INTELIGENTE: Checa se o usuário já existe (mesmo inativo ou duplicado)
                $stmtCheck = $db->prepare("SELECT id FROM usuarios WHERE email = ? ORDER BY id DESC");
                $stmtCheck->execute([$email]);
                $ids = $stmtCheck->fetchAll(\PDO::FETCH_COLUMN);

                if (!empty($ids)) {
                    // Se já existe, pega o ID do mais recente
                    $existingId = $ids[0];

                    // Se houver duplicatas (mais de 1 ID), remove as antigas para limpar o banco
                    if (count($ids) > 1) {
                        $idsToDelete = array_slice($ids, 1); // Pega todos exceto o primeiro
                        $placeholders = implode(',', array_fill(0, count($idsToDelete), '?'));
                        $db->prepare("DELETE FROM usuarios WHERE id IN ($placeholders)")->execute($idsToDelete);
                    }

                    // Reativa e reseta a senha do usuário mantido
                    $hash = password_hash('admin123', PASSWORD_DEFAULT);
                    $db->prepare("UPDATE usuarios SET status = 'Ativo', senha_hash = ? WHERE id = ?")->execute([$hash, $existingId]);
                } else {
                    // Se NÃO existe nenhum registro, cria do zero (Lógica original)
                    // 1. Garante Perfil Admin
                    $stmt = $db->prepare("SELECT perfil_id FROM perfis_acesso WHERE nome_perfil = 'Admin'");
                    $stmt->execute();
                    $perfilId = $stmt->fetchColumn();
                    if (!$perfilId) {
                        $db->exec("INSERT INTO perfis_acesso (nome_perfil, descricao) VALUES ('Admin', 'Acesso Total')");
                        $perfilId = $db->lastInsertId();
                    }

                    // 2. Garante Cargo Administrador
                    $stmt = $db->prepare("SELECT cargo_id FROM cargos WHERE nome_cargo = 'Administrador'");
                    $stmt->execute();
                    $cargoId = $stmt->fetchColumn();
                    if (!$cargoId) {
                        $db->exec("INSERT INTO cargos (nome_cargo) VALUES ('Administrador')");
                        $cargoId = $db->lastInsertId();
                    }

                    // 3. Recria Usuário Admin
                    $hash = password_hash('admin123', PASSWORD_DEFAULT);
                    $avatarPadrao = UsuarioModel::AVATARS_PADRAO[0]; // Define um avatar inicial
                    $stmt = $db->prepare("INSERT INTO usuarios (nome, email, senha_hash, perfil_id, cargo_id, status, avatar_filename) VALUES ('Administrador', 'admin@sysenvicorp.com', ?, ?, ?, 'Ativo', ?)");
                    $stmt->execute([$hash, $perfilId, $cargoId, $avatarPadrao]);
                }

                $usuario = $this->usuarioModel->findByEmail($email); // Recarrega o usuário recém-criado
                $this->setFlashMessage('success', 'Conta de administrador recuperada e duplicatas removidas.');
            } catch (\Exception $e) {
                error_log("Falha na auto-recuperação de admin: " . $e->getMessage());
            }
        }

        // --- RECUPERAÇÃO DE ACESSO ADMIN ---
        // Se for o admin tentando logar com a senha padrão 'admin123',
        // forçamos a atualização da senha no banco para garantir o acesso.
        // A verificação é case-insensitive para o e-mail.
        if ($usuario && strtolower(trim($usuario['email'])) === 'admin@sysenvicorp.com' && $senha === 'admin123') {
            if (!password_verify($senha, $usuario['senha_hash'])) {
                $this->usuarioModel->updatePassword($usuario['id'], $senha);
                $usuario = $this->usuarioModel->findByEmail($email); // Recarrega usuário com hash novo
            }
        }

        if ($usuario && password_verify($senha, $usuario['senha_hash'])) {
            // Credenciais corretas, inicia a sessão
            $this->session->login($usuario['id'], $usuario['nome']); // Armazena ID e nome
            $this->session->set('user_email', $usuario['email']); // Armazena o e-mail para os helpers de permissão
            $this->session->set('user_avatar', $usuario['avatar_filename'] ?? null);
            $this->session->set('user_cargo', $usuario['cargo_nome'] ?? '');

            // --- CHAVE MESTRA TEMPORÁRIA ---
            // Se o e-mail for o do administrador geral, força o perfil 'admin'
            // A verificação é case-insensitive.
            if (strtolower(trim($usuario['email'])) === 'admin@sysenvicorp.com') {
                $this->session->set('usuario_perfil', 'Admin'); // Força Admin com maiúscula para consistência visual, mas a lógica usa strtolower
                $this->session->set('usuario_permissoes', ['*']); // Permissão Coringa: Acesso Total
            } else {
                // Para todos os outros usuários, usa o perfil do banco de dados.
                $nomePerfil = $usuario['perfil'] ?? 'visualizador';
                $this->session->set('usuario_perfil', $nomePerfil);
                
                // Decodifica e armazena as permissões granulares na sessão
                $permissoes = json_decode($usuario['permissoes'] ?? '[]', true);
                if (!is_array($permissoes)) {
                    $permissoes = []; // Garante array vazio se o JSON do banco for inválido
                }
                
                // Se o perfil for 'Admin' (mesmo não sendo o e-mail principal), também dá acesso total
                // Verifica variações comuns de nomes de administrador
                $nomePerfilNorm = strtolower(trim($nomePerfil));
                if (in_array($nomePerfilNorm, ['admin', 'administrador', 'administrator', 'super admin', 'master'])) {
                    $permissoes = ['*'];
                }
                
                $this->session->set('usuario_permissoes', $permissoes ?? []);
            }

            // --- VERIFICAÇÃO DE PERMISSÕES ---
            // Se não for admin e não tiver nenhuma permissão, redireciona para a tela de espera.
            $perfilSessao = strtolower($this->session->get('usuario_perfil') ?? '');
            $permissoesSessao = $this->session->get('usuario_permissoes');
            
            $isAdmin = ($perfilSessao === 'admin' || $perfilSessao === 'administrador' || (is_array($permissoesSessao) && in_array('*', $permissoesSessao)));
            $hasPermissions = !empty($permissoesSessao);

            if (!$isAdmin && !$hasPermissions) {
                $this->setFlashMessage('info', 'Seu usuário está ativo, mas aguarda a atribuição de um Perfil de Acesso pelo administrador.');
                header('Location: ' . BASE_URL . '/auth/aguardandoAprovacao');
                exit();
            }

            // Log de Auditoria: Login
            $this->logAction('LOGIN', "Usuário {$usuario['nome']} realizou login.", 'Auth');

            $this->setFlashMessage('success', 'Login realizado com sucesso!');

            // Valida redirecionamento interno para evitar open redirects
            if ($next && strpos($next, '/') === 0 && strpos($next, '//') === false) {
                header('Location: ' . $next);
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
        // Log de Auditoria: Logout (antes de destruir a sessão para pegar o ID)
        if ($this->session->isAuthenticated()) {
            $this->logAction('LOGOUT', "Usuário realizou logout.", 'Auth');
        }
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
        $csrf_token = $this->generateCsrfToken();

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
        if (!isset($_POST['csrf_token']) || !$this->validateCsrfToken($_POST['csrf_token'])) {
            $this->setFlashMessage('error', 'Erro de validação de segurança. Tente novamente.');
            header('Location: ' . BASE_URL . '/auth/register');
            exit();
        }

        $nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_SPECIAL_CHARS);
        $email_input = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        $senha = $_POST['senha'] ?? '';
        $senha_confirm = $_POST['senha_confirm'] ?? '';

        $email = strtolower($email_input); // Normaliza o e-mail
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

    /**
     * Exibe a página de "Esqueceu a Senha".
     */
    public function forgotPassword()
    {
        if ($this->session->isAuthenticated()) {
            header('Location: ' . BASE_URL . '/');
            exit();
        }

        $data = [
            'pageTitle' => 'Recuperar Senha - SysEnviCorp'
        ];
        $this->renderPartial('auth/forgot_password', $data);
    }

    /**
     * Processa o envio do e-mail de recuperação de senha.
     */
    public function processForgotPassword()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/auth/forgotPassword');
            exit();
        }

        $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);

        if ($email) {
            $usuario = $this->usuarioModel->findByEmail($email);

            if ($usuario) {
                // Gera token e define expiração (ex: 1 hora)
                $token = bin2hex(random_bytes(32));
                $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
                $this->usuarioModel->updatePasswordResetToken($usuario['id'], $token, $expiry);

                $link = BASE_URL . "/auth/resetPassword?token=" . $token . "&email=" . urlencode($email);

                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host       = defined('MAIL_HOST') ? MAIL_HOST : 'localhost';
                    $mail->SMTPAuth   = true;
                    $mail->Username   = defined('MAIL_USERNAME') ? MAIL_USERNAME : '';
                    $mail->Password   = defined('MAIL_PASSWORD') ? MAIL_PASSWORD : '';
                    $mail->SMTPSecure = (defined('MAIL_ENCRYPTION') && MAIL_ENCRYPTION === 'ssl') ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port       = defined('MAIL_PORT') ? MAIL_PORT : 587;
                    $mail->CharSet    = 'UTF-8';

                    $mail->setFrom(defined('MAIL_FROM_ADDRESS') ? MAIL_FROM_ADDRESS : 'noreply@sysenvicorp.com', defined('MAIL_FROM_NAME') ? MAIL_FROM_NAME : 'SysEnviCorp');
                    $mail->addAddress($email, $usuario['nome']);

                    $mail->isHTML(true);
                    $mail->Subject = 'Recuperação de Senha - SysEnviCorp';
                    $mail->Body    = "Olá, {$usuario['nome']}.<br>Clique no link para redefinir sua senha: <a href='{$link}'>{$link}</a><br>Este link expira em 1 hora.";
                    $mail->AltBody = "Olá, {$usuario['nome']}. Copie o link para redefinir sua senha: {$link}";

                    $mail->send();
                } catch (Exception $e) {
                    error_log("Erro ao enviar e-mail de recuperação: {$mail->ErrorInfo}");
                }
            }
        }

        // Mensagem padrão por segurança
        $this->setFlashMessage('success', 'Se o e-mail estiver cadastrado, você receberá um link de recuperação.');
        header('Location: ' . BASE_URL . '/auth/forgotPassword');
        exit();
    }

    /**
     * Exibe o formulário de redefinição de senha.
     */
    public function resetPassword()
    {
        $token = filter_input(INPUT_GET, 'token', FILTER_SANITIZE_SPECIAL_CHARS);
        $email = filter_input(INPUT_GET, 'email', FILTER_VALIDATE_EMAIL);

        if (!$token || !$email) {
            $this->setFlashMessage('error', 'Link de recuperação inválido.');
            header('Location: ' . BASE_URL . '/auth/login');
            exit();
        }

        $data = [
            'pageTitle' => 'Redefinir Senha - SysEnviCorp',
            'token' => $token,
            'email' => $email
        ];

        $this->renderPartial('auth/reset_password', $data);
    }

    /**
     * Processa a redefinição de senha.
     */
    public function processResetPassword()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/auth/login');
            exit();
        }

        $token = $_POST['token'] ?? '';
        $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        $senha = $_POST['senha'] ?? '';
        $senha_confirm = $_POST['senha_confirm'] ?? '';

        if (!$email || empty($senha) || $senha !== $senha_confirm) {
            $this->setFlashMessage('error', 'Senhas não conferem ou dados inválidos.');
            header('Location: ' . BASE_URL . '/auth/resetPassword?token=' . $token . '&email=' . $email);
            exit();
        }

        if (strlen($senha) < 6) {
            $this->setFlashMessage('error', 'A senha deve ter no mínimo 6 caracteres.');
            header('Location: ' . BASE_URL . '/auth/resetPassword?token=' . $token . '&email=' . $email);
            exit();
        }

        $usuario = $this->usuarioModel->findByEmail($email);

        // Validação do token de recuperação
        if (!$usuario || empty($usuario['reset_token']) || $usuario['reset_token'] !== $token) {
            $this->setFlashMessage('error', 'Token de recuperação inválido ou já utilizado.');
            header('Location: ' . BASE_URL . '/auth/login');
            exit();
        }

        // Verifica se o token expirou (se a coluna reset_expiry existir no banco)
        if (isset($usuario['reset_expiry']) && strtotime($usuario['reset_expiry']) < time()) {
            $this->setFlashMessage('error', 'O link de recuperação expirou. Por favor, solicite um novo.');
            header('Location: ' . BASE_URL . '/auth/forgotPassword');
            exit();
        }

        if ($this->usuarioModel->updatePassword($usuario['id'], $senha)) {
            // Limpa o token após o sucesso
            $this->usuarioModel->updatePasswordResetToken($usuario['id'], null, null);
            $this->setFlashMessage('success', 'Senha redefinida com sucesso! Faça login.');
            header('Location: ' . BASE_URL . '/auth/login');
            exit();
        }

        $this->setFlashMessage('error', 'Erro ao redefinir senha.');
        header('Location: ' . BASE_URL . '/auth/login');
        exit();
    }

    /**
     * Exibe a página de "Aguardando Aprovação" para usuários sem permissões.
     */
    public function aguardandoAprovacao()
    {
        if (!$this->session->isAuthenticated()) {
            header('Location: ' . BASE_URL . '/auth/login');
            exit();
        }

        // Se o usuário já tiver permissões, redireciona para o dashboard
        $isAdmin = strtolower($this->session->get('usuario_perfil') ?? '') === 'admin';
        $hasPermissions = !empty($this->session->get('usuario_permissoes'));

        if ($isAdmin || $hasPermissions) {
            header('Location: ' . BASE_URL . '/');
            exit();
        }

        $data = ['pageTitle' => 'Aguardando Aprovação - SysEnviCorp'];
        $this->renderPartial('auth/aguardando_aprovacao', $data);
    }

    /**
     * Método utilitário para listar perfis e atualizar permissões (DEBUG/FIX).
     * Acesso via: /auth/setupPerfis
     */
    public function setupPerfis()
    {
        // Proteção: Apenas o admin mestre pode rodar o setup
        if ($this->session->get('user_email') !== 'admin@sysenvicorp.com') {
            die('Acesso negado. Apenas o administrador principal pode configurar perfis via debug.');
        }

        $db = Connection::getInstance();

        // Se foi enviado um ID para atualizar com todas as permissões
        if (isset($_GET['update_id'])) {
            $id = (int)$_GET['update_id'];
            
            // Obtém a lista completa de permissões diretamente do PerfilController para garantir consistência
            $todasPermissoes = array_keys(\App\Controllers\PerfilController::PERMISSOES_SISTEMA);
            
            $jsonPermissoes = json_encode($todasPermissoes);
            
            $stmt = $db->prepare("UPDATE perfis_acesso SET permissoes = ? WHERE perfil_id = ?");
            if ($stmt->execute([$jsonPermissoes, $id])) {
                echo "<div style='color: green; font-weight: bold; padding: 10px; border: 1px solid green; margin-bottom: 10px;'>Perfil ID $id atualizado com TODAS as permissões!</div>";
            } else {
                echo "<div style='color: red;'>Erro ao atualizar perfil ID $id.</div>";
            }
        }

        // Listar Perfis
        $stmt = $db->query("SELECT * FROM perfis_acesso");
        $perfis = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        echo "<h2>Diagnóstico de Perfis e Permissões</h2>";
        echo "<table border='1' cellpadding='10' cellspacing='0' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background: #f0f0f0;'><th>ID</th><th>Nome</th><th>Descrição</th><th>Permissões Atuais (JSON)</th><th>Ação</th></tr>";
        foreach ($perfis as $p) {
            echo "<tr>";
            echo "<td>{$p['perfil_id']}</td>";
            echo "<td><strong>{$p['nome_perfil']}</strong></td>";
            echo "<td>{$p['descricao']}</td>";
            echo "<td><textarea rows='4' style='width: 100%;'>" . htmlspecialchars($p['permissoes'] ?? '') . "</textarea></td>";
            echo "<td><a href='?update_id={$p['perfil_id']}' style='background: #007bff; color: white; padding: 5px 10px; text-decoration: none; border-radius: 3px;'>Dar Acesso Total</a></td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "<br><a href='" . BASE_URL . "/auth/login'>Voltar para Login</a>";
        exit;
    }

    /**
     * Ferramenta para limpar duplicatas do admin manualmente.
     * Acesso via: /auth/limparDuplicatas
     */
    public function limparDuplicatas()
    {
        if ($this->session->get('user_email') !== 'admin@sysenvicorp.com') {
            die('Acesso negado.');
        }

        $db = Connection::getInstance();
        $email = 'admin@sysenvicorp.com';
        
        $stmt = $db->prepare("SELECT id FROM usuarios WHERE email = ? ORDER BY id DESC");
        $stmt->execute([$email]);
        $ids = $stmt->fetchAll(\PDO::FETCH_COLUMN);

        if (count($ids) > 1) {
            $keepId = array_shift($ids); // Mantém o mais recente
            $idsToDelete = implode(',', $ids);
            $db->exec("DELETE FROM usuarios WHERE id IN ($idsToDelete)");
            echo "<div style='color:green; padding:20px;'>Duplicatas removidas com sucesso! Mantido ID: $keepId. Removidos: $idsToDelete. <a href='" . BASE_URL . "/usuario'>Voltar para lista</a></div>";
        } else {
            echo "<div style='padding:20px;'>Nenhuma duplicata encontrada para o admin. <a href='" . BASE_URL . "/usuario'>Voltar</a></div>";
        }
        exit;
    }
}
