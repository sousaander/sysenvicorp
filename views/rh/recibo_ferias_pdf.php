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
    <title>Recibo de Pagamento de Férias</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 12px;
            line-height: 1.6;
            color: #333;
        }

        .container {
            width: 90%;
            margin: 0 auto;
        }

        h1 {
            text-align: center;
            font-size: 18px;
            border-bottom: 1px solid #ccc;
            padding-bottom: 10px;
            margin-bottom: 30px;
        }

        p {
            margin-bottom: 15px;
            text-align: justify;
        }

        .signature-line {
            margin-top: 80px;
            border-top: 1px solid #000;
            width: 350px;
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            margin-bottom: 20px;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        .text-right {
            text-align: right;
        }
    </style>
</head>

<body>
    <div class="container">
        <?php if (!empty($logoSrc)): ?>
            <div style="text-align: center; margin-bottom: 20px;"><img src="<?php echo $logoSrc; ?>" alt="Logo" style="max-height: 60px;"></div>
        <?php endif; ?>
        <h1>RECIBO DE PAGAMENTO DE FÉRIAS</h1>

        <p>Eu, <strong><?php echo htmlspecialchars($funcionario['nome']); ?></strong>, funcionário(a) da empresa <strong><?php echo htmlspecialchars($empresa['razao_social'] ?? 'EnviCorp Soluções Ambientais LTDA'); ?></strong>, declaro ter recebido a importância líquida de <strong><?php echo ReportHelper::formatCurrency($valores['valor_liquido']); ?> (<?php echo valorPorExtenso($valores['valor_liquido']); ?>)</strong>, referente ao pagamento das minhas férias.</p>

        <p>O período de gozo das férias será de <strong><?php echo ReportHelper::formatDate($periodo['data_inicio']); ?></strong> a <strong><?php echo ReportHelper::formatDate($periodo['data_fim']); ?></strong>, totalizando <?php echo htmlspecialchars($periodo['dias']); ?> dias.</p>

        <p>Por ser a expressão da verdade, firmo o presente recibo para que produza seus devidos e legais efeitos.</p>

        <p style="text-align: right; margin-top: 40px;">Cidade, <?php echo date('d \d\e F \d\e Y'); ?>.</p>

        <div style="margin-top: 100px; text-align: center;">
            <div class="signature-line" style="margin: 0 auto;"><?php echo htmlspecialchars($funcionario['nome']); ?></div>
            <p style="text-align: center;">Assinatura do(a) Funcionário(a)</p>
        </div>
    </div>
</body>

</html>