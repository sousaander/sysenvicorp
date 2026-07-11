<?php
$isEdit = isset($cliente) && !empty($cliente['id']);
$actionUrl = BASE_URL . '/clientes/salvar';

$enderecos = $isEdit && !empty($cliente['enderecos_json']) ? json_decode($cliente['enderecos_json'], true) : ['principal' => []];
$contatos  = $isEdit && !empty($cliente['contatos_json'])  ? json_decode($cliente['contatos_json'],  true) : ['principal' => [], 'responsavel' => []];
$financeiro = $isEdit && !empty($cliente['financeiro_json']) ? json_decode($cliente['financeiro_json'], true) : [];
$comercial  = $isEdit && !empty($cliente['comercial_json'])  ? json_decode($cliente['comercial_json'],  true) : [];

$enderecos['principal']   = $enderecos['principal']   ?? [];
$contatos['principal']    = $contatos['principal']    ?? [];
$contatos['responsavel']  = $contatos['responsavel']  ?? [];

$tipoCliente = isset($cliente['tipo_cliente']) ? $cliente['tipo_cliente'] : 'Juridica';
// Normaliza para o valor esperado nos inputs/JS
$tipoCliente = (strtolower($tipoCliente) === 'fisica') ? 'Fisica' : 'Juridica';
?>

<style>
  /* ── Reset & Base (Mesmo estilo dos fornecedores) ── */
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

  #form-cliente-view {
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
    scrollbar-width: none;
  }
  .steps-header::-webkit-scrollbar { display: none; }

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
    background: var(--card); border: 1px solid var(--border); border-radius: var(--rad-lg);
    padding: 14px 28px; display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 24px; box-shadow: var(--fin-shadow);
  }
  .top-bar-left { display: flex; align-items: center; gap: 12px; }
  .page-title { font-size: 15px; font-weight: 600; }
  .breadcrumb { font-size: 12px; color: var(--txt3); }

  /* ── Form Body ── */
  .form-body { flex: 1; }
  .section-card { background: var(--card); border: 1px solid var(--border); border-radius: var(--rad-lg); margin-bottom: 18px; width: 100%; overflow: hidden; }
  .section-header { padding: 14px 20px; border-bottom: 1px solid var(--border); display: flex; align-items: center; gap: 12px; background: var(--bg); }
  .section-icon { width: 30px; height: 30px; border-radius: 7px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
  .section-header-text h3 { font-size: 13.5px; font-weight: 600; color: var(--txt); }
  .section-header-text p  { font-size: 11.5px; color: var(--txt3); margin-top: 1px; }
  .section-body { padding: 20px; }

  /* ── Inputs ── */
  .field { display: flex; flex-direction: column; gap: 5px; margin-bottom: 16px; }
  .field > label { font-size: 12px; font-weight: 500; color: var(--txt2); }
  .field > label .req { color: var(--c-danger); margin-left: 2px; }
  input[type=text], input[type=email], input[type=date], select, textarea {
    width: 100%; height: 36px; padding: 0 10px; border: 1px solid var(--border2); border-radius: var(--rad); font-size: 13px; outline: none;
    background: var(--card); color: var(--txt);
  }
  textarea { height: 80px; padding: 8px 10px; }
  .input-addon { display: flex; }
  .input-addon input { border-radius: var(--rad) 0 0 var(--rad); border-right: none; }
  .addon-btn {
    height: 36px; padding: 0 12px; background: var(--c-primary-l); border: 1px solid var(--border2);
    border-left: none; border-radius: 0 var(--rad) var(--rad) 0; cursor: pointer; color: var(--c-primary); font-size: 12px; font-weight: 500;
  }

  /* ── Status selector ── */
  .status-options { display: flex; gap: 10px; }
  .status-opt { flex: 1; padding: 10px; border: 1.5px solid var(--border2); border-radius: var(--rad); cursor: pointer; text-align: center; }
  .status-opt.active { border-color: var(--c-primary); background: var(--c-primary-l); }
  .status-opt input { display: none; }
  .status-dot { width: 8px; height: 8px; border-radius: 50%; margin: 0 auto 6px; background: var(--txt3); }
  .status-label { font-size: 12px; color: var(--txt2); font-weight: 500; }
  
  /* Estilos específicos de status (igual fornecedores) */
  .status-opt.sel-ativo   { border-color: var(--c-success); background: var(--c-success-l); }
  .status-opt.sel-ativo   .status-dot { background: var(--c-success); }
  .status-opt.sel-ativo   .status-label { color: var(--c-success); }
  .status-opt.sel-inativo { border-color: var(--c-danger); background: var(--c-danger-l); }
  .status-opt.sel-inativo .status-dot { background: var(--c-danger); }
  .status-opt.sel-inativo .status-label { color: var(--c-danger); }
  .status-opt.sel-potencial { border-color: var(--c-primary); background: var(--c-primary-l); }
  .status-opt.sel-potencial .status-dot { background: var(--c-primary); }
  .status-opt.sel-potencial .status-label { color: var(--c-primary); }
  .status-opt.sel-negoc     { border-color: var(--c-warn); background: var(--c-warn-l); }
  .status-opt.sel-negoc     .status-dot { background: var(--c-warn); }
  .status-opt.sel-negoc     .status-label { color: var(--c-warn); }

  /* ── Footer Actions ── */
  .footer-actions {
    background: var(--card); border: 1px solid var(--border); border-radius: var(--rad-lg);
    padding: 12px 24px; display: flex; align-items: center; justify-content: space-between;
    position: sticky; bottom: 10px; z-index: 40; box-shadow: 0 -4px 6px -1px rgb(0 0 0 / 0.1);
    margin-top: 10px;
  }
  .progress-area .prog-info { font-size: 12px; color: var(--txt3); }
  .progress-bar { width: 160px; height: 4px; background: var(--border); border-radius: 2px; margin-top: 2px; }
  .progress-fill { height: 4px; background: var(--c-primary); border-radius: 2px; transition: width .3s; }
  .footer-btns { display: flex; gap: 8px; }

  /* Grid Helpers */
  .g2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
  .g3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; }
  .g23 { display: grid; grid-template-columns: 2fr 1fr; gap: 16px; }
  .gap-bot { margin-bottom: 20px; }

  /* ── Visibility Helpers ── */
  /* Devem vir após os seletores de layout (g2, g3) para que o 'none' tenha prioridade */
  .inative-group { display: none; }
  .inative-group.visible { display: grid; }

  .radio-group { display: flex; gap: 10px; }
  .radio-card {
    display: flex; align-items: center; gap: 8px; padding: 8px 16px; border: 1px solid var(--border2); border-radius: var(--rad);
    cursor: pointer; font-size: 13px; background: var(--card); color: var(--txt2);
  }
  .radio-card:has(input:checked) { border-color: var(--c-primary); background: var(--c-primary-l); color: var(--c-primary); font-weight: 600; }
  .radio-card input { display: none; }

  .btn {
    height: 36px; padding: 0 16px; border-radius: var(--rad); font-size: 13px; font-weight: 500; cursor: pointer;
    display: inline-flex; align-items: center; gap: 6px; border: 1px solid transparent; text-decoration: none;
    transition: all .15s;
  }
  .btn-primary { background: var(--c-primary); color: #fff; }
  .btn-ghost { background: transparent; border-color: var(--border2); color: var(--txt2); }
  .btn-ghost:hover { background: var(--db-surface2); }
  .btn-outline-danger { border-color: var(--c-danger); color: var(--c-danger); background: transparent; }
  .btn-outline-danger:hover { background: var(--c-danger-l); }

</style>

<?php
// Variáveis de apoio para a aba de Status
$currentStatus = $cliente['status'] ?? 'Potencial';
$statusList = [
    'Potencial'      => ['class' => 'sel-potencial', 'dot' => 'var(--c-primary)'],
    'Em negociação'  => ['class' => 'sel-negoc',     'dot' => 'var(--c-warn)'],
    'Ativo'          => ['class' => 'sel-ativo',     'dot' => 'var(--c-success)'],
    'Inativo'        => ['class' => 'sel-inativo',   'dot' => 'var(--c-danger)']
];
?>

<div id="form-cliente-view">
  <div class="form-content-area">

      <header class="top-bar">
        <div class="top-bar-left">
          <div class="page-title">
            <?php echo $isEdit ? 'Editar Cadastro' : 'Novo Cadastro de Cliente'; ?>
          </div>
          <div class="breadcrumb">
            <a href="<?php echo BASE_URL; ?>/clientes" style="color:inherit;text-decoration:none">Clientes</a> &rsaquo; <b>Ficha Cadastral</b>
          </div>
        </div>
        <div class="top-bar-actions">
           <a href="<?php echo BASE_URL; ?>/clientes" class="btn btn-outline-danger">Sair sem salvar</a>
        </div>
      </header>

      <!-- Navegação Horizontal de Etapas -->
      <nav class="steps-header" aria-label="Etapas do cadastro">
        <?php
        $steps = [
          'tab-basicos'  => ['label' => 'Identificação', 'icon' => 'bx bx-id-card'],
          'tab-endereco' => ['label' => 'Endereço',      'icon' => 'bx bx-map-alt'],
          'tab-contatos' => ['label' => 'Contatos',      'icon' => 'bx bx-phone-call'],
          'tab-financeiro'=> ['label' => 'Financeiro',    'icon' => 'bx bx-wallet'],
          'tab-comercial' => ['label' => 'Comercial',     'icon' => 'bx bx-briefcase-alt-2'],
          'tab-status'    => ['label' => 'Status',        'icon' => 'bx bx-check-shield'],
        ];
        $stepIdx = 1;
        foreach ($steps as $id => $s):
        ?>
          <a class="step-item <?php echo $id === 'tab-basicos' ? 'active' : ''; ?>" href="#<?php echo $id; ?>" data-step="<?php echo $stepIdx; ?>">
            <div class="step-num"><?php echo $stepIdx++; ?></div>
            <div class="step-label"><?php echo $s['label']; ?></div>
          </a>
        <?php endforeach; ?>
      </nav>

      <form id="cliente-form" action="<?php echo $actionUrl; ?>" method="POST" novalidate>
        <?php if (!empty($csrf_token)) : ?><input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>"><?php endif; ?>
        <?php if (!empty($cliente['id'])) : ?><input type="hidden" name="id" value="<?php echo htmlspecialchars($cliente['id']); ?>"><?php endif; ?>

        <div class="form-body">
          
          <!-- ── 1. Identificação ── -->
          <section class="section-card" id="tab-basicos">
            <div class="section-header">
              <div class="section-icon" style="background:var(--c-primary-l)"><i class='bx bx-user text-blue-600'></i></div>
              <div class="section-header-text">
                <h3>1. Identificação do Cliente</h3>
                <p>Dados primordiais para o registro fiscal e comercial.</p>
              </div>
            </div>
            <div class="section-body">
              <div class="field">
                <label>Tipo de Pessoa <span class="req">*</span></label>
                <div class="radio-group">
                  <label class="radio-card">
                    <input type="radio" name="tipo_cliente" value="Juridica" <?php echo ($tipoCliente === 'Juridica') ? 'checked' : ''; ?>>
                    🏢 Pessoa Jurídica (CNPJ)
                  </label>
                  <label class="radio-card">
                    <input type="radio" name="tipo_cliente" value="Fisica" <?php echo ($tipoCliente === 'Fisica') ? 'checked' : ''; ?>>
                    👤 Pessoa Física (CPF)
                  </label>
                </div>
              </div>

              <div class="g23">
                <div class="field">
                  <label id="label-nome">Nome / Razão Social <span class="req">*</span></label>
                  <input type="text" name="nome" id="nome" required placeholder="Nome completo ou razão social" value="<?php echo htmlspecialchars($cliente['nome'] ?? ''); ?>">
                </div>
                <div class="field">
                  <label id="label-documento">CPF / CNPJ <span class="req">*</span></label>
                  <div class="input-addon">
                    <input type="text" id="cnpj_cpf" name="cnpj_cpf" required placeholder="00.000.000/0000-00" value="<?php echo htmlspecialchars($cliente['cnpj_cpf'] ?? ''); ?>">
                    <button type="button" id="buscar-cnpj-btn" class="addon-btn">Consultar</button>
                  </div>
                </div>
              </div>

              <div class="g3">
                <div class="field">
                  <label>Nome Fantasia</label>
                  <input type="text" name="nome_fantasia" value="<?php echo htmlspecialchars($cliente['nome_fantasia'] ?? ''); ?>">
                </div>
                <div class="field">
                  <label>Sigla</label>
                  <input type="text" name="sigla" maxlength="5" class="uppercase" value="<?php echo htmlspecialchars($cliente['sigla'] ?? ''); ?>">
                </div>
                <div class="field">
                  <label>Origem</label>
                  <input type="text" name="origem_cliente" value="<?php echo htmlspecialchars($cliente['origem_cliente'] ?? ''); ?>">
                </div>
              </div>
            </div>
          </section>

          <!-- ── 2. Endereço ── -->
          <section class="section-card" id="tab-endereco">
            <div class="section-header">
              <div class="section-icon" style="background:var(--c-success-l)"><i class='bx bx-map text-green-600'></i></div>
              <div class="section-header-text">
                <h3>2. Localização</h3>
                <p>Endereço principal para faturamento e entregas.</p>
              </div>
            </div>
            <div class="section-body">
              <div class="g23">
                <div class="field">
                  <label>CEP <span class="req">*</span></label>
                  <div class="input-addon">
                    <input type="text" id="cep" name="enderecos[principal][cep]" required placeholder="00000-000" value="<?php echo htmlspecialchars($enderecos['principal']['cep'] ?? ''); ?>">
                    <button type="button" id="buscar-cep-btn" class="addon-btn">Buscar</button>
                  </div>
                </div>
                <div class="field">
                  <label>Logradouro <span class="req">*</span></label>
                  <input type="text" id="logradouro" name="enderecos[principal][logradouro]" required value="<?php echo htmlspecialchars($enderecos['principal']['logradouro'] ?? ''); ?>">
                </div>
              </div>
              <div class="g2">
                 <div class="field"><label>Número</label><input type="text" name="enderecos[principal][numero]" value="<?php echo htmlspecialchars($enderecos['principal']['numero'] ?? ''); ?>"></div>
                 <div class="field"><label>Complemento</label><input type="text" name="enderecos[principal][complemento]" value="<?php echo htmlspecialchars($enderecos['principal']['complemento'] ?? ''); ?>"></div>
              </div>
              <div class="g3">
                 <div class="field"><label>Bairro</label><input type="text" id="bairro" name="enderecos[principal][bairro]" value="<?php echo htmlspecialchars($enderecos['principal']['bairro'] ?? ''); ?>"></div>
                 <div class="field"><label>Município</label><input type="text" id="cidade" name="enderecos[principal][cidade]" value="<?php echo htmlspecialchars($enderecos['principal']['cidade'] ?? ''); ?>"></div>
                 <div class="field"><label>UF</label><input type="text" id="estado" name="enderecos[principal][estado]" maxlength="2" value="<?php echo htmlspecialchars($enderecos['principal']['estado'] ?? ''); ?>"></div>
              </div>
            </div>
          </section>

          <!-- ── 3. Contatos ── -->
          <section class="section-card" id="tab-contatos">
            <div class="section-header">
              <div class="section-icon" style="background:var(--c-primary-l)"><i class='bx bx-phone text-blue-600'></i></div>
              <div class="section-header-text">
                <h3>3. Comunicação</h3>
                <p>Telefones e e-mails de contato.</p>
              </div>
            </div>
            <div class="section-body">
              <div class="g2">
                <div class="field">
                  <label>E-mail Principal <span class="req">*</span></label>
                  <input type="email" name="contatos[principal][email]" required value="<?php echo htmlspecialchars($contatos['principal']['email'] ?? ''); ?>">
                </div>
                <div class="field">
                  <label>Telefone Principal <span class="req">*</span></label>
                  <input type="text" name="contatos[principal][telefone]" required value="<?php echo htmlspecialchars($contatos['principal']['telefone'] ?? ''); ?>">
                </div>
              </div>

              <div class="g3">
                <div class="field">
                  <label>WhatsApp</label>
                  <input type="text" name="contatos[principal][whatsapp]" class="phone-mask" value="<?php echo htmlspecialchars($contatos['principal']['whatsapp'] ?? ''); ?>">
                </div>
                <div class="field">
                  <label>Telefone Secundário</label>
                  <input type="text" name="contatos[principal][telefone_secundario]" class="phone-mask" value="<?php echo htmlspecialchars($contatos['principal']['telefone_secundario'] ?? ''); ?>">
                </div>
                <div class="field">
                  <label>E-mail Financeiro</label>
                  <input type="email" name="contatos[principal][email_financeiro]" value="<?php echo htmlspecialchars($contatos['principal']['email_financeiro'] ?? ''); ?>">
                </div>
              </div>

              <div class="mt-4 pt-4 border-t border-gray-100">
                <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4">Contato Responsável (Focal)</h4>
                <div class="g2">
                  <div class="field">
                    <label>Nome do Responsável</label>
                    <input type="text" name="contatos[responsavel][nome]" value="<?php echo htmlspecialchars($contatos['responsavel']['nome'] ?? ''); ?>" placeholder="Nome do contato principal">
                  </div>
                  <div class="field">
                    <label>Cargo</label>
                    <input type="text" name="contatos[responsavel][cargo]" value="<?php echo htmlspecialchars($contatos['responsavel']['cargo'] ?? ''); ?>" placeholder="Cargo na empresa">
                  </div>
                </div>
                <div class="g2">
                  <div class="field">
                    <label>E-mail do Responsável</label>
                    <input type="email" name="contatos[responsavel][email]" value="<?php echo htmlspecialchars($contatos['responsavel']['email'] ?? ''); ?>">
                  </div>
                  <div class="field">
                    <label>Telefone do Responsável</label>
                    <input type="text" name="contatos[responsavel][telefone]" class="phone-mask" value="<?php echo htmlspecialchars($contatos['responsavel']['telefone'] ?? ''); ?>">
                  </div>
                </div>
              </div>
            </div>
          </section>

          <!-- ── 4. Financeiro ── -->
          <section class="section-card" id="tab-financeiro">
            <div class="section-header">
              <div class="section-icon" style="background:var(--c-warn-l)"><i class='bx bx-wallet text-orange-600'></i></div>
              <div class="section-header-text">
                <h3>4. Dados Financeiros</h3>
                <p>Condições de crédito e faturamento.</p>
              </div>
            </div>
            <div class="section-body">
              <div class="g3">
                <div class="field">
                  <label>Limite de Crédito</label>
                  <input type="text" name="financeiro[limite_credito]" value="<?php echo htmlspecialchars($financeiro['limite_credito'] ?? ''); ?>">
                </div>
                <div class="field">
                  <label>Classificação</label>
                  <select name="classificacao">
                    <option value="Bronze" <?php echo (($cliente['classificacao'] ?? '') === 'Bronze') ? 'selected' : ''; ?>>🥉 Bronze</option>
                    <option value="Prata" <?php echo (($cliente['classificacao'] ?? '') === 'Prata') ? 'selected' : ''; ?>>🥈 Prata</option>
                    <option value="Ouro" <?php echo (($cliente['classificacao'] ?? '') === 'Ouro') ? 'selected' : ''; ?>>🥇 Ouro</option>
                    <option value="Premium" <?php echo (($cliente['classificacao'] ?? '') === 'Premium') ? 'selected' : ''; ?>>💎 Premium</option>
                  </select>
                </div>
              </div>
            </div>
          </section>

          <!-- ── 5. Comercial ── -->
          <section class="section-card" id="tab-comercial">
            <div class="section-header">
              <div class="section-icon" style="background:var(--c-success-l)"><i class='bx bx-briefcase text-green-600'></i></div>
              <div class="section-header-text">
                <h3>5. Comercial</h3>
                <p>Segmentação e interesses do cliente.</p>
              </div>
            </div>
            <div class="section-body">
              <div class="g2 gap-bot">
                <div class="field">
                  <label>Representante Responsável</label>
                  <input type="text" name="comercial[representante_comercial]" value="<?php echo htmlspecialchars($comercial['representante_comercial'] ?? ''); ?>">
                </div>
                <div class="field">
                  <label>Região de Atuação</label>
                  <input type="text" name="comercial[regiao_atuacao]" value="<?php echo htmlspecialchars($comercial['regiao_atuacao'] ?? ''); ?>">
                </div>
              </div>
              <div class="field">
                <label>Produtos / Serviços de Interesse</label>
                <textarea name="comercial[produtos_servicos_interesse]"><?php echo htmlspecialchars($comercial['produtos_servicos_interesse'] ?? ''); ?></textarea>
              </div>
            </div>
          </section>

          <!-- ── 6. Status ── -->
          <section class="section-card" id="tab-status">
            <div class="section-header">
              <div class="section-icon" style="background:var(--c-primary-l)"><i class='bx bx-check-shield text-blue-600'></i></div>
              <div class="section-header-text">
                <h3>6. Situação do Cadastro</h3>
                <p>Defina o status atual no funil de vendas.</p>
              </div>
            </div>
            <div class="section-body">
              <div class="status-options gap-bot">
                <?php foreach ($statusList as $label => $opt): ?>
                  <label class="status-opt <?php echo ($currentStatus === $label) ? $opt['class'] : ''; ?>" data-class="<?php echo $opt['class']; ?>">
                    <input type="radio" name="status" value="<?php echo $label; ?>" <?php echo ($currentStatus === $label) ? 'checked' : ''; ?>>
                    <div class="status-dot"></div>
                    <div class="status-label"><?php echo $label; ?></div>
                  </label>
                <?php endforeach; ?>
              </div>

              <div class="g2 inative-group <?php echo ($currentStatus === 'Inativo') ? 'visible' : ''; ?>" id="inativacao-fields">
                <div class="field">
                  <label>Motivo da Inativação</label>
                  <input type="text" name="motivo_inativacao" value="<?php echo htmlspecialchars($cliente['motivo_inativacao'] ?? ''); ?>">
                </div>
                <div class="field">
                  <label>Data da Inativação</label>
                  <input type="date" name="data_inativacao" value="<?php echo $cliente['data_inativacao'] ?? ''; ?>">
                </div>
              </div>
            </div>
          </section>

        </div><!-- /form-body -->

        <!-- ── Footer ── -->
        <footer class="footer-actions">
          <div class="progress-area">
            <div class="prog-info" id="form-msg">Preencha os campos obrigatórios marcados com *</div>
            <div class="progress-bar">
              <div class="progress-fill" id="progress-fill" style="width:0%"></div>
            </div>
          </div>
          <div class="footer-btns">
            <button type="submit" id="btn-salvar" class="btn btn-primary">
              <?php echo $isEdit ? '✓ Atualizar Cadastro' : '✓ Finalizar Cadastro'; ?>
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
          if (l.getAttribute('href') === '#' + id) {
            l.classList.add('active');
            l.parentElement.scrollTo({ left: l.offsetLeft - 40, behavior: 'smooth' });
          } else {
            l.classList.remove('active');
          }
        });
      }
    });
  }, { root: null, threshold: 0.1, rootMargin: '-20% 0px -60% 0px' });
  sections.forEach(s => observer.observe(s));

  /* ── Smooth Scroll on Sidebar Click ── */
  stepLinks.forEach(link => {
    link.addEventListener('click', (e) => {
      e.preventDefault();
      // Destaque imediato ao clicar
      stepLinks.forEach(l => l.classList.remove('active'));
      link.classList.add('active');

      const id = link.getAttribute('href').substring(1);
      const target = document.getElementById(id);
      if (target) {
        target.scrollIntoView({ behavior: 'smooth' });
        // Fecha sidebar no mobile após clique
        if (window.innerWidth <= 768) {
          sidebar.classList.remove('open');
          overlay.classList.remove('visible');
        }
      }
    });
  });

  /* ── Status Toggle ── */
  const statusOpts = document.querySelectorAll('.status-opt');
  statusOpts.forEach(opt => {
    opt.addEventListener('click', function() {
      // Remove o destaque visual de todas as opções
      statusOpts.forEach(o => o.classList.remove('sel-ativo', 'sel-inativo', 'sel-potencial', 'sel-negoc', 'active'));
      
      const cls = this.dataset.class;
      const radio = this.querySelector('input');
      this.classList.add(cls);
      document.getElementById('inativacao-fields').classList.toggle('visible', radio.value === 'Inativo');
    });
  });

  /* ── Máscara de CPF/CNPJ ── */
  function aplicarMascara(val, tipo) {
    val = val.replace(/\D/g, ''); // Remove tudo que não é dígito
    if (tipo === 'Fisica') {
      val = val.substring(0, 11);
      val = val.replace(/(\d{3})(\d)/, '$1.$2');
      val = val.replace(/(\d{3})(\d)/, '$1.$2');
      val = val.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
    } else {
      val = val.substring(0, 14);
      val = val.replace(/^(\d{2})(\d)/, '$1.$2');
      val = val.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
      val = val.replace(/\.(\d{3})(\d)/, '.$1/$2');
      val = val.replace(/(\d{4})(\d)/, '$1-$2');
    }
    return val;
  }

  // --- Alternar PF/PJ ---
  function togglePfPjFields() {
    const tipo = document.querySelector('input[name="tipo_cliente"]:checked')?.value;
    const labelNome = document.getElementById('label-nome');
    const labelDoc = document.getElementById('label-documento');
    const btnConsultar = document.getElementById('buscar-cnpj-btn');
    const input = document.getElementById('cnpj_cpf');

    if(labelNome) {
      labelNome.innerHTML = tipo === 'Juridica' ? 'Razão Social <span class="req">*</span>' : 'Nome Completo <span class="req">*</span>';
    }
    if(labelDoc) {
      labelDoc.innerHTML = tipo === 'Juridica' ? 'CNPJ <span class="req">*</span>' : 'CPF <span class="req">*</span>';
    }
    if(btnConsultar) {
      // Oculta o botão de consulta para CPF, pois a API BrasilAPI/ReceitaWS é apenas para CNPJ
      btnConsultar.style.display = tipo === 'Juridica' ? 'block' : 'none';
      // Ajusta o arredondamento do input quando o botão some
      input.style.borderRadius = tipo === 'Juridica' ? 'var(--rad) 0 0 var(--rad)' : 'var(--rad)';
      input.style.borderRight = tipo === 'Juridica' ? 'none' : '1px solid var(--border2)';
    }
    if (input) {
      input.placeholder = tipo === 'Juridica' ? '00.000.000/0000-00' : '000.000.000-00';
      input.value = aplicarMascara(input.value, tipo);
    }
  }

  document.querySelectorAll('input[name="tipo_cliente"]').forEach(r => r.addEventListener('change', togglePfPjFields));
  document.getElementById('cnpj_cpf')?.addEventListener('input', function(e) {
    const tipo = document.querySelector('input[name="tipo_cliente"]:checked')?.value;
    e.target.value = aplicarMascara(e.target.value, tipo);
  });
  togglePfPjFields();

  // --- Busca de CEP ---
  document.getElementById('buscar-cep-btn')?.addEventListener('click', async () => {
    const cepInput = document.getElementById('cep');
    const cep = cepInput.value.replace(/\D/g, '');
    if (cep.length !== 8) { alert('CEP Inválido'); return; }

    const originalBtn = document.getElementById('buscar-cep-btn').innerHTML;
    document.getElementById('buscar-cep-btn').innerText = '...';
    try {
      const response = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
      const data = await response.json();
      if (!data.erro) {
        document.getElementById('logradouro').value = data.logradouro;
        document.getElementById('bairro').value = data.bairro || '';
        document.getElementById('cidade').value = data.localidade;
        document.getElementById('estado').value = data.uf;
      }
    } catch (e) { console.error("Erro CEP", e); } 
    finally { document.getElementById('buscar-cep-btn').innerHTML = originalBtn; }
  });

  // --- Busca de CNPJ ---
  document.getElementById('buscar-cnpj-btn')?.addEventListener('click', async () => {
    const raw = document.getElementById('cnpj_cpf').value.replace(/\D/g, '');
    if (raw.length !== 14) { alert('Informe um CNPJ válido.'); return; }
    try {
      const res  = await fetch(`https://brasilapi.com.br/api/cnpj/v1/${raw}`);
      const data = await res.json();
      if (data.razao_social) {
        document.getElementById('nome').value = data.razao_social;
        if(data.cep) {
          document.getElementById('cep').value = data.cep;
          document.getElementById('buscar-cep-btn').click();
        }
      }
    } catch (err) { alert('CNPJ não encontrado.'); }
  });

  /* ── Progress bar (quick estimate) ── */
  const requiredFields = document.querySelectorAll('[required]');
  const fill           = document.getElementById('progress-fill');
  function updateProgress() {
    const total   = requiredFields.length;
    if (total === 0) return;
    const filled  = Array.from(requiredFields).filter(f => {
      if (f.type === 'radio') return document.querySelector(`[name="${f.name}"]:checked`);
      return f.value.trim() !== '';
    }).length;
    if (fill) fill.style.width = Math.round((filled / total) * 100) + '%';
  }
  const formEl = document.getElementById('cliente-form');
  if (formEl) {
    formEl.addEventListener('input', updateProgress);
    formEl.addEventListener('change', updateProgress);
    updateProgress();
  }

})();
</script>
