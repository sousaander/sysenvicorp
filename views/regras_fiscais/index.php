<?php $pageTitle = $pageTitle ?? 'Regras Fiscais'; ?>
<div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Regras Fiscais</h1>
            <p class="text-sm text-gray-500 mt-1">Parametrização de tributação por produto, serviço e regime tributário</p>
        </div>
        <a href="<?= BASE_URL ?>/regrasFiscais/form" class="inline-flex items-center gap-2 px-4 py-2 bg-orange-600 text-white text-sm font-medium rounded-lg hover:bg-orange-700 transition-colors">
            <i class='bx bx-plus'></i> Nova Regra Fiscal
        </a>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200">
                    <th class="text-left p-3 font-semibold text-gray-600">Produto/Serviço</th>
                    <th class="text-left p-3 font-semibold text-gray-600">Regime</th>
                    <th class="text-left p-3 font-semibold text-gray-600">CFOP</th>
                    <th class="text-center p-3 font-semibold text-gray-600">CST ICMS</th>
                    <th class="text-right p-3 font-semibold text-gray-600">ICMS %</th>
                    <th class="text-right p-3 font-semibold text-gray-600">PIS %</th>
                    <th class="text-right p-3 font-semibold text-gray-600">COFINS %</th>
                    <th class="text-center p-3 font-semibold text-gray-600">Vigência</th>
                    <th class="text-center p-3 font-semibold text-gray-600">Ativo</th>
                    <th class="text-right p-3 font-semibold text-gray-600">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($regras)): ?>
                    <tr><td colspan="10" class="p-8 text-center text-gray-400">Nenhuma regra fiscal cadastrada.</td></tr>
                <?php else: ?>
                    <?php foreach ($regras as $r): ?>
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="p-3">
                                <span class="font-medium text-gray-800"><?= htmlspecialchars($r['produto_nome'] ?? 'Todos os produtos') ?></span>
                                <?php if ($r['produto_id']): ?>
                                    <span class="text-xs text-gray-400 block"><?= htmlspecialchars($r['produto_codigo'] ?? '') ?></span>
                                <?php else: ?>
                                    <span class="text-xs text-orange-500">Regra geral</span>
                                <?php endif; ?>
                            </td>
                            <td class="p-3 text-xs">
                                <span class="capitalize"><?= str_replace('_', ' ', $r['regime_tributario'] ?? 'N/A') ?></span>
                            </td>
                            <td class="p-3 font-mono text-xs"><?= htmlspecialchars($r['cfop'] ?? '-') ?></td>
                            <td class="p-3 text-center font-mono text-xs"><?= htmlspecialchars($r['cst_icms'] ?? ($r['csosn'] ?? '-')) ?></td>
                            <td class="p-3 text-right font-medium"><?= number_format($r['aliquota_icms'], 2) ?>%</td>
                            <td class="p-3 text-right"><?= number_format($r['aliquota_pis'], 2) ?>%</td>
                            <td class="p-3 text-right"><?= number_format($r['aliquota_cofins'], 2) ?>%</td>
                            <td class="p-3 text-center text-xs text-gray-500">
                                <?php if ($r['data_vigencia_inicio']): ?>
                                    <?= date('d/m/Y', strtotime($r['data_vigencia_inicio'])) ?>
                                <?php else: ?>
                                    Sem prazo
                                <?php endif; ?>
                            </td>
                            <td class="p-3 text-center">
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium <?= $r['ativo'] ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500' ?>">
                                    <?= $r['ativo'] ? 'Sim' : 'Não' ?>
                                </span>
                            </td>
                            <td class="p-3 text-right space-x-1">
                                <a href="<?= BASE_URL ?>/regrasFiscais/form/<?= $r['id'] ?>" class="inline-block px-2 py-1 text-xs font-medium text-blue-600 hover:bg-blue-50 rounded">
                                    <i class='bx bx-edit'></i>
                                </a>
                                <a href="<?= BASE_URL ?>/regrasFiscais/excluir/<?= $r['id'] ?>"
                                   class="inline-block px-2 py-1 text-xs font-medium text-red-600 hover:bg-red-50 rounded"
                                   onclick="return confirm('Excluir regra fiscal?')">
                                    <i class='bx bx-trash'></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="flex items-center gap-4">
        <a href="<?= BASE_URL ?>/regrasFiscais/historico" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700">
            <i class='bx bx-history'></i> Ver histórico de alterações
        </a>
    </div>
</div>
