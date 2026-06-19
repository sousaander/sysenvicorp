<?php
$isPagar = ($tipo === 'P');
$isPendente = (strpos($pageTitle, 'Contas a Pagar') !== false || strpos($pageTitle, 'Contas a Receber') !== false);
$isPago = (strpos($pageTitle, 'Contas Pagas') !== false || strpos($pageTitle, 'Contas Recebidas') !== false);
$enableBulkActions = $isPendente || $isPago;

$corPrincipal = $isPagar ? 'red' : 'green';
$textoBotao = $isPagar ? '+ Nova Despesa' : '+ Nova Receita';
$urlNovo = BASE_URL . '/financeiro/novo?tipo=' . $tipo;
?>

<div class="flex justify-between items-center mb-6">
    <div>
        <h2 class="text-2xl font-bold"><?php echo htmlspecialchars($pageTitle ?? ''); ?></h2>
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

<div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm mb-6 border border-gray-200 dark:border-gray-700">
    <form method="GET" class="space-y-4">
        <?php if (!empty($filtros['status'])): ?>
            <input type="hidden" name="status" value="<?= htmlspecialchars($filtros['status']) ?>">
        <?php endif; ?>

        <!-- Linha 1: Campo de Pesquisa -->
        <div>
            <label for="descricao_filtro" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Pesquisar por texto ou nome</label>
            <input type="text"
                id="descricao_filtro"
                name="descricao_filtro"
                value="<?= htmlspecialchars($filtros['descricao'] ?? '') ?>"
                placeholder="Digite o nome ou descrição..."
                class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:border-sky-500 focus:ring-sky-500 p-2.5 transition-colors duration-200">
        </div>

        <!-- Linha 2: Filtros de Data e Valor -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label for="data_filtro" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    <?php echo $isPago ? 'Pago em' : 'Data Vencimento'; ?>
                </label>
                <input type="date"
                    id="data_filtro"
                    name="data_filtro"
                    value="<?= htmlspecialchars($filtros['data'] ?? '') ?>"
                    class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:border-sky-500 focus:ring-sky-500 p-2.5 transition-colors duration-200">
            </div>
            <div>
                <label for="mes_filtro" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Mês de Referência</label>
                <input type="month"
                    id="mes_filtro"
                    name="mes_filtro"
                    value="<?= htmlspecialchars($filtros['mes'] ?? '') ?>"
                    class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:border-sky-500 focus:ring-sky-500 p-2.5 transition-colors duration-200">
            </div>
            <div>
                <label for="valor_filtro" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Valor (R$)</label>
                <input type="text"
                    id="valor_filtro"
                    name="valor_filtro"
                    value="<?= htmlspecialchars(is_numeric($filtros['valor'] ?? null) ? number_format((float)$filtros['valor'], 2, ',', '.') : '') ?>"
                    placeholder="0,00"
                    class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:border-sky-500 focus:ring-sky-500 p-2.5 transition-colors duration-200 money-mask">
            </div>
        </div>

        <!-- Linha 3: Botões de Ação -->
        <div class="flex flex-col sm:flex-row gap-3 pt-2">
            <button type="submit" class="flex-1 sm:flex-initial bg-sky-600 text-white px-6 py-2.5 rounded-lg hover:bg-sky-700 transition-colors duration-200 font-medium shadow-sm">
                <svg class="w-4 h-4 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                Filtrar
            </button>
            <a href="<?= BASE_URL . '/financeiro/' . ($isPagar ? 'pagar' : 'receber') . (!empty($filtros['status']) ? '?status=' . $filtros['status'] : '') ?>"
                class="flex-1 sm:flex-initial px-6 py-2.5 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors duration-200 font-medium text-center shadow-sm">
                Limpar Filtros
            </a>
        </div>
    </form>
</div>

<form id="form-acoes-massa" method="POST">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <?php if ($enableBulkActions): ?>
            <div id="bulk-actions-toolbar" class="px-6 py-3 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700 hidden flex gap-2">
                <?php if ($isPendente): ?>
                    <button type="submit" formaction="<?= BASE_URL; ?>/financeiro/liquidarEmMassa" class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg shadow-sm hover:bg-green-700 transition focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500" onclick="return confirm('Confirma a liquidação das transações selecionadas?');">
                        <?= $isPagar ? 'Pagar Selecionados' : 'Receber Selecionados'; ?>
                    </button>
                <?php endif; ?>
                <?php if ($isPago): ?>
                    <button type="submit" formaction="<?= BASE_URL; ?>/financeiro/excluirEmMassa" class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg shadow-sm hover:bg-red-700 transition focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500" onclick="return confirm('Tem certeza que deseja excluir as transações selecionadas? Esta ação não pode ser desfeita.');">
                        Excluir Selecionados
                    </button>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        <?php if ($enableBulkActions): ?>
                            <th class="px-4 py-4 text-left">
                                <input type="checkbox" id="selecionar-todos" class="h-4 w-4 text-sky-600 border-gray-300 dark:border-gray-600 rounded focus:ring-sky-500">
                            </th>
                        <?php endif; ?>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Descrição</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider"><?= $isPagar ? 'Fornecedor' : 'Cliente' ?></th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Categoria</th>
                        <?php if ($isPagar): // Mostra a coluna apenas se for Contas a Pagar 
                        ?>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Centro de Custo</th>
                        <?php endif; ?>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider"><?php echo $isPago ? 'Pago em' : 'Vencimento'; ?></th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Valor</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Ações</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    <?php if (!empty($transacoes)): ?>
                        <?php foreach ($transacoes as $t): ?>
                            <?php
                            $isOverdue = ($t['status'] === 'Pendente' && $t['vencimento'] < date('Y-m-d')) || $t['status'] === 'Atrasado';
                            $missingEntity = ($isPagar && empty($t['fornecedor_id'])) || (!$isPagar && empty($t['cliente_id']));
                            
                            $rowClass = 'hover:bg-gray-50 dark:hover:bg-gray-700/50';
                            if ($isOverdue) $rowClass = 'bg-red-50 hover:bg-red-100';
                            if ($missingEntity) $rowClass .= ' border-l-4 border-l-amber-500 bg-amber-50/30 dark:bg-amber-900/10';
                            ?>
                            <tr class="<?= $rowClass ?> transition-colors">
                                <?php if ($enableBulkActions): ?>
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <input type="checkbox" name="transacao_ids[]" value="<?= $t['id']; ?>" class="checkbox-item h-4 w-4 text-sky-600 border-gray-300 dark:border-gray-600 rounded focus:ring-sky-500">
                                    </td>
                                <?php endif; ?>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                    <?php echo htmlspecialchars($t['descricao'] ?? ''); ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300 break-words min-w-[120px]">
                                    <?php 
                                    $nomeEntidade = $isPagar ? ($t['nome_fornecedor'] ?? '') : ($t['nome_cliente'] ?? '');
                                    if (!empty($nomeEntidade)): 
                                        echo htmlspecialchars($nomeEntidade ?? '');
                                    else: ?>
                                        <span class="inline-flex items-center text-amber-600 dark:text-amber-400 font-bold text-xs" title="Falta vincular o <?= $isPagar ? 'fornecedor' : 'cliente' ?>">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                                            Não Vinculado
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 border dark:border-gray-600">
                                        <?php echo htmlspecialchars($t['nome_classificacao'] ?? 'N/A'); ?>
                                    </span>
                                </td>
                                <?php if ($isPagar): // Mostra a célula apenas se for Contas a Pagar 
                                ?>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        <?php echo htmlspecialchars($t['nome_centro_custo'] ?? 'N/A'); ?>
                                    </td>
                                <?php endif; ?>
                                <td class="px-6 py-4 whitespace-nowrap text-sm <?= $isOverdue ? 'text-red-700 dark:text-red-400 font-semibold' : 'text-gray-500 dark:text-gray-400' ?>">
                                    <?php
                                    $dataExibicao = ($isPago && !empty($t['data_pagamento'])) ? $t['data_pagamento'] : $t['vencimento'];
                                    echo date('d/m/Y', strtotime($dataExibicao));
                                    ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium text-gray-900 dark:text-white">
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
                                        <?php echo htmlspecialchars($t['status'] ?? ''); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                    <a href="<?php echo BASE_URL; ?>/financeiro/editar/<?php echo $t['id']; ?>" class="text-gray-400 hover:text-sky-600 transition-colors" title="Editar">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z" />
                                            <path fill-rule="evenodd" d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" clip-rule="evenodd" />
                                        </svg>
                                    </a>
                                    <form action="<?php echo BASE_URL; ?>/financeiro/excluir/<?php echo $t['id']; ?>" method="POST" class="inline" onsubmit="return confirm('Tem certeza que deseja excluir esta transação? Esta ação não pode ser desfeita.');">
                                        <button type="submit" class="text-gray-400 hover:text-red-600 transition-colors" title="Excluir">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="<?php echo 7 + ($isPagar ? 1 : 0) + ($enableBulkActions ? 1 : 0); ?>" class="px-6 py-10 text-center text-gray-500 dark:text-gray-400">
                                Nenhuma transação encontrada.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
                <?php if (!empty($transacoes)): ?>
                    <tfoot class="bg-gray-50 dark:bg-gray-700/50 border-t-2 border-gray-300 dark:border-gray-600">
                        <tr>
                            <td colspan="<?= 4 + ($isPagar ? 1 : 0) + ($enableBulkActions ? 1 : 0); ?>" class="px-6 py-3 text-right text-sm font-semibold text-gray-600 uppercase tracking-wider">
                                Total da Página
                            </td>
                            <td class="px-6 py-3 text-right text-sm font-bold text-gray-800 dark:text-white">
                                R$ <?php echo number_format($totalPagina ?? 0, 2, ',', '.'); ?>
                            </td>
                            <td colspan="2" class="px-6 py-3"></td>
                        </tr>
                    </tfoot>
                <?php endif; ?>
            </table>
        </div>
    </div>
</form>

<!-- Controles de Paginação -->
<?php if (isset($totalPaginas) && $totalPaginas > 1) : ?>
    <div class="px-6 py-4 border-t border-gray-200 flex justify-center bg-gray-50/75">
        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
            <?php
            // Adiciona os parâmetros de filtro à URL de paginação (para futuras implementações de filtro)
            $queryParams = $_GET;
            unset($queryParams['page']); // Remove a página atual para não duplicar
            $queryString = http_build_query($queryParams);
            $baseUrl = BASE_URL . '/financeiro/' . ($isPagar ? 'pagar' : 'receber') . '?' . ($queryString ? $queryString . '&' : '');

            $maxLinks = 5; // Número máximo de botões de página visíveis
            $start = max(1, $paginaAtual - floor($maxLinks / 2));
            $end = min($totalPaginas, $start + $maxLinks - 1);
            $start = max(1, $end - $maxLinks + 1);
            ?>

            <!-- Botão Anterior -->
            <a href="<?php echo $paginaAtual > 1 ? $baseUrl . 'page=' . ($paginaAtual - 1) : '#'; ?>"
                class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 <?php echo $paginaAtual <= 1 ? 'cursor-not-allowed opacity-50' : ''; ?>">
                <span class="sr-only">Anterior</span>
                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
            </a>

            <?php
            for ($i = $start; $i <= $end; $i++) {
                $activeClass = ($i == $paginaAtual) ? 'z-10 bg-sky-50 border-sky-500 text-sky-600' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50';
                echo '<a href="' . $baseUrl . 'page=' . $i . '" class="' . $activeClass . ' relative inline-flex items-center px-4 py-2 border text-sm font-medium">' . $i . '</a>';
            }
            ?>

            <!-- Botão Próximo -->
            <a href="<?php echo $paginaAtual < $totalPaginas ? $baseUrl . 'page=' . ($paginaAtual + 1) : '#'; ?>"
                class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 <?php echo $paginaAtual >= $totalPaginas ? 'cursor-not-allowed opacity-50' : ''; ?>">
                <span class="sr-only">Próximo</span>
                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                </svg>
            </a>
        </nav>
    </div>
<?php endif; ?>

<?php if ($enableBulkActions): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('form-acoes-massa');
            const selectAllCheckbox = document.getElementById('selecionar-todos');
            const itemCheckboxes = document.querySelectorAll('.checkbox-item');
            const bulkActionsToolbar = document.getElementById('bulk-actions-toolbar');

            function toggleBulkActions() {
                const anyChecked = Array.from(itemCheckboxes).some(cb => cb.checked);
                if (anyChecked) {
                    bulkActionsToolbar.classList.remove('hidden');
                } else {
                    bulkActionsToolbar.classList.add('hidden');
                }
            }

            if (selectAllCheckbox) {
                selectAllCheckbox.addEventListener('change', function() {
                    itemCheckboxes.forEach(checkbox => {
                        checkbox.checked = this.checked;
                    });
                    toggleBulkActions();
                });
            }

            itemCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    if (!this.checked) {
                        selectAllCheckbox.checked = false;
                    } else {
                        const allChecked = Array.from(itemCheckboxes).every(cb => cb.checked);
                        if (allChecked) {
                            selectAllCheckbox.checked = true;
                        }
                    }
                    toggleBulkActions();
                });
            });
        });
    </script>
<?php endif; ?>