<?php
/**
 * views/orcamento/formulario-orcamento.php
 *
 * Formulário completo de Proposta Técnica Orçamentária
 * Seções: Identificação · Cliente · Itens · Financeiro · Revisão
 *
 * Variáveis esperadas do controller:
 *   $orc          array|null   — dados do orçamento (null = novo)
 *   $clientes     array        — [{id, nome, sigla, email?}]
 *   $usuarios     array        — [{id, nome}]
 *   $projetos     array        — [{id, nome}]
 *   $contratos    array        — [{id, numero_contrato, titulo, cliente_id, vencimento, valor}]
 *   $condicoes    array        — [{id, descricao}]
 *   $csrf_token   string
 *   $erros        array|null   — erros de validação
 *   $isEdicao     bool         — (opcional, inferido de $orc['id'])
 */

// ─── Tema (claro/escuro) ───────────────────────────────────────────────────────
$themePref = $_COOKIE['theme'] ?? 'light';
$isDark = ($themePref === 'dark');

// ─── Setup inicial ────────────────────────────────────────────────────────────
$isEdicao = isset($isEdicao) ? (bool)$isEdicao : !empty($orc['id']);
$isAjax   = (isset($_GET['ajax']) && $_GET['ajax'] == 1);
$titulo   = $isEdicao
    ? 'Editar Proposta ' . htmlspecialchars($orc['numero'] ?? $orc['codigo'] ?? '')
    : 'Nova Proposta Técnica Orçamentária';

$itens = $orc['itens'] ?? [];

// Defaults para campos principais
$def = [
    'titulo_proposta'    => $orc['titulo']            ?? $orc['nome_proposta']   ?? '',
    'codigo'             => $orc['codigo']             ?? $orc['numero']          ?? '',
    'data_proposta'      => $orc['data_proposta']      ?? date('Y-m-d'),
    'responsavel_id'     => $orc['responsavel_interno_id'] ?? '',
    'validade'           => $orc['validade_proposta']  ?? $orc['validade_dias']   ?? 30,
    'versao'             => $orc['versao_documento']   ?? 'PO.REV.02.26-COM-001-26-EC',
    'escopo'             => $orc['descricao_geral']    ?? $orc['escopo']          ?? '',
    'observacoes'        => $orc['observacoes']        ?? '',
    'cliente_id'         => $orc['cliente_id']         ?? '',
    'representante'      => $orc['representante']      ?? '',
    'email_cliente'      => $orc['email_cliente']      ?? '',
    'municipio'          => $orc['municipio']          ?? '',
    'area'               => $orc['area']               ?? '',
    'cliente_logradouro' => $orc['cliente_logradouro'] ?? '',
    'cliente_numero'     => $orc['cliente_numero']     ?? '',
    'cliente_complemento'=> $orc['cliente_complemento']?? '',
    'cliente_endereco'   => $orc['cliente_endereco']   ?? $orc['endereco_cliente'] ?? '',
    'cliente_bairro'     => $orc['cliente_bairro']     ?? '',
    'cliente_municipio'  => $orc['cliente_municipio']  ?? '',
    'cliente_uf'         => $orc['cliente_uf']         ?? '',
    'projeto_id'         => $orc['projeto_id']         ?? '',
    'contrato_id'        => $orc['contrato_id']        ?? '',
    'cliente_documento'  => $orc['cliente_documento']  ?? '',
    'cliente_telefone'   => $orc['cliente_telefone']   ?? '',
    'desconto_tipo'      => $orc['desconto_tipo']      ?? 'percentual',
    'desconto_valor'     => $orc['desconto_valor']     ?? 0,
    'impostos_perc'      => $orc['impostos_perc']      ?? 0,
    'condicao'           => $orc['condicao_pagamento'] ?? '',
    'forma'              => $orc['forma_pagamento']    ?? '',
    'prazo'              => $orc['prazo_execucao']     ?? $orc['prazo_entrega']   ?? '',
    'latitude'           => $orc['latitude']           ?? '',
    'longitude'          => $orc['longitude']          ?? '',
    'pix_tipo_chave'     => $orc['pix_tipo_chave']     ?? '',
    'pix_chave'          => $orc['pix_chave']          ?? '',
    'dados_bancarios'    => $orc['dados_bancarios']    ?? '',
    'banco_id'           => $orc['banco_id']           ?? '',

    // Assinatura (Contratada)
    'assinatura_tipo'               => $orc['assinatura_tipo'] ?? 'imagem',
    'assinatura_imagem'             => $orc['assinatura_imagem'] ?? null,
    'assinatura_certificado_nome'   => $orc['assinatura_certificado_nome'] ?? '',
    'assinatura_certificado_cpf'    => $orc['assinatura_certificado_cpf'] ?? '',
    'assinatura_certificado_path'   => $orc['assinatura_certificado_path'] ?? '',
    'assinatura_certificado_validade' => $orc['assinatura_certificado_validade'] ?? '',

    // Assinatura do Elaborador
    'assinatura_elaborador_responsavel' => $orc['assinatura_elaborador_responsavel'] ?? 0,
    'assinatura_elaborador_tipo'        => $orc['assinatura_elaborador_tipo'] ?? 'imagem',
    'assinatura_elaborador_imagem'      => $orc['assinatura_elaborador_imagem'] ?? null,
    'assinatura_elaborador_certificado_nome' => $orc['assinatura_elaborador_certificado_nome'] ?? '',
    'assinatura_elaborador_certificado_cpf'  => $orc['assinatura_elaborador_certificado_cpf'] ?? '',
    'assinatura_elaborador_certificado_path' => $orc['assinatura_elaborador_certificado_path'] ?? '',
    'assinatura_elaborador_certificado_validade' => $orc['assinatura_elaborador_certificado_validade'] ?? '',
];

// Categorias carregadas do banco ou padrões fixos
$categorias = (!empty($categorias)) ? $categorias : [
    'Planejamento / Coordenação',
    'Serviços de Campo',
    'Custos Reembolsáveis',
    'Elaboração de Peças Técnicas',
    'Outros',
];

// Unidades carregadas do banco ou padrões fixos
$unidades = (!empty($unidades)) ? $unidades : ['H/D', 'UN', 'Ticket', 'Diária', 'Litros', 'Peça', 'M²', 'KM', 'HR'];

$formasPagamento = [
    'Pix', 'Transferência Bancária', 'Boleto', 'Cartão de Crédito',
    'Cartão de Débito', 'Depósito', 'Cheque',
];

$condicoesPagamento = [
    '30 dias após aprovação',
    '50% na aprovação / 50% na entrega',
    'À vista',
    '100% após a conclusão',
    'Parcelado (negociar)',
    'Conforme contrato',
];

// Cores de badge por categoria (usadas no preview)
$categoriaCores = [
    'Planejamento / Coordenação'   => ['bg' => '#dbeafe', 'text' => '#1d4ed8', 'border' => '#93c5fd'],
    'Serviços de Campo'            => ['bg' => '#EAF3DE', 'text' => '#27500A', 'border' => '#C0DD97'],
    'Custos Reembolsáveis'         => ['bg' => '#FAEEDA', 'text' => '#633806', 'border' => '#FAC775'],
    'Elaboração de Peças Técnicas' => ['bg' => '#EEEDFE', 'text' => '#3C3489', 'border' => '#CECBF6'],
    'Outros'                       => ['bg' => '#F1EFE8', 'text' => '#444441', 'border' => '#D3D1C7'],
];

/**
 * Helper: badge HTML inline
 */
function badgeStyle(array $cor, string $label): string {
    return sprintf(
        '<span style="display:inline-block;font-size:7px;font-weight:800;padding:0.5px 5px;border-radius:999px;'
        . 'border:0.3px solid %s;background:%s;color:%s;letter-spacing:.02em;text-transform:uppercase">%s</span>',
        htmlspecialchars($cor['border']),
        htmlspecialchars($cor['bg']),
        htmlspecialchars($cor['text']),
        htmlspecialchars($label)
    );
}
?>
<!DOCTYPE html>
<!--
    NOTA: Se este arquivo for incluído como partial (sem cabeçalho HTML),
    remova as tags DOCTYPE / html / head / body abaixo e inclua o CSS
    via sua view-layout principal.
-->
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($titulo) ?></title>

    <!-- Tailwind CSS local (build de produção) -->
    <link href="<?= BASE_URL ?>/css/output.css" rel="stylesheet">
    <!-- Estilos Globais Customizados (variáveis CSS, temas) -->
    <link href="<?= BASE_URL ?>/css/global.css" rel="stylesheet">
    <!-- Estilos específicos do orçamento -->
    <link href="<?= BASE_URL ?>/assets/css/orcamento.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        /* ── Variáveis de cor ── */
        :root {
            --brand:          #2563eb;
            --brand-light:    #dbeafe;
            --brand-border:   #93c5fd;
            --brand-dark:     #1d4ed8;
            --success:        #3B6D11;
            --success-light:  #EAF3DE;
            --success-border: #C0DD97;
            --danger:         #A32D2D;
        }

        /* ── Stepper ── */
        .step-btn { transition: background .15s, color .15s; }
        .step-btn.active  { background: var(--brand-light); }
        .step-btn.done    { background: var(--success-light); }
        .step-btn:not(.active):not(.done) { background: #F9FAFB; }
        .step-btn .step-icon.active  { color: var(--brand); }
        .step-btn .step-icon.done    { color: var(--success); }
        .step-btn .step-label.active { color: var(--brand-dark); }
        .step-btn .step-label.done   { color: #27500A; }

        /* ── Cards ── */
        .card {
            background: #fff;
            border: 0.5px solid #E5E7EB;
            border-radius: 12px;
        }
        .card-header {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 14px 20px;
            border-bottom: 0.5px solid #E5E7EB;
            background: #F9FAFB;
        }
        .card-header i { font-size: 15px; color: var(--brand); }
        .card-header .title {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: #374151;
            margin: 0;
        }
        .card-header .subtitle {
            font-size: 11px;
            color: #6B7280;
            margin: 0;
        }

        /* ── Form labels ── */
        .lbl {
            display: block;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: #6B7280;
            margin-bottom: 4px;
        }
        .lbl .req { color: #E24B4A; margin-left: 3px; }

        /* ── Inputs ── */
        .fi {
            width: 100%;
            box-sizing: border-box;
            padding: 7px 10px;
            border: 1px solid #D1D5DB;
            border-radius: 8px;
            font-size: 13px;
            outline: none;
            transition: border-color .15s, box-shadow .15s;
            background: #fff;
        }
        .fi:focus { border-color: var(--brand); box-shadow: 0 0 0 3px rgba(37,99,235,.12); }
        .fi[readonly] { background: #F9FAFB; color: #9CA3AF; cursor: not-allowed; }

        /* ── Tabela de itens ── */
        .tabela-itens th {
            background: #F9FAFB;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: #6B7280;
            padding: 10px 10px;
            white-space: nowrap;
        }
        .tabela-itens td { padding: 8px 10px; vertical-align: middle; }
        .tabela-itens tbody tr:not(:last-child) td { border-bottom: 0.5px solid #F3F4F6; }
        .tabela-itens tbody tr:hover td { background: #F9FAFB; }
        .item-total-cell { text-align: right; font-weight: 600; font-size: 13px; white-space: nowrap; }

        /* ── Resumo financeiro ── */
        .resumo-linha { display: flex; justify-content: space-between; font-size: 13px; margin-bottom: 4px; }
        .resumo-total {
            display: flex; justify-content: space-between; align-items: center;
            padding: 14px 16px;
            background: var(--brand-light);
            border-radius: 8px;
            border: 0.5px solid var(--brand-border);
            margin-top: 8px;
        }

        /* ── Preview / Revisão ── */
        .preview-header {
            padding: 20px 24px;
            border-bottom: 3px solid var(--brand);
        }
        .preview-badge {
            display: inline-block;
            font-size: 6.5px;
            font-weight: 800;
            padding: 0.5px 5px;
            border-radius: 999px;
            letter-spacing: .04em;
            text-transform: uppercase;
        }
        .preview-field-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
        }
        .preview-field {
            padding: 12px 20px;
        }
        .preview-field .pf-label {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: #9CA3AF;
            font-weight: 600;
            margin: 0 0 2px;
        }
        .preview-field .pf-value {
            font-size: 13px;
            font-weight: 600;
            color: #111827;
            margin: 0;
        }

        /* ── Botões de navegação ── */
        .btn-nav {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 8px 18px;
            border: 1px solid #D1D5DB;
            border-radius: 8px;
            background: #fff;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            transition: background .15s;
        }
        .btn-nav:hover { background: #F3F4F6; }
        .btn-primary {
            background: var(--brand);
            color: #fff;
            border-color: var(--brand);
        }
        .btn-primary:hover { background: #1450870; filter: brightness(1.08); }

        /* ── Steps content ── */
        .step-content { display: none; flex-direction: column; gap: 16px; }
        .step-content.active { display: flex; }

        /* ── Sticky sidebar ── */
        @media (min-width: 1024px) {
            .sticky-sidebar { position: sticky; top: 24px; }
        }

        /* ── Sucesso ── */
        .success-screen { padding: 3rem 2rem; text-align: center; }
        .success-icon {
            width: 64px; height: 64px; border-radius: 50%;
            background: var(--success-light);
            border: 0.5px solid var(--success-border);
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 1.5rem;
        }

        /* ══════════════════════════════════════════════════════════════
           DARK MODE — sobrescreve classes custom do formulário
           ══════════════════════════════════════════════════════════════ */
        body.dark-theme { background: #0f172a; color: #e2e8f0; }

        body.dark-theme .card {
            background: var(--db-surface, #1e293b);
            border-color: var(--db-border, #334155);
        }
        body.dark-theme .card-header {
            background: var(--db-surface2, #0f172a);
            border-bottom-color: var(--db-border, #334155);
        }
        body.dark-theme .card-header .title { color: var(--db-text, #f1f5f9); }
        body.dark-theme .card-header .subtitle { color: var(--db-text2, #94a3b8); }

        body.dark-theme .fi {
            background: var(--db-surface2, #0f172a);
            border-color: var(--db-border, #334155);
            color: var(--db-text, #e2e8f0);
        }
        body.dark-theme .fi:focus { border-color: var(--brand); box-shadow: 0 0 0 3px rgba(37,99,235,.25); }
        body.dark-theme .fi[readonly] { background: #1e293b; color: #64748b; }

        body.dark-theme .lbl { color: #94a3b8; }

        body.dark-theme .step-btn:not(.active):not(.done) { background: #1e293b; }
        body.dark-theme .step-btn .step-icon { color: #64748b; }
        body.dark-theme .step-btn .step-label { color: #64748b; }
        body.dark-theme .step-btn.active .step-icon { color: var(--brand); }
        body.dark-theme .step-btn.active .step-label { color: var(--brand-dark); }
        body.dark-theme .step-btn.done .step-icon { color: var(--success); }
        body.dark-theme .step-btn.done .step-label { color: #4ade80; }

        body.dark-theme #stepper { border-color: var(--db-border, #334155); }
        body.dark-theme #stepper .border-r { border-color: var(--db-border, #334155); }

        body.dark-theme .tabela-itens th {
            background: #1e293b;
            color: #94a3b8;
            border-bottom-color: var(--db-border, #334155);
        }
        body.dark-theme .tabela-itens td { border-bottom-color: #334155; }
        body.dark-theme .tabela-itens tbody tr:not(:last-child) td { border-bottom-color: #334155; }
        body.dark-theme .tabela-itens tbody tr:hover td { background: #1a2332; }
        body.dark-theme .tabela-itens tbody tr:hover .btn-remover-item { color: #64748b; }
        body.dark-theme .tabela-itens .btn-remover-item { color: #475569; }
        body.dark-theme .item-total-cell { color: #e2e8f0; }

        body.dark-theme .btn-nav {
            background: #1e293b;
            border-color: var(--db-border, #334155);
            color: #e2e8f0;
        }
        body.dark-theme .btn-nav:hover { background: #334155; }
        body.dark-theme .btn-primary {
            background: var(--brand);
            color: #fff;
            border-color: var(--brand);
        }
        body.dark-theme .btn-primary:hover { background: #145087; }

        body.dark-theme .resumo-total {
            background: rgba(37,99,235,.15);
            border-color: rgba(37,99,235,.3);
        }

        body.dark-theme .preview-field .pf-label { color: #64748b; }
        body.dark-theme .preview-field .pf-value { color: #e2e8f0; }

        body.dark-theme .preview-header { border-bottom-color: var(--brand); }
        body.dark-theme #prev-titulo { color: #e2e8f0; }
        body.dark-theme #prev-header-codigo { background: rgba(37,99,235,.2); }

        body.dark-theme #totais-por-categoria > div > span:last-child { color: #e2e8f0; }
        body.dark-theme #display-subtotal { color: #e2e8f0; }

        body.dark-theme #fin-categorias > div {
            background: #1e293b !important;
            border-color: #334155 !important;
        }
        body.dark-theme #fin-categorias > div span { color: #e2e8f0; }
        body.dark-theme #fin-categorias > div span.text-gray-400,
        body.dark-theme #fin-categorias > div span.text-gray-500 { color: #94a3b8 !important; }

        body.dark-theme #prev-composicao > div {
            background: #1e293b !important;
            border-color: #334155 !important;
        }
        body.dark-theme #prev-composicao > div span { color: #e2e8f0; }

        body.dark-theme .preview-field-grid { border-bottom-color: #334155; }
        body.dark-theme .preview-field { border-color: #334155; }

        body.dark-theme .crono-table td.col-ativ { background: #1e293b; }
        body.dark-theme .crono-table td.col-ativ input { color: #e2e8f0; }
        body.dark-theme .crono-table tbody tr:hover td { background: #1a2332; }
        body.dark-theme .crono-table tbody tr:hover td.col-ativ { background: #1a2332; }
        body.dark-theme .crono-table th { background: #1e293b; color: #94a3b8; border-bottom-color: #334155; }
        body.dark-theme .crono-table td { border-bottom-color: #334155; }
        body.dark-theme .crono-dot { border-color: #475569; background: #1e293b; }
        body.dark-theme .crono-sum-card { background: #1e293b; border-color: #334155; }
        body.dark-theme .crono-sum-label { color: #64748b; }
        body.dark-theme .crono-sum-value { color: #e2e8f0; }
        body.dark-theme .crono-day-num { color: #e2e8f0; }
        body.dark-theme .crono-day-sub { color: #64748b; }
        body.dark-theme .crono-mode-switcher { background: #1e293b; }
        body.dark-theme .crono-mode-btn.active { background: #0f172a; color: #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,.3); }
        body.dark-theme .crono-mode-btn { color: #64748b; }

        /* ── Metadados: Data · Validade · Elaborado por ── */
        .idf-meta-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
        }
        .idf-expiry-chip {
            display: block;
            margin-top: 6px;
            font-size: 11px;
            font-weight: 600;
            color: #6B7280;
            font-family: ui-monospace, SFMono-Regular, "IBM Plex Mono", Menlo, Consolas, monospace;
        }
        .idf-expiry-chip strong { color: var(--brand-dark); font-weight: 700; letter-spacing: .01em; }

        body.dark-theme .idf-expiry-chip { color: #94a3b8; }
        body.dark-theme .idf-expiry-chip strong { color: #60a5fa; }
        body.dark-theme .crono-config-bar { background: #0f172a; border-bottom-color: #334155; }
        body.dark-theme .crono-legend { background: #0f172a; border-top-color: #334155; }
        body.dark-theme .crono-legend-item { color: #94a3b8; }
        body.dark-theme .crono-tip { background: #3b2f1a; border-color: #5c4a2e; }
        body.dark-theme .crono-tip p { color: #d4b87a; }
        body.dark-theme .crono-tip i { color: #d4b87a; }
        body.dark-theme .crono-add-btn { background: #1e293b; color: #60a5fa; border-top-color: #334155; }
        body.dark-theme .crono-add-btn:hover { background: rgba(37,99,235,.15); }
body.dark-theme #btn-add-item { background: var(--brand); }

        body.dark-theme #btn-add-item:hover { background: #145087; }
        body.dark-theme #btn-add-legend { background: #1e293b; border-color: #334155; color: #60a5fa; }
        body.dark-theme #btn-add-legend:hover { background: #334155; }

        body.dark-theme #section-projeto #project-details-container { background: rgba(37,99,235,.12); border-color: rgba(37,99,235,.3); }
        body.dark-theme #section-contrato #section-contrato-detalhes { background: rgba(16,185,129,.12); border-color: rgba(16,185,129,.3); }

        body.dark-theme tr.bg-slate-100\/50 { background: rgba(30,41,59,.5) !important; }
        body.dark-theme tr.bg-slate-100\/50 .text-sky-700 { color: #60a5fa; }
        body.dark-theme tr.bg-slate-100\/50 .bg-sky-100 { background: rgba(37,99,235,.3); }
        body.dark-theme tr.bg-slate-100\/50 .border-sky-200 { border-color: rgba(37,99,235,.3); }
        body.dark-theme tr.bg-slate-100\/50 textarea { color: #e2e8f0; }

        body.dark-theme .border-t.border-gray-100 { border-top-color: #334155 !important; }
        body.dark-theme .border-b.border-gray-200 { border-bottom-color: #334155 !important; }
        body.dark-theme .border-b.border-gray-100 { border-bottom-color: #334155 !important; }
        body.dark-theme hr { border-color: #334155; }
        body.dark-theme .bg-gray-50 { background: #1a2332 !important; }
        body.dark-theme .bg-white { background: var(--db-surface, #1e293b) !important; }
        body.dark-theme .text-gray-400 { color: #94a3b8 !important; }
        body.dark-theme .text-gray-500 { color: #94a3b8 !important; }
        body.dark-theme .text-gray-600 { color: #94a3b8 !important; }
        body.dark-theme .text-gray-700 { color: #cbd5e1 !important; }
        body.dark-theme .text-gray-800 { color: #e2e8f0 !important; }
        body.dark-theme .text-gray-900 { color: #e2e8f0 !important; }
        body.dark-theme .text-slate-500 { color: #94a3b8 !important; }
        body.dark-theme .text-sky-700 { color: #60a5fa; }
        body.dark-theme .bg-sky-50 { background: rgba(37,99,235,.15) !important; }
        body.dark-theme .border-sky-200 { border-color: rgba(37,99,235,.3) !important; }
        body.dark-theme .bg-emerald-50 { background: rgba(16,185,129,.12) !important; }
        body.dark-theme .border-emerald-200 { border-color: rgba(16,185,129,.3) !important; }
        body.dark-theme .text-sky-600 { color: #60a5fa; }

        body.dark-theme .bg-slate-50 { background: #1e293b !important; }
        body.dark-theme .border-slate-200 { border-color: #334155 !important; }
        body.dark-theme .text-\[\#9CA3AF\] { color: #64748b; }

        body.dark-theme #btn-reset-codigo { background: #1e293b; border-color: #334155; color: #94a3b8; }
        body.dark-theme #btn-reset-codigo:hover { background: #334155; }

        body.dark-theme label[for="permitir-edicao-versao"] { background: #1e293b !important; border-color: #334155 !important; }
        body.dark-theme label[for="permitir-edicao-versao"] .text-gray-600 { color: #94a3b8 !important; }

        body.dark-theme table.tabela-itens thead tr.border-b { border-bottom-color: #334155; }
        body.dark-theme .text-gray-400 i { color: #64748b; }

        /* Dark mode: Título de seção e Texto descritivo */
        body.dark-theme tr[style*="rgba(238,237,254"] { background: rgba(60,52,137,.2) !important; }
        body.dark-theme tr[style*="rgba(230,241,251"] { background: rgba(37,99,235,.12) !important; }

        body.dark-theme #prev-desc-row .resumo-linha span { color: #94a3b8; }
        body.dark-theme #prev-imp-row .resumo-linha span { color: #94a3b8; }
        body.dark-theme #prev-escopo { color: #cbd5e1; }
        body.dark-theme #prev-observacoes { color: #cbd5e1; }
        body.dark-theme #prev-pagamento { color: #cbd5e1; }

        body.dark-theme .file\:bg-sky-50::file-selector-button { background: rgba(37,99,235,.25); color: #60a5fa; }
        body.dark-theme .file\:text-sky-700::file-selector-button { color: #60a5fa; }
        body.dark-theme .hover\:file\:bg-sky-100::file-selector-button:hover { background: rgba(37,99,235,.35); }

        body.dark-theme .drag-handle { color: #475569; }
        body.dark-theme .drag-handle:hover { color: #60a5fa; }

        body.dark-theme .crono-row-drag.bg-sky-50 { background: rgba(37,99,235,.15) !important; }

        body.dark-theme .bg-gray-100 { background: #1a2332 !important; }
        body.dark-theme .hover\:bg-gray-50:hover { background: #1e293b !important; }
        body.dark-theme .hover\:bg-gray-200:hover { background: #334155 !important; }
        body.dark-theme .bg-amber-50 { background: rgba(251,191,36,.12) !important; }
        body.dark-theme .border-amber-200 { border-color: rgba(251,191,36,.3) !important; }
        body.dark-theme .border-gray-300 { border-color: #475569 !important; }
        body.dark-theme .border-dashed.border-gray-300 { border-color: #475569 !important; }
        body.dark-theme .bg-white.rounded-lg.shadow-md { background: var(--db-surface, #1e293b) !important; }
    </style>
</head>
<body class="bg-gray-50 text-gray-900 <?= $isDark ? 'dark-theme' : '' ?>">
<script>
// Aplica o tema escuro imediatamente (antes do paint) para evitar flash
(function() {
    const stored = localStorage.getItem('theme');
    if (stored === 'dark' || (!stored && document.cookie.indexOf('theme=dark') !== -1)) {
        document.body.classList.add('dark-theme');
        document.documentElement.classList.add('dark');
    }
})();
</script>

<div class="max-w-6xl mx-auto py-8 px-4 sm:px-6">

    <?php if (!$isAjax): ?>
    <!-- ── Cabeçalho da Página ─────────────────────────────────────────── -->
    <div class="mb-8 flex flex-col items-start md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <nav class="mb-2" aria-label="breadcrumb">
                <ol class="flex items-center gap-2 text-[10px] uppercase font-bold tracking-widest text-gray-400 list-none p-0 m-0">
                    <li><a href="<?= BASE_URL ?>/orcamento" class="hover:text-sky-600 transition">Comercial</a></li>
                    <li class="opacity-40">/</li>
                    <li><a href="<?= BASE_URL ?>/orcamento/index" class="hover:text-sky-600 transition">Propostas</a></li>
                    <li class="opacity-40">/</li>
                    <li class="text-gray-400"><?= $isEdicao ? 'Edição' : 'Novo Registro' ?></li>
                </ol>
            </nav>
            <h1 class="text-2xl font-black text-gray-900 tracking-tight"><?= htmlspecialchars($titulo) ?></h1>
        </div>
        <a href="<?= BASE_URL ?>/orcamento/index"
           class="inline-flex items-center gap-1.5 px-2 py-1 text-sm font-bold text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition shadow-sm w-fit">
            <i class="fas fa-arrow-left text-sky-500"></i> Voltar
        </a>
    </div>
    <?php else: ?>
    <!-- ── Cabeçalho Modal ────────────────────────────────────────────── -->
    <div class="flex justify-between items-center mb-6 pb-4 border-b border-gray-100">
        <div class="flex items-center gap-3">
            <div class="p-2 bg-sky-100 text-sky-600 rounded-lg">
                <i class="fas fa-file-invoice-dollar text-xl"></i>
            </div>
            <h2 class="text-xl font-bold text-gray-800"><?= htmlspecialchars($titulo) ?></h2>
        </div>
        <button type="button" onclick="closePropostaModal()" class="text-gray-400 hover:text-gray-600 transition">
            <i class="fas fa-times text-lg"></i>
        </button>
    </div>
    <?php endif; ?>

    <!-- ══════════════════════════════════════════════════════════════════
         FORM PRINCIPAL
    ══════════════════════════════════════════════════════════════════ -->
    <form method="POST" action="<?= BASE_URL ?>/orcamento/salvar" id="form-orcamento" onsubmit="preSubmit()" enctype="multipart/form-data" novalidate>
        <input type="hidden" name="csrf_token"        value="<?= htmlspecialchars($csrf_token ?? '') ?>">
        <?php if ($isEdicao): ?>
            <input type="hidden" name="id"            value="<?= (int)$orc['id'] ?>">
        <?php endif; ?>

        <!-- Campos ocultos calculados via JS antes do submit -->
        <input type="hidden" id="hid-subtotal"        name="subtotal"        value="<?= $orc['subtotal']        ?? 0 ?>">
        <input type="hidden" id="hid-desconto-calc"   name="descontos_valor" value="<?= $orc['descontos_valor'] ?? 0 ?>">
        <input type="hidden" id="hid-impostos-valor"  name="impostos_valor"  value="<?= $orc['impostos_valor']  ?? 0 ?>">
        <input type="hidden" id="hid-total-geral"     name="valor_total"     value="<?= $orc['total_final']     ?? 0 ?>">

        <!-- ── STEPPER ──────────────────────────────────────────────────── -->
        <div id="stepper"
             class="flex overflow-hidden border border-gray-200 rounded-xl mb-6"
             role="tablist" aria-label="Etapas da proposta">
            <?php
            $steps = [
                ['n' => 1, 'label' => 'Identificação', 'icon' => 'fa-file-alt'],
                ['n' => 2, 'label' => 'Cliente',        'icon' => 'fa-building'],
                ['n' => 3, 'label' => 'Cronograma',     'icon' => 'fa-calendar-alt'],
                ['n' => 4, 'label' => 'Itens',          'icon' => 'fa-list-ol'],
                ['n' => 5, 'label' => 'Financeiro',     'icon' => 'fa-calculator'],
                ['n' => 6, 'label' => 'Revisão',        'icon' => 'fa-eye'],
            ];
            foreach ($steps as $i => $s):
            ?>
            <button type="button"
                    id="step-btn-<?= $s['n'] ?>"
                    class="step-btn flex-1 flex flex-col items-center gap-1 py-3 border-0 cursor-pointer
                           <?= $i < count($steps) - 1 ? 'border-r border-gray-200' : '' ?>"
                    onclick="goToStep(<?= $s['n'] ?>)"
                    role="tab"
                    aria-controls="step-panel-<?= $s['n'] ?>"
                    aria-selected="false">
                <i id="step-icon-<?= $s['n'] ?>"
                   class="fas <?= $s['icon'] ?> step-icon text-gray-400"
                   style="font-size:15px" aria-hidden="true"></i>
                <span id="step-label-<?= $s['n'] ?>"
                      class="step-label text-[10px] font-bold tracking-wider uppercase text-gray-400">
                    <?= $s['label'] ?>
                </span>
            </button>
            <?php endforeach; ?>
        </div>

        <!-- ════════════════════════════════════════════════════════════
             STEP 1 — IDENTIFICAÇÃO
        ════════════════════════════════════════════════════════════ -->
        <div id="step-panel-1" class="step-content" role="tabpanel">

            <!-- Informações da proposta -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-file-invoice"></i>
                    <div>
                        <p class="title">Informações da Proposta</p>
                    </div>
                </div>
                <div class="p-5 flex flex-col gap-4">

                    <!-- Título -->
                    <div>
                        <label class="lbl" for="titulo_proposta">Título da Proposta <span class="req">*</span></label>
                        <input type="text" id="titulo_proposta" name="titulo_proposta" required
                               class="fi" placeholder="Ex: Proposta Técnica Orçamentária – Inventário Florestal"
                               value="<?= htmlspecialchars($def['titulo_proposta']) ?>">
                        <?php if (!empty($erros['titulo_proposta'])): ?>
                            <p class="text-red-500 text-xs mt-1"><?= $erros['titulo_proposta'] ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- Metadados da proposta: Data · Validade · Elaborado por -->
                    <div class="idf-meta-grid">

                        <!-- Data da Proposta -->
                        <div class="idf-meta-item">
                            <label class="lbl" for="data_proposta">Data da Proposta <span class="req">*</span></label>
                            <input type="date" id="data_proposta" name="data_proposta" required
                                   class="fi"
                                   value="<?= htmlspecialchars($def['data_proposta']) ?>">
                        </div>

                        <!-- Validade -->
                        <div class="idf-meta-item">
                            <label class="lbl" for="validade_proposta">Validade (dias corridos)</label>
                            <input type="number" id="validade_proposta" name="validade_proposta"
                                   class="fi"
                                   min="1" max="365"
                                   value="<?= (int)$def['validade'] ?>">
                            <span class="idf-expiry-chip">
                                Expira em <strong id="data-validade-preview">—</strong>
                            </span>
                        </div>

                        <!-- Elaborado por -->
                        <div class="idf-meta-item">
                            <label class="lbl" for="responsavel_id">Elaborado por</label>
                            <select id="responsavel_id" name="responsavel_interno_id" class="fi">
                                <option value="">— Selecione —</option>
                                <?php if (!empty($usuarios)): ?>
                                    <?php foreach ($usuarios as $u): ?>
                                        <option value="<?= (int)$u['id'] ?>"
                                            <?= ((int)($def['responsavel_id'] ?? 0)) === (int)$u['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($u['nome']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                    </div>

                    <!-- Coordenadas Geográficas -->
                    <div class="border-t border-gray-100 pt-5 mt-2">
                        <p class="lbl mb-3"><i class="fas fa-map-marked-alt text-rose-500 mr-1"></i> Localização Geográfica da Atividade</p>

                        <!-- Importação via KML/GPX -->
                        <div class="mb-4 p-3 bg-slate-50 border border-slate-200 rounded-xl border-dashed">
                            <label class="lbl !normal-case !text-[10px] !mb-2" for="import-geo">Capturar de arquivo (KML ou GPX)</label>
                            <div class="flex items-center gap-3">
                                <input type="file" id="import-geo" name="geo_file" accept=".kml,.gpx" 
                                       class="text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-sky-50 file:text-sky-700 hover:file:bg-sky-100 transition cursor-pointer">
                                <p id="import-status" class="text-[10px] text-gray-500 hidden"><i class="fas fa-spinner fa-spin mr-1"></i> Extraindo dados...</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="lbl" for="latitude">Latitude</label>
                                <input type="text" id="latitude" name="latitude"
                                       class="fi" placeholder="Ex: -12.9704 ou 12°58'S"
                                       value="<?= htmlspecialchars($def['latitude']) ?>">
                            </div>
                            <div>
                                <label class="lbl" for="longitude">Longitude</label>
                                <input type="text" id="longitude" name="longitude"
                                       class="fi" placeholder="Ex: -38.5024 ou 38°30'W"
                                       value="<?= htmlspecialchars($def['longitude']) ?>">
                            </div>
                        </div>
                        <p class="text-[10px] text-gray-400 mt-2 italic leading-tight">
                            <i class="fas fa-info-circle"></i> O preenchimento permite a visualização prévia da proposta no mapa de calor do Dashboard.
                        </p>
                    </div>

                    <!-- Versão do documento -->
                    <div>
                        <label class="lbl" for="versao_documento">
                            Versão do Documento
                            <i class="fas fa-lock ml-1 text-[10px]" id="icon-lock-versao"></i>
                        </label>
                        <div class="flex items-stretch gap-2">
                            <input type="text" id="versao_documento" name="versao_documento"
                                   class="fi flex-grow"
                                   readonly
                                   value="<?= htmlspecialchars($def['versao']) ?>"
                                   placeholder="Ex: PO.REV.02.26-COM-001-26-EC"
                                   style="background:#F9FAFB;color:#9CA3AF;cursor:not-allowed">
                            <label class="flex items-center gap-1.5 px-3 bg-gray-100 border border-gray-200 rounded-lg cursor-pointer whitespace-nowrap"
                                   title="Habilitar edição manual da versão">
                                <input type="checkbox" id="permitir-edicao-versao"
                                       class="w-4 h-4 accent-sky-600 cursor-pointer">
                                <span class="text-[10px] font-bold text-gray-600 select-none">Editar Manualmente</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div><!-- /card Identificação -->

            <!-- Escopo / Apresentação -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-align-left"></i>
                    <div>
                        <p class="title">Apresentação / Objeto</p>
                        <p class="subtitle">Descreva o objeto da proposta</p>
                    </div>
                </div>
                <div class="p-5">
                    <textarea id="escopo" name="descricao_geral"
                              class="fi" rows="4"
                              style="resize:vertical"
                              placeholder="Descreva o escopo completo dos serviços a serem prestados..."><?= htmlspecialchars($def['escopo']) ?></textarea>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="button" class="btn-nav btn-primary" onclick="goToStep(2)">
                    Próximo: Cliente <i class="fas fa-arrow-right"></i>
                </button>
            </div>
        </div><!-- /step-panel-1 -->

        <!-- ════════════════════════════════════════════════════════════
             STEP 2 — CLIENTE
        ════════════════════════════════════════════════════════════ -->
        <div id="step-panel-2" class="step-content" role="tabpanel">

            <div class="card">
                <div class="card-header">
                    <i class="fas fa-building"></i>
                    <div><p class="title">Dados do Cliente</p></div>
                </div>
                <div class="p-5 flex flex-col gap-4">

                    <!-- Cliente + sigla + código da proposta -->
                    <div class="flex flex-wrap items-end gap-4">
                        <div class="min-w-0 flex-1">
                            <label class="lbl" for="select-cliente">Cliente <span class="req">*</span></label>
                            <select id="select-cliente" name="cliente_id" required class="fi">
                                <option value="">— Selecione um cliente —</option>
                                <?php if (!empty($clientes)): foreach ($clientes as $c): ?>
                                    <option value="<?= (int)$c['id'] ?>"
                                            data-sigla="<?= htmlspecialchars($c['sigla'] ?? '') ?>"
                                            data-email="<?= htmlspecialchars($c['email'] ?? '') ?>"
                                            data-representante="<?= htmlspecialchars($c['contato_principal'] ?? '') ?>"
                                            data-logradouro="<?= htmlspecialchars($c['logradouro'] ?? '') ?>"
                                            data-numero="<?= htmlspecialchars($c['numero'] ?? '') ?>"
                                            data-complemento="<?= htmlspecialchars($c['complemento'] ?? '') ?>"
                                            data-bairro="<?= htmlspecialchars($c['bairro'] ?? '') ?>"
                                            data-municipio="<?= htmlspecialchars($c['municipio'] ?? '') ?>"
                                            data-uf="<?= htmlspecialchars($c['estado'] ?? '') ?>"
                                            data-telefone="<?= htmlspecialchars($c['telefone'] ?? '') ?>"
                                            data-documento="<?= htmlspecialchars($c['cnpj_cpf'] ?? '') ?>"
                                            data-fantasia="<?= htmlspecialchars($c['nome_fantasia'] ?? '') ?>"
                                            data-financeiro='<?= $c['financeiro_json'] ?? "[]" ?>'
                                            data-comercial='<?= $c['comercial_json'] ?? "[]" ?>'
                                            <?= ((int)($def['cliente_id'] ?? 0)) === (int)$c['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($c['nome']) ?>
                                    </option>
                                <?php endforeach; endif; ?>
                            </select>
                            <?php if (!empty($erros['cliente_id'])): ?>
                                <p class="text-red-500 text-xs mt-1"><?= $erros['cliente_id'] ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="inline-block w-auto">
                            <label class="lbl" for="cliente_sigla_input">Sigla</label>
                            <input type="text" id="cliente_sigla_input" name="cliente_sigla"
                                   class="fi font-bold uppercase" readonly
                                   placeholder="—"
                                   value="<?= htmlspecialchars($orc['cliente_sigla'] ?? '') ?>"
                                   style="background:#F9FAFB;color:#6B7280;width:auto">
                        </div>
                        <div class="inline-block w-auto">
                            <label class="lbl" for="codigo">Código da Proposta <span class="req">*</span></label>
                            <div class="flex gap-2 items-center">
                                <input type="text" id="codigo" name="codigo" required
                                       class="fi" placeholder="Ex: 002-26-FCA"
                                       value="<?= htmlspecialchars($def['codigo']) ?>"
                                       style="width:auto;min-width:150px;max-width:100%"
                                       oninput="this.style.width = (this.scrollWidth + 4) + 'px'">
                                <button type="button" id="btn-reset-codigo"
                                        class="px-3 py-1 bg-gray-100 border border-gray-200 rounded-lg hover:bg-gray-200 transition text-gray-600 flex-shrink-0"
                                        title="Resetar para o código sugerido pelo sistema">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Representante · E-mail · Telefone -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="lbl" for="cliente_documento">CPF / CNPJ</label>
                            <input type="text" id="cliente_documento" name="cliente_documento"
                                   class="fi" readonly
                                   placeholder="—"
                                   value="<?= htmlspecialchars($def['cliente_documento']) ?>"
                                   style="background:#F9FAFB;color:#6B7280">
                        </div>
                        <div>
                            <label class="lbl" for="representante">Representante / Contato</label>
                            <input type="text" id="representante" name="representante"
                                   class="fi" placeholder="Ex: Rafael Macedo – Setor de Meio Ambiente"
                                   value="<?= htmlspecialchars($def['representante']) ?>">
                        </div>
                        <div>
                            <label class="lbl" for="email_cliente">E-mail do cliente</label>
                            <input type="email" id="email_cliente" name="email_cliente"
                                   class="fi" placeholder="email@empresa.com.br"
                                   value="<?= htmlspecialchars($def['email_cliente']) ?>">
                        </div>
                        <div class="md:col-span-3">
                            <label class="lbl" for="telefone_cliente">Telefone / Celular</label>
                            <input type="text" id="telefone_cliente" name="cliente_telefone"
                                   class="fi phone-mask" placeholder="(00) 00000-0000"
                                   value="<?= htmlspecialchars($def['cliente_telefone']) ?>">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
                        <div class="md:col-span-3">
                            <label class="lbl" for="cliente_logradouro">Logradouro</label>
                            <input type="text" id="cliente_logradouro" name="cliente_logradouro"
                                   class="fi" readonly placeholder="—"
                                   value="<?= htmlspecialchars($def['cliente_logradouro']) ?>"
                                   style="background:#F9FAFB;color:#6B7280">
                        </div>
                        <div class="md:col-span-1">
                            <label class="lbl" for="cliente_numero">Nº</label>
                            <input type="text" id="cliente_numero" name="cliente_numero"
                                   class="fi" readonly placeholder="—"
                                   value="<?= htmlspecialchars($def['cliente_numero']) ?>"
                                   style="background:#F9FAFB;color:#6B7280">
                        </div>
                        <div class="md:col-span-2">
                            <label class="lbl" for="cliente_complemento">Complemento</label>
                            <input type="text" id="cliente_complemento" name="cliente_complemento"
                                   class="fi" readonly placeholder="—"
                                   value="<?= htmlspecialchars($def['cliente_complemento']) ?>"
                                   style="background:#F9FAFB;color:#6B7280">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="md:col-span-1">
                            <label class="lbl" for="cliente_bairro">Bairro</label>
                            <input type="text" id="cliente_bairro" name="cliente_bairro"
                                   class="fi" readonly placeholder="—"
                                   value="<?= htmlspecialchars($def['cliente_bairro']) ?>"
                                   style="background:#F9FAFB;color:#6B7280">
                        </div>
                        <div class="md:col-span-2">
                            <label class="lbl" for="cliente_municipio">Município</label>
                            <input type="text" id="cliente_municipio" name="cliente_municipio"
                                   class="fi" readonly placeholder="—"
                                   value="<?= htmlspecialchars($def['cliente_municipio']) ?>"
                                   style="background:#F9FAFB;color:#6B7280">
                        </div>
                        <div class="md:col-span-1">
                            <label class="lbl" for="cliente_uf">UF</label>
                            <input type="text" id="cliente_uf" name="cliente_uf"
                                   class="fi" readonly placeholder="—"
                                   value="<?= htmlspecialchars($def['cliente_uf']) ?>"
                                   style="background:#F9FAFB;color:#6B7280">
                        </div>
                    </div>

                    <!-- Município / Área agora calculados automaticamente pela tabela de Contextualização -->
                    <input type="hidden" id="municipio" name="municipio" value="<?= htmlspecialchars($def['municipio']) ?>">
                    <input type="hidden" id="area" name="area" value="<?= htmlspecialchars($def['area']) ?>">

                    <!-- Projeto vinculado (opcional) -->
                    <div class="border-t border-gray-100 pt-4">
                        <label class="flex items-center gap-3 cursor-pointer select-none">
                            <input type="checkbox" id="has-projeto-checkbox"
                                   class="w-4 h-4 border border-gray-300 rounded accent-sky-600 cursor-pointer"
                                   <?= !empty($def['projeto_id']) ? 'checked' : '' ?>>
                            <span class="text-xs font-bold text-gray-500 uppercase">Há um Projeto Vinculado?</span>
                        </label>
                    </div>
                    <div id="section-projeto" class="<?= empty($def['projeto_id']) ? 'hidden' : '' ?> flex flex-col gap-4 pt-2">
                        <div>
                            <label class="lbl" for="projeto_id">Projeto Vinculado</label>
                            <select id="projeto_id" name="projeto_id" class="fi">
                                <option value="">— Selecione o projeto —</option>
                                <?php if (!empty($projetos)): foreach ($projetos as $p): ?>
                                    <option value="<?= (int)$p['id'] ?>"
                                            <?= ((int)($def['projeto_id'] ?? 0)) === (int)$p['id'] ? 'selected' : '' ?>>
                                        [#<?= (int)$p['id'] ?>] <?= htmlspecialchars($p['nome']) ?>
                                    </option>
                                <?php endforeach; endif; ?>
                            </select>
                        </div>
                        <!-- Detalhes do projeto -->
                        <div id="project-details-container"
                             class="<?= empty($def['projeto_id']) ? 'hidden' : '' ?> grid grid-cols-2 md:grid-cols-4 gap-3 p-3 bg-sky-50 rounded-lg border border-sky-200 text-[11px]">
                            <div>
                                <span class="text-gray-500 block mb-1 uppercase font-bold">Cliente</span>
                                <span id="detail-cliente" class="font-semibold text-gray-700">—</span>
                            </div>
                            <div>
                                <span class="text-gray-500 block mb-1 uppercase font-bold">Responsável</span>
                                <span id="detail-responsavel" class="font-semibold text-gray-700">—</span>
                            </div>
                            <div>
                                <span class="text-gray-500 block mb-1 uppercase font-bold">Tipo</span>
                                <span id="detail-tipo" class="font-semibold text-gray-700">—</span>
                            </div>
                            <div>
                                <span class="text-gray-500 block mb-1 uppercase font-bold">ID</span>
                                <span id="detail-id" class="font-semibold text-sky-600">—</span>
                            </div>
                        </div>
                    </div>

                    <!-- Contrato vinculado (opcional) -->
                    <div class="border-t border-gray-100 pt-4">
                        <label class="flex items-center gap-3 cursor-pointer select-none">
                            <input type="checkbox" id="has-contrato-checkbox"
                                   class="w-4 h-4 border border-gray-300 rounded accent-emerald-600 cursor-pointer"
                                   <?= !empty($def['contrato_id']) ? 'checked' : '' ?>>
                            <span class="text-xs font-bold text-gray-500 uppercase">Há um Contrato Vinculado?</span>
                        </label>
                    </div>
                    <div id="section-contrato" class="<?= empty($def['contrato_id']) ? 'hidden' : '' ?> flex flex-col gap-4 pt-2">
                        <div>
                            <label class="lbl" for="contrato_id">Contrato Vinculado</label>
                            <select id="contrato_id" name="contrato_id" class="fi">
                                <option value="">— Selecione o contrato —</option>
                                <?php if (!empty($contratos)): foreach ($contratos as $ct): ?>
                                    <option value="<?= (int)$ct['id'] ?>"
                                            <?= ((int)($def['contrato_id'] ?? 0)) === (int)$ct['id'] ? 'selected' : '' ?>>
                                        [#<?= htmlspecialchars($ct['numero_contrato'] ?? $ct['id']) ?>]
                                        <?= htmlspecialchars($ct['titulo'] ?? $ct['objeto'] ?? '') ?>
                                    </option>
                                <?php endforeach; endif; ?>
                            </select>
                        </div>
                        <!-- Detalhes do contrato -->
                        <div id="section-contrato-detalhes"
                             class="<?= empty($def['contrato_id']) ? 'hidden' : '' ?> grid grid-cols-2 md:grid-cols-3 gap-3 p-3 bg-emerald-50 rounded-lg border border-emerald-200 text-[11px]">
                            <div>
                                <span class="text-gray-500 block mb-1 uppercase font-bold">Vencimento</span>
                                <span id="detail-ct-vencimento" class="font-semibold text-gray-700">—</span>
                            </div>
                            <div>
                                <span class="text-gray-500 block mb-1 uppercase font-bold">Valor</span>
                                <span id="detail-ct-valor" class="font-semibold text-gray-700">—</span>
                            </div>
                            <div>
                                <span class="text-gray-500 block mb-1 uppercase font-bold">ID</span>
                                <span id="detail-ct-id" class="font-semibold text-emerald-600">—</span>
                            </div>
                        </div>
                    </div>

                </div>
            </div><!-- /card Cliente -->

            <!-- ════════════════════════════════════════════════════════════
                 CARD — CONTEXTUALIZAÇÃO (Empreendedor / Faixa de domínio / KM / Município / Área)
            ════════════════════════════════════════════════════════════ -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-map-marked-alt"></i>
                    <div>
                        <p class="title">Contextualização</p>
                        <p class="subtitle">Áreas de inventário florestal por empreendedor / trecho</p>
                    </div>
                </div>
                <div class="p-5 flex flex-col gap-3">
                    <textarea id="ctx-texto-intro" rows="2"
                              class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-sky-400 focus:border-sky-400 resize-none"
                              placeholder="Texto de introdução antes da tabela (opcional)"></textarea>
                    <div class="overflow-x-auto">
                        <table class="w-full tabela-itens" id="tabela-contextualizacao">
                            <thead>
                                <tr class="border-b border-gray-200">
                                    <th style="min-width:140px">Empreendedor</th>
                                    <th style="min-width:140px">Faixa de domínio</th>
                                    <th style="min-width:120px">KM</th>
                                    <th style="min-width:160px">Município / Estado</th>
                                    <th style="width:110px">Área (ha)</th>
                                    <th style="width:40px"></th>
                                </tr>
                            </thead>
                            <tbody id="tbody-contextualizacao"></tbody>
                            <tfoot>
                                <tr style="background:var(--brand-light);font-weight:700">
                                    <td colspan="4" style="padding:10px;color:var(--brand-dark);text-transform:uppercase;font-size:11px;letter-spacing:.05em">Total</td>
                                    <td id="contexto-total-area" style="padding:10px;color:var(--brand-dark)">0,00</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <div class="flex items-center justify-between mt-2">
                        <button type="button" id="btn-add-contexto"
                                class="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-wide text-sky-700 hover:text-sky-900 transition">
                            <i class="fas fa-plus-circle"></i> Incluir linha
                        </button>
                        <label class="flex items-center gap-1.5 text-xs text-gray-500 cursor-pointer select-none">
                            <input type="checkbox" id="ctx-ocultar-vazias" class="rounded border-gray-300 text-sky-600 focus:ring-sky-500" checked>
                            <i class="fas fa-file-pdf text-gray-400"></i> Ocultar tabela no PDF
                        </label>
                    </div>
                    <input type="hidden" id="contextualizacao_json" name="contextualizacao_json" value="">
                </div>
            </div><!-- /card Contextualização -->

            <!-- ════════════════════════════════════════════════════════════
                 CARD — EQUIPE DO PROJETO
            ════════════════════════════════════════════════════════════ -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-users"></i>
                    <div>
                        <p class="title">Equipe do Projeto</p>
                        <p class="subtitle">Profissionais responsáveis pela execução das atividades</p>
                    </div>
                </div>
                <div class="p-5 flex flex-col gap-3">
                    <textarea id="eq-texto-intro" rows="2"
                              class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-sky-400 focus:border-sky-400 resize-none"
                              placeholder="Texto de introdução antes da tabela (opcional)"></textarea>
                    <div class="overflow-x-auto">
                        <table class="w-full tabela-itens" id="tabela-equipe">
                            <thead>
                                <tr class="border-b border-gray-200">
                                    <th style="min-width:160px">Profissional</th>
                                    <th style="min-width:140px">Campo de atuação</th>
                                    <th style="min-width:260px">Função</th>
                                    <th style="width:40px"></th>
                                </tr>
                            </thead>
                            <tbody id="tbody-equipe"></tbody>
                        </table>
                    </div>
                    <button type="button" id="btn-add-equipe"
                            class="self-start inline-flex items-center gap-2 text-xs font-bold uppercase tracking-wide text-sky-700 hover:text-sky-900 transition">
                        <i class="fas fa-plus-circle"></i> Incluir profissional
                    </button>
                    <input type="hidden" id="equipe_json" name="equipe_json" value="">
                </div>
            </div><!-- /card Equipe -->

            <div class="flex justify-between">
                <button type="button" class="btn-nav" onclick="goToStep(1)">
                    <i class="fas fa-arrow-left"></i> Anterior
                </button>
                <button type="button" class="btn-nav btn-primary" onclick="goToStep(3)">
                    Próximo: Cronograma <i class="fas fa-arrow-right"></i>
                </button>
            </div>
        </div><!-- /step-panel-2 -->

        <!-- ════════════════════════════════════════════════════════════
             STEP 3 — CRONOGRAMA DE EXECUÇÃO
        ════════════════════════════════════════════════════════════ -->
        <div id="step-panel-3" class="step-content" role="tabpanel">

            <style>
                /* ── Cronograma ── */
                .crono-mode-switcher { display:flex;background:#F3F4F6;border-radius:8px;padding:3px;gap:2px; }
                .crono-mode-btn { padding:5px 12px;border-radius:6px;font-size:11px;font-weight:700;border:none;cursor:pointer;background:transparent;color:#6B7280;text-transform:uppercase;letter-spacing:.05em;transition:all .15s; }
                .crono-mode-btn.active { background:#fff;color:var(--brand);box-shadow:0 1px 3px rgba(0,0,0,.1); }
                .crono-config-bar { display:flex;align-items:center;gap:16px;padding:12px 20px;border-bottom:0.5px solid #E5E7EB;background:#FAFAFA;flex-wrap:wrap; }
                .crono-config-group { display:flex;align-items:center;gap:8px; }
                .crono-badge-esc { font-size:10px;font-weight:700;padding:3px 8px;border-radius:999px;background:var(--brand-light);color:var(--brand-dark);border:0.5px solid var(--brand-border);white-space:nowrap; }
                .crono-badge-camp { font-size:10px;font-weight:700;padding:3px 8px;border-radius:999px;background:var(--success-light);color:#27500A;border:0.5px solid var(--success-border);white-space:nowrap; }
                .crono-tip { display:flex;align-items:flex-start;gap:10px;padding:10px 16px;background:#FAEEDA;border-radius:8px;margin:12px 20px;border:0.5px solid #FAC775; }
                .crono-tip i { color:#854F0B;margin-top:1px;font-size:13px; }
                .crono-tip p { font-size:11px;color:#633806;line-height:1.5;margin:0; }
                .crono-wrapper { overflow-x:auto;padding-bottom:4px; }
                .crono-table { width:100%;border-collapse:collapse;min-width:700px; }
                .crono-table th { font-size:9px;font-weight:800;text-transform:uppercase;letter-spacing:.06em;color:#6B7280;background:#F9FAFB;padding:8px 4px;text-align:center;border-bottom:0.5px solid #E5E7EB;white-space:nowrap; }
                .crono-table th.col-ativ { text-align:left;padding:8px 16px;min-width:230px;width:230px;position:sticky;left:0;z-index:2; }
                .crono-table td { padding:6px 4px;border-bottom:0.5px solid #F3F4F6;text-align:center;vertical-align:middle; }
                .crono-table td.col-ativ { text-align:left;padding:7px 16px;border-right:0.5px solid #E5E7EB;background:#fff;position:sticky;left:0;z-index:1;min-width:230px;width:230px; }
                .crono-table td.col-ativ input { border:none;outline:none;width:100%;font-size:12px;font-weight:500;color:#374151;background:transparent;padding:0;white-space:normal; }
                .crono-table td.col-ativ input:focus { background:#F0F7FF;border-radius:4px;padding:2px 4px; }
                .crono-table tbody tr:hover td { background:#F9FAFB; }
                .crono-table tbody tr:hover td.col-ativ { background:#F9FAFB; }
                .crono-cell-day { width:30px;min-width:26px;cursor:pointer; }
                .crono-dot { width:22px;height:22px;border-radius:6px;border:1.5px solid #E5E7EB;background:#fff;margin:0 auto;display:flex;align-items:center;justify-content:center;cursor:pointer;transition:all .1s;user-select:none;font-size:10px;font-weight:800; }
                .crono-dot:hover { border-color:var(--brand);background:var(--brand-light); }
                .crono-dot.st-esc { background:var(--brand);border-color:var(--brand-dark);color:#fff; }
                .crono-dot.st-camp { background:var(--success);border-color:#27500A;color:#fff; }
                .crono-day-num { font-size:10px;font-weight:800;color:#374151; }
                .crono-day-sub { font-size:7px;color:#9CA3AF;display:block;margin-top:1px; }
                .crono-legend { display:flex;align-items:center;gap:20px;padding:12px 20px;border-top:0.5px solid #E5E7EB;background:#FAFAFA;flex-wrap:wrap; }
                .crono-legend-item { display:flex;align-items:center;gap:6px;font-size:11px;color:#6B7280; }
                .crono-legend-dot { width:14px;height:14px;border-radius:4px; }
                .crono-summary-bar { display:flex;gap:12px;padding:14px 20px;border-top:0.5px solid #E5E7EB; }
                .crono-sum-card { flex:1;background:#F9FAFB;border-radius:8px;padding:10px 14px;border:0.5px solid #E5E7EB; }
                .crono-sum-label { font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#9CA3AF; }
                .crono-sum-value { font-size:20px;font-weight:800;color:#111827;margin-top:2px; }
                .crono-sum-value.blue { color:var(--brand); }
                .crono-sum-value.green { color:var(--success); }
                .crono-add-btn { display:flex;align-items:center;gap:6px;margin:0;padding:10px 16px;background:#F9FAFB;border-top:0.5px dashed #E5E7EB;cursor:pointer;font-size:11px;font-weight:700;color:var(--brand);text-transform:uppercase;letter-spacing:.05em;border:none;width:100%;transition:background .15s; }
                .crono-add-btn:hover { background:var(--brand-light); }
            </style>

            <div class="card">
                <div class="card-header" style="justify-content:space-between">
                    <div style="display:flex;align-items:center;gap:10px">
                        <i class="fas fa-calendar-alt"></i>
                        <div>
                            <p class="title">Cronograma de Execução de Atividades do Projeto</p>
                            <p class="subtitle">Clique nas células para marcar os dias de cada atividade</p>
                        </div>
                    </div>
                    <div class="crono-mode-switcher">
                        <button type="button" class="crono-mode-btn active" onclick="cronoSetMode('dias',this)">Dias</button>
                        <button type="button" class="crono-mode-btn" onclick="cronoSetMode('semanas',this)">Semanas</button>
                        <button type="button" class="crono-mode-btn" onclick="cronoSetMode('meses',this)">Meses</button>
                    </div>
                </div>

                <textarea id="cronograma-texto-intro" rows="2"
                          class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-sky-400 focus:border-sky-400 resize-none"
                          placeholder="Texto de introdução antes do cronograma (opcional)"></textarea>

                <div class="crono-config-bar">
                    <div class="crono-config-group">
                        <label class="lbl" style="margin:0">Total de períodos:</label>
                        <input type="number" class="fi" id="crono-total-periods" style="width:130px" value="24" min="1" max="999" list="crono-periods-list" oninput="cronoBuildTable()">
                        <datalist id="crono-periods-list">
                            <option value="15">15</option>
                            <option value="20">20</option>
                            <option value="24">24</option>
                            <option value="30">30</option>
                            <option value="45">45</option>
                            <option value="60">60</option>
                        </datalist>
                    </div>
                    <div class="crono-config-group">
                        <label class="lbl" style="margin:0">Data de início:</label>
                        <input type="date" class="fi" id="crono-data-inicio" style="width:160px" onchange="cronoBuildTable()">
                    </div>
                    <div class="crono-config-group">
                        <label class="lbl" style="margin:0">Marcação padrão:</label>
                        <select class="fi" id="crono-tipo-padrao" style="width:130px">
                            <option value="esc">Escritório</option>
                            <option value="camp">Campo</option>
                        </select>
                    </div>
                    <div style="margin-left:auto;display:flex;gap:8px;align-items:center">
                        <span class="crono-badge-esc" id="crono-badge-esc">0 escritório</span>
                        <span class="crono-badge-camp" id="crono-badge-camp">0 campo</span>
                    </div>
                </div>

                <div class="crono-tip">
                    <i class="fas fa-info-circle"></i>
                    <p><strong>Como usar:</strong> Clique 1× para marcar como <strong>escritório</strong> (azul) · Clique 2× para <strong>campo</strong> (verde) · Clique 3× para <strong>desmarcar</strong>. Arraste o mouse para marcar múltiplas células de uma vez.</p>
                </div>

                <div class="crono-wrapper">
                    <table class="crono-table" id="crono-table">
                        <thead id="crono-head"></thead>
                        <tbody id="crono-body"></tbody>
                    </table>
                </div>

                <button type="button" class="crono-add-btn" onclick="cronoAddRow()">
                    <i class="fas fa-plus"></i> Adicionar atividade
                </button>

                <div class="crono-summary-bar">
                    <div class="crono-sum-card">
                        <p class="crono-sum-label">Atividades</p>
                        <p class="crono-sum-value" id="crono-sum-ativ">0</p>
                    </div>
                    <div class="crono-sum-card">
                        <p class="crono-sum-label">Dias escritório</p>
                        <p class="crono-sum-value blue" id="crono-sum-esc">0</p>
                    </div>
                    <div class="crono-sum-card">
                        <p class="crono-sum-label">Dias campo</p>
                        <p class="crono-sum-value green" id="crono-sum-camp">0</p>
                    </div>
                    <div class="crono-sum-card">
                        <p class="crono-sum-label">Duração prevista</p>
                        <p class="crono-sum-value" id="crono-sum-total" style="font-size:14px;color:#374151;margin-top:6px">—</p>
                    </div>
                </div>

                <div class="crono-legend">
                    <span class="lbl" style="margin:0">Legenda:</span>
                    <div class="crono-legend-item">
                        <div class="crono-legend-dot" style="background:var(--brand)"></div> Atividade de escritório
                    </div>
                    <div class="crono-legend-item">
                        <div class="crono-legend-dot" style="background:var(--success)"></div> Atividade de campo
                    </div>
                    <div class="crono-legend-item">
                        <div class="crono-legend-dot" style="background:#fff;border:1.5px solid #E5E7EB"></div> Não marcado
                    </div>
                </div>

                <textarea id="cronograma-texto-footer" rows="2"
                          class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-sky-400 focus:border-sky-400 resize-none mt-2"
                          placeholder="Texto opcional exibido após o cronograma">* Cronograma sujeito a alterações conforme condições climáticas ou liberações de órgãos anuentes.</textarea>

                <!-- Campo oculto para persistir o cronograma no submit -->
                <input type="hidden" name="cronograma_data" id="crono-hidden-data" value="">
            </div>

            <!-- Navegação -->
            <div class="flex justify-between items-center mt-2">
                <button type="button" class="btn-nav" onclick="goToStep(2)">
                    <i class="fas fa-arrow-left"></i> Cliente
                </button>
                <div class="flex gap-2">
                    <button type="button" class="btn-nav" onclick="cronoClearAll()" style="color:var(--danger);border-color:#F7C1C1">
                        <i class="fas fa-times"></i> Limpar tudo
                    </button>
                    <button type="button" class="btn-nav btn-primary" onclick="goToStep(4)">
                        Itens <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </div>

        </div><!-- /step-panel-3 Cronograma -->

        <!-- ════════════════════════════════════════════════════════════
             STEP 4 — ITENS
        ════════════════════════════════════════════════════════════ -->
        <div id="step-panel-4" class="step-content" role="tabpanel">

            <div class="card">
                <div class="sticky top-0 z-20 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700" style="padding:12px 18px;display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap;margin:0">
                    <div style="display:flex;align-items:center;gap:10px">
                        <div style="background:linear-gradient(135deg,var(--brand),#3b82f6);width:4px;height:32px;border-radius:4px;flex-shrink:0"></div>
                        <div>
                            <p class="title" style="margin:0;font-size:15px;font-weight:700;letter-spacing:-.01em;color:var(--brand-dark, #1e293b)">Itens da Proposta</p>
                            <p class="subtitle" style="margin:2px 0 0;font-size:11.5px;color:#6b7280;font-weight:500">Organize por categoria — os totais são calculados em tempo real</p>
                        </div>
                    </div>
                    <div class="flex gap-2 flex-wrap">
                        <button type="button" id="btn-add-titulo"
                                class="flex items-center gap-1 px-3 py-1.5 text-xs font-bold text-purple-700 bg-white border border-purple-200 hover:bg-purple-50 rounded-lg transition shadow-sm"
                                title="Adiciona título de seção numerado (ex: 5.1 Planejamento)">
                            <i class="fas fa-heading"></i> Título de seção
                        </button>
                        <button type="button" id="btn-add-subtitulo"
                                class="flex items-center gap-1 px-3 py-1.5 text-xs font-bold text-sky-700 bg-white border border-sky-200 hover:bg-sky-50 rounded-lg transition shadow-sm"
                                title="Adiciona parágrafo descritivo abaixo do título">
                            <i class="fas fa-align-left"></i> Texto descritivo
                        </button>
                        <button type="button" id="btn-add-item"
                                class="flex items-center gap-1 px-3 py-1.5 text-xs font-bold text-white bg-sky-600 hover:bg-sky-700 rounded-lg transition shadow-sm shadow-sky-100">
                            <i class="fas fa-plus"></i> Adicionar item
                        </button>
                    </div>
                </div>

                <?php if (!empty($erros['itens'])): ?>
                    <div class="mx-4 mt-3 px-4 py-2 bg-red-50 border border-red-200 rounded-lg text-red-600 text-sm">
                        <?= $erros['itens'] ?>
                    </div>
                <?php endif; ?>

                <div style="overflow-x:auto;max-width:100%">
                    <table class="w-full tabela-itens" id="tabela-itens">
                        <thead id="thead-itens">
                            <tr class="border-b border-gray-200">
                                <th style="min-width:140px">Categoria</th>
                                <th style="min-width:180px">Descrição</th>
                                <th style="min-width:180px">Detalhes</th>
                                <th style="width:90px">Unidade</th>
                                <th style="width:90px">Qtd.</th>
                                <th style="width:120px">Valor Unit.</th>
                                <th style="width:80px">Desc. %</th>
                                <th style="width:120px;text-align:right">Total</th>
                                <th style="width:40px"></th>
                            </tr>
                        </thead>
                        <tbody id="tbody-itens">
                        <?php
                        $idxItem = 0;
                        foreach ($itens as $item):
                            $catItem = $item['categoria'] ?? 'Outros';
                            $isTitulo    = ($catItem === 'Titulo');
                            $isSubtitulo = ($catItem === 'Subtitulo');
                            $isLegend    = ($catItem === 'Legenda') || $isTitulo || $isSubtitulo;
                            $totalItem   = $isLegend ? 0 : (float)$item['quantidade'] * (float)$item['valor_unit'] * (1 - ((float)($item['desconto_item'] ?? 0) / 100));
                        ?>
                        <?php if ($isTitulo): ?>
                            <!-- TÍTULO DE SEÇÃO -->
                            <tr class="border-l-4 border-l-purple-500" style="background:rgba(238,237,254,.55)" data-is-legend="1">
                                <td class="px-3 py-2" style="white-space:nowrap">
                                    <div class="flex items-center gap-1">
                                        <span style="font-size:8px;font-weight:900;text-transform:uppercase;letter-spacing:.04em;padding:2px 7px;border-radius:6px;border:0.5px solid #CECBF6;background:#EEEDFE;color:#3C3489">
                                            <i class="fas fa-heading" style="font-size:8px;margin-right:3px"></i>Título
                                        </span>
                                        <input type="hidden" name="item_categoria[]" class="item-categoria" value="Titulo">
                                    </div>
                                </td>
                                <td colspan="5" style="padding:6px 8px">
                                    <input type="text" name="item_descricao[]"
                                           class="fi" style="font-size:13px;font-weight:700;padding:5px 8px;border-color:#CECBF6;background:transparent"
                                           placeholder="Ex: Planejamento logístico e coordenação geral do projeto"
                                           value="<?= htmlspecialchars($item['descricao'] ?? '') ?>" required>
                                    <input type="hidden" name="item_detalhes[]" value="<?= htmlspecialchars($item['detalhes'] ?? '') ?>">
                                    <input type="hidden" name="item_unidade[]" value="un">
                                    <input type="hidden" name="item_quantidade[]" value="0">
                                    <input type="hidden" name="item_valor[]" value="0">
                                    <input type="hidden" name="item_desconto[]" value="0">
                                </td>
                                <td colspan="2"></td>
                                <td class="text-center">
                                    <button type="button" class="btn-remover-item text-gray-300 hover:text-red-500 transition" title="Remover título">
                                        <i class="fas fa-trash-alt" style="font-size:13px"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php elseif ($isSubtitulo): ?>
                            <!-- TEXTO DESCRITIVO -->
                            <tr class="border-l-4 border-l-sky-300" style="background:rgba(230,241,251,.4)" data-is-legend="1">
                                <td class="px-3 py-2" style="white-space:nowrap">
                                    <div class="flex items-center gap-1">
                                        <span style="font-size:8px;font-weight:900;text-transform:uppercase;letter-spacing:.04em;padding:2px 7px;border-radius:6px;border:0.5px solid #93c5fd;background:#dbeafe;color:#1d4ed8">
                                            <i class="fas fa-align-left" style="font-size:7px;margin-right:3px"></i>Texto
                                        </span>
                                        <input type="hidden" name="item_categoria[]" class="item-categoria" value="Subtitulo">
                                    </div>
                                </td>
                                <td colspan="7" style="padding:6px 8px">
                                    <textarea name="item_descricao[]"
                                              class="fi !bg-transparent !border-none focus:!ring-0 resize-none overflow-hidden"
                                              style="font-size:12px;color:#374151;min-height:36px;line-height:1.6;padding:4px 8px"
                                              placeholder="Descreva as atividades desta seção (aparece como parágrafo no PDF)..."
                                              oninput="this.style.height='';this.style.height=this.scrollHeight+'px'"
                                              ><?= htmlspecialchars($item['descricao'] ?? '') ?></textarea>
                                    <input type="hidden" name="item_detalhes[]" value="">
                                    <input type="hidden" name="item_unidade[]" value="un">
                                    <input type="hidden" name="item_quantidade[]" value="0">
                                    <input type="hidden" name="item_valor[]" value="0">
                                    <input type="hidden" name="item_desconto[]" value="0">
                                </td>
                                <td class="text-center" style="vertical-align:middle">
                                    <button type="button" class="btn-remover-item text-gray-300 hover:text-red-500 transition" title="Remover texto">
                                        <i class="fas fa-trash-alt" style="font-size:13px"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php elseif ($isLegend): ?>
                            <!-- LEGENDA LEGADA (compatibilidade) -->
                            <tr class="bg-slate-100/50 border-l-4 border-l-sky-500" data-is-legend="1">
                                <td class="px-4 py-3">
                                    <div class="flex items-center h-full">
                                        <span class="text-[9px] font-black uppercase text-sky-700 bg-sky-100 px-2 py-1 rounded border border-sky-200">Legenda</span>
                                        <input type="hidden" name="item_categoria[]" class="item-categoria" value="Legenda">
                                    </div>
                                </td>
                                <td colspan="7">
                                    <textarea name="item_descricao[]"
                                              class="fi !font-bold !bg-transparent !border-none focus:!ring-0 resize-none overflow-hidden"
                                              style="font-size:13px;min-height:38px;line-height:1.5;padding:8px"
                                              placeholder="Título da seção..."
                                              oninput="this.style.height='';this.style.height=this.scrollHeight+'px'"
                                              required><?= htmlspecialchars($item['descricao'] ?? '') ?></textarea>
                                    <input type="hidden" name="item_detalhes[]" value="">
                                    <input type="hidden" name="item_unidade[]" value="un">
                                    <input type="hidden" name="item_quantidade[]" value="0">
                                    <input type="hidden" name="item_valor[]" value="0">
                                    <input type="hidden" name="item_desconto[]" value="0">
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn-remover-item text-gray-300 hover:text-red-500 transition" title="Remover legenda">
                                        <i class="fas fa-trash-alt" style="font-size:13px"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php else: ?>
                        <tr data-idx="<?= $idxItem ?>" data-total="<?= round($totalItem, 2) ?>">
                            <td>
                                <select name="item_categoria[]" class="fi item-categoria" style="font-size:11px;padding:5px 7px">
                                    <?php foreach ($categorias as $cat): ?>
                                        <option value="<?= htmlspecialchars($cat) ?>"
                                            <?= ($item['categoria'] ?? '') === $cat ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($cat) ?>
                                        </option>
                                    <?php endforeach; ?>
                                    <option value="ADD_NEW" class="font-bold text-sky-600">+ Adicionar Nova...</option>
                                </select>
                            </td>
                            <td>
                                <input type="text" name="item_descricao[]"
                                       class="fi" style="font-size:12px;padding:5px 8px"
                                       placeholder="Descrição"
                                       value="<?= htmlspecialchars($item['descricao']) ?>" required>
                            </td>
                            <td>
                                <input type="text" name="item_detalhes[]"
                                       class="fi" style="font-size:12px;padding:5px 8px"
                                       placeholder="Detalhes (opcional)"
                                       value="<?= htmlspecialchars($item['detalhes'] ?? '') ?>">
                            </td>
                            <td>
                                <select name="item_unidade[]" class="fi item-unidade" style="font-size:12px;padding:5px 7px">
                                    <?php foreach ($unidades as $un): ?>
                                        <option value="<?= htmlspecialchars($un) ?>"
                                            <?= ($item['unidade'] ?? 'un') === $un ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($un) ?>
                                        </option>
                                    <?php endforeach; ?>
                                    <option value="ADD_NEW" class="font-bold text-sky-600">+ Nova...</option>
                                </select>
                            </td>
                            <td>
                                <input type="number" name="item_quantidade[]"
                                       class="fi item-qty" style="width:72px;font-size:12px;text-align:right;padding:5px 8px"
                                       min="0.001" step="any"
                                       value="<?= (float)$item['quantidade'] ?>">
                            </td>
                            <td>
                                <input type="number" name="item_valor[]"
                                       class="fi item-vunit" style="width:100px;font-size:12px;text-align:right;padding:5px 8px"
                                       min="0" step="0.01"
                                       value="<?= number_format((float)$item['valor_unit'], 2, '.', '') ?>">
                            </td>
                            <td>
                                <input type="number" name="item_desconto[]"
                                       class="fi item-desc" style="width:60px;font-size:12px;text-align:right;padding:5px 8px"
                                       min="0" max="100" step="0.01"
                                       value="<?= (float)($item['desconto_item'] ?? 0) ?>">
                            </td>
                            <td class="item-total-cell" style="color:#111827">
                                <?= 'R$ ' . number_format($totalItem, 2, ',', '.') ?>
                            </td>
                            <td>
                                <button type="button" class="btn-remover-item text-gray-300 hover:text-red-500 transition" title="Remover item">
                                    <i class="fas fa-trash-alt" style="font-size:13px"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endif; $idxItem++; endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Totais por categoria (renderizados via JS) -->
                <div id="totais-por-categoria"
                     class="flex flex-wrap gap-2 p-4 border-t border-gray-100">
                </div>

                <!-- Subtotal da tabela -->
                <div class="flex justify-end items-center gap-5 px-5 py-3 bg-gray-50 border-t border-gray-100">
                    <span class="text-xs text-gray-500">Subtotal (<span id="count-itens">0</span> itens)</span>
                    <span id="display-subtotal" class="text-lg font-bold text-gray-700">R$ 0,00</span>
                </div>
            </div><!-- /card Itens -->

            <!-- Observações adicionais -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-comment-dots"></i>
                    <div><p class="title">Observações Adicionais</p></div>
                </div>
                <div class="p-5">
                    <textarea name="observacoes" id="observacoes"
                              class="fi" rows="5" style="resize:vertical"
                              placeholder="Exclusões de escopo, garantias, condicionantes..."><?= htmlspecialchars($def['observacoes']) ?></textarea>
                </div>
            </div>

            <div class="flex justify-between">
                <button type="button" class="btn-nav" onclick="goToStep(3)">
                    <i class="fas fa-arrow-left"></i> Cronograma
                </button>
                <button type="button" class="btn-nav btn-primary" onclick="goToStep(5)">
                    Próximo: Financeiro <i class="fas fa-arrow-right"></i>
                </button>
            </div>
        </div><!-- /step-panel-4 ITENS -->

        <!-- ════════════════════════════════════════════════════════════
             STEP 5 — FINANCEIRO
        ════════════════════════════════════════════════════════════ -->
        <div id="step-panel-5" class="step-content" role="tabpanel">

            <div class="card">
                <div class="card-header">
                    <i class="fas fa-calculator"></i>
                    <div>
                        <p class="title">Resumo Financeiro</p>
                        <p class="subtitle">Descontos, impostos e totais</p>
                    </div>
                </div>
                <div class="p-5 flex flex-col gap-5">

                    <!-- Composição por categoria -->
                    <div>
                        <p class="lbl">Composição por Categoria</p>
                        <div id="fin-categorias" class="flex flex-col gap-2">
                            <!-- Preenchido pelo JS -->
                            <p class="text-xs text-gray-400 italic">Nenhum item cadastrado ainda.</p>
                        </div>
                    </div>

                    <hr class="border-gray-100">

                    <!-- Desconto global -->
                    <div>
                        <label class="lbl">Desconto Global</label>
                        <div class="flex gap-2 items-center">
                            <select id="desconto_tipo" name="desconto_tipo"
                                    class="fi" style="width:64px">
                                <option value="percentual" <?= $def['desconto_tipo'] === 'percentual' ? 'selected' : '' ?>>%</option>
                                <option value="valor"      <?= $def['desconto_tipo'] === 'valor'      ? 'selected' : '' ?>>R$</option>
                            </select>
                            <input type="number" id="desconto_valor" name="desconto_valor"
                                   class="fi" style="width:140px"
                                   min="0" step="0.01"
                                   value="<?= (float)$def['desconto_valor'] ?>">
                        </div>
                    </div>

                    <!-- Impostos -->
                    <div>
                        <label class="lbl" for="impostos_perc">Impostos / Taxas (%)</label>
                        <input type="number" id="impostos_perc" name="impostos_perc"
                               class="fi" style="width:140px"
                               min="0" max="100" step="0.01"
                               value="<?= (float)$def['impostos_perc'] ?>">
                    </div>

                    <hr class="border-gray-100">

                    <!-- Linhas de resumo -->
                    <div class="flex flex-col gap-1">
                        <div class="resumo-linha">
                            <span class="text-gray-500">Subtotal (bruto)</span>
                            <span id="fin-subtotal-bruto" class="font-semibold text-gray-700">R$ 0,00</span>
                        </div>
                        <div class="resumo-linha">
                            <span class="text-gray-500">Desconto nos Itens</span>
                            <span id="fin-item-desc" class="font-semibold" style="color:var(--danger)">− R$ 0,00</span>
                        </div>
                        <div class="resumo-linha" style="border-bottom:1px dashed #E5E7EB;padding-bottom:4px">
                            <span class="text-gray-500">Subtotal (líquido)</span>
                            <span id="fin-subtotal" class="font-semibold text-gray-700">R$ 0,00</span>
                        </div>
                        <div class="resumo-linha">
                            <span id="fin-desc-label" class="text-gray-500">Desconto Global</span>
                            <span id="fin-desconto" class="font-semibold" style="color:var(--danger)">− R$ 0,00</span>
                        </div>
                        <div class="resumo-linha">
                            <span id="fin-imp-label" class="text-gray-500">Impostos (0%)</span>
                            <span id="fin-impostos" class="font-semibold text-gray-700">R$ 0,00</span>
                        </div>
                        <div class="resumo-total">
                            <span class="text-sm font-bold uppercase tracking-wide" style="color:var(--brand-dark)">Total Geral</span>
                            <span id="fin-total" class="text-2xl font-black" style="color:var(--brand)">R$ 0,00</span>
                        </div>
                    </div>
                </div>
            </div><!-- /card Financeiro Resumo -->

            <!-- Pagamento e prazo -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-credit-card"></i>
                    <div><p class="title">Pagamento e Prazo</p></div>
                </div>
                <div class="p-5 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="lbl" for="condicao_pagamento">Condição de Pagamento</label>
                        <select id="condicao_pagamento" name="condicao_pagamento" class="fi">
                            <option value="">— Selecione —</option>
                            <?php
                            $condicaoAtual = $def['condicao'];
                            $listaCondicoes = !empty($condicoes)
                                ? array_column($condicoes, 'descricao')
                                : $condicoesPagamento;
                            $isOutro = $condicaoAtual && !in_array($condicaoAtual, $listaCondicoes);
                            foreach ($listaCondicoes as $cp): ?>
                                <option value="<?= htmlspecialchars($cp) ?>"
                                    <?= $condicaoAtual === $cp ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cp) ?>
                                </option>
                            <?php endforeach; ?>
                            <option value="outro" <?= $isOutro ? 'selected' : '' ?>>
                                Outra (especificar)
                            </option>
                        </select>
                        <input type="text" id="condicao_pagamento_outro" class="fi mt-1"
                            value="<?= $isOutro ? htmlspecialchars($condicaoAtual) : '' ?>"
                            placeholder="Digite a condição de pagamento"
                            style="<?= $isOutro ? '' : 'display:none' ?>">
                    </div>
                    <div>
                        <label class="lbl" for="forma_pagamento">Forma de Pagamento</label>
                        <select id="forma_pagamento" name="forma_pagamento" class="fi">
                            <option value="">— Selecione —</option>
                            <?php foreach ($formasPagamento as $fp): ?>
                                <option value="<?= htmlspecialchars($fp) ?>"
                                    <?= $def['forma'] === $fp ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($fp) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Seletor de conta bancária (PIX e Transferência) -->
                    <div id="container-banco" class="md:col-span-2 hidden">
                        <label class="lbl" for="banco_id">Conta Bancária</label>
                        <select id="banco_id" class="fi" style="max-width:400px">
                            <option value="">— Selecione uma conta —</option>
                            <?php $bancoSelecionado = $def['banco_id'] ?? ''; ?>
                            <?php foreach ($bancos as $b): ?>
                                <?php
                                    $label = $b['nome'];
                                    if ($b['agencia'] && $b['conta']) $label .= " — Ag {$b['agencia']}" . ($b['agencia_dv'] ? "-{$b['agencia_dv']}" : '') . " / CC {$b['conta']}" . ($b['conta_dv'] ? "-{$b['conta_dv']}" : '');
                                    if (!empty($b['nome_titular'])) $label .= " — {$b['nome_titular']}";

                                    $dadosBancariosLabel = $b['nome'];
                                    if ($b['agencia'] && $b['conta']) $dadosBancariosLabel .= "\nAg {$b['agencia']}" . ($b['agencia_dv'] ? "-{$b['agencia_dv']}" : '') . " / CC {$b['conta']}" . ($b['conta_dv'] ? "-{$b['conta_dv']}" : '');
                                    if (!empty($b['nome_titular'])) $dadosBancariosLabel .= "\n{$b['nome_titular']}";
                                ?>
                                <option value="<?= $b['id'] ?>" <?= $bancoSelecionado == $b['id'] ? 'selected' : '' ?>
                                    data-pix-tipo="<?= htmlspecialchars($b['pix_tipo'] ?? '') ?>"
                                    data-pix-chave="<?= htmlspecialchars($b['pix_chave'] ?? '') ?>"
                                    data-dados-bancarios="<?= htmlspecialchars($dadosBancariosLabel) ?>">
                                    <?= htmlspecialchars($label) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="text-xs text-gray-400 mt-1">Selecione a conta para preencher automaticamente os dados abaixo.</p>
                        <p id="banco-selecionado-hint" class="text-xs font-semibold text-brand mt-1 hidden"></p>
                    </div>

                    <!-- Campos condicionais para PIX -->
                    <div id="container-pix" class="md:col-span-2 hidden grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="lbl" for="pix_tipo_chave">Tipo de Chave Pix</label>
                            <select id="pix_tipo_chave" name="pix_tipo_chave" class="fi">
                                <option value="">— Selecione —</option>
                                <option value="CPF" <?= ($def['pix_tipo_chave'] ?? '') === 'CPF' ? 'selected' : '' ?>>CPF</option>
                                <option value="CNPJ" <?= ($def['pix_tipo_chave'] ?? '') === 'CNPJ' ? 'selected' : '' ?>>CNPJ</option>
                                <option value="E-mail" <?= ($def['pix_tipo_chave'] ?? '') === 'E-mail' ? 'selected' : '' ?>>E-mail</option>
                                <option value="Celular" <?= ($def['pix_tipo_chave'] ?? '') === 'Celular' ? 'selected' : '' ?>>Celular</option>
                                <option value="Chave Aleatória" <?= ($def['pix_tipo_chave'] ?? '') === 'Chave Aleatória' ? 'selected' : '' ?>>Chave Aleatória</option>
                            </select>
                        </div>
                        <div>
                            <label class="lbl" for="pix_chave">Chave Pix</label>
                            <input type="text" id="pix_chave" name="pix_chave" class="fi" placeholder="Informe a chave" value="<?= htmlspecialchars($def['pix_chave'] ?? '') ?>">
                        </div>
                    </div>

                    <!-- Campos condicionais para Transferência Bancária -->
                    <div id="container-transferencia" class="md:col-span-2 hidden">
                        <label class="lbl" for="dados_bancarios">Dados Bancários (Banco, Agência, Conta)</label>
                        <textarea id="dados_bancarios" name="dados_bancarios" class="fi" rows="2" placeholder="Ex: Banco do Brasil - Ag: 1234-5 / CC: 56789-0"><?= htmlspecialchars($def['dados_bancarios'] ?? '') ?></textarea>
                    </div>

                    <div class="md:col-span-2">
                        <label class="lbl" for="prazo_execucao">Prazo de Execução / Início</label>
                        <input type="text" id="prazo_execucao" name="prazo_execucao"
                               class="fi"
                               placeholder="Ex: 24 dias corridos após aprovação"
                               value="<?= htmlspecialchars($def['prazo']) ?>">
                    </div>
                </div>
            </div>

            <!-- Assinaturas -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-file-signature"></i>
                    <div><p class="title">Assinaturas do PDF</p></div>
                </div>

                <!-- Contratada -->
                <div class="p-5 border-b border-gray-100">
                    <h4 class="text-xs font-bold text-gray-600 uppercase tracking-wider mb-3 flex items-center gap-2">
                        <i class="fas fa-building text-sky-500"></i> Assinatura da Contratada
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="lbl" for="assinatura_tipo">Tipo de Assinatura</label>
                            <select id="assinatura_tipo" name="assinatura_tipo" class="fi">
                                <option value="imagem" <?= $def['assinatura_tipo'] === 'imagem' ? 'selected' : '' ?>>Assinatura por Imagem</option>
                                <option value="certificado" <?= $def['assinatura_tipo'] === 'certificado' ? 'selected' : '' ?>>Assinatura via Certificado Digital</option>
                                <option value="nenhum" <?= $def['assinatura_tipo'] === 'nenhum' ? 'selected' : '' ?>>Nenhum (linha em branco)</option>
                            </select>
                        </div>
                    </div>

                    <div id="ctd_imagem_container" class="mt-3 <?= $def['assinatura_tipo'] !== 'imagem' ? 'hidden' : '' ?>">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
                            <div>
                                <label class="lbl">Upload de Imagem</label>
                                <input type="file" class="sig-img-input block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-bold file:bg-sky-50 file:text-sky-700 hover:file:bg-sky-100" data-preview="ctd_imagem_preview" data-hidden="ctd_imagem_hidden" data-remover-field="ctd_imagem_remover" accept="image/png,image/jpeg">
                                <button type="button" class="btn-limpar-sig text-xs text-red-500 hover:text-red-700 font-bold mt-1 <?= !$def['assinatura_imagem'] ? 'hidden' : '' ?>" data-hidden="ctd_imagem_hidden" data-preview="ctd_imagem_preview" data-remover-field="ctd_imagem_remover"><i class="fas fa-trash-alt"></i> Remover</button>
                                <p class="text-xs text-gray-400 mt-1">PNG ou JPG, máx. 500KB.</p>
                                <input type="hidden" name="assinatura_imagem" id="ctd_imagem_hidden" value="<?= htmlspecialchars($def['assinatura_imagem'] ?? '') ?>">
                                <input type="hidden" name="assinatura_imagem_remover" id="ctd_imagem_remover" value="0">
                            </div>
                            <div class="flex items-center justify-center">
                                <div class="text-center">
                                    <p class="text-xs font-bold text-gray-400 uppercase mb-2">Preview</p>
                                    <div id="ctd_imagem_preview" class="border-2 border-dashed border-gray-300 rounded-lg p-4 min-h-[60px] flex items-center justify-center bg-white">
                                        <?php if ($def['assinatura_imagem']): ?>
                                            <img src="data:image/png;base64,<?= $def['assinatura_imagem'] ?>" style="max-height:50px; max-width:200px; object-fit:contain;">
                                        <?php else: ?>
                                            <span class="text-xs text-gray-400">Nenhuma</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="ctd_certificado_container" class="mt-3 <?= $def['assinatura_tipo'] !== 'certificado' ? 'hidden' : '' ?>">
                        <div class="p-4 bg-amber-50 rounded-lg border border-amber-200 cert-container">
                            <div class="flex items-center justify-between mb-2">
                                <label class="lbl mb-0">Certificado Digital A1 (ICP-Brasil)</label>
                                <span id="ctd_cert_verified" class="hidden text-xs text-emerald-600 font-bold bg-emerald-50 px-2 py-1 rounded-full border border-emerald-200">
                                    <i class="fas fa-lock"></i> Senha verificada
                                </span>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="lbl">Arquivo (.pfx / .p12)</label>
                                    <input type="file" class="cert-file-input block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-bold file:bg-sky-50 file:text-sky-700 hover:file:bg-sky-100" accept=".pfx,.p12" data-target="ctd">
                                </div>
                                <div>
                                    <label class="lbl">Senha do Certificado</label>
                                    <input type="password" class="cert-password-input w-full border border-gray-300 rounded-lg px-3 py-2 text-sm outline-none" data-target="ctd" placeholder="Senha do .pfx">
                                    <a href="#" id="ctd_cert_change" class="hidden text-[10px] text-sky-600 hover:text-sky-800 font-bold mt-1 inline-block"><i class="fas fa-sync-alt"></i> Alterar certificado</a>
                                </div>
                            </div>
                            <div id="ctd_cert_status" class="mt-3"></div>
                            <div id="ctd_cert_info" class="mt-3 hidden">
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-xs bg-white p-3 rounded border border-gray-200">
                                    <div><span class="text-gray-400 block">Titular</span><span id="ctd_cert_nome" class="font-semibold">-</span></div>
                                    <div><span class="text-gray-400 block">CPF/CNPJ</span><span id="ctd_cert_doc" class="font-semibold">-</span></div>
                                    <div><span class="text-gray-400 block">Validade</span><span id="ctd_cert_validade" class="font-semibold">-</span></div>
                                    <div><span class="text-gray-400 block">ICP-Brasil</span><span id="ctd_cert_icp" class="font-semibold">-</span></div>
                                </div>
                            </div>
                            <input type="hidden" name="assinatura_certificado_nome" id="ctd_cert_nome_hidden" value="<?= htmlspecialchars($def['assinatura_certificado_nome']) ?>">
                            <input type="hidden" name="assinatura_certificado_cpf" id="ctd_cert_cpf_hidden" value="<?= htmlspecialchars($def['assinatura_certificado_cpf']) ?>">
                            <input type="hidden" name="assinatura_certificado_path" id="ctd_cert_path_hidden" value="">
                            <input type="hidden" name="assinatura_certificado_senha" id="ctd_cert_senha_hidden" value="">
                            <input type="hidden" name="assinatura_certificado_validade" id="ctd_cert_validade_hidden" value="">
                        </div>
                    </div>
                </div>

                <!-- Elaborador como Responsável Técnico -->
                <div class="p-5">
                    <label class="flex items-center gap-3 cursor-pointer group mb-3">
                        <input type="checkbox" name="assinatura_elaborador_responsavel" value="1" id="elab_checkbox" <?= $def['assinatura_elaborador_responsavel'] ? 'checked' : '' ?> class="w-4 h-4 text-sky-600 border-gray-300 rounded focus:ring-sky-500">
                        <span class="text-sm font-medium text-gray-700 group-hover:text-sky-600 transition">
                            <i class="fas fa-user-check text-sky-500 mr-1"></i>
                            Elaborador também assina como Responsável Técnico
                        </span>
                    </label>

                    <div id="elab_signature_fields" class="<?= !$def['assinatura_elaborador_responsavel'] ? 'hidden' : '' ?> space-y-3">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="lbl">Tipo de Assinatura do Elaborador</label>
                                <select name="assinatura_elaborador_tipo" id="elab_assinatura_tipo" class="fi">
                                    <option value="imagem" <?= $def['assinatura_elaborador_tipo'] === 'imagem' ? 'selected' : '' ?>>Assinatura por Imagem</option>
                                    <option value="certificado" <?= $def['assinatura_elaborador_tipo'] === 'certificado' ? 'selected' : '' ?>>Assinatura via Certificado Digital</option>
                                    <option value="nenhum" <?= $def['assinatura_elaborador_tipo'] === 'nenhum' ? 'selected' : '' ?>>Nenhum (linha em branco)</option>
                                </select>
                            </div>
                        </div>

                        <div id="elab_imagem_container" class="<?= $def['assinatura_elaborador_tipo'] !== 'imagem' ? 'hidden' : '' ?>">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
                                <div>
                                    <label class="lbl">Imagem de Assinatura</label>
                                    <input type="file" class="sig-img-input block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-bold file:bg-sky-50 file:text-sky-700 hover:file:bg-sky-100" data-preview="elab_imagem_preview" data-hidden="elab_imagem_hidden" data-remover-field="elab_imagem_remover" accept="image/png,image/jpeg">
                                    <button type="button" class="btn-limpar-sig text-xs text-red-500 hover:text-red-700 font-bold mt-1 <?= !$def['assinatura_elaborador_imagem'] ? 'hidden' : '' ?>" data-hidden="elab_imagem_hidden" data-preview="elab_imagem_preview" data-remover-field="elab_imagem_remover"><i class="fas fa-trash-alt"></i> Remover</button>
                                    <p class="text-xs text-gray-400 mt-1">PNG ou JPG, máx. 500KB.</p>
                                    <input type="hidden" name="assinatura_elaborador_imagem" id="elab_imagem_hidden" value="<?= htmlspecialchars($def['assinatura_elaborador_imagem'] ?? '') ?>">
                                    <input type="hidden" name="assinatura_elaborador_imagem_remover" id="elab_imagem_remover" value="0">
                                </div>
                                <div class="flex items-center justify-center">
                                    <div class="text-center">
                                        <p class="text-xs font-bold text-gray-400 uppercase mb-2">Preview</p>
                                        <div id="elab_imagem_preview" class="border-2 border-dashed border-gray-300 rounded-lg p-4 min-h-[60px] flex items-center justify-center bg-white">
                                            <?php if ($def['assinatura_elaborador_imagem']): ?>
                                                <img src="data:image/png;base64,<?= $def['assinatura_elaborador_imagem'] ?>" style="max-height:50px; max-width:200px; object-fit:contain;">
                                            <?php else: ?>
                                                <span class="text-xs text-gray-400">Nenhuma</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="elab_certificado_container" class="<?= $def['assinatura_elaborador_tipo'] !== 'certificado' ? 'hidden' : '' ?>">
                            <div class="p-4 bg-amber-50 rounded-lg border border-amber-200 cert-container">
                                <div class="flex items-center justify-between mb-2">
                                    <label class="lbl mb-0">Certificado Digital A1 do Elaborador</label>
                                    <span id="elab_cert_verified" class="hidden text-xs text-emerald-600 font-bold bg-emerald-50 px-2 py-1 rounded-full border border-emerald-200">
                                        <i class="fas fa-lock"></i> Senha verificada
                                    </span>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="lbl">Arquivo (.pfx / .p12)</label>
                                        <input type="file" class="cert-file-input block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-bold file:bg-sky-50 file:text-sky-700 hover:file:bg-sky-100" accept=".pfx,.p12" data-target="elab">
                                    </div>
                                    <div>
                                        <label class="lbl">Senha do Certificado</label>
                                        <input type="password" class="cert-password-input w-full border border-gray-300 rounded-lg px-3 py-2 text-sm outline-none" data-target="elab" placeholder="Senha do .pfx">
                                        <a href="#" id="elab_cert_change" class="hidden text-[10px] text-sky-600 hover:text-sky-800 font-bold mt-1 inline-block"><i class="fas fa-sync-alt"></i> Alterar certificado</a>
                                    </div>
                                </div>
                                <div id="elab_cert_status" class="mt-3"></div>
                                <div id="elab_cert_info" class="mt-3 hidden">
                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-xs bg-white p-3 rounded border border-gray-200">
                                        <div><span class="text-gray-400 block">Titular</span><span id="elab_cert_nome" class="font-semibold">-</span></div>
                                        <div><span class="text-gray-400 block">CPF/CNPJ</span><span id="elab_cert_doc" class="font-semibold">-</span></div>
                                        <div><span class="text-gray-400 block">Validade</span><span id="elab_cert_validade" class="font-semibold">-</span></div>
                                        <div><span class="text-gray-400 block">ICP-Brasil</span><span id="elab_cert_icp" class="font-semibold">-</span></div>
                                    </div>
                                </div>
                                <input type="hidden" name="assinatura_elaborador_certificado_nome" id="elab_cert_nome_hidden" value="<?= htmlspecialchars($def['assinatura_elaborador_certificado_nome']) ?>">
                                <input type="hidden" name="assinatura_elaborador_certificado_cpf" id="elab_cert_cpf_hidden" value="<?= htmlspecialchars($def['assinatura_elaborador_certificado_cpf']) ?>">
                                <input type="hidden" name="assinatura_elaborador_certificado_path" id="elab_cert_path_hidden" value="">
                                <input type="hidden" name="assinatura_elaborador_certificado_senha" id="elab_cert_senha_hidden" value="">
                                <input type="hidden" name="assinatura_elaborador_certificado_validade" id="elab_cert_validade_hidden" value="">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-between">
                <button type="button" class="btn-nav" onclick="goToStep(4)">
                    <i class="fas fa-arrow-left"></i> Itens
                </button>
                <button type="button" class="btn-nav btn-primary" onclick="goToStep(6)">
                    Revisar proposta <i class="fas fa-arrow-right"></i>
                </button>
            </div>
        </div><!-- /step-panel-5 FINANCEIRO -->

        <!-- ════════════════════════════════════════════════════════════
             STEP 6 — REVISÃO (preview estilo documento)
        ════════════════════════════════════════════════════════════ -->
        <div id="step-panel-6" class="step-content" role="tabpanel">

            <!-- Documento preview -->
            <div class="card" id="preview-documento">

                <!-- Cabeçalho do documento -->
                <div class="preview-header" style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:12px">
                    <div>
                        <p style="margin:0 0 6px;font-size:13px;text-transform:uppercase;letter-spacing:.05em;color:var(--brand);font-weight:700">
                            Proposta Técnica Orçamentária <span id="prev-header-codigo" style="background:var(--brand-light);padding:2px 8px;border-radius:6px;margin-left:4px;border:0.5px solid var(--brand-border)"></span>
                        </p>
                        <h3 id="prev-titulo" style="margin:0 0 8px;font-size:17px;font-weight:600;color:#111827">—</h3>
                        <div style="display:flex;gap:6px;flex-wrap:wrap">
                            <span id="prev-badge-contrato" class="preview-badge"
                                  style="background:var(--success-light);border:0.5px solid var(--success-border);color:var(--success)"></span>
                        </div>
                    </div>
                    <div style="text-align:right">
                        <p style="margin:0 0 2px;font-size:12px;color:#6B7280">
                            Emitida em <strong id="prev-data-emissao" style="color:#111827">—</strong>
                        </p>
                        <p style="margin:0 0 2px;font-size:12px;color:#6B7280">
                            Válida até <strong id="prev-data-validade" style="color:var(--brand)">—</strong>
                        </p>
                        <p style="margin:0;font-size:12px;color:#6B7280">
                            Elaborado por <strong id="prev-responsavel" style="color:#111827">—</strong>
                        </p>
                    </div>
                </div>

                <!-- Grade de campos do cliente -->
                <div class="preview-field-grid" style="border-bottom:0.5px solid #E5E7EB">
                    <div class="preview-field" style="border-right:0.5px solid #E5E7EB;border-bottom:0.5px solid #E5E7EB">
                        <p class="pf-label">Interessado</p>
                        <p id="prev-cliente-nome" class="pf-value">—</p>
                    </div>
                    <div class="preview-field" style="border-bottom:0.5px solid #E5E7EB">
                        <p class="pf-label">Representante</p>
                        <p id="prev-representante" class="pf-value">—</p>
                    </div>
                    <div class="preview-field" style="border-right:0.5px solid #E5E7EB">
                        <p class="pf-label">Município / Estado</p>
                        <p id="prev-municipio" class="pf-value">—</p>
                    </div>
                    <div class="preview-field" style="border-right:0.5px solid #E5E7EB">
                        <p class="pf-label">Telefone / Celular</p>
                        <p id="prev-telefone" class="pf-value">—</p>
                    </div>
                    <div class="preview-field">
                        <p class="pf-label">Área total</p>
                        <p id="prev-area" class="pf-value">—</p>
                    </div>
                </div>

                <!-- Escopo -->
                <div id="prev-escopo-wrapper" class="p-5 border-b border-gray-100 hidden">
                    <p class="lbl mb-2">Escopo / Objeto</p>
                    <p id="prev-escopo" class="text-sm text-gray-600 leading-relaxed">—</p>
                </div>

                <!-- Composição por categoria -->
                <div class="p-5 border-b border-gray-100">
                    <p class="lbl mb-3">Composição do Orçamento</p>
                    <div id="prev-composicao" class="flex flex-col gap-2">
                        <p class="text-xs text-gray-400 italic">Nenhum item.</p>
                    </div>
                </div>

                <!-- Totais -->
                <div class="p-5 border-b border-gray-100 flex flex-col gap-1">
                    <div class="resumo-linha text-sm">
                        <span class="text-gray-500">Subtotal</span>
                        <span id="prev-subtotal" class="font-semibold">R$ 0,00</span>
                    </div>
                    <div id="prev-desc-row" class="resumo-linha text-sm hidden">
                        <span id="prev-desc-label" class="text-gray-500">Desconto</span>
                        <span id="prev-desconto" class="font-semibold" style="color:var(--danger)">− R$ 0,00</span>
                    </div>
                    <div id="prev-imp-row" class="resumo-linha text-sm hidden">
                        <span id="prev-imp-label" class="text-gray-500">Impostos</span>
                        <span id="prev-impostos" class="font-semibold">R$ 0,00</span>
                    </div>
                </div>

                <!-- Rodapé: pagamento + total geral -->
                <div class="p-5 flex justify-between items-center flex-wrap gap-4">
                    <div>
                        <p class="lbl mb-1">Forma de Pagamento</p>
                        <p id="prev-pagamento" class="text-sm text-gray-700">—</p>
                        <p id="prev-prazo" class="text-xs text-gray-400 mt-1">—</p>
                    </div>
                    <div style="text-align:right">
                        <p class="lbl mb-1" style="color:var(--brand)">Total Geral</p>
                        <p id="prev-total-geral" class="font-black" style="font-size:28px;color:var(--brand)">R$ 0,00</p>
                    </div>
                </div>

            </div><!-- /preview-documento -->

            <!-- Observações no preview -->
            <div id="prev-obs-card" class="card hidden">
                <div class="card-header">
                    <i class="fas fa-sticky-note"></i>
                    <div><p class="title">Observações</p></div>
                </div>
                <div class="p-5">
                    <div id="prev-observacoes" class="text-sm text-gray-600 leading-relaxed whitespace-pre-wrap">—</div>
                </div>
            </div>

            <div class="flex justify-between items-center flex-wrap gap-3">
                <button type="button" class="btn-nav" onclick="goToStep(5)">
                    <i class="fas fa-arrow-left"></i> Editar
                </button>
                <button type="submit" id="btn-submit"
                        class="btn-nav btn-primary"
                        >
                    <i class="fas fa-check-circle"></i>
                    <?= $isEdicao ? 'Atualizar Proposta' : 'Gerar Proposta' ?>
                </button>
            </div>

        </div><!-- /step-panel-6 REVISÃO -->

    </form><!-- /form-orcamento -->

</div><!-- /container -->

<!-- ══════════════════════════════════════════════════════════════════════
     TEMPLATE DE LINHA — clonado via JS ao adicionar item
══════════════════════════════════════════════════════════════════════ -->
<template id="tmpl-item-row">
    <tr data-idx="" data-total="0">
        <td>
            <select name="item_categoria[]" class="fi item-categoria" style="font-size:11px;padding:5px 7px">
                <?php foreach ($categorias as $cat): ?>
                    <option value="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></option>
                <?php endforeach; ?>
                <option value="ADD_NEW" class="font-bold text-sky-600">+ Adicionar Nova...</option>
            </select>
        </td>
        <td>
            <input type="text" name="item_descricao[]" class="fi" style="font-size:12px;padding:5px 8px" placeholder="Descrição" required>
        </td>
        <td>
            <input type="text" name="item_detalhes[]" class="fi" style="font-size:12px;padding:5px 8px" placeholder="Detalhes (opcional)">
        </td>
        <td>
            <select name="item_unidade[]" class="fi item-unidade" style="font-size:12px;padding:5px 7px">
                <?php foreach ($unidades as $un): ?>
                    <option value="<?= htmlspecialchars($un) ?>"><?= htmlspecialchars($un) ?></option>
                <?php endforeach; ?>
                <option value="ADD_NEW" class="font-bold text-sky-600">+ Nova...</option>
            </select>
        </td>
        <td>
            <input type="number" name="item_quantidade[]" class="fi item-qty"
                   style="width:72px;font-size:12px;text-align:right;padding:5px 8px"
                   min="0.001" step="any" value="1">
        </td>
        <td>
            <input type="number" name="item_valor[]" class="fi item-vunit"
                   style="width:100px;font-size:12px;text-align:right;padding:5px 8px"
                   min="0" step="0.01" value="0">
        </td>
        <td>
            <input type="number" name="item_desconto[]" class="fi item-desc"
                   style="width:60px;font-size:12px;text-align:right;padding:5px 8px"
                   min="0" max="100" step="0.01" value="0">
        </td>
        <td class="item-total-cell" style="color:#111827">R$ 0,00</td>
        <td>
            <button type="button" class="btn-remover-item text-gray-300 hover:text-red-500 transition" title="Remover item">
                <i class="fas fa-trash-alt" style="font-size:13px"></i>
            </button>
        </td>
    </tr>
</template>

<!-- Template: TÍTULO DE SEÇÃO (gera "5.X Título" no PDF com tabela própria abaixo) -->
<template id="tmpl-titulo-row">
    <tr class="border-l-4 border-l-purple-500" style="background:rgba(238,237,254,.55)" data-is-legend="1">
        <td class="px-3 py-2" style="white-space:nowrap">
            <div class="flex items-center gap-1">
                <span style="font-size:8px;font-weight:900;text-transform:uppercase;letter-spacing:.04em;padding:2px 7px;border-radius:6px;border:0.5px solid #CECBF6;background:#EEEDFE;color:#3C3489">
                    <i class="fas fa-heading" style="font-size:8px;margin-right:3px"></i>Título
                </span>
                <input type="hidden" name="item_categoria[]" class="item-categoria" value="Titulo">
            </div>
        </td>
        <td colspan="5" style="padding:6px 8px">
            <input type="text" name="item_descricao[]"
                   class="fi" style="font-size:13px;font-weight:700;padding:5px 8px;border-color:#CECBF6;background:transparent"
                   placeholder="Ex: Planejamento logístico e coordenação geral do projeto"
                   required>
            <input type="hidden" name="item_detalhes[]" value="">
            <input type="hidden" name="item_unidade[]" value="un">
            <input type="hidden" name="item_quantidade[]" value="0">
            <input type="hidden" name="item_valor[]" value="0">
            <input type="hidden" name="item_desconto[]" value="0">
        </td>
        <td colspan="2"></td>
        <td class="text-center">
            <button type="button" class="btn-remover-item text-gray-300 hover:text-red-500 transition" title="Remover título">
                <i class="fas fa-trash-alt" style="font-size:13px"></i>
            </button>
        </td>
    </tr>
</template>

<!-- Template: TEXTO DESCRITIVO (parágrafo que aparece entre o título e a tabela no PDF) -->
<template id="tmpl-subtitulo-row">
    <tr class="border-l-4 border-l-sky-300" style="background:rgba(230,241,251,.4)" data-is-legend="1">
        <td class="px-3 py-2" style="white-space:nowrap">
            <div class="flex items-center gap-1">
                <span style="font-size:8px;font-weight:900;text-transform:uppercase;letter-spacing:.04em;padding:2px 7px;border-radius:6px;border:0.5px solid #93c5fd;background:#dbeafe;color:#1d4ed8">
                    <i class="fas fa-align-left" style="font-size:7px;margin-right:3px"></i>Texto
                </span>
                <input type="hidden" name="item_categoria[]" class="item-categoria" value="Subtitulo">
            </div>
        </td>
        <td colspan="7" style="padding:6px 8px">
            <textarea name="item_descricao[]"
                      class="fi !bg-transparent !border-none focus:!ring-0 resize-none overflow-hidden"
                      style="font-size:12px;color:#374151;min-height:36px;line-height:1.6;padding:4px 8px"
                      placeholder="Descreva as atividades desta seção (aparece como parágrafo no PDF)..."
                      oninput="this.style.height='';this.style.height=this.scrollHeight+'px'"></textarea>
            <input type="hidden" name="item_detalhes[]" value="">
            <input type="hidden" name="item_unidade[]" value="un">
            <input type="hidden" name="item_quantidade[]" value="0">
            <input type="hidden" name="item_valor[]" value="0">
            <input type="hidden" name="item_desconto[]" value="0">
        </td>
        <td class="text-center" style="vertical-align:middle">
            <button type="button" class="btn-remover-item text-gray-300 hover:text-red-500 transition" title="Remover texto">
                <i class="fas fa-trash-alt" style="font-size:13px"></i>
            </button>
        </td>
    </tr>
</template>

<!-- Template: LEGENDA LEGADA (mantida para compatibilidade retroativa) -->
<template id="tmpl-legend-row">
    <tr class="bg-slate-100/50 border-l-4 border-l-sky-500" data-is-legend="1">
        <td class="px-4 py-3">
            <div class="flex items-center h-full">
                <span class="text-[9px] font-black uppercase text-sky-700 bg-sky-100 px-2 py-1 rounded border border-sky-200">Legenda</span>
                <input type="hidden" name="item_categoria[]" class="item-categoria" value="Legenda">
            </div>
        </td>
        <td colspan="7">
            <textarea name="item_descricao[]"
                      class="fi !font-bold !bg-transparent !border-none focus:!ring-0 resize-none overflow-hidden"
                      style="font-size:13px;min-height:38px;line-height:1.5;padding:8px"
                      placeholder="Título da seção..."
                      oninput="this.style.height='';this.style.height=this.scrollHeight+'px'"
                      required></textarea>
            <input type="hidden" name="item_detalhes[]" value="">
            <input type="hidden" name="item_unidade[]" value="un">
            <input type="hidden" name="item_quantidade[]" value="0">
            <input type="hidden" name="item_valor[]" value="0">
            <input type="hidden" name="item_desconto[]" value="0">
        </td>
        <td class="text-center">
            <button type="button" class="btn-remover-item text-gray-300 hover:text-red-500 transition" title="Remover legenda">
                <i class="fas fa-trash-alt" style="font-size:13px"></i>
            </button>
        </td>
    </tr>
</template>

<!-- ══════════════════════════════════════════════════════════════════════
     JAVASCRIPT
══════════════════════════════════════════════════════════════════════ -->
<script>
const BASE_URL = '<?= BASE_URL ?>';

// ── Cores de categoria (mapeado do PHP) ─────────────────────────────
const CATEGORIA_CORES = <?= json_encode($categoriaCores, JSON_UNESCAPED_UNICODE) ?>;

// ── Máscara de Telefone Dinâmica (Global) ──
function formatPhone(value) {
    if (!value) return "";
    value = value.replace(/\D/g, "");
    if (value.length > 11) value = value.slice(0, 11);

    if (value.length > 10) {
        return value.replace(/^(\d{2})(\d{5})(\d{4}).*/, "($1) $2-$3");
    } else if (value.length > 6) {
        return value.replace(/^(\d{2})(\d{4})(\d{0,4}).*/, "($1) $2-$3");
    } else if (value.length > 2) {
        return value.replace(/^(\d{2})(\d{0,5})/, "($1) $2");
    } else {
        return value.length > 0 ? "(" + value : "";
    }
}

// ── Utilitários de formatação ────────────────────────────────────────
const fmtBRL = (v) =>
    new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(v || 0);

// ── Guia interativo da aba Itens ─────────────────────────────────────
let guiaItensExibido = false;

function exibirGuiaItens() {
    if (guiaItensExibido) return;
    guiaItensExibido = true;

    const passo1 = `<div class="flex items-start gap-2 mb-2">
        <span style="background:#6D28D9;color:#fff;border-radius:50%;min-width:28px;height:28px;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:13px">1</span>
        <div><strong style="color:#6D28D9;font-size:14px">Título de seção</strong>
        <p style="margin:2px 0 0;font-size:13px;color:#374151">Clique no botão <span style="background:#EEEDFE;color:#3C3489;padding:2px 8px;border-radius:4px;font-weight:600;font-size:11px"><i class="fas fa-heading"></i> Título de seção</span> para criar uma seção numerada (ex: <em>5.1 Planejamento</em>).</p></div>
    </div>`;
    const passo2 = `<div class="flex items-start gap-2 mb-2">
        <span style="background:#0284C7;color:#fff;border-radius:50%;min-width:28px;height:28px;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:13px">2</span>
        <div><strong style="color:#0284C7;font-size:14px">Texto descritivo</strong>
        <p style="margin:2px 0 0;font-size:13px;color:#374151">Clique no botão <span style="background:#dbeafe;color:#1d4ed8;padding:2px 8px;border-radius:4px;font-weight:600;font-size:11px"><i class="fas fa-align-left"></i> Texto descritivo</span> para adicionar um parágrafo explicativo abaixo do título.</p></div>
    </div>`;
    const passo3 = `<div class="flex items-start gap-2">
        <span style="background:#0369A1;color:#fff;border-radius:50%;min-width:28px;height:28px;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:13px">3</span>
        <div><strong style="color:#0369A1;font-size:14px">Adicionar item</strong>
        <p style="margin:2px 0 0;font-size:13px;color:#374151">Por fim, clique em <span style="background:#0284C7;color:#fff;padding:2px 8px;border-radius:4px;font-weight:600;font-size:11px"><i class="fas fa-plus"></i> Adicionar item</span> para inserir os produtos/serviços com valores e quantidades.</p></div>
    </div>`;

    Swal.fire({
        title: '<span style="font-size:20px;font-weight:800;color:#1F2937"><i class="fas fa-list-ol" style="color:#6D28D9;margin-right:8px"></i>Como preencher a aba Itens</span>',
        html: `<div style="text-align:left;line-height:1.6">
            <p style="font-size:13px;color:#4B5563;margin-bottom:12px">Siga a ordem recomendada para estruturar sua proposta:</p>
            ${passo1}${passo2}${passo3}
            <div style="margin-top:14px;padding:8px 12px;background:#F3F4F6;border-radius:6px;font-size:12px;color:#6B7280;text-align:center">
                <i class="fas fa-lightbulb" style="color:#F59E0B;margin-right:4px"></i>
                Você pode reordenar ou remover itens a qualquer momento.
            </div>
        </div>`,
        icon: null,
        showConfirmButton: true,
        confirmButtonText: '<i class="fas fa-check"></i> Entendi!',
        confirmButtonColor: '#6D28D9',
        showCancelButton: true,
        cancelButtonText: 'Não mostrar novamente',
        cancelButtonColor: '#9CA3AF',
        reverseButtons: true,
        width: 520,
        padding: '1.25rem',
        allowOutsideClick: true,
        customClass: {
            popup: 'rounded-2xl',
            confirmButton: '!rounded-lg !font-bold !text-sm !px-5',
            cancelButton: '!rounded-lg !text-sm',
        }
    });
}

// ── Estado do step atual ─────────────────────────────────────────────
let stepAtual = 1;

function goToStep(n) {
    // Oculta todos os painéis
    document.querySelectorAll('.step-content').forEach(p => p.classList.remove('active'));

    // Ativa o painel alvo
    const painel = document.getElementById('step-panel-' + n);
    if (painel) painel.classList.add('active');

    // Atualiza visual dos botões do stepper
    for (let i = 1; i <= 6; i++) {
        const btn   = document.getElementById('step-btn-' + i);
        const icon  = document.getElementById('step-icon-' + i);
        const label = document.getElementById('step-label-' + i);
        if (!btn) continue;

        btn.classList.remove('active', 'done');
        icon.classList.remove('active', 'done');
        label.classList.remove('active', 'done');

        if (i === n) {
            btn.classList.add('active');
            icon.classList.add('active');
            label.classList.add('active');
        } else if (i < n) {
            btn.classList.add('done');
            icon.classList.add('done');
            label.classList.add('done');
            // Ícone de check para steps concluídos
            icon.classList.remove('fa-file-alt','fa-building','fa-calendar-alt','fa-list-ol','fa-calculator','fa-eye');
            icon.classList.add('fa-check');
        }
    }

    stepAtual = n;

    // Ao entrar no step 4 (Itens), exibe guia interativo
    if (n === 4) exibirGuiaItens();

    // Ao entrar no step 6 (Revisão), atualiza o preview
    if (n === 6) atualizarPreview();

    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// ── Cálculo de item ──────────────────────────────────────────────────
function calcItemTotal(qty, vunit, desc) {
    return qty * vunit * (1 - (desc || 0) / 100);
}

// ── Atualiza linhas de subtotal por categoria na tabela ──────────────
function atualizarSubtotaisCategoria(tbody, porCategoria, porCategoriaQtde) {
    tbody.querySelectorAll('.cat-subtotal-row').forEach(el => el.remove());

    const rows = [...tbody.querySelectorAll('tr')];
    let currentCat = null;
    let catQtySum = 0;
    let catTotalSum = 0;
    let catItemCount = 0;
    let lastRowIndex = -1;

    for (let i = 0; i < rows.length; i++) {
        const row = rows[i];
        const cat = row.querySelector('.item-categoria')?.value || '';
        if (cat === 'Titulo' || cat === 'Subtitulo') continue;

        if (cat !== currentCat) {
            if (currentCat !== null && catItemCount > 0) {
                const subRow = document.createElement('tr');
                subRow.className = 'cat-subtotal-row';
                subRow.style.background = '#2563eb';
                subRow.style.color = '#fff';
                subRow.style.fontWeight = '700';
                subRow.style.fontSize = '12px';
                subRow.innerHTML =
                    '<td colspan="4" style="padding:8px 10px;text-align:right;border-bottom:0.5px solid #fff">' +
                    'Subtotal ' + currentCat + ' (' + catItemCount + ' ' + (catItemCount === 1 ? 'item' : 'itens') + ')' +
                    '</td>' +
                    '<td style="padding:8px 10px;text-align:right;border-bottom:0.5px solid #fff">' +
                    'Qtd: ' + (Number.isInteger(catQtySum) ? catQtySum : catQtySum.toFixed(3).replace('.', ',')) +
                    '</td>' +
                    '<td colspan="2" style="border-bottom:0.5px solid #fff"></td>' +
                    '<td style="padding:8px 10px;text-align:right;border-bottom:0.5px solid #fff;font-size:13px">' +
                    fmtBRL(catTotalSum) +
                    '</td>' +
                    '<td style="border-bottom:0.5px solid #fff"></td>';
                rows[lastRowIndex].after(subRow);
            }
            currentCat = cat;
            catQtySum = 0;
            catTotalSum = 0;
            catItemCount = 0;
        }

        catItemCount++;
        catQtySum += parseFloat(row.querySelector('.item-qty')?.value) || 0;
        catTotalSum += parseFloat(row.dataset.total) || 0;
        lastRowIndex = i;
    }

    if (currentCat !== null && catItemCount > 0) {
        const subRow = document.createElement('tr');
        subRow.className = 'cat-subtotal-row';
        subRow.style.background = '#2563eb';
        subRow.style.color = '#fff';
        subRow.style.fontWeight = '700';
        subRow.style.fontSize = '12px';
        subRow.innerHTML =
            '<td colspan="4" style="padding:8px 10px;text-align:right;border-bottom:0.5px solid #fff">' +
            'Subtotal ' + currentCat + ' (' + catItemCount + ' ' + (catItemCount === 1 ? 'item' : 'itens') + ')' +
            '</td>' +
            '<td style="padding:8px 10px;text-align:right;border-bottom:0.5px solid #fff">' +
            'Qtd: ' + (Number.isInteger(catQtySum) ? catQtySum : catQtySum.toFixed(3).replace('.', ',')) +
            '</td>' +
            '<td colspan="2" style="border-bottom:0.5px solid #fff"></td>' +
            '<td style="padding:8px 10px;text-align:right;border-bottom:0.5px solid #fff;font-size:13px">' +
            fmtBRL(catTotalSum) +
            '</td>' +
            '<td style="border-bottom:0.5px solid #fff"></td>';
        rows[lastRowIndex].after(subRow);
    }
}

// ── Recalcula todos os totais visíveis ───────────────────────────────
function recalcularTotais() {
    const tbody = document.getElementById('tbody-itens');
    if (!tbody) return;

    const rows  = tbody.querySelectorAll('tr');
    let subtotal = 0;
    let grossSubtotal = 0;
    const porCategoria = {};
    const porCategoriaQtde = {};

    rows.forEach(row => {
        const qty    = parseFloat(row.querySelector('.item-qty')?.value)   || 0;
        const vunit  = parseFloat(row.querySelector('.item-vunit')?.value) || 0;
        const desc   = parseFloat(row.querySelector('.item-desc')?.value)  || 0;
        const cat    = row.querySelector('.item-categoria')?.value || 'Outros';
        const total  = calcItemTotal(qty, vunit, desc);

        row.dataset.total = total.toFixed(2);
        const cell = row.querySelector('.item-total-cell');
        if (cell) cell.textContent = fmtBRL(total);

        if (cat === 'Titulo' || cat === 'Subtitulo') return;

        subtotal += total;
        grossSubtotal += qty * vunit;
        if (cat !== 'Legenda') {
            porCategoria[cat] = (porCategoria[cat] || 0) + total;
            porCategoriaQtde[cat] = (porCategoriaQtde[cat] || 0) + qty;
        }
    });

    const itemDiscount = grossSubtotal - subtotal;

    atualizarSubtotaisCategoria(tbody, porCategoria, porCategoriaQtde);

    // ── Totais por categoria (Step 3) ── (chips menores)
    const divCats3 = document.getElementById('totais-por-categoria');
    if (divCats3) {
        divCats3.innerHTML = '';
        const catsComValor = Object.entries(porCategoria).filter(([, val]) => val > 0);
        if (catsComValor.length === 0) {
            divCats3.innerHTML = '<p class="text-xs text-gray-400 italic">Nenhum item.</p>';
        }
        catsComValor.forEach(([cat, val]) => {
            const cor = CATEGORIA_CORES[cat] || CATEGORIA_CORES['Outros'];
            divCats3.insertAdjacentHTML('beforeend', `
                <div style="display:flex;align-items:center;gap:6px">
                    <span style="display:inline-block;font-size:7px;font-weight:800;padding:0.5px 5px;
                                 border-radius:999px;border:0.3px solid ${cor.border};
                                 background:${cor.bg};color:${cor.text};
                                 text-transform:uppercase;letter-spacing:.02em">${cat}</span>
                    <span style="font-size:11px;font-weight:600;color:#111827">${fmtBRL(val)}</span>
                </div>
            `);
        });
    }

    // ── Contador e subtotal (Step 3) ──
    const countEl = document.getElementById('count-itens');
    const subEl   = document.getElementById('display-subtotal');
    const itemCount = [...rows].filter(r => {
        const cat = r.querySelector('.item-categoria')?.value || '';
        return cat !== 'Titulo' && cat !== 'Subtitulo';
    }).length;
    if (countEl) countEl.textContent = itemCount;
    if (subEl)   subEl.textContent   = fmtBRL(subtotal);

    // ── Financeiro (Step 4) ──
    const descTipo  = document.getElementById('desconto_tipo')?.value  || 'percentual';
    const descValor = parseFloat(document.getElementById('desconto_valor')?.value) || 0;
    const impPerc   = parseFloat(document.getElementById('impostos_perc')?.value)  || 0;

    const descontoCalc = descTipo === 'percentual'
        ? subtotal * (descValor / 100)
        : descValor;
    const baseImp  = subtotal - descontoCalc;
    const impValor = baseImp * (impPerc / 100);
    const total    = baseImp + impValor;

    // Campos ocultos para o submit
    document.getElementById('hid-subtotal').value       = subtotal.toFixed(2);
    document.getElementById('hid-desconto-calc').value  = descontoCalc.toFixed(2);
    document.getElementById('hid-impostos-valor').value = impValor.toFixed(2);
    document.getElementById('hid-total-geral').value    = total.toFixed(2);

    // Linha de composição por categoria (Step 4)
    const divCats4 = document.getElementById('fin-categorias');
    if (divCats4) {
        divCats4.innerHTML = '';
        if (Object.keys(porCategoria).length === 0 || Object.values(porCategoria).every(v => v <= 0)) {
            divCats4.innerHTML = '<p class="text-xs text-gray-400 italic">Nenhum item cadastrado ainda.</p>';
        }
        Object.entries(porCategoria).filter(([, val]) => val > 0).forEach(([cat, val]) => {
            const cor = CATEGORIA_CORES[cat] || CATEGORIA_CORES['Outros'];
            const cnt = [...tbody.querySelectorAll('.item-categoria')]
                            .filter(s => s.value === cat).length;
            divCats4.insertAdjacentHTML('beforeend', `
                <div style="display:flex;justify-content:space-between;align-items:center;
                            padding:8px 12px;border-radius:8px;
                            background:#F9FAFB;border:0.3px solid #E5E7EB;margin-bottom:4px">
                    <div style="display:flex;align-items:center;gap:8px">
                        <span style="display:inline-block;font-size:7px;font-weight:800;padding:0.5px 5px;
                                     border-radius:999px;border:0.3px solid ${cor.border};
                                     background:${cor.bg};color:${cor.text};
                                     text-transform:uppercase;letter-spacing:.02em">${cat}</span>
                        <span style="font-size:11px;color:#6B7280">${cnt} ${cnt===1?'item':'itens'}</span>
                    </div>
                    <span style="font-size:14px;font-weight:600;color:#111827">${fmtBRL(val)}</span>
                </div>
            `);
        });
    }

    // Linhas de totais (Step 4)
    setEl('fin-subtotal-bruto', fmtBRL(grossSubtotal));
    setEl('fin-item-desc',      itemDiscount > 0 ? `− ${fmtBRL(itemDiscount)}` : fmtBRL(0));
    document.getElementById('fin-item-desc')?.style.setProperty('color', itemDiscount > 0 ? 'var(--danger)' : '#111827');
    setEl('fin-subtotal',  fmtBRL(subtotal));
    setEl('fin-desc-label', `Desconto Global${descTipo==='percentual' && descValor>0 ? ` (${descValor}%)`:''}`);
    setEl('fin-desconto',  descontoCalc > 0 ? `− ${fmtBRL(descontoCalc)}` : fmtBRL(0));
    document.getElementById('fin-desconto')?.style.setProperty('color', descontoCalc > 0 ? 'var(--danger)' : '#111827');
    setEl('fin-imp-label', `Impostos (${impPerc}%)`);
    setEl('fin-impostos',  fmtBRL(impValor));
    setEl('fin-total',     fmtBRL(total));

    return { subtotal, descontoCalc, impValor, total, porCategoria };
}

// Helper: atualiza texto de elemento por id
function setEl(id, txt) {
    const el = document.getElementById(id);
    if (el) el.textContent = txt;
}

// ── Atualiza preview (Step 5) ────────────────────────────────────────
function atualizarPreview() {
    const tots = recalcularTotais();

    // Identificação
    const titulo   = document.getElementById('titulo_proposta')?.value  || '—';
    const codigo   = document.getElementById('codigo')?.value           || '—';
    const versao   = document.getElementById('versao_documento')?.value || '—';
    const dataRaw  = document.getElementById('data_proposta')?.value;
    const validade = parseInt(document.getElementById('validade_proposta')?.value) || 30;

    let dataEmissao = '—', dataValidade = '—';
    if (dataRaw) {
        const d = new Date(dataRaw + 'T12:00:00');
        dataEmissao = d.toLocaleDateString('pt-BR');
        const dv = new Date(dataRaw + 'T12:00:00');
        dv.setDate(dv.getDate() + validade);
        dataValidade = dv.toLocaleDateString('pt-BR');
    }

    // Responsável
    const selResp = document.getElementById('responsavel_id');
    const nomeResp = selResp?.options[selResp.selectedIndex]?.text || '—';

    setEl('prev-titulo', titulo);
    setEl('prev-header-codigo', codigo);
    setEl('prev-badge-versao', versao);
    setEl('prev-data-emissao', dataEmissao);
    setEl('prev-data-validade', dataValidade);
    setEl('prev-responsavel', nomeResp);

    // Contrato badge
    const contrato = document.getElementById('contrato_id')?.value;
    const badgeCtEl = document.getElementById('prev-badge-contrato');
    if (badgeCtEl) {
        if (contrato) {
            const selCt = document.getElementById('contrato_id');
            const numCt = selCt?.options[selCt.selectedIndex]?.text || contrato;
            badgeCtEl.textContent = 'Contrato: ' + numCt;
            badgeCtEl.style.display = 'inline-block';
        } else {
            badgeCtEl.style.display = 'none';
        }
    }

    // Cliente
    const selCli = document.getElementById('select-cliente');
    const nomeCliente = selCli?.options[selCli.selectedIndex]?.text || '—';
    setEl('prev-cliente-nome', nomeCliente);
    setEl('prev-representante', document.getElementById('representante')?.value || '—');
    setEl('prev-municipio',     document.getElementById('municipio')?.value     || '—');
    setEl('prev-telefone',      document.getElementById('telefone_cliente')?.value || '—');
    setEl('prev-area',          document.getElementById('area')?.value          || '—');

    // Escopo
    const escopo = document.getElementById('escopo')?.value || '';
    const escopoWrapper = document.getElementById('prev-escopo-wrapper');
    if (escopoWrapper) {
        if (escopo.trim()) {
            escopoWrapper.classList.remove('hidden');
            setEl('prev-escopo', escopo);
        } else {
            escopoWrapper.classList.add('hidden');
        }
    }

    // Composição por categoria
    const compDiv = document.getElementById('prev-composicao');
    if (compDiv && tots) {
        compDiv.innerHTML = '';
        if (Object.keys(tots.porCategoria).length === 0 || Object.values(tots.porCategoria).every(v => v <= 0)) {
            compDiv.innerHTML = '<p class="text-xs text-gray-400 italic">Nenhum item.</p>';
        }
        const tbody = document.getElementById('tbody-itens');
        Object.entries(tots.porCategoria).filter(([, val]) => val > 0).forEach(([cat, val]) => {
            const cor = CATEGORIA_CORES[cat] || CATEGORIA_CORES['Outros'];
            const cnt = [...(tbody?.querySelectorAll('.item-categoria') || [])]
                            .filter(s => s.value === cat).length;
            compDiv.insertAdjacentHTML('beforeend', `
                <div style="display:flex;justify-content:space-between;align-items:center;
                            padding:8px 12px;border-radius:8px;background:#F9FAFB;border:0.3px solid #E5E7EB">
                    <div style="display:flex;align-items:center;gap:8px">
                        <span style="display:inline-block;font-size:7px;font-weight:800;padding:0.5px 5px;
                                     border-radius:999px;border:0.3px solid ${cor.border};
                                     background:${cor.bg};color:${cor.text};
                                     text-transform:uppercase;letter-spacing:.02em">${cat}</span>
                        <span style="font-size:11px;color:#6B7280">${cnt} ${cnt===1?'item':'itens'}</span>
                    </div>
                    <span style="font-size:13px;font-weight:600;color:#111827">${fmtBRL(val)}</span>
                </div>
            `);
        });
    }

    // Totais
    setEl('prev-subtotal', fmtBRL(tots.subtotal));

    const descRow = document.getElementById('prev-desc-row');
    if (descRow) {
        const descTipo  = document.getElementById('desconto_tipo')?.value || 'percentual';
        const descValor = parseFloat(document.getElementById('desconto_valor')?.value) || 0;
        if (tots.descontoCalc > 0) {
            descRow.classList.remove('hidden');
            setEl('prev-desc-label', `Desconto${descTipo==='percentual'&&descValor>0 ? ` (${descValor}%)`  :''}`);
            setEl('prev-desconto', `− ${fmtBRL(tots.descontoCalc)}`);
        } else {
            descRow.classList.add('hidden');
        }
    }

    const impRow = document.getElementById('prev-imp-row');
    if (impRow) {
        const impPerc = parseFloat(document.getElementById('impostos_perc')?.value) || 0;
        if (tots.impValor > 0) {
            impRow.classList.remove('hidden');
            setEl('prev-imp-label', `Impostos (${impPerc}%)`);
            setEl('prev-impostos', fmtBRL(tots.impValor));
        } else {
            impRow.classList.add('hidden');
        }
    }

    setEl('prev-total-geral', fmtBRL(tots.total));

    // Pagamento
    const forma    = document.getElementById('forma_pagamento')?.value   || '';
    const condicao = document.getElementById('condicao_pagamento')?.value || '';
    const prazo    = document.getElementById('prazo_execucao')?.value     || '';
    
    const selBancoPreview = document.getElementById('banco_id');
    const bancoNome = selBancoPreview?.options[selBancoPreview.selectedIndex]?.text || '';
    
    let infoPagamento = [forma, condicao].filter(Boolean).join(' · ');
    
    if (forma === 'Pix') {
        const tipoKey = document.getElementById('pix_tipo_chave')?.value;
        const keyVal = document.getElementById('pix_chave')?.value;
        if (tipoKey && keyVal) infoPagamento += ` (${tipoKey}: ${keyVal})`;
        if (bancoNome && selBancoPreview?.value) infoPagamento += ` — ${bancoNome}`;
    } else if (forma === 'Transferência Bancária') {
        const dadosBanc = document.getElementById('dados_bancarios')?.value;
        if (dadosBanc) infoPagamento += ` [${dadosBanc}]`;
    }
    setEl('prev-pagamento', infoPagamento || '—');
    setEl('prev-prazo', prazo || '');

    // Observações
    const obsCard = document.getElementById('prev-obs-card');
    const obsText = document.getElementById('observacoes')?.value || '';
    if (obsCard) {
        if (obsText.trim()) {
            obsCard.classList.remove('hidden');
            setEl('prev-observacoes', obsText);
        } else {
            obsCard.classList.add('hidden');
        }
    }
}

// ── Adicionar item ───────────────────────────────────────────────────
document.getElementById('btn-add-item')?.addEventListener('click', () => {
    const tmpl  = document.getElementById('tmpl-item-row');
    const tbody = document.getElementById('tbody-itens');
    if (!tmpl || !tbody) return;
    const clone = tmpl.content.cloneNode(true);
    tbody.appendChild(clone);
    const newRow = tbody.lastElementChild;
    bindItemListeners(newRow);
    
    // Adiciona listener para detecção de novos itens nos selects da nova linha
    newRow.querySelectorAll('select').forEach(sel => {
        sel.addEventListener('change', handleAddNewOption);
    });
    recalcularTotais();
});

// ── Adicionar Título de Seção ──────────────────────────────────────────
document.getElementById('btn-add-titulo')?.addEventListener('click', () => {
    const tmpl  = document.getElementById('tmpl-titulo-row');
    const tbody = document.getElementById('tbody-itens');
    if (!tmpl || !tbody) return;
    tbody.appendChild(tmpl.content.cloneNode(true));
    recalcularTotais();
});

// ── Adicionar Texto Descritivo ─────────────────────────────────────────
document.getElementById('btn-add-subtitulo')?.addEventListener('click', () => {
    const tmpl  = document.getElementById('tmpl-subtitulo-row');
    const tbody = document.getElementById('tbody-itens');
    if (!tmpl || !tbody) return;
    tbody.appendChild(tmpl.content.cloneNode(true));
    recalcularTotais();
});

/**
 * Lógica para incluir novas opções "on-the-fly" nos selects
 */
function handleAddNewOption(e) {
    const select = e.target;
    if (select.value !== 'ADD_NEW') return;

    const isCat = select.classList.contains('item-categoria');
    const type = isCat ? 'Categoria' : 'Unidade';
    const endpoint = isCat ? 'addItemCategoriaAjax' : 'addItemUnidadeAjax';
    const newValue = prompt(`Informe o nome da nova ${type}:`);

    if (newValue && newValue.trim() !== '') {
        const cleanValue = newValue.trim();
        const csrfToken = document.querySelector('input[name="csrf_token"]')?.value;

        const fd = new FormData();
        fd.append('nome', cleanValue);
        fd.append('csrf_token', csrfToken);

        // Salva no banco de dados via AJAX
        fetch(`${BASE_URL}/orcamento/${endpoint}`, {
            method: 'POST',
            body: fd
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                // Atualiza todos os selects do mesmo tipo na tela
                const selector = isCat ? '.item-categoria' : '.item-unidade';
                document.querySelectorAll(selector).forEach(s => {
                    if (![...s.options].some(opt => opt.value === cleanValue)) {
                        s.add(new Option(cleanValue, cleanValue), s.options.length - 1);
                    }
                });
                select.value = cleanValue;
                recalcularTotais();
            } else {
                alert(data.message || 'Erro ao salvar nova opção.');
                select.selectedIndex = 0;
            }
        })
        .catch(err => {
            console.error('Erro:', err);
            select.selectedIndex = 0;
        });
    } else {
        select.selectedIndex = 0;
    }
}

// ── Remover item (delegação) ─────────────────────────────────────────
document.getElementById('tbody-itens')?.addEventListener('click', e => {
    const btn = e.target.closest('.btn-remover-item');
    if (btn) {
        btn.closest('tr').remove();
        recalcularTotais();
    }
});

// ── Binds nos inputs de linha ─────────────────────────────────────────
function bindItemListeners(row) {
    row.querySelectorAll('.item-qty, .item-vunit, .item-desc, .item-categoria')
       .forEach(el => el.addEventListener('input', recalcularTotais));
}

// Bind nas linhas existentes (PHP)
document.querySelectorAll('#tbody-itens tr').forEach(bindItemListeners);
// Bind nos selects iniciais para permitir adição de novos
document.querySelectorAll('#tbody-itens select').forEach(sel => {
    sel.addEventListener('change', handleAddNewOption);
});

// ════════════════════════════════════════════════════════════════════
// TABELA — CONTEXTUALIZAÇÃO (Empreendedor / Faixa de domínio / KM / Município / Área)
// ════════════════════════════════════════════════════════════════════
(function () {
    const tbody       = document.getElementById('tbody-contextualizacao');
    const btnAdd       = document.getElementById('btn-add-contexto');
    const totalCell    = document.getElementById('contexto-total-area');
    const hiddenJson   = document.getElementById('contextualizacao_json');
    const hiddenArea   = document.getElementById('area');
    const hiddenMun    = document.getElementById('municipio');

    function parseNum(v) {
        if (!v) return 0;
        const n = parseFloat(String(v).replace(/\./g, '').replace(',', '.'));
        return isNaN(n) ? 0 : n;
    }
    function fmtNum(n) {
        return n.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function novaLinhaContexto(dados) {
        dados = dados || {};
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td><input type="text" class="fi ctx-empreendedor" placeholder="Ex: FCA" value="${dados.empreendedor ?? ''}"></td>
            <td><input type="text" class="fi ctx-faixa" placeholder="Ex: Dentro / Fora" value="${dados.faixa || ''}"></td>
            <td><input type="text" class="fi ctx-km" placeholder="Ex: KM 137+270" value="${dados.km ?? ''}"></td>
            <td><input type="text" class="fi ctx-municipio" placeholder="Ex: Cachoeira – BA" value="${dados.municipio ?? ''}"></td>
            <td><input type="text" class="fi ctx-area" placeholder="0,00" value="${dados.area ?? ''}"></td>
            <td class="text-center">
                <button type="button" class="btn-remover-contexto text-gray-300 hover:text-red-500 transition" title="Remover linha">
                    <i class="fas fa-trash-alt" style="font-size:13px"></i>
                </button>
            </td>`;
        tbody.appendChild(tr);
        tr.querySelectorAll('input, select').forEach(el => {
            el.addEventListener('input', syncContexto);
            el.addEventListener('change', syncContexto);
        });
    }

    function syncContexto() {
        const linhas = [];
        let totalArea = 0;
        tbody.querySelectorAll('tr').forEach(tr => {
            const empreendedor = tr.querySelector('.ctx-empreendedor')?.value || '';
            const faixa        = tr.querySelector('.ctx-faixa')?.value || '';
            const km            = tr.querySelector('.ctx-km')?.value || '';
            const municipio     = tr.querySelector('.ctx-municipio')?.value || '';
            const area          = tr.querySelector('.ctx-area')?.value || '';
            totalArea += parseNum(area);
            linhas.push({ empreendedor, faixa, km, municipio, area });
        });
        if (totalCell) totalCell.textContent = fmtNum(totalArea);
        if (hiddenArea) hiddenArea.value = fmtNum(totalArea);
        if (hiddenMun && linhas.length) hiddenMun.value = linhas[0].municipio || '';
        const ocultarVazias = document.getElementById('ctx-ocultar-vazias')?.checked ?? true;
        const textoIntro = document.getElementById('ctx-texto-intro')?.value ?? '';
        if (hiddenJson) hiddenJson.value = JSON.stringify({ linhas, ocultar_vazias: ocultarVazias, texto_intro: textoIntro });
    }

    tbody?.addEventListener('click', e => {
        const btn = e.target.closest('.btn-remover-contexto');
        if (!btn) return;
        if (tbody.querySelectorAll('tr').length <= 1) { btn.closest('tr').querySelectorAll('input').forEach(i => i.value=''); syncContexto(); return; }
        btn.closest('tr').remove();
        syncContexto();
    });

    btnAdd?.addEventListener('click', () => { novaLinhaContexto(); syncContexto(); });

    document.getElementById('ctx-ocultar-vazias')?.addEventListener('change', syncContexto);
    document.getElementById('ctx-texto-intro')?.addEventListener('input', syncContexto);

    // Carga inicial — uma linha vazia (ou a partir de contextualizacao_json salvo, se existir)
    const initial = <?= !empty($orc['contextualizacao_json']) ? $orc['contextualizacao_json'] : '[]' ?>;
    let linhasIniciais;
    if (Array.isArray(initial)) {
        linhasIniciais = initial;
    } else if (typeof initial === 'object' && initial !== null) {
        linhasIniciais = Array.isArray(initial.linhas) ? initial.linhas : [];
        const cb = document.getElementById('ctx-ocultar-vazias');
        if (cb && typeof initial.ocultar_vazias === 'boolean') cb.checked = initial.ocultar_vazias;
        const ti = document.getElementById('ctx-texto-intro');
        if (ti && typeof initial.texto_intro === 'string') ti.value = initial.texto_intro;
    } else {
        linhasIniciais = [];
    }
    if (linhasIniciais.length) {
        linhasIniciais.forEach(novaLinhaContexto);
    } else {
        novaLinhaContexto({ empreendedor: 'FCA', faixa: 'Dentro', km: '', municipio: '<?= htmlspecialchars($def['cliente_municipio']) ?>', area: '' });
    }
    syncContexto();
})();

// ════════════════════════════════════════════════════════════════════
// TABELA — EQUIPE DO PROJETO
// ════════════════════════════════════════════════════════════════════
(function () {
    const tbody     = document.getElementById('tbody-equipe');
    const btnAdd    = document.getElementById('btn-add-equipe');
    const hiddenJson = document.getElementById('equipe_json');

    function novaLinhaEquipe(dados) {
        dados = dados || {};
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td><input type="text" class="fi eq-profissional" placeholder="Ex: Engenheiro Florestal Pleno" value="${dados.profissional ?? ''}"></td>
            <td>
                <select class="fi eq-campo">
                    <option value="Escritório" ${dados.campo === 'Escritório' ? 'selected' : ''}>Escritório</option>
                    <option value="Campo" ${dados.campo === 'Campo' ? 'selected' : ''}>Campo</option>
                    <option value="Escritório e Campo" ${dados.campo === 'Escritório e Campo' ? 'selected' : ''}>Escritório e Campo</option>
                </select>
            </td>
            <td><textarea class="fi eq-funcao" rows="2" placeholder="Descreva a função..."
                          oninput="this.style.height='';this.style.height=this.scrollHeight+'px'">${dados.funcao ?? ''}</textarea></td>
            <td class="text-center">
                <button type="button" class="btn-remover-equipe text-gray-300 hover:text-red-500 transition" title="Remover profissional">
                    <i class="fas fa-trash-alt" style="font-size:13px"></i>
                </button>
            </td>`;
        tbody.appendChild(tr);
        tr.querySelectorAll('input, select, textarea').forEach(el => {
            el.addEventListener('input', syncEquipe);
            el.addEventListener('change', syncEquipe);
        });
        const ta = tr.querySelector('.eq-funcao');
        if (ta) { ta.style.height = ''; ta.style.height = ta.scrollHeight + 'px'; }
    }

    function syncEquipe() {
        const membros = [];
        tbody.querySelectorAll('tr').forEach(tr => {
            membros.push({
                profissional: tr.querySelector('.eq-profissional')?.value || '',
                campo:        tr.querySelector('.eq-campo')?.value || '',
                funcao:       tr.querySelector('.eq-funcao')?.value || '',
            });
        });
        const textoIntro = document.getElementById('eq-texto-intro')?.value ?? '';
        if (hiddenJson) hiddenJson.value = JSON.stringify({ membros, texto_intro: textoIntro });
    }

    tbody?.addEventListener('click', e => {
        const btn = e.target.closest('.btn-remover-equipe');
        if (!btn) return;
        if (tbody.querySelectorAll('tr').length <= 1) { btn.closest('tr').querySelectorAll('input,textarea').forEach(i => i.value=''); syncEquipe(); return; }
        btn.closest('tr').remove();
        syncEquipe();
    });

    btnAdd?.addEventListener('click', () => { novaLinhaEquipe(); syncEquipe(); });

    document.getElementById('eq-texto-intro')?.addEventListener('input', syncEquipe);

    // Carga inicial — a partir de equipe_json salvo, ou os 4 perfis padrão do projeto
    const initialEquipe = <?= !empty($orc['equipe_json']) ? $orc['equipe_json'] : '[]' ?>;
    let membrosIniciais;
    if (Array.isArray(initialEquipe)) {
        membrosIniciais = initialEquipe;
    } else if (typeof initialEquipe === 'object' && initialEquipe !== null) {
        membrosIniciais = Array.isArray(initialEquipe.membros) ? initialEquipe.membros : [];
        const ti = document.getElementById('eq-texto-intro');
        if (ti && typeof initialEquipe.texto_intro === 'string') ti.value = initialEquipe.texto_intro;
    } else {
        membrosIniciais = [];
    }
    const equipePadrao = [
        { profissional: 'Serviços de Coordenadoria', campo: 'Escritório',
          funcao: 'Coordenação geral do projeto, preparação e controle orçamentário, orientação e determinação de funções e atividades a serem desenvolvidas, planejamento logístico, planejamento de cronograma de execução e etc.' },
        { profissional: 'Engenheiro Florestal Pleno', campo: 'Escritório e Campo',
          funcao: 'Coleta de dados em campo de inventário florestal, orientação de equipes em campo, garantir seguimento de procedimentos técnicos e de segurança do trabalho, garantir qualidade de dados, liderança geral da equipe. Identificação botânica de indivíduos florestais a nível científico, auxílio de medição dendrométrica auxílio em atividades em geral.' },
        { profissional: 'Engenheiro Florestal Sênior', campo: 'Escritório e Campo',
          funcao: 'Coleta de dados em campo de inventário florestal, orientação de equipes em campo, garantir seguimento de procedimentos técnicos e de segurança do trabalho, garantir qualidade de dados, liderança geral da equipe. Elaboração de Relatórios Técnicos.' },
        { profissional: 'Editoração de Relatórios', campo: 'Escritório',
          funcao: 'Padronização de documentos, revisão do relatório, nomenclatura e estruturação de projetos. Editoração em geral.' },
    ];
    const baseEquipe = membrosIniciais.length ? membrosIniciais : equipePadrao;
    baseEquipe.forEach(novaLinhaEquipe);
    syncEquipe();
})();

// Bind nos campos financeiros que influenciam os totais
['desconto_tipo','desconto_valor','impostos_perc'].forEach(id => {
    document.getElementById(id)?.addEventListener('input', recalcularTotais);
    document.getElementById(id)?.addEventListener('change', recalcularTotais);
});

// ── Preview de data de validade ───────────────────────────────────────
(function () {
    const input = document.getElementById('validade_proposta');
    const prev  = document.getElementById('data-validade-preview');
    const dataProp = document.getElementById('data_proposta');

    function update() {
        const dias = parseInt(input?.value) || 0;
        if (!dias || !dataProp?.value) { if(prev) prev.textContent = '—'; return; }
        const d = new Date(dataProp.value + 'T12:00:00');
        d.setDate(d.getDate() + dias);
        if (prev) prev.textContent = d.toLocaleDateString('pt-BR');
    }

    input?.addEventListener('input', update);
    dataProp?.addEventListener('change', update);
    update();
})();

// ── Lock / unlock versão do documento ────────────────────────────────
document.getElementById('permitir-edicao-versao')?.addEventListener('change', function () {
    const input  = document.getElementById('versao_documento');
    const icone  = document.getElementById('icon-lock-versao');
    if (!input) return;

    if (this.checked) {
        input.readOnly = false;
        input.style.background = '#fff';
        input.style.color      = '#111827';
        input.style.cursor     = 'text';
        input.style.boxShadow  = '0 0 0 3px rgba(37,99,235,.12)';
        input.style.borderColor = 'var(--brand)';
        if (icone) { icone.classList.replace('fa-lock', 'fa-lock-open'); icone.style.color = 'var(--brand)'; }
    } else {
        input.readOnly = true;
        input.style.background  = '#F9FAFB';
        input.style.color       = '#9CA3AF';
        input.style.cursor      = 'not-allowed';
        input.style.boxShadow   = 'none';
        input.style.borderColor = '#D1D5DB';
        if (icone) { icone.classList.replace('fa-lock-open', 'fa-lock'); icone.style.color = ''; }
    }
});

// ── Toggle projeto / contrato vinculado ───────────────────────────────
document.getElementById('has-projeto-checkbox')?.addEventListener('change', function () {
    const sec = document.getElementById('section-projeto');
    const sel = document.getElementById('projeto_id');
    if (sec) sec.classList.toggle('hidden', !this.checked);
    if (!this.checked && sel) {
        sel.value = '';
        document.getElementById('project-details-container')?.classList.add('hidden');
    }
});

document.getElementById('has-contrato-checkbox')?.addEventListener('change', function () {
    const sec = document.getElementById('section-contrato');
    const sel = document.getElementById('contrato_id');
    if (sec) sec.classList.toggle('hidden', !this.checked);
    if (!this.checked) {
        if (sel) sel.value = '';
        document.getElementById('section-contrato-detalhes')?.classList.add('hidden');
    }
});

// ── Sigla automática ao selecionar cliente ────────────────────────────
document.addEventListener('DOMContentLoaded', () => {

    // ── Toggle campos de pagamento (fora do setTimeout para ficar acessível globalmente) ──
    const selForma = document.getElementById('forma_pagamento');
    const contBanco = document.getElementById('container-banco');
    const contPix = document.getElementById('container-pix');
    const contTransf = document.getElementById('container-transferencia');
    const selBanco = document.getElementById('banco_id');

    window.preencherDadosBanco = function () {
        if (!selBanco) return;
        const opt = selBanco.options[selBanco.selectedIndex];
        if (!opt || !opt.value) return;

        const pixTipoRaw = opt.getAttribute('data-pix-tipo') || '';
        const pixChave = opt.getAttribute('data-pix-chave') || '';
        const dadosBancarios = opt.getAttribute('data-dados-bancarios') || '';

        const mapaTipo = {
            'cpf_cnpj': pixChave.length > 14 ? 'CNPJ' : 'CPF',
            'email': 'E-mail',
            'telefone': 'Celular',
            'aleatoria': 'Chave Aleatória',
        };
        const pixTipo = mapaTipo[pixTipoRaw] || pixTipoRaw;

        const pixTipoField = document.getElementById('pix_tipo_chave');
        const pixChaveField = document.getElementById('pix_chave');
        const dadosBancField = document.getElementById('dados_bancarios');

        if (pixTipoField) pixTipoField.value = pixTipo;
        if (pixChaveField) pixChaveField.value = pixChave;
        if (dadosBancField) dadosBancField.value = dadosBancarios;

        const optText = opt.text;
        const bancoHint = document.getElementById('banco-selecionado-hint');
        if (bancoHint) {
            bancoHint.textContent = optText ? 'Conta: ' + optText : '';
            bancoHint.classList.toggle('hidden', !optText);
        }
    };

    function updatePaymentFields() {
        const val = selForma.value;
        const isPix = val === 'Pix';
        const isTransf = val === 'Transferência Bancária';
        const mostraBanco = isPix || isTransf;

        contBanco?.classList.toggle('hidden', !mostraBanco);
        contPix?.classList.toggle('hidden', !mostraBanco);
        contTransf?.classList.toggle('hidden', !isTransf);

        if (mostraBanco && selBanco.value) {
            window.preencherDadosBanco();
        }
    }

    selForma?.addEventListener('change', updatePaymentFields);
    selBanco?.addEventListener('change', window.preencherDadosBanco);
    updatePaymentFields();

    setTimeout(() => {
        const selCli = document.getElementById('select-cliente');

        function updateSigla() {
            const selCliScratch = document.getElementById('select-cliente-scratch');
            const selContrato = document.getElementById('contrato_id');

            // Tenta obter o ID de forma mais robusta, priorizando o seletor que tem valor no momento
            let clienteId = "";
            let activeSelect = null;

            if (selCli && selCli.value) { clienteId = selCli.value; activeSelect = selCli; }
            else if (selCliScratch && selCliScratch.value) { clienteId = selCliScratch.value; activeSelect = selCliScratch; }

            const opt = activeSelect ? activeSelect.options[activeSelect.selectedIndex] : null;

            if (!clienteId || !opt || !opt.value || opt.value === "") {
                if (selContrato) {
                    selContrato.innerHTML = '<option value="">— Selecione um cliente primeiro —</option>';
                    selContrato.disabled = true;
                }
                return;
            }
            const sigla = opt?.getAttribute('data-sigla') || '';
            const email = opt?.getAttribute('data-email') || '';
            const representante = opt?.getAttribute('data-representante') || '';
            const bairro = opt?.getAttribute('data-bairro') || '';
            const clienteMunicipio = opt?.getAttribute('data-municipio') || '';
            const clienteUf = opt?.getAttribute('data-uf') || '';
            const telefone = opt?.getAttribute('data-telefone') || '';
            const documento = opt?.getAttribute('data-documento') || '';
            
            // Tenta extrair dados dos JSONs do cliente para preferências automáticas
            let financeiro = {};
            let comercial = {};
            try {
                const finRaw = opt?.getAttribute('data-financeiro');
                if (finRaw) financeiro = JSON.parse(finRaw);
                
                const comRaw = opt?.getAttribute('data-comercial');
                if (comRaw) comercial = JSON.parse(comRaw);
            } catch (err) {
                console.warn('Erro ao processar preferências do cliente:', err);
            }

            // Preenche Documento (apenas visualização)
            const docInput = document.getElementById('cliente_documento');
            if (docInput) {
                let docFormatado = documento;
                if (documento.length === 11) docFormatado = documento.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, "$1.$2.$3-$4");
                else if (documento.length === 14) docFormatado = documento.replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/, "$1.$2.$3/$4-$5");
                docInput.value = docFormatado;
            }

            const siglaInput = document.getElementById('cliente_sigla_input');
            if (siglaInput) siglaInput.value = sigla.trim();

            // Preenche representante e e-mail se estiverem vazios
            const repInput = document.getElementById('representante');
            if (repInput && !repInput.value && representante) repInput.value = representante;

            const emailInput = document.getElementById('email_cliente');
            if (emailInput && !emailInput.value && email) emailInput.value = email;

            // Preenche telefone se estiver vazio
            const telInput = document.getElementById('telefone_cliente');
            if (telInput && telefone) telInput.value = formatPhone(telefone);

            const logradouro = opt?.getAttribute('data-logradouro') || '';
            const numero = opt?.getAttribute('data-numero') || '';
            const complemento = opt?.getAttribute('data-complemento') || '';

            const logradouroInput = document.getElementById('cliente_logradouro');
            const numeroInput = document.getElementById('cliente_numero');
            const complementoInput = document.getElementById('cliente_complemento');
            const bairroInput = document.getElementById('cliente_bairro');
            const clienteMunicipioInput = document.getElementById('cliente_municipio');
            const clienteUfInput = document.getElementById('cliente_uf');

            if (logradouroInput) logradouroInput.value = logradouro;
            if (numeroInput) numeroInput.value = numero;
            if (complementoInput) complementoInput.value = complemento;
            if (bairroInput) bairroInput.value = bairro;
            if (clienteMunicipioInput) clienteMunicipioInput.value = clienteMunicipio;
            if (clienteUfInput) clienteUfInput.value = clienteUf;

            // Preenche Município / Estado se estiver vazio
            const munInput = document.getElementById('municipio');
            if (munInput && !munInput.value && clienteMunicipio) {
                munInput.value = clienteMunicipio;
            }

            // Preenche preferências financeiras se for um novo registro
            const isEdicao = <?= json_encode($isEdicao) ?>;
            if (!isEdicao) {
                const condSelect = document.getElementById('condicao_pagamento');
                const formaSelect = document.getElementById('forma_pagamento');
                const prazoInput = document.getElementById('prazo_execucao');
                
                // Procura chaves comuns nos JSONs de Clientes do CRM
                const condPref = financeiro.condicao_pagamento_preferencial || financeiro.condicao_pagamento || comercial.condicao_pagamento || '';
                const formaPref = financeiro.forma_pagamento_preferencial || financeiro.forma_pagamento || comercial.forma_pagamento || '';
                const prazoPref = comercial.prazo_execucao_padrao || comercial.prazo_entrega || '';

                if (condSelect && condPref) {
                    const hasOption = [...condSelect.options].some(o => o.value === condPref);
                    if (hasOption) condSelect.value = condPref;
                    else {
                        // Se não existe na lista, adiciona como opção temporária
                        const newOpt = new Option(condPref, condPref);
                        condSelect.add(newOpt, condSelect.options[condSelect.options.length - 1]);
                        condSelect.value = condPref;
                    }
                }
                
                if (formaSelect && formaPref) {
                    const hasOption = [...formaSelect.options].some(o => o.value === formaPref);
                    if (hasOption) formaSelect.value = formaPref;
                }

                if (prazoInput && !prazoInput.value && prazoPref) {
                    prazoInput.value = prazoPref;
                }
            }

            // Gerar número da proposta automaticamente se for novo registro e cliente estiver selecionado
            const codigoInput = document.getElementById('codigo');

            if (clienteId && codigoInput && !isEdicao) {
                fetch('<?= BASE_URL ?>/orcamento/getProximoNumeroAjax/' + clienteId, {
                    headers: {
                        'Accept': 'application/json'
                    }
                })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            codigoInput.value = data.numero;
                        }
                    })
                    .catch(err => console.error('Erro ao gerar número:', err));
            }

            // Busca contratos do cliente via AJAX para atualizar o dropdown
            const containerCtDet = document.getElementById('section-contrato-detalhes');

            if (selContrato) {
                if (clienteId) {
                    selContrato.disabled = true; // Bloqueia brevemente enquanto carrega
                    fetch('<?= BASE_URL ?>/orcamento/getContratosAjax/' + clienteId)
                        .then(r => r.json())
                        .catch(err => { console.error('Erro na rede:', err); selContrato.disabled = false; return {success:false}; })
                        .then(data => {
                            if (data.success) {
                                const currentVal = selContrato.value;
                                
                                if (data.contratos.length === 0) {
                                    selContrato.innerHTML = '<option value="">— Sem contratos para este cliente —</option>';
                                    selContrato.value = "";
                                    selContrato.disabled = true;
                                    containerCtDet?.classList.add('hidden');
                                } else {
                                    selContrato.innerHTML = '<option value="">— Selecione o contrato —</option>';
                                    selContrato.disabled = false;
                                    data.contratos.forEach(ct => {
                                        const num = ct.numero_contrato || ct.id;
                                        const desc = (ct.titulo || ct.objeto || '').substring(0, 50);
                                        const opt = new Option(`[#${num}] ${desc}${desc.length >= 50 ? '...' : ''}`, ct.id);
                                        selContrato.add(opt);
                                    });

                                    // Tenta manter o valor anterior (importante na carga inicial da edição)
                                    if (currentVal) selContrato.value = currentVal;
                                    
                                    // Se não houver seleção válida e existir apenas um contrato na lista,
                                    // seleciona-o automaticamente para agilizar o preenchimento.
                                    if (!selContrato.value && data.contratos.length === 1) {
                                        selContrato.value = data.contratos[0].id;
                                        // Aplica destaque visual temporário (borda verde e brilho)
                                        selContrato.classList.add('ring-2', 'ring-emerald-500', 'border-emerald-500');
                                        setTimeout(() => {
                                            selContrato.classList.remove('ring-2', 'ring-emerald-500', 'border-emerald-500');
                                        }, 2500);
                                    }

                                    // Se o valor não existir na nova lista (troca de cliente), limpa a seleção e oculta detalhes
                                    if (!selContrato.value) {
                                        containerCtDet?.classList.add('hidden');
                                    } else {
                                        // Dispara o evento para carregar as informações do contrato no box de detalhes
                                        selContrato.dispatchEvent(new Event('change'));
                                    }
                                }
                            }
                        })
                        .catch(err => console.error('Erro ao buscar contratos:', err));
                } else {
                    selContrato.innerHTML = '<option value="">— Selecione um cliente primeiro —</option>';
                    selContrato.disabled = true;
                }
            }
        }

        document.querySelectorAll('.phone-mask').forEach(input => {
            input.addEventListener('input', e => {
                let cursor = e.target.selectionStart;
                let oldLen = e.target.value.length;
                e.target.value = formatPhone(e.target.value);
                let newLen = e.target.value.length;
                cursor = cursor + (newLen - oldLen);
                e.target.setSelectionRange(cursor, cursor);
            });
        });

        // Aplica a máscara em todos os campos carregados (preenchimento automático/PHP)
        document.querySelectorAll('.phone-mask').forEach(i => {
            if(i.value) i.value = formatPhone(i.value);
        });

        updateSigla();
        selCli?.addEventListener('change', updateSigla);

        // Sincroniza o seletor da Etapa 1 com o da Etapa 2 para carregar os contratos
        const selCliScratch = document.getElementById('select-cliente-scratch');
        selCliScratch?.addEventListener('change', function() {
            if (selCli) {
                selCli.value = this.value;
                // Dispara a atualização visual e o carregamento dos contratos
                updateSigla();
            }
        });

        // ── Resetar Código Sugerido ──
        document.getElementById('btn-reset-codigo')?.addEventListener('click', function() {
            const clienteId = selCli?.value;
            const codigoInput = document.getElementById('codigo');

            if (!clienteId) {
                alert('Selecione um cliente no Passo 2 primeiro para gerar o código.');
                goToStep(2);
                return;
            }

            const originalBtn = this.innerHTML;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            this.disabled = true;

            fetch('<?= BASE_URL ?>/orcamento/getProximoNumeroAjax/' + clienteId, {
                headers: {
                    'Accept': 'application/json'
                }
            })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        if (codigoInput) codigoInput.value = data.numero;
                        // Feedback visual de atualização
                        codigoInput.classList.add('ring-2', 'ring-sky-500');
                        setTimeout(() => codigoInput.classList.remove('ring-2', 'ring-sky-500'), 1500);
                    } else {
                        alert(data.message || 'Erro ao gerar número.');
                    }
                })
                .catch(err => {
                    console.error('Erro ao resetar código:', err);
                    alert('Falha na comunicação com o servidor.');
                })
                .finally(() => {
                    this.innerHTML = originalBtn;
                    this.disabled = false;
                });
        });

        // ── AJAX: detalhes de projeto ──
        const selProjeto = document.getElementById('projeto_id');
        selProjeto?.addEventListener('change', function () {
            const pId = this.value;
            const container = document.getElementById('project-details-container');
            if (!pId) { container?.classList.add('hidden'); return; }

            fetch(`${BASE_URL}/projetos/getProjetoDados/${pId}`)
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('detail-cliente').textContent    = data.data.cliente_nome || '—';
                        document.getElementById('detail-responsavel').textContent = data.data.responsavel_nome || '—';
                        document.getElementById('detail-tipo').textContent       = data.data.tipo_servico || '—';
                        document.getElementById('detail-id').textContent         = '#' + data.data.id;
                        container?.classList.remove('hidden');
                        // Preenche cliente se vier no projeto
                        if (data.data.cliente_id && selCli) {
                            selCli.value = data.data.cliente_id;
                            updateSigla();
                        }
                    }
                })
                .catch(err => console.error('Erro ao buscar projeto:', err));
        });

        if (selProjeto?.value) selProjeto.dispatchEvent(new Event('change'));

        // ── AJAX: detalhes de contrato ──
        const selContrato = document.getElementById('contrato_id');
        selContrato?.addEventListener('change', function () {
            const cId = this.value;
            const container = document.getElementById('section-contrato-detalhes');
            if (!cId) { container?.classList.add('hidden'); return; }
            const BASE_URL_GLOBAL = '<?= BASE_URL ?>';

            fetch(BASE_URL_GLOBAL + '/contratos/getContratoDados/' + cId)
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        if(document.getElementById('detail-ct-vencimento')) document.getElementById('detail-ct-vencimento').textContent = data.data.vencimento || '—';
                        if(document.getElementById('detail-ct-valor')) document.getElementById('detail-ct-valor').textContent = new Intl.NumberFormat('pt-BR', { style:'currency', currency:'BRL' }).format(data.data.valor || 0);
                        if(document.getElementById('detail-ct-id')) document.getElementById('detail-ct-id').textContent = '#' + data.data.id;
                        container?.classList.remove('hidden');
                        // Preenche cliente se vier no contrato
                        if (data.data.cliente_id && selCli) {
                            if (selCli.value != data.data.cliente_id) { selCli.value = data.data.cliente_id; updateSigla(); }
                        }
                    }
                })
                .catch(err => console.error('Erro ao buscar contrato:', err));
        });

        if (selContrato?.value) selContrato.dispatchEvent(new Event('change'));

    // Adiciona event listeners aos radio buttons creation_type
    document.querySelectorAll('input[name="creation_type"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const fromScratchSection = document.getElementById('section_from_scratch');
            const fromProjectSection = document.getElementById('section_from_project');

            if (this.value === 'from_scratch') {
                fromScratchSection?.classList.remove('hidden');
                fromProjectSection?.classList.add('hidden');
            } else {
                fromScratchSection?.classList.add('hidden');
                fromProjectSection?.classList.remove('hidden');
            }
            updateSigla(); // Chama updateSigla sempre que o tipo de criação muda
        });
    });

    // ── Importação de Coordenadas via KML/GPX ──
    document.getElementById('import-geo')?.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (!file) return;

        const status = document.getElementById('import-status');
        const reader = new FileReader();
        const extension = file.name.split('.').pop().toLowerCase();

        if (status) status.classList.remove('hidden');

        reader.onload = function(event) {
            try {
                const content = event.target.result;
                const parser = new DOMParser();
                const xml = parser.parseFromString(content, "text/xml");
                let lat = null, lng = null;

                if (extension === 'kml') {
                    // KML: Procura a primeira tag <coordinates>
                    const coordTag = xml.getElementsByTagName("coordinates")[0];
                    if (coordTag) {
                        // O formato KML é lng,lat,alt (separados por vírgula)
                        const rawCoords = coordTag.textContent.trim().split(/\s+/)[0];
                        const coords = rawCoords.split(',');
                        if (coords.length >= 2) {
                            lng = coords[0];
                            lat = coords[1];
                        }
                    }
                } else if (extension === 'gpx') {
                    // GPX: Procura o primeiro ponto (waypoint <wpt> ou trackpoint <trkpt>)
                    const point = xml.getElementsByTagName("wpt")[0] || xml.getElementsByTagName("trkpt")[0];
                    if (point) {
                        lat = point.getAttribute("lat");
                        lng = point.getAttribute("lon");
                    }
                }

                if (lat && lng) {
                    const latInput = document.getElementById('latitude');
                    const lngInput = document.getElementById('longitude');
                    if (latInput) latInput.value = lat;
                    if (lngInput) lngInput.value = lng;
                    
                    // Feedback visual de sucesso
                    [latInput, lngInput].forEach(el => {
                        el.classList.add('ring-2', 'ring-emerald-500', 'border-emerald-500');
                        setTimeout(() => el.classList.remove('ring-2', 'ring-emerald-500', 'border-emerald-500'), 2500);
                    });
                } else {
                    alert('Aviso: Não foi possível localizar tags de coordenadas válidas no arquivo ' + extension.toUpperCase() + '.');
                }
            } catch (err) {
                console.error('Erro ao processar arquivo geográfico:', err);
                alert('Erro crítico ao ler o arquivo. Verifique se o XML está íntegro.');
            } finally {
                if (status) status.classList.add('hidden');
            }
        };

        reader.readAsText(file);
    });

    }, 100);
});

// ── Preenche campos ocultos antes do submit ───────────────────────────
function preSubmit() {
    recalcularTotais();
    // Se "outro" estiver selecionado, usa o valor do campo de texto
    const condSelect = document.getElementById('condicao_pagamento');
    const condOutro = document.getElementById('condicao_pagamento_outro');
    if (condSelect && condSelect.value === 'outro' && condOutro) {
        condSelect.value = condOutro.value.trim() || condOutro.placeholder;
    }
    // Serializa cronograma
    const hidden = document.getElementById('crono-hidden-data');
    if (hidden) {
        const data = {
            mode: window._cronoMode || 'dias',
            startDate: document.getElementById('crono-data-inicio').value,
            totalPeriods: document.getElementById('crono-total-periods').value,
            activities: typeof window.getRowNames === 'function' ? window.getRowNames() : [],
            state: window._cronoState || {},
            texto_intro: document.getElementById('cronograma-texto-intro')?.value ?? '',
            texto_footer: document.getElementById('cronograma-texto-footer')?.value ?? ''
        };
        hidden.value = JSON.stringify(data);
    }
}

// ── Init: calcula na carga e vai para Step 1 ──────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    recalcularTotais();
    goToStep(1);

    // Inicializa largura dinâmica dos campos
    const codigoInput = document.getElementById('codigo');
    if (codigoInput) codigoInput.style.width = (codigoInput.scrollWidth + 4) + 'px';
    const respSelect = document.getElementById('responsavel_id');
    if (respSelect) respSelect.style.width = (respSelect.scrollWidth + 4) + 'px';
    const dataInput = document.getElementById('data_proposta');
    if (dataInput) dataInput.style.width = (dataInput.scrollWidth + 4) + 'px';

    // Ajusta altura inicial de todas as legendas existentes
    document.querySelectorAll('textarea[name="item_descricao[]"]').forEach(el => {
        el.style.height = el.scrollHeight + 'px';
    });

    // Recupera dados salvos do cronograma se existirem (para edição)
    const rawData = <?= json_encode($orc['cronograma_data'] ?? null) ?>;
    if (rawData) {
        try {
            const saved = typeof rawData === 'string' ? JSON.parse(rawData) : rawData;
            if (saved) {
                window._cronoState = saved.state || {};
                window._cronoMode  = saved.mode || 'dias';
                document.getElementById('cronograma-texto-intro') && (document.getElementById('cronograma-texto-intro').value = saved.texto_intro || '');
                document.getElementById('cronograma-texto-footer') && (document.getElementById('cronograma-texto-footer').value = saved.texto_footer || '');
                // Atualiza campos da interface
                if (saved.startDate) document.getElementById('crono-data-inicio').value = saved.startDate;
                if (saved.totalPeriods) document.getElementById('crono-total-periods').value = saved.totalPeriods;

                // Armazena temporariamente os nomes para a primeira carga da tabela
                window._cronoNamesSaved = saved.activities || null;
            }
        } catch(e) { 
            console.error("Erro ao processar dados do cronograma:", e); 
        }
    }

    // Sincroniza visualmente os botões de modo e atualiza o select de períodos e labels
    document.querySelectorAll('.crono-mode-btn').forEach(btn => {
        const mode = btn.textContent.trim().toLowerCase();
        if (mode === (window._cronoMode || 'dias')) {
            cronoSetMode(mode, btn);
        }
    });

    // Tenta restaurar o banco selecionado pelos dados já salvos
    const selBancoRestore = document.getElementById('banco_id');
    const pixTipoSalvo = document.getElementById('pix_tipo_chave')?.value || '';
    const pixChaveSalvo = document.getElementById('pix_chave')?.value || '';
    const dadosBancSalvo = document.getElementById('dados_bancarios')?.value || '';
    if (selBancoRestore && !selBancoRestore.value) {
        // Mapeamento reverso (form → banco) para comparação
        const mapaReverso = { 'CPF': 'cpf_cnpj', 'CNPJ': 'cpf_cnpj', 'E-mail': 'email', 'Celular': 'telefone', 'Chave Aleatória': 'aleatoria' };
        const tipoBanco = mapaReverso[pixTipoSalvo] || pixTipoSalvo;
        for (const opt of selBancoRestore.options) {
            if (!opt.value) continue;
            const optTipo = opt.getAttribute('data-pix-tipo') || '';
            const optChave = opt.getAttribute('data-pix-chave') || '';
            const optDados = opt.getAttribute('data-dados-bancarios') || '';
            const matchPix = optTipo === tipoBanco && optChave === pixChaveSalvo;
            const matchDados = optDados === dadosBancSalvo;
            if ((pixChaveSalvo && matchPix) || (dadosBancSalvo && matchDados)) {
                selBancoRestore.value = opt.value;
                preencherDadosBanco();
                break;
            }
        }
    }

    // Inicia cronograma
    if (typeof cronoBuildTable === 'function' && !document.getElementById('crono-body').innerHTML) {
        if (!document.getElementById('crono-data-inicio').value) {
            document.getElementById('crono-data-inicio').value = new Date().toISOString().split('T')[0];
        }
        cronoBuildTable();
    }

    // ── Mostra/esconde campo "outro" da condição de pagamento ──
    const condSelect = document.getElementById('condicao_pagamento');
    const condOutro = document.getElementById('condicao_pagamento_outro');
    if (condSelect && condOutro) {
        condSelect.addEventListener('change', function () {
            condOutro.style.display = this.value === 'outro' ? 'block' : 'none';
            if (this.value === 'outro') condOutro.focus();
        });
    }

    // ── Sticky header da tabela de itens ──
    const thead = document.getElementById('thead-itens');
    if (thead) {
        let floating = null;
        function syncHeaderWidths() {
            if (!floating) return;
            const ths = thead.querySelectorAll('th');
            const fths = floating.querySelectorAll('th');
            const wrapper = thead.closest('[style*="overflow"]');
            const wrapperRect = wrapper?.getBoundingClientRect();
            ths.forEach((th, i) => {
                if (fths[i]) fths[i].style.width = th.offsetWidth + 'px';
            });
            if (wrapperRect) {
                floating.style.left = wrapperRect.left + 'px';
                floating.style.width = wrapperRect.width + 'px';
            }
        }
        function updateStickyHeader() {
            const panel = document.getElementById('step-panel-4');
            if (!panel?.classList.contains('active')) { if (floating) { floating.remove(); floating = null; } return; }
            const rect = thead.getBoundingClientRect();
            if (rect.top < 58) {
                if (!floating) {
                    floating = document.createElement('div');
                    floating.id = 'floating-thead';
                    const isDark = document.body.classList.contains('dark-theme');
                    const bg = isDark ? '#1e293b' : '#fff';
                    const border = isDark ? '#334155' : '#E5E7EB';
                    floating.style.cssText = 'position:fixed;top:58px;z-index:15;background:' + bg + ';border-bottom:1px solid ' + border + ';box-shadow:0 2px 6px rgba(0,0,0,.08);overflow:hidden;border-radius:0 0 8px 8px';
                    const table = document.createElement('table');
                    table.className = 'w-full';
                    table.style.tableLayout = 'fixed';
                    const clone = thead.cloneNode(true);
                    clone.style.display = 'table-header-group';
                    table.appendChild(clone);
                    floating.appendChild(table);
                    document.body.appendChild(floating);
                    syncHeaderWidths();
                }
                syncHeaderWidths();
            } else {
                if (floating) { floating.remove(); floating = null; }
            }
        }
        window.addEventListener('scroll', updateStickyHeader, { passive: true });
        window.addEventListener('resize', () => { if (floating) { floating.remove(); floating = null; } });
        const origGo = window.goToStep;
        if (origGo) {
            window.goToStep = function(n) { origGo(n); if (n === 4) setTimeout(updateStickyHeader, 100); };
        }
    }
});

// ══════════════════════════════════════════════════════════════════════
//  CRONOGRAMA — lógica completa
// ══════════════════════════════════════════════════════════════════════
(function () {
    const ATIVIDADES_DEFAULT = [
        'Aceite da proposta',
        'Coordenação Geral do projeto',
        'Planejamento logístico',
        'Preparação de equipes e equipamentos',
        'Movimentação da equipe para campo',
        'Coleta de dados de campo',
        'Retorno da equipe de campo',
        'Elaboração de peças técnicas',
        'Emissão de ART – CREA ou CRBio',
        'Entrega das peças técnicas',
        'Protocolo SINAFLOR (Se necessário)',
    ];

    window._cronoState = {};   // { "row_col": 0|1|2 }
    window._cronoMode  = 'dias';
    let dragging = false;
    let draggingRow = null;
    let dragVal  = null;

    window.cronoSetMode = function (m, btn) {
        window._cronoMode = m;
        document.querySelectorAll('.crono-mode-btn').forEach(b => b.classList.remove('active'));
        if (btn) btn.classList.add('active');

        // Atualiza o placeholder do input "Total de períodos" conforme o tipo selecionado
        const inputPeriods = document.getElementById('crono-total-periods');
        if (inputPeriods) {
            const unitLabel = m === 'semanas' ? 'semanas' : (m === 'meses' ? 'meses' : 'dias');
            inputPeriods.placeholder = `Nº de ${unitLabel}`;
        }

        cronoBuildTable();
    };

    function getPeriods() { return parseInt(document.getElementById('crono-total-periods')?.value) || 24; }

    function getDateStart() {
        const v = document.getElementById('crono-data-inicio')?.value;
        return v ? new Date(v + 'T00:00:00') : null;
    }

    function getPeriodLabel(i) {
        if (window._cronoMode === 'semanas') return 'S' + (i + 1);
        if (window._cronoMode === 'meses')   return 'M' + (i + 1);
        return i + 1;
    }

    function getPeriodSub(i) {
        const d = getDateStart();
        if (!d) return '';
        const dd = new Date(d);
        if (window._cronoMode === 'dias')    { dd.setDate(dd.getDate() + i); return dd.toLocaleDateString('pt-BR', {day:'2-digit',month:'2-digit'}); }
        if (window._cronoMode === 'semanas') { dd.setDate(dd.getDate() + i * 7); return dd.toLocaleDateString('pt-BR', {day:'2-digit',month:'2-digit'}); }
        if (window._cronoMode === 'meses')   { dd.setMonth(dd.getMonth() + i); return dd.toLocaleDateString('pt-BR', {month:'short',year:'2-digit'}); }
        return '';
    }

    // Exporta a função para o escopo global para que o preSubmit possa acessá-la
    window.getRowNames = function () {
        return [...(document.querySelectorAll('#crono-body tr') || [])].map(tr => {
            return tr.querySelector('input.ativ-name')?.value || '';
        });
    };

    window.cronoBuildTable = function () {
        const n     = getPeriods();
        const head  = document.getElementById('crono-head');
        const body  = document.getElementById('crono-body');
        if (!head || !body) return;

        // Prioridade de nomes: 1. Nomes atuais na tabela DOM | 2. Nomes vindos do banco | 3. Padrão do sistema
        const currentInTable = getRowNames();
        let names = ATIVIDADES_DEFAULT;
        
        if (currentInTable.length > 0) {
            names = currentInTable;
        } else if (window._cronoNamesSaved) {
            names = window._cronoNamesSaved;
            window._cronoNamesSaved = null; // Limpa após o primeiro uso
        }

        // Build header
        let th = '<tr><th style="width:30px"></th><th class="col-ativ">Atividade</th>';
        for (let i = 0; i < n; i++) {
            const sub = getPeriodSub(i);
            th += `<th class="crono-cell-day"><span class="crono-day-num">${getPeriodLabel(i)}</span>${sub ? '<span class="crono-day-sub">' + sub + '</span>' : ''}</th>`;
        }
        th += '<th style="width:40px"></th></tr>';
        head.innerHTML = th;

        // Build body
        let tb = '';
        names.forEach((name, ri) => {
            tb += `<tr data-row="${ri}" draggable="true" class="crono-row-drag">
                <td class="drag-handle text-gray-300 hover:text-sky-500 transition-colors" style="cursor:grab"><i class="fas fa-grip-vertical"></i></td>
                <td class="col-ativ"><input class="ativ-name" value="${name.replace(/"/g,'&quot;')}" placeholder="Nome da atividade"></td>`;
            for (let ci = 0; ci < n; ci++) {
                const key = ri + '_' + ci;
                const st  = window._cronoState[key] || 0;
                const cls = st === 1 ? 'st-esc' : st === 2 ? 'st-camp' : '';
                const mark = st > 0 ? '✕' : '';
                tb += `<td class="crono-cell-day"><div class="crono-dot ${cls}" data-ri="${ri}" data-ci="${ci}">${mark}</div></td>`;
            }
            tb += `<td style="width:40px;text-align:center">
                <button type="button" onclick="cronoDeleteRow(this)" class="w-8 h-8 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 transition-colors inline-flex items-center justify-center" title="Excluir atividade">
                    <i class="fas fa-trash-alt text-xs"></i>
                </button>
            </td></tr>`;
        });
        body.innerHTML = tb;

        // Bind events
        cronoBindAllEvents();

        document.getElementById('crono-sum-ativ').textContent = names.length;
        cronoUpdateSummary();
    };

    function cronoBindAllEvents() {
        const body = document.getElementById('crono-body');
        
        // Eventos das células (pintar)
        body.querySelectorAll('.crono-dot').forEach(dot => {
            dot.addEventListener('mousedown', e => { e.preventDefault(); startDrag(dot); });
            dot.addEventListener('mouseenter', () => { if (dragging) applyDrag(dot); });
        });

        // Eventos de Arrastar Linha
        body.querySelectorAll('.crono-row-drag').forEach(row => {
            row.addEventListener('dragstart', handleRowDragStart);
            row.addEventListener('dragover', handleRowDragOver);
            row.addEventListener('drop', handleRowDrop);
            row.addEventListener('dragend', handleRowDragEnd);
        });
    }

    function handleRowDragStart(e) {
        draggingRow = this;
        this.classList.add('opacity-40', 'bg-sky-50');
        e.dataTransfer.effectAllowed = 'move';
    }

    function handleRowDragOver(e) {
        e.preventDefault();
        e.dataTransfer.dropEffect = 'move';
        const overRow = this;
        if (overRow !== draggingRow) {
            overRow.classList.add('border-t-2', 'border-sky-500');
        }
    }

    function handleRowDragEnd(e) {
        document.querySelectorAll('.crono-row-drag').forEach(r => {
            r.classList.remove('opacity-40', 'bg-sky-50', 'border-t-2', 'border-sky-500');
        });
    }

    function handleRowDrop(e) {
        e.preventDefault();
        if (this === draggingRow) return;

        const body = document.getElementById('crono-body');
        const allRows = Array.from(body.querySelectorAll('tr'));
        const fromIndex = allRows.indexOf(draggingRow);
        const toIndex = allRows.indexOf(this);

        if (fromIndex < toIndex) {
            this.after(draggingRow);
        } else {
            this.before(draggingRow);
        }

        reorganizeStateAfterMove();
    }

    function reorganizeStateAfterMove() {
        const body = document.getElementById('crono-body');
        const n = getPeriods();
        const newState = {};
        const rows = body.querySelectorAll('tr');

        rows.forEach((row, newRi) => {
            const oldRi = row.getAttribute('data-row');
            // Move as marcações no objeto de estado
            for (let ci = 0; ci < n; ci++) {
                const oldKey = oldRi + '_' + ci;
                if (window._cronoState[oldKey]) {
                    newState[newRi + '_' + ci] = window._cronoState[oldKey];
                }
            }
            // Atualiza os atributos da linha e das células
            row.setAttribute('data-row', newRi);
            row.querySelectorAll('.crono-dot').forEach(dot => {
                dot.setAttribute('data-ri', newRi);
            });
        });

        window._cronoState = newState;
        cronoUpdateSummary();
    }

    function startDrag(dot) {
        dragging = true;
        const key = dot.dataset.ri + '_' + dot.dataset.ci;
        dragVal = ((window._cronoState[key] || 0) + 1) % 3;
        applyDrag(dot);
    }

    function applyDrag(dot) {
        const key = dot.dataset.ri + '_' + dot.dataset.ci;
        window._cronoState[key] = dragVal;
        dot.className = 'crono-dot' + (dragVal === 1 ? ' st-esc' : dragVal === 2 ? ' st-camp' : '');
        dot.textContent = dragVal > 0 ? '✕' : '';
        cronoUpdateSummary();
    }

    document.addEventListener('mouseup', () => { dragging = false; dragVal = null; });

    function cronoUpdateSummary() {
        let esc = 0, camp = 0;
        Object.values(window._cronoState).forEach(v => { if (v === 1) esc++; if (v === 2) camp++; });
        const setSafe = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val; };
        const n    = getPeriods();
        const unit = window._cronoMode === 'semanas' ? ' semanas' : window._cronoMode === 'meses' ? ' meses' : ' dias';
        setSafe('crono-sum-esc',   esc);
        setSafe('crono-sum-camp',  camp);
        setSafe('crono-sum-total', n + unit + ' previstos');
        setSafe('crono-badge-esc',  esc  + ' escritório');
        setSafe('crono-badge-camp', camp + ' campo');
    }

    window.cronoDeleteRow = function (btn) {
        if (!confirm('Deseja remover esta atividade do cronograma?')) return;
        const row = btn.closest('tr');
        row.remove();
        reorganizeStateAfterMove();
        document.getElementById('crono-sum-ativ').textContent = getRowNames().length;
    };

    window.cronoAddRow = function () {
        const body = document.getElementById('crono-body');
        if (!body) return;
        const n  = getPeriods();
        const ri = body.querySelectorAll('tr').length;
        let html = `<tr data-row="${ri}" draggable="true" class="crono-row-drag">
            <td class="drag-handle text-gray-300 hover:text-sky-500 transition-colors" style="cursor:grab"><i class="fas fa-grip-vertical"></i></td>
            <td class="col-ativ"><input class="ativ-name" value="Nova atividade" placeholder="Nome da atividade"></td>`;
        for (let ci = 0; ci < n; ci++) {
            html += `<td class="crono-cell-day"><div class="crono-dot" data-ri="${ri}" data-ci="${ci}"></div></td>`;
        }
        html += `<td style="width:40px;text-align:center">
            <button type="button" onclick="cronoDeleteRow(this)" class="w-8 h-8 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 transition-colors inline-flex items-center justify-center" title="Excluir atividade">
                <i class="fas fa-trash-alt text-xs"></i>
            </button>
        </td></tr>`;
        
        body.insertAdjacentHTML('beforeend', html);
        
        // Re-vincula eventos para a nova linha (Drag and Drop e Cliques)
        cronoBindAllEvents();
        
        document.getElementById('crono-sum-ativ').textContent = ri + 1;
    };

    window.cronoClearAll = function () {
        if (!confirm('Limpar todas as marcações do cronograma?')) return;
        window._cronoState = {};
        cronoBuildTable();
    };
})();
// ========== LÓGICA DE ASSINATURAS ==========
(function() {
    function toggleVisibility(selectId, targets) {
        const sel = document.getElementById(selectId);
        if (!sel) return;
        sel.addEventListener('change', function() {
            var v = this.value;
            targets.forEach(function(t) {
                var el = document.getElementById(t.id);
                if (el) el.classList.toggle('hidden', v !== t.val);
            });
        });
    }

    // Contratada
    toggleVisibility('assinatura_tipo', [
        { id: 'ctd_imagem_container', val: 'imagem' },
        { id: 'ctd_certificado_container', val: 'certificado' }
    ]);

    // Elaborador checkbox
    var elabChk = document.getElementById('elab_checkbox');
    var elabFields = document.getElementById('elab_signature_fields');
    if (elabChk && elabFields) {
        elabChk.addEventListener('change', function() {
            elabFields.classList.toggle('hidden', !this.checked);
        });
    }

    // Elaborador tipo
    toggleVisibility('elab_assinatura_tipo', [
        { id: 'elab_imagem_container', val: 'imagem' },
        { id: 'elab_certificado_container', val: 'certificado' }
    ]);

    // Upload genérico via data attributes
    document.querySelectorAll('.sig-img-input').forEach(function(inp) {
        inp.addEventListener('change', function(e) {
            var file = e.target.files[0];
            if (!file) return;
            if (file.size > 500 * 1024) {
                alert('A imagem deve ter no máximo 500KB.');
                this.value = '';
                return;
            }
            var hiddenId = this.dataset.hidden;
            var preview = document.getElementById(this.dataset.preview);
            var hidden = document.getElementById(hiddenId);
            var removerField = document.getElementById(this.dataset.removerField);
            var reader = new FileReader();
            reader.onload = function(ev) {
                var b64 = ev.target.result.split(',')[1];
                if (hidden) hidden.value = b64;
                if (preview) preview.innerHTML = '<img src=\"data:image/png;base64,' + b64 + '\" style=\"max-height:50px; max-width:200px; object-fit:contain;\">';
                if (removerField) removerField.value = '0';
                var btn = document.querySelector('.btn-limpar-sig[data-hidden=\"' + hiddenId + '\"]');
                if (btn) btn.classList.remove('hidden');
            };
            reader.readAsDataURL(file);
        });
    });

    document.querySelectorAll('.btn-limpar-sig').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var hidden = document.getElementById(this.dataset.hidden);
            var preview = document.getElementById(this.dataset.preview);
            var removerField = document.getElementById(this.dataset.removerField);
            if (hidden) hidden.value = '';
            if (removerField) removerField.value = '1';
            if (preview) preview.innerHTML = '<span class=\"text-xs text-gray-400\">Nenhuma</span>';
            this.classList.add('hidden');
            var inp = document.querySelector('.sig-img-input[data-hidden=\"' + this.dataset.hidden + '\"]');
            if (inp) inp.value = '';
        });
    });

    // ========== CERTIFICADO DIGITAL A1 ==========
    function setupCertUpload(target) {
        var fileInput = document.querySelector('.cert-file-input[data-target=\"' + target + '\"]');
        var pwdInput = document.querySelector('.cert-password-input[data-target=\"' + target + '\"]');
        var statusEl = document.getElementById(target + '_cert_status');
        var infoEl = document.getElementById(target + '_cert_info');
        var pathHidden = document.getElementById(target + '_cert_path_hidden');
        var senhaHidden = document.getElementById(target + '_cert_senha_hidden');
        var nomeHidden = document.getElementById(target + '_cert_nome_hidden');
        var cpfHidden = document.getElementById(target + '_cert_cpf_hidden');
        var valHidden = document.getElementById(target + '_cert_validade_hidden');

        if (!fileInput || !pwdInput) return;

        function lerCertificado() {
            var file = fileInput.files[0];
            var senha = pwdInput.value;
            if (!file || !senha) {
                statusEl.innerHTML = '<span class=\"text-xs text-red-500\">Selecione o arquivo e informe a senha.</span>';
                return;
            }
            var fd = new FormData();
            fd.append('certificado_file', file);
            fd.append('certificado_senha', senha);
            statusEl.innerHTML = '<span class=\"text-xs text-sky-500\"><i class=\"fas fa-spinner fa-spin\"></i> Lendo certificado...</span>';
            infoEl.classList.add('hidden');
            fetch(BASE_URL + '/orcamento/uploadCertificado', { method: 'POST', body: fd })
            .then(function(r) { return r.json(); })
            .then(function(res) {
                if (res.success) {
                    var d = res.data;
                    statusEl.innerHTML = '<span class=\"text-xs text-green-600\"><i class=\"fas fa-check-circle\"></i> Certificado v\u00e1lido</span>';
                    document.getElementById(target + '_cert_nome').textContent = d.nome || '-';
                    document.getElementById(target + '_cert_doc').textContent = d.documento || d.cpf || d.cnpj || '-';
                    document.getElementById(target + '_cert_validade').textContent = d.validade_ate || '-';
                    document.getElementById(target + '_cert_icp').textContent = d.icp_brasil ? 'Sim' : 'N\u00e3o';
                    infoEl.classList.remove('hidden');
                    if (pathHidden) pathHidden.value = d.path;
                    if (senhaHidden) senhaHidden.value = senha;
                    if (nomeHidden) nomeHidden.value = d.nome || '';
                    if (cpfHidden) cpfHidden.value = d.documento || d.cpf || '';
                    if (valHidden) valHidden.value = d.validade_ate || '';
                } else {
                    statusEl.innerHTML = '<span class=\"text-xs text-red-500\"><i class=\"fas fa-exclamation-triangle\"></i> ' + (res.error || 'Erro') + '</span>';
                    if (pathHidden) pathHidden.value = '';
                    if (senhaHidden) senhaHidden.value = '';
                }
            })
            .catch(function(err) {
                statusEl.innerHTML = '<span class=\"text-xs text-red-500\">Erro de comunica\u00e7\u00e3o.</span>';
                console.error(err);
            });
        }
        fileInput.addEventListener('change', lerCertificado);
        pwdInput.addEventListener('blur', function() {
            if (fileInput.files[0]) lerCertificado();
        });
    }
    setupCertUpload('ctd');
    setupCertUpload('elab');
})();
</script>

</body>
</html>