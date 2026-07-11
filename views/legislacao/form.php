<?php $isEdit = isset($versao) && $versao !== null; ?>
<div class="p-6 max-w-3xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800"><?= $isEdit ? 'Editar Versão' : 'Nova Versão Legislativa' ?></h1>
            <p class="text-sm text-gray-500 mt-1">Registro de alterações na legislação com impacto fiscal</p>
        </div>
    </div>

    <form action="<?= BASE_URL ?>/legislacao/salvar" method="POST" class="bg-white rounded-xl border border-gray-200 p-6 space-y-5">
        <?php if ($isEdit): ?>
            <input type="hidden" name="id" value="<?= $versao['id'] ?>">
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Módulo <span class="text-red-500">*</span></label>
                <select name="modulo" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <option value="">Selecione</option>
                    <?php foreach ($modulos as $k => $v): ?>
                        <option value="<?= $k ?>" <?= $isEdit && $versao['modulo'] === $k ? 'selected' : '' ?>><?= $v ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Versão</label>
                <input type="text" name="versao" maxlength="20"
                       value="<?= $isEdit ? htmlspecialchars($versao['versao'] ?? '') : '' ?>"
                       placeholder="Ex: 1.0, 2026.1"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Título <span class="text-red-500">*</span></label>
            <input type="text" name="titulo" required maxlength="255"
                   value="<?= $isEdit ? htmlspecialchars($versao['titulo']) : '' ?>"
                   placeholder="Ex: Alteração na alíquota do ICMS-ST para produtos de informática"
                   class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
            <textarea name="descricao" rows="3" maxlength="2000"
                      class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"><?= $isEdit ? htmlspecialchars($versao['descricao'] ?? '') : '' ?></textarea>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Ato</label>
                <select name="tipo_ato" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <option value="">Selecione</option>
                    <?php foreach ($tiposAto as $k => $v): ?>
                        <option value="<?= $k ?>" <?= $isEdit && $versao['tipo_ato'] === $k ? 'selected' : '' ?>><?= $v ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Número do Ato</label>
                <input type="text" name="numero_ato" maxlength="50"
                       value="<?= $isEdit ? htmlspecialchars($versao['numero_ato'] ?? '') : '' ?>"
                       placeholder="Ex: 123/2026"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Órgão Emissor</label>
                <input type="text" name="orgao_emissor" maxlength="100"
                       value="<?= $isEdit ? htmlspecialchars($versao['orgao_emissor'] ?? '') : '' ?>"
                       placeholder="Ex: CONFAZ, RFB, SEFAZ"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Data de Publicação</label>
                <input type="date" name="data_publicacao"
                       value="<?= $isEdit ? htmlspecialchars($versao['data_publicacao'] ?? '') : '' ?>"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Data de Vigência</label>
                <input type="date" name="data_vigencia"
                       value="<?= $isEdit ? htmlspecialchars($versao['data_vigencia'] ?? '') : '' ?>"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Data de Revogação</label>
                <input type="date" name="data_revogacao"
                       value="<?= $isEdit ? htmlspecialchars($versao['data_revogacao'] ?? '') : '' ?>"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Resumo das Mudanças</label>
            <textarea name="resumo_mudancas" rows="3" maxlength="2000"
                      class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"><?= $isEdit ? htmlspecialchars($versao['resumo_mudancas'] ?? '') : '' ?></textarea>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Impacto Esperado</label>
            <textarea name="impacto_esperado" rows="2" maxlength="2000"
                      class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"><?= $isEdit ? htmlspecialchars($versao['impacto_esperado'] ?? '') : '' ?></textarea>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Arquivo Anexo (caminho ou URL)</label>
            <input type="text" name="arquivo_anexo" maxlength="255"
                   value="<?= $isEdit ? htmlspecialchars($versao['arquivo_anexo'] ?? '') : '' ?>"
                   placeholder="Ex: /storage/legislacao/icms_2026.pdf"
                   class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
        </div>

        <div class="flex items-center gap-2">
            <input type="checkbox" name="obrigatorio" value="1"
                   <?= $isEdit && $versao['obrigatorio'] ? 'checked' : '' ?>
                   class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
            <label class="text-sm text-gray-700">Obrigatório (requer ação do usuário)</label>
        </div>

        <div class="flex items-center justify-between pt-4 border-t border-gray-200">
            <a href="<?= BASE_URL ?>/legislacao" class="text-sm text-gray-500 hover:text-gray-700">
                <i class='bx bx-arrow-back'></i> Voltar
            </a>
            <button type="submit" class="px-5 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors">
                <i class='bx bx-check'></i> <?= $isEdit ? 'Salvar Alterações' : 'Cadastrar Versão' ?>
            </button>
        </div>
    </form>
</div>
