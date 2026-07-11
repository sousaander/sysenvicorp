<?php
$totalContas = is_array($bancos) ? count($bancos) : 0;
$totalAtivas = 0;
$saldoTotal = 0;
if (!empty($bancos)) {
    foreach ($bancos as $b) {
        if (!isset($b['ativo']) || $b['ativo']) $totalAtivas++;
        $saldoTotal += (float) ($b['saldo_inicial'] ?? 0);
    }
}

$tipoLabels = [
    'corrente' => 'Conta Corrente',
    'poupanca' => 'Poupança',
    'caixa'    => 'Caixa Físico',
    'digital'  => 'Conta Digital',
];
$tipoIcons = [
    'corrente' => 'bx-credit-card',
    'poupanca' => 'bx-coin-stack',
    'caixa'    => 'bx-archive',
    'digital'  => 'bx-mobile',
];
?>

<style>
    :root {
        --bk-bg: #f8fafc;
        --bk-panel: #ffffff;
        --bk-panel-2: #f1f5f9;
        --bk-border: #e2e8f0;
        --bk-border-soft: #d1d5db;
        --bk-text: #0f172a;
        --bk-text-dim: #475569;
        --bk-text-faint: #64748b;
        --bk-accent: #10b981;
        --bk-accent-soft: rgba(16, 185, 129, 0.12);
        --bk-danger: #ef4444;
        --bk-radius: 12px;
    }

    .dark-theme .bk-list-wrap {
        --bk-bg: #0f1623;
        --bk-panel: #161f30;
        --bk-panel-2: #1c2538;
        --bk-border: #2a3650;
        --bk-border-soft: #233048;
        --bk-text: #e7ecf5;
        --bk-text-dim: #93a0b8;
        --bk-text-faint: #5d6b85;
        --bk-accent: #10b981;
        --bk-accent-soft: rgba(16, 185, 129, 0.12);
        --bk-danger: #f87171;
    }

    .bk-list-wrap {
        background: var(--bk-bg);
        color: var(--bk-text);
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
    }

    .bk-list-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
    }

    .bk-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        font-size: .7rem;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: var(--bk-accent);
        background: var(--bk-accent-soft);
        border: 1px solid rgba(16,185,129,.25);
        padding: .3rem .65rem;
        border-radius: 999px;
        margin-bottom: .65rem;
    }

    .bk-list-title { font-size: 1.5rem; font-weight: 700; letter-spacing: -0.01em; margin: 0; color: var(--bk-text); }
    .bk-list-subtitle { color: var(--bk-text-dim); font-size: .875rem; margin-top: .35rem; }

    .bk-btn-add {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        font-size: .85rem;
        font-weight: 600;
        padding: .65rem 1.3rem;
        border-radius: 8px;
        background: var(--bk-accent);
        color: #06281c;
        box-shadow: 0 4px 14px rgba(16,185,129,.25);
        text-decoration: none;
        transition: all .15s;
        white-space: nowrap;
    }
    .bk-btn-add:hover { background: #14c98e; box-shadow: 0 6px 18px rgba(16,185,129,.35); }

    /* ===== KPI cards ===== */
    .bk-kpis {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1rem;
        margin-bottom: 1.5rem;
    }
    @media (max-width: 720px) { .bk-kpis { grid-template-columns: 1fr; } }

    .bk-kpi {
        background: var(--bk-panel);
        border: 1px solid var(--bk-border-soft);
        border-radius: var(--bk-radius);
        padding: 1.1rem 1.25rem;
        display: flex;
        align-items: center;
        gap: .9rem;
    }
    .bk-kpi-icon {
        width: 42px; height: 42px;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.25rem;
        flex-shrink: 0;
    }
    .bk-kpi-icon.green { background: var(--bk-accent-soft); color: #34d399; }
    .bk-kpi-icon.blue { background: rgba(59,130,246,.12); color: #60a5fa; }
    .bk-kpi-icon.amber { background: rgba(245,158,11,.12); color: #fbbf24; }

    .bk-kpi-label { font-size: .72rem; color: var(--bk-text-faint); text-transform: uppercase; letter-spacing: .04em; }
    .bk-kpi-value { font-size: 1.25rem; font-weight: 700; color: var(--bk-text); margin-top: .15rem; }

    /* ===== Filtros ===== */
    .bk-toolbar {
        display: flex;
        align-items: center;
        gap: .75rem;
        margin-bottom: 1.1rem;
        flex-wrap: wrap;
    }

    .bk-search {
        position: relative;
        flex: 1;
        min-width: 220px;
    }
    .bk-search i {
        position: absolute; left: .8rem; top: 50%; transform: translateY(-50%);
        color: var(--bk-text-faint); font-size: 1rem;
    }
    .bk-search input {
        width: 100%;
        background: var(--bk-panel-2);
        border: 1px solid var(--bk-border);
        color: var(--bk-text);
        border-radius: 8px;
        padding: .6rem .8rem .6rem 2.3rem;
        font-size: .85rem;
    }
    .bk-search input::placeholder { color: var(--bk-text-faint); }
    .bk-search input:focus { outline: none; border-color: var(--bk-accent); box-shadow: 0 0 0 3px var(--bk-accent-soft); }

    .bk-filter-chip {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        font-size: .78rem;
        font-weight: 600;
        padding: .55rem .9rem;
        border-radius: 8px;
        background: var(--bk-panel-2);
        border: 1px solid var(--bk-border);
        color: var(--bk-text-dim);
        cursor: pointer;
        transition: all .15s;
        white-space: nowrap;
    }
    .bk-filter-chip.active { background: var(--bk-accent-soft); border-color: rgba(16,185,129,.35); color: #34d399; }
    .bk-filter-chip:hover { border-color: var(--bk-border-soft); }

    /* ===== Painel principal ===== */
    .bk-panel-main {
        background: var(--bk-panel);
        border: 1px solid var(--bk-border-soft);
        border-radius: var(--bk-radius);
        overflow: hidden;
    }

    .bk-table { width: 100%; border-collapse: collapse; }
    .bk-table thead th {
        text-align: left;
        font-size: .7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: var(--bk-text-faint);
        padding: .9rem 1.25rem;
        background: var(--bk-panel-2);
        border-bottom: 1px solid var(--bk-border-soft);
    }
    .bk-table thead th.right { text-align: right; }
    .bk-table thead th.center { text-align: center; }

    .bk-table tbody tr {
        border-bottom: 1px solid var(--bk-border-soft);
        transition: background .12s;
    }
    .bk-table tbody tr:last-child { border-bottom: none; }
    .bk-table tbody tr:hover { background: rgba(16, 185, 129, 0.06); }
    .dark-theme .bk-table tbody tr:hover { background: rgba(255, 255, 255, 0.02); }

    .bk-table td { padding: .9rem 1.25rem; font-size: .85rem; color: var(--bk-text); vertical-align: middle; }

    .bk-account-cell { display: flex; align-items: center; gap: .75rem; }
    .bk-account-dot {
        width: 38px; height: 38px;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.1rem;
        flex-shrink: 0;
    }
    .bk-account-logo {
        width: 38px; height: 38px;
        border-radius: 10px;
        object-fit: contain;
        background: var(--bk-panel);
        padding: 2px;
        flex-shrink: 0;
    }
    .bk-account-name { font-weight: 600; color: var(--bk-text); }
    .bk-account-meta { font-size: .75rem; color: var(--bk-text-faint); margin-top: .1rem; }

    .bk-type-badge {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        font-size: .72rem;
        font-weight: 600;
        padding: .3rem .65rem;
        border-radius: 999px;
        background: var(--bk-panel-2);
        border: 1px solid var(--bk-border);
        color: var(--bk-text-dim);
    }

    .bk-balance { font-weight: 700; font-variant-numeric: tabular-nums; }
    .bk-balance.positive { color: #34d399; }
    .bk-balance.negative { color: var(--bk-danger); }

    .bk-status-dot {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        font-size: .72rem;
        font-weight: 600;
        padding: .3rem .6rem;
        border-radius: 999px;
    }
    .bk-status-dot.active { background: rgba(52,211,153,.1); color: #34d399; }
    .bk-status-dot.inactive { background: rgba(148,163,184,.1); color: var(--bk-text-faint); }
    .bk-status-dot .dot { width: 6px; height: 6px; border-radius: 50%; background: currentColor; }

    .bk-row-actions { display: flex; align-items: center; justify-content: flex-end; gap: .35rem; }
    .bk-icon-btn {
        width: 32px; height: 32px;
        display: flex; align-items: center; justify-content: center;
        border-radius: 7px;
        color: var(--bk-text-dim);
        background: transparent;
        border: 1px solid transparent;
        text-decoration: none;
        transition: all .15s;
        cursor: pointer;
        font-size: 1rem;
    }
    .bk-icon-btn:hover { background: var(--bk-panel-2); border-color: var(--bk-border); color: var(--bk-text); }
    .bk-icon-btn.danger:hover { background: rgba(248,113,113,.1); border-color: rgba(248,113,113,.3); color: var(--bk-danger); }

    /* ===== Empty state ===== */
    .bk-empty {
        text-align: center;
        padding: 4rem 2rem;
    }
    .bk-empty-icon {
        width: 60px; height: 60px;
        border-radius: 50%;
        background: var(--bk-panel-2);
        border: 1px solid var(--bk-border);
        display: flex; align-items: center; justify-content: center;
        font-size: 1.6rem;
        color: var(--bk-text-faint);
        margin: 0 auto 1rem;
    }
    .bk-empty-title { font-size: .95rem; font-weight: 600; color: var(--bk-text); }
    .bk-empty-desc { font-size: .82rem; color: var(--bk-text-faint); margin-top: .3rem; }
    .bk-empty-cta {
        display: inline-flex; align-items: center; gap: .4rem;
        margin-top: 1.25rem;
        font-size: .82rem; font-weight: 600;
        padding: .55rem 1.1rem;
        border-radius: 8px;
        background: var(--bk-accent-soft);
        border: 1px solid rgba(16,185,129,.3);
        color: #34d399;
        text-decoration: none;
    }

    @media (max-width: 860px) {
        .bk-table thead { display: none; }
        .bk-table, .bk-table tbody, .bk-table tr, .bk-table td { display: block; width: 100%; }
        .bk-table tr { padding: 1rem 1.25rem; }
        .bk-table td { padding: .35rem 0; border: none; }
        .bk-row-actions { justify-content: flex-start; margin-top: .5rem; }
    }
</style>

<div class="bk-list-wrap">

    <div class="bk-list-header">
        <div>
            <span class="bk-eyebrow"><i class="bx bx-buildings"></i> Módulo Financeiro</span>
            <h2 class="bk-list-title"><?php echo htmlspecialchars($pageTitle); ?></h2>
            <p class="bk-list-subtitle">Adicione, edite ou remova as contas bancárias e caixas da empresa.</p>
        </div>
        <a href="<?php echo BASE_URL; ?>/banco/form" class="bk-btn-add">
            <i class="bx bx-plus"></i> Adicionar Banco
        </a>
    </div>

    <!-- KPIs -->
    <div class="bk-kpis">
        <div class="bk-kpi">
            <div class="bk-kpi-icon green"><i class="bx bx-wallet"></i></div>
            <div>
                <div class="bk-kpi-label">Saldo Total</div>
                <div class="bk-kpi-value">R$ <?php echo number_format($saldoTotal, 2, ',', '.'); ?></div>
            </div>
        </div>
        <div class="bk-kpi">
            <div class="bk-kpi-icon blue"><i class="bx bx-buildings"></i></div>
            <div>
                <div class="bk-kpi-label">Contas Cadastradas</div>
                <div class="bk-kpi-value"><?php echo $totalContas; ?></div>
            </div>
        </div>
        <div class="bk-kpi">
            <div class="bk-kpi-icon amber"><i class="bx bx-check-shield"></i></div>
            <div>
                <div class="bk-kpi-label">Contas Ativas</div>
                <div class="bk-kpi-value"><?php echo $totalAtivas; ?> / <?php echo $totalContas; ?></div>
            </div>
        </div>
    </div>

    <!-- Toolbar -->
    <div class="bk-toolbar">
        <div class="bk-search">
            <i class="bx bx-search"></i>
            <input type="text" id="bkBuscarConta" placeholder="Buscar por nome da conta...">
        </div>
        <div class="bk-filter-chip active" data-filtro="todos"><i class="bx bx-list-ul"></i> Todos</div>
        <div class="bk-filter-chip" data-filtro="ativo"><i class="bx bx-check-circle"></i> Ativos</div>
        <div class="bk-filter-chip" data-filtro="inativo"><i class="bx bx-x-circle"></i> Inativos</div>
    </div>

    <!-- Tabela -->
    <div class="bk-panel-main">
        <?php if (!empty($bancos)): ?>
            <table class="bk-table" id="bkTabelaBancos">
                <thead>
                    <tr>
                        <th>Conta</th>
                        <th>Tipo</th>
                        <th class="right">Saldo Inicial</th>
                        <th class="center">Status</th>
                        <th class="right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bancos as $banco):
                        $tipoChave = strtolower($banco['tipo'] ?? 'corrente');
                        // Compatibilidade: se vier texto livre (versão antiga), tenta mapear, senão usa como veio
                        $tipoLabel = $tipoLabels[$tipoChave] ?? htmlspecialchars($banco['tipo'] ?? '-');
                        $tipoIcon  = $tipoIcons[$tipoChave] ?? 'bx-credit-card';
                        $cor       = $banco['cor'] ?? '#10b981';
                        $ativo     = !isset($banco['ativo']) || $banco['ativo'];
                        $saldo     = (float) ($banco['saldo_inicial'] ?? 0);
                        $iniciais  = strtoupper(mb_substr($banco['nome'], 0, 1));
                    ?>
                        <tr data-status="<?php echo $ativo ? 'ativo' : 'inativo'; ?>" data-nome="<?php echo strtolower(htmlspecialchars($banco['nome'])); ?>">
                            <td>
                                <div class="bk-account-cell">
                                    <?php if (!empty($banco['logo'])): ?>
                                        <img src="<?php echo BASE_URL; ?>/public/uploads/bancos/<?php echo htmlspecialchars($banco['logo']); ?>" 
                                            alt="<?php echo htmlspecialchars($banco['nome']); ?>" 
                                            class="bk-account-logo">
                                    <?php else: ?>
                                        <div class="bk-account-dot" style="background: <?php echo htmlspecialchars($cor); ?>22; color: <?php echo htmlspecialchars($cor); ?>;">
                                            <?php echo $iniciais; ?>
                                        </div>
                                    <?php endif; ?>
                                    <div>
                                        <div class="bk-account-name"><?php echo htmlspecialchars($banco['nome']); ?></div>
                                        <?php if (!empty($banco['agencia']) || !empty($banco['conta'])): ?>
                                            <div class="bk-account-meta">
                                                <?php if (!empty($banco['agencia'])): ?>
                                                    Ag. <?= htmlspecialchars($banco['agencia']) . (!empty($banco['agencia_dv']) ? '-' . htmlspecialchars($banco['agencia_dv']) : '') ?>
                                                <?php endif; ?>
                                                <?php if (!empty($banco['agencia']) && !empty($banco['conta'])): ?> &middot; <?php endif; ?>
                                                <?php if (!empty($banco['conta'])): ?>
                                                    Cc. <?= htmlspecialchars($banco['conta']) . (!empty($banco['conta_dv']) ? '-' . htmlspecialchars($banco['conta_dv']) : '') ?>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (!empty($banco['nome_titular'])): ?>
                                            <div class="bk-account-titular" style="font-size:11px;color:#6B7280;margin-top:1px">
                                                <i class="bx bx-user" style="font-size:11px;margin-right:2px"></i><?php echo htmlspecialchars($banco['nome_titular']); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="bk-type-badge"><i class="bx <?php echo $tipoIcon; ?>"></i> <?php echo $tipoLabel; ?></span>
                            </td>
                            <td class="right">
                                <span class="bk-balance <?php echo $saldo >= 0 ? 'positive' : 'negative'; ?>">
                                    R$ <?php echo number_format($saldo, 2, ',', '.'); ?>
                                </span>
                            </td>
                            <td class="center">
                                <span class="bk-status-dot <?php echo $ativo ? 'active' : 'inactive'; ?>">
                                    <span class="dot"></span><?php echo $ativo ? 'Ativo' : 'Inativo'; ?>
                                </span>
                            </td>
                            <td>
                                <div class="bk-row-actions">
                                    <a href="<?php echo BASE_URL; ?>/banco/form/<?php echo $banco['id']; ?>" class="bk-icon-btn" title="Editar">
                                        <i class="bx bx-edit-alt"></i>
                                    </a>
                                    <a href="<?php echo BASE_URL; ?>/banco/excluir/<?php echo $banco['id']; ?>" class="bk-icon-btn danger" title="Excluir"
                                        onclick="return confirm('Tem certeza que deseja excluir este banco? Esta ação não pode ser desfeita.');">
                                        <i class="bx bx-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="bk-empty">
                <div class="bk-empty-icon"><i class="bx bx-buildings"></i></div>
                <div class="bk-empty-title">Nenhum banco cadastrado</div>
                <div class="bk-empty-desc">Cadastre a primeira conta bancária ou caixa da empresa para começar.</div>
                <a href="<?php echo BASE_URL; ?>/banco/form" class="bk-empty-cta"><i class="bx bx-plus"></i> Adicionar Banco</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
(function() {
    const busca = document.getElementById('bkBuscarConta');
    const chips = document.querySelectorAll('.bk-filter-chip');
    const linhas = document.querySelectorAll('#bkTabelaBancos tbody tr');
    let filtroAtual = 'todos';

    function aplicarFiltros() {
        const termo = (busca?.value || '').toLowerCase().trim();
        linhas.forEach(function(linha) {
            const nome = linha.getAttribute('data-nome') || '';
            const status = linha.getAttribute('data-status');
            const condNome = nome.includes(termo);
            const condStatus = filtroAtual === 'todos' || status === filtroAtual;
            linha.style.display = (condNome && condStatus) ? '' : 'none';
        });
    }

    if (busca) busca.addEventListener('input', aplicarFiltros);

    chips.forEach(function(chip) {
        chip.addEventListener('click', function() {
            chips.forEach(function(c) { c.classList.remove('active'); });
            chip.classList.add('active');
            filtroAtual = chip.getAttribute('data-filtro');
            aplicarFiltros();
        });
    });
})();
</script>
