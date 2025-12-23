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
            <button type="button" id="openRelatorioModalBtn" class="text-sm text-indigo-600 hover:text-indigo-800 px-3 py-1 bg-indigo-100 rounded-md">Gerar Relatório</button>
        </div>
    </div>
    <?php if (!empty($fluxoCaixa)): ?>
        <!-- Tabela com dados do modelo -->
        <div class="overflow-x-auto">
            <table class="w-full table-auto divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Conta</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descrição</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Pago Em</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Valor (R$)</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($fluxoCaixa as $transacao): ?>
                        <?php
                        // Lógica simplificada usando o helper
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
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center"><?= htmlspecialchars($transacao['banco_nome'] ?? 'N/A'); ?></td>

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
                                            <path d="M3.172 7l4.95-4.95a1 1 0 111.415 1.414L6.586 8.414H13a5 5 0 010 10H9a1 1 0 110-2h4a3 3 0 000-6H6.586l3.95 3.95a1 1 0 11-1.415 1.414L3.172 7z" />
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
        <!-- Controles de Paginação -->
        <?php if (isset($totalPaginas) && $totalPaginas > 1) : ?>
            <div class="mt-4 flex justify-center">
                <?php
                // Adiciona os parâmetros de filtro à URL de paginação
                $queryParams = $_GET;
                unset($queryParams['page']); // Remove a página atual para não duplicar
                $queryString = http_build_query($queryParams);
                ?>
                <nav class="inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                    <?php for ($i = 1; $i <= $totalPaginas; $i++) : ?>
                        <a href="<?= BASE_URL; ?>/financeiro/movimentacoes?<?= $queryString ? $queryString . '&' : '' ?>page=<?= $i; ?>" class="<?= ($i == $paginaAtual) ? 'z-10 bg-sky-50 border-sky-500 text-sky-600' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'; ?> relative inline-flex items-center px-4 py-2 border text-sm font-medium">
                            <?= $i; ?>
                        </a>
                    <?php endfor; ?>
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

<!-- Modal de Geração de Relatório -->
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

        if (visualizarRelatorioBtn) {
            visualizarRelatorioBtn.addEventListener('click', (e) => {
                e.preventDefault();
                if (relatorioForm) {
                    relatorioForm.action = '<?= BASE_URL; ?>/financeiro/relatorio';
                    relatorioForm.target = '_blank'; // Abre em nova aba
                    relatorioForm.submit();
                }
            });
        }

        if (exportarPdfBtn) {
            exportarPdfBtn.addEventListener('click', (e) => {
                e.preventDefault();
                if (relatorioForm) {
                    relatorioForm.action = '<?= BASE_URL; ?>/financeiro/exportarRelatorioPdf';
                    relatorioForm.target = '_self'; // Exporta na mesma aba
                    relatorioForm.submit();
                }
            });
        }
    });
</script>