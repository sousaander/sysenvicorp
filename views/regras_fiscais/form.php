<?php $isEdit = isset($regra) && $regra !== null; ?>
<div class="p-6 max-w-5xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800"><?= $isEdit ? 'Editar Regra Fiscal' : 'Nova Regra Fiscal' ?></h1>
            <p class="text-sm text-gray-500 mt-1">Parametrize a tributação por produto, serviço e regime</p>
        </div>
    </div>

    <form action="<?= BASE_URL ?>/regrasFiscais/salvar" method="POST" class="bg-white rounded-xl border border-gray-200 p-6 space-y-6">
        <?php if ($isEdit): ?>
            <input type="hidden" name="id" value="<?= $regra['id'] ?>">
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Produto (opcional)</label>
                <select name="produto_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <option value="">Regra geral (todos os produtos)</option>
                    <?php foreach ($produtos as $p): ?>
                        <option value="<?= $p['id'] ?>" <?= $isEdit && $regra['produto_id'] == $p['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($p['codigo'] . ' - ' . $p['nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
                <select name="tipo_entidade" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <option value="produto" <?= $isEdit && $regra['tipo_entidade'] === 'produto' ? 'selected' : '' ?>>Produto</option>
                    <option value="servico" <?= $isEdit && $regra['tipo_entidade'] === 'servico' ? 'selected' : '' ?>>Serviço</option>
                    <option value="ambos" <?= $isEdit && $regra['tipo_entidade'] === 'ambos' ? 'selected' : '' ?>>Ambos</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Regime Tributário</label>
                <select name="regime_tributario" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <option value="">Todos os regimes</option>
                    <?php foreach ($regimes as $k => $v): ?>
                        <option value="<?= $k ?>" <?= $isEdit && $regra['regime_tributario'] === $k ? 'selected' : '' ?>><?= $v ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="border-t border-gray-200 pt-4">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">CFOP e CST</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">CFOP</label>
                    <select name="cfop" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        <option value="">Selecione</option>
                        <?php foreach ($cfop as $k => $v): ?>
                            <option value="<?= $k ?>" <?= $isEdit && $regra['cfop'] === $k ? 'selected' : '' ?>><?= $v ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">CST ICMS</label>
                    <select name="cst_icms" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        <option value="">Selecione</option>
                        <?php foreach ($cst as $k => $v): ?>
                            <option value="<?= $k ?>" <?= $isEdit && $regra['cst_icms'] === $k ? 'selected' : '' ?>><?= $v ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">CSOSN (Simples Nacional)</label>
                    <select name="csosn" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        <option value="">Selecione</option>
                        <?php foreach ($csosn as $k => $v): ?>
                            <option value="<?= $k ?>" <?= $isEdit && $regra['csosn'] === $k ? 'selected' : '' ?>><?= $v ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">CST IPI</label>
                    <select name="cst_ipi" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        <option value="">Selecione</option>
                        <option value="00" <?= $isEdit && $regra['cst_ipi'] === '00' ? 'selected' : '' ?>>00 - Entrada/Saída tributada</option>
                        <option value="01" <?= $isEdit && $regra['cst_ipi'] === '01' ? 'selected' : '' ?>>01 - Entrada tributada (alíquota zero)</option>
                        <option value="02" <?= $isEdit && $regra['cst_ipi'] === '02' ? 'selected' : '' ?>>02 - Saída tributada (alíquota zero)</option>
                        <option value="03" <?= $isEdit && $regra['cst_ipi'] === '03' ? 'selected' : '' ?>>03 - Isenta</option>
                        <option value="04" <?= $isEdit && $regra['cst_ipi'] === '04' ? 'selected' : '' ?>>04 - Não tributada</option>
                        <option value="49" <?= $isEdit && $regra['cst_ipi'] === '49' ? 'selected' : '' ?>>49 - Outras</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">CST PIS</label>
                    <select name="cst_pis" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        <option value="">Selecione</option>
                        <?php foreach ($pisCofins as $k => $v): ?>
                            <option value="<?= $k ?>" <?= $isEdit && $regra['cst_pis'] === $k ? 'selected' : '' ?>><?= $v ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">CST COFINS</label>
                    <select name="cst_cofins" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        <option value="">Selecione</option>
                        <?php foreach ($pisCofins as $k => $v): ?>
                            <option value="<?= $k ?>" <?= $isEdit && $regra['cst_cofins'] === $k ? 'selected' : '' ?>><?= $v ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <div class="border-t border-gray-200 pt-4">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Alíquotas</h3>
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">ICMS (%)</label>
                    <input type="number" name="aliquota_icms" step="0.01" min="0" max="100"
                           value="<?= $isEdit ? htmlspecialchars($regra['aliquota_icms']) : '0' ?>"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">IPI (%)</label>
                    <input type="number" name="aliquota_ipi" step="0.01" min="0" max="100"
                           value="<?= $isEdit ? htmlspecialchars($regra['aliquota_ipi']) : '0' ?>"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">PIS (%)</label>
                    <input type="number" name="aliquota_pis" step="0.01" min="0" max="100"
                           value="<?= $isEdit ? htmlspecialchars($regra['aliquota_pis']) : '0' ?>"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">COFINS (%)</label>
                    <input type="number" name="aliquota_cofins" step="0.01" min="0" max="100"
                           value="<?= $isEdit ? htmlspecialchars($regra['aliquota_cofins']) : '0' ?>"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">ISS (%)</label>
                    <input type="number" name="aliquota_iss" step="0.01" min="0" max="100"
                           value="<?= $isEdit ? htmlspecialchars($regra['aliquota_iss']) : '0' ?>"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                </div>
            </div>
        </div>

        <div class="border-t border-gray-200 pt-4">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Base de Cálculo e Benefícios</h3>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Redução Base ICMS (%)</label>
                    <input type="number" name="reducao_base_icms" step="0.01" min="0" max="100"
                           value="<?= $isEdit ? htmlspecialchars($regra['reducao_base_icms']) : '0' ?>"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">MVA / Margem ST (%)</label>
                    <input type="number" name="margem_st" step="0.01" min="0"
                           value="<?= $isEdit ? htmlspecialchars($regra['margem_st']) : '0' ?>"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Base de Cálculo (R$)</label>
                    <input type="number" name="base_calculo" step="0.01" min="0"
                           value="<?= $isEdit && $regra['base_calculo'] !== null ? htmlspecialchars($regra['base_calculo']) : '' ?>"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Benefício Fiscal</label>
                    <input type="text" name="beneficio_fiscal" maxlength="255"
                           value="<?= $isEdit ? htmlspecialchars($regra['beneficio_fiscal'] ?? '') : '' ?>"
                           placeholder="Ex: Redução ICMS, Crédito presumido"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                </div>
            </div>
        </div>

        <div class="border-t border-gray-200 pt-4">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Vigência e Abrangência</h3>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">UF Origem</label>
                    <input type="text" name="uf_origem" maxlength="2" placeholder="SP"
                           value="<?= $isEdit ? htmlspecialchars($regra['uf_origem'] ?? '') : '' ?>"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">UF Destino</label>
                    <input type="text" name="uf_destino" maxlength="2" placeholder="RJ"
                           value="<?= $isEdit ? htmlspecialchars($regra['uf_destino'] ?? '') : '' ?>"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Vigência Início</label>
                    <input type="date" name="data_vigencia_inicio"
                           value="<?= $isEdit ? htmlspecialchars($regra['data_vigencia_inicio'] ?? '') : '' ?>"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Vigência Fim</label>
                    <input type="date" name="data_vigencia_fim"
                           value="<?= $isEdit ? htmlspecialchars($regra['data_vigencia_fim'] ?? '') : '' ?>"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                </div>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-4">
            <label class="flex items-center gap-2">
                <input type="checkbox" name="ncm_obrigatorio" value="1"
                       <?= !$isEdit || $regra['ncm_obrigatorio'] ? 'checked' : '' ?>
                       class="rounded border-gray-300 text-orange-600 focus:ring-orange-500">
                <span class="text-sm text-gray-700">NCM obrigatório</span>
            </label>
            <label class="flex items-center gap-2">
                <input type="checkbox" name="cest_obrigatorio" value="1"
                       <?= $isEdit && $regra['cest_obrigatorio'] ? 'checked' : '' ?>
                       class="rounded border-gray-300 text-orange-600 focus:ring-orange-500">
                <span class="text-sm text-gray-700">CEST obrigatório</span>
            </label>
            <label class="flex items-center gap-2">
                <input type="checkbox" name="ativo" value="1"
                       <?= !$isEdit || $regra['ativo'] ? 'checked' : '' ?>
                       class="rounded border-gray-300 text-orange-600 focus:ring-orange-500">
                <span class="text-sm text-gray-700">Ativo</span>
            </label>
        </div>

        <div class="flex items-center justify-between pt-4 border-t border-gray-200">
            <a href="<?= BASE_URL ?>/regrasFiscais" class="text-sm text-gray-500 hover:text-gray-700">
                <i class='bx bx-arrow-back'></i> Voltar
            </a>
            <button type="submit" class="px-5 py-2 bg-orange-600 text-white text-sm font-medium rounded-lg hover:bg-orange-700 transition-colors">
                <i class='bx bx-check'></i> <?= $isEdit ? 'Salvar Alterações' : 'Cadastrar Regra' ?>
            </button>
        </div>
    </form>
</div>
