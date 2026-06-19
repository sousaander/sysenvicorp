<?php
/**
 * Formulário de Cadastro / Edição de Projeto
 * Visual redesenhado — paleta teal/verde, layout em seções, status visual, barra de progresso de prazo.
 */

// Mapeamento de status para classe CSS
$statusMap = [
    'Planejado'          => 'planejado',
    'Em Execução'        => 'execucao',
    'Aguardando Cliente' => 'aguardando',
    'Concluído'          => 'concluido',
    'Atrasado'           => 'atrasado',
    'Cancelado'          => 'cancelado',
];
$statusAtual = $projeto['status'] ?? 'Planejado';
$statusKey   = $statusMap[$statusAtual] ?? 'planejado';

$urlVoltar = (isset($projeto['status']) && $projeto['status'] === 'Concluído')
    ? BASE_URL . '/projetos/arquivados'
    : BASE_URL . '/projetos';

$isEdit  = isset($projeto['id']);
?>

<style>
    /* Estilos personalizados para botões de status para garantir uma aparência consistente */
    .pf-status-btn {
        display: flex; flex-direction: column; align-items: center; gap: 4px;
        padding: 8px 6px;
        font-size: 0.65rem; /* 11px */ font-weight: 500;
        line-height: 1.2;
    }
    .pf-status-btn .sdot {
        width: 7px; height: 7px;
        border-radius: 50%;
        background: currentColor;
        opacity: 0.55;
        flex-shrink: 0;
    }

    /* Active states (Semantic Colors based on theme) */
    .pf-status-btn.active[data-s="Planejado"]          { border-color: #378ADD; color: #185FA5; background: #E6F1FB; } /* Planejado */
    .pf-status-btn.active[data-s="Em Execução"]        { border-color: #1D9E75; color: #0F6E56; background: #E1F5EE; } /* Em Execução */
    .pf-status-btn.active[data-s="Aguardando Cliente"] { border-color: #BA7517; color: #854F0B; background: #FAEEDA; } /* Aguardando Cliente */
    .pf-status-btn.active[data-s="Concluído"]          { border-color: #639922; color: #3B6D11; background: #EAF3DE; } /* Concluído */
    .pf-status-btn.active[data-s="Atrasado"]           { border-color: #E24B4A; color: #A32D2D; background: #FCEBEB; } /* Atrasado */
    .pf-status-btn.active[data-s="Cancelado"]          { border-color: #888780; color: #5F5E5A; background: #F1EFE8; } /* Cancelado */

    /* Sobrescritas de Tema Escuro para botões de status ativos */
    .dark-theme .pf-status-btn.active[data-s="Planejado"]          { border-color: #3b82f6; color: #93c5fd; background: rgba(59, 130, 246, 0.2); } /* Planejado */
    .dark-theme .pf-status-btn.active[data-s="Em Execução"]        { border-color: #10b981; color: #6ee7b7; background: rgba(16, 185, 129, 0.2); } /* Em Execução */
    .dark-theme .pf-status-btn.active[data-s="Aguardando Cliente"] { border-color: #f59e0b; color: #fcd34d; background: rgba(245, 158, 11, 0.2); } /* Aguardando Cliente */
    .dark-theme .pf-status-btn.active[data-s="Concluído"]          { border-color: #84cc16; color: #bef264; background: rgba(132, 204, 22, 0.2); } /* Concluído */
    .dark-theme .pf-status-btn.active[data-s="Atrasado"]           { border-color: #ef4444; color: #fca5a5; background: rgba(239, 68, 68, 0.2); } /* Atrasado */
    .dark-theme .pf-status-btn.active[data-s="Cancelado"]          { border-color: #94a3b8; color: #cbd5e1; background: rgba(148, 163, 184, 0.2); } /* Cancelado */

    /* Ajustes responsivos para o grupo de status */
    @media (max-width: 768px) { /* md breakpoint */
        .pf-status-group { grid-template-columns: repeat(3, 1fr) !important; }
    }
    @media (max-width: 640px) { /* sm breakpoint */
        .pf-status-group { grid-template-columns: repeat(2, 1fr) !important; }
    }
</style>

<div class="max-w-4xl mx-auto p-6 bg-white dark:bg-gray-800 rounded-lg shadow-md">

    <!-- ── Header ── -->
    <div class="flex justify-between items-start pb-4 mb-4 border-b border-gray-200 dark:border-gray-700">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-lg bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center flex-shrink-0">
                <svg viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 12h3m-3 4h3M9 12h.01M9 16h.01"/>
                </svg>
            </div>
            <div>
                <h2 class="text-xl font-bold text-gray-800 dark:text-white">
                    <?php echo $isEdit ? 'Editar Projeto' : 'Novo Projeto'; ?>
                </h2>
                <p class="text-gray-600 dark:text-gray-400">
                    <?php echo $isEdit ? 'Atualize as informações do projeto abaixo.' : 'Preencha as informações para cadastrar o projeto.'; ?>
                </p>
            </div>
        </div>
        <span class="text-xs font-medium px-2.5 py-1 rounded-md bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-300">
            <?php echo !empty($projeto['numero_projeto']) ? htmlspecialchars($projeto['numero_projeto']) : ($isEdit ? 'ID #' . $projeto['id'] : 'NOVO'); ?>
        </span>
    </div>

    <!-- ── Formulário ── -->
    <form action="<?php echo BASE_URL; ?>/projetos/salvar" method="POST" id="projetoForm">
        <input type="hidden" name="id"     value="<?php echo $projeto['id'] ?? ''; ?>">
        <input type="hidden" name="status" id="statusHidden" value="<?php echo htmlspecialchars($statusAtual); ?>">

        <!-- ── Seção 1: Informações Principais ── -->
        <div class="py-6 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center gap-2 mb-4">
                <div class="w-1.5 h-1.5 rounded-full bg-emerald-500"></div>
                <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Informações principais</span>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                <div class="flex flex-col gap-1">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300" for="numero_projeto">Código do projeto <span class="text-xs text-gray-400 font-normal">(Automático)</span></label>
                    <input class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm p-2.5 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all font-mono" type="text" id="numero_projeto" name="numero_projeto" 
                           value="<?php echo htmlspecialchars($projeto['numero_projeto'] ?? ''); ?>" 
                           placeholder="PRJ-YYYY-000">
                </div>

                <div class="flex flex-col gap-1">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300" for="nome">Nome do projeto <span class="text-red-500">*</span></label>
                    <input class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm p-2.5 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all" type="text" id="nome" name="nome" required
                           value="<?php echo htmlspecialchars($projeto['nome'] ?? ''); ?>"
                           placeholder="Ex: Estudo de Impacto Ambiental (EIA)">
                </div>

                <div class="flex flex-col gap-1">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300" for="cliente_id">Cliente <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <select class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm p-2.5 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all" id="cliente_id" name="cliente_id" required>
                            <option value="">Selecione um cliente</option>
                            <?php if (!empty($clientes)): ?>
                                <?php foreach ($clientes as $cliente): ?>
                                    <?php $sel = (isset($projeto['cliente_id']) && $projeto['cliente_id'] == $cliente['id']) ? 'selected' : ''; ?>
                                    <option value="<?php echo $cliente['id']; ?>" <?php echo $sel; ?>>
                                        <?php echo htmlspecialchars($cliente['nome']); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700 dark:text-gray-300">
                            <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col gap-1">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300" for="tipo_servico">Tipo de serviço</label>
                    <input class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm p-2.5 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all" type="text" id="tipo_servico" name="tipo_servico"
                           value="<?php echo htmlspecialchars($projeto['tipo_servico'] ?? ''); ?>"
                           placeholder="Ex: Licenciamento Ambiental">
                </div>

                <div class="flex flex-col gap-1">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300" for="empreendimento">Empreendimento</label>
                    <input class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm p-2.5 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all" type="text" id="empreendimento" name="empreendimento"
                           value="<?php echo htmlspecialchars($projeto['empreendimento'] ?? ''); ?>"
                           placeholder="Ex: Usina Hidrelétrica">
                </div>

                <div class="flex flex-col gap-1">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300" for="produto_entregue">Produto final</label>
                    <input class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm p-2.5 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all" type="text" id="produto_entregue" name="produto_entregue"
                           value="<?php echo htmlspecialchars($projeto['produto_entregue'] ?? ''); ?>"
                           placeholder="Ex: RIMA, EIA, PRAD">
                </div>

            </div>
        </div>

        <!-- ── Seção 2: Status ── -->
        <div class="py-6 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center gap-2 mb-4">
                <div class="w-1.5 h-1.5 rounded-full bg-emerald-500"></div>
                <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Status do projeto</span>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2" id="statusGroup">
                <?php
                $statusOptions = [
                    'Planejado'          => 'Planejado',
                    'Em Execução'        => 'Em execução',
                    'Aguardando Cliente' => 'Aguardando cliente',
                    'Concluído'          => 'Concluído',
                    'Atrasado'           => 'Atrasado',
                    'Cancelado'          => 'Cancelado',
                ];
                foreach ($statusOptions as $val => $label):
                    $active = ($statusAtual === $val) ? 'active' : '';
                    ?>
                <button type="button" 
                        class="pf-status-btn flex flex-col items-center gap-1 p-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-600 dark:text-gray-300 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700 transition-all <?php echo $active; ?>"
                        data-s="<?php echo $val; ?>" 
                        onclick="pfSetStatus(this)">
                    <div class="sdot"></div>
                    <?php echo $label; ?>
                </button>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- ── Seção 3: Prazos e Custos ── -->
        <div class="py-6 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center gap-2 mb-4">
                <div class="w-1.5 h-1.5 rounded-full bg-emerald-500"></div>
                <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Prazos e custos</span>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                <div class="flex flex-col gap-1">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300" for="data_inicial">Data de início <span class="text-red-500">*</span></label>
                    <input class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm p-2.5 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all" type="date" id="data_inicial" name="data_inicial" required
                           value="<?php echo htmlspecialchars($projeto['data_inicial'] ?? ''); ?>"
                           onchange="pfCalcProgress()">
                </div>

                <div class="flex flex-col gap-1">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300" for="data_fim_prevista">Previsão de término</label>
                    <input class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm p-2.5 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all" type="date" id="data_fim_prevista" name="data_fim_prevista"
                           value="<?php echo htmlspecialchars($projeto['data_fim_prevista'] ?? ''); ?>"
                           onchange="pfCalcProgress()">
                </div>

                <div class="flex flex-col gap-1">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300" for="orcamento">Custo operacional</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-400">R$</span>
                        <input class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm p-2.5 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all pl-10" type="text" id="orcamento" name="orcamento"
                               value="<?php echo htmlspecialchars($projeto['orcamento'] ?? ''); ?>"
                               placeholder="0,00"
                               oninput="pfMaskMoney(this)">
                    </div>
                </div>

            </div>
            <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400 mt-4 mb-1">
                <span>Progresso estimado do prazo</span>
                <span id="pfProgPct">—</span>
            </div>
            <div class="h-1 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                <div class="h-full rounded-full bg-emerald-500 transition-all duration-300" id="pfProgFill" style="width:0%"></div>
            </div>
        </div>

        <!-- ── Seção 4: Detalhes Técnicos ── -->
        <div class="py-6 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center gap-2 mb-4">
                <div class="w-1.5 h-1.5 rounded-full bg-emerald-500"></div>
                <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Detalhes técnicos</span>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">

                <div class="flex flex-col gap-1">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300" for="orcamento_id">ID orçamento</label>
                    <input class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm p-2.5 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all font-mono text-sm text-gray-500 dark:text-gray-400" type="text" id="orcamento_id" name="orcamento_id"
                           value="<?php echo htmlspecialchars($projeto['orcamento_id'] ?? ''); ?>"
                           placeholder="ORC-001">
                </div>

                <div class="flex flex-col gap-1">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300" for="area_id">ID área</label>
                    <input class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm p-2.5 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all font-mono text-sm text-gray-500 dark:text-gray-400" type="text" id="area_id" name="area_id"
                           value="<?php echo htmlspecialchars($projeto['area_id'] ?? ''); ?>"
                           placeholder="AREA-12">
                </div>

                <div class="flex flex-col gap-1">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300" for="tamanho_ha">Tamanho (ha)</label>
                    <input class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm p-2.5 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all" type="number" step="0.01" min="0"
                           id="tamanho_ha" name="tamanho_ha"
                           value="<?php echo htmlspecialchars($projeto['tamanho_ha'] ?? ''); ?>"
                           placeholder="0,00">
                </div>

            </div>

            <div class="mt-4">
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 block">Coordenadas geográficas</label>
                <div class="flex gap-2 items-end">
                    <div class="flex flex-col gap-1 flex-1">
                        <label class="text-sm font-normal text-gray-700 dark:text-gray-300" for="latitude">Latitude</label>
                        <input class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm p-2.5 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all" type="text" id="latitude" name="latitude"
                               value="<?php echo htmlspecialchars($projeto['latitude'] ?? ''); ?>"
                               placeholder="Ex: -3.4653 ou 03°27'55''S">
                    </div>
                    <div class="flex flex-col gap-1 flex-1">
                        <label class="text-sm font-normal text-gray-700 dark:text-gray-300" for="longitude">Longitude</label>
                        <input class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm p-2.5 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all" type="text" id="longitude" name="longitude"
                               value="<?php echo htmlspecialchars($projeto['longitude'] ?? ''); ?>"
                               placeholder="Ex: -62.2159 ou 62°12'57''W">
                    </div>
                    <a class="px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 flex items-center justify-center gap-2 flex-shrink-0 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-600 hover:text-emerald-600 dark:hover:text-emerald-400 transition-all text-sm font-medium text-gray-700 dark:text-gray-300 shadow-sm" href="https://www.google.com/maps" target="_blank">
                        <svg class="w-5 h-5 text-gray-400 dark:text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        Abrir Mapa
                    </a>
                </div>
            </div>
        </div>

        <!-- ── Seção 5: Equipe Responsável ── -->
        <div class="py-6 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center gap-2 mb-4">
                <div class="w-1.5 h-1.5 rounded-full bg-emerald-500"></div>
                <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Equipe responsável</span>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                <div class="flex flex-col gap-1">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300" for="responsavel">Responsável técnico <span class="text-red-500">*</span></label>
                    <input class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm p-2.5 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all" type="text" id="responsavel" name="responsavel" required
                           value="<?php echo htmlspecialchars($projeto['responsavel'] ?? ''); ?>"
                           placeholder="Nome do RT">
                </div>

                <div class="flex flex-col gap-1">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300" for="responsavel_elaboracao">Resp. elaboração</label>
                    <input class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm p-2.5 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all" type="text" id="responsavel_elaboracao" name="responsavel_elaboracao"
                           value="<?php echo htmlspecialchars($projeto['responsavel_elaboracao'] ?? ''); ?>"
                           placeholder="Nome">
                </div>

                <div class="flex flex-col gap-1">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300" for="responsavel_execucao">Resp. execução</label>
                    <input class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm p-2.5 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all" type="text" id="responsavel_execucao" name="responsavel_execucao"
                           value="<?php echo htmlspecialchars($projeto['responsavel_execucao'] ?? ''); ?>"
                           placeholder="Nome">
                </div>

            </div>
        </div>

        <!-- ── Seção 6: Observações ── -->
        <div class="py-6">
            <div class="flex items-center gap-2 mb-4">
                <div class="w-1.5 h-1.5 rounded-full bg-emerald-500"></div>
                <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Observações gerais</span>
            </div>
            <textarea class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm p-2.5 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all" id="observacoes" name="observacoes" rows="4"
                      placeholder="Detalhes adicionais, restrições, histórico ou instruções específicas do projeto..."><?php echo htmlspecialchars($projeto['observacoes'] ?? ''); ?></textarea>
        </div>

    </form>

    <!-- ── Footer de Ações ── -->
    <div class="flex flex-col sm:flex-row justify-between items-center pt-6 mt-4 border-t border-gray-200 dark:border-gray-700 gap-4">
        <span class="text-xs text-gray-500 dark:text-gray-400 self-start sm:self-center">Campos com <span class="text-red-500">*</span> são obrigatórios</span>
        <div class="flex w-full sm:w-auto gap-3">
            <a href="<?php echo $urlVoltar; ?>" class="flex-1 sm:flex-none text-center px-6 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 font-medium hover:bg-gray-100 dark:hover:bg-gray-700 transition-all">Cancelar</a>
            <button type="submit" form="projetoForm" class="flex-1 sm:flex-none flex items-center justify-center gap-2 px-6 py-2.5 bg-emerald-600 text-white font-medium rounded-lg hover:bg-emerald-700 shadow-md transition-all whitespace-nowrap">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
                <?php echo $isEdit ? 'Atualizar Projeto' : 'Salvar Projeto'; ?>
            </button>
        </div>
    </div>

</div>

<script>
(function () {

    /* --- Status: seleção visual --- */
    function pfSetStatus(btn) {
        document.querySelectorAll('.pf-status-btn').forEach(function(b) {
            b.classList.remove('active');
        });
        btn.classList.add('active');
        document.getElementById('statusHidden').value = btn.dataset.s;
    }
    window.pfSetStatus = pfSetStatus;

    /* --- Progresso do prazo --- */
    function pfCalcProgress() {
        var start = document.getElementById('data_inicial').value;
        var end   = document.getElementById('data_fim_prevista').value;
        var fill  = document.getElementById('pfProgFill');
        var label = document.getElementById('pfProgPct');
    
        if (!start || !end) {
            fill.style.width = '0%';
            label.textContent = '—';
            return;
        }
        var s = new Date(start), e = new Date(end), now = new Date();
        // Garante que 'now' seja definido para o início do dia para comparações consistentes
    
        if (e <= s) {
            fill.style.width = '0%';
            label.textContent = '—';
            return;
        }
    
        var totalDuration = e.getTime() - s.getTime();
        var elapsedDuration = now.getTime() - s.getTime();
    
        var pct = Math.min(100, Math.max(0, Math.round((elapsedDuration / totalDuration) * 100)));
        fill.style.width = pct + '%';
        // Cor dinâmica baseada no progresso
        if (pct > 90) {
            fill.style.backgroundColor = '#ef4444'; // Tailwind red-500
        } else if (pct > 70) {
            fill.style.backgroundColor = '#f59e0b'; // Tailwind orange-500
        } else {
            fill.style.backgroundColor = '#10b981'; // Tailwind emerald-500
        }
        label.textContent = pct + '%';
    }
    window.pfCalcProgress = pfCalcProgress;
    
    /* --- Máscara de moeda --- */
    function pfMaskMoney(el) {
        var v = el.value.replace(/\D/g, '');
        el.value = (parseInt(v || '0', 10) / 100)
            .toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }
    window.pfMaskMoney = pfMaskMoney;
    
    /* --- Inicialização --- */
    document.addEventListener('DOMContentLoaded', function () {
        pfCalcProgress();
    
        // Formata o valor do orçamento já existente
        var orcInput = document.getElementById('orcamento');
        if (orcInput && orcInput.value) {
            var raw = parseFloat(orcInput.value.replace(',', '.')) || 0;
            orcInput.value = raw.toLocaleString('pt-BR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        } 
    });

}());
</script>