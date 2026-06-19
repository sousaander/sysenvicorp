<?php
// views/financeiro/dre.php
?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Mono:wght@400;500&family=Instrument+Serif:ital@0;1&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">

<style>
.dre-wrap *{box-sizing:border-box}
.dre-wrap{
    --ink:#1a1a1a;--ink2:#4a4a4a;--ink3:#888;--ink4:#bbb;
    --surface:#fafaf8;--card:#ffffff;--border:#e8e6e0;
    --green-dark:#0a4a2f;--green-mid:#166534;--green-soft:#dcfce7;--green-line:#bbf7d0;
    --red-dark:#4a0f0f;--red-mid:#991b1b;--red-soft:#fef2f2;--red-line:#fecaca;
    --blue-dark:#0c2d5e;--blue-mid:#1e40af;--blue-soft:#eff6ff;--blue-line:#bfdbfe;
    --mono:'DM Mono',monospace;--serif:'Courier New', Times, serif;--sans:'DM Sans',sans-serif;
    font-family:var(--sans);color:var(--ink);max-width:760px;margin:0 auto;padding:24px 16px 40px;
}
.dark-theme .dre-wrap {
    --ink: #f3f4f6;
    --ink2: #d1d5db;
    --ink3: #9ca3af;
    --ink4: #4b5563;
    --surface: #0f172a;
    --card: #1e293b;
    --border: #334155;
    --blue-soft: rgba(30, 64, 175, 0.2);
}

.dre-header{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:28px;gap:16px;flex-wrap:wrap}
.dre-eyebrow{font-size:11px;font-family:var(--mono);letter-spacing:.12em;text-transform:uppercase;color:var(--ink3);margin-bottom:6px}
.dre-title{font-family:var(--serif);font-size:26px;color:var(--ink);line-height:1.2;font-style:italic}
.dre-sub{font-size:12px;color:var(--ink3);margin-top:4px;font-family:var(--mono)}
.dre-actions{display:flex;flex-direction:column;gap:8px;align-items:flex-end}
.dre-controls{display:flex;gap:8px;align-items:center;flex-wrap:wrap;justify-content:flex-end}
.ctrl-label{font-size:11px;color:var(--ink3);font-family:var(--mono);letter-spacing:.06em}
.dre-wrap select{font-family:var(--mono);font-size:12px;padding:6px 10px;border:.5px solid var(--border);border-radius:6px;background:var(--card);color:var(--ink);cursor:pointer;outline:none}
.dre-wrap select:focus{border-color:#94a3b8}
.pdf-btn{display:inline-flex;align-items:center;gap:6px;font-family:var(--mono);font-size:11px;letter-spacing:.06em;padding:7px 14px;border-radius:6px;border:.5px solid var(--border);background:var(--card);color:var(--ink2);cursor:pointer;text-decoration:none;transition:background .15s;white-space:nowrap}
.pdf-btn:hover{background:var(--border)}
.pdf-btn svg{flex-shrink:0}

.metrics-row{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:24px}
.metric-card{background:var(--card);border:.5px solid var(--border);border-radius:10px;padding:14px 16px}
.metric-label{font-size:11px;font-family:var(--mono);letter-spacing:.08em;text-transform:uppercase;color:var(--ink3);margin-bottom:6px}
.metric-value{font-size:19px;font-family:var(--mono);font-weight:500;color:var(--ink);line-height:1}
.metric-value.green{color:var(--green-mid)}
.metric-value.red{color:var(--red-mid)}
.metric-value.blue{color:var(--blue-mid)}

/* Formatação de Moeda Premium */
.currency-symbol { font-size: 0.65em; font-weight: 600; margin-right: 1px; opacity: 0.7; vertical-align: 0.15em; }
.currency-cents { font-size: 0.75em; font-weight: 500; opacity: 0.8; margin-left: 0.5px; vertical-align: 0.15em; }
.currency-negative { font-weight: 800; margin-right: 2px; }

.metric-bar{height:3px;border-radius:2px;margin-top:10px;background:var(--border)}
.metric-bar-fill{height:100%;border-radius:2px}

.dre-section{margin-bottom:4px}
.section-head{display:flex;align-items:center;gap:10px;padding:10px 14px;border-radius:8px}
.section-icon{width:28px;height:28px;border-radius:6px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.section-icon.green-icon{background:var(--green-soft)}
.section-icon.red-icon{background:var(--red-soft)}
.section-tag{font-size:10px;font-family:var(--mono);letter-spacing:.1em;text-transform:uppercase;padding:3px 8px;border-radius:4px}
.section-tag.green{background:var(--green-soft);color:var(--green-mid)}
.section-tag.red{background:var(--red-soft);color:var(--red-mid)}
.section-name{font-size:13px;font-weight:500;color:var(--ink);flex:1}
.section-total{font-size:16px;font-family:var(--mono);font-weight:500}
.section-total.green{color:var(--green-mid)}
.section-total.red{color:var(--red-mid)}

.items-table{width:100%;border-collapse:collapse;margin:0 0 0}
.items-table thead th{font-size:10px;font-family:var(--mono);letter-spacing:.1em;text-transform:uppercase;color:var(--ink3);padding:0 14px 8px;text-align:left;border-bottom:.5px solid var(--border)}
.items-table thead th:nth-child(2){text-align:right}
.items-table thead th:nth-child(3){text-align:right}
.items-table tbody td{padding:9px 14px;font-size:13px;border-bottom:.5px solid var(--border);transition:background .12s}
.items-table tbody tr:last-child td{border-bottom:none}
.cat-name{color:var(--ink)}
.cat-val{text-align:right;font-family:var(--mono);font-size:12px;color:var(--ink2);white-space:nowrap}
.cat-pct{text-align:right;font-family:var(--mono);font-size:11px}
.pct-bar-wrap{display:flex;align-items:center;justify-content:flex-end;gap:8px}
.pct-mini{display:inline-block;height:3px;border-radius:2px;min-width:2px}
.pct-mini.green{background:var(--green-mid)}
.pct-mini.red{background:var(--red-mid)}
.pct-highlight-green{font-weight:500;color:var(--green-mid)}
.pct-highlight-red{font-weight:500;color:var(--red-mid)}
.pct-muted{color:var(--ink4)}

.subtotal-row{display:flex;align-items:center;justify-content:space-between;padding:10px 14px;border-top:1px solid var(--border)}
.subtotal-label{font-size:12px;font-family:var(--mono);letter-spacing:.06em;text-transform:uppercase;color:var(--ink3)}
.subtotal-val{font-size:14px;font-family:var(--mono);font-weight:500}
.subtotal-val.green{color:var(--green-mid)}
.subtotal-val.red{color:var(--red-mid)}

.dre-divider{height:.5px;background:var(--border);margin:16px 0}

.resultado-card{border-radius:12px;border:1px solid var(--blue-line);background:var(--blue-soft);padding:20px 22px;display:flex;justify-content:space-between;align-items:center;margin-top:16px;gap:16px;flex-wrap:wrap}
.resultado-card.deficit{border-color:var(--red-line);background:var(--red-soft)}
.res-eyebrow{font-size:10px;font-family:var(--mono);letter-spacing:.12em;text-transform:uppercase;color:var(--ink3);margin-bottom:4px}
.res-name{font-family:var(--serif);font-size:18px;color:var(--ink);font-style:italic}
.res-badge{display:inline-block;font-size:10px;font-family:var(--mono);padding:3px 10px;border-radius:4px;margin-left:10px;vertical-align:middle;background:var(--blue-line);color:var(--blue-dark)}
.res-badge.deficit{background:var(--red-line);color:var(--red-dark)}
.res-regime{font-size:11px;font-family:var(--mono);color:var(--ink3);margin-top:3px}
.res-valor{font-family:var(--mono);font-size:28px;font-weight:500;color:var(--blue-mid);white-space:nowrap}
.res-valor.deficit{color:var(--red-mid)}

.empty-state{font-size:13px;color:var(--ink3);font-style:italic;text-align:center;padding:20px 14px}

@media(max-width:600px){
    .metrics-row{grid-template-columns:1fr 1fr}
    .metric-card:last-child{grid-column:1/-1}
    .dre-header{flex-direction:column}
    .dre-actions{align-items:flex-start}
    .res-valor{font-size:22px}
}
</style>

<div class="dre-wrap">

    <!-- Cabeçalho -->
    <div class="dre-header">
        <div>
            <div class="dre-eyebrow">Demonstrativo de Resultado</div>
            <div class="dre-title">Exercício <?= htmlspecialchars($anoSelecionado) ?></div>
            <div class="dre-sub">Regime de <?= $regimeSelecionado === 'caixa' ? 'Caixa' : 'Competência' ?></div>
        </div>
        <div class="dre-actions">
            <form action="<?= BASE_URL ?>/financeiro/dre" method="GET" class="dre-controls" id="dre-form">
                <span class="ctrl-label">Mês</span>
                <select name="mes" id="mes" onchange="this.form.submit()">
                    <option value="">Anual</option>
                    <?php
                    $meses_pt = [1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril', 5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto', 9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'];
                    foreach ($meses_pt as $m => $nome) {
                        $sel = ($m == ($mesSelecionado ?? 0)) ? 'selected' : '';
                        echo "<option value='$m' $sel>$nome</option>";
                    }
                    ?>
                </select>
                <span class="ctrl-label">Ano</span>
                <select name="ano" id="ano" onchange="this.form.submit()">
                    <?php
                    $anoAtual = date('Y');
                    for ($i = $anoAtual - 2; $i <= $anoAtual + 2; $i++) {
                        $sel = ($i == $anoSelecionado) ? 'selected' : '';
                        echo "<option value='$i' $sel>$i</option>";
                    }
                    ?>
                </select>
                <span class="ctrl-label">Regime</span>
                <select name="regime" id="regime" onchange="this.form.submit()">
                    <option value="competencia" <?= $regimeSelecionado === 'competencia' ? 'selected' : '' ?>>Competência</option>
                    <option value="caixa"        <?= $regimeSelecionado === 'caixa'        ? 'selected' : '' ?>>Caixa</option>
                </select>
            </form>
            <div style="display:flex;gap:8px;align-items:center">
                <a href="<?= BASE_URL ?>/financeiro/exportarDrePdf?ano=<?= $anoSelecionado ?>&mes=<?= $mesSelecionado ?>&regime=<?= $regimeSelecionado ?>"
                   target="_blank" class="pdf-btn">
                    <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                    Exportar PDF
                </a>
                <a href="<?= BASE_URL ?>/financeiro/balanco" class="pdf-btn">
                    <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"/>
                    </svg>
                    Voltar ao Balanço
                </a>
            </div>
        </div>
    </div>

    <?php
    $totR = $dreData['total_receitas'];
    $totD = $dreData['total_despesas'];
    $res  = $dreData['resultado'];
    $margem = $totR > 0 ? ($res / $totR) * 100 : 0;
    $despPct = $totR > 0 ? ($totD / $totR) * 100 : 0;

    function fmtBr($v) {
        return 'R$ ' . number_format(abs($v), 2, ',', '.');
    }
    function fmtShort($v) {
        if ($v >= 1000000) return 'R$ ' . number_format($v / 1000000, 1, ',', '.') . 'M';
        if ($v >= 1000)    return 'R$ ' . number_format($v / 1000, 1, ',', '.') . 'k';
        return fmtBr($v);
    }
    ?>

    <!-- Cards de métricas -->
    <div class="metrics-row">
        <div class="metric-card">
            <div class="metric-label">Receita Bruta</div>
            <div class="metric-value green"><?= fmtShort($totR) ?></div>
            <div class="metric-bar"><div class="metric-bar-fill" style="width:100%;background:var(--green-mid)"></div></div>
        </div>
        <div class="metric-card">
            <div class="metric-label">Total Despesas</div>
            <div class="metric-value red"><?= fmtShort($totD) ?></div>
            <div class="metric-bar"><div class="metric-bar-fill" style="width:<?= min(100, $despPct) ?>%;background:var(--red-mid)"></div></div>
        </div>
        <div class="metric-card">
            <div class="metric-label">Margem Líquida</div>
            <div class="metric-value <?= $margem >= 0 ? 'blue' : 'red' ?>"><?= number_format($margem, 1, ',', '.') ?>%</div>
            <div class="metric-bar"><div class="metric-bar-fill" style="width:<?= min(100, abs($margem)) ?>%;background:<?= $margem >= 0 ? 'var(--blue-mid)' : 'var(--red-mid)' ?>"></div></div>
        </div>
    </div>

    <!-- Receitas -->
    <div class="dre-section">
        <div class="section-head">
            <div class="section-icon green-icon">
                <svg width="15" height="15" viewBox="0 0 20 20" fill="none" stroke="var(--green-mid)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <polyline points="3 17 8 12 12 15 17 8"/>
                    <polyline points="14 8 17 8 17 11"/>
                </svg>
            </div>
            <span class="section-tag green">Receitas</span>
            <span class="section-name">Receitas Operacionais</span>
            <span class="section-total green"><?= fmtBr($totR) ?></span>
        </div>

        <?php if (!empty($dreData['receitas'])): ?>
        <table class="items-table">
            <thead>
                <tr>
                    <th>Categoria</th>
                    <th style="text-align:right">Valor</th>
                    <th style="text-align:right">Var. %</th>
                    <th style="text-align:right">Part.</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($dreData['receitas'] as $rec):
                    $pct    = $totR > 0 ? ($rec['valor'] / $totR) * 100 : 0;
                    $barW   = min(60, $pct * 1.4);
                    $cls    = $pct >= 25 ? 'pct-highlight-green' : 'pct-muted';
                ?>
                <tr>
                    <td class="cat-name"><?= htmlspecialchars($rec['categoria']) ?></td>
                    <td class="cat-val"><?= fmtBr($rec['valor'], true) ?></td>
                    <td class="cat-val <?= $rec['variacao'] >= 0 ? 'text-green-600' : 'text-red-600' ?>" style="font-size:11px; text-align:right">
                        <?= $rec['variacao'] > 0 ? '+' : '' ?><?= number_format($rec['variacao'], 1, ',', '.') ?>%
                    </td>
                    <td class="cat-pct">
                        <div class="pct-bar-wrap">
                            <div class="pct-mini green" style="width:<?= $barW ?>px"></div>
                            <span class="<?= $cls ?>"><?= number_format($pct, 1, ',', '.') ?>%</span>
                        </div>
                    </td>
                </tr>
                <?php endforeach ?>
            </tbody>
        </table>
        <?php else: ?>
            <p class="empty-state">Nenhuma receita registrada neste período.</p>
        <?php endif ?>

        <div class="subtotal-row">
            <span class="subtotal-label">Receita Bruta</span>
            <span class="subtotal-val green"><?= fmtBr($totR) ?></span>
        </div>
    </div>

    <div class="dre-divider"></div>

    <!-- Despesas -->
    <div class="dre-section">
        <div class="section-head">
            <div class="section-icon red-icon">
                <svg width="15" height="15" viewBox="0 0 20 20" fill="none" stroke="var(--red-mid)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <polyline points="3 8 8 13 12 10 17 17"/>
                    <polyline points="14 17 17 17 17 14"/>
                </svg>
            </div>
            <span class="section-tag red">Despesas</span>
            <span class="section-name">Despesas Operacionais</span>
            <span class="section-total red">− <?= fmtBr($totD) ?></span>
        </div>

        <?php if (!empty($dreData['despesas'])): ?>
        <table class="items-table">
            <thead>
                <tr>
                    <th>Categoria</th>
                    <th style="text-align:right">Valor</th>
                    <th style="text-align:right">Var. %</th>
                    <th style="text-align:right">Part.</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($dreData['despesas'] as $desp):
                    $pct    = $totR > 0 ? ($desp['valor'] / $totR) * 100 : 0;
                    $barW   = min(60, $pct * 1.4);
                    $cls    = $pct > 15 ? 'pct-highlight-red' : 'pct-muted';
                ?>
                <tr>
                    <td class="cat-name"><?= htmlspecialchars($desp['categoria']) ?></td>
                    <td class="cat-val"><?= fmtBr(-$desp['valor']) ?></td>
                    <td class="cat-val <?= $desp['variacao'] <= 0 ? 'text-green-600' : 'text-red-600' ?>" style="font-size:11px; text-align:right">
                        <?= $desp['variacao'] > 0 ? '+' : '' ?><?= number_format($desp['variacao'], 1, ',', '.') ?>%
                    </td>
                    <td class="cat-pct">
                        <div class="pct-bar-wrap">
                            <div class="pct-mini red" style="width:<?= $barW ?>px"></div>
                            <span class="<?= $cls ?>" title="% sobre a Receita Bruta"><?= number_format($pct, 1, ',', '.') ?>%</span>
                        </div>
                    </td>
                </tr>
                <?php endforeach ?>
            </tbody>
        </table>
        <?php else: ?>
            <p class="empty-state">Nenhuma despesa registrada neste período.</p>
        <?php endif ?>

        <div class="subtotal-row">
            <span class="subtotal-label">Total de Despesas</span>
            <span class="subtotal-val red">− <?= fmtBr($totD) ?></span>
        </div>
    </div>

    <!-- Resultado -->
    <?php $surplus = $res >= 0; ?>
    <div class="resultado-card <?= $surplus ? '' : 'deficit' ?>">
        <div>
            <div class="res-eyebrow">Resultado do Exercício</div>
            <div class="res-name">
                <?= $surplus ? 'Lucro Líquido' : 'Prejuízo' ?> — <?= $anoSelecionado ?>
                <span class="res-badge <?= $surplus ? '' : 'deficit' ?>"><?= $surplus ? 'SUPERÁVIT' : 'DÉFICIT' ?></span>
            </div>
            <div class="res-regime">Regime de <?= $regimeSelecionado === 'caixa' ? 'Caixa' : 'Competência' ?> · <?= count($dreData['receitas']) + count($dreData['despesas']) ?> categorias</div>
        </div>
        <div class="res-valor <?= $surplus ? '' : 'deficit' ?>"><?= fmtBr($res) ?></div>
    </div>

</div><!-- /.dre-wrap -->
