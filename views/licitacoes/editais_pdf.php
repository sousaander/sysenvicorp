<?php
/**
 * View: Relatório de Editais em PDF
 * Foco: Layout para impressão/visualização do relatório de editais.
 */
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
            font-size: 10pt;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 18pt;
            color: #2563eb;
        }
        .header p {
            margin: 5px 0 0;
            font-size: 10pt;
            color: #666;
        }
        .report-info {
            margin-bottom: 20px;
            font-size: 10pt;
        }
        .report-info strong {
            color: #222;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
            color: #333;
            font-size: 9pt;
        }
        td {
            font-size: 9pt;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 8pt;
            color: #999;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }
        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 8pt;
            font-weight: bold;
            text-transform: uppercase;
            background-color: #e0e7ff; /* Light blue for general status */
            color: #2563eb; /* Blue for general status */
        }
        .status-badge.publicada { background-color: #e0e7ff; color: #2563eb; }
        .status-badge.aberta { background-color: #d1fae5; color: #10b981; }
        .status-badge.rascunho { background-color: #f3f4f6; color: #6b7280; }
        .status-badge.concluida { background-color: #e2e8f0; color: #475569; }
    </style>
</head>
<body>
    <div class="header">
        <h1><?= $pageTitle ?></h1>
        <p>Mês de Referência: <?= strftime('%B', mktime(0, 0, 0, $mes, 1)) ?> de <?= $ano ?></p>
    </div>

    <div class="report-info">
        <strong>Data de Geração:</strong> <?= $dataGeracao ?><br>
        <strong>Total de Editais:</strong> <?= count($editais) ?>
    </div>

    <table>
        <thead>
            <tr>
                <th>Número</th>
                <th>Órgão</th>
                <th>Objeto</th>
                <th>Data Sessão</th>
                <th>Valor Estimado</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($editais as $lic): ?>
                <tr>
                    <td><?= htmlspecialchars($lic['numero']) ?></td>
                    <td><?= htmlspecialchars($lic['orgao']) ?></td>
                    <td><?= htmlspecialchars($lic['objeto']) ?></td>
                    <td><?= date('d/m/Y', strtotime($lic['dt_sessao'])) ?></td>
                    <td>R$ <?= number_format($lic['valor_estimado'], 2, ',', '.') ?></td>
                    <td><span class="status-badge <?= strtolower(str_replace(' ', '_', $lic['status'])) ?>"><?= htmlspecialchars($lic['status']) ?></span></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="footer">
        Relatório gerado por SysEnviCorp - Módulo de Licitações
    </div>
</body>
</html>