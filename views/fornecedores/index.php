<?php
/**
 * Módulo Fornecedores — Dashboard Principal
 * Visual redesign: layout profissional, clean e moderno.
 */
?>

<style>
/* ── Reset e Tokens ── */
*, *::before, *::after { box-sizing: border-box; }
:root {
  --brand:       #0A6EBD;
  --brand-mid:   #1a7fcf;
  --brand-dim:   #EBF4FB;
  --success:     #0F8B5A;
  --success-dim: #E3F7EE;
  --warning:     #B45E0A;
  --warning-dim: #FEF3E6;
  --danger:      #C42B2B;
  --danger-dim:  #FDEAEA;
  --surface:     #FFFFFF;
  --surface2:    #F5F6F8;
  --surface3:    #ECEEF2;
  --border:      rgba(0,0,0,0.08);
  --border2:     rgba(0,0,0,0.14);
  --text1:       #0D1117;
  --text2:       #4A5568;
  --text3:       #8A95A4;
  --radius:      12px;
  --shadow:      0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
  --shadow-md:   0 4px 12px rgba(0,0,0,0.07), 0 2px 4px rgba(0,0,0,0.05);
}

/* ── Topbar do módulo ── */
#forn-topbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 24px;
  flex-wrap: wrap;
  gap: 12px;
}

.forn-topbar-left { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }

.forn-module-badge {
  background: var(--brand-dim);
  color: var(--brand);
  font-size: 11px;
  font-weight: 600;
  letter-spacing: 0.05em;
  text-transform: uppercase;
  padding: 3px 10px;
  border-radius: 20px;
  display: inline-flex;
  align-items: center;
  gap: 5px;
}

.forn-page-title {
  font-size: 20px;
  font-weight: 700;
  color: var(--text1);
  letter-spacing: -0.02em;
}

.forn-page-sub {
  font-size: 13px;
  color: var(--text3);
  margin-top: 2px;
}

.forn-topbar-right { display: flex; align-items: center; gap: 8px; }

/* ── Botões ── */
.forn-btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 13px;
  font-weight: 500;
  border-radius: 8px;
  padding: 0 14px;
  height: 36px;
  border: 1px solid var(--border2);
  cursor: pointer;
  transition: all 0.15s;
  text-decoration: none;
  white-space: nowrap;
  background: var(--surface);
  color: var(--text2);
  font-family: inherit;
}
.forn-btn:hover { background: var(--surface2); }
.forn-btn svg { width:14px; height:14px; flex-shrink:0; }

.forn-btn-primary {
  background: var(--brand);
  color: #fff;
  border-color: var(--brand);
  box-shadow: 0 2px 8px rgba(10,110,189,0.25);
}
.forn-btn-primary:hover { background: var(--brand-mid); }

/* ── Cards KPI ── */
.forn-kpis {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 14px;
  margin-bottom: 20px;
}

.forn-kpi {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  padding: 18px 20px;
  box-shadow: var(--shadow);
  position: relative;
  overflow: hidden;
  transition: box-shadow 0.2s;
}
.forn-kpi:hover { box-shadow: var(--shadow-md); }
.forn-kpi::before {
  content: '';
  position: absolute;
  left: 0; top: 0; bottom: 0;
  width: 3px;
  border-radius: 3px 0 0 3px;
}
.forn-kpi-blue::before  { background: var(--brand); }
.forn-kpi-green::before { background: var(--success); }
.forn-kpi-warn::before  { background: var(--warning); }
.forn-kpi-red::before   { background: var(--danger); }

.forn-kpi-icon {
  position: absolute;
  top: 16px;
  right: 16px;
  width: 20px;
  height: 20px;
  opacity: 0.2;
}
.forn-kpi-blue  .forn-kpi-icon { color: var(--brand); }
.forn-kpi-green .forn-kpi-icon { color: var(--success); }
.forn-kpi-warn  .forn-kpi-icon { color: var(--warning); }
.forn-kpi-red   .forn-kpi-icon { color: var(--danger); }

.forn-kpi-label {
  font-size: 11px;
  font-weight: 600;
  color: var(--text3);
  text-transform: uppercase;
  letter-spacing: 0.05em;
  margin-bottom: 8px;
}
.forn-kpi-value {
  font-size: 32px;
  font-weight: 700;
  line-height: 1;
  letter-spacing: -0.03em;
  margin-bottom: 4px;
}
.forn-kpi-blue  .forn-kpi-value { color: var(--brand); }
.forn-kpi-green .forn-kpi-value { color: var(--success); }
.forn-kpi-warn  .forn-kpi-value { color: var(--warning); }
.forn-kpi-red   .forn-kpi-value { color: var(--danger); }

.forn-kpi-sub { font-size: 11.5px; color: var(--text3); }

/* ── Painel de Filtros ── */
.forn-filters {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  padding: 14px 18px;
  box-shadow: var(--shadow);
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 20px;
  flex-wrap: wrap;
}

.forn-search-wrap {
  flex: 1;
  min-width: 200px;
  position: relative;
}

.forn-search-wrap svg {
  position: absolute;
  left: 10px;
  top: 50%;
  transform: translateY(-50%);
  width: 14px; height: 14px;
  color: var(--text3);
  pointer-events: none;
}

.forn-input {
  width: 100%;
  height: 36px;
  border: 1px solid var(--border2);
  border-radius: 8px;
  padding: 0 12px 0 33px;
  font-family: inherit;
  font-size: 13.5px;
  color: var(--text1);
  background: var(--surface2);
  outline: none;
  transition: border-color 0.15s, box-shadow 0.15s;
}
.forn-input:focus {
  border-color: var(--brand);
  background: var(--surface);
  box-shadow: 0 0 0 3px rgba(10,110,189,0.1);
}
.forn-input-select {
  padding-left: 12px;
  cursor: pointer;
  min-width: 170px;
}

.forn-filters-divider { width: 1px; height: 24px; background: var(--border); }

/* ── Painel da Tabela ── */
.forn-table-panel {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  box-shadow: var(--shadow);
  overflow: hidden;
}

.forn-table-head {
  padding: 14px 18px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-bottom: 1px solid var(--border);
  gap: 12px;
}

.forn-table-title-wrap { display: flex; align-items: center; gap: 10px; }

.forn-table-title {
  font-size: 14px;
  font-weight: 600;
  color: var(--text1);
}

.forn-count-badge {
  background: var(--surface3);
  color: var(--text2);
  font-size: 11.5px;
  font-weight: 600;
  padding: 2px 9px;
  border-radius: 20px;
}

/* Tabela */
.forn-table {
  width: 100%;
  border-collapse: collapse;
}

.forn-table thead th {
  padding: 9px 16px;
  font-size: 10.5px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.07em;
  color: var(--text3);
  background: var(--surface2);
  border-bottom: 1px solid var(--border);
  text-align: left;
  white-space: nowrap;
}
.forn-table thead th.th-center { text-align: center; }
.forn-table thead th.th-right  { text-align: right; }

.forn-table tbody td {
  padding: 13px 16px;
  font-size: 13.5px;
  color: var(--text2);
  border-bottom: 1px solid var(--border);
  vertical-align: middle;
}
.forn-table tbody tr:last-child td { border-bottom: none; }
.forn-table tbody tr:hover td { background: #FAFBFC; }

.forn-supplier-name {
  font-weight: 600;
  color: var(--text1);
  font-size: 13.5px;
  margin-bottom: 2px;
}
.forn-supplier-cnpj {
  font-family: 'Courier New', monospace;
  font-size: 11.5px;
  color: var(--text3);
  letter-spacing: 0.02em;
}

.forn-city-wrap { display: flex; align-items: center; gap: 5px; }
.forn-city-wrap svg { width:13px; height:13px; color: var(--text3); flex-shrink:0; }

.forn-cat-tag {
  font-size: 11px;
  font-weight: 500;
  color: var(--text2);
  background: var(--surface3);
  padding: 3px 10px;
  border-radius: 20px;
  display: inline-block;
}

/* Status pills */
.forn-status {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 3px 12px;
  border-radius: 20px;
  font-size: 11px;
  font-weight: 600;
  white-space: nowrap;
}
.forn-status::before {
  content: '';
  width: 5px; height: 5px;
  border-radius: 50%;
  flex-shrink: 0;
}
.forn-status-ativo       { background: var(--success-dim); color: var(--success); }
.forn-status-ativo::before  { background: var(--success); }
.forn-status-inativo     { background: var(--danger-dim); color: var(--danger); }
.forn-status-inativo::before  { background: var(--danger); }
.forn-status-homologacao { background: var(--warning-dim); color: var(--warning); }
.forn-status-homologacao::before { background: var(--warning); }

.forn-td-center { text-align: center; }
.forn-td-right  { text-align: right; }

.forn-action-link {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  color: var(--brand);
  text-decoration: none;
  border-radius: 8px;
  transition: all 0.15s;
  border: none;
  background: none;
  cursor: pointer;
}
.forn-action-link:hover { background: var(--brand-dim); }
.forn-action-link svg { width:15px; height:15px; }

/* Paginação */
.forn-pagination {
  padding: 14px 18px;
  border-top: 1px solid var(--border);
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  flex-wrap: wrap;
}
.forn-page-info { font-size: 12.5px; color: var(--text3); }
.forn-page-btns { display: flex; gap: 4px; }
.forn-page-btn {
  width: 30px; height: 30px;
  display: flex; align-items: center; justify-content: center;
  border-radius: 6px;
  font-size: 12.5px; font-weight: 500;
  cursor: pointer;
  border: 1px solid var(--border);
  background: var(--surface);
  color: var(--text2);
  transition: all 0.15s;
  text-decoration: none;
}
.forn-page-btn:hover { background: var(--surface2); }
.forn-page-btn.active { background: var(--brand); color: #fff; border-color: var(--brand); }

/* Empty state */
.forn-empty {
  text-align: center;
  padding: 48px 24px;
  color: var(--text3);
  font-size: 14px;
}
.forn-empty svg { width: 40px; height: 40px; margin: 0 auto 12px; display: block; opacity: 0.3; }

/* Responsivo */
@media (max-width: 900px) {
  .forn-kpis { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 600px) {
  .forn-kpis { grid-template-columns: 1fr 1fr; }
  .forn-topbar-right .forn-btn:not(.forn-btn-primary) { display: none; }
  .forn-filters-divider { display: none; }
  .forn-input-select { min-width: 100%; }
}
</style>

<!-- Topbar do Módulo -->
<div id="forn-topbar">
  <div class="forn-topbar-left">
    <div>
      <div style="display:flex;align-items:center;gap:10px;margin-bottom:4px;">
        <span class="forn-module-badge">
          <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
          Fornecedores
        </span>
      </div>
      <h2 class="forn-page-title"><?php echo htmlspecialchars($pageTitle ?? 'Gestão de Fornecedores'); ?></h2>
      <p class="forn-page-sub">Controle centralizado de parcerias, vigência de contratos e requisitos técnicos.</p>
    </div>
  </div>
  <div class="forn-topbar-right">
    <button id="export-pdf-btn" class="forn-btn">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
      Exportar PDF
    </button>
    <button id="export-excel-btn" class="forn-btn">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="3" y1="15" x2="21" y2="15"/><line x1="9" y1="3" x2="9" y2="21"/></svg>
      Exportar Excel
    </button>
    <a href="<?php echo BASE_URL; ?>/fornecedores/novo" class="forn-btn forn-btn-primary">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      Novo Fornecedor
    </a>
  </div>
</div>

<!-- KPIs -->
<div class="forn-kpis">
  <div class="forn-kpi forn-kpi-blue">
    <svg class="forn-kpi-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
    <div class="forn-kpi-label">Fornecedores Ativos</div>
    <div class="forn-kpi-value"><?php echo $totalAtivos ?? 0; ?></div>
    <div class="forn-kpi-sub">Base homologada ativa</div>
  </div>
  <div class="forn-kpi forn-kpi-red">
    <svg class="forn-kpi-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
    <div class="forn-kpi-label">Contratos a Vencer (30d)</div>
    <div class="forn-kpi-value"><?php echo $contratoVencer30 ?? 0; ?></div>
    <div class="forn-kpi-sub">Requer atenção imediata</div>
  </div>
  <div class="forn-kpi forn-kpi-warn">
    <svg class="forn-kpi-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><polyline points="13 2 13 9 20 9"/></svg>
    <div class="forn-kpi-label">Pendência de Documentos</div>
    <div class="forn-kpi-value"><?php echo $pendenciaDocs ?? 0; ?></div>
    <div class="forn-kpi-sub">Aguardando envio</div>
  </div>
  <div class="forn-kpi forn-kpi-red">
    <svg class="forn-kpi-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
    <div class="forn-kpi-label">Avaliação de Risco Alta</div>
    <div class="forn-kpi-value"><?php echo $riscoAlto ?? 0; ?></div>
    <div class="forn-kpi-sub">Monitoramento ativo</div>
  </div>
</div>

<!-- Filtros -->
<div class="forn-filters">
  <form action="<?php echo BASE_URL; ?>/fornecedores" method="GET" style="display:contents">
    <div class="forn-search-wrap">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
      <input
        type="text"
        name="busca"
        class="forn-input"
        placeholder="Buscar por nome, CNPJ ou cidade..."
        value="<?php echo htmlspecialchars($filtros['busca'] ?? ''); ?>"
      >
    </div>
    <div class="forn-filters-divider"></div>
    <select name="status" class="forn-input forn-input-select">
      <option value="">Todos os status</option>
      <option value="Ativo"          <?php echo (($filtros['status'] ?? '') === 'Ativo')          ? 'selected' : ''; ?>>Ativo</option>
      <option value="Inativo"        <?php echo (($filtros['status'] ?? '') === 'Inativo')        ? 'selected' : ''; ?>>Inativo</option>
      <option value="Em Homologação" <?php echo (($filtros['status'] ?? '') === 'Em Homologação') ? 'selected' : ''; ?>>Em Homologação</option>
    </select>
    <div style="display:flex; gap:8px; flex-shrink:0">
      <button type="submit" class="forn-btn forn-btn-primary">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
        Filtrar
      </button>
      <button type="button" id="forn-clear-filters" class="forn-btn" title="Limpar todos os filtros">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
        Limpar
      </button>
    </div>
  </form>
</div>

<!-- Tabela -->
<div class="forn-table-panel">
  <div class="forn-table-head">
    <div class="forn-table-title-wrap">
      <span class="forn-table-title">Lista de Fornecedores</span>
      <?php if (!empty($paginacao['total'])) : ?>
        <span class="forn-count-badge"><?php echo $paginacao['total']; ?> registros</span>
      <?php endif; ?>
    </div>
  </div>

  <div style="overflow-x:auto">
    <table class="forn-table">
      <thead>
        <tr>
          <th style="width:44px; padding-right:4px">
            <input type="checkbox" style="cursor:pointer;accent-color:#0A6EBD">
          </th>
          <th>Razão Social</th>
          <th>CNPJ</th>
          <th>Cidade / UF</th>
          <th>Categoria</th>
          <th class="th-center">Status</th>
          <th class="th-right">Ações</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($fornecedores)) : ?>
          <?php foreach ($fornecedores as $f) :
            $status    = $f['status'] ?? '';
            $statusCls = match($status) {
              'Ativo'          => 'forn-status-ativo',
              'Inativo'        => 'forn-status-inativo',
              'Em Homologação' => 'forn-status-homologacao',
              default          => 'forn-status-homologacao',
            };
          ?>
            <tr>
              <td style="padding-right:4px">
                <input type="checkbox" style="cursor:pointer;accent-color:#0A6EBD">
              </td>
              <td>
                <div class="forn-supplier-name"><?php echo htmlspecialchars($f['nome'] ?? ''); ?></div>
                <?php if (!empty($f['nome_fantasia'])) : ?>
                  <div class="forn-supplier-cnpj"><?php echo htmlspecialchars($f['nome_fantasia']); ?></div>
                <?php endif; ?>
              </td>
              <td>
                <span class="forn-supplier-cnpj">
                  <?php 
                    $doc = preg_replace('/\D/', '', $f['cnpj'] ?? $f['cnpj_cpf'] ?? '');
                    if (strlen($doc) === 11) {
                        echo vsprintf('%s%s%s.%s%s%s.%s%s%s-%s%s', str_split($doc));
                    } elseif (strlen($doc) === 14) {
                        echo vsprintf('%s%s.%s%s%s.%s%s%s/%s%s%s%s-%s%s', str_split($doc));
                    } else {
                        echo htmlspecialchars($f['cnpj'] ?? $f['cnpj_cpf'] ?? '—');
                    }
                  ?>
                </span>
              </td>
              <td>
                <div class="forn-city-wrap">
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                  <?php echo htmlspecialchars($f['cidade'] ?? '—'); ?>
                  <?php if (!empty($f['uf'])) : ?>, <?php echo htmlspecialchars($f['uf']); ?><?php endif; ?>
                </div>
              </td>
              <td>
                <?php if (!empty($f['categoria_fornecimento'])) : ?>
                  <span class="forn-cat-tag"><?php echo htmlspecialchars($f['categoria_fornecimento']); ?></span>
                <?php else : ?>
                  <span style="color:var(--text3);font-size:13px">—</span>
                <?php endif; ?>
              </td>
              <td class="forn-td-center">
                <span class="forn-status <?php echo $statusCls; ?>"><?php echo htmlspecialchars($status); ?></span>
              </td>
              <td class="forn-td-right">
                <div style="display:flex; justify-content:flex-end; gap:4px">
                  <a href="<?php echo BASE_URL; ?>/fornecedores/detalhe/<?php echo $f['id']; ?>" class="forn-action-link" title="Visualizar Detalhes" style="color:var(--text2)">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                  </a>
                  <a href="<?php echo BASE_URL; ?>/fornecedores/editar/<?php echo $f['id']; ?>" class="forn-action-link" title="Editar Cadastro" style="color:var(--brand)">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                  </a>

                  <?php if ($status === 'Inativo') : ?>
                    <form action="<?php echo BASE_URL; ?>/fornecedores/ativar/<?php echo $f['id']; ?>" method="POST" onsubmit="return confirm('Deseja reativar este fornecedor e movê-lo para a base ativa?');" style="display:inline">
                      <input type="hidden" name="id" value="<?php echo $f['id']; ?>">
                      <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                      <button type="submit" class="forn-action-link" style="color:var(--success)" title="Restaurar Fornecedor">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="1 4 1 10 7 10"></polyline><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"></path></svg>
                      </button>
                    </form>
                  <?php else : ?>
                    <form action="<?php echo BASE_URL; ?>/fornecedores/arquivar/<?php echo $f['id']; ?>" method="POST" onsubmit="return confirm('Deseja realmente arquivar este fornecedor? Ele será marcado como Inativo.');" style="display:inline">
                      <input type="hidden" name="id" value="<?php echo $f['id']; ?>">
                      <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                      <button type="submit" class="forn-action-link" style="color:var(--danger)" title="Arquivar Fornecedor">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                      </button>
                    </form>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else : ?>
          <tr>
            <td colspan="7" class="forn-empty">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
              Nenhum fornecedor encontrado com os filtros aplicados.
            </td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- Paginação -->
  <?php if (!empty($paginacao)) : ?>
    <div class="forn-pagination">
      <span class="forn-page-info">
        Mostrando <?php echo $paginacao['inicio'] ?? 1; ?>–<?php echo $paginacao['fim'] ?? count($fornecedores); ?>
        de <?php echo $paginacao['total'] ?? count($fornecedores); ?> fornecedores
      </span>
      <div class="forn-page-btns">
        <?php if ($paginacao['pagina_atual'] > 1) : ?>
          <a href="?page=<?php echo $paginacao['pagina_atual'] - 1; ?>" class="forn-page-btn">
            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
          </a>
        <?php endif; ?>
        <?php for ($p = 1; $p <= ($paginacao['total_paginas'] ?? 1); $p++) : ?>
          <a href="?page=<?php echo $p; ?>" class="forn-page-btn <?php echo $p === ($paginacao['pagina_atual'] ?? 1) ? 'active' : ''; ?>">
            <?php echo $p; ?>
          </a>
        <?php endfor; ?>
        <?php if (($paginacao['pagina_atual'] ?? 1) < ($paginacao['total_paginas'] ?? 1)) : ?>
          <a href="?page=<?php echo $paginacao['pagina_atual'] + 1; ?>" class="forn-page-btn">
            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
          </a>
        <?php endif; ?>
      </div>
    </div>
  <?php endif; ?>
</div>

<script>
(function () {
  /* Export PDF */
  document.getElementById('export-pdf-btn')?.addEventListener('click', () => {
    const params = new URLSearchParams(new FormData(document.querySelector('.forn-filters form')));
    const url = '<?php echo BASE_URL; ?>/fornecedores/exportarPdf?' + params.toString();
    window.open(url, '_blank');
  });
  /* Export Excel */
  document.getElementById('export-excel-btn')?.addEventListener('click', () => {
    const params = new URLSearchParams(new FormData(document.querySelector('.forn-filters form')));
    window.location.href = '<?php echo BASE_URL; ?>/fornecedores/exportarExcel?' + params.toString();
  });
  /* Event delegation for select-all checkbox */
  document.addEventListener('change', (e) => {
    if (e.target.matches('thead input[type=checkbox]')) {
      const isChecked = e.target.checked;
      document.querySelectorAll('tbody input[type=checkbox]')
        .forEach(c => c.checked = isChecked);
    }
  });

  /* AJAX Filtering & Reset */
  const filterForm = document.querySelector('.forn-filters form');
  const searchInput = filterForm?.querySelector('input[name="busca"]');
  const statusSelect = filterForm?.querySelector('select[name="status"]');
  const clearBtn = document.getElementById('forn-clear-filters');

  let debounceTimer;
  const debounce = (callback, time) => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(callback, time);
  };

  const updateDashboard = async (url) => {
    const panel = document.querySelector('.forn-table-panel');
    panel.style.opacity = '0.5'; // Feedback visual de carregamento
    
    try {
      const res = await fetch(url);
      const html = await res.text();
      const parser = new DOMParser();
      const doc = parser.parseFromString(html, 'text/html');
      
      const kpiContainer = document.querySelector('.forn-kpis');
      const tableContainer = document.querySelector('.forn-table-panel');

      if (kpiContainer && doc.querySelector('.forn-kpis')) kpiContainer.innerHTML = doc.querySelector('.forn-kpis').innerHTML;
      if (tableContainer && doc.querySelector('.forn-table-panel')) tableContainer.innerHTML = doc.querySelector('.forn-table-panel').innerHTML;
    } finally {
      panel.style.opacity = '1';
    }
  };

  const triggerFilter = () => {
    const url = new URL(filterForm.action);
    new FormData(filterForm).forEach((val, key) => url.searchParams.set(key, val));
    window.history.pushState({}, '', url);
    updateDashboard(url);
  };

  /* Interceptar cliques na paginação para usar AJAX */
  document.addEventListener('click', (e) => {
    const btn = e.target.closest('.forn-page-btn');
    if (btn && btn.href) {
      e.preventDefault();
      updateDashboard(btn.href);
      window.history.pushState({}, '', btn.href);
    }
  });

  filterForm?.addEventListener('submit', (e) => {
    e.preventDefault();
    triggerFilter();
  });

  searchInput?.addEventListener('input', () => debounce(triggerFilter, 400));
  statusSelect?.addEventListener('change', triggerFilter);

  clearBtn?.addEventListener('click', () => {
    filterForm.reset();
    const url = new URL(filterForm.action);
    window.history.pushState({}, '', url);
    updateDashboard(url);
  });
})();
</script>
