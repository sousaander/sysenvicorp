<div class="flex justify-between items-start mb-6">
    <div>
        <h2 class="text-2xl font-bold mb-2"><?php echo htmlspecialchars($pageTitle); ?></h2>
        <p class="text-gray-600">Visualize dados consolidados e gere relatórios sobre sua carteira de contratos.</p>
    </div>
    <a href="<?php echo BASE_URL; ?>/contratos" class="bg-gray-200 text-gray-800 px-4 py-2 rounded-md hover:bg-gray-300 font-medium flex-shrink-0">
        &larr; Voltar
    </a>
</div>

<!-- Seção de KPIs (Indicadores Chave) -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-white p-5 rounded-lg shadow-md flex items-center justify-between">
        <div>
            <p class="text-sm font-medium text-gray-500">Contratos Ativos</p>
            <p class="text-3xl font-bold text-gray-800"><?php echo $totalVigentes; ?></p>
        </div>
        <div class="bg-green-100 p-3 rounded-full">
            <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </div>
    </div>
    <div class="bg-white p-5 rounded-lg shadow-md flex items-center justify-between">
        <div>
            <p class="text-sm font-medium text-gray-500">Contratos Vencidos</p>
            <p class="text-3xl font-bold text-red-600"><?php echo $totalVencidos; ?></p>
        </div>
        <div class="bg-red-100 p-3 rounded-full">
            <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </div>
    </div>
    <div class="bg-white p-5 rounded-lg shadow-md flex items-center justify-between">
        <div>
            <p class="text-sm font-medium text-gray-500">A Vencer (30 dias)</p>
            <p class="text-3xl font-bold text-yellow-600"><?php echo $vencendo30dias; ?></p>
        </div>
        <div class="bg-yellow-100 p-3 rounded-full">
            <svg class="h-6 w-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
        </div>
    </div>
    <div class="bg-white p-5 rounded-lg shadow-md flex items-center justify-between">
        <div>
            <p class="text-sm font-medium text-gray-500">Obrigações Pendentes</p>
            <p class="text-3xl font-bold text-gray-800"><?php echo $obrigacoesPendentes; ?></p>
        </div>
        <div class="bg-gray-100 p-3 rounded-full">
            <svg class="h-6 w-6 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
            </svg>
        </div>
    </div>
</div>

<!-- Seção de Gráficos -->
<div class="grid grid-cols-1 lg:grid-cols-5 gap-6 mb-8">
    <!-- Gráfico de Valores por Tipo -->
    <div class="lg:col-span-3 bg-white p-6 rounded-lg shadow-md">
        <h3 class="text-lg font-semibold mb-4">Valores Totais por Tipo de Contrato (Ativos)</h3>
        <div class="h-80">
            <canvas id="chartValoresPorTipo"></canvas>
        </div>
    </div>

    <!-- Gráfico de Cumprimento de Obrigações -->
    <div class="lg:col-span-2 bg-white p-6 rounded-lg shadow-md">
        <h3 class="text-lg font-semibold mb-4">Cumprimento de Obrigações</h3>
        <div class="h-80">
            <canvas id="chartObrigacoesStatus"></canvas>
        </div>
    </div>
</div>

<!-- Seção de Geração de Relatórios -->
<div class="bg-white p-6 rounded-lg shadow-md">
    <h3 class="text-lg font-semibold mb-4 border-b pb-3">Geração de Relatórios</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <!-- Relatório de Vigência -->
        <div class="border p-4 rounded-lg hover:shadow-lg transition-shadow">
            <h4 class="font-semibold text-gray-800">Relatório de Vigência</h4>
            <p class="text-sm text-gray-600 mt-1 mb-3">Exporte uma lista de todos os contratos com suas respectivas datas de início e término.</p>
            <a href="<?php echo BASE_URL; ?>/contratos/exportarRelatorioVigenciaPdf" target="_blank" class="w-full text-center block bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-blue-700">
                Gerar PDF
            </a>
        </div>

        <!-- Relatório Financeiro Consolidado -->
        <div class="border p-4 rounded-lg hover:shadow-lg transition-shadow">
            <h4 class="font-semibold text-gray-800">Relatório Financeiro</h4>
            <p class="text-sm text-gray-600 mt-1 mb-3">Consolide os valores totais, aditivos e parcelas de todos os contratos ativos.</p>
            <button onclick="alert('Gerando relatório financeiro em CSV...')" class="w-full bg-green-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-green-700">
                Gerar CSV
            </button>
        </div>

        <!-- Relatório de Compliance -->
        <div class="border p-4 rounded-lg hover:shadow-lg transition-shadow">
            <h4 class="font-semibold text-gray-800">Relatório de Compliance</h4>
            <p class="text-sm text-gray-600 mt-1 mb-3">Liste o status de conformidade (LGPD, Risco) de todos os contratos para auditoria.</p>
            <button onclick="alert('Gerando relatório de compliance em PDF...')" class="w-full bg-yellow-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-yellow-700">
                Gerar PDF
            </button>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Dados para os gráficos (injetados pelo PHP)
        const valoresPorTipo = <?php echo json_encode($valoresPorTipo); ?>;
        const obrigacoesSummary = <?php echo json_encode($obrigacoesSummary); ?>;

        // Gráfico de Valores por Tipo
        const ctxValores = document.getElementById('chartValoresPorTipo').getContext('2d');
        new Chart(ctxValores, {
            type: 'bar',
            data: {
                labels: valoresPorTipo.map(item => item.tipo),
                datasets: [{
                    label: 'Valor Total (R$)',
                    data: valoresPorTipo.map(item => item.total_valor),
                    backgroundColor: '#38bdf8',
                    borderColor: '#0284c7',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    label += new Intl.NumberFormat('pt-BR', {
                                        style: 'currency',
                                        currency: 'BRL'
                                    }).format(context.parsed.y);
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return new Intl.NumberFormat('pt-BR', {
                                    style: 'currency',
                                    currency: 'BRL'
                                }).format(value);
                            }
                        }
                    }
                }
            }
        });

        // Gráfico de Cumprimento de Obrigações
        const ctxObrigacoes = document.getElementById('chartObrigacoesStatus').getContext('2d');
        new Chart(ctxObrigacoes, {
            type: 'doughnut',
            data: {
                labels: obrigacoesSummary.map(item => item.status),
                datasets: [{
                    label: 'Obrigações',
                    data: obrigacoesSummary.map(item => item.total),
                    backgroundColor: [
                        '#F59E0B', // Pendente
                        '#10B981', // Concluída
                        '#EF4444', // Atrasada
                        '#6B7280' // Outro
                    ],
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                }
            }
        });
    });
</script>