<?php
$funcionario = $calculo['funcionario'];
$verbas = $calculo['verbas'];
$totais = $calculo['totais'];
$outros = $calculo['outros'];
?>

<div class="flex justify-between items-center mb-4 no-print">
    <div>
        <h2 class="text-2xl font-bold"><?php echo htmlspecialchars($pageTitle); ?></h2>
        <p class="text-gray-600">Resumo dos valores rescisórios para <?php echo htmlspecialchars($funcionario['nome']); ?>.</p>
    </div>
    <div class="flex items-center gap-3">
        <a href="<?php echo BASE_URL; ?>/rh/calculoRescisao" class="px-4 py-2 text-sm font-semibold text-gray-700 bg-gray-200 rounded-lg shadow-md hover:bg-gray-300 transition">
            &larr; Novo Cálculo
        </a>
        <!-- Botão Dropdown para Ações -->
        <div class="relative" id="actions-dropdown-container">
            <button id="actions-dropdown-button" class="flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-sky-600 rounded-lg shadow-md hover:bg-sky-700 transition">
                <i class='bx bx-printer text-base'></i>
                <span>Imprimir / Gerar PDF</span>
                <i class='bx bx-chevron-down text-base'></i>
            </button>
            <div id="actions-dropdown-menu" class="hidden absolute right-0 mt-2 w-56 bg-white rounded-md shadow-lg py-1 z-50 border">
                <a href="#" onclick="window.print(); return false;" class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                    <i class='bx bx-printer text-gray-500'></i> Imprimir Resumo
                </a>
                <a href="<?php echo BASE_URL; ?>/rh/gerarAvisoPrevioPdf?calculo=<?php echo urlencode(json_encode($calculo)); ?>" target="_blank" class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                    <i class='bx bxs-file-pdf text-orange-500'></i> Gerar Aviso Prévio
                </a>
                <a href="<?php echo BASE_URL; ?>/rh/gerarTrctPdf?calculo=<?php echo urlencode(json_encode($calculo)); ?>" target="_blank" class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                    <i class='bx bxs-file-pdf text-red-500'></i> Gerar TRCT
                </a>
            </div>
        </div>
    </div>
</div>

<div class="bg-white p-8 rounded-lg shadow-lg max-w-4xl mx-auto print-area">
    <!-- Cabeçalho (diferente para tela e impressão) -->
    <div class="text-center border-b pb-4 mb-8">
        <h3 class="text-2xl font-bold text-gray-800 print:hidden">Demonstrativo da Rescisão</h3>
        <h3 class="text-xl font-bold hidden print:block">Termo de Rescisão de Contrato de Trabalho (TRCT)</h3>
        <p class="text-gray-500 print:text-sm">Resumo dos valores calculados</p>
    </div>

    <!-- Detalhes do Funcionário e Contrato -->
    <div class="border rounded-lg p-4 mb-8 bg-gray-50 print:border-0 print:p-0 print:bg-white print:mb-6">
        <h4 class="text-md font-semibold mb-3 text-gray-700 print:hidden">Informações do Contrato</h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-2 text-sm">
            <div><strong>Funcionário:</strong> <?php echo htmlspecialchars($funcionario['nome']); ?></div>
            <div><strong>Motivo:</strong> <?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $funcionario['motivo']))); ?></div>
            <div><strong>Data de Admissão:</strong> <?php echo date('d/m/Y', strtotime($funcionario['data_admissao'])); ?></div>
            <div><strong>Data de Desligamento:</strong> <?php echo date('d/m/Y', strtotime($funcionario['data_desligamento'])); ?></div>
        </div>
    </div>

    <div class="mb-8">
        <h4 class="text-xl font-semibold mb-4 text-gray-800 border-b pb-2 print:hidden">Verbas Rescisórias</h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12">
            <!-- Coluna de Proventos -->
            <div>
                <h5 class="text-lg font-semibold mb-2 text-green-700">Proventos (Créditos)</h5>
                <table class="min-w-full">
                    <tbody class="divide-y divide-gray-200">
                        <?php foreach ($verbas['proventos'] as $provento) : ?>
                            <tr class="text-sm">
                                <td class="py-2 pr-4"><?php echo htmlspecialchars($provento['descricao']); ?></td>
                                <td class="py-2 text-right font-medium">R$ <?php echo number_format($provento['valor'], 2, ',', '.'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="bg-gray-50 font-bold text-base">
                            <td class="py-2 pr-4">Total de Proventos</td>
                            <td class="py-2 text-right">R$ <?php echo number_format($totais['total_proventos'], 2, ',', '.'); ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Coluna de Descontos -->
            <div>
                <h5 class="text-lg font-semibold mb-2 text-red-700">Descontos (Débitos)</h5>
                <table class="min-w-full">
                    <tbody class="divide-y divide-gray-200">
                        <?php if (!empty($verbas['descontos'])) : ?>
                            <?php foreach ($verbas['descontos'] as $desconto) : ?>
                                <tr class="text-sm">
                                    <td class="py-2 pr-4"><?php echo htmlspecialchars($desconto['descricao']); ?></td>
                                    <td class="py-2 text-right font-medium text-red-600">(R$ <?php echo number_format($desconto['valor'], 2, ',', '.'); ?>)</td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr class="text-sm">
                                <td class="py-2 pr-4 text-gray-500" colspan="2">Nenhum desconto a ser aplicado.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr class="bg-gray-50 font-bold text-base">
                            <td class="py-2 pr-4">Total de Descontos</td>
                            <td class="py-2 text-right text-red-600">(R$ <?php echo number_format($totais['total_descontos'], 2, ',', '.'); ?>)</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Resumo Final -->
    <div class="mt-8 pt-6 border-t-2 border-dashed border-gray-200">
        <h4 class="text-xl font-semibold mb-4 text-gray-800 print:hidden">Resumo Final</h4>
        <div class="bg-blue-50 p-6 rounded-lg border border-blue-200">
            <div class="space-y-4">
                <?php if ($outros['multa_fgts'] > 0) : ?>
                    <div class="flex justify-between items-center text-md text-gray-700">
                        <span>Multa de 40% do FGTS (a ser depositada):</span>
                        <span class="font-semibold text-gray-900">R$ <?php echo number_format($outros['multa_fgts'], 2, ',', '.'); ?></span>
                    </div>
                <?php endif; ?>

                <div class="flex justify-between items-center text-2xl font-bold <?php if ($outros['multa_fgts'] > 0): ?>border-t border-blue-200 pt-4 mt-4<?php endif; ?>">
                    <span class="text-gray-800">Valor Líquido a Receber:</span>
                    <span class="text-blue-700">R$ <?php echo number_format($totais['total_liquido'], 2, ',', '.'); ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    @media print {
        @page {
            margin: 1.5cm;
            size: auto;
        }

        body,
        html {
            background-color: white !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            width: 100%;
            height: auto;
            margin: 0;
            padding: 0;
        }

        /* Oculta elementos de layout do template principal */
        nav,
        header,
        footer,
        aside,
        .sidebar,
        #sidebar,
        #menu-button,
        #user-menu-button {
            display: none !important;
        }

        /* Reseta containers do layout */
        body>div.flex {
            display: block !important;
            height: auto !important;
        }

        body>div.flex>*:not(#main-content) {
            display: none !important;
        }

        #main-content {
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
            height: auto !important;
            overflow: visible !important;
        }

        main {
            padding: 0 !important;
            margin: 0 !important;
        }

        .print-area {
            box-shadow: none !important;
            border: none !important;
            margin: 0 !important;
            padding: 0 !important;
            max-width: 100% !important;
            width: 100% !important;
            background-color: white !important;
        }

        .no-print {
            display: none !important;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const container = document.getElementById('actions-dropdown-container');
        const button = document.getElementById('actions-dropdown-button');
        const menu = document.getElementById('actions-dropdown-menu');

        if (button && menu) {
            button.addEventListener('click', function(event) {
                event.stopPropagation();
                menu.classList.toggle('hidden');
            });

            // Fecha o menu se clicar fora dele
            document.addEventListener('click', function() {
                menu.classList.add('hidden');
            });
        }
    });
</script>