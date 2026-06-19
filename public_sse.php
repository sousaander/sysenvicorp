<?php
// public_sse.php - Endpoint Server-Sent Events

header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');
header('X-Accel-Buffering: no'); // Desabilita buffering do nginx

// Previne timeouts
set_time_limit(0);
ignore_user_abort(true);

// 1. Inicializa o ambiente da aplicação
define('ROOT_PATH', __DIR__);
require_once ROOT_PATH . '/vendor/autoload.php';
require_once ROOT_PATH . '/app/config/env.php';
require_once ROOT_PATH . '/app/config/settings.php';

use App\Core\SessionManager;
use App\Core\Connection;

// 2. Verifica a sessão do usuário
$session = SessionManager::getInstance();
$userId = $session->get('user_id');

// Se não houver usuário logado, encerra a conexão SSE
if (!$userId) {
    exit();
}

$db = Connection::getInstance();

// 3. Identifica o ponto de partida (última notificação já existente)
// para enviar apenas o que for gerado após a abertura desta conexão.
$stmtLast = $db->prepare("SELECT MAX(id) FROM notificacoes WHERE usuario_id = ?");
$stmtLast->execute([$userId]);
$lastProcessedId = (int)$stmtLast->fetchColumn();

// Loop infinito para manter conexão
while (true) {
    // 4. Busca novas notificações não lidas para o usuário logado
    $stmt = $db->prepare("SELECT id, titulo, mensagem, link FROM notificacoes WHERE usuario_id = ? AND id > ? AND lida = 0 ORDER BY id ASC");
    $stmt->execute([$userId, $lastProcessedId]);
    $notificacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($notificacoes as $n) {
        $lastProcessedId = $n['id'];
        echo "event: notificacao\n";
        echo "data: " . json_encode($n) . "\n\n";
    }

    // 5. Envia keep-alive para evitar que proxies (como Nginx ou Cloudflare) derrubem a conexão
    echo ": keep-alive\n\n";
    ob_flush();
    flush();

    // Intervalo de verificação (3 segundos para não sobrecarregar o banco)
    sleep(3);

    // Verifica se cliente desconectou
    if (connection_aborted()) {
        break;
    }
}