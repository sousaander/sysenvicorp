<?php
$isEdit = isset($fornecedor) && !empty($fornecedor['id']);
$actionUrl = BASE_URL . '/fornecedores/salvar';

$endereco        = $isEdit && !empty($fornecedor['endereco_json'])        ? json_decode($fornecedor['endereco_json'], true)        : [];
$contato         = $isEdit && !empty($fornecedor['contato_json'])         ? json_decode($fornecedor['contato_json'], true)         : [];
$dados_financeiros = $isEdit && !empty($fornecedor['dados_financeiros_json']) ? json_decode($fornecedor['dados_financeiros_json'], true) : [];
$info_comerciais = $isEdit && !empty($fornecedor['info_comerciais_json']) ? json_decode($fornecedor['info_comerciais_json'], true) : [];

$fornecedor['nome'] = $fornecedor['nome'] ?? ($fornecedor['razao_social'] ?? '');
$fornecedor['cnpj'] = $fornecedor['cnpj'] ?? ($fornecedor['cnpj_cpf'] ?? '');
?>
<style>
  /* ── Reset & Base ── */
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  :root {
    --c-primary:   #185FA5;
    --c-primary-d: #0F4C81;
    --c-primary-l: #EBF3FB;
    --c-success:   #1D9E75;
    --c-success-l: #E1F5EE;
    --c-danger:    #E24B4A;
    --c-danger-l:  #FCEBEB;
    --c-warn:      #BA7517;
    --c-warn-l:    #FAEEDA;
    --bg:          #F7F8FA;
    --card:        #ffffff;
    --border:      #E2E6EC;
    --border2:     #CBD2DB;
    --txt:         #1A2233;
    --txt2:        #5A6478;
    --txt3:        #8D97A8;
    --rad:         8px;
    --rad-lg:      12px;
    --sidebar-w:   244px;
  }

  /* ── Suporte ao Modo Escuro ── */
  .dark-theme {
    --bg:          var(--db-bg, #0d1117);
    --card:        var(--db-surface, #161b22);
    --border:      var(--db-border, #30363d);
    --border2:     var(--db-border, #30363d);
    --txt:         var(--db-text, #e6edf3);
    --txt2:        var(--db-text2, #8b949e);
    --txt3:        #6e7681;
    --c-primary-l: rgba(24, 95, 165, 0.15);
    --c-success-l: rgba(29, 158, 117, 0.15);
    --c-danger-l:  rgba(226, 75, 74, 0.15);
    --c-warn-l:    rgba(186, 117, 23, 0.15);
  }

  #form-fornecedor-view {
    background: transparent; font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
    color: var(--txt);
  }

  /* ── Navegação Horizontal de Etapas ── */
  .steps-header {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: var(--rad-lg);
    padding: 8px;
    margin-bottom: 24px;
    display: flex;
    gap: 8px;
    overflow-x: auto;
    white-space: nowrap;
    position: sticky;
    top: 10px;
    z-index: 45;
    box-shadow: var(--fin-shadow);
    scrollbar-width: none; /* Firefox */
  }
  .steps-header::-webkit-scrollbar { display: none; } /* Chrome/Safari */

  .step-item {
    display: flex; align-items: center; gap: 10px;
    padding: 8px 16px; border-radius: var(--rad);
    cursor: pointer; transition: all .2s;
    text-decoration: none;
    border: 1px solid transparent;
    background: var(--bg);
    color: var(--txt2);
  }
  .step-item:hover { border-color: var(--c-primary); color: var(--c-primary); }
  .step-item.active {
    background: var(--c-primary);
    color: #fff !important;
    box-shadow: 0 4px 12px rgba(24,95,165,0.2);
  }
  .step-num {
    width: 22px; height: 22px; border-radius: 50%; flex-shrink: 0;
    background: rgba(0,0,0,0.05); color: var(--txt3);
    font-size: 11px; font-weight: 700; display: flex; align-items: center; justify-content: center;
  }
  .dark-theme .step-num { background: rgba(255,255,255,0.1); }
  .step-item.active .step-num { background: rgba(255,255,255,0.25); color: #fff; }
  .step-label { font-size: 12.5px; font-weight: 600; }

  /* ── Form Content Area ── */
  .form-content-area { width: 100%; max-width: 1060px; margin: 0 auto; }

  .top-bar {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: var(--rad-lg);
    padding: 14px 28px;
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 24px;
    box-shadow: var(--fin-shadow);
  }
  .top-bar-left { display: flex; align-items: center; gap: 12px; }
  .top-bar-left .page-title { font-size: 15px; font-weight: 600; white-space: nowrap; }
  .top-bar-left .page-title .step-info { font-weight: 400; color: var(--txt3); font-size: 13px; margin-left: 8px; }
  .breadcrumb { font-size: 12px; color: var(--txt3); margin-top: 1px; }
  .breadcrumb b { color: var(--txt2); }
  .top-bar-actions { display: flex; gap: 8px; }
  .menu-toggle { display: block; background: none; border: none; font-size: 20px; cursor: pointer; color: var(--txt2); }

  /* ── Buttons ── */
  .btn {
    height: 36px; padding: 0 16px; border-radius: var(--rad);
    font-size: 13px; font-weight: 500; cursor: pointer;
    border: 1px solid transparent; display: inline-flex; align-items: center; gap: 6px;
    transition: all .15s; white-space: nowrap; text-decoration: none;
    font-family: inherit;
  }
  .btn-ghost   { background: transparent; border-color: var(--border2); color: var(--txt2); }
  .btn-ghost:hover { background: var(--db-surface2); }
  .btn-danger  { background: transparent; border-color: var(--c-danger); color: var(--c-danger); }
  .btn-danger:hover { background: var(--c-danger-l); }
  .btn-primary { background: var(--c-primary); color: #fff; border-color: var(--c-primary); }
  .btn-primary:hover { background: var(--c-primary-d); }
  .btn-outline { background: transparent; border-color: var(--c-primary); color: var(--c-primary); }
  .btn-outline:hover { background: var(--c-primary-l); }

  /* ── Form Body ── */
  .form-body { flex: 1; }

  /* ── Section Cards ── */
  .section-card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: var(--rad-lg);
    margin-bottom: 18px;
    width: 100%;
    overflow: hidden;
    transition: box-shadow .2s;
  }
  .section-card:focus-within { box-shadow: 0 0 0 2px rgba(24,95,165,.15); }
  .section-header {
    padding: 14px 20px;
    border-bottom: 1px solid var(--border);
    display: flex; align-items: center; gap: 12px;
    background: var(--bg);
  }
  .section-icon {
    width: 30px; height: 30px; border-radius: 7px;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
  }
  .section-header-text h3 { font-size: 13.5px; font-weight: 600; color: var(--txt); }
  .section-header-text p  { font-size: 11.5px; color: var(--txt3); margin-top: 1px; }
  .section-badge {
    margin-left: auto;
    padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 500;
  }
  .badge-wait    { background: var(--c-warn-l); color: var(--c-warn); }
  /* Estilo para as legendas de upload na seção de documentação */
  .doc-label { 
    font-size: 13px !important; font-weight: 600 !important; color: var(--txt2); margin-bottom: 10px; display: block;
  }
  .badge-ok      { background: var(--c-success-l); color: var(--c-success); }
  .badge-active  { background: var(--c-primary-l); color: var(--c-primary); }
  .section-body  { padding: 20px; }

  /* ── Grid Helpers ── */
  .g2  { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
  .g3  { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; }
  .g23 { display: grid; grid-template-columns: 2fr 1fr; gap: 16px; }
  .g12 { display: grid; grid-template-columns: 200px 1fr; gap: 16px; }
  .g14 { display: grid; grid-template-columns: 160px 2fr 1fr 1fr; gap: 16px; }
  .g26 { display: grid; grid-template-columns: 2fr 4fr; gap: 16px; }
  .col2 { grid-column: span 2; }
  .col3 { grid-column: span 3; }
  .gap-bot { margin-bottom: 16px; }

  /* ── Field ── */
  .field { display: flex; flex-direction: column; gap: 5px; }
  .field > label {
    font-size: 12px; font-weight: 500; color: var(--txt2); letter-spacing: .01em;
  }
  .field > label .req { color: var(--c-danger); margin-left: 2px; }

  /* ── Inputs ── */
  input[type=text],
  input[type=email],
  input[type=url],
  input[type=date],
  input[type=file],
  select,
  textarea {
    width: 100%; padding: 0 10px; height: 36px;
    border: 1px solid var(--border2); border-radius: var(--rad);
    font-family: inherit; font-size: 13px; color: var(--txt);
    background: var(--card); outline: none;
    color: var(--txt);
    transition: border-color .15s, box-shadow .15s;
    appearance: none;
  }
  input:focus, select:focus, textarea:focus {
    border-color: var(--c-primary);
    box-shadow: 0 0 0 3px rgba(24,95,165,.1);
  }
  input:disabled { background: var(--db-surface2); color: var(--txt3); cursor: not-allowed; }
  textarea { height: 86px; padding: 8px 10px; resize: vertical; line-height: 1.55; }
  select {
    padding-right: 28px; cursor: pointer;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6'%3E%3Cpath d='M0 0l5 6 5-6z' fill='%238D97A8'/%3E%3C/svg%3E");
    background-repeat: no-repeat; background-position: right 10px center;
  }
  input[type=file] {
    height: auto; padding: 7px 10px;
    font-size: 12.5px; color: var(--txt3); cursor: pointer;
  }

  /* ── Input with button (CEP / CNPJ) ── */
  .input-addon { display: flex; }
  .input-addon input { border-radius: var(--rad) 0 0 var(--rad); border-right: none; flex: 1; }
  .addon-btn {
    height: 36px; padding: 0 12px;
    background: var(--c-primary-l); border: 1px solid var(--border2);
    border-left: none; border-radius: 0 var(--rad) var(--rad) 0;
    cursor: pointer; color: var(--c-primary); display: flex; align-items: center; gap: 5px;
    font-size: 12px; font-weight: 500; white-space: nowrap; transition: background .15s;
    font-family: inherit;
  }
  .addon-btn:hover { background: var(--c-primary); color: #fff; }
  .addon-btn svg { flex-shrink: 0; }

  /* ── Radio Tipo Pessoa ── */
  .radio-group { display: flex; gap: 10px; flex-wrap: wrap; }
  .radio-card {
    display: flex; align-items: center; gap: 8px;
    padding: 8px 16px; border: 1px solid var(--border2); border-radius: var(--rad);
    cursor: pointer; font-size: 13px; color: var(--txt2);
    background: var(--card); transition: all .15s; user-select: none;
  }
  .radio-card:hover { border-color: var(--c-primary); color: var(--c-primary); background: var(--c-primary-l); }
  .radio-card input[type=radio] { appearance: none; width: 14px; height: 14px; border: 2px solid currentColor; border-radius: 50%; display: grid; place-content: center; cursor: pointer; }
  .radio-card input[type=radio]:checked::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: var(--c-primary); display: block; }
  .radio-card:has(input:checked) { border-color: var(--c-primary); color: var(--c-primary); background: var(--c-primary-l); font-weight: 500; }

  /* ── Checkbox ── */
  .check-row { display: flex; align-items: center; gap: 7px; margin-top: 7px; cursor: pointer; user-select: none; font-size: 12.5px; color: var(--txt3); }
  .check-row input[type=checkbox] {
    appearance: none; width: 15px; height: 15px; border-radius: 4px;
    border: 1.5px solid var(--border2); background: var(--card); cursor: pointer;
    display: grid; place-content: center; flex-shrink: 0;
  }
  .check-row input[type=checkbox]:checked { background: var(--c-primary); border-color: var(--c-primary); }
  .check-row input[type=checkbox]:checked::before { content: '✓'; color: #fff; font-size: 10px; line-height: 1; }

  /* ── Sub-block (Representante) ── */
  .sub-block {
    background: var(--db-surface2); border: 1px solid var(--border);
    border-radius: var(--rad); padding: 16px; margin-top: 6px;
  }
  .sub-block-title {
    font-size: 11px; font-weight: 600; color: var(--txt3);
    text-transform: uppercase; letter-spacing: .06em; margin-bottom: 12px;
  }

  /* ── Upload Area ── */
  .upload-zone {
    border: 1.5px dashed var(--border2); border-radius: var(--rad);
    padding: 24px 16px; text-align: center; cursor: pointer;
    background: var(--bg); transition: all .15s; display: flex;
    flex-direction: column; align-items: center; justify-content: center; min-height: 110px;
  }
  .upload-zone:hover { border-color: var(--c-primary); background: var(--c-primary-l); }
  .upload-zone .upload-ico { font-size: 22px; line-height: 1; display: block; margin-bottom: 5px; }
  .upload-zone p { font-size: 12.5px; color: var(--txt3); }
  .upload-zone strong { color: var(--c-primary); }

  /* ── Status selector ── */
  .status-options { display: flex; gap: 10px; }
  .status-opt {
    flex: 1; padding: 10px 14px; border: 1.5px solid var(--border2);
    border-radius: var(--rad); cursor: pointer; text-align: center; transition: all .15s;
  }
  .status-opt:hover { border-color: var(--c-primary); }
  .status-opt input[type=radio] { position: absolute; opacity: 0; pointer-events: none; }
  .status-dot { width: 8px; height: 8px; border-radius: 50%; margin: 0 auto 6px; background: var(--txt3); }
  .status-label { font-size: 12px; color: var(--txt2); font-weight: 500; }
  .status-opt.sel-ativo   { border-color: var(--c-success); background: var(--c-success-l); }
  .status-opt.sel-ativo   .status-dot { background: var(--c-success); }
  .status-opt.sel-ativo   .status-label { color: var(--c-success); }
  .status-opt.sel-inativo { border-color: var(--c-danger); background: var(--c-danger-l); }
  .status-opt.sel-inativo .status-dot { background: var(--c-danger); }
  .status-opt.sel-inativo .status-label { color: var(--c-danger); }
  .status-opt.sel-hom     { border-color: var(--c-warn); background: var(--c-warn-l); }
  .status-opt.sel-hom     .status-dot { background: var(--c-warn); }
  .status-opt.sel-hom     .status-label { color: var(--c-warn); }

  /* ── Footer Actions ── */
  .footer-actions {
    background: var(--card); 
    border: 1px solid var(--border);
    border-radius: var(--rad-lg);
    margin: 10px 28px 20px;
    max-width: 1000px;
    padding: 4px 24px; display: flex; align-items: center; justify-content: space-between;
    position: sticky; bottom: 20px; z-index: 50;
  }
  .progress-area .prog-info { font-size: 12px; color: var(--txt3); }
  .progress-bar { width: 160px; height: 4px; background: var(--border); border-radius: 2px; margin-top: 2px; }
  .progress-fill { height: 4px; background: var(--c-primary); border-radius: 2px; transition: width .3s; }
  .footer-btns { display: flex; gap: 8px; }

  /* ── Utilities ── */
  .divider { height: 1px; background: var(--border); margin: 18px 0; }
  .hint { font-size: 11.5px; color: var(--txt3); margin-top: 4px; }
  .inative-group { display: none; }
  .inative-group.visible { display: grid; }
  /* ── Spinner for CNPJ search ── */
  .spinner { animation: spin .7s linear infinite; display: none; }
  @keyframes spin { to { transform: rotate(360deg); } }
</style>
<div id="form-fornecedor-view">
  <div class="form-content-area">

    <!-- Top Bar -->
    <header class="top-bar">
      <div class="top-bar-left">
        <div class="page-title">
          <?php echo $isEdit ? 'Editar Fornecedor' : 'Cadastrar Novo Fornecedor'; ?>
          <span class="step-info">— preencha todas as seções</span>
        </div>
        <div class="breadcrumb">
          <a href="<?php echo BASE_URL; ?>/fornecedores" style="color:inherit;text-decoration:none">Fornecedores</a>
          &rsaquo;
          <b><?php echo $isEdit ? 'Editar' : 'Novo Cadastro'; ?></b>
        </div>
      </div>
      <div class="top-bar-actions">
        <button type="button" class="btn btn-ghost" id="btn-save-draft">Salvar rascunho</button>
        <a href="<?php echo BASE_URL; ?>/fornecedores" class="btn btn-danger">Voltar</a>
      </div>
    </header>

    <!-- Navegação Horizontal de Etapas -->
    <nav class="steps-header" aria-label="Etapas do cadastro">
      <?php
      $steps = [
        1 => ['label' => 'Identificação',    'anchor' => '#sec-basicos',    'icon' => 'bx bx-id-card'],
        2 => ['label' => 'Endereço',         'anchor' => '#sec-endereco',   'icon' => 'bx bx-map-alt'],
        3 => ['label' => 'Contato',          'anchor' => '#sec-contato',    'icon' => 'bx bx-phone-call'],
        4 => ['label' => 'Financeiro',       'anchor' => '#sec-financeiro', 'icon' => 'bx bx-wallet'],
        5 => ['label' => 'Documentação',     'anchor' => '#sec-docs',       'icon' => 'bx bx-file-blank'],
        6 => ['label' => 'Comercial',        'anchor' => '#sec-comercial',  'icon' => 'bx bx-briefcase-alt-2'],
        7 => ['label' => 'Status',           'anchor' => '#sec-status',     'icon' => 'bx bx-check-shield'],
      ];
      foreach ($steps as $n => $s):
      ?>
        <a class="step-item <?php echo $n === 1 ? 'active' : ''; ?>" href="<?php echo $s['anchor']; ?>" data-step="<?php echo $n; ?>">
          <div class="step-num"><?php echo $n; ?></div>
          <div class="step-label"><?php echo $s['label']; ?></div>
        </a>
      <?php endforeach; ?>
    </nav>

    <!-- Form -->
    <form action="<?php echo $actionUrl; ?>" method="POST" enctype="multipart/form-data" id="fornecedor-form" novalidate>
      <?php if ($isEdit): ?>
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($fornecedor['id']); ?>">
      <?php endif; ?>

      <div class="form-body">

        <!-- ── 1. Dados Básicos ── -->
        <section class="section-card" id="sec-basicos">
          <div class="section-header">
            <div class="section-icon" style="background:var(--c-primary-l)">
              <svg width="15" height="15" viewBox="0 0 16 16" fill="none"><rect x="2" y="2" width="12" height="3" rx="1" fill="#185FA5"/><rect x="2" y="7" width="8" height="2" rx="1" fill="#185FA5" opacity=".5"/><rect x="2" y="11" width="10" height="2" rx="1" fill="#185FA5" opacity=".3"/></svg>
            </div>
            <div class="section-header-text">
              <h3>1. Dados Básicos</h3>
              <p>Identificação fiscal e classificação do fornecedor</p>
            </div>
          </div>
          <div class="section-body">

            <!-- Tipo de Pessoa -->
            <div class="field gap-bot">
              <label>Tipo de Fornecedor <span class="req">*</span></label>
              <div class="radio-group">
                <label class="radio-card">
                  <input type="radio" name="tipo_pessoa" value="Juridica" id="tipo_juridica"
                    <?php echo (($fornecedor['tipo_pessoa'] ?? 'Juridica') === 'Juridica') ? 'checked' : ''; ?>>
                  Pessoa Jurídica (CNPJ)
                </label>
                <label class="radio-card">
                  <input type="radio" name="tipo_pessoa" value="Fisica" id="tipo_fisica"
                    <?php echo (($fornecedor['tipo_pessoa'] ?? '') === 'Fisica') ? 'checked' : ''; ?>>
                  Pessoa Física (CPF)
                </label>
              </div>
            </div>

            <!-- Razão Social + CNPJ -->
            <div class="g23 gap-bot">
              <div class="field">
                <label for="nome">Nome / Razão Social <span class="req">*</span></label>
                <input type="text" id="nome" name="nome" required
                  value="<?php echo htmlspecialchars($fornecedor['nome'] ?? ''); ?>"
                  placeholder="Razão social ou nome completo">
              </div>
              <div class="field">
                <label for="cnpj" id="label-cnpj">CNPJ <span class="req">*</span></label>
                <div class="input-addon">
                  <input type="text" id="cnpj" name="cnpj" placeholder="Apenas números"
                    value="<?php echo htmlspecialchars($fornecedor['cnpj'] ?? ''); ?>">
                  <button type="button" class="addon-btn" id="buscar-cnpj-btn" title="Consultar CNPJ na Receita Federal">
                    <svg id="cnpj-search-icon" width="13" height="13" viewBox="0 0 16 16" fill="none"><circle cx="7" cy="7" r="4.5" stroke="#185FA5" stroke-width="1.5"/><line x1="10.5" y1="10.5" x2="14" y2="14" stroke="#185FA5" stroke-width="1.5" stroke-linecap="round"/></svg>
                    <svg id="cnpj-loading-spinner" class="spinner" xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="#185FA5" stroke-width="3" opacity=".25"/><path d="M4 12a8 8 0 018-8" stroke="#185FA5" stroke-width="3" stroke-linecap="round"/></svg>
                    Consultar
                  </button>
                </div>
              </div>
            </div>

            <!-- Nome Fantasia + Categoria -->
            <div class="g23 gap-bot">
              <div class="field">
                <label for="nome_fantasia">Nome Fantasia</label>
                <input type="text" id="nome_fantasia" name="nome_fantasia"
                  value="<?php echo htmlspecialchars($fornecedor['nome_fantasia'] ?? ''); ?>"
                  placeholder="Nome de uso comercial">
              </div>
              <div class="field">
                <label for="categoria_fornecimento">Categoria <span class="req">*</span></label>
                <select id="categoria_fornecimento" name="categoria_fornecimento" required>
                  <option value="">Selecione...</option>
                  <?php foreach (['Materiais','Serviços','Insumos','Consultoria'] as $cat): ?>
                    <option value="<?php echo $cat; ?>"
                      <?php echo (($fornecedor['categoria_fornecimento'] ?? '') === $cat) ? 'selected' : ''; ?>>
                      <?php echo $cat; ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>

            <!-- Inscrições -->
            <div class="g2">
              <div class="field">
                <label for="inscricao_estadual">Inscrição Estadual</label>
                <input type="text" id="inscricao_estadual" name="inscricao_estadual"
                  value="<?php echo htmlspecialchars($fornecedor['inscricao_estadual'] ?? ''); ?>"
                  placeholder="Nº de inscrição"
                  <?php echo !empty($fornecedor['ie_isento']) ? 'disabled' : ''; ?>>
                <label class="check-row">
                  <input type="checkbox" id="ie_isento" name="ie_isento" value="1"
                    <?php echo !empty($fornecedor['ie_isento']) ? 'checked' : ''; ?>>
                  Contribuinte Isento
                </label>
              </div>
              <div class="field">
                <label for="inscricao_municipal">Inscrição Municipal</label>
                <input type="text" id="inscricao_municipal" name="inscricao_municipal"
                  value="<?php echo htmlspecialchars($fornecedor['inscricao_municipal'] ?? ''); ?>"
                  placeholder="Nº de inscrição">
              </div>
            </div>

          </div>
        </section>

        <!-- ── 2. Endereço ── -->
        <section class="section-card" id="sec-endereco">
          <div class="section-header">
            <div class="section-icon" style="background:var(--c-success-l)">
              <svg width="14" height="14" viewBox="0 0 16 16" fill="none"><path d="M8 1.5C5.5 1.5 3.5 3.5 3.5 6c0 3.5 4.5 8.5 4.5 8.5s4.5-5 4.5-8.5c0-2.5-2-4.5-4.5-4.5z" stroke="#1D9E75" stroke-width="1.4" fill="none"/><circle cx="8" cy="6" r="1.5" fill="#1D9E75"/></svg>
            </div>
            <div class="section-header-text">
              <h3>2. Endereço</h3>
              <p>Localização e dados de correspondência</p>
            </div>
          </div>
          <div class="section-body">

            <div class="g26 gap-bot">
              <div class="field">
                <label for="cep">CEP <span class="req">*</span></label>
                <div class="input-addon">
                  <input type="text" id="cep" name="endereco[cep]" required
                    placeholder="00000-000"
                    value="<?php echo htmlspecialchars($endereco['cep'] ?? ''); ?>">
                  <button type="button" class="addon-btn" id="buscar-cep-btn">
                    <svg width="13" height="13" viewBox="0 0 16 16" fill="none"><circle cx="7" cy="7" r="4.5" stroke="#185FA5" stroke-width="1.5"/><line x1="10.5" y1="10.5" x2="14" y2="14" stroke="#185FA5" stroke-width="1.5" stroke-linecap="round"/></svg>
                    Buscar
                  </button>
                </div>
              </div>
              <div class="field">
                <label for="logradouro">Logradouro <span class="req">*</span></label>
                <input type="text" id="logradouro" name="endereco[logradouro]" required
                  placeholder="Rua, Avenida, Alameda..."
                  value="<?php echo htmlspecialchars($endereco['logradouro'] ?? ''); ?>">
              </div>
            </div>

            <div class="g3 gap-bot">
              <div class="field">
                <label for="numero">Número</label>
                <input type="text" id="numero" name="endereco[numero]"
                  placeholder="Ex: 123 ou S/N"
                  value="<?php echo htmlspecialchars($endereco['numero'] ?? ''); ?>">
              </div>
              <div class="col2 field">
                <label for="complemento">Complemento</label>
                <input type="text" id="complemento" name="endereco[complemento]"
                  placeholder="Bloco, sala, andar, conjunto..."
                  value="<?php echo htmlspecialchars($endereco['complemento'] ?? ''); ?>">
              </div>
            </div>

            <div style="display:grid;grid-template-columns:2fr 2fr 1fr 1fr;gap:16px">
              <div class="field">
                <label for="bairro">Bairro</label>
                <input type="text" id="bairro" name="endereco[bairro]"
                  value="<?php echo htmlspecialchars($endereco['bairro'] ?? ''); ?>">
              </div>
              <div class="field">
                <label for="cidade">Cidade <span class="req">*</span></label>
                <input type="text" id="cidade" name="endereco[cidade]" required
                  value="<?php echo htmlspecialchars($endereco['cidade'] ?? ''); ?>">
              </div>
              <div class="field">
                <label for="uf">UF <span class="req">*</span></label>
                <input type="text" id="uf" name="endereco[uf]" required maxlength="2"
                  placeholder="PR"
                  value="<?php echo htmlspecialchars($endereco['uf'] ?? ''); ?>">
              </div>
              <div class="field">
                <label for="pais">País</label>
                <input type="text" id="pais" name="endereco[pais]"
                  value="<?php echo htmlspecialchars($endereco['pais'] ?? 'Brasil'); ?>"
                  style="background:#F7F8FA;color:var(--txt3)">
              </div>
            </div>

          </div>
        </section>

        <!-- ── 3. Contato ── -->
        <section class="section-card" id="sec-contato">
          <div class="section-header">
            <div class="section-icon" style="background:var(--c-primary-l)">
              <svg width="14" height="14" viewBox="0 0 16 16" fill="none"><path d="M2 4a1 1 0 011-1h10a1 1 0 011 1v8a1 1 0 01-1 1H3a1 1 0 01-1-1V4z" stroke="#185FA5" stroke-width="1.4"/><path d="M2 4l6 5 6-5" stroke="#185FA5" stroke-width="1.4" stroke-linecap="round"/></svg>
            </div>
            <div class="section-header-text">
              <h3>3. Contato</h3>
              <p>Telefones, e-mails e representante comercial</p>
            </div>
          </div>
          <div class="section-body">

            <div class="g2 gap-bot">
              <div class="field">
                <label for="telefone_comercial">Telefone Comercial <span class="req">*</span></label>
                <input type="text" id="telefone_comercial" name="contato[telefone_comercial]" required
                  placeholder="(00) 0000-0000"
                  value="<?php echo htmlspecialchars($contato['telefone_comercial'] ?? ''); ?>">
              </div>
              <div class="field">
                <label for="email_principal">E-mail Principal <span class="req">*</span></label>
                <input type="email" id="email_principal" name="contato[email_principal]" required
                  placeholder="contato@empresa.com.br"
                  value="<?php echo htmlspecialchars($contato['email_principal'] ?? ''); ?>">
              </div>
              <div class="field">
                <label for="telefone_celular">Telefone Celular</label>
                <input type="text" id="telefone_celular" name="contato[telefone_celular]"
                  placeholder="(00) 0 0000-0000"
                  value="<?php echo htmlspecialchars($contato['telefone_celular'] ?? ''); ?>">
              </div>
              <div class="field">
                <label for="whatsapp">WhatsApp</label>
                <input type="text" id="whatsapp" name="contato[whatsapp]"
                  placeholder="(00) 0 0000-0000"
                  value="<?php echo htmlspecialchars($contato['whatsapp'] ?? ''); ?>">
              </div>
              <div class="field">
                <label for="email_financeiro">E-mail Financeiro</label>
                <input type="email" id="email_financeiro" name="contato[email_financeiro]"
                  placeholder="financeiro@empresa.com.br"
                  value="<?php echo htmlspecialchars($contato['email_financeiro'] ?? ''); ?>">
              </div>
              <div class="field">
                <label for="site">Site / URL</label>
                <input type="url" id="site" name="contato[site]"
                  placeholder="https://www.empresa.com.br"
                  value="<?php echo htmlspecialchars($contato['site'] ?? ''); ?>">
              </div>
            </div>

            <!-- Representante -->
            <div class="sub-block">
              <div class="sub-block-title">Representante Comercial</div>
              <div class="g2">
                <div class="field">
                  <label>Nome do Representante</label>
                  <input type="text" name="contato[representante_nome]"
                    placeholder="Nome completo"
                    value="<?php echo htmlspecialchars($contato['representante_nome'] ?? ''); ?>">
                </div>
                <div class="field">
                  <label>Cargo</label>
                  <input type="text" name="contato[representante_cargo]"
                    placeholder="Ex: Diretor Comercial"
                    value="<?php echo htmlspecialchars($contato['representante_cargo'] ?? ''); ?>">
                </div>
                <div class="field">
                  <label>Telefone</label>
                  <input type="text" name="contato[representante_telefone]"
                    placeholder="(00) 0 0000-0000"
                    value="<?php echo htmlspecialchars($contato['representante_telefone'] ?? ''); ?>">
                </div>
                <div class="field">
                  <label>E-mail</label>
                  <input type="email" name="contato[representante_email]"
                    placeholder="representante@empresa.com.br"
                    value="<?php echo htmlspecialchars($contato['representante_email'] ?? ''); ?>">
                </div>
              </div>
            </div>

          </div>
        </section>

        <!-- ── 4. Dados Financeiros ── -->
        <section class="section-card" id="sec-financeiro">
          <div class="section-header">
            <div class="section-icon" style="background:var(--c-warn-l)">
              <svg width="14" height="14" viewBox="0 0 16 16" fill="none"><rect x="1.5" y="4" width="13" height="9" rx="1.5" stroke="#BA7517" stroke-width="1.4"/><path d="M1.5 8h13M5.5 4V3a1.5 1.5 0 013 0v1" stroke="#BA7517" stroke-width="1.4" stroke-linecap="round"/></svg>
            </div>
            <div class="section-header-text">
              <h3>4. Dados Financeiros</h3>
              <p>Conta bancária, PIX e condições de pagamento</p>
            </div>
          </div>
          <div class="section-body">

            <div class="g3 gap-bot">
              <div class="field">
                <label for="banco">Banco</label>
                <input type="text" id="banco" name="dados_financeiros[banco]"
                  placeholder="Nome ou código do banco"
                  value="<?php echo htmlspecialchars($dados_financeiros['banco'] ?? ''); ?>">
              </div>
              <div class="field">
                <label for="agencia">Agência</label>
                <input type="text" id="agencia" name="dados_financeiros[agencia]"
                  placeholder="0000-0"
                  value="<?php echo htmlspecialchars($dados_financeiros['agencia'] ?? ''); ?>">
              </div>
              <div class="field">
                <label for="conta">Conta Corrente</label>
                <input type="text" id="conta" name="dados_financeiros[conta]"
                  placeholder="00000-0"
                  value="<?php echo htmlspecialchars($dados_financeiros['conta'] ?? ''); ?>">
              </div>
              <div class="field">
                <label for="tipo_conta">Tipo de Conta</label>
                <select id="tipo_conta" name="dados_financeiros[tipo_conta]">
                  <?php foreach (['Corrente','Poupança','PJ' => 'Pessoa Jurídica'] as $val => $lbl):
                    $v = is_string($val) ? $val : $lbl;
                  ?>
                    <option value="<?php echo $v; ?>"
                      <?php echo (($dados_financeiros['tipo_conta'] ?? '') === $v) ? 'selected' : ''; ?>>
                      <?php echo $lbl; ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="field">
                <label for="condicoes_pagamento">Condições de Pagamento</label>
                <input type="text" id="condicoes_pagamento" name="dados_financeiros[condicoes_pagamento]"
                  placeholder="Ex: 30/60/90 dias"
                  value="<?php echo htmlspecialchars($dados_financeiros['condicoes_pagamento'] ?? ''); ?>">
              </div>
              <div class="field">
                <label for="limite_credito">Limite de Crédito (R$)</label>
                <input type="text" id="limite_credito" name="dados_financeiros[limite_credito]"
                  placeholder="0,00"
                  value="<?php echo htmlspecialchars($dados_financeiros['limite_credito'] ?? ''); ?>">
              </div>
            </div>

            <!-- PIX -->
            <div class="sub-block">
              <div class="sub-block-title">Chave PIX</div>
              <div class="g23">
                <div class="field">
                  <label for="chave_pix">Chave PIX</label>
                  <input type="text" id="chave_pix" name="dados_financeiros[chave_pix]"
                    placeholder="CPF, CNPJ, e-mail, telefone ou chave aleatória"
                    value="<?php echo htmlspecialchars($dados_financeiros['chave_pix'] ?? ''); ?>">
                </div>
                <div class="field">
                  <label for="tipo_chave_pix">Tipo de Chave</label>
                  <select id="tipo_chave_pix" name="dados_financeiros[tipo_chave_pix]">
                    <?php foreach (['CPF','CNPJ','Email' => 'E-mail','Telefone','Aleatoria' => 'Aleatória'] as $val => $lbl):
                      $v = is_string($val) ? $val : $lbl;
                    ?>
                      <option value="<?php echo $v; ?>"
                        <?php echo (($dados_financeiros['tipo_chave_pix'] ?? '') === $v) ? 'selected' : ''; ?>>
                        <?php echo $lbl; ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>
            </div>

          </div>
        </section>

        <!-- ── 5. Documentação ── -->
        <section class="section-card" id="sec-docs">
          <div class="section-header">
            <div class="section-icon" style="background:var(--c-danger-l)" >
              <svg width="14" height="14" viewBox="0 0 16 16" fill="none"><path d="M4 2h6l3 3v9a1 1 0 01-1 1H4a1 1 0 01-1-1V3a1 1 0 011-1z" stroke="#993556" stroke-width="1.4"/><path d="M10 2v3h3" stroke="#993556" stroke-width="1.4" stroke-linejoin="round"/><line x1="5" y1="8" x2="11" y2="8" stroke="#993556" stroke-width="1.2" stroke-linecap="round"/><line x1="5" y1="11" x2="9" y2="11" stroke="#993556" stroke-width="1.2" stroke-linecap="round"/></svg>
            </div>
            <div class="section-header-text">
              <h3>5. Documentação</h3>
              <p>Anexe os documentos legais e certidões do fornecedor</p>
            </div>
          </div>
          <div class="section-body">
            <div class="g2">
              <div>
                <label class="doc-label">Contrato Social (PDF)</label>
                <label class="upload-zone" for="doc_contrato_social">
                  <span class="upload-ico">📄</span>
                  <p><strong>Clique para selecionar</strong> ou arraste o arquivo aqui</p>
                  <p style="margin-top:2px;font-size:11px">Apenas PDF — máx. 10 MB</p>
                </label>
                <input type="file" id="doc_contrato_social" name="documentacao[contrato_social]"
                  accept=".pdf" style="display:none">
              </div>
              <div>
                <label class="doc-label">Certidões Negativas</label>
                <label class="upload-zone" for="doc_certidoes">
                  <span class="upload-ico">📋</span>
                  <p><strong>Clique para selecionar</strong> ou arraste os arquivos</p>
                  <p style="margin-top:2px;font-size:11px">PDF ou ZIP — múltiplos arquivos</p>
                </label>
                <input type="file" id="doc_certidoes" name="documentacao[certidoes]"
                  accept=".pdf,.zip" multiple style="display:none">
              </div>
            </div>
          </div>
        </section>

        <!-- ── 6. Informações Comerciais ── -->
        <section class="section-card" id="sec-comercial">
          <div class="section-header">
            <div class="section-icon" style="background:var(--c-success-l)">
              <svg width="14" height="14" viewBox="0 0 16 16" fill="none"><path d="M2 13l4-4 3 3 5-7" stroke="#1D9E75" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </div>
            <div class="section-header-text">
              <h3>6. Informações Comerciais</h3>
              <p>Produtos, serviços fornecidos e observações internas</p>
            </div>
          </div>
          <div class="section-body">
            <div class="field gap-bot">
              <label for="produtos_servicos">Produtos / Serviços Fornecidos</label>
              <textarea id="produtos_servicos" name="info_comerciais[produtos_servicos]"
                placeholder="Descreva os produtos ou serviços fornecidos..."><?php echo htmlspecialchars($info_comerciais['produtos_servicos'] ?? ''); ?></textarea>
            </div>
            <div class="field">
              <label for="observacoes_internas">Observações Internas</label>
              <textarea id="observacoes_internas" name="info_comerciais[observacoes_internas]"
                placeholder="Notas e observações de uso interno (não visíveis ao fornecedor)..."><?php echo htmlspecialchars($info_comerciais['observacoes_internas'] ?? ''); ?></textarea>
            </div>
          </div>
        </section>

        <!-- ── 7. Status ── -->
        <section class="section-card" id="sec-status">
          <div class="section-header">
            <div class="section-icon" style="background:var(--c-primary-l)">
              <svg width="14" height="14" viewBox="0 0 16 16" fill="none"><circle cx="8" cy="8" r="6" stroke="#185FA5" stroke-width="1.4"/><path d="M5.5 8.5l2 2 3-4" stroke="#185FA5" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </div>
            <div class="section-header-text">
              <h3>7. Status do Fornecedor</h3>
              <p>Situação atual e motivo de inativação (se aplicável)</p>
            </div>
          </div>
          <div class="section-body">

            <div class="field gap-bot">
              <label>Situação <span class="req">*</span></label>
              <div class="status-options" id="status-options-group">
                <?php
                $statusAtual = $fornecedor['status'] ?? 'Ativo';
                $statusList  = [
                  'Ativo'          => ['class' => 'sel-ativo',   'dot' => '#1D9E75'],
                  'Inativo'        => ['class' => 'sel-inativo', 'dot' => '#E24B4A'],
                  'Em Homologação' => ['class' => 'sel-hom',     'dot' => '#BA7517'],
                ];
                foreach ($statusList as $sv => $sc):
                ?>
                  <label class="status-opt <?php echo ($statusAtual === $sv) ? $sc['class'] : ''; ?>"
                         data-class="<?php echo $sc['class']; ?>">
                    <input type="radio" name="status" value="<?php echo $sv; ?>" required
                      <?php echo ($statusAtual === $sv) ? 'checked' : ''; ?>>
                    <div class="status-dot" style="background:<?php echo ($statusAtual === $sv) ? $sc['dot'] : 'var(--txt3)'; ?>"></div>
                    <div class="status-label"><?php echo $sv; ?></div>
                  </label>
                <?php endforeach; ?>
              </div>
            </div>

            <!-- Campos de inativação -->
            <div class="g2 inative-group <?php echo ($statusAtual === 'Inativo') ? 'visible' : ''; ?>" id="inativacao-fields">
              <div class="field">
                <label for="motivo_inativacao">Motivo da Inativação</label>
                <input type="text" id="motivo_inativacao" name="motivo_inativacao"
                  placeholder="Descreva o motivo..."
                  value="<?php echo htmlspecialchars($fornecedor['motivo_inativacao'] ?? ''); ?>">
              </div>
              <div class="field">
                <label for="data_inativacao">Data da Inativação</label>
                <input type="date" id="data_inativacao" name="data_inativacao"
                  value="<?php echo htmlspecialchars($fornecedor['data_inativacao'] ?? ''); ?>">
              </div>
            </div>

          </div>
        </section>

      </div><!-- /form-body -->

      <!-- ── Footer ── -->
      <footer class="footer-actions">
        <div class="progress-area">
          <div class="prog-info">Preencha todos os campos obrigatórios antes de salvar</div>
          <div class="progress-bar">
            <div class="progress-fill" id="progress-fill" style="width:0%"></div>
          </div>
        </div>
        <div class="footer-btns">
          <a href="<?php echo BASE_URL; ?>/fornecedores" class="btn btn-ghost">Cancelar</a>
          <button type="submit" class="btn btn-primary">
            <?php echo $isEdit ? '✓ Atualizar Fornecedor' : '✓ Salvar Fornecedor'; ?>
          </button>
        </div>
      </footer>

    </form>
  </div>
</div>

<script>
(function () {
  /* ── Sidebar: highlight active section on scroll ── */
  const sections = document.querySelectorAll('.section-card');
  const stepLinks = document.querySelectorAll('.step-item');

  const observer = new IntersectionObserver((entries) => {
    entries.forEach(e => {
      if (e.isIntersecting) {
        const id = e.target.id;
        stepLinks.forEach(l => {
          const isActive = l.getAttribute('href') === '#' + id;
          l.classList.toggle('active', isActive);
          if (isActive) {
            // Garante que o item ativo esteja visível no scroll horizontal
            l.parentElement.scrollTo({ left: l.offsetLeft - 40, behavior: 'smooth' });
          }
        });
      }
    });
  }, { root: null, threshold: 0.1, rootMargin: '-20% 0px -60% 0px' });
  sections.forEach(s => observer.observe(s));

  /* ── Mascara CPF/CNPJ Helpers ── */
  const cnpjInput = document.getElementById('cnpj');
  const getTipoPessoa = () => document.querySelector('input[name="tipo_pessoa"]:checked')?.value || 'Juridica';

  function formatDocument(value, type) {
    const digits = value.replace(/\D/g, '');
    if (type === 'Juridica') {
      return digits
        .replace(/^(\d{2})(\d)/, '$1.$2')
        .replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3')
        .replace(/\.(\d{3})(\d)/, '.$1/$2')
        .replace(/(\d{4})(\d)/, '$1-$2')
        .substring(0, 18);
    } else {
      return digits
        .replace(/^(\d{3})(\d)/, '$1.$2')
        .replace(/^(\d{3})\.(\d{3})(\d)/, '$1.$2.$3')
        .replace(/(\d{3})(\d)/, '$1-$2')
        .substring(0, 14);
    }
  }

  /* ── Tipo Pessoa: toggle label CNPJ/CPF ── */
  const labelCnpj = document.getElementById('label-cnpj');
  document.querySelectorAll('input[name="tipo_pessoa"]').forEach(r => {
    r.addEventListener('change', () => {
      labelCnpj.innerHTML = r.value === 'Juridica'
        ? 'CNPJ <span class="req">*</span>'
        : 'CPF <span class="req">*</span>';
      cnpjInput.placeholder = r.value === 'Juridica' ? '00.000.000/0000-00' : '000.000.000-00';
      cnpjInput.value = formatDocument(cnpjInput.value, r.value);
    });
  });

  /* ── IE Isento: toggle inscricao_estadual ── */
  const ieIsento = document.getElementById('ie_isento');
  const ieInput  = document.getElementById('inscricao_estadual');
  ieIsento && ieIsento.addEventListener('change', () => {
    ieInput.disabled = ieIsento.checked;
    if (ieIsento.checked) ieInput.value = '';
  });

  /* ── Status: show/hide inativacao fields + style ── */
  const statusGroup    = document.getElementById('status-options-group');
  const inativFields   = document.getElementById('inativacao-fields');
  const statusDotMap   = { 'sel-ativo': '#1D9E75', 'sel-inativo': '#E24B4A', 'sel-hom': '#BA7517' };

  function applyStatus(optEl) {
    document.querySelectorAll('.status-opt').forEach(o => {
      o.classList.remove('sel-ativo', 'sel-inativo', 'sel-hom');
      o.querySelector('.status-dot').style.background = 'var(--txt3)';
    });
    const cls = optEl.dataset.class;
    optEl.classList.add(cls);
    optEl.querySelector('.status-dot').style.background = statusDotMap[cls] || 'var(--txt3)';
    inativFields && inativFields.classList.toggle('visible', cls === 'sel-inativo');
  }

  statusGroup && statusGroup.addEventListener('change', e => {
    const opt = e.target.closest('.status-opt');
    if (opt) applyStatus(opt);
  });

  /* ── Evento de input para máscara em tempo real ── */
  cnpjInput?.addEventListener('input', (e) => {
    e.target.value = formatDocument(e.target.value, getTipoPessoa());
  });

  // Aplica máscara inicial se houver valor (edição)
  if (cnpjInput && cnpjInput.value) cnpjInput.value = formatDocument(cnpjInput.value, getTipoPessoa());

  /* ── CNPJ Busca ── */
  const cnpjBtn     = document.getElementById('buscar-cnpj-btn');
  const cnpjIcon    = document.getElementById('cnpj-search-icon');
  const cnpjSpinner = document.getElementById('cnpj-loading-spinner');

  cnpjBtn && cnpjBtn.addEventListener('click', async () => {
    const raw = cnpjInput.value.replace(/\D/g, '');
    if (raw.length !== 14) { alert('Informe um CNPJ válido com 14 dígitos.'); return; }
    cnpjIcon.style.display    = 'none';
    cnpjSpinner.style.display = 'block';
    cnpjBtn.disabled          = true;
    try {
      const res  = await fetch(`https://brasilapi.com.br/api/cnpj/v1/${raw}`);
      const data = await res.json();
      if (data.razao_social) {
        document.getElementById('nome').value          = data.razao_social;
        document.getElementById('nome_fantasia').value = data.nome_fantasia || '';
        document.getElementById('logradouro').value    = data.logradouro   || '';
        document.getElementById('numero').value        = data.numero        || '';
        document.getElementById('complemento').value   = data.complemento  || '';
        document.getElementById('bairro').value        = data.bairro        || '';
        document.getElementById('cidade').value        = data.municipio     || '';
        document.getElementById('uf').value            = data.uf            || '';
        document.getElementById('cep').value           = data.cep ? data.cep.replace(/(\d{5})(\d{3})/, "$1-$2") : '';
      }
    } catch (err) {
      alert('Não foi possível consultar o CNPJ. Tente novamente.');
    } finally {
      cnpjIcon.style.display    = 'block';
      cnpjSpinner.style.display = 'none';
      cnpjBtn.disabled          = false;
    }
  });

  /* ── CEP Busca ── */
  document.getElementById('buscar-cep-btn') && document.getElementById('buscar-cep-btn').addEventListener('click', async () => {
    const raw = document.getElementById('cep').value.replace(/\D/g, '');
    if (raw.length !== 8) { alert('Informe um CEP válido com 8 dígitos.'); return; }
    try {
      const res  = await fetch(`https://viacep.com.br/ws/${raw}/json/`);
      const data = await res.json();
      if (!data.erro) {
        document.getElementById('logradouro').value = data.logradouro || '';
        document.getElementById('bairro').value     = data.bairro     || '';
        document.getElementById('cidade').value     = data.localidade || '';
        document.getElementById('uf').value         = data.uf         || '';
      }
    } catch { alert('CEP não encontrado.'); }
  });

  /* ── Progress bar (quick estimate) ── */
  const requiredFields = document.querySelectorAll('[required]');
  const fill           = document.getElementById('progress-fill');
  function updateProgress() {
    const total   = requiredFields.length;
    const filled  = Array.from(requiredFields).filter(f => {
      if (f.type === 'radio') return document.querySelector(`[name="${f.name}"]:checked`);
      return f.value.trim() !== '';
    }).length;
    if (fill) fill.style.width = Math.round((filled / total) * 100) + '%';
  }
  document.getElementById('fornecedor-form').addEventListener('input', updateProgress);
  document.getElementById('fornecedor-form').addEventListener('change', updateProgress);
  updateProgress();

  /* ── Upload labels: show selected filename ── */
  ['doc_contrato_social','doc_certidoes'].forEach(id => {
    const inp = document.getElementById(id);
    if (!inp) return;
    inp.addEventListener('change', function () {
      const zone = this.previousElementSibling;
      if (this.files.length > 0) {
        const names = Array.from(this.files).map(f => f.name).join(', ');
        zone.querySelector('p').innerHTML = `<strong>${names}</strong>`;
        zone.style.borderColor  = 'var(--c-success)';
        zone.style.background   = 'var(--c-success-l)';
      }
    });
  });

})();
</script>