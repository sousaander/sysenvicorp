<?php

/**
 * View para listagem de prestações de contas aguardando aprovação.
 *
 * Variáveis disponíveis:
 *   - $pageTitle (string)
 *   - $transacoes (array)
 */
?>

<div class="flex justify-between items-center mb-6">
    <div>
        <h2 class="text-2xl font-bold"><?php echo htmlspecialchars($pageTitle); ?></h2>
        <p class="text-gray-600">Veja e aprove ou rejeite as prestações de contas enviadas pelos usuários.</p>
    </div>
    <div class="flex gap-2">
        <a href="<?php echo BASE_URL; ?>/financeiro/prestacoesAprovadas" class="px-4 py-2 text-sm font-semibold text-indigo-700 bg-indigo-100 rounded-lg shadow-md hover:bg-indigo-200 transition">
            Ver Histórico
        </a>
        <a href="<?php echo BASE_URL; ?>/financeiro" class="px-4 py-2 text-sm font-semibold text-gray-700 bg-gray-200 rounded-lg shadow-md hover:bg-gray-300 transition">
            &larr; Voltar
        </a>
    </div>
</div>

<div class="bg-white p-6 rounded-lg shadow-md w-full mx-auto">
    <?php if (!empty($transacoes)): ?>
        <form id="form-aprovacao-massa" action="<?php echo BASE_URL; ?>/financeiro/processarAprovacaoEmMassa" method="POST">
            <!-- Toolbar de Ações em Massa -->
            <div id="bulk-actions-toolbar" class="hidden bg-sky-50 border border-sky-200 rounded-t-lg p-3 flex items-center justify-between mb-0">
                <div>
                    <span id="selected-count" class="font-medium text-sky-800">0</span>
                    <span class="text-sky-700"> itens selecionados</span>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="abrirModalReprovacaoEmMassa()" class="px-4 py-2 text-sm font-semibold text-white bg-red-600 rounded-lg shadow-sm hover:bg-red-700 transition">
                        Reprovar Selecionados
                    </button>
                    <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-green-600 rounded-lg shadow-sm hover:bg-green-700 transition" onclick="return confirm('Confirma a aprovação dos itens selecionados?');">
                        Aprovar Selecionados
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left">
                                <input type="checkbox" id="selecionar-todos" class="h-4 w-4 text-sky-600 border-gray-300 rounded focus:ring-sky-500">
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Data</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Solicitante</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Descrição / Detalhes</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Categoria</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Anexo</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Valor</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($transacoes as $t): ?>
                            <?php
                            // Processa observações para extrair detalhes
                            $obs = htmlspecialchars($t['observacoes'] ?? '');
                            $projetoBadge = '';
                            if (preg_match('/Projeto ID: (\d+)/', $obs, $matches)) {
                                $projetoBadge = '<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">Proj. #' . $matches[1] . '</span>';
                                $obs = str_replace($matches[0] . '.', '', $obs);
                            }
                            // Formata detalhes extras para exibição mais limpa
                            $replacements = [
                                'Fornecedor:' => '<br><strong class="font-medium text-gray-700">Forn:</strong>',
                                'Local:' => '<strong class="font-medium text-gray-700">Loc:</strong>',
                                'Placa:' => '<strong class="font-medium text-gray-700">Placa:</strong>',
                                'Hodômetro:' => '<strong class="font-medium text-gray-700">Km:</strong>',
                                'Litros:' => '<strong class="font-medium text-gray-700">L:</strong>',
                                '|' => '&nbsp;'
                            ];
                            $obsDisplay = strtr($obs, $replacements);
                            ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-4 whitespace-nowrap align-top">
                                    <input type="checkbox" name="transacao_ids[]" value="<?php echo $t['id']; ?>" class="checkbox-item h-4 w-4 text-sky-600 border-gray-300 rounded focus:ring-sky-500">
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 align-top"><?php echo date('d/m/Y', strtotime($t['vencimento'])); ?></td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-700 align-top"><?php echo htmlspecialchars($t['nome_usuario'] ?? 'N/A'); ?></td>
                                <td class="px-4 py-4 text-sm text-gray-900 align-top">
                                    <div class="font-medium"><?php echo htmlspecialchars(str_replace('Prestação de Contas: ', '', $t['descricao'])); ?></div>
                                    <div class="mt-1"><?php echo $projetoBadge; ?></div>
                                    <div class="mt-1 text-xs text-gray-500 leading-relaxed"><?php echo $obsDisplay; ?></div>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 align-top"><?php echo htmlspecialchars($t['nome_prestacao_categoria'] ?? '-'); ?></td>
                                <td class="px-4 py-4 whitespace-nowrap text-center align-top">
                                    <?php if (!empty($t['documentoVinculado'])): ?>
                                        <a href="<?php echo BASE_URL; ?>/storage/comprovantes_prestacao/<?php echo $t['documentoVinculado']; ?>" target="_blank" class="text-indigo-600 hover:text-indigo-900 inline-flex flex-col items-center group" title="Visualizar Comprovante">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 group-hover:scale-110 transition-transform" viewBox="0 0 20 20" fill="currentColor">
                                                <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                                                <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                                            </svg>
                                            <span class="text-[10px] mt-0.5">Ver</span>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-gray-300 text-xs">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm text-right font-medium text-gray-900 align-top">
                                    R$ <?php echo number_format($t['valor'], 2, ',', '.'); ?>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-center space-x-2 align-top">
                                    <form action="<?php echo rtrim(BASE_URL, '/'); ?>/financeiro/processarAprovacao" method="POST" class="inline">
                                        <input type="hidden" name="id" value="<?php echo $t['id']; ?>">
                                        <input type="hidden" name="acao" value="aprovar">
                                        <button type="submit" class="px-3 py-1 text-xs font-semibold text-white bg-green-600 rounded hover:bg-green-700 transition shadow-sm" onclick="return confirm('Confirma aprovação desta despesa?');">
                                            Aprovar
                                        </button>
                                    </form>
                                    <button type="button" onclick="abrirModalReprovacao(<?php echo $t['id']; ?>)" class="px-3 py-1 text-xs font-semibold text-white bg-red-600 rounded hover:bg-red-700 transition shadow-sm">
                                        Reprovar
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </form>

        <!-- Paginação -->
        <?php if (isset($totalPaginas) && $totalPaginas > 1): ?>
            <div class="mt-4 flex justify-center pb-2">
                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                    <?php if ($paginaAtual > 1): ?>
                        <a href="?page=<?php echo $paginaAtual - 1; ?>" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                            <span class="sr-only">Anterior</span>
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                        </a>
                    <?php endif; ?>

                    <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">
                        Página <?php echo $paginaAtual; ?> de <?php echo $totalPaginas; ?>
                    </span>

                    <?php if ($paginaAtual < $totalPaginas): ?>
                        <a href="?page=<?php echo $paginaAtual + 1; ?>" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
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
        <p class="text-center text-gray-500">Nenhuma prestação de contas aguardando aprovação.</p>
    <?php endif; ?>
</div>

<!-- Modal de Reprovação -->
<div id="modalReprovacao" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Reprovar Prestação de Contas</h3>
            <form id="formReprovacao" action="<?php echo rtrim(BASE_URL, '/'); ?>/financeiro/processarAprovacao" method="POST" class="mt-2 text-left">
                <input type="hidden" name="id" id="reprovacao_id">
                <input type="hidden" name="acao" value="reprovar">

                <div class="mt-4">
                    <label for="motivo_reprovacao" class="block text-sm font-medium text-gray-700">Motivo da Reprovação <span class="text-red-500">*</span></label>
                    <textarea id="motivo_reprovacao" name="motivo_reprovacao" rows="3" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm p-2" placeholder="Explique o motivo da reprovação..."></textarea>
                </div>

                <div class="mt-4 flex justify-end gap-2">
                    <button type="button" onclick="fecharModalReprovacao()" class="px-4 py-2 bg-gray-100 text-gray-700 text-base font-medium rounded-md shadow-sm hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-300">
                        Cancelar
                    </button>
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white text-base font-medium rounded-md shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-300">
                        Confirmar Reprovação
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de Reprovação em Massa -->
<div id="modalReprovacaoEmMassa" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Reprovar Prestações em Massa</h3>
            <form id="formReprovacaoEmMassa" action="<?php echo rtrim(BASE_URL, '/'); ?>/financeiro/processarReprovacaoEmMassa" method="POST" class="mt-2 text-left">
                <div id="ids-container-massa"></div> <!-- Container para os inputs hidden com os IDs -->

                <div class="mt-4">
                    <label for="motivo_reprovacao_massa" class="block text-sm font-medium text-gray-700">Motivo da Reprovação (será aplicado a todos) <span class="text-red-500">*</span></label>
                    <textarea id="motivo_reprovacao_massa" name="motivo_reprovacao" rows="3" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm p-2" placeholder="Explique o motivo da reprovação..."></textarea>
                </div>

                <div class="mt-4 flex justify-end gap-2">
                    <button type="button" onclick="fecharModalReprovacaoEmMassa()" class="px-4 py-2 bg-gray-100 text-gray-700 text-base font-medium rounded-md shadow-sm hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-300">
                        Cancelar
                    </button>
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white text-base font-medium rounded-md shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-300" onclick="return confirm('Confirma a reprovação dos itens selecionados?');">
                        Confirmar Reprovação
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function abrirModalReprovacao(id) {
        document.getElementById('reprovacao_id').value = id;
        document.getElementById('modalReprovacao').classList.remove('hidden');
    }

    function fecharModalReprovacao() {
        document.getElementById('modalReprovacao').classList.add('hidden');
        document.getElementById('motivo_reprovacao').value = ''; // Limpa o campo
    }

    function abrirModalReprovacaoEmMassa() {
        const selectedCheckboxes = document.querySelectorAll('.checkbox-item:checked');
        if (selectedCheckboxes.length === 0) {
            alert('Por favor, selecione pelo menos uma despesa para reprovar.');
            return;
        }

        const idsContainer = document.getElementById('ids-container-massa');
        idsContainer.innerHTML = ''; // Limpa container

        selectedCheckboxes.forEach(checkbox => {
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'transacao_ids[]';
            hiddenInput.value = checkbox.value;
            idsContainer.appendChild(hiddenInput);
        });

        document.getElementById('modalReprovacaoEmMassa').classList.remove('hidden');
    }

    function fecharModalReprovacaoEmMassa() {
        document.getElementById('modalReprovacaoEmMassa').classList.add('hidden');
        document.getElementById('motivo_reprovacao_massa').value = '';
    }

    // --- LÓGICA DE SELEÇÃO EM MASSA ---
    document.addEventListener('DOMContentLoaded', function() {
        const selectAllCheckbox = document.getElementById('selecionar-todos');
        const itemCheckboxes = document.querySelectorAll('.checkbox-item');
        const bulkActionsToolbar = document.getElementById('bulk-actions-toolbar');
        const selectedCountSpan = document.getElementById('selected-count');

        function updateToolbar() {
            const selected = document.querySelectorAll('.checkbox-item:checked');
            const count = selected.length;

            if (count > 0) {
                bulkActionsToolbar.classList.remove('hidden');
                selectedCountSpan.textContent = count;
            } else {
                bulkActionsToolbar.classList.add('hidden');
            }
        }

        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                itemCheckboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
                updateToolbar();
            });
        }

        itemCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                updateToolbar();
                selectAllCheckbox.checked = Array.from(itemCheckboxes).every(c => c.checked);
            });
        });
    });
</script>