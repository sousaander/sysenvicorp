<?php
// Extract data for easier access
$funcionario = $calculo['funcionario'];
$full_funcionario_data = $calculo['full_funcionario_data'];
$empresa = $empresa; // Passed from controller

// Helper function to format date in Portuguese
function formatarDataPorExtenso(\DateTime $data): string
{
    $meses = [
        1 => 'janeiro',
        2 => 'fevereiro',
        3 => 'março',
        4 => 'abril',
        5 => 'maio',
        6 => 'junho',
        7 => 'julho',
        8 => 'agosto',
        9 => 'setembro',
        10 => 'outubro',
        11 => 'novembro',
        12 => 'dezembro'
    ];
    $dia = $data->format('d');
    $mes = $meses[(int)$data->format('m')];
    $ano = $data->format('Y');
    return "$dia de $mes de $ano";
}

// Logic to determine the content based on the reason and notice type
$motivo = $funcionario['motivo'];
$aviso_tipo = $funcionario['aviso_previo'] ?? 'nao_se_aplica';
$dias_aviso = $calculo['referencias']['dias_aviso_previo'] ?? 30;

$titulo = "AVISO PRÉVIO";
$remetente = "";
$destinatario = "";
$corpo_texto = "";

$data_aviso = !empty($funcionario['data_aviso_previo']) ? new DateTime($funcionario['data_aviso_previo']) : new DateTime();
$cidade = $empresa['cidade'] ?? 'Nossa Cidade';

if ($motivo === 'demissao_sem_justa_causa') {
    $titulo = "AVISO PRÉVIO DO EMPREGADOR";
    $remetente = htmlspecialchars($empresa['razao_social'] ?? 'EnviCorp Soluções Ambientais LTDA');
    $destinatario = htmlspecialchars($funcionario['nome']);

    if ($aviso_tipo === 'indenizado') {
        $corpo_texto = "Prezado(a) Senhor(a) " . htmlspecialchars($funcionario['nome']) . ",<br><br>" .
            "Comunicamos, para os devidos fins, que seus serviços não serão mais necessários a esta empresa, sendo a partir desta data o seu AVISO PRÉVIO INDENIZADO, correspondente a {$dias_aviso} dias, nos termos do Art. 487, § 1º da CLT.<br><br>" .
            "Solicitamos a devolução de sua Carteira de Trabalho e Previdência Social (CTPS) para as devidas anotações.";
    } else { // trabalhado
        $data_inicio_aviso = clone $data_aviso;
        $data_fim_aviso = (clone $data_aviso)->modify("+" . ($dias_aviso - 1) . " days");
        $corpo_texto = "Prezado(a) Senhor(a) " . htmlspecialchars($funcionario['nome']) . ",<br><br>" .
            "Comunicamos, nos termos do Art. 487, inciso II da CLT, que seu contrato de trabalho será rescindido. Vossa Senhoria deverá cumprir o aviso prévio trabalhado no período de " . $data_inicio_aviso->format('d/m/Y') . " a " . $data_fim_aviso->format('d/m/Y') . ".<br><br>" .
            "Durante o período do aviso prévio, V.Sa. poderá optar pela redução de 2 (duas) horas diárias em sua jornada de trabalho ou por faltar 7 (sete) dias corridos, conforme o parágrafo único do Art. 488 da CLT.";
    }
} elseif ($motivo === 'pedido_demissao') {
    $titulo = "AVISO PRÉVIO DO EMPREGADO";
    $remetente = htmlspecialchars($funcionario['nome']);
    $destinatario = htmlspecialchars($empresa['razao_social'] ?? 'EnviCorp Soluções Ambientais LTDA');

    if ($aviso_tipo === 'nao_cumprido_empregado') {
        $corpo_texto = "À " . htmlspecialchars($empresa['razao_social'] ?? 'EnviCorp Soluções Ambientais LTDA') . ",<br><br>" .
            "Por motivos particulares, venho por meio desta comunicar meu pedido de demissão do cargo que ocupo nesta empresa, com dispensa do cumprimento do aviso prévio. Estou ciente de que a falta do aviso prévio me acarretará o desconto do valor correspondente em minha rescisão, conforme o Art. 487, § 2º da CLT.<br><br>" .
            "Solicito a anotação em minha Carteira de Trabalho e Previdência Social (CTPS).";
    } else { // trabalhado
        $data_inicio_aviso = clone $data_aviso;
        $data_fim_aviso = (clone $data_aviso)->modify("+29 days"); // Pedido de demissão, aviso é de 30 dias
        $corpo_texto = "À " . htmlspecialchars($empresa['razao_social'] ?? 'EnviCorp Soluções Ambientais LTDA') . ",<br><br>" .
            "Por motivos particulares, venho por meio desta comunicar meu pedido de demissão do cargo que ocupo nesta empresa. Cumprirei o aviso prévio legal, trabalhando no período de " . $data_inicio_aviso->format('d/m/Y') . " a " . $data_fim_aviso->format('d/m/Y') . ".<br><br>" .
            "Solicito a anotação em minha Carteira de Trabalho e Previdência Social (CTPS).";
    }
} else {
    // Caso para outros motivos onde o aviso prévio não se aplica (justa causa, término de contrato)
    $titulo = "COMUNICADO DE RESCISÃO";
    $remetente = htmlspecialchars($empresa['razao_social'] ?? 'EnviCorp Soluções Ambientais LTDA');
    $destinatario = htmlspecialchars($funcionario['nome']);
    $motivo_formatado = ucwords(str_replace('_', ' ', $motivo));
    $corpo_texto = "Prezado(a) Senhor(a) " . htmlspecialchars($funcionario['nome']) . ",<br><br>" .
        "Comunicamos que, a partir desta data, seu contrato de trabalho está sendo rescindido pelo motivo de: <strong>" . htmlspecialchars($motivo_formatado) . "</strong>.<br><br>" .
        "Solicitamos que compareça ao departamento de Recursos Humanos para os procedimentos de desligamento.";
}

// Prepara o logo em Base64
$logoPath = ROOT_PATH . '/public/assets/images/logo.png';
$logoSrc = '';
if (file_exists($logoPath) && extension_loaded('gd')) {
    $logoSrc = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($titulo); ?></title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 12px;
            line-height: 1.6;
            color: #333;
            margin: 2cm;
        }

        .container {
            width: 100%;
            margin: 0 auto;
        }

        h1 {
            text-align: center;
            font-size: 16px;
            text-transform: uppercase;
            border-bottom: 1px solid #ccc;
            padding-bottom: 10px;
            margin-bottom: 40px;
        }

        p {
            margin-bottom: 20px;
            text-align: justify;
        }

        .destinatario {
            margin-bottom: 30px;
        }

        .assinatura {
            margin-top: 80px;
            text-align: center;
        }

        .linha-assinatura {
            border-top: 1px solid #000;
            width: 350px;
            margin: 0 auto;
            padding-top: 5px;
            font-size: 11px;
        }
    </style>
</head>

<body>
    <div class="container">
        <?php if (!empty($logoSrc)): ?>
            <div style="text-align: center; margin-bottom: 20px;"><img src="<?php echo $logoSrc; ?>" alt="Logo" style="max-height: 60px;"></div>
        <?php endif; ?>
        <h1><?php echo $titulo; ?></h1>

        <div class="destinatario">
            <strong>À</strong><br>
            <strong><?php echo $destinatario; ?></strong><br>
            <?php if ($motivo === 'pedido_demissao'): ?>
                CNPJ: <?php echo htmlspecialchars($empresa['cnpj'] ?? 'N/A'); ?>
            <?php else: ?>
                CTPS: <?php echo htmlspecialchars($full_funcionario_data['ctps'] ?? 'N/A'); ?>
            <?php endif; ?>
        </div>

        <p><?php echo $corpo_texto; ?></p>

        <p style="text-align: right; margin-top: 50px;"><?php echo htmlspecialchars($cidade); ?>, <?php echo formatarDataPorExtenso($data_aviso); ?>.</p>

        <div class="assinatura">
            <div class="linha-assinatura">
                <?php echo $remetente; ?><br>
                <?php if ($motivo === 'pedido_demissao'): ?>
                    (Assinatura do Empregado)
                <?php else: ?>
                    (Assinatura do Empregador/Preposto)
                <?php endif; ?>
            </div>
        </div>

        <div class="assinatura">
            <p>Ciente em: ______ / ______ / ________</p>
            <div class="linha-assinatura">
                <?php echo $destinatario; ?><br>
                <?php if ($motivo === 'pedido_demissao'): ?>
                    (Assinatura do Empregador/Preposto)
                <?php else: ?>
                    (Assinatura do Empregado)
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>

</html>