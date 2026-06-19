<?php
// api_webhook.php - na raiz do sysenvicorp

require_once __DIR__ . '/app/config/settings.php';
require_once __DIR__ . '/models/CaptacaoIAModel.php';
require_once __DIR__ . '/models/ConfigIAModel.php';

use App\Models\CaptacaoIAModel;
use App\Models\ConfigIAModel;

// Valida token de autenticação
$headers = getallheaders();
$token = str_replace('Bearer ', '', $headers['Authorization'] ?? '');
$iaConfig = require __DIR__ . '/app/config/ia_config.php';

if ($token !== $iaConfig['webhook']['token']) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$event = $input['event'] ?? '';

$captacaoModel = new CaptacaoIAModel();

switch ($event) {
    case 'new_captacoes':
        $novas = 0;
        foreach ($input['data'] as $captacao) {
            if ($captacaoModel->save($captacao)) {
                $novas++;
            }
        }
        
        // Armazena evento para SSE
        file_put_contents(__DIR__ . '/storage/ia_logs/last_events.json', json_encode([
            'timestamp' => time(),
            'event' => $event,
            'data' => $input['data'],
            'sound' => $input['sound'] ?? null,
            'count' => $novas
        ]));
        
        echo json_encode(['success' => true, 'saved' => $novas]);
        break;
        
    case 'daily_summary':
        // Envia resumo por e-mail
        $configModel = new ConfigIAModel();
        $config = $configModel->get();
        
        if ($config['daily_email_summary_enabled']) {
            require_once __DIR__ . '/app/core/Mailer.php';
            $mailer = new Mailer();
            
            $captacoesDoDia = $captacaoModel->getByDate($input['date']);
            $mailer->sendDailySummary($captacoesDoDia, $input['date']);
        }
        
        echo json_encode(['success' => true]);
        break;
        
    default:
        echo json_encode(['success' => false, 'error' => 'Unknown event']);
}