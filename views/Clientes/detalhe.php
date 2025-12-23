<div class="flex justify-between items-center mb-6">
    <div>
        <h2 class="text-2xl font-bold text-gray-800">Detalhes do Cliente</h2>
        <p class="text-gray-600">Histórico de interações e informações de contato.</p>
    </div>
    <a href="<?php echo BASE_URL; ?>/clientes" class="bg-gray-200 text-gray-800 px-4 py-2 rounded-md hover:bg-gray-300 font-medium">
        &larr; Voltar para a Lista
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Coluna de Informações do Cliente -->
    <div class="lg:col-span-1 bg-white p-6 rounded-lg shadow-md">
        <h3 class="text-xl font-semibold mb-4 border-b pb-2"><?php echo htmlspecialchars($cliente['nome']); ?></h3>
        <div class="space-y-4 text-sm">
            <div>
                <p class="font-medium text-gray-500">CNPJ/CPF</p>
                <p class="text-gray-800"><?php echo htmlspecialchars($cliente['cnpj_cpf'] ?: 'Não informado'); ?></p>
            </div>
            <div>
                <p class="font-medium text-gray-500">Contato Principal</p>
                <p class="text-gray-800"><?php echo htmlspecialchars($cliente['contato_principal'] ?: 'Não informado'); ?></p>
            </div>
            <div>
                <p class="font-medium text-gray-500">E-mail</p>
                <p class="text-gray-800"><?php echo htmlspecialchars($cliente['email'] ?: 'Não informado'); ?></p>
            </div>
            <div>
                <p class="font-medium text-gray-500">Telefone</p>
                <p class="text-gray-800"><?php echo htmlspecialchars($cliente['telefone'] ?: 'Não informado'); ?></p>
            </div>
            <div>
                <p class="font-medium text-gray-500">Endereço</p>
                <p class="text-gray-800"><?php echo htmlspecialchars($cliente['endereco'] ?: 'Não informado'); ?></p>
            </div>
            <div>
                <p class="font-medium text-gray-500">Status</p>
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                    <?php
                    if ($cliente['status'] === 'Lead') echo 'bg-blue-100 text-blue-800';
                    elseif ($cliente['status'] === 'Proposta Enviada') echo 'bg-sky-100 text-sky-800';
                    elseif ($cliente['status'] === 'Em Negociação') echo 'bg-purple-100 text-purple-800';
                    elseif ($cliente['status'] === 'Risco de Perda') echo 'bg-orange-100 text-orange-800';
                    elseif ($cliente['status'] === 'Ativo') echo 'bg-green-100 text-green-800';
                    elseif ($cliente['status'] === 'Inativo') echo 'bg-red-100 text-red-800';
                    else echo 'bg-gray-100 text-gray-800';
                    ?>">
                    <?php echo $cliente['status']; ?>
                </span>
            </div>
        </div>
        <div class="mt-6 pt-6 border-t flex flex-col sm:flex-row gap-2">
            <!-- O botão de edição agora é um 'button' para ser controlado pelo JS -->
            <button data-cliente-id="<?php echo htmlspecialchars($cliente['id']); ?>" class="edit-cliente-btn w-full text-center bg-sky-600 text-white px-4 py-2 rounded-md hover:bg-sky-700 font-medium">
                Editar
            </button>
            <button id="open-interaction-modal-btn" class="w-full bg-teal-600 text-white px-4 py-2 rounded-md hover:bg-teal-700 font-medium">
                Nova Interação
            </button>
        </div>
    </div>

    <!-- Coluna de Histórico de Interações -->
    <div class="lg:col-span-2 bg-white p-6 rounded-lg shadow-md">
        <h3 class="text-xl font-semibold mb-4">Histórico de Interações</h3>
        <div class="flow-root">
            <ul role="list" class="-mb-8">
                <?php if (!empty($interacoes)) : ?>
                    <?php foreach ($interacoes as $index => $interacao) : ?>
                        <li>
                            <div class="relative pb-8">
                                <?php if ($index < count($interacoes) - 1) : ?>
                                    <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                <?php endif; ?>
                                <div class="relative flex space-x-3">
                                    <div>
                                        <span class="h-8 w-8 rounded-full bg-gray-400 flex items-center justify-center ring-8 ring-white">
                                            <!-- Ícone pode ser dinâmico com base no tipo de interação -->
                                            <svg class="h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                                            </svg>
                                        </span>
                                    </div>
                                    <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                        <div>
                                            <p class="text-sm text-gray-500">
                                                <span class="font-medium text-gray-900"><?php echo htmlspecialchars($interacao['tipo_interacao']); ?></span>
                                                - por Usuário ID: <?php echo htmlspecialchars($interacao['usuario_id']); ?>
                                            </p>
                                            <p class="mt-1 text-sm text-gray-700"><?php echo nl2br(htmlspecialchars($interacao['descricao'])); ?></p>
                                        </div>
                                        <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                            <time datetime="<?php echo $interacao['data_interacao']; ?>"><?php echo date('d/m/Y H:i', strtotime($interacao['data_interacao'])); ?></time>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                <?php else : ?>
                    <li>
                        <p class="text-center text-gray-500">Nenhuma interação registrada para este cliente ainda.</p>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</div>

<!-- O modal de interação que já existe em index.php pode ser movido para um arquivo parcial e incluído aqui e em index.php -->
<!-- Por simplicidade, estou replicando-o aqui. -->
<?php require_once ROOT_PATH . '/views/partials/modal_interacao.php'; ?>
<?php require_once ROOT_PATH . '/views/partials/modal_cliente_form.php'; ?>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const modal = document.getElementById('interaction-modal');
        const openBtn = document.getElementById('open-interaction-modal-btn');
        const closeBtn = document.getElementById('close-interaction-modal-btn');
        const modalBg = document.getElementById('modal-bg');
        const clienteSelect = document.getElementById('cliente_id');

        openBtn.addEventListener('click', () => {
            // Pré-seleciona o cliente atual no modal
            if (clienteSelect) {
                clienteSelect.value = "<?php echo $cliente['id']; ?>";
            }
            modal.classList.remove('hidden');
        });
        closeBtn.addEventListener('click', () => modal.classList.add('hidden'));
        modalBg.addEventListener('click', () => modal.classList.add('hidden'));

        // Lógica para o botão "Editar" do cliente
        const editClientBtn = document.querySelector('.edit-cliente-btn');
        if (editClientBtn) {
            editClientBtn.addEventListener('click', () => {
                const clientId = editClientBtn.dataset.clienteId;
                window.openClientFormModal(clientId); // Chama a função global para abrir o modal de edição
            });
        }
    });
</script>