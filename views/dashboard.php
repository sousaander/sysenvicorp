<?php
// Busca tarefas pendentes do usuário logado
$tarefasModel = new \App\Models\TarefasModel();
$userId = $_SESSION['user_id'] ?? 0;
$tarefasPendentes = $tarefasModel->getCountTarefasPendentesByUsuario($userId);
$minhasTarefas = $tarefasModel->getTarefasPendentesByUsuario($userId, 5);

// Busca projetos com localização para o mapa
$projetosModel = new \App\Models\ProjetosModel();
$projetosComLocalizacao = $projetosModel->getProjetosComLocalizacao(); // retorna lat, lng, nome, status, cliente_nome

// Extrai setores únicos para o filtro do mapa
$setoresMapa = array_unique(array_filter(array_column($projetosComLocalizacao, 'tipo_servico')));
sort($setoresMapa);

// Garante a busca dos projetos para a tabela e gráfico de status
$allProjetos = $projetosModel->getProjetos([], 500, 0) ?? []; 
$projetos = $projetos ?? array_slice($allProjetos, 0, 10);
$projetosAtivos = $projetosAtivos ?? count(array_filter($allProjetos, fn($p) => in_array($p['status'] ?? '', ['Em Andamento', 'Em Execução', 'Atrasado'])));
$licencasAVencer = $licencasAVencer ?? 0; // Fallback se não vier do controller
$contratosVigentes = $contratosVigentes ?? 0;
$novosClientesMes = $novosClientesMes ?? 0;

// Busca dados financeiros para o gráfico de linha
$financialModel = new \App\Models\FinancialModel();
if (!isset($monthlySummary)) {
    $monthlySummary = $financialModel->getResumoMensalParaGrafico(6);
}
?>

<!-- === ESTILOS DO DASHBOARD === -->
<style>
  /* As variáveis --db- agora vêm do main_template.php */
  
  .db-section-label {
    font-size: 10px; font-weight: 600; text-transform: uppercase;
    letter-spacing: 1px; color: var(--db-text3);
    margin-bottom: 12px; margin-top: 8px;
  }

  /* === STAT CARDS === */
  .db-stats-grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 14px; margin-bottom: 6px;
  }
  .db-stat-card {
    background: var(--db-surface);
    border: 1px solid var(--db-border);
    border-radius: 12px;
    padding: 18px;
    display: flex; flex-direction: column; gap: 10px;
    cursor: pointer; transition: all 0.2s;
    text-decoration: none;
    position: relative; overflow: hidden;
  }
  .db-stat-card::after {
    content: ''; position: absolute; bottom: 0; left: 0; right: 0; height: 2px;
  }
  .db-stat-card.red::after   { background: var(--db-red); }
  .db-stat-card.blue::after  { background: var(--db-accent2); }
  .db-stat-card.orange::after { background: var(--db-orange); }
  .db-stat-card.purple::after { background: var(--db-purple); }
  .db-stat-card.green::after  { background: var(--db-green); }
  .db-stat-card:hover {
    border-color: var(--db-text3);
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(0,0,0,0.4);
  }
  .db-stat-top { display: flex; justify-content: space-between; align-items: flex-start; }
  .db-stat-label {
    font-size: 9px; font-weight: 700; text-transform: uppercase;
    letter-spacing: 1px; color: var(--db-text3);
  }
  .db-stat-icon {
    width: 32px; height: 32px; border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
  }
  .db-stat-icon.red    { background: rgba(248,81,73,0.12);    color: var(--db-red); }
  .db-stat-icon.blue   { background: rgba(46,168,224,0.12);   color: var(--db-accent2); }
  .db-stat-icon.orange { background: rgba(227,161,26,0.12);   color: var(--db-orange); }
  .db-stat-icon.purple { background: rgba(188,140,255,0.12);  color: var(--db-purple); }
  .db-stat-icon.green  { background: rgba(63,185,80,0.12);    color: var(--db-green); }
  .db-stat-icon svg { width: 16px; height: 16px; }
  .db-stat-value {
    font-size: 24px; font-weight: 800; line-height: 1;
    font-variant-numeric: tabular-nums;
  }
  .db-stat-value.red    { color: var(--db-red); }
  .db-stat-value.blue   { color: var(--db-accent2); }
  .db-stat-value.orange { color: var(--db-orange); }
  .db-stat-value.purple { color: var(--db-purple); }
  .db-stat-value.green  { color: var(--db-green); }

  /* === CARD === */
  .db-card {
    background: var(--db-surface);
    border: 1px solid var(--db-border);
    border-radius: 12px;
    padding: 20px 22px;
    margin-bottom: 16px;
  }
  .db-card-header {
    display: flex; justify-content: space-between; align-items: center;
    margin-bottom: 16px;
    background: var(--db-surface2);
    padding: 12px 16px;
    border-radius: 8px;
    border: 1px solid var(--db-border);
  }
  .db-card-title { font-size: 14px; font-weight: 600; color: var(--db-text); }
  .db-card-link { font-size: 12px; color: var(--db-accent2); text-decoration: none; }
  .db-card-link:hover { text-decoration: underline; }

  /* === TABLE === */
  .db-table { width: 100%; border-collapse: collapse; }
  .db-table th {
    padding: 8px 12px; text-align: left;
    font-size: 10px; font-weight: 600; text-transform: uppercase;
    letter-spacing: 0.8px; color: var(--db-text3);
    border-bottom: 1px solid var(--db-border);
  }
  .db-table td {
    padding: 11px 12px;
    font-size: 13px; color: inherit;
    border-bottom: 1px solid var(--db-border);
  }
  .db-table tr:last-child td { border-bottom: none; }
  .db-table tr:hover td { background: rgba(255,255,255,0.015); }
  .db-table td.bold { color: var(--db-text); font-weight: 500; }

  /* === BADGES === */
  .db-badge {
    display: inline-flex; align-items: center;
    padding: 2px 8px; border-radius: 20px;
    font-size: 10px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;
  }
  .db-badge.red    { background: rgba(248,81,73,0.12);    color: var(--db-red); }
  .db-badge.orange { background: rgba(227,161,26,0.12);   color: var(--db-orange); }
  .db-badge.blue   { background: rgba(46,168,224,0.12);   color: var(--db-accent2); }
  .db-badge.gray   { background: rgba(139,148,158,0.12);  color: var(--db-text2); }
  .db-badge.green  { background: rgba(63,185,80,0.12);    color: var(--db-green); }
  .db-badge.purple { background: rgba(188,140,255,0.12);  color: var(--db-purple); }

  /* === BUTTON === */
  .db-btn-sm {
    padding: 4px 12px; border-radius: 6px;
    font-size: 11px; font-weight: 500; cursor: pointer;
    border: 1px solid var(--db-border);
    background: transparent; color: var(--db-accent2);
    transition: all 0.15s; text-decoration: none; display: inline-block;
  }
  .db-btn-sm:hover { background: var(--db-accent-glow); border-color: var(--db-accent2); }

  /* === PROGRESS BAR === */
  .db-progress-bg { width: 100%; height: 6px; background: var(--db-border); border-radius: 10px; overflow: hidden; margin-top: 4px; }
  .db-progress-fill { height: 100%; border-radius: 10px; transition: width 0.8s cubic-bezier(0.4, 0, 0.2, 1); }
  .db-progress-text { font-size: 10px; font-weight: 600; color: var(--db-text2); display: block; margin-bottom: 2px; }

  /* === 3-COL LAYOUT === */
  .db-three-col {
    display: grid;
    grid-template-columns: 280px 1fr 1fr;
    gap: 16px; margin-bottom: 16px;
  }

  /* === INDICATORS === */
  .db-ind-row {
    display: flex; justify-content: space-between; align-items: center;
    padding: 10px 0; border-bottom: 1px solid var(--db-border);
  }
  .db-ind-row:last-child { border-bottom: none; padding-bottom: 0; }
  .db-ind-label {
    display: flex; align-items: center; gap: 8px;
    font-size: 12px; color: var(--db-text2);
  }
  .db-ind-label svg { width: 14px; height: 14px; color: var(--db-text3); }
  .db-ind-val { text-align: right; }
  .db-ind-val span {
    font-size: 13px; font-weight: 600; color: var(--db-text); display: block;
  }
  .db-ind-val small { font-size: 9px; color: var(--db-text3); }
  .db-refresh-btn {
    background: none; border: none; cursor: pointer;
    color: var(--db-text3); padding: 4px; border-radius: 4px;
    transition: color 0.2s; display: flex; align-items: center;
  }
  .db-refresh-btn:hover { color: var(--db-green); }
  .db-refresh-btn svg { width: 13px; height: 13px; }
  @keyframes db-spin { to { transform: rotate(360deg); } }
  .db-spinning { animation: db-spin 1s linear infinite; }

  /* === MAP === */
  #db-brazil-map {
    height: 420px; border-radius: 10px;
    border: 1px solid var(--db-border); overflow: hidden;
  }
  .db-map-header {
    display: flex; justify-content: space-between; align-items: center;
    margin-bottom: 16px; flex-wrap: wrap; gap: 10px;
    background: var(--db-surface2);
    padding: 12px 16px;
    border-radius: 8px;
    border: 1px solid var(--db-border);
  }
  .db-map-legend { display: flex; gap: 15px; flex-wrap: wrap; }
  .db-legend-item {
    display: flex; align-items: center; gap: 6px;
    font-size: 11px; font-weight: 500; color: var(--db-text2);
  }
  .db-legend-item i { font-size: 14px; }

  /* Badge de contagem dinâmica */
  .db-count-badge { 
    background: var(--db-border); color: var(--db-text2); 
    font-size: 9px; padding: 1px 5px; border-radius: 4px; font-weight: 700; 
  }

  /* Estilo do mapa: Inversão apenas no modo escuro */
  .dark-theme #db-brazil-map .leaflet-tile { filter: brightness(0.6) invert(1) contrast(90%); }
  /* Impede a inversão de cores na camada de satélite (Esri) para manter a fidelidade visual */
  .dark-theme #db-brazil-map img.leaflet-tile[src*="ArcGIS"] { filter: brightness(0.8) contrast(1.1) !important; }
  .dark-theme #db-brazil-map .leaflet-container { background: #0d1117; }
  #db-brazil-map .leaflet-container { background: var(--db-bg); }

  /* === CHART === */
  .db-chart-wrap { position: relative; height: 220px; }

  /* === MODAL TAREFA (Estilos Faltantes) === */
  .db-modal-box {
    background: var(--db-surface);
    border: 1px solid var(--db-border);
    border-radius: 16px;
    width: 100%; max-width: 550px;
    padding: 30px; position: relative;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
  }
  .db-modal-close {
    position: absolute; top: 20px; right: 20px;
    color: var(--db-text3); cursor: pointer;
  }
  .db-modal-grid {
    display: grid; grid-template-columns: 1fr 1fr;
    gap: 20px; margin: 20px 0;
  }
  .db-modal-field label {
    display: block; font-size: 10px; text-transform: uppercase;
    color: var(--db-text3); margin-bottom: 4px;
  }
  .db-modal-field span {
    font-size: 14px; font-weight: 500; color: var(--db-text);
  }
  .db-modal-desc label {
    display: block; font-size: 10px; text-transform: uppercase;
    color: var(--db-text3); margin-bottom: 8px;
  }
  .db-modal-desc-content {
    background: var(--db-bg); padding: 12px; border-radius: 8px;
    font-size: 13px; line-height: 1.6; color: var(--db-text2);
    border: 1px solid var(--db-border);
  }
  .db-modal-actions {
    display: flex; justify-content: flex-end; gap: 12px; margin-top: 25px;
  }
  .db-btn-primary {
    background: var(--db-accent); color: white; padding: 8px 20px;
    border-radius: 8px; font-size: 13px; font-weight: 600; text-decoration: none;
  }
  .db-btn-ghost {
    color: var(--db-text2); padding: 8px 20px; font-size: 13px;
  }
  .db-btn-success {
    background: var(--db-green); color: white; padding: 8px 20px;
    border-radius: 8px; font-size: 13px; font-weight: 600;
  }
  .hidden { display: none; }

  /* === PAGE HEADER === */
  .db-page-header {
    display: flex; align-items: flex-start; justify-content: space-between;
    margin-bottom: 24px; gap: 20px; flex-wrap: wrap;
  }
  .db-page-header h2 { font-size: 22px; font-weight: 700; color: var(--db-text); margin-bottom: 6px; }
  .db-page-header p { font-size: 13px; color: var(--db-text2); max-width: 520px; line-height: 1.6; }

  @media (max-width: 1100px) {
    .db-three-col { grid-template-columns: 1fr 1fr; }
    .db-stats-grid { grid-template-columns: repeat(3, 1fr); }
  }
  @media (max-width: 640px) {
    .db-stats-grid { grid-template-columns: repeat(2, 1fr); }
    .db-three-col { grid-template-columns: 1fr; }
  }
</style>

<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>

<!-- === PAGE HEADER === -->
<div class="db-page-header">
  <div>
    <h2 class="!mb-1 text-2xl font-bold text-gray-800 dark:text-white"><?php echo htmlspecialchars($pageTitle ?? 'Dashboard'); ?></h2>
    <p class="!mb-0 text-sm text-gray-500 font-medium italic">Bem-vindo de volta ao seu painel administrativo. Aqui está a visão consolidada da sua empresa.</p>
  </div>
</div>

<!-- === STAT CARDS === -->
<p class="db-section-label">Resumo Geral</p>
<div class="db-stats-grid" style="margin-bottom:20px">
  <!-- Minhas Tarefas -->
  <a href="#" class="db-stat-card red">
    <div class="db-stat-top">
      <span class="db-stat-label">Minhas Tarefas</span>
      <div class="db-stat-icon red">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
      </div>
    </div>
    <div class="db-stat-value red"><?php echo $tarefasPendentes ?? 0; ?></div>
  </a>
  <!-- Projetos Ativos -->
  <a href="<?php echo BASE_URL; ?>/projetos" class="db-stat-card blue">
    <div class="db-stat-top">
      <span class="db-stat-label">Projetos Ativos</span>
      <div class="db-stat-icon blue">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 3h-8a2 2 0 00-2 2v2h12V5a2 2 0 00-2-2z"/></svg>
      </div>
    </div>
    <div class="db-stat-value blue"><?php echo $projetosAtivos ?? 0; ?></div>
  </a>
  <!-- Licenças -->
  <a href="<?php echo BASE_URL; ?>/licencasOperacao" class="db-stat-card orange">
    <div class="db-stat-top">
      <span class="db-stat-label">Licenças (30d)</span>
      <div class="db-stat-icon orange">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/></svg>
      </div>
    </div>
    <div class="db-stat-value orange"><?php echo $licencasAVencer ?? 0; ?></div>
  </a>
  <!-- Contratos -->
  <a href="<?php echo BASE_URL; ?>/contratos" class="db-stat-card purple">
    <div class="db-stat-top">
      <span class="db-stat-label">Contratos Vigentes</span>
      <div class="db-stat-icon purple">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
      </div>
    </div>
    <div class="db-stat-value purple"><?php echo $contratosVigentes ?? 0; ?></div>
  </a>
  <!-- Clientes -->
  <a href="<?php echo BASE_URL; ?>/clientes" class="db-stat-card green">
    <div class="db-stat-top">
      <span class="db-stat-label">Novos Clientes</span>
      <div class="db-stat-icon green">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
      </div>
    </div>
    <div class="db-stat-value green"><?php echo $novosClientesMes ?? 0; ?></div>
  </a>
</div>

<!-- === MINHAS TAREFAS === -->
<p class="db-section-label">Minhas Tarefas Pendentes</p>
<div class="db-card">
  <div class="db-card-header">
    <span class="db-card-title">Tarefas em Aberto</span>
    <a href="<?php echo BASE_URL; ?>/projetos" class="db-card-link">Ver todas →</a>
  </div>
  <?php if (!empty($minhasTarefas)): ?>
    <div style="overflow-x:auto">
      <table class="db-table">
        <thead>
          <tr>
            <th>Tarefa</th><th>Projeto</th><th>Prioridade</th><th>Prazo</th><th style="text-align:right">Ação</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($minhasTarefas as $tarefa):
            $pBadge = ['Baixa'=>'gray','Média'=>'blue','Alta'=>'orange','Urgente'=>'red'][$tarefa['prioridade']] ?? 'gray';
          ?>
          <tr>
            <td class="bold"><?php echo htmlspecialchars($tarefa['titulo']); ?></td>
            <td><?php echo htmlspecialchars($tarefa['projeto_nome'] ?? 'N/A'); ?></td>
            <td><span class="db-badge <?php echo $pBadge; ?>"><?php echo htmlspecialchars($tarefa['prioridade']); ?></span></td>
            <td><?php echo $tarefa['data_fim'] ? date('d/m/Y', strtotime($tarefa['data_fim'])) : 'Sem prazo'; ?></td>
            <td style="text-align:right">
              <button onclick="dbOpenTaskModal(<?php echo $tarefa['id']; ?>)" class="db-btn-sm">Ver</button>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php else: ?>
    <p style="font-size:13px;color:var(--db-text2);text-align:center;padding:20px">Nenhuma tarefa pendente no momento.</p>
  <?php endif; ?>
</div>

<!-- === INDICADORES + GRÁFICOS === -->
<p class="db-section-label">Indicadores & Análises</p>
<div class="db-three-col">
  <!-- Indicadores -->
  <div class="db-card" style="margin:0">
    <div class="db-card-header">
      <span class="db-card-title">Indicadores Econômicos</span>
      <button class="db-refresh-btn" id="db-btn-refresh" title="Atualizar">
        <svg id="db-refresh-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M23 4v6h-6"/><path d="M1 20v-6h6"/>
          <path d="M3.51 9a9 9 0 0114.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0020.49 15"/>
        </svg>
      </button>
    </div>
    <div class="db-ind-row">
      <span class="db-ind-label"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg>Salário Mínimo</span>
      <div class="db-ind-val"><span id="ind-salario">R$ --</span><small id="ind-salario-data">...</small></div>
    </div>
    <div class="db-ind-row">
      <span class="db-ind-label"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/></svg>Selic (Meta)</span>
      <div class="db-ind-val"><span id="ind-selic">--%</span><small id="ind-selic-data">...</small></div>
    </div>
    <div class="db-ind-row">
      <span class="db-ind-label"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>Dólar (Venda)</span>
      <div class="db-ind-val"><span id="ind-dolar">R$ --</span><small id="ind-dolar-data">...</small></div>
    </div>
    <div class="db-ind-row">
      <span class="db-ind-label"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M9.5 9a2.5 2.5 0 015 0c0 2.5-5 2.5-5 5h5"/><path d="M12 18v1"/></svg>Bitcoin</span>
      <div class="db-ind-val"><span id="ind-bitcoin">R$ --</span><small id="ind-bitcoin-data">Agora</small></div>
    </div>
  </div>

  <!-- Gráfico Linha -->
  <div class="db-card" style="margin:0">
    <div class="db-card-header">
      <span class="db-card-title">Receitas vs. Despesas</span>
      <span style="font-size:11px;color:var(--db-text2)">Últimos 6 meses</span>
    </div>
    <div class="db-chart-wrap"><canvas id="receitasDespesasChart"></canvas></div>
  </div>

  <!-- Gráfico Pizza -->
  <div class="db-card" style="margin:0">
    <div class="db-card-header">
      <span class="db-card-title">Projetos por Status</span>
    </div>
    <div class="db-chart-wrap"><canvas id="projetosStatusChart"></canvas></div>
  </div>
</div>

<!-- === MAPA DO BRASIL === -->
<p class="db-section-label" style="margin-top:8px">Mapa de Projetos — Brasil</p>
<div class="db-card">
  <div class="db-map-header">
    <span class="db-card-title">Distribuição Geográfica dos Projetos <span id="db-map-total-count" class="db-count-badge ml-1">0</span></span>
    <div class="flex items-center gap-3">
      <select id="db-type-filter" class="db-btn-sm" style="background:var(--db-surface); outline:none; height:28px">
        <option value="all">Ver Ambos</option>
        <option value="projeto">Apenas Projetos</option>
        <option value="proposta">Apenas Orçamentos</option>
      </select>
      <select id="db-sector-filter" class="db-btn-sm" style="background:var(--db-surface); outline:none; height:28px">
        <option value="all">Todos os Setores</option>
        <?php foreach($setoresMapa as $setor): ?>
          <option value="<?php echo htmlspecialchars($setor); ?>"><?php echo htmlspecialchars($setor); ?></option>
        <?php endforeach; ?>
      </select>
    <div class="db-map-legend" id="db-map-legend">
      <span class="db-legend-item" data-status="Em Andamento"><i class='bx bxs-time-five' style='color:#2ea8e0'></i> Em Andamento <span class="db-count-badge">0</span></span>
      <span class="db-legend-item" data-status="Concluído"><i class='bx bxs-check-circle' style='color:#3fb950'></i> Concluído <span class="db-count-badge">0</span></span>
      <span class="db-legend-item" data-status="Planejado"><i class='bx bxs-calendar' style='color:#e3a11a'></i> Planejado <span class="db-count-badge">0</span></span>
      <span class="db-legend-item" data-status="Atrasado"><i class='bx bxs-error-circle' style='color:#f85149'></i> Atrasado <span class="db-count-badge">0</span></span>
      <span class="db-legend-item" data-status="Enviada"><i class='bx bxs-paper-plane' style='color:#a855f7'></i> Proposta Enviada <span class="db-count-badge">0</span></span>
      <span class="db-legend-item" data-status="Rascunho"><i class='bx bxs-edit' style='color:#6b7280'></i> Orçamento/Rascunho <span class="db-count-badge">0</span></span>
    </div>
    </div>
  </div>
  <div id="db-brazil-map"></div>
</div>

<!-- === PROJETOS RECENTES === -->
<p class="db-section-label">Projetos Recentes</p>
<div class="db-card">
  <div class="db-card-header">
    <span class="db-card-title">Últimos Projetos</span>
    <a href="<?php echo BASE_URL; ?>/projetos" class="db-card-link">Ver todos →</a>
  </div>
  <?php if (!empty($projetos) && is_array($projetos)): ?>
    <div style="overflow-x:auto">
      <table class="db-table">
        <thead>
          <tr>
            <th>Projeto</th><th>Cliente</th><th>Setor</th><th>Responsável</th><th>Progresso</th><th>Status</th><th style="text-align:right">Ações</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($projetos as $projeto):
            $statusName = $projeto['status'] ?? '';
            $icon = [
              'Em Andamento' => 'bx-time-five',
              'Concluído'    => 'bx-check-circle',
              'Planejado'    => 'bx-calendar',
              'Atrasado'     => 'bx-error-circle',
              'Cancelado'    => 'bx-x-circle'
            ][$statusName] ?? 'bx-circle';

            // Cálculo real baseado no volume de tarefas concluídas vs total
            $totalT = (int)($projeto['total_tarefas'] ?? 0);
            $concluidasT = (int)($projeto['tarefas_concluidas'] ?? 0);

            if ($totalT > 0) {
                $progresso = round(($concluidasT / $totalT) * 100);
            } else {
                // Caso não existam tarefas, assume 100% se o projeto estiver concluído, senão 0%
                $progresso = ($statusName === 'Concluído') ? 100 : 0;
            }

            $sBadgeColor = [
              'Concluído' => 'green', 'Cancelado' => 'red',
              'Planejado' => 'gray', 'Em Andamento' => 'blue', 'Atrasado' => 'orange'
            ][$statusName] ?? 'blue';

            // Mapeia a cor da barra para o padrão do dashboard
            $fillColor = [
                'green'  => 'var(--db-green)',
                'red'    => 'var(--db-red)',
                'orange' => 'var(--db-orange)',
                'blue'   => 'var(--db-accent2)',
                'gray'   => 'var(--db-text3)'
            ][$sBadgeColor] ?? 'var(--db-accent2)';
          ?>
          <tr>
            <td class="bold"><?php echo htmlspecialchars($projeto['nome']); ?></td>
            <td><?php echo htmlspecialchars($projeto['cliente_nome'] ?? 'N/A'); ?></td>
            <td style="font-size:11px;color:var(--db-text3)"><?php echo htmlspecialchars($projeto['tipo_servico'] ?? 'Geral'); ?></td>
            <td><?php echo htmlspecialchars($projeto['responsavel'] ?? 'N/A'); ?></td>
            <td style="min-width: 110px;">
              <span class="db-progress-text"><?php echo $progresso; ?>%</span>
              <div class="db-progress-bg">
                <div class="db-progress-fill" style="width: <?php echo $progresso; ?>%; background-color: <?php echo $fillColor; ?>;"></div>
              </div>
            </td>
            <td>
              <span class="db-badge <?php echo $sBadgeColor; ?>" style="gap:4px">
                <i class='bx <?php echo $icon; ?>' style="font-size:12px"></i>
                <?php echo htmlspecialchars($statusName); ?>
              </span>
            </td>
            <td style="text-align:right">
              <a href="<?php echo BASE_URL; ?>/projetos/detalhe/<?php echo $projeto['id']; ?>" class="db-btn-sm">Detalhes</a>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php else: ?>
    <p style="font-size:13px;color:var(--db-text2);text-align:center;padding:20px">Nenhum projeto ativo no momento.</p>
  <?php endif; ?>
</div>

<!-- === MODAL TAREFA === -->
<div id="db-taskModal" style="position:fixed;inset:0;background:rgba(0,0,0,0.75);z-index:1000;display:none;align-items:center;justify-content:center">
  <div class="db-modal-box">
    <button class="db-modal-close" onclick="dbCloseTaskModal()">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><path d="M18 6L6 18M6 6l12 12"/></svg>
    </button>
    <h3 id="db-modal-task-title">Carregando...</h3>
    <div class="db-modal-grid">
      <div class="db-modal-field"><label>Projeto</label><span id="db-modal-task-project">...</span></div>
      <div class="db-modal-field"><label>Status</label><span id="db-modal-task-status"></span></div>
      <div class="db-modal-field"><label>Prioridade</label><span id="db-modal-task-priority"></span></div>
      <div class="db-modal-field"><label>Prazo</label><span id="db-modal-task-end"></span></div>
      <div class="db-modal-field"><label>Início</label><span id="db-modal-task-start"></span></div>
    </div>
    <div class="db-modal-desc">
      <label>Descrição</label>
      <div class="db-modal-desc-content" id="db-modal-task-desc">...</div>
    </div>
    <div class="db-modal-actions">
      <button class="db-btn-ghost" onclick="dbCloseTaskModal()">Fechar</button>
      <button class="db-btn-success hidden" id="db-btn-concluir" onclick="dbConcluirTarefaAtual()">✓ Concluir</button>
      <a id="db-modal-task-link" href="#" class="db-btn-primary">Ir para Projeto</a>
    </div>
  </div>
</div>

<!-- === SCRIPTS === -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
(function() {
  'use strict';

  // ── MODAL TAREFA ──
  let dbCurrentTaskId = null;

  window.dbOpenTaskModal = function(id) {
    dbCurrentTaskId = id;
    const modal = document.getElementById('db-taskModal');
    modal.style.display = 'flex';
    document.getElementById('db-modal-task-title').textContent = 'Carregando...';
    document.getElementById('db-modal-task-project').textContent = '...';
    document.getElementById('db-modal-task-desc').textContent = '';
    document.getElementById('db-btn-concluir').classList.add('hidden');

    fetch('<?php echo BASE_URL; ?>/projetos/getTarefa/' + id)
      .then(r => r.json())
      .then(data => {
        if (data.success) {
          const t = data.data;
          document.getElementById('db-modal-task-title').textContent   = t.titulo;
          document.getElementById('db-modal-task-project').textContent = t.projeto_nome || 'N/A';
          document.getElementById('db-modal-task-status').textContent  = t.status;
          document.getElementById('db-modal-task-priority').textContent= t.prioridade;
          document.getElementById('db-modal-task-start').textContent   = t.data_inicio_formatada;
          document.getElementById('db-modal-task-end').textContent     = t.data_fim_formatada;
          document.getElementById('db-modal-task-desc').textContent    = t.descricao || 'Sem descrição.';
          document.getElementById('db-modal-task-link').href = '<?php echo BASE_URL; ?>/projetos/detalhe/' + t.projeto_id + '/tarefas';
          if (t.status !== 'Concluída' && t.status !== 'Cancelada') {
            document.getElementById('db-btn-concluir').classList.remove('hidden');
          }
        }
      })
      .catch(() => dbCloseTaskModal());
  };

  window.dbCloseTaskModal = function() {
    document.getElementById('db-taskModal').style.display = 'none';
  };

  document.getElementById('db-taskModal').addEventListener('click', function(e) {
    if (e.target === this) dbCloseTaskModal();
  });

  window.dbConcluirTarefaAtual = function() {
    if (!dbCurrentTaskId || !confirm('Confirmar conclusão desta tarefa?')) return;
    const fd = new FormData();
    fd.append('id', dbCurrentTaskId);
    fetch('<?php echo BASE_URL; ?>/projetos/concluirTarefaAjax', { method: 'POST', body: fd })
      .then(r => r.json())
      .then(data => { if (data.success) location.reload(); else alert(data.message || 'Erro.'); });
  };

  // ── MAPA DO BRASIL ──
  const standardLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap', maxZoom: 18
  });

  const satelliteLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
    attribution: 'Tiles &copy; Esri &mdash; Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community',
    maxZoom: 18
  });

  const dbMap = L.map('db-brazil-map', { 
    center: [-14.2, -51.9], 
    zoom: 4,
    layers: [standardLayer] // Define a camada padrão inicial
  });

  // Adiciona o controle de camadas no canto superior direito para alternar visualização
  const baseMaps = { "Padrão": standardLayer, "Satélite": satelliteLayer };
  L.control.layers(baseMaps, null, { position: 'topright' }).addTo(dbMap);

  const dbStatusColors = {
    'Em Andamento': '#2ea8e0',
    'Concluído':    '#3fb950',
    'Planejado':    '#e3a11a',
    'Atrasado':     '#f85149',
    'Cancelado':    '#8b949e'
  };

  const dbStatusIconsJS = {
    'Em Andamento': 'bx-time-five',
    'Concluído':    'bx-check-circle',
    'Planejado':    'bx-calendar',
    'Atrasado':     'bx-error-circle',
    'Cancelado':    'bx-x-circle',
    'Rascunho':     'bx-edit',
    'Enviada':      'bx-paper-plane',
    'Rejeitada':    'bx-dislike'
  };

  function dbColorMarker(color, name, type, isNew) {
    const isProposal = type === 'proposta';
    const borderStyle = isProposal ? 'border-style: dashed; border-width: 2px;' : 'border: 2px solid rgba(255,255,255,0.35);';
    const pulseClass = (isNew && !isProposal) ? 'db-pulse-effect' : '';
    return L.divIcon({
      className: '',
      html: `<div style="position:relative">
        <div class="${pulseClass}" style="width:13px;height:13px;background:${color};border-radius:50%;${borderStyle}box-shadow:0 0 10px ${color}88"></div>
        <div style="position:absolute;top:-22px;left:50%;transform:translateX(-50%);background:rgba(22,27,34,0.92);border:1px solid #30363d;padding:2px 6px;border-radius:4px;font-size:9px;font-family:sans-serif;color:#e6edf3;white-space:nowrap;pointer-events:none">${name}</div>
      </div>`,
      iconSize: [13,13], iconAnchor: [6,6]
    });
  }

  // Dados do PHP (projetos com localização)
  const dbProjetos = <?php
    $mapData = [];
    $vinteQuatroHorasAtras = time() - 86400;
    if (!empty($projetosComLocalizacao) && is_array($projetosComLocalizacao)) {
      foreach ($projetosComLocalizacao as $p) {
        if (!empty($p['latitude']) && !empty($p['longitude'])) {
          $mapData[] = [
            'lat'    => (float)$p['latitude'],
            'lng'    => (float)$p['longitude'],
            'name'   => $p['nome'] ?? '',
            'status' => $p['status'] ?? '',
            'client' => $p['cliente_nome'] ?? '',
            'sector' => $p['tipo_servico'] ?? 'Não Definido',
            'type'   => $p['item_tipo'] ?? 'projeto',
            'is_new' => (isset($p['created_at']) && strtotime($p['created_at']) > $vinteQuatroHorasAtras)
          ];
        }
      }
    }
    echo json_encode($mapData);
  ?>;

  const dbStatusColorsJS = <?php echo json_encode(array(
    'Em Andamento'=>'#2ea8e0',
    'Concluído'=>'#3fb950',
    'Planejado'=>'#e3a11a',
    'Atrasado'=>'#f85149',
    'Cancelado'=>'#8b949e',
    'Rascunho'=>'#6b7280',
    'Enviada'=>'#a855f7',
    'Rejeitada'=>'#ef4444'
  )); ?>;

  const markerGroup = L.layerGroup().addTo(dbMap);

  function renderMapMarkers() {
    const sectorFilter = document.getElementById('db-sector-filter').value;
    const typeFilter = document.getElementById('db-type-filter').value;

    const counts = {
        'Em Andamento': 0, 'Concluído': 0, 'Planejado': 0, 'Atrasado': 0,
        'Enviada': 0, 'Rascunho': 0, 'total': 0
    };

    markerGroup.clearLayers();
    dbProjetos.forEach(function(p) {
      if (sectorFilter !== 'all' && p.sector !== sectorFilter) return;
      if (typeFilter !== 'all' && p.type !== typeFilter) return;

      counts.total++;
      if (Object.prototype.hasOwnProperty.call(counts, p.status)) {
          counts[p.status]++;
      }

      const color = dbStatusColorsJS[p.status] || '#8b949e';
      const icon  = dbStatusIconsJS[p.status] || 'bx-circle';
      const typeLabel = p.type === 'proposta' ? '<span style="background:#f3e8ff;color:#9333ea;padding:1px 4px;border-radius:4px;font-size:9px;font-weight:800;margin-right:4px">ORÇAMENTO</span>' : '';

      const marker = L.marker([p.lat, p.lng], { icon: dbColorMarker(color, p.name, p.type, p.is_new) });
      marker.bindPopup(`
        <div style="font-family:sans-serif;min-width:170px;padding:4px">
          <strong style="font-size:13px;color:#111">${typeLabel}${p.name}</strong>${p.is_new ? ' <span class="text-[10px] text-blue-500 font-bold">(RECENTE)</span>' : ''}<br>
          <span style="font-size:11px;color:#555">Cliente: ${p.client}</span><br>
          <span style="display:block;font-size:10px;color:#888;margin-top:2px">Setor: ${p.sector}</span>
          <span style="display:inline-flex;align-items:center;gap:4px;margin-top:6px;padding:2px 8px;border-radius:20px;font-size:10px;font-weight:600;background:${color}22;color:${color};border:1px solid ${color}44"><i class='bx ${icon}'></i>${p.status}</span>
        </div>
      `, { maxWidth: 220 });
      markerGroup.addLayer(marker);
    });

    // Atualiza a UI com as contagens calculadas
    document.getElementById('db-map-total-count').textContent = counts.total;
    document.querySelectorAll('#db-map-legend .db-legend-item').forEach(item => {
        const status = item.getAttribute('data-status');
        const badge = item.querySelector('.db-count-badge');
        if (badge) badge.textContent = counts[status] || 0;
    });
  }

  document.getElementById('db-sector-filter').addEventListener('change', renderMapMarkers);
  document.getElementById('db-type-filter').addEventListener('change', renderMapMarkers);

  renderMapMarkers();

  // ── INDICADORES ──
  const dbFmt = v => parseFloat(v).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
  const dbFmtPct = v => parseFloat(v).toLocaleString('pt-BR', { minimumFractionDigits: 2 }) + '%';

  async function dbFetchBcb(code, elId, type) {
    try {
      const r = await fetch(`https://api.bcb.gov.br/dados/serie/bcdata.sgs.${code}/dados/ultimos/1?formato=json`);
      const d = await r.json();
      if (d && d[0]) {
        document.getElementById(elId).textContent = type === 'money' ? dbFmt(d[0].valor) : dbFmtPct(d[0].valor);
        document.getElementById(elId + '-data').textContent = 'Ref: ' + d[0].data;
      }
    } catch(e) { document.getElementById(elId).textContent = 'Erro'; }
  }

  async function dbFetchDolar() {
    try {
      const r = await fetch('https://economia.awesomeapi.com.br/json/last/USD-BRL');
      const d = await r.json();
      if (d && d.USDBRL) {
        document.getElementById('ind-dolar').textContent = dbFmt(d.USDBRL.ask);
        const dt = new Date(parseInt(d.USDBRL.timestamp) * 1000);
        document.getElementById('ind-dolar-data').textContent = 'Ref: ' + dt.toLocaleDateString('pt-BR');
      }
    } catch(e) { document.getElementById('ind-dolar').textContent = 'Erro'; }
  }

  async function dbFetchBtc() {
    try {
      const r = await fetch('https://api.coingecko.com/api/v3/simple/price?ids=bitcoin&vs_currencies=brl');
      const d = await r.json();
      if (d && d.bitcoin) document.getElementById('ind-bitcoin').textContent = dbFmt(d.bitcoin.brl);
    } catch(e) { document.getElementById('ind-bitcoin').textContent = 'Erro'; }
  }

  async function dbUpdateAll() {
    const icon = document.getElementById('db-refresh-icon');
    const btn  = document.getElementById('db-btn-refresh');
    icon.classList.add('db-spinning'); btn.disabled = true;
    await Promise.all([
      dbFetchBcb(1619, 'ind-salario', 'money'),
      dbFetchBcb(432,  'ind-selic',   'percent'), // Selic (Meta)
      dbFetchDolar(), dbFetchBtc()
    ]);
    setTimeout(() => { icon.classList.remove('db-spinning'); btn.disabled = false; }, 500);
  }

  document.getElementById('db-btn-refresh').addEventListener('click', dbUpdateAll);
  dbUpdateAll();

  // ── GRÁFICOS ──
  document.addEventListener('DOMContentLoaded', function() {
    Chart.defaults.color = '#8b949e';
    Chart.defaults.borderColor = '#30363d';
    Chart.defaults.font.family = 'sans-serif';
    Chart.defaults.font.size = 11;

    // Dados mensais do PHP
    const monthlyData = <?php echo json_encode($monthlySummary ?? []); ?>;
    const labels   = monthlyData.map(item => { const [y,m] = item.mes.split('-'); return new Date(y,m-1).toLocaleString('default',{month:'short',year:'2-digit'}); });
    const receitas = monthlyData.map(item => item.receitas);
    const despesas = monthlyData.map(item => item.despesas);

    new Chart(document.getElementById('receitasDespesasChart'), {
      type: 'line',
      data: {
        labels,
        datasets: [
          { label:'Receitas', data: receitas, borderColor:'#2ea8e0', backgroundColor:'rgba(46,168,224,0.08)', fill:true, tension:0.4, pointRadius:4, pointBackgroundColor:'#2ea8e0' },
          { label:'Despesas', data: despesas, borderColor:'#f85149', backgroundColor:'rgba(248,81,73,0.08)',  fill:true, tension:0.4, pointRadius:4, pointBackgroundColor:'#f85149' }
        ]
      },
      options: {
        responsive:true, maintainAspectRatio:false,
        scales: {
          y: { grid:{color:'#30363d'}, ticks:{ callback: v => 'R$ '+(v/1000).toFixed(0)+'k' } },
          x: { grid:{color:'#30363d'} }
        },
        plugins: { legend:{ labels:{ boxWidth:10, padding:16 } } }
      }
    });

    // Pizza por status
    const projetosStatusData = <?php
      $statusCounts = ['Em Andamento'=>0,'Planejado'=>0,'Concluído'=>0,'Atrasado'=>0];
      if (!empty($allProjetos) && is_array($allProjetos)) {
        foreach ($allProjetos as $p) {
          $s = $p['status'] ?? '';
          if (array_key_exists($s, $statusCounts)) $statusCounts[$s]++;
          elseif ($s === 'Em Execução' || $s === 'Execução') $statusCounts['Em Andamento']++;
        }
      }
      $cd = [];
      foreach ($statusCounts as $s => $c) $cd[] = ['status'=>$s,'total'=>$c];
      echo json_encode($cd);
    ?>;

    new Chart(document.getElementById('projetosStatusChart'), {
      type: 'doughnut',
      data: {
        labels: projetosStatusData.map(i => i.status),
        datasets: [{
          data: projetosStatusData.map(i => i.total),
          backgroundColor: ['#2ea8e0','#8b949e','#3fb950','#f85149'],
          borderWidth: 0, hoverOffset: 6
        }]
      },
      options: {
        responsive:true, maintainAspectRatio:false, cutout:'65%',
        plugins: { legend:{ position:'right', labels:{ boxWidth:10, padding:14 } } }
      }
    });
  });

})();
</script>