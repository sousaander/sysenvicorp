<style>
/* ============================================================
   ENVIROCORP — MÓDULO FINANCEIRO
   Formulário de Movimentação — Design Executivo
   ============================================================ */

/* ── Modo claro (padrão) ── */
:root {
    --fin-bg:         #f8fafc;
    --fin-surface:    #ffffff;
    --fin-surface-2:  #f1f5f9;
    --fin-border:     #e2e8f0;
    --fin-border-2:   #cbd5e1;
    --fin-accent:     #3b82f6;
    --fin-accent-dim: rgba(59,130,246,.12);
    --fin-accent-glow:rgba(59,130,246,.25);
    --fin-green:      #10b981;
    --fin-green-dim:  rgba(16,185,129,.12);
    --fin-amber:      #f59e0b;
    --fin-amber-dim:  rgba(245,158,11,.10);
    --fin-red:        #ef4444;
    --fin-red-dim:    rgba(239,68,68,.10);
    --fin-text:       #0f172a;
    --fin-text-muted: #64748b;
    --fin-text-sub:   #475569;
    --fin-radius:     10px;
    --fin-radius-sm:  6px;
    --fin-transition: .18s cubic-bezier(.4,0,.2,1);
}

/* ── Modo escuro ── */
body.dark-theme {
    --fin-bg:         #0f1117;
    --fin-surface:    #181c27;
    --fin-surface-2:  #1f2438;
    --fin-border:     #2a3050;
    --fin-border-2:   #364070;
    --fin-text:       #e2e8f0;
    --fin-text-muted: #7a8ba8;
    --fin-text-sub:   #9aa8c0;
}

/* ── Reset de contexto ── */
.fin-wrap * { box-sizing: border-box; }

/* ── Wrapper principal ── */
.fin-wrap {
    font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;
    color: var(--fin-text);
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 0 40px;
}

/* ── Cabeçalho da tela ── */
.fin-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 28px;
    padding-bottom: 22px;
    border-bottom: 1px solid var(--fin-border);
}
.fin-header-left {}
.fin-header h2 {
    font-size: 1.35rem;
    font-weight: 700;
    letter-spacing: -.3px;
    color: var(--fin-text);
    margin: 0 0 4px;
}
.fin-header p {
    font-size: .8rem;
    color: var(--fin-text-muted);
    margin: 0;
}
.fin-badge-tipo {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: .72rem;
    font-weight: 600;
    letter-spacing: .5px;
    text-transform: uppercase;
    padding: 4px 10px;
    border-radius: 20px;
    margin-top: 10px;
}
.fin-badge-tipo.receita  { background: var(--fin-green-dim); color: var(--fin-green); border: 1px solid rgba(16,185,129,.25); }
.fin-badge-tipo.despesa  { background: var(--fin-red-dim);   color: var(--fin-red);   border: 1px solid rgba(239,68,68,.25); }
.fin-badge-tipo.pendente { background: var(--fin-accent-dim);color: var(--fin-accent);border: 1px solid var(--fin-accent-glow); }

/* ── Grid de dois painéis ── */
.fin-grid {
    display: grid;
    grid-template-columns: 1fr 340px;
    gap: 20px;
    align-items: start;
}
@media (max-width: 900px) {
    .fin-grid { grid-template-columns: 1fr; }
}

/* ── Card genérico ── */
.fin-card {
    background: var(--fin-surface);
    border: 1px solid var(--fin-border);
    border-radius: var(--fin-radius);
    overflow: hidden;
}

/* ── Card header ── */
.fin-card-header {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 14px 20px;
    border-bottom: 1px solid var(--fin-border);
    background: var(--fin-surface-2);
}
.fin-card-header-icon {
    width: 30px;
    height: 30px;
    border-radius: var(--fin-radius-sm);
    background: var(--fin-accent-dim);
    border: 1px solid var(--fin-accent-glow);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    color: var(--fin-accent);
}
.fin-card-header h3 {
    font-size: .82rem;
    font-weight: 700;
    letter-spacing: .4px;
    text-transform: uppercase;
    color: var(--fin-text);
    margin: 0;
}
.fin-card-body {
    padding: 20px;
}

/* ── Grid de campos dentro do card ── */
.fin-fields {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px 20px;
}
.fin-fields.col-1 { grid-template-columns: 1fr; }
.fin-col-2 { grid-column: span 2; }
@media (max-width: 600px) {
    .fin-fields { grid-template-columns: 1fr; }
    .fin-col-2 { grid-column: span 1; }
}

/* ── Campo individual ── */
.fin-field {}
.fin-field label {
    display: block;
    font-size: .72rem;
    font-weight: 600;
    letter-spacing: .4px;
    text-transform: uppercase;
    color: var(--fin-text-muted);
    margin-bottom: 6px;
}
.fin-field label .req { color: var(--fin-red); margin-left: 2px; }

/* ── Inputs, selects ── */
.fin-input,
.fin-select,
.fin-textarea {
    width: 100%;
    background: var(--fin-bg);
    border: 1px solid var(--fin-border);
    border-radius: var(--fin-radius-sm);
    color: var(--fin-text);
    font-size: .85rem;
    padding: 9px 12px;
    outline: none;
    transition: border-color var(--fin-transition), box-shadow var(--fin-transition);
    appearance: none;
    -webkit-appearance: none;
}
.fin-input:focus,
.fin-select:focus,
.fin-textarea:focus {
    border-color: var(--fin-accent);
    box-shadow: 0 0 0 3px var(--fin-accent-dim);
}
.fin-input::placeholder { color: var(--fin-text-muted); }
.fin-input.large {
    font-size: 1rem;
    font-weight: 700;
    padding: 9px 12px;
    letter-spacing: -.3px;
}
.fin-input[type="date"]::-webkit-calendar-picker-indicator {
    cursor: pointer;
}
body.dark-theme .fin-input[type="date"]::-webkit-calendar-picker-indicator {
    filter: invert(.6);
}

/* ── Select com seta customizada ── */
.fin-select-wrap {
    position: relative;
}
.fin-select-wrap::after {
    content: '';
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    width: 0;
    height: 0;
    border-left: 4px solid transparent;
    border-right: 4px solid transparent;
    border-top: 5px solid var(--fin-text-muted);
    pointer-events: none;
}
.fin-select { padding-right: 32px; cursor: pointer; }
.fin-select option { background: #ffffff; color: var(--fin-text); }
body.dark-theme .fin-select option { background: #1e2535; }

/* ── Select com botão + ── */
.fin-input-group {
    display: flex;
    gap: 6px;
    align-items: stretch;
    position: relative;
}
.fin-input-group .fin-input,
.fin-input-group .fin-select-wrap { flex: 1; }
.fin-btn-add {
    flex-shrink: 0;
    width: 36px;
    background: var(--fin-accent-dim);
    border: 1px solid var(--fin-accent-glow);
    border-radius: var(--fin-radius-sm);
    color: var(--fin-accent);
    font-size: 1.1rem;
    font-weight: 700;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background var(--fin-transition);
}
.fin-btn-add:hover { background: rgba(59,130,246,.22); }

/* ── Textarea ── */
.fin-textarea { resize: vertical; min-height: 72px; font-family: inherit; }

/* ── File input ── */
.fin-file-label {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 9px 14px;
    background: var(--fin-bg);
    border: 1px dashed var(--fin-border-2);
    border-radius: var(--fin-radius-sm);
    cursor: pointer;
    transition: border-color var(--fin-transition), background var(--fin-transition);
    font-size: .8rem;
    color: var(--fin-text-muted);
}
.fin-file-label:hover { border-color: var(--fin-accent); color: var(--fin-accent); background: var(--fin-accent-dim); }
.fin-file-label svg { flex-shrink: 0; }
.fin-file-input { display: none; }

/* ── Valor em destaque ── */
.fin-valor-destaque {
    background: linear-gradient(135deg, var(--fin-surface-2), var(--fin-bg));
    border: 1px solid var(--fin-border-2);
    border-radius: var(--fin-radius);
    padding: 16px 18px;
    margin-bottom: 16px;
}
.fin-valor-destaque label {
    font-size: .7rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .5px;
    color: var(--fin-text-muted);
    display: block;
    margin-bottom: 8px;
}
.fin-valor-destaque .fin-valor-row {
    display: flex;
    gap: 12px;
    align-items: flex-end;
}
.fin-valor-destaque .fin-valor-row > * { flex: 1; }
.fin-valor-destaque .fin-input.large { border-color: var(--fin-border-2); }
.fin-valor-destaque .fin-input.large:focus { border-color: var(--fin-accent); }

/* ── Status pill ── */
.fin-status-pills {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 6px;
    margin-bottom: 0;
}
.fin-status-pill {
    position: relative;
}
.fin-status-pill input[type="radio"] {
    position: absolute;
    opacity: 0;
    width: 0;
    height: 0;
}
.fin-status-pill label {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 4px;
    padding: 8px 4px;
    border-radius: var(--fin-radius-sm);
    border: 1px solid var(--fin-border);
    background: var(--fin-bg);
    cursor: pointer;
    text-align: center;
    transition: all var(--fin-transition);
    font-size: .65rem;
    font-weight: 600;
    letter-spacing: .3px;
    text-transform: uppercase;
    color: var(--fin-text-muted);
}
.fin-status-pill label svg { opacity: .5; }
.fin-status-pill input:checked + label {
    border-color: currentColor;
}
.fin-status-pill.s-pendente input:checked + label  { color: var(--fin-amber); background: var(--fin-amber-dim); border-color: rgba(245,158,11,.3); }
.fin-status-pill.s-pendente input:checked + label svg { opacity: 1; }
.fin-status-pill.s-pago input:checked + label     { color: var(--fin-green); background: var(--fin-green-dim); border-color: rgba(16,185,129,.3); }
.fin-status-pill.s-pago input:checked + label svg { opacity: 1; }
.fin-status-pill.s-parcial input:checked + label  { color: #60a5fa; background: rgba(96,165,250,.1); border-color: rgba(96,165,250,.3); }
.fin-status-pill.s-parcial input:checked + label svg { opacity: 1; }
.fin-status-pill.s-atrasado input:checked + label { color: var(--fin-red); background: var(--fin-red-dim); border-color: rgba(239,68,68,.3); }
.fin-status-pill.s-atrasado input:checked + label svg { opacity: 1; }
.fin-status-pill.s-cancelado input:checked + label{ color: var(--fin-text-muted); background: rgba(100,116,139,.12); border-color: rgba(100,116,139,.3); }

/* ── Divider com label ── */
.fin-divider {
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 18px 0 14px;
    color: var(--fin-text-muted);
    font-size: .68rem;
    font-weight: 600;
    letter-spacing: .5px;
    text-transform: uppercase;
}
.fin-divider::before, .fin-divider::after {
    content: '';
    flex: 1;
    height: 1px;
    background: var(--fin-border);
}

/* ── Painel de pagamento colapsável ── */
.fin-pay-panel {
    background: var(--fin-bg);
    border: 1px solid var(--fin-border);
    border-radius: var(--fin-radius);
    overflow: hidden;
    transition: all var(--fin-transition);
}
.fin-pay-panel.hidden-panel { display: none; }
.fin-pay-panel-body { padding: 14px; }
.fin-pay-panel .fin-fields { gap: 12px 14px; }

/* ── Resumo parcial ── */
.fin-parcial-summary {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1px;
    background: var(--fin-border);
    border-radius: var(--fin-radius-sm);
    overflow: hidden;
    margin-bottom: 14px;
}
.fin-parcial-stat {
    background: var(--fin-bg);
    padding: 10px 12px;
    text-align: center;
}
.fin-parcial-stat .value { font-size: .92rem; font-weight: 700; }
.fin-parcial-stat .label { font-size: .62rem; font-weight: 500; text-transform: uppercase; letter-spacing: .4px; color: var(--fin-text-muted); margin-top: 2px; }

/* ── Toggle checkbox ── */
.fin-toggle-wrap {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding: 10px 12px;
    border-radius: var(--fin-radius-sm);
    border: 1px solid var(--fin-border);
    background: var(--fin-bg);
    cursor: pointer;
    transition: border-color var(--fin-transition);
}
.fin-toggle-wrap:hover { border-color: var(--fin-accent); }
.fin-toggle-wrap input[type="checkbox"] {
    width: 15px;
    height: 15px;
    accent-color: var(--fin-accent);
    flex-shrink: 0;
    margin-top: 2px;
    cursor: pointer;
}
.fin-toggle-wrap .fin-toggle-label { font-size: .78rem; color: var(--fin-text-sub); line-height: 1.45; }
.fin-toggle-wrap .fin-toggle-label strong { color: var(--fin-text); font-weight: 600; }

/* ── Alerta inline ── */
.fin-alert {
    display: flex;
    align-items: flex-start;
    gap: 8px;
    padding: 10px 12px;
    border-radius: var(--fin-radius-sm);
    font-size: .75rem;
    line-height: 1.5;
    margin-top: 10px;
}
.fin-alert.amber { background: var(--fin-amber-dim); border: 1px solid rgba(245,158,11,.2); color: #92400e; }
body.dark-theme .fin-alert.amber { color: #fbbf24; }
.fin-alert.blue  { background: var(--fin-accent-dim); border: 1px solid var(--fin-accent-glow); color: #1e40af; }
body.dark-theme .fin-alert.blue { color: #93c5fd; }

/* ── Stepper de parcelas ── */
.fin-stepper {
    display: flex;
    align-items: center;
    width: max-content;
    border: 1px solid var(--fin-border);
    border-radius: var(--fin-radius-sm);
    overflow: hidden;
}
.fin-stepper button {
    width: 34px;
    height: 34px;
    background: var(--fin-surface-2);
    border: none;
    color: var(--fin-text);
    font-size: 1rem;
    cursor: pointer;
    transition: background var(--fin-transition);
    display: flex;
    align-items: center;
    justify-content: center;
}
.fin-stepper button:hover { background: var(--fin-accent-dim); color: var(--fin-accent); }
.fin-stepper input {
    width: 44px;
    text-align: center;
    border: none;
    border-left: 1px solid var(--fin-border);
    border-right: 1px solid var(--fin-border);
    background: var(--fin-bg);
    color: var(--fin-text);
    font-size: .9rem;
    font-weight: 700;
    padding: 6px 0;
    outline: none;
}

/* ── Radio cards de repetição ── */
.fin-radio-card-group { display: flex; flex-direction: column; gap: 8px; }
.fin-radio-card { position: relative; }
.fin-radio-card input { position: absolute; opacity: 0; width: 0; height: 0; }
.fin-radio-card label {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 14px;
    border-radius: var(--fin-radius-sm);
    border: 1px solid var(--fin-border);
    background: var(--fin-bg);
    cursor: pointer;
    transition: all var(--fin-transition);
    font-size: .8rem;
    color: var(--fin-text-sub);
}
.fin-radio-card label .rc-dot {
    width: 14px; height: 14px;
    border-radius: 50%;
    border: 2px solid var(--fin-border-2);
    flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    transition: all var(--fin-transition);
}
.fin-radio-card input:checked + label {
    border-color: var(--fin-accent);
    background: var(--fin-accent-dim);
    color: var(--fin-text);
}
.fin-radio-card input:checked + label .rc-dot {
    border-color: var(--fin-accent);
    background: var(--fin-accent);
    box-shadow: 0 0 0 3px var(--fin-accent-dim);
}

/* ── Tabela de histórico ── */
.fin-table {
    width: 100%;
    border-collapse: collapse;
    font-size: .75rem;
}
.fin-table th {
    padding: 8px 10px;
    text-align: left;
    font-size: .65rem;
    font-weight: 700;
    letter-spacing: .5px;
    text-transform: uppercase;
    color: var(--fin-text-muted);
    background: var(--fin-surface-2);
    border-bottom: 1px solid var(--fin-border);
}
.fin-table td {
    padding: 8px 10px;
    border-bottom: 1px solid var(--fin-border);
    color: var(--fin-text-sub);
    vertical-align: middle;
}
.fin-table tr:last-child td { border-bottom: none; }
.fin-table tr:hover td { background: var(--fin-surface-2); }
.fin-table .td-green { color: var(--fin-green); font-weight: 700; }
.fin-table .td-action {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 24px; height: 24px;
    border-radius: 4px;
    border: none;
    background: transparent;
    cursor: pointer;
    color: var(--fin-text-muted);
    transition: all var(--fin-transition);
}
.fin-table .td-action:hover { background: var(--fin-accent-dim); color: var(--fin-accent); }
.fin-table .td-action.del:hover { background: var(--fin-red-dim); color: var(--fin-red); }

/* ── Botões de ação ── */
.fin-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 24px;
    padding-top: 20px;
    border-top: 1px solid var(--fin-border);
}
.fin-btn {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 10px 22px;
    border-radius: var(--fin-radius-sm);
    font-size: .82rem;
    font-weight: 600;
    letter-spacing: .2px;
    cursor: pointer;
    border: 1px solid transparent;
    text-decoration: none;
    transition: all var(--fin-transition);
}
.fin-btn-cancel {
    background: transparent;
    border-color: var(--fin-border);
    color: var(--fin-text-muted);
}
.fin-btn-cancel:hover { border-color: var(--fin-border-2); color: var(--fin-text); }
.fin-btn-submit {
    background: var(--fin-accent);
    color: #fff;
    box-shadow: 0 2px 12px rgba(59,130,246,.3);
}
.fin-btn-submit:hover { background: #2563eb; box-shadow: 0 4px 18px rgba(59,130,246,.4); }
.fin-btn-submit:disabled { opacity: .55; cursor: not-allowed; }

/* ── Dropdown de busca ── */
.fin-search-results {
    position: absolute;
    z-index: 50;
    width: 100%;
    top: calc(100% + 4px);
    background: var(--fin-surface);
    border: 1px solid var(--fin-border-2);
    border-radius: var(--fin-radius-sm);
    box-shadow: 0 16px 32px rgba(0,0,0,.5);
    max-height: 200px;
    overflow-y: auto;
}
.fin-search-results div {
    padding: 8px 12px;
    font-size: .8rem;
    color: var(--fin-text-sub);
    cursor: pointer;
    transition: background var(--fin-transition);
}
.fin-search-results div:hover { background: var(--fin-accent-dim); color: var(--fin-text); }

/* ── Modal ── */
.fin-modal-overlay {
    position: fixed; inset: 0; z-index: 200;
    background: rgba(0,0,0,.65);
    display: flex; align-items: center; justify-content: center;
    padding: 20px;
}
.fin-modal {
    background: var(--fin-surface);
    border: 1px solid var(--fin-border-2);
    border-radius: var(--fin-radius);
    width: 100%; max-width: 420px;
    box-shadow: 0 24px 64px rgba(0,0,0,.6);
}
.fin-modal-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 16px 20px;
    border-bottom: 1px solid var(--fin-border);
}
.fin-modal-header h5 { font-size: .88rem; font-weight: 700; margin: 0; }
.fin-modal-close { background: transparent; border: none; color: var(--fin-text-muted); cursor: pointer; font-size: 1.1rem; line-height: 1; }
.fin-modal-close:hover { color: var(--fin-text); }
.fin-modal-body { padding: 20px; }
.fin-modal-footer { display: flex; justify-content: flex-end; gap: 8px; padding: 14px 20px; border-top: 1px solid var(--fin-border); }

/* ── Nota de info ── */
.fin-note {
    font-size: .7rem;
    color: var(--fin-text-muted);
    margin-top: 5px;
    line-height: 1.45;
    display: flex;
    align-items: flex-start;
    gap: 5px;
}

/* ── Anexo existente ── */
.fin-attachment-link {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: .72rem;
    color: var(--fin-accent);
    text-decoration: none;
    margin-top: 6px;
    transition: color var(--fin-transition);
}
.fin-attachment-link:hover { color: var(--fin-accent); text-decoration: underline; }
body.dark-theme .fin-attachment-link:hover { color: #93c5fd; }

/* ── Tipo toggle no topo ── */
.fin-tipo-toggle {
    display: flex;
    gap: 8px;
}
.fin-tipo-opt { position: relative; flex: 1; }
.fin-tipo-opt input { position: absolute; opacity: 0; width: 0; height: 0; }
.fin-tipo-opt label {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    padding: 9px 12px;
    border-radius: var(--fin-radius-sm);
    border: 1px solid var(--fin-border);
    background: var(--fin-bg);
    cursor: pointer;
    font-size: .78rem;
    font-weight: 600;
    color: var(--fin-text-muted);
    transition: all var(--fin-transition);
    text-transform: uppercase;
    letter-spacing: .3px;
}
.fin-tipo-opt.receita  input:checked + label { background: var(--fin-green-dim); border-color: rgba(16,185,129,.35); color: var(--fin-green); }
.fin-tipo-opt.despesa  input:checked + label { background: var(--fin-red-dim); border-color: rgba(239,68,68,.35); color: var(--fin-red); }
.fin-tipo-opt.transferencia input:checked + label { background: var(--fin-accent-dim); border-color: var(--fin-accent-glow); color: var(--fin-accent); }

/* Scrollbar customizada */
.fin-wrap ::-webkit-scrollbar { width: 5px; height: 5px; }
.fin-wrap ::-webkit-scrollbar-track { background: var(--fin-bg); }
.fin-wrap ::-webkit-scrollbar-thumb { background: var(--fin-border-2); border-radius: 3px; }
</style>

<?php
$isEdit = isset($transacao) && $transacao !== null;
$actionUrl = BASE_URL . '/financeiro/salvar';

$valorOriginalParaExibir = $isEdit ? ($transacao['valor'] ?? 0) : 0;

$isRecorrencia = false;
if ($isEdit && preg_match('/\((?:Recorrência\s)?\d+\/\d+\)$/', $transacao['descricao'])) {
    $isRecorrencia = true;
}
?>

<div class="fin-wrap">

    <!-- Cabeçalho -->
    <div class="fin-header">
        <div class="fin-header-left">
            <h2><?php echo htmlspecialchars($pageTitle); ?></h2>
            <p>Preencha os dados para registrar ou atualizar a movimentação financeira.</p>
        </div>
    </div>

    <form action="<?php echo $actionUrl; ?>" method="POST" enctype="multipart/form-data">

        <input type="hidden" name="id"                value="<?php echo $isEdit ? htmlspecialchars($transacao['id']) : ''; ?>">
        <input type="hidden" name="csrf_token"        value="<?php echo $csrf_token ?? ''; ?>">
        <input type="hidden" name="contrato_parcela_id" value="<?php echo $isEdit ? htmlspecialchars($transacao['contrato_parcela_id'] ?? '') : ''; ?>">
        <input type="hidden" id="valor_real"          name="valor"       value="<?php echo $isEdit ? number_format($valorOriginalParaExibir, 2, ',', '.') : ''; ?>">
        <input type="hidden" id="valor_pago_hidden"   name="valor_pago"  value="<?php echo $isEdit ? number_format($transacao['valor_pago'] ?? 0, 2, ',', '.') : '0'; ?>">

        <div class="fin-grid">

            <!-- ══════════════════════════════════════════
                 COLUNA ESQUERDA — INFORMAÇÕES PRINCIPAIS
            ══════════════════════════════════════════ -->
            <div style="display:flex;flex-direction:column;gap:16px;">

                <!-- Card: Identificação -->
                <div class="fin-card">
                    <div class="fin-card-header">
                        <div class="fin-card-header-icon">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>
                        </div>
                        <h3>Informações da Movimentação</h3>
                    </div>
                    <div class="fin-card-body">
                        <div class="fin-fields">

                            <!-- Tipo -->
                            <div class="fin-field fin-col-2">
                                <label>Tipo de Movimentação <span class="req">*</span></label>
                                <?php if ($isEdit && isset($transacao['tipo']) && $transacao['tipo'] === 'Transferência'): ?>
                                    <div class="fin-tipo-toggle">
                                        <div class="fin-tipo-opt transferencia">
                                            <input type="radio" id="tipo_tr" name="tipo" value="Transferência" checked>
                                            <label for="tipo_tr">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 3L4 7l4 4M4 7h16M16 21l4-4-4-4m4 4H4"/></svg>
                                                Transferência
                                            </label>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="fin-tipo-toggle">
                                        <div class="fin-tipo-opt receita">
                                            <input type="radio" id="tipo_r" name="tipo" value="R" <?php echo ($isEdit && $transacao['tipo'] === 'R') ? 'checked' : ''; ?>>
                                            <label for="tipo_r">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 5v14M5 12l7-7 7 7"/></svg>
                                                Receita (a Receber)
                                            </label>
                                        </div>
                                        <div class="fin-tipo-opt despesa">
                                            <input type="radio" id="tipo_p" name="tipo" value="P" <?php echo ($isEdit && $transacao['tipo'] === 'P') ? 'checked' : ''; ?>>
                                            <label for="tipo_p">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 19V5M5 12l7 7 7-7"/></svg>
                                                Despesa (a Pagar)
                                            </label>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Descrição -->
                            <div class="fin-field fin-col-2">
                                <label>Descrição <span class="req">*</span></label>
                                <input type="text" id="descricao" name="descricao" required
                                    value="<?php echo $isEdit ? htmlspecialchars($transacao['descricao']) : ''; ?>"
                                    class="fin-input" placeholder="Descreva a movimentação…">
                            </div>

                            <!-- Categoria -->
                            <div class="fin-field" id="categoria-container">
                                <label>Categoria</label>
                                <div class="fin-input-group">
                                    <div style="position:relative;flex:1;">
                                        <input type="text" id="search_classificacao" placeholder="Buscar categoria…" class="fin-input">
                                        <div id="results_classificacao" class="fin-search-results" style="display:none;"></div>
                                    </div>
                                    <select id="classificacao_id" name="classificacao_id" style="display:none;">
                                        <option value="">Nenhuma</option>
                                        <?php foreach ($classificacoes as $class): ?>
                                            <option value="<?php echo $class['id']; ?>" data-tipo="<?php echo $class['tipo']; ?>"
                                                <?php echo ($isEdit && isset($transacao['classificacao_id']) && $transacao['classificacao_id'] == $class['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($class['nome']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="button" id="addClassificacaoBtn" class="fin-btn-add" title="Nova Categoria">+</button>
                                </div>
                            </div>

                            <!-- Centro de Custo -->
                            <div class="fin-field <?php echo ($isEdit && $transacao['tipo'] !== 'P') ? 'hidden' : ''; ?>" id="centro-custo-container">
                                <label>Centro de Custo</label>
                                <div class="fin-input-group">
                                    <div style="position:relative;flex:1;">
                                        <input type="text" id="search_centro_custo" placeholder="Buscar centro de custo…" class="fin-input">
                                        <div id="results_centro_custo" class="fin-search-results" style="display:none;"></div>
                                    </div>
                                    <select id="centro_custo_id" name="centro_custo_id" style="display:none;">
                                        <option value="">Nenhum</option>
                                        <?php if (!empty($centrosCusto)): ?>
                                            <?php foreach ($centrosCusto as $cc):
                                                $selected = '';
                                                if ($isEdit && isset($transacao['centro_custo_id']) && $transacao['centro_custo_id'] == $cc['id']) { $selected = 'selected'; }
                                                elseif (!$isEdit && count($centrosCusto) == 1) { $selected = 'selected'; }
                                            ?>
                                                <option value="<?php echo $cc['id']; ?>" <?php echo $selected; ?>><?php echo htmlspecialchars($cc['nome']); ?></option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                    <button type="button" id="addCentroCustoBtn" class="fin-btn-add" title="Novo Centro de Custo">+</button>
                                </div>
                                <?php if ($isRecorrencia): ?>
                                    <div class="fin-alert amber" style="margin-top:8px;">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" flex-shrink="0"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                                        <label class="fin-toggle-wrap" style="background:transparent;border:none;padding:0;cursor:pointer;">
                                            <input type="checkbox" id="atualizar_futuras" name="atualizar_futuras" value="1">
                                            <span class="fin-toggle-label">Atualizar Centro de Custo nas <strong>parcelas futuras</strong> desta série</span>
                                        </label>
                                    </div>
                                    <div class="fin-alert blue" style="margin-top:6px;">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                                        <label class="fin-toggle-wrap" style="background:transparent;border:none;padding:0;cursor:pointer;">
                                            <input type="checkbox" id="atualizar_valor_futuras" name="atualizar_valor_futuras" value="1">
                                            <span class="fin-toggle-label">Atualizar o <strong>Valor</strong> nas <strong>parcelas futuras</strong> desta série</span>
                                        </label>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Cliente -->
                            <div class="fin-field <?php echo ($isEdit && $transacao['tipo'] === 'R') ? '' : 'hidden'; ?>" id="cliente-container">
                                <label>Cliente</label>
                                <div class="fin-input-group">
                                    <div class="fin-select-wrap" style="flex:1;">
                                        <select id="cliente_id" name="cliente_id" class="fin-select">
                                            <option value="">Selecione um cliente</option>
                                            <?php if (!empty($clientes)): foreach ($clientes as $c): ?>
                                                <option value="<?php echo $c['id']; ?>" <?php echo ($isEdit && isset($transacao['cliente_id']) && $transacao['cliente_id'] == $c['id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($c['nome']); ?>
                                                </option>
                                            <?php endforeach; endif; ?>
                                        </select>
                                    </div>
                                    <button type="button" id="btnAddCliente" class="fin-btn-add" title="Novo Cliente">+</button>
                                </div>
                            </div>

                            <!-- Fornecedor -->
                            <div class="fin-field <?php echo ($isEdit && $transacao['tipo'] === 'P') ? '' : 'hidden'; ?>" id="fornecedor-container">
                                <label>Fornecedor</label>
                                <div class="fin-input-group">
                                    <div class="fin-select-wrap" style="flex:1;">
                                        <select id="fornecedor_id" name="fornecedor_id" class="fin-select">
                                            <option value="">Selecione um fornecedor</option>
                                            <?php if (!empty($fornecedores)): foreach ($fornecedores as $f): ?>
                                                <option value="<?php echo $f['id']; ?>" <?php echo ($isEdit && isset($transacao['fornecedor_id']) && $transacao['fornecedor_id'] == $f['id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($f['nome']); ?>
                                                </option>
                                            <?php endforeach; endif; ?>
                                        </select>
                                    </div>
                                    <button type="button" id="btnAddFornecedor" class="fin-btn-add" title="Novo Fornecedor">+</button>
                                </div>
                            </div>

                            <!-- Vencimento -->
                            <div class="fin-field">
                                <label>Data de Vencimento <span class="req">*</span></label>
                                <input type="date" id="vencimento" name="vencimento" required
                                    value="<?php echo $isEdit ? htmlspecialchars($transacao['vencimento']) : date('Y-m-d'); ?>"
                                    class="fin-input">
                            </div>

                            <!-- Emissão -->
                            <div class="fin-field">
                                <label>Data de Emissão</label>
                                <input type="date" id="dataEmissao" name="dataEmissao"
                                    value="<?php echo $isEdit ? htmlspecialchars($transacao['dataEmissao']) : date('Y-m-d'); ?>"
                                    class="fin-input">
                            </div>

                            <!-- Banco / Caixa -->
                            <div class="fin-field">
                                <label>Banco / Caixa</label>
                                <div class="fin-select-wrap">
                                    <select id="banco_id" name="banco_id" class="fin-select">
                                        <option value="">Selecione</option>
                                        <?php foreach ($bancos as $banco): ?>
                                            <?php $label = $banco['nome'] . (!empty($banco['nome_titular']) ? ' — ' . $banco['nome_titular'] : ''); ?>
                                            <option value="<?php echo $banco['id']; ?>" <?php echo ($isEdit && isset($transacao['banco_id']) && $transacao['banco_id'] == $banco['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($label); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <!-- Documento Vinculado -->
                            <div class="fin-field">
                                <label>Nº do Documento</label>
                                <input type="text" id="documentoVinculado" name="documentoVinculado"
                                    value="<?php echo $isEdit ? htmlspecialchars($transacao['documentoVinculado'] ?? '') : ''; ?>"
                                    class="fin-input" placeholder="NF, boleto, contrato…">
                            </div>

                            <!-- Anexo -->
                            <div class="fin-field fin-col-2">
                                <label>Comprovante / Anexo</label>
                                <label class="fin-file-label" for="anexo">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21.44 11.05l-9.19 9.19a6 6 0 01-8.49-8.49l9.19-9.19a4 4 0 015.66 5.66l-9.2 9.19a2 2 0 01-2.83-2.83l8.49-8.48"/></svg>
                                    <span>Clique para selecionar arquivo…</span>
                                </label>
                                <input type="file" id="anexo" name="anexo" class="fin-file-input">
                                <?php if ($isEdit && !empty($transacao['documentoVinculado'])): ?>
                                    <a href="<?php echo BASE_URL . '/storage/financeiro_anexos/' . htmlspecialchars($transacao['documentoVinculado']); ?>" target="_blank" class="fin-attachment-link">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21.44 11.05l-9.19 9.19a6 6 0 01-8.49-8.49l9.19-9.19a4 4 0 015.66 5.66l-9.2 9.19a2 2 0 01-2.83-2.83l8.49-8.48"/></svg>
                                        Visualizar anexo atual
                                    </a>
                                <?php endif; ?>
                            </div>

                            <!-- Observações -->
                            <div class="fin-field fin-col-2">
                                <label>Observações</label>
                                <textarea id="observacoes" name="observacoes" class="fin-textarea"
                                    placeholder="Informações adicionais sobre esta movimentação…"><?php echo $isEdit ? htmlspecialchars($transacao['observacoes'] ?? '') : ''; ?></textarea>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- Card: Repetição e Parcelamento -->
                <?php if (!$isEdit): ?>
                <div class="fin-card">
                    <div class="fin-card-header">
                        <div class="fin-card-header-icon" style="background:rgba(245,158,11,.12);border-color:rgba(245,158,11,.25);color:var(--fin-amber);">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M17 1l4 4-4 4"/><path d="M3 11V9a4 4 0 014-4h14M7 23l-4-4 4-4"/><path d="M21 13v2a4 4 0 01-4 4H3"/></svg>
                        </div>
                        <h3>Repetição e Parcelamento</h3>
                    </div>
                    <div class="fin-card-body">
                        <div class="fin-fields">
                            <div class="fin-field fin-col-2">
                                <label class="fin-toggle-wrap" style="cursor:pointer;display:flex;align-items:center;gap:10px;">
                                    <input type="checkbox" id="repetir" name="repetir" style="width:16px;height:16px;accent-color:var(--fin-accent);cursor:pointer;">
                                    <div>
                                        <div style="font-size:.82rem;font-weight:600;color:var(--fin-text);text-transform:none;letter-spacing:0;">Repetir este lançamento</div>
                                        <div style="font-size:.72rem;color:var(--fin-text-muted);margin-top:2px;font-weight:400;">Ative para configurar parcelamento ou recorrência</div>
                                    </div>
                                </label>
                            </div>

                            <div id="container_tipo_repeticao" class="fin-col-2 hidden" style="display:none;">
                                <div class="fin-fields" style="grid-template-columns:1fr 1fr;gap:12px;">
                                    <div class="fin-field fin-col-2">
                                        <label>Modo de Repetição</label>
                                        <div class="fin-radio-card-group">
                                            <div class="fin-radio-card">
                                                <input type="radio" id="rp_parcela" name="tipo_repeticao" value="parcelamento" checked>
                                                <label for="rp_parcela">
                                                    <span class="rc-dot"></span>
                                                    <div>
                                                        <div style="font-weight:600;color:var(--fin-text);font-size:.8rem;">Parcelamento</div>
                                                        <div style="font-size:.7rem;margin-top:1px;">Divide o valor total em parcelas</div>
                                                    </div>
                                                </label>
                                            </div>
                                            <div class="fin-radio-card">
                                                <input type="radio" id="rp_recorr" name="tipo_repeticao" value="recorrencia">
                                                <label for="rp_recorr">
                                                    <span class="rc-dot"></span>
                                                    <div>
                                                        <div style="font-weight:600;color:var(--fin-text);font-size:.8rem;">Recorrência</div>
                                                        <div style="font-size:.7rem;margin-top:1px;">Repete o valor integral mensalmente</div>
                                                    </div>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="fin-field">
                                        <label id="label_parcelas">Número de Parcelas</label>
                                        <div class="fin-stepper">
                                            <button type="button" id="btn-minus-parcelas">−</button>
                                            <input type="number" id="parcelas" name="parcelas" value="1" min="1" max="120" readonly>
                                            <button type="button" id="btn-plus-parcelas">+</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

            </div>

            <!-- ══════════════════════════════════════════
                 COLUNA DIREITA — VALORES E PAGAMENTO
            ══════════════════════════════════════════ -->
            <div style="display:flex;flex-direction:column;gap:16px;">

                <!-- Card: Valor e Status -->
                <div class="fin-card">
                    <div class="fin-card-header">
                        <div class="fin-card-header-icon" style="background:rgba(16,185,129,.12);border-color:rgba(16,185,129,.25);color:var(--fin-green);">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
                        </div>
                        <h3>Valores e Status</h3>
                    </div>
                    <div class="fin-card-body">

                        <!-- Valor bruto em destaque -->
                        <div class="fin-valor-destaque">
                            <div style="display:flex;align-items:flex-end;gap:12px;">
                                <div style="flex:1;">
                                    <label>Valor Bruto (R$) <span class="req">*</span></label>
                                    <input type="text" id="valor_formatado" name="valor_formatado_display" required
                                        value="<?php echo $isEdit ? number_format($valorOriginalParaExibir, 2, ',', '.') : ''; ?>"
                                        class="fin-input large" placeholder="0,00" inputmode="decimal">
                                </div>
                                <div id="div_iss" style="width:100px;display:none;">
                                    <label>ISS (%)</label>
                                    <input type="text" id="iss_percentual" name="iss_percentual"
                                        value="<?php echo $isEdit ? number_format($transacao['iss_percentual'] ?? 0, 2, ',', '.') : ''; ?>"
                                        class="fin-input money-mask" placeholder="0,00">
                                </div>
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="fin-field" style="margin-bottom:16px;">
                            <label>Status <span class="req">*</span></label>
                            <div class="fin-status-pills" style="grid-template-columns:repeat(3,1fr);">
                                <?php
                                $statusAtual = $isEdit ? $transacao['status'] : 'Pendente';
                                $statuses = [
                                    'Pendente'     => ['class'=>'s-pendente',  'icon'=>'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', 'label'=>'Pendente'],
                                    'Pago'         => ['class'=>'s-pago',     'icon'=>'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', 'label'=>'Pago'],
                                    'Pago Parcial' => ['class'=>'s-parcial',  'icon'=>'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'label'=>'Parcial'],
                                    'Atrasado'     => ['class'=>'s-atrasado', 'icon'=>'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z', 'label'=>'Atrasado'],
                                    'Cancelado'    => ['class'=>'s-cancelado','icon'=>'M6 18L18 6M6 6l12 12', 'label'=>'Cancelado'],
                                ];
                                foreach ($statuses as $val => $cfg): ?>
                                <div class="fin-status-pill <?php echo $cfg['class']; ?>">
                                    <input type="radio" name="status" id="st_<?php echo $cfg['class']; ?>"
                                        value="<?php echo $val; ?>" <?php echo ($statusAtual === $val) ? 'checked' : ''; ?>>
                                    <label for="st_<?php echo $cfg['class']; ?>">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="<?php echo $cfg['icon']; ?>"/>
                                        </svg>
                                        <?php echo $cfg['label']; ?>
                                    </label>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <!-- select oculto para compatibilidade com o JS original -->
                            <select id="status" name="status" style="display:none;">
                                <option value="Pendente"    <?php echo ($statusAtual==='Pendente') ? 'selected':''; ?>>Pendente</option>
                                <option value="Pago"        <?php echo ($statusAtual==='Pago') ? 'selected':''; ?>>Pago/Recebido</option>
                                <option value="Pago Parcial"<?php echo ($statusAtual==='Pago Parcial') ? 'selected':''; ?>>Pagamento Parcial</option>
                                <option value="Atrasado"    <?php echo ($statusAtual==='Atrasado') ? 'selected':''; ?>>Atrasado</option>
                                <option value="Cancelado"   <?php echo ($statusAtual==='Cancelado') ? 'selected':''; ?>>Cancelado</option>
                            </select>
                        </div>

                        <!-- Painel de Pagamento -->
                        <div id="container_pagamento" class="fin-pay-panel <?php echo (!$isEdit || ($isEdit && $transacao['status'] !== 'Pago' && $transacao['status'] !== 'Pago Parcial')) ? 'hidden-panel' : ''; ?>">
                            <div class="fin-pay-panel-body">
                                <div class="fin-divider">Detalhes do Pagamento</div>
                                <div class="fin-fields" style="grid-template-columns:1fr 1fr;gap:12px;">

                                    <div class="fin-field fin-col-2">
                                        <label>Data do Pagamento</label>
                                        <input type="date" id="data_pagamento" name="data_pagamento"
                                            value="<?php echo $isEdit ? htmlspecialchars($transacao['data_pagamento'] ?? '') : ''; ?>"
                                            class="fin-input">
                                    </div>

                                    <div class="fin-field">
                                        <label>Juros (R$)</label>
                                        <input type="text" id="juros" name="juros"
                                            value="<?php echo $isEdit ? number_format($transacao['juros'] ?? 0, 2, ',', '.') : ''; ?>"
                                            class="fin-input money-mask" placeholder="0,00">
                                    </div>

                                    <div class="fin-field">
                                        <label>Desconto (R$)</label>
                                        <input type="text" id="desconto" name="desconto"
                                            value="<?php echo $isEdit ? number_format($transacao['desconto'] ?? 0, 2, ',', '.') : ''; ?>"
                                            class="fin-input money-mask" placeholder="0,00">
                                    </div>

                                    <!-- Valor Total Pago -->
                                    <div id="div_valor_pago_total" class="fin-col-2 <?php echo ($isEdit && $transacao['status'] === 'Pago Parcial') ? 'hidden' : ''; ?>">
                                        <label>Valor Total Pago (R$)</label>
                                        <input type="text" id="valor_pago_formatado" name="valor_pago_formatado_display"
                                            value="<?php echo ($isEdit && $transacao['status'] === 'Pago') ? number_format($transacao['valor_pago'] ?? 0, 2, ',', '.') : ''; ?>"
                                            class="fin-input large money-mask" placeholder="0,00">
                                    </div>

                                    <!-- Valor Parcial -->
                                    <div id="div_valor_recebido" class="fin-col-2 <?php echo ($isEdit && $transacao['status'] === 'Pago Parcial') ? '' : 'hidden'; ?>">
                                        <label id="label_valor_parcial"><?php echo ($isEdit ? ($transacao['tipo'] ?? 'R') : ($tipoPreSelecionado ?? 'R')) === 'P' ? 'Valor a Pagar Agora (R$)' : 'Valor a Receber Agora (R$)'; ?> <span class="req">*</span></label>
                                        <input type="text" id="valor_recebido_formatado" name="valor_recebido_formatado"
                                            class="fin-input money-mask" placeholder="0,00">
                                        <?php if ($isEdit && $transacao['status'] === 'Pago Parcial'): ?>
                                        <p class="fin-note">
                                            Já pago: <strong style="color:var(--fin-green);">R$ <?php echo number_format($transacao['valor_pago'] ?? 0, 2, ',', '.'); ?></strong>
                                            &nbsp;|&nbsp; Restante: <strong style="color:var(--fin-accent);">R$ <?php echo number_format(($transacao['valor'] + ($transacao['juros'] ?? 0) - ($transacao['desconto'] ?? 0)) - ($transacao['valor_pago'] ?? 0), 2, ',', '.'); ?></strong>
                                        </p>
                                        <?php endif; ?>
                                    </div>

                                    <div class="fin-field fin-col-2">
                                        <label>Forma de Pagamento</label>
                                        <div class="fin-select-wrap">
                                            <select id="forma_pagamento" name="forma_pagamento" class="fin-select">
                                                <option value="">Selecione</option>
                                                <?php
                                                $formas = ['Pix','Dinheiro','Transferência','Boleto','Cartão de Crédito','Cartão de Débito','Cheque','Watsapp','Pagamento Digital','Depósito'];
                                                foreach ($formas as $forma) {
                                                    $sel = ($isEdit && isset($transacao['forma_pagamento']) && $transacao['forma_pagamento'] == $forma) ? 'selected' : '';
                                                    echo "<option value=\"$forma\" $sel>$forma</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Resumo Parcial -->
                        <?php if ($isEdit && $transacao['status'] === 'Pago Parcial'): ?>
                        <div id="container_parcial" style="margin-top:14px;">
                            <div class="fin-parcial-summary">
                                <div class="fin-parcial-stat">
                                    <div class="value" style="color:var(--fin-text);">R$ <?php echo number_format($transacao['valor'], 2, ',', '.'); ?></div>
                                    <div class="label">Original</div>
                                </div>
                                <div class="fin-parcial-stat">
                                    <div class="value" style="color:var(--fin-green);">R$ <?php echo number_format($transacao['valor_pago'] ?? 0, 2, ',', '.'); ?></div>
                                    <div class="label">Já Pago</div>
                                </div>
                                <div class="fin-parcial-stat">
                                    <div class="value" style="color:var(--fin-accent);">R$ <?php echo number_format(($transacao['valor'] + ($transacao['juros'] ?? 0) - ($transacao['desconto'] ?? 0)) - ($transacao['valor_pago'] ?? 0), 2, ',', '.'); ?></div>
                                    <div class="label">Saldo</div>
                                </div>
                            </div>

                            <?php if (!empty($pagamentosParciais)): ?>
                            <div style="font-size:.65rem;font-weight:700;letter-spacing:.5px;text-transform:uppercase;color:var(--fin-text-muted);margin-bottom:6px;">Histórico de Recebimentos</div>
                            <table class="fin-table" id="tabela-pagamentos-parciais">
                                <thead>
                                    <tr>
                                        <th>Data</th>
                                        <th style="text-align:right">Valor</th>
                                        <th>Forma</th>
                                        <th style="text-align:center;width:56px">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pagamentosParciais as $pp): ?>
                                    <tr data-id="<?php echo $pp['id']; ?>">
                                        <td class="pp-data"><?php echo date('d/m/Y', strtotime($pp['data_pagamento'])); ?></td>
                                        <td class="td-green pp-valor" style="text-align:right;">R$ <?php echo number_format($pp['valor'], 2, ',', '.'); ?></td>
                                        <td class="pp-forma" style="color:var(--fin-text-muted);"><?php echo htmlspecialchars($pp['forma_pagamento'] ?? '—'); ?></td>
                                        <td style="text-align:center;">
                                            <button type="button" class="td-action edit-pp" title="Editar">
                                                <svg width="12" height="12" viewBox="0 0 20 20" fill="currentColor"><path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z"/><path fill-rule="evenodd" d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" clip-rule="evenodd"/></svg>
                                            </button>
                                            <button type="button" class="td-action del delete-pp" title="Excluir">
                                                <svg width="12" height="12" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>

                    </div>
                </div>

            </div>
        </div>

        <!-- ── Botões de Ação ── -->
        <div class="fin-actions">
            <a href="<?php echo BASE_URL; ?>/financeiro" class="fin-btn fin-btn-cancel">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                Voltar ao Painel
            </a>
            <button type="submit" class="fin-btn fin-btn-submit">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                <?php echo $isEdit ? 'Salvar Alterações' : 'Incluir Movimentação'; ?>
            </button>
        </div>

    </form>
</div>

<!-- ══════════════════════════════════════════
     MODAIS
══════════════════════════════════════════ -->

<!-- Modal: Cadastro Rápido Cliente/Fornecedor -->
<div id="quickRegisterModal" class="fin-modal-overlay" style="display:none;" role="dialog" aria-modal="true">
    <div class="fin-modal">
        <form id="quickRegisterForm">
            <div class="fin-modal-header">
                <h5 id="quickRegisterTitle">Novo Cadastro</h5>
                <button type="button" id="btnCancelQuickRegister" class="fin-modal-close">✕</button>
            </div>
            <div class="fin-modal-body">
                    <input type="hidden" id="qr_tipo" value="">
                    <input type="hidden" id="qr_return_id" value="">
                    <div class="fin-fields fin-col-1" style="gap:14px;">
                        <div class="fin-field">
                            <label>Nome / Razão Social <span class="req">*</span></label>
                            <input type="text" id="qr_nome" required class="fin-input" placeholder="Nome completo ou razão social">
                        </div>
                        <div class="fin-field">
                            <label>E-mail</label>
                            <input type="email" id="qr_email" class="fin-input" placeholder="email@exemplo.com">
                        </div>
                        <div class="fin-field">
                            <label>Telefone</label>
                            <input type="text" id="qr_telefone" class="fin-input" placeholder="(00) 00000-0000">
                        </div>
                    </div>
            </div>
            <div class="fin-modal-footer">
                <button type="button" id="btnCancelQuickRegister2" class="fin-btn fin-btn-cancel">Cancelar</button>
                <button type="submit" class="fin-btn fin-btn-submit">Salvar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Editar Pagamento Parcial -->
<div id="modal-editar-pp" class="fin-modal-overlay" style="display:none;">
    <div class="fin-modal">
        <div class="fin-modal-header">
            <h5>Editar Pagamento</h5>
            <button type="button" onclick="document.getElementById('modal-editar-pp').style.display='none'" class="fin-modal-close">✕</button>
        </div>
        <div class="fin-modal-body">
            <input type="hidden" id="edit-pp-id">
            <div class="fin-fields fin-col-1" style="gap:12px;">
                <div class="fin-field">
                    <label>Data</label>
                    <input type="date" id="edit-pp-data" class="fin-input">
                </div>
                <div class="fin-field">
                    <label>Valor (R$)</label>
                    <input type="text" id="edit-pp-valor" class="fin-input money-mask" placeholder="0,00">
                </div>
                <div class="fin-field">
                    <label>Forma de Pagamento</label>
                    <div class="fin-select-wrap">
                        <select id="edit-pp-forma" class="fin-select">
                            <option value="">Selecione</option>
                            <option>Pix</option><option>Dinheiro</option><option>Transferência</option>
                            <option>Boleto</option><option>Cartão de Crédito</option><option>Cartão de Débito</option>
                            <option>Cheque</option><option>Depósito</option>
                        </select>
                    </div>
                </div>
            </div>
            <div id="edit-pp-feedback" style="display:none;font-size:.72rem;color:var(--fin-red);margin-top:8px;"></div>
        </div>
        <div class="fin-modal-footer">
            <button type="button" onclick="document.getElementById('modal-editar-pp').style.display='none'" class="fin-btn fin-btn-cancel">Cancelar</button>
            <button type="button" id="btn-salvar-edit-pp" class="fin-btn fin-btn-submit">Salvar</button>
        </div>
    </div>
</div>

<script>
/* ══════════════════════════════════════════
   LÓGICA DO FORMULÁRIO — MÓDULO FINANCEIRO
   Mantida 100% compatível com o original
══════════════════════════════════════════ */

let lastClienteId   = "<?php echo $isEdit ? ($transacao['cliente_id'] ?? '') : (isset($_GET['cliente_id']) ? $_GET['cliente_id'] : ''); ?>";
let lastFornecedorId= "<?php echo $isEdit ? ($transacao['fornecedor_id'] ?? '') : ''; ?>";

function syncTipoChange(tipo) {
    const centroCustoContainer = document.getElementById('centro-custo-container');
    const clienteContainer     = document.getElementById('cliente-container');
    const fornecedorContainer  = document.getElementById('fornecedor-container');
    const divIss               = document.getElementById('div_iss');

        if (tipo === 'R') {
            centroCustoContainer?.classList.add('hidden');
            clienteContainer?.classList.remove('hidden');
            fornecedorContainer?.classList.add('hidden');
            if (divIss) divIss.style.display = 'flex';
        } else if (tipo === 'P') {
            centroCustoContainer?.classList.remove('hidden');
            clienteContainer?.classList.add('hidden');
            fornecedorContainer?.classList.remove('hidden');
            if (divIss) divIss.style.display = 'flex';
        } else {
            centroCustoContainer?.classList.add('hidden');
            clienteContainer?.classList.add('hidden');
            fornecedorContainer?.classList.add('hidden');
        }
    filterClassificacoes(tipo);
    const labelValorParcial = document.getElementById('label_valor_parcial');
    if (labelValorParcial) {
        labelValorParcial.innerHTML = (tipo === 'P' ? 'Valor a Pagar Agora (R$)' : 'Valor a Receber Agora (R$)') + ' <span class="req">*</span>';
    }
}

function filterClassificacoes(tipo) {
    const sel = window._classificacaoSelect;
    const all = window._allClassificacoes;
    if (!sel || !all) return;
    const filtered = tipo === 'R' ? all.filter(o => !o.value || o.dataset.tipo !== 'P')
                   : tipo === 'P' ? all.filter(o => !o.value || o.dataset.tipo !== 'R')
                   : all;
    sel.innerHTML = '';
    filtered.forEach(o => sel.appendChild(o));
}

function preSelecionarCampos() {
    const urlParams = new URLSearchParams(window.location.search);
    const tipoFromUrl = urlParams.get('tipo');
    const tipoRadios = document.querySelectorAll('input[name="tipo"]');
    if (tipoFromUrl && tipoRadios.length) {
        tipoRadios.forEach(r => { if (r.value === tipoFromUrl) r.checked = true; });
        syncTipoChange(tipoFromUrl);
    }
    const clienteFromUrl = urlParams.get('cliente_id');
    const clienteSelect = document.getElementById('cliente_id');
    if (clienteFromUrl && clienteSelect) {
        clienteSelect.value = clienteFromUrl;
        lastClienteId = clienteFromUrl;
    }
}

document.addEventListener('DOMContentLoaded', function() {

    // ── Sincronização dos radio pills de status com o select oculto ──
    const statusSelect = document.getElementById('status');
    document.querySelectorAll('input[name="status"]').forEach(radio => {
        radio.addEventListener('change', function() {
            statusSelect.value = this.value;
            statusSelect.dispatchEvent(new Event('change'));
        });
    });

    // ── Sincronização dos radio de tipo com lógica de visibilidade ──
    document.querySelectorAll('input[name="tipo"]').forEach(radio => {
        radio.addEventListener('change', function() {
            syncTipoChange(this.value);
        });
    });

    // ── Máscara de moeda ──
    const formatCurrency = (value) => {
        let digits = value.replace(/\D/g, '');
        if (digits === '') return '';
        return (Number(digits) / 100).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    };
    const parseCurrency = (value) => {
        if (!value) return 0;
        return parseFloat(value.replace(/\./g, '').replace(',', '.')) || 0;
    };
    const applyMoneyMask = (input) => {
        input.addEventListener('input', e => { e.target.value = formatCurrency(e.target.value); });
    };

    ['valor_formatado','valor_pago_formatado','valor_recebido_formatado','juros','desconto','iss_percentual','edit-pp-valor'].forEach(id => {
        const el = document.getElementById(id);
        if (el) applyMoneyMask(el);
    });

    // ── Painel de Pagamento ──
    const containerPagamento       = document.getElementById('container_pagamento');
    const dataPagamentoInput       = document.getElementById('data_pagamento');
    const valorFormatadoField      = document.getElementById('valor_formatado');
    const valorPagoFormatadoField  = document.getElementById('valor_pago_formatado');
    const valorRecebidoField       = document.getElementById('valor_recebido_formatado');
    const valorPagoHidden          = document.getElementById('valor_pago_hidden');
    const valorRealField           = document.getElementById('valor_real');
    const jurosInput               = document.getElementById('juros');
    const descontoInput            = document.getElementById('desconto');
    let existingValorPago = <?php echo json_encode($isEdit ? (float)($transacao['valor_pago'] ?? 0) : 0); ?>;

    function updateValorReal() {
        const status = statusSelect.value;
        if (status === 'Pago') {
            valorRealField.value     = valorFormatadoField.value;
            valorPagoHidden.value    = valorPagoFormatadoField.value;
        } else if (status === 'Pago Parcial') {
            valorRealField.value     = valorFormatadoField.value;
            const recebido = valorRecebidoField?.value?.trim();
            if (recebido) {
                const total = existingValorPago + parseCurrency(recebido);
                valorPagoHidden.value = total.toLocaleString('pt-BR', { minimumFractionDigits: 2 });
            }
        } else {
            valorRealField.value  = valorFormatadoField.value;
            valorPagoHidden.value = '0';
        }
    }

    function calculateTotalPago() {
        const bruto    = parseCurrency(valorFormatadoField.value);
        const juros    = parseCurrency(jurosInput?.value || '0');
        const desconto = parseCurrency(descontoInput?.value || '0');
        const issPerc  = parseCurrency(document.getElementById('iss_percentual')?.value || '0');
        const liquido  = bruto * (1 - issPerc / 100) + juros - desconto;
        if (valorPagoFormatadoField && !valorPagoFormatadoField.dataset.touched) {
            valorPagoFormatadoField.value = liquido.toLocaleString('pt-BR', { minimumFractionDigits: 2 });
        }
        updateValorReal();
    }

    function togglePaymentCard() {
        const status   = statusSelect.value;
        const divTotal = document.getElementById('div_valor_pago_total');
        const divParc  = document.getElementById('div_valor_recebido');
        if (status === 'Pago' || status === 'Pago Parcial') {
            containerPagamento.classList.remove('hidden-panel');
            containerPagamento.style.display = '';
            if (!dataPagamentoInput.value) dataPagamentoInput.value = new Date().toISOString().split('T')[0];
            if (status === 'Pago') {
                divTotal?.classList.remove('hidden');
                divParc?.classList.add('hidden');
                calculateTotalPago();
            } else {
                divTotal?.classList.add('hidden');
                divParc?.classList.remove('hidden');
            }
        } else {
            containerPagamento.style.display = 'none';
        }
        updateValorReal();
    }

    statusSelect.addEventListener('change', togglePaymentCard);
    valorFormatadoField?.addEventListener('input', () => { if (statusSelect.value === 'Pago') calculateTotalPago(); else updateValorReal(); });
    jurosInput?.addEventListener('input', () => { if (statusSelect.value === 'Pago') calculateTotalPago(); });
    descontoInput?.addEventListener('input', () => { if (statusSelect.value === 'Pago') calculateTotalPago(); });
    valorPagoFormatadoField?.addEventListener('input', () => { valorPagoFormatadoField.dataset.touched = '1'; updateValorReal(); });
    valorRecebidoField?.addEventListener('input', updateValorReal);

    togglePaymentCard();

    // ── Filtro de Classificações ──
    window._classificacaoSelect = document.getElementById('classificacao_id');
    window._allClassificacoes = window._classificacaoSelect ? Array.from(window._classificacaoSelect.options) : [];

    // ── Busca tipo-ahead ──
    function setupSearchInput(inputId, selectId, resultsId) {
        const input   = document.getElementById(inputId);
        const select  = document.getElementById(selectId);
        const results = document.getElementById(resultsId);
        if (!input || !select || !results) return;

        // Exibe texto do selecionado
        const selOpt = select.options[select.selectedIndex];
        if (selOpt && selOpt.value) input.value = selOpt.text;

        input.addEventListener('input', function() {
            const q = this.value.toLowerCase();
            if (q.length < 3) { results.style.display = 'none'; return; }
            const opts = Array.from(select.options).filter(o => o.value && o.text.toLowerCase().includes(q));
            if (!opts.length) { results.style.display = 'none'; return; }
            results.innerHTML = opts.map(o => `<div data-val="${o.value}">${o.text}</div>`).join('');
            results.style.display = 'block';
            results.querySelectorAll('div').forEach(d => {
                d.addEventListener('click', function() {
                    select.value  = this.dataset.val;
                    input.value   = this.textContent;
                    results.style.display = 'none';
                });
            });
        });
        document.addEventListener('click', e => { if (!input.contains(e.target) && !results.contains(e.target)) results.style.display = 'none'; });
    }

    setupSearchInput('search_classificacao', 'classificacao_id', 'results_classificacao');
    setupSearchInput('search_centro_custo', 'centro_custo_id', 'results_centro_custo');

    // ── Repetição / Parcelamento ──
    const repetirChk = document.getElementById('repetir');
    const containerTR = document.getElementById('container_tipo_repeticao');
    if (repetirChk && containerTR) {
        repetirChk.addEventListener('change', function() {
            containerTR.style.display = this.checked ? 'block' : 'none';
        });
    }

    const parcelasInput = document.getElementById('parcelas');
    document.getElementById('btn-minus-parcelas')?.addEventListener('click', () => {
        let v = parseInt(parcelasInput.value) || 1;
        if (v > 1) parcelasInput.value = v - 1;
    });
    document.getElementById('btn-plus-parcelas')?.addEventListener('click', () => {
        let v = parseInt(parcelasInput.value) || 1;
        parcelasInput.value = v + 1;
    });

    // ── Modal Cadastro Rápido ──
    const qrModal = document.getElementById('quickRegisterModal');
    function closeQuickModal() { qrModal.style.display = 'none'; }
    document.getElementById('btnCancelQuickRegister')?.addEventListener('click', closeQuickModal);
    document.getElementById('btnCancelQuickRegister2')?.addEventListener('click', closeQuickModal);

    function openQuickModal(tipo) {
        document.getElementById('qr_tipo').value = tipo;
        document.getElementById('qr_nome').value = '';
        document.getElementById('qr_email').value = '';
        document.getElementById('qr_telefone').value = '';
        document.getElementById('quickRegisterTitle').textContent =
            tipo === 'cliente' ? 'Novo Cliente' : 'Novo Fornecedor';
        qrModal.style.display = 'flex';
        setTimeout(() => document.getElementById('qr_nome').focus(), 100);
    }

    document.getElementById('btnAddCliente')?.addEventListener('click', () => openQuickModal('cliente'));
    document.getElementById('btnAddFornecedor')?.addEventListener('click', () => openQuickModal('fornecedor'));

    // ── Cadastro rápido de Classificação ──
    document.getElementById('addClassificacaoBtn')?.addEventListener('click', function() {
        const nome = prompt("Digite o nome da nova categoria:");
        if (!nome || !nome.trim()) return;
        const tipo = document.querySelector('input[name="tipo"]:checked')?.value || 'R';
        const body = new URLSearchParams();
        body.append('nome', nome.trim());
        body.append('tipo', tipo);
        body.append('csrf_token', '<?php echo $csrf_token ?? ''; ?>');
        fetch('<?php echo BASE_URL; ?>/financeiro/addClassificacao', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
            body: body
        })
        .then(r => r.json())
        .then(d => {
            if (d.success) {
                const sel = document.getElementById('classificacao_id');
                const opt = new Option(d.data.nome, d.data.id);
                opt.selected = true;
                opt.dataset.tipo = tipo;
                sel.add(opt);
                if (window._allClassificacoes) window._allClassificacoes.push(opt);
                document.getElementById('search_classificacao').value = d.data.nome;
                sel.dispatchEvent(new Event('change'));
            } else {
                alert(d.message || 'Erro ao adicionar categoria.');
            }
        })
        .catch(() => alert('Erro de conexão.'));
    });

    // ── Cadastro rápido de Centro de Custo ──
    document.getElementById('addCentroCustoBtn')?.addEventListener('click', function() {
        const nome = prompt("Digite o nome do novo centro de custo:");
        if (!nome || !nome.trim()) return;
        const body = new URLSearchParams();
        body.append('nome', nome.trim());
        body.append('csrf_token', '<?php echo $csrf_token ?? ''; ?>');
        fetch('<?php echo BASE_URL; ?>/financeiro/addCentroCusto', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
            body: body
        })
        .then(r => r.json())
        .then(d => {
            if (d.success) {
                const sel = document.getElementById('centro_custo_id');
                const opt = new Option(d.data.nome, d.data.id, true, true);
                sel.add(opt);
                document.getElementById('search_centro_custo').value = d.data.nome;
                sel.dispatchEvent(new Event('change'));
            } else {
                alert(d.message || 'Erro ao adicionar centro de custo.');
            }
        })
        .catch(() => alert('Erro de conexão.'));
    });

    document.getElementById('quickRegisterForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        const tipo   = document.getElementById('qr_tipo').value;
        const nome   = document.getElementById('qr_nome').value.trim();
        const email  = document.getElementById('qr_email').value.trim();
        const tel    = document.getElementById('qr_telefone').value.trim();
        if (!nome) { alert('Informe o nome.'); return; }

        const url   = tipo === 'cliente' ? '<?php echo BASE_URL; ?>/clientes/salvar' : '<?php echo BASE_URL; ?>/fornecedores/salvar';
        const body  = new URLSearchParams();
        body.append('csrf_token', '<?php echo $csrf_token ?? ''; ?>');
        body.append('nome', nome);
        if (tipo === 'cliente') {
            body.append('tipo_cliente', 'Fisica');
            if (email) body.append('contatos[principal][email]', email);
            if (tel)   body.append('contatos[principal][telefone]', tel);
        } else {
            if (email) body.append('contato[email_principal]', email);
            if (tel)   body.append('contato[telefone_comercial]', tel);
        }

        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: body
        })
        .then(r => r.json())
        .then(d => {
            if (d.success) {
                const selectId = tipo === 'cliente' ? 'cliente_id' : 'fornecedor_id';
                const sel = document.getElementById(selectId);
                if (sel) {
                    const opt = document.createElement('option');
                    opt.value = d.data.id;
                    opt.textContent = d.data.nome;
                    opt.selected = true;
                    sel.appendChild(opt);
                    sel.dispatchEvent(new Event('change'));
                }
                closeQuickModal();
            } else {
                alert(d.message || 'Erro ao salvar.');
            }
        })
        .catch(() => alert('Erro de conexão.'));
    });

    // ── Modal Editar PP ──
    document.getElementById('tabela-pagamentos-parciais')?.addEventListener('click', function(e) {
        const row = e.target.closest('tr');
        if (!row) return;
        if (e.target.closest('.edit-pp')) {
            const id = row.dataset.id;
            document.getElementById('edit-pp-id').value = id;
            document.getElementById('edit-pp-data').value = row.querySelector('.pp-data').textContent.trim().split('/').reverse().join('-');
            document.getElementById('edit-pp-valor').value = row.querySelector('.pp-valor').textContent.replace('R$ ','').replace(/\./g,'').replace(',','.');
            document.getElementById('edit-pp-forma').value = row.querySelector('.pp-forma').textContent.trim() === '—' ? '' : row.querySelector('.pp-forma').textContent.trim();
            document.getElementById('edit-pp-feedback').style.display = 'none';
            document.getElementById('modal-editar-pp').style.display = 'flex';
        }
        if (e.target.closest('.delete-pp')) {
            if (!confirm('Excluir este pagamento?')) return;
            const id = row.dataset.id;
            fetch('<?php echo BASE_URL; ?>/financeiro/ajaxExcluirPagamentoParcial', {
                method:'POST',
                headers:{'Content-Type':'application/x-www-form-urlencoded','X-Requested-With':'XMLHttpRequest'},
                body: new URLSearchParams({'csrf_token':'<?php echo $csrf_token ?? ''; ?>','id':id})
            }).then(r=>r.json()).then(d=>{
                if(d.success){ row.remove(); atualizarResumoPP(d.valor_pago, d.saldo_restante); if(!document.querySelector('#tabela-pagamentos-parciais tbody tr')) location.reload(); }
                else alert(d.message);
            });
        }
    });

    document.getElementById('btn-salvar-edit-pp')?.addEventListener('click', function() {
        const id    = document.getElementById('edit-pp-id').value;
        const data  = document.getElementById('edit-pp-data').value;
        const valor = document.getElementById('edit-pp-valor').value;
        const forma = document.getElementById('edit-pp-forma').value;
        const fb    = document.getElementById('edit-pp-feedback');
        if (!id || !data || !valor) { fb.textContent='Preencha data e valor.'; fb.style.display='block'; return; }
        fetch('<?php echo BASE_URL; ?>/financeiro/ajaxEditarPagamentoParcial', {
            method:'POST',
            headers:{'Content-Type':'application/x-www-form-urlencoded','X-Requested-With':'XMLHttpRequest'},
            body: new URLSearchParams({'csrf_token':'<?php echo $csrf_token ?? ''; ?>','id':id,'valor':valor,'data_pagamento':data,'forma_pagamento':forma})
        }).then(r=>r.json()).then(d=>{
            if(d.success){
                const row = document.querySelector(`#tabela-pagamentos-parciais tbody tr[data-id="${id}"]`);
                if(row){
                    row.querySelector('.pp-data').textContent = new Date(data+'T12:00:00').toLocaleDateString('pt-BR');
                    row.querySelector('.pp-valor').textContent = 'R$ '+parseFloat(valor.replace(/\./g,'').replace(',','.')).toLocaleString('pt-BR',{minimumFractionDigits:2});
                    row.querySelector('.pp-forma').textContent = forma||'—';
                }
                document.getElementById('modal-editar-pp').style.display = 'none';
                atualizarResumoPP(d.valor_pago, d.saldo_restante);
            } else { fb.textContent=d.message; fb.style.display='block'; }
        });
    });

    function atualizarResumoPP(valorPago, saldoRestante) {
        const fmt = v => 'R$ '+Number(v).toLocaleString('pt-BR',{minimumFractionDigits:2});
        const stats = document.querySelectorAll('#container_parcial .fin-parcial-stat .value');
        if(stats[1]) stats[1].textContent = fmt(valorPago);
        if(stats[2]) stats[2].textContent = fmt(saldoRestante);
    }

    // ── Anti-duplo clique ──
    const financeForm = document.querySelector('form[action*="financeiro/salvar"]');
    if (financeForm) {
        financeForm.addEventListener('submit', function(e) {
            updateValorReal();
            if (financeForm.checkValidity()) {
                const btn = financeForm.querySelector('button[type="submit"]');
                if (btn && !btn.disabled) {
                    const orig = btn.innerHTML;
                    setTimeout(() => {
                        btn.disabled = true;
                        btn.style.opacity = '.55';
                        btn.innerHTML = '<svg class="animate-spin" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="animation:spin 1s linear infinite"><path d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" opacity=".25"/><path d="M12 3a9 9 0 019 9" opacity=".75"/></svg> Processando…';
                    }, 50);
                    setTimeout(() => { if (btn.disabled) { btn.disabled=false; btn.style.opacity=''; btn.innerHTML=orig; } }, 30000);
                }
            }
        });
    }

    // ── Inicialização ──
    preSelecionarCampos();

    // ── File input feedback ──
    document.getElementById('anexo')?.addEventListener('change', function() {
        const label = this.closest('.fin-field').querySelector('.fin-file-label span');
        if (label) label.textContent = this.files[0]?.name || 'Clique para selecionar arquivo…';
    });

});

// CSS spin keyframe
const styleEl = document.createElement('style');
styleEl.textContent = '@keyframes spin{to{transform:rotate(360deg)}}';
document.head.appendChild(styleEl);
</script>
<!--
[PROMPT_SUGGESTION]Como implementar a lógica de repetição e parcelamento no método salvar do FinanceiroController?[/PROMPT_SUGGESTION]
[PROMPT_SUGGESTION]Como adicionar validações para garantir que os campos de repetição e parcelamento sejam preenchidos corretamente?[/PROMPT_SUGGESTION]
-->