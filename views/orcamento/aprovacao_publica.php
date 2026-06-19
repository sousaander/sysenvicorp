<!DOCTYPE html>
<?php
use App\Helpers\ReportHelper;
use App\Core\SessionManager;

$status    = $proposta['status'] ?? 'Enviada';
$isPending = in_array($status, ['Enviada', 'pendente']);

$statusMap = [
    'Enviada'   => ['label' => 'Aguardando Aprovação', 'dot' => '#f59e0b', 'bg' => '#fef3c7', 'color' => '#92400e', 'pulse' => true],
    'pendente'  => ['label' => 'Aguardando Aprovação', 'dot' => '#f59e0b', 'bg' => '#fef3c7', 'color' => '#92400e', 'pulse' => true],
    'Aprovada'  => ['label' => 'Aprovado',             'dot' => '#10b981', 'bg' => '#d1fae5', 'color' => '#065f46', 'pulse' => false],
    'Rejeitada' => ['label' => 'Rejeitado',            'dot' => '#ef4444', 'bg' => '#fee2e2', 'color' => '#991b1b', 'pulse' => false],
    'Cancelada' => ['label' => 'Cancelada',            'dot' => '#94a3b8', 'bg' => '#f1f5f9', 'color' => '#475569', 'pulse' => false],
    'Rascunho'  => ['label' => 'Rascunho',             'dot' => '#94a3b8', 'bg' => '#f1f5f9', 'color' => '#475569', 'pulse' => false],
];
$sc = $statusMap[$status] ?? $statusMap['Enviada'];
?>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo htmlspecialchars($pageTitle); ?></title>
<link href="<?php echo BASE_URL; ?>/css/output.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script>
    if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
    }
</script>
<style>
/* ── RESET ─────────────────────────────────── */
*, *::before, *::after { box-sizing: border-box; }

/* ── BODY — mesmo gradiente do original ─────── */
body {
    background:
        radial-gradient(circle at top left,    rgba(56,189,248,.18), transparent 35%),
        radial-gradient(circle at bottom right, rgba(16,185,129,.18), transparent 30%),
        linear-gradient(180deg, #eff6ff 0%, #f8fafc 100%);
    min-height: 100vh;
    padding: 1.5rem 1.5rem 6.5rem; /* espaço para FAB */
    color: #111827;
    font-family: Inter, ui-sans-serif, system-ui, -apple-system, "Segoe UI", sans-serif;
    -webkit-font-smoothing: antialiased;
}

/* ── DARK MODE BASE ───────────────────────── */
.dark body {
    background: linear-gradient(180deg, #0f172a 0%, #020617 100%);
    color: #f1f5f9;
}
.dark .hero-panel, .dark .card-modern, .dark .fab-bar, .dark .modal-box, .dark .empty-state {
    background: #1e293b;
    border-color: #334155;
    box-shadow: 0 10px 30px rgba(0,0,0,.3);
}
.dark .card-header, .dark .table-clean thead th, .dark .desc-box, .dark .cond-card, .dark .meta-card {
    background: #0f172a;
    border-color: #334155;
    color: #f1f5f9;
}
.dark .hero-title, .dark .item-name, .dark .value-highlight, .dark .fin-row.grand, .dark h3 { color: #ffffff; }

.container { max-width: 1080px; margin: 0 auto; }

/* ── ANIMAÇÕES DE ENTRADA ───────────────────── */
.anim { animation: fadeUp .45s cubic-bezier(.22,1,.36,1) both; }
@keyframes fadeUp {
    from { opacity:0; transform:translateY(14px); }
    to   { opacity:1; transform:translateY(0); }
}
.d1{animation-delay:.04s} .d2{animation-delay:.09s} .d3{animation-delay:.14s}
.d4{animation-delay:.19s} .d5{animation-delay:.24s} .d6{animation-delay:.29s}

/* ── HEADER ─────────────────────────────────── */
.header-banner {
    display: flex; align-items: center;
    justify-content: space-between;
    flex-wrap: wrap; gap: .75rem;
    margin-bottom: 1.5rem;
}
.logo-container { display: flex; align-items: center; gap: .85rem; }
.logo-container img { height: 38px; width: auto; }

/* ── STATUS BADGE ───────────────────────────── */
.status-badge {
    display: inline-flex; align-items: center; gap: .45rem;
    padding: .4rem 1rem; border-radius: 999px;
    font-size: .75rem; font-weight: 700;
    letter-spacing: .05em; text-transform: uppercase;
    background: <?php echo $sc['bg']; ?>;
    color: <?php echo $sc['color']; ?>;
}
.status-dot {
    width: 7px; height: 7px; border-radius: 50%;
    background: <?php echo $sc['dot']; ?>;
    <?php if ($sc['pulse']): ?>animation: sdpulse 1.8s ease infinite;<?php endif; ?>
}
@keyframes sdpulse {
    0%,100%{opacity:1;transform:scale(1)} 50%{opacity:.3;transform:scale(1.4)}
}

/* ── ALERTS ─────────────────────────────────── */
.alert {
    display: flex; align-items: flex-start; gap: .75rem;
    padding: 1rem 1.25rem; border-radius: .85rem;
    margin-bottom: 1.25rem; font-size: .875rem; font-weight: 500;
    border-left: 4px solid transparent;
}
.alert-success { background:#d1fae5; color:#047857; border-color:#10b981; }
.alert-error   { background:#fee2e2; color:#991b1b; border-color:#ef4444; }
.alert-info    { background:#dbeafe; color:#1e40af; border-color:#3b82f6; }

/* ── HERO PANEL — idêntico ao original ──────── */
.hero-panel {
    position: relative; overflow: hidden;
    background: #ffffff; border-radius: 1.25rem;
    box-shadow: 0 24px 60px rgba(15,23,42,.08);
    margin-bottom: 1.75rem;
}
.hero-panel::before,
.hero-panel::after {
    content:''; position: absolute;
    width:420px; height:200px;
    border-radius:999px; filter:blur(44px); opacity:.88;
    pointer-events:none;
}
.hero-panel::before { top:-80px; left:-80px;   background:linear-gradient(135deg,#2563eb,#14b8a6); }
.hero-panel::after  { bottom:-80px; right:-80px; background:linear-gradient(135deg,rgba(16,185,129,.6),rgba(59,130,246,.6)); }

.hero-body {
    position: relative; z-index: 1;
    display: grid; grid-template-columns: 1fr auto;
    gap: 2rem; padding: 2.75rem 2.5rem; align-items: start;
}
.hero-eyebrow {
    display: inline-flex; align-items: center; gap: .5rem;
    padding: .45rem .9rem; color: #1d4ed8;
    background: rgba(59,130,246,.1); border-radius: 999px;
    font-size: .75rem; font-weight: 700;
    letter-spacing: .06em; text-transform: uppercase;
    width: fit-content; margin-bottom: .85rem;
}
.hero-title {
    font-size: clamp(1.75rem,3vw,2.6rem);
    line-height: 1.06; font-weight: 800; color: #0f172a;
    letter-spacing: -.02em; margin-bottom: .6rem;
}
.hero-subtitle { font-size: .95rem; color: #475569; max-width: 600px; line-height: 1.65; }

/* meta cards dentro do hero */
.hero-meta { display: grid; gap: .85rem; align-content: start; min-width: 220px; }
.meta-card {
    padding: 1.1rem 1.4rem; border-radius: 1rem;
    background: rgba(248,250,252,.9);
    border: 1px solid rgba(148,163,184,.28);
    backdrop-filter: blur(4px);
}
.meta-label {
    font-size: .7rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: .08em;
    color: #64748b; margin-bottom: .5rem; display: block;
}
.meta-value     { font-size: 1.15rem; font-weight: 700; color: #0f172a; line-height: 1.2; }
.meta-value.big { font-size: 1.4rem; color: #0284c7; }

/* ── CARD MODERN ─────────────────────────────── */
.card-modern {
    background: #ffffff; border-radius: 1rem;
    box-shadow: 0 4px 24px rgba(15,23,42,.07);
    overflow: hidden; margin-bottom: 1.5rem;
    border: 1px solid rgba(226,232,240,.8);
}
.card-header {
    display: flex; align-items: center; gap: .65rem;
    padding: 1.1rem 1.5rem;
    background: #f8fafc; border-bottom: 1px solid #e2e8f0;
    font-size: .875rem; font-weight: 700; color: #0f172a;
}
.card-icon {
    width: 30px; height: 30px; border-radius: 8px;
    background: #eff6ff; color: #2563eb;
    display: flex; align-items: center; justify-content: center;
    font-size: .8rem; flex-shrink: 0;
}

/* ── LABELS & VALUES ─────────────────────────── */
.label-muted {
    font-size: .7rem; font-weight: 700; color: #94a3b8;
    text-transform: uppercase; letter-spacing: .08em;
    margin-bottom: .35rem; display: block;
}
.value-highlight { font-weight: 600; color: #0f172a; font-size: .93rem; line-height: 1.4; }
.value-sub       { font-size: .8rem; color: #64748b; margin-top: 2px; }

/* ── INFO GRID ───────────────────────────────── */
.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px,1fr));
    gap: 1.25rem; padding: 1.5rem;
}

/* ── DESC BOX ────────────────────────────────── */
.desc-box {
    background: #f8fafc; border: 1px solid #e2e8f0;
    border-radius: .6rem; padding: .9rem 1rem;
    font-size: .875rem; color: #475569;
    line-height: 1.7; white-space: pre-wrap;
}

/* ── TABLE ───────────────────────────────────── */
.table-wrap { overflow-x: auto; }
.table-clean { width: 100%; border-collapse: collapse; min-width: 640px; }
.table-clean thead th {
    padding: .9rem 1.25rem; text-align: left;
    font-size: .72rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: .07em;
    color: #475569; background: #f8fafc;
    border-bottom: 2px solid #e2e8f0;
}
.table-clean thead th.r { text-align: right; }
.table-clean tbody tr   { transition: background .12s; }
.table-clean tbody tr:hover { background: #fafbff; }
.table-clean tbody td {
    padding: .95rem 1.25rem; border-bottom: 1px solid #e2e8f0;
    font-size: .875rem; color: #334155; vertical-align: middle;
}
.table-clean tbody td.r { text-align: right; }
.item-name { font-weight: 600; color: #0f172a; margin-bottom: 3px; }
.item-desc { font-size: .8rem; color: #6b7280; }
.item-idx  { font-family: monospace; font-size: .78rem; color: #94a3b8; }
.disc-tag  {
    display: inline-block; padding: 1px 7px; border-radius: 99px;
    background: #d1fae5; color: #065f46;
    font-size: .7rem; font-weight: 700; margin-left: 6px;
}

/* ── FINANCIAL TOTALS ────────────────────────── */
.fin-wrap { display: flex; justify-content: flex-end; padding: 1rem 1.5rem 1.5rem; }
.fin-box  { width: 300px; }
.fin-row  {
    display: flex; justify-content: space-between; align-items: baseline;
    padding: .55rem 0; border-bottom: 1px solid #e2e8f0;
    font-size: .875rem; color: #64748b;
}
.fin-row:last-child { border-bottom: none; }
.fin-row.discount { color: #059669; font-weight: 600; }
.fin-row.grand    {
    margin-top: .5rem; padding-top: .9rem;
    border-top: 2px solid #0f172a;
    color: #0f172a; font-weight: 700;
}
.fin-total-val {
    font-size: 1.45rem; font-weight: 800;
    color: #0284c7; letter-spacing: -.02em;
}

/* ── CONDITIONS ──────────────────────────────── */
.cond-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px,1fr));
    gap: 1rem; padding: 1.5rem;
}
.cond-card {
    background: #f8fafc; border: 1px solid rgba(148,163,184,.22);
    border-radius: .85rem; padding: 1.1rem 1.25rem;
}
.cond-card .cond-icon {
    width: 28px; height: 28px; border-radius: 7px;
    background: #eff6ff; color: #2563eb;
    display: flex; align-items: center; justify-content: center;
    font-size: .75rem; margin-bottom: .65rem;
}
.cond-card .cond-label { font-size: .68rem; font-weight: 700; letter-spacing: .07em; text-transform: uppercase; color: #94a3b8; margin-bottom: .35rem; }
.cond-card .cond-value { font-size: .88rem; font-weight: 600; color: #0f172a; line-height: 1.45; }

/* ── RESULT BANNERS ──────────────────────────── */
.result-banner {
    display: flex; align-items: center; gap: 1.25rem;
    padding: 1.75rem 2rem; border-radius: 1rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 4px 20px rgba(15,23,42,.07);
}
.result-banner.approved { background: #f0fdf4; border: 2px solid #6ee7b7; }
.result-banner.rejected { background: #fff5f5; border: 2px solid #fca5a5; }
.result-icon {
    width: 52px; height: 52px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.35rem; flex-shrink: 0;
}
.result-banner.approved .result-icon { background: #d1fae5; color: #059669; }
.result-banner.rejected .result-icon { background: #fee2e2; color: #dc2626; }
.result-banner h3 { font-size: 1.05rem; font-weight: 800; margin-bottom: 4px; }
.result-banner.approved h3 { color: #065f46; }
.result-banner.rejected h3 { color: #991b1b; }
.result-banner p  { font-size: .85rem; color: #475569; line-height: 1.6; }

/* ═══════════════════════════════════════════════
   FLOATING ACTION BAR
═══════════════════════════════════════════════ */
.fab-bar {
    position: fixed; bottom: 0; left: 0; right: 0; z-index: 100;
    background: rgba(255,255,255,.94);
    backdrop-filter: blur(14px) saturate(180%);
    -webkit-backdrop-filter: blur(14px) saturate(180%);
    border-top: 1px solid rgba(226,232,240,.95);
    box-shadow: 0 -6px 28px rgba(15,23,42,.1);
    padding: .9rem 1.5rem;
    animation: slideUp .4s cubic-bezier(.22,1,.36,1) both;
}
@keyframes slideUp {
    from { transform:translateY(100%); opacity:0; }
    to   { transform:translateY(0);    opacity:1; }
}
.fab-inner {
    max-width: 1080px; margin: 0 auto;
    display: flex; align-items: center;
    justify-content: space-between; gap: 1rem; flex-wrap: wrap;
}
.fab-left {
    display: flex; align-items: center; gap: 1.25rem; flex-wrap: wrap;
}
.fab-total {
    display: flex; align-items: center; gap: .5rem;
    font-size: .82rem; color: #64748b; font-weight: 500;
}
.fab-total strong { font-size: 1.2rem; font-weight: 800; color: #0284c7; }
.fab-divider { width: 1px; height: 28px; background: #e2e8f0; }
.fab-validity { font-size: .78rem; color: #94a3b8; }
.fab-validity strong { color: #475569; }
.fab-right { display: flex; align-items: center; gap: .65rem; flex-wrap: wrap; }

/* ── BUTTONS ─────────────────────────────────── */
.btn-action {
    display: inline-flex; align-items: center; justify-content: center;
    gap: .6rem; padding: .8rem 1.6rem; border-radius: .85rem;
    font-weight: 700; font-size: .875rem; font-family: inherit;
    cursor: pointer; border: none; text-decoration: none;
    transition: transform .2s, box-shadow .2s, background .2s;
    white-space: nowrap;
}
.btn-action:hover  { transform: translateY(-2px); }
.btn-action:active { transform: translateY(0); }

.btn-approve {
    background: #10b981; color: #fff;
    box-shadow: 0 4px 18px rgba(16,185,129,.35);
}
.btn-approve:hover { background: #059669; box-shadow: 0 8px 24px rgba(16,185,129,.45); }

.btn-reject {
    background: #fff; color: #dc2626;
    border: 1.5px solid #dc2626;
}
.btn-reject:hover { background: #fff5f5; box-shadow: 0 4px 14px rgba(220,38,38,.15); }

.btn-print { background: #1e293b; color: #fff; box-shadow: 0 4px 14px rgba(15,23,42,.2); }
.btn-print:hover { background: #0f172a; }

.btn-secondary { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }
.btn-icon-only { padding: .8rem 1rem; }

/* ── MODAL ───────────────────────────────────── */
.modal-overlay {
    display: none; position: fixed; inset: 0;
    background: rgba(15,23,42,.55);
    backdrop-filter: blur(6px);
    z-index: 200; align-items: center; justify-content: center; padding: 1rem;
}
.modal-overlay.open { display: flex; }
.modal-box {
    background: #fff; border-radius: 1.25rem;
    padding: 2.25rem; width: 100%; max-width: 460px;
    box-shadow: 0 24px 64px rgba(15,23,42,.18);
    animation: modalIn .28s cubic-bezier(.34,1.56,.64,1) both;
}
@keyframes modalIn {
    from { opacity:0; transform:scale(.9); }
    to   { opacity:1; transform:scale(1); }
}
.modal-icon-box {
    width: 48px; height: 48px; border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.2rem; margin-bottom: 1rem;
}
.modal-icon-box.approve { background: #d1fae5; color: #059669; }
.modal-icon-box.reject  { background: #fee2e2; color: #dc2626; }
.modal-box h3 { font-size: 1.2rem; font-weight: 800; color: #0f172a; margin-bottom: .5rem; }
.modal-box p  { font-size: .875rem; color: #475569; line-height: 1.65; margin-bottom: 1.25rem; }
.modal-label  {
    font-size: .7rem; font-weight: 700; letter-spacing: .07em;
    text-transform: uppercase; color: #94a3b8; margin-bottom: .5rem; display: block;
}
.modal-textarea {
    width: 100%; border: 1.5px solid #e2e8f0; border-radius: .6rem;
    padding: .75rem 1rem; font-family: inherit; font-size: .875rem;
    color: #0f172a; background: #f8fafc;
    resize: vertical; min-height: 90px; margin-bottom: 1.25rem;
    outline: none; transition: border-color .15s, box-shadow .15s;
}
.modal-textarea:focus {
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37,99,235,.12);
    background: #fff;
}
.modal-actions { display: flex; gap: .65rem; justify-content: flex-end; }

/* ── EMPTY STATE ─────────────────────────────── */
.empty-state {
    background: #fff; border-radius: 1rem;
    box-shadow: 0 4px 24px rgba(15,23,42,.07);
    text-align: center; padding: 4rem 2rem;
}
.empty-icon { font-size: 2.5rem; color: #94a3b8; margin-bottom: 1rem; }

/* ── RESPONSIVE ──────────────────────────────── */
@media (max-width: 860px) {
    .hero-body { grid-template-columns: 1fr; padding: 2rem 1.5rem; }
    .hero-meta { grid-template-columns: 1fr 1fr; }
    .fab-left  { display: none; } /* simplifica barra mobile */
}
@media (max-width: 600px) {
    body { padding-left: 1rem; padding-right: 1rem; }
    .hero-meta { grid-template-columns: 1fr; }
    .fab-inner { justify-content: center; }
    .fab-right { width: 100%; }
    .btn-action { flex: 1; }
    .cond-grid, .info-grid { grid-template-columns: 1fr; }
}
/* ── ACEITE RECORD ───────────────────────── */
.aceite-record {
    display: flex; align-items: flex-start; gap: 1rem;
    background: #f0fdf4; border: 1.5px solid #6ee7b7;
    border-radius: .85rem; padding: 1.1rem 1.4rem;
    margin-bottom: 1.5rem; font-size: .85rem;
}
.aceite-record .aceite-icon {
    width: 36px; height: 36px; border-radius: 50%;
    background: #d1fae5; color: #059669;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; font-size: .95rem;
}
.aceite-record .aceite-title { font-weight: 700; color: #065f46; margin-bottom: .35rem; }
.aceite-record .aceite-meta  { color: #374151; line-height: 1.75; }
.aceite-record .aceite-meta span { color: #6b7280; font-size: .78rem; margin-right: .35rem; }

/* ── CHECKBOX ACEITE ─────────────────────── */
.aceite-wrap {
    display: flex; align-items: flex-start; gap: .65rem;
    background: #f0fdf4; border: 1.5px solid #a7f3d0;
    border-radius: .75rem; padding: .9rem 1.1rem;
    margin-bottom: 1rem; cursor: pointer;
}
.aceite-wrap input[type=checkbox] { margin-top: 2px; accent-color: #059669; width: 16px; height: 16px; flex-shrink: 0; cursor: pointer; }
.aceite-wrap label { font-size: .85rem; color: #065f46; font-weight: 500; cursor: pointer; line-height: 1.5; }

@media print {
    .fab-bar, .modal-overlay { display: none !important; }
    body { background: #fff; padding: 0; }
    .card-modern, .hero-panel { box-shadow: none; border: 1px solid #e2e8f0; }
}
</style>
</head>
<body>
<div class="container">

    <!-- ── HEADER ──────────────────────────── -->
    <div class="header-banner anim">
        <div class="logo-container">
            <img src="<?php echo BASE_URL; ?>/assets/images/logo.png" alt="Logo">
            <h1 class="text-xl font-bold text-gray-800"><?php echo htmlspecialchars($pageTitle); ?></h1>
        </div>
        <div style="display:flex;align-items:center;gap:.75rem;flex-wrap:wrap">
            <?php if (isset($proposta)): ?>
            <span style="font-size:.8rem;color:#94a3b8;font-weight:500">
                #<?php echo htmlspecialchars($proposta['numero_proposta'] ?? $proposta['id']); ?>
            </span>
            <?php endif; ?>
            <span class="status-badge">
                <span class="status-dot"></span>
                <?php echo $sc['label']; ?>
            </span>
        </div>
    </div>

    <!-- ── FLASH MESSAGES ──────────────────── -->
    <?php
    $flash = SessionManager::getInstance()->getFlash();
    if ($flash):
        $fType = $flash['type'] === 'success' ? 'success' : ($flash['type'] === 'error' ? 'error' : 'info');
        $fIcons = ['success'=>'fa-check-circle','error'=>'fa-exclamation-circle','info'=>'fa-info-circle'];
    ?>
        <div class="alert alert-<?php echo $fType; ?> anim">
            <i class="fas <?php echo $fIcons[$fType]; ?>" style="margin-top:2px;flex-shrink:0"></i>
            <span><?php echo htmlspecialchars($flash['message']); ?></span>
        </div>
    <?php endif; ?>

    <?php if (isset($message)): ?>
    <!-- ── ESTADO VAZIO / EXPIRADO ─────────── -->
    <div class="empty-state anim">
        <div class="empty-icon"><i class="fas fa-file-circle-question"></i></div>
        <p class="text-gray-600 mb-6"><?php echo htmlspecialchars($message); ?></p>
        <a href="<?php echo BASE_URL; ?>" class="btn-action btn-print">
            <i class="fas fa-home"></i> Voltar ao Início
        </a>
    </div>

    <?php elseif (isset($proposta)): ?>

    <!-- ══════════════════════════════════════
         HERO PANEL
    ══════════════════════════════════════ -->
    <div class="hero-panel anim d1">
        <div class="hero-body">
            <div>
                <div class="hero-eyebrow">
                    <i class="fas fa-file-invoice-dollar"></i>
                    Proposta Comercial
                </div>
                <h2 class="hero-title"><?php echo htmlspecialchars($proposta['nome_proposta']); ?></h2>
                <p class="hero-subtitle">
                    Revise todos os itens desta proposta e utilize os botões no rodapé da tela para tomar sua decisão.
                </p>
            </div>
            <div class="hero-meta">
                <div class="meta-card">
                    <span class="meta-label">Valor Total</span>
                    <div class="meta-value big"><?php echo ReportHelper::formatCurrency($proposta['total_final']); ?></div>
                </div>
                <div class="meta-card">
                    <span class="meta-label">Válida até</span>
                    <div class="meta-value"><?php echo ReportHelper::formatDate($proposta['token_validade']); ?></div>
                </div>
                <?php if (!empty($proposta['prazo_execucao'])): ?>
                <div class="meta-card">
                    <span class="meta-label">Prazo de Execução</span>
                    <div class="meta-value"><?php echo htmlspecialchars($proposta['prazo_execucao']); ?></div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- ── BANNER DE RESULTADO ─────────────── -->
    <?php if ($status === 'Aprovada'): ?>
    <div class="result-banner approved anim d1">
        <div class="result-icon"><i class="fas fa-check-circle"></i></div>
        <div>
            <h3>Proposta Aprovada!</h3>
            <p>Obrigado pela confiança. Nossa equipe entrará em contato em breve para alinhar os próximos passos.</p>
        </div>
    </div>
    <?php if (!empty($proposta['aceite_em'])): ?>
    <div class="aceite-record anim d1">
        <div class="aceite-icon"><i class="fas fa-file-signature"></i></div>
        <div>
            <div class="aceite-title">Registro de Aceite Eletrônico</div>
            <div class="aceite-meta">
                <?php if (!empty($proposta['aceite_nome'])): ?>
                <div><span>Nome:</span><?php echo htmlspecialchars($proposta['aceite_nome']); ?></div>
                <?php endif; ?>
                <div><span>Data/Hora:</span><?php echo date('d/m/Y \à\s H:i:s', strtotime($proposta['aceite_em'])); ?></div>
                <?php if (!empty($proposta['aceite_ip'])): ?>
                <div><span>IP:</span><?php echo htmlspecialchars($proposta['aceite_ip']); ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
    <?php elseif ($status === 'Rejeitada'): ?>
    <div class="result-banner rejected anim d1">
        <div class="result-icon"><i class="fas fa-times-circle"></i></div>
        <div>
            <h3>Proposta Rejeitada</h3>
            <p>Agradecemos sua resposta. Entre em contato conosco caso queira negociar novos termos.</p>
        </div>
    </div>
    <?php endif; ?>

    <!-- ══════════════════════════════════════
         CARD ÚNICO — DETALHES DA PROPOSTA
    ══════════════════════════════════════ -->
    <?php
    $descTech = $proposta['descricao_geral'] ?? $proposta['descricao'] ?? null;
    $servicos = [];
    if (!empty($proposta['servicos_json'])) {
        $servicos = json_decode($proposta['servicos_json'], true) ?: [];
    }
    ?>
    <div class="card-modern anim d2">

        <!-- Seção: Cliente -->
        <div class="card-header">
            <div class="card-icon"><i class="fas fa-user-tie"></i></div>
            Informações do Cliente
        </div>
        <div class="info-grid">
            <div>
                <span class="label-muted">Razão Social / Nome</span>
                <div class="value-highlight"><?php echo htmlspecialchars($proposta['cliente_nome'] ?? '—'); ?></div>
            </div>
            <?php if (!empty($proposta['cliente_contato'])): ?>
            <div>
                <span class="label-muted">Contato</span>
                <div class="value-highlight"><?php echo htmlspecialchars($proposta['cliente_contato']); ?></div>
                <?php if (!empty($proposta['cliente_email'])): ?>
                <div class="value-sub"><?php echo htmlspecialchars($proposta['cliente_email']); ?></div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            <?php if (!empty($proposta['cliente_telefone'])): ?>
            <div>
                <span class="label-muted">Telefone</span>
                <div class="value-highlight"><?php echo htmlspecialchars($proposta['cliente_telefone']); ?></div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Seção: Descrição e Objetivo -->
        <?php if (!empty($descTech)): ?>
        <div class="card-header" style="border-top:1px solid #e2e8f0">
            <div class="card-icon"><i class="fas fa-file-alt"></i></div>
            Escopo / Objeto
        </div>
        <div style="padding:1.5rem;display:grid;gap:1.25rem">
            <div>
                <div class="desc-box"><?php echo nl2br(htmlspecialchars($descTech)); ?></div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Seção: Itens / Serviços -->
        <?php if (!empty($servicos)): ?>
        <div class="card-header" style="border-top:1px solid #e2e8f0">
            <div class="card-icon"><i class="fas fa-list-ul"></i></div>
            Detalhamento dos Itens
            <span style="margin-left:auto;font-size:.8rem;color:#94a3b8;font-weight:500">
                <?php echo count($servicos); ?> item(ns)
            </span>
        </div>
        <div class="table-wrap">
            <table class="table-clean">
                <thead>
                    <tr>
                        <th style="width:44px">#</th>
                        <th>Descrição</th>
                        <th class="r" style="width:90px">Qtd.</th>
                        <th class="r" style="width:130px">Valor Unit.</th>
                        <th class="r" style="width:130px">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($servicos as $idx => $item):
                        $qtd    = (float)($item['quantidade']     ?? 1);
                        $vUnit  = (float)($item['valor_unitario'] ?? 0);
                        $desc   = (float)($item['desconto']        ?? 0);
                        $subtot = ($qtd * $vUnit) * (1 - $desc / 100);
                    ?>
                    <tr>
                        <td><span class="item-idx"><?php echo str_pad($idx + 1, 2, '0', STR_PAD_LEFT); ?></span></td>
                        <td>
                            <div class="item-name">
                                <?php echo htmlspecialchars($item['nome'] ?? ''); ?>
                                <?php if ($desc > 0): ?>
                                <span class="disc-tag">-<?php echo number_format($desc, 0); ?>%</span>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($item['descricao'])): ?>
                            <div class="item-desc"><?php echo htmlspecialchars($item['descricao']); ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="r">
                            <?php echo number_format($qtd, 2, ',', '.'); ?>
                            <span style="font-size:.75rem;color:#94a3b8;margin-left:2px"><?php echo htmlspecialchars($item['unidade'] ?? 'un'); ?></span>
                        </td>
                        <td class="r" style="color:#64748b"><?php echo 'R$ ' . number_format($vUnit, 2, ',', '.'); ?></td>
                        <td class="r" style="font-weight:700;color:#0f172a"><?php echo 'R$ ' . number_format($subtot, 2, ',', '.'); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="fin-wrap">
            <div class="fin-box">
                <?php
                $subTotal = (float)($proposta['total_servicos'] ?? 0) + (float)($proposta['total_materiais'] ?? 0);
                $descVal  = (float)($proposta['descontos_valor'] ?? 0);
                $taxVal   = (float)($proposta['impostos_valor']  ?? 0);
                $total    = (float)($proposta['total_final']     ?? 0);
                ?>
                <div class="fin-row">
                    <span>Subtotal</span>
                    <span><?php echo 'R$ ' . number_format($subTotal, 2, ',', '.'); ?></span>
                </div>
                <?php if ($descVal > 0): ?>
                <div class="fin-row discount">
                    <span><i class="fas fa-tag" style="margin-right:4px;font-size:.72rem"></i>Desconto</span>
                    <span>− R$ <?php echo number_format($descVal, 2, ',', '.'); ?></span>
                </div>
                <?php endif; ?>
                <?php if ($taxVal > 0): ?>
                <div class="fin-row">
                    <span style="color:#94a3b8">Impostos</span>
                    <span>+ R$ <?php echo number_format($taxVal, 2, ',', '.'); ?></span>
                </div>
                <?php endif; ?>
                <div class="fin-row grand">
                    <span>Total</span>
                    <span class="fin-total-val"><?php echo 'R$ ' . number_format($total, 2, ',', '.'); ?></span>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Seção: Condições Comerciais -->
        <?php if (!empty($proposta['forma_pagamento']) || !empty($proposta['prazo_execucao']) || !empty($proposta['garantias']) || !empty($proposta['observacoes'])): ?>
        <div class="card-header" style="border-top:1px solid #e2e8f0">
            <div class="card-icon"><i class="fas fa-handshake"></i></div>
            Condições Comerciais
        </div>
        <div class="cond-grid">
            <?php if (!empty($proposta['forma_pagamento'])): ?>
            <div class="cond-card">
                <div class="cond-icon"><i class="fas fa-credit-card"></i></div>
                <div class="cond-label">Forma de Pagamento</div>
                <div class="cond-value"><?php echo htmlspecialchars($proposta['forma_pagamento']); ?></div>
            </div>
            <?php endif; ?>
            <?php if (!empty($proposta['prazo_execucao'])): ?>
            <div class="cond-card">
                <div class="cond-icon"><i class="fas fa-calendar-check"></i></div>
                <div class="cond-label">Prazo de Execução</div>
                <div class="cond-value"><?php echo htmlspecialchars($proposta['prazo_execucao']); ?></div>
            </div>
            <?php endif; ?>
            <?php if (!empty($proposta['garantias'])): ?>
            <div class="cond-card">
                <div class="cond-icon"><i class="fas fa-shield-alt"></i></div>
                <div class="cond-label">Garantias</div>
                <div class="cond-value"><?php echo nl2br(htmlspecialchars(html_entity_decode($proposta['garantias']))); ?></div>
            </div>
            <?php endif; ?>
            <?php if (!empty($proposta['observacoes'])): ?>
            <div class="cond-card" style="grid-column:1/-1">
                <div class="cond-icon"><i class="fas fa-sticky-note"></i></div>
                <div class="cond-label">Observações</div>
                <div class="cond-value" style="font-weight:400;color:#475569">
                    <?php echo nl2br(htmlspecialchars(html_entity_decode($proposta['observacoes']))); ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

    </div>

    <!-- ── RODAPÉ ───────────────────────────── -->
    <div class="anim d6" style="text-align:center;margin-top:1.5rem;font-size:.78rem;color:#94a3b8;line-height:1.8">
        Documento gerado em <?php echo date('d/m/Y \à\s H:i'); ?>
        &nbsp;·&nbsp; <?php echo htmlspecialchars($pageTitle); ?>
    </div>

    <?php if ($status === 'Aprovada' && !empty($proposta['aceite_em'])): ?>
    <div style="margin-top:1.5rem;border:1.5px solid #6ee7b7;border-radius:.85rem;padding:1.25rem 1.5rem;background:#f0fdf4;font-size:.82rem;color:#065f46">
        <div style="font-weight:700;margin-bottom:.5rem;display:flex;align-items:center;gap:.5rem">
            <i class="fas fa-file-signature"></i> Aceite Eletrônico Registrado
        </div>
        <?php if (!empty($proposta['aceite_nome'])): ?>
        <div>Nome: <strong><?php echo htmlspecialchars($proposta['aceite_nome']); ?></strong></div>
        <?php endif; ?>
        <div>Data/Hora: <strong><?php echo date('d/m/Y \à\s H:i:s', strtotime($proposta['aceite_em'])); ?></strong></div>
        <?php if (!empty($proposta['aceite_ip'])): ?>
        <div>Endereço IP: <strong><?php echo htmlspecialchars($proposta['aceite_ip']); ?></strong></div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php endif; // isset($proposta) ?>

</div><!-- /container -->

<!-- ══════════════════════════════════════════
     FLOATING ACTION BAR — só quando pendente
══════════════════════════════════════════ -->
<?php if (isset($proposta)): ?>
<div class="fab-bar">
    <div class="fab-inner">

        <!-- Lado esquerdo: info resumida -->
        <div class="fab-left">
            <div class="fab-total">
                <i class="fas fa-file-invoice-dollar" style="color:#94a3b8;font-size:.9rem"></i>
                Total da proposta:
                <strong><?php echo ReportHelper::formatCurrency($proposta['total_final']); ?></strong>
            </div>
            <div class="fab-divider"></div>
            <div class="fab-validity">
                Válida até <strong><?php echo ReportHelper::formatDate($proposta['token_validade']); ?></strong>
            </div>
        </div>

        <!-- Lado direito: ações -->
        <div class="fab-right">
            <button class="btn-action btn-print btn-icon-only" onclick="window.print()" title="Imprimir / Salvar PDF">
                <i class="fas fa-print"></i>
            </button>
            <button class="btn-action btn-reject" onclick="openModal('reject')">
                <i class="fas fa-times-circle"></i>
                Rejeitar
            </button>
            <button class="btn-action btn-approve" onclick="openModal('approve')">
                <i class="fas fa-check-circle"></i>
                Aprovar Proposta
            </button>
        </div>

    </div>
</div>

<!-- ══════════════════════════════════════════
     MODAL — APROVAR
══════════════════════════════════════════ -->
<div class="modal-overlay" id="modal-approve" onclick="bgClose(this,event)">
    <div class="modal-box">
        <div class="modal-icon-box approve"><i class="fas fa-check-circle"></i></div>
        <h3>Confirmar Aprovação</h3>
        <p>
            Você está aprovando a proposta
            <strong>#<?php echo htmlspecialchars($proposta['numero_proposta'] ?? $proposta['id']); ?></strong>
            no valor de <strong><?php echo ReportHelper::formatCurrency($proposta['total_final']); ?></strong>.
            <br>Essa ação não poderá ser desfeita.
        </p>
        <form action="<?php echo BASE_URL; ?>/orcamento/aprovarPropostaPublica/<?php echo htmlspecialchars($token); ?>" method="POST" id="form-approve">
            <input type="hidden" name="acao" value="aprovar">
            <label class="modal-label">Seu nome completo</label>
            <input type="text" name="aceite_nome" id="aceite_nome"
                class="modal-textarea" style="min-height:unset;height:42px;resize:none"
                placeholder="Ex: João da Silva"
                required>
            <label class="modal-label" style="margin-top:.85rem">Mensagem para a equipe (opcional)</label>
            <textarea name="motivo" class="modal-textarea" placeholder="Ex: Pode iniciar. Vamos alinhar reunião na segunda…"></textarea>
            <div class="aceite-wrap" onclick="if(event.target.tagName !== 'INPUT' && event.target.tagName !== 'LABEL') document.getElementById('chk-aceite').click()">
                <input type="checkbox" id="chk-aceite" name="aceite_confirmado" value="1" required>
                <label for="chk-aceite">
                    Declaro que li e compreendi todos os termos desta proposta e manifesto meu aceite eletrônico, ciente de que esta ação terá validade como confirmação formal.
                </label>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-action btn-secondary" onclick="closeModal('approve')">Cancelar</button>
                <button type="submit" class="btn-action btn-approve" id="btn-confirmar-aprovacao">
                    <i class="fas fa-check"></i> Confirmar Aprovação
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ══════════════════════════════════════════
     MODAL — REJEITAR
══════════════════════════════════════════ -->
<div class="modal-overlay" id="modal-reject" onclick="bgClose(this,event)">
    <div class="modal-box">
        <div class="modal-icon-box reject"><i class="fas fa-times-circle"></i></div>
        <h3>Rejeitar Proposta</h3>
        <p>Informe o motivo para que possamos apresentar uma proposta revisada com mais adequação às suas necessidades.</p>
        <form action="<?php echo BASE_URL; ?>/orcamento/aprovarPropostaPublica/<?php echo htmlspecialchars($token); ?>" method="POST">
            <input type="hidden" name="acao" value="rejeitar">
            <label class="modal-label">Motivo da Rejeição (opcional)</label>
            <textarea name="motivo" class="modal-textarea" placeholder="Ex: Valor fora do orçamento, prazo incompatível…"></textarea>
            <div class="modal-actions">
                <button type="button" class="btn-action btn-secondary" onclick="closeModal('reject')">Cancelar</button>
                <button type="submit" class="btn-action" style="background:#dc2626;color:#fff;box-shadow:0 4px 14px rgba(220,38,38,.3)">
                    <i class="fas fa-times"></i> Confirmar Rejeição
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(type) {
    document.getElementById('modal-' + type).classList.add('open');
    document.body.style.overflow = 'hidden';
}
function closeModal(type) {
    document.getElementById('modal-' + type).classList.remove('open');
    document.body.style.overflow = '';
}
function bgClose(overlay, event) {
    if (event.target === overlay) {
        overlay.classList.remove('open');
        document.body.style.overflow = '';
    }
}
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal-overlay.open').forEach(m => m.classList.remove('open'));
        document.body.style.overflow = '';
    }
});
</script>
<?php endif; ?>

</body>
</html>