<div class="flex justify-between items-start mb-6">
    <div>
        <h2 class="text-2xl font-bold"><?php echo htmlspecialchars($pageTitle); ?></h2>
        <p class="text-gray-600">Selecione um funcionário para visualizar ou imprimir a ficha cadastral completa.</p>
    </div>
    <a href="<?php echo BASE_URL; ?>/rh" class="px-4 py-2 text-sm font-semibold text-gray-700 bg-gray-200 rounded-lg shadow-md hover:bg-gray-300 transition">
        &larr; Voltar para RH
    </a>
</div>

<div class="bg-white p-6 rounded-lg shadow-md max-w-2xl mx-auto">
    <form id="form-ficha-cadastral">
        <div class="space-y-4">
            <!-- Seleção de Funcionário -->
            <div>
                <label for="funcionario_id" class="block text-sm font-medium text-gray-700 mb-1">Funcionário <span class="text-red-500">*</span></label>
                <select id="funcionario_id" name="funcionario_id" required class="w-full border-gray-300 rounded-lg shadow-sm focus:border-sky-500 focus:ring-sky-500 p-2">
                    <option value="">Selecione um funcionário...</option>
                    <?php if (!empty($funcionarios)): ?>
                        <?php foreach ($funcionarios as $funcionario): ?>
                            <option value="<?php echo htmlspecialchars($funcionario['id']); ?>">
                                <?php echo htmlspecialchars($funcionario['nome']); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
        </div>

        <div class="mt-8 pt-4 border-t border-gray-200 flex justify-end">
            <button type="submit" class="px-6 py-2 text-sm font-semibold text-white bg-teal-600 rounded-lg shadow-md hover:bg-teal-700 transition">Gerar Ficha</button>
        </div>
    </form>
</div>

<script>
    document.getElementById('form-ficha-cadastral').addEventListener('submit', function(e) {
        e.preventDefault();
        const funcionarioId = document.getElementById('funcionario_id').value;
        if (funcionarioId) {
            const url = `<?php echo BASE_URL; ?>/rh/fichaCadastral/${funcionarioId}`;
            window.open(url, '_blank');
        }
    });
</script>