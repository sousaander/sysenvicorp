<?php
$kpis = $kpis ?? ['total' => 0, 'valor_total' => 0, 'em_andamento' => 0, 'finalizadas' => 0, 'abertas' => 0, 'suspensas' => 0];
$volumeMensal = $volumeMensal ?? array_fill(0, 12, 0); 
$aiConfig = $aiConfig ?? ['ativo' => 0];
$ultimasCaptacoes = $ultimasCaptacoes ?? [];
$totalCaptadas = $totalCaptadas ?? 0;
$contagemCaptacoesIA = $contagemCaptacoesIA ?? 0;
$statusCount = [
    'Aberto'    => $kpis['abertas'] ?? 0,
    'Andamento' => $kpis['em_andamento'] ?? 0,
    'Concluído' => $kpis['finalizadas'],
    'Suspenso'  => $kpis['suspensas'] ?? 0
];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard · Módulo de Licitações</title>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --bg-base:      #f0f4fa;
            --bg-surface:   #ffffff;
            --bg-elevated:  #f8fafc;
            --bg-overlay:   #ffffff;
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

            --radius-sm:     6px;
            --radius-md:     10px;
            --radius-lg:     14px;
            --radius-xl:     20px;
            --font-ui:      'Sora', system-ui, sans-serif;
            --font-mono:    'JetBrains Mono', monospace;
            --transition:   all 0.22s cubic-bezier(0.4,0,0.2,1);
        }
        body.dark-theme {
            --bg-base:      #0b0f1a;
            --bg-surface:   #111827;
            --bg-elevated:  #1a2235;
            --bg-overlay:   #1f2d44;
            --border-soft:  rgba(255,255,255,0.06);
            --border-mid:   rgba(255,255,255,0.12);
            --border-accent:rgba(56,189,248,0.35);

            --text-primary:  #f0f6ff;
            --text-secondary:#8fa3c0;
            --text-tertiary: #4d6282;

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

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        html { font-size: 14px; }

        body {
            background: var(--bg-base);
            color: var(--text-primary);
            font-family: var(--font-ui);
            line-height: 1.6;
            min-height: 100vh;
        }
        /* ... (restante do CSS) ... */
        /* ─────────── SCROLLBAR ─────────── */
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: var(--border-mid); border-radius: 99px; }
        ::-webkit-scrollbar-thumb:hover { background: var(--accent); }

        /* ─────────── LAYOUT ─────────── */
        .page { max-width: 1480px; margin: 0 auto; padding: 28px 32px 48px; }

        /* ─────────── HEADER ─────────── */
        .header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 20px;
            flex-wrap: wrap;
            margin-bottom: 28px;
        }

        .header-left { display: flex; flex-direction: column; gap: 6px; }

        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 11.5px;
            color: var(--text-tertiary);
            letter-spacing: 0.4px;
        }
        .breadcrumb a { color: var(--accent); text-decoration: none; transition: var(--transition); }
        .breadcrumb a:hover { color: var(--accent-hover); }
        .breadcrumb-sep { opacity: 0.35; }

        .header-title {
            font-size: 22px;
            font-weight: 700;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 10px;
            letter-spacing: -0.3px;
        }
        .header-title .icon-gavel {
            width: 36px; height: 36px;
            background: var(--accent-dim);
            border: 1px solid var(--border-accent);
            border-radius: var(--radius-md);
            display: flex; align-items: center; justify-content: center;
            color: var(--accent);
            font-size: 15px;
            flex-shrink: 0;
        }

        .version-badge {
            display: inline-flex;
            align-items: center;
            padding: 2px 9px;
            background: var(--accent-dim);
            border: 1px solid var(--border-accent);
            border-radius: 99px;
            font-size: 10.5px;
            font-weight: 600;
            color: var(--accent);
            letter-spacing: 0.3px;
        }

        .header-sub { font-size: 12.5px; color: var(--text-secondary); }

        .header-actions { display: flex; gap: 10px; flex-wrap: wrap; align-items: center; }

        /* ─────────── BUTTONS ─────────── */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 9px 18px;
            border-radius: var(--radius-md);
            font-family: var(--font-ui);
            font-size: 12.5px;
            font-weight: 500;
            border: 1px solid transparent;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
            white-space: nowrap;
        }
        .btn-ghost {
            background: transparent;
            border-color: var(--border-mid);
            color: var(--text-secondary);
        }
        .btn-ghost:hover {
            background: var(--bg-elevated);
            border-color: var(--accent);
            color: var(--accent);
        }
        .btn-primary {
            background: var(--accent);
            border-color: var(--accent);
            color: #0b0f1a;
            font-weight: 600;
        }
        .btn-primary:hover {
            background: var(--accent-hover);
            box-shadow: 0 4px 18px rgba(56,189,248,0.3);
            transform: translateY(-1px);
        }
        .btn-ai {
            background: linear-gradient(135deg, rgba(167,139,250,0.15), rgba(56,189,248,0.12));
            border-color: rgba(167,139,250,0.3);
            color: var(--purple);
        }
        .btn-ai:hover {
            background: linear-gradient(135deg, rgba(167,139,250,0.25), rgba(56,189,248,0.18));
            border-color: var(--purple);
        }
        .btn-sm { padding: 6px 13px; font-size: 11.5px; }

        /* ─────────── NAV TABS ─────────── */
        .nav-tabs-wrapper {
            display: flex;
            align-items: center;
            gap: 2px;
            background: var(--bg-surface);
            border: 1px solid var(--border-soft);
            border-radius: var(--radius-lg);
            padding: 4px;
            margin-bottom: 28px;
            overflow-x: auto;
        }
        .nav-tab {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: var(--radius-md);
            font-size: 12.5px;
            font-weight: 500;
            color: var(--text-secondary);
            text-decoration: none;
            transition: var(--transition);
            white-space: nowrap;
            border: 1px solid transparent;
        }
        .nav-tab:hover {
            color: var(--text-primary);
            background: var(--bg-elevated);
        }
        .nav-tab.active {
            background: var(--bg-elevated);
            color: var(--accent);
            border-color: var(--border-accent);
        }
        .nav-tab .pulse-dot {
            width: 6px; height: 6px;
            background: var(--green);
            border-radius: 50%;
            animation: pulse-anim 2s infinite;
        }

        /* ─────────── KPI CARDS ─────────── */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 14px;
            margin-bottom: 24px;
        }

        .kpi-card {
            background: var(--bg-surface);
            border: 1px solid var(--border-soft);
            border-radius: var(--radius-lg);
            padding: 20px 22px;
            position: relative;
            overflow: hidden;
            transition: var(--transition);
            cursor: pointer;
        }
        .kpi-card::before {
            content: '';
            position: absolute;
            inset: 0;
            opacity: 0;
            background: radial-gradient(circle at 0% 0%, var(--accent-glow), transparent 65%);
            transition: opacity 0.4s ease;
            pointer-events: none;
        }
        .kpi-card:hover { border-color: var(--border-accent); transform: translateY(-3px); }
        .kpi-card:hover::before { opacity: 1; }

        .kpi-top { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 14px; }

        .kpi-label {
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: var(--text-tertiary);
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .kpi-label i { font-size: 12px; }

        .kpi-badge {
            font-size: 10.5px;
            font-weight: 600;
            padding: 3px 8px;
            border-radius: 99px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .badge-up   { background: var(--green-bg); color: var(--green); }
        .badge-down { background: var(--red-bg); color: var(--red); }

        .kpi-value {
            font-family: var(--font-mono);
            font-size: 28px;
            font-weight: 600;
            line-height: 1;
            letter-spacing: -1px;
        }
        .kpi-value.accent { color: var(--accent); }
        .kpi-value.green  { color: var(--green); }
        .kpi-value.amber  { color: var(--amber); }
        .kpi-value.purple { color: var(--purple); }

        .kpi-footer {
            margin-top: 12px;
            padding-top: 12px;
            border-top: 1px solid var(--border-soft);
            font-size: 11.5px;
            color: var(--text-tertiary);
        }

        .kpi-progress {
            height: 2px;
            background: var(--border-soft);
            border-radius: 99px;
            overflow: hidden;
            margin-top: 10px;
        }
        .kpi-progress-bar {
            height: 100%;
            border-radius: 99px;
            background: var(--accent);
            transition: width 1s ease;
        }

        /* ─────────── CHARTS GRID ─────────── */
        .charts-grid {
            display: grid;
            grid-template-columns: 1fr 340px;
            gap: 14px;
            margin-bottom: 24px;
        }

        .panel {
            background: var(--bg-surface);
            border: 1px solid var(--border-soft);
            border-radius: var(--radius-lg);
            overflow: hidden;
            transition: var(--transition);
        }
        .panel:hover { border-color: var(--border-mid); }

        .panel-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 18px 22px;
            border-bottom: 1px solid var(--border-soft);
        }
        .panel-title {
            font-size: 13px;
            font-weight: 600;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .panel-title i { color: var(--accent); font-size: 13px; }

        .panel-body { padding: 20px 22px; }

        .btn-group { display: flex; gap: 4px; }
        .btn-tab {
            padding: 5px 12px;
            font-size: 11.5px;
            font-weight: 500;
            border-radius: var(--radius-sm);
            border: 1px solid var(--border-soft);
            background: transparent;
            color: var(--text-tertiary);
            cursor: pointer;
            transition: var(--transition);
            font-family: var(--font-ui);
        }
        .btn-tab.active, .btn-tab:hover {
            background: var(--accent-dim);
            border-color: var(--border-accent);
            color: var(--accent);
        }

        /* ─────────── AI PANEL ─────────── */
        .ai-panel {
            background: linear-gradient(135deg, var(--bg-surface) 0%, #151c2e 100%);
            border: 1px solid rgba(167,139,250,0.2);
            border-radius: var(--radius-lg);
            padding: 22px 26px;
            margin-bottom: 24px;
            position: relative;
            overflow: hidden;
        }
        .ai-panel::before {
            content: '';
            position: absolute;
            top: -60px; right: -60px;
            width: 200px; height: 200px;
            background: radial-gradient(circle, rgba(167,139,250,0.08), transparent 70%);
            pointer-events: none;
        }

        .ai-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 18px;
            flex-wrap: wrap;
            gap: 12px;
        }

        .ai-title-group { display: flex; align-items: center; gap: 12px; }

        .ai-icon {
            width: 44px; height: 44px;
            background: var(--purple-bg);
            border: 1px solid rgba(167,139,250,0.25);
            border-radius: var(--radius-md);
            display: flex; align-items: center; justify-content: center;
            font-size: 18px; color: var(--purple);
            flex-shrink: 0;
        }

        .ai-name { font-size: 14.5px; font-weight: 600; color: var(--text-primary); }
        .ai-meta {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 11.5px;
            color: var(--text-tertiary);
            margin-top: 2px;
        }
        .status-online {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 2px 8px;
            background: var(--green-bg);
            border: 1px solid rgba(52,211,153,0.2);
            border-radius: 99px;
            font-size: 10.5px;
            font-weight: 600;
            color: var(--green);
        }
        .status-online::before {
            content: '';
            width: 5px; height: 5px;
            background: var(--green);
            border-radius: 50%;
            animation: pulse-anim 2s infinite;
        }

        .ai-features {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
        }

        .ai-feat {
            background: rgba(255,255,255,0.03);
            border: 1px solid var(--border-soft);
            border-left: 3px solid rgba(167,139,250,0.4);
            border-radius: var(--radius-md);
            padding: 12px 14px;
            transition: var(--transition);
        }
        .ai-feat:hover {
            background: var(--purple-bg);
            border-left-color: var(--purple);
            transform: translateX(3px);
        }
        .ai-feat-title {
            font-size: 11.5px;
            font-weight: 600;
            color: var(--purple);
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .ai-feat-list {
            display: flex;
            flex-direction: column;
            gap: 4px;
            font-size: 11px;
            color: var(--text-secondary);
        }
        .ai-feat-item {
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .ai-feat-item::before {
            content: '';
            width: 4px; height: 4px;
            border-radius: 50%;
            background: var(--green);
            flex-shrink: 0;
        }
        .ai-feat-item.warn::before { background: var(--amber); }

        /* ─────────── FILTERS BAR ─────────── */
        .filters-bar {
            background: var(--bg-surface);
            border: 1px solid var(--border-soft);
            border-radius: var(--radius-lg);
            padding: 16px 20px;
            margin-bottom: 16px;
        }
        .filters-inner {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .input-wrap {
            position: relative;
            flex: 1;
            min-width: 220px;
        }
        .input-wrap .icon {
            position: absolute;
            left: 12px; top: 50%;
            transform: translateY(-50%);
            color: var(--text-tertiary);
            font-size: 12px;
            pointer-events: none;
        }
        .form-input {
            width: 100%;
            background: var(--bg-elevated);
            border: 1px solid var(--border-soft);
            border-radius: var(--radius-md);
            color: var(--text-primary);
            font-family: var(--font-ui);
            font-size: 12.5px;
            padding: 9px 14px 9px 34px;
            outline: none;
            transition: var(--transition);
        }
        .form-input:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px var(--accent-dim);
        }
        .form-input::placeholder { color: var(--text-tertiary); }

        select.form-input { padding-left: 12px; min-width: 180px; cursor: pointer; }

        /* ─────────── TABLE ─────────── */
        .table-panel {
            background: var(--bg-surface);
            border: 1px solid var(--border-soft);
            border-radius: var(--radius-lg);
            overflow: hidden;
        }

        .table-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 18px 22px;
            border-bottom: 1px solid var(--border-soft);
            flex-wrap: wrap;
            gap: 12px;
        }
        .table-title {
            font-size: 13px;
            font-weight: 600;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .table-title i { color: var(--accent); }

        .count-badge {
            display: inline-flex;
            padding: 2px 9px;
            background: var(--bg-elevated);
            border: 1px solid var(--border-mid);
            border-radius: 99px;
            font-size: 11px;
            font-weight: 600;
            color: var(--text-secondary);
        }

        .refresh-info {
            font-size: 11.5px;
            color: var(--text-tertiary);
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .table-wrapper { overflow-x: auto; }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
        }
        table.data-table thead tr {
            background: rgba(255,255,255,0.02);
        }
        table.data-table thead th {
            padding: 12px 16px;
            font-size: 10.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.7px;
            color: var(--text-tertiary);
            border-bottom: 1px solid var(--border-soft);
            white-space: nowrap;
        }
        table.data-table tbody tr {
            border-bottom: 1px solid var(--border-soft);
            transition: var(--transition);
        }
        table.data-table tbody tr:last-child { border-bottom: none; }
        table.data-table tbody tr:hover { background: rgba(56,189,248,0.04); cursor: pointer; }

        table.data-table tbody td {
            padding: 14px 16px;
            vertical-align: middle;
            font-size: 12.5px;
        }

        .td-num {
            font-family: var(--font-mono);
            font-weight: 600;
            color: var(--accent);
            font-size: 12.5px;
        }
        .td-id { font-size: 10.5px; color: var(--text-tertiary); margin-top: 2px; }

        .td-org {
            font-size: 10.5px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            color: var(--text-tertiary);
            margin-bottom: 3px;
        }
        .td-obj { font-size: 12px; color: var(--text-primary); }

        .td-mod { font-size: 12px; color: var(--text-secondary); }

        .td-val {
            font-family: var(--font-mono);
            font-weight: 600;
            color: var(--green);
            text-align: right;
            white-space: nowrap;
        }

        .td-date { font-size: 12px; color: var(--text-secondary); white-space: nowrap; }

        /* Status Badges */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 10px;
            border-radius: 99px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            white-space: nowrap;
        }
        .status-badge::before {
            content: '';
            width: 5px; height: 5px;
            border-radius: 50%;
            background: currentColor;
        }

        .s-rascunho   { background: rgba(77,98,130,0.2); color: #6b7fa0; border: 1px solid rgba(77,98,130,0.25); }
        .s-publicada  { background: rgba(56,189,248,0.12); color: var(--accent); border: 1px solid rgba(56,189,248,0.25); }
        .s-aberta     { background: var(--green-bg); color: var(--green); border: 1px solid rgba(52,211,153,0.25); }
        .s-concluida  { background: rgba(77,98,130,0.15); color: #5d7494; border: 1px solid rgba(77,98,130,0.2); }
        .s-urgente    { background: var(--red-bg); color: var(--red); border: 1px solid rgba(248,113,113,0.25); animation: pulse-anim 2s infinite; }
        .s-em_analise { background: var(--amber-bg); color: var(--amber); border: 1px solid rgba(251,191,36,0.25); }
        .s-homologada { background: var(--purple-bg); color: var(--purple); border: 1px solid rgba(167,139,250,0.25); }
        .s-suspensa   { background: rgba(100,116,139,0.15); color: #94a3b8; border: 1px solid rgba(100,116,139,0.2); }
        .s-cancelada  { background: var(--red-bg); color: var(--red); border: 1px solid rgba(248,113,113,0.2); }

        /* Action Buttons */
        .actions-cell { display: flex; gap: 6px; justify-content: flex-end; }
        .action-btn {
            width: 30px; height: 30px;
            border-radius: var(--radius-sm);
            background: var(--bg-elevated);
            border: 1px solid var(--border-soft);
            color: var(--text-secondary);
            font-size: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
        }
        .action-btn:hover { border-color: var(--border-mid); transform: scale(1.08); }
        .action-btn.view:hover  { color: var(--accent); border-color: rgba(56,189,248,0.4); background: var(--accent-dim); }
        .action-btn.edit:hover  { color: var(--amber); border-color: rgba(251,191,36,0.3); background: var(--amber-bg); }
        .action-btn.trash:hover { color: var(--red); border-color: rgba(248,113,113,0.3); background: var(--red-bg); }

        /* ─────────── PAGINATION ─────────── */
        .table-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 22px;
            border-top: 1px solid var(--border-soft);
            flex-wrap: wrap;
            gap: 12px;
        }
        .pagination-info { font-size: 12px; color: var(--text-tertiary); }

        .pagination { display: flex; gap: 4px; }
        .page-btn {
            min-width: 30px; height: 30px;
            padding: 0 8px;
            border-radius: var(--radius-sm);
            background: transparent;
            border: 1px solid var(--border-soft);
            color: var(--text-secondary);
            font-size: 12px;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-family: var(--font-ui);
        }
        .page-btn:hover { background: var(--bg-elevated); border-color: var(--border-mid); color: var(--text-primary); }
        .page-btn.active { background: var(--accent); border-color: var(--accent); color: #0b0f1a; font-weight: 700; }
        .page-btn:disabled { opacity: 0.3; cursor: not-allowed; }

        /* ─────────── EMPTY STATE ─────────── */
        .empty-state {
            text-align: center;
            padding: 56px 24px;
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

        /* ─────────── ANIMATIONS ─────────── */
        @keyframes pulse-anim {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.45; }
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(16px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .anim-1 { animation: fadeUp 0.5s ease both; }
        .anim-2 { animation: fadeUp 0.5s 0.1s ease both; }
        .anim-3 { animation: fadeUp 0.5s 0.2s ease both; }
        .anim-4 { animation: fadeUp 0.5s 0.3s ease both; }
        .anim-5 { animation: fadeUp 0.5s 0.4s ease both; }
        .anim-6 { animation: fadeUp 0.5s 0.5s ease both; }

        /* ─────────── RESPONSIVE ─────────── */
        @media (max-width: 1200px) {
            .kpi-grid { grid-template-columns: repeat(2, 1fr); }
            .charts-grid { grid-template-columns: 1fr; }
            .ai-features { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 768px) {
            .page { padding: 16px 16px 32px; }
            .kpi-grid { grid-template-columns: 1fr; }
            .ai-features { grid-template-columns: 1fr; }
            .header-title { font-size: 18px; }
        }

        /* ─────────── DIVIDER LINE ─────────── */
        .section-label {
            font-size: 10.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--text-tertiary);
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 14px;
        }
        .section-label::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border-soft);
        }

        /* Donut chart legend */
        .donut-legend {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-top: 14px;
        }
        .donut-legend-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            color: var(--text-secondary);
        }
        .donut-legend-dot {
            width: 8px; height: 8px;
            border-radius: 2px;
            flex-shrink: 0;
        }
        .donut-legend-val {
            margin-left: auto;
            font-family: var(--font-mono);
            font-size: 12px;
            font-weight: 600;
            color: var(--text-primary);
        }
    </style>
</head>
<body>
<div class="page">

    <!-- ── HEADER ── -->
    <div class="header anim-1">
        <div class="header-left">
            <div class="breadcrumb">
                <a href="<?= BASE_URL ?>/licitacoes">Licitações</a>
                <span class="breadcrumb-sep">/</span>
                <span>Dashboard</span>
            </div>
            <h1 class="header-title">
                <span class="icon-gavel"><i class="fas fa-gavel"></i></span>
                Módulo de Licitações
                <span class="version-badge">v1.0</span>
            </h1>
            <p class="header-sub">Gestão inteligente de processos licitatórios · Atualizado em <?= date('d/m/Y \à\s H:i') ?></p>
        </div>
        <div class="header-actions">
            <a href="<?= BASE_URL ?>/licitacoes/agenteIA" class="btn btn-ai">
                <i class="fas fa-robot"></i>Agente IA
            </a>
            <button class="btn btn-ghost" id="btnExportar">
                <i class="fas fa-arrow-down-to-line"></i>Exportar
            </button>
            <a href="<?= BASE_URL ?>/licitacoes/novo" class="btn btn-primary">
                <i class="fas fa-plus"></i>Nova Licitação
            </a>
        </div>
    </div>

    <!-- ── NAV TABS ── -->
    <nav class="nav-tabs-wrapper anim-1">
        <a href="<?= BASE_URL ?>/licitacoes/dashboard" class="nav-tab active"><i class="fas fa-gauge-high"></i>Dashboard</a>
        <a href="<?= BASE_URL ?>/licitacoes/index" class="nav-tab"><i class="fas fa-file-contract"></i>Licitações</a>
        <a href="<?= BASE_URL ?>/licitacoes/captacoes" class="nav-tab">
            <i class="fas fa-bolt"></i>Radar IA 
            <?php if (($contagemCaptacoesIA ?? 0) > 0): ?>
                <span class="pulse-dot"></span>
            <?php endif; ?>
        </a>
        <a href="<?= BASE_URL ?>/licitacoes/editais" class="nav-tab"><i class="fas fa-file-lines"></i>Editais</a>
        <a href="<?= BASE_URL ?>/licitacoes/relatorios" class="nav-tab"><i class="fas fa-chart-column"></i>Relatórios</a>
    </nav>

    <!-- ── SECTION LABEL ── -->
    <div class="section-label anim-2">Indicadores chave</div>

    <!-- ── KPI CARDS ── -->
    <div class="kpi-grid anim-2">
        <div class="kpi-card">
            <div class="kpi-top">
                <div class="kpi-label"><i class="fas fa-layer-group"></i>Total de Licitações</div>
                <span class="kpi-badge badge-up"><i class="fas fa-arrow-up"></i>12%</span>
            </div>
            <div class="kpi-value accent"><?= $kpis['total'] ?></div>
            <div class="kpi-footer">Processos cadastrados no sistema</div>
            <div class="kpi-progress"><div class="kpi-progress-bar" style="width:72%; background:var(--accent);"></div></div>
        </div>

        <div class="kpi-card">
            <div class="kpi-top">
                <div class="kpi-label"><i class="fas fa-coins"></i>Valor Estimado</div>
                <span class="kpi-badge badge-up"><i class="fas fa-arrow-up"></i>8.4%</span>
            </div>
            <div class="kpi-value green" style="font-size:20px; letter-spacing:-0.5px;">
                R$ <?= number_format($kpis['valor_total'], 0, ',', '.') ?>
            </div>
            <div class="kpi-footer">Volume financeiro total</div>
            <div class="kpi-progress"><div class="kpi-progress-bar" style="width:60%; background:var(--green);"></div></div>
        </div>

        <div class="kpi-card">
            <div class="kpi-top">
                <div class="kpi-label"><i class="fas fa-spinner"></i>Em Andamento</div>
                <span class="kpi-badge badge-down"><i class="fas fa-arrow-down"></i>2.1%</span>
            </div>
            <div class="kpi-value amber"><?= $kpis['em_andamento'] ?></div>
            <div class="kpi-footer">Processos ativos no momento</div>
            <div class="kpi-progress"><div class="kpi-progress-bar" style="width:45%; background:var(--amber);"></div></div>
        </div>

        <div class="kpi-card">
            <div class="kpi-top">
                <div class="kpi-label"><i class="fas fa-circle-check"></i>Concluídos (mês)</div>
                <span class="kpi-badge badge-up"><i class="fas fa-arrow-up"></i>15%</span>
            </div>
            <div class="kpi-value purple"><?= $kpis['finalizadas'] ?></div>
            <div class="kpi-footer">Finalizados neste período</div>
            <div class="kpi-progress"><div class="kpi-progress-bar" style="width:55%; background:var(--purple);"></div></div>
        </div>
    </div>

    <!-- ── SECTION LABEL ── -->
    <div class="section-label anim-3">Análise visual</div>

    <!-- ── CHARTS ── -->
    <div class="charts-grid anim-3">
        <div class="panel">
            <div class="panel-header">
                <div class="panel-title"><i class="fas fa-chart-bar"></i>Volume Mensal de Licitações</div>
                <div class="btn-group">
                    <button class="btn-tab active" id="tab2025">2025</button>
                    <button class="btn-tab" id="tab2024">2026</button>
                </div>
            </div>
            <div class="panel-body">
                <div style="position:relative; width:100%; height:220px;">
                    <canvas id="licVolumeChart" role="img" aria-label="Gráfico de barras: volume mensal de licitações em 2025">Dados de volume mensal de licitações.</canvas>
                </div>
            </div>
        </div>

        <div class="panel">
            <div class="panel-header">
                <div class="panel-title"><i class="fas fa-chart-donut"></i>Por Status</div>
            </div>
            <div class="panel-body">
                <div style="position:relative; width:100%; height:180px;">
                    <canvas id="licStatusChart" role="img" aria-label="Gráfico donut: distribuição por status">Distribuição por status das licitações.</canvas>
                </div>
                <div class="donut-legend">
                    <div class="donut-legend-item">
                        <span class="donut-legend-dot" style="background:#34d399;"></span>Aberto
                        <span class="donut-legend-val"><?= $statusCount['Aberto'] ?></span>
                    </div>
                    <div class="donut-legend-item">
                        <span class="donut-legend-dot" style="background:#fbbf24;"></span>Andamento
                        <span class="donut-legend-val"><?= $statusCount['Andamento'] ?></span>
                    </div>
                    <div class="donut-legend-item">
                        <span class="donut-legend-dot" style="background:#38bdf8;"></span>Concluído
                        <span class="donut-legend-val"><?= $statusCount['Concluído'] ?></span>
                    </div>
                    <div class="donut-legend-item">
                        <span class="donut-legend-dot" style="background:#f87171;"></span>Suspenso
                        <span class="donut-legend-val"><?= $statusCount['Suspenso'] ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ── SECTION LABEL ── -->
    <div class="section-label anim-4">Inteligência artificial</div>

    <!-- ── AI PANEL ── -->
    <div class="ai-panel anim-4">
        <div class="ai-header">
            <div class="ai-title-group">
                <div class="ai-icon"><i class="fas fa-robot"></i></div>
                <div>
                    <div class="ai-name">Agente IA Comercial</div>
                    <div class="ai-meta">
                        <?php if ($aiConfig['ativo'] ?? 0): ?>
                            <span class="status-online">Executando</span>
                        <?php else: ?>
                            <span class="status-online" style="background: var(--red-bg); color: var(--red); border-color: rgba(239,68,68,0.2);">Pausado</span>
                        <?php endif; ?>
                        <span>v1.0.0· Engenheiro Neural</span>
                    </div>
                </div>
            </div>
            <a href="<?= BASE_URL ?>/licitacoes/agenteIA" class="btn btn-ai btn-sm">
                <i class="fas fa-sliders"></i>Configurar Agente
            </a>
        </div>
        <div class="ai-features">
            <div class="ai-feat">
                <div class="ai-feat-title"><i class="fas fa-database"></i>Captação Automática</div>
                <div class="ai-feat-list">
                    <?php 
                    $portais = json_decode($aiConfig['portais'] ?? '[]', true) ?: [];
                    if (!empty($portais)): 
                        foreach(array_slice($portais, 0, 2) as $p): ?>
                            <span class="ai-feat-item"><?= strtoupper($p) ?> Ativo</span>
                        <?php endforeach;
                    else: ?>
                        <span class="ai-feat-item">Nenhum portal configurado</span>
                    <?php endif; ?>
                    <span class="ai-feat-item <?= ($aiConfig['ativo'] ?? 0) ? 'warn' : '' ?>">Sync: 5 min</span>
                </div>
            </div>
            <div class="ai-feat">
                <div class="ai-feat-title"><i class="fas fa-bell"></i>Alertas Inteligentes</div>
                <div class="ai-feat-list">
                    <?php if (!empty($ultimasCaptacoes)): ?>
                        <?php foreach ($ultimasCaptacoes as $cap): ?>
                            <span class="ai-feat-item" title="<?= htmlspecialchars($cap['objeto']) ?>">
                                <i class="fas fa-file-import"></i> <?= mb_strimwidth(htmlspecialchars($cap['orgao_externo']), 0, 20, "...") ?>
                            </span>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <span class="ai-feat-item">Nenhuma oportunidade nova</span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="ai-feat">
                <div class="ai-feat-title"><i class="fas fa-brain"></i>Análise de Editais</div>
                <div class="ai-feat-list">
                    <span class="ai-feat-item"><?= $totalCaptadas ?> editais no radar</span>
                    <span class="ai-feat-item">94% de precisão</span>
                    <span class="ai-feat-item">Processamento: 2.3s</span>
                </div>
            </div>
        </div>
    </div>

</div><!-- /page -->

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── Volume Mensal ──
    const volCtx = document.getElementById('licVolumeChart').getContext('2d');
    const volChart = new Chart(volCtx, {
        type: 'bar',
        data: {
            labels: ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'],
            datasets: [{
                label: 'Licitações',
                data: <?= json_encode($volumeMensal) ?>,
                backgroundColor: function(ctx) {
                    const g = ctx.chart.ctx.createLinearGradient(0, 0, 0, 220);
                    g.addColorStop(0, 'rgba(56,189,248,0.85)');
                    g.addColorStop(1, 'rgba(56,189,248,0.2)');
                    return g;
                },
                hoverBackgroundColor: 'rgba(125,211,252,0.95)',
                borderRadius: 6,
                borderSkipped: false,
                barPercentage: 0.6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#1a2235',
                    titleColor: '#38bdf8',
                    bodyColor: '#8fa3c0',
                    borderColor: 'rgba(56,189,248,0.3)',
                    borderWidth: 1,
                    padding: 10,
                    callbacks: {
                        title: items => items[0].label + ' · 2025',
                        label: item => '  ' + item.raw + ' licitações'
                    }
                }
            },
            scales: {
                y: {
                    grid: { color: 'rgba(255,255,255,0.04)', drawBorder: false },
                    ticks: { color: '#4d6282', stepSize: 5, font: { size: 11 } },
                    border: { display: false }
                },
                x: {
                    grid: { display: false },
                    ticks: { color: '#4d6282', font: { size: 11 } },
                    border: { display: false }
                }
            }
        }
    });

    // ── Status Donut ──
    const statCtx = document.getElementById('licStatusChart').getContext('2d');
    new Chart(statCtx, {
        type: 'doughnut',
        data: {
            labels: ['Aberto', 'Andamento', 'Concluído', 'Suspenso'],
            datasets: [{
                data: [<?= $statusCount['Aberto'] ?>, <?= $statusCount['Andamento'] ?>, <?= $statusCount['Concluído'] ?>, <?= $statusCount['Suspenso'] ?>],
                backgroundColor: ['#34d399','#fbbf24','#38bdf8','#f87171'],
                borderWidth: 0,
                hoverOffset: 10,
                spacing: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '72%',
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#1a2235',
                    titleColor: '#38bdf8',
                    bodyColor: '#8fa3c0',
                    borderColor: 'rgba(56,189,248,0.2)',
                    borderWidth: 1
                }
            }
        }
    });

    // ── Export handler (apenas para o dashboard, se houver necessidade) ──
    const btnExportar = document.getElementById('btnExportar');
    if (btnExportar) btnExportar.addEventListener('click', () => {
        alert('📊 Exportação de Dashboard\n\nFormatos disponíveis em breve:\n• Imagem (PNG)\n• PDF');
    });

    // ── Year tabs ──
    document.getElementById('tab2025').addEventListener('click', function() {
        this.classList.add('active');
        document.getElementById('tab2024').classList.remove('active');
        volChart.data.datasets[0].data = <?= json_encode($volumeMensal) ?>;
        volChart.update('active');
    });
    document.getElementById('tab2024').addEventListener('click', function() {
        this.classList.add('active');
        document.getElementById('tab2025').classList.remove('active');
        volChart.data.datasets[0].data = [8,14,6,9,4,7,11,18,22,10,6,3]; // Dados de exemplo para 2026
        volChart.update('active');
    });

    // ── Animação KPI progress bars ──
    document.querySelectorAll('.kpi-progress-bar').forEach(bar => {
        const w = bar.style.width;
        bar.style.width = '0';
        setTimeout(() => { bar.style.width = w; }, 300);
    });

    // ── Som de Notificação para novas captações IA ──
    <?php if (($contagemCaptacoesIA > 0) && ($aiConfig['sound_alerts_enabled'] ?? 1)): ?>
        const SOUND_MAP = {
            'ping': 'https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3',
            'chime': 'https://assets.mixkit.co/active_storage/sfx/2358/2358-preview.mp3',
            'bell': 'https://assets.mixkit.co/active_storage/sfx/2568/2568-preview.mp3'
        };

        const playNotificationSound = () => {
            const soundKey = '<?= $aiConfig['notification_sound'] ?? 'ping' ?>';
            const audio = new Audio(SOUND_MAP[soundKey] || SOUND_MAP['ping']);
            audio.volume = 0.2; // Volume discreto
            audio.play().catch(() => {
                // Fallback: Toca na primeira interação se o autoplay for bloqueado pelo browser
                document.addEventListener('click', () => audio.play(), { once: true });
            });
        };
        setTimeout(playNotificationSound, 1000); // Aguarda 1s para o carregamento visual antes do som
    <?php endif; ?>
});
</script>
</body>
</html>