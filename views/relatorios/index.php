<?php $pageTitle = $pageTitle ?? 'Modelos de Relatórios'; ?>
<div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Modelos de Relatórios</h1>
            <p class="text-sm text-gray-500 mt-1">Relatórios personalizados para diferentes auditorias e finalidades</p>
        </div>
        <a href="<?= BASE_URL ?>/relatorios/form" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors">
            <i class='bx bx-plus'></i> Novo Modelo
        </a>
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
        </form>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <?php if (empty($modelos)): ?>
            <div class="col-span-full bg-white rounded-xl border border-gray-200 p-8 text-center text-gray-400">
                Nenhum modelo encontrado.
            </div>
        <?php else: ?>
            <?php foreach ($modelos as $m): ?>
                <div class="bg-white rounded-xl border border-gray-200 p-5 hover:shadow-sm transition-shadow">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-10 h-10 rounded-lg flex items-center justify-center
                            <?= $m['modulo'] === 'contabil' ? 'bg-emerald-100 text-emerald-600' : '' ?>
                            <?= $m['modulo'] === 'fiscal' ? 'bg-orange-100 text-orange-600' : '' ?>
                            <?= $m['modulo'] === 'estoque' ? 'bg-blue-100 text-blue-600' : '' ?>
                            <?= $m['modulo'] === 'financeiro' ? 'bg-amber-100 text-amber-600' : '' ?>
                            <?= $m['modulo'] === 'rh' ? 'bg-rose-100 text-rose-600' : '' ?>">
                            <i class='bx bx-file'></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-800"><?= htmlspecialchars($m['nome']) ?></h3>
                            <span class="text-xs text-gray-400"><?= $modulos[$m['modulo']] ?? $m['modulo'] ?></span>
                        </div>
                    </div>
                    <?php if (!empty($m['descricao'])): ?>
                        <p class="text-xs text-gray-500 mb-3"><?= htmlspecialchars($m['descricao']) ?></p>
                    <?php endif; ?>
                    <div class="flex items-center justify-between pt-3 border-t border-gray-100">
                        <span class="text-xs text-gray-400">
                            <i class='bx bx-file'></i> <?= strtoupper($m['formato_padrao']) ?>
                            &middot; <?= $m['orientacao'] ?>
                        </span>
                        <div class="flex gap-2">
                            <a href="<?= BASE_URL ?>/relatorios/preview/<?= $m['id'] ?>" class="text-xs text-blue-600 hover:underline">
                                <i class='bx bx-show'></i> Preview
                            </a>
                            <a href="<?= BASE_URL ?>/relatorios/form/<?= $m['id'] ?>" class="text-xs text-gray-600 hover:underline">
                                <i class='bx bx-edit'></i>
                            </a>
                            <a href="<?= BASE_URL ?>/relatorios/excluir/<?= $m['id'] ?>"
                               class="text-xs text-red-600 hover:underline"
                               onclick="return confirm('Excluir modelo?')">
                                <i class='bx bx-trash'></i>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
