<?php
$funcionario = $calculo['funcionario'];
$periodo = $calculo['periodo'];
$valores = $calculo['valores'];

// Prepara o logo em Base64
$logoPath = ROOT_PATH . '/public/assets/images/logo.png';
$logoSrc = '';
if (file_exists($logoPath) && extension_loaded('gd')) {
    $logoSrc = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
}
$empresa = $empresa ?? []; // Garante que a variável exista
require_once ROOT_PATH . '/app/helpers/ReportHelper.php';

use App\Helpers\ReportHelper;
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
        <?php if (!empty($logoSrc)): ?>
            <div style="text-align: center; margin-bottom: 20px;"><img src="<?php echo $logoSrc; ?>" alt="Logo" style="max-height: 60px;"></div>
        <?php endif; ?>
        <h1><?php echo htmlspecialchars($empresa['razao_social'] ?? 'Empresa'); ?></h1>
        <p style="text-align: center; font-size: 12px; margin-top: -20px; margin-bottom: 20px;">
            Relatório de Cálculo de Férias
        </p>

        <div class="section">
            <h2>Dados do Funcionário e Período</h2>
            <p><strong>Funcionário:</strong> <?php echo htmlspecialchars($funcionario['nome']); ?></p>
            <p><strong>Período de Gozo:</strong> de <?php echo ReportHelper::formatDate($periodo['data_inicio']); ?> a <?php echo ReportHelper::formatDate($periodo['data_fim']); ?> (<?php echo $periodo['dias']; ?> dias)</p>
        </div>

        <div class="section">
            <h2>Demonstrativo de Pagamento</h2>
            <table>
                <tr>
                    <td>Salário Base</td>
                    <td class="text-right"><?php echo ReportHelper::formatCurrency($valores['salario_base']); ?></td>
                </tr>
                <tr>
                    <td>(+) Valor das Férias</td>
                    <td class="text-right"><?php echo ReportHelper::formatCurrency($valores['valor_ferias']); ?></td>
                </tr>
                <tr>
                    <td>(+) 1/3 Constitucional</td>
                    <td class="text-right"><?php echo ReportHelper::formatCurrency($valores['terco_constitucional']); ?></td>
                </tr>
                <tr class="total-row">
                    <td>Total Bruto</td>
                    <td class="text-right"><?php echo ReportHelper::formatCurrency($valores['total_bruto']); ?></td>
                </tr>
                <tr>
                    <td>(-) Desconto INSS</td>
                    <td class="text-right"><?php echo ReportHelper::formatCurrency($valores['inss']); ?></td>
                </tr>
                <tr>
                    <td>(-) Desconto IRRF</td>
                    <td class="text-right"><?php echo ReportHelper::formatCurrency($valores['irrf']); ?></td>
                </tr>
                <tr class="total-row">
                    <td>Total de Descontos</td>
                    <td class="text-right"><?php echo ReportHelper::formatCurrency($valores['total_descontos']); ?></td>
                </tr>
                <tr class="total-row total-liquido">
                    <td>Líquido a Receber</td>
                    <td class="text-right"><?php echo ReportHelper::formatCurrency($valores['valor_liquido']); ?></td>
                </tr>
            </table>
        </div>
    </div>
</body>

</html>