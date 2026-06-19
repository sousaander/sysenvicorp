<?php
//use App\Helpers\ReportHelper;
?>

<div class="mb-8">
    <div class="flex flex-wrap justify-between items-center gap-4 mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white tracking-tight">Propostas & Orçamentos</h2>
            <p class="text-gray-500 dark:text-gray-400 text-sm">Gestão comercial e acompanhamento de propostas.</p>
        </div>

        <!-- Navegação Interativa entre as 4 Telas Principais -->
        <div class="flex flex-wrap gap-2 bg-white dark:bg-gray-800 p-1.5 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <a href="<?= BASE_URL ?>/orcamento/index" class="flex items-center gap-2 px-4 py-2 text-sm font-bold rounded-lg bg-sky-50 text-sky-600 border border-sky-100 transition shadow-sm">
                <i class="fas fa-list-ul"></i> <span class="hidden sm:inline">① Lista</span>
            </a>
            
            <a href="<?= BASE_URL ?>/orcamento/novo" class="flex items-center gap-2 px-4 py-2 text-sm font-bold text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-sky-600 rounded-lg transition">
                <i class="fas fa-plus-circle text-sky-500"></i> <span class="hidden sm:inline">② Novo</span>
            </a>

            <a id="btn-nav-view" href="javascript:void(0)" class="flex items-center gap-2 px-4 py-2 text-sm font-bold text-gray-300 dark:text-gray-600 bg-gray-50/50 dark:bg-gray-900/20 rounded-lg cursor-not-allowed transition" title="Selecione um item na lista abaixo para visualizar">
                <i class="fas fa-eye"></i> <span class="hidden sm:inline">③ Visualizar</span>
            </a>

            <a id="btn-nav-pdf" href="javascript:void(0)" target="_blank" class="flex items-center gap-2 px-4 py-2 text-sm font-bold text-gray-300 dark:text-gray-600 bg-gray-50/50 dark:bg-gray-900/20 rounded-lg cursor-not-allowed transition" title="Selecione um item na lista abaixo para gerar o PDF">
                <i class="fas fa-file-pdf"></i> <span class="hidden sm:inline">④ Impressão/PDF</span>
            </a>
        </div>
    </div>
</div>

<div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-md border border-gray-100 dark:border-gray-700">
    <div class="overflow-x-auto" style="min-height: 350px;">
        <table class="w-full text-sm text-left">
            <thead class="bg-gray-50 dark:bg-gray-900/50 text-gray-500 dark:text-gray-400 font-bold uppercase text-[11px] border-b dark:border-gray-700">
                <tr>
                    <th class="px-4 py-3">ID</th>
                    <th class="px-4 py-3">Cliente</th>
                    <th class="px-4 py-3">Título</th>
                    <th class="px-4 py-3 text-right">Total</th>
                    <th class="px-4 py-3 text-center">Status</th>
                    <th class="px-4 py-3 text-center">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if (empty($orcamentos)): ?>
                    <tr><td colspan="6" class="px-4 py-8 text-center text-gray-400 dark:text-gray-500">Nenhum registro encontrado.</td></tr>
                <?php else: ?>
                    <?php foreach ($orcamentos as $orc): 
                        // Normalização do status para as cores do Tailwind definidas no Controller
                        $statusKey = $orc['status'];
                        $sl = $statusLabels[$statusKey] ?? ['label' => $statusKey, 'cor' => 'gray'];
                    ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition cursor-pointer budget-row" data-id="<?= $orc['id'] ?>">
                            <td class="px-4 py-4 font-mono text-sky-600 font-bold"><?= htmlspecialchars($orc['numero']) ?></td>
                            <td class="px-4 py-4 font-semibold text-gray-700 dark:text-gray-300"><?= htmlspecialchars($orc['cliente_nome']) ?></td>
                            <td class="px-4 py-4 text-gray-600 dark:text-gray-400"><?= htmlspecialchars($orc['titulo']) ?></td>
                            <td class="px-4 py-4 text-right font-bold text-gray-800 dark:text-gray-200"><?= \App\Helpers\ReportHelper::formatCurrency($orc['total']) ?></td>
                            <td class="px-4 py-4 text-center relative">
                                <div class="inline-flex items-center gap-1">
                                    <span class="px-2 py-1 rounded-full text-[10px] font-bold uppercase bg-<?= $sl['cor'] ?>-100 dark:bg-<?= $sl['cor'] ?>-900/30 text-<?= $sl['cor'] ?>-600 dark:text-<?= $sl['cor'] ?>-400 inline-flex items-center gap-1">
                                        <span><?= $sl['label'] ?></span>
                                    </span>
                                    <button type="button" onclick="toggleStatusMenu(this, <?= $orc['id'] ?>)" class="p-1 rounded-full text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-600 transition cursor-pointer" title="Alterar status">
                                        <i class="fas fa-chevron-down text-[8px]"></i>
                                    </button>
                                </div>
                                <div class="hidden absolute right-4 top-12 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-2xl z-[100] min-w-[150px] status-menu-<?= $orc['id'] ?>">
                                    <button type="button" onclick="changeStatus(<?= $orc['id'] ?>, 'Rascunho')" class="block w-full text-left px-4 py-2.5 hover:bg-sky-50 dark:hover:bg-sky-900/20 text-xs font-bold text-gray-700 dark:text-gray-300 transition">Rascunho</button>
                                    <button type="button" onclick="changeStatus(<?= $orc['id'] ?>, 'Enviada')" class="block w-full text-left px-4 py-2.5 hover:bg-sky-50 dark:hover:bg-sky-900/20 text-xs font-bold text-gray-700 dark:text-gray-300 border-t dark:border-gray-700 transition">Enviada</button>
                                    <button type="button" onclick="changeStatus(<?= $orc['id'] ?>, 'Aprovada')" class="block w-full text-left px-4 py-2.5 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 text-xs font-bold text-emerald-600 dark:text-emerald-400 border-t dark:border-gray-700 transition">Aprovada</button>
                                    <button type="button" onclick="changeStatus(<?= $orc['id'] ?>, 'Rejeitada')" class="block w-full text-left px-4 py-2.5 hover:bg-rose-50 dark:hover:bg-rose-900/20 text-xs font-bold text-rose-600 dark:text-rose-400 border-t dark:border-gray-700 transition">Rejeitada</button>
                                    <button type="button" onclick="changeStatus(<?= $orc['id'] ?>, 'Cancelada')" class="block w-full text-left px-4 py-2.5 hover:bg-gray-50 dark:hover:bg-gray-700 text-xs font-bold text-gray-500 border-t dark:border-gray-700 transition">Cancelada</button>
                                </div>
                            </td>
                            <td class="px-4 py-4 text-center">
                                <div class="flex justify-center gap-2">
                                    <a href="<?= BASE_URL ?>/orcamento/ver/<?= $orc['id'] ?>" class="p-1.5 text-sky-500 hover:text-sky-700 transition" title="Visualizar">
                                        <i class="fas fa-eye text-lg"></i>
                                    </a>
                                    <a href="<?= BASE_URL ?>/orcamento/editar/<?= $orc['id'] ?>" class="p-1.5 text-amber-500 hover:text-amber-700 transition" title="Editar">
                                        <i class="fas fa-edit text-lg"></i>
                                    </a>
                                    <a href="<?= BASE_URL ?>/orcamento/clonar/<?= $orc['id'] ?>" class="p-1.5 text-emerald-500 hover:text-emerald-700 transition" title="Clonar / Duplicar">
                                        <i class="fas fa-copy text-lg"></i>
                                    </a>
                                    <button onclick="copiarLinkDireto(<?= $orc['id'] ?>)" class="p-1.5 text-slate-400 hover:text-sky-600 transition" title="Copiar Link de Aprovação">
                                        <i class="fas fa-link text-lg"></i>
                                    </button>
                                    <button onclick="enviarWhatsApp(<?= $orc['id'] ?>, '<?= addslashes($orc['titulo']) ?>', '<?= $orc['cliente_telefone'] ?? '' ?>')" class="p-1.5 text-green-500 hover:text-green-700 transition" title="Enviar via WhatsApp">
                                        <i class="fab fa-whatsapp text-lg"></i>
                                    </button>
                                    <button onclick="openEmailModal(<?= $orc['id'] ?>, '<?= addslashes($orc['titulo']) ?>', '<?= addslashes($orc['cliente_email'] ?? '') ?>')" class="p-1.5 text-blue-500 hover:text-blue-700 transition" title="Enviar por E-mail">
                                        <i class="fas fa-envelope text-lg"></i>
                                    </button>
                                    <button onclick="excluirProposta('<?= htmlspecialchars($orc['id']) ?>', this)" class="p-1.5 text-rose-500 hover:text-rose-700 transition" title="Excluir">
                                        <i class="fas fa-trash-alt text-lg"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Estrutura da Modal para Propostas -->
<div id="propostaModal" class="fixed inset-0 bg-gray-800 dark:bg-black bg-opacity-75 dark:bg-opacity-75 flex items-center justify-center z-50 hidden">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-3xl max-h-[90vh] overflow-y-auto">
        <div id="propostaModalContent" class="p-6">
            <p class="text-center dark:text-gray-300">Carregando formulário...</p>
        </div>
    </div>
</div>

<!-- Modal de Envio de E-mail -->
<div id="emailModal" class="fixed inset-0 bg-gray-800 dark:bg-black bg-opacity-75 dark:bg-opacity-75 flex items-center justify-center z-50 hidden">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-2xl">
        <form id="emailForm" action="" method="POST" class="p-6">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>" />
            <h3 class="text-xl font-bold mb-4 dark:text-white">Enviar Proposta por E-mail</h3>

            <div class="mb-4">
                <label for="email_destinatario" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Para:</label>
                <input type="email" name="email_destinatario" id="email_destinatario" required class="w-full border dark:border-gray-700 dark:bg-gray-700 dark:text-white rounded p-2 mt-1" placeholder="email@cliente.com">
            </div>

            <div class="mb-4">
                <label for="email_assunto" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Assunto:</label>
                <input type="text" name="email_assunto" id="email_assunto" required class="w-full border dark:border-gray-700 dark:bg-gray-700 dark:text-white rounded p-2 mt-1">
            </div>

            <div class="mb-4">
                <label for="email_corpo" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Mensagem:</label>
                <textarea name="email_corpo" id="email_corpo" rows="6" class="w-full border dark:border-gray-700 dark:bg-gray-700 dark:text-white rounded p-2 mt-1"></textarea>
            </div>

            <div class="flex justify-end gap-2">
                <button type="button" onclick="closeEmailModal()" class="px-4 py-2 bg-gray-300 dark:bg-gray-600 dark:text-white rounded font-bold">Cancelar</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded font-bold">
                    <i class="fas fa-paper-plane mr-1"></i> Enviar E-mail
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal de Confirmação de Duplicidade de Contrato (Tailwind CSS) -->
<div id="modalConfirmacaoContrato" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm flex items-center justify-center z-[100] hidden">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-lg w-full mx-4 overflow-hidden border border-gray-200 dark:border-gray-700 animate-popIn">
        <div class="p-6">
            <div class="flex items-center gap-4 mb-5">
                <div class="w-12 h-12 rounded-xl bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center text-amber-600 dark:text-amber-400">
                    <i class="fas fa-exclamation-triangle text-xl"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Atenção: Contratos Ativos</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400" id="modal-cliente-subtitle"></p>
                </div>
            </div>
            
            <div class="mb-6">
                <p class="text-sm text-gray-700 dark:text-gray-300 mb-3 font-medium">O cliente já possui os seguintes instrumentos:</p>
                <div class="max-h-48 overflow-y-auto space-y-2 pr-1 custom-scrollbar" id="modal-lista-contratos">
                    <!-- Lista injetada via JS -->
                </div>
            </div>

            <p class="text-sm text-gray-600 dark:text-gray-400 mb-6 leading-relaxed">
                Deseja que o sistema gere um <strong>NOVO</strong> contrato automático para esta proposta aprovada?
            </p>

            <div class="flex flex-col sm:flex-row gap-3">
                <button type="button" id="btn-confirm-sim" class="flex-1 px-4 py-3 bg-sky-600 hover:bg-sky-700 text-white text-sm font-bold rounded-xl transition shadow-lg shadow-sky-200 dark:shadow-none">
                    Sim, criar novo contrato
                </button>
                <button type="button" id="btn-confirm-nao" class="flex-1 px-4 py-3 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-200 text-sm font-bold rounded-xl hover:bg-gray-50 dark:hover:bg-gray-600 transition">
                    Não, apenas aprovar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    const modal = document.getElementById('propostaModal');
    const modalContent = document.getElementById('propostaModalContent');

    /**
     * Abre a modal e carrega o conteúdo via AJAX
     */
    function openPropostaModal(url) {
        if (!modal || !modalContent) return;

        modal.classList.remove('hidden');
        modalContent.innerHTML = `
            <div class="flex justify-center items-center py-16">
                <div class="animate-spin rounded-full h-12 w-12 border-4 border-sky-500 border-t-transparent"></div>
                <p class="ml-4 text-gray-600 font-semibold">Carregando formulário...</p>
            </div>`;

        // Adiciona o parâmetro ajax para o controller renderizar apenas o partial (formulario.php)
        const ajaxUrl = url.includes('?') ? `${url}&ajax=1` : `${url}?ajax=1`;

        fetch(ajaxUrl)
            .then(response => {
                if (!response.ok) throw new Error('Erro na requisição');
                return response.text();
            })
            .then(html => {
                modalContent.innerHTML = html;
            })
            .catch(error => {
                modalContent.innerHTML = `
                    <div class="p-8 text-center">
                        <i class="fas fa-exclamation-circle text-red-500 text-4xl mb-4"></i>
                        <p class="text-gray-800 font-bold">Erro ao carregar o formulário.</p>
                        <button onclick="closePropostaModal()" class="mt-4 text-sky-600 underline">Fechar</button>
                    </div>`;
                console.error('Modal Load Error:', error);
            });
    }

    function closePropostaModal() {
        modal.classList.add('hidden');
        modalContent.innerHTML = '';
    }

    /**
     * Abre a modal de e-mail e preenche os campos básicos
     */
    function openEmailModal(id, titulo, email) {
        const eModal = document.getElementById('emailModal');
        const form = document.getElementById('emailForm');
        
        form.action = `<?= BASE_URL ?>/orcamento/enviarEmail/${id}`;
        document.getElementById('email_destinatario').value = email || '';
        document.getElementById('email_assunto').value = `Proposta Comercial: ${titulo}`;
        document.getElementById('email_corpo').value = `Prezado(a),\n\nSegue em anexo nossa proposta comercial referente a "${titulo}".\n\nFicamos à disposição para qualquer esclarecimento.\n\nAtenciosamente,\nSua Empresa.`;

        eModal.classList.remove('hidden');
    }

    /**
     * Fecha a modal de e-mail
     */
    function closeEmailModal() {
        document.getElementById('emailModal').classList.add('hidden');
    }

    /**
     * Lógica de seleção de linha para ativar botões de navegação (otimizada)
     */
    document.addEventListener('DOMContentLoaded', function() {
        // Delegação de eventos para melhor performance
        const tableBody = document.querySelector('tbody');
        if (tableBody) {
            tableBody.addEventListener('click', function(e) {
                const row = e.target.closest('.budget-row');
                if (!row) return;

                // Se o clique for nas ações (última coluna), ignoramos a seleção da linha
                if (e.target.closest('td:last-child')) return;

                handleRowSelection(row);
            });
        }

        // Fecha menus de status ao clicar fora
        document.addEventListener('click', function(event) {
            if (!event.target.closest('[class*="status-menu-"]') && !event.target.closest('button[onclick*="toggleStatusMenu"]')) {
                document.querySelectorAll('[class*="status-menu-"]').forEach(m => m.classList.add('hidden'));
            }
        });
    });

    /**
     * Trata seleção de linha (extraído para reutilização)
     */
    function handleRowSelection(row) {
        const id = row.dataset.id;
        const btnView = document.getElementById('btn-nav-view');
        const btnPdf = document.getElementById('btn-nav-pdf');

        // Remove seleção visual anterior de todas as linhas (otimizado)
        const selectedRows = document.querySelectorAll('.budget-row.bg-sky-50');
        selectedRows.forEach(r => r.classList.remove('bg-sky-50', 'ring-1', 'ring-inset', 'ring-sky-200'));

        // Aplica destaque na linha clicada
        row.classList.add('bg-sky-50', 'ring-1', 'ring-inset', 'ring-sky-200');

        // Ativa os botões de navegação superior
        if (btnView) {
            btnView.href = `<?= BASE_URL ?>/orcamento/ver/${id}`;
            btnView.classList.remove('text-gray-300', 'bg-gray-50/50', 'cursor-not-allowed');
            btnView.classList.add('text-gray-600', 'hover:bg-gray-50', 'hover:text-sky-600');
        }

        if (btnPdf) {
            btnPdf.href = `<?= BASE_URL ?>/orcamento/pdf/${id}`;
            btnPdf.classList.remove('text-gray-300', 'bg-gray-50/50', 'cursor-not-allowed');
            btnPdf.classList.add('text-gray-600', 'hover:bg-gray-50', 'hover:text-sky-600');
        }
    }

    /**
     * Exclui uma proposta com confirmação
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

    /**
     * Gera link público e abre WhatsApp
     */
    function enviarWhatsApp(id, titulo, telefone) {
        const originalCursor = document.body.style.cursor;
        document.body.style.cursor = 'wait';

        fetch(`<?= BASE_URL ?>/orcamento/gerarLinkPublico/${id}`)
            .then(res => res.json())
            .then(data => {
                document.body.style.cursor = originalCursor;
                if (data.success) {
                    const texto = `Prezado cliente, segue o link para visualização e aprovação da proposta *${titulo}*:\n\n ${data.link} \n\nFicamos à disposição para qualquer dúvida através do nosso contato oficial: <?= WHATSAPP_COMERCIAL_FORMATTED ?>.`;
                    
                    const cleanPhone = telefone ? telefone.replace(/\D/g, '') : '';
                    const url = cleanPhone.length >= 10 
                        ? `https://wa.me/55${cleanPhone}?text=${encodeURIComponent(texto)}`
                        : `https://wa.me/?text=${encodeURIComponent(texto)}`;
                    
                    window.open(url, '_blank');
                } else {
                    alert(data.message || 'Erro ao gerar o link público.');
                }
            })
            .catch(err => {
                document.body.style.cursor = originalCursor;
                console.error('WhatsApp Error:', err);
                alert('Falha na comunicação com o servidor.');
            });
    }

    /**
     * Abre/fecha o menu de alteração de status
     */
    function toggleStatusMenu(button, id) {
        const menu = document.querySelector(`.status-menu-${id}`);
        const td = button.closest('td');
        if (!menu) return;

        // Fecha todos os menus abertos
        document.querySelectorAll('[class*="status-menu-"]').forEach(m => {
            if (m !== menu) {
                m.classList.add('hidden');
                m.closest('td').style.zIndex = '';
            }
        });

        const isOpening = menu.classList.contains('hidden');
        menu.classList.toggle('hidden');
        
        // Eleva o z-index da célula para o menu sobrepor as linhas de baixo
        td.style.zIndex = isOpening ? '50' : '';
    }

    /**
     * Altera o status da proposta via AJAX
     */
    function changeStatus(id, newStatus, confirmacaoDuplicado = null) {
        if (newStatus === 'Aprovada' && !confirmacaoDuplicado) {
            if (!confirm('Deseja marcar esta proposta como Aprovada?')) {
                toggleStatusMenu(null, id);
                return;
            }
        } else if (newStatus !== 'Aprovada') {
            const acao = newStatus === 'Rejeitada' ? 'rejeitar' : 'alterar para ' + newStatus;
            if (!confirm(`Deseja ${acao} esta proposta?`)) {
                toggleStatusMenu(null, id);
                return;
            }
        }

        let motivo = '';
        if (newStatus === 'Rejeitada') {
            motivo = prompt('Informe o motivo da rejeição (opcional):');
        }

        const formData = new FormData();
        formData.append('status', newStatus);
        formData.append('motivo', motivo);
        formData.append('csrf_token', '<?= $csrf_token ?? '' ?>');
        if (confirmacaoDuplicado) {
            formData.append('confirmacao_duplicado', confirmacaoDuplicado);
        }

        const originalCursor = document.body.style.cursor;
        document.body.style.cursor = 'wait';

        fetch(`<?= BASE_URL ?>/orcamento/updateStatusAjax/${id}`, {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(result => {
                document.body.style.cursor = originalCursor;

                if (result.confirmacao_necessaria) {
                    abrirModalConfirmacaoContrato(id, newStatus, result);
                    return;
                }

                if (result.success) {
                    window.location.reload();
                } else {
                    alert(result.message || 'Erro ao atualizar status.');
                    toggleStatusMenu(null, id);
                }
            })
            .catch(err => {
                document.body.style.cursor = originalCursor;
                console.error('Status update error:', err);
                alert('Erro na comunicação com o servidor.');
                toggleStatusMenu(null, id);
            });
    }

    function abrirModalConfirmacaoContrato(id, status, data) {
        const modal = document.getElementById('modalConfirmacaoContrato');
        const listContainer = document.getElementById('modal-lista-contratos');
        const subtitle = document.getElementById('modal-cliente-subtitle');
        
        subtitle.textContent = `Cliente: ${data.cliente_nome}`;
        listContainer.innerHTML = '';
        
        data.contratos.forEach(c => {
            const num = c.numero_contrato || `ID: ${c.id}`;
            const div = document.createElement('div');
            div.className = "p-3 rounded-xl bg-gray-50 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-700 text-[12px] hover:border-amber-200 transition-colors";
            div.innerHTML = `<div class="font-bold text-sky-600 dark:text-sky-400 mb-1">${num}</div>
                             <div class="text-gray-600 dark:text-gray-400 line-clamp-2">${c.objeto}</div>`;
            listContainer.appendChild(div);
        });
        
        modal.classList.remove('hidden');
        
        const btnSim = document.getElementById('btn-confirm-sim');
        const btnNao = document.getElementById('btn-confirm-nao');
        
        btnSim.onclick = () => {
            modal.classList.add('hidden');
            changeStatus(id, status, 'sim');
        };
        
        btnNao.onclick = () => {
            modal.classList.add('hidden');
            changeStatus(id, status, 'nao');
        };
    }

    /**
     * Gera o link e copia diretamente, garantindo o protocolo completo
     */
    function copiarLinkDireto(id) {
        const originalCursor = document.body.style.cursor;
        document.body.style.cursor = 'wait';

        fetch(`<?= BASE_URL ?>/orcamento/gerarLinkPublico/${id}`)
            .then(res => res.json())
            .then(data => {
                document.body.style.cursor = originalCursor;
                if (data.success) {
                    let url = data.link.trim();
                    
                    // Validação de protocolo no JS (Double-check)
                    if (!url.startsWith('http')) {
                        url = window.location.origin + (url.startsWith('/') ? '' : '/') + url;
                    }
                    
                    navigator.clipboard.writeText(url).then(() => {
                        alert('Link de aprovação copiado!');
                    });
                } else {
                    alert('Erro ao gerar link.');
                }
            })
            .catch(err => {
                document.body.style.cursor = originalCursor;
                console.error(err);
            });
    }

    // Fecha os menus quando clica fora
    document.addEventListener('click', function(event) {
        if (!event.target.closest('[class*="status-menu-"]') && !event.target.closest('button[onclick*="toggleStatusMenu"]')) {
            document.querySelectorAll('[class*="status-menu-"]').forEach(m => m.classList.add('hidden'));
        }
    });
</script>
