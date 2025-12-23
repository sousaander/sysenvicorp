<h2 class="text-2xl font-bold mb-4">
    <?php echo htmlspecialchars($pageTitle); ?>
</h2>
<p class="mb-6 text-gray-600">
    Preencha os detalhes para registrar uma nova movimentação ou revise os dados existentes.
</p>

<?php
$isEdit = isset($transacao) && $transacao !== null;
$actionUrl = BASE_URL . '/financeiro/salvar';

// Lógica para determinar o valor original a ser exibido no formulário.
// Isso evita que o valor "pisque" na tela ao carregar uma transação paga.
$valorOriginalParaExibir = 0;
if ($isEdit) {
    // Se estiver editando uma transação 'Paga', o campo 'valor' no banco é o valor final.
    // Precisamos recalcular o valor original para exibição.
    if ($transacao['status'] === 'Pago') {
        $valorOriginalParaExibir = ($transacao['valor'] ?? 0) - ($transacao['juros'] ?? 0) + ($transacao['desconto'] ?? 0);
    } else {
        // Para status 'Pendente', 'Atrasado', etc., o campo 'valor' já é o valor original.
        $valorOriginalParaExibir = $transacao['valor'] ?? 0;
    }
}

?>

<form action="<?php echo $actionUrl; ?>" method="POST" class="max-w-7xl mx-auto space-y-6" enctype="multipart/form-data">

    <input type="hidden" name="id" value="<?php echo $isEdit ? htmlspecialchars($transacao['id']) : ''; ?>">

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
        <!-- Card 1: Informações Gerais -->
        <div class="bg-white p-6 rounded-lg shadow-md lg:col-span-2">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Informações da Movimentação</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <!-- Tipo de Transação -->
                <div>
                    <label for="tipo" class="block text-sm font-medium text-gray-700 mb-1">Tipo <span class="text-red-500">*</span></label>
                    <?php if ($isEdit && isset($transacao['tipo']) && $transacao['tipo'] === 'Transferência'): ?>
                        <select id="tipo" name="tipo_disabled" disabled class="w-full border-gray-300 rounded-lg shadow-sm p-2 bg-gray-100">
                            <option value="Transferência" selected>Transferência</option>
                        </select>
                        <input type="hidden" name="tipo" value="Transferência">
                        <p class="mt-1 text-sm text-gray-500">Tipo fixo: Transferência. O campo é gerenciado automaticamente.</p>
                    <?php else: ?>
                        <select id="tipo" name="tipo" required class="w-full border-gray-300 rounded-lg shadow-sm focus:border-sky-500 focus:ring-sky-500 p-2">
                            <option value="">Selecione</option>
                            <option value="R" <?php echo $isEdit && $transacao['tipo'] === 'R' ? 'selected' : ''; ?>>Receita (a Receber)</option>
                            <option value="P" <?php echo $isEdit && $transacao['tipo'] === 'P' ? 'selected' : ''; ?>>Despesa (a Pagar)</option>
                        </select>
                    <?php endif; ?>
                </div>

                <!-- Classificação -->
                <div class="flex items-end space-x-2" id="categoria-container">
                    <div class="flex-grow">
                        <label for="classificacao_id" class="block text-sm font-medium text-gray-700 mb-1">Categoria</label>
                        <select id="classificacao_id" name="classificacao_id" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-sky-500 focus:ring-sky-500 p-2">
                            <option value="">Nenhuma</option>
                            <?php foreach ($classificacoes as $class): ?>
                                <option value="<?php echo $class['id']; ?>" data-tipo="<?php echo $class['tipo']; ?>" <?php echo ($isEdit && isset($transacao['classificacao_id']) && $transacao['classificacao_id'] == $class['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($class['nome']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="button" id="addClassificacaoBtn" title="Adicionar Nova Categoria"
                        class="h-10 w-10 flex-shrink-0 flex items-center justify-center bg-sky-500 text-white rounded-lg shadow-sm hover:bg-sky-600 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>

                <!-- Centro de Custo (visível apenas para Despesas) -->
                <div class="flex items-end space-x-2 <?php echo ($isEdit && $transacao['tipo'] !== 'P') ? 'hidden' : ''; ?>" id="centro-custo-container">
                    <div class="flex-grow">
                        <label for="centro_custo_id" class="block text-sm font-medium text-gray-700 mb-1">Centro de Custo</label>
                        <select id="centro_custo_id" name="centro_custo_id" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-sky-500 focus:ring-sky-500 p-2">
                            <option value="">Nenhum</option>
                            <?php if (!empty($centrosCusto)): ?>
                                <?php foreach ($centrosCusto as $cc): ?>
                                    <option value="<?php echo $cc['id']; ?>" <?php echo ($isEdit && isset($transacao['centro_custo_id']) && $transacao['centro_custo_id'] == $cc['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cc['nome']); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <button type="button" id="addCentroCustoBtn" title="Adicionar Novo Centro de Custo" class="h-10 w-10 flex-shrink-0 flex items-center justify-center bg-sky-500 text-white rounded-lg shadow-sm hover:bg-sky-600 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg>
                    </button>
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
                    <?php if ($isEdit && !empty($transacao['anexo'])): ?>
                        <div class="mt-2 text-xs">
                            <a href="<?php echo BASE_URL . '/uploads/' . htmlspecialchars($transacao['anexo']); ?>" target="_blank" class="inline-flex items-center text-sky-600 hover:text-sky-800 hover:underline">
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
        <div class="bg-white p-6 rounded-lg shadow-md lg:col-span-1">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Efetuar Pagamento</h3>

            <div class="grid grid-cols-1 gap-6 mb-6">
                <!-- Valor Original / Previsto -->
                <div>
                    <label for="valor_formatado" class="block text-sm font-medium text-gray-700 mb-1">Valor Pago (R$) <span class="text-red-500">*</span></label>
                    <input type="text" id="valor_formatado" name="valor_formatado_display" required
                        value="<?php echo $isEdit ? number_format($valorOriginalParaExibir, 2, ',', '.') : ''; ?>"
                        class="w-full border-gray-300 rounded-lg shadow-sm focus:border-sky-500 focus:ring-sky-500 p-2 text-lg font-semibold"
                        placeholder="0,00"
                        inputmode="decimal">
                </div>
            </div>

            <!-- Container de Pagamento (Visível apenas quando status é Pago) -->
            <div id="container_pagamento" class="hidden bg-gray-50 p-4 rounded-lg border border-gray-200">
                <div class="grid grid-cols-1 gap-4">
                    <!-- Coluna 1: Data do Pagamento -->
                    <div>
                        <label for="data_pagamento" class="block text-sm font-medium text-gray-700 mb-1">Data do Pagamento</label>
                        <input type="date" id="data_pagamento" name="data_pagamento"
                            value="<?php echo $isEdit ? htmlspecialchars($transacao['data_pagamento'] ?? '') : ''; ?>"
                            class="w-full border-gray-300 rounded-lg shadow-sm focus:border-sky-500 focus:ring-sky-500 p-2">
                    </div>

                    <!-- Coluna 2: Juros -->
                    <div>
                        <label for="juros" class="block text-sm font-medium text-gray-700 mb-1">Juros (R$)</label>
                        <input type="text" id="juros" name="juros" value="<?php echo $isEdit ? number_format($transacao['juros'] ?? 0, 2, ',', '.') : ''; ?>" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-sky-500 focus:ring-sky-500 p-2 money-mask" placeholder="0,00">
                    </div>

                    <!-- Coluna 3: Desconto -->
                    <div>
                        <label for="desconto" class="block text-sm font-medium text-gray-700 mb-1">Desconto (R$)</label>
                        <input type="text" id="desconto" name="desconto" value="<?php echo $isEdit ? number_format($transacao['desconto'] ?? 0, 2, ',', '.') : ''; ?>" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-sky-500 focus:ring-sky-500 p-2 money-mask" placeholder="0,00">
                    </div>

                    <!-- Coluna 4: Valor Pago -->
                    <div>
                        <label for="valor_pago_formatado" class="block text-sm font-medium text-gray-700 mb-1">Valor Total Pago (R$)</label>
                        <input type="text" id="valor_pago_formatado"
                            value="<?php echo ($isEdit && $transacao['status'] === 'Pago') ? number_format($transacao['valor'], 2, ',', '.') : ''; ?>"
                            class="w-full border-gray-300 rounded-lg shadow-sm focus:border-sky-500 focus:ring-sky-500 p-2 font-bold text-gray-900 bg-gray-50 money-mask"
                            placeholder="0,00">
                    </div>

                    <!-- Coluna 5: Efetuado em -->
                    <div>
                        <label for="forma_pagamento" class="block text-sm font-medium text-gray-700 mb-1">Efetuado em</label>
                        <select id="forma_pagamento" name="forma_pagamento" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-sky-500 focus:ring-sky-500 p-2">
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

<script>
    // Função para pré-selecionar o tipo de transação com base na URL
    function preSelecionarTipo() {
        const urlParams = new URLSearchParams(window.location.search);
        const tipoFromUrl = urlParams.get('tipo');
        if (tipoFromUrl) {
            document.getElementById('tipo').value = tipoFromUrl;
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

        // Campos de Valor
        const valorFormatadoField = document.getElementById('valor_formatado');
        const valorPagoFormatadoField = document.getElementById('valor_pago_formatado');
        const valorRealField = document.getElementById('valor_real');

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

            const total = baseVal + juros - desconto;

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
        valorFormatadoField.addEventListener('input', calculateTotalPago);
        valorPagoFormatadoField.addEventListener('input', updateValorReal); // Se o usuário editar manualmente o valor pago

        // Inicialização ao carregar a página
        function initializeForm() {
            // Os valores iniciais agora são definidos corretamente pelo PHP no atributo 'value' dos inputs.
            // Apenas precisamos garantir que os cálculos e a visibilidade estejam corretos no carregamento da página.
            calculateTotalPago(); // Calcula o valor pago com base nos valores iniciais
            togglePaymentCard();
            updateValorReal(); // Garante que o valor real oculto seja o correto no carregamento
        }

        initializeForm();


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
            } else {
                centroCustoContainer.classList.add('hidden');
                centroCustoSelect.value = '';
            }
        }

        preSelecionarTipo();
        tipoSelect.addEventListener('change', filtrarClassificacoes);
        tipoSelect.addEventListener('change', toggleCentroCusto);
        toggleCentroCusto();
        filtrarClassificacoes();

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
                            } else {
                                alert('Erro: ' + data.message);
                            }
                        })
                        .catch(error => console.error('Erro na requisição:', error));
                }
            });
        }

    });
</script>