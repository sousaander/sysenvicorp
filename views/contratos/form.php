<?php
/**
 * Formulário de Contrato — Versão Completa e Profissional
 * Inclui: Identificação, Partes, Objeto/Prazos, Pagamento/Multas,
 *         Confidencialidade/LGPD, Rescisão e Documentos
 */

$isEdit = (isset($isEdit) && $isEdit === true) || (!empty($contrato) && isset($contrato['id']));
$contratoData = $contrato ?? null;
$settings = $settings ?? [];
$projetos = $projetos ?? [];
$actionUrl = ($baseUrl ?? '') . '/contratos/salvar';

// Helper para preencher value nos inputs
if (!function_exists('val')) { 
    function val($data, $key, $default = '') {
        return htmlspecialchars($data[$key] ?? $default);
    }
}

// Helper para selected em <select>
if (!function_exists('sel')) { 
    function sel($data, $key, $value) {
        return (($data[$key] ?? '') === $value) ? 'selected' : '';
    }
}

// Helper para checked em <input type="checkbox">
if (!function_exists('chk')) {
    function chk($data, $key, $default = false) {
        return (isset($data[$key]) ? (bool)$data[$key] : $default) ? 'checked' : '';
    }
}
?>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;500;600&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
/* ================================================================
   DESIGN SYSTEM — CONTRATO
   Paleta: Azul-ardósia profissional + dourado como acento
================================================================ */
:root {
    --c-bg:          #F7F8FA;
    --c-surface:     #FFFFFF;
    --c-border:      #E2E6ED;
    --c-border-md:   #C8D0DC;
    --c-text:        #1A2233;
    --c-text-2:      #4A5568;
    --c-text-3:      #8896A9;

    --c-accent:        #2563eb;
    --c-accent-soft:   #dbeafe;
    --c-accent-deep:   #1d4ed8;
    --c-accent-hover:  #1e40af;

    --c-amber:        #d97706;
    --c-amber-light:  #fef3c7;
    --c-amber-border: #fcd34d;

    --c-green:       #1A6B45;
    --c-green-light: #E8F5EE;
    --c-green-border:#6BBF8A;

    --c-red:         #9B1B1B;
    --c-red-light:   #FDF0F0;
    --c-red-border:  #E88080;

    --radius:        8px;
    --radius-lg:     12px;
    --radius-xl:     16px;

    --shadow-sm:     0 1px 3px rgba(0,0,0,.07), 0 1px 2px rgba(0,0,0,.05);
    --shadow-md:     0 4px 12px rgba(0,0,0,.08), 0 2px 4px rgba(0,0,0,.05);

    --font-display:  'Lora', Georgia, serif;
    --font-body:     'DM Sans', system-ui, sans-serif;
}

/* Ajustes para Modo Escuro (Dark Mode) */
.dark-theme .ctr-form-wrap {
    --c-bg:          var(--db-bg, #0d1117);
    --c-surface:     var(--db-surface, #161b22);
    --c-border:      var(--db-border, #30363d);
    --c-border-md:   #475569;
    --c-text:        var(--db-text, #e6edf3);
    --c-text-2:      var(--db-text2, #8b949e);
    --c-text-3:      var(--db-text3, #6e7681);
    --c-accent-soft: rgba(37, 99, 235, 0.15);
    --c-green-light: rgba(22, 163, 74, 0.1);
    --c-amber-light: rgba(217, 119, 6, 0.15);
    --c-red-light:   rgba(220, 38, 38, 0.15);
}

/* Overrides para Tailwind e estados no Modo Escuro */
.dark-theme .bg-white { background-color: var(--c-surface) !important; color: var(--c-text); }
.dark-theme .bg-gray-50 { background-color: var(--c-bg) !important; }
.dark-theme .border-gray-200, .dark-theme .border-gray-100 { border-color: var(--c-border) !important; }
.dark-theme .text-gray-800, .dark-theme .text-gray-700, .dark-theme .text-gray-900 { color: var(--c-text) !important; }
.dark-theme .text-gray-600, .dark-theme .text-gray-500 { color: var(--c-text-2) !important; }

.dark-theme .ctr-section-header:hover { background: rgba(255,255,255,0.03); }
.dark-theme .ctr-step:hover { background: var(--c-accent-soft); color: var(--c-text-2); }
.dark-theme .btn-cancel:hover { background: rgba(255,255,255,0.05); }
.dark-theme .ctr-input, .dark-theme .ctr-select, .dark-theme .ctr-textarea { background-color: var(--c-bg); color: var(--c-text); border-color: var(--c-border); }
.dark-theme .ctr-input:focus, .dark-theme .ctr-select:focus, .dark-theme .ctr-textarea:focus { background-color: var(--c-surface); border-color: var(--c-accent-deep); }

.ctr-form-wrap * { box-sizing: border-box; margin: 0; padding: 0; }
.ctr-form-wrap { font-family: var(--font-body); color: var(--c-text); background: var(--c-bg); padding: 0 0 2.5rem; }

/* ---- Cabeçalho ---- */
.ctr-header {
    background: var(--c-surface);
    border: 1px solid var(--c-border);
    border-radius: var(--radius-xl);
    padding: 24px 28px;
    margin-bottom: 20px;
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
    box-shadow: var(--shadow-sm);
}
.ctr-header-left { display: flex; align-items: flex-start; gap: 14px; }
.ctr-header-icon {
    width: 46px; height: 46px;
    background: var(--c-accent);
    border-radius: var(--radius);
    display: flex; align-items: center; justify-content: center;
    color: #fff; font-size: 18px; flex-shrink: 0;
}
.ctr-title { font-family: var(--font-display); font-size: 20px; font-weight: 600; color: var(--c-text); line-height: 1.2; }
.ctr-subtitle { font-size: 13px; color: var(--c-text-3); margin-top: 4px; line-height: 1.5; }
.ctr-status-badge {
    display: inline-flex; align-items: center; gap: 6px;
    font-size: 11px; font-weight: 600; letter-spacing: .04em; text-transform: uppercase;
    padding: 5px 12px; border-radius: 20px; white-space: nowrap;
    border: 1px solid var(--c-amber-border);
    background: var(--c-amber-light); color: var(--c-amber);
}
.ctr-status-badge.vigente { background: var(--c-green-light); color: var(--c-green); border-color: var(--c-green-border); }
.ctr-status-badge.finalizado { background: #F0F4FA; color: var(--c-text-2); border-color: var(--c-border); }
.ctr-status-badge.cancelado { background: var(--c-red-light); color: var(--c-red); border-color: var(--c-red-border); }

/* ---- Barra de Progresso ---- */
.ctr-progress {
    display: flex; margin-bottom: 20px;
    background: var(--c-surface);
    border: 1px solid var(--c-border);
    border-radius: var(--radius-lg);
    overflow: hidden;
    box-shadow: var(--shadow-sm);
    position: sticky;
    top: 10px;
    z-index: 50;
}
.ctr-step {
    flex: 1; padding: 11px 8px;
    text-align: center; font-size: 11px; font-weight: 500; letter-spacing: .02em;
    color: var(--c-text-3);
    border-right: 1px solid var(--c-border);
    cursor: pointer; transition: background .15s, color .15s;
    display: flex; align-items: center; justify-content: center; gap: 5px;
}
.ctr-step:last-child { border-right: none; }
.ctr-step:hover { background: #F0F4FA; color: var(--c-text-2); }
.ctr-step.active { background: var(--c-accent); color: #fff; }
.ctr-step.done { background: var(--c-green-light); color: var(--c-green); }
.ctr-step-num { font-size: 10px; opacity: .7; }

/* ---- Seções ---- */
.ctr-section {
    background: var(--c-surface);
    border: 1px solid var(--c-border);
    border-radius: var(--radius-xl);
    margin-bottom: 14px;
    overflow: hidden;
    box-shadow: var(--shadow-sm);
    transition: box-shadow .2s;
}
.ctr-section:focus-within { box-shadow: var(--shadow-md); }

.ctr-section-header {
    display: flex; align-items: center; gap: 12px;
    padding: 16px 22px;
    border-bottom: 1px solid var(--c-border);
    cursor: pointer; user-select: none;
    transition: background .15s;
}
.ctr-section-header:hover { background: #FAFBFC; }
.ctr-section.collapsed .ctr-section-header { border-bottom-color: transparent; }
.ctr-section.collapsed .ctr-section-body { display: none; }

.ctr-section-num {
    width: 28px; height: 28px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 12px; font-weight: 600; flex-shrink: 0;
}
.num-blue   { background: var(--c-accent-soft); color: var(--c-accent); }
.num-green  { background: var(--c-green-light); color: var(--c-green); }
.num-gold   { background: var(--c-amber-light); color: var(--c-amber); }
.num-red    { background: var(--c-red-light); color: var(--c-red); }
.num-gray   { background: #F0F4FA; color: var(--c-text-2); }

.ctr-section-label {
    font-family: var(--font-display); font-size: 14px; font-weight: 600;
    color: var(--c-text); flex: 1;
}
.ctr-section-desc { font-size: 12px; color: var(--c-text-3); margin-top: 1px; }

.ctr-badge {
    font-size: 10px; font-weight: 600; letter-spacing: .04em; text-transform: uppercase;
    padding: 3px 9px; border-radius: 20px;
}
.badge-required { background: var(--c-red-light); color: var(--c-red); border: 1px solid var(--c-red-border); }
.badge-finance  { background: var(--c-green-light); color: var(--c-green); border: 1px solid var(--c-green-border); }
.badge-legal    { background: var(--c-amber-light); color: var(--c-amber); border: 1px solid var(--c-amber-border); }
.badge-optional { background: #F0F4FA; color: var(--c-text-2); border: 1px solid var(--c-border); }

.ctr-chevron { font-size: 11px; color: var(--c-text-3); transition: transform .2s; }
.ctr-chevron.open { transform: rotate(180deg); }

/* ---- Corpo das seções ---- */
.ctr-section-body { padding: 22px; }
.ctr-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.ctr-grid-4 { display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 16px; }
.ctr-grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; }

.ctr-full  { grid-column: 1 / -1; }
.ctr-span2 { grid-column: span 2; }
.ctr-span3 { grid-column: span 3; }

/* ---- Campos ---- */
.ctr-field { display: flex; flex-direction: column; gap: 5px; }

.ctr-field-header { display: flex; align-items: center; justify-content: space-between; gap: 8px; }

.ctr-label {
    font-size: 12px; font-weight: 600; letter-spacing: .025em;
    color: var(--c-text-2); display: block;
}
.ctr-label .req { color: #C53030; margin-left: 2px; }

.ctr-input, .ctr-select, .ctr-textarea {
    width: 100%;
    font-family: var(--font-body);
    font-size: 13.5px;
    color: var(--c-text);
    padding: 8px 12px;
    background: var(--c-bg);
    border: 1px solid var(--c-border);
    border-radius: var(--radius);
    outline: none;
    transition: border-color .15s, box-shadow .15s, background .15s;
    appearance: none; -webkit-appearance: none;
}
.ctr-select {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%238896A9' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 10px center;
    padding-right: 30px;
}
.ctr-input:hover, .ctr-select:hover, .ctr-textarea:hover {
    border-color: var(--c-border-md); background: #fff;
}
.ctr-input:focus, .ctr-select:focus, .ctr-textarea:focus {
    border-color: var(--c-accent-deep);
    box-shadow: 0 0 0 3px rgba(30,77,140,.1);
    background: #fff;
}
.ctr-textarea { resize: vertical; line-height: 1.65; }

.ctr-helper { font-size: 11px; color: var(--c-text-3); line-height: 1.4; }

/* ---- Input com botão ---- */
.ctr-input-group { display: flex; gap: 6px; align-items: stretch; }
.ctr-input-group .ctr-input { flex: 1; }
.btn-cnpj {
    padding: 8px 14px;
    background: var(--c-accent-soft);
    border: 1px solid #C0D4EE;
    border-radius: var(--radius);
    color: var(--c-accent);
    font-size: 12px; font-weight: 600;
    cursor: pointer; white-space: nowrap;
    display: flex; align-items: center; gap: 5px;
    font-family: var(--font-body);
    transition: background .15s, color .15s;
}
.btn-cnpj:hover { background: var(--c-accent); color: #fff; }

/* ---- Divisores ---- */
.ctr-divider {
    grid-column: 1 / -1;
    display: flex; align-items: center; gap: 10px;
    margin: 6px 0 2px;
}
.ctr-divider span {
    font-size: 11px; font-weight: 700; letter-spacing: .07em; text-transform: uppercase;
    color: var(--c-text-3); white-space: nowrap;
}
.ctr-divider::after {
    content: ''; flex: 1; height: 1px; background: var(--c-border);
}

/* ---- Tags clicáveis ---- */
.ctr-tag-group { display: flex; flex-wrap: wrap; gap: 6px; }
.ctr-tag {
    font-size: 12px; font-weight: 500;
    padding: 4px 12px;
    border-radius: 20px;
    border: 1px solid var(--c-border);
    cursor: pointer;
    background: var(--c-bg); color: var(--c-text-2);
    transition: all .15s;
    user-select: none;
}
.ctr-tag:hover { border-color: var(--c-accent-deep); color: var(--c-accent); }
.ctr-tag.active { background: var(--c-accent); border-color: var(--c-accent); color: #fff; }

/* ---- Caixas de seleção ---- */
.ctr-check-row { display: flex; align-items: center; gap: 9px; }
.ctr-check-row input[type="checkbox"] {
    width: 16px; height: 16px;
    border: 1.5px solid var(--c-border-md);
    border-radius: 4px; cursor: pointer;
    accent-color: var(--c-accent);
    flex-shrink: 0;
}
.ctr-check-label { font-size: 13px; color: var(--c-text-2); line-height: 1.4; cursor: pointer; }

/* ---- Caixas de Info/Aviso/Alerta ---- */
.ctr-info-box, .ctr-warn-box, .ctr-alert-box {
    grid-column: 1 / -1;
    padding: 12px 16px;
    border-radius: var(--radius);
    font-size: 12.5px; line-height: 1.55;
    display: flex; gap: 10px; align-items: flex-start;
}
.ctr-info-box  { background: var(--c-accent-soft); border: 1px solid #C0D4EE; color: var(--c-accent); }
.ctr-warn-box  { background: var(--c-amber-light); border: 1px solid var(--c-amber-border); color: var(--c-amber); }
.ctr-alert-box { background: var(--c-red-light); border: 1px solid var(--c-red-border); color: var(--c-red); }
.ctr-info-box i, .ctr-warn-box i, .ctr-alert-box i { margin-top: 1px; flex-shrink: 0; }

/* ---- Botão carregar modelo ---- */
.btn-template {
    font-size: 11px; font-weight: 600;
    color: var(--c-accent); background: none; border: none;
    cursor: pointer; font-family: var(--font-body);
    display: flex; align-items: center; gap: 4px;
    transition: color .15s;
}
.btn-template:hover { color: var(--c-accent-hover); text-decoration: underline; }

/* ---- Botões de ação ---- */
.ctr-actions {
    display: flex; justify-content: flex-end; align-items: center; gap: 10px;
    padding-top: 20px; margin-top: 6px;
    border-top: 1px solid var(--c-border);
}
.btn-cancel {
    padding: 9px 20px; font-size: 13px; font-weight: 500;
    font-family: var(--font-body);
    border: 1px solid var(--c-border); border-radius: var(--radius);
    background: var(--c-surface); color: var(--c-text-2);
    cursor: pointer; transition: background .15s;
}
.btn-cancel:hover { background: #F0F4FA; }
.btn-draft {
    padding: 9px 20px; font-size: 13px; font-weight: 500;
    font-family: var(--font-body);
    border: 1px solid var(--c-amber-border); border-radius: var(--radius);
    background: var(--c-amber-light); color: var(--c-amber);
    cursor: pointer; transition: background .15s;
}
.btn-draft:hover { background: #FFF0B3; }
.btn-save {
    padding: 9px 26px; font-size: 13px; font-weight: 600;
    font-family: var(--font-body);
    border: none; border-radius: var(--radius);
    background: var(--c-accent); color: #fff;
    cursor: pointer; transition: background .15s, transform .1s;
    box-shadow: 0 2px 8px rgba(30,77,140,.25);
    display: flex; align-items: center; gap: 7px;
}
.btn-save:hover { background: var(--c-accent-hover); }
.btn-save:active { transform: scale(.98); }

/* ---- Arquivo ---- */
.ctr-file-input {
    padding: 7px 10px; font-size: 13px;
    border: 1px dashed var(--c-border-md);
    border-radius: var(--radius);
    background: var(--c-bg); width: 100%;
    cursor: pointer; color: var(--c-text-2);
}

/* ---- Arquivo atual ---- */
.ctr-file-current {
    display: flex; align-items: center; gap: 6px;
    font-size: 12px; color: var(--c-text-3); margin-top: 5px;
}
.ctr-file-current a { color: var(--c-accent); text-decoration: none; }
.ctr-file-current a:hover { text-decoration: underline; }

/* ---- Responsivo ---- */
@media (max-width: 768px) {
    .ctr-grid-4, .ctr-grid-3 { grid-template-columns: 1fr 1fr; }
    .ctr-grid { grid-template-columns: 1fr; }
    .ctr-span2, .ctr-span3 { grid-column: 1 / -1; }
    .ctr-step-num { display: none; }
    .ctr-step { font-size: 10px; }
    .ctr-progress { flex-wrap: wrap; }
    .ctr-header { flex-direction: column; }
}
@media (max-width: 480px) {
    .ctr-grid-4, .ctr-grid-3 { grid-template-columns: 1fr; }
    .ctr-section-body { padding: 16px; }
    .ctr-progress { display: none; }
}
</style>

<div class="ctr-form-wrap">

<form action="<?= $actionUrl ?>" method="POST" enctype="multipart/form-data" id="contrato-form">
<?php if ($isEdit): ?>
    <input type="hidden" name="id" value="<?= val($contratoData, 'id') ?>">
<?php elseif (!empty($contratoData['cloned_from_id'])): ?>
    <input type="hidden" name="cloned_from_id" value="<?= $contratoData['cloned_from_id'] ?>">
<?php endif; ?>

<!-- ID do Cliente vinculado (populado via busca de CNPJ ou carregamento inicial) -->
<input type="hidden" name="cliente_id" id="cliente_id_hidden" value="<?= val($contratoData, 'cliente_id') ?>">

<!-- ════════ CABEÇALHO ════════ -->
<div class="ctr-header">
    <div class="ctr-header-left">
        <div class="ctr-header-icon"><i class="fas fa-file-signature"></i></div>
        <div>
            <div class="ctr-title"><?= $isEdit ? 'Editar Contrato' : 'Novo Contrato' ?></div>
            <div class="ctr-subtitle">Preencha todas as seções para gerar um contrato completo e juridicamente estruturado</div>
        </div>
    </div>
    <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 8px;">
        <div class="ctr-status-badge <?= strtolower(str_replace(' ', '-', $contratoData['status'] ?? 'Rascunho')) ?>">
            <i class="fas fa-circle" style="font-size:7px"></i>
            <?= htmlspecialchars($contratoData['status'] ?? 'Rascunho') ?>
        </div>
        <button type="button" class="btn-cancel btn-voltar-trigger" style="padding: 6px 12px; font-size: 11px; font-weight: 600;">
            <i class="fas fa-arrow-left" style="margin-right:4px"></i> Voltar
        </button>
    </div>
</div>

<!-- ════════ BARRA DE PROGRESSO ════════ -->
<div class="ctr-progress">
    <div class="ctr-step active" data-sec="sec-identificacao"><span class="ctr-step-num">1.</span> Identificação</div>
    <div class="ctr-step" data-sec="sec-partes"><span class="ctr-step-num">2.</span> Partes</div>
    <div class="ctr-step" data-sec="sec-objeto"><span class="ctr-step-num">3.</span> Objeto</div>
    <div class="ctr-step" data-sec="sec-pagamento"><span class="ctr-step-num">4.</span> Pagamento</div>
    <div class="ctr-step" data-sec="sec-lgpd"><span class="ctr-step-num">5.</span> LGPD</div>
    <div class="ctr-step" data-sec="sec-rescisao"><span class="ctr-step-num">6.</span> Rescisão</div>
    <div class="ctr-step" data-sec="sec-documentos"><span class="ctr-step-num">7.</span> Documentos</div>
</div>


<!-- ════════ 1. IDENTIFICAÇÃO ════════ -->
<div class="ctr-section" id="sec-identificacao">
    <div class="ctr-section-header" onclick="ctrToggle(this)">
        <div class="ctr-section-num num-blue">1</div>
        <div>
            <div class="ctr-section-label">Identificação do Contrato</div>
            <div class="ctr-section-desc">Dados gerais, tipo, status e foro</div>
        </div>
        <div style="flex:1"></div>
        <span class="ctr-badge badge-required">Obrigatório</span>
        <i class="fas fa-chevron-down ctr-chevron open"></i>
    </div>
    <div class="ctr-section-body">
        <div class="ctr-grid-4">
            <div class="ctr-field ctr-span2">
                <label class="ctr-label" for="titulo">Título do Contrato <span class="req">*</span></label>
                <input type="text" id="titulo" name="titulo" class="ctr-input"
                       value="<?= val($contratoData, 'titulo') ?>" required
                       placeholder="Ex: Contrato de Prestação de Serviços de TI">
            </div>
            <div class="ctr-field">
                <label class="ctr-label" for="numero_contrato">Número / Código</label>
                <input type="text" id="numero_contrato" name="numero_contrato" class="ctr-input"
                       value="<?= val($contratoData, 'numero_contrato') ?>"
                       placeholder="Ex: CTR-2026-001"
                       pattern="CTR-[0-9]{4}-[0-9]{3}"
                       title="O formato deve ser CTR-YYYY-NNN (ex: CTR-2026-001)">
            </div>
            <div class="ctr-field">
                <label class="ctr-label" for="numero_contrato_cliente">ID/CTR-CLIENTE</label>
                <input type="text" id="numero_contrato_cliente" name="numero_contrato_cliente" class="ctr-input"
                       value="<?= val($contratoData, 'numero_contrato_cliente') ?>"
                       placeholder="Ref. externa no cliente">
            </div>
            <div class="ctr-field ctr-span2">
                <label class="ctr-label" for="base_referencia">Base de Referência</label>
                <input type="text" id="base_referencia" name="base_referencia" class="ctr-input"
                       value="<?= val($contratoData, 'base_referencia') ?>"
                       placeholder="Ex: Unidade Manaus, Filial SP">
                <span class="ctr-helper">Unidade do cliente onde o serviço será executado.</span>
            </div>

            <div class="ctr-field">
                <label class="ctr-label" for="tipo">Tipo de Contrato <span class="req">*</span></label>
                <select id="tipo" name="tipo" class="ctr-select" required>
                    <option value="">Selecione...</option>
                    <?php foreach (['Prestação de Serviço','Compra / Fornecimento','Parceria','Locação','Consultoria','Licença de Software','Empreitada','Outro'] as $t): ?>
                        <option value="<?= $t ?>" <?= sel($contratoData ?? [], 'tipo', $t) ?>><?= $t ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="ctr-field">
                <label class="ctr-label" for="status">Status</label>
                <select id="status" name="status" class="ctr-select" required>
                    <?php foreach (['Rascunho','Em Vigência','Pendente Assinatura','Finalizado','Cancelado','Suspenso'] as $s): ?>
                        <option value="<?= $s ?>" <?= sel($contratoData ?? [], 'status', $s) ?>><?= $s ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="ctr-field ctr-span2">
                <label class="ctr-label" for="foro_eleicao">Foro de Eleição</label>
                <input type="text" id="foro_eleicao" name="foro_eleicao" class="ctr-input"
                       value="<?= val($contratoData, 'foro_eleicao') ?>"
                       placeholder="Ex: Comarca de Manaus/AM">
                <span class="ctr-helper">Cidade onde eventuais disputas serão julgadas</span>
            </div>
            <div class="ctr-field">
                <label class="ctr-label" for="lei_aplicavel">Lei Aplicável</label>
                <select id="lei_aplicavel" name="lei_aplicavel" class="ctr-select">
                    <?php foreach (['Direito Brasileiro (CC/2002)','CDC — Código de Defesa do Consumidor','Lei de Licitações (14.133/2021)','Outro'] as $l): ?>
                        <option value="<?= $l ?>" <?= sel($contratoData ?? [], 'lei_aplicavel', $l) ?>><?= $l ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="ctr-field">
                <label class="ctr-label" for="resolucao_disputas">Resolução de Disputas</label>
                <select id="resolucao_disputas" name="resolucao_disputas" class="ctr-select">
                    <?php foreach (['Judicial — foro eleito','Mediação extrajudicial','Arbitragem (Lei 9.307/96)','Câmara de arbitragem'] as $r): ?>
                        <option value="<?= $r ?>" <?= sel($contratoData ?? [], 'resolucao_disputas', $r) ?>><?= $r ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>
</div>


<!-- ════════ 2. PARTES ════════ -->
<div class="ctr-section" id="sec-partes">
    <div class="ctr-section-header" onclick="ctrToggle(this)">
        <div class="ctr-section-num num-blue">2</div>
        <div>
            <div class="ctr-section-label">Partes Envolvidas</div>
            <div class="ctr-section-desc">Contratante e Contratado — dados completos para validade jurídica</div>
        </div>
        <div style="flex:1"></div>
        <span class="ctr-badge badge-required">Obrigatório</span>
        <i class="fas fa-chevron-down ctr-chevron open"></i>
    </div>
    <div class="ctr-section-body">
        <div class="ctr-grid">

            <!-- CONTRATANTE -->
            <div class="ctr-divider ctr-full"><span><i class="fas fa-user-tie" style="margin-right:5px"></i>Contratante</span></div>

            <div class="ctr-field">
                <label class="ctr-label" for="contratante_nome">Nome / Razão Social <span class="req">*</span></label>
                <input type="text" id="contratante_nome" name="contratante_nome" class="ctr-input"
                       value="<?= val($contratoData, 'contratante_nome') ?>" required
                       placeholder="Nome completo ou razão social">
            </div>
            <div class="ctr-field">
                <label class="ctr-label" for="contratante_documento">CPF / CNPJ</label>
                <div class="ctr-input-group">
                    <input type="text" id="contratante_documento" name="contratante_documento" class="ctr-input"
                           value="<?= val($contratoData, 'contratante_documento') ?>"
                           placeholder="000.000.000-00 ou 00.000.000/0001-00">
                    <button type="button" class="btn-cnpj" onclick="executarBuscaCnpj('contratante', this)">
                        <i class="fas fa-search"></i> Buscar
                    </button>
                </div>
            </div>
            <div class="ctr-field ctr-full">
                <label class="ctr-label" for="contratante_endereco">Endereço Completo</label>
                <input type="text" id="contratante_endereco" name="contratante_endereco" class="ctr-input"
                       value="<?= val($contratoData, 'contratante_endereco') ?>"
                       placeholder="Rua, número, complemento, bairro, cidade - UF, CEP">
            </div>
            <div class="ctr-field">
                <label class="ctr-label" for="contratante_email">E-mail</label>
                <input type="email" id="contratante_email" name="contratante_email" class="ctr-input"
                       value="<?= val($contratoData, 'contratante_email') ?>"
                       placeholder="email@empresa.com.br">
            </div>
            <div class="ctr-field">
                <label class="ctr-label" for="contratante_telefone">Telefone / WhatsApp</label>
                <input type="text" id="contratante_telefone" name="contratante_telefone" class="ctr-input"
                       value="<?= val($contratoData, 'contratante_telefone') ?>"
                       placeholder="(00) 00000-0000">
            </div>
            <div class="ctr-field">
                <label class="ctr-label" for="contratante_representante">Representante Legal</label>
                <input type="text" id="contratante_representante" name="contratante_representante" class="ctr-input"
                       value="<?= val($contratoData, 'contratante_representante') ?>"
                       placeholder="Nome do signatário e cargo">
            </div>
            <div class="ctr-field">
                <label class="ctr-label" for="contratante_rg_cpf_rep">CPF do Representante</label>
                <input type="text" id="contratante_rg_cpf_rep" name="contratante_rg_cpf_rep" class="ctr-input"
                       value="<?= val($contratoData, 'contratante_rg_cpf_rep') ?>"
                       placeholder="000.000.000-00">
            </div>

            <!-- CONTRATADO -->
            <div class="ctr-divider ctr-full"><span><i class="fas fa-user-cog" style="margin-right:5px"></i>Contratado</span></div>

            <div class="ctr-field">
                <label class="ctr-label" for="contratado_nome">Nome / Razão Social <span class="req">*</span></label>
                <input type="text" id="contratado_nome" name="contratado_nome" class="ctr-input"
                       value="<?= val($contratoData, 'contratado_nome') ?>" required
                       placeholder="Nome completo ou razão social">
            </div>
            <div class="ctr-field">
                <label class="ctr-label" for="contratado_documento">CPF / CNPJ</label>
                <div class="ctr-input-group">
                    <input type="text" id="contratado_documento" name="contratado_documento" class="ctr-input"
                           value="<?= val($contratoData, 'contratado_documento') ?>"
                           placeholder="000.000.000-00 ou 00.000.000/0001-00">
                    <button type="button" class="btn-cnpj" onclick="executarBuscaCnpj('contratado', this)">
                        <i class="fas fa-search"></i> Buscar
                    </button>
                </div>
            </div>
            <div class="ctr-field ctr-full">
                <label class="ctr-label" for="contratado_endereco">Endereço Completo</label>
                <input type="text" id="contratado_endereco" name="contratado_endereco" class="ctr-input"
                       value="<?= val($contratoData, 'contratado_endereco') ?>"
                       placeholder="Rua, número, complemento, bairro, cidade - UF, CEP">
            </div>
            <div class="ctr-field">
                <label class="ctr-label" for="contratado_email">E-mail</label>
                <input type="email" id="contratado_email" name="contratado_email" class="ctr-input"
                       value="<?= val($contratoData, 'contratado_email') ?>"
                       placeholder="email@prestador.com.br">
            </div>
            <div class="ctr-field">
                <label class="ctr-label" for="contratado_telefone">Telefone / WhatsApp</label>
                <input type="text" id="contratado_telefone" name="contratado_telefone" class="ctr-input"
                       value="<?= val($contratoData, 'contratado_telefone') ?>"
                       placeholder="(00) 00000-0000">
            </div>
            <div class="ctr-field">
                <label class="ctr-label" for="contratado_representante">Representante Legal</label>
                <input type="text" id="contratado_representante" name="contratado_representante" class="ctr-input"
                       value="<?= val($contratoData, 'contratado_representante') ?>"
                       placeholder="Nome do signatário e cargo">
            </div>
            <div class="ctr-field">
                <label class="ctr-label" for="contratado_rg_cpf_rep">CPF do Representante</label>
                <input type="text" id="contratado_rg_cpf_rep" name="contratado_rg_cpf_rep" class="ctr-input"
                       value="<?= val($contratoData, 'contratado_rg_cpf_rep') ?>"
                       placeholder="000.000.000-00">
            </div>
        </div>
    </div>
</div>


<!-- ════════ 3. OBJETO, PRAZOS E RESPONSABILIDADES ════════ -->
<div class="ctr-section" id="sec-objeto">
    <div class="ctr-section-header" onclick="ctrToggle(this)">
        <div class="ctr-section-num num-blue">3</div>
        <div>
            <div class="ctr-section-label">Objeto, Prazos e Responsabilidades</div>
            <div class="ctr-section-desc">Definição detalhada do que será entregue, quando e por quem</div>
        </div>
        <div style="flex:1"></div>
        <span class="ctr-badge badge-required">Obrigatório</span>
        <i class="fas fa-chevron-down ctr-chevron open"></i>
    </div>
    <div class="ctr-section-body">
        <div class="ctr-grid">
            <div class="ctr-field ctr-full">
                <div class="ctr-field-header">
                    <label class="ctr-label" for="objeto">Objeto do Contrato <span class="req">*</span></label>                    
                    <?php if (!empty($settings['modelo_padrao'])): ?>
                        <button type="button" data-target="objeto" class="btn-template btn-load-template">
                            <i class="fas fa-file-alt"></i> Carregar Modelo
                        </button>
                    <?php endif; ?>
                </div>
                <textarea id="objeto" name="objeto" class="ctr-textarea" rows="6" required
                          placeholder="Descreva detalhadamente o objeto: o que será entregue, como, com qual qualidade, critérios de aceite e exclusões de escopo..."><?= val($contratoData, 'objeto') ?></textarea>
                <span class="ctr-helper">Seja específico — contratos vagos são fontes de litígio</span>
            </div>

            <div class="ctr-field ctr-full">
                <div class="ctr-field-header">
                    <label class="ctr-label" for="responsabilidades_contratante">Responsabilidades do Contratante</label>
                    <?php if (!empty($settings['modelo_responsabilidades_contratante'])): ?>
                        <button type="button" data-target="responsabilidades_contratante" class="btn-template btn-load-template">
                            <i class="fas fa-file-alt"></i> Carregar Modelo
                        </button>
                    <?php endif; ?>
                </div>
                <textarea id="responsabilidades_contratante" name="responsabilidades_contratante"
                          class="ctr-textarea" rows="3"
                          placeholder="Liste o que o contratante deve prover: acesso, informações, recursos, aprovações, pagamentos em prazo..."><?= val($contratoData, 'responsabilidades_contratante') ?></textarea>
            </div>
            <div class="ctr-field ctr-full">
                <div class="ctr-field-header">
                    <label class="ctr-label" for="responsabilidades_contratado">Responsabilidades do Contratado</label>
                    <?php if (!empty($settings['modelo_responsabilidades_contratado'])): ?>
                        <button type="button" data-target="responsabilidades_contratado" class="btn-template btn-load-template">
                            <i class="fas fa-file-alt"></i> Carregar Modelo
                        </button>
                    <?php endif; ?>
                </div>
                <textarea id="responsabilidades_contratado" name="responsabilidades_contratado"
                          class="ctr-textarea" rows="3"
                          placeholder="Liste as obrigações do contratado: entregas, prazos, qualidade, padrões técnicos, SLAs, disponibilidade..."><?= val($contratoData, 'responsabilidades_contratado') ?></textarea>
            </div>

            <div class="ctr-field">
                <label class="ctr-label" for="data_inicio">Data de Início <span class="req">*</span></label>
                <input type="date" id="data_inicio" name="data_inicio" class="ctr-input"
                       value="<?= val($contratoData, 'data_inicio') ?>">
            </div>
            <div class="ctr-field">
                <label class="ctr-label" for="vencimento">Data de Término</label>
                <input type="date" id="vencimento" name="vencimento" class="ctr-input"
                       value="<?= val($contratoData, 'vencimento') ?>">
            </div>
            <div class="ctr-field">
                <label class="ctr-label" for="duracao_meses">Duração (meses)</label>
                <input type="number" id="duracao_meses" name="duracao_meses" class="ctr-input"
                       value="<?= val($contratoData, 'duracao_meses') ?>"
                       min="1" placeholder="Ex: 12">
            </div>
            <div class="ctr-field">
                <label class="ctr-label" for="renovacao_automatica">Renovação Automática</label>
                <select id="renovacao_automatica" name="renovacao_automatica" class="ctr-select">
                    <?php foreach (['Não se aplica','Sim — automática','Sim — aviso 30 dias','Sim — aviso 60 dias','Não — encerra na data final'] as $rv): ?>
                        <option value="<?= $rv ?>" <?= sel($contratoData ?? [], 'renovacao_automatica', $rv) ?>><?= $rv ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="ctr-field ctr-full">
                <label class="ctr-label" for="criterios_aceite">Critérios de Aceite e Entrega</label>
                <textarea id="criterios_aceite" name="criterios_aceite"
                          class="ctr-textarea" rows="3"
                          placeholder="Como será validada a entrega? Testes, inspeção, homologação, prazo de aprovação silenciosa..."><?= val($contratoData, 'criterios_aceite') ?></textarea>
            </div>
        </div>
    </div>
</div>


<!-- ════════ 4. PAGAMENTO, MULTAS E INADIMPLÊNCIA ════════ -->
<div class="ctr-section" id="sec-pagamento">
    <div class="ctr-section-header" onclick="ctrToggle(this)">
        <div class="ctr-section-num num-green">4</div>
        <div>
            <div class="ctr-section-label">Cláusula de Pagamento</div>
            <div class="ctr-section-desc">Condições, vencimentos, multas e inadimplência</div>
        </div>
        <div style="flex:1"></div>
        <span class="ctr-badge badge-finance">Financeiro</span>
        <i class="fas fa-chevron-down ctr-chevron open"></i>
    </div>
    <div class="ctr-section-body">
        <div class="ctr-grid-4">
            <div class="ctr-field">
                <label class="ctr-label" for="valor">Valor Total (R$) <span class="req">*</span></label>
                <input type="text" id="valor" name="valor" class="ctr-input"
                       value="<?= $isEdit ? number_format($contratoData['valor'] ?? 0, 2, ',', '.') : '' ?>"
                       placeholder="0,00">
            </div>
            <div class="ctr-field">
                <label class="ctr-label" for="condicao_pagamento">Condição de Pagamento</label>
                <select id="condicao_pagamento" name="condicao_pagamento" class="ctr-select">
                    <?php foreach (['À vista','Mensal','Quinzenal','Semanal','Parcelado','Por entrega / milestone','Outro'] as $cp): ?>
                        <option value="<?= $cp ?>" <?= sel($contratoData ?? [], 'condicao_pagamento', $cp) ?>><?= $cp ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="ctr-field">
                <label class="ctr-label" for="dia_vencimento">Dia de Vencimento</label>
                <input type="number" id="dia_vencimento" name="dia_vencimento" class="ctr-input"
                       value="<?= val($contratoData, 'dia_vencimento') ?>"
                       min="1" max="31" placeholder="Ex: 10">
                <span class="ctr-helper">Dia do mês para pagamento</span>
            </div>
            <div class="ctr-field">
                <label class="ctr-label" for="forma_pagamento">Forma de Pagamento</label>
                <select id="forma_pagamento" name="forma_pagamento" class="ctr-select">
                    <?php foreach (['PIX','Transferência Bancária (TED)','Boleto','Cartão de Crédito','Cheque','Outro'] as $fp): ?>
                        <option value="<?= $fp ?>" <?= sel($contratoData ?? [], 'forma_pagamento', $fp) ?>><?= $fp ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="ctr-field">
                <label class="ctr-label" for="pix_tipo_chave">Tipo de Chave PIX</label>
                <select id="pix_tipo_chave" name="pix_tipo_chave" class="ctr-select">
                    <option value="">Nenhum / Não informado</option>
                    <?php foreach (['CPF','CNPJ','E-mail','Celular','Chave Aleatória'] as $tk): ?>
                        <option value="<?= $tk ?>" <?= sel($contratoData ?? [], 'pix_tipo_chave', $tk) ?>><?= $tk ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="ctr-field">
                <label class="ctr-label" for="dados_bancarios">Dados Bancários / Chave PIX</label>
                <input type="text" id="dados_bancarios" name="dados_bancarios" class="ctr-input"
                       value="<?= val($contratoData, 'dados_bancarios') ?>"
                       placeholder="Banco, agência, conta ou chave PIX">
            </div>
            <div class="ctr-field">
                <label class="ctr-label" for="valor_sinal">Valor do Sinal / Entrada (R$)</label>
                <input type="text" id="valor_sinal" name="valor_sinal" class="ctr-input"
                       value="<?= val($contratoData, 'valor_sinal') ?>"
                       placeholder="0,00">
            </div>
            <div class="ctr-field">
                <label class="ctr-label" for="numero_parcelas">Número de Parcelas</label>
                <input type="number" id="numero_parcelas" name="numero_parcelas" class="ctr-input"
                       value="<?= val($contratoData, 'numero_parcelas') ?>"
                       min="1" placeholder="Ex: 12">
            </div>

            <!-- Multas -->
            <div class="ctr-divider ctr-full"><span><i class="fas fa-exclamation-triangle" style="margin-right:5px"></i>Multas e Inadimplência</span></div>

            <div class="ctr-field">
                <label class="ctr-label" for="multa_atraso">Multa por Atraso (%)</label>
                <input type="number" id="multa_atraso" name="multa_atraso" class="ctr-input"
                       value="<?= val($contratoData, 'multa_atraso') ?>"
                       step="0.5" min="0" max="10" placeholder="Ex: 2">
                <span class="ctr-helper">Limite legal: 2% ao mês (CDC)</span>
            </div>
            <div class="ctr-field">
                <label class="ctr-label" for="juros_mora">Juros de Mora (% ao mês)</label>
                <input type="number" id="juros_mora" name="juros_mora" class="ctr-input"
                       value="<?= val($contratoData, 'juros_mora') ?>"
                       step="0.1" min="0" placeholder="Ex: 1">
            </div>
            <div class="ctr-field">
                <label class="ctr-label" for="correcao_monetaria">Correção Monetária</label>
                <select id="correcao_monetaria" name="correcao_monetaria" class="ctr-select">
                    <?php foreach (['Nenhuma','IPCA (mensal)','IGP-M (mensal)','INPC (mensal)','Selic'] as $cm): ?>
                        <option value="<?= $cm ?>" <?= sel($contratoData ?? [], 'correcao_monetaria', $cm) ?>><?= $cm ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="ctr-field">
                <label class="ctr-label" for="prazo_carencia_multa">Prazo de Carência (dias)</label>
                <input type="number" id="prazo_carencia_multa" name="prazo_carencia_multa" class="ctr-input"
                       value="<?= val($contratoData, 'prazo_carencia_multa') ?>"
                       min="0" placeholder="Ex: 5">
                <span class="ctr-helper">Dias antes de aplicar a multa</span>
            </div>
            <div class="ctr-field ctr-span2">
                <label class="ctr-label" for="penalidade_descumprimento">Penalidade por Descumprimento do Objeto</label>
                <input type="text" id="penalidade_descumprimento" name="penalidade_descumprimento" class="ctr-input"
                       value="<?= val($contratoData, 'penalidade_descumprimento') ?>"
                       placeholder="Ex: R$ 5.000,00 por evento ou 10% do valor total">
            </div>
            <div class="ctr-field ctr-span2">
                <label class="ctr-label" for="multa_rescisao_antecipada">Multa por Rescisão Antecipada</label>
                <input type="text" id="multa_rescisao_antecipada" name="multa_rescisao_antecipada" class="ctr-input"
                       value="<?= val($contratoData, 'multa_rescisao_antecipada') ?>"
                       placeholder="Ex: 20% do valor restante">
            </div>
            <div class="ctr-info-box ctr-full">
                <i class="fas fa-info-circle"></i>
                <span>Multas de mora acima de 2% ao mês em relações de consumo podem ser reduzidas judicialmente (art. 52, §1º, CDC). Em contratos B2B, as partes podem pactuar livremente. Inclua a fundamentação legal na seção de cláusulas adicionais.</span>
            </div>
            <div class="ctr-field ctr-full">
                <label class="ctr-label" for="observacoes_financeiras">Observações Financeiras</label>
                <textarea id="observacoes_financeiras" name="observacoes_financeiras"
                          class="ctr-textarea" rows="2"
                          placeholder="Notas sobre reajustes, descontos, condições especiais de pagamento..."><?= val($contratoData, 'observacoes_financeiras') ?></textarea>
            </div>
        </div>
    </div>
</div>


<!-- ════════ 5. CONFIDENCIALIDADE E LGPD ════════ -->
<div class="ctr-section" id="sec-lgpd">
    <div class="ctr-section-header" onclick="ctrToggle(this)">
        <div class="ctr-section-num num-gold">5</div>
        <div>
            <div class="ctr-section-label">Confidencialidade e Proteção de Dados (LGPD)</div>
            <div class="ctr-section-desc">Sigilo, tratamento de dados pessoais e conformidade legal</div>
        </div>
        <div style="flex:1"></div>
        <span class="ctr-badge badge-legal">LGPD</span>
        <i class="fas fa-chevron-down ctr-chevron open"></i>
    </div>
    <div class="ctr-section-body">
        <div class="ctr-grid">
            <div class="ctr-field ctr-full">
                <label class="ctr-label">Tipo de Dado / Nível de Confidencialidade</label>
                <div class="ctr-tag-group" id="tag-confidencialidade">
                    <?php
                    $tagsCfg = ['Nenhum','Dados de negócio','Dados pessoais (LGPD)','Dados sensíveis (art. 11)','Segredos industriais','Dados financeiros','Dados de menores'];
                    $tagsSel = json_decode($contratoData['confidencialidade_tags'] ?? '[]', true) ?: [];
                    foreach ($tagsCfg as $tag):
                        $active = in_array($tag, $tagsSel) ? 'active' : '';
                    ?>
                        <div class="ctr-tag <?= $active ?>" data-value="<?= htmlspecialchars($tag) ?>"><?= $tag ?></div>
                    <?php endforeach; ?>
                </div>
                <input type="hidden" id="confidencialidade_tags" name="confidencialidade_tags" value="<?= val($contratoData, 'confidencialidade_tags') ?>">
            </div>

            <div class="ctr-field">
                <label class="ctr-label" for="prazo_sigilo">Prazo de Sigilo após o Término</label>
                <select id="prazo_sigilo" name="prazo_sigilo" class="ctr-select">
                    <?php foreach (['Indefinido','12 meses','24 meses','36 meses','60 meses','Personalizado'] as $ps): ?>
                        <option value="<?= $ps ?>" <?= sel($contratoData ?? [], 'prazo_sigilo', $ps) ?>><?= $ps ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="ctr-field">
                <label class="ctr-label" for="base_legal_lgpd">Base Legal LGPD</label>
                <select id="base_legal_lgpd" name="base_legal_lgpd" class="ctr-select">
                    <?php foreach (['Execução de contrato (art. 7º, V)','Legítimo interesse (art. 7º, IX)','Consentimento (art. 7º, I)','Obrigação legal (art. 7º, II)','Não se aplica'] as $bl): ?>
                        <option value="<?= $bl ?>" <?= sel($contratoData ?? [], 'base_legal_lgpd', $bl) ?>><?= $bl ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="ctr-field">
                <label class="ctr-label" for="penalidade_violacao_sigilo">Penalidade por Violação de Sigilo</label>
                <input type="text" id="penalidade_violacao_sigilo" name="penalidade_violacao_sigilo" class="ctr-input"
                       value="<?= val($contratoData, 'penalidade_violacao_sigilo') ?>"
                       placeholder="Ex: R$ 50.000,00 por ocorrência + perdas e danos">
            </div>
            <div class="ctr-field">
                <label class="ctr-label" for="dpo_encarregado">DPO / Encarregado de Dados</label>
                <input type="text" id="dpo_encarregado" name="dpo_encarregado" class="ctr-input"
                       value="<?= val($contratoData, 'dpo_encarregado') ?>"
                       placeholder="Nome e e-mail do responsável pelos dados">
            </div>
            <div class="ctr-field ctr-full">
                <label class="ctr-label" for="clausula_confidencialidade">Cláusula de Confidencialidade</label>
                <textarea id="clausula_confidencialidade" name="clausula_confidencialidade"
                          class="ctr-textarea" rows="5"
                          placeholder="Descreva as obrigações de sigilo, o que está protegido, exceções (informações já públicas, divulgação por ordem judicial) e consequências da violação..."><?= val($contratoData, 'clausula_confidencialidade') ?></textarea>
            </div>
            <div class="ctr-field ctr-full">
                <label class="ctr-check-row ctr-label">
                    <input type="checkbox" name="lgpd_conformidade" value="1" <?= chk($contratoData ?? [], 'lgpd_conformidade', false) ?>>
                    <span class="ctr-check-label">As partes declaram conformidade com a Lei Geral de Proteção de Dados — LGPD (Lei 13.709/2018)</span>
                </label>
            </div>
            <div class="ctr-field ctr-full">
                <label class="ctr-check-row ctr-label">
                    <input type="checkbox" name="transferencia_internacional" value="1" <?= chk($contratoData ?? [], 'transferencia_internacional') ?>>
                    <span class="ctr-check-label">Há transferência internacional de dados pessoais (requer salvaguardas específicas)</span>
                </label>
            </div>
            <div class="ctr-field ctr-full">
                <label class="ctr-check-row ctr-label">
                    <input type="checkbox" name="subcontratacao_dados" value="1" <?= chk($contratoData ?? [], 'subcontratacao_dados') ?>>
                    <span class="ctr-check-label">O contratado poderá subcontratar serviços que envolvam acesso a dados pessoais (require cláusula específica)</span>
                </label>
            </div>
        </div>
    </div>
</div>


<!-- ════════ 6. RESCISÃO ════════ -->
<div class="ctr-section" id="sec-rescisao">
    <div class="ctr-section-header" onclick="ctrToggle(this)">
        <div class="ctr-section-num num-red">6</div>
        <div>
            <div class="ctr-section-label">Regras de Rescisão e Encerramento</div>
            <div class="ctr-section-desc">Condições de encerramento sem prejuízos desnecessários</div>
        </div>
        <div style="flex:1"></div>
        <span class="ctr-badge badge-legal">Jurídico</span>
        <i class="fas fa-chevron-down ctr-chevron open"></i>
    </div>
    <div class="ctr-section-body">
        <div class="ctr-grid">
            <div class="ctr-field">
                <label class="ctr-label" for="aviso_previo_rescisao">Aviso Prévio para Rescisão</label>
                <select id="aviso_previo_rescisao" name="aviso_previo_rescisao" class="ctr-select">
                    <?php foreach (['30 dias','60 dias','90 dias','Imediato (justa causa)','Personalizado'] as $ap): ?>
                        <option value="<?= $ap ?>" <?= sel($contratoData ?? [], 'aviso_previo_rescisao', $ap) ?>><?= $ap ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="ctr-field">
                <label class="ctr-label" for="rescisao_descumprimento">Rescisão por Descumprimento</label>
                <select id="rescisao_descumprimento" name="rescisao_descumprimento" class="ctr-select">
                    <?php foreach (['Imediata após notificação','Após 5 dias sem regularização','Após 15 dias sem regularização','Após 30 dias sem regularização'] as $rd): ?>
                        <option value="<?= $rd ?>" <?= sel($contratoData ?? [], 'rescisao_descumprimento', $rd) ?>><?= $rd ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="ctr-field">
                <label class="ctr-label" for="nao_concorrencia">Cláusula de Não Concorrência</label>
                <select id="nao_concorrencia" name="nao_concorrencia" class="ctr-select">
                    <?php foreach (['Não aplicável','6 meses após o término','12 meses após o término','24 meses após o término'] as $nc): ?>
                        <option value="<?= $nc ?>" <?= sel($contratoData ?? [], 'nao_concorrencia', $nc) ?>><?= $nc ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="ctr-field">
                <label class="ctr-label" for="indenizacao_rescisao">Indenização por Rescisão Antecipada</label>
                <input type="text" id="indenizacao_rescisao" name="indenizacao_rescisao" class="ctr-input"
                       value="<?= val($contratoData, 'indenizacao_rescisao') ?>"
                       placeholder="Ex: equivalente a 3 parcelas restantes">
            </div>
            <div class="ctr-field ctr-full">
                <label class="ctr-label" for="causas_rescisao_imotivada">Causas de Rescisão Imotivada (sem penalidade)</label>
                <textarea id="causas_rescisao_imotivada" name="causas_rescisao_imotivada"
                          class="ctr-textarea" rows="3"
                          placeholder="Situações em que qualquer parte pode rescindir sem penalidades: término natural, força maior, caso fortuito, acordo mútuo..."><?= val($contratoData, 'causas_rescisao_imotivada') ?></textarea>
            </div>
            <div class="ctr-field ctr-full">
                <label class="ctr-label" for="causas_justa_causa">Causas de Rescisão por Justa Causa</label>
                <textarea id="causas_justa_causa" name="causas_justa_causa"
                          class="ctr-textarea" rows="3"
                          placeholder="Eventos que caracterizam justa causa: inadimplência, descumprimento do objeto, violação de sigilo, ato ilícito, falência, insolvência..."><?= val($contratoData, 'causas_justa_causa') ?></textarea>
            </div>
            <div class="ctr-field ctr-full">
                <label class="ctr-label" for="obrigacoes_pos_encerramento">Obrigações após o Encerramento</label>
                <textarea id="obrigacoes_pos_encerramento" name="obrigacoes_pos_encerramento"
                          class="ctr-textarea" rows="3"
                          placeholder="O que ocorre após o término: devolução de materiais, transferência de arquivos, pagamentos pendentes, período de transição, sigilo continuado..."><?= val($contratoData, 'obrigacoes_pos_encerramento') ?></textarea>
            </div>
            <div class="ctr-warn-box ctr-full">
                <i class="fas fa-exclamation-triangle"></i>
                <span>Cláusulas de não concorrência superiores a 24 meses ou sem delimitação geográfica clara podem ser contestadas judicialmente por abuso de direito. Recomenda-se limitar ao máximo de 24 meses com área de atuação específica.</span>
            </div>
        </div>
    </div>
</div>


<!-- ════════ 7. CLÁUSULAS ADICIONAIS E DOCUMENTOS ════════ -->
<div class="ctr-section" id="sec-documentos">
    <div class="ctr-section-header" onclick="ctrToggle(this)">
        <div class="ctr-section-num num-gray">7</div>
        <div>
            <div class="ctr-section-label">Cláusulas Adicionais, Projeto e Documentos</div>
            <div class="ctr-section-desc">Disposições finais, assinaturas, anexos e vínculo com projetos</div>
        </div>
        <div style="flex:1"></div>
        <span class="ctr-badge badge-optional">Opcional</span>
        <i class="fas fa-chevron-down ctr-chevron open"></i>
    </div>
    <div class="ctr-section-body">
        <div class="ctr-grid">
            <div class="ctr-field ctr-full">
                <div class="ctr-field-header">
                    <label class="ctr-label" for="clausulas_adicionais">Cláusulas Adicionais / Disposições Gerais</label>
                    <?php if (!empty($settings['modelo_clausulas_adicionais'])): ?>
                        <button type="button" data-target="clausulas_adicionais" class="btn-template btn-load-template">
                            <i class="fas fa-file-alt"></i> Carregar Modelo
                        </button>
                    <?php endif; ?>
                </div>
                <textarea id="clausulas_adicionais" name="clausulas_adicionais"
                          class="ctr-textarea" rows="6"
                          placeholder="Propriedade intelectual, exclusividade, SLA detalhado, penalidades específicas, mediação e arbitragem, caso fortuito, força maior..."><?= val($contratoData, 'clausulas_adicionais') ?></textarea>
            </div>

            <div class="ctr-field">
                <label class="ctr-label" for="assinatura_tipo">Tipo de Assinatura</label>
                <select id="assinatura_tipo" name="assinatura_tipo" class="ctr-select">
                    <?php foreach (['2 testemunhas (padrão)','Assinatura digital (ICP-Brasil)','Plataforma de assinatura eletrônica','Reconhecimento de firma','Sem testemunhas'] as $at): ?>
                        <option value="<?= $at ?>" <?= sel($contratoData ?? [], 'assinatura_tipo', $at) ?>><?= $at ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="ctr-field">
                <label class="ctr-label" for="numero_vias">Número de Vias</label>
                <select id="numero_vias" name="numero_vias" class="ctr-select">
                    <?php foreach (['2 vias de igual teor','1 via (digital)','3 vias'] as $nv): ?>
                        <option value="<?= $nv ?>" <?= sel($contratoData ?? [], 'numero_vias', $nv) ?>><?= $nv ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="ctr-field">
                <label class="ctr-label" for="projeto_id">Projeto Vinculado</label>
                <select id="projeto_id" name="projeto_id" class="ctr-select">
                    <option value="">Nenhum</option>
                    <?php foreach ($projetos as $projeto): ?>
                        <option value="<?= $projeto['id'] ?>"
                            <?= ($isEdit && isset($contratoData['projeto_id']) && $contratoData['projeto_id'] == $projeto['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($projeto['nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="ctr-field">
                <label class="ctr-label" for="local_assinatura">Local de Assinatura</label>
                <input type="text" id="local_assinatura" name="local_assinatura" class="ctr-input"
                       value="<?= val($contratoData, 'local_assinatura') ?>"
                       placeholder="Ex: Manaus/AM">
            </div>
            <div class="ctr-field ctr-full">
                <label class="ctr-label" for="documento">Anexar Documento (PDF / DOCX)</label>
                <input type="file" id="documento" name="documento" accept=".pdf,.docx" class="ctr-file-input">
                <?php if ($isEdit && !empty($contratoData['documento_path'])): ?>
                    <div class="ctr-file-current">
                        <i class="fas fa-paperclip"></i>
                        Arquivo atual:
                        <a href="<?= BASE_URL ?>/contratos/download/<?= val($contratoData, 'documento_path') ?>" target="_blank">
                            <?= htmlspecialchars(substr($contratoData['documento_path'], 0, 50)) ?>
                        </a>
                    </div>
                <?php endif; ?>
                <span class="ctr-helper">Tamanho máximo: 20 MB. Formatos aceitos: PDF, DOCX</span>
            </div>
            <div class="ctr-field ctr-full">
                <label class="ctr-label" for="observacoes">Observações Internas</label>
                <textarea id="observacoes" name="observacoes"
                          class="ctr-textarea" rows="2"
                          placeholder="Notas internas sobre este contrato (não constam no documento final impresso)..."><?= val($contratoData, 'observacoes') ?></textarea>
            </div>
        </div>
    </div>
</div>


<!-- ════════ BOTÕES DE AÇÃO ════════ -->
<div class="ctr-actions">
    <button type="button" class="btn-cancel btn-voltar-trigger">
        <i class="fas fa-arrow-left" style="margin-right:5px"></i> Voltar
    </button>
    <button type="button" id="cancel-form-btn" class="btn-cancel">
        <i class="fas fa-times" style="margin-right:5px"></i> Cancelar
    </button>
    <button type="button" id="btn-salvar-rascunho" class="btn-draft">
        <i class="fas fa-save" style="margin-right:5px"></i> Salvar Rascunho
    </button>
    <button type="submit" class="btn-save">
        <i class="fas fa-check-circle"></i>
        <?= $isEdit ? 'Atualizar Contrato' : 'Salvar Contrato' ?>
    </button>
</div>

</form>
</div><!-- /ctr-form-wrap -->


<!-- ════════════════════════════════════════
     JAVASCRIPT
═══════════════════════════════════════════ -->
<script>
// Declaração tradicional para maior compatibilidade de escopo no browser
function ctrToggle(header) {
    if (!header) return;
    const section = header.closest('.ctr-section');
    const chevron = header.querySelector('.ctr-chevron');
    if (section) section.classList.toggle('collapsed');
    if (chevron) chevron.classList.toggle('open');
}

(function () {
    'use strict';

    const form = document.getElementById('contrato-form');
    // Se o formulário não for encontrado, as outras funcionalidades de formulário não inicializam, mas os botões de navegação podem rodar.

    // Detecção dinâmica e robusta do contêiner de rolagem real
    const getScrollContainer = () => {
        // Procura por containers comuns de dashboard que podem estar scrollando
        const potentialContainers = ['.content', '.main-content', 'main', '#main-wrapper', '.app-content', 'article', '.page-wrapper'];
        for (const selector of potentialContainers) {
            const el = document.querySelector(selector);
            if (el && el.scrollHeight > el.clientHeight && window.getComputedStyle(el).overflowY !== 'visible') {
                return el;
            }
        }
        
        // Fallback para verificar se o scroll está no documento principal
        if (document.documentElement.scrollHeight > window.innerHeight || document.body.scrollHeight > window.innerHeight) {
            return window;
        }

        return window;
    };

    const scrollContainer = getScrollContainer();
    let isManualScrolling = false; // Flag para evitar que o ScrollSpy sobrescreva o clique

    /* ---- Barra de progresso ---- */
    const steps = document.querySelectorAll('.ctr-step');
    steps.forEach(btn => {
        btn.addEventListener('click', function () {
            const targetId = this.dataset.sec;
            const target = document.getElementById(targetId);
            if (!target) return;

            // Garante que a seção está aberta
            const header = target.querySelector('.ctr-section-header');
            if (target.classList.contains('collapsed') && header) {
                ctrToggle(header);
            }

            // Scroll suave - encontra o container rolavel
            const offset = 115; // Ajustado para não cobrir o título da seção com headers fixos
            const targetRect = target.getBoundingClientRect();

            // ATIVAÇÃO IMEDIATA: Aplica a classe active logo no clique para feedback instantâneo
            isManualScrolling = true;
            steps.forEach(s => s.classList.remove('active'));
            this.classList.add('active');

            if (scrollContainer !== window) {
                const containerRect = scrollContainer.getBoundingClientRect();
                const absoluteElementTop = targetRect.top + scrollContainer.scrollTop - containerRect.top;
                scrollContainer.scrollTo({
                    top: absoluteElementTop - offset,
                    behavior: 'smooth'
                });
            } else {
                const absoluteElementTop = targetRect.top + window.scrollY;
                window.scrollTo({
                    top: absoluteElementTop - offset,
                    behavior: 'smooth'
                });
            }
            
            // Libera o ScrollSpy após o tempo da animação de rolagem
            setTimeout(() => { isManualScrolling = false; }, 800);
        });
    });

    // ScrollSpy: Atualiza o estado ativo da barra conforme o scroll
    scrollContainer.addEventListener('scroll', () => {
        // Se o usuário clicou em um passo, ignoramos o cálculo automático até o scroll terminar
        if (isManualScrolling) return;

        let current = "";

        document.querySelectorAll('.ctr-section').forEach(section => {
            const rect = section.getBoundingClientRect();
            // Threshold de 180px para considerar a seção como "ativa" na visualização
            if (rect.top <= 180) {
                current = section.getAttribute('id');
            }
        });

        // TRATAMENTO PARA FIM DE PÁGINA: Se o scroll chegar ao fim, ativa o último passo 
        // mesmo que a seção seja curta e não atinja o topo.
        const isBottom = (scrollContainer === window) ? 
                         (window.innerHeight + window.scrollY >= document.documentElement.scrollHeight - 50) :
                         (scrollContainer.scrollTop + scrollContainer.clientHeight >= scrollContainer.scrollHeight - 20);

        if (isBottom && steps.length > 0) {
            current = steps[steps.length - 1].dataset.sec;
        }

        if (!current && steps.length > 0) current = steps[0].dataset.sec;

        steps.forEach(step => {
            step.classList.toggle('active', step.dataset.sec === current);
        });
    });

    if (!form) return; // Retorna aqui caso form não exista para o resto dos scripts


    // Funcionalidade para o botão Cancelar (quando fora do modal / acesso direto)
    const btnCancel = document.getElementById('cancel-form-btn');
    if (btnCancel) {
        btnCancel.addEventListener('click', function() {
            // Se não houver um modal pai aberto, redireciona para a lista principal
            if (!this.closest('#form-contrato-modal')) {
                window.location.href = '<?= $baseUrl ?? '' ?>/contratos';
            }
        });
    }

    // Funcionalidade para o botão Voltar
    document.querySelectorAll('.btn-voltar-trigger').forEach(btn => {
        btn.addEventListener('click', function() {
            const btnCancelRef = document.getElementById('cancel-form-btn');
            if (btnCancelRef) btnCancelRef.click(); // Aciona o evento de clique do botão Cancelar
        });
    });

    // Prevenção de duplicidade (Duplo clique)
    form.addEventListener('submit', function() {
        const btnSave = form.querySelector('.btn-save');
        if (btnSave) {
            btnSave.disabled = true;
            btnSave.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processando...';
        }
    });

    /* ---- Tags de Confidencialidade ---- */
    const tagGroup  = document.getElementById('tag-confidencialidade');
    const tagHidden = document.getElementById('confidencialidade_tags');

    if (tagGroup) {
        tagGroup.addEventListener('click', function (e) {
            const tag = e.target.closest('.ctr-tag');
            if (!tag) return;
            tag.classList.toggle('active');
            const selected = [...tagGroup.querySelectorAll('.ctr-tag.active')].map(t => t.dataset.value);
            if (tagHidden) tagHidden.value = JSON.stringify(selected);
        });
    }

    /* ---- Salvar Rascunho ---- */
    const btnRascunho = document.getElementById('btn-salvar-rascunho');
    if (btnRascunho) {
        btnRascunho.addEventListener('click', function () {
            const statusSel = form.querySelector('[name="status"]');
            if (statusSel) statusSel.value = 'Rascunho';
            form.submit();
        });
    }

    /* ---- Carregar Modelo Padrão ---- */
    document.querySelectorAll('.btn-load-template').forEach(button => {
        button.addEventListener('click', function () {
            const targetField = this.dataset.target;
            const textarea = form.querySelector(`textarea[name="${targetField}"]`);
            let modelContent = '';

            // Use a variável settings diretamente do PHP
            const settings = <?= json_encode($settings ?? []) ?>;

            if (targetField === 'objeto') {
                modelContent = settings.modelo_padrao ?? '';
            } else if (targetField === 'responsabilidades_contratante') {
                modelContent = settings.modelo_responsabilidades_contratante ?? '';
            } else if (targetField === 'responsabilidades_contratado') {
                modelContent = settings.modelo_responsabilidades_contratado ?? '';
            } else if (targetField === 'clausulas_adicionais') {
                modelContent = settings.modelo_clausulas_adicionais ?? '';
            }
            if (modelContent && (textarea.value.trim() === '' || confirm('Isso substituirá o texto atual pelo modelo padrão. Deseja continuar?'))) {
                textarea.value = modelContent;
            }
        });
    });

    /* ---- Máscara CPF / CNPJ ---- */
    form.querySelectorAll('[name$="_documento"]').forEach(input => {
        input.addEventListener('input', function (e) {
            let v = e.target.value.replace(/\D/g, '');
            if (v.length <= 11) {
                v = v.replace(/(\d{3})(\d)/, '$1.$2')
                     .replace(/(\d{3})(\d)/, '$1.$2')
                     .replace(/(\d{3})(\d{1,2})$/, '$1-$2');
            } else {
                v = v.replace(/^(\d{2})(\d)/, '$1.$2')
                     .replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3')
                     .replace(/\.(\d{3})(\d)/, '.$1/$2')
                     .replace(/(\d{4})(\d)/, '$1-$2');
            }
            e.target.value = v.substring(0, 18);
        });
    });

    /* ---- Máscara CPF simples (representantes) ---- */
    ['contratante_rg_cpf_rep','contratado_rg_cpf_rep'].forEach(name => {
        const input = form.querySelector(`[name="${name}"]`);
        if (!input) return;
        input.addEventListener('input', function (e) {
            let v = e.target.value.replace(/\D/g, '').substring(0, 11);
            v = v.replace(/(\d{3})(\d)/, '$1.$2')
                 .replace(/(\d{3})(\d)/, '$1.$2')
                 .replace(/(\d{3})(\d{1,2})$/, '$1-$2');
            e.target.value = v;
        });
    });

    /* ---- Máscara de Telefone ---- */
    form.querySelectorAll('[name$="_telefone"]').forEach(input => {
        input.addEventListener('input', function (e) {
            let v = e.target.value.replace(/\D/g, '').substring(0, 11);
            if (v.length >= 11) {
                v = v.replace(/^(\d{2})(\d{5})(\d{4})$/, '($1) $2-$3');
            } else if (v.length >= 10) {
                v = v.replace(/^(\d{2})(\d{4})(\d{4})$/, '($1) $2-$3');
            } else if (v.length > 2) {
                v = v.replace(/^(\d{2})(\d+)/, '($1) $2');
            }
            e.target.value = v;
        });
    });

    /* ---- Máscara de Moeda ---- */
    ['valor', 'valor_sinal'].forEach(id => {
        const input = form.querySelector(`#${id}`);
        if (!input) return;
        input.addEventListener('input', function (e) {
            let value = e.target.value.replace(/\D/g, '');
            value = (parseInt(value || '0') / 100).toLocaleString('pt-BR', { minimumFractionDigits: 2 });
            e.target.value = value === '0,00' ? '' : value;
        });
    });

    /* ---- Cálculo automático de duração (meses) ---- */
    const dtInicio   = form.querySelector('#data_inicio');
    const dtTermino  = form.querySelector('#vencimento');
    const dtDuracao  = form.querySelector('#duracao_meses');

    function calcDuracao() {
        if (!dtInicio.value || !dtTermino.value || !dtDuracao) return;
        const inicio  = new Date(dtInicio.value);
        const termino = new Date(dtTermino.value);
        if (termino <= inicio) { dtDuracao.value = ''; return; }
        const meses = (termino.getFullYear() - inicio.getFullYear()) * 12 + (termino.getMonth() - inicio.getMonth());
        dtDuracao.value = meses > 0 ? meses : '';
    }

    if (dtInicio)  dtInicio.addEventListener('change', calcDuracao);
    if (dtTermino) dtTermino.addEventListener('change', calcDuracao);

    /* ════ Busca CNPJ ════ */
    window.executarBuscaCnpj = async function (prefix, btnEl = null) {
        const currentForm = btnEl ? btnEl.closest('form') : (form || document.getElementById('contrato-form'));
        if (!currentForm) return;

        const docInput = currentForm.querySelector(`[name="${prefix}_documento"]`);
        if (!docInput) return;

        const cnpj     = docInput.value.replace(/\D/g, '');

        if (cnpj.length !== 14) {
            alert('Por favor, insira um CNPJ válido (14 dígitos) para buscar.');
            return;
        }

        const btn = btnEl || (typeof event !== 'undefined' ? event.currentTarget : null);
        const originalHTML = btn ? btn.innerHTML : '';
        if (btn) {
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Buscando...';
            btn.disabled  = true;
        }

        const setField = (name, value) => {
            const el = currentForm.querySelector(`[name="${prefix}_${name}"]`);
            if (el) el.value = value || '';
        };

        try {
            /* 1. Banco local */
            const checkResp   = await fetch(`<?= BASE_URL ?>/contratos/verificarDocumentoExistente/${cnpj}`);
            const checkResult = await checkResp.json();

            if (checkResult.exists && checkResult.entidade) {
                const ent = checkResult.entidade;
                if (confirm(`Encontrado no banco local (${ent.tipo_entidade}: ${ent.nome_entidade}). Deseja carregar?`)) {
                    setField('nome',     ent.nome_entidade);
                    setField('endereco', ent.endereco);
                    setField('email',    ent.email);
                    setField('telefone', ent.telefone);

                    // Vincula o ID do cliente para validações em tempo real
                    if (ent.tipo_entidade === 'Cliente' && ent.id) {
                        const hiddenId = document.getElementById('cliente_id_hidden');
                        if (hiddenId) { hiddenId.value = ent.id; checkDuplicateContractId(); }
                    }
                    return;
                }
            }

            /* 2. API externa */
            const resp   = await fetch(`<?= BASE_URL ?>/contratos/buscarCnpjAjax/${cnpj}`);
            const result = await resp.json();

            if (result.success) {
                const d = result.data;
                setField('nome', d.razao_social || d.nome_fantasia || '');

                // Endereço
                const addrParts = [];
                let logr = [d.descricao_tipo_de_logradouro, d.logradouro].filter(Boolean).join(' ');
                if (logr) {
                    if (d.numero && d.numero !== 'S/N') logr += `, ${d.numero}`;
                    addrParts.push(logr);
                }
                if (d.complemento) addrParts.push(d.complemento);
                if (d.bairro)      addrParts.push(d.bairro);
                let cityState = d.municipio || '';
                if (d.uf) cityState += cityState ? ` - ${d.uf}` : d.uf;
                if (cityState) addrParts.push(cityState);
                if (d.cep) {
                    const cepFmt = d.cep.replace(/\D/g, '').replace(/^(\d{5})(\d{3})$/, '$1-$2');
                    addrParts.push(`CEP: ${cepFmt}`);
                }
                setField('endereco', addrParts.join(', '));

                // Preenchimento de Telefone (se não estiver preenchido)
                if (d.ddd_telefone_1 && d.telefone) {
                    const telFmt = `(${d.ddd_telefone_1}) ${d.telefone}`;
                    setField('telefone', telFmt);
                } else if (d.telefone) {
                    setField('telefone', d.telefone);
                }

                if (d.email) setField('email', d.email);
                
                // Trata telefone da BrasilAPI (DDD + Número)
                if (d.ddd_telefone_1) {
                    const tel = `(${d.ddd_telefone_1}) ${d.telefone || ''}`;
                    setField('telefone', tel);
                } else if (d.telefone) {
                    setField('telefone', d.telefone);
                }
            } else {
                alert(result.message || 'CNPJ não encontrado na receita federal.');
            }
        } catch (err) {
            alert('Erro ao consultar o serviço. Verifique sua conexão e tente novamente.');
        } finally {
            if (btn) {
                btn.innerHTML = originalHTML;
                btn.disabled  = false;
            }
        }
    };

    /* ── Verificação de ID/CTR-CLIENTE Duplicado em Tempo Real ── */
    const inputIdCliente = document.getElementById('numero_contrato_cliente');
    const hiddenClientId = document.getElementById('cliente_id_hidden');

    window.checkDuplicateContractId = async function() {
        const numero = inputIdCliente.value.trim();
        const clienteId = hiddenClientId.value;
        const contratoId = form.querySelector('[name="id"]')?.value || '';

        if (!numero || !clienteId) return;

        try {
            const resp = await fetch(`<?= BASE_URL ?>/contratos/verificarNumeroClienteAjax?numero=${encodeURIComponent(numero)}&cliente_id=${clienteId}&exclude_id=${contratoId}`);
            const data = await resp.json();
            
            let msg = document.getElementById('error-numero-cliente');
            if (data.exists) {
                inputIdCliente.style.borderColor = 'var(--c-red)';
                inputIdCliente.style.backgroundColor = 'var(--c-red-light)';
                if (!msg) {
                    msg = document.createElement('span');
                    msg.id = 'error-numero-cliente';
                    msg.className = 'ctr-helper';
                    msg.style.color = 'var(--c-red)';
                    msg.style.fontWeight = '600';
                    msg.style.marginTop = '4px';
                    inputIdCliente.parentNode.appendChild(msg);
                }
                msg.textContent = '⚠ Este ID já está registrado para este cliente!';
            } else {
                inputIdCliente.style.borderColor = '';
                inputIdCliente.style.backgroundColor = '';
                if (msg) msg.remove();
            }
        } catch (e) { console.error('Erro na validação de ID externo:', e); }
    };

    if (inputIdCliente) inputIdCliente.addEventListener('blur', checkDuplicateContractId);

}());
</script>
