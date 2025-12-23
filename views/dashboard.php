<h2 class="text-2xl font-bold mb-4"><?php echo htmlspecialchars($pageTitle); ?></h2>
<p class="mb-6 text-gray-600">Bem-vindo ao painel de controle. Aqui você encontra uma visão geral e consolidada das informações mais importantes para a gestão da sua empresa.</p>

<!-- Cards de Resumo Dinâmicos -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <!-- Card Projetos Ativos -->
    <a href="<?php echo BASE_URL; ?>/projetos" class="bg-white p-6 rounded-lg shadow-md border-l-4 border-blue-500 hover:shadow-lg transition-all duration-300 hover:-translate-y-1">
        <div class="flex justify-between items-center">
            <div>
                <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider">Projetos Ativos</h3>
                <p class="text-3xl font-bold text-blue-600"><?php echo $projetosAtivos ?? 0; ?></p>
            </div>
            <div class="h-12 w-12 rounded-full bg-blue-100 flex items-center justify-center text-blue-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
            </div>
        </div>
    </a>
    <!-- Card Licenças a Vencer -->
    <a href="<?php echo BASE_URL; ?>/licencasOperacao" class="bg-white p-6 rounded-lg shadow-md border-l-4 border-orange-500 hover:shadow-lg transition-all duration-300 hover:-translate-y-1">
        <div class="flex justify-between items-center">
            <div>
                <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider">Licenças (30d)</h3>
                <p class="text-3xl font-bold text-orange-600"><?php echo $licencasAVencer ?? 0; ?></p>
            </div>
            <div class="h-12 w-12 rounded-full bg-orange-100 flex items-center justify-center text-orange-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>
    </a>
    <!-- Card de Contratos (Substituído) -->
    <a href="<?php echo BASE_URL; ?>/contratos" class="bg-white p-6 rounded-lg shadow-md border-l-4 border-purple-500 hover:shadow-lg transition-all duration-300 hover:-translate-y-1">
        <div class="flex justify-between items-center">
            <div>
                <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider">Contratos Vigentes</h3>
                <p class="text-3xl font-bold text-purple-600"><?php echo $contratosVigentes ?? 0; ?></p>
            </div>
            <div class="h-12 w-12 rounded-full bg-purple-100 flex items-center justify-center text-purple-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
        </div>
    </a>
    <!-- Card Novos Clientes -->
    <a href="<?php echo BASE_URL; ?>/clientes" class="bg-white p-6 rounded-lg shadow-md border-l-4 border-green-500 hover:shadow-lg transition-all duration-300 hover:-translate-y-1">
        <div class="flex justify-between items-center">
            <div>
                <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider">Novos Clientes</h3>
                <p class="text-3xl font-bold text-green-600"><?php echo $novosClientesMes ?? 0; ?></p>
            </div>
            <div class="h-12 w-12 rounded-full bg-green-100 flex items-center justify-center text-green-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                </svg>
            </div>
        </div>
    </a>
</div>

<!-- Seção de Gráficos -->
<div class="flex flex-wrap gap-6 mb-6">
    <!-- Gráfico de Linha: Receitas vs Despesas -->
    <div class="bg-white p-6 rounded-lg shadow-md">
        <h3 class="text-lg font-semibold mb-4">Receitas vs. Despesas (Últimos 6 Meses)</h3>
        <div class="relative h-72 w-full">
            <canvas id="receitasDespesasChart"></canvas>
        </div>
    </div>

    <!-- Gráfico de Pizza: Despesas por Categoria -->
    <div class="bg-white p-6 rounded-lg shadow-md">
        <h3 class="text-lg font-semibold mb-4">Despesas por Categoria (Mês Atual)</h3>
        <div class="relative h-72 w-full flex justify-center">
            <canvas id="despesasCategoriaChart"></canvas>
        </div>
    </div>
</div>

<!-- Tabela -->
<!-- Projetos Recentes -->
<div class="bg-white p-6 rounded-lg shadow-md mb-6">
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-semibold">Projetos Recentes</h3>
        <a href="<?php echo BASE_URL; ?>/projetos" class="text-sm text-blue-600 hover:text-blue-800 font-medium hover:underline">Ver Todos &rarr;</a>
    </div>

    <?php if (!empty($projetos) && is_array($projetos)): ?>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Projeto</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Responsável</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($projetos as $projeto): ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($projeto['nome']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($projeto['cliente_nome'] ?? 'N/A'); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($projeto['responsavel'] ?? 'N/A'); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php
                                $statusColors = [
                                    'Concluído' => 'bg-green-100 text-green-800',
                                    'Cancelado' => 'bg-red-100 text-red-800',
                                    'Planejado' => 'bg-gray-100 text-gray-800',
                                    'Em Andamento' => 'bg-blue-100 text-blue-800',
                                    'Atrasado' => 'bg-orange-100 text-orange-800'
                                ];
                                $statusClass = $statusColors[$projeto['status']] ?? 'bg-blue-100 text-blue-800';
                                ?>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $statusClass; ?>">
                                    <?php echo htmlspecialchars($projeto['status']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="<?php echo BASE_URL; ?>/projetos/detalhe/<?php echo $projeto['id']; ?>" class="text-indigo-600 hover:text-indigo-900">Detalhes</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="text-center py-8 text-gray-500">
            <p>Nenhum projeto ativo no momento.</p>
        </div>
    <?php endif; ?>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Dados para o gráfico de Receitas vs Despesas
        const monthlyData = <?php echo json_encode($monthlySummary ?? []); ?>;
        const labels = monthlyData.map(item => {
            const [year, month] = item.mes.split('-');
            return new Date(year, month - 1).toLocaleString('default', {
                month: 'short',
                year: '2-digit'
            });
        });
        const receitas = monthlyData.map(item => item.receitas);
        const despesas = monthlyData.map(item => item.despesas);

        const ctxLine = document.getElementById('receitasDespesasChart').getContext('2d');
        new Chart(ctxLine, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Receitas',
                    data: receitas,
                    borderColor: 'rgba(59, 130, 246, 1)',
                    backgroundColor: 'rgba(59, 130, 246, 0.2)',
                    fill: true,
                    tension: 0.3
                }, {
                    label: 'Despesas',
                    data: despesas,
                    borderColor: 'rgba(239, 68, 68, 1)',
                    backgroundColor: 'rgba(239, 68, 68, 0.2)',
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Dados para o gráfico de Despesas por Categoria
        const categoryData = <?php echo json_encode($expenseByCategory ?? []); ?>;
        const categoryLabels = categoryData.map(item => item.categoria);
        const categoryTotals = categoryData.map(item => item.total);

        const ctxPie = document.getElementById('despesasCategoriaChart').getContext('2d');
        new Chart(ctxPie, {
            type: 'doughnut',
            data: {
                labels: categoryLabels,
                datasets: [{
                    label: 'Despesas',
                    data: categoryTotals,
                    backgroundColor: ['#EF4444', '#F97316', '#F59E0B', '#84CC16', '#22C55E', '#10B981', '#06B6D4', '#3B82F6', '#8B5CF6', '#EC4899'],
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
            }
        });
    });
</script>