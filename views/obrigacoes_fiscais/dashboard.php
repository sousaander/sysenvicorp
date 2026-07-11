<?php $pageTitle = $pageTitle ?? 'Dashboard de Obrigações Fiscais'; ?>
<div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Dashboard de Obrigações Fiscais</h1>
            <p class="text-sm text-gray-500 mt-1">Acompanhamento de prazos, entregas e alertas</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="<?= BASE_URL ?>/obrigacoesFiscais/calendario" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
                <i class='bx bx-calendar'></i> Calendário
            </a>
            <a href="<?= BASE_URL ?>/obrigacoesFiscais/alertas" class="inline-flex items-center gap-2 px-4 py-2 bg-rose-600 text-white text-sm font-medium rounded-lg hover:bg-rose-700">
                <i class='bx bx-bell'></i> Alertas
                <?php if (($resumo['alertas_nao_lidos'] ?? 0) > 0): ?>
                    <span class="px-1.5 py-0.5 bg-white text-rose-600 text-xs font-bold rounded-full"><?= $resumo['alertas_nao_lidos'] ?></span>
                <?php endif; ?>
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="text-2xl font-bold text-gray-800"><?= $resumo['total_obrigacoes'] ?? 0 ?></div>
            <p class="text-xs text-gray-500">Obrigações Cadastradas</p>
        </div>
        <div class="bg-white rounded-xl border border-amber-200 p-5">
            <div class="text-2xl font-bold text-amber-600"><?= $resumo['pendentes'] ?? 0 ?></div>
            <p class="text-xs text-gray-500">Pendentes</p>
        </div>
        <div class="bg-white rounded-xl border border-emerald-200 p-5">
            <div class="text-2xl font-bold text-emerald-600"><?= $resumo['entregues'] ?? 0 ?></div>
            <p class="text-xs text-gray-500">Entregues</p>
        </div>
        <div class="bg-white rounded-xl border border-red-200 p-5">
            <div class="text-2xl font-bold text-red-600"><?= $resumo['atrasados'] ?? 0 ?></div>
            <p class="text-xs text-gray-500">Atrasados</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h3 class="font-semibold text-gray-800 mb-3"><i class='bx bx-calendar-exclamation'></i> Próximos Vencimentos</h3>
            <?php if (empty($resumo['proximos_vencimentos'])): ?>
                <p class="text-sm text-gray-400">Nenhum vencimento nos próximos 30 dias.</p>
            <?php else: ?>
                <ul class="space-y-2">
                    <?php foreach ($resumo['proximos_vencimentos'] as $v): ?>
                        <li class="flex items-center justify-between text-sm <?= strtotime($v['data_vencimento']) < time() ? 'text-red-600' : 'text-gray-600' ?>">
                            <span><?= htmlspecialchars($v['nome']) ?> <span class="text-xs text-gray-400">(<?= $v['orgao'] ?>)</span></span>
                            <span class="font-mono text-xs"><?= date('d/m/Y', strtotime($v['data_vencimento'])) ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h3 class="font-semibold text-gray-800 mb-3"><i class='bx bx-bell'></i> Alertas Recentes</h3>
            <?php if (empty($alertas)): ?>
                <p class="text-sm text-gray-400">Nenhum alerta pendente.</p>
            <?php else: ?>
                <ul class="space-y-2">
                    <?php foreach ($alertas as $a): ?>
                        <li class="flex items-center justify-between text-sm
                            <?= $a['prioridade'] === 'critica' ? 'text-red-600' : '' ?>
                            <?= $a['prioridade'] === 'alta' ? 'text-amber-600' : '' ?>
                            <?= $a['prioridade'] === 'media' ? 'text-blue-600' : '' ?>">
                            <span><i class='bx bx-dot'></i> <?= htmlspecialchars($a['titulo']) ?></span>
                            <a href="<?= BASE_URL ?>/obrigacoesFiscais/marcarLido/<?= $a['id'] ?>" class="text-xs text-gray-400 hover:text-gray-600"><i class='bx bx-check'></i></a>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <a href="<?= BASE_URL ?>/obrigacoesFiscais/alertas" class="text-xs text-blue-600 hover:underline mt-2 inline-block">Ver todos</a>
            <?php endif; ?>
        </div>
    </div>
</div>
