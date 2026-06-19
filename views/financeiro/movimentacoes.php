<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="text-2xl font-bold mb-0">Movimentações de Caixa</h2>
        <p class="text-gray-600">Visualize, filtre e gerencie todas as suas transações financeiras.</p>
    </div>
    <a href="<?= BASE_URL; ?>/financeiro" class="bg-gray-200 text-gray-800 px-4 py-2 rounded-md hover:bg-gray-300 font-medium flex items-center">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M12.293 16.293a1 1 0 010-1.414L15.586 11H4a1 1 0 110-2h11.586l-3.293-3.293a1 1 0 011.414-1.414l5 5a1 1 0 010 1.414l-5 5a1 1 0 01-1.414 0z" clip-rule="evenodd" />
        </svg>
        <span>Voltar</span>
    </a>
</div>

<?php
// Exibe mensagens flash (de sucesso ou erro) vindas da sessão
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (isset($_SESSION['flash_message'])):
    $message = $_SESSION['flash_message'];
    unset($_SESSION['flash_message']);
    $type_classes = $message['type'] === 'success'
        ? 'bg-green-100 border-green-400 text-green-700'
        : 'bg-red-100 border-red-400 text-red-700';
?>
    <div class="border px-4 py-3 rounded relative mb-4 <?= $type_classes ?>" role="alert">
        <span class="block sm:inline"><?= htmlspecialchars($message['message']) ?></span>
    </div>
<?php endif; ?>

<div class="bg-white p-6 rounded-lg shadow-md">
    <!-- Filtros -->
    <form method="GET" action="<?= BASE_URL; ?>/financeiro/movimentacoes" class="mb-6 border-b pb-6">
        <?php if (!empty($filtros['ordem'])): ?>
            <input type="hidden" name="ordem" value="<?= htmlspecialchars($filtros['ordem']) ?>">
            <input type="hidden" name="direcao" value="<?= htmlspecialchars($filtros['direcao']) ?>">
        <?php endif; ?>
        <div class="grid grid-cols-1 md:grid-cols-6 gap-4 items-end">
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" id="status" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-sky-500 focus:border-sky-500 sm:text-sm p-2">
                    <option value="">Todos</option>
                    <option value="Pendente" <?= ($filtros['status'] ?? '') === 'Pendente' ? 'selected' : '' ?>>Pendente</option>
                    <option value="Pago" <?= ($filtros['status'] ?? '') === 'Pago' ? 'selected' : '' ?>>Pago</option>
                    <option value="Atrasado" <?= ($filtros['status'] ?? '') === 'Atrasado' ? 'selected' : '' ?>>Atrasado</option>
                </select>
            </div>
            <div>
                <label for="tipo" class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
                <select name="tipo" id="tipo" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-sky-500 focus:border-sky-500 sm:text-sm p-2">
                    <option value="">Todos</option>
                    <option value="R" <?= ($filtros['tipo'] ?? '') === 'R' ? 'selected' : '' ?>>Receita</option>
                    <option value="P" <?= ($filtros['tipo'] ?? '') === 'P' ? 'selected' : '' ?>>Despesa</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="bg-sky-600 text-white px-3 py-1 rounded-md hover:bg-sky-700 font-medium">Filtrar</button>
                <a href="<?= BASE_URL; ?>/financeiro/movimentacoes" class="bg-gray-200 text-gray-700 px-2 py-1 rounded-md hover:bg-gray-300 font-medium flex items-center justify-center">Limpar</a>
            </div>
        </div>
    </form>

    <div class="flex justify-between items-center mb-4 border-b pb-2">
        <div class="flex items-center space-x-4">
            <h3 class="text-lg font-semibold">Todas as Movimentações</h3>
        </div>
        <!-- Ações Rápidas -->
        <div class="flex items-center space-x-2">
            <a href="<?= BASE_URL; ?>/financeiro/novo?tipo=R" class="flex items-center justify-center text-sm font-medium text-emerald-600 hover:text-emerald-800 px-3 py-1 bg-emerald-100 hover:bg-emerald-200 rounded-md shadow-sm transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd" />
                </svg>
                <span>Receita</span>
            </a>
            <a href="<?= BASE_URL; ?>/financeiro/novo?tipo=P" class="flex items-center justify-center text-sm font-medium text-red-600 hover:text-red-800 px-3 py-1 bg-red-100 hover:bg-red-200 rounded-md shadow-sm transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM7 9a1 1 0 000 2h6a1 1 0 100-2H7z" clip-rule="evenodd" />
                </svg>
                <span>Despesa</span>
            </a>
            <button id="openTransferenciaModalBtn" class="flex items-center justify-center text-sm font-medium text-sky-600 hover:text-sky-800 px-3 py-1 bg-sky-100 hover:bg-sky-200 rounded-md shadow-sm transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
                <span>Transferência</span>
            </button>
            <a href="<?= BASE_URL; ?>/financeiro/baixarModelo" class="flex items-center justify-center text-sm font-medium text-gray-600 hover:text-gray-800 px-3 py-1 bg-gray-100 hover:bg-gray-200 rounded-md shadow-sm transition-colors" title="Baixar Modelo CSV">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
                <span>Modelo</span>
            </a>
            <button id="openImportacaoModalBtn" class="flex items-center justify-center text-sm font-medium text-orange-600 hover:text-orange-800 px-3 py-1 bg-orange-100 hover:bg-orange-200 rounded-md shadow-sm transition-colors" title="Importar CSV">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM6.293 6.707a1 1 0 010-1.414l3-3a1 1 0 011.414 0l3 3a1 1 0 01-1.414 1.414L11 5.414V13a1 1 0 11-2 0V5.414L7.707 6.707a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                </svg>
                <span>Importar</span>
            </button>
            <a href="<?= BASE_URL; ?>/financeiro/relatorio" class="text-sm text-indigo-600 hover:text-indigo-800 px-3 py-1 bg-indigo-100 rounded-md">
                Gerar Relatório
            </a>
            <a href="<?= BASE_URL; ?>/financeiro/relatorioCombustivel" class="text-sm text-amber-600 hover:text-amber-800 px-3 py-1 bg-amber-100 rounded-md" title="Relatório de Combustível">
                Rel. Combustível
            </a>
        </div>
    </div>

    <form id="form-acoes-massa" method="POST" action="<?= BASE_URL; ?>/financeiro/excluirEmMassa">
        <div id="bulk-actions-toolbar" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded relative mb-4 hidden flex justify-between items-center">
            <span id="selected-count" class="font-medium">0 selecionados</span>
            <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 font-medium text-sm transition-colors" onclick="return confirm('Tem certeza que deseja excluir as transações selecionadas? Esta ação não pode ser desfeita.');">
                Excluir Selecionados
            </button>
        </div>

        <?php if (!empty($fluxoCaixa)): ?>
            <!-- Tabela com dados do modelo -->
            <div class="overflow-x-auto">
                <table class="w-full table-auto divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-center w-10">
                                <input type="checkbox" id="select-all" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            </th>
                            <th class="px-6 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Conta</th>
                            <th class="px-6 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descrição</th>
                            <th class="px-6 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer group">
                                <?php
                                $ordemAtual = $filtros['ordem'] ?? '';
                                $direcaoAtual = $filtros['direcao'] ?? 'DESC';
                                $novaDirecao = ($ordemAtual === 'data' && $direcaoAtual === 'DESC') ? 'ASC' : 'DESC';

                                // Reconstrói a URL mantendo os outros filtros
                                $params = array_filter($filtros); // Remove filtros vazios
                                unset($params['page']); // Reseta a paginação ao ordenar
                                $params['ordem'] = 'data';
                                $params['direcao'] = $novaDirecao;
                                $urlOrdenacao = BASE_URL . '/financeiro/movimentacoes?' . http_build_query($params);
                                ?>
                                <a href="<?= $urlOrdenacao; ?>" class="flex items-center justify-center hover:text-gray-700">
                                    Vencimento
                                    <?php if ($ordemAtual === 'data'): ?>
                                        <span class="ml-1"><?= $direcaoAtual === 'ASC' ? '↑' : '↓'; ?></span>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th class="px-6 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer group">
                                <?php
                                // Lógica de ordenação para a coluna "Pago Em"
                                $ordemAtualPagoEm = $filtros['ordem'] ?? '';
                                $direcaoAtualPagoEm = $filtros['direcao'] ?? 'DESC';
                                // Se a ordenação atual for por 'pago_em' e DESC, a próxima será ASC. Caso contrário, será DESC.
                                $novaDirecaoPagoEm = ($ordemAtualPagoEm === 'pago_em' && $direcaoAtualPagoEm === 'DESC') ? 'ASC' : 'DESC';

                                $paramsPagoEm = array_filter($filtros);
                                unset($paramsPagoEm['page']);
                                $paramsPagoEm['ordem'] = 'pago_em';
                                $paramsPagoEm['direcao'] = $novaDirecaoPagoEm;
                                $urlOrdenacaoPagoEm = BASE_URL . '/financeiro/movimentacoes?' . http_build_query($paramsPagoEm);
                                ?>
                                <a href="<?= $urlOrdenacaoPagoEm; ?>" class="flex items-center justify-center hover:text-gray-700">
                                    Pago Em
                                    <span class="ml-1"><?= ($ordemAtualPagoEm === 'pago_em') ? ($direcaoAtualPagoEm === 'ASC' ? '↑' : '↓') : ''; ?></span>
                                </a>
                            </th>
                            <th class="px-6 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                            <th class="px-6 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Valor (R$)</th>
                            <th class="px-6 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($fluxoCaixa as $transacao): ?>
                            <?php
                            // Lógica para determinar o tipo e a cor da transação
                            $transferType = get_transfer_type($transacao);
                            $valorSign = '';
                            $tipoLabel = get_tipo_transacao_texto($transacao['tipo']);
                            $tipoClass = get_tipo_transacao_classes($transacao['tipo']);

                            if ($transferType === 'out') {
                                $valorSign = '-';
                                $tipoLabel = 'Transferência (Saída)';
                                $tipoClass = 'bg-sky-100 text-sky-800';
                            } elseif ($transferType === 'in') {
                                $valorSign = '';
                                $tipoLabel = 'Transferência (Entrada)';
                                $tipoClass = 'bg-sky-100 text-sky-800';
                            } else {
                                // Não é transferência, usa a lógica padrão
                                $valorSign = ($transacao['tipo'] === 'P') ? '-' : '';
                            }
                            ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 whitespace-nowrap text-center">
                                    <input type="checkbox" name="transacao_ids[]" value="<?= $transacao['id']; ?>" class="checkbox-item rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                </td>
                                <td class="px-6 py-2 whitespace-nowrap text-sm text-gray-500 text-center"><?= htmlspecialchars($transacao['banco_nome'] ?? 'N/A'); ?></td>

                                <td class="px-6 py-2 whitespace-nowrap text-sm font-medium text-gray-900 text-left">
                                    <a href="<?= htmlspecialchars(BASE_URL . '/financeiro/detalhe/' . $transacao['id']); ?>" class="hover:underline"><?= htmlspecialchars($transacao['descricao']); ?></a>
                                </td>

                                <td class="px-6 py-2 whitespace-nowrap text-sm text-gray-500 text-center"><?= !empty($transacao['vencimento']) ? date('d/m/Y', strtotime($transacao['vencimento'])) : '-'; ?></td>
                                <td class=" px-6 py-2 whitespace-nowrap text-sm text-gray-500 text-center"><?= !empty($transacao['data_pagamento']) ? date('d/m/Y', strtotime($transacao['data_pagamento'])) : '-'; ?></td>

                                <td class="px-6 py-2 whitespace-nowrap text-center">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $tipoClass; ?>">
                                        <?= htmlspecialchars($tipoLabel); ?>
                                    </span>
                                </td>

                                <td class="px-6 py-2 whitespace-nowrap text-sm text-center font-medium <?= ($valorSign === '-') ? 'text-red-600' : 'text-green-600'; ?>">
                                    <?= $valorSign . 'R$ ' . number_format($transacao['valor'], 2, ',', '.'); ?>
                                </td>
                                <td class="px-6 py-2 whitespace-nowrap text-center text-sm font-medium">
                                    <a href="<?= BASE_URL; ?>/financeiro/editar/<?= $transacao['id']; ?>" class="text-indigo-600 hover:text-indigo-900 mr-3" title="Editar">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z" />
                                            <path fill-rule="evenodd" d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" clip-rule="evenodd" />
                                        </svg>
                                    </a>
                                    <?php if (!empty($transacao['transfer_partner_id'])): ?>
                                        <a href="<?= BASE_URL; ?>/financeiro/detalhe/<?= $transacao['transfer_partner_id']; ?>" class="text-sky-600 hover:text-sky-900 mr-3" title="Ver transação relacionada">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block" viewBox="0 0 20 20" fill="currentColor">
                                                <path d="M3.172 7l4.95-4.95a1 1 0 111.415 1.414L6.586 8.414H13a5 5 0 010 10H9a1 1 0 110-2h4a3 3 0 000-6H6.586l3.95 3.95a1 1 0 11-1.415 1.414L3.172 7z" />
                                            </svg>
                                        </a>
                                    <?php endif; ?>
                                    <a href="<?= BASE_URL; ?>/financeiro/bloquear/<?= $transacao['id']; ?>" class="text-orange-600 hover:text-orange-900" onclick="return confirm('Tem certeza que deseja bloquear esta transação? Ela não será mais contabilizada nos saldos.');" title="Bloquear / Cancelar">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M13.477 14.89A6 6 0 015.11 6.524l8.367 8.368zm1.414-1.414L6.524 5.11a6 6 0 018.367 8.367zM18 10a8 8 0 11-16 0 8 8 0 0116 0z" clip-rule="evenodd" />
                                        </svg>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
    </form>
    <!-- Controles de Paginação -->
    <?php if (isset($totalPaginas) && $totalPaginas > 1) : ?>
        <div class="mt-4 flex justify-center">
            <?php
                // Adiciona os parâmetros de filtro à URL de paginação
                $queryParams = $_GET;
                unset($queryParams['page']); // Remove a página atual para não duplicar
                $queryString = http_build_query($queryParams);
                $baseUrl = BASE_URL . '/financeiro/movimentacoes?' . ($queryString ? $queryString . '&' : '');

                $maxLinks = 5;
                $start = max(1, $paginaAtual - floor($maxLinks / 2));
                $end = min($totalPaginas, $start + $maxLinks - 1);
                $start = max(1, $end - $maxLinks + 1);
            ?>
            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                <a href="<?= $paginaAtual > 1 ? $baseUrl . 'page=' . ($paginaAtual - 1) : '#'; ?>" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 <?= $paginaAtual <= 1 ? 'cursor-not-allowed opacity-50' : ''; ?>">
                    <span class="sr-only">Anterior</span>
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                </a>
                <?php for ($i = $start; $i <= $end; $i++) : ?>
                    <a href="<?= $baseUrl . 'page=' . $i; ?>" class="<?= ($i == $paginaAtual) ? 'z-10 bg-sky-50 border-sky-500 text-sky-600' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'; ?> relative inline-flex items-center px-4 py-2 border text-sm font-medium">
                        <?= $i; ?>
                    </a>
                <?php endfor; ?>
                <a href="<?= $paginaAtual < $totalPaginas ? $baseUrl . 'page=' . ($paginaAtual + 1) : '#'; ?>" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 <?= $paginaAtual >= $totalPaginas ? 'cursor-not-allowed opacity-50' : ''; ?>">
                    <span class="sr-only">Próximo</span>
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                    </svg>
                </a>
            </nav>
        </div>
    <?php endif; ?>
<?php else: ?>
    <p class="text-gray-500">Nenhuma transação encontrada.</p>
<?php endif; ?>
</div>

<!-- Modais de Transferência e Relatório (copiados de index.php para esta página) -->

<!-- Modal de Transferência entre Contas -->
<div id="transferenciaModal" class="fixed z-50 inset-0 overflow-y-auto hidden" aria-labelledby="modal-title-transfer" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:p-0">
        <!-- Background overlay -->
        <div id="transferenciaModalBg" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

        <div class="relative bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-lg sm:w-full" role="document">
            <form id="transferenciaForm" action="<?= BASE_URL; ?>/financeiro/realizarTransferencia" method="POST">
                <!-- Modal Header -->
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 border-b border-gray-200">
                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title-transfer">
                        Transferência entre Contas
                    </h3>
                </div>

                <!-- Modal Body -->
                <div class="px-4 sm:p-6">
                    <div class="grid grid-cols-1 gap-6">
                        <div>
                            <label for="conta_origem" class="block text-sm font-medium text-gray-700">De</label>
                            <select id="conta_origem" name="conta_origem" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-sky-500 focus:border-sky-500 sm:text-sm p-2">
                                <option value="">Selecione a conta de origem</option>
                                <?php if (!empty($saldosBancos)): ?>
                                    <?php foreach ($saldosBancos as $banco): ?>
                                        <option value="<?= $banco['id']; ?>"><?= htmlspecialchars($banco['nome']); ?> (R$ <?= number_format($banco['saldo_atual'], 2, ',', '.'); ?>)</option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div>
                            <label for="conta_destino" class="block text-sm font-medium text-gray-700">Para</label>
                            <select id="conta_destino" name="conta_destino" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-sky-500 focus:border-sky-500 sm:text-sm p-2">
                                <option value="">Selecione a conta de destino</option>
                                <?php if (!empty($saldosBancos)): ?>
                                    <?php foreach ($saldosBancos as $banco): ?>
                                        <option value="<?= $banco['id']; ?>"><?= htmlspecialchars($banco['nome']); ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div>
                            <label for="valor_transferencia" class="block text-sm font-medium text-gray-700">Valor</label>
                            <input type="text" name="valor" id="valor_transferencia" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-sky-500 focus:border-sky-500 sm:text-sm p-2" placeholder="0,00">
                        </div>
                        <div>
                            <label for="data_transferencia" class="block text-sm font-medium text-gray-700">Data da Transferência</label>
                            <input type="date" name="data_transferencia" id="data_transferencia" value="<?= date('Y-m-d'); ?>" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-sky-500 focus:border-sky-500 sm:text-sm p-2">
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-sky-600 text-base font-medium text-white hover:bg-sky-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                        Confirmar Transferência
                    </button>
                    <button type="button" id="fecharTransferenciaModal" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de Importação CSV -->
<div id="importacaoModal" class="fixed z-50 inset-0 overflow-y-auto hidden" aria-labelledby="modal-title-import" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:p-0">
        <div id="importacaoModalBg" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <div class="relative bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-lg sm:w-full" role="document">
            <form id="importacaoForm" action="<?= BASE_URL; ?>/financeiro/processarImportacao" method="POST" enctype="multipart/form-data">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 border-b border-gray-200">
                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title-import">
                        Importar Movimentações (CSV)
                    </h3>
                </div>
                <div class="px-4 sm:p-6">
                    <div class="mb-4">
                        <p class="text-sm text-gray-600 mb-2">Selecione o arquivo CSV. Deixe a coluna 'ID' em branco para inserir novos registros, ou preencha-a para atualizar um registro existente.</p>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Arquivo CSV</label>
                        <div class="mt-1 flex items-center">
                            <label for="arquivo_csv" class="cursor-pointer py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                                Escolher arquivo
                            </label>
                            <input id="arquivo_csv" name="arquivo_csv" type="file" accept=".csv" class="sr-only" required onchange="document.getElementById('file-name').textContent = this.files.length > 0 ? this.files[0].name : 'Nenhum arquivo selecionado'">
                            <span id="file-name" class="ml-3 text-sm text-gray-500">Nenhum arquivo selecionado</span>
                        </div>
                    </div>
                    <div class="text-xs text-gray-500">
                        <p><strong>Dica:</strong> Certifique-se de que os nomes de Bancos, Categorias e Centros de Custo correspondam exatamente aos cadastrados no sistema para que sejam vinculados corretamente.</p>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-orange-100 text-base font-medium text-orange-700 hover:bg-orange-200 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                        Importar
                    </button>
                    <button type="button" id="fecharImportacaoModal" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Removido o Modal de Geração de Relatório -->
<div id="relatorioModal" class="fixed z-50 inset-0 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:p-0">
        <!-- Background overlay -->
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

        <div class="relative bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-2xl sm:w-full" role="document">
            <form id="relatorioForm" action="<?= BASE_URL; ?>/financeiro/relatorio" method="GET">
                <!-- Modal Header -->
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 border-b border-gray-200">
                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                        Gerar Relatório Financeiro
                    </h3>
                </div>

                <!-- Modal Body (Scrollable) -->
                <div class="px-4 sm:p-6 max-h-[70vh] overflow-y-auto">
                    <!-- Tipo de Relatório -->
                    <div class="mb-4">
                        <label for="modal_filtro_tipo_relatorio" class="block text-sm font-medium text-gray-700">Tipo de Relatório</label>
                        <select id="modal_filtro_tipo_relatorio" name="tipo_relatorio" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-sky-500 focus:border-sky-500 sm:text-sm p-2">
                            <option value="geral" <?= (isset($filtros['tipo_relatorio']) && $filtros['tipo_relatorio'] == 'geral') ? 'selected' : ''; ?>>Extrato Geral</option>
                            <option value="banco" <?= (isset($filtros['tipo_relatorio']) && $filtros['tipo_relatorio'] == 'banco') ? 'selected' : ''; ?>>Por Conta Bancária</option>
                        </select>
                    </div>

                    <!-- Seleção de Banco (visível apenas se tipo_relatorio for 'banco') -->
                    <div id="modal_campo_banco" class="<?= (isset($filtros['tipo_relatorio']) && $filtros['tipo_relatorio'] == 'banco') ? '' : 'hidden'; ?> mb-4">
                        <label for="modal_filtro_banco_id" class="block text-sm font-medium text-gray-700">Conta Bancária</label>
                        <select id="modal_filtro_banco_id" name="banco_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-sky-500 focus:border-sky-500 sm:text-sm p-2">
                            <option value="">Todas as Contas</option>
                            <?php if (!empty($bancos)): ?>
                                <?php foreach ($bancos as $banco): ?>
                                    <option value="<?= $banco['id']; ?>" <?= (isset($filtros['banco_id']) && $filtros['banco_id'] == $banco['id']) ? 'selected' : ''; ?>>
                                        <?= htmlspecialchars($banco['nome']); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <!-- Visualizar por -->
                    <div class="mb-4">
                        <label for="modal_filtro_periodo" class="block text-sm font-medium text-gray-700">Período</label>
                        <select id="modal_filtro_periodo" name="periodo" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-sky-500 focus:border-sky-500 sm:text-sm p-2">
                            <option value="recente" <?= (!isset($filtros['periodo']) || $filtros['periodo'] == 'recente') ? 'selected' : ''; ?>>Mais Recentes</option>
                            <option value="dia" <?= (isset($filtros['periodo']) && $filtros['periodo'] == 'dia') ? 'selected' : ''; ?>>Dia Específico</option>
                            <option value="mes" <?= (isset($filtros['periodo']) && $filtros['periodo'] == 'mes') ? 'selected' : ''; ?>>Mês Específico</option>
                            <option value="intervalo" <?= (isset($filtros['periodo']) && $filtros['periodo'] == 'intervalo') ? 'selected' : ''; ?>>Intervalo de Datas</option>
                        </select>
                    </div>

                    <!-- Campos de Data (controlados por JS) -->
                    <div id="modal_campos_data" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div id="modal_campo_data_unica" class="hidden mb-4">
                            <label for="modal_data_unica" class="block text-sm font-medium text-gray-700">Data</label>
                            <input type="date" name="data_unica" id="modal_data_unica" value="<?= htmlspecialchars($filtros['data_unica'] ?? ''); ?>" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-sky-500 focus:border-sky-500 sm:text-sm p-2">
                        </div>
                        <div id="modal_campo_mes_ano" class="hidden mb-4">
                            <label for="modal_mes_ano" class="block text-sm font-medium text-gray-700">Mês/Ano</label>
                            <input type="month" name="mes_ano" id="modal_mes_ano" value="<?= htmlspecialchars($filtros['mes_ano'] ?? ''); ?>" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-sky-500 focus:border-sky-500 sm:text-sm p-2">
                        </div>
                        <div id="modal_campo_intervalo" class="hidden sm:col-span-2 grid grid-cols-2 gap-4 mb-4">
                            <div><label for="modal_data_inicio" class="block text-sm font-medium text-gray-700">De</label><input type="date" name="data_inicio" id="modal_data_inicio" value="<?= htmlspecialchars($filtros['data_inicio'] ?? ''); ?>" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-sky-500 focus:border-sky-500 sm:text-sm p-2"></div>
                            <div><label for="modal_data_fim" class="block text-sm font-medium text-gray-700">Até</label><input type="date" name="data_fim" id="modal_data_fim" value="<?= htmlspecialchars($filtros['data_fim'] ?? ''); ?>" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-sky-500 focus:border-sky-500 sm:text-sm p-2"></div>
                        </div>
                    </div>

                </div>

                <!-- Modal Footer -->
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" id="visualizarRelatorioBtn" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-sky-600 text-base font-medium text-white hover:bg-sky-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                        Visualizar Relatório
                    </button>
                    <button type="button" id="exportarPdfBtn" class="mt-3 w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Exportar PDF
                    </button>
                    <button type="button" id="fecharRelatorioModal" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // --- MODAL DE RELATÓRIO ---
        const openRelatorioModalBtn = document.getElementById('openRelatorioModalBtn');
        const relatorioModal = document.getElementById('relatorioModal');
        const fecharRelatorioModal = document.getElementById('fecharRelatorioModal');
        const relatorioModalBg = relatorioModal ? relatorioModal.querySelector('.fixed.inset-0.bg-gray-500') : null;

        const modalFiltroTipoRelatorio = document.getElementById('modal_filtro_tipo_relatorio');
        const modalCampoBanco = document.getElementById('modal_campo_banco');
        const modalFiltroPeriodo = document.getElementById('modal_filtro_periodo');
        const modalCampoDataUnica = document.getElementById('modal_campo_data_unica');
        const modalCampoMesAno = document.getElementById('modal_campo_mes_ano');
        const modalCampoIntervalo = document.getElementById('modal_campo_intervalo');

        const relatorioForm = document.getElementById('relatorioForm');
        const visualizarRelatorioBtn = document.getElementById('visualizarRelatorioBtn');
        const exportarPdfBtn = document.getElementById('exportarPdfBtn');

        // --- MODAL DE TRANSFERÊNCIA ---
        const openTransferenciaModalBtn = document.getElementById('openTransferenciaModalBtn');
        const transferenciaModal = document.getElementById('transferenciaModal');
        const fecharTransferenciaModal = document.getElementById('fecharTransferenciaModal');
        const transferenciaModalBg = document.getElementById('transferenciaModalBg');
        const contaOrigemSelect = document.getElementById('conta_origem');
        const contaDestinoSelect = document.getElementById('conta_destino');

        // --- MODAL DE IMPORTAÇÃO ---
        const openImportacaoModalBtn = document.getElementById('openImportacaoModalBtn');
        const importacaoModal = document.getElementById('importacaoModal');
        const fecharImportacaoModal = document.getElementById('fecharImportacaoModal');
        const importacaoModalBg = document.getElementById('importacaoModalBg');

        if (openImportacaoModalBtn) {
            openImportacaoModalBtn.addEventListener('click', () => {
                if (importacaoModal) importacaoModal.classList.remove('hidden');
            });
        }

        function closeImportacaoModal() {
            if (importacaoModal) importacaoModal.classList.add('hidden');
        }

        if (fecharImportacaoModal) fecharImportacaoModal.addEventListener('click', closeImportacaoModal);
        if (importacaoModalBg) importacaoModalBg.addEventListener('click', closeImportacaoModal);

        if (openTransferenciaModalBtn) {
            openTransferenciaModalBtn.addEventListener('click', () => {
                if (transferenciaModal) transferenciaModal.classList.remove('hidden');
            });
        }

        function closeTransferenciaModal() {
            if (transferenciaModal) transferenciaModal.classList.add('hidden');
        }

        if (fecharTransferenciaModal) fecharTransferenciaModal.addEventListener('click', closeTransferenciaModal);
        if (transferenciaModalBg) transferenciaModalBg.addEventListener('click', closeTransferenciaModal);

        function updateDestinoOptions() {
            if (!contaOrigemSelect || !contaDestinoSelect) return;
            const origemId = contaOrigemSelect.value;
            // Reseta as opções de destino
            for (let option of contaDestinoSelect.options) {
                option.disabled = false;
            }
            // Desabilita a opção de destino que é igual à origem
            if (origemId) {
                const destinoOption = contaDestinoSelect.querySelector(`option[value="${origemId}"]`);
                if (destinoOption) destinoOption.disabled = true;
            }
        }
        if (contaOrigemSelect) contaOrigemSelect.addEventListener('change', updateDestinoOptions);

        function closeModal() {
            if (relatorioModal) relatorioModal.classList.add('hidden');
        }
        if (fecharRelatorioModal) fecharRelatorioModal.addEventListener('click', closeModal);
        if (relatorioModalBg) relatorioModalBg.addEventListener('click', closeModal);

        // Fecha o modal com a tecla 'Escape'
        document.addEventListener('keydown', (e) => {
            if (e.key === "Escape") {
                if (relatorioModal && !relatorioModal.classList.contains('hidden')) closeModal();
                if (transferenciaModal && !transferenciaModal.classList.contains('hidden')) closeTransferenciaModal();
                if (importacaoModal && !importacaoModal.classList.contains('hidden')) closeImportacaoModal();
            }
        });

        function toggleModalBancoField() {
            if (modalFiltroTipoRelatorio && modalFiltroTipoRelatorio.value === 'banco') {
                if (modalCampoBanco) modalCampoBanco.classList.remove('hidden');
            } else {
                if (modalCampoBanco) modalCampoBanco.classList.add('hidden');
            }
        }
        if (modalFiltroTipoRelatorio) modalFiltroTipoRelatorio.addEventListener('change', toggleModalBancoField);

        function toggleModalDateFields() {
            const selectedPeriod = modalFiltroPeriodo ? modalFiltroPeriodo.value : null;

            // Hide all date-related fields first
            if (modalCampoDataUnica) modalCampoDataUnica.classList.add('hidden');
            if (modalCampoMesAno) modalCampoMesAno.classList.add('hidden');
            if (modalCampoIntervalo) modalCampoIntervalo.classList.add('hidden');

            // Then, show the relevant field based on the selected period
            if (selectedPeriod === 'dia') {
                if (modalCampoDataUnica) modalCampoDataUnica.classList.remove('hidden');
            } else if (selectedPeriod === 'mes') {
                if (modalCampoMesAno) modalCampoMesAno.classList.remove('hidden');

            } else if (selectedPeriod === 'intervalo') {
                if (modalCampoIntervalo) modalCampoIntervalo.classList.remove('hidden');
            }
        }
        if (modalFiltroPeriodo) modalFiltroPeriodo.addEventListener('change', toggleModalDateFields);

        // Lógica de Seleção em Massa
        const selectAll = document.getElementById('select-all');
        const checkboxes = document.querySelectorAll('.checkbox-item');
        const toolbar = document.getElementById('bulk-actions-toolbar');
        const selectedCountSpan = document.getElementById('selected-count');

        function updateToolbar() {
            const selected = document.querySelectorAll('.checkbox-item:checked');
            const count = selected.length;

            if (count > 0) {
                toolbar.classList.remove('hidden');
                selectedCountSpan.textContent = count + (count === 1 ? ' item selecionado' : ' itens selecionados');
            } else {
                toolbar.classList.add('hidden');
            }
        }

        if (selectAll) {
            selectAll.addEventListener('change', function() {
                checkboxes.forEach(cb => cb.checked = selectAll.checked);
                updateToolbar();
            });
        }

        checkboxes.forEach(cb => {
            cb.addEventListener('change', function() {
                updateToolbar();
                if (!this.checked) {
                    selectAll.checked = false;
                } else {
                    const allChecked = Array.from(checkboxes).every(c => c.checked);
                    if (allChecked) selectAll.checked = true;
                }
            });
        });
    });
</script>