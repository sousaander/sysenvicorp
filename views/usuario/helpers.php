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

        // O admin tem todas as permissões implicitamente.
        if (strtolower($session->get('usuario_perfil')) === 'admin') {
            return true;
        }

        $permissoes = $session->get('usuario_permissoes', []);
        return in_array($permission, $permissoes);
    }
}

if (!function_exists('get_transfer_type')) {
    /**
     * Verifica se uma transação é uma transferência e retorna o tipo (in/out).
     *
     * @param array $transacao
     * @return string|null 'in', 'out', ou null se não for transferência.
     */
    function get_transfer_type(array $transacao): ?string
    {
        if (isset($transacao['documento_vinculado'])) {
            if (strpos($transacao['documento_vinculado'], 'transfer_in:') === 0) {
                return 'in';
            }
            if (strpos($transacao['documento_vinculado'], 'transfer_out:') === 0) {
                return 'out';
            }
        }
        return null;
    }
}
