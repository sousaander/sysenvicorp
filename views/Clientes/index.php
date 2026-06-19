<!-- Google Font: Plus Jakarta Sans -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>
  :root {
    --cl-bg: var(--db-bg, #f0f4fa);
    --cl-surface: var(--db-surface, #ffffff);
    --cl-border: var(--db-border, #e2e8f0);
    --cl-accent: var(--db-accent, #2563eb);
    --cl-text: var(--db-text, #1e293b);
    --cl-text2: var(--db-text2, #475569);
    --cl-green: var(--db-green, #10b981);
    --cl-red: var(--db-red, #ef4444);
    --cl-orange: var(--db-orange, #f59e0b);
    --cl-blue: var(--db-blue, #2563eb);
    --cl-surface2: var(--db-surface2, #f8fafc);
  }

  /* Força a atualização das variáveis quando o tema escuro está ativo */
  .dark-theme {
    --cl-bg: var(--db-bg, #111827);
    --cl-surface: var(--db-surface, #1f2937);
    --cl-surface2: var(--db-surface2, #374151);
    --cl-border: var(--db-border, #374151);
    --cl-text: var(--db-text, #f3f4f6);
    --cl-text2: var(--db-text2, #d1d5db);
  }

  #clients-page-container {
    font-family: 'Plus Jakarta Sans', sans-serif;
    color: var(--cl-text);
    padding: 24px;
    background-color: var(--cl-bg);
    min-height: 100vh;
  }

  .cl-card {
    background: var(--cl-surface);
    border: 1px solid var(--cl-border);
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    padding: 24px;
  }

  .cl-stat-card {
    background: var(--cl-surface);
    border: 1px solid var(--cl-border);
    border-radius: 12px;
    padding: 20px;
    border-top: 4px solid var(--cl-accent);
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    transition: transform 0.2s, box-shadow 0.2s;
  }
  .cl-stat-card:hover { 
    transform: translateY(-3px); 
    box-shadow: 0 10px 20px rgba(0,0,0,0.06);
  }

  .cl-table th {
    padding: 12px;
    font-size: 10px; font-weight: 700; text-transform: uppercase;
    letter-spacing: 0.8px; color: var(--cl-text2);
    border-bottom: 1px solid var(--cl-border);
    background: var(--cl-surface2) !important;
  }
  .cl-table td { padding: 14px 12px; font-size: 13px; color: var(--cl-text2); }
  .cl-table td.bold { color: var(--cl-text); font-weight: 600; }
  .cl-table tbody tr:hover { background-color: rgba(0,0,0,0.02); }
  .dark-theme .cl-table tbody tr:hover { background-color: rgba(255,255,255,0.05); }

  .cl-badge {
    display: inline-flex; align-items: center;
    padding: 2px 10px; border-radius: 20px;
    font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.3px;
  }
  /* Cores dinâmicas para badges (usando transparência para funcionar em ambos os temas) */
  .cl-badge-green  { background: rgba(16, 185, 129, 0.15); color: #10b981; }
  .cl-badge-blue   { background: rgba(37, 99, 235, 0.15); color: #3b82f6; }
  .cl-badge-orange { background: rgba(245, 158, 11, 0.15); color: #f59e0b; }
  .cl-badge-red    { background: rgba(239, 68, 68, 0.15); color: #ef4444; }
  .cl-badge-gray   { background: rgba(148, 163, 184, 0.15); color: #94a3b8; }

  .dark-theme .divide-gray-200 { border-color: var(--cl-border) !important; }
  
  /* Ajuste para inputs e filtros no modo escuro */
  .dark-theme #clients-page-container input,
  .dark-theme #clients-page-container select {
    background-color: var(--cl-surface);
    color: var(--cl-text);
    border-color: var(--cl-border);
  }
  .dark-theme #clients-page-container .text-gray-600 {
    color: var(--cl-text2) !important;
  }
</style>

<div id="clients-page-container">

<div class="flex justify-between items-center mb-6">
    <div>
        <h2 class="text-2xl font-extrabold tracking-tight"><?php echo htmlspecialchars($pageTitle ?? ''); ?></h2>
        <p class="text-gray-600">Gerencie seus leads, propostas e clientes ativos.</p>
    </div>
    <a href="<?php echo BASE_URL; ?>/clientes/novo" class="px-5 py-2.5 text-sm font-bold text-white bg-blue-600 rounded-lg shadow-md hover:bg-blue-700 transform hover:-translate-y-0.5 transition-all">
        + Novo Cliente
    </a>
</div>

<!-- Cards de Resumo -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <div class="cl-stat-card" style="border-top-color: var(--cl-green)">
        <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider">Clientes Ativos</h3>
        <p class="text-3xl font-extrabold mt-1" style="color: var(--cl-green)"><?php echo $totalAtivos ?? 0; ?></p>
    </div>
    <div class="cl-stat-card" style="border-top-color: var(--cl-blue)">
        <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider">Novos no Mês</h3>
        <p class="text-3xl font-extrabold mt-1" style="color: var(--cl-blue)"><?php echo $novosMes ?? 0; ?></p>
    </div>
    <div class="cl-stat-card" style="border-top-color: var(--cl-orange)">
        <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider">Propostas Pendentes</h3>
        <p class="text-3xl font-extrabold mt-1" style="color: var(--cl-orange)"><?php echo $propostasPendentes ?? 0; ?></p>
    </div>
    <div class="cl-stat-card" style="border-top-color: var(--cl-red)">
        <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider">Risco de Perda</h3>
        <p class="text-3xl font-extrabold mt-1" style="color: var(--cl-red)"><?php echo $riscoPerda ?? 0; ?></p>
    </div>
</div>

<!-- Tabela de Clientes -->
<div class="cl-card">
    <!-- Formulário de Busca -->
    <form action="<?php echo BASE_URL; ?>/clientes" method="GET" class="mb-6 flex items-center gap-4">
        <div class="flex-grow">
            <input type="text" name="busca" id="busca" class="w-full border-gray-200 dark:border-gray-700 rounded-lg shadow-sm p-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all" placeholder="Buscar por Nome, CNPJ/CPF ou Cidade..." value="<?php echo htmlspecialchars($filtros['busca'] ?? ''); ?>">
        </div>
        <div class="w-48">
            <select name="status" id="status" class="w-full border-gray-200 dark:border-gray-700 rounded-lg shadow-sm p-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                <option value="">Todos os Status</option>
                <option value="Ativo" <?php echo (($filtros['status'] ?? '') === 'Ativo') ? 'selected' : ''; ?>>Ativo</option>
                <option value="Potencial" <?php echo (($filtros['status'] ?? '') === 'Potencial') ? 'selected' : ''; ?>>Potencial</option>
                <option value="Em negociação" <?php echo (($filtros['status'] ?? '') === 'Em negociação') ? 'selected' : ''; ?>>Em negociação</option>
                <option value="Inativo" <?php echo (($filtros['status'] ?? '') === 'Inativo') ? 'selected' : ''; ?>>Arquivados (Inativos)</option>
            </select>
        </div>
        <button type="submit" class="px-5 py-2.5 text-sm font-bold text-white bg-blue-600 rounded-lg shadow-sm hover:bg-blue-700 transition-colors">Buscar</button>
        <a href="<?php echo BASE_URL; ?>/clientes" class="px-5 py-2.5 text-sm font-bold text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">Limpar</a>
    </form>

    <h3 class="text-sm font-bold text-gray-500 uppercase tracking-widest mb-4">Base de Clientes</h3>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 cl-table">
            <thead>
                <tr>
                    <th class="px-6 py-3 text-left">Nome</th>
                    <th class="px-6 py-3 text-left">Contato</th>
                    <th class="px-6 py-3 text-left">Última Interação</th>
                    <th class="px-6 py-3 text-center">Status</th>
                    <th class="px-6 py-3 text-right">Ações</th>
                </tr>
            </thead>
            <tbody id="clientes-table-body" class="divide-y divide-gray-200 dark:divide-gray-700">
                <?php if (!empty($clientes)) : ?>
                    <?php foreach ($clientes as $cliente) : ?>
                        <?php
                            $st = $cliente['status'] ?? '';
                            $badgeClass = 'cl-badge-gray';
                            if ($st === 'Ativo') $badgeClass = 'cl-badge-green';
                            elseif ($st === 'Potencial') $badgeClass = 'cl-badge-blue';
                            elseif ($st === 'Em negociação') $badgeClass = 'cl-badge-orange';
                            elseif ($st === 'Inativo') $badgeClass = 'cl-badge-red';
                        ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap bold"><?php echo htmlspecialchars($cliente['nome'] ?? ''); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($cliente['contato_principal'] ?? ''); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap"><?php echo $cliente['data_ultima_interacao'] ? date('d/m/Y', strtotime($cliente['data_ultima_interacao'])) : 'N/A'; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="cl-badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($cliente['status'] ?? ''); ?></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-bold">
                                <a href="<?php echo BASE_URL; ?>/clientes/detalhe/<?php echo $cliente['id']; ?>" class="text-blue-600 hover:text-blue-800 mr-4" title="Detalhes">
                                    <i class='bx bx-search-alt-2 text-xl'></i>
                                </a>
                                <form action="<?php echo BASE_URL; ?>/clientes/excluir/<?php echo $cliente['id']; ?>" method="POST" class="inline" onsubmit="return confirm('Tem certeza que deseja arquivar este cliente?');">
                                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                    <button type="submit" class="text-red-500 hover:text-red-700 bg-transparent border-none p-0 cursor-pointer" title="Arquivar">
                                        <i class='bx bx-archive-in text-xl'></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="5" class="text-center py-4 text-gray-500">Nenhum cliente encontrado.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Paginação -->
    <?php if (isset($totalPaginas) && $totalPaginas > 1): ?>
        <div class="flex justify-end mt-6">
            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                <?php if ($paginaAtual > 1): ?>
                    <a href="?page=<?php echo $paginaAtual - 1; ?>&busca=<?php echo urlencode($filtros['busca'] ?? ''); ?>&status=<?php echo urlencode($filtros['status'] ?? ''); ?>" class="relative inline-flex items-center px-3 py-2 rounded-l-md border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm font-bold text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700">
                        <span class="sr-only">Anterior</span>
                        <i class='bx bx-chevron-left text-xl'></i>
                    </a>
                <?php endif; ?>
                <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                    <a href="?page=<?php echo $i; ?>&busca=<?php echo urlencode($filtros['busca'] ?? ''); ?>&status=<?php echo urlencode($filtros['status'] ?? ''); ?>" class="relative inline-flex items-center px-4 py-2 border border-gray-200 dark:border-gray-700 text-sm font-bold <?php echo $i == $paginaAtual ? 'text-white bg-blue-600 border-blue-600 z-10' : 'bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700'; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
                <?php if ($paginaAtual < $totalPaginas): ?>
                    <a href="?page=<?php echo $paginaAtual + 1; ?>&busca=<?php echo urlencode($filtros['busca'] ?? ''); ?>&status=<?php echo urlencode($filtros['status'] ?? ''); ?>" class="relative inline-flex items-center px-3 py-2 rounded-r-md border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm font-bold text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700">
                        <span class="sr-only">Próxima</span>
                        <i class='bx bx-chevron-right text-xl'></i>
                    </a>
                <?php endif; ?>
            </nav>
        </div>
    <?php endif; ?>
</div>

</div>