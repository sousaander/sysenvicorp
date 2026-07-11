<?php
//use App\Helpers\ReportHelper;

// --- Métricas executivas calculadas a partir da listagem atual ---
$totalPropostas = count($orcamentos ?? []);
$valorTotalCarteira = 0.0;
$totalAprovadas = 0;
$totalAguardandoDiretor = 0;
foreach ($orcamentos ?? [] as $__o) {
    $valorTotalCarteira += (float) ($__o['total'] ?? 0);
    if (($__o['status'] ?? '') === 'Aprovada') { $totalAprovadas++; }
    if (($__o['aprovacao_diretor_status'] ?? '') === 'pendente') { $totalAguardandoDiretor++; }
}
?>

<style>
    .ec-wrap {
        --ec-ink: #1b1f27;
        --ec-ink-soft: #454a55;
        --ec-paper: #f8fafc;
        --ec-card: #ffffff;
        --ec-border: #e2e8f0;
        --ec-border-soft: #e2e8f0;
        --ec-accent: #2563eb;
        --ec-accent-deep: #1d4ed8;
        --ec-accent-soft: #dbeafe;
        --ec-text-muted: #64748b;
        --ec-sans: 'Plus Jakarta Sans', -apple-system, sans-serif;
        font-family: var(--ec-sans);
        color: var(--ec-ink);
    }
    html.dark .ec-wrap {
        --ec-ink: #e2e8f0;
        --ec-ink-soft: #94a3b8;
        --ec-paper: #0f172a;
        --ec-card: #1e293b;
        --ec-border: #334155;
        --ec-border-soft: #334155;
        --ec-accent: #60a5fa;
        --ec-accent-deep: #93c5fd;
        --ec-accent-soft: #1e3a5f;
        --ec-text-muted: #94a3b8;
    }

    .ec-eyebrow {
        font-family: var(--ec-mono);
        font-size: 11px;
        letter-spacing: .12em;
        text-transform: uppercase;
        color: var(--ec-accent-deep);
        display: block;
        margin-bottom: 6px;
        font-weight: 600;
    }
    .ec-title {
        font-family: var(--ec-display);
        font-size: 22px;
        font-weight: 600;
        letter-spacing: -.01em;
        color: var(--ec-ink);
        line-height: 1.15;
        margin: 0;
    }
    .ec-subtitle {
        color: var(--ec-text-muted);
        font-size: 13.5px;
        margin: 6px 0 0;
    }
    .ec-header-row {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: flex-end;
        gap: 20px;
        margin-bottom: 22px;
        padding-bottom: 18px;
        border-bottom: 1px solid var(--ec-border);
        position: relative;
    }
    .ec-header-row::after {
        content: '';
        position: absolute;
        left: 0; bottom: -1px;
        width: 64px; height: 2px;
        background: var(--ec-accent);
    }

    .ec-stepper {
        display: flex;
        flex-wrap: wrap;
        background: var(--ec-card);
        border: 1px solid var(--ec-border);
        border-radius: 12px;
        overflow: hidden;
    }
    .ec-step {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 10px 16px;
        font-size: 12.5px;
        font-weight: 600;
        letter-spacing: .01em;
        color: var(--ec-text-muted);
        border-right: 1px solid var(--ec-border-soft);
        transition: background .15s, color .15s;
        text-decoration: none;
        white-space: nowrap;
    }
    .ec-step:last-child { border-right: none; }
    .ec-step-num {
        width: 19px; height: 19px;
        border-radius: 50%;
        border: 1px solid currentColor;
        display: flex; align-items: center; justify-content: center;
        font-family: var(--ec-mono);
        font-size: 10px;
        flex-shrink: 0;
    }
    .ec-step.is-active {
        color: var(--ec-accent-deep);
        background: var(--ec-accent-soft);
    }
    .ec-step.is-active .ec-step-num {
        background: var(--ec-accent);
        border-color: var(--ec-accent);
        color: #fff;
    }
    .ec-step.is-disabled {
        color: #c8c2b2;
        cursor: not-allowed;
        pointer-events: none;
    }
    html.dark .ec-step.is-disabled { color: #454a55; }
    .ec-step:not(.is-disabled):not(.is-active):hover {
        background: var(--ec-paper);
        color: var(--ec-ink);
    }
    @media (max-width: 640px) { .ec-step span.ec-step-label { display: none; } }

    .ec-kpis {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
        margin-bottom: 20px;
    }
    @media (max-width: 900px) { .ec-kpis { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    .ec-kpi {
        background: var(--ec-card);
        border: 1px solid var(--ec-border);
        border-radius: 12px;
        padding: 12px 14px;
        position: relative;
        overflow: hidden;
    }
    .ec-kpi::before {
        content: '';
        position: absolute;
        left: 0; top: 0; bottom: 0;
        width: 3px;
        background: var(--ec-border);
    }
    .ec-kpi--accent::before { background: var(--ec-accent); }
    .ec-kpi--warn::before { background: var(--ec-accent); }
    .ec-kpi-label {
        display: block;
        font-size: 9.5px;
        color: var(--ec-text-muted);
        text-transform: uppercase;
        letter-spacing: .08em;
        font-weight: 700;
        margin-bottom: 6px;
    }
    .ec-kpi-value {
        font-family: var(--ec-display);
        font-size: 18px;
        font-weight: 700;
        color: var(--ec-ink);
    }

    .ec-card {
        background: var(--ec-card);
        border: 1px solid var(--ec-border);
        border-radius: 14px;
        overflow: hidden;
    }
    .ec-table-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 14px 18px;
        border-bottom: 1px solid var(--ec-border);
        flex-wrap: wrap;
    }
    .ec-search {
        display: flex;
        align-items: center;
        gap: 8px;
        background: var(--ec-paper);
        border: 1px solid var(--ec-border);
        border-radius: 9px;
        padding: 8px 12px;
        min-width: 260px;
        flex: 1;
        max-width: 360px;
    }
    .ec-search i { color: var(--ec-text-muted); font-size: 12px; }
    .ec-search input {
        border: none;
        background: transparent;
        outline: none;
        font-size: 13px;
        color: var(--ec-ink);
        width: 100%;
        font-family: var(--ec-sans);
    }
    .ec-search input::placeholder { color: var(--ec-text-muted); }
    .ec-count-tag {
        font-family: var(--ec-mono);
        font-size: 11.5px;
        color: var(--ec-text-muted);
        white-space: nowrap;
    }

    .ec-table { width: 100%; border-collapse: collapse; font-size: 13px; }
    .ec-table thead th {
        text-align: left;
        font-family: var(--ec-mono);
        font-size: 10.5px;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: var(--ec-text-muted);
        font-weight: 600;
        padding: 12px 16px;
        border-bottom: 2px solid var(--ec-accent-soft);
        background: var(--ec-paper);
    }
    .ec-table th.ec-right, .ec-table td.ec-right { text-align: right; }
    .ec-table th.ec-center, .ec-table td.ec-center { text-align: center; }
    .ec-table tbody tr.budget-row {
        border-bottom: 1px solid var(--ec-border-soft);
        cursor: pointer;
        transition: background .12s, box-shadow .12s;
    }
    .ec-table tbody tr.budget-row:hover { background: var(--ec-paper); }
    .ec-table tbody tr.budget-row.is-selected {
        background: var(--ec-accent-soft);
        box-shadow: inset 4px 0 0 var(--ec-accent);
    }
    .ec-table td { padding: 14px 16px; vertical-align: middle; color: var(--ec-ink); }
    .ec-numero {
        font-family: var(--ec-mono);
        font-weight: 700;
        color: var(--ec-accent-deep);
        letter-spacing: .01em;
        font-size: 10px;
        white-space: nowrap;
    }
    .ec-client { display: flex; align-items: center; gap: 10px; }
    .ec-avatar {
        width: 30px; height: 30px;
        border-radius: 9px;
        background: var(--ec-ink);
        color: var(--ec-accent-soft);
        display: flex; align-items: center; justify-content: center;
        font-family: var(--ec-mono);
        font-size: 11px;
        font-weight: 600;
        flex-shrink: 0;
    }
    .ec-client-name { font-weight: 600; color: var(--ec-ink); font-size: 10px; }
    .ec-titulo { color: var(--ec-ink-soft); font-size: 10px; }
    .ec-total { font-family: var(--ec-mono); font-weight: 700; color: var(--ec-ink); font-size: 10px; white-space: nowrap; }

    .ec-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 10px;
        border-radius: 999px;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .04em;
        border: 1px solid transparent;
        white-space: nowrap;
    }
    .ec-dot { width: 6px; height: 6px; border-radius: 50%; background: currentColor; flex-shrink: 0; }
    .ec-badge.cor-gray   { background: #f1efe8; color: #5c584f; border-color: #e2ddd0; }
    .ec-badge.cor-sky    { background: #e9f2fa; color: #2b5a82; border-color: #cfe3f2; }
    .ec-badge.cor-emerald{ background: #e9f5ee; color: #1f6b46; border-color: #cbe8d7; }
    .ec-badge.cor-rose   { background: #faeceb; color: #8c2f2b; border-color: #f0d3d1; }
    .ec-badge.cor-amber  { background: #faf1e0; color: #8a5a13; border-color: #f0ddb8; }
    .ec-badge.cor-indigo { background: #edeffb; color: #3c3f8a; border-color: #d7dbf2; }
    html.dark .ec-badge { border-width: 1px; filter: brightness(1); }
    html.dark .ec-badge.cor-gray { background: #2a2a26; color: #cfccc2; border-color: #3a3a35; }
    html.dark .ec-badge.cor-sky { background: #16283a; color: #8fc2ee; border-color: #204365; }
    html.dark .ec-badge.cor-emerald { background: #123626; color: #7fd6ac; border-color: #1b4f38; }
    html.dark .ec-badge.cor-rose { background: #3a1c1a; color: #f0a6a2; border-color: #55302d; }
    html.dark .ec-badge.cor-amber { background: #362a10; color: #ecc36c; border-color: #503d18; }
    html.dark .ec-badge.cor-indigo { background: #201f3a; color: #a6acef; border-color: #302f55; }

    .ec-chevron-btn {
        width: 20px; height: 20px;
        display: inline-flex; align-items: center; justify-content: center;
        border-radius: 50%;
        color: var(--ec-text-muted);
        transition: background .12s, color .12s;
    }
    .ec-chevron-btn:hover { background: var(--ec-border-soft); color: var(--ec-ink); }

    .ec-actions { display: flex; justify-content: center; gap: 2px; }
    .ec-icon-btn {
        width: 28px; height: 28px;
        display: inline-flex; align-items: center; justify-content: center;
        border-radius: 8px;
        color: var(--ec-text-muted);
        font-size: 14px;
        transition: background .12s, color .12s;
    }
    .ec-icon-btn:hover { background: var(--ec-paper); }
    .ec-icon-btn--view:hover { color: #2b6cb0; }
    .ec-icon-btn--edit:hover { color: var(--ec-accent); }
    .ec-icon-btn--clone:hover { color: #1f6b46; }
    .ec-icon-btn--director:hover { color: #4338ca; }
    .ec-icon-btn--link:hover { color: #2b6cb0; }
    .ec-icon-btn--whatsapp:hover { color: #1f9d55; }
    .ec-icon-btn--email:hover { color: #2b6cb0; }
    .ec-icon-btn--delete:hover { color: #a3312d; }

    .ec-empty { text-align: center; padding: 48px 16px; color: var(--ec-text-muted); }
    .ec-empty i { font-size: 26px; display: block; margin-bottom: 10px; color: var(--ec-border); }
</style>

<div class="ec-wrap mb-8">
    <div class="ec-header-row">
        <div>
            <span class="ec-eyebrow">Gestão comercial · <?= $totalPropostas ?> registro<?= $totalPropostas === 1 ? '' : 's' ?></span>
            <h2 class="ec-title">Propostas & Orçamentos</h2>
            <p class="ec-subtitle">Painel executivo de acompanhamento comercial.</p>
        </div>

        <!-- Navegação entre as 4 telas principais -->
        <nav class="ec-stepper">
            <a href="<?= BASE_URL ?>/orcamento/index" class="ec-step is-active">
                <span class="ec-step-num">1</span><span class="ec-step-label">Lista</span>
            </a>
            <a href="<?= BASE_URL ?>/orcamento/novo" class="ec-step">
                <span class="ec-step-num">2</span><span class="ec-step-label">Novo</span>
            </a>
            <a id="btn-nav-view" href="javascript:void(0)" class="ec-step is-disabled" title="Selecione um item na lista abaixo para visualizar">
                <span class="ec-step-num">3</span><span class="ec-step-label">Visualizar</span>
            </a>
            <a id="btn-nav-pdf" href="javascript:void(0)" target="_blank" class="ec-step is-disabled" title="Selecione um item na lista abaixo para gerar o PDF">
                <span class="ec-step-num">4</span><span class="ec-step-label">PDF</span>
            </a>
        </nav>
    </div>

    <!-- Resumo executivo -->
    <div class="ec-kpis">
        <div class="ec-kpi">
            <span class="ec-kpi-label">Total de propostas</span>
            <span class="ec-kpi-value"><?= $totalPropostas ?></span>
        </div>
        <div class="ec-kpi ec-kpi--accent">
            <span class="ec-kpi-label">Valor da carteira</span>
            <span class="ec-kpi-value"><?= \App\Helpers\ReportHelper::formatCurrency($valorTotalCarteira) ?></span>
        </div>
        <div class="ec-kpi">
            <span class="ec-kpi-label">Aprovadas</span>
            <span class="ec-kpi-value"><?= $totalAprovadas ?></span>
        </div>
        <div class="ec-kpi ec-kpi--warn">
            <span class="ec-kpi-label">Aguardando diretoria</span>
            <span class="ec-kpi-value"><?= $totalAguardandoDiretor ?></span>
        </div>
    </div>

    <div class="ec-card">
        <div class="ec-table-toolbar">
            <div class="ec-search">
                <i class="fas fa-search"></i>
                <input type="text" id="ec-search-input" placeholder="Buscar por número, cliente ou título...">
            </div>
            <span class="ec-count-tag" id="ec-count-tag"><?= $totalPropostas ?> registro<?= $totalPropostas === 1 ? '' : 's' ?></span>
        </div>

        <div class="overflow-x-auto" style="min-height: 350px;">
            <table class="ec-table" id="ec-proposals-table">
                <thead>
                    <tr>
                        <th>Nº</th>
                        <th>Cliente</th>
                        <th>Título</th>
                        <th class="ec-right">Total</th>
                        <th class="ec-center">Status</th>
                        <th class="ec-center">Dir.</th>
                        <th class="ec-center">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($orcamentos)): ?>
                        <tr><td colspan="7"><div class="ec-empty"><i class="fas fa-folder-open"></i>Nenhum registro encontrado.</div></td></tr>
                    <?php else: ?>
                        <?php foreach ($orcamentos as $orc):
                            // Normalização do status para as cores definidas no Controller
                            $statusKey = $orc['status'];
                            $sl = $statusLabels[$statusKey] ?? ['label' => $statusKey, 'cor' => 'gray'];

                            // Iniciais do cliente para o avatar
                            $__nomeCliente = $orc['cliente_nome'] ?? '';
                            $__partes = preg_split('/\s+/', trim($__nomeCliente));
                            $__iniciais = '';
                            if (!empty($__partes[0])) { $__iniciais .= mb_substr($__partes[0], 0, 1); }
                            if (count($__partes) > 1 && !empty(end($__partes))) { $__iniciais .= mb_substr(end($__partes), 0, 1); }
                            $__iniciais = mb_strtoupper($__iniciais) ?: '—';

                            $__searchBlob = mb_strtolower($orc['numero'] . ' ' . $__nomeCliente . ' ' . $orc['titulo']);
                        ?>
                            <tr class="budget-row" data-id="<?= $orc['id'] ?>" data-search="<?= htmlspecialchars($__searchBlob) ?>">
                                <td><span class="ec-numero"><?= htmlspecialchars($orc['numero']) ?></span></td>
                                <td>
                                    <div class="ec-client">
                                        <span class="ec-avatar"><?= htmlspecialchars($__iniciais) ?></span>
                                        <span class="ec-client-name"><?= htmlspecialchars($orc['cliente_nome']) ?></span>
                                    </div>
                                </td>
                                <td><span class="ec-titulo"><?= htmlspecialchars($orc['titulo']) ?></span></td>
                                <td class="ec-right"><span class="ec-total"><?= \App\Helpers\ReportHelper::formatCurrency($orc['total']) ?></span></td>
                                <td class="ec-center" style="position: relative;">
                                    <div class="inline-flex items-center gap-1">
                                        <span class="ec-badge cor-<?= $sl['cor'] ?>">
                                            <span class="ec-dot"></span><span><?= $sl['label'] ?></span>
                                        </span>
                                        <?php if ($isAdmin || $orc['status'] !== 'Aprovada'): ?>
                                        <button type="button" onclick="toggleStatusMenu(this, <?= $orc['id'] ?>)" class="ec-chevron-btn" title="Alterar status">
                                            <i class="fas fa-chevron-down" style="font-size:8px;"></i>
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                    <?php if ($isAdmin || $orc['status'] !== 'Aprovada'): ?>
                                    <div class="hidden absolute right-4 top-12 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-2xl z-[100] min-w-[150px] status-menu-<?= $orc['id'] ?>">
                                        <button type="button" onclick="changeStatus(<?= $orc['id'] ?>, 'Rascunho')" class="block w-full text-left px-4 py-2.5 hover:bg-sky-50 dark:hover:bg-sky-900/20 text-xs font-bold text-gray-700 dark:text-gray-300 transition">Rascunho</button>
                                        <button type="button" onclick="changeStatus(<?= $orc['id'] ?>, 'Enviada')" class="block w-full text-left px-4 py-2.5 hover:bg-sky-50 dark:hover:bg-sky-900/20 text-xs font-bold text-gray-700 dark:text-gray-300 border-t dark:border-gray-700 transition">Enviada</button>
                                        <button type="button" onclick="changeStatus(<?= $orc['id'] ?>, 'Aprovada')" class="block w-full text-left px-4 py-2.5 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 text-xs font-bold text-emerald-600 dark:text-emerald-400 border-t dark:border-gray-700 transition">Aprovada</button>
                                        <button type="button" onclick="changeStatus(<?= $orc['id'] ?>, 'Rejeitada')" class="block w-full text-left px-4 py-2.5 hover:bg-rose-50 dark:hover:bg-rose-900/20 text-xs font-bold text-rose-600 dark:text-rose-400 border-t dark:border-gray-700 transition">Rejeitada</button>
                                        <button type="button" onclick="changeStatus(<?= $orc['id'] ?>, 'Cancelada')" class="block w-full text-left px-4 py-2.5 hover:bg-gray-50 dark:hover:bg-gray-700 text-xs font-bold text-gray-500 border-t dark:border-gray-700 transition">Cancelada</button>
                                    </div>
                                    <?php endif; ?>
                                </td>
                                <td class="ec-center">
                                    <?php
                                        $dirSt = $orc['aprovacao_diretor_status'] ?? 'nao_solicitado';
                                        $dsl = $diretorStatusLabels[$dirSt] ?? ['label' => 'N/A', 'cor' => 'gray'];
                                    ?>
                                    <div class="inline-flex items-center gap-1">
                                        <?php if ($dirSt === 'pendente'): ?>
                                            <button type="button" onclick="abrirModalDiretor(<?= $orc['id'] ?>)" class="ec-badge cor-amber" style="cursor:pointer; border:none;" title="Clique para aprovar ou rejeitar">
                                                <i class="fas fa-hourglass-half"></i><?= $dsl['label'] ?>
                                            </button>
                                        <?php else: ?>
                                            <span class="ec-badge cor-<?= $dsl['cor'] ?>">
                                                <?php if ($dirSt === 'aprovado'): ?><i class="fas fa-check-circle"></i>
                                                <?php elseif ($dirSt === 'rejeitado'): ?><i class="fas fa-times-circle"></i>
                                                <?php else: ?><span class="ec-dot"></span>
                                                <?php endif; ?>
                                                <span><?= $dsl['label'] ?></span>
                                            </span>
                                            <?php if ($dirSt === 'aprovado' && !empty($orc['diretor_nome'])): ?>
                                                <span class="text-[9px] text-gray-400 dark:text-gray-500 block mt-0.5">por <?= htmlspecialchars($orc['diretor_nome']) ?></span>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="ec-center">
                                    <div class="ec-actions">
                                        <?php if ($orc['aprovacao_diretor_status'] ?? '' === 'nao_solicitado' || $orc['aprovacao_diretor_status'] ?? '' === 'rejeitado'): ?>
                                        <?php if ($orc['status'] !== 'Aprovada'): ?>
                                        <button type="button" onclick="enviarParaDiretor(<?= $orc['id'] ?>)" class="ec-icon-btn ec-icon-btn--director" title="Enviar para aprovação do diretor">
                                            <i class="fas fa-user-tie"></i>
                                        </button>
                                        <?php endif; ?>
                                        <?php elseif ($orc['aprovacao_diretor_status'] === 'pendente'): ?>
                                        <span class="ec-icon-btn" style="color:#b5842b; cursor:default;" title="Aguardando aprovação do diretor">
                                            <i class="fas fa-hourglass"></i>
                                        </span>
                                        <?php elseif ($orc['aprovacao_diretor_status'] === 'aprovado'): ?>
                                        <span class="ec-icon-btn" style="color:#1f6b46; cursor:default;" title="Aprovado pelo diretor">
                                            <i class="fas fa-check-circle"></i>
                                        </span>
                                        <?php endif; ?>
                                        <a href="<?= BASE_URL ?>/orcamento/ver/<?= $orc['id'] ?>" class="ec-icon-btn ec-icon-btn--view" title="Visualizar">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php if ($isAdmin || $orc['status'] !== 'Aprovada'): ?>
                                        <a href="<?= BASE_URL ?>/orcamento/editar/<?= $orc['id'] ?>" class="ec-icon-btn ec-icon-btn--edit" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php endif; ?>
                                        <a href="<?= BASE_URL ?>/orcamento/clonar/<?= $orc['id'] ?>" class="ec-icon-btn ec-icon-btn--clone" title="Clonar / Duplicar">
                                            <i class="fas fa-copy"></i>
                                        </a>
                                        <button onclick="copiarLinkDireto(<?= $orc['id'] ?>)" class="ec-icon-btn ec-icon-btn--link" title="Copiar Link de Aprovação">
                                            <i class="fas fa-link"></i>
                                        </button>
                                        <button onclick="enviarWhatsApp(<?= $orc['id'] ?>, '<?= addslashes($orc['titulo']) ?>', '<?= $orc['cliente_telefone'] ?? '' ?>')" class="ec-icon-btn ec-icon-btn--whatsapp" title="Enviar via WhatsApp">
                                            <i class="fab fa-whatsapp"></i>
                                        </button>
                                        <button onclick="openEmailModal(<?= $orc['id'] ?>, '<?= addslashes($orc['titulo']) ?>', '<?= addslashes($orc['cliente_email'] ?? '') ?>')" class="ec-icon-btn ec-icon-btn--email" title="Enviar por E-mail">
                                            <i class="fas fa-envelope"></i>
                                        </button>
                                        <?php if ($isAdmin || $orc['status'] !== 'Aprovada'): ?>
                                        <button onclick="excluirProposta('<?= htmlspecialchars($orc['id']) ?>', this)" class="ec-icon-btn ec-icon-btn--delete" title="Excluir">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Estrutura da Modal para Propostas -->
<div id="propostaModal" class="fixed inset-0 bg-gray-800 dark:bg-black bg-opacity-75 dark:bg-opacity-75 flex items-center justify-center z-50 hidden">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-3xl max-h-[90vh] overflow-y-auto">
        <div id="propostaModalContent" class="p-6">
            <p class="text-center dark:text-gray-300">Carregando formulário...</p>
        </div>
    </div>
</div>

<!-- Modal de Envio de E-mail -->
<?php
$nomeEmpresa = htmlspecialchars($empresa['nome_fantasia'] ?? $empresa['razao_social'] ?? '');
$userEmail = htmlspecialchars($userEmail ?? '');
$userCargo = htmlspecialchars($userCargo ?? '');
$remetenteNome = htmlspecialchars($userName ?? '');
$assinaturaPadrao = $nomeEmpresa . "\n" . ($userCargo ? $userCargo . ' - ' : '') . $remetenteNome;
?>
<div id="emailModal" class="fixed inset-0 bg-gray-800 dark:bg-black bg-opacity-75 dark:bg-opacity-75 flex items-center justify-center z-50 hidden">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-2xl">
        <form id="emailForm" action="" method="POST" class="p-6">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>" />
            <h3 class="text-xl font-bold mb-4 dark:text-white">Enviar Proposta por E-mail</h3>

<?php
$logoUrl = '';
$logoPath = $empresa['logo_path'] ?? '';
if ($logoPath) {
    $logoFile = ROOT_PATH . '/public/uploads/logos/' . $logoPath;
    if (file_exists($logoFile)) {
        $logoUrl = BASE_URL . '/uploads/logos/' . $logoPath;
    }
}
?>
            <div class="mb-4 p-3 bg-blue-50 dark:bg-gray-700/80 border border-blue-200 dark:border-gray-600 rounded-lg">
                <label class="block text-xs font-semibold text-blue-700 dark:text-blue-400 uppercase tracking-wide">De (remetente)</label>
                <div class="flex items-center gap-3 mt-1">
                    <?php if ($logoUrl): ?>
                        <img src="<?= $logoUrl ?>" alt="<?= $nomeEmpresa ?>" class="h-8 w-auto object-contain">
                    <?php else: ?>
                        <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900/50 rounded-full flex items-center justify-center text-blue-600 dark:text-blue-300 font-bold text-xs"><?= mb_substr($nomeEmpresa, 0, 2, 'UTF-8') ?></div>
                    <?php endif; ?>
                    <div>
                        <p class="text-sm font-semibold text-gray-800 dark:text-gray-100"><?= $nomeEmpresa ?></p>
                        <p class="text-xs font-medium text-gray-600 dark:text-gray-300">
                            <?= $remetenteNome ?>
                            <span class="text-gray-400 dark:text-gray-400 font-normal">&lt;<?= $userEmail ?>&gt;</span>
                        </p>
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <label for="email_destinatario" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Para:</label>
                <input type="email" name="email_destinatario" id="email_destinatario" required class="w-full border dark:border-gray-700 dark:bg-gray-700 dark:text-white rounded p-2 mt-1" placeholder="email@cliente.com">
            </div>

            <div class="mb-4">
                <label for="email_assunto" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Assunto:</label>
                <input type="text" name="email_assunto" id="email_assunto" required class="w-full border dark:border-gray-700 dark:bg-gray-700 dark:text-white rounded p-2 mt-1">
            </div>

            <div class="mb-4">
                <label for="email_corpo" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Mensagem:</label>
                <textarea name="email_corpo" id="email_corpo" rows="6" class="w-full border dark:border-gray-700 dark:bg-gray-700 dark:text-white rounded p-2 mt-1"></textarea>
            </div>

            <div class="mb-4 p-3 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-lg">
                <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">Assinatura (será exibida no rodapé do e-mail)</label>
                <div class="flex items-start gap-3">
                    <?php if ($logoUrl): ?>
                        <img src="<?= $logoUrl ?>" alt="<?= $nomeEmpresa ?>" class="h-10 w-auto object-contain mt-1">
                    <?php endif; ?>
                    <div class="text-sm text-gray-700 dark:text-gray-300">
                        <p class="font-bold text-gray-900 dark:text-gray-100"><?= $nomeEmpresa ?></p>
                        <p><?= ($userCargo ? $userCargo . ' - ' : '') . $remetenteNome ?></p>
                        <p class="text-xs text-gray-400 dark:text-gray-500"><?= $userEmail ?></p>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-2">
                <button type="button" onclick="closeEmailModal()" class="px-4 py-2 bg-gray-300 dark:bg-gray-600 dark:text-white rounded font-bold">Cancelar</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded font-bold">
                    <i class="fas fa-paper-plane mr-1"></i> Enviar E-mail
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal de Confirmação de Duplicidade de Contrato (Tailwind CSS) -->
<div id="modalConfirmacaoContrato" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm flex items-center justify-center z-[100] hidden">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-lg w-full mx-4 overflow-hidden border border-gray-200 dark:border-gray-700 animate-popIn">
        <div class="p-6">
            <div class="flex items-center gap-4 mb-5">
                <div class="w-12 h-12 rounded-xl bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center text-amber-600 dark:text-amber-400">
                    <i class="fas fa-exclamation-triangle text-xl"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Atenção: Contratos Ativos</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400" id="modal-cliente-subtitle"></p>
                </div>
            </div>
            
            <div class="mb-4">
                <p class="text-sm text-gray-700 dark:text-gray-300 mb-3 font-medium">O cliente já possui os seguintes instrumentos. Selecione um para vincular ou crie um novo:</p>
                <div class="max-h-48 overflow-y-auto space-y-2 pr-1 custom-scrollbar" id="modal-lista-contratos">
                    <!-- Lista injetada via JS -->
                </div>
            </div>

            <div id="modal-selecionado-info" class="hidden mb-4 p-3 rounded-xl bg-sky-50 dark:bg-sky-900/20 border border-sky-200 dark:border-sky-800 text-sm text-sky-700 dark:text-sky-300">
                <i class="fas fa-check-circle mr-1"></i>
                Contrato <strong id="modal-selecionado-numero"></strong> selecionado para vinculação.
            </div>

            <div class="flex flex-col sm:flex-row gap-3">
                <button type="button" id="btn-confirm-sim" class="flex-1 px-4 py-3 bg-sky-600 hover:bg-sky-700 text-white text-sm font-bold rounded-xl transition shadow-lg shadow-sky-200 dark:shadow-none">
                    <i class="fas fa-file-signature mr-1"></i> Novo Contrato
                </button>
                <button type="button" id="btn-confirm-vincular" class="flex-1 px-4 py-3 bg-emerald-600 hover:bg-emerald-700 disabled:bg-gray-300 dark:disabled:bg-gray-600 text-white text-sm font-bold rounded-xl transition shadow-lg shadow-emerald-200 dark:shadow-none disabled:cursor-not-allowed" disabled>
                    <i class="fas fa-link mr-1"></i> Usar Selecionado
                </button>
                <button type="button" id="btn-confirm-nao" class="flex-1 px-4 py-3 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-200 text-sm font-bold rounded-xl hover:bg-gray-50 dark:hover:bg-gray-600 transition">
                    Apenas Aprovar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Aprovação do Diretor -->
<div id="diretorModal" class="fixed inset-0 bg-gray-800 dark:bg-black bg-opacity-75 dark:bg-opacity-75 flex items-center justify-center z-[60] hidden">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
        <div id="diretorModalContent" class="p-6">
            <div class="flex justify-center items-center py-16">
                <div class="animate-spin rounded-full h-12 w-12 border-4 border-indigo-500 border-t-transparent"></div>
                <p class="ml-4 text-gray-600 dark:text-gray-300 font-semibold">Carregando...</p>
            </div>
        </div>
    </div>
</div>

<script>
    const modal = document.getElementById('propostaModal');
    const modalContent = document.getElementById('propostaModalContent');

    /**
     * Abre a modal e carrega o conteúdo via AJAX
     */
    function openPropostaModal(url) {
        if (!modal || !modalContent) return;

        modal.classList.remove('hidden');
        modalContent.innerHTML = `
            <div class="flex justify-center items-center py-16">
                <div class="animate-spin rounded-full h-12 w-12 border-4 border-sky-500 border-t-transparent"></div>
                <p class="ml-4 text-gray-600 font-semibold">Carregando formulário...</p>
            </div>`;

        // Adiciona o parâmetro ajax para o controller renderizar apenas o partial (formulario.php)
        const ajaxUrl = url.includes('?') ? `${url}&ajax=1` : `${url}?ajax=1`;

        fetch(ajaxUrl)
            .then(response => {
                if (!response.ok) throw new Error('Erro na requisição');
                return response.text();
            })
            .then(html => {
                modalContent.innerHTML = html;
            })
            .catch(error => {
                modalContent.innerHTML = `
                    <div class="p-8 text-center">
                        <i class="fas fa-exclamation-circle text-red-500 text-4xl mb-4"></i>
                        <p class="text-gray-800 font-bold">Erro ao carregar o formulário.</p>
                        <button onclick="closePropostaModal()" class="mt-4 text-sky-600 underline">Fechar</button>
                    </div>`;
                console.error('Modal Load Error:', error);
            });
    }

    function closePropostaModal() {
        modal.classList.add('hidden');
        modalContent.innerHTML = '';
    }

    // Delegação de eventos para checkboxes de projeto/contrato (scripts inline não executam em innerHTML)
    modalContent.addEventListener('change', function(e) {
        if (e.target.id === 'has-projeto-checkbox' && !e.target.checked) {
            const sel = document.getElementById('projeto_id');
            if (sel) sel.value = '';
            document.getElementById('project-details-container')?.classList.add('hidden');
        }
        if (e.target.id === 'has-contrato-checkbox' && !e.target.checked) {
            const sel = document.getElementById('contrato_id');
            if (sel) sel.value = '';
            document.getElementById('section-contrato-detalhes')?.classList.add('hidden');
        }
    });

    /**
     * Abre a modal de e-mail e preenche os campos básicos
     */
    function openEmailModal(id, titulo, email) {
        const eModal = document.getElementById('emailModal');
        const form = document.getElementById('emailForm');
        const assinatura = <?= json_encode($assinaturaPadrao) ?>;

        form.action = `<?= BASE_URL ?>/orcamento/enviarEmail/${id}`;
        document.getElementById('email_destinatario').value = email || '';
        document.getElementById('email_assunto').value = `Proposta Comercial: ${titulo}`;
        document.getElementById('email_corpo').value = `Prezado(a),\n\nSegue em anexo nossa proposta comercial referente a "${titulo}".\n\nFicamos à disposição para qualquer esclarecimento.`;

        eModal.classList.remove('hidden');
    }

    /**
     * Fecha a modal de e-mail
     */
    function closeEmailModal() {
        document.getElementById('emailModal').classList.add('hidden');
    }

    /**
     * Lógica de seleção de linha para ativar botões de navegação (otimizada)
     */
    document.addEventListener('DOMContentLoaded', function() {
        // Delegação de eventos para melhor performance
        const tableBody = document.querySelector('#ec-proposals-table tbody');
        if (tableBody) {
            tableBody.addEventListener('click', function(e) {
                const row = e.target.closest('.budget-row');
                if (!row) return;

                // Se o clique for nas ações (última coluna), ignoramos a seleção da linha
                if (e.target.closest('td:last-child')) return;

                handleRowSelection(row);
            });
        }

        // Fecha menus de status ao clicar fora
        document.addEventListener('click', function(event) {
            if (!event.target.closest('[class*="status-menu-"]') && !event.target.closest('button[onclick*="toggleStatusMenu"]')) {
                document.querySelectorAll('[class*="status-menu-"]').forEach(m => m.classList.add('hidden'));
            }
        });

        // Busca instantânea por número, cliente ou título
        const searchInput = document.getElementById('ec-search-input');
        const countTag = document.getElementById('ec-count-tag');
        if (searchInput) {
            searchInput.addEventListener('input', function(e) {
                const term = e.target.value.trim().toLowerCase();
                const rows = document.querySelectorAll('#ec-proposals-table tbody tr.budget-row');
                let visible = 0;
                rows.forEach(row => {
                    const hay = row.dataset.search || '';
                    const match = !term || hay.includes(term);
                    row.style.display = match ? '' : 'none';
                    if (match) visible++;
                });
                if (countTag) countTag.textContent = `${visible} registro${visible === 1 ? '' : 's'}`;
            });
        }
    });

    /**
     * Trata seleção de linha (extraído para reutilização)
     */
    function handleRowSelection(row) {
        const id = row.dataset.id;
        const btnView = document.getElementById('btn-nav-view');
        const btnPdf = document.getElementById('btn-nav-pdf');

        // Remove seleção visual anterior de todas as linhas
        document.querySelectorAll('.budget-row.is-selected').forEach(r => r.classList.remove('is-selected'));

        // Aplica destaque na linha clicada
        row.classList.add('is-selected');

        // Ativa os botões de navegação superior
        if (btnView) {
            btnView.href = `<?= BASE_URL ?>/orcamento/ver/${id}`;
            btnView.classList.remove('is-disabled');
        }

        if (btnPdf) {
            btnPdf.href = `<?= BASE_URL ?>/orcamento/pdf/${id}`;
            btnPdf.classList.remove('is-disabled');
        }
    }

    /**
     * Exclui uma proposta com confirmação
     */
    function normalizeProposalId(value) {
        if (value === null || value === undefined) return null;
        const trimmed = String(value).trim();
        if (trimmed === '') return null;

        const parsed = Number(trimmed);
        if (!Number.isInteger(parsed) || parsed <= 0) return null;

        return parsed;
    }

    function excluirProposta(id, element = null) {
        let resolvedId = normalizeProposalId(id);
        
        // Se o primeiro parâmetro é um objeto (elemento), tenta extrair o ID
        if (!resolvedId && element && typeof element === 'object') {
            const row = element.closest('tr');
            const rowId = row?.dataset?.id;
            resolvedId = normalizeProposalId(rowId);
            
            // Se o data-id é 0 ou inválido, tenta extrair dos links
            if (!resolvedId && rowId === '0') {
                const editLink = row?.querySelector('a[href*="/editar/"]');
                if (editLink) {
                    const href = editLink.getAttribute('href');
                    const match = href.match(/\/editar\/(\d+)/);
                    if (match && match[1]) {
                        resolvedId = normalizeProposalId(match[1]);
                    }
                }
            }
            
            if (!resolvedId) {
                console.warn('Falha ao extrair ID do elemento', { element, row, rowId });
            }
        }

        // Fallback: se ainda não tem, procura o ID no elemento passado
        if (!resolvedId && id && typeof id === 'object') {
            const fallback = id.dataset?.id || id.id || id.value || id.getAttribute?.('data-id');
            resolvedId = normalizeProposalId(fallback);
        }

        if (!resolvedId && element) {
            const row = element.closest('tr');
            const rowId = row?.dataset?.id;
            resolvedId = normalizeProposalId(rowId);
            
            // Se ainda vazio, tenta extrair dos links
            if (!resolvedId && rowId === '0') {
                const editLink = row?.querySelector('a[href*="/editar/"]');
                if (editLink) {
                    const href = editLink.getAttribute('href');
                    const match = href.match(/\/editar\/(\d+)/);
                    if (match && match[1]) {
                        resolvedId = normalizeProposalId(match[1]);
                    }
                }
            }
        }

        if (!resolvedId || resolvedId <= 0 || Number.isNaN(resolvedId)) {
            // Última tentativa: procura qualquer link com ID na página
            let searchRow = element?.closest('tr');
            if (!searchRow) {
                const selectedRow = document.querySelector('tr[data-id].is-selected');
                searchRow = selectedRow || document.querySelector('tr[data-id]');
            }
            
            if (searchRow) {
                // Procura ID em links (ver, editar, pdf, etc)
                const allLinks = searchRow.querySelectorAll('a[href]');
                for (const link of allLinks) {
                    const href = link.getAttribute('href');
                    const match = href.match(/\/(ver|editar|pdf)\/(\d+)/);
                    if (match && match[2]) {
                        const extractedId = normalizeProposalId(match[2]);
                        if (extractedId) {
                            resolvedId = extractedId;
                            break;
                        }
                    }
                }
            }
            
            // Se ainda não tem, tenta o rowId
            if (!resolvedId && searchRow?.dataset?.id) {
                resolvedId = Number(searchRow.dataset.id);
            }
        }

        if (!resolvedId || resolvedId <= 0 || Number.isNaN(resolvedId)) {
            alert('ID de proposta inválido. Não foi possível excluir.');
            console.warn('excluirProposta: ID inválido', { id, resolvedId });
            return;
        }

        if (!confirm('Tem certeza que deseja excluir esta proposta permanentemente? Esta ação não pode ser desfeita.')) return;
        
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `<?= BASE_URL ?>/orcamento/excluir/${resolvedId}`;
        
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = 'csrf_token';
        csrfInput.value = '<?= $csrf_token ?? '' ?>';

        const idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'id';
        idInput.value = resolvedId;

        form.appendChild(csrfInput);
        form.appendChild(idInput);
        document.body.appendChild(form);
        form.submit();
    }

    /**
     * Gera link público e abre WhatsApp
     */
    function enviarWhatsApp(id, titulo, telefone) {
        const originalCursor = document.body.style.cursor;
        document.body.style.cursor = 'wait';

        fetch(`<?= BASE_URL ?>/orcamento/gerarLinkPublico/${id}?origem=whatsapp`)
            .then(res => res.json())
            .then(data => {
                document.body.style.cursor = originalCursor;
                if (data.success) {
                    const texto = `Prezado cliente, segue o link para visualização e aprovação da proposta *${titulo}*:\n\n ${data.link} \n\nFicamos à disposição para qualquer dúvida através do nosso contato oficial: <?= WHATSAPP_COMERCIAL_FORMATTED ?>.`;
                    
                    const cleanPhone = telefone ? telefone.replace(/\D/g, '') : '';
                    const url = cleanPhone.length >= 10 
                        ? `https://wa.me/55${cleanPhone}?text=${encodeURIComponent(texto)}`
                        : `https://wa.me/?text=${encodeURIComponent(texto)}`;
                    
                    window.open(url, '_blank');
                } else {
                    alert(data.message || 'Erro ao gerar o link público.');
                }
            })
            .catch(err => {
                document.body.style.cursor = originalCursor;
                console.error('WhatsApp Error:', err);
                alert('Falha na comunicação com o servidor.');
            });
    }

    /**
     * Abre/fecha o menu de alteração de status
     */
    function toggleStatusMenu(button, id) {
        const menu = document.querySelector(`.status-menu-${id}`);
        const td = button.closest('td');
        if (!menu) return;

        // Fecha todos os menus abertos
        document.querySelectorAll('[class*="status-menu-"]').forEach(m => {
            if (m !== menu) {
                m.classList.add('hidden');
                m.closest('td').style.zIndex = '';
            }
        });

        const isOpening = menu.classList.contains('hidden');
        menu.classList.toggle('hidden');
        
        // Eleva o z-index da célula para o menu sobrepor as linhas de baixo
        td.style.zIndex = isOpening ? '50' : '';
    }

    /**
     * Altera o status da proposta via AJAX
     */
    function changeStatus(id, newStatus, confirmacaoDuplicado = null) {
        if (newStatus === 'Aprovada' && !confirmacaoDuplicado) {
            if (!confirm('Deseja marcar esta proposta como Aprovada?')) {
                toggleStatusMenu(null, id);
                return;
            }
        } else if (newStatus !== 'Aprovada') {
            const acao = newStatus === 'Rejeitada' ? 'rejeitar' : 'alterar para ' + newStatus;
            if (!confirm(`Deseja ${acao} esta proposta?`)) {
                toggleStatusMenu(null, id);
                return;
            }
        }

        let motivo = '';
        if (newStatus === 'Rejeitada') {
            motivo = prompt('Informe o motivo da rejeição (opcional):');
        }

        const formData = new FormData();
        formData.append('status', newStatus);
        formData.append('motivo', motivo);
        formData.append('csrf_token', '<?= $csrf_token ?? '' ?>');
        if (confirmacaoDuplicado) {
            formData.append('confirmacao_duplicado', confirmacaoDuplicado);
        }

        const originalCursor = document.body.style.cursor;
        document.body.style.cursor = 'wait';

        fetch(`<?= BASE_URL ?>/orcamento/updateStatusAjax/${id}`, {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(result => {
                document.body.style.cursor = originalCursor;

                if (result.confirmacao_necessaria) {
                    abrirModalConfirmacaoContrato(id, newStatus, result);
                    return;
                }

                if (result.success) {
                    window.location.reload();
                } else {
                    alert(result.message || 'Erro ao atualizar status.');
                    toggleStatusMenu(null, id);
                }
            })
            .catch(err => {
                document.body.style.cursor = originalCursor;
                console.error('Status update error:', err);
                alert('Erro na comunicação com o servidor.');
                toggleStatusMenu(null, id);
            });
    }

    function abrirModalConfirmacaoContrato(id, status, data) {
        const modal = document.getElementById('modalConfirmacaoContrato');
        const listContainer = document.getElementById('modal-lista-contratos');
        const subtitle = document.getElementById('modal-cliente-subtitle');
        const selectedInfo = document.getElementById('modal-selecionado-info');
        const selectedNumero = document.getElementById('modal-selecionado-numero');
        
        let selectedId = null;
        
        subtitle.textContent = `Cliente: ${data.cliente_nome}`;
        listContainer.innerHTML = '';
        selectedInfo.classList.add('hidden');
        
        data.contratos.forEach(c => {
            const num = c.numero_contrato || `ID: ${c.id}`;
            const div = document.createElement('div');
            div.dataset.contratoId = c.id;
            div.dataset.numero = num;
            div.className = "p-3 rounded-xl bg-gray-50 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-700 text-[12px] hover:border-sky-300 dark:hover:border-sky-600 transition-colors cursor-pointer flex items-start gap-3";
            div.innerHTML = `<span class="w-4 h-4 mt-0.5 rounded-full border-2 border-gray-300 dark:border-gray-500 flex-shrink-0 flex items-center justify-center contrato-radio"></span>
                             <div class="flex-1 min-w-0">
                                 <div class="font-bold text-sky-600 dark:text-sky-400 mb-1">${num}</div>
                                 <div class="text-gray-600 dark:text-gray-400 line-clamp-2">${c.objeto || 'Sem descrição'}</div>
                             </div>`;
            
            div.addEventListener('click', () => {
                document.querySelectorAll('#modal-lista-contratos > div').forEach(el => {
                    el.classList.remove('border-sky-400', 'bg-sky-50', 'dark:bg-sky-900/20');
                    el.querySelector('.contrato-radio').innerHTML = '';
                });
                div.classList.add('border-sky-400', 'bg-sky-50', 'dark:bg-sky-900/20');
                div.querySelector('.contrato-radio').innerHTML = '<span class="w-2 h-2 rounded-full bg-sky-500"></span>';
                selectedId = c.id;
                selectedNumero.textContent = num;
                selectedInfo.classList.remove('hidden');
                btnVincular.disabled = false;
            });
            
            listContainer.appendChild(div);
        });
        
        modal.classList.remove('hidden');
        
        const btnSim = document.getElementById('btn-confirm-sim');
        const btnNao = document.getElementById('btn-confirm-nao');
        const btnVincular = document.getElementById('btn-confirm-vincular');
        
        btnSim.onclick = () => {
            modal.classList.add('hidden');
            changeStatus(id, status, 'sim');
        };
        
        btnNao.onclick = () => {
            modal.classList.add('hidden');
            changeStatus(id, status, 'nao');
        };
        
        btnVincular.onclick = () => {
            if (selectedId) {
                modal.classList.add('hidden');
                changeStatus(id, status, 'vincular_' + selectedId);
            }
        };
    }

    /**
     * Gera o link e copia diretamente, garantindo o protocolo completo
     */
    function copiarLinkDireto(id) {
        const originalCursor = document.body.style.cursor;
        document.body.style.cursor = 'wait';

        fetch(`<?= BASE_URL ?>/orcamento/gerarLinkPublico/${id}`)
            .then(res => res.json())
            .then(data => {
                document.body.style.cursor = originalCursor;
                if (data.success) {
                    let url = data.link.trim();
                    
                    // Validação de protocolo no JS (Double-check)
                    if (!url.startsWith('http')) {
                        url = window.location.origin + (url.startsWith('/') ? '' : '/') + url;
                    }
                    
                    navigator.clipboard.writeText(url).then(() => {
                        alert('Link de aprovação copiado!');
                    });
                } else {
                    alert('Erro ao gerar link.');
                }
            })
            .catch(err => {
                document.body.style.cursor = originalCursor;
                console.error(err);
            });
    }

    // ─── Funções de Aprovação do Diretor ───

    const diretorModal = document.getElementById('diretorModal');
    const diretorModalContent = document.getElementById('diretorModalContent');

    function abrirModalDiretor(id) {
        if (!diretorModal || !diretorModalContent) return;

        diretorModal.classList.remove('hidden');
        diretorModalContent.innerHTML = `
            <div class="flex justify-center items-center py-16">
                <div class="animate-spin rounded-full h-12 w-12 border-4 border-indigo-500 border-t-transparent"></div>
                <p class="ml-4 text-gray-600 dark:text-gray-300 font-semibold">Carregando...</p>
            </div>`;

        fetch(`<?= BASE_URL ?>/orcamento/getDiretorModalAjax/${id}`)
            .then(response => response.text())
            .then(html => {
                diretorModalContent.innerHTML = html;
            })
            .catch(error => {
                diretorModalContent.innerHTML = `
                    <div class="p-8 text-center">
                        <i class="fas fa-exclamation-circle text-red-500 text-4xl mb-4"></i>
                        <p class="text-gray-800 dark:text-white font-bold">Erro ao carregar.</p>
                        <button onclick="closeDiretorModal()" class="mt-4 text-indigo-600 underline">Fechar</button>
                    </div>`;
                console.error('Diretor Modal Error:', error);
            });
    }

    function closeDiretorModal() {
        if (diretorModal) diretorModal.classList.add('hidden');
        if (diretorModalContent) diretorModalContent.innerHTML = '';
    }

    function enviarParaDiretor(id) {
        if (!confirm('Enviar esta proposta para aprovação do diretor?')) return;

        const formData = new FormData();
        formData.append('csrf_token', '<?= $csrf_token ?? '' ?>');

        fetch(`<?= BASE_URL ?>/orcamento/enviarParaDiretorAjax/${id}`, {
            method: 'POST',
            body: formData
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.message || 'Erro ao enviar para diretor.');
                }
            })
            .catch(err => {
                console.error(err);
                alert('Erro de comunicação com o servidor.');
            });
    }

    function openPropostaView(id) {
        closeDiretorModal();
        window.open('<?= BASE_URL ?>/orcamento/pdf/' + id, '_blank');
    }

    function aprovarDiretor(id, email, titulo, telefone) {
        Swal.fire({
            title: 'Confirmar aprovação?',
            text: 'Esta ação registrará sua aprovação como diretor.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-check"></i> Sim, aprovar',
            confirmButtonColor: '#059669',
            cancelButtonText: '<i class="fas fa-times"></i> Cancelar',
            cancelButtonColor: '#6b7280',
        }).then(result => {
            if (!result.isConfirmed) return;

            Swal.fire({ title: 'Aprovando...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

            const formData = new FormData();
            formData.append('csrf_token', '<?= $csrf_token ?? '' ?>');

            fetch('<?= BASE_URL ?>/orcamento/aprovarDiretorAjax/' + id, {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                closeDiretorModal();
                if (data.success) {
                    Swal.fire({
                        title: 'Proposta aprovada!',
                        text: 'O que deseja fazer agora?',
                        icon: 'success',
                        showCancelButton: true,
                        showDenyButton: true,
                        confirmButtonText: '<i class="fab fa-whatsapp"></i> WhatsApp',
                        confirmButtonColor: '#25D366',
                        denyButtonText: '<i class="fas fa-envelope"></i> E-mail',
                        denyButtonColor: '#2563eb',
                        cancelButtonText: '<i class="fas fa-check"></i> Fechar',
                        cancelButtonColor: '#6b7280',
                    }).then(result => {
                        if (result.isConfirmed) {
                            enviarWhatsApp(id, titulo, telefone);
                        } else if (result.isDenied) {
                            openEmailModal(id, titulo, email);
                        }
                        window.location.reload();
                    });
                } else {
                    Swal.fire('Erro', data.message || 'Erro ao aprovar proposta.', 'error');
                }
            })
            .catch(err => {
                console.error(err);
                Swal.fire('Erro', 'Falha na comunicação com o servidor.', 'error');
                closeDiretorModal();
            });
        });
    }

    function rejeitarDiretor(id) {
        Swal.fire({
            title: 'Rejeitar Proposta',
            text: 'Informe o motivo da rejeição:',
            icon: 'warning',
            input: 'textarea',
            inputPlaceholder: 'Descreva o motivo da rejeição...',
            inputAttributes: { 'aria-label': 'Justificativa' },
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-times"></i> Rejeitar',
            confirmButtonColor: '#dc2626',
            cancelButtonText: '<i class="fas fa-arrow-left"></i> Voltar',
            cancelButtonColor: '#6b7280',
            preConfirm: (value) => {
                if (!value || value.trim() === '') {
                    Swal.showValidationMessage('A justificativa é obrigatória');
                    return false;
                }
                return value.trim();
            }
        }).then(result => {
            if (!result.isConfirmed) return;

            Swal.fire({ title: 'Rejeitando...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

            const formData = new FormData();
            formData.append('csrf_token', '<?= $csrf_token ?? '' ?>');
            formData.append('justificativa', result.value);

            fetch(`<?= BASE_URL ?>/orcamento/rejeitarDiretorAjax/` + id, {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                closeDiretorModal();
                if (data.success) {
                    Swal.fire({
                        title: 'Proposta rejeitada!',
                        text: 'A proposta foi retornada para edição.',
                        icon: 'info',
                        timer: 2500,
                        showConfirmButton: false
                    }).then(() => window.location.reload());
                } else {
                    Swal.fire('Erro', data.message || 'Erro ao rejeitar proposta.', 'error');
                }
            })
            .catch(err => {
                console.error(err);
                Swal.fire('Erro', 'Falha na comunicação com o servidor.', 'error');
                closeDiretorModal();
            });
        });
    }

    // Fecha os menus quando clica fora
    document.addEventListener('click', function(event) {
        if (!event.target.closest('[class*="status-menu-"]') && !event.target.closest('button[onclick*="toggleStatusMenu"]')) {
            document.querySelectorAll('[class*="status-menu-"]').forEach(m => m.classList.add('hidden'));
        }
    });
</script>