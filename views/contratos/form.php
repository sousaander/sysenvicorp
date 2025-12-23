<?php
// Define se é modo de edição e extrai os dados do contrato, se houver
$isEdit = isset($contrato) && !empty($contrato);
$contratoData = $isEdit ? $contrato : null;

// Define a URL de ação do formulário
$actionUrl = BASE_URL . '/contratos/salvar';
?>

<form action="<?php echo $actionUrl; ?>" method="POST" enctype="multipart/form-data">
    <?php if ($isEdit) : ?>
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($contratoData['id']); ?>">
    <?php endif; ?>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Tipo de Contrato -->
        <div class="md:col-span-1">
            <label for="tipo" class="block text-sm font-medium text-gray-700 mb-1">Tipo de Contrato <span class="text-red-500">*</span></label>
            <select id="tipo" name="tipo" required class="w-full border-gray-300 rounded-lg shadow-sm p-2 focus:border-sky-500 focus:ring-sky-500">
                <option value="">Selecione...</option>
                <option value="Venda" <?php echo ($isEdit && $contratoData['tipo'] === 'Venda') ? 'selected' : ''; ?>>Venda / Prestação de Serviço</option>
                <option value="Compra" <?php echo ($isEdit && $contratoData['tipo'] === 'Compra') ? 'selected' : ''; ?>>Compra / Fornecimento</option>
                <option value="Parceria" <?php echo ($isEdit && $contratoData['tipo'] === 'Parceria') ? 'selected' : ''; ?>>Parceria</option>
                <option value="Locacao" <?php echo ($isEdit && $contratoData['tipo'] === 'Locacao') ? 'selected' : ''; ?>>Locação</option>
                <option value="Outro" <?php echo ($isEdit && $contratoData['tipo'] === 'Outro') ? 'selected' : ''; ?>>Outro</option>
            </select>
        </div>

        <!-- Cliente (Parte Contratada) -->
        <div id="cliente-group" class="md:col-span-2 hidden">
            <label for="cliente_id" class="block text-sm font-medium text-gray-700 mb-1">Cliente <span class="text-red-500">*</span></label>
            <input type="text" id="filtro-cliente" placeholder="Filtrar cliente..." class="w-full border-gray-300 rounded-lg shadow-sm p-2 mb-1">
            <select id="cliente_id" name="cliente_id" class="w-full border-gray-300 rounded-lg shadow-sm p-2 focus:border-sky-500 focus:ring-sky-500 h-24" size="4">
                <option value="">Selecione um cliente</option>
                <?php foreach ($clientes as $cliente) : ?>
                    <option value="<?php echo $cliente['id']; ?>" data-nome="<?php echo strtolower(htmlspecialchars($cliente['nome'])); ?>" <?php echo ($isEdit && isset($contratoData['cliente_id']) && $contratoData['cliente_id'] == $cliente['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cliente['nome']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Fornecedor (Parte Contratada) -->
        <div id="fornecedor-group" class="md:col-span-2 hidden">
            <label for="pessoa_id" class="block text-sm font-medium text-gray-700 mb-1">Fornecedor <span class="text-red-500">*</span></label>
            <input type="text" id="filtro-fornecedor" placeholder="Filtrar fornecedor..." class="w-full border-gray-300 rounded-lg shadow-sm p-2 mb-1">
            <select id="pessoa_id" name="pessoa_id" class="w-full border-gray-300 rounded-lg shadow-sm p-2 focus:border-sky-500 focus:ring-sky-500 h-24" size="4">
                <option value="">Selecione um fornecedor</option>
                <?php foreach ($fornecedores as $fornecedor) : ?>
                    <option value="<?php echo $fornecedor['pessoa_id']; ?>" data-nome="<?php echo strtolower(htmlspecialchars($fornecedor['razao_social'])); ?>" <?php echo ($isEdit && isset($contratoData['pessoa_id']) && $contratoData['pessoa_id'] == $fornecedor['pessoa_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($fornecedor['razao_social']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <!-- Objeto do Contrato -->
    <div class="mt-4">
        <label for="objeto" class="block text-sm font-medium text-gray-700 mb-1">Objeto do Contrato / Cláusulas Principais <span class="text-red-500">*</span></label>
        <textarea id="objeto" name="objeto" required rows="4" class="w-full border-gray-300 rounded-lg shadow-sm p-2 focus:border-sky-500 focus:ring-sky-500"><?php echo $isEdit ? htmlspecialchars($contratoData['objeto']) : ''; ?></textarea>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-4">
        <!-- Valor do Contrato -->
        <div>
            <label for="valor" class="block text-sm font-medium text-gray-700 mb-1">Valor (R$)</label>
            <input type="text" id="valor" name="valor" value="<?php echo $isEdit ? number_format($contratoData['valor'], 2, ',', '.') : ''; ?>" class="w-full border-gray-300 rounded-lg shadow-sm p-2 focus:border-sky-500 focus:ring-sky-500" placeholder="1.234,56">
        </div>
        <!-- Data de Início -->
        <div>
            <label for="data_inicio" class="block text-sm font-medium text-gray-700 mb-1">Data de Início <span class="text-red-500">*</span></label>
            <input type="date" id="data_inicio" name="data_inicio" required value="<?php echo $isEdit ? htmlspecialchars($contratoData['data_inicio']) : ''; ?>" class="w-full border-gray-300 rounded-lg shadow-sm p-2 focus:border-sky-500 focus:ring-sky-500">
        </div>
        <!-- Data de Vencimento -->
        <div>
            <label for="vencimento" class="block text-sm font-medium text-gray-700 mb-1">Data de Vencimento</label>
            <input type="date" id="vencimento" name="vencimento" value="<?php echo $isEdit ? htmlspecialchars($contratoData['vencimento']) : ''; ?>" class="w-full border-gray-300 rounded-lg shadow-sm p-2 focus:border-sky-500 focus:ring-sky-500">
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-4">
        <!-- Status -->
        <div>
            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status <span class="text-red-500">*</span></label>
            <select id="status" name="status" required class="w-full border-gray-300 rounded-lg shadow-sm p-2 focus:border-sky-500 focus:ring-sky-500">
                <option value="Em Vigência" <?php echo ($isEdit && $contratoData['status'] === 'Em Vigência') ? 'selected' : ''; ?>>Em Vigência</option>
                <option value="Pendência Assinatura" <?php echo ($isEdit && $contratoData['status'] === 'Pendência Assinatura') ? 'selected' : ''; ?>>Pendência Assinatura</option>
                <option value="Finalizado" <?php echo ($isEdit && $contratoData['status'] === 'Finalizado') ? 'selected' : ''; ?>>Finalizado</option>
                <option value="Cancelado" <?php echo ($isEdit && $contratoData['status'] === 'Cancelado') ? 'selected' : ''; ?>>Cancelado</option>
            </select>
        </div>

        <!-- Projeto Vinculado -->
        <div>
            <label for="projeto_id" class="block text-sm font-medium text-gray-700 mb-1">Projeto Vinculado</label>
            <select id="projeto_id" name="projeto_id" class="w-full border-gray-300 rounded-lg shadow-sm p-2 focus:border-sky-500 focus:ring-sky-500">
                <option value="">Nenhum</option>
                <?php foreach ($projetos as $projeto) : ?>
                    <option value="<?php echo $projeto['id']; ?>" <?php echo ($isEdit && isset($contratoData['projeto_id']) && $contratoData['projeto_id'] == $projeto['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($projeto['nome']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Upload de Documento -->
        <div>
            <label for="documento" class="block text-sm font-medium text-gray-700 mb-1">Anexar Documento (PDF)</label>
            <input type="file" id="documento" name="documento" accept=".pdf" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-sky-50 file:text-sky-700 hover:file:bg-sky-100">
            <?php if ($isEdit && !empty($contratoData['documento_path'])) : ?>
                <small class="text-gray-500 mt-1 block">
                    Arquivo atual: <a href="<?php echo BASE_URL; ?>/contratos/download/<?php echo htmlspecialchars($contratoData['documento_path']); ?>" target="_blank" class="text-sky-600 hover:underline"><?php echo htmlspecialchars(substr($contratoData['documento_path'], 0, 30)); ?>...</a>
                    <a href="<?php echo BASE_URL; ?>/contratos/removerDocumento/<?php echo $contratoData['id']; ?>" class="text-red-500 hover:text-red-700 text-xs ml-2" onclick="return confirm('Tem certeza que deseja remover o documento anexo? Esta ação não pode ser desfeita.');" title="Remover documento anexo">
                        (Remover)
                    </a>
                    <br>Enviar um novo arquivo irá substituir o atual.
                </small>
            <?php endif; ?>
        </div>
    </div>

    <!-- Botões de Ação -->
    <div class="mt-8 pt-4 border-t border-gray-200 flex justify-end space-x-3">
        <button type="button" id="cancel-form-btn" class="px-4 py-2 text-sm font-semibold text-gray-700 bg-gray-200 rounded-lg shadow-md hover:bg-gray-300 transition">
            Cancelar
        </button>
        <button type="submit" class="px-6 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg shadow-md hover:bg-blue-700 transition">
            <?php echo $isEdit ? 'Atualizar Contrato' : 'Salvar Contrato'; ?>
        </button>
    </div>
</form>

<script>
    // IIFE (Immediately Invoked Function Expression) para encapsular o escopo e rodar imediatamente
    (function() {
        const form = document.querySelector('form[action="<?php echo $actionUrl; ?>"]');
        if (!form) return;

        const tipoSelect = form.querySelector('#tipo');
        const clienteGroup = form.querySelector('#cliente-group');
        const clienteSelect = form.querySelector('#cliente_id');
        const filtroCliente = form.querySelector('#filtro-cliente');
        const fornecedorGroup = form.querySelector('#fornecedor-group');
        const fornecedorSelect = form.querySelector('#pessoa_id');
        const filtroFornecedor = form.querySelector('#filtro-fornecedor');

        function toggleParteContratada() {
            const tipo = tipoSelect.value;
            // Reseta ambos os campos para o estado inicial
            clienteGroup.classList.add('hidden');
            clienteSelect.required = false;
            fornecedorGroup.classList.add('hidden');
            fornecedorSelect.required = false;

            if (tipo === 'Venda') {
                clienteGroup.classList.remove('hidden');
                clienteSelect.required = true;
                fornecedorSelect.value = ''; // Limpa a seleção do outro campo
            } else if (tipo === 'Compra') {
                fornecedorGroup.classList.remove('hidden');
                fornecedorSelect.required = true;
                clienteSelect.value = ''; // Limpa a seleção do outro campo
            }
        }

        function filtrarLista(input, select) {
            const filtro = input.value.toLowerCase();
            Array.from(select.options).forEach(option => {
                const nome = option.getAttribute('data-nome');
                if (nome) {
                    option.style.display = nome.includes(filtro) ? '' : 'none';
                }
            });
        }

        // Adiciona os listeners
        tipoSelect.addEventListener('change', toggleParteContratada);
        filtroCliente.addEventListener('input', () => filtrarLista(filtroCliente, clienteSelect));
        filtroFornecedor.addEventListener('input', () => filtrarLista(filtroFornecedor, fornecedorSelect));

        // Executa a função na inicialização para ajustar o formulário no modo de edição
        toggleParteContratada();
    }());
</script>