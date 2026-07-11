<?php
$isEdit = isset($conciliacao) && $conciliacao !== null;
$actionUrl = BASE_URL . '/contabil/salvarConciliacao';
?>
<div class="p-6 max-w-4xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800"><?= $isEdit ? 'Editar Conciliação' : 'Nova Conciliação Bancária' ?></h1>
            <p class="text-sm text-gray-500 mt-1">Compare os saldos do extrato bancário com os lançamentos do sistema</p>
        </div>
    </div>

    <form action="<?= $actionUrl ?>" method="POST" class="bg-white rounded-xl border border-gray-200 p-6 space-y-5">
        <input type="hidden" name="id" value="<?= $isEdit ? htmlspecialchars($conciliacao['id']) : '' ?>">

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Banco <span class="text-red-500">*</span></label>
                <select name="banco_id" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                    <option value="">Selecione</option>
                    <?php foreach ($bancos as $b): ?>
                        <option value="<?= $b['id'] ?>" <?= $isEdit && $conciliacao['banco_id'] == $b['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($b['nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div></div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Período Início <span class="text-red-500">*</span></label>
                <input type="date" name="periodo_inicio" required
                       value="<?= $isEdit ? htmlspecialchars($conciliacao['periodo_inicio']) : date('Y-m-01') ?>"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Período Fim <span class="text-red-500">*</span></label>
                <input type="date" name="periodo_fim" required
                       value="<?= $isEdit ? htmlspecialchars($conciliacao['periodo_fim']) : date('Y-m-t') ?>"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Saldo do Extrato</label>
                <input type="number" name="saldo_extrato" step="0.01"
                       value="<?= $isEdit ? htmlspecialchars($conciliacao['saldo_extrato']) : '0.00' ?>"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Saldo do Sistema</label>
                <input type="number" name="saldo_sistema" step="0.01"
                       value="<?= $isEdit ? htmlspecialchars($conciliacao['saldo_sistema']) : '0.00' ?>"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Observações</label>
            <textarea name="observacoes" rows="3" maxlength="500"
                      placeholder="Informações complementares sobre a conciliação"
                      class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"><?= $isEdit ? htmlspecialchars($conciliacao['observacoes'] ?? '') : '' ?></textarea>
        </div>

        <?php if ($isEdit && !empty($itens)): ?>
            <div>
                <h3 class="text-sm font-semibold text-gray-700 mb-3">Itens da Conciliação</h3>
                <table class="w-full text-sm border border-gray-200 rounded-lg">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="text-left p-2 font-semibold text-gray-600">Data</th>
                            <th class="text-left p-2 font-semibold text-gray-600">Descrição</th>
                            <th class="text-right p-2 font-semibold text-gray-600">Valor</th>
                            <th class="text-left p-2 font-semibold text-gray-600">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($itens as $item): ?>
                            <tr class="border-b border-gray-100">
                                <td class="p-2 text-gray-600"><?= date('d/m/Y', strtotime($item['data_operacao'])) ?></td>
                                <td class="p-2 text-gray-700"><?= htmlspecialchars($item['descricao']) ?></td>
                                <td class="p-2 text-right font-mono">R$ <?= number_format($item['valor'], 2, ',', '.') ?></td>
                                <td class="p-2">
                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium
                                        <?= $item['status_conciliacao'] === 'conciliado' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' ?>">
                                        <?= $item['status_conciliacao'] === 'conciliado' ? 'Conciliado' : 'Pendente' ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <div class="flex items-center justify-between pt-4 border-t border-gray-200">
            <a href="<?= BASE_URL ?>/contabil/conciliacoes" class="text-sm text-gray-500 hover:text-gray-700">
                <i class='bx bx-arrow-back'></i> Voltar
            </a>
            <button type="submit" class="px-5 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700 transition-colors">
                <i class='bx bx-check'></i> <?= $isEdit ? 'Salvar Alterações' : 'Iniciar Conciliação' ?>
            </button>
        </div>
    </form>
</div>
