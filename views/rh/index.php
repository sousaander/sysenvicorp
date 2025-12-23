<h2 class="text-2xl font-bold mb-4">Módulo Recursos Humanos</h2>
<p class="mb-6 text-gray-600">Gerenciamento de funcionários, controle de férias, treinamentos e comunicação interna.</p>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <!-- Card 1: Total de Funcionários -->
    <div class="bg-white p-6 rounded-lg shadow-lg border-l-4 border-indigo-500">
        <h3 class="font-semibold text-gray-500">Total de Funcionários</h3>
        <p class="text-3xl font-bold text-indigo-600"><?php echo $totalFuncionarios ?? 0; ?></p>
        <p class="text-sm text-gray-400 mt-2">Ativos na folha de pagamento</p>
    </div>
    <!-- Card 2: Funcionários em Férias -->
    <div class="bg-white p-6 rounded-lg shadow-lg border-l-4 border-yellow-500">
        <h3 class="font-semibold text-gray-500">Em Férias</h3>
        <p class="text-3xl font-bold text-yellow-600"><?php echo $funcionariosFerias ?? 0; ?></p>
        <p class="text-sm text-gray-400 mt-2">Retorno previsto em breve</p>
    </div>
    <!-- Card 3: Novas Contratações -->
    <div class="bg-white p-6 rounded-lg shadow-lg border-l-4 border-green-500">
        <h3 class="font-semibold text-gray-500">Contratações (Mês)</h3>
        <p class="text-3xl font-bold text-green-600"><?php echo $novasContratacoesMes ?? 0; ?></p>
        <p class="text-sm text-gray-400 mt-2">Metas atingidas: 80%</p>
    </div>
    <!-- Card 4: Próximo Treinamento -->
    <div class="bg-white p-6 rounded-lg shadow-lg border-l-4 border-sky-500">
        <h3 class="font-semibold text-gray-500">Próximo Treinamento</h3>
        <p class="text-lg font-bold text-sky-600 truncate"><?php echo $proximoTreinamento ?? 'Nenhum agendado'; ?></p>
        <p class="text-sm text-gray-400 mt-2">Obrigatório para 20 colaboradores</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- Lista de Aniversariantes da Semana -->
    <div class="bg-white p-6 rounded-lg shadow-md lg:col-span-1">
        <h3 class="text-lg font-semibold mb-4 border-b pb-2">Aniversariantes da Semana 🎉</h3>

        <?php if (!empty($aniversariantes)): ?>
            <ul class="space-y-3">
                <?php foreach ($aniversariantes as $aniv): ?>
                    <li class="flex items-center justify-between p-2 bg-gray-50 rounded-md">
                        <span class="text-sm font-medium text-gray-900"><?php echo $aniv['nome']; ?></span>
                        <div class="text-right">
                            <span class="text-xs text-indigo-500 font-semibold mr-2"><?php echo $aniv['setor']; ?></span>
                            <span class="text-sm text-gray-600"><?php echo $aniv['data']; ?></span>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="text-gray-500">Nenhum aniversário esta semana.</p>
        <?php endif; ?>
    </div>

    <!-- Tabela de Funcionários -->
    <div class="bg-white p-6 rounded-lg shadow-md lg:col-span-2">
        <h3 class="text-lg font-semibold mb-4 border-b pb-2">Lista de Funcionários</h3>

        <!-- Seção de Filtros -->
        <form method="GET" action="<?php echo BASE_URL; ?>/rh" class="mb-4 grid grid-cols-1 md:grid-cols-4 gap-4 p-4 bg-gray-50 rounded-lg border">
            <div>
                <label for="filtro_nome" class="text-sm font-medium text-gray-700">Nome</label>
                <input type="text" name="nome" id="filtro_nome" value="<?php echo htmlspecialchars($filtros['nome'] ?? ''); ?>" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm p-2">
            </div>
            <div>
                <label for="filtro_setor" class="text-sm font-medium text-gray-700">Setor</label>
                <input type="text" name="setor" id="filtro_setor" value="<?php echo htmlspecialchars($filtros['setor'] ?? ''); ?>" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm p-2">
            </div>
            <div class="col-span-1 md:col-span-2 flex items-end space-x-2">
                <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md shadow-sm hover:bg-indigo-700">Filtrar</button>
                <a href="<?php echo BASE_URL; ?>/rh" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-300">Limpar</a>
            </div>
        </form>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Funcionário</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cargo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Setor</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (!empty($funcionarios)): ?>
                        <?php foreach ($funcionarios as $func): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($func['nome']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600"><?php echo htmlspecialchars($func['cargo']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($func['setor']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($func['status']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="<?php echo BASE_URL; ?>/rh/detalhe/<?php echo $func['id']; ?>" class="text-indigo-600 hover:text-indigo-900">Detalhes</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-gray-500">Nenhum funcionário encontrado.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="mt-4 flex justify-end items-center">
            <!-- Navegação da Paginação -->
            <?php if ($totalPaginas > 1): ?>
                <nav class="flex items-center justify-end space-x-2">
                    <?php
                    // Mantém os filtros na URL da paginação
                    $queryString = http_build_query(array_merge($filtros, ['page' => '']));
                    ?>
                    <a href="<?php echo BASE_URL; ?>/rh?<?php echo $queryString . ($paginaAtual - 1); ?>" class="<?php echo ($paginaAtual <= 1) ? 'pointer-events-none text-gray-400' : 'text-gray-600 hover:text-indigo-600'; ?> px-3 py-1 rounded-md text-sm font-medium">
                        Anterior
                    </a>

                    <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                        <a href="<?php echo BASE_URL; ?>/rh?<?php echo $queryString . $i; ?>" class="<?php echo ($i == $paginaAtual) ? 'bg-indigo-500 text-white' : 'bg-white text-gray-600 hover:bg-gray-100'; ?> px-3 py-1 rounded-md text-sm font-medium border">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>

                    <a href="<?php echo BASE_URL; ?>/rh?<?php echo $queryString . ($paginaAtual + 1); ?>" class="<?php echo ($paginaAtual >= $totalPaginas) ? 'pointer-events-none text-gray-400' : 'text-gray-600 hover:text-indigo-600'; ?> px-3 py-1 rounded-md text-sm font-medium">
                        Próxima
                    </a>
                </nav>
            <?php endif; ?>
        </div>
    </div>
</div>