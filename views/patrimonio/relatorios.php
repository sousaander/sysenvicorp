<div class="flex justify-between items-start mb-6">
    <div>
        <h2 class="text-2xl font-bold"><?php echo htmlspecialchars($pageTitle); ?></h2>
        <p class="text-gray-600">Visualize dados consolidados sobre o patrimônio da empresa.</p>
    </div>
    <a href="<?php echo BASE_URL; ?>/patrimonio" class="px-4 py-2 text-sm font-semibold text-gray-700 bg-gray-200 rounded-lg shadow-md hover:bg-gray-300 transition">
        &larr; Voltar para o Dashboard
    </a>
</div>

<!-- Seção de Indicadores -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <!-- Card Demonstrativo de Depreciação -->
    <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-sky-500">
        <h3 class="font-semibold text-gray-600 mb-2">Demonstrativo de Depreciação</h3>
        <div class="space-y-2 text-sm">
            <div class="flex justify-between">
                <span>Valor de Aquisição:</span>
                <span class="font-semibold">R$ <?php echo number_format($depreciacaoGeral['total_aquisicao'], 2, ',', '.'); ?></span>
            </div>
            <div class="flex justify-between text-red-600">
                <span>(-) Depreciação Acumulada:</span>
                <span class="font-semibold">R$ <?php echo number_format($depreciacaoGeral['total_depreciacao_acumulada'], 2, ',', '.'); ?></span>
            </div>
            <div class="flex justify-between font-bold text-green-700 border-t pt-2 mt-2">
                <span>(=) Valor Contábil:</span>
                <span>R$ <?php echo number_format($depreciacaoGeral['total_valor_contabil'], 2, ',', '.'); ?></span>
            </div>
        </div>
    </div>

    <!-- Card Idade Média dos Ativos -->
    <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-purple-500">
        <h3 class="font-semibold text-gray-600">Idade Média dos Ativos</h3>
        <p class="text-3xl font-bold text-purple-600 mt-2"><?php echo number_format($indicadoresRenovacao['idade_media_anos'], 1, ',', '.'); ?> anos</p>
        <p class="text-sm text-gray-400 mt-2">Indica a necessidade de renovação do parque de ativos.</p>
    </div>

    <!-- Card Ativos Totalmente Depreciados -->
    <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-gray-500">
        <h3 class="font-semibold text-gray-600">Ativos 100% Depreciados</h3>
        <p class="text-3xl font-bold text-gray-600 mt-2"><?php echo number_format($indicadoresRenovacao['percentual_depreciado'], 2, ',', '.'); ?>%</p>
        <p class="text-sm text-gray-400 mt-2">Percentual de ativos que atingiram o fim da vida útil contábil.</p>
    </div>
</div>

<!-- Seção de Relatórios em Tabela e Gráfico -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    <!-- Tabela de Bens por Centro de Custo -->
    <div class="bg-white p-6 rounded-lg shadow-md">
        <h3 class="text-lg font-semibold mb-4 border-b pb-2">Relatório de Bens por Centro de Custo</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Centro de Custo</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Qtd. Bens</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Valor Total</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (!empty($bensPorCentroCusto)) : ?>
                        <?php foreach ($bensPorCentroCusto as $item) : ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($item['centro_custo']); ?></td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm text-center text-gray-700"><?php echo $item['quantidade_bens']; ?></td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm text-right text-gray-700">R$ <?php echo number_format($item['valor_total'], 2, ',', '.'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="3" class="px-6 py-4 text-center text-gray-500">Nenhum dado encontrado.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Gráfico de Pizza -->
    <div class="bg-white p-6 rounded-lg shadow-md">
        <h3 class="text-lg font-semibold mb-4">Distribuição de Valor por Centro de Custo</h3>
        <canvas id="centroCustoChart"></canvas>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const chartData = <?php echo json_encode($bensPorCentroCusto ?? []); ?>;

        if (chartData.length > 0) {
            const labels = chartData.map(item => item.centro_custo);
            const data = chartData.map(item => item.valor_total);

            const ctx = document.getElementById('centroCustoChart').getContext('2d');
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Valor por Centro de Custo',
                        data: data,
                        backgroundColor: [
                            '#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6',
                            '#6366F1', '#EC4899', '#F97316', '#14B8A6', '#6B7280'
                        ],
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        title: {
                            display: false,
                        }
                    }
                }
            });
        }
    });
</script>