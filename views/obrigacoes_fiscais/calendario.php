<?php $pageTitle = $pageTitle ?? 'Calendário Fiscal ' . $ano; ?>
<div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Calendário Fiscal <?= $ano ?></h1>
            <p class="text-sm text-gray-500 mt-1">Acompanhamento de prazos de obrigações acessórias</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="<?= BASE_URL ?>/obrigacoesFiscais/dashboard" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200">
                <i class='bx bxs-dashboard'></i> Dashboard
            </a>
            <form method="POST" action="<?= BASE_URL ?>/obrigacoesFiscais/gerarCalendario" class="inline">
                <input type="hidden" name="ano" value="<?= $ano ?>">
                <button type="submit" class="px-4 py-2 bg-purple-600 text-white text-sm font-medium rounded-lg hover:bg-purple-700"
                        onclick="return confirm('Gerar calendário para <?= $ano ?>?')">
                    <i class='bx bx-refresh'></i> Gerar Períodos
                </button>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
            <div class="text-lg font-bold text-gray-800"><?= count($calendario) ?></div>
            <div class="text-xs text-gray-500">Total de Períodos</div>
        </div>
        <div class="bg-amber-50 rounded-xl border border-amber-200 p-4 text-center">
            <div class="text-lg font-bold text-amber-600"><?= $resumo['pendentes'] ?? 0 ?></div>
            <div class="text-xs text-gray-500">Pendentes</div>
        </div>
        <div class="bg-emerald-50 rounded-xl border border-emerald-200 p-4 text-center">
            <div class="text-lg font-bold text-emerald-600"><?= $resumo['entregues'] ?? 0 ?></div>
            <div class="text-xs text-gray-500">Entregues</div>
        </div>
        <div class="bg-red-50 rounded-xl border border-red-200 p-4 text-center">
            <div class="text-lg font-bold text-red-600"><?= $resumo['atrasados'] ?? 0 ?></div>
            <div class="text-xs text-gray-500">Atrasados</div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <form method="GET" class="flex flex-wrap items-end gap-3">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Ano</label>
                <select name="ano" class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm">
                    <?php for ($a = date('Y') - 1; $a <= date('Y') + 2; $a++): ?>
                        <option value="<?= $a ?>" <?= $ano == $a ? 'selected' : '' ?>><?= $a ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Mês</label>
                <select name="mes" class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm">
                    <option value="">Todos</option>
                    <?php for ($m = 1; $m <= 12; $m++): ?>
                        <option value="<?= $m ?>" <?= $mes == $m ? 'selected' : '' ?>><?= str_pad($m, 2, '0', STR_PAD_LEFT) ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Órgão</label>
                <select name="orgao" class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm">
                    <option value="">Todos</option>
                    <option value="federal" <?= $orgao === 'federal' ? 'selected' : '' ?>>Federal</option>
                    <option value="estadual" <?= $orgao === 'estadual' ? 'selected' : '' ?>>Estadual</option>
                    <option value="municipal" <?= $orgao === 'municipal' ? 'selected' : '' ?>>Municipal</option>
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
                    <th class="text-left p-3 font-semibold text-gray-600">Obrigação</th>
                    <th class="text-center p-3 font-semibold text-gray-600">Mês/Ano</th>
                    <th class="text-center p-3 font-semibold text-gray-600">Orgão</th>
                    <th class="text-center p-3 font-semibold text-gray-600">Vencimento</th>
                    <th class="text-center p-3 font-semibold text-gray-600">Entrega</th>
                    <th class="text-center p-3 font-semibold text-gray-600">Status</th>
                    <th class="text-left p-3 font-semibold text-gray-600">Obs.</th>
                    <th class="text-right p-3 font-semibold text-gray-600">Ação</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($calendario)): ?>
                    <tr><td colspan="8" class="p-8 text-center text-gray-400">Nenhum período encontrado. Clique em "Gerar Períodos".</td></tr>
                <?php else: ?>
                    <?php foreach ($calendario as $c): ?>
                        <?php $vencido = strtotime($c['data_vencimento']) < time() && $c['status'] !== 'entregue'; ?>
                        <tr class="border-b border-gray-100 hover:bg-gray-50 <?= $vencido ? 'bg-red-50' : '' ?>">
                            <td class="p-3 font-medium text-gray-800"><?= htmlspecialchars($c['obrigacao_nome']) ?></td>
                            <td class="p-3 text-center font-mono text-xs"><?= str_pad($c['mes'], 2, '0', STR_PAD_LEFT) ?>/<?= $c['ano'] ?></td>
                            <td class="p-3 text-center">
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium
                                    <?= $c['orgao'] === 'federal' ? 'bg-blue-100 text-blue-700' : '' ?>
                                    <?= $c['orgao'] === 'estadual' ? 'bg-orange-100 text-orange-700' : '' ?>
                                    <?= $c['orgao'] === 'municipal' ? 'bg-purple-100 text-purple-700' : '' ?>">
                                    <?= ucfirst($c['orgao']) ?>
                                </span>
                            </td>
                            <td class="p-3 text-center font-mono <?= $vencido ? 'text-red-600 font-bold' : 'text-gray-600' ?>">
                                <?= date('d/m/Y', strtotime($c['data_vencimento'])) ?>
                            </td>
                            <td class="p-3 text-center text-gray-500">
                                <?= $c['data_entrega'] ? date('d/m/Y', strtotime($c['data_entrega'])) : '-' ?>
                            </td>
                            <td class="p-3 text-center">
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium
                                    <?= $c['status'] === 'pendente' ? 'bg-amber-100 text-amber-700' : '' ?>
                                    <?= $c['status'] === 'entregue' ? 'bg-emerald-100 text-emerald-700' : '' ?>
                                    <?= $c['status'] === 'atrasado' ? 'bg-red-100 text-red-700' : '' ?>
                                    <?= $c['status'] === 'dispensado' ? 'bg-gray-100 text-gray-600' : '' ?>">
                                    <?= ucfirst($c['status']) ?>
                                </span>
                            </td>
                            <td class="p-3 text-xs text-gray-500 max-w-[150px] truncate"><?= htmlspecialchars($c['observacoes'] ?? '-') ?></td>
                            <td class="p-3 text-right">
                                <form method="POST" action="<?= BASE_URL ?>/obrigacoesFiscais/atualizarStatus" class="inline-flex items-center gap-1">
                                    <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                    <select name="status" class="rounded border border-gray-300 px-1 py-0.5 text-xs"
                                            onchange="this.form.submit()">
                                        <option value="pendente" <?= $c['status'] === 'pendente' ? 'selected' : '' ?>>Pendente</option>
                                        <option value="entregue" <?= $c['status'] === 'entregue' ? 'selected' : '' ?>>Entregue</option>
                                        <option value="atrasado" <?= $c['status'] === 'atrasado' ? 'selected' : '' ?>>Atrasado</option>
                                        <option value="dispensado" <?= $c['status'] === 'dispensado' ? 'selected' : '' ?>>Dispensado</option>
                                    </select>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
