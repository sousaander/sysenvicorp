<!-- Google Fonts: DM Mono para números e Plus Jakarta para textos -->
<link href="https://fonts.googleapis.com/css2?family=DM+Mono:wght@400;500&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<!-- Chart.js Library com fallback para CDN caso o arquivo local falhe (404) -->
<script src="<?= BASE_URL; ?>/assets/js/chart.umd.min.js" onerror="this.onerror=null; this.src='https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js';"></script>

<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>

<style>
    .balanco-wrap {
        --fin-font: 'Plus Jakarta Sans', sans-serif;
        --fin-mono: 'DM Mono', monospace;
        font-family: var(--fin-font);
        letter-spacing: -0.015em;
    }
    .bal-kpi-card {
        background: var(--sys-surface);
        border: 1px solid var(--sys-border);
        border-radius: 10px;
        padding: 0.6rem 0.85rem;
        display: flex;
        flex-direction: column;
        justify-content: center;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        min-height: 75px;
    }
    .bal-kpi-card:hover {
        border-color: var(--sys-blue);
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }
    .bal-kpi-label {
        font-size: 0.58rem;
        font-weight: 800;
        color: var(--sys-text-muted);
        text-transform: uppercase;
        letter-spacing: 0.08em;
        margin-bottom: 0.15rem;
    }
    .bal-kpi-value {
        font-family: var(--fin-mono);
        font-size: 1.05rem;
        font-weight: 600;
        letter-spacing: -0.04em;
    }
    .bal-kpi-sublabel {
        font-size: 0.58rem;
        font-weight: 600;
        color: var(--sys-text-muted);
        margin-top: 0.15rem;
    }
    .bal-btn-action {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        height: 30px;
        padding: 0 0.6rem;
        font-size: 0.65rem;
        font-weight: 700;
        border-radius: 10px;
        transition: all 0.2s;
        gap: 0.4rem;
    }
    .currency-prefix { font-size: 0.7em; font-weight: 400; opacity: 0.6; margin-right: 2px; }
    .chart-container-premium {
        background: var(--sys-surface);
        border: 1px solid var(--sys-border);
        border-radius: 10px;
        padding: 8px 12px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.02);
    }
    .progress-track {
        background-color: var(--sys-surface-alt);
        height: 3px;
        border-radius: 10px;
        overflow: hidden;
    }
    .progress-fill {
        height: 100%;
        border-radius: 10px;
        transition: width 1s ease-in-out;
    }
    /* Tabela Refinada - Estética Contábil */
    .balanco-table th {
        background-color: var(--sys-surface-alt) !important;
        font-weight: 700;
        color: var(--sys-text-muted) !important;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        font-size: 0.65rem;
        padding: 12px 16px;
        border-bottom: 2px solid var(--sys-border);
    }
    .balanco-table td {
        padding: 10px 16px;
        border-bottom: 1px solid var(--sys-border);
        font-size: 0.75rem;
        line-height: 1.4;
        vertical-align: middle;
    }
    .balanco-table td.font-mono-val {
        font-family: var(--fin-mono);
        font-size: 0.8rem;
        letter-spacing: -0.02em;
    }
    .balanco-table tbody tr:hover {
        background-color: var(--sys-surface-alt);
    }
    .bal-row-current td:first-child {
        border-left: 4px solid #2563eb !important;
    }
    .meta-badge {
        font-size: 0.52rem;
        padding: 1px 5px;
        border-radius: 4px;
        background: var(--sys-blue-light);
        color: var(--sys-blue);
        font-weight: 800;
    }
    /* Estilo para fontes Serifadas (Courier New) nos cards solicitados */
    .bal-card-serif,
    .bal-card-serif *:not(i):not(svg) {
        font-family: 'courier new', Times, serif !important;
    }
</style>

<div class="balanco-wrap w-full">
<?php
if (isset($_SESSION['flash_message'])):
    $message = $_SESSION['flash_message'];
    unset($_SESSION['flash_message']);
    $type_classes = $message['type'] === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-700' : 'bg-rose-50 border-rose-200 text-rose-700';
?>
    <div class="border px-4 py-3 rounded-xl relative mb-6 <?= $type_classes ?> animate-fade-in">
        <span class="text-sm font-medium"><?= htmlspecialchars($message['message']) ?></span>
    </div>
<?php endif; ?>

<?php
// Cálculos e Preparação de Dados
$totalReceitas = array_sum(array_column($balancoMensal, 'receitas_realizadas'));
$totalDespesas = array_sum(array_column($balancoMensal, 'despesas_realizadas'));
$resultadoPeriodo = $totalReceitas - $totalDespesas;

$totalReceitasProjetadas = array_sum(array_column($balancoMensal, 'receitas_previstas'));
$totalDespesasProjetadas = array_sum(array_column($balancoMensal, 'despesas_previstas'));
$resultadoPeriodoProjetado = $totalReceitasProjetadas - $totalDespesasProjetadas;

// Análise de Metas Mensais (Performance técnica)
$mesesMetaAtingida = 0;
$mesesAnalisados = 0;
$mesAtualCompare = date('Y-m');

if (!empty($balancoMensal)) {
    foreach ($balancoMensal as $m) {
        $mesAnoCompare = $m['mes'];
        // Só analisamos meses que já passaram ou o atual para o consolidado
        if ($mesAnoCompare <= $mesAtualCompare) {
            $mesesAnalisados++;
            $resReal = $m['receitas_realizadas'] - $m['despesas_realizadas'];
            if ($resReal >= $metaMensal) {
                $mesesMetaAtingida++;
            }
        }
    }
}
$pctMetaMensal = $mesesAnalisados > 0 ? ($mesesMetaAtingida / $mesesAnalisados) * 100 : 0;

// Preparação de dados para o gráfico
$chartLabels = [];
$chartDataReal = [];
$chartDataProjetado = [];
$chartDataMeta = []; 
$meses_pt_chart = [1 => 'Jan', 2 => 'Fev', 3 => 'Mar', 4 => 'Abr', 5 => 'Mai', 6 => 'Jun', 7 => 'Jul', 8 => 'Ago', 9 => 'Set', 10 => 'Out', 11 => 'Nov', 12 => 'Dez'];

if (!empty($balancoMensal)) {
    foreach ($balancoMensal as $m) {
        $ts = strtotime($m['mes'] . '-01');
        $mesNum = (int)date('n', $ts);
        $chartLabels[] = $meses_pt_chart[$mesNum] . '/' . date('y', $ts);
        $chartDataReal[] = $m['receitas_realizadas'] - $m['despesas_realizadas'];
        $chartDataProjetado[] = $m['receitas_previstas'] - $m['despesas_previstas'];
        $chartDataMeta[] = $metaMensal; 
    }
}
?>

<div class="flex flex-col sm:flex-row items-center justify-between mb-4 gap-4">
    <div class="flex items-center gap-4">
        <h2 class="text-base font-bold text-gray-900 dark:text-white tracking-tight">Balanço Financeiro</h2>
        <form action="<?= BASE_URL; ?>/financeiro/balanco" method="GET" class="flex items-center bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-md px-1.5 py-0 shadow-sm">
            <select name="ano" id="ano" onchange="this.form.submit()" class="bg-transparent border-none text-xs font-bold text-blue-600 focus:ring-0 cursor-pointer py-1">
                <?php
                $anoAtual = date('Y');
                for ($i = $anoAtual - 2; $i <= $anoAtual + 2; $i++) {
                    $selected = ($i == $anoSelecionado) ? 'selected' : '';
                    echo "<option value='$i' $selected>$i</option>";
                }
                ?>
            </select>
        </form>
    </div>

    <div class="flex items-center gap-2">
        <div class="flex items-center bg-gray-100 dark:bg-gray-800 p-0.5 rounded-lg">
            <a href="<?= BASE_URL; ?>/financeiro/exportarBalancoPdf?ano=<?= $anoSelecionado ?>" target="_blank" class="bal-btn-action text-gray-600 hover:bg-white dark:hover:bg-gray-700 hover:text-rose-600" title="Relatório PDF">
                <i class='bx bxs-file-pdf text-base'></i>
            </a>
            <a href="<?= BASE_URL; ?>/financeiro/dre?ano=<?= $anoSelecionado ?>" class="bal-btn-action text-gray-600 hover:bg-white dark:hover:bg-gray-700 hover:text-indigo-600" title="Demonstrativo de Resultado">
                <i class='bx bx-list-check text-base'></i>
            </a>
            <button type="button" id="btnOpenEmailModal" class="bal-btn-action text-gray-600 hover:bg-white dark:hover:bg-gray-700" title="Enviar balanço">
                <i class='bx bx-envelope text-base'></i>
            </button>
            <button type="button" id="btnOpenMetaModal" class="bal-btn-action text-gray-600 hover:bg-white dark:hover:bg-gray-700" title="Configurar metas">
                <i class='bx bx-target-lock text-base'></i>
            </button>
        </div>
        <a href="<?= BASE_URL; ?>/financeiro" class="bal-btn-action bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-50">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
            </svg>
            <span>Voltar</span>
        </a>
    </div>
</div>

<!-- Panorâmica de KPIs Superiores -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 mb-4">
    <div class="bal-kpi-card border-l-4 border-l-blue-500 shadow-sm">
        <span class="bal-kpi-label">Saldo em Caixa</span>
        <div class="bal-kpi-value text-blue-600">
            <span class="currency-prefix">R$</span><?= number_format($saldoAtual, 2, ',', '.'); ?>
        </div>
        <p class="bal-kpi-sublabel">Disponível hoje</p>
    </div>

    <div class="bal-kpi-card border-l-4 border-l-emerald-500 shadow-sm bal-card-serif">
        <span class="bal-kpi-label">Receitas Realizadas</span>
        <div class="bal-kpi-value text-emerald-600">
            <span class="currency-prefix">R$</span><?= number_format($totalReceitas, 2, ',', '.'); ?>
        </div>
        <p class="bal-kpi-sublabel text-gray-500 dark:text-gray-400">R$ <?= number_format($totalReceitasProjetadas, 2, ',', '.'); ?> projetado</p>
    </div>

    <div class="bal-kpi-card border-l-4 border-l-rose-500 shadow-sm bal-card-serif">
        <span class="bal-kpi-label">Despesas Realizadas</span>
        <div class="bal-kpi-value text-rose-600">
            <span class="currency-prefix">R$</span><?= number_format($totalDespesas, 2, ',', '.'); ?>
        </div>
        <p class="bal-kpi-sublabel text-gray-500 dark:text-gray-400">R$ <?= number_format($totalDespesasProjetadas, 2, ',', '.'); ?> projetado</p>
    </div>

    <?php $corRes = $resultadoPeriodo >= 0 ? 'indigo' : 'rose'; ?>
    <div class="bal-kpi-card border-l-4 border-l-<?= $corRes ?>-500 shadow-sm bal-card-serif">
        <span class="bal-kpi-label">Resultado do Período</span>
        <div class="bal-kpi-value text-<?= $corRes ?>-600">
            <?= $resultadoPeriodo >= 0 ? '+' : ''; ?> <span class="currency-prefix">R$</span><?= number_format($resultadoPeriodo, 2, ',', '.'); ?>
        </div>
        <p class="bal-kpi-sublabel text-gray-500 dark:text-gray-400">Projeção 12 meses: R$ <?= number_format($saldoProjetado, 0, ',', '.'); ?></p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-4">
    <!-- Gráfico de Comparação (Esquerda - 2 colunas) -->
    <div class="lg:col-span-2 chart-container-premium flex flex-col min-h-[240px]">
        <div class="mb-1.5">
            <h3 class="text-[9px] font-extrabold text-gray-400 dark:text-gray-500 uppercase tracking-widest">Performance Mensal: Realizado vs. Projeção</h3>
        </div>
        <div class="flex-1 w-full h-full">
            <canvas id="balancoChart" style="display: block; width: 100% !important;"></canvas>
        </div>
    </div>

    <!-- Painel Lateral de Utilidades (Direita - 1 coluna) -->
    <div class="lg:col-span-1 flex flex-col gap-3">
        <!-- Card 1: Execução Orçamentária -->
        <div class="bal-kpi-card py-2.5 bal-card-serif">
            <h4 class="text-[8px] font-extrabold text-gray-500 dark:text-white mb-2.5 flex items-center gap-2 uppercase tracking-wide">
                <i class='bx bx-pie-chart-alt-2 text-blue-500'></i> Execução Orçamentária
            </h4>
            
            <div class="mb-1.5">
                <div class="flex justify-between items-end mb-0.5">
                    <span class="text-[8px] font-bold text-gray-400 uppercase">Receitas</span>
                    <span class="text-[9px] font-extrabold text-emerald-600"><?= number_format(($totalReceitas / ($totalReceitasProjetadas ?: 1)) * 100, 1) ?>%</span>
                </div>
                <div class="progress-track">
                    <div class="progress-fill bg-emerald-500" style="width: <?= min(100, ($totalReceitas / ($totalReceitasProjetadas ?: 1)) * 100) ?>%"></div>
                </div>
                <div class="flex justify-between mt-1 items-center">
                    <span class="text-[10px] font-bold text-emerald-700 dark:text-emerald-400">R$ <?= number_format($totalReceitas, 2, ',', '.') ?></span>
                    <span class="text-[8px] text-gray-400 font-medium opacity-50">Alvo: R$ <?= number_format($totalReceitasProjetadas, 2, ',', '.') ?></span>
                </div>
            </div>

            <div class="mb-1.5">
                <div class="flex justify-between items-end mb-0.5">
                    <span class="text-[8px] font-bold text-gray-400 uppercase">Despesas</span>
                    <span class="text-[9px] font-extrabold text-red-600"><?= number_format(($totalDespesas / ($totalDespesasProjetadas ?: 1)) * 100, 1) ?>%</span>
                </div>
                <div class="progress-track">
                    <div class="progress-fill bg-red-500" style="width: <?= min(100, ($totalDespesas / ($totalDespesasProjetadas ?: 1)) * 100) ?>%"></div>
                </div>
                <div class="flex justify-between mt-1 items-center">
                    <span class="text-[10px] font-bold text-red-700 dark:text-red-400">R$ <?= number_format($totalDespesas, 2, ',', '.') ?></span>
                    <span class="text-[8px] text-gray-400 font-medium opacity-50">Alvo: R$ <?= number_format($totalDespesasProjetadas, 2, ',', '.') ?></span>
                </div>
            </div>

            <!-- Performance de Meta Mensal -->
            <div class="mt-3 pt-2.5 border-t border-gray-100 dark:border-gray-700">
                <div class="flex justify-between items-center mb-1">
                    <span class="text-[8px] font-bold text-gray-400 uppercase">Meta de Resultado</span>
                    <span class="meta-badge">R$ <?= number_format($metaMensal, 2, ',', '.') ?>/mês</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="flex-1">
                        <div class="progress-track">
                            <div class="progress-fill bg-indigo-500" style="width: <?= $pctMetaMensal ?>%"></div>
                        </div>
                    </div>
                    <span class="text-[10px] font-bold text-indigo-600"><?= number_format($pctMetaMensal, 0) ?>%</span>
                </div>
                <p class="text-[7.5px] text-gray-400 font-medium mt-1">
                    <?= $mesesMetaAtingida ?> de <?= $mesesAnalisados ?> meses analisados
                </p>
            </div>
        </div>

        <!-- Card 2: Saldo Acumulado e Liquidez -->
        <div class="bal-kpi-card bg-gray-50/50 dark:bg-gray-800/30 py-2.5 bal-card-serif">
            <h4 class="text-[8px] font-bold text-gray-400 dark:text-gray-500 mb-2 uppercase tracking-wider">
                Projeção — Próximos 12 meses
            </h4>
            
            <div class="flex justify-between items-center mb-1.5">
                <span class="text-[10px] font-bold text-gray-500">Entradas Futuras</span>
                <span class="text-xs font-bold text-emerald-600/80 font-mono tracking-tight">
                    <?= number_format($previsaoRecebimento ?? 0, 2, ',', '.') ?>
                </span>
            </div>
            
            <div class="flex justify-between items-center mb-2.5">
                <span class="text-[10px] font-bold text-gray-500">Saídas Futuras</span>
                <span class="text-xs font-bold text-rose-600/80 tracking-tight font-mono">
                    − <?= number_format($previsaoPagamento ?? 0, 2, ',', '.') ?>
                </span>
            </div>

            <div class="pt-2.5 border-t border-gray-200 dark:border-gray-700 flex justify-between items-center">
                <span class="text-[9px] font-extrabold text-gray-900 dark:text-white uppercase">Saldo Final</span>
                <span class="text-base font-black px-2 py-0.5 rounded-lg <?= ($saldoProjetado ?? 0) >= 0 ? 'text-blue-700 bg-blue-50 dark:bg-blue-900/20' : 'text-rose-700 bg-rose-50 dark:bg-rose-900/20' ?> tracking-tighter font-mono shadow-sm">
                    <?= number_format($saldoProjetado ?? 0, 2, ',', '.') ?>
                </span>
            </div>
        </div>
    </div>
</div>

<!-- Tabela Detalhada -->
<div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden shadow-sm">
    <div class="overflow-x-auto">
        <table class="balanco-table w-full border-collapse">
            <thead>
                <tr class="divide-x divide-gray-200/50 dark:divide-gray-700/50">
                    <th rowspan="2" class="text-left align-bottom">Mês de Referência</th>
                    <th colspan="2" class="text-center">Fluxo: Receitas</th>
                    <th colspan="2" class="text-center">Fluxo: Despesas</th>
                    <th colspan="2" class="text-center">Resultado Líquido</th>
                    <th rowspan="2" class="text-center align-bottom">Ciclo</th>
                </tr>
                <tr class="divide-x divide-gray-200/50 dark:divide-gray-700/50">
                    <th class="text-right">Realizado</th>
                    <th class="text-right text-[9px] opacity-60">Alvo</th>
                    <th class="text-right">Realizado</th>
                    <th class="text-right text-[9px] opacity-60">Alvo</th>
                    <th class="text-right">Valor</th>
                    <th class="text-right text-[9px] opacity-60">Previsto</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700 text-gray-700 dark:text-gray-300">
                <?php if (!empty($balancoMensal)): ?>
                    <!-- Linha de Saldo Inicial (Transporte do Ano Anterior) -->
                    <tr class="bg-gray-50/30 dark:bg-gray-900/10 italic">
                        <td class="whitespace-nowrap font-medium text-gray-500 dark:text-gray-400">
                            Saldo Inicial (Acumulado <?= $anoSelecionado - 1 ?>)
                        </td>
                        <td colspan="4" class="border-r dark:border-gray-700"></td>
                        <td class="text-right font-bold text-gray-500 dark:text-gray-400 font-mono-val tabular-nums border-l border-gray-100 dark:border-gray-700">
                            <?= number_format($saldoInicioAno, 2, ',', '.'); ?>
                        </td>
                        <td class="text-right font-medium text-gray-400 dark:text-gray-500 font-mono-val tabular-nums border-l border-gray-100 dark:border-gray-700">
                            <?= number_format($saldoInicioAno, 2, ',', '.'); ?>
                        </td>
                        <td class="text-center">
                            <span class="px-2 py-0.5 text-[8.5px] font-bold uppercase rounded-full bg-gray-100 text-gray-500">Abertura</span>
                        </td>
                    </tr>
                    <?php
                    $meses_pt = [1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril', 5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto', 9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'];
                    ?>
                    <?php foreach ($balancoMensal as $mes): ?>
                        <?php
                        $resultadoReal = $mes['receitas_realizadas'] - $mes['despesas_realizadas'];
                        $resultadoPrevisto = $mes['receitas_previstas'] - $mes['despesas_previstas'];
                        $mesAno = date('m/Y', strtotime($mes['mes'] . '-01'));
                        $mesAtual = date('m/Y');
                        $isPassado = strtotime($mes['mes'] . '-01') < strtotime(date('Y-m-01'));
                        ?>
                        <tr class="bal-table-row <?= ($mesAno == $mesAtual) ? 'bal-row-current bg-blue-50/80 dark:bg-blue-900/30' : ''; ?> transition-colors">
                            <td class="whitespace-nowrap font-semibold text-gray-800 dark:text-white relative">
                                <div class="flex items-center gap-3">
                                    <?php
                                    $ts = strtotime($mes['mes'] . '-01');
                                    echo $meses_pt[(int)date('n', $ts)] . ' ' . date('Y', $ts);
                                    if ($mesAno == $mesAtual): ?>
                                        <span class="flex h-1.5 w-1.5 rounded-full bg-blue-600 animate-pulse"></span>
                                        <span class="text-[10px] font-black text-blue-600 uppercase tracking-tighter">Hoje</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="whitespace-nowrap text-right text-emerald-600 dark:text-emerald-400 font-bold border-l border-gray-100 dark:border-gray-700 font-mono-val tabular-nums">
                                <?= number_format($mes['receitas_realizadas'], 2, ',', '.'); ?>
                            </td>
                            <td class="whitespace-nowrap text-right text-gray-400 dark:text-gray-500 border-r dark:border-gray-700 font-medium font-mono-val tabular-nums">
                                <?= number_format($mes['receitas_previstas'], 2, ',', '.'); ?>
                            </td>
                            <td class="whitespace-nowrap text-right text-rose-600 dark:text-rose-400 font-bold font-mono-val tabular-nums border-l border-gray-100 dark:border-gray-700">
                                <?= number_format($mes['despesas_realizadas'], 2, ',', '.'); ?>
                            </td>
                            <td class="whitespace-nowrap text-right text-gray-400 dark:text-gray-500 border-r dark:border-gray-700 font-medium font-mono-val tabular-nums">
                                <?= number_format($mes['despesas_previstas'], 2, ',', '.'); ?>
                            </td>
                            <td class="whitespace-nowrap text-right font-black <?= $resultadoReal >= 0 ? 'text-blue-700 dark:text-blue-400' : 'text-rose-600'; ?> font-mono-val tabular-nums border-l border-gray-100 dark:border-gray-700">
                                <div class="flex items-center justify-end gap-1.5">
                                    <?= number_format($resultadoReal, 2, ',', '.'); ?>
                                    <?php if ($isPassado && isset($metaMensal) && $metaMensal > 0 && $resultadoReal < $metaMensal): ?>
                                        <i class='bx bx-trending-down text-rose-500 text-xs' title="Abaixo da Meta"></i>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="whitespace-nowrap text-right font-semibold text-gray-400 dark:text-gray-500 font-mono-val tabular-nums">
                                <?= number_format($resultadoPrevisto, 2, ',', '.'); ?>
                            </td>
                            <td class="whitespace-nowrap text-center">
                                <span class="px-2 py-0.5 text-[8.5px] font-bold uppercase rounded-full <?= ($mesAno == $mesAtual) ? 'bg-blue-100 text-blue-800' : ($isPassado ? 'bg-gray-100 text-gray-600' : 'bg-amber-100 text-amber-800'); ?>">
                                    <?= ($mesAno == $mesAtual) ? 'Vigente' : ($isPassado ? 'Concluído' : 'Projetado'); ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="px-4 py-10 text-center text-gray-500">
                            Nenhum dado financeiro encontrado para o período.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
            <tfoot class="bg-gray-50 dark:bg-gray-900/50 border-t border-gray-200 dark:border-gray-700">
                <tr>
                    <td class="text-left font-bold text-gray-900 dark:text-white uppercase tracking-wider py-4">Consolidado Anual</td>
                    <td class="text-right text-emerald-600 font-black font-mono-val tabular-nums py-4"><?= number_format($totalReceitas, 2, ',', '.'); ?></td>
                    <td class="text-right text-gray-400 font-bold font-mono-val tabular-nums py-4"><?= number_format($totalReceitasProjetadas, 2, ',', '.'); ?></td>
                    <td class="text-right text-rose-600 font-black font-mono-val tabular-nums py-4"><?= number_format($totalDespesas, 2, ',', '.'); ?></td>
                    <td class="text-right text-gray-400 font-bold font-mono-val tabular-nums py-4"><?= number_format($totalDespesasProjetadas, 2, ',', '.'); ?></td>
                    <td class="text-right font-black <?= $resultadoPeriodo >= 0 ? 'text-blue-700 dark:text-blue-400' : 'text-rose-600'; ?> font-mono-val tabular-nums py-4">
                        <?= number_format($resultadoPeriodo, 2, ',', '.'); ?>
                    </td>
                    <td class="text-right text-gray-400 font-black font-mono-val tabular-nums py-4"><?= number_format($resultadoPeriodoProjetado, 2, ',', '.'); ?></td>
                    <td></td> <!-- Coluna vazia para Status -->
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<!-- Modal de Envio de E-mail -->
<div id="emailModal" class="fixed z-50 inset-0 overflow-y-auto hidden flex items-center justify-center p-4 balanco-wrap" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div id="emailModalOverlay" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity z-40" aria-hidden="true"></div>
    <div class="bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all w-full max-w-lg relative z-50 border border-gray-200 dark:border-gray-700">
        <form action="<?= BASE_URL; ?>/financeiro/enviarBalancoEmail" method="POST">
            <input type="hidden" name="ano" value="<?= $anoSelecionado ?>">
            <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-indigo-100 sm:mx-0 sm:h-10 sm:w-10">
                        <svg class="h-6 w-6 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white" id="modal-title">Enviar Balanço por E-mail</h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">O balanço de <strong><?= $anoSelecionado ?></strong> será enviado em formato PDF.</p>
                            <label for="email_destino" class="block text-sm font-medium text-gray-700 dark:text-gray-300">E-mail do Destinatário</label>
                            <input type="email" name="email_destino" id="email_destino" required class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2 border dark:bg-gray-700 dark:text-white" placeholder="exemplo@empresa.com">
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 dark:bg-gray-700/50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">Enviar</button>
                <button type="button" id="btnCancelEmailModal" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal de Definição de Meta -->
<div id="metaModal" class="fixed z-50 inset-0 overflow-y-auto hidden flex items-center justify-center p-4 balanco-wrap" aria-labelledby="modal-title-meta" role="dialog" aria-modal="true">
    <div id="metaModalOverlay" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity z-40" aria-hidden="true"></div>
    <div class="bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all w-full max-w-lg relative z-50 border border-gray-200 dark:border-gray-700">
        <form id="formMeta" action="<?= BASE_URL; ?>/financeiro/salvarMeta" method="POST">
            <input type="hidden" name="ano" value="<?= $anoSelecionado ?>">
            <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white mb-4" id="modal-title-meta">Definir Meta Mensal</h3>
                <div>
                    <label for="meta_mensal" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Valor da Meta (R$)</label>
                    <input type="text" name="meta_mensal" id="meta_mensal" value="<?= number_format($metaMensal, 2, ',', '.'); ?>" class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-teal-500 focus:border-teal-500 sm:text-sm p-2 border money-mask dark:bg-gray-700 dark:text-white" required>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Este valor será usado como linha de referência no gráfico de balanço.</p>
                </div>
            </div>
            <div class="bg-gray-50 dark:bg-gray-700/50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-teal-600 text-base font-medium text-white hover:bg-teal-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">Salvar</button>
                <button type="button" id="btnCancelMetaModal" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Cancelar</button>
            </div>
        </form>
    </div>
</div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM Content Loaded - Inicializando modais...');

        // --- Lógica dos Modais ---
        function toggleModal(modalId, show) {
            const modal = document.getElementById(modalId);
            console.log('toggleModal:', modalId, 'show:', show, 'modal encontrado:', !!modal);
            if (modal) {
                if (show) {
                    modal.classList.remove('hidden');
                    modal.style.display = 'flex';
                    const computedStyle = window.getComputedStyle(modal);
                    console.log('Modal display após abrir:', computedStyle.display);
                    console.log('Modal visibility:', computedStyle.visibility);
                    console.log('Modal z-index:', computedStyle.zIndex);
                    console.log('Modal aberto:', modalId);
                } else {
                    modal.classList.add('hidden');
                    modal.style.display = 'none';
                    console.log('Modal fechado:', modalId);
                }
            } else {
                console.warn('Modal não encontrado:', modalId);
            }
        }

        // Abrir Modal de Email
        const btnOpenEmail = document.getElementById('btnOpenEmailModal');
        console.log('btnOpenEmail encontrado:', !!btnOpenEmail);
        if (btnOpenEmail) {
            btnOpenEmail.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                console.log('Clique no botão de email detectado');
                toggleModal('emailModal', true);
            });
        } else {
            console.warn('Botão de email não encontrado');
        }

        // Abrir Modal de Meta
        const btnOpenMeta = document.getElementById('btnOpenMetaModal');
        console.log('btnOpenMeta encontrado:', !!btnOpenMeta);
        if (btnOpenMeta) {
            btnOpenMeta.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                console.log('Clique no botão de meta detectado');
                toggleModal('metaModal', true);
            });
        } else {
            console.warn('Botão de meta não encontrado');
        }

        // Fechar modais (botões de cancelar e overlay)
        ['btnCancelEmailModal', 'emailModalOverlay'].forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                el.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    console.log('Clique em:', id);
                    toggleModal('emailModal', false);
                });
            }
        });

        ['btnCancelMetaModal', 'metaModalOverlay'].forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                el.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    console.log('Clique em:', id);
                    toggleModal('metaModal', false);
                });
            }
        });

        // Fechar com ESC
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                toggleModal('emailModal', false);
                toggleModal('metaModal', false);
            }
        });

        console.log('Modais inicializados com sucesso');

        // --- Lógica do Gráfico ---
        if (typeof Chart !== 'undefined') {
            const chartContainer = document.getElementById('balancoChart').parentElement;

            // Configurar altura mínima do container
            const minHeight = 240;
            chartContainer.style.height = minHeight + 'px';

            const ctx = document.getElementById('balancoChart');
            const labels = <?= json_encode($chartLabels); ?>;
            const dataReal = <?= json_encode($chartDataReal); ?>;
            const dataProjetado = <?= json_encode($chartDataProjetado); ?>;
            const dataMeta = <?= json_encode($chartDataMeta); ?>;
            const dataCumulativeReal = <?= json_encode($chartCumulativeReal); ?>;
            const dataCumulativeProjected = <?= json_encode($chartCumulativeProjected); ?>;

            // Cores dinâmicas para melhor visualização:
            // Real: Verde (>=0) / Vermelho (<0)
            const bgReal = dataReal.map(v => v >= 0 ? 'rgba(16, 185, 129, 0.6)' : 'rgba(239, 68, 68, 0.6)');
            const borderReal = dataReal.map(v => v >= 0 ? 'rgba(16, 185, 129, 0.8)' : 'rgba(239, 68, 68, 0.8)');

            // Projetado: Azul (>=0) / Laranja (<0)
            const bgProjetado = dataProjetado.map(v => v >= 0 ? 'rgba(59, 130, 246, 0.15)' : 'rgba(249, 115, 22, 0.15)');
            const borderProjetado = dataProjetado.map(v => v >= 0 ? 'rgba(59, 130, 246, 0.4)' : 'rgba(249, 115, 22, 0.4)');

            new Chart(ctx.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Resultado Real',
                        data: dataReal,
                        backgroundColor: bgReal,
                        borderColor: borderReal,
                        borderWidth: 1,
                        yAxisID: 'y',
                        barPercentage: 0.6,
                        categoryPercentage: 0.9
                    }, {
                        label: 'Resultado Projetado',
                        data: dataProjetado,
                        backgroundColor: bgProjetado,
                        borderColor: borderProjetado,
                        borderWidth: 1,
                        yAxisID: 'y',
                        barPercentage: 0.6,
                        categoryPercentage: 0.9
                    }, {
                        label: 'Alvo (Meta)',
                        data: dataMeta,
                        type: 'line',
                        borderColor: 'rgba(255, 99, 132, 1)', // Red-500
                        borderWidth: 2,
                        fill: false,
                        borderDash: [5, 5], // Linha tracejada
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        yAxisID: 'y',
                    }, {
                        label: 'Saldo Acumulado (Real)',
                        data: dataCumulativeReal,
                        type: 'line',
                        borderColor: 'rgba(147, 51, 234, 1)', // Purple-600 (Melhor contraste)
                        backgroundColor: 'rgba(147, 51, 234, 0.1)',
                        borderWidth: 3,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        fill: false,
                        tension: 0.4,
                        yAxisID: 'y1', // Eixo da direita
                    }, {
                        label: 'Saldo Acumulado (Projetado)',
                        data: dataCumulativeProjected,
                        type: 'line',
                        borderColor: 'rgba(75, 85, 99, 0.3)', // Gray-600 (Melhor contraste)
                        borderWidth: 2,
                        borderDash: [10, 5],
                        fill: false,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        tension: 0.1,
                        yAxisID: 'y1', // Eixo da direita
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    scales: {
                        y: { // Eixo da esquerda para resultados mensais (barras)
                            type: 'linear',
                            display: true,
                            position: 'left',
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)',
                                drawBorder: false
                            },
                            ticks: {
                                font: {
                                    family: "'Plus Jakarta Sans', sans-serif",
                                    size: 8,
                                    weight: 'bold'
                                },
                                color: '#94a3b8',
                                maxTicksLimit: 6,
                                padding: 5,
                                callback: function(value) {
                                    return value.toLocaleString('pt-BR', { notation: 'compact', compactDisplay: 'short' });
                                }
                            }
                        },
                        y1: { // Eixo da direita para saldo acumulado (linhas)
                            type: 'linear',
                            display: true,
                            position: 'right',
                            beginAtZero: true,
                            grid: {
                                drawOnChartArea: false, // Evita linhas de grade duplicadas
                                drawBorder: false
                            },
                            ticks: {
                                font: {
                                    family: "'Plus Jakarta Sans', sans-serif",
                                    size: 8,
                                    weight: 'bold'
                                },
                                color: '#94a3b8',
                                maxTicksLimit: 6,
                                padding: 5,
                                callback: function(value) {
                                    return value.toLocaleString('pt-BR', { notation: 'compact', compactDisplay: 'short' });
                                }
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            backgroundColor: 'rgba(15, 23, 42, 0.9)',
                            titleFont: {
                                family: "'Plus Jakarta Sans', sans-serif",
                                size: 11,
                                weight: 'bold'
                            },
                            bodyFont: {
                                family: "'Plus Jakarta Sans', sans-serif",
                                size: 10
                            },
                            padding: 12,
                            cornerRadius: 12,
                            displayColors: true,
                            boxPadding: 6,
                            usePointStyle: true,
                            borderColor: 'rgba(255, 255, 255, 0.1)',
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.parsed.y !== null) {
                                        label += new Intl.NumberFormat('pt-BR', {
                                            style: 'currency',
                                            currency: 'BRL'
                                        }).format(context.parsed.y);
                                    }

                                    // Lógica personalizada: Diferença percentual vs Meta
                                    // Verifica se estamos no dataset "Resultado Real" e se temos dados de Meta
                                    if (context.dataset.label === 'Resultado Real' && typeof dataMeta !== 'undefined') {
                                        const meta = dataMeta[context.dataIndex];
                                        const real = context.parsed.y;

                                        if (meta && meta !== 0) {
                                            const diff = real - meta;
                                            // Calcula percentual: (Diferença / Meta) * 100
                                            const percent = (diff / Math.abs(meta)) * 100;
                                            const sign = percent >= 0 ? '+' : '';

                                            // Adiciona a informação ao tooltip
                                            label += ` (${sign}${percent.toFixed(1)}% vs Meta)`;
                                        }
                                    }

                                    return label;
                                }
                            }
                        }
                    }
                }
            });

            // Ajustar altura do gráfico ao redimensionar a janela
            window.addEventListener('resize', function() {
                const newHeight = 240;
                chartContainer.style.height = newHeight + 'px';
            });
        } else {
            console.warn('Chart.js não encontrado. O gráfico não será renderizado.');
        }

        // Validação do formulário de meta
        const formMeta = document.getElementById('formMeta');
        if (formMeta) {
            formMeta.addEventListener('submit', function(e) {
                const inputMeta = document.getElementById('meta_mensal');
                let valor = inputMeta.value;

                // Remove pontos de milhar e substitui vírgula por ponto para validação (formato PT-BR)
                valor = valor.replace(/\./g, '').replace(',', '.');

                if (isNaN(valor) || valor.trim() === '') {
                    e.preventDefault();
                    alert('Por favor, insira um valor numérico válido para a meta.');
                    inputMeta.focus();
                    return;
                }

                if (!confirm('Tem certeza que deseja salvar esta meta mensal?')) {
                    e.preventDefault();
                }
            });
        }
    });
</script>