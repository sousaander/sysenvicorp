<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        .section-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: #1F2937;
            border-bottom: 2px solid #E5E7EB;
            padding-bottom: 0.5rem;
            margin-bottom: 1rem;
        }

        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .no-print {
                display: none;
            }

            #print-area {
                margin: 0;
                padding: 0;
                box-shadow: none;
                border: none;
            }
        }
    </style>
</head>

<body class="bg-gray-100">

    <div class="max-w-6xl mx-auto my-8 bg-white p-8 shadow-lg" id="print-area">
        <!-- Cabeçalho -->
        <div class="flex justify-between items-start border-b-2 pb-4 mb-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800"><?php echo htmlspecialchars($dados['info_empresa']['nome']); ?></h1>
                <p class="text-sm text-gray-500">CNPJ: <?php echo htmlspecialchars($dados['info_empresa']['cnpj']); ?></p>
                <p class="text-sm text-gray-500"><?php echo htmlspecialchars($dados['info_empresa']['endereco']); ?></p>
            </div>
            <div class="text-right">
                <h2 class="text-xl font-semibold text-gray-700">Resultados da Folha de Pagamento</h2>
                <p class="text-md text-gray-600">Competência: <?php echo htmlspecialchars($dados['competencia']); ?></p>
                <p class="text-sm text-gray-500">Emitido em: <?php echo htmlspecialchars($dados['data_emissao']); ?></p>
            </div>
        </div>

        <!-- Tabela de Funcionários -->
        <?php if (!empty($dados['funcionarios'])) : ?>
            <table class="min-w-full divide-y divide-gray-300 text-sm">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-3 py-2 text-left font-semibold text-gray-700">ID</th>
                        <th class="px-3 py-2 text-left font-semibold text-gray-700">Funcionário</th>
                        <th class="px-3 py-2 text-right font-semibold text-gray-700">Sal. Bruto</th>
                        <th class="px-3 py-2 text-right font-semibold text-gray-700">INSS</th>
                        <th class="px-3 py-2 text-right font-semibold text-gray-700">IRRF</th>
                        <th class="px-3 py-2 text-right font-semibold text-gray-700">Outros Desc.</th>
                        <th class="px-3 py-2 text-right font-semibold text-gray-700">Sal. Líquido</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($dados['funcionarios'] as $func) : ?>
                        <tr>
                            <td class="px-3 py-2 whitespace-nowrap"><?php echo htmlspecialchars($func['id']); ?></td>
                            <td class="px-3 py-2 whitespace-nowrap font-medium text-gray-900"><?php echo htmlspecialchars($func['nome']); ?></td>
                            <td class="px-3 py-2 whitespace-nowrap text-right">R$ <?php echo number_format($func['salario_bruto'], 2, ',', '.'); ?></td>
                            <td class="px-3 py-2 whitespace-nowrap text-right text-red-600">R$ <?php echo number_format($func['inss'], 2, ',', '.'); ?></td>
                            <td class="px-3 py-2 whitespace-nowrap text-right text-red-600">R$ <?php echo number_format($func['irrf'], 2, ',', '.'); ?></td>
                            <td class="px-3 py-2 whitespace-nowrap text-right text-red-600">R$ <?php echo number_format($func['outros_descontos'], 2, ',', '.'); ?></td>
                            <td class="px-3 py-2 whitespace-nowrap text-right font-bold text-green-700">R$ <?php echo number_format($func['salario_liquido'], 2, ',', '.'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="bg-gray-100 font-bold">
                    <tr>
                        <td colspan="2" class="px-3 py-2 text-right text-gray-800">TOTAIS:</td>
                        <td class="px-3 py-2 text-right text-gray-800">R$ <?php echo number_format($dados['totais']['salario_bruto'], 2, ',', '.'); ?></td>
                        <td class="px-3 py-2 text-right text-red-700">R$ <?php echo number_format($dados['totais']['inss'], 2, ',', '.'); ?></td>
                        <td class="px-3 py-2 text-right text-red-700">R$ <?php echo number_format($dados['totais']['irrf'], 2, ',', '.'); ?></td>
                        <td class="px-3 py-2 text-right text-red-700">R$ <?php echo number_format($dados['totais']['outros_descontos'], 2, ',', '.'); ?></td>
                        <td class="px-3 py-2 text-right text-green-800">R$ <?php echo number_format($dados['totais']['salario_liquido'], 2, ',', '.'); ?></td>
                    </tr>
                </tfoot>
            </table>
        <?php else : ?>
            <p class="text-center text-red-500 py-8">Nenhum dado encontrado para esta competência.</p>
        <?php endif; ?>

        <!-- Resumos Adicionais -->
        <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-8 print:grid-cols-2">
            <!-- Resumo dos Encargos -->
            <div>
                <h3 class="section-title">Resumo dos Encargos</h3>
                <div class="space-y-2 text-sm bg-gray-50 p-4 rounded-lg border">
                    <div class="flex justify-between">
                        <span class="font-medium text-gray-600">Total INSS (Funcionários):</span>
                        <span class="font-semibold text-gray-800">R$ <?php echo number_format($dados['totais']['inss'], 2, ',', '.'); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-medium text-gray-600">Total IRRF (Funcionários):</span>
                        <span class="font-semibold text-gray-800">R$ <?php echo number_format($dados['totais']['irrf'], 2, ',', '.'); ?></span>
                    </div>
                    <div class="flex justify-between border-t pt-2 mt-2">
                        <span class="font-medium text-gray-600">Total FGTS (Encargo Empresa):</span>
                        <span class="font-semibold text-gray-800">R$ <?php echo number_format($dados['encargos']['total_fgts'] ?? 0, 2, ',', '.'); ?></span>
                    </div>
                </div>
            </div>

            <!-- Resumo Geral da Folha -->
            <div>
                <h3 class="section-title">Resumo Geral da Folha</h3>
                <div class="space-y-2 text-sm bg-gray-50 p-4 rounded-lg border">
                    <div class="flex justify-between">
                        <span class="font-medium text-gray-600">Total de Vencimentos (Salário Bruto):</span>
                        <span class="font-semibold text-green-700">R$ <?php echo number_format($dados['totais']['salario_bruto'], 2, ',', '.'); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-medium text-gray-600">Total de Descontos (INSS + IRRF + Outros):</span>
                        <span class="font-semibold text-red-700">R$ <?php echo number_format($dados['totais']['inss'] + $dados['totais']['irrf'] + $dados['totais']['outros_descontos'], 2, ',', '.'); ?></span>
                    </div>
                    <div class="flex justify-between border-t pt-2 mt-2 font-bold">
                        <span class="text-gray-800">Valor Líquido a Pagar:</span>
                        <span class="text-blue-800">R$ <?php echo number_format($dados['totais']['salario_liquido'], 2, ',', '.'); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Rodapé com Assinaturas -->
        <div class="mt-24 pt-8 grid grid-cols-2 gap-8">
            <div class="text-center">
                <p class="inline-block border-t-2 border-gray-400 px-24 pt-2"> </p>
                <p class="text-sm font-medium mt-1">Responsável pelo RH</p>
                <p class="text-xs text-gray-600"><?php echo htmlspecialchars($dados['info_empresa']['nome']); ?></p>
            </div>
            <div class="text-center">
                <p class="inline-block border-t-2 border-gray-400 px-24 pt-2"> </p>
                <p class="text-sm font-medium mt-1">Diretor Financeiro</p>
                <p class="text-xs text-gray-600"><?php echo htmlspecialchars($dados['info_empresa']['nome']); ?></p>
            </div>
        </div>
    </div>

</body>

</html>