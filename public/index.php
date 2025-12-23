<?php
// Carrega o autoloader do Composer como a primeira ação. Isso é crucial.
require_once dirname(__DIR__) . '/vendor/autoload.php';

// Habilita a exibição de todos os erros (essencial para desenvolvimento)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Inicia a sessão para suportar flash messages
session_start();

// Define constantes globais
define('ROOT_PATH', dirname(__DIR__));
// CARREGAMENTO DAS CONFIGURAÇÕES GLOBAIS
require_once ROOT_PATH . '/app/config/settings.php';

// Definição dinâmica e completa da BASE_URL
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || ($_SERVER['SERVER_PORT'] ?? 80) == 443) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'];
$scriptName = str_replace('/index.php', '', $_SERVER['SCRIPT_NAME']); // Remove apenas /index.php para manter /public
define('BASE_URL', rtrim($protocol . $host . $scriptName, '/'));

// --- Lógica de Roteamento ---

// Obtém a URI da requisição, removendo o caminho do subdiretório (se houver)
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$scriptName = $_SERVER['SCRIPT_NAME'];
$basePath = dirname($scriptName); // Pega o diretório do index.php (ex: /sysenvicorp/public)
$basePath = rtrim($basePath, '/'); // Garante que não tenha barra no final

$uri = substr($requestUri, strlen($basePath));
$uri = trim($uri, '/');
$segments = $uri ? explode('/', $uri) : [];

// Usa o controller padrão definido em settings.php se não houver segmentos na URL
$controllerName = empty($segments[0]) ? DEFAULT_CONTROLLER : ucfirst(strtolower($segments[0]));
$actionName = isset($segments[1]) && !empty($segments[1]) ? $segments[1] : 'index';
$params = array_slice($segments, 2);

// Monta o nome completo da classe do controller com namespace
$controllerClass = "App\\Controllers\\" . $controllerName . 'Controller';

if (class_exists($controllerClass)) {
    // O bloco try agora engloba a criação do controller.
    // Isso é CRUCIAL para capturar erros no construtor.
    try {
        $controller = new $controllerClass(); // A instanciação foi movida para cá.
        if (method_exists($controller, $actionName)) {
            // call_user_func_array é a forma padrão e mais eficiente de chamar um método
            // com um array de parâmetros. Cada item do array $params se tornará um
            // argumento separado para o método.
            // Ex: call_user_func_array([$obj, 'metodo'], ['arg1', 'arg2'])
            // é o mesmo que $obj->metodo('arg1', 'arg2');
            call_user_func_array([$controller, $actionName], $params);
        } else {
            // Controller encontrado, mas a ação não
            http_response_code(404);
            echo "404 - Ação '{$actionName}' não encontrada no módulo '{$controllerName}'.";
        }
    } catch (\Throwable $e) {
        http_response_code(500); // Erro interno do servidor
        echo "<h1>Erro 500 - Erro Interno do Servidor</h1>";
        echo "<p>Ocorreu um erro ao processar a sua requisição.</p>";
        // Em ambiente de desenvolvimento, mostramos o erro detalhado.
        // Em produção, isso deve ser desabilitado em settings.php.
        if (defined('SHOW_ERRORS') && SHOW_ERRORS) {
            echo "<pre><strong>Erro:</strong> " . $e->getMessage() . "<br>";
            echo "<strong>Arquivo:</strong> " . $e->getFile() . "<br>";
            echo "<strong>Linha:</strong> " . $e->getLine() . "<br>";
            echo "<strong>Trace:</strong><br>" . $e->getTraceAsString() . "</pre>";
        }
        exit;
    }
} else {
    // Controller não encontrado (ex: módulo inexistente)
    http_response_code(404);
    echo "404 - Módulo '{$controllerName}' não encontrado (Classe '{$controllerClass}' não existe).";
    // Adicione um log para depuração em ambiente de desenvolvimento
    error_log("ROUTER ERROR: Controller class not found: " . $controllerClass . " | URI: " . $uri);
}
