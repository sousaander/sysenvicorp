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
        '<span style="display:inline-block;font-size:8px;font-weight:bold;'
        . 'padding:1px 5px;border-radius:10px;border:0.3pt solid %s;'
        . 'background:%s;color:%s;letter-spacing:.02em;text-transform:uppercase">%s</span>',
        $border, $bg, $color, htmlspecialchars($label)
    );
}

/**
 * Verifica se há descontos (itens ou global) a serem exibidos no resumo financeiro.
 */
function hasDiscountsToShow(float $totalItemDiscount, float $descontoValor): bool {
    return $totalItemDiscount > 0 || $descontoValor > 0;
}

// ── Paleta (espelha o formulário) ───────────────────────────────────────────
$COR = [
    'brand'          => '#2563eb',
    'brand_light'    => '#dbeafe',
    'brand_border'   => '#93c5fd',
    'brand_dark'     => '#1d4ed8',
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
    'row_alt'           => '#F9FAFB',
    'border'            => '#E5E7EB',
    'text'              => '#111827',
    'muted'             => '#6B7280',
    'table_header'      => '#0C92D7',
    'table_header_light'=> '#E6F4FC',
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

// Fallback: extrai município da primeira linha da contextualização se o campo estiver vazio
$contextualizacao = !empty($p['contextualizacao'])
    ? (is_array($p['contextualizacao']) ? $p['contextualizacao'] : [])
    : [];
$ocultarTabelaContexto = !empty($p['contextualizacao_ocultar_vazias']);
if (empty($municipio) && !empty($contextualizacao)) {
    $firstRow = reset($contextualizacao);
    if (!empty($firstRow['municipio'])) {
        $municipio = htmlspecialchars($firstRow['municipio']);
    }
}
if (empty($area) && !empty($contextualizacao)) {
    $firstRow = reset($contextualizacao);
    if (!empty($firstRow['area'])) {
        $area = htmlspecialchars($firstRow['area']);
    }
}

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

$escopo        = htmlspecialchars($p['descricao_geral'] ?? $p['escopo'] ?? $p['descricao'] ?? '');
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

$clienteDoc      = ReportHelper::formatCpfCnpj($p['cliente_documento'] ?? '00.000.000/0000-00');
$clienteEndereco = htmlspecialchars($p['cliente_endereco'] ?? '');
$clienteLogradouro = htmlspecialchars($p['cliente_logradouro'] ?? '');
$clienteNumero = htmlspecialchars($p['cliente_numero'] ?? '');
$clienteComplemento = htmlspecialchars($p['cliente_complemento'] ?? '');
$clienteBairro = htmlspecialchars($p['cliente_bairro'] ?? '');
$clienteMunicipio = htmlspecialchars($p['cliente_municipio'] ?? '');
$clienteUf = htmlspecialchars($p['cliente_uf'] ?? '');
$empresaRazao    = htmlspecialchars($empresa['razao_social'] ?? 'Sua Empresa LTDA');
$empresaCnpj     = ReportHelper::formatCpfCnpj($empresa['cnpj']       ?? '00.000.000/0001-00');
$empresaEnd      = htmlspecialchars($empresa['endereco']    ?? 'Endereço não informado');
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

// ── Equipe ──────────────────────────────────────────────────────────
$equipe = !empty($p['equipe'])
    ? (is_array($p['equipe']) ? $p['equipe'] : [])
    : [];

// ── Agrupa itens por categoria (novo modelo) ─────────────────────────────────
// Para o PDF, vamos iterar diretamente sobre $p['itens'] para manter a ordem do formulário.
// A variável $subtotaisCat será usada para o resumo financeiro.
// Precisamos também de uma contagem de itens por categoria para o resumo.
$subtotaisCat = []; // Para o resumo financeiro
$itemCountsCat = []; // Para a contagem de itens por categoria no resumo
$totalItemDiscount = 0; // Total de descontos por item
$grossSubtotal = 0;
if (!empty($p['itens']) && is_array($p['itens'])) {
    foreach ($p['itens'] as $item) {
        if (empty($item['descricao']) && empty($item['nome'])) continue;
        $cat = $item['categoria'] ?? 'Outros';
        if ($cat !== 'Legenda' && $cat !== 'Titulo' && $cat !== 'Subtitulo') {
            $qty = (float)($item['quantidade'] ?? 1);
            $vunit = (float)($item['valor_unit'] ?? $item['valor_unitario'] ?? 0);
            $desc = (float)($item['desconto_item'] ?? 0);
            $grossSubtotal += $qty * $vunit;
            $totalItemDiscount += $qty * $vunit * ($desc / 100);
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
        $cat = $item['categoria'] ?? '';
        if ($cat === 'Legenda' || $cat === 'Titulo' || $cat === 'Subtitulo') continue;
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

// Assinatura digital do representante (opcional — assinatura.png)
$assinaturaPath = ROOT_PATH . '/public/assets/images/assinatura.png';
$assinaturaBase64 = file_exists($assinaturaPath) ? base64_encode(file_get_contents($assinaturaPath)) : null;

// Configuração de assinatura da proposta
$assinaturaTipo = $p['assinatura_tipo'] ?? 'imagem';
$assinaturaImagemProposta = $p['assinatura_imagem'] ?? null;
$assinaturaCertNome = htmlspecialchars($p['assinatura_certificado_nome'] ?? '');
$assinaturaCertCpf = $p['assinatura_certificado_cpf'] ?? '';
$sigImgBase64 = $assinaturaImagemProposta ?: $assinaturaBase64;

// Configuração de assinatura do Elaborador (Responsável Técnico)
$assinaturaElaboradorResp = !empty($p['assinatura_elaborador_responsavel']);
$assinaturaElaboradorTipo = $p['assinatura_elaborador_tipo'] ?? 'imagem';
$assinaturaElaboradorImagem = $p['assinatura_elaborador_imagem'] ?? null;
$assinaturaElaboradorCertNome = htmlspecialchars($p['assinatura_elaborador_certificado_nome'] ?? '');
$assinaturaElaboradorCertCpf = $p['assinatura_elaborador_certificado_cpf'] ?? '';
$elaboradorSigImg = $assinaturaElaboradorImagem ?: $assinaturaBase64;
?>
<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<style>
/* ── Página ──────────────────────────────────────────────────────────── */
/* Margem inferior alargada para 2.4cm: reserva espaço suficiente para o
   bloco de conteúdo terminar, o rodapé fixo e ainda sobrar um respiro até
   a borda física da folha (ver .pdf-footer abaixo). */
@page {
    margin: 3cm 2cm 2.4cm 3cm;
    size: A4 portrait;
}

/* ── Capa: sem margens (sem espaço para cabeçalho, full bleed) ── */
@page :first {
    margin: 0;
}

body {
    font-family: 'Helvetica', 'Arial', sans-serif;
    font-size: 12px;
    color: <?= $COR['text'] ?>;
    line-height: 1.5;
}

/* ── Página de Capa ─────────────────────────────────────────────────── */
.cover-page {
    height: 297mm;
    position: relative;
    background: #fff;
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
    font-size: 16px;
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
    top: 0;
    left: 0;
    width: 210mm;
    height: 100%;
    z-index: 0;
}
.cover-page {
    height: 297mm;
    position: relative;
    background: #fff;
}
.cover-content {
    position: relative;
    z-index: 1;
    margin-left: 20%; /* Abre espaço para a ribbon na lateral esquerda */
    width: 80%;
    text-align: center;
}
.cover-client-label { font-size: 11px; color: <?= $COR['muted'] ?>; letter-spacing: 0.1em; margin-bottom: 8px; font-weight: bold; text-transform: uppercase;}
.cover-client-name { font-size: 22px; font-weight: bold; color: <?= $COR['brand_dark'] ?>; }

/* Ficha técnica da capa (pills de metadados) */
.cover-meta-strip {
    margin-top: 70px;
    padding-top: 20px;
    border-top: 0.5pt solid <?= $COR['border'] ?>;
}
.cover-meta-grid {
    width: 100%;
    border-collapse: collapse;
}
.cover-meta-pill {
    text-align: center;
    padding: 10px 6px;
    border-right: 0.5pt solid <?= $COR['border'] ?>;
}
.cover-meta-pill:last-child { border-right: none; }
.cover-meta-label {
    font-size: 8px;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: <?= $COR['muted'] ?>;
    font-weight: bold;
    display: block;
    margin-bottom: 4px;
}
.cover-meta-value {
    font-size: 12px;
    font-weight: bold;
    color: <?= $COR['text'] ?>;
}

/* ── Cabeçalho fixo ─────────────────────────────────────────────────── */
.pdf-header {
    position: fixed; /* Mantemos fixo, mas vamos "esconder" com a máscara */
    top: -102px; /* Ajustado para margem superior de 3cm (ABNT) */
    left: 0;
    right: 0;
    height: 90px;
    border-bottom: 3px solid <?= $COR['brand'] ?>;
    background: #fff;
    padding: 0 8px;
}

.pdf-header table { width: 100%; }
.pdf-header td    { vertical-align: middle; }
.pdf-header .logo-td { width: 1%; white-space: nowrap; }
.pdf-header .text-td { padding-left: 6px; }
.pdf-header .meta-td { white-space: nowrap; padding-left: 12px; vertical-align: middle; width: 145px; }

.header-doc-label {
    font-size: 9px;
    text-transform: uppercase;
    letter-spacing: .02em;
    color: <?= $COR['brand'] ?>;
    font-weight: bold;
    margin: 0 0 2px;
}
.header-doc-code {
    font-size: 9px;
    color: <?= $COR['brand_dark'] ?>;
    font-weight: bold;
    margin-left: 4px;
}
.header-doc-title {
    font-size: 12px;
    font-weight: bold;
    color: <?= $COR['text'] ?>;
    margin: 0 0 5px;
}
.header-badge {
    display: inline-block;
    font-size: 8px;
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
    line-height: 1.6;
}
.header-meta strong { color: <?= $COR['text'] ?>; }
.logo { max-width: 160px; max-height: 58px; }

/* ── Rodapé fixo ──────────────────────────────────────────────────────
   No Dompdf, "bottom: 0" em position:fixed é calculado a partir do limite
   inferior da CAIXA DE CONTEÚDO (onde termina a margem da página) — não a
   partir da borda física da folha. Por isso, com bottom:0, o rodapé nascia
   colado exatamente onde o conteúdo termina, podendo parecer "grudado" ou
   invadido por blocos que preenchem a página inteira.

   A correção usa um deslocamento NEGATIVO em "bottom" para empurrar o
   rodapé para dentro da margem inferior da página (2.4cm, definida no
   @page acima), aproximando-o da borda física do papel e abrindo uma
   folga real entre ele e qualquer conteúdo da página.

   O padding vertical (em vez de height fixo) garante boa compatibilidade
   com o motor de renderização do Dompdf, mantendo a altura previsível
   mesmo se o texto do rodapé variar entre páginas. */
.pdf-footer {
    position: fixed;
    left: 0;
    right: 0;
    bottom: -1.7cm;      /* empurra o bloco para perto do pé físico da folha */
    padding: 5pt 8px 6pt; /* altura resulta do padding — estável e compatível com Dompdf */
    background: <?= $COR['row_alt'] ?>;
    font-family: 'Helvetica', 'Arial', sans-serif;
    font-size: 7pt;
    color: <?= $COR['muted'] ?>;
    line-height: 1.3;
}
.pdf-footer table {
    width: 100%;
    border-collapse: collapse;
}
.pdf-footer td {
    padding: 0;
    vertical-align: middle;
}
.footer-left {
    text-align: left;
    font-size: 6.5pt;
    color: <?= $COR['text'] ?>;
    line-height: 1.3;
}
.footer-right {
    text-align: right;
}
.footer-version {
    font-size: 6.5pt;
    color: <?= $COR['text'] ?>;
    line-height: 1.3;
    white-space: nowrap;
}

/* ── Seções ─────────────────────────────────────────────────────────── */
.section { margin-bottom: 20px; }

.sec-title {
    font-size: 10px;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: .08em;
    color: <?= $COR['brand_dark'] ?>;
    border-bottom: 0.5pt solid <?= $COR['brand_border'] ?>;
    padding-bottom: 6px;
    margin: 0 0 12px;
}

/* ── Cartão de identificação do cliente (executivo) ───────────────────── */
.client-id-card {
    width: 100%;
    border-collapse: collapse;
    background: <?= $COR['brand_dark'] ?>;
    border-radius: 8px;
    margin-bottom: 10px;
}
.client-id-card td { padding: 14px 18px; vertical-align: middle; }
.cic-name-col { width: 66%; }
.cic-doc-col  {
    width: 34%;
    text-align: right;
    border-left: 0.5pt solid rgba(255,255,255,0.25);
}
.cic-label, .cic-doc-label {
    display: block;
    font-size: 8px;
    text-transform: uppercase;
    letter-spacing: .12em;
    color: rgba(255,255,255,0.65);
    font-weight: bold;
    margin-bottom: 4px;
}
.cic-name { font-size: 16px; font-weight: 900; color: #FFFFFF; }
.cic-sigla {
    display: inline-block;
    font-size: 9px;
    font-weight: bold;
    color: <?= $COR['brand_dark'] ?>;
    background: #FFFFFF;
    border-radius: 8px;
    padding: 1px 7px;
    margin-left: 6px;
    vertical-align: middle;
}
.cic-doc-value {
    font-size: 13px;
    font-weight: bold;
    color: #FFFFFF;
    letter-spacing: .02em;
}

/* ── Blocos informativos do cliente (contato / vínculo / endereço) ────── */
.client-info-grid { width: 100%; border-collapse: collapse; }
.client-info-grid td {
    padding: 10px 14px;
    vertical-align: top;
    background: <?= $COR['row_alt'] ?>;
    border: 0.5pt solid <?= $COR['border'] ?>;
}
.cib-title {
    display: block;
    font-size: 8.5px;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: .08em;
    margin-bottom: 8px;
    color: <?= $COR['brand_dark'] ?>;
}
.cib-contato { border-left: 2.5pt solid <?= $COR['brand'] ?>; width: 50%; }
.cib-vinculo { border-left: 2.5pt solid <?= $COR['purple'] ?>; width: 50%; }
.cib-vinculo .cib-title { color: <?= $COR['purple'] ?>; }
.cib-endereco { border-left: 2.5pt solid <?= $COR['gray'] ?>; }
.cib-endereco .cib-title { color: <?= $COR['gray'] ?>; }

/* Sub-tabelas: sobrescrevem bordas/fundo herdados do .client-info-grid td */
.cib-rows { width: 100%; border-collapse: collapse; }
.cib-rows td { padding: 2px 0; border: none; background: transparent; }
.cib-k { width: 38%; font-size: 9px; color: <?= $COR['muted'] ?>; }
.cib-v { width: 62%; font-size: 10.5px; font-weight: bold; color: <?= $COR['text'] ?>; white-space: nowrap; }

.addr-cols { width: 100%; border-collapse: collapse; }
.addr-cols td { border: none; background: transparent; padding: 0 10px 0 0; vertical-align: top; }
.addr-cols .ac-label { display: block; font-size: 8.5px; color: <?= $COR['muted'] ?>; margin-bottom: 2px; }
.addr-cols .ac-value { font-size: 10.5px; font-weight: bold; color: <?= $COR['text'] ?>; }

/* ── Subsecção de orçamento (3.X) ───────────────────────────────────── */
.subsection-title {
    font-size: 10px;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: .05em;
    color: <?= $COR['brand_dark'] ?>;
    margin: 12px 0 6px;
}
.aux-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 10px;
    border: 0.5pt solid <?= $COR['border'] ?>;
}
.aux-table th {
    background: <?= $COR['brand'] ?>;
    color: #fff;
    font-size: 8.5px;
    text-transform: uppercase;
    letter-spacing: .05em;
    padding: 5px 6px;
    border: 0.5pt solid <?= $COR['brand'] ?>;
    text-align: left;
}
.aux-table td {
    padding: 5px 6px;
    border: 0.5pt solid <?= $COR['border'] ?>;
    vertical-align: top;
    color: <?= $COR['text'] ?>;
}
.aux-table tr:nth-child(even) td {
    background: <?= $COR['row_alt'] ?>;
}

/* ── Escopo ─────────────────────────────────────────────────────────── */
.escopo-box {
    font-size: 12px;
    line-height: 1.5;
    text-align: justify;
    color: <?= $COR['text'] ?>;
}
.escopo-box p {
    margin: 0 0 8px 0;
    text-indent: 1.25cm;
}

/* ── Tabela de itens ────────────────────────────────────────────────── */
.items-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 10px;
    margin-bottom: 12px;
}
.items-table th {
    background: <?= $COR['brand'] ?>;
    font-size: 9px;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: .05em;
    color: #ffffff;
    padding: 7px 8px;
    border-bottom: 0.5pt solid <?= $COR['brand_dark'] ?>;
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
.item-details { font-size: 10px; color: <?= $COR['muted'] ?>; margin-top: 1px; }

/* Linha de categoria dentro da tabela */
.cat-header-row { background: <?= $COR['row_alt'] ?>; }
.cat-header-row td {
    padding: 5px 8px;
    font-size: 9.5px;
    color: <?= $COR['muted'] ?>;
    border-bottom: 0.5pt solid <?= $COR['border'] ?>;
}

/* Linha de subtotal por categoria */
.cat-subtotal-row td {
    background: <?= $COR['row_alt'] ?>;
    font-size: 10px;
    font-weight: bold;
    padding: 5px 8px;
    border-bottom: 0.5pt solid <?= $COR['border'] ?>;
}
.cat-subtotal-row td.r { text-align: right; }

/* ── Resumo financeiro ───────────────────────────────────────────────── */
.fin-table { width: 100%; border-collapse: collapse; }
.fin-table td { padding: 5px 10px; font-size: 12px; border-bottom: 0.5pt solid <?= $COR['border'] ?>; }
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
    font-size: 10px;
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
.cond-label { font-size: 9px; text-transform: uppercase; letter-spacing: .05em; color: <?= $COR['muted'] ?>; font-weight: bold; display: block; margin-bottom: 3px; }
.cond-value { font-size: 12px; font-weight: bold; color: <?= $COR['text'] ?>; }

/* ── Observações ────────────────────────────────────────────────────── */
.obs-box {
    font-size: 12px;
    line-height: 1.8;
    color: #374151;
    background: <?= $COR['row_alt'] ?>;
    border-left: 2.5pt solid <?= $COR['brand_border'] ?>;
    padding: 8px 12px;
}

/* ── Assinaturas ────────────────────────────────────────────────────── */
.sig-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
.sig-table td { text-align: center; padding: 0 12px; vertical-align: bottom; }
.sig-line { border-top: 0.5pt solid <?= $COR['border'] ?>; margin-bottom: 6px; }
.sig-name { font-size: 9px; font-weight: bold; color: <?= $COR['text'] ?>; margin: 0; white-space: nowrap; }
.sig-doc  { font-size: 9px;  color: <?= $COR['muted'] ?>; margin: 2px 0 0; white-space: nowrap; }
.sig-role { font-size: 9px; text-transform: uppercase; letter-spacing: .05em; color: <?= $COR['brand'] ?>; font-weight: bold; margin: 3px 0 0; white-space: nowrap; }
.sig-cert { font-size: 9px; color: <?= $COR['muted'] ?>; margin: 2px 0 0; white-space: nowrap; }

/* ── Cronograma ── */
.crono-table-pdf { width: 100%; border-collapse: collapse; margin-top: 5px; }
.crono-table-pdf th, .crono-table-pdf td { border: 0.1pt solid #ddd; text-align: center; padding: 0px; }
.crono-table-pdf th { background: <?= $COR['brand'] ?>; font-size: 8.5px; color: #fff; height: 16px; line-height: 1; border: 0.5pt solid <?= $COR['brand'] ?>; }
.crono-table-pdf th.col-ativ-pdf { text-align: left; padding-left: 5px; font-size: 10px; color: #fff; }
.crono-table-pdf td.col-ativ-pdf { text-align: left; padding: 3px 5px; font-size: 10px; color: <?= $COR['text'] ?>; white-space: nowrap; line-height: 1.2; }
.crono-mark { width: 100%; height: 11px; display: block; border-radius: 1px; }
.crono-mark-esc { background-color: <?= $COR['brand'] ?>; }
.crono-mark-camp { background-color: <?= $COR['success'] ?>; }
.crono-legend-pdf { margin-top: 6px; font-size: 10px; color: <?= $COR['muted'] ?>; }
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

<?php if ($logoBase64): ?>
<div class="pdf-watermark">
    <img src="data:image/png;base64,<?= $logoBase64 ?>" style="width: 500px;">
</div>
<?php endif; ?>

<div class="pdf-header">
    <table>
        <tr>
            <td class="logo-td">
                <?php if ($logoBase64): ?>
                    <img src="data:image/png;base64,<?= $logoBase64 ?>" class="logo">
                <?php else: ?>
                    <div style="font-size:14px;font-weight:bold;color:<?= $COR['brand'] ?>"><?= $empresaRazao ?></div>
                <?php endif; ?>
            </td>
            <td class="text-td">
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
            <td class="header-meta meta-td">
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
            <td class="footer-left" style="width:60%;">
                ENVICORP ENGENHARIA E NEGOCIOS LTDA<br>
                CNPJ 49.787.357/0001-50<br>
                Avenida dos Oitis, 5941 | contato@envicorp.com.br
            </td>
            <td class="footer-right" style="width:40%;">
                <?php if ($versao): ?>
                    <div class="footer-version"><?= $versao ?></div>
                <?php endif; ?>
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

        <div style="margin-top: 50px;">
            <div class="cover-client-label">Interessado</div>
            <div class="cover-client-name"><?= $clienteNome ?></div>
        </div>

        <!-- Ficha técnica: 4 pills com os metadados do documento -->
        <div class="cover-meta-strip">
            <table class="cover-meta-grid">
                <tr>
                    <td class="cover-meta-pill">
                        <span class="cover-meta-label">Código</span>
                        <span class="cover-meta-value"><?= $codigo ?></span>
                    </td>
                    <td class="cover-meta-pill">
                        <span class="cover-meta-label">Emissão</span>
                        <span class="cover-meta-value"><?= $dataEmissao ?></span>
                    </td>
                    <td class="cover-meta-pill">
                        <span class="cover-meta-label">Validade</span>
                        <span class="cover-meta-value" style="color: <?= $COR['brand'] ?>"><?= $dataValidade ?></span>
                    </td>
                    <?php if ($responsavel): ?>
                    <td class="cover-meta-pill">
                        <span class="cover-meta-label">Elaboração</span>
                        <span class="cover-meta-value"><?= $responsavel ?></span>
                    </td>
                    <?php endif; ?>
        </tr>
    </table>
</div>
    </div>

</div>

<!-- ══════════════════════════════════════════════════════
     1. APRESENTAÇÃO / OBJETO
══════════════════════════════════════════════════════ -->
<?php if ($escopo && $escopo !== 'N/A'): ?>
<div class="section">
    <p class="sec-title">1- APRESENTAÇÃO / OBJETO</p>
    <div class="escopo-box"><?php
        $paragrafos = preg_split('/\n\s*\n/', trim($escopo));
        foreach ($paragrafos as $paragrafo):
            $paragrafo = nl2br(trim($paragrafo));
            if ($paragrafo !== ''):
    ?><p style="text-indent: 1.25cm;"><?= $paragrafo ?></p><?php
            endif;
        endforeach;
    ?></div>
</div>
<div style="page-break-after: always;"></div>
<?php endif; ?>


<!-- ══════════════════════════════════════════════════════
     2. DADOS DO CLIENTE
══════════════════════════════════════════════════════ -->
<div class="section">
    <p class="sec-title">2- DADOS DO CLIENTE</p>

    <!-- Cartão de identificação principal -->
    <table class="client-id-card">
        <tr>
            <td class="cic-name-col">
                <span class="cic-label">Interessado / Cliente</span>
                <span class="cic-name">
                    <?= $clienteNome ?>
                    <?php if ($clienteSigla): ?><span class="cic-sigla"><?= $clienteSigla ?></span><?php endif; ?>
                </span>
            </td>
            <td class="cic-doc-col">
                <span class="cic-doc-label">CNPJ / CPF</span>
                <span class="cic-doc-value"><?= $clienteDoc ?></span>
            </td>
        </tr>
    </table>

    <!-- Blocos: Contato / Vínculo do Projeto / Endereço -->
    <table class="client-info-grid">
        <tr>
            <td class="cib-contato">
                <span class="cib-title">Contato</span>
                <table class="cib-rows">
                    <tr><td class="cib-k">Representante</td><td class="cib-v"><?= $representante ?: '—' ?></td></tr>
                    <tr><td class="cib-k">E-mail</td><td class="cib-v"><?= $emailCliente ?: '—' ?></td></tr>
                    <tr><td class="cib-k">Telefone</td><td class="cib-v"><?= $telefone ?: '—' ?></td></tr>
                </table>
            </td>
            <td class="cib-vinculo">
                <span class="cib-title">Vínculo do Projeto</span>
                <table class="cib-rows">
                    <tr><td class="cib-k">Município (Projeto)</td><td class="cib-v"><?= $municipio ?: '—' ?></td></tr>
                    <tr><td class="cib-k">Área</td><td class="cib-v"><?= $area ?: '—' ?></td></tr>
                    <tr><td class="cib-k">Projeto</td><td class="cib-v"><?= $projetoNome ?: '—' ?></td></tr>
                </table>
            </td>
        </tr>
        <tr>
            <td colspan="2" class="cib-endereco">
                <span class="cib-title">Endereço</span>
                <table class="addr-cols">
                    <tr>
                        <td style="width:46%">
                            <span class="ac-label">Logradouro</span>
                            <span class="ac-value"><?= $clienteLogradouro ?: '—' ?><?php if ($clienteNumero): ?>, <?= $clienteNumero ?><?php endif; ?><?php if ($clienteComplemento): ?> - <?= $clienteComplemento ?><?php endif; ?></span>
                        </td>
                        <td style="width:24%">
                            <span class="ac-label">Bairro</span>
                            <span class="ac-value"><?= $clienteBairro ?: '—' ?></span>
                        </td>
                        <td style="width:30%;padding-right:0">
                            <span class="ac-label">Município / UF</span>
                            <span class="ac-value"><?= $clienteMunicipio ?: '—' ?><?php if ($clienteUf): ?> / <?= $clienteUf ?><?php endif; ?></span>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</div>

<?php if (!empty($contextualizacao) || !empty($p['contextualizacao_texto_intro'])): ?>
<div class="section">
    <p class="subsection-title">2.1 Contextualização</p>
    <?php if (!empty($p['contextualizacao_texto_intro'])): ?>
        <div style="font-size:12px;color:#374151;margin:6px 0 8px;line-height:1.5;text-align:justify;"><?php
            $paragrafos = preg_split('/\n\s*\n/', trim($p['contextualizacao_texto_intro']));
            foreach ($paragrafos as $paragrafo):
                $paragrafo = nl2br(trim($paragrafo));
                if ($paragrafo !== ''):
        ?><p style="margin:0 0 8px 0;text-indent:1.25cm;"><?= $paragrafo ?></p><?php
                endif;
            endforeach;
        ?></div>
    <?php endif; ?>
    <?php if (!empty($contextualizacao) && !$ocultarTabelaContexto): ?>
    <table class="aux-table">
        <thead>
            <tr>
                <th style="width:22%">Empreendedor</th>
                <th style="width:14%">Faixa de domínio</th>
                <th style="width:14%">KM</th>
                <th style="width:28%">Município / Estado</th>
                <th style="width:10%">Área (ha)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($contextualizacao as $ctx): ?>
                <tr>
                    <td><?= htmlspecialchars($ctx['empreendedor'] ?? '') ?></td>
                    <td><?= htmlspecialchars($ctx['faixa'] ?? '') ?></td>
                    <td><?= htmlspecialchars($ctx['km'] ?? '') ?></td>
                    <td><?= htmlspecialchars($ctx['municipio'] ?? '') ?></td>
                    <td style="text-align:right"><?= htmlspecialchars($ctx['area'] ?? '') ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php if (!empty($equipe)): ?>
<div class="section">
    <p class="subsection-title">2.2 Equipe do Projeto</p>
    <?php if (!empty($p['equipe_texto_intro'])): ?>
        <div style="font-size:12px;color:#374151;margin:6px 0 8px;line-height:1.5;text-align:justify;"><?php
            $paragrafos = preg_split('/\n\s*\n/', trim($p['equipe_texto_intro']));
            foreach ($paragrafos as $paragrafo):
                $paragrafo = nl2br(trim($paragrafo));
                if ($paragrafo !== ''):
        ?><p style="margin:0 0 8px 0;text-indent:1.25cm;"><?= $paragrafo ?></p><?php
                endif;
            endforeach;
        ?></div>
    <?php endif; ?>
    <table class="aux-table">
        <thead>
            <tr>
                <th style="width:24%">Profissional</th>
                <th style="width:18%">Campo de atuação</th>
                <th style="width:58%">Função</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($equipe as $eq): ?>
                <?php if (!empty($eq['profissional']) || !empty($eq['funcao'])): ?>
                <tr>
                    <td><?= htmlspecialchars($eq['profissional'] ?? '') ?></td>
                    <td><?= htmlspecialchars($eq['campo'] ?? '') ?></td>
                    <td style="font-weight:normal"><?= nl2br(htmlspecialchars($eq['funcao'] ?? '')) ?></td>
                </tr>
                <?php endif; ?>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<!-- ══════════════════════════════════════════════════════
     2.3 CRONOGRAMA DE EXECUÇÃO
══════════════════════════════════════════════════════ -->
<!--CRONOGRAMA_START-->
<?php 
$hasCronoMarks = false;
if ($crono && !empty($crono['state'])) {
    foreach ($crono['state'] as $st) {
        if ($st > 0) { $hasCronoMarks = true; break; }
    }
}
if ($crono && !empty($crono['activities']) && $hasCronoMarks): ?>
    <div class="section">
        <p class="sec-title">2.3- CRONOGRAMA ESTIMADO DE EXECUÇÃO</p>
            <?php if (!empty($p['cronograma_texto_intro'])): ?>
                <div style="font-size:12px;color:#374151;margin:6px 0 8px;line-height:1.5;text-align:justify;"><?php
                    $paragrafos = preg_split('/\n\s*\n/', trim($p['cronograma_texto_intro']));
                    foreach ($paragrafos as $paragrafo):
                        $paragrafo = nl2br(trim($paragrafo));
                        if ($paragrafo !== ''):
                ?><p style="margin:0 0 8px 0;text-indent:1.25cm;"><?= $paragrafo ?></p><?php
                        endif;
                    endforeach;
                ?></div>
            <?php endif; ?>
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
                            <th style="width:20px"><?= $label ?><br><span style="font-size:6px;font-weight:normal"><?= $sub ?></span></th>
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
                <?php if (!empty($p['cronograma_texto_footer'])): ?>
                <div class="crono-legend-item-pdf" style="margin-left: 20px;"><?= nl2br(htmlspecialchars($p['cronograma_texto_footer'])) ?></div>
                <?php endif; ?>
            </div>
    </div>
<?php endif; ?>
<!--CRONOGRAMA_END-->

<!-- ══════════════════════════════════════════════════════
     3. ORÇAMENTO (itens agrupados por categoria)
══════════════════════════════════════════════════════ -->
<?php
$currentCategory = null;
$categorySubtotal = 0;
$subSectionCounter = 0;
$hasItemsToDisplay = !empty($p['itens']) && is_array($p['itens']);

// ── Helper: fecha tabela aberta e imprime subtotal ──────────────────────
function fecharTabelaAberta(bool &$tabelaAberta, ?string $currentCategory, float $categorySubtotal, float $categoryQtde, array $COR): void {
    if (!$tabelaAberta) return;
    ?>
        <tr class="cat-subtotal-row" style="font-weight:700;">
            <td colspan="2" style="padding:4px 8px;font-size:8.5px;text-transform:uppercase;letter-spacing:.04em;text-align:right;background:<?= $COR['brand'] ?>;color:#fff;">
                Subtotal <?= $categoryQtde > 0 ? '(Qtd: ' . number_format($categoryQtde, 0, ',', '.') . ')' : '' ?>
            </td>
            <td colspan="2" style="background:<?= $COR['brand'] ?>"></td>
            <td colspan="2" class="r" style="padding:4px 8px;font-size:9px;background:<?= $COR['brand'] ?>;color:#fff;"><?= \App\Helpers\ReportHelper::formatCurrency($categorySubtotal) ?></td>
        </tr>
        </tbody>
    </table>
    <?php
    $tabelaAberta = false;
}
?>

<?php if ($hasItemsToDisplay): ?>
<div class="section">
    <p class="sec-title">3- ORÇAMENTO</p>

    <?php
    $tabelaAberta    = false;
    $currentCategory = null;
    $categorySubtotal = 0;
    $categoryQtde    = 0;

    foreach ($p['itens'] as $idx => $item):
        $cat         = $item['categoria'] ?? 'Outros';
        $isTitulo    = ($cat === 'Titulo');
        $isSubtitulo = ($cat === 'Subtitulo');
        $isLegend    = ($cat === 'Legenda');
        $isEspecial  = $isTitulo || $isSubtitulo || $isLegend;

        // ── TÍTULO DE SEÇÃO — fecha tabela anterior se aberta ──
        if ($isTitulo):
            if ($tabelaAberta) fecharTabelaAberta($tabelaAberta, $currentCategory, $categorySubtotal, $categoryQtde, $COR);
            $currentCategory  = null;
            $categorySubtotal = 0;
            $categoryQtde    = 0;
            $subSectionCounter++;
    ?>
        <p class="subsection-title" style="margin-top:14px;font-size:10px;font-weight:800;color:<?= $COR['brand_dark'] ?>;text-transform:none;letter-spacing:.01em;">
            <span style="font-size:11px;color:<?= $COR['brand'] ?>;margin-right:5px;">3.<?= $subSectionCounter ?>.</span><?= htmlspecialchars($item['descricao'] ?? '') ?>
        </p>

    <?php
            continue;
        endif;

        // ── TEXTO DESCRITIVO — parágrafo justificado ──
        if ($isSubtitulo):
            if ($tabelaAberta) fecharTabelaAberta($tabelaAberta, $currentCategory, $categorySubtotal, $categoryQtde, $COR);
            $currentCategory  = null;
            $categorySubtotal = 0;
            $categoryQtde    = 0;
    ?>
            <div style="font-size:12px;color:#374151;margin:4px 0 8px;line-height:1.7;text-align:justify;padding:0 4px;"><?php
                $paragrafos = preg_split('/\n\s*\n/', trim($item['descricao'] ?? ''));
                foreach ($paragrafos as $paragrafo):
                    $paragrafo = nl2br(trim($paragrafo));
                    if ($paragrafo !== ''):
            ?><p style="margin:0 0 8px 0;text-indent:1.25cm;"><?= $paragrafo ?></p><?php
                    endif;
                endforeach;
            ?></div>
    <?php
            continue;
        endif;

        // ── LEGENDA LEGADA (compatibilidade) ──
        if ($isLegend):
            if ($tabelaAberta) fecharTabelaAberta($tabelaAberta, $currentCategory, $categorySubtotal, $categoryQtde, $COR);
            $currentCategory  = null;
            $categorySubtotal = 0;
            $categoryQtde    = 0;
            $subSectionCounter++;
    ?>
        <table style="width:100%;border-collapse:collapse;">
            <tr style="background-color:<?= $COR['row_alt'] ?>;">
                <td colspan="5" style="padding:10px 12px;border-bottom:0.5pt solid <?= $COR['border'] ?>;">
                    <div style="font-size:10px;font-weight:800;color:<?= $COR['brand_dark'] ?>;text-transform:uppercase;letter-spacing:.05em;">
                        <span style="font-size:12px;margin-right:5px;color:<?= $COR['brand'] ?>;">3.<?= $subSectionCounter ?></span>
                        <?= htmlspecialchars($item['descricao'] ?? '') ?>
                    </div>
                </td>
            </tr>
        </table>
    <?php
            continue;
        endif;

        // ── ITEM NORMAL ──
        // Abre tabela se ainda não estiver aberta
        if (!$tabelaAberta):
    ?>
        <table class="items-table" style="margin-bottom:0">
            <thead>
                <tr>
                    <th style="width:26%;background:<?= $COR['brand'] ?>;color:#fff;">Profissional / Serviço</th>
                    <th style="width:7%;background:<?= $COR['brand'] ?>;color:#fff;">Un.</th>
                    <th style="width:7%;background:<?= $COR['brand'] ?>;color:#fff;" class="r">Qtd.</th>
                    <th style="width:13%;background:<?= $COR['brand'] ?>;color:#fff;" class="r">Preço Un.</th>
                    <th style="width:8%;background:<?= $COR['brand'] ?>;color:#fff;" class="r">Desc.%</th>
                    <th style="width:13%;background:<?= $COR['brand'] ?>;color:#fff;" class="r">Total</th>
                </tr>
            </thead>
            <tbody>
    <?php
            $tabelaAberta    = true;
            $currentCategory = null;
            $categorySubtotal = 0;
            $categoryQtde    = 0;
        endif;

        // Se a categoria mudou, imprime badge de categoria
        if ($cat !== $currentCategory):
            $cor = $catCores[$cat] ?? [$COR['gray_light'], $COR['gray_border'], $COR['gray']];
    ?>
            <tr class="cat-header-row">
                <td colspan="6">
                    <?= pdfBadge($cat, $cor[0], $cor[1], $cor[2]) ?>
                </td>
            </tr>
    <?php
            $currentCategory = $cat;
        endif;

        $qty      = (float)($item['quantidade']    ?? 1);
        $vunit    = (float)($item['valor_unit']     ?? $item['valor_unitario'] ?? 0);
        $descItem = (float)($item['desconto_item']  ?? 0);
        $totalItem = isset($item['total_item']) && $item['total_item'] > 0
            ? (float)$item['total_item']
            : $qty * $vunit * (1 - $descItem / 100);
        $descricao = htmlspecialchars($item['descricao'] ?? $item['nome'] ?? '');
        $detalhes  = htmlspecialchars($item['detalhes']  ?? '');
        $unidade   = htmlspecialchars($item['unidade']   ?? 'un');
        $categorySubtotal += $totalItem;
        $categoryQtde    += $qty;
    ?>
            <tr>
                <td>
                    <?= $descricao ?>
                    <?php if ($detalhes): ?>
                        <span class="item-details" style="display:block"><?= $detalhes ?></span>
                    <?php endif; ?>
                </td>
                <td><?= $unidade ?></td>
                <td class="r"><?= number_format($qty, $qty == intval($qty) ? 0 : 3, ',', '.') ?></td>
                <td class="r"><?= \App\Helpers\ReportHelper::formatCurrency($vunit) ?></td>
                <td class="r" style="font-size:9px"><?= $descItem > 0 ? number_format($descItem, 2, ',', '.') . '%' : '—' ?></td>
                <td class="r" style="color:<?= $COR['brand'] ?>;font-weight:700;"><?= \App\Helpers\ReportHelper::formatCurrency($totalItem) ?></td>
            </tr>
    <?php
    endforeach;

    // Fecha a última tabela aberta
    if ($tabelaAberta) fecharTabelaAberta($tabelaAberta, $currentCategory, $categorySubtotal, $categoryQtde, $COR);
    ?>

</div>
<?php else: ?>
<div class="section">
    <p class="sec-title">3- ORÇAMENTO</p>
    <p style="font-size:10px;color:<?= $COR['muted'] ?>;padding:8px 0;">Nenhum item detalhado nesta proposta.</p>
</div>
<?php endif; ?>


<!-- ══════════════════════════════════════════════════════
     4. RESUMO FINANCEIRO
══════════════════════════════════════════════════════ -->
<div class="section">
    <p class="sec-title">4- RESUMO FINANCEIRO</p>
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

        <!-- Subtotal bruto e descontos por item (só exibe se houver descontos) -->
        <?php if ($grossSubtotal > 0 && hasDiscountsToShow($totalItemDiscount, $descontoValor)): ?>
        <tr>
            <td class="fl">Subtotal (bruto)</td>
            <td class="fv"><?= ReportHelper::formatCurrency($grossSubtotal) ?></td>
        </tr>
        <?php if ($totalItemDiscount > 0): ?>
        <tr>
            <td class="fl" style="font-size:9px;padding-left:12px;color:<?= $COR['danger'] ?>">Desconto nos Itens</td>
            <td class="fv" style="color:<?= $COR['danger'] ?>">- <?= ReportHelper::formatCurrency($totalItemDiscount) ?></td>
        </tr>
        <?php endif; ?>
        <tr style="border-bottom:0.5pt dashed <?= $COR['border'] ?>">
            <td class="fl" style="font-weight:600">Subtotal (líquido)</td>
            <td class="fv" style="font-weight:600"><?= ReportHelper::formatCurrency($subtotal) ?></td>
        </tr>
        <tr><td colspan="2" style="padding:0"></td></tr>
        <?php endif; ?>

        <!-- Desconto Global -->
        <?php if ($descontoValor > 0): ?>
        <tr>
            <td class="fl">
                Desconto Global
                <?php if ($descontoTipo === 'percentual' && $descontoPerc > 0): ?>
                    <span style="font-size:9px">(<?= number_format($descontoPerc, 2, ',', '.') ?>%)</span>
                <?php endif; ?>
            </td>
            <td class="fv text-danger">- <?= ReportHelper::formatCurrency($descontoValor) ?></td>
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
     PÁGINA FINAL — CONDIÇÕES · ASSINATURAS · ACEITE DIGITAL
     (mantidos juntos em uma única página)
══════════════════════════════════════════════════════ -->
<div style="page-break-before:always; position:relative; min-height:24.3cm;">

<!-- 5. CONDIÇÕES COMERCIAIS -->
<div class="section">
    <p class="sec-title">5- CONDIÇÕES COMERCIAIS</p>
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
                    <?php if ($pixChave): ?>
                        <br><span style="font-size:9px; font-weight:normal; color:<?= $COR['muted'] ?>">Chave (<?= $pixTipoChave ?>): <?= $pixChave ?></span>
                    <?php endif; ?>
                    <?php if ($dadosBancarios): ?>
                        <br><span style="font-size:9px; font-weight:normal; color:<?= $COR['muted'] ?>"><?= nl2br($dadosBancarios) ?></span>
                    <?php endif; ?>
                </span>
            </td>
        </tr>
        <tr>
            <td>
                <span class="cond-label">Prazo de Início</span>
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

<?php if ($observacoes && $observacoes !== 'N/A'): ?>
<!-- 6. OBSERVAÇÕES -->
<div class="section">
    <p class="sec-title">6- OBSERVAÇÕES</p>
    <div class="obs-box"><?= $observacoes ?></div>
</div>
<?php endif; ?>

<!-- 7. ASSINATURAS (fixada no meio vertical da página, fora do fluxo normal) -->
<div class="section" style="position:absolute; top:10.4cm; left:0; right:0;">
    <p class="sec-title">Assinaturas</p>
    <table class="sig-table">
        <tr>
            <!-- Elaborador como Responsável Técnico (se ativado) — sempre primeiro -->
            <?php if ($assinaturaElaboradorResp && $responsavel): ?>
            <td>
                <?php if ($assinaturaElaboradorTipo === 'imagem'): ?>
                    <?php if ($elaboradorSigImg): ?>
                        <img src="data:image/png;base64,<?= $elaboradorSigImg ?>" style="height:38px; max-width:180px; object-fit:contain; margin-bottom:4px;">
                    <?php else: ?>
                        <div style="height:40px"></div>
                    <?php endif; ?>
                <?php elseif ($assinaturaElaboradorTipo === 'certificado' && $assinaturaElaboradorCertNome): ?>
                    <div style="height:40px; display:flex; flex-direction:column; align-items:center; justify-content:center; margin-bottom:4px;">
                        <span style="font-size:9px; color:<?= $COR['brand_dark'] ?>; font-weight:bold; letter-spacing:0.08em;">ASSINADO DIGITALMENTE</span>
                        <span style="font-size:8px; color:<?= $COR['muted'] ?>;">Certificado ICP-Brasil</span>
                    </div>
                <?php else: ?>
                    <div style="height:40px"></div>
                <?php endif; ?>
                <div class="sig-line"></div>
                <p class="sig-name"><?= $responsavel ?></p>
                <p class="sig-doc">Responsável pela Elaboração</p>

                <?php if ($assinaturaElaboradorTipo === 'certificado' && $assinaturaElaboradorCertNome): ?>
                    <p class="sig-cert">
                        Certificado: <?= $assinaturaElaboradorCertNome ?> <?= $assinaturaElaboradorCertCpf ? '· CPF: '.ReportHelper::formatCpfCnpj($assinaturaElaboradorCertCpf) : '' ?>
                    </p>
                <?php endif; ?>
            </td>
            <?php endif; ?>
            <!-- Contratada (meio) -->
            <td>
                <?php if ($assinaturaTipo === 'imagem'): ?>
                    <?php if ($sigImgBase64): ?>
                        <img src="data:image/png;base64,<?= $sigImgBase64 ?>" style="height:38px; max-width:180px; object-fit:contain; margin-bottom:4px;">
                    <?php else: ?>
                        <div style="height:40px"></div>
                    <?php endif; ?>
                <?php elseif ($assinaturaTipo === 'certificado' && $assinaturaCertNome): ?>
                    <div style="height:40px; display:flex; flex-direction:column; align-items:center; justify-content:center; margin-bottom:4px;">
                        <span style="font-size:9px; color:<?= $COR['brand_dark'] ?>; font-weight:bold; letter-spacing:0.08em;">ASSINADO DIGITALMENTE</span>
                        <span style="font-size:8px; color:<?= $COR['muted'] ?>;">Certificado ICP-Brasil</span>
                    </div>
                <?php else: ?>
                    <div style="height:40px"></div>
                <?php endif; ?>
                <div class="sig-line"></div>
                <p class="sig-name"><?= $empresaRazao ?></p>
                <p class="sig-doc">CNPJ: <?= $empresaCnpj ?></p>
                <p class="sig-role">Contratada</p>
                <?php if ($assinaturaTipo === 'certificado' && $assinaturaCertNome): ?>
                    <p class="sig-cert">
                        Certificado: <?= $assinaturaCertNome ?> <?= $assinaturaCertCpf ? '· CPF: '.ReportHelper::formatCpfCnpj($assinaturaCertCpf) : '' ?>
                    </p>
                <?php endif; ?>
            </td>
            <!-- Contratante (último) -->
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


<!-- ══════════════════════════════════════════════════════
     INSTRUÇÕES DE ACEITE DIGITAL (grudado ao fundo da página,
     imediatamente acima do rodapé fixo — fora do fluxo normal)
══════════════════════════════════════════════════════ -->
<div style="position:absolute; bottom:0; left:0; right:0; padding: 6px 12px; border-top: 0.5pt solid <?= $COR['border'] ?>; font-size: 7px; line-height: 1.3; color: <?= $COR['muted'] ?>;">
    <table style="width:100%;border-collapse:collapse;">
        <tr>
            <td style="width:70%;vertical-align:top;padding-right:10px;">
                <p style="font-size: 8px; font-weight: bold; color: <?= $COR['brand_dark'] ?>; margin: 0 0 4px; text-transform: uppercase; letter-spacing: 0.08em;">Instruções de Aceite Digital</p>
                <p style="margin: 0 0 4px; font-size: 7px;">Esta proposta utiliza tecnologia de <strong>Assinatura Eletrônica</strong> com total segurança e validade jurídica:</p>
                <ol style="margin: 0 0 4px 14px; padding: 0;">
                    <li style="margin-bottom: 2px;"><strong>Acesse</strong> o QR Code ou link seguro enviado por nossos consultores.</li>
                    <li style="margin-bottom: 2px;"><strong>Revise</strong> escopo, valores e prazos no portal de aprovação.</li>
                    <li style="margin-bottom: 2px;"><strong>Formalize</strong> informando seu nome completo para nossa equipe.</li>
                </ol>
                <p style="font-size: 6.5px; color: <?= $COR['brand_dark'] ?>; margin: 0; line-height: 1.3;">
                    <strong>VALIDADE JURÍDICA:</strong> Amparado pela MP nº 2.200-2/2001 e Lei nº 14.063/2020. Registramos IP, data, hora e geolocalização.
                </p>
            </td>
            <td style="width:30%;vertical-align:bottom;text-align:right;">
                <?php if (!empty($qr_code_base64)): ?>
                <div>
                    <img src="data:image/png;base64,<?= $qr_code_base64 ?>" style="width:50px; height:50px;">
                    <p style="font-size:6px; color:<?= $COR['muted'] ?>; margin:2px 0 0;">Aponte a câmera para aprovar</p>
                </div>
                <?php endif; ?>
            </td>
        </tr>
    </table>
</div>

</div><!-- /final-page-wrapper -->

</body>
</html>