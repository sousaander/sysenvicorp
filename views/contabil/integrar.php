<?php $pageTitle = $pageTitle ?? 'Integração Automática'; ?>
<div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Lançamentos Automáticos</h1>
            <p class="text-sm text-gray-500 mt-1">Integração com financeiro, folha de pagamento e contratos</p>
        </div>
        <a href="<?= BASE_URL ?>/contabil/lancamentos" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
            <i class='bx bx-list-ul'></i> Ver Lançamentos
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center bg-blue-100 text-blue-600">
                    <i class='bx bx-dollar-circle text-xl'></i>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-800">Financeiro</h3>
                    <p class="text-xs text-gray-500">Receitas e despesas do fluxo de caixa</p>
                </div>
            </div>
            <p class="text-sm text-gray-600 mb-4">Integra automaticamente contas a pagar e receber do módulo financeiro para o plano de contas contábil.</p>
            <form method="POST" action="<?= BASE_URL ?>/contabil/integrarFinanceiro" class="flex items-center gap-2">
                <select name="mes" class="rounded-lg border border-gray-300 px-2 py-1.5 text-sm flex-1">
                    <?php for ($m = 1; $m <= 12; $m++): ?>
                        <option value="<?= $m ?>" <?= $m == date('m') ? 'selected' : '' ?>><?= str_pad($m, 2, '0', STR_PAD_LEFT) ?></option>
                    <?php endfor; ?>
                </select>
                <select name="ano" class="rounded-lg border border-gray-300 px-2 py-1.5 text-sm flex-1">
                    <?php for ($a = date('Y') - 2; $a <= date('Y') + 1; $a++): ?>
                        <option value="<?= $a ?>" <?= $a == date('Y') ? 'selected' : '' ?>><?= $a ?></option>
                    <?php endfor; ?>
                </select>
                <button type="submit" class="px-4 py-1.5 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
                    Integrar
                </button>
            </form>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center bg-rose-100 text-rose-600">
                    <i class='bx bx-group text-xl'></i>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-800">Folha de Pagamento</h3>
                    <p class="text-xs text-gray-500">Proventos e encargos trabalhistas</p>
                </div>
            </div>
            <p class="text-sm text-gray-600 mb-4">Integra os valores de folha de pagamento, gerando os lançamentos de despesas com pessoal.</p>
            <form method="POST" action="<?= BASE_URL ?>/contabil/integrarFolha" class="flex items-center gap-2">
                <select name="mes" class="rounded-lg border border-gray-300 px-2 py-1.5 text-sm flex-1">
                    <?php for ($m = 1; $m <= 12; $m++): ?>
                        <option value="<?= $m ?>" <?= $m == date('m') ? 'selected' : '' ?>><?= str_pad($m, 2, '0', STR_PAD_LEFT) ?></option>
                    <?php endfor; ?>
                </select>
                <select name="ano" class="rounded-lg border border-gray-300 px-2 py-1.5 text-sm flex-1">
                    <?php for ($a = date('Y') - 2; $a <= date('Y') + 1; $a++): ?>
                        <option value="<?= $a ?>" <?= $a == date('Y') ? 'selected' : '' ?>><?= $a ?></option>
                    <?php endfor; ?>
                </select>
                <button type="submit" class="px-4 py-1.5 bg-rose-600 text-white text-sm font-medium rounded-lg hover:bg-rose-700">
                    Integrar
                </button>
            </form>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center bg-purple-100 text-purple-600">
                    <i class='bx bx-file text-xl'></i>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-800">Contratos</h3>
                    <p class="text-xs text-gray-500">Receitas contratuais recorrentes</p>
                </div>
            </div>
            <p class="text-sm text-gray-600 mb-4">Integra as parcelas de contratos recebidas, gerando lançamentos de receita.</p>
            <form method="POST" action="<?= BASE_URL ?>/contabil/integrarContratos" class="flex items-center gap-2">
                <select name="mes" class="rounded-lg border border-gray-300 px-2 py-1.5 text-sm flex-1">
                    <?php for ($m = 1; $m <= 12; $m++): ?>
                        <option value="<?= $m ?>" <?= $m == date('m') ? 'selected' : '' ?>><?= str_pad($m, 2, '0', STR_PAD_LEFT) ?></option>
                    <?php endfor; ?>
                </select>
                <select name="ano" class="rounded-lg border border-gray-300 px-2 py-1.5 text-sm flex-1">
                    <?php for ($a = date('Y') - 2; $a <= date('Y') + 1; $a++): ?>
                        <option value="<?= $a ?>" <?= $a == date('Y') ? 'selected' : '' ?>><?= $a ?></option>
                    <?php endfor; ?>
                </select>
                <button type="submit" class="px-4 py-1.5 bg-purple-600 text-white text-sm font-medium rounded-lg hover:bg-purple-700">
                    Integrar
                </button>
            </form>
        </div>
    </div>

    <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 text-sm text-amber-800">
        <i class='bx bx-info-circle'></i>
        A integração evita lançamentos duplicados. Cada transação é integrada apenas uma vez.
        Os lançamentos gerados ficam disponíveis para conciliação bancária e auditoria.
    </div>
</div>
