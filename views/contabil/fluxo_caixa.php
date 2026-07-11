<?php $pageTitle = $pageTitle ?? 'Fluxo de Caixa'; ?>
<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Fluxo de Caixa</h1>
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

    <?php if (empty($data)): ?>
        <div class="bg-white rounded-xl border border-gray-200 p-8 text-center">
            <i class='bx bx-line-chart text-4xl text-gray-300 mb-3'></i>
            <p class="text-gray-500">Nenhum lançamento contábil encontrado para o período.</p>
        </div>
    <?php else: ?>
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <th class="text-left p-3 font-semibold text-gray-600">Mês</th>
                        <th class="text-right p-3 font-semibold text-gray-600">Entradas</th>
                        <th class="text-right p-3 font-semibold text-gray-600">Saídas</th>
                        <th class="text-right p-3 font-semibold text-gray-600">Saldo do Mês</th>
                        <th class="text-right p-3 font-semibold text-gray-600">Saldo Acumulado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $meses_pt = [1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril', 5 => 'Maio', 6 => 'Junho',
                                  7 => 'Julho', 8 => 'Agosto', 9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'];
                    ?>
                    <?php foreach ($data as $m): ?>
                        <?php
                        $numMes = (int)substr($m['mes'], 5, 2);
                        $nomeMes = $meses_pt[$numMes] ?? $m['mes'];
                        ?>
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="p-3 font-medium text-gray-800"><?= $nomeMes ?></td>
                            <td class="p-3 text-right font-mono text-emerald-600">R$ <?= number_format($m['entradas'], 2, ',', '.') ?></td>
                            <td class="p-3 text-right font-mono text-rose-600">R$ <?= number_format($m['saidas'], 2, ',', '.') ?></td>
                            <td class="p-3 text-right font-mono <?= $m['saldo'] >= 0 ? 'text-emerald-600' : 'text-rose-600' ?>">
                                R$ <?= number_format($m['saldo'], 2, ',', '.') ?>
                            </td>
                            <td class="p-3 text-right font-mono font-bold <?= $m['acumulado'] >= 0 ? 'text-blue-600' : 'text-red-600' ?>">
                                R$ <?= number_format($m['acumulado'], 2, ',', '.') ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="font-bold bg-gray-50 border-t-2 border-gray-200">
                        <td class="p-3 text-gray-800">Total</td>
                        <td class="p-3 text-right text-emerald-700">R$ <?= number_format(array_sum(array_column($data, 'entradas')), 2, ',', '.') ?></td>
                        <td class="p-3 text-right text-rose-700">R$ <?= number_format(array_sum(array_column($data, 'saidas')), 2, ',', '.') ?></td>
                        <td class="p-3 text-right text-blue-700">—</td>
                        <td class="p-3 text-right text-blue-700">R$ <?= number_format(end($data)['acumulado'], 2, ',', '.') ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    <?php endif; ?>

    <div class="mt-4 text-center">
        <a href="<?= BASE_URL ?>/contabil/demonstracoes" class="text-sm text-gray-500 hover:text-gray-700">
            <i class='bx bx-arrow-back'></i> Voltar para Demonstrações
        </a>
    </div>
</div>
