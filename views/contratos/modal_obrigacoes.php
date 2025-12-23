<div class="flex justify-between items-center border-b pb-3 mb-4">
    <div>
        <h3 class="text-xl font-bold text-gray-900">Gerenciar Obrigações do Contrato</h3>
        <p class="text-sm text-gray-500">Objeto: <?php echo htmlspecialchars(substr($contrato['objeto'], 0, 70)) . '...'; ?></p>
    </div>
    <button id="close-obrigacoes-modal" class="text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <!-- Coluna da Lista de Obrigações -->
    <div class="md:col-span-2">
        <h4 class="font-semibold mb-2">Obrigações Registradas</h4>
        <div id="lista-obrigacoes" class="border rounded-lg max-h-96 overflow-y-auto">
            <?php if (empty($obrigacoes)) : ?>
                <p class="text-center text-gray-500 p-4">Nenhuma obrigação registrada.</p>
            <?php else : ?>
                <ul class="divide-y divide-gray-200">
                    <?php foreach ($obrigacoes as $obrigacao) : ?>
                        <li class="p-3 flex items-center justify-between hover:bg-gray-50" data-id="<?php echo $obrigacao['id']; ?>">
                            <div class="flex items-center">
                                <input type="checkbox" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 status-checkbox" <?php echo $obrigacao['status'] === 'Concluída' ? 'checked' : ''; ?>>
                                <div class="ml-3 text-sm">
                                    <p class="font-medium text-gray-800 <?php echo $obrigacao['status'] === 'Concluída' ? 'line-through text-gray-500' : ''; ?>">
                                        <?php echo htmlspecialchars($obrigacao['descricao']); ?>
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        Tipo: <span class="font-semibold"><?php echo htmlspecialchars($obrigacao['tipo_clausula']); ?></span>
                                        <?php if ($obrigacao['data_prevista']) : ?>
                                            | Prazo: <span class="font-semibold"><?php echo date('d/m/Y', strtotime($obrigacao['data_prevista'])); ?></span>
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                            <button class="text-red-500 hover:text-red-700 delete-obrigacao-btn" title="Excluir Obrigação">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>

    <!-- Coluna do Formulário de Nova Obrigação -->
    <div class="md:col-span-1 bg-gray-50 p-4 rounded-lg border">
        <h4 class="font-semibold mb-3">Adicionar Nova Obrigação</h4>
        <form id="form-nova-obrigacao">
            <input type="hidden" name="contrato_id" value="<?php echo $contrato['id']; ?>">
            <div class="space-y-4">
                <div>
                    <label for="descricao" class="block text-sm font-medium text-gray-700 mb-1">Descrição <span class="text-red-500">*</span></label>
                    <textarea name="descricao" rows="3" required class="w-full border-gray-300 rounded-lg shadow-sm p-2 text-sm" placeholder="Ex: Enviar relatório mensal"></textarea>
                </div>
                <div>
                    <label for="tipo_clausula" class="block text-sm font-medium text-gray-700 mb-1">Tipo <span class="text-red-500">*</span></label>
                    <select name="tipo_clausula" required class="w-full border-gray-300 rounded-lg shadow-sm p-2 text-sm">
                        <option value="">Selecione...</option>
                        <option value="Pagamento">Pagamento</option>
                        <option value="Entrega">Entrega de Produto/Serviço</option>
                        <option value="Relatório">Envio de Relatório</option>
                        <option value="Garantia">Execução de Garantia</option>
                        <option value="Reajuste">Aplicação de Reajuste</option>
                        <option value="Multa">Cláusula de Multa</option>
                        <option value="Outra">Outra</option>
                    </select>
                </div>
                <div>
                    <label for="data_prevista" class="block text-sm font-medium text-gray-700 mb-1">Data Prevista</label>
                    <input type="date" name="data_prevista" class="w-full border-gray-300 rounded-lg shadow-sm p-2 text-sm">
                </div>
                <div>
                    <label for="responsavel" class="block text-sm font-medium text-gray-700 mb-1">Responsável</label>
                    <input type="text" name="responsavel" class="w-full border-gray-300 rounded-lg shadow-sm p-2 text-sm" placeholder="Ex: Depto. Financeiro">
                </div>
                <button type="submit" class="w-full bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 font-medium">
                    Adicionar
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Este script é auto-contido e gerencia apenas este modal
    (function() {
        const modal = document.getElementById('obrigacoes-modal');
        const modalContent = document.getElementById('obrigacoes-modal-content');
        const form = document.getElementById('form-nova-obrigacao');

        // Salvar nova obrigação
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(form);
            const contratoId = formData.get('contrato_id');

            try {
                const response = await fetch('<?php echo BASE_URL; ?>/contratos/salvarObrigacao', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                if (result.success) {
                    // Recarrega o conteúdo do modal para mostrar a nova obrigação
                    const reloadResponse = await fetch(`<?php echo BASE_URL; ?>/contratos/gerenciarObrigacoes/${contratoId}`);
                    modalContent.innerHTML = await reloadResponse.text();
                    // Recarrega a página principal para atualizar a contagem
                    window.location.reload();
                } else {
                    alert(result.message);
                }
            } catch (error) {
                alert('Erro de comunicação com o servidor.');
            }
        });

        // Atualizar status da obrigação
        document.querySelectorAll('.status-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', async (e) => {
                const li = e.target.closest('li');
                const id = li.dataset.id;
                const status = e.target.checked ? 'Concluída' : 'Pendente';

                const formData = new FormData();
                formData.append('id', id);
                formData.append('status', status);

                try {
                    const response = await fetch('<?php echo BASE_URL; ?>/contratos/atualizarStatusObrigacao', {
                        method: 'POST',
                        body: formData
                    });
                    const result = await response.json();
                    if (result.success) {
                        li.querySelector('p.font-medium').classList.toggle('line-through', e.target.checked);
                        li.querySelector('p.font-medium').classList.toggle('text-gray-500', e.target.checked);
                        // Recarrega a página principal para atualizar a contagem
                        window.location.reload();
                    } else {
                        alert(result.message);
                        e.target.checked = !e.target.checked; // Reverte
                    }
                } catch (error) {
                    alert('Erro de comunicação.');
                }
            });
        });

        // Excluir obrigação
        document.querySelectorAll('.delete-obrigacao-btn').forEach(button => {
            button.addEventListener('click', async (e) => {
                if (!confirm('Tem certeza que deseja excluir esta obrigação?')) return;

                const li = e.target.closest('li');
                const id = li.dataset.id;
                const contratoId = form.querySelector('input[name="contrato_id"]').value;

                const formData = new FormData();
                formData.append('id', id);

                try {
                    const response = await fetch('<?php echo BASE_URL; ?>/contratos/excluirObrigacao', {
                        method: 'POST',
                        body: formData
                    });
                    const result = await response.json();
                    if (result.success) {
                        // Recarrega o conteúdo do modal
                        const reloadResponse = await fetch(`<?php echo BASE_URL; ?>/contratos/gerenciarObrigacoes/${contratoId}`);
                        modalContent.innerHTML = await reloadResponse.text();
                        // Recarrega a página principal para atualizar a contagem
                        window.location.reload();
                    } else {
                        alert(result.message);
                    }
                } catch (error) {
                    alert('Erro de comunicação.');
                }
            });
        });
    }());
</script>