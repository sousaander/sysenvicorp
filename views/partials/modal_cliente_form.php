<!-- Modal Genérica para Formulários de Cliente -->
<div id="form-cliente-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-10 mx-auto p-5 border w-full max-w-3xl shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex justify-between items-center border-b pb-3">
                <h3 id="modal-title" class="text-xl font-bold text-gray-900"></h3>
                <button id="close-client-form-modal-btn" class="text-gray-400 hover:text-gray-600">
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
    // Este script será incluído em todas as páginas que precisam do modal de cliente
    document.addEventListener('DOMContentLoaded', function() {
        const clientFormModal = document.getElementById('form-cliente-modal');
        const clientFormModalTitle = document.getElementById('modal-title');
        const clientFormModalContent = document.getElementById('modal-content');
        const closeClientFormModalBtn = document.getElementById('close-client-form-modal-btn');

        const openClientFormModal = () => clientFormModal.classList.remove('hidden');
        const closeClientFormModal = () => {
            clientFormModal.classList.add('hidden');
            clientFormModalContent.innerHTML = ''; // Limpa o conteúdo ao fechar
        };

        // Função para inicializar os scripts específicos do formulário de cliente (abas, etc.)
        const initializeFormScripts = () => {
            const form = document.getElementById('cliente-form');
            if (!form) return;

            const tabs = form.querySelectorAll('.tab-button');
            const contents = form.querySelectorAll('.tab-content');

            tabs.forEach(tab => {
                tab.addEventListener('click', () => {
                    tabs.forEach(t => {
                        t.classList.remove('border-sky-500', 'text-sky-600');
                        t.classList.add('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
                    });
                    contents.forEach(c => c.classList.add('hidden'));

                    tab.classList.add('border-sky-500', 'text-sky-600');
                    tab.classList.remove('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
                    const contentId = tab.getAttribute('data-tab');
                    const activeContent = form.querySelector('#' + contentId);
                    if (activeContent) {
                        activeContent.classList.remove('hidden');
                    }
                });
            });

            // Lógica para alternar campos PF/PJ
            const tipoClienteRadios = form.querySelectorAll('input[name="tipo_cliente"]');
            const nomeLabel = form.querySelector('label[for="nome"]');
            const nomeFantasiaDiv = form.querySelector('#nome_fantasia').parentElement;
            const cnpjCpfLabel = form.querySelector('label[for="cnpj_cpf"]');
            const rgDiv = form.querySelector('#rg').parentElement;
            const dataNascimentoDiv = form.querySelector('#data_nascimento').parentElement;

            function togglePfPjFields() {
                const tipo = form.querySelector('input[name="tipo_cliente"]:checked').value;
                if (tipo === 'Fisica') {
                    nomeLabel.innerHTML = 'Nome Completo <span class="text-red-500">*</span>';
                    cnpjCpfLabel.innerHTML = 'CPF <span class="text-red-500">*</span>';
                    nomeFantasiaDiv.classList.add('hidden');
                    rgDiv.classList.remove('hidden');
                    dataNascimentoDiv.classList.remove('hidden');
                } else { // Juridica
                    nomeLabel.innerHTML = 'Razão Social <span class="text-red-500">*</span>';
                    cnpjCpfLabel.innerHTML = 'CNPJ <span class="text-red-500">*</span>';
                    nomeFantasiaDiv.classList.remove('hidden');
                    rgDiv.classList.add('hidden');
                    dataNascimentoDiv.classList.add('hidden');
                }
            }

            tipoClienteRadios.forEach(radio => {
                radio.addEventListener('change', togglePfPjFields);
            });
            togglePfPjFields(); // Executa na inicialização para definir o estado correto

            // Lógica para IE Isento
            const ieIsentoCheckbox = form.querySelector('#ie_isento');
            const ieInput = form.querySelector('#inscricao_estadual');
            if (ieIsentoCheckbox && ieInput) {
                ieIsentoCheckbox.addEventListener('change', () => { ieInput.disabled = ieIsentoCheckbox.checked; if (ieIsentoCheckbox.checked) ieInput.value = ''; });
                ieInput.disabled = ieIsentoCheckbox.checked;
            }
        };

        // Função para adicionar a lógica de busca de CEP e CNPJ
        const initializeApiLookups = () => {
            const form = document.getElementById('cliente-form');
            if (!form) return;

            const cepInput = form.querySelector('#cep');
            const buscarCepBtn = form.querySelector('#buscar-cep-btn');
            const cnpjInput = form.querySelector('#cnpj_cpf');
            const buscarCnpjBtn = form.querySelector('#buscar-cnpj-btn');

            // --- LÓGICA DE BUSCA DE CEP (ViaCEP) ---
            if (buscarCepBtn && cepInput) {
                buscarCepBtn.addEventListener('click', async function() {
                    const cep = cepInput.value.replace(/\D/g, '');
                    if (cep.length !== 8) return;

                    cepInput.disabled = true;
                    cepInput.value = "Buscando...";

                    try {
                        const response = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
                        const data = await response.json();

                        if (data.erro) {
                            alert('CEP não encontrado.');
                            cepInput.value = cep;
                        } else {
                            form.querySelector('#logradouro').value = data.logradouro;
                            form.querySelector('#bairro').value = data.bairro;
                            form.querySelector('#cidade').value = data.localidade;
                            form.querySelector('#estado').value = data.uf;
                            form.querySelector('#numero').focus();
                            cepInput.value = cep; // Restaura o valor limpo
                        }
                    } catch (error) {
                        alert('Falha ao buscar o CEP.');
                    } finally {
                        cepInput.disabled = false;
                        if (cepInput.value === "Buscando...") cepInput.value = cep;
                    }
                });
            }

            // --- LÓGICA DE BUSCA DE CNPJ (BrasilAPI via nosso backend) ---
            if (buscarCnpjBtn && cnpjInput) {
                buscarCnpjBtn.addEventListener('click', async function() {
                    const cnpj = cnpjInput.value.replace(/\D/g, '');
                    if (cnpj.length !== 14) return; // Apenas para CNPJ

                    cnpjInput.disabled = true;
                    cnpjInput.value = "Buscando CNPJ...";

                    try {
                        const response = await fetch(`<?php echo BASE_URL; ?>/clientes/consultarCnpj/${cnpj}`);
                        const data = await response.json();

                        if (!response.ok) {
                            throw new Error(data.message || 'CNPJ não encontrado ou inválido.');
                        }

                        // Garante que o formulário esteja no modo "Pessoa Jurídica"
                        const pjRadio = form.querySelector('#tipo_cliente_juridica');
                        if (pjRadio && !pjRadio.checked) {
                            pjRadio.click(); // Simula o clique para acionar o togglePfPjFields()
                        }

                        form.querySelector('#nome').value = data.razao_social || '';
                        // Garante que o campo nome_fantasia exista antes de tentar preenchê-lo
                        const nomeFantasiaInput = form.querySelector('#nome_fantasia');
                        if (nomeFantasiaInput) nomeFantasiaInput.value = data.nome_fantasia || '';
                        // Preenche o endereço se disponível na resposta da API
                        if (data.cep) form.querySelector('#cep').value = data.cep;
                        if (data.logradouro) form.querySelector('#logradouro').value = data.logradouro;
                        if (data.numero) form.querySelector('#numero').value = data.numero;
                        if (data.bairro) form.querySelector('#bairro').value = data.bairro;
                        if (data.municipio) form.querySelector('#cidade').value = data.municipio;
                        if (data.uf) form.querySelector('#estado').value = data.uf;

                    } catch (error) {
                        alert(error.message);
                    } finally {
                        cnpjInput.disabled = false;
                        cnpjInput.value = cnpj; // Restaura o valor original
                    }
                });
            }
        };

        // Função global para abrir o modal do formulário de cliente (novo ou edição)
        window.openClientFormModal = async (clientId = null) => {
            clientFormModalTitle.innerText = clientId ? 'Editar Cliente' : 'Novo Cliente / Lead';
            clientFormModalContent.innerHTML = '<p class="text-center">Carregando formulário...</p>';
            openClientFormModal();

            try {
                // Adiciona um parâmetro '_=' com o timestamp atual para evitar o cache do navegador ("cache busting")
                const cacheBuster = `_=${new Date().getTime()}`;
                const url = clientId ? `<?php echo BASE_URL; ?>/clientes/getFormForEdit/${clientId}?${cacheBuster}` : `<?php echo BASE_URL; ?>/clientes/getFormForNew?${cacheBuster}`;
                const response = await fetch(url);
                if (!response.ok) throw new Error('Falha ao carregar o formulário.');
                clientFormModalContent.innerHTML = await response.text();
                // Após carregar o HTML, inicializa os scripts das abas e outros.
                initializeFormScripts();
                // E também inicializa as buscas de API.
                initializeApiLookups();
            } catch (error) {
                clientFormModalContent.innerHTML = `<p class="text-center text-red-500">${error.message}</p>`;
            }
        };

        // Listeners para fechar o modal
        closeClientFormModalBtn.addEventListener('click', closeClientFormModal);
        clientFormModal.addEventListener('click', function(event) {
            if (event.target === clientFormModal) {
                closeClientFormModal();
            }
        });

        // Delegação de evento para o botão "Cancelar" dentro do formulário carregado dinamicamente
        clientFormModal.addEventListener('click', function(event) {
            if (event.target && event.target.id === 'cancel-form-btn') {
                closeClientFormModal();
            }
        });

        // --- DELEGAÇÃO DE EVENTOS PARA O FORMULÁRIO CARREGADO DINAMICAMENTE ---

        // Listener unificado para eventos de 'click' dentro do modal
        clientFormModal.addEventListener('click', async function(event) {
            // Botão "Salvar" da Nova Categoria (se clicado)
            if (event.target && event.target.id === 'salvar-nova-categoria-btn') {
                event.preventDefault();

                const novaCategoriaInput = document.getElementById('nova-categoria-nome');
                const nomeNovaCategoria = novaCategoriaInput.value.trim();

                if (!nomeNovaCategoria) {
                    alert('Por favor, digite o nome da nova categoria.');
                    return;
                }

                const formData = new FormData();
                formData.append('nome', nomeNovaCategoria);

                try {
                    const response = await fetch('<?php echo BASE_URL; ?>/clientes/addCategoria', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    const result = await response.json();

                    if (result.success) {
                        const categoriaSelect = document.getElementById('categoria_segmento');
                        const novaCategoriaDiv = document.getElementById('nova-categoria-div');

                        // O 'value' da nova opção deve ser o ID, não o nome.
                        const newOption = new Option(result.data.nome, result.data.id, true, true);
                        categoriaSelect.insertBefore(newOption, categoriaSelect.options[categoriaSelect.options.length - 1]);

                        novaCategoriaDiv.classList.add('hidden');
                        novaCategoriaInput.value = '';
                    } else {
                        alert('Erro: ' + result.message);
                    }
                } catch (error) {
                    alert('Ocorreu um erro de comunicação ao salvar a categoria.');
                }
            }

            // Botão "Cancelar" da Nova Categoria
            if (event.target && event.target.id === 'cancelar-nova-categoria-btn') {
                document.getElementById('nova-categoria-div').classList.add('hidden');
                document.getElementById('categoria_segmento').value = ''; // Reseta a seleção
            }

            // Botão "Salvar" do Novo Segmento
            if (event.target && event.target.id === 'salvar-novo-segmento-btn') {
                event.preventDefault();
                const nomeNovoSegmento = document.getElementById('novo-segmento-nome').value.trim();
                const categoriaId = document.getElementById('categoria_segmento').value;

                if (!nomeNovoSegmento || !categoriaId) {
                    alert('Nome do segmento e categoria são necessários.');
                    return;
                }

                const formData = new FormData();
                formData.append('nome', nomeNovoSegmento);
                formData.append('categoria_id', categoriaId);

                try {
                    const response = await fetch('<?php echo BASE_URL; ?>/clientes/addSegmentoAjax', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    const result = await response.json();

                    if (result.success) {
                        const segmentoSelect = document.getElementById('segmento');
                        const novoSegmentoDiv = document.getElementById('novo-segmento-div');

                        // Adiciona a nova opção, seleciona e esconde o campo de input
                        const newOption = new Option(result.data.nome, result.data.nome, true, true);
                        segmentoSelect.insertBefore(newOption, segmentoSelect.options[segmentoSelect.options.length - 1]);

                        novoSegmentoDiv.classList.add('hidden');
                        document.getElementById('novo-segmento-nome').value = '';
                    } else {
                        alert('Erro: ' + (result.message || 'Não foi possível salvar o segmento.'));
                    }
                } catch (error) {
                    alert('Ocorreu um erro de comunicação ao salvar o segmento.');
                }
            }

            // Botão "Cancelar" do Novo Segmento
            if (event.target && event.target.id === 'cancelar-novo-segmento-btn') {
                document.getElementById('novo-segmento-div').classList.add('hidden');
                document.getElementById('segmento').value = '';
            }
        });

        // Listener unificado para eventos de 'change' dentro do modal
        clientFormModal.addEventListener('change', async function(event) {
            // Quando o select de Categoria muda
            if (event.target && event.target.id === 'categoria_segmento') {
                const categoriaSelect = event.target;
                const categoriaId = categoriaSelect.value;
                const segmentoSelect = document.getElementById('segmento');
                const novaCategoriaDiv = document.getElementById('nova-categoria-div');
                const novoSegmentoDiv = document.getElementById('novo-segmento-div');

                // Esconde os campos de "novo"
                novaCategoriaDiv.classList.add('hidden');
                novoSegmentoDiv.classList.add('hidden');

                // Se for para adicionar nova categoria
                if (categoriaId === '--add-new--') {
                    novaCategoriaDiv.classList.remove('hidden');
                    document.getElementById('nova-categoria-nome').focus();
                    segmentoSelect.innerHTML = '<option value="">Selecione uma categoria</option>';
                    segmentoSelect.disabled = true;
                    return;
                }

                // Limpa e desabilita o select de segmento enquanto carrega
                segmentoSelect.innerHTML = '<option value="">Carregando...</option>';
                segmentoSelect.disabled = true;

                if (!categoriaId) {
                    segmentoSelect.innerHTML = '<option value="">Selecione uma categoria</option>';
                    return;
                }

                // Busca os segmentos via AJAX
                try {
                    const response = await fetch(`<?php echo BASE_URL; ?>/clientes/getSegmentosAjax/${categoriaId}`);
                    const result = await response.json();

                    if (result.success) {
                        segmentoSelect.innerHTML = '<option value="">Selecione um segmento...</option>';
                        result.data.forEach(segmento => {
                            const option = new Option(segmento.nome, segmento.nome);
                            segmentoSelect.add(option);
                        });
                        segmentoSelect.add(new Option('-- Cadastrar Novo Segmento --', '--add-new--'));
                        segmentoSelect.disabled = false;
                    } else {
                        segmentoSelect.innerHTML = '<option value="">Erro ao carregar</option>';
                    }
                } catch (error) {
                    segmentoSelect.innerHTML = '<option value="">Falha na comunicação</option>';
                }
            }

            // Quando o select de Segmento muda
            if (event.target && event.target.id === 'segmento' && event.target.value === '--add-new--') {
                document.getElementById('novo-segmento-div').classList.remove('hidden');
                document.getElementById('novo-segmento-nome').focus();
            }
        });

        // Delegação de evento para a submissão do formulário de cliente via AJAX
        clientFormModal.addEventListener('submit', async function(event) {
            // Verifica se o evento de submit veio do formulário que nos interessa
            if (event.target && event.target.id === 'cliente-form') {
                event.preventDefault(); // Impede a submissão tradicional

                const form = event.target;
                const formData = new FormData(form);
                const submitButton = form.querySelector('button[type="submit"]');
                submitButton.disabled = true;
                submitButton.textContent = 'Salvando...';

                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest' // Essencial para o controller identificar como AJAX
                        }
                    });

                    const result = await response.json();

                    if (result.success) {
                        closeClientFormModal();
                        window.location.reload(); // Recarrega a página para ver as atualizações.
                    } else {
                        alert('Erro: ' + (result.message || 'Não foi possível salvar o cliente.'));
                    }
                } catch (error) {
                    alert('Ocorreu um erro de comunicação. Tente novamente.');
                } finally {
                    submitButton.disabled = false;
                    submitButton.textContent = submitButton.dataset.originalText || 'Salvar Cliente';
                }
            }
        });

    });
</script>