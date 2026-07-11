<?php $pageTitle = $pageTitle ?? 'Log de Atualizações'; ?>
<div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Log de Atualizações</h1>
            <p class="text-sm text-gray-500 mt-1">Auditoria de todas as alterações em regras, obrigações e legislação</p>
        </div>
        <a href="<?= BASE_URL ?>/legislacao" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200">
            <i class='bx bx-arrow-back'></i> Voltar
        </a>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <form method="GET" class="flex flex-wrap items-end gap-3">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Tipo</label>
                <select name="tipo" class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm" onchange="this.form.submit()">
                    <option value="">Todos</option>
                    <?php foreach ($tipos as $t): ?>
                        <option value="<?= $t ?>" <?= ($_GET['tipo'] ?? '') === $t ? 'selected' : '' ?>><?= ucfirst($t) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200">
                    <th class="text-left p-3 font-semibold text-gray-600">Data/Hora</th>
                    <th class="text-left p-3 font-semibold text-gray-600">Tipo</th>
                    <th class="text-left p-3 font-semibold text-gray-600">Ação</th>
                    <th class="text-left p-3 font-semibold text-gray-600">Descrição</th>
                    <th class="text-left p-3 font-semibold text-gray-600">Origem</th>
                    <th class="text-left p-3 font-semibold text-gray-600">Usuário</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($registros)): ?>
                    <tr><td colspan="6" class="p-8 text-center text-gray-400">Nenhum registro encontrado.</td></tr>
                <?php else: ?>
                    <?php foreach ($registros as $r): ?>
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="p-3 text-xs text-gray-600"><?= date('d/m/Y H:i', strtotime($r['created_at'])) ?></td>
                            <td class="p-3">
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium
                                    <?= $r['tipo'] === 'regra' ? 'bg-orange-100 text-orange-700' : '' ?>
                                    <?= $r['tipo'] === 'obrigacao' ? 'bg-emerald-100 text-emerald-700' : '' ?>
                                    <?= $r['tipo'] === 'legislacao' ? 'bg-indigo-100 text-indigo-700' : '' ?>
                                    <?= $r['tipo'] === 'relatorio' ? 'bg-purple-100 text-purple-700' : '' ?>">
                                    <?= ucfirst($r['tipo']) ?>
                                </span>
                            </td>
                            <td class="p-3 capitalize"><?= htmlspecialchars($r['acao']) ?></td>
                            <td class="p-3 text-gray-800"><?= htmlspecialchars($r['descricao']) ?></td>
                            <td class="p-3 text-xs text-gray-500 capitalize"><?= htmlspecialchars($r['origem'] ?? 'manual') ?></td>
                            <td class="p-3 text-gray-500"><?= htmlspecialchars($r['usuario_nome'] ?? 'Sistema') ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
