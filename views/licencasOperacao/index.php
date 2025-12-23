<h2 class="text-2xl font-bold mb-4">Módulo Licenças de Operação</h2>
<p class="mb-6 text-gray-600">Controle rigoroso sobre todas as licenças, alvarás e certificações necessárias para a operação.</p>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <!-- Card 1: Total de Licenças -->
    <div class="bg-white p-6 rounded-lg shadow-lg border-l-4 border-indigo-500">
        <h3 class="font-semibold text-gray-500">Total de Licenças Ativas</h3>
        <p class="text-3xl font-bold text-indigo-600"><?php echo $totalLicencas ?? 0; ?></p>
        <p class="text-sm text-gray-400 mt-2">Documentos em conformidade</p>
    </div>
    <!-- Card 2: Vencimento Próximo (30 dias) -->
    <div class="bg-white p-6 rounded-lg shadow-lg border-l-4 border-red-500">
        <h3 class="font-semibold text-gray-500">Vencendo em 30 Dias</h3>
        <p class="text-3xl font-bold text-red-600"><?php echo $vencimento30Dias ?? 0; ?></p>
        <p class="text-sm text-gray-400 mt-2">Ações de renovação urgentes</p>
    </div>
    <!-- Card 3: Licenças Vencidas -->
    <div class="bg-white p-6 rounded-lg shadow-lg border-l-4 border-gray-500">
        <h3 class="font-semibold text-gray-500">Licenças Vencidas</h3>
        <p class="text-3xl font-bold text-gray-600"><?php echo $vencidas ?? 0; ?></p>
        <p class="text-sm text-gray-400 mt-2">Zero é a meta!</p>
    </div>
    <!-- Card 4: Em Processo de Renovação -->
    <div class="bg-white p-6 rounded-lg shadow-lg border-l-4 border-yellow-500">
        <h3 class="font-semibold text-gray-500">Em Renovação</h3>
        <p class="text-3xl font-bold text-yellow-600"><?php echo $emRenovacao ?? 0; ?></p>
        <p class="text-sm text-gray-400 mt-2">Acompanhamento e prazos</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- Lista de Licenças Críticas (Tabela Principal) -->
    <div class="lg:col-span-2 bg-white p-6 rounded-lg shadow-md">
        <h3 class="text-lg font-semibold mb-4 border-b pb-2 flex justify-between items-center">
            Monitoramento de Licenças Críticas
            <a href="<?php echo BASE_URL; ?>/licencasOperacao/cadastro" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">
                + Nova Licença
            </a>
        </h3>

        <?php if (!empty($criticalList)): ?>
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Licença</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Órgão Emissor</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vencimento</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($criticalList as $license): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo $license['id']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800">
                                <a href="<?php echo BASE_URL; ?>/licencasOperacao/detalhe/<?php echo $license['id']; ?>" class="text-indigo-600 hover:text-indigo-800 font-medium">
                                    <?php echo $license['nome']; ?>
                                </a>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $license['orgao']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold 
                            <?php
                            // Verifica a proximidade do vencimento
                            $vencimento = strtotime($license['vencimento']);
                            $diff = ($vencimento - time()) / (60 * 60 * 24);
                            if ($diff <= 30) echo 'text-red-600';
                            elseif ($diff <= 90) echo 'text-yellow-600';
                            else echo 'text-green-600';
                            ?>
                        ">
                                <?php echo $license['vencimento']; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                <?php
                                if ($license['status'] === 'Pendente Renovação') echo 'bg-orange-100 text-orange-800';
                                elseif ($license['status'] === 'Vencendo') echo 'bg-red-100 text-red-800';
                                else echo 'bg-green-100 text-green-800';
                                ?>">
                                    <?php echo $license['status']; ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="text-gray-500">Nenhuma licença com status crítico no momento.</p>
        <?php endif; ?>
    </div>

    <!-- Prazos e Documentação -->
    <div class="bg-white p-6 rounded-lg shadow-md lg:col-span-1">
        <h3 class="text-lg font-semibold mb-4">Ações Rápidas de Conformidade</h3>

        <label for="dataRenovacao" class="block text-sm font-medium text-gray-700 mb-1">Buscar por Data de Renovação</label>
        <input type="date" id="dataRenovacao" class="w-full mb-4 border border-gray-300 rounded-md shadow-sm p-2 focus:ring-indigo-500 focus:border-indigo-500">

        <button onclick="alert('Funcionalidade de Upload de Documentos em desenvolvimento.')" class="w-full mb-3 inline-flex items-center justify-center px-4 py-2 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none">
            Upload de Documento (Renovação)
        </button>

        <button onclick="alert('Funcionalidade de Relatório de Não Conformidades em desenvolvimento.')" class="w-full inline-flex items-center justify-center px-4 py-2 border border-gray-300 text-base font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none">
            Relatório de Não Conformidade
        </button>

        <div class="mt-6 pt-4 border-t">
            <p class="text-sm text-gray-500">Mantenha os arquivos digitais de todas as licenças anexados para auditoria.</p>
        </div>
    </div>
</div>