<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 11px; color: #333; margin: 0; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #0A6EBD; padding-bottom: 10px; }
        .header h1 { margin: 0; color: #0A6EBD; font-size: 18px; }
        .company-info { font-size: 10px; color: #666; margin-top: 5px; }
        .table { width: 100%; border-collapse: collapse; margin-top: 10px; table-layout: fixed; }
        .table th { background: #F5F6F8; color: #0A6EBD; padding: 8px; border: 1px solid #ddd; text-align: left; text-transform: uppercase; font-size: 9px; }
        .table td { padding: 8px; border: 1px solid #ddd; vertical-align: top; }
        .status { font-weight: bold; font-size: 9px; text-transform: uppercase; text-align: center; }
        .status-ativo { color: #0F8B5A; }
        .status-inativo { color: #C42B2B; }
        .footer { position: fixed; bottom: -30px; width: 100%; font-size: 9px; color: #999; border-top: 1px solid #ddd; padding-top: 5px; }
        .footer .page-number:after { content: counter(page) " de " counter(pages); }

        /* Papel Timbrado (Fundo) */
        .letterhead {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -2000; /* Fica atrás de todo o conteúdo e da marca d'água */
            opacity: 0.3;   /* Ajuste a intensidade do fundo aqui */
        }
        .letterhead img { width: 100%; height: 100%; }
    </style>
</head>
<body>
    <?php if (!empty($bg_image)): ?><div class="letterhead"><img src="<?php echo $bg_image; ?>"></div><?php endif; ?>

    <div class="header">
        <?php if (!empty($empresa['logo_path'])): ?>
            <?php 
                $logoPath = ROOT_PATH . '/public/uploads/logos/' . $empresa['logo_path']; 
                if (file_exists($logoPath)): 
                    $type = pathinfo($logoPath, PATHINFO_EXTENSION);
                    $imgData = file_get_contents($logoPath);
                    $base64Logo = 'data:image/' . $type . ';base64,' . base64_encode($imgData);
                ?>
                <img src="<?php echo $base64Logo; ?>" style="height: 40px; margin-bottom: 10px;">
            <?php endif; ?>
        <?php endif; ?>

        <h1>Relatório de Fornecedores</h1>
        <div class="company-info">
            <strong><?php echo htmlspecialchars($empresa['razao_social'] ?? 'SysEnviCorp'); ?></strong><br>
            CNPJ: <?php echo htmlspecialchars($empresa['cnpj'] ?? ''); ?> | Gerado em: <?php echo $dataGeracao; ?>
        </div>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th style="width: 25%;">Razão Social / Nome Fantasia</th>
                <th style="width: 15%;">CNPJ/CPF</th>
                <th style="width: 15%;">Categoria</th>
                <th style="width: 15%;">Localização</th>
                <th style="width: 20%;">Contato</th>
                <th style="width: 10%; text-align:center">Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($fornecedores)): foreach ($fornecedores as $f): ?>
            <tr>
                <td>
                    <strong><?php echo htmlspecialchars($f['nome']); ?></strong><br>
                    <small style="color:#666"><?php echo htmlspecialchars($f['nome_fantasia'] ?? ''); ?></small>
                </td>
                <td style="font-family:monospace">
                    <?php 
                        $doc = preg_replace('/\D/', '', $f['cnpj_cpf'] ?? $f['cnpj'] ?? '');
                        if (strlen($doc) === 11) {
                            echo vsprintf('%s%s%s.%s%s%s.%s%s%s-%s%s', str_split($doc));
                        } elseif (strlen($doc) === 14) {
                            echo vsprintf('%s%s.%s%s%s.%s%s%s/%s%s%s%s-%s%s', str_split($doc));
                        } else {
                            echo htmlspecialchars($f['cnpj_cpf'] ?? $f['cnpj'] ?? '—');
                        }
                    ?>
                </td>
                <td><?php echo htmlspecialchars($f['categoria_fornecimento'] ?? '—'); ?></td>
                <td><?php echo htmlspecialchars(($f['cidade'] ?? '—') . ' / ' . ($f['uf'] ?? '')); ?></td>
                <td>
                    <?php echo htmlspecialchars($f['email'] ?? ''); ?><br>
                    <?php echo htmlspecialchars($f['telefone'] ?? ''); ?>
                </td>
                <td class="status">
                    <span class="<?php echo (strtolower($f['status']) === 'ativo') ? 'status-ativo' : 'status-inativo'; ?>">
                        <?php echo htmlspecialchars($f['status']); ?>
                    </span>
                </td>
            </tr>
            <?php endforeach; else: ?>
            <tr><td colspan="6" style="text-align:center">Nenhum registro encontrado.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
    <div class="footer">
        <table width="100%" style="border: none;">
            <tr>
                <td style="text-align: left; border: none; padding: 0;">SysEnviCorp ERP — Gestão Inteligente</td>
                <td style="text-align: right; border: none; padding: 0;">Página <span class="page-number"></span></td>
            </tr>
        </table>
    </div>
</body>
</html>