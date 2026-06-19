<?php
// Prepara a logomarca em Base64 para garantir a exibição no PDF independente do ambiente (Windows/Linux)
$logoBase64 = '';
if (!empty($empresa['logo_path'])) {
    $logoPath = ROOT_PATH . '/public/uploads/logos/' . $empresa['logo_path'];
    if (file_exists($logoPath)) {
        $logoData = base64_encode(file_get_contents($logoPath));
        $mime = function_exists('mime_content_type') ? mime_content_type($logoPath) : 'image/png';
        $logoBase64 = 'data:' . $mime . ';base64,' . $logoData;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; margin: 0; padding: 0; }
        .header { text-align: center; border-bottom: 2px solid #0284c7; padding-bottom: 10px; margin-bottom: 20px; }
        .header h1 { margin: 0; color: #0284c7; font-size: 20px; }
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .info-table td { padding: 5px; border: 1px solid #ddd; }
        .info-label { font-weight: bold; background: #f9fafb; width: 150px; }
        .attendance-table { width: 100%; border-collapse: collapse; }
        .attendance-table th, .attendance-table td { border: 1px solid #333; padding: 8px; text-align: left; }
        .attendance-table th { background: #f3f4f6; font-weight: bold; }
        .signature-cell { width: 250px; height: 35px; border-bottom: 1px solid #333; }
        .footer { position: fixed; bottom: 30px; left: 0; right: 0; text-align: center; font-size: 10px; color: #9ca3af; }
    </style>
</head>
<body>
    <div class="header">
        <?php if ($logoBase64): ?>
            <img src="<?php echo $logoBase64; ?>" style="max-height: 50px; margin-bottom: 5px;">
        <?php endif; ?>
        <h1>Lista de Presença - Treinamento</h1>
        <p><?php echo htmlspecialchars($empresa['razao_social'] ?? 'SysEnviCorp'); ?></p>
    </div>

    <table class="info-table">
        <tr>
            <td class="info-label">Treinamento:</td>
            <td><?php echo htmlspecialchars($treinamento['nome_treinamento']); ?></td>
        </tr>
        <tr>
            <td class="info-label">Data e Hora:</td>
            <td><?php echo date('d/m/Y H:i', strtotime($treinamento['data_prevista'])); ?></td>
        </tr>
        <tr>
            <td class="info-label">Instrutor:</td>
            <td><?php echo htmlspecialchars($treinamento['instrutor'] ?? '—'); ?></td>
        </tr>
        <tr>
            <td class="info-label">Local:</td>
            <td><?php echo htmlspecialchars($treinamento['local'] ?? '—'); ?></td>
        </tr>
    </table>

    <table class="attendance-table">
        <thead>
            <tr>
                <th style="width: 30px;">Nº</th>
                <th>Colaborador</th>
                <th>Departamento</th>
                <th style="width: 200px;">Assinatura</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($treinamento['participantes'])): ?>
                <?php foreach ($treinamento['participantes'] as $index => $p): ?>
                    <tr>
                        <td><?php echo $index + 1; ?></td>
                        <td><?php echo htmlspecialchars($p['nome']); ?></td>
                        <td><?php echo htmlspecialchars($p['departamento'] ?? '—'); ?></td>
                        <td class="signature-cell"></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" style="text-align: center; padding: 20px;">Nenhum participante vinculado a este treinamento.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="footer">
        Gerado em <?php echo $dataGeracao; ?> | SysEnviCorp - Gestão Ambiental e Corporativa
    </div>
</body>
</html>