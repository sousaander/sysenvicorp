<?php $pageTitle = $pageTitle ?? 'Obrigações Fiscais'; ?>
<div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Obrigações Fiscais</h1>
            <p class="text-sm text-gray-500 mt-1">Cadastro de obrigações acessórias federais, estaduais e municipais</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="<?= BASE_URL ?>/obrigacoesFiscais/form" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700">
                <i class='bx bx-plus'></i> Nova Obrigação
            </a>
            <a href="<?= BASE_URL ?>/obrigacoesFiscais/calendario" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
                <i class='bx bx-calendar'></i> Calendário
            </a>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200">
                    <th class="text-left p-3 font-semibold text-gray-600">Obrigação</th>
                    <th class="text-left p-3 font-semibold text-gray-600">Órgão</th>
                    <th class="text-left p-3 font-semibold text-gray-600">Periodicidade</th>
                    <th class="text-center p-3 font-semibold text-gray-600">Dia Venc.</th>
                    <th class="text-center p-3 font-semibold text-gray-600">Pendentes</th>
                    <th class="text-center p-3 font-semibold text-gray-600">Atrasados</th>
                    <th class="text-center p-3 font-semibold text-gray-600">Ativo</th>
                    <th class="text-right p-3 font-semibold text-gray-600">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($obrigacoes)): ?>
                    <tr><td colspan="8" class="p-8 text-center text-gray-400">Nenhuma obrigação cadastrada.</td></tr>
                <?php else: ?>
                    <?php foreach ($obrigacoes as $o): ?>
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="p-3">
                                <span class="font-medium text-gray-800"><?= htmlspecialchars($o['nome']) ?></span>
                                <?php if (!empty($o['forma_entrega'])): ?>
                                    <span class="text-xs text-gray-400 block"><?= htmlspecialchars($o['forma_entrega']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="p-3">
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium
                                    <?= $o['orgao'] === 'federal' ? 'bg-blue-100 text-blue-700' : '' ?>
                                    <?= $o['orgao'] === 'estadual' ? 'bg-orange-100 text-orange-700' : '' ?>
                                    <?= $o['orgao'] === 'municipal' ? 'bg-purple-100 text-purple-700' : '' ?>
                                    <?= $o['orgao'] === 'outros' ? 'bg-gray-100 text-gray-700' : '' ?>">
                                    <?= ucfirst($o['orgao']) ?>
                                </span>
                            </td>
                            <td class="p-3 capitalize text-gray-600"><?= $o['periodicidade'] ?></td>
                            <td class="p-3 text-center font-mono"><?= str_pad($o['dia_vencimento'], 2, '0', STR_PAD_LEFT) ?></td>
                            <td class="p-3 text-center">
                                <?php if ($o['pendentes'] > 0): ?>
                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700"><?= $o['pendentes'] ?></span>
                                <?php else: ?>
                                    <span class="text-xs text-gray-400">0</span>
                                <?php endif; ?>
                            </td>
                            <td class="p-3 text-center">
                                <?php if ($o['atrasados'] > 0): ?>
                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700"><?= $o['atrasados'] ?></span>
                                <?php else: ?>
                                    <span class="text-xs text-gray-400">0</span>
                                <?php endif; ?>
                            </td>
                            <td class="p-3 text-center">
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium <?= $o['ativo'] ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500' ?>">
                                    <?= $o['ativo'] ? 'Sim' : 'Não' ?>
                                </span>
                            </td>
                            <td class="p-3 text-right space-x-1">
                                <a href="<?= BASE_URL ?>/obrigacoesFiscais/form/<?= $o['id'] ?>" class="inline-block px-2 py-1 text-xs font-medium text-blue-600 hover:bg-blue-50 rounded">
                                    <i class='bx bx-edit'></i>
                                </a>
                                <a href="<?= BASE_URL ?>/obrigacoesFiscais/excluir/<?= $o['id'] ?>"
                                   class="inline-block px-2 py-1 text-xs font-medium text-red-600 hover:bg-red-50 rounded"
                                   onclick="return confirm('Excluir obrigação?')">
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
