<?php
/**
 * Radar de Oportunidades IA - Visual Redesign
 * Estilo moderno seguindo o padrão UI/UX do módulo de Licitações.
 */
?>
<style>
    :root {
        --bg-base: #f0f4fa;
        --bg-surface: #ffffff;
        --bg-elevated:  #f8fafc;
        --border-soft:  rgba(0,0,0,0.06);
        --border-mid:   rgba(0,0,0,0.12);
        --border-accent:rgba(37,99,235,0.2);

        --text-primary:  #1e293b;
        --text-secondary:#475569;
        --text-tertiary: #94a3b8;

        --accent:        #2563eb;
        --accent-glow:   rgba(37,99,235,0.1);
        --accent-dim:    rgba(37,99,235,0.05);
        --accent-hover:  #1d4ed8;

        --green:         #10b981;
        --green-bg:      rgba(16,185,129,0.1);
        --amber:         #f59e0b;
        --amber-bg:      rgba(245,158,11,0.1);
        --red:           #ef4444;
        --red-bg:        rgba(239,68,68,0.1);
        --purple:        #8b5cf6;
        --purple-bg:     rgba(139,92,246,0.1);

        --radius-md:     10px;
        --radius-lg:     14px;
        --font-ui:      'Sora', sans-serif;
        --font-mono:    'JetBrains Mono', monospace;
        --transition:   all 0.22s cubic-bezier(0.4,0,0.2,1);
    }

    body.dark-theme {
        --bg-base: #0b0f1a;
        --bg-surface: #111827;
        --bg-elevated:  #1a2235;
        --border-soft:  rgba(255,255,255,0.06);
        --border-mid:   rgba(255,255,255,0.12);
        --border-accent:rgba(56,189,248,0.35);

        --text-primary:  #f0f6ff;
        --text-secondary:#8fa3c0;
        --text-tertiary: #64748b;

        --accent:        #38bdf8;
        --accent-glow:   rgba(56,189,248,0.15);
        --accent-dim:    rgba(56,189,248,0.08);
        --accent-hover:  #7dd3fc;

        --green:         #34d399;
        --green-bg:      rgba(52,211,153,0.1);
        --amber:         #fbbf24;
        --amber-bg:      rgba(251,191,36,0.1);
        --red:           #f87171;
        --red-bg:        rgba(248,113,113,0.1);
        --purple:        #a78bfa;
        --purple-bg:     rgba(167,139,250,0.1);
    }

    .radar-page { background: var(--bg-base); min-height: 100vh; font-family: var(--font-ui); color: var(--text-primary); padding: 28px 32px 48px; }

    /* --- Header --- */
    .radar-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 20px; flex-wrap: wrap; margin-bottom: 28px; }
    .radar-header-left { display: flex; flex-direction: column; gap: 6px; }
    .breadcrumb { display: flex; align-items: center; gap: 8px; font-size: 11.5px; color: var(--text-tertiary); letter-spacing: 0.4px; }
    .breadcrumb a { color: var(--accent); text-decoration: none; transition: var(--transition); }
    .breadcrumb a:hover { color: var(--accent-hover); }
    .breadcrumb-sep { opacity: 0.35; }
    .header-title { font-size: 22px; font-weight: 700; color: var(--text-primary); display: flex; align-items: center; gap: 10px; letter-spacing: -0.3px; }
    .header-title i { color: var(--accent); font-size: 15px; }
    .header-sub { font-size: 12.5px; color: var(--text-secondary); }

    /* --- Grid & Cards --- */
    .radar-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 16px; }
    
    .captacao-card {
        background: var(--bg-surface);
        border: 1px solid var(--border-soft);
        border-radius: var(--radius-lg);
        padding: 24px;
        position: relative;
        transition: all 0.25s cubic-bezier(0.4,0,0.2,1);
        display: flex;
        flex-direction: column;
        height: 100%;
    }

    .captacao-card:hover {
        border-color: var(--border-mid);
        box-shadow: 0 8px 20px rgba(0,0,0,0.3);
    }

    .card-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; }
    
    .portal-badge {
        font-size: 10.5px;
        font-weight: 700;
        text-transform: capitalize;
        background: var(--accent-dim);
        color: var(--accent);
        padding: 3px 10px;
        border-radius: 6px;
        border: 1px solid var(--border-accent);
    }

    .cap-date { font-family: var(--font-mono); font-size: 11.5px; color: var(--text-secondary); }
    
    .cap-orgao { font-size: 11px; font-weight: 600; text-transform: uppercase; color: var(--text-tertiary); margin-bottom: 8px; letter-spacing: 0.5px; }
    .cap-objeto { font-size: 14px; font-weight: 500; color: var(--text-primary); line-height: 1.6; margin-bottom: 20px; flex-grow: 1; }

    .cap-footer {
        margin-top: auto;
        padding-top: 18px;
        border-top: 1px solid var(--border-soft);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .val-label { font-size: 10px; font-weight: 600; color: var(--text-tertiary); text-transform: uppercase; margin-bottom: 2px; }
    .val-amount { font-family: var(--font-mono); font-size: 17px; font-weight: 600; color: var(--green); }

    /* --- Actions --- */
    .actions-group { display: flex; gap: 8px; }
    .btn-action {
        width: 34px; height: 34px;
        border-radius: 8px;
        background: var(--bg-elevated);
        border: 1px solid var(--border-soft);
        color: var(--text-tertiary);
        display: flex; align-items: center; justify-content: center;
        transition: var(--transition);
        cursor: pointer;
        padding: 0; /* Remove default button padding */
    }
    .btn-action:hover { border-color: var(--border-mid); color: var(--text-primary); transform: scale(1.05); }
    
    .btn-fav.active { color: var(--amber); border-color: rgba(251,191,36,0.3); background: var(--amber-bg); }
    .btn-ignore:hover { color: var(--red); border-color: rgba(248,113,113,0.3); background: var(--red-bg); }

    .btn-convert {
        background: var(--accent);
        color: var(--bg-base);
        font-weight: 700;
        font-size: 12.5px;
        padding: 0 16px;
        height: 34px;
        border-radius: 8px;
        border: none;
        display: flex; align-items: center; gap: 6px;
        transition: var(--transition);
        text-decoration: none; /* For anchor tags */
    }
    .btn-convert:hover { background: var(--accent-hover); transform: translateY(-1px); box-shadow: 0 4px 12px var(--accent-glow); }

    /* --- Empty State --- */
    .empty-state {
        text-align: center;
        padding: 56px 24px;
        background: var(--bg-surface);
        border: 1px solid var(--border-soft);
        border-radius: var(--radius-lg);
        margin-top: 20px;
    }
    .empty-icon {
        width: 64px; height: 64px;
        background: var(--bg-elevated);
        border: 1px solid var(--border-soft);
        border-radius: var(--radius-lg);
        display: flex; align-items: center; justify-content: center;
        font-size: 24px;
        color: var(--text-tertiary);
        margin: 0 auto 16px;
    }
    .empty-title { font-size: 14px; font-weight: 600; color: var(--text-primary); margin-bottom: 6px; }
    .empty-sub { font-size: 12.5px; color: var(--text-secondary); }

    /* --- Responsive --- */
    @media (max-width: 768px) {
        .radar-page { padding: 16px 16px 32px; }
        .radar-grid { grid-template-columns: 1fr; }
        .radar-header { flex-direction: column; align-items: flex-start; }
        .radar-header-actions { width: 100%; justify-content: flex-end; }
        .btn-convert { width: 100%; justify-content: center; }
    }
    @media (max-width: 480px) {
        .cap-footer { flex-direction: column; align-items: flex-start; gap: 12px; }
        .actions-group { width: 100%; justify-content: space-between; }
        .btn-action { flex-grow: 1; }
        .btn-convert { width: 100%; }
    }
</style>

<div class="radar-page">
    <!-- Header -->
    <div class="radar-header">
        <div class="radar-header-left">
            <div class="breadcrumb">
                <a href="<?= BASE_URL ?>/licitacoes">Licitações</a>
                <span class="breadcrumb-sep">/</span>
                <span>Radar IA</span>
            </div>
            <h1 class="header-title">
                <i class="fas fa-bolt"></i> Radar de Oportunidades IA
            </h1>
            <p class="header-sub">Novos editais detectados nas últimas varreduras.</p>
        </div>
        <div class="radar-header-actions">
            <a href="<?= BASE_URL ?>/licitacoes" class="btn-convert" style="background: var(--bg-elevated); color: var(--text-secondary); border: 1px solid var(--border-mid);">
                <i class="fas fa-arrow-left"></i> Voltar ao Painel
            </a>
        </div>
    </div>

    <div class="radar-grid">
        <?php if (!empty($captacoes)): ?>
            <?php foreach ($captacoes as $cap): ?>
                <div class="captacao-card">
                    <div class="card-top">
                        <span class="portal-badge"><?= htmlspecialchars($cap['portal_origem']) ?></span>
                        <span class="cap-date"><?= date('d/m/Y H:i', strtotime($cap['captado_em'])) ?></span>
                    </div>
                    
                    <div class="cap-orgao"><?= htmlspecialchars($cap['orgao_externo']) ?></div>
                    <div class="cap-objeto">
                        <?= mb_strimwidth(htmlspecialchars($cap['objeto']), 0, 150, "...") ?>
                    </div>

                    <div class="cap-footer">
                        <div>
                            <div class="val-label">Valor Estimado</div>
                            <div class="val-amount">R$ <?= number_format($cap['valor_estimado'], 2, ',', '.') ?></div>
                        </div>
                        
                        <div class="actions-group">
                            <form action="<?= BASE_URL ?>/licitacoes/favoritar/<?= $cap['id'] ?>" method="POST">
                                <button type="submit" class="btn-action btn-fav <?= $cap['favorito'] ? 'active' : '' ?>" title="Marcar como favorito">
                                        <i class="<?= $cap['favorito'] ? 'fas' : 'far' ?> fa-star"></i>
                                </button>
                            </form>
                            <form action="<?= BASE_URL ?>/licitacoes/ignorar/<?= $cap['id'] ?>" method="POST" onsubmit="return confirm('Deseja realmente ignorar esta oportunidade?')">
                                <button type="submit" class="btn-action btn-ignore" title="Ignorar">
                                        <i class="fas fa-times"></i>
                                </button>
                            </form>
                            <a href="<?= BASE_URL ?>/licitacoes/novo?captacao_id=<?= $cap['id'] ?>" class="btn-convert">
                                <i class="fas fa-file-import"></i> Converter
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-icon"><i class="fas fa-satellite-dish"></i></div>
                <div class="empty-title">Nenhuma nova oportunidade captada</div>
                <div class="empty-sub">O Agente IA está trabalhando. Verifique as <a href="<?= BASE_URL ?>/licitacoes/agenteIA" style="color:var(--accent);">configurações</a> ou aguarde a próxima varredura.</div>
            </div>
        <?php endif; ?>
    </div>
</div>