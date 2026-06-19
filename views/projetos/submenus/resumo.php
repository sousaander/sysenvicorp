<?php
// --- Dados para os Cards ---
// (Valores vindos do controller em $summaryDetails)
$progresso = $summaryDetails['progresso_calculado'] ?? ($projeto['progresso'] ?? 0);
$dias_restantes = $summaryDetails['dias_restantes'] ?? 'N/A';
$orcamento_gasto_percent = $summaryDetails['orcamento_gasto_percent'] ?? 0;
$faturamento_realizado = $summaryDetails['faturamento_realizado'] ?? 'R$ 0,00';

// --- Lógica para a barra de progresso e dias restantes ---
$progress_color = 'bg-blue-600';
if ($progresso < 30) {
    $progress_color = 'bg-sky-500';
} elseif ($progresso >= 100) {
    $progress_color = 'bg-green-500';
} elseif ($progresso > 75) {
    $progress_color = 'bg-emerald-500';
}

if (isset($projeto['status']) && $projeto['status'] === 'Atrasado') {
    $progress_color = 'bg-red-500';
}

$dias_cor = 'text-gray-800';
if (is_numeric($dias_restantes)) {
    if ($dias_restantes < 0) {
        $dias_cor = 'text-red-600 font-bold';
        $dias_restantes_texto = abs($dias_restantes) . ' dias atrasado';
    } elseif ($dias_restantes == 0) {
        $dias_cor = 'text-yellow-600 font-bold';
        $dias_restantes_texto = 'Vence hoje';
    } elseif ($dias_restantes <= 7) {
        $dias_cor = 'text-yellow-600';
        $dias_restantes_texto = $dias_restantes . ' dias restantes';
    } else {
        $dias_restantes_texto = $dias_restantes . ' dias restantes';
    }
} else {
    $dias_restantes_texto = 'Não definido';
}

// --- Lógica para o status ---
if (!function_exists('get_status_info')) {
    function get_status_info($status)
    {
        $status_map = [
            'Planejado' => ['icon' => 'bx-calendar-edit', 'color' => 'bg-gray-100 text-gray-800', 'text_color' => 'text-gray-600'],
            'Em Andamento' => ['icon' => 'bx-run', 'color' => 'bg-blue-100 text-blue-800', 'text_color' => 'text-blue-600'],
            'Em Execução' => ['icon' => 'bx-run', 'color' => 'bg-blue-100 text-blue-800', 'text_color' => 'text-blue-600'],
            'Concluído' => ['icon' => 'bx-check-double', 'color' => 'bg-green-100 text-green-800', 'text_color' => 'text-green-600'],
            'Cancelado' => ['icon' => 'bx-x-circle', 'color' => 'bg-red-100 text-red-800', 'text_color' => 'text-red-600'],
            'Atrasado' => ['icon' => 'bx-time-five', 'color' => 'bg-orange-100 text-orange-800', 'text_color' => 'text-orange-600'],
            'Aguardando Cliente' => ['icon' => 'bx-user-voice', 'color' => 'bg-yellow-100 text-yellow-800', 'text_color' => 'text-yellow-600'],
        ];
        return $status_map[$status] ?? ['icon' => 'bx-question-mark', 'color' => 'bg-gray-100 text-gray-800', 'text_color' => 'text-gray-600'];
    }
}
$status_info = get_status_info($projeto['status'] ?? 'Planejado');
?>

<!-- Cards de Resumo Compactos -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
    <!-- Card Status -->
    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 flex items-center gap-4">
        <div class="p-3 rounded-full <?php echo $status_info['color']; ?>">
            <i class='bx <?php echo $status_info['icon']; ?> text-2xl'></i>
        </div>
        <div>
            <p class="text-sm font-medium text-gray-500">Status</p>
            <p class="text-lg font-bold <?php echo $status_info['text_color']; ?>"><?php echo htmlspecialchars($projeto['status']); ?></p>
        </div>
    </div>

    <!-- Card Dias Restantes -->
    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 flex items-center gap-4">
        <div class="p-3 rounded-full bg-gray-100 text-gray-600">
            <i class='bx bx-calendar-exclamation text-2xl'></i>
        </div>
        <div>
            <p class="text-sm font-medium text-gray-500">Prazo</p>
            <p class="text-lg font-bold <?php echo $dias_cor; ?>"><?php echo $dias_restantes_texto; ?></p>
        </div>
    </div>

    <!-- Card Orçamento -->
    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 flex items-center gap-4">
        <div class="p-3 rounded-full bg-gray-100 text-gray-600">
            <i class='bx bx-wallet text-2xl'></i>
        </div>
        <div>
            <p class="text-sm font-medium text-gray-500">Orçamento Gasto</p>
            <p class="text-lg font-bold text-gray-800"><?php echo number_format($orcamento_gasto_percent, 1, ',', ''); ?>%</p>
        </div>
    </div>

    <!-- Card Faturamento -->
    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 flex items-center gap-4">
        <div class="p-3 rounded-full bg-gray-100 text-gray-600">
            <i class='bx bx-dollar-circle text-2xl'></i>
        </div>
        <div>
            <p class="text-sm font-medium text-gray-500">Faturado</p>
            <p class="text-lg font-bold text-gray-800"><?php echo htmlspecialchars($faturamento_realizado); ?></p>
        </div>
    </div>
</div>

<!-- Barra de Progresso -->
<div class="mb-8">
    <div class="flex justify-between mb-1">
        <span class="text-base font-medium text-gray-700">Progresso Geral</span>
        <span class="text-sm font-medium text-gray-700"><?php echo $progresso; ?>%</span>
    </div>
    <div class="w-full bg-gray-200 rounded-full h-4">
        <div class="<?php echo $progress_color; ?> h-4 rounded-full transition-all duration-500" style="width: <?php echo $progresso; ?>%"></div>
    </div>
</div>

<!-- Seção de Dados Gerais -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4">
    <h3 class="md:col-span-2 text-lg font-semibold text-gray-800 border-b pb-2 mb-2">Dados Gerais do Projeto</h3>

    <div class="flex justify-between py-2 border-b border-gray-100"><span class="font-medium text-gray-600">Responsável Técnico:</span><span class="text-gray-800"><?php echo htmlspecialchars($projeto['responsavel'] ?? 'Não definido'); ?></span></div>
    <div class="flex justify-between py-2 border-b border-gray-100"><span class="font-medium text-gray-600">Tipo de Serviço:</span><span class="text-gray-800"><?php echo htmlspecialchars($projeto['tipo_servico'] ?? 'Não definido'); ?></span></div>
    <div class="flex justify-between py-2 border-b border-gray-100"><span class="font-medium text-gray-600">Data de Início:</span><span class="text-gray-800"><?php echo !empty($projeto['data_inicial']) ? date('d/m/Y', strtotime($projeto['data_inicial'])) : 'Não definida'; ?></span></div>
    <div class="flex justify-between py-2 border-b border-gray-100"><span class="font-medium text-gray-600">Previsão de Término:</span><span class="text-gray-800"><?php echo !empty($projeto['data_fim_prevista']) ? date('d/m/Y', strtotime($projeto['data_fim_prevista'])) : 'Não definida'; ?></span></div>
    <div class="flex justify-between py-2 border-b border-gray-100"><span class="font-medium text-gray-600">Orçamento Total:</span><span class="text-gray-800">R$ <?php echo !empty($projeto['orcamento']) ? number_format($projeto['orcamento'], 2, ',', '.') : '0,00'; ?></span></div>
    <div class="flex justify-between py-2 border-b border-gray-100"><span class="font-medium text-gray-600">Empreendimento:</span><span class="text-gray-800"><?php echo htmlspecialchars($projeto['empreendimento'] ?? 'Não definido'); ?></span></div>

    <div class="md:col-span-2 mt-4">
        <h4 class="font-medium text-gray-600 mb-1">Observações:</h4>
        <p class="text-gray-800 bg-gray-50 p-3 rounded-md border text-sm"><?php echo !empty($projeto['observacoes']) ? nl2br(htmlspecialchars($projeto['observacoes'])) : 'Nenhuma observação.'; ?></p>
    </div>
</div>

<!-- Timeline de Eventos do Projeto -->
<div class="mt-8">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">Histórico do Projeto</h3>
    <?php if (!empty($timeline)): ?>
        <ul class="space-y-4">
            <?php foreach ($timeline as $event): ?>
                <li class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                    <div class="flex justify-between items-center mb-1">
                        <span class="text-sm text-gray-600"><?php echo date('d/m/Y H:i', strtotime($event['data_evento'])); ?></span>
                        <span class="text-xs uppercase font-semibold text-gray-500"><?php echo htmlspecialchars($event['evento']); ?></span>
                    </div>
                    <p class="text-gray-800 text-sm"><?php echo htmlspecialchars($event['descricao']); ?></p>
                    <?php if (!empty($event['usuario_nome'])): ?>
                        <p class="text-xs text-gray-500 mt-1">por <?php echo htmlspecialchars($event['usuario_nome']); ?></p>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p class="text-gray-500">Ainda não há eventos registrados.</p>
    <?php endif; ?>
</div>

<!-- Botão de Edição -->
<div class="mt-8 flex justify-end">
    <a href="<?php echo BASE_URL; ?>/projetos/editar/<?php echo $projeto['id']; ?>" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-violet-600 hover:bg-violet-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-violet-500">
        <i class='bx bx-edit-alt mr-2'></i>
        Editar Dados do Projeto
    </a>
</div>