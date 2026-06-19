<?php
// Prepara o logo em Base64
$logoPath = ROOT_PATH . '/public/assets/images/logo.png';
$logoSrc = '';
if (file_exists($logoPath) && extension_loaded('gd')) {
    $logoSrc = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
}

// Prepara a foto do funcionário em Base64 para garantir carregamento no PDF
$fotoSrc = '';
if (extension_loaded('gd')) {
    $fotoSrc = 'https://placehold.co/128x128/E2E8F0/4A5568?text=Foto'; // Placeholder padrão
    if (!empty($funcionario['foto_path'])) {
        $fotoPathLocal = ROOT_PATH . '/storage/fotos_funcionarios/' . $funcionario['foto_path'];
        if (file_exists($fotoPathLocal)) {
            $ext = pathinfo($fotoPathLocal, PATHINFO_EXTENSION);
            $fotoSrc = 'data:image/' . $ext . ';base64,' . base64_encode(file_get_contents($fotoPathLocal));
        }
    }
}
require_once ROOT_PATH . '/app/helpers/ReportHelper.php';

use App\Helpers\ReportHelper;
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Using @page for headers/footers is tricky with Dompdf's default CSS parser.
           A fixed position element is a more reliable approach. */
        @page {
            margin: 1.5cm;
            /* Define margins for the page */
        }

        body {
            font-family: 'Times New Roman', serif;
            font-size: 10px;
            /* Smaller base font size for PDF */
            color: #374151;
            /* gray-700 */
        }

        .header,
        .footer {
            position: fixed;
            left: 0;
            right: 0;
            color: #6B7280;
            /* gray-500 */
            text-align: center;
        }

        .header {
            top: -1.2cm;
            height: 1cm;
        }

        .footer {
            bottom: -1.2cm;
            height: 1cm;
        }

        .footer .page-number:before {
            content: "Página " counter(page);
        }

        .section-title {
            font-size: 1rem;
            /* 16px */
            font-weight: 600;
            color: #111827;
            /* gray-900 */
            border-bottom: 1px solid #D1D5DB;
            /* border-gray-300 */
            padding-bottom: 0.5rem;
            margin-top: 1.5rem;
            margin-bottom: 1rem;
        }

        .field-group {
            margin-bottom: 0.75rem;
            /* mb-3 */
        }

        .field-label {
            font-size: 0.75rem;
            /* 12px */
            color: #6B7280;
            text-transform: uppercase;
            font-weight: 500;
        }

        .field-value {
            font-size: 0.875rem;
            /* 14px */
            font-weight: 500;
            color: #1F2937;
            /* gray-800 */
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td {
            vertical-align: top;
            padding-bottom: 0.75rem;
            /* 12px, like mb-3 */
        }

        .text-center {
            text-align: center;
        }

        .page-break {
            page-break-before: always;
        }

        /* Estilos específicos para o cabeçalho para garantir formatação no PDF */
        .header-table {
            width: 100%;
            border-bottom: 2px solid #1F2937;
            /* gray-800 */
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .header-title {
            font-size: 14px;
            font-weight: bold;
            color: #1F2937;
            margin: 0 0 4px 0;
        }

        .header-text {
            font-size: 10px;
            color: #374151;
            /* gray-700 */
            margin: 0;
        }
    </style>
</head>

<body>
    <!-- Footer -->
    <div class="footer">
        <span class="page-number"></span> | Gerado em: <?php echo ReportHelper::formatDateTime(date('Y-m-d H:i:s')); ?>
    </div>

    <!-- Main Content -->
    <main>
        <!-- Cabeçalho do Documento -->
        <table class="header-table">
            <tr>
                <td style="width: 25%; vertical-align: top;">
                    <!-- O logo da empresa viria aqui. -->
                    <?php if (!empty($logoSrc)): ?>
                        <img src="<?php echo $logoSrc; ?>" alt="Logo da Empresa" style="max-height: 60px;">
                    <?php endif; ?>
                </td>
                <td style="width: 75%; text-align: right; vertical-align: top;">
                    <h1 class="header-title"><?php echo htmlspecialchars($empresa['razao_social'] ?? 'EnviCorp Soluções Ambientais'); ?></h1>
                    <p class="header-text">CNPJ: <?php echo ReportHelper::formatCpfCnpj($empresa['cnpj'] ?? 'N/A'); ?></p>
                    <p class="header-text"><?php echo htmlspecialchars($empresa['endereco'] ?? 'Endereço não informado'); ?></p>
                </td>
            </tr>
        </table>

        <h1 class="text-2xl font-bold text-center text-gray-800 my-8">FICHA CADASTRAL DE FUNCIONÁRIO</h1>

        <!-- Dados Pessoais -->
        <div class="section-title">Dados Pessoais</div>
        <table style="width: 100%; margin-bottom: 10px;">
            <tr>
                <td style="width: 25%; text-align: center; vertical-align: top; padding-right: 20px;">
                    <?php if (!empty($fotoSrc)): ?>
                        <img src="<?php echo $fotoSrc; ?>" alt="Foto do Funcionário" style="width: 120px; height: 120px; object-fit: cover; border-radius: 8px; border: 2px solid #E5E7EB;">
                    <?php else: ?>
                        <div style="width: 120px; height: 120px; border: 2px solid #E5E7EB; text-align: center; line-height: 120px; color: #ccc; font-size: 10px;">Sem Foto</div>
                    <?php endif; ?>
                </td>
                <td style="width: 75%; vertical-align: top;">
                    <table>
                        <tbody>
                            <tr>
                                <td colspan="3">
                                    <p class="field-label">Nome Completo</p>
                                    <p class="field-value"><?php echo htmlspecialchars($funcionario['nome'] ?? 'N/A'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <td class="w-1/3 pr-6">
                                    <p class="field-label">Data de Nascimento</p>
                                    <p class="field-value"><?php echo $funcionario['data_nascimento'] ? ReportHelper::formatDate($funcionario['data_nascimento']) : 'N/A'; ?></p>
                                </td>
                                <td class="w-1/3 pr-6">
                                    <p class="field-label">Estado Civil</p>
                                    <p class="field-value"><?php echo htmlspecialchars($funcionario['estado_civil'] ?? 'N/A'); ?></p>
                                </td>
                                <td class="w-1/3">
                                    <p class="field-label">CPF</p>
                                    <p class="field-value"><?php echo ReportHelper::formatCpfCnpj($funcionario['cpf'] ?? 'N/A'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <td class="pr-6">
                                    <p class="field-label">RG</p>
                                    <p class="field-value"><?php echo htmlspecialchars($funcionario['rg'] ?? 'N/A'); ?></p>
                                </td>
                                <td class="pr-6">
                                    <p class="field-label">Celular</p>
                                    <p class="field-value"><?php echo htmlspecialchars($funcionario['celular'] ?? 'N/A'); ?></p>
                                </td>
                                <td>
                                    <p class="field-label">Tipo Sanguíneo</p>
                                    <p class="field-value"><?php echo htmlspecialchars($funcionario['tipo_sanguineo'] ?? 'N/A'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="3">
                                    <p class="field-label">E-mail Pessoal</p>
                                    <p class="field-value"><?php echo htmlspecialchars($funcionario['email'] ?? 'N/A'); ?></p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        </table>

        <!-- Documentação -->
        <div class="section-title">Documentação</div>
        <table>
            <tbody>
                <tr>
                    <td class="w-1/3 pr-6">
                        <p class="field-label">CTPS</p>
                        <p class="field-value"><?php echo htmlspecialchars($funcionario['ctps'] ?? 'N/A'); ?></p>
                    </td>
                    <td class="w-1/3 pr-6">
                        <p class="field-label">PIS/PASEP</p>
                        <p class="field-value"><?php echo htmlspecialchars($funcionario['pis'] ?? 'N/A'); ?></p>
                    </td>
                    <td class="w-1/3">
                        <p class="field-label">Título de Eleitor</p>
                        <p class="field-value"><?php echo htmlspecialchars($funcionario['titulo_eleitor'] ?? 'N/A'); ?></p>
                    </td>
                </tr>
                <tr>
                    <td class="pr-6">
                        <p class="field-label">Reservista</p>
                        <p class="field-value"><?php echo htmlspecialchars($funcionario['reservista'] ?? 'N/A'); ?></p>
                    </td>
                    <td class="pr-6">
                        <p class="field-label">CNH</p>
                        <p class="field-value"><?php echo htmlspecialchars($funcionario['cnh'] ?? 'N/A'); ?></p>
                    </td>
                    <td>
                        <p class="field-label">Categoria CNH</p>
                        <p class="field-value"><?php echo htmlspecialchars($funcionario['cnh_categoria'] ?? 'N/A'); ?></p>
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- Endereço -->
        <div class="section-title page-break">Endereço Residencial</div>
        <table>
            <tbody>
                <tr>
                    <td colspan="2" class="w-4/6 pr-6">
                        <p class="field-label">Logradouro, Nº e Complemento</p>
                        <p class="field-value"><?php echo htmlspecialchars($funcionario['endereco'] ?? 'N/A'); ?></p>
                    </td>
                    <td class="w-2/6">
                        <p class="field-label">Bairro</p>
                        <p class="field-value"><?php echo htmlspecialchars($funcionario['bairro'] ?? 'N/A'); ?></p>
                    </td>
                </tr>
                <tr>
                    <td class="w-3/6 pr-6">
                        <p class="field-label">Cidade</p>
                        <p class="field-value"><?php echo htmlspecialchars($funcionario['cidade'] ?? 'N/A'); ?></p>
                    </td>
                    <td class="w-1/6 pr-6">
                        <p class="field-label">UF</p>
                        <p class="field-value"><?php echo htmlspecialchars($funcionario['uf'] ?? 'N/A'); ?></p>
                    </td>
                    <td class="w-2/6">
                        <p class="field-label">CEP</p>
                        <p class="field-value"><?php echo htmlspecialchars($funcionario['cep'] ?? 'N/A'); ?></p>
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- Dados Contratuais e Bancários -->
        <div class="section-title">Dados Contratuais e Bancários</div>
        <table>
            <tbody>
                <tr>
                    <td class="w-1/4 pr-6">
                        <p class="field-label">Data de Admissão</p>
                        <p class="field-value"><?php echo $funcionario['data_admissao'] ? ReportHelper::formatDate($funcionario['data_admissao']) : 'N/A'; ?></p>
                    </td>
                    <td class="w-1/4 pr-6">
                        <p class="field-label">Cargo/Função</p>
                        <p class="field-value"><?php echo htmlspecialchars($funcionario['cargo'] ?? 'N/A'); ?></p>
                    </td>
                    <td class="w-1/4 pr-6">
                        <p class="field-label">Setor</p>
                        <p class="field-value"><?php echo htmlspecialchars($funcionario['setor'] ?? 'N/A'); ?></p>
                    </td>
                    <td class="w-1/4">
                        <p class="field-label">Salário</p>
                        <p class="field-value"><?php echo ReportHelper::formatCurrency($funcionario['salario'] ?? 0); ?></p>
                    </td>
                </tr>
                <tr>
                    <td class="pr-6">
                        <p class="field-label">Banco</p>
                        <p class="field-value"><?php echo htmlspecialchars($funcionario['banco'] ?? 'N/A'); ?></p>
                    </td>
                    <td class="pr-6">
                        <p class="field-label">Agência</p>
                        <p class="field-value"><?php echo htmlspecialchars($funcionario['agencia'] ?? 'N/A'); ?></p>
                    </td>
                    <td class="pr-6">
                        <p class="field-label">Conta</p>
                        <p class="field-value"><?php echo htmlspecialchars($funcionario['conta'] ?? 'N/A'); ?> (<?php echo htmlspecialchars(ucfirst($funcionario['tipo_conta'] ?? '')); ?>)</p>
                    </td>
                    <td>
                        <p class="field-label">Chave PIX</p>
                        <p class="field-value"><?php echo htmlspecialchars($funcionario['chave_pix'] ?? 'N/A'); ?></p>
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- Assinatura -->
        <div style="margin-top: 100px;">
            <p class="text-center">Declaro que as informações acima são verdadeiras e autorizo a empresa a utilizá-las para os fins de registro e demais obrigações legais.</p>
            <div class="text-center" style="margin-top: 80px;">
                <div style="border-top: 1px solid #000; width: 300px; margin: 0 auto 5px auto;"></div>
                <p class="font-medium mt-1"><?php echo htmlspecialchars($funcionario['nome'] ?? 'N/A'); ?></p>
                <p class="text-xs text-gray-600">Assinatura do Funcionário</p>
            </div>
        </div>
    </main>
</body>

</html>