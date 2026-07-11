<?php

// No início do arquivo, antes de qualquer outra configuração
require_once __DIR__ . '/../app/config/env.php';

// Agora você pode usar as variáveis
$dbHost = env('DB_HOST');
$dbName = env('DB_NAME');
$secretKey = env('WEBHOOK_SECRET_KEY');

// Suas outras configurações...
// Carrega o autoloader do Composer como a primeira ação. Isso é crucial.
require_once dirname(__DIR__) . '/vendor/autoload.php';

// Define constantes globais
define('ROOT_PATH', dirname(__DIR__));

ini_set('log_errors', 1);
ini_set('error_log', ROOT_PATH . '/error_log_php.log');

// CARREGAMENTO DE HELPERS GLOBAIS
require_once ROOT_PATH . '/app/helpers/format_helper.php';
// Carregamento das funções de permissão e autenticação
require_once ROOT_PATH . '/app/helpers.php';
// Carregamento manual do ReportHelper para garantir disponibilidade nas views
require_once ROOT_PATH . '/app/helpers/ReportHelper.php';

// Normalização do caminho base para evitar problemas de origem em redirecionamentos.
// Esta variável deve ser calculada sempre, pois é essencial para a lógica de roteamento abaixo.
$scriptName = $_SERVER['SCRIPT_NAME'];
$basePath = str_replace('\\', '/', dirname($scriptName)); 
if ($basePath === '/' || $basePath === '.') {
    $basePath = '';
}
// Remove /public se estiver no final, mas mantém o nome da subpasta do sistema
$basePath = rtrim(preg_replace('|/public$|', '', $basePath), '/');

// Inicia a sessão após o cálculo do basePath para configurar os cookies corretamente
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_strict_mode', 1);
    
    // Define o caminho do cookie para a subpasta específica do sistema (calculado dinamicamente)
    ini_set('session.cookie_path', $basePath ?: '/');
    
    // Configurações extras para produção (HTTPS)
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        ini_set('session.cookie_secure', 1);
        ini_set('session.cookie_samesite', 'Lax');
    }

    if (!session_start()) {
        error_log("Falha ao iniciar a sessão no index.php");
    }
}

// CARREGAMENTO DAS CONFIGURAÇÕES GLOBAIS (Movemos para antes da definição da BASE_URL)
require_once ROOT_PATH . '/app/config/settings.php';

if (!defined('BASE_URL')) {
    // Definição dinâmica e completa da BASE_URL (Considerando SSL e Proxies Reversos)
    $protocol = (
        (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || 
        ($_SERVER['SERVER_PORT'] ?? 80) == 443 ||
        (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
    ) ? "https://" : "http://";

    $host = $_SERVER['HTTP_HOST'];

    define('BASE_URL', $protocol . $host . $basePath);
}

// --- Lógica de Roteamento ---
// Obtém a URI da requisição, removendo o caminho do subdiretório (se houver)
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$scriptName = $_SERVER['SCRIPT_NAME'];

// A URI para o roteador é o que sobra da URL após remover o caminho base.
$routingBasePath = $basePath;

// A URI para o roteador é o que sobra da URL após remover o caminho base de roteamento.
$uri = substr($requestUri, strlen($routingBasePath));

$uri = trim($uri, '/');
// Remove a extensão .php se estiver presente na URL para não confundir o nome do Controller
$uri = preg_replace('/\.php$/', '', $uri);

$segments = $uri ? explode('/', $uri) : [];

// CORREÇÃO PARA HOSPEDAGEM (InfinityFree/Shared):
// Se a URL contiver 'public' mas o basePath não (ex: index.php na raiz ou rewrite incorreto),
// removemos o segmento 'public' para não ser confundido com um Controller.
if (isset($segments[0]) && strtolower($segments[0]) === 'public') {
    array_shift($segments);
}

// Usa o controller padrão definido em settings.php se não houver segmentos na URL
$segment = isset($segments[0]) ? $segments[0] : '';

if (empty($segment)) {
    $controllerName = DEFAULT_CONTROLLER;
} else {
    $lowerSegment = strtolower($segment);
    // Mapa para corrigir nomes de controllers compostos (CamelCase)
    $controllerMap = [
        'licencasoperacao' => 'LicencasOperacao',
        'notafiscal'       => 'NotaFiscal',
        'nfse'             => 'Nfse',
        'cte'              => 'Cte',
        'centrocusto'      => 'CentroCusto',
        'contabil'         => 'Contabil',
        'estoque'          => 'Estoque',
        'regrasfiscais'    => 'RegraFiscal',
        'relatorios'       => 'Relatorio',
        'obrigacoesfiscais' => 'ObrigacaoFiscal',
        'legislacao'       => 'Legislacao',
        'login'            => 'Auth', // Redireciona /login para AuthController
        'auth'             => 'Auth', // Garante que /auth também mapeie corretamente
        'bensativos'       => 'BensAtivos',
        'auditlog'         => 'AuditLog',
    ];

    $controllerName = isset($controllerMap[$lowerSegment])
        ? $controllerMap[$lowerSegment]
        : (strpos($segment, '-') !== false ? str_replace('-', '', ucwords($segment, '-')) : ucfirst($lowerSegment));
}

$actionName = isset($segments[1]) && !empty($segments[1]) ? $segments[1] : 'index';
$params = array_slice($segments, 2);

// Define a ação atual globalmente para simplificar a verificação de permissões no BaseController
define('CURRENT_ACTION', $actionName);

// Monta o nome completo da classe do controller com namespace
$controllerClass = "App\\Controllers\\" . $controllerName . 'Controller';

if (class_exists($controllerClass)) {
    // O bloco try agora engloba a criação do controller.
    // Isso é CRUCIAL para capturar erros no construtor.
    try {
        $controller = new $controllerClass(); // A instanciação foi movida para cá.
        // Verifica se o método pode ser chamado (suporta __call para rotas dinâmicas como Storage)
        if (is_callable([$controller, $actionName])) {
            // call_user_func_array é a forma padrão e mais eficiente de chamar um método
            // com um array de parâmetros. Cada item do array $params se tornará um
            // argumento separado para o método.
            // Ex: call_user_func_array([$obj, 'metodo'], ['arg1', 'arg2'])
            // é o mesmo que $obj->metodo('arg1', 'arg2');
            call_user_func_array([$controller, $actionName], $params);
        } else {
            // Controller encontrado, mas a ação não.
            http_response_code(404);
            
            $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') || 
                      (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => "Ação '{$actionName}' não encontrada."]);
            } else {
                echo "404 - Ação '{$actionName}' não encontrada no módulo '{$controllerName}'.";
            }
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

    $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') || 
              (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => "Módulo '{$controllerName}' não encontrado."]);
    } else {
        echo "404 - Módulo '{$controllerName}' não encontrado (Classe '{$controllerClass}' não existe).";
    }
    error_log("404 DEBUG: Tentativa de acesso ao módulo '{$controllerName}' via URI: " . $_SERVER['REQUEST_URI']);
    // Adicione um log para depuração em ambiente de desenvolvimento
    error_log("ROUTER ERROR: Controller class not found: " . $controllerClass . " | URI: " . $uri);
}
