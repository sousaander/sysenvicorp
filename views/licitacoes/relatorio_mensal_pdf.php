<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 10pt; color: #333; margin: 0; padding: 0; }
        .header { border-bottom: 2px solid #2563eb; padding-bottom: 10px; margin-bottom: 20px; }
        .header table { width: 100%; }
        .company-name { font-size: 14pt; font-weight: bold; color: #1e293b; }
        .report-title { font-size: 16pt; font-weight: bold; color: #2563eb; text-align: right; }
        
        .summary-box { background: #f8fafc; border: 1px solid #e2e8f0; padding: 15px; margin-bottom: 20px; border-radius: 5px; }
        .summary-box table { width: 100%; }
        .summary-label { font-size: 9pt; color: #64748b; text-transform: uppercase; }
        .summary-value { font-size: 12pt; font-weight: bold; color: #1e293b; }

        table.data-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.data-table th { background: #f1f5f9; color: #475569; font-size: 8pt; text-transform: uppercase; padding: 8px; border: 1px solid #e2e8f0; text-align: left; }
        table.data-table td { padding: 8px; border: 1px solid #e2e8f0; font-size: 8.5pt; vertical-align: top; }
        
        .status-badge { font-weight: bold; text-transform: uppercase; font-size: 7pt; }
        .footer { position: fixed; bottom: 0; width: 100%; font-size: 8pt; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 5px; text-align: center; }
        
        .text-right { text-align: right; }
        .bold { font-weight: bold; }
    </style>
</head>
<body>

    <div class="header">
        <table>
            <tr>
                <td>
                    <div class="company-name"><?= htmlspecialchars($empresa['razao_social'] ?? 'SysEnviCorp') ?></div>
                    <div style="font-size: 9pt; color: #64748b;">CNPJ: <?= htmlspecialchars($empresa['cnpj'] ?? '00.000.000/0000-00') ?></div>
                </td>
                <td class="report-title">
                    Consolidado de Editais<br>
                    <span style="font-size: 10pt; color: #64748b; font-weight: normal;">
                        <?= str_pad($mes, 2, '0', STR_PAD_LEFT) ?> / <?= $ano ?>
                        <?php if (!empty($categoria_filtro)): ?> · Categoria: <?= htmlspecialchars($categoria_filtro) ?><?php endif; ?>
                    </span>
                </td>
            </tr>
        </table>
    </div>

    <div class="summary-box">
        <table>
            <tr>
                <td>
                    <div class="summary-label">Total de Editais</div>
                    <div class="summary-value"><?= $total_registros ?></div>
                </td>
                <td>
                    <div class="summary-label">Volume Financeiro Estimado</div>
                    <div class="summary-value">R$ <?= number_format($valor_total, 2, ',', '.') ?></div>
                </td>
                <td class="text-right">
                    <div class="summary-label">Data de Emissão</div>
                    <div class="summary-value" style="font-weight: normal; font-size: 10pt;"><?= date('d/m/Y H:i') ?></div>
                </td>
            </tr>
        </table>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th width="10%">Número</th>
                <th width="20%">Órgão</th>
                <th width="40%">Objeto</th>
                <th width="10%">Sessão</th>
                <th width="10%">Valor Est.</th>
                <th width="10%">Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($lista)): ?>
                <?php foreach ($lista as $lic): ?>
                    <tr>
                        <td class="bold"><?= htmlspecialchars($lic['numero']) ?></td>
                        <td><?= htmlspecialchars($lic['orgao']) ?></td>
                        <td><?= mb_strimwidth(htmlspecialchars($lic['objeto']), 0, 150, '...') ?></td>
                        <td class="text-right"><?= date('d/m/Y', strtotime($lic['dt_sessao'])) ?></td>
                        <td class="text-right">R$ <?= number_format($lic['valor_estimado'], 2, ',', '.') ?></td>
                        <td>
                            <span class="status-badge"><?= strtoupper($lic['status']) ?></span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" style="text-align: center; padding: 20px; color: #94a3b8;">
                        Nenhum edital encontrado para o período selecionado.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="footer">
        Documento gerado eletronicamente pelo Sistema de Gestão SysEnviCorp · Página 1 de 1
    </div>

</body>
</html>