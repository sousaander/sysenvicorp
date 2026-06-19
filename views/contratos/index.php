<?php
/**
 * Dashboard de Contratos — Versão Profissional
 * Mantém toda a lógica original e adiciona:
 * - Cards de KPI com ícones e tendências
 * - Barra de busca + filtros combinados
 * - Tabela com avatar de parte, status visual e ações consolidadas
 * - Painel lateral com ações, alertas críticos e distribuição por status
 * - Modal AJAX preservado e aprimorado
 */
?>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Lora:wght@500;600&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
/* ================================================================
   DESIGN SYSTEM — DASHBOARD CONTRATOS
================================================================ */
:root {
    --c-bg:           #F4F6FA;
    --c-surface:      #FFFFFF;
    --c-border:       #E4E9F0;
    --c-border-md:    #C8D4E3;

    --c-text:         #18243A;
    --c-text-2:       #4A5878;
    --c-text-3:       #8A97AE;

    --c-blue:         #1B4F8C;
    --c-blue-mid:     #2E6EC5;
    --c-blue-light:   #EBF2FB;
    --c-blue-hover:   #153D70;

    --c-green:        #17673E;
    --c-green-light:  #E6F5EE;
    --c-green-border: #5BBF8A;

    --c-gold:         #916A00;
    --c-gold-light:   #FFF7E0;
    --c-gold-border:  #E2B94A;

    --c-red:          #921C1C;
    --c-red-light:    #FDF0F0;
    --c-red-border:   #E88080;

    --c-purple:       #4A35A8;
    --c-purple-light: #EEEDFB;
    --c-purple-border:#A098E6;

    --c-gray-light:   #F0F3F8;

    --radius:         8px;
    --radius-lg:      14px;
    --radius-xl:      18px;
    --shadow:         0 1px 4px rgba(0,0,0,.07), 0 1px 2px rgba(0,0,0,.04);
    --shadow-md:      0 4px 16px rgba(0,0,0,.09), 0 2px 4px rgba(0,0,0,.05);

    --font-display:   'Lora', Georgia, serif;
    --font-body:      'DM Sans', system-ui, sans-serif;
}

/* Ajustes para Modo Escuro (Dark Mode) */
.dark-theme .dash {
    --c-bg:           var(--db-bg, #0F172A);
    --c-surface:      var(--db-surface, #1E293B);
    --c-border:       var(--db-border, #334155);
    --c-border-md:    #475569;
    --c-text:         var(--db-text, #F8FAFC);
    --c-text-2:       var(--db-text2, #CBD5E1);
    --c-text-3:       var(--db-text3, #94A3B8);
    --c-blue-light:   #1E293B;
    --c-gray-light:   rgba(255, 255, 255, 0.05);
    --c-green-light:  rgba(22, 163, 74, 0.1);
    --c-gold-light:   rgba(234, 179, 8, 0.1);
    --c-red-light:    rgba(220, 38, 38, 0.15);
}

/* Overrides para classes utilitárias e estados no Modo Escuro */
.dark-theme .bg-white { background-color: var(--c-surface) !important; color: var(--c-text); }
.dark-theme .bg-gray-50 { background-color: var(--c-bg) !important; }
.dark-theme .border-gray-200, .dark-theme .border-gray-100 { border-color: var(--c-border) !important; }
.dark-theme .text-gray-800, .dark-theme .text-gray-700, .dark-theme .text-gray-900 { color: var(--c-text) !important; }
.dark-theme .text-gray-600, .dark-theme .text-gray-500 { color: var(--c-text-2) !important; }
.dark-theme .text-gray-400 { color: var(--c-text-3) !important; }

.dark-theme .contracts-table tbody tr:hover { background: rgba(255,255,255,0.02); }
.dark-theme .pg-btn, .dark-theme .btn-action { background-color: var(--c-surface); color: var(--c-text-2); border-color: var(--c-border); }
.dark-theme .pg-btn:hover, .dark-theme .btn-action:hover { background-color: var(--c-blue-light); color: var(--c-blue-mid); }
.dark-theme .qa-btn { background-color: var(--c-surface); color: var(--c-text-2); border-color: var(--c-border); }
.dark-theme .qa-btn:hover { background-color: var(--c-gray-light); }
.dark-theme .search-input:focus { background-color: var(--c-surface); color: var(--c-text); }

.dash * { box-sizing: border-box; margin: 0; padding: 0; }
.dash { font-family: var(--font-body); color: var(--c-text); }

/* ---- Cabeçalho da página ---- */
.dash-header {
    display: flex; align-items: flex-end; justify-content: space-between;
    margin-bottom: 22px; gap: 16px; flex-wrap: wrap;
}
.dash-title { font-family: var(--font-display); font-size: 22px; font-weight: 600; color: var(--c-text); }
.dash-subtitle { font-size: 13px; color: var(--c-text-3); margin-top: 4px; }
.dash-header-actions { display: flex; gap: 8px; flex-shrink: 0; }

/* ---- KPI Cards ---- */
.kpi-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin-bottom: 20px; }

.kpi-card {
    background: var(--c-surface);
    border: 1px solid var(--c-border);
    border-radius: var(--radius-lg);
    padding: 20px 22px;
    box-shadow: var(--shadow);
    display: flex; align-items: flex-start; gap: 14px;
    position: relative; overflow: hidden;
    transition: box-shadow .2s, transform .2s;
}
.kpi-card:hover { box-shadow: var(--shadow-md); transform: translateY(-1px); }
.kpi-card::before {
    content: ''; position: absolute; top: 0; left: 0;
    width: 4px; height: 100%;
}
.kpi-card.blue::before   { background: var(--c-blue-mid); }
.kpi-card.red::before    { background: #D94040; }
.kpi-card.gold::before   { background: #D4A017; }
.kpi-card.green::before  { background: #22A05B; }

.kpi-icon {
    width: 44px; height: 44px; border-radius: var(--radius);
    display: flex; align-items: center; justify-content: center;
    font-size: 18px; flex-shrink: 0;
}
.kpi-icon.blue   { background: var(--c-blue-light); color: var(--c-blue-mid); }
.kpi-icon.red    { background: var(--c-red-light);  color: var(--c-red); }
.kpi-icon.gold   { background: var(--c-gold-light); color: var(--c-gold); }
.kpi-icon.green  { background: var(--c-green-light); color: var(--c-green); }

.kpi-body { flex: 1; min-width: 0; }
.kpi-label { font-size: 12px; font-weight: 500; color: var(--c-text-3); letter-spacing: .02em; }
.kpi-value { font-size: 28px; font-weight: 600; color: var(--c-text); line-height: 1.1; margin: 4px 0; }
.kpi-value.small { font-size: 20px; }
.kpi-sub   { font-size: 11px; color: var(--c-text-3); }
.kpi-badge {
    position: absolute; top: 16px; right: 16px;
    font-size: 10px; font-weight: 600; padding: 2px 8px;
    border-radius: 20px; letter-spacing: .03em;
}
.kpi-badge.up   { background: var(--c-green-light); color: var(--c-green); }
.kpi-badge.warn { background: var(--c-red-light); color: var(--c-red); }
.kpi-badge.info { background: var(--c-blue-light); color: var(--c-blue-mid); }

/* ---- Layout Principal ---- */
.dash-grid { display: grid; grid-template-columns: minmax(0, 1fr) 280px; gap: 16px; align-items: start; }

/* ---- Card Genérico ---- */
.card {
    background: var(--c-surface); min-width: 0;
    border: 1px solid var(--c-border);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow);
}
.card-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 16px 22px; border-bottom: 1px solid var(--c-border);
    gap: 12px;
}
.card-title { font-family: var(--font-display); font-size: 15px; font-weight: 600; color: var(--c-text); }
.card-body  { padding: 0; }

/* ---- Barra de Pesquisa + Filtros ---- */
.dash-toolbar {
    display: flex; align-items: center; gap: 10px;
    padding: 14px 22px; border-bottom: 1px solid var(--c-border);
    flex-wrap: wrap;
}
.search-wrap { position: relative; flex: 1; min-width: 180px; }
.search-icon { position: absolute; left: 11px; top: 50%; transform: translateY(-50%); color: var(--c-text-3); font-size: 13px; }
.search-input {
    width: 100%; padding: 8px 10px 8px 32px;
    font-family: var(--font-body); font-size: 13px;
    border: 1px solid var(--c-border); border-radius: var(--radius);
    background: var(--c-gray-light); color: var(--c-text); outline: none;
    transition: border-color .15s, background .15s;
}
.search-input:focus { border-color: var(--c-blue-mid); background: #fff; box-shadow: 0 0 0 3px rgba(46,110,197,.1); }
.filter-select {
    padding: 8px 28px 8px 10px; font-family: var(--font-body); font-size: 13px;
    border: 1px solid var(--c-border); border-radius: var(--radius);
    background: var(--c-gray-light) url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='11' height='11' viewBox='0 0 12 12'%3E%3Cpath fill='%238A97AE' d='M6 8L1 3h10z'/%3E%3C/svg%3E") no-repeat right 9px center;
    color: var(--c-text-2); outline: none; cursor: pointer; appearance: none;
    transition: border-color .15s;
}
.filter-select:focus { border-color: var(--c-blue-mid); }

/* ---- Tabela ---- */
.contracts-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.contracts-table thead th {
    padding: 10px 16px; text-align: left;
    font-size: 11px; font-weight: 600; letter-spacing: .06em; text-transform: uppercase;
    color: var(--c-text-3); background: var(--c-gray-light);
    border-bottom: 1px solid var(--c-border);
    white-space: nowrap;
}
.contracts-table thead th:last-child { text-align: right; }
.contracts-table tbody tr {
    border-bottom: 1px solid var(--c-border);
    transition: background .12s;
}
.contracts-table tbody tr:last-child { border-bottom: none; }
.contracts-table tbody tr:hover { background: #F7F9FD; }
.contracts-table td { padding: 13px 16px; vertical-align: middle; }
.contracts-table td:last-child { text-align: right; }

/* Célula de parte (avatar + nome) */
.td-parte { display: flex; align-items: center; gap: 10px; }
.td-avatar {
    width: 34px; height: 34px; border-radius: 50%; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    font-size: 12px; font-weight: 600;
}
.td-nome { font-weight: 500; color: var(--c-text); line-height: 1.3; }
.td-tipo { font-size: 11px; color: var(--c-text-3); margin-top: 1px; }

/* Tipo badge */
.tipo-badge {
    display: inline-block; font-size: 11px; font-weight: 500;
    padding: 2px 8px; border-radius: 4px;
    background: var(--c-gray-light); color: var(--c-text-2);
    white-space: nowrap;
}

/* Status pill */
.status-pill {
    display: inline-flex; align-items: center; gap: 5px;
    font-size: 11px; font-weight: 600;
    padding: 4px 10px; border-radius: 20px; white-space: nowrap;
}
.status-pill::before { content: '●'; font-size: 7px; line-height: 1; }
.status-vigente     { background: var(--c-green-light); color: var(--c-green); border: 1px solid var(--c-green-border); }
.status-pendente    { background: var(--c-gold-light);  color: var(--c-gold);  border: 1px solid var(--c-gold-border); }
.status-finalizado  { background: var(--c-blue-light);  color: var(--c-blue);  border: 1px solid var(--c-border-md); }
.status-cancelado   { background: var(--c-red-light);   color: var(--c-red);   border: 1px solid var(--c-red-border); }
.status-rascunho    { background: var(--c-gray-light);  color: var(--c-text-3); border: 1px solid var(--c-border); }

/* Vencimento com alerta */
.td-venc { display: flex; align-items: center; gap: 6px; }
.venc-alert { font-size: 11px; color: var(--c-red); font-weight: 600; background: var(--c-red-light); padding: 1px 6px; border-radius: 4px; }

/* Ações na tabela */
.td-acoes { display: flex; align-items: center; justify-content: flex-end; gap: 4px; }
.btn-action {
    display: inline-flex; align-items: center; justify-content: center;
    width: 30px; height: 30px; border-radius: var(--radius);
    border: 1px solid var(--c-border); background: var(--c-surface);
    color: var(--c-text-2); font-size: 12px; cursor: pointer;
    transition: background .12s, color .12s, border-color .12s;
    text-decoration: none;
}
.btn-action:hover { background: var(--c-blue-light); color: var(--c-blue-mid); border-color: var(--c-border-md); }
.btn-action.danger:hover { background: var(--c-red-light); color: var(--c-red); }
.btn-action.disabled { opacity: .35; pointer-events: none; }

/* Paginação */
.pagination { display: flex; align-items: center; justify-content: space-between; padding: 14px 22px; border-top: 1px solid var(--c-border); }
.pagination-info { font-size: 12px; color: var(--c-text-3); }
.pagination-nav { display: flex; gap: 4px; }
.pg-btn {
    display: inline-flex; align-items: center; justify-content: center;
    min-width: 30px; height: 30px; padding: 0 6px;
    border: 1px solid var(--c-border); border-radius: var(--radius);
    font-size: 12px; font-weight: 500; color: var(--c-text-2); text-decoration: none;
    background: var(--c-surface); transition: background .12s;
}
.pg-btn:hover { background: var(--c-blue-light); color: var(--c-blue-mid); border-color: var(--c-border-md); }
.pg-btn.active { background: var(--c-blue); color: #fff; border-color: var(--c-blue); }
.pg-btn.disabled { opacity: .4; pointer-events: none; }

/* Estado vazio */
.empty-state { padding: 48px 0; text-align: center; }
.empty-state i { font-size: 36px; color: var(--c-text-3); opacity: .4; margin-bottom: 12px; display: block; }
.empty-state p { font-size: 14px; color: var(--c-text-3); }

/* ---- Painel Lateral ---- */
.side-panel { display: flex; flex-direction: column; gap: 14px; }

/* Ações rápidas */
.quick-actions { display: flex; flex-direction: column; gap: 6px; padding: 16px; }
.qa-btn {
    display: flex; align-items: center; gap: 10px;
    padding: 10px 14px; border-radius: var(--radius);
    font-size: 13px; font-weight: 500; font-family: var(--font-body);
    border: 1px solid var(--c-border); cursor: pointer;
    transition: background .12s, color .12s; text-decoration: none;
    background: var(--c-surface); color: var(--c-text-2);
}
.qa-btn:hover { background: var(--c-gray-light); }
.qa-btn.primary { background: var(--c-blue); color: #fff; border-color: var(--c-blue); }
.qa-btn.primary:hover { background: var(--c-blue-hover); }
.qa-btn.warn-btn { border-color: var(--c-gold-border); background: var(--c-gold-light); color: var(--c-gold); }
.qa-btn.warn-btn:hover { background: #FFF0B0; }
.qa-btn i { width: 16px; text-align: center; flex-shrink: 0; }

/* Alertas críticos */
.alerts-list { display: flex; flex-direction: column; gap: 0; }
.alert-item {
    display: flex; align-items: flex-start; gap: 10px;
    padding: 12px 16px; border-bottom: 1px solid var(--c-border);
}
.alert-item:last-child { border-bottom: none; }
.alert-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; margin-top: 4px; }
.alert-dot.red  { background: #D94040; }
.alert-dot.gold { background: #D4A017; }
.alert-dot.blue { background: var(--c-blue-mid); }
.alert-text { font-size: 12px; color: var(--c-text-2); line-height: 1.5; }
.alert-text strong { color: var(--c-text); font-weight: 600; }
.alert-time { font-size: 11px; color: var(--c-text-3); margin-top: 2px; display: block; }

/* Distribuição por status (mini-barras) */
.dist-list { display: flex; flex-direction: column; gap: 12px; padding: 16px; }
.dist-item-label { display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 4px; }
.dist-item-label span:first-child { color: var(--c-text-2); font-weight: 500; }
.dist-item-label span:last-child  { color: var(--c-text-3); }
.dist-bar-track { height: 5px; background: var(--c-gray-light); border-radius: 20px; overflow: hidden; }
.dist-bar-fill  { height: 100%; border-radius: 20px; }

/* Nota informativa */
.info-note {
    margin: 0 16px 16px;
    padding: 10px 14px;
    background: var(--c-blue-light); border: 1px solid #C0D4EE;
    border-radius: var(--radius); font-size: 11.5px; color: var(--c-blue);
    line-height: 1.55; display: flex; gap: 8px;
}

/* ---- Botões de cabeçalho ---- */
.btn-primary {
    display: inline-flex; align-items: center; gap: 7px;
    padding: 8px 18px; border-radius: var(--radius);
    font-size: 13px; font-weight: 600; font-family: var(--font-body);
    background: var(--c-blue); color: #fff; border: none; cursor: pointer;
    box-shadow: 0 2px 8px rgba(27,79,140,.22);
    text-decoration: none; transition: background .12s;
}
.btn-primary:hover { background: var(--c-blue-hover); }
.btn-outline {
    display: inline-flex; align-items: center; gap: 7px;
    padding: 8px 14px; border-radius: var(--radius);
    font-size: 13px; font-weight: 500; font-family: var(--font-body);
    background: var(--c-surface); color: var(--c-text-2);
    border: 1px solid var(--c-border); cursor: pointer; text-decoration: none;
    transition: background .12s;
}
.btn-outline:hover { background: var(--c-gray-light); }

/* ---- Modal aprimorado ---- */
.ctr-modal-overlay {
    position: fixed; inset: 0;
    background: rgba(20,30,50,.48);
    display: flex; align-items: flex-start; justify-content: center;
    padding: 40px 16px; z-index: 100;
    opacity: 0; pointer-events: none; transition: opacity .2s;
}
.ctr-modal-overlay.open { opacity: 1; pointer-events: all; }
.ctr-modal-box {
    background: var(--c-surface);
    border-radius: var(--radius-xl);
    width: 100%; max-width: 820px;
    max-height: calc(100vh - 80px);
    display: flex; flex-direction: column;
    box-shadow: 0 20px 60px rgba(0,0,0,.18);
    transform: translateY(12px); transition: transform .2s;
}
.ctr-modal-overlay.open .ctr-modal-box { transform: translateY(0); }
.ctr-modal-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 18px 24px; border-bottom: 1px solid var(--c-border); flex-shrink: 0;
}
.ctr-modal-title { font-family: var(--font-display); font-size: 17px; font-weight: 600; color: var(--c-text); }
.ctr-modal-close {
    width: 32px; height: 32px; border-radius: var(--radius);
    display: flex; align-items: center; justify-content: center;
    background: var(--c-gray-light); border: none; cursor: pointer;
    color: var(--c-text-2); font-size: 14px; transition: background .12s;
}
.ctr-modal-close:hover { background: var(--c-red-light); color: var(--c-red); }
.ctr-modal-body { overflow-y: auto; padding: 24px; flex: 1; }

/* ---- Responsivo ---- */
@media (max-width: 1100px) { .kpi-grid { grid-template-columns: 1fr 1fr; } }
@media (max-width: 900px)  { .dash-grid { grid-template-columns: 1fr; } }
@media (max-width: 640px)  { .kpi-grid { grid-template-columns: 1fr; } .dash-toolbar { gap: 6px; } }
</style>

<div class="dash">

<!-- ════════ CABEÇALHO ════════ -->
<div class="dash-header">
    <div>
        <h2 class="dash-title">Gestão de Contratos</h2>
        <p class="dash-subtitle">Acompanhamento centralizado de contratos ativos, prazos e pendências</p>
    </div>
    <div class="dash-header-actions">
        <button class="btn-outline" onclick="openAjaxModal('<?= BASE_URL ?>/contratos/uploadDocumento', 'Upload de Documento')">
            <i class="fas fa-upload"></i> Upload
        </button>
        <a href="<?= BASE_URL ?>/contratos/wizard" class="btn-primary">
            <i class="fas fa-plus"></i> Novo Contrato
        </a>
    </div>
</div>

<!-- ════════ KPI CARDS ════════ -->
<div class="kpi-grid">
    <!-- 1. Vigentes -->
    <div class="kpi-card blue">
        <div class="kpi-icon blue"><i class="fas fa-file-contract"></i></div>
        <div class="kpi-body">
            <div class="kpi-label">Contratos Vigentes</div>
            <div class="kpi-value"><?= $totalVigentes ?? 0 ?></div>
            <div class="kpi-sub">Clientes e fornecedores ativos</div>
        </div>
        <span class="kpi-badge info">Ativo</span>
    </div>
    <!-- 2. Vencendo 30 dias -->
    <div class="kpi-card red">
        <div class="kpi-icon red"><i class="fas fa-clock"></i></div>
        <div class="kpi-body">
            <div class="kpi-label">Vencendo em 30 dias</div>
            <div class="kpi-value"><?= $vencendo30dias ?? 0 ?></div>
            <div class="kpi-sub">Renovação obrigatória</div>
        </div>
        <?php if (($vencendo30dias ?? 0) > 0): ?>
            <span class="kpi-badge warn">Urgente</span>
        <?php endif; ?>
    </div>
    <!-- 3. Pendências -->
    <div class="kpi-card gold">
        <div class="kpi-icon gold"><i class="fas fa-pen-nib"></i></div>
        <div class="kpi-body">
            <div class="kpi-label">Pendente de Assinatura</div>
            <div class="kpi-value"><?= $comPendenciaDocs ?? 0 ?></div>
            <div class="kpi-sub">Aguardando legalização</div>
        </div>
        <?php if (($comPendenciaDocs ?? 0) > 0): ?>
            <span class="kpi-badge warn">Atenção</span>
        <?php endif; ?>
    </div>
    <!-- 4. Valor total -->
    <div class="kpi-card green">
        <div class="kpi-icon green"><i class="fas fa-dollar-sign"></i></div>
        <div class="kpi-body">
            <div class="kpi-label">Valor Total Anual (Previsto)</div>
            <div class="kpi-value small"><?= $valorTotalAnual ?? 'R$ 0,00' ?></div>
            <div class="kpi-sub">Receitas e despesas contratuais</div>
        </div>
        <span class="kpi-badge up">Anual</span>
    </div>
</div>

<!-- ════════ LAYOUT PRINCIPAL ════════ -->
<div class="dash-grid">

    <!-- ── COLUNA ESQUERDA: TABELA ── -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">Lista de Contratos</div>
            <span style="font-size:12px;color:var(--c-text-3)">
                <?= isset($totalContratos) ? $totalContratos . ' registro(s) encontrado(s)' : '' ?>
            </span>
        </div>

        <!-- Toolbar de busca e filtros -->
        <div class="dash-toolbar">
            <div class="search-wrap">
                <i class="fas fa-search search-icon"></i>
                <input type="text" class="search-input" id="busca-contrato"
                       placeholder="Buscar por título, parte ou valor..."
                       value="<?= htmlspecialchars($filtros['busca'] ?? '') ?>">
            </div>
            <select class="filter-select" id="filtro-tipo"
                    onchange="aplicarFiltros()">
                <option value="">Todos os tipos</option>
                <?php foreach (['Prestação de Serviço','Compra / Fornecimento','Parceria','Locação','Consultoria','Outro'] as $t): ?>
                    <option value="<?= $t ?>" <?= (($filtros['tipo'] ?? '') === $t) ? 'selected' : '' ?>><?= $t ?></option>
                <?php endforeach; ?>
            </select>
            <select class="filter-select" id="filtro-status"
                    onchange="aplicarFiltros()">
                <option value="">Todos os status</option>
                <?php foreach (['Em Vigência','Pendente Assinatura','Rascunho','Finalizado','Cancelado'] as $s): ?>
                    <option value="<?= $s ?>" <?= (($filtros['status'] ?? '') === $s) ? 'selected' : '' ?>><?= $s ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Tabela -->
        <div class="card-body" style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;">
            <?php if (!empty($contratos)): ?>
            <table class="contracts-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Base de Referência</th>
                        <th>ID/CTR-CLIENTE</th>
                        <th style="min-width: 320px;">Parte Contratante / Contratada</th>
                        <th>Tipo</th>
                        <th>Valor</th>
                        <th>Vencimento</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                // Paleta de cores para avatares (rotacional)
                $avatarColors = [
                    ['#EBF2FB','#1B4F8C'],['#E6F5EE','#17673E'],
                    ['#EEEDFB','#4A35A8'],['#FFF7E0','#916A00'],
                    ['#FDF0F0','#921C1C'],['#F0F3F8','#4A5878'],
                ];
                $colorIdx = 0;

                foreach ($contratos as $contrato):
                    $contratante = (!empty($contrato['contratante_nome'])) ? $contrato['contratante_nome'] : ($contrato['cliente_nome'] ?? 'N/A');
                    $contratada = (!empty($contrato['contratado_nome'])) ? $contrato['contratado_nome'] : ($contrato['parteContratada'] ?? 'N/A');
                    $initials = implode('', array_map(fn($w) => strtoupper($w[0]), array_filter(explode(' ', $contratante))));
                    $initials = substr($initials, 0, 2);
                    [$bgColor, $fgColor] = $avatarColors[$colorIdx % count($avatarColors)];
                    $colorIdx++;

                    // Dias para o vencimento
                    $diasVenc = null;
                    if (!empty($contrato['vencimento'])) {
                        $hoje = new DateTime();
                        $dtVenc = new DateTime($contrato['vencimento']);
                        $diasVenc = (int)$hoje->diff($dtVenc)->format('%r%a');
                    }

                    // Classe do status
                    $statusClass = match($contrato['status']) {
                        'Em Vigência'          => 'status-vigente',
                        'Pendência Assinatura', 'Pendente Assinatura' => 'status-pendente',
                        'Finalizado'           => 'status-finalizado',
                        'Cancelado'            => 'status-cancelado',
                        default                => 'status-rascunho',
                    };
                ?>
                <tr <?= ($diasVenc !== null && $diasVenc < 0) ? 'style="background-color: var(--c-red-light);"' : '' ?>>
                    <!-- ID / Número do Contrato -->
                    <td style="font-weight:600;color:var(--c-blue-mid);white-space:nowrap">
                        <?= htmlspecialchars($contrato['numero_contrato'] ?? $contrato['id']) ?>
                    </td>
                    <td>
                        <?= htmlspecialchars($contrato['base_referencia'] ?? 'N/A') ?>
                    </td>
                    <td>
                        <?= htmlspecialchars($contrato['numero_contrato_cliente'] ?? 'N/A') ?>
                    </td>
                    <!-- Parte -->
                    <td>
                        <div class="td-parte">
                            <div class="td-avatar" style="background:<?= $bgColor ?>;color:<?= $fgColor ?>"><?= $initials ?></div>
                            <div>
                                <div class="td-nome" title="Contratante"><?= htmlspecialchars($contratante) ?></div>
                                <div class="td-tipo">Contratada: <?= htmlspecialchars($contratada) ?></div>
                            </div>
                        </div>
                    </td>
                    <!-- Tipo -->
                    <td><span class="tipo-badge"><?= htmlspecialchars($contrato['tipo']) ?></span></td>
                    <!-- Valor -->
                    <td style="font-weight:500;white-space:nowrap">R$ <?= number_format($contrato['valor'] ?? 0, 2, ',', '.') ?></td>
                    <!-- Vencimento -->
                    <td>
                        <div class="td-venc" <?= ($diasVenc !== null && $diasVenc < 0) ? 'style="color: var(--c-red); font-weight: 600;"' : '' ?>>
                            <?= $contrato['vencimento'] ? date('d/m/Y', strtotime($contrato['vencimento'])) : '—' ?>
                            <?php if ($diasVenc !== null && $diasVenc >= 0 && $diasVenc <= 30): ?>
                                <span class="venc-alert"><?= $diasVenc ?>d</span>
                            <?php elseif ($diasVenc !== null && $diasVenc < 0): ?>
                                <span class="venc-alert" style="background:var(--c-red);color:#fff">Vencido</span>
                            <?php endif; ?>
                        </div>
                    </td>
                    <!-- Status -->
                    <td><span class="status-pill <?= $statusClass ?>"><?= htmlspecialchars($contrato['status']) ?></span></td>
                    <!-- Ações -->
                    <td>
                        <div class="td-acoes">
                            <?php if ($contrato['status'] !== 'Finalizado' && $contrato['status'] !== 'Cancelado'): ?>
                            <a href="<?= BASE_URL ?>/contratos/enviarParaAssinatura/<?= $contrato['id'] ?>" 
                               class="btn-action" 
                               title="Enviar para assinatura digital"
                               onclick="return confirm('Deseja enviar o link de assinatura eletrônica para <?= htmlspecialchars($contrato['contratante_email'] ?? 'o cliente') ?>?')">
                                <i class="fas fa-paper-plane" style="color: var(--c-blue-mid)"></i>
                            </a>
                            <?php endif; ?>
                            <a href="<?= BASE_URL ?>/contratos/detalhe/<?= $contrato['id'] ?>" class="btn-action" title="Ver detalhes">
                                <i class="fas fa-eye"></i>
                            </a>
                            <?php if ($contrato['status'] !== 'Cancelado'): ?>
                            <a href="<?= BASE_URL ?>/contratos/clonar/<?= $contrato['id'] ?>" class="btn-action" title="Duplicar contrato">
                                <i class="far fa-copy"></i>
                            </a>
                            <?php else: ?>
                            <span class="btn-action disabled" title="Não é permitido duplicar contratos cancelados">
                                <i class="far fa-copy"></i>
                            </span>
                            <?php endif; ?>
                            <?php if (!empty($contrato['documento_path'])): ?>
                            <a href="<?= BASE_URL ?>/contratos/download/<?= htmlspecialchars($contrato['documento_path']) ?>" target="_blank" class="btn-action" title="Baixar documento">
                                <i class="fas fa-download"></i>
                            </a>
                            <?php endif; ?>
                            <?php if ($contrato['status'] !== 'Finalizado'): ?>
                            <a href="<?= BASE_URL ?>/contratos/wizard/<?= $contrato['id'] ?>" class="btn-action" title="Editar contrato">
                                <i class="fas fa-pen"></i>
                            </a>
                            <?php else: ?>
                            <span class="btn-action disabled" title="Contratos finalizados não podem ser editados">
                                <i class="fas fa-pen"></i>
                            </span>
                            <?php endif; ?>
                            <a href="<?= BASE_URL ?>/contratos/excluir/<?= $contrato['id'] ?>"
                               class="btn-action danger"
                               title="Excluir contrato"
                               onclick="return confirm('Tem certeza que deseja excluir este contrato? Esta ação não pode ser desfeita.')">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-file-contract"></i>
                <p>Nenhum contrato encontrado.</p>
                <a href="<?= BASE_URL ?>/contratos/wizard" class="btn-primary" style="margin-top:16px;display:inline-flex">
                    <i class="fas fa-plus"></i> Criar primeiro contrato
                </a>
            </div>
            <?php endif; ?>
        </div>

        <!-- Paginação -->
        <?php if (($totalPaginas ?? 1) > 1): ?>
        <div class="pagination">
            <div class="pagination-info">
                Página <?= $paginaAtual ?? 1 ?> de <?= $totalPaginas ?? 1 ?>
            </div>
            <?php
            $qs = http_build_query(array_merge($filtros ?? [], ['page' => '']));
            $pa = $paginaAtual ?? 1;
            $pt = $totalPaginas ?? 1;
            ?>
            <nav class="pagination-nav">
                <a href="<?= BASE_URL ?>/contratos?<?= $qs . ($pa - 1) ?>" class="pg-btn <?= $pa <= 1 ? 'disabled' : '' ?>">
                    <i class="fas fa-chevron-left" style="font-size:10px"></i>
                </a>
                <?php for ($i = max(1, $pa-2); $i <= min($pt, $pa+2); $i++): ?>
                    <a href="<?= BASE_URL ?>/contratos?<?= $qs . $i ?>" class="pg-btn <?= $i == $pa ? 'active' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>
                <a href="<?= BASE_URL ?>/contratos?<?= $qs . ($pa + 1) ?>" class="pg-btn <?= $pa >= $pt ? 'disabled' : '' ?>">
                    <i class="fas fa-chevron-right" style="font-size:10px"></i>
                </a>
            </nav>
        </div>
        <?php endif; ?>
    </div>

    <!-- ── COLUNA DIREITA: PAINEL LATERAL ── -->
    <div class="side-panel">

        <!-- Ações Rápidas -->
        <div class="card">
            <div class="card-header" style="padding:14px 16px">
                <div class="card-title" style="font-size:14px">Ações Rápidas</div>
            </div>
            <div class="quick-actions">
                <button id="open-alerta-modal-btn" class="qa-btn warn-btn">
                    <i class="fas fa-bell"></i> Enviar Alerta de Renovação
                </button>
                <a href="<?= BASE_URL ?>/contratos/configuracoes" class="qa-btn">
                    <i class="fas fa-cog"></i> Modelos de Contrato
                </a>
                <a href="<?= BASE_URL ?>/contratos/exportar" class="qa-btn">
                    <i class="fas fa-file-export"></i> Exportar Relatório
                </a>
            </div>
        </div>

        <!-- Alertas Críticos -->
        <?php
        $alertasVencimento = array_filter($contratos ?? [], function($c) {
            if (empty($c['vencimento'])) return false;
            $dias = (int)(new DateTime())->diff(new DateTime($c['vencimento']))->format('%r%a');
            return $dias >= 0 && $dias <= 30;
        });
        $alertasVencimento = array_slice($alertasVencimento, 0, 4);
        ?>
        <?php if (!empty($alertasVencimento) || ($comPendenciaDocs ?? 0) > 0): ?>
        <div class="card">
            <div class="card-header" style="padding:14px 16px">
                <div class="card-title" style="font-size:14px">
                    <i class="fas fa-triangle-exclamation" style="color:#D94040;margin-right:6px;font-size:13px"></i>
                    Alertas Críticos
                </div>
            </div>
            <div class="alerts-list">
                <?php foreach ($alertasVencimento as $ac):
                    $dias = (int)(new DateTime())->diff(new DateTime($ac['vencimento']))->format('%r%a');
                    $dotClass = $dias <= 7 ? 'red' : 'gold';
                ?>
                <div class="alert-item">
                    <div class="alert-dot <?= $dotClass ?>"></div>
                    <div class="alert-text">
                        <strong><?= htmlspecialchars($ac['parteContratada'] ?? 'Contrato') ?></strong>
                        vence em <?= $dias ?> dia<?= $dias != 1 ? 's' : '' ?>
                        <span class="alert-time"><?= date('d/m/Y', strtotime($ac['vencimento'])) ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php if (($comPendenciaDocs ?? 0) > 0): ?>
                <div class="alert-item">
                    <div class="alert-dot blue"></div>
                    <div class="alert-text">
                        <strong><?= $comPendenciaDocs ?> contrato<?= $comPendenciaDocs > 1 ? 's' : '' ?></strong>
                        aguardando assinatura/documentação
                        <span class="alert-time">Regularização pendente</span>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Distribuição por Status -->
        <?php
        $statusDist = ['Em Vigência' => 0, 'Pendente Assinatura' => 0, 'Rascunho' => 0, 'Finalizado' => 0, 'Cancelado' => 0];
        foreach ($contratos ?? [] as $c) {
            $st = $c['status'] ?? 'Rascunho';
            if (isset($statusDist[$st])) $statusDist[$st]++;
        }
        $totalDist = array_sum($statusDist);
        $distColors = ['Em Vigência' => '#22A05B', 'Pendente Assinatura' => '#D4A017', 'Rascunho' => '#8A97AE', 'Finalizado' => '#2E6EC5', 'Cancelado' => '#D94040'];
        ?>
        <div class="card">
            <div class="card-header" style="padding:14px 16px">
                <div class="card-title" style="font-size:14px">Distribuição por Status</div>
            </div>
            <div class="dist-list">
                <?php foreach ($statusDist as $status => $qtd):
                    $pct = $totalDist > 0 ? round($qtd / $totalDist * 100) : 0;
                    $cor = $distColors[$status] ?? '#8A97AE';
                ?>
                <div>
                    <div class="dist-item-label">
                        <span><?= $status ?></span>
                        <span><?= $qtd ?> (<?= $pct ?>%)</span>
                    </div>
                    <div class="dist-bar-track">
                        <div class="dist-bar-fill" style="width:<?= $pct ?>%;background:<?= $cor ?>"></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Nota -->
        <div class="info-note">
            <i class="fas fa-shield-halved" style="flex-shrink:0;margin-top:1px"></i>
            <span>A documentação completa deve ser arquivada digitalmente e estar em conformidade com as cláusulas legais vigentes — LGPD e CC/2002.</span>
        </div>

    </div><!-- /side-panel -->

</div><!-- /dash-grid -->
</div><!-- /dash -->


<!-- ════════ MODAL APRIMORADO ════════ -->
<div id="ctr-modal" class="ctr-modal-overlay" role="dialog" aria-modal="true">
    <div class="ctr-modal-box">
        <div class="ctr-modal-header">
            <h3 id="ctr-modal-title" class="ctr-modal-title"></h3>
            <button id="ctr-modal-close" class="ctr-modal-close" aria-label="Fechar">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div id="ctr-modal-body" class="ctr-modal-body">
            <!-- Conteúdo AJAX -->
        </div>
    </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', function () {

    /* ── Modal ── */
    const overlay   = document.getElementById('ctr-modal');
    const modalTitle = document.getElementById('ctr-modal-title');
    const modalBody  = document.getElementById('ctr-modal-body');
    const closeBtn   = document.getElementById('ctr-modal-close');

    window.openModal  = () => overlay.classList.add('open');
    window.closeModal = () => {
        overlay.classList.remove('open');
        modalBody.innerHTML = '';
    };

    window.openAjaxModal = async (url, title) => {
        modalTitle.textContent = title;
        modalBody.innerHTML = '<p style="text-align:center;padding:32px;color:var(--c-text-3)"><i class="fas fa-spinner fa-spin"></i> Carregando...</p>';
        openModal();
        try {
            const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            if (!res.ok) throw new Error('Falha ao carregar o conteúdo.');
            const html = await res.text();
            modalBody.innerHTML = html;
            // Re-executa scripts carregados via AJAX
            modalBody.querySelectorAll('script').forEach(oldScript => {
                const s = document.createElement('script');
                s.textContent = oldScript.textContent;
                document.body.appendChild(s).parentNode.removeChild(s);
                oldScript.remove();
            });
        } catch (err) {
            modalBody.innerHTML = `<p style="color:var(--c-red);text-align:center;padding:24px">${err.message}</p>`;
        }
    };

    closeBtn.addEventListener('click', closeModal);
    overlay.addEventListener('click', e => { if (e.target === overlay) closeModal(); });
    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });

    // Delegação para botão cancelar dentro do modal
    overlay.addEventListener('click', e => {
        if (e.target.closest('#cancel-form-btn')) closeModal();
    });

    /* ── Ações laterais ── */
    const btnAlerta  = document.getElementById('open-alerta-modal-btn');

    if (btnAlerta) btnAlerta.addEventListener('click', () =>
        openAjaxModal('<?= BASE_URL ?>/contratos/enviarAlerta', 'Enviar Alerta de Renovação'));

    /* ── Busca com debounce ── */
    let debounceTimer;
    const buscaInput = document.getElementById('busca-contrato');
    if (buscaInput) {
        buscaInput.addEventListener('input', () => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(aplicarFiltros, 420);
        });
    }

    /* ── Aplicar filtros via URL ── */
    window.aplicarFiltros = function () {
        const busca  = document.getElementById('busca-contrato')?.value ?? '';
        const tipo   = document.getElementById('filtro-tipo')?.value ?? '';
        const status = document.getElementById('filtro-status')?.value ?? '';
        const params = new URLSearchParams({ busca, tipo, status, page: 1 });
        window.location.href = '<?= BASE_URL ?>/contratos?' + params.toString();
    };

});
</script>