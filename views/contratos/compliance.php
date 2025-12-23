<div class="flex justify-between items-start mb-6">
    <div>
        <h2 class="text-2xl font-bold mb-2"><?php echo htmlspecialchars($pageTitle); ?></h2>
        <p class="text-gray-600">Gerencie a conformidade legal, cláusulas de LGPD, e documentação jurídica dos contratos.</p>
    </div>
    <a href="<?php echo BASE_URL; ?>/contratos" class="bg-gray-200 text-gray-800 px-4 py-2 rounded-md hover:bg-gray-300 font-medium flex-shrink-0">
        &larr; Voltar
    </a>
</div>

<div class="bg-white p-6 rounded-lg shadow-md">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Tabela de Contratos -->
        <div class="md:col-span-2">
            <h3 class="text-lg font-semibold mb-4 border-b pb-2">Contratos Ativos para Análise de Compliance</h3>

            <div class="overflow-x-auto">
                <?php if (!empty($contratos)) : ?>
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contrato</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Parte Contratada</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Cláusula LGPD</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Risco Contratual</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($contratos as $contrato) : ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        <?php echo htmlspecialchars(substr($contrato['objeto'], 0, 40)) . (strlen($contrato['objeto']) > 40 ? '...' : ''); ?>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($contrato['parteContratada'] ?? 'N/A'); ?></td>
                                    <td class="px-4 py-4 whitespace-nowrap text-center text-sm">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $contrato['clausula_lgpd'] === 'Sim' ? 'bg-green-100 text-green-800' : ($contrato['clausula_lgpd'] === 'Não' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800'); ?>">
                                            <?php echo htmlspecialchars($contrato['clausula_lgpd']); ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-center text-sm">
                                        <?php
                                        $risco = $contrato['risco_contratual'];
                                        $cor = 'green';
                                        if ($risco === 'Médio') $cor = 'yellow';
                                        if ($risco === 'Alto') $cor = 'red';
                                        ?>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-<?php echo $cor; ?>-100 text-<?php echo $cor; ?>-800">
                                            <?php echo htmlspecialchars($risco); ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <button data-id="<?php echo $contrato['id']; ?>" class="manage-compliance-btn text-indigo-600 hover:text-indigo-900">Gerenciar</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else : ?>
                    <p class="text-center py-10 text-gray-500">Nenhum contrato ativo para análise de compliance.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Coluna de Relatórios -->
        <div class="md:col-span-1 bg-gray-50 p-4 rounded-lg border">
            <h3 class="text-lg font-semibold mb-4">Relatórios de Conformidade</h3>
            <div class="space-y-3">
                <a href="<?php echo BASE_URL; ?>/contratos/relatorioCompliance/lgpd" target="_blank" class="block w-full text-left p-3 bg-white border rounded-lg hover:bg-gray-100 transition">
                    <div class="flex items-center">
                        <i class="fas fa-file-pdf text-red-500 mr-3"></i>
                        <p class="font-semibold text-gray-700">Contratos sem Cláusula LGPD</p>
                    </div>
                    <p class="text-sm text-gray-500 mt-1 ml-7">Listar contratos que precisam de aditivo para conformidade com a LGPD.</p>
                </a>
                <a href="<?php echo BASE_URL; ?>/contratos/relatorioCompliance/risco_alto" target="_blank" class="block w-full text-left p-3 bg-white border rounded-lg hover:bg-gray-100 transition">
                    <div class="flex items-center">
                        <i class="fas fa-file-pdf text-red-500 mr-3"></i>
                        <p class="font-semibold text-gray-700">Contratos com Risco Alto</p>
                    </div>
                    <p class="text-sm text-gray-500 mt-1 ml-7">Identificar contratos que necessitam de revisão jurídica imediata.</p>
                </a>
                <a href="<?php echo BASE_URL; ?>/contratos/relatorioCompliance/geral" target="_blank" class="block w-full text-left p-3 bg-white border rounded-lg hover:bg-gray-100 transition">
                    <div class="flex items-center">
                        <i class="fas fa-file-pdf text-red-500 mr-3"></i>
                        <p class="font-semibold text-gray-700">Relatório Geral de Conformidade</p>
                    </div>
                    <p class="text-sm text-gray-500 mt-1 ml-7">Exportar um resumo completo do status de compliance de todos os contratos.</p>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Gerenciar Compliance do Contrato -->
<div id="compliance-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-10 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
        <div id="compliance-modal-content">
            <!-- Conteúdo do modal será carregado aqui via AJAX -->
            <p class="text-center p-8">Carregando...</p>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const modal = document.getElementById('compliance-modal');
        const modalContent = document.getElementById('compliance-modal-content');

        document.querySelectorAll('.manage-compliance-btn').forEach(button => {
            button.addEventListener('click', async () => {
                const contratoId = button.dataset.id;
                modal.classList.remove('hidden');
                modalContent.innerHTML = '<p class="text-center p-8">Carregando...</p>';

                try {
                    const response = await fetch(`<?php echo BASE_URL; ?>/contratos/gerenciarCompliance/${contratoId}`);
                    if (!response.ok) throw new Error('Falha ao carregar dados de compliance.');
                    modalContent.innerHTML = await response.text();
                } catch (error) {
                    modalContent.innerHTML = `<p class="text-center text-red-500 p-8">${error.message}</p>`;
                }
            });
        });

        // Fechar modal clicando fora ou no botão de fechar (via delegação)
        modal.addEventListener('click', (e) => {
            if (e.target === modal || e.target.closest('.close-modal-btn')) {
                modal.classList.add('hidden');
            }
        });

        // Salvar formulário de compliance (via delegação)
        modal.addEventListener('submit', async (e) => {
            // Verifica se o evento de submit veio do formulário correto
            if (e.target && e.target.id === 'form-compliance') {
                e.preventDefault();
                const formData = new FormData(e.target);
                try {
                    const response = await fetch('<?php echo BASE_URL; ?>/contratos/salvarCompliance', {
                        method: 'POST',
                        body: formData
                    });
                    const result = await response.json();
                    alert(result.message); // Exibe a mensagem de sucesso ou erro
                    if (result.success) {
                        window.location.reload(); // Recarrega a página para ver as atualizações
                    }
                } catch (error) {
                    alert('Erro de comunicação ao salvar os dados.');
                }
            }
        });
    });
</script>