<h2 class="text-2xl font-bold mb-4"><?php echo htmlspecialchars($pageTitle); ?></h2>
<p class="mb-6 text-gray-600">Registre e controle o cumprimento de cláusulas, multas, reajustes e outras obrigações contratuais.</p>

<div class="bg-white p-6 rounded-lg shadow-md">
    <h3 class="text-lg font-semibold mb-4 border-b pb-2">Contratos Ativos</h3>

    <div class="overflow-x-auto">
        <?php if (!empty($contratos)) : ?>
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contrato (Objeto)</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Parte Contratada</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vencimento</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Obrigações</th>
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
                                <?php echo $contrato['vencimento'] ? date('d/m/Y', strtotime($contrato['vencimento'])) : 'Indeterminado'; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo ($contrato['total_obrigacoes'] > 0 && $contrato['obrigacoes_concluidas'] == $contrato['total_obrigacoes']) ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'; ?>">
                                    <?php echo (int)$contrato['obrigacoes_concluidas']; ?> / <?php echo (int)$contrato['total_obrigacoes']; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button data-id="<?php echo $contrato['id']; ?>" class="manage-obrigacoes-btn bg-blue-500 text-white px-3 py-1 rounded-md hover:bg-blue-600">
                                    Gerenciar Obrigações
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else : ?>
            <div class="text-center py-10">
                <p class="text-gray-500">Nenhum contrato "Em Vigência" para gerenciar obrigações.</p>
                <a href="<?php echo BASE_URL; ?>/contratos" class="mt-4 inline-block text-indigo-600 hover:text-indigo-800 font-medium">
                    Ir para Cadastro de Contratos &rarr;
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal para Gerenciar Obrigações -->
<div id="obrigacoes-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-10 mx-auto p-5 border w-full max-w-4xl shadow-lg rounded-md bg-white">
        <div id="obrigacoes-modal-content">
            <!-- Conteúdo do modal será carregado aqui via AJAX -->
            <p class="text-center">Carregando...</p>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const modal = document.getElementById('obrigacoes-modal');
        const modalContent = document.getElementById('obrigacoes-modal-content');

        document.querySelectorAll('.manage-obrigacoes-btn').forEach(button => {
            button.addEventListener('click', async () => {
                const contratoId = button.dataset.id;
                modal.classList.remove('hidden');
                modalContent.innerHTML = '<p class="text-center p-8">Carregando...</p>';

                try {
                    const response = await fetch(`<?php echo BASE_URL; ?>/contratos/gerenciarObrigacoes/${contratoId}`);
                    if (!response.ok) throw new Error('Falha ao carregar dados.');
                    modalContent.innerHTML = await response.text();
                } catch (error) {
                    modalContent.innerHTML = `<p class="text-center text-red-500 p-8">${error.message}</p>`;
                }
            });
        });

        // Event listener no modal para delegação
        modal.addEventListener('click', (e) => {
            // Fecha o modal se clicar no background
            if (e.target === modal) {
                modal.classList.add('hidden');
            }
            // Fecha o modal se clicar no botão de fechar (com ID 'close-obrigacoes-modal')
            // O método .closest() procura o ancestral mais próximo que corresponde ao seletor.
            if (e.target.closest('#close-obrigacoes-modal')) {
                modal.classList.add('hidden');
            }
        });
    });
</script>