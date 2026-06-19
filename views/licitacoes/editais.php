<?php
/**
 * View: Repositório de Editais
 * Foco: Disponibilização e gestão dos arquivos PDF vinculados às licitações.
 */
$editais = $editais ?? [];
$filtros = $filtros ?? ['busca' => ''];
$paginaAtual = $paginaAtual ?? 1;
$totalPaginas = $totalPaginas ?? 1;
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

        /* ── FILTERS ── */
        .filters-bar { background: var(--bg-surface); border: 1px solid var(--border-soft); border-radius: var(--radius-lg); padding: 16px 20px; margin-bottom: 24px; }
        .input-wrap { position: relative; flex: 1; min-width: 300px; }
        .input-wrap .icon { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-tertiary); font-size: 12px; }
        .form-input { width: 100%; background: var(--bg-elevated); border: 1px solid var(--border-soft); border-radius: var(--radius-md); color: var(--text-primary); padding: 9px 14px 9px 34px; outline: none; transition: var(--transition); font-family: var(--font-ui); font-size: 12.5px; }
        .form-input:focus { border-color: var(--accent); box-shadow: 0 0 0 3px var(--accent-dim); }

        .btn { display: inline-flex; align-items: center; gap: 7px; padding: 9px 18px; border-radius: var(--radius-md); font-size: 12.5px; font-weight: 600; cursor: pointer; transition: var(--transition); border: 1px solid transparent; text-decoration: none; }
        .btn-primary { background: var(--accent); color: #fff; }
        .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 4px 12px var(--accent-glow); }

        /* ── GRID DE EDITAIS ── */
        .edital-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 20px; }
        
        .edital-card { 
            background: var(--bg-surface); 
            border: 1px solid var(--border-soft); 
            border-radius: var(--radius-lg); 
            padding: 24px; 
            display: flex; 
            flex-direction: column; 
            gap: 16px; 
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }
        .edital-card:hover { border-color: var(--accent); transform: translateY(-3px); box-shadow: 0 12px 30px -10px rgba(0,0,0,0.1); }
        
        .edital-card::before {
            content: 'PDF';
            position: absolute;
            top: -10px;
            right: -10px;
            font-family: var(--font-mono);
            font-size: 40px;
            font-weight: 900;
            color: var(--red-bg);
            opacity: 0.5;
            transform: rotate(15deg);
        }

        .card-header { display: flex; justify-content: space-between; align-items: flex-start; z-index: 1; }
        .protocol-num { font-family: var(--font-mono); font-weight: 700; color: var(--accent); font-size: 14px; }
        .badge-status { font-size: 10px; text-transform: uppercase; font-weight: 700; padding: 2px 8px; border-radius: 99px; background: var(--bg-elevated); border: 1px solid var(--border-soft); }

        .orgao-name { font-size: 11px; font-weight: 700; text-transform: uppercase; color: var(--text-tertiary); letter-spacing: 0.5px; }
        .objeto-preview { font-size: 13px; color: var(--text-primary); font-weight: 500; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; height: 60px; }

        .card-meta { display: flex; gap: 15px; font-size: 11.5px; color: var(--text-secondary); border-top: 1px solid var(--border-soft); padding-top: 15px; }
        .meta-item { display: flex; align-items: center; gap: 6px; }
        .meta-item i { color: var(--text-tertiary); }

        .card-actions { display: flex; gap: 10px; margin-top: auto; }
        .btn-download { flex: 1; background: var(--red-bg); color: var(--red); justify-content: center; border-color: rgba(239,68,68,0.2); }
        .btn-download:hover { background: var(--red); color: #fff; }
        
        .btn-view { width: 40px; padding: 0; justify-content: center; background: var(--bg-elevated); color: var(--text-secondary); border-color: var(--border-soft); }
        .btn-view:hover { color: var(--accent); border-color: var(--accent); }

        /* ── PAGINATION ── */
        .pagination { display: flex; justify-content: center; gap: 6px; margin-top: 40px; }
        .page-btn { min-width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; border-radius: 8px; background: var(--bg-surface); border: 1px solid var(--border-soft); color: var(--text-secondary); text-decoration: none; font-size: 13px; transition: var(--transition); }
        .page-btn:hover:not(.disabled) { border-color: var(--accent); color: var(--accent); }
        .page-btn.active { background: var(--accent); color: #fff; border-color: var(--accent); }
        .page-btn.disabled { opacity: 0.4; cursor: not-allowed; }

        /* ── EMPTY STATE ── */
        .empty-state { text-align: center; padding: 80px 20px; background: var(--bg-surface); border-radius: var(--radius-lg); border: 1px dashed var(--border-mid); }
        .empty-icon { font-size: 48px; color: var(--text-tertiary); margin-bottom: 20px; }
        .empty-title { font-size: 18px; font-weight: 600; color: var(--text-primary); }

        @keyframes fadeUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .anim-item { animation: fadeUp 0.4s ease forwards; opacity: 0; }
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
                <span>Editais</span>
            </nav>
            <h1 class="header-title">
                <i class="fas fa-file-pdf"></i>
                Repositório de Editais
            </h1>
        </div>
    </header>

    <!-- NAVEGAÇÃO -->
    <nav class="nav-tabs-wrapper">
        <a href="<?= BASE_URL ?>/licitacoes/dashboard" class="nav-tab"><i class="fas fa-gauge-high"></i>Dashboard</a>
        <a href="<?= BASE_URL ?>/licitacoes/index" class="nav-tab"><i class="fas fa-file-contract"></i>Listagem</a>
        <a href="<?= BASE_URL ?>/licitacoes/captacoes" class="nav-tab"><i class="fas fa-bolt"></i>Radar IA</a>
        <a href="<?= BASE_URL ?>/licitacoes/editais" class="nav-tab active"><i class="fas fa-file-pdf"></i>Editais</a>
        <a href="<?= BASE_URL ?>/licitacoes/relatorios" class="nav-tab"><i class="fas fa-chart-column"></i>Relatórios</a>
    </nav>

    <!-- FILTROS -->
    <div class="filters-bar">
        <form method="GET" action="<?= BASE_URL ?>/licitacoes/editais" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
            <div class="input-wrap">
                <i class="fas fa-search icon"></i>
                <input type="text" name="busca" class="form-input" 
                       value="<?= htmlspecialchars($filtros['busca'] ?? '') ?>" 
                       placeholder="Pesquisar por número, órgão ou termo no objeto…">
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-filter"></i> Filtrar Documentos
            </button>
            <?php if(!empty($filtros['busca'])): ?>
                <a href="<?= BASE_URL ?>/licitacoes/editais" class="btn" style="color: var(--text-tertiary);">Limpar</a>
            <?php endif; ?>
            <div style="margin-left: auto; font-size: 12px; color: var(--text-tertiary);">
                <strong><?= count($editais) ?></strong> editais com arquivo disponíveis
            </div>
        </form>
    </div>

    <!-- GRID -->
    <?php if (!empty($editais)): ?>
        <div class="edital-grid">
            <?php foreach ($editais as $index => $lic): 
                $delay = $index * 0.05;
            ?>
                <article class="edital-card anim-item" style="animation-delay: <?= $delay ?>s">
                    <div class="card-header">
                        <span class="protocol-num"><?= htmlspecialchars($lic['numero']) ?></span>
                        <span class="badge-status"><?= htmlspecialchars($lic['status']) ?></span>
                    </div>
                    
                    <div>
                        <h3 class="orgao-name"><?= htmlspecialchars($lic['orgao']) ?></h3>
                        <p class="objeto-preview" title="<?= htmlspecialchars($lic['objeto']) ?>">
                            <?= htmlspecialchars($lic['objeto']) ?>
                        </p>
                    </div>

                    <div class="card-meta">
                        <div class="meta-item">
                            <i class="far fa-calendar-check"></i>
                            <?= date('d/m/Y', strtotime($lic['dt_sessao'])) ?>
                        </div>
                        <div class="meta-item">
                            <i class="fas fa-hand-holding-dollar"></i>
                            R$ <?= number_format($lic['valor_estimado'], 2, ',', '.') ?>
                        </div>
                    </div>

                    <div class="card-actions">
                        <a href="<?= BASE_URL ?>/storage/licitacoes/<?= $lic['edital_path'] ?>" 
                           target="_blank" class="btn btn-download" title="Baixar arquivo PDF">
                            <i class="fas fa-file-arrow-down"></i> Download Edital
                        </a>
                        <a href="<?= BASE_URL ?>/licitacoes/detalhe/<?= $lic['id'] ?>" 
                           class="btn btn-view" title="Ver detalhes do protocolo">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="<?= BASE_URL ?>/licitacoes/editar/<?= $lic['id'] ?>" 
                           class="btn btn-view" title="Editar informações">
                            <i class="fas fa-pen-to-square"></i>
                        </a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <!-- PAGINAÇÃO -->
        <?php if ($totalPaginas > 1): ?>
            <div class="pagination">
                <a href="?page=<?= max(1, $paginaAtual - 1) ?>&busca=<?= urlencode($filtros['busca']) ?>" 
                   class="page-btn <?= $paginaAtual == 1 ? 'disabled' : '' ?>">
                    <i class="fas fa-chevron-left"></i>
                </a>
                
                <?php 
                $start = max(1, $paginaAtual - 2);
                $end = min($totalPaginas, $paginaAtual + 2);
                for ($i = $start; $i <= $end; $i++): 
                ?>
                    <a href="?page=<?= $i ?>&busca=<?= urlencode($filtros['busca']) ?>" 
                       class="page-btn <?= $i == $paginaAtual ? 'active' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>

                <a href="?page=<?= min($totalPaginas, $paginaAtual + 1) ?>&busca=<?= urlencode($filtros['busca']) ?>" 
                   class="page-btn <?= $paginaAtual == $totalPaginas ? 'disabled' : '' ?>">
                    <i class="fas fa-chevron-right"></i>
                </a>
            </div>
        <?php endif; ?>

    <?php else: ?>
        <div class="empty-state anim-item" style="opacity: 1;">
            <div class="empty-icon"><i class="fas fa-file-circle-exclamation"></i></div>
            <h3 class="empty-title">Nenhum edital PDF encontrado</h3>
            <p style="color: var(--text-secondary); margin-top: 8px;">
                <?= !empty($filtros['busca']) ? 'Não encontramos editais para os termos pesquisados.' : 'Parece que ainda não há licitações com arquivos de edital anexados.' ?>
            </p>
            <div style="margin-top: 24px;">
                <a href="<?= BASE_URL ?>/licitacoes/novo" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Cadastrar Nova Licitação
                </a>
            </div>
        </div>
    <?php endif; ?>

</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Pequeno feedback visual ao clicar em download
    document.querySelectorAll('.btn-download').forEach(btn => {
        btn.addEventListener('click', function() {
            const icon = this.querySelector('i');
            const originalClass = icon.className;
            icon.className = 'fas fa-circle-notch fa-spin';
            setTimeout(() => icon.className = originalClass, 2000);
        });
    });
});
</script>
</body>
</html>