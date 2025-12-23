<div class="flex justify-between items-center mb-6">
    <div>
        <h2 class="text-2xl font-bold text-gray-800">Relatório de Movimentações</h2>
        <p class="text-gray-600">Visualize e filtre as transações financeiras.</p>
    </div>
    <a href="<?= BASE_URL; ?>/financeiro" class="bg-gray-200 text-gray-800 px-4 py-2 rounded-md hover:bg-gray-300 font-medium flex items-center">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
        </svg>
        Voltar ao Financeiro
    </a>
</div>

<!-- Formulário de Filtros -->
<div class="bg-white p-6 rounded-lg shadow-md mb-6">
    <form action="<?= BASE_URL; ?>/financeiro/relatorio" method="GET">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Filtro por Conta Bancária -->
            <div>
                <label for="filtro_banco_id" class="block text-sm font-medium text-gray-700">Conta Bancária</label>
                <select id="filtro_banco_id" name="banco_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-sky-500 focus:border-sky-500 sm:text-sm p-2">
                    <option value="">Todas as Contas</option>
                    <?php foreach ($bancos as $banco): ?>
                        <option value="<?= $banco['id']; ?>" <?= (isset($filtros['banco_id']) && $filtros['banco_id'] == $banco['id']) ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($banco['nome']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Filtro por Período -->
            <div>
                <label for="filtro_periodo" class="block text-sm font-medium text-gray-700">Período</label>
                <select id="filtro_periodo" name="periodo" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-sky-500 focus:border-sky-500 sm:text-sm p-2">
                    <option value="recente" <?= (!isset($filtros['periodo']) || $filtros['periodo'] == 'recente') ? 'selected' : ''; ?>>Mais Recentes</option>
                    <option value="dia" <?= (isset($filtros['periodo']) && $filtros['periodo'] == 'dia') ? 'selected' : ''; ?>>Dia Específico</option>
                    <option value="mes" <?= (isset($filtros['periodo']) && $filtros['periodo'] == 'mes') ? 'selected' : ''; ?>>Mês Específico</option>
                    <option value="intervalo" <?= (isset($filtros['periodo']) && $filtros['periodo'] == 'intervalo') ? 'selected' : ''; ?>>Intervalo de Datas</option>
                </select>
            </div>

            <!-- Filtro por Data de Início e Fim -->
            <div class="col-span-1 md:col-span-2 grid grid-cols-2 gap-4">
                <div>
                    <label for="data_inicio" class="block text-sm font-medium text-gray-700">De</label>
                    <input type="date" name="data_inicio" id="data_inicio" value="<?= htmlspecialchars($filtros['data_inicio'] ?? ''); ?>" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-sky-500 focus:border-sky-500 sm:text-sm p-2">
                </div>
                <div>
                    <label for="data_fim" class="block text-sm font-medium text-gray-700">Até</label>
                    <input type="date" name="data_fim" id="data_fim" value="<?= htmlspecialchars($filtros['data_fim'] ?? ''); ?>" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-sky-500 focus:border-sky-500 sm:text-sm p-2">
                </div>
            </div>
        </div>
        <div class="mt-4 flex justify-end gap-3">
            <a href="<?= BASE_URL; ?>/financeiro/relatorio" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300">Limpar Filtros</a>
            <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-sky-600 rounded-md shadow-sm hover:bg-sky-700">Filtrar</button>
            <button type="submit" formaction="<?= BASE_URL; ?>/financeiro/exportarRelatorioPdf" class="px-4 py-2 text-sm font-semibold text-white bg-red-600 rounded-md shadow-sm hover:bg-red-700">Exportar PDF</button>
        </div>
    </form>
</div>

<!-- Tabela de Resultados -->
<div class="bg-white rounded-lg shadow-md overflow-hidden">
    <?php if (!empty($transacoes)): ?>
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold">Resultados</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descrição</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Conta</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Categoria</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider w-40">Valor</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php
                    $totalReceitas = 0;
                    $totalDespesas = 0;
                    ?>
                    <?php foreach ($transacoes as $transacao): ?>
                        <?php
                        // Lógica simplificada usando o helper
                        $transferType = get_transfer_type($transacao);
                        $valorSign = '';

                        if ($transferType === 'out') {
                            $valorSign = '- ';
                            $totalDespesas += $transacao['valor'];
                        } elseif ($transferType === 'in') {
                            $valorSign = '+ ';
                            $totalReceitas += $transacao['valor'];
                        } elseif ($transacao['tipo'] === 'P') {
                            $valorSign = '- ';
                            $totalDespesas += $transacao['valor'];
                        } elseif ($transacao['tipo'] === 'R') {
                            $valorSign = '+ ';
                            $totalReceitas += $transacao['valor'];
                        }
                        ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= date('d/m/Y', strtotime($transacao['data'])); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= htmlspecialchars($transacao['descricao']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($transacao['nome_banco'] ?? 'N/A'); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($transacao['nome_classificacao'] ?? 'N/A'); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-mono <?= str_contains($valorSign, '-') ? 'text-red-600' : 'text-green-600'; ?>">
                                <?= $valorSign; ?>R$ <?= number_format($transacao['valor'], 2, ',', '.'); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <?php
                                $statusMap = [
                                    'Pago' => ['texto' => $transacao['tipo'] === 'R' ? 'Recebido' : 'Pago', 'classes' => 'bg-emerald-50 text-emerald-700 border border-emerald-200'],
                                    'Pendente' => ['texto' => 'Pendente', 'classes' => 'bg-yellow-50 text-yellow-700 border border-yellow-200'],
                                    'Atrasado' => ['texto' => 'Atrasado', 'classes' => 'bg-red-50 text-red-700 border border-red-200'],
                                    'Cancelado' => ['texto' => 'Cancelado', 'classes' => 'bg-gray-50 text-gray-600 border border-gray-200'],
                                ];
                                $config = $statusMap[$transacao['status']] ?? ['texto' => htmlspecialchars($transacao['status']), 'classes' => 'bg-gray-200 text-gray-800'];
                                ?>
                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-medium rounded-full <?= $config['classes']; ?>">
                                    <?= $config['texto']; ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="border-t-2 border-gray-200 bg-gray-50">
                    <tr>
                        <td colspan="4" class="px-6 py-3 text-right text-sm font-semibold text-gray-600">Total de Receitas</td>
                        <td class="px-6 py-3 text-right text-sm font-semibold text-green-600 font-mono">
                            R$ <?= number_format($totalReceitas, 2, ',', '.'); ?>
                        </td>
                        <td class="px-6 py-3"></td>
                    </tr>
                    <tr>
                        <td colspan="4" class="px-6 py-3 text-right text-sm font-semibold text-gray-600">Total de Despesas</td>
                        <td class="px-6 py-3 text-right text-sm font-semibold text-red-600 font-mono">
                            - R$ <?= number_format($totalDespesas, 2, ',', '.'); ?>
                        </td>
                        <td class="px-6 py-3"></td>
                    </tr>
                    <tr class="border-t border-gray-200">
                        <td colspan="4" class="px-6 py-4 text-right text-base font-bold text-gray-800">Saldo do Período</td>
                        <td class="px-6 py-4 text-right text-base font-bold <?= ($totalReceitas - $totalDespesas) >= 0 ? 'text-blue-600' : 'text-red-700'; ?> font-mono">
                            R$ <?= number_format($totalReceitas - $totalDespesas, 2, ',', '.'); ?>
                        </td>
                        <td class="px-6 py-4"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    <?php else: ?>
        <div class="text-center text-gray-500 py-16">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhuma transação encontrada</h3>
            <p class="mt-1 text-sm text-gray-500">Por favor, ajuste sua busca ou limpe os filtros.</p>
        </div>
    <?php endif; ?>
</div>