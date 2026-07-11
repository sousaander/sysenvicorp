<?php $pageTitle = $pageTitle ?? 'Demonstrações Contábeis'; ?>
<div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Demonstrações Contábeis</h1>
            <p class="text-sm text-gray-500 mt-1">Relatórios contábeis baseados no plano de contas</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <a href="<?= BASE_URL ?>/contabil/balanco" class="bg-white rounded-xl border border-gray-200 p-6 hover:border-emerald-300 hover:shadow-md transition-all group">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-12 h-12 rounded-lg flex items-center justify-center bg-emerald-100 text-emerald-600 group-hover:scale-110 transition-transform">
                    <i class='bx bx-balance text-2xl'></i>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-800">Balanço Patrimonial</h3>
                    <p class="text-xs text-gray-500">Ativo, Passivo e Patrimônio Líquido</p>
                </div>
            </div>
            <p class="text-sm text-gray-600">Demonstra a posição financeira e patrimonial da empresa em determinado período.</p>
            <span class="inline-block mt-4 text-sm font-medium text-emerald-600 group-hover:underline">Acessar →</span>
        </a>

        <a href="<?= BASE_URL ?>/contabil/dre" class="bg-white rounded-xl border border-gray-200 p-6 hover:border-blue-300 hover:shadow-md transition-all group">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-12 h-12 rounded-lg flex items-center justify-center bg-blue-100 text-blue-600 group-hover:scale-110 transition-transform">
                    <i class='bx bx-trending-up text-2xl'></i>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-800">DRE</h3>
                    <p class="text-xs text-gray-500">Demonstrativo de Resultado</p>
                </div>
            </div>
            <p class="text-sm text-gray-600">Demonstra o resultado econômico (receitas, despesas e resultado líquido).</p>
            <span class="inline-block mt-4 text-sm font-medium text-blue-600 group-hover:underline">Acessar →</span>
        </a>

        <a href="<?= BASE_URL ?>/contabil/fluxocaixa" class="bg-white rounded-xl border border-gray-200 p-6 hover:border-cyan-300 hover:shadow-md transition-all group">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-12 h-12 rounded-lg flex items-center justify-center bg-cyan-100 text-cyan-600 group-hover:scale-110 transition-transform">
                    <i class='bx bx-line-chart text-2xl'></i>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-800">Fluxo de Caixa</h3>
                    <p class="text-xs text-gray-500">Entradas e Saídas do Período</p>
                </div>
            </div>
            <p class="text-sm text-gray-600">Demonstra a movimentação financeira com saldo acumulado mês a mês.</p>
            <span class="inline-block mt-4 text-sm font-medium text-cyan-600 group-hover:underline">Acessar →</span>
        </a>
    </div>
</div>
