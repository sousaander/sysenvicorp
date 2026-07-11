<?php $pageTitle = $pageTitle ?? 'Parâmetros Contábeis'; ?>
<div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Parâmetros Contábeis</h1>
            <p class="text-sm text-gray-500 mt-1">Módulo de contabilidade com plano de contas, lançamentos automáticos e demonstrações</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center bg-emerald-100 text-emerald-600 text-lg">
                    <i class='bx bx-book'></i>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wider font-medium">Contas</p>
                    <p class="text-2xl font-bold text-gray-800"><?= $resumo['total_contas'] ?? 0 ?></p>
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
                    <p class="text-xs text-gray-500 uppercase tracking-wider font-medium">Conciliações</p>
                    <p class="text-2xl font-bold text-gray-800"><?= $resumo['total_conciliacoes'] ?? 0 ?></p>
                    <p class="text-[10px] text-gray-400">pendentes</p>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Módulos Contábeis</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <a href="<?= BASE_URL ?>/contabil/planocontas" class="flex items-center gap-3 p-4 rounded-lg border border-gray-200 hover:border-emerald-300 hover:bg-emerald-50 transition-colors">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center bg-emerald-100 text-emerald-600">
                    <i class='bx bx-list-ul text-xl'></i>
                </div>
                <div>
                    <p class="font-medium text-gray-800">Plano de Contas</p>
                    <p class="text-xs text-gray-500">Estrutura personalizável aderente às normas CPC</p>
                </div>
            </a>
            <a href="<?= BASE_URL ?>/contabil/integrar" class="flex items-center gap-3 p-4 rounded-lg border border-gray-200 hover:border-blue-300 hover:bg-blue-50 transition-colors">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center bg-blue-100 text-blue-600">
                    <i class='bx bx-transfer text-xl'></i>
                </div>
                <div>
                    <p class="font-medium text-gray-800">Lançamentos Automáticos</p>
                    <p class="text-xs text-gray-500">Integração com financeiro, folha e contratos</p>
                </div>
            </a>
            <a href="<?= BASE_URL ?>/contabil/lancamentos" class="flex items-center gap-3 p-4 rounded-lg border border-gray-200 hover:border-blue-300 hover:bg-blue-50 transition-colors">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center bg-blue-100 text-blue-600">
                    <i class='bx bx-book-open text-xl'></i>
                </div>
                <div>
                    <p class="font-medium text-gray-800">Lançamentos Contábeis</p>
                    <p class="text-xs text-gray-500">Registros manuais com partida dobrada</p>
                </div>
            </a>
            <a href="<?= BASE_URL ?>/contabil/demonstracoes" class="flex items-center gap-3 p-4 rounded-lg border border-gray-200 hover:border-purple-300 hover:bg-purple-50 transition-colors">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center bg-purple-100 text-purple-600">
                    <i class='bx bx-bar-chart-alt-2 text-xl'></i>
                </div>
                <div>
                    <p class="font-medium text-gray-800">Demonstrações Contábeis</p>
                    <p class="text-xs text-gray-500">Balanço Patrimonial, DRE, Fluxo de Caixa</p>
                </div>
            </a>
            <a href="<?= BASE_URL ?>/contabil/conciliacoes" class="flex items-center gap-3 p-4 rounded-lg border border-gray-200 hover:border-amber-300 hover:bg-amber-50 transition-colors">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center bg-amber-100 text-amber-600">
                    <i class='bx bx-check-shield text-xl'></i>
                </div>
                <div>
                    <p class="font-medium text-gray-800">Conciliação Bancária</p>
                    <p class="text-xs text-gray-500">Validação de lançamentos com extratos</p>
                </div>
            </a>
            <a href="<?= BASE_URL ?>/contabil/parametros" class="flex items-center gap-3 p-4 rounded-lg border border-gray-200 hover:border-slate-300 hover:bg-slate-50 transition-colors">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center bg-slate-100 text-slate-600">
                    <i class='bx bx-cog text-xl'></i>
                </div>
                <div>
                    <p class="font-medium text-gray-800">Parâmetros Contábeis</p>
                    <p class="text-xs text-gray-500">Configurações e preferências do módulo</p>
                </div>
            </a>
        </div>
    </div>
</div>
