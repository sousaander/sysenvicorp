<div class="flex justify-between items-center mb-4">
    <div>
        <h2 class="text-2xl font-bold"><?php echo htmlspecialchars($pageTitle); ?></h2>
        <p class="text-gray-600">Resumo dos encargos sociais para geração de guias.</p>
    </div>
    <a href="<?php echo BASE_URL; ?>/rh/folhaDePagamento" class="px-4 py-2 text-sm font-semibold text-gray-700 bg-gray-200 rounded-lg shadow-md hover:bg-gray-300 transition">
        &larr; Voltar
    </a>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <!-- Guia FGTS -->
    <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-blue-500">
        <div class="flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-800">FGTS (GRF)</h3>
            <span class="text-xs font-bold text-blue-600 bg-blue-100 px-2 py-1 rounded-full">A Recolher</span>
        </div>
        <p class="text-3xl font-bold text-gray-900 mt-2">R$ <?php echo number_format($encargos['total_fgts'] ?? 0, 2, ',', '.'); ?></p>
        <div class="text-sm text-gray-500 mt-4 space-y-1">
            <p><strong>Base de Cálculo:</strong> R$ <?php echo number_format($encargos['base_fgts'] ?? 0, 2, ',', '.'); ?></p>
            <p><strong>Vencimento:</strong> <?php echo htmlspecialchars($encargos['vencimento_fgts'] ?? 'N/A'); ?></p>
        </div>
        <button class="mt-4 w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 transition">Gerar Guia GRF</button>
    </div>

    <!-- Guia INSS -->
    <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-red-500">
        <div class="flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-800">INSS (GPS)</h3>
            <span class="text-xs font-bold text-red-600 bg-red-100 px-2 py-1 rounded-full">A Recolher</span>
        </div>
        <p class="text-3xl font-bold text-gray-900 mt-2">R$ <?php echo number_format($encargos['total_inss'] ?? 0, 2, ',', '.'); ?></p>
        <div class="text-sm text-gray-500 mt-4 space-y-1">
            <p><strong>Competência:</strong> <?php echo htmlspecialchars($encargos['competencia'] ?? 'N/A'); ?></p>
            <p><strong>Vencimento:</strong> <?php echo htmlspecialchars($encargos['vencimento_inss'] ?? 'N/A'); ?></p>
        </div>
        <button class="mt-4 w-full bg-red-600 text-white py-2 rounded-lg hover:bg-red-700 transition">Gerar Guia GPS</button>
    </div>

    <!-- Guia IRRF -->
    <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-green-500">
        <div class="flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-800">IRRF (DARF)</h3>
            <span class="text-xs font-bold text-green-600 bg-green-100 px-2 py-1 rounded-full">A Recolher</span>
        </div>
        <p class="text-3xl font-bold text-gray-900 mt-2">R$ <?php echo number_format($encargos['total_irrf'] ?? 0, 2, ',', '.'); ?></p>
        <div class="text-sm text-gray-500 mt-4 space-y-1">
            <p><strong>Período de Apuração:</strong> <?php echo htmlspecialchars($encargos['competencia'] ?? 'N/A'); ?></p>
            <p><strong>Vencimento:</strong> <?php echo htmlspecialchars($encargos['vencimento_inss'] ?? 'N/A'); ?> (mesmo do INSS)</p>
        </div>
        <button class="mt-4 w-full bg-green-600 text-white py-2 rounded-lg hover:bg-green-700 transition">Gerar Guia DARF</button>
    </div>
</div>