<div class="flex justify-between items-center border-b pb-3 mb-4">
    <div>
        <h3 class="text-xl font-bold text-gray-900">Gerenciar Financeiro do Contrato</h3>
        <p class="text-sm text-gray-500">Objeto: <?php echo htmlspecialchars(substr($contrato['objeto'], 0, 70)) . '...'; ?></p>
    </div>
    <button id="close-financeiro-modal" class="text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <!-- Coluna da Lista de Parcelas -->
    <div class="md:col-span-2">
        <h4 class="font-semibold mb-2">Parcelas Previstas</h4>
        <div id="lista-parcelas" class="border rounded-lg max-h-96 overflow-y-auto">
            <?php if (empty($parcelas)) : ?>
                <p class="text-center text-gray-500 p-4">Nenhuma parcela prevista.</p>
            <?php else : ?>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Descrição</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Vencimento</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Valor</th>
                            <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Ação</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($parcelas as $parcela) : ?>
                            <tr data-id="<?php echo $parcela['id']; ?>">
                                <td class="px-4 py-2 whitespace-nowrap text-sm font-medium text-gray-800"><?php echo htmlspecialchars($parcela['descricao']); ?></td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500"><?php echo date('d/m/Y', strtotime($parcela['data_vencimento'])); ?></td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm text-right text-gray-800 font-semibold">R$ <?php echo number_format($parcela['valor'], 2, ',', '.'); ?></td>
                                <td class="px-4 py-2 whitespace-nowrap text-center text-sm">
                                    <?php
                                    $status = $parcela['status'];
                                    $cor = 'gray';
                                    if ($status === 'Pendente') $cor = 'yellow';
                                    if ($status === 'Lançada') $cor = 'blue';
                                    if ($status === 'Paga') $cor = 'green';
                                    ?>
                                    <span class="px-2 py-1 text-xs font-semibold text-<?php echo $cor; ?>-800 bg-<?php echo $cor; ?>-100 rounded-full"><?php echo $status; ?></span>
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap text-right text-sm">
                                    <?php if ($parcela['status'] === 'Pendente') : ?>
                                        <button class="lancar-parcela-btn text-green-600 hover:text-green-800 font-medium" title="Lançar no Contas a Pagar/Receber">Lançar</button>
                                    <?php elseif ($parcela['transacao_id']) : ?>
                                        <a href="<?php echo BASE_URL . '/financeiro/detalhe/' . $parcela['transacao_id']; ?>" target="_blank" class="text-indigo-600 hover:text-indigo-800 font-medium" title="Ver lançamento no financeiro">Ver Lanç.</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- Coluna do Formulário de Nova Parcela -->
    <div class="md:col-span-1 bg-gray-50 p-4 rounded-lg border">
        <h4 class="font-semibold mb-3">Adicionar Nova Parcela</h4>
        <form id="form-nova-parcela">
            <input type="hidden" name="contrato_id" value="<?php echo $contrato['id']; ?>">
            <div class="space-y-4">
                <div>
                    <label for="descricao_parcela" class="block text-sm font-medium text-gray-700 mb-1">Descrição <span class="text-red-500">*</span></label>
                    <input type="text" name="descricao" id="descricao_parcela" required class="w-full border-gray-300 rounded-lg shadow-sm p-2 text-sm" placeholder="Ex: Parcela 1/12">
                </div>
                <div>
                    <label for="data_vencimento_parcela" class="block text-sm font-medium text-gray-700 mb-1">Data Vencimento <span class="text-red-500">*</span></label>
                    <input type="date" name="data_vencimento" id="data_vencimento_parcela" required class="w-full border-gray-300 rounded-lg shadow-sm p-2 text-sm">
                </div>
                <div>
                    <label for="valor_parcela" class="block text-sm font-medium text-gray-700 mb-1">Valor (R$) <span class="text-red-500">*</span></label>
                    <input type="text" name="valor" id="valor_parcela" required class="w-full border-gray-300 rounded-lg shadow-sm p-2 text-sm" placeholder="1.234,56">
                </div>
                <button type="submit" class="w-full bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 font-medium">
                    Adicionar Parcela
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Script auto-contido para gerenciar o modal financeiro
    (function() {
        const modalContent = document.getElementById('financeiro-modal-content');
        const form = document.getElementById('form-nova-parcela');

        // Salvar nova parcela
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(form);
            const contratoId = formData.get('contrato_id');

            try {
                const response = await fetch('<?php echo BASE_URL; ?>/contratos/salvarParcela', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                if (result.success) {
                    // Recarrega o conteúdo do modal para mostrar a nova parcela
                    const reloadResponse = await fetch(`<?php echo BASE_URL; ?>/contratos/gerenciarFinanceiro/${contratoId}`);
                    modalContent.innerHTML = await reloadResponse.text();
                } else {
                    alert('Erro: ' + (result.message || 'Não foi possível salvar a parcela.'));
                }
            } catch (error) {
                alert('Erro de comunicação com o servidor.');
            }
        });

        // Lançar parcela no financeiro
        document.querySelectorAll('.lancar-parcela-btn').forEach(button => {
            button.addEventListener('click', async (e) => {
                if (!confirm('Tem certeza que deseja lançar esta parcela no financeiro? Esta ação não pode ser desfeita.')) return;

                const tr = e.target.closest('tr');
                const parcelaId = tr.dataset.id;
                const contratoId = form.querySelector('input[name="contrato_id"]').value;
                e.target.disabled = true;
                e.target.textContent = 'Lançando...';

                try {
                    const response = await fetch(`<?php echo BASE_URL; ?>/contratos/lancarParcela/${parcelaId}`, {
                        method: 'POST'
                    });
                    const result = await response.json();
                    if (result.success) {
                        // Recarrega o conteúdo do modal para refletir a mudança de status
                        const reloadResponse = await fetch(`<?php echo BASE_URL; ?>/contratos/gerenciarFinanceiro/${contratoId}`);
                        modalContent.innerHTML = await reloadResponse.text();
                    } else {
                        alert('Erro: ' + (result.message || 'Não foi possível lançar a parcela.'));
                        e.target.disabled = false;
                        e.target.textContent = 'Lançar';
                    }
                } catch (error) {
                    alert('Erro de comunicação com o servidor ao tentar lançar a parcela.');
                    e.target.disabled = false;
                    e.target.textContent = 'Lançar';
                }
            });
        });

        // Máscara de moeda simples
        const valorInput = document.getElementById('valor_parcela');
        if (valorInput) {
            valorInput.addEventListener('input', (e) => {
                let value = e.target.value.replace(/\D/g, '');
                value = (value / 100).toLocaleString('pt-BR', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
                if (value === '0,00') value = '';
                e.target.value = value;
            });
        }
    }());
</script>