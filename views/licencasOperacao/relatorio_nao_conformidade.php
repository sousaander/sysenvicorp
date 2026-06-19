<div class="mb-6">
    <div class="flex items-center gap-2 text-sm text-gray-500 mb-2">
        <a href="<?= BASE_URL ?>/licencasOperacao" class="hover:text-indigo-600">Licenças de Operação</a>
        <i class='bx bx-chevron-right'></i>
        <span>Relatório de Não Conformidades</span>
    </div>
    <div class="flex justify-between items-center">
        <h2 class="text-2xl font-bold text-gray-800">Relatório de Não Conformidades</h2>
        <div class="flex gap-2">
            <button onclick="window.print()" class="px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-semibold rounded-lg hover:bg-gray-50 transition flex items-center gap-2">
                <i class='bx bx-printer'></i> Imprimir
            </button>
            <a href="<?= BASE_URL ?>/licencasOperacao" class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-semibold rounded-lg hover:bg-gray-200 transition">
                Voltar
            </a>
        </div>
    </div>
</div>

<div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
    <div class="p-6 border-b border-gray-100 bg-gray-50">
        <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider">Histórico de Ocorrências e Auditoria</h3>
    </div>
    
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Data</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Licença</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Tipo</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Descrição</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Prioridade</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Responsável</th>
                    <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Ações</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
                <?php if (!empty($ocorrencias)): ?>
                    <?php foreach ($ocorrencias as $oc): 
                        $statusColor = match($oc['status']) {
                            'Aberta' => 'bg-red-100 text-red-700',
                            'Em Tratativa' => 'bg-yellow-100 text-yellow-700',
                            'Concluída' => 'bg-green-100 text-green-700',
                            default => 'bg-gray-100 text-gray-700'
                        };
                        $priorityColor = match($oc['prioridade'] ?? 'Média') {
                            'Alta' => 'text-red-600 font-bold',
                            'Baixa' => 'text-green-600',
                            default => 'text-gray-600'
                        };
                        $tipoColor = match($oc['tipo']) {
                            'Não Conformidade' => 'text-red-600 font-bold',
                            'Observação' => 'text-blue-600',
                            default => 'text-gray-600'
                        };
                    ?>
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                <?= date('d/m/Y', strtotime($oc['data_ocorrencia'])) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-semibold text-gray-800"><?= htmlspecialchars($oc['licenca_nome']) ?></div>
                                <div class="text-xs text-gray-400"><?= htmlspecialchars($oc['orgao_emissor']) ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-xs <?= $tipoColor ?>">
                                <?= htmlspecialchars($oc['tipo']) ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                <div class="max-w-xs truncate" title="<?= htmlspecialchars($oc['descricao']) ?>">
                                    <?= htmlspecialchars($oc['descricao']) ?>
                                </div>
                                <?php if (!empty($oc['plano_acao'])): ?>
                                    <div class="text-[10px] text-indigo-500 mt-1 italic">Plano: <?= htmlspecialchars($oc['plano_acao']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-xs <?= $priorityColor ?>">
                                <?= htmlspecialchars($oc['prioridade'] ?? 'Média') ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-[10px] font-bold rounded-full <?= $statusColor ?>">
                                    <?= strtoupper($oc['status']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?= htmlspecialchars($oc['responsavel'] ?? '—') ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button onclick='openTratativaModal(<?= htmlspecialchars(json_encode($oc), ENT_QUOTES, 'UTF-8') ?>)' 
                                    class="text-indigo-600 hover:text-indigo-900 bg-indigo-50 px-3 py-1 rounded transition flex items-center gap-1 ml-auto">
                                    <i class='bx bx-edit-alt'></i> Tratar
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-sm text-gray-500 italic">
                            Nenhuma não conformidade ou ocorrência registrada até o momento.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal de Tratativa (Atividades) -->
<div id="modal-tratativa" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50 flex items-center justify-center">
    <div class="relative mx-auto w-full max-w-md bg-white rounded-xl shadow-xl p-6">
        <div class="flex justify-between items-center mb-5">
            <h3 class="text-lg font-bold text-gray-800">Tratativa de Ocorrência</h3>
            <button onclick="closeTratativaModal()" class="text-gray-400 hover:text-gray-600 transition">
                <i class='bx bx-x text-2xl'></i>
            </button>
        </div>
        
        <form action="<?= BASE_URL ?>/licencasOperacao/atualizarOcorrencia" method="POST">
            <input type="hidden" name="id" id="tratativa-id">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Status</label>
                    <select name="status" id="tratativa-status" required class="w-full text-sm border border-gray-300 rounded-lg p-2.5 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="Aberta">Aberta</option>
                        <option value="Em Tratativa">Em Tratativa</option>
                        <option value="Concluída">Concluída</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Prioridade</label>
                    <select name="prioridade" id="tratativa-prioridade" required class="w-full text-sm border border-gray-300 rounded-lg p-2.5 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="Baixa">Baixa</option>
                        <option value="Média">Média</option>
                        <option value="Alta">Alta</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Plano de Ação / Atividades Executadas</label>
                    <textarea name="plano_acao" id="tratativa-plano" rows="4" class="w-full text-sm border border-gray-300 rounded-lg p-2.5 focus:ring-indigo-500 focus:border-indigo-500" placeholder="Descreva o que foi feito ou o planejamento para solução..."></textarea>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Responsável pela Ação</label>
                    <input type="text" name="responsavel" id="tratativa-responsavel" required class="w-full text-sm border border-gray-300 rounded-lg p-2.5 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <button type="button" onclick="closeTratativaModal()" class="px-4 py-2 text-sm font-semibold text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 transition">Cancelar</button>
                <button type="submit" class="px-6 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 shadow-md transition">Salvar Tratativa</button>
            </div>
        </form>
    </div>
</div>

<script>
function openTratativaModal(ocorrencia) {
    document.getElementById('tratativa-id').value = ocorrencia.id;
    document.getElementById('tratativa-status').value = ocorrencia.status;
    document.getElementById('tratativa-prioridade').value = ocorrencia.prioridade || 'Média';
    document.getElementById('tratativa-plano').value = ocorrencia.plano_acao || '';
    document.getElementById('tratativa-responsavel').value = ocorrencia.responsavel || '';
    document.getElementById('modal-tratativa').classList.remove('hidden');
}

function closeTratativaModal() {
    document.getElementById('modal-tratativa').classList.add('hidden');
}
</script>