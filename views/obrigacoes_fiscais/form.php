<?php $isEdit = isset($obrigacao) && $obrigacao !== null; ?>
<div class="p-6 max-w-2xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800"><?= $isEdit ? 'Editar Obrigação' : 'Nova Obrigação Fiscal' ?></h1>
            <p class="text-sm text-gray-500 mt-1">Cadastro de obrigações acessórias para controle de prazos</p>
        </div>
    </div>

    <form action="<?= BASE_URL ?>/obrigacoesFiscais/salvar" method="POST" class="bg-white rounded-xl border border-gray-200 p-6 space-y-5">
        <?php if ($isEdit): ?>
            <input type="hidden" name="id" value="<?= $obrigacao['id'] ?>">
        <?php endif; ?>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nome <span class="text-red-500">*</span></label>
            <input type="text" name="nome" required maxlength="255"
                   value="<?= $isEdit ? htmlspecialchars($obrigacao['nome']) : '' ?>"
                   placeholder="Ex: DAS - Simples Nacional"
                   class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
            <textarea name="descricao" rows="2" maxlength="500"
                      class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"><?= $isEdit ? htmlspecialchars($obrigacao['descricao'] ?? '') : '' ?></textarea>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Órgão <span class="text-red-500">*</span></label>
                <select name="orgao" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <option value="federal" <?= $isEdit && $obrigacao['orgao'] === 'federal' ? 'selected' : '' ?>>Federal</option>
                    <option value="estadual" <?= $isEdit && $obrigacao['orgao'] === 'estadual' ? 'selected' : '' ?>>Estadual</option>
                    <option value="municipal" <?= $isEdit && $obrigacao['orgao'] === 'municipal' ? 'selected' : '' ?>>Municipal</option>
                    <option value="outros" <?= $isEdit && $obrigacao['orgao'] === 'outros' ? 'selected' : '' ?>>Outros</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Periodicidade <span class="text-red-500">*</span></label>
                <select name="periodicidade" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <option value="mensal" <?= $isEdit && $obrigacao['periodicidade'] === 'mensal' ? 'selected' : '' ?>>Mensal</option>
                    <option value="bimestral" <?= $isEdit && $obrigacao['periodicidade'] === 'bimestral' ? 'selected' : '' ?>>Bimestral</option>
                    <option value="trimestral" <?= $isEdit && $obrigacao['periodicidade'] === 'trimestral' ? 'selected' : '' ?>>Trimestral</option>
                    <option value="semestral" <?= $isEdit && $obrigacao['periodicidade'] === 'semestral' ? 'selected' : '' ?>>Semestral</option>
                    <option value="anual" <?= $isEdit && $obrigacao['periodicidade'] === 'anual' ? 'selected' : '' ?>>Anual</option>
                    <option value="eventual" <?= $isEdit && $obrigacao['periodicidade'] === 'eventual' ? 'selected' : '' ?>>Eventual</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Dia de Vencimento <span class="text-red-500">*</span></label>
                <input type="number" name="dia_vencimento" required min="1" max="31"
                       value="<?= $isEdit ? htmlspecialchars($obrigacao['dia_vencimento']) : '20' ?>"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Mês de Referência (para anuais)</label>
                <select name="mes_referencia" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <option value="">N/A</option>
                    <?php for ($m = 1; $m <= 12; $m++): ?>
                        <option value="<?= $m ?>" <?= $isEdit && $obrigacao['mes_referencia'] == $m ? 'selected' : '' ?>><?= str_pad($m, 2, '0', STR_PAD_LEFT) ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Forma de Entrega</label>
                <input type="text" name="forma_entrega" maxlength="100"
                       value="<?= $isEdit ? htmlspecialchars($obrigacao['forma_entrega'] ?? '') : '' ?>"
                       placeholder="Ex: PGDAS-D, SPED, eSocial"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Base Legal</label>
            <input type="text" name="base_legal" maxlength="500"
                   value="<?= $isEdit ? htmlspecialchars($obrigacao['base_legal'] ?? '') : '' ?>"
                   placeholder="Ex: Lei Complementar 123/2006, IN RFB 2.005/2021"
                   class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
        </div>

        <div class="flex items-center gap-4">
            <label class="flex items-center gap-2">
                <input type="checkbox" name="obrigatorio" value="1"
                       <?= !$isEdit || $obrigacao['obrigatorio'] ? 'checked' : '' ?>
                       class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                <span class="text-sm text-gray-700">Obrigatório</span>
            </label>
            <label class="flex items-center gap-2">
                <input type="checkbox" name="ativo" value="1"
                       <?= !$isEdit || $obrigacao['ativo'] ? 'checked' : '' ?>
                       class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                <span class="text-sm text-gray-700">Ativo</span>
            </label>
        </div>

        <div class="flex items-center justify-between pt-4 border-t border-gray-200">
            <a href="<?= BASE_URL ?>/obrigacoesFiscais" class="text-sm text-gray-500 hover:text-gray-700">
                <i class='bx bx-arrow-back'></i> Voltar
            </a>
            <button type="submit" class="px-5 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700 transition-colors">
                <i class='bx bx-check'></i> <?= $isEdit ? 'Salvar Alterações' : 'Cadastrar Obrigação' ?>
            </button>
        </div>
    </form>
</div>
