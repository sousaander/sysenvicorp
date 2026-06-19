<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 10pt;
            color: #333;
        }

        h1 {
            text-align: center;
            font-size: 16pt;
            margin-bottom: 5px;
        }

        .subtitle {
            text-align: center;
            color: #666;
            font-size: 9pt;
            margin-bottom: 20px;
        }

        .project-info {
            margin-bottom: 15px;
            padding: 10px;
            background-color: #f9fafb;
            border: 1px solid #eee;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 6px;
            text-align: left;
            font-size: 9pt;
        }

        th {
            background-color: #f3f4f6;
            font-weight: bold;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .total-row td {
            background-color: #e5e7eb;
            font-weight: bold;
        }

        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 8pt;
            color: #999;
            border-top: 1px solid #eee;
            padding-top: 5px;
        }
    </style>
</head>

<body>
    <h1>Relatório de Prestação de Contas</h1>
    <p class="subtitle">Gerado em: <?php echo $dataGeracao; ?></p>

    <div class="project-info">
        <strong>Projeto:</strong> <?php echo htmlspecialchars($projeto['nome']); ?><br>
        <strong>Cliente:</strong> <?php echo htmlspecialchars($projeto['nome_cliente'] ?? 'N/A'); ?><br>
        <strong>Status do Projeto:</strong> <?php echo htmlspecialchars($projeto['status']); ?>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 80px;">Data</th>
                <th>Descrição</th>
                <th>Categoria</th>
                <th>Fornecedor</th>
                <th>Local (Cidade/UF)</th>
                <th class="text-right" style="width: 100px;">Valor (R$)</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $totalValor = 0;
            $totaisPorCategoria = [];
            if (!empty($transacoes)):
                foreach ($transacoes as $t):
                    $totalValor += $t['valor'];
                    
                    $cat = $t['nome_classificacao'] ?? 'Sem Categoria';
                    if (!isset($totaisPorCategoria[$cat])) {
                        $totaisPorCategoria[$cat] = 0;
                    }
                    $totaisPorCategoria[$cat] += $t['valor'];
            ?>
                    <tr>
                        <td><?php echo date('d/m/Y', strtotime($t['data'])); ?></td>
                        <td><?php echo htmlspecialchars($t['descricao']); ?></td>
                        <td><?php echo htmlspecialchars($t['nome_classificacao'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($t['fornecedor'] ?: '-'); ?></td>
                        <td><?php echo htmlspecialchars($t['local_despesa'] ?: '-'); ?></td>
                        <td class="text-right"><?php echo number_format($t['valor'], 2, ',', '.'); ?></td>
                    </tr>
                <?php endforeach;
            else: ?>
                <tr>
                    <td colspan="6" class="text-center">Nenhuma despesa aprovada encontrada para este projeto.</td>
                </tr>
            <?php endif; ?>
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="5" class="text-right">Total Reembolsável:</td>
                <td class="text-right">R$ <?php echo number_format($totalValor, 2, ',', '.'); ?></td>
            </tr>
        </tfoot>
    </table>

    <?php if (!empty($totaisPorCategoria)): ?>
        <div style="margin-top: 20px; width: 60%;">
            <h3 style="font-size: 12pt; margin-bottom: 10px;">Resumo por Categoria</h3>
            <table>
                <thead>
                    <tr>
                        <th>Categoria</th>
                        <th class="text-right">Total (R$)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($totaisPorCategoria as $categoria => $valor): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($categoria); ?></td>
                            <td class="text-right">R$ <?php echo number_format($valor, 2, ',', '.'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <div style="margin-top: 50px;">
        <table style="border: none;">
            <tr style="border: none;">
                <td style="border: none; text-align: center; width: 50%;">
                    __________________________________________<br>
                    Aprovador Responsável
                </td>
                <td style="border: none; text-align: center; width: 50%;">
                    __________________________________________<br>
                    Solicitante
                </td>
            </tr>
        </table>
    </div>

    <div class="footer">SysEnviCorp - Gestão de Projetos</div>
</body>

</html>