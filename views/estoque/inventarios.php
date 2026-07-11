<?php $pageTitle = $pageTitle ?? 'Inventário Físico'; ?>
<div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Inventário Físico</h1>
            <p class="text-sm text-gray-500 mt-1">Controle de inventários periódicos e ajustes de saldo</p>
        </div>
        <a href="<?= BASE_URL ?>/estoque/novoInventario" class="inline-flex items-center gap-2 px-4 py-2 bg-purple-600 text-white text-sm font-medium rounded-lg hover:bg-purple-700 transition-colors">
            <i class='bx bx-plus'></i> Novo Inventário
        </a>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200">
                    <th class="text-left p-3 font-semibold text-gray-600">#</th>
                    <th class="text-left p-3 font-semibold text-gray-600">Data Inventário</th>
                    <th class="text-left p-3 font-semibold text-gray-600">Tipo</th>
                    <th class="text-center p-3 font-semibold text-gray-600">Status</th>
                    <th class="text-right p-3 font-semibold text-gray-600">Itens</th>
                    <th class="text-right p-3 font-semibold text-gray-600">Divergências</th>
                    <th class="text-left p-3 font-semibold text-gray-600">Responsável</th>
                    <th class="text-right p-3 font-semibold text-gray-600">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($inventarios)): ?>
                    <tr><td colspan="8" class="p-8 text-center text-gray-400">Nenhum inventário encontrado.</td></tr>
                <?php else: ?>
                    <?php foreach ($inventarios as $inv): ?>
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="p-3 font-mono text-xs text-gray-600"><?= $inv['id'] ?></td>
                            <td class="p-3 text-gray-600"><?= date('d/m/Y', strtotime($inv['data_inventario'])) ?></td>
                            <td class="p-3">
                                <span class="capitalize"><?= htmlspecialchars($inv['tipo']) ?></span>
                            </td>
                            <td class="p-3 text-center">
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium
                                    <?= $inv['status'] === 'aberto' ? 'bg-amber-100 text-amber-700' : '' ?>
                                    <?= $inv['status'] === 'contagem' ? 'bg-blue-100 text-blue-700' : '' ?>
                                    <?= $inv['status'] === 'finalizado' ? 'bg-emerald-100 text-emerald-700' : '' ?>">
                                    <?= $inv['status'] === 'aberto' ? 'Aberto' : ($inv['status'] === 'contagem' ? 'Em Contagem' : 'Finalizado') ?>
                                </span>
                            </td>
                            <td class="p-3 text-right text-gray-600"><?= $inv['total_itens'] ?? 0 ?></td>
                            <td class="p-3 text-right">
                                <?php if (($inv['divergencias'] ?? 0) > 0): ?>
                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">
                                        <?= $inv['divergencias'] ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-xs text-gray-400">0</span>
                                <?php endif; ?>
                            </td>
                            <td class="p-3 text-gray-500 text-xs"><?= htmlspecialchars($inv['responsavel_nome'] ?? '-') ?></td>
                            <td class="p-3 text-right">
                                <a href="<?= BASE_URL ?>/estoque/verInventario/<?= $inv['id'] ?>" class="inline-block px-3 py-1 text-xs font-medium text-blue-600 hover:bg-blue-50 rounded">
                                    <i class='bx bx-show'></i> Ver
                                </a>
                                <?php if ($inv['status'] !== 'finalizado'): ?>
                                    <a href="<?= BASE_URL ?>/estoque/finalizarInventario/<?= $inv['id'] ?>"
                                       class="inline-block px-3 py-1 text-xs font-medium text-emerald-600 hover:bg-emerald-50 rounded"
                                       onclick="return confirm('Finalizar inventário? Ajustes de saldo serão aplicados automaticamente.')">
                                        <i class='bx bx-check-double'></i> Finalizar
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
