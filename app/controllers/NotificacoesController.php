<?php

namespace App\Controllers;

use App\Models\NotificacoesModel;

class NotificacoesController extends BaseController
{
    private $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new NotificacoesModel();
    }

    public function check()
    {
        header('Content-Type: application/json');
        if (!$this->session->isAuthenticated()) {
            echo json_encode(['success' => false]);
            exit;
        }

        $usuarioId = $this->session->get('user_id');
        $notificacoes = $this->model->getNaoLidas($usuarioId);

        echo json_encode(['success' => true, 'data' => $notificacoes]);
        exit;
    }

    public function marcarLida()
    {
        header('Content-Type: application/json');
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $usuarioId = $this->session->get('user_id');

        if ($id) $this->model->marcarComoLida($id, $usuarioId);
        else $this->model->marcarTodasComoLidas($usuarioId);

        echo json_encode(['success' => true]);
        exit;
    }

    /**
     * Rotina de limpeza automática de notificações.
     * Pode ser disparada via Cron Job ou manualmente.
     */
    public function limpar()
    {
        // Segurança: Apenas CLI, via chave secreta (CRON_TOKEN) ou se for um administrador logado
        $token = $_GET['token'] ?? null;

        $isCli = php_sapi_name() === 'cli';
        $isValidToken = defined('CRON_TOKEN') && !empty(CRON_TOKEN) && $token === CRON_TOKEN;
        $isAdmin = $this->session->isAuthenticated() && $this->session->isAdmin();

        if (!$isCli && !$isValidToken && !$isAdmin) {
            http_response_code(403);
            die('Acesso negado. Token de limpeza inválido ou permissão insuficiente.');
        }

        $dias = (int)($_GET['dias'] ?? 30);

        if (method_exists($this->model, 'limparNotificacoes')) {
            $count = $this->model->limparNotificacoes($dias);
            
            if (php_sapi_name() === 'cli') {
                echo "Rotina executada: {$count} notificações antigas removidas.\n";
            } else {
                $this->setFlashMessage('info', "Limpeza concluída: {$count} notificações removidas (Critério: Lidas com mais de {$dias} dias).");
                header('Location: ' . BASE_URL . '/configuracoes');
                exit;
            }
        } else {
            http_response_code(500);
            die("Erro de Implementação: O método 'limparNotificacoes' não foi encontrado no arquivo NotificacoesModel.php.");
        }
    }
}