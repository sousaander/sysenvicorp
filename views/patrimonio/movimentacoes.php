<div class="flex justify-between items-start mb-6">
    <div>
        <h2 class="text-2xl font-bold"><?php echo htmlspecialchars($pageTitle); ?></h2>
        <p class="text-gray-600">Registre transferências, empréstimos e baixas de ativos.</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="<?php echo BASE_URL; ?>/patrimonio" class="px-4 py-2 text-sm font-semibold text-gray-700 bg-gray-200 rounded-lg shadow-md hover:bg-gray-300 transition">&larr; Voltar</a>
        <button id="open-modal-btn" class="px-4 py-2 text-sm font-semibold text-white bg-sky-600 rounded-lg shadow-md hover:bg-sky-700 transition">
            + Nova Movimentação
        </button>
    </div>
</div>

<!-- Tabela de Histórico de Movimentações -->
<div class="bg-white p-6 rounded-lg shadow-md">
    <h3 class="text-lg font-semibold mb-4 border-b pb-2">Histórico de Movimentações</h3>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bem / Ativo</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Destino / Responsável</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (!empty($movimentacoes)) : ?>
                    <?php foreach ($movimentacoes as $mov) : ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600"><?php echo date('d/m/Y', strtotime($mov['data_movimentacao'])); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    <?php
                                    if ($mov['tipo_movimentacao'] === 'Transferência') echo 'bg-blue-100 text-blue-800';
                                    elseif ($mov['tipo_movimentacao'] === 'Empréstimo') echo 'bg-yellow-100 text-yellow-800';
                                    elseif ($mov['tipo_movimentacao'] === 'Baixa') echo 'bg-red-100 text-red-800';
                                    else echo 'bg-gray-100 text-gray-800';
                                    ?>">
                                    <?php echo htmlspecialchars($mov['tipo_movimentacao']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                <?php echo htmlspecialchars($mov['nome_bem']); ?>
                                <span class="text-xs text-gray-500">(#<?php echo htmlspecialchars($mov['numero_patrimonio']); ?>)</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                <?php echo htmlspecialchars($mov['destino'] ?: $mov['responsavel_retirada']); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-center text-gray-500">Nenhuma movimentação registrada.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal para Nova Movimentação -->
<div id="movimentacao-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center pb-3 border-b">
            <p class="text-2xl font-bold">Registrar Nova Movimentação</p>
            <button id="close-modal-btn" class="text-gray-500 hover:text-gray-800">&times;</button>
        </div>
        <div class="mt-5">
            <form action="<?php echo BASE_URL; ?>/patrimonio/salvarMovimentacao" method="POST">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Bem / Ativo -->
                    <div class="md:col-span-2">
                        <label for="bem_id" class="block text-sm font-medium text-gray-700 mb-1">Bem / Ativo <span class="text-red-500">*</span></label>
                        <select id="bem_id" name="bem_id" required class="w-full border-gray-300 rounded-lg shadow-sm p-2">
                            <option value="">Selecione um bem...</option>
                            <?php foreach ($bens as $bem) : ?>
                                <option value="<?php echo $bem['id']; ?>"><?php echo htmlspecialchars($bem['nome'] . ' (#' . $bem['numero_patrimonio'] . ')'); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Tipo de Movimentação -->
                    <div>
                        <label for="tipo_movimentacao" class="block text-sm font-medium text-gray-700 mb-1">Tipo de Movimentação <span class="text-red-500">*</span></label>
                        <select id="tipo_movimentacao" name="tipo_movimentacao" required class="w-full border-gray-300 rounded-lg shadow-sm p-2">
                            <option value="">Selecione...</option>
                            <option value="Transferência">Transferência entre setores</option>
                            <option value="Empréstimo">Empréstimo ou Cessão</option>
                            <option value="Baixa">Baixa de Bem</option>
                        </select>
                    </div>

                    <!-- Data da Movimentação -->
                    <div>
                        <label for="data_movimentacao" class="block text-sm font-medium text-gray-700 mb-1">Data da Movimentação <span class="text-red-500">*</span></label>
                        <input type="date" id="data_movimentacao" name="data_movimentacao" required value="<?php echo date('Y-m-d'); ?>" class="w-full border-gray-300 rounded-lg shadow-sm p-2">
                    </div>

                    <!-- Campos Dinâmicos -->
                    <div id="campos-transferencia" class="hidden md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="destino" class="block text-sm font-medium text-gray-700 mb-1">Setor / Local de Destino <span class="text-red-500">*</span></label>
                            <input type="text" id="destino" name="destino" class="w-full border-gray-300 rounded-lg shadow-sm p-2">
                        </div>
                        <div>
                            <label for="responsavel_retirada" class="block text-sm font-medium text-gray-700 mb-1">Novo Responsável</label>
                            <input type="text" id="responsavel_retirada" name="responsavel_retirada" class="w-full border-gray-300 rounded-lg shadow-sm p-2">
                        </div>
                    </div>

                    <div id="campos-emprestimo" class="hidden md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="responsavel_emprestimo" class="block text-sm font-medium text-gray-700 mb-1">Responsável pela Retirada <span class="text-red-500">*</span></label>
                            <input type="text" id="responsavel_emprestimo" name="responsavel_retirada" class="w-full border-gray-300 rounded-lg shadow-sm p-2">
                        </div>
                        <div>
                            <label for="data_devolucao" class="block text-sm font-medium text-gray-700 mb-1">Data de Devolução Prevista</label>
                            <input type="date" id="data_devolucao" name="data_devolucao" class="w-full border-gray-300 rounded-lg shadow-sm p-2">
                        </div>
                    </div>

                    <div id="campos-baixa" class="hidden md:col-span-2">
                        <label for="motivo_baixa" class="block text-sm font-medium text-gray-700 mb-1">Motivo da Baixa <span class="text-red-500">*</span></label>
                        <select id="motivo_baixa" name="motivo_baixa" class="w-full border-gray-300 rounded-lg shadow-sm p-2">
                            <option value="">Selecione...</option>
                            <option value="Venda">Venda</option>
                            <option value="Descarte por Obsolescência">Descarte por Obsolescência</option>
                            <option value="Descarte por Dano">Descarte por Dano Irreparável</option>
                            <option value="Doação">Doação</option>
                            <option value="Perda ou Roubo">Perda ou Roubo</option>
                        </select>
                    </div>

                    <!-- Observações -->
                    <div class="md:col-span-2">
                        <label for="observacoes" class="block text-sm font-medium text-gray-700 mb-1">Observações</label>
                        <textarea id="observacoes" name="observacoes" rows="3" class="w-full border-gray-300 rounded-lg shadow-sm p-2"></textarea>
                    </div>
                </div>

                <div class="mt-8 pt-4 border-t flex justify-end">
                    <button type="submit" class="px-6 py-2 text-sm font-semibold text-white bg-sky-600 rounded-lg shadow-md hover:bg-sky-700 transition">
                        Registrar Movimentação
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const modal = document.getElementById('movimentacao-modal');
        const openBtn = document.getElementById('open-modal-btn');
        const closeBtn = document.getElementById('close-modal-btn');

        openBtn.addEventListener('click', () => modal.classList.remove('hidden'));
        closeBtn.addEventListener('click', () => modal.classList.add('hidden'));

        const tipoMovimentacaoSelect = document.getElementById('tipo_movimentacao');
        const camposTransferencia = document.getElementById('campos-transferencia');
        const camposEmprestimo = document.getElementById('campos-emprestimo');
        const camposBaixa = document.getElementById('campos-baixa');

        tipoMovimentacaoSelect.addEventListener('change', (e) => {
            const tipo = e.target.value;

            // Esconde todos e remove 'required'
            [camposTransferencia, camposEmprestimo, camposBaixa].forEach(el => el.classList.add('hidden'));
            camposTransferencia.querySelectorAll('input, select').forEach(input => input.required = false);
            camposEmprestimo.querySelectorAll('input, select').forEach(input => input.required = false);
            camposBaixa.querySelectorAll('input, select').forEach(input => input.required = false);

            // Mostra o correto e adiciona 'required'
            if (tipo === 'Transferência') {
                camposTransferencia.classList.remove('hidden');
                document.getElementById('destino').required = true;
            } else if (tipo === 'Empréstimo') {
                camposEmprestimo.classList.remove('hidden');
                document.getElementById('responsavel_emprestimo').required = true;
            } else if (tipo === 'Baixa') {
                camposBaixa.classList.remove('hidden');
                document.getElementById('motivo_baixa').required = true;
            }
        });
    });
</script>