<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SysEnviCorp - <?php echo htmlspecialchars($pageTitle ?? 'Sistema de Gestão'); ?></title>
    <?php
        // Verifica a data de modificação do arquivo CSS para gerar uma versão única e evitar cache antigo
        $cssFilePath = ROOT_PATH . '/public/css/output.css';
        $cssVersion = file_exists($cssFilePath) ? filemtime($cssFilePath) : time();
    ?>
    <link href="<?php echo BASE_URL; ?>/css/output.css?v=<?php echo $cssVersion; ?>" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        .sidebar-item-active {
            background-color: #38bdf8;
            color: white;
        }

        /* Estilos para a transição suave da modal */
        .modal-transition {
            transition: opacity 0.3s ease, transform 0.3s ease;
        }

        .modal-hidden {
            opacity: 0;
            transform: scale(0.95);
            pointer-events: none;
        }

        .modal-visible {
            opacity: 1;
            transform: scale(1);
            pointer-events: auto;
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
    </style>
</head>

<body class="bg-gray-100 text-gray-800">

    <div class="flex h-screen bg-gray-200">
        <!-- Sidebar - Código fixo (seria incluído por um include separado em um projeto maior) -->
        <aside id="sidebar" class="w-64 bg-gray-800 text-white p-4 fixed inset-y-0 left-0 transform -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out z-50 overflow-y-auto">
            <div class="flex items-center space-x-2 mb-8">
                <span class="bg-sky-500 p-2 rounded-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </span>
                <h1 class="text-xl font-bold">Gestão Empresarial</h1>
            </div>

            <nav id="nav-menu">
                <ul>
                    <!-- Os links agora apontam para a URL /Controller/Action -->
                    <li><a href="<?php echo BASE_URL; ?>/" class="flex items-center space-x-3 p-2 rounded-md hover:bg-gray-700 sidebar-item"><span>📊</span><span>Dashboard</span></a></li>
                    <!-- Menu Administrativo com Sub-itens -->
                    <li>
                        <a href="#" id="admin-menu-toggle" class="w-full flex justify-between items-center space-x-3 p-2 rounded-md hover:bg-gray-700 sidebar-item">
                            <span class="flex items-center space-x-3">
                                <span>⚙️</span>
                                <span>Administrativo</span>
                            </span>
                            <svg id="admin-menu-arrow" class="w-4 h-4 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </a>
                        <ul id="admin-submenu" class="hidden pl-8 mt-2 space-y-2">
                            <!-- Submenu de Financeiro dentro de Administrativo -->
                            <li>
                                <a href="#" id="financeiro-menu-toggle" class="w-full flex justify-between items-center p-2 rounded-md hover:bg-gray-700 text-sm sidebar-item">
                                    <span class="flex items-center">💰 Financeiro</span>
                                    <svg id="financeiro-menu-arrow" class="w-4 h-4 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </a>
                                <ul id="financeiro-submenu" class="hidden pl-4 mt-2 space-y-2">
                                    <li><a href="<?php echo BASE_URL; ?>/financeiro" class="block p-1 rounded-md hover:bg-gray-600 text-xs">Dashboard Financeiro</a></li>
                                    <li><a href="<?php echo BASE_URL; ?>/banco" class="block p-1 rounded-md hover:bg-gray-600 text-xs">Bancos e Contas</a></li>
                                    <li><a href="<?php echo BASE_URL; ?>/financeiro/pagar" class="block p-1 rounded-md hover:bg-gray-600 text-xs">🔴 Pagamentos</a></li>
                                    <li><a href="<?php echo BASE_URL; ?>/financeiro/receber" class="block p-1 rounded-md hover:bg-gray-600 text-xs">🟢 Recebimentos</a></li>
                                    <li><a href="<?php echo BASE_URL; ?>/classificacao" class="block p-1 rounded-md hover:bg-gray-600 text-xs">🗂️ Gerenciar Categorias</a></li>
                                    <li><a href="<?php echo BASE_URL; ?>/centrocusto" class="block p-1 rounded-md hover:bg-gray-600 text-xs">🏢 Gerenciar Centros de Custo</a></li>
                                    <li><a href="<?php echo BASE_URL; ?>/clientes" class="block p-1 rounded-md hover:bg-gray-600 text-xs">👤 Clientes</a></li>
                                    <li><a href="<?php echo BASE_URL; ?>/fornecedores" class="block p-1 rounded-md hover:bg-gray-600 text-xs">🚚 Fornecedores</a></li>
                                    <!-- Submenu de Faturamento dentro de Financeiro -->
                                    <li>
                                        <a href="#" id="faturamento-menu-toggle" class="w-full flex justify-between items-center p-1 rounded-md hover:bg-gray-600 text-xs sidebar-item">
                                            <span class="flex items-center">🧾 Faturamento</span>
                                            <svg id="faturamento-menu-arrow" class="w-3 h-3 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                            </svg>
                                        </a>
                                        <ul id="faturamento-submenu" class="hidden pl-4 mt-2 space-y-2">
                                            <!-- O link pode apontar para /faturamento/notaFiscal ou similar -->
                                            <li><a href="<?php echo BASE_URL; ?>/notaFiscal" class="block p-1 rounded-md hover:bg-gray-500 text-xs">Nota Fiscal</a></li>
                                        </ul>
                                    </li>
                                    <li><a href="#" onclick="console.log('Gerando relatório financeiro...')" class="block p-1 rounded-md hover:bg-gray-600 text-xs">Gerar Relatório</a></li>
                                    <!-- Menu Gestão de Bens e Ativos movido para cá -->
                                    <li>
                                        <a href="#" id="patrimonio-menu-toggle" class="w-full flex justify-between items-center p-1 rounded-md hover:bg-gray-600 text-xs sidebar-item">
                                            <span class="flex items-center">🏢 Gestão de Bens e Ativos</span>
                                            <svg id="patrimonio-menu-arrow" class="w-3 h-3 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                            </svg>
                                        </a>
                                        <ul id="patrimonio-submenu" class="hidden pl-4 mt-2 space-y-2">
                                            <li><a href="<?php echo BASE_URL; ?>/patrimonio" class="block p-1 rounded-md hover:bg-gray-500 text-xs">Dashboard de Ativos</a></li>
                                            <li><a href="<?php echo BASE_URL; ?>/patrimonio/cadastro" class="block p-1 rounded-md hover:bg-gray-500 text-xs">Cadastro de Bens</a></li>
                                            <li><a href="<?php echo BASE_URL; ?>/patrimonio/movimentacoes" class="block p-1 rounded-md hover:bg-gray-500 text-xs">Controle de Movimentações</a></li>
                                            <li><a href="<?php echo BASE_URL; ?>/patrimonio/depreciacao" class="block p-1 rounded-md hover:bg-gray-500 text-xs">Depreciação e Reavaliação</a></li>
                                            <li><a href="<?php echo BASE_URL; ?>/patrimonio/inventario" class="block p-1 rounded-md hover:bg-gray-500 text-xs">Inventário Patrimonial</a></li>
                                            <li><a href="<?php echo BASE_URL; ?>/patrimonio/relatorios" class="block p-1 rounded-md hover:bg-gray-500 text-xs">Relatórios e Indicadores</a></li>
                                        </ul>
                                    </li>
                                </ul>
                            </li>
                            <li>
                                <a href="#" id="rh-menu-toggle" class="w-full flex justify-between items-center p-2 rounded-md hover:bg-gray-700 text-sm sidebar-item">
                                    <span class="flex items-center">👥 RH</span>
                                    <svg id="rh-menu-arrow" class="w-4 h-4 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </a>
                                <ul id="rh-submenu" class="hidden pl-4 mt-2 space-y-2">
                                    <li><a href="<?php echo BASE_URL; ?>/rh" class="block p-1 rounded-md hover:bg-gray-600 text-xs">Dashboard</a></li>
                                    <li><a href="<?php echo BASE_URL; ?>/treinamentos" class="block p-1 rounded-md hover:bg-gray-600 text-xs">Treinamentos</a></li>
                                    <li><a href="<?php echo BASE_URL; ?>/rh/registroFuncionario" class="block p-1 rounded-md hover:bg-gray-600 text-xs">Cadastrar Funcionário</a></li>
                                    <li>
                                        <a href="#" id="calculos-rh-menu-toggle" class="w-full flex justify-between items-center p-1 rounded-md hover:bg-gray-600 text-xs sidebar-item">
                                            <span class="flex items-center">🧮 Cálculos</span>
                                            <svg id="calculos-rh-menu-arrow" class="w-3 h-3 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                            </svg>
                                        </a>
                                        <ul id="calculos-rh-submenu" class="hidden pl-4 mt-2 space-y-2">
                                            <li><a href="<?php echo BASE_URL; ?>/rh/folhaDePagamento" class="block p-1 rounded-md hover:bg-gray-500 text-xs">Folha de Pagamento</a></li>
                                            <li><a href="<?php echo BASE_URL; ?>/rh/calculoRescisao" class="block p-1 rounded-md hover:bg-gray-500 text-xs">Cálculo de Rescisão</a></li>
                                            <li><a href="<?php echo BASE_URL; ?>/rh/calculoFerias" class="block p-1 rounded-md hover:bg-gray-500 text-xs">Novo Cálculo de Férias</a></li>
                                            <li><a href="<?php echo BASE_URL; ?>/rh/historicoFerias" class="block p-1 rounded-md hover:bg-gray-500 text-xs">Histórico de Férias</a></li>
                                        </ul>
                                    </li>
                                    <li>
                                        <a href="#" id="relatorios-rh-menu-toggle" class="w-full flex justify-between items-center p-1 rounded-md hover:bg-gray-600 text-xs sidebar-item">
                                            <span class="flex items-center">📈 Relatórios</span>
                                            <svg id="relatorios-rh-menu-arrow" class="w-3 h-3 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                            </svg>
                                        </a>
                                        <ul id="relatorios-rh-submenu" class="hidden pl-4 mt-2 space-y-2">
                                            <li><a href="<?php echo BASE_URL; ?>/rh/relatorios" class="block p-1 rounded-md hover:bg-gray-500 text-xs">Indicadores</a></li>
                                            <li><a href="<?php echo BASE_URL; ?>/rh/relatorioFichaCadastral" class="block p-1 rounded-md hover:bg-gray-500 text-xs">Ficha Cadastral</a></li>
                                        </ul>
                                    </li>
                                </ul>
                            </li>
                            <!-- Novo Submenu Jurídico -->
                            <li>
                                <a href="#" id="juridico-menu-toggle" class="w-full flex justify-between items-center p-2 rounded-md hover:bg-gray-700 text-sm sidebar-item">
                                    <span class="flex items-center">⚖️ Jurídico</span>
                                    <svg id="juridico-menu-arrow" class="w-4 h-4 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </a>
                                <ul id="juridico-submenu" class="hidden pl-4 mt-2 space-y-2">
                                    <!-- Links do Jurídico serão adicionados aqui -->
                                </ul>
                            </li>
                            <li><a href="<?php echo BASE_URL; ?>/organograma" class="block p-2 rounded-md hover:bg-gray-700 text-sm">🧬 Organograma</a></li>
                        </ul>
                    </li>
                    <!-- Menu Comercial com Sub-itens -->
                    <li>
                        <a href="#" id="comercial-menu-toggle" class="w-full flex justify-between items-center space-x-3 p-2 rounded-md hover:bg-gray-700 sidebar-item">
                            <span class="flex items-center space-x-3">
                                <span>🤝</span>
                                <span>Comercial</span>
                            </span>
                            <svg id="comercial-menu-arrow" class="w-4 h-4 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </a>
                        <ul id="comercial-submenu" class="hidden pl-8 mt-2 space-y-2">
                            <li><a href="<?php echo BASE_URL; ?>/orcamento/proposta" class="block p-2 rounded-md hover:bg-gray-700 text-sm">💰 Orçamento-Proposta</a></li>
                            <li><a href="<?php echo BASE_URL; ?>/orcamento/comercial" class="block p-2 rounded-md hover:bg-gray-700 text-sm">📊 Orçamento-Comercial</a></li>
                            <li><a href="<?php echo BASE_URL; ?>/licitacoes" class="block p-2 rounded-md hover:bg-gray-700 text-sm">🏆 Licitações</a></li>
                            <!-- Submenu de Contratos -->
                            <li>
                                <a href="#" id="contratos-menu-toggle" class="w-full flex justify-between items-center p-2 rounded-md hover:bg-gray-700 text-sm sidebar-item">
                                    <span class="flex items-center">✍️ Contratos</span>
                                    <svg id="contratos-menu-arrow" class="w-4 h-4 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </a>
                                <ul id="contratos-submenu" class="hidden pl-4 mt-2 space-y-2">
                                    <li><a href="<?php echo BASE_URL; ?>/contratos" class="block p-1 rounded-md hover:bg-gray-600 text-xs">Cadastro de Contratos</a></li>
                                    <li><a href="<?php echo BASE_URL; ?>/contratos/vigencia" class="block p-1 rounded-md hover:bg-gray-600 text-xs">Gestão de Vigência</a></li>
                                    <li><a href="<?php echo BASE_URL; ?>/contratos/obrigacoes" class="block p-1 rounded-md hover:bg-gray-600 text-xs">Obrigações e Cláusulas</a></li>
                                    <li><a href="<?php echo BASE_URL; ?>/contratos/financeiro" class="block p-1 rounded-md hover:bg-gray-600 text-xs">Financeiro Integrado</a></li>
                                    <li><a href="<?php echo BASE_URL; ?>/contratos/compliance" class="block p-1 rounded-md hover:bg-gray-600 text-xs">Compliance e Jurídico</a></li>
                                    <!-- Novo Submenu de Ações e Alertas -->
                                    <li>
                                        <a href="#" id="contratos-alertas-menu-toggle" class="w-full flex justify-between items-center p-1 rounded-md hover:bg-gray-600 text-xs sidebar-item">
                                            <span class="flex items-center">🔔 Ações e Alertas</span>
                                            <svg id="contratos-alertas-menu-arrow" class="w-3 h-3 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                            </svg>
                                        </a>
                                        <ul id="contratos-alertas-submenu" class="hidden pl-4 mt-2 space-y-2">
                                            <li><a href="<?php echo BASE_URL; ?>/contratos/enviarAlerta" class="block p-1 rounded-md hover:bg-gray-500 text-xs">Enviar Alerta de Renovação</a></li>
                                            <li><a href="<?php echo BASE_URL; ?>/contratos/uploadDocumento" class="block p-1 rounded-md hover:bg-gray-500 text-xs">Upload de Documento (PDF)</a></li>
                                        </ul>
                                    </li>
                                    <li><a href="<?php echo BASE_URL; ?>/contratos/relatorios" class="block p-1 rounded-md hover:bg-gray-600 text-xs">Relatórios e Indicadores</a></li>
                                </ul>
                            </li>
                        </ul>
                    </li>
                    <!-- Menu Gestão Técnica e Documental com Sub-itens -->
                    <li>
                        <a href="#" id="gestao-tecnica-menu-toggle" class="w-full flex justify-between items-center space-x-3 p-2 rounded-md hover:bg-gray-700 sidebar-item">
                            <span class="flex items-center space-x-3">
                                <span>📚</span>
                                <span>Gestão Técnica</span>
                            </span>
                            <svg id="gestao-tecnica-menu-arrow" class="w-4 h-4 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </a>
                        <ul id="gestao-tecnica-submenu" class="hidden pl-8 mt-2 space-y-2">
                            <li><a href="<?php echo BASE_URL; ?>/licencasOperacao" class="block p-2 rounded-md hover:bg-gray-700 text-sm">📜 Licenças</a></li>
                            <li><a href="<?php echo BASE_URL; ?>/pops" class="block p-2 rounded-md hover:bg-gray-700 text-sm">📋 POPs</a></li>
                            <!-- Menu Projetos com Sub-itens -->
                            <li>
                                <a href="#" id="projetos-menu-toggle" class="w-full flex justify-between items-center p-2 rounded-md hover:bg-gray-700 text-sm sidebar-item">
                                    <span class="flex items-center">🏗️ Projetos</span>
                                    <svg id="projetos-menu-arrow" class="w-4 h-4 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </a>
                                <ul id="projetos-submenu" class="hidden pl-4 mt-2 space-y-2">
                                    <li><a href="<?php echo BASE_URL; ?>/projetos" class="block p-1 rounded-md hover:bg-gray-600 text-xs">Dashboard de Projetos</a></li>
                                    <li><a href="<?php echo BASE_URL; ?>/prad" class="block p-1 rounded-md hover:bg-gray-600 text-xs">PRAD</a></li>
                                </ul>
                            </li>
                        </ul>
                    </li>
                    <li><a href="<?php echo BASE_URL; ?>/usuario" class="flex items-center space-x-3 p-2 rounded-md hover:bg-gray-700 sidebar-item"><span>👤</span><span>Usuários</span></a></li>
                    <!-- Menu Configurações com Sub-itens -->
                    <li>
                        <a href="#" id="config-menu-toggle" class="w-full flex justify-between items-center space-x-3 p-2 rounded-md hover:bg-gray-700 sidebar-item">
                            <span class="flex items-center space-x-3">
                                <span>🛠️</span>
                                <span>Configurações</span>
                            </span>
                            <svg id="config-menu-arrow" class="w-4 h-4 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </a>
                        <ul id="config-submenu" class="hidden pl-8 mt-2 space-y-2">
                            <li><a href="<?php echo BASE_URL; ?>/empresa" class="block p-2 rounded-md hover:bg-gray-700 text-sm">🏢 Dados da Empresa</a></li>
                            <li><a href="<?php echo BASE_URL; ?>/categorias" class="block p-2 rounded-md hover:bg-gray-700 text-sm">🗂️ Categorias e Segmentos</a></li>
                            <li><a href="<?php echo BASE_URL; ?>/configuracoes" class="block p-2 rounded-md hover:bg-gray-700 text-sm">⚙️ Gerais</a></li>
                        </ul>
                    </li>
                    <!-- Adicionar os demais links aqui -->
                </ul>
            </nav>
        </aside>

        <!-- Main content -->
        <div id="main-content" class="flex-1 flex flex-col overflow-hidden md:ml-64 transition-all duration-300 ease-in-out">
            <header class="flex justify-between items-center p-4 bg-white border-b">
                <div class="flex items-center">
                    <!-- Botão de Menu agora visível em todas as telas -->
                    <button id="menu-button" class="text-gray-500 focus:outline-none">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path>
                        </svg>
                    </button>
                    <h2 class="text-xl font-semibold ml-4" id="page-title"><?php echo $pageTitle ?? 'Sistema'; ?></h2>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-gray-600">Olá, <?php echo htmlspecialchars($this->session->get('user_name', 'Usuário')); ?></span>
                    <div class="relative">
                        <button id="user-menu-button" class="focus:outline-none">
                            <img src="<?php echo htmlspecialchars($currentUserPhoto ?? 'https://placehold.co/40x40/E2E8F0/4A5568?text=U'); ?>" alt="Avatar do Usuário" class="rounded-full" style="width:40px;height:40px;">
                        </button>
                        <!-- Dropdown do Usuário -->
                        <div id="user-menu" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50">
                            <a href="<?php echo BASE_URL; ?>/usuario/perfil" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Meu Perfil</a>
                            <a href="<?php echo BASE_URL; ?>/auth/logout" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Sair</a>
                        </div>
                    </div>
                </div>
            </header>

            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-6">
                <!-- Conteúdo dinâmico da View é injetado aqui -->
                <?php
                // Renderiza a flash message, se houver, usando a instância do controller atual.
                $this->renderFlashMessage();
                ?>
                <?php echo $content ?? '<p>Nenhum conteúdo carregado.</p>'; ?>
            </main>

            <!-- Inclusão do Rodapé -->
            <?php require_once ROOT_PATH . '/views/layouts/footer.php'; ?>

        </div>
    </div>

    <!-- Modal para Novo Projeto -->
    <div id="novo-projeto-modal" class="modal-hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 modal-transition">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center pb-3 border-b">
                <p class="text-2xl font-bold">Cadastrar Novo Projeto</p>
                <div id="close-project-modal" class="cursor-pointer z-50">
                    <svg class="fill-current text-black" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                        <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z" />
                    </svg>
                </div>
            </div>
            <!-- O conteúdo do formulário será carregado aqui dinamicamente -->
            <div id="modal-form-content" class="mt-5">
                <!-- Spinner de Carregamento -->
                <div class="flex justify-center items-center py-16">
                    <div class="spinner w-12 h-12 border-4 border-sky-500 border-t-transparent rounded-full"></div>
                    <p class="ml-4 text-gray-600">Carregando formulário...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts da Página -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const menuButton = document.getElementById('menu-button');
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('main-content');

            menuButton.addEventListener('click', function() {
                // Para telas mobile (abaixo de md)
                if (window.innerWidth < 768) {
                    sidebar.classList.toggle('-translate-x-full');
                } else { // Para telas de desktop (md e acima)
                    // Se a sidebar está visível (tem md:translate-x-0), esconde
                    if (sidebar.classList.contains('md:translate-x-0')) {
                        sidebar.classList.remove('md:translate-x-0');
                        sidebar.classList.add('md:-translate-x-full');
                        mainContent.classList.remove('md:ml-64'); // Remove a margem
                    } else { // Se a sidebar está escondida (tem md:-translate-x-full), mostra
                        sidebar.classList.remove('md:-translate-x-full');
                        sidebar.classList.add('md:translate-x-0');
                        mainContent.classList.add('md:ml-64'); // Adiciona a margem
                    }
                }
            });

            // Lógica para o submenu Administrativo
            const adminMenuToggle = document.getElementById('admin-menu-toggle');
            const adminSubmenu = document.getElementById('admin-submenu');
            const adminMenuArrow = document.getElementById('admin-menu-arrow');

            adminMenuToggle.addEventListener('click', function(e) {
                e.preventDefault(); // Impede a navegação do link '#'
                adminSubmenu.classList.toggle('hidden');
                adminMenuArrow.classList.toggle('rotate-180');
            });

            // Lógica para o submenu de RH (agora aninhado)
            // Certifique-se de que o elemento existe antes de adicionar o listener
            const rhMenuToggle = document.getElementById('rh-menu-toggle');
            const rhSubmenu = document.getElementById('rh-submenu');
            const rhMenuArrow = document.getElementById('rh-menu-arrow');

            rhMenuToggle.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation(); // Impede que o clique feche o menu pai (Administrativo)
                rhSubmenu.classList.toggle('hidden');
                rhMenuArrow.classList.toggle('rotate-180');
            });

            // Lógica para o submenu de Cálculos de RH
            const calculosRhMenuToggle = document.getElementById('calculos-rh-menu-toggle');
            const calculosRhSubmenu = document.getElementById('calculos-rh-submenu');
            const calculosRhMenuArrow = document.getElementById('calculos-rh-menu-arrow');

            if (calculosRhMenuToggle) {
                calculosRhMenuToggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    calculosRhSubmenu.classList.toggle('hidden');
                    calculosRhMenuArrow.classList.toggle('rotate-180');
                });
            }

            // Lógica para o submenu de Relatórios de RH
            const relatoriosRhMenuToggle = document.getElementById('relatorios-rh-menu-toggle');
            const relatoriosRhSubmenu = document.getElementById('relatorios-rh-submenu');
            const relatoriosRhMenuArrow = document.getElementById('relatorios-rh-menu-arrow');

            if (relatoriosRhMenuToggle) {
                relatoriosRhMenuToggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    relatoriosRhSubmenu.classList.toggle('hidden');
                    relatoriosRhMenuArrow.classList.toggle('rotate-180');
                });
            }


            // Lógica para o submenu de Financeiro (agora aninhado)
            // Certifique-se de que o elemento existe antes de adicionar o listener
            const financeiroMenuToggle = document.getElementById('financeiro-menu-toggle');
            const financeiroSubmenu = document.getElementById('financeiro-submenu');
            const financeiroMenuArrow = document.getElementById('financeiro-menu-arrow');

            financeiroMenuToggle.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation(); // Impede que o clique feche o menu pai (Administrativo)
                financeiroSubmenu.classList.toggle('hidden');
                financeiroMenuArrow.classList.toggle('rotate-180');
            });

            // Lógica para o submenu de Faturamento (aninhado em Financeiro)
            // Certifique-se de que o elemento existe antes de adicionar o listener
            const faturamentoMenuToggle = document.getElementById('faturamento-menu-toggle');
            const faturamentoSubmenu = document.getElementById('faturamento-submenu');
            const faturamentoMenuArrow = document.getElementById('faturamento-menu-arrow');

            faturamentoMenuToggle.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation(); // Impede que o clique feche os menus pais
                faturamentoSubmenu.classList.toggle('hidden');
                faturamentoMenuArrow.classList.toggle('rotate-180');
            });


            // Lógica para o submenu Comercial (Certifique-se de que o elemento existe antes de adicionar o listener)
            const comercialMenuToggle = document.getElementById('comercial-menu-toggle');
            const comercialSubmenu = document.getElementById('comercial-submenu');
            const comercialMenuArrow = document.getElementById('comercial-menu-arrow');

            if (comercialMenuToggle) {
                comercialMenuToggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    comercialSubmenu.classList.toggle('hidden');
                    comercialMenuArrow.classList.toggle('rotate-180');
                });
            }

            // Lógica para o submenu Contratos (aninhado em Comercial)
            const contratosMenuToggle = document.getElementById('contratos-menu-toggle');
            const contratosSubmenu = document.getElementById('contratos-submenu');
            const contratosMenuArrow = document.getElementById('contratos-menu-arrow');

            if (contratosMenuToggle) {
                contratosMenuToggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    contratosSubmenu.classList.toggle('hidden');
                    contratosMenuArrow.classList.toggle('rotate-180');
                });
            }

            // Lógica para o submenu de Alertas de Contratos (aninhado em Contratos)
            const alertasContratosMenuToggle = document.getElementById('contratos-alertas-menu-toggle');
            const alertasContratosSubmenu = document.getElementById('contratos-alertas-submenu');
            const alertasContratosMenuArrow = document.getElementById('contratos-alertas-menu-arrow');

            if (alertasContratosMenuToggle) {
                alertasContratosMenuToggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    alertasContratosSubmenu.classList.toggle('hidden');
                    alertasContratosMenuArrow.classList.toggle('rotate-180');
                });
            }


            // Lógica para o submenu Gestão Técnica e Documental (Certifique-se de que o elemento existe antes de adicionar o listener)
            const gestaoTecnicaMenuToggle = document.getElementById('gestao-tecnica-menu-toggle');
            const gestaoTecnicaSubmenu = document.getElementById('gestao-tecnica-submenu');
            const gestaoTecnicaMenuArrow = document.getElementById('gestao-tecnica-menu-arrow');

            if (gestaoTecnicaMenuToggle) {
                gestaoTecnicaMenuToggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    gestaoTecnicaSubmenu.classList.toggle('hidden');
                    gestaoTecnicaMenuArrow.classList.toggle('rotate-180');
                });
            }

            // Lógica para o submenu Projetos (aninhado em Gestão Técnica)
            const projetosMenuToggle = document.getElementById('projetos-menu-toggle');
            const projetosSubmenu = document.getElementById('projetos-submenu');
            const projetosMenuArrow = document.getElementById('projetos-menu-arrow');

            if (projetosMenuToggle) {
                projetosMenuToggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    projetosSubmenu.classList.toggle('hidden');
                    projetosMenuArrow.classList.toggle('rotate-180');
                });
            }

            // Lógica para o submenu Patrimônio
            const patrimonioMenuToggle = document.getElementById('patrimonio-menu-toggle');
            const patrimonioSubmenu = document.getElementById('patrimonio-submenu');
            const patrimonioMenuArrow = document.getElementById('patrimonio-menu-arrow');

            if (patrimonioMenuToggle) {
                patrimonioMenuToggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    patrimonioSubmenu.classList.toggle('hidden');
                    patrimonioMenuArrow.classList.toggle('rotate-180');
                });
            }

            // Lógica para o submenu Configurações
            const configMenuToggle = document.getElementById('config-menu-toggle');
            const configSubmenu = document.getElementById('config-submenu');
            const configMenuArrow = document.getElementById('config-menu-arrow');

            if (configMenuToggle) {
                configMenuToggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    configSubmenu.classList.toggle('hidden');
                    configMenuArrow.classList.toggle('rotate-180');
                });
            }


            // --- Lógica de Estado Ativo e Abertura de Submenus ---
            const currentPath = window.location.pathname;

            // 1. Remove a classe 'sidebar-item-active' de todos os itens para garantir que apenas o correto seja ativado
            document.querySelectorAll('.sidebar-item').forEach(item => {
                item.classList.remove('sidebar-item-active');
            });

            // 2. Encontra o link da página atual (correspondência exata) e o ativa
            let activeLinkElement = null;
            document.querySelectorAll('#nav-menu a').forEach(link => {
                try {
                    const linkUrl = new URL(link.href);
                    const linkPath = linkUrl.pathname;

                    // Normaliza os caminhos para comparação (remove a barra final, exceto para a raiz)
                    const normalizedCurrentPath = currentPath.length > 1 && currentPath.endsWith('/') ? currentPath.slice(0, -1) : currentPath;
                    const normalizedLinkPath = linkPath.length > 1 && linkPath.endsWith('/') ? linkPath.slice(0, -1) : linkPath;

                    // Lógica de correspondência aprimorada
                    const basePath = '<?php echo str_replace('/public/index.php', '', $_SERVER['SCRIPT_NAME']); ?>';
                    const isRootLink = normalizedLinkPath === basePath || normalizedLinkPath === basePath + '/';

                    // Se o link é para a raiz, só ativa se o caminho atual também for a raiz.
                    // Isso evita que o Dashboard fique ativo em todas as páginas.
                    if (isRootLink) {
                        if (normalizedCurrentPath === basePath || normalizedCurrentPath === basePath + '/') {
                            activeLinkElement = link;
                        }
                    }
                    // Para outros links, faz a correspondência exata.
                    else if (normalizedCurrentPath === normalizedLinkPath) {
                        activeLinkElement = link;
                    }
                } catch (e) {
                    // Ignora links inválidos como href="#" ou javascript:;
                }
            });

            if (activeLinkElement) {
                activeLinkElement.classList.add('sidebar-item-active');

                // 3. Percorre os elementos pai para ativar os toggles e abrir os submenus
                let parentUl = activeLinkElement.closest('ul');
                while (parentUl && parentUl.id !== 'nav-menu') { // Sobe até o menu principal
                    if (parentUl.classList.contains('hidden')) {
                        parentUl.classList.remove('hidden');
                    }

                    // Encontra o link de toggle para este UL pai (assume que é o <a> irmão anterior)
                    const toggleElement = parentUl.previousElementSibling;
                    if (toggleElement && toggleElement.tagName === 'A') {
                        toggleElement.classList.add('sidebar-item-active');
                        const arrow = toggleElement.querySelector('svg');
                        if (arrow) {
                            arrow.classList.add('rotate-180');
                        }
                    }
                    // Move para o próximo ul pai na hierarquia, evitando um loop infinito.
                    // Primeiro, sobe para o elemento pai do <ul> atual e, a partir daí,
                    // procura o próximo <ul> ancestral.
                    parentUl = parentUl.parentElement.closest('ul');
                }
            }

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

            // --- Lógica da Modal de Novo Projeto ---
            const projectModal = document.getElementById('novo-projeto-modal');
            const openProjectModalButton = document.getElementById('open-project-modal-button'); // O botão que abre a modal
            const modalFormContent = document.getElementById('modal-form-content');

            // Spinner HTML para resetar o estado de carregamento
            const spinnerHtml = `
                <div class="flex justify-center items-center py-16">
                    <div class="spinner w-12 h-12 border-4 border-sky-500 border-t-transparent rounded-full"></div>
                    <p class="ml-4 text-gray-600">Carregando formulário...</p>
                </div>`;

            // A função para fechar a modal
            const closeProjectModal = () => {
                projectModal.classList.remove('modal-visible');
                projectModal.classList.add('modal-hidden');
                // Reseta o conteúdo da modal para o spinner após a animação de saída
                setTimeout(() => {
                    modalFormContent.innerHTML = spinnerHtml;
                }, 300); // Mesmo tempo da transição CSS
            };

            // Abre a modal se o botão de abrir existir na página
            if (openProjectModalButton) {
                openProjectModalButton.addEventListener('click', async () => {
                    // Mostra a modal com o spinner
                    projectModal.classList.remove('modal-hidden');
                    projectModal.classList.add('modal-visible');

                    try {
                        // Busca o conteúdo do formulário no novo endpoint do controller
                        const response = await fetch('<?php echo BASE_URL; ?>/projetos/getFormulario');
                        if (!response.ok) {
                            throw new Error(`Falha ao carregar o formulário. Status: ${response.status}`);
                        }
                        const formHtml = await response.text();

                        // Injeta o HTML do formulário no corpo da modal
                        modalFormContent.innerHTML = formHtml;

                        // Adiciona o listener para o botão de cancelar que agora está dentro do formulário carregado
                        const cancelBtn = modalFormContent.querySelector('#cancel-project-modal, a[href="<?php echo BASE_URL; ?>/projetos"]');
                        if (cancelBtn) {
                            cancelBtn.addEventListener('click', (e) => {
                                e.preventDefault();
                                closeProjectModal();
                            });
                        }
                    } catch (error) {
                        console.error('Erro ao carregar formulário na modal:', error);
                        modalFormContent.innerHTML = '<p class="text-red-500 text-center py-16">Ocorreu um erro ao carregar o formulário. Tente novamente.</p>';
                    }
                });
            }
            // Eventos para fechar a modal
            document.getElementById('close-project-modal').addEventListener('click', closeProjectModal);
            // Fecha ao clicar fora do conteúdo da modal
            projectModal.addEventListener('click', (event) => {
                if (event.target === projectModal) {
                    closeProjectModal();
                }
            });
        });
    </script>
</body>

</html>