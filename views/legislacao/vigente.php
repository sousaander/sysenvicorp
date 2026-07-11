<?php $pageTitle = $pageTitle ?? 'Legislação Vigente'; ?>
<div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Legislação Vigente</h1>
            <p class="text-sm text-gray-500 mt-1"><?= $modulo ?> - Versão atualmente em vigor</p>
        </div>
        <a href="<?= BASE_URL ?>/legislacao" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200">
            <i class='bx bx-arrow-back'></i> Voltar
        </a>
    </div>

    <?php if ($versao): ?>
        <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-bold text-gray-800"><?= htmlspecialchars($versao['titulo']) ?></h2>
                    <p class="text-sm text-gray-500 mt-1">
                        <?= htmlspecialchars($versao['tipo_ato'] ?? '') ?>
                        <?= $versao['numero_ato'] ? 'nº ' . htmlspecialchars($versao['numero_ato']) : '' ?>
                    </p>
                </div>
                <span class="px-3 py-1 rounded-full text-sm font-bold bg-emerald-100 text-emerald-700">VIGENTE</span>
            </div>

            <?php if (!empty($versao['descricao'])): ?>
                <p class="text-sm text-gray-600"><?= nl2br(htmlspecialchars($versao['descricao'])) ?></p>
            <?php endif; ?>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div>
                    <label class="text-xs text-gray-500">Módulo</label>
                    <p class="text-sm font-medium"><?= $modulos[$versao['modulo']] ?? $versao['modulo'] ?></p>
                </div>
                <div>
                    <label class="text-xs text-gray-500">Versão</label>
                    <p class="text-sm font-mono"><?= htmlspecialchars($versao['versao'] ?? '-') ?></p>
                </div>
                <div>
                    <label class="text-xs text-gray-500">Publicação</label>
                    <p class="text-sm"><?= $versao['data_publicacao'] ? date('d/m/Y', strtotime($versao['data_publicacao'])) : '-' ?></p>
                </div>
                <div>
                    <label class="text-xs text-gray-500">Vigência</label>
                    <p class="text-sm"><?= $versao['data_vigencia'] ? date('d/m/Y', strtotime($versao['data_vigencia'])) : '-' ?></p>
                </div>
            </div>

            <?php if (!empty($versao['resumo_mudancas'])): ?>
                <div>
                    <label class="text-xs text-gray-500 font-semibold">Resumo das Mudanças</label>
                    <p class="text-sm text-gray-700 mt-1"><?= nl2br(htmlspecialchars($versao['resumo_mudancas'])) ?></p>
                </div>
            <?php endif; ?>

            <?php if (!empty($versao['impacto_esperado'])): ?>
                <div>
                    <label class="text-xs text-gray-500 font-semibold">Impacto Esperado</label>
                    <p class="text-sm text-gray-700 mt-1"><?= nl2br(htmlspecialchars($versao['impacto_esperado'])) ?></p>
                </div>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="bg-white rounded-xl border border-gray-200 p-8 text-center text-gray-400">
            Nenhuma versão vigente encontrada para o módulo <strong><?= htmlspecialchars($modulo) ?></strong>.
        </div>
    <?php endif; ?>
</div>
