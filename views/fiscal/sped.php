<?php $pageTitle = $pageTitle ?? 'SPED'; ?>
<div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">SPED</h1>
            <p class="text-sm text-gray-500 mt-1">Sistema Público de Escrituração Digital</p>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <form method="GET" class="flex items-end gap-4 flex-wrap">
            <input type="hidden" name="tipo" value="<?= htmlspecialchars($tipo) ?>">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
                <select name="tipo" class="border border-gray-300 rounded-lg px-3 py-2 text-sm" onchange="this.form.submit()">
                    <option value="fiscal" <?= $tipo === 'fiscal' ? 'selected' : '' ?>>SPED Fiscal</option>
                    <option value="contabil" <?= $tipo === 'contabil' ? 'selected' : '' ?>>SPED Contábil</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Data Início</label>
                <input type="date" name="data_inicio" value="<?= htmlspecialchars($dataInicio) ?>" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Data Fim</label>
                <input type="date" name="data_fim" value="<?= htmlspecialchars($dataFim) ?>" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 text-sm">Visualizar</button>
                <button type="submit" name="exportar" value="1" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 text-sm">Exportar TXT</button>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Pré-visualização</h2>
        <p class="text-sm text-gray-500">Selecione o período e clique em "Visualizar" para gerar os dados do SPED.</p>
        <p class="text-sm text-gray-500 mt-2">Tipo selecionado: <strong><?= $tipo === 'contabil' ? 'SPED Contábil (ECD)' : 'SPED Fiscal' ?></strong></p>
        <p class="text-sm text-gray-500">Período: <strong><?= date('d/m/Y', strtotime($dataInicio)) ?> a <?= date('d/m/Y', strtotime($dataFim)) ?></strong></p>
    </div>
</div>
