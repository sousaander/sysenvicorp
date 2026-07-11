<?php $isEdit = isset($transacao) && $transacao !== null; ?>

<div class="flex justify-between items-center mb-6">
    <div>
        <h2 class="text-2xl font-bold text-gray-800"><?php echo htmlspecialchars($pageTitle ?? 'Prestação de Contas'); ?></h2>
        <p class="text-gray-600">Registre suas despesas de projeto para reembolso.</p>
    </div>
    <a href="<?php echo BASE_URL; ?>/financeiro" class="bg-gray-200 text-gray-800 px-4 py-2 rounded-md hover:bg-gray-300 font-medium flex items-center">
        &larr; Voltar
    </a>
</div>

<div class="bg-white p-6 rounded-lg shadow-md max-w-4xl mx-auto mb-8">
    <form action="<?php echo BASE_URL; ?>/financeiro/salvarPrestacaoContas" method="POST" enctype="multipart/form-data" class="space-y-4">
        <input type="hidden" name="id" value="<?php echo $isEdit ? $transacao['id'] : ''; ?>">
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="projeto_id" class="block text-sm font-medium text-gray-700">Projeto <span class="text-red-500">*</span></label>
                <select id="projeto_id" name="projeto_id" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-sky-500 focus:border-sky-500 sm:text-sm p-2 border">
                    <option value="">Selecione...</option>
                    <?php foreach ($projetos as $proj): ?>
                        <option value="<?php echo $proj['id']; ?>" <?php echo ($isEdit && isset($transacao['projeto_id']) && $transacao['projeto_id'] == $proj['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($proj['nome']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div>
                <label for="data_despesa" class="block text-sm font-medium text-gray-700">Data da Despesa <span class="text-red-500">*</span></label>
                <input type="date" id="data_despesa" name="data_despesa" required value="<?php echo $isEdit ? $transacao['vencimento'] : date('Y-m-d'); ?>" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-sky-500 focus:border-sky-500 sm:text-sm p-2 border">
            </div>

            <div class="md:col-span-2">
                <label for="descricao" class="block text-sm font-medium text-gray-700">Descrição da Despesa <span class="text-red-500">*</span></label>
                <input type="text" id="descricao" name="descricao" required value="<?php echo $isEdit ? htmlspecialchars($transacao['descricao_limpa'] ?? str_replace('Prestação de Contas: ', '', $transacao['descricao'])) : ''; ?>" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-sky-500 focus:border-sky-500 sm:text-sm p-2 border" placeholder="Ex: Almoço com cliente, Material de escritório...">
            </div>

            <div>
                <label for="prestacao_categoria_id" class="block text-sm font-medium text-gray-700">Categoria <span class="text-red-500">*</span></label>
                <div class="flex gap-2">
                    <select id="prestacao_categoria_id" name="prestacao_categoria_id" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-sky-500 focus:border-sky-500 sm:text-sm p-2 border">
                        <option value="">Selecione...</option>
                        <?php foreach ($categorias as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo ($isEdit && isset($transacao['prestacao_categoria_id']) && $transacao['prestacao_categoria_id'] == $cat['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['nome']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="button" id="btnAddCategoria" class="mt-1 px-3 py-2 bg-sky-100 text-sky-700 rounded-md hover:bg-sky-200" title="Adicionar Categoria">+</button>
                </div>
            </div>

            <div>
                <label for="centro_custo_id" class="block text-sm font-medium text-gray-700">Centro de Custo</label>
                <div class="flex gap-2">
                    <select id="centro_custo_id" name="centro_custo_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-sky-500 focus:border-sky-500 sm:text-sm p-2 border">
                        <option value="">Selecione...</option>
                        <?php if (!empty($centrosCusto)): ?>
                            <?php foreach ($centrosCusto as $cc): ?>
                                <option value="<?php echo $cc['id']; ?>" <?php echo ($isEdit && isset($transacao['centro_custo_id']) && $transacao['centro_custo_id'] == $cc['id']) ? 'selected' : (count($centrosCusto) == 1 ? 'selected' : ''); ?>>
                                    <?php echo htmlspecialchars($cc['nome']); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <button type="button" id="btnAddCentroCusto" class="mt-1 px-3 py-2 bg-sky-100 text-sky-700 rounded-md hover:bg-sky-200" title="Adicionar Centro de Custo">+</button>
                </div>
            </div>

            <div>
                <label for="valor" class="block text-sm font-medium text-gray-700">Valor (R$) <span class="text-red-500">*</span></label>
                <input type="text" id="valor" name="valor" required value="<?php echo $isEdit ? number_format($transacao['valor'], 2, ',', '.') : ''; ?>" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-sky-500 focus:border-sky-500 sm:text-sm p-2 border font-bold text-gray-700" placeholder="0,00">
            </div>

            <div>
                <label for="forma_pagamento" class="block text-sm font-medium text-gray-700">Forma de Pagamento <span class="text-red-500">*</span></label>
                <select id="forma_pagamento" name="forma_pagamento" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-sky-500 focus:border-sky-500 sm:text-sm p-2 border">
                    <option value="">Selecione...</option>
                    <?php 
                    $formas = ['Pix', 'Dinheiro', 'Transferência', 'Boleto', 'Cartão Corporativo', 'Cartão de Crédito', 'Cartão de Débito', 'Cheque', 'Watsapp', 'Pagamento Digital', 'Depósito'];
                    foreach ($formas as $f) {
                        $selected = ($isEdit && isset($transacao['forma_pagamento']) && $transacao['forma_pagamento'] == $f) ? 'selected' : '';
                        echo "<option value='$f' $selected>$f</option>";
                    }
                    ?>
                </select>
            </div>

            <div>
                <label for="banco_id" class="block text-sm font-medium text-gray-700">Banco / Caixa</label>
                <select id="banco_id" name="banco_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-sky-500 focus:border-sky-500 sm:text-sm p-2 border">
                    <option value="">Selecione...</option>
                    <?php if (!empty($bancos)): ?>
                        <?php foreach ($bancos as $banco): ?>
                            <option value="<?php echo $banco['id']; ?>" <?php echo ($isEdit && isset($transacao['banco_id']) && $transacao['banco_id'] == $banco['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($banco['nome']); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <!-- Campos Extras Opcionais -->
            <div>
                <label for="fornecedor" class="block text-sm font-medium text-gray-700">Fornecedor</label>
                <input type="text" id="fornecedor" name="fornecedor" value="<?php echo $isEdit ? htmlspecialchars($transacao['fornecedor'] ?? '') : ''; ?>" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-sky-500 focus:border-sky-500 sm:text-sm p-2 border">
            </div>
            
            <div>
                <label for="local_despesa" class="block text-sm font-medium text-gray-700">Cidade/UF</label>
                <input type="text" id="local_despesa" name="local_despesa" value="<?php echo $isEdit ? htmlspecialchars($transacao['local_despesa'] ?? '') : ''; ?>" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-sky-500 focus:border-sky-500 sm:text-sm p-2 border" placeholder="Ex: São Paulo/SP">
            </div>
            
            <div>
                <label for="numero_nota_fiscal" class="block text-sm font-medium text-gray-700">Nº Nota Fiscal</label>
                <input type="text" id="numero_nota_fiscal" name="numero_nota_fiscal" value="<?php echo $isEdit ? htmlspecialchars($transacao['numero_nota_fiscal'] ?? '') : ''; ?>" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-sky-500 focus:border-sky-500 sm:text-sm p-2 border">
            </div>

            <!-- Campos específicos para Combustível -->
            <div id="campos_combustivel" class="md:col-span-2 grid grid-cols-1 md:grid-cols-3 gap-4 border p-3 rounded-md bg-yellow-50 hidden">
                <div class="md:col-span-3 text-sm font-bold text-gray-700 border-b border-yellow-200 pb-1 mb-2">Dados de Abastecimento</div>
                <div>
                    <label for="placa_veiculo" class="block text-sm font-medium text-gray-700">Placa</label>
                    <input type="text" id="placa_veiculo" name="placa_veiculo" value="<?php echo $isEdit ? htmlspecialchars($transacao['placa_veiculo'] ?? '') : ''; ?>" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-sky-500 focus:border-sky-500 sm:text-sm p-2 border uppercase" placeholder="ABC-1234">
                </div>
                <div>
                    <label for="litros" class="block text-sm font-medium text-gray-700">Litros</label>
                    <input type="number" step="0.01" id="litros" name="litros" value="<?php echo $isEdit ? htmlspecialchars($transacao['litros'] ?? '') : ''; ?>" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-sky-500 focus:border-sky-500 sm:text-sm p-2 border">
                </div>
                <div>
                    <label for="hodometro" class="block text-sm font-medium text-gray-700">Hodômetro (Km)</label>
                    <input type="number" id="hodometro" name="hodometro" value="<?php echo $isEdit ? htmlspecialchars($transacao['hodometro'] ?? '') : ''; ?>" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-sky-500 focus:border-sky-500 sm:text-sm p-2 border">
                </div>
            </div>

            <!-- Upload de Comprovante -->
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700">Comprovante (Foto ou PDF)</label>
                <div class="mt-1 flex items-center gap-2">
                    <input type="file" id="comprovante" name="comprovante" accept="image/*,application/pdf" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-sky-50 file:text-sky-700 hover:file:bg-sky-100">
                    <button type="button" id="btn-camera" class="px-3 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 flex items-center gap-1" title="Tirar Foto">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        Foto
                    </button>
                    <input type="file" id="camera_input" accept="image/*" capture="environment" style="visibility: hidden; position: absolute; width: 0; height: 0;">
                </div>
                <div id="camera_preview_container" class="mt-2 hidden">
                    <p class="text-xs text-gray-500 mb-1">Prévia da foto:</p>
                    <img id="camera_preview" src="" alt="Prévia" class="max-w-xs h-auto rounded border shadow-sm">
                </div>
                <?php if ($isEdit && !empty($transacao['documentoVinculado'])): ?>
                    <p class="mt-1 text-sm text-green-600">Comprovante atual: <a href="<?php echo BASE_URL . '/storage/comprovantes_prestacao/' . $transacao['documentoVinculado']; ?>" target="_blank" class="underline">Visualizar</a></p>
                <?php endif; ?>
            </div>
        </div>

        <div class="flex justify-end pt-4">
            <button type="submit" class="px-6 py-2 bg-emerald-600 text-white font-bold rounded-lg shadow-md hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 transition">
                <?php echo $isEdit ? 'Atualizar Solicitação' : 'Enviar para Aprovação'; ?>
            </button>
        </div>
    </form>
</div>

<!-- Lista de Solicitações Pendentes -->
<div class="bg-white p-6 rounded-lg shadow-md max-w-4xl mx-auto mt-8">
    <h3 class="text-lg font-bold text-gray-800 mb-4">Minhas Solicitações Pendentes</h3>
    <?php if (!empty($minhasPrestacoes)): ?>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Data da Solicitação</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Descrição</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Valor</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Forma de Pagto</th>
                        <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Ação</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($minhasPrestacoes as $p): ?>
                        <tr>
                            <td class="px-4 py-2 text-sm text-gray-500"><?php echo date('d/m/Y', strtotime($p['created_at'])); ?></td>
                            <td class="px-4 py-2 text-sm text-gray-900"><?php echo htmlspecialchars(str_replace('Prestação de Contas: ', '', $p['descricao'])); ?></td>
                            <td class="px-4 py-2 text-sm text-right font-medium">R$ <?php echo number_format($p['valor'], 2, ',', '.'); ?></td>
                            <td class="px-4 py-2 text-sm text-gray-500"><?php echo htmlspecialchars($p['forma_pagamento'] ?? '-'); ?></td>
                            <td class="px-4 py-2 text-center">
                                <?php
                                $statusClass = $p['status'] === 'Reprovado' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800';
                                ?>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $statusClass; ?>">
                                    <?php echo htmlspecialchars($p['status']); ?>
                                </span>
                            </td>
                            <td class="px-4 py-2 text-center">
                                <a href="<?php echo BASE_URL; ?>/financeiro/editarPrestacaoContas/<?php echo $p['id']; ?>" class="text-indigo-600 hover:text-indigo-900 mr-2" title="Editar">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                    </svg>
                                </a>
                                <form method="POST" action="<?php echo BASE_URL; ?>/financeiro/excluirPrestacaoContas" class="inline-block align-middle" onsubmit="return confirm('Tem certeza que deseja excluir esta solicitação?');">
                                    <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
                                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                    <button type="submit" class="text-red-600 hover:text-red-900" title="Excluir">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="text-gray-500 text-center py-4">Nenhuma solicitação pendente encontrada.</p>
    <?php endif; ?>

    <!-- Controles de Paginação -->
    <?php if (isset($totalPaginas) && $totalPaginas > 1): ?>
        <div class="mt-4 flex justify-center pb-2">
            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                <?php
                $queryParams = $_GET;
                ?>
                
                <?php if ($paginaAtual > 1): ?>
                    <?php $queryParams['page_pendentes'] = $paginaAtual - 1; ?>
                    <a href="?<?= http_build_query($queryParams) ?>" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                        <span class="sr-only">Anterior</span>
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                    </a>
                <?php endif; ?>

                <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">
                    Página <?= $paginaAtual ?> de <?= $totalPaginas ?>
                </span>

                <?php if ($paginaAtual < $totalPaginas): ?>
                    <?php $queryParams['page_pendentes'] = $paginaAtual + 1; ?>
                    <a href="?<?= http_build_query($queryParams) ?>" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                        <span class="sr-only">Próximo</span>
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                        </svg>
                    </a>
                <?php endif; ?>
            </nav>
        </div>
    <?php endif; ?>
</div>

<!-- Seção para Gerar Relatório -->
<div class="bg-white p-6 rounded-lg shadow-md max-w-4xl mx-auto mt-8">
    <h3 class="text-lg font-bold text-gray-800 mb-4">Relatório de Despesas Aprovadas</h3>
    <form id="formRelatorio" action="<?php echo BASE_URL; ?>/financeiro/relatorioPrestacaoContasProjeto" method="GET" target="_blank" class="flex flex-col md:flex-row gap-4 items-end">
        <div class="flex-grow w-full md:w-auto">
            <label for="relatorio_projeto_id" class="block text-sm font-medium text-gray-700 mb-1">Selecione o Projeto</label>
            <select id="relatorio_projeto_id" name="relatorio_projeto_id" required class="w-full border-gray-300 rounded-lg shadow-sm focus:border-sky-500 focus:ring-sky-500 p-2">
                <option value="">Selecione...</option>
                <?php foreach ($projetos as $projeto) : ?>
                    <option value="<?php echo $projeto['id']; ?>"><?php echo htmlspecialchars($projeto['nome']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="flex gap-2 w-full md:w-auto">
            <button type="submit" class="flex-1 md:flex-none px-4 py-2 text-sm font-semibold text-white bg-gray-600 rounded-lg shadow-md hover:bg-gray-700 transition h-10">
                Gerar PDF
            </button>
            <button type="submit" formaction="<?php echo BASE_URL; ?>/financeiro/exportarPrestacaoContasZip" class="flex-1 md:flex-none px-4 py-2 text-sm font-semibold text-white bg-green-600 rounded-lg shadow-md hover:bg-green-700 transition h-10 flex items-center justify-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Baixar CSV + Anexos (ZIP)
            </button>
        </div>
    </form>
</div>

<script>
    // Validação do formulário de relatório
    function validarFormularioRelatorio(event) {
        const relatorioProjetoSelect = document.getElementById('relatorio_projeto_id');
        if (!relatorioProjetoSelect || relatorioProjetoSelect.value.trim() === '') {
            event.preventDefault();
            alert('Por favor, selecione um projeto para gerar o relatório.');
            relatorioProjetoSelect.focus();
            return false;
        }
        return true;
    }

    // Attach validation to the report form on page load
    document.addEventListener('DOMContentLoaded', function() {
        const formRelatorio = document.getElementById('formRelatorio');
        if (formRelatorio) {
            formRelatorio.addEventListener('submit', validarFormularioRelatorio);
        }
    });

    // Função de validação do formulário com DEBUG
    function validarFormDebug() {
        const projetoSelect = document.getElementById('projeto_id');
        console.log('DEBUG - projeto_id element:', projetoSelect);
        console.log('DEBUG - projeto_id value:', projetoSelect ? projetoSelect.value : 'ELEMENT NOT FOUND');
        console.log('DEBUG - projeto_id name attribute:', projetoSelect ? projetoSelect.getAttribute('name') : 'N/A');
        
        const descricaoInput = document.getElementById('descricao');
        const valorInput = document.getElementById('valor');
        const dataDespesaInput = document.getElementById('data_despesa');
        const categoriaSelect = document.getElementById('prestacao_categoria_id');
        const formaPagtoSelect = document.getElementById('forma_pagamento');

        if (!projetoSelect || projetoSelect.value === '') {
            alert('Por favor, selecione um projeto.');
            projetoSelect.focus();
            return false;
        }

        if (!descricaoInput || descricaoInput.value.trim() === '') {
            alert('Por favor, preencha a descrição da despesa.');
            descricaoInput.focus();
            return false;
        }

        if (!valorInput || valorInput.value.trim() === '' || parseFloat(valorInput.value.replace(/\./g, '').replace(',', '.')) <= 0) {
            alert('Por favor, preencha um valor válido maior que zero.');
            valorInput.focus();
            return false;
        }

        if (!dataDespesaInput || dataDespesaInput.value === '') {
            alert('Por favor, selecione a data da despesa.');
            dataDespesaInput.focus();
            return false;
        }

        if (!categoriaSelect || categoriaSelect.value === '') {
            alert('Por favor, selecione uma categoria de despesa.');
            categoriaSelect.focus();
            return false;
        }

        if (!formaPagtoSelect || formaPagtoSelect.value === '') {
            alert('Por favor, selecione a forma de pagamento utilizada.');
            formaPagtoSelect.focus();
            return false;
        }

        return true;
    }

    // Script para formatar o campo de valor como moeda
    document.addEventListener('DOMContentLoaded', function() {
        // Validação de tamanho de arquivo (Frontend)
        const inputComprovante = document.getElementById('comprovante');
        if (inputComprovante) {
            inputComprovante.addEventListener('change', function() {
                // Define o limite (ex: 8MB = 8 * 1024 * 1024 bytes). Ajuste conforme seu php.ini (post_max_size).
                const maxSize = 8 * 1024 * 1024; 
                if (this.files && this.files[0] && this.files[0].size > maxSize) {
                    alert('O arquivo selecionado é muito grande (maior que 8MB). Por favor, escolha um arquivo menor ou comprima-o antes de enviar.');
                    this.value = ''; // Limpa o campo para impedir o envio
                }
            });
        }

        const valorInput = document.getElementById('valor');
        
        // --- DEBUG: Listener para form submission ---
        const form = document.querySelector('form[action*="salvarPrestacaoContas"]');
        if (form) {
            form.addEventListener('submit', function(e) {
                // Validação do formulário antes de processar
                if (!validarFormDebug()) {
                    e.preventDefault();
                    return false;
                }

                const projetoSelect = document.getElementById('projeto_id');
                console.log('=== FORM SUBMIT DEBUG ===');
                console.log('projeto_id exists:', !!projetoSelect);
                console.log('projeto_id value:', projetoSelect ? projetoSelect.value : 'N/A');

                // --- PREVENÇÃO DE DUPLO CLIQUE ---
                const submitBtn = form.querySelector('button[type="submit"]');
                if (submitBtn && !submitBtn.disabled) {
                    const originalHtml = submitBtn.innerHTML;

                    // Pequeno delay para garantir que o envio comece antes de desabilitar o botão
                    setTimeout(() => {
                        submitBtn.disabled = true;
                        submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
                        submitBtn.innerHTML = '<svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Enviando...';
                    }, 50);

                    // Reabilita após 5 segundos se a página não redirecionar
                    setTimeout(() => {
                        if (submitBtn.disabled) {
                            submitBtn.disabled = false;
                            submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                            submitBtn.innerHTML = originalHtml;
                        }
                    }, 5000);
                }

                // Log all form inputs
                const formData = new FormData(form);
                console.log('Form data keys:', Array.from(formData.keys()));
                console.log('projeto_id from FormData:', formData.get('projeto_id'));
                console.log('====END DEBUG====');
            });
        }
        
        // --- LÓGICA DA CÂMERA ---
        const btnCamera = document.getElementById('btn-camera');
        const cameraInput = document.getElementById('camera_input');
        const mainInput = document.getElementById('comprovante');

        if (btnCamera && cameraInput && mainInput) {
            // Ao clicar no botão da câmera, aciona o input oculto com capture="environment"
            btnCamera.addEventListener('click', function() {
                cameraInput.click();
            });

            // Quando uma foto é tirada/selecionada no input da câmera
            cameraInput.addEventListener('change', function() {
                if (this.files && this.files.length > 0) {
                    const file = this.files[0];

                    // Compressão de imagem antes do envio
                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const img = new Image();
                            img.onload = function() {
                                // Configuração: Máximo de 1280px (largura ou altura)
                                const maxWidth = 1280;
                                const maxHeight = 1280;
                                let width = img.width;
                                let height = img.height;

                                // Calcula novas dimensões mantendo proporção
                                if (width > height) {
                                    if (width > maxWidth) {
                                        height *= maxWidth / width;
                                        width = maxWidth;
                                    }
                                } else {
                                    if (height > maxHeight) {
                                        width *= maxHeight / height;
                                        height = maxHeight;
                                    }
                                }

                                const canvas = document.createElement('canvas');
                                canvas.width = width;
                                canvas.height = height;
                                const ctx = canvas.getContext('2d');
                                ctx.drawImage(img, 0, 0, width, height);

                                // Converte canvas para Blob (JPEG com 70% de qualidade)
                                canvas.toBlob((blob) => {
                                    // Cria novo arquivo comprimido (força extensão .jpg)
                                    const compressedFile = new File([blob], file.name.replace(/\.[^/.]+$/, "") + ".jpg", {
                                        type: 'image/jpeg',
                                        lastModified: Date.now()
                                    });

                                    // Transfere para o input principal
                                    const dataTransfer = new DataTransfer();
                                    dataTransfer.items.add(compressedFile);
                                    mainInput.files = dataTransfer.files;

                                    // Exibe a prévia da foto
                                    const previewContainer = document.getElementById('camera_preview_container');
                                    const previewImg = document.getElementById('camera_preview');
                                    if (previewContainer && previewImg) {
                                        previewImg.src = URL.createObjectURL(blob);
                                        previewContainer.classList.remove('hidden');
                                    }
                                }, 'image/jpeg', 0.7);
                            };
                            img.src = e.target.result;
                        };
                        reader.readAsDataURL(file);
                    } else {
                        // Se não for imagem (ex: PDF), transfere diretamente sem comprimir
                        const dataTransfer = new DataTransfer();
                        dataTransfer.items.add(file);
                        mainInput.files = dataTransfer.files;
                    }
                }
            });
        }

        if (valorInput) {
            valorInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value) {
                    value = (parseFloat(value) / 100).toLocaleString('pt-BR', {
                        minimumFractionDigits: 2
                    });
                    e.target.value = value;
                }
            });
        }

        // Adicionar nova categoria via AJAX
        const btnAddCategoria = document.getElementById('btnAddCategoria');
        if (btnAddCategoria) {
            btnAddCategoria.addEventListener('click', function() {
                const nome = prompt("Digite o nome da nova categoria de despesa:");
                if (nome && nome.trim() !== "") {
                    const formData = new FormData();
                    formData.append('nome', nome.trim());
                    formData.append('csrf_token', '<?php echo $csrf_token ?? ''; ?>');

                    fetch('<?php echo BASE_URL; ?>/financeiro/addPrestacaoCategoria', {
                            method: 'POST',
                            headers: { 'X-Requested-With': 'XMLHttpRequest' },
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                const select = document.getElementById('prestacao_categoria_id');
                                const option = new Option(data.data.nome, data.data.id);
                                option.selected = true;
                                select.add(option);
                                // Dispara o evento change para atualizar a visibilidade dos campos
                                select.dispatchEvent(new Event('change'));
                            } else {
                                alert(data.message || 'Erro ao adicionar categoria.');
                            }
                        })
                        .catch(error => {
                            console.error('Erro:', error);
                            alert('Erro ao processar a solicitação.');
                        });
                }
            });
        }

        // Adicionar novo centro de custo via AJAX
        const btnAddCentroCusto = document.getElementById('btnAddCentroCusto');
        if (btnAddCentroCusto) {
            btnAddCentroCusto.addEventListener('click', function() {
                const nome = prompt("Digite o nome do novo Centro de Custo:");
                if (nome && nome.trim() !== "") {
                    const formData = new FormData();
                    formData.append('nome', nome.trim());
                    formData.append('csrf_token', '<?php echo $csrf_token ?? ''; ?>');

                    fetch('<?php echo BASE_URL; ?>/financeiro/addCentroCusto', {
                            method: 'POST',
                            headers: { 'X-Requested-With': 'XMLHttpRequest' },
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                const select = document.getElementById('centro_custo_id');
                                const option = new Option(data.data.nome, data.data.id, true, true);
                                select.add(option); // Adiciona e define como selecionado no DOM
                            } else {
                                alert(data.message || 'Erro ao adicionar centro de custo.');
                            }
                        })
                        .catch(error => {
                            console.error('Erro:', error);
                            alert('Erro ao processar a solicitação.');
                        });
                }
            });
        }

        // Lógica para mostrar campos de combustível
        const categoriaSelect = document.getElementById('prestacao_categoria_id');
        const divCombustivel = document.getElementById('campos_combustivel');

        function checkCategoriaCombustivel() {
            if (!categoriaSelect || !divCombustivel) return;
            
            const selectedIndex = categoriaSelect.selectedIndex;
            if (selectedIndex < 0) return; // Nenhuma opção selecionada

            const text = categoriaSelect.options[selectedIndex].text.toLowerCase();
            const termos = ['combustível', 'combustivel', 'abastecimento', 'gasolina', 'diesel', 'etanol'];
            
            if (termos.some(termo => text.includes(termo))) {
                divCombustivel.classList.remove('hidden');
            } else {
                divCombustivel.classList.add('hidden');
            }
        }

        if (categoriaSelect) {
            categoriaSelect.addEventListener('change', checkCategoriaCombustivel);
            // Executa imediatamente para garantir o estado inicial (ex: em edição)
            checkCategoriaCombustivel();
        }
    });
</script>