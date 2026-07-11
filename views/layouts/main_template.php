<?php
// Recupera a preferência de tema salva no cookie para aplicar a classe no servidor e evitar o flash branco no carregamento
$themePref = $_COOKIE['theme'] ?? 'light';
$isDark = ($themePref === 'dark');

// Lógica para obter o nome do dia da semana em português
$dayOfWeekNum = date('N'); // 1 (for Monday) through 7 (for Sunday)
$portugueseDays = [
    1 => 'Segunda-feira',
    2 => 'Terça-feira',
    3 => 'Quarta-feira',
    4 => 'Quinta-feira',
    5 => 'Sexta-feira',
    6 => 'Sábado',
    7 => 'Domingo'
];
$dayName = $portugueseDays[$dayOfWeekNum];
?>
<!DOCTYPE html>
<html lang="pt-BR" class="<?= $isDark ? 'dark' : '' ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SysEnviCorp - <?php echo htmlspecialchars($pageTitle ?? 'Sistema de Gestão'); ?></title>
    <link rel="icon" type="image/png" href="<?php echo BASE_URL; ?>/public/assets/images/logo-icon.png">
    <?php
    // Verifica a data de modificação do arquivo CSS para gerar uma versão única e evitar cache antigo
    $cssFilePath = ROOT_PATH . '/public/css/output.css';
    $cssVersion = file_exists($cssFilePath) ? filemtime($cssFilePath) : time();

    // Versão específica para o global.css para forçar o recarregamento independente
    $globalCssPath = ROOT_PATH . '/public/css/global.css';
    $globalVersion = file_exists($globalCssPath) ? filemtime($globalCssPath) : time();

    // Faz o mesmo para o Chart.js para garantir que o caminho e o cache estejam corretos
    $jsChartPath = ROOT_PATH . '/public/assets/js/chart.umd.min.js';
    $jsChartVersion = file_exists($jsChartPath) ? filemtime($jsChartPath) : time();
    ?>
    <!-- Tailwind CSS Compilado (Recomendado para produção e evita bloqueios de storage/tracking) -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/output.css?v=<?php echo $cssVersion; ?>">
    <!-- Estilos Globais Customizados (3D Icons, Temas, etc) -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/global.css?v=<?php echo $globalVersion; ?>">

    <!-- Bibliotecas de Terceiros -->
    <!-- Dica: Para remover os avisos de Tracking Prevention no console, baixe estes arquivos para /public/assets/vendor/ -->
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" crossorigin="anonymous" referrerpolicy="strict-origin-when-cross-origin">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="strict-origin-when-cross-origin">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.0/dist/sweetalert2.min.css" crossorigin="anonymous" referrerpolicy="strict-origin-when-cross-origin">

    <script src="<?php echo BASE_URL; ?>/public/assets/js/chart.umd.min.js?v=<?php echo $jsChartVersion; ?>"></script>
    <style>
        :root {
            /* Sincronização com global.css */
            --sys-sidebar-width: 260px;
            --sys-sidebar-collapsed: 80px;
            
            /* Mapeamento de variáveis do Dashboard para o padrão Global */
            --db-bg: var(--sys-bg, #f0f4fa);
            --db-surface: var(--sys-surface, #ffffff);
            --db-surface2: var(--sys-surface-alt, #f8fafc);
            --db-border: var(--sys-border, #e2e8f0);
            --db-text: var(--sys-text-main, #1e293b);
            --db-text2: var(--sys-text-muted, #475569);
            --db-text3: var(--sys-text-light, #94a3b8);
            --db-accent: var(--sys-blue, #2563eb);
            --db-accent2: #38bdf8; /* Azul claro para realce */
            --db-accent-glow: rgba(56,189,248,0.1);
            
            /* Cores de Status */
            --db-green: var(--sys-green, #10b981);
            --db-red: var(--sys-red, #ef4444);
            --db-orange: #f59e0b;
            --db-purple: #a855f7;

            /* Variáveis específicas para Sidebar (herdadas do global.css se possível) */
            --sys-navy: #2563eb;
            --sys-blue-marinho: #2563eb;
            --sys-blue-soft: #dbeafe;
            --sys-text-light: #94a3b8;
            --sys-blue: #2563eb;
        }

        /* Transição suave para troca de tema em elementos principais */
        body, main, footer, .card, .db-card, .db-stat-card, .sys-header, .bg-white, .bg-gray-50, .bg-gray-100, .bg-slate-50, .bg-slate-100, .bg-slate-200, .bg-gray-200, .page-wrapper, .page-content,
        .form-control, input, select, textarea, 
        .border, .border-t, .border-b, .border-l, .border-r, hr, .table, thead, th, td, tr,
        .text-slate-900, .text-slate-800, .text-slate-700, .text-slate-600, .text-slate-500, .text-slate-400,
        .text-gray-900, .text-gray-800, .text-gray-700, .text-gray-600, .text-gray-500, .text-gray-400, .text-black,
        .text-secondary, .text-muted, h1, h2, h3, h4, h5, h6,
        img, canvas, #db-brazil-map, .leaflet-tile {
            transition: background-color 0.3s ease, border-color 0.3s ease, color 0.3s ease, box-shadow 0.3s ease, opacity 0.3s ease, filter 0.3s ease;
        }

        body {
            background-color: var(--db-bg);
            overflow-x: hidden; /* Impede que oscilações na largura criem rolagem horizontal e desestabilizem a vertical */
        }

        body.dark-theme {
            /* Variáveis base */
            --sys-bg: #0d1117;
            --sys-surface: #161b22;
            --sys-surface-alt: #1c2330;
            --sys-border: #30363d;
            --sys-text-main: #e6edf3;
            --sys-text-muted: #8b949e;

            --db-bg: var(--sys-bg);
            --db-surface: var(--sys-surface);
            --db-surface2: var(--sys-surface-alt);
            --db-border: var(--sys-border);
            --db-text: var(--sys-text-main);
            --db-text2: var(--sys-text-muted);
            --db-text3: #6e7681;
        }

        /* Overrides Globais para garantir que Telas de Perfil, Usuários e Tabelas respeitem o tema escuro */
        body.dark-theme .card,
        body.dark-theme .db-card,
        body.dark-theme .db-stat-card,
        body.dark-theme .bg-white,
        body.dark-theme .bg-gray-50,
        body.dark-theme .bg-gray-100,
        body.dark-theme .bg-slate-50,
        body.dark-theme main,
        body.dark-theme .page-wrapper,
        body.dark-theme .page-content,
        body.dark-theme .bg-slate-100,
        body.dark-theme .bg-slate-200,
        body.dark-theme .bg-gray-200,
        body.dark-theme footer,
        body.dark-theme thead,
        body.dark-theme th,
        body.dark-theme td,
        body.dark-theme tr,
        body.dark-theme tbody,
        body.dark-theme .table,
        body.dark-theme .sys-header { 
            background-color: var(--db-surface) !important; 
            color: var(--db-text) !important; 
        }
        

        body.dark-theme .form-control,
        body.dark-theme input:not([type="checkbox"]):not([type="radio"]),
        body.dark-theme select,
        body.dark-theme textarea,
        body.dark-theme .bg-gray-200,
        body.dark-theme .bg-slate-100,
        body.dark-theme .db-modal-desc-content {
            background-color: var(--db-surface2) !important;
            border-color: var(--db-border) !important;
            color: var(--db-text) !important;
        }

        body.dark-theme .text-slate-900, 
        body.dark-theme .text-slate-800,
        body.dark-theme .text-gray-900,
        body.dark-theme .text-slate-700,
        body.dark-theme .text-gray-700,
        body.dark-theme .text-black,
        body.dark-theme .text-gray-800,
        body.dark-theme h1, body.dark-theme h2, body.dark-theme h3, 
        body.dark-theme h4, body.dark-theme h5, body.dark-theme h6 { color: var(--db-text) !important; }

        body.dark-theme .text-slate-600,
        body.dark-theme .text-slate-500,
        body.dark-theme .text-slate-400,
        body.dark-theme .text-gray-600,
        body.dark-theme .text-gray-500,
        body.dark-theme .text-gray-400,
        body.dark-theme .text-secondary,
        body.dark-theme .text-muted { color: var(--db-text2) !important; }

        body.dark-theme .border, body.dark-theme .border-t, body.dark-theme .border-b, body.dark-theme .border-l, body.dark-theme .border-r, body.dark-theme hr { 
            border-color: var(--db-border) !important; 
        }

        body.dark-theme ::placeholder { color: var(--db-text3) !important; opacity: 1; }
        
        body.dark-theme .hover\:bg-gray-100:hover { background-color: var(--db-surface2) !important; }
        body.dark-theme .hover\:bg-gray-100:hover, body.dark-theme .hover\:bg-gray-50:hover { background-color: var(--db-surface2) !important; }

        .sys-layout {
            display: flex;
            min-height: 100vh;
        }

        /* Melhoria na transição da Sidebar para evitar saltos no layout */
        #sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sys-sidebar-width);
            background-color: var(--sys-navy, #2563eb);
            color: white;
            z-index: 50;
            display: flex;
            flex-direction: column;
            box-shadow: 4px 0 10px rgba(0,0,0,0.1);
        }

        #sidebar.collapsed {
            width: var(--sys-sidebar-collapsed);
        }

        /* Ajustes para o estado recolhido */
        #sidebar.collapsed .sidebar-text,
        #sidebar.collapsed .sidebar-brand-text {
            display: none;
        }

        #sidebar.collapsed #sidebar-header {
            justify-content: center;
            padding: 0 4px;
        }

        #sidebar.collapsed .sidebar-logo-icon {
            width: 36px;
            height: 36px;
        }

        #sidebar.collapsed .sidebar-logo-wrap {
            flex: 0;
        }

        #sidebar.collapsed .sys-sidebar-item {
            justify-content: center;
            padding: 0.75rem 0;
            margin: 0.125rem 0.25rem;
            gap: 0;
        }

        .sys-main {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            width: calc(100% - var(--sys-sidebar-width)); /* Ajusta a largura real descontando a sidebar */
            margin-left: var(--sys-sidebar-width);
            transition: margin-left 0.3s ease, width 0.3s ease;
            overflow-anchor: none; /* Desativa o algoritmo do navegador que tenta "adivinhar" para onde rolar durante mudanças de layout */
        }

        .collapsed + .sys-main {
            margin-left: var(--sys-sidebar-collapsed);
            width: calc(100% - var(--sys-sidebar-collapsed));
        }

        .sys-header {
            background-color: var(--db-surface);
            border-bottom: 1px solid var(--db-border);
            padding: 0 1.5rem;
            height: 64px; /* Alinhado com h-16 da sidebar */
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* Animação do Spinner */
        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .spinner {
            animation: spin 1s linear infinite;
        }
        
        /* --- Animações de Ícones --- */
        @keyframes bell-swing {
            0% { transform: rotate(0); }
            15% { transform: rotate(15deg); }
            30% { transform: rotate(-10deg); }
            45% { transform: rotate(10deg); }
            60% { transform: rotate(-5deg); }
            75% { transform: rotate(2deg); }
            100% { transform: rotate(0); }
        }

        #notification-bell:hover i {
            display: inline-block;
            animation: bell-swing 0.6s ease-in-out;
        }

        @keyframes pulse-red {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); }
            70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(239, 68, 68, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
        }

        .notification-pulse { animation: pulse-red 2s infinite; }

        @keyframes theme-icon-spin {
            0% { transform: rotate(0deg) scale(0.5); opacity: 0; }
            100% { transform: rotate(360deg) scale(1); opacity: 1; }
        }

        .theme-animate { animation: theme-icon-spin 0.4s cubic-bezier(0.34, 1.56, 0.64, 1) forwards; }

        /* --- Custom Scrollbar for Sidebar --- */
        .scrollbar-hide::-webkit-scrollbar {
            display: none;
        }
        .scrollbar-hide {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        #sidebar-nav.show-scrollbar::-webkit-scrollbar {
            display: block;
            width: 4px;
        }
        #sidebar-nav.show-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 10px;
        }
        #sidebar-nav.show-scrollbar {
            -ms-overflow-style: auto;
            scrollbar-width: thin;
        }
    </style>
</head>

<body class="<?php echo $isDark ? 'dark-theme' : ''; ?>">

    <div class="sys-layout">
        <!-- Sidebar Azul Marinho -->
        <aside id="sidebar" class="transition-all duration-300 ease-in-out">
        <?php
        $sidebarPath = ROOT_PATH . '/views/layouts/sidebar.php';
        if (file_exists($sidebarPath)) {
            require_once $sidebarPath;
        } else {
            echo '<div class="p-4 text-white"><p>Erro: Sidebar não encontrada.</p></div>';
        }
        ?>
        </aside>

        <!-- Main content -->
        <div id="main-content" class="sys-main transition-all duration-300 ease-in-out">
            <header class="sys-header">
                <div class="flex items-center flex-1">
                    <!-- Botão de Menu agora visível em todas as telas -->
                    <button id="menu-button" class="text-slate-500 hover:text-slate-700 focus:outline-none p-2 rounded-lg hover:bg-slate-100 transition-all">
                        <i class='bx bx-menu-alt-left text-2xl'></i>
                    </button>
                    <div class="ml-2 sm:ml-4 border-l pl-4 border-slate-200 block">
                        <h2 class="text-lg font-bold text-slate-800 leading-tight" id="page-title"><?php echo $pageTitle ?? 'Sistema'; ?></h2>
                        <p class="text-[9px] text-slate-500 dark:text-slate-400 font-medium tracking-widest leading-none mt-1" style="text-transform: none !important; white-space: nowrap;">
                            <i class='bx bx-calendar-alt mr-1'></i><?php echo $dayName . ', ' . (!empty($currentDateFormatted) ? $currentDateFormatted : date('d/m/Y')); ?>
                        </p>
                    </div>
                </div>
                <div class="flex items-center space-x-2 sm:space-x-4">
                    <!-- Botão de Alertas (Sino) -->
                    <div class="relative">
                        <button id="notification-bell" class="p-2 rounded-full text-slate-500 hover:bg-slate-100 hover:text-blue-600 transition-all flex items-center gap-1.5 relative group" title="Notificações">
                            <i class='bx bx-bell text-xl'></i>
                            <span id="notif-badge" class="bg-red-500 text-white text-[10px] font-bold min-w-[18px] px-1 h-[18px] flex items-center justify-center rounded-full group-hover:scale-110 transition-transform notification-pulse <?php echo ($unreadNotificationCount ?? 0) > 0 ? '' : 'hidden'; ?>">
                                <?php echo ($unreadNotificationCount ?? 0) > 9 ? '9+' : ($unreadNotificationCount ?? 0); ?>
                            </span>
                        </button>
                        <!-- Dropdown de Notificações -->
                        <div id="notif-dropdown" class="hidden absolute right-0 mt-2 w-80 bg-white dark:bg-gray-800 rounded-lg shadow-xl py-2 z-50 border border-slate-100 dark:border-gray-700 ring-1 ring-black ring-opacity-5">
                            <div class="px-4 py-2 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                                <h3 class="text-sm font-bold text-gray-700 dark:text-white">Notificações</h3>
                                <button id="mark-all-read" class="text-blue-600 hover:text-blue-800 transition-colors p-1 rounded-md" title="Marcar todas como lidas">
                                    <i class='bx bx-trash text-base'></i>
                                </button>
                            </div>
                            <div id="notif-list" class="max-h-96 overflow-y-auto">
                                <!-- Itens inseridos via JS -->
                            </div>
                            <div class="px-4 py-2 border-t border-gray-100 dark:border-gray-700 text-center">
                                <a href="<?php echo BASE_URL; ?>/" class="text-xs text-gray-500 hover:text-blue-600 font-medium">Ver tudo no Dashboard</a>
                            </div>
                        </div>
                    </div>

                    <button id="theme-toggle" class="p-2.5 rounded-full hover:bg-slate-100 transition-colors text-slate-500" title="Alternar tema">
                        <i class='bx <?php echo $isDark ? 'bx-sun' : 'bx-moon'; ?> text-xl' id="theme-icon"></i>
                    </button>
                    
                    <div class="h-8 w-px bg-slate-200 mx-2 hidden md:block"></div>

                    <div class="relative inline-block text-left">
                        <?php 
                            // Busca o nome do usuário da sessão caso não tenha sido passado pelo controller
                            $displayUserName = (!empty($userName)) ? $userName : ($_SESSION['user_name'] ?? 'Usuário');
                            $nameParts = explode(' ', trim($displayUserName));
                            $firstName = $nameParts[0];
                            $initials = strtoupper(substr($firstName, 0, 1));
                            if (count($nameParts) > 1) $initials .= strtoupper(substr(end($nameParts), 0, 1));
                        ?>
                        <button id="user-menu-button" class="flex items-center space-x-1.5 focus:outline-none group pl-2" title="<?php echo htmlspecialchars($displayUserName); ?>">
                            <div class="text-right">
                                <p class="text-xs sm:text-sm text-slate-700 dark:text-white leading-tight group-hover:text-blue-600 transition-all">
                                    Olá, <?php echo htmlspecialchars($firstName); ?>!
                                </p>
                            </div>
                            <div class="w-8 h-8 rounded-full overflow-hidden border-2 border-white shadow-sm group-hover:scale-105 transition-all flex-shrink-0">
                                <img src="<?php echo $currentUserPhoto; ?>" alt="Avatar" class="w-full h-full object-cover">
                            </div>
                        </button>
                        <!-- Dropdown do Usuário -->
                        <div id="user-menu" class="hidden absolute right-0 top-full mt-2 w-48 bg-white rounded-md shadow-xl py-1 z-50 border border-slate-100 ring-1 ring-black ring-opacity-5">
                            <a href="<?php echo rtrim(BASE_URL, '/'); ?>/usuario/perfil" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Meu Perfil</a>
                            <a href="<?php echo rtrim(BASE_URL, '/'); ?>/auth/logout" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Sair</a>
                        </div>
                    </div>
                </div>
            </header>

            <?php
            // Renderiza a flash message, se houver
            $this->renderFlashMessage();
            ?>

            <main class="p-6">
                <!-- Conteúdo dinâmico da View -->
                <?php echo $content ?? '<p>Nenhum conteúdo carregado.</p>'; ?>
            </main>

            <!-- Inclusão do Rodapé -->
            <?php require_once ROOT_PATH . '/views/layouts/footer.php'; ?>
        </div>
    </div>

    <!-- SweetAlert2 JS via CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.0/dist/sweetalert2.all.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <!-- Configurações Globais para JavaScript -->
    <script>
        window.SYS_BASE_URL = "<?= rtrim(BASE_URL, '/') ?>";
    </script>

    <!-- Radar de Inteligência Artificial - Server-Sent Events -->
    <script src="<?= BASE_URL ?>/public/assets/js/ia_events.js"></script>

    <!-- Scripts da Página -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // --- LÓGICA DO NOVO SIDEBAR ---
            const sidebar = document.getElementById('sidebar');
            const sidebarHeader = document.getElementById('sidebar-header');
            const menuButton = document.getElementById('menu-button'); // Botão no header principal
            const mainContent = document.getElementById('main-content');

            // --- LÓGICA DE TEMA ---
            const themeToggle = document.getElementById('theme-toggle');
            const themeIcon = document.getElementById('theme-icon');
            
            const updateThemeUI = (isDark) => {
                if (themeIcon) {
                    // Reinicia a animação removendo e adicionando a classe
                    themeIcon.classList.remove('theme-animate');
                    void themeIcon.offsetWidth; // Truque para forçar o navegador a reiniciar a animação CSS
                    themeIcon.className = (isDark ? 'bx bx-sun text-xl' : 'bx bx-moon text-xl') + ' theme-animate';
                }
            };

            // Inicializa ícone
            updateThemeUI(document.body.classList.contains('dark-theme'));

            if (themeToggle) {
                themeToggle.addEventListener('click', () => {
                    document.body.classList.toggle('dark-theme');
                    const isDark = document.documentElement.classList.toggle('dark');
                    localStorage.setItem('theme', isDark ? 'dark' : 'light');
                    updateThemeUI(isDark);
                    document.cookie = "theme=" + (isDark ? 'dark' : 'light') + ";path=/;max-age=31536000";
                });
            }

            // --- LÓGICA DE NOTIFICAÇÕES (SINO) ---
            const badge = document.getElementById('notif-badge');
            const bellBtn = document.getElementById('notification-bell');
            const notifDropdown = document.getElementById('notif-dropdown');
            const notifList = document.getElementById('notif-list');
            const markAllReadBtn = document.getElementById('mark-all-read');

            // Função Global para marcar notificações como lidas (disponível em todo o sistema)
            window.dbMarcarNotificacaoLida = async function(e, id, url) {
                if (e) e.preventDefault();
                try {
                    const fd = new FormData();
                    if (id > 0) fd.append('id', id);
                    
                    await fetch('<?php echo rtrim(BASE_URL, "/"); ?>/notificacoes/marcarLida', {
                        method: 'POST',
                        body: fd
                    });
                    
                    if (url && url !== '#') window.location.href = url;
                    else updateNotifications();
                } catch (err) {
                    console.error('Erro ao marcar como lida:', err);
                    if (url && url !== '#') window.location.href = url;
                }
            };

            const updateNotifications = async () => {
                try {
                    const response = await fetch('<?php echo rtrim(BASE_URL, "/"); ?>/notificacoes/check');
                    const result = await response.json();
                    
                    if (result.success && result.data && result.data.length > 0) {
                        badge.classList.remove('hidden');
                        badge.textContent = result.data.length > 9 ? '9+' : result.data.length;
                        badge.title = `${result.data.length} novas notificações`;
                        
                        // Popula o dropdown com as últimas 5 notificações
                        const last5 = result.data.slice(0, 5);
                        notifList.innerHTML = last5.map(n => `
                            <a href="${n.link || '#'}" class="block px-4 py-2.5 hover:bg-gray-50 dark:hover:bg-gray-700 border-b border-gray-50 dark:border-gray-700 last:border-0 transition-colors" onclick="dbMarcarNotificacaoLida(event, ${n.id}, '${n.link || '#'}')">
                                <div class="flex items-start">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-xs font-bold text-gray-900 dark:text-white truncate tracking-tight">${n.titulo}</p>
                                        <p class="text-[10px] text-gray-500 dark:text-gray-400 mt-0.5 line-clamp-2 leading-snug">${n.mensagem}</p>
                                        <p class="text-[8px] uppercase font-semibold text-gray-400 mt-1.5 tracking-wider">${new Date(n.created_at).toLocaleString('pt-BR', {day:'2-digit', month:'2-digit', hour:'2-digit', minute:'2-digit'})}</p>
                                    </div>
                                </div>
                            </a>
                        `).join('');
                    } else {
                        badge.classList.add('hidden');
                        notifList.innerHTML = '<div class="py-10 text-center"><i class="bx bx-bell-off text-3xl text-gray-200 mb-2"></i><p class="text-xs text-gray-400 italic">Nenhuma notificação nova.</p></div>';
                    }
                } catch (error) {
                    console.error('Erro ao verificar notificações:', error);
                }
            };

            updateNotifications();

            // --- CONEXÃO SSE PARA NOTIFICAÇÕES EM TEMPO REAL ---
            if (!!window.EventSource) {
                // Conecta ao endpoint configurado para monitorar o banco de dados
                const sseSource = new EventSource("<?= rtrim(BASE_URL, '/') ?>/public_sse.php");

                sseSource.addEventListener('notificacao', function(e) {
                    const data = JSON.parse(e.data);
                    
                    // 1. Sincroniza a interface (badge e dropdown) chamando a função existente
                    updateNotifications();

                    // 2. Alerta Sonoro (caminho padrão de sons)
                    const alertSound = new Audio("<?= rtrim(BASE_URL, '/') ?>/assets/sounds/chime.mp3");
                    alertSound.play().catch(() => console.log("Som de notificação aguardando interação do usuário."));

                    // 3. Alerta Visual (Toast) via SweetAlert2 (já incluído no template)
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 6000,
                            timerProgressBar: true,
                            icon: 'info',
                            title: data.titulo,
                            text: data.mensagem
                        });
                    }
                });

                sseSource.onerror = function(e) {
                    if (e.target.readyState == EventSource.CLOSED) {
                        console.log('Conexão SSE encerrada.');
                    }
                };
            }

            if (bellBtn) {
                bellBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    notifDropdown.classList.toggle('hidden');
                    if (typeof userMenu !== 'undefined' && userMenu) userMenu.classList.add('hidden');
                });
            }

            if (markAllReadBtn) {
                markAllReadBtn.addEventListener('click', async (e) => {
                    e.preventDefault(); e.stopPropagation();
                    await dbMarcarNotificacaoLida(null, 0, null); // ID 0 indica limpar tudo
                    notifDropdown.classList.add('hidden');
                });
            }

            // Fechar dropdown ao clicar fora
            document.addEventListener('click', (event) => {
                if (notifDropdown && !bellBtn.contains(event.target) && !notifDropdown.contains(event.target)) {
                    notifDropdown.classList.add('hidden');
                }
            });

            const toggleSidebar = () => {
                if (!sidebar || !mainContent) return;

                // A classe .collapsed agora controla a visibilidade do texto via CSS
                sidebar.classList.toggle('collapsed');
            }

            if (sidebarHeader) {
                sidebarHeader.addEventListener('click', toggleSidebar);
            }
            if (menuButton) {
                menuButton.addEventListener('click', toggleSidebar);
            }

            // Lógica para os submenus (dropdowns)
            const sidebarNav = document.getElementById('sidebar-nav');
            document.querySelectorAll('.has-submenu').forEach(item => {
                const link = item.querySelector('a');
                const submenu = item.querySelector('ul');
                const arrow = item.querySelector('.submenu-arrow');

                link.addEventListener('click', (e) => {
                    e.preventDefault();
                    submenu.classList.toggle('hidden');
                    arrow.classList.toggle('rotate-180');

                    // Verifica se algum submenu está visível para mostrar a scrollbar
                    setTimeout(() => {
                        const hasOpenSubmenu = !!document.querySelector('#sidebar-nav .has-submenu ul:not(.hidden)');
                        if (hasOpenSubmenu) {
                            sidebarNav?.classList.add('show-scrollbar');
                            sidebarNav?.classList.remove('scrollbar-hide');
                        } else {
                            sidebarNav?.classList.remove('show-scrollbar');
                            sidebarNav?.classList.add('scrollbar-hide');
                        }
                    }, 50);
                });
            });

            // Lógica para o menu dropdown do usuário
            const userMenuButton = document.getElementById('user-menu-button');
            const userMenu = document.getElementById('user-menu');

            if (userMenuButton) {
                userMenuButton.addEventListener('click', () => {
                    userMenu.classList.toggle('hidden');
                });
                // Fecha o menu se clicar fora
                document.addEventListener('click', (event) => {
                    if (!userMenuButton.contains(event.target) && !userMenu.contains(event.target)) {
                        userMenu.classList.add('hidden');
                    }
                });
            }

            // --- LÓGICA PARA EXIBIR ALERTA DO SISTEMA (Manutenção/Atualização) ---
            <?php if (isset($alerta_sistema) && $alerta_sistema): ?>
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: `<span style="font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 800; color: #1e293b;"><?= htmlspecialchars($alerta_sistema['titulo']) ?></span>`,
                    html: `<div style="font-family: 'Plus Jakarta Sans', sans-serif; color: #475569; line-height: 1.6; font-size: 0.95rem;"><?= nl2br($alerta_sistema['mensagem']) ?></div>`,
                    icon: '<?= ($alerta_sistema['tipo'] === 'danger') ? 'error' : $alerta_sistema['tipo'] ?>', // Ajuste para compatibilidade
                    confirmButtonText: 'Entendido',
                    confirmButtonColor: '#4f46e5',
                    padding: '1.5rem',
                    width: '30rem',
                    background: '#ffffff',
                    backdrop: `rgba(15, 23, 42, 0.4) blur(4px)`,
                    customClass: {
                        confirmButton: 'px-10 py-2.5 rounded-xl font-bold text-sm tracking-tight',
                        popup: 'rounded-3xl shadow-2xl border border-slate-100'
                    }
                });
            }
            <?php endif; ?>
        });
    </script>
</body>

</html>