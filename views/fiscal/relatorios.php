<?php $pageTitle = $pageTitle ?? 'Relatórios Fiscais'; ?>
<div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Relatórios</h1>
            <p class="text-sm text-gray-500 mt-1">Exportação e visualização de obrigações acessórias</p>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl border border-gray-200 p-5 hover:border-orange-300 hover:shadow-sm transition-all cursor-pointer">
            <div class="w-10 h-10 rounded-lg flex items-center justify-center bg-orange-100 text-orange-600 mb-3">
                <i class='bx bx-file text-lg'></i>
            </div>
            <h3 class="font-semibold text-gray-800">SPED Fiscal</h3>
            <p class="text-xs text-gray-500 mt-1">Escrituração fiscal digital do ICMS e IPI</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5 hover:border-orange-300 hover:shadow-sm transition-all cursor-pointer">
            <div class="w-10 h-10 rounded-lg flex items-center justify-center bg-blue-100 text-blue-600 mb-3">
                <i class='bx bx-file text-lg'></i>
            </div>
            <h3 class="font-semibold text-gray-800">SPED Contábil</h3>
            <p class="text-xs text-gray-500 mt-1">Escrituração contábil digital (ECD)</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5 hover:border-orange-300 hover:shadow-sm transition-all cursor-pointer">
            <div class="w-10 h-10 rounded-lg flex items-center justify-center bg-emerald-100 text-emerald-600 mb-3">
                <i class='bx bx-line-chart text-lg'></i>
            </div>
            <h3 class="font-semibold text-gray-800">DRE</h3>
            <p class="text-xs text-gray-500 mt-1">Demonstração do resultado do exercício</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5 hover:border-orange-300 hover:shadow-sm transition-all cursor-pointer">
            <div class="w-10 h-10 rounded-lg flex items-center justify-center bg-amber-100 text-amber-600 mb-3">
                <i class='bx bx-data text-lg'></i>
            </div>
            <h3 class="font-semibold text-gray-800">Balancete</h3>
            <p class="text-xs text-gray-500 mt-1">Balancete de verificação do período</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5 hover:border-orange-300 hover:shadow-sm transition-all cursor-pointer">
            <div class="w-10 h-10 rounded-lg flex items-center justify-center bg-violet-100 text-violet-600 mb-3">
                <i class='bx bx-calculator text-lg'></i>
            </div>
            <h3 class="font-semibold text-gray-800">Livro Caixa</h3>
            <p class="text-xs text-gray-500 mt-1">Movimentações financeiras do período</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5 hover:border-orange-300 hover:shadow-sm transition-all cursor-pointer">
            <div class="w-10 h-10 rounded-lg flex items-center justify-center bg-rose-100 text-rose-600 mb-3">
                <i class='bx bx-printer text-lg'></i>
            </div>
            <h3 class="font-semibold text-gray-800">Relatório Personalizado</h3>
            <p class="text-xs text-gray-500 mt-1">Exportar dados em PDF ou CSV</p>
        </div>
    </div>
</div>
