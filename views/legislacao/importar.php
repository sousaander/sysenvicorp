<?php $pageTitle = $pageTitle ?? 'Importar Legislação'; ?>
<div class="p-6 max-w-3xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Importar Legislação</h1>
            <p class="text-sm text-gray-500 mt-1">Importe versões legislativas em lote via JSON</p>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-5">
        <form method="POST" action="<?= BASE_URL ?>/legislacao/importar">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Módulo</label>
                <select name="modulo" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <?php foreach ($modulos as $k => $v): ?>
                        <option value="<?= $k ?>"><?= $v ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Dados (JSON)</label>
                <textarea name="dados_json" rows="12" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm font-mono"
                placeholder='[
    {
        "titulo": "Alteração ICMS SP",
        "tipo_ato": "Portaria",
        "numero_ato": "123/2026",
        "orgao_emissor": "SEFAZ-SP",
        "data_publicacao": "2026-06-01",
        "data_vigencia": "2026-07-01",
        "resumo_mudancas": "Redução de alíquota...",
        "versao": "1.0"
    }
]'></textarea>
                <p class="text-xs text-gray-400 mt-1">O JSON deve conter um array de objetos com os campos desejados.</p>
            </div>

            <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                <a href="<?= BASE_URL ?>/legislacao" class="text-sm text-gray-500 hover:text-gray-700">
                    <i class='bx bx-arrow-back'></i> Voltar
                </a>
                <button type="submit" class="px-5 py-2 bg-purple-600 text-white text-sm font-medium rounded-lg hover:bg-purple-700 transition-colors">
                    <i class='bx bx-import'></i> Importar
                </button>
            </div>
        </form>

        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 text-sm text-blue-800">
            <i class='bx bx-info-circle'></i>
            <strong>Formato esperado:</strong> array JSON com objetos contendo os campos
            <code>titulo</code>, <code>tipo_ato</code>, <code>numero_ato</code>, <code>orgao_emissor</code>,
            <code>data_publicacao</code>, <code>data_vigencia</code>, <code>data_revogacao</code>,
            <code>resumo_mudancas</code>, <code>impacto_esperado</code>, <code>versao</code>.
            Apenas <code>titulo</code> é obrigatório em cada item.
        </div>
    </div>
</div>
