<h2 class="text-2xl font-bold mb-4">Módulo Gestão de Contratos</h2>
<p class="mb-6 text-gray-600">Acompanhamento centralizado de todos os contratos ativos (clientes, fornecedores, parceiros), prazos e pendências.</p>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <!-- Card 1: Total Vigentes -->
    <div class="bg-white p-6 rounded-lg shadow-lg border-l-4 border-indigo-500">
        <h3 class="font-semibold text-gray-500">Total de Contratos Vigentes</h3>
        <p class="text-3xl font-bold text-indigo-600"><?php echo $totalVigentes ?? 0; ?></p>
        <p class="text-sm text-gray-400 mt-2">Clientes e Fornecedores</p>
    </div>
    <!-- Card 2: Vencendo 30 Dias -->
    <div class="bg-white p-6 rounded-lg shadow-lg border-l-4 border-red-500">
        <h3 class="font-semibold text-gray-500">Vencendo nos Próximos 30 Dias</h3>
        <p class="text-3xl font-bold text-red-600"><?php echo $vencendo30dias ?? 0; ?></p>
        <p class="text-sm text-gray-400 mt-2">Ação de renovação obrigatória</p>
    </div>
    <!-- Card 3: Pendências Documentais -->
    <div class="bg-white p-6 rounded-lg shadow-lg border-l-4 border-yellow-500">
        <h3 class="font-semibold text-gray-500">Pendência de Assinatura/Docs</h3>
        <p class="text-3xl font-bold text-yellow-600"><?php echo $comPendenciaDocs ?? 0; ?></p>
        <p class="text-sm text-gray-400 mt-2">Aguardando legalização</p>
    </div>
    <!-- Card 4: Valor Total Anual -->
    <div class="bg-white p-6 rounded-lg shadow-lg border-l-4 border-green-500">
        <h3 class="font-semibold text-gray-500">Valor Total Anual (Previsto)</h3>
        <p class="text-2xl font-bold text-green-600"><?php echo $valorTotalAnual ?? 'R$ 0'; ?></p>
        <p class="text-sm text-gray-400 mt-2">Receitas e despesas contratuais</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- Lista de Contratos Críticos (Tabela Principal) -->
    <div class="lg:col-span-2 bg-white p-6 rounded-lg shadow-md">
        <h3 class="text-lg font-semibold mb-4 border-b pb-2 flex justify-between items-center">
            Lista de Contratos
            <button id="open-modal-btn" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">
                + Novo Contrato
            </button>
        </h3>

        <div class="overflow-x-auto">
            <?php if (!empty($contratos)) : ?>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contratante / Contratada</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valor</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vencimento</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($contratos as $contrato) : ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($contrato['tipo']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    <?php echo htmlspecialchars($contrato['parteContratada'] ?? 'N/A'); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">R$ <?php echo number_format($contrato['valor'], 2, ',', '.'); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $contrato['vencimento'] ? date('d/m/Y', strtotime($contrato['vencimento'])) : 'N/A'; ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    <?php
                                    if ($contrato['status'] === 'Em Vigência') echo 'bg-green-100 text-green-800';
                                    elseif ($contrato['status'] === 'Pendência Assinatura') echo 'bg-yellow-100 text-yellow-800';
                                    elseif ($contrato['status'] === 'Finalizado') echo 'bg-blue-100 text-blue-800';
                                    elseif ($contrato['status'] === 'Cancelado') echo 'bg-red-100 text-red-800';
                                    else echo 'bg-gray-100 text-gray-800'; // Default
                                    ?>">
                                        <?php echo htmlspecialchars($contrato['status']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <?php if (!empty($contrato['documento_path'])) : ?>
                                        <a href="<?php echo BASE_URL; ?>/contratos/download/<?php echo htmlspecialchars($contrato['documento_path']); ?>" target="_blank" title="Baixar Documento" class="text-gray-500 hover:text-sky-600 mr-3 inline-block align-middle">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                                            </svg>
                                        </a>
                                    <?php endif; ?>
                                    <button data-id="<?php echo $contrato['id']; ?>" class="open-edit-modal-btn text-indigo-600 hover:text-indigo-900 mr-3">
                                        Editar
                                    </button>
                                    <a href="<?php echo BASE_URL; ?>/contratos/excluir/<?php echo $contrato['id']; ?>" class="text-red-600 hover:text-red-900" onclick="return confirm('Tem certeza que deseja excluir este contrato?');">Excluir</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <p class="text-gray-500 text-center py-4">Nenhum contrato encontrado.</p>
            <?php endif; ?>
        </div>

        <!-- Navegação da Paginação -->
        <div class="mt-4 flex justify-end items-center">
            <?php if ($totalPaginas > 1) : ?>
                <nav class="flex items-center justify-end space-x-2">
                    <?php
                    $queryString = http_build_query(array_merge($filtros, ['page' => '']));
                    ?>
                    <a href="<?php echo BASE_URL; ?>/contratos?<?php echo $queryString . ($paginaAtual - 1); ?>" class="<?php echo ($paginaAtual <= 1) ? 'pointer-events-none text-gray-400' : 'text-gray-600 hover:text-indigo-600'; ?> px-3 py-1 rounded-md text-sm font-medium">
                        Anterior
                    </a>

                    <?php for ($i = 1; $i <= $totalPaginas; $i++) : ?>
                        <a href="<?php echo BASE_URL; ?>/contratos?<?php echo $queryString . $i; ?>" class="<?php echo ($i == $paginaAtual) ? 'bg-indigo-500 text-white' : 'bg-white text-gray-600 hover:bg-gray-100'; ?> px-3 py-1 rounded-md text-sm font-medium border">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>

                    <a href="<?php echo BASE_URL; ?>/contratos?<?php echo $queryString . ($paginaAtual + 1); ?>" class="<?php echo ($paginaAtual >= $totalPaginas) ? 'pointer-events-none text-gray-400' : 'text-gray-600 hover:text-indigo-600'; ?> px-3 py-1 rounded-md text-sm font-medium">
                        Próxima
                    </a>
                </nav>
            <?php endif; ?>
        </div>
    </div>

    <!-- Ações e Alertas -->
    <div class="bg-white p-6 rounded-lg shadow-md lg:col-span-1">
        <h3 class="text-lg font-semibold mb-4">Ações e Alertas Contratuais</h3>

        <label for="filtroTipo" class="block text-sm font-medium text-gray-700 mb-1">Filtrar por Tipo</label>
        <select id="filtroTipo" class="w-full mb-4 border border-gray-300 rounded-md shadow-sm p-2 focus:ring-indigo-500 focus:border-indigo-500">
            <option>Todos</option>
            <option>Cliente</option>
            <option>Fornecedor</option>
            <option>Parceiro</option>
        </select>

        <button id="open-alerta-modal-btn" class="w-full mb-3 inline-flex items-center justify-center px-4 py-2 border border-orange-300 text-base font-medium rounded-md shadow-sm text-orange-700 bg-orange-50 hover:bg-orange-100 focus:outline-none">
            <i class="fas fa-bell mr-2"></i> Enviar Alerta de Renovação
        </button>

        <button id="open-upload-modal-btn" class="w-full mb-3 inline-flex items-center justify-center px-4 py-2 border border-gray-300 text-base font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none">
            <i class="fas fa-upload mr-2"></i> Upload de Documento (PDF)
        </button>

        <div class="mt-6 pt-4 border-t">
            <p class="text-sm text-gray-500">A documentação completa deve ser arquivada digitalmente e estar em conformidade com as cláusulas legais.</p>
        </div>
    </div>
</div>

<!-- Div oculta para armazenar o template do formulário de novo contrato -->
<div id="novo-contrato-form-template" class="hidden">
    <?php
    // Pré-renderiza o formulário de criação uma vez
    $isEdit = false;
    $contrato = null; // Garante que o formulário seja de criação
    // As variáveis $clientes, $fornecedores e $projetos já estão disponíveis nesta view
    require ROOT_PATH . '/views/contratos/form.php';
    ?>
</div>

<!-- Modal Genérica para Formulários -->
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
                <!-- O conteúdo do formulário (novo ou edição) será carregado aqui -->
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('form-contrato-modal');
        const modalTitle = document.getElementById('modal-title');
        const modalContent = document.getElementById('modal-content');
        const openNewBtn = document.getElementById('open-modal-btn');
        const closeBtn = document.getElementById('close-modal-btn');
        const editBtns = document.querySelectorAll('.open-edit-modal-btn');
        const openAlertaBtn = document.getElementById('open-alerta-modal-btn');
        const openUploadBtn = document.getElementById('open-upload-modal-btn');

        const openModal = () => modal.classList.remove('hidden');
        const closeModal = () => {
            modal.classList.add('hidden');
            modalContent.innerHTML = ''; // Limpa o conteúdo ao fechar
        };

        // Abrir modal para NOVO contrato
        openNewBtn.addEventListener('click', () => {
            modalTitle.innerText = 'Novo Contrato';
            // Clona o formulário pré-renderizado da div oculta para o modal
            modalContent.innerHTML = document.getElementById('novo-contrato-form-template').innerHTML;
            openModal();
        });

        // Abrir modal para EDITAR contrato
        editBtns.forEach(btn => {
            btn.addEventListener('click', async () => {
                const url = `<?php echo BASE_URL; ?>/contratos/getFormForEdit/${btn.dataset.id}`;
                openAjaxModal(url, 'Editar Contrato');
            });
        });

        // Função genérica para abrir modal com conteúdo via AJAX
        const openAjaxModal = async (url, title) => {
            modalTitle.innerText = title;
            modalContent.innerHTML = '<p class="text-center">Carregando...</p>';
            openModal();

            try {
                const response = await fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                if (!response.ok) throw new Error('Falha ao carregar o conteúdo.');
                const contentHtml = await response.text();
                modalContent.innerHTML = contentHtml;

                // Re-executa scripts específicos do conteúdo carregado no modal
                const scripts = modalContent.querySelectorAll('script');
                scripts.forEach(script => {
                    const newScript = document.createElement('script');
                    if (script.src) {
                        newScript.src = script.src;
                    } else {
                        newScript.textContent = script.textContent;
                    }
                    // Adiciona um atributo para evitar re-execução infinita se o script já estiver na página
                    newScript.setAttribute('data-loaded-by-modal', 'true');
                    document.body.appendChild(newScript).parentNode.removeChild(newScript);
                });

                // Remove o script original do modal para evitar duplicação no DOM
                // Isso é importante para não deixar o DOM poluído com scripts inativos
                Array.from(modalContent.querySelectorAll('script')).forEach(oldScript => {
                    if (oldScript.parentNode) {
                        oldScript.parentNode.removeChild(oldScript);
                    }
                });
            } catch (error) {
                modalContent.innerHTML = `<p class="text-center text-red-500">${error.message}</p>`;
            }
        };

        // Abrir modal para ENVIAR ALERTA
        if (openAlertaBtn) {
            openAlertaBtn.addEventListener('click', () => openAjaxModal('<?php echo BASE_URL; ?>/contratos/enviarAlerta', 'Enviar Alerta de Renovação'));
        }

        // Abrir modal para UPLOAD DE DOCUMENTO
        if (openUploadBtn) {
            openUploadBtn.addEventListener('click', () => openAjaxModal('<?php echo BASE_URL; ?>/contratos/uploadDocumento', 'Upload de Documento (PDF)'));
        }

        closeBtn.addEventListener('click', closeModal);

        // Fecha o modal se clicar fora dele
        modal.addEventListener('click', function(event) {
            if (event.target === modal) {
                closeModal();
            }
        });

        // Adiciona um único listener de evento no modal para lidar com o botão "Cancelar"
        // que é carregado dinamicamente. Isso se chama "delegação de eventos".
        modal.addEventListener('click', function(event) {
            if (event.target && event.target.id === 'cancel-form-btn') {
                closeModal();
            }
        });

        // Verifica se a URL tem o parâmetro para abrir o modal
        // const urlParams = new URLSearchParams(window.location.search);
        // if (urlParams.get('action') === 'novo') {
        //     openNewBtn.click(); // Simula o clique para abrir o modal de novo
        // }
    });
</script>