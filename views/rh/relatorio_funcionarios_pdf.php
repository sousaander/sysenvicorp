<?php
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
    <title>Relatório de Funcionários</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10px;
            color: #333;
        }

        .header {
            /* text-align: center; */
            margin-bottom: 20px;
            border-bottom: 1px solid #ccc;
            padding-bottom: 10px;
            /* Flexbox for alignment */
            display: table;
            width: 100%;
        }

        .header h1 {
            font-size: 16px;
            margin: 0;
        }

        .header .logo {
            display: table-cell;
            width: 25%;
            vertical-align: middle;
        }

        .header .company-info {
            display: table-cell;
            width: 75%;
            text-align: right;
            vertical-align: middle;
        }

        .header p {
            font-size: 12px;
            margin: 5px 0;
            color: #666;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        .status-ativo {
            color: green;
            font-weight: bold;
        }

        .status-inativo {
            color: red;
            font-weight: bold;
        }

        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            font-size: 9px;
            text-align: center;
            color: #999;
            border-top: 1px solid #eee;
            padding-top: 5px;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="logo">
            <?php if (!empty($logoSrc)): ?>
                <img src="<?php echo $logoSrc; ?>" alt="Logo" style="max-height: 50px;">
            <?php endif; ?>
        </div>
        <div class="company-info">
            <h1><?php echo htmlspecialchars($empresa['razao_social'] ?? 'Relatório Geral de Funcionários'); ?></h1>
            <p>Filtro: <?php echo htmlspecialchars($filtroStatus); ?> | Gerado em: <?php echo ReportHelper::formatDateTime(date('Y-m-d H:i:s')); ?></p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Nome</th>
                <th>CPF</th>
                <th>Cargo</th>
                <th>Setor</th>
                <th>Data Admissão</th>
                <th>E-mail</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($funcionarios)): ?>
                <?php foreach ($funcionarios as $func): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($func['nome']); ?></td>
                        <td><?php echo ReportHelper::formatCpfCnpj($func['cpf'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($func['cargo']); ?></td>
                        <td><?php echo htmlspecialchars($func['setor']); ?></td>
                        <td><?php echo !empty($func['data_admissao']) ? ReportHelper::formatDate($func['data_admissao']) : '-'; ?></td>
                        <td><?php echo htmlspecialchars($func['email']); ?></td>
                        <td class="<?php echo strtolower($func['status']) === 'ativo' ? 'status-ativo' : 'status-inativo'; ?>">
                            <?php echo htmlspecialchars($func['status']); ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" style="text-align: center;">Nenhum funcionário encontrado.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="footer">
        SysEnviCorp - Sistema de Gestão Integrado
    </div>
</body>

</html>