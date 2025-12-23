<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            margin: 20mm;
            font-size: 10pt;
            color: #333;
        }

        h1,
        h2,
        h3 {
            color: #222;
            margin-top: 0;
        }

        h1 {
            font-size: 18pt;
            border-bottom: 1px solid #ccc;
            padding-bottom: 5px;
        }

        h2 {
            font-size: 14pt;
        }

        h3 {
            font-size: 12pt;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        th,
        td {
            border: 1px solid #eee;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f5f5f5;
            font-weight: bold;
            font-size: 9pt;
        }

        td {
            font-size: 9pt;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .text-left {
            text-align: left;
        }

        .color-green {
            color: #10B981;
        }

        /* Tailwind emerald-600 */
        .color-red {
            color: #EF4444;
        }

        /* Tailwind red-600 */
        .bg-green-light {
            background-color: #D1FAE5;
        }

        /* Tailwind emerald-100 */
        .bg-red-light {
            background-color: #FEE2E2;
        }

        /* Tailwind red-100 */
        .bg-yellow-light {
            background-color: #FEF3C7;
        }

        /* Tailwind yellow-100 */
        .bg-gray-light {
            background-color: #F3F4F6;
        }

        /* Tailwind gray-100 */
        .summary-table td {
            font-weight: bold;
            background-color: #f0f0f0;
        }

        .footer {
            position: fixed;
            bottom: 10mm;
            left: 20mm;
            right: 20mm;
            text-align: right;
            font-size: 8pt;
            color: #777;
        }
    </style>
</head>

<body>
    <h1><?php echo htmlspecialchars($pageTitle); ?></h1>
    <p><strong>Data de Geração:</strong> <?php echo htmlspecialchars($dataGeracao); ?></p>
    <?php if ($filtros['tipo_relatorio'] === 'banco' && $bancoSelecionado): ?>
        <p><strong>Conta Bancária:</strong> <?php echo htmlspecialchars($bancoSelecionado); ?></p>
    <?php endif; ?>
    <p><strong>Período:</strong>
        <?php
        if ($filtros['periodo'] === 'dia' && $filtros['data_unica']) {
            echo date('d/m/Y', strtotime($filtros['data_unica']));
        } elseif ($filtros['periodo'] === 'mes' && $filtros['mes_ano']) {
            echo date('m/Y', strtotime($filtros['mes_ano'] . '-01'));
        } elseif ($filtros['periodo'] === 'intervalo' && $filtros['data_inicio'] && $filtros['data_fim']) {
            echo date('d/m/Y', strtotime($filtros['data_inicio'])) . ' a ' . date('d/m/Y', strtotime($filtros['data_fim']));
        } else {
            echo 'Todas as movimentações recentes';
        }
        ?>
    </p>

    <?php if (!empty($transacoes)): ?>
        <table>
            <thead>
                <tr>
                    <th>Conta</th>
                    <th class="text-left">Descrição</th>
                    <th>Pago Em</th>
                    <th>Tipo</th>
                    <th class="text-right">Valor</th>
                </tr>
            </thead>
            <tbody>
                <?php $totalReceitas = 0;
                $totalDespesas = 0; ?>
                <?php foreach ($transacoes as $transacao):
                    $valorSign = '';
                    $tipoLabel = get_tipo_transacao_texto($transacao['tipo']);

                    // Lógica simplificada usando o helper
                    $transferType = get_transfer_type($transacao);

                    if ($transferType === 'out') {
                        $valorSign = '-';
                        $tipoLabel = 'Transferência (Saída)';
                        $totalDespesas += $transacao['valor'];
                    } elseif ($transferType === 'in') {
                        $valorSign = '+';
                        $tipoLabel = 'Transferência (Entrada)';
                        $totalReceitas += $transacao['valor'];
                    } elseif ($transacao['tipo'] === 'P') {
                        $valorSign = '-';
                        $tipoLabel = 'Despesa';
                        $totalDespesas += $transacao['valor'];
                    } elseif ($transacao['tipo'] === 'R') {
                        $tipoLabel = 'Receita';
                        $valorSign = '+';
                        $totalReceitas += $transacao['valor'];
                    }
                ?>
                    <tr>
                        <td><?php echo htmlspecialchars($transacao['nome_banco'] ?? 'N/A'); ?></td>
                        <td class="text-left"><?php echo htmlspecialchars($transacao['descricao']); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($transacao['data'])); ?></td>
                        <td class="<?php echo $valorSign === '-' ? 'color-red' : 'color-green'; ?>"><?php echo htmlspecialchars($tipoLabel); ?></td>
                        <td class="text-right <?php echo $valorSign === '-' ? 'color-red' : 'color-green'; ?>"><?php echo $valorSign . 'R$ ' . number_format($transacao['valor'], 2, ',', '.'); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php
        $saldoFinal = $totalReceitas - $totalDespesas;
        ?>
        <table class="summary-table">
            <tr>
                <td colspan="4" class="text-right">Total Receitas:</td>
                <td class="text-right color-green">R$ <?php echo number_format($totalReceitas, 2, ',', '.'); ?></td>
            </tr>
            <tr>
                <td colspan="4" class="text-right">Total Despesas:</td>
                <td class="text-right color-red">R$ <?php echo number_format($totalDespesas, 2, ',', '.'); ?></td>
            </tr>
            <tr>
                <td colspan="4" class="text-right"><strong>Saldo do Período:</strong></td>
                <td class="text-right <?php echo $saldoFinal >= 0 ? 'color-green' : 'color-red'; ?>"><strong>R$ <?php echo number_format($saldoFinal, 2, ',', '.'); ?></strong></td>
            </tr>
        </table>
    <?php else: ?>
        <p>Nenhuma transação encontrada para os filtros selecionados.</p>
    <?php endif; ?>

    <div class="footer">
        Página 1 de 1
    </div>
</body>

</html>