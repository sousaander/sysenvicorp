<?php
$isPagar = ($tipo === 'P');
$corPrincipal = $isPagar ? 'red' : 'green';
$textoBotao = $isPagar ? '+ Nova Despesa' : '+ Nova Receita';
$urlNovo = BASE_URL . '/financeiro/novo?tipo=' . $tipo;
?>

<div class="flex justify-between items-center mb-6">
    <div>
        <h2 class="text-2xl font-bold"><?php echo htmlspecialchars($pageTitle); ?></h2>
        <p class="text-gray-600">Visualize e gerencie todas as suas <?php echo $isPagar ? 'despesas' : 'receitas'; ?>.</p>
    </div>
    <div class="flex items-center space-x-4">
        <a href="<?php echo BASE_URL; ?>/financeiro" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 transition">
            &larr; Voltar
        </a>
        <a href="<?php echo $urlNovo; ?>" class="px-4 py-2 text-sm font-medium text-white bg-<?php echo $corPrincipal; ?>-600 rounded-lg shadow-sm hover:bg-<?php echo $corPrincipal; ?>-700 transition focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-<?php echo $corPrincipal; ?>-500">
            <?php echo $textoBotao; ?>
        </a>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Descrição</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Categoria</th>
                    <?php if ($isPagar): // Mostra a coluna apenas se for Contas a Pagar 
                    ?>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Centro de Custo</th>
                    <?php endif; ?>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Vencimento</th>
                    <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Valor</th>
                    <th class="px-6 py-4 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Ações</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (!empty($transacoes)): ?>
                    <?php foreach ($transacoes as $t): ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                <?php echo htmlspecialchars($t['descricao']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    <?php echo htmlspecialchars($t['nome_classificacao'] ?? 'N/A'); ?>
                                </span>
                            </td>
                            <?php if ($isPagar): // Mostra a célula apenas se for Contas a Pagar 
                            ?>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo htmlspecialchars($t['nome_centro_custo'] ?? 'N/A'); ?>
                                </td>
                            <?php endif; ?>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo date('d/m/Y', strtotime($t['vencimento'])); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium text-gray-900">
                                R$ <?php echo number_format($t['valor'], 2, ',', '.'); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <?php
                                $statusClasses = [
                                    'Pendente' => 'bg-yellow-50 text-yellow-700 border border-yellow-200',
                                    'Pago' => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
                                    'Atrasado' => 'bg-red-50 text-red-700 border border-red-200',
                                    'Cancelado' => 'bg-gray-50 text-gray-600 border border-gray-200',
                                ];
                                $classe = $statusClasses[$t['status']] ?? 'bg-gray-50 text-gray-600 border border-gray-200';
                                ?>
                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-medium rounded-full <?php echo $classe; ?>">
                                    <?php echo htmlspecialchars($t['status']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                <a href="<?php echo BASE_URL; ?>/financeiro/editar/<?php echo $t['id']; ?>" class="text-gray-400 hover:text-sky-600 transition-colors" title="Editar">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z" />
                                        <path fill-rule="evenodd" d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" clip-rule="evenodd" />
                                    </svg>
                                </a>
                                <a href="<?php echo BASE_URL; ?>/financeiro/excluir/<?php echo $t['id']; ?>" class="text-gray-400 hover:text-red-600 transition-colors" onclick="return confirm('Tem certeza que deseja excluir esta transação? Esta ação não pode ser desfeita.');" title="Excluir">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="<?php echo $isPagar ? '7' : '6'; ?>" class="px-6 py-10 text-center text-gray-500">
                            Nenhuma transação encontrada.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Controles de Paginação -->
    <?php if (isset($totalPaginas) && $totalPaginas > 1) : ?>
        <div class="px-6 py-4 border-t border-gray-200 flex justify-center bg-gray-50">
            <nav class="inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                <?php for ($i = 1; $i <= $totalPaginas; $i++) : ?>
                    <a href="<?php echo BASE_URL; ?>/financeiro/<?php echo $isPagar ? 'pagar' : 'receber'; ?>?page=<?php echo $i; ?>"
                        class="<?php echo (isset($paginaAtual) && $i == $paginaAtual) ? 'z-10 bg-sky-50 border-sky-500 text-sky-600' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'; ?> relative inline-flex items-center px-4 py-2 border text-sm font-medium">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
            </nav>
        </div>
    <?php endif; ?>
</div>