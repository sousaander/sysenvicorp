<div class="flex justify-between items-center mb-6">
    <div>
        <h2 class="text-2xl font-bold text-gray-800"><?php echo htmlspecialchars($pageTitle); ?></h2>
        <p class="text-gray-600">Histórico de despesas de projeto que já foram aprovadas e estão em fluxo de pagamento.</p>
    </div>
    <div class="flex gap-2">
        <a href="<?php echo BASE_URL; ?>/financeiro/aprovacaoPrestacaoContas" class="bg-indigo-100 text-indigo-700 px-4 py-2 rounded-md hover:bg-indigo-200 font-medium text-sm">
            Ir para Aprovações
        </a>
        <a href="<?php echo BASE_URL; ?>/financeiro" class="bg-gray-200 text-gray-800 px-4 py-2 rounded-md hover:bg-gray-300 font-medium flex items-center">
            &larr; Voltar
        </a>
    </div>
</div>

<form method="GET" class="mb-6 bg-white p-4 rounded-lg shadow-sm flex items-end gap-4 border border-gray-100">
    <div class="flex-grow max-w-xs">
        <label for="mes" class="block text-sm font-medium text-gray-700 mb-1">Filtrar por Mês (Vencimento)</label>
        <input type="month" name="mes" id="mes" value="<?php echo htmlspecialchars($mesFiltro ?? ''); ?>" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2 border">
    </div>
    <div>
        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 font-medium shadow-sm transition-colors h-[38px]">
            Filtrar
        </button>
        <?php if (!empty($mesFiltro)): ?>
            <a href="?" class="ml-2 text-gray-600 hover:text-gray-800 text-sm font-medium underline">Limpar</a>
        <?php endif; ?>
    </div>
</form>

<div class="bg-white p-6 rounded-lg shadow-md w-full mx-auto">
    <?php if (!empty($transacoes)): ?>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data Despesa</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Solicitante</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descrição / Projeto</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Categoria Financeira</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Valor</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($transacoes as $t): ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo date('d/m/Y', strtotime($t['vencimento'])); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                <?php echo htmlspecialchars($t['nome_usuario'] ?? 'N/A'); ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                <div class="font-medium text-gray-900">
                                    <?php echo htmlspecialchars(str_replace('Prestação de Contas: ', '', $t['descricao'])); ?>
                                </div>
                                <?php if (preg_match('/Projeto ID: (\d+)/', $t['observacoes'], $matches)): ?>
                                    <div class="text-xs text-blue-600 mt-1">Projeto #<?php echo $matches[1]; ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo htmlspecialchars($t['nome_classificacao'] ?? '-'); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-bold text-gray-700">
                                R$ <?php echo number_format($t['valor'], 2, ',', '.'); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <?php
                                $statusClass = $t['status'] === 'Pago' ? 'bg-emerald-100 text-emerald-800' : 'bg-yellow-100 text-yellow-800';
                                ?>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $statusClass; ?>">
                                    <?php echo htmlspecialchars($t['status']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium space-x-2">
                                <a href="<?php echo BASE_URL; ?>/financeiro/detalhe/<?php echo $t['id']; ?>" class="text-indigo-600 hover:text-indigo-900">Detalhes</a>
                                <?php if (!empty($t['documentoVinculado'])): ?>
                                    <span class="text-gray-300">|</span>
                                    <a href="<?php echo BASE_URL; ?>/storage/comprovantes_prestacao/<?php echo $t['documentoVinculado']; ?>" target="_blank" class="text-blue-600 hover:text-blue-900" title="Ver Comprovante">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                        </svg>
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Paginação -->
        <?php if (isset($totalPaginas) && $totalPaginas > 1): ?>
            <div class="mt-4 flex justify-center pb-2">
                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                    <?php
                    $queryParams = $_GET;
                    unset($queryParams['page']);
                    $queryString = http_build_query($queryParams);
                    $baseUrl = '?' . ($queryString ? $queryString . '&' : '');
                    ?>
                    <?php if ($paginaAtual > 1): ?>
                        <a href=" class="sr-only">Anterior</span>
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                        </a>
                    <?php endif; ?>

                    <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">
                        Página <?php echo $paginaAtual; ?> de <?php echo $totalPaginas; ?>
                    </span>

                    <?php if ($paginaAtual < $totalPaginas): ?>
                        <a href="<?php echo $baseUrl; ?>page=<
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                            </svg>
                        </a>
                    <?php endif; ?>
                </nav>
            </div>
        <?php endif; ?>

    <?php else: ?>
        <p class="text-gray-500 text-center py-8">Nenhuma prestação de contas aprovada encontrada no histórico.</p>
    <?php endif; ?>
</div>
