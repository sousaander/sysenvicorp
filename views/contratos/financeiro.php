<h2 class="text-2xl font-bold mb-4"><?php echo htmlspecialchars($pageTitle); ?></h2>
<p class="mb-6 text-gray-600">Lance valores previstos, integre com o contas a pagar/receber e controle reajustes.</p>

<div class="bg-white p-6 rounded-lg shadow-md">
    <h3 class="text-lg font-semibold mb-4 border-b pb-2">Contratos Ativos para Lançamento Financeiro</h3>

    <div class="overflow-x-auto">
        <?php if (!empty($contratos)) : ?>
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contrato (Objeto)</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Parte Contratada</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Valor Previsto</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($contratos as $contrato) : ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                <?php echo htmlspecialchars(substr($contrato['objeto'], 0, 50)) . (strlen($contrato['objeto']) > 50 ? '...' : ''); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo htmlspecialchars($contrato['parteContratada'] ?? 'N/A'); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $contrato['tipo'] === 'Venda' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                    <?php echo htmlspecialchars($contrato['tipo']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                R$ <?php echo number_format($contrato['valor_previsto'] ?? 0.00, 2, ',', '.'); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button data-id="<?php echo $contrato['id']; ?>" class="manage-financeiro-btn bg-green-500 text-white px-3 py-1 rounded-md hover:bg-green-600 disabled:bg-gray-300 disabled:cursor-not-allowed">
                                    Gerenciar Lançamentos
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else : ?>
            <div class="text-center py-10">
                <p class="text-gray-500">Nenhum contrato "Em Vigência" para realizar lançamentos financeiros.</p>
                <a href="<?php echo BASE_URL; ?>/contratos" class="mt-4 inline-block text-indigo-600 hover:text-indigo-800 font-medium">
                    Ir para Cadastro de Contratos &rarr;
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal para Gerenciar Financeiro do Contrato -->
<div id="financeiro-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-10 mx-auto p-5 border w-full max-w-4xl shadow-lg rounded-md bg-white">
        <div id="financeiro-modal-content">
            <!-- Conteúdo do modal será carregado aqui via AJAX -->
            <p class="text-center p-8">Carregando...</p>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const modal = document.getElementById('financeiro-modal');
        const modalContent = document.getElementById('financeiro-modal-content');

        document.querySelectorAll('.manage-financeiro-btn').forEach(button => {
            button.addEventListener('click', async () => {
                const contratoId = button.dataset.id;
                modal.classList.remove('hidden');
                modalContent.innerHTML = '<p class="text-center p-8">Carregando...</p>';

                try {
                    const response = await fetch(`<?php echo BASE_URL; ?>/contratos/gerenciarFinanceiro/${contratoId}`);
                    if (!response.ok) throw new Error('Falha ao carregar dados financeiros.');
                    modalContent.innerHTML = await response.text();
                } catch (error) {
                    modalContent.innerHTML = `<p class="text-center text-red-500 p-8">${error.message}</p>`;
                }
            });
        });

        // Fechar modal clicando fora ou no botão de fechar (via delegação)
        modal.addEventListener('click', (e) => {
            if (e.target === modal || e.target.closest('#close-financeiro-modal')) {
                modal.classList.add('hidden');
            }
        });
    });
</script>