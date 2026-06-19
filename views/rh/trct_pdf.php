<?php
// Extract data for easier access
$funcionario = $calculo['funcionario'];
$full_funcionario_data = $calculo['full_funcionario_data'];
$verbas = $calculo['verbas'];
$totais = $calculo['totais'];
$referencias = $calculo['referencias'] ?? [];
// A variável 'empresa' é passada do controlador

// Função auxiliar para encontrar um valor de verba por uma parte única de sua descrição
function findVerbaValor(array $verbas, string $search_string, bool $exact_start = false): float
{
    foreach ($verbas as $verba) {
        if ($exact_start) {
            // Verifique se a descrição começa com a string de busca
            if (strpos($verba['descricao'], $search_string) === 0) {
                return (float) $verba['valor'];
            }
        } else {
            // Comportamento original: verificar se a string de busca está em qualquer lugar da descrição
            if (strpos($verba['descricao'], $search_string) !== false) {
                return (float) $verba['valor'];
            }
        }
    }
    return 0.0;
}

// Função auxiliar para formatar o valor ou retornar um espaço não separável se for zero
function formatarValor(float $valor): string
{
    if ($valor > 0.001 || $valor < -0.001) { // Use um epsilon pequeno para lidar com imprecisões de ponto flutuante
        return number_format($valor, 2, ',', '.');
    }
    return '&nbsp;';
}

// Extraia valores para o PDF usando a função auxiliar
$proventos = $verbas['proventos'] ?? [];
$descontos = $verbas['descontos'] ?? [];
$valorSaldoSalario = findVerbaValor($proventos, 'Saldo de Salário', true);
$valor13Proporcional = findVerbaValor($proventos, '13º Salário Proporcional', true);
$valorFeriasVencidas = findVerbaValor($proventos, 'Férias Vencidas', true);
$valor13Aviso = findVerbaValor($proventos, '13º Salário s/ Projeção Aviso', true);
// Soma os diferentes tipos de horas extras para o campo único do TRCT
$valorHorasExtras50 = findVerbaValor($proventos, 'Horas Extras 50%', true);
$valorHorasExtras100 = findVerbaValor($proventos, 'Horas Extras 100%', true);
$valorHorasExtras = $valorHorasExtras50 + $valorHorasExtras100;

// Valores para a segunda coluna de proventos (buscando dos dados calculados)
$valorComissao = findVerbaValor($proventos, 'Comissão', true);
$valorPericulosidade = findVerbaValor($proventos, 'Adicional de Periculosidade', true);
$valorGorjetas = findVerbaValor($proventos, 'Gorjetas', true);
$valorMulta477 = findVerbaValor($proventos, 'Multa Art. 477', true); // Supondo que será calculado assim
$valor13ExerciciosAnteriores = findVerbaValor($proventos, '13º Salário Exercícios Anteriores', true);
$valorTercoFerias = findVerbaValor($proventos, '1/3 sobre Férias Vencidas', true) + findVerbaValor($proventos, '1/3 sobre Férias Proporcionais', true);
$valorFeriasAviso = findVerbaValor($proventos, 'Férias s/ Aviso Prévio', true) + findVerbaValor($proventos, '1/3 sobre Férias s/ Aviso', true);

$valorIndenizacao479 = findVerbaValor($proventos, 'Indenização Art. 479 CLT', true);

// Valores para a terceira coluna de proventos (buscando dos dados calculados)
$valorGratificacao = findVerbaValor($proventos, 'Gratificação', true);
$valorAdicionalNoturno = findVerbaValor($proventos, 'Adicional Noturno', true);
$valorDSR = findVerbaValor($proventos, 'Descanso Semanal Remunerado (DSR)', true);
$valorSalarioFamilia = findVerbaValor($proventos, 'Salário Família', true);
$valorFeriasProporcionais = findVerbaValor($proventos, 'Férias Proporcionais', true);
$valorAvisoPrevioIndenizado = findVerbaValor($proventos, 'Aviso Prévio Indenizado', true);

// Valores para a seção de deduções
$valorPensao = findVerbaValor($descontos, 'Pensão Alimentícia', true);
$valorAvisoPrevioDesconto = findVerbaValor($descontos, 'Desconto Aviso Prévio não cumprido', true);
$valorIRRF = findVerbaValor($descontos, 'IRRF', true); // O cálculo precisa gerar uma verba com este nome

// Valores para a segunda coluna de deduções
$valorAdiantamentoSalarial = findVerbaValor($descontos, 'Adiantamento Salarial', true);
$valorPrevidenciaSocial = findVerbaValor($descontos, 'INSS sobre Saldo de Salário', true);
$valorIRRF13 = findVerbaValor($descontos, 'IRRF sobre 13º Salário', true);

// Valores para a terceira coluna de deduções
$valorAdiantamento13 = findVerbaValor($descontos, 'Adiantamento de 13º Salário', true);
$valorPrevidencia13 = findVerbaValor($descontos, 'INSS sobre 13º Salário', true);
$valorOutrosDescontos = findVerbaValor($descontos, 'Outros Descontos', true);
$valorAjusteSaldoDevedor = findVerbaValor($proventos, 'Ajuste do Saldo Devedor', true);

// Map for Categoria do Trabalhador
$categorias_trabalhador_map = [
    '101' => '101 - Empregado - Geral',
    '102' => '102 - Empregado - Trabalhador Rural por Pequeno Prazo',
    '103' => '103 - Empregado - Aprendiz',
    '104' => '104 - Empregado - Doméstico',
    '111' => '111 - Empregado - Contrato Verde e Amarelo',
    '721' => '721 - Contribuinte individual - Diretor não empregado, com FGTS',
    '901' => '901 - Estagiário',
];
$categoria_trabalhador_codigo = $funcionario['categoria_trabalhador'] ?? '';
$categoria_trabalhador_descricao = $categorias_trabalhador_map[$categoria_trabalhador_codigo] ?? $categoria_trabalhador_codigo;

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
    <title>TRCT - <?php echo htmlspecialchars($funcionario['nome']); ?></title>
    <style>
        /* Estilização básica para o PDF */
        body {
            font-family: 'Times New Roman', serif;
            font-size: 10px;
            color: #333;
            margin: 0.5cm 1.2cm;
            /* Margem superior reduzida */
        }

        .container {
            width: 100%;
            margin: 0 auto;
        }

        h1 {
            text-align: center;
            font-size: 14px;
            text-transform: uppercase;
            margin-bottom: 10px;
            /* Margem inferior do título reduzida */
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 0;
        }

        th,
        td {
            border: 1px solid #999;
            padding: 3px;
            /* Reduz o padding das células */
            text-align: left;
            vertical-align: top;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        .section-title {
            font-weight: bold;
            background-color: #f2f2f2;
            text-align: left;
            padding: 5px;
            margin-top: 5px;
            /* Margem superior das seções reduzida */
            border: 1px solid #999;
            border-bottom: none;
        }

        .text-right {
            text-align: right;
        }

        .total-row {
            font-weight: bold;
        }

        .total-final-row {
            font-weight: bold;
            background-color: #f2f2f2;
        }

        .signature-area {
            margin-top: 40px;
            page-break-inside: avoid;
        }

        .signature-line {
            border-top: 1px solid #000;
            width: 70%;
            margin: 50px auto 5px auto;
        }

        .signature-text {
            text-align: center;
            font-size: 9px;
        }
    </style>
</head>

<body>
    <div class="container">
        <?php if (!empty($logoSrc)): ?>
            <div style="text-align: center; margin-bottom: 10px;"><img src="<?php echo $logoSrc; ?>" alt="Logo" style="max-height: 50px;"></div>
        <?php endif; ?>
        <p style="text-align: center; font-size: 12px; margin-bottom: 2px; font-weight: bold;">ANEXO I</p>
        <h1 style="margin-top: 0;">Termo de Rescisão do Contrato de Trabalho</h1>

        <!-- IDENTIFICAÇÃO DO EMPREGADOR -->
        <div class="section-title">01 - IDENTIFICAÇÃO DO EMPREGADOR</div>
        <table>
            <tr>
                <td><strong>01 CNPJ/CEI:</strong><br><?php echo htmlspecialchars($empresa['cnpj'] ?? 'N/A'); ?></td>
                <td colspan="3"><strong>02 Razão Social/Nome:</strong><br><?php echo htmlspecialchars($empresa['razao_social'] ?? 'EnviCorp Soluções Ambientais LTDA'); ?></td>
            </tr>
            <tr>
                <td colspan="2"><strong>03 Endereço (Logradouro, Nº, Andar, Apartamento):</strong><br><?php echo htmlspecialchars($empresa['endereco'] ?? 'Endereço não informado'); ?></td>
                <td><strong>04 Bairro/Distrito:</strong><br><?php echo htmlspecialchars($empresa['bairro'] ?? 'N/A'); ?></td>
                <td><strong>05 Município:</strong><br><?php echo htmlspecialchars($empresa['cidade'] ?? 'N/A'); ?></td>
            </tr>
            <tr>
                <td><strong>06 UF:</strong><br><?php echo htmlspecialchars($empresa['uf'] ?? 'N/A'); ?></td>
                <td><strong>07 CEP:</strong><br><?php echo htmlspecialchars($empresa['cep'] ?? 'N/A'); ?></td>
                <td><strong>08 CNAE:</strong><br><?php echo htmlspecialchars($empresa['cnae'] ?? 'N/A'); ?></td>
                <td><strong>09 CNPJ/CEI Tomador/Obra:</strong><br>&nbsp;</td>
            </tr>
        </table>

        <!-- IDENTIFICAÇÃO DO TRABALHADOR -->
        <div class="section-title">02 - IDENTIFICAÇÃO DO TRABALHADOR</div>
        <table>
            <tr>
                <td><strong>10 PIS/PASEP:</strong><br><?php echo htmlspecialchars($full_funcionario_data['pis'] ?? 'N/A'); ?></td>
                <td colspan="3"><strong>11 Nome:</strong><br><?php echo htmlspecialchars($funcionario['nome']); ?></td>
            </tr>
            <tr>
                <td colspan="2"><strong>12 Endereço (Logradouro, Nº, Andar, Apartamento):</strong><br><?php echo htmlspecialchars($full_funcionario_data['endereco'] ?? 'N/A'); ?></td>
                <td><strong>13 Bairro:</strong><br><?php echo htmlspecialchars($full_funcionario_data['bairro'] ?? 'N/A'); ?></td>
                <td><strong>14 Município:</strong><br><?php echo htmlspecialchars($full_funcionario_data['cidade'] ?? 'N/A'); ?></td>
            </tr>
            <tr>
                <td><strong>15 UF:</strong><br><?php echo htmlspecialchars($full_funcionario_data['uf'] ?? 'N/A'); ?></td>
                <td><strong>16 CEP:</strong><br><?php echo htmlspecialchars($full_funcionario_data['cep'] ?? 'N/A'); ?></td>
                <td><strong>17 CTPS (Nº, Série, UF):</strong><br><?php echo htmlspecialchars($full_funcionario_data['ctps'] ?? 'N/A'); ?></td>
                <td><strong>18 CPF:</strong><br><?php echo htmlspecialchars($full_funcionario_data['cpf'] ?? 'N/A'); ?></td>
            </tr>
            <tr>
                <td><strong>19 Data de Nascimento:</strong><br><?php echo !empty($full_funcionario_data['data_nascimento']) ? date('d/m/Y', strtotime($full_funcionario_data['data_nascimento'])) : 'N/A'; ?></td>
                <td colspan="3"><strong>20 Nome da Mãe:</strong><br><?php echo htmlspecialchars($full_funcionario_data['nome_mae'] ?? 'N/A'); ?></td>
            </tr>
        </table>

        <!-- DADOS DO CONTRATO -->
        <div class="section-title">03 - DADOS DO CONTRATO</div>
        <table>
            <tr>
                <td><strong>22 Tipo de Contrato:</strong><br><?php echo htmlspecialchars($funcionario['tipo_contrato'] ?? 'N/A'); ?></td>
                <td colspan="2"><strong>23 Causa do Afastamento:</strong><br><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $funcionario['motivo']))); ?></td>
            </tr>
            <tr>
                <td><strong>24 Remuneração Mês Ant.:</strong><br>R$ <?php echo number_format($funcionario['remuneracao_mes_anterior'] ?? 0, 2, ',', '.'); ?></td>
                <td><strong>25 Data de Admissão:</strong><br><?php echo date('d/m/Y', strtotime($funcionario['data_admissao'])); ?></td>
                <td><strong>26 Data do Aviso Prévio:</strong><br><?php echo !empty($funcionario['data_aviso_previo']) ? date('d/m/Y', strtotime($funcionario['data_aviso_previo'])) : 'N/A'; ?></td>
            </tr>
            <tr>
                <td><strong>27 Data de Afastamento:</strong><br><?php echo date('d/m/Y', strtotime($funcionario['data_desligamento'])); ?></td>
                <td><strong>28 Cód. Afastamento:</strong><br><?php echo htmlspecialchars($funcionario['cod_afastamento'] ?? 'N/A'); ?></td>
                <td><strong>29 Pensão Alim. (%) TRCT:</strong><br><?php echo number_format($funcionario['pensao_trct_percent'] ?? 0, 2, ',', '.'); ?>%</td>
            </tr>
            <tr>
                <td><strong>30 Pensão Alim. (%) FGTS:</strong><br><?php echo number_format($funcionario['pensao_fgts_percent'] ?? 0, 2, ',', '.'); ?>%</td>
                <td><strong>31 Categoria do Trabalhador:</strong><br><?php echo htmlspecialchars($categoria_trabalhador_descricao); ?></td>
                <td><strong>32 Código Sindical:</strong><br><?php echo htmlspecialchars($funcionario['codigo_sindical'] ?? 'N/A'); ?></td>
            </tr>
        </table>

        <!-- ENTIDADE SINDICAL LABORAL -->
        <div class="section-title">ENTIDADE SINDICAL LABORAL</div>
        <table>
            <tr>
                <td><strong>33 CNPJ:</strong><br><?php echo htmlspecialchars($funcionario['cnpj_sindicato'] ?? 'N/A'); ?></td>
                <td><strong>34 Nome:</strong><br><?php echo htmlspecialchars($funcionario['nome_sindicato'] ?? 'N/A'); ?></td>
            </tr>
        </table>

        <!-- DISCRIMINAÇÃO DAS VERBAS RESCISÓRIAS -->
        <div class="section-title">04 - DISCRIMINAÇÃO DAS VERBAS RESCISÓRIAS</div>
        <table>
            <thead>
                <tr>
                    <td colspan="6" style="border-bottom: none; font-weight: bold; padding-bottom: 2px;">VERBAS RESCISÓRIAS</td>
                </tr>
                <tr>
                    <th style="width: 28%;">Rubrica</th>
                    <th class="text-right">Valor</th>
                    <th style="width: 28%;">Rubrica</th>
                    <th class="text-right">Valor</th>
                    <th style="width: 28%;">Rubrica</th>
                    <th class="text-right">Valor</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>50. Saldo de <?php echo ($referencias['dias_saldo_salario'] ?? '0'); ?>/dias Salário</td>
                    <td class="text-right"><?php echo formatarValor($valorSaldoSalario); ?></td>
                    <td>51. Comissão</td>
                    <td class="text-right"><?php echo formatarValor($valorComissao); ?></td>
                    <td>52. Gratificação</td>
                    <td class="text-right"><?php echo formatarValor($valorGratificacao); ?></td>
                </tr>
                <tr>
                    <td>53. Adicional de Insalubridade</td>
                    <td class="text-right"><?php echo formatarValor(0.0); ?></td>
                    <td>54. Adicional de Periculosidade</td>
                    <td class="text-right"><?php echo formatarValor($valorPericulosidade); ?></td>
                    <td>55. Adicional Noturno</td>
                    <td class="text-right"><?php echo formatarValor($valorAdicionalNoturno); ?></td>
                </tr>
                <tr>
                    <td>56. Horas-Extras</td>
                    <td class="text-right"><?php echo formatarValor($valorHorasExtras); ?></td>
                    <td>57. Gorjetas</td>
                    <td class="text-right"><?php echo formatarValor($valorGorjetas); ?></td>
                    <td>58. Descanso Semanal Remunerado (DSR)</td>
                    <td class="text-right"><?php echo formatarValor($valorDSR); ?></td>
                </tr>
                <tr>
                    <td>59. Reflexo do DSR sobre Salário Variável</td>
                    <td class="text-right"><?php echo formatarValor(0.0); ?></td>
                    <td>60. Multa Art. 477, § 8º/CLT</td>
                    <td class="text-right"><?php echo formatarValor($valorMulta477); ?></td>
                    <td>61. Salário Família</td>
                    <td class="text-right"><?php echo formatarValor($valorSalarioFamilia); ?></td>
                </tr>
                <tr>
                    <td>62. 13º Salário Proporcional (<?php echo ($referencias['avos_13_proporcional'] ?? '0'); ?>/12 avos)</td>
                    <td class="text-right"><?php echo formatarValor($valor13Proporcional); ?></td>
                    <td>63. 13º Salário Exercícios Anteriores</td>
                    <td class="text-right"><?php echo formatarValor($valor13ExerciciosAnteriores); ?></td>
                    <td>64. Férias Proporcionais (<?php echo ($referencias['avos_ferias_proporcionais'] ?? '0'); ?>/12 avos)</td>
                    <td class="text-right"><?php echo formatarValor($valorFeriasProporcionais); ?></td>
                </tr>
                <tr>
                    <td>65. Férias Vencidas</td>
                    <td class="text-right"><?php echo formatarValor($valorFeriasVencidas); ?></td>
                    <td>66. Terço Constitucional de Férias</td>
                    <td class="text-right"><?php echo formatarValor($valorTercoFerias); ?></td>
                    <td>68. Aviso Prévio Indenizado</td>
                    <td class="text-right"><?php echo formatarValor($valorAvisoPrevioIndenizado); ?></td>
                </tr>
                <tr>
                    <td>69. 13º Salário (Aviso Prévio Indenizado)</td>
                    <td class="text-right"><?php echo formatarValor($valor13Aviso); ?></td>
                    <td>70. Férias (Aviso Prévio Indenizado)</td>
                    <td class="text-right"><?php echo formatarValor($valorFeriasAviso); ?></td>
                    <td>72. Indenização Art. 479 CLT</td>
                    <td class="text-right"><?php echo formatarValor($valorIndenizacao479); ?></td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                    <td class="text-right">&nbsp;</td>
                    <td>98. Ajuste do Saldo Devedor</td> <!-- Campo 98 -->
                    <td class="text-right"><?php echo formatarValor($valorAjusteSaldoDevedor); ?></td>
                    <td>&nbsp;</td>
                    <td class="text-right">&nbsp;</td>
                </tr>
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="5" class="text-right">150. Total Bruto (A)</td>
                    <td class="text-right"><?php echo number_format($totais['total_proventos'], 2, ',', '.'); ?></td>
                </tr>
            </tfoot>
        </table>

        <!-- DEDUÇÕES -->
        <table style="margin-top: -1px;"> <!-- margin-top para juntar as tabelas -->
            <thead>
                <tr>
                    <td colspan="6" style="border-bottom: none; font-weight: bold; padding-bottom: 2px;">DEDUÇÕES</td>
                </tr>
                <tr>
                    <th style="width: 28%;">Desconto</th>
                    <th class="text-right">Valor</th>
                    <th style="width: 28%;">Desconto</th>
                    <th class="text-right">Valor</th>
                    <th style="width: 28%;">Desconto</th>
                    <th class="text-right">Valor</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>100. Pensão Alimentícia</td>
                    <td class="text-right"><?php echo formatarValor($valorPensao); ?></td>
                    <td>101. Adiantamento Salarial</td>
                    <td class="text-right"><?php echo formatarValor($valorAdiantamentoSalarial); ?></td>
                    <td>102. Adiantamento de 13º Salário</td>
                    <td class="text-right"><?php echo formatarValor($valorAdiantamento13); ?></td>
                </tr>
                <tr>
                    <td>112.1 Aviso Prévio Indenizado</td>
                    <td class="text-right"><?php echo formatarValor($valorAvisoPrevioDesconto); ?></td>
                    <td>114. Previdência Social</td>
                    <td class="text-right"><?php echo formatarValor($valorPrevidenciaSocial); ?></td>
                    <td>114.1 Previdência Social s/ 13º Salário</td>
                    <td class="text-right"><?php echo formatarValor($valorPrevidencia13); ?></td>
                </tr>
                <tr>
                    <td>115. IRRF</td>
                    <td class="text-right"><?php echo formatarValor($valorIRRF); ?></td>
                    <td>115.1 IRRF sobre 13º Salário</td>
                    <td class="text-right"><?php echo formatarValor($valorIRRF13); ?></td>
                    <td>112.2 Outros Descontos</td>
                    <td class="text-right"><?php echo formatarValor($valorOutrosDescontos); ?></td>
                </tr>
            </tbody>
            <tfoot>
                <tr class="total-final-row">
                    <td colspan="5"><strong>116. Total Deduções (B)</strong></td>
                    <td class="text-right"><?php echo number_format($totais['total_descontos'], 2, ',', '.'); ?></td>
                </tr>
                <tr class="total-final-row">
                    <td colspan="5"><strong>117. Valor Líquido (A - B)</strong></td>
                    <td class="text-right"><strong><?php echo number_format($totais['total_liquido'], 2, ',', '.'); ?></strong></td>
                </tr>
            </tfoot>
        </table>

        <!-- TERMO DE QUITAÇÃO -->
        <div style="page-break-before: always;">
            <p style="text-align: center; font-size: 12px; margin-bottom: 2px; font-weight: bold;">ANEXO VI</p>
            <h1 style="margin-top: 0; font-size: 12px;">TERMO DE QUITAÇÃO DE RESCISÃO DO CONTRATO DE TRABALHO</h1>

            <div class="section-title" style="margin-top: 10px;">EMPREGADOR</div>
            <table>
                <tr>
                    <td style="width: 30%;"><strong>01 CNPJ/CEI:</strong><br><?php echo htmlspecialchars($empresa['cnpj'] ?? 'N/A'); ?></td>
                    <td><strong>Razão Social/Nome:</strong><br><?php echo htmlspecialchars($empresa['razao_social'] ?? 'EnviCorp Soluções Ambientais LTDA'); ?></td>
                </tr>
            </table>

            <div class="section-title">TRABALHADOR</div>
            <table>
                <tr>
                    <td><strong>PIS/PASEP:</strong><br><?php echo htmlspecialchars($full_funcionario_data['pis'] ?? 'N/A'); ?></td>
                    <td colspan="3"><strong>Nome:</strong><br><?php echo htmlspecialchars($funcionario['nome']); ?></td>
                </tr>
                <tr>
                    <td><strong>CTPS (nº, série, UF):</strong><br><?php echo htmlspecialchars($full_funcionario_data['ctps'] ?? 'N/A'); ?></td>
                    <td><strong>CPF:</strong><br><?php echo htmlspecialchars($full_funcionario_data['cpf'] ?? 'N/A'); ?></td>
                    <td><strong>Data de Nascimento:</strong><br><?php echo !empty($full_funcionario_data['data_nascimento']) ? date('d/m/Y', strtotime($full_funcionario_data['data_nascimento'])) : 'N/A'; ?></td>
                    <td><strong>Nome da Mãe:</strong><br><?php echo htmlspecialchars($full_funcionario_data['nome_mae'] ?? 'N/A'); ?></td>
                </tr>
            </table>

            <div class="section-title">CONTRATO</div>
            <table>
                <tr>
                    <td colspan="5"><strong>Causa do Afastamento:</strong><br><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $funcionario['motivo']))); ?></td>
                </tr>
                <tr>
                    <td><strong>Data de Admissão:</strong><br><?php echo date('d/m/Y', strtotime($funcionario['data_admissao'])); ?></td>
                    <td><strong>Data do Aviso Prévio:</strong><br><?php echo !empty($funcionario['data_aviso_previo']) ? date('d/m/Y', strtotime($funcionario['data_aviso_previo'])) : 'N/A'; ?></td>
                    <td><strong>Data de Afastamento:</strong><br><?php echo date('d/m/Y', strtotime($funcionario['data_desligamento'])); ?></td>
                    <td><strong>Cód. Afastamento:</strong><br><?php echo htmlspecialchars($funcionario['cod_afastamento'] ?? 'N/A'); ?></td>
                    <td><strong>Pensão Alim. (%) FGTS:</strong><br><?php echo number_format($funcionario['pensao_fgts_percent'] ?? 0, 2, ',', '.'); ?>%</td>
                </tr>
                <tr>
                    <td colspan="5"><strong>Categoria do Trabalhador:</strong><br><?php echo htmlspecialchars($categoria_trabalhador_descricao); ?></td>
                </tr>
            </table>

            <p style="margin-top: 15px; text-align: justify; line-height: 1.4;">
                Foi realizada a rescisão do contrato de trabalho do trabalhador acima qualificado, nos termos do artigo n.º 477 da Consolidação das Leis do Trabalho (CLT).
            </p>
            <p style="margin-top: 8px; text-align: justify; line-height: 1.4;">
                No dia &nbsp;&nbsp;&nbsp;&nbsp;/&nbsp;&nbsp;&nbsp;&nbsp;/&nbsp;&nbsp;&nbsp;&nbsp; foi realizado, nos termos do art. 23 da Instrução Normativa/SRT n.º 15/2010, o efetivo pagamento das verbas
                rescisórias especificadas no corpo do TRCT, no valor líquido de <strong><?php echo number_format($totais['total_liquido'], 2, ',', '.'); ?></strong>, o qual, devidamente rubricado pelas partes, é parte integrante
                do presente Termo de Quitação.
            </p>

            <p style="text-align: right; margin-top: 20px;"><?php echo htmlspecialchars($empresa['cidade'] ?? '___________________'); ?>, _____ de ___________________ de ______.</p>

            <!-- ASSINATURAS -->
            <table style="width: 100%; border: none; border-collapse: collapse; margin-top: 30px;">
                <tbody>
                    <tr style="border: none;">
                        <td style="width: 45%; border: none; padding: 0; vertical-align: bottom;">
                            <div style="border-top: 1px solid #000; margin-top: 20px;"></div>
                            <p style="text-align: center; font-size: 9px; margin-top: 5px;">
                                <?php echo htmlspecialchars($empresa['razao_social'] ?? 'EnviCorp Soluções Ambientais LTDA'); ?><br>
                                (Assinatura do Empregador ou Preposto)
                            </p>
                        </td>
                        <td style="width: 10%; border: none;"></td>
                        <td style="width: 45%; border: none;"></td>
                    </tr>
                    <tr style="border: none;">
                        <td style="width: 45%; border: none; padding: 0; vertical-align: bottom;">
                            <div style="border-top: 1px solid #000; margin-top: 40px;"></div>
                            <p style="text-align: center; font-size: 9px; margin-top: 5px;">
                                <?php echo htmlspecialchars($funcionario['nome']); ?><br>
                                (Assinatura do Trabalhador)
                            </p>
                        </td>
                        <td style="width: 10%; border: none;"></td>
                        <td style="width: 45%; border: none; padding: 0; vertical-align: bottom;">
                            <div style="border-top: 1px solid #000; margin-top: 40px;"></div>
                            <p style="text-align: center; font-size: 9px; margin-top: 5px;">
                                (Assinatura do Responsável Legal do Trabalhador)
                            </p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>