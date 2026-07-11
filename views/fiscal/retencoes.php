<?php $pageTitle = $pageTitle ?? 'Retenções de Impostos'; ?>
<div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Retenções de Impostos</h1>
            <p class="text-sm text-gray-500 mt-1">Consolidação de retenções por período</p>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <form method="GET" class="flex items-end gap-4 flex-wrap">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Data Início</label>
                <input type="date" name="data_inicio" value="<?= htmlspecialchars($dataInicio) ?>" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Data Fim</label>
                <input type="date" name="data_fim" value="<?= htmlspecialchars($dataFim) ?>" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
            </div>
            <button type="submit" class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 text-sm">Filtrar</button>
        </form>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Totais por Tipo de Retenção</h2>
        <p class="text-sm text-gray-500 mb-4">Período: <?= date('d/m/Y', strtotime($dataInicio)) ?> a <?= date('d/m/Y', strtotime($dataFim)) ?></p>
        <table class="w-full text-sm">
            <thead><tr class="text-left text-gray-500 border-b"><th class="pb-3">Tipo</th><th class="pb-3 text-right">Valor Total</th></tr></thead>
            <tbody>
                <?php if (empty($totais)): ?>
                    <tr><td colspan="2" class="py-4 text-center text-gray-400">Nenhuma retenção encontrada no período.</td></tr>
                <?php else: ?>
                    <?php foreach ($totais as $t): ?>
                        <tr class="border-t border-gray-100">
                            <td class="py-3 font-medium text-gray-800"><?= htmlspecialchars($t['tipo_retencao']) ?></td>
                            <td class="py-3 text-right font-medium text-gray-800">R$ <?= number_format($t['total'], 2, ',', '.') ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
