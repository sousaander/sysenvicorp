<?php

// Define o caminho raiz do projeto para que o script possa encontrar os arquivos necessários
define('ROOT_PATH', dirname(__DIR__));

// Inclui o autoloader do Composer para carregar as classes
require_once ROOT_PATH . '/vendor/autoload.php';

// Inclui o arquivo de configuração, que geralmente define BASE_URL e as configurações do banco
require_once ROOT_PATH . '/config/config.php';

// Usa as classes necessárias
use App\Core\Connection;
use App\Models\LicencasOperacaoModel;

// Inicializa a conexão com o banco de dados
$pdo = Connection::getInstance();
$licencasModel = new LicencasOperacaoModel();

echo "Iniciando verificação e atualização de licenças vencidas...\n";

// Chama o método no modelo para atualizar as licenças
$updatedCount = $licencasModel->updateExpiredLicensesStatus();

echo "Finalizado. $updatedCount licença(s) foram atualizadas para 'Vencida'.\n";

// Opcional: registrar em um arquivo de log específico se necessário
file_put_contents(ROOT_PATH . '/storage/logs/cron_licencas.log', date('Y-m-d H:i:s') . " - Licenças atualizadas: $updatedCount\n", FILE_APPEND);