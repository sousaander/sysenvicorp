<div class="flex justify-between items-center mb-6">
    <div>
        <h2 class="text-2xl font-bold"><?php echo htmlspecialchars($pageTitle); ?></h2>
        <p class="text-gray-600">Gerencie suas notas fiscais emitidas.</p>
    </div>
    <a href="<?php echo BASE_URL; ?>/notaFiscal/form" class="px-4 py-2 text-sm font-semibold text-white bg-sky-600 rounded-lg shadow-md hover:bg-sky-700 transition">
        + Emitir Nova Nota
    </a>
</div>

<div class="bg-white p-6 rounded-lg shadow-md">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Número</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Emissão</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vencimento</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Valor</th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            <?php if (!empty($notas)): ?>
                <?php foreach ($notas as $nota): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            <?php echo htmlspecialchars($nota['numero']); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php echo htmlspecialchars($nota['cliente']); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php echo date('d/m/Y', strtotime($nota['data_emissao'])); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php echo date('d/m/Y', strtotime($nota['data_vencimento'])); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-semibold text-gray-800">
                            R$ <?php echo number_format($nota['valor_total'], 2, ',', '.'); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <?php
                            $statusClasses = [
                                'Emitida' => 'bg-blue-100 text-blue-800',
                                'Paga' => 'bg-green-100 text-green-800',
                                'Cancelada' => 'bg-red-100 text-red-800',
                            ];
                            $classe = $statusClasses[$nota['status']] ?? 'bg-gray-100 text-gray-800';
                            ?>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $classe; ?>">
                                <?php echo htmlspecialchars($nota['status']); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="<?php echo BASE_URL; ?>/notaFiscal/form/<?php echo $nota['id']; ?>" class="text-indigo-600 hover:text-indigo-900 mr-4">Editar</a>
                            <a href="<?php echo BASE_URL; ?>/notaFiscal/excluir/<?php echo $nota['id']; ?>" class="text-red-600 hover:text-red-900" onclick="return confirm('Tem certeza que deseja excluir esta Nota Fiscal?');">Excluir</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                        Nenhuma nota fiscal encontrada.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>