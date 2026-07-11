<?php $pageTitle = $pageTitle ?? 'Novo Inventário'; ?>
<div class="p-6 max-w-2xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Novo Inventário Físico</h1>
            <p class="text-sm text-gray-500 mt-1">Cria um novo inventário para contagem física dos produtos</p>
        </div>
    </div>

    <form action="<?= BASE_URL ?>/estoque/novoInventario" method="POST" class="bg-white rounded-xl border border-gray-200 p-6 space-y-5">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Data do Inventário <span class="text-red-500">*</span></label>
                <input type="date" name="data_inventario" required
                       value="<?= date('Y-m-d') ?>"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Inventário</label>
                <select name="tipo" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                    <option value="total">Total (todos os produtos)</option>
                    <option value="parcial">Parcial (selecionar depois)</option>
                    <option value="rotativo">Rotativo (por categoria)</option>
                </select>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Observações</label>
            <textarea name="observacoes" rows="3" maxlength="500"
                      placeholder="Ex: Inventário anual obrigatório"
                      class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500"></textarea>
        </div>

        <div class="bg-purple-50 border border-purple-200 rounded-lg p-3 text-sm text-purple-800">
            <i class='bx bx-info-circle'></i>
            Ao criar o inventário, todos os produtos com saldo positivo serão incluídos automaticamente para contagem.
            Após a contagem, o sistema ajustará os saldos com base nas diferenças encontradas.
        </div>

        <div class="flex items-center justify-between pt-4 border-t border-gray-200">
            <a href="<?= BASE_URL ?>/estoque/inventarios" class="text-sm text-gray-500 hover:text-gray-700">
                <i class='bx bx-arrow-back'></i> Voltar
            </a>
            <button type="submit" class="px-5 py-2 bg-purple-600 text-white text-sm font-medium rounded-lg hover:bg-purple-700 transition-colors">
                <i class='bx bx-check'></i> Criar Inventário
            </button>
        </div>
    </form>
</div>
