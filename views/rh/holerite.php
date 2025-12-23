<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100">
    <div class="max-w-4xl mx-auto my-8">
        <div class="no-print flex justify-between items-center mb-6">
            <div>
                <h2 class="text-2xl font-bold"><?php echo htmlspecialchars($pageTitle); ?></h2>
                <p class="text-gray-600">Demonstrativo de Pagamento de Salário</p>
            </div>
            <div class="flex gap-2">
                <a href="<?php echo BASE_URL; ?>/rh/verFolha/<?php echo $mes; ?>/<?php echo $ano; ?>" class="px-4 py-2 text-sm font-semibold text-gray-700 bg-gray-200 rounded-lg shadow-md hover:bg-gray-300 transition">
                    &larr; Voltar para Resultados
                </a>
                <button onclick="window.print()" class="px-4 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg shadow-md hover:bg-blue-700 transition">
                    Imprimir / Salvar PDF
                </button>
            </div>
        </div>

        <?php if (!empty($holerites)) : ?>
            <?php foreach ($holerites as $holerite) : ?>
                <div class="holerite-container bg-white p-4 rounded-lg shadow-lg mb-8 border border-gray-200" style="page-break-after: always;">
                    <!-- Cabeçalho -->
                    <div class="grid grid-cols-2 gap-4 border-b pb-2 mb-2">
                        <div>
                            <p class="font-bold text-lg"><?php echo htmlspecialchars($holerite['info_empresa']['nome']); ?></p>
                            <p class="text-sm text-gray-600">CNPJ: <?php echo htmlspecialchars($holerite['info_empresa']['cnpj']); ?></p>
                        </div>
                        <div class="text-right">
                            <p class="font-bold">Demonstrativo de Pagamento</p>
                            <p class="text-sm text-gray-600">Competência: <?php echo htmlspecialchars($holerite['competencia']); ?></p>
                        </div>
                    </div>

                    <!-- Informações do Funcionário -->
                    <div class="grid grid-cols-3 gap-4 border-b pb-2 mb-2 text-sm">
                        <div><span class="font-semibold">Funcionário:</span> <?php echo htmlspecialchars($holerite['info_funcionario']['nome']); ?></div>
                        <div><span class="font-semibold">Cargo:</span> <?php echo htmlspecialchars($holerite['info_funcionario']['cargo']); ?></div>
                        <div><span class="font-semibold">Setor:</span> <?php echo htmlspecialchars($holerite['info_funcionario']['setor']); ?></div>
                    </div>

                    <!-- Corpo do Holerite (Proventos e Descontos) -->
                    <div class="grid grid-cols-2 gap-8">
                        <!-- Proventos -->
                        <div class="proventos-block">
                            <h4 class="font-bold text-center border-b mb-1">PROVENTOS</h4>
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="text-left text-gray-500">
                                        <th class="w-1/10 font-medium">Cód.</th>
                                        <th class="w-2/5 font-medium">Descrição</th>
                                        <th class="w-1/10 font-medium text-center">Quant.</th>
                                        <th class="w-1/10 font-medium text-center">%</th>
                                        <th class="w-3/10 font-medium text-right">Valor</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    <?php foreach ($holerite['proventos'] as $item) : ?>
                                        <tr>
                                            <td class="w-1/10"><?php echo $item['codigo']; ?></td>
                                            <td class="w-2/5"><?php echo htmlspecialchars($item['descricao']); ?></td>
                                            <td class="w-1/10 text-center">-</td> <!-- Proventos geralmente não têm quantidade/percentual -->
                                            <td class="w-1/10 text-center">-</td>
                                            <td class="w-3/10 text-right">R$ <?php echo number_format($item['valor'], 2, ',', '.'); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <!-- Descontos -->
                        <div class="descontos-block">
                            <h4 class="font-bold text-center border-b mb-1">DESCONTOS</h4>
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="text-left text-gray-500">
                                        <th class="w-1/10 font-medium">Cód.</th>
                                        <th class="w-2/5 font-medium">Descrição</th>
                                        <th class="w-1/10 font-medium text-center">Quant.</th>
                                        <th class="w-1/10 font-medium text-center">%</th>
                                        <th class="w-3/10 font-medium text-right">Valor</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    <?php foreach ($holerite['descontos'] as $item) : ?>
                                        <tr>
                                            <td class="w-1/10"><?php echo $item['codigo']; ?></td>
                                            <td class="w-2/5"><?php echo htmlspecialchars($item['descricao']); ?></td>
                                            <td class="w-1/10 text-center"><?php echo isset($item['quantidade']) ? htmlspecialchars($item['quantidade']) : '-'; ?></td>
                                            <td class="w-1/10 text-center"><?php echo isset($item['percentual']) && $item['percentual'] > 0 ? number_format($item['percentual'], 2, ',') . '%' : '-'; ?></td>
                                            <td class="w-3/10 text-right text-red-600">R$ <?php echo number_format($item['valor'], 2, ',', '.'); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Totais -->
                    <div class="grid grid-cols-3 gap-4 mt-4 pt-2 border-t font-bold text-sm">
                        <div>Total Proventos: <span class="float-right">R$ <?php echo number_format($holerite['totais']['bruto'], 2, ',', '.'); ?></span></div>
                        <div>Total Descontos: <span class="float-right text-red-600">R$ <?php echo number_format($holerite['totais']['descontos'], 2, ',', '.'); ?></span></div>
                        <div>Valor Líquido: <span class="float-right text-green-700">R$ <?php echo number_format($holerite['totais']['liquido'], 2, ',', '.'); ?></span></div>
                    </div>

                    <!-- Rodapé com Bases de Cálculo -->
                    <div class="grid grid-cols-4 gap-4 mt-4 pt-2 border-t text-xs text-gray-600">
                        <div><span class="font-semibold">Salário Base:</span> R$ <?php echo number_format($holerite['bases']['base_inss'], 2, ',', '.'); ?></div>
                        <div><span class="font-semibold">Base INSS:</span> R$ <?php echo number_format($holerite['bases']['base_inss'], 2, ',', '.'); ?></div>
                        <div><span class="font-semibold">Base FGTS:</span> R$ <?php echo number_format($holerite['bases']['base_fgts'], 2, ',', '.'); ?></div>
                        <div><span class="font-semibold">FGTS do Mês:</span> R$ <?php echo number_format($holerite['bases']['fgts_mes'], 2, ',', '.'); ?></div>
                        <div><span class="font-semibold">Base IRRF:</span> R$ <?php echo number_format($holerite['bases']['base_irrf'], 2, ',', '.'); ?></div>
                    </div>

                    <!-- Assinatura -->
                    <div class="mt-16 pt-4 flex justify-around items-end">
                        <div class="text-center">
                            <p class="inline-block border-t-2 border-gray-400 px-24"> </p>
                            <p class="text-sm font-medium mt-1"><?php echo htmlspecialchars($holerite['info_funcionario']['nome']); ?></p>
                            <p class="text-xs text-gray-600">Assinatura do Funcionário</p>
                        </div>
                        <div class="text-center">
                            <p class="inline-block border-t-2 border-gray-400 px-24"> </p>
                            <p class="text-xs text-gray-600 mt-1">Data</p>
                        </div>
                    </div>

                </div>
            <?php endforeach; ?>
        <?php else : ?>
            <div class="bg-white p-6 rounded-lg shadow-md">
                <p class="text-center text-red-500">Nenhum dado de holerite encontrado para a competência e funcionário selecionados.</p>
            </div>
        <?php endif; ?>
    </div>
</body>
<style>
    @media print {

        body,
        html {
            background-color: #fff;
            /* Garante fundo branco */
            margin: 0;
            padding: 0;
        }

        /* Esconde os botões e o cabeçalho da página */
        .no-print,
        .no-print * {
            display: none !important;
        }

        /* Remove estilos de layout da web e centraliza o holerite */
        .holerite-container {
            box-shadow: none;
            border: none;
            margin: 0 auto;
            /* Centraliza o holerite na página de impressão */
        }
    }
</style>