<?php
$kpis = $kpis ?? ['total' => 0, 'valor_total' => 0, 'em_andamento' => 0, 'finalizadas' => 0];
$licitacoes = $licitacoes ?? [];
$filtros = $filtros ?? ['busca' => '', 'status' => '']; // Mantém os filtros para a listagem

// Os dados de KPIs, volumeMensal e statusCount não são mais necessários aqui,
// pois foram movidos para o dashboard.php.
// Se você ainda precisar de 'total' para a paginação, certifique-se de que o controller
// continue passando $kpis['total'] para esta view.

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
        .s-vencida    { background: rgba(71, 85, 105, 0.15); color: #64748b; border: 1px solid rgba(71, 85, 105, 0.2); }

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
        .action-btn.edit:hover  { color: var(--amber); border-color: rgba(251,191,36,0.3); background: var(--amber-bg); border-width: 1px; }
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
                <span>Listagem</span>
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
            <button class="btn btn-ghost" id="btnExportarListagem">
                <i class="fas fa-arrow-down-to-line"></i>Exportar
            </button>
            <a href="<?= BASE_URL ?>/licitacoes/novo" class="btn btn-primary">
                <i class="fas fa-plus"></i>Nova Licitação
            </a>
        </div>
    </div>

    <!-- ── NAV TABS ── -->
    <nav class="nav-tabs-wrapper anim-1">
        <a href="<?= BASE_URL ?>/licitacoes/dashboard" class="nav-tab"><i class="fas fa-gauge-high"></i>Dashboard</a>
        <a href="<?= BASE_URL ?>/licitacoes/index" class="nav-tab active"><i class="fas fa-file-contract"></i>Listagem</a>
        <a href="<?= BASE_URL ?>/licitacoes/captacoes" class="nav-tab"><i class="fas fa-bolt"></i>Radar IA <span class="pulse-dot"></span></a>
        <a href="<?= BASE_URL ?>/licitacoes/editais" class="nav-tab"><i class="fas fa-file-lines"></i>Editais</a>
        <a href="<?= BASE_URL ?>/licitacoes/relatorios" class="nav-tab"><i class="fas fa-chart-column"></i>Relatórios</a>
    </nav>

    <!-- ── SECTION LABEL ── -->
    <div class="section-label anim-5">Registros</div>

    <!-- ── FILTERS ── -->
    <div class="filters-bar anim-5">
        <form method="GET" action="<?= BASE_URL ?>/licitacoes/index">
            <div class="filters-inner">
                <div class="input-wrap">
                    <i class="fas fa-search icon"></i>
                    <input type="text" name="busca" class="form-input"
                           value="<?= htmlspecialchars($filtros['busca'] ?? '') ?>"
                           placeholder="Número, órgão ou objeto da licitação…">
                </div>
                <select name="status" class="form-input">
                    <option value="">Todos os status</option>
                    <option value="em_andamento" <?= ($filtros['status'] ?? '') == 'em_andamento' ? 'selected' : '' ?>>📌 Em Andamento (Ativas)</option>
                    <option value="rascunho"  <?= $filtros['status']=='rascunho'  ? 'selected':'' ?>>Rascunho</option>
                    <option value="publicada" <?= $filtros['status']=='publicada' ? 'selected':'' ?>>Publicada</option>
                    <option value="aberta"    <?= $filtros['status']=='aberta'    ? 'selected':'' ?>>Aberta</option>
                    <option value="em_analise"<?= $filtros['status']=='em_analise'? 'selected':'' ?>>Em Análise</option>
                    <option value="homologada"<?= $filtros['status']=='homologada'? 'selected':'' ?>>Homologada</option>
                    <option value="suspensa"  <?= $filtros['status']=='suspensa'  ? 'selected':'' ?>>Suspensa</option>
                    <option value="cancelada" <?= $filtros['status']=='cancelada' ? 'selected':'' ?>>Cancelada</option>
                    <option value="concluida" <?= $filtros['status']=='concluida' ? 'selected':'' ?>>Concluída</option>
                    <option value="urgente"   <?= $filtros['status']=='urgente'   ? 'selected':'' ?>>Urgente</option>
                </select>
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="fas fa-filter"></i>Filtrar
                </button>
                <a href="<?= BASE_URL ?>/licitacoes/index" class="btn btn-ghost btn-sm">
                    <i class="fas fa-rotate-left"></i>Limpar
                </a>
                <button type="button" class="btn btn-ghost btn-sm" id="btnExportarListagem2" style="margin-left:auto;">
                    <i class="fas fa-file-arrow-down"></i>Exportar
                </button>
            </div>
        </form>
    </div>

    <!-- ── TABLE ── -->
    <div class="table-panel anim-6">
        <div class="table-header">
            <div class="table-title">
                <i class="fas fa-table-list"></i>
                Registros em banco de dados
                <span class="count-badge"><?= $kpis['total'] ?? count($licitacoes) ?> registros</span>
            </div>
            <div class="refresh-info">
                <i class="fas fa-rotate"></i>
                Última atualização: <?= date('d/m/Y H:i') ?>
            </div>
        </div>
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="padding-left:22px;">ID / Número</th>
                        <th>Órgão / Objeto</th>
                        <th>Modalidade</th>
                        <th style="text-align:center;">Status</th>
                        <th>Data Sessão</th>
                        <th style="text-align:right;">Valor Est.</th>
                        <th style="text-align:right; padding-right:22px;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($licitacoes)): ?>
                        <?php foreach ($licitacoes as $lic):
                            $statusSlug = str_replace(' ', '_', strtolower($lic['status'] ?? ''));
                            
                            // Lógica de Urgência: Sessão nos próximos 3 dias
                            $hoje = new DateTime(date('Y-m-d'));
                            $dataSessao = new DateTime($lic['dt_sessao']);
                            $diff = $hoje->diff($dataSessao);
                            $diasRestantes = (int)$diff->format("%r%a");
                            $isUrgente = ($diasRestantes >= 0 && $diasRestantes <= 3 && !in_array($lic['status'], ['concluida', 'cancelada', 'suspensa', 'vencida']));
                            
                            $badgeClass = $isUrgente ? 's-urgente' : 's-' . $statusSlug;
                        ?>
                        <tr>
                            <td style="padding-left:22px;">
                                <div class="td-num"><?= htmlspecialchars($lic['numero'] ?? '') ?></div>
                                <div class="td-id">ID: #<?= $lic['id'] ?></div>
                            </td>
                            <td>
                                <div class="td-org"><?= htmlspecialchars($lic['orgao'] ?? '') ?></div>
                                <div class="td-obj"><?= mb_strimwidth(htmlspecialchars($lic['objeto'] ?? ''), 0, 68, '…') ?></div>
                            </td>
                            <td>
                                <span class="td-mod"><?= str_replace('_', ' ', ucfirst($lic['modalidade'] ?? 'N/A')) ?></span>
                            </td>
                            <td style="text-align:center;">
                                <span class="status-badge <?= $badgeClass ?>">
                                    <?php if($isUrgente): ?><i class="fas fa-bolt mr-1"></i><?php endif; ?>
                                    <?= str_replace('_', ' ', strtoupper($lic['status'] ?? '')) ?>
                                </span>
                            </td>
                            <td>
                                <span class="td-date">
                                    <i class="far fa-calendar-alt" style="margin-right:5px; color:var(--text-tertiary);"></i>
                                    <?= date('d/m/Y', strtotime($lic['dt_sessao'])) ?>
                                    <?php if($isUrgente): ?>
                                        <div style="font-size: 10px; color: var(--red); font-weight: 700; margin-top: 2px; text-transform: uppercase;">
                                            <?= $diasRestantes == 0 ? '📌 Sessão Hoje!' : '⏰ Faltam ' . $diasRestantes . ' dias' ?>
                                        </div>
                                    <?php endif; ?>
                                </span>
                            </td>
                            <td class="td-val">
                                R$ <?= number_format($lic['valor_estimado'] ?? 0, 2, ',', '.') ?>
                            </td>
                            <td style="padding-right:22px;">
                                <div class="actions-cell">
                                    <?php if (!empty($lic['edital_path'])): ?>
                                        <a href="<?= BASE_URL ?>/storage/licitacoes/<?= $lic['edital_path'] ?>" class="action-btn" style="color: var(--red); border-color: rgba(248,113,113,0.2); background: var(--red-bg);" target="_blank" title="Baixar Edital PDF">
                                            <i class="fas fa-file-pdf"></i>
                                        </a>
                                    <?php endif; ?>
                                    <a href="<?= BASE_URL ?>/licitacoes/detalhe/<?= $lic['id'] ?>" class="action-btn view" title="Visualizar">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="<?= BASE_URL ?>/licitacoes/editar/<?= $lic['id'] ?>" class="action-btn edit" title="Editar">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                    <form action="<?= BASE_URL ?>/licitacoes/excluir/<?= $lic['id'] ?>" method="POST" style="display:inline;" onsubmit="return confirm('Confirmar exclusão definitiva do protocolo?')">
                                        <button type="submit" class="action-btn trash" title="Excluir">
                                            <i class="fas fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <div class="empty-icon"><i class="fas fa-folder-open"></i></div>
                                    <div class="empty-title">Nenhuma licitação encontrada</div>
                                    <div class="empty-sub">Tente ajustar os filtros ou <a href="<?= BASE_URL ?>/licitacoes/listar" style="color:var(--accent);">limpar a busca</a>.</div>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="table-footer">
            <div class="pagination-info">
                <i class="fas fa-info-circle" style="margin-right:5px;"></i>
                Exibindo <?= count($licitacoes) ?> de <?= $kpis['total_filtrado'] ?? $kpis['total'] ?> licitações
            </div>
            <div class="pagination">
                <a href="?page=<?= max(1, $paginaAtual - 1) ?>&busca=<?= urlencode($filtros['busca'] ?? '') ?>&status=<?= urlencode($filtros['status'] ?? '') ?>" class="page-btn <?= $paginaAtual == 1 ? 'disabled' : '' ?>"><i class="fas fa-chevron-left"></i></a>
                
                <?php for($i = 1; $i <= $totalPaginas; $i++): ?>
                    <a href="?page=<?= $i ?>&busca=<?= urlencode($filtros['busca'] ?? '') ?>&status=<?= urlencode($filtros['status'] ?? '') ?>" class="page-btn <?= $i == $paginaAtual ? 'active' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>

                <a href="?page=<?= min($totalPaginas, $paginaAtual + 1) ?>&busca=<?= urlencode($filtros['busca'] ?? '') ?>&status=<?= urlencode($filtros['status'] ?? '') ?>" class="page-btn <?= $paginaAtual == $totalPaginas ? 'disabled' : '' ?>"><i class="fas fa-chevron-right"></i></a>
            </div>
        </div>
    </div>

</div><!-- /page -->

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── Animação nas linhas da tabela ──
    document.querySelectorAll('.data-table tbody tr').forEach((row, i) => {
        row.style.opacity = '0';
        row.style.transform = 'translateX(-8px)';
        setTimeout(() => {
            row.style.transition = 'all 0.3s ease';
            row.style.opacity = '1';
            row.style.transform = 'none';
        }, 60 + i * 45);
    });

    // ── Export handler para a listagem ──
    ['btnExportarListagem','btnExportarListagem2'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.addEventListener('click', () => {
            alert('📊 Exportação\n\nFormatos disponíveis em breve:\n• CSV\n• Excel (.xlsx)\n• PDF');
        });
    });
});
</script>
</body>
</html>
