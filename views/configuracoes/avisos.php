<div class="page-content">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div>
            <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight">Avisos do Sistema</h2>
            <p class="text-slate-500 text-sm">Gerencie comunicados, alertas de manutenção e novidades exibidas no Dashboard.</p>
        </div>
        <button onclick="abrirModalAviso()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-xl flex items-center gap-2 transition-all shadow-lg shadow-indigo-200 hover:-translate-y-0.5">
            <i class='bx bx-plus-circle text-xl'></i> <span class="font-bold text-sm">Criar Comunicado</span>
        </button>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden transition-all hover:shadow-md">
        <table class="w-full text-left">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <th class="px-6 py-3 text-xs font-bold text-slate-500 uppercase">Título</th>
                    <th class="px-6 py-3 text-xs font-bold text-slate-500 uppercase">Período de Exibição</th>
                    <th class="px-6 py-3 text-xs font-bold text-slate-500 uppercase">Tipo</th>
                    <th class="px-6 py-3 text-xs font-bold text-slate-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-xs font-bold text-slate-500 uppercase text-right">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php if (empty($avisos)): ?>
                    <tr>
                        <td colspan="5" class="px-6 py-10 text-center text-slate-400 italic">Nenhum aviso cadastrado.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($avisos as $aviso): ?>
                        <tr class="group hover:bg-slate-50/80 transition-colors">
                            <td class="px-6 py-4 font-bold text-slate-700"><?php echo htmlspecialchars($aviso['titulo']); ?></td>
                            <td class="px-6 py-4 text-sm text-slate-500">
                                <span class="flex items-center gap-1.5"><i class='bx bx-calendar-event text-indigo-400'></i> <?php echo date('d/m/Y H:i', strtotime($aviso['data_inicio'])); ?></span>
                                <span class="text-[10px] text-slate-400 font-bold uppercase ml-5">até <?php echo date('d/m/Y H:i', strtotime($aviso['data_fim'])); ?></span>
                            </td>
                            <td class="px-6 py-4">
                                <?php
                                $cores = [
                                    'info' => 'bg-blue-100 text-blue-700',
                                    'warning' => 'bg-amber-100 text-amber-700',
                                    'danger' => 'bg-red-100 text-red-700',
                                    'success' => 'bg-emerald-100 text-emerald-700'
                                ];
                                $cor = $cores[$aviso['tipo']] ?? $cores['info'];
                                ?>
                                <span class="px-3 py-1 rounded-lg text-[10px] font-extrabold uppercase tracking-wider <?php echo $cor; ?>">
                                    <?php echo $aviso['tipo']; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <?php if ($aviso['ativo']): ?>
                                    <span class="flex items-center gap-1 text-emerald-600 text-sm font-medium">
                                        <i class='bx bxs-circle text-[8px]'></i> Ativo
                                    </span>
                                <?php else: ?>
                                    <span class="flex items-center gap-1 text-slate-400 text-sm font-medium">
                                        <i class='bx bxs-circle text-[8px]'></i> Inativo
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-2">
                                <button onclick='editarAviso(<?php echo json_encode($aviso); ?>)' class="w-9 h-9 flex items-center justify-center rounded-lg bg-indigo-50 border border-indigo-100 text-indigo-600 hover:bg-indigo-600 hover:text-white hover:shadow-md transition-all" title="Editar">
                                    <i class='bx bx-edit text-xl'></i>
                                </button>
                                <a href="<?php echo BASE_URL; ?>/configuracoes/excluirAviso/<?php echo $aviso['id']; ?>" class="w-9 h-9 flex items-center justify-center rounded-lg bg-red-50 border border-red-100 text-red-600 hover:bg-red-600 hover:text-white hover:shadow-md transition-all" onclick="return confirm('Excluir este alerta?')" title="Excluir">
                                    <i class='bx bx-trash text-xl'></i>
                                </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal -->
<div id="modalAviso" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm hidden items-center justify-center z-[100] p-4 transition-all">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden animate-in fade-in zoom-in duration-300">
        <div class="px-6 py-5 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center">
                    <i class='bx bx-notification text-2xl'></i>
                </div>
                <h3 id="modalTitle" class="text-xl font-extrabold text-slate-800 tracking-tight">Novo Aviso</h3>
            </div>
            <button onclick="fecharModalAviso()" class="w-10 h-10 flex items-center justify-center rounded-full hover:bg-slate-200 text-slate-400 transition-colors">
                <i class='bx bx-x text-3xl'></i>
            </button>
        </div>
        <form action="<?php echo BASE_URL; ?>/configuracoes/salvarAviso" method="POST" class="p-6 space-y-4">
            <input type="hidden" name="id" id="aviso_id">
            <div>
                <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Título do Comunicado</label>
                <input type="text" name="titulo" id="aviso_titulo" required class="w-full border-slate-200 rounded-xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all placeholder:text-slate-300" placeholder="Ex: Manutenção Programada">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Mensagem (Aceita HTML)</label>
                <textarea name="mensagem" id="aviso_mensagem" rows="4" required class="w-full border-slate-200 rounded-xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all placeholder:text-slate-300" placeholder="Descreva o que os usuários precisam saber..."></textarea>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Tipo de Alerta</label>
                    <select name="tipo" id="aviso_tipo" class="w-full border-slate-200 rounded-xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all">
                        <option value="info">🔵 Informativo</option>
                        <option value="warning">🟠 Manutenção</option>
                        <option value="success">🟢 Atualização</option>
                        <option value="danger">🔴 Crítico</option>
                    </select>
                </div>
                <div class="flex items-center pt-5">
                    <input type="checkbox" name="ativo" id="aviso_ativo" value="1" checked class="w-5 h-5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                    <label class="ml-3 text-sm font-bold text-slate-600">Ativar Exibição</label>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-6 pt-2">
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Início da Exibição</label>
                    <input type="datetime-local" name="data_inicio" id="aviso_data_inicio" required class="w-full border-slate-200 rounded-xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Fim da Exibição</label>
                    <input type="datetime-local" name="data_fim" id="aviso_data_fim" required class="w-full border-slate-200 rounded-xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all">
                </div>
            </div>
            <div class="pt-6 flex justify-end gap-3">
                <button type="button" onclick="fecharModalAviso()" class="px-6 py-2.5 text-slate-500 hover:bg-slate-100 rounded-xl font-bold transition-colors">Cancelar</button>
                <button type="submit" class="px-10 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-extrabold shadow-lg shadow-indigo-100 transition-all hover:scale-105 active:scale-95">Salvar Aviso</button>
            </div>
        </form>
    </div>
</div>

<script>
function abrirModalAviso() {
    document.getElementById('aviso_id').value = '';
    document.getElementById('modalTitle').textContent = 'Novo Aviso';
    document.getElementById('modalAviso').classList.replace('hidden', 'flex');
}
function fecharModalAviso() { document.getElementById('modalAviso').classList.replace('flex', 'hidden'); }
function editarAviso(aviso) {
    document.getElementById('aviso_id').value = aviso.id;
    document.getElementById('aviso_titulo').value = aviso.titulo;
    document.getElementById('aviso_mensagem').value = aviso.mensagem;
    document.getElementById('aviso_tipo').value = aviso.tipo;
    document.getElementById('aviso_ativo').checked = (aviso.ativo == 1);
    document.getElementById('aviso_data_inicio').value = aviso.data_inicio.replace(' ', 'T').slice(0, 16);
    document.getElementById('aviso_data_fim').value = aviso.data_fim.replace(' ', 'T').slice(0, 16);
    document.getElementById('modalTitle').textContent = 'Editar Aviso';
    document.getElementById('modalAviso').classList.replace('hidden', 'flex');
}
</script>