<div class="flex justify-between items-center mb-4">
    <div>
        <h2 class="text-2xl font-bold"><?php echo htmlspecialchars($pageTitle); ?></h2>
        <p class="text-gray-600">Resumo dos valores calculados para a competência.</p>
    </div>
    <div class="flex gap-2">
        <a href="<?php echo BASE_URL; ?>/rh/folhaDePagamento" class="px-4 py-2 text-sm font-semibold text-gray-700 bg-gray-200 rounded-lg shadow-md hover:bg-gray-300 transition">
            &larr; Voltar
        </a>
        <a href="<?php echo BASE_URL; ?>/rh/holerite/<?php echo $mes; ?>/<?php echo $ano; ?>" class="px-4 py-2 text-sm font-semibold text-white bg-green-600 rounded-lg shadow-md hover:bg-green-700 transition" title="Em breve, gerará um PDF com todos os holerites.">
            Gerar Holerites em Lote
        </a>
        <a href="<?php echo BASE_URL; ?>/rh/espelhoFolha/<?php echo $mes; ?>/<?php echo $ano; ?>" target="_blank" class="px-4 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg shadow-md hover:bg-blue-700 transition" title="Gera um relatório consolidado da folha de pagamento.">
            Gerar Espelho da Folha
        </a>
        <a href="<?php echo BASE_URL; ?>/rh/exportarFolhaPdf/<?php echo $mes; ?>/<?php echo $ano; ?>" target="_blank" class="px-4 py-2 text-sm font-semibold text-white bg-red-600 rounded-lg shadow-md hover:bg-red-700 transition" title="Gera uma versão em PDF dos resultados da folha.">
            Exportar (PDF)
        </a>
        <a href="<?php echo BASE_URL; ?>/rh/exportarFolhaContabil/<?php echo $mes; ?>/<?php echo $ano; ?>" class="px-4 py-2 text-sm font-semibold text-white bg-teal-600 rounded-lg shadow-md hover:bg-teal-700 transition" title="Exporta os dados consolidados para o financeiro/contabilidade.">
            Exportar (CSV)
        </a>
    </div>
</div>

<div class="bg-white p-6 rounded-lg shadow-md">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Funcionário</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Salário Bruto</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">INSS</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">IRRF</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Outros Desc.</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Salário Líquido</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            <?php if (!empty($resultados)): ?>
                <?php
                $totalBruto = 0;
                $totalLiquido = 0;
                ?>
                <?php foreach ($resultados as $res): ?>
                    <?php
                    $totalBruto += $res['salario_bruto'];
                    $totalLiquido += $res['salario_liquido'];
                    ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($res['nome']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 text-right">R$ <?php echo number_format($res['salario_bruto'], 2, ',', '.'); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600 text-right">R$ <?php echo number_format($res['inss'], 2, ',', '.'); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600 text-right">R$ <?php echo number_format($res['irrf'], 2, ',', '.'); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600 text-right">R$ <?php echo number_format($res['outros_descontos'], 2, ',', '.'); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-green-700 text-right">R$ <?php echo number_format($res['salario_liquido'], 2, ',', '.'); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="<?php echo BASE_URL; ?>/rh/holerite/<?php echo $mes; ?>/<?php echo $ano; ?>/<?php echo htmlspecialchars($res['id']); ?>" class="text-indigo-600 hover:text-indigo-900">Ver Holerite</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <tr class="bg-gray-100 font-bold">
                    <td class="px-6 py-4 text-right">TOTAIS:</td>
                    <td class="px-6 py-4 text-right">R$ <?php echo number_format($totalBruto, 2, ',', '.'); ?></td>
                    <td colspan="3"></td>
                    <td class="px-6 py-4 text-right">R$ <?php echo number_format($totalLiquido, 2, ',', '.'); ?></td>
                    <td></td>
                </tr>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">Nenhum resultado encontrado para esta competência.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>