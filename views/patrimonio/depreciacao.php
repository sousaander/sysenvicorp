<div class="flex justify-between items-start mb-6">
    <div>
        <h2 class="text-2xl font-bold"><?php echo htmlspecialchars($pageTitle); ?></h2>
        <p class="text-gray-600">Acompanhe o cálculo de depreciação e registre reavaliações de mercado dos seus ativos.</p>
    </div>
    <a href="<?php echo BASE_URL; ?>/patrimonio" class="px-4 py-2 text-sm font-semibold text-gray-700 bg-gray-200 rounded-lg shadow-md hover:bg-gray-300 transition">
        &larr; Voltar para o Dashboard
    </a>
</div>

<!-- Tabela de Depreciação -->
<div class="bg-white p-6 rounded-lg shadow-md">
    <h3 class="text-lg font-semibold mb-4 border-b pb-2">Cálculo de Depreciação Linear</h3>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ativo</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valor Aquisição</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Deprec. Mensal</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Deprec. Acumulada</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valor Contábil Atual</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (!empty($bens)) : ?>
                    <?php foreach ($bens as $bem) : ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($bem['nome']); ?></div>
                                <div class="text-xs text-gray-500">#<?php echo htmlspecialchars($bem['numero_patrimonio']); ?> | Aquisição: <?php echo date('d/m/Y', strtotime($bem['data_aquisicao'])); ?></div>
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-700">R$ <?php echo number_format($bem['valor_aquisicao'], 2, ',', '.'); ?></td>
                            <td class="px-4 py-4 whitespace-nowrap text-sm text-blue-600">- R$ <?php echo number_format($bem['depreciacao_mensal'], 2, ',', '.'); ?></td>
                            <td class="px-4 py-4 whitespace-nowrap text-sm text-red-600">- R$ <?php echo number_format($bem['depreciacao_acumulada'], 2, ',', '.'); ?></td>
                            <td class="px-4 py-4 whitespace-nowrap text-sm font-bold text-green-700">R$ <?php echo number_format($bem['valor_contabil'], 2, ',', '.'); ?></td>
                            <td class="px-4 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button class="open-reavaliacao-modal text-indigo-600 hover:text-indigo-900"
                                    data-bem-id="<?php echo $bem['id']; ?>"
                                    data-bem-nome="<?php echo htmlspecialchars($bem['nome']); ?>">
                                    Reavaliar
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">Nenhum bem depreciável encontrado. Verifique o cadastro dos bens.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <!-- Paginação -->
    <?php if ($totalPaginas > 1) : ?>
        <div class="mt-4 flex justify-end items-center">
            <nav class="flex items-center justify-end space-x-2">
                <a href="<?php echo BASE_URL; ?>/patrimonio/depreciacao?page=<?php echo ($paginaAtual > 1 ? $paginaAtual - 1 : 1); ?>" class="<?php echo ($paginaAtual <= 1) ? 'pointer-events-none text-gray-400' : 'text-gray-600 hover:text-sky-600'; ?> px-3 py-1 rounded-md text-sm font-medium">
                    Anterior
                </a>
                <?php for ($i = 1; $i <= $totalPaginas; $i++) : ?>
                    <a href="<?php echo BASE_URL; ?>/patrimonio/depreciacao?page=<?php echo $i; ?>" class="<?php echo ($i == $paginaAtual) ? 'bg-sky-500 text-white' : 'bg-white text-gray-600 hover:bg-gray-100'; ?> px-3 py-1 rounded-md text-sm font-medium border">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
                <a href="<?php echo BASE_URL; ?>/patrimonio/depreciacao?page=<?php echo ($paginaAtual < $totalPaginas ? $paginaAtual + 1 : $totalPaginas); ?>" class="<?php echo ($paginaAtual >= $totalPaginas) ? 'pointer-events-none text-gray-400' : 'text-gray-600 hover:text-sky-600'; ?> px-3 py-1 rounded-md text-sm font-medium">
                    Próxima
                </a>
            </nav>
        </div>
    <?php endif; ?>
</div>

<!-- Modal para Reavaliação -->
<div id="reavaliacao-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-lg shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center pb-3 border-b">
            <p class="text-2xl font-bold">Reavaliação de Ativo</p>
            <button id="close-reavaliacao-modal" class="text-gray-500 hover:text-gray-800">&times;</button>
        </div>
        <div class="mt-5">
            <form action="<?php echo BASE_URL; ?>/patrimonio/salvarReavaliacao" method="POST">
                <input type="hidden" id="reav-bem-id" name="bem_id">

                <div class="mb-4">
                    <h4 class="font-semibold text-gray-800" id="reav-bem-nome"></h4>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="novo_valor" class="block text-sm font-medium text-gray-700 mb-1">Novo Valor de Mercado (R$) <span class="text-red-500">*</span></label>
                        <input type="text" id="novo_valor" name="novo_valor" required class="w-full border-gray-300 rounded-lg shadow-sm p-2">
                    </div>
                    <div>
                        <label for="data_reavaliacao" class="block text-sm font-medium text-gray-700 mb-1">Data da Reavaliação <span class="text-red-500">*</span></label>
                        <input type="date" id="data_reavaliacao" name="data_reavaliacao" required value="<?php echo date('Y-m-d'); ?>" class="w-full border-gray-300 rounded-lg shadow-sm p-2">
                    </div>
                    <div class="md:col-span-2">
                        <label for="reav-observacoes" class="block text-sm font-medium text-gray-700 mb-1">Observações / Justificativa</label>
                        <textarea id="reav-observacoes" name="observacoes" rows="3" class="w-full border-gray-300 rounded-lg shadow-sm p-2"></textarea>
                    </div>
                </div>

                <div class="mt-8 pt-4 border-t flex justify-end">
                    <button type="submit" class="px-6 py-2 text-sm font-semibold text-white bg-sky-600 rounded-lg shadow-md hover:bg-sky-700 transition">
                        Salvar Reavaliação
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const modal = document.getElementById('reavaliacao-modal');
        const closeBtn = document.getElementById('close-reavaliacao-modal');
        const openModalButtons = document.querySelectorAll('.open-reavaliacao-modal');

        const bemIdInput = document.getElementById('reav-bem-id');
        const bemNomeTitle = document.getElementById('reav-bem-nome');

        openModalButtons.forEach(button => {
            button.addEventListener('click', () => {
                const bemId = button.getAttribute('data-bem-id');
                const bemNome = button.getAttribute('data-bem-nome');

                bemIdInput.value = bemId;
                bemNomeTitle.textContent = `Ativo: ${bemNome}`;

                modal.classList.remove('hidden');
            });
        });

        closeBtn.addEventListener('click', () => modal.classList.add('hidden'));
    });
</script>