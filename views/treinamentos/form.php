<?php
$isEdit = isset($treinamento) && !empty($treinamento['id']);
$actionUrl = BASE_URL . '/treinamentos/salvar';
?>

<div class="flex justify-between items-start mb-6">
    <div>
        <h2 class="text-2xl font-bold"><?php echo htmlspecialchars($pageTitle); ?></h2>
        <p class="text-gray-600">Preencha os dados abaixo para agendar um novo treinamento.</p>
    </div>
    <a href="<?php echo BASE_URL; ?>/treinamentos" class="px-4 py-2 text-sm font-semibold text-gray-700 bg-gray-200 rounded-lg shadow-md hover:bg-gray-300 transition">
        &larr; Voltar para a Lista
    </a>
</div>

<div class="bg-white p-6 rounded-lg shadow-md max-w-4xl mx-auto">
    <form action="<?php echo $actionUrl; ?>" method="POST">
        <?php if ($isEdit) : ?>
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($treinamento['id']); ?>">
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="md:col-span-2">
                <label for="nome_treinamento" class="block text-sm font-medium text-gray-700 mb-1">Nome do Treinamento <span class="text-red-500">*</span></label>
                <input type="text" id="nome_treinamento" name="nome_treinamento" required value="<?php echo htmlspecialchars($treinamento['nome_treinamento'] ?? ''); ?>" class="w-full border-gray-300 rounded-lg shadow-sm p-2">
            </div>

            <div>
                <label for="data_prevista" class="block text-sm font-medium text-gray-700 mb-1">Data e Hora Prevista <span class="text-red-500">*</span></label>
                <input type="datetime-local" id="data_prevista" name="data_prevista" required value="<?php echo $isEdit ? date('Y-m-d\TH:i', strtotime($treinamento['data_prevista'])) : ''; ?>" class="w-full border-gray-300 rounded-lg shadow-sm p-2">
            </div>

            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select id="status" name="status" class="w-full border-gray-300 rounded-lg shadow-sm p-2">
                    <?php
                    $statusOptions = ['Agendado', 'Realizado', 'Cancelado'];
                    $statusSalvo = $treinamento['status'] ?? 'Agendado';
                    foreach ($statusOptions as $opt) : ?>
                        <option value="<?php echo $opt; ?>" <?php echo ($opt === $statusSalvo) ? 'selected' : ''; ?>><?php echo $opt; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label for="instrutor" class="block text-sm font-medium text-gray-700 mb-1">Instrutor</label>
                <input type="text" id="instrutor" name="instrutor" value="<?php echo htmlspecialchars($treinamento['instrutor'] ?? ''); ?>" class="w-full border-gray-300 rounded-lg shadow-sm p-2">
            </div>

            <div>
                <label for="local" class="block text-sm font-medium text-gray-700 mb-1">Local</label>
                <input type="text" id="local" name="local" value="<?php echo htmlspecialchars($treinamento['local'] ?? ''); ?>" class="w-full border-gray-300 rounded-lg shadow-sm p-2">
            </div>

            <div class="md:col-span-2">
                <label for="descricao" class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
                <textarea id="descricao" name="descricao" rows="4" class="w-full border-gray-300 rounded-lg shadow-sm p-2"><?php echo htmlspecialchars($treinamento['descricao'] ?? ''); ?></textarea>
            </div>
        </div>

        <div class="mt-8 pt-4 border-t border-gray-200 flex justify-end">
            <button type="submit" class="px-6 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-lg shadow-md hover:bg-indigo-700 transition">Salvar Treinamento</button>
        </div>
    </form>
</div>

