<h2 class="text-2xl font-bold mb-4">Módulo POPs (Procedimentos Operacionais Padrão)</h2>
<p class="mb-6 text-gray-600">Gerenciamento, distribuição e controle de versão de todos os Procedimentos Operacionais Padrão da SysEnviCorp.</p>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <!-- Card 1: Total de POPs -->
    <div class="bg-white p-6 rounded-lg shadow-lg border-l-4 border-teal-500">
        <h3 class="font-semibold text-gray-500">Total de POPs Ativos</h3>
        <p class="text-3xl font-bold text-teal-600"><?php echo $totalPops ?? 0; ?></p>
        <p class="text-sm text-gray-400 mt-2">Procedimentos padronizados</p>
    </div>
    <!-- Card 2: Em Processo de Revisão -->
    <div class="bg-white p-6 rounded-lg shadow-lg border-l-4 border-orange-500">
        <h3 class="font-semibold text-gray-500">Em Revisão / Edição</h3>
        <p class="text-3xl font-bold text-orange-600"><?php echo $emRevisao ?? 0; ?></p>
        <p class="text-sm text-gray-400 mt-2">Aguardando aprovação</p>
    </div>
    <!-- Card 3: POPs Expirados (Vencidos) -->
    <div class="bg-white p-6 rounded-lg shadow-lg border-l-4 border-red-500">
        <h3 class="font-semibold text-gray-500">POPs Expirados</h3>
        <p class="text-3xl font-bold text-red-600"><?php echo $expirados ?? 0; ?></p>
        <p class="text-sm text-gray-400 mt-2">Revisão obrigatória pendente</p>
    </div>
    <!-- Card 4: Novos no Mês -->
    <div class="bg-white p-6 rounded-lg shadow-lg border-l-4 border-blue-500">
        <h3 class="font-semibold text-gray-500">Novos POPs no Mês</h3>
        <p class="text-3xl font-bold text-blue-600"><?php echo $novosMes ?? 0; ?></p>
        <p class="text-sm text-gray-400 mt-2">Última atualização: hoje</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- Lista de POPs Críticos (Tabela Principal) -->
    <div class="lg:col-span-2 bg-white p-6 rounded-lg shadow-md">
        <h3 class="text-lg font-semibold mb-4 border-b pb-2 flex justify-between items-center">
            Monitoramento de POPs com Revisão Pendente
            <a href="/sysenvicorp/pops/novo" class="text-sm font-medium text-teal-600 hover:text-teal-800">
                + Novo POP
            </a>
        </h3>

        <?php if (!empty($criticalList)): ?>
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Título do Procedimento</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Última Revisão</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Próxima Revisão</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($criticalList as $pop): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo $pop['id']; ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800">
                            <a href="/sysenvicorp/pops/visualizar?id=<?php echo $pop['id']; ?>" class="text-teal-600 hover:text-teal-800 font-medium">
                                <?php echo $pop['titulo']; ?>
                            </a>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $pop['ultimaRevisao']; ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold 
                            <?php 
                                if ($pop['status'] === 'Expirado') echo 'text-red-600';
                                elseif ($pop['status'] === 'Em Revisão') echo 'text-orange-600';
                                else echo 'text-green-600';
                            ?>
                        ">
                            <?php echo $pop['proximaRevisao']; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                <?php 
                                    if ($pop['status'] === 'Expirado') echo 'bg-red-100 text-red-800';
                                    elseif ($pop['status'] === 'Em Revisão') echo 'bg-orange-100 text-orange-800';
                                    else echo 'bg-green-100 text-green-800';
                                ?>">
                                <?php echo $pop['status']; ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="text-gray-500">Nenhum POP com pendência de revisão no momento.</p>
        <?php endif; ?>
    </div>

    <!-- Filtros e Busca Rápida -->
    <div class="bg-white p-6 rounded-lg shadow-md lg:col-span-1">
        <h3 class="text-lg font-semibold mb-4">Busca e Ações Rápidas</h3>
        
        <label for="buscaPop" class="block text-sm font-medium text-gray-700 mb-1">Buscar por Título/ID</label>
        <input type="text" id="buscaPop" placeholder="Ex: POP-001 ou Químicos" class="w-full mb-4 border border-gray-300 rounded-md shadow-sm p-2 focus:ring-teal-500 focus:border-teal-500">
        
        <label for="filtroSetorPop" class="block text-sm font-medium text-gray-700 mb-1">Filtrar por Setor</label>
        <select id="filtroSetorPop" class="w-full mb-4 border border-gray-300 rounded-md shadow-sm p-2 focus:ring-teal-500 focus:border-teal-500">
            <option>Todos os Setores</option>
            <option>Operacional</option>
            <option>Projetos</option>
            <option>Segurança</option>
            <option>Administrativo</option>
        </select>
        
        <button onclick="alert('Funcionalidade de Relatório de Conformidade de POPs em desenvolvimento.')" class="w-full inline-flex items-center justify-center px-4 py-2 border border-gray-300 text-base font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none">
            Relatório de Conformidade
        </button>
        
        <div class="mt-6 pt-4 border-t">
            <p class="text-sm text-gray-500">A manutenção da qualidade dos POPs garante a segurança e eficiência da SysEnviCorp.</p>
        </div>
    </div>
</div>
