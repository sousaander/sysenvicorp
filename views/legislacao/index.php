<?php $pageTitle = $pageTitle ?? 'Legislação e Versões'; ?>
<div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Legislação e Versões</h1>
            <p class="text-sm text-gray-500 mt-1">Controle de alterações legislativas com vigência e impacto fiscal</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="<?= BASE_URL ?>/legislacao/importar" class="inline-flex items-center gap-2 px-4 py-2 bg-purple-600 text-white text-sm font-medium rounded-lg hover:bg-purple-700">
                <i class='bx bx-import'></i> Importar
            </a>
            <a href="<?= BASE_URL ?>/legislacao/form" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">
                <i class='bx bx-plus'></i> Nova Versão
            </a>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <form method="GET" class="flex flex-wrap items-end gap-3">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Módulo</label>
                <select name="modulo" class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm" onchange="this.form.submit()">
                    <option value="">Todos</option>
                    <?php foreach ($modulos as $k => $v): ?>
                        <option value="<?= $k ?>" <?= $moduloAtual === $k ? 'selected' : '' ?>><?= $v ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <a href="<?= BASE_URL ?>/legislacao/proximas" class="px-4 py-1.5 bg-amber-600 text-white text-sm font-medium rounded-lg hover:bg-amber-700">
                <i class='bx bx-calendar'></i> Próximas alterações
            </a>
            <a href="<?= BASE_URL ?>/legislacao/log" class="px-4 py-1.5 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700">
                <i class='bx bx-history'></i> Log de atualizações
            </a>
        </form>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200">
                    <th class="text-left p-3 font-semibold text-gray-600">Título</th>
                    <th class="text-left p-3 font-semibold text-gray-600">Tipo</th>
                    <th class="text-left p-3 font-semibold text-gray-600">Nº Ato</th>
                    <th class="text-left p-3 font-semibold text-gray-600">Módulo</th>
                    <th class="text-center p-3 font-semibold text-gray-600">Publicação</th>
                    <th class="text-center p-3 font-semibold text-gray-600">Vigência</th>
                    <th class="text-center p-3 font-semibold text-gray-600">Revogação</th>
                    <th class="text-center p-3 font-semibold text-gray-600">Versão</th>
                    <th class="text-right p-3 font-semibold text-gray-600">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($versoes)): ?>
                    <tr><td colspan="9" class="p-8 text-center text-gray-400">Nenhuma versão legislativa cadastrada.</td></tr>
                <?php else: ?>
                    <?php foreach ($versoes as $v): ?>
                        <?php $vigente = (!$v['data_revogacao'] || $v['data_revogacao'] > date('Y-m-d')) && (!$v['data_vigencia'] || $v['data_vigencia'] <= date('Y-m-d')); ?>
                        <tr class="border-b border-gray-100 hover:bg-gray-50 <?= $v['data_revogacao'] && $v['data_revogacao'] <= date('Y-m-d') ? 'opacity-50' : '' ?>">
                            <td class="p-3">
                                <span class="font-medium text-gray-800"><?= htmlspecialchars($v['titulo']) ?></span>
                                <?php if ($vigente): ?>
                                    <span class="px-1.5 py-0.5 bg-emerald-100 text-emerald-700 text-xs font-bold rounded ml-1">VIGENTE</span>
                                <?php endif; ?>
                            </td>
                            <td class="p-3 text-xs text-gray-600"><?= htmlspecialchars($v['tipo_ato'] ?? '-') ?></td>
                            <td class="p-3 font-mono text-xs"><?= htmlspecialchars($v['numero_ato'] ?? '-') ?></td>
                            <td class="p-3">
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">
                                    <?= $modulos[$v['modulo']] ?? $v['modulo'] ?>
                                </span>
                            </td>
                            <td class="p-3 text-center text-xs"><?= $v['data_publicacao'] ? date('d/m/Y', strtotime($v['data_publicacao'])) : '-' ?></td>
                            <td class="p-3 text-center text-xs"><?= $v['data_vigencia'] ? date('d/m/Y', strtotime($v['data_vigencia'])) : '-' ?></td>
                            <td class="p-3 text-center text-xs"><?= $v['data_revogacao'] ? date('d/m/Y', strtotime($v['data_revogacao'])) : '-' ?></td>
                            <td class="p-3 text-center font-mono text-xs"><?= htmlspecialchars($v['versao'] ?? '-') ?></td>
                            <td class="p-3 text-right space-x-1">
                                <a href="<?= BASE_URL ?>/legislacao/form/<?= $v['id'] ?>" class="inline-block px-2 py-1 text-xs font-medium text-blue-600 hover:bg-blue-50 rounded">
                                    <i class='bx bx-edit'></i>
                                </a>
                                <a href="<?= BASE_URL ?>/legislacao/excluir/<?= $v['id'] ?>"
                                   class="inline-block px-2 py-1 text-xs font-medium text-red-600 hover:bg-red-50 rounded"
                                   onclick="return confirm('Excluir versão?')">
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
