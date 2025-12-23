<div class="flex justify-between items-center mb-4">
    <div>
        <h2 class="text-2xl font-bold"><?php echo htmlspecialchars($pageTitle); ?></h2>
        <p class="text-gray-600">Insira os eventos variáveis para o cálculo da folha de pagamento.</p>
    </div>
    <a href="<?php echo BASE_URL; ?>/rh/folhaDePagamento" class="px-4 py-2 text-sm font-semibold text-gray-700 bg-gray-200 rounded-lg shadow-md hover:bg-gray-300 transition">
        &larr; Voltar
    </a>
</div>

<div class="bg-white p-6 rounded-lg shadow-md">
    <form action="<?php echo BASE_URL; ?>/rh/salvarLancamentos" method="POST">
        <input type="hidden" name="mes" value="<?php echo $mes; ?>">
        <input type="hidden" name="ano" value="<?php echo $ano; ?>">

        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Funcionário</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Horas Extras (50%)</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Horas Extras (100%)</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Faltas (dias)</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Adiant./Outros Desc. (R$)</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (!empty($funcionarios)): ?>
                    <?php foreach ($funcionarios as $func): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                <?php echo htmlspecialchars($func['nome']); ?>
                                <input type="hidden" name="lancamentos[<?php echo $func['id']; ?>][id]" value="<?php echo $func['id']; ?>">
                            </td>
                            <td class="px-6 py-4"><input type="number" name="lancamentos[<?php echo $func['id']; ?>][horas_extras_50]" class="w-24 border-gray-300 rounded-md shadow-sm p-2" value="0"></td>
                            <td class="px-6 py-4"><input type="number" name="lancamentos[<?php echo $func['id']; ?>][horas_extras_100]" class="w-24 border-gray-300 rounded-md shadow-sm p-2" value="0"></td>
                            <td class="px-6 py-4"><input type="number" name="lancamentos[<?php echo $func['id']; ?>][faltas]" class="w-24 border-gray-300 rounded-md shadow-sm p-2" value="0"></td>
                            <td class="px-6 py-4"><input type="number" step="0.01" name="lancamentos[<?php echo $func['id']; ?>][outros_descontos]" class="w-32 border-gray-300 rounded-md shadow-sm p-2" value="0.00"></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">Nenhum funcionário ativo encontrado.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="mt-6 flex justify-end">
            <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-md shadow-sm hover:bg-indigo-700">
                Salvar Lançamentos
            </button>
        </div>
    </form>
</div>