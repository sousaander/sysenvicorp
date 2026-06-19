<?php

namespace App\Controllers;

use App\Core\Connection;
use App\Models\RhModel;
use App\Models\UsuarioModel;

class UsuarioController extends BaseController
{
    private $model;
    private $rhModel;

    /**
     * Mapeia ações para as permissões necessárias.
     * O BaseController usará este mapa para verificar o acesso.
     * @var array
     */
    protected $requiredPermissions = [
        'index' => 'config_usuarios_view',
        'novo' => 'config_usuarios_manage',
        'salvar' => 'config_usuarios_manage',
        'editar' => 'config_usuarios_manage',
        'atualizar' => 'config_usuarios_manage',
        'resetPassword' => 'config_usuarios_manage',
        'toggleStatus' => 'config_usuarios_manage',
        'excluir' => 'config_usuarios_delete',
    ];

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

        $data = [
            'pageTitle' => 'Usuários e Permissões',
            'usuarios' => $usuarios,
            'perfis' => $perfis,
            'cargos' => $cargos, // Envia os cargos para a view
            'usuarios_json' => json_encode($usuarios), // Envia os dados em JSON para o JavaScript
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
        if (!isset($_POST['csrf_token']) || !$this->validateCsrfToken($_POST['csrf_token'])) {
            $this->setFlashMessage('error', 'Erro de validação de segurança (CSRF). Por favor, tente novamente.');
            header('Location: ' . BASE_URL . '/usuario');
            exit();
        }

        // Coleta dados diretamente do POST para evitar problemas com filter_input
        // CORREÇÃO: Verifica se há um ID. Se houver, é uma edição, não criação.
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        if ($id && $id > 0) {
            return $this->atualizar($id);
        }

        $nome = trim($_POST['nome'] ?? '');
        $email = strtolower(trim($_POST['email'] ?? '')); // Normaliza para minúsculas
        $cargoId = $_POST['cargo_id'] ?? $_POST['cargo'] ?? '';
        $perfilId = $_POST['perfil_id'] ?? $_POST['perfil'] ?? '';
        $status = $_POST['status'] ?? 'Ativo';

        // Verifica se os dados obrigatórios estão preenchidos
        // Alterado de empty() para verificação estrita para aceitar valor '0' e identificar qual campo falta
        if ($nome === '' || $email === '' || $cargoId === '' || $perfilId === '') {
            $campos = [];
            if ($nome === '') $campos[] = 'Nome';
            if ($email === '') $campos[] = 'E-mail';
            if ($cargoId === '') $campos[] = 'Cargo';
            if ($perfilId === '') $campos[] = 'Perfil';

            $this->setFlashMessage('error', 'Erro ao salvar o usuário. Campos obrigatórios faltando: ' . implode(', ', $campos));
            header('Location: ' . BASE_URL . '/usuario');
            exit();
        }

        $dados = [
            'nome' => htmlspecialchars($nome, ENT_QUOTES, 'UTF-8'),
            'email' => filter_var($email, FILTER_VALIDATE_EMAIL),
            'cargo_id' => (int)$cargoId,
            'perfil_id' => (int)$perfilId,
            'status' => ucfirst(strtolower($status)),
        ];

        if (!$dados['email']) {
            $this->setFlashMessage('error', 'O e-mail informado é inválido.');
            header('Location: ' . BASE_URL . '/usuario');
            exit();
        }

        if ($this->model->salvarUsuario($dados)) {
            $this->setFlashMessage('success', 'Usuário salvo com sucesso!');
            $this->logAction('CREATE', "Criou novo usuário: {$dados['nome']} ({$dados['email']})", 'Usuarios');
        } else {
            $this->setFlashMessage('error', 'Erro ao salvar o usuário. Verifique se o e-mail já não está em uso.');
        }

        header('Location: ' . BASE_URL . '/usuario');
        exit();
    }

    public function editar(int $id)
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

    public function atualizar(int $id)
    {
        // CSRF token validation
        if (!isset($_POST['csrf_token']) || !$this->validateCsrfToken($_POST['csrf_token'])) {
            $this->setFlashMessage('error', 'Erro de validação de segurança (CSRF). Por favor, tente novamente.');
            header('Location: ' . BASE_URL . '/usuario');
            exit();
        }

        // Coleta dados diretamente do POST para evitar problemas com filter_input
        $nome = trim($_POST['nome'] ?? '');
        $email = strtolower(trim($_POST['email'] ?? '')); // Normaliza para minúsculas
        $cargoId = $_POST['cargo_id'] ?? $_POST['cargo'] ?? '';
        $perfilId = $_POST['perfil_id'] ?? $_POST['perfil'] ?? '';
        $status = $_POST['status'] ?? 'Ativo';

        // Verifica se os dados obrigatórios estão preenchidos
        // Alterado de empty() para verificação estrita para aceitar valor '0' e identificar qual campo falta
        // CORREÇÃO: Se for o usuário Admin principal, permite salvar mesmo se cargo vier vazio (mantém o atual ou define padrão)
        $isMainAdmin = (strtolower($email) === 'admin@sysenvicorp.com');
        
        if ($nome === '' || $email === '' || ($cargoId === '' && !$isMainAdmin) || $perfilId === '') {
            $campos = [];
            if ($nome === '') $campos[] = 'Nome';
            if ($email === '') $campos[] = 'E-mail';
            if ($cargoId === '') $campos[] = 'Cargo';
            if ($perfilId === '') $campos[] = 'Perfil';

            error_log("Falha validação update usuário ID $id. POST: " . print_r($_POST, true));
            $this->setFlashMessage('error', 'Erro ao atualizar o usuário. Campos obrigatórios faltando: ' . implode(', ', $campos));
            header('Location: ' . BASE_URL . '/usuario');
            exit();
        }

        $dados = [
            'nome' => htmlspecialchars($nome, ENT_QUOTES, 'UTF-8'),
            'email' => filter_var($email, FILTER_VALIDATE_EMAIL),
            'cargo_id' => (int)$cargoId,
            'perfil_id' => (int)$perfilId,
            'status' => ucfirst(strtolower($status)),
        ];

        if (!$dados['email']) {
            $this->setFlashMessage('error', 'O e-mail informado é inválido.');
            header('Location: ' . BASE_URL . '/usuario');
            exit();
        }

        if ($this->model->atualizarUsuario($id, $dados)) {
            $this->setFlashMessage('success', 'Usuário atualizado com sucesso!');
            $this->logAction('UPDATE', "Atualizou usuário ID {$id}: {$dados['nome']}", 'Usuarios', $id);
        } else {
            $this->setFlashMessage('error', 'Erro ao atualizar o usuário. Verifique se o e-mail já não está em uso por outro usuário.');
        }

        // Redirect back to the user list
        header('Location: ' . BASE_URL . '/usuario');
        exit();
    }

    public function resetPassword(int $id)
    {
        // Verifica se o método é POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->setFlashMessage('error', 'Operação inválida.');
            header('Location: ' . BASE_URL . '/usuario');
            exit();
        }

        // Validação do token CSRF
        if (!isset($_POST['csrf_token']) || !$this->validateCsrfToken($_POST['csrf_token'])) {
            $this->setFlashMessage('error', 'Erro de validação de segurança (CSRF). Por favor, tente novamente.');
            header('Location: ' . BASE_URL . '/usuario');
            exit();
        }

        // Chama o método do model para resetar a senha
        if ($this->model->resetarSenha($id)) {
            $this->setFlashMessage('success', 'A senha do usuário foi redefinida para a senha padrão com sucesso!');
            $this->logAction('RESET_PASSWORD', "Resetou a senha do usuário ID {$id}", 'Usuarios', $id);
        } else {
            $this->setFlashMessage('error', 'Erro ao redefinir a senha do usuário.');
        }

        // Redireciona de volta para a lista de usuários
        header('Location: ' . BASE_URL . '/usuario');
        exit();
    }

    public function excluir(int $id)
    {
        // Verifica se o método é POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->setFlashMessage('error', 'Operação inválida.');
            header('Location: ' . BASE_URL . '/usuario');
            exit();
        }

        // Validação do token CSRF
        if (!isset($_POST['csrf_token']) || !$this->validateCsrfToken($_POST['csrf_token'])) {
            $this->setFlashMessage('error', 'Erro de validação de segurança (CSRF). Por favor, tente novamente.');
            header('Location: ' . BASE_URL . '/usuario');
            exit();
        }

        // Se o ID não veio pela rota (argumento zerado), tenta pegar do POST
        if ($id <= 0) {
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: 0;
        }

        if ($id <= 0) {
            $this->setFlashMessage('error', 'ID de usuário inválido.');
            header('Location: ' . BASE_URL . '/usuario');
            exit();
        }

        // --- PROTEÇÃO CONTRA EXCLUSÃO DO ADMIN PRINCIPAL ---
        // Busca os dados do usuário que está prestes a ser excluído.
        $usuarioParaExcluir = $this->model->getUsuario($id);

        // Verifica se o usuário existe e se o e-mail é o do admin principal (case-insensitive).
        if ($usuarioParaExcluir && strtolower($usuarioParaExcluir['email']) === 'admin@sysenvicorp.com') {
            $this->setFlashMessage('error', 'O usuário administrador principal não pode ser excluído.');
            header('Location: ' . BASE_URL . '/usuario');
            exit();
        }
        // --- FIM DA PROTEÇÃO ---

        // Chama o método do model para excluir o usuário (e o colaborador associado)
        if ($this->model->excluirUsuario($id)) {
            $this->setFlashMessage('success', 'Usuário e funcionário associado foram excluídos com sucesso!');
            $this->logAction('DELETE', "Excluiu usuário ID {$id}", 'Usuarios', $id);
        } else {
            $this->setFlashMessage('error', 'Erro ao excluir o usuário.');
        }

        // Redireciona de volta para a lista de usuários
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
                $this->logAction('TOGGLE_STATUS', "Alterou status do usuário ID {$id} para {$novoStatus}", 'Usuarios', $id);
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

        $usuarioId = (int)$this->session->get('user_id');
        // Usaremos o método getUsuario que já existe no seu Model
        $usuario = ($usuarioId > 0) ? $this->model->getUsuario($usuarioId) : null;

        if (!$usuario) {
            $this->setFlashMessage('error', 'Usuário não encontrado.');
            header('Location: ' . BASE_URL); // Redireciona para a página inicial
            exit();
        }

        $data = [
            'pageTitle' => 'Meu Perfil',
            'usuario' => $usuario,
            'availableAvatars' => UsuarioModel::AVATARS_PADRAO,
        ];

        $this->renderView('usuario/perfil', $data);
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
        if (!isset($_POST['csrf_token']) || !$this->validateCsrfToken($_POST['csrf_token'])) {
            $this->setFlashMessage('error', 'Erro de validação de segurança (CSRF). Por favor, tente novamente.');
            header('Location: ' . BASE_URL . '/usuario/perfil');
            exit();
        }

        $senha = isset($_POST['senha']) ? trim($_POST['senha']) : '';
        $confirmarSenha = isset($_POST['confirmar_senha']) ? trim($_POST['confirmar_senha']) : '';

        $usuarioId = $this->session->get('user_id');
        $dados = [
            'id' => $usuarioId,
            'nome' => filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_SPECIAL_CHARS),
            'email' => filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL),
            'senha' => $senha,
            'confirmar_senha' => $confirmarSenha,
        ];

        // Validação básica
        if (empty($dados['nome']) || !$dados['email']) {
            $this->setFlashMessage('error', 'Nome e e-mail são obrigatórios e o e-mail deve ser válido.');
            header('Location: ' . BASE_URL . '/usuario/perfil');
            exit();
        }

        // Verifica se o e-mail já está em uso por OUTRO usuário
        if ($this->model->emailExists($dados['email'], $usuarioId)) {
            $this->setFlashMessage('error', 'O e-mail informado já está sendo utilizado por outro usuário.');
            header('Location: ' . BASE_URL . '/usuario/perfil');
            exit();
        }

        // --- Lógica de Avatar/Foto de Perfil ---
        $storageDir = ROOT_PATH . '/storage/users';
        if (!is_dir($storageDir)) {
            @mkdir($storageDir, 0755, true);
        }

        $currentAvatarFilename = null;
        $currentUser = $this->model->getUsuario($usuarioId);
        if ($currentUser) {
            $currentAvatarFilename = $currentUser['avatar_filename'];
        }

        $newAvatarFilenameToSave = $currentAvatarFilename; // Começa com o valor atual do DB

        // Lógica de prioridade para avatar/foto:
        // 1. Checkbox 'remover_foto' tem a maior prioridade
        if (!empty($_POST['remover_foto'])) {
            $newAvatarFilenameToSave = null; // Limpa explicitamente o avatar
            // Remove qualquer foto de upload física
            $possibleExt = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
            foreach ($possibleExt as $ext) {
                $f = $storageDir . "/user_{$usuarioId}." . $ext;
                if (file_exists($f)) {
                    @unlink($f);
                }
            }
        }
        // 2. Novo upload de foto tem a próxima prioridade
        if (!empty($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $newAvatarFilenameToSave = null; // Foto de upload significa que não há avatar da galeria
            $file = $_FILES['foto'];
            $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
            if (!array_key_exists($file['type'], $allowed)) {
                $this->setFlashMessage('error', 'Tipo de imagem não suportado. Use JPEG, PNG, GIF ou WEBP.');
                header('Location: ' . BASE_URL . '/usuario/perfil');
                exit();
            }
            if ($file['size'] > 2 * 1024 * 1024) { // Limite de 2MB
                $this->setFlashMessage('error', 'A imagem deve ter no máximo 2MB.');
                header('Location: ' . BASE_URL . '/usuario/perfil');
                exit();
            }
            
            $ext = $allowed[$file['type']];
            $dest = $storageDir . "/user_{$usuarioId}." . $ext;
            
            // Remove versões anteriores de fotos de upload com outras extensões
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
        // 3. Avatar da galeria selecionado tem a próxima prioridade (apenas se não houver upload/remoção)
        elseif (!empty($_POST['selected_avatar'])) {
            $selectedAvatar = filter_input(INPUT_POST, 'selected_avatar', FILTER_SANITIZE_SPECIAL_CHARS);
            // Valida se o avatar selecionado é um dos permitidos
            if (in_array($selectedAvatar, UsuarioModel::AVATARS_PADRAO)) {
                $newAvatarFilenameToSave = $selectedAvatar;
                // Se um avatar da galeria é selecionado, remove qualquer foto de upload existente
                $possibleExt = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
                foreach ($possibleExt as $ext) {
                    $f = $storageDir . "/user_{$usuarioId}." . $ext;
                    if (file_exists($f)) {
                        @unlink($f);
                    }
                }
            }
        }
        // Se nenhuma das opções acima foi acionada, $newAvatarFilenameToSave mantém o valor do DB

        // Validação da senha (se o usuário decidiu alterar)
        if ($senha !== '') {
            if (strlen($senha) < 6) {
                $this->setFlashMessage('error', 'A nova senha deve ter no mínimo 6 caracteres.');
                header('Location: ' . BASE_URL . '/usuario/perfil');
                exit();
            }
            if ($senha !== $confirmarSenha) {
                $this->setFlashMessage('error', 'As senhas não coincidem.');
                header('Location: ' . BASE_URL . '/usuario/perfil');
                exit();
            }
        }

        // Adiciona o avatar_filename final ao array $dados APENAS se ele mudou
        if ($newAvatarFilenameToSave !== $currentAvatarFilename) {
            $dados['avatar_filename'] = $newAvatarFilenameToSave;
        } else {
            // Remove do array para que o modelo não tente atualizar se não houve mudança
            unset($dados['avatar_filename']);
        }

        if ($this->model->atualizarPerfil($dados)) {
            // AJUSTE: Atualiza o nome na sessão para que a mudança reflita imediatamente no layout (header/sidebar)
            $this->session->set('user_name', $dados['nome']);
            // Atualiza o avatar na sessão APENAS se ele foi alterado
            if (array_key_exists('avatar_filename', $dados)) {
                $this->session->set('user_avatar', $dados['avatar_filename']);
            }

            $this->setFlashMessage('success', 'Perfil atualizado com sucesso!');
        } else {
            $this->setFlashMessage('error', 'Ocorreu um erro ao atualizar o perfil. Verifique se o e-mail já não está em uso por outro usuário.');
        }

        header('Location: ' . BASE_URL . '/usuario/perfil');
        exit();
    }
}
