<!DOCTYPE html>
<?php
use App\Helpers\ReportHelper;
use App\Core\SessionManager;

$status    = $proposta['status'] ?? 'Enviada';
$isPending = in_array($status, ['Enviada', 'Rascunho']);

$statusMap = [
    'Enviada'   => ['label' => 'Aguardando Aprovação', 'dot' => '#f59e0b', 'bg' => '#fef3c7', 'color' => '#92400e', 'pulse' => true],
    'pendente'  => ['label' => 'Aguardando Aprovação', 'dot' => '#f59e0b', 'bg' => '#fef3c7', 'color' => '#92400e', 'pulse' => true],
    'Aprovada'  => ['label' => 'Aprovada',             'dot' => '#10b981', 'bg' => '#d1fae5', 'color' => '#065f46', 'pulse' => false],
    'Rejeitada' => ['label' => 'Rejeitada',            'dot' => '#ef4444', 'bg' => '#fee2e2', 'color' => '#991b1b', 'pulse' => false],
    'Cancelada' => ['label' => 'Cancelada',            'dot' => '#94a3b8', 'bg' => '#f1f5f9', 'color' => '#475569', 'pulse' => false],
    'Rascunho'  => ['label' => 'Rascunho',             'dot' => '#94a3b8', 'bg' => '#f1f5f9', 'color' => '#475569', 'pulse' => false],
];
$sc = $statusMap[$status] ?? $statusMap['Enviada'];

/* ── Mapeamento de campos (espelha proposta_pdf.php) ── */
$p = $proposta ?? [];

$titulo       = $p['titulo']          ?? $p['nome_proposta']  ?? 'Proposta Técnica Orçamentária';
$codigo       = $p['codigo']          ?? $p['numero_proposta'] ?? str_pad($p['id'] ?? 0, 4, '0', STR_PAD_LEFT);
$versao       = $p['versao_documento'] ?? '';
$dataEmissao  = ReportHelper::formatDate($p['data_proposta']  ?? date('Y-m-d'));
$validadeDias = (int)($p['validade_proposta'] ?? $p['validade_dias'] ?? $p['validade'] ?? 30);
$responsavel  = $p['responsavel_nome'] ?? '';
$municipio    = $p['municipio'] ?? '';
$area         = $p['area'] ?? '';
$projetoNome  = $p['projeto_nome'] ?? '';
$contratoNum  = $p['contrato_numero'] ?? $p['contrato_id'] ?? '';
$escopo       = $p['descricao_geral'] ?? $p['escopo'] ?? $p['descricao'] ?? null;
$observacoes  = $p['observacoes'] ?? $p['condicoes'] ?? null;

$clienteNome   = $p['cliente_nome']   ?? '—';
$clienteSigla  = $p['cliente_sigla']  ?? '';
$representante = $p['representante']  ?? '';
$emailCliente  = $p['email_cliente']  ?? $p['cliente_email'] ?? '';
$telefone      = $p['cliente_telefone'] ?? '';
$clienteDoc    = ReportHelper::formatCpfCnpj($p['cliente_documento'] ?? '');
$clienteEndereco = trim(implode(', ', array_filter([
    $p['cliente_logradouro'] ?? $p['cliente_endereco'] ?? '',
    $p['cliente_numero'] ?? '',
    $p['cliente_complemento'] ?? '',
    $p['cliente_bairro'] ?? '',
    ($p['cliente_municipio'] ?? '') . ($p['cliente_uf'] ? '/'.$p['cliente_uf'] : ''),
])));

$formaPagamento   = $p['forma_pagamento']   ?? '';
$condicaoPagamento = $p['condicao_pagamento'] ?? '';
$prazoExecucao    = $p['prazo_execucao']    ?? '';
$garantias        = html_entity_decode($p['garantias'] ?? '');
$pixChave         = $p['pix_chave'] ?? '';
$pixTipoChave     = $p['pix_tipo_chave'] ?? '';
$dadosBancarios   = $p['dados_bancarios'] ?? '';

/* Itens — suporte novo modelo (itens[]) + legado (servicos_json) */
$itens = [];
if (!empty($p['itens']) && is_array($p['itens'])) {
    $itens = array_filter($p['itens'], fn($i) => !in_array($i['categoria'] ?? '', ['Legenda', 'Titulo', 'Subtitulo']) && (!empty($i['descricao']) || !empty($i['nome'])));
} elseif (!empty($p['servicos_json'])) {
    $raw = json_decode($p['servicos_json'], true) ?: [];
    foreach ($raw as $item) {
        $item['descricao'] = $item['descricao'] ?? $item['nome'] ?? '';
        $item['valor_unit'] = $item['valor_unitario'] ?? $item['valor_unit'] ?? 0;
        $item['desconto_item'] = $item['desconto'] ?? 0;
        $itens[] = $item;
    }
}

/* Subtotais por categoria */
$catCores = [
    'Planejamento / Coordenação'   => ['#dbeafe','#93c5fd','#1d4ed8'],
    'Serviços de Campo'            => ['#EAF3DE','#C0DD97','#3B6D11'],
    'Custos Reembolsáveis'         => ['#FAEEDA','#FAC775','#633806'],
    'Elaboração de Peças Técnicas' => ['#EEEDFE','#CECBF6','#3C3489'],
    'Outros'                       => ['#F1EFE8','#D3D1C7','#444441'],
];
$subtotaisCat = [];
foreach ($itens as $item) {
    $cat   = $item['categoria'] ?? 'Outros';
    $qty   = (float)($item['quantidade']  ?? 1);
    $vunit = (float)($item['valor_unit']  ?? $item['valor_unitario'] ?? 0);
    $desc  = (float)($item['desconto_item'] ?? $item['desconto'] ?? 0);
    $subtotaisCat[$cat] = ($subtotaisCat[$cat] ?? 0) + ($qty * $vunit * (1 - $desc / 100));
}

$subTotal    = (float)($p['subtotal']       ?? array_sum($subtotaisCat));
$descValor   = (float)($p['descontos_valor'] ?? 0);
$descPerc    = (float)($p['desconto_valor']  ?? 0);
$descTipo    = $p['desconto_tipo'] ?? 'percentual';
$taxPerc     = (float)($p['impostos_perc']  ?? 0);
$taxValor    = (float)($p['impostos_valor'] ?? 0);
$totalFinal  = (float)($p['total_final']    ?? $p['total'] ?? $p['valor_total'] ?? 0);

if ($descValor == 0 && $descPerc > 0)
    $descValor = $descTipo === 'percentual' ? $subTotal * ($descPerc / 100) : $descPerc;
if ($taxValor == 0 && $taxPerc > 0)
    $taxValor = ($subTotal - $descValor) * ($taxPerc / 100);
if ($totalFinal == 0)
    $totalFinal = $subTotal - $descValor + $taxValor;

$crono = !empty($p['cronograma_data'])
    ? (is_array($p['cronograma_data']) ? $p['cronograma_data'] : json_decode($p['cronograma_data'], true))
    : null;
$cronoTextoIntro = is_array($crono) ? ($crono['texto_intro'] ?? '') : '';
$cronoTextoFooter = is_array($crono) ? ($crono['texto_footer'] ?? '') : '';
$cronoDuracao = '';
if ($crono && isset($crono['totalPeriods'])) {
    $n    = (int)($crono['totalPeriods'] ?? 0);
    $unit = match($crono['mode'] ?? 'dias') { 'semanas' => 'semanas', 'meses' => 'meses', default => 'dias' };
    $cronoDuracao = "{$n} {$unit}";
}

$equipe = !empty($p['equipe']) && is_array($p['equipe']) ? $p['equipe'] : [];
$equipeTextoIntro = $p['equipe_texto_intro'] ?? '';
$contextualizacao = !empty($p['contextualizacao']) && is_array($p['contextualizacao']) ? $p['contextualizacao'] : [];
$ocultarTabelaContexto = !empty($p['contextualizacao_ocultar_vazias']);
$contextualizacaoTextoIntro = $p['contextualizacao_texto_intro'] ?? '';
if (empty($municipio) && !empty($contextualizacao)) $municipio = reset($contextualizacao)['municipio'] ?? '';
if (empty($area)      && !empty($contextualizacao)) $area      = reset($contextualizacao)['area'] ?? '';

$numProposta = $p['numero_proposta'] ?? $p['id'] ?? '';
$validadeToken = ReportHelper::formatDate($p['token_validade'] ?? '');
$logoAlt  = $empresa['razao_social'] ?? 'EnviCorp';
?>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo htmlspecialchars($pageTitle ?? $titulo); ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
    --brand:#2563eb;
    --brand-dark:#1d4ed8;
    --brand-light:#dbeafe;
    --brand-border:#93c5fd;
    --success:#3B6D11;
    --success-light:#EAF3DE;
    --success-border:#C0DD97;
    --amber:#633806;
    --amber-light:#FAEEDA;
    --amber-border:#FAC775;
    --purple:#3C3489;
    --purple-light:#EEEDFE;
    --purple-border:#CECBF6;
    --gray:#444441;
    --gray-light:#F1EFE8;
    --gray-border:#D3D1C7;
    --danger:#A32D2D;
    --surface:#FFFFFF;
    --surface-2:#F9FAFB;
    --surface-3:#F3F4F6;
    --border:#E5E7EB;
    --text:#111827;
    --muted:#6B7280;
    --text-3:#6B7280;
    --radius:8px;
    --radius-lg:12px;
    --shadow:0 1px 3px rgba(0,0,0,.08);
    --shadow-lg:0 8px 30px rgba(0,0,0,.10);
}
.dark{
    --surface:#1F2937;
    --surface-2:#111827;
    --surface-3:#0F172A;
    --border:#374151;
    --text:#F3F4F6;
    --muted:#9CA3AF;
    --text-3:#9CA3AF;
    --brand-light:#1E3A5F;
    --brand-dark:#93C5FD;
    --shadow:0 1px 3px rgba(0,0,0,.3);
    --shadow-lg:0 8px 30px rgba(0,0,0,.4);
}
body{
    font-family:Inter,system-ui,-apple-system,sans-serif;
    background:var(--surface-2);
    color:var(--text);
    font-size:14px;
    line-height:1.6;
    min-height:100vh;
    padding:0 0 140px;
    -webkit-font-smoothing:antialiased;
}

/* Marca d'água */
.watermark{
    position:fixed;
    top:20%;
    left:10%;
    width:80%;
    z-index:-1;
    opacity:.04;
    text-align:center;
    pointer-events:none;
}
.watermark img{width:500px;max-width:80vw}

/* Cabeçalho fixo */
.pdf-header{
    position:fixed;
    top:0;left:0;right:0;
    height:68px;
    background:var(--surface);
    border-bottom:3px solid var(--brand);
    padding:0 24px;
    z-index:50;
    display:flex;
    align-items:center;
    justify-content:space-between;
    box-shadow:0 1px 4px rgba(0,0,0,.06);
}
.dark .pdf-header{background:var(--surface-2)}
.header-left{display:flex;align-items:center;gap:12px}
.header-logo img{max-height:42px;max-width:140px}
.header-logo-text{font-size:15px;font-weight:700;color:var(--brand);font-family:Inter,sans-serif}
.header-info{display:flex;align-items:center;gap:8px;flex-wrap:wrap}
.header-doc-label{font-size:10px;text-transform:uppercase;letter-spacing:.06em;color:var(--brand);font-weight:700}
.header-doc-code{font-size:10px;color:var(--brand-dark);font-weight:700;margin-left:4px}
.header-badge{
    display:inline-block;font-size:9px;font-weight:700;
    padding:2px 8px;border-radius:99px;
    border:1px solid var(--brand_border);
    background:var(--brand-light);
    color:var(--brand-dark);
    letter-spacing:.04em;text-transform:uppercase;
}
.header-badge-green{
    border-color:var(--success-border);
    background:var(--success-light);
    color:var(--success);
}
.header-meta{
    font-size:9px;color:var(--muted);text-align:right;line-height:1.7;
}
.header-meta strong{color:var(--text)}
.theme-btn{
    width:32px;height:32px;border-radius:50%;
    background:var(--brand-light);
    border:1px solid var(--brand_border);
    color:var(--brand-dark);
    cursor:pointer;display:flex;align-items:center;justify-content:center;
    font-size:.85rem;transition:background .15s;
}
.theme-btn:hover{background:var(--brand_border)}
.dark .theme-btn{background:var(--surface-3);border-color:var(--border);color:var(--muted)}

/* Rodapé fixo */
.pdf-footer{
    position:fixed;
    bottom:0;left:0;right:0;
    padding:8px 24px;
    background:var(--surface);
    border-top:1px solid var(--border);
    font-size:10px;
    color:var(--muted);
    z-index:50;
    display:flex;
    align-items:center;
    justify-content:space-between;
}
.dark .pdf-footer{background:var(--surface-2)}

/* Conteúdo */
.page-body{
    max-width:960px;
    margin:88px auto 0;
    padding:0 24px;
}

/* Cover section */
.cover-section{
    background:var(--surface);
    border:1px solid var(--border);
    border-radius:var(--radius-lg);
    overflow:hidden;
    margin-bottom:20px;
    box-shadow:var(--shadow);
}
.cover-header{
    background:var(--brand);
    padding:32px 32px 24px;
    position:relative;
}
.cover-header::after{
    content:'';
    position:absolute;
    top:-60%;right:-8%;
    width:400px;height:400px;
    border-radius:50%;
    background:radial-gradient(circle,rgba(255,255,255,.08)0%,transparent 70%);
    pointer-events:none;
}
.cover-header-top{
    display:flex;
    align-items:center;
    justify-content:space-between;
    margin-bottom:24px;
    position:relative;z-index:1;
}
.cover-doc-badge{
    display:inline-flex;align-items:center;gap:8px;
    background:rgba(255,255,255,.12);
    border:1px solid rgba(255,255,255,.25);
    color:rgba(255,255,255,.9);
    padding:4px 14px;border-radius:99px;
    font-size:11px;font-weight:700;
    letter-spacing:.08em;text-transform:uppercase;
}
.cover-title{
    font-size:26px;font-weight:800;
    color:#fff;line-height:1.2;
    letter-spacing:-.02em;
    margin-bottom:8px;
    padding-left:16px;
    border-left:3px solid rgba(255,255,255,.5);
    position:relative;z-index:1;
}
.cover-client-line{
    display:flex;align-items:center;gap:10px;
    margin-bottom:16px;position:relative;z-index:1;
}
.cover-client-label{font-size:10px;font-weight:700;color:rgba(255,255,255,.55);text-transform:uppercase;letter-spacing:.08em}
.cover-client-name{font-size:16px;font-weight:700;color:#fff}
.cover-meta-strip{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(140px,1fr));
    gap:1px;
    background:rgba(255,255,255,.10);
    border:1px solid rgba(255,255,255,.12);
    border-radius:8px;
    overflow:hidden;
    position:relative;z-index:1;
}
.cover-meta-cell{
    background:rgba(0,0,0,.25);
    padding:12px 14px;
    text-align:center;
}
.cover-meta-cell:last-child{
    background:linear-gradient(160deg,rgba(37,99,235,.25),rgba(0,0,0,.25));
}
.cover-meta-cell .clabel{
    font-size:9px;font-weight:700;
    color:rgba(255,255,255,.5);
    text-transform:uppercase;
    letter-spacing:.08em;
    display:block;margin-bottom:4px;
}
.cover-meta-cell .cvalue{
    font-size:13px;font-weight:700;
    color:#fff;line-height:1.2;
    font-family:Inter,sans-serif;
}
.cover-meta-cell .cvalue.big{
    font-size:18px;font-weight:800;
    color:#FCD34D;
}

/* Status banner */
.status-banner{
    display:flex;align-items:center;gap:14px;
    padding:16px 20px;
    background:var(--surface);
    border:1px solid var(--border);
    border-left:4px solid transparent;
    border-radius:var(--radius-lg);
    margin-bottom:16px;
    box-shadow:var(--shadow);
}
.status-banner.pending{border-left-color:#f59e0b}
.status-banner.approved{border-left-color:#10b981}
.status-banner.rejected{border-left-color:#ef4444}
.sb-icon{
    width:42px;height:42px;border-radius:50%;
    display:flex;align-items:center;justify-content:center;
    font-size:1.1rem;flex-shrink:0;
}
.status-banner.pending .sb-icon{background:#fef3c7;color:#b45309}
.status-banner.approved .sb-icon{background:#d1fae5;color:#059669}
.status-banner.rejected .sb-icon{background:#fee2e2;color:#dc2626}
.sb-title{font-size:15px;font-weight:700;margin-bottom:2px}
.status-banner.pending .sb-title{color:#92400e}
.status-banner.approved .sb-title{color:#065f46}
.status-banner.rejected .sb-title{color:#991b1b}
.sb-sub{font-size:12px;color:var(--muted)}

/* Section */
.section{
    background:var(--surface);
    border:1px solid var(--border);
    border-radius:var(--radius-lg);
    margin-bottom:14px;
    overflow:hidden;
    box-shadow:var(--shadow);
}
.sec-title{
    display:flex;align-items:center;gap:10px;
    padding:12px 18px;
    background:var(--brand-light);
    border-bottom:1px solid var(--border);
    font-size:11px;font-weight:700;text-transform:uppercase;
    letter-spacing:.08em;color:var(--brand-dark);
}
.dark .sec-title{background:rgba(37,99,235,.15)}
.sec-num{
    width:22px;height:22px;border-radius:4px;
    background:var(--brand);color:#fff;
    font-size:10px;font-weight:700;font-family:monospace;
    display:flex;align-items:center;justify-content:center;flex-shrink:0;
}
.sec-count{
    margin-left:auto;
    font-size:10px;color:var(--muted);
    background:var(--surface-3);
    padding:2px 10px;
    border-radius:99px;
    border:1px solid var(--border);
}
.sec-body{padding:16px 18px}
.sec-body-no-pad{padding:0}

/* Info grid */
.info-grid{
    display:grid;
    grid-template-columns:repeat(auto-fill,minmax(200px,1fr));
    gap:12px;
}
.info-cell{display:flex;flex-direction:column;gap:2px}
.info-label{
    font-size:10px;font-weight:700;
    color:var(--muted);text-transform:uppercase;letter-spacing:.07em;
}
.info-value{font-size:14px;font-weight:600;color:var(--text);line-height:1.4}
.info-sub{font-size:11px;color:var(--muted)}

/* Scope */
.scope-box{
    background:var(--surface-2);
    border-left:3px solid var(--brand);
    border-radius:0 8px 8px 0;
    padding:14px 18px;
    font-size:13px;
    color:var(--text-3);
    line-height:1.75;
}
.scope-box p{margin:0 0 8px 0;text-indent:1.25cm}

/* Tabela itens */
.table-wrap{overflow-x:auto}
.items-table{
    width:100%;border-collapse:collapse;font-size:12px;min-width:580px;
}
.items-table thead tr{background:var(--brand)}
.items-table thead th{
    padding:8px 12px;
    font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;
    color:#fff;text-align:left;
}
.items-table thead th.r{text-align:right}
.items-table tbody tr{border-bottom:1px solid var(--border)}
.items-table tbody td{padding:8px 12px;vertical-align:top;color:var(--text)}
.items-table tbody td.r{text-align:right;font-weight:600}
.item-name{font-weight:600;margin-bottom:2px;font-size:13px}
.item-desc{font-size:11px;color:var(--muted);line-height:1.5}
.item-num{font-family:monospace;font-size:11px;color:var(--muted)}
.cat-badge{
    display:inline-block;padding:2px 10px;border-radius:99px;
    font-size:10px;font-weight:700;line-height:1.5;
}
.cat-header-row td{
    padding:6px 12px;background:var(--surface-2);
    font-size:10px;color:var(--muted);border-bottom:1px solid var(--border);
    font-weight:700;text-transform:uppercase;letter-spacing:.05em;
}
.cat-subtotal-row td{
    background:var(--surface-2);
    font-weight:700;padding:6px 12px;border-bottom:1px solid var(--border);
}
.cat-subtotal-row td.r{text-align:right}
.disc-chip{
    display:inline-flex;align-items:center;
    padding:1px 8px;border-radius:99px;
    background:#d1fae5;color:#065f46;
    font-size:10px;font-weight:700;margin-left:6px;
}

/* Financial */
.fin-wrap{display:flex;justify-content:flex-end;padding:12px 18px 16px}
.fin-box{width:320px}
.fin-row{
    display:flex;justify-content:space-between;align-items:baseline;
    padding:6px 0;border-bottom:1px solid var(--border);
    font-size:13px;color:var(--muted);
}
.fin-row.discount{color:#059669;font-weight:600}
.fin-row.grand{
    margin-top:6px;padding-top:10px;
    border-top:2px solid var(--brand);
    color:var(--text);font-weight:700;
}
.fin-total-val{
    font-size:22px;font-weight:800;
    color:var(--brand);letter-spacing:-.02em;
}

/* Cat summary */
.cat-summary-grid{
    display:grid;
    grid-template-columns:repeat(auto-fill,minmax(180px,1fr));
    gap:8px;
    padding:12px 18px;
    border-bottom:1px solid var(--border);
}
.cat-summary-card{
    border-radius:8px;padding:10px 12px;border:1px solid;
}
.cat-summary-card .cscat{font-size:10px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;margin-bottom:4px}
.cat-summary-card .cscount{font-size:11px;margin-bottom:2px}
.cat-summary-card .csval{font-size:15px;font-weight:700}

/* Condições */
.cond-grid{
    display:grid;
    grid-template-columns:repeat(auto-fill,minmax(220px,1fr));
    gap:10px;
}
.cond-card{
    background:var(--surface-2);
    border:1px solid var(--border);
    border-radius:8px;
    padding:12px 14px;
}
.cond-icon-wrap{
    width:28px;height:28px;border-radius:6px;
    background:var(--brand-light);color:var(--brand-dark);
    display:flex;align-items:center;justify-content:center;
    font-size:12px;margin-bottom:8px;
}
.cond-label{font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--muted);margin-bottom:4px}
.cond-value{font-size:13px;font-weight:600;color:var(--text);line-height:1.5}
.cond-sub{font-size:11px;color:var(--muted);margin-top:2px;font-weight:400}

/* Payment */
.payment-detail{
    background:var(--surface-2);
    border:1px solid var(--border);border-radius:8px;
    padding:12px 14px;
    margin-top:10px;
    font-size:13px;color:var(--muted);
    display:flex;align-items:flex-start;gap:10px;
}

/* Equipe */
.equipe-table{width:100%;border-collapse:collapse;font-size:12px}
.equipe-table thead th{
    background:var(--brand);
    padding:8px 12px;text-align:left;
    font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;
    color:#fff;
}
.equipe-table tbody td{padding:8px 12px;border-bottom:1px solid var(--border);color:var(--text);vertical-align:middle}
.avatar-circle{
    width:30px;height:30px;border-radius:50%;
    background:var(--brand-light);color:var(--brand-dark);
    display:flex;align-items:center;justify-content:center;
    font-size:10px;font-weight:700;flex-shrink:0;
}

/* Obs */
.obs-box{
    background:var(--surface-2);
    border:1px dashed var(--border);
    border-radius:8px;padding:12px 16px;
    font-size:13px;color:var(--muted);line-height:1.7;
    white-space:pre-wrap;
}

/* Alert */
.alert{
    display:flex;align-items:flex-start;gap:10px;
    padding:12px 18px;border-radius:8px;
    margin-bottom:14px;font-size:13px;font-weight:500;
    border-left:3px solid transparent;
}
.alert-success{background:#d1fae5;color:#047857;border-color:#10b981}
.alert-error{background:#fee2e2;color:#991b1b;border-color:#ef4444}

/* Aceite */
.aceite-record{
    background:#f0fdf4;
    border:1.5px solid #6ee7b7;
    border-radius:var(--radius-lg);
    padding:14px 18px;
    display:flex;align-items:flex-start;gap:12px;
    margin-bottom:14px;
}
.aceite-icon{
    width:36px;height:36px;border-radius:50%;
    background:#d1fae5;color:#059669;
    display:flex;align-items:center;justify-content:center;
    font-size:1rem;flex-shrink:0;
}
.aceite-title{font-size:13px;font-weight:700;color:#065f46;margin-bottom:6px}
.aceite-meta{font-size:11px;color:#047857;display:flex;flex-direction:column;gap:2px}
.aceite-meta span{color:#059669;font-weight:600;margin-right:4px}

/* Footer text */
.page-footer{
    text-align:center;padding:20px;
    font-size:11px;color:var(--muted);
    line-height:1.8;
    border-top:1px solid var(--border);
    margin-top:24px;
}

/* FAB */
.fab-bar{
    position:fixed;bottom:32px;left:50%;transform:translateX(-50%);
    z-index:100;
    background:var(--surface);
    backdrop-filter:blur(12px);
    border:1.5px solid var(--brand);
    border-radius:14px;
    box-shadow:0 8px 32px rgba(37,99,235,.15);
    padding:10px 18px;
    display:flex;align-items:center;gap:16px;
    animation:slideUp .4s cubic-bezier(.22,1,.36,1) both;
}
.dark .fab-bar{background:rgba(31,41,55,.95)}
@keyframes slideUp{from{transform:translateX(-50%) translateY(100%);opacity:0}to{transform:translateX(-50%) translateY(0);opacity:1}}
.fab-inner{display:flex;align-items:center;gap:16px;flex-wrap:wrap;justify-content:center}
.fab-total{display:flex;align-items:center;gap:8px;font-size:12px;color:var(--muted)}
.fab-total strong{font-size:18px;font-weight:800;color:var(--brand)}
.fab-divider{width:1px;height:24px;background:var(--border)}
.fab-validity{font-size:11px;color:var(--muted)}
.fab-validity strong{color:var(--text)}
.fab-actions{display:flex;align-items:center;gap:8px}

.btn{
    display:inline-flex;align-items:center;justify-content:center;
    gap:6px;padding:10px 20px;
    border-radius:8px;
    font-weight:700;font-size:13px;font-family:inherit;
    cursor:pointer;border:none;text-decoration:none;
    transition:transform .12s,box-shadow .12s,background .12s;
    white-space:nowrap;letter-spacing:-.01em;
}
.btn:hover{transform:translateY(-1px)}
.btn:active{transform:translateY(0)}
.btn-approve{background:#2563eb;color:#fff;box-shadow:0 4px 14px rgba(37,99,235,.3)}
.btn-approve:hover{background:#1d4ed8;box-shadow:0 6px 18px rgba(37,99,235,.4)}
.btn-reject{background:var(--surface);color:var(--danger);border:1.5px solid var(--danger)}
.btn-reject:hover{background:#FDF2F2}
.btn-icon{padding:10px;border-radius:8px;background:var(--surface-2);color:var(--muted);border:1px solid var(--border)}
.dark .btn-icon{background:var(--surface-3)}
.btn-secondary{background:var(--surface-2);color:var(--muted);border:1px solid var(--border)}

/* Modal */
.modal-overlay{
    position:fixed;inset:0;z-index:200;
    background:rgba(15,23,42,.6);
    backdrop-filter:blur(4px);
    display:none;align-items:center;justify-content:center;
    padding:20px;
}
.modal-overlay.open{display:flex;animation:fadeIn .2s ease}
@keyframes fadeIn{from{opacity:0}to{opacity:1}}
.modal-box{
    background:var(--surface);
    border-radius:14px;padding:24px;
    width:100%;max-width:460px;
    box-shadow:0 24px 60px rgba(0,0,0,.18);
    border:1px solid var(--border);
}
.modal-icon{
    width:48px;height:48px;border-radius:50%;
    display:flex;align-items:center;justify-content:center;
    font-size:1.2rem;margin-bottom:16px;
}
.modal-icon.approve{background:#dbeafe;color:#2563eb}
.modal-icon.reject{background:#fee2e2;color:#dc2626}
.modal-box h3{font-size:16px;font-weight:700;color:var(--text);margin-bottom:8px}
.modal-box p{font-size:13px;color:var(--muted);line-height:1.6;margin-bottom:16px}
.modal-label{display:block;font-size:11px;font-weight:700;color:var(--muted);margin-bottom:4px;letter-spacing:.03em}
.modal-input,.modal-textarea{
    width:100%;background:var(--surface-2);
    border:1.5px solid var(--border);
    border-radius:8px;padding:10px 12px;
    font-size:13px;color:var(--text);
    font-family:inherit;resize:vertical;
    transition:border-color .15s,box-shadow .15s;
    margin-bottom:12px;
}
.modal-input{min-height:unset;height:40px;resize:none}
.modal-textarea{min-height:80px}
.modal-input:focus,.modal-textarea:focus{
    outline:none;border-color:var(--brand);
    box-shadow:0 0 0 3px rgba(37,99,235,.15);
}
.aceite-wrap{
    background:var(--brand-light);
    border:1px solid var(--brand_border);
    border-radius:8px;padding:10px 14px;
    display:flex;align-items:flex-start;gap:8px;
    cursor:pointer;margin-bottom:16px;
    font-size:12px;color:var(--muted);font-weight:500;line-height:1.5;
}
.aceite-wrap input[type="checkbox"]{flex-shrink:0;margin-top:2px;cursor:pointer}
.modal-actions{display:flex;gap:8px;justify-content:flex-end}

/* Animations */
.anim{animation:fadeUp .4s cubic-bezier(.22,1,.36,1) both}
@keyframes fadeUp{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:translateY(0)}}
.d1{animation-delay:.04s}.d2{animation-delay:.09s}.d3{animation-delay:.14s}
.d4{animation-delay:.19s}.d5{animation-delay:.24s}.d6{animation-delay:.29s}

@media print{
    .fab-bar,.theme-btn,.modal-overlay,.pdf-header,.pdf-footer{display:none!important}
    body{padding:0;background:#fff}
    .cover-header{print-color-adjust:exact;-webkit-print-color-adjust:exact}
    .section{break-inside:avoid}
}
@media(max-width:640px){
    .page-body{padding:0 12px}
    .cover-header{padding:20px 16px 18px}
    .cover-meta-strip{grid-template-columns:repeat(2,1fr)}
    .cover-meta-cell .cvalue.big{font-size:15px}
    .info-grid{grid-template-columns:1fr}
    .fin-wrap{padding:10px}
    .fin-box{width:100%}
    .fab-bar{bottom:16px;left:12px;right:12px;transform:none;border-radius:10px;padding:10px 14px}
    .fab-inner{flex-direction:column;gap:8px}
    .fab-actions{width:100%;justify-content:stretch}
    .fab-actions .btn{flex:1;justify-content:center}
    .modal-actions{flex-wrap:wrap}
    .modal-actions .btn{flex:1;justify-content:center}
}
</style>
<script>
    if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
    }
</script>
</head>
<body>

<?php
// Logo para marca d'água e header
$empresaLogoPath = !empty($empresa['logo_path']) && file_exists(ROOT_PATH . '/public/uploads/logos/' . $empresa['logo_path'])
    ? BASE_URL . '/uploads/logos/' . htmlspecialchars($empresa['logo_path'])
    : null;
$logoBase64 = null;
$logoFile = ROOT_PATH . '/public/assets/images/logo.png';
if (file_exists($logoFile)) {
    $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoFile));
}
?>

<?php if ($logoBase64): ?>
<div class="watermark">
    <img src="<?= $logoBase64 ?>" alt="">
</div>
<?php endif; ?>

<!-- Cabeçalho fixo -->
<div class="pdf-header">
    <div class="header-left">
        <div class="header-logo">
            <?php if ($empresaLogoPath): ?>
                <img src="<?php echo $empresaLogoPath; ?>" alt="Logo" style="max-height:42px;max-width:140px">
            <?php else: ?>
                <span class="header-logo-text"><?php echo htmlspecialchars($logoAlt); ?></span>
            <?php endif; ?>
        </div>
        <div class="header-info">
            <span class="header-doc-label">Proposta Técnica Orçamentária <span class="header-doc-code"><?php echo htmlspecialchars($codigo); ?></span></span>
            <?php if ($contratoNum): ?>
            <span class="header-badge" style="border-color:var(--success-border);background:var(--success-light);color:var(--success)">Contrato <?php echo htmlspecialchars($contratoNum); ?></span>
            <?php endif; ?>
        </div>
    </div>
    <div style="display:flex;align-items:center;gap:10px">
        <div class="header-meta">
            Emitida em <strong><?php echo $dataEmissao; ?></strong>
            <?php if ($responsavel): ?><br>Elaborado por <strong><?php echo htmlspecialchars($responsavel); ?></strong><?php endif; ?>
        </div>
        <button class="theme-btn" onclick="toggleTheme()" title="Alternar tema" aria-label="Alternar tema">
            <i class="fas fa-moon" id="theme-icon"></i>
        </button>
    </div>
</div>

<!-- Rodapé fixo -->
<div class="pdf-footer">
    <span><?php echo htmlspecialchars($logoAlt); ?> &mdash; CNPJ <?php echo htmlspecialchars($empresa['cnpj'] ?? '00.000.000/0000-00'); ?></span>
    <span><?php echo htmlspecialchars($codigo); ?> &mdash; <?php echo htmlspecialchars($titulo); ?></span>
</div>

<?php if (!isset($proposta)): ?>
<div class="page-body" style="display:flex;align-items:center;justify-content:center;min-height:60vh">
    <div style="text-align:center;max-width:380px">
        <div style="font-size:3rem;margin-bottom:1rem;color:var(--muted)"><i class="fas fa-file-slash"></i></div>
        <h2 style="font-size:1.25rem;font-weight:700;color:var(--text);margin-bottom:.5rem">Proposta não encontrada</h2>
        <p style="color:var(--muted);font-size:.875rem;line-height:1.65">O link pode ter expirado ou a proposta não está disponível. Entre em contato com o responsável.</p>
    </div>
</div>
<?php else: ?>

<div class="page-body">

    <!-- Cover -->
    <div class="cover-section anim d1">
        <div class="cover-header">
            <div class="cover-header-top">
                <div class="cover-doc-badge">
                    <i class="fas fa-file-contract"></i>
                    Proposta Técnica Orçamentária
                </div>
            </div>
            <div class="cover-client-line">
                <span class="cover-client-label">Cliente</span>
                <span class="cover-client-name"><?php echo htmlspecialchars($clienteNome); ?></span>
                <?php if ($clienteSigla): ?>
                <span style="font-size:11px;color:rgba(255,255,255,.5);font-weight:500">(<?php echo htmlspecialchars($clienteSigla); ?>)</span>
                <?php endif; ?>
            </div>
            <h1 class="cover-title"><?php echo htmlspecialchars($titulo); ?></h1>
            <?php if ($projetoNome || $contratoNum || $municipio): ?>
            <div style="display:flex;flex-wrap:wrap;gap:6px;position:relative;z-index:1;margin-bottom:20px">
                <?php if ($projetoNome): ?>
                <span style="display:inline-flex;align-items:center;gap:6px;background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.2);color:rgba(255,255,255,.85);padding:3px 10px;border-radius:99px;font-size:11px;font-weight:600">
                    <i class="fas fa-project-diagram" style="font-size:10px"></i>
                    <?php echo htmlspecialchars($projetoNome); ?>
                </span>
                <?php endif; ?>
                <?php if ($contratoNum): ?>
                <span style="display:inline-flex;align-items:center;gap:6px;background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.2);color:rgba(255,255,255,.85);padding:3px 10px;border-radius:99px;font-size:11px;font-weight:600">
                    <i class="fas fa-hashtag" style="font-size:10px"></i>
                    Contrato <?php echo htmlspecialchars($contratoNum); ?>
                </span>
                <?php endif; ?>
                <?php if ($municipio): ?>
                <span style="display:inline-flex;align-items:center;gap:6px;background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.2);color:rgba(255,255,255,.85);padding:3px 10px;border-radius:99px;font-size:11px;font-weight:600">
                    <i class="fas fa-map-marker-alt" style="font-size:10px"></i>
                    <?php echo htmlspecialchars($municipio); ?><?php echo $area ? " &middot; {$area}" : ''; ?>
                </span>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            <div class="cover-meta-strip">
                <div class="cover-meta-cell">
                    <span class="clabel">Proposta N&ordm;</span>
                    <span class="cvalue"><?php echo htmlspecialchars($codigo); ?></span>
                </div>
                <div class="cover-meta-cell">
                    <span class="clabel">Emiss&atilde;o</span>
                    <span class="cvalue"><?php echo $dataEmissao; ?></span>
                </div>
                <div class="cover-meta-cell">
                    <span class="clabel">Validade</span>
                    <span class="cvalue"><?php echo $validadeDias; ?> dias</span>
                </div>
                <?php if ($responsavel): ?>
                <div class="cover-meta-cell">
                    <span class="clabel">Elaborado por</span>
                    <span class="cvalue"><?php echo htmlspecialchars($responsavel); ?></span>
                </div>
                <?php endif; ?>
                <?php if ($totalFinal > 0): ?>
                <div class="cover-meta-cell">
                    <span class="clabel">Valor Total</span>
                    <span class="cvalue big"><?php echo ReportHelper::formatCurrency($totalFinal); ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Flash messages -->
    <?php if (!empty($_GET['msg'])): ?>
    <?php $msg = $_GET['msg']; ?>
    <?php if ($msg === 'aprovada'): ?>
    <div class="alert alert-success anim d1"><i class="fas fa-check-circle"></i> Proposta aprovada com sucesso! Nossa equipe j&aacute; foi notificada.</div>
    <?php elseif ($msg === 'rejeitada'): ?>
    <div class="alert alert-error anim d1"><i class="fas fa-times-circle"></i> Proposta rejeitada. Obrigado pelo retorno.</div>
    <?php endif; ?>
    <?php endif; ?>

    <!-- Status banner -->
    <?php
    $bannerClass = match($status) {
        'Aprovada'  => 'approved',
        'Rejeitada' => 'rejected',
        default     => 'pending',
    };
    $bannerIcon = match($status) {
        'Aprovada'  => 'fa-check-circle',
        'Rejeitada' => 'fa-times-circle',
        default     => 'fa-clock',
    };
    $bannerTitle = match($status) {
        'Aprovada'  => 'Proposta Aprovada',
        'Rejeitada' => 'Proposta Rejeitada',
        default     => 'Aguardando sua decis&atilde;o',
    };
    $bannerSub = match($status) {
        'Aprovada'  => 'Obrigado pela confian&ccedil;a! Nossa equipe entrar&aacute; em contato em breve para alinhar os pr&oacute;ximos passos.',
        'Rejeitada' => 'Agradecemos o retorno. Entre em contato para negociarmos novas condi&ccedil;&otilde;es.',
        default     => 'Revise os detalhes abaixo e utilize os bot&otilde;es no rodap&eacute; para aprovar ou rejeitar esta proposta.',
    };
    ?>
    <div class="status-banner <?php echo $bannerClass; ?> anim d1">
        <div class="sb-icon"><i class="fas <?php echo $bannerIcon; ?>"></i></div>
        <div>
            <div class="sb-title"><?php echo $bannerTitle; ?></div>
            <div class="sb-sub"><?php echo $bannerSub; ?></div>
        </div>
        <?php if ($validadeToken && $isPending): ?>
        <div style="margin-left:auto;text-align:right;white-space:nowrap">
            <div style="font-size:10px;font-weight:700;color:#b45309;text-transform:uppercase;letter-spacing:.08em">V&aacute;lida at&eacute;</div>
            <div style="font-size:13px;font-weight:700;color:#92400e"><?php echo $validadeToken; ?></div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Aceite record (when approved) -->
    <?php if ($status === 'Aprovada' && !empty($proposta['aceite_em'])): ?>
    <div class="aceite anim d1">
        <div class="aceite-icon"><i class="fas fa-file-signature"></i></div>
        <div>
            <div class="aceite-title">Registro de Aceite Eletr&ocirc;nico</div>
            <div class="aceite-meta">
                <?php if (!empty($proposta['aceite_nome'])): ?>
                <div><span>Nome:</span><?php echo htmlspecialchars($proposta['aceite_nome']); ?></div>
                <?php endif; ?>
                <div><span>Data/Hora:</span><?php echo date('d/m/Y \&\a\g\r\a\v\e;s H:i:s', strtotime($proposta['aceite_em'])); ?></div>
                <?php if (!empty($proposta['aceite_ip'])): ?>
                <div><span>IP:</span><?php echo htmlspecialchars($proposta['aceite_ip']); ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- 1. INFORMAÇÕES DO CLIENTE -->
    <div class="section anim d2">
        <div class="sec-title">
            <div class="sec-num">1</div>
            <span>Informa&ccedil;&otilde;es do Cliente</span>
        </div>
        <div class="sec-body">
            <div class="info-grid">
                <div class="info-cell" style="<?php echo (!$representante && !$emailCliente) ? 'grid-column:1/-1' : ''; ?>">
                    <span class="info-label">Raz&atilde;o Social / Nome</span>
                    <span class="info-value"><?php echo htmlspecialchars($clienteNome); ?></span>
                    <?php if ($clienteSigla): ?><span class="info-sub"><?php echo htmlspecialchars($clienteSigla); ?></span><?php endif; ?>
                </div>
                <?php if ($representante): ?>
                <div class="info-cell">
                    <span class="info-label">Representante</span>
                    <span class="info-value"><?php echo htmlspecialchars($representante); ?></span>
                </div>
                <?php endif; ?>
                <?php if ($emailCliente): ?>
                <div class="info-cell">
                    <span class="info-label">E-mail</span>
                    <span class="info-value"><?php echo htmlspecialchars($emailCliente); ?></span>
                </div>
                <?php endif; ?>
                <?php if ($telefone): ?>
                <div class="info-cell">
                    <span class="info-label">Telefone</span>
                    <span class="info-value"><?php echo htmlspecialchars($telefone); ?></span>
                </div>
                <?php endif; ?>
                <?php if ($clienteDoc && $clienteDoc !== '—'): ?>
                <div class="info-cell">
                    <span class="info-label">CNPJ / CPF</span>
                    <span class="info-value"><?php echo htmlspecialchars($clienteDoc); ?></span>
                </div>
                <?php endif; ?>
                <?php if ($clienteEndereco): ?>
                <div class="info-cell" style="grid-column:1/-1">
                    <span class="info-label">Endere&ccedil;o</span>
                    <span class="info-value"><?php echo htmlspecialchars($clienteEndereco); ?></span>
                </div>
                <?php endif; ?>
            </div>
            <?php if (!empty($contextualizacao) || !empty($contextualizacaoTextoIntro)): ?>
            <div style="margin-top:14px;padding-top:14px;border-top:1px solid var(--border)">
                <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--muted);margin-bottom:10px">Localiza&ccedil;&atilde;o / Contextualiza&ccedil;&atilde;o</div>
                <?php if (!empty($contextualizacaoTextoIntro)): ?>
                <div style="font-size:12px;color:var(--muted);margin-bottom:10px;line-height:1.75"><?php
                    $paragrafos = preg_split('/\n\s*\n/', trim($contextualizacaoTextoIntro));
                    foreach ($paragrafos as $p):
                        $p = nl2br(trim($p));
                        if ($p !== ''):
                    ?><p style="margin:0 0 6px 0;text-indent:1.25cm"><?= $p ?></p><?php
                        endif;
                    endforeach;
                ?></div>
                <?php endif; ?>
                <?php if (!empty($contextualizacao) && !$ocultarTabelaContexto): ?>
                <div style="overflow-x:auto">
                    <table style="width:100%;border-collapse:collapse;font-size:12px;min-width:400px">
                        <thead>
                            <tr style="background:var(--brand-light);border-bottom:1px solid var(--border)">
                                <?php foreach (array_keys(reset($contextualizacao)) as $col): ?>
                                <th style="padding:6px 10px;text-align:left;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--brand-dark)">
                                    <?php echo htmlspecialchars(ucfirst($col)); ?>
                                </th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($contextualizacao as $row): ?>
                            <tr style="border-bottom:1px solid var(--border)">
                                <?php foreach ($row as $val): ?>
                                <td style="padding:6px 10px;color:var(--text)"><?php echo htmlspecialchars($val); ?></td>
                                <?php endforeach; ?>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- 2. ESCOPO / OBJETO -->
    <?php if ($escopo): ?>
    <div class="section anim d2">
        <div class="sec-title">
            <div class="sec-num">2</div>
            <span>Apresenta&ccedil;&atilde;o / Objeto da Proposta</span>
        </div>
        <div class="sec-body">
            <div class="scope-box"><?php
                $paragrafos = preg_split('/\n\s*\n/', trim($escopo));
                foreach ($paragrafos as $paragrafo):
                    $paragrafo = nl2br(trim($paragrafo));
                    if ($paragrafo !== ''):
            ?><p><?= $paragrafo ?></p><?php
                    endif;
                endforeach;
            ?></div>
        </div>
    </div>
    <?php endif; ?>

    <!-- 3. EQUIPE TÉCNICA -->
    <?php if (!empty($equipe)): ?>
    <div class="section anim d3">
        <div class="sec-title">
            <div class="sec-num">3</div>
            <span>Equipe T&eacute;cnica</span>
            <span class="sec-count"><?php echo count($equipe); ?> membro(s)</span>
        </div>
        <?php if (!empty($equipeTextoIntro)): ?>
        <div style="font-size:12px;color:var(--muted);padding:12px 18px 0;line-height:1.5"><?= nl2br(htmlspecialchars($equipeTextoIntro)) ?></div>
        <?php endif; ?>
        <div class="sec-body sec-body-no-pad" style="padding:0">
            <div style="overflow-x:auto">
                <table class="equipe-table">
                    <thead>
                        <tr>
                            <th>Profissional</th>
                            <th>Campo de Atua&ccedil;&atilde;o</th>
                            <th>Fun&ccedil;&atilde;o</th>
                            <?php if (!empty(array_column($equipe, 'registro'))): ?><th>Registro</th><?php endif; ?>
                            <?php if (!empty(array_column($equipe, 'horas'))): ?><th style="text-align:right">Horas</th><?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($equipe as $membro):
                            $nomeMembro = $membro['profissional'] ?? $membro['nome'] ?? '';
                            $partes = $nomeMembro ? array_slice(explode(' ', $nomeMembro), 0, 2) : [];
                            $iniciais = strtoupper(implode('', array_map(fn($w) => $w[0] ?? '', $partes)));
                        ?>
                        <tr>
                            <td>
                                <div style="display:flex;align-items:center;gap:8px">
                                    <div class="avatar-circle"><?php echo htmlspecialchars($iniciais); ?></div>
                                    <div>
                                        <div style="font-weight:600;color:var(--text)"><?php echo htmlspecialchars($nomeMembro); ?></div>
                                        <?php if (!empty($membro['email'])): ?><div style="font-size:11px;color:var(--muted)"><?php echo htmlspecialchars($membro['email']); ?></div><?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($membro['campo'] ?? '—'); ?></td>
                            <td><?php echo htmlspecialchars($membro['funcao'] ?? $membro['cargo'] ?? '—'); ?></td>
                            <?php if (!empty(array_column($equipe, 'registro'))): ?>
                            <td style="font-size:11px;color:var(--muted)"><?php echo htmlspecialchars($membro['registro'] ?? '—'); ?></td>
                            <?php endif; ?>
                            <?php if (!empty(array_column($equipe, 'horas'))): ?>
                            <td style="text-align:right;font-weight:600"><?php echo htmlspecialchars($membro['horas'] ?? '—'); ?>h</td>
                            <?php endif; ?>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- 4. ITENS DA PROPOSTA -->
    <?php if (!empty($itens)): ?>
    <div class="section anim d3">
        <div class="sec-title">
            <div class="sec-num"><?php echo (!empty($equipe)) ? '4' : (($escopo) ? '3' : '2'); ?></div>
            <span>Detalhamento dos Itens</span>
            <span class="sec-count"><?php echo count($itens); ?> item(ns)</span>
        </div>
        <div class="sec-body sec-body-no-pad" style="padding:0">
            <div class="table-wrap">
                <table class="items-table">
                    <thead>
                        <tr>
                            <th style="width:40px">#</th>
                            <th>Descri&ccedil;&atilde;o</th>
                            <th style="width:50px;text-align:center">Un.</th>
                            <th class="r" style="width:70px">Qtd.</th>
                            <th class="r" style="width:100px">Unit&aacute;rio</th>
                            <th class="r" style="width:60px">Desc.%</th>
                            <th class="r" style="width:110px">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $prevCat = null;
                    $catSubtotal = 0;
                    $totalRegular = count(array_filter($itens, fn($i) => !in_array($i['categoria'] ?? '', ['Titulo', 'Subtitulo', 'Legenda'])));
                    $itemIndex = 0;
                    $subSectionCounter = 0;
                    foreach ($itens as $idx => $item):
                        $cat    = $item['categoria'] ?? '';
                        $isTitulo    = ($cat === 'Titulo');
                        $isSubtitulo = ($cat === 'Subtitulo');
                        $isLegend    = ($cat === 'Legenda');
                        $isEspecial  = $isTitulo || $isSubtitulo || $isLegend;
                        $qtd    = (float)($item['quantidade']   ?? 1);
                        $vunit  = (float)($item['valor_unit']   ?? $item['valor_unitario'] ?? 0);
                        $disc   = (float)($item['desconto_item']?? $item['desconto'] ?? 0);
                        $subtot = $qtd * $vunit * (1 - $disc / 100);
                        $cor    = $catCores[$cat] ?? $catCores['Outros'];
                        $nome   = $item['nome'] ?? $item['descricao'] ?? '';
                        $desc   = $item['detalhes'] ?? ($item['descricao'] ?? ($item['nome'] !== $nome ? ($item['descricao'] ?? '') : ''));
                        if (isset($item['nome']) && $desc === $item['nome']) $desc = '';

                        if ($isTitulo):
                            $subSectionCounter++;
                    ?>
                        <tr>
                            <td colspan="7" style="padding:8px 12px;font-weight:700;font-size:14px;color:var(--brand);border-bottom:1px solid var(--border);background:var(--surface-2)">
                                <span style="font-family:monospace;font-size:12px;color:var(--muted);margin-right:6px"><?php echo str_pad($subSectionCounter, 2, '0', STR_PAD_LEFT); ?>.</span>
                                <?php echo htmlspecialchars($nome); ?>
                            </td>
                        </tr>
                    <?php elseif ($isSubtitulo): ?>
                        <tr>
                            <td colspan="7" style="padding:6px 16px;font-size:12px;color:var(--muted);line-height:1.7;text-align:justify">
                                <?php echo nl2br(htmlspecialchars($item['descricao'] ?? $item['detalhes'] ?? '')); ?>
                            </td>
                        </tr>
                    <?php elseif ($isLegend): ?>
                        <tr>
                            <td colspan="7" style="padding:6px 12px;background:var(--surface-2);border-bottom:1px solid var(--border)">
                                <span style="font-size:11px;font-weight:700;color:var(--amber);text-transform:uppercase;letter-spacing:.05em"><?php echo htmlspecialchars($nome); ?></span>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php
                        $itemIndex++;
                        if ($prevCat !== null && $cat !== $prevCat):
                        ?>
                        <tr class="cat-subtotal-row">
                            <td colspan="6" style="text-align:right;font-size:11px;color:var(--brand)">SUBTOTAL <?php echo htmlspecialchars($prevCat); ?></td>
                            <td class="r" style="font-size:12px">R$ <?php echo number_format($catSubtotal, 2, ',', '.'); ?></td>
                        </tr>
                    <?php $catSubtotal = 0; endif; ?>
                        <?php if ($cat && $cat !== $prevCat): ?>
                        <tr class="cat-header-row">
                            <td colspan="7">
                                <span class="cat-badge" style="background:<?php echo $cor[0]; ?>;border:1px solid <?php echo $cor[1]; ?>;color:<?php echo $cor[2]; ?>">
                                    <?php echo htmlspecialchars($cat); ?>
                                </span>
                            </td>
                        </tr>
                        <?php $prevCat = $cat; endif; ?>
                        <tr>
                            <td><span class="item-num"><?php echo str_pad($idx + 1, 2, '0', STR_PAD_LEFT); ?></span></td>
                            <td>
                                <div class="item-name">
                                    <?php echo htmlspecialchars($nome); ?>
                                    <?php if ($disc > 0): ?>
                                    <span class="disc-chip">-<?php echo number_format($disc, 0); ?>%</span>
                                    <?php endif; ?>
                                </div>
                                <?php if ($desc && $desc !== $nome): ?><div class="item-desc"><?php echo htmlspecialchars($desc); ?></div><?php endif; ?>
                                <?php if (!empty($item['unidade_medida'])): ?><div class="item-desc" style="margin-top:2px">Unidade: <?php echo htmlspecialchars($item['unidade_medida']); ?></div><?php endif; ?>
                            </td>
                            <td style="text-align:center;font-size:11px;color:var(--muted)">
                                <?php echo htmlspecialchars($item['unidade'] ?? $item['unidade_medida'] ?? 'un'); ?>
                            </td>
                            <td class="r">
                                <?php echo number_format($qtd, 2, ',', '.'); ?>
                            </td>
                            <td class="r" style="font-weight:400;color:var(--muted)">R$ <?php echo number_format($vunit, 2, ',', '.'); ?></td>
                            <td class="r" style="color:<?php echo $disc > 0 ? '#059669' : 'var(--muted)' ?>"><?php echo $disc > 0 ? '-' . number_format($disc, 0) . '%' : '&mdash;'; ?></td>
                            <td class="r">R$ <?php echo number_format($subtot, 2, ',', '.'); ?></td>
                        </tr>
                        <?php $catSubtotal += $subtot; ?>
                        <?php if ($itemIndex === $totalRegular && $prevCat !== null): ?>
                        <tr class="cat-subtotal-row">
                            <td colspan="6" style="text-align:right;font-size:11px;color:var(--muted)">SUBTOTAL <?php echo htmlspecialchars($prevCat); ?></td>
                            <td class="r" style="font-size:12px">R$ <?php echo number_format($catSubtotal, 2, ',', '.'); ?></td>
                        </tr>
                        <?php endif; ?>
                    <?php endif; ?>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php if (!empty($subtotaisCat)): ?>
        <div class="cat-summary-grid">
            <?php foreach ($subtotaisCat as $cat => $val):
                $cor = $catCores[$cat] ?? $catCores['Outros'];
                $cnt = count(array_filter($itens, fn($i)=>($i['categoria']??'Outros')===$cat));
            ?>
            <div class="cat-summary-card" style="background:<?php echo $cor[0]; ?>;border-color:<?php echo $cor[1]; ?>">
                <div class="cscat" style="color:<?php echo $cor[2]; ?>"><?php echo htmlspecialchars($cat); ?></div>
                <div class="cscount" style="color:<?php echo $cor[2]; ?>;opacity:.75"><?php echo $cnt; ?> item(ns)</div>
                <div class="csval" style="color:<?php echo $cor[2]; ?>"><?php echo ReportHelper::formatCurrency($val); ?></div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="fin-wrap">
            <div class="fin-box">
                <div class="fin-row">
                    <span>Subtotal</span>
                    <span>R$ <?php echo number_format($subTotal, 2, ',', '.'); ?></span>
                </div>
                <?php if ($descValor > 0): ?>
                <div class="fin-row discount">
                    <span><i class="fas fa-tag" style="margin-right:4px;font-size:10px"></i>Desconto<?php if ($descTipo === 'percentual' && $descPerc > 0): ?> (<?php echo number_format($descPerc, 2, ',', '.'); ?>%)<?php endif; ?></span>
                    <span>&minus; R$ <?php echo number_format($descValor, 2, ',', '.'); ?></span>
                </div>
                <?php endif; ?>
                <?php if ($taxValor > 0): ?>
                <div class="fin-row">
                    <span style="color:var(--muted)">Impostos / Taxas<?php if ($taxPerc > 0): ?> (<?php echo number_format($taxPerc, 2, ',', '.'); ?>%)<?php endif; ?></span>
                    <span>+ R$ <?php echo number_format($taxValor, 2, ',', '.'); ?></span>
                </div>
                <?php endif; ?>
                <div class="fin-row grand">
                    <span>Total Geral</span>
                    <span class="fin-total-val"><?php echo ReportHelper::formatCurrency($totalFinal); ?></span>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- 5. CRONOGRAMA -->
    <?php if ($crono): ?>
    <div class="section anim d4">
        <div class="sec-title">
            <div class="sec-num">5</div>
            <span>Cronograma de Execu&ccedil;&atilde;o</span>
            <?php if ($cronoDuracao): ?><span class="sec-count"><?php echo $cronoDuracao; ?></span><?php endif; ?>
        </div>
        <?php if (!empty($cronoTextoIntro)): ?>
        <div style="font-size:12px;color:var(--muted);padding:12px 18px 0;line-height:1.5"><?= nl2br(htmlspecialchars($cronoTextoIntro)) ?></div>
        <?php endif; ?>
        <div class="sec-body">
            <?php if (!empty($crono['totalPeriods'])): ?>
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:8px;margin-bottom:14px">
                <div style="background:var(--surface-2);border:1px solid var(--border);border-radius:8px;padding:10px 14px;text-align:center">
                    <div style="font-size:22px;font-weight:800;color:var(--brand);line-height:1;margin-bottom:4px"><?php echo $crono['totalPeriods']; ?></div>
                    <div style="font-size:11px;color:var(--muted);font-weight:500"><?php echo match($crono['mode'] ?? 'dias') { 'semanas' => 'Semanas', 'meses' => 'Meses', default => 'Dias' }; ?> de Dura&ccedil;&atilde;o</div>
                </div>
                <?php if (!empty($crono['tarefas'])): ?>
                <div style="background:var(--surface-2);border:1px solid var(--border);border-radius:8px;padding:10px 14px;text-align:center">
                    <div style="font-size:22px;font-weight:800;color:var(--brand);line-height:1;margin-bottom:4px"><?php echo count($crono['tarefas']); ?></div>
                    <div style="font-size:11px;color:var(--muted);font-weight:500">Atividades Previstas</div>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            <?php if (!empty($crono['tarefas'])): ?>
            <div style="overflow-x:auto">
                <table style="width:100%;border-collapse:collapse;font-size:12px;min-width:400px">
                    <thead>
                        <tr style="background:var(--surface-2);border-bottom:1px solid var(--border)">
                            <th style="padding:6px 12px;text-align:left;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--muted)">Atividade</th>
                            <th style="padding:6px 12px;text-align:center;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--muted)">Per&iacute;odo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($crono['tarefas'] as $t): ?>
                        <tr style="border-bottom:1px solid var(--border)">
                            <td style="padding:6px 12px;color:var(--text);font-weight:500"><?php echo htmlspecialchars($t['nome'] ?? $t['tarefa'] ?? ''); ?></td>
                            <td style="padding:6px 12px;text-align:center;font-size:11px;color:var(--muted)">
                                <?php echo isset($t['inicio'], $t['fim']) ? "{$t['inicio']} &ndash; {$t['fim']}" : '&mdash;'; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php if (!empty($cronoTextoFooter)): ?>
            <div style="font-size:11px;color:var(--muted);margin-top:8px;line-height:1.5;font-style:italic"><?= nl2br(htmlspecialchars($cronoTextoFooter)) ?></div>
            <?php endif; ?>
            <?php else: ?>
            <p style="font-size:13px;color:var(--muted)">Cronograma com <?php echo $cronoDuracao; ?> de execu&ccedil;&atilde;o prevista. Detalhamento dispon&iacute;vel no PDF da proposta.</p>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- 6. CONDIÇÕES COMERCIAIS -->
    <?php if ($formaPagamento || $condicaoPagamento || $prazoExecucao || $garantias || $validadeDias): ?>
    <div class="section anim d4">
        <div class="sec-title">
            <div class="sec-num">6</div>
            <span>Condi&ccedil;&otilde;es Comerciais</span>
        </div>
        <div class="sec-body">
            <div class="cond-grid">
                <?php if ($condicaoPagamento || $formaPagamento): ?>
                <div class="cond-card">
                    <div class="cond-icon-wrap"><i class="fas fa-receipt"></i></div>
                    <div class="cond-label">Condi&ccedil;&atilde;o de Pagamento</div>
                    <div class="cond-value"><?php echo htmlspecialchars($condicaoPagamento ?: $formaPagamento); ?></div>
                    <?php if ($condicaoPagamento && $formaPagamento && $formaPagamento !== $condicaoPagamento): ?>
                    <div class="cond-sub"><?php echo htmlspecialchars($formaPagamento); ?></div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                <?php if ($prazoExecucao): ?>
                <div class="cond-card">
                    <div class="cond-icon-wrap"><i class="fas fa-calendar-check"></i></div>
                    <div class="cond-label">Prazo de Execu&ccedil;&atilde;o</div>
                    <div class="cond-value"><?php echo htmlspecialchars($prazoExecucao); ?></div>
                </div>
                <?php endif; ?>
                <div class="cond-card">
                    <div class="cond-icon-wrap"><i class="fas fa-hourglass-half"></i></div>
                    <div class="cond-label">Validade da Proposta</div>
                    <div class="cond-value" style="color:var(--brand)"><?php echo $validadeDias; ?> dias</div>
                    <?php if ($validadeToken): ?><div class="cond-sub">Expira em <?php echo $validadeToken; ?></div><?php endif; ?>
                </div>
                <?php if ($garantias): ?>
                <div class="cond-card" style="grid-column:1/-1">
                    <div class="cond-icon-wrap"><i class="fas fa-shield-alt"></i></div>
                    <div class="cond-label">Garantias</div>
                    <div class="cond-value" style="font-weight:400;line-height:1.65"><?php echo nl2br(htmlspecialchars($garantias)); ?></div>
                </div>
                <?php endif; ?>
            </div>
            <?php if (($formaPagamento === 'Pix' && $pixChave) || ($formaPagamento === 'Transfer&ecirc;ncia Banc&aacute;ria' && $dadosBancarios)): ?>
            <?php if ($formaPagamento === 'Pix' && $pixChave): ?>
            <div class="payment-detail" style="margin-top:14px">
                <span style="color:var(--brand);font-size:1.1rem"><i class="fas fa-qrcode"></i></span>
                <div>
                    <strong>Chave PIX</strong>
                    <?php if ($pixTipoChave): ?><span style="font-size:11px;color:var(--muted);margin-left:4px">(<?php echo htmlspecialchars($pixTipoChave); ?>)</span><?php endif; ?>
                    <br><?php echo htmlspecialchars($pixChave); ?>
                </div>
            </div>
            <?php elseif ($dadosBancarios): ?>
            <div class="payment-detail" style="margin-top:14px">
                <span style="color:var(--brand);font-size:1.1rem"><i class="fas fa-university"></i></span>
                <div style="white-space:pre-line"><?php echo nl2br(htmlspecialchars($dadosBancarios)); ?></div>
            </div>
            <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- 7. OBSERVAÇÕES -->
    <?php if ($observacoes && strtoupper($observacoes) !== 'N/A'): ?>
    <div class="section anim d5">
        <div class="sec-header">
            <div class="sec-num">7</div>
            <span>Observa&ccedil;&otilde;es</span>
        </div>
        <div class="sec-body">
            <div class="obs-box"><?php echo nl2br(htmlspecialchars($observacoes)); ?></div>
        </div>
    </div>
    <?php endif; ?>

    <div class="page-footer anim d6">
        Documento gerado em <?php echo date('d/m/Y \à\s H:i'); ?>
        &nbsp;&middot;&nbsp;
        <strong><?php echo htmlspecialchars($codigo); ?></strong>
        &nbsp;&middot;&nbsp;
        <?php echo htmlspecialchars($titulo); ?>
    </div>

</div>

<!-- Floating Action Bar -->
<?php if ($isPending): ?>
<div class="fab-bar" role="toolbar" aria-label="A&ccedil;&otilde;es da proposta">
    <div class="fab-inner">
        <div class="fab-total">
            <i class="fas fa-file-invoice-dollar" style="color:var(--muted)"></i>
            Valor total:
            <strong><?php echo ReportHelper::formatCurrency($totalFinal); ?></strong>
        </div>
        <?php if ($validadeToken): ?>
        <div class="fab-divider" aria-hidden="true"></div>
        <div class="fab-validity">V&aacute;lida at&eacute; <strong><?php echo $validadeToken; ?></strong></div>
        <?php endif; ?>
        <div class="fab-divider" aria-hidden="true"></div>
        <div class="fab-actions">
            <button class="btn btn-icon" onclick="window.print()" title="Imprimir / Salvar PDF" aria-label="Imprimir proposta">
                <i class="fas fa-print" aria-hidden="true"></i>
            </button>
            <button class="btn btn-reject" onclick="openModal('reject')">
                <i class="fas fa-times-circle" aria-hidden="true"></i>
                Rejeitar
            </button>
            <button class="btn btn-approve" onclick="openModal('approve')">
                <i class="fas fa-check-circle" aria-hidden="true"></i>
                Aprovar Proposta
            </button>
        </div>
    </div>
</div>

<!-- MODAL APROVAR -->
<div class="modal-overlay" id="modal-approve" onclick="bgClose(this,event)" role="dialog" aria-modal="true" aria-labelledby="modal-approve-title">
    <div class="modal-box">
        <div class="modal-icon approve"><i class="fas fa-check-circle" aria-hidden="true"></i></div>
        <h3 id="modal-approve-title">Confirmar Aprova&ccedil;&atilde;o</h3>
        <p>
            Voc&ecirc; est&aacute; aprovando a proposta <strong>#<?php echo htmlspecialchars($numProposta); ?></strong>
            no valor de <strong><?php echo ReportHelper::formatCurrency($totalFinal); ?></strong>.
            <br>Ap&oacute;s a confirma&ccedil;&atilde;o, essa decis&atilde;o n&atilde;o poder&aacute; ser revertida.
        </p>
        <form action="<?php echo BASE_URL; ?>/orcamento/aprovarPropostaPublica/<?php echo htmlspecialchars($token); ?>" method="POST" id="form-approve">
            <input type="hidden" name="acao" value="aprovar">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token ?? ''); ?>">
            <label class="modal-label" for="aceite_nome">Seu nome completo</label>
            <input type="text" name="aceite_nome" id="aceite_nome"
                class="modal-input"
                placeholder="Ex: Jo&atilde;o da Silva"
                autocomplete="name"
                required>
            <label class="modal-label" for="msg-aprovacao">Mensagem para a equipe <span style="font-weight:400;color:var(--muted)">(opcional)</span></label>
            <textarea name="motivo" id="msg-aprovacao" class="modal-textarea" placeholder="Ex: Podem iniciar. Vamos alinhar reuni&atilde;o na segunda."></textarea>
            <div class="aceite-wrap" onclick="if(event.target.tagName !== 'INPUT' && event.target.tagName !== 'LABEL') document.getElementById('chk-aceite').click()">
                <input type="checkbox" id="chk-aceite" name="aceite_confirmado" value="1" required>
                <label for="chk-aceite" onclick="event.stopPropagation()">
                    Declaro que li e compreendi todos os termos desta proposta e manifesto meu aceite eletr&ocirc;nico, ciente de que esta a&ccedil;&atilde;o ter&aacute; validade como confirma&ccedil;&atilde;o formal.
                </label>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" onclick="closeModal('approve')">Cancelar</button>
                <button type="submit" class="btn btn-approve">
                    <i class="fas fa-check" aria-hidden="true"></i> Confirmar Aprova&ccedil;&atilde;o
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL REJEITAR -->
<div class="modal-overlay" id="modal-reject" onclick="bgClose(this,event)" role="dialog" aria-modal="true" aria-labelledby="modal-reject-title">
    <div class="modal-box">
        <div class="modal-icon reject"><i class="fas fa-times-circle" aria-hidden="true"></i></div>
        <h3 id="modal-reject-title">Rejeitar Proposta</h3>
        <p>Informe o motivo para que possamos apresentar uma proposta revisada com mais adequa&ccedil;&atilde;o &agrave;s suas necessidades.</p>
        <form action="<?php echo BASE_URL; ?>/orcamento/aprovarPropostaPublica/<?php echo htmlspecialchars($token); ?>" method="POST">
            <input type="hidden" name="acao" value="rejeitar">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token ?? ''); ?>">
            <label class="modal-label" for="msg-rejeicao">Motivo da Rejei&ccedil;&atilde;o <span style="font-weight:400;color:var(--muted)">(opcional)</span></label>
            <textarea name="motivo" id="msg-rejeicao" class="modal-textarea" placeholder="Ex: Valor fora do or&ccedil;amento previsto, prazo incompat&iacute;vel."></textarea>
            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" onclick="closeModal('reject')">Cancelar</button>
                <button type="submit" class="btn" style="background:#DC2626;color:#fff;box-shadow:0 4px 14px rgba(220,38,38,.3)">
                    <i class="fas fa-times" aria-hidden="true"></i> Confirmar Rejei&ccedil;&atilde;o
                </button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<?php endif; // isset($proposta) ?>

<script>
function openModal(type) {
    document.getElementById('modal-' + type).classList.add('open');
    document.body.style.overflow = 'hidden';
}
function closeModal(type) {
    document.getElementById('modal-' + type).classList.remove('open');
    document.body.style.overflow = '';
}
function bgClose(overlay, event) {
    if (event.target === overlay) {
        overlay.classList.remove('open');
        document.body.style.overflow = '';
    }
}
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal-overlay.open').forEach(m => m.classList.remove('open'));
        document.body.style.overflow = '';
    }
});
function toggleTheme() {
    const isDark = document.documentElement.classList.toggle('dark');
    localStorage.setItem('theme', isDark ? 'dark' : 'light');
    document.getElementById('theme-icon').className = isDark ? 'fas fa-sun' : 'fas fa-moon';
}
(function() {
    const isDark = document.documentElement.classList.contains('dark');
    const icon = document.getElementById('theme-icon');
    if (icon) icon.className = isDark ? 'fas fa-sun' : 'fas fa-moon';
})();
</script>
</body>
</html>