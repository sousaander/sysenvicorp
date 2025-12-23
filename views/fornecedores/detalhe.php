<?php
// Decodifica os dados JSON para exibição, com segurança
$endereco = !empty($fornecedor['endereco_json']) ? json_decode($fornecedor['endereco_json'], true) : [];
$contato = !empty($fornecedor['contato_json']) ? json_decode($fornecedor['contato_json'], true) : [];
$dados_financeiros = !empty($fornecedor['dados_financeiros_json']) ? json_decode($fornecedor['dados_financeiros_json'], true) : [];
$info_comerciais = !empty($fornecedor['info_comerciais_json']) ? json_decode($fornecedor['info_comerciais_json'], true) : [];
?>

<div class="flex justify-between items-start mb-6">
    <div>
        <h2 class="text-2xl font-bold text-gray-800"><?php echo htmlspecialchars($fornecedor['nome']); ?></h2>
        <p class="text-gray-600">Visão completa do fornecedor, incluindo histórico e documentos.</p>
    </div>
    <a href="<?php echo BASE_URL; ?>/fornecedores" class="bg-gray-200 text-gray-800 px-4 py-2 rounded-md hover:bg-gray-300 font-medium flex-shrink-0">
        &larr; Voltar para a Lista
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Coluna de Informações Principais -->
    <div class="lg:col-span-1 bg-white p-6 rounded-lg shadow-md">
        <h3 class="text-xl font-semibold mb-4 border-b pb-2">Dados Cadastrais</h3>
        <div class="space-y-4 text-sm">
            <div>
                <p class="font-medium text-gray-500"><?php echo ($fornecedor['tipo_pessoa'] ?? 'Juridica') === 'Juridica' ? 'CNPJ' : 'CPF'; ?></p>
                <p class="text-gray-800"><?php
                                            $cnpj_cpf = $fornecedor['cnpj_cpf'] ?? '';
                                            $formatted_cnpj_cpf = 'Não informado';
                                            if (!empty($cnpj_cpf)) {
                                                $cleaned = preg_replace('/\D/', '', $cnpj_cpf);
                                                if (strlen($cleaned) == 14) { // CNPJ
                                                    $formatted_cnpj_cpf = vsprintf('%s%s.%s%s%s.%s%s%s/%s%s%s%s-%s%s', str_split($cleaned));
                                                } elseif (strlen($cleaned) == 11) { // CPF
                                                    $formatted_cnpj_cpf = vsprintf('%s%s%s.%s%s%s.%s%s%s-%s%s', str_split($cleaned));
                                                } else {
                                                    $formatted_cnpj_cpf = $cleaned; // Exibe o número limpo se o tamanho for inválido
                                                }
                                            }
                                            echo htmlspecialchars($formatted_cnpj_cpf);
                                            ?></p>
            </div>
            <div>
                <p class="font-medium text-gray-500">Nome Fantasia</p>
                <p class="text-gray-800"><?php echo htmlspecialchars($fornecedor['nome_fantasia'] ?: 'Não informado'); ?></p>
            </div>
            <div>
                <p class="font-medium text-gray-500">Contato Principal</p>
                <p class="text-gray-800"><?php echo htmlspecialchars($contato['representante_nome'] ?? ($fornecedor['contato_principal'] ?? 'Não informado')); ?></p>
            </div>
            <div>
                <p class="font-medium text-gray-500">E-mail</p>
                <p class="text-gray-800"><?php echo htmlspecialchars($contato['email_principal'] ?? ($fornecedor['email'] ?? 'Não informado')); ?></p>
            </div>
            <div>
                <p class="font-medium text-gray-500">Telefone</p>
                <p class="text-gray-800"><?php echo htmlspecialchars($contato['telefone_comercial'] ?? ($fornecedor['telefone'] ?? 'Não informado')); ?></p>
            </div>
            <div>
                <p class="font-medium text-gray-500">Endereço</p>
                <p class="text-gray-800">
                    <?php
                    echo htmlspecialchars($endereco['logradouro'] ?? '') . ', ' . htmlspecialchars($endereco['numero'] ?? '');
                    echo '<br>' . htmlspecialchars($endereco['bairro'] ?? '') . ', ' . htmlspecialchars($endereco['cidade'] ?? '') . ' - ' . htmlspecialchars($endereco['uf'] ?? '');
                    echo '<br>CEP: ' . htmlspecialchars($endereco['cep'] ?? '');
                    ?>
                </p>
            </div>
            <div>
                <p class="font-medium text-gray-500">Categoria</p>
                <p class="text-gray-800 font-semibold"><?php echo htmlspecialchars($fornecedor['categoria_fornecimento'] ?: 'Não informado'); ?></p>
            </div>
            <div>
                <p class="font-medium text-gray-500">Status</p>
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                    <?php
                    if ($fornecedor['status'] === 'Ativo') echo 'bg-green-100 text-green-800';
                    elseif ($fornecedor['status'] === 'Inativo') echo 'bg-red-100 text-red-800';
                    else echo 'bg-yellow-100 text-yellow-800';
                    ?>">
                    <?php echo htmlspecialchars($fornecedor['status']); ?>
                </span>
            </div>
        </div>
        <div class="mt-6 pt-6 border-t flex gap-2">
            <button id="open-edit-modal-btn" class="w-full text-center bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 font-medium">
                Editar Cadastro
            </button>
        </div>
    </div>

    <!-- Coluna de Históricos e Documentos -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Dados Financeiros -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h3 class="text-xl font-semibold mb-4">Dados Financeiros</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="font-medium text-gray-500">Banco</p>
                    <p class="text-gray-800"><?php echo htmlspecialchars($dados_financeiros['banco'] ?? 'Não informado'); ?></p>
                </div>
                <div>
                    <p class="font-medium text-gray-500">Agência / Conta</p>
                    <p class="text-gray-800"><?php echo htmlspecialchars($dados_financeiros['agencia'] ?? ''); ?> / <?php echo htmlspecialchars($dados_financeiros['conta'] ?? ''); ?></p>
                </div>
                <div>
                    <p class="font-medium text-gray-500">Chave PIX</p>
                    <p class="text-gray-800"><?php echo htmlspecialchars($dados_financeiros['chave_pix'] ?? 'Não informado'); ?> <span class="text-gray-500 text-xs">(<?php echo htmlspecialchars($dados_financeiros['tipo_chave_pix'] ?? 'N/A'); ?>)</span></p>
                </div>
                <div>
                    <p class="font-medium text-gray-500">Condições de Pagamento</p>
                    <p class="text-gray-800"><?php echo htmlspecialchars($dados_financeiros['condicoes_pagamento'] ?? 'Não informado'); ?></p>
                </div>
            </div>
        </div>
        <!-- Histórico de Compras -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h3 class="text-xl font-semibold mb-4">Histórico de Compras (Notas Fiscais Recentes)</h3>
            <div class="overflow-x-auto">
                <?php if (empty($historicoCompras)) : ?>
                    <p class="text-center text-gray-500 py-4">Nenhum histórico de compra encontrado.</p>
                <?php else : ?>
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Descrição</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Vencimento</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Valor</th>
                                <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($historicoCompras as $compra) : ?>
                                <tr>
                                    <td class="px-4 py-3 text-sm text-gray-800"><?php echo htmlspecialchars($compra['descricao']); ?></td>
                                    <td class="px-4 py-3 text-sm text-gray-500"><?php echo date('d/m/Y', strtotime($compra['vencimento'])); ?></td>
                                    <td class="px-4 py-3 text-sm text-right font-semibold">R$ <?php echo number_format($compra['valor'], 2, ',', '.'); ?></td>
                                    <td class="px-4 py-3 text-sm text-center"><?php echo htmlspecialchars($compra['status']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>

        <!-- Contratos e Documentos -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h3 class="text-xl font-semibold mb-4">Contratos e Documentos Anexados</h3>
            <div class="overflow-x-auto">
                <?php if (empty($contratos)) : ?>
                    <p class="text-center text-gray-500 py-4">Nenhum contrato vinculado a este fornecedor.</p>
                <?php else : ?>
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Objeto</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Vencimento</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($contratos as $contrato) : ?>
                                <tr>
                                    <td class="px-4 py-3 text-sm text-gray-800"><?php echo htmlspecialchars(substr($contrato['objeto'], 0, 50)) . '...'; ?></td>
                                    <td class="px-4 py-3 text-sm text-gray-500"><?php echo date('d/m/Y', strtotime($contrato['vencimento'])); ?></td>
                                    <td class="px-4 py-3 text-sm text-right">
                                        <a href="<?php echo BASE_URL; ?>/contratos/detalhe/<?php echo $contrato['id']; ?>" class="text-indigo-600 hover:text-indigo-800 mr-3">Ver Contrato</a>
                                        <?php if (!empty($contrato['documento_path'])) : ?>
                                            <a href="<?php echo BASE_URL; ?>/contratos/download/<?php echo htmlspecialchars($contrato['documento_path']); ?>" target="_blank" class="text-sky-600 hover:text-sky-800">Baixar Doc</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>

        <!-- Status e Ocorrências -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-semibold">Status e Ocorrências</h3>
                <button id="open-ocorrencia-modal-btn" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">+ Registrar Ocorrência</button>
            </div>
            <div class="flow-root">
                <ul role="list" class="-mb-8">
                    <?php if (!empty($ocorrencias)) : ?>
                        <?php foreach ($ocorrencias as $index => $ocorrencia) : ?>
                            <li>
                                <div class="relative pb-8">
                                    <?php if ($index < count($ocorrencias) - 1) : ?>
                                        <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                    <?php endif; ?>
                                    <div class="relative flex space-x-3">
                                        <div>
                                            <span class="h-8 w-8 rounded-full bg-gray-400 flex items-center justify-center ring-8 ring-white">
                                                <svg class="h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                                </svg>
                                            </span>
                                        </div>
                                        <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                            <div>
                                                <p class="text-sm text-gray-500">
                                                    <span class="font-medium text-gray-900"><?php echo htmlspecialchars($ocorrencia['tipo']); ?></span>
                                                    - por <?php echo htmlspecialchars($ocorrencia['responsavel']); ?>
                                                </p>
                                                <p class="mt-1 text-sm text-gray-700"><?php echo nl2br(htmlspecialchars($ocorrencia['descricao'])); ?></p>
                                            </div>
                                            <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                                <time datetime="<?php echo $ocorrencia['data']; ?>"><?php echo date('d/m/Y', strtotime($ocorrencia['data'])); ?></time>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <li>
                            <p class="text-center text-gray-500">Nenhuma ocorrência registrada para este fornecedor.</p>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Edição (reutiliza a lógica da página de listagem) -->
<div id="form-fornecedor-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-10 mx-auto p-5 border w-full max-w-4xl shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex justify-between items-center border-b pb-3">
                <h3 id="modal-title" class="text-xl font-bold text-gray-900">Editar Fornecedor</h3>
                <button id="close-modal-btn" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div id="modal-content" class="mt-4">
                <?php require ROOT_PATH . '/views/fornecedores/form.php'; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Registrar Ocorrência -->
<div id="ocorrencia-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-lg shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex justify-between items-center border-b pb-3">
                <h3 class="text-xl font-bold text-gray-900">Registrar Nova Ocorrência</h3>
                <button id="close-ocorrencia-modal-btn" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <form action="<?php echo BASE_URL; ?>/fornecedores/salvarOcorrencia" method="POST" class="mt-4 space-y-4">
                <input type="hidden" name="fornecedor_id" value="<?php echo $fornecedor['id']; ?>">
                <div>
                    <label for="data_ocorrencia" class="block text-sm font-medium text-gray-700">Data da Ocorrência</label>
                    <input type="date" name="data_ocorrencia" id="data_ocorrencia" required value="<?php echo date('Y-m-d'); ?>" class="mt-1 w-full border-gray-300 rounded-lg shadow-sm p-2 text-sm">
                </div>
                <div>
                    <label for="tipo" class="block text-sm font-medium text-gray-700">Tipo</label>
                    <select name="tipo" id="tipo" required class="mt-1 w-full border-gray-300 rounded-lg shadow-sm p-2 text-sm">
                        <option value="Comunicação">Comunicação</option>
                        <option value="Atraso na Entrega">Atraso na Entrega</option>
                        <option value="Qualidade">Qualidade do Produto/Serviço</option>
                        <option value="Financeiro">Financeiro</option>
                        <option value="Documentação">Documentação</option>
                        <option value="Outro">Outro</option>
                    </select>
                </div>
                <div>
                    <label for="responsavel" class="block text-sm font-medium text-gray-700">Responsável (Interno)</label>
                    <input type="text" name="responsavel" id="responsavel" required value="<?php echo htmlspecialchars($this->session->get('user_name', '')); ?>" class="mt-1 w-full border-gray-300 rounded-lg shadow-sm p-2 text-sm">
                </div>
                <div>
                    <label for="descricao" class="block text-sm font-medium text-gray-700">Descrição</label>
                    <textarea name="descricao" id="descricao" rows="4" required class="mt-1 w-full border-gray-300 rounded-lg shadow-sm p-2 text-sm" placeholder="Descreva a ocorrência..."></textarea>
                </div>
                <div class="pt-4 border-t flex justify-end gap-3">
                    <button type="button" id="cancel-ocorrencia-btn" class="bg-gray-200 text-gray-800 px-4 py-2 rounded-lg hover:bg-gray-300 font-semibold text-sm">Cancelar</button>
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 font-semibold text-sm">Salvar Ocorrência</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('form-fornecedor-modal');
        const openBtn = document.getElementById('open-edit-modal-btn');
        const closeBtn = document.getElementById('close-modal-btn');

        const openModal = () => modal.classList.remove('hidden');
        const closeModal = () => modal.classList.add('hidden');

        openBtn.addEventListener('click', openModal);
        closeBtn.addEventListener('click', closeModal);

        modal.addEventListener('click', function(event) {
            if (event.target === modal || (event.target && event.target.id === 'cancel-form-btn')) {
                closeModal();
            }
        });

        // Lógica para o modal de ocorrências
        const ocorrenciaModal = document.getElementById('ocorrencia-modal');
        const openOcorrenciaBtn = document.getElementById('open-ocorrencia-modal-btn');
        const closeOcorrenciaBtn = document.getElementById('close-ocorrencia-modal-btn');
        const cancelOcorrenciaBtn = document.getElementById('cancel-ocorrencia-btn');

        openOcorrenciaBtn.addEventListener('click', () => ocorrenciaModal.classList.remove('hidden'));
        closeOcorrenciaBtn.addEventListener('click', () => ocorrenciaModal.classList.add('hidden'));
        cancelOcorrenciaBtn.addEventListener('click', () => ocorrenciaModal.classList.add('hidden'));
        ocorrenciaModal.addEventListener('click', (e) => {
            if (e.target === ocorrenciaModal) {
                ocorrenciaModal.classList.add('hidden');
            }
        });
    });
</script>