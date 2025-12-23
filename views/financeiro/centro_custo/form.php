<?php
$isEdit = isset($centroCusto) && !empty($centroCusto['id']);
$actionUrl = BASE_URL . '/centrocusto/salvar';
?>

<div class="flex justify-between items-start mb-6">
    <div>
        <h2 class="text-2xl font-bold"><?php echo htmlspecialchars($pageTitle); ?></h2>
        <p class="text-gray-600">Preencha os dados do centro de custo.</p>
    </div>
    <a href="<?php echo BASE_URL; ?>/centrocusto" class="px-4 py-2 text-sm font-semibold text-gray-700 bg-gray-200 rounded-lg shadow-md hover:bg-gray-300 transition">
        &larr; Voltar para a Lista
    </a>
</div>

<div class="bg-white p-6 rounded-lg shadow-md max-w-2xl mx-auto">
    <form action="<?php echo $actionUrl; ?>" method="POST">
        <?php if ($isEdit) : ?>
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($centroCusto['id']); ?>">
        <?php endif; ?>

        <div class="mb-4">
            <label for="nome" class="block text-sm font-medium text-gray-700 mb-1">Nome do Centro de Custo <span class="text-red-500">*</span></label>
            <input type="text" id="nome" name="nome" required value="<?php echo htmlspecialchars($centroCusto['nome'] ?? ''); ?>" class="w-full border-gray-300 rounded-lg shadow-sm p-2">
        </div>

        <div class="mt-8 pt-4 border-t border-gray-200 flex justify-end">
            <button type="submit" class="px-6 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-lg shadow-md hover:bg-indigo-700 transition">Salvar</button>
        </div>
    </form>
</div>