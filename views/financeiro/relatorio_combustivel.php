<?php
// views/financeiro/relatorio_combustivel.php
?>
<div class="flex justify-between items-center mb-6">
    <div>
        <h2 class="text-2xl font-bold text-gray-800"><?php echo htmlspecialchars($pageTitle); ?></h2>
        <p class="text-gray-600">Acompanhe as despesas de abastecimento por veículo.</p>
    </div>
    <a href="<?php echo BASE_URL; ?>/financeiro" class="bg-gray-200 text-gray-800 px-4 py-2 rounded-md hover:bg-gray-300 font-medium flex items-center">
        &larr; Voltar
    </a>
</div>

<div class="bg-white p-6 rounded-lg shadow-md mb-6">
    <form action="<?php echo BASE_URL; ?>/financeiro/relatorioCombustivel" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
        <div>
            <label for="placa" class="block text-sm font-medium text-gray-700 mb-1">Placa do Veículo</label>
            <input type="text" name="placa" id="placa" value="<?php echo htmlspecialchars($filtros['placa'] ?? ''); ?>" placeholder="Ex: ABC-1234" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-sky-500 focus:border-sky-500 p-2">
        </div>
        <div>
            <label for="data_inicio" class="block text-sm font-medium text-gray-700 mb-1">Data Início</label>
            <input type="date" name="data_inicio" id="data_inicio" value="<?php echo htmlspecialchars($filtros['data_inicio'] ?? ''); ?>" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-sky-500 focus:border-sky-500 p-2">
        </div>
        <div>
            <label for="data_fim" class="block text-sm font-medium text-gray-700 mb-1">Data Fim</label>
            <input type="date" name="data_fim" id="data_fim" value="<?php echo htmlspecialchars($filtros['data_fim'] ?? ''); ?>" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-sky-500 focus:border-sky-500 p-2">
        </div>
        <div class="flex gap-2">
            <button type="submit" class="bg-sky-600 text-white px-4 py-2 rounded-md hover:bg-sky-700 font-medium w-full">Filtrar</button>
            <button type="submit" formaction="<?php echo BASE_URL; ?>/financeiro/exportarRelatorioCombustivelPdf" class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 font-medium w-full flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg> PDF
            </button>
        </div>
    </form>
</div>

<div class="bg-white rounded-lg shadow-md overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Placa</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descrição</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Litros</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Hodômetro</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Valor Total</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (!empty($transacoes)): ?>
                    <?php 
                    $totalValor = 0;
                    $totalLitros = 0;
                    foreach ($transacoes as $t): 
                        $totalValor += $t['valor'];
                        $totalLitros += $t['litros'];
                    ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo date('d/m/Y', strtotime($t['vencimento'])); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-800"><?php echo htmlspecialchars($t['placa_veiculo'] ?: '-'); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars(str_replace('Prestação de Contas: ', '', $t['descricao'])); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-700"><?php echo $t['litros'] > 0 ? number_format($t['litros'], 2, ',', '.') . ' L' : '-'; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-700"><?php echo $t['hodometro'] > 0 ? number_format($t['hodometro'], 0, ',', '.') . ' Km' : '-'; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium text-red-600">R$ <?php echo number_format($t['valor'], 2, ',', '.'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-gray-500">Nenhum registro encontrado para os filtros selecionados.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
            <?php if (!empty($transacoes)): ?>
                <tfoot class="bg-gray-50 font-semibold">
                    <tr>
                        <td colspan="3" class="px-6 py-3 text-right">Totais:</td>
                        <td class="px-6 py-3 text-right"><?php echo number_format($totalLitros, 2, ',', '.'); ?> L</td>
                        <td></td>
                        <td class="px-6 py-3 text-right text-red-700">R$ <?php echo number_format($totalValor, 2, ',', '.'); ?></td>
                    </tr>
                </tfoot>
            <?php endif; ?>
        </table>
    </div>
</div>
