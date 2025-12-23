<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10px;
            color: #333;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 1px solid #ccc;
            padding-bottom: 10px;
        }

        .header h1 {
            margin: 0;
            font-size: 18px;
        }

        .header p {
            margin: 5px 0 0;
            font-size: 12px;
            color: #666;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 9px;
            color: #999;
        }

        .text-right {
            text-align: right;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1><?php echo htmlspecialchars($pageTitle); ?></h1>
        <p>Gerado em: <?php echo htmlspecialchars($dataGeracao); ?></p>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Objeto do Contrato</th>
                <th>Parte Contratada</th>
                <th>Tipo</th>
                <th class="text-right">Valor (R$)</th>
                <th>Início</th>
                <th>Vencimento</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($contratos as $contrato) : ?>
                <tr>
                    <td><?php echo $contrato['id']; ?></td>
                    <td><?php echo htmlspecialchars($contrato['objeto']); ?></td>
                    <td><?php echo htmlspecialchars($contrato['parteContratada'] ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($contrato['tipo']); ?></td>
                    <td class="text-right"><?php echo number_format($contrato['valor'], 2, ',', '.'); ?></td>
                    <td><?php echo date('d/m/Y', strtotime($contrato['data_inicio'])); ?></td>
                    <td><?php echo $contrato['vencimento'] ? date('d/m/Y', strtotime($contrato['vencimento'])) : 'Indeterminado'; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="footer">
        Página <span class="page-number"></span>
    </div>
</body>

</html>