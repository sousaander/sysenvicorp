<h2 class="text-2xl font-bold mb-4 dark:text-white">
    <?php echo htmlspecialchars($pageTitle); ?>
</h2>
<p class="mb-6 text-gray-600 dark:text-gray-400">
    Preencha os detalhes para registrar uma nova movimentação ou revise os dados existentes.
</p>

<?php
$isEdit = isset($transacao) && $transacao !== null;
$actionUrl = BASE_URL . '/financeiro/salvar';

// Lógica para determinar o valor bruto original a ser exibido no formulário.
// Isso evita que o valor "pisque" na tela ao carregar uma transação paga, que salva o valor líquido.
$valorOriginalParaExibir = 0;
if ($isEdit) {
    if ($transacao['status'] === 'Pago') {
        // Se 'Pago', o 'valor' no banco é o LÍQUIDO. Precisamos recalcular o BRUTO para o formulário.
        $valorLiquido = $transacao['valor'] ?? 0;
        $juros = $transacao['juros'] ?? 0;
        $desconto = $transacao['desconto'] ?? 0;
        $issPercent = $transacao['iss_percentual'] ?? 0;

        // Fórmula: valor_liquido = (valor_bruto * (1 - iss_percent/100)) + juros - desconto
        // Isolando valor_bruto: valor_bruto = (valor_liquido - juros + desconto) / (1 - iss_percent/100)
        $baseParaCalculo = $valorLiquido - $juros + $desconto;
        $divisor = 1 - ($issPercent / 100);

        if ($divisor > 0) {
            $valorOriginalParaExibir = $baseParaCalculo / $divisor;
        } else {
            // Evita divisão por zero se ISS for 100% ou mais.
            $valorOriginalParaExibir = $baseParaCalculo;
        }
    } else {
        // Para outros status, o 'valor' no banco já é o valor bruto.
        $valorOriginalParaExibir = $transacao['valor'] ?? 0;
    }
}

// Detecta se é uma parcela de uma recorrência para oferecer atualização em lote do Centro de Custo
$isRecorrencia = false;
if ($isEdit && preg_match('/\((?:Recorrência\s)?\d+\/\d+\)$/', $transacao['descricao'])) {
    $isRecorrencia = true;
}

?>

<style>
    /* Estilos para o dropdown de pesquisa */
    .search-results-container {
        position: absolute;
        z-index: 50;
        width: 100%;
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        margin-top: 0.25rem;
    }
    .dark-theme .search-results-container {
        background: #1f2937;
        border-color: #374151;
    }
</style>

<form action="<?php echo $actionUrl; ?>" method="POST" class="max-w-7xl mx-auto space-y-6" enctype="multipart/form-data">

    <input type="hidden" name="id" value="<?php echo $isEdit ? htmlspecialchars($transacao['id']) : ''; ?>">
    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token ?? ''; ?>">
    <input type="hidden" name="contrato_parcela_id" value="<?php echo $isEdit ? htmlspecialchars($transacao['contrato_parcela_id'] ?? '') : ''; ?>">

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
        <!-- Card 1: Informações Gerais -->
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md lg:col-span-2 border border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4 border-b dark:border-gray-700 pb-2">Informações da Movimentação</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <!-- Tipo de Transação -->
                <div>
                    <label for="tipo" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tipo <span class="text-red-500">*</span></label>
                    <?php if ($isEdit && isset($transacao['tipo']) && $transacao['tipo'] === 'Transferência'): ?>
                        <select id="tipo" name="tipo_disabled" disabled class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-400 rounded-lg shadow-sm p-2 bg-gray-100">
                            <option value="Transferência" selected>Transferência</option>
                        </select>
                        <input type="hidden" name="tipo" value="Transferência">
                        <p class="mt-1 text-sm text-gray-500">Tipo fixo: Transferência. O campo é gerenciado automaticamente.</p>
                    <?php else: ?>
                        <select id="tipo" name="tipo" required class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:border-sky-500 focus:ring-sky-500 p-2">
                            <option value="">Selecione</option>
                            <option value="R" <?php echo $isEdit && $transacao['tipo'] === 'R' ? 'selected' : ''; ?>>Receita (a Receber)</option>
                            <option value="P" <?php echo $isEdit && $transacao['tipo'] === 'P' ? 'selected' : ''; ?>>Despesa (a Pagar)</option>
                        </select>
                    <?php endif; ?>
                </div>

                <!-- Classificação -->
                <div id="categoria-container">
                    <label for="classificacao_id" class="block text-sm font-medium text-gray-700 mb-1">Categoria</label>
                    <div class="flex gap-2 relative">
                        <div class="relative flex-grow">
                            <input type="text" id="search_classificacao" placeholder="Digite 3 caracteres para pesquisar..." 
                                class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:border-sky-500 focus:ring-sky-500 p-2">
                            <div id="results_classificacao" class="search-results-container hidden max-h-60 overflow-y-auto"></div>
                        </div>
                        <select id="classificacao_id" name="classificacao_id" class="hidden">
                            <option value="">Nenhuma</option>
                            <?php foreach ($classificacoes as $class): ?>
                                <option value="<?php echo $class['id']; ?>" data-tipo="<?php echo $class['tipo']; ?>" <?php echo ($isEdit && isset($transacao['classificacao_id']) && $transacao['classificacao_id'] == $class['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($class['nome']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="button" id="addClassificacaoBtn" title="Adicionar Nova Categoria" class="px-3 py-2 bg-sky-100 text-sky-700 rounded-md hover:bg-sky-200">+</button>
                    </div>
                </div>

                <!-- Centro de Custo (visível apenas para Despesas) -->
                <div class="<?php echo ($isEdit && $transacao['tipo'] !== 'P') ? 'hidden' : ''; ?>" id="centro-custo-container">
                    <label for="centro_custo_id" class="block text-sm font-medium text-gray-700 mb-1">Centro de Custo</label>
                    <div class="flex gap-2 relative">
                        <div class="relative flex-grow">
                            <input type="text" id="search_centro_custo" placeholder="Digite 3 caracteres para pesquisar..." 
                                class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:border-sky-500 focus:ring-sky-500 p-2">
                            <div id="results_centro_custo" class="search-results-container hidden max-h-60 overflow-y-auto"></div>
                        </div>
                        <select id="centro_custo_id" name="centro_custo_id" class="hidden">
                            <option value="">Nenhum</option>
                            <?php if (!empty($centrosCusto)): ?>
                                <?php foreach ($centrosCusto as $cc): 
                                    $selected = '';
                                    if ($isEdit && isset($transacao['centro_custo_id']) && $transacao['centro_custo_id'] == $cc['id']) {
                                        $selected = 'selected';
                                    } elseif (!$isEdit && count($centrosCusto) == 1) {
                                        $selected = 'selected';
                                    }
                                ?>
                                    <option value="<?php echo $cc['id']; ?>" <?php echo $selected; ?>>
                                        <?php echo htmlspecialchars($cc['nome']); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                        <button type="button" id="addCentroCustoBtn" title="Adicionar Novo Centro de Custo" class="px-3 py-2 bg-sky-100 text-sky-700 rounded-md hover:bg-sky-200">+</button>
                    </div>
                <?php if ($isRecorrencia): ?>
                    <div class="mt-2 flex items-start gap-2 p-2 bg-amber-50 dark:bg-amber-900/10 border border-amber-100 dark:border-amber-800/30 rounded-md">
                        <input type="checkbox" id="atualizar_futuras" name="atualizar_futuras" value="1" class="mt-0.5 h-4 w-4 text-amber-600 border-gray-300 rounded focus:ring-amber-500">
                        <label for="atualizar_futuras" class="text-xs text-amber-800 dark:text-amber-400 leading-tight cursor-pointer">
                            Atualizar o Centro de Custo em todas as <strong>parcelas futuras</strong> desta série?
                        </label>
                    </div>
                    <div class="mt-2 flex items-start gap-2 p-2 bg-blue-50 dark:bg-blue-900/10 border border-blue-100 dark:border-blue-800/30 rounded-md">
                        <input type="checkbox" id="atualizar_valor_futuras" name="atualizar_valor_futuras" value="1" class="mt-0.5 h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <label for="atualizar_valor_futuras" class="text-xs text-blue-800 dark:text-blue-400 leading-tight cursor-pointer">
                            Atualizar o <strong>Valor</strong> em todas as <strong>parcelas futuras</strong> desta série?
                        </label>
                    </div>
                <?php endif; ?>
                </div>

                <!-- Cliente (Visível apenas para Receitas) -->
                <div class="<?php echo ($isEdit && $transacao['tipo'] === 'R') ? '' : 'hidden'; ?>" id="cliente-container">
                    <label for="cliente_id" class="block text-sm font-medium text-gray-700 mb-1">Cliente</label>
                    <div class="flex gap-2">
                        <select id="cliente_id" name="cliente_id" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-sky-500 focus:ring-sky-500 p-2">
                            <option value="">Selecione um Cliente</option>
                            <?php if (!empty($clientes)): ?>
                                <?php foreach ($clientes as $c): ?>
                                    <option value="<?php echo $c['id']; ?>" <?php echo ($isEdit && isset($transacao['cliente_id']) && $transacao['cliente_id'] == $c['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($c['nome']); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                        <button type="button" id="btnAddCliente" class="px-3 py-2 bg-sky-100 text-sky-700 rounded-md hover:bg-sky-200" title="Cadastrar Novo Cliente">+</button>
                    </div>
                </div>

                <!-- Fornecedor (Visível apenas para Despesas) -->
                <div class="<?php echo ($isEdit && $transacao['tipo'] === 'P') ? '' : 'hidden'; ?>" id="fornecedor-container">
                    <label for="fornecedor_id" class="block text-sm font-medium text-gray-700 mb-1">Fornecedor (Cadastro)</label>
                    <div class="flex gap-2">
                        <select id="fornecedor_id" name="fornecedor_id" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-sky-500 focus:ring-sky-500 p-2">
                            <option value="">Selecione um Fornecedor</option>
                            <?php if (!empty($fornecedores)): ?>
                                <?php foreach ($fornecedores as $f): ?>
                                    <option value="<?php echo $f['id']; ?>" <?php echo ($isEdit && isset($transacao['fornecedor_id']) && $transacao['fornecedor_id'] == $f['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($f['nome']); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                        <button type="button" id="btnAddFornecedor" class="px-3 py-2 bg-sky-100 text-sky-700 rounded-md hover:bg-sky-200" title="Cadastrar Novo Fornecedor">+</button>
                    </div>
                </div>

                <!-- Descrição -->
                <div class="md:col-span-2">
                    <label for="descricao" class="block text-sm font-medium text-gray-700 mb-1">Descrição <span class="text-red-500">*</span></label>
                    <input type="text" id="descricao" name="descricao" required
                        value="<?php echo $isEdit ? htmlspecialchars($transacao['descricao']) : ''; ?>"
                        class="w-full border-gray-300 rounded-lg shadow-sm focus:border-sky-500 focus:ring-sky-500 p-2">
                </div>

                <!-- Data de Vencimento -->
                <div>
                    <label for="vencimento" class="block text-sm font-medium text-gray-700 mb-1">Data de Vencimento <span class="text-red-500">*</span></label>
                    <input type="date" id="vencimento" name="vencimento" required
                        value="<?php echo $isEdit ? htmlspecialchars($transacao['vencimento']) : date('Y-m-d'); ?>"
                        class="w-full border-gray-300 rounded-lg shadow-sm focus:border-sky-500 focus:ring-sky-500 p-2">
                </div>

                <!-- Data de Emissão -->
                <div>
                    <label for="dataEmissao" class="block text-sm font-medium text-gray-700 mb-1">Data de Emissão</label>
                    <input type="date" id="dataEmissao" name="dataEmissao"
                        value="<?php echo $isEdit ? htmlspecialchars($transacao['dataEmissao']) : date('Y-m-d'); ?>"
                        class="w-full border-gray-300 rounded-lg shadow-sm focus:border-sky-500 focus:ring-sky-500 p-2">
                </div>

                <!-- Status -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status <span class="text-red-500">*</span></label>
                    <select id="status" name="status" required class="w-full border-gray-300 rounded-lg shadow-sm focus:border-sky-500 focus:ring-sky-500 p-2">
                        <option value="Pendente" <?php echo $isEdit && $transacao['status'] === 'Pendente' ? 'selected' : ''; ?>>Pendente</option>
                        <option value="Pago" <?php echo $isEdit && $transacao['status'] === 'Pago' ? 'selected' : ''; ?>>Pago/Recebido</option>
                        <option value="Atrasado" <?php echo $isEdit && $transacao['status'] === 'Atrasado' ? 'selected' : ''; ?>>Atrasado</option>
                        <option value="Cancelado" <?php echo $isEdit && $transacao['status'] === 'Cancelado' ? 'selected' : ''; ?>>Cancelado</option>
                    </select>
                </div>

                <!-- Banco / Caixa (Seleção Padrão) -->
                <div>
                    <label for="banco_id" class="block text-sm font-medium text-gray-700 mb-1">Banco / Caixa</label>
                    <select id="banco_id" name="banco_id" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-sky-500 focus:ring-sky-500 p-2">
                        <option value="">Selecione</option>
                        <?php foreach ($bancos as $banco): ?>
                            <option value="<?php echo $banco['id']; ?>" <?php echo ($isEdit && isset($transacao['banco_id']) && $transacao['banco_id'] == $banco['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($banco['nome']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Documento Vinculado -->
                <div>
                    <label for="documentoVinculado" class="block text-sm font-medium text-gray-700 mb-1">Documento Vinculado</label>
                    <input type="text" id="documentoVinculado" name="documentoVinculado"
                        value="<?php echo $isEdit ? htmlspecialchars($transacao['documentoVinculado'] ?? '') : ''; ?>"
                        class="w-full border-gray-300 rounded-lg shadow-sm focus:border-sky-500 focus:ring-sky-500 p-2">
                </div>

                <!-- Anexo / Comprovante -->
                <div>
                    <label for="anexo" class="block text-sm font-medium text-gray-700 mb-1">Anexar Comprovante</label>
                    <input type="file" id="anexo" name="anexo" class="block w-full text-sm text-gray-500
                        file:mr-4 file:py-2 file:px-4
                        file:rounded-full file:border-0
                        file:text-sm file:font-semibold
                        file:bg-sky-50 file:text-sky-700
                        hover:file:bg-sky-100">
                    <?php if ($isEdit && !empty($transacao['documentoVinculado'])): ?>
                        <div class="mt-2 text-xs">
                            <a href="<?php echo BASE_URL . '/storage/financeiro_anexos/' . htmlspecialchars($transacao['documentoVinculado']); ?>" target="_blank" class="inline-flex items-center text-sky-600 hover:text-sky-800 hover:underline">
                                📎 Visualizar anexo atual
                            </a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Observações -->
                <div class="md:col-span-2">
                    <label for="observacoes" class="block text-sm font-medium text-gray-700 mb-1">Observações</label>
                    <textarea id="observacoes" name="observacoes" rows="3" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-sky-500 focus:ring-sky-500 p-2"><?php echo $isEdit ? htmlspecialchars($transacao['observacoes'] ?? '') : ''; ?></textarea>
                </div>

            </div>
        </div>

        <!-- Card 2: Valores e Pagamento -->
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md lg:col-span-1 border border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4 border-b dark:border-gray-700 pb-2">Efetuar Pagamento</h3>

            <div class="flex gap-4 mb-6">
                <!-- Valor Original / Previsto -->
                <div class="flex-grow">
                    <label for="valor_formatado" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Valor Bruto (R$) <span class="text-red-500">*</span></label>
                    <input type="text" id="valor_formatado" name="valor_formatado_display" required
                        value="<?php echo $isEdit ? number_format($valorOriginalParaExibir, 2, ',', '.') : ''; ?>"
                        class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:border-sky-500 focus:ring-sky-500 p-2 text-lg font-semibold"
                        placeholder="0,00"
                        inputmode="decimal">
                </div>
                <!-- Dedução ISS (Apenas Receitas) -->
                <div class="w-1/3 hidden" id="div_iss">
                    <label for="iss_percentual" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">ISS (%)</label>
                    <input type="text" id="iss_percentual" name="iss_percentual"
                        value="<?php echo $isEdit ? number_format($transacao['iss_percentual'] ?? 0, 2, ',', '.') : ''; ?>"
                        class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:border-sky-500 focus:ring-sky-500 p-2 money-mask" placeholder="0,00">
                </div>
            </div>

            <!-- Container de Pagamento (Visível apenas quando status é Pago) -->
            <div id="container_pagamento" class="hidden bg-gray-50 dark:bg-gray-700/30 p-4 rounded-lg border border-gray-200 dark:border-gray-700">
                <div class="grid grid-cols-1 gap-4">
                    <!-- Coluna 1: Data do Pagamento -->
                    <div>
                        <label for="data_pagamento" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Data do Pagamento</label>
                        <input type="date" id="data_pagamento" name="data_pagamento"
                            value="<?php echo $isEdit ? htmlspecialchars($transacao['data_pagamento'] ?? '') : ''; ?>"
                            class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:border-sky-500 focus:ring-sky-500 p-2">
                    </div>

                    <!-- Coluna 2: Juros -->
                    <div>
                        <label for="juros" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Juros (R$)</label>
                        <input type="text" id="juros" name="juros" value="<?php echo $isEdit ? number_format($transacao['juros'] ?? 0, 2, ',', '.') : ''; ?>" class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:border-sky-500 focus:ring-sky-500 p-2 money-mask" placeholder="0,00">
                    </div>

                    <!-- Coluna 3: Desconto -->
                    <div>
                        <label for="desconto" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Desconto (R$)</label>
                        <input type="text" id="desconto" name="desconto" value="<?php echo $isEdit ? number_format($transacao['desconto'] ?? 0, 2, ',', '.') : ''; ?>" class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:border-sky-500 focus:ring-sky-500 p-2 money-mask" placeholder="0,00">
                    </div>

                    <!-- Coluna 4: Valor Pago -->
                    <div>
                        <label for="valor_pago_formatado" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Valor Total Pago (R$)</label>
                        <input type="text" id="valor_pago_formatado"
                            value="<?php echo ($isEdit && $transacao['status'] === 'Pago') ? number_format($transacao['valor'], 2, ',', '.') : ''; ?>"
                            class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:border-sky-500 focus:ring-sky-500 p-2 font-bold bg-gray-50 dark:bg-gray-800/50 money-mask"
                            placeholder="0,00">
                    </div>

                    <!-- Coluna 5: Efetuado em -->
                    <div>
                        <label for="forma_pagamento" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Efetuado em</label>
                        <select id="forma_pagamento" name="forma_pagamento" class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:border-sky-500 focus:ring-sky-500 p-2">
                            <option value="">Selecione</option>
                            <?php
                            $formas = ['Pix', 'Dinheiro', 'Transferência', 'Boleto', 'Cartão de Crédito', 'Cartão de Débito', 'Cheque', 'Watsapp', 'Pagamento Digital', 'Depósito'];
                            foreach ($formas as $forma) {
                                $selected = ($isEdit && isset($transacao['forma_pagamento']) && $transacao['forma_pagamento'] == $forma) ? 'selected' : '';
                                echo "<option value=\"$forma\" $selected>$forma</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Card 3: Repetição e Parcelamento (Opcional) -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md lg:col-span-1 mt-6 border border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4 border-b dark:border-gray-700 pb-2">Repetição e Parcelamento (Opcional)</h3>

                <div class="grid grid-cols-1 gap-6 mb-6">
                    <!-- Repetir Lançamento -->
                    <div>
                        <label for="repetir" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Repetir Lançamento?</label>
                        <input type="checkbox" id="repetir" name="repetir" class="border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg shadow-sm focus:border-sky-500 focus:ring-sky-500 p-2">
                    </div>

                    <!-- Tipo de Repetição (Novo) -->
                    <div id="container_tipo_repeticao" class="hidden">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Modo de Repetição</label>
                        <div class="flex flex-col space-y-2">
                            <label class="inline-flex items-center">
                                <input type="radio" name="tipo_repeticao" value="parcelamento" checked class="text-sky-600 focus:ring-sky-500 border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Parcelar (Dividir valor total)</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" name="tipo_repeticao" value="recorrencia" class="text-sky-600 focus:ring-sky-500 border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Recorrência (Repetir valor integral)</span>
                            </label>
                        </div>
                    </div>

                    <!-- Número de Parcelas -->
                    <div>
                        <label for="parcelas" id="label_parcelas" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Número de Parcelas</label>
                        <div class="flex items-center w-max">
                            <button type="button" id="btn-minus-parcelas" class="px-3 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-l-lg hover:bg-gray-300 dark:hover:bg-gray-600 focus:outline-none border border-r-0 border-gray-300 dark:border-gray-600">-</button>
                            <input type="number" id="parcelas" name="parcelas" value="1" min="1" max="120" class="w-12 text-center border-t border-b border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:ring-0 focus:border-gray-300 p-2" readonly>
                            <button type="button" id="btn-plus-parcelas" class="px-3 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-r-lg hover:bg-gray-300 dark:hover:bg-gray-600 focus:outline-none border border-l-0 border-gray-300 dark:border-gray-600">+</button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Campo oculto real que será enviado como 'valor' -->
    <input type="hidden" id="valor_real" name="valor" value="<?php echo $isEdit ? number_format($transacao['valor'], 2, ',', '.') : ''; ?>">

    <!-- Botões de Ação -->
    <div class="flex justify-end space-x-4">
        <a href="<?php echo BASE_URL; ?>/financeiro" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg shadow-sm hover:bg-gray-200 transition">
            Cancelar
        </a>
        <button type="submit" class="px-6 py-2 text-sm font-semibold text-white bg-emerald-600 rounded-lg shadow-md hover:bg-emerald-700 transition">
            <?php echo $isEdit ? 'Salvar Alterações' : 'Incluir Pagamento'; ?>
        </button>
    </div>

</form>

<!-- Modal de Cadastro Rápido (Cliente/Fornecedor) -->
<div id="quickRegisterModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="relative inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form id="quickRegisterForm">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="quickRegisterTitle">Novo Cadastro</h3>
                            <div class="mt-4 space-y-4">
                                <div>
                                    <label for="qr_nome" class="block text-sm font-medium text-gray-700">Nome / Razão Social <span class="text-red-500">*</span></label>
                                    <input type="text" id="qr_nome" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-sky-500 focus:border-sky-500 sm:text-sm p-2">
                                </div>
                                <div>
                                    <label for="qr_email" class="block text-sm font-medium text-gray-700">E-mail</label>
                                    <input type="email" id="qr_email" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-sky-500 focus:border-sky-500 sm:text-sm p-2">
                                </div>
                                <div>
                                    <label for="qr_telefone" class="block text-sm font-medium text-gray-700">Telefone</label>
                                    <input type="text" id="qr_telefone" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-sky-500 focus:border-sky-500 sm:text-sm p-2">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-sky-600 text-base font-medium text-white hover:bg-sky-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">Salvar</button>
                    <button type="button" id="btnCancelQuickRegister" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Variáveis para persistir a seleção do cliente/fornecedor durante a sessão do formulário
    let lastClienteId = "<?php echo $isEdit ? ($transacao['cliente_id'] ?? '') : (isset($_GET['cliente_id']) ? $_GET['cliente_id'] : ''); ?>";
    let lastFornecedorId = "<?php echo $isEdit ? ($transacao['fornecedor_id'] ?? '') : (isset($_GET['fornecedor_id']) ? $_GET['fornecedor_id'] : ''); ?>";

    // Função para pré-selecionar campos com base na URL
    function preSelecionarCampos() {
        const urlParams = new URLSearchParams(window.location.search);
        
        // Tipo
        const tipoFromUrl = urlParams.get('tipo');
        const tipoSelect = document.getElementById('tipo');
        if (tipoFromUrl && tipoSelect) {
            tipoSelect.value = tipoFromUrl;
            tipoSelect.dispatchEvent(new Event('change'));
        }
        
        // Cliente
        const clienteFromUrl = urlParams.get('cliente_id');
        const clienteSelect = document.getElementById('cliente_id');
        if (clienteFromUrl && clienteSelect) {
            clienteSelect.value = clienteFromUrl;
            lastClienteId = clienteFromUrl;
            clienteSelect.dispatchEvent(new Event('change'));
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const tipoSelect = document.getElementById('tipo');
        const classificacaoSelect = document.getElementById('classificacao_id');
        const allClassificacoes = Array.from(classificacaoSelect.options);
        const addBtn = document.getElementById('addClassificacaoBtn');
        const statusSelect = document.getElementById('status');
        const dataPagamentoInput = document.getElementById('data_pagamento');
        const centroCustoContainer = document.getElementById('centro-custo-container');
        const centroCustoSelect = document.getElementById('centro_custo_id');
        const clienteContainer = document.getElementById('cliente-container');
        let lastCentroCustoValue = centroCustoSelect ? centroCustoSelect.value : ''; // Armazena o último valor selecionado

        const clienteSelect = document.getElementById('cliente_id');
        const fornecedorSelect = document.getElementById('fornecedor_id');

        if (clienteSelect) clienteSelect.addEventListener('change', () => { if(clienteSelect.value) lastClienteId = clienteSelect.value; });
        if (fornecedorSelect) fornecedorSelect.addEventListener('change', () => { if(fornecedorSelect.value) lastFornecedorId = fornecedorSelect.value; });

        // Sincroniza a variável de controle sempre que a seleção mudar manualmente
        if (centroCustoSelect) {
            centroCustoSelect.addEventListener('change', () => {
                if (tipoSelect.value === 'P') {
                    lastCentroCustoValue = centroCustoSelect.value;
                }
            });
        }
        const fornecedorContainer = document.getElementById('fornecedor-container');

        // Campos de Valor
        const valorFormatadoField = document.getElementById('valor_formatado');
        const valorPagoFormatadoField = document.getElementById('valor_pago_formatado');
        const valorRealField = document.getElementById('valor_real');
        const issInput = document.getElementById('iss_percentual');
        const divIss = document.getElementById('div_iss');

        // Card e novos campos
        const containerPagamento = document.getElementById('container_pagamento');
        const jurosInput = document.getElementById('juros');
        const descontoInput = document.getElementById('desconto');

        const dataVencimentoInput = document.getElementById('vencimento');

        // Função auxiliar de formatação de moeda
        const formatCurrency = (value) => {
            let digits = value.replace(/\D/g, '');
            if (digits === '') return '';
            let num = (Number(digits) / 100);
            return num.toLocaleString('pt-BR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        };

        // Função para parsear valor pt-BR para float
        const parseCurrency = (value) => {
            if (!value) return 0;
            return parseFloat(value.replace(/\./g, '').replace(',', '.')) || 0;
        };

        // Aplica máscara e eventos a um input
        const applyMoneyMask = (input) => {
            input.addEventListener('input', (e) => {
                e.target.value = formatCurrency(e.target.value);
                // O cálculo e a atualização do valor real são chamados em eventos específicos
            });
        };

        if (valorFormatadoField) applyMoneyMask(valorFormatadoField);
        if (valorPagoFormatadoField) applyMoneyMask(valorPagoFormatadoField);
        if (jurosInput) applyMoneyMask(jurosInput);
        if (descontoInput) applyMoneyMask(descontoInput);
        if (issInput) applyMoneyMask(issInput);

        // Sincroniza o valor real (hidden) com o campo visível correto
        function updateValorReal() {
            const status = statusSelect.value;
            if (status === 'Pago') {
                // Se pago, o valor real vem do campo "Valor Pago"
                valorRealField.value = valorPagoFormatadoField.value;
            } else {
                // Se pendente, o valor real vem do campo "Valor Original"
                valorRealField.value = valorFormatadoField.value;
            }
        }

        // Lógica de visibilidade do Card
        function togglePaymentCard() {
            const status = statusSelect.value;
            if (status === 'Pago') {
                containerPagamento.classList.remove('hidden');
                if (!dataPagamentoInput.value) {
                    dataPagamentoInput.value = new Date().toISOString().split('T')[0];
                }
                calculateTotalPago(); // Calcula o total ao mudar para 'Pago'
            } else {
                containerPagamento.classList.add('hidden');
                dataPagamentoInput.value = '';
            }
            updateValorReal();
        }

        // Cálculo automático de Valor Pago
        function calculateTotalPago() {
            const baseVal = parseCurrency(valorFormatadoField.value);
            const juros = parseCurrency(jurosInput.value);
            const desconto = parseCurrency(descontoInput.value);

            let issVal = 0;
            const issPercent = parseCurrency(issInput.value);
            if (issPercent > 0) {
                issVal = baseVal * (issPercent / 100);
            }

            const total = baseVal + juros - desconto - issVal;

            if (total >= 0) {
                const totalFormatted = total.toLocaleString('pt-BR', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
                valorPagoFormatadoField.value = totalFormatted;
                updateValorReal();
            }
        }

        // Event Listeners
        statusSelect.addEventListener('change', togglePaymentCard);
        jurosInput.addEventListener('input', calculateTotalPago);
        descontoInput.addEventListener('input', calculateTotalPago);
        issInput.addEventListener('input', calculateTotalPago);
        valorFormatadoField.addEventListener('input', calculateTotalPago);
        valorPagoFormatadoField.addEventListener('input', updateValorReal); // Se o usuário editar manualmente o valor pago

        // Inicialização ao carregar a página
        function initializeForm() {
            // Os valores iniciais agora são definidos corretamente pelo PHP no atributo 'value' dos inputs.
            // Apenas precisamos garantir que os cálculos e a visibilidade estejam corretos no carregamento da página.
            calculateTotalPago(); // Calcula o valor pago com base nos valores iniciais
            togglePaymentCard();
            updateValorReal(); // Garante que o valor real oculto seja o correto no carregamento
            toggleIssField();
        }

        initializeForm();

        // --- LÓGICA DE PESQUISA NOS CAMPOS (SENIOR COMPONENT) ---
        function initSearchableSelect(inputId, resultsId, selectId, ajaxUrl, extraParamsFn = null) {
            const input = document.getElementById(inputId);
            const results = document.getElementById(resultsId);
            const select = document.getElementById(selectId);

            // Inicializa valor no input se for edição
            if (select.value && select.selectedIndex >= 0) {
                input.value = select.options[select.selectedIndex].text;
            }

            let currentFocus = -1;
            let debounceTimer;
            input.addEventListener('input', function(e) {
                clearTimeout(debounceTimer);
                const query = this.value.toLowerCase().trim();
                results.innerHTML = '';
                results.classList.add('hidden');
                currentFocus = -1;

                if (query.length < 3) {
                    return;
                }

                // Adiciona um spinner de carregamento
                results.classList.remove('hidden');
                results.innerHTML = '<div class="px-4 py-2 text-gray-500 italic text-sm flex items-center"><svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-sky-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Carregando...</div>';

                debounceTimer = setTimeout(() => {
                    let params = `query=${encodeURIComponent(query)}`;
                    if (extraParamsFn) {
                        params += `&${extraParamsFn()}`;
                    }

                    fetch(`${ajaxUrl}?${params}`)
                        .then(response => response.json())
                        .then(data => {
                            results.innerHTML = ''; // Limpa o spinner
                            if (data.success && data.data.length > 0) {
                                data.data.forEach(item => {
                                    const div = document.createElement('div');
                                    div.className = 'px-4 py-2 hover:bg-sky-100 dark:hover:bg-sky-900 cursor-pointer text-sm dark:text-white';
                                    div.textContent = item.nome;
                                    div.setAttribute('data-value', item.id);
                                    if (item.tipo) div.setAttribute('data-tipo', item.tipo); // Para categorias

                                    div.addEventListener('click', () => {
                                        input.value = item.nome;
                                        select.value = item.id;

                                        // Adiciona a opção ao select oculto se não existir
                                        if (!select.querySelector(`option[value="${item.id}"]`)) {
                                            const newOption = new Option(item.nome, item.id);
                                            if (item.tipo) newOption.setAttribute('data-tipo', item.tipo);
                                            select.add(newOption);
                                        }
                                        select.dispatchEvent(new Event('change')); // Dispara evento de mudança
                                        results.classList.add('hidden');
                                    });
                                    results.appendChild(div);
                                });
                                results.classList.remove('hidden');
                            } else {
                                const div = document.createElement('div');
                                div.className = 'px-4 py-2 text-gray-500 italic text-sm';
                                div.textContent = data.message || 'Nenhum resultado encontrado';
                                results.appendChild(div);
                                results.classList.remove('hidden');
                            }
                        });
                }, 300); // Debounce de 300ms
            });

            // Navegação por teclado para acessibilidade e velocidade
            input.addEventListener('keydown', function(e) {
                let x = results.getElementsByTagName("div");
                if (e.keyCode == 40) { // Tecla para baixo
                    currentFocus++;
                    addActive(x);
                } else if (e.keyCode == 38) { // Tecla para cima
                    currentFocus--;
                    addActive(x);
                } else if (e.keyCode == 13) { // Tecla Enter
                    if (currentFocus > -1) {
                        e.preventDefault();
                        if (x) x[currentFocus].click();
                    }
                }
            });

            function addActive(x) {
                if (!x || x.length === 0) return false;
                removeActive(x);
                if (currentFocus >= x.length) currentFocus = 0;
                if (currentFocus < 0) currentFocus = (x.length - 1);
                x[currentFocus].classList.add("bg-sky-100", "dark:bg-sky-900");
                x[currentFocus].scrollIntoView({ block: 'nearest' });
            }

            function removeActive(x) {
                for (let i = 0; i < x.length; i++) {
                    x[i].classList.remove("bg-sky-100", "dark:bg-sky-900");
                }
            }

            // Fecha a lista ao clicar fora
            document.addEventListener('click', (e) => {
                if (!input.contains(e.target) && !results.contains(e.target)) {
                    results.classList.add('hidden');
                    // Se o campo for limpo e não houver seleção, limpa o select
                    if (input.value.trim() === '' && select.value !== '') {
                        select.value = '';
                        select.dispatchEvent(new Event('change'));
                    }
                }
            });

            // Listener para quando o select mudar via botões de "+" (Cadastro Rápido)
            const observer = new MutationObserver(() => {
                if (select.value && select.selectedIndex >= 0) {
                    input.value = select.options[select.selectedIndex].text;
                }
            });
            observer.observe(select, { childList: true, subtree: true, attributes: true });
        }

        // Inicializa os componentes com as URLs AJAX
        initSearchableSelect(
            'search_classificacao',
            'results_classificacao',
            'classificacao_id',
            '<?php echo BASE_URL; ?>/financeiro/searchClassificacoesAjax',
            () => `tipo=${document.getElementById('tipo').value}` // Parâmetro extra para filtrar por tipo
        );
        initSearchableSelect('search_centro_custo', 'results_centro_custo', 'centro_custo_id',
            '<?php echo BASE_URL; ?>/financeiro/searchCentrosCustoAjax'
        );

        // Sincroniza a limpeza do campo de pesquisa quando o tipo muda
        tipoSelect.addEventListener('change', () => {
            document.getElementById('search_classificacao').value = '';
            document.getElementById('search_centro_custo').value = '';
            // Se for Receita, garante que o centro de custo suma
            if (tipoSelect.value === 'R') {
                lastCentroCustoValue = '';
                document.getElementById('centro_custo_id').value = '';
            }
        });


        function filtrarClassificacoes() {
            const tipoSelecionado = tipoSelect.value;
            const valorAtual = classificacaoSelect.value;

            classificacaoSelect.innerHTML = '';

            allClassificacoes.forEach(option => {
                const optionTipo = option.getAttribute('data-tipo');
                if (option.value === "" || optionTipo === null || optionTipo === tipoSelecionado) {
                    classificacaoSelect.add(option.cloneNode(true));
                }
            });

            classificacaoSelect.value = valorAtual;
        }

        function toggleCentroCusto() {
            if (tipoSelect.value === 'P') {
                centroCustoContainer.classList.remove('hidden');
                centroCustoSelect.value = lastCentroCustoValue; // Restaura o valor
            } else {
                centroCustoContainer.classList.add('hidden');
                lastCentroCustoValue = centroCustoSelect.value; // Salva o valor antes de ocultar
                centroCustoSelect.value = ''; // Limpa o valor quando oculto
            }
        }

        function toggleIssField() {
            if (tipoSelect.value === 'R') {
                divIss.classList.remove('hidden');
            } else {
                divIss.classList.add('hidden');
                issInput.value = ''; // Limpa se esconder
                calculateTotalPago();
            }
        }

        function toggleEntidades() {
            // Obtém valor inclusive se o select estiver disabled (usando hidden input se necessário)
            const tipoValue = tipoSelect.value || document.getElementsByName('tipo')[0]?.value;
            const clienteSelect = document.getElementById('cliente_id');
            const fornecedorSelect = document.getElementById('fornecedor_id');

            if (tipoValue === 'P') {
                fornecedorContainer.classList.remove('hidden');
                clienteContainer.classList.add('hidden');
                if (clienteSelect) {
                    clienteSelect.required = false;
                }
                if (fornecedorSelect) {
                    fornecedorSelect.required = true;
                }
            } else if (tipoValue === 'R') {
                clienteContainer.classList.remove('hidden');
                fornecedorContainer.classList.add('hidden');
                if (fornecedorSelect) {
                    fornecedorSelect.required = false;
                }
                if (clienteSelect) {
                    clienteSelect.required = true;
                }
            } else {
                clienteContainer.classList.add('hidden');
                fornecedorContainer.classList.add('hidden');
                if (clienteSelect) clienteSelect.required = false;
                if (fornecedorSelect) fornecedorSelect.required = false;
            }
        }

        tipoSelect.addEventListener('change', filtrarClassificacoes);
        tipoSelect.addEventListener('change', () => {
            toggleCentroCusto();
            toggleIssField();
            toggleEntidades();
        });
        toggleCentroCusto();
        filtrarClassificacoes();
        toggleEntidades(); // Inicializa o estado dos campos de Cliente/Fornecedor

        // Executa a pré-seleção APÓS configurar os eventos acima
        preSelecionarCampos();

        addBtn.addEventListener('click', function() {
            const tipoSelecionado = tipoSelect.value;
            if (!tipoSelecionado) {
                alert('Por favor, selecione primeiro o Tipo de Transação (Receita ou Despesa).');
                return;
            }

            const nomeNovaClassificacao = prompt('Digite o nome da nova categoria:');
            if (nomeNovaClassificacao && nomeNovaClassificacao.trim() !== '') {
                const formData = new FormData();
                formData.append('nome', nomeNovaClassificacao.trim());
                formData.append('tipo', tipoSelecionado);

                fetch('<?php echo BASE_URL; ?>/financeiro/addClassificacao', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const newOption = new Option(data.data.nome, data.data.id, true, true);
                            newOption.setAttribute('data-tipo', tipoSelecionado);
                            classificacaoSelect.add(newOption);
                            allClassificacoes.push(newOption);
                        } else {
                            alert('Erro: ' + data.message);
                        }
                    })
                    .catch(error => console.error('Erro na requisição:', error));
            }
        });

        const addCentroCustoBtn = document.getElementById('addCentroCustoBtn');
        if (addCentroCustoBtn) {
            addCentroCustoBtn.addEventListener('click', function() {
                const nomeNovoCentroCusto = prompt('Digite o nome do novo Centro de Custo:');
                if (nomeNovoCentroCusto && nomeNovoCentroCusto.trim() !== '') {
                    const formData = new FormData();
                    formData.append('nome', nomeNovoCentroCusto.trim());

                    fetch('<?php echo BASE_URL; ?>/financeiro/addCentroCusto', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                const newOption = new Option(data.data.nome, data.data.id, true, true);
                                centroCustoSelect.add(newOption);
                                lastCentroCustoValue = data.data.id; // Sincroniza o valor para persistir em trocas de tipo
                            } else {
                                alert('Erro: ' + data.message);
                            }
                        })
                        .catch(error => console.error('Erro na requisição:', error));
                }
            });
        }

        // --- Lógica de Cadastro Rápido (Modal) ---
        const quickRegisterModal = document.getElementById('quickRegisterModal');
        const quickRegisterForm = document.getElementById('quickRegisterForm');
        const quickRegisterTitle = document.getElementById('quickRegisterTitle');
        const btnCancelQuickRegister = document.getElementById('btnCancelQuickRegister');
        let quickRegisterMode = ''; // 'cliente' ou 'fornecedor'

        function openQuickRegister(mode) {
            quickRegisterMode = mode;
            quickRegisterTitle.textContent = mode === 'cliente' ? 'Novo Cliente' : 'Novo Fornecedor';
            document.getElementById('qr_nome').value = '';
            document.getElementById('qr_email').value = '';
            document.getElementById('qr_telefone').value = '';
            quickRegisterModal.classList.remove('hidden');
        }

        function closeQuickRegister() {
            quickRegisterModal.classList.add('hidden');
        }

        if (btnCancelQuickRegister) {
            btnCancelQuickRegister.addEventListener('click', closeQuickRegister);
        }

        const btnAddCliente = document.getElementById('btnAddCliente');
        if (btnAddCliente) {
            btnAddCliente.addEventListener('click', () => openQuickRegister('cliente'));
        }

        const btnAddFornecedor = document.getElementById('btnAddFornecedor');
        if (btnAddFornecedor) {
            btnAddFornecedor.addEventListener('click', () => openQuickRegister('fornecedor'));
        }

        if (quickRegisterForm) {
            quickRegisterForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const nome = document.getElementById('qr_nome').value;
                const email = document.getElementById('qr_email').value;
                const telefone = document.getElementById('qr_telefone').value;

                const formData = new FormData();
                formData.append('nome', nome);
                formData.append('csrf_token', '<?php echo $csrf_token ?? ''; ?>');
                formData.append('status', 'Ativo');

                let url = '';
                if (quickRegisterMode === 'cliente') {
                    url = '<?php echo BASE_URL; ?>/clientes/salvar';
                    if (email) formData.append('contatos[principal][email]', email);
                    if (telefone) formData.append('contatos[principal][telefone]', telefone);
                } else {
                    url = '<?php echo BASE_URL; ?>/fornecedores/salvar';
                    formData.append('tipo_pessoa', 'Juridica'); // Padrão razoável para fornecedor
                    if (email) formData.append('contato[email]', email);
                    if (telefone) formData.append('contato[telefone]', telefone);
                }

                fetch(url, {
                        method: 'POST',
                        body: formData,
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const targetSelectId = quickRegisterMode === 'cliente' ? 'cliente_id' : 'fornecedor_id';
                            const select = document.getElementById(targetSelectId);
                            const option = new Option(data.data.nome, data.data.id, true, true);
                            select.add(option);
                            closeQuickRegister();
                        } else {
                            alert(data.message || 'Erro ao salvar.');
                        }
                    })
                    .catch(error => {
                        console.error('Erro:', error);
                        alert('Erro ao processar a solicitação.');
                    });
            });
        }

        // Lógica para mostrar/ocultar opções de repetição
        const repetirCheckbox = document.getElementById('repetir');
        const containerTipoRepeticao = document.getElementById('container_tipo_repeticao');
        const parcelasSelect = document.getElementById('parcelas');
        const labelParcelas = document.getElementById('label_parcelas');

        function validateRecorrencia() {
            const recorrenciaRadio = document.querySelector('input[name="tipo_repeticao"][value="recorrencia"]');
            const parcelamentoRadio = document.querySelector('input[name="tipo_repeticao"][value="parcelamento"]');

            if (parcelasSelect && recorrenciaRadio && parcelamentoRadio) {
                if (parseInt(parcelasSelect.value) === 1) {
                    recorrenciaRadio.disabled = true;
                    recorrenciaRadio.parentElement.classList.add('opacity-50', 'cursor-not-allowed');
                    if (recorrenciaRadio.checked) {
                        parcelamentoRadio.checked = true;
                    }
                } else {
                    recorrenciaRadio.disabled = false;
                    recorrenciaRadio.parentElement.classList.remove('opacity-50', 'cursor-not-allowed');
                }
                updateLabelParcelas(); // Atualiza o texto do label conforme a seleção
            }
        }

        function updateLabelParcelas() {
            const tipoRepeticao = document.querySelector('input[name="tipo_repeticao"]:checked');
            if (tipoRepeticao && labelParcelas) {
                if (tipoRepeticao.value === 'recorrencia') {
                    labelParcelas.textContent = 'Quantidade de Meses (Repetições)';
                } else {
                    labelParcelas.textContent = 'Número de Parcelas';
                }
            }
        }

        function toggleRepeticaoOptions() {
            if (repetirCheckbox.checked) {
                containerTipoRepeticao.classList.remove('hidden');
                validateRecorrencia();
                updateLabelParcelas();
            } else {
                containerTipoRepeticao.classList.add('hidden');
            }
        }

        if (repetirCheckbox) {
            repetirCheckbox.addEventListener('change', toggleRepeticaoOptions);
            toggleRepeticaoOptions(); // Inicializa o estado correto
        }

        if (parcelasSelect) {
            parcelasSelect.addEventListener('change', validateRecorrencia);
        }

        const radiosTipoRepeticao = document.querySelectorAll('input[name="tipo_repeticao"]');
        if (radiosTipoRepeticao) {
            radiosTipoRepeticao.forEach(radio => {
                radio.addEventListener('change', updateLabelParcelas);
            });
        }

        // Controle de botões + e - para parcelas
        const btnMinus = document.getElementById('btn-minus-parcelas');
        const btnPlus = document.getElementById('btn-plus-parcelas');

        if (btnMinus && btnPlus && parcelasSelect) {
            btnMinus.addEventListener('click', () => {
                let val = parseInt(parcelasSelect.value) || 1;
                if (val > 1) {
                    parcelasSelect.value = val - 1;
                    validateRecorrencia();
                }
            });
            btnPlus.addEventListener('click', () => {
                let val = parseInt(parcelasSelect.value) || 1;
                parcelasSelect.value = val + 1;
                validateRecorrencia();
            });
        }

        // --- PREVENÇÃO DE DUPLO CLIQUE COM TRAVA DE 5 SEGUNDOS ---
        const financeForm = document.querySelector('form[action*="financeiro/salvar"]');
        if (financeForm) {
            financeForm.addEventListener('submit', function(e) {
                // Só desabilita se o formulário for válido (passar nas validações HTML5)
                if (financeForm.checkValidity()) {
                    const submitBtn = financeForm.querySelector('button[type="submit"]');
                    if (submitBtn && !submitBtn.disabled) {
                        const originalHtml = submitBtn.innerHTML;

                        // Pequeno delay para garantir que o envio comece antes de desabilitar o botão
                        setTimeout(() => {
                            submitBtn.disabled = true;
                            submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
                            submitBtn.innerHTML = '<svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Gerando Parcelas...';
                        }, 50);

                        // Reabilita após 30 segundos se a página não redirecionar (timeout de segurança)
                        setTimeout(() => {
                            if (submitBtn.disabled) {
                                submitBtn.disabled = false;
                                submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                                submitBtn.innerHTML = originalHtml;
                            }
                        }, 30000);
                    }
                }
            });
        }

    });
</script>
<!--
[PROMPT_SUGGESTION]Como implementar a lógica de repetição e parcelamento no método salvar do FinanceiroController?[/PROMPT_SUGGESTION]
[PROMPT_SUGGESTION]Como adicionar validações para garantir que os campos de repetição e parcelamento sejam preenchidos corretamente?[/PROMPT_SUGGESTION]
-->
