<div class="flex justify-between items-center mb-6">
    <h2 class="text-2xl font-bold text-gray-800">Logs de Auditoria</h2>
</div>

<!-- Filtros -->
<div class="bg-white p-4 rounded-lg shadow-md mb-6">
    <form action="<?php echo BASE_URL; ?>/audit-log" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
        <div>
            <label for="user_id" class="block text-sm font-medium text-gray-700 mb-1">Usuário</label>
            <select name="user_id" id="user_id" class="w-full border-gray-300 rounded-lg shadow-sm p-2 text-sm">
                <option value="">Todos</option>
                <?php foreach ($usuarios as $usuario): ?>
                    <option value="<?php echo $usuario['id']; ?>" <?php echo ($filtros['user_id'] == $usuario['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($usuario['nome']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label for="module" class="block text-sm font-medium text-gray-700 mb-1">Módulo</label>
            <input type="text" name="module" id="module" value="<?php echo htmlspecialchars($filtros['module'] ?? ''); ?>" class="w-full border-gray-300 rounded-lg shadow-sm p-2 text-sm" placeholder="Ex: Clientes">
        </div>
        <div>
            <label for="action" class="block text-sm font-medium text-gray-700 mb-1">Ação</label>
            <input type="text" name="action" id="action" value="<?php echo htmlspecialchars($filtros['action'] ?? ''); ?>" class="w-full border-gray-300 rounded-lg shadow-sm p-2 text-sm" placeholder="Ex: CREATE">
        </div>
        <div class="flex gap-2">
            <button type="submit" class="bg-sky-600 text-white px-4 py-2 rounded-lg hover:bg-sky-700 text-sm font-medium flex-1">Filtrar</button>
            <a href="<?php echo BASE_URL; ?>/audit-log" class="bg-gray-200 text-gray-800 px-4 py-2 rounded-lg hover:bg-gray-300 text-sm font-medium flex items-center justify-center">Limpar</a>
        </div>
    </form>
</div>

<!-- Tabela -->
<div class="bg-white rounded-lg shadow-md overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data/Hora</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuário</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ação</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Módulo</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descrição</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (!empty($logs)): ?>
                    <?php foreach ($logs as $log): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo date('d/m/Y H:i:s', strtotime($log['created_at'])); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                <?php echo htmlspecialchars($log['user_name'] ?? 'Sistema/Anônimo'); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    <?php
                                    if (strpos($log['action'], 'DELETE') !== false) echo 'bg-red-100 text-red-800';
                                    elseif (strpos($log['action'], 'CREATE') !== false) echo 'bg-green-100 text-green-800';
                                    elseif (strpos($log['action'], 'UPDATE') !== false) echo 'bg-yellow-100 text-yellow-800';
                                    else echo 'bg-blue-100 text-blue-800';
                                    ?>">
                                    <?php echo htmlspecialchars($log['action']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo htmlspecialchars($log['module'] ?? '-'); ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate" title="<?php echo htmlspecialchars($log['description']); ?>">
                                <?php echo htmlspecialchars($log['description']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo htmlspecialchars($log['ip_address'] ?? '-'); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">Nenhum registro encontrado.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Paginação Simples -->
    <div class="bg-gray-50 px-4 py-3 border-t border-gray-200 flex items-center justify-between sm:px-6">
        <div class="flex-1 flex justify-between">
            <?php if ($paginaAtual > 1): ?>
                <a href="?page=<?php echo $paginaAtual - 1; ?>&user_id=<?php echo $filtros['user_id']; ?>&module=<?php echo $filtros['module']; ?>&action=<?php echo $filtros['action']; ?>" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    Anterior
                </a>
            <?php else: ?>
                <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-400 bg-gray-100 cursor-not-allowed">
                    Anterior
                </span>
            <?php endif; ?>

            <span class="text-sm text-gray-700 self-center">Página <?php echo $paginaAtual; ?></span>

            <?php if ($temMais): ?>
                <a href="?page=<?php echo $paginaAtual + 1; ?>&user_id=<?php echo $filtros['user_id']; ?>&module=<?php echo $filtros['module']; ?>&action=<?php echo $filtros['action']; ?>" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    Próxima
                </a>
            <?php else: ?>
                <span class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-400 bg-gray-100 cursor-not-allowed">
                    Próxima
                </span>
            <?php endif; ?>
        </div>
    </div>
</div>