<div class="flex justify-between items-start mb-6">
    <div>
        <h2 class="text-2xl font-bold"><?php echo htmlspecialchars($pageTitle); ?></h2>
        <p class="text-gray-600">Ajuste os dados e recalcule os valores para o registro de férias.</p>
    </div>
    <a href="<?php echo BASE_URL; ?>/rh/historicoFerias" class="px-4 py-2 text-sm font-semibold text-gray-700 bg-gray-200 rounded-lg shadow-md hover:bg-gray-300 transition">
        &larr; Voltar para o Histórico
    </a>
</div>

<div class="bg-white p-6 rounded-lg shadow-md max-w-4xl mx-auto">
    <form action="<?php echo BASE_URL; ?>/rh/atualizarHistoricoFerias" method="POST">
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($registro['id']); ?>">

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Seleção de Funcionário -->
            <div class="md:col-span-3">
                <label for="funcionario_id" class="block text-sm font-medium text-gray-700 mb-1">Funcionário <span class="text-red-500">*</span></label>
                <select id="funcionario_id" name="funcionario_id" required class="w-full border-gray-300 rounded-lg shadow-sm focus:border-sky-500 focus:ring-sky-500 p-2">
                    <option value="">Selecione um funcionário...</option>
                    <?php foreach ($funcionarios as $funcionario) : ?>
                        <option value="<?php echo $funcionario['id']; ?>" <?php echo ($funcionario['id'] == $registro['funcionario_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($funcionario['nome']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Data de Início -->
            <div>
                <label for="data_inicio" class="block text-sm font-medium text-gray-700 mb-1">Data de Início das Férias <span class="text-red-500">*</span></label>
                <input type="date" id="data_inicio" name="data_inicio" required value="<?php echo htmlspecialchars($registro['data_inicio_ferias']); ?>" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-sky-500 focus:ring-sky-500 p-2">
            </div>

            <!-- Dias de Férias -->
            <div>
                <label for="dias_ferias" class="block text-sm font-medium text-gray-700 mb-1">Dias de Férias <span class="text-red-500">*</span></label>
                <input type="number" id="dias_ferias" name="dias_ferias" min="1" max="30" required value="<?php echo htmlspecialchars($registro['dias_ferias']); ?>" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-sky-500 focus:ring-sky-500 p-2">
            </div>
        </div>

        <div class="mt-8 pt-4 border-t border-gray-200 flex justify-end">
            <button type="submit" class="px-6 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-lg shadow-md hover:bg-indigo-700 transition">Salvar Alterações</button>
        </div>
    </form>
</div>