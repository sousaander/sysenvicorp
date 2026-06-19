<div class="flex justify-between items-start mb-6">
    <div>
        <h2 class="text-2xl font-bold"><?php echo htmlspecialchars($pageTitle); ?></h2>
        <p class="text-gray-600">Realize a checagem física dos ativos e concilie com os registros do sistema.</p>
    </div>
    <a href="<?php echo BASE_URL; ?>/patrimonio" class="px-4 py-2 text-sm font-semibold text-gray-700 bg-gray-200 rounded-lg shadow-md hover:bg-gray-300 transition">
        &larr; Voltar para o Dashboard
    </a>
</div>

<!-- Relatório de Divergências (se houver) -->
<?php if (isset($relatorioDivergencias) && !empty($relatorioDivergencias)) : ?>
    <div class="mb-8 bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-r-lg">
        <h3 class="text-xl font-bold text-yellow-800 mb-4">Relatório de Divergências do Último Inventário</h3>

        <?php if (!empty($relatorioDivergencias['nao_localizados'])) : ?>
            <div class="mb-4">
                <h4 class="font-semibold text-gray-700">Bens Não Localizados:</h4>
                <ul class="list-disc list-inside text-sm text-red-700">
                    <?php foreach ($relatorioDivergencias['nao_localizados'] as $bem) : ?>
                        <li><?php echo htmlspecialchars($bem['nome'] . ' (#' . $bem['numero_patrimonio'] . ')'); ?> - Esperado em: <?php echo htmlspecialchars($bem['localizacao']); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if (!empty($relatorioDivergencias['localizacao_divergente'])) : ?>
            <div class="mb-4">
                <h4 class="font-semibold text-gray-700">Bens com Localização Divergente (Local Atualizado):</h4>
                <ul class="list-disc list-inside text-sm text-blue-700">
                    <?php foreach ($relatorioDivergencias['localizacao_divergente'] as $bem) : ?>
                        <li><?php echo htmlspecialchars($bem['nome'] . ' (#' . $bem['numero_patrimonio'] . ')'); ?> - Esperado em: "<?php echo htmlspecialchars($bem['localizacao']); ?>", encontrado em: "<?php echo htmlspecialchars($bem['local_novo']); ?>"</li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if (empty($relatorioDivergencias['nao_localizados']) && empty($relatorioDivergencias['localizacao_divergente'])) : ?>
            <p class="text-green-700 font-semibold">Nenhuma divergência encontrada no último inventário.</p>
        <?php endif; ?>
    </div>
<?php endif; ?>


<!-- Formulário de Inventário Físico -->
<div class="bg-white p-6 rounded-lg shadow-md">
    <form action="<?php echo BASE_URL; ?>/patrimonio/conciliarInventario" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
        <h3 class="text-lg font-semibold mb-4 border-b pb-2">Lista de Checagem de Ativos</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ativo</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Localização Registrada</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" colspan="2">Status da Checagem</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (!empty($bens)) : ?>
                        <?php foreach ($bens as $bem) : ?>
                            <tr class="hover:bg-gray-50" data-bem-id="<?php echo $bem['id']; ?>">
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($bem['nome']); ?></div>
                                    <div class="text-xs text-gray-500">#<?php echo htmlspecialchars($bem['numero_patrimonio']); ?></div>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-700">
                                    <?php echo htmlspecialchars($bem['localizacao']); ?>
                                    <div class="text-xs text-gray-500">Resp: <?php echo htmlspecialchars($bem['responsavel'] ?: 'N/D'); ?></div>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm">
                                    <select name="inventario[<?php echo $bem['id']; ?>][status_checagem]" class="status-checagem-select w-full border-gray-300 rounded-md shadow-sm p-2 text-sm">
                                        <option value="localizado">✅ Localizado</option>
                                        <option value="nao_localizado">❌ Não Localizado</option>
                                        <option value="localizado_outro_setor">🔄 Localizado em outro setor</option>
                                    </select>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm">
                                    <input type="text" name="inventario[<?php echo $bem['id']; ?>][novo_local]" placeholder="Informar novo local/setor" class="novo-local-input hidden w-full border-gray-300 rounded-md shadow-sm p-2 text-sm">
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-center text-gray-500">Nenhum bem ativo para inventariar.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="mt-8 pt-4 border-t flex justify-end">
            <button type="submit" class="px-6 py-2 text-sm font-semibold text-white bg-green-600 rounded-lg shadow-md hover:bg-green-700 transition">
                Conciliar Inventário e Gerar Relatório
            </button>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const tableRows = document.querySelectorAll('tr[data-bem-id]');

        tableRows.forEach(row => {
            const select = row.querySelector('.status-checagem-select');
            const input = row.querySelector('.novo-local-input');

            select.addEventListener('change', () => {
                if (select.value === 'localizado_outro_setor') {
                    input.classList.remove('hidden');
                    input.required = true;
                } else {
                    input.classList.add('hidden');
                    input.required = false;
                    input.value = '';
                }
            });
        });
    });
</script>