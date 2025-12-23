<h2 class="text-2xl font-bold mb-4">Detalhes do Funcionário</h2>
<p class="mb-6 text-gray-600">Informações detalhadas sobre o colaborador.</p>

<div class="bg-white p-8 rounded-lg shadow-md max-w-4xl mx-auto">
    <?php if (isset($funcionario)): ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <p class="text-sm font-medium text-gray-700">ID:</p>
                <p class="text-lg font-semibold text-gray-900 mb-4"><?php echo htmlspecialchars($funcionario['id']); ?></p>

                <p class="text-sm font-medium text-gray-700">Nome Completo:</p>
                <p class="text-lg font-semibold text-gray-900 mb-4"><?php echo htmlspecialchars($funcionario['nome'] ?? 'N/A'); ?></p>

                <p class="text-sm font-medium text-gray-700">Email Corporativo:</p>
                <p class="text-lg font-semibold text-gray-900 mb-4"><?php echo htmlspecialchars($funcionario['email'] ?? 'Não informado'); ?></p>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-700">Cargo:</p>
                <p class="text-lg font-semibold text-gray-900 mb-4"><?php echo htmlspecialchars($funcionario['cargo'] ?? 'Não informado'); ?></p>

                <p class="text-sm font-medium text-gray-700">Setor:</p>
                <p class="text-lg font-semibold text-gray-900 mb-4"><?php echo htmlspecialchars($funcionario['setor'] ?? 'Não informado'); ?></p>

                <p class="text-sm font-medium text-gray-700">Data de Admissão:</p>
                <p class="text-lg font-semibold text-gray-900 mb-4"><?php echo !empty($funcionario['data_admissao']) ? htmlspecialchars(date('d/m/Y', strtotime($funcionario['data_admissao']))) : 'N/A'; ?></p>
            </div>
        </div>
        <div class="mt-6 flex justify-end">
            <a href="<?php echo BASE_URL; ?>/rh" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-md mr-3 hover:bg-gray-300">Voltar para RH</a>
            <a href="<?php echo BASE_URL; ?>/rh/fichaCadastral/<?php echo htmlspecialchars($funcionario['id']); ?>" target="_blank" class="bg-teal-600 text-white px-4 py-2 rounded-md shadow-sm hover:bg-teal-700 ml-3">Gerar Ficha (PDF)</a>
            <a href="<?php echo BASE_URL; ?>/rh/editar/<?php echo htmlspecialchars($funcionario['id']); ?>" class="bg-indigo-600 text-white px-4 py-2 rounded-md shadow-sm hover:bg-indigo-700">Editar Funcionário</a>
            <a href="<?php echo BASE_URL; ?>/rh/excluir/<?php echo htmlspecialchars($funcionario['id']); ?>" onclick="return confirm('Tem certeza que deseja excluir este funcionário? Esta ação não pode ser desfeita.');" class="bg-red-600 text-white px-4 py-2 rounded-md shadow-sm hover:bg-red-700 ml-3">Excluir Funcionário</a>
        </div>
    <?php else: ?>
        <p class="text-red-500">Funcionário não encontrado.</p>
        <div class="mt-6 flex justify-end">
            <a href="<?php echo BASE_URL; ?>/rh" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-md mr-3 hover:bg-gray-300">Voltar para RH</a>
        </div>
    <?php endif; ?>
</div>