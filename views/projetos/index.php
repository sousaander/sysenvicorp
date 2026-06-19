<style>
  /* Força a atualização das variáveis quando o tema escuro está ativo */
  .dark-theme {
    --pj-bg: var(--db-bg, #111827);
    --pj-surface: var(--db-surface, #1f2937);
    --pj-surface2: var(--db-surface2, #374151);
    --pj-border: var(--db-border, #374151);
    --pj-text: var(--db-text, #f3f4f6);
    --pj-text2: var(--db-text2, #d1d5db);
  }

  .dark-theme #projects-page-container select,
  .dark-theme #projects-page-container input {
    background-color: var(--pj-surface);
    color: var(--pj-text);
    border-color: var(--pj-border);
  }

  /* Estilos base para o contêiner da página de projetos */
  #projects-page-container {
    color: var(--pj-text);
  }

  /* Contêineres e Cards */
  .pj-card {
    background: var(--pj-surface);
    border: 1px solid var(--pj-border);
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
  }

  .pj-stat-card { /* Card de Estatísticas */
    background: var(--pj-surface);
    border: 1px solid var(--pj-border);
    border-radius: 16px;
    padding: 24px;
    color: var(--pj-text);
    box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05), 0 2px 4px -1px rgba(0,0,0,0.03);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
  }
  .pj-stat-card:hover {
    transform: translateY(-4px); 
    box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04);
  }
  .pj-stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 4px;
    opacity: 0.8;
  }
  .pj-stat-card-blue::before { background-color: #3b82f6; }
  .pj-stat-card-red::before { background-color: #ef4444; }
  .pj-stat-card-orange::before { background-color: #f59e0b; }
  .pj-stat-card-green::before { background-color: #10b981; }
  .dark-theme .pj-stat-card { box-shadow: 0 4px 6px -1px rgba(0,0,0,0.2); }
  .dark-theme .pj-stat-card:hover { box-shadow: 0 20px 25px -5px rgba(0,0,0,0.3); }

  /* Tabela Corporativa */
  .pj-table {
    background: var(--pj-surface);
    color: var(--pj-text);
  } /* Tabela Corporativa */
  .pj-table th {
    padding: 12px;
    font-size: 10px; font-weight: 700; text-transform: uppercase;
    letter-spacing: 0.8px; color: var(--pj-text2);
    border-bottom: 1px solid var(--pj-border);
    background: var(--pj-surface2) !important;
  }
  .pj-table td { padding: 14px 12px; font-size: 13px; }
  .pj-table tbody tr:hover { background-color: rgba(0,0,0,0.02); }
  .dark-theme .pj-table tbody tr:hover { background-color: rgba(255,255,255,0.05); }
  
  /* Badges Suaves */
  .pj-badge {
    display: inline-flex; align-items: center;
    padding: 2px 10px; border-radius: 20px;
    font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.3px;
  }
  /* Cores dinâmicas para badges (usando transparência para funcionar em ambos os temas) */
  .pj-badge-red    { background: rgba(239, 68, 68, 0.15); color: #ef4444; } /* Vermelho */
  .pj-badge-orange { background: rgba(245, 158, 11, 0.15); color: #f59e0b; }
  .pj-badge-blue   { background: rgba(37, 99, 235, 0.15); color: #3b82f6; }
  .pj-badge-gray   { background: rgba(148, 163, 184, 0.15); color: #94a3b8; }
  .pj-badge-green  { background: rgba(16, 185, 129, 0.15); color: #10b981; }

  .dark-theme .divide-gray-200 { border-color: var(--pj-border) !important; }
  
  /* Correção para inputs e botões de filtro no modo escuro */ 
  .dark-theme .pj-card a.bg-white { background-color: var(--pj-surface2) !important; color: var(--pj-text) !important; border-color: var(--pj-border) !important; }
  .dark-theme .pj-card a.bg-white:hover { background-color: var(--pj-border) !important; }
  .dark-theme .pj-table td { color: var(--pj-text2) !important; }
</style>

<!-- Incluindo a biblioteca Frappe Gantt via CDN --> 
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/frappe-gantt@0.6.1/dist/frappe-gantt.css">
<script src="https://cdn.jsdelivr.net/npm/frappe-gantt@0.6.1/dist/frappe-gantt.min.js"></script>

<?php
// Helper para gerar links de ordenação
$orderBy = $filtros['orderBy'] ?? 'id';
$orderDir = $filtros['orderDir'] ?? 'DESC';
 
function renderSortLink($column, $label, $currentOrderBy, $currentOrderDir, $baseUrl, $filtros)
{
    $newDir = ($currentOrderBy === $column && $currentOrderDir === 'ASC') ? 'DESC' : 'ASC';
    $icon = '';
    if ($currentOrderBy === $column) {
        $icon = $currentOrderDir === 'ASC' ? "<i class='bx bx-chevron-up'></i>" : "<i class='bx bx-chevron-down'></i>";
    }

    $params = array_merge($filtros, ['orderBy' => $column, 'orderDir' => $newDir, 'page' => 1]);
    $url = BASE_URL . $baseUrl . '?' . http_build_query($params);

    return "<a href='{$url}' class='group inline-flex items-center hover:text-blue-700 transition-colors cursor-pointer'>{$label} <span class='ml-1 text-blue-500'>{$icon}</span></a>";
}
?>

<div id="projects-page-container">

<h2 class="text-2xl font-bold mb-4">Módulo Gestão de Projetos</h2> 
<p class="mb-6 text-gray-600">Acompanhamento de cronogramas, marcos, recursos e progresso de cada projeto em andamento.</p> 

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <!-- Card 1: Total Em Andamento --> 
    <div class="pj-stat-card pj-stat-card-blue group">
        <div class="absolute -right-4 -top-4 opacity-10 group-hover:opacity-20 transition-opacity duration-300 transform group-hover:scale-110">
            <i class='bx bx-briefcase text-9xl text-blue-600 dark:text-blue-400'></i>
        </div>
        <div class="relative z-10">
            <div class="flex items-center gap-3 mb-3">
                <div class="p-2.5 bg-blue-50 dark:bg-blue-900/30 rounded-xl text-blue-600 dark:text-blue-400 shadow-sm">
                    <i class='bx bx-briefcase text-xl'></i>
                </div>
                <h3 class="font-bold text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">Projetos Ativos</h3>
            </div>
            <p class="text-4xl font-extrabold mt-1 text-gray-800 dark:text-gray-100"><?php echo $totalEmAndamento ?? 0; ?></p>
            <div class="flex items-center mt-3 text-sm text-gray-500 dark:text-gray-400">
                <span class="text-blue-600 dark:text-blue-400 flex items-center font-medium mr-2"><i class='bx bx-trending-up mr-1'></i> Atual</span>
                <span>Na carteira</span>
            </div>
        </div>
    </div>
    <!-- Card 2: Atrasados --> 
    <div class="pj-stat-card pj-stat-card-red group">
        <div class="absolute -right-4 -top-4 opacity-10 group-hover:opacity-20 transition-opacity duration-300 transform group-hover:scale-110">
            <i class='bx bx-time-five text-9xl text-red-600 dark:text-red-400'></i>
        </div>
        <div class="relative z-10">
            <div class="flex items-center gap-3 mb-3">
                <div class="p-2.5 bg-red-50 dark:bg-red-900/30 rounded-xl text-red-600 dark:text-red-400 shadow-sm">
                    <i class='bx bx-time-five text-xl'></i>
                </div>
                <h3 class="font-bold text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">Projetos Atrasados</h3>
            </div>
            <p class="text-4xl font-extrabold mt-1 text-gray-800 dark:text-gray-100"><?php echo $projetosAtrasados ?? 0; ?></p>
            <div class="flex items-center mt-3 text-sm text-gray-500 dark:text-gray-400">
                <span class="text-red-600 dark:text-red-400 flex items-center font-medium mr-2"><i class='bx bx-error-circle mr-1'></i> Atenção</span>
                <span>Exige replanejamento</span>
            </div>
        </div>
    </div>
    <!-- Card 3: Próximos Marcos --> 
    <div class="pj-stat-card pj-stat-card-orange group">
        <div class="absolute -right-4 -top-4 opacity-10 group-hover:opacity-20 transition-opacity duration-300 transform group-hover:scale-110">
            <i class='bx bx-flag text-9xl text-orange-500 dark:text-orange-400'></i>
        </div>
        <div class="relative z-10">
            <div class="flex items-center gap-3 mb-3">
                <div class="p-2.5 bg-orange-50 dark:bg-orange-900/30 rounded-xl text-orange-500 dark:text-orange-400 shadow-sm">
                    <i class='bx bx-flag text-xl'></i>
                </div>
                <h3 class="font-bold text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">Marcos a Vencer</h3>
            </div>
            <p class="text-4xl font-extrabold mt-1 text-gray-800 dark:text-gray-100"><?php echo $proximoMarcoVencer ?? 0; ?></p>
            <div class="flex items-center mt-3 text-sm text-gray-500 dark:text-gray-400">
                <span class="text-orange-500 dark:text-orange-400 flex items-center font-medium mr-2"><i class='bx bx-calendar-event mr-1'></i> 7 dias</span>
                <span>Entregas iminentes</span>
            </div>
        </div>
    </div>
    <!-- Card 4: Faturamento Previsto --> 
    <div class="pj-stat-card pj-stat-card-green group">
        <div class="absolute -right-4 -top-4 opacity-10 group-hover:opacity-20 transition-opacity duration-300 transform group-hover:scale-110">
            <i class='bx bx-dollar-circle text-9xl text-green-600 dark:text-green-400'></i>
        </div>
        <div class="relative z-10">
            <div class="flex items-center gap-3 mb-3">
                <div class="p-2.5 bg-green-50 dark:bg-green-900/30 rounded-xl text-green-600 dark:text-green-400 shadow-sm">
                    <i class='bx bx-dollar-circle text-xl'></i>
                </div>
                <h3 class="font-bold text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">Faturamento Prev.</h3>
            </div>
            <p class="text-4xl font-extrabold mt-1 text-gray-800 dark:text-gray-100"><?php echo $faturamentoPrevistoMes ?? 'R$ 0'; ?></p>
            <div class="flex items-center mt-3 text-sm text-gray-500 dark:text-gray-400">
                <span class="text-green-600 dark:text-green-400 flex items-center font-medium mr-2"><i class='bx bx-line-chart mr-1'></i> No Mês</span>
                <span>Baseado nos marcos</span>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6"> 
    <!-- Lista de Projetos Críticos (Tabela Principal) --> 
    <div id="project-list-container" class="lg:col-span-2 pj-card p-6">
        <h3 class="text-lg font-semibold mb-4 border-b dark:border-gray-700 pb-2 flex justify-between items-center">
            Lista de Projetos
            <div>
                <button id="toggle-gantt-view-btn" class="text-sm font-medium text-gray-700 dark:text-gray-200 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 px-3 py-1 rounded-md mr-2 transition-colors">
                    Ver Cronograma (Gantt)
                </button>
                <a href="<?php echo BASE_URL; ?>/projetos/novo" class="text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 px-4 py-1.5 rounded-md transition-colors shadow-sm">
                    + Novo Projeto
                </a>
            </div>
        </h3>

        <!-- Conteúdo da Lista de Projetos -->
        <div id="project-list-content">
            <div class="overflow-x-auto">
                <?php if (!empty($projetos)): ?>
                    <table class="min-w-full divide-y divide-gray-200 pj-table">
                        <thead>
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?php echo renderSortLink('numero_projeto', 'ID', $orderBy, $orderDir, $baseUrl, $filtros); ?></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?php echo renderSortLink('nome', 'Nome do Projeto', $orderBy, $orderDir, $baseUrl, $filtros); ?></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?php echo renderSortLink('cliente_nome', 'Cliente', $orderBy, $orderDir, $baseUrl, $filtros); ?></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?php echo renderSortLink('responsavel', 'Responsável', $orderBy, $orderDir, $baseUrl, $filtros); ?></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?php echo renderSortLink('data_inicial', 'Data Início', $orderBy, $orderDir, $baseUrl, $filtros); ?></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?php echo renderSortLink('data_fim_prevista', 'Data Fim', $orderBy, $orderDir, $baseUrl, $filtros); ?></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?php echo renderSortLink('status', 'Status', $orderBy, $orderDir, $baseUrl, $filtros); ?></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php foreach ($projetos as $projeto): ?>
                                <?php
                                    $statusClass = 'pj-badge-gray';
                                    if ($projeto['status'] === 'Atrasado') $statusClass = 'pj-badge-red';
                                    elseif ($projeto['status'] === 'Marco Vencendo') $statusClass = 'pj-badge-orange';
                                    elseif ($projeto['status'] === 'Em Execução' || $projeto['status'] === 'Em Andamento') $statusClass = 'pj-badge-blue';
                                    elseif ($projeto['status'] === 'Concluído') $statusClass = 'pj-badge-green';
                                ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100"><?php echo htmlspecialchars($projeto['numero_projeto'] ?? 'ID #'.$projeto['id']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 dark:text-gray-200">
                                        <a href="<?php echo BASE_URL; ?>/projetos/detalhe/<?php echo $projeto['id']; ?>/resumo" class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 font-semibold">
                                            <?php echo htmlspecialchars($projeto['nome']); ?>
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($projeto['cliente_nome'] ?? 'N/A'); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($projeto['responsavel']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo $projeto['data_inicial'] ? date('d/m/Y', strtotime($projeto['data_inicial'])) : 'N/A'; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo $projeto['data_fim_prevista'] ? date('d/m/Y', strtotime($projeto['data_fim_prevista'])) : 'N/A'; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="pj-badge <?php echo $statusClass; ?>">
                                            <?php echo htmlspecialchars($projeto['status']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <?php if ($projeto['status'] === 'Cancelado'): ?>
                                            <form action="<?php echo BASE_URL; ?>/projetos/restaurar/<?php echo $projeto['id']; ?>" method="POST" class="inline-block mr-3" onsubmit="return confirm('Deseja restaurar este projeto para Em Execução?');">
                                                <button type="submit" class="text-green-600 hover:text-green-900" title="Restaurar Projeto">
                                                    <i class='bx bx-undo text-xl'></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        <a href="<?php echo BASE_URL; ?>/projetos/detalhe/<?php echo $projeto['id']; ?>/resumo" class="text-blue-600 hover:text-blue-900 mr-3 inline-block" title="Visualizar Detalhes">
                                            <i class='bx bx-show text-xl'></i>
                                        </a>
                                        <a href="<?php echo BASE_URL; ?>/projetos/editar/<?php echo $projeto['id']; ?>" class="text-indigo-600 hover:text-indigo-900 mr-3 inline-block" title="Editar Projeto">
                                            <i class='bx bx-edit text-xl'></i>
                                        </a>
                                        <form action="<?php echo BASE_URL; ?>/projetos/excluir/<?php echo $projeto['id']; ?>" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir este projeto?');" class="inline-block">
                                            <button type="submit" class="text-red-600 hover:text-red-900" title="Excluir Projeto">
                                                <i class='bx bx-trash text-xl'></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="text-gray-500 text-center py-4">Nenhum projeto encontrado.</p>
                <?php endif; ?>
            </div>

            <!-- Navegação da Paginação -->
            <div class="mt-4 flex justify-end items-center">
                <?php if ($totalPaginas > 1): ?>
                    <nav class="flex items-center justify-end space-x-2">
                        <?php
                        $queryString = http_build_query(array_merge($filtros, ['page' => '']));
                        ?>
                        <a href="<?php echo BASE_URL . $baseUrl; ?>?<?php echo $queryString . ($paginaAtual - 1); ?>" class="<?php echo ($paginaAtual <= 1) ? 'pointer-events-none text-gray-400' : 'text-gray-600 hover:text-violet-600'; ?> px-3 py-1 rounded-md text-sm font-medium">
                            Anterior
                        </a>

                        <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                            <a href="<?php echo BASE_URL . $baseUrl; ?>?<?php echo $queryString . $i; ?>" class="<?php echo ($i == $paginaAtual) ? 'bg-blue-600 text-white border-blue-600' : 'bg-white dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600'; ?> px-3 py-1 rounded-md text-sm font-medium border dark:border-gray-600">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>

                        <a href="<?php echo BASE_URL . $baseUrl; ?>?<?php echo $queryString . ($paginaAtual + 1); ?>" class="<?php echo ($paginaAtual >= $totalPaginas) ? 'pointer-events-none text-gray-400' : 'text-gray-600 hover:text-violet-600'; ?> px-3 py-1 rounded-md text-sm font-medium">
                            Próxima
                        </a>
                    </nav>
                <?php endif; ?>
            </div>
        </div>

        <!-- Contêiner para o Gráfico de Gantt Mestre (começa oculto) --> 
        <div id="master-gantt-container" class="hidden">
            <h3 class="text-lg font-semibold mb-4">Cronograma Geral de Projetos</h3>
            <div id="master-gantt"></div>
        </div>
    </div>

    <!-- Filtros de Status e Responsáveis -->
    <div class="pj-card p-6 lg:col-span-1"> 
        <h3 class="text-lg font-semibold mb-4">Filtros Rápidos</h3>

        <label for="filtroStatus" class="block text-sm font-medium text-gray-700 mb-1">Filtrar por Status</label>
        <select id="filtroStatus" class="w-full mb-4 border border-gray-300 rounded-md shadow-sm p-2 focus:ring-violet-500 focus:border-violet-500">
            <option value="Todos Ativos" <?php echo (empty($filtros['status']) || $filtros['status'] === 'Todos Ativos') ? 'selected' : ''; ?>>Todos Ativos</option>
            <option value="Todos" <?php echo ($filtros['status'] === 'Todos') ? 'selected' : ''; ?>>Todos (Inclui Arquivados)</option>
            <option value="Em Execução" <?php echo ($filtros['status'] === 'Em Execução') ? 'selected' : ''; ?>>Em Execução</option>
            <option value="Aguardando Cliente" <?php echo ($filtros['status'] === 'Aguardando Cliente') ? 'selected' : ''; ?>>Aguardando Cliente</option>
            <option value="Concluído" <?php echo ($filtros['status'] === 'Concluído') ? 'selected' : ''; ?>>Concluído</option>
            <option value="Cancelado" <?php echo ($filtros['status'] === 'Cancelado') ? 'selected' : ''; ?>>Cancelado</option>
            <option value="Atrasado" <?php echo ($filtros['status'] === 'Atrasado') ? 'selected' : ''; ?>>Atrasado</option>
        </select>

        <label for="filtroResponsavel" class="block text-sm font-medium text-gray-700 mb-1">Filtrar por Responsável</label>
        <input type="text" id="filtroResponsavel" value="<?php echo htmlspecialchars($filtros['responsavel'] ?? ''); ?>" placeholder="Ex: Mariana A." class="w-full mb-4 border border-gray-300 rounded-md shadow-sm p-2 focus:ring-violet-500 focus:border-violet-500">

        <!-- Links de Navegação --> 
        <div class="space-y-3">
            <?php if (isset($baseUrl) && (strpos($baseUrl, 'arquivados') !== false || strpos($baseUrl, 'cancelados') !== false)): ?>
                <a href="<?php echo BASE_URL; ?>/projetos" class="w-full inline-flex items-center justify-center px-4 py-2 border border-gray-300 text-base font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none">
                    <i class='bx bx-arrow-back mr-2'></i> Ver Projetos Ativos
                </a>
            <?php else: ?>
                <a href="<?php echo BASE_URL; ?>/projetos/arquivados" class="w-full inline-flex items-center justify-center px-4 py-2 border border-gray-300 text-base font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none">
                    Ver Projetos Arquivados
                </a>
                <a href="<?php echo BASE_URL; ?>/projetos/cancelados" class="w-full inline-flex items-center justify-center px-4 py-2 border border-gray-300 text-base font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none">
                    Ver Projetos Cancelados
                </a>
            <?php endif; ?>
        </div>

        <div class="mt-6 pt-4 border-t">
            <p class="text-sm text-gray-500">O progresso de cada projeto deve ser atualizado diariamente pelos responsáveis.</p>
        </div>
    </div>
</div>

</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggleBtn = document.getElementById('toggle-gantt-view-btn'); 
        const projectListContent = document.getElementById('project-list-content');
        const masterGanttContainer = document.getElementById('master-gantt-container');
        let ganttChart = null;
        
        // --- Lógica dos Filtros --- 
        const filtroStatus = document.getElementById('filtroStatus');
        const filtroResponsavel = document.getElementById('filtroResponsavel');

        function aplicarFiltros() {
            const status = filtroStatus.value;
            const responsavel = filtroResponsavel.value;
            const url = new URL(window.location.href);

            if (status) url.searchParams.set('status', status);
            if (responsavel) url.searchParams.set('responsavel', responsavel);
            else url.searchParams.delete('responsavel');

            // Mantém a ordenação atual se existir 
            // (Não é necessário código extra aqui, pois new URL() já pega os parâmetros atuais da janela) 

            url.searchParams.set('page', 1); // Reseta para a página 1 ao filtrar 
            window.location.href = url.toString();
        }

        filtroStatus.addEventListener('change', aplicarFiltros);
        // Aplica filtro de responsável ao pressionar Enter 
        filtroResponsavel.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') aplicarFiltros();
        });
        
        // Prepara os dados dos projetos para o formato do Gantt 
        const projectData = <?php echo json_encode($projetos ?? []); ?>;
        const tasksForGantt = projectData
            .filter(p => p.data_inicial && p.data_fim_prevista) // Filtra projetos sem datas 
            .map(project => ({
                id: 'proj_' + project.id,
                name: (project.numero_projeto || 'ID #' + project.id) + ' - ' + project.nome,
                start: project.data_inicial,
                end: project.data_fim_prevista,
                progress: 50, // Placeholder, pode ser calculado no futuro 
                custom_class: 'bar-milestone' // Estilo opcional 
            }));
        
        let isGanttVisible = false; // Moved to local scope
        toggleBtn.addEventListener('click', function() {
            isGanttVisible = !isGanttVisible; // Toggle state

            if (isGanttVisible) {
                // Esconde a lista e mostra o Gantt 
                projectListContent.classList.add('hidden');
                masterGanttContainer.classList.remove('hidden');
                toggleBtn.textContent = 'Ver Lista de Projetos';

                // Inicializa o Gantt apenas na primeira vez 
                if (!ganttChart && tasksForGantt.length > 0) { 
                    ganttChart = new Gantt("#master-gantt", tasksForGantt, {
                        view_mode: 'Month',
                        language: 'en',
                        on_click: function(task) {
                            // Ao clicar em uma barra, redireciona para os detalhes do projeto
                            const projectId = task.id.replace('proj_', '');
                            window.location.href = `<?php echo BASE_URL; ?>/projetos/detalhe/${projectId}/resumo`;
                        }
                    });
                } else if (tasksForGantt.length === 0) {
                    document.getElementById('master-gantt').innerHTML = '<p class="text-gray-500 text-center py-4">Não há projetos com datas de início e fim para exibir no cronograma.</p>';
                } 
            } else {
                // Esconde o Gantt e mostra a lista 
                projectListContent.classList.remove('hidden');
                masterGanttContainer.classList.add('hidden');
                toggleBtn.textContent = 'Ver Cronograma (Gantt)';
            }
        });
    });
</script>