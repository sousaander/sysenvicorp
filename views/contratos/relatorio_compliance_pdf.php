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
    </style>
</head>

<body>
    <div class="header">
        <h1><?php echo htmlspecialchars($pageTitle); ?></h1>
        <p>Gerado em: <?php echo htmlspecialchars($dataGeracao); ?></p>
    </div>

    <?php if (empty($contratos)) : ?>
        <p style="text-align: center; margin-top: 50px;">Nenhum contrato encontrado para este critério.</p>
    <?php else : ?>
        <table>
            <thead>
                <tr>
                    <th style="width: 5%;">ID</th>
                    <th style="width: 35%;">Objeto do Contrato</th>
                    <th style="width: 25%;">Parte Contratada</th>
                    <th style="width: 15%;">Tipo</th>
                    <th style="width: 10%;">LGPD</th>
                    <th style="width: 10%;">Risco</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($contratos as $contrato) : ?>
                    <tr>
                        <td><?php echo $contrato['id']; ?></td>
                        <td><?php echo htmlspecialchars($contrato['objeto']); ?></td>
                        <td><?php echo htmlspecialchars($contrato['parteContratada'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($contrato['tipo']); ?></td>
                        <td><?php echo htmlspecialchars($contrato['clausula_lgpd'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($contrato['risco_contratual'] ?? 'N/A'); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <div class="footer">
        Página <span class="page-number"></span>
    </div>
</body>

</html>