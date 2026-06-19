<?php
$statusAtivo = $_GET['status'] ?? '';
$buscaAtiva = $_GET['search'] ?? '';

// Helper para construir URLs mantendo os filtros atuais
$buildUrl = function($page = null, $status = null, $search = null) use ($statusAtivo, $buscaAtiva, $paginaAtual) {
    $params = $_GET;
    if ($page !== null) $params['page'] = $page;
    if ($status !== null) {
        if ($status === '') unset($params['status']);
        else $params['status'] = $status;
    }
    if ($search !== null) {
        if ($search === '') unset($params['search']);
        else $params['search'] = $search;
    }
    return BASE_URL . '/treinamentos?' . http_build_query($params);
};
?>

<style>
.trein-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 1.5rem; }
.trein-stat-card { background: #fff; border: 0.5px solid #e5e7eb; border-radius: 12px; padding: 1rem 1.25rem; }
.trein-stat-label { font-size: 11px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 6px; display: flex; align-items: center; gap: 6px; }
.trein-stat-value { font-size: 22px; font-weight: 600; color: #111827; }
.trein-dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; }
.trein-progress { height: 4px; border-radius: 2px; background: #e5e7eb; overflow: hidden; margin-top: 6px; }
.trein-progress-fill { height: 100%; border-radius: 2px; }
.trein-badge { display: inline-flex; align-items: center; gap: 5px; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; }
.trein-badge-agendado { background: #dbeafe; color: #1e40af; }
.trein-badge-realizado { background: #dcfce7; color: #166534; }
.trein-badge-cancelado { background: #fee2e2; color: #991b1b; }
.trein-avatar-group { display: flex; }
.trein-avatar { width: 24px; height: 24px; border-radius: 50%; border: 2px solid #fff; background: #bfdbfe; color: #1e40af; font-size: 9px; font-weight: 600; display: flex; align-items: center; justify-content: center; margin-left: -6px; }
.trein-avatar:first-child { margin-left: 0; }
.trein-avatar-more { background: #f3f4f6; color: #6b7280; }
.trein-filter-row { display: flex; gap: 6px; }
.trein-filter-btn { padding: 4px 12px; border-radius: 20px; font-size: 12px; cursor: pointer; border: 1px solid #d1d5db; background: transparent; color: #6b7280; transition: all 0.15s; }
.trein-filter-btn.active, .trein-filter-btn:hover { background: #dbeafe; color: #1e40af; border-color: #93c5fd; }
.trein-table-name { font-weight: 600; color: #111827; font-size: 14px; }
.trein-table-desc { font-size: 12px; color: #9ca3af; margin-top: 2px; }
@media (max-width: 768px) {
  .trein-stats { grid-template-columns: repeat(2, 1fr); }
}
</style>

<div class="flex justify-between items-start mb-6">
    <div>
        <h2 class="text-2xl font-bold flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-sky-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 3.741-1.342m-7.482 0c.634.074 1.26.168 1.741.437" /></svg>
            <?php echo htmlspecialchars($pageTitle); ?>
        </h2>
        <p class="text-gray-500 text-sm mt-1">Gerencie os treinamentos e capacitações da equipe.</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="<?php echo BASE_URL; ?>/rh" class="px-4 py-2 text-sm font-semibold text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition flex items-center gap-1">
            &larr; Voltar para RH
        </a>
        <a href="<?php echo BASE_URL; ?>/treinamentos/novo" class="px-4 py-2 text-sm font-semibold text-white bg-sky-600 rounded-lg shadow hover:bg-sky-700 transition flex items-center gap-1">
            + Novo Treinamento
        </a>
    </div>
</div>

<!-- Cards de resumo -->
<div class="trein-stats">
    <div class="trein-stat-card">
        <div class="trein-stat-label"><span class="trein-dot" style="background:#6b7280"></span> Total</div>
        <div class="trein-stat-value"><?php echo $totalTreinamentos ?? 0; ?></div>
        <div style="font-size:11px;color:#9ca3af;margin-top:2px">treinamentos cadastrados</div>
    </div>
    <div class="trein-stat-card">
        <div class="trein-stat-label"><span class="trein-dot" style="background:#3b82f6"></span> Agendados</div>
        <div class="trein-stat-value"><?php echo $totalAgendados ?? 0; ?></div>
        <div class="trein-progress"><div class="trein-progress-fill" style="width:<?php echo $totalTreinamentos > 0 ? round(($totalAgendados/$totalTreinamentos)*100) : 0; ?>%;background:#3b82f6"></div></div>
    </div>
    <div class="trein-stat-card">
        <div class="trein-stat-label"><span class="trein-dot" style="background:#22c55e"></span> Realizados</div>
        <div class="trein-stat-value"><?php echo $totalRealizados ?? 0; ?></div>
        <div class="trein-progress"><div class="trein-progress-fill" style="width:<?php echo $totalTreinamentos > 0 ? round(($totalRealizados/$totalTreinamentos)*100) : 0; ?>%;background:#22c55e"></div></div>
    </div>
    <div class="trein-stat-card">
        <div class="trein-stat-label"><span class="trein-dot" style="background:#ef4444"></span> Cancelados</div>
        <div class="trein-stat-value"><?php echo $totalCancelados ?? 0; ?></div>
        <div class="trein-progress"><div class="trein-progress-fill" style="width:<?php echo $totalTreinamentos > 0 ? round(($totalCancelados/$totalTreinamentos)*100) : 0; ?>%;background:#ef4444"></div></div>
    </div>
</div>

<!-- Tabela -->
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
        <div class="flex items-center gap-4 flex-1">
            <h3 class="text-sm font-semibold text-gray-700 whitespace-nowrap">Lista de Treinamentos</h3>
            <form action="<?php echo BASE_URL; ?>/treinamentos" method="GET" class="relative max-w-xs w-full">
                <?php if ($statusAtivo): ?>
                    <input type="hidden" name="status" value="<?php echo htmlspecialchars($statusAtivo); ?>">
                <?php endif; ?>
                <input type="text" name="search" value="<?php echo htmlspecialchars($buscaAtiva); ?>" 
                       placeholder="Buscar por nome ou instrutor..." 
                       class="w-full pl-4 pr-12 py-1.5 text-xs border border-gray-200 rounded-full bg-gray-50/50 focus:bg-white focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none transition-all">
                <button type="submit" class="absolute right-2 top-1/2 -translate-y-1/2 w-8 h-8 flex items-center justify-center text-gray-400 hover:text-sky-600 hover:bg-sky-50 rounded-full transition-all focus:outline-none" title="Pesquisar">
                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </button>
            </form>
        </div>
        <div class="trein-filter-row">
            <a href="<?php echo $buildUrl(1, ''); ?>" class="trein-filter-btn <?php echo $statusAtivo === '' ? 'active' : ''; ?>">Todos</a>
            <a href="<?php echo $buildUrl(1, 'Agendado'); ?>" class="trein-filter-btn <?php echo $statusAtivo === 'Agendado' ? 'active' : ''; ?>">Agendados</a>
            <a href="<?php echo $buildUrl(1, 'Realizado'); ?>" class="trein-filter-btn <?php echo $statusAtivo === 'Realizado' ? 'active' : ''; ?>">Realizados</a>
            <a href="<?php echo $buildUrl(1, 'Cancelado'); ?>" class="trein-filter-btn <?php echo $statusAtivo === 'Cancelado' ? 'active' : ''; ?>">Cancelados</a>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-100">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Treinamento</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Data Prevista</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Instrutor</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Local</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Participantes</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Status</th>
                    <th class="px-5 py-3 text-right text-xs font-semibold text-gray-400 uppercase tracking-wider">Ações</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-50" id="trein-tbody">
                <?php if (!empty($treinamentos)) : ?>
                    <?php foreach ($treinamentos as $item) :
                        $badgeClass = match($item['status']) {
                            'Realizado' => 'trein-badge-realizado',
                            'Cancelado' => 'trein-badge-cancelado',
                            default     => 'trein-badge-agendado',
                        };
                        $participantes = $item['participantes'] ?? [];
                        $totalPart = count($participantes);
                        $visiveis = array_slice($participantes, 0, 3);
                        $extras = $totalPart - 3;
                    ?>
                    <tr class="hover:bg-gray-50 transition trein-row" data-status="<?php echo htmlspecialchars($item['status']); ?>">
                        <td class="px-5 py-4">
                            <div class="trein-table-name"><?php echo htmlspecialchars($item['nome_treinamento']); ?></div>
                            <?php if (!empty($item['descricao'])) : ?>
                                <div class="trein-table-desc"><?php echo htmlspecialchars(mb_strimwidth($item['descricao'], 0, 60, '…')); ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="px-5 py-4 whitespace-nowrap text-sm text-gray-600"><?php echo date('d/m/Y H:i', strtotime($item['data_prevista'])); ?></td>
                        <td class="px-5 py-4 whitespace-nowrap text-sm text-gray-600"><?php echo htmlspecialchars($item['instrutor'] ?? '—'); ?></td>
                        <td class="px-5 py-4 text-sm text-gray-600 max-w-xs truncate" title="<?php echo htmlspecialchars($item['local'] ?? ''); ?>"><?php echo htmlspecialchars($item['local'] ?? '—'); ?></td>
                        <td class="px-5 py-4 whitespace-nowrap">
                            <?php if ($totalPart > 0) : ?>
                                <div class="trein-avatar-group">
                                    <?php foreach ($visiveis as $p) : ?>
                                        <div class="trein-avatar" title="<?php echo htmlspecialchars($p['nome']); ?>">
                                            <?php echo strtoupper(mb_substr($p['nome'], 0, 2)); ?>
                                        </div>
                                    <?php endforeach; ?>
                                    <?php if ($extras > 0) : ?>
                                        <div class="trein-avatar trein-avatar-more">+<?php echo $extras; ?></div>
                                    <?php endif; ?>
                                </div>
                            <?php else : ?>
                                <span class="text-xs text-gray-400">Sem participantes</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-5 py-4 whitespace-nowrap">
                            <span class="trein-badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($item['status']); ?></span>
                        </td>
                        <td class="px-5 py-4 whitespace-nowrap text-right">
                            <a href="<?php echo BASE_URL; ?>/treinamentos/listaPresenca/<?php echo $item['id']; ?>" class="inline-flex text-emerald-600 hover:text-emerald-800 transition-colors mr-3 align-middle" target="_blank" title="Gerar Lista de Presença">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                            </a>
                            <a href="<?php echo BASE_URL; ?>/treinamentos/editar/<?php echo $item['id']; ?>" class="inline-flex text-sky-600 hover:text-sky-800 transition-colors mr-3 align-middle" title="Editar Treinamento">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>
                            </a>
                            <a href="<?php echo BASE_URL; ?>/treinamentos/excluir/<?php echo $item['id']; ?>" class="inline-flex text-red-500 hover:text-red-700 transition-colors align-middle" onclick="return confirm('Tem certeza que deseja excluir este treinamento?');" title="Excluir Treinamento">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="7" class="px-5 py-10 text-center text-gray-400 text-sm">
                            <div style="font-size:32px;margin-bottom:8px">🎓</div>
                            Nenhum treinamento cadastrado.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Paginação -->
    <?php if ($totalPaginas > 1) : ?>
    <div class="px-5 py-3 border-t border-gray-100 flex items-center justify-between">
        <span class="text-xs text-gray-400">
            Página <?php echo $paginaAtual; ?> de <?php echo $totalPaginas; ?>
        </span>
        <nav class="flex items-center gap-1">
            <a href="<?php echo $buildUrl($paginaAtual - 1); ?>"
               class="px-3 py-1 rounded-md text-sm <?php echo ($paginaAtual <= 1) ? 'pointer-events-none text-gray-300' : 'text-gray-600 hover:bg-gray-100'; ?>">
                Anterior
            </a>
            <?php for ($i = 1; $i <= $totalPaginas; $i++) : ?>
                <a href="<?php echo $buildUrl($i); ?>"
                   class="w-8 h-8 flex items-center justify-center rounded-md text-sm font-medium <?php echo ($i == $paginaAtual) ? 'bg-sky-600 text-white' : 'text-gray-600 hover:bg-gray-100 border border-gray-200'; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
            <a href="<?php echo $buildUrl($paginaAtual + 1); ?>"
               class="px-3 py-1 rounded-md text-sm <?php echo ($paginaAtual >= $totalPaginas) ? 'pointer-events-none text-gray-300' : 'text-gray-600 hover:bg-gray-100'; ?>">
                Próxima
            </a>
        </nav>
    </div>
    <?php endif; ?>
</div>
