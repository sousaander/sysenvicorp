<?php
/**
 * Dashboard - Módulo Recursos Humanos
 * View: rh/index.php
 */

// Dados de exemplo (substituir pelas variáveis do controller)
$totalFuncionarios     = $totalFuncionarios     ?? 148;
$funcionariosFerias    = $funcionariosFerias    ?? 7;
$novasContratacoesMes  = $novasContratacoesMes  ?? 5;
$proximoTreinamento    = $proximoTreinamento    ?? [];
$aniversariantes       = $aniversariantes       ?? [];
$listaFuncionariosFerias = $listaFuncionariosFerias ?? [];
$totalTreinamentos     = $totalTreinamentos     ?? 0;
$funcionarios          = $funcionarios          ?? [];
$filtros               = $filtros               ?? [];
$setores               = $setores               ?? [];
$paginaAtual           = $paginaAtual           ?? 1;
$totalPaginas          = $totalPaginas          ?? 1;
$retornosBreve         = $retornosBreve         ?? 0;
$metaContratacao       = $metaContratacao       ?? 5;
$percentualContratacao = $percentualContratacao ?? 0;
?>

<!-- ═══════════════════════════════════════════
     ESTILOS DO MÓDULO RH — escopo: .rh-module
     ═══════════════════════════════════════════ -->
<style>
/* ── Reset & base ─────────────────────────────── */
.rh-module *, .rh-module *::before, .rh-module *::after {
    box-sizing: border-box;
    margin: 0; padding: 0;
}
.rh-module {
    font-family: 'DM Sans', 'Segoe UI', sans-serif;
    color: var(--c-text, #1a1d27);
    --c-indigo:    #4F46E5;
    --c-indigo-lt: #EEF2FF;
    --c-indigo-dk: #3730A3;
    --c-amber:     #F59E0B;
    --c-amber-lt:  #FFFBEB;
    --c-green:     #10B981;
    --c-green-lt:  #ECFDF5;
    --c-sky:       #0EA5E9;
    --c-sky-lt:    #F0F9FF;
    --c-red:       #EF4444;
    --c-red-lt:    #FEF2F2;
    --c-surface:   var(--db-surface, #ffffff);
    --c-bg:        var(--db-bg, #F8F9FC);
    --c-border:    var(--db-border, #E8EAF0);
    --c-text:      var(--db-text, #1a1d27);
    --c-muted:     var(--db-text2, #6B7280);
    --c-hint:      var(--db-text3, #9CA3AF);
    --radius-sm:   6px;
    --radius-md:   10px;
    --radius-lg:   16px;
    --shadow-sm:   0 1px 3px rgba(0,0,0,.06), 0 1px 2px rgba(0,0,0,.04);
    --shadow-md:   0 4px 12px rgba(0,0,0,.07), 0 2px 4px rgba(0,0,0,.04);
}

.dark-theme .rh-module {
    --c-surface:   var(--db-surface);
    --c-bg:        var(--db-bg);
    --c-border:    var(--db-border);
    --c-text:      var(--db-text);
    --c-muted:     var(--db-text2);
    --c-hint:      var(--db-text3);
    --shadow-sm:   0 4px 6px -1px rgba(0, 0, 0, 0.2);
    --shadow-md:   0 10px 15px -3px rgba(0, 0, 0, 0.3);
}

/* ── Topbar ───────────────────────────────────── */
.rh-topbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 28px;
}
.rh-topbar-title h2 {
    font-size: 22px;
    font-weight: 700;
    color: var(--c-text);
    letter-spacing: -.3px;
}
.rh-topbar-title p {
    font-size: 13px;
    color: var(--c-muted);
    margin-top: 3px;
}
.rh-topbar-actions { display: flex; gap: 8px; align-items: center; }
.btn { display: inline-flex; align-items: center; gap: 6px; padding: 9px 18px; font-size: 13px; font-weight: 600; border-radius: var(--radius-md); cursor: pointer; transition: all .15s; border: none; text-decoration: none; }
.btn-ghost { background: var(--c-surface); color: var(--c-muted); border: 1px solid var(--c-border); }
.btn-ghost:hover { background: var(--c-bg); color: var(--c-text); }
.btn-primary { background: var(--c-indigo); color: #fff; }
.btn-primary:hover { background: var(--c-indigo-dk); transform: translateY(-1px); box-shadow: 0 4px 12px rgba(79,70,229,.35); }

/* ── KPI Cards ────────────────────────────────── */
.rh-kpi-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 14px;
    margin-bottom: 24px;
}
.kpi-card {
    background: var(--c-surface);
    border: 1px solid var(--c-border);
    border-radius: var(--radius-lg);
    padding: 20px;
    position: relative;
    overflow: hidden;
    box-shadow: var(--shadow-sm);
    transition: box-shadow .2s, transform .2s;
}
.kpi-card:hover { box-shadow: var(--shadow-md); transform: translateY(-2px); }
.kpi-card::after {
    content: '';
    position: absolute;
    bottom: 0; left: 0; right: 0;
    height: 3px;
}
.kpi-card.indigo::after { background: var(--c-indigo); }
.kpi-card.amber::after  { background: var(--c-amber); }
.kpi-card.green::after  { background: var(--c-green); }
.kpi-card.sky::after    { background: var(--c-sky); }

.kpi-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 14px; }
.kpi-icon {
    width: 40px; height: 40px;
    border-radius: var(--radius-md);
    display: flex; align-items: center; justify-content: center;
    font-size: 18px;
}
.kpi-icon.indigo { background: var(--c-indigo-lt); }
.kpi-icon.amber  { background: var(--c-amber-lt); }
.kpi-icon.green  { background: var(--c-green-lt); }
.kpi-icon.sky    { background: var(--c-sky-lt); }
.kpi-trend { font-size: 11px; font-weight: 700; padding: 3px 8px; border-radius: 20px; }
.trend-up   { background: var(--c-green-lt); color: #059669; }
.trend-warn { background: var(--c-amber-lt); color: #D97706; }
.trend-info { background: var(--c-sky-lt);   color: #0284C7; }
.trend-neu  { background: var(--c-border); color: var(--c-muted); }

.kpi-value { font-size: 34px; font-weight: 800; color: var(--c-text); line-height: 1; letter-spacing: -1px; }
.kpi-label { font-size: 12px; color: var(--c-muted); margin-top: 5px; font-weight: 500; text-transform: uppercase; letter-spacing: .04em; }
.kpi-sub   { font-size: 12px; color: var(--c-hint); margin-top: 4px; }

/* ── Sparkline ────────────────────────────────── */
.sparkline { display: flex; align-items: flex-end; gap: 3px; height: 28px; margin-top: 10px; }
.sp-bar { flex: 1; border-radius: 2px 2px 0 0; background: var(--c-border); min-height: 4px; }
.sp-bar.hi { background: var(--c-indigo); opacity: .7; }
.sp-bar.hi.last { opacity: 1; }

/* ── Main grid ────────────────────────────────── */
.rh-main-grid {
    display: grid;
    grid-template-columns: 260px 260px 1fr;
    gap: 16px;
    align-items: start;
}

/* ── Panel genérico ───────────────────────────── */
.panel {
    background: var(--c-surface);
    border: 1px solid var(--c-border);
    border-radius: var(--radius-lg);
    padding: 20px;
    box-shadow: var(--shadow-sm);
}
.panel-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
    padding-bottom: 14px;
    border-bottom: 1px solid var(--c-border);
}
.panel-title { font-size: 14px; font-weight: 700; color: var(--c-text); }
.panel-action { font-size: 12px; color: var(--c-indigo); text-decoration: none; font-weight: 600; }
.panel-action:hover { text-decoration: underline; }

/* ── Pessoas (aniversário / férias) ───────────── */
.person-list { display: flex; flex-direction: column; gap: 0; }
.person-row {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 0;
    border-bottom: 1px solid var(--c-border);
    transition: background .1s;
}
.person-row:last-child { border-bottom: none; padding-bottom: 0; }
.person-row:first-child { padding-top: 0; }
.avatar {
    width: 34px; height: 34px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 12px; font-weight: 700;
    flex-shrink: 0;
    letter-spacing: -.3px;
}
.av-purple { background: #EDE9FE; color: #5B21B6; }
.av-teal   { background: #D1FAE5; color: #065F46; }
.av-orange { background: #FEE3C7; color: #9A3412; }
.av-pink   { background: #FCE7F3; color: #9D174D; }
.av-blue   { background: #DBEAFE; color: #1E40AF; }
.av-lime   { background: #D9F99D; color: #3F6212; }

.person-info { flex: 1; min-width: 0; }
.person-name { font-size: 13px; font-weight: 600; color: var(--c-text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.person-meta { font-size: 11px; color: var(--c-muted); margin-top: 1px; }
.person-right { flex-shrink: 0; text-align: right; }

.pill { display: inline-block; font-size: 10px; font-weight: 700; padding: 3px 8px; border-radius: 20px; letter-spacing: .02em; }
.pill-today   { background: var(--c-indigo-lt); color: var(--c-indigo); }
.pill-date    { background: var(--c-border); color: var(--c-muted); }
.pill-ferias  { background: var(--c-amber-lt); color: var(--c-amber); }
.pill-ativo   { background: var(--c-green-lt); color: var(--c-green); }
.pill-licenca { background: var(--c-sky-lt); color: var(--c-sky); }
.pill-setor   { background: var(--c-border); color: var(--c-muted); font-size: 10px; }

/* ── Seção extra dentro do painel ─────────────── */
.panel-section {
    margin-top: 18px;
    padding-top: 16px;
    border-top: 1px solid var(--c-border);
}
.panel-section-title { font-size: 13px; font-weight: 700; color: var(--c-text); margin-bottom: 12px; }

/* ── Treinamentos ─────────────────────────────── */
.trein-item {
    background: var(--c-bg);
    border: 1px solid var(--c-border);
    border-radius: var(--radius-md);
    padding: 12px;
    margin-bottom: 8px;
}
.trein-item:last-child { margin-bottom: 0; }
.trein-name { font-size: 13px; font-weight: 600; color: var(--c-text); margin-bottom: 3px; }
.trein-meta { font-size: 11px; color: var(--c-muted); }
.progress-bar { height: 4px; background: var(--c-border); border-radius: 2px; margin-top: 8px; overflow: hidden; }
.progress-fill { height: 100%; border-radius: 2px; background: var(--c-indigo); transition: width .4s ease; }

/* ── Barras de distribuição ───────────────────── */
.dist-row { margin-bottom: 9px; }
.dist-row:last-child { margin-bottom: 0; }
.dist-label { display: flex; justify-content: space-between; font-size: 12px; color: var(--c-muted); margin-bottom: 4px; font-weight: 500; }
.dist-label span:last-child { color: var(--c-text); font-weight: 700; }
.dist-bar { height: 5px; background: var(--c-border); border-radius: 3px; overflow: hidden; }
.dist-fill { height: 100%; border-radius: 3px; background: linear-gradient(90deg, var(--c-indigo), #818CF8); }

/* ── Tabela de funcionários ───────────────────── */
.table-toolbar {
    display: flex;
    gap: 8px;
    margin-bottom: 14px;
    align-items: center;
}
.input-search {
    flex: 1;
    border: 1px solid var(--c-border);
    border-radius: var(--radius-md);
    padding: 8px 12px 8px 34px;
    font-size: 13px;
    color: var(--c-text);
    background: var(--c-bg) url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%239CA3AF' stroke-width='2'%3E%3Ccircle cx='11' cy='11' r='8'/%3E%3Cpath d='m21 21-4.35-4.35'/%3E%3C/svg%3E") no-repeat 10px center;
    outline: none;
    transition: border-color .15s, box-shadow .15s;
}
.input-search:focus { border-color: var(--c-indigo); box-shadow: 0 0 0 3px rgba(79,70,229,.1); }
.input-setor {
    width: 130px;
    border: 1px solid var(--c-border);
    border-radius: var(--radius-md);
    padding: 8px 12px;
    font-size: 13px;
    color: var(--c-text);
    background: var(--c-bg);
    outline: none;
    transition: border-color .15s;
}
.input-setor:focus { border-color: var(--c-indigo); box-shadow: 0 0 0 3px rgba(79,70,229,.1); }
.btn-filter { background: var(--c-indigo); color: #fff; border: none; border-radius: var(--radius-md); padding: 8px 16px; font-size: 13px; font-weight: 600; cursor: pointer; transition: background .15s; }
.btn-filter:hover { background: var(--c-indigo-dk); }
.btn-clear { background: var(--c-surface); color: var(--c-muted); border: 1px solid var(--c-border); border-radius: var(--radius-md); padding: 8px 14px; font-size: 13px; cursor: pointer; text-decoration: none; }
.btn-clear:hover { background: var(--c-bg); }

.rh-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.rh-table thead th {
    font-size: 11px;
    font-weight: 700;
    color: var(--c-muted);
    text-align: left;
    padding: 8px 12px;
    background: var(--c-bg);
    text-transform: uppercase;
    letter-spacing: .05em;
    white-space: nowrap;
}
.rh-table thead th:first-child { border-radius: var(--radius-sm) 0 0 var(--radius-sm); }
.rh-table thead th:last-child  { border-radius: 0 var(--radius-sm) var(--radius-sm) 0; text-align: right; }
.rh-table tbody tr {
    border-bottom: 1px solid var(--c-border);
    transition: background .1s;
    cursor: default;
}
.rh-table tbody tr:last-child { border-bottom: none; }
.rh-table tbody tr:hover { background: var(--c-bg); opacity: 0.8; }
.rh-table tbody td { padding: 11px 12px; vertical-align: middle; }
.rh-table tbody td:last-child { text-align: right; }
.td-name { font-weight: 600; color: var(--c-text); }
.td-cargo { color: var(--c-muted); }
.td-flex { display: flex; align-items: center; gap: 9px; }
.btn-detalhes { font-size: 12px; font-weight: 600; color: var(--c-indigo); text-decoration: none; padding: 4px 10px; border-radius: var(--radius-sm); transition: background .15s; }
.btn-detalhes:hover { background: var(--c-indigo-lt); }

/* ── Paginação ────────────────────────────────── */
.pagination {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 14px;
    padding-top: 14px;
    border-top: 1px solid var(--c-border);
}
.pg-info { font-size: 12px; color: var(--c-muted); }
.pg-nav { display: flex; gap: 4px; }
.pg-btn {
    display: inline-flex; align-items: center; justify-content: center;
    min-width: 32px; height: 32px;
    padding: 0 8px;
    font-size: 13px; font-weight: 600;
    border-radius: var(--radius-sm);
    border: 1px solid var(--c-border);
    background: var(--c-surface);
    color: var(--c-muted);
    cursor: pointer;
    text-decoration: none;
    transition: all .15s;
}
.pg-btn:hover:not(.disabled):not(.active) { border-color: var(--c-indigo); color: var(--c-indigo); }
.pg-btn.active { background: var(--c-indigo); color: #fff; border-color: var(--c-indigo); }
.pg-btn.disabled { opacity: .4; pointer-events: none; cursor: default; }

/* ── Responsivo ───────────────────────────────── */
@media (max-width: 1100px) {
    .rh-main-grid { grid-template-columns: 1fr 1fr; }
    .rh-main-grid .panel:last-child { grid-column: span 2; }
}
@media (max-width: 768px) {
    .rh-kpi-grid { grid-template-columns: repeat(2, 1fr); }
    .rh-main-grid { grid-template-columns: 1fr; }
    .rh-main-grid .panel:last-child { grid-column: span 1; }
    .rh-topbar { flex-direction: column; align-items: flex-start; gap: 12px; }
}
@media (max-width: 480px) {
    .rh-kpi-grid { grid-template-columns: 1fr; }
}
</style>

<!-- ═══════════════════════════════════════════
     MARKUP DO DASHBOARD
     ═══════════════════════════════════════════ -->
<div class="rh-module">

    <!-- ── Topbar ─────────────────────────────── -->
    <div class="rh-topbar">
        <div class="rh-topbar-title">
            <h2>Recursos Humanos</h2>
            <p>Visão geral</p>
        </div>
        <div class="rh-topbar-actions">
            <a href="<?php echo BASE_URL; ?>/rh/relatorio" class="btn btn-ghost">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Exportar
            </a>
            <a href="<?php echo BASE_URL; ?>/rh/registroFuncionario" class="btn btn-primary">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Novo Funcionário
            </a>
        </div>
    </div>

    <!-- ── KPI Cards ──────────────────────────── -->
    <div class="rh-kpi-grid">
        <!-- Total de Funcionários -->
        <div class="kpi-card indigo">
            <div class="kpi-header">
                <div class="kpi-icon indigo">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#4F46E5" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </div>
            </div>
            <div class="kpi-value"><?php echo number_format($totalFuncionarios); ?></div>
            <div class="kpi-label">Total de Funcionários</div>
            <div class="kpi-sub">Ativos na folha de pagamento</div>
        </div>

        <!-- Em Férias -->
        <div class="kpi-card amber" title="Clique para ver a lista de colaboradores ausentes">
            <div class="kpi-header">
                <div class="kpi-icon amber">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#F59E0B" stroke-width="2"><path d="M17 18a5 5 0 0 0-10 0"/><line x1="12" y1="2" x2="12" y2="9"/><line x1="4.22" y1="10.22" x2="5.64" y2="11.64"/><line x1="1" y1="18" x2="3" y2="18"/><line x1="21" y1="18" x2="23" y2="18"/><line x1="18.36" y1="11.64" x2="19.78" y2="10.22"/></svg>
                </div>
                <div class="d-flex" style="display:flex;gap:4px;align-items:center;">
                    <a href="#panel-ferias" class="panel-action">Ver Lista</a>
                </div>
            </div>
            <div class="kpi-value"><?php echo $funcionariosFerias ?? 0; ?></div>
            <div class="kpi-label">Férias Ativas</div>
            <div class="kpi-sub">
                <?php echo ($retornosBreve > 0) ? "$retornosBreve retornam esta semana" : 'Nenhum retorno próximo'; ?>
            </div>
            <?php if ($retornosBreve > 0): ?>
                <span class="kpi-trend trend-warn" style="display:inline-block;margin-top:10px;">Retornos próximos</span>
            <?php endif; ?>
        </div>

        <!-- Novas Contratações -->
        <div class="kpi-card green">
            <div class="kpi-header">
                <div class="kpi-icon green">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
                </div>
                <span class="kpi-trend <?php echo $percentualContratacao >= 100 ? 'trend-up' : 'trend-neu'; ?>"><?php echo round($percentualContratacao); ?>% da meta</span>
            </div>
            <div class="kpi-value"><?php echo $novasContratacoesMes; ?></div>
            <div class="kpi-label">Contratações no Mês</div>
            <div class="kpi-sub">Meta: <?php echo $metaContratacao; ?> contratações</div>
            <div class="progress-bar" style="margin-top:10px;">
                <div class="progress-fill" style="background:var(--c-green);width:<?php echo $percentualContratacao; ?>%;"></div>
            </div>
        </div>

        <!-- Próximo Treinamento -->
        <div class="kpi-card sky">
            <div class="kpi-header">
                <div class="kpi-icon sky">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#0EA5E9" stroke-width="2"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                </div>
                <a href="<?php echo BASE_URL; ?>/rh/treinamentos" class="panel-action">Gerenciar</a>
            </div>
            <?php if (!empty($proximoTreinamento['nome'])): ?>
                <div class="kpi-value" style="font-size:18px;line-height:1.3;letter-spacing:-.2px;"><?php echo htmlspecialchars($proximoTreinamento['nome']); ?></div>
                <div class="kpi-label" style="margin-top:6px;">Agenda de Treinamento</div>
                <div class="kpi-sub">Previsto para <?php echo date('d/m/Y', strtotime($proximoTreinamento['data_prevista'])); ?></div>
                <span class="kpi-trend trend-info" style="display:inline-block;margin-top:10px;"><?php echo $totalTreinamentos; ?> treinamentos ativos</span>
            <?php else: ?>
                <div class="kpi-value" style="font-size:18px;">Nenhum agendado</div>
                <div class="kpi-label" style="margin-top:6px;">Próximo Treinamento</div>
                <a href="<?php echo BASE_URL; ?>/rh/treinamentos" class="btn btn-ghost" style="margin-top:10px;font-size:12px;padding:6px 12px;">Agendar</a>
            <?php endif; ?>
        </div>
    </div>

    <!-- ── Main Grid ───────────────────────────── -->
    <div class="rh-main-grid">

        <!-- ── Painel 1: Aniversariantes ── -->
        <div class="panel">
            <div class="panel-header">
                <span class="panel-title">Aniversariantes da Semana</span>
            </div>

            <?php if (!empty($aniversariantes)): ?>
            <div class="person-list">
                <?php foreach ($aniversariantes as $aniv):
                    $iniciais = strtoupper(substr($aniv['nome'], 0, 1) . (strpos($aniv['nome'], ' ') !== false ? substr(strrchr($aniv['nome'], ' '), 1, 1) : ''));
                    $cores = ['av-purple','av-teal','av-orange','av-pink','av-blue','av-lime'];
                    $cor = $cores[crc32($aniv['nome']) % count($cores)];
                    $hoje = date('d/m') === $aniv['data'];
                ?>
                <div class="person-row">
                    <div class="avatar <?php echo $cor; ?>"><?php echo $iniciais; ?></div>
                    <div class="person-info">
                        <div class="person-name"><?php echo htmlspecialchars($aniv['nome']); ?></div>
                        <div class="person-meta"><?php echo htmlspecialchars($aniv['setor']); ?></div>
                    </div>
                    <div class="person-right">
                        <?php if ($hoje): ?>
                            <span class="pill pill-today">Hoje</span>
                        <?php else: ?>
                            <span class="pill pill-date"><?php echo $aniv['data']; ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
                <p style="font-size:13px;color:var(--c-muted);padding:8px 0;">Nenhum aniversário esta semana.</p>
            <?php endif; ?>
        </div>

        <!-- ── Painel 2: Lista de Férias ─── -->
        <div class="panel" id="panel-ferias">
            <div class="panel-header">
                <span class="panel-title">Quem está de Férias?</span>
                <a href="<?php echo BASE_URL; ?>/rh/historicoFerias" class="panel-action">Ver tudo</a>
            </div>

            <?php if (!empty($listaFuncionariosFerias)): ?>
            <div class="person-list">
                <?php foreach ($listaFuncionariosFerias as $ferias):
                    $dataRetorno = new DateTime($ferias['data_inicio_ferias']);
                    $dataRetorno->add(new DateInterval('P' . $ferias['dias_ferias'] . 'D'));
                    $nome = $ferias['funcionario_nome'];
                    $iniciais = strtoupper(substr($nome, 0, 1) . (strpos($nome, ' ') !== false ? substr(strrchr($nome, ' '), 1, 1) : ''));
                    $cores = ['av-purple','av-teal','av-orange','av-pink','av-blue','av-lime'];
                    $cor = $cores[crc32($nome) % count($cores)];
                ?>
                <div class="person-row">
                    <div class="avatar <?php echo $cor; ?>"><?php echo $iniciais; ?></div>
                    <div class="person-info">
                        <div class="person-name"><?php echo htmlspecialchars($nome); ?></div>
                        <div class="person-meta">Retorna <?php echo $dataRetorno->format('d/m/Y'); ?></div>
                    </div>
                    <div class="person-right">
                        <span class="pill pill-ferias">Férias</span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
                <p style="font-size:13px;color:var(--c-muted);padding:8px 0;">Nenhum funcionário em férias no momento.</p>
            <?php endif; ?>
        </div>

        <!-- ── Painel 3: Tabela de Funcionários ── -->
        <div class="panel" style="display: flex; flex-direction: column; gap: 24px;">
            <!-- Distribuição por Setor (Movido para cá para otimizar os painéis laterais) -->
            <div class="panel-section" style="border-top: none; padding-top: 0; margin-top: 0;">
                <div class="panel-section-title">Ocupação por Setor</div>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); gap: 15px;">
                    <?php foreach ($setores as $s):
                        $pct = ($totalFuncionarios > 0) ? round(($s['qtd'] / $totalFuncionarios) * 100) : 0;
                    ?>
                    <div class="dist-row">
                        <div class="dist-label"><span><?php echo $s['nome']; ?></span> <span><?php echo $s['qtd']; ?></span></div>
                        <div class="dist-bar"><div class="dist-fill" style="width:<?php echo $pct; ?>%;"></div></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="panel-header">
                <span class="panel-title">Lista de Funcionários</span>
            </div>

            <!-- Filtros -->
            <form method="GET" action="<?php echo BASE_URL; ?>/rh" class="table-toolbar">
                <input
                    type="text"
                    name="nome"
                    class="input-search"
                    placeholder="Buscar por nome..."
                    value="<?php echo htmlspecialchars($filtros['nome'] ?? ''); ?>"
                >
                <input
                    type="text"
                    name="setor"
                    class="input-setor"
                    placeholder="Setor..."
                    value="<?php echo htmlspecialchars($filtros['setor'] ?? ''); ?>"
                >
                <button type="submit" class="btn-filter">Filtrar</button>
                <a href="<?php echo BASE_URL; ?>/rh" class="btn-clear">Limpar</a>
            </form>

            <!-- Tabela -->
            <div style="overflow-x:auto;">
                <table class="rh-table">
                    <thead>
                        <tr>
                            <th>Funcionário</th>
                            <th>Cargo</th>
                            <th>Setor</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($funcionarios)): ?>
                            <?php foreach ($funcionarios as $func):
                                $nome = $func['nome'];
                                $iniciais = strtoupper(substr($nome, 0, 1) . (strpos($nome, ' ') !== false ? substr(strrchr($nome, ' '), 1, 1) : ''));
                                $cores = ['av-purple','av-teal','av-orange','av-pink','av-blue','av-lime'];
                                $cor = $cores[crc32($nome) % count($cores)];
                                $statusMap = [
                                    'Ativo'   => 'pill-ativo',
                                    'Férias'  => 'pill-ferias',
                                    'Licença' => 'pill-licenca',
                                ];
                                $statusPill = $statusMap[$func['status']] ?? 'pill-date';
                            ?>
                            <tr>
                                <td>
                                    <div class="td-flex">
                                        <div class="avatar <?php echo $cor; ?>" style="width:30px;height:30px;font-size:11px;"><?php echo $iniciais; ?></div>
                                        <span class="td-name"><?php echo htmlspecialchars($nome); ?></span>
                                    </div>
                                </td>
                                <td class="td-cargo"><?php echo htmlspecialchars($func['cargo']); ?></td>
                                <td><span class="pill pill-setor"><?php echo htmlspecialchars($func['setor']); ?></span></td>
                                <td><span class="pill <?php echo $statusPill; ?>"><?php echo htmlspecialchars($func['status']); ?></span></td>
                                <td>
                                    <a href="<?php echo BASE_URL; ?>/rh/detalhe/<?php echo $func['id']; ?>" class="btn-detalhes">Detalhes</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" style="padding:24px;text-align:center;color:var(--c-muted);">
                                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#D1D5DB" stroke-width="1.5" style="display:block;margin:0 auto 8px;"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                                    Nenhum funcionário encontrado.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Paginação -->
            <?php if ($totalPaginas >= 1): ?>
            <?php $queryString = http_build_query(array_merge($filtros, ['page' => ''])); ?>
            <div class="pagination">
                <span class="pg-info">
                    Página <?php echo $paginaAtual; ?> de <?php echo $totalPaginas; ?>
                </span>
                <div class="pg-nav">
                    <a href="<?php echo BASE_URL; ?>/rh?<?php echo $queryString . ($paginaAtual - 1); ?>"
                       class="pg-btn <?php echo ($paginaAtual <= 1) ? 'disabled' : ''; ?>">
                        &#8592; Anterior
                    </a>
                    <?php for ($i = 1; $i <= min($totalPaginas, 5); $i++): ?>
                    <a href="<?php echo BASE_URL; ?>/rh?<?php echo $queryString . $i; ?>"
                       class="pg-btn <?php echo ($i == $paginaAtual) ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                    <?php endfor; ?>
                    <?php if ($totalPaginas > 5): ?>
                    <span class="pg-btn disabled">…</span>
                    <?php endif; ?>
                    <a href="<?php echo BASE_URL; ?>/rh?<?php echo $queryString . ($paginaAtual + 1); ?>"
                       class="pg-btn <?php echo ($paginaAtual >= $totalPaginas) ? 'disabled' : ''; ?>">
                        Próxima &#8594;
                    </a>
                </div>
            </div>
            <?php endif; ?>
        </div>

    </div><!-- /.rh-main-grid -->
</div><!-- /.rh-module -->
