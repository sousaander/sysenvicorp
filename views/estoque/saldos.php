<?php $pageTitle = $pageTitle ?? 'Saldo de Estoque'; ?>
<div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Saldo de Estoque</h1>
            <p class="text-sm text-gray-500 mt-1">Consulta de saldo atual por produto</p>
        </div>
        <a href="<?= BASE_URL ?>/estoque/movimentos" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
            <i class='bx bx-transfer'></i> Movimentações
        </a>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200">
                    <th class="text-left p-3 font-semibold text-gray-600">Produto</th>
                    <th class="text-center p-3 font-semibold text-gray-600">Saldo Atual</th>
                    <th class="text-right p-3 font-semibold text-gray-600">Custo Médio</th>
                    <th class="text-right p-3 font-semibold text-gray-600">Valor Total</th>
                    <th class="text-center p-3 font-semibold text-gray-600">Última Mov.</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($saldos)): ?>
                    <tr><td colspan="5" class="p-8 text-center text-gray-400">Nenhum saldo encontrado.</td></tr>
                <?php else: ?>
                    <?php $totalGeral = 0; ?>
                    <?php foreach ($saldos as $s): ?>
                        <?php $totalGeral += $s['valor_total']; ?>
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="p-3">
                                <span class="font-medium text-gray-800"><?= htmlspecialchars($s['produto_nome']) ?></span>
                                <span class="text-xs text-gray-400 block"><?= htmlspecialchars($s['produto_codigo']) ?></span>
                            </td>
                            <td class="p-3 text-center">
                                <span class="px-3 py-1 rounded-full text-sm font-bold
                                    <?= $s['quantidade'] > 0 ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500' ?>">
                                    <?= number_format($s['quantidade'], 3, ',', '.') ?>
                                </span>
                            </td>
                            <td class="p-3 text-right font-medium text-gray-800">R$ <?= number_format($s['custo_medio'], 2, ',', '.') ?></td>
                            <td class="p-3 text-right font-medium text-blue-600">R$ <?= number_format($s['valor_total'], 2, ',', '.') ?></td>
                            <td class="p-3 text-center text-xs text-gray-500">
                                <?= !empty($s['ultima_movimentacao']) ? date('d/m/Y', strtotime($s['ultima_movimentacao'])) : '-' ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
            <?php if (!empty($saldos)): ?>
                <tfoot>
                    <tr class="bg-gray-50 border-t-2 border-gray-200">
                        <td class="p-3 font-semibold text-gray-700">Total Geral</td>
                        <td></td>
                        <td></td>
                        <td class="p-3 text-right font-bold text-lg text-blue-700">R$ <?= number_format($totalGeral, 2, ',', '.') ?></td>
                        <td></td>
                    </tr>
                </tfoot>
            <?php endif; ?>
        </table>
    </div>
</div>
