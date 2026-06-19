<?php
$qrData = $bem['numero_patrimonio'] ?: $bem['id'];
$qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . urlencode($qrData);

// Prepara a logomarca em Base64 para garantir a exibição no Dompdf
$logoBase64 = '';
if (!empty($empresa['logo_path'])) {
    $fullPath = ROOT_PATH . '/public/uploads/logos/' . $empresa['logo_path'];
    if (file_exists($fullPath)) {
        $logoData = base64_encode(file_get_contents($fullPath));
        // Tenta detectar o tipo MIME para o cabeçalho do Base64
        $mime = function_exists('mime_content_type') ? mime_content_type($fullPath) : 'image/png';
        $logoBase64 = 'data:' . $mime . ';base64,' . $logoData;
    }
}
?>
<style>
    body { font-family: 'Helvetica', sans-serif; margin: 0; padding: 10px; background: #fff; }
    .etiqueta { border: 1px solid #eee; width: 100%; height: 100%; box-sizing: border-box; display: block; text-align: center; }
    .logo-container { margin-bottom: 2px; line-height: 0; }
    .header-logo { max-height: 25px; max-width: 180px; }
    .empresa { font-size: 7px; font-weight: bold; margin-bottom: 5px; color: #555; text-transform: uppercase; border-bottom: 1px solid #eee; padding-bottom: 3px; line-height: 1.2; }
    .qr-container { margin: 4px 0; }
    .qr-container img { width: 60px; height: 60px; }
    .nome { font-size: 11px; font-weight: bold; margin-bottom: 2px; color: #333; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .patrimonio { font-size: 14px; font-weight: 900; color: #000; margin: 2px 0; letter-spacing: 1px; }
    .footer { font-size: 6px; color: #aaa; margin-top: 6px; border-top: 1px solid #eee; padding-top: 2px; }
</style>

<div class="etiqueta">
    <?php if ($logoBase64): ?>
        <div class="logo-container">
            <img src="<?php echo $logoBase64; ?>" class="header-logo">
        </div>
    <?php endif; ?>

    <div class="empresa"><?php echo htmlspecialchars($empresa['razao_social'] ?? 'CONTROLE DE PATRIMÔNIO'); ?></div>

    <div class="qr-container">
        <img src="<?php echo $qrUrl; ?>" alt="QR Code">
    </div>

    <div class="nome"><?php echo htmlspecialchars($bem['nome']); ?></div>
    <div class="patrimonio"><?php echo htmlspecialchars($bem['numero_patrimonio'] ?: str_pad($bem['id'], 6, '0', STR_PAD_LEFT)); ?></div>

    <div class="footer">Gerado via SysEnviCorp em <?php echo date('d/m/Y'); ?></div>
</div>