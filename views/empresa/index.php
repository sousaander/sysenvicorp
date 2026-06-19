<div class="flex justify-between items-center mb-6">
    <div>
        <h2 class="text-2xl font-bold"><?php echo htmlspecialchars($pageTitle); ?></h2>
        <p class="text-gray-600">Gerencie os dados cadastrais e o certificado digital da sua empresa.</p>
    </div>
</div>

<div class="bg-white p-8 rounded-lg shadow-xl max-w-4xl mx-auto">
    <form action="<?php echo BASE_URL; ?>/empresa/salvar" method="POST" enctype="multipart/form-data">

        <h3 class="text-lg font-semibold text-gray-800 border-b pb-2 mb-6">Dados Cadastrais</h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="razao_social" class="block text-sm font-medium text-gray-700">Razão Social</label>
                <input type="text" id="razao_social" name="razao_social" value="<?php echo htmlspecialchars($empresa['razao_social'] ?? ''); ?>" class="mt-1 input-form">
            </div>
            <div>
                <label for="nome_fantasia" class="block text-sm font-medium text-gray-700">Nome Fantasia</label>
                <input type="text" id="nome_fantasia" name="nome_fantasia" value="<?php echo htmlspecialchars($empresa['nome_fantasia'] ?? ''); ?>" class="mt-1 input-form">
            </div>
            <div>
                <label for="cnpj" class="block text-sm font-medium text-gray-700">CNPJ</label>
                <div class="mt-1 flex rounded-md shadow-sm">
                    <input type="text" id="cnpj" name="cnpj" value="<?php echo htmlspecialchars($empresa['cnpj'] ?? ''); ?>" class="input-form flex-1 rounded-none rounded-l-md" placeholder="Digite o CNPJ e clique em buscar">
                    <button type="button" id="buscar-cnpj" class="inline-flex items-center px-3 rounded-r-md border border-l-0 border-gray-300 bg-gray-50 text-gray-500 text-sm hover:bg-gray-100 focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                        <svg id="search-icon" class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd" />
                        </svg>
                        <svg id="loading-spinner" class="animate-spin h-5 w-5 text-gray-500 hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </button>
                </div>
            </div>
            <div>
                <label for="ie" class="block text-sm font-medium text-gray-700">Inscrição Estadual</label>
                <div class="flex items-center mt-1">
                    <select id="status_ie" class="input-form rounded-r-none border-r-0 !mt-0 bg-gray-50" style="width: 140px;">
                        <option value="contribuinte">Contribuinte</option>
                        <option value="isento">Isento</option>
                    </select>
                    <input type="text" id="ie" name="ie" value="<?php echo htmlspecialchars($empresa['ie'] ?? ''); ?>" class="input-form flex-1 rounded-l-none !mt-0">
                </div>
            </div>
            <div>
                <label for="cnae" class="block text-sm font-medium text-gray-700">CNAE Principal</label>
                <input type="text" id="cnae" name="cnae" value="<?php echo htmlspecialchars($empresa['cnae'] ?? ''); ?>" class="mt-1 input-form" placeholder="Código - Descrição">
            </div>
            <div>
                <label for="cnpj_cei_tomador" class="block text-sm font-medium text-gray-700">CNPJ/CEI Tomador/Obra</label>
                <input type="text" id="cnpj_cei_tomador" name="cnpj_cei_tomador" value="<?php echo htmlspecialchars($empresa['cnpj_cei_tomador'] ?? ''); ?>" class="mt-1 input-form" placeholder="Caso aplicável">
            </div>
            <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-6 gap-6">
                <div class="md:col-span-2">
                    <label for="cep" class="block text-sm font-medium text-gray-700">CEP</label>
                    <div class="flex rounded-md shadow-sm mt-1">
                        <input type="text" id="cep" name="cep" value="<?php echo htmlspecialchars($empresa['cep'] ?? ''); ?>" class="input-form flex-1 rounded-r-none !mt-0" placeholder="Apenas números">
                        <button type="button" id="buscar-cep-btn" class="inline-flex items-center px-3 rounded-l-none rounded-r-md border border-l-0 border-gray-300 bg-gray-50 text-gray-500 text-sm hover:bg-gray-100">
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="md:col-span-4">
                    <label for="endereco" class="block text-sm font-medium text-gray-700">Endereço (Rua, Nº)</label>
                    <input type="text" id="endereco" name="endereco" value="<?php echo htmlspecialchars($empresa['endereco'] ?? ''); ?>" class="mt-1 input-form">
                </div>
                <div class="md:col-span-2">
                    <label for="bairro" class="block text-sm font-medium text-gray-700">Bairro</label>
                    <input type="text" id="bairro" name="bairro" value="<?php echo htmlspecialchars($empresa['bairro'] ?? ''); ?>" class="mt-1 input-form">
                </div>
                <div class="md:col-span-3">
                    <label for="cidade" class="block text-sm font-medium text-gray-700">Cidade</label>
                    <input type="text" id="cidade" name="cidade" value="<?php echo htmlspecialchars($empresa['cidade'] ?? ''); ?>" class="mt-1 input-form">
                </div>
                <div class="md:col-span-1">
                    <label for="uf" class="block text-sm font-medium text-gray-700">UF</label>
                    <input type="text" id="uf" name="uf" value="<?php echo htmlspecialchars($empresa['uf'] ?? ''); ?>" class="mt-1 input-form">
                </div>
            </div>
            <div>
                <label for="telefone" class="block text-sm font-medium text-gray-700">Telefone</label>
                <input type="text" id="telefone" name="telefone" value="<?php echo htmlspecialchars($empresa['telefone'] ?? ''); ?>" class="mt-1 input-form">
            </div>
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">E-mail</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($empresa['email'] ?? ''); ?>" class="mt-1 input-form">
            </div>
        </div>

        <h3 class="text-lg font-semibold text-gray-800 border-b pb-2 mt-10 mb-6">Logomarca da Empresa</h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-center">
            <div>
                <label for="logo" class="block text-sm font-medium text-gray-700">Alterar Logomarca (JPG, PNG, WEBP)</label>
                <input type="file" id="logo" name="logo" accept="image/*" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                <p class="mt-1 text-xs text-gray-500">Recomendado: Fundo transparente e proporção retangular para o cabeçalho do PDF.</p>
            </div>
            <div id="logo-preview-container" class="flex flex-col items-center justify-center border-2 border-dashed border-gray-200 rounded-lg p-4 bg-gray-50">
                <span class="text-xs font-medium text-gray-500 mb-2 uppercase tracking-wider">Visualização Atual</span>
                <?php if (!empty($empresa['logo_path']) && file_exists(ROOT_PATH . '/public/uploads/logos/' . $empresa['logo_path'])): ?>
                    <img src="<?php echo BASE_URL . '/uploads/logos/' . $empresa['logo_path']; ?>" alt="Logo da Empresa" class="max-h-24 object-contain shadow-sm rounded">
                <?php else: ?>
                    <div class="h-24 w-48 flex flex-col items-center justify-center text-gray-400 bg-white border border-gray-200 rounded italic">
                        <i class="fas fa-image text-3xl mb-1"></i>
                        <span class="text-[10px]">Sem Logomarca</span>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <h3 class="text-lg font-semibold text-gray-800 border-b pb-2 mt-10 mb-6">Certificado Digital (A1)</h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-start">
            <div>
                <label for="certificado_digital" class="block text-sm font-medium text-gray-700">Arquivo do Certificado (.pfx, .p12)</label>
                <input type="file" id="certificado_digital" name="certificado_digital" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-violet-50 file:text-violet-700 hover:file:bg-violet-100">
                <?php if (isset($empresa['caminho_certificado']) && file_exists($empresa['caminho_certificado'])): ?>
                    <p class="mt-2 text-sm text-green-600">Um certificado já está salvo no sistema.</p>
                <?php endif; ?>
            </div>
            <div>
                <label for="senha_certificado" class="block text-sm font-medium text-gray-700">Senha do Certificado</label>
                <input type="password" id="senha_certificado" name="senha_certificado" class="mt-1 input-form" placeholder="Digite a senha para salvar">
                <p class="mt-1 text-xs text-gray-500">A senha não é armazenada, apenas usada para validar o certificado no momento do upload (funcionalidade futura).</p>
            </div>
        </div>

        <!-- Botões de Ação -->
        <div class="mt-8 pt-6 border-t border-gray-200 flex justify-end">
            <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-md shadow-sm hover:bg-indigo-700">Salvar Dados da Empresa</button>
        </div>
    </form>
</div>

<style>
    .input-form {
        margin-top: 0.25rem;
        display: block;
        width: 100%;
        padding: 0.5rem 0.75rem;
        background-color: white;
        border: 1px solid #D1D5DB;
        border-radius: 0.375rem;
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    }

    .input-form:focus {
        outline: none;
        --tw-ring-color: #4F46E5;
        --tw-ring-offset-shadow: var(--tw-ring-inset) 0 0 0 var(--tw-ring-offset-width) var(--tw-ring-offset-color);
        --tw-ring-shadow: var(--tw-ring-inset) 0 0 0 calc(1px + var(--tw-ring-offset-width)) var(--tw-ring-color);
        box-shadow: var(--tw-ring-offset-shadow), var(--tw-ring-shadow), var(--tw-shadow, 0 0 #0000);
        border-color: #6366F1;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const btnBuscar = document.getElementById('buscar-cnpj');
        const inputCnpj = document.getElementById('cnpj');
        const searchIcon = document.getElementById('search-icon');
        const loadingSpinner = document.getElementById('loading-spinner');
        const ieInput = document.getElementById('ie');
        const statusIeSelect = document.getElementById('status_ie');

        btnBuscar.addEventListener('click', async () => {
            const cnpj = inputCnpj.value.replace(/\D/g, ''); // Remove caracteres não numéricos

            if (cnpj.length !== 14) {
                alert('Por favor, digite um CNPJ válido com 14 dígitos.');
                return;
            }

            // Mostra o spinner e esconde o ícone de busca
            searchIcon.classList.add('hidden');
            loadingSpinner.classList.remove('hidden');
            btnBuscar.disabled = true;

            try {
                const response = await fetch(`https://brasilapi.com.br/api/cnpj/v1/${cnpj}`);

                if (!response.ok) {
                    const errorData = await response.json();
                    throw new Error(errorData.message || 'CNPJ não encontrado ou inválido.');
                }

                const data = await response.json();

                // Preenche os campos do formulário
                document.getElementById('razao_social').value = data.razao_social || '';
                document.getElementById('nome_fantasia').value = data.nome_fantasia || '';

                ieInput.value = data.inscricao_estadual || '';
                if (ieInput.value && ieInput.value.toUpperCase() !== 'ISENTO') {
                    statusIeSelect.value = 'contribuinte';
                }
                // Atualiza o estado visual
                updateIeState();

                // Preenche o CNAE automaticamente
                const cnaeTexto = data.cnae_fiscal ? `${data.cnae_fiscal} - ${data.cnae_fiscal_descricao}` : '';
                document.getElementById('cnae').value = cnaeTexto;

                document.getElementById('email').value = data.email || '';
                document.getElementById('telefone').value = data.ddd_telefone_1 || '';

                // Monta o endereço completo
                const endereco = [
                    data.descricao_tipo_de_logradouro,
                    data.logradouro,
                ].filter(Boolean).join(' ');
                const numeroComplemento = [
                    data.numero,
                    data.complemento,
                ].filter(Boolean).join(', ');
                document.getElementById('endereco').value = [endereco, numeroComplemento].filter(Boolean).join(', ');
                document.getElementById('bairro').value = data.bairro || '';
                document.getElementById('cidade').value = data.municipio || '';
                document.getElementById('uf').value = data.uf || '';
                document.getElementById('cep').value = (data.cep || '').replace(/\D/g, '');

            } catch (error) {
                alert(`Erro ao buscar CNPJ: ${error.message}`);
            } finally {
                // Esconde o spinner e mostra o ícone de busca novamente
                searchIcon.classList.remove('hidden');
                loadingSpinner.classList.add('hidden');
                btnBuscar.disabled = false;
            }
        });

        // Lógica para busca de endereço via CEP
        const buscarCepBtn = document.getElementById('buscar-cep-btn');
        const cepInput = document.getElementById('cep');

        const buscarEnderecoPorCep = async () => {
            const cep = cepInput.value.replace(/\D/g, ''); // Remove caracteres não numéricos

            if (cep.length !== 8) {
                return; // Sai se o CEP não tiver 8 dígitos
            }

            // Mostra um feedback de carregamento
            document.getElementById('endereco').value = 'Buscando...';
            document.getElementById('bairro').value = 'Buscando...';
            document.getElementById('cidade').value = 'Buscando...';
            document.getElementById('uf').value = '...';

            try {
                const response = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
                if (!response.ok) throw new Error('Falha na requisição ao ViaCEP.');

                const data = await response.json();
                if (data.erro) throw new Error('CEP não encontrado.');

                // Preenche os campos com os dados retornados
                document.getElementById('endereco').value = data.logradouro || '';
                document.getElementById('bairro').value = data.bairro || '';
                document.getElementById('cidade').value = data.localidade || '';
                document.getElementById('uf').value = data.uf || '';

            } catch (error) {
                alert(`Erro ao buscar CEP: ${error.message}`);
                // Limpa os campos em caso de erro
                document.getElementById('endereco').value = '';
                document.getElementById('bairro').value = '';
                document.getElementById('cidade').value = '';
                document.getElementById('uf').value = '';
            }
        };

        cepInput.addEventListener('blur', buscarEnderecoPorCep);
        buscarCepBtn.addEventListener('click', buscarEnderecoPorCep);

        // Lógica para controle do campo Inscrição Estadual
        function updateIeState() {
            if (statusIeSelect.value === 'isento') {
                ieInput.value = 'ISENTO';
                ieInput.readOnly = true;
                ieInput.classList.add('bg-gray-100', 'text-gray-500');
            } else {
                if (ieInput.value.toUpperCase() === 'ISENTO') {
                    ieInput.value = '';
                }
                ieInput.readOnly = false;
                ieInput.classList.remove('bg-gray-100', 'text-gray-500');
            }
        }

        // Inicialização
        if (ieInput.value.toUpperCase() === 'ISENTO') {
            statusIeSelect.value = 'isento';
        }
        updateIeState();
        statusIeSelect.addEventListener('change', updateIeState);

        // Lógica para prévia instantânea da logomarca
        const logoInput = document.getElementById('logo');
        const logoPreviewContainer = document.getElementById('logo-preview-container');

        if (logoInput && logoPreviewContainer) {
            logoInput.addEventListener('change', function() {
                const file = this.files[0];
                if (file) {
                    // Validação básica de tipo de arquivo no cliente
                    if (!file.type.startsWith('image/')) {
                        alert('Por favor, selecione um arquivo de imagem válido (JPG, PNG ou WEBP).');
                        this.value = '';
                        return;
                    }

                    const reader = new FileReader();
                    reader.onload = function(e) {
                        // Remove a imagem antiga ou o placeholder de "Sem Logomarca"
                        const existingImg = logoPreviewContainer.querySelector('img');
                        const placeholder = logoPreviewContainer.querySelector('div');
                        if (existingImg) existingImg.remove();
                        if (placeholder) placeholder.remove();

                        // Cria e adiciona a nova imagem de prévia
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.alt = "Nova Logomarca";
                        img.className = "max-h-24 object-contain shadow-sm rounded border border-indigo-200 animate-pulse";
                        logoPreviewContainer.appendChild(img);
                        
                        // Remove o efeito de pulso após o carregamento visual
                        setTimeout(() => img.classList.remove('animate-pulse'), 500);
                    };
                    reader.readAsDataURL(file);
                }
            });
        }
    });
</script>