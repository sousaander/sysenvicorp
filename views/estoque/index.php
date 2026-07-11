<?php $pageTitle = $pageTitle ?? 'Estoque e Inventário'; ?>
<div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Estoque e Inventário</h1>
            <p class="text-sm text-gray-500 mt-1">Gestão de produtos, movimentações e inventário físico</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="<?= BASE_URL ?>/estoque/produtoForm" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700 transition-colors">
                <i class='bx bx-package'></i> Novo Produto
            </a>
            <a href="<?= BASE_URL ?>/estoque/entradaForm" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                <i class='bx bx-plus-circle'></i> Entrada
            </a>
            <a href="<?= BASE_URL ?>/estoque/saidaForm" class="inline-flex items-center gap-2 px-4 py-2 bg-rose-600 text-white text-sm font-medium rounded-lg hover:bg-rose-700 transition-colors">
                <i class='bx bx-minus-circle'></i> Saída
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center">
                    <i class='bx bx-package text-xl'></i>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-800"><?= $resumo['total_produtos'] ?? 0 ?></p>
                    <p class="text-xs text-gray-500">Produtos</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center">
                    <i class='bx bx-dollar text-xl'></i>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-800">R$ <?= number_format($resumo['valor_total_estoque'] ?? 0, 2, ',', '.') ?></p>
                    <p class="text-xs text-gray-500">Valor em Estoque</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center">
                    <i class='bx bx-transfer text-xl'></i>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-800"><?= $resumo['total_movimentos_mes'] ?? 0 ?></p>
                    <p class="text-xs text-gray-500">Movimentos no Mês</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center">
                    <i class='bx bx-clipboard text-xl'></i>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-800"><?= $resumo['inventarios_pendentes'] ?? 0 ?></p>
                    <p class="text-xs text-gray-500">Inventários Pendentes</p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <a href="<?= BASE_URL ?>/estoque/produtos" class="bg-white rounded-xl border border-gray-200 p-6 hover:border-blue-300 hover:shadow-sm transition-all group">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center group-hover:bg-blue-600 group-hover:text-white transition-colors">
                    <i class='bx bx-package text-xl'></i>
                </div>
                <h3 class="font-semibold text-gray-800">Produtos</h3>
            </div>
            <p class="text-sm text-gray-600">Cadastro completo com NCM, CEST, alíquotas fiscais, custo de aquisição e preço de venda.</p>
        </a>
        <a href="<?= BASE_URL ?>/estoque/movimentos" class="bg-white rounded-xl border border-gray-200 p-6 hover:border-emerald-300 hover:shadow-sm transition-all group">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center group-hover:bg-emerald-600 group-hover:text-white transition-colors">
                    <i class='bx bx-transfer text-xl'></i>
                </div>
                <h3 class="font-semibold text-gray-800">Movimentações</h3>
            </div>
            <p class="text-sm text-gray-600">Entradas, saídas e ajustes com cálculo automático de custo médio ponderado.</p>
        </a>
        <a href="<?= BASE_URL ?>/estoque/saldo" class="bg-white rounded-xl border border-gray-200 p-6 hover:border-amber-300 hover:shadow-sm transition-all group">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center group-hover:bg-amber-600 group-hover:text-white transition-colors">
                    <i class='bx bx-bar-chart text-xl'></i>
                </div>
                <h3 class="font-semibold text-gray-800">Saldo</h3>
            </div>
            <p class="text-sm text-gray-600">Consulta de saldo atual por produto com quantidade, custo médio e valor total.</p>
        </a>
        <a href="<?= BASE_URL ?>/estoque/inventarios" class="bg-white rounded-xl border border-gray-200 p-6 hover:border-purple-300 hover:shadow-sm transition-all group">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center group-hover:bg-purple-600 group-hover:text-white transition-colors">
                    <i class='bx bx-clipboard text-xl'></i>
                </div>
                <h3 class="font-semibold text-gray-800">Inventário Físico</h3>
            </div>
            <p class="text-sm text-gray-600">Criação, contagem física, ajuste automático de saldo e divergências.</p>
        </a>
    </div>
</div>
