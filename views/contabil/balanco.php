<?php $pageTitle = $pageTitle ?? 'Balanço Patrimonial'; ?>
<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Balanço Patrimonial</h1>
            <p class="text-sm text-gray-500 mt-1">Exercício de <?= $anoSelecionado ?></p>
        </div>
        <form method="GET" class="flex items-center gap-2">
            <select name="ano" onchange="this.form.submit()" class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm">
                <?php for ($a = date('Y') - 3; $a <= date('Y') + 1; $a++): ?>
                    <option value="<?= $a ?>" <?= $a == $anoSelecionado ? 'selected' : '' ?>><?= $a ?></option>
                <?php endfor; ?>
            </select>
        </form>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-emerald-500"></span> Ativo
            </h3>
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200">
                        <th class="text-left p-2 font-semibold text-gray-600">Conta</th>
                        <th class="text-right p-2 font-semibold text-gray-600">Saldo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data['ativo'])): ?>
                        <tr><td colspan="2" class="p-4 text-center text-gray-400">Nenhum lançamento no período</td></tr>
                    <?php else: ?>
                        <?php foreach ($data['ativo'] as $c): ?>
                            <tr class="border-b border-gray-100">
                                <td class="p-2 text-gray-700">
                                    <span class="font-mono text-xs text-gray-400"><?= htmlspecialchars($c['codigo']) ?></span>
                                    <?= htmlspecialchars($c['nome']) ?>
                                </td>
                                <td class="p-2 text-right font-mono <?= $c['saldo'] >= 0 ? 'text-gray-800' : 'text-red-600' ?>">
                                    R$ <?= number_format($c['saldo'], 2, ',', '.') ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <tfoot>
                    <tr class="font-bold bg-emerald-50">
                        <td class="p-2 text-emerald-800">Total do Ativo</td>
                        <td class="p-2 text-right font-mono text-emerald-800">R$ <?= number_format($totalAtivo, 2, ',', '.') ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="space-y-6">
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-amber-500"></span> Passivo
                </h3>
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200">
                            <th class="text-left p-2 font-semibold text-gray-600">Conta</th>
                            <th class="text-right p-2 font-semibold text-gray-600">Saldo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($data['passivo'])): ?>
                            <tr><td colspan="2" class="p-4 text-center text-gray-400">Nenhum lançamento no período</td></tr>
                        <?php else: ?>
                            <?php foreach ($data['passivo'] as $c): ?>
                                <tr class="border-b border-gray-100">
                                    <td class="p-2 text-gray-700">
                                        <span class="font-mono text-xs text-gray-400"><?= htmlspecialchars($c['codigo']) ?></span>
                                        <?= htmlspecialchars($c['nome']) ?>
                                    </td>
                                    <td class="p-2 text-right font-mono text-gray-800">
                                        R$ <?= number_format($c['saldo'], 2, ',', '.') ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr class="font-bold bg-amber-50">
                            <td class="p-2 text-amber-800">Total do Passivo</td>
                            <td class="p-2 text-right font-mono text-amber-800">R$ <?= number_format($totalPassivo, 2, ',', '.') ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-blue-500"></span> Patrimônio Líquido
                </h3>
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200">
                            <th class="text-left p-2 font-semibold text-gray-600">Conta</th>
                            <th class="text-right p-2 font-semibold text-gray-600">Saldo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($data['patrimonio_liquido'])): ?>
                            <tr><td colspan="2" class="p-4 text-center text-gray-400">Nenhum lançamento no período</td></tr>
                        <?php else: ?>
                            <?php foreach ($data['patrimonio_liquido'] as $c): ?>
                                <tr class="border-b border-gray-100">
                                    <td class="p-2 text-gray-700">
                                        <span class="font-mono text-xs text-gray-400"><?= htmlspecialchars($c['codigo']) ?></span>
                                        <?= htmlspecialchars($c['nome']) ?>
                                    </td>
                                    <td class="p-2 text-right font-mono text-gray-800">
                                        R$ <?= number_format($c['saldo'], 2, ',', '.') ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr class="font-bold bg-blue-50">
                            <td class="p-2 text-blue-800">Total do PL</td>
                            <td class="p-2 text-right font-mono text-blue-800">R$ <?= number_format($totalPL, 2, ',', '.') ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-6 bg-gray-50 rounded-xl border border-gray-200 p-4 text-center">
        <p class="text-sm text-gray-600">Total do Ativo: <strong>R$ <?= number_format($totalAtivo, 2, ',', '.') ?></strong></p>
        <p class="text-sm text-gray-600">Total do Passivo + PL: <strong>R$ <?= number_format($totalPassivo + $totalPL, 2, ',', '.') ?></strong></p>
        <p class="text-xs text-gray-400 mt-1">O balanço patrimonial deve apresentar Ativo = Passivo + PL</p>
    </div>
</div>
