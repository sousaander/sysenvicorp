<?php $pageTitle = $pageTitle ?? 'Plano de Contas'; ?>
<div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Plano de Contas</h1>
            <p class="text-sm text-gray-500 mt-1">Estrutura personalizável aderente às normas brasileiras (CPC)</p>
        </div>
        <a href="<?= BASE_URL ?>/contabil/planocontaForm" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700 transition-colors">
            <i class='bx bx-plus'></i> Nova Conta
        </a>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <?php if (empty($contas)): ?>
            <div class="p-8 text-center">
                <i class='bx bx-book text-4xl text-gray-300 mb-3'></i>
                <p class="text-gray-500">Nenhuma conta contábil cadastrada.</p>
                <a href="<?= BASE_URL ?>/contabil/planocontaForm" class="inline-block mt-3 text-sm text-emerald-600 hover:text-emerald-700 font-medium">Criar primeira conta</a>
            </div>
        <?php else: ?>
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <th class="text-left p-3 font-semibold text-gray-600">Código</th>
                        <th class="text-left p-3 font-semibold text-gray-600">Nome</th>
                        <th class="text-left p-3 font-semibold text-gray-600">Tipo</th>
                        <th class="text-left p-3 font-semibold text-gray-600">Natureza</th>
                        <th class="text-left p-3 font-semibold text-gray-600">Status</th>
                        <th class="text-right p-3 font-semibold text-gray-600">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($contas as $c): ?>
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="p-3 text-gray-600 font-mono" style="padding-left: <?= (($c['nivel'] ?? 0) * 20) + 12 ?>px">
                                <?php if (($c['nivel'] ?? 0) > 0): ?>
                                    <span class="text-gray-300 mr-1"><?= str_repeat('—', $c['nivel'] ?? 0) ?></span>
                                <?php endif; ?>
                                <?= htmlspecialchars($c['codigo']) ?>
                            </td>
                            <td class="p-3">
                                <span class="font-medium text-gray-800"><?= htmlspecialchars($c['nome']) ?></span>
                                <?php if (!empty($c['conta_pai_nome'])): ?>
                                    <span class="text-xs text-gray-400 block"><?= htmlspecialchars($c['conta_pai_nome']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="p-3">
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium <?= $c['tipo'] === 'sintetico' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700' ?>">
                                    <?= $c['tipo'] === 'sintetico' ? 'Sintética' : 'Analítica' ?>
                                </span>
                            </td>
                            <td class="p-3 text-gray-600"><?= $c['natureza'] === 'devedora' ? 'Devedora' : 'Credora' ?></td>
                            <td class="p-3">
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium <?= $c['ativo'] ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500' ?>">
                                    <?= $c['ativo'] ? 'Ativo' : 'Inativo' ?>
                                </span>
                            </td>
                            <td class="p-3 text-right">
                                <a href="<?= BASE_URL ?>/contabil/planocontaForm/<?= $c['id'] ?>" class="inline-block px-3 py-1 text-xs font-medium text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">
                                    <i class='bx bx-edit'></i>
                                </a>
                                <a href="<?= BASE_URL ?>/contabil/excluirPlanoConta/<?= $c['id'] ?>"
                                   class="inline-block px-3 py-1 text-xs font-medium text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                   onclick="return confirm('Excluir esta conta contábil?')">
                                    <i class='bx bx-trash'></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
