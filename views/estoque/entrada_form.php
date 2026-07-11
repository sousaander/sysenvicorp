<?php $pageTitle = $pageTitle ?? 'Registrar Entrada'; ?>
<div class="p-6 max-w-2xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Registrar Entrada</h1>
            <p class="text-sm text-gray-500 mt-1">Entrada de produtos no estoque com custo de aquisição</p>
        </div>
    </div>

    <form action="<?= BASE_URL ?>/estoque/registrarEntrada" method="POST" class="bg-white rounded-xl border border-gray-200 p-6 space-y-5">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Produto <span class="text-red-500">*</span></label>
            <select name="produto_id" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option value="">Selecione um produto</option>
                <?php foreach ($produtos as $p): ?>
                    <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['codigo'] . ' - ' . $p['nome']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Quantidade <span class="text-red-500">*</span></label>
                <input type="number" name="quantidade" required step="0.001" min="0.001"
                       placeholder="Ex: 10"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Valor Unitário (R$) <span class="text-red-500">*</span></label>
                <input type="number" name="valor_unitario" required step="0.01" min="0.01"
                       placeholder="Ex: 150.00"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Data da Entrada <span class="text-red-500">*</span></label>
                <input type="date" name="data_movimento" required
                       value="<?= date('Y-m-d') ?>"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Documento (NF, pedido, etc.)</label>
                <input type="text" name="documento" maxlength="100"
                       placeholder="Ex: NF-123456"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div></div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Observações</label>
            <textarea name="observacoes" rows="2" maxlength="500"
                      placeholder="Informações complementares sobre a entrada"
                      class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
        </div>

        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 text-sm text-blue-800">
            <i class='bx bx-info-circle'></i>
            O custo médio do produto será recalculado automaticamente com base no valor total da entrada.
        </div>

        <div class="flex items-center justify-between pt-4 border-t border-gray-200">
            <a href="<?= BASE_URL ?>/estoque/movimentos" class="text-sm text-gray-500 hover:text-gray-700">
                <i class='bx bx-arrow-back'></i> Voltar
            </a>
            <button type="submit" class="px-5 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                <i class='bx bx-check'></i> Registrar Entrada
            </button>
        </div>
    </form>
</div>
