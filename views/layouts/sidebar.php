<?php
$current_uri = $_SERVER['REQUEST_URI'];
function is_active($path) {
    global $current_uri;
    return (strpos($current_uri, $path) !== false || ($path === '/' && $current_uri === BASE_URL . '/')) ? 'active' : '';
}
?>

<!-- ═══════════════════════════════════════════════
     SIDEBAR — redesign v2
     Paleta: azul-marinho profundo (#0F172A base)
     Accent primário: #38BDF8 (sky-400)
     Design: glassmorphism sutil, ícones com glow colorido
     Tipografia: Inter via sistema
  ═══════════════════════════════════════════════ -->

<style>
/* ── Reset + Base ── */
#sidebar-header,
#sidebar-nav { font-family: 'Inter', 'Segoe UI', system-ui, sans-serif; }

/* ── Sidebar Header ── */
#sidebar-header {
    position: relative;
    height: 64px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    cursor: pointer;
    border-bottom: 1px solid rgba(255,255,255,0.07);
    background: rgba(255,255,255,0.02);
    transition: background 0.2s ease;
}
#sidebar-header:hover { background: rgba(255,255,255,0.06); }

#sidebar-header img {
    height: 36px;
    width: 36px;
    object-fit: contain;
    transition: transform 0.3s ease, filter 0.3s ease;
    filter: drop-shadow(0 0 6px rgba(56,189,248,0.25));
}
#sidebar-header:hover img {
    transform: scale(1.08);
    filter: drop-shadow(0 0 12px rgba(56,189,248,0.5));
}

.sidebar-collapse-btn {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    width: 26px;
    height: 26px;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.08);
    color: rgba(255,255,255,0.4);
    font-size: 14px;
    transition: all 0.2s ease;
}
#sidebar-header:hover .sidebar-collapse-btn {
    background: rgba(56,189,248,0.1);
    border-color: rgba(56,189,248,0.3);
    color: #38BDF8;
}

/* ── Navigation ── */
#sidebar-nav {
    flex: 1;
    padding: 12px 8px 16px;
    overflow-y: auto;
    overflow-x: hidden;
    scrollbar-width: thin;
    scrollbar-color: rgba(255,255,255,0.08) transparent;
}
#sidebar-nav::-webkit-scrollbar { width: 3px; }
#sidebar-nav::-webkit-scrollbar-track { background: transparent; }
#sidebar-nav::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 2px; }

/* ── Section Labels ── */
.sidebar-section-label {
    display: block;
    font-size: 9.5px;
    font-weight: 600;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: rgba(255,255,255,0.28);
    padding: 12px 10px 4px;
    margin-bottom: 2px;
}

/* ── Nav Items ── */
.sys-sidebar-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 7px 10px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 450;
    color: rgba(255,255,255,0.6);
    text-decoration: none;
    cursor: pointer;
    transition: all 0.18s ease;
    position: relative;
    margin-bottom: 1px;
    white-space: nowrap;
    overflow: hidden;
}
.sys-sidebar-item:hover {
    color: rgba(255,255,255,0.92);
    background: rgba(255,255,255,0.06);
}

/* Active state — barra lateral colorida */
.sys-sidebar-item.active {
    color: #fff !important;
    font-weight: 500;
}
.sys-sidebar-item.active::before {
    content: '';
    position: absolute;
    left: 0;
    top: 20%;
    height: 60%;
    width: 3px;
    border-radius: 0 2px 2px 0;
}

/* ── Icon Box ── */
.icon-box-3d {
    width: 30px;
    height: 30px;
    border-radius: 7px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 15px;
    transition: all 0.2s ease;
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.06);
}
.sys-sidebar-item:hover .icon-box-3d,
.sys-sidebar-item.active .icon-box-3d {
    transform: scale(1.05);
}

/* ── Submenu ── */
.has-submenu > ul {
    padding-left: 12px;
    margin-top: 2px;
    border-left: 1px solid rgba(255,255,255,0.06);
    margin-left: 15px;
}
.has-submenu > ul .sys-sidebar-item {
    font-size: 12.5px;
    padding: 6px 10px;
    color: rgba(255,255,255,0.5);
}
.has-submenu > ul .sys-sidebar-item:hover,
.has-submenu > ul .sys-sidebar-item.active { color: rgba(255,255,255,0.88); }

/* Submenu de terceiro nível */
.has-submenu > ul .has-submenu > ul {
    margin-left: 10px;
    border-left-color: rgba(255,255,255,0.04);
}

/* ── Cores por módulo ── */

/* Sky — Dashboard */
.item-sky.active, .item-sky:hover { background: rgba(56,189,248,0.1); }
.item-sky.active::before { background: #38BDF8; }
.item-sky .icon-box-3d { background: rgba(56,189,248,0.12); border-color: rgba(56,189,248,0.18); color: #38BDF8; }
.item-sky.active .icon-box-3d, .item-sky:hover .icon-box-3d { background: rgba(56,189,248,0.2); }

/* Purple — Jurídico */
.item-purple.active, .item-purple:hover { background: rgba(167,139,250,0.1); }
.item-purple.active::before { background: #A78BFA; }
.item-purple .icon-box-3d { background: rgba(167,139,250,0.12); border-color: rgba(167,139,250,0.18); color: #A78BFA; }
.item-purple.active .icon-box-3d, .item-purple:hover .icon-box-3d { background: rgba(167,139,250,0.2); }

/* Blue — Clientes */
.item-blue.active, .item-blue:hover { background: rgba(96,165,250,0.1); }
.item-blue.active::before { background: #60A5FA; }
.item-blue .icon-box-3d { background: rgba(96,165,250,0.12); border-color: rgba(96,165,250,0.18); color: #60A5FA; }
.item-blue.active .icon-box-3d, .item-blue:hover .icon-box-3d { background: rgba(96,165,250,0.2); }

/* Violet — Fornecedores */
.item-violet.active, .item-violet:hover { background: rgba(192,132,252,0.1); }
.item-violet.active::before { background: #C084FC; }
.item-violet .icon-box-3d { background: rgba(192,132,252,0.12); border-color: rgba(192,132,252,0.18); color: #C084FC; }
.item-violet.active .icon-box-3d, .item-violet:hover .icon-box-3d { background: rgba(192,132,252,0.2); }

/* Emerald — Comercial */
.item-emerald.active, .item-emerald:hover { background: rgba(52,211,153,0.1); }
.item-emerald.active::before { background: #34D399; }
.item-emerald .icon-box-3d { background: rgba(52,211,153,0.12); border-color: rgba(52,211,153,0.18); color: #34D399; }
.item-emerald.active .icon-box-3d, .item-emerald:hover .icon-box-3d { background: rgba(52,211,153,0.2); }

/* Amber — Financeiro */
.item-amber.active, .item-amber:hover { background: rgba(251,191,36,0.1); }
.item-amber.active::before { background: #FBBF24; }
.item-amber .icon-box-3d { background: rgba(251,191,36,0.12); border-color: rgba(251,191,36,0.18); color: #FBBF24; }
.item-amber.active .icon-box-3d, .item-amber:hover .icon-box-3d { background: rgba(251,191,36,0.2); }

/* Rose — RH */
.item-rose.active, .item-rose:hover { background: rgba(251,113,133,0.1); }
.item-rose.active::before { background: #FB7185; }
.item-rose .icon-box-3d { background: rgba(251,113,133,0.12); border-color: rgba(251,113,133,0.18); color: #FB7185; }
.item-rose.active .icon-box-3d, .item-rose:hover .icon-box-3d { background: rgba(251,113,133,0.2); }

/* Cyan — Organograma */
.item-cyan.active, .item-cyan:hover { background: rgba(34,211,238,0.1); }
.item-cyan.active::before { background: #22D3EE; }
.item-cyan .icon-box-3d { background: rgba(34,211,238,0.12); border-color: rgba(34,211,238,0.18); color: #22D3EE; }
.item-cyan.active .icon-box-3d, .item-cyan:hover .icon-box-3d { background: rgba(34,211,238,0.2); }

/* Teal — Gestão Técnica */
.item-teal.active, .item-teal:hover { background: rgba(20,184,166,0.1); }
.item-teal.active::before { background: #14B8A6; }
.item-teal .icon-box-3d { background: rgba(20,184,166,0.12); border-color: rgba(20,184,166,0.18); color: #14B8A6; }
.item-teal.active .icon-box-3d, .item-teal:hover .icon-box-3d { background: rgba(20,184,166,0.2); }

/* Slate — Configurações */
.item-slate.active, .item-slate:hover { background: rgba(148,163,184,0.1); }
.item-slate.active::before { background: #94A3B8; }
.item-slate .icon-box-3d { background: rgba(148,163,184,0.12); border-color: rgba(148,163,184,0.18); color: #94A3B8; }
.item-slate.active .icon-box-3d, .item-slate:hover .icon-box-3d { background: rgba(148,163,184,0.2); }

/* ── Badge de contagem ── */
.sidebar-badge {
    margin-left: auto;
    background: rgba(56,189,248,0.18);
    color: #38BDF8;
    font-size: 10px;
    font-weight: 700;
    padding: 1px 7px;
    border-radius: 20px;
    border: 1px solid rgba(56,189,248,0.25);
    line-height: 1.5;
    flex-shrink: 0;
}

/* ── Submenu arrow ── */
.submenu-arrow { font-size: 14px !important; transition: transform 0.2s ease; color: rgba(255,255,255,0.3); margin-left: auto; flex-shrink: 0; }
.submenu-arrow.rotate-180 { transform: rotate(180deg); }

/* ── SVG icons dentro de icon-box ── */
.icon-box-3d svg { width: 16px; height: 16px; }
</style>

<!-- Sidebar Header -->
<div id="sidebar-header">
    <img src="<?php echo BASE_URL; ?>/public/assets/images/logo-icon.png" alt="logo icon">
    <div class="sidebar-collapse-btn sidebar-text">
        <i class='bx bx-chevrons-left'></i>
    </div>
</div>

<!-- Navigation -->
<nav id="sidebar-nav">

    <?php if (has_permission('dashboard_view')) : ?>
        <a href="<?php echo BASE_URL; ?>/" class="sys-sidebar-item item-sky group <?php echo is_active('/dashboard'); ?>">
            <div class="icon-box-3d"><i class='bx bxs-home-circle'></i></div>
            <span class="sidebar-text">Dashboard</span>
        </a>
    <?php endif; ?>

    <span class="sidebar-section-label sidebar-text">Módulos Principais</span>

    <!-- Módulo Jurídico -->
    <?php if (has_any_permission(['juridico_dashboard_view', 'juridico_processos_view', 'juridico_documentos_manage', 'juridico_agenda_manage', 'contratos_view'])) : ?>
        <div class="has-submenu">
            <a href="#" class="sys-sidebar-item item-purple justify-between w-full group <?php echo is_active('/juridico') ?: is_active('/contratos'); ?>">
                <div class="flex items-center gap-3" style="display:flex;align-items:center;gap:10px">
                    <div class="icon-box-3d"><i class='bx bxs-institution'></i></div>
                    <span class="sidebar-text">Jurídico</span>
                </div>
                <i class='bx bx-chevron-down submenu-arrow sidebar-text <?php echo (is_active('/juridico') || is_active('/contratos')) ? 'rotate-180' : ''; ?>'></i>
            </a>
            <ul class="hidden mt-1 space-y-1">
                <li><a href="<?php echo BASE_URL; ?>/juridico/dashboard" class="sys-sidebar-item item-purple group <?php echo is_active('/juridico/dashboard'); ?>"><span class="sidebar-text">Dashboard</span></a></li>
                <li><a href="<?php echo BASE_URL; ?>/juridico/processos" class="sys-sidebar-item item-purple group <?php echo is_active('/juridico/processos'); ?>"><span class="sidebar-text">Processos</span></a></li>
                <li><a href="<?php echo BASE_URL; ?>/juridico/documentos" class="sys-sidebar-item item-purple group <?php echo is_active('/juridico/documentos'); ?>"><span class="sidebar-text">Documentos</span></a></li>
                <?php if (has_any_permission(['contratos_view', 'contratos_create', 'contratos_edit'])) : ?>
                    <li><a href="<?php echo BASE_URL; ?>/contratos" class="sys-sidebar-item item-purple group <?php echo is_active('/contratos'); ?>"><span class="sidebar-text">Contratos</span></a></li>
                <?php endif; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if (has_any_permission(['clientes_view', 'clientes_create', 'clientes_edit', 'clientes_delete', 'clientes_reports_view'])) : ?>
        <a href="<?php echo BASE_URL; ?>/clientes" class="sys-sidebar-item item-blue group <?php echo is_active('/clientes'); ?>">
            <div class="icon-box-3d"><i class='bx bxs-user-pin'></i></div>
            <span class="sidebar-text">Clientes</span>
        </a>
    <?php endif; ?>

    <?php if (has_any_permission(['fornecedores_view', 'fornecedores_create', 'fornecedores_edit', 'fornecedores_delete'])) : ?>
        <a href="<?php echo BASE_URL; ?>/fornecedores" class="sys-sidebar-item item-violet group <?php echo is_active('/fornecedores'); ?>">
            <div class="icon-box-3d"><i class='bx bxs-package'></i></div>
            <span class="sidebar-text">Fornecedores</span>
        </a>
    <?php endif; ?>

    <!-- Comercial -->
    <?php if (has_any_permission(['comercial_propostas_view', 'comercial_licitacoes_view'])) : ?>
        <div class="has-submenu">
            <a href="#" class="sys-sidebar-item item-emerald justify-between w-full group <?php echo is_active('/orcamento'); ?>">
                <div style="display:flex;align-items:center;gap:10px">
                    <div class="icon-box-3d">
                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M3 17l4-5 4 3 4-6 4 4" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M14 7h6v6" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <span class="sidebar-text">Comercial</span>
                </div>
                <i class='bx bx-chevron-down submenu-arrow sidebar-text'></i>
            </a>
            <ul class="hidden mt-1 space-y-1">
                <?php if (has_permission('comercial_propostas_view')) : ?>
                    <li>
                        <a href="<?php echo BASE_URL; ?>/orcamento/index" class="sys-sidebar-item item-emerald group <?php echo is_active('/orcamento'); ?>" style="justify-content:space-between">
                            <span class="sidebar-text">Orçamentos / Propostas</span>
                            <?php if (isset($contagemPropostasPendentes) && $contagemPropostasPendentes > 0) : ?>
                                <span class="sidebar-badge"><?php echo $contagemPropostasPendentes; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                <?php endif; ?>
                <?php if (has_permission('comercial_licitacoes_view')) : ?>
                    <li><a href="<?php echo BASE_URL; ?>/licitacoes" class="sys-sidebar-item item-emerald group <?php echo is_active('/licitacoes'); ?>"><span class="sidebar-text">Licitações</span></a></li>
                <?php endif; ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- Financeiro -->
    <?php if (has_any_permission(['financeiro_dashboard_view', 'financeiro_lancamentos_view', 'financeiro_reports_view', 'patrimonio_view', 'financeiro_prestacao_contas_view', 'financeiro_prestacao_contas_approve'])) : ?>
        <div class="has-submenu">
            <a href="#" class="sys-sidebar-item item-amber justify-between w-full group <?php echo is_active('/financeiro') ?: is_active('/patrimonio'); ?>">
                <div style="display:flex;align-items:center;gap:10px">
                    <div class="icon-box-3d"><i class='bx bxs-dollar-circle'></i></div>
                    <span class="sidebar-text">Financeiro</span>
                </div>
                <i class='bx bx-chevron-down submenu-arrow sidebar-text'></i>
            </a>
            <ul class="hidden mt-1 space-y-1">
                <?php if (has_permission('financeiro_dashboard_view')) : ?>
                    <li><a href="<?php echo BASE_URL; ?>/financeiro" class="sys-sidebar-item item-amber group <?php echo is_active('/financeiro/index'); ?>"><i class='bx bxs-pie-chart-alt-2'></i><span class="sidebar-text">Dashboard</span></a></li>
                <?php endif; ?>
                <?php if (has_permission('financeiro_lancamentos_view')) : ?>
                    <li class="has-submenu">
                        <a href="#" class="sys-sidebar-item item-amber justify-between w-full group" style="justify-content:space-between">
                            <div style="display:flex;align-items:center;gap:8px"><i class='bx bx-money-withdraw'></i><span class="sidebar-text">Pagamentos</span></div>
                            <i class='bx bx-chevron-down submenu-arrow sidebar-text' style="font-size:12px"></i>
                        </a>
                        <ul class="hidden mt-1 space-y-1" style="border-left:1px solid rgba(255,255,255,0.06);margin-left:14px;padding-left:10px">
                            <li><a href="<?php echo BASE_URL; ?>/financeiro/pagar?status=Pendente" class="sys-sidebar-item item-amber group"><span class="sidebar-text">Contas a Pagar</span></a></li>
                            <li><a href="<?php echo BASE_URL; ?>/financeiro/pagar?status=Pago" class="sys-sidebar-item item-amber group"><span class="sidebar-text">Contas Pagas</span></a></li>
                        </ul>
                    </li>
                    <li class="has-submenu">
                        <a href="#" class="sys-sidebar-item item-amber justify-between w-full group" style="justify-content:space-between">
                            <div style="display:flex;align-items:center;gap:8px"><i class='bx bx-money'></i><span class="sidebar-text">Recebimentos</span></div>
                            <i class='bx bx-chevron-down submenu-arrow sidebar-text' style="font-size:12px"></i>
                        </a>
                        <ul class="hidden mt-1 space-y-1" style="border-left:1px solid rgba(255,255,255,0.06);margin-left:14px;padding-left:10px">
                            <li><a href="<?php echo BASE_URL; ?>/financeiro/receber?status=Pendente" class="sys-sidebar-item item-amber group"><span class="sidebar-text">Contas a Receber</span></a></li>
                            <li><a href="<?php echo BASE_URL; ?>/financeiro/receber?status=Pago" class="sys-sidebar-item item-amber group"><span class="sidebar-text">Contas Recebidas</span></a></li>
                        </ul>
                    </li>
                    <li><a href="<?php echo BASE_URL; ?>/financeiro/movimentacoes" class="sys-sidebar-item item-amber group <?php echo is_active('/movimentacoes'); ?>"><i class='bx bx-transfer'></i><span class="sidebar-text">Movimentações</span></a></li>
                <?php endif; ?>
                <?php if (has_any_permission(['patrimonio_view', 'patrimonio_create'])) : ?>
                    <li><a href="<?php echo BASE_URL; ?>/patrimonio" class="sys-sidebar-item item-amber group <?php echo is_active('/patrimonio'); ?>"><i class='bx bxs-buildings'></i><span class="sidebar-text">Patrimônio</span></a></li>
                <?php endif; ?>
                <?php if (has_permission('financeiro_reports_view')) : ?>
                    <li><a href="<?php echo BASE_URL; ?>/financeiro/relatorio" class="sys-sidebar-item item-amber group"><i class='bx bx-file-find'></i><span class="sidebar-text">Relatórios</span></a></li>
                <?php endif; ?>
                <?php if (has_permission('financeiro_prestacao_contas_view')) : ?>
                    <li><a href="<?php echo BASE_URL; ?>/financeiro/prestacaoContas" class="sys-sidebar-item item-amber group <?php echo is_active('/prestacaoContas'); ?>"><i class='bx bxs-receipt'></i><span class="sidebar-text">Prest. de Contas</span></a></li>
                <?php endif; ?>
                <?php if (has_permission('financeiro_prestacao_contas_approve')) : ?>
                    <li><a href="<?php echo BASE_URL; ?>/financeiro/aprovacaoPrestacaoContas" class="sys-sidebar-item item-amber group <?php echo is_active('/aprovacaoPrestacaoContas'); ?>"><i class='bx bxs-check-shield'></i><span class="sidebar-text">Aprovação de Contas</span></a></li>
                <?php endif; ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- Recursos Humanos -->
    <?php
    $rhPermissions = ['rh_dashboard_view','rh_funcionarios_manage','rh_folha_pagamento_manage','rh_ferias_manage','rh_rescisao_manage','rh_treinamentos_view','rh_reports_view'];
    ?>
    <?php if (has_any_permission($rhPermissions)) : ?>
        <div class="has-submenu">
            <a href="#" class="sys-sidebar-item item-rose justify-between w-full group <?php echo is_active('/rh') ?: is_active('/treinamentos'); ?>">
                <div style="display:flex;align-items:center;gap:10px">
                    <div class="icon-box-3d"><i class='bx bxs-group'></i></div>
                    <span class="sidebar-text">Recursos Humanos</span>
                </div>
                <i class='bx bx-chevron-down submenu-arrow sidebar-text'></i>
            </a>
            <ul class="hidden mt-1 space-y-1">
                <?php if (has_permission('rh_dashboard_view')) : ?>
                    <li><a href="<?php echo BASE_URL; ?>/rh" class="sys-sidebar-item item-rose group <?php echo is_active('/rh/index'); ?>"><span class="sidebar-text">Dashboard</span></a></li>
                <?php endif; ?>
                <?php if (has_permission('rh_funcionarios_manage')) : ?>
                    <li><a href="<?php echo BASE_URL; ?>/rh/registroFuncionario" class="sys-sidebar-item item-rose group"><span class="sidebar-text">Funcionários</span></a></li>
                <?php endif; ?>
                <?php if (has_any_permission(['rh_folha_pagamento_manage', 'rh_ferias_manage', 'rh_rescisao_manage'])) : ?>
                    <li class="has-submenu">
                        <a href="#" class="sys-sidebar-item item-rose justify-between w-full group" style="justify-content:space-between">
                            <span class="sidebar-text">Cálculos</span>
                            <i class='bx bx-chevron-down submenu-arrow sidebar-text' style="font-size:12px"></i>
                        </a>
                        <ul class="hidden mt-1 space-y-1" style="border-left:1px solid rgba(255,255,255,0.06);margin-left:14px;padding-left:10px">
                            <?php if (has_permission('rh_folha_pagamento_manage')) : ?>
                                <li><a href="<?php echo BASE_URL; ?>/rh/folhaDePagamento" class="sys-sidebar-item item-rose group"><span class="sidebar-text">Folha de Pagamento</span></a></li>
                            <?php endif; ?>
                            <?php if (has_permission('rh_ferias_manage')) : ?>
                                <li><a href="<?php echo BASE_URL; ?>/rh/calculoFerias" class="sys-sidebar-item item-rose group"><span class="sidebar-text">Férias</span></a></li>
                            <?php endif; ?>
                            <?php if (has_permission('rh_rescisao_manage')) : ?>
                                <li><a href="<?php echo BASE_URL; ?>/rh/calculoRescisao" class="sys-sidebar-item item-rose group"><span class="sidebar-text">Rescisão</span></a></li>
                            <?php endif; ?>
                        </ul>
                    </li>
                <?php endif; ?>
                <?php if (has_permission('rh_treinamentos_view')) : ?>
                    <li><a href="<?php echo BASE_URL; ?>/treinamentos" class="sys-sidebar-item item-rose group <?php echo is_active('/treinamentos'); ?>"><span class="sidebar-text">Treinamentos</span></a></li>
                <?php endif; ?>
                <?php if (has_permission('rh_reports_view')) : ?>
                    <li class="has-submenu">
                        <a href="#" class="sys-sidebar-item item-rose justify-between w-full group" style="justify-content:space-between">
                            <span class="sidebar-text">Relatórios</span>
                            <i class='bx bx-chevron-down submenu-arrow sidebar-text' style="font-size:12px"></i>
                        </a>
                        <ul class="hidden mt-1 space-y-1" style="border-left:1px solid rgba(255,255,255,0.06);margin-left:14px;padding-left:10px">
                            <li><a href="<?php echo BASE_URL; ?>/rh/relatorios" class="sys-sidebar-item item-rose group"><span class="sidebar-text">Geral</span></a></li>
                            <li><a href="<?php echo BASE_URL; ?>/rh/relatorioFichaCadastral" class="sys-sidebar-item item-rose group"><span class="sidebar-text">Ficha Cadastral</span></a></li>
                            <li><a href="<?php echo BASE_URL; ?>/rh/historicoFerias" class="sys-sidebar-item item-rose group"><span class="sidebar-text">Histórico de Férias</span></a></li>
                        </ul>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    <?php endif; ?>

    <span class="sidebar-section-label sidebar-text">Gestão e Documentos</span>

    <?php if (has_permission('organograma_view')) : ?>
        <a href="<?php echo BASE_URL; ?>/organograma" class="sys-sidebar-item item-cyan group <?php echo is_active('/organograma'); ?>">
            <div class="icon-box-3d">
                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect x="9" y="1" width="6" height="4" rx="1" fill="currentColor"/>
                    <rect x="1" y="15" width="6" height="4" rx="1" fill="currentColor"/>
                    <rect x="9" y="15" width="6" height="4" rx="1" fill="currentColor"/>
                    <rect x="17" y="15" width="6" height="4" rx="1" fill="currentColor"/>
                    <line x1="12" y1="5" x2="12" y2="11" stroke="currentColor" stroke-width="1.6"/>
                    <line x1="4" y1="11" x2="20" y2="11" stroke="currentColor" stroke-width="1.6"/>
                    <line x1="4" y1="11" x2="4" y2="15" stroke="currentColor" stroke-width="1.6"/>
                    <line x1="12" y1="11" x2="12" y2="15" stroke="currentColor" stroke-width="1.6"/>
                    <line x1="20" y1="11" x2="20" y2="15" stroke="currentColor" stroke-width="1.6"/>
                </svg>
            </div>
            <span class="sidebar-text">Organograma &amp; KPIs</span>
        </a>
    <?php endif; ?>

    <?php if (has_any_permission(['projetos_view', 'projetos_create', 'projetos_edit', 'projetos_delete', 'pops_view', 'licencas_operacao_view'])) : ?>
        <div class="has-submenu">
            <a href="#" class="sys-sidebar-item item-teal justify-between w-full group <?php echo is_active('/projetos') ?: (is_active('/pops') ?: is_active('/licencas')); ?>">
                <div style="display:flex;align-items:center;gap:10px">
                    <div class="icon-box-3d"><i class='bx bxs-book-content'></i></div>
                    <span class="sidebar-text">Gestão Técnica</span>
                </div>
                <i class='bx bx-chevron-down submenu-arrow sidebar-text'></i>
            </a>
            <ul class="hidden mt-1 space-y-1">
                <?php if (has_any_permission(['projetos_view', 'projetos_create', 'projetos_edit', 'projetos_delete'])) : ?>
                    <li><a href="<?php echo BASE_URL; ?>/projetos" class="sys-sidebar-item item-teal group <?php echo is_active('/projetos'); ?>"><span class="sidebar-text">Projetos</span></a></li>
                <?php endif; ?>
                <?php if (has_permission('pops_view')) : ?>
                    <li><a href="<?php echo BASE_URL; ?>/pops" class="sys-sidebar-item item-teal group <?php echo is_active('/pops'); ?>"><span class="sidebar-text">POPs</span></a></li>
                <?php endif; ?>
                <?php if (has_permission('licencas_operacao_view')) : ?>
                    <li><a href="<?php echo BASE_URL; ?>/licencasOperacao" class="sys-sidebar-item item-teal group <?php echo is_active('/licencasOperacao'); ?>"><span class="sidebar-text">Licenças de Operação</span></a></li>
                <?php endif; ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- Administração -->
    <?php $configPermissions = ['config_empresa_manage', 'config_usuarios_view', 'config_perfis_manage', 'config_financeiro_manage', 'config_clientes_manage', 'config_audit_view']; ?>
    <?php if (has_any_permission($configPermissions)) : ?>
        <span class="sidebar-section-label sidebar-text">Administração</span>
        <a href="<?php echo BASE_URL; ?>/configuracoes" class="sys-sidebar-item item-slate group <?php echo is_active('/configuracoes'); ?>">
            <div class="icon-box-3d"><i class='bx bxs-cog'></i></div>
            <span class="sidebar-text">Configurações</span>
        </a>
    <?php endif; ?>

</nav>
