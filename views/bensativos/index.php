<h2 class="text-2xl font-bold mb-4">Módulo Bens e Ativos</h2>
<p class="mb-6 text-gray-600">Controle, inventário, depreciação e agendamento de manutenção de todos os bens da empresa.</p>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <!-- Card 1: Total de Ativos -->
    <div class="bg-white p-6 rounded-lg shadow-lg border-l-4 border-sky-500">
        <h3 class="font-semibold text-gray-500">Total de Ativos Registrados</h3>
        <p class="text-3xl font-bold text-sky-600"><?php echo $totalAtivos ?? 0; ?></p>
        <p class="text-sm text-gray-400 mt-2">Ativos físicos e digitais</p>
    </div>
    <!-- Card 2: Valor Total Estimado -->
    <div class="bg-white p-6 rounded-lg shadow-lg border-l-4 border-green-500">
        <h3 class="font-semibold text-gray-500">Valor Total Estimado</h3>
        <p class="text-2xl font-bold text-green-600">R$ <?php echo number_format($valorTotalEstimado ?? 0, 2, ',', '.'); ?></p>
        <p class="text-sm text-gray-400 mt-2">Valor contábil atual</p>
    </div>
    <!-- Card 3: Manutenção Pendente -->
    <div class="bg-white p-6 rounded-lg shadow-lg border-l-4 border-red-500">
        <h3 class="font-semibold text-gray-500">Manutenção Pendente</h3>
        <p class="text-3xl font-bold text-red-600"><?php echo $manutencaoPendente ?? 0; ?></p>
        <p class="text-sm text-gray-400 mt-2">Inspeções e reparos urgentes</p>
    </div>
    <!-- Card 4: Ativos em Depreciação -->
    <div class="bg-white p-6 rounded-lg shadow-lg border-l-4 border-gray-500">
        <h3 class="font-semibold text-gray-500">Ativos em Depreciação</h3>
        <p class="text-3xl font-bold text-gray-600"><?php echo $ativosDepreciacao ?? 0; ?></p>
        <p class="text-sm text-gray-400 mt-2">Próxima baixa: 5</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- Lista de Manutenções Pendentes (Tabela Principal) -->
    <div class="lg:col-span-2 bg-white p-6 rounded-lg shadow-md">
        <h3 class="text-lg font-semibold mb-4 border-b pb-2 flex justify-between items-center">
            Próximas Manutenções Agendadas
            <a href="/sysenvicorp/bensAtivos/cadastro" class="text-sm font-medium text-sky-600 hover:text-sky-800">
                + Novo Ativo
            </a>
        </h3>

        <?php if (!empty($maintenanceList)): ?>
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ativo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Local/Setor</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Próxima Manutenção</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($maintenanceList as $asset): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo $asset['id']; ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800">
                            <a href="/sysenvicorp/bensAtivos/detalhe?id=<?php echo $asset['id']; ?>" class="text-sky-600 hover:text-sky-800 font-medium">
                                <?php echo $asset['nome']; ?>
                            </a>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $asset['local']; ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $asset['proximaManutencao']; ?></td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                <?php 
                                    if ($asset['status'] === 'Urgente') echo 'bg-red-100 text-red-800';
                                    elseif ($asset['status'] === 'Programada') echo 'bg-yellow-100 text-yellow-800';
                                    else echo 'bg-gray-100 text-gray-800';
                                ?>">
                                <?php echo $asset['status']; ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="text-gray-500">Nenhum ativo com manutenção agendada no momento.</p>
        <?php endif; ?>
    </div>

    <!-- Filtros e Ações Rápidas -->
    <div class="bg-white p-6 rounded-lg shadow-md lg:col-span-1">
        <h3 class="text-lg font-semibold mb-4">Ações do Inventário</h3>
        
        <label for="filtroSetor" class="block text-sm font-medium text-gray-700 mb-1">Filtrar por Setor</label>
        <select id="filtroSetor" class="w-full mb-4 border border-gray-300 rounded-md shadow-sm p-2 focus:ring-sky-500 focus:border-sky-500">
            <option>Todos os Setores</option>
            <option>TI</option>
            <option>Operacional</option>
            <option>Administrativo</option>
            <option>Campo</option>
        </select>
        
        <button onclick="alert('Funcionalidade de Gerar Relatório de Inventário em desenvolvimento.')" class="w-full mb-3 inline-flex items-center justify-center px-4 py-2 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none">
            Gerar Inventário Completo
        </button>
        
        <button onclick="alert('Funcionalidade de Mapeamento de Ativos em desenvolvimento.')" class="w-full inline-flex items-center justify-center px-4 py-2 border border-gray-300 text-base font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none">
            Mapeamento (Localização)
        </button>
        
        <div class="mt-6 pt-4 border-t">
            <p class="text-sm text-gray-500">Gerencie a vida útil e o custo-benefício de cada ativo com precisão.</p>
        </div>
    </div>
</div>
