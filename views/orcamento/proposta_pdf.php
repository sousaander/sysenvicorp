<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        @page {
            margin: 120px 50px 80px 50px;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 12px;
            color: #333;
        }

        .header {
            position: fixed;
            top: -100px;
            left: 0;
            right: 0;
            height: 80px;
            border-bottom: 1px solid #007bff;
        }

        .footer {
            position: fixed;
            bottom: -60px;
            left: 0;
            right: 0;
            height: 50px;
            font-size: 10px;
            text-align: center;
            color: #777;
            border-top: 1px solid #ccc;
            padding-top: 10px;
        }

        .footer .page-number:after {
            content: counter(page);
        }

        .logo {
            max-width: 180px;
            max-height: 70px;
        }

        .proposal-title {
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
            text-align: center;
            margin-bottom: 20px;
        }

        .section {
            margin-bottom: 25px;
            /* Garante que seções longas não quebrem no meio de forma estranha */
            page-break-inside: avoid;
        }

        .section-title {
            font-size: 16px;
            font-weight: bold;
            color: #333;
            border-bottom: 2px solid #007bff;
            padding-bottom: 5px;
            margin-bottom: 15px;
        }

        .content {
            line-height: 1.6;
        }

        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .details-table th,
        .details-table td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }

        .details-table th {
            background-color: #f8f9fa;
            font-weight: bold;
            width: 30%;
        }

        .total-row {
            font-weight: bold;
            background-color: #e9ecef;
        }
    </style>
</head>

<body>
    <!-- Cabeçalho Fixo -->
    <div class="header">
        <table width="100%">
            <tr>
                <td width="50%" style="vertical-align: middle;">
                    <?php
                    // Converte o caminho do logo para base64 para embutir no PDF
                    $logoPath = ROOT_PATH . '/public/assets/images/logo.png';
                    if (file_exists($logoPath)) {
                        $logoData = base64_encode(file_get_contents($logoPath));
                        echo '<img src="data:image/png;base64,' . $logoData . '" class="logo">';
                    } else {
                        echo '<h1 style="font-size: 20px; color: #007bff;">Sua Empresa</h1>';
                    }
                    ?>
                </td>
                <td width="50%" style="text-align: right; vertical-align: middle;">
                    <h2 style="margin: 0; font-size: 18px;">Proposta Comercial</h2>
                    <p style="margin: 5px 0 0 0;"><strong>Nº:</strong> <?php echo str_pad($proposta_pdf['id'], 4, '0', STR_PAD_LEFT); ?></p>
                    <p style="margin: 5px 0 0 0;"><strong>Data:</strong> <?php echo date('d/m/Y'); ?></p>
                </td>
            </tr>
        </table>
    </div>

    <!-- Rodapé Fixo -->
    <div class="footer">
        Sua Empresa LTDA - CNPJ: 00.000.000/0001-00<br>
        Rua Exemplo, 123, Bairro, Cidade-UF, CEP 00000-000 | Telefone: (00) 1234-5678 | E-mail: contato@suaempresa.com
    </div>

    <script type="text/php">
        if (isset($pdf)) {
            $text = "Página {PAGE_NUM} de {PAGE_COUNT}";
            $size = 10;
            $font = $fontMetrics->getFont("Helvetica");
            $width = $fontMetrics->get_text_width($text, $font, $size) / 2;
            $x = ($pdf->get_width() - $width) / 2;
            $y = $pdf->get_height() - 35;
            $pdf->page_text($x, $y, $text, $font, $size);
        }
    </script>

    <!-- Conteúdo Principal -->
    <div class="proposal-title"><?php echo htmlspecialchars($proposta_pdf['titulo']); ?></div>

    <div class="section">
        <div class="section-title">Descrição Técnica</div>
        <div class="content"><?php echo nl2br(htmlspecialchars($proposta_pdf['descricao_tecnica'] ?? 'N/A')); ?></div>
    </div>

    <div class="section">
        <div class="section-title">Condições Comerciais</div>
        <div class="content"><?php echo nl2br(htmlspecialchars($proposta_pdf['condicoes'] ?? 'N/A')); ?></div>
    </div>

    <div class="section">
        <div class="section-title">Resumo Financeiro</div>
        <table class="details-table">
            <tr class="total-row">
                <th>Valor Total da Proposta</th>
                <td><?php echo !empty($proposta_pdf['valor_total']) ? 'R$ ' . number_format($proposta_pdf['valor_total'], 2, ',', '.') : 'A ser definido'; ?></td>
            </tr>
        </table>
    </div>

</body>

</html>