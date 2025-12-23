<div class="flex justify-between items-center mb-6">
    <div>
        <h2 class="text-2xl font-bold text-gray-800">Detalhes do Contrato</h2>
        <p class="text-gray-600">Visão completa do contrato, incluindo vigência, valores e histórico.</p>
    </div>
    <div class="flex gap-2">
        <button data-id="<?php echo $contrato['id']; ?>" class="open-edit-modal-btn bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 font-medium flex items-center gap-2" title="Editar Contrato">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z" />
                <path fill-rule="evenodd" d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" clip-rule="evenodd" />
            </svg>
            <span>Editar</span>
        </button>
        <a href="<?php echo BASE_URL; ?>/contratos/vigencia" class="bg-gray-200 text-gray-800 px-4 py-2 rounded-md hover:bg-gray-300 font-medium">
            &larr; Voltar
        </a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Coluna de Informações Principais -->
    <div class="lg:col-span-1 bg-white p-6 rounded-lg shadow-md">
        <h3 class="text-xl font-semibold mb-4 border-b pb-2">Informações Gerais</h3>
        <div class="space-y-4 text-sm">
            <div>
                <p class="font-medium text-gray-500">Tipo de Contrato</p>
                <p class="text-gray-800 font-semibold"><?php echo htmlspecialchars($contrato['tipo']); ?></p>
            </div>
            <div>
                <p class="font-medium text-gray-500">Parte Contratada</p>
                <p class="text-gray-800"><?php echo htmlspecialchars($contrato['parteContratada'] ?? 'Não informado'); ?></p>
            </div>
            <div>
                <p class="font-medium text-gray-500">Valor do Contrato</p>
                <p class="text-gray-800 font-bold text-lg text-green-700">R$ <?php echo number_format($contrato['valor'], 2, ',', '.'); ?></p>
            </div>
            <div>
                <p class="font-medium text-gray-500">Status</p>
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                    <?php
                    if ($contrato['status'] === 'Em Vigência') echo 'bg-green-100 text-green-800';
                    elseif ($contrato['status'] === 'Pendência Assinatura') echo 'bg-yellow-100 text-yellow-800';
                    elseif ($contrato['status'] === 'Finalizado') echo 'bg-blue-100 text-blue-800';
                    elseif ($contrato['status'] === 'Cancelado') echo 'bg-red-100 text-red-800';
                    else echo 'bg-gray-100 text-gray-800';
                    ?>">
                    <?php echo htmlspecialchars($contrato['status']); ?>
                </span>
            </div>
            <?php if (!empty($contrato['documento_path'])) : ?>
                <div>
                    <p class="font-medium text-gray-500">Documento Anexo</p>
                    <a href="<?php echo BASE_URL; ?>/contratos/download/<?php echo htmlspecialchars($contrato['documento_path']); ?>" target="_blank" class="text-sky-600 hover:underline flex items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd" />
                        </svg>
                        Baixar Documento
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Coluna de Vigência e Objeto -->
    <div class="lg:col-span-2 bg-white p-6 rounded-lg shadow-md space-y-6">
        <div>
            <h3 class="text-xl font-semibold mb-2">Vigência</h3>
            <div class="flex items-center space-x-8 border p-4 rounded-lg bg-gray-50">
                <div>
                    <p class="text-sm font-medium text-gray-500">Data de Início</p>
                    <p class="text-lg font-semibold text-gray-800"><?php echo date('d/m/Y', strtotime($contrato['data_inicio'])); ?></p>
                </div>
                <div class="text-gray-300">&rarr;</div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Data de Término</p>
                    <p class="text-lg font-semibold text-red-600"><?php echo $contrato['vencimento'] ? date('d/m/Y', strtotime($contrato['vencimento'])) : 'Indeterminado'; ?></p>
                </div>
            </div>
        </div>

        <div>
            <h3 class="text-xl font-semibold mb-2">Objeto do Contrato</h3>
            <div class="border p-4 rounded-lg bg-gray-50 text-gray-700">
                <p><?php echo nl2br(htmlspecialchars($contrato['objeto'])); ?></p>
            </div>
        </div>

        <!-- Histórico de Aditivos -->
        <div>
            <h3 class="text-xl font-semibold mb-2">Histórico de Alterações e Aditivos</h3>
            <div class="border rounded-lg bg-gray-50">
                <div class="p-4">
                    <?php if (empty($aditivos)) : ?>
                        <p class="text-gray-500 text-center">Nenhum aditivo registrado para este contrato.</p>
                    <?php else : ?>
                        <ul class="space-y-4">
                            <?php foreach ($aditivos as $aditivo) : ?>
                                <li class="p-3 border-b last:border-b-0">
                                    <div class="flex justify-between items-center">
                                        <div>
                                            <p class="font-semibold text-gray-800"><?php echo htmlspecialchars($aditivo['tipo_aditivo']); ?> - <span class="font-normal text-gray-600"><?php echo date('d/m/Y', strtotime($aditivo['data_aditivo'])); ?></span></p>
                                            <p class="text-sm text-gray-600 mt-1"><?php echo nl2br(htmlspecialchars($aditivo['descricao'])); ?></p>
                                            <div class="flex gap-4 mt-2 text-xs">
                                                <?php if ($aditivo['valor_alteracao']) : ?>
                                                    <p><strong>Valor:</strong> R$ <?php echo number_format($aditivo['valor_alteracao'], 2, ',', '.'); ?></p>
                                                <?php endif; ?>
                                                <?php if ($aditivo['novo_vencimento']) : ?>
                                                    <p><strong>Novo Venc.:</strong> <?php echo date('d/m/Y', strtotime($aditivo['novo_vencimento'])); ?></p>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-3 flex-shrink-0 ml-4">
                                            <?php if (!empty($aditivo['documento_path'])) : ?>
                                                <a href="<?php echo BASE_URL; ?>/contratos/download/<?php echo htmlspecialchars($aditivo['documento_path']); ?>" target="_blank" class="text-sky-600 hover:text-sky-800" title="Baixar Documento do Aditivo">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                    </svg>
                                                </a>
                                            <?php endif; ?>
                                            <button data-aditivo-id="<?php echo $aditivo['id']; ?>" class="edit-aditivo-btn text-indigo-600 hover:text-indigo-900" title="Editar Aditivo">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                    <path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z" />
                                                    <path fill-rule="evenodd" d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                            <a href="<?php echo BASE_URL; ?>/contratos/excluirAditivo/<?php echo $aditivo['id']; ?>" class="text-red-600 hover:text-red-900" onclick="return confirm('Atenção! Excluir este aditivo irá reverter as alterações de valor e/ou vencimento no contrato principal. Deseja continuar?');" title="Excluir Aditivo">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                </svg>
                                            </a>
                                        </div>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
                <div class="p-4 bg-gray-100 border-t text-center">
                    <button id="open-aditivo-modal-btn" class="px-4 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg shadow-md hover:bg-blue-700 transition">
                        + Adicionar Aditivo
                    </button>
                </div>
            </div>
        </div>

    </div>
</div>

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
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('form-contrato-modal');
        if (!modal) return;

        const modalTitle = document.getElementById('modal-title');
        const modalContent = document.getElementById('modal-content');
        const editBtn = document.querySelector('.open-edit-modal-btn');
        const closeBtn = document.getElementById('close-modal-btn');

        const openModal = () => modal.classList.remove('hidden');
        const closeModal = () => {
            modal.classList.add('hidden');
            modalContent.innerHTML = ''; // Limpa o conteúdo ao fechar
        };

        const openAjaxModal = async (url, title) => {
            modalTitle.innerText = title;
            modalContent.innerHTML = '<p class="text-center">Carregando...</p>';
            openModal();

            try {
                const response = await fetch(url);
                if (!response.ok) throw new Error('Falha ao carregar o formulário.');
                const formHtml = await response.text();
                modalContent.innerHTML = formHtml;
            } catch (error) {
                modalContent.innerHTML = `<p class="text-center text-red-500">${error.message}</p>`;
            }
        };

        if (editBtn) {
            editBtn.addEventListener('click', () => {
                const url = `<?php echo BASE_URL; ?>/contratos/getFormForEdit/${editBtn.dataset.id}`;
                openAjaxModal(url, 'Editar Contrato');
            });
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