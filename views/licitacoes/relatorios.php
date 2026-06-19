<?php
/**
 * View: Relatórios de Licitações
 * Foco: Apresentar opções para geração de relatórios diversos.
 */
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> · Módulo de Licitações</title>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --bg-base:      #f0f4fa;
            --bg-surface:   #ffffff;
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

            --red:           #ef4444;
            --red-bg:        rgba(239,68,68,0.1);
            --green:         #10b981;
            --green-bg:      rgba(16,185,129,0.1);

            --radius-md:     10px;
            --radius-lg:     14px;

            --font-ui:      'Sora', system-ui, sans-serif;
            --font-mono:    'JetBrains Mono', monospace;
            --transition:   all 0.22s cubic-bezier(0.4,0,0.2,1);
        }

        body.dark-theme {
            --bg-base:      #0b0f1a;
            --bg-surface:   #111827;
            --bg-elevated:  #1a2235;
            --border-soft:  rgba(255,255,255,0.06);
            --border-mid:   rgba(255,255,255,0.12);
            --border-accent:rgba(56,189,248,0.35);
            --text-primary:  #f0f6ff;
            --text-secondary:#8fa3c0;
            --text-tertiary: #4d6282;
            --accent:        #38bdf8;
            --accent-hover:  #7dd3fc;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { background: var(--bg-base); color: var(--text-primary); font-family: var(--font-ui); line-height: 1.6; }

        .page { max-width: 1480px; margin: 0 auto; padding: 28px 32px 48px; }

        /* ── HEADER ── */
        .header { display: flex; align-items: flex-start; justify-content: space-between; gap: 20px; flex-wrap: wrap; margin-bottom: 28px; }
        .breadcrumb { display: flex; align-items: center; gap: 8px; font-size: 11.5px; color: var(--text-tertiary); letter-spacing: 0.4px; }
        .breadcrumb a { color: var(--accent); text-decoration: none; }
        .header-title { font-size: 22px; font-weight: 700; display: flex; align-items: center; gap: 10px; }
        .header-title i { color: var(--accent); }

        /* ── NAV TABS ── */
        .nav-tabs-wrapper { display: flex; align-items: center; gap: 2px; background: var(--bg-surface); border: 1px solid var(--border-soft); border-radius: var(--radius-lg); padding: 4px; margin-bottom: 28px; overflow-x: auto; }
        .nav-tab { display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; border-radius: var(--radius-md); font-size: 12.5px; font-weight: 500; color: var(--text-secondary); text-decoration: none; transition: var(--transition); border: 1px solid transparent; white-space: nowrap; }
        .nav-tab:hover { background: var(--bg-elevated); color: var(--text-primary); }
        .nav-tab.active { background: var(--bg-elevated); color: var(--accent); border-color: var(--border-accent); }

        /* ── REPORT CARD ── */
        .report-card {
            background: var(--bg-surface);
            border: 1px solid var(--border-soft);
            border-radius: var(--radius-lg);
            padding: 24px;
            display: flex;
            flex-direction: column;
            gap: 16px;
            transition: var(--transition);
            max-width: 400px;
        }
        .report-card:hover { border-color: var(--accent); transform: translateY(-3px); box-shadow: 0 12px 30px -10px rgba(0,0,0,0.1); }
        .report-card-title { font-size: 18px; font-weight: 600; color: var(--text-primary); margin-bottom: 8px; }
        .report-card-description { font-size: 13px; color: var(--text-secondary); line-height: 1.5; }
        .report-card-actions { margin-top: 16px; }
        .btn { display: inline-flex; align-items: center; gap: 7px; padding: 9px 18px; border-radius: var(--radius-md); font-size: 12.5px; font-weight: 600; cursor: pointer; transition: var(--transition); border: 1px solid transparent; text-decoration: none; }
        .btn-primary { background: var(--accent); color: #fff; }
        .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 4px 12px var(--accent-glow); }
        .form-select {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid var(--border-soft);
            border-radius: var(--radius-md);
            background: var(--bg-elevated);
            color: var(--text-primary);
            font-family: var(--font-ui);
            font-size: 12.5px;
            margin-top: 8px;
        }
    </style>
</head>
<body>
<div class="page">

    <!-- HEADER -->
    <header class="header">
        <div class="header-left">
            <nav class="breadcrumb">
                <a href="<?= BASE_URL ?>/licitacoes">Licitações</a>
                <span style="opacity:0.4;">/</span>
                <span>Relatórios</span>
            </nav>
            <h1 class="header-title">
                <i class="fas fa-chart-column"></i>
                Relatórios de Licitações
            </h1>
        </div>
    </header>

    <!-- NAVEGAÇÃO -->
    <nav class="nav-tabs-wrapper">
        <a href="<?= BASE_URL ?>/licitacoes/dashboard" class="nav-tab"><i class="fas fa-gauge-high"></i>Dashboard</a>
        <a href="<?= BASE_URL ?>/licitacoes/index" class="nav-tab"><i class="fas fa-file-contract"></i>Listagem</a>
        <a href="<?= BASE_URL ?>/licitacoes/captacoes" class="nav-tab"><i class="fas fa-bolt"></i>Radar IA</a>
        <a href="<?= BASE_URL ?>/licitacoes/editais" class="nav-tab"><i class="fas fa-file-lines"></i>Editais</a>
        <a href="<?= BASE_URL ?>/licitacoes/relatorios" class="nav-tab active"><i class="fas fa-chart-column"></i>Relatórios</a>
    </nav>

    <div class="report-card">
        <h2 class="report-card-title">Relatório de Editais por Mês</h2>
        <p class="report-card-description">Gere um relatório PDF consolidando todos os editais disponíveis para um mês e ano específicos.</p>
        <form action="<?= BASE_URL ?>/licitacoes/relatorioPdf" method="GET" class="report-card-actions">
            <label for="mes" class="text-secondary" style="font-size: 12px; font-weight: 500;">Mês:</label>
            <?php 
                $mesesNomes = [
                    1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril', 5 => 'Maio', 6 => 'Junho',
                    7 => 'Julho', 8 => 'Agosto', 9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'
                ];
            ?>
            <select name="mes" id="mes" class="form-select">
                <?php for ($m = 1; $m <= 12; $m++): ?>
                    <option value="<?= $m ?>" <?= (date('n') == $m) ? 'selected' : '' ?>><?= $mesesNomes[$m] ?></option>
                <?php endfor; ?>
            </select>
            <label for="ano" class="text-secondary" style="font-size: 12px; font-weight: 500; margin-top: 12px; display: block;">Ano:</label>
            <select name="ano" id="ano" class="form-select">
                <?php for ($a = date('Y'); $a >= date('Y') - 5; $a--): ?>
                    <option value="<?= $a ?>" <?= (date('Y') == $a) ? 'selected' : '' ?>><?= $a ?></option>
                <?php endfor; ?>
            </select>
            <label for="categoria" class="text-secondary" style="font-size: 12px; font-weight: 500; margin-top: 12px; display: block;">Categoria:</label>
            <select name="categoria" id="categoria" class="form-select">
                <option value="">Todas as Categorias</option>
                <option value="Obras">Obras</option>
                <option value="Serviços">Serviços</option>
                <option value="TI">TI</option>
            </select>
            <button type="submit" class="btn btn-primary" style="margin-top: 20px; width: 100%;">
                <i class="fas fa-file-pdf"></i> Gerar PDF
            </button>
        </form>
    </div>

</div>
</body>
</html>