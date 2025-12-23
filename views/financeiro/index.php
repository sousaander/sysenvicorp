<h2 class="text-2xl font-bold mb-4">Módulo Financeiro</h2>
<p class="mb-6 text-gray-600">Gerencie contas a pagar e a receber, acompanhe o fluxo de caixa e gere relatórios financeiros detalhados.</p>

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

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <!-- Card 1: Total Contas a Pagar -->
    <div class="bg-white p-6 rounded-lg shadow-lg border-l-4 border-red-500 flex items-start space-x-4">
        <div class="flex-shrink-0 h-12 w-12 flex items-center justify-center bg-red-100 rounded-lg">
            <svg class="h-6 w-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" />
            </svg>
        </div>
        <div>
            <h3 class="font-semibold text-gray-500">Contas a Pagar (Mês Atual)</h3>
            <p class="text-3xl font-bold text-red-600">R$ <?= number_format($contasPagarTotal ?? 0, 2, ',', '.'); ?></p>
            <p class="text-sm text-gray-400 mt-2">
                <?php if (!empty($proximoVencimento)): ?>
                    Próximo vencimento: <?= htmlspecialchars(date('d/m/Y', strtotime($proximoVencimento))); ?>
                <?php else: ?>
                    Nenhum próximo vencimento
                <?php endif; ?>
            </p>
        </div>
    </div>
    <!-- Card 2: Total Contas a Receber -->
    <div class="bg-white p-6 rounded-lg shadow-lg border-l-4 border-green-500 flex items-start space-x-4">
        <div class="flex-shrink-0 h-12 w-12 flex items-center justify-center bg-green-100 rounded-lg">
            <svg class="h-6 w-6 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
            </svg>
        </div>
        <div>
            <h3 class="font-semibold text-gray-500">Contas a Receber (Mês Atual)</h3>
            <p class="text-3xl font-bold text-green-600">R$ <?= number_format($contasReceberTotal ?? 0, 2, ',', '.'); ?></p>
            <?php if (isset($resumoAtrasadas) && $resumoAtrasadas['count'] > 0) : ?>
                <div class="mt-2 p-2 bg-red-100 border border-red-200 rounded-md">
                    <a href="<?= BASE_URL; ?>/financeiro/receber?status=Atrasado" class="block hover:bg-red-200 rounded-md p-1 transition-colors duration-200">
                        <p class="text-sm font-semibold text-red-800">
                            <i class="fas fa-exclamation-triangle mr-1"></i>
                            Atenção: <?= $resumoAtrasadas['count']; ?> conta(s) em atraso!
                        </p>
                        <p class="text-xs text-red-700">
                            Totalizando R$ <?= number_format($resumoAtrasadas['valor'], 2, ',', '.'); ?>. Clique para ver.
                        </p>
                    </a>
                </div>
            <?php else: ?>
                <p class="text-sm text-gray-400 mt-2">
                    <span class="text-emerald-600 font-medium">Em dia</span> (Nenhuma atrasada)
                </p>
            <?php endif; ?>
        </div>
    </div>
    <!-- Card 3: Saldo Atual -->
    <div class="bg-white p-6 rounded-lg shadow-lg border-l-4 border-blue-500 flex items-start space-x-4">
        <div class="flex-shrink-0 h-12 w-12 flex items-center justify-center bg-blue-100 rounded-lg">
            <svg class="h-6 w-6 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3" />
            </svg>
        </div>
        <div>
            <h3 class="font-semibold text-gray-500">Saldo Atual (Bancos)</h3>
            <p class="text-3xl font-bold text-blue-600">R$ <?= number_format($saldoAtual ?? 0, 2, ',', '.'); ?></p>
            <p class="text-sm text-gray-400 mt-2">
                <?php if (!empty($ultimaAtualizacaoSaldo)): ?>
                    Última atualização: <?= htmlspecialchars(date('d/m/Y', strtotime($ultimaAtualizacaoSaldo))); ?>
                <?php else: ?>
                    Nenhuma movimentação
                <?php endif; ?>
            </p>
        </div>
    </div>
</div>

<!-- Saldos dos Bancos -->
<div class="mb-6">
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-semibold text-gray-700">Saldos em Contas</h3>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <?php if (!empty($saldosBancos)): ?>
            <?php foreach ($saldosBancos as $banco): ?>
                <div class="bg-white p-4 rounded-lg shadow-md flex flex-col justify-between transition-all duration-300 hover:shadow-xl hover:scale-105">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-sm font-semibold text-gray-600"><?= htmlspecialchars($banco['nome']); ?></p>
                            <p class="text-xs text-gray-400"><?= htmlspecialchars($banco['tipo']); ?></p>
                        </div>
                        <img src="<?= get_bank_flag_url($banco['nome']); ?>" alt="Bandeira do <?= htmlspecialchars($banco['nome']); ?>" class="h-8 w-8 object-contain">
                    </div>
                    <div>
                        <p class="text-2xl font-bold mt-2 <?= $banco['saldo_atual'] >= 0 ? 'text-gray-800' : 'text-red-600'; ?>">
                            R$ <?= number_format($banco['saldo_atual'], 2, ',', '.'); ?>
                        </p>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-gray-500 col-span-full">Nenhuma conta bancária cadastrada.</p>
        <?php endif; ?>
    </div>
</div>

<!-- Seção de Gráficos -->
<div class="grid grid-cols-1 lg:grid-cols-5 gap-6 mb-6">
    <!-- Gráfico de Receitas vs Despesas -->
    <div class="lg:col-span-3 bg-white p-6 rounded-lg shadow-md text-center">
        <h3 class="text-lg font-semibold mb-4">Receitas vs. Despesas (Últimos 6 Meses)</h3>
        <canvas id="receitasDespesasChart" style="max-height:300px; max-width:100%;"></canvas>
    </div>
    <!-- Gráfico de Despesas por Categoria -->
    <div class="lg:col-span-2 bg-white p-6 rounded-lg shadow-md text-center">
        <h3 class="text-lg font-semibold mb-4">Despesas por Categoria (Mês Atual)</h3>
        <canvas id="despesasCategoriaChart" style="max-height:300px; max-width:100%;"></canvas>
    </div>
</div>
<div class="grid grid-cols-1 lg:grid-cols-1 gap-6 mb-6">
    <!-- Tabela de Fluxo de Caixa Recente -->
    <div class="lg:col-span-3 bg-white p-6 rounded-lg shadow-md">
        <div class="flex justify-between items-center mb-4 border-b pb-2">
            <div class="flex items-center space-x-4">
                <h3 class="text-lg font-semibold">Movimentações de Caixa Recentes</h3>
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
                <a href="<?= BASE_URL; ?>/financeiro/movimentacoes" class="text-sm font-medium text-indigo-600 hover:text-indigo-800 px-3 py-1 bg-indigo-100 hover:bg-indigo-200 rounded-md shadow-sm transition-colors">Ver Todas as Movimentações</a>
            </div>
        </div>
        <?php if (!empty($fluxoCaixa)): ?>
            <!-- Simulação de tabela com dados do modelo -->
            <div class="overflow-x-auto">
                <table class="w-full table-auto divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Conta</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descrição</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Pago Em</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Valor(R$)</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($fluxoCaixa as $transacao): ?>
                            <tr class="hover:bg-gray-50">
                                <?php
                                // Lógica simplificada usando o helper, para consistência com outras views
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

                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center"><?php echo htmlspecialchars($transacao['banco_nome'] ?? 'N/A'); ?></td>

                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-left">
                                    <a href="<?= htmlspecialchars(BASE_URL . '/financeiro/detalhe/' . $transacao['id']); ?>" class="hover:underline"><?= htmlspecialchars($transacao['descricao']); ?></a>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center"><?= date('d/m/Y', strtotime($transacao['data'])); ?></td>

                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $tipoClass; ?>">
                                        <?= htmlspecialchars($tipoLabel); ?>
                                    </span>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-sm text-center <?= ($valorSign === '-') ? 'text-red-600' : 'text-green-600'; ?>">
                                    <?= $valorSign . 'R$ ' . number_format($transacao['valor'], 2, ',', '.'); ?>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                    <a href="<?= BASE_URL; ?>/financeiro/editar/<?= $transacao['id']; ?>" class="text-indigo-600 hover:text-indigo-900 mr-3" title="Editar">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z" />
                                            <path fill-rule="evenodd" d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" clip-rule="evenodd" />
                                        </svg>
                                    </a>
                                    <?php if (!empty($transacao['transfer_partner_id'])): ?>
                                        <a href="<?= BASE_URL; ?>/financeiro/detalhe/<?= $transacao['transfer_partner_id']; ?>" class="text-sky-600 hover:text-sky-900 mr-3" title="Ver transação relacionada">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block" viewBox="0 0 20 20" fill="currentColor">
                                                <path d="M3.172 7l4.95-4.95a1 1 0 111.415 1.414L6.586 8.414H13 a5 5 0 010 10H9a1 1 0 110-2h4a3 3 0 000-6H6.586l3.95 3.95a1 1 0 11-1.415 1.414L3.172 7z" />
                                            </svg>
                                        </a>
                                    <?php endif; ?>
                                    <a href="<?= BASE_URL; ?>/financeiro/excluir/<?= $transacao['id']; ?>" class="text-red-600 hover:text-red-900" onclick="return confirm('Tem certeza que deseja excluir esta transação? Esta ação não pode ser desfeita.');" title="Excluir">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-gray-500">Nenhuma transação encontrada.</p>
        <?php endif; ?>
    </div>
</div>

<!-- Modal de Transferência entre Contas -->
<div id="transferenciaModal" class="fixed z-50 inset-0 overflow-y-auto hidden" aria-labelledby="modal-title-transfer" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Background overlay -->
        <div id="transferenciaModalBg" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

        <!-- This element is to trick the browser into centering the modal contents. -->
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full" role="document">
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
                                <?php foreach ($saldosBancos as $banco): ?>
                                    <option value="<?= $banco['id']; ?>"><?= htmlspecialchars($banco['nome']); ?> (R$ <?= number_format($banco['saldo_atual'], 2, ',', '.'); ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label for="conta_destino" class="block text-sm font-medium text-gray-700">Para</label>
                            <select id="conta_destino" name="conta_destino" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-sky-500 focus:border-sky-500 sm:text-sm p-2">
                                <option value="">Selecione a conta de destino</option>
                                <?php foreach ($saldosBancos as $banco): ?>
                                    <option value="<?= $banco['id']; ?>"><?= htmlspecialchars($banco['nome']); ?></option>
                                <?php endforeach; ?>
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

<!-- Modal de Geração de Relatório -->
<div id="relatorioModal" class="fixed z-50 inset-0 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Background overlay -->
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

        <!-- This element is to trick the browser into centering the modal contents. -->
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full" role="document">
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
                            <?php foreach ($bancos as $banco): ?>
                                <option value="<?= $banco['id']; ?>" <?= (isset($filtros['banco_id']) && $filtros['banco_id'] == $banco['id']) ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars($banco['nome']); ?>
                                </option>
                            <?php endforeach; ?>
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

        if (openRelatorioModalBtn) {
            openRelatorioModalBtn.addEventListener('click', () => {
                if (relatorioModal) {
                    relatorioModal.classList.remove('hidden');
                    toggleModalBancoField();
                    toggleModalDateFields();
                }
            });
        }

        function closeModal() {
            if (relatorioModal) relatorioModal.classList.add('hidden');
        }
        if (fecharRelatorioModal) fecharRelatorioModal.addEventListener('click', closeModal);
        if (relatorioModalBg) relatorioModalBg.addEventListener('click', closeModal);

        // Fecha o modal com a tecla 'Escape'
        document.addEventListener('keydown', (e) => {
            if (e.key === "Escape" && ((relatorioModal && !relatorioModal.classList.contains('hidden')) || (transferenciaModal && !transferenciaModal.classList.contains('hidden')))) {
                closeModal();
                closeTransferenciaModal();
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
            if (modalCampoDataUnica) modalCampoDataUnica.classList.add('hidden');
            if (modalCampoMesAno) modalCampoMesAno.classList.add('hidden');
            if (modalCampoIntervalo) modalCampoIntervalo.classList.add('hidden');

            if (selectedPeriod === 'dia') {
                if (modalCampoDataUnica) modalCampoDataUnica.classList.remove('hidden');
            } else if (selectedPeriod === 'mes') {
                if (modalCampoMesAno) modalCampoMesAno.classList.remove('hidden');
            } else if (selectedPeriod === 'intervalo') {
                if (modalCampoIntervalo) modalCampoIntervalo.classList.remove('hidden');
            }
        }
        if (modalFiltroPeriodo) modalFiltroPeriodo.addEventListener('change', toggleModalDateFields);

        if (visualizarRelatorioBtn) visualizarRelatorioBtn.addEventListener('click', (e) => {
            e.preventDefault();
            if (relatorioForm) {
                relatorioForm.action = '<?= BASE_URL; ?>/financeiro/relatorio';
                relatorioForm.target = '_blank'; // Abre em nova aba
                relatorioForm.submit();
            }
        });

        if (exportarPdfBtn) exportarPdfBtn.addEventListener('click', (e) => {
            e.preventDefault();
            if (relatorioForm) {
                relatorioForm.action = '<?= BASE_URL; ?>/financeiro/exportarRelatorioPdf';
                relatorioForm.target = '_self'; // Exporta na mesma aba
                relatorioForm.submit();
            }
        });

        // --- GRÁFICOS ---
        try {
            const monthlySummary = <?= $monthlySummaryJson ?? '[]'; ?>;
            const expenseSummary = <?= $expenseSummaryJson ?? '[]'; ?>;

            // Logs de depuração para console (úteis em dev)
            console.debug('monthlySummary:', monthlySummary);
            console.debug('expenseSummary:', expenseSummary);

            if (typeof Chart === 'undefined') {
                console.error('Chart.js não está carregado. Verifique se o script foi incluído na página.');
            } else {
                const receitasDespesasCtx = document.getElementById('receitasDespesasChart').getContext('2d');
                if (monthlySummary && monthlySummary.length > 0) {
                    const labels = monthlySummary.map(item => {
                        const [year, month] = item.mes.split('-');
                        return new Date(year, month - 1).toLocaleString('default', {
                            month: 'short',
                            year: '2-digit'
                        });
                    });
                    const receitasData = monthlySummary.map(item => item.receitas);
                    const despesasData = monthlySummary.map(item => item.despesas);

                    new Chart(receitasDespesasCtx, {
                        type: 'bar',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'Receitas',
                                data: receitasData,
                                backgroundColor: 'rgba(16, 185, 129, 0.6)',
                                borderColor: 'rgba(16, 185, 129, 1)',
                                borderWidth: 1
                            }, {
                                label: 'Despesas',
                                data: despesasData,
                                backgroundColor: 'rgba(239, 68, 68, 0.6)',
                                borderColor: 'rgba(239, 68, 68, 1)',
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        callback: function(value) {
                                            return 'R$ ' + value.toLocaleString('pt-BR');
                                        }
                                    }
                                }
                            },
                            plugins: {
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            let label = context.dataset.label || '';
                                            if (label) {
                                                label += ': ';
                                            }
                                            if (context.parsed.y !== null) {
                                                label += 'R$ ' + context.parsed.y.toLocaleString('pt-BR', {
                                                    minimumFractionDigits: 2
                                                });
                                            }
                                            return label;
                                        }
                                    }
                                }
                            }
                        }
                    });
                } else {
                    receitasDespesasCtx.font = "16px Arial";
                    receitasDespesasCtx.fillStyle = "#9ca3af";
                    receitasDespesasCtx.textAlign = "center";
                    receitasDespesasCtx.fillText("Sem dados para exibir no gráfico.", receitasDespesasCtx.canvas.width / 2, receitasDespesasCtx.canvas.height / 2);
                }

                const despesasCategoriaCtx = document.getElementById('despesasCategoriaChart').getContext('2d');
                if (expenseSummary && expenseSummary.length > 0) {
                    const labelsCategoria = expenseSummary.map(item => item.categoria);
                    const dataCategoria = expenseSummary.map(item => item.total);

                    new Chart(despesasCategoriaCtx, {
                        type: 'doughnut',
                        data: {
                            labels: labelsCategoria,
                            datasets: [{
                                label: 'Despesas',
                                data: dataCategoria,
                                backgroundColor: [
                                    'rgba(239, 68, 68, 0.7)', 'rgba(249, 115, 22, 0.7)', 'rgba(245, 158, 11, 0.7)',
                                    'rgba(132, 204, 22, 0.7)', 'rgba(34, 197, 94, 0.7)', 'rgba(16, 185, 129, 0.7)',
                                    'rgba(20, 184, 166, 0.7)', 'rgba(6, 182, 212, 0.7)', 'rgba(59, 130, 246, 0.7)'
                                ],
                                hoverOffset: 4
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                        }
                    });
                } else {
                    despesasCategoriaCtx.font = "16px Arial";
                    despesasCategoriaCtx.fillStyle = "#9ca3af";
                    despesasCategoriaCtx.textAlign = "center";
                    despesasCategoriaCtx.fillText("Sem despesas no mês atual.", despesasCategoriaCtx.canvas.width / 2, despesasCategoriaCtx.canvas.height / 2);
                }
            }
        } catch (e) {
            console.error('Erro ao inicializar gráficos:', e);
        }
    });
</script>