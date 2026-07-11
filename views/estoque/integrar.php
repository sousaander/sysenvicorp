<?php $pageTitle = $pageTitle ?? 'Integração Contábil - Estoque'; ?>
<div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Integração Contábil do Estoque</h1>
            <p class="text-sm text-gray-500 mt-1">Impacto fiscal das movimentações de estoque no plano de contas</p>
        </div>
        <a href="<?= BASE_URL ?>/contabil/lancamentos" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
            <i class='bx bx-list-ul'></i> Ver Lançamentos Contábeis
        </a>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 rounded-lg flex items-center justify-center bg-amber-100 text-amber-600">
                <i class='bx bx-package text-xl'></i>
            </div>
            <div>
                <h3 class="font-semibold text-gray-800">Estoque e CMV</h3>
                <p class="text-xs text-gray-500">Custo das Mercadorias Vendidas e impacto no estoque</p>
            </div>
        </div>
        <p class="text-sm text-gray-600 mb-4">
            Integra as movimentações de estoque (entradas e saídas) gerando lançamentos contábeis
            de débito e crédito nas contas de estoque e CMV (Custo das Mercadorias Vendidas),
            incluindo os tributos incidentes (ICMS, IPI, PIS, COFINS).
        </p>
        <form method="POST" action="<?= BASE_URL ?>/estoque/integrarContabil" class="flex items-center gap-2">
            <input type="date" name="data_movimento" value="<?= date('Y-m-d') ?>"
                   class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm">
            <button type="submit" class="px-4 py-1.5 bg-amber-600 text-white text-sm font-medium rounded-lg hover:bg-amber-700"
                    onclick="return confirm('Integrar movimentos de estoque pendentes à contabilidade?')">
                <i class='bx bx-sync'></i> Integrar Agora
            </button>
        </form>
    </div>

    <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 text-sm text-amber-800">
        <i class='bx bx-info-circle'></i>
        A integração gera automaticamente:
        <ul class="list-disc list-inside mt-2 space-y-1">
            <li><strong>Entradas:</strong> Débito na conta de Estoque, Crédito na conta de Fornecedores/Caixa</li>
            <li><strong>Saídas (vendas):</strong> Débito na conta de CMV, Crédito na conta de Estoque</li>
            <li><strong>Saídas (consumo):</strong> Débito na conta de Despesa, Crédito na conta de Estoque</li>
        </ul>
        Os lançamentos duplicados são evitados automaticamente.
    </div>
</div>
