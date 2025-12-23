<?php

namespace App\Controllers;

use App\Core\SessionManager;

/**
 * Classe base para todos os controllers da aplicação.
 * Contém métodos e propriedades compartilhados.
 */
abstract class BaseController
{
    protected $session;

    public function __construct()
    {
        $this->session = SessionManager::getInstance();
    }

    /**
     * Renderiza uma view dentro do template principal.
     *
     * @param string $viewPath O caminho para o arquivo da view (ex: 'dashboard/index').
     * @param array $data Os dados a serem passados para a view.
     */
    protected function renderView(string $viewPath, array $data = [])
    {
        // Garantir que um token CSRF exista na sessão e torná-lo disponível para todas as views
        $csrf = $this->session->get('csrf_token');
        if (empty($csrf)) {
            $csrf = bin2hex(random_bytes(32));
            $this->session->set('csrf_token', $csrf);
        }
        // Disponibiliza o token para a view via variável $csrf_token
        $data['csrf_token'] = $csrf;

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
        if (empty($currentUserPhoto)) {
            $name = $this->session->get('user_name', 'Usuário');
            $currentUserPhoto = 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&background=38bdf8&color=fff&size=40';
        }
        $data['currentUserPhoto'] = $currentUserPhoto;

        extract($data);
        ob_start();
        require ROOT_PATH . '/views/' . $viewPath . '.php'; // Carrega o conteúdo da view específica
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
        // Carrega diretamente o arquivo da view sem o buffer e o template.
        require ROOT_PATH . '/views/' . $viewPath . '.php';
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
}
