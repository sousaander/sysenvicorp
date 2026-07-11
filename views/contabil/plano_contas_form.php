<?php
$isEdit = isset($conta) && $conta !== null;
$actionUrl = BASE_URL . '/contabil/salvarPlanoConta';
?>
<div class="p-6 max-w-3xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800"><?= $isEdit ? 'Editar Conta Contábil' : 'Nova Conta Contábil' ?></h1>
            <p class="text-sm text-gray-500 mt-1">Preencha os dados da conta de acordo com o plano de contas</p>
        </div>
    </div>

    <form action="<?= $actionUrl ?>" method="POST" class="bg-white rounded-xl border border-gray-200 p-6 space-y-5">
        <input type="hidden" name="id" value="<?= $isEdit ? htmlspecialchars($conta['id']) : '' ?>">

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Código <span class="text-red-500">*</span></label>
                <input type="text" name="codigo" required maxlength="20"
                       placeholder="Ex: 1.1.1.01.001"
                       value="<?= $isEdit ? htmlspecialchars($conta['codigo']) : '' ?>"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                <p class="text-xs text-gray-400 mt-1">Formato numérico hierárquico (Ex: 1.1.1.01.001)</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nome <span class="text-red-500">*</span></label>
                <input type="text" name="nome" required maxlength="255"
                       placeholder="Ex: Caixa Geral"
                       value="<?= $isEdit ? htmlspecialchars($conta['nome']) : '' ?>"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
                <select name="tipo" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                    <option value="analitico" <?= $isEdit && $conta['tipo'] === 'analitico' ? 'selected' : '' ?>>Analítica (lançável)</option>
                    <option value="sintetico" <?= $isEdit && $conta['tipo'] === 'sintetico' ? 'selected' : '' ?>>Sintética (agrupadora)</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Natureza</label>
                <select name="natureza" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                    <option value="devedora" <?= $isEdit && $conta['natureza'] === 'devedora' ? 'selected' : '' ?>>Devedora</option>
                    <option value="credora" <?= $isEdit && $conta['natureza'] === 'credora' ? 'selected' : '' ?>>Credora</option>
                </select>
                <p class="text-xs text-gray-400 mt-1">Ativo = Devedora | Passivo/PL = Credora | Despesa = Devedora | Receita = Credora</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Conta Pai (Sintética)</label>
                <select name="conta_pai_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                    <option value="">Nenhuma (conta raiz)</option>
                    <?php foreach ($contasPai as $p): ?>
                        <?php if (!$isEdit || $p['id'] !== $conta['id']): ?>
                            <option value="<?= $p['id'] ?>" <?= $isEdit && $conta['conta_pai_id'] == $p['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($p['codigo'] . ' - ' . $p['nome']) ?>
                            </option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="flex items-center gap-3 mt-6">
                    <input type="checkbox" name="ativo" value="1" <?= !$isEdit || $conta['ativo'] ? 'checked' : '' ?>
                           class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                    <span class="text-sm text-gray-700">Conta ativa</span>
                </label>
            </div>
        </div>

        <div class="flex items-center justify-between pt-4 border-t border-gray-200">
            <a href="<?= BASE_URL ?>/contabil/planocontas" class="text-sm text-gray-500 hover:text-gray-700">
                <i class='bx bx-arrow-back'></i> Voltar
            </a>
            <button type="submit" class="px-5 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700 transition-colors">
                <i class='bx bx-check'></i> <?= $isEdit ? 'Salvar Alterações' : 'Cadastrar Conta' ?>
            </button>
        </div>
    </form>
</div>
