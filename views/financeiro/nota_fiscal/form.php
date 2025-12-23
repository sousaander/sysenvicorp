<h2 class="text-2xl font-bold mb-4">
    <?php echo htmlspecialchars($pageTitle); ?>
</h2>
<p class="mb-6 text-gray-600">
    Preencha os detalhes da nota fiscal.
</p>

<?php
$isEdit = isset($nota) && $nota !== null;
$actionUrl = BASE_URL . '/notaFiscal/salvar';
?>

<form action="<?php echo $actionUrl; ?>" method="POST" class="bg-white p-6 rounded-lg shadow-xl max-w-4xl mx-auto">

    <input type="hidden" name="id" value="<?php echo $isEdit ? htmlspecialchars($nota['id']) : ''; ?>">

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <!-- Número da Nota -->
        <div>
            <label for="numero" class="block text-sm font-medium text-gray-700 mb-1">Número da Nota <span class="text-red-500">*</span></label>
            <input type="text" id="numero" name="numero" required
                value="<?php echo $isEdit ? htmlspecialchars($nota['numero']) : ''; ?>"
                class="w-full border-gray-300 rounded-lg shadow-sm focus:border-sky-500 focus:ring-sky-500 p-2">
        </div>

        <!-- Cliente -->
        <div>
            <label for="cliente" class="block text-sm font-medium text-gray-700 mb-1">Cliente <span class="text-red-500">*</span></label>
            <select id="cliente" name="cliente" required class="w-full border-gray-300 rounded-lg shadow-sm focus:border-sky-500 focus:ring-sky-500 p-2">
                <option value="">Selecione um Cliente</option>
                <?php
                // Assuming $clientes is an array of client data, e.g., from ClientesModel
                // For simplicity, using 'nome' from getClientesSummary, but a dedicated getClientesList would be better
                if (isset($clientes) && is_array($clientes['criticalList'])) { // Using criticalList as a source for mock clients
                    foreach ($clientes['criticalList'] as $cliente) {
                        $clienteNome = htmlspecialchars($cliente['nome']);
                        $selected = ($isEdit && $nota['cliente'] === $clienteNome) ? 'selected' : '';
                        echo "<option value=\"{$clienteNome}\" {$selected}>{$clienteNome}</option>";
                    }
                }
                ?>
            </select>
        </div>

        <!-- Data de Emissão -->
        <div>
            <label for="data_emissao" class="block text-sm font-medium text-gray-700 mb-1">Data de Emissão <span class="text-red-500">*</span></label>
            <input type="date" id="data_emissao" name="data_emissao" required
                value="<?php echo $isEdit ? htmlspecialchars($nota['data_emissao']) : date('Y-m-d'); ?>"
                class="w-full border-gray-300 rounded-lg shadow-sm focus:border-sky-500 focus:ring-sky-500 p-2">
        </div>

        <!-- Data de Vencimento -->
        <div>
            <label for="data_vencimento" class="block text-sm font-medium text-gray-700 mb-1">Data de Vencimento</label>
            <input type="date" id="data_vencimento" name="data_vencimento"
                value="<?php echo $isEdit ? htmlspecialchars($nota['data_vencimento']) : ''; ?>"
                class="w-full border-gray-300 rounded-lg shadow-sm focus:border-sky-500 focus:ring-sky-500 p-2">
        </div>

        <!-- Valor Total -->
        <div>
            <label for="valor_total" class="block text-sm font-medium text-gray-700 mb-1">Valor Total (R$) <span class="text-red-500">*</span></label>
            <input type="number" id="valor_total" name="valor_total" required step="0.01"
                value="<?php echo $isEdit ? htmlspecialchars($nota['valor_total']) : ''; ?>"
                class="w-full border-gray-300 rounded-lg shadow-sm focus:border-sky-500 focus:ring-sky-500 p-2">
        </div>

        <!-- Status -->
        <div>
            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status <span class="text-red-500">*</span></label>
            <select id="status" name="status" required class="w-full border-gray-300 rounded-lg shadow-sm focus:border-sky-500 focus:ring-sky-500 p-2">
                <option value="Emitida" <?php echo $isEdit && $nota['status'] === 'Emitida' ? 'selected' : ''; ?>>Emitida</option>
                <option value="Paga" <?php echo $isEdit && $nota['status'] === 'Paga' ? 'selected' : ''; ?>>Paga</option>
                <option value="Cancelada" <?php echo $isEdit && $nota['status'] === 'Cancelada' ? 'selected' : ''; ?>>Cancelada</option>
            </select>
        </div>

        <!-- Observações -->
        <div class="md:col-span-2">
            <label for="observacoes" class="block text-sm font-medium text-gray-700 mb-1">Observações</label>
            <textarea id="observacoes" name="observacoes" rows="3" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-sky-500 focus:ring-sky-500 p-2"><?php echo $isEdit ? htmlspecialchars($nota['observacoes']) : ''; ?></textarea>
        </div>

    </div>

    <!-- Botões de Ação -->
    <div class="mt-8 pt-4 border-t border-gray-200 flex justify-end space-x-4">
        <a href="<?php echo BASE_URL; ?>/notaFiscal" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg shadow-sm hover:bg-gray-200 transition">
            Cancelar
        </a>
        <button type="submit" class="px-6 py-2 text-sm font-semibold text-white bg-emerald-600 rounded-lg shadow-md hover:bg-emerald-700 transition">
            <?php echo $isEdit ? 'Salvar Alterações' : 'Emitir Nota Fiscal'; ?>
        </button>
    </div>

</form>