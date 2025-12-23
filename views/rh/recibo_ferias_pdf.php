<?php
$funcionario = $calculo['funcionario'];
$periodo = $calculo['periodo'];
$valores = $calculo['valores'];

// Função para escrever um número por extenso
function valorPorExtenso($valor)
{
    // CORREÇÃO: Substituída a função que dependia da extensão 'intl' (NumberFormatter)
    // por uma implementação em PHP puro para evitar o erro "Class not found".
    $singular = ["centavo", "real", "mil", "milhão", "bilhão", "trilhão", "quatrilhão"];
    $plural = ["centavos", "reais", "mil", "milhões", "bilhões", "trilhões", "quatrilhões"];
    $c = ["", "cem", "duzentos", "trezentos", "quatrocentos", "quinhentos", "seiscentos", "setecentos", "oitocentos", "novecentos"];
    $d = ["", "dez", "vinte", "trinta", "quarenta", "cinquenta", "sessenta", "setenta", "oitenta", "noventa"];
    $d10 = ["dez", "onze", "doze", "treze", "quatorze", "quinze", "dezesseis", "dezessete", "dezoito", "dezenove"];
    $u = ["", "um", "dois", "três", "quatro", "cinco", "seis", "sete", "oito", "nove"];

    $z = 0;
    $valor = number_format($valor, 2, ".", ".");
    $inteiro = explode(".", $valor);
    $rt = "";

    for ($i = 0; $i < count($inteiro); $i++) {
        for ($ii = strlen($inteiro[$i]); $ii < 3; $ii++) {
            $inteiro[$i] = "0" . $inteiro[$i];
        }
    }

    $fim = count($inteiro) - ($inteiro[count($inteiro) - 1] > 0 ? 1 : 2);
    for ($i = 0; $i < count($inteiro); $i++) {
        $valor = $inteiro[$i];
        $rc = (($valor > 100) && ($valor < 200)) ? "cento" : $c[$valor[0]];
        $rd = ($valor[1] < 2) ? "" : $d[$valor[1]];
        $ru = ($valor > 0) ? (($valor[1] == 1) ? $d10[$valor[2]] : $u[$valor[2]]) : "";

        $r = $rc . (($rc && ($rd || $ru)) ? " e " : "") . $rd . (($rd && $ru) ? " e " : "") . $ru;
        $t = count($inteiro) - 1 - $i;
        $r .= ($r) ? " " . ($valor > 1 ? $plural[$t] : $singular[$t]) : "";
        if ($valor == "000") $z++;
        elseif ($z > 0) $z--;
        if (($t == 1) && ($z > 0) && ($inteiro[0] > 0)) $r .= (($z > 1) ? " de " : "") . $plural[$t];
        if ($r) $rt = $rt . ((($i > 0) && ($i <= $fim) && ($inteiro[0] > 0) && ($z < 1)) ? (($i < $fim) ? ", " : " e ") : " ") . $r;
    }

    $rt = trim($rt);
    if (!$rt) return "zero";

    // Adiciona 'reais' e 'centavos'
    $partes = explode(' e ', $rt);
    if (count($partes) > 1 && in_array(end($partes), $plural)) {
        $ultimo = array_pop($partes);
        $rt = implode(' e ', $partes) . ' ' . $ultimo;
    }

    return ucfirst($rt);
}
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
        <h1>RECIBO DE PAGAMENTO DE FÉRIAS</h1>

        <p>Eu, <strong><?php echo htmlspecialchars($funcionario['nome']); ?></strong>, funcionário(a) da empresa <strong>EnviCorp Soluções Ambientais LTDA</strong>, declaro ter recebido a importância líquida de <strong>R$ <?php echo number_format($valores['valor_liquido'], 2, ',', '.'); ?> (<?php echo valorPorExtenso($valores['valor_liquido']); ?>)</strong>, referente ao pagamento das minhas férias.</p>

        <p>O período de gozo das férias será de <strong><?php echo date('d/m/Y', strtotime($periodo['data_inicio'])); ?></strong> a <strong><?php echo date('d/m/Y', strtotime($periodo['data_fim'])); ?></strong>, totalizando <?php echo htmlspecialchars($periodo['dias']); ?> dias.</p>

        <p>Por ser a expressão da verdade, firmo o presente recibo para que produza seus devidos e legais efeitos.</p>

        <p style="text-align: right; margin-top: 40px;">Cidade, <?php echo date('d \d\e F \d\e Y'); ?>.</p>

        <div style="margin-top: 100px; text-align: center;">
            <div class="signature-line" style="margin: 0 auto;"><?php echo htmlspecialchars($funcionario['nome']); ?></div>
            <p style="text-align: center;">Assinatura do(a) Funcionário(a)</p>
        </div>
    </div>
</body>

</html>