<?php $pageTitle = $pageTitle ?? 'Parâmetros Contábeis'; ?>
<div class="p-6 max-w-3xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Parâmetros Contábeis</h1>
            <p class="text-sm text-gray-500 mt-1">Configurações e preferências do módulo de contabilidade</p>
        </div>
    </div>

    <form action="<?= BASE_URL ?>/contabil/salvarParametros" method="POST" class="bg-white rounded-xl border border-gray-200 p-6 space-y-5">
        <div class="space-y-4">
            <?php
            $paramMap = [];
            foreach ($parametros as $p) {
                $paramMap[$p['chave']] = $p['valor'];
            }
            ?>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Método de Depreciação</label>
                <select name="parametros[metodo_depreciacao]" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                    <option value="linear" <?= ($paramMap['metodo_depreciacao'] ?? 'linear') === 'linear' ? 'selected' : '' ?>>Linear (Quotas Constantes)</option>
                    <option value="sac" <?= ($paramMap['metodo_depreciacao'] ?? '') === 'sac' ? 'selected' : '' ?>>SAC (Sistema de Amortização Constante)</option>
                </select>
                <p class="text-xs text-gray-400 mt-1">Método utilizado para calcular a depreciação de ativos</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Regime Tributário</label>
                <select name="parametros[regime_tributario]" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                    <option value="simples_nacional" <?= ($paramMap['regime_tributario'] ?? '') === 'simples_nacional' ? 'selected' : '' ?>>Simples Nacional</option>
                    <option value="lucro_presumido" <?= ($paramMap['regime_tributario'] ?? 'lucro_presumido') === 'lucro_presumido' ? 'selected' : '' ?>>Lucro Presumido</option>
                    <option value="lucro_real" <?= ($paramMap['regime_tributario'] ?? '') === 'lucro_real' ? 'selected' : '' ?>>Lucro Real</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Mês de Fechamento do Exercício Fiscal</label>
                <select name="parametros[competencias_mes_fechamento]" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                    <?php for ($m = 1; $m <= 12; $m++): ?>
                        <option value="<?= $m ?>" <?= ($paramMap['competencias_mes_fechamento'] ?? '12') == $m ? 'selected' : '' ?>>
                            <?= str_pad($m, 2, '0', STR_PAD_LEFT) ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>

            <div class="border-t border-gray-200 pt-4">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">Integrações Automáticas</h3>

                <label class="flex items-center justify-between p-3 bg-gray-50 rounded-lg mb-2">
                    <div>
                        <span class="text-sm font-medium text-gray-700">Integrar Financeiro</span>
                        <p class="text-xs text-gray-400">Lançamentos automáticos de contas a pagar/receber</p>
                    </div>
                    <input type="hidden" name="parametros[integrar_financeiro]" value="0">
                    <input type="checkbox" name="parametros[integrar_financeiro]" value="1"
                           <?= ($paramMap['integrar_financeiro'] ?? '1') == '1' ? 'checked' : '' ?>
                           class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                </label>

                <label class="flex items-center justify-between p-3 bg-gray-50 rounded-lg mb-2">
                    <div>
                        <span class="text-sm font-medium text-gray-700">Integrar Folha de Pagamento</span>
                        <p class="text-xs text-gray-400">Lançamentos automáticos da folha de pagamento</p>
                    </div>
                    <input type="hidden" name="parametros[integrar_folha]" value="0">
                    <input type="checkbox" name="parametros[integrar_folha]" value="1"
                           <?= ($paramMap['integrar_folha'] ?? '1') == '1' ? 'checked' : '' ?>
                           class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                </label>

                <label class="flex items-center justify-between p-3 bg-gray-50 rounded-lg mb-2">
                    <div>
                        <span class="text-sm font-medium text-gray-700">Rateio por Centro de Custo</span>
                        <p class="text-xs text-gray-400">Habilitar rateio automático por centro de custo</p>
                    </div>
                    <input type="hidden" name="parametros[rateio_por_centro_custo]" value="0">
                    <input type="checkbox" name="parametros[rateio_por_centro_custo]" value="1"
                           <?= ($paramMap['rateio_por_centro_custo'] ?? '1') == '1' ? 'checked' : '' ?>
                           class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                </label>
            </div>
        </div>

        <div class="flex items-center justify-between pt-4 border-t border-gray-200">
            <a href="<?= BASE_URL ?>/contabil" class="text-sm text-gray-500 hover:text-gray-700">
                <i class='bx bx-arrow-back'></i> Voltar
            </a>
            <button type="submit" class="px-5 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700 transition-colors">
                <i class='bx bx-check'></i> Salvar Parâmetros
            </button>
        </div>
    </form>
</div>
