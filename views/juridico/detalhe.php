<div class="space-y-6">
    <!-- Cabeçalho -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div class="flex items-center gap-4">
            <a href="<?= BASE_URL ?>/juridico/processos" class="p-2 bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-slate-200 dark:border-slate-700 text-slate-500 hover:text-purple-600 transition-all">
                <i class='bx bx-left-arrow-alt text-2xl'></i>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-slate-800 dark:text-white tracking-tight"><?= $pageTitle ?></h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 font-medium">Informações detalhadas do processo judicial ou administrativo.</p>
            </div>
        </div>
        <div class="flex gap-2">
            <?php if (has_permission('juridico_processos_manage')) : ?>
                <a href="<?= BASE_URL ?>/juridico/processos/editar/<?= $proc['id'] ?>" class="px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-bold text-blue-600 hover:bg-blue-50 transition-all flex items-center gap-2">
                    <i class='bx bx-edit-alt'></i> Editar Processo
                </a>
                <button type="button" onclick="confirmarExclusaoProcesso(<?= $proc['id'] ?>)" class="px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-bold text-red-600 hover:bg-red-50 transition-all flex items-center gap-2">
                    <i class='bx bx-trash'></i> Excluir Processo
                </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Grid de Informações -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Coluna da Esquerda: Dados Gerais -->
        <div class="lg:col-span-2 space-y-6">
            <div class="sys-card">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-white flex items-center gap-2">
                        <i class='bx bx-info-circle text-purple-600'></i>
                        Dados do Processo
                    </h3>
                    <?php
                    $statusClass = 'sys-badge-gray';
                    if ($proc['status'] === 'Ativo') $statusClass = 'sys-badge-blue !bg-purple-100 !text-purple-700';
                    if ($proc['status'] === 'Suspenso') $statusClass = 'sys-badge-orange';
                    if ($proc['status'] === 'Concluído') $statusClass = 'sys-badge-green';
                    ?>
                    <span class="sys-badge <?= $statusClass ?> font-bold"><?= $proc['status'] ?></span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-y-6 gap-x-8">
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Número do Processo</label>
                        <p class="font-mono font-bold text-slate-700 dark:text-slate-200 text-base"><?= htmlspecialchars($proc['numero']) ?></p>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Tipo de Ação</label>
                        <p class="font-semibold text-slate-600 dark:text-slate-300"><?= htmlspecialchars($proc['tipo']) ?></p>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Tribunal / Órgão</label>
                        <p class="text-slate-600 dark:text-slate-300"><?= htmlspecialchars($proc['tribunal'] ?: '—') ?></p>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Vara / Câmara</label>
                        <p class="text-slate-600 dark:text-slate-300"><?= htmlspecialchars($proc['vara_camara'] ?: '—') ?></p>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Parte Contrária</label>
                        <p class="font-bold text-slate-700 dark:text-slate-200"><?= htmlspecialchars($proc['parte_contraria'] ?: '—') ?></p>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Valor da Causa</label>
                        <p class="font-black text-slate-800 dark:text-white text-lg">R$ <?= number_format($proc['valor_causa'] ?? 0, 2, ',', '.') ?></p>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Objeto da Ação</label>
                        <div class="p-4 bg-slate-50 dark:bg-white/5 rounded-xl border border-slate-100 dark:border-slate-700 text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                            <?= nl2br(htmlspecialchars($proc['objeto'] ?: 'Nenhum objeto detalhado.')) ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="sys-card">
                <h3 class="text-lg font-bold text-slate-800 dark:text-white flex items-center gap-2 mb-4">
                    <i class='bx bx-notepad text-purple-600'></i>
                    Observações e Notas
                </h3>
                <p class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed italic">
                    <?= $proc['observacoes'] ? nl2br(htmlspecialchars($proc['observacoes'])) : 'Nenhuma observação interna registrada.' ?>
                </p>
            </div>
        </div>

        <!-- Coluna da Direita: Metadados e Vínculos -->
        <div class="space-y-6">
            <div class="sys-card">
                <h3 class="text-sm font-bold text-slate-800 dark:text-white uppercase tracking-widest border-b border-slate-100 dark:border-slate-700 pb-3 mb-4">Prazos e Histórico</h3>
                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-xs text-slate-500 font-medium">Data de Distribuição</span>
                        <span class="text-xs font-bold text-slate-700 dark:text-slate-200"><?= $proc['data_distribuicao'] ? date('d/m/Y', strtotime($proc['data_distribuicao'])) : '—' ?></span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-xs text-slate-500 font-medium">Cadastrado em</span>
                        <span class="text-xs font-bold text-slate-700 dark:text-slate-200"><?= date('d/m/Y', strtotime($proc['created_at'])) ?></span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-xs text-slate-500 font-medium">Última Modificação</span>
                        <span class="text-xs font-bold text-slate-700 dark:text-slate-200"><?= date('d/m/Y H:i', strtotime($proc['updated_at'])) ?></span>
                    </div>
                </div>
            </div>

            <div class="sys-card">
                <h3 class="text-sm font-bold text-slate-800 dark:text-white uppercase tracking-widest border-b border-slate-100 dark:border-slate-700 pb-3 mb-4">Cliente Responsável</h3>
                <?php if ($proc['cliente_id']): ?>
                    <div class="flex items-center gap-4 group">
                        <div class="w-12 h-12 rounded-2xl bg-purple-100 dark:bg-purple-900/30 text-purple-600 flex items-center justify-center text-xl shadow-sm">
                            <i class='bx bxs-user-detail'></i>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-slate-700 dark:text-slate-200">ID do Cliente: #<?= $proc['cliente_id'] ?></p>
                            <a href="<?= BASE_URL ?>/clientes/detalhe/<?= $proc['cliente_id'] ?>" class="text-[10px] text-purple-600 font-black uppercase tracking-tighter hover:underline">Ver Perfil Completo</a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="flex items-center gap-3 text-slate-400 italic py-2">
                        <i class='bx bx-user-x text-lg'></i>
                        <span class="text-xs">Sem cliente vinculado</span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmação de Exclusão -->
<div id="modal-confirmar-exclusao" class="hidden fixed inset-0 z-[100] overflow-y-auto">
    <!-- Backdrop (Fundo escurecido) -->
    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"></div>

    <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
        <div class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-slate-800 text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-slate-200 dark:border-slate-700">
            <div class="p-6">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-xl bg-red-100 dark:bg-red-900/30 sm:mx-0 sm:h-10 sm:w-10">
                        <i class='bx bx-error-alt text-2xl text-red-600'></i>
                    </div>
                    <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                        <h3 class="text-lg font-bold leading-6 text-slate-800 dark:text-white tracking-tight">Excluir Processo</h3>
                        <div class="mt-2">
                            <p class="text-sm text-slate-500 dark:text-slate-400">Tem certeza que deseja excluir permanentemente este processo? Esta ação não pode ser desfeita e todos os dados vinculados serão removidos.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-slate-50 dark:bg-white/5 px-6 py-4 flex flex-col-reverse sm:flex-row sm:justify-end gap-3">
                <button type="button" onclick="fecharModalExclusao()" class="px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition-all">
                    Cancelar
                </button>
                <button type="button" id="btn-confirmar-submit" class="px-6 py-2 bg-red-600 hover:bg-red-700 rounded-xl text-sm font-bold text-white shadow-lg shadow-red-500/20 transition-all">
                    Confirmar Exclusão
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let idParaExcluir = null;

function confirmarExclusao(id) {
    idParaExcluir = id;
    document.getElementById('modal-confirmar-exclusao').classList.remove('hidden');
    document.body.style.overflow = 'hidden'; // Evita rolagem da página com modal aberto
}

function fecharModalExclusao() {
    document.getElementById('modal-confirmar-exclusao').classList.add('hidden');
    document.body.style.overflow = 'auto';
    idParaExcluir = null;
}

document.getElementById('btn-confirmar-submit').addEventListener('click', function() {
    if (idParaExcluir) {
        document.getElementById('form-excluir-' + idParaExcluir).submit();
    }
});
</script>