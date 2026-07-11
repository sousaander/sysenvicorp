<?php $pageTitle = $pageTitle ?? 'Lançamentos Contábeis'; ?>
<div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Lançamentos Contábeis</h1>
            <p class="text-sm text-gray-500 mt-1">Registros de débito e crédito do período</p>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200">
                    <th class="text-left p-3 font-semibold text-gray-600">Data</th>
                    <th class="text-left p-3 font-semibold text-gray-600">Descrição</th>
                    <th class="text-left p-3 font-semibold text-gray-600">Categoria</th>
                    <th class="text-left p-3 font-semibold text-gray-600">Tipo</th>
                    <th class="text-right p-3 font-semibold text-gray-600">Valor</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($lancamentos)): ?>
                    <tr><td colspan="5" class="p-8 text-center text-gray-400">Nenhum lançamento encontrado.</td></tr>
                <?php else: ?>
                    <?php foreach ($lancamentos as $l): ?>
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="p-3 text-gray-600"><?= date('d/m/Y', strtotime($l['data_lancamento'])) ?></td>
                            <td class="p-3 font-medium text-gray-800"><?= htmlspecialchars($l['descricao']) ?></td>
                            <td class="p-3 text-gray-600"><?= htmlspecialchars($l['categoria'] ?? '-') ?></td>
                            <td class="p-3">
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium <?= $l['tipo'] === 'credito' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' ?>">
                                    <?= $l['tipo'] === 'credito' ? 'Crédito' : 'Débito' ?>
                                </span>
                            </td>
                            <td class="p-3 text-right font-medium <?= $l['tipo'] === 'credito' ? 'text-emerald-600' : 'text-rose-600' ?>">
                                R$ <?= number_format($l['valor'], 2, ',', '.') ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
