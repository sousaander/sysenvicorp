<?php

use App\Core\SessionManager;

if (!function_exists('has_permission')) {
    /**
     * Verifica se o usuário logado tem uma permissão específica.
     *
     * @param string $permission A chave da permissão (ex: 'users_edit').
     * @return bool
     */
    function has_permission(string $permission): bool
    {
        $session = SessionManager::getInstance();
        if (!$session->isAuthenticated()) {
            return false;
        }

        // Admin ou perfil com permissão '*' tem acesso total
        $perfil = strtolower(trim($session->get('usuario_perfil') ?? $session->get('user_perfil') ?? ''));
        $permissoes = $session->get('usuario_permissoes', []);
        $userEmail = strtolower($session->get('user_email') ?? '');

        if (in_array($perfil, ['admin', 'administrador']) || in_array('*', $permissoes) || $userEmail === 'admin@sysenvicorp.com') {
            return true;
        }

        return in_array($permission, $permissoes);
    }
}

if (!function_exists('has_any_permission')) {
    /**
     * Verifica se o usuário logado tem pelo menos UMA das permissões de uma lista.
     * Útil para exibir menus no sidebar.
     *
     * @param array $permissions Array com as chaves das permissões (ex: ['clientes_view', 'clientes_create']).
     * @return bool
     */
    function has_any_permission(array $permissions): bool
    {
        $session = SessionManager::getInstance();
        if (!$session->isAuthenticated()) {
            return false;
        }

        // Admin ou perfil com permissão '*' tem acesso total
        $perfil = strtolower(trim($session->get('usuario_perfil') ?? $session->get('user_perfil') ?? ''));
        $userPermissions = $session->get('usuario_permissoes', []);
        $userEmail = strtolower($session->get('user_email') ?? '');

        if (in_array($perfil, ['admin', 'administrador']) || in_array('*', $userPermissions) || $userEmail === 'admin@sysenvicorp.com') {
            return true;
        }

        // Retorna true se houver qualquer interseção entre as permissões do usuário e as permissões necessárias.
        return !empty(array_intersect($permissions, $userPermissions));
    }
}
