<?php

// app/config/settings.php - Configurações centrais do sistema

// Define o ambiente da aplicação (útil para logs, erros e configs específicas)
define('APP_ENV', 'development'); // Mudar para 'production' ao subir para o servidor

// ---------------------------------------------
// 1. CONFIGURAÇÃO DE ERROS E SEGURANÇA
// ---------------------------------------------

// Configuração de exibição de erros baseada no ambiente
if (APP_ENV === 'development') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    // Em ambiente de desenvolvimento, permitir que o front mostre detalhes de exceção
    if (!defined('SHOW_ERRORS')) {
        define('SHOW_ERRORS', true);
    }
} else {
    // Em produção, os erros devem ser logados, não exibidos.
    ini_set('display_errors', 0);
    error_reporting(0);
    if (!defined('SHOW_ERRORS')) {
        define('SHOW_ERRORS', false);
    }
}

// Configuração de Fuso Horário
date_default_timezone_set('America/Sao_Paulo');

// ---------------------------------------------
// 2. CONFIGURAÇÃO DE CAMINHOS E URLs
// ---------------------------------------------

// Caminho absoluto para diretórios internos (ROOT_PATH deve estar definido em public/index.php)
define('VIEWS_PATH', ROOT_PATH . '/views');
define('CORE_PATH', ROOT_PATH . '/app/core');
define('UPLOADS_DIR', ROOT_PATH . '/public/uploads'); // Pasta para documentos/anexos

// ---------------------------------------------
// 3. CONFIGURAÇÕES GERAIS DA APLICAÇÃO
// ---------------------------------------------

define('APP_NAME', 'SysEnviCorp - Sistema de Gestão Ambiental');

// Roteamento padrão
define('DEFAULT_CONTROLLER', 'Dashboard');
define('DEFAULT_ACTION', 'index');

// Chave para criptografia de sessão/dados sensíveis (MUITO IMPORTANTE EM PRODUÇÃO!)
define('SECRET_KEY', 'Seu_Segredo_Muito_Longo_e_Complexo_Aqui_12345');
