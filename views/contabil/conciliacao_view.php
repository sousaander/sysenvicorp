<?php $pageTitle = $pageTitle ?? 'Conciliação Bancária'; ?>
<div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Conciliação Bancária</h1>
            <p class="text-sm text-gray-500 mt-1"><?= htmlspecialchars($conciliacao['banco_nome'] ?? 'Banco') ?></p>
        </div>
        <div class="flex items-center gap-2">
            <?php if ($conciliacao['status'] === 'aberta'): ?>
                <a href="<?= BASE_URL ?>/contabil/finalizarConciliacao/<?= $conciliacao['id'] ?>"
                   class="px-4 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700"
                   onclick="return confirm('Finalizar conciliação?')">
                    <i class='bx bx-check'></i> Finalizar Conciliação
                </a>
            <?php endif; ?>
            <a href="<?= BASE_URL ?>/contabil/conciliacoes" class="px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg hover:bg-gray-50">
                Voltar
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500 uppercase tracking-wider font-medium">Período</p>
            <p class="text-lg font-bold text-gray-800"><?= date('d/m/Y', strtotime($conciliacao['periodo_inicio'])) ?> - <?= date('d/m/Y', strtotime($conciliacao['periodo_fim'])) ?></p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500 uppercase tracking-wider font-medium">Saldo Extrato</p>
            <p class="text-lg font-bold text-blue-600">R$ <?= number_format($conciliacao['saldo_extrato'], 2, ',', '.') ?></p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500 uppercase tracking-wider font-medium">Saldo Sistema</p>
            <p class="text-lg font-bold text-blue-600">R$ <?= number_format($conciliacao['saldo_sistema'], 2, ',', '.') ?></p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500 uppercase tracking-wider font-medium">Diferença</p>
            <p class="text-lg font-bold <?= abs($conciliacao['diferenca']) < 0.01 ? 'text-emerald-600' : 'text-red-600' ?>">
                R$ <?= number_format($conciliacao['diferenca'], 2, ',', '.') ?>
            </p>
        </div>
    </div>

    <div class="flex items-center gap-2">
        <span class="px-3 py-1 rounded-full text-xs font-semibold
            <?= $conciliacao['status'] === 'conciliada' ? 'bg-emerald-100 text-emerald-700' : '' ?>
            <?= $conciliacao['status'] === 'aberta' ? 'bg-amber-100 text-amber-700' : '' ?>
            <?= $conciliacao['status'] === 'divergente' ? 'bg-red-100 text-red-700' : '' ?>">
            <?= ucfirst($conciliacao['status']) ?>
        </span>
        <?php if (!empty($conciliacao['conciliada_em'])): ?>
            <span class="text-xs text-gray-400">Finalizada em <?= date('d/m/Y H:i', strtotime($conciliacao['conciliada_em'])) ?></span>
        <?php endif; ?>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="p-4 border-b border-gray-200">
            <h3 class="font-semibold text-gray-800">Itens da Conciliação</h3>
        </div>

        <?php if (empty($itens)): ?>
            <div class="p-8 text-center text-gray-400">Nenhum item encontrado para esta conciliação.</div>
        <?php else: ?>
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <th class="text-left p-3 font-semibold text-gray-600">Data</th>
                        <th class="text-left p-3 font-semibold text-gray-600">Descrição</th>
                        <th class="text-left p-3 font-semibold text-gray-600">Tipo</th>
                        <th class="text-right p-3 font-semibold text-gray-600">Valor</th>
                        <th class="text-left p-3 font-semibold text-gray-600">Status</th>
                        <th class="text-center p-3 font-semibold text-gray-600">Ação</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($itens as $item): ?>
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="p-3 text-gray-600"><?= date('d/m/Y', strtotime($item['data_operacao'])) ?></td>
                            <td class="p-3 text-gray-700 font-medium"><?= htmlspecialchars($item['descricao']) ?></td>
                            <td class="p-3">
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium
                                    <?= $item['tipo'] === 'extrato' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700' ?>">
                                    <?= $item['tipo'] === 'extrato' ? 'Extrato' : 'Sistema' ?>
                                </span>
                            </td>
                            <td class="p-3 text-right font-mono">R$ <?= number_format($item['valor'], 2, ',', '.') ?></td>
                            <td class="p-3">
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium
                                    <?= $item['status_conciliacao'] === 'conciliado' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' ?>">
                                    <?= $item['status_conciliacao'] === 'conciliado' ? 'Conciliado' : 'Pendente' ?>
                                </span>
                            </td>
                            <td class="p-3 text-center">
                                <?php if ($item['status_conciliacao'] === 'pendente'): ?>
                                    <a href="<?= BASE_URL ?>/contabil/conciliarItem/<?= $item['id'] ?>"
                                       class="px-3 py-1 text-xs font-medium text-emerald-600 hover:bg-emerald-50 rounded-lg"
                                       onclick="return confirm('Conciliar este item?')">
                                        <i class='bx bx-check'></i> Conciliar
                                    </a>
                                <?php else: ?>
                                    <span class="text-xs text-emerald-500"><i class='bx bx-check-double'></i></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <?php if (!empty($conciliacao['observacoes'])): ?>
        <div class="bg-gray-50 rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500 uppercase tracking-wider font-medium mb-1">Observações</p>
            <p class="text-sm text-gray-700"><?= nl2br(htmlspecialchars($conciliacao['observacoes'])) ?></p>
        </div>
    <?php endif; ?>
</div>
