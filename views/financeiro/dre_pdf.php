<?php
// views/financeiro/dre_pdf.php

$totR    = $dreData['total_receitas'];
$totD    = $dreData['total_despesas'];
$res     = $dreData['resultado'];
$margem  = $totR > 0 ? ($res / $totR) * 100 : 0;
$despPct = $totR > 0 ? ($totD / $totR) * 100 : 0;
$surplus = $res >= 0;
$regime  = $regime ?? 'competencia';
$nCats   = count($dreData['receitas']) + count($dreData['despesas']);

// Processamento da Logo para Base64 (Garante exibição no Dompdf)
$logoBase64 = null;
if (!empty($empresa['logo_path'])) {
    $logoFile = ROOT_PATH . '/public/uploads/logos/' . $empresa['logo_path'];
    if (file_exists($logoFile)) {
        $logoData = file_get_contents($logoFile);
        $logoExt  = pathinfo($logoFile, PATHINFO_EXTENSION);
        $logoBase64 = 'data:image/' . $logoExt . ';base64,' . base64_encode($logoData);
    }
}

function dreShort($v) {
    if ($v >= 1000000) return 'R$ ' . number_format($v / 1000000, 1, ',', '.') . 'M';
    if ($v >= 1000)    return 'R$ ' . number_format($v / 1000, 1, ',', '.') . 'k';
    return 'R$ ' . number_format(abs($v), 2, ',', '.');
}
function dreFmt($v) {
    return 'R$ ' . number_format(abs($v), 2, ',', '.');
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <style>
        /* Dompdf prefere fontes nativas ou declaradas explicitamente */
        @page { margin: 1cm; }
        
        * { box-sizing: border-box; }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10pt;
            color: #1a1a1a;
            line-height: 1.4;
            margin: 0;
            padding-bottom: 40pt; /* Garante espaço para o rodapé fixo */
        }

        /* ─── Cabeçalho ─── */
        .header-table {
            width: 100%;
            border-bottom: 1.5pt solid #1a1a1a;
            padding-bottom: 14pt;
            margin-bottom: 20pt;
        }
        .logo-box {
            width: 140px;
            text-align: left;
            vertical-align: middle;
        }
        .logo-img {
            max-width: 130px;
            max-height: 60px;
        }
        .title-box {
            text-align: right;
            vertical-align: middle;
        }
        .header-info {
            text-align: left;
            vertical-align: middle;
            padding-left: 15px;
        }
        .doc-company {
            font-family: monospace;
            font-size: 7.5pt;
            letter-spacing: .13em;
            text-transform: uppercase;
            color: #999;
            margin-bottom: 5pt;
        }
        .doc-title {
            font-family: 'Courier New', Times, serif;
            font-size: 18pt;
            font-style: italic;
            color: #1a1a1a;
            margin: 0;
        }
        .doc-subtitle {
            font-family: monospace;
            font-size: 8.5pt;
            color: #666;
            margin-top: 3pt;
        }
        .doc-meta {
            font-family: monospace;
            font-size: 8pt;
            color: #888;
        }
        .meta-label {
            font-size: 8.5pt;
            color: #555;
            font-weight: bold;
        }

        /* ─── KPIs ─── */
        .kpi-grid {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 22pt;
        }
        .kpi-cell {
            padding: 12pt;
            border: .5pt solid #e0ddd6;
            width: 33.33%;
            text-align: center;
        }
        .kpi-label {
            font-family: monospace;
            font-size: 7pt;
            letter-spacing: .12em;
            text-transform: uppercase;
            color: #aaa;
            margin-bottom: 4pt;
        }
        .kpi-value {
            font-family: monospace;
            font-size: 14pt;
            font-weight: bold;
        }
        .kpi-value.green { color: #166534; }
        .kpi-value.red   { color: #991b1b; }
        .kpi-value.blue  { color: #1e40af; }
        
        .kpi-bar {
            height: 2pt;
            background: #eee;
            margin-top: 7pt;
        }
        .kpi-bar-fill { height: 100%; }

        /* ─── Seções ─── */
        .section-table {
            width: 100%;
            margin-bottom: 10pt;
        }
        .section-pill {
            font-family: monospace;
            font-size: 8pt;
            letter-spacing: .1em;
            text-transform: uppercase;
            padding: 2pt 7pt;
            border-radius: 3pt;
        }
        .section-pill.green { background: #dcfce7; color: #166534; }
        .section-pill.red   { background: #fef2f2; color: #991b1b; }
        .section-name {
            font-size: 10pt;
            font-weight: bold;
            color: #1a1a1a;
        }
        .section-total {
            font-family: monospace;
            font-size: 12pt;
            font-weight: 500;
            text-align: right;
        }
        .section-total.green { color: #166534; }
        .section-total.red   { color: #991b1b; }

        /* ─── Tabelas ─── */
        table.data {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5pt;
            table-layout: fixed;
        }
        table.data thead th {
            font-family: monospace;
            font-size: 7pt;
            letter-spacing: .1em;
            text-transform: uppercase;
            color: #aaa;
            font-weight: 400;
            padding: 0 0 6pt;
            border-bottom: .5pt solid #ddd;
            text-align: left;
        }
        table.data thead th.right { text-align: right; }
        table.data tbody td {
            padding: 6.5pt 0;
            font-size: 9.5pt;
            border-bottom: .5pt solid #f2f2f2;
            color: #333;
        }
        table.data tbody tr:last-child td { border-bottom: none; }
        table.data td.right {
            text-align: right;
            font-family: monospace;
            font-size: 9pt;
        }
        table.data td.pct {
            text-align: right;
            font-family: monospace;
            font-size: 8pt;
            color: #888;
            width: 58pt;
        }
        .pct-alert-r { color: #991b1b; font-weight: 500; }
        .pct-alert-g { color: #166534; font-weight: 500; }
        .val-g { color: #166534; }
        .val-r { color: #991b1b; }

        .subtotal-table {
            width: 100%;
            border-top: 1pt solid #ddd;
            margin-bottom: 20pt;
        }
        .subtotal-label {
            font-family: monospace;
            font-size: 8pt;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: #999;
            padding: 10pt 0;
        }
        .subtotal-val {
            font-family: monospace;
            font-size: 11pt;
            font-weight: bold;
            text-align: right;
            padding: 10pt 0;
        }

        .divider { border-top: .5pt solid #e8e6e0; margin: 5pt 0 15pt; }

        /* ─── Resultado ─── */
        .resultado {
            width: 100%;
            border: 1pt solid #bfdbfe;
            background: #eff6ff;
            margin-top: 6pt;
        }
        .resultado.deficit { border-color: #fecaca; background: #fef2f2; }
        .res-inner { padding: 15pt; }
        .res-name {
            font-family: 'Courier New', Times, serif;
            font-size: 13pt;
            font-style: italic;
            color: #1a1a1a;
        }
        .res-badge {
            font-family: monospace;
            font-size: 7pt;
            padding: 2pt 7pt;
            background: #bfdbfe;
            color: #1e3a8a;
        }
        .res-badge.deficit { background: #fecaca; color: #7f1d1d; }
        .res-regime {
            font-family: monospace;
            font-size: 8pt;
            color: #666;
            margin-top: 3pt;
        }
        .res-value {
            font-family: monospace;
            font-size: 22pt;
            font-weight: bold;
            color: #1e40af;
            text-align: right;
            white-space: nowrap;
        }
        .res-value.deficit { color: #991b1b; }

        /* ─── Rodapé ─── */
        .doc-footer {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            padding-top: 5pt;
            border-top: .5pt solid #e8e6e0;
        }
        .doc-footer td {
            font-family: monospace;
            font-size: 7.5pt;
            color: #ccc;
            letter-spacing: .05em;
            text-transform: uppercase;
        }
    </style>
</head>
<body>

    <!-- Script PHP para Numeração de Páginas Dinâmica -->
    <script type="text/php">
        if (isset($pdf)) {
            $font = $fontMetrics->get_font("Helvetica", "normal");
            $size = 7.5;
            $y = $pdf->get_height() - 25; // Posição vertical (de baixo para cima)
            $x = $pdf->get_width() - 80;  // Posição horizontal (da direita para esquerda)
            $pdf->page_text($x, $y, "Página {PAGE_NUM} de {PAGE_COUNT}", $font, $size, array(0.8, 0.8, 0.8));
        }
    </script>

    <!-- Rodapé Fixo (Declarado no início para repetir em todas as páginas no Dompdf) -->
    <table class="doc-footer">
        <tr>
            <td>SysEnviCorp · Demonstrativo de Resultado</td>
            <td style="text-align:right"></td> <!-- O script PHP injetará a paginação aqui via coordenadas -->
        </tr>
    </table>

    <!-- ── Cabeçalho ── -->
    <table class="header-table">
        <tr>
            <td class="logo-box">
                <?php if ($logoBase64): ?>
                    <img src="<?= $logoBase64 ?>" class="logo-img" alt="Logo">
                <?php else: ?>
                    <div class="doc-company" style="color:#000"><strong><?= htmlspecialchars($empresa['razao_social'] ?? 'SysEnviCorp') ?></strong></div>
                <?php endif; ?>
            </td>
            <td class="header-info">
                <div class="doc-title">Demonstrativo de Resultado</div>
                <div class="doc-subtitle">Exercício <?= htmlspecialchars($anoSelecionado ?? date('Y')) ?> · Regime de <?= $regime === 'caixa' ? 'Caixa' : 'Competência' ?></div>
            </td>
            <td class="title-box">
                <div class="doc-meta">
                    <span class="meta-label">Data de geração</span><br>
                    <?= $dataGeracao ?>
                </div>
            </td>
        </tr>
    </table>

    <!-- KPIs -->
    <?php
    $margemW   = min(100, abs($margem));
    $despW     = min(100, $despPct);
    $margemCls = $margem >= 0 ? 'blue' : 'red';
    ?>
    <table class="kpi-grid">
        <tr>
            <td class="kpi-cell">
                <div class="kpi-label">Receita Bruta</div>
                <div class="kpi-value green"><?= dreShort($totR) ?></div>
                <div class="kpi-bar"><div class="kpi-bar-fill" style="width:100%;background:#166534"></div></div>
            </td>
            <td class="kpi-cell">
                <div class="kpi-label">Total Despesas</div>
                <div class="kpi-value red"><?= dreShort($totD) ?></div>
                <div class="kpi-bar"><div class="kpi-bar-fill" style="width:<?= $despW ?>%;background:#991b1b"></div></div>
            </td>
            <td class="kpi-cell">
                <div class="kpi-label">Margem Líquida</div>
                <div class="kpi-value <?= $margemCls ?>"><?= number_format($margem, 1, ',', '.') ?>%</div>
                <div class="kpi-bar"><div class="kpi-bar-fill" style="width:<?= $margemW ?>%;background:<?= $margem >= 0 ? '#1e40af' : '#991b1b' ?>"></div></div>
            </td>
        </tr>
    </table>

    <!-- Receitas -->
    <table class="section-table">
        <tr>
            <td style="width:60pt"><span class="section-pill green">Receitas</span></td>
            <td class="section-name">Receitas Operacionais</td>
            <td class="section-total green"><?= dreFmt($totR) ?></td>
        </tr>
    </table>

    <?php if (!empty($dreData['receitas'])): ?>
    <table class="data">
        <thead>
            <tr>
                <th>Categoria</th>
                <th class="right">Valor</th>
                <th class="right" style="width:58pt">% Rec.</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($dreData['receitas'] as $rec):
                $pct    = $totR > 0 ? ($rec['valor'] / $totR) * 100 : 0;
                $pctCls = $pct >= 25 ? 'pct-alert-g' : '';
            ?>
            <tr>
                <td><?= htmlspecialchars($rec['categoria']) ?></td>
                <td class="right val-g">+ <?= dreFmt($rec['valor']) ?></td>
                <td class="pct <?= $pctCls ?>"><?= number_format($pct, 1, ',', '.') ?>%</td>
            </tr>
            <?php endforeach ?>
        </tbody>
    </table>
    <?php else: ?>
        <p style="font-size:9pt;color:#aaa;font-style:italic;text-align:center;padding:14pt 0">Nenhuma receita registrada.</p>
    <?php endif; ?>

    <table class="subtotal-table">
        <tr>
            <td class="subtotal-label">Receita Bruta</td>
            <td class="subtotal-val" style="color:#166534"><?= dreFmt($totR) ?></td>
        </tr>
    </table>

    <div class="divider"></div>

    <!-- Despesas -->
    <table class="section-table">
        <tr>
            <td style="width:60pt"><span class="section-pill red">Despesas</span></td>
            <td class="section-name">Despesas Operacionais</td>
            <td class="section-total red">- <?= dreFmt($totD) ?></td>
        </tr>
    </table>

    <?php if (!empty($dreData['despesas'])): ?>
    <table class="data">
        <thead>
            <tr>
                <th>Categoria</th>
                <th class="right">Valor</th>
                <th class="right" style="width:58pt">% Rec.</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($dreData['despesas'] as $desp):
                $pct    = $totR > 0 ? ($desp['valor'] / $totR) * 100 : 0;
                $pctCls = $pct > 15 ? 'pct-alert-r' : '';
            ?>
            <tr>
                <td><?= htmlspecialchars($desp['categoria']) ?></td>
                <td class="right val-r">- <?= dreFmt($desp['valor']) ?></td>
                <td class="pct <?= $pctCls ?>"><?= number_format($pct, 1, ',', '.') ?>%</td>
            </tr>
            <?php endforeach ?>
        </tbody>
    </table>
    <?php else: ?>
        <p style="font-size:9pt;color:#aaa;font-style:italic;text-align:center;padding:14pt 0">Nenhuma despesa registrada.</p>
    <?php endif; ?>

    <table class="subtotal-table">
        <tr>
            <td class="subtotal-label">Total de Despesas</td>
            <td class="subtotal-val" style="color:#991b1b">- <?= dreFmt($totD) ?></td>
        </tr>
    </table>

    <!-- Resultado -->
    <table class="resultado <?= $surplus ? '' : 'deficit' ?>">
        <tr>
            <td class="res-inner">
                <div class="res-name">
                    <?= $surplus ? 'Lucro Líquido' : 'Prejuízo' ?>
                    <span class="res-badge <?= $surplus ? '' : 'deficit' ?>"><?= $surplus ? 'SUPERÁVIT' : 'DÉFICIT' ?></span>
                </div>
                <div class="res-regime">Regime de <?= $regime === 'caixa' ? 'Caixa' : 'Competência' ?> · <?= $nCats ?> categorias</div>
            </td>
            <td class="res-inner res-value <?= $surplus ? '' : 'deficit' ?>">
                <?= dreFmt($res) ?>
            </td>
        </tr>
    </table>

</body>
</html>
