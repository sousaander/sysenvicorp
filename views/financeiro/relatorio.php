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
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
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

            <!-- Filtro por Categoria -->
            <div>
                <label for="filtro_classificacao_id" class="block text-sm font-medium text-gray-700">Categoria</label>
                <select id="filtro_classificacao_id" name="classificacao_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-sky-500 focus:border-sky-500 sm:text-sm p-2">
                    <option value="">Todas as Categorias</option>
                    <?php foreach ($classificacoes as $class): ?>
                        <option value="<?= $class['id']; ?>" <?= (isset($filtros['classificacao_id']) && $filtros['classificacao_id'] == $class['id']) ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($class['nome']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Filtro por Centro de Custo -->
            <div>
                <label for="filtro_centro_custo_id" class="block text-sm font-medium text-gray-700">Centro de Custo</label>
                <select id="filtro_centro_custo_id" name="centro_custo_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-sky-500 focus:border-sky-500 sm:text-sm p-2">
                    <option value="">Todos os Centros</option>
                    <?php foreach ($centrosCusto as $cc): ?>
                        <option value="<?= $cc['id']; ?>" <?= (isset($filtros['centro_custo_id']) && $filtros['centro_custo_id'] == $cc['id']) ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($cc['nome']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Filtro por Status -->
            <div>
                <label for="filtro_status" class="block text-sm font-medium text-gray-700">Status</label>
                <select id="filtro_status" name="status" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-sky-500 focus:border-sky-500 sm:text-sm p-2">
                    <option value="">Todos os Status</option>
                    <option value="Pago" <?= (isset($filtros['status']) && $filtros['status'] == 'Pago') ? 'selected' : ''; ?>>Somente Pagos/Recebidos</option>
                    <option value="Pendente" <?= (isset($filtros['status']) && $filtros['status'] == 'Pendente') ? 'selected' : ''; ?>>Pendentes / A vencer</option>
                    <option value="Atrasado" <?= (isset($filtros['status']) && $filtros['status'] == 'Atrasado') ? 'selected' : ''; ?>>Somente Atrasados</option>
                    <option value="Cancelado" <?= (isset($filtros['status']) && $filtros['status'] == 'Cancelado') ? 'selected' : ''; ?>>Cancelados</option>
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mt-4">
            <!-- Filtro por Período -->
            <div>
                <label for="filtro_periodo" class="block text-sm font-medium text-gray-700">Período</label>
                <select id="filtro_periodo" name="periodo" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-sky-500 focus:border-sky-500 sm:text-sm p-2">
                    <option value="recente" <?= (isset($filtros['periodo']) && $filtros['periodo'] == 'recente') ? 'selected' : ''; ?>>Mais Recentes (30 dias)</option>
                    <option value="dia" <?= (isset($filtros['periodo']) && $filtros['periodo'] == 'dia') ? 'selected' : ''; ?>>Dia Específico</option>
                    <option value="mes" <?= (isset($filtros['periodo']) && $filtros['periodo'] == 'mes') ? 'selected' : ''; ?>>Mês Específico</option>
                    <option value="intervalo" <?= (isset($filtros['periodo']) && $filtros['periodo'] == 'intervalo') ? 'selected' : ''; ?>>Intervalo de Datas</option>
                </select>
            </div>

            <!-- Campos de Data Dinâmicos -->
            <div class="col-span-1 md:col-span-2 lg:col-span-2">
                <div id="campo_data_unica" class="hidden">
                    <label for="data_unica" class="block text-sm font-medium text-gray-700">Data</label>
                    <input type="date" name="data_unica" id="data_unica" value="<?= htmlspecialchars($filtros['data_unica'] ?? ''); ?>" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-sky-500 focus:border-sky-500 sm:text-sm p-2">
                </div>
                <div id="campo_mes_ano" class="hidden">
                    <label for="mes_ano" class="block text-sm font-medium text-gray-700">Mês/Ano</label>
                    <input type="month" name="mes_ano" id="mes_ano" value="<?= htmlspecialchars($filtros['mes_ano'] ?? ''); ?>" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-sky-500 focus:border-sky-500 sm:text-sm p-2">
                </div>
                <div id="campo_intervalo" class="grid grid-cols-2 gap-4 hidden">
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
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Centro de Custo</th>
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

                        if ($transacao['status'] !== 'Cancelado') {
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
                        } else {
                            $valorSign = $transacao['tipo'] === 'P' ? '- ' : '+ ';
                        }
                        ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php 
                                $dataExibir = ($transacao['status'] === 'Pago' && !empty($transacao['data_pagamento'])) ? $transacao['data_pagamento'] : $transacao['data'];
                                echo date('d/m/Y', strtotime($dataExibir)); 
                                ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= htmlspecialchars($transacao['descricao']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($transacao['nome_banco'] ?? 'N/A'); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($transacao['nome_classificacao'] ?? 'N/A'); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($transacao['nome_centro_custo'] ?? 'N/A'); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-mono <?= (strpos($valorSign, '-') !== false) ? 'text-red-600' : 'text-green-600'; ?>">
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
                        <td colspan="5" class="px-6 py-3 text-right text-sm font-semibold text-gray-600">Total de Receitas</td>
                        <td class="px-6 py-3 text-right text-sm font-semibold text-green-600 font-mono">
                            R$ <?= number_format($totalReceitas, 2, ',', '.'); ?>
                        </td>
                        <td class="px-6 py-3"></td>
                    </tr>
                    <tr>
                        <td colspan="5" class="px-6 py-3 text-right text-sm font-semibold text-gray-600">Total de Despesas</td>
                        <td class="px-6 py-3 text-right text-sm font-semibold text-red-600 font-mono">
                            - R$ <?= number_format($totalDespesas, 2, ',', '.'); ?>
                        </td>
                        <td class="px-6 py-3"></td>
                    </tr>
                    <tr class="border-t border-gray-200">
                        <td colspan="5" class="px-6 py-4 text-right text-base font-bold text-gray-800">Saldo do Período</td>
                        <td class="px-6 py-4 text-right text-base font-bold <?= ($totalReceitas - $totalDespesas) >= 0 ? 'text-blue-600' : 'text-red-700'; ?> font-mono">
                            R$ <?= number_format($totalReceitas - $totalDespesas, 2, ',', '.'); ?>
                        </td>
                        <td class="px-6 py-4"></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Controles de Paginação -->
        <?php if (isset($totalPaginas) && $totalPaginas > 1): ?>
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="text-sm text-gray-700">
                    Exibindo <span class="font-medium"><?= count($transacoes) ?></span> de <span class="font-medium"><?= $totalRegistros ?></span> registros
                </div>
                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                    <?php
                    // Preserva os filtros da URL nas trocas de página
                    $queryParams = $_GET;
                    unset($queryParams['page']);
                    $queryString = http_build_query($queryParams);
                    $baseUrl = '?' . ($queryString ? $queryString . '&' : '');

                    $maxLinks = 5;
                    $start = max(1, $paginaAtual - floor($maxLinks / 2));
                    $end = min($totalPaginas, $start + $maxLinks - 1);
                    $start = max(1, $end - $maxLinks + 1);
                    ?>

                    <?php if ($paginaAtual > 1): ?>
                        <a href="<?= $baseUrl ?>page=<?= $paginaAtual - 1 ?>" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                            <span class="sr-only">Anterior</span>
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                        </a>
                    <?php endif; ?>

                    <?php for ($i = $start; $i <= $end; $i++): ?>
                        <a href="<?= $baseUrl ?>page=<?= $i ?>" class="relative inline-flex items-center px-4 py-2 border text-sm font-medium <?= ($i == $paginaAtual) ? 'z-10 bg-sky-50 border-sky-500 text-sky-600' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($paginaAtual < $totalPaginas): ?>
                        <a href="<?= $baseUrl ?>page=<?= $paginaAtual + 1 ?>" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                            <span class="sr-only">Próximo</span>
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                            </svg>
                        </a>
                    <?php endif; ?>
                </nav>
            </div>
        <?php endif; ?>
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

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const filtroPeriodo = document.getElementById('filtro_periodo');
        const campoDataUnica = document.getElementById('campo_data_unica');
        const campoMesAno = document.getElementById('campo_mes_ano');
        const campoIntervalo = document.getElementById('campo_intervalo');

        function toggleDateFields() {
            const periodo = filtroPeriodo.value;

            // Oculta todos
            campoDataUnica.classList.add('hidden');
            campoMesAno.classList.add('hidden');
            campoIntervalo.classList.add('hidden');

            // Mostra o selecionado
            if (periodo === 'dia') {
                campoDataUnica.classList.remove('hidden');
            } else if (periodo === 'mes') {
                campoMesAno.classList.remove('hidden');
            } else if (periodo === 'intervalo') {
                campoIntervalo.classList.remove('hidden');
            }
        }

        if (filtroPeriodo) {
            filtroPeriodo.addEventListener('change', toggleDateFields);
            // Inicializa o estado dos campos
            toggleDateFields();
        }
    });
</script>