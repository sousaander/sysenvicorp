<div class="max-w-6xl mx-auto py-8 px-4 sm:px-6">
    <!-- Cabeçalho com Breadcrumbs e Ações -->
    <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <nav class="mb-2" aria-label="breadcrumb">
                <ol class="flex items-center gap-2 text-[10px] uppercase font-bold tracking-widest text-gray-400 list-none p-0 m-0">
                    <li><a href="<?= BASE_URL ?>/orcamento" class="hover:text-sky-600 transition">Comercial</a></li>
                    <li class="opacity-40">/</li>
                    <li><a href="<?= BASE_URL ?>/configuracoes" class="hover:text-sky-600 transition">Configurações</a></li>
                    <li class="opacity-40">/</li>
                    <li class="text-sky-600">Atributos de Itens</li>
                </ol>
            </nav>
            <h1 class="text-3xl font-black text-gray-900 dark:text-white tracking-tight">Atributos de Itens</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Configure as categorias e unidades de medida para padronizar suas propostas técnicas.</p>
        </div>
        <a href="<?= BASE_URL ?>/configuracoes" class="flex items-center gap-2 px-2 py-1 text-sm font-bold text-gray-600 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition shadow-sm">
            <i class="fas fa-arrow-left text-sky-500"></i> Voltar
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <!-- Seção de Categorias -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="bg-gray-50 dark:bg-gray-900/50 px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                <div class="flex items-center gap-2">
                    <div class="p-1.5 bg-sky-100 text-sky-600 rounded-lg">
                        <i class="fas fa-tags text-sm"></i>
                    </div>
                    <h3 class="font-bold text-gray-800 dark:text-gray-200 uppercase text-xs tracking-widest">Categorias de Serviço</h3>
                </div>
                <button onclick="addItem('categoria')" class="flex items-center gap-1 px-3 py-1.5 text-[10px] font-black uppercase text-white bg-sky-600 hover:bg-sky-700 rounded-lg transition shadow-sm">
                    <i class="fas fa-plus"></i> Nova
                </button>
            </div>
            <div class="p-2">
                <ul class="divide-y divide-gray-100 dark:divide-gray-700" id="list-categorias">
                    <?php if (empty($categorias)): ?>
                        <li class="px-4 py-8 text-center text-gray-400 dark:text-gray-500 italic text-sm">Nenhuma categoria cadastrada.</li>
                    <?php else: ?>
                        <?php foreach($categorias as $cat): ?>
                        <li class="px-4 py-3 flex justify-between items-center hover:bg-gray-50 dark:hover:bg-gray-700/50 rounded-xl transition" data-id="<?= $cat['id'] ?>">
                            <div class="flex items-center gap-3">
                                <span class="w-1.5 h-1.5 rounded-full bg-sky-400"></span>
                                <span class="text-sm text-gray-700 dark:text-gray-300 font-semibold"><?= htmlspecialchars($cat['nome']) ?></span>
                            </div>
                            <div class="flex gap-1">
                                <button onclick="editItem(<?= $cat['id'] ?>, '<?= htmlspecialchars($cat['nome']) ?>', 'categoria')" 
                                        class="p-2 text-amber-500 hover:bg-amber-50 dark:hover:bg-amber-900/20 rounded-lg transition" 
                                        title="Editar">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="deleteItem(<?= $cat['id'] ?>, 'categoria')" 
                                        class="p-2 text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-900/20 rounded-lg transition" 
                                        title="Excluir">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div>
        </div>

        <!-- Seção de Unidades -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="bg-gray-50 dark:bg-gray-900/50 px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                <div class="flex items-center gap-2">
                    <div class="p-1.5 bg-emerald-100 text-emerald-600 rounded-lg">
                        <i class="fas fa-ruler-combined text-sm"></i>
                    </div>
                    <h3 class="font-bold text-gray-800 dark:text-gray-200 uppercase text-xs tracking-widest">Unidades de Medida</h3>
                </div>
                <button onclick="addItem('unidade')" class="flex items-center gap-1 px-3 py-1.5 text-[10px] font-black uppercase text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition shadow-sm">
                    <i class="fas fa-plus"></i> Nova
                </button>
            </div>
            <div class="p-2">
                <ul class="divide-y divide-gray-100 dark:divide-gray-700" id="list-unidades">
                    <?php if (empty($unidades)): ?>
                        <li class="px-4 py-8 text-center text-gray-400 dark:text-gray-500 italic text-sm">Nenhuma unidade cadastrada.</li>
                    <?php else: ?>
                        <?php foreach($unidades as $un): ?>
                        <li class="px-4 py-3 flex justify-between items-center hover:bg-gray-50 dark:hover:bg-gray-700/50 rounded-xl transition" data-id="<?= $un['id'] ?>">
                            <div class="flex items-center gap-3">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                <span class="text-sm text-gray-700 dark:text-gray-300 font-semibold"><?= htmlspecialchars($un['nome']) ?></span>
                            </div>
                            <div class="flex gap-1">
                                <button onclick="editItem(<?= $un['id'] ?>, '<?= htmlspecialchars($un['nome']) ?>', 'unidade')" 
                                        class="p-2 text-amber-500 hover:bg-amber-50 dark:hover:bg-amber-900/20 rounded-lg transition" 
                                        title="Editar">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="deleteItem(<?= $un['id'] ?>, 'unidade')" 
                                        class="p-2 text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-900/20 rounded-lg transition" 
                                        title="Excluir">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
const CSRF_TOKEN = '<?= $csrf_token ?>';

function addItem(type) {
    const nome = prompt(`Informe o nome da nova ${type}:`);
    if (!nome) return;

    const fd = new FormData();
    fd.append('nome', nome);
    fd.append('csrf_token', CSRF_TOKEN);

    const action = type === 'categoria' ? 'addItemCategoriaAjax' : 'addItemUnidadeAjax';

    fetch(`<?= BASE_URL ?>/orcamento/${action}`, { method: 'POST', body: fd })
    .then(r => r.json())
    .then(data => {
        if (data.success) window.location.reload();
        else alert(data.message || 'Erro ao adicionar.');
    });
}

function editItem(id, nomeAtual, type) {
    const novoNome = prompt(`Editar ${type}:`, nomeAtual);
    if (!novoNome || novoNome === nomeAtual) return;

    const fd = new FormData();
    fd.append('id', id);
    fd.append('nome', novoNome);
    fd.append('csrf_token', CSRF_TOKEN);

    const action = type === 'categoria' ? 'updateItemCategoriaAjax' : 'updateItemUnidadeAjax';

    fetch(`<?= BASE_URL ?>/orcamento/${action}`, { method: 'POST', body: fd })
    .then(r => r.json())
    .then(data => {
        if (data.success) window.location.reload();
        else alert(data.message || 'Erro ao atualizar.');
    });
}

function deleteItem(id, type) {
    if (!confirm(`Tem certeza que deseja excluir esta ${type}?`)) return;

    const fd = new FormData();
    fd.append('id', id);
    fd.append('csrf_token', CSRF_TOKEN);

    const action = type === 'categoria' ? 'deleteItemCategoriaAjax' : 'deleteItemUnidadeAjax';

    fetch(`<?= BASE_URL ?>/orcamento/${action}`, { method: 'POST', body: fd })
    .then(r => r.json())
    .then(data => {
        if (data.success) window.location.reload();
        else alert(data.message || 'Erro ao excluir.');
    });
}
</script>