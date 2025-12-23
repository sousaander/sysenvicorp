<h2 class="text-2xl font-bold mb-4">
    <?php echo htmlspecialchars($pageTitle); ?>
</h2>
<p class="mb-6 text-gray-600">
    Preencha os dados da classificação financeira.
</p>

<?php
$isEdit = isset($classificacao) && $classificacao !== null;
$actionUrl = BASE_URL . '/classificacao/salvar';
?>

<form action="<?php echo $actionUrl; ?>" method="POST" class="bg-white p-6 rounded-lg shadow-xl max-w-2xl mx-auto">

    <input type="hidden" name="id" value="<?php echo $isEdit ? htmlspecialchars($classificacao['id']) : ''; ?>">

    <div class="space-y-6">

        <!-- Nome -->
        <div>
            <label for="nome" class="block text-sm font-medium text-gray-700 mb-1">Nome da Classificação <span class="text-red-500">*</span></label>
            <input type="text" id="nome" name="nome" required
                value="<?php echo $isEdit ? htmlspecialchars($classificacao['nome']) : ''; ?>"
                class="w-full border-gray-300 rounded-lg shadow-sm focus:border-sky-500 focus:ring-sky-500 p-2">
        </div>

        <!-- Tipo -->
        <div>
            <label for="tipo" class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
            <select id="tipo" name="tipo" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-sky-500 focus:ring-sky-500 p-2">
                <option value="" <?php echo ($isEdit && $classificacao['tipo'] === null) ? 'selected' : ''; ?>>Geral (Ambos)</option>
                <option value="R" <?php echo ($isEdit && $classificacao['tipo'] === 'R') ? 'selected' : ''; ?>>Receita</option>
                <option value="P" <?php echo ($isEdit && $classificacao['tipo'] === 'P') ? 'selected' : ''; ?>>Despesa</option>
            </select>
        </div>
    </div>

    <!-- Botões de Ação -->
    <div class="mt-8 pt-4 border-t border-gray-200 flex justify-end space-x-4">
        <a href="<?php echo BASE_URL; ?>/classificacao" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg shadow-sm hover:bg-gray-200 transition">Cancelar</a>
        <button type="submit" class="px-6 py-2 text-sm font-semibold text-white bg-emerald-600 rounded-lg shadow-md hover:bg-emerald-700 transition">
            <?php echo $isEdit ? 'Salvar Alterações' : 'Cadastrar'; ?>
        </button>
    </div>
</form>