<div class="flex justify-between items-center mb-6">
    <div>
        <h2 class="text-2xl font-bold"><?php echo htmlspecialchars($pageTitle); ?></h2>
        <p class="text-gray-600">Cadastre, edite e consulte os treinamentos da empresa.</p>
    </div>
    <div class="flex items-center space-x-2">
        <a href="<?php echo BASE_URL; ?>/rh" class="px-4 py-2 text-sm font-semibold text-gray-700 bg-gray-200 rounded-lg shadow-md hover:bg-gray-300 transition">
            &larr; Voltar para RH
        </a>
        <a href="<?php echo BASE_URL; ?>/treinamentos/novo" class="px-4 py-2 text-sm font-semibold text-white bg-sky-600 rounded-lg shadow-md hover:bg-sky-700 transition">
            + Novo Treinamento
        </a>
    </div>
</div>

<div class="bg-white p-6 rounded-lg shadow-md">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Treinamento</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data Prevista</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Instrutor</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (!empty($treinamentos)) : ?>
                    <?php foreach ($treinamentos as $item) : ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($item['nome_treinamento']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600"><?php echo date('d/m/Y H:i', strtotime($item['data_prevista'])); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600"><?php echo htmlspecialchars($item['instrutor']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $item['status'] === 'Agendado' ? 'bg-blue-100 text-blue-800' : ($item['status'] === 'Realizado' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'); ?>">
                                    <?php echo htmlspecialchars($item['status']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="<?php echo BASE_URL; ?>/treinamentos/editar/<?php echo $item['id']; ?>" class="text-indigo-600 hover:text-indigo-900">Editar</a>
                                <a href="<?php echo BASE_URL; ?>/treinamentos/excluir/<?php echo $item['id']; ?>" class="text-red-600 hover:text-red-900 ml-4" onclick="return confirm('Tem certeza que deseja excluir este treinamento?');">Excluir</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">Nenhum treinamento cadastrado.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Paginação -->
    <div class="mt-4 flex justify-end items-center">
        <?php if ($totalPaginas > 1) : ?>
            <nav class="flex items-center justify-end space-x-2">
                <a href="<?php echo BASE_URL; ?>/treinamentos?page=<?php echo $paginaAtual - 1; ?>" class="<?php echo ($paginaAtual <= 1) ? 'pointer-events-none text-gray-400' : 'text-gray-600 hover:text-indigo-600'; ?> px-3 py-1 rounded-md text-sm font-medium">
                    Anterior
                </a>
                <?php for ($i = 1; $i <= $totalPaginas; $i++) : ?>
                    <a href="<?php echo BASE_URL; ?>/treinamentos?page=<?php echo $i; ?>" class="<?php echo ($i == $paginaAtual) ? 'bg-indigo-500 text-white' : 'bg-white text-gray-600 hover:bg-gray-100'; ?> px-3 py-1 rounded-md text-sm font-medium border">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
                <a href="<?php echo BASE_URL; ?>/treinamentos?page=<?php echo $paginaAtual + 1; ?>" class="<?php echo ($paginaAtual >= $totalPaginas) ? 'pointer-events-none text-gray-400' : 'text-gray-600 hover:text-indigo-600'; ?> px-3 py-1 rounded-md text-sm font-medium">
                    Próxima
                </a>
            </nav>
        <?php endif; ?>
    </div>
</div>