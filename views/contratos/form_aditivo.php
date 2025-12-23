<?php
// A variável $contrato_id é passada pelo controller
$actionUrl = BASE_URL . '/contratos/salvarAditivo';
?>

<form action="<?php echo $actionUrl; ?>" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="contrato_id" value="<?php echo htmlspecialchars($contrato_id); ?>">

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Tipo de Aditivo -->
        <div>
            <label for="tipo_aditivo" class="block text-sm font-medium text-gray-700 mb-1">Tipo de Aditivo <span class="text-red-500">*</span></label>
            <select id="tipo_aditivo" name="tipo_aditivo" required class="w-full border-gray-300 rounded-lg shadow-sm p-2 focus:border-sky-500 focus:ring-sky-500">
                <option value="">Selecione...</option>
                <option value="Prazo">Alteração de Prazo</option>
                <option value="Valor">Alteração de Valor</option>
                <option value="Escopo">Alteração de Escopo</option>
                <option value="Outro">Outro</option>
            </select>
        </div>

        <!-- Data do Aditivo -->
        <div>
            <label for="data_aditivo" class="block text-sm font-medium text-gray-700 mb-1">Data do Aditivo <span class="text-red-500">*</span></label>
            <input type="date" id="data_aditivo" name="data_aditivo" required value="<?php echo date('Y-m-d'); ?>" class="w-full border-gray-300 rounded-lg shadow-sm p-2 focus:border-sky-500 focus:ring-sky-500">
        </div>
    </div>

    <!-- Descrição -->
    <div class="mt-4">
        <label for="descricao" class="block text-sm font-medium text-gray-700 mb-1">Descrição da Alteração <span class="text-red-500">*</span></label>
        <textarea id="descricao" name="descricao" required rows="4" class="w-full border-gray-300 rounded-lg shadow-sm p-2 focus:border-sky-500 focus:ring-sky-500" placeholder="Descreva o que foi alterado no contrato. Ex: Prorrogação do prazo de vigência por mais 12 meses."></textarea>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
        <!-- Alteração de Valor -->
        <div>
            <label for="valor_alteracao" class="block text-sm font-medium text-gray-700 mb-1">Acréscimo/Decréscimo de Valor (R$)</label>
            <input type="text" id="valor_alteracao" name="valor_alteracao" class="w-full border-gray-300 rounded-lg shadow-sm p-2" placeholder="+1000,00 ou -500,00">
        </div>

        <!-- Novo Vencimento -->
        <div>
            <label for="novo_vencimento" class="block text-sm font-medium text-gray-700 mb-1">Nova Data de Vencimento</label>
            <input type="date" id="novo_vencimento" name="novo_vencimento" class="w-full border-gray-300 rounded-lg shadow-sm p-2">
        </div>
    </div>

    <!-- Upload de Documento -->
    <div class="mt-4">
        <label for="documento_aditivo" class="block text-sm font-medium text-gray-700 mb-1">Anexar Documento do Aditivo (PDF)</label>
        <input type="file" id="documento_aditivo" name="documento_aditivo" accept=".pdf" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-sky-50 file:text-sky-700 hover:file:bg-sky-100">
    </div>

    <!-- Botões de Ação -->
    <div class="mt-8 pt-4 border-t border-gray-200 flex justify-end space-x-3">
        <button type="button" id="cancel-aditivo-btn" class="px-4 py-2 text-sm font-semibold text-gray-700 bg-gray-200 rounded-lg shadow-md hover:bg-gray-300 transition">
            Cancelar
        </button>
        <button type="submit" class="px-6 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg shadow-md hover:bg-blue-700 transition">
            Salvar Aditivo
        </button>
    </div>
</form>