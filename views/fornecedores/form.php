<?php
$isEdit = isset($fornecedor) && !empty($fornecedor['id']);
$actionUrl = BASE_URL . '/fornecedores/salvar';

// Garante que os campos JSON sejam arrays vazios se não existirem, para evitar erros.
$endereco = $isEdit && !empty($fornecedor['endereco_json']) ? json_decode($fornecedor['endereco_json'], true) : [];
$contato = $isEdit && !empty($fornecedor['contato_json']) ? json_decode($fornecedor['contato_json'], true) : [];
$dados_financeiros = $isEdit && !empty($fornecedor['dados_financeiros_json']) ? json_decode($fornecedor['dados_financeiros_json'], true) : [];
$info_comerciais = $isEdit && !empty($fornecedor['info_comerciais_json']) ? json_decode($fornecedor['info_comerciais_json'], true) : [];

// Mapeia o nome do campo antigo para o novo para retrocompatibilidade
$fornecedor['nome'] = $fornecedor['nome'] ?? ($fornecedor['razao_social'] ?? '');
$fornecedor['cnpj'] = $fornecedor['cnpj'] ?? ($fornecedor['cnpj_cpf'] ?? '');
?>

<div class="bg-white p-6 rounded-lg shadow-md max-w-4xl mx-auto">
    <h2 class="text-xl font-bold mb-6 text-gray-800 border-b pb-3">
        <?php echo $isEdit ? 'Editar Fornecedor' : 'Cadastrar Novo Fornecedor'; ?>
    </h2>

    <form action="<?php echo $actionUrl; ?>" method="POST" enctype="multipart/form-data">
        <?php if ($isEdit) : ?>
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($fornecedor['id']); ?>">
        <?php endif; ?>

        <!-- 1. Dados Básicos -->
        <h3 class="text-lg font-semibold text-gray-700 mb-4 border-b pb-2">1. Dados Básicos</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Tipo de Pessoa -->
            <div class="md:col-span-3">
                <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de Fornecedor <span class="text-red-500">*</span></label>
                <div class="flex items-center gap-x-6">
                    <div class="flex items-center">
                        <input id="tipo_pessoa_juridica" name="tipo_pessoa" type="radio" value="Juridica" <?php echo (($fornecedor['tipo_pessoa'] ?? 'Juridica') === 'Juridica') ? 'checked' : ''; ?> class="h-4 w-4 border-gray-300 text-sky-600 focus:ring-sky-600">
                        <label for="tipo_pessoa_juridica" class="ml-2 block text-sm font-medium leading-6 text-gray-900">Pessoa Jurídica (CNPJ)</label>
                    </div>
                    <div class="flex items-center">
                        <input id="tipo_pessoa_fisica" name="tipo_pessoa" type="radio" value="Fisica" <?php echo (($fornecedor['tipo_pessoa'] ?? '') === 'Fisica') ? 'checked' : ''; ?> class="h-4 w-4 border-gray-300 text-sky-600 focus:ring-sky-600">
                        <label for="tipo_pessoa_fisica" class="ml-2 block text-sm font-medium leading-6 text-gray-900">Pessoa Física (CPF)</label>
                    </div>
                </div>
            </div>

            <!-- Razão Social -->
            <div class="md:col-span-2">
                <label for="nome" class="block text-sm font-medium text-gray-700 mb-1">Nome / Razão Social <span class="text-red-500">*</span></label>
                <input type="text" id="nome" name="nome" required value="<?php echo htmlspecialchars($fornecedor['nome'] ?? ''); ?>" class="w-full border-gray-300 rounded-lg shadow-sm p-2 text-sm">
            </div>

            <!-- CNPJ/CPF -->
            <div class="md:col-span-1">
                <label for="cnpj" id="label-cnpj-cpf" class="block text-sm font-medium text-gray-700 mb-1">CNPJ <span class="text-red-500">*</span></label>
                <div class="flex">
                    <input type="text" id="cnpj" name="cnpj" value="<?php echo htmlspecialchars($fornecedor['cnpj'] ?? ''); ?>" class="w-full border-gray-300 rounded-l-lg shadow-sm p-2 text-sm" placeholder="Apenas números">
                    <button type="button" id="buscar-cnpj-btn" class="px-3 bg-gray-200 border border-l-0 border-gray-300 rounded-r-lg hover:bg-gray-300">
                        <svg id="cnpj-search-icon" class="h-5 w-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        <svg id="cnpj-loading-spinner" class="animate-spin h-5 w-5 text-gray-500 hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Nome Fantasia -->
            <div class="md:col-span-2">
                <label for="nome_fantasia" class="block text-sm font-medium text-gray-700 mb-1">Nome Fantasia</label>
                <input type="text" id="nome_fantasia" name="nome_fantasia" value="<?php echo htmlspecialchars($fornecedor['nome_fantasia'] ?? ''); ?>" class="w-full border-gray-300 rounded-lg shadow-sm p-2 text-sm">
            </div>

            <!-- Categoria de Fornecimento -->
            <div>
                <label for="categoria_fornecimento" class="block text-sm font-medium text-gray-700 mb-1">Categoria <span class="text-red-500">*</span></label>
                <select id="categoria_fornecimento" name="categoria_fornecimento" required class="w-full border-gray-300 rounded-lg shadow-sm p-2 text-sm">
                    <option value="">Selecione...</option>
                    <option value="Materiais" <?php echo (($fornecedor['categoria_fornecimento'] ?? '') === 'Materiais') ? 'selected' : ''; ?>>Materiais</option>
                    <option value="Serviços" <?php echo (($fornecedor['categoria_fornecimento'] ?? '') === 'Serviços') ? 'selected' : ''; ?>>Serviços</option>
                    <option value="Insumos" <?php echo (($fornecedor['categoria_fornecimento'] ?? '') === 'Insumos') ? 'selected' : ''; ?>>Insumos</option>
                    <option value="Consultoria" <?php echo (($fornecedor['categoria_fornecimento'] ?? '') === 'Consultoria') ? 'selected' : ''; ?>>Consultoria</option>
                </select>
            </div>

            <!-- Inscrição Estadual -->
            <div>
                <label for="inscricao_estadual" class="block text-sm font-medium text-gray-700 mb-1">Inscrição Estadual</label>
                <input type="text" id="inscricao_estadual" name="inscricao_estadual" value="<?php echo htmlspecialchars($fornecedor['inscricao_estadual'] ?? ''); ?>" class="w-full border-gray-300 rounded-lg shadow-sm p-2 text-sm" <?php echo !empty($fornecedor['ie_isento']) ? 'disabled' : ''; ?>>
                <div class="mt-2">
                    <input type="checkbox" id="ie_isento" name="ie_isento" value="1" <?php echo !empty($fornecedor['ie_isento']) ? 'checked' : ''; ?> class="h-4 w-4 rounded border-gray-300 text-sky-600 focus:ring-sky-600">
                    <label for="ie_isento" class="ml-2 text-sm text-gray-600">Contribuinte Isento</label>
                </div>
            </div>

            <!-- Inscrição Municipal -->
            <div>
                <label for="inscricao_municipal" class="block text-sm font-medium text-gray-700 mb-1">Inscrição Municipal</label>
                <input type="text" id="inscricao_municipal" name="inscricao_municipal" value="<?php echo htmlspecialchars($fornecedor['inscricao_municipal'] ?? ''); ?>" class="w-full border-gray-300 rounded-lg shadow-sm p-2 text-sm">
            </div>
        </div>

        <!-- 2. Endereço -->
        <h3 class="text-lg font-semibold text-gray-700 mt-6 mb-4 pt-4 border-t">2. Endereço</h3>
        <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
            <div class="md:col-span-2">
                <label for="cep" class="block text-sm font-medium text-gray-700 mb-1">CEP <span class="text-red-500">*</span></label>
                <div class="flex">
                    <input type="text" id="cep" name="endereco[cep]" required value="<?php echo htmlspecialchars($endereco['cep'] ?? ''); ?>" class="w-full border-gray-300 rounded-l-lg shadow-sm p-2 text-sm" placeholder="Apenas números">
                    <button type="button" id="buscar-cep-btn" class="px-3 bg-gray-200 border border-l-0 border-gray-300 rounded-r-lg hover:bg-gray-300">
                        <svg class="h-5 w-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </button>
                </div>
            </div>
            <div class="md:col-span-4">
                <label for="logradouro" class="block text-sm font-medium text-gray-700 mb-1">Logradouro <span class="text-red-500">*</span></label>
                <input type="text" id="logradouro" name="endereco[logradouro]" required value="<?php echo htmlspecialchars($endereco['logradouro'] ?? ''); ?>" class="w-full border-gray-300 rounded-lg shadow-sm p-2 text-sm">
            </div>
            <div class="md:col-span-2">
                <label for="numero" class="block text-sm font-medium text-gray-700 mb-1">Número</label>
                <input type="text" id="numero" name="endereco[numero]" value="<?php echo htmlspecialchars($endereco['numero'] ?? ''); ?>" class="w-full border-gray-300 rounded-lg shadow-sm p-2 text-sm">
            </div>
            <div class="md:col-span-4">
                <label for="complemento" class="block text-sm font-medium text-gray-700 mb-1">Complemento</label>
                <input type="text" id="complemento" name="endereco[complemento]" value="<?php echo htmlspecialchars($endereco['complemento'] ?? ''); ?>" class="w-full border-gray-300 rounded-lg shadow-sm p-2 text-sm">
            </div>
            <div class="md:col-span-3">
                <label for="bairro" class="block text-sm font-medium text-gray-700 mb-1">Bairro</label>
                <input type="text" id="bairro" name="endereco[bairro]" value="<?php echo htmlspecialchars($endereco['bairro'] ?? ''); ?>" class="w-full border-gray-300 rounded-lg shadow-sm p-2 text-sm">
            </div>
            <div class="md:col-span-2">
                <label for="cidade" class="block text-sm font-medium text-gray-700 mb-1">Cidade <span class="text-red-500">*</span></label>
                <input type="text" id="cidade" name="endereco[cidade]" required value="<?php echo htmlspecialchars($endereco['cidade'] ?? ''); ?>" class="w-full border-gray-300 rounded-lg shadow-sm p-2 text-sm">
            </div>
            <div class="md:col-span-1">
                <label for="uf" class="block text-sm font-medium text-gray-700 mb-1">Estado (UF) <span class="text-red-500">*</span></label>
                <input type="text" id="uf" name="endereco[uf]" required value="<?php echo htmlspecialchars($endereco['uf'] ?? ''); ?>" class="w-full border-gray-300 rounded-lg shadow-sm p-2 text-sm">
            </div>
            <div class="md:col-span-2">
                <label for="pais" class="block text-sm font-medium text-gray-700 mb-1">País</label>
                <input type="text" id="pais" name="endereco[pais]" value="<?php echo htmlspecialchars($endereco['pais'] ?? 'Brasil'); ?>" class="w-full border-gray-300 rounded-lg shadow-sm p-2 text-sm bg-gray-50">
            </div>
        </div>

        <!-- 3. Contato -->
        <h3 class="text-lg font-semibold text-gray-700 mt-6 mb-4 pt-4 border-t">3. Contato</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="telefone_comercial" class="block text-sm font-medium text-gray-700 mb-1">Telefone Comercial <span class="text-red-500">*</span></label>
                <input type="text" id="telefone_comercial" name="contato[telefone_comercial]" required value="<?php echo htmlspecialchars($contato['telefone_comercial'] ?? ''); ?>" class="w-full border-gray-300 rounded-lg shadow-sm p-2 text-sm">
            </div>
            <div>
                <label for="email_principal" class="block text-sm font-medium text-gray-700 mb-1">E-mail Principal <span class="text-red-500">*</span></label>
                <input type="email" id="email_principal" name="contato[email_principal]" required value="<?php echo htmlspecialchars($contato['email_principal'] ?? ''); ?>" class="w-full border-gray-300 rounded-lg shadow-sm p-2 text-sm">
            </div>
            <div>
                <label for="telefone_celular" class="block text-sm font-medium text-gray-700 mb-1">Telefone Celular</label>
                <input type="text" id="telefone_celular" name="contato[telefone_celular]" value="<?php echo htmlspecialchars($contato['telefone_celular'] ?? ''); ?>" class="w-full border-gray-300 rounded-lg shadow-sm p-2 text-sm">
            </div>
            <div>
                <label for="whatsapp" class="block text-sm font-medium text-gray-700 mb-1">WhatsApp</label>
                <input type="text" id="whatsapp" name="contato[whatsapp]" value="<?php echo htmlspecialchars($contato['whatsapp'] ?? ''); ?>" class="w-full border-gray-300 rounded-lg shadow-sm p-2 text-sm">
            </div>
            <div>
                <label for="email_financeiro" class="block text-sm font-medium text-gray-700 mb-1">E-mail Financeiro</label>
                <input type="email" id="email_financeiro" name="contato[email_financeiro]" value="<?php echo htmlspecialchars($contato['email_financeiro'] ?? ''); ?>" class="w-full border-gray-300 rounded-lg shadow-sm p-2 text-sm">
            </div>
            <div>
                <label for="site" class="block text-sm font-medium text-gray-700 mb-1">Site / URL</label>
                <input type="url" id="site" name="contato[site]" value="<?php echo htmlspecialchars($contato['site'] ?? ''); ?>" class="w-full border-gray-300 rounded-lg shadow-sm p-2 text-sm">
            </div>
            <div class="md:col-span-2 pt-2 mt-2 border-t">
                <p class="text-sm font-medium text-gray-600 mb-2">Representante</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <input type="text" name="contato[representante_nome]" placeholder="Nome do Representante" value="<?php echo htmlspecialchars($contato['representante_nome'] ?? ''); ?>" class="w-full border-gray-300 rounded-lg shadow-sm p-2 text-sm">
                    <input type="text" name="contato[representante_cargo]" placeholder="Cargo do Representante" value="<?php echo htmlspecialchars($contato['representante_cargo'] ?? ''); ?>" class="w-full border-gray-300 rounded-lg shadow-sm p-2 text-sm">
                    <input type="text" name="contato[representante_telefone]" placeholder="Telefone do Representante" value="<?php echo htmlspecialchars($contato['representante_telefone'] ?? ''); ?>" class="w-full border-gray-300 rounded-lg shadow-sm p-2 text-sm">
                    <input type="email" name="contato[representante_email]" placeholder="E-mail do Representante" value="<?php echo htmlspecialchars($contato['representante_email'] ?? ''); ?>" class="w-full border-gray-300 rounded-lg shadow-sm p-2 text-sm">
                </div>
            </div>
        </div>

        <!-- 4. Dados Financeiros -->
        <h3 class="text-lg font-semibold text-gray-700 mt-6 mb-4 pt-4 border-t">4. Dados Financeiros</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label for="banco" class="block text-sm font-medium text-gray-700 mb-1">Banco</label>
                <input type="text" id="banco" name="dados_financeiros[banco]" value="<?php echo htmlspecialchars($dados_financeiros['banco'] ?? ''); ?>" class="w-full border-gray-300 rounded-lg shadow-sm p-2 text-sm">
            </div>
            <div>
                <label for="agencia" class="block text-sm font-medium text-gray-700 mb-1">Agência</label>
                <input type="text" id="agencia" name="dados_financeiros[agencia]" value="<?php echo htmlspecialchars($dados_financeiros['agencia'] ?? ''); ?>" class="w-full border-gray-300 rounded-lg shadow-sm p-2 text-sm">
            </div>
            <div>
                <label for="conta" class="block text-sm font-medium text-gray-700 mb-1">Conta Corrente</label>
                <input type="text" id="conta" name="dados_financeiros[conta]" value="<?php echo htmlspecialchars($dados_financeiros['conta'] ?? ''); ?>" class="w-full border-gray-300 rounded-lg shadow-sm p-2 text-sm">
            </div>
            <div>
                <label for="tipo_conta" class="block text-sm font-medium text-gray-700 mb-1">Tipo de Conta</label>
                <select id="tipo_conta" name="dados_financeiros[tipo_conta]" class="w-full border-gray-300 rounded-lg shadow-sm p-2 text-sm">
                    <option value="Corrente" <?php echo (($dados_financeiros['tipo_conta'] ?? '') === 'Corrente') ? 'selected' : ''; ?>>Corrente</option>
                    <option value="Poupança" <?php echo (($dados_financeiros['tipo_conta'] ?? '') === 'Poupança') ? 'selected' : ''; ?>>Poupança</option>
                    <option value="PJ" <?php echo (($dados_financeiros['tipo_conta'] ?? '') === 'PJ') ? 'selected' : ''; ?>>Pessoa Jurídica</option>
                </select>
            </div>
            <div class="md:col-span-3 grid grid-cols-3 gap-4">
                <div class="col-span-2">
                    <label for="chave_pix" class="block text-sm font-medium text-gray-700 mb-1">Chave PIX</label>
                    <input type="text" id="chave_pix" name="dados_financeiros[chave_pix]" value="<?php echo htmlspecialchars($dados_financeiros['chave_pix'] ?? ''); ?>" class="w-full border-gray-300 rounded-lg shadow-sm p-2 text-sm" placeholder="CPF, CNPJ, E-mail, Telefone ou Chave Aleatória">
                </div>
                <div>
                    <label for="tipo_chave_pix" class="block text-sm font-medium text-gray-700 mb-1">Tipo de Chave</label>
                    <select id="tipo_chave_pix" name="dados_financeiros[tipo_chave_pix]" class="w-full border-gray-300 rounded-lg shadow-sm p-2 text-sm">
                        <option value="CPF" <?php echo (($dados_financeiros['tipo_chave_pix'] ?? '') === 'CPF') ? 'selected' : ''; ?>>CPF</option>
                        <option value="CNPJ" <?php echo (($dados_financeiros['tipo_chave_pix'] ?? '') === 'CNPJ') ? 'selected' : ''; ?>>CNPJ</option>
                        <option value="Email" <?php echo (($dados_financeiros['tipo_chave_pix'] ?? '') === 'Email') ? 'selected' : ''; ?>>E-mail</option>
                        <option value="Telefone" <?php echo (($dados_financeiros['tipo_chave_pix'] ?? '') === 'Telefone') ? 'selected' : ''; ?>>Telefone</option>
                        <option value="Aleatoria" <?php echo (($dados_financeiros['tipo_chave_pix'] ?? '') === 'Aleatoria') ? 'selected' : ''; ?>>Aleatória</option>
                    </select>
                </div>
            </div>
            <div>
                <label for="condicoes_pagamento" class="block text-sm font-medium text-gray-700 mb-1">Condições de Pagamento</label>
                <input type="text" id="condicoes_pagamento" name="dados_financeiros[condicoes_pagamento]" value="<?php echo htmlspecialchars($dados_financeiros['condicoes_pagamento'] ?? ''); ?>" class="w-full border-gray-300 rounded-lg shadow-sm p-2 text-sm" placeholder="Ex: 30 dias">
            </div>
            <div>
                <label for="limite_credito" class="block text-sm font-medium text-gray-700 mb-1">Limite de Crédito (R$)</label>
                <input type="text" id="limite_credito" name="dados_financeiros[limite_credito]" value="<?php echo htmlspecialchars($dados_financeiros['limite_credito'] ?? ''); ?>" class="w-full border-gray-300 rounded-lg shadow-sm p-2 text-sm">
            </div>
        </div>

        <!-- 5. Documentação -->
        <h3 class="text-lg font-semibold text-gray-700 mt-6 mb-4 pt-4 border-t">5. Documentação</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="doc_contrato_social" class="block text-sm font-medium text-gray-700 mb-1">Contrato Social (PDF)</label>
                <input type="file" id="doc_contrato_social" name="documentacao[contrato_social]" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-sky-50 file:text-sky-700 hover:file:bg-sky-100">
            </div>
            <div>
                <label for="doc_certidoes" class="block text-sm font-medium text-gray-700 mb-1">Certidões Negativas (PDF, ZIP)</label>
                <input type="file" id="doc_certidoes" name="documentacao[certidoes]" multiple class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-sky-50 file:text-sky-700 hover:file:bg-sky-100">
            </div>
        </div>

        <!-- 6. Informações Comerciais -->
        <h3 class="text-lg font-semibold text-gray-700 mt-6 mb-4 pt-4 border-t">6. Informações Comerciais</h3>
        <div class="space-y-4">
            <div>
                <label for="produtos_servicos" class="block text-sm font-medium text-gray-700 mb-1">Produtos / Serviços Fornecidos</label>
                <textarea id="produtos_servicos" name="info_comerciais[produtos_servicos]" rows="3" class="w-full border-gray-300 rounded-lg shadow-sm p-2 text-sm"><?php echo htmlspecialchars($info_comerciais['produtos_servicos'] ?? ''); ?></textarea>
            </div>
            <div>
                <label for="observacoes_internas" class="block text-sm font-medium text-gray-700 mb-1">Observações Internas</label>
                <textarea id="observacoes_internas" name="info_comerciais[observacoes_internas]" rows="3" class="w-full border-gray-300 rounded-lg shadow-sm p-2 text-sm"><?php echo htmlspecialchars($info_comerciais['observacoes_internas'] ?? ''); ?></textarea>
            </div>
        </div>

        <!-- 7. Status -->
        <h3 class="text-lg font-semibold text-gray-700 mt-6 mb-4 pt-4 border-t">7. Status</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Situação <span class="text-red-500">*</span></label>
                <select id="status" name="status" required class="w-full border-gray-300 rounded-lg shadow-sm p-2 text-sm">
                    <option value="Ativo" <?php echo (($fornecedor['status'] ?? 'Ativo') === 'Ativo') ? 'selected' : ''; ?>>Ativo</option>
                    <option value="Inativo" <?php echo (($fornecedor['status'] ?? '') === 'Inativo') ? 'selected' : ''; ?>>Inativo</option>
                    <option value="Em avaliação" <?php echo (($fornecedor['status'] ?? '') === 'Em avaliação') ? 'selected' : ''; ?>>Em avaliação</option>
                </select>
            </div>
            <div>
                <label for="motivo_inativacao" class="block text-sm font-medium text-gray-700 mb-1">Motivo da Inativação</label>
                <input type="text" id="motivo_inativacao" name="motivo_inativacao" value="<?php echo htmlspecialchars($fornecedor['motivo_inativacao'] ?? ''); ?>" class="w-full border-gray-300 rounded-lg shadow-sm p-2 text-sm">
            </div>
            <div>
                <label for="data_inativacao" class="block text-sm font-medium text-gray-700 mb-1">Data da Inativação</label>
                <input type="date" id="data_inativacao" name="data_inativacao" value="<?php echo htmlspecialchars($fornecedor['data_inativacao'] ?? ''); ?>" class="w-full border-gray-300 rounded-lg shadow-sm p-2 text-sm">
            </div>
        </div>

        <!-- Botões de Ação -->
        <div class="mt-6 pt-4 border-t border-gray-200 flex justify-end gap-3">
            <!-- O botão Voltar foi substituído por um botão Cancelar para o modal -->
            <button type="button" id="cancel-form-btn" class="bg-gray-200 text-gray-800 px-4 py-2 rounded-lg hover:bg-gray-300 font-semibold text-sm">
                Cancelar
            </button>
            <button type="submit" class="bg-sky-600 text-white px-6 py-2 rounded-lg hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-500 font-semibold text-sm">
                <?php echo $isEdit ? 'Atualizar Fornecedor' : 'Salvar Fornecedor'; ?>
            </button>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // --- Lógica para busca de CEP ---
        const buscarCepBtn = document.getElementById('buscar-cep-btn');
        if (buscarCepBtn) {
            buscarCepBtn.addEventListener('click', async () => {
                const cepInput = document.getElementById('cep');
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

                    document.getElementById('logradouro').value = data.logradouro || '';
                    document.getElementById('bairro').value = data.bairro || '';
                    document.getElementById('cidade').value = data.localidade || '';
                    document.getElementById('uf').value = data.uf || '';
                    document.getElementById('numero').focus(); // Foca no campo de número

                } catch (error) {
                    alert(`Erro ao buscar CEP: ${error.message}`);
                }
            });
        }

        // --- Lógica para busca de CNPJ ---
        const buscarCnpjBtn = document.getElementById('buscar-cnpj-btn');
        if (buscarCnpjBtn) {
            const cnpjInput = document.getElementById('cnpj');
            const searchIcon = document.getElementById('cnpj-search-icon');
            const loadingSpinner = document.getElementById('cnpj-loading-spinner');

            buscarCnpjBtn.addEventListener('click', async () => {
                const cnpj = cnpjInput.value.replace(/\D/g, '');

                if (cnpj.length !== 14) {
                    alert('Por favor, digite um CNPJ válido com 14 dígitos.');
                    return;
                }

                searchIcon.classList.add('hidden');
                loadingSpinner.classList.remove('hidden');
                buscarCnpjBtn.disabled = true;

                try {
                    // Usando um proxy para evitar problemas de CORS, se necessário.
                    // Em ambiente de desenvolvimento, pode funcionar direto.
                    const response = await fetch(`https://brasilapi.com.br/api/cnpj/v1/${cnpj}`);
                    if (!response.ok) {
                        const errorData = await response.json();
                        throw new Error(errorData.message || 'CNPJ não encontrado ou inválido.');
                    }

                    const data = await response.json();

                    // Preenche os campos do formulário com os dados da API
                    document.getElementById('nome').value = data.razao_social || '';
                    document.getElementById('email').value = data.email || '';
                    document.getElementById('telefone').value = data.ddd_telefone_1 || '';
                    document.getElementById('cep').value = data.cep.replace(/\D/g, '') || '';
                    document.getElementById('logradouro').value = data.logradouro || '';
                    document.getElementById('numero').value = data.numero || '';
                    document.getElementById('complemento').value = data.complemento || '';
                    document.getElementById('bairro').value = data.bairro || '';
                    document.getElementById('cidade').value = data.municipio || '';
                    document.getElementById('uf').value = data.uf || '';
                    document.getElementById('inscricao_estadual').value = data.inscricao_estadual || '';

                } catch (error) {
                    alert(`Erro ao buscar CNPJ: ${error.message}`);
                } finally {
                    searchIcon.classList.remove('hidden');
                    loadingSpinner.classList.add('hidden');
                    buscarCnpjBtn.disabled = false;
                }
            });
        }
    });
</script>