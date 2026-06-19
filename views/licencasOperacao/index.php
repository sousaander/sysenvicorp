<?php
$canManage = $this->session->hasPermission('licencas_operacao_manage');
?>

<!-- Cabeçalho do módulo -->
<div class="flex items-start justify-between mb-6">
    <div>
        <h2 class="text-2xl font-bold text-gray-800">Módulo Licenças de Operação</h2>
        <p class="mt-1 text-sm text-gray-500">Controle rigoroso sobre todas as licenças, alvarás e certificações necessárias para a operação.</p>
    </div>
    <?php if ($canManage): ?>
        <a href="<?php echo BASE_URL; ?>/licencasOperacao/novo"
           class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-semibold rounded-lg hover:bg-indigo-700 transition shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Nova Licença
        </a>
    <?php endif; ?>
</div>

<!-- Cards de resumo -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-xl border border-gray-200 p-5 border-l-4 border-l-indigo-500">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Total Ativas</p>
        <p id="card-total" class="text-3xl font-bold text-indigo-600 mt-1"><?php echo $totalLicencas ?? 0; ?></p>
        <p class="text-xs text-gray-400 mt-1">Documentos em conformidade</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-5 border-l-4 border-l-orange-400">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Vencendo em 30 dias</p>
        <p id="card-vencendo" class="text-3xl font-bold text-orange-500 mt-1"><?php echo $vencimento30Dias ?? 0; ?></p>
        <p class="text-xs text-gray-400 mt-1">Ações de renovação urgentes</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-5 border-l-4 border-l-red-500">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Licenças Vencidas</p>
        <p id="card-vencidas" class="text-3xl font-bold text-red-600 mt-1"><?php echo $vencidas ?? 0; ?></p>
        <p class="text-xs text-gray-400 mt-1">Zero é a meta!</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-5 border-l-4 border-l-yellow-400">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Em Renovação</p>
        <p id="card-renovacao" class="text-3xl font-bold text-yellow-500 mt-1"><?php echo $emRenovacao ?? 0; ?></p>
        <p class="text-xs text-gray-400 mt-1">Acompanhamento e prazos</p>
    </div>
</div>

<!-- Conteúdo principal -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- Tabela de licenças críticas -->
    <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4 pb-3 border-b border-gray-100">
            <h3 class="text-base font-semibold text-gray-800">Gestão e Monitoramento de Licenças</h3>
            <!-- Filtro rápido por status -->
            <div class="flex gap-2">
                <button onclick="filterTable('', this)" class="filter-btn active-filter text-xs px-3 py-1.5 rounded-full border border-gray-300 font-medium transition bg-indigo-600 text-white border-indigo-600">Todas</button>
                <button onclick="filterTable('Vigente', this)" class="filter-btn text-xs px-3 py-1.5 rounded-full border border-gray-300 text-gray-600 transition">Vigentes</button>
                <button onclick="filterTable('Pendente', this)" class="filter-btn text-xs px-3 py-1.5 rounded-full border border-gray-300 text-gray-600 transition">Pendentes</button>
                <button onclick="filterTable('Vencida', this)" class="filter-btn text-xs px-3 py-1.5 rounded-full border border-gray-300 text-gray-600 transition">Vencidas</button>
            </div>
        </div>

        <!-- Busca -->
        <div class="mb-4">
            <div class="relative">
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <input type="text" id="searchLicenca" placeholder="Buscar licença ou órgão emissor..."
                    class="w-full pl-4 pr-9 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-indigo-400 focus:ring-1 focus:ring-indigo-300"
                    oninput="applyFilters()">
            </div>
        </div>

        <?php if (!empty($todasLicencas)): ?>
            <div class="overflow-x-auto">
                <table class="min-w-full" id="licencasTable">
                    <thead>
                        <tr class="bg-gray-50 rounded-lg">
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider rounded-l-lg">Licença</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Órgão</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Vencimento</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                            <?php if ($canManage): ?>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider rounded-r-lg">Ações</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach ($todasLicencas as $license):
                            $vencimento = strtotime($license['vencimento'] ?? '');
                            $diff = ($vencimento - time()) / (60 * 60 * 24);
                            if ($diff <= 0)      { $dateClass = 'text-red-600 font-bold'; $urgency = 'vencida'; }
                            elseif ($diff <= 30) { $dateClass = 'text-red-500 font-semibold'; $urgency = 'critica'; }
                            elseif ($diff <= 90) { $dateClass = 'text-orange-500 font-semibold'; $urgency = 'atencao'; }
                            else                 { $dateClass = 'text-green-600'; $urgency = 'ok'; }

                            $statusBadge = [
                                'Vigente'            => 'bg-green-100 text-green-800',
                                'Pendente Renovação' => 'bg-orange-100 text-orange-800',
                                'Vencendo'           => 'bg-red-100 text-red-800',
                                'Em Análise'         => 'bg-blue-100 text-blue-800',
                                'Vencida'            => 'bg-red-100 text-red-800',
                            ];
                            $badge = $statusBadge[$license['status']] ?? 'bg-gray-100 text-gray-700';
                        ?>
                            <tr class="hover:bg-gray-50 transition table-row" data-status="<?php echo htmlspecialchars($license['status']); ?>"
                                data-name="<?php echo strtolower(htmlspecialchars($license['nome'])); ?>"
                                data-orgao="<?php echo strtolower(htmlspecialchars($license['orgao'] ?? '')); ?>">
                                <td class="px-4 py-3">
                                    <a href="<?php echo BASE_URL; ?>/licencasOperacao/detalheLicenca/<?php echo $license['id']; ?>"
                                       class="text-indigo-600 hover:text-indigo-800 font-medium text-sm">
                                        <?php echo htmlspecialchars($license['nome']); ?>
                                    </a>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-500"><?php echo htmlspecialchars($license['orgao'] ?? ''); ?></td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-1.5">
                                        <?php if ($diff <= 30 && $diff >= 0): ?>
                                            <svg class="w-3.5 h-3.5 <?php echo $dateClass; ?>" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                        <?php endif; ?>
                                        <span class="text-sm <?php echo $dateClass; ?>">
                                            <?php echo date('d/m/Y', $vencimento); ?>
                                        </span>
                                    </div>
                                    <?php if ($diff > 0 && $diff <= 90): ?>
                                        <p class="text-xs text-gray-400 mt-0.5"><?php echo round($diff); ?> dias restantes</p>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full <?php echo $badge; ?>">
                                        <?php echo htmlspecialchars($license['status']); ?>
                                    </span>
                                </td>
                                <?php if ($canManage): ?>
                                    <td class="px-4 py-3 text-right">
                                        <div class="flex items-center justify-end gap-3">
                                            <a href="<?php echo BASE_URL; ?>/licencasOperacao/editar/<?php echo $license['id']; ?>"
                                               class="text-indigo-600 hover:text-indigo-900 font-medium" title="Editar"><i class='bx bx-edit-alt text-lg'></i></a>
                                            <a href="<?php echo BASE_URL; ?>/licencasOperacao/excluir/<?php echo $license['id']; ?>"
                                               class="text-red-500 hover:text-red-700 font-medium"
                                               onclick="return confirm('Confirma exclusão desta licença?')" title="Excluir"><i class='bx bx-trash text-lg'></i></a>
                                        </div>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <p class="text-xs text-gray-400 mt-4" id="tableCount"></p>
        <?php else: ?>
            <div class="text-center py-12">
                <svg class="mx-auto w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p class="mt-3 text-sm text-gray-500">Nenhuma licença com status crítico no momento.</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Painel lateral -->
    <div class="space-y-4">

        <!-- Ações rápidas -->
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h3 class="text-sm font-semibold text-gray-800 mb-4">Ações Rápidas</h3>

            <div class="mb-4">
                <label for="dataRenovacao" class="block text-xs font-medium text-gray-600 mb-1.5">Buscar por data de renovação</label>
                <input type="date" id="dataRenovacao"
                    class="w-full text-sm border border-gray-200 rounded-lg p-2 focus:outline-none focus:border-indigo-400 focus:ring-1 focus:ring-indigo-300">
            </div>

            <?php if ($canManage): ?>
                <button onclick="openUploadModal()"
                    class="w-full mb-3 flex items-center justify-center gap-2 px-4 py-2.5 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                    Upload de Documento
                </button>

                <a href="<?php echo BASE_URL; ?>/licencasOperacao/relatorioNaoConformidade"
                    class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-white border border-gray-200 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Relatório de Não Conformidade
                </a>
            <?php endif; ?>
        </div>

        <!-- Legenda de vencimentos -->
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h3 class="text-sm font-semibold text-gray-800 mb-3">Legenda de Vencimento</h3>
            <div class="space-y-2.5">
                <div class="flex items-center gap-2.5">
                    <span class="w-3 h-3 rounded-full bg-green-500 flex-shrink-0"></span>
                    <span class="text-xs text-gray-600">Acima de 90 dias — Em dia</span>
                </div>
                <div class="flex items-center gap-2.5">
                    <span class="w-3 h-3 rounded-full bg-orange-400 flex-shrink-0"></span>
                    <span class="text-xs text-gray-600">31 a 90 dias — Atenção</span>
                </div>
                <div class="flex items-center gap-2.5">
                    <span class="w-3 h-3 rounded-full bg-red-500 flex-shrink-0"></span>
                    <span class="text-xs text-gray-600">Até 30 dias — Urgente</span>
                </div>
                <div class="flex items-center gap-2.5">
                    <span class="w-3 h-3 rounded-full bg-gray-400 flex-shrink-0"></span>
                    <span class="text-xs text-gray-600">Vencida — Ação imediata</span>
                </div>
            </div>
        </div>

        <!-- Dica de conformidade -->
        <div class="bg-indigo-50 rounded-xl border border-indigo-100 p-5">
            <div class="flex items-start gap-2">
                <svg class="w-4 h-4 text-indigo-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p class="text-xs text-indigo-700">Mantenha os arquivos digitais de todas as licenças anexados ao sistema para facilitar auditorias internas e externas.</p>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Upload -->
<div id="modal-upload" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50 flex items-center justify-center">
    <div class="relative mx-auto w-full max-w-md bg-white rounded-xl shadow-xl p-6">
        <div class="flex justify-between items-center mb-5">
            <h3 class="text-base font-semibold text-gray-800">Upload de Documento</h3>
            <button onclick="closeUploadModal()" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form action="<?php echo BASE_URL; ?>/licencasOperacao/uploadDocumento" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? '' ?>">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Licença relacionada</label>
                    <select name="licenca_id" required class="w-full text-sm border border-gray-300 rounded-lg p-2.5 focus:outline-none focus:border-indigo-400 focus:ring-1 focus:ring-indigo-300">
                        <option value="">Selecione a licença...</option>
                        <?php if (!empty($todasLicencas)): ?>
                            <?php foreach ($todasLicencas as $l): ?>
                                <option value="<?php echo $l['id']; ?>">
                                    <?php echo htmlspecialchars($l['nome'] . ' — ' . $l['orgao_emissor']); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Arquivo (PDF ou imagem)</label>
                    <div class="border-2 border-dashed border-gray-200 rounded-lg p-4 text-center hover:border-indigo-300 transition">
                        <svg class="mx-auto w-8 h-8 text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                        <input type="file" name="documento" required accept=".pdf,.jpg,.jpeg,.png"
                            class="block w-full text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        <p class="text-xs text-gray-400 mt-1">PDF, JPG ou PNG — máx. 10MB</p>
                    </div>
                </div>
            </div>
            <div class="mt-5 flex justify-end gap-3">
                <button type="button" onclick="closeUploadModal()"
                    class="px-4 py-2 text-sm text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                    Cancelar
                </button>
                <button type="submit"
                    class="px-4 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition">
                    Enviar documento
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openUploadModal() { document.getElementById('modal-upload').classList.remove('hidden'); }
function closeUploadModal() { document.getElementById('modal-upload').classList.add('hidden'); }

let currentStatusFilter = '';

// Filtro por status
function filterTable(status, btn) {
    document.querySelectorAll('.filter-btn').forEach(b => {
        b.classList.remove('bg-indigo-600', 'text-white', 'border-indigo-600', 'active-filter');
        b.classList.add('text-gray-600', 'border-gray-300');
    });
    
    btn.classList.add('bg-indigo-600', 'text-white', 'border-indigo-600', 'active-filter');
    btn.classList.remove('text-gray-600');

    currentStatusFilter = status;
    applyFilters();
}

function applyFilters() {
    const query = (document.getElementById('searchLicenca').value || '').toLowerCase();
    
    document.querySelectorAll('.table-row').forEach(row => {
        const rowStatus = row.dataset.status || '';
        const name = row.dataset.name || '';
        const orgao = row.dataset.orgao || '';
        
        const matchStatus = !currentStatusFilter || rowStatus.includes(currentStatusFilter);
        const matchSearch = !query || name.includes(query) || orgao.includes(query);
        
        row.style.display = (matchStatus && matchSearch) ? '' : 'none';
    });
    updateCount();
}

function updateCount() {
    const visible = document.querySelectorAll('.table-row:not([style*="none"])').length;
    const total = document.querySelectorAll('.table-row').length;
    const el = document.getElementById('tableCount');
    if (el) el.textContent = visible < total ? `Exibindo ${visible} de ${total} licenças` : `${total} licença(s) no total`;
}
updateCount();
</script>
