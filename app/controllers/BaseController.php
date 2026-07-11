<?php

namespace App\Controllers;

use App\Core\SessionManager;
use App\Middleware\AuthMiddleware;
use App\Middleware\PermissionMiddleware;

/**
 * Classe base para todos os controllers da aplicação.
 * Contém métodos e propriedades compartilhados.
 */
abstract class BaseController
{
    protected $session;

    // Configurações de Segurança CSRF
    protected const CSRF_TOKEN_LIFETIME = 7200; // 2 horas em segundos
    protected const CSRF_MAX_TOKENS = 10;       // Máximo de tokens mantidos na sessão

    /**
     * Mapeia ações (métodos) do controller para a permissão necessária.
     * Ex: ['index' => 'users_view', 'salvar' => 'users_manage']
     * @var array
     */
    protected $requiredPermissions = [];

    public function __construct()
    {
        $this->session = new SessionDecorator(SessionManager::getInstance());

        // IMPORTANTE: Verifique se no seu servidor existe a linha '$this->session->start();' 
        // logo abaixo e REMOVA-A. A sessão já é iniciada no index.php.

        // Detecta o nome da ação atual
        $actionName = defined('CURRENT_ACTION') ? CURRENT_ACTION : $this->getCurrentActionName();

        // Pula autenticação para métodos específicos
        if (!in_array(strtolower($actionName), ['login', 'logout', 'processlogin', 'processregister', 'getformforedit', 'getformforeditaditivo', 'aprovarpropostapublica', 'limpar', 'croncheckvencimento'])) {
            $authMiddleware = new AuthMiddleware();

            // Extrai o nome simples do controller (ex: 'UsuarioController')
            $fullControllerName = get_class($this);
            $parts = explode('\\', $fullControllerName);
            $controllerName = end($parts);

            $authMiddleware->handle($controllerName);
        }

        // Verifica se a ação atual requer uma permissão específica
        if (isset($this->requiredPermissions[$actionName])) {
            // Delega a verificação de permissão para o middleware.
            // O middleware usará o SessionDecorator, que já contém a lógica
            // para dar acesso total a administradores.
            $permission = $this->requiredPermissions[$actionName];
            (new PermissionMiddleware())->handle($permission);
        }
    }

    /**
     * Detecta o nome da ação atual baseado na URI da requisição.
     * @return string O nome da ação.
     */
    protected function getCurrentActionName(): string
    {
        if (defined('CURRENT_ACTION')) {
            return CURRENT_ACTION;
        }

        try {
            $uri = $_SERVER['REQUEST_URI'] ?? '';
            $path = parse_url($uri, PHP_URL_PATH);
            $segments = array_values(array_filter(explode('/', strtolower($path))));
            
            // Tenta localizar a action baseada na posição relativa ao 'public'
            $publicIndex = array_search('public', $segments);
            
            if ($publicIndex !== false && isset($segments[$publicIndex + 2])) {
                $action = $segments[$publicIndex + 2];
            } else {
                // Fallback: a action é o segmento após o controller /{controller}/{action}/...
                $action = $segments[1] ?? 'index';
            }

            if ($action && preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $action)) {
                return $action;
            }
        } catch (\Throwable $e) {
            error_log("Erro em getCurrentActionName: " . $e->getMessage());
        }
        
        // Fallback para 'index'
        return 'index';
    }

    /**
     * Gera um novo token CSRF, adiciona ao pool da sessão e limpa tokens antigos.
     * @return string
     */
    protected function generateCsrfToken(): string
    {
        $tokens = $this->session->get('csrf_tokens') ?: [];
        
        // Limpeza automática: remove tokens expirados
        $now = time();
        $tokens = array_filter($tokens, function($expiry) use ($now) {
            return $expiry > $now;
        });

        // Gera novo token
        $newToken = bin2hex(random_bytes(32));
        $tokens[$newToken] = $now + self::CSRF_TOKEN_LIFETIME;

        // Limita o tamanho do pool para evitar inchaço da sessão
        if (count($tokens) > self::CSRF_MAX_TOKENS) {
            asort($tokens); // Ordena por expiração
            array_shift($tokens); // Remove o que expira mais cedo
        }

        $this->session->set('csrf_tokens', $tokens);
        return $newToken;
    }

    /**
     * Valida se um token existe no pool e ainda é válido.
     * @param string $token
     * @return bool
     */
    protected function validateCsrfToken(string $token): bool
    {
        if (empty($token)) return false;

        $tokens = $this->session->get('csrf_tokens') ?: [];
        $now = time();

        if (isset($tokens[$token]) && $tokens[$token] > $now) {
            return true;
        }

        return false;
    }

    protected function renderView(string $viewPath, array $data = [])
    {
        // Disponibiliza um novo token válido para a view
        $data['csrf_token'] = $this->generateCsrfToken();

        // Determina a foto do usuário atual (se existir) para ser usada no layout
        $userId = $this->session->get('user_id');
        $currentUserPhoto = null;
        if ($userId) {
            $possibleExt = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
            foreach ($possibleExt as $ext) {
                $filename = "users/user_{$userId}." . $ext;
                $filePath = ROOT_PATH . '/storage/' . $filename;
                if (file_exists($filePath)) {
                    $currentUserPhoto = rtrim(BASE_URL, '/') . '/storage/' . $filename;
                    break;
                }
            }
        }

        // Se não tem foto de upload, verifica se tem avatar da galeria selecionado
        if (empty($currentUserPhoto)) {
            $avatar = $this->session->get('user_avatar');
            if ($avatar) {
                $currentUserPhoto = rtrim(BASE_URL, '/') . '/public/assets/avatars/' . $avatar;
            }
        }

        if (empty($currentUserPhoto)) {
            $name = $this->session->get('user_name', 'Usuário');
            // Adicionado background mais neutro ou dinâmico via parâmetros
            $currentUserPhoto = 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&background=random&color=fff&size=128';
        }
        $data['currentUserPhoto'] = $currentUserPhoto;

        // Dados do usuário para o cabeçalho (Garante exibição em todas as telas)
        $data['userName'] = $this->session->get('user_name', 'Usuário');
        // Adiciona um fallback explícito para garantir que o nome nunca seja nulo ou vazio
        if (empty($data['userName'])) $data['userName'] = 'Usuário';
        $data['userProfile'] = $this->session->get('usuario_perfil', 'Acesso');
        $data['userCargo'] = $this->session->get('user_cargo', '');

        // Data atual formatada em Português
        $meses = [
            1 => 'janeiro', 2 => 'fevereiro', 3 => 'março', 4 => 'abril',
            5 => 'maio', 6 => 'junho', 7 => 'julho', 8 => 'agosto',
            9 => 'setembro', 10 => 'outubro', 11 => 'novembro', 12 => 'dezembro'
        ];
        $data['currentDateFormatted'] = date('d') . ' de ' . mb_strtolower($meses[(int)date('n')], 'UTF-8') . ' de ' . date('Y');

        // Busca contadores globais para a Sidebar (apenas se o usuário estiver logado)
        $data['contagemPropostasPendentes'] = 0;
        if ($this->session->isAuthenticated()) {
            $propostaModel = new \App\Models\PropostaModel();
            $data['contagemPropostasPendentes'] = $propostaModel->getCountPropostasPendentes();

            // Busca contagem de notificações não lidas para o header
            $notificacoesModel = new \App\Models\NotificacoesModel();
            $notificacoesAtivas = $notificacoesModel->getNaoLidas($userId);
            $data['unreadNotificationCount'] = count($notificacoesAtivas);

            // Busca contagem de captações da IA para o menu lateral (Radar IA)
            if (!class_exists(\App\Models\CaptacaoIAModel::class)) {
                $captacaoModelFile = ROOT_PATH . '/models/CaptacaoIAModel.php';
                if (file_exists($captacaoModelFile)) {
                    require_once $captacaoModelFile;
                }
            }
            if (class_exists(\App\Models\CaptacaoIAModel::class)) {
                $captacaoIAModel = new \App\Models\CaptacaoIAModel();
                $data['contagemCaptacoesIA'] = $captacaoIAModel->getContagemNaoLidas();
            } else {
                $data['contagemCaptacoesIA'] = 0;
            }
        }

        extract($data);
        ob_start();

        $viewFile = ROOT_PATH . '/views/' . $viewPath . '.php';

        // Correção para Case Sensitivity em servidores Linux (InfinityFree)
        if (!file_exists($viewFile)) {
            $parts = explode('/', $viewPath);
            if (count($parts) > 0) {
                $parts[0] = ucfirst($parts[0]); // Tenta capitalizar a pasta (ex: clientes -> Clientes)
                $altViewFile = ROOT_PATH . '/views/' . implode('/', $parts) . '.php';
                if (file_exists($altViewFile)) {
                    $viewFile = $altViewFile;
                }
            }
        }

        require $viewFile; // Carrega o conteúdo da view específica
        $content = ob_get_clean();

        // Agora, carrega o template principal, que terá acesso à variável $content
        require ROOT_PATH . '/views/layouts/main_template.php';
    }

    /**
     * Renderiza uma view parcial (sem o template principal).
     * Útil para requisições AJAX que retornam HTML.
     *
     * @param string $viewPath O caminho para o arquivo da view.
     * @param array $data Os dados a serem passados para a view.
     */
    protected function renderPartial(string $viewPath, array $data = [])
    {
        extract($data);

        $viewFile = ROOT_PATH . '/views/' . $viewPath . '.php';

        // Correção para Case Sensitivity em servidores Linux (InfinityFree)
        // Verifica se o arquivo existe; se não, tenta com a primeira letra maiúscula na pasta
        if (!file_exists($viewFile)) {
            $parts = explode('/', $viewPath);
            if (count($parts) > 0) {
                $parts[0] = ucfirst($parts[0]); // Ex: clientes -> Clientes
                $altViewFile = ROOT_PATH . '/views/' . implode('/', $parts) . '.php';
                if (file_exists($altViewFile)) {
                    $viewFile = $altViewFile;
                }
            }
        }

        require $viewFile;
    }

    /**
     * Define uma flash message na sessão.
     *
     * @param string $type O tipo da mensagem (ex: 'success', 'error', 'info').
     * @param string $message A mensagem a ser exibida.
     */
    protected function setFlashMessage(string $type, string $message): void
    {
        $this->session->setFlash($message, $type);
    }

    /**
     * Renderiza a flash message se ela existir na sessão.
     * Esta função é chamada a partir do template principal.
     */
    public function renderFlashMessage(): void
    {
        $flash = $this->session->getFlash();
        if ($flash) {
            $type = $flash['type'];
            $message = $flash['message'];
            // A view partial/flash_message.php será incluída
            require ROOT_PATH . '/views/partials/flash_message.php';
        }
    }

    /**
     * Registra uma ação no log de auditoria.
     *
     * @param string $action A ação realizada (ex: 'LOGIN', 'CREATE', 'UPDATE').
     * @param string $description Descrição detalhada da ação.
     * @param string|null $module O módulo onde a ação ocorreu (ex: 'Clientes', 'Financeiro').
     * @param int|null $resourceId O ID do recurso afetado.
     */
    protected function logAction(string $action, string $description, ?string $module = null, ?int $resourceId = null): void
    {
        $userId = $this->session->get('user_id'); // Pode ser null se não logado
        $ip = $_SERVER['REMOTE_ADDR'] ?? null;

        // Instancia o model apenas quando necessário para evitar overhead
        $auditModel = new \App\Models\AuditLogModel();
        $auditModel->log($userId, $action, $description, $module, $resourceId, $ip);
    }
}

/**
 * Decorator para adicionar funcionalidade ao SessionManager sem modificar a classe original.
 * Isso resolve o erro de 'Call to undefined method SessionManager::hasPermission()' nas views.
 */
class SessionDecorator
{
    private $sessionManager;

    public function __construct($sessionManager)
    {
        $this->sessionManager = $sessionManager;
    }

    /**
     * Verifica se o usuário logado é um administrador com acesso total.
     * @return bool
     */
    public function isAdmin(): bool
    {
        $perfilAtual = strtolower(trim($this->sessionManager->get('usuario_perfil') ?? ''));
        $permissoes = $this->sessionManager->get('usuario_permissoes') ?? [];

        // Nomes de perfil de admin ou a permissão coringa '*' garantem acesso total.
        return in_array($perfilAtual, ['admin', 'administrador']) || in_array('*', $permissoes);
    }

    public function hasPermission(string $permission): bool
    {
        // Se for admin, sempre tem permissão.
        if ($this->isAdmin()) {
            return true;
        }

        // Caso contrário, verifica a lista de permissões granulares.
        $permissoes = $this->sessionManager->get('usuario_permissoes') ?? [];

        // Regra de Dependência Hierárquica (Sênior):
        // Como o módulo de Contratos agora é um submódulo do Jurídico, qualquer ação 
        // de contratos exige que o usuário tenha, no mínimo, a permissão base do Jurídico.
        if (strpos($permission, 'contratos_') === 0) {
            if (!is_array($permissoes) || !in_array('juridico_dashboard_view', $permissoes)) {
                return false;
            }
        }

        return is_array($permissoes) && in_array($permission, $permissoes);
    }

    public function __call($method, $args)
    {
        // Evita erro 500 caso o método 'start' seja chamado (comum em migrações de código),
        // já que o SessionManager não o implementa e a sessão já é iniciada no index.php.
        if ($method === 'start') {
            return true;
        }

        return call_user_func_array([$this->sessionManager, $method], $args);
    }

    public function __get($property)
    {
        return $this->sessionManager->$property;
    }
}
