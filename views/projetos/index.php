<!-- Incluindo a biblioteca Frappe Gantt via CDN -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/frappe-gantt@0.6.1/dist/frappe-gantt.css">
<script src="https://cdn.jsdelivr.net/npm/frappe-gantt@0.6.1/dist/frappe-gantt.min.js"></script>

<h2 class="text-2xl font-bold mb-4">Módulo Gestão de Projetos</h2>
<p class="mb-6 text-gray-600">Acompanhamento de cronogramas, marcos, recursos e progresso de cada projeto em andamento.</p>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <!-- Card 1: Total Em Andamento -->
    <div class="bg-white p-6 rounded-lg shadow-lg border-l-4 border-violet-500">
        <h3 class="font-semibold text-gray-500">Total de Projetos Ativos</h3>
        <p class="text-3xl font-bold text-violet-600"><?php echo $totalEmAndamento ?? 0; ?></p>
        <p class="text-sm text-gray-400 mt-2">Na carteira atual da SysEnviCorp</p>
    </div>
    <!-- Card 2: Atrasados -->
    <div class="bg-white p-6 rounded-lg shadow-lg border-l-4 border-red-500">
        <h3 class="font-semibold text-gray-500">Projetos Atrasados</h3>
        <p class="text-3xl font-bold text-red-600"><?php echo $projetosAtrasados ?? 0; ?></p>
        <p class="text-sm text-gray-400 mt-2">Exige atenção e replanejamento</p>
    </div>
    <!-- Card 3: Próximos Marcos -->
    <div class="bg-white p-6 rounded-lg shadow-lg border-l-4 border-yellow-500">
        <h3 class="font-semibold text-gray-500">Marcos a Vencer (7 dias)</h3>
        <p class="text-3xl font-bold text-yellow-600"><?php echo $proximoMarcoVencer ?? 0; ?></p>
        <p class="text-sm text-gray-400 mt-2">Entregas iminentes</p>
    </div>
    <!-- Card 4: Faturamento Previsto -->
    <div class="bg-white p-6 rounded-lg shadow-lg border-l-4 border-green-500">
        <h3 class="font-semibold text-gray-500">Faturamento Previsto (Mês)</h3>
        <p class="text-3xl font-bold text-green-600"><?php echo $faturamentoPrevistoMes ?? 'R$ 0'; ?></p>
        <p class="text-sm text-gray-400 mt-2">Baseado nos marcos cumpridos</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Lista de Projetos Críticos (Tabela Principal) -->
    <div id="project-list-container" class="lg:col-span-2 bg-white p-6 rounded-lg shadow-md">
        <h3 class="text-lg font-semibold mb-4 border-b pb-2 flex justify-between items-center">
            Lista de Projetos
            <div>
                <button id="toggle-gantt-view-btn" class="text-sm font-medium text-gray-700 bg-gray-200 hover:bg-gray-300 px-3 py-1 rounded-md mr-2">
                    Ver Cronograma (Gantt)
                </button>
                <button id="btnNovoProjeto" class="text-sm font-medium text-white bg-sky-500 hover:bg-sky-700 px-3 py-1 rounded-md">
                    + Novo Projeto
                </button>
            </div>
        </h3>

        <!-- Conteúdo da Lista de Projetos -->
        <div id="project-list-content">
            <div class="overflow-x-auto">
                <?php if (!empty($projetos)): ?>
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nome do Projeto</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Responsável</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data da Aprovação</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($projetos as $projeto): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo $projeto['id']; ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800">
                                        <a href="<?php echo BASE_URL; ?>/projetos/detalhe/<?php echo $projeto['id']; ?>/resumo" class="text-violet-600 hover:text-violet-800 font-medium">
                                            <?php echo htmlspecialchars($projeto['nome']); ?>
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($projeto['cliente_nome'] ?? 'N/A'); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($projeto['responsavel']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo $projeto['data_inicial'] ? date('d/m/Y', strtotime($projeto['data_inicial'])) : 'N/A'; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    <?php
                                    if ($projeto['status'] === 'Atrasado') echo 'bg-red-100 text-red-800';
                                    elseif ($projeto['status'] === 'Marco Vencendo') echo 'bg-yellow-100 text-yellow-800';
                                    elseif ($projeto['status'] === 'Em Execução') echo 'bg-sky-100 text-sky-800';
                                    else echo 'bg-green-100 text-green-800';
                                    ?>">
                                            <?php echo htmlspecialchars($projeto['status']); ?>
                                        </span>
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
                            <a href="<?php echo BASE_URL . $baseUrl; ?>?<?php echo $queryString . $i; ?>" class="<?php echo ($i == $paginaAtual) ? 'bg-violet-500 text-white' : 'bg-white text-gray-600 hover:bg-gray-100'; ?> px-3 py-1 rounded-md text-sm font-medium border">
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
    <div class="bg-white p-6 rounded-lg shadow-md lg:col-span-1">
        <h3 class="text-lg font-semibold mb-4">Filtros Rápidos</h3>

        <label for="filtroStatus" class="block text-sm font-medium text-gray-700 mb-1">Filtrar por Status</label>
        <select id="filtroStatus" class="w-full mb-4 border border-gray-300 rounded-md shadow-sm p-2 focus:ring-violet-500 focus:border-violet-500">
            <option value="Todos">Todos Ativos</option>
            <option>Em Execução</option>
            <option>Aguardando Cliente</option>
            <option>Concluído</option>
            <option>Atrasado</option>
        </select>

        <label for="filtroResponsavel" class="block text-sm font-medium text-gray-700 mb-1">Filtrar por Responsável</label>
        <input type="text" id="filtroResponsavel" placeholder="Ex: Mariana A." class="w-full mb-4 border border-gray-300 rounded-md shadow-sm p-2 focus:ring-violet-500 focus:border-violet-500">

        <!-- Link para Projetos Arquivados (agora como botão) -->
        <a href="<?php echo BASE_URL; ?>/projetos/arquivados" class="w-full inline-flex items-center justify-center px-4 py-2 border border-gray-300 text-base font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none">
            Ver Projetos Arquivados
        </a>

        <div class="mt-6 pt-4 border-t">
            <p class="text-sm text-gray-500">O progresso de cada projeto deve ser atualizado diariamente pelos responsáveis.</p>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggleBtn = document.getElementById('toggle-gantt-view-btn');
        const projectListContent = document.getElementById('project-list-content');
        const masterGanttContainer = document.getElementById('master-gantt-container');
        let ganttChart = null;
        let isGanttVisible = false;

        // Prepara os dados dos projetos para o formato do Gantt
        const projectData = <?php echo json_encode($projetos ?? []); ?>;
        const tasksForGantt = projectData
            .filter(p => p.data_inicial && p.data_fim_prevista) // Filtra projetos sem datas
            .map(project => ({
                id: 'proj_' + project.id,
                name: project.nome,
                start: project.data_inicial,
                end: project.data_fim_prevista,
                progress: 50, // Placeholder, pode ser calculado no futuro
                custom_class: 'bar-milestone' // Estilo opcional
            }));

        toggleBtn.addEventListener('click', function() {
            isGanttVisible = !isGanttVisible;

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

<!-- Include da Modal de Novo Projeto -->
<?php include __DIR__ . '/modal_novo_projeto.php'; ?>