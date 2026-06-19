<?php
/**
 * views/orcamento/proposta_pdf.php
 *
 * Template PDF da Proposta Técnica Orçamentária
 * Gerado via Dompdf / similar (suporte a CSS2 + position:fixed)
 *
 * Variáveis esperadas:
 *   $proposta_pdf  array   — dados da proposta (campos do novo formulário)
 *   $empresa       array   — dados da empresa emissora
 *
 * Campos mapeados do novo formulário:
 *   titulo / nome_proposta      → título da proposta
 *   codigo / numero_proposta    → código / número
 *   versao_documento            → versão do documento
 *   data_proposta               → data de emissão
 *   validade_proposta / validade_dias / validade → validade em dias
 *   responsavel_nome            → elaborado por
 *   cliente_nome / cliente_sigla
 *   representante               → representante do cliente
 *   email_cliente
 *   municipio / area
 *   projeto_nome / contrato_numero
 *   descricao_geral / escopo    → escopo / objeto
 *   itens[]                     → categoria, descricao, detalhes, unidade,
 *                                  quantidade, valor_unit, desconto_item, total_item
 *   subtotal / descontos_valor / desconto_tipo / desconto_valor
 *   impostos_perc / impostos_valor
 *   total_final / total / valor_total
 *   condicao_pagamento / forma_pagamento / prazo_execucao
 *   pix_tipo_chave / pix_chave / dados_bancarios
 *   observacoes
 *   cliente_documento           → CNPJ/CPF do cliente para assinatura
 *   — legados ainda suportados: servicos[], materiais[], custos_extras[]
 */

use App\Helpers\ReportHelper;

// ── Helpers locais ──────────────────────────────────────────────────────────

/**
 * Devolve a data de expiração formatada em pt-BR dado a data base e qtd de dias.
 */
function calcDataValidade(string $dataBase, int $dias): string {
    try {
        $d = new DateTime($dataBase);
        $d->modify("+{$dias} days");
        return $d->format('d/m/Y');
    } catch (Exception $e) {
        return '—';
    }
}

/**
 * Badge HTML inline — compatível com Dompdf (sem border-radius em alguns engines,
 * mas mantemos para engines modernos).
 */
function pdfBadge(string $label, string $bg, string $border, string $color): string {
    return sprintf(
        '<span style="display:inline-block;font-size:7px;font-weight:bold;'
        . 'padding:1px 5px;border-radius:10px;border:0.3pt solid %s;'
        . 'background:%s;color:%s;letter-spacing:.02em;text-transform:uppercase">%s</span>',
        $border, $bg, $color, htmlspecialchars($label)
    );
}

// ── Paleta (espelha o formulário) ───────────────────────────────────────────
$COR = [
    'brand'          => '#008AF2',
    'brand_light'    => '#E6F1FB',
    'brand_border'   => '#B5D4F4',
    'brand_dark'     => '#0C447C',
    'success'        => '#3B6D11',
    'success_light'  => '#EAF3DE',
    'success_border' => '#C0DD97',
    'amber'          => '#633806',
    'amber_light'    => '#FAEEDA',
    'amber_border'   => '#FAC775',
    'purple'         => '#3C3489',
    'purple_light'   => '#EEEDFE',
    'purple_border'  => '#CECBF6',
    'gray'           => '#444441',
    'gray_light'     => '#F1EFE8',
    'gray_border'    => '#D3D1C7',
    'danger'         => '#A32D2D',
    'row_alt'        => '#F9FAFB',
    'border'         => '#E5E7EB',
    'text'           => '#111827',
    'muted'          => '#6B7280',
];

// Mapeamento categoria → cores do badge
$catCores = [
    'Planejamento / Coordenação'   => [$COR['brand_light'],  $COR['brand_border'],  $COR['brand_dark']],
    'Serviços de Campo'            => [$COR['success_light'],$COR['success_border'],$COR['success']],
    'Custos Reembolsáveis'         => [$COR['amber_light'],  $COR['amber_border'],  $COR['amber']],
    'Elaboração de Peças Técnicas' => [$COR['purple_light'], $COR['purple_border'], $COR['purple']],
    'Outros'                       => [$COR['gray_light'],   $COR['gray_border'],   $COR['gray']],
];

function catBadge(string $cat, array $catCores): string {
    $cor = $catCores[$cat] ?? [$GLOBALS['COR']['gray_light'], $GLOBALS['COR']['gray_border'], $GLOBALS['COR']['gray']];
    return pdfBadge($cat, $cor[0], $cor[1], $cor[2]);
}

// ── Normalização dos dados ──────────────────────────────────────────────────
$p = $proposta_pdf;

$titulo        = htmlspecialchars($p['titulo']          ?? $p['nome_proposta']       ?? 'Proposta Técnica Orçamentária');
$codigo        = htmlspecialchars($p['codigo']          ?? $p['numero_proposta']      ?? str_pad($p['id'] ?? 0, 4, '0', STR_PAD_LEFT));
$versao        = htmlspecialchars($p['versao_documento'] ?? '');
$dataEmissao   = ReportHelper::formatDate($p['data_proposta'] ?? date('Y-m-d'));
$validadeDias  = (int)($p['validade_proposta'] ?? $p['validade_dias'] ?? $p['validade'] ?? 30);
$dataValidade  = calcDataValidade($p['data_proposta'] ?? date('Y-m-d'), $validadeDias);
$responsavel   = htmlspecialchars($p['responsavel_nome'] ?? '');

$clienteNome   = htmlspecialchars($p['cliente_nome']    ?? '');
$clienteSigla  = htmlspecialchars($p['cliente_sigla']   ?? '');
$representante = htmlspecialchars($p['representante']   ?? '');
$emailCliente  = htmlspecialchars($p['email_cliente']   ?? '');
$municipio     = htmlspecialchars($p['municipio']       ?? '');
$area          = htmlspecialchars($p['area']            ?? '');

// Formata o telefone no padrão (XX) XXXXX-XXXX ou (XX) XXXX-XXXX
$rawTel = preg_replace('/\D/', '', $p['cliente_telefone'] ?? '');
if (strlen($rawTel) === 11) {
    $telefone = '(' . substr($rawTel, 0, 2) . ') ' . substr($rawTel, 2, 5) . '-' . substr($rawTel, 7);
} elseif (strlen($rawTel) === 10) {
    $telefone = '(' . substr($rawTel, 0, 2) . ') ' . substr($rawTel, 2, 4) . '-' . substr($rawTel, 6);
} else {
    $telefone = htmlspecialchars($p['cliente_telefone'] ?? '—');
}

$projetoNome   = htmlspecialchars($p['projeto_nome']    ?? '');
$contratoNum   = htmlspecialchars($p['contrato_numero'] ?? $p['contrato_id'] ?? '');

$escopo        = nl2br(htmlspecialchars($p['descricao_geral'] ?? $p['escopo'] ?? $p['descricao'] ?? ''));
$observacoes   = nl2br(htmlspecialchars($p['observacoes']     ?? $p['condicoes'] ?? ''));

$subtotal      = (float)($p['subtotal']       ?? 0);
$descontoValor = (float)($p['descontos_valor'] ?? 0);
$descontoTipo  = $p['desconto_tipo']   ?? 'percentual';
$descontoPerc  = (float)($p['desconto_valor'] ?? 0);
$impostosPerc  = (float)($p['impostos_perc']  ?? 0);
$impostosValor = (float)($p['impostos_valor'] ?? 0);
$totalFinal    = (float)($p['total_final']    ?? $p['total'] ?? $p['valor_total'] ?? 0);

$formaPagamento  = htmlspecialchars($p['forma_pagamento']    ?? '');
$condicaoPagamento = htmlspecialchars($p['condicao_pagamento'] ?? '');
$prazoExecucao   = htmlspecialchars($p['prazo_execucao']     ?? '');
$garantias       = htmlspecialchars(html_entity_decode($p['garantias'] ?? ''));

$pixTipoChave   = htmlspecialchars($p['pix_tipo_chave']     ?? '');
$pixChave       = htmlspecialchars($p['pix_chave']          ?? '');
$dadosBancarios = htmlspecialchars($p['dados_bancarios']    ?? '');

$clienteDoc    = ReportHelper::formatCpfCnpj($p['cliente_documento'] ?? '00.000.000/0000-00');
$empresaRazao  = htmlspecialchars($empresa['razao_social'] ?? 'Sua Empresa LTDA');
$empresaCnpj   = ReportHelper::formatCpfCnpj($empresa['cnpj']       ?? '00.000.000/0001-00');
$empresaEnd    = htmlspecialchars($empresa['endereco']    ?? 'Endereço não informado');
$empresaEmail  = htmlspecialchars($empresa['email']       ?? 'contato@suaempresa.com');

// Processa dados do cronograma
$crono = !empty($p['cronograma_data']) 
    ? (is_array($p['cronograma_data']) ? $p['cronograma_data'] : json_decode($p['cronograma_data'], true)) 
    : null;

$totalDurationText = "";
if ($crono && isset($crono['totalPeriods'])) {
    $n = (int)($crono['totalPeriods'] ?? 0);
    $unit = ($crono['mode'] ?? 'dias') === 'semanas' ? ' semanas' : (($crono['mode'] ?? 'dias') === 'meses' ? ' meses' : ' dias');
    $totalDurationText = "Duração prevista: " . $n . $unit;
}

// ── Agrupa itens por categoria (novo modelo) ─────────────────────────────────
// Para o PDF, vamos iterar diretamente sobre $p['itens'] para manter a ordem do formulário.
// A variável $subtotaisCat será usada para o resumo financeiro.
// Precisamos também de uma contagem de itens por categoria para o resumo.
$subtotaisCat = []; // Para o resumo financeiro
 $itemCountsCat = []; // Para a contagem de itens por categoria no resumo
if (!empty($p['itens']) && is_array($p['itens'])) {
    foreach ($p['itens'] as $item) {
        if (empty($item['descricao']) && empty($item['nome'])) continue;
        $cat = $item['categoria'] ?? 'Outros';
        if ($cat !== 'Legenda') { // Não acumula legendas no subtotal por categoria
            $qty = (float)($item['quantidade'] ?? 1);
            $vunit = (float)($item['valor_unit'] ?? $item['valor_unitario'] ?? 0);
            $desc = (float)($item['desconto_item'] ?? 0);
            $subtotaisCat[$cat] = ($subtotaisCat[$cat] ?? 0) + ($qty * $vunit * (1 - $desc / 100));
            $itemCountsCat[$cat] = ($itemCountsCat[$cat] ?? 0) + 1;
        }
    }
}

// ── Recalcula totais se não vieram calculados ─────────────────────────────────
// O subtotal já é calculado no controller e passado via $p['subtotal']
// Se não vier, recalcula a partir dos itens
if ($subtotal == 0 && !empty($p['itens'])) {
    foreach ($p['itens'] as $item) {
        if (($item['categoria'] ?? '') === 'Legenda') continue;
        $qty = (float)($item['quantidade'] ?? 1);
        $vunit = (float)($item['valor_unit'] ?? $item['valor_unitario'] ?? 0);
        $desc = (float)($item['desconto_item'] ?? 0);
        $subtotal += $qty * $vunit * (1 - $desc / 100);
    }
}

if ($descontoValor == 0 && $descontoPerc > 0) {
    $descontoValor = $descontoTipo === 'percentual'
        ? $subtotal * ($descontoPerc / 100)
        : $descontoPerc;
}
if ($impostosValor == 0 && $impostosPerc > 0) {
    $impostosValor = ($subtotal - $descontoValor) * ($impostosPerc / 100);
}
if ($totalFinal == 0) {
    $totalFinal = $subtotal - $descontoValor + $impostosValor;
}

// Prepara o logo para uso múltiplo (Cabeçalho e Marca d'água)
$logoPath = ROOT_PATH . '/public/assets/images/logo.png';
$logoBase64 = file_exists($logoPath) ? base64_encode(file_get_contents($logoPath)) : null;

// Figura decorativa da capa (ribbon Envicorp — figura_envicorp.png)
$figuracapaPath = ROOT_PATH . '/public/assets/images/figura_envicorp.png';
$figuracapaBase64 = file_exists($figuracapaPath) ? base64_encode(file_get_contents($figuracapaPath)) : null;
?>
<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<style>
/* ── Página ──────────────────────────────────────────────────────────── */
@page {
    margin: 115px 44px 70px 44px;
    size: A4 portrait;
}

/* ── Capa: sem margem superior (sem espaço para cabeçalho) ── */
@page :first {
    margin-top: 0;
    margin-bottom: 0;
}

body {
    font-family: 'Helvetica', 'Arial', sans-serif;
    font-size: 11px;
    color: <?= $COR['text'] ?>;
    line-height: 1.5;
}

/* ── Página de Capa ─────────────────────────────────────────────────── */
.cover-page {
    height: 297mm;
    position: relative;
}
.cover-logo {
    margin-top: 60px;
    margin-bottom: 50px;
    max-width: 220px;
    max-height: 100px;
}
.cover-title-group {
    margin-bottom: 60px;
}
.cover-document-label {
    font-size: 14px;
    letter-spacing: 0.3em;
    color: <?= $COR['brand'] ?>;
    font-weight: bold;
    text-transform: uppercase;
    margin-bottom: 20px;
}
.cover-title {
    font-size: 30px;
    font-weight: 900;
    color: <?= $COR['text'] ?>;
    line-height: 1.2;
    margin: 0 auto;
    max-width: 75%; /* Reduzido para abrir espaço visual para a ribbon */
}
.cover-accent {
    width: 60px;
    height: 4px;
    background-color: <?= $COR['brand'] ?>;
    margin: 30px auto;
}
.cover-ribbon {
    position: absolute;
    top: 0px;
    left: -44px;
    width: 210mm;
    height: 297mm;
    z-index: 0;
}
.cover-content {
    position: relative;
    z-index: 1;
    margin-left: 20%; /* Abre espaço para a ribbon na lateral esquerda */
    width: 80%;
    text-align: center;
}
.cover-client-label { font-size: 10px; color: <?= $COR['muted'] ?>; letter-spacing: 0.1em; margin-bottom: 8px; font-weight: bold; text-transform: uppercase;}
.cover-client-name { font-size: 20px; font-weight: bold; color: <?= $COR['brand_dark'] ?>; }
.cover-footer-bar {
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    border-top: 1px solid <?= $COR['border'] ?>;
    padding-top: 14px;
    z-index: 2;
    background: #fff;
}
/* ── Cabeçalho fixo ─────────────────────────────────────────────────── */
.pdf-header {
    position: fixed; /* Mantemos fixo, mas vamos "esconder" com a máscara */
    top: -104px; /* Refinado para melhor alinhamento na margem de 115px */
    left: 0;
    right: 0;
    height: 90px;
    border-bottom: 3px solid <?= $COR['brand'] ?>;
    background: #fff;
    padding: 0 8px;
}

.pdf-header table { width: 100%; }
.pdf-header td    { vertical-align: middle; }

.header-doc-label {
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: .08em;
    color: <?= $COR['brand'] ?>;
    font-weight: bold;
    margin: 0 0 2px;
}
.header-doc-code {
    font-size: 11px;
    background-color: <?= $COR['brand_light'] ?>;
    color: <?= $COR['brand_dark'] ?>;
    padding: 1px 5px;
    border-radius: 4px;
    border: 0.3pt solid <?= $COR['brand_border'] ?>;
    margin-left: 4px;
    display: inline-block;
}
.header-doc-title {
    font-size: 13px;
    font-weight: bold;
    color: <?= $COR['text'] ?>;
    margin: 0 0 5px;
}
.header-badge {
    display: inline-block;
    font-size: 7px;
    font-weight: bold;
    padding: 1px 5px;
    border-radius: 10px;
    border: 0.3pt solid <?= $COR['brand_border'] ?>;
    background: <?= $COR['brand_light'] ?>;
    color: <?= $COR['brand_dark'] ?>;
    letter-spacing: .04em;
    text-transform: uppercase;
    margin-right: 4px;
}
.header-badge-gray {
    border-color: <?= $COR['gray_border'] ?>;
    background: <?= $COR['gray_light'] ?>;
    color: <?= $COR['gray'] ?>;
}
.header-badge-green {
    border-color: <?= $COR['success_border'] ?>;
    background: <?= $COR['success_light'] ?>;
    color: <?= $COR['success'] ?>;
}
.header-meta {
    font-size: 9px;
    color: <?= $COR['muted'] ?>;
    text-align: right;
    line-height: 1.7;
}
.header-meta strong { color: <?= $COR['text'] ?>; }
.logo { max-width: 160px; max-height: 58px; }

/* ── Rodapé fixo ────────────────────────────────────────────────────── */
.pdf-footer {
    position: fixed; /* Essencial para repetir em todas as páginas */
    bottom: -60px; /* Ajustado para centralização na margem inferior de 70px */
    left: 0;
    right: 0;
    height: 44px;
    border-top: 0.5pt solid <?= $COR['border'] ?>;
    background: <?= $COR['row_alt'] ?>;
    padding: 6px 8px 0;
}
.pdf-footer table { width: 100%; }
.pdf-footer td    { vertical-align: middle; }
.footer-info {
    font-size: 8.5px;
    color: <?= $COR['muted'] ?>;
    line-height: 1.5;
}
.footer-versao {
    font-size: 8px;
    font-weight: bold;
    letter-spacing: .04em;
    color: <?= $COR['brand'] ?>;
}
.footer-page { font-size: 8.5px; color: <?= $COR['muted'] ?>; text-align: right; }

/* ── Seções ─────────────────────────────────────────────────────────── */
.section { margin-bottom: 20px; page-break-inside: avoid; }

.sec-title {
    font-size: 8px;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: .08em;
    color: <?= $COR['brand'] ?>;
    border-bottom: 0.5pt solid <?= $COR['brand_border'] ?>;
    padding-bottom: 4px;
    margin: 0 0 10px;
}

/* ── Grade do cliente ───────────────────────────────────────────────── */
.client-grid {
    width: 100%;
    border-collapse: collapse;
    border: 0.5pt solid <?= $COR['border'] ?>;
    border-radius: 6px;
}
.client-grid td {
    padding: 8px 12px;
    border: 0.5pt solid <?= $COR['border'] ?>;
    vertical-align: top;
    width: 50%;
}
.cg-label {
    font-size: 8px;
    text-transform: uppercase;
    letter-spacing: .05em;
    color: <?= $COR['muted'] ?>;
    font-weight: bold;
    display: block;
    margin-bottom: 2px;
}
.cg-value {
    font-size: 11px;
    font-weight: bold;
    color: <?= $COR['text'] ?>;
}

/* ── Escopo ─────────────────────────────────────────────────────────── */
.escopo-box {
    font-size: 10.5px;
    line-height: 1.7;
    color: #374151;
    background: <?= $COR['row_alt'] ?>;
    border-left: 2.5pt solid <?= $COR['brand_border'] ?>;
    padding: 8px 12px;
}

/* ── Tabela de itens ────────────────────────────────────────────────── */
.items-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 8.5px;
}
.items-table th {
    background: <?= $COR['row_alt'] ?>;
    font-size: 7.5px;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: .05em;
    color: <?= $COR['muted'] ?>;
    padding: 7px 8px;
    border-bottom: 0.5pt solid <?= $COR['border'] ?>;
    text-align: left;
}
.items-table th.r { text-align: right; }
.items-table td {
    padding: 4px 6px;
    border-bottom: 0.5pt solid <?= $COR['border'] ?>;
    color: <?= $COR['text'] ?>;
    vertical-align: top;
}
.items-table td.r { text-align: right; font-weight: bold; }
.item-details { font-size: 7.5px; color: <?= $COR['muted'] ?>; margin-top: 1px; }

/* Linha de categoria dentro da tabela */
.cat-header-row { background: <?= $COR['row_alt'] ?>; }
.cat-header-row td {
    padding: 5px 8px;
    font-size: 8px;
    color: <?= $COR['muted'] ?>;
    border-bottom: 0.5pt solid <?= $COR['border'] ?>;
}

/* Linha de subtotal por categoria */
.cat-subtotal-row td {
    background: <?= $COR['row_alt'] ?>;
    font-size: 9px;
    font-weight: bold;
    padding: 5px 8px;
    border-bottom: 0.5pt solid <?= $COR['border'] ?>;
}
.cat-subtotal-row td.r { text-align: right; }

/* ── Resumo financeiro ───────────────────────────────────────────────── */
.fin-table { width: 100%; border-collapse: collapse; }
.fin-table td { padding: 5px 10px; font-size: 11px; border-bottom: 0.5pt solid <?= $COR['border'] ?>; }
.fin-table .fl { color: <?= $COR['muted'] ?>; }
.fin-table .fv { text-align: right; font-weight: bold; color: <?= $COR['text'] ?>; }

.fin-cat-row td { background: <?= $COR['row_alt'] ?>; }
.fin-cat-badge { display: inline-block; }

.fin-total-row td {
    background: <?= $COR['brand_light'] ?>;
    border-top: 1pt solid <?= $COR['brand_border'] ?>;
    border-bottom: none;
    padding: 10px;
}
.fin-total-label {
    font-size: 9px;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: .06em;
    color: <?= $COR['brand_dark'] ?>;
}
.fin-total-val {
    font-size: 20px;
    font-weight: bold;
    color: <?= $COR['brand'] ?>;
    text-align: right;
}

/* ── Condições ──────────────────────────────────────────────────────── */
.cond-table { width: 100%; border-collapse: collapse; }
.cond-table td { padding: 8px 12px; vertical-align: top; width: 50%; border: 0.5pt solid <?= $COR['border'] ?>; }
.cond-label { font-size: 8px; text-transform: uppercase; letter-spacing: .05em; color: <?= $COR['muted'] ?>; font-weight: bold; display: block; margin-bottom: 3px; }
.cond-value { font-size: 11px; font-weight: bold; color: <?= $COR['text'] ?>; }

/* ── Observações ────────────────────────────────────────────────────── */
.obs-box {
    font-size: 10.5px;
    line-height: 1.8;
    color: #374151;
    background: <?= $COR['row_alt'] ?>;
    border-left: 2.5pt solid <?= $COR['brand_border'] ?>;
    padding: 8px 12px;
}

/* ── Assinaturas ────────────────────────────────────────────────────── */
.sig-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
.sig-table td { width: 50%; text-align: center; padding: 0 20px; vertical-align: bottom; }
.sig-line { border-top: 0.5pt solid <?= $COR['border'] ?>; margin-bottom: 6px; }
.sig-name { font-size: 11px; font-weight: bold; color: <?= $COR['text'] ?>; margin: 0; }
.sig-doc  { font-size: 9px;  color: <?= $COR['muted'] ?>; margin: 2px 0 0; }
.sig-role { font-size: 8px; text-transform: uppercase; letter-spacing: .05em; color: <?= $COR['brand'] ?>; font-weight: bold; margin: 3px 0 0; }

/* ── Cronograma ── */
.crono-table-pdf { width: 100%; border-collapse: collapse; margin-top: 5px; }
.crono-table-pdf th, .crono-table-pdf td { border: 0.1pt solid #ddd; text-align: center; padding: 0px; }
.crono-table-pdf th { background: <?= $COR['row_alt'] ?>; font-size: 5px; color: <?= $COR['muted'] ?>; height: 14px; line-height: 1; }
.crono-table-pdf th.col-ativ-pdf { text-align: left; padding-left: 5px; font-size: 7px; color: <?= $COR['brand_dark'] ?>; }
.crono-table-pdf td.col-ativ-pdf { text-align: left; padding: 2px 5px; font-size: 7px; color: <?= $COR['text'] ?>; white-space: nowrap; line-height: 1.1; }
.crono-mark { width: 100%; height: 10px; display: block; border-radius: 1px; }
.crono-mark-esc { background-color: <?= $COR['brand'] ?>; }
.crono-mark-camp { background-color: <?= $COR['success'] ?>; }
.crono-legend-pdf { margin-top: 6px; font-size: 7px; color: <?= $COR['muted'] ?>; }
.crono-legend-item-pdf { display: inline-block; margin-right: 12px; }
.crono-legend-box { display: inline-block; width: 7px; height: 7px; margin-right: 3px; vertical-align: middle; border-radius: 1px; }

/* ── Marca d'água ── */
.pdf-watermark {
    position: fixed;
    top: 28%;
    left: 10%;
    width: 80%;
    z-index: -1000;
    opacity: 0.05;
    text-align: center;
}

/* ── Máscara de Limpeza (Removida) ── */

/* ── Utilitários ────────────────────────────────────────────────────── */
.text-right  { text-align: right; }
.text-muted  { color: <?= $COR['muted'] ?>; }
.text-danger { color: <?= $COR['danger'] ?>; }
.text-brand  { color: <?= $COR['brand'] ?>; }
.fw-bold     { font-weight: bold; }
.page-break  { page-break-before: always; }
</style>
</head>
<body>

<!-- ══════════════════════════════════════════════════════
     ELEMENTOS FIXOS (Definidos no topo para repetição em todas as páginas)
══════════════════════════════════════════════════════ -->
<?php if ($logoBase64): ?>
<div class="pdf-watermark"> 
    <img src="data:image/png;base64,<?= $logoBase64 ?>" style="width: 500px;">
</div>
<?php endif; ?>

<div class="pdf-header">
    <table>
        <tr>
            <!-- Logo -->
            <td style="width:160px">
                <?php if ($logoBase64): ?>
                    <img src="data:image/png;base64,<?= $logoBase64 ?>" class="logo">
                <?php else: ?>
                    <div style="font-size:14px;font-weight:bold;color:<?= $COR['brand'] ?>"><?= $empresaRazao ?></div>
                <?php endif; ?>
            </td>

            <!-- Título e badges da proposta -->
            <td style="padding-left:16px">
                <p class="header-doc-label">
                    Proposta Técnica Orçamentária 
                    <span class="header-doc-code"><?= $codigo ?></span>
                </p>
                <p class="header-doc-title"><?= $titulo ?></p>
                <div>
                    <?php if ($contratoNum): ?>
                        <span class="header-badge header-badge-green">Contrato <?= $contratoNum ?></span>
                    <?php endif; ?>
                </div>
            </td>

            <!-- Datas e responsável -->
            <td class="header-meta" style="white-space:nowrap; padding-left:12px; vertical-align: middle;">
                Emitida em <strong><?= $dataEmissao ?></strong><br>
                Válida até <strong style="color:<?= $COR['brand'] ?>"><?= $dataValidade ?></strong>
                <?php if ($responsavel): ?>
                    <br>Elaborado por <strong><?= $responsavel ?></strong>
                <?php endif; ?>
            </td>
        </tr>
    </table>
</div>

<div class="pdf-footer">
    <table>
        <tr>
            <td>
                <div class="footer-info">
                    <?= $empresaRazao ?> &nbsp;·&nbsp; CNPJ <?= $empresaCnpj ?><br>
                    <?= $empresaEnd ?> &nbsp;|&nbsp; <?= $empresaEmail ?>
                </div>
            </td>
            <td class="footer-page">
                <?php if (!empty($versao)): ?>
                    <div class="footer-versao"><?= $versao ?></div>
                <?php endif; ?>
                <div>
                    <span class="page-number"></span>
                </div>
            </td>
        </tr>
    </table>
</div>

<!-- ══════════════════════════════════════════════════════
     PÁGINA 1: CAPA (Sem cabeçalho, sem rodapé fixo)
══════════════════════════════════════════════════════ -->
<div class="cover-page">
    <?php if ($figuracapaBase64): ?>
        <img src="data:image/png;base64,<?= $figuracapaBase64 ?>" class="cover-ribbon">
    <?php endif; ?>

    <div class="cover-content">
        <div style="margin-bottom: 40px;">
            <?php if ($logoBase64): ?>
                <img src="data:image/png;base64,<?= $logoBase64 ?>" class="cover-logo">
            <?php endif; ?>
        </div>

        <div class="cover-title-group">
            <div class="cover-document-label">Proposta Técnica Orçamentária</div>
            <h1 class="cover-title"><?= $titulo ?></h1>
            <div class="cover-accent"></div>
        </div>

        <div style="margin-top: 80px;">
            <div class="cover-client-label">Preparado para</div>
            <div class="cover-client-name"><?= $clienteNome ?></div>
        </div>
    </div>

    <!-- Rodapé da Capa: Posicionamento fixo na base da página de capa, alinhado com o conteúdo principal -->
    <div style="position: absolute; bottom: 0px; left: 0; width: 80%; border-top: 1px solid <?= $COR['border'] ?>; padding-top: 15px; margin-left: 20%;">
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="width: 35%; text-align: left; font-size: 10px; color: <?= $COR['muted'] ?>; vertical-align: bottom;">
                    Código: <strong><?= $codigo ?></strong><br>
                    Emissão: <strong><?= $dataEmissao ?></strong>
                </td>
                <td style="width: 30%; text-align: center; vertical-align: middle;">
                    <!-- QR Code movido para o rodapé da última página -->
                </td>
                <td style="width: 35%; text-align: right; font-size: 10px; color: <?= $COR['muted'] ?>; vertical-align: bottom;">
                    Elaboração: <strong><?= $responsavel ?></strong><br>
                    Validade: <strong><?= $dataValidade ?></strong>
                </td>
            </tr>
        </table>
    </div>
</div>

<div style="page-break-after: always;"></div>

<!-- ══════════════════════════════════════════════════════
     PÁGINA 2: INSTRUÇÕES DE ACEITE DIGITAL (Sem cabeçalho, com rodapé fixo)
══════════════════════════════════════════════════════ -->
<div style="padding: 20px; text-align: center;">

    <div style="margin-top: 10px; margin-bottom: 40px;">
        <h2 style="font-size: 24px; font-weight: 900; color: <?= $COR['brand_dark'] ?>; text-transform: uppercase; letter-spacing: 0.12em;">Instruções de Aceite Digital</h2>
        <div style="width: 60px; height: 4px; background-color: <?= $COR['brand'] ?>; margin: 15px auto;"></div>
    </div>

    <div style="text-align: left; max-width: 85%; margin: 0 auto; line-height: 1.8;">
        <p style="font-size: 12px; margin-bottom: 25px; color: <?= $COR['text'] ?>;">
            Esta proposta utiliza tecnologia de <strong>Assinatura Eletrônica</strong> para agilizar o processo de contratação com total segurança e validade jurídica. Siga os passos abaixo para formalizar sua aprovação:
        </p>

        <table style="width: 100%; border-collapse: collapse; margin-bottom: 40px;">
            <tr>
                <td style="width: 35px; vertical-align: top; padding-top: 5px;"><div style="background: <?= $COR['brand'] ?>; color: #fff; width: 22px; height: 22px; line-height: 22px; text-align: center; border-radius: 50%; font-weight: bold; font-size: 10px;">1</div></td>
                <td style="padding-bottom: 20px;">
                    <strong style="color: <?= $COR['brand_dark'] ?>; font-size: 11px;">Acesse o link de aprovação</strong><br>
                    Aponte a câmera do seu smartphone para o <strong>QR Code</strong> localizado na capa deste documento ou utilize o link seguro enviado por nossos consultores.
                </td>
            </tr>
            <tr>
                <td style="width: 35px; vertical-align: top; padding-top: 5px;"><div style="background: <?= $COR['brand'] ?>; color: #fff; width: 22px; height: 22px; line-height: 22px; text-align: center; border-radius: 50%; font-weight: bold; font-size: 10px;">2</div></td>
                <td style="padding-bottom: 20px;">
                    <strong style="color: <?= $COR['brand_dark'] ?>; font-size: 11px;">Revise as informações</strong><br>
                    No portal de aprovação, confira o escopo técnico, valores e prazos. Você terá acesso à versão digitalizada e atualizada do orçamento.
                </td>
            </tr>
            <tr>
                <td style="width: 35px; vertical-align: top; padding-top: 5px;"><div style="background: <?= $COR['brand'] ?>; color: #fff; width: 22px; height: 22px; line-height: 22px; text-align: center; border-radius: 50%; font-weight: bold; font-size: 10px;">3</div></td>
                <td style="padding-bottom: 20px;">
                    <strong style="color: <?= $COR['brand_dark'] ?>; font-size: 11px;">Formalize o Aceite</strong><br>
                    Informe seu nome completo e, se necessário, anexe uma mensagem ou instrução adicional para nossa equipe de planejamento.
                </td>
            </tr>
        </table>

        <div style="background: <?= $COR['row_alt'] ?>; border: 0.5pt solid <?= $COR['brand_border'] ?>; padding: 20px; border-radius: 10px;">
            <p style="font-size: 9px; color: <?= $COR['brand_dark'] ?>; margin: 0; line-height: 1.5;">
                <strong>VALIDADE JURÍDICA:</strong> O aceite eletrônico é amparado pela MP nº 2.200-2/2001 e pela Lei nº 14.063/2020. Para cada aprovação, registramos o endereço IP, data, hora e geolocalização aproximada do dispositivo, garantindo a integridade e o não-repúdio da assinatura.
            </p>
        </div>
    </div>
</div>

<div style="page-break-after: always;"></div>

<!-- Numeração de páginas (Dompdf) -->
<script type="text/php">
    if (isset($pdf)) {
        $pageNum = $pdf->get_page_number();
        $pageCount = $pdf->get_page_count();

        // 1. Oculta cabeçalho/rodapé nas páginas iniciais desenhando retângulos brancos por cima
        if ($pageNum == 1) {
            // Capa: cobre cabeçalho (topo) e rodapé (base) — garante capa 100% limpa
            $pdf->filled_rectangle(0, 0, $pdf->get_width(), 130, array(1, 1, 1));
            $pdf->filled_rectangle(0, $pdf->get_height() - 90, $pdf->get_width(), 90, array(1, 1, 1));
        } elseif ($pageNum == 2) {
            // Instruções: Cobre apenas o cabeçalho
            $pdf->filled_rectangle(0, 0, $pdf->get_width(), 130, array(1, 1, 1));
        }

        // 2. Adiciona o número da página em todas as páginas (exceto capa)
        if ($pageNum > 1) {
            $text  = "Página {PAGE_NUM} de {PAGE_COUNT}";
            $size  = 8;
            $font  = $fontMetrics->getFont("Helvetica");
            $width = $fontMetrics->get_text_width($text, $font, $size);
            $x     = ($pdf->get_width() - $width) - 44; // Ajusta para a margem
            $y     = $pdf->get_height() - 28;
            $pdf->page_text($x, $y, $text, $font, $size, array(0.42, 0.45, 0.50));
        }

        // 3. Adiciona o QR Code APENAS na última página
        if ($pageNum == $pageCount && $pageNum > 1) {
            $token = $GLOBALS['p']['token_aprovacao'] ?? null;
            if ($token) {
                $qrUrl = BASE_URL . "/orcamento/aprovarPropostaPublica/" . $token;
                $qrImage = "https://api.qrserver.com/v1/create-qr-code/?size=80x80&data=" . urlencode($qrUrl);
                $qr_width = 35;
                $x_qr = ($pdf->get_width() - $qr_width) / 2;
                $y_qr = $pdf->get_height() - 62;
                $pdf->image($qrImage, $x_qr, $y_qr, $qr_width, $qr_width);
            }
        }
    }
</script>

<!-- ══════════════════════════════════════════════════════
     1. DADOS DO CLIENTE
══════════════════════════════════════════════════════ -->
<div class="section">
    <p class="sec-title">1- DADOS DO CLIENTE</p>
    <table class="client-grid">
        <tr>
            <td>
                <span class="cg-label">Interessado</span>
                <span class="cg-value">
                    <?= $clienteNome ?>
                    <?php if ($clienteSigla): ?>
                        &nbsp;<span style="font-size:9px;color:<?= $COR['muted'] ?>">(<?= $clienteSigla ?>)</span>
                    <?php endif; ?>
                </span>
            </td>
            <td>
                <span class="cg-label">Documento (CNPJ/CPF)</span>
                <span class="cg-value"><?= $clienteDoc ?></span>
            </td>
        </tr>
        <tr>
            <td>
                <span class="cg-label">Representante / Contato</span>
                <span class="cg-value"><?= $representante ?: '—' ?></span>
            </td>
            <td>
                <span class="cg-label">E-mail / Telefone</span>
                <span class="cg-value">
                    <?= $emailCliente ?: '—' ?>
                    <?php if ($telefone): ?>
                        <br><span style="font-weight:normal;font-size:10px"><?= $telefone ?></span>
                    <?php endif; ?>
                </span>
            </td>
        </tr>
        <tr>
            <td>
                <span class="cg-label">Município / Estado</span>
                <span class="cg-value"><?= $municipio ?: '—' ?></span>
            </td>
            <td>
                <span class="cg-label">Área / Projeto Vinculado</span>
                <span class="cg-value">
                    <?= $area ?: '—' ?>
                    <?php if ($projetoNome): ?>
                        <br><span style="font-size:9px;color:<?= $COR['muted'] ?>">Projeto: <?= $projetoNome ?></span>
                    <?php endif; ?>
                </span>
            </td>
        </tr>
    </table>
</div>

<!-- ══════════════════════════════════════════════════════
     2. ESCOPO / OBJETO
══════════════════════════════════════════════════════ -->
<?php if ($escopo && $escopo !== 'N/A'): ?>
<div class="section">
    <p class="sec-title">2- ESCOPO / OBJETO</p>
    <div class="escopo-box"><?= $escopo ?></div>
</div>
<?php endif; ?>

<!-- ══════════════════════════════════════════════════════
     3.1 CRONOGRAMA DE EXECUÇÃO
══════════════════════════════════════════════════════ -->
<?php 
$hasCronoMarks = false;
if ($crono && !empty($crono['state'])) {
    foreach ($crono['state'] as $st) {
        if ($st > 0) { $hasCronoMarks = true; break; }
    }
}
if ($crono && !empty($crono['activities']) && $hasCronoMarks): ?>
    <div class="section">
        <p class="sec-title">3- CRONOGRAMA ESTIMADO DE EXECUÇÃO</p>
        <table class="crono-table-pdf">
            <thead>
                <tr>
                    <th class="col-ativ-pdf">Atividades do Projeto</th>
                    <?php 
                    $n = (int)($crono['totalPeriods'] ?? 24);
                    $mode = $crono['mode'] ?? 'dias';
                    $start = $crono['startDate'] ?? null;
                    $totalEsc = 0; $totalCamp = 0;
                    for ($i = 0; $i < $n; $i++): 
                        $label = $i + 1; $sub = '';
                        if ($start) {
                            $d = new DateTime($start);
                            if ($mode === 'semanas') { $d->modify("+" . ($i * 7) . " days"); $label = "S".($i+1); $sub = $d->format('d/m'); }
                            elseif ($mode === 'meses') { $d->modify("+" . $i . " months"); $label = "M".($i+1); $sub = $d->format('m/y'); }
                            else { $d->modify("+" . $i . " days"); $sub = $d->format('d/m'); }
                        }
                    ?>
                        <th><?= $label ?><br><span style="font-size:4px;font-weight:normal"><?= $sub ?></span></th>
                    <?php endfor; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($crono['activities'] as $ri => $name): ?>
                <tr>
                    <td class="col-ativ-pdf"><?= htmlspecialchars($name) ?></td>
                    <?php for ($ci = 0; $ci < $n; $ci++): 
                        $val = $crono['state'][$ri . '_' . $ci] ?? 0;
                        $class = $val == 1 ? 'crono-mark-esc' : ($val == 2 ? 'crono-mark-camp' : '');
                        if ($val == 1) $totalEsc++;
                        if ($val == 2) $totalCamp++;
                    ?>
                        <td><?php if ($val > 0): ?><div class="crono-mark <?= $class ?>">&nbsp;</div><?php endif; ?></td>
                    <?php endfor; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="crono-legend-pdf">
            <div class="crono-legend-item-pdf"><div class="crono-legend-box crono-mark-esc"></div> Atividade de Escritório (<strong><?= $totalEsc ?></strong>)</div>
            <div class="crono-legend-item-pdf"><div class="crono-legend-box crono-mark-camp"></div> Atividade de Campo (<strong><?= $totalCamp ?></strong>)</div>
            <?php if ($totalDurationText): ?><div class="crono-legend-item-pdf" style="margin-left: 20px;"><strong><?= $totalDurationText ?></strong></div><?php endif; ?>
            <div class="crono-legend-item-pdf" style="margin-left: 20px;">* Cronograma sujeito a alterações conforme condições climáticas ou liberações de órgãos anuentes.</div>
        </div>
    </div>
<?php endif; ?>


<!-- ══════════════════════════════════════════════════════
     3. COMPOSIÇÃO DE CUSTOS (itens agrupados por categoria)
══════════════════════════════════════════════════════ -->
<?php
$currentCategory = null; // Para controlar a exibição dos cabeçalhos de categoria na tabela
$categorySubtotal = 0;
$subSectionCounter = 0; // Novo contador para sub-seções
$hasItemsToDisplay = !empty($p['itens']) && is_array($p['itens']);
?>

<?php if ($hasItemsToDisplay): ?>
<div class="section">
    <p class="sec-title">4- COMPOSIÇÃO DE CUSTOS</p>
    <table class="items-table">
        <thead>
            <tr>
                <th style="width:24%">Descrição</th> <!-- Ajustado para 24% para compensar a remoção da coluna de categoria -->
                <th style="width:24%">Detalhes</th>
                <th style="width:7%">Un.</th>
                <th style="width:7%" class="r">Qtd.</th>
                <th style="width:13%" class="r">Vlr. Unit.</th>
                <th style="width:7%" class="r">Desc.%</th>
                <th style="width:12%" class="r">Total</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($p['itens'] as $idx => $item):
            $cat = $item['categoria'] ?? 'Outros';
            $isLegend = ($cat === 'Legenda');
            
            // Se havia uma categoria sendo processada e o contexto mudou (nova categoria ou legenda)
            if ($currentCategory !== null && $cat !== $currentCategory) {
                $corPrev = $catCores[$currentCategory] ?? [$COR['gray_light'], $COR['gray_border'], $COR['gray']];
                ?>
                <tr class="cat-subtotal-row">
                    <td colspan="6" class="text-right text-muted" style="font-size:8px;text-transform:uppercase;letter-spacing:.04em;padding:5px 8px;">
                        Subtotal <?= htmlspecialchars($currentCategory) ?>
                    </td>
                    <td class="r" style="color:<?= $corPrev[2] ?>"><?= ReportHelper::formatCurrency($categorySubtotal) ?></td>
                </tr>
                <?php                
                $categorySubtotal = 0; // Reseta o subtotal APÓS imprimir o da categoria anterior
            }

            // Se for uma legenda, renderiza-a diretamente e reseta a categoria atual
            if ($isLegend): ?>
                <tr style="background-color: <?= $COR['row_alt'] ?>;">
                    <td colspan="7" style="padding: 10px 12px; border-bottom: 0.5pt solid <?= $COR['border'] ?>;">
                        <?php $subSectionCounter++; // Incrementa o contador da sub-seção ?>
                        <div style="font-size: 10px; font-weight: 800; color: <?= $COR['brand_dark'] ?>; text-transform: uppercase; letter-spacing: 0.05em;">
                            <span style="font-size: 12px; margin-right: 5px; color: <?= $COR['brand'] ?>;">4.<?= $subSectionCounter ?></span> <!-- Numeração da sub-seção -->
                            <?= htmlspecialchars($item['descricao'] ?? $item['nome'] ?? '') ?>
                        </div>
                    </td>
                </tr>
            <?php
                $currentCategory = null; // Reseta a categoria atual após uma legenda
                continue; // Pula para o próximo item
            endif;

            // Se a categoria mudou (e não é uma legenda), imprime o cabeçalho da nova categoria
            if ($cat !== $currentCategory) {
                $cor = $catCores[$cat] ?? [$COR['gray_light'], $COR['gray_border'], $COR['gray']];
                ?>
                <tr class="cat-header-row">
                    <td colspan="7">
                        <?= pdfBadge($cat, $cor[0], $cor[1], $cor[2]) ?>
                    </td>
                </tr>
                <?php
                $currentCategory = $cat;
            }

            // Renderiza a linha do item
            $qty       = (float)($item['quantidade']    ?? 1);
            $vunit     = (float)($item['valor_unit']    ?? $item['valor_unitario'] ?? 0);
            $descItem  = (float)($item['desconto_item'] ?? 0);
            $totalItem = isset($item['total_item']) && $item['total_item'] > 0
                ? (float)$item['total_item']
                : $qty * $vunit * (1 - $descItem / 100);
            $descricao = htmlspecialchars($item['descricao'] ?? $item['nome'] ?? '');
            $detalhes  = htmlspecialchars($item['detalhes']  ?? '');
            $unidade   = htmlspecialchars($item['unidade']   ?? 'un');

            $categorySubtotal += $totalItem; // Acumula o subtotal para a categoria atual
            ?>
            <tr>
                <td><?= $descricao ?></td>
                <td>
                    <?php if ($detalhes): ?>
                        <span class="item-details"><?= $detalhes ?></span>
                    <?php else: ?>
                        <span class="text-muted">—</span>
                    <?php endif; ?>
                </td>
                <td><?= $unidade ?></td>
                <td class="r"><?= number_format($qty, $qty == intval($qty) ? 0 : 3, ',', '.') ?></td>
                <td class="r"><?= ReportHelper::formatCurrency($vunit) ?></td>
                <td class="r text-muted"><?= $descItem > 0 ? number_format($descItem, 2, ',', '.') . '%' : '—' ?></td>
                <td class="r"><?= ReportHelper::formatCurrency($totalItem) ?></td>
            </tr>
        <?php endforeach; ?>
        <?php
        // Imprime o subtotal da última categoria, se houver
        if ($currentCategory !== null && $categorySubtotal > 0) { // Garante que o subtotal da última categoria seja impresso
            $corPrev = $catCores[$currentCategory] ?? [$COR['gray_light'], $COR['gray_border'], $COR['gray']];
            ?>
            <tr class="cat-subtotal-row">
                <td colspan="6" class="text-right text-muted" style="font-size:8px;text-transform:uppercase;letter-spacing:.04em">
                    Subtotal <?= htmlspecialchars($currentCategory) ?>
                </td>
                <td class="r" style="color:<?= $corPrev[2] ?>"><?= ReportHelper::formatCurrency($categorySubtotal) ?></td>
            </tr>
            <?php
        }
        ?>
        </tbody>
    </table>
</div>
<?php else: ?>
<div class="section">
    <p class="sec-title">4- COMPOSIÇÃO DE CUSTOS</p>
    <p style="font-size:10px; color:<?= $COR['muted'] ?>; padding: 8px 0;">Nenhum item detalhado nesta proposta.</p>
</div>
<?php endif; ?>


<!-- ══════════════════════════════════════════════════════
     4. RESUMO FINANCEIRO
══════════════════════════════════════════════════════ -->
<div class="section">
    <p class="sec-title">5- RESUMO FINANCEIRO</p>
    <table class="fin-table">

        <!-- Linhas de subtotal por categoria (para o resumo geral) -->
        <?php foreach ($subtotaisCat as $cat => $val): // Itera sobre os subtotais para garantir que todas as categorias com valor apareçam
            $cor = $catCores[$cat] ?? [$COR['gray_light'], $COR['gray_border'], $COR['gray']];
            $cnt = $itemCountsCat[$cat] ?? 0; // Usa a nova variável para a contagem
        ?>
        <tr class="fin-cat-row">
            <td class="fl">
                <?= pdfBadge($cat, $cor[0], $cor[1], $cor[2]) ?>
                &nbsp;<span style="font-size:9px;color:<?= $COR['muted'] ?>"><?= $cnt ?> <?= $cnt === 1 ? 'item' : 'itens' ?></span> <!-- Exibe a contagem correta -->
            </td>
            <td class="fv"><?= ReportHelper::formatCurrency($val) ?></td>
        </tr>
        <?php endforeach; ?>

        <!-- Legado: custos extras -->
        <?php if (!empty($p['custos_extras'])): ?>
        <?php foreach ($p['custos_extras'] as $key => $value):
            $fv = floatval(str_replace(',', '.', str_replace('.', '', $value)));
            if ($fv <= 0) continue;
        ?>
        <tr>
            <td class="fl"><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $key))) ?></td>
            <td class="fv"><?= ReportHelper::formatCurrency($value) ?></td>
        </tr>
        <?php endforeach; endif; ?>

        <!-- Separador -->
        <tr><td colspan="2" style="padding:0;border-bottom:0.5pt solid <?= $COR['border'] ?>"></td></tr>

        <!-- Desconto -->
        <?php if ($descontoValor > 0): ?>
        <tr>
            <td class="fl">
                Desconto
                <?php if ($descontoTipo === 'percentual' && $descontoPerc > 0): ?>
                    <span style="font-size:9px">(<?= number_format($descontoPerc, 2, ',', '.') ?>%)</span>
                <?php endif; ?>
            </td>
            <td class="fv text-danger">− <?= ReportHelper::formatCurrency($descontoValor) ?></td>
        </tr>
        <?php endif; ?>

        <!-- Impostos -->
        <?php if ($impostosValor > 0): ?>
        <tr>
            <td class="fl">
                Impostos / Taxas
                <?php if ($impostosPerc > 0): ?>
                    <span style="font-size:9px">(<?= number_format($impostosPerc, 2, ',', '.') ?>%)</span>
                <?php endif; ?>
            </td>
            <td class="fv"><?= ReportHelper::formatCurrency($impostosValor) ?></td>
        </tr>
        <?php endif; ?>

        <!-- Total geral -->
        <tr class="fin-total-row">
            <td class="fin-total-label">Total Geral da Proposta</td>
            <td class="fin-total-val"><?= ReportHelper::formatCurrency($totalFinal) ?></td>
        </tr>
    </table>
</div>


<!-- ══════════════════════════════════════════════════════
     5. CONDIÇÕES COMERCIAIS
══════════════════════════════════════════════════════ -->
<div class="section">
    <p class="sec-title">6- CONDIÇÕES COMERCIAIS</p>
    <table class="cond-table">
        <tr>
            <td>
                <span class="cond-label">Condição de Pagamento</span>
                <span class="cond-value"><?= $condicaoPagamento ?: ($formaPagamento ?: '—') ?></span>
            </td>
            <td>
                <span class="cond-label">Forma de Pagamento</span>
                <span class="cond-value">
                    <?= $formaPagamento ?: '—' ?>
                    <?php if ($formaPagamento === 'Pix' && $pixChave): ?>
                        <br><span style="font-size:9px; font-weight:normal; color:<?= $COR['muted'] ?>">Chave (<?= $pixTipoChave ?>): <?= $pixChave ?></span>
                    <?php elseif ($formaPagamento === 'Transferência Bancária' && $dadosBancarios): ?>
                        <br><span style="font-size:9px; font-weight:normal; color:<?= $COR['muted'] ?>"><?= nl2br($dadosBancarios) ?></span>
                    <?php endif; ?>
                </span>
            </td>
        </tr>
        <tr>
            <td>
                <span class="cond-label">Prazo de Execução</span>
                <span class="cond-value"><?= $prazoExecucao ?: '—' ?></span>
            </td>
            <td>
                <span class="cond-label">Validade da Proposta</span>
                <span class="cond-value text-brand">
                    <?= $validadeDias ?> dias &nbsp;·&nbsp; expira <?= $dataValidade ?>
                </span>
            </td>
        </tr>
        <?php if ($garantias): ?>
        <tr>
            <td colspan="2">
                <span class="cond-label">Garantias</span>
                <span class="cond-value"><?= $garantias ?></span>
            </td>
        </tr>
        <?php endif; ?>
    </table>
</div>


<!-- ══════════════════════════════════════════════════════
     6. OBSERVAÇÕES
══════════════════════════════════════════════════════ -->
<?php if ($observacoes && $observacoes !== 'N/A'): ?>
<div class="section">
    <p class="sec-title">7- OBSERVAÇÕES</p>
    <div class="obs-box"><?= $observacoes ?></div>
</div>
<?php endif; ?>


<!-- ══════════════════════════════════════════════════════
     7. ASSINATURAS
══════════════════════════════════════════════════════ -->
<div class="section" style="margin-top:30px">
    <p class="sec-title">Assinaturas</p>
    <table class="sig-table">
        <tr>
            <!-- Contratada -->
            <td>
                <div style="height:40px"></div>
                <div class="sig-line"></div>
                <p class="sig-name"><?= $empresaRazao ?></p>
                <p class="sig-doc">CNPJ: <?= $empresaCnpj ?></p>
                <p class="sig-role">Contratada</p>
            </td>
            <!-- Contratante -->
            <td>
                <div style="height:40px"></div>
                <div class="sig-line"></div>
                <p class="sig-name"><?= $clienteNome ?: 'Contratante' ?></p>
                <p class="sig-doc">CPF/CNPJ: <?= $clienteDoc ?></p>
                <p class="sig-role">Contratante</p>
            </td>
        </tr>
    </table>
</div>

</body>
</html>
