<?php $pageTitle = $pageTitle ?? 'Próximas Alterações Legislativas'; ?>
<div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Próximas Alterações Legislativas</h1>
            <p class="text-sm text-gray-500 mt-1">Normas com vigência futura ou revogação programada (<?= $dias ?> dias)</p>
        </div>
        <a href="<?= BASE_URL ?>/legislacao" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200">
            <i class='bx bx-arrow-back'></i> Voltar
        </a>
    </div>

    <?php if (empty($alteracoes)): ?>
        <div class="bg-white rounded-xl border border-gray-200 p-8 text-center text-gray-400">
            Nenhuma alteração prevista nos próximos <?= $dias ?> dias.
        </div>
    <?php else: ?>
        <div class="space-y-4">
            <?php foreach ($alteracoes as $a): ?>
                <?php $ehVigencia = $a['data_vigencia'] >= date('Y-m-d'); ?>
                <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-start gap-4
                    <?= $ehVigencia ? 'border-l-4 border-l-blue-500' : 'border-l-4 border-l-red-500' ?>">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="font-semibold text-gray-800"><?= htmlspecialchars($a['titulo']) ?></span>
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">
                                <?= $a['modulo'] ?>
                            </span>
                        </div>
                        <p class="text-sm text-gray-600">
                            <?= $ehVigencia ? 'Início da vigência' : 'Revogação' ?>:
                            <strong><?= date('d/m/Y', strtotime($ehVigencia ? $a['data_vigencia'] : $a['data_revogacao'])) ?></strong>
                            <?php if (!empty($a['resumo_mudancas'])): ?>
                                &middot; <?= htmlspecialchars(mb_strimwidth($a['resumo_mudancas'], 0, 100, '...')) ?>
                            <?php endif; ?>
                        </p>
                    </div>
                    <a href="<?= BASE_URL ?>/legislacao/form/<?= $a['id'] ?>" class="text-sm text-blue-600 hover:underline whitespace-nowrap">
                        <i class='bx bx-show'></i> Ver
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
