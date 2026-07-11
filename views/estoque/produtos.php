<?php $pageTitle = $pageTitle ?? 'Produtos'; ?>
<div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Produtos</h1>
            <p class="text-sm text-gray-500 mt-1">Cadastro de produtos com informações fiscais e contábeis</p>
        </div>
        <a href="<?= BASE_URL ?>/estoque/produtoForm" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700 transition-colors">
            <i class='bx bx-plus'></i> Novo Produto
        </a>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200">
                    <th class="text-left p-3 font-semibold text-gray-600">Código</th>
                    <th class="text-left p-3 font-semibold text-gray-600">Nome</th>
                    <th class="text-left p-3 font-semibold text-gray-600">NCM</th>
                    <th class="text-left p-3 font-semibold text-gray-600">CEST</th>
                    <th class="text-right p-3 font-semibold text-gray-600">Custo Médio</th>
                    <th class="text-right p-3 font-semibold text-gray-600">Preço Venda</th>
                    <th class="text-center p-3 font-semibold text-gray-600">Ativo</th>
                    <th class="text-right p-3 font-semibold text-gray-600">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($produtos)): ?>
                    <tr><td colspan="8" class="p-8 text-center text-gray-400">Nenhum produto cadastrado.</td></tr>
                <?php else: ?>
                    <?php foreach ($produtos as $p): ?>
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="p-3 font-mono text-xs text-gray-600"><?= htmlspecialchars($p['codigo']) ?></td>
                            <td class="p-3">
                                <span class="font-medium text-gray-800"><?= htmlspecialchars($p['nome']) ?></span>
                                <?php if (!empty($p['categoria'])): ?>
                                    <span class="text-xs text-gray-400">/ <?= htmlspecialchars($p['categoria']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="p-3 font-mono text-xs text-gray-600"><?= htmlspecialchars($p['ncm'] ?? '-') ?></td>
                            <td class="p-3 font-mono text-xs text-gray-600"><?= htmlspecialchars($p['cest'] ?? '-') ?></td>
                            <td class="p-3 text-right font-medium text-gray-800">R$ <?= number_format($p['custo_medio'] ?? $p['custo_aquisicao'], 2, ',', '.') ?></td>
                            <td class="p-3 text-right font-medium text-emerald-600">R$ <?= number_format($p['preco_venda'], 2, ',', '.') ?></td>
                            <td class="p-3 text-center">
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium <?= $p['ativo'] ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500' ?>">
                                    <?= $p['ativo'] ? 'Sim' : 'Não' ?>
                                </span>
                            </td>
                            <td class="p-3 text-right space-x-1">
                                <a href="<?= BASE_URL ?>/estoque/produtoForm/<?= $p['id'] ?>" class="inline-block px-2 py-1 text-xs font-medium text-blue-600 hover:bg-blue-50 rounded">
                                    <i class='bx bx-edit'></i>
                                </a>
                                <a href="<?= BASE_URL ?>/estoque/excluirProduto/<?= $p['id'] ?>"
                                   class="inline-block px-2 py-1 text-xs font-medium text-red-600 hover:bg-red-50 rounded"
                                   onclick="return confirm('Excluir permanentemente este produto?')">
                                    <i class='bx bx-trash'></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
