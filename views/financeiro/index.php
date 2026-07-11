<?php
/* ============================================================
   MÓDULO FINANCEIRO — Dashboard Principal
   Arquivo: views/financeiro/index.php
   Compatível com: Tailwind CSS + customizações
   ============================================================ */

/**
 * Chart.js - Garantimos a inclusão local caso não esteja no layout pai.
 * O uso do BASE_URL dinâmico previne problemas de caminhos relativos em subdiretórios de produção.
 */
?>
<!-- Carregamento do Chart.js com fallback para CDN caso o arquivo local falhe (404) -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@500;700&display=swap" rel="stylesheet">
<script src="<?= BASE_URL; ?>/assets/js/chart.umd.min.js" onerror="this.onerror=null; this.src='https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js';"></script>

<?php
if (session_status() == PHP_SESSION_NONE) session_start();
?>

<!-- ── ESTILOS CUSTOMIZADOS ──────────────────────────────── -->
<style>
  /* Variáveis de cor do tema financeiro */
  :root {
    --fin-red:       var(--db-red, #dc2626);
    --fin-red-light: rgba(220, 38, 38, 0.15);
    --fin-red-mid:   #fca5a5;
    --fin-green:     var(--db-green, #16a34a);
    --fin-green-light: rgba(22, 163, 74, 0.15);
    --fin-blue:      var(--db-blue, #1d4ed8);
    --fin-blue-light: rgba(29, 78, 216, 0.15);
    --fin-amber:     var(--db-orange, #b45309);
    --fin-amber-light: rgba(180, 83, 9, 0.15);
    --fin-sky:       var(--db-accent, #0369a1);
    --fin-sky-light: rgba(3, 105, 161, 0.15);
    --fin-border:    var(--db-border, #e5e7eb);
    --fin-surface:   var(--db-surface, #ffffff);
    --fin-surface2:  var(--db-surface2, #f9fafb);
    --fin-text:      var(--db-text, #111827);
    --fin-muted:     var(--db-text2, #6b7280);
    --fin-shadow:    0 1px 3px rgba(0,0,0,.08), 0 1px 2px rgba(0,0,0,.05);
    --fin-shadow-md: 0 4px 12px rgba(0,0,0,.08);
  }

  /* Força a atualização das variáveis quando o tema escuro está ativo */
  .dark-theme {
    --fin-red:       var(--db-red, #ef4444);
    --fin-green:     var(--db-green, #10b981);
    --fin-blue:      var(--db-blue, #3b82f6);
    --fin-amber:     var(--db-orange, #f59e0b);
    --fin-sky:       var(--db-accent, #0ea5e9);
    --fin-surface:   var(--db-surface, #1f2937);
    --fin-surface2:  var(--db-surface2, #374151);
    --fin-border:    var(--db-border, #374151);
    --fin-text:      var(--db-text, #f3f4f6);
    --fin-muted:     var(--db-text2, #94a3b8);
  }

  .dark-theme #finance-module-container input,
  .dark-theme #finance-module-container select {
    background-color: var(--fin-surface);
    color: var(--fin-text);
    border-color: var(--fin-border);
  }

  /* ── Container de Conteúdo Centralizado ── */
  .fin-content-area { width: 100%; max-width: 1200px; margin: 0 auto; }

  /* Cards base */
  .fin-card {
    background: var(--fin-surface);
    border: 1px solid var(--fin-border);
    border-radius: 12px;
    box-shadow: var(--fin-shadow);
  }
  .fin-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 1.25rem;
    border-bottom: 1px solid var(--fin-border);
  }
  .fin-card-title {
    font-size: 14px;
    font-weight: 600;
    color: var(--fin-text);
    letter-spacing: -.01em;
  }

  /* KPI Cards */
  .kpi-card {
    background: var(--fin-surface);
    border: 1px solid var(--fin-border);
    border-radius: 12px;
    padding: 1.25rem;
    box-shadow: var(--fin-shadow);
    position: relative;
    overflow: hidden;
    transition: box-shadow .2s, transform .2s;
  }
  .kpi-card:hover { box-shadow: var(--fin-shadow-md); transform: translateY(-1px); }
  .kpi-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
    border-radius: 12px 12px 0 0;
  }
  .kpi-card.red::before   { background: var(--fin-red); }
  .kpi-card.green::before { background: var(--fin-green); }
  .kpi-card.blue::before  { background: var(--fin-blue); }
  .kpi-card.amber::before { background: var(--fin-amber); }

  .kpi-icon {
    width: 40px; height: 40px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
  }
  .kpi-label { font-size: 11px; font-weight: 600; color: var(--fin-muted); text-transform: uppercase; letter-spacing: .06em; }
  .kpi-value { font-size: 24px; font-weight: 700; letter-spacing: -.02em; line-height: 1.15; margin-top: 4px; }
  .kpi-bar   { height: 4px; border-radius: 2px; background: var(--fin-surface2); margin-top: 12px; overflow: hidden; }
  .kpi-bar-fill { height: 100%; border-radius: 2px; transition: width .6s cubic-bezier(.4,0,.2,1); }

  /* Badges */
  .fin-badge {
    display: inline-flex; align-items: center; gap: 4px;
    font-size: 11px; font-weight: 600;
    padding: 2px 8px; border-radius: 999px;
  }
  .fin-badge.red    { background: var(--fin-red-light);   color: var(--fin-red); }
  .fin-badge.green  { background: var(--fin-green-light); color: var(--fin-green); }
  .fin-badge.blue   { background: var(--fin-blue-light);  color: var(--fin-blue); }
  .fin-badge.sky    { background: var(--fin-sky-light);   color: var(--fin-sky); }
  .fin-badge.amber  { background: var(--fin-amber-light); color: var(--fin-amber); }
  .fin-badge.gray   { background: #f3f4f6; color: #374151; }
  .dark-theme .fin-badge.gray { background: rgba(148,163,184,.12); color: #d1d5db; }

  /* Alert */
  .fin-alert {
    display: flex; gap: 12px; align-items: flex-start;
    background: rgba(220, 38, 38, 0.1);
    border: 1px solid rgba(220, 38, 38, 0.3);
    border-left: 4px solid var(--fin-red);
    border-radius: 0 10px 10px 0;
    padding: 12px 16px;
    margin-bottom: 1.5rem;
    font-size: 13px;
    color: var(--fin-text);
  }
  .fin-alert-icon { color: var(--fin-red); flex-shrink: 0; margin-top: 1px; }

  /* Listas de contas */
  .conta-item {
    display: flex; justify-content: space-between; align-items: flex-start;
    padding: 8px 0;
    border-bottom: 1px solid var(--fin-border);
    font-size: 13px;
  }
  .conta-item:last-child { border-bottom: none; }
  .conta-desc { color: var(--fin-text); padding-right: 8px; flex: 1; min-width: 0; }
  .conta-meta { display: flex; align-items: center; gap: 8px; flex-shrink: 0; }
  .conta-date { font-size: 10px; color: var(--fin-muted); }

  /* Saldo banco item */
  .banco-item {
    display: flex; justify-content: space-between; align-items: center;
    padding: 10px 8px;
    border-radius: 10px;
    transition: all .2s ease;
  }
  .banco-item:hover { background: var(--fin-surface); }
  .banco-avatar {
    width: 34px; height: 34px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 11px; font-weight: 700;
    border: 1px solid var(--fin-border);
    background: var(--fin-surface);
    flex-shrink: 0;
  }

  /* Tabs toggle */
  .fin-tabs {
    display: flex;
    background: var(--fin-surface);
    border: 1px solid var(--fin-border);
    border-radius: 8px;
    padding: 3px;
    gap: 2px;
  }
  .fin-tab {
    flex: 1; font-size: 12px; font-weight: 500;
    padding: 5px 10px; border-radius: 6px;
    border: none; cursor: pointer;
    color: var(--fin-muted); background: transparent;
    transition: all .15s;
  }
  .fin-tab.active { background: var(--fin-surface); color: var(--fin-blue); box-shadow: 0 1px 3px rgba(0,0,0,.1); }

  /* Tabela de movimentações */
  .fin-table { width: 100%; border-collapse: collapse; font-size: 13px; background: var(--fin-surface); color: var(--fin-text); }
  .fin-table th {
    padding: 10px 14px;
    font-size: 11px; font-weight: 600; color: var(--fin-muted);
    text-transform: uppercase; letter-spacing: .06em;
    background: var(--fin-surface);
    border-bottom: 1px solid var(--fin-border);
    text-align: left;
    white-space: nowrap;
  }
  .fin-table th.center { text-align: center; }
  .fin-table th.right  { text-align: right; }
  .fin-table td {
    padding: 11px 14px;
    border-bottom: 1px solid var(--fin-border);
    color: var(--fin-text);
    vertical-align: middle;
  }
  .fin-table td.center { text-align: center; }
  .fin-table td.right  { text-align: right; }
  .fin-table tbody tr:hover td { background: rgba(0,0,0,0.02); }
  .dark-theme .fin-table tbody tr:hover td { background: rgba(255,255,255,0.05); }
  .fin-table tbody tr:last-child td { border-bottom: none; }

  /* Action links na tabela */
  .tbl-action { font-size: 12px; font-weight: 500; text-decoration: none; transition: opacity .15s; }
  .tbl-action:hover { opacity: .7; }

  /* Modal */
  .fin-modal-overlay {
    position: fixed; inset: 0; z-index: 50;
    background: rgba(0,0,0,.45);
    display: flex; align-items: center; justify-content: center;
    padding: 1rem;
  }
  .fin-modal {
    background: var(--fin-surface);
    border-radius: 14px;
    box-shadow: 0 20px 60px rgba(0,0,0,.18);
    width: 100%; max-width: 500px;
    overflow: hidden;
    animation: modalIn .2s cubic-bezier(.34,1.56,.64,1);
  }
  .fin-modal.wide { max-width: 680px; }
  @keyframes modalIn { from { opacity:0; transform:scale(.95) translateY(10px); } to { opacity:1; transform:none; } }
  .fin-modal-header {
    display: flex; justify-content: space-between; align-items: center;
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid var(--fin-border);
  }
  .fin-modal-title { font-size: 16px; font-weight: 600; color: var(--fin-text); }
  .fin-modal-body  { padding: 1.5rem; max-height: 70vh; overflow-y: auto; }
  .fin-modal-footer {
    padding: 1rem 1.5rem;
    background: var(--fin-surface);
    border-top: 1px solid var(--fin-border);
    display: flex; justify-content: flex-end; gap: 8px;
  }

  /* Buttons */
  .fin-btn {
    display: inline-flex; align-items: center; gap: 6px;
    font-size: 13px; font-weight: 500;
    padding: 7px 14px; border-radius: 8px;
    border: 1px solid var(--fin-border);
    cursor: pointer; text-decoration: none;
    transition: all .15s; white-space: nowrap;
  }
  .fin-btn:hover { opacity: .88; transform: translateY(-1px); }
  .fin-btn:active { transform: none; }
  .fin-btn.primary { background: var(--fin-blue);  color: #fff; border-color: var(--fin-blue); }
  .fin-btn.success { background: var(--fin-green); color: #fff; border-color: var(--fin-green); }
  .fin-btn.danger  { background: var(--fin-red);   color: #fff; border-color: var(--fin-red); }
  .fin-btn.sky     { background: var(--fin-sky);   color: #fff; border-color: var(--fin-sky); }
  .fin-btn.ghost   { background: var(--fin-surface); color: var(--fin-text); }
  .fin-btn.ghost-red { background: var(--fin-red-light); color: var(--fin-red); border-color: var(--fin-red-mid); }

  /* Form fields no modal */
  .fin-label { display: block; font-size: 12px; font-weight: 600; color: var(--fin-muted); text-transform: uppercase; letter-spacing: .05em; margin-bottom: 6px; }
  .fin-input, .fin-select {
    width: 100%; padding: 9px 12px;
    border: 1px solid var(--fin-border);
    border-radius: 8px; font-size: 13px;
    color: var(--fin-text); background: var(--fin-surface);
    transition: border-color .15s, box-shadow .15s;
    outline: none;
  }
  .fin-input:focus, .fin-select:focus {
    border-color: #93c5fd;
    box-shadow: 0 0 0 3px rgba(59,130,246,.12);
  }

  /* ── Ajustes de Estilo Adicionais (Modo Escuro) ── */
  .projection-box { background: var(--fin-green-light); border: 1px solid var(--fin-green); border-radius: 10px; padding: 14px 16px; margin-top: 12px; color: var(--fin-text); }
  .projection-box.negative { background: var(--fin-red-light); border-color: var(--fin-red); }
  
  .dark-theme .bg-red-50 { background-color: var(--fin-red-light) !important; color: var(--fin-red) !important; border-color: rgba(220, 38, 38, 0.3) !important; }
  .dark-theme .bg-blue-600 { background-color: var(--fin-blue) !important; }
  .dark-theme .divide-gray-50 { border-color: var(--fin-border) !important; }

  /* Scrollbar customizada */
  .fin-scroll::-webkit-scrollbar { width: 4px; }
  .fin-scroll::-webkit-scrollbar-track { background: transparent; }
  .fin-scroll::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 2px; }

  /* Containers de Gráficos Responsivos */
  .chart-wrapper-main { position: relative; width: 100%; height: 240px; }
  .chart-wrapper-dist { position: relative; width: 100%; height: 160px; }

  /* Flash messages */
  .fin-flash { padding: 12px 16px; border-radius: 10px; margin-bottom: 1rem; font-size: 13px; border: 1px solid; }
  .fin-flash.success { background: var(--fin-green-light); border-color: #86efac; color: #15803d; }
  .fin-flash.error   { background: var(--fin-red-light);   border-color: var(--fin-red-mid); color: var(--fin-red); }

  /* Responsividade */
  @media (max-width: 1024px) {
    .grid-fin-main  { grid-template-columns: 1fr !important; }
    .grid-fin-chart { grid-template-columns: 1fr !important; }
    .grid-kpi       { grid-template-columns: 1fr 1fr !important; }
    .chart-wrapper-main { height: 220px; }
    .chart-wrapper-dist { height: 180px; }
  }

  /* Redesign Sênior: Otimização de Espaço nos KPIs */
  .grid-kpi { gap: 8px !important; }
  .kpi-card { padding: 10px 12px !important; border-radius: 10px !important; }
  .kpi-label { font-size: 10px !important; letter-spacing: 0.04em !important; }
  .kpi-value { font-size: 15px !important; font-weight: 500 !important; font-family: 'IBM Plex Mono', 'DM Mono', monospace !important; margin-top: 2px !important; }
  .kpi-icon { width: 28px !important; height: 28px !important; border-radius: 8px !important; }
  .kpi-icon svg { width: 14px !important; height: 14px !important; }
  .kpi-bar { height: 3px !important; margin-top: 8px !important; }
  .fin-badge { font-size: 9px !important; padding: 1px 5px !important; }

  /* Extensão do Redesign para Análise de Clientes */
  .ana-stat-card { padding: 10px 12px !important; border-radius: 10px !important; gap: 10px !important; display: flex !important; align-items: center !important; }
  .ana-stat-label { font-size: 10px !important; font-weight: 600 !important; text-transform: uppercase !important; letter-spacing: 0.04em !important; color: var(--fin-muted) !important; line-height: 1.2 !important; margin-bottom: 2px !important; }
  .ana-stat-value { font-size: 15px !important; font-weight: 500 !important; font-family: 'IBM Plex Mono', monospace !important; color: var(--fin-text) !important; line-height: 1 !important; }
  .ana-stat-icon { width: 28px !important; height: 28px !important; border-radius: 7px !important; flex-shrink: 0 !important; display: flex !important; align-items: center !important; justify-content: center !important; }
  .ana-stat-icon i { font-size: 14px !important; }
  .ana-table-value { font-family: 'IBM Plex Mono', 'DM Mono', monospace !important; font-size: 11px !important; letter-spacing: -0.02em !important; }
  .ana-status-dot { width: 6px !important; height: 6px !important; border-radius: 50% !important; display: inline-block !important; margin-right: 6px !important; vertical-align: middle !important; margin-top: -2px !important; }
  .ana-status-dot.green { background-color: var(--fin-green) !important; box-shadow: 0 0 4px var(--fin-green-light) !important; }
  .ana-status-dot.red { background-color: var(--fin-red) !important; box-shadow: 0 0 4px var(--fin-red-light) !important; }

  @media (max-width: 640px) {
    .grid-kpi       { grid-template-columns: 1fr !important; }
    .topbar-actions { display: none; }
    .fin-modal      { border-radius: 14px 14px 0 0; align-self: flex-end; }
    .grid-summary-12m { grid-template-columns: 1fr !important; gap: 1.5rem !important; }
    .chart-wrapper-main { height: 200px; }
    .chart-wrapper-dist { height: 200px; }
  }
</style>

<div id="finance-module-container" class="w-full">
<div class="fin-content-area">

<!-- ── FLASH MESSAGES ──────────────────────────────────────── -->
<?php if (session_status() == PHP_SESSION_NONE): session_start(); endif; ?>
<?php if (isset($_SESSION['flash_message'])): ?>
  <?php $msg = $_SESSION['flash_message']; unset($_SESSION['flash_message']); ?>
  <div class="fin-flash <?= $msg['type'] === 'success' ? 'success' : 'error' ?>">
    <?= htmlspecialchars($msg['message']) ?>
  </div>
<?php endif; ?>

<!-- ── ALERTA ORÇAMENTO ESTOURADO ────────────────────────── -->
<?php if (!empty($projetosEstourados)): ?>
<div class="fin-alert">
  <svg class="fin-alert-icon" width="16" height="16" viewBox="0 0 20 20" fill="currentColor">
    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
  </svg>
  <div>
    <strong class="text-red-800 font-semibold">Atenção: projetos com orçamento excedido</strong>
    <ul class="mt-1 space-y-0.5 list-none">
      <?php foreach ($projetosEstourados as $proj): ?>
        <li class="text-red-700">
          <strong><?= htmlspecialchars($proj['nome']) ?></strong>
          — Orçado: R$ <?= number_format($proj['orcamento'], 2, ',', '.') ?>
          | Gasto: R$ <?= number_format($proj['total_gasto'], 2, ',', '.') ?>
          <span class="font-bold">(+R$ <?= number_format($proj['total_gasto'] - $proj['orcamento'], 2, ',', '.') ?>)</span>
          <a href="<?= BASE_URL ?>/projetos/detalhe/<?= $proj['id'] ?>/orcamento"
             class="ml-1 underline text-red-900 hover:text-red-700 text-xs">ver projeto →</a>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
</div>
<?php endif; ?>

<!-- ── TOPBAR ──────────────────────────────────────────────── -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
  <div>
    <h2 class="text-xl font-bold text-gray-900 dark:text-white tracking-tight">Módulo financeiro</h2>
    <div class="flex items-center gap-3 mt-0.5">
        <p class="text-sm text-gray-500 font-medium">
            <?php 
                $meses_pt_ext = [1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril', 5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto', 9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'];
                $tsRef = strtotime($filtros['mes_referencia'] . '-01');
                echo $meses_pt_ext[(int)date('n', $tsRef)] . ' ' . date('Y', $tsRef);
            ?>
        </p>
        <div class="flex items-center bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-0.5 shadow-sm">
            <?php
                $navParams = array_filter($_GET);
                unset($navParams['mes_referencia']);
                $prevMonth = date('Y-m', strtotime($filtros['mes_referencia'] . ' -1 month'));
                $nextMonth = date('Y-m', strtotime($filtros['mes_referencia'] . ' +1 month'));
                $currentMonth = date('Y-m');
            ?>
            <a href="<?= BASE_URL . '/financeiro/index?' . http_build_query(array_merge($navParams, ['mes_referencia' => $prevMonth])) ?>" class="p-1 hover:bg-gray-100 dark:hover:bg-gray-700 rounded text-gray-400 hover:text-blue-600 transition-colors" title="Mês anterior">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M15 19l-7-7 7-7"/></svg>
            </a>
            <?php if ($filtros['mes_referencia'] !== $currentMonth): ?>
                <a href="<?= BASE_URL . '/financeiro/index?' . http_build_query(array_merge($navParams, ['mes_referencia' => $currentMonth])) ?>" class="px-2 text-[10px] font-bold uppercase text-blue-600 hover:text-blue-800 dark:text-blue-400 flex items-center" title="Voltar para hoje">Hoje</a>
            <?php endif; ?>
            <a href="<?= BASE_URL . '/financeiro/index?' . http_build_query(array_merge($navParams, ['mes_referencia' => $nextMonth])) ?>" class="p-1 hover:bg-gray-100 dark:hover:bg-gray-700 rounded text-gray-400 hover:text-blue-600 transition-colors" title="Próximo mês">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>
    </div>
  </div>
  <div class="topbar-actions flex items-center gap-2 flex-wrap">
    <a href="<?= BASE_URL ?>/financeiro/novo?tipo=R" class="fin-btn success">
      <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd"/></svg>
      Receita
    </a>
    <a href="<?= BASE_URL ?>/financeiro/novo?tipo=P" class="fin-btn danger">
      <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM7 9a1 1 0 000 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/></svg>
      Despesa
    </a>
    <button id="openTransferenciaModalBtn" class="fin-btn sky">
      <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
      Transferência
    </button>
    <button id="openRelatorioModalBtn" class="fin-btn primary">
      <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V7.414A2 2 0 0015.414 6L12 2.586A2 2 0 0010.586 2H6zm2 10a1 1 0 10-2 0v3a1 1 0 102 0v-3zm2-3a1 1 0 011 1v5a1 1 0 11-2 0v-5a1 1 0 011-1zm4-1a1 1 0 10-2 0v6a1 1 0 102 0V8z" clip-rule="evenodd"/></svg>
      Relatório
    </button>
  </div>
</div>

<!-- ── KPI CARDS ───────────────────────────────────────────── -->
<?php
$hoje = date('Y-m-d');
$venceHoje = !empty($proximoVencimento) && $proximoVencimento == $hoje;
$kpiPagarCor = $venceHoje ? 'amber' : 'red';
?>
<div class="grid-kpi" style="display:grid;grid-template-columns:repeat(4,1fr);gap:8px;margin-bottom:1.5rem">

  <!-- A Pagar -->
  <div class="kpi-card <?= $kpiPagarCor ?>">
    <div class="flex items-start justify-between">
      <div>
        <div class="kpi-label">A pagar (mês)</div>
        <div class="kpi-value" style="color:var(--fin-<?= $kpiPagarCor ?>)">
          R$ <?= number_format($contasPagarTotal ?? 0, 2, ',', '.') ?>
        </div>
      </div>
      <div class="kpi-icon" style="background:var(--fin-<?= $kpiPagarCor ?>-light)">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--fin-<?= $kpiPagarCor ?>)" stroke-width="2.5"><path d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/></svg>
      </div>
    </div>
    <?php if (!empty($resumoAtrasadasPagar) && $resumoAtrasadasPagar['count'] > 0): ?>
      <div class="mt-2">
        <a href="<?= BASE_URL ?>/financeiro/pagar?status=Atrasado" class="fin-badge red" style="text-decoration:none">
          ⚠ <?= $resumoAtrasadasPagar['count'] ?> em atraso
        </a>
      </div>
    <?php endif; ?>
    <div class="kpi-bar"><div class="kpi-bar-fill" style="width:65%;background:var(--fin-<?= $kpiPagarCor ?>)"></div></div>
  </div>

  <!-- A Receber -->
  <div class="kpi-card green">
    <div class="flex items-start justify-between">
      <div>
        <div class="kpi-label">A receber (mês)</div>
        <div class="kpi-value" style="color:var(--fin-green)">
          R$ <?= number_format($contasReceberTotal ?? 0, 2, ',', '.') ?>
        </div>
      </div>
      <div class="kpi-icon" style="background:var(--fin-green-light)">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--fin-green)" stroke-width="2.5"><path d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
      </div>
    </div>
    <?php if (!empty($resumoAtrasadas) && $resumoAtrasadas['count'] > 0): ?>
      <div class="mt-2">
        <a href="<?= BASE_URL ?>/financeiro/receber?status=Atrasado" class="fin-badge red" style="text-decoration:none">
          ⚠ <?= $resumoAtrasadas['count'] ?> em atraso
        </a>
      </div>
    <?php endif; ?>
    <div class="kpi-bar"><div class="kpi-bar-fill" style="width:80%;background:var(--fin-green)"></div></div>
  </div>

  <!-- Saldo Total -->
  <div class="kpi-card blue">
    <div class="flex items-start justify-between">
      <div>
        <div class="kpi-label">Saldo em caixa</div>
        <div class="kpi-value saldo-valor" style="color:var(--fin-blue)" 
             data-exact="<?= number_format($saldoAtual ?? 0, 2, '.', '') ?>" data-valor="R$ <?= number_format($saldoAtual ?? 0, 2, ',', '.') ?>">
          R$ <?= number_format($saldoAtual ?? 0, 2, ',', '.') ?>
        </div>
      </div>
      <button id="toggleSaldosBtn" class="kpi-icon" style="background:var(--fin-blue-light);border:none;cursor:pointer" title="Ocultar/Exibir saldos">
        <svg id="eyeOpenIcon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--fin-blue)" stroke-width="2.5"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
        <svg id="eyeClosedIcon" class="hidden" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--fin-blue)" stroke-width="2.5"><path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24M1 1l22 22"/></svg>
      </button>
    </div>
    <div class="mt-2">
      <span class="fin-badge blue"><?= count($saldosBancos ?? []) ?> contas</span>
    </div>
    <div class="kpi-bar"><div class="kpi-bar-fill" style="width:55%;background:var(--fin-blue)"></div></div>
  </div>

  <!-- Projeção -->
  <div class="kpi-card <?= ($projecaoFinanceira ?? 0) >= 0 ? 'green' : 'red' ?>">
    <div class="flex items-start justify-between">
      <div>
        <div class="kpi-label">Projeção (12 meses)</div>
        <div class="kpi-value saldo-valor" style="color:var(--fin-<?= ($projecaoFinanceira ?? 0) >= 0 ? 'green' : 'red' ?>)" 
             data-exact="<?= number_format($projecaoFinanceira ?? 0, 2, '.', '') ?>"
             data-valor="R$ <?= number_format($projecaoFinanceira ?? 0, 2, ',', '.') ?>">
          R$ <?= number_format($projecaoFinanceira ?? 0, 2, ',', '.') ?>
        </div>
      </div>
      <div class="kpi-icon" style="background:var(--fin-<?= ($projecaoFinanceira ?? 0) >= 0 ? 'green' : 'red' ?>-light)">
        <?php if (($projecaoFinanceira ?? 0) >= ($saldoAtual ?? 0)): ?>
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--fin-green)" stroke-width="2.5"><path d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
        <?php else: ?>
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--fin-red)" stroke-width="2.5"><path d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/></svg>
        <?php endif; ?>
      </div>
    </div>
    <div class="mt-2">
      <span class="fin-badge <?= ($projecaoFinanceira ?? 0) >= 0 ? 'green' : 'red' ?>">
        Previsto (líquido)
      </span>
    </div>
    <div class="kpi-bar"><div class="kpi-bar-fill" style="width:70%;background:var(--fin-<?= ($projecaoFinanceira ?? 0) >= 0 ? 'green' : 'red' ?>)"></div></div>
  </div>
</div>

<!-- ── NOVO CARD: ANÁLISE DE CLIENTES COM PAGAMENTOS ──────── -->
<?php $this->renderPartial('financeiro/analise_clientes_pagamentos', ['analiseClientesPagamentos' => $analiseClientesPagamentos]); ?>

<!-- ── BLOCO PRINCIPAL: Contas + Saldos ───────────────────── -->
<div class="grid-fin-main mb-6" style="display:grid;grid-template-columns:repeat(3, 1fr);gap:1rem">

    <!-- A Pagar -->
    <div class="fin-card">
      <div class="fin-card-header">
        <div>
          <div class="fin-card-title">Contas a pagar</div>
          <div class="text-xs text-gray-400 mt-0.5">Próximos vencimentos do mês</div>
        </div>
        <a href="<?= BASE_URL ?>/financeiro/pagar" class="text-xs font-medium text-blue-600 hover:text-blue-800 flex items-center gap-1">
          Ver todas →
        </a>
      </div>
      <div class="px-4 pb-3 pt-2">
        <?php if (!empty($resumoAtrasadasPagar) && $resumoAtrasadasPagar['count'] > 0): ?>
          <a href="<?= BASE_URL ?>/financeiro/pagar?status=Atrasado"
           class="flex items-center gap-2 px-3 py-2 bg-red-50 dark:bg-red-900/20 border border-red-100 dark:border-red-800 rounded-lg mb-3 text-xs text-red-700 dark:text-red-400 font-semibold hover:opacity-80 transition-colors" style="text-decoration:none">
            <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
            <?= $resumoAtrasadasPagar['count'] ?> conta(s) em atraso — clique para ver
          </a>
        <?php endif; ?>
        <ul id="lista-pagar" class="divide-y divide-gray-50 dark:divide-gray-700">
          <?php if (!empty($listaContasPagar)): ?>
            <?php foreach ($listaContasPagar as $conta): ?>
              <li class="conta-item">
                <span class="conta-desc" title="<?= htmlspecialchars($conta['descricao'] ?? '') ?>">
                  <?= htmlspecialchars($conta['descricao'] ?? '') ?>
                </span>
                <span class="conta-meta">
                  <span class="conta-date"><?= date('d/m', strtotime($conta['vencimento'])) ?></span>
                  <?php if (isset($conta['status']) && $conta['status'] === 'Pago Parcial'): ?>
                    <span class="text-xs text-blue-500 font-medium mr-1">Restam</span>
                    <span class="font-bold text-red-600 text-sm">R$ <?= number_format(($conta['valor'] + ($conta['juros'] ?? 0) - ($conta['desconto'] ?? 0)) - ($conta['valor_pago'] ?? 0), 2, ',', '.') ?></span>
                  <?php else: ?>
                    <span class="font-bold text-red-600 text-sm">R$ <?= number_format($conta['valor'], 2, ',', '.') ?></span>
                  <?php endif; ?>
                </span>
              </li>
            <?php endforeach; ?>
          <?php else: ?>
            <li class="py-4 text-center text-xs text-gray-400 italic">Nenhuma conta pendente.</li>
          <?php endif; ?>
        </ul>
        <!-- Paginação A Pagar -->
        <div id="paginacao-pagar" class="hidden flex justify-center items-center mt-2 pt-2 border-t border-gray-100 text-xs text-gray-500">
          <button class="btn-prev p-1 hover:text-gray-800 disabled:opacity-30">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
          </button>
          <span class="page-info mx-3 font-medium"></span>
          <button class="btn-next p-1 hover:text-gray-800 disabled:opacity-30">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
          </button>
        </div>
      </div>
    </div>

    <!-- A Receber -->
    <div class="fin-card">
      <div class="fin-card-header">
        <div>
          <div class="fin-card-title">Contas a receber</div>
          <div class="text-xs text-gray-400 mt-0.5">Próximos recebimentos do mês</div>
        </div>
        <a href="<?= BASE_URL ?>/financeiro/receber" class="text-xs font-medium text-blue-600 hover:text-blue-800">
          Ver todas →
        </a>
      </div>
      <div class="px-4 pb-3 pt-2">
        <?php if (!empty($resumoAtrasadas) && $resumoAtrasadas['count'] > 0): ?>
          <a href="<?= BASE_URL ?>/financeiro/receber?status=Atrasado"
           class="flex items-center gap-2 px-3 py-2 bg-red-50 dark:bg-red-900/20 border border-red-100 dark:border-red-800 rounded-lg mb-3 text-xs text-red-700 dark:text-red-400 font-semibold hover:opacity-80 transition-colors" style="text-decoration:none">
            <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
            <?= $resumoAtrasadas['count'] ?> conta(s) em atraso — clique para ver
          </a>
        <?php endif; ?>
        <ul id="lista-receber" class="divide-y divide-gray-50 dark:divide-gray-700">
          <?php if (!empty($listaContasReceber)): ?>
            <?php foreach ($listaContasReceber as $conta): ?>
              <li class="conta-item">
                <span class="conta-desc" title="<?= htmlspecialchars($conta['descricao'] ?? '') ?>">
                  <?= htmlspecialchars($conta['descricao'] ?? '') ?>
                </span>
                <span class="conta-meta">
                  <span class="conta-date"><?= date('d/m', strtotime($conta['vencimento'])) ?></span>
                  <?php if (isset($conta['status']) && $conta['status'] === 'Pago Parcial'): ?>
                    <span class="text-xs text-blue-500 font-medium mr-1">Restam</span>
                    <span class="font-bold text-green-600 text-sm">R$ <?= number_format(($conta['valor'] + ($conta['juros'] ?? 0) - ($conta['desconto'] ?? 0)) - ($conta['valor_pago'] ?? 0), 2, ',', '.') ?></span>
                  <?php else: ?>
                    <span class="font-bold text-green-600 text-sm">R$ <?= number_format($conta['valor'], 2, ',', '.') ?></span>
                  <?php endif; ?>
                </span>
              </li>
            <?php endforeach; ?>
          <?php else: ?>
            <li class="py-4 text-center text-xs text-gray-400 italic">Nenhum recebimento pendente.</li>
          <?php endif; ?>
        </ul>
        <!-- Paginação A Receber -->
        <div id="paginacao-receber" class="hidden flex justify-center items-center mt-2 pt-2 border-t border-gray-100 text-xs text-gray-500">
          <button class="btn-prev p-1 hover:text-gray-800 disabled:opacity-30">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
          </button>
          <span class="page-info mx-3 font-medium"></span>
          <button class="btn-next p-1 hover:text-gray-800 disabled:opacity-30">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
          </button>
        </div>
      </div>
  </div>

  <!-- Coluna Direita: Saldos + Visão Geral -->
  <div class="fin-card" style="display:flex;flex-direction:column">
    <div class="fin-card-header" style="border-radius: 12px 12px 0 0;">
      <div class="fin-card-title">Saldos em contas</div>
      <button id="toggleSaldosBtnAlt" class="text-xs text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 font-bold flex items-center gap-1 focus:outline-none">
        <svg id="eyeOpenIconAlt" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
        <svg id="eyeClosedIconAlt" class="hidden" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24M1 1l22 22"/></svg>
        <span id="saldoBtnLabel">Ocultar</span>
      </button>
    </div>

    <div class="p-4 flex-1 flex flex-col" style="min-height: 280px; background: var(--fin-surface);">
      <!-- Lista de bancos -->
      <div class="space-y-1 fin-scroll pr-1" style="max-height:240px;overflow-y:auto" id="saldo-list">
        <?php if (!empty($saldosBancos)): ?>
          <?php foreach ($saldosBancos as $banco): ?>
            <?php
              $logoUrl = BASE_URL . '/img/bank_flags/default.svg';
              if (!empty($banco['logo']) && file_exists(ROOT_PATH . '/public/uploads/bancos/' . $banco['logo'])) {
                  $logoUrl = BASE_URL . '/uploads/bancos/' . htmlspecialchars($banco['logo']);
              } else {
                  $logoUrl = get_bank_flag_url($banco['nome']);
              }
              $initials = strtoupper(implode('', array_map(fn($w) => $w[0], array_slice(explode(' ', $banco['nome']), 0, 2))));
            ?>
            <div class="banco-item">
              <div class="flex items-center gap-2">
                <div class="banco-avatar">
                  <img src="<?= $logoUrl ?>" alt="<?= htmlspecialchars($banco['nome'] ?? '') ?>" 
                       class="w-full h-full object-contain rounded-full"
                       onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                  <span style="display:none;font-size:10px;font-weight:700;color:var(--fin-text)"><?= $initials ?></span>
                </div>
                <div>
                  <div class="text-sm font-bold text-gray-800 dark:text-gray-100 leading-tight"><?= htmlspecialchars($banco['nome'] ?? '') ?></div>
                  <div class="text-xs text-gray-400"><?= htmlspecialchars($banco['tipo'] ?? '') ?></div>
                </div>
              </div>
              <div class="saldo-valor text-sm font-bold <?= $banco['saldo_atual'] >= 0 ? 'text-gray-800 dark:text-gray-100' : 'text-red-600 dark:text-red-400' ?>" 
                   data-exact="<?= number_format($banco['saldo_atual'], 2, '.', '') ?>"
                   data-valor="R$ <?= number_format($banco['saldo_atual'], 2, ',', '.') ?>">
                R$ <?= number_format($banco['saldo_atual'], 2, ',', '.') ?>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p class="text-sm text-gray-400 italic text-center py-6">Nenhuma conta cadastrada.</p>
        <?php endif; ?>
      </div>

      <!-- Saldo Total -->
      <div class="mt-auto pt-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 -mx-4 -mb-4 p-4 rounded-b-xl">
        <div class="flex justify-between items-center">
          <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Saldo consolidado</span>
          <span class="text-lg font-bold text-blue-700 dark:text-blue-400 saldo-valor" 
                data-exact="<?= number_format($saldoAtual ?? 0, 2, '.', '') ?>"
                data-valor="R$ <?= number_format($saldoAtual ?? 0, 2, ',', '.') ?>">
            R$ <?= number_format($saldoAtual ?? 0, 2, ',', '.') ?>
          </span>
        </div>
        <p class="text-xs text-gray-400 text-right mt-1">
          Atualizado: <?= !empty($ultimaAtualizacaoSaldo) ? date('d/m/Y H:i', strtotime($ultimaAtualizacaoSaldo)) : 'N/A' ?>
        </p>
      </div>
    </div>
  </div>
</div>

<!-- ── GRÁFICOS ────────────────────────────────────────────── -->
<div class="grid-fin-chart mb-6" style="display:grid;grid-template-columns:2fr 1fr;gap:1rem">

  <!-- Receitas vs Despesas -->
  <div class="fin-card">
    <div class="fin-card-header">
      <div>
        <div class="fin-card-title"><?= htmlspecialchars($chartTitle ?? 'Receitas vs. despesas') ?></div>
        <div class="text-xs text-gray-400 mt-0.5">Comparativo mensal</div>
      </div>
      <div class="flex items-center gap-2">
        <div class="flex items-center gap-3 text-xs text-gray-500 mr-3">
          <span class="flex items-center gap-1"><span style="display:inline-block;width:10px;height:10px;border-radius:2px;background:#16a34a"></span> Receitas</span>
          <span class="flex items-center gap-1"><span style="display:inline-block;width:10px;height:10px;border-radius:2px;background:#dc2626"></span> Despesas</span>
        </div>
        <select id="periodoFiltro" class="fin-select" style="width:auto;padding:5px 10px;font-size:12px">
          <option value="6"       <?= ($periodoSelecionado == '6')        ? 'selected' : '' ?>>Últimos 6 meses</option>
          <option value="12"      <?= ($periodoSelecionado == '12')       ? 'selected' : '' ?>>Últimos 12 meses</option>
          <option value="future_6"<?= ($periodoSelecionado == 'future_6') ? 'selected' : '' ?>>Próximos 6 meses</option>
          <option value="future_12"<?= ($periodoSelecionado == 'future_12')? 'selected' : ''?>>Próximos 12 meses</option>
        </select>
        <button id="aplicarFiltroBtn" class="fin-btn primary" style="padding:5px 12px;font-size:12px">Aplicar</button>
      </div>
    </div>
    <div class="p-4">
      <div class="chart-wrapper-main">
        <canvas id="receitasDespesasChart" role="img" aria-label="Gráfico de barras de receitas e despesas mensais">Dados mensais de receitas e despesas.</canvas>
      </div>
      <!-- ── Visão Consolidada 12 Meses — REDESIGN ─────── -->
      <?php
        /* ── Cálculos de apoio para o card ── */
        $pctPago12m     = ($totalDespesasAno > 0) ? ($totalDespesasPagasAno / $totalDespesasAno) * 100 : 0;
        $pctPago12m     = min(100, $pctPago12m);
        $custoBarColor  = $pctPago12m >= 80 ? '#dc2626' : ($pctPago12m >= 50 ? '#f59e0b' : '#10b981');
        $lucroPositivo  = ($lucratividadeAno ?? 0) >= 0;
        $projPositivo   = ($projecaoFinanceira ?? 0) >= 0;
        $projCrescendo  = ($projecaoFinanceira ?? 0) > ($saldoAtual ?? 0);
        /* margem de lucratividade em % sobre o total recebido */
        $margemPct      = ($previsaoRecebimento > 0) ? (($lucratividadeAno ?? 0) / $previsaoRecebimento) * 100 : 0;
        $totalReceber   = $previsaoRecebimento ?? 0;
        $totalCusto     = $totalDespesasAno ?? 0;
        /* barra de cobertura: quanto do custo total é coberto pelo a-receber */
        $cobertura      = ($totalCusto > 0) ? min(100, ($totalReceber / $totalCusto) * 100) : 100;
      ?>
      <div class="vis-12m-card mt-6">

        <!-- Cabeçalho do card -->
        <div class="vis-12m-header">
          <div class="vis-12m-header-left">
            <div class="vis-12m-icon">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/>
              </svg>
            </div>
            <div>
              <div class="vis-12m-title">Visão consolidada</div>
              <div class="vis-12m-subtitle">Acumulado dos últimos 12 meses</div>
            </div>
          </div>
          <a href="<?= BASE_URL ?>/financeiro/balanco" class="vis-12m-link" style="text-decoration:none">
            Balanço completo
            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
          </a>
        </div>

        <!-- Grade de métricas -->
        <div class="vis-12m-grid">

          <!-- 1. Saldo Atual -->
          <div class="vis-metric-cell vis-cell-blue">
            <div class="vis-metric-top">
              <div class="vis-metric-dot" style="background:#1d4ed8"></div>
              <span class="vis-metric-label">Saldo Atual</span>
              <div class="vis-metric-icon vis-icon-blue">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#1d4ed8" stroke-width="2.5"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v16"/></svg>
              </div>
            </div>
            <div class="vis-metric-value saldo-valor" style="color:#1d4ed8"
                 data-exact="<?= number_format($saldoAtual ?? 0, 2, '.', '') ?>"
                 data-valor="R$ <?= number_format($saldoAtual ?? 0, 2, ',', '.') ?>">
              R$ <?= number_format($saldoAtual ?? 0, 2, ',', '.') ?>
            </div>
            <div class="vis-metric-sub">Saldo bancário atual</div>
          </div>

          <!-- 2. A Receber -->
          <div class="vis-metric-cell vis-cell-green">
            <div class="vis-metric-top">
              <div class="vis-metric-dot" style="background:#16a34a"></div>
              <span class="vis-metric-label">A Receber (12m)</span>
              <div class="vis-metric-icon vis-icon-green">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2.5"><path d="M12 5v14M5 12l7-7 7 7"/></svg>
              </div>
            </div>
            <div class="vis-metric-value saldo-valor" style="color:#16a34a"
                 data-exact="<?= number_format($previsaoRecebimento ?? 0, 2, '.', '') ?>"
                 data-valor="+ R$ <?= number_format($previsaoRecebimento ?? 0, 2, ',', '.') ?>">
              + R$ <?= number_format($previsaoRecebimento ?? 0, 2, ',', '.') ?>
            </div>
            <!-- barra de cobertura: quanto do custo será coberto pelo recebível -->
            <div class="vis-metric-sub">Cobre <?= number_format($cobertura, 0) ?>% dos custos</div>
            <div class="vis-mini-bar-track">
              <div class="vis-mini-bar-fill" style="width:<?= $cobertura ?>%;background:#16a34a"></div>
            </div>
          </div>

          <!-- 3. Custo Total -->
          <div class="vis-metric-cell vis-cell-red">
            <div class="vis-metric-top">
              <div class="vis-metric-dot" style="background:#dc2626"></div>
              <span class="vis-metric-label">Custo Total (ano)</span>
              <div class="vis-metric-icon vis-icon-red">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2.5"><path d="M12 19V5M5 12l7 7 7-7"/></svg>
              </div>
            </div>
            <div class="vis-metric-value saldo-valor" style="color:#dc2626"
                 data-exact="<?= number_format($totalDespesasAno ?? 0, 2, '.', '') ?>"
                 data-valor="- R$ <?= number_format($totalDespesasAno ?? 0, 2, ',', '.') ?>">
              - R$ <?= number_format($totalDespesasAno ?? 0, 2, ',', '.') ?>
            </div>
            <div class="vis-metric-sub-row">
              <span style="color:var(--fin-muted)">Pago:</span>
              <span style="font-weight:600;color:<?= $custoBarColor ?>">
                R$ <?= number_format($totalDespesasPagasAno ?? 0, 2, ',', '.') ?>
                (<?= number_format($pctPago12m, 0) ?>%)
              </span>
            </div>
            <div class="vis-mini-bar-track" title="<?= number_format($pctPago12m, 1) ?>% do custo total já foi pago">
              <div class="vis-mini-bar-fill vis-mini-bar-animated" style="width:<?= $pctPago12m ?>%;background:<?= $custoBarColor ?>"></div>
            </div>
          </div>

          <!-- 4. Lucratividade -->
          <?php $lucroColor = $lucroPositivo ? '#059669' : '#dc2626'; ?>
          <div class="vis-metric-cell <?= $lucroPositivo ? 'vis-cell-emerald' : 'vis-cell-red' ?>">
            <div class="vis-metric-top">
              <div class="vis-metric-dot" style="background:<?= $lucroColor ?>"></div>
              <span class="vis-metric-label">Lucratividade</span>
              <div class="vis-metric-icon" style="background:<?= $lucroPositivo ? 'rgba(5,150,105,.12)' : 'rgba(220,38,38,.12)' ?>">
                <?php if ($lucroPositivo): ?>
                  <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2.5"><path d="M22 7l-8.5 8.5-5-5L1 17"/><path d="M16 7h6v6"/></svg>
                <?php else: ?>
                  <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2.5"><path d="M22 17l-8.5-8.5-5 5L1 7"/><path d="M16 17h6v-6"/></svg>
                <?php endif; ?>
              </div>
            </div>
            <div class="vis-metric-value saldo-valor" style="color:<?= $lucroColor ?>"
                 data-exact="<?= number_format($lucratividadeAno ?? 0, 2, '.', '') ?>"
                 data-valor="<?= $lucroPositivo ? '+' : '-' ?> R$ <?= number_format(abs($lucratividadeAno ?? 0), 2, ',', '.') ?>">
              <?= $lucroPositivo ? '+' : '-' ?> R$ <?= number_format(abs($lucratividadeAno ?? 0), 2, ',', '.') ?>
            </div>
            <div class="vis-metric-sub">
              Margem: <strong style="color:<?= $lucroColor ?>"><?= number_format(abs($margemPct), 1) ?>%</strong>
              <?= $lucroPositivo ? '✓' : '↓' ?>
            </div>
          </div>

          <!-- 5. Projeção Final -->
          <?php $projColor = $projPositivo ? '#059669' : '#dc2626'; ?>
          <div class="vis-metric-cell vis-cell-proj <?= $projPositivo ? 'vis-cell-proj-pos' : 'vis-cell-proj-neg' ?>">
            <div class="vis-metric-top">
              <div class="vis-metric-dot" style="background:<?= $projColor ?>"></div>
              <span class="vis-metric-label">Projeção Final</span>
              <div class="vis-metric-icon" style="background:<?= $projPositivo ? 'rgba(5,150,105,.12)' : 'rgba(220,38,38,.12)' ?>">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="<?= $projColor ?>" stroke-width="2.5">
                  <circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/>
                </svg>
              </div>
            </div>
            <div class="vis-metric-value saldo-valor vis-proj-value" style="color:<?= $projColor ?>"
                 data-exact="<?= number_format($projecaoFinanceira ?? 0, 2, '.', '') ?>"
                 data-valor="R$ <?= number_format($projecaoFinanceira ?? 0, 2, ',', '.') ?>">
              R$ <?= number_format($projecaoFinanceira ?? 0, 2, ',', '.') ?>
            </div>
            <div class="vis-metric-sub vis-proj-badge <?= $projCrescendo ? 'vis-proj-up' : 'vis-proj-down' ?>">
              <?php if ($projCrescendo): ?>
                <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M12 5v14M5 12l7-7 7 7"/></svg>
                Tendência de crescimento
              <?php else: ?>
                <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M12 19V5M5 12l7 7 7-7"/></svg>
                Tendência de queda
              <?php endif; ?>
            </div>
          </div>

        </div><!-- /vis-12m-grid -->

        <!-- Rodapé: fórmula visual -->
        <div class="vis-12m-formula">
          <span class="vis-formula-chip vis-fc-blue">
            <svg width="10" height="10" viewBox="0 0 24 24" fill="#1d4ed8" stroke="none"><rect x="2" y="7" width="20" height="14" rx="2"/></svg>
            Saldo
          </span>
          <span class="vis-formula-op">+</span>
          <span class="vis-formula-chip vis-fc-green">
            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="3"><path d="M12 5v14M5 12l7-7 7 7"/></svg>
            A Receber
          </span>
          <span class="vis-formula-op">−</span>
          <span class="vis-formula-chip vis-fc-red">
            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="3"><path d="M12 19V5M5 12l7 7 7-7"/></svg>
            A Pagar
          </span>
          <span class="vis-formula-op">=</span>
          <span class="vis-formula-chip <?= $projPositivo ? 'vis-fc-emerald' : 'vis-fc-red-dark' ?>">
            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="<?= $projColor ?>" stroke-width="3"><circle cx="12" cy="12" r="9"/></svg>
            Projeção: R$ <?= number_format($projecaoFinanceira ?? 0, 2, ',', '.') ?>
          </span>
        </div>

      </div><!-- /vis-12m-card -->

      <style>
        /* ══ Visão Consolidada 12m — Estilos ══════════════════════ */
        .vis-12m-card {
          background: var(--fin-surface2);
          border: 1px solid var(--fin-border);
          border-radius: 14px;
          overflow: hidden;
        }
        .vis-12m-header {
          display: flex;
          align-items: center;
          justify-content: space-between;
          padding: 10px 16px;
          background: var(--fin-surface);
          border-bottom: 1px solid var(--fin-border);
        }
        .vis-12m-header-left { display: flex; align-items: center; gap: 10px; }
        .vis-12m-icon {
          width: 30px; height: 30px; border-radius: 8px;
          background: rgba(29,78,216,.1);
          color: var(--fin-blue);
          display: flex; align-items: center; justify-content: center;
          flex-shrink: 0;
        }
        .vis-12m-title   { font-size: 13px; font-weight: 700; color: var(--fin-text); letter-spacing: -.01em; }
        .vis-12m-subtitle{ font-size: 10px; color: var(--fin-muted); margin-top: 1px; }
        .vis-12m-link {
          font-size: 11px; font-weight: 700;
          color: var(--fin-blue);
          display: flex; align-items: center; gap: 4px;
          padding: 4px 10px; border-radius: 6px;
          background: rgba(29,78,216,.08);
          transition: background .15s, color .15s;
        }
        .vis-12m-link:hover { background: rgba(29,78,216,.16); }

        /* Grid de 5 métricas */
        .vis-12m-grid {
          display: grid;
          grid-template-columns: repeat(5, 1fr);
          gap: 0;
          border-top: none;
        }
        @media (max-width: 900px) {
          .vis-12m-grid { grid-template-columns: repeat(3, 1fr); }
        }
        @media (max-width: 600px) {
          .vis-12m-grid { grid-template-columns: repeat(2, 1fr); }
        }

        .vis-metric-cell {
          padding: 14px 14px 12px;
          border-right: 1px solid var(--fin-border);
          border-bottom: 1px solid var(--fin-border);
          display: flex; flex-direction: column; gap: 4px;
          position: relative;
          transition: background .15s;
        }
        .vis-metric-cell:last-child { border-right: none; }
        .vis-metric-cell:hover { background: var(--fin-surface) !important; }

        /* Acento de cor no topo de cada célula */
        .vis-cell-blue   { border-top: 3px solid #1d4ed8; }
        .vis-cell-green  { border-top: 3px solid #16a34a; }
        .vis-cell-red    { border-top: 3px solid #dc2626; }
        .vis-cell-emerald{ border-top: 3px solid #059669; }
        .vis-cell-proj-pos { border-top: 3px solid #059669; background: rgba(5,150,105,.03); }
        .vis-cell-proj-neg { border-top: 3px solid #dc2626; background: rgba(220,38,38,.03); }

        /* Topo da métrica */
        .vis-metric-top {
          display: flex; align-items: center; gap: 5px;
          margin-bottom: 4px;
        }
        .vis-metric-dot {
          width: 6px; height: 6px; border-radius: 50%; flex-shrink: 0;
        }
        .vis-metric-label {
          font-size: 9px; font-weight: 700; text-transform: uppercase;
          letter-spacing: .07em; color: var(--fin-muted);
          flex: 1;
        }
        .vis-metric-icon {
          width: 22px; height: 22px; border-radius: 6px;
          display: flex; align-items: center; justify-content: center;
          flex-shrink: 0;
        }
        .vis-icon-blue  { background: rgba(29,78,216,.10); }
        .vis-icon-green { background: rgba(22,163,74,.10); }
        .vis-icon-red   { background: rgba(220,38,38,.10); }

        /* Valor principal */
        .vis-metric-value {
          font-size: 13px; font-weight: 800;
          letter-spacing: -.025em; line-height: 1.1;
          white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        @media (max-width: 1100px) { .vis-metric-value { font-size: 11px; } }

        /* Sub-textos */
        .vis-metric-sub {
          font-size: 9px; color: var(--fin-muted);
          margin-top: 2px;
        }
        .vis-metric-sub-row {
          display: flex; gap: 4px; align-items: center;
          font-size: 9px; margin-top: 2px;
        }

        /* Mini barra de progresso */
        .vis-mini-bar-track {
          height: 3px; border-radius: 2px;
          background: var(--fin-border);
          margin-top: 5px; overflow: hidden;
        }
        .vis-mini-bar-fill {
          height: 100%; border-radius: 2px;
          transition: width .8s cubic-bezier(.4,0,.2,1);
        }

        /* Badge de tendência na projeção */
        .vis-proj-badge {
          display: inline-flex; align-items: center; gap: 3px;
          font-size: 9px; font-weight: 700;
          padding: 2px 6px; border-radius: 999px; margin-top: 4px;
          width: fit-content;
        }
        .vis-proj-up   { background: rgba(5,150,105,.12); color: #059669; }
        .vis-proj-down { background: rgba(220,38,38,.12);  color: #dc2626; }

        /* Rodapé fórmula */
        .vis-12m-formula {
          display: flex; align-items: center; gap: 6px; flex-wrap: wrap;
          padding: 10px 16px;
          background: var(--fin-surface);
          border-top: 1px solid var(--fin-border);
          font-size: 10px;
        }
        .vis-formula-chip {
          display: inline-flex; align-items: center; gap: 4px;
          padding: 3px 8px; border-radius: 6px; font-weight: 600;
        }
        .vis-formula-op { color: var(--fin-muted); font-weight: 700; font-size: 12px; }
        .vis-fc-blue    { background: rgba(29,78,216,.10); color: #1d4ed8; }
        .vis-fc-green   { background: rgba(22,163,74,.10); color: #16a34a; }
        .vis-fc-red     { background: rgba(220,38,38,.10); color: #dc2626; }
        .vis-fc-emerald { background: rgba(5,150,105,.10); color: #059669; }
        .vis-fc-red-dark{ background: rgba(220,38,38,.12); color: #dc2626; }
      </style>
    </div>
  </div>

  <!-- Distribuição de Despesas -->
  <div class="fin-card">
    <div class="fin-card-header">
      <div>
        <div class="fin-card-title">Distribuição de despesas</div>
        <div class="text-xs text-gray-400 mt-0.5">Fluxo por categoria</div>
      </div>
    </div>
    <div class="p-4">
      <div class="fin-tabs mb-3">
        <button class="fin-tab active" id="btnToggleCategory">Por categoria</button>
        <button class="fin-tab" id="btnToggleCostCenter">Centro de custo</button>
      </div>
      <div class="chart-wrapper-dist">
        <canvas id="distributionChart" role="img" aria-label="Gráfico de rosca com distribuição de despesas">Distribuição de despesas por categoria.</canvas>
      </div>
      <div class="overflow-x-auto mt-3">
        <table class="w-full" style="font-size:11px;border-collapse:collapse">
          <thead>
            <tr>
              <th class="text-left text-gray-400 pb-1 font-semibold uppercase" style="letter-spacing:.05em">Categoria</th>
              <th class="text-right text-gray-400 pb-1 font-semibold uppercase" style="letter-spacing:.05em">Valor</th>
              <th class="text-right text-gray-400 pb-1 font-semibold uppercase" style="letter-spacing:.05em">%</th>
            </tr>
          </thead>
          <tbody id="distributionTableBody"></tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- ── TABELA DE MOVIMENTAÇÕES ────────────────────────────── -->
<div class="fin-card mb-6">
  <div class="fin-card-header" style="flex-wrap:wrap;gap:8px">
    <div class="fin-card-title">Movimentações recentes</div>
    <div class="flex items-center gap-2 flex-wrap">
      <a href="<?= BASE_URL ?>/financeiro/movimentacoes" class="fin-badge blue" style="text-decoration:none;cursor:pointer">
        Ver todas as movimentações →
      </a>
      <a href="<?= BASE_URL ?>/financeiro/relatorioCombustivel" class="fin-badge amber" style="text-decoration:none">
        Rel. combustível →
      </a>
    </div>
  </div>

  <?php if (!empty($fluxoCaixa)): ?>
    <div class="overflow-x-auto">
      <table class="fin-table">
        <thead>
          <tr>
            <th>Conta</th>
            <th>Descrição</th>
            <th class="center">Vencimento</th>
            <th class="center">Pago em</th>
            <th class="center">Tipo</th>
            <th class="right">Valor (R$)</th>
            <th class="center">Ações</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($fluxoCaixa as $transacao): ?>
            <?php
              $transferType = get_transfer_type($transacao);
              $valorSign  = '';
              $tipoLabel  = get_tipo_transacao_texto($transacao['tipo']);
              $tipoBadge  = '';

              if ($transferType === 'out') {
                  $valorSign = '-'; $tipoLabel = 'Transf. saída';  $tipoBadge = 'sky';
              } elseif ($transferType === 'in') {
                  $valorSign = '';  $tipoLabel = 'Transf. entrada'; $tipoBadge = 'sky';
              } elseif ($transacao['tipo'] === 'P') {
                  $valorSign = '-'; $tipoBadge = 'red';
              } else {
                  $valorSign = '';  $tipoBadge = 'green';
              }
              $valorCor = $valorSign === '-' ? 'color:var(--fin-red)' : 'color:var(--fin-green)';
            ?>
            <tr>
              <td style="color:var(--fin-muted)"><?= htmlspecialchars($transacao['banco_nome'] ?? 'N/A') ?></td>
              <td>
                <a href="<?= BASE_URL ?>/financeiro/detalhe/<?= $transacao['id'] ?>"
                   class="font-medium text-gray-900 hover:text-blue-600" style="text-decoration:none">
                  <?= htmlspecialchars($transacao['descricao'] ?? '') ?>
                </a>
              </td>
              <td class="center" style="color:var(--fin-muted)">
                <?= !empty($transacao['vencimento']) ? date('d/m/Y', strtotime($transacao['vencimento'])) : '—' ?>
              </td>
              <td class="center" style="color:var(--fin-muted)">
                <?= !empty($transacao['data_pagamento']) ? date('d/m/Y', strtotime($transacao['data_pagamento'])) : '—' ?>
              </td>
              <td class="center">
                <span class="fin-badge <?= $tipoBadge ?>"><?= htmlspecialchars($tipoLabel) ?></span>
              </td>
              <td class="right font-bold" style="<?= $valorCor ?>">
                <?php if ($transacao['status'] === 'Pago'): ?>
                  <?= $valorSign ?>R$ <?= number_format($transacao['valor_pago'] ?? $transacao['valor'], 2, ',', '.') ?>
                <?php elseif ($transacao['status'] === 'Pago Parcial'): ?>
                  <span class="text-gray-500"><?= $valorSign ?>R$ <?= number_format($transacao['valor'], 2, ',', '.') ?></span>
                  <br>
                  <span class="text-xs" style="color:var(--fin-blue)">Pago: R$ <?= number_format($transacao['valor_pago'] ?? 0, 2, ',', '.') ?> | Resta: R$ <?= number_format(($transacao['valor'] + ($transacao['juros'] ?? 0) - ($transacao['desconto'] ?? 0)) - ($transacao['valor_pago'] ?? 0), 2, ',', '.') ?></span>
                <?php else: ?>
                  <?= $valorSign ?>R$ <?= number_format($transacao['valor'], 2, ',', '.') ?>
                <?php endif; ?>
              </td>
              <td class="center">
                <div style="display:flex;align-items:center;justify-content:center;gap:10px">
                  <a href="<?= BASE_URL ?>/financeiro/editar/<?= $transacao['id'] ?>"
                     class="tbl-action" style="color:var(--fin-blue)" title="Editar">
                    <svg width="15" height="15" viewBox="0 0 20 20" fill="currentColor"><path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z"/><path fill-rule="evenodd" d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" clip-rule="evenodd"/></svg>
                  </a>
                  <?php if (!empty($transacao['transfer_partner_id'])): ?>
                    <a href="<?= BASE_URL ?>/financeiro/detalhe/<?= $transacao['transfer_partner_id'] ?>"
                       class="tbl-action" style="color:var(--fin-sky)" title="Transação relacionada">
                      <svg width="15" height="15" viewBox="0 0 20 20" fill="currentColor"><path d="M3.172 7l4.95-4.95a1 1 0 111.415 1.414L6.586 8.414H13a5 5 0 010 10H9a1 1 0 110-2h4a3 3 0 000-6H6.586l3.95 3.95a1 1 0 11-1.415 1.414L3.172 7z"/></svg>
                    </a>
                  <?php endif; ?>
                  <a href="<?= BASE_URL ?>/financeiro/bloquear/<?= $transacao['id'] ?>"
                     class="tbl-action" style="color:var(--fin-amber)" title="Bloquear / Cancelar"
                     onclick="return confirm('Bloquear esta transação? Ela não será mais contabilizada nos saldos.')">
                    <svg width="15" height="15" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M13.477 14.89A6 6 0 015.11 6.524l8.367 8.368zm1.414-1.414L6.524 5.11a6 6 0 018.367 8.367zM18 10a8 8 0 11-16 0 8 8 0 0116 0z" clip-rule="evenodd"/></svg>
                  </a>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php else: ?>
    <div class="py-12 text-center text-gray-400 text-sm italic">Nenhuma transação encontrada.</div>
  <?php endif; ?>
</div>
</div>
</div>

<!-- ── MODAL: TRANSFERÊNCIA ENTRE CONTAS ──────────────────── -->
<div id="transferenciaModal" class="fin-modal-overlay hidden" style="position:fixed;inset:0;z-index:50;background:rgba(0,0,0,.45);display:none;align-items:center;justify-content:center;padding:1rem">
  <div class="fin-modal">
    <div class="fin-modal-header">
      <div>
        <div class="fin-modal-title">Transferência entre contas</div>
        <div class="text-xs text-gray-400 mt-0.5">Mova saldo entre suas contas cadastradas</div>
      </div>
      <button id="fecharTransferenciaModal" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg hover:bg-gray-100">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"/></svg>
      </button>
    </div>
    <form id="transferenciaForm" action="<?= BASE_URL ?>/financeiro/realizarTransferencia" method="POST">
      <div class="fin-modal-body" style="display:flex;flex-direction:column;gap:16px">
        <div>
          <label class="fin-label" for="conta_origem">De (conta de origem)</label>
          <select id="conta_origem" name="conta_origem" required class="fin-select">
            <option value="">Selecione a conta de origem</option>
            <?php foreach ($saldosBancos as $banco): ?>
              <option value="<?= $banco['id'] ?>">
                <?= htmlspecialchars($banco['nome']) ?> — R$ <?= number_format($banco['saldo_atual'], 2, ',', '.') ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="fin-label" for="conta_destino">Para (conta de destino)</label>
          <select id="conta_destino" name="conta_destino" required class="fin-select">
            <option value="">Selecione a conta de destino</option>
            <?php foreach ($saldosBancos as $banco): ?>
              <option value="<?= $banco['id'] ?>"><?= htmlspecialchars($banco['nome']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
          <div>
            <label class="fin-label" for="valor_transferencia">Valor</label>
            <input type="text" name="valor" id="valor_transferencia" required class="fin-input" placeholder="0,00">
          </div>
          <div>
            <label class="fin-label" for="data_transferencia">Data</label>
            <input type="date" name="data_transferencia" id="data_transferencia" value="<?= date('Y-m-d') ?>" required class="fin-input">
          </div>
        </div>
      </div>
      <div class="fin-modal-footer">
        <button type="button" id="fecharTransferenciaModalBtn" class="fin-btn ghost">Cancelar</button>
        <button type="submit" class="fin-btn sky">Confirmar transferência</button>
      </div>
    </form>
  </div>
</div>

<!-- ── MODAL: RELATÓRIO FINANCEIRO ────────────────────────── -->
<div id="relatorioModal" class="fin-modal-overlay hidden" style="position:fixed;inset:0;z-index:50;background:rgba(0,0,0,.45);display:none;align-items:center;justify-content:center;padding:1rem">
  <div class="fin-modal wide">
    <div class="fin-modal-header">
      <div>
        <div class="fin-modal-title">Gerar relatório financeiro</div>
        <div class="text-xs text-gray-400 mt-0.5">Configure os filtros e exporte</div>
      </div>
      <button id="fecharRelatorioModalBtn" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg hover:bg-gray-100">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"/></svg>
      </button>
    </div>
    <form id="relatorioForm" action="<?= BASE_URL ?>/financeiro/relatorio" method="GET">
      <div class="fin-modal-body" style="display:flex;flex-direction:column;gap:16px">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
          <div>
            <label class="fin-label" for="modal_filtro_tipo_relatorio">Tipo de relatório</label>
            <select id="modal_filtro_tipo_relatorio" name="tipo_relatorio" class="fin-select">
              <option value="geral" <?= (($filtros['tipo_relatorio'] ?? '') == 'geral') ? 'selected' : '' ?>>Extrato geral</option>
              <option value="banco" <?= (($filtros['tipo_relatorio'] ?? '') == 'banco') ? 'selected' : '' ?>>Por conta bancária</option>
            </select>
          </div>
          <div id="modal_campo_banco" class="<?= (($filtros['tipo_relatorio'] ?? '') == 'banco') ? '' : 'hidden' ?>">
            <label class="fin-label" for="modal_filtro_banco_id">Conta bancária</label>
            <select id="modal_filtro_banco_id" name="banco_id" class="fin-select">
              <option value="">Todas as contas</option>
              <?php foreach ($bancos as $banco): ?>
                <option value="<?= $banco['id'] ?>" <?= (($filtros['banco_id'] ?? '') == $banco['id']) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($banco['nome']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div>
          <label class="fin-label" for="modal_filtro_periodo">Período</label>
          <select id="modal_filtro_periodo" name="periodo" class="fin-select">
            <option value="recente"   <?= (($filtros['periodo'] ?? 'recente') == 'recente')   ? 'selected' : '' ?>>Mais recentes</option>
            <option value="dia"       <?= (($filtros['periodo'] ?? '') == 'dia')       ? 'selected' : '' ?>>Dia específico</option>
            <option value="mes"       <?= (($filtros['periodo'] ?? '') == 'mes')       ? 'selected' : '' ?>>Mês específico</option>
            <option value="intervalo" <?= (($filtros['periodo'] ?? '') == 'intervalo') ? 'selected' : '' ?>>Intervalo de datas</option>
          </select>
        </div>
        <div id="modal_campo_data_unica" class="hidden">
          <label class="fin-label" for="modal_data_unica">Data</label>
          <input type="date" name="data_unica" id="modal_data_unica" value="<?= htmlspecialchars($filtros['data_unica'] ?? '') ?>" class="fin-input">
        </div>
        <div id="modal_campo_mes_ano" class="hidden">
          <label class="fin-label" for="modal_mes_ano">Mês / Ano</label>
          <input type="month" name="mes_ano" id="modal_mes_ano" value="<?= htmlspecialchars($filtros['mes_ano'] ?? '') ?>" class="fin-input">
        </div>
        <div id="modal_campo_intervalo" class="hidden" style="display:none;grid-template-columns:1fr 1fr;gap:12px">
          <div>
            <label class="fin-label" for="modal_data_inicio">De</label>
            <input type="date" name="data_inicio" id="modal_data_inicio" value="<?= htmlspecialchars($filtros['data_inicio'] ?? '') ?>" class="fin-input">
          </div>
          <div>
            <label class="fin-label" for="modal_data_fim">Até</label>
            <input type="date" name="data_fim" id="modal_data_fim" value="<?= htmlspecialchars($filtros['data_fim'] ?? '') ?>" class="fin-input">
          </div>
        </div>
      </div>
      <div class="fin-modal-footer">
        <button type="button" id="fecharRelatorioModal" class="fin-btn ghost">Cancelar</button>
        <button type="button" id="exportarPdfBtn" class="fin-btn ghost-red">
          <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V7.414A2 2 0 0015.414 6L12 2.586A2 2 0 0010.586 2H6zm5 6a1 1 0 10-2 0v3.586l-1.293-1.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 11.586V8z" clip-rule="evenodd"/></svg>
          Exportar PDF
        </button>
        <button type="button" id="visualizarRelatorioBtn" class="fin-btn primary">
          <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor"><path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/><path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/></svg>
          Visualizar relatório
        </button>
      </div>
    </form>
  </div>
</div>

<!-- ── JAVASCRIPT ──────────────────────────────────────────── -->
<script>
document.addEventListener('DOMContentLoaded', function () {

  /* ── HELPER: abrir/fechar modal ── */
  function openModal(el)  { el.style.display = 'flex'; }
  function closeModal(el) { el.style.display = 'none'; }

  /* ── MODAL TRANSFERÊNCIA ── */
  const transferenciaModal = document.getElementById('transferenciaModal');
  document.getElementById('openTransferenciaModalBtn')?.addEventListener('click', () => openModal(transferenciaModal));
  document.getElementById('fecharTransferenciaModal')?.addEventListener('click', () => closeModal(transferenciaModal));
  document.getElementById('fecharTransferenciaModalBtn')?.addEventListener('click', () => closeModal(transferenciaModal));
  transferenciaModal?.addEventListener('click', e => { if (e.target === transferenciaModal) closeModal(transferenciaModal); });

  // Desabilitar conta destino igual à origem
  const contaOrigem  = document.getElementById('conta_origem');
  const contaDestino = document.getElementById('conta_destino');
  contaOrigem?.addEventListener('change', () => {
    for (const opt of contaDestino.options) opt.disabled = false;
    if (contaOrigem.value) {
      const d = contaDestino.querySelector(`option[value="${contaOrigem.value}"]`);
      if (d) d.disabled = true;
    }
  });

  /* ── MODAL RELATÓRIO ── */
  const relatorioModal = document.getElementById('relatorioModal');
  document.getElementById('openRelatorioModalBtn')?.addEventListener('click', () => { openModal(relatorioModal); toggleModalDateFields(); });
  document.getElementById('fecharRelatorioModal')?.addEventListener('click', () => closeModal(relatorioModal));
  document.getElementById('fecharRelatorioModalBtn')?.addEventListener('click', () => closeModal(relatorioModal));
  relatorioModal?.addEventListener('click', e => { if (e.target === relatorioModal) closeModal(relatorioModal); });

  document.addEventListener('keydown', e => {
    if (e.key === 'Escape') { closeModal(transferenciaModal); closeModal(relatorioModal); }
  });

  // Toggle campo banco
  const tipoRelatorio = document.getElementById('modal_filtro_tipo_relatorio');
  const campoBanco    = document.getElementById('modal_campo_banco');
  tipoRelatorio?.addEventListener('change', () => {
    campoBanco.classList.toggle('hidden', tipoRelatorio.value !== 'banco');
  });

  // Toggle campos de data
  const filtroPeriodo    = document.getElementById('modal_filtro_periodo');
  const campoDataUnica   = document.getElementById('modal_campo_data_unica');
  const campoMesAno      = document.getElementById('modal_campo_mes_ano');
  const campoIntervalo   = document.getElementById('modal_campo_intervalo');

  function toggleModalDateFields() {
    const p = filtroPeriodo?.value;
    [campoDataUnica, campoMesAno].forEach(el => el?.classList.add('hidden'));
    if (campoIntervalo) campoIntervalo.style.display = 'none';
    if (p === 'dia')       campoDataUnica?.classList.remove('hidden');
    else if (p === 'mes')  campoMesAno?.classList.remove('hidden');
    else if (p === 'intervalo' && campoIntervalo) campoIntervalo.style.display = 'grid';
  }
  filtroPeriodo?.addEventListener('change', toggleModalDateFields);

  // Botões do relatório
  const relatorioForm = document.getElementById('relatorioForm');
  document.getElementById('visualizarRelatorioBtn')?.addEventListener('click', () => {
    relatorioForm.action = '<?= BASE_URL ?>/financeiro/relatorio';
    relatorioForm.target = '_blank';
    relatorioForm.submit();
  });
  document.getElementById('exportarPdfBtn')?.addEventListener('click', () => {
    relatorioForm.action = '<?= BASE_URL ?>/financeiro/exportarRelatorioPdf';
    relatorioForm.target = '_self';
    relatorioForm.submit();
  });

  /* ── TOGGLE SALDOS ── */
  let saldosVisiveis = true;
  try {
    saldosVisiveis = localStorage.getItem('saldosVisible') !== 'false';
  } catch (e) {
    console.warn("Acesso ao localStorage bloqueado pelo navegador.");
  }
  const saldoEls = document.querySelectorAll('.saldo-valor');

  function updateSaldos() {
    const eyeOpen   = [document.getElementById('eyeOpenIcon'),   document.getElementById('eyeOpenIconAlt')];
    const eyeClosed = [document.getElementById('eyeClosedIcon'), document.getElementById('eyeClosedIconAlt')];
    const label     = document.getElementById('saldoBtnLabel');

    eyeOpen.forEach(el  => el?.classList.toggle('hidden', !saldosVisiveis));
    eyeClosed.forEach(el => el?.classList.toggle('hidden', saldosVisiveis));
    if (label) label.textContent = saldosVisiveis ? 'ocultar' : 'exibir';

    saldoEls.forEach(el => {
      el.textContent = saldosVisiveis ? el.getAttribute('data-valor') : 'R$ ••••••';
      if (el.hasAttribute('data-exact')) {
        el.title = saldosVisiveis ? el.getAttribute('data-exact') : '';
      }
    });
  }
  updateSaldos();

  function toggleSaldos() {
    saldosVisiveis = !saldosVisiveis;
    try {
      localStorage.setItem('saldosVisible', saldosVisiveis);
    } catch (e) {
      // Apenas ignora se não puder salvar
    }
    updateSaldos();
  }
  document.getElementById('toggleSaldosBtn')?.addEventListener('click', toggleSaldos);
  document.getElementById('toggleSaldosBtnAlt')?.addEventListener('click', toggleSaldos);

  /* ── GRÁFICO: RECEITAS VS DESPESAS ── */
  // Usamos uma verificação mais robusta para garantir que sempre exista um array válido no JS
  const monthlySummary = <?= !empty($monthlySummaryJson) ? $monthlySummaryJson : '[]' ?>;

  if (typeof Chart !== 'undefined') {
    const barCtx = document.getElementById('receitasDespesasChart')?.getContext('2d');
    if (barCtx) {
      if (monthlySummary && monthlySummary.length > 0) {
        // Adicionada verificação de existência do campo 'mes' para evitar erro .split() em produção
        const labels      = monthlySummary.map(i => { if(!i.mes) return 'N/A'; const [y, m] = i.mes.split('-'); return new Date(y, m-1).toLocaleString('pt-BR', {month:'short', year:'2-digit'}); });
        const receitasData = monthlySummary.map(i => i.receitas);
        const despesasData = monthlySummary.map(i => i.despesas);
        
        // Cálculo do Saldo Acumulado Progressivo
        const saldoInicial = <?= (float)($saldoAtual ?? 0) ?>;
        let runningBalance = saldoInicial;
        const acumuladoData = monthlySummary.map(i => {
          runningBalance += (parseFloat(i.receitas) - parseFloat(i.despesas));
          return runningBalance;
        });

        new Chart(barCtx, {
          data: {
            labels,
            datasets: [
              { type: 'bar', label:'Receitas', data: receitasData, backgroundColor:'rgba(22,163,74,.6)', borderColor:'#16a34a', borderWidth:1, borderRadius:4, borderSkipped:false, yAxisID: 'y' },
              { type: 'bar', label:'Despesas', data: despesasData, backgroundColor:'rgba(220,38,38,.55)', borderColor:'#dc2626', borderWidth:1, borderRadius:4, borderSkipped:false, yAxisID: 'y' },
              { 
                type: 'line', 
                label: 'Saldo Acumulado', 
                data: acumuladoData, 
                borderColor: '#1d4ed8',
                borderWidth: 3, 
                fill: true,
                tension: 0.3, 
                pointRadius: 3,
                pointBackgroundColor: acumuladoData.map(v => v < 0 ? '#dc2626' : '#1d4ed8'),
                pointBorderColor: acumuladoData.map(v => v < 0 ? '#dc2626' : '#1d4ed8'),
                yAxisID: 'ySaldo',
                segment: {
                  borderColor: ctx => ctx.p1.parsed.y < 0 ? '#dc2626' : undefined,
                  backgroundColor: ctx => {
                    return ctx.p1.parsed.y < 0 ? 'rgba(220, 38, 38, 0.1)' : 'rgba(29, 78, 216, 0.1)';
                  }
                },
                order: 0 // Mantém a linha à frente das barras
              }
            ]
          },
          options: {
            responsive:true, maintainAspectRatio:false,
            plugins: { 
              legend:{ display: false }, 
              tooltip:{ callbacks:{ label: c => c.dataset.label + ': R$ ' + c.parsed.y.toLocaleString('pt-BR',{minimumFractionDigits:2}) } } 
            },
            scales: {
              y: { beginAtZero:true, grid:{color:'rgba(0,0,0,.05)'}, ticks:{ callback: v => 'R$ ' + Intl.NumberFormat('pt-BR',{notation:'compact'}).format(v), color:'#6b7280', font:{size:11} } },
              ySaldo: { position: 'right', beginAtZero: false, grid:{ display: false }, ticks: { callback: v => 'R$ ' + Intl.NumberFormat('pt-BR',{notation:'compact'}).format(v), color:'#1d4ed8', font:{size:10} } },
              x: { grid:{display:false}, ticks:{color:'#6b7280', font:{size:11}} }
            }
          }
        });
      } else {
        barCtx.font = '13px sans-serif';
        barCtx.fillStyle = '#9ca3af';
        barCtx.textAlign = 'center';
        barCtx.fillText('Sem dados para exibir.', barCtx.canvas.width/2, barCtx.canvas.height/2);
      }
    }

    /* ── GRÁFICO: DISTRIBUIÇÃO DE DESPESAS ── */
    const categoryData   = <?= !empty($expenseSummaryJson) ? $expenseSummaryJson : '[]' ?>;
    const costCenterData = <?= !empty($costCenterSummaryJson) ? $costCenterSummaryJson : '[]' ?>;
    let currentDistType  = 'category';
    let distChart        = null;

    const PALETTE = ['#1d4ed8','#16a34a','#dc2626','#b45309','#7c3aed','#0369a1','#be185d','#047857','#c2410c','#1d4ed8'];

    function updateDistributionUI(type) {
      const raw    = type === 'category' ? categoryData : costCenterData;
      console.log('DEBUG: Distribuição (' + type + ') Iniciando renderização:', raw);

      const dataToProcess = (raw && Array.isArray(raw) && raw.length > 0) ? raw : [];
      const labels = dataToProcess.map(i => (i.label && i.label !== 'null') ? i.label : 'Sem Nome');
      const values = dataToProcess.map(i => parseFloat(i.total) || 0);
      const total  = values.reduce((a,b) => a+b, 0);
      const colors = labels.map((_, i) => PALETTE[i % PALETTE.length]);

      const ctx = document.getElementById('distributionChart').getContext('2d');
      if (distChart) distChart.destroy();

      const surfaceColor = getComputedStyle(document.body).getPropertyValue('--fin-surface').trim() || '#fff';
      distChart = new Chart(ctx, {
        type: 'doughnut',
        data: { labels, datasets:[{ data:values, backgroundColor:colors, borderWidth:2, borderColor:surfaceColor }] },
        options: {
          responsive:true, maintainAspectRatio:false, cutout:'68%',
          plugins: { legend:{display:false}, tooltip:{ callbacks:{ label: c => c.label+': R$ '+c.parsed.toLocaleString('pt-BR',{minimumFractionDigits:2})+' ('+(total > 0 ? Math.round(c.parsed/total*100) : 0)+'%)' } } }
        }
      });

      const tbody = document.getElementById('distributionTableBody');
      tbody.innerHTML = '';
      if (dataToProcess.length === 0 || total === 0) {
        tbody.innerHTML = '<tr><td colspan="3" style="text-align:center;color:#9ca3af;padding:24px;font-style:italic;font-size:11px">Nenhuma despesa registrada para este mês.</td></tr>';
        return;
      }
      dataToProcess.forEach((item, i) => {
        const label = (item.label && item.label !== 'null') ? item.label : 'Sem Nome';
        const val   = parseFloat(item.total);
        const pct   = total > 0 ? ((val/total)*100).toFixed(1) : 0;
        tbody.innerHTML += `
          <tr>
            <td style="padding:4px 0;display:flex;align-items:flex-start;gap:5px;font-size:11px;color:var(--fin-text)">
              <span style="display:inline-block;width:8px;height:8px;border-radius:2px;background:${colors[i]};flex-shrink:0;margin-top:3px"></span>
              <span title="${label}" style="word-break: break-word;">${label}</span>
            </td>
            <td style="text-align:right;font-size:11px;color:var(--fin-text);font-weight:700;padding:4px 0 4px 4px;white-space:nowrap">R$ ${val.toLocaleString('pt-BR',{minimumFractionDigits:2})}</td>
            <td style="text-align:right;font-size:11px;color:var(--fin-muted);padding:4px 0 4px 4px">${pct}%</td>
          </tr>`;
      });
    }

    // Toggle categoria / centro de custo
    document.getElementById('btnToggleCategory')?.addEventListener('click', function() {
      if (currentDistType === 'category') return;
      currentDistType = 'category';
      this.classList.add('active');
      document.getElementById('btnToggleCostCenter').classList.remove('active');
      updateDistributionUI('category');
    });
    document.getElementById('btnToggleCostCenter')?.addEventListener('click', function() {
      if (currentDistType === 'costcenter') return;
      currentDistType = 'costcenter';
      this.classList.add('active');
      document.getElementById('btnToggleCategory').classList.remove('active');
      updateDistributionUI('costcenter');
    });

    updateDistributionUI('category');
  }

  /* ── FILTRO DO GRÁFICO ── */
  document.getElementById('aplicarFiltroBtn')?.addEventListener('click', () => {
    const p = document.getElementById('periodoFiltro')?.value;
    window.location.href = `<?= BASE_URL ?>/financeiro/index?periodo=${p}`;
  });

  /* ── PAGINAÇÃO DOS CARDS ── */
  function setupCardPagination(listId, controlsId, pageSize) {
    const list     = document.getElementById(listId);
    const controls = document.getElementById(controlsId);
    if (!list || !controls) return;

    const items      = list.querySelectorAll('li');
    const totalItems = items.length;
    const totalPages = Math.ceil(totalItems / pageSize);
    let currentPage  = 1;

    const btnPrev  = controls.querySelector('.btn-prev');
    const btnNext  = controls.querySelector('.btn-next');
    const pageInfo = controls.querySelector('.page-info');

    if (totalItems <= pageSize) { items.forEach(el => el.style.display = ''); return; }
    controls.classList.remove('hidden');

    function showPage(page) {
      const start = (page-1) * pageSize;
      const end   = start + pageSize;
      items.forEach((el, i) => { el.style.display = (i >= start && i < end) ? '' : 'none'; });
      if (pageInfo) pageInfo.textContent = `${page}/${totalPages}`;
      if (btnPrev)  btnPrev.disabled  = page === 1;
      if (btnNext)  btnNext.disabled  = page === totalPages;
    }
    btnPrev?.addEventListener('click', e => { e.preventDefault(); if (currentPage > 1) { currentPage--; showPage(currentPage); } });
    btnNext?.addEventListener('click', e => { e.preventDefault(); if (currentPage < totalPages) { currentPage++; showPage(currentPage); } });
    showPage(1);
  }

  setupCardPagination('lista-pagar',   'paginacao-pagar',   5);
  setupCardPagination('lista-receber', 'paginacao-receber', 5);

}); // fim DOMContentLoaded
</script>