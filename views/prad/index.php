<h2 class="text-2xl font-bold mb-4">Módulo de Planos de Recuperação de Área Degradada (PRAD)</h2>
<p class="mb-6 text-gray-600">Gestão completa dos PRADs: acompanhamento de cronogramas, monitoramento de indicadores de recuperação e controle de entregas de laudos.</p>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <!-- Card 1: Total Ativos -->
    <div class="bg-white p-6 rounded-lg shadow-lg border-l-4 border-emerald-500">
        <h3 class="font-semibold text-gray-500">Total de PRADs Ativos</h3>
        <p class="text-3xl font-bold text-emerald-600"><?php echo $totalAtivos ?? 0; ?></p>
        <p class="text-sm text-gray-400 mt-2">Em execução ou fase de monitoramento</p>
    </div>
    <!-- Card 2: Relatórios Iminentes -->
    <div class="bg-white p-6 rounded-lg shadow-lg border-l-4 border-amber-500">
        <h3 class="font-semibold text-gray-500">Relatórios Vencendo (30 dias)</h3>
        <p class="text-3xl font-bold text-amber-600"><?php echo $relatoriosVencendo30dias ?? 0; ?></p>
        <p class="text-sm text-gray-400 mt-2">Próximas entregas de laudos</p>
    </div>
    <!-- Card 3: Área Total Monitorada -->
    <div class="bg-white p-6 rounded-lg shadow-lg border-l-4 border-sky-500">
        <h3 class="font-semibold text-gray-500">Área Total Monitorada (ha)</h3>
        <p class="text-3xl font-bold text-sky-600"><?php echo $areasMonitoradas ?? 0; ?></p>
        <p class="text-sm text-gray-400 mt-2">Hectares sob gestão ativa</p>
    </div>
    <!-- Card 4: Status Crítico -->
    <div class="bg-white p-6 rounded-lg shadow-lg border-l-4 border-red-500">
        <h3 class="font-semibold text-gray-500">PRADs em Status Crítico</h3>
        <p class="text-3xl font-bold text-red-600"><?php echo $statusCritico ?? 0; ?></p>
        <p class="text-sm text-gray-400 mt-2">Atraso ou não conformidade</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- Lista de PRADs Críticos (Tabela Principal) -->
    <div class="lg:col-span-2 bg-white p-6 rounded-lg shadow-md">
        <h3 class="text-lg font-semibold mb-4 border-b pb-2 flex justify-between items-center">
            Acompanhamento de Prazos e Status de Recuperação
            <a href="/sysenvicorp/prad/novo" class="text-sm font-medium text-emerald-600 hover:text-emerald-800">
                + Novo PRAD
            </a>
        </h3>

        <?php if (!empty($criticalList)): ?>
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Localização</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Etapa Atual</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Próximo Relatório</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($criticalList as $prad): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo $prad['id']; ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800">
                            <a href="/sysenvicorp/prad/detalhe?id=<?php echo $prad['id']; ?>" class="text-emerald-600 hover:text-emerald-800 font-medium">
                                <?php echo $prad['cliente']; ?>
                            </a>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $prad['localizacao']; ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $prad['etapaAtual']; ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-500">
                            <?php echo $prad['proximoRelatorio']; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                <?php 
                                    if ($prad['status'] === 'Atrasado') echo 'bg-red-100 text-red-800';
                                    elseif ($prad['status'] === 'Relatório Iminente') echo 'bg-amber-100 text-amber-800';
                                    elseif ($prad['status'] === 'Em Conformidade') echo 'bg-green-100 text-green-800';
                                    else echo 'bg-gray-100 text-gray-800';
                                ?>">
                                <?php echo $prad['status']; ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="text-gray-500">Nenhum PRAD com status crítico no momento. Acompanhamento em dia!</p>
        <?php endif; ?>
    </div>

    <!-- Indicadores de Recuperação e Ações Rápidas -->
    <div class="bg-white p-6 rounded-lg shadow-md lg:col-span-1">
        <h3 class="text-lg font-semibold mb-4">Indicadores de Recuperação</h3>
        
        <div class="space-y-4 mb-6">
            <div>
                <p class="text-sm font-medium text-gray-700">Densidade de Cobertura Vegetal (%)</p>
                <div class="w-full bg-gray-200 rounded-full h-3">
                    <div class="bg-green-500 h-3 rounded-full" style="width: 78%"></div>
                </div>
                <p class="text-xs text-gray-500 mt-1">Média Atual: 78% (Meta: 85%)</p>
            </div>

            <div>
                <p class="text-sm font-medium text-gray-700">Conformidade Legal (%)</p>
                <div class="w-full bg-gray-200 rounded-full h-3">
                    <div class="bg-blue-500 h-3 rounded-full" style="width: 95%"></div>
                </div>
                <p class="text-xs text-gray-500 mt-1">95% dos laudos enviados e aprovados</p>
            </div>
        </div>

        <button onclick="alert('Funcionalidade para upload de Laudos e Fotos de Monitoramento em desenvolvimento.')" class="w-full mb-3 inline-flex items-center justify-center px-4 py-2 border border-gray-300 text-base font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none">
            Upload de Laudo/Monitoramento
        </button>
        
        <div class="mt-6 pt-4 border-t">
            <p class="text-sm text-gray-500">A recuperação da área degradada é um processo contínuo que exige monitoramento rigoroso e registro fotográfico.</p>
        </div>
    </div>
</div>
