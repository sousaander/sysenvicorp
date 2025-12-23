<div class="flex justify-between items-center mb-6">
    <div>
        <h2 class="text-2xl font-bold"><?php echo htmlspecialchars($pageTitle); ?></h2>
        <p class="text-gray-600">Adicione, edite ou remova as classificações usadas nas transações financeiras.</p>
    </div>
    <a href="<?php echo BASE_URL; ?>/classificacao/form" class="px-4 py-2 text-sm font-semibold text-white bg-sky-600 rounded-lg shadow-md hover:bg-sky-700 transition">
        + Nova Classificação
    </a>
</div>

<div class="bg-white p-6 rounded-lg shadow-md">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nome</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            <?php if (!empty($classificacoes)): ?>
                <?php foreach ($classificacoes as $class): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            <?php echo htmlspecialchars($class['nome']); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php
                            if ($class['tipo'] === 'R') echo 'Receita';
                            elseif ($class['tipo'] === 'P') echo 'Despesa';
                            else echo 'Geral (Ambos)';
                            ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="<?php echo BASE_URL; ?>/classificacao/form/<?php echo $class['id']; ?>" class="text-indigo-600 hover:text-indigo-900 mr-4">Editar</a>
                            <a href="<?php echo BASE_URL; ?>/classificacao/excluir/<?php echo $class['id']; ?>" class="text-red-600 hover:text-red-900" onclick="return confirm('Tem certeza que deseja excluir esta classificação? As transações associadas a ela ficarão sem classificação.');">Excluir</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="3" class="px-6 py-4 text-center text-gray-500">
                        Nenhuma classificação cadastrada.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>