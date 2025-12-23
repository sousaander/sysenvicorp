<?php

namespace App\Core;

/**
 * SessionManager
 * Gerencia todas as operações relacionadas à sessão PHP de forma segura e centralizada.
 * Utiliza o padrão Singleton.
 */
class SessionManager
{
    private static $instance = null;

    private function __construct()
    {
        // A sessão agora é iniciada globalmente em public/index.php.
        // Esta verificação é para garantir que a sessão exista. Se não existir, lança um erro.
        if (session_status() === PHP_SESSION_NONE) {
            throw new \LogicException("A sessão não foi iniciada. Verifique a configuração em public/index.php.");
        }
    }

    /**
     * Retorna a única instância do SessionManager (Singleton).
     */
    public static function getInstance(): SessionManager
    {
        if (self::$instance === null) {
            self::$instance = new SessionManager();
        }
        return self::$instance;
    }

    /**
     * Define um valor na sessão.
     * @param string $key Chave da sessão.
     * @param mixed $value Valor a ser armazenado.
     */
    public function set(string $key, $value): void
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Obtém um valor da sessão.
     * @param string $key Chave da sessão.
     * @param mixed $default Valor padrão caso a chave não exista.
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Remove uma chave da sessão.
     * @param string $key Chave a ser removida.
     */
    public function remove(string $key): void
    {
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }

    /**
     * Limpa e destrói toda a sessão.
     */
    public function destroy(): void
    {
        session_unset();
        session_destroy();
        // Remove o cookie de sessão para garantir o encerramento completo
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }
    }

    // --- Métodos para Flash Messages (Mensagens de uso único após redirecionamento) ---

    /**
     * Define uma mensagem flash.
     * @param string $message Conteúdo da mensagem.
     * @param string $type Tipo da mensagem (success, error, warning).
     */
    public function setFlash(string $message, string $type = 'success'): void
    {
        $this->set('flash_message', ['message' => $message, 'type' => $type]);
    }

    /**
     * Obtém e remove a mensagem flash (uso único).
     * @return array|null Retorna a mensagem ou null.
     */
    public function getFlash(): ?array
    {
        $flash = $this->get('flash_message');
        if ($flash) {
            $this->remove('flash_message'); // Remove após a leitura
        }
        return $flash;
    }

    // --- Métodos de Autenticação (Base) ---

    /**
     * Verifica se o usuário está logado.
     */
    public function isAuthenticated(): bool
    {
        return $this->get('user_id') !== null;
    }

    /**
     * Define o usuário como logado.
     * @param int $userId ID do usuário logado.
     * @param string $userName Nome do usuário logado.
     */
    public function login(int $userId, string $userName): void
    {
        $this->set('user_id', $userId);
        $this->set('user_name', $userName);
        // Regenerar o ID da sessão após o login para prevenir Session Fixation
        session_regenerate_id(true);
    }
}
