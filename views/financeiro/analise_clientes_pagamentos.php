<?php
/* ============================================================
   PARTIAL: Análise de Pontualidade de Clientes
   Arquivo: views/financeiro/analise_clientes_pagamentos.php
   Variável esperada: $analiseClientesPagamentos (array)
   ============================================================ */

$dados = $analiseClientesPagamentos ?? [];

/* ── Extração segura das variáveis do array ── */
$totalClientes        = (int)   ($dados['total_clientes']         ?? 0);
$inadimplencia        = (float) ($dados['inadimplencia_pct']      ?? 0);
$inadimplenciaQtd     = (int)   ($dados['inadimplencia_qtd']      ?? 0);
$antecipacaoPct       = (float) ($dados['antecipacao_pct']        ?? 0);
$antecipacaoQtd       = (int)   ($dados['antecipacao_qtd']        ?? 0);
$pontualidadePct      = (float) ($dados['pontualidade_pct']       ?? 0);
$pontualidadeQtd      = (int)   ($dados['pontualidade_qtd']       ?? 0);
$ticketMedioAtraso    = (float) ($dados['ticket_medio_atraso']    ?? 0);
$ticketMedioAdiantado = (float) ($dados['ticket_medio_adiantado'] ?? 0);
$atrasoMedio          = (int)   ($dados['atraso_medio_dias']      ?? 0);
$antecipacaoMedia     = (int)   ($dados['antecipacao_media_dias'] ?? 0);
$impactoAtraso        = (float) ($dados['impacto_bruto_atraso']   ?? 0);
$impactoAdiantamento  = (float) ($dados['impacto_adiantamento']   ?? 0);
$saldoLiquido         = $impactoAdiantamento - $impactoAtraso;
$saldoPositivo        = $saldoLiquido >= 0;

$maioresDevedores  = $dados['maiores_devedores']  ?? [];
$principaisAdiant  = $dados['principais_adiantadores'] ?? [];

/* ── JSON para o gráfico de barras ── */
$chartLabels   = [];
$chartAtraso   = [];
$chartAdiant   = [];
foreach ($maioresDevedores as $d) {
    $chartLabels[] = htmlspecialchars($d['nome'] ?? '');
    $chartAtraso[] = (float)($d['valor_atraso'] ?? 0);
    $chartAdiant[] = (float)($d['valor_adiantamento'] ?? 0);
}
$chartLabelsJson = json_encode($chartLabels);
$chartAtrasoJson = json_encode($chartAtraso);
$chartAdiantJson = json_encode($chartAdiant);
?>

<!-- ══ ESTILOS DO CARD DE PONTUALIDADE ══════════════════════════ -->
<style>
  /* Card container */
  .acp-card {
    background: var(--fin-surface);
    border: 1px solid var(--fin-border);
    border-radius: 12px;
    margin-bottom: 1rem;
    overflow: hidden;
    box-shadow: var(--fin-shadow);
  }

  /* Header */
  .acp-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 9px 16px;
    border-bottom: 1px solid var(--fin-border);
    flex-wrap: wrap;
    gap: 8px;
  }
  .acp-title {
    display: flex;
    align-items: center;
    gap: 7px;
    font-size: 13px;
    font-weight: 600;
    color: var(--fin-text);
  }
  .acp-title-icon {
    width: 24px; height: 24px;
    border-radius: 7px;
    background: rgba(29,78,216,.1);
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
  }
  .acp-subtitle {
    font-size: 11px;
    font-weight: 400;
    color: var(--fin-muted);
    margin-left: 4px;
  }
  .acp-chips {
    display: flex;
    gap: 6px;
    align-items: center;
    flex-wrap: wrap;
  }
  .acp-chip {
    font-size: 10px;
    font-weight: 600;
    padding: 2px 8px;
    border-radius: 999px;
    white-space: nowrap;
    display: inline-flex;
    align-items: center;
    gap: 3px;
  }
  .acp-chip.red   { background: rgba(220,38,38,.1);   color: #991b1b; border: 1px solid rgba(220,38,38,.25); }
  .acp-chip.green { background: rgba(22,163,74,.1);   color: #15803d; border: 1px solid rgba(22,163,74,.25); }
  .acp-chip.blue  { background: rgba(29,78,216,.1);   color: #1e40af; border: 1px solid rgba(29,78,216,.2); }
  .acp-link {
    font-size: 11px;
    font-weight: 500;
    color: var(--fin-blue);
    text-decoration: none;
    margin-left: 2px;
    white-space: nowrap;
  }
  .acp-link:hover { opacity: .75; }

  /* Body: 4 colunas */
  .acp-body {
    display: grid;
    grid-template-columns: minmax(170px, 1fr) 1.2fr 1fr minmax(180px, 1fr);
    background: var(--fin-surface);
  }
  @media (max-width: 1024px) {
    .acp-body { grid-template-columns: 1fr 1fr; }
    /* Adiciona borda inferior para separar as linhas quando em modo 2x2 */
    .acp-col:nth-child(1), .acp-col:nth-child(2) { border-bottom: 1px solid var(--fin-border); }
    /* Remove a borda direita na segunda coluna de cada linha */
    .acp-col:nth-child(2n) { border-right: none !important; }
  }
  @media (max-width: 640px) {
    .acp-body { grid-template-columns: 1fr; }
    /* Transforma bordas verticais em horizontais para mobile */
    .acp-col { border-right: none !important; border-bottom: 1px solid var(--fin-border); }
    .acp-col:last-child { border-bottom: none; }
  }

  /* Coluna divisória */
  .acp-col {
    padding: 14px 16px;
    border-right: 1.5px solid var(--fin-border);
    transition: background 0.2s ease;
  }
  .acp-col:last-child { border-right: none; }

  /* Efeito Zebra para destaque visual profissional entre as partes */
  .acp-col:nth-child(even) { background-color: rgba(0, 0, 0, 0.01); }
  .dark-theme .acp-col:nth-child(even) { background-color: rgba(255, 255, 255, 0.02); }

  .acp-col:hover { background-color: rgba(0, 0, 0, 0.02); }
  .dark-theme .acp-col:hover { background-color: rgba(255, 255, 255, 0.04); }

  /* Coluna 1 – métricas */
  .acp-metric-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 5px 0;
    border-bottom: 1px solid var(--fin-border);
    font-size: 10.5px;
  }
  .acp-metric-row:last-child { border-bottom: none; }
  .acp-metric-label { color: var(--fin-muted); }
  .acp-metric-val   { font-family: 'IBM Plex Mono', monospace; font-size: 10.5px; font-weight: 600; }
  .acp-metric-val.red   { color: var(--fin-red); }
  .acp-metric-val.green { color: var(--fin-green); }

  /* Colunas 2 e 3 – listas de clientes */
  .acp-col-title {
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .06em;
    color: var(--fin-muted);
    margin-bottom: 7px;
    display: flex;
    align-items: center;
    gap: 5px;
  }
  .acp-client-row, a.acp-client-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 4px 0;
    border-bottom: 1px solid var(--fin-border);
    font-size: 10.5px;
    cursor: pointer;
    transition: background 0.2s;
  }
  .acp-client-row:hover { background: rgba(0,0,0,0.03); }
  .acp-client-row:last-child { border-bottom: none; }
  .acp-client-name {
    color: var(--fin-text);
    flex: 1;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    max-width: 95px;
    padding-right: 6px;
  }
  .acp-client-val {
    font-family: 'IBM Plex Mono', monospace;
    font-size: 10.5px;
    font-weight: 600;
    white-space: nowrap;
  }
  .acp-client-badge {
    font-size: 9px;
    font-weight: 600;
    padding: 1px 6px;
    border-radius: 999px;
    margin-left: 5px;
    white-space: nowrap;
  }
  .acp-client-badge.red   { background: rgba(220,38,38,.1);  color: #991b1b; }
  .acp-client-badge.amber { background: rgba(180,83,9,.12);  color: #92400e; }
  .acp-client-badge.green { background: rgba(22,163,74,.1);  color: #15803d; }

  /* Caixa de saldo líquido */
  .acp-saldo-box {
    margin-top: 8px;
    padding: 8px 10px;
    border-radius: 8px;
    border: 1px solid;
  }
  .acp-saldo-box.pos { background: rgba(22,163,74,.08);  border-color: rgba(22,163,74,.3); }
  .acp-saldo-box.neg { background: rgba(220,38,38,.07);  border-color: rgba(220,38,38,.25); }
  .acp-saldo-box-label {
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .04em;
    margin-bottom: 2px;
  }
  .acp-saldo-box.pos .acp-saldo-box-label { color: #15803d; }
  .acp-saldo-box.neg .acp-saldo-box-label { color: #991b1b; }
  .acp-saldo-box-val {
    font-size: 12px;
    font-weight: 700;
    font-family: 'IBM Plex Mono', monospace;
  }
  .acp-saldo-box.pos .acp-saldo-box-val { color: var(--fin-green); }
  .acp-saldo-box.neg .acp-saldo-box-val { color: var(--fin-red); }
  .acp-saldo-box-sub { font-size: 10px; color: var(--fin-muted); margin-top: 2px; }

  /* Coluna 4 – gráfico */
  .acp-chart-wrap { position: relative; width: 100%; height: 160px; margin-top: 5px; }
  .acp-legend {
    display: flex;
    gap: 12px;
    margin-top: 8px;
    justify-content: center;
    flex-wrap: wrap;
  }
  .acp-legend-item {
    display: flex;
    align-items: center;
    gap: 3px;
    font-size: 10px;
    color: var(--fin-muted);
  }
  .acp-legend-dot {
    width: 8px; height: 8px;
    border-radius: 2px;
    display: inline-block;
    flex-shrink: 0;
  }

  /* Estado vazio */
  .acp-empty {
    padding: 2rem;
    text-align: center;
    font-size: 12px;
    color: var(--fin-muted);
    font-style: italic;
  }

  /* Dark mode */
  .dark-theme .acp-chip.red   { background: rgba(220,38,38,.15); color: #fca5a5; border-color: rgba(220,38,38,.3); }
  .dark-theme .acp-chip.green { background: rgba(22,163,74,.15); color: #86efac; border-color: rgba(22,163,74,.3); }
  .dark-theme .acp-chip.blue  { background: rgba(29,78,216,.2);  color: #93c5fd; border-color: rgba(29,78,216,.3); }
  .dark-theme .acp-client-badge.red   { background: rgba(220,38,38,.2); color: #fca5a5; }
  .dark-theme .acp-client-badge.amber { background: rgba(180,83,9,.2);  color: #fcd34d; }
  .dark-theme .acp-client-badge.green { background: rgba(22,163,74,.2); color: #86efac; }
  .dark-theme .acp-saldo-box.neg { background: rgba(220,38,38,.12); border-color: rgba(220,38,38,.3); }
  .dark-theme .acp-saldo-box.pos { background: rgba(22,163,74,.12); border-color: rgba(22,163,74,.3); }
</style>

<!-- ══ CARD ══════════════════════════════════════════════════════ -->
<?php if (empty($dados)): ?>
  <!-- sem dados: oculta o card silenciosamente -->
<?php else: ?>
<div class="acp-card">

  <!-- Header ---------------------------------------------------- -->
  <div class="acp-header">
    <div class="acp-title">
      <div class="acp-title-icon">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#1d4ed8" stroke-width="2.5">
          <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
        </svg>
      </div>
      Análise de pontualidade de clientes
      <span class="acp-subtitle">— <?= $totalClientes ?> clientes analisados</span>
    </div>

    <div class="acp-chips">
      <span class="acp-chip red">
        <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M12 19V5M5 12l7 7 7-7"/></svg>
        Inadimplência <?= number_format($inadimplencia, 1) ?>% (<?= $inadimplenciaQtd ?>)
      </span>
      <span class="acp-chip green">
        <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M12 5v14M5 12l7-7 7 7"/></svg>
        Antecipação <?= number_format($antecipacaoPct, 1) ?>% (<?= $antecipacaoQtd ?>)
      </span>
      <span class="acp-chip blue">
        Pontualidade <?= number_format($pontualidadePct, 1) ?>% (<?= $pontualidadeQtd ?>)
      </span>
      <a href="<?= BASE_URL ?>/financeiro/pontualidade" class="acp-link">
        Ver relatório →
      </a>
    </div>
  </div>

  <!-- Body ------------------------------------------------------ -->
  <div class="acp-body">

    <!-- Col 1: KPIs internos ------------------------------------ -->
    <div class="acp-col">
      <div class="acp-metric-row">
        <span class="acp-metric-label">Ticket médio (atraso)</span>
        <span class="acp-metric-val red">R$ <?= number_format($ticketMedioAtraso, 2, ',', '.') ?></span>
      </div>
      <div class="acp-metric-row">
        <span class="acp-metric-label">Ticket médio (adiantado)</span>
        <span class="acp-metric-val green">R$ <?= number_format($ticketMedioAdiantado, 2, ',', '.') ?></span>
      </div>
      <div class="acp-metric-row">
        <span class="acp-metric-label">Atraso médio</span>
        <span class="acp-metric-val red"><?= $atrasoMedio ?> dias</span>
      </div>
      <div class="acp-metric-row">
        <span class="acp-metric-label">Antecipação média</span>
        <span class="acp-metric-val green"><?= $antecipacaoMedia ?> dias</span>
      </div>
      <div class="acp-metric-row">
        <span class="acp-metric-label">Impacto bruto atraso</span>
        <span class="acp-metric-val red">R$ <?= number_format($impactoAtraso, 2, ',', '.') ?></span>
      </div>
      <div class="acp-metric-row">
        <span class="acp-metric-label">Impacto adiantamento</span>
        <span class="acp-metric-val green">R$ <?= number_format($impactoAdiantamento, 2, ',', '.') ?></span>
      </div>
    </div>

    <!-- Col 2: Maiores devedores -------------------------------- -->
    <div class="acp-col">
      <div class="acp-col-title">
        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2.5">
          <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
          <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
        </svg>
        Maiores devedores
      </div>

      <?php if (!empty($maioresDevedores)): ?>
        <?php foreach ($maioresDevedores as $d):
          $diasAtraso = (int)($d['dias_atraso'] ?? 0);
          $badgeClass = $diasAtraso > 30 ? 'amber' : 'red';
        ?>
        <a href="<?= BASE_URL ?>/financeiro/receber?descricao_filtro=<?= urlencode($d['nome']) ?>" class="acp-client-row" style="text-decoration:none">
          <span class="acp-client-name" title="<?= htmlspecialchars($d['nome'] ?? '') ?>">
            <?= htmlspecialchars($d['nome'] ?? '') ?>
          </span>
          <span class="acp-client-val" style="color:var(--fin-red)">
            R$ <?= number_format($d['valor_atraso'] ?? 0, 2, ',', '.') ?>
          </span>
          <span class="acp-client-badge <?= $badgeClass ?>">
            <?= $diasAtraso ?> d
          </span>
        </a>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="acp-empty">Nenhum devedor registrado.</div>
      <?php endif; ?>
    </div>

    <!-- Col 3: Principais adiantadores ------------------------- -->
    <div class="acp-col">
      <div class="acp-col-title">
        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2.5">
          <path d="M22 11.08V12a10 10 0 11-5.93-9.14"/>
          <polyline points="22 4 12 14.01 9 11.01"/>
        </svg>
        Principais adiantadores
      </div>

      <?php if (!empty($principaisAdiant)): ?>
        <?php foreach ($principaisAdiant as $a): ?>
        <a href="<?= BASE_URL ?>/financeiro/receber?descricao_filtro=<?= urlencode($a['nome']) ?>" class="acp-client-row" style="text-decoration:none">
          <span class="acp-client-name" title="<?= htmlspecialchars($a['nome'] ?? '') ?>">
            <?= htmlspecialchars($a['nome'] ?? '') ?>
          </span>
          <span class="acp-client-val" style="color:var(--fin-green)">
            R$ <?= number_format($a['valor_adiantamento'] ?? 0, 2, ',', '.') ?>
          </span>
          <span class="acp-client-badge green">
            <?= (int)($a['dias_adiantamento'] ?? 0) ?> d
          </span>
        </a>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="acp-empty">Nenhum adiantamento registrado.</div>
      <?php endif; ?>

      <!-- Saldo líquido de impacto -->
      <div class="acp-saldo-box <?= $saldoPositivo ? 'pos' : 'neg' ?>">
        <div class="acp-saldo-box-label">Saldo líquido de impacto</div>
        <div class="acp-saldo-box-val">
          <?= $saldoPositivo ? '+' : '−' ?> R$ <?= number_format(abs($saldoLiquido), 2, ',', '.') ?>
        </div>
        <div class="acp-saldo-box-sub">
          <?= $saldoPositivo ? 'Impacto positivo de caixa' : 'Impacto negativo de caixa' ?>
        </div>
      </div>
    </div>

    <!-- Col 4: Mini gráfico ------------------------------------- -->
    <div class="acp-col" style="border-right:none">
      <div class="acp-col-title">
        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
          <line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/>
          <line x1="6" y1="20" x2="6" y2="14"/>
        </svg>
        Distribuição por cliente
      </div>

      <div class="acp-chart-wrap">
        <canvas id="acpPontChart"
                role="img"
                aria-label="Gráfico comparativo de atraso e adiantamento por cliente">
          <?php foreach ($maioresDevedores as $d): ?>
            <?= htmlspecialchars($d['nome'] ?? '') ?>: atraso R$ <?= number_format($d['valor_atraso'] ?? 0, 2, ',', '.') ?>.
          <?php endforeach; ?>
        </canvas>
      </div>

      <div class="acp-legend">
        <span class="acp-legend-item">
          <span class="acp-legend-dot" style="background:rgba(220,38,38,.65)"></span>
          Atraso
        </span>
        <span class="acp-legend-item">
          <span class="acp-legend-dot" style="background:rgba(22,163,74,.65)"></span>
          Adiantamento
        </span>
      </div>
    </div>

  </div><!-- /.acp-body -->
</div><!-- /.acp-card -->

<!-- ══ SCRIPT DO GRÁFICO ════════════════════════════════════════ -->
<script>
(function () {
  const labels = <?= $chartLabelsJson ?>;
  const atraso = <?= $chartAtrasoJson ?>;
  const adiant = <?= $chartAdiantJson ?>;

  if (!labels.length) return;

  const ctx = document.getElementById('acpPontChart');
  if (!ctx) return;

  new Chart(ctx, {
    type: 'bar',
    data: {
      labels: labels,
      datasets: [
        {
          label: 'Atraso',
          data: atraso,
          backgroundColor: 'rgba(220,38,38,.55)',
          borderColor: '#dc2626',
          borderWidth: 1,
          borderRadius: 3,
          borderSkipped: false
        },
        {
          label: 'Adiantamento',
          data: adiant,
          backgroundColor: 'rgba(22,163,74,.55)',
          borderColor: '#16a34a',
          borderWidth: 1,
          borderRadius: 3,
          borderSkipped: false
        }
      ]
    },
    options: {
      indexAxis: 'y',
      responsive: true,
      maintainAspectRatio: false,
      layout: {
        padding: {
          top: 5,
          bottom: 0,
          left: 5
        }
      },
      onClick: (evt, elements) => {
        if (elements.length > 0) {
          const index = elements[0].index;
          const clientName = labels[index];
          // Redireciona para a lista de recebimentos filtrando pelo nome do cliente
          window.location.href = `<?= BASE_URL ?>/financeiro/receber?descricao_filtro=${encodeURIComponent(clientName)}`;
        }
      },
      onHover: (evt, elements) => {
        evt.native.target.style.cursor = elements.length > 0 ? 'pointer' : 'default';
      },
      plugins: {
        legend: { display: false },
        tooltip: {
          callbacks: {
            title: (items) => labels[items[0].dataIndex],
            label: c => c.dataset.label + ': R$ ' +
              c.parsed.x.toLocaleString('pt-BR', { minimumFractionDigits: 2 })
          }
        }
      },
      scales: {
        y: {
          grid: { display: false },
          ticks: {
            font: { size: 9 },
            color: '#9ca3af',
            autoSkip: false,
            callback: function(val, index) {
              const label = labels[index];
              return label.length > 20 ? label.substring(0, 17) + '...' : label;
            }
          }
        },
        x: {
          beginAtZero: true,
          grid: { color: 'rgba(0,0,0,.04)' },
          ticks: {
            font: { size: 9 },
            color: '#9ca3af',
            maxTicksLimit: 3,
            callback: v => 'R$' + Intl.NumberFormat('pt-BR', { notation: 'compact', maximumFractionDigits: 1 }).format(v)
          }
        }
      },
      datasets: {
        bar: {
          barPercentage: 0.7,
          categoryPercentage: 0.8
        }
      }
    }
  });
})();
</script>
<?php endif; ?>
