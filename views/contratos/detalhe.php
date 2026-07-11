<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Lora:wght@500;600&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
:root {
    --c-bg:           #F4F6FA;
    --c-surface:      #FFFFFF;
    --c-border:       #E4E9F0;
    --c-border-md:    #C8D0DC;
    --c-text:         #18243A;
    --c-text-2:       #4A5878;
    --c-text-3:       #8A97AE;

    --c-accent:       #2563eb;
    --c-accent-deep:  #1d4ed8;
    --c-accent-soft:  #dbeafe;

    --c-green:        #17673E;
    --c-green-light:  #E6F5EE;
    --c-red:          #921C1C;
    --c-red-light:    #FDF0F0;

    --c-amber:        #d97706;
    --c-amber-light:  #fef3c7;
    --c-amber-border: #fcd34d;

    --radius:         8px;
    --radius-lg:      14px;
    --font-display:   'Lora', Georgia, serif;
    --font-body:      'DM Sans', system-ui, sans-serif;
    --shadow:         0 1px 4px rgba(0,0,0,.07);
}

/* Ajustes para Modo Escuro (Dark Mode) */
.dark-theme .detail-wrap {
    --c-bg:           var(--db-bg, #0d1117);
    --c-surface:      var(--db-surface, #161b22);
    --c-border:       var(--db-border, #30363d);
    --c-text:         var(--db-text, #e6edf3);
    --c-text-2:       var(--db-text2, #8b949e);
    --c-text-3:       var(--db-text3, #94A3B8);
    --c-accent-soft:  rgba(37, 99, 235, 0.15);
    --c-green-light:  rgba(22, 163, 74, 0.15);
    --c-red-light:    rgba(220, 38, 38, 0.15);
    --c-amber-light:  rgba(217, 119, 6, 0.15);
}

.dark-theme .bg-gray-50 { background-color: var(--c-bg) !important; }
.dark-theme .bg-gray-100 { background-color: var(--c-bg) !important; }

.detail-wrap { font-family: var(--font-body); color: var(--c-text); }
.detail-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; gap: 16px; flex-wrap: wrap; }
.detail-title { font-family: var(--font-display); font-size: 24px; font-weight: 600; }
.detail-subtitle { font-size: 14px; color: var(--c-text-3); }

.info-card { background: var(--c-surface); border: 1px solid var(--c-border); border-radius: var(--radius-lg); box-shadow: var(--shadow); height: 100%; }
.info-card-header { padding: 16px 22px; border-bottom: 1px solid var(--c-border); display: flex; align-items: center; gap: 10px; }
.info-card-title { font-family: var(--font-display); font-size: 16px; font-weight: 600; color: var(--c-text); }
.info-card-body { padding: 20px 22px; }

.data-row { display: flex; flex-direction: column; gap: 4px; margin-bottom: 16px; }
.data-row:last-child { margin-bottom: 0; }
.data-label { font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .03em; color: var(--c-text-3); }
.data-value { font-size: 14px; color: var(--c-text); font-weight: 500; }
.data-value.bold { font-weight: 600; color: var(--c-accent); }

.status-badge { display: inline-flex; align-items: center; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 700; }
.status-vigente { background: var(--c-green-light); color: var(--c-green); }
.status-pendente { background: var(--c-amber-light); color: var(--c-amber); }
.status-finalizado { background: var(--c-accent-soft); color: var(--c-accent); }

.vigencia-box { display: flex; align-items: center; gap: 20px; background: var(--c-bg); padding: 16px; border-radius: 10px; border: 1px solid var(--c-border); }
.vigencia-item { flex: 1; }
.vigencia-sep { color: var(--c-border-md); font-size: 20px; }

.timeline-item { position: relative; padding-left: 24px; padding-bottom: 20px; border-left: 2px solid var(--c-border); }
.timeline-item::before { content: ''; position: absolute; left: -7px; top: 0; width: 12px; height: 12px; border-radius: 50%; background: var(--c-accent); border: 2px solid #fff; }
.timeline-item:last-child { border-left-color: transparent; padding-bottom: 0; }

.btn-action { display: inline-flex; align-items: center; gap: 8px; padding: 8px 16px; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; transition: all .2s; text-decoration: none; border: 1px solid transparent; }
.btn-primary { background: var(--c-accent); color: #fff; }
.btn-primary:hover { background: var(--c-accent-deep); }
.btn-outline { background: #fff; border-color: var(--c-border); color: var(--c-text-2); }
.btn-outline:hover { background: var(--c-bg); }
.btn-pdf { background: #E11D48; color: #fff; }
.btn-pdf:hover { background: #BE123C; }
</style>

<div class="detail-wrap">

<!-- ════════ CABEÇALHO ════════ -->
<div class="flex justify-between items-center mb-6">
    <div>
        <h2 class="detail-title">Instrumento: <?= htmlspecialchars($contrato['titulo'] ?? 'Contrato') ?></h2>
        <p class="detail-subtitle">Gestão de vigência, compliance e histórico de aditivos</p>
    </div>
    <div class="flex gap-2">
        <?php if ($contrato['status'] !== 'Finalizado') : ?>
            <button data-id="<?= $contrato['id'] ?>" class="btn-action btn-primary open-edit-modal-btn">
                <i class="fas fa-pen-to-square"></i> Editar
            </button>
        <?php endif; ?>
        <a href="<?= BASE_URL ?>/contratos/gerarPdfFinal/<?= $contrato['id'] ?>" target="_blank" class="btn-action btn-pdf">
            <i class="fas fa-file-pdf"></i>
            Gerar PDF (ABNT)
        </a>
        <a href="<?= BASE_URL ?>/contratos" class="btn-action btn-outline">
            &larr; Voltar
        </a>
    </div>
</div>

<!-- ════════ LAYOUT ════════ -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
    
    <!-- COLUNA LATERAL: STATUS E FINANCEIRO -->
    <div class="space-y-6">
        
        <!-- Card Principal -->
        <div class="info-card">
            <div class="info-card-header">
                <i class="fas fa-file-contract text-blue-600"></i>
                <span class="info-card-title">Dados Jurídicos</span>
            </div>
            <div class="info-card-body">
                <div class="data-row">
                    <span class="data-label">Status do Contrato</span>
                    <div>
                        <?php
                        $statusClass = match($contrato['status']) {
                            'Em Vigência' => 'status-vigente',
                            'Pendência Assinatura', 'Pendente Assinatura' => 'status-pendente',
                            'Finalizado' => 'status-finalizado',
                            default => 'status-pendente'
                        };
                        ?>
                        <span class="status-badge <?= $statusClass ?>">
                            <i class="fas fa-circle mr-2" style="font-size:7px"></i>
                            <?= htmlspecialchars($contrato['status']) ?>
                        </span>
                    </div>
                </div>
                <div class="data-row">
                    <span class="data-label">Tipo de Contrato</span>
                    <span class="data-value"><?= htmlspecialchars($contrato['tipo']) ?></span>
                </div>
                <div class="data-row">
                    <span class="data-label">Número / Código</span>
                    <span class="data-value"><?= htmlspecialchars($contrato['numero_contrato'] ?? 'N/A') ?></span>
                </div>
                <div class="data-row">
                    <span class="data-label">ID/CTR-CLIENTE</span>
                    <span class="data-value"><?= htmlspecialchars($contrato['numero_contrato_cliente'] ?? 'N/A') ?></span>
                </div>
                <div class="data-row">
                    <span class="data-label">Base de Referência</span>
                    <span class="data-value"><?= htmlspecialchars($contrato['base_referencia'] ?? 'N/A') ?></span>
                </div>
                <div class="data-row">
                    <span class="data-label">Foro de Eleição</span>
                    <span class="data-value"><?= htmlspecialchars($contrato['foro_eleicao'] ?? 'Não definido') ?></span>
                </div>
            </div>
        </div>

        <!-- Card Financeiro -->
        <div class="info-card">
            <div class="info-card-header">
                <i class="fas fa-sack-dollar text-green-600"></i>
                <span class="info-card-title">Resumo Financeiro</span>
            </div>
            <div class="info-card-body">
                <div class="data-row">
                    <span class="data-label">Valor Total</span>
                    <span class="data-value bold text-xl">R$ <?= number_format($contrato['valor'], 2, ',', '.') ?></span>
                </div>
                <div class="data-row">
                    <span class="data-label">Condição de Pagamento</span>
                    <span class="data-value"><?= htmlspecialchars($contrato['condicao_pagamento'] ?? 'N/A') ?></span>
                </div>
                <div class="data-row">
                    <span class="data-label">Forma de Recebimento</span>
                    <span class="data-value"><?= htmlspecialchars($contrato['forma_pagamento'] ?? 'N/A') ?></span>
                </div>
            </div>
        </div>

        <!-- Compliance -->
        <?php if(!empty($contrato['clausula_lgpd']) || !empty($contrato['risco_contratual'])): ?>
        <div class="info-card">
            <div class="info-card-header">
                <i class="fas fa-shield-halved text-gold-600"></i>
                <span class="info-card-title">Compliance & Risco</span>
            </div>
            <div class="info-card-body">
                <div class="data-row">
                    <span class="data-label">Grau de Risco</span>
                    <span class="data-value"><?= htmlspecialchars($contrato['risco_contratual'] ?? 'Baixo') ?></span>
                </div>
                <div class="data-row">
                    <span class="data-label">Conformidade LGPD</span>
                    <span class="data-value"><?= htmlspecialchars($contrato['clausula_lgpd'] ?? 'Sim') ?></span>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- COLUNA PRINCIPAL: VIGÊNCIA E ADITIVOS -->
    <div class="lg:col-span-2 space-y-6">
        
        <!-- Vigência -->
        <div class="info-card">
            <div class="info-card-header">
                <i class="far fa-calendar-check text-blue-600"></i>
                <span class="info-card-title">Prazos de Vigência</span>
            </div>
            <div class="info-card-body">
                <div class="vigencia-box">
                    <div class="vigencia-item">
                        <span class="data-label">Início</span>
                        <div class="text-lg font-semibold"><?= date('d/m/Y', strtotime($contrato['data_inicio'])) ?></div>
                    </div>
                    <div class="vigencia-sep">&rarr;</div>
                    <div class="vigencia-item">
                        <span class="data-label">Vencimento</span>
                        <div class="text-lg font-semibold text-red-600">
                            <?= $contrato['vencimento'] ? date('d/m/Y', strtotime($contrato['vencimento'])) : 'Indeterminado' ?>
                        </div>
                    </div>
                    <div class="vigencia-item text-right">
                        <?php
                        if ($contrato['vencimento']) {
                            $hoje = new DateTime();
                            $venc = new DateTime($contrato['vencimento']);
                            $diff = $hoje->diff($venc);
                            $dias = (int)$diff->format('%r%a');
                            if ($dias < 0) echo '<span class="text-red-600 font-bold">Vencido</span>';
                            else echo '<span class="text-blue-600 font-bold">Faltam '.$dias.' dias</span>';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Objeto -->
        <div class="info-card">
            <div class="info-card-header">
                <i class="fas fa-align-left text-blue-600"></i>
                <span class="info-card-title">Objeto do Contrato</span>
            </div>
            <div class="info-card-body">
                <div class="text-gray-700 leading-relaxed bg-gray-50 p-4 rounded-lg border border-dashed">
                    <?= nl2br(htmlspecialchars($contrato['objeto'])) ?>
                </div>
            </div>
        </div>

        <!-- Histórico de Aditivos (Acompanhamento de Mudanças) -->
        <div class="info-card">
            <div class="info-card-header justify-between">
                <div class="flex items-center gap-2">
                    <i class="fas fa-history text-blue-600"></i>
                    <span class="info-card-title">Mudanças e Aditivos</span>
                </div>
                <button id="open-aditivo-modal-btn" class="text-xs font-bold text-blue-600 hover:underline bg-transparent border-none cursor-pointer">
                    <i class="fas fa-plus mr-1"></i> Novo Aditivo
                </button>
            </div>
            <div class="info-card-body">
                <?php if (empty($aditivos)) : ?>
                    <div class="text-center py-6">
                        <i class="fas fa-info-circle text-gray-300 text-3xl mb-2"></i>
                        <p class="text-gray-500 text-sm">Nenhuma alteração registrada para este contrato.</p>
                    </div>
                <?php else : ?>
                    <div class="timeline">
                        <?php foreach ($aditivos as $aditivo) : ?>
                            <div class="timeline-item">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <div class="font-semibold text-gray-800">
                                            <?= htmlspecialchars($aditivo['tipo_aditivo']) ?>
                                            <span class="text-xs text-gray-400 ml-2">
                                                <i class="far fa-clock mr-1"></i><?= date('d/m/Y', strtotime($aditivo['data_aditivo'])) ?>
                                            </span>
                                        </div>
                                        <div class="text-sm text-gray-600 mt-1"><?= nl2br(htmlspecialchars($aditivo['descricao'])) ?></div>
                                        
                                        <div class="flex gap-4 mt-2">
                                            <?php if ($aditivo['valor_alteracao']) : ?>
                                                <span class="text-[11px] bg-green-50 text-green-700 px-2 py-1 rounded border border-green-100">
                                                    Impacto: <strong>R$ <?= number_format($aditivo['valor_alteracao'], 2, ',', '.') ?></strong>
                                                </span>
                                            <?php endif; ?>
                                            <?php if ($aditivo['novo_vencimento']) : ?>
                                                <span class="text-[11px] bg-blue-50 text-blue-700 px-2 py-1 rounded border border-blue-100">
                                                    Prorrogado até: <strong><?= date('d/m/Y', strtotime($aditivo['novo_vencimento'])) ?></strong>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="flex gap-2">
                                        <?php if (!empty($aditivo['documento_path'])) : ?>
                                            <a href="<?= BASE_URL ?>/contratos/download/<?= htmlspecialchars($aditivo['documento_path']) ?>" target="_blank" class="text-blue-500 hover:text-blue-700" title="Ver Comprovante">
                                                <i class="fas fa-paperclip"></i>
                                            </a>
                                        <?php endif; ?>
                                        <button data-aditivo-id="<?= $aditivo['id'] ?>" class="edit-aditivo-btn text-gray-400 hover:text-blue-600 border-none bg-transparent cursor-pointer">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

</div> <!-- /grid -->
</div> <!-- /detail-wrap -->


<!-- Modal para Adicionar Aditivo -->
<div id="aditivo-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-10 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center border-b pb-3 mb-4">
            <h3 class="text-xl font-bold text-gray-900">Registrar Novo Aditivo</h3>
            <button id="close-aditivo-modal-btn" class="text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
        </div>
        <div id="aditivo-modal-body">
            <?php
            // Passa o ID do contrato principal para o formulário de aditivo
            $contrato_id = $contrato['id'];
            require ROOT_PATH . '/views/contratos/form_aditivo.php';
            ?>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const aditivoModal = document.getElementById('aditivo-modal');
        const openAditivoBtn = document.getElementById('open-aditivo-modal-btn');
        const closeAditivoBtn = document.getElementById('close-aditivo-modal-btn');

        // Abrir modal para NOVO aditivo
        openAditivoBtn.addEventListener('click', () => {
            fetch('<?php echo BASE_URL; ?>/contratos/getFormForEditAditivo/0?contrato_id=<?php echo $contrato['id']; ?>')
                .then(response => response.text())
                .then(html => {
                    document.getElementById('aditivo-modal-body').innerHTML = html;
                    aditivoModal.classList.remove('hidden');
                });
        });

        // Abrir modal para EDITAR aditivo
        document.querySelectorAll('.edit-aditivo-btn').forEach(button => {
            button.addEventListener('click', function() {
                const aditivoId = this.dataset.aditivoId;
                fetch(`<?php echo BASE_URL; ?>/contratos/getFormForEditAditivo/${aditivoId}`)
                    .then(response => response.text())
                    .then(html => {
                        document.getElementById('aditivo-modal-body').innerHTML = html;
                        aditivoModal.classList.remove('hidden');
                    });
            });
        });

        // Fechar modal
        aditivoModal.addEventListener('click', (e) => {
            // Fecha se clicar no X, no botão Cancelar, ou fora do conteúdo do modal
            if (e.target === aditivoModal || e.target.closest('#close-aditivo-modal-btn') || e.target.closest('#cancel-aditivo-btn')) {
                aditivoModal.classList.add('hidden');
            }
        });
    });
</script>

<!-- Modal Genérico para Edição (Copiado de index.php e adaptado) -->
<div id="form-contrato-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-10 mx-auto p-5 border w-full max-w-4xl shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex justify-between items-center border-b pb-3">
                <h3 id="modal-title" class="text-xl font-bold text-gray-900"></h3>
                <button id="close-modal-btn" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <div id="modal-content" class="mt-4">
                <!-- O conteúdo do formulário de edição será carregado aqui -->
            </div>
        </div>
    </div>
</div>

<script>
    // Script para controlar o modal de edição nesta página
    console.log('Script de edição de contrato carregado');
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM carregado, inicializando modal de edição');
        const modal = document.getElementById('form-contrato-modal');
        if (!modal) {
            console.error('Modal form-contrato-modal não encontrado');
            return;
        }

        const modalTitle = document.getElementById('modal-title');
        const modalContent = document.getElementById('modal-content');
        const editBtn = document.querySelector('.open-edit-modal-btn');
        const closeBtn = document.getElementById('close-modal-btn');

        console.log('Elementos encontrados:', { modal, modalTitle, modalContent, editBtn, closeBtn });

        const openModal = () => modal.classList.remove('hidden');
        const closeModal = () => {
            modal.classList.add('hidden');
            modalContent.innerHTML = ''; // Limpa o conteúdo ao fechar
        };

        const openAjaxModal = async (url, title) => {
            console.log('Abrindo modal AJAX:', url, title);
            modalTitle.innerText = title;
            modalContent.innerHTML = '<p class="text-center">Carregando...</p>';
            openModal();

            try {
                console.log('Fazendo fetch para:', url);
                const response = await fetch(url, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                console.log('Resposta recebida:', response.status, response.statusText);
                if (!response.ok) throw new Error('Falha ao carregar o formulário.');
                const formHtml = await response.text();
                console.log('HTML recebido, tamanho:', formHtml.length);
                modalContent.innerHTML = formHtml;

                // Re-executa scripts específicos do conteúdo carregado no modal
                const scripts = modalContent.querySelectorAll('script');
                scripts.forEach(script => {
                    const newScript = document.createElement('script');
                    if (script.src) {
                        newScript.src = script.src;
                    } else {
                        newScript.textContent = script.textContent;
                    }
                    document.body.appendChild(newScript).parentNode.removeChild(newScript);
                });

                // Remove o script original do modal para evitar duplicação no DOM
                Array.from(modalContent.querySelectorAll('script')).forEach(oldScript => {
                    if (oldScript.parentNode) {
                        oldScript.parentNode.removeChild(oldScript);
                    }
                });
            } catch (error) {
                console.log('Erro no fetch:', error);
                modalContent.innerHTML = `<p class="text-center text-red-500">${error.message}</p>`;
            }
        };

        if (editBtn) {
            console.log('Botão editar encontrado:', editBtn);
            console.log('ID do contrato:', editBtn.dataset.id);
            editBtn.addEventListener('click', () => {
                console.log('Botão editar clicado');
                const url = `<?php echo BASE_URL; ?>/contratos/getFormForEdit/${editBtn.dataset.id}`;
                console.log('URL da requisição:', url);
                openAjaxModal(url, 'Editar Contrato');
            });
        } else {
            console.log('Botão editar não encontrado');
        }

        if (closeBtn) {
            closeBtn.addEventListener('click', closeModal);
        }

        modal.addEventListener('click', function(event) {
            if (event.target === modal || (event.target && event.target.id === 'cancel-form-btn')) {
                closeModal();
            }
        });
    });
</script>