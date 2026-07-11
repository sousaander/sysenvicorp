<?php $pageTitle = $pageTitle ?? 'Preview: ' . $modelo['nome']; ?>
<div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800"><?= htmlspecialchars($modelo['nome']) ?></h1>
            <p class="text-sm text-gray-500 mt-1"><?= htmlspecialchars($modelo['descricao'] ?? '') ?></p>
        </div>
        <div class="flex items-center gap-3">
            <a href="<?= BASE_URL ?>/relatorios/exportar/<?= $modelo['id'] ?>?formato=csv" class="px-4 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700">
                <i class='bx bx-download'></i> Exportar CSV
            </a>
            <a href="<?= BASE_URL ?>/relatorios" class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200">
                <i class='bx bx-arrow-back'></i> Voltar
            </a>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <form method="GET" class="flex flex-wrap items-end gap-3">
            <input type="hidden" name="modulo" value="<?= $modelo['modulo'] ?>">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Data Início</label>
                <input type="date" name="data_inicio" value="<?= htmlspecialchars($filtros['data_inicio'] ?? '') ?>"
                       class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Data Fim</label>
                <input type="date" name="data_fim" value="<?= htmlspecialchars($filtros['data_fim'] ?? '') ?>"
                       class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm">
            </div>
            <button type="submit" class="px-4 py-1.5 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
                <i class='bx bx-filter'></i> Atualizar
            </button>
        </form>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200">
                    <?php
                    $colunas = $config['colunas'] ?? [];
                    if (!empty($colunas)):
                        foreach ($colunas as $col): ?>
                            <th class="text-left p-3 font-semibold text-gray-600 whitespace-nowrap"><?= htmlspecialchars(ucwords(str_replace('_', ' ', $col))) ?></th>
                        <?php endforeach;
                    elseif (!empty($dados)):
                        foreach (array_keys($dados[0]) as $col): ?>
                            <th class="text-left p-3 font-semibold text-gray-600 whitespace-nowrap"><?= htmlspecialchars(ucwords(str_replace('_', ' ', $col))) ?></th>
                        <?php endforeach;
                    endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($dados)): ?>
                    <tr><td colspan="99" class="p-8 text-center text-gray-400">Nenhum dado encontrado para os filtros atuais.</td></tr>
                <?php else: ?>
                    <?php foreach ($dados as $row): ?>
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <?php if (!empty($colunas)): ?>
                                <?php foreach ($colunas as $col): ?>
                                    <td class="p-3 text-gray-600"><?= htmlspecialchars($row[$col] ?? '-') ?></td>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <?php foreach ($row as $val): ?>
                                    <td class="p-3 text-gray-600"><?= htmlspecialchars(is_string($val) || is_numeric($val) ? $val : '-') ?></td>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="text-xs text-gray-400 text-center">
        <?= count($dados) ?> registro(s) encontrados
        <?php if (!empty($modelo['rodape'])): ?>
            &middot; <?= htmlspecialchars($modelo['rodape']) ?>
        <?php endif; ?>
    </div>
</div>
