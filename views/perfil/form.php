<h2 class="text-2xl font-bold mb-4">
    <?php echo htmlspecialchars($pageTitle); ?>
</h2>
<p class="mb-6 text-gray-600">Preencha os dados do perfil de acesso.</p>

<?php
$isEdit = isset($perfil) && $perfil !== null;
$actionUrl = BASE_URL . '/perfil/salvar';
?>

<form action="<?php echo $actionUrl; ?>" method="post" class="bg-white p-6 rounded-lg shadow-xl max-w-2xl mx-auto">

    <input type="hidden" name="id" value="<?php echo $isEdit ? htmlspecialchars($perfil['id'] ?? $perfil['perfil_id'] ?? '') : ''; ?>">
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

    <div class="space-y-6">
        <div>
            <label for="nome_perfil" class="block text-sm font-medium text-gray-700 mb-1">Nome do Perfil <span class="text-red-500">*</span></label>
            <input type="text" id="nome_perfil" name="nome_perfil" required
                value="<?php echo $isEdit ? htmlspecialchars($perfil['nome_perfil'] ?? $perfil['nome'] ?? '') : ''; ?>"
                class="w-full border-gray-300 rounded-lg shadow-sm focus:border-sky-500 focus:ring-sky-500 p-2">
        </div>

        <div>
            <label for="descricao" class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
            <textarea id="descricao" name="descricao" rows="4" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-sky-500 focus:ring-sky-500 p-2"><?php echo $isEdit ? htmlspecialchars($perfil['descricao'] ?? '') : ''; ?></textarea>
        </div>
    </div>

    <div class="mt-8 pt-4 border-t border-gray-200 flex justify-end space-x-4">
        <a href="<?php echo BASE_URL; ?>/perfil" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg shadow-sm hover:bg-gray-200 transition">Cancelar</a>
        <button type="submit" class="px-6 py-2 text-sm font-semibold text-white bg-emerald-600 rounded-lg shadow-md hover:bg-emerald-700 transition">
            <?php echo $isEdit ? 'Salvar Alterações' : 'Criar Perfil'; ?>
        </button>
    </div>
</form>