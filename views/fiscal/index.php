<?php $pageTitle = $pageTitle ?? 'Fiscal e Contábil'; ?>
<div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Fiscal e Contábil</h1>
            <p class="text-sm text-gray-500 mt-1">Dashboard consolidado de obrigações fiscais e contábeis</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center bg-orange-100 text-orange-600 text-lg">
                    <i class='bx bx-receipt'></i>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wider font-medium">Notas Fiscais</p>
                    <p class="text-2xl font-bold text-gray-800"><?= $resumo['total_notas'] ?? 0 ?></p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center bg-blue-100 text-blue-600 text-lg">
                    <i class='bx bx-book-open'></i>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wider font-medium">Lançamentos</p>
                    <p class="text-2xl font-bold text-gray-800"><?= $resumo['total_lancamentos'] ?? 0 ?></p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center bg-emerald-100 text-emerald-600 text-lg">
                    <i class='bx bx-trending-up'></i>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wider font-medium">Receitas</p>
                    <p class="text-2xl font-bold text-gray-800">R$ <?= number_format($resumo['total_receitas'] ?? 0, 2, ',', '.') ?></p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center bg-rose-100 text-rose-600 text-lg">
                    <i class='bx bx-trending-down'></i>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wider font-medium">Despesas</p>
                    <p class="text-2xl font-bold text-gray-800">R$ <?= number_format($resumo['total_despesas'] ?? 0, 2, ',', '.') ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Acesso Rápido</h2>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <a href="<?= BASE_URL ?>/fiscal/notas" class="flex items-center gap-3 p-4 rounded-lg border border-gray-200 hover:border-orange-300 hover:bg-orange-50 transition-colors">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center bg-orange-100 text-orange-600">
                    <i class='bx bx-receipt text-xl'></i>
                </div>
                <div>
                    <p class="font-medium text-gray-800">Notas Fiscais</p>
                    <p class="text-xs text-gray-500">Gerenciar NFs emitidas e recebidas</p>
                </div>
            </a>
            <a href="<?= BASE_URL ?>/fiscal/lancamentos" class="flex items-center gap-3 p-4 rounded-lg border border-gray-200 hover:border-orange-300 hover:bg-orange-50 transition-colors">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center bg-blue-100 text-blue-600">
                    <i class='bx bx-book-open text-xl'></i>
                </div>
                <div>
                    <p class="font-medium text-gray-800">Lançamentos</p>
                    <p class="text-xs text-gray-500">Lançamentos contábeis do período</p>
                </div>
            </a>
            <a href="<?= BASE_URL ?>/fiscal/relatorios" class="flex items-center gap-3 p-4 rounded-lg border border-gray-200 hover:border-orange-300 hover:bg-orange-50 transition-colors">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center bg-amber-100 text-amber-600">
                    <i class='bx bx-bar-chart-alt-2 text-xl'></i>
                </div>
                <div>
                    <p class="font-medium text-gray-800">Relatórios</p>
                    <p class="text-xs text-gray-500">SPED, DRE, balancetes</p>
                </div>
            </a>
            <a href="<?= BASE_URL ?>/fiscal/sped" class="flex items-center gap-3 p-4 rounded-lg border border-gray-200 hover:border-orange-300 hover:bg-orange-50 transition-colors">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center bg-violet-100 text-violet-600">
                    <i class='bx bx-file text-xl'></i>
                </div>
                <div>
                    <p class="font-medium text-gray-800">SPED</p>
                    <p class="text-xs text-gray-500">Escrituração fiscal e contábil</p>
                </div>
            </a>
            <a href="<?= BASE_URL ?>/fiscal/retencoes" class="flex items-center gap-3 p-4 rounded-lg border border-gray-200 hover:border-orange-300 hover:bg-orange-50 transition-colors">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center bg-rose-100 text-rose-600">
                    <i class='bx bx-calculator text-xl'></i>
                </div>
                <div>
                    <p class="font-medium text-gray-800">Retenções</p>
                    <p class="text-xs text-gray-500">IRRF, INSS, ISS, PIS, COFINS, CSLL</p>
                </div>
            </a>
            <a href="<?= BASE_URL ?>/fiscal/parametros" class="flex items-center gap-3 p-4 rounded-lg border border-gray-200 hover:border-orange-300 hover:bg-orange-50 transition-colors">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center bg-slate-100 text-slate-600">
                    <i class='bx bx-slider-alt text-xl'></i>
                </div>
                <div>
                    <p class="font-medium text-gray-800">Parâmetros</p>
                    <p class="text-xs text-gray-500">Alíquotas, CST, CFOP, regime</p>
                </div>
            </a>
        </div>
    </div>
</div>
