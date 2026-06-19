<?php
/**
 * Patrimônio - Dashboard (index.php)
 * Tela principal com métricas e listagem de bens.
 *
 * Variáveis esperadas do controller:
 *   $pageTitle        string
 *   $totalAtivos      int
 *   $valorContabilTotal float
 *   $bensBaixadosAno  int
 *   $totalDepreciaveis int
 *   $bensRecentes     array
 *   $totalPaginas     int
 *   $paginaAtual      int
 */
?>
<div class="flex justify-between items-start mb-6 gap-4 flex-wrap">
    <div>
        <h2 class="text-xl font-bold text-gray-800"><?php echo htmlspecialchars($pageTitle ?? 'Controle de Patrimônio'); ?></h2>
        <p class="text-xs text-gray-500">Visão geral dos ativos, valores contábeis e movimentações recentes.</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="<?php echo BASE_URL; ?>/patrimonio/relatorios" class="px-4 py-2 text-sm font-bold text-gray-600 bg-white border border-gray-200 rounded-xl shadow-sm hover:bg-gray-50 hover:border-gray-300 transition-all duration-200 flex items-center gap-2">
            <i class='bx bx-bar-chart-alt-2 text-lg'></i>
            Relatórios
        </a>
        <button id="pat-open-modal" class="px-4 py-2 text-sm font-bold text-white bg-sky-600 rounded-xl shadow-lg hover:bg-sky-700 hover:shadow-sky-200 hover:-translate-y-0.5 active:scale-95 transition-all duration-200 flex items-center gap-2 group">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 group-hover:rotate-90 transition-transform duration-300">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
            </svg>
            Novo Bem
        </button>
    </div>
</div>

<!-- Métricas -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white p-4 rounded-lg shadow-sm border-l-4 border-sky-500">
        <div class="flex justify-between items-center mb-2">
            <span class="text-xs font-semibold text-gray-500">Total de ativos</span>
            <span class="p-2 bg-sky-50 text-sky-600 rounded-lg">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75"/></svg>
            </span>
        </div>
        <div class="text-xl font-bold text-gray-900"><?php echo intval($totalAtivos ?? 0); ?></div>
        <p class="text-xs text-gray-400 mt-1">Bens registrados ativos</p>
    </div>

    <div class="bg-white p-4 rounded-lg shadow-sm border-l-4 border-green-500">
        <div class="flex justify-between items-center mb-2">
            <span class="text-xs font-semibold text-gray-500">Valor contábil</span>
            <span class="p-2 bg-green-50 text-green-600 rounded-lg">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
            </span>
        </div>
        <div class="text-xl font-bold text-gray-900">R$ <?php echo number_format($valorContabilTotal ?? 0, 2, ',', '.'); ?></div>
        <p class="text-xs text-gray-400 mt-1">Valor contábil atual</p>
    </div>

    <div class="bg-white p-4 rounded-lg shadow-sm border-l-4 border-red-500">
        <div class="flex justify-between items-center mb-2">
            <span class="text-xs font-semibold text-gray-500">Baixados no ano</span>
            <span class="p-2 bg-red-50 text-red-600 rounded-lg">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z"/></svg>
            </span>
        </div>
        <div class="text-xl font-bold text-gray-900"><?php echo intval($bensBaixadosAno ?? 0); ?></div>
        <p class="text-xs text-gray-400 mt-1">Vendas ou descartes</p>
    </div>

    <div class="bg-white p-4 rounded-lg shadow-sm border-l-4 border-amber-500">
        <div class="flex justify-between items-center mb-2">
            <span class="text-xs font-semibold text-gray-500">Depreciáveis</span>
            <span class="p-2 bg-amber-50 text-amber-600 rounded-lg">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941"/></svg>
            </span>
        </div>
        <div class="text-xl font-bold text-gray-900"><?php echo intval($totalDepreciaveis ?? 0); ?></div>
        <p class="text-xs text-gray-400 mt-1">Com depreciação ativa</p>
    </div>
</div>

<!-- Tabela de bens -->
<div class="bg-white rounded-lg shadow-md overflow-hidden">
    <div class="p-4 border-b flex justify-between items-center flex-wrap gap-4">
        <div class="flex items-center gap-3">
            <h3 class="text-base font-bold text-gray-800">Bens Patrimoniais</h3>
            <?php if (!empty($totalAtivos)): ?>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-sky-100 text-sky-800"><?php echo intval($totalAtivos); ?> itens</span>
            <?php endif; ?>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-2.5 flex items-center text-gray-400">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </span>
                <input type="text" placeholder="Buscar bem..." id="pat-search-input" class="pl-8 pr-3 py-1.5 border rounded-lg text-xs focus:ring-sky-500 focus:border-sky-500 outline-none w-40 lg:w-56 transition">
            </div>
            <select class="px-2 py-1.5 border rounded-lg text-xs focus:ring-sky-500 focus:border-sky-500 outline-none text-gray-600" id="pat-filter-tipo">
                <option value="">Todos</option>
                <option value="Equipamento de TI">Equip. TI</option>
                <option value="Veículo">Veículo</option>
                <option value="Mobiliário">Mobiliário</option>
                <option value="Imóvel">Imóvel</option>
                <option value="Máquina / Ferramenta">Máquina</option>
                <option value="Software / Licença">Software</option>
                <option value="Outro">Outro</option>
            </select>
        </div>
    </div>

    <div class="overflow-x-auto">
        <?php if (!empty($bensRecentes)) : ?>
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr class="text-left text-[10px] font-bold text-gray-400">
                        <th class="px-3 py-2 w-1/3">Nome do Bem</th>
                        <th class="px-3 py-2">Nº Patrimônio</th>
                        <th class="px-3 py-2">Classificação</th>
                        <th class="px-3 py-2">Localização</th>
                        <th class="px-3 py-2 text-center">Aquisição</th>
                        <th class="px-3 py-2" style="text-align:right;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bensRecentes as $bem) :
                        $tipo = $bem['classificacao'] ?? '';
                        $tipoIcon = match(true) {
                            str_contains($tipo, 'TI')       => 'bx-laptop',
                            str_contains($tipo, 'Veículo')  => 'bx-car',
                            str_contains($tipo, 'Mobil')    => 'bx-chair',
                            str_contains($tipo, 'Imóvel')   => 'bx-building',
                            str_contains($tipo, 'Máquina')  => 'bx-wrench',
                            str_contains($tipo, 'Software') => 'bx-code-alt',
                            default                         => 'bx-cube',
                        };
                        $tipoClass = match(true) {
                            str_contains($tipo, 'TI')       => 'bg-blue-100 text-blue-800',
                            str_contains($tipo, 'Veículo')  => 'bg-amber-100 text-amber-800',
                            str_contains($tipo, 'Mobil')    => 'bg-green-100 text-green-800',
                            str_contains($tipo, 'Imóvel')   => 'bg-pink-100 text-pink-800',
                            str_contains($tipo, 'Máquina')  => 'bg-orange-100 text-orange-800',
                            str_contains($tipo, 'Software') => 'bg-purple-100 text-purple-800',
                            default                         => 'bg-gray-100 text-gray-800',
                        };
                    ?>
                        <tr class="hover:bg-gray-50/30 transition border-t border-gray-100 text-gray-700">
                            <td class="px-3 py-1.5">
                                <div class="text-[12px] font-semibold text-sky-600 cursor-pointer pat-edit-btn" data-id="<?php echo htmlspecialchars($bem['id']); ?>">
                                    <?php echo htmlspecialchars($bem['nome']); ?>
                                </div>
                            </td>
                            <td class="px-3 py-1.5 text-[11px] text-gray-400 font-medium">
                                <?php echo htmlspecialchars($bem['numero_patrimonio'] ?: '—'); ?> 
                            </td>
                            <td class="px-3 py-1.5">
                                <span class="px-1.5 py-0.5 inline-flex items-center gap-1 text-[9px] font-bold rounded-md <?php echo $tipoClass; ?>">
                                    <i class='bx <?php echo $tipoIcon; ?>'></i>
                                    <?php echo htmlspecialchars($tipo ?: 'Outro'); ?>
                                </span>
                            </td>
                            <td class="px-3 py-1.5">
                                <div class="flex items-center gap-1 text-gray-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3 h-3"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0z"/></svg>
                                    <span class="text-[10px]"><?php echo htmlspecialchars($bem['localizacao'] ?: '—'); ?></span>
                                </div>
                            </td>
                            <td class="px-3 py-1.5 text-center text-[10px] text-gray-400">
                                <?php echo $bem['data_aquisicao'] ? date('d/m/Y', strtotime($bem['data_aquisicao'])) : '—'; ?>
                            </td>
                            <td class="px-3 py-1.5">
                                <div class="flex justify-end gap-0.5">
                                    <a href="<?php echo BASE_URL; ?>/patrimonio/etiqueta/<?php echo $bem['id']; ?>" target="_blank" 
                                       class="p-1 text-gray-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-md transition" title="Imprimir Etiqueta">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231a1.125 1.125 0 0 1-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.653" /></svg>
                                    </a>
                                    <button class="p-1 text-gray-500 hover:text-sky-600 hover:bg-sky-50 rounded-md transition pat-edit-btn" 
                                            data-id="<?php echo htmlspecialchars($bem['id']); ?>" title="Editar">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931zm0 0L19.5 7.125"/></svg>
                                    </button>
                                    <button class="p-1 text-gray-500 hover:text-red-600 hover:bg-red-50 rounded-md transition pat-delete-btn" 
                                            data-id="<?php echo htmlspecialchars($bem['id']); ?>" title="Excluir">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="py-12 flex flex-col items-center justify-center text-gray-400">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" class="w-12 h-12 mb-4 opacity-20"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/></svg>
                <p class="text-sm">Nenhum bem cadastrado no momento.</p>
                <button onclick="openNew()" class="mt-2 text-sky-600 font-medium hover:underline text-xs">+ Cadastrar primeiro bem</button>
            </div>
        <?php endif; ?>
    </div>

    <!-- Paginação -->
    <?php if (!empty($totalPaginas) && $totalPaginas > 1) : ?>
        <div class="pat-pagination">
            <span class="pat-pagination-info">
                Página <?php echo intval($paginaAtual); ?> de <?php echo intval($totalPaginas); ?>
            </span>
            <div class="pat-pagination-btns">
                <nav class="flex items-center space-x-1">
                    <a href="<?php echo BASE_URL; ?>/patrimonio?page=<?php echo ($paginaAtual > 1 ? $paginaAtual - 1 : 1); ?>" class="<?php echo ($paginaAtual <= 1) ? 'pointer-events-none opacity-40' : 'hover:bg-gray-100'; ?> px-2 py-1 rounded text-gray-500 border">
                        &larr;
                    </a>
                    <?php for ($i = 1; $i <= $totalPaginas; $i++) : ?>
                        <a href="<?php echo BASE_URL; ?>/patrimonio?page=<?php echo $i; ?>" class="<?php echo ($i == $paginaAtual) ? 'bg-sky-500 text-white border-sky-500' : 'bg-white text-gray-600 hover:bg-gray-100 border-gray-200'; ?> px-3 py-1 rounded text-xs font-semibold border transition">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                    <a href="<?php echo BASE_URL; ?>/patrimonio?page=<?php echo ($paginaAtual < $totalPaginas ? $paginaAtual + 1 : $totalPaginas); ?>" class="<?php echo ($paginaAtual >= $totalPaginas) ? 'pointer-events-none opacity-40' : 'hover:bg-gray-100'; ?> px-2 py-1 rounded text-gray-500 border">
                        &rarr;
                    </a>
                </nav>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- ===== MODAL: Cadastro / Edição (Modernizado) ===== -->
<div id="pat-bem-modal" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-start justify-center p-4 z-50 transition-opacity hidden">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl overflow-hidden mt-6 animate-in fade-in zoom-in duration-200">
        <div class="p-4 border-b flex justify-between items-center bg-gray-50">
            <h3 id="pat-modal-title" class="text-base font-bold text-gray-800">Cadastro de Bem Patrimonial</h3>
            <button id="pat-close-modal" class="p-1 rounded-lg hover:bg-gray-200 transition text-gray-500 text-xl">&times;</button>
        </div>
        <div class="p-4 max-h-[75vh] overflow-y-auto">
            <form id="pat-bem-form" action="<?php echo BASE_URL; ?>/patrimonio/salvar" method="POST">
                <input type="hidden" id="pat-bem-id" name="id">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Identificação -->
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-gray-500 mb-1">Nome / Descrição do bem <span class="text-red-500">*</span></label>
                        <input type="text" id="pat-nome" name="nome" required placeholder="Ex: Notebook Dell Latitude 5540" class="w-full px-2.5 py-1.5 border rounded-lg text-xs focus:ring-2 focus:ring-sky-500 outline-none transition bg-gray-50">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 mb-1">Nº patrimônio / Plaqueta</label>
                        <input type="text" id="pat-numero_patrimonio" name="numero_patrimonio" placeholder="PAT-00124" class="w-full px-2.5 py-1.5 border rounded-lg text-xs focus:ring-2 focus:ring-sky-500 outline-none transition bg-gray-50">
                        <span class="text-[9px] text-gray-400">Vazio para gerar automático</span>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 mb-1">Responsável</label>
                        <input type="text" id="pat-responsavel" name="responsavel" placeholder="Nome do colaborador" class="w-full px-2.5 py-1.5 border rounded-lg text-xs focus:ring-2 focus:ring-sky-500 outline-none transition bg-gray-50">
                    </div>

                    <!-- Classificação visual -->
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-gray-500 mb-1.5">Classificação / Tipo <span class="text-red-500">*</span></label>
                        <input type="hidden" id="pat-classificacao" name="classificacao" required>
                        <div class="grid grid-cols-4 sm:grid-cols-7 gap-3" id="pat-tipo-grid">
                            <?php
                            $tipos = [
                                ['val' => 'Equipamento de TI',   'label' => 'TI',  'icon' => '<svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 0 1-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0 1 15 18.257V17.25m6-12V15a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 15V5.25m18 0A2.25 2.25 0 0 0 18.75 3H5.25A2.25 2.25 0 0 0 3 5.25m18 0H3"/></svg>'],
                                ['val' => 'Veículo',             'label' => 'Veículo',    'icon' => '<svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/></svg>'],
                                ['val' => 'Imóvel',              'label' => 'Imóvel',     'icon' => '<svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75"/></svg>'],
                                ['val' => 'Mobiliário',          'label' => 'Mobil.', 'icon' => '<svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/></svg>'],
                                ['val' => 'Máquina / Ferramenta','label' => 'Máquina',   'icon' => '<svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l5.653-4.655m5.8-7.425a3 3 0 1 1-4.243 4.243"/></svg>'],
                                ['val' => 'Software / Licença',  'label' => 'Soft.',  'icon' => '<svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 7.5l3 2.25-3 2.25m4.5 0h3m-9 8.25h13.5A2.25 2.25 0 0 0 21 18V6a2.25 2.25 0 0 0-2.25-2.25H5.25A2.25 2.25 0 0 0 3 6v12a2.25 2.25 0 0 0 2.25 2.25z"/></svg>'],
                                ['val' => 'Outro',               'label' => 'Outro',     'icon' => '<svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.75a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5zM12 12.75a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5zM12 18.75a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5z"/></svg>'],
                            ];
                            foreach ($tipos as $t):
                            ?>
                                <button type="button" class="pat-tipo-btn flex flex-col items-center gap-2 py-3 px-1 border-2 rounded-xl hover:bg-gray-100 transition text-[10px] font-bold text-gray-500" data-val="<?php echo htmlspecialchars($t['val']); ?>" title="<?php echo htmlspecialchars($t['val']); ?>">
                                    <?php echo $t['icon']; ?>
                                    <?php echo htmlspecialchars($t['label']); ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-gray-500 mb-1">Localização / Setor <span class="text-red-500">*</span></label>
                        <input type="text" id="pat-localizacao" name="localizacao" required placeholder="Ex: Sala de TI, Administrativo, Campo" class="w-full px-2.5 py-1.5 border rounded-lg text-xs focus:ring-2 focus:ring-sky-500 outline-none transition bg-gray-50">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-gray-500 mb-1">Observações</label>
                        <textarea id="pat-observacoes" name="observacoes" rows="2" placeholder="Estado de conservação, detalhes relevantes..." class="w-full px-2.5 py-1.5 border rounded-lg text-xs focus:ring-2 focus:ring-sky-500 outline-none transition bg-gray-50"></textarea>
                    </div>

                    <!-- Dados contábeis -->
                    <div class="md:col-span-2 mt-1.5 pt-1.5 border-t flex items-center gap-2 text-xs font-bold text-amber-600">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.306"/></svg>
                        Dados contábeis e depreciação
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 mb-1">Data de aquisição</label>
                        <input type="date" id="pat-data_aquisicao" name="data_aquisicao" class="w-full px-2.5 py-1.5 border rounded-lg text-xs focus:ring-2 focus:ring-sky-500 transition bg-gray-50 outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 mb-1">Valor aquisição (R$)</label>
                        <input type="text" id="pat-valor_aquisicao" name="valor_aquisicao" placeholder="1.500,00" class="w-full px-2.5 py-1.5 border rounded-lg text-xs focus:ring-2 focus:ring-sky-500 transition bg-gray-50 outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 mb-1">Vida útil (meses)</label>
                        <input type="number" id="pat-vida_util_meses" name="vida_util_meses" placeholder="60" min="1" class="w-full px-2.5 py-1.5 border rounded-lg text-xs focus:ring-2 focus:ring-sky-500 transition bg-gray-50 outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 mb-1">Centro de custo</label>
                        <input type="text" id="pat-centro_custo" name="centro_custo" placeholder="Ex: TI, Administrativo" class="w-full px-2.5 py-1.5 border rounded-lg text-xs focus:ring-2 focus:ring-sky-500 transition bg-gray-50 outline-none">
                    </div>

                </div>
            </form>
        </div>
        <div class="p-3 bg-gray-50 border-t flex justify-end gap-2">
            <button type="button" class="px-4 py-1.5 text-xs font-semibold text-gray-600 hover:bg-gray-200 rounded-lg transition" id="pat-cancel-modal">Cancelar</button>
            <button type="submit" form="pat-bem-form" id="pat-modal-submit" class="px-4 py-1.5 text-xs font-semibold text-white bg-sky-600 rounded-lg shadow-sm hover:bg-sky-700 transition">Salvar bem</button>
        </div>
    </div>
</div>

<!-- ===== MODAL: Confirmar exclusão ===== -->
<form id="pat-delete-form" action="<?php echo BASE_URL; ?>/patrimonio/excluir" method="POST" style="display:none;">
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
    <input type="hidden" id="pat-delete-id" name="id">
</form>

<script>
(function () {
    const modal       = document.getElementById('pat-bem-modal');
    const form        = document.getElementById('pat-bem-form');
    const titleEl     = document.getElementById('pat-modal-title');
    const submitBtn   = document.getElementById('pat-modal-submit');
    const idInput     = document.getElementById('pat-bem-id');
    const classInput  = document.getElementById('pat-classificacao');

    // Tipo buttons
    const tipoButtons = document.querySelectorAll('.pat-tipo-btn');
    document.querySelectorAll('.pat-tipo-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            tipoButtons.forEach(b => b.classList.remove('bg-sky-50', 'border-sky-500', 'text-sky-600'));
            btn.classList.add('bg-sky-50', 'border-sky-500', 'text-sky-600');
            classInput.value = btn.dataset.val;
        });
    });

    function setTipo(val) {
        tipoButtons.forEach(b => {
            b.classList.toggle('bg-sky-50', b.dataset.val === val);
            b.classList.toggle('border-sky-500', b.dataset.val === val);
            b.classList.toggle('text-sky-600', b.dataset.val === val);
        });
        classInput.value = val;
    }
    window.openNew = function() {
        form.reset();
        idInput.value = '';
        classInput.value = '';
        document.querySelectorAll('.pat-tipo-btn').forEach(b => b.classList.remove('selected'));
        titleEl.textContent = 'Cadastro de Bem Patrimonial';
        submitBtn.textContent = 'Salvar bem';
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function openEdit(id) {
        fetch(`<?php echo BASE_URL; ?>/patrimonio/getBemJson/${id}`)
            .then(r => r.json())
            .then(result => {
                if (!result.success) { alert(result.message); return; }
                const b = result.data;
                form.reset();
                idInput.value = b.id || '';
                document.getElementById('pat-nome').value              = b.nome || '';
                document.getElementById('pat-numero_patrimonio').value = b.numero_patrimonio || '';
                document.getElementById('pat-responsavel').value       = b.responsavel || '';
                document.getElementById('pat-localizacao').value       = b.localizacao || '';
                document.getElementById('pat-observacoes').value       = b.observacoes || '';
                document.getElementById('pat-data_aquisicao').value    = b.data_aquisicao || '';
                document.getElementById('pat-valor_aquisicao').value   = b.valor_aquisicao || '';
                document.getElementById('pat-vida_util_meses').value   = b.vida_util_meses || '';
                document.getElementById('pat-centro_custo').value      = b.centro_custo || '';
                setTipo(b.classificacao || '');
                titleEl.textContent = 'Editar Bem Patrimonial';
                submitBtn.textContent = 'Atualizar bem';
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            })
            .catch(e => console.error('Erro ao buscar bem:', e));
    }

    function closeModal()    { 
        modal.classList.add('hidden'); 
        modal.classList.remove('flex');
    }
    
    const btnOpen = document.getElementById('pat-open-modal');
    if(btnOpen) btnOpen.addEventListener('click', openNew);
    
    if(document.getElementById('pat-close-modal')) document.getElementById('pat-close-modal').addEventListener('click', closeModal);
    if(document.getElementById('pat-cancel-modal')) document.getElementById('pat-cancel-modal').addEventListener('click', closeModal);
    
    modal.addEventListener('click', e => { if (e.target === modal) closeModal(); });

    document.querySelectorAll('.pat-edit-btn').forEach(btn => {
        btn.addEventListener('click', e => { e.preventDefault(); openEdit(btn.dataset.id); });
    });

    document.querySelectorAll('.pat-delete-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.dataset.id;
            Swal.fire({
                title: 'Excluir bem?',
                text: "Esta ação removerá o bem de todos os registros permanentemente.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444', // Red 500
                cancelButtonColor: '#94a3b8',  // Slate 400
                confirmButtonText: 'Sim, excluir',
                cancelButtonText: 'Voltar',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('pat-delete-id').value = id;
                    document.getElementById('pat-delete-form').submit();
                }
            });
        });
    });
})();
</script>
