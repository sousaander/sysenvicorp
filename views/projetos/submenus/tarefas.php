<?php
// Cálculos para o resumo e gráfico
$totalTarefas = count($tarefas);
$statusCounts = [
    'Concluída' => 0,
    'Em Andamento' => 0,
    'Pendente' => 0,
    'Cancelada' => 0
];

foreach ($tarefas as $t) {
    if (isset($statusCounts[$t['status']])) {
        $statusCounts[$t['status']]++;
    }
}

$progresso = $totalTarefas > 0 ? round(($statusCounts['Concluída'] / $totalTarefas) * 100) : 0;
?>
<div class="flex justify-between items-center mb-6">
    <h3 class="text-xl font-semibold text-gray-800">Etapas do Projeto</h3>

    <!-- Botão de Notificações (Exemplo de integração na view) -->
    <div class="relative mr-4" id="notification-container">
        <button id="notification-btn" class="relative text-gray-600 hover:text-violet-600 focus:outline-none">
            <i class='bx bx-bell text-2xl'></i>
            <span id="notification-badge" class="hidden absolute top-0 right-0 inline-flex items-center justify-center px-1.5 py-0.5 text-xs font-bold leading-none text-red-100 transform translate-x-1/4 -translate-y-1/4 bg-red-600 rounded-full">0</span>
        </button>
        <!-- Dropdown de Notificações -->
        <div id="notification-dropdown" class="hidden absolute right-0 mt-2 w-80 bg-white rounded-md shadow-lg overflow-hidden z-50 border border-gray-200">
            <div class="py-2 px-4 bg-gray-50 border-b border-gray-200 flex justify-between items-center">
                <span class="text-sm font-semibold text-gray-700">Notificações</span>
                <button id="mark-all-read" class="text-xs text-gray-500 hover:text-red-600 flex items-center gap-1 transition-colors" title="Limpar todas">
                    <i class='bx bx-trash'></i> Limpar
                </button>
            </div>
            <div id="notification-list" class="max-h-64 overflow-y-auto">
                <div class="py-4 text-center text-gray-500 text-sm">Nenhuma notificação nova.</div>
            </div>
        </div>
    </div>

    <div class="flex gap-3">
        <a href="<?php echo BASE_URL; ?>/projetos/relatorioTarefasPdf/<?php echo $projeto['id']; ?>" target="_blank" class="bg-white text-gray-700 border border-gray-300 px-4 py-2 rounded-md hover:bg-gray-50 font-medium shadow-sm flex items-center gap-2 transition-colors">
            <i class='bx bxs-file-pdf text-red-500 text-xl'></i>
            Exportar PDF
        </a>
        <button id="manage-tags-btn" class="bg-white text-gray-700 border border-gray-300 px-4 py-2 rounded-md hover:bg-gray-50 font-medium shadow-sm flex items-center gap-2 transition-colors">
            <i class='bx bx-purchase-tag text-xl'></i> Tags
        </button>
        <button id="open-tarefa-modal-btn" class="bg-violet-600 text-white px-4 py-2 rounded-md hover:bg-violet-700 font-medium shadow-sm flex items-center gap-2 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Nova Tarefa
        </button>
    </div>
</div>

<!-- Filtros -->
<form method="GET" action="<?php echo BASE_URL; ?>/projetos/detalhe/<?php echo $projeto['id']; ?>/tarefas" class="mb-6 bg-gray-50 p-4 rounded-lg border border-gray-200 flex flex-wrap gap-4 items-end">
    <div>
        <label for="status_tarefa" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
        <select name="status_tarefa" id="status_tarefa" class="border-gray-300 rounded-md shadow-sm focus:ring-violet-500 focus:border-violet-500 text-sm py-2 px-3">
            <option value="">Todos</option>
            <option value="Pendente" <?php echo ($filtros['status'] ?? '') === 'Pendente' ? 'selected' : ''; ?>>Pendente</option>
            <option value="Em Andamento" <?php echo ($filtros['status'] ?? '') === 'Em Andamento' ? 'selected' : ''; ?>>Em Andamento</option>
            <option value="Concluída" <?php echo ($filtros['status'] ?? '') === 'Concluída' ? 'selected' : ''; ?>>Concluída</option>
            <option value="Cancelada" <?php echo ($filtros['status'] ?? '') === 'Cancelada' ? 'selected' : ''; ?>>Cancelada</option>
        </select>
    </div>

    <div>
        <label for="responsavel_id_filter" class="block text-sm font-medium text-gray-700 mb-1">Responsável</label>
        <select name="responsavel_id" id="responsavel_id_filter" class="border-gray-300 rounded-md shadow-sm focus:ring-violet-500 focus:border-violet-500 text-sm py-2 px-3">
            <option value="">Todos</option>
            <?php if (isset($_SESSION['user_id'])): ?>
                <option value="<?php echo $_SESSION['user_id']; ?>" <?php echo ($filtros['responsavel_id'] ?? '') == $_SESSION['user_id'] ? 'selected' : ''; ?>>Minhas Tarefas</option>
            <?php endif; ?>
            <?php foreach ($usuarios as $usuario): ?>
                <option value="<?php echo $usuario['id']; ?>" <?php echo ($filtros['responsavel_id'] ?? '') == $usuario['id'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($usuario['nome']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <button type="submit" class="bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-50 text-sm font-medium shadow-sm">
        Filtrar
    </button>

    <?php if (!empty($filtros['status']) || !empty($filtros['responsavel_id'])): ?>
        <a href="<?php echo BASE_URL; ?>/projetos/detalhe/<?php echo $projeto['id']; ?>/tarefas" class="text-gray-600 hover:text-gray-800 text-sm font-medium py-2 ml-2">
            Limpar Filtros
        </a>
    <?php endif; ?>
</form>

<!-- Resumo e Gráfico de Progresso -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <!-- Cards de Contagem e Barra de Progresso -->
    <div class="lg:col-span-2 grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 flex flex-col justify-center items-center">
            <span class="text-3xl font-bold text-violet-600"><?php echo $totalTarefas; ?></span>
            <span class="text-sm text-gray-500">Total</span>
        </div>
        <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 flex flex-col justify-center items-center">
            <span class="text-3xl font-bold text-green-600"><?php echo $statusCounts['Concluída']; ?></span>
            <span class="text-sm text-gray-500">Concluídas</span>
        </div>
        <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 flex flex-col justify-center items-center">
            <span class="text-3xl font-bold text-yellow-600"><?php echo $statusCounts['Em Andamento']; ?></span>
            <span class="text-sm text-gray-500">Em Andamento</span>
        </div>
        <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 flex flex-col justify-center items-center">
            <span class="text-3xl font-bold text-gray-600"><?php echo $statusCounts['Pendente']; ?></span>
            <span class="text-sm text-gray-500">Pendentes</span>
        </div>

        <!-- Barra de Progresso Geral -->
        <div class="col-span-2 sm:col-span-4 bg-white p-4 rounded-lg shadow-sm border border-gray-200">
            <div class="flex justify-between items-center mb-2">
                <span class="text-sm font-medium text-gray-700">Progresso do Projeto</span>
                <span class="text-sm font-bold text-violet-600"><?php echo $progresso; ?>%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-4">
                <div class="bg-violet-600 h-4 rounded-full transition-all duration-500" style="width: <?php echo $progresso; ?>%"></div>
            </div>
        </div>
    </div>

    <!-- Gráfico de Rosca -->
    <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 flex flex-col items-center justify-center relative">
        <h4 class="text-sm font-semibold text-gray-700 mb-2 absolute top-4 left-4">Status</h4>
        <div class="relative h-40 w-40 mt-4">
            <canvas id="tarefasStatusChart"></canvas>
        </div>
    </div>
</div>

<!-- Lista de Tarefas -->
<div class="bg-white rounded-lg shadow overflow-hidden border border-gray-200">
    <?php if (!empty($tarefas)): ?>
        <ul class="divide-y divide-gray-200">
            <?php foreach ($tarefas as $tarefa): ?>
                <li class="p-4 hover:bg-gray-50 transition-colors duration-150">
                    <div class="flex items-center justify-between">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-3 mb-1">
                                <h4 class="text-lg font-medium text-gray-900 truncate"><?php echo htmlspecialchars($tarefa['titulo']); ?></h4>
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-medium
                                    <?php
                                    switch ($tarefa['prioridade']) {
                                        case 'Urgente':
                                            echo 'bg-red-100 text-red-800';
                                            break;
                                        case 'Alta':
                                            echo 'bg-orange-100 text-orange-800';
                                            break;
                                        case 'Média':
                                            echo 'bg-blue-100 text-blue-800';
                                            break;
                                        default:
                                            echo 'bg-gray-100 text-gray-800';
                                    }
                                    ?>">
                                    <?php echo $tarefa['prioridade']; ?>
                                </span>
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-medium
                                    <?php
                                    switch ($tarefa['status']) {
                                        case 'Concluída':
                                            echo 'bg-green-100 text-green-800';
                                            break;
                                        case 'Em Andamento':
                                            echo 'bg-yellow-100 text-yellow-800';
                                            break;
                                        case 'Cancelada':
                                            echo 'bg-gray-100 text-gray-800 line-through';
                                            break;
                                        default:
                                            echo 'bg-gray-100 text-gray-600';
                                    }
                                    ?>">
                                    <?php echo $tarefa['status']; ?>
                                </span>
                            </div>
                            <div class="flex flex-wrap gap-1 mb-2">
                                <?php foreach ($tarefa['tags'] as $tag): ?>
                                    <span class="px-2 py-0.5 rounded text-xs font-medium text-white" style="background-color: <?php echo $tag['cor']; ?>"><?php echo htmlspecialchars($tag['nome']); ?></span>
                                <?php endforeach; ?>
                            </div>
                            <p class="text-sm text-gray-500 mb-2"><?php echo nl2br(htmlspecialchars($tarefa['descricao'])); ?></p>
                            <div class="flex items-center gap-4 text-xs text-gray-500">
                                <?php if ($tarefa['responsavel_nome']): ?>
                                    <span class="flex items-center gap-1">
                                        <i class='bx bx-user'></i> <?php echo htmlspecialchars($tarefa['responsavel_nome']); ?>
                                    </span>
                                <?php endif; ?>
                                <?php if ($tarefa['data_fim']): ?>
                                    <span class="flex items-center gap-1 <?php echo (strtotime($tarefa['data_fim']) < time() && $tarefa['status'] !== 'Concluída') ? 'text-red-600 font-bold' : ''; ?>">
                                        <i class='bx bx-calendar'></i> Prazo: <?php echo date('d/m/Y', strtotime($tarefa['data_fim'])); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 ml-4">
                            <button class="open-comments-btn text-gray-500 hover:text-violet-600 p-2 rounded hover:bg-violet-50"
                                data-id="<?php echo $tarefa['id']; ?>" title="Comentários">
                                <i class='bx bx-message-rounded-dots text-xl'></i>
                            </button>
                            <button class="open-checklist-btn text-gray-500 hover:text-green-600 p-2 rounded hover:bg-green-50"
                               data-id="<?php echo $tarefa['id']; ?>" title="Checklist">
                                <i class='bx bx-list-check text-xl'></i>
                            </button>
                            <button class="open-deps-btn text-gray-500 hover:text-orange-600 p-2 rounded hover:bg-orange-50"
                                data-id="<?php echo $tarefa['id']; ?>" title="Dependências">
                                <i class='bx bx-link text-xl'></i>
                            </button>
                            <button class="edit-tarefa-btn text-indigo-600 hover:text-indigo-900 p-2 rounded hover:bg-indigo-50"
                                data-tarefa='<?php echo json_encode($tarefa, JSON_HEX_APOS | JSON_HEX_QUOT); ?>'>
                                <i class='bx bx-edit text-xl'></i>
                            </button>
                            <a href="<?php echo BASE_URL; ?>/projetos/excluirTarefa/<?php echo $tarefa['id']; ?>/<?php echo $projeto['id']; ?>"
                                onclick="return confirm('Tem certeza que deseja excluir esta tarefa?')"
                                class="text-red-600 hover:text-red-900 p-2 rounded hover:bg-red-50">
                                <i class='bx bx-trash text-xl'></i>
                            </a>
                        </div>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <div class="text-center py-12">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-400 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
            </svg>
            <h4 class="text-lg font-medium text-gray-700">Nenhuma tarefa encontrada</h4>
            <p class="text-gray-500 mt-1">Comece criando tarefas para organizar o trabalho da equipe.</p>
        </div>
    <?php endif; ?>
</div>

<!-- Histórico de Atividades (Logs) -->
<div class="mt-8">
    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
        <i class='bx bx-history text-gray-500'></i> Atividades Recentes
    </h3>
    <div class="bg-white rounded-lg shadow border border-gray-200 overflow-hidden">
        <?php if (!empty($logs)): ?>
            <ul class="divide-y divide-gray-200">
                <?php foreach ($logs as $log): ?>
                    <li class="p-3 hover:bg-gray-50 text-sm">
                        <div class="flex justify-between">
                            <div class="flex gap-2">
                                <span class="font-medium text-gray-900"><?php echo htmlspecialchars($log['usuario_nome'] ?? 'Sistema'); ?></span>
                                <span class="text-gray-500"><?php echo htmlspecialchars($log['acao']); ?></span>
                            </div>
                            <span class="text-xs text-gray-400"><?php echo date('d/m/Y H:i', strtotime($log['created_at'])); ?></span>
                        </div>
                        <div class="text-gray-600 mt-1">
                            <?php echo htmlspecialchars($log['descricao']); ?>
                            <?php if ($log['tarefa_titulo'] && $log['acao'] !== 'Excluiu Tarefa'): ?>
                                <span class="text-xs text-gray-400 block mt-0.5">Em: <?php echo htmlspecialchars($log['tarefa_titulo']); ?></span>
                            <?php endif; ?>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>

            <!-- Paginação -->
            <?php if (isset($total_pages_logs) && $total_pages_logs > 1): ?>
                <?php 
                // Lógica de cálculo de links unificada
                $queryParams = $_GET;
                unset($queryParams['url']); // Remove parâmetro de rota
                
                $prevPage = max(1, $current_page_logs - 1);
                $nextPage = min($total_pages_logs, $current_page_logs + 1);
                
                $queryParams['page_logs'] = $prevPage;
                $prevLink = BASE_URL . "/projetos/detalhe/{$projeto['id']}/tarefas?" . http_build_query($queryParams);
                
                $queryParams['page_logs'] = $nextPage;
                $nextLink = BASE_URL . "/projetos/detalhe/{$projeto['id']}/tarefas?" . http_build_query($queryParams);
                ?>
                <div class="px-4 py-3 border-t border-gray-200 flex items-center justify-between sm:px-6">
                    <!-- Pagination View -->
                    <div class="flex-1 flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-700">
                                Página <span class="font-medium"><?php echo $current_page_logs; ?></span> de <span class="font-medium"><?php echo $total_pages_logs; ?></span>
                            </p>
                        </div>
                        <div>
                            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                                <a href="<?php echo $prevLink; ?>" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 <?php echo $current_page_logs == 1 ? 'pointer-events-none opacity-50' : ''; ?>">
                                    <span class="sr-only">Anterior</span>
                                    <i class='bx bx-chevron-left text-xl'></i>
                                </a>

                                <?php
                                for ($i = 1; $i <= $total_pages_logs; $i++): 
                                    $queryParams['page_logs'] = $i;
                                    $link = BASE_URL . "/projetos/detalhe/{$projeto['id']}/tarefas?" . http_build_query($queryParams);
                                    $activeClass = ($i == $current_page_logs) ? 'z-10 bg-violet-50 border-violet-500 text-violet-600' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50';
                                ?>
                                    <a href="<?php echo $link; ?>" class="relative inline-flex items-center px-4 py-2 border text-sm font-medium <?php echo $activeClass; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                <?php endfor; ?>

                                <a href="<?php echo $nextLink; ?>" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 <?php echo $current_page_logs == $total_pages_logs ? 'pointer-events-none opacity-50' : ''; ?>">
                                    <span class="sr-only">Próximo</span>
                                    <i class='bx bx-chevron-right text-xl'></i>
                                </a>
                            </nav>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="p-4 text-center text-gray-500 text-sm">Nenhuma atividade registrada recentemente.</div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal para Adicionar/Editar Tarefa -->
<div id="tarefa-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-center justify-center">
    <div class="relative p-5 border w-full max-w-lg shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center pb-3 border-b">
            <p id="modal-title" class="text-xl font-bold text-gray-800">Nova Tarefa</p>
            <div id="close-tarefa-modal" class="cursor-pointer z-50 text-gray-500 hover:text-gray-800">
                <svg class="fill-current" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                    <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z" />
                </svg>
            </div>
        </div>
        <div class="mt-4">
            <form action="<?php echo BASE_URL; ?>/projetos/salvarTarefa" method="POST">
                <input type="hidden" name="id" id="tarefa_id">
                <input type="hidden" name="projeto_id" value="<?php echo $projeto['id']; ?>">

                <div class="space-y-4">
                    <div>
                        <label for="titulo" class="block text-sm font-medium text-gray-700">Título da Tarefa <span class="text-red-500">*</span></label>
                        <input type="text" name="titulo" id="titulo" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm p-2 focus:ring-violet-500 focus:border-violet-500">
                    </div>

                    <div>
                        <label for="descricao" class="block text-sm font-medium text-gray-700">Descrição</label>
                        <textarea name="descricao" id="descricao" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm p-2 focus:ring-violet-500 focus:border-violet-500"></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                            <select name="status" id="status" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm p-2 bg-white">
                                <option value="Pendente">Pendente</option>
                                <option value="Em Andamento">Em Andamento</option>
                                <option value="Concluída">Concluída</option>
                                <option value="Cancelada">Cancelada</option>
                            </select>
                        </div>
                        <div>
                            <label for="prioridade" class="block text-sm font-medium text-gray-700">Prioridade</label>
                            <select name="prioridade" id="prioridade" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm p-2 bg-white">
                                <option value="Baixa">Baixa</option>
                                <option value="Média" selected>Média</option>
                                <option value="Alta">Alta</option>
                                <option value="Urgente">Urgente</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="data_inicio" class="block text-sm font-medium text-gray-700">Data Início</label>
                            <input type="date" name="data_inicio" id="data_inicio" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm p-2">
                        </div>
                        <div>
                            <label for="data_fim" class="block text-sm font-medium text-gray-700">Prazo Final</label>
                            <input type="date" name="data_fim" id="data_fim" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm p-2">
                        </div>
                    </div>

                    <div>
                        <label for="responsavel_id" class="block text-sm font-medium text-gray-700">Responsável</label>
                        <select name="responsavel_id" id="responsavel_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm p-2 bg-white">
                            <option value="">Selecione um responsável...</option>
                            <?php if (!empty($usuarios)): ?>
                                <?php foreach ($usuarios as $usuario): ?>
                                    <option value="<?php echo $usuario['id']; ?>"><?php echo htmlspecialchars($usuario['nome']); ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Etiquetas (Tags)</label>
                        <div class="flex flex-wrap gap-2" id="tags-container">
                            <?php foreach ($tags as $tag): ?>
                                <label class="inline-flex items-center px-3 py-1 rounded-full border cursor-pointer select-none transition-colors hover:opacity-80" style="background-color: <?php echo $tag['cor']; ?>20; border-color: <?php echo $tag['cor']; ?>;">
                                    <input type="checkbox" name="tags[]" value="<?php echo $tag['id']; ?>" class="form-checkbox h-4 w-4 text-violet-600 rounded border-gray-300 focus:ring-violet-500 mr-2">
                                    <span class="text-sm font-medium" style="color: <?php echo $tag['cor']; ?>;"><?php echo htmlspecialchars($tag['nome']); ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end pt-6 mt-4 border-t">
                    <button type="button" id="cancel-tarefa-modal" class="bg-white text-gray-700 border border-gray-300 font-medium py-2 px-4 rounded-md hover:bg-gray-50 mr-3">Cancelar</button>
                    <button type="submit" class="bg-violet-600 hover:bg-violet-700 text-white font-bold py-2 px-4 rounded-md shadow-sm">Salvar Tarefa</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de Comentários -->
<div id="comments-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-center justify-center">
    <div class="relative p-5 border w-full max-w-lg shadow-lg rounded-md bg-white flex flex-col max-h-[80vh]">
        <div class="flex justify-between items-center pb-3 border-b">
            <p class="text-xl font-bold text-gray-800">Comentários da Tarefa</p>
            <div id="close-comments-modal" class="cursor-pointer z-50 text-gray-500 hover:text-gray-800">
                <svg class="fill-current" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                    <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z" />
                </svg>
            </div>
        </div>

        <!-- Lista de Comentários -->
        <div id="comments-list" class="flex-1 overflow-y-auto py-4 space-y-4">
            <!-- Comentários serão carregados aqui via JS -->
            <div class="text-center text-gray-500">Carregando...</div>
        </div>

        <!-- Formulário de Novo Comentário -->
        <div class="mt-4 pt-4 border-t">
            <textarea id="new-comment-text" rows="2" class="w-full border-gray-300 rounded-md shadow-sm p-2 focus:ring-violet-500 focus:border-violet-500" placeholder="Escreva um comentário..."></textarea>
            <button id="submit-comment-btn" class="mt-2 bg-violet-600 text-white px-4 py-2 rounded-md hover:bg-violet-700 text-sm font-medium w-full">Enviar Comentário</button>
        </div>
    </div>
</div>

<!-- Modal de Checklist -->
<div id="checklist-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-center justify-center">
    <div class="relative p-5 border w-full max-w-lg shadow-lg rounded-md bg-white flex flex-col max-h-[80vh]">
        <div class="flex justify-between items-center pb-3 border-b">
            <p class="text-xl font-bold text-gray-800">Checklist da Tarefa</p>
            <div id="close-checklist-modal" class="cursor-pointer z-50 text-gray-500 hover:text-gray-800">
                <svg class="fill-current" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                    <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z" />
                </svg>
            </div>
        </div>

        <!-- Lista de Itens do Checklist -->
        <div id="checklist-list" class="flex-1 overflow-y-auto py-4 space-y-2">
            <!-- Itens serão carregados aqui via JS -->
            <div class="text-center text-gray-500">Carregando...</div>
        </div>

        <!-- Formulário de Novo Item -->
        <div class="mt-4 pt-4 border-t flex gap-2">
            <input type="text" id="new-checklist-item" class="flex-1 border-gray-300 rounded-md shadow-sm p-2 focus:ring-violet-500 focus:border-violet-500" placeholder="Novo item...">
            <button id="add-checklist-btn" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 text-sm font-medium">
                <i class='bx bx-plus'></i>
            </button>
        </div>
    </div>
</div>

<!-- Modal de Dependências -->
<div id="deps-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-center justify-center">
    <div class="relative p-5 border w-full max-w-lg shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center pb-3 border-b">
            <p class="text-xl font-bold text-gray-800">Dependências da Tarefa</p>
            <div id="close-deps-modal" class="cursor-pointer z-50 text-gray-500 hover:text-gray-800">
                <svg class="fill-current" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                    <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z" />
                </svg>
            </div>
        </div>

        <div class="py-4">
            <p class="text-sm text-gray-600 mb-2">Esta tarefa só poderá ser iniciada após a conclusão das seguintes:</p>
            <!-- Lista de Dependências -->
            <div id="deps-list" class="space-y-2 max-h-[50vh] overflow-y-auto">
                <div class="text-center text-gray-500">Carregando...</div>
            </div>
        </div>

        <!-- Adicionar Nova Dependência -->
        <div class="pt-4 border-t">
            <label class="block text-sm font-medium text-gray-700 mb-1">Adicionar Dependência</label>
            <div class="flex gap-2">
                <select id="new-dep-select" class="flex-1 border-gray-300 rounded-md shadow-sm p-2 text-sm">
                    <option value="">Selecione uma tarefa...</option>
                    <!-- Opções preenchidas via JS -->
                </select>
                <button id="add-dep-btn" class="bg-orange-600 text-white px-3 py-2 rounded-md hover:bg-orange-700 text-sm font-medium">Adicionar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Gerenciamento de Tags -->
<div id="tags-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-center justify-center">
    <div class="relative p-5 border w-full max-w-md shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center pb-3 border-b">
            <p class="text-xl font-bold text-gray-800">Gerenciar Tags</p>
            <div id="close-tags-modal" class="cursor-pointer z-50 text-gray-500 hover:text-gray-800">
                <svg class="fill-current" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                    <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z" />
                </svg>
            </div>
        </div>
        <div class="mt-4">
            <div class="flex gap-2 mb-4">
                <input type="text" id="new-tag-name" placeholder="Nome da Tag" class="flex-1 border-gray-300 rounded-md shadow-sm p-2 text-sm">
                <input type="color" id="new-tag-color" value="#6B7280" class="h-9 w-12 p-1 border-gray-300 rounded-md shadow-sm cursor-pointer">
                <button id="add-tag-btn" class="bg-violet-600 text-white px-3 py-2 rounded-md hover:bg-violet-700 text-sm font-medium">Adicionar</button>
            </div>
            <div class="max-h-60 overflow-y-auto space-y-2" id="manage-tags-list">
                <?php foreach ($tags as $tag): ?>
                    <div class="flex items-center justify-between bg-gray-50 p-2 rounded border border-gray-200">
                        <div class="flex items-center gap-2">
                            <span class="w-4 h-4 rounded-full" style="background-color: <?php echo $tag['cor']; ?>"></span>
                            <span class="text-sm text-gray-700"><?php echo htmlspecialchars($tag['nome']); ?></span>
                        </div>
                        <button class="delete-tag-btn text-red-500 hover:text-red-700" data-id="<?php echo $tag['id']; ?>">
                            <i class='bx bx-trash'></i>
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const modal = document.getElementById('tarefa-modal');
        const openBtn = document.getElementById('open-tarefa-modal-btn');
        const closeBtn = document.getElementById('close-tarefa-modal');
        const cancelBtn = document.getElementById('cancel-tarefa-modal');
        const form = modal.querySelector('form');
        const modalTitle = document.getElementById('modal-title');
        const editBtns = document.querySelectorAll('.edit-tarefa-btn');
        const tagsCheckboxes = document.querySelectorAll('input[name="tags[]"]');

        const resetForm = () => {
            form.reset();
            document.getElementById('tarefa_id').value = '';
            // Reset tags
            tagsCheckboxes.forEach(cb => {
                cb.checked = false;
                cb.parentElement.style.backgroundColor = cb.parentElement.style.borderColor + '20'; // Reset opacity
            });
            modalTitle.textContent = 'Nova Tarefa';
        };

        const openModal = () => modal.classList.remove('hidden');
        const closeModal = () => modal.classList.add('hidden');

        openBtn.addEventListener('click', () => {
            resetForm();
            openModal();
        });

        editBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                const tarefa = JSON.parse(btn.getAttribute('data-tarefa'));

                document.getElementById('tarefa_id').value = tarefa.id;
                document.getElementById('titulo').value = tarefa.titulo;
                document.getElementById('descricao').value = tarefa.descricao || '';
                document.getElementById('status').value = tarefa.status;
                document.getElementById('prioridade').value = tarefa.prioridade;
                document.getElementById('data_inicio').value = tarefa.data_inicio || '';
                document.getElementById('data_fim').value = tarefa.data_fim || '';
                document.getElementById('responsavel_id').value = tarefa.responsavel_id || '';

                // Preencher tags
                const tarefaTags = tarefa.tags ? tarefa.tags.map(t => t.id) : [];
                tagsCheckboxes.forEach(cb => {
                    const isChecked = tarefaTags.includes(parseInt(cb.value));
                    cb.checked = isChecked;
                    // Visual feedback
                    const color = cb.parentElement.style.borderColor;
                    cb.parentElement.style.backgroundColor = isChecked ? color + '50' : color + '20';
                });

                modalTitle.textContent = 'Editar Tarefa';
                openModal();
            });
        });

        closeBtn.addEventListener('click', closeModal);
        cancelBtn.addEventListener('click', closeModal);
        modal.addEventListener('click', (event) => {
            if (event.target === modal) closeModal();
        });

        // Visual feedback for tags selection
        tagsCheckboxes.forEach(cb => {
            cb.addEventListener('change', (e) => {
                const color = e.target.parentElement.style.borderColor;
                e.target.parentElement.style.backgroundColor = e.target.checked ? color + '50' : color + '20';
            });
        });

        // Inicialização do Gráfico de Status
        const ctx = document.getElementById('tarefasStatusChart').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Concluída', 'Em Andamento', 'Pendente', 'Cancelada'],
                datasets: [{
                    data: [
                        <?php echo $statusCounts['Concluída']; ?>,
                        <?php echo $statusCounts['Em Andamento']; ?>,
                        <?php echo $statusCounts['Pendente']; ?>,
                        <?php echo $statusCounts['Cancelada']; ?>
                    ],
                    backgroundColor: ['#10B981', '#F59E0B', '#3B82F6', '#9CA3AF'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                cutout: '75%'
            }
        });

        // --- Lógica de Comentários ---
        const commentsModal = document.getElementById('comments-modal');
        const closeCommentsBtn = document.getElementById('close-comments-modal');
        const commentsList = document.getElementById('comments-list');
        const newCommentText = document.getElementById('new-comment-text');
        const submitCommentBtn = document.getElementById('submit-comment-btn');
        let currentTarefaId = null;

        // Abrir modal de comentários
        document.querySelectorAll('.open-comments-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                currentTarefaId = btn.getAttribute('data-id');
                commentsModal.classList.remove('hidden');
                loadComments(currentTarefaId);
            });
        });

        // Fechar modal
        closeCommentsBtn.addEventListener('click', () => commentsModal.classList.add('hidden'));
        commentsModal.addEventListener('click', (e) => {
            if (e.target === commentsModal) commentsModal.classList.add('hidden');
        });

        // Carregar comentários
        function loadComments(tarefaId) {
            commentsList.innerHTML = '<div class="text-center text-gray-500">Carregando...</div>';
            fetch(`<?php echo BASE_URL; ?>/projetos/getComentariosTarefa/${tarefaId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        renderComments(data.data);
                    } else {
                        commentsList.innerHTML = '<div class="text-center text-red-500">Erro ao carregar comentários.</div>';
                    }
                });
        }

        // Renderizar comentários
        function renderComments(comments) {
            if (comments.length === 0) {
                commentsList.innerHTML = '<div class="text-center text-gray-400 text-sm">Nenhum comentário ainda. Seja o primeiro!</div>';
                return;
            }
            commentsList.innerHTML = comments.map(c => `
                <div class="bg-gray-50 p-3 rounded-lg fwns-start mb-1">
                        <span class="font-semibold text-sm text-gray-800">${c.usuario_nome}</span>
                        <span class="text-xs text-gray-500">${new Date(c.created_at).toLocaleString('pt-BR')}</span>
                    </div>
                    <div }</p>
                    </div>
                    ${ c.usuario_id == <?php echo $_SESSION['user_id'] ?? 'null'; ?> ? `
                    <div class="comment-actions absolute top-2 right-2 flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                        <button class="edit-comment-btn text-blue-600 hover:text-blue-800 p-1" data-comment-id="${c.id}" title="Editar">
                            <i class='bx bx-pencil text-base'></i>
                        </button>
                        <button class="delete-comment-btn text-red-600 hover:text-red-800 p-1" data-comment-id="${c.id}" title="Excluir">
                            <i class='bx bx-trash text-base'></i>
                        </button>
                    </div>
                    ` : '' }
                </div>
            `).join('');
            commentsList.scrollTop = commentsList.scrollHeight;
            attachCommentActionListeners();
        }

        function attachCommentActionListeners() {
            // Botão Excluir
            document.querySelectorAll('.delete-comment-btn').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    if (!confirm('Tem certeza que deseja excluir este comentário?')) return;
                    
                    const commentId = e.currentTarget.getAttribute('data-comment-id');
                    fetch(`<?php echo BASE_URL; ?>/projetos/excluirComentario/${commentId}`, { method: 'POST' })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                loadComments(currentTarefaId);
                            } else {
                                alert(data.message || 'Erro ao excluir comentário.');
                            }
                        });
                });
            });

            // Botão Editar
            document.querySelectorAll('.edit-comment-btn').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    const commentId = e.currentTarget.getAttribute('data-comment-id');
                    const commentDiv = document.getElementById(`comment-${commentId}`);
                    const contentDiv = commentDiv.querySelector('.comment-content');
                    const originalText = contentDiv.querySelector('p').innerText;

                    // Substitui o conteúdo pelo textarea e botões
                    contentDiv.innerHTML = `
                        <textarea class="edit-comment-textarea w-full border-gray-300 rounded-md shadow-sm p-2 text-sm" rows="3">${originalText}</textarea>
                        <div class="mt-2 flex justify-end gap-2">
                            <button class="cancel-edit-btn text-sm text-gray-600 hover:text-gray-800 px-3 py-1">Cancelar</button>
                            <button class="save-comment-btn text-sm bg-blue-600 text-white px-3 py-1 rounded-md hover:bg-blue-700">Salvar</button>
                        </div>
                    `;

                    contentDiv.querySelector('.cancel-edit-btn').addEventListener('click', () => {
                        contentDiv.innerHTML = `<p class="text-sm text-gray-700 whitespace-pre-wrap">${originalText}</p>`;
                    });

                    contentDiv.querySelector('.save-comment-btn').addEventListener('click', () => {
                        const newText = contentDiv.querySelector('.edit-comment-textarea').value.trim();
                        if (!newText) return;

                        const formData = new FormData();
                        formData.append('comentario_id', commentId);
                        formData.append('texto', newText);

                        fetch('<?php echo BASE_URL; ?>/projetos/atualizarComentario', { method: 'POST', body: formData })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) loadComments(currentTarefaId);
                                else alert(data.message || 'Erro ao salvar alteração.');
                            });
                    });
                });
            });
        }

        // Enviar comentário
        submitCommentBtn.addEventListener('click', () => {
            const text = newCommentText.value.trim();
            if (!text || !currentTarefaId) return;

            const formData = new FormData();
            formData.append('tarefa_id', currentTarefaId);
            formData.append('comentario', text);

            fetch('<?php echo BASE_URL; ?>/projetos/salvarComentario', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        newCommentText.value = '';
                        loadComments(currentTarefaId);
                    } else {
                        alert('Erro ao enviar comentário.');
                    }
                });
        });

        // --- Lógica de Checklist ---
        const checklistModal = document.getElementById('checklist-modal');
        const closeChecklistBtn = document.getElementById('close-checklist-modal');
        const checklistList = document.getElementById('checklist-list');
        const newChecklistItemInput = document.getElementById('new-checklist-item');
        const addChecklistBtn = document.getElementById('add-checklist-btn');
        let currentChecklistTarefaId = null;

        document.querySelectorAll('.open-checklist-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                currentChecklistTarefaId = btn.getAttribute('data-id');
                checklistModal.classList.remove('hidden');
                loadChecklist(currentChecklistTarefaId);
            });
        });

        closeChecklistBtn.addEventListener('click', () => checklistModal.classList.add('hidden'));
        checklistModal.addEventListener('click', (e) => {
            if (e.target === checklistModal) checklistModal.classList.add('hidden');
        });

        function loadChecklist(tarefaId) {
            checklistList.innerHTML = '<div class="text-center text-gray-500">Carregando...</div>';
            fetch(`<?php echo BASE_URL; ?>/projetos/getChecklist/${tarefaId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        renderChecklist(data.data);
                    } else {
                        checklistList.innerHTML = '<div class="text-center text-red-500">Erro ao carregar checklist.</div>';
                    }
                });
        }

        function renderChecklist(items) {
            if (items.length === 0) {
                checklistList.innerHTML = '<div class="text-center text-gray-400 text-sm">Nenhum item no checklist.</div>';
                return;
            }
            checklistList.innerHTML = items.map(item => `
                <div class="flex items-center justify-between bg-gray-50 p-2 rounded border border-gray-200">
                    <div class="flex items-center gap-3">
                        <input type="checkbox" class="toggle-checklist h-5 w-5 text-green-600 rounded focus:ring-green-500" 
                            data-id="${item.id}" ${item.concluido == 1 ? 'checked' : ''}>
                        <span class="${item.concluido == 1 ? 'line-through text-gray-400' : 'text-gray-700'}">${item.descricao}</span>
                    </div>
                    <button class="delete-checklist-btn text-red-500 hover:text-red-700" data-id="${item.id}">
                        <i class='bx bx-trash'></i>
                    </button>
                </div>
            `).join('');

            // Reattach events
            document.querySelectorAll('.toggle-checklist').forEach(cb => {
                cb.addEventListener('change', (e) => {
                    const id = e.target.getAttribute('data-id');
                    const status = e.target.checked ? 1 : 0;
                    const formData = new FormData();
                    formData.append('id', id);
                    formData.append('status', status);
                    fetch('<?php echo BASE_URL; ?>/projetos/toggleChecklistItem', {
                            method: 'POST',
                            body: formData
                        })
                        .then(() => loadChecklist(currentChecklistTarefaId));
                });
            });

            document.querySelectorAll('.delete-checklist-btn').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    if (!confirm('Excluir item?')) return;
                    const id = btn.getAttribute('data-id');
                    fetch(`<?php echo BASE_URL; ?>/projetos/excluirChecklistItem/${id}`)
                        .then(() => loadChecklist(currentChecklistTarefaId));
                });
            });
        }

        addChecklistBtn.addEventListener('click', () => {
            const text = newChecklistItemInput.value.trim();
            if (!text || !currentChecklistTarefaId) return;

            const formData = new FormData();
            formData.append('tarefa_id', currentChecklistTarefaId);
            formData.append('descricao', text);

            fetch('<?php echo BASE_URL; ?>/projetos/salvarChecklistItem', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        newChecklistItemInput.value = '';
                        loadChecklist(currentChecklistTarefaId);
                    }
                });
        });

        // --- Lógica de Dependências ---
        const depsModal = document.getElementById('deps-modal');
        const closeDepsBtn = document.getElementById('close-deps-modal');
        const depsList = document.getElementById('deps-list');
        const newDepSelect = document.getElementById('new-dep-select');
        const addDepBtn = document.getElementById('add-dep-btn');
        let currentDepTarefaId = null;

        // Todas as tarefas disponíveis no projeto (para o select)
        const allTarefas = <?php echo json_encode($tarefas); ?>;

        document.querySelectorAll('.open-deps-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                currentDepTarefaId = btn.getAttribute('data-id');
                depsModal.classList.remove('hidden');
                populateDepSelect(currentDepTarefaId);
                loadDeps(currentDepTarefaId);
            });
        });

        closeDepsBtn.addEventListener('click', () => depsModal.classList.add('hidden'));
        depsModal.addEventListener('click', (e) => {
            if (e.target === depsModal) depsModal.classList.add('hidden');
        });

        function populateDepSelect(tarefaId) {
            newDepSelect.innerHTML = '<option value="">Selecione uma tarefa...</option>';
            allTarefas.forEach(t => {
                if (t.id != tarefaId) { // Não pode depender de si mesma
                    newDepSelect.innerHTML += `<option value="${t.id}">${t.titulo}</option>`;
                }
            });
        }

        function loadDeps(tarefaId) {
            depsList.innerHTML = '<div class="text-center text-gray-500">Carregando...</div>';
            fetch(`<?php echo BASE_URL; ?>/projetos/getDependencias/${tarefaId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        renderDeps(data.data);
                    } else {
                        depsList.innerHTML = '<div class="text-center text-red-500">Erro ao carregar dependências.</div>';
                    }
                });
        }

        function renderDeps(items) {
            if (items.length === 0) {
                depsList.innerHTML = '<div class="text-center text-gray-400 text-sm">Nenhuma dependência configurada.</div>';
                return;
            }
            depsList.innerHTML = items.map(item => `
                <div class="flex items-center justify-between bg-gray-50 p-2 rounded border border-gray-200">
                    <div class="flex flex-col">
                        <span class="text-sm font-medium text-gray-800">${item.titulo}</span>
                        <span class="text-xs ${item.status === 'Concluída' ? 'text-green-600' : 'text-red-500'}">Status: ${item.status}</span>
                    </div>
                    <button class="delete-dep-btn text-red-500 hover:text-red-700" data-id="${item.id}">
                        <i class='bx bx-trash'></i>
                    </button>
                </div>
            `).join('');

            document.querySelectorAll('.delete-dep-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    if (!confirm('Remover dependência?')) return;
                    fetch(`<?php echo BASE_URL; ?>/projetos/excluirDependencia/${btn.getAttribute('data-id')}`)
                        .then(() => loadDeps(currentDepTarefaId));
                });
            });
        }

        addDepBtn.addEventListener('click', () => {
            const depId = newDepSelect.value;
            if (!depId || !currentDepTarefaId) return;

            const formData = new FormData();
            formData.append('tarefa_id', currentDepTarefaId);
            formData.append('dependencia_id', depId);

            fetch('<?php echo BASE_URL; ?>/projetos/salvarDependencia', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        newDepSelect.value = '';
                        loadDeps(currentDepTarefaId);
                    } else {
                        alert('Erro ao adicionar dependência.');
                    }
                });
        });

        // --- Gerenciamento de Tags ---
        const tagsModal = document.getElementById('tags-modal');
        const manageTagsBtn = document.getElementById('manage-tags-btn');
        const closeTagsBtn = document.getElementById('close-tags-modal');
        const addTagBtn = document.getElementById('add-tag-btn');
        const newTagName = document.getElementById('new-tag-name');
        const newTagColor = document.getElementById('new-tag-color');

        manageTagsBtn.addEventListener('click', () => tagsModal.classList.remove('hidden'));
        closeTagsBtn.addEventListener('click', () => tagsModal.classList.add('hidden'));
        tagsModal.addEventListener('click', (e) => {
            if (e.target === tagsModal) tagsModal.classList.add('hidden');
        });

        addTagBtn.addEventListener('click', () => {
            const name = newTagName.value.trim();
            const color = newTagColor.value;
            if (!name) return;

            const formData = new FormData();
            formData.append('projeto_id', '<?php echo $projeto['id']; ?>');
            formData.append('nome', name);
            formData.append('cor', color);

            fetch('<?php echo BASE_URL; ?>/projetos/salvarTag', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload(); // Recarrega para atualizar listas
                    } else {
                        alert('Erro ao salvar tag.');
                    }
                });
        });

        document.querySelectorAll('.delete-tag-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                if (!confirm('Excluir esta tag?')) return;
                fetch(`<?php echo BASE_URL; ?>/projetos/excluirTag/${btn.getAttribute('data-id')}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) location.reload();
                    });
            });
        });

        // --- Sistema de Notificações (Polling) ---
        const notificationBtn = document.getElementById('notification-btn');
        const notificationDropdown = document.getElementById('notification-dropdown');
        const notificationBadge = document.getElementById('notification-badge');
        const notificationList = document.getElementById('notification-list');
        const markAllReadBtn = document.getElementById('mark-all-read');

        // Toggle dropdown
        notificationBtn.addEventListener('click', () => {
            notificationDropdown.classList.toggle('hidden');
        });

        // Fechar dropdown ao clicar fora
        document.addEventListener('click', (e) => {
            if (!notificationBtn.contains(e.target) && !notificationDropdown.contains(e.target)) {
                notificationDropdown.classList.add('hidden');
            }
        });

        function checkNotifications() {
            fetch('<?php echo BASE_URL; ?>/notificacoes/check')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateNotificationsUI(data.data);
                    }
                });
        }

        function updateNotificationsUI(notificacoes) {
            const count = notificacoes.length;
            if (count > 0) {
                notificationBadge.textContent = count;
                notificationBadge.classList.remove('hidden');

                notificationList.innerHTML = notificacoes.map(n => `
                    <div class="p-4 border-b border-gray-100 hover:bg-gray-50 cursor-pointer transition-colors duration-200" onclick="markAsRead(${n.id}, '${n.link || '#'}')">
                        <h6 class="text-sm font-bold text-gray-800 mb-1">${n.titulo}</h6>
                        <p class="text-sm text-gray-600 leading-relaxed mb-2">${n.mensagem}</p>
                        <p class="text-xs text-gray-400 text-right flex justify-end items-center gap-1">
                            <i class='bx bx-time-five'></i>
                            ${new Date(n.created_at).toLocaleString('pt-BR')}
                        </p>
                    </div>
                `).join('');
            } else {
                notificationBadge.classList.add('hidden');
                notificationList.innerHTML = '<div class="py-4 text-center text-gray-500 text-sm">Nenhuma notificação nova.</div>';
            }
        }

        window.markAsRead = function(id, link) {
            const formData = new FormData();
            formData.append('id', id);
            fetch('<?php echo BASE_URL; ?>/notificacoes/marcarLida', {
                    method: 'POST',
                    body: formData
                })
                .then(() => {
                    if (link && link !== '#') window.location.href = link;
                    checkNotifications();
                });
        };

        markAllReadBtn.addEventListener('click', () => {
            fetch('<?php echo BASE_URL; ?>/notificacoes/marcarLida', {
                    method: 'POST'
                })
                .then(() => checkNotifications());
        });

        // Inicia polling a cada 5 segundos
        checkNotifications();
        setInterval(checkNotifications, 5000);
    });
</script>