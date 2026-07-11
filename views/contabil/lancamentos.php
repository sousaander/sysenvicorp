<?php $pageTitle = $pageTitle ?? 'Lançamentos Contábeis'; ?>
<div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Lançamentos Contábeis</h1>
            <p class="text-sm text-gray-500 mt-1">Registros contábeis em partida dobrada com trilha de auditoria</p>
        </div>
        <a href="<?= BASE_URL ?>/contabil/lancamentoForm" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700 transition-colors">
            <i class='bx bx-plus'></i> Novo Lançamento
        </a>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <form method="GET" class="flex flex-wrap items-end gap-3">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Data Início</label>
                <input type="date" name="data_inicio" value="<?= htmlspecialchars($filters['data_inicio'] ?? '') ?>"
                       class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Data Fim</label>
                <input type="date" name="data_fim" value="<?= htmlspecialchars($filters['data_fim'] ?? '') ?>"
                       class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Origem</label>
                <select name="origem" class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm">
                    <option value="">Todas</option>
                    <option value="manual" <?= ($filters['origem'] ?? '') === 'manual' ? 'selected' : '' ?>>Manual</option>
                    <option value="financeiro" <?= ($filters['origem'] ?? '') === 'financeiro' ? 'selected' : '' ?>>Financeiro</option>
                    <option value="folha" <?= ($filters['origem'] ?? '') === 'folha' ? 'selected' : '' ?>>Folha</option>
                    <option value="contrato" <?= ($filters['origem'] ?? '') === 'contrato' ? 'selected' : '' ?>>Contrato</option>
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
                    <th class="text-left p-3 font-semibold text-gray-600">Descrição</th>
                    <th class="text-left p-3 font-semibold text-gray-600">Conta Débito</th>
                    <th class="text-left p-3 font-semibold text-gray-600">Conta Crédito</th>
                    <th class="text-left p-3 font-semibold text-gray-600">Origem</th>
                    <th class="text-right p-3 font-semibold text-gray-600">Valor</th>
                    <th class="text-center p-3 font-semibold text-gray-600">Status</th>
                    <th class="text-right p-3 font-semibold text-gray-600">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($lancamentos)): ?>
                    <tr><td colspan="8" class="p-8 text-center text-gray-400">Nenhum lançamento encontrado.</td></tr>
                <?php else: ?>
                    <?php foreach ($lancamentos as $l): ?>
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="p-3 text-gray-600"><?= date('d/m/Y', strtotime($l['data_lancamento'])) ?></td>
                            <td class="p-3">
                                <span class="font-medium text-gray-800"><?= htmlspecialchars($l['descricao']) ?></span>
                                <?php if (!empty($l['categoria'])): ?>
                                    <span class="text-xs text-gray-400 block"><?= htmlspecialchars($l['categoria']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="p-3 text-gray-600"><?= htmlspecialchars($l['debito_conta_nome'] ?? '-') ?></td>
                            <td class="p-3 text-gray-600"><?= htmlspecialchars($l['credito_conta_nome'] ?? '-') ?></td>
                            <td class="p-3">
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium
                                    <?= $l['origem'] === 'manual' ? 'bg-gray-100 text-gray-600' : '' ?>
                                    <?= $l['origem'] === 'financeiro' ? 'bg-blue-100 text-blue-600' : '' ?>
                                    <?= $l['origem'] === 'folha' ? 'bg-rose-100 text-rose-600' : '' ?>
                                    <?= $l['origem'] === 'contrato' ? 'bg-purple-100 text-purple-600' : '' ?>">
                                    <?= ucfirst($l['origem'] ?? 'manual') ?>
                                </span>
                            </td>
                            <td class="p-3 text-right font-medium <?= $l['tipo'] === 'credito' ? 'text-emerald-600' : 'text-rose-600' ?>">
                                R$ <?= number_format($l['valor'], 2, ',', '.') ?>
                            </td>
                            <td class="p-3 text-center">
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium <?= $l['conciliado'] ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' ?>">
                                    <?= $l['conciliado'] ? 'Conciliado' : 'Pendente' ?>
                                </span>
                            </td>
                            <td class="p-3 text-right">
                                <a href="<?= BASE_URL ?>/contabil/lancamentoForm/<?= $l['id'] ?>" class="inline-block px-2 py-1 text-xs font-medium text-blue-600 hover:bg-blue-50 rounded">
                                    <i class='bx bx-edit'></i>
                                </a>
                                <a href="<?= BASE_URL ?>/contabil/excluirLancamento/<?= $l['id'] ?>"
                                   class="inline-block px-2 py-1 text-xs font-medium text-red-600 hover:bg-red-50 rounded"
                                   onclick="return confirm('Excluir este lançamento contábil?')">
                                    <i class='bx bx-trash'></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
