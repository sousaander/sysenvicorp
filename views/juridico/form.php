<div class="space-y-6">
    <!-- Cabeçalho -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div class="flex items-center gap-4">
            <a href="<?= BASE_URL ?>/juridico/processos" class="p-2 bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-slate-200 dark:border-slate-700 text-slate-500 hover:text-purple-600 transition-all">
                <i class='bx bx-left-arrow-alt text-2xl'></i>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-slate-800 dark:text-white tracking-tight"><?= $pageTitle ?></h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 font-medium">Preencha as informações técnicas do processo judicial ou administrativo.</p>
            </div>
        </div>
    </div>

    <div class="sys-card !p-0 overflow-hidden">
        <div class="p-5 border-b border-slate-100 dark:border-slate-700 bg-slate-50/50 dark:bg-white/5 flex items-center gap-3">
            <div class="icon-box-3d item-purple !w-8 !h-8">
                <i class='bx bxs-edit-alt !text-sm'></i>
            </div>
            <h3 class="font-bold text-slate-700 dark:text-slate-200">Dados do Processo</h3>
        </div>

        <form action="<?= BASE_URL ?>/juridico/salvar" method="POST" class="p-6">
            <!-- Token de Segurança e ID -->
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            <input type="hidden" name="id" value="<?= $proc['id'] ?? '' ?>">

            <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
                <!-- Número do Processo -->
                <div class="md:col-span-4">
                    <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Número do Processo</label>
                    <input type="text" name="numero" value="<?= htmlspecialchars($proc['numero'] ?? '') ?>" 
                           placeholder="0000000-00.0000.0.00.0000" required
                           class="w-full px-4 py-2.5 bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-purple-500 outline-none transition-all font-medium">
                </div>

                <!-- Tipo de Processo -->
                <div class="md:col-span-4">
                    <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Tipo de Ação</label>
                    <select name="tipo" required class="w-full px-4 py-2.5 bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-slate-700 rounded-xl text-sm outline-none focus:ring-2 focus:ring-purple-500 font-medium">
                        <option value="">Selecione...</option>
                        <option value="Cível" <?= ($proc['tipo'] ?? '') === 'Cível' ? 'selected' : '' ?>>Cível</option>
                        <option value="Trabalhista" <?= ($proc['tipo'] ?? '') === 'Trabalhista' ? 'selected' : '' ?>>Trabalhista</option>
                        <option value="Administrativo" <?= ($proc['tipo'] ?? '') === 'Administrativo' ? 'selected' : '' ?>>Administrativo</option>
                        <option value="Tributário" <?= ($proc['tipo'] ?? '') === 'Tributário' ? 'selected' : '' ?>>Tributário</option>
                        <option value="Ambiental" <?= ($proc['tipo'] ?? '') === 'Ambiental' ? 'selected' : '' ?>>Ambiental</option>
                    </select>
                </div>

                <!-- Status -->
                <div class="md:col-span-4">
                    <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Status Atual</label>
                    <select name="status" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-slate-700 rounded-xl text-sm outline-none focus:ring-2 focus:ring-purple-500 font-medium">
                        <option value="Ativo" <?= ($proc['status'] ?? 'Ativo') === 'Ativo' ? 'selected' : '' ?>>Ativo (Em curso)</option>
                        <option value="Suspenso" <?= ($proc['status'] ?? '') === 'Suspenso' ? 'selected' : '' ?>>Suspenso</option>
                        <option value="Concluído" <?= ($proc['status'] ?? '') === 'Concluído' ? 'selected' : '' ?>>Concluído / Arquivado</option>
                        <option value="Acordo" <?= ($proc['status'] ?? '') === 'Acordo' ? 'selected' : '' ?>>Acordo Realizado</option>
                    </select>
                </div>

                <!-- Fase do Processo -->
                <div class="md:col-span-4">
                    <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Fase Processual</label>
                    <select name="fase" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-slate-700 rounded-xl text-sm outline-none focus:ring-2 focus:ring-purple-500 font-medium">
                        <option value="Conhecimento" <?= ($proc['fase'] ?? '') === 'Conhecimento' ? 'selected' : '' ?>>Conhecimento</option>
                        <option value="Instrução" <?= ($proc['fase'] ?? '') === 'Instrução' ? 'selected' : '' ?>>Instrução</option>
                        <option value="Recurso" <?= ($proc['fase'] ?? '') === 'Recurso' ? 'selected' : '' ?>>Recurso</option>
                        <option value="Execução" <?= ($proc['fase'] ?? '') === 'Execução' ? 'selected' : '' ?>>Execução</option>
                        <option value="Arquivado" <?= ($proc['fase'] ?? '') === 'Arquivado' ? 'selected' : '' ?>>Arquivado</option>
                    </select>
                </div>

                <!-- Cliente Vinculado -->
                <div class="md:col-span-6">
                    <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Cliente / Interessado</label>
                    <select name="cliente_id" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-slate-700 rounded-xl text-sm outline-none focus:ring-2 focus:ring-purple-500 font-medium">
                        <option value="">Nenhum vínculo direto</option>
                        <?php foreach ($clientes as $cliente): ?>
                            <option value="<?= $cliente['id'] ?>" <?= ($proc['cliente_id'] ?? '') == $cliente['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cliente['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Parte Contrária -->
                <div class="md:col-span-6">
                    <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Parte Contrária</label>
                    <input type="text" name="parte_contraria" value="<?= htmlspecialchars($proc['parte_contraria'] ?? '') ?>" 
                           placeholder="Ex: Empresa de Energia S.A."
                           class="w-full px-4 py-2.5 bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-purple-500 outline-none transition-all font-medium">
                </div>

                <!-- Tribunal -->
                <div class="md:col-span-4">
                    <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Tribunal / Órgão</label>
                    <input type="text" name="tribunal" value="<?= htmlspecialchars($proc['tribunal'] ?? '') ?>" 
                           placeholder="Ex: TJSP, TRT2, JFDF"
                           class="w-full px-4 py-2.5 bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-purple-500 outline-none transition-all font-medium">
                </div>

                <!-- Vara / Câmara -->
                <div class="md:col-span-4">
                    <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Vara / Câmara</label>
                    <input type="text" name="vara_camara" value="<?= htmlspecialchars($proc['vara_camara'] ?? '') ?>" 
                           placeholder="Ex: 2ª Vara Cível de Santos"
                           class="w-full px-4 py-2.5 bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-purple-500 outline-none transition-all font-medium">
                </div>

                <!-- Data de Distribuição -->
                <div class="md:col-span-4">
                    <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Data de Distribuição</label>
                    <input type="date" name="data_distribuicao" value="<?= $proc['data_distribuicao'] ?? '' ?>" 
                           class="w-full px-4 py-2.5 bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-purple-500 outline-none transition-all font-medium">
                </div>

                <!-- Objeto da Ação -->
                <div class="md:col-span-12">
                    <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Objeto da Ação</label>
                    <input type="text" name="objeto" value="<?= htmlspecialchars($proc['objeto'] ?? '') ?>" 
                           placeholder="Resumo do que se trata o processo..."
                           class="w-full px-4 py-2.5 bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-purple-500 outline-none transition-all font-medium">
                </div>

                <!-- Valor da Causa -->
                <div class="md:col-span-4">
                    <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Valor da Causa (R$)</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm font-bold">R$</span>
                        <input type="text" name="valor_causa" id="valor_causa" 
                               value="<?= number_format($proc['valor_causa'] ?? 0, 2, ',', '.') ?>" 
                               class="w-full pl-10 pr-4 py-2.5 bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-purple-500 outline-none transition-all font-bold text-slate-700 dark:text-white">
                    </div>
                </div>

                <!-- Observações -->
                <div class="md:col-span-8">
                    <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Observações Adicionais</label>
                    <textarea name="observacoes" rows="3" 
                              class="w-full px-4 py-2.5 bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-purple-500 outline-none transition-all font-medium"><?= htmlspecialchars($proc['observacoes'] ?? '') ?></textarea>
                </div>
            </div>

            <!-- Botões de Ação -->
            <div class="mt-8 pt-6 border-t border-slate-100 dark:border-slate-700 flex flex-col md:flex-row justify-end gap-3">
                <a href="<?= BASE_URL ?>/juridico/processos" class="px-6 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 font-bold text-sm hover:bg-slate-50 dark:hover:bg-white/5 transition-all text-center">
                    Cancelar
                </a>
                <button type="submit" class="sys-btn-primary !bg-purple-600 hover:!bg-purple-700 !px-10 !py-2.5 shadow-lg shadow-purple-500/20">
                    <i class='bx bx-check-double mr-2 text-lg'></i> Salvar Processo
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Máscara simples para valor monetário (pode ser substituída por uma lib como imask)
    const inputValor = document.getElementById('valor_causa');
    inputValor.addEventListener('input', (e) => {
        let value = e.target.value.replace(/\D/g, '');
        value = (value / 100).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        e.target.value = value;
    });
</script>
