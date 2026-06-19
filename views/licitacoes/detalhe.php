<link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
<style>
    :root {
        --lic-bg:       #f0f4fa;
        --lic-surface:  #ffffff;
        --lic-border:   rgba(0, 0, 0, 0.06);
        --lic-border-mid: rgba(0, 0, 0, 0.12);
        --lic-text:     #1e293b;
        --lic-muted:    #475569;
        --lic-faint:    #94a3b8;
        --lic-accent:   #2563eb;
        --lic-accent-glow: rgba(37, 99, 235, 0.1);
        --lic-success:  #10b981;
        --lic-danger:   #ef4444;
        --lic-mono:     'JetBrains Mono', monospace;
        --radius-lg:    14px;
        --radius-md:    10px;
        --transition:   all 0.22s cubic-bezier(0.4, 0, 0.2, 1);
    }

    body.dark-theme {
        --lic-bg:       #0b0f1a;
        --lic-surface:  #111827;
        --lic-border:   rgba(255, 255, 255, 0.06);
        --lic-border-mid: rgba(255, 255, 255, 0.12);
        --lic-text:     #f0f6ff;
        --lic-muted:    #8fa3c0;
        --lic-faint:    #4d6282;
        --lic-accent:   #38bdf8;
        --lic-success:  #34d399;
        --lic-danger:   #f87171;
    }

    /* ─── Container ─── */
    .lic-wrap {
        background: var(--lic-bg);
        color: var(--lic-text);
        padding: 32px;
        font-family: 'Sora', sans-serif;
        font-size: 14px;
    }

    /* ─── Breadcrumb ─── */
    .lic-breadcrumb {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 11.5px;
        color: var(--lic-faint);
        list-style: none;
        margin-bottom: 24px;
        padding: 0;
    }
    .lic-breadcrumb a { color: var(--lic-accent); text-decoration: none; }
    .lic-breadcrumb .sep { color: var(--lic-faint); }

    /* ─── Page header ─── */
    .lic-page-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 28px;
        flex-wrap: wrap;
    }
    .lic-header-left { display: flex; flex-direction: column; gap: 4px; }
    
    .lic-page-title {
        font-size: 22px;
        font-weight: 700;
        color: var(--lic-text);
        display: flex;
        align-items: center;
        gap: 10px;
        margin: 0;
    }
    .lic-page-title i { color: var(--lic-accent); }
    
    .lic-page-subtitle {
        font-size: 12.5px;
        color: var(--lic-muted);
    }
    .lic-num-badge {
        font-family: var(--lic-mono);
        font-size: 11px;
        color: var(--lic-accent);
        background: var(--lic-accent-glow);
        border: 1px solid var(--lic-accent);
        border-radius: 99px;
        padding: 2px 9px;
        font-weight: 600;
    }
    .lic-header-actions { display: flex; gap: 8px; flex-shrink: 0; flex-wrap: wrap; }

    /* ─── Buttons ─── */
    .lic-btn {
        font-size: 12.5px;
        font-weight: 500;
        padding: 8px 18px;
        border-radius: 8px;
        border: 1px solid var(--lic-border);
        background: var(--lic-surface);
        color: var(--lic-text);
        cursor: pointer;
        display: inline-flex; align-items: center; gap: 8px;
        text-decoration: none;
        transition: var(--transition);
        white-space: nowrap;
    }
    .lic-btn:hover { border-color: var(--lic-accent); background: var(--lic-bg); color: var(--lic-accent); }
    .lic-btn i { font-size: 14px; }

    .lic-btn-primary {
        background: var(--lic-accent);
        color: #fff;
        border-color: var(--lic-accent);
    }
    .lic-btn-primary:hover { background: #1558c0; border-color: #1558c0; color: #fff; }

    /* ─── Two-column grid ─── */
    .lic-grid {
        display: grid;
        grid-template-columns: 1fr 320px;
        gap: 20px;
        align-items: start;
    }
    @media (max-width: 900px) {
        .lic-grid { grid-template-columns: 1fr; }
    }

    /* ─── Cards ─── */
    .lic-card {
        background: var(--lic-surface);
        border: 1px solid var(--lic-border);
        border-radius: var(--radius-lg);
        overflow: hidden;
        margin-bottom: 20px;
        transition: var(--transition);
    }
    .lic-card:last-child { margin-bottom: 0; }
    .lic-card-head {
        padding: 16px 22px;
        border-bottom: 1px solid var(--lic-border);
        display: flex; align-items: center; gap: 10px;
    }
    .lic-card:hover { border-color: var(--lic-border-mid); }

    .lic-card-head-label {
        font-size: 13px;
        font-weight: 600;
        color: var(--lic-text);
    }
    .lic-card-head i { font-size: 15px; color: var(--lic-muted); }
    .lic-card-body { padding: 20px; }
    .lic-card-body-sm { padding: 18px 22px; }

    /* ─── Texto longo ─── */
    .lic-prose {
        font-size: 14px;
        line-height: 1.75;
        color: var(--lic-text);
    }
    .lic-prose-muted {
        font-size: 13px;
        line-height: 1.75;
        color: var(--lic-muted);
    }

    /* ─── Metadados ─── */
    .lic-meta-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 4px 16px;
    }
    .lic-meta-item { margin-bottom: 16px; }
    .lic-meta-item:last-child { margin-bottom: 0; }
    .lic-meta-label {
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.7px;
        color: var(--lic-faint);
        margin-bottom: 4px;
        display: block;
    }
    .lic-meta-val {
        font-size: 13px;
        color: var(--lic-text);
        font-weight: 400;
    }
    .lic-meta-val.strong { font-weight: 600; }
    .lic-meta-val.valor {
        font-family: var(--lic-mono);
        font-size: 18px;
        font-weight: 600;
        color: var(--lic-success);
    }
    .lic-meta-val.data {
        color: var(--lic-danger);
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    /* ─── Divider ─── */
    .lic-divider {
        border: none;
        border-top: 1px solid var(--lic-border);
        margin: 16px 0;
    }

    /* ─── Status badge ─── */
    .lic-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        padding: 3px 12px;
        border-radius: 20px;
    }
    .lic-badge .dot {
        width: 6px; height: 6px;
        border-radius: 50%;
        background: currentColor;
    }

    .badge-rascunho   { background: rgba(100,116,139,.12); color: #64748b; }
    .badge-publicada  { background: rgba(29,111,232,.12);  color: #1d6fe8; }
    .badge-aberta     { background: rgba(15,110,86,.12);   color: #0f6e56; }
    .badge-concluida  { background: rgba(15,23,42,.08);    color: #475569; }
    .badge-urgente    { background: rgba(185,28,28,.12);   color: #b91c1c; }
    .badge-em_analise { background: rgba(217,119,6,.12);   color: #b45309; }
    .badge-homologada { background: rgba(109,40,217,.12);  color: #6d28d9; }
    .badge-suspensa   { background: rgba(100,116,139,.12); color: #64748b; }
    .badge-cancelada  { background: rgba(220,38,38,.12);   color: #dc2626; }
    .badge-vencida    { background: rgba(71,85,105,.10);   color: #64748b; }

    body.dark-theme .badge-aberta     { background: rgba(52,211,153,.12); color: #34d399; }
    body.dark-theme .badge-publicada  { background: rgba(96,165,250,.12); color: #60a5fa; }
    body.dark-theme .badge-urgente    { background: rgba(248,113,113,.12);color: #f87171; }
    body.dark-theme .badge-em_analise { background: rgba(251,191,36,.12); color: #fbbf24; }
    body.dark-theme .badge-homologada { background: rgba(167,139,250,.12);color: #a78bfa; }

    /* ─── Timeline ─── */
    .lic-timeline { display: flex; flex-direction: column; }
    .lic-tl-item {
        display: flex;
        gap: 12px;
        padding-bottom: 18px;
    }
    .lic-tl-item:last-child { padding-bottom: 0; }
    .lic-tl-left {
        display: flex;
        flex-direction: column;
        align-items: center;
        width: 16px;
        flex-shrink: 0;
    }
    .lic-tl-dot {
        width: 10px; height: 10px;
        border-radius: 2px;
        border: 1.5px solid var(--lic-accent);
        background: var(--lic-accent);
        margin-top: 4px;
        flex-shrink: 0;
    }
    .lic-tl-dot.done  { background: var(--lic-success); border-color: var(--lic-success); }
    .lic-tl-dot.pending { background: transparent; border-color: var(--lic-faint); }
    .lic-tl-line {
        width: 1px;
        flex: 1;
        background: var(--lic-border);
        margin: 4px 0;
    }
    .lic-tl-title  { font-size: 13px; font-weight: 500; color: var(--lic-text); }
    .lic-tl-date   { font-size: 11px; color: var(--lic-faint); margin-top: 2px; }

    /* ─── Progress ─── */
    .lic-progress-labels {
        display: flex;
        justify-content: space-between;
        font-size: 11px;
        color: var(--lic-faint);
        margin-bottom: 6px;
    }
    .lic-progress-track {
        height: 4px;
        background: var(--lic-border);
        border-radius: 2px;
        overflow: hidden;
    }
    .lic-progress-fill {
        height: 100%;
        background: var(--lic-accent);
        border-radius: 2px;
        transition: width 0.4s ease;
    }
    .lic-progress-hint {
        font-size: 11px;
        color: var(--lic-faint);
        margin-top: 6px;
    }

    /* ─── Tags ─── */
    .lic-tags { display: flex; flex-wrap: wrap; gap: 6px; }
    .lic-tag {
        font-size: 11px;
        padding: 3px 10px;
        border-radius: 20px;
        background: var(--lic-bg);
        color: var(--lic-muted);
        border: 1px solid var(--lic-border);
    }

    /* ─── Action list ─── */
    .lic-actions { display: flex; flex-direction: column; gap: 7px; }
    .lic-action-btn {
        width: 100%;
        font-size: 13px;
        font-weight: 500;
        padding: 9px 14px;
        border-radius: 8px;
        border: 1px solid var(--lic-border);
        background: transparent;
        color: var(--lic-text);
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 9px;
        text-align: left;
        transition: border-color 0.15s, color 0.15s;
    }
    .lic-action-btn:hover { border-color: var(--lic-accent); color: var(--lic-accent); }
    .lic-action-btn i { font-size: 15px; color: var(--lic-muted); }
    .lic-action-btn:hover i { color: var(--lic-accent); }
</style>

<div class="lic-wrap">

    <!-- Breadcrumb -->
    <ol class="lic-breadcrumb" aria-label="Navegação">
        <li><a href="<?= BASE_URL ?>/licitacoes">Licitações</a></li>
        <li class="sep">/</li>
        <li>Detalhamento</li>
    </ol>

    <!-- Header -->
    <div class="lic-page-header">
        <div class="lic-header-left">
            <h1 class="lic-page-title">
                <i class="fas fa-file-description"></i>
                Detalhamento do Processo
            </h1>
            <p class="lic-page-subtitle">
                <span class="lic-num-badge">#<?= htmlspecialchars($lic['numero']) ?></span> &middot;
                <?= str_replace('_', ' ', ucfirst($lic['modalidade'])) ?>
                &middot; Atualizado em <?= date('d/m/Y', strtotime($lic['updated_at'] ?? 'now')) ?>
            </p>
        </div>
        <div class="lic-header-actions">
            <a href="<?= BASE_URL ?>/licitacoes" class="lic-btn">
                <i class="fas fa-chevron-left"></i> Voltar
            </a>
            <button class="lic-btn" onclick="window.print()">
                <i class="fas fa-print"></i> Imprimir
            </button>
            <a href="<?= BASE_URL ?>/licitacoes/editar/<?= $lic['id'] ?>" class="lic-btn lic-btn-primary">
                <i class="fas fa-edit"></i> Editar
            </a>
        </div>
    </div>

    <!-- Grid principal -->
    <div class="lic-grid">

        <!-- Coluna principal -->
        <div>

            <!-- Objeto -->
            <div class="lic-card">
                <div class="lic-card-head">
                    <i class="fas fa-align-left"></i>
                    <span class="lic-card-head-label">Objeto da licitação</span>
                </div>
                <div class="lic-card-body">
                    <p class="lic-prose"><?= nl2br(htmlspecialchars($lic['objeto'])) ?></p>
                </div>
            </div>

            <!-- Justificativa -->
            <?php if (!empty($lic['justificativa'])): ?>
            <div class="lic-card">
                <div class="lic-card-head">
                    <i class="fas fa-info-circle"></i>
                    <span class="lic-card-head-label">Justificativa técnica</span>
                </div>
                <div class="lic-card-body">
                    <p class="lic-prose-muted"><?= nl2br(htmlspecialchars($lic['justificativa'])) ?></p>
                </div>
            </div>
            <?php endif; ?>

            <!-- Linha do tempo -->
            <?php if (!empty($lic['dt_publicacao']) || !empty($lic['dt_sessao'])): ?>
            <div class="lic-card">
                <div class="lic-card-head">
                    <i class="fas fa-stream"></i>
                    <span class="lic-card-head-label">Linha do tempo</span>
                </div>
                <div class="lic-card-body">
                    <div class="lic-timeline">
                        <?php if (!empty($lic['dt_publicacao'])): ?>
                        <div class="lic-tl-item">
                            <div class="lic-tl-left">
                                <div class="lic-tl-dot done"></div>
                                <div class="lic-tl-line"></div>
                            </div>
                            <div>
                                <div class="lic-tl-title">Publicação do edital</div>
                                <div class="lic-tl-date"><?= date('d/m/Y', strtotime($lic['dt_publicacao'])) ?></div>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($lic['dt_impugnacao'])): ?>
                        <div class="lic-tl-item">
                            <div class="lic-tl-left">
                                <div class="lic-tl-dot done"></div>
                                <div class="lic-tl-line"></div>
                            </div>
                            <div>
                                <div class="lic-tl-title">Prazo de impugnações</div>
                                <div class="lic-tl-date"><?= date('d/m/Y', strtotime($lic['dt_impugnacao'])) ?></div>
                            </div>
                        </div>
                        <?php endif; ?>
                        <div class="lic-tl-item">
                            <div class="lic-tl-left">
                                <div class="lic-tl-dot"></div>
                                <div class="lic-tl-line"></div>
                            </div>
                            <div>
                                <div class="lic-tl-title">Sessão pública — abertura</div>
                                <div class="lic-tl-date"><?= date('d/m/Y', strtotime($lic['dt_sessao'])) ?></div>
                            </div>
                        </div>
                        <div class="lic-tl-item">
                            <div class="lic-tl-left">
                                <div class="lic-tl-dot pending"></div>
                            </div>
                            <div>
                                <div class="lic-tl-title">Homologação prevista</div>
                                <div class="lic-tl-date"><?= !empty($lic['dt_homologacao']) ? date('d/m/Y', strtotime($lic['dt_homologacao'])) : 'A definir' ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Tags -->
            <?php if (!empty($lic['tags'])): ?>
            <div class="lic-card">
                <div class="lic-card-head">
                    <i class="fas fa-tags"></i>
                    <span class="lic-card-head-label">Categorias</span>
                </div>
                <div class="lic-card-body">
                    <div class="lic-tags">
                        <?php foreach (explode(',', $lic['tags']) as $tag): ?>
                            <span class="lic-tag"><?= htmlspecialchars(trim($tag)) ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

        </div>

        <!-- Coluna lateral -->
        <div>

            <!-- Metadados -->
            <div class="lic-card">
                <div class="lic-card-head">
                    <i class="fas fa-list-ul"></i>
                    <span class="lic-card-head-label">Informações do processo</span>
                </div>
                <div class="lic-card-body">

                    <div class="lic-meta-item">
                        <span class="lic-meta-label">Status</span>
                        <?php $slug = str_replace(' ', '_', strtolower($lic['status'] ?? '')); ?>
                        <span class="lic-badge badge-<?= $slug ?>">
                            <span class="dot"></span>
                            <?= str_replace('_', ' ', ucfirst($lic['status'] ?? '')) ?>
                        </span>
                    </div>

                    <hr class="lic-divider">

                    <div class="lic-meta-grid">
                        <div class="lic-meta-item">
                            <span class="lic-meta-label">Modalidade</span>
                            <span class="lic-meta-val"><?= str_replace('_', ' ', ucfirst($lic['modalidade'])) ?></span>
                        </div>
                        <div class="lic-meta-item">
                            <span class="lic-meta-label">Número</span>
                            <span class="lic-meta-val strong">#<?= htmlspecialchars($lic['numero']) ?></span>
                        </div>
                    </div>

                    <div class="lic-meta-item">
                        <span class="lic-meta-label">Órgão público</span>
                        <span class="lic-meta-val strong"><?= htmlspecialchars($lic['orgao']) ?></span>
                    </div>

                    <div class="lic-meta-item">
                        <span class="lic-meta-label">Responsável técnico</span>
                        <span class="lic-meta-val"><?= htmlspecialchars($lic['responsavel']) ?></span>
                    </div>

                    <hr class="lic-divider">

                    <div class="lic-meta-item">
                        <span class="lic-meta-label">Valor estimado</span>
                        <span class="lic-meta-val valor">R$ <?= number_format($lic['valor_estimado'], 2, ',', '.') ?></span>
                    </div>

                    <div class="lic-meta-item" style="margin-bottom:0">
                        <span class="lic-meta-label">Data da sessão</span>
                        <span class="lic-meta-val data">
                            <i class="far fa-calendar-alt"></i>
                            <?= date('d/m/Y', strtotime($lic['dt_sessao'])) ?>
                        </span>
                    </div>

                </div>
            </div>

            <!-- Prazo visual -->
            <?php if (!empty($lic['dt_publicacao']) && !empty($lic['dt_sessao'])): ?>
            <?php
                $inicio   = strtotime($lic['dt_publicacao']);
                $sessao   = strtotime($lic['dt_sessao']);
                $hoje     = time();
                $total    = max(1, $sessao - $inicio);
                $elapsed  = min($total, max(0, $hoje - $inicio));
                $pct      = round(($elapsed / $total) * 100);
                $restam   = max(0, (int)(($sessao - $hoje) / 86400));
            ?>
            <div class="lic-card">
                <div class="lic-card-head">
                    <i class="fas fa-hourglass-half"></i>
                    <span class="lic-card-head-label">Prazo do processo</span>
                </div>
                <div class="lic-card-body-sm">
                    <div class="lic-progress-labels">
                        <span>Início: <?= date('d/m', $inicio) ?></span>
                        <span>Sessão: <?= date('d/m', $sessao) ?></span>
                    </div>
                    <div class="lic-progress-track">
                        <div class="lic-progress-fill" style="width:0%" data-pct="<?= $pct ?>"></div>
                    </div>
                    <p class="lic-progress-hint">
                        <?= $restam > 0 ? "$restam dias restantes" : "Prazo encerrado" ?>
                    </p>
                </div>
            </div>
            <?php endif; ?>

            <!-- Ações -->
            <div class="lic-card">
                <div class="lic-card-head">
                    <i class="fas fa-bolt"></i>
                    <span class="lic-card-head-label">Ações</span>
                </div>
                <div class="lic-card-body">
                    <div class="lic-actions">
                        <button class="lic-action-btn" onclick="alert('Funcionalidade de anexo em desenvolvimento.')">
                            <i class="fas fa-paperclip"></i> Gerenciar anexos
                        </button>
                        <button class="lic-action-btn" onclick="window.print()">
                            <i class="fas fa-print"></i> Imprimir dossiê
                        </button>
                        <button class="lic-action-btn">
                            <i class="fas fa-share-alt"></i> Compartilhar link
                        </button>
                        <button class="lic-action-btn">
                            <i class="fas fa-history"></i> Ver histórico
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const bar = document.querySelector('.lic-progress-fill');
    if (bar) {
        setTimeout(() => {
            bar.style.width = bar.dataset.pct + '%';
        }, 300);
    }
});
</script>
