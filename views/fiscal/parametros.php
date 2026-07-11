<?php $pageTitle = $pageTitle ?? 'Parâmetros Fiscais'; ?>
<div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Parâmetros Fiscais</h1>
            <p class="text-sm text-gray-500 mt-1">Configurações de alíquotas, CST, CFOP e regime tributário</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Dados da Empresa</h2>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between"><span class="text-gray-500">CNPJ:</span><span class="font-medium"><?= htmlspecialchars($empresa['cnpj'] ?? '-') ?></span></div>
                <div class="flex justify-between"><span class="text-gray-500">Razão Social:</span><span class="font-medium"><?= htmlspecialchars($empresa['razao_social'] ?? '-') ?></span></div>
                <div class="flex justify-between"><span class="text-gray-500">Regime Tributário:</span><span class="font-medium"><?= htmlspecialchars($empresa['regime_tributario'] ?? 'Não configurado') ?></span></div>
                <div class="flex justify-between"><span class="text-gray-500">Ambiente NFe:</span><span class="font-medium"><?= ($empresa['nfe_ambiente'] ?? 'homologacao') === 'producao' ? 'Produção' : 'Homologação' ?></span></div>
                <div class="flex justify-between"><span class="text-gray-500">Certificado:</span><span class="font-medium"><?= !empty($empresa['caminho_certificado']) ? 'Configurado' : 'Não configurado' ?></span></div>
            </dl>
            <a href="<?= BASE_URL ?>/configuracoes" class="mt-4 inline-block text-sm text-orange-600 hover:text-orange-700">Gerenciar configurações da empresa →</a>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-800">Alíquotas Fiscais</h2>
                <button onclick="abrirModalAliquota()" class="px-3 py-1.5 bg-orange-600 text-white rounded-lg hover:bg-orange-700 text-xs">Nova Alíquota</button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead><tr class="text-left text-gray-500 border-b"><th class="pb-2">UF</th><th class="pb-2">Município</th><th class="pb-2">ISS</th><th class="pb-2">CFOP</th><th class="pb-2">Regime</th><th class="pb-2">Ações</th></tr></thead>
                    <tbody>
                        <?php if (empty($aliquotas)): ?>
                            <tr><td colspan="6" class="py-4 text-center text-gray-400">Nenhuma alíquota cadastrada.</td></tr>
                        <?php else: ?>
                            <?php foreach ($aliquotas as $a): ?>
                                <tr class="border-t border-gray-100">
                                    <td class="py-2 text-sm"><?= htmlspecialchars($a['uf']) ?></td>
                                    <td class="py-2 text-sm"><?= htmlspecialchars($a['municipio']) ?></td>
                                    <td class="py-2 text-sm"><?= htmlspecialchars($a['aliquota_iss']) ?>%</td>
                                    <td class="py-2 text-sm"><?= htmlspecialchars($a['cfop_padrao'] ?? '-') ?></td>
                                    <td class="py-2 text-sm"><?= htmlspecialchars($a['regime_tributario'] ?? '-') ?></td>
                                    <td class="py-2 text-sm">
                                        <a href="#" class="text-orange-600 hover:text-orange-700" onclick="editarAliquota(<?= $a['id'] ?>)">Editar</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
