<?php $pageTitle = $pageTitle ?? 'Movimentações de Estoque'; ?>
<div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Movimentações de Estoque</h1>
            <p class="text-sm text-gray-500 mt-1">Entradas, saídas e ajustes de inventário</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="<?= BASE_URL ?>/estoque/entradaForm" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                <i class='bx bx-plus-circle'></i> Nova Entrada
            </a>
            <a href="<?= BASE_URL ?>/estoque/saidaForm" class="inline-flex items-center gap-2 px-4 py-2 bg-rose-600 text-white text-sm font-medium rounded-lg hover:bg-rose-700 transition-colors">
                <i class='bx bx-minus-circle'></i> Nova Saída
            </a>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <form method="GET" class="flex flex-wrap items-end gap-3">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Produto</label>
                <select name="produto_id" class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm">
                    <option value="">Todos</option>
                    <?php foreach ($produtos as $p): ?>
                        <option value="<?= $p['id'] ?>" <?= $produtoId == $p['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($p['codigo'] . ' - ' . $p['nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="px-4 py-1.5 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
                <i class='bx bx-filter'></i> Filtrar
            </button>
        </form>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200">
                    <th class="text-left p-3 font-semibold text-gray-600">Data</th>
                    <th class="text-left p-3 font-semibold text-gray-600">Produto</th>
                    <th class="text-center p-3 font-semibold text-gray-600">Tipo</th>
                    <th class="text-right p-3 font-semibold text-gray-600">Quantidade</th>
                    <th class="text-right p-3 font-semibold text-gray-600">Valor Unit.</th>
                    <th class="text-right p-3 font-semibold text-gray-600">Valor Total</th>
                    <th class="text-left p-3 font-semibold text-gray-600">Documento</th>
                    <th class="text-right p-3 font-semibold text-gray-600">Saldo Após</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($movimentos)): ?>
                    <tr><td colspan="8" class="p-8 text-center text-gray-400">Nenhuma movimentação encontrada.</td></tr>
                <?php else: ?>
                    <?php foreach ($movimentos as $m): ?>
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="p-3 text-gray-600"><?= date('d/m/Y', strtotime($m['data_movimento'])) ?></td>
                            <td class="p-3">
                                <span class="font-medium text-gray-800"><?= htmlspecialchars($m['produto_nome']) ?></span>
                                <span class="text-xs text-gray-400 block"><?= htmlspecialchars($m['produto_codigo']) ?></span>
                            </td>
                            <td class="p-3 text-center">
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium
                                    <?= $m['tipo_movimento'] === 'entrada' ? 'bg-blue-100 text-blue-700' : '' ?>
                                    <?= $m['tipo_movimento'] === 'saida' ? 'bg-rose-100 text-rose-700' : '' ?>
                                    <?= $m['tipo_movimento'] === 'ajuste' ? 'bg-amber-100 text-amber-700' : '' ?>">
                                    <?= ucfirst($m['tipo_movimento']) ?>
                                </span>
                            </td>
                            <td class="p-3 text-right font-medium <?= $m['tipo_movimento'] === 'entrada' ? 'text-blue-600' : 'text-rose-600' ?>">
                                <?= number_format($m['quantidade'], 3, ',', '.') ?>
                            </td>
                            <td class="p-3 text-right text-gray-600">R$ <?= number_format($m['valor_unitario'], 2, ',', '.') ?></td>
                            <td class="p-3 text-right font-medium text-gray-800">R$ <?= number_format($m['valor_total'], 2, ',', '.') ?></td>
                            <td class="p-3 text-gray-500 text-xs"><?= htmlspecialchars($m['documento'] ?? '-') ?></td>
                            <td class="p-3 text-right font-mono text-sm text-gray-600"><?= number_format($m['saldo_apos'], 3, ',', '.') ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
