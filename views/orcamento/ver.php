<?php
// views/ver.php
// $orc, $historico, $statusLabels
$sl = ($statusLabels ?? [])[$orc['status'] ?? 'Rascunho'] ?? ['label' => $orc['status'] ?? 'Indefinido', 'cor' => 'secondary'];
?>
<style>
    .proposta-view-container { max-width: 1200px; margin: 0 auto; }
    .card-modern { border: 1px solid #f3f4f6; border-radius: 1rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); background: #ffffff; margin-bottom: 1.5rem; overflow: hidden; }
    .card-modern-header { padding: 1.25rem 1.5rem; border-bottom: 1px solid #f3f4f6; font-weight: 700; color: #374151; display: flex; align-items: center; gap: 0.75rem; background-color: #f9fafb; }
    .badge-status-lg { padding: 0.5rem 1rem; border-radius: 9999px; font-weight: 700; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.025em; }
    .table-clean thead th { background-color: #f9fafb; color: #6b7280; font-weight: 600; text-transform: uppercase; font-size: 0.7rem; letter-spacing: 0.05em; padding: 0.75rem 1rem; border: none; }
    .table-clean tbody td { padding: 1rem; vertical-align: middle; border-bottom: 1px solid #f3f4f6; }
    .timeline-modern { position: relative; padding-left: 1.5rem; }
    .timeline-modern::before { content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 2px; background: #e5e7eb; }
    .timeline-item { position: relative; padding-bottom: 1.5rem; }
    .timeline-marker { position: absolute; left: -1.85rem; width: 0.75rem; height: 0.75rem; border-radius: 50%; border: 2px solid #fff; background: #3b82f6; top: 0.25rem; }
    .info-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; }
    .label-muted { font-size: 0.7rem; font-weight: 700; color: #9ca3af; text-transform: uppercase; margin-bottom: 0.25rem; display: block; }
    .value-highlight { font-weight: 600; color: #111827; }
    .btn-action { border-radius: 0.75rem; font-weight: 600; padding: 0.5rem 1rem; transition: all 0.2s; display: inline-flex; align-items: center; gap: 0.5rem; }
    .btn-back {
        border-radius: 0.75rem;
        font-weight: 700;
        padding: 0.2rem 0.6rem;
        color: #ffffff;
        background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
        border: 1px solid #0369a1;
        box-shadow: 0 4px 14px -6px rgba(2, 132, 199, 0.8);
        transition: transform 0.2s ease, filter 0.2s ease, background 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
    }
    .btn-back:hover {
        transform: translateY(-1px) scale(1.02);
        filter: brightness(1.08);
        background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
        color: #f8fafc;
        text-decoration: none;
    }
    .btn-back:active {
        transform: translateY(0) scale(0.99);
    }
    .total-card { background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); color: #fff; }
    .total-card .label-muted { color: #94a3b8; }
    .total-card .value-highlight { color: #fff; font-size: 1.5rem; }

    /* Dark Mode Overrides */
    .dark .card-modern { background: #1f2937; border-color: #374151; }
    .dark .card-modern-header { background-color: #111827; border-bottom-color: #374151; color: #f9fafb; }
    .dark .table-clean thead th { background-color: #111827; color: #9ca3af; }
    .dark .table-clean tbody td { border-bottom-color: #374151; color: #d1d5db; }
    .dark .value-highlight { color: #f3f4f6; }
    .dark .timeline-modern::before { background: #374151; }
    .dark .timeline-item .text-gray-700 { color: #d1d5db; }
    @media print { .no-print { display: none !important; } .proposta-view-container { max-width: 100%; padding: 0; } }
</style>

<div class="proposta-view-container py-4 px-3">

    <!-- Cabeçalho Profissional -->
    <div class="mb-5">
        <div class="flex justify-between items-center border-b dark:border-gray-700 pb-4">
            <div>
                <nav class="mb-2" aria-label="breadcrumb">
                    <ol class="p-0 m-0 mb-1 text-[10px] uppercase font-bold tracking-widest text-gray-400 list-none flex items-center gap-2">
                        <li><a href="<?= BASE_URL ?>/orcamento" class="text-gray-400 hover:text-sky-600 transition">Comercial</a></li>
                        <li class="opacity-50">/</li>
                        <li><a href="<?= BASE_URL ?>/orcamento/index" class="text-gray-400 hover:text-sky-600 transition">Propostas</a></li>
                        <li class="opacity-50">/</li>
                        <li class="text-sky-600">Visualização</li>
                    </ol>
                </nav>
                <h1 class="text-3xl mb-1 font-black text-gray-900 dark:text-white tracking-tight">
                    Proposta <span class="text-sky-600 bg-sky-50 dark:bg-sky-900/30 px-3 py-1 rounded-xl border border-sky-100 dark:border-sky-800 ml-1">#<?= htmlspecialchars($orc['numero']) ?></span>
                </h1>
                <div class="flex items-center gap-3 text-xs text-gray-500 font-medium">
                    <span><i class="far fa-calendar-alt me-1"></i> Emitida em <?= date('d/m/Y', strtotime($orc['criado_em'])) ?></span>
                    <span class="text-gray-300">|</span>
                    <span><i class="far fa-user me-1"></i> Por <?= htmlspecialchars($orc['responsavel_nome'] ?? 'Gestor') ?></span>
                </div>
            </div>
            <div class="d-flex gap-2 no-print mt-2">
                <span class="badge bg-<?= $sl['cor'] ?>-100 text-<?= $sl['cor'] ?>-700 badge-status-lg align-self-center me-2">
                    <i class="fas fa-circle text-[8px] me-1"></i> <?= $sl['label'] ?>
                </span>
                <a href="<?= BASE_URL ?>/orcamento/index" class="btn-back">
                    <i class="fas fa-arrow-left me-1"></i> Voltar
                </a>
                <a href="<?= BASE_URL ?>/orcamento/pdf/<?= $orc['id'] ?>" target="_blank" class="btn btn-danger btn-sm px-3 font-bold">
                    <i class="fas fa-file-pdf me-1"></i> PDF
                </a>
            </div>
        </div>
    </div>

    <!-- Barra de Ações Rápidas -->
    <div class="card-modern mb-4 no-print">
        <div class="card-body p-3 d-flex flex-wrap align-items-center gap-2">
            <?php if (in_array($orc['status'], ['Rascunho', 'Rejeitada'])): ?>
                <a href="<?= BASE_URL ?>/orcamento/editar/<?= $orc['id'] ?>" class="btn btn-light dark:bg-gray-700 dark:text-white dark:border-gray-600 btn-sm btn-action border">
                    <i class="fas fa-edit"></i> Editar
                </a>
            <?php endif; ?>

            <?php if ($orc['status'] === 'Rascunho'): ?>
                <button type="button" onclick="updateProposalStatus(<?= $orc['id'] ?>, 'Enviada')" class="btn btn-light dark:bg-gray-700 dark:text-white dark:border-gray-600 btn-sm btn-action border">
                    <i class="fas fa-paper-plane"></i> Marcar como Enviada
                </button>
            <?php endif; ?>

            <?php if ($orc['status'] === 'Enviada'): ?>
                <button type="button" onclick="updateProposalStatus(<?= $orc['id'] ?>, 'Aprovada')" class="btn btn-light dark:bg-gray-700 dark:text-white dark:border-gray-600 btn-sm btn-action border">
                    <i class="fas fa-check-circle"></i> Aprovar
                </button>
                <button type="button" onclick="updateProposalStatus(<?= $orc['id'] ?>, 'Rejeitada')" class="btn btn-light dark:bg-gray-700 dark:text-white dark:border-gray-600 btn-sm btn-action border">
                    <i class="fas fa-times-circle"></i> Reprovar
                </button>
            <?php endif; ?>

            <a href="<?= BASE_URL ?>/orcamento/clonar/<?= $orc['id'] ?>" class="btn btn-light btn-sm btn-action border" title="Duplicar">
                <i class="fas fa-copy text-gray-500"></i> <span>Duplicar</span>
            </a>
            <button onclick="window.print()" class="btn btn-light btn-sm btn-action border">
                <i class="fas fa-print text-gray-500"></i> <span>Imprimir</span>
            </button>

            <?php if (in_array($orc['status'], ['Rascunho', 'Rejeitada']) && !empty($orc['id']) && $orc['id'] > 0): ?>
                <button type="button" onclick="excluirProposta('<?= htmlspecialchars($orc['id']) ?>', this)" class="btn btn-light btn-sm btn-action border text-rose-600">
                    <i class="fas fa-trash-alt"></i> Excluir
                </button>
            <?php endif; ?>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- CARD CLIENTE -->
            <div class="card-modern">
                <div class="card-modern-header">
                    <i class="fas fa-user-tie text-sky-500"></i> Informações do Cliente
                </div>
                <div class="card-body p-4">
                    <div class="info-grid">
                        <div>
                            <span class="label-muted">Razão Social / Nome</span>
                            <div class="value-highlight"><?= htmlspecialchars($orc['razao_social']) ?></div>
                            <?php if ($orc['nome_fantasia']): ?>
                                <div class="text-sm text-gray-500"><?= htmlspecialchars($orc['nome_fantasia']) ?></div>
                            <?php endif; ?>
                        </div>
                        <div>
                            <span class="label-muted">Documento</span>
                            <div class="value-highlight"><?= htmlspecialchars($orc['cnpj_cpf'] ?? '—') ?></div>
                        </div>
                        <div>
                            <span class="label-muted">Contato Principal</span>
                            <div class="value-highlight"><?= htmlspecialchars($orc['cliente_contato'] ?? '—') ?></div>
                            <div class="text-xs text-gray-500"><?= htmlspecialchars($orc['cliente_email'] ?? '—') ?></div>
                        </div>
                        <div>
                            <span class="label-muted">Telefone</span>
                            <div class="value-highlight"><?= htmlspecialchars($orc['telefone'] ?? '—') ?></div>
                        </div>
                    </div>
                    <div class="mt-4 pt-3 border-top">
                        <span class="label-muted">Endereço Completo</span>
                        <div class="text-sm text-gray-700 font-medium"><?= htmlspecialchars($orc['endereco'] ?? 'Endereço não informado') ?></div>
                    </div>
                    <?php if (!empty($orc['descricao_geral'])): ?>
                        <div class="mt-4 pt-3 border-top">
                            <span class="label-muted">Escopo / Objeto</span>
                            <div class="text-sm text-gray-600 lh-base"><?= nl2br(htmlspecialchars($orc['descricao_geral'])) ?></div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- CARD RESPONSÁVEL INTERNO -->
            <div class="card-modern">
                <div class="card-modern-header">
                    <i class="fas fa-user-cog text-sky-500"></i> Responsável Interno
                </div>
                <div class="card-body p-4">
                    <div class="info-grid">
                        <div>
                            <span class="label-muted">Nome</span>
                            <div class="value-highlight"><?= htmlspecialchars($orc['responsavel_nome'] ?? 'Não informado') ?></div>
                        </div>
                        <div>
                            <span class="label-muted">ID Responsável</span>
                            <div class="value-highlight">#<?= htmlspecialchars($orc['responsavel_interno_id'] ?? '—') ?></div>
                        </div>
                    </div>
                    <?php if (!empty($orc['responsavel_nome'])): ?>
                        <div class="mt-4 pt-3 border-top">
                            <span class="label-muted">Observação</span>
                            <div class="text-sm text-gray-600">Este profissional é responsável pelo acompanhamento e execução desta proposta.</div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- CARD ITENS -->
            <div class="card-modern">
                <div class="card-modern-header">
                    <i class="fas fa-list-ul text-sky-500"></i> Detalhamento dos Itens
                </div>
                <?php if (!empty($orc['itens']) && is_array($orc['itens'])): ?>
                    <div class="table-responsive">
                        <table class="table table-clean mb-0">
                            <thead>
                                <tr>
                                    <th style="width:50px">#</th>
                                    <th>Descrição do Serviço / Produto</th>
                                    <th class="text-center">Qtd.</th>
                                    <th class="text-end">Vlr. Unitário</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orc['itens'] as $idx => $item): ?>
                                <tr>
                                    <td class="text-gray-400 font-mono"><?= str_pad($idx + 1, 2, '0', STR_PAD_LEFT) ?></td>
                                    <td>
                                        <div class="fw-bold text-gray-800"><?= htmlspecialchars($item['descricao'] ?? $item['nome'] ?? '') ?></div>
                                        <?php if (!empty($item['detalhes']) || !empty($item['descricao'])): ?>
                                            <div class="text-xs text-gray-500 italic"><?= htmlspecialchars($item['detalhes'] ?? $item['descricao'] ?? '') ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center text-gray-600"><?= number_format((float)($item['quantidade'] ?? 0), 2, ',', '.') ?> <small><?= htmlspecialchars($item['unidade'] ?? 'un') ?></small></td>
                                    <td class="text-end text-gray-600"><?= \App\Helpers\ReportHelper::formatCurrency($item['valor_unit'] ?? $item['valor_unitario'] ?? 0) ?></td>
                                    <td class="text-end fw-bold text-gray-800"><?= \App\Helpers\ReportHelper::formatCurrency($item['total_item'] ?? (($item['quantidade'] ?? 0) * ($item['valor_unit'] ?? $item['valor_unitario'] ?? 0))) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="card-body bg-gray-50 p-4 border-top">
                        <div class="row justify-content-end">
                            <div class="col-md-5">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-gray-500 text-sm">Subtotal:</span>
                                    <span class="fw-bold"><?= \App\Helpers\ReportHelper::formatCurrency($orc['subtotal'] ?? ($orc['total_servicos'] + $orc['total_materiais'])) ?></span>
                                </div>
                                <?php if ((float)($orc['desconto_valor'] ?? 0) > 0): ?>
                                <div class="d-flex justify-content-between mb-2 text-danger">
                                    <span class="text-sm">Desconto Global:</span>
                                    <span class="fw-bold">- <?= \App\Helpers\ReportHelper::formatCurrency($orc['desconto_valor']) ?></span>
                                </div>
                                <?php endif; ?>
                                <?php if ((float)($orc['impostos_valor'] ?? 0) > 0): ?>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-sm">Impostos:</span>
                                    <span class="fw-bold">+ <?= \App\Helpers\ReportHelper::formatCurrency($orc['impostos_valor']) ?></span>
                                </div>
                                <?php endif; ?>
                                <div class="d-flex justify-content-between pt-2 border-top">
                                    <span class="h5 mb-0 font-black">VALOR TOTAL:</span>
                                    <span class="h5 mb-0 font-black text-sky-600"><?= \App\Helpers\ReportHelper::formatCurrency($orc['total'] ?? $orc['total_final']) ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="card-body p-4 text-center">
                        <div class="text-gray-500">
                            <i class="fas fa-info-circle fa-2x mb-3"></i>
                            <p>Nenhum item foi adicionado a esta proposta ainda.</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- CARD CRONOGRAMA -->
            <?php 
            $crono = !empty($orc['cronograma_data']) ? json_decode($orc['cronograma_data'], true) : null;
            if ($crono && !empty($crono['activities'])): 
            ?>
            <div class="card-modern">
                <div class="card-modern-header">
                    <i class="fas fa-calendar-alt text-sky-500"></i> Cronograma de Execução
                </div>
                <div class="card-body p-0 overflow-x-auto">
                    <table class="table table-sm table-bordered mb-0" style="font-size: 0.7rem; min-width: 600px;">
                        <thead class="bg-light">
                            <tr>
                                <th style="width: 180px;">Atividade</th>
                                <?php 
                                $n = (int)$crono['totalPeriods'];
                                for($i=1; $i<=$n; $i++) echo "<th class='text-center p-1'>$i</th>"; 
                                ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($crono['activities'] as $ri => $name): ?>
                            <tr>
                                <td class="fw-bold bg-light"><?= htmlspecialchars($name) ?></td>
                                <?php for($ci=0; $ci<$n; $ci++): 
                                    $val = $crono['state'][$ri.'_'.$ci] ?? 0;
                                    $bg = $val == 1 ? 'bg-primary' : ($val == 2 ? 'bg-success' : '');
                                ?>
                                    <td class="<?= $bg ?> p-0" style="height: 20px; border: 1px solid #eee;"></td>
                                <?php endfor; ?>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="card-footer bg-white p-2">
                    <div class="d-flex gap-3 text-[9px] uppercase font-bold text-gray-500">
                        <div class="d-flex align-items-center gap-1">
                            <span class="d-inline-block bg-primary" style="width:10px; height:10px; border-radius:2px;"></span> Escritório
                        </div>
                        <div class="d-flex align-items-center gap-1">
                            <span class="d-inline-block bg-success" style="width:10px; height:10px; border-radius:2px;"></span> Campo
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- OBSERVAÇÕES -->
            <?php if ($orc['observacoes']): ?>
            <div class="card-modern">
                <div class="card-modern-header">
                    <i class="fas fa-comment-alt text-sky-500"></i> Observações
                </div>
                <div class="card-body p-4">
                    <div class="bg-yellow-50 p-3 rounded-lg border border-yellow-100 text-sm text-yellow-800 lh-base">
                        <?= nl2br(htmlspecialchars(html_entity_decode($orc['observacoes']))) ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <div class="col-lg-4">
            <!-- CARD RESUMO RÁPIDO -->
            <div class="card-modern total-card">
                <div class="card-body p-4">
                    <span class="label-muted">Valor Final da Proposta</span>
                    <div class="value-highlight"><?= \App\Helpers\ReportHelper::formatCurrency($orc['total'] ?? $orc['total_final']) ?></div>
                    <hr class="my-3 opacity-20">
                    <div class="d-flex justify-content-between text-xs mb-2">
                        <span>Vencimento:</span>
                        <span class="fw-bold"><?= $orc['data_validade'] ? date('d/m/Y', strtotime($orc['data_validade'])) : '—' ?></span>
                    </div>
                    <div class="d-flex justify-content-between text-xs mb-2">
                        <span>Status:</span>
                        <span class="badge bg-<?= $sl['cor'] ?>-500"><?= $sl['label'] ?></span>
                    </div>
                    <div class="d-flex justify-content-between text-xs">
                        <span>Data Criação:</span>
                        <span class="fw-bold"><?= date('d/m/Y', strtotime($orc['criado_em'] ?? $orc['created_at'] ?? 'now')) ?></span>
                    </div>
                </div>
            </div>

            <!-- CARD PROJETO VINCULADO -->
            <?php if (!empty($orc['projeto_nome'])): ?>
            <div class="card-modern">
                <div class="card-modern-header">
                    <i class="fas fa-project-diagram text-sky-500"></i> Projeto Vinculado
                </div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <span class="label-muted">Nome do Projeto</span>
                        <div class="value-highlight text-sm">
                            <a href="<?= BASE_URL ?>/projetos/detalhe/<?= $orc['projeto_id'] ?>/resumo" class="text-sky-600 hover:text-sky-800">
                                <?= htmlspecialchars($orc['projeto_nome']) ?>
                            </a>
                        </div>
                    </div>
                    <div class="text-xs text-gray-500">
                        <i class="fas fa-info-circle mr-1"></i>
                        Esta proposta está vinculada ao projeto acima.
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- CARD CONTRATO VINCULADO -->
            <?php if (!empty($orc['contrato_id'])): ?>
            <div class="card-modern">
                <div class="card-modern-header">
                    <i class="fas fa-file-contract text-emerald-500"></i> Contrato Vinculado
                </div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <span class="label-muted">Identificação</span>
                        <div class="value-highlight text-sm">
                            <a href="<?= BASE_URL ?>/contratos/detalhe/<?= $orc['contrato_id'] ?>" class="text-emerald-600 hover:text-emerald-800 font-bold">
                                <?= htmlspecialchars($orc['contrato_numero'] ?? 'Contrato #' . $orc['contrato_id']) ?>
                            </a>
                        </div>
                    </div>
                    <div class="text-xs text-gray-500">
                        <i class="fas fa-info-circle mr-1"></i> Esta proposta faz parte do escopo do contrato acima.
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- CARD CONDIÇÕES -->
            <div class="card-modern">
                <div class="card-modern-header"><i class="fas fa-credit-card text-sky-500"></i> Condições Comerciais</div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <span class="label-muted">Pagamento</span>
                        <div class="text-sm font-bold text-gray-700"><?= htmlspecialchars($orc['condicao_pagamento'] ?? $orc['forma_pagamento'] ?? '—') ?></div>
                    </div>
                    <div class="mb-3">
                        <span class="label-muted">Prazo de Entrega / Início</span>
                        <div class="text-sm font-bold text-gray-700"><?= htmlspecialchars($orc['prazo_entrega'] ?? $orc['prazo_execucao'] ?? '—') ?></div>
                    </div>
                    <?php if (!empty($orc['garantias'])): ?>
                    <div>
                        <span class="label-muted">Garantias</span>
                        <div class="text-sm font-bold text-gray-700"><?= nl2br(htmlspecialchars(html_entity_decode($orc['garantias']))) ?></div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- ASSINATURA / APROVAÇÃO -->
            <?php if (strtolower($orc['status']) === 'aprovada'): ?>
            <div class="card-modern border-success border-top border-4">
                <div class="card-modern-header text-success"><i class="fas fa-check-double"></i> Proposta Aprovada</div>
                <div class="card-body p-3 text-sm">
                    <div class="mb-2"><span class="label-muted">Aprovado Por:</span> <div class="fw-bold"><?= htmlspecialchars($orc['aprovado_por'] ?? 'Interno') ?></div></div>
                    <div><span class="label-muted">Data da Aprovação:</span> <div class="fw-bold"><?= (!empty($orc['aprovado_em']) && $orc['aprovado_em'] !== '0000-00-00 00:00:00' && strtotime($orc['aprovado_em']) !== false) ? date('d/m/Y H:i', strtotime($orc['aprovado_em'])) : '—' ?></div></div>
                </div>
            </div>
            <?php endif; ?>

            <!-- HISTÓRICO -->
            <?php if (!empty($historico)): ?>
            <div class="card-modern">
                <div class="card-modern-header"><i class="fas fa-history text-sky-500"></i> Histórico de Eventos</div>
                <div class="card-body p-4">
                    <div class="timeline-modern">
                        <?php foreach ($historico as $h): ?>
                        <div class="timeline-item">
                            <div class="timeline-marker"></div>
                            <div class="text-xs text-gray-400 font-bold mb-1"><?= date('d/m/Y H:i', strtotime($h['data_revisao'] ?? $h['data_evento'] ?? 'now')) ?></div>
                            <div class="text-sm text-gray-700">
                                <span class="fw-bold"><?= htmlspecialchars($h['status_para'] ?? 'Alteração') ?></span>
                                <?php if (!empty($h['usuario_nome'])): ?>
                                    <span class="text-xs">por <?= htmlspecialchars($h['usuario_nome']) ?></span>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($h['motivo_alteracao'] ?? $h['observacao'])): ?>
                                <div class="text-xs text-gray-500 mt-1 italic">"<?= htmlspecialchars($h['motivo_alteracao'] ?? $h['observacao']) ?>"</div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
                    <?php endif; ?>
        </div>
    </div>
</div>

<script>
async function updateProposalStatus(id, newStatus) {
    const acao = newStatus === 'Aprovada' ? 'aprovar' : (newStatus === 'Rejeitada' ? 'rejeitar' : 'enviar');
    if (!confirm(`Deseja ${acao} esta proposta?`)) return;

    let motivo = '';
    if (newStatus === 'Rejeitada') {
        motivo = prompt('Informe o motivo da rejeição (opcional):');
    }

    const formData = new FormData();
    formData.append('status', newStatus);
    formData.append('motivo', motivo);
    formData.append('csrf_token', '<?= $csrf_token ?? '' ?>');

    try {
        const response = await fetch(`<?= BASE_URL ?>/orcamento/updateStatusAjax/${id}`, {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        if (result.success) {
            window.location.reload();
        } else {
            alert(result.message || 'Erro ao atualizar status.');
        }
    } catch (e) {
        console.error(e);
        alert('Erro na comunicação com o servidor.');
    }
}

/**
 * Exclui a proposta visualizada
 */
function normalizeProposalId(value) {
    if (value === null || value === undefined) return null;
    const trimmed = String(value).trim();
    if (trimmed === '') return null;

    const parsed = Number(trimmed);
    if (!Number.isInteger(parsed) || parsed <= 0) return null;

    return parsed;
}

function excluirProposta(id, element = null) {
    let resolvedId = normalizeProposalId(id);
    
    // Se o primeiro parâmetro é um objeto (elemento), tenta extrair o ID
    if (!resolvedId && element && typeof element === 'object') {
        const row = element.closest('tr');
        const rowId = row?.dataset?.id;
        resolvedId = normalizeProposalId(rowId);
        
        // Se o data-id é 0 ou inválido, tenta extrair dos links
        if (!resolvedId && rowId === '0') {
            const editLink = row?.querySelector('a[href*="/editar/"]');
            if (editLink) {
                const href = editLink.getAttribute('href');
                const match = href.match(/\/editar\/(\d+)/);
                if (match && match[1]) {
                    resolvedId = normalizeProposalId(match[1]);
                }
            }
        }
        
        if (!resolvedId) {
            console.warn('Falha ao extrair ID do elemento', { element, row, rowId });
        }
    }

    // Fallback: se ainda não tem, procura o ID no elemento passado
    if (!resolvedId && id && typeof id === 'object') {
        const fallback = id.dataset?.id || id.id || id.value || id.getAttribute?.('data-id');
        resolvedId = normalizeProposalId(fallback);
    }

    if (!resolvedId && element) {
        const row = element.closest('tr');
        const rowId = row?.dataset?.id;
        resolvedId = normalizeProposalId(rowId);
        
        // Se ainda vazio, tenta extrair dos links
        if (!resolvedId && rowId === '0') {
            const editLink = row?.querySelector('a[href*="/editar/"]');
            if (editLink) {
                const href = editLink.getAttribute('href');
                const match = href.match(/\/editar\/(\d+)/);
                if (match && match[1]) {
                    resolvedId = normalizeProposalId(match[1]);
                }
            }
        }
    }

    if (!resolvedId || resolvedId <= 0 || Number.isNaN(resolvedId)) {
        // Última tentativa: procura qualquer link com ID na página
        let searchRow = element?.closest('tr');
        if (!searchRow) {
            const selectedRow = document.querySelector('tr[data-id].bg-sky-50, tr[data-id].selected');
            searchRow = selectedRow || document.querySelector('tr[data-id]');
        }
        
        if (searchRow) {
            // Procura ID em links (ver, editar, pdf, etc)
            const allLinks = searchRow.querySelectorAll('a[href]');
            for (const link of allLinks) {
                const href = link.getAttribute('href');
                const match = href.match(/\/(ver|editar|pdf)\/(\d+)/);
                if (match && match[2]) {
                    const extractedId = normalizeProposalId(match[2]);
                    if (extractedId) {
                        resolvedId = extractedId;
                        break;
                    }
                }
            }
        }
        
        // Se ainda não tem, tenta o rowId
        if (!resolvedId && searchRow?.dataset?.id) {
            resolvedId = Number(searchRow.dataset.id);
        }
    }

    if (!resolvedId || resolvedId <= 0 || Number.isNaN(resolvedId)) {
        alert('ID de proposta inválido. Não foi possível excluir.');
        console.warn('excluirProposta: ID inválido', { id, resolvedId });
        return;
    }

    if (!confirm('Tem certeza que deseja excluir esta proposta permanentemente? Esta ação não pode ser desfeita.')) return;
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `<?= BASE_URL ?>/orcamento/excluir/${resolvedId}`;

    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = 'csrf_token';
    csrfInput.value = '<?= $csrf_token ?? '' ?>';

    const idInput = document.createElement('input');
    idInput.type = 'hidden';
    idInput.name = 'id';
    idInput.value = resolvedId;

    form.appendChild(csrfInput);
    form.appendChild(idInput);
    document.body.appendChild(form);
    form.submit();
}
</script>
