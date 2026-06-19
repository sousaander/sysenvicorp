<?php

namespace App\Middleware;

use App\Core\SessionManager;

class AuthMiddleware
{
    private $session;

    /**
     * Lista de controllers que exigem perfil de 'admin'.
     * Use o nome simples da classe, sem 'Controller'.
     */
    private const ADMIN_CONTROLLERS = [
        'Perfil',
        'Configuracoes',
        'Empresa',
        'Banco',
        'CentroCusto',
        'Classificacao',
        'Categorias',
    ];

    /**
     * Lista de controllers que são públicos (não exigem login).
     */
    private const PUBLIC_CONTROLLERS = [
        'Auth',
    ];

    public function __construct()
    {
        $this->session = SessionManager::getInstance();
    }

    /**
     * Executa a verificação do middleware.
     *
     * @param string $controllerName O nome simples do controller (ex: 'UsuarioController').
     */
    public function handle(string $controllerName): void
    {
        // Remove o sufixo 'Controller' para a verificação
        $baseControllerName = str_replace('Controller', '', $controllerName);

        // 1. Permite acesso a controllers públicos (como AuthController)
        if (in_array($baseControllerName, self::PUBLIC_CONTROLLERS)) {
            return;
        }

        // 2. Se não for uma rota pública, verifica se o usuário está autenticado
        if (!$this->session->isAuthenticated()) {
            // Guarda a URL original para redirecionar o usuário de volta após o login
            $next = $_SERVER['REQUEST_URI'];
            $this->redirect('/auth/login?next=' . urlencode($next));
        }

        // 3. Verifica se o controller exige privilégios de administrador
        $userProfile = $this->session->get('usuario_perfil');

        if (in_array($baseControllerName, self::ADMIN_CONTROLLERS) && strtolower($userProfile ?? '') !== 'admin') {
            $this->session->setFlash('Você não tem permissão para acessar esta página.', 'error');
            $this->redirect('/'); // Redireciona para o dashboard principal
        }
    }

    /**
     * Função auxiliar para redirecionamento.
     *
     * @param string $url A URL para redirecionar (relativa à BASE_URL).
     */
    private function redirect(string $url): void
    {
        // Limpa qualquer saída que já tenha sido enviada
        if (ob_get_length()) ob_end_clean();
        header('Location: ' . BASE_URL . $url);
        exit();
    }
}
