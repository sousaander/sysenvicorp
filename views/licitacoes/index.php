<h2 class="text-2xl font-bold mb-4">Módulo Licitações e Propostas</h2>
<p class="mb-6 text-gray-600">Gerenciamento completo do ciclo de licitações: desde o edital até o resultado final.</p>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <!-- Card 1: Total de Licitações -->
    <div class="bg-white p-6 rounded-lg shadow-lg border-l-4 border-purple-500">
        <h3 class="font-semibold text-gray-500">Total de Licitações Monitoradas</h3>
        <p class="text-3xl font-bold text-purple-600"><?php echo $totalLicitacoes ?? 0; ?></p>
        <p class="text-sm text-gray-400 mt-2">Ativas e Finalizadas</p>
    </div>
    <!-- Card 2: Em Andamento -->
    <div class="bg-white p-6 rounded-lg shadow-lg border-l-4 border-sky-500">
        <h3 class="font-semibold text-gray-500">Em Andamento (Proposta Enviada)</h3>
        <p class="text-3xl font-bold text-sky-600"><?php echo $emAndamento ?? 0; ?></p>
        <p class="text-sm text-gray-400 mt-2">Aguardando resultado/abertura</p>
    </div>
    <!-- Card 3: Prazos Críticos -->
    <div class="bg-white p-6 rounded-lg shadow-lg border-l-4 border-red-500">
        <h3 class="font-semibold text-gray-500">Propostas a Enviar (30 dias)</h3>
        <p class="text-3xl font-bold text-red-600"><?php echo $propostasVencer ?? 0; ?></p>
        <p class="text-sm text-gray-400 mt-2">Deadline se aproximando</p>
    </div>
    <!-- Card 4: Ganhas no Mês -->
    <div class="bg-white p-6 rounded-lg shadow-lg border-l-4 border-green-500">
        <h3 class="font-semibold text-gray-500">Licitações Ganhas no Mês</h3>
        <p class="text-3xl font-bold text-green-600"><?php echo $ganhasMes ?? 0; ?></p>
        <p class="text-sm text-gray-400 mt-2">Resultados positivos</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- Lista de Licitações Críticas (Tabela Principal) -->
    <div class="lg:col-span-2 bg-white p-6 rounded-lg shadow-md">
        <h3 class="text-lg font-semibold mb-4 border-b pb-2 flex justify-between items-center">
            Prazos Críticos e Status
            <a href="/sysenvicorp/licitacoes/nova" class="text-sm font-medium text-purple-600 hover:text-purple-800">
                + Nova Licitação
            </a>
        </h3>

        <?php if (!empty($criticalList)): ?>
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Objeto da Licitação</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente/Órgão</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Prazo de Envio</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($criticalList as $licitacao): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo $licitacao['id']; ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800">
                            <a href="/sysenvicorp/licitacoes/detalhe?id=<?php echo $licitacao['id']; ?>" class="text-purple-600 hover:text-purple-800 font-medium">
                                <?php echo $licitacao['objeto']; ?>
                            </a>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $licitacao['cliente']; ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold 
                            <?php 
                                // Verifica a proximidade do prazo
                                $prazo = strtotime(str_replace('/', '-', $licitacao['prazoEnvio']));
                                $diff = ($prazo - time()) / (60 * 60 * 24);
                                if ($diff <= 15) echo 'text-red-600';
                                elseif ($diff <= 45) echo 'text-orange-600';
                                else echo 'text-green-600';
                            ?>
                        ">
                            <?php echo $licitacao['prazoEnvio']; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                <?php 
                                    if ($licitacao['status'] === 'Proposta Pendente') echo 'bg-red-100 text-red-800';
                                    elseif ($licitacao['status'] === 'Em Elaboração') echo 'bg-yellow-100 text-yellow-800';
                                    elseif ($licitacao['status'] === 'Proposta Enviada') echo 'bg-sky-100 text-sky-800';
                                    else echo 'bg-green-100 text-green-800';
                                ?>">
                                <?php echo $licitacao['status']; ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="text-gray-500">Nenhuma licitação com prazo crítico no momento.</p>
        <?php endif; ?>
    </div>

    <!-- Filtros e Documentos -->
    <div class="bg-white p-6 rounded-lg shadow-md lg:col-span-1">
        <h3 class="text-lg font-semibold mb-4">Gerenciamento de Documentos</h3>
        
        <label for="tipoLicitacao" class="block text-sm font-medium text-gray-700 mb-1">Tipo de Licitação</label>
        <select id="tipoLicitacao" class="w-full mb-4 border border-gray-300 rounded-md shadow-sm p-2 focus:ring-purple-500 focus:border-purple-500">
            <option>Todas</option>
            <option>Concorrência Pública</option>
            <option>Tomada de Preços</option>
            <option>Convite/Dispensa</option>
        </select>
        
        <label for="buscaCliente" class="block text-sm font-medium text-gray-700 mb-1">Buscar por Cliente</label>
        <input type="text" id="buscaCliente" placeholder="Ex: Petrocorp S.A." class="w-full mb-4 border border-gray-300 rounded-md shadow-sm p-2 focus:ring-purple-500 focus:border-purple-500">

        <button onclick="alert('Funcionalidade de Relatório de Desempenho em Licitações em desenvolvimento.')" class="w-full mb-3 inline-flex items-center justify-center px-4 py-2 border border-gray-300 text-base font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none">
            Relatório de Desempenho
        </button>
        
        <div class="mt-6 pt-4 border-t">
            <p class="text-sm text-gray-500">Anexe editais e propostas finais diretamente no sistema.</p>
        </div>
    </div>
</div>
