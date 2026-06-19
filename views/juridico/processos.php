<div class="space-y-6">
    <!-- Cabeçalho -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white tracking-tight">Processos Judiciais e Administrativos</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 font-medium">Gerenciamento completo da carteira processual.</p>
        </div>
        <?php if (has_permission('juridico_processos_manage')) : ?>
            <a href="<?= BASE_URL ?>/juridico/processos/novo" class="sys-btn-primary !bg-purple-600 hover:!bg-purple-700">
                <i class='bx bx-plus mr-2'></i> Novo Processo
            </a>
        <?php endif; ?>
    </div>

    <!-- Filtros -->
    <div class="sys-card">
        <form method="GET" action="<?= BASE_URL ?>/juridico/processos" class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
            <div class="md:col-span-6">
                <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Busca Rápida</label>
                <div class="relative">
                    <i class='bx bx-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg'></i>
                    <input type="text" name="busca" value="<?= htmlspecialchars($filtros['busca'] ?? '') ?>" 
                           placeholder="Número, parte contrária ou tribunal..." 
                           class="w-full pl-10 pr-4 py-2 bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 outline-none transition-all">
                </div>
            </div>
            <div class="md:col-span-4">
                <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Status</label>
                <select name="status" class="w-full px-4 py-2 bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-slate-700 rounded-lg text-sm outline-none focus:ring-2 focus:ring-purple-500">
                    <option value="">Todos os Status</option>
                    <option value="Ativo" <?= ($filtros['status'] ?? '') === 'Ativo' ? 'selected' : '' ?>>Ativo</option>
                    <option value="Suspenso" <?= ($filtros['status'] ?? '') === 'Suspenso' ? 'selected' : '' ?>>Suspenso</option>
                    <option value="Concluído" <?= ($filtros['status'] ?? '') === 'Concluído' ? 'selected' : '' ?>>Concluído</option>
                </select>
            </div>
            <div class="md:col-span-2 flex gap-2">
                <button type="submit" class="flex-1 py-2 bg-slate-800 text-white rounded-lg text-sm font-bold hover:bg-slate-900 transition-colors">
                    Filtrar
                </button>
                <a href="<?= BASE_URL ?>/juridico/processos" class="p-2 bg-slate-100 text-slate-500 rounded-lg hover:bg-slate-200 transition-colors" title="Limpar">
                    <i class='bx bx-refresh text-xl'></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Tabela de Processos -->
    <div class="sys-card !p-0 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="sys-table">
                <thead>
                    <tr>
                        <th class="pl-6">Nº do Processo / Tipo</th>
                        <th>Parte Contrária</th>
                        <th>Tribunal</th>
                        <th>Status</th>
                        <th>Prazo Próximo</th>
                        <th class="text-center pr-6">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($processos)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-12 text-slate-400 italic">
                                Nenhum processo encontrado com os filtros aplicados.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($processos as $proc): ?>
                            <tr class="hover:bg-slate-50 dark:hover:bg-white/5 transition-colors">
                                <td class="pl-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="font-bold text-slate-700 dark:text-slate-200"><?= htmlspecialchars($proc['numero']) ?></span>
                                        <span class="text-[10px] text-slate-400 font-bold uppercase tracking-tighter"><?= htmlspecialchars($proc['tipo']) ?></span>
                                    </div>
                                </td>
                                <td class="text-slate-600 dark:text-slate-400 font-medium">
                                    <?= htmlspecialchars($proc['parte_contraria']) ?>
                                </td>
                                <td>
                                    <span class="sys-badge sys-badge-gray"><?= htmlspecialchars($proc['tribunal']) ?></span>
                                </td>
                                <td>
                                    <?php
                                    $statusClass = 'sys-badge-gray';
                                    if ($proc['status'] === 'Ativo') $statusClass = 'sys-badge-blue !bg-purple-100 !text-purple-700';
                                    if ($proc['status'] === 'Suspenso') $statusClass = 'sys-badge-orange';
                                    if ($proc['status'] === 'Concluído') $statusClass = 'sys-badge-green';
                                    ?>
                                    <span class="sys-badge <?= $statusClass ?>"><?= $proc['status'] ?></span>
                                </td>
                                <td>
                                    <?php if ($proc['vencimento_prazo']): ?>
                                        <span class="text-xs font-bold text-red-500">
                                            <i class='bx bx-calendar-exclamation mr-1'></i>
                                            <?= date('d/m/Y', strtotime($proc['vencimento_prazo'])) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-slate-300 italic text-xs">Sem prazos</span>
                                    <?php endif; ?>
                                </td>
                                <td class="pr-6 text-center">
                                    <div class="flex justify-center gap-1">
                                        <a href="<?= BASE_URL ?>/juridico/processos/detalhe/<?= $proc['id'] ?>" class="p-1.5 text-slate-400 hover:text-purple-600 transition-colors" title="Ver Detalhes">
                                            <i class='bx bx-show text-xl'></i>
                                        </a>
                                        <?php if (has_permission('juridico_processos_manage')) : ?>
                                            <a href="<?= BASE_URL ?>/juridico/processos/editar/<?= $proc['id'] ?>" class="p-1.5 text-slate-400 hover:text-blue-600 transition-colors" title="Editar">
                                                <i class='bx bx-edit-alt text-xl'></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Paginação -->
        <?php if ($totalPaginas > 1): ?>
            <div class="p-4 bg-slate-50 dark:bg-white/5 border-t border-slate-100 dark:border-slate-700 flex justify-center gap-2">
                <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                    <a href="<?= BASE_URL ?>/juridico/processos?page=<?= $i ?>&busca=<?= urlencode($filtros['busca'] ?? '') ?>&status=<?= urlencode($filtros['status'] ?? '') ?>" 
                       class="px-3 py-1 rounded-md text-sm font-bold transition-all <?= ($i == $paginaAtual) ? 'bg-purple-600 text-white shadow-md' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-purple-50' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </div>
</div>