<?php $pageTitle = $pageTitle ?? 'Notas Fiscais'; ?>
<div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Notas Fiscais</h1>
            <p class="text-sm text-gray-500 mt-1">Controle de notas fiscais emitidas e recebidas</p>
        </div>
        <?php if (has_permission('fiscal_notas_manage')): ?>
            <a href="<?= BASE_URL ?>/notaFiscal/form" class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 text-sm">Nova Nota Fiscal</a>
        <?php endif; ?>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <form method="GET" class="flex items-end gap-4 flex-wrap">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                <select name="status" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    <option value="">Todos</option>
                    <option value="Pendente" <?= (($filtros['status'] ?? '') === 'Pendente') ? 'selected' : '' ?>>Pendente</option>
                    <option value="Autorizada" <?= (($filtros['status'] ?? '') === 'Autorizada') ? 'selected' : '' ?>>Autorizada</option>
                    <option value="Cancelada" <?= (($filtros['status'] ?? '') === 'Cancelada') ? 'selected' : '' ?>>Cancelada</option>
                    <option value="Rejeitada" <?= (($filtros['status'] ?? '') === 'Rejeitada') ? 'selected' : '' ?>>Rejeitada</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Tipo</label>
                <select name="tipo" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    <option value="">Todos</option>
                    <option value="Entrada" <?= (($filtros['tipo'] ?? '') === 'Entrada') ? 'selected' : '' ?>>Entrada</option>
                    <option value="Saida" <?= (($filtros['tipo'] ?? '') === 'Saida') ? 'selected' : '' ?>>Saída</option>
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 text-sm">Filtrar</button>
            <a href="<?= BASE_URL ?>/fiscal/notas" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 text-sm">Limpar</a>
        </form>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200">
                    <th class="text-left p-3 font-semibold text-gray-600">Número</th>
                    <th class="text-left p-3 font-semibold text-gray-600">Cliente/Fornecedor</th>
                    <th class="text-left p-3 font-semibold text-gray-600">Tipo</th>
                    <th class="text-left p-3 font-semibold text-gray-600">Emissão</th>
                    <th class="text-left p-3 font-semibold text-gray-600">Status</th>
                    <th class="text-right p-3 font-semibold text-gray-600">Valor</th>
                    <th class="text-center p-3 font-semibold text-gray-600">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($notas)): ?>
                    <tr><td colspan="7" class="p-8 text-center text-gray-400">Nenhuma nota fiscal encontrada.</td></tr>
                <?php else: ?>
                    <?php foreach ($notas as $n): ?>
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="p-3 font-medium text-gray-800"><?= htmlspecialchars($n['numero']) ?></td>
                            <td class="p-3 text-gray-600"><?= htmlspecialchars($n['cliente_fornecedor']) ?></td>
                            <td class="p-3">
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium <?= $n['tipo'] === 'Entrada' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' ?>">
                                    <?= $n['tipo'] === 'Entrada' ? 'Entrada' : 'Saída' ?>
                                </span>
                            </td>
                            <td class="p-3 text-gray-600"><?= date('d/m/Y', strtotime($n['emissao'])) ?></td>
                            <td class="p-3">
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium
                                    <?= match($n['status']) {
                                        'Autorizada' => 'bg-emerald-100 text-emerald-700',
                                        'Cancelada' => 'bg-red-100 text-red-700',
                                        'Rejeitada' => 'bg-rose-100 text-rose-700',
                                        default => 'bg-gray-100 text-gray-600',
                                    } ?>">
                                    <?= htmlspecialchars($n['status'] ?? 'Pendente') ?>
                                </span>
                            </td>
                            <td class="p-3 text-right font-medium text-gray-800">
                                R$ <?= number_format($n['valor'], 2, ',', '.') ?>
                            </td>
                            <td class="p-3 text-center">
                                <a href="<?= BASE_URL ?>/notaFiscal/detalhe/<?= $n['id'] ?>" class="text-orange-600 hover:text-orange-700 text-xs font-medium">Detalhes</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
