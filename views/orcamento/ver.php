<?php
// views/ver.php
// $orc, $historico, $statusLabels
$sl = ($statusLabels ?? [])[$orc['status'] ?? 'Rascunho'] ?? ['label' => $orc['status'] ?? 'Indefinido', 'cor' => 'secondary'];
?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Inter:wght@400;500;600;700;800&family=IBM+Plex+Mono:wght@500;600;700&display=swap" rel="stylesheet">
<style>
    :root {
        --bg-page: #f1f5f9;
        --bg-surface: #ffffff;
        --bg-surface-alt: #f8fafc;
        --bg-surface-raised: #ffffff;
        --border-hairline: #e2e8f0;
        --border-strong: #cbd5e1;
        --text-primary: #0f172a;
        --text-secondary: #475569;
        --text-tertiary: #94a3b8;
        --accent-primary: #2563eb;
        --accent-primary-dim: #1d4ed8;
        --accent-primary-soft: rgba(37, 99, 235, 0.68);
        --accent-emerald: #059669;
        --accent-rose: #e11d48;
        --accent-amber: #d97706;
        --font-display: 'Plus Jakarta Sans', -apple-system, sans-serif;
        --font-body: 'Plus Jakarta Sans', -apple-system, sans-serif;
        --font-mono: 'IBM Plex Mono', 'Courier New', monospace;
    }

    body.dark-theme {
        --bg-page: #0a0e17;
        --bg-surface: #131a2b;
        --bg-surface-alt: #1a2338;
        --bg-surface-raised: #1c2540;
        --border-hairline: #232d42;
        --border-strong: #2d3a54;
        --text-primary: #f1f5f9;
        --text-secondary: #94a3b8;
        --text-tertiary: #64748b;
        --accent-primary: #60a5fa;
        --accent-primary-dim: #93c5fd;
        --accent-primary-soft: rgba(96, 165, 250, 0.14);
        --accent-emerald: #34d399;
        --accent-rose: #fb7185;
        --accent-amber: #fbbf24;
    }

    .proposta-view-container { max-width: 1260px; margin: 0 auto; font-family: var(--font-body); color: var(--text-primary); }
    .proposta-view-container * { box-sizing: border-box; }
    .proposta-view-shell { background: var(--bg-page); border-radius: 20px; padding: 2rem 2rem 2.5rem; background-image: radial-gradient(circle at 0% 0%, rgba(37,99,235,0.04), transparent 45%); }

    body.dark-theme .proposta-view-shell { background-image: radial-gradient(circle at 0% 0%, rgba(37,99,235,0.06), transparent 45%); }

    /* ===== Letterhead / Cabecalho ===== */
    .letterhead { position: relative; border: 1px solid var(--border-hairline); background: var(--bg-surface); border-radius: 16px; padding: 1.85rem 2rem; margin-bottom: 1.5rem; border-top: 2px solid var(--accent-primary); overflow: hidden; }
    .lh-breadcrumb { list-style: none; display: flex; align-items: center; gap: 0.5rem; padding: 0; margin: 0 0 0.9rem; font-family: var(--font-mono); font-size: 10.5px; text-transform: uppercase; letter-spacing: 0.12em; color: var(--text-tertiary); }
    .lh-breadcrumb a { color: var(--text-tertiary); text-decoration: none; transition: color .15s; }
    .lh-breadcrumb a:hover { color: var(--accent-primary); }
    .lh-breadcrumb .sep { opacity: .4; }
    .lh-breadcrumb .current { color: var(--accent-primary); }
    .lh-title { font-family: var(--font-display); font-weight: 600; font-size: 2rem; line-height: 1.15; margin: 0 0 0.65rem; color: var(--text-primary); display: flex; align-items: center; flex-wrap: wrap; gap: 0.65rem; }
    .lh-title .doc-number { font-family: var(--font-mono); font-size: 1.05rem; font-weight: 700; color: var(--accent-primary); background: var(--accent-primary-soft); border: 1px solid rgba(37,99,235,0.3); border-radius: 8px; padding: 0.3rem 0.75rem; letter-spacing: 0.03em; }
    .lh-meta { display: flex; align-items: center; gap: 0.9rem; font-size: 12.5px; color: var(--text-secondary); font-weight: 500; }
    .lh-meta .divider { width: 3px; height: 3px; border-radius: 50%; background: var(--border-strong); }
    .lh-meta i { color: var(--text-tertiary); margin-right: 0.3rem; }

    .lh-top-row { display: flex; justify-content: space-between; align-items: flex-start; gap: 1.5rem; }
    .lh-actions { display: flex; align-items: center; gap: 0.6rem; flex-wrap: wrap; }

    .status-tag { position: relative; display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.5rem 1.4rem 0.5rem 0.9rem; font-family: var(--font-mono); font-size: 10.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; clip-path: polygon(0 0, 88% 0, 100% 50%, 88% 100%, 0 100%); }
    .status-tag .dot { width: 6px; height: 6px; border-radius: 50%; }
    .status-tag.tag-success, .status-tag.tag-verde { background: rgba(52,211,153,0.14); color: var(--accent-emerald); }
    .status-tag.tag-success .dot, .status-tag.tag-verde .dot { background: var(--accent-emerald); }
    .status-tag.tag-danger, .status-tag.tag-vermelho { background: rgba(251,113,133,0.14); color: var(--accent-rose); }
    .status-tag.tag-danger .dot, .status-tag.tag-vermelho .dot { background: var(--accent-rose); }
    .status-tag.tag-warning, .status-tag.tag-amarelo { background: rgba(251,191,36,0.14); color: var(--accent-amber); }
    .status-tag.tag-warning .dot, .status-tag.tag-amarelo .dot { background: var(--accent-amber); }
    .status-tag.tag-info, .status-tag.tag-primary, .status-tag.tag-azul { background: rgba(37,99,235,0.14); color: var(--accent-primary); }
    .status-tag.tag-info .dot, .status-tag.tag-primary .dot, .status-tag.tag-azul .dot { background: var(--accent-primary); }
    .status-tag.tag-secondary, .status-tag.tag-cinza { background: rgba(148,163,184,0.14); color: var(--text-secondary); }
    .status-tag.tag-secondary .dot, .status-tag.tag-cinza .dot { background: var(--text-secondary); }

    .btn-back { border-radius: 10px; font-weight: 700; font-size: 12.5px; padding: 0.55rem 1rem; color: #fff; background: linear-gradient(135deg, var(--accent-primary) 0%, var(--accent-primary-dim) 100%); border: none; display: inline-flex; align-items: center; gap: 0.4rem; text-decoration: none; transition: transform .15s ease, filter .15s ease; }
    .btn-back:hover { transform: translateY(-1px); filter: brightness(1.08); color: #fff; text-decoration: none; }
    .btn-back:active { transform: translateY(0); }

    .btn-pdf { border-radius: 10px; font-weight: 700; font-size: 12.5px; padding: 0.55rem 1rem; color: #fff; background: #e11d48; border: none; display: inline-flex; align-items: center; gap: 0.4rem; text-decoration: none; transition: filter .15s ease; }
    .btn-pdf:hover { filter: brightness(1.1); color: #fff; text-decoration: none; }

    /* ===== Action bar ===== */
    .action-bar { background: var(--bg-surface); border: 1px solid var(--border-hairline); border-radius: 14px; padding: 0.85rem 1.1rem; margin-bottom: 1.5rem; display: flex; flex-wrap: wrap; align-items: center; gap: 0.55rem; }
    .btn-pill { border-radius: 999px; font-size: 12.5px; font-weight: 600; padding: 0.5rem 1rem; display: inline-flex; align-items: center; gap: 0.45rem; background: var(--bg-surface-alt); border: 1px solid var(--border-strong); color: var(--text-secondary); transition: all .15s ease; }
    .btn-pill:hover { border-color: var(--accent-primary); color: var(--accent-primary); background: rgba(37,99,235,0.08); }
    .btn-pill.pill-danger:hover { border-color: var(--accent-rose); color: var(--accent-rose); background: rgba(251,113,133,0.08); }
    .btn-pill.pill-success:hover { border-color: var(--accent-emerald); color: var(--accent-emerald); background: rgba(52,211,153,0.08); }
    .btn-pill-sm { border-radius: 999px; font-size: 11px; font-weight: 600; padding: 0.3rem 0.7rem; display: inline-flex; align-items: center; gap: 0.35rem; background: transparent; border: 1px solid var(--border-strong); color: var(--text-tertiary); transition: all .15s ease; cursor: pointer; flex-shrink: 0; margin-left: auto; }
    .btn-pill-sm:hover { border-color: var(--accent-rose); color: var(--accent-rose); background: rgba(251,113,133,0.08); }

    /* ===== Section cards (dossie) ===== */
    .section-card { background: var(--bg-surface); border: 1px solid var(--border-hairline); border-radius: 14px; margin-bottom: 1.35rem; overflow: hidden; }
    .section-head { display: flex; align-items: center; gap: 0.85rem; padding: 1.05rem 1.4rem; border-bottom: 1px solid var(--border-hairline); background: var(--bg-surface-alt); }
    .section-num { font-family: var(--font-mono); font-size: 11px; font-weight: 700; color: var(--accent-primary); border: 1px solid rgba(37,99,235,0.35); border-radius: 6px; padding: 0.2rem 0.5rem; letter-spacing: 0.05em; flex-shrink: 0; }
    .section-title { font-family: var(--font-display); font-size: 1.05rem; font-weight: 600; color: var(--text-primary); letter-spacing: 0.005em; }
    .section-title i { color: var(--accent-primary); margin-right: 0.55rem; font-size: 0.95rem; }
    .section-body { padding: 1.5rem 1.4rem; }

    .info-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem 1.75rem; }
    .label-muted { font-family: var(--font-mono); font-size: 10.5px; font-weight: 600; color: var(--text-tertiary); text-transform: uppercase; letter-spacing: 0.09em; margin-bottom: 0.35rem; display: block; }
    .value-highlight { font-weight: 600; font-size: 14.5px; color: var(--text-primary); }
    .value-sub { font-size: 12.5px; color: var(--text-secondary); margin-top: 0.15rem; }
    .divider-soft { border-top: 1px solid var(--border-hairline); margin-top: 1.3rem; padding-top: 1.1rem; }

    /* ===== Tabela de itens ===== */
    .table-ledger { width: 100%; margin: 0; border-collapse: collapse; }
    .table-ledger thead th { background: var(--bg-surface-alt); font-family: var(--font-mono); font-size: 10px; letter-spacing: 0.08em; text-transform: uppercase; color: var(--text-tertiary); padding: 0.85rem 1.4rem; border-bottom: 1px solid var(--border-hairline); font-weight: 600; text-align: left; }
    .table-ledger tbody td { padding: 0.95rem 1.4rem; border-bottom: 1px solid var(--border-hairline); color: var(--text-secondary); font-size: 13.5px; vertical-align: middle; }
    .table-ledger tbody tr:last-child td { border-bottom: none; }
    .table-ledger tbody tr:hover { background: rgba(37,99,235,0.03); }
    .item-idx { font-family: var(--font-mono); color: var(--text-tertiary); font-size: 12px; }
    .item-desc-title { font-weight: 600; color: var(--text-primary); font-size: 13.5px; }
    .item-desc-sub { font-size: 12px; color: var(--text-tertiary); font-style: italic; margin-top: 0.15rem; }
    .item-mono { font-family: var(--font-mono); color: var(--text-secondary); }
    .item-total { font-family: var(--font-mono); font-weight: 700; color: var(--text-primary); }

    .item-section-title { font-weight: 700; font-size: 14px; color: var(--accent-primary-dim); padding: 0.6rem 1.4rem; background: var(--bg-surface-alt); border-bottom: 1px solid var(--border-hairline); }
    .item-section-title .sub-num { font-family: var(--font-mono); font-size: 11px; color: var(--accent-primary); margin-right: 0.5rem; }
    .item-text-row td { padding: 0.6rem 1.4rem !important; }
    .item-text-content { font-size: 13px; color: var(--text-secondary); line-height: 1.7; text-align: justify; padding: 0.2rem 0; }
    .item-legend { font-size: 11px; font-weight: 700; color: var(--accent-primary); text-transform: uppercase; letter-spacing: 0.05em; }

    .ledger-summary { background: var(--bg-surface-alt); padding: 1.4rem; border-top: 1px solid var(--border-hairline); }
    .ledger-row { display: flex; justify-content: space-between; align-items: baseline; padding: 0.4rem 0; font-size: 13px; color: var(--text-secondary); border-bottom: 1px dashed var(--border-hairline); }
    .ledger-row .val { font-family: var(--font-mono); color: var(--text-primary); font-weight: 600; }
    .ledger-row.discount .val { color: var(--accent-rose); }
    .ledger-row.tax .val { color: var(--accent-amber); }
    .ledger-total-row { margin-top: 0.9rem; padding-top: 1rem; border-top: 2px solid var(--accent-primary); display: flex; justify-content: space-between; align-items: baseline; }
    .ledger-total-row .lbl { font-family: var(--font-display); font-size: 1rem; font-weight: 600; color: var(--text-primary); text-transform: uppercase; letter-spacing: 0.04em; }
    .ledger-total-row .val { font-family: var(--font-mono); font-size: 1.5rem; font-weight: 700; color: var(--accent-primary); }

    .empty-state { padding: 3rem 1.5rem; text-align: center; color: var(--text-tertiary); }
    .empty-state i { font-size: 1.8rem; margin-bottom: 0.85rem; display: block; opacity: 0.6; }

    /* ===== Cronograma ===== */
    .crono-scroll { overflow-x: auto; }
    .table-crono { width: 100%; min-width: 640px; border-collapse: collapse; font-size: 10.5px; }
    .table-crono th { background: var(--bg-surface-alt); color: var(--text-tertiary); font-family: var(--font-mono); font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; padding: 0.5rem 0.2rem; text-align: center; border: 1px solid var(--border-hairline); min-width: 34px; width: 34px; }
    .table-crono th:first-child { text-align: left; min-width: 180px; width: 180px; }
    .table-crono td { border: 1px solid var(--border-hairline); height: 22px; padding: 0; }
    .table-crono td.crono-label { background: var(--bg-surface-alt); font-weight: 600; color: var(--text-secondary); padding: 0.4rem 0.6rem; font-family: var(--font-body); font-size: 12px; }
    .crono-office { background: var(--accent-primary-dim); }
    .crono-field { background: var(--accent-emerald); }
    .crono-legend { display: flex; gap: 1.25rem; padding: 0.85rem 1.4rem; font-family: var(--font-mono); font-size: 9.5px; text-transform: uppercase; letter-spacing: 0.06em; color: var(--text-tertiary); border-top: 1px solid var(--border-hairline); }
    .crono-legend span { display: inline-flex; align-items: center; gap: 0.4rem; }
    .crono-swatch { width: 10px; height: 10px; border-radius: 2px; display: inline-block; }

    /* ===== Observacoes ===== */
    .note-box { background: rgba(251,191,36,0.07); border: 1px solid rgba(251,191,36,0.2); border-left: 3px solid var(--accent-amber); border-radius: 8px; padding: 1rem 1.2rem; font-size: 13.5px; color: var(--text-primary); line-height: 1.6; }

    /* ===== Sidebar: certificado de valor ===== */
    .certificate-card { position: relative; background: linear-gradient(165deg, var(--bg-surface-raised) 0%, var(--bg-surface) 100%); border: 1px solid var(--border-strong); border-radius: 16px; padding: 1.75rem 1.6rem; margin-bottom: 1.35rem; }
    .certificate-card::before { content: ''; position: absolute; top: 10px; left: 10px; width: 14px; height: 14px; border-top: 2px solid var(--accent-primary); border-left: 2px solid var(--accent-primary); opacity: 0.6; }
    .certificate-card::after { content: ''; position: absolute; bottom: 10px; right: 10px; width: 14px; height: 14px; border-bottom: 2px solid var(--accent-primary); border-right: 2px solid var(--accent-primary); opacity: 0.6; }
    .cert-eyebrow { font-family: var(--font-mono); font-size: 10px; letter-spacing: 0.14em; text-transform: uppercase; color: var(--accent-primary); margin-bottom: 0.5rem; }
    .cert-value { font-family: var(--font-display); font-size: 2.1rem; font-weight: 600; color: var(--text-primary); line-height: 1.1; margin-bottom: 1.2rem; }
    .cert-row { display: flex; justify-content: space-between; align-items: center; font-size: 12px; padding: 0.5rem 0; border-top: 1px solid var(--border-hairline); color: var(--text-secondary); }
    .cert-row span:first-child { text-transform: uppercase; letter-spacing: 0.06em; font-size: 10.5px; font-family: var(--font-mono); }
    .cert-row .cert-val { font-weight: 700; color: var(--text-primary); }

    .side-card-link { color: var(--accent-primary); text-decoration: none; font-weight: 600; }
    .side-card-link:hover { color: #7dd3fc; text-decoration: underline; }
    .side-hint { font-size: 11.5px; color: var(--text-tertiary); margin-top: 0.65rem; display: flex; align-items: flex-start; gap: 0.4rem; }

    .approved-card { border-top: 3px solid var(--accent-emerald) !important; }
    .approved-card .section-title { color: var(--accent-emerald); }
    .approved-card .section-title i { color: var(--accent-emerald); }

    /* ===== Timeline ===== */
    .timeline-modern { position: relative; padding-left: 1.5rem; }
    .timeline-modern::before { content: ''; position: absolute; left: 4px; top: 4px; bottom: 4px; width: 1px; background: var(--border-strong); }
    .timeline-item { position: relative; padding-bottom: 1.4rem; }
    .timeline-item:last-child { padding-bottom: 0; }
    .timeline-marker { position: absolute; left: -1.5rem; width: 9px; height: 9px; border-radius: 50%; border: 2px solid var(--bg-surface); background: var(--accent-primary); top: 3px; }
    .timeline-date { font-family: var(--font-mono); font-size: 10.5px; color: var(--text-tertiary); font-weight: 600; margin-bottom: 0.2rem; letter-spacing: 0.03em; }
    .timeline-status { font-size: 13px; font-weight: 600; color: var(--text-primary); }
    .timeline-user { font-size: 11.5px; color: var(--text-tertiary); font-weight: 400; }
    .timeline-motivo { font-size: 12px; color: var(--text-secondary); margin-top: 0.25rem; font-style: italic; }

    @media print {
        .no-print { display: none !important; }
        .proposta-view-container { max-width: 100%; padding: 0; }
        .proposta-view-shell { background: #fff; }
    }
    @media (max-width: 991px) {
        .lh-top-row { flex-direction: column; }
        .lh-actions { width: 100%; }
    }
</style>

<div class="proposta-view-container py-4 px-3">
<div class="proposta-view-shell">

    <!-- Letterhead -->
    <div class="letterhead">
        <div class="lh-top-row">
            <div>
                <ol class="lh-breadcrumb">
                    <li><a href="<?= BASE_URL ?>/orcamento">Comercial</a></li>
                    <li class="sep">/</li>
                    <li><a href="<?= BASE_URL ?>/orcamento/index">Propostas</a></li>
                    <li class="sep">/</li>
                    <li class="current">Visualização</li>
                </ol>
                <h1 class="lh-title">
                    Proposta <span class="doc-number">#<?= htmlspecialchars($orc['numero']) ?></span>
                </h1>
                <div class="lh-meta">
                    <span><i class="far fa-calendar-alt"></i>Emitida em <?= date('d/m/Y', strtotime($orc['criado_em'])) ?></span>
                    <span class="divider"></span>
                    <span><i class="far fa-user"></i>Por <?= htmlspecialchars($orc['responsavel_nome'] ?? 'Gestor') ?></span>
                </div>
            </div>
            <div class="lh-actions no-print">
                <span class="status-tag tag-<?= $sl['cor'] ?>">
                    <span class="dot"></span><?= $sl['label'] ?>
                </span>
                <a href="<?= BASE_URL ?>/orcamento/index" class="btn-back">
                    <i class="fas fa-arrow-left"></i> Voltar
                </a>
                <a href="<?= BASE_URL ?>/orcamento/pdf/<?= $orc['id'] ?>" target="_blank" class="btn-pdf">
                    <i class="fas fa-file-pdf"></i> PDF
                </a>
            </div>
        </div>
    </div>

    <!-- Barra de Ações Rápidas -->
    <div class="action-bar no-print">
        <?php if ($isAdmin || in_array($orc['status'], ['Rascunho', 'Rejeitada'])): ?>
            <a href="<?= BASE_URL ?>/orcamento/editar/<?= $orc['id'] ?>" class="btn-pill">
                <i class="fas fa-edit"></i> Editar
            </a>
        <?php endif; ?>

        <?php if ($orc['status'] === 'Rascunho'): ?>
            <button type="button" onclick="updateProposalStatus(<?= $orc['id'] ?>, 'Enviada')" class="btn-pill">
                <i class="fas fa-paper-plane"></i> Marcar como Enviada
            </button>
        <?php endif; ?>

        <?php if ($orc['status'] === 'Enviada'): ?>
            <button type="button" onclick="updateProposalStatus(<?= $orc['id'] ?>, 'Aprovada')" class="btn-pill pill-success">
                <i class="fas fa-check-circle"></i> Aprovar
            </button>
            <button type="button" onclick="updateProposalStatus(<?= $orc['id'] ?>, 'Rejeitada')" class="btn-pill pill-danger">
                <i class="fas fa-times-circle"></i> Reprovar
            </button>
        <?php endif; ?>

        <a href="<?= BASE_URL ?>/orcamento/clonar/<?= $orc['id'] ?>" class="btn-pill" title="Duplicar">
            <i class="fas fa-copy"></i> Duplicar
        </a>
        <button onclick="window.print()" class="btn-pill">
            <i class="fas fa-print"></i> Imprimir
        </button>

        <?php if (in_array($orc['status'], ['Rascunho', 'Rejeitada']) && !empty($orc['id']) && $orc['id'] > 0): ?>
            <button type="button" onclick="excluirProposta('<?= htmlspecialchars($orc['id']) ?>', this)" class="btn-pill pill-danger">
                <i class="fas fa-trash-alt"></i> Excluir
            </button>
        <?php endif; ?>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- 01 CLIENTE -->
            <div class="section-card">
                <div class="section-head">
                    <span class="section-num">01</span>
                    <span class="section-title"><i class="fas fa-user-tie"></i>Informações do Cliente</span>
                </div>
                <div class="section-body">
                    <div class="info-grid">
                        <div>
                            <span class="label-muted">Razão Social / Nome</span>
                            <div class="value-highlight"><?= htmlspecialchars($orc['razao_social']) ?></div>
                            <?php if ($orc['nome_fantasia']): ?>
                                <div class="value-sub"><?= htmlspecialchars($orc['nome_fantasia']) ?></div>
                            <?php endif; ?>
                        </div>
                        <div>
                            <span class="label-muted">Documento</span>
                            <div class="value-highlight"><?= htmlspecialchars($orc['cnpj_cpf'] ?? '—') ?></div>
                        </div>
                        <div>
                            <span class="label-muted">Contato Principal</span>
                            <div class="value-highlight"><?= htmlspecialchars($orc['cliente_contato'] ?? '—') ?></div>
                            <div class="value-sub"><?= htmlspecialchars($orc['cliente_email'] ?? '—') ?></div>
                        </div>
                        <div>
                            <span class="label-muted">Telefone</span>
                            <div class="value-highlight"><?= htmlspecialchars($orc['telefone'] ?? '—') ?></div>
                        </div>
                    </div>
                    <div class="divider-soft">
                        <span class="label-muted">Endereço Completo</span>
                        <div class="value-sub" style="color: var(--text-secondary); font-size: 13px;"><?= htmlspecialchars($orc['endereco'] ?? 'Endereço não informado') ?></div>
                    </div>
                    <?php if (!empty($orc['descricao_geral'])): ?>
                        <div class="divider-soft">
                            <span class="label-muted">Escopo / Objeto</span>
                            <div style="color: var(--text-secondary); font-size: 13px; line-height: 1.6;"><?= nl2br(htmlspecialchars($orc['descricao_geral'])) ?></div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- 02 RESPONSÁVEL INTERNO -->
            <div class="section-card">
                <div class="section-head">
                    <span class="section-num">02</span>
                    <span class="section-title"><i class="fas fa-user-cog"></i>Responsável Interno</span>
                </div>
                <div class="section-body">
                    <div class="info-grid">
                        <div>
                            <span class="label-muted">Nome</span>
                            <div class="value-highlight"><?= htmlspecialchars($orc['responsavel_nome'] ?? 'Não informado') ?></div>
                        </div>
                        <div>
                            <span class="label-muted">ID Responsável</span>
                            <div class="value-highlight">#<?= htmlspecialchars($orc['responsavel_interno_id'] ?? '—') ?></div>
                        </div>
                    </div>
                    <?php if (!empty($orc['responsavel_nome'])): ?>
                        <div class="divider-soft">
                            <span class="label-muted">Observação</span>
                            <div style="color: var(--text-secondary); font-size: 13px;">Este profissional é responsável pelo acompanhamento e execução desta proposta.</div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- 03 ITENS -->
            <div class="section-card">
                <div class="section-head">
                    <span class="section-num">03</span>
                    <span class="section-title"><i class="fas fa-list-ul"></i>Detalhamento dos Itens</span>
                </div>
                <?php if (!empty($orc['itens']) && is_array($orc['itens'])): ?>
                    <div class="table-responsive">
                        <table class="table-ledger">
                            <thead>
                                <tr>
                                    <th style="width:40px">#</th>
                                    <th>Descrição do Serviço / Produto</th>
                                    <th style="text-align:center">Qtd.</th>
                                    <th style="text-align:right">Vlr. Unitário</th>
                                    <th style="text-align:right">Desc.%</th>
                                    <th style="text-align:right">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $subSectionCounter = 0;
                                foreach ($orc['itens'] as $idx => $item):
                                    $cat = $item['categoria'] ?? '';
                                    $isTitulo = ($cat === 'Titulo');
                                    $isSubtitulo = ($cat === 'Subtitulo');
                                    $isLegend = ($cat === 'Legenda');
                                    if ($isTitulo):
                                        $subSectionCounter++;
                                ?>
                                <tr class="item-text-row">
                                    <td colspan="6" class="item-section-title">
                                        <span class="sub-num"><?= str_pad($subSectionCounter, 2, '0', STR_PAD_LEFT) ?>.</span>
                                        <?= htmlspecialchars($item['descricao'] ?? $item['nome'] ?? '') ?>
                                    </td>
                                </tr>
                                <?php elseif ($isSubtitulo): ?>
                                <tr class="item-text-row">
                                    <td colspan="6">
                                        <div class="item-text-content"><?= nl2br(htmlspecialchars($item['descricao'] ?? $item['detalhes'] ?? '')) ?></div>
                                    </td>
                                </tr>
                                <?php elseif ($isLegend): ?>
                                <tr class="item-text-row" style="background:var(--bg-surface-alt)">
                                    <td colspan="6">
                                        <span class="item-legend"><?= htmlspecialchars($item['descricao'] ?? $item['nome'] ?? '') ?></span>
                                    </td>
                                </tr>
                                <?php else: ?>
                                <tr>
                                    <td class="item-idx"><?= str_pad($idx + 1, 2, '0', STR_PAD_LEFT) ?></td>
                                    <td>
                                        <div class="item-desc-title"><?= htmlspecialchars($item['descricao'] ?? $item['nome'] ?? '') ?></div>
                                        <?php if (!empty($item['detalhes']) && $item['detalhes'] !== ($item['descricao'] ?? '')): ?>
                                            <div class="item-desc-sub"><?= htmlspecialchars($item['detalhes']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align:center" class="item-mono"><?= number_format((float)($item['quantidade'] ?? 0), 2, ',', '.') ?> <small><?= htmlspecialchars($item['unidade'] ?? 'un') ?></small></td>
                                    <td style="text-align:right" class="item-mono"><?= \App\Helpers\ReportHelper::formatCurrency($item['valor_unit'] ?? $item['valor_unitario'] ?? 0) ?></td>
                                    <td style="text-align:center" class="item-mono"><?= ($item['desconto_item'] ?? 0) > 0 ? number_format((float)$item['desconto_item'], 2, ',', '.') . '%' : '—' ?></td>
                                    <td style="text-align:right" class="item-total"><?= \App\Helpers\ReportHelper::formatCurrency($item['total_item'] ?? (($item['quantidade'] ?? 0) * ($item['valor_unit'] ?? $item['valor_unitario'] ?? 0))) ?></td>
                                </tr>
                                <?php endif; ?>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="ledger-summary">
                        <div class="row justify-content-end">
                            <div class="col-md-5">
                                <?php
                                $totalItemDesc = 0;
                                if (!empty($orc['itens'])) {
                                    foreach ($orc['itens'] as $item) {
                                        $descItem = (float)($item['desconto_item'] ?? 0);
                                        $qty = (float)($item['quantidade'] ?? 0);
                                        $vunit = (float)($item['valor_unit'] ?? $item['valor_unitario'] ?? 0);
                                        $totalItemDesc += $qty * $vunit * ($descItem / 100);
                                    }
                                }
                                ?>
                                <div class="ledger-row">
                                    <span>Subtotal (bruto)</span>
                                    <span class="val"><?= \App\Helpers\ReportHelper::formatCurrency(($orc['subtotal'] ?? 0) + $totalItemDesc) ?></span>
                                </div>
                                <?php if ($totalItemDesc > 0): ?>
                                <div class="ledger-row discount">
                                    <span>Desconto nos Itens</span>
                                    <span class="val">- <?= \App\Helpers\ReportHelper::formatCurrency($totalItemDesc) ?></span>
                                </div>
                                <?php endif; ?>
                                <div class="ledger-row" style="border-bottom:1px dashed #ddd;padding-bottom:4px;margin-bottom:4px">
                                    <span>Subtotal (líquido)</span>
                                    <span class="val"><?= \App\Helpers\ReportHelper::formatCurrency($orc['subtotal'] ?? 0) ?></span>
                                </div>
                                <?php if ((float)($orc['desconto_valor'] ?? 0) > 0): ?>
                                <div class="ledger-row discount">
                                    <span>Desconto Global</span>
                                    <span class="val">- <?= \App\Helpers\ReportHelper::formatCurrency($orc['desconto_valor']) ?></span>
                                </div>
                                <?php endif; ?>
                                <?php if ((float)($orc['impostos_valor'] ?? 0) > 0): ?>
                                <div class="ledger-row tax">
                                    <span>Impostos</span>
                                    <span class="val">+ <?= \App\Helpers\ReportHelper::formatCurrency($orc['impostos_valor']) ?></span>
                                </div>
                                <?php endif; ?>
                                <div class="ledger-total-row">
                                    <span class="lbl">Valor Total</span>
                                    <span class="val"><?= \App\Helpers\ReportHelper::formatCurrency($orc['total'] ?? $orc['total_final']) ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-info-circle"></i>
                        <p style="margin:0;">Nenhum item foi adicionado a esta proposta ainda.</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- 04 CRONOGRAMA -->
            <?php
            $crono = !empty($orc['cronograma_data']) ? json_decode($orc['cronograma_data'], true) : null;
            if ($crono && !empty($crono['activities'])):
            ?>
            <div class="section-card">
                <div class="section-head">
                    <span class="section-num">04</span>
                    <span class="section-title"><i class="fas fa-calendar-alt"></i>Cronograma de Execução</span>
                </div>
                <div class="crono-scroll">
                    <table class="table-crono">
                        <thead>
                            <tr>
                                <th>Atividade</th>
                                <?php
                                $n = (int)$crono['totalPeriods'];
                                for ($i = 1; $i <= $n; $i++) echo "<th>$i</th>";
                                ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($crono['activities'] as $ri => $name): ?>
                            <tr>
                                <td class="crono-label"><?= htmlspecialchars($name) ?></td>
                                <?php for ($ci = 0; $ci < $n; $ci++):
                                    $val = $crono['state'][$ri . '_' . $ci] ?? 0;
                                    $bg = $val == 1 ? 'crono-office' : ($val == 2 ? 'crono-field' : '');
                                ?>
                                    <td class="<?= $bg ?>"></td>
                                <?php endfor; ?>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="crono-legend">
                    <span><span class="crono-swatch crono-office"></span>Escritório</span>
                    <span><span class="crono-swatch crono-field"></span>Campo</span>
                </div>
            </div>
            <?php endif; ?>

            <!-- 05 OBSERVAÇÕES -->
            <?php if ($orc['observacoes']): ?>
            <div class="section-card">
                <div class="section-head">
                    <span class="section-num">05</span>
                    <span class="section-title"><i class="fas fa-comment-alt"></i>Observações</span>
                </div>
                <div class="section-body">
                    <div class="note-box">
                        <?= nl2br(htmlspecialchars(html_entity_decode($orc['observacoes']))) ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <div class="col-lg-4">
            <!-- CERTIFICADO DE VALOR -->
            <div class="certificate-card">
                <div class="cert-eyebrow">Valor Final da Proposta</div>
                <div class="cert-value"><?= \App\Helpers\ReportHelper::formatCurrency($orc['total'] ?? $orc['total_final']) ?></div>
                <div class="cert-row">
                    <span>Vencimento</span>
                    <span class="cert-val"><?= $orc['data_validade'] ? date('d/m/Y', strtotime($orc['data_validade'])) : '—' ?></span>
                </div>
                <div class="cert-row">
                    <span>Status</span>
                    <span class="cert-val"><?= $sl['label'] ?></span>
                </div>
                <div class="cert-row">
                    <span>Data Criação</span>
                    <span class="cert-val"><?= date('d/m/Y', strtotime($orc['criado_em'] ?? $orc['created_at'] ?? 'now')) ?></span>
                </div>
            </div>

            <!-- PROJETO VINCULADO -->
            <?php if (!empty($orc['projeto_nome'])): ?>
            <div class="section-card">
                <div class="section-head">
                    <span class="section-title"><i class="fas fa-project-diagram"></i>Projeto Vinculado</span>
                </div>
                <div class="section-body">
                    <span class="label-muted">Nome do Projeto</span>
                    <div style="margin-bottom: 0.85rem;">
                        <a href="<?= BASE_URL ?>/projetos/detalhe/<?= $orc['projeto_id'] ?>/resumo" class="side-card-link">
                            <?= htmlspecialchars($orc['projeto_nome']) ?>
                        </a>
                    </div>
                    <div class="side-hint"><i class="fas fa-info-circle"></i> Esta proposta está vinculada ao projeto acima.</div>
                </div>
            </div>
            <?php endif; ?>

            <!-- CONTRATO VINCULADO -->
            <?php if (!empty($orc['contrato_id'])): ?>
            <div class="section-card">
                <div class="section-head">
                    <span class="section-title" style="color: var(--accent-emerald);"><i class="fas fa-file-contract" style="color: var(--accent-emerald);"></i>Contrato Vinculado</span>
                </div>
                <div class="section-body">
                    <span class="label-muted">Identificação</span>
                    <div style="margin-bottom: 0.85rem;">
                        <a href="<?= BASE_URL ?>/contratos/detalhe/<?= $orc['contrato_id'] ?>" class="side-card-link" style="color: var(--accent-emerald);">
                            <?= htmlspecialchars($orc['contrato_numero'] ?? 'Contrato #' . $orc['contrato_id']) ?>
                        </a>
                    </div>
                    <div class="side-hint"><i class="fas fa-info-circle"></i> Esta proposta faz parte do escopo do contrato acima.</div>
                </div>
            </div>
            <?php endif; ?>

            <!-- CONDIÇÕES COMERCIAIS -->
            <div class="section-card">
                <div class="section-head">
                    <span class="section-title"><i class="fas fa-credit-card"></i>Condições Comerciais</span>
                </div>
                <div class="section-body">
                    <div style="margin-bottom: 1.1rem;">
                        <span class="label-muted">Pagamento</span>
                        <div class="value-highlight" style="font-size: 13.5px;"><?= htmlspecialchars($orc['condicao_pagamento'] ?? $orc['forma_pagamento'] ?? '—') ?></div>
                    </div>
                    <div style="margin-bottom: 1.1rem;">
                        <span class="label-muted">Prazo de Entrega / Início</span>
                        <div class="value-highlight" style="font-size: 13.5px;"><?= htmlspecialchars($orc['prazo_entrega'] ?? $orc['prazo_execucao'] ?? '—') ?></div>
                    </div>
                    <?php if (!empty($orc['garantias'])): ?>
                    <div>
                        <span class="label-muted">Garantias</span>
                        <div class="value-highlight" style="font-size: 13.5px; font-weight: 500;"><?= nl2br(htmlspecialchars(html_entity_decode($orc['garantias']))) ?></div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- APROVAÇÃO DO DIRETOR -->
            <?php $dirStatus = $orc['aprovacao_diretor_status'] ?? 'nao_solicitado'; ?>
            <?php if ($dirStatus !== 'nao_solicitado'): ?>
            <div class="section-card" style="<?= $dirStatus === 'pendente' ? 'border-top: 3px solid var(--accent-amber);' : ($dirStatus === 'aprovado' ? 'border-top: 3px solid var(--accent-emerald);' : 'border-top: 3px solid var(--accent-rose);') ?>">
                <div class="section-head" style="<?= $dirStatus === 'pendente' ? 'background: rgba(251,191,36,0.08);' : ($dirStatus === 'aprovado' ? 'background: rgba(52,211,153,0.08);' : 'background: rgba(251,113,133,0.08);') ?>">
                    <span class="section-title" style="<?= $dirStatus === 'pendente' ? 'color: var(--accent-amber);' : ($dirStatus === 'aprovado' ? 'color: var(--accent-emerald);' : 'color: var(--accent-rose);') ?>">
                        <i class="fas fa-user-shield" style="<?= $dirStatus === 'pendente' ? 'color: var(--accent-amber);' : ($dirStatus === 'aprovado' ? 'color: var(--accent-emerald);' : 'color: var(--accent-rose);') ?>"></i>
                        <?= $dirStatus === 'pendente' ? 'Aguardando Aprovação do Diretor' : ($dirStatus === 'aprovado' ? 'Aprovada pelo Diretor' : 'Rejeitada pelo Diretor') ?>
                    </span>
                </div>
                <div class="section-body">
                    <?php if ($dirStatus === 'pendente'): ?>
                        <div style="margin-bottom: 0.85rem;">
                            <span class="label-muted">Status</span>
                            <div class="value-highlight" style="font-size: 13.5px; color: var(--accent-amber);">
                                <i class="fas fa-clock"></i> Pendente
                            </div>
                        </div>
                        <?php if (!empty($orc['enviado_diretor_nome'])): ?>
                        <div style="margin-bottom: 0.85rem;">
                            <span class="label-muted">Enviado por</span>
                            <div class="value-highlight" style="font-size: 13.5px;"><?= htmlspecialchars($orc['enviado_diretor_nome']) ?></div>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($orc['enviado_para_diretor_em'])): ?>
                        <div style="margin-bottom: 0.85rem;">
                            <span class="label-muted">Enviado em</span>
                            <div class="value-highlight" style="font-size: 13.5px;"><?= date('d/m/Y H:i', strtotime($orc['enviado_para_diretor_em'])) ?></div>
                        </div>
                        <?php endif; ?>
                        <?php if ($isAdmin): ?>
                        <div style="margin-top: 1rem; display: flex; gap: 0.5rem;">
                            <button type="button" onclick="abrirModalDiretor(<?= $orc['id'] ?>)" class="btn-pill pill-success" style="flex:1; justify-content:center;">
                                <i class="fas fa-check"></i> Aprovar / Rejeitar
                            </button>
                        </div>
                        <?php endif; ?>
                    <?php elseif ($dirStatus === 'aprovado'): ?>
                        <div style="margin-bottom: 0.85rem;">
                            <span class="label-muted">Status</span>
                            <div class="value-highlight" style="font-size: 13.5px; color: var(--accent-emerald);">
                                <i class="fas fa-check-circle"></i> Aprovado
                            </div>
                        </div>
                        <?php if (!empty($orc['diretor_nome'])): ?>
                        <div style="margin-bottom: 0.85rem;">
                            <span class="label-muted">Aprovado por</span>
                            <div class="value-highlight" style="font-size: 13.5px;"><?= htmlspecialchars($orc['diretor_nome']) ?></div>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($orc['aprovado_diretor_em'])): ?>
                        <div style="margin-bottom: 0.85rem;">
                            <span class="label-muted">Aprovado em</span>
                            <div class="value-highlight" style="font-size: 13.5px;"><?= date('d/m/Y H:i', strtotime($orc['aprovado_diretor_em'])) ?></div>
                        </div>
                        <?php endif; ?>
                    <?php elseif ($dirStatus === 'rejeitado'): ?>
                        <div style="margin-bottom: 0.85rem;">
                            <span class="label-muted">Status</span>
                            <div class="value-highlight" style="font-size: 13.5px; color: var(--accent-rose);">
                                <i class="fas fa-times-circle"></i> Rejeitado
                            </div>
                        </div>
                        <div style="margin-bottom: 0.85rem;">
                            <span class="label-muted">Justificativa</span>
                            <div class="value-highlight" style="font-size: 13.5px;"><?= htmlspecialchars($orc['justificativa_rejeicao'] ?? '—') ?></div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- ASSINATURA / APROVAÇÃO -->
            <?php if (strtolower($orc['status']) === 'aprovada'): ?>
            <div class="section-card approved-card">
                <div class="section-head">
                    <span class="section-title"><i class="fas fa-check-double"></i>Proposta Aprovada</span>
                </div>
                <div class="section-body" style="padding-top: 1rem; padding-bottom: 1.1rem;">
                    <div style="margin-bottom: 0.85rem;">
                        <span class="label-muted">Aprovado Por</span>
                        <div class="value-highlight" style="font-size: 13.5px;"><?= htmlspecialchars($orc['aprovado_por'] ?? 'Interno') ?></div>
                    </div>
                    <div>
                        <span class="label-muted">Data da Aprovação</span>
                        <div class="value-highlight" style="font-size: 13.5px;"><?= (!empty($orc['aprovado_em']) && $orc['aprovado_em'] !== '0000-00-00 00:00:00' && strtotime($orc['aprovado_em']) !== false) ? date('d/m/Y H:i', strtotime($orc['aprovado_em'])) : '—' ?></div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- HISTÓRICO -->
            <?php if (!empty($historico)): ?>
            <div class="section-card">
                <div class="section-head">
                    <span class="section-title"><i class="fas fa-history"></i>Histórico de Eventos</span>
                    <button type="button" onclick="limparHistorico(<?= $orc['id'] ?>)" class="btn-pill-sm" title="Limpar todo o histórico">
                        <i class="fas fa-eraser"></i> Limpar
                    </button>
                </div>
                <div class="section-body">
                    <div class="timeline-modern">
                        <?php foreach ($historico as $h): ?>
                        <div class="timeline-item">
                            <div class="timeline-marker"></div>
                            <div class="timeline-date"><?= date('d/m/Y H:i', strtotime($h['data_revisao'] ?? $h['data_evento'] ?? 'now')) ?></div>
                            <div class="timeline-status">
                                <?= htmlspecialchars($h['status_para'] ?? 'Alteração') ?>
                                <?php if (!empty($h['usuario_nome'])): ?>
                                    <span class="timeline-user">por <?= htmlspecialchars($h['usuario_nome']) ?></span>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($h['motivo_alteracao'] ?? $h['observacao'])): ?>
                                <div class="timeline-motivo">"<?= htmlspecialchars($h['motivo_alteracao'] ?? $h['observacao']) ?>"</div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
</div>

<!-- Modal de Aprovação do Diretor -->
<div id="diretorModal" class="fixed inset-0 bg-gray-800 dark:bg-black bg-opacity-75 dark:bg-opacity-75 flex items-center justify-center z-[60] hidden">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-lg mx-4 max-h-[90vh] overflow-y-auto">
        <div id="diretorModalContent" class="p-6"></div>
    </div>
</div>

<!-- Modal de envio de E-mail -->
<?php
$nomeEmpresa = htmlspecialchars($empresa['nome_fantasia'] ?? $empresa['razao_social'] ?? '');
$userEmail = htmlspecialchars($userEmail ?? '');
$userCargo = htmlspecialchars($userCargo ?? '');
$remetenteNome = htmlspecialchars($userName ?? '');
$assinaturaPadrao = $nomeEmpresa . "\n" . ($userCargo ? $userCargo . ' - ' : '') . $remetenteNome;
$logoUrl = '';
$logoPath = $empresa['logo_path'] ?? '';
if ($logoPath) {
    $logoFile = ROOT_PATH . '/public/uploads/logos/' . $logoPath;
    if (file_exists($logoFile)) {
        $logoUrl = BASE_URL . '/uploads/logos/' . $logoPath;
    }
}
?>
<div id="emailModal" class="fixed inset-0 bg-gray-800 dark:bg-black bg-opacity-75 dark:bg-opacity-75 flex items-center justify-center z-50 hidden">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-2xl">
        <form id="emailForm" action="" method="POST" class="p-6">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>" />
            <h3 class="text-xl font-bold mb-4 dark:text-white">Enviar Proposta por E-mail</h3>

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
                <button type="button" onclick="document.getElementById('emailModal').classList.add('hidden')" class="px-4 py-2 bg-gray-300 dark:bg-gray-600 dark:text-white rounded font-bold">Cancelar</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded font-bold">
                    <i class="fas fa-paper-plane mr-1"></i> Enviar E-mail
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const diretorModal = document.getElementById('diretorModal');
    const diretorModalContent = document.getElementById('diretorModalContent');

    function enviarWhatsApp(id, titulo, telefone) {
        const originalCursor = document.body.style.cursor;
        document.body.style.cursor = 'wait';
        fetch('<?= BASE_URL ?>/orcamento/gerarLinkPublico/' + id + '?origem=whatsapp')
            .then(res => res.json())
            .then(data => {
                document.body.style.cursor = originalCursor;
                if (data.success) {
                    const texto = 'Prezado cliente, segue o link para visualização e aprovação da proposta *' + titulo + '*:\n\n ' + data.link + ' \n\nFicamos à disposição para qualquer dúvida através do nosso contato oficial: <?= WHATSAPP_COMERCIAL_FORMATTED ?>.';
                    const cleanPhone = telefone ? telefone.replace(/\D/g, '') : '';
                    const url = cleanPhone.length >= 10
                        ? 'https://wa.me/55' + cleanPhone + '?text=' + encodeURIComponent(texto)
                        : 'https://wa.me/?text=' + encodeURIComponent(texto);
                    window.open(url, '_blank');
                } else {
                    alert(data.message || 'Erro ao gerar o link público.');
                }
            })
            .catch(err => {
                document.body.style.cursor = originalCursor;
                console.error(err);
                alert('Falha na comunicação com o servidor.');
            });
    }

    function openEmailModal(id, titulo, email) {
        const eModal = document.getElementById('emailModal');
        const form = document.getElementById('emailForm');
        if (!eModal || !form) return;
        form.action = '<?= BASE_URL ?>/orcamento/enviarEmail/' + id;
        document.getElementById('email_destinatario').value = email || '';
        document.getElementById('email_assunto').value = 'Proposta Comercial: ' + titulo;
        document.getElementById('email_corpo').value = 'Prezado(a),\n\nSegue em anexo nossa proposta comercial referente a "' + titulo + '".\n\nFicamos à disposição para qualquer esclarecimento.';
        eModal.classList.remove('hidden');
    }

    function abrirModalDiretor(id) {
        if (!diretorModal || !diretorModalContent) return;
        diretorModal.classList.remove('hidden');
        diretorModalContent.innerHTML = `
            <div class="flex justify-center items-center py-16">
                <div class="animate-spin rounded-full h-12 w-12 border-4 border-indigo-500 dark:border-indigo-400 border-t-transparent"></div>
                <p class="ml-4 text-gray-600 dark:text-gray-300 font-semibold">Carregando...</p>
            </div>`;
        fetch(`<?= BASE_URL ?>/orcamento/getDiretorModalAjax/${id}`)
            .then(response => response.text())
            .then(html => { diretorModalContent.innerHTML = html; })
            .catch(() => {
                diretorModalContent.innerHTML = `
                    <div class="p-8 text-center">
                        <i class="fas fa-exclamation-circle text-red-500 text-4xl mb-4"></i>
                        <p class="text-gray-800 dark:text-gray-200 font-bold">Erro ao carregar.</p>
                        <button onclick="closeDiretorModal()" class="mt-4 text-blue-600 underline">Fechar</button>
                    </div>`;
            });
    }

    function closeDiretorModal() {
        if (diretorModal) diretorModal.classList.add('hidden');
        if (diretorModalContent) diretorModalContent.innerHTML = '';
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
            fetch('<?= BASE_URL ?>/orcamento/aprovarDiretorAjax/' + id, { method: 'POST', body: formData })
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
                        if (result.isConfirmed) enviarWhatsApp(id, titulo, telefone);
                        else if (result.isDenied) openEmailModal(id, titulo, email);
                        else window.location.reload();
                    });
                } else {
                    Swal.fire('Erro', data.message || 'Erro ao aprovar proposta.', 'error');
                }
            })
            .catch(() => { Swal.fire('Erro', 'Falha na comunicação com o servidor.', 'error'); closeDiretorModal(); });
        });
    }

    function rejeitarDiretor(id) {
        Swal.fire({
            title: 'Rejeitar Proposta',
            text: 'Informe o motivo da rejeição:',
            icon: 'warning',
            input: 'textarea',
            inputPlaceholder: 'Descreva o motivo da rejeição...',
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-times"></i> Rejeitar',
            confirmButtonColor: '#dc2626',
            cancelButtonText: '<i class="fas fa-arrow-left"></i> Voltar',
            cancelButtonColor: '#6b7280',
            preConfirm: (value) => {
                if (!value || value.trim() === '') { Swal.showValidationMessage('A justificativa é obrigatória'); return false; }
                return value.trim();
            }
        }).then(result => {
            if (!result.isConfirmed) return;
            Swal.fire({ title: 'Rejeitando...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
            const formData = new FormData();
            formData.append('csrf_token', '<?= $csrf_token ?? '' ?>');
            formData.append('justificativa', result.value);
            fetch('<?= BASE_URL ?>/orcamento/rejeitarDiretorAjax/' + id, { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                closeDiretorModal();
                if (data.success) {
                    Swal.fire({ title: 'Proposta rejeitada!', text: 'A proposta foi retornada para edição.', icon: 'info', timer: 2500, showConfirmButton: false })
                        .then(() => window.location.reload());
                } else {
                    Swal.fire('Erro', data.message || 'Erro ao rejeitar proposta.', 'error');
                }
            })
            .catch(() => { Swal.fire('Erro', 'Falha na comunicação com o servidor.', 'error'); closeDiretorModal(); });
        });
    }

async function updateProposalStatus(id, newStatus) {
    const acao = newStatus === 'Aprovada' ? 'aprovar' : (newStatus === 'Rejeitada' ? 'rejeitar' : 'enviar');
    if (!confirm(`Deseja ${acao} esta proposta?`)) return;

    let motivo = '';
    if (newStatus === 'Rejeitada') {
        motivo = prompt('Informe o motivo da rejeição (opcional):');
    }

    const formData = new FormData();
    formData.append('status', newStatus);
    formData.append('motivo', motivo);
    formData.append('csrf_token', '<?= $csrf_token ?? '' ?>');

    try {
        const response = await fetch(`<?= BASE_URL ?>/orcamento/updateStatusAjax/${id}`, {
            method: 'POST',
            body: formData
        });

        const result = await response.json();
        if (result.success) {
            window.location.reload();
        } else {
            alert(result.message || 'Erro ao atualizar status.');
        }
    } catch (e) {
        console.error(e);
        alert('Erro na comunicação com o servidor.');
    }
}

/**
 * Exclui a proposta visualizada
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
            const selectedRow = document.querySelector('tr[data-id].bg-sky-50, tr[data-id].selected');
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

async function limparHistorico(id) {
    if (!confirm('Tem certeza que deseja limpar todo o histórico de eventos desta proposta?')) return;
    if (!confirm('Esta ação não pode ser desfeita. Os registros de histórico serão permanentemente removidos. Deseja continuar?')) return;

    const formData = new FormData();
    formData.append('csrf_token', '<?= $csrf_token ?? '' ?>');

    try {
        const response = await fetch(`<?= BASE_URL ?>/orcamento/limparHistoricoAjax/${id}`, {
            method: 'POST',
            body: formData
        });

        const result = await response.json();
        if (result.success) {
            window.location.reload();
        } else {
            alert(result.message || 'Erro ao limpar histórico.');
        }
    } catch (e) {
        console.error(e);
        alert('Erro na comunicação com o servidor.');
    }
}
</script>
