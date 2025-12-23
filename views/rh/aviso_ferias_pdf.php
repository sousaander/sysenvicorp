<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Aviso de Férias</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 12px;
            line-height: 1.5;
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
            width: 300px;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>AVISO DE FÉRIAS</h1>

        <p><strong>Empregador:</strong> <?php echo htmlspecialchars($dados['empresa']['razao_social']); ?><br>
            <strong>CNPJ:</strong> <?php echo htmlspecialchars($dados['empresa']['cnpj']); ?>
        </p>

        <p><strong>Empregado(a):</strong> <?php echo htmlspecialchars($dados['funcionario']['nome']); ?><br>
            <strong>Cargo:</strong> <?php echo htmlspecialchars($dados['funcionario']['cargo']); ?><br>
            <strong>Setor:</strong> <?php echo htmlspecialchars($dados['funcionario']['setor']); ?>
        </p>

        <p>Prezado(a) Senhor(a),</p>

        <p>Comunicamos, nos termos do Art. 135 da CLT, que suas férias relativas ao período aquisitivo que se encerrou serão concedidas a partir de <strong><?php echo date('d/m/Y', strtotime($dados['periodo']['data_inicio'])); ?></strong>, com duração de <strong><?php echo $dados['periodo']['dias']; ?></strong> dias, encerrando-se em <strong><?php echo date('d/m/Y', strtotime($dados['periodo']['data_fim'])); ?></strong>.</p>

        <p>Seu retorno às atividades laborais está previsto para o dia <strong><?php echo date('d/m/Y', strtotime($dados['periodo']['data_retorno'])); ?></strong>.</p>

        <p>Cidade, <?php echo date('d \d\e F \d\e Y'); ?>.</p>

        <div style="margin-top: 100px; text-align: center;">
            <div class="signature-line" style="margin: 0 auto;"><?php echo htmlspecialchars($dados['funcionario']['nome']); ?></div>
            <p style="text-align: center;">Ciente em <?php echo date('d/m/Y'); ?></p>
        </div>
    </div>
</body>

</html>