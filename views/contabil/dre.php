<?php $pageTitle = $pageTitle ?? 'Demonstração de Resultado (DRE)'; ?>
<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Demonstração de Resultado (DRE)</h1>
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

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-5">
            <p class="text-xs text-emerald-700 uppercase tracking-wider font-medium mb-1">Receitas</p>
            <p class="text-2xl font-bold text-emerald-700">R$ <?= number_format($data['receitas'], 2, ',', '.') ?></p>
        </div>
        <div class="bg-rose-50 border border-rose-200 rounded-xl p-5">
            <p class="text-xs text-rose-700 uppercase tracking-wider font-medium mb-1">Despesas</p>
            <p class="text-2xl font-bold text-rose-700">R$ <?= number_format($data['despesas'], 2, ',', '.') ?></p>
        </div>
        <div class="<?= $data['resultado'] >= 0 ? 'bg-blue-50 border-blue-200' : 'bg-red-50 border-red-200' ?> border rounded-xl p-5">
            <p class="text-xs <?= $data['resultado'] >= 0 ? 'text-blue-700' : 'text-red-700' ?> uppercase tracking-wider font-medium mb-1">
                <?= $data['resultado'] >= 0 ? 'Lucro Líquido' : 'Prejuízo' ?>
            </p>
            <p class="text-2xl font-bold <?= $data['resultado'] >= 0 ? 'text-blue-700' : 'text-red-700' ?>">
                R$ <?= number_format(abs($data['resultado']), 2, ',', '.') ?>
            </p>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-4">Detalhamento Contábil</h3>
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-200">
                    <th class="text-left p-2 font-semibold text-gray-600 w-24">Código</th>
                    <th class="text-left p-2 font-semibold text-gray-600">Conta</th>
                    <th class="text-right p-2 font-semibold text-gray-600">Débitos</th>
                    <th class="text-right p-2 font-semibold text-gray-600">Créditos</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $stmt = $this->db ?? null;
                if ($stmt) {
                    $sql = "SELECT pc.codigo, pc.nome, pc.natureza,
                                   COALESCE(SUM(CASE WHEN l.tipo='debito' THEN l.valor ELSE 0 END),0) as total_debito,
                                   COALESCE(SUM(CASE WHEN l.tipo='credito' THEN l.valor ELSE 0 END),0) as total_credito
                            FROM plano_contas pc
                            JOIN lancamentos_contabeis l ON (pc.id=l.debito_conta_id OR pc.id=l.credito_conta_id)
                                AND YEAR(l.data_lancamento) = ?
                            WHERE pc.ativo=1 AND (pc.codigo LIKE '3.%' OR pc.codigo LIKE '4.%')
                            GROUP BY pc.id ORDER BY pc.codigo ASC";
                    $s = $stmt->prepare($sql);
                    $s->execute([$anoSelecionado]);
                    $rows = $s->fetchAll(PDO::FETCH_ASSOC);
                    if (empty($rows)): ?>
                        <tr><td colspan="4" class="p-4 text-center text-gray-400">Nenhum lançamento contábil no período.</td></tr>
                    <?php else: ?>
                        <?php foreach ($rows as $r): ?>
                            <tr class="border-b border-gray-100">
                                <td class="p-2 text-gray-500 font-mono"><?= htmlspecialchars($r['codigo']) ?></td>
                                <td class="p-2 text-gray-700 font-medium"><?= htmlspecialchars($r['nome']) ?></td>
                                <td class="p-2 text-right font-mono text-rose-600">R$ <?= number_format($r['total_debito'], 2, ',', '.') ?></td>
                                <td class="p-2 text-right font-mono text-emerald-600">R$ <?= number_format($r['total_credito'], 2, ',', '.') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif;
                } else { ?>
                    <tr><td colspan="4" class="p-4 text-center text-gray-400">
                        Dados do plano de contas indisponíveis.
                    </td></tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <div class="mt-4 text-center">
        <a href="<?= BASE_URL ?>/contabil/demonstracoes" class="text-sm text-gray-500 hover:text-gray-700">
            <i class='bx bx-arrow-back'></i> Voltar para Demonstrações
        </a>
    </div>
</div>
