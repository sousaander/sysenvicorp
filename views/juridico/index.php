<?php
/**
 * View: Dashboard Jurídico
 * Controller: JuridicoController::dashboard()
 *
 * Variáveis esperadas (injetadas pelo controller):
 *   $kpis                 array   – indicadores principais
 *   $prazosProximos       array   – prazos dos próximos 7 dias
 *   $processos            array   – lista paginada de processos ativos
 *   $andamentosRecentes   array   – últimas movimentações
 *   $audiencias           array   – próximas audiências
 *   $distribuicaoTipos    array   – contagem por tipo de processo
 *   $responsaveis         array   – carga por advogado
 *   $prazosHoje           int     – quantidade de prazos vencendo hoje
 *   $dataAtualizacao      string  – timestamp da última atualização
 *   $tipoFiltro           string  – filtro de tipo ativo (todos|civel|trabalhista|…)
 */

// Mapeamento para substituir o strftime depreciado
$meses_abrev = [
    'Jan' => 'Jan', 'Feb' => 'Fev', 'Mar' => 'Mar', 'Apr' => 'Abr', 'May' => 'Mai', 'Jun' => 'Jun',
    'Jul' => 'Jul', 'Aug' => 'Ago', 'Sep' => 'Set', 'Oct' => 'Out', 'Nov' => 'Nov', 'Dec' => 'Dez'
];

?>

<style>
    /* ── Layout e utilitários ───────────────────────────────────── */
    .jur-root { 
        display: flex; 
        flex-direction: column; 
        gap: 1.5rem; 
        width: 100%;
        max-width: 100%;
        min-width: 0; /* Impede que filhos com largura fixa expandam o container */
    }

    /* Rolagem horizontal suave para tabelas em dispositivos móveis */
    .overflow-x-auto {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch; /* Habilita momentum scroll no iOS */
        scrollbar-width: thin;
        scrollbar-color: rgba(148, 163, 184, 0.4) transparent;
        width: 100%;
        min-width: 0;
    }

    /* Estilização da barra de rolagem para navegadores Webkit (Chrome, Edge, Safari) */
    .overflow-x-auto::-webkit-scrollbar { height: 5px; }
    .overflow-x-auto::-webkit-scrollbar-track { background: transparent; }
    .overflow-x-auto::-webkit-scrollbar-thumb {
        background: rgba(148, 163, 184, 0.4);
        border-radius: 10px;
    }
    .dark .overflow-x-auto::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.1); }

    /* Garante legibilidade forçando a largura mínima da tabela para acionar o scroll */
    .sys-table { 
        min-width: 900px;
        width: 100%;
    }

    /* KPI grid */
    .jur-kpi-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
        gap: 10px;
    }
    .jur-kpi {
        position: relative;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        gap: 6px;
        min-height: 105px;
        padding: 1rem 1rem 1rem 1.25rem;
    }
    .jur-kpi::before {
        content: '';
        position: absolute;
        left: 0; top: 0; bottom: 0;
        width: 3px;
        border-radius: 0;
    }
    .jur-kpi.kpi-purple::before { background: #534AB7; }
    .jur-kpi.kpi-red::before    { background: #D85A30; }
    .jur-kpi.kpi-amber::before  { background: #BA7517; }
    .jur-kpi.kpi-green::before  { background: #1D9E75; }
    .jur-kpi.kpi-blue::before   { background: #185FA5; }
    .jur-kpi label {
        font-size: 10px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: var(--slate-400, #94a3b8);
        line-height: 1;
    }
    .jur-kpi .jur-val {
        font-size: 28px;
        font-weight: 700;
        color: var(--slate-800, #1e293b);
        line-height: 1;
    }
    .dark .jur-kpi .jur-val { color: #fff; }
    .jur-kpi .jur-sub {
        font-size: 11px;
        display: flex;
        align-items: center;
        gap: 4px;
        color: var(--slate-400, #94a3b8);
    }
    .jur-kpi .jur-sub.up   { color: #1D9E75; }
    .jur-kpi .jur-sub.down { color: #D85A30; }

    /* Alerta de prazo fatal */
    .jur-alert {
        background: #FCEBEB;
        border: 0.5px solid #F7C1C1;
        border-radius: 8px;
        padding: .7rem 1rem;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .dark .jur-alert { background: #501313; border-color: #791F1F; }
    .jur-alert i    { color: #A32D2D; font-size: 18px; flex-shrink: 0; }
    .dark .jur-alert i { color: #F09595; }
    .jur-alert span { font-size: 12px; color: #791F1F; }
    .dark .jur-alert span { color: #F7C1C1; }
    .jur-alert a    { color: #A32D2D; font-weight: 600; text-decoration: none; }
    .jur-alert a:hover { text-decoration: underline; }

    .jur-col-right { display: flex; flex-direction: column; gap: 1rem; }

    /* Prazos list */
    .jur-prazo-row {
        display: grid;
        grid-template-columns: 54px 1fr auto;
        gap: 10px;
        align-items: center;
        padding: .55rem 1rem;
        border-bottom: 0.5px solid var(--border-color, #e2e8f0);
        transition: background .15s;
    }
    .jur-prazo-row:last-child { border-bottom: none; }
    .jur-prazo-row:hover { background: var(--slate-50, #f8fafc); }
    .dark .jur-prazo-row:hover { background: rgba(255,255,255,.04); }
    .jur-date-box {
        display: flex; flex-direction: column; align-items: center;
        border-radius: 6px; padding: 4px 6px;
        background: var(--slate-50, #f8fafc);
    }
    .dark .jur-date-box { background: rgba(255,255,255,.06); }
    .jur-date-box.crit { background: #FCEBEB; }
    .dark .jur-date-box.crit { background: #501313; }
    .jur-date-box.warn { background: #FAEEDA; }
    .dark .jur-date-box.warn { background: #412402; }
    .jur-date-box .jur-day  { font-size: 18px; font-weight: 700; line-height: 1; color: var(--slate-800, #1e293b); }
    .dark .jur-date-box .jur-day { color: #f1f5f9; }
    .jur-date-box.crit .jur-day  { color: #A32D2D; }
    .dark .jur-date-box.crit .jur-day { color: #F09595; }
    .jur-date-box.warn .jur-day  { color: #854F0B; }
    .dark .jur-date-box.warn .jur-day { color: #FAC775; }
    .jur-date-box .jur-mon  { font-size: 9px; font-weight: 600; text-transform: uppercase; color: var(--slate-400, #94a3b8); }
    .jur-date-box.crit .jur-mon { color: #A32D2D; }
    .dark .jur-date-box.crit .jur-mon { color: #F09595; }
    .jur-date-box.warn .jur-mon { color: #854F0B; }
    .dark .jur-date-box.warn .jur-mon { color: #FAC775; }
    .jur-prazo-meta .pt { font-size: 13px; color: var(--slate-700, #334155); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .dark .jur-prazo-meta .pt { color: #cbd5e1; }
    .jur-prazo-meta .ps { font-size: 11px; color: var(--slate-400, #94a3b8); margin-top: 2px; }

    /* Badges de dias */
    .jur-dias { font-size: 10px; font-weight: 600; padding: 3px 9px; border-radius: 20px; white-space: nowrap; }
    .jur-dias.d-crit  { background: #FCEBEB; color: #A32D2D; }
    .jur-dias.d-warn  { background: #FAEEDA; color: #854F0B; }
    .jur-dias.d-ok    { background: #EAF3DE; color: #3B6D11; }
    .dark .jur-dias.d-crit { background: #501313; color: #F09595; }
    .dark .jur-dias.d-warn { background: #412402; color: #FAC775; }
    .dark .jur-dias.d-ok   { background: #173404; color: #C0DD97; }

    /* Barras de distribuição */
    .jur-bar-row { display: flex; align-items: center; gap: 8px; margin-bottom: 10px; }
    .jur-bar-label { font-size: 12px; color: var(--slate-500, #64748b); width: 94px; flex-shrink: 0; text-align: right; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .jur-bar-track { flex: 1; height: 8px; background: var(--slate-100, #f1f5f9); border-radius: 4px; overflow: hidden; }
    .dark .jur-bar-track { background: rgba(255,255,255,.08); }
    .jur-bar-fill  { height: 100%; border-radius: 4px; transition: width .4s ease; }
    .jur-bar-count { font-size: 12px; font-weight: 600; width: 24px; flex-shrink: 0; text-align: right; color: var(--slate-800, #1e293b); }
    .dark .jur-bar-count { color: #f1f5f9; }

    /* Responsáveis */
    .jur-resp-row { display: flex; align-items: center; gap: 10px; padding: .55rem 0; }
    .jur-avatar {
        width: 30px; height: 30px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 11px; font-weight: 700; flex-shrink: 0;
        background: #EEEDFE; color: #534AB7;
    }
    .jur-resp-info { flex: 1; min-width: 0; }
    .jur-resp-info .rn { font-size: 12px; font-weight: 600; color: var(--slate-800, #1e293b); }
    .dark .jur-resp-info .rn { color: #f1f5f9; }
    .jur-resp-info .rc { font-size: 10px; color: var(--slate-400, #94a3b8); }
    .jur-minibar-track { width: 80px; height: 4px; background: var(--slate-100, #f1f5f9); border-radius: 2px; overflow: hidden; flex-shrink: 0; }
    .dark .jur-minibar-track { background: rgba(255,255,255,.08); }
    .jur-minibar-fill  { height: 100%; background: #534AB7; border-radius: 2px; }
    .jur-resp-count { font-size: 13px; font-weight: 600; color: var(--slate-800, #1e293b); flex-shrink: 0; min-width: 20px; text-align: right; }
    .dark .jur-resp-count { color: #f1f5f9; }

    /* Andamentos */
    .jur-and-row { display: flex; gap: 10px; align-items: flex-start; padding: .6rem 1rem; border-bottom: 0.5px solid var(--border-color, #e2e8f0); transition: background .15s; }
    .jur-and-row:last-child { border-bottom: none; }
    .jur-and-row:hover { background: var(--slate-50, #f8fafc); }
    .dark .jur-and-row:hover { background: rgba(255,255,255,.04); }
    .jur-and-dot {
        width: 28px; height: 28px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0; margin-top: 1px;
    }
    .jur-and-dot.ic-purple { background: #EEEDFE; color: #534AB7; }
    .jur-and-dot.ic-amber  { background: #FAEEDA; color: #854F0B; }
    .jur-and-dot.ic-green  { background: #EAF3DE; color: #3B6D11; }
    .jur-and-dot.ic-red    { background: #FCEBEB; color: #A32D2D; }
    .jur-and-dot.ic-blue   { background: #E6F1FB; color: #185FA5; }
    .dark .jur-and-dot.ic-purple { background: #26215C; color: #AFA9EC; }
    .dark .jur-and-dot.ic-amber  { background: #412402; color: #FAC775; }
    .dark .jur-and-dot.ic-green  { background: #173404; color: #97C459; }
    .dark .jur-and-dot.ic-red    { background: #501313; color: #F09595; }
    .dark .jur-and-dot.ic-blue   { background: #042C53; color: #85B7EB; }
    .jur-and-body { flex: 1; min-width: 0; }
    .jur-and-body .at { font-size: 13px; color: var(--slate-700, #334155); }
    .dark .jur-and-body .at { color: #cbd5e1; }
    .jur-and-body .am { font-size: 11px; color: var(--slate-400, #94a3b8); margin-top: 2px; }
    .jur-and-time { font-size: 10px; color: var(--slate-400, #94a3b8); white-space: nowrap; margin-top: 2px; }

    /* Audiências */
    .jur-aud-dbox {
        border-radius: 6px; padding: 6px 8px; text-align: center;
        min-width: 40px; flex-shrink: 0;
        background: var(--slate-50, #f8fafc);
    }
    .dark .jur-aud-dbox { background: rgba(255,255,255,.06); }
    .jur-aud-dbox.crit { background: #FCEBEB; }
    .dark .jur-aud-dbox.crit { background: #501313; }
    .jur-aud-dbox .ad { font-size: 16px; font-weight: 700; line-height: 1; color: var(--slate-800, #1e293b); }
    .dark .jur-aud-dbox .ad { color: #f1f5f9; }
    .jur-aud-dbox.crit .ad { color: #A32D2D; }
    .dark .jur-aud-dbox.crit .ad { color: #F09595; }
    .jur-aud-dbox .am { font-size: 9px; text-transform: uppercase; color: var(--slate-400, #94a3b8); }
    .jur-aud-dbox.crit .am { color: #A32D2D; }
    .dark .jur-aud-dbox.crit .am { color: #F09595; }

    /* Tabela de processos */
    .jur-tab-bar { display: flex; gap: 0; border-bottom: 0.5px solid var(--border-color, #e2e8f0); padding: 0 1rem; overflow-x: auto; }
    .jur-tab {
        font-size: 12px; padding: .6rem .9rem; white-space: nowrap;
        color: var(--slate-400, #94a3b8); cursor: pointer;
        border-bottom: 2px solid transparent; margin-bottom: -0.5px;
        text-decoration: none; display: inline-block;
    }
    .jur-tab:hover { color: #534AB7; }
    .jur-tab.active { color: #534AB7; border-bottom-color: #534AB7; font-weight: 600; }

    /* Tags de status/tipo */
    .jur-tag { font-size: 10px; font-weight: 600; padding: 2px 8px; border-radius: 20px; white-space: nowrap; display: inline-block; }
    .jur-tag.t-purple { background: #EEEDFE; color: #534AB7; }
    .jur-tag.t-green  { background: #EAF3DE; color: #3B6D11; }
    .jur-tag.t-amber  { background: #FAEEDA; color: #854F0B; }
    .jur-tag.t-blue   { background: #E6F1FB; color: #185FA5; }
    .jur-tag.t-red    { background: #FCEBEB; color: #A32D2D; }
    .jur-tag.t-gray   { background: var(--slate-100, #f1f5f9); color: var(--slate-500, #64748b); }
    .dark .jur-tag.t-purple { background: #26215C; color: #AFA9EC; }
    .dark .jur-tag.t-green  { background: #173404; color: #97C459; }
    .dark .jur-tag.t-amber  { background: #412402; color: #FAC775; }
    .dark .jur-tag.t-blue   { background: #042C53; color: #85B7EB; }
    .dark .jur-tag.t-red    { background: #501313; color: #F09595; }
    .dark .jur-tag.t-gray   { background: rgba(255,255,255,.08); color: #94a3b8; }

    .jur-proc-num { font-family: monospace; font-size: 11px; color: var(--slate-400, #94a3b8); }
    .jur-empty-state { text-align: center; padding: 3rem 1rem; color: var(--slate-400, #94a3b8); font-style: italic; }

    /* Botões de ação na tabela */
    .jur-action-btn {
        background: none; border: none; cursor: pointer;
        color: var(--slate-400, #94a3b8); padding: 4px;
        border-radius: 4px; font-size: 18px; line-height: 1;
        transition: color .15s;
    }
    .jur-action-btn:hover { color: #534AB7; }

    /* Seção 2 colunas bottom */
    .jur-bottom-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }

    /* Responsivo */
    @media (max-width: 640px) {
        .jur-two-col, .jur-bottom-grid { grid-template-columns: 1fr; }
        .jur-kpi-grid { grid-template-columns: 1fr 1fr; }
    }
</style>

<div class="jur-root">

    <!-- ── Cabeçalho ─────────────────────────────────────────────── -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white tracking-tight">Dashboard Jurídico</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">
                Controle de processos, prazos fatais e diligências —
                atualizado em <?= date('d/m/Y \à\s H:i', strtotime($dataAtualizacao)) ?>
            </p>
        </div>
        <div class="flex items-center gap-3 flex-wrap md:flex-nowrap">
            <?php if (has_permission('juridico_processos_view')) : ?>
                <a href="<?= BASE_URL ?>/juridico/relatorios" class="px-4 py-2.5 h-[42px] bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-50 transition-all flex items-center gap-2 shadow-sm">
                    <i class='bx bx-download text-lg'></i> Exportar
                </a>
            <?php endif; ?>
            <?php if (has_permission('juridico_processos_manage')) : ?>
                <a href="<?= BASE_URL ?>/juridico/processos/novo" class="px-5 py-2.5 h-[42px] bg-purple-600 hover:bg-purple-700 text-white rounded-xl text-sm font-bold shadow-lg shadow-purple-500/20 transition-all flex items-center gap-2 whitespace-nowrap">
                    <i class='bx bx-plus text-xl'></i> Novo Processo
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- ── Alerta de prazo fatal (exibido apenas se houver) ───────── -->
    <?php if ($prazosHoje > 0) : ?>
        <div class="jur-alert">
            <i class='bx bx-error-alt'></i>
            <span>
                <strong><?= $prazosHoje ?> <?= $prazosHoje === 1 ? 'prazo fatal vence' : 'prazos fatais vencem' ?></strong>
                hoje — ação imediata necessária.
                <a href="<?= BASE_URL ?>/juridico/prazos?filtro=hoje">Ver detalhes →</a>
            </span>
        </div>
    <?php endif; ?>

    <!-- ── KPIs ──────────────────────────────────────────────────── -->
    <div class="jur-kpi-grid">

        <div class="sys-card jur-kpi kpi-purple">
            <label>Processos Ativos</label>
            <div class="jur-val"><?= number_format($kpis['processos_ativos']) ?></div>
            <div class="jur-sub <?= $kpis['processos_novos_mes'] > 0 ? 'up' : '' ?>">
                <i class='bx bx-trending-up'></i>
                +<?= $kpis['processos_novos_mes'] ?> este mês
            </div>
        </div>

        <div class="sys-card jur-kpi kpi-red">
            <label>Prazos Críticos (7d)</label>
            <div class="jur-val"><?= count($prazosProximos) ?></div>
            <div class="jur-sub <?= $prazosHoje > 0 ? 'down' : '' ?>">
                <i class='bx bx-error-circle'></i>
                <?= $prazosHoje ?> vencem hoje
            </div>
        </div>

        <div class="sys-card jur-kpi kpi-amber">
            <label>Audiências no Mês</label>
            <div class="jur-val"><?= number_format($kpis['audiencias_mes']) ?></div>
            <div class="jur-sub">
                <i class='bx bx-calendar'></i>
                <?= $kpis['audiencias_semana'] ?> esta semana
            </div>
        </div>

        <div class="sys-card jur-kpi kpi-green">
            <label>Encerrados (Ano)</label>
            <div class="jur-val"><?= number_format($kpis['encerrados_ano']) ?></div>
            <div class="jur-sub up">
                <i class='bx bx-check-circle'></i>
                <?= $kpis['taxa_exito'] ?>% favoráveis
            </div>
        </div>

        <div class="sys-card jur-kpi kpi-blue">
            <label>Diligências Abertas</label>
            <div class="jur-val"><?= number_format($kpis['diligencias_abertas']) ?></div>
            <div class="jur-sub <?= $kpis['diligencias_atrasadas'] > 0 ? 'down' : '' ?>">
                <i class='bx bx-time'></i>
                <?= $kpis['diligencias_atrasadas'] ?> atrasadas
            </div>
        </div>

    </div>

    <!-- ── Prazos + Distribuição / Responsáveis ───────────────────── -->
    <div class="jur-two-col">

        <!-- Agenda de prazos (7 dias) -->
        <div class="sys-card !p-0 overflow-hidden">
            <div class="p-4 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
                <h3 class="text-sm font-bold text-slate-800 dark:text-white flex items-center gap-2">
                    <i class='bx bxs-time-five text-red-500'></i>
                    Agenda de Prazos — 7 dias
                </h3>
                <a href="<?= BASE_URL ?>/juridico/prazos" class="text-xs font-semibold text-purple-600 hover:underline">
                    Ver todos
                </a>
            </div>

            <?php if (empty($prazosProximos)) : ?>
                <div class="jur-empty-state">
                    <i class='bx bx-info-circle text-2xl block mb-2'></i>
                    Nenhum prazo crítico nos próximos 7 dias.
                </div>
            <?php else : ?>
                <?php foreach ($prazosProximos as $prazo) :
                    $diasRestantes = (int) ceil((strtotime($prazo['data_prazo']) - time()) / 86400);
                    $dateClass  = $diasRestantes <= 0 ? 'crit' : ($diasRestantes <= 2 ? 'crit' : ($diasRestantes <= 4 ? 'warn' : ''));
                    $badgeClass = $diasRestantes <= 0 ? 'd-crit' : ($diasRestantes <= 2 ? 'd-crit' : ($diasRestantes <= 4 ? 'd-warn' : 'd-ok'));
                    $badgeLabel = $diasRestantes <= 0 ? 'Hoje' : ($diasRestantes === 1 ? '1 dia' : "{$diasRestantes} dias");
                    $dataTs = strtotime($prazo['data_prazo']);
                    $monPt = $meses_abrev[date('M', $dataTs)] ?? date('M', $dataTs);
                ?>
                    <div class="jur-prazo-row">
                        <div class="jur-date-box <?= $dateClass ?>">
                            <span class="jur-day"><?= date('d', $dataTs) ?></span>
                            <span class="jur-mon"><?= $monPt ?></span>
                        </div> 
                        <div class="jur-prazo-meta" style="min-width:0">
                            <div class="pt"><?= htmlspecialchars($prazo['descricao'] ?? 'Sem descrição') ?></div>
                            <div class="ps">
                                <?= htmlspecialchars($prazo['numero_processo'] ?? $prazo['numero_cnj'] ?? 'N/A') ?>
                                <?php if (!empty($prazo['vara'])) : ?>
                                    · <?= htmlspecialchars($prazo['vara']) ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <span class="jur-dias <?= $badgeClass ?>"><?= $badgeLabel ?></span>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Coluna direita: Distribuição + Responsáveis -->
        <div class="jur-col-right">

            <!-- Distribuição por tipo -->
            <div class="sys-card !p-0 overflow-hidden">
                <div class="p-4 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-slate-800 dark:text-white flex items-center gap-2">
                        <i class='bx bx-pie-chart-alt-2 text-purple-600'></i>
                        Distribuição por Tipo
                    </h3>
                </div>
                <div class="p-4">
                    <?php
                    $coresDistribuicao = [
                        'Cível'          => '#534AB7',
                        'Trabalhista'    => '#1D9E75',
                        'Tributário'     => '#BA7517',
                        'Administrativo' => '#185FA5',
                        'Regulatório'    => '#D85A30',
                    ];
                    $distValues = array_column($distribuicaoTipos, 'total');
                    $maxDist = !empty($distValues) ? max($distValues) : 0;
                    foreach ($distribuicaoTipos as $tipo) :
                        $pct  = $maxDist > 0 ? round(($tipo['total'] / $maxDist) * 100) : 0;
                        $cor  = $coresDistribuicao[$tipo['tipo']] ?? '#888';
                    ?>
                        <div class="jur-bar-row">
                            <span class="jur-bar-label"><?= htmlspecialchars($tipo['tipo']) ?></span>
                            <div class="jur-bar-track">
                                <div class="jur-bar-fill" style="width:<?= $pct ?>%;background:<?= $cor ?>"></div>
                            </div>
                            <span class="jur-bar-count"><?= $tipo['total'] ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Por responsável -->
            <div class="sys-card !p-0 overflow-hidden">
                <div class="p-4 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-slate-800 dark:text-white flex items-center gap-2">
                        <i class='bx bx-group text-purple-600'></i>
                        Por Responsável
                    </h3>
                    <a href="<?= BASE_URL ?>/juridico/equipe" class="text-xs font-semibold text-purple-600 hover:underline">Ver carga</a>
                </div>
                <div class="p-4">
                    <?php
                    $coresResp   = ['#534AB7','#1D9E75','#BA7517','#185FA5','#D85A30'];
                    $bgResp      = ['#EEEDFE','#E1F5EE','#FAEEDA','#E6F1FB','#FAECE7'];
                    $txtResp     = ['#534AB7','#0F6E56','#854F0B','#185FA5','#993C1D'];
                    $respValues  = array_column($responsaveis, 'total_processos');
                    $maxResp     = !empty($respValues) ? max($respValues) : 0;
                    foreach ($responsaveis as $i => $resp) :
                        $pctResp = $maxResp > 0 ? round(($resp['total_processos'] / $maxResp) * 100) : 0;
                        $cIdx    = $i % count($coresResp);
                        $iniciais = implode('', array_map(fn($p) => strtoupper(substr($p, 0, 1)),
                                        array_slice(explode(' ', $resp['nome']), 0, 2)));
                    ?>
                        <div class="jur-resp-row">
                            <div class="jur-avatar" style="background:<?= $bgResp[$cIdx] ?>;color:<?= $txtResp[$cIdx] ?>">
                                <?= $iniciais ?>
                            </div>
                            <div class="jur-resp-info">
                                <div class="rn"><?= htmlspecialchars($resp['nome_abreviado'] ?? $resp['nome']) ?></div>
                                <div class="rc"><?= htmlspecialchars($resp['cargo']) ?></div>
                            </div>
                            <div class="jur-minibar-track">
                                <div class="jur-minibar-fill" style="width:<?= $pctResp ?>%;background:<?= $coresResp[$cIdx] ?>"></div>
                            </div>
                            <span class="jur-resp-count"><?= $resp['total_processos'] ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

        </div>
    </div>

    <!-- ── Tabela de Processos Ativos ─────────────────────────────── -->
    <div class="sys-card !p-0 overflow-hidden">
        <div class="p-4 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-800 dark:text-white flex items-center gap-2">
                <i class='bx bx-file-blank text-purple-600'></i>
                Processos Ativos
            </h3>
            <a href="<?= BASE_URL ?>/juridico/processos" class="text-xs font-semibold text-purple-600 hover:underline">
                Ver todos os processos
            </a>
        </div>

        <!-- Tabs de filtro por tipo -->
        <div class="jur-tab-bar">
            <?php
            $tabs = ['todos' => "Todos ({$kpis['processos_ativos']})"] + array_column($distribuicaoTipos, 'total', 'tipo');
            // Reconstrói com label formatado
            $tabLinks = ['todos' => "Todos ({$kpis['processos_ativos']})"];
            foreach ($distribuicaoTipos as $dt) {
                $tabLinks[strtolower($dt['tipo'])] = "{$dt['tipo']} ({$dt['total']})";
            }
            foreach ($tabLinks as $slug => $label) :
                $active = ($tipoFiltro === $slug) ? 'active' : '';
            ?>
                <a href="<?= BASE_URL ?>/juridico?tipo=<?= $slug ?>"
                   class="jur-tab <?= $active ?>">
                    <?= htmlspecialchars($label) ?>
                </a>
            <?php endforeach; ?>
        </div>

        <div class="overflow-x-auto">
            <table class="sys-table">
                <thead>
                    <tr>
                        <th>Número do Processo</th>
                        <th>Parte Adversa</th>
                        <th>Tipo</th>
                        <th>Vara / Tribunal</th>
                        <th>Próximo Prazo</th>
                        <th>Fase</th>
                        <th>Responsável</th>
                        <th class="text-center w-20">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($processos)) : ?>
                        <tr>
                            <td colspan="8" class="jur-empty-state">
                                <i class='bx bx-info-circle text-2xl block mb-2'></i>
                                Nenhum processo encontrado para o filtro selecionado.
                            </td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($processos as $proc) :
                            // Mapeamento de tipo → classe de tag
                            $tipoTagMap = [
                                'Cível'          => 't-purple',
                                'Trabalhista'     => 't-green',
                                'Tributário'      => 't-amber',
                                'Administrativo'  => 't-blue',
                                'Regulatório'     => 't-red',
                            ];
                            $faseTagMap = [
                                'Conhecimento'   => 't-blue',
                                'Instrução'      => 't-blue',
                                'Contestação'    => 't-amber',
                                'Perícia'        => 't-amber',
                                'Recurso'        => 't-red',
                                'Execução'       => 't-purple',
                                'Encerrado'      => 't-gray',
                            ];
                            $tipoClass = $tipoTagMap[$proc['tipo']] ?? 't-gray';
                            $faseClass = $faseTagMap[$proc['fase']] ?? 't-gray';

                            // Badge de prazo
                            $prazoLabel = '—';
                            $prazoClass = '';
                            if (!empty($proc['proximo_prazo'])) {
                                $diasP = (int) ceil((strtotime($proc['proximo_prazo']) - time()) / 86400);
                                $prazoClass = $diasP <= 0 ? 'd-crit' : ($diasP <= 2 ? 'd-crit' : ($diasP <= 4 ? 'd-warn' : 'd-ok'));
                                $prazoLabel = $diasP <= 0 ? 'Hoje' : ($diasP === 1 ? '1 dia' : "{$diasP} dias");
                            }
                        ?>
                            <tr class="hover:bg-slate-50 dark:hover:bg-white/5 transition-colors">
                                <td>
                                    <span class="jur-proc-num"><?= htmlspecialchars($proc['numero_cnj'] ?? 'N/A') ?></span>
                                </td>
                                <td class="text-sm text-slate-700 dark:text-slate-300">
                                    <?= htmlspecialchars($proc['parte_adversa'] ?? 'N/A') ?>
                                </td>
                                <td>
                                    <span class="jur-tag <?= $tipoClass ?>"><?= htmlspecialchars($proc['tipo'] ?? 'N/A') ?></span>
                                </td>
                                <td class="text-xs text-slate-500 dark:text-slate-400">
                                    <?= htmlspecialchars($proc['vara'] ?? 'N/A') ?>
                                </td>
                                <td>
                                    <?php if (!empty($proc['proximo_prazo'])) : ?>
                                        <span class="jur-dias <?= $prazoClass ?>"><?= $prazoLabel ?></span>
                                    <?php else : ?>
                                        <span class="text-xs text-slate-400">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="jur-tag <?= $faseClass ?>"><?= htmlspecialchars($proc['fase'] ?? 'N/A') ?></span>
                                </td>
                                <td class="text-sm text-slate-600 dark:text-slate-400">
                                    <?= htmlspecialchars($proc['responsavel_nome'] ?? 'N/A') ?>
                                </td>
                                <td class="text-center">
                                    <?php if (has_permission('juridico_processos_view')) : ?>
                                        <a href="<?= BASE_URL ?>/juridico/detalhe/<?= $proc['id'] ?>"
                                           class="jur-action-btn" title="Ver processo">
                                            <i class='bx bx-right-arrow-alt'></i>
                                        </a>
                                    <?php endif; ?>
                                    <?php if (has_permission('juridico_processos_manage')) : ?>
                                        <a href="<?= BASE_URL ?>/juridico/editar/<?= $proc['id'] ?>"
                                           class="jur-action-btn" title="Editar">
                                            <i class='bx bx-edit-alt'></i>
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ── Andamentos recentes + Próximas audiências ──────────────── -->
    <div class="jur-bottom-grid">

        <!-- Andamentos recentes -->
        <div class="sys-card !p-0 overflow-hidden">
            <div class="p-4 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
                <h3 class="text-sm font-bold text-slate-800 dark:text-white flex items-center gap-2">
                    <i class='bx bx-pulse text-purple-600'></i>
                    Andamentos Recentes
                </h3>
                <a href="<?= BASE_URL ?>/juridico/andamentos" class="text-xs font-semibold text-purple-600 hover:underline">
                    Ver histórico
                </a>
            </div>

            <?php if (empty($andamentosRecentes)) : ?>
                <div class="jur-empty-state">Nenhum andamento recente.</div>
            <?php else : ?>
                <?php
                $iconeAndamento = [
                    'sentenca'    => ['ic-red',    'bx-gavel'],
                    'acordo'      => ['ic-green',  'bx-check'],
                    'audiencia'   => ['ic-amber',  'bx-calendar-event'],
                    'peticao'     => ['ic-purple', 'bx-file-blank'],
                    'intimacao'   => ['ic-blue',   'bx-envelope'],
                    'recurso'     => ['ic-red',    'bx-redo'],
                    'despacho'    => ['ic-blue',   'bx-mail-send'],
                    'default'     => ['ic-purple', 'bx-file'],
                ];
                foreach ($andamentosRecentes as $and) :
                    $tipoAnd = strtolower($and['tipo_andamento'] ?? 'default');
                    [$icClass, $icName] = $iconeAndamento[$tipoAnd] ?? $iconeAndamento['default'];
                    $tsAnd = strtotime($and['criado_em']);
                    $hoje  = strtotime('today');
                    $ontem = strtotime('yesterday');
                    if ($tsAnd >= $hoje)       $tempoLabel = 'hoje';
                    elseif ($tsAnd >= $ontem)  $tempoLabel = 'ontem';
                    else                       $tempoLabel = date('d/m', $tsAnd);
                ?>
                    <div class="jur-and-row">
                        <div class="jur-and-dot <?= $icClass ?>">
                            <i class='bx <?= $icName ?>' style="font-size:14px"></i>
                        </div>
                        <div class="jur-and-body">
                            <div class="at"><?= htmlspecialchars($and['descricao']) ?></div>
                            <div class="am"><?= htmlspecialchars($and['complemento'] ?? '') ?></div>
                        </div>
                        <div class="jur-and-time"><?= $tempoLabel ?></div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Próximas audiências -->
        <div class="sys-card !p-0 overflow-hidden">
            <div class="p-4 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
                <h3 class="text-sm font-bold text-slate-800 dark:text-white flex items-center gap-2">
                    <i class='bx bx-calendar-check text-purple-600'></i>
                    Próximas Audiências
                </h3>
                <a href="<?= BASE_URL ?>/juridico/agenda" class="text-xs font-semibold text-purple-600 hover:underline">
                    Agenda completa
                </a>
            </div>

            <?php if (empty($audiencias)) : ?>
                <div class="jur-empty-state">Nenhuma audiência agendada.</div>
            <?php else : ?>
                <?php foreach ($audiencias as $aud) :
                    $tsAud   = strtotime($aud['data_audiencia']);
                    $diasAud = (int) ceil(($tsAud - time()) / 86400);
                    $isHoje  = $diasAud <= 0;
                    $badgeA  = $isHoje ? 'Hoje' : ($diasAud === 1 ? '1 dia' : "{$diasAud} dias");
                    $badgeCl = $isHoje ? 't-red' : ($diasAud <= 3 ? 't-amber' : 't-gray');
                    $monPtA = $meses_abrev[date('M', $tsAud)] ?? date('M', $tsAud);
                ?>
                    <div class="jur-and-row">
                        <div class="jur-aud-dbox <?= $isHoje ? 'crit' : '' ?>">
                            <div class="ad"><?= date('d', $tsAud) ?></div>
                            <div class="am"><?= $monPtA ?></div>
                        </div>
                        <div class="jur-and-body">
                            <div class="at"><?= htmlspecialchars($aud['tipo_audiencia']) ?></div>
                            <div class="am">
                                <?= date('H:i', $tsAud) ?> · <?= htmlspecialchars($aud['vara']) ?> · <?= htmlspecialchars($aud['responsavel_nome']) ?>
                            </div>
                        </div>
                        <span class="jur-tag <?= $badgeCl ?>"><?= $badgeA ?></span>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </div>

</div>
