<?php
$isEdit = isset($produto) && $produto !== null;
$actionUrl = BASE_URL . '/estoque/salvarProduto';
?>
<div class="p-6 max-w-4xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800"><?= $isEdit ? 'Editar Produto' : 'Novo Produto' ?></h1>
            <p class="text-sm text-gray-500 mt-1">Informações fiscais, contábeis e comerciais</p>
        </div>
    </div>

    <form action="<?= $actionUrl ?>" method="POST" class="bg-white rounded-xl border border-gray-200 p-6 space-y-6">
        <?php if ($isEdit): ?>
            <input type="hidden" name="id" value="<?= htmlspecialchars($produto['id']) ?>">
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Código <span class="text-red-500">*</span></label>
                <input type="text" name="codigo" required maxlength="50"
                       placeholder="Ex: PROD-001"
                       value="<?= $isEdit ? htmlspecialchars($produto['codigo']) : '' ?>"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nome <span class="text-red-500">*</span></label>
                <input type="text" name="nome" required maxlength="255"
                       placeholder="Ex: Lote de Análise de Solo"
                       value="<?= $isEdit ? htmlspecialchars($produto['nome']) : '' ?>"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Unidade</label>
                <input type="text" name="unidade" maxlength="10" placeholder="UN, KG, LT, M³"
                       value="<?= $isEdit ? htmlspecialchars($produto['unidade'] ?? 'UN') : 'UN' ?>"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
            <textarea name="descricao" rows="2" maxlength="1000"
                      placeholder="Descrição detalhada do produto"
                      class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"><?= $isEdit ? htmlspecialchars($produto['descricao'] ?? '') : '' ?></textarea>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Categoria</label>
                <input type="text" name="categoria" maxlength="100"
                       placeholder="Ex: Insumos Laboratoriais"
                       value="<?= $isEdit ? htmlspecialchars($produto['categoria'] ?? '') : '' ?>"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">NCM (Nomenclatura Comum do Mercosul)</label>
                <input type="text" name="ncm" maxlength="10"
                       placeholder="Ex: 3822.00.90"
                       value="<?= $isEdit ? htmlspecialchars($produto['ncm'] ?? '') : '' ?>"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">CEST (Código Especificador da Substituição Tributária)</label>
                <input type="text" name="cest" maxlength="10"
                       placeholder="Ex: 12.345.67"
                       value="<?= $isEdit ? htmlspecialchars($produto['cest'] ?? '') : '' ?>"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
            </div>
            <div></div>
        </div>

        <div class="border-t border-gray-200 pt-4">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Alíquotas Fiscais</h3>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">ICMS (%)</label>
                    <input type="number" name="aliquota_icms" step="0.01" min="0" max="100"
                           value="<?= $isEdit ? htmlspecialchars($produto['aliquota_icms'] ?? 0) : '0' ?>"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">IPI (%)</label>
                    <input type="number" name="aliquota_ipi" step="0.01" min="0" max="100"
                           value="<?= $isEdit ? htmlspecialchars($produto['aliquota_ipi'] ?? 0) : '0' ?>"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">PIS (%)</label>
                    <input type="number" name="aliquota_pis" step="0.01" min="0" max="100"
                           value="<?= $isEdit ? htmlspecialchars($produto['aliquota_pis'] ?? 0) : '0' ?>"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">COFINS (%)</label>
                    <input type="number" name="aliquota_cofins" step="0.01" min="0" max="100"
                           value="<?= $isEdit ? htmlspecialchars($produto['aliquota_cofins'] ?? 0) : '0' ?>"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                </div>
            </div>
        </div>

        <div class="border-t border-gray-200 pt-4">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Custos e Preços</h3>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Custo de Aquisição (R$)</label>
                    <input type="number" name="custo_aquisicao" step="0.01" min="0"
                           value="<?= $isEdit ? htmlspecialchars($produto['custo_aquisicao'] ?? 0) : '0' ?>"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Despesas Acessórias (R$)</label>
                    <input type="number" name="despesas_acessorias" step="0.01" min="0"
                           value="<?= $isEdit ? htmlspecialchars($produto['despesas_acessorias'] ?? 0) : '0' ?>"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Margem de Lucro (%)</label>
                    <input type="number" name="margem_lucro" step="0.01" min="0" max="1000"
                           value="<?= $isEdit ? htmlspecialchars($produto['margem_lucro'] ?? 0) : '0' ?>"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Preço de Venda (R$)</label>
                    <input type="number" name="preco_venda" step="0.01" min="0"
                           value="<?= $isEdit ? htmlspecialchars($produto['preco_venda'] ?? 0) : '0' ?>"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <input type="checkbox" name="ativo" id="ativo" value="1"
                   <?= !$isEdit || $produto['ativo'] ? 'checked' : '' ?>
                   class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
            <label for="ativo" class="text-sm text-gray-700">Produto ativo</label>
        </div>

        <div class="flex items-center justify-between pt-4 border-t border-gray-200">
            <a href="<?= BASE_URL ?>/estoque/produtos" class="text-sm text-gray-500 hover:text-gray-700">
                <i class='bx bx-arrow-back'></i> Voltar
            </a>
            <button type="submit" class="px-5 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700 transition-colors">
                <i class='bx bx-check'></i> <?= $isEdit ? 'Salvar Alterações' : 'Cadastrar Produto' ?>
            </button>
        </div>
    </form>
</div>
