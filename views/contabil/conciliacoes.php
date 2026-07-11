<?php $pageTitle = $pageTitle ?? 'Conciliação Bancária'; ?>
<div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Conciliação Bancária</h1>
            <p class="text-sm text-gray-500 mt-1">Integração com extratos e validação de lançamentos</p>
        </div>
        <a href="<?= BASE_URL ?>/contabil/conciliacaoForm" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700 transition-colors">
            <i class='bx bx-plus'></i> Nova Conciliação
        </a>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <?php if (empty($conciliacoes)): ?>
            <div class="p-8 text-center">
                <i class='bx bx-check-shield text-4xl text-gray-300 mb-3'></i>
                <p class="text-gray-500">Nenhuma conciliação bancária registrada.</p>
                <a href="<?= BASE_URL ?>/contabil/conciliacaoForm" class="inline-block mt-3 text-sm text-emerald-600 hover:text-emerald-700 font-medium">Iniciar conciliação</a>
            </div>
        <?php else: ?>
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <th class="text-left p-3 font-semibold text-gray-600">Banco</th>
                        <th class="text-left p-3 font-semibold text-gray-600">Período</th>
                        <th class="text-right p-3 font-semibold text-gray-600">Saldo Extrato</th>
                        <th class="text-right p-3 font-semibold text-gray-600">Saldo Sistema</th>
                        <th class="text-right p-3 font-semibold text-gray-600">Diferença</th>
                        <th class="text-left p-3 font-semibold text-gray-600">Status</th>
                        <th class="text-right p-3 font-semibold text-gray-600">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($conciliacoes as $c): ?>
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="p-3 font-medium text-gray-800"><?= htmlspecialchars($c['banco_nome'] ?? 'Banco #' . $c['banco_id']) ?></td>
                            <td class="p-3 text-gray-600">
                                <?= date('d/m/Y', strtotime($c['periodo_inicio'])) ?> - <?= date('d/m/Y', strtotime($c['periodo_fim'])) ?>
                            </td>
                            <td class="p-3 text-right font-mono text-gray-800">R$ <?= number_format($c['saldo_extrato'], 2, ',', '.') ?></td>
                            <td class="p-3 text-right font-mono text-gray-800">R$ <?= number_format($c['saldo_sistema'], 2, ',', '.') ?></td>
                            <td class="p-3 text-right font-mono <?= abs($c['diferenca']) < 0.01 ? 'text-emerald-600' : 'text-red-600' ?>">
                                R$ <?= number_format($c['diferenca'], 2, ',', '.') ?>
                            </td>
                            <td class="p-3">
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium
                                    <?= $c['status'] === 'conciliada' ? 'bg-emerald-100 text-emerald-700' : '' ?>
                                    <?= $c['status'] === 'aberta' ? 'bg-amber-100 text-amber-700' : '' ?>
                                    <?= $c['status'] === 'divergente' ? 'bg-red-100 text-red-700' : '' ?>">
                                    <?= ucfirst($c['status']) ?>
                                </span>
                                <?php if (!empty($c['usuario_nome'])): ?>
                                    <span class="text-xs text-gray-400 block mt-1">por <?= htmlspecialchars($c['usuario_nome']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="p-3 text-right">
                                <a href="<?= BASE_URL ?>/contabil/verConciliacao/<?= $c['id'] ?>" class="inline-block px-3 py-1 text-xs font-medium text-blue-600 hover:bg-blue-50 rounded-lg">
                                    <i class='bx bx-show'></i> Ver
                                </a>
                                <?php if ($c['status'] === 'aberta'): ?>
                                    <a href="<?= BASE_URL ?>/contabil/finalizarConciliacao/<?= $c['id'] ?>"
                                       class="inline-block px-3 py-1 text-xs font-medium text-emerald-600 hover:bg-emerald-50 rounded-lg"
                                       onclick="return confirm('Finalizar conciliação?')">
                                        <i class='bx bx-check'></i> Finalizar
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
