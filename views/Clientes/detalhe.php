<?php
// Decodifica os campos JSON para uso na view
$enderecos = json_decode($cliente['enderecos_json'] ?? '{}', true) ?? [];
$contatos = json_decode($cliente['contatos_json'] ?? '{}', true) ?? [];
$financeiro = json_decode($cliente['financeiro_json'] ?? '{}', true) ?? [];
$comercial = json_decode($cliente['comercial_json'] ?? '{}', true) ?? [];
?>
<div class="flex justify-between items-center mb-6">
    <div>
        <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Detalhes do Cliente</h2>
        <p class="text-gray-600 dark:text-gray-400">Histórico de interações e informações de contato.</p>
    </div>
    <a href="<?php echo BASE_URL; ?>/clientes" class="bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 px-4 py-2 rounded-md hover:bg-gray-300 dark:hover:bg-gray-600 font-medium transition-colors">
        &larr; Voltar para a Lista
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Coluna de Informações do Cliente -->
    <div class="lg:col-span-1 bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md border border-gray-200 dark:border-gray-700">
        <h3 class="text-xl font-semibold mb-4 border-b dark:border-gray-700 pb-2 text-gray-800 dark:text-gray-100"><?php echo htmlspecialchars($cliente['nome'] ?? ''); ?></h3>
        <div class="space-y-4 text-sm">
            <div>
                <p class="font-medium text-gray-500 dark:text-gray-400"><?php echo (strtolower($cliente['tipo_cliente'] ?? '') === 'fisica') ? 'CPF' : 'CNPJ'; ?></p>
                <p class="text-gray-800 dark:text-gray-200">
                    <?php
                    $cnpj_cpf = $cliente['cnpj_cpf'] ?? '';
                    if (!empty($cnpj_cpf)) {
                        $cleaned = preg_replace('/\D/', '', $cnpj_cpf);
                        if (strlen($cleaned) == 14) { // CNPJ
                            echo vsprintf('%s%s.%s%s%s.%s%s%s/%s%s%s%s-%s%s', str_split($cleaned));
                        } elseif (strlen($cleaned) == 11) { // CPF
                            echo vsprintf('%s%s%s.%s%s%s.%s%s%s-%s%s', str_split($cleaned));
                        } else {
                            echo htmlspecialchars($cnpj_cpf);
                        }
                    } else {
                        echo 'Não informado';
                    }
                    ?>
                </p>
            </div>
            <div>
                <p class="font-medium text-gray-500 dark:text-gray-400">Contato Responsável</p>
                <p class="text-gray-800 dark:text-gray-200"><?php echo htmlspecialchars($contatos['responsavel']['nome'] ?? ($cliente['contato_principal'] ?? 'Não informado')); ?></p>
            </div>
            <div>
                <p class="font-medium text-gray-500 dark:text-gray-400">E-mail Principal</p>
                <p class="text-gray-800 dark:text-gray-200"><?php echo htmlspecialchars($contatos['principal']['email'] ?? ($cliente['email'] ?? 'Não informado')); ?></p>
            </div>
            <div>
                <p class="font-medium text-gray-500 dark:text-gray-400">Telefone Principal</p>
                <p class="text-gray-800 dark:text-gray-200"><?php echo htmlspecialchars($contatos['principal']['telefone'] ?? ($cliente['telefone'] ?? 'Não informado')); ?></p>
            </div>
            <div>
                <p class="font-medium text-gray-500 dark:text-gray-400">Endereço Principal</p>
                <?php
                    $enderecoParts = [];
                    if (!empty($enderecos['principal']['logradouro'])) $enderecoParts[] = $enderecos['principal']['logradouro'];
                    if (!empty($enderecos['principal']['numero'])) $enderecoParts[] = 'nº ' . $enderecos['principal']['numero'];
                    if (!empty($enderecos['principal']['cidade'])) $enderecoParts[] = $enderecos['principal']['cidade'] . '/' . ($enderecos['principal']['estado'] ?? '');
                    $enderecoCompleto = implode(', ', $enderecoParts);
                ?>
                <p class="text-gray-800 dark:text-gray-200"><?php echo htmlspecialchars($enderecoCompleto ?: ($cliente['endereco'] ?? 'Não informado')); ?></p>
            </div>
            <div>
                <p class="font-medium text-gray-500 dark:text-gray-400">Status</p>
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                    <?php
                    $st = $cliente['status'] ?? '';
                    if ($st === 'Potencial') echo 'bg-blue-100 text-blue-800';
                    elseif ($st === 'Em negociação') echo 'bg-yellow-100 text-yellow-800';
                    elseif ($st === 'Ativo') echo 'bg-green-100 text-green-800';
                    elseif ($st === 'Inativo') echo 'bg-red-100 text-red-800';
                    else echo 'bg-gray-100 text-gray-800';
                    ?>">
                    <?php echo htmlspecialchars($cliente['status'] ?? ''); ?>
                </span>
            </div>
            <div class="mt-6 pt-6 border-t flex flex-col sm:flex-row gap-2">
            <?php
            $status = isset($cliente['status']) ? strtolower($cliente['status']) : '';
            if ($status === 'inativo') : ?>
                <form action="<?php echo BASE_URL; ?>/clientes/restaurar/<?php echo $cliente['id']; ?>" method="POST" onsubmit="return confirm('Deseja restaurar este cliente?');" class="w-full">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <button type="submit" title="Restaurar Cliente" class="w-full bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 font-medium flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Restaurar
                    </button>
                </form>
            <?php else : ?>
                <a href="<?php echo BASE_URL; ?>/clientes/getFormForEdit/<?php echo $cliente['id']; ?>" title="Editar Cliente" class="w-full bg-sky-600 text-white px-4 py-2 rounded-md hover:bg-sky-700 font-medium flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Editar
                </a>
                <button type="button" id="open-interaction-modal-btn" title="Registrar Nova Interação" class="w-full bg-teal-600 text-white px-3 py-1 rounded-md hover:bg-teal-700 font-medium flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                    Nova Interação
                </button>
                <form action="<?php echo BASE_URL; ?>/clientes/excluir/<?php echo $cliente['id']; ?>" method="POST" onsubmit="return confirm('Tem certeza que deseja arquivar este cliente?');" class="w-full">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <button type="submit" title="Arquivar Cliente" class="w-full bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 font-medium flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                        </svg>
                        Arquivar
                    </button>
                </form>
            <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="lg:col-span-2 bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md border border-gray-200 dark:border-gray-700">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-100">Histórico de Interações</h3>
            <?php if (!empty($interacoes)) : ?>
                <form action="<?php echo BASE_URL; ?>/clientes/limparHistorico/<?php echo $cliente['id']; ?>" method="POST" onsubmit="return confirm('Tem certeza que deseja apagar todo o histórico de interações deste cliente?');">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <button type="submit" class="text-sm text-red-600 hover:text-red-800 font-medium flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Limpar Histórico
                    </button>
                </form>
            <?php endif; ?>
        </div>
        <div class="flow-root">
            <ul role="list" class="-mb-8">
                <?php if (!empty($interacoes)) : ?>
                    <?php foreach ($interacoes as $index => $interacao) : ?>
                        <li>
                            <div class="relative pb-8">
                                <?php if ($index < count($interacoes) - 1) : ?>
                                    <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200 dark:bg-gray-700" aria-hidden="true"></span>
                                <?php endif; ?>
                                <div class="relative flex space-x-3">
                                    <div>
                                        <span class="h-8 w-8 rounded-full bg-gray-400 flex items-center justify-center ring-8 ring-white dark:ring-gray-800">
                                            <!-- Ícone pode ser dinâmico com base no tipo de interação -->
                                            <svg class="h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                                            </svg>
                                        </span>
                                    </div>
                                    <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                        <div>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                                <span class="font-medium text-gray-900 dark:text-gray-100"><?php echo htmlspecialchars($interacao['tipo_interacao'] ?? ''); ?></span>
                                                - por Usuário ID: <?php echo htmlspecialchars($interacao['usuario_id'] ?? ''); ?>
                                            </p>
                                            <p class="mt-1 text-sm text-gray-700 dark:text-gray-300"><?php echo nl2br(htmlspecialchars($interacao['descricao'] ?? '')); ?></p>
                                        </div>
                                        <div class="text-right text-sm whitespace-nowrap text-gray-500 dark:text-gray-400">
                                            <time datetime="<?php echo $interacao['data_interacao']; ?>"><?php echo date('d/m/Y H:i', strtotime($interacao['data_interacao'])); ?></time>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                <?php else : ?>
                    <li>
                        <p class="text-center text-gray-500 dark:text-gray-400">Nenhuma interação registrada para este cliente ainda.</p>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</div>

<!-- Seção de Abas com Informações Relacionadas -->
<div class="mt-6 bg-white dark:bg-gray-800 rounded-lg shadow-md border border-gray-200 dark:border-gray-700">
    <!-- Abas de Navegação -->
    <div class="border-b border-gray-200 dark:border-gray-700">
        <nav class="-mb-px flex space-x-6 px-6" aria-label="Tabs">
            <button type="button" data-tab="tab-detalhes" class="tab-button whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm border-sky-500 text-sky-600">Informações Detalhadas</button>
            <button type="button" data-tab="tab-projetos" class="tab-button whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:border-gray-300">Projetos</button>
            <button type="button" data-tab="tab-contratos" class="tab-button whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:border-gray-300">Contratos</button>
            <button type="button" data-tab="tab-financeiro-cliente" class="tab-button whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:border-gray-300">Financeiro</button>
        </nav>
    </div>

    <!-- Conteúdo das Abas -->
    <div class="p-6">
        <!-- Tab: Informações Detalhadas -->
        <div class="tab-content" id="tab-detalhes">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Endereço -->
                <div class="bg-gray-50 dark:bg-gray-900/50 p-4 rounded-lg">
                    <h4 class="font-semibold text-gray-700 dark:text-gray-300 mb-2 flex items-center"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg> Endereço Principal</h4>
                    <?php if (!empty($enderecos['principal'])): ?>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            <?php echo htmlspecialchars($enderecos['principal']['logradouro'] ?? ''); ?>, 
                            <?php echo htmlspecialchars($enderecos['principal']['numero'] ?? ''); ?>
                            <?php echo !empty($enderecos['principal']['complemento']) ? ' - ' . htmlspecialchars($enderecos['principal']['complemento']) : ''; ?>
                        </p>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            <?php echo htmlspecialchars($enderecos['principal']['bairro'] ?? ''); ?> - 
                            <?php echo htmlspecialchars($enderecos['principal']['cidade'] ?? ''); ?>/<?php echo htmlspecialchars($enderecos['principal']['estado'] ?? ''); ?>
                        </p>
                        <p class="text-sm text-gray-600 dark:text-gray-400">CEP: <?php echo htmlspecialchars($enderecos['principal']['cep'] ?? ''); ?></p>
                    <?php else: ?>
                        <p class="text-sm text-gray-500 dark:text-gray-500 italic">Nenhum endereço cadastrado.</p>
                    <?php endif; ?>
                </div>

                <!-- Contatos Adicionais -->
                <div class="bg-gray-50 dark:bg-gray-900/50 p-4 rounded-lg">
                    <h4 class="font-semibold text-gray-700 dark:text-gray-300 mb-2 flex items-center"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg> Contatos Extras</h4>
                    <ul class="text-sm text-gray-600 dark:text-gray-400 space-y-1">
                        <?php if (!empty($contatos['principal']['telefone_secundario'])): ?>
                            <li><strong>Tel. Secundário:</strong> <?php echo htmlspecialchars($contatos['principal']['telefone_secundario']); ?></li>
                        <?php endif; ?>
                        <?php if (!empty($contatos['principal']['whatsapp'])): ?>
                            <li><strong>WhatsApp:</strong> <?php echo htmlspecialchars($contatos['principal']['whatsapp']); ?></li>
                        <?php endif; ?>
                        <?php if (!empty($contatos['principal']['email_financeiro'])): ?>
                            <li><strong>E-mail Financeiro:</strong> <?php echo htmlspecialchars($contatos['principal']['email_financeiro']); ?></li>
                        <?php endif; ?>
                    </ul>
                    <?php if (!empty($contatos['responsavel']['nome'])): ?>
                        <div class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-700">
                            <p class="font-medium text-gray-700 dark:text-gray-400 text-xs uppercase mb-1">Responsável</p>
                            <p class="text-sm text-gray-800 dark:text-gray-200 font-medium"><?php echo htmlspecialchars($contatos['responsavel']['nome'] ?? ''); ?> 
                            <span class="text-gray-500 dark:text-gray-500 font-normal text-xs">(<?php echo htmlspecialchars($contatos['responsavel']['cargo'] ?? ''); ?>)</span></p>
                            <p class="text-sm text-gray-600 dark:text-gray-400"><?php echo htmlspecialchars($contatos['responsavel']['email'] ?? ''); ?></p>
                            <p class="text-sm text-gray-600 dark:text-gray-400"><?php echo htmlspecialchars($contatos['responsavel']['telefone'] ?? ''); ?></p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Financeiro -->
                <div class="bg-gray-50 dark:bg-gray-900/50 p-4 rounded-lg">
                    <h4 class="font-semibold text-gray-700 dark:text-gray-300 mb-2 flex items-center"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg> Dados Financeiros</h4>
                    <ul class="text-sm text-gray-600 dark:text-gray-400 space-y-1">
                        <li><strong>Limite de Crédito:</strong> R$ <?php echo htmlspecialchars(number_format((float)($financeiro['limite_credito'] ?? 0), 2, ',', '.')); ?></li>
                        <li><strong>Condição Pagto:</strong> <?php echo htmlspecialchars($financeiro['condicao_pagamento'] ?? 'N/A'); ?></li>
                        <li><strong>Forma Pagto:</strong> <?php echo htmlspecialchars($financeiro['forma_pagamento'] ?? 'N/A'); ?></li>
                        <?php if (!empty($financeiro['chave_pix'])): ?>
                            <li><strong>Chave PIX:</strong> <?php echo htmlspecialchars($financeiro['chave_pix']); ?></li>
                        <?php endif; ?>
                    </ul>
                </div>

                <!-- Comercial -->
                <div class="bg-gray-50 dark:bg-gray-900/50 p-4 rounded-lg">
                    <h4 class="font-semibold text-gray-700 dark:text-gray-300 mb-2 flex items-center"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg> Dados Comerciais</h4>
                    <ul class="text-sm text-gray-600 dark:text-gray-400 space-y-1">
                        <li><strong>Representante:</strong> <?php echo htmlspecialchars($comercial['representante_comercial'] ?? 'N/A'); ?></li>
                        <li><strong>Região:</strong> <?php echo htmlspecialchars($comercial['regiao_atuacao'] ?? 'N/A'); ?></li>
                        <?php if (!empty($comercial['produtos_servicos_interesse'])): ?>
                            <li class="mt-2 pt-2 border-t border-gray-200 dark:border-gray-700"><strong>Interesse:</strong><br> <span class="italic"><?php echo nl2br(htmlspecialchars($comercial['produtos_servicos_interesse'])); ?></span></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Tab: Projetos -->
        <div class="tab-content hidden" id="tab-projetos">
            <h4 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">Projetos Vinculados</h4>
            <?php if (!empty($projetos)): ?>
                <div class="overflow-x-auto border dark:border-gray-700 rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900/50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Nome do Projeto</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Status</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            <?php foreach ($projetos as $projeto): ?>
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-800 dark:text-gray-200"><?php echo htmlspecialchars($projeto['nome'] ?? 'Sem nome'); ?></td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400"><?php echo htmlspecialchars($projeto['status']); ?></td>
                                    <td class="px-4 py-3 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="<?php echo BASE_URL; ?>/projetos/detalhe/<?php echo $projeto['id']; ?>" class="text-sky-600 dark:text-sky-400 hover:text-sky-800 dark:hover:text-sky-300">Ver Detalhes</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-center text-gray-500 dark:text-gray-400 py-4">Nenhum projeto vinculado a este cliente.</p>
            <?php endif; ?>
        </div>

        <!-- Tab: Contratos -->
        <div class="tab-content hidden" id="tab-contratos">
            <h4 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">Contratos Vinculados</h4>
            <?php if (!empty($contratos)): ?>
                <div class="overflow-x-auto border dark:border-gray-700 rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900/50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Contrato / Título</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Valor</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Vencimento</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Status</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            <?php foreach ($contratos as $contrato): ?>
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-800 dark:text-gray-200">
                                        <div class="font-bold text-sky-600"><?php echo htmlspecialchars($contrato['numero_contrato'] ?: '#' . $contrato['id']); ?></div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">
                                            <?php echo htmlspecialchars($contrato['titulo'] ?: mb_substr($contrato['objeto'] ?? '', 0, 50) . '...'); ?>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">R$ <?php echo number_format($contrato['valor'], 2, ',', '.'); ?></td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400"><?php echo $contrato['vencimento'] ? date('d/m/Y', strtotime($contrato['vencimento'])) : 'N/A'; ?></td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400"><?php echo htmlspecialchars($contrato['status']); ?></td>
                                    <td class="px-4 py-3 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="<?php echo BASE_URL; ?>/contratos/detalhe/<?php echo $contrato['id']; ?>" class="text-sky-600 dark:text-sky-400 hover:text-sky-800 dark:hover:text-sky-300">Ver Detalhes</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-center text-gray-500 dark:text-gray-400 py-4">Nenhum contrato vinculado a este cliente.</p>
            <?php endif; ?>
        </div>

        <!-- Tab: Financeiro -->
        <div class="tab-content hidden" id="tab-financeiro-cliente">
            <div class="flex justify-between items-center mb-4">
                <div>
                    <h4 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Histórico de Recebimentos</h4>
                    <p class="text-sm text-gray-500 dark:text-gray-500 italic">Exibindo apenas transações vinculadas a este cliente</p>
                </div>
            </div>
            
            <?php if (!empty($historicoFinanceiro)): ?>
                <div class="overflow-x-auto border dark:border-gray-700 rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900/50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Data/Venc.</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Data/Pagamento</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Descrição</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Valor</th>
                                <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            <?php foreach ($historicoFinanceiro as $trans): ?>
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                                        <?php echo !empty($trans['vencimento']) ? date('d/m/Y', strtotime($trans['vencimento'])) : 'N/A'; ?>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                                        <?php echo !empty($trans['data_pagamento']) ? date('d/m/Y', strtotime($trans['data_pagamento'])) : 'N/A'; ?>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-800 dark:text-gray-200 font-medium">
                                        <?php echo htmlspecialchars($trans['descricao'] ?: 'Sem descrição'); ?>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100 font-bold">
                                        R$ <?php echo number_format($trans['valor'], 2, ',', '.'); ?>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-center">
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full <?php echo $trans['status'] === 'Pago' ? 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400' : 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-400'; ?>">
                                            <?php echo $trans['status']; ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            <!-- Controles de Paginação Financeiro -->
            <?php if (isset($totalPaginasFin) && $totalPaginasFin > 1): ?>
                <div class="flex justify-end mt-6">
                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                        <?php if ($paginaAtualFin > 1): ?>
                            <a href="?page_fin=<?php echo $paginaAtualFin - 1; ?>&tab=tab-financeiro-cliente" class="relative inline-flex items-center px-3 py-2 rounded-l-md border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm font-bold text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700">
                                <span class="sr-only">Anterior</span>
                                <i class='bx bx-chevron-left text-xl'></i>
                            </a>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $totalPaginasFin; $i++): ?>
                            <a href="?page_fin=<?php echo $i; ?>&tab=tab-financeiro-cliente" class="relative inline-flex items-center px-4 py-2 border border-gray-200 dark:border-gray-700 text-sm font-bold <?php echo $i == $paginaAtualFin ? 'text-white bg-blue-600 border-blue-600 z-10' : 'bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700'; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>

                        <?php if ($paginaAtualFin < $totalPaginasFin): ?>
                            <a href="?page_fin=<?php echo $paginaAtualFin + 1; ?>&tab=tab-financeiro-cliente" class="relative inline-flex items-center px-3 py-2 rounded-r-md border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm font-bold text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700">
                                <span class="sr-only">Próxima</span>
                                <i class='bx bx-chevron-right text-xl'></i>
                            </a>
                        <?php endif; ?>
                    </nav>
                </div>
            <?php endif; ?>
            <?php else: ?>
                <p class="text-center text-gray-500 dark:text-gray-400 py-4">Nenhuma movimentação financeira encontrada.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal de Nova Interação -->
<div id="interaction-modal" class="fixed inset-0 z-50 hidden overflow-y-auto" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Background overlay -->
        <div id="modal-bg" class="fixed inset-0 bg-gray-900/70 backdrop-blur-sm transition-opacity"></div>

        <!-- Center modal -->
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="relative z-10 inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form action="<?php echo BASE_URL; ?>/clientes/registrarInteracao" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <input type="hidden" name="cliente_id" id="cliente_id" value="<?php echo $cliente['id']; ?>">

                <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-teal-100 dark:bg-teal-900/30 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-teal-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100">Nova Interação</h3>
                            <div class="mt-2 space-y-4">
                                <div>
                                    <label for="tipo_interacao" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tipo de Interação</label>
                                    <select name="tipo_interacao" id="tipo_interacao" required class="mt-1 block w-full py-2 px-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:outline-none focus:ring-teal-500 focus:border-teal-500 sm:text-sm transition-colors">
                                        <option value="Telefone">Telefone</option>
                                        <option value="E-mail">E-mail</option>
                                        <option value="WhatsApp">WhatsApp</option>
                                        <option value="Reunião">Reunião</option>
                                        <option value="Visita">Visita</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="descricao" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Descrição</label>
                                    <textarea name="descricao" id="descricao" rows="3" required class="mt-1 block w-full py-2 px-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:outline-none focus:ring-teal-500 focus:border-teal-500 sm:text-sm transition-colors"></textarea>
                                </div>
                                <div>
                                    <label for="data_interacao" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Data</label>
                                    <input type="datetime-local" name="data_interacao" id="data_interacao" value="<?php echo date('Y-m-d\TH:i'); ?>" class="mt-1 block w-full py-2 px-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:outline-none focus:ring-teal-500 focus:border-teal-500 sm:text-sm transition-colors">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-900/50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t dark:border-gray-700">
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-teal-600 text-base font-medium text-white hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Salvar
                    </button>
                    <button type="button" id="close-interaction-modal-btn" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-700 text-base font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const modal = document.getElementById('interaction-modal');
        const openBtn = document.getElementById('open-interaction-modal-btn');
        const closeBtn = document.getElementById('close-interaction-modal-btn');
        const modalBg = document.getElementById('modal-bg');
        const clienteSelect = document.getElementById('cliente_id');

        if (openBtn && modal) {
            openBtn.addEventListener('click', (e) => {
                e.preventDefault();
                // Pré-seleciona o cliente atual no modal
                if (clienteSelect) {
                    clienteSelect.value = "<?php echo $cliente['id']; ?>";
                }
                modal.classList.remove('hidden');
            });
        }
        if (closeBtn && modal) {
            closeBtn.addEventListener('click', () => modal.classList.add('hidden'));
        }
        if (modalBg && modal) {
            modalBg.addEventListener('click', () => modal.classList.add('hidden'));
        }

        // Lógica para o botão "Editar" do cliente
        const editClientBtn = document.querySelector('.edit-cliente-btn');
        if (editClientBtn) {
            editClientBtn.addEventListener('click', () => {
                const clientId = editClientBtn.dataset.clienteId;
                window.openClientFormModal(clientId); // Chama a função global para abrir o modal de edição
            });
        }

        // Lógica para as abas de informações detalhadas
        const tabButtons = document.querySelectorAll('.tab-button');
        const tabContents = document.querySelectorAll('.tab-content');

        tabButtons.forEach(button => {
            button.addEventListener('click', () => {
                // Desativa todos os botões e esconde todos os conteúdos
                tabButtons.forEach(btn => {
                    btn.classList.remove('border-sky-500', 'text-sky-600');
                    btn.classList.add('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
                });
                tabContents.forEach(content => {
                    content.classList.add('hidden');
                });

                // Ativa o botão clicado e mostra o conteúdo correspondente
                button.classList.add('border-sky-500', 'text-sky-600');
                button.classList.remove('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
                const tabId = button.getAttribute('data-tab');
                const activeTab = document.getElementById(tabId);
                if (activeTab) {
                    activeTab.classList.remove('hidden');
                }
            });
        });

        // Ativa a aba inicial baseada no parâmetro 'tab' da URL para manter a visualização após paginar
        const urlParams = new URLSearchParams(window.location.search);
        const activeTabParam = urlParams.get('tab');
        if (activeTabParam) {
            const tabToActivate = document.querySelector(`.tab-button[data-tab="${activeTabParam}"]`);
            if (tabToActivate) {
                tabToActivate.click();
            }
        }
    });
</script>