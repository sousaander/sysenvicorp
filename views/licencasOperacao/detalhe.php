<?php
/** @var array $licenca */

// Verifica permissão específica ou se o usuário é administrador total
$isAdmin = $this->session->isAdmin();
$canManageNC = $isAdmin || $this->session->hasPermission('licencas_operacao_nc_manage');

$csrf_token = $csrf_token ?? '';
?>

<div class="mb-6">
    <div class="flex items-center gap-2 text-sm text-gray-500 mb-2">
        <a href="<?= BASE_URL ?>/licencasOperacao" class="hover:text-indigo-600">Licenças de Operação</a>
        <i class='bx bx-chevron-right'></i>
        <span>Detalhes da Licença</span>
    </div>
    <div class="flex justify-between items-start">
        <h2 class="text-2xl font-bold text-gray-800"><?= htmlspecialchars($licenca['nome'] ?? 'Licença') ?></h2>
        <div class="flex gap-2">
            <?php if ($canManageNC): ?>
                <button onclick="openOcorrenciaModal()" 
                   class="px-4 py-2 bg-orange-500 text-white text-sm font-bold rounded-lg hover:bg-orange-600 transition-all flex items-center gap-2 shadow-md hover:shadow-lg active:scale-95 border-b-2 border-orange-700">
                    <i class='bx bx-error-circle'></i> Registrar Ocorrência
                </button>
            <?php endif; ?>
            <a href="<?= BASE_URL ?>/licencasOperacao/editar/<?= $licenca['id'] ?>" 
               class="px-4 py-2 bg-indigo-50 text-indigo-700 text-sm font-semibold rounded-lg hover:bg-indigo-100 transition">
                Editar Licença
            </a>
            <a href="<?= BASE_URL ?>/licencasOperacao" 
               class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-semibold rounded-lg hover:bg-gray-200 transition">
                Voltar
            </a>
        </div>
    </div>
</div>

<!-- Modal para Registrar Ocorrência -->
<div id="modal-ocorrencia" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50 flex items-center justify-center">
    <div class="relative mx-auto w-full max-w-lg bg-white rounded-xl shadow-xl p-6">
        <div class="flex justify-between items-center mb-5">
            <h3 class="text-lg font-bold text-gray-800">Registrar Ocorrência / Não Conformidade</h3>
            <button onclick="closeOcorrenciaModal()" class="text-gray-400 hover:text-gray-600 transition">
                <i class='bx bx-x text-2xl'></i>
            </button>
        </div>
        
        <form action="<?= BASE_URL ?>/licencasOperacao/salvarOcorrencia" method="POST">
            <input type="hidden" name="licenca_id" value="<?= $licenca['id'] ?>">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Data da Ocorrência</label>
                        <input type="date" name="data_ocorrencia" required value="<?= date('Y-m-d') ?>"
                            class="w-full text-sm border border-gray-300 rounded-lg p-2.5 focus:ring-orange-500 focus:border-orange-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Tipo</label>
                        <select name="tipo" required class="w-full text-sm border border-gray-300 rounded-lg p-2.5 focus:ring-orange-500 focus:border-orange-500">
                            <option value="Não Conformidade">Não Conformidade</option>
                            <option value="Observação">Observação</option>
                            <option value="Melhoria">Melhoria</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Prioridade</label>
                    <select name="prioridade" required class="w-full text-sm border border-gray-300 rounded-lg p-2.5 focus:ring-orange-500 focus:border-orange-500">
                        <option value="Baixa">Baixa</option>
                        <option value="Média" selected>Média</option>
                        <option value="Alta">Alta</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Descrição do Evento</label>
                    <textarea name="descricao" required rows="3" 
                        class="w-full text-sm border border-gray-300 rounded-lg p-2.5 focus:ring-orange-500 focus:border-orange-500"
                        placeholder="Descreva o que aconteceu ou o que foi identificado..."></textarea>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Plano de Ação (Tratativa)</label>
                    <textarea name="plano_acao" rows="2" 
                        class="w-full text-sm border border-gray-300 rounded-lg p-2.5 focus:ring-orange-500 focus:border-orange-500"
                        placeholder="O que será feito para corrigir ou evitar recorrência?"></textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Responsável</label>
                    <input type="text" name="responsavel" required value="<?= $this->session->get('user_name', '') ?>"
                            class="w-full text-sm border border-gray-300 rounded-lg p-2.5 focus:ring-orange-500 focus:border-orange-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Status Inicial</label>
                        <select name="status" class="w-full text-sm border border-gray-300 rounded-lg p-2.5 focus:ring-orange-500 focus:border-orange-500">
                            <option value="Aberta">Aberta</option>
                            <option value="Em Tratativa">Em Tratativa</option>
                            <option value="Concluída">Concluída</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <button type="button" onclick="closeOcorrenciaModal()"
                    class="px-4 py-2 text-sm font-semibold text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 transition">
                    Cancelar
                </button>
                <button type="submit"
                    class="px-6 py-2 text-sm font-bold text-white bg-orange-500 rounded-lg hover:bg-orange-600 shadow-md hover:shadow-lg transition-all active:scale-95 border-b-2 border-orange-700">
                    Registrar Ocorrência
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openOcorrenciaModal() {
    document.getElementById('modal-ocorrencia').classList.remove('hidden');
}

function closeOcorrenciaModal() {
    document.getElementById('modal-ocorrencia').classList.add('hidden');
}

// Fechar modal ao clicar fora dele
window.onclick = function(event) {
    let modal = document.getElementById('modal-ocorrencia');
    if (event.target == modal) {
        closeOcorrenciaModal();
    }
}
</script>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Informações Principais -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Identificação e Classificação -->
        <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2 flex items-center gap-2">
                <i class='bx bx-id-card text-indigo-500'></i> Identificação e Classificação
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Órgão Emissor</p>
                    <p class="text-sm font-semibold text-gray-700"><?= htmlspecialchars($licenca['orgao_emissor'] ?? '—') ?></p>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Número da Licença</p>
                    <p class="text-sm font-semibold text-gray-700"><?= htmlspecialchars($licenca['numero_licenca'] ?? '—') ?></p>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Tipo / Categoria</p>
                    <p class="text-sm font-semibold text-gray-700"><?= htmlspecialchars($licenca['tipo_licenca'] ?? '—') ?> / <?= htmlspecialchars($licenca['categoria'] ?? '—') ?></p>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Produto / Serviço</p>
                    <p class="text-sm font-semibold text-gray-700"><?= htmlspecialchars($licenca['produto_servico'] ?? '—') ?></p>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Data de Emissão</p>
                    <p class="text-sm font-semibold text-gray-700"><?= !empty($licenca['data_emissao']) ? date('d/m/Y', strtotime($licenca['data_emissao'])) : '—' ?></p>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Data de Vencimento</p>
                    <p class="text-sm font-semibold text-gray-700"><?= !empty($licenca['data_vencimento']) ? date('d/m/Y', strtotime($licenca['data_vencimento'])) : '—' ?></p>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Projeto Vinculado</p>
                    <p class="text-sm font-semibold text-indigo-600">
                        <?= !empty($licenca['projeto_nome']) ? htmlspecialchars($licenca['projeto_nome']) : 'Global / Nenhum' ?>
                    </p>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Número de Série / Registro</p>
                    <p class="text-sm font-semibold text-gray-700"><?= htmlspecialchars($licenca['numero_serie'] ?? '—') ?></p>
                </div>
            </div>
        </div>

        <!-- Custos e Contrato -->
        <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2 flex items-center gap-2">
                <i class='bx bx-money text-green-500'></i> Custos e Contrato
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Valor da Licença</p>
                    <p class="text-sm font-semibold text-gray-700">
                        <?= !empty($licenca['valor_licenca']) ? ($licenca['moeda'] ?? 'BRL') . ' ' . number_format($licenca['valor_licenca'], 2, ',', '.') : '—' ?>
                    </p>
                    <p class="text-[10px] text-gray-400"><?= htmlspecialchars($licenca['frequencia_pagamento'] ?? '') ?></p>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Centro de Custo</p>
                    <p class="text-sm font-semibold text-gray-700"><?= htmlspecialchars($licenca['centro_custo'] ?? '—') ?></p>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Nº Contrato Base</p>
                    <p class="text-sm font-semibold text-gray-700"><?= htmlspecialchars($licenca['numero_contrato'] ?? '—') ?></p>
                </div>
                <div class="md:col-span-2">
                    <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Fornecedor (CNPJ)</p>
                    <p class="text-sm font-semibold text-gray-700"><?= htmlspecialchars($licenca['cnpj_fornecedor'] ?? '—') ?></p>
                </div>
            </div>
        </div>

        <!-- Observações -->
        <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm">
            <p class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-2">Descrição e Observações</p>
            <div class="p-3 bg-gray-50 rounded-lg text-sm text-gray-600 border border-gray-100 min-h-[80px]">
                <?= !empty($licenca['observacoes']) ? nl2br(htmlspecialchars($licenca['observacoes'])) : 'Nenhuma observação registrada.' ?>
            </div>
        </div>

        <!-- Últimas Ocorrências -->
        <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm">
            <div class="flex justify-between items-center mb-4 border-b pb-2">
                <h3 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                    <i class='bx bx-history text-orange-500'></i> Histórico Recente de Ocorrências
                </h3>
                <a href="<?= BASE_URL ?>/licencasOperacao/relatorioNaoConformidade" class="text-xs text-indigo-600 hover:underline">Ver relatório completo</a>
            </div>
            
            <?php if (!empty($ocorrencias)): ?>
                <div class="space-y-4">
                    <?php foreach ($ocorrencias as $oc): 
                        $statusColor = match($oc['status']) {
                            'Aberta' => 'text-red-600 bg-red-50',
                            'Em Tratativa' => 'text-yellow-700 bg-yellow-50',
                            'Concluída' => 'text-green-700 bg-green-50',
                            default => 'text-gray-600 bg-gray-50'
                        };
                        $priorityColor = match($oc['prioridade'] ?? 'Média') {
                            'Alta' => 'text-red-600',
                            'Baixa' => 'text-green-600',
                            default => 'text-gray-400'
                        };
                    ?>
                        <div class="flex gap-4 p-3 rounded-lg border border-gray-100 hover:bg-gray-50 transition">
                            <div class="flex-shrink-0 text-center w-12 pt-1">
                                <span class="block text-[10px] font-bold text-gray-400 uppercase"><?= date('M', strtotime($oc['data_ocorrencia'])) ?></span>
                                <span class="block text-lg font-bold text-gray-700 leading-none"><?= date('d', strtotime($oc['data_ocorrencia'])) ?></span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex justify-between items-start mb-1">
                                    <span class="text-xs font-bold uppercase <?= $oc['tipo'] === 'Não Conformidade' ? 'text-red-500' : 'text-blue-500' ?>">
                                        <?= htmlspecialchars($oc['tipo']) ?>
                                        <span class="ml-1 text-[10px] font-normal lowercase italic <?= $priorityColor ?>">(prioridade: <?= htmlspecialchars($oc['prioridade'] ?? 'Média') ?>)</span>
                                    </span>
                                    <span class="px-2 py-0.5 text-[9px] font-bold rounded-full <?= $statusColor ?>">
                                        <?= strtoupper($oc['status']) ?>
                                    </span>
                                </div>
                                <p class="text-sm text-gray-700 line-clamp-2" title="<?= htmlspecialchars($oc['descricao']) ?>"><?= htmlspecialchars($oc['descricao']) ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-6 bg-gray-50 rounded-lg border border-dashed border-gray-300">
                    <p class="text-sm text-gray-500 italic">Nenhuma ocorrência registrada para esta licença.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Documentação Anexa -->
        <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2 flex items-center gap-2">
                <i class='bx bx-file text-red-500'></i> Documento Digitalizado
            </h3>
            <?php if (!empty($licenca['documento_path'])): ?>
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg border border-gray-100">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-white rounded flex items-center justify-center border border-gray-200 text-red-500">
                            <i class='bx bxs-file-pdf text-2xl'></i>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-700">
                                Documento da Licença
                                <?php if (!empty($licenca['documento_path'])): ?>
                                    <span class="text-xs text-gray-400">(<?= htmlspecialchars($licenca['documento_path']) ?>)</span>
                                <?php endif; ?>
                            </p>
                            <p class="text-xs text-gray-400">Clique para visualizar ou baixar o arquivo oficial</p>
                        </div>
                    </div>
                    <a href="<?= BASE_URL ?>/storage/licencas/<?= $licenca['documento_path'] ?>" target="_blank"
                       class="px-4 py-2 bg-white border border-gray-200 text-gray-700 text-sm font-semibold rounded-lg hover:bg-gray-50 transition flex items-center gap-2">
                        <i class='bx bx-download'></i> Abrir Documento
                    </a>
                    <!-- Botão para remover o documento -->
                    <form action="<?= BASE_URL ?>/licencasOperacao/removerDocumento/<?= $licenca['id'] ?>" method="POST" onsubmit="return confirm('Tem certeza que deseja remover este documento? Esta ação não pode ser desfeita.');">
                        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                        <button type="submit" class="px-4 py-2 bg-red-50 text-red-700 text-sm font-semibold rounded-lg hover:bg-red-100 transition flex items-center gap-2">
                            <i class='bx bx-trash'></i> Remover
                        </button>
                    </form>
                </div>
            <?php else: ?>
                <div class="text-center py-4 bg-gray-50 rounded-lg border border-dashed border-gray-300">
                    <p class="text-sm text-gray-500 italic">Nenhum documento digitalizado foi anexado a esta licença.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Status e Contexto -->
    <div class="space-y-6">
        <!-- Card de Responsáveis -->
        <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm">
            <h3 class="text-sm font-bold text-gray-800 mb-4 uppercase tracking-tight">Responsáveis</h3>
            <div class="space-y-4">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center text-indigo-600">
                        <i class='bx bx-user'></i>
                    </div>
                    <div>
                        <p class="text-[10px] text-gray-400 uppercase">Gestor</p>
                        <p class="text-sm font-semibold text-gray-700"><?= htmlspecialchars($licenca['gestor_responsavel'] ?? 'Não atribuído') ?></p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center text-gray-600">
                        <i class='bx bx-envelope'></i>
                    </div>
                    <div class="overflow-hidden">
                        <p class="text-[10px] text-gray-400 uppercase">E-mail Notificações</p>
                        <p class="text-xs font-medium text-gray-600 truncate"><?= htmlspecialchars($licenca['email_responsavel'] ?? '—') ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Situação e Conformidade -->
        <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm">
            <h3 class="text-sm font-bold text-gray-800 mb-4 uppercase tracking-tight">Conformidade</h3>
            
            <div class="mb-4">
                <?php
                $status = $licenca['status'] ?? 'Ativa';
                $badgeClass = match($status) {
                    'Ativa', 'Vigente' => 'bg-green-500 text-white',
                    'Vencendo', 'Pendente Renovação' => 'bg-orange-100 text-orange-800',
                    'Vencida' => 'bg-red-500 text-white',
                    default => 'bg-gray-100 text-gray-800'
                };
                ?>
                <span class="w-full inline-block text-center px-4 py-2 text-xs font-bold rounded-lg <?= $badgeClass ?>">
                    <?= htmlspecialchars($status) ?>
                </span>
            </div>

            <div class="space-y-2 border-t pt-4">
                <div class="flex justify-between text-xs">
                    <span class="text-gray-500">Auditoria Ativa:</span>
                    <span class="<?= ($licenca['auditoria_ativa'] ?? 0) ? 'text-green-600' : 'text-gray-400' ?> font-bold">
                        <?= ($licenca['auditoria_ativa'] ?? 0) ? 'SIM' : 'NÃO' ?>
                    </span>
                </div>
                <div class="flex justify-between text-xs">
                    <span class="text-gray-500">Requer Aprovação:</span>
                    <span class="<?= ($licenca['requer_aprovacao'] ?? 0) ? 'text-orange-600' : 'text-gray-400' ?> font-bold">
                        <?= ($licenca['requer_aprovacao'] ?? 0) ? 'SIM' : 'NÃO' ?>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>