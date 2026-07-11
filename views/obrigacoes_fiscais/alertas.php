<?php $pageTitle = $pageTitle ?? 'Alertas Fiscais'; ?>
<div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Alertas Fiscais</h1>
            <p class="text-sm text-gray-500 mt-1">Notificações de vencimentos, atrasos e alterações legislativas</p>
        </div>
        <div class="flex items-center gap-3">
            <form method="POST" action="<?= BASE_URL ?>/obrigacoesFiscais/gerarAlertas" class="inline">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
                    <i class='bx bx-sync'></i> Gerar Alertas
                </button>
            </form>
            <a href="<?= BASE_URL ?>/obrigacoesFiscais/dashboard" class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200">
                <i class='bx bx-arrow-back'></i> Dashboard
            </a>
        </div>
    </div>

    <div class="space-y-3">
        <?php if (empty($alertas)): ?>
            <div class="bg-white rounded-xl border border-gray-200 p-8 text-center text-gray-400">
                Nenhum alerta encontrado.
            </div>
        <?php else: ?>
            <?php foreach ($alertas as $a): ?>
                <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-start gap-4
                    <?= $a['prioridade'] === 'critica' ? 'border-l-4 border-l-red-500' : '' ?>
                    <?= $a['prioridade'] === 'alta' ? 'border-l-4 border-l-amber-500' : '' ?>
                    <?= $a['prioridade'] === 'media' ? 'border-l-4 border-l-blue-500' : '' ?>
                    <?= $a['prioridade'] === 'baixa' ? 'border-l-4 border-l-gray-300' : '' ?>">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="font-semibold text-gray-800"><?= htmlspecialchars($a['titulo']) ?></span>
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium
                                <?= $a['prioridade'] === 'critica' ? 'bg-red-100 text-red-700' : '' ?>
                                <?= $a['prioridade'] === 'alta' ? 'bg-amber-100 text-amber-700' : '' ?>
                                <?= $a['prioridade'] === 'media' ? 'bg-blue-100 text-blue-700' : '' ?>
                                <?= $a['prioridade'] === 'baixa' ? 'bg-gray-100 text-gray-600' : '' ?>">
                                <?= ucfirst($a['prioridade']) ?>
                            </span>
                            <?php if (!$a['lido']): ?>
                                <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($a['mensagem'])): ?>
                            <p class="text-sm text-gray-600"><?= htmlspecialchars($a['mensagem']) ?></p>
                        <?php endif; ?>
                        <p class="text-xs text-gray-400 mt-1"><?= date('d/m/Y H:i', strtotime($a['created_at'])) ?></p>
                    </div>
                    <?php if (!$a['lido']): ?>
                        <a href="<?= BASE_URL ?>/obrigacoesFiscais/marcarLido/<?= $a['id'] ?>"
                           class="text-sm text-blue-600 hover:underline whitespace-nowrap">
                            <i class='bx bx-check'></i> Marcar lido
                        </a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
