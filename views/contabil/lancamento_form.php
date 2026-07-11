<?php
$isEdit = isset($lancamento) && $lancamento !== null;
$actionUrl = BASE_URL . '/contabil/salvarLancamento';
?>
<div class="p-6 max-w-3xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800"><?= $isEdit ? 'Editar Lançamento' : 'Novo Lançamento Contábil' ?></h1>
            <p class="text-sm text-gray-500 mt-1">Registro em partida dobrada com débito e crédito</p>
        </div>
    </div>

    <form action="<?= $actionUrl ?>" method="POST" class="bg-white rounded-xl border border-gray-200 p-6 space-y-5">
        <input type="hidden" name="id" value="<?= $isEdit ? htmlspecialchars($lancamento['id']) : '' ?>">
        <input type="hidden" name="origem" value="manual">

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Descrição <span class="text-red-500">*</span></label>
            <input type="text" name="descricao" required maxlength="500"
                   placeholder="Ex: Pagamento de aluguel referente a janeiro/2026"
                   value="<?= $isEdit ? htmlspecialchars($lancamento['descricao']) : '' ?>"
                   class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Valor <span class="text-red-500">*</span></label>
                <input type="number" name="valor" required step="0.01" min="0.01"
                       value="<?= $isEdit ? htmlspecialchars($lancamento['valor']) : '' ?>"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Data do Lançamento <span class="text-red-500">*</span></label>
                <input type="date" name="data_lancamento" required
                       value="<?= $isEdit ? htmlspecialchars($lancamento['data_lancamento']) : date('Y-m-d') ?>"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Categoria</label>
                <input type="text" name="categoria" maxlength="100"
                       placeholder="Ex: Despesas Operacionais"
                       value="<?= $isEdit ? htmlspecialchars($lancamento['categoria'] ?? '') : '' ?>"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Conta de Débito</label>
                <select name="debito_conta_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                    <option value="">Selecione</option>
                    <?php foreach ($contas as $c): ?>
                        <?php if ($c['tipo'] === 'analitico'): ?>
                            <option value="<?= $c['id'] ?>" <?= $isEdit && $lancamento['debito_conta_id'] == $c['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['codigo'] . ' - ' . $c['nome']) ?>
                            </option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Conta de Crédito</label>
                <select name="credito_conta_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                    <option value="">Selecione</option>
                    <?php foreach ($contas as $c): ?>
                        <?php if ($c['tipo'] === 'analitico'): ?>
                            <option value="<?= $c['id'] ?>" <?= $isEdit && $lancamento['credito_conta_id'] == $c['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['codigo'] . ' - ' . $c['nome']) ?>
                            </option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Conta (texto livre)</label>
                <input type="text" name="conta" maxlength="100"
                       placeholder="Ex: Banco Itaú"
                       value="<?= $isEdit ? htmlspecialchars($lancamento['conta'] ?? '') : '' ?>"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Centro de Custo</label>
                <input type="text" name="centro_custo" maxlength="100"
                       placeholder="Ex: Administrativo"
                       value="<?= $isEdit ? htmlspecialchars($lancamento['centro_custo'] ?? '') : '' ?>"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Observações</label>
            <textarea name="observacoes" rows="3" maxlength="500"
                      placeholder="Informações complementares"
                      class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"><?= $isEdit ? htmlspecialchars($lancamento['observacoes'] ?? '') : '' ?></textarea>
        </div>

        <div class="flex items-center justify-between pt-4 border-t border-gray-200">
            <a href="<?= BASE_URL ?>/contabil/lancamentos" class="text-sm text-gray-500 hover:text-gray-700">
                <i class='bx bx-arrow-back'></i> Voltar
            </a>
            <button type="submit" class="px-5 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700 transition-colors">
                <i class='bx bx-check'></i> <?= $isEdit ? 'Salvar Alterações' : 'Registrar Lançamento' ?>
            </button>
        </div>
    </form>
</div>
