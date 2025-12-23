<div class="flex justify-between items-center mb-6">
    <div>
        <h2 class="text-2xl font-bold"><?php echo htmlspecialchars($pageTitle); ?></h2>
        <p class="text-gray-600">Adicione, edite ou remova os perfis de acesso do sistema.</p>
    </div>
    <div class="flex items-center gap-4">
        <a href="<?php echo BASE_URL; ?>/usuario" class="px-4 py-2 text-sm font-semibold text-gray-700 bg-gray-200 rounded-lg shadow-md hover:bg-gray-300 transition">
            &larr; Voltar
        </a>
        <button id="openModalBtn" class="px-4 py-2 text-sm font-semibold text-white bg-sky-600 rounded-lg shadow-md hover:bg-sky-700 transition">
            + Novo Perfil
        </button>
    </div>
</div>

<div class="bg-white p-6 rounded-lg shadow-md">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nome do Perfil</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descrição</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            <?php if (!empty($perfis)): ?>
                <?php foreach ($perfis as $perfil): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            <?php echo htmlspecialchars($perfil['nome_perfil']); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <!-- CORREÇÃO: Remover htmlspecialchars daqui, pois já é aplicado ao salvar. -->
                            <?php echo nl2br($perfil['descricao'] ?? ''); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <button data-perfilid="<?php echo $perfil['perfil_id']; ?>" class="edit-perfil-btn text-indigo-600 hover:text-indigo-900 mr-4 font-medium">
                                Editar
                            </button>
                            <form action="<?php echo BASE_URL; ?>/perfil/excluir" method="post" style="display:inline;">
                                <input type="hidden" name="id" value="<?php echo $perfil['id'] ?? $perfil['perfil_id'] ?? ''; ?>">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                                <button type="submit" class="text-red-600 hover:text-red-900 bg-transparent border-0 p-0 m-0" onclick="return confirm('Tem certeza que deseja excluir este perfil? Esta ação não pode ser desfeita.');">Excluir</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="3" class="px-6 py-4 text-center text-gray-500">Nenhum perfil cadastrado.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal para Novo Perfil -->
<div id="novoPerfilModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-lg shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg leading-6 font-medium text-gray-900 text-center">Novo Perfil de Acesso</h3>
            <div class="mt-2 px-7 py-3">
                <form action="<?php echo BASE_URL; ?>/perfil/salvar" method="post" class="text-left space-y-6">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

                    <div>
                        <label for="nome_perfil" class="block text-sm font-medium text-gray-700">Nome do Perfil <span class="text-red-500">*</span></label>
                        <input type="text" id="nome_perfil" name="nome_perfil" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-sky-500 focus:ring-sky-500 p-2">
                    </div>

                    <div>
                        <label for="descricao" class="block text-sm font-medium text-gray-700">Descrição</label>
                        <textarea id="descricao" name="descricao" rows="3" placeholder="Descreva brevemente as permissões deste perfil." class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-sky-500 focus:ring-sky-500 p-2"></textarea>
                    </div>

                    <div class="items-center px-4 py-3 flex flex-col-reverse sm:flex-row sm:justify-end sm:space-x-4">
                        <button id="closeModalBtn" type="button" class="mt-2 sm:mt-0 w-full sm:w-auto px-4 py-2 bg-gray-200 text-gray-800 text-base font-medium rounded-md shadow-sm hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-300">
                            Cancelar
                        </button>
                        <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-emerald-500 text-white text-base font-medium rounded-md shadow-sm hover:bg-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-300">
                            Salvar Perfil
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Editar Perfil -->
<div id="editarPerfilModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-lg shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg leading-6 font-medium text-gray-900 text-center">Editar Perfil de Acesso</h3>
            <div class="mt-2 px-7 py-3">
                <form id="editPerfilForm" action="<?php echo BASE_URL; ?>/perfil/salvar" method="post" class="text-left space-y-6">
                    <input type="hidden" name="id" id="edit_perfil_id">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

                    <div>
                        <label for="edit_nome_perfil" class="block text-sm font-medium text-gray-700">Nome do Perfil <span class="text-red-500">*</span></label>
                        <input type="text" id="edit_nome_perfil" name="nome_perfil" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-sky-500 focus:ring-sky-500 p-2">
                    </div>

                    <div>
                        <label for="edit_descricao" class="block text-sm font-medium text-gray-700">Descrição</label>
                        <textarea id="edit_descricao" name="descricao" rows="3" placeholder="Descreva brevemente as permissões deste perfil." class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-sky-500 focus:ring-sky-500 p-2"></textarea>
                    </div>

                    <div class="items-center px-4 py-3 flex flex-col-reverse sm:flex-row sm:justify-end sm:space-x-4">
                        <button id="closeEditModalBtn" type="button" class="mt-2 sm:mt-0 w-full sm:w-auto px-4 py-2 bg-gray-200 text-gray-800 text-base font-medium rounded-md shadow-sm hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-300">
                            Cancelar
                        </button>
                        <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-indigo-600 text-white text-base font-medium rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-300">
                            Salvar Alterações
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // --- Lógica para a Modal de Novo Perfil ---
        const modal = document.getElementById('novoPerfilModal');
        const openBtn = document.getElementById('openModalBtn');
        const closeBtn = document.getElementById('closeModalBtn');

        if (openBtn) {
            openBtn.onclick = () => {
                modal.style.display = "block";
            };
        }

        if (closeBtn) {
            closeBtn.onclick = () => {
                modal.style.display = "none";
            };
        }

        // Fecha a modal se clicar fora dela
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
            // Fecha a modal de edição se clicar fora dela
            if (event.target == editModal) {
                editModal.style.display = "none";
            }
        }

        // --- Lógica para a Modal de Edição ---
        const editModal = document.getElementById('editarPerfilModal');
        const closeEditBtn = document.getElementById('closeEditModalBtn');
        const editPerfilButtons = document.querySelectorAll('.edit-perfil-btn');

        // Dados dos perfis passados pelo PHP
        const perfisData = <?php echo $perfis_json; ?>;

        editPerfilButtons.forEach(button => {
            button.addEventListener('click', function() {
                const perfilId = this.getAttribute('data-perfilid');
                const perfilData = perfisData.find(p => p.perfil_id == perfilId);

                if (perfilData) {
                    // Preenche o formulário com os dados do perfil
                    document.getElementById('edit_perfil_id').value = perfilData.perfil_id;
                    document.getElementById('edit_nome_perfil').value = perfilData.nome_perfil;
                    document.getElementById('edit_descricao').value = perfilData.descricao || '';

                    // Exibe a modal
                    editModal.style.display = 'block';
                }
            });
        });

        if (closeEditBtn) {
            closeEditBtn.onclick = () => {
                editModal.style.display = 'none';
            };
        }

    });
</script>