<?php

namespace App\Middleware;

use App\Core\SessionManager;

class PermissionMiddleware
{
    /**
     * Verifica se o usuário tem a permissão necessária para acessar a rota.
     * Se não tiver, define uma mensagem de erro e redireciona.
     *
     * @param string $requiredPermission A permissão necessária (ex: 'config_usuarios_view').
     */
    public function handle(string $requiredPermission)
    {
        // A função has_permission() já verifica se o usuário está autenticado e se é admin.
        if (!has_permission($requiredPermission)) {
            $session = SessionManager::getInstance();
            $session->setFlash('Acesso negado. Você não tem permissão para realizar esta ação.', 'error');

            // Redireciona para a página anterior ou para o dashboard
            $redirectUrl = $_SERVER['HTTP_REFERER'] ?? BASE_URL . '/';
            header('Location: ' . $redirectUrl);
            exit();
        }
    }
}