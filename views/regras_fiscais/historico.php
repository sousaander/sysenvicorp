<?php $pageTitle = $pageTitle ?? 'Histórico de Regras Fiscais'; ?>
<div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Histórico de Regras Fiscais</h1>
            <p class="text-sm text-gray-500 mt-1">Log de auditoria de alterações nas regras fiscais</p>
        </div>
        <a href="<?= BASE_URL ?>/regrasFiscais" class="inline-flex items-center gap-2 px-4 py-2 bg-orange-600 text-white text-sm font-medium rounded-lg hover:bg-orange-700">
            <i class='bx bx-arrow-back'></i> Voltar
        </a>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200">
                    <th class="text-left p-3 font-semibold text-gray-600">Data</th>
                    <th class="text-left p-3 font-semibold text-gray-600">Ação</th>
                    <th class="text-left p-3 font-semibold text-gray-600">Descrição</th>
                    <th class="text-left p-3 font-semibold text-gray-600">Usuário</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($log)): ?>
                    <tr><td colspan="4" class="p-8 text-center text-gray-400">Nenhum registro encontrado.</td></tr>
                <?php else: ?>
                    <?php foreach ($log as $l): ?>
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="p-3 text-gray-600"><?= date('d/m/Y H:i', strtotime($l['created_at'])) ?></td>
                            <td class="p-3">
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium
                                    <?= $l['acao'] === 'criar' ? 'bg-emerald-100 text-emerald-700' : '' ?>
                                    <?= $l['acao'] === 'atualizar' ? 'bg-blue-100 text-blue-700' : '' ?>
                                    <?= $l['acao'] === 'excluir' ? 'bg-red-100 text-red-700' : '' ?>">
                                    <?= ucfirst($l['acao']) ?>
                                </span>
                            </td>
                            <td class="p-3 text-gray-800"><?= htmlspecialchars($l['descricao']) ?></td>
                            <td class="p-3 text-gray-500"><?= htmlspecialchars($l['usuario_nome'] ?? 'Sistema') ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
