<h2 class="text-2xl font-bold mb-4"><?php echo htmlspecialchars($pageTitle); ?></h2>
<p class="mb-6 text-gray-600">Visão geral do patrimônio, valores contábeis e movimentações recentes.</p>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <!-- Card 1: Total de Ativos -->
    <div class="bg-white p-6 rounded-lg shadow-lg border-l-4 border-sky-500">
        <h3 class="font-semibold text-gray-500">Total de Ativos Ativos</h3>
        <p class="text-3xl font-bold text-sky-600"><?php echo $totalAtivos ?? 0; ?></p>
        <p class="text-sm text-gray-400 mt-2">Bens registrados e não baixados</p>
    </div>
    <!-- Card 2: Valor Total Estimado -->
    <div class="bg-white p-6 rounded-lg shadow-lg border-l-4 border-green-500">
        <h3 class="font-semibold text-gray-500">Valor Contábil Total</h3>
        <p class="text-2xl font-bold text-green-600">R$ <?php echo number_format($valorContabilTotal ?? 0, 2, ',', '.'); ?></p>
        <p class="text-sm text-gray-400 mt-2">Valor contábil atual</p>
    </div>
    <!-- Card 3: Bens Baixados no Ano -->
    <div class="bg-white p-6 rounded-lg shadow-lg border-l-4 border-red-500">
        <h3 class="font-semibold text-gray-500">Bens Baixados (Ano)</h3>
        <p class="text-3xl font-bold text-red-600"><?php echo $bensBaixadosAno ?? 0; ?></p>
        <p class="text-sm text-gray-400 mt-2">Vendas, descartes ou doações</p>
    </div>
    <!-- Card 4: Ativos em Depreciação -->
    <div class="bg-white p-6 rounded-lg shadow-lg border-l-4 border-gray-500">
        <h3 class="font-semibold text-gray-500">Ativos Depreciáveis</h3>
        <p class="text-3xl font-bold text-gray-600"><?php echo $totalDepreciaveis ?? 0; ?></p>
        <p class="text-sm text-gray-400 mt-2">Bens com cálculo de depreciação</p>
    </div>
</div>

<div class="bg-white p-6 rounded-lg shadow-md">
    <h3 class="text-lg font-semibold mb-4 border-b pb-2 flex justify-between items-center">
        Bens Adicionados Recentemente
        <button id="open-modal-btn" class="px-4 py-2 text-sm font-semibold text-white bg-sky-600 rounded-lg shadow-md hover:bg-sky-700 transition">
            + Novo Bem
        </button>
    </h3>

    <div class="overflow-x-auto">
        <?php if (!empty($bensRecentes)) : ?>
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nome do Bem</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Classificação</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Localização</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data de Aquisição</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($bensRecentes as $bem) : ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                <a href="#" class="text-sky-600 hover:underline edit-btn" data-id="<?php echo $bem['id']; ?>">
                                    <?php echo htmlspecialchars($bem['nome']); ?>
                                </a>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600"><?php echo htmlspecialchars($bem['classificacao']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600"><?php echo htmlspecialchars($bem['localizacao']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600"><?php echo $bem['data_aquisicao'] ? date('d/m/Y', strtotime($bem['data_aquisicao'])) : 'N/A'; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button class="text-indigo-600 hover:text-indigo-900 edit-btn" data-id="<?php echo $bem['id']; ?>">Editar</button>
                                <button class="text-red-600 hover:text-red-900 ml-4 delete-btn" data-id="<?php echo $bem['id']; ?>">Excluir</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else : ?>
            <p class="text-gray-500 py-4">Nenhum bem adicionado recentemente.</p>
        <?php endif; ?>

        <!-- Paginação -->
        <?php if ($totalPaginas > 1) : ?>
            <div class="mt-4 flex justify-end items-center">
                <nav class="flex items-center justify-end space-x-2">
                    <a href="<?php echo BASE_URL; ?>/patrimonio?page=<?php echo $paginaAtual - 1; ?>" class="<?php echo ($paginaAtual <= 1) ? 'pointer-events-none text-gray-400' : 'text-gray-600 hover:text-sky-600'; ?> px-3 py-1 rounded-md text-sm font-medium">
                        Anterior
                    </a>
                    <?php for ($i = 1; $i <= $totalPaginas; $i++) : ?>
                        <a href="<?php echo BASE_URL; ?>/patrimonio?page=<?php echo $i; ?>" class="<?php echo ($i == $paginaAtual) ? 'bg-sky-500 text-white' : 'bg-white text-gray-600 hover:bg-gray-100'; ?> px-3 py-1 rounded-md text-sm font-medium border">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                    <a href="<?php echo BASE_URL; ?>/patrimonio?page=<?php echo $paginaAtual + 1; ?>" class="<?php echo ($paginaAtual >= $totalPaginas) ? 'pointer-events-none text-gray-400' : 'text-gray-600 hover:text-sky-600'; ?> px-3 py-1 rounded-md text-sm font-medium">
                        Próxima
                    </a>
                </nav>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal para Novo Bem -->
<div id="novo-bem-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-10 mx-auto p-5 border w-full max-w-4xl shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center pb-3 border-b">
            <p id="modal-title" class="text-2xl font-bold">Cadastro de Bem Patrimonial</p>
            <button id="close-modal-btn" class="text-gray-500 hover:text-gray-800 text-3xl">&times;</button>
        </div>
        <div class="mt-5 max-h-[75vh] overflow-y-auto pr-2">
            <form id="bem-form" action="<?php echo BASE_URL; ?>/patrimonio/salvar" method="POST">
                <input type="hidden" id="bem-id" name="id">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Nome do Bem -->
                    <div class="md:col-span-2">
                        <label for="nome" class="block text-sm font-medium text-gray-700 mb-1">Nome / Descrição do Bem <span class="text-red-500">*</span></label>
                        <input type="text" id="bem-nome" name="nome" required class="w-full border-gray-300 rounded-lg shadow-sm p-2 focus:border-sky-500 focus:ring-sky-500">
                    </div>

                    <!-- Número de Patrimônio / Plaqueta -->
                    <div>
                        <label for="numero_patrimonio" class="block text-sm font-medium text-gray-700 mb-1">Nº de Patrimônio / Plaqueta</label>
                        <input type="text" id="numero_patrimonio" name="numero_patrimonio" class="w-full border-gray-300 rounded-lg shadow-sm p-2 focus:border-sky-500 focus:ring-sky-500">
                    </div>

                    <!-- Classificação / Tipo -->
                    <div>
                        <label for="classificacao" class="block text-sm font-medium text-gray-700 mb-1">Classificação / Tipo <span class="text-red-500">*</span></label>
                        <select id="bem-classificacao" name="classificacao" required class="w-full border-gray-300 rounded-lg shadow-sm p-2 focus:border-sky-500 focus:ring-sky-500">
                            <option value="">Selecione...</option>
                            <option value="Imóvel">Imóvel</option>
                            <option value="Veículo">Veículo</option>
                            <option value="Equipamento de TI">Equipamento de TI</option>
                            <option value="Mobiliário">Mobiliário</option>
                            <option value="Máquina / Ferramenta">Máquina / Ferramenta</option>
                            <option value="Software / Licença">Software / Licença</option>
                            <option value="Outro">Outro</option>
                        </select>
                    </div>

                    <!-- Localização -->
                    <div>
                        <label for="localizacao" class="block text-sm font-medium text-gray-700 mb-1">Localização / Setor <span class="text-red-500">*</span></label>
                        <input type="text" id="bem-localizacao" name="localizacao" required placeholder="Ex: Sala de TI, Campo, Administrativo" class="w-full border-gray-300 rounded-lg shadow-sm p-2 focus:border-sky-500 focus:ring-sky-500">
                    </div>

                    <!-- Responsável -->
                    <div>
                        <label for="responsavel" class="block text-sm font-medium text-gray-700 mb-1">Responsável pelo Bem</label>
                        <input type="text" id="bem-responsavel" name="responsavel" placeholder="Nome do colaborador" class="w-full border-gray-300 rounded-lg shadow-sm p-2 focus:border-sky-500 focus:ring-sky-500">
                    </div>

                    <!-- Observações -->
                    <div class="md:col-span-2">
                        <label for="observacoes" class="block text-sm font-medium text-gray-700 mb-1">Observações</label>
                        <textarea id="bem-observacoes" name="observacoes" rows="3" class="w-full border-gray-300 rounded-lg shadow-sm p-2 focus:border-sky-500 focus:ring-sky-500"></textarea>
                    </div>
                </div>

                <!-- Seção de Dados Contábeis -->
                <div class="mt-8 pt-6 border-t border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Dados Contábeis e de Depreciação</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <!-- Data de Aquisição -->
                        <div>
                            <label for="bem-data_aquisicao" class="block text-sm font-medium text-gray-700 mb-1">Data de Aquisição</label>
                            <input type="date" id="bem-data_aquisicao" name="data_aquisicao" class="w-full border-gray-300 rounded-lg shadow-sm p-2">
                        </div>

                        <!-- Valor de Aquisição -->
                        <div>
                            <label for="bem-valor_aquisicao" class="block text-sm font-medium text-gray-700 mb-1">Valor de Aquisição (R$)</label>
                            <input type="text" id="bem-valor_aquisicao" name="valor_aquisicao" placeholder="1500,00" class="w-full border-gray-300 rounded-lg shadow-sm p-2">
                        </div>

                        <!-- Vida Útil (Meses) -->
                        <div>
                            <label for="bem-vida_util_meses" class="block text-sm font-medium text-gray-700 mb-1">Vida Útil (Meses)</label>
                            <input type="number" id="bem-vida_util_meses" name="vida_util_meses" placeholder="60" class="w-full border-gray-300 rounded-lg shadow-sm p-2">
                        </div>

                        <!-- Centro de Custo -->
                        <div>
                            <label for="bem-centro_custo" class="block text-sm font-medium text-gray-700 mb-1">Centro de Custo</label>
                            <input type="text" id="bem-centro_custo" name="centro_custo" placeholder="Ex: TI, Administrativo" class="w-full border-gray-300 rounded-lg shadow-sm p-2">
                        </div>
                    </div>
                </div>

                <div class="mt-8 pt-4 border-t border-gray-200 flex justify-end">
                    <button type="submit" id="modal-submit-btn" class="px-6 py-2 text-sm font-semibold text-white bg-sky-600 rounded-lg shadow-md hover:bg-sky-700 transition">
                        Salvar Bem
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de Confirmação de Exclusão -->
<div id="delete-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-40 mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            </div>
            <h3 class="text-lg leading-6 font-medium text-gray-900 mt-2">Excluir Bem</h3>
            <div class="mt-2 px-7 py-3">
                <p class="text-sm text-gray-500">Tem certeza de que deseja excluir este bem? Esta ação não pode ser desfeita.</p>
            </div>
            <form id="delete-form" action="<?php echo BASE_URL; ?>/patrimonio/excluir" method="POST" class="items-center px-4 py-3">
                <input type="hidden" id="delete-bem-id" name="id">
                <button id="cancel-delete-btn" type="button" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md mr-2 hover:bg-gray-300">Cancelar</button>
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">Excluir</button>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const modal = document.getElementById('novo-bem-modal');
        const openBtn = document.getElementById('open-modal-btn');
        const closeBtn = document.getElementById('close-modal-btn');
    const modalTitle = document.getElementById('modal-title');
    const modalSubmitBtn = document.getElementById('modal-submit-btn');
    const bemForm = document.getElementById('bem-form');
    const bemIdInput = document.getElementById('bem-id');

    // Função para abrir o modal para um novo bem
    const openNewModal = () => {
        bemForm.reset();
        bemIdInput.value = '';
        modalTitle.textContent = 'Cadastro de Bem Patrimonial';
        modalSubmitBtn.textContent = 'Salvar Bem';
        modal.classList.remove('hidden');
    };

    // Função para abrir o modal para edição
    const openEditModal = (id) => {
        fetch(`<?php echo BASE_URL; ?>/patrimonio/getBemJson/${id}`)
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    const bem = result.data;
                    bemForm.reset(); // Limpa o formulário antes de preencher
                    
                    // Preenche os campos do formulário
                    document.getElementById('bem-id').value = bem.id;
                    document.getElementById('bem-nome').value = bem.nome || '';
                    document.getElementById('numero_patrimonio').value = bem.numero_patrimonio || '';
                    document.getElementById('bem-classificacao').value = bem.classificacao || '';
                    document.getElementById('bem-localizacao').value = bem.localizacao || '';
                    document.getElementById('bem-responsavel').value = bem.responsavel || '';
                    document.getElementById('bem-observacoes').value = bem.observacoes || '';
                    document.getElementById('bem-data_aquisicao').value = bem.data_aquisicao || '';
                    document.getElementById('bem-valor_aquisicao').value = bem.valor_aquisicao || '';
                    document.getElementById('bem-vida_util_meses').value = bem.vida_util_meses || '';
                    document.getElementById('bem-centro_custo').value = bem.centro_custo || '';

                    modalTitle.textContent = 'Editar Bem Patrimonial';
                    modalSubmitBtn.textContent = 'Atualizar Bem';
                    modal.classList.remove('hidden');
                } else {
                    alert(result.message);
                }
            })
            .catch(error => console.error('Erro ao buscar dados do bem:', error));
    };

    if (openBtn) openBtn.addEventListener('click', openNewModal);
        if (closeBtn) closeBtn.addEventListener('click', () => modal.classList.add('hidden'));

        // Fecha o modal se clicar fora dele
        window.addEventListener('click', (event) => {
            if (event.target == modal) {
                modal.classList.add('hidden');
            }
        });

    // Lógica para os botões de Editar
    document.querySelectorAll('.edit-btn').forEach(button => {
        button.addEventListener('click', (e) => {
            e.preventDefault();
            const id = button.dataset.id;
            openEditModal(id);
        });
    });

    // Lógica para o modal de exclusão
    const deleteModal = document.getElementById('delete-modal');
    const cancelDeleteBtn = document.getElementById('cancel-delete-btn');
    const deleteBemIdInput = document.getElementById('delete-bem-id');

    document.querySelectorAll('.delete-btn').forEach(button => {
        button.addEventListener('click', () => {
            const id = button.dataset.id;
            deleteBemIdInput.value = id;
            deleteModal.classList.remove('hidden');
        });
    });

    if(cancelDeleteBtn) cancelDeleteBtn.addEventListener('click', () => deleteModal.classList.add('hidden'));
    });
</script>