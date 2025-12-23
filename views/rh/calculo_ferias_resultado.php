<?php
$funcionario = $calculo['funcionario'];
$periodo = $calculo['periodo'];
$valores = $calculo['valores'];
?>

<div class="flex justify-between items-center mb-4">
    <div>
        <h2 class="text-2xl font-bold"><?php echo htmlspecialchars($pageTitle); ?></h2>
        <p class="text-gray-600">Resumo dos valores de férias para <?php echo htmlspecialchars($funcionario['nome']); ?>.</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="<?php echo BASE_URL; ?>/rh/calculoFerias" class="px-4 py-2 text-sm font-semibold text-gray-700 bg-gray-200 rounded-lg shadow-md hover:bg-gray-300 transition">
            &larr; Novo Cálculo
        </a>
        <!-- Botão Dropdown para PDFs -->
        <div class="relative" id="pdf-dropdown-container">
            <button id="pdf-dropdown-button" class="flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-sky-600 rounded-lg shadow-md hover:bg-sky-700 transition">
                <span>Gerar PDF</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            <div id="pdf-dropdown-menu" class="hidden absolute right-0 mt-2 w-56 bg-white rounded-md shadow-lg py-1 z-50 border">
                <a href="<?php echo BASE_URL; ?>/rh/gerarAvisoFeriasPdf?funcionario_id=<?php echo $funcionario['id']; ?>&data_inicio=<?php echo $periodo['data_inicio']; ?>&dias_ferias=<?php echo $periodo['dias']; ?>" target="_blank" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                    Aviso de Férias
                </a>
                <a href="<?php echo BASE_URL; ?>/rh/gerarRelatorioFeriasPdf?calculo=<?php echo urlencode(json_encode($calculo)); ?>" target="_blank" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                    Relatório de Cálculo
                </a>
                <a href="<?php echo BASE_URL; ?>/rh/gerarReciboFeriasPdf?calculo=<?php echo urlencode(json_encode($calculo)); ?>" target="_blank" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                    Recibo de Pagamento
                </a>
            </div>
        </div>
    </div>
</div>

<div class="bg-white p-6 rounded-lg shadow-md max-w-4xl mx-auto">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Informações do Funcionário e Período -->
        <div class="border-r pr-6">
            <h3 class="text-lg font-semibold mb-3 text-gray-800">Detalhes</h3>
            <p><strong>Funcionário:</strong> <?php echo htmlspecialchars($funcionario['nome']); ?></p>
            <p><strong>Cargo:</strong> <?php echo htmlspecialchars($funcionario['cargo']); ?></p>
            <p><strong>Setor:</strong> <?php echo htmlspecialchars($funcionario['setor']); ?></p>
            <hr class="my-3">
            <p><strong>Início das Férias:</strong> <?php echo date('d/m/Y', strtotime($periodo['data_inicio'])); ?></p>
            <p><strong>Término das Férias:</strong> <?php echo date('d/m/Y', strtotime($periodo['data_fim'])); ?></p>
            <p><strong>Duração:</strong> <?php echo htmlspecialchars($periodo['dias']); ?> dias</p>
        </div>

        <!-- Valores Calculados -->
        <div>
            <h3 class="text-lg font-semibold mb-3 text-gray-800">Valores Calculados</h3>
            <table class="min-w-full">
                <tbody>
                    <tr class="border-b">
                        <td class="py-2">Salário Base:</td>
                        <td class="py-2 text-right">R$ <?php echo number_format($valores['salario_base'], 2, ',', '.'); ?></td>
                    </tr>
                    <tr class="border-b">
                        <td class="py-2">Valor das Férias:</td>
                        <td class="py-2 text-right">R$ <?php echo number_format($valores['valor_ferias'], 2, ',', '.'); ?></td>
                    </tr>
                    <tr class="border-b">
                        <td class="py-2">1/3 Constitucional:</td>
                        <td class="py-2 text-right">R$ <?php echo number_format($valores['terco_constitucional'], 2, ',', '.'); ?></td>
                    </tr>
                    <tr class="border-b bg-gray-50 font-bold">
                        <td class="py-2">Total Bruto:</td>
                        <td class="py-2 text-right">R$ <?php echo number_format($valores['total_bruto'], 2, ',', '.'); ?></td>
                    </tr>
                    <tr class="border-b">
                        <td class="py-2 text-red-600">(-) INSS sobre Férias:</td>
                        <td class="py-2 text-right text-red-600">R$ <?php echo number_format($valores['inss'], 2, ',', '.'); ?></td>
                    </tr>
                    <tr class="border-b">
                        <td class="py-2 text-red-600">(-) IRRF sobre Férias:</td>
                        <td class="py-2 text-right text-red-600">R$ <?php echo number_format($valores['irrf'], 2, ',', '.'); ?></td>
                    </tr>
                    <tr class="border-b bg-gray-50 font-bold">
                        <td class="py-2 text-red-600">Total de Descontos:</td>
                        <td class="py-2 text-right text-red-600">R$ <?php echo number_format($valores['total_descontos'], 2, ',', '.'); ?></td>
                    </tr>
                    <tr class="bg-green-50 font-bold text-green-800">
                        <td class="py-2 text-lg">Valor Líquido a Receber:</td>
                        <td class="py-2 text-right text-lg">R$ <?php echo number_format($valores['valor_liquido'], 2, ',', '.'); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const container = document.getElementById('pdf-dropdown-container');
        const button = document.getElementById('pdf-dropdown-button');
        const menu = document.getElementById('pdf-dropdown-menu');

        button.addEventListener('click', function(event) {
            event.stopPropagation();
            menu.classList.toggle('hidden');
        });

        // Fecha o menu se clicar fora dele
        document.addEventListener('click', function() {
            menu.classList.add('hidden');
        });
    });
</script>