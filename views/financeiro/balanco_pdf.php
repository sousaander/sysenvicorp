<?php
// views/financeiro/balanco_pdf.php
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($pageTitle); ?></title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 10pt;
            color: #333;
        }

        h1 {
            text-align: center;
            margin-bottom: 5px;
            font-size: 16pt;
        }

        p.subtitle {
            text-align: center;
            color: #666;
            font-size: 9pt;
            margin-top: 0;
            margin-bottom: 20px;
        }

        /* Cards de Resumo */
        .resumo-cards {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }

        .resumo-cards td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
            width: 33%;
            background-color: #f9fafb;
        }

        .resumo-label {
            font-size: 8pt;
            color: #666;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .resumo-valor {
            font-size: 12pt;
            font-weight: bold;
        }

        /* Cores */
        .text-blue {
            color: #2563eb;
        }

        .text-green {
            color: #10b981;
        }

        .text-red {
            color: #ef4444;
        }

        .text-purple {
            color: #9333ea;
        }

        /* Tabela Detalhada */
        table.detalhes {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        table.detalhes th,
        table.detalhes td {
            border: 1px solid #ccc;
            padding: 6px 8px;
            text-align: right;
            font-size: 9pt;
        }

        table.detalhes th {
            background-color: #e5e7eb;
            text-align: center;
            font-weight: bold;
            color: #374151;
        }

        table.detalhes td:first-child {
            text-align: left;
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
    <h1><?= htmlspecialchars($pageTitle); ?></h1>
    <p class="subtitle">Gerado em: <?= $dataGeracao; ?></p>

    <!-- Resumo -->
    <table class="resumo-cards">
        <tr>
            <td>
                <div class="resumo-label">Saldo Atual (Caixa)</div>
                <div class="resumo-valor text-blue">R$ <?= number_format($saldoAtual, 2, ',', '.'); ?></div>
            </td>
            <td>
                <div class="resumo-label">Resultado do Ano (<?= $anoSelecionado ?>)</div>
                <?php
                $totalReceitas = array_sum(array_column($balancoMensal, 'receitas_realizadas'));
                $totalDespesas = array_sum(array_column($balancoMensal, 'despesas_realizadas'));
                $resultadoPeriodo = $totalReceitas - $totalDespesas;
                ?>
                <div class="resumo-valor <?= $resultadoPeriodo >= 0 ? 'text-purple' : 'text-red'; ?>">
                    <?= $resultadoPeriodo >= 0 ? '+' : ''; ?> R$ <?= number_format($resultadoPeriodo, 2, ',', '.'); ?>
                </div>
            </td>
            <td>
                <div class="resumo-label">Projeção (Próx. 12 Meses)</div>
                <div class="resumo-valor <?= $saldoProjetado >= 0 ? 'text-green' : 'text-red'; ?>">
                    R$ <?= number_format($saldoProjetado, 2, ',', '.'); ?>
                </div>
            </td>
        </tr>
    </table>

    <!-- Tabela -->
    <h3 style="margin-bottom: 5px; font-size: 12pt;">Detalhamento Mensal</h3>
    <table class="detalhes">
        <thead>
            <tr>
                <th>Mês/Ano</th>
                <th>Receitas (Real)</th>
                <th>Despesas (Real)</th>
                <th>Resultado (Real)</th>
                <th>Previsão (Saldo)</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $meses_pt = [1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril', 5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto', 9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'];

            if (!empty($balancoMensal)):
                foreach ($balancoMensal as $mes):
                    $resultadoReal = $mes['receitas_realizadas'] - $mes['despesas_realizadas'];
                    $resultadoPrevisto = $mes['receitas_previstas'] - $mes['despesas_previstas'];
                    $ts = strtotime($mes['mes'] . '-01');
                    $mesNome = $meses_pt[(int)date('n', $ts)] . ' ' . date('Y', $ts);
            ?>
                    <tr>
                        <td><?= $mesNome; ?></td>
                        <td class="text-green">+ <?= number_format($mes['receitas_realizadas'], 2, ',', '.'); ?></td>
                        <td class="text-red">- <?= number_format($mes['despesas_realizadas'], 2, ',', '.'); ?></td>
                        <td style="font-weight: bold; color: <?= $resultadoReal >= 0 ? '#2563eb' : '#ef4444'; ?>">
                            <?= number_format($resultadoReal, 2, ',', '.'); ?>
                        </td>
                        <td style="color: #6b7280;">
                            <?= number_format($resultadoPrevisto, 2, ',', '.'); ?>
                        </td>
                    </tr>
                <?php endforeach;
            else: ?>
                <tr>
                    <td colspan="5" style="text-align: center; padding: 20px;">Nenhum dado encontrado para o período.</td>
                </tr>
            <?php endif; ?>
        </tbody>
        <tfoot>
            <tr style="background-color: #f3f4f6; font-weight: bold;">
                <td>Total do Período</td>
                <td class="text-green"><?= number_format($totalReceitas, 2, ',', '.'); ?></td>
                <td class="text-red"><?= number_format($totalDespesas, 2, ',', '.'); ?></td>
                <td style="color: <?= $resultadoPeriodo >= 0 ? '#2563eb' : '#ef4444'; ?>">
                    <?= number_format($resultadoPeriodo, 2, ',', '.'); ?>
                </td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        SysEnviCorp - Sistema de Gestão | Página 1
    </div>
</body>

</html>