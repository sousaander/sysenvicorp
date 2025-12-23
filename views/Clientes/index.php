<div class="flex justify-between items-center mb-6">
    <div>
        <h2 class="text-2xl font-bold"><?php echo htmlspecialchars($pageTitle); ?></h2>
        <p class="text-gray-600">Gerencie seus leads, propostas e clientes ativos.</p>
    </div>
    <!-- O link foi trocado por um botão que abrirá o modal -->
    <button id="open-new-client-modal-btn" class="px-4 py-2 text-sm font-semibold text-white bg-sky-600 rounded-lg shadow-md hover:bg-sky-700 transition">
        + Novo Cliente
    </button>
</div>

<!-- Cards de Resumo -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <!-- Card 1: Total Ativos -->
    <div class="bg-white p-6 rounded-lg shadow-lg border-l-4 border-green-500">
        <h3 class="font-semibold text-gray-500">Clientes Ativos</h3>
        <p class="text-3xl font-bold text-green-600"><?php echo $totalAtivos ?? 0; ?></p>
    </div>
    <!-- Card 2: Novos no Mês -->
    <div class="bg-white p-6 rounded-lg shadow-lg border-l-4 border-blue-500">
        <h3 class="font-semibold text-gray-500">Novos no Mês</h3>
        <p class="text-3xl font-bold text-blue-600"><?php echo $novosMes ?? 0; ?></p>
    </div>
    <!-- Card 3: Propostas Pendentes -->
    <div class="bg-white p-6 rounded-lg shadow-lg border-l-4 border-yellow-500">
        <h3 class="font-semibold text-gray-500">Propostas Pendentes</h3>
        <p class="text-3xl font-bold text-yellow-600"><?php echo $propostasPendentes ?? 0; ?></p>
    </div>
    <!-- Card 4: Risco de Perda -->
    <div class="bg-white p-6 rounded-lg shadow-lg border-l-4 border-red-500">
        <h3 class="font-semibold text-gray-500">Risco de Perda</h3>
        <p class="text-3xl font-bold text-red-600"><?php echo $riscoPerda ?? 0; ?></p>
    </div>
</div>

<!-- Tabela de Clientes -->
<div class="bg-white p-6 rounded-lg shadow-md">
    <!-- Formulário de Busca -->
    <form action="<?php echo BASE_URL; ?>/clientes" method="GET" class="mb-4 flex items-center gap-4">
        <div class="flex-grow">
            <label for="busca" class="sr-only">Buscar</label>
            <input type="text" name="busca" id="busca" class="w-full border-gray-300 rounded-lg shadow-sm p-2 text-sm" placeholder="Buscar por Nome, CNPJ/CPF ou Cidade..." value="<?php echo htmlspecialchars($filtros['busca'] ?? ''); ?>">
        </div>
        <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-sky-600 rounded-lg shadow-md hover:bg-sky-700">Buscar</button>
        <a href="<?php echo BASE_URL; ?>/clientes" class="px-4 py-2 text-sm font-semibold text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300">Limpar</a>
    </form>

    <h3 class="text-lg font-semibold mb-4 border-b pb-2">Lista de Clientes Recentes</h3>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nome</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contato</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Última Interação</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (!empty($clientes)) : ?>
                    <?php foreach ($clientes as $cliente) : ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($cliente['nome']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($cliente['contato_principal']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $cliente['data_ultima_interacao'] ? date('d/m/Y', strtotime($cliente['data_ultima_interacao'])) : 'N/A'; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                <?php
                                if ($cliente['status'] === 'Ativo') echo 'bg-green-100 text-green-800';
                                elseif ($cliente['status'] === 'Lead') echo 'bg-blue-100 text-blue-800';
                                elseif ($cliente['status'] === 'Inativo') echo 'bg-red-100 text-red-800';
                                else echo 'bg-yellow-100 text-yellow-800';
                                ?>"><?php echo htmlspecialchars($cliente['status']); ?></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="<?php echo BASE_URL; ?>/clientes/detalhe/<?php echo $cliente['id']; ?>" class="text-indigo-600 hover:text-indigo-900 mr-3">Detalhes</a>
                                <a href="<?php echo BASE_URL; ?>/clientes/excluir/<?php echo $cliente['id']; ?>" class="text-red-600 hover:text-red-900" onclick="return confirm('Tem certeza que deseja excluir este cliente?');">Excluir</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="5" class="text-center py-4 text-gray-500">Nenhum cliente encontrado.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once ROOT_PATH . '/views/partials/modal_cliente_form.php'; ?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const openNewClientBtn = document.getElementById('open-new-client-modal-btn');

        if (openNewClientBtn) {
            // Abrir modal para NOVO cliente
            openNewClientBtn.addEventListener('click', async () => {
                // Chama a função global definida em modal_cliente_form.php
                window.openClientFormModal();
            });
        }

        // Verifica se a URL tem o parâmetro para abrir o modal
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('action') === 'novo') {
            // A função global openClientFormModal já é chamada pelo script do modal_cliente_form.php
            // quando ele detecta o parâmetro 'action=novo' na URL.
            // Não é necessário simular o clique aqui.
            // window.openClientFormModal(); // Isso seria redundante se o script do modal já faz.
        }
    });
</script>