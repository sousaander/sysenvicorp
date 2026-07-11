<?php $pageTitle = $pageTitle ?? 'CT-e - Conhecimentos de Transporte'; ?>
<div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">CT-e</h1>
            <p class="text-sm text-gray-500 mt-1">Conhecimentos de Transporte Eletrônicos</p>
        </div>
        <?php if (has_permission('fiscal_notas_manage')): ?>
            <a href="<?= BASE_URL ?>/cte/form" class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 text-sm">Novo CT-e</a>
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
                    <option value="Encerrado" <?= (($filtros['status'] ?? '') === 'Encerrado') ? 'selected' : '' ?>>Encerrado</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Data Início</label>
                <input type="date" name="data_inicio" value="<?= htmlspecialchars($filtros['data_inicio'] ?? '') ?>" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Data Fim</label>
                <input type="date" name="data_fim" value="<?= htmlspecialchars($filtros['data_fim'] ?? '') ?>" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
            </div>
            <button type="submit" class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 text-sm">Filtrar</button>
            <a href="<?= BASE_URL ?>/cte" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 text-sm">Limpar</a>
        </form>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200">
                    <th class="text-left p-3 font-semibold text-gray-600">Número</th>
                    <th class="text-left p-3 font-semibold text-gray-600">Tomador</th>
                    <th class="text-left p-3 font-semibold text-gray-600">Modal</th>
                    <th class="text-left p-3 font-semibold text-gray-600">Emissão</th>
                    <th class="text-left p-3 font-semibold text-gray-600">Status</th>
                    <th class="text-right p-3 font-semibold text-gray-600">Valor Frete</th>
                    <th class="text-center p-3 font-semibold text-gray-600">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($ctes)): ?>
                    <tr><td colspan="7" class="p-8 text-center text-gray-400">Nenhum CT-e encontrado.</td></tr>
                <?php else: ?>
                    <?php foreach ($ctes as $c): ?>
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="p-3 font-medium text-gray-800"><?= htmlspecialchars($c['numero']) ?></td>
                            <td class="p-3 text-gray-600"><?= htmlspecialchars($c['tomador_nome']) ?></td>
                            <td class="p-3 text-gray-600"><?= htmlspecialchars(ucfirst($c['modal'] ?? 'Rodoviário')) ?></td>
                            <td class="p-3 text-gray-600"><?= date('d/m/Y', strtotime($c['data_emissao'])) ?></td>
                            <td class="p-3">
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium
                                    <?= match($c['status']) {
                                        'Autorizada' => 'bg-emerald-100 text-emerald-700',
                                        'Cancelada' => 'bg-red-100 text-red-700',
                                        'Rejeitada' => 'bg-rose-100 text-rose-700',
                                        'Encerrado' => 'bg-blue-100 text-blue-700',
                                        default => 'bg-gray-100 text-gray-600',
                                    } ?>">
                                    <?= htmlspecialchars($c['status'] ?? 'Pendente') ?>
                                </span>
                            </td>
                            <td class="p-3 text-right font-medium text-gray-800">
                                R$ <?= number_format($c['valor_frete'], 2, ',', '.') ?>
                            </td>
                            <td class="p-3 text-center">
                                <a href="<?= BASE_URL ?>/cte/detalhe/<?= $c['id'] ?>" class="text-orange-600 hover:text-orange-700 text-xs font-medium">Detalhes</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
