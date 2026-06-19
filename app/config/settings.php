<?php

// app/config/settings.php - Configurações centrais do sistema

// Define o ambiente da aplicação (útil para logs, erros e configs específicas)
define('APP_ENV', env('APP_ENV', 'development')); 

// Versão da aplicação para quebra de cache de ativos (CSS, JS, Imagens)
define('APP_VERSION', '1.0.2'); 

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
// 1.1 CONFIGURAÇÃO DE URL
// ---------------------------------------------
// IMPORTANTE: Defina a URL completa com o protocolo (http ou https).
// Em produção, configure a variável BASE_URL no seu arquivo .env
if (!defined('BASE_URL')) {
    $envBaseUrl = env('BASE_URL');
    if ($envBaseUrl) {
        define('BASE_URL', $envBaseUrl);
    }
}

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

// ---------------------------------------------
// 4. CONFIGURAÇÃO DE ENVIO DE E-MAIL (SMTP)
// ---------------------------------------------
// Use as credenciais do seu provedor de e-mail (ex: SendGrid, Amazon SES, Gmail)
// Abaixo exemplo genérico para HostGator (confira seus dados no cPanel > Contas de E-mail > Connect Devices)

define('MAIL_HOST', 'smtp.titan.email'); // Endereço padrão para conexões SMTP da Titan
define('MAIL_PORT', 587);                      // Porta 587 é mais recomendada em servidores compartilhados
define('MAIL_ENCRYPTION', 'tls');              // Usar TLS para a porta 587
define('MAIL_USERNAME', 'contato@envicorp.com.br'); // Crie um e-mail específico para o sistema
define('MAIL_PASSWORD', 'envi@#CORP01'); // Senha do e-mail (mantenha segura e complexa)

// Configurações do remetente
define('MAIL_FROM_ADDRESS', 'contato@envicorp.com.br'); // Deve ser igual ao USERNAME para evitar bloqueio
define('MAIL_FROM_NAME', 'Victor Galvão - Diretor/Sócio');

// E-mail do administrador para receber alertas e backups
define('MAIL_ADMIN_RECIPIENT', 'contato@envicorp.com.br'); // IMPORTANTE: Altere para um e-mail real

// E-mail do responsável por aprovar as prestações de contas
define('FINANCEIRO_APROVADOR_EMAIL', [
    'tassia.soares@envicorp.com.br',
    'victor.galvao@envicorp.com.br',
    'eron.serra@envicorp.com.br',
    'contato@envicorp.com.br',
    'anderson.sousa@envicorp.com.br'
]); // IMPORTANTE: Altere para o e-mail real do aprovador

// ---------------------------------------------
// 5. CONFIGURAÇÃO DE COMUNICAÇÃO (WHATSAPP)
// ---------------------------------------------
// Número oficial da empresa para contato (Formato: DDI + DDD + Número, apenas dígitos)
define('WHATSAPP_COMERCIAL', '5592981524190'); 
// Versão formatada para exibição (Máscara: (XX) XXXXX-XXXX)
define('WHATSAPP_COMERCIAL_FORMATTED', '(' . substr(WHATSAPP_COMERCIAL, 2, 2) . ') ' . substr(WHATSAPP_COMERCIAL, 4, 5) . '-' . substr(WHATSAPP_COMERCIAL, 9));
// Nome identificador para mensagens
define('WHATSAPP_SENDER_NAME', 'Comercial SysEnviCorp');