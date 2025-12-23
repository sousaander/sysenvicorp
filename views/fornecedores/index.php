<div class="flex justify-between items-center mb-6">
    <div>
        <h2 class="text-2xl font-bold"><?php echo htmlspecialchars($pageTitle); ?></h2>
        <p class="text-gray-600">Gerencie sua base de fornecedores, contratos e conformidade.</p>
    </div>
    <div class="flex items-center gap-2">
        <button id="export-pdf-btn" class="px-3 py-2 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50">Exportar PDF</button>
        <button id="export-excel-btn" class="px-3 py-2 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50">Exportar Excel</button>
        <!-- O link foi trocado por um botão que abrirá o modal -->
        <button id="open-modal-btn" class="px-4 py-2 text-sm font-semibold text-white bg-sky-600 rounded-lg shadow-md hover:bg-sky-700 transition">
            + Novo Fornecedor
        </button>
    </div>
</div>

<!-- Cards de Resumo -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <div class="bg-white p-6 rounded-lg shadow-lg border-l-4 border-blue-500">
        <h3 class="font-semibold text-gray-500">Fornecedores Ativos</h3>
        <p class="text-3xl font-bold text-blue-600"><?php echo $totalAtivos ?? 0; ?></p>
    </div>
    <div class="bg-white p-6 rounded-lg shadow-lg border-l-4 border-red-500">
        <h3 class="font-semibold text-gray-500">Contratos a Vencer (30d)</h3>
        <p class="text-3xl font-bold text-red-600"><?php echo $contratoVencer30 ?? 0; ?></p>
    </div>
    <div class="bg-white p-6 rounded-lg shadow-lg border-l-4 border-yellow-500">
        <h3 class="font-semibold text-gray-500">Pendência de Documentos</h3>
        <p class="text-3xl font-bold text-yellow-600"><?php echo $pendenciaDocs ?? 0; ?></p>
    </div>
    <div class="bg-white p-6 rounded-lg shadow-lg border-l-4 border-orange-500">
        <h3 class="font-semibold text-gray-500">Avaliação de Risco Alta</h3>
        <p class="text-3xl font-bold text-orange-600"><?php echo $riscoAlto ?? 0; ?></p>
    </div>
</div>

<!-- Formulário de Busca e Filtros -->
<div class="bg-white p-4 rounded-lg shadow-md mb-6">
    <form action="<?php echo BASE_URL; ?>/fornecedores" method="GET">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="md:col-span-2">
                <label for="busca" class="sr-only">Buscar</label>
                <input type="text" name="busca" id="busca" class="w-full border-gray-300 rounded-lg shadow-sm p-2" placeholder="Buscar por Nome, CNPJ ou Cidade..." value="<?php echo htmlspecialchars($filtros['busca'] ?? ''); ?>">
            </div>
            <div>
                <label for="status" class="sr-only">Status</label>
                <select name="status" id="status" class="w-full border-gray-300 rounded-lg shadow-sm p-2">
                    <option value="">Todos os Status</option>
                    <option value="Ativo" <?php echo (($filtros['status'] ?? '') === 'Ativo') ? 'selected' : ''; ?>>Ativo</option>
                    <option value="Inativo" <?php echo (($filtros['status'] ?? '') === 'Inativo') ? 'selected' : ''; ?>>Inativo</option>
                    <option value="Em Homologação" <?php echo (($filtros['status'] ?? '') === 'Em Homologação') ? 'selected' : ''; ?>>Em Homologação</option>
                </select>
            </div>
            <div>
                <button type="submit" class="w-full bg-sky-600 text-white px-4 py-2 rounded-lg hover:bg-sky-700">Filtrar</button>
            </div>
        </div>
    </form>
</div>

<!-- Tabela de Fornecedores -->
<div class="bg-white p-6 rounded-lg shadow-md">
    <h3 class="text-lg font-semibold mb-4 border-b pb-2">Lista de Fornecedores</h3>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Razão Social</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">CNPJ</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cidade</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (!empty($fornecedores)) : ?>
                    <?php foreach ($fornecedores as $fornecedor) : ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($fornecedor['nome']); ?></td>
                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($fornecedor['cnpj']); ?></td>
                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($fornecedor['cidade']); ?></td>
                            <td class="px-4 py-4 whitespace-nowrap text-center">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                <?php
                                if ($fornecedor['status'] === 'Ativo') echo 'bg-green-100 text-green-800';
                                elseif ($fornecedor['status'] === 'Inativo') echo 'bg-red-100 text-red-800';
                                else echo 'bg-yellow-100 text-yellow-800';
                                ?>"><?php echo htmlspecialchars($fornecedor['status']); ?></span>
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="<?php echo BASE_URL; ?>/fornecedores/detalhe/<?php echo $fornecedor['id']; ?>" class="text-indigo-600 hover:text-indigo-900">Detalhes</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="5" class="text-center py-10 text-gray-500">Nenhum fornecedor encontrado com os filtros aplicados.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Paginação -->
    <!-- (A lógica de paginação pode ser adicionada aqui se necessário) -->
</div>

<!-- Modal Genérica para Formulários -->
<div id="form-fornecedor-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-10 mx-auto p-5 border w-full max-w-4xl shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex justify-between items-center border-b pb-3">
                <h3 id="modal-title" class="text-xl font-bold text-gray-900">Novo Fornecedor</h3>
                <button id="close-modal-btn" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <div id="modal-content" class="mt-4">
                <!-- O conteúdo do formulário será carregado aqui -->
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('form-fornecedor-modal');
        const modalTitle = document.getElementById('modal-title');
        const modalContent = document.getElementById('modal-content');
        const openNewBtn = document.getElementById('open-modal-btn');
        const closeBtn = document.getElementById('close-modal-btn');

        const openModal = () => modal.classList.remove('hidden');
        const closeModal = () => {
            modal.classList.add('hidden');
            modalContent.innerHTML = ''; // Limpa o conteúdo ao fechar
        };

        // Função para abrir o modal de NOVO fornecedor e inicializar seus scripts
        const openNewModal = async () => {
            modalTitle.innerText = 'Novo Fornecedor';
            modalContent.innerHTML = '<p class="text-center p-8">Carregando formulário...</p>';
            openModal();

            try {
                const response = await fetch('<?php echo BASE_URL; ?>/fornecedores/getFormForNew');
                if (!response.ok) throw new Error('Falha ao carregar o formulário.');
                modalContent.innerHTML = await response.text();
                initializeModalScripts(); // Chama a função para inicializar os scripts do modal
            } catch (error) {
                modalContent.innerHTML = `<p class="text-center text-red-500 p-8">${error.message}</p>`;
            }
        };

        // Abrir modal para NOVO fornecedor
        openNewBtn.addEventListener('click', openNewModal);

        // Fechar modal
        closeBtn.addEventListener('click', closeModal);
        modal.addEventListener('click', function(event) {
            if (event.target === modal) {
                closeModal();
            }
        });

        // Delegação de evento para o botão "Cancelar" dentro do modal
        modal.addEventListener('click', function(event) {
            if (event.target && event.target.id === 'cancel-form-btn') {
                closeModal();
            }
        });

        // Função para inicializar todos os scripts necessários dentro do modal
        const initializeModalScripts = () => {
            const tipoPessoaRadio = modal.querySelector('input[name="tipo_pessoa"]:checked');
            if (tipoPessoaRadio) {
                handleTipoPessoaChange(); // Configura o estado inicial
            }

            const tipoPessoaRadios = modal.querySelectorAll('input[name="tipo_pessoa"]');
            tipoPessoaRadios.forEach(radio => {
                radio.addEventListener('change', handleTipoPessoaChange);
            });

            const ieIsentoCheckbox = modal.querySelector('#ie_isento');
            if (ieIsentoCheckbox) {
                ieIsentoCheckbox.addEventListener('change', () => {
                    const ieInput = modal.querySelector('#inscricao_estadual');
                    if(ieInput) ieInput.disabled = ieIsentoCheckbox.checked;
                });
            }
        };

        // Verifica se a URL tem o parâmetro para abrir o modal
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('action') === 'novo') {
            openNewModal();
        }

        // --- Função para aplicar a máscara de CNPJ ---
        const applyCnpjMask = (inputElement) => {
            if (!inputElement) return;
            inputElement.addEventListener('input', (e) => {
                let value = e.target.value.replace(/\D/g, ''); // Remove tudo que não é dígito
                if (value.length > 14) {
                    value = value.substring(0, 14);
                }
                // Aplica a máscara XX.XXX.XXX/XXXX-XX
                if (value.length > 12) {
                    value = value.replace(/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/, '$1.$2.$3/$4-$5');
                } else if (value.length > 8) {
                    value = value.replace(/^(\d{2})(\d{3})(\d{3})(\d{1,4})$/, '$1.$2.$3/$4');
                } else if (value.length > 5) {
                    value = value.replace(/^(\d{2})(\d{3})(\d{3})$/, '$1.$2.$3');
                } else if (value.length > 2) {
                    value = value.replace(/^(\d{2})(\d{3})$/, '$1.$2');
                }
                e.target.value = value;
            });
        };

        // --- Função para aplicar a máscara de CPF ---
        const applyCpfMask = (inputElement) => {
            if (!inputElement) return;
            inputElement.addEventListener('input', (e) => {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length > 11) {
                    value = value.substring(0, 11);
                }
                value = value.replace(/(\d{3})(\d)/, '$1.$2');
                value = value.replace(/(\d{3})(\d)/, '$1.$2');
                value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
                e.target.value = value;
            });
        };

        // --- Função para gerenciar a mudança entre CNPJ e CPF ---
        const handleTipoPessoaChange = () => {
            const tipoPessoaRadio = modal.querySelector('input[name="tipo_pessoa"]:checked');
            if (!tipoPessoaRadio) return;

            const tipoPessoa = tipoPessoaRadio.value;
            const cnpjInput = modal.querySelector('#cnpj');
            const labelCnpjCpf = modal.querySelector('#label-cnpj-cpf');
            const buscarCnpjBtn = modal.querySelector('#buscar-cnpj-btn');

            if (!cnpjInput || !labelCnpjCpf || !buscarCnpjBtn) return;

            // Remove listeners antigos para evitar duplicação
            const newCnpjInput = cnpjInput.cloneNode(true);
            cnpjInput.parentNode.replaceChild(newCnpjInput, cnpjInput);
            newCnpjInput.value = ''; // Limpa o campo na troca

            if (tipoPessoa === 'Juridica') {
                labelCnpjCpf.innerHTML = 'CNPJ <span class="text-red-500">*</span>';
                newCnpjInput.placeholder = 'Digite o CNPJ';
                buscarCnpjBtn.style.display = 'inline-flex';
                applyCnpjMask(newCnpjInput);
            } else { // Física
                labelCnpjCpf.innerHTML = 'CPF <span class="text-red-500">*</span>';
                newCnpjInput.placeholder = 'Digite o CPF';
                buscarCnpjBtn.style.display = 'none'; // Esconde o botão de busca para CPF
                applyCpfMask(newCnpjInput);
            }
        };

        // --- Lógica de busca por CEP e CNPJ com DELEGAÇÃO DE EVENTOS ---
        // Adiciona um listener ao modal, que é um elemento pai persistente.
        modal.addEventListener('click', async function(event) {
            // Busca por CEP
            if (event.target && event.target.closest('#buscar-cep-btn')) {
                const cepInput = modal.querySelector('#cep');
                if (!cepInput) return;

                const cep = cepInput.value.replace(/\D/g, '');
                if (cep.length !== 8) {
                    alert('Por favor, digite um CEP válido com 8 dígitos.');
                    return;
                }

                try {
                    const response = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
                    if (!response.ok) throw new Error('CEP não encontrado.');
                    const data = await response.json();
                    if (data.erro) throw new Error('CEP não encontrado.');

                    modal.querySelector('#logradouro').value = data.logradouro || '';
                    modal.querySelector('#bairro').value = data.bairro || '';
                    modal.querySelector('#cidade').value = data.localidade || '';
                    modal.querySelector('#uf').value = data.uf || '';
                    modal.querySelector('#numero').focus();
                } catch (error) {
                    alert(`Erro ao buscar CEP: ${error.message}`);
                }
            }

            // Busca por CNPJ
            if (event.target && event.target.closest('#buscar-cnpj-btn')) {
                const buscarCnpjBtn = event.target.closest('#buscar-cnpj-btn');
                const cnpjInput = modal.querySelector('#cnpj');
                const searchIcon = modal.querySelector('#cnpj-search-icon');
                const loadingSpinner = modal.querySelector('#cnpj-loading-spinner');

                if (!cnpjInput || !searchIcon || !loadingSpinner) return;

                const cnpj = cnpjInput.value.replace(/\D/g, '');
                if (cnpj.length !== 14) {
                    alert('Por favor, digite um CNPJ válido com 14 dígitos.');
                    return;
                }

                searchIcon.classList.add('hidden');
                loadingSpinner.classList.remove('hidden');
                buscarCnpjBtn.disabled = true;

                try {
                    const response = await fetch(`https://brasilapi.com.br/api/cnpj/v1/${cnpj}`);
                    if (!response.ok) {
                        const errorData = await response.json();
                        throw new Error(errorData.message || 'CNPJ não encontrado ou inválido.');
                    }

                    const data = await response.json();

                    // Preenche os campos do formulário com os dados da API
                    modal.querySelector('#nome').value = data.razao_social || '';
                    modal.querySelector('#nome_fantasia').value = data.nome_fantasia || '';
                    modal.querySelector('#email_principal').value = data.email || '';
                    modal.querySelector('#telefone_comercial').value = data.ddd_telefone_1 || '';
                    modal.querySelector('#cep').value = (data.cep || '').replace(/\D/g, '');
                    modal.querySelector('#logradouro').value = data.logradouro || '';
                    modal.querySelector('#numero').value = data.numero || '';
                    modal.querySelector('#complemento').value = data.complemento || '';
                    modal.querySelector('#bairro').value = data.bairro || '';
                    modal.querySelector('#cidade').value = data.municipio || '';
                    modal.querySelector('#uf').value = data.uf || '';

                    // Tenta preencher a inscrição estadual se disponível
                    const ieField = modal.querySelector('#inscricao_estadual');
                    if (ieField && data.inscricoes_estaduais && data.inscricoes_estaduais.length > 0) {
                        // Pega a primeira inscrição estadual ativa, se houver
                        const ieAtiva = data.inscricoes_estaduais.find(ie => ie.ativo);
                        ieField.value = ieAtiva ? ieAtiva.inscricao_estadual : data.inscricoes_estaduais[0].inscricao_estadual;
                    }

                } catch (error) {
                    alert(`Erro ao buscar CNPJ: ${error.message}`);
                } finally {
                    searchIcon.classList.remove('hidden');
                    loadingSpinner.classList.add('hidden');
                    buscarCnpjBtn.disabled = false;
                }
            }
        });
    });
</script>
