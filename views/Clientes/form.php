<?php
$isEdit = isset($cliente) && !empty($cliente['id']);
$actionUrl = BASE_URL . '/clientes/salvar';

// Garante que os campos JSON sejam arrays vazios se não existirem, para evitar erros no formulário.
$enderecos = $isEdit && !empty($cliente['enderecos_json']) ? json_decode($cliente['enderecos_json'], true) : ['principal' => []];
$contatos = $isEdit && !empty($cliente['contatos_json']) ? json_decode($cliente['contatos_json'], true) : ['principal' => [], 'responsavel' => []];
$financeiro = $isEdit && !empty($cliente['financeiro_json']) ? json_decode($cliente['financeiro_json'], true) : [];
$comercial = $isEdit && !empty($cliente['comercial_json']) ? json_decode($cliente['comercial_json'], true) : [];

// Garante que as subchaves existam para evitar erros de 'undefined array key'
$enderecos['principal'] = $enderecos['principal'] ?? [];
$contatos['principal'] = $contatos['principal'] ?? [];
$contatos['responsavel'] = $contatos['responsavel'] ?? [];

?>

<form id="cliente-form" action="<?php echo $actionUrl; ?>" method="POST" class="space-y-6">
    <?php if ($isEdit) : ?>
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($cliente['id']); ?>">
    <?php endif; ?>

    <!-- Abas de Navegação -->
    <div class="border-b border-gray-200">
        <nav class="-mb-px flex space-x-6" aria-label="Tabs">
            <button type="button" data-tab="tab-basicos" class="tab-button whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm border-sky-500 text-sky-600">Dados Básicos</button>
            <button type="button" data-tab="tab-endereco" class="tab-button whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">Endereço</button>
            <button type="button" data-tab="tab-contatos" class="tab-button whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">Contatos</button>
            <button type="button" data-tab="tab-financeiro" class="tab-button whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">Financeiro</button>
            <button type="button" data-tab="tab-comercial" class="tab-button whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">Comercial</button>
            <button type="button" data-tab="tab-status" class="tab-button whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">Status</button>
        </nav>
    </div>

    <!-- Conteúdo das Abas -->
    <div class="tab-content" id="tab-basicos">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="md:col-span-3">
                <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de Cliente <span class="text-red-500">*</span></label>
                <div class="flex items-center gap-x-6">
                    <div class="flex items-center">
                        <input id="tipo_cliente_juridica" name="tipo_cliente" type="radio" value="Juridica" <?php echo (($cliente['tipo_cliente'] ?? 'Juridica') === 'Juridica') ? 'checked' : ''; ?> class="h-4 w-4 border-gray-300 text-sky-600 focus:ring-sky-600">
                        <label for="tipo_cliente_juridica" class="ml-2 block text-sm font-medium leading-6 text-gray-900">Pessoa Jurídica (CNPJ)</label>
                    </div>
                    <div class="flex items-center">
                        <input id="tipo_cliente_fisica" name="tipo_cliente" type="radio" value="Fisica" <?php echo (($cliente['tipo_cliente'] ?? '') === 'Fisica') ? 'checked' : ''; ?> class="h-4 w-4 border-gray-300 text-sky-600 focus:ring-sky-600">
                        <label for="tipo_cliente_fisica" class="ml-2 block text-sm font-medium leading-6 text-gray-900">Pessoa Física (CPF)</label>
                    </div>
                </div>
            </div>

            <div class="md:col-span-2">
                <label for="nome" class="block text-sm font-medium text-gray-700 mb-1">Nome Completo / Razão Social <span class="text-red-500">*</span></label>
                <input type="text" id="nome" name="nome" required value="<?php echo htmlspecialchars($cliente['nome'] ?? ''); ?>" class="w-full border-gray-300 rounded-lg shadow-sm p-2 text-sm">
            </div>
            <div class="md:col-span-1">
                <label for="nome_fantasia" class="block text-sm font-medium text-gray-700 mb-1">Nome Fantasia</label>
                <input type="text" id="nome_fantasia" name="nome_fantasia" value="<?php echo htmlspecialchars($cliente['nome_fantasia'] ?? ''); ?>" class="w-full border-gray-300 rounded-lg shadow-sm p-2 text-sm">
            </div>

            <div>
                <label for="cnpj_cpf" class="block text-sm font-medium text-gray-700 mb-1">CPF / CNPJ <span class="text-red-500">*</span></label>
                <div class="relative flex items-center">
                    <input type="text" id="cnpj_cpf" name="cnpj_cpf" required value="<?php echo htmlspecialchars($cliente['cnpj_cpf'] ?? ''); ?>" class="w-full border-gray-300 rounded-lg shadow-sm p-2 text-sm pr-10">
                    <button type="button" id="buscar-cnpj-btn" class="absolute right-0 p-2 text-gray-500 hover:text-sky-600" title="Buscar dados pelo CNPJ">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
            </div>
            <div>
                <label for="rg" class="block text-sm font-medium text-gray-700 mb-1">RG</label>
                <input type="text" id="rg" name="rg" value="<?php echo htmlspecialchars($cliente['rg'] ?? ''); ?>" class="w-full border-gray-300 rounded-lg shadow-sm p-2 text-sm">
            </div>
            <div>
                <label for="data_nascimento" class="block text-sm font-medium text-gray-700 mb-1">Data de Nascimento</label>
                <input type="date" id="data_nascimento" name="data_nascimento" value="<?php echo htmlspecialchars($cliente['data_nascimento'] ?? ''); ?>" class="w-full border-gray-300 rounded-lg shadow-sm p-2 text-sm">
            </div>

            <div>
                <label for="inscricao_estadual" class="block text-sm font-medium text-gray-700 mb-1">Inscrição Estadual</label>
                <input type="text" id="inscricao_estadual" name="inscricao_estadual" value="<?php echo htmlspecialchars($cliente['inscricao_estadual'] ?? ''); ?>" class="w-full border-gray-300 rounded-lg shadow-sm p-2 text-sm">
                <div class="mt-1">
                    <input type="checkbox" id="ie_isento" name="ie_isento" value="1" <?php echo !empty($cliente['ie_isento']) ? 'checked' : ''; ?> class="h-4 w-4 rounded border-gray-300 text-sky-600 focus:ring-sky-600">
                    <label for="ie_isento" class="ml-2 text-xs text-gray-600">Isento</label>
                </div>
            </div>
            <div>
                <label for="inscricao_municipal" class="block text-sm font-medium text-gray-700 mb-1">Inscrição Municipal</label>
                <input type="text" id="inscricao_municipal" name="inscricao_municipal" value="<?php echo htmlspecialchars($cliente['inscricao_municipal'] ?? ''); ?>" class="w-full border-gray-300 rounded-lg shadow-sm p-2 text-sm">
            </div>
            <div class="md:col-span-1">
                <label for="categoria_segmento" class="block text-sm font-medium text-gray-700 mb-1">Categoria</label>
                <select id="categoria_segmento" name="categoria_id" class="w-full border-gray-300 rounded-lg shadow-sm p-2 text-sm">
                    <option value="">Selecione...</option>
                    <?php foreach ($categorias as $categoria) : ?>
                        <option value="<?php echo htmlspecialchars($categoria['id']); ?>" <?php echo (($cliente['categoria_id'] ?? '') == $categoria['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($categoria['nome']); ?>
                        </option>
                    <?php endforeach; ?>
                    <option value="--add-new--">-- Cadastrar Nova Categoria --</option>
                </select>
                <div id="nova-categoria-div" class="hidden mt-2 flex gap-2">
                    <input type="text" id="nova-categoria-nome" placeholder="Nome da nova categoria" class="flex-grow w-full border-gray-300 rounded-lg shadow-sm p-2 text-sm">
                    <button type="button" id="salvar-nova-categoria-btn" class="px-3 py-1 bg-green-500 text-white rounded-lg text-sm hover:bg-green-600">Salvar</button>
                    <button type="button" id="cancelar-nova-categoria-btn" class="px-3 py-1 bg-gray-200 text-gray-700 rounded-lg text-sm hover:bg-gray-300">X</button>
                </div>
            </div>

            <div>
                <label for="segmento" class="block text-sm font-medium text-gray-700 mb-1">Segmento</label>
                <select id="segmento" name="segmento" class="w-full border-gray-300 rounded-lg shadow-sm p-2 text-sm" <?php echo empty($cliente['categoria_id']) ? 'disabled' : ''; ?>>
                    <option value="">Selecione uma categoria primeiro</option>
                    <?php if (!empty($segmentos)) : ?>
                        <?php foreach ($segmentos as $segmento) : ?>
                            <option value="<?php echo htmlspecialchars($segmento['nome']); ?>" <?php echo (($cliente['segmento'] ?? '') === $segmento['nome']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($segmento['nome']); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <option value="--add-new--">-- Cadastrar Novo Segmento --</option>
                </select>
                <div id="novo-segmento-div" class="hidden mt-2 flex gap-2">
                    <input type="text" id="novo-segmento-nome" placeholder="Nome do novo segmento" class="flex-grow w-full border-gray-300 rounded-lg shadow-sm p-2 text-sm">
                    <button type="button" id="salvar-novo-segmento-btn" class="px-3 py-1 bg-green-500 text-white rounded-lg text-sm hover:bg-green-600">Salvar</button>
                    <button type="button" id="cancelar-novo-segmento-btn" class="px-3 py-1 bg-gray-200 text-gray-700 rounded-lg text-sm hover:bg-gray-300">X</button>
                </div>
            </div>

            <div>
                <label for="classificacao" class="block text-sm font-medium text-gray-700 mb-1">Classificação</label>
                <select id="classificacao" name="classificacao" class="w-full border-gray-300 rounded-lg shadow-sm p-2 text-sm">
                    <option value="Bronze" <?php echo (($cliente['classificacao'] ?? '') === 'Bronze') ? 'selected' : ''; ?>>Bronze</option>
                    <option value="Prata" <?php echo (($cliente['classificacao'] ?? '') === 'Prata') ? 'selected' : ''; ?>>Prata</option>
                    <option value="Ouro" <?php echo (($cliente['classificacao'] ?? '') === 'Ouro') ? 'selected' : ''; ?>>Ouro</option>
                    <option value="Premium" <?php echo (($cliente['classificacao'] ?? '') === 'Premium') ? 'selected' : ''; ?>>Premium</option>
                </select>
            </div>
            <div>
                <label for="origem_cliente" class="block text-sm font-medium text-gray-700 mb-1">Origem do Cliente</label>
                <input type="text" id="origem_cliente" name="origem_cliente" value="<?php echo htmlspecialchars($cliente['origem_cliente'] ?? ''); ?>" class="w-full border-gray-300 rounded-lg shadow-sm p-2 text-sm" placeholder="Indicação, Site, etc.">
            </div>
            <div class="md:col-span-3">
                <label for="observacoes_iniciais" class="block text-sm font-medium text-gray-700 mb-1">Observações Iniciais</label>
                <textarea id="observacoes_iniciais" name="observacoes_iniciais" rows="2" class="w-full border-gray-300 rounded-lg shadow-sm p-2 text-sm"><?php echo htmlspecialchars($cliente['observacoes_iniciais'] ?? ''); ?></textarea>
            </div>
        </div>
    </div>

    <div class="tab-content hidden" id="tab-endereco">
        <p class="text-sm text-gray-500 mb-4">Preencha o endereço principal. Futuramente, haverá suporte para múltiplos endereços.</p>
        <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
            <div class="md:col-span-2">
                <label for="cep" class="block text-sm font-medium text-gray-700 mb-1">CEP <span class="text-red-500">*</span></label>
                <div class="relative flex items-center">
                    <input type="text" id="cep" name="enderecos[principal][cep]" required value="<?php echo htmlspecialchars($enderecos['principal']['cep'] ?? ''); ?>" class="w-full border-gray-300 rounded-lg shadow-sm p-2 text-sm pr-10">
                    <button type="button" id="buscar-cep-btn" class="absolute right-0 p-2 text-gray-500 hover:text-sky-600" title="Buscar endereço pelo CEP">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
            </div>
            <div class="md:col-span-4">
                <label for="logradouro" class="block text-sm font-medium text-gray-700 mb-1">Logradouro <span class="text-red-500">*</span></label>
                <input type="text" id="logradouro" name="enderecos[principal][logradouro]" required value="<?php echo htmlspecialchars($enderecos['principal']['logradouro'] ?? ''); ?>" class="w-full border-gray-300 rounded-lg shadow-sm p-2 text-sm">
            </div>
            <div class="md:col-span-2">
                <label for="numero" class="block text-sm font-medium text-gray-700 mb-1">Número</label>
                <input type="text" id="numero" name="enderecos[principal][numero]" value="<?php echo htmlspecialchars($enderecos['principal']['numero'] ?? ''); ?>" class="w-full border-gray-300 rounded-lg shadow-sm p-2 text-sm">
            </div>
            <div class="md:col-span-4">
                <label for="complemento" class="block text-sm font-medium text-gray-700 mb-1">Complemento</label>
                <input type="text" id="complemento" name="enderecos[principal][complemento]" value="<?php echo htmlspecialchars($enderecos['principal']['complemento'] ?? ''); ?>" class="w-full border-gray-300 rounded-lg shadow-sm p-2 text-sm">
            </div>
            <div class="md:col-span-3">
                <label for="bairro" class="block text-sm font-medium text-gray-700 mb-1">Bairro</label>
                <input type="text" id="bairro" name="enderecos[principal][bairro]" value="<?php echo htmlspecialchars($enderecos['principal']['bairro'] ?? ''); ?>" class="w-full border-gray-300 rounded-lg shadow-sm p-2 text-sm">
            </div>
            <div class="md:col-span-2">
                <label for="cidade" class="block text-sm font-medium text-gray-700 mb-1">Cidade <span class="text-red-500">*</span></label>
                <input type="text" id="cidade" name="enderecos[principal][cidade]" required value="<?php echo htmlspecialchars($enderecos['principal']['cidade'] ?? ''); ?>" class="w-full border-gray-300 rounded-lg shadow-sm p-2 text-sm">
            </div>
            <div class="md:col-span-1">
                <label for="estado" class="block text-sm font-medium text-gray-700 mb-1">Estado (UF) <span class="text-red-500">*</span></label>
                <input type="text" id="estado" name="enderecos[principal][estado]" required value="<?php echo htmlspecialchars($enderecos['principal']['estado'] ?? ''); ?>" class="w-full border-gray-300 rounded-lg shadow-sm p-2 text-sm">
            </div>
        </div>
    </div>

    <div class="tab-content hidden" id="tab-contatos">
        <h3 class="text-lg font-semibold text-gray-700 mb-4 border-b pb-2">Contato Principal</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="telefone_principal" class="block text-sm font-medium text-gray-700 mb-1">Telefone Principal <span class="text-red-500">*</span></label>
                <input type="text" id="telefone_principal" name="contatos[principal][telefone]" required value="<?php echo htmlspecialchars($contatos['principal']['telefone'] ?? ($cliente['telefone'] ?? '')); ?>" class="w-full border-gray-300 rounded-lg shadow-sm p-2 text-sm">
            </div>
            <div>
                <label for="email_principal" class="block text-sm font-medium text-gray-700 mb-1">E-mail Principal <span class="text-red-500">*</span></label>
                <input type="email" id="email_principal" name="contatos[principal][email]" required value="<?php echo htmlspecialchars($contatos['principal']['email'] ?? ($cliente['email'] ?? '')); ?>" class="w-full border-gray-300 rounded-lg shadow-sm p-2 text-sm">
            </div>
            <div>
                <label for="telefone_secundario" class="block text-sm font-medium text-gray-700 mb-1">Telefone Secundário</label>
                <input type="text" id="telefone_secundario" name="contatos[principal][telefone_secundario]" value="<?php echo htmlspecialchars($contatos['principal']['telefone_secundario'] ?? ''); ?>" class="w-full border-gray-300 rounded-lg shadow-sm p-2 text-sm">
            </div>
            <div>
                <label for="whatsapp" class="block text-sm font-medium text-gray-700 mb-1">WhatsApp</label>
                <input type="text" id="whatsapp" name="contatos[principal][whatsapp]" value="<?php echo htmlspecialchars($contatos['principal']['whatsapp'] ?? ''); ?>" class="w-full border-gray-300 rounded-lg shadow-sm p-2 text-sm">
            </div>
            <div>
                <label for="email_financeiro" class="block text-sm font-medium text-gray-700 mb-1">E-mail Financeiro</label>
                <input type="email" id="email_financeiro" name="contatos[principal][email_financeiro]" value="<?php echo htmlspecialchars($contatos['principal']['email_financeiro'] ?? ''); ?>" class="w-full border-gray-300 rounded-lg shadow-sm p-2 text-sm">
            </div>
            <div>
                <label for="email_comercial" class="block text-sm font-medium text-gray-700 mb-1">E-mail Comercial</label>
                <input type="email" id="email_comercial" name="contatos[principal][email_comercial]" value="<?php echo htmlspecialchars($contatos['principal']['email_comercial'] ?? ''); ?>" class="w-full border-gray-300 rounded-lg shadow-sm p-2 text-sm">
            </div>
        </div>

        <h3 class="text-lg font-semibold text-gray-700 mt-6 mb-4 pt-4 border-t">Responsável pelo Cliente</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="responsavel_nome" class="block text-sm font-medium text-gray-700 mb-1">Nome do Responsável</label>
                <input type="text" id="responsavel_nome" name="contatos[responsavel][nome]" value="<?php echo htmlspecialchars($contatos['responsavel']['nome'] ?? ($cliente['contato_principal'] ?? '')); ?>" class="w-full border-gray-300 rounded-lg shadow-sm p-2 text-sm">
            </div>
            <div>
                <label for="responsavel_cargo" class="block text-sm font-medium text-gray-700 mb-1">Cargo</label>
                <input type="text" id="responsavel_cargo" name="contatos[responsavel][cargo]" value="<?php echo htmlspecialchars($contatos['responsavel']['cargo'] ?? ''); ?>" class="w-full border-gray-300 rounded-lg shadow-sm p-2 text-sm">
            </div>
            <div>
                <label for="responsavel_telefone" class="block text-sm font-medium text-gray-700 mb-1">Telefone do Responsável</label>
                <input type="text" id="responsavel_telefone" name="contatos[responsavel][telefone]" value="<?php echo htmlspecialchars($contatos['responsavel']['telefone'] ?? ''); ?>" class="w-full border-gray-300 rounded-lg shadow-sm p-2 text-sm">
            </div>
            <div>
                <label for="responsavel_email" class="block text-sm font-medium text-gray-700 mb-1">E-mail do Responsável</label>
                <input type="email" id="responsavel_email" name="contatos[responsavel][email]" value="<?php echo htmlspecialchars($contatos['responsavel']['email'] ?? ''); ?>" class="w-full border-gray-300 rounded-lg shadow-sm p-2 text-sm">
            </div>
        </div>
    </div>

    <div class="tab-content hidden" id="tab-financeiro">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label for="limite_credito" class="block text-sm font-medium text-gray-700 mb-1">Limite de Crédito (R$)</label>
                <input type="text" id="limite_credito" name="financeiro[limite_credito]" value="<?php echo htmlspecialchars($financeiro['limite_credito'] ?? ''); ?>" class="w-full border-gray-300 rounded-lg shadow-sm p-2 text-sm">
            </div>
            <div>
                <label for="condicao_pagamento" class="block text-sm font-medium text-gray-700 mb-1">Condição de Pagamento Padrão</label>
                <select id="condicao_pagamento" name="financeiro[condicao_pagamento]" class="w-full border-gray-300 rounded-lg shadow-sm p-2 text-sm">
                    <option value="À vista" <?php echo (($financeiro['condicao_pagamento'] ?? '') === 'À vista') ? 'selected' : ''; ?>>À vista</option>
                    <option value="15 dias" <?php echo (($financeiro['condicao_pagamento'] ?? '') === '15 dias') ? 'selected' : ''; ?>>15 dias</option>
                    <option value="30 dias" <?php echo (($financeiro['condicao_pagamento'] ?? '') === '30 dias') ? 'selected' : ''; ?>>30 dias</option>
                    <option value="60 dias" <?php echo (($financeiro['condicao_pagamento'] ?? '') === '60 dias') ? 'selected' : ''; ?>>60 dias</option>
                </select>
            </div>
            <div>
                <label for="forma_pagamento" class="block text-sm font-medium text-gray-700 mb-1">Forma de Pagamento Padrão</label>
                <select id="forma_pagamento" name="financeiro[forma_pagamento]" class="w-full border-gray-300 rounded-lg shadow-sm p-2 text-sm">
                    <option value="Pix" <?php echo (($financeiro['forma_pagamento'] ?? '') === 'Pix') ? 'selected' : ''; ?>>Pix</option>
                    <option value="Cartão de Crédito" <?php echo (($financeiro['forma_pagamento'] ?? '') === 'Cartão de Crédito') ? 'selected' : ''; ?>>Cartão de Crédito</option>
                    <option value="Boleto" <?php echo (($financeiro['forma_pagamento'] ?? '') === 'Boleto') ? 'selected' : ''; ?>>Boleto</option>
                    <option value="Transferência" <?php echo (($financeiro['forma_pagamento'] ?? '') === 'Transferência') ? 'selected' : ''; ?>>Transferência</option>
                </select>
            </div>
            <div>
                <label for="banco" class="block text-sm font-medium text-gray-700 mb-1">Banco</label>
                <input type="text" id="banco" name="financeiro[banco]" value="<?php echo htmlspecialchars($financeiro['banco'] ?? ''); ?>" class="w-full border-gray-300 rounded-lg shadow-sm p-2 text-sm">
            </div>
            <div>
                <label for="agencia" class="block text-sm font-medium text-gray-700 mb-1">Agência</label>
                <input type="text" id="agencia" name="financeiro[agencia]" value="<?php echo htmlspecialchars($financeiro['agencia'] ?? ''); ?>" class="w-full border-gray-300 rounded-lg shadow-sm p-2 text-sm">
            </div>
            <div>
                <label for="conta" class="block text-sm font-medium text-gray-700 mb-1">Conta</label>
                <input type="text" id="conta" name="financeiro[conta]" value="<?php echo htmlspecialchars($financeiro['conta'] ?? ''); ?>" class="w-full border-gray-300 rounded-lg shadow-sm p-2 text-sm">
            </div>
            <div class="md:col-span-2">
                <label for="chave_pix" class="block text-sm font-medium text-gray-700 mb-1">Chave PIX</label>
                <input type="text" id="chave_pix" name="financeiro[chave_pix]" value="<?php echo htmlspecialchars($financeiro['chave_pix'] ?? ''); ?>" class="w-full border-gray-300 rounded-lg shadow-sm p-2 text-sm">
            </div>
            <div>
                <label for="enviar_fatura_por" class="block text-sm font-medium text-gray-700 mb-1">Enviar Fatura Por</label>
                <select id="enviar_fatura_por" name="financeiro[enviar_fatura_por]" class="w-full border-gray-300 rounded-lg shadow-sm p-2 text-sm">
                    <option value="E-mail" <?php echo (($financeiro['enviar_fatura_por'] ?? '') === 'E-mail') ? 'selected' : ''; ?>>E-mail</option>
                    <option value="WhatsApp" <?php echo (($financeiro['enviar_fatura_por'] ?? '') === 'WhatsApp') ? 'selected' : ''; ?>>WhatsApp</option>
                    <option value="Sistema" <?php echo (($financeiro['enviar_fatura_por'] ?? '') === 'Sistema') ? 'selected' : ''; ?>>Sistema Interno</option>
                </select>
            </div>
        </div>
    </div>

    <div class="tab-content hidden" id="tab-comercial">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
                <label for="produtos_servicos_interesse" class="block text-sm font-medium text-gray-700 mb-1">Produtos/Serviços de Interesse</label>
                <textarea id="produtos_servicos_interesse" name="comercial[produtos_servicos_interesse]" rows="3" class="w-full border-gray-300 rounded-lg shadow-sm p-2 text-sm"><?php echo htmlspecialchars($comercial['produtos_servicos_interesse'] ?? ''); ?></textarea>
            </div>
            <div>
                <label for="representante_comercial" class="block text-sm font-medium text-gray-700 mb-1">Representante Comercial Responsável</label>
                <input type="text" id="representante_comercial" name="comercial[representante_comercial]" value="<?php echo htmlspecialchars($comercial['representante_comercial'] ?? ''); ?>" class="w-full border-gray-300 rounded-lg shadow-sm p-2 text-sm">
            </div>
            <div>
                <label for="regiao_atuacao" class="block text-sm font-medium text-gray-700 mb-1">Região de Atuação</label>
                <input type="text" id="regiao_atuacao" name="comercial[regiao_atuacao]" value="<?php echo htmlspecialchars($comercial['regiao_atuacao'] ?? ''); ?>" class="w-full border-gray-300 rounded-lg shadow-sm p-2 text-sm">
            </div>
            <div class="md:col-span-2">
                <label for="observacoes_internas" class="block text-sm font-medium text-gray-700 mb-1">Observações Internas (visível apenas para a equipe)</label>
                <textarea id="observacoes_internas" name="comercial[observacoes_internas]" rows="3" class="w-full border-gray-300 rounded-lg shadow-sm p-2 text-sm"><?php echo htmlspecialchars($comercial['observacoes_internas'] ?? ''); ?></textarea>
            </div>
        </div>
    </div>

    <div class="tab-content hidden" id="tab-status">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Situação <span class="text-red-500">*</span></label>
                <select id="status" name="status" required class="w-full border-gray-300 rounded-lg shadow-sm p-2 text-sm">
                    <option value="Potencial" <?php echo (($cliente['status'] ?? 'Potencial') === 'Potencial') ? 'selected' : ''; ?>>Potencial</option>
                    <option value="Em negociação" <?php echo (($cliente['status'] ?? '') === 'Em negociação') ? 'selected' : ''; ?>>Em negociação</option>
                    <option value="Ativo" <?php echo (($cliente['status'] ?? '') === 'Ativo') ? 'selected' : ''; ?>>Ativo</option>
                    <option value="Inativo" <?php echo (($cliente['status'] ?? '') === 'Inativo') ? 'selected' : ''; ?>>Inativo</option>
                </select>
            </div>
            <div>
                <label for="motivo_inativacao" class="block text-sm font-medium text-gray-700 mb-1">Motivo da Inativação</label>
                <input type="text" id="motivo_inativacao" name="motivo_inativacao" value="<?php echo htmlspecialchars($cliente['motivo_inativacao'] ?? ''); ?>" class="w-full border-gray-300 rounded-lg shadow-sm p-2 text-sm">
            </div>
            <div>
                <label for="data_inativacao" class="block text-sm font-medium text-gray-700 mb-1">Data da Inativação</label>
                <input type="date" id="data_inativacao" name="data_inativacao" value="<?php echo htmlspecialchars($cliente['data_inativacao'] ?? ''); ?>" class="w-full border-gray-300 rounded-lg shadow-sm p-2 text-sm">
            </div>
        </div>
    </div>

    <!-- Botões de Ação -->
    <div class="mt-6 pt-4 border-t border-gray-200 flex justify-end gap-3">
        <button type="button" id="cancel-form-btn" class="bg-gray-200 text-gray-800 px-4 py-2 rounded-lg hover:bg-gray-300 font-semibold text-sm">
            Cancelar
        </button>
        <button type="submit" class="bg-sky-600 text-white px-6 py-2 rounded-lg hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-500 font-semibold text-sm">
            <?php echo $isEdit ? 'Atualizar Cliente' : 'Salvar Cliente'; ?>
        </button>
    </div>
</form>

<!-- Todo o script de controle do formulário foi movido para /views/partials/modal_cliente_form.php para garantir a execução correta após o carregamento dinâmico do conteúdo. -->