<?php
$funcionario = $calculo['funcionario'];
$periodo = $calculo['periodo'];
$valores = $calculo['valores'];
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Relatório de Cálculo de Férias</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 11px;
            color: #333;
        }

        .container {
            width: 95%;
            margin: 0 auto;
        }

        h1 {
            text-align: center;
            font-size: 16px;
            border-bottom: 1px solid #ccc;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .section {
            border: 1px solid #eee;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
        }

        .section h2 {
            font-size: 13px;
            margin-top: 0;
            margin-bottom: 10px;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 6px;
            text-align: left;
            border-bottom: 1px solid #f0f0f0;
        }

        .text-right {
            text-align: right;
        }

        .total-row {
            font-weight: bold;
            background-color: #f9f9f9;
        }

        .total-liquido {
            font-size: 14px;
            background-color: #e8f5e9;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Relatório de Cálculo de Férias</h1>

        <div class="section">
            <h2>Dados do Funcionário e Período</h2>
            <p><strong>Funcionário:</strong> <?php echo htmlspecialchars($funcionario['nome']); ?></p>
            <p><strong>Período de Gozo:</strong> de <?php echo date('d/m/Y', strtotime($periodo['data_inicio'])); ?> a <?php echo date('d/m/Y', strtotime($periodo['data_fim'])); ?> (<?php echo $periodo['dias']; ?> dias)</p>
        </div>

        <div class="section">
            <h2>Demonstrativo de Pagamento</h2>
            <table>
                <tr>
                    <td>Salário Base</td>
                    <td class="text-right">R$ <?php echo number_format($valores['salario_base'], 2, ',', '.'); ?></td>
                </tr>
                <tr>
                    <td>(+) Valor das Férias</td>
                    <td class="text-right">R$ <?php echo number_format($valores['valor_ferias'], 2, ',', '.'); ?></td>
                </tr>
                <tr>
                    <td>(+) 1/3 Constitucional</td>
                    <td class="text-right">R$ <?php echo number_format($valores['terco_constitucional'], 2, ',', '.'); ?></td>
                </tr>
                <tr class="total-row">
                    <td>Total Bruto</td>
                    <td class="text-right">R$ <?php echo number_format($valores['total_bruto'], 2, ',', '.'); ?></td>
                </tr>
                <tr>
                    <td>(-) Desconto INSS</td>
                    <td class="text-right">R$ <?php echo number_format($valores['inss'], 2, ',', '.'); ?></td>
                </tr>
                <tr>
                    <td>(-) Desconto IRRF</td>
                    <td class="text-right">R$ <?php echo number_format($valores['irrf'], 2, ',', '.'); ?></td>
                </tr>
                <tr class="total-row">
                    <td>Total de Descontos</td>
                    <td class="text-right">R$ <?php echo number_format($valores['total_descontos'], 2, ',', '.'); ?></td>
                </tr>
                <tr class="total-row total-liquido">
                    <td>Líquido a Receber</td>
                    <td class="text-right">R$ <?php echo number_format($valores['valor_liquido'], 2, ',', '.'); ?></td>
                </tr>
            </table>
        </div>
    </div>
</body>

</html>