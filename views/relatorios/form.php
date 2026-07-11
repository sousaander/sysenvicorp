<?php $isEdit = isset($modelo) && $modelo !== null; ?>
<div class="p-6 max-w-3xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800"><?= $isEdit ? 'Editar Modelo' : 'Novo Modelo de Relatório' ?></h1>
            <p class="text-sm text-gray-500 mt-1">Customize colunas, filtros e layout do relatório</p>
        </div>
    </div>

    <form action="<?= BASE_URL ?>/relatorios/salvar" method="POST" class="bg-white rounded-xl border border-gray-200 p-6 space-y-5">
        <?php if ($isEdit): ?>
            <input type="hidden" name="id" value="<?= $modelo['id'] ?>">
        <?php endif; ?>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nome <span class="text-red-500">*</span></label>
            <input type="text" name="nome" required maxlength="255"
                   value="<?= $isEdit ? htmlspecialchars($modelo['nome']) : '' ?>"
                   placeholder="Ex: Relatório de Auditoria Fiscal - ICMS"
                   class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
            <input type="text" name="descricao" maxlength="500"
                   value="<?= $isEdit ? htmlspecialchars($modelo['descricao'] ?? '') : '' ?>"
                   placeholder="Finalidade do relatório, para qual auditoria se destina"
                   class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Módulo <span class="text-red-500">*</span></label>
                <select name="modulo" id="moduloSelect" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"
                        onchange="carregarColunas(this.value)">
                    <option value="">Selecione</option>
                    <?php foreach ($modulos as $k => $v): ?>
                        <option value="<?= $k ?>" <?= $isEdit && $modelo['modulo'] === $k ? 'selected' : '' ?>><?= $v ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Formato Padrão</label>
                <select name="formato_padrao" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <option value="pdf" <?= $isEdit && $modelo['formato_padrao'] === 'pdf' ? 'selected' : '' ?>>PDF</option>
                    <option value="xlsx" <?= $isEdit && $modelo['formato_padrao'] === 'xlsx' ? 'selected' : '' ?>>Excel (XLSX)</option>
                    <option value="csv" <?= $isEdit && $modelo['formato_padrao'] === 'csv' ? 'selected' : '' ?>>CSV</option>
                    <option value="html" <?= $isEdit && $modelo['formato_padrao'] === 'html' ? 'selected' : '' ?>>HTML</option>
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Orientação</label>
                <select name="orientacao" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <option value="retrato" <?= $isEdit && $modelo['orientacao'] === 'retrato' ? 'selected' : '' ?>>Retrato</option>
                    <option value="paisagem" <?= $isEdit && $modelo['orientacao'] === 'paisagem' ? 'selected' : '' ?>>Paisagem</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Período Padrão</label>
                <select name="periodo_padrao" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <option value="mensal">Mensal</option>
                    <option value="trimestral">Trimestral</option>
                    <option value="semestral">Semestral</option>
                    <option value="anual">Anual</option>
                    <option value="personalizado">Personalizado</option>
                </select>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Colunas do Relatório</label>
            <div id="colunasContainer" class="border border-gray-200 rounded-lg p-3 min-h-[100px]">
                <p class="text-sm text-gray-400">Selecione um módulo para carregar as colunas disponíveis.</p>
            </div>
            <div id="colunasSelecionadas" class="flex flex-wrap gap-2 mt-2"></div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Rodapé do Relatório</label>
            <input type="text" name="rodape" maxlength="500"
                   value="<?= $isEdit ? htmlspecialchars($modelo['rodape'] ?? '') : '' ?>"
                   placeholder="Ex: Documento gerado pelo Sistema Envicorp - Confidencial"
                   class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
        </div>

        <div class="flex items-center gap-2">
            <input type="checkbox" name="ativo" id="ativo" value="1"
                   <?= !$isEdit || $modelo['ativo'] ? 'checked' : '' ?>
                   class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
            <label for="ativo" class="text-sm text-gray-700">Modelo ativo</label>
        </div>

        <div class="flex items-center justify-between pt-4 border-t border-gray-200">
            <a href="<?= BASE_URL ?>/relatorios" class="text-sm text-gray-500 hover:text-gray-700">
                <i class='bx bx-arrow-back'></i> Voltar
            </a>
            <button type="submit" class="px-5 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors">
                <i class='bx bx-check'></i> <?= $isEdit ? 'Salvar Alterações' : 'Criar Modelo' ?>
            </button>
        </div>
    </form>
</div>

<script>
const colunasPorModulo = <?= json_encode($colunasDisponiveis ?? []) ?>;

document.addEventListener('DOMContentLoaded', function () {
    const moduloSelect = document.getElementById('moduloSelect');
    if (moduloSelect.value) carregarColunas(moduloSelect.value);
});

function carregarColunas(modulo) {
    const container = document.getElementById('colunasContainer');
    const selecionadas = document.getElementById('colunasSelecionadas');

    if (!modulo || !colunasPorModulo[modulo]) {
        container.innerHTML = '<p class="text-sm text-gray-400">Selecione um módulo para carregar as colunas disponíveis.</p>';
        selecionadas.innerHTML = '';
        return;
    }

    const colunas = colunasPorModulo[modulo];
    let html = '<div class="grid grid-cols-2 md:grid-cols-3 gap-2">';
    for (const [key, label] of Object.entries(colunas)) {
        html += `<label class="flex items-center gap-2 p-1 hover:bg-gray-50 rounded cursor-pointer">
                    <input type="checkbox" name="colunas[]" value="${key}" class="rounded border-gray-300 text-indigo-600">
                    <span class="text-sm">${label}</span>
                </label>`;
    }
    html += '</div>';
    container.innerHTML = html;
}
</script>
