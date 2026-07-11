<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600;9..144,700&family=Inter:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;500;600&display=swap" rel="stylesheet">

<style>
  /* ============================================================
     TOKENS — Dossiê Corporativo (Projetos)
     ============================================================ */
  #projects-page-container {
    --pj-bg: var(--db-bg, #0c1322);
    --pj-surface: var(--db-surface, #121a2c);
    --pj-surface-2: var(--db-surface2, #182338);
    --pj-border: var(--db-border, #26314a);
    --pj-text: var(--db-text, #f1efe6);
    --pj-text2: var(--db-text2, #99a3bc);
    --pj-text3: #6d7893;

    --pj-gold: #c9a227;
    --pj-gold-bright: #e3c15c;
    --pj-gold-soft: rgba(201, 162, 39, 0.14);

    --pj-blue: #5c7ea8;
    --pj-red: #b8544a;
    --pj-amber: #c9a227;
    --pj-green: #4a9574;

    --font-display: 'Fraunces', serif;
    --font-body: 'Inter', sans-serif;
    --font-mono: 'IBM Plex Mono', monospace;

    background: var(--pj-bg);
    color: var(--pj-text);
    font-family: var(--font-body);
    padding: 20px;
    border-radius: 14px;
    position: relative;
  }

  #projects-page-container * { box-sizing: border-box; }

  #projects-page-container .mono { font-family: var(--font-mono); font-variant-numeric: tabular-nums; }

  /* ---------------- Header ---------------- */
  .pj-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    flex-wrap: wrap;
    gap: 16px;
    padding-bottom: 12px;
    margin-bottom: 16px;
    border-bottom: 1px solid var(--pj-border);
  }
  .pj-eyebrow {
    font-family: var(--font-body);
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 2.2px;
    text-transform: uppercase;
    color: var(--pj-gold);
    margin: 0 0 4px 0;
    display: flex;
    align-items: center;
    gap: 8px;
  }
  .pj-eyebrow::before {
    content: '';
    display: inline-block;
    width: 18px;
    height: 1px;
    background: var(--pj-gold);
  }
  .pj-title {
    font-family: var(--font-display);
    font-size: 24px;
    font-weight: 600;
    letter-spacing: -0.3px;
    color: var(--pj-text);
    margin: 0;
  }
  .pj-subtitle {
    font-family: var(--font-body);
    font-size: 12px;
    color: var(--pj-text2);
    margin: 4px 0 0 0;
    max-width: 560px;
    line-height: 1.4;
  }
  .pj-header-meta {
    font-family: var(--font-mono);
    font-size: 11px;
    color: var(--pj-text3);
    text-align: right;
    letter-spacing: 0.4px;
    text-transform: uppercase;
  }

  /* ---------------- Stat Cards ---------------- */
  .pj-stat-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 10px;
    margin-bottom: 16px;
  }
  @media (max-width: 1100px) { .pj-stat-grid { grid-template-columns: repeat(2, 1fr); } }
  @media (max-width: 600px) { .pj-stat-grid { grid-template-columns: 1fr; } }

  .pj-stat-card {
    background: var(--pj-surface);
    border: 1px solid var(--pj-border);
    border-radius: 8px;
    padding: 12px 14px;
    position: relative;
    transition: border-color 0.25s ease, transform 0.25s ease;
  }
  .pj-stat-card:hover { transform: translateY(-2px); border-color: var(--pj-accent, var(--pj-gold)); }
  .pj-stat-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0;
    width: 100%; height: 2px;
    background: var(--pj-accent);
  }
  .pj-stat-card-blue   { --pj-accent: var(--pj-blue); }
  .pj-stat-card-red    { --pj-accent: var(--pj-red); }
  .pj-stat-card-orange { --pj-accent: var(--pj-amber); }
  .pj-stat-card-green  { --pj-accent: var(--pj-green); }

  .pj-stat-icon {
    position: absolute;
    right: 10px;
    top: 10px;
    font-size: 20px;
    opacity: 0.10;
    color: var(--pj-accent);
  }
  .pj-stat-label {
    font-family: var(--font-body);
    font-size: 9.5px;
    font-weight: 700;
    letter-spacing: 1.2px;
    text-transform: uppercase;
    color: var(--pj-text2);
    margin: 0 0 6px 0;
    display: flex;
    align-items: center;
    gap: 6px;
  }
  .pj-stat-label i { color: var(--pj-accent); font-size: 12px; }
  .pj-stat-value {
    font-family: var(--font-mono);
    font-size: 24px;
    font-weight: 700;
    color: var(--pj-text);
    line-height: 1;
    margin: 0;
  }
  .pj-stat-value.mono {
    font-size: 20px;
    font-weight: 600;
    word-break: keep-all;
    white-space: nowrap;
  }
  .pj-stat-foot {
    margin-top: 8px;
    padding-top: 6px;
    border-top: 1px solid var(--pj-border);
    font-size: 10px;
    color: var(--pj-text3);
    display: flex;
    align-items: center;
    gap: 4px;
  }
  .pj-stat-foot strong { color: var(--pj-accent); font-weight: 600; }

  /* ---------------- Cards genéricos ---------------- */
  .pj-card {
    background: var(--pj-surface);
    border: 1px solid var(--pj-border);
    border-radius: 10px;
  }
  .pj-card-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
    padding: 18px 22px;
    border-bottom: 1px solid var(--pj-border);
  }
  .pj-card-title {
    font-family: var(--font-display);
    font-size: 17px;
    font-weight: 600;
    color: var(--pj-text);
    margin: 0;
  }
  .pj-card-body { padding: 22px; }

  /* ---------------- Botões ---------------- */
  .pj-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-family: var(--font-body);
    font-size: 12.5px;
    font-weight: 600;
    letter-spacing: 0.2px;
    padding: 8px 16px;
    border-radius: 6px;
    border: 1px solid transparent;
    cursor: pointer;
    transition: all 0.2s ease;
    text-decoration: none;
    white-space: nowrap;
  }
  .pj-btn-gold {
    background: var(--pj-gold);
    color: #14100a;
  }
  .pj-btn-gold:hover { background: var(--pj-gold-bright); }
  .pj-btn-outline {
    background: transparent;
    border-color: var(--pj-border);
    color: var(--pj-text2);
  }
  .pj-btn-outline:hover { border-color: var(--pj-gold); color: var(--pj-gold-bright); }

  /* ---------------- Tabela (Ledger) ---------------- */
  .pj-table-wrap { overflow-x: auto; }
  .pj-table { width: 100%; border-collapse: collapse; }
  .pj-table thead th {
    padding: 11px 14px;
    font-family: var(--font-body);
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 1px;
    text-transform: uppercase;
    color: var(--pj-text2);
    background: var(--pj-surface-2);
    border-bottom: 1px solid var(--pj-border);
    text-align: left;
    white-space: nowrap;
  }
  .pj-table thead th a { color: inherit; text-decoration: none; }
  .pj-table thead th a:hover { color: var(--pj-gold-bright); }
  .pj-table tbody td {
    padding: 13px 14px;
    font-size: 13px;
    color: var(--pj-text2);
    border-bottom: 1px solid var(--pj-border);
  }
  .pj-table tbody tr:hover { background: var(--pj-surface-2); }
  .pj-table tbody tr:last-child td { border-bottom: none; }
  .pj-cell-strong { color: var(--pj-text); font-weight: 600; }
  .pj-link {
    color: var(--pj-gold-bright);
    text-decoration: none;
    font-weight: 600;
  }
  .pj-link:hover { text-decoration: underline; }

  .pj-icon-action {
    color: var(--pj-text3);
    margin-right: 10px;
    transition: color 0.2s ease;
  }
  .pj-icon-action:hover { color: var(--pj-gold-bright); }
  .pj-icon-action.danger:hover { color: var(--pj-red); }
  .pj-icon-action.success:hover { color: var(--pj-green); }

  /* Badges (tags retangulares, estilo dossiê) */
  .pj-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 3px 9px 3px 7px;
    border-radius: 3px;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    border: 1px solid;
  }
  .pj-badge::before {
    content: '';
    width: 6px;
    height: 6px;
    border-radius: 1px;
  }
  .pj-badge-red    { color: var(--pj-red);   border-color: rgba(184,84,74,0.4);   background: rgba(184,84,74,0.1); }
  .pj-badge-red::before { background: var(--pj-red); }
  .pj-badge-orange { color: var(--pj-amber); border-color: rgba(201,162,39,0.4);  background: rgba(201,162,39,0.1); }
  .pj-badge-orange::before { background: var(--pj-amber); }
  .pj-badge-blue   { color: var(--pj-blue);  border-color: rgba(92,126,168,0.4);  background: rgba(92,126,168,0.1); }
  .pj-badge-blue::before { background: var(--pj-blue); }
  .pj-badge-gray   { color: var(--pj-text2); border-color: var(--pj-border);      background: var(--pj-surface-2); }
  .pj-badge-gray::before { background: var(--pj-text2); }
  .pj-badge-green  { color: var(--pj-green); border-color: rgba(74,149,116,0.4);  background: rgba(74,149,116,0.1); }
  .pj-badge-green::before { background: var(--pj-green); }

  /* ---------------- Filtros ---------------- */
  .pj-field-label {
    display: block;
    font-size: 10.5px;
    font-weight: 700;
    letter-spacing: 1px;
    text-transform: uppercase;
    color: var(--pj-text2);
    margin-bottom: 7px;
  }
  .pj-input, .pj-select {
    width: 100%;
    background: var(--pj-surface-2);
    border: 1px solid var(--pj-border);
    color: var(--pj-text);
    border-radius: 6px;
    padding: 9px 11px;
    font-family: var(--font-body);
    font-size: 13px;
    margin-bottom: 18px;
    transition: border-color 0.2s ease;
  }
  .pj-input:focus, .pj-select:focus {
    outline: none;
    border-color: var(--pj-gold);
  }
  .pj-nav-link {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    width: 100%;
    padding: 10px 14px;
    border: 1px solid var(--pj-border);
    border-radius: 6px;
    color: var(--pj-text2);
    font-size: 12.5px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.2s ease;
  }
  .pj-nav-link:hover { border-color: var(--pj-gold); color: var(--pj-gold-bright); }
  .pj-note {
    font-size: 12px;
    color: var(--pj-text3);
    line-height: 1.6;
    margin: 0;
  }

  /* ---- Filtros: seções internas organizadas ---- */
  .pj-card-body-flush { padding: 0; }
  .pj-filter-section { padding: 20px 22px; }
  .pj-clear-link {
    background: none;
    border: none;
    cursor: pointer;
    font-family: var(--font-body);
    font-size: 10.5px;
    font-weight: 700;
    letter-spacing: 0.8px;
    text-transform: uppercase;
    color: var(--pj-text3);
    padding: 0;
    transition: color 0.2s ease;
  }
  .pj-clear-link:hover { color: var(--pj-gold-bright); }
  .pj-section-label {
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 1.4px;
    text-transform: uppercase;
    color: var(--pj-text3);
    padding: 14px 22px 8px;
    border-top: 1px solid var(--pj-border);
    margin-top: 2px;
  }
  .pj-input-icon { position: relative; margin-bottom: 0; }
  .pj-input-icon > i {
    position: absolute;
    left: 11px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--pj-text3);
    font-size: 14px;
    pointer-events: none;
  }
  .pj-input-with-icon { padding-left: 33px; }
  .pj-nav-list {
    display: flex;
    flex-direction: column;
    gap: 3px;
    padding: 6px 12px 14px;
  }
  .pj-nav-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 9px 10px;
    border-radius: 6px;
    color: var(--pj-text2);
    font-size: 12.5px;
    font-weight: 600;
    text-decoration: none;
    transition: background 0.2s ease, color 0.2s ease;
  }
  .pj-nav-item:hover { background: var(--pj-surface-2); color: var(--pj-gold-bright); }
  .pj-nav-item span { display: flex; align-items: center; gap: 9px; }
  .pj-nav-item span i { color: var(--pj-text3); font-size: 16px; transition: color 0.2s ease; }
  .pj-nav-item:hover span i { color: var(--pj-gold-bright); }
  .pj-nav-item .bx-chevron-right { font-size: 14px; opacity: 0.45; transition: transform 0.2s ease, opacity 0.2s ease; }
  .pj-nav-item:hover .bx-chevron-right { transform: translateX(2px); opacity: 0.9; }
  .pj-filter-tip {
    display: flex;
    gap: 10px;
    align-items: flex-start;
    background: var(--pj-surface-2);
    border-left: 2px solid var(--pj-gold);
    border-radius: 6px;
    padding: 12px 14px;
    margin: 6px 22px 20px;
  }
  .pj-filter-tip i { color: var(--pj-gold); font-size: 15px; margin-top: 1px; flex-shrink: 0; }
  .pj-filter-tip p { margin: 0; }

  /* ---- Barra de filtros compacta (acima da lista) ---- */
  .pj-filterbar-card { margin-bottom: 20px; }
  .pj-filterbar {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 16px;
    padding: 14px 20px;
  }
  .pj-filterbar-label {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 10.5px;
    font-weight: 700;
    letter-spacing: 1.2px;
    text-transform: uppercase;
    color: var(--pj-gold);
    padding-right: 16px;
    border-right: 1px solid var(--pj-border);
    white-space: nowrap;
  }
  .pj-filterbar-label i { font-size: 14px; }
  .pj-filterbar-fields {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
    flex: 1;
  }
  .pj-filterbar-field { display: flex; align-items: center; gap: 8px; }
  .pj-filterbar-field label {
    font-size: 10.5px;
    font-weight: 600;
    color: var(--pj-text3);
    white-space: nowrap;
  }
  .pj-select-sm, .pj-input-sm {
    background: var(--pj-surface-2);
    border: 1px solid var(--pj-border);
    color: var(--pj-text);
    border-radius: 6px;
    padding: 7px 10px;
    font-family: var(--font-body);
    font-size: 12.5px;
    transition: border-color 0.2s ease;
  }
  .pj-select-sm:focus, .pj-input-sm:focus { outline: none; border-color: var(--pj-gold); }
  .pj-select-sm { min-width: 168px; }
  .pj-input-sm { width: 180px; }
  .pj-input-icon-sm { position: relative; }
  .pj-input-icon-sm > i {
    position: absolute; left: 10px; top: 50%; transform: translateY(-50%);
    color: var(--pj-text3); font-size: 13px; pointer-events: none;
  }
  .pj-input-icon-sm .pj-input-sm { padding-left: 30px; }
  .pj-filterbar-actions { display: flex; align-items: center; gap: 8px; }
  .pj-chip {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 7px 13px;
    border-radius: 20px;
    border: 1px solid var(--pj-border);
    color: var(--pj-text2);
    font-size: 11.5px;
    font-weight: 600;
    text-decoration: none;
    white-space: nowrap;
    transition: all 0.2s ease;
  }
  .pj-chip:hover { border-color: var(--pj-gold); color: var(--pj-gold-bright); }
  .pj-chip i { font-size: 13px; }
  .pj-icon-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 6px;
    border: 1px solid var(--pj-border);
    background: transparent;
    color: var(--pj-text3);
    cursor: pointer;
    transition: all 0.2s ease;
    flex-shrink: 0;
  }
  .pj-icon-btn:hover { border-color: var(--pj-red); color: var(--pj-red); }
  .pj-filterbar-divider { width: 1px; align-self: stretch; background: var(--pj-border); margin: 0 2px; }
  .pj-filterbar-note {
    width: 100%;
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 11px;
    color: var(--pj-text3);
    padding: 10px 20px 0;
    margin-top: 2px;
    border-top: 1px solid var(--pj-border);
  }
  .pj-filterbar-note i { color: var(--pj-gold); font-size: 13px; }
  @media (max-width: 900px) {
    .pj-filterbar-label { border-right: none; padding-right: 0; }
    .pj-filterbar { flex-direction: column; align-items: stretch; }
    .pj-filterbar-fields, .pj-filterbar-actions { width: 100%; }
    .pj-select-sm, .pj-input-sm { flex: 1; min-width: 0; width: 100%; }
  }

  /* ---------------- Paginação ---------------- */
  .pj-page-link {
    padding: 6px 12px;
    border-radius: 5px;
    font-size: 12.5px;
    font-weight: 600;
    text-decoration: none;
    border: 1px solid var(--pj-border);
    color: var(--pj-text2);
  }
  .pj-page-link:hover { border-color: var(--pj-gold); color: var(--pj-gold-bright); }
  .pj-page-link.active { background: var(--pj-gold); border-color: var(--pj-gold); color: #14100a; }
  .pj-page-link.disabled { opacity: 0.35; pointer-events: none; }

  .pj-empty {
    text-align: center;
    padding: 48px 20px;
    color: var(--pj-text3);
    font-size: 13.5px;
  }

  /* Gantt */
  #master-gantt-container { margin-top: 4px; }
  #master-gantt .bar-milestone .bar { fill: var(--pj-gold) !important; }
  #master-gantt .grid-header { fill: var(--pj-surface-2) !important; }
</style>

<!-- Incluindo a biblioteca Frappe Gantt via CDN -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/frappe-gantt@0.6.1/dist/frappe-gantt.css">
<script src="https://cdn.jsdelivr.net/npm/frappe-gantt@0.6.1/dist/frappe-gantt.min.js"></script>

<?php
// Helper para gerar links de ordenação
$orderBy = $filtros['orderBy'] ?? 'id';
$orderDir = $filtros['orderDir'] ?? 'DESC';

function renderSortLink($column, $label, $currentOrderBy, $currentOrderDir, $baseUrl, $filtros)
{
    $newDir = ($currentOrderBy === $column && $currentOrderDir === 'ASC') ? 'DESC' : 'ASC';
    $icon = '';
    if ($currentOrderBy === $column) {
        $icon = $currentOrderDir === 'ASC' ? "<i class='bx bx-chevron-up'></i>" : "<i class='bx bx-chevron-down'></i>";
    }

    $params = array_merge($filtros, ['orderBy' => $column, 'orderDir' => $newDir, 'page' => 1]);
    $url = BASE_URL . $baseUrl . '?' . http_build_query($params);

    return "<a href='{$url}' class='group inline-flex items-center transition-colors cursor-pointer'>{$label} <span class='ml-1'>{$icon}</span></a>";
}
?>

<div id="projects-page-container">

    <!-- Header -->
    <div class="pj-header">
        <div>
            <p class="pj-eyebrow">Gestão de Projetos</p>
            <h2 class="pj-title">Painel de Controle de Projetos</h2>
            <p class="pj-subtitle">Acompanhamento de cronogramas, marcos, recursos e progresso de cada projeto em andamento.</p>
        </div>
        <div class="pj-header-meta">
            Atualizado em <?php echo date('d/m/Y \à\s H:i'); ?>
        </div>
    </div>

    <!-- KPIs -->
    <div class="pj-stat-grid">
        <!-- Card 1: Total Em Andamento -->
        <div class="pj-stat-card pj-stat-card-blue">
            <i class='bx bx-briefcase pj-stat-icon'></i>
            <p class="pj-stat-label"><i class='bx bx-briefcase'></i> Projetos Ativos</p>
            <p class="pj-stat-value"><?php echo $totalEmAndamento ?? 0; ?></p>
            <div class="pj-stat-foot"><strong>Atual</strong>&nbsp;na carteira</div>
        </div>
        <!-- Card 2: Atrasados -->
        <div class="pj-stat-card pj-stat-card-red">
            <i class='bx bx-time-five pj-stat-icon'></i>
            <p class="pj-stat-label"><i class='bx bx-time-five'></i> Projetos Atrasados</p>
            <p class="pj-stat-value"><?php echo $projetosAtrasados ?? 0; ?></p>
            <div class="pj-stat-foot"><strong>Atenção</strong>&nbsp;exige replanejamento</div>
        </div>
        <!-- Card 3: Próximos Marcos -->
        <div class="pj-stat-card pj-stat-card-orange">
            <i class='bx bx-flag pj-stat-icon'></i>
            <p class="pj-stat-label"><i class='bx bx-flag'></i> Marcos a Vencer</p>
            <p class="pj-stat-value"><?php echo $proximoMarcoVencer ?? 0; ?></p>
            <div class="pj-stat-foot"><strong>7 dias</strong>&nbsp;entregas iminentes</div>
        </div>
        <!-- Card 4: Faturamento Previsto -->
        <div class="pj-stat-card pj-stat-card-green">
            <i class='bx bx-dollar-circle pj-stat-icon'></i>
            <p class="pj-stat-label"><i class='bx bx-dollar-circle'></i> Faturamento Prev.</p>
            <p class="pj-stat-value mono"><?php echo $faturamentoPrevistoMes ?? 'R$ 0'; ?></p>
            <div class="pj-stat-foot"><strong>No mês</strong>&nbsp;baseado nos marcos</div>
        </div>
    </div>

    <!-- Barra de Filtros Rápidos -->
    <div class="pj-card pj-filterbar-card">
        <div class="pj-filterbar">
            <span class="pj-filterbar-label"><i class='bx bx-filter-alt'></i> Filtros</span>

            <div class="pj-filterbar-fields">
                <div class="pj-filterbar-field">
                    <label for="filtroStatus">Status</label>
                    <select id="filtroStatus" class="pj-select-sm">
                        <option value="Todos Ativos" <?php echo (empty($filtros['status']) || $filtros['status'] === 'Todos Ativos') ? 'selected' : ''; ?>>Todos Ativos</option>
                        <option value="Todos" <?php echo ($filtros['status'] === 'Todos') ? 'selected' : ''; ?>>Todos (Inclui Arquivados)</option>
                        <option value="Em Execução" <?php echo ($filtros['status'] === 'Em Execução') ? 'selected' : ''; ?>>Em Execução</option>
                        <option value="Aguardando Cliente" <?php echo ($filtros['status'] === 'Aguardando Cliente') ? 'selected' : ''; ?>>Aguardando Cliente</option>
                        <option value="Concluído" <?php echo ($filtros['status'] === 'Concluído') ? 'selected' : ''; ?>>Concluído</option>
                        <option value="Cancelado" <?php echo ($filtros['status'] === 'Cancelado') ? 'selected' : ''; ?>>Cancelado</option>
                        <option value="Atrasado" <?php echo ($filtros['status'] === 'Atrasado') ? 'selected' : ''; ?>>Atrasado</option>
                    </select>
                </div>

                <div class="pj-filterbar-field">
                    <label for="filtroResponsavel">Responsável</label>
                    <div class="pj-input-icon-sm">
                        <i class='bx bx-user'></i>
                        <input type="text" id="filtroResponsavel" value="<?php echo htmlspecialchars($filtros['responsavel'] ?? ''); ?>" placeholder="Ex: Mariana A." class="pj-input-sm">
                    </div>
                </div>
            </div>

            <div class="pj-filterbar-actions">
                <?php if (isset($baseUrl) && (strpos($baseUrl, 'arquivados') !== false || strpos($baseUrl, 'cancelados') !== false)): ?>
                    <a href="<?php echo BASE_URL; ?>/projetos" class="pj-chip">
                        <i class='bx bx-arrow-back'></i> Ativos
                    </a>
                <?php else: ?>
                    <a href="<?php echo BASE_URL; ?>/projetos/arquivados" class="pj-chip">
                        <i class='bx bx-archive'></i> Arquivados
                    </a>
                    <a href="<?php echo BASE_URL; ?>/projetos/cancelados" class="pj-chip">
                        <i class='bx bx-x-circle'></i> Cancelados
                    </a>
                <?php endif; ?>

                <div class="pj-filterbar-divider"></div>

                <button type="button" id="limparFiltrosBtn" class="pj-icon-btn" title="Limpar filtros">
                    <i class='bx bx-x'></i>
                </button>
            </div>

            <p class="pj-filterbar-note">
                <i class='bx bx-info-circle'></i>
                O progresso de cada projeto deve ser atualizado diariamente pelos responsáveis.
            </p>
        </div>
    </div>

    <!-- Lista de Projetos -->
    <div id="project-list-container" class="pj-card">
        <div class="pj-card-head">
            <h3 class="pj-card-title">Lista de Projetos</h3>
            <div style="display:flex; gap:10px;">
                <button id="toggle-gantt-view-btn" class="pj-btn pj-btn-outline">
                    <i class='bx bx-calendar'></i> Ver Cronograma
                </button>
                <a href="<?php echo BASE_URL; ?>/projetos/novo" class="pj-btn pj-btn-gold">
                    <i class='bx bx-plus'></i> Novo Projeto
                </a>
            </div>
        </div>

        <div class="pj-card-body">
            <!-- Conteúdo da Lista de Projetos -->
            <div id="project-list-content">
                <div class="pj-table-wrap">
                    <?php if (!empty($projetos)): ?>
                        <table class="pj-table">
                            <thead>
                                <tr>
                                    <th><?php echo renderSortLink('numero_projeto', 'ID', $orderBy, $orderDir, $baseUrl, $filtros); ?></th>
                                    <th><?php echo renderSortLink('nome', 'Nome do Projeto', $orderBy, $orderDir, $baseUrl, $filtros); ?></th>
                                    <th><?php echo renderSortLink('cliente_nome', 'Cliente', $orderBy, $orderDir, $baseUrl, $filtros); ?></th>
                                    <th><?php echo renderSortLink('responsavel', 'Responsável', $orderBy, $orderDir, $baseUrl, $filtros); ?></th>
                                    <th><?php echo renderSortLink('data_inicial', 'Data Início', $orderBy, $orderDir, $baseUrl, $filtros); ?></th>
                                    <th><?php echo renderSortLink('data_fim_prevista', 'Data Fim', $orderBy, $orderDir, $baseUrl, $filtros); ?></th>
                                    <th><?php echo renderSortLink('status', 'Status', $orderBy, $orderDir, $baseUrl, $filtros); ?></th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($projetos as $projeto): ?>
                                    <?php
                                        $statusClass = 'pj-badge-gray';
                                        if ($projeto['status'] === 'Atrasado') $statusClass = 'pj-badge-red';
                                        elseif ($projeto['status'] === 'Marco Vencendo') $statusClass = 'pj-badge-orange';
                                        elseif ($projeto['status'] === 'Em Execução' || $projeto['status'] === 'Em Andamento') $statusClass = 'pj-badge-blue';
                                        elseif ($projeto['status'] === 'Concluído') $statusClass = 'pj-badge-green';
                                    ?>
                                    <tr>
                                        <td class="mono pj-cell-strong"><?php echo htmlspecialchars($projeto['numero_projeto'] ?? 'ID #'.$projeto['id']); ?></td>
                                        <td>
                                            <a href="<?php echo BASE_URL; ?>/projetos/detalhe/<?php echo $projeto['id']; ?>/resumo" class="pj-link">
                                                <?php echo htmlspecialchars($projeto['nome']); ?>
                                            </a>
                                        </td>
                                        <td><?php echo htmlspecialchars($projeto['cliente_nome'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($projeto['responsavel']); ?></td>
                                        <td class="mono">
                                            <?php echo $projeto['data_inicial'] ? date('d/m/Y', strtotime($projeto['data_inicial'])) : 'N/A'; ?>
                                        </td>
                                        <td class="mono">
                                            <?php echo $projeto['data_fim_prevista'] ? date('d/m/Y', strtotime($projeto['data_fim_prevista'])) : 'N/A'; ?>
                                        </td>
                                        <td>
                                            <span class="pj-badge <?php echo $statusClass; ?>">
                                                <?php echo htmlspecialchars($projeto['status']); ?>
                                            </span>
                                        </td>
                                        <td style="white-space:nowrap;">
                                            <?php if ($projeto['status'] === 'Cancelado'): ?>
                                                <form action="<?php echo BASE_URL; ?>/projetos/restaurar/<?php echo $projeto['id']; ?>" method="POST" class="inline-block" onsubmit="return confirm('Deseja restaurar este projeto para Em Execução?');" style="display:inline;">
                                                    <button type="submit" class="pj-icon-action success" style="background:none;border:none;cursor:pointer;" title="Restaurar Projeto">
                                                        <i class='bx bx-undo text-xl'></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                            <a href="<?php echo BASE_URL; ?>/projetos/detalhe/<?php echo $projeto['id']; ?>/resumo" class="pj-icon-action" title="Visualizar Detalhes">
                                                <i class='bx bx-show text-xl'></i>
                                            </a>
                                            <a href="<?php echo BASE_URL; ?>/projetos/editar/<?php echo $projeto['id']; ?>" class="pj-icon-action" title="Editar Projeto">
                                                <i class='bx bx-edit text-xl'></i>
                                            </a>
                                            <form action="<?php echo BASE_URL; ?>/projetos/excluir/<?php echo $projeto['id']; ?>" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir este projeto?');" style="display:inline;">
                                                <button type="submit" class="pj-icon-action danger" style="background:none;border:none;cursor:pointer;" title="Excluir Projeto">
                                                    <i class='bx bx-trash text-xl'></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p class="pj-empty">Nenhum projeto encontrado.</p>
                    <?php endif; ?>
                </div>

                <!-- Navegação da Paginação -->
                <div class="mt-4 flex justify-end items-center">
                    <?php if ($totalPaginas > 1): ?>
                        <nav class="flex items-center justify-end space-x-2">
                            <?php
                            $queryString = http_build_query(array_merge($filtros, ['page' => '']));
                            ?>
                            <a href="<?php echo BASE_URL . $baseUrl; ?>?<?php echo $queryString . ($paginaAtual - 1); ?>" class="pj-page-link <?php echo ($paginaAtual <= 1) ? 'disabled' : ''; ?>">
                                Anterior
                            </a>

                            <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                                <a href="<?php echo BASE_URL . $baseUrl; ?>?<?php echo $queryString . $i; ?>" class="pj-page-link <?php echo ($i == $paginaAtual) ? 'active' : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>

                            <a href="<?php echo BASE_URL . $baseUrl; ?>?<?php echo $queryString . ($paginaAtual + 1); ?>" class="pj-page-link <?php echo ($paginaAtual >= $totalPaginas) ? 'disabled' : ''; ?>">
                                Próxima
                            </a>
                        </nav>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Contêiner para o Gráfico de Gantt Mestre (começa oculto) -->
            <div id="master-gantt-container" class="hidden">
                <h3 class="pj-card-title" style="margin-bottom:16px;">Cronograma Geral de Projetos</h3>
                <div id="master-gantt"></div>
            </div>
        </div>
    </div>

</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggleBtn = document.getElementById('toggle-gantt-view-btn');
        const projectListContent = document.getElementById('project-list-content');
        const masterGanttContainer = document.getElementById('master-gantt-container');
        let ganttChart = null;

        // --- Lógica dos Filtros ---
        const filtroStatus = document.getElementById('filtroStatus');
        const filtroResponsavel = document.getElementById('filtroResponsavel');

        function aplicarFiltros() {
            const status = filtroStatus.value;
            const responsavel = filtroResponsavel.value;
            const url = new URL(window.location.href);

            if (status && status !== 'Todos Ativos') url.searchParams.set('status', status);
            else url.searchParams.delete('status');
            if (responsavel) url.searchParams.set('responsavel', responsavel);
            else url.searchParams.delete('responsavel');

            // Mantém a ordenação atual se existir
            // (Não é necessário código extra aqui, pois new URL() já pega os parâmetros atuais da janela)

            url.searchParams.set('page', 1); // Reseta para a página 1 ao filtrar
            window.location.href = url.toString();
        }

        filtroStatus.addEventListener('change', aplicarFiltros);
        // Aplica filtro de responsável ao pressionar Enter ou ao sair do campo
        filtroResponsavel.addEventListener('change', aplicarFiltros);
        filtroResponsavel.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') aplicarFiltros();
        });

        // --- Limpar Filtros ---
        const limparFiltrosBtn = document.getElementById('limparFiltrosBtn');
        if (limparFiltrosBtn) {
            limparFiltrosBtn.addEventListener('click', function() {
                filtroStatus.value = 'Todos Ativos';
                filtroResponsavel.value = '';
                aplicarFiltros();
            });
        }

        // Prepara os dados dos projetos para o formato do Gantt
        const projectData = <?php echo json_encode($projetos ?? []); ?>;
        const tasksForGantt = projectData
            .filter(p => p.data_inicial && p.data_fim_prevista) // Filtra projetos sem datas
            .map(project => ({
                id: 'proj_' + project.id,
                name: (project.numero_projeto || 'ID #' + project.id) + ' - ' + project.nome,
                start: project.data_inicial,
                end: project.data_fim_prevista,
                progress: 50, // Placeholder, pode ser calculado no futuro
                custom_class: 'bar-milestone' // Estilo opcional
            }));

        let isGanttVisible = false; // Moved to local scope
        toggleBtn.addEventListener('click', function() {
            isGanttVisible = !isGanttVisible; // Toggle state

            if (isGanttVisible) {
                // Esconde a lista e mostra o Gantt
                projectListContent.classList.add('hidden');
                masterGanttContainer.classList.remove('hidden');
                toggleBtn.innerHTML = "<i class='bx bx-list-ul'></i> Ver Lista de Projetos";

                // Inicializa o Gantt apenas na primeira vez
                if (!ganttChart && tasksForGantt.length > 0) {
                    ganttChart = new Gantt("#master-gantt", tasksForGantt, {
                        view_mode: 'Month',
                        language: 'en',
                        on_click: function(task) {
                            // Ao clicar em uma barra, redireciona para os detalhes do projeto
                            const projectId = task.id.replace('proj_', '');
                            window.location.href = `<?php echo BASE_URL; ?>/projetos/detalhe/${projectId}/resumo`;
                        }
                    });
                } else if (tasksForGantt.length === 0) {
                    document.getElementById('master-gantt').innerHTML = '<p class="pj-empty">Não há projetos com datas de início e fim para exibir no cronograma.</p>';
                }
            } else {
                // Esconde o Gantt e mostra a lista
                projectListContent.classList.remove('hidden');
                masterGanttContainer.classList.add('hidden');
                toggleBtn.innerHTML = "<i class='bx bx-calendar'></i> Ver Cronograma";
            }
        });
    });
</script>