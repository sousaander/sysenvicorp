<?php $pageTitle = $pageTitle ?? 'Inventário #' . $inventario['id']; ?>
<div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Inventário #<?= $inventario['id'] ?></h1>
            <p class="text-sm text-gray-500 mt-1">
                <?= date('d/m/Y', strtotime($inventario['data_inventario'])) ?>
                &middot; <span class="capitalize"><?= htmlspecialchars($inventario['tipo']) ?></span>
                &middot;
                <span class="px-2 py-0.5 rounded-full text-xs font-medium
                    <?= $inventario['status'] === 'aberto' ? 'bg-amber-100 text-amber-700' : '' ?>
                    <?= $inventario['status'] === 'contagem' ? 'bg-blue-100 text-blue-700' : '' ?>
                    <?= $inventario['status'] === 'finalizado' ? 'bg-emerald-100 text-emerald-700' : '' ?>">
                    <?= $inventario['status'] === 'aberto' ? 'Aberto' : ($inventario['status'] === 'contagem' ? 'Em Contagem' : 'Finalizado') ?>
                </span>
            </p>
        </div>
        <div class="flex items-center gap-3">
            <?php if ($inventario['status'] !== 'finalizado'): ?>
                <a href="<?= BASE_URL ?>/estoque/finalizarInventario/<?= $inventario['id'] ?>"
                   class="px-4 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700 transition-colors"
                   onclick="return confirm('Finalizar inventário? Ajustes de saldo serão aplicados automaticamente.')">
                    <i class='bx bx-check-double'></i> Finalizar Inventário
                </a>
            <?php endif; ?>
            <a href="<?= BASE_URL ?>/estoque/inventarios" class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">
                <i class='bx bx-arrow-back'></i> Voltar
            </a>
        </div>
    </div>

    <?php if (!empty($inventario['observacoes'])): ?>
        <div class="bg-gray-50 border border-gray-200 rounded-xl p-3 text-sm text-gray-600">
            <?= htmlspecialchars($inventario['observacoes']) ?>
        </div>
    <?php endif; ?>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200">
                    <th class="text-left p-3 font-semibold text-gray-600">Produto</th>
                    <th class="text-right p-3 font-semibold text-gray-600">Saldo Sistema</th>
                    <th class="text-right p-3 font-semibold text-gray-600">Quant. Contada</th>
                    <th class="text-right p-3 font-semibold text-gray-600">Diferença</th>
                    <th class="text-left p-3 font-semibold text-gray-600">Status</th>
                    <th class="text-left p-3 font-semibold text-gray-600">Obs.</th>
                    <?php if ($inventario['status'] !== 'finalizado'): ?>
                        <th class="text-right p-3 font-semibold text-gray-600">Ação</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($itens)): ?>
                    <tr><td colspan="7" class="p-8 text-center text-gray-400">Nenhum item neste inventário.</td></tr>
                <?php else: ?>
                    <?php foreach ($itens as $item): ?>
                        <?php
                        $diferenca = $item['quantidade_contada'] - $item['saldo_sistema'];
                        $temDivergencia = abs($diferenca) > 0.001;
                        ?>
                        <tr class="border-b border-gray-100 hover:bg-gray-50 <?= $temDivergencia ? 'bg-red-50' : '' ?>">
                            <td class="p-3">
                                <span class="font-medium text-gray-800"><?= htmlspecialchars($item['produto_nome']) ?></span>
                                <span class="text-xs text-gray-400 block"><?= htmlspecialchars($item['produto_codigo']) ?></span>
                            </td>
                            <td class="p-3 text-right font-mono"><?= number_format($item['saldo_sistema'], 3, ',', '.') ?></td>
                            <td class="p-3 text-right font-mono">
                                <?php if ($inventario['status'] !== 'finalizado'): ?>
                                    <form method="POST" action="<?= BASE_URL ?>/estoque/atualizarContagem" class="inline-flex items-center gap-1">
                                        <input type="hidden" name="inventario_id" value="<?= $inventario['id'] ?>">
                                        <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                                        <input type="number" name="quantidade_contada" step="0.001" min="0"
                                               value="<?= $item['quantidade_contada'] ?? 0 ?>"
                                               class="w-24 rounded border border-gray-300 px-2 py-1 text-xs text-right">
                                        <button type="submit" class="px-2 py-1 text-xs bg-blue-600 text-white rounded hover:bg-blue-700">
                                            <i class='bx bx-check'></i>
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <?= number_format($item['quantidade_contada'], 3, ',', '.') ?>
                                <?php endif; ?>
                            </td>
                            <td class="p-3 text-right font-mono font-medium <?= $diferenca < 0 ? 'text-red-600' : ($diferenca > 0 ? 'text-blue-600' : 'text-gray-400') ?>">
                                <?= $diferenca != 0 ? ($diferenca > 0 ? '+' : '') . number_format($diferenca, 3, ',', '.') : '-' ?>
                            </td>
                            <td class="p-3">
                                <?php if ($temDivergencia): ?>
                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">Divergente</span>
                                <?php else: ?>
                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">OK</span>
                                <?php endif; ?>
                            </td>
                            <td class="p-3 text-xs text-gray-500 max-w-[150px] truncate"><?= htmlspecialchars($item['observacoes'] ?? '-') ?></td>
                            <?php if ($inventario['status'] !== 'finalizado'): ?>
                                <td class="p-3 text-right">
                                    <form method="POST" action="<?= BASE_URL ?>/estoque/atualizarContagem" class="inline">
                                        <input type="hidden" name="inventario_id" value="<?= $inventario['id'] ?>">
                                        <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                                        <input type="hidden" name="quantidade_contada" value="<?= $item['saldo_sistema'] ?>">
                                        <input type="text" name="observacoes" placeholder="Obs. rápida"
                                               class="w-24 rounded border border-gray-300 px-2 py-1 text-xs">
                                        <button type="submit" class="px-2 py-1 text-xs text-blue-600 hover:bg-blue-50 rounded">
                                            <i class='bx bx-save'></i>
                                        </button>
                                    </form>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 text-sm text-amber-800">
        <i class='bx bx-info-circle'></i>
        <?php if ($inventario['status'] === 'finalizado'): ?>
            Inventário finalizado. Os ajustes de saldo já foram aplicados automaticamente.
        <?php else: ?>
            Informe a quantidade contada ao lado de cada produto e clique em <i class='bx bx-check'></i> para salvar.
            Ao finalizar, os saldos serão ajustados automaticamente com base nas diferenças apuradas.
        <?php endif; ?>
    </div>
</div>
