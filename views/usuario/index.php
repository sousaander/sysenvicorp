<h2 class="text-2xl font-bold mb-4">Módulo de Usuários e Gestão de Acesso</h2>
<p class="mb-6 text-gray-600">Administre o cadastro de usuários, defina perfis de acesso e garanta a segurança das informações através do controle de permissões.</p>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- Card de Resumo de Perfis -->
    <div class="lg:col-span-1 bg-white p-6 rounded-lg shadow-md">
        <h3 class="text-lg font-semibold mb-4 border-b pb-2">Resumo de Perfis</h3>
        <div class="space-y-3">
            <?php if (!empty($perfis)): ?>
                <?php foreach ($perfis as $perfil): ?>
                    <div class="p-3 border border-gray-200 rounded-lg bg-gray-50">
                        <p class="font-medium text-gray-700"><?php echo htmlspecialchars($perfil['nome_perfil']); ?></p>
                        <?php if (!empty($perfil['descricao'])): ?>
                            <p class="text-xs text-gray-500 mt-1"><?php echo htmlspecialchars($perfil['descricao']); ?></p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-gray-500">Nenhum perfil de acesso cadastrado.</p>
            <?php endif; ?>
        </div>
        <a href="<?php echo BASE_URL; ?>/perfil" class="block text-center w-full mt-4 bg-sky-500 text-white px-4 py-2 rounded-lg shadow-md hover:bg-sky-600 transition text-sm">
            Gerenciar Perfis
        </a>
    </div>

    <!-- Lista Principal de Usuários -->
    <div class="lg:col-span-2 bg-white p-6 rounded-lg shadow-md">
        <h3 class="text-lg font-semibold mb-4 border-b pb-2 flex justify-between items-center">
            Lista de Usuários Cadastrados
            <button id="openModalBtn" class="text-sm font-medium text-emerald-600 hover:text-emerald-800">
                + Novo Usuário
            </button>
        </h3>

        <?php if (!empty($usuarios)): ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nome</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cargo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Perfil de Acesso</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($usuarios as $user): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($user['nome'] ?? ''); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($user['cargo'] ?? ''); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 font-semibold"><?php echo htmlspecialchars($user['perfil'] ?? ''); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    <?php
                                    if ($user['status'] === 'Ativo') echo 'bg-green-100 text-green-800';
                                    else echo 'bg-red-100 text-red-800';
                                    ?>">
                                        <?php echo htmlspecialchars($user['status']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button data-userid="<?php echo $user['id']; ?>" class="edit-user-btn text-indigo-600 hover:text-indigo-900 mr-3" title="Editar">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z" />
                                            <path fill-rule="evenodd" d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                    <a href="<?php echo BASE_URL; ?>/usuario/toggleStatus/<?php echo $user['id']; ?>" class="<?php echo $user['status'] === 'Ativo' ? 'text-red-600 hover:text-red-900' : 'text-green-600 hover:text-green-900'; ?>" title="<?php echo $user['status'] === 'Ativo' ? 'Desativar' : 'Ativar'; ?>">
                                        <?php if ($user['status'] === 'Ativo'): ?>
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM7 9a1 1 0 000 2h6a1 1 0 100-2H7z" clip-rule="evenodd" />
                                            </svg>
                                        <?php else: ?>
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd" />
                                            </svg>
                                        <?php endif; ?>
                                    </a>
                                </td>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-gray-500">Nenhum usuário encontrado.</p>
        <?php endif; ?>
    </div>
</div>

<!-- Modal para Novo Usuário -->
<div id="novoUsuarioModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg leading-6 font-medium text-gray-900 text-center">Novo Usuário</h3>
            <div class="mt-2 px-7 py-3">
                <!-- Formulário Corrigido -->
                <form action="<?php echo BASE_URL; ?>/usuario/salvar" method="post" class="text-left space-y-4">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

                    <div>
                        <label for="nome" class="block text-sm font-medium text-gray-700">Nome Completo <span class="text-red-500">*</span></label>
                        <input type="text" id="nome" name="nome" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-sky-500 focus:ring-sky-500 p-2">
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">E-mail <span class="text-red-500">*</span></label>
                        <input type="email" id="email" name="email" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-sky-500 focus:ring-sky-500 p-2">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="cargo_id" class="block text-sm font-medium text-gray-700">Cargo <span class="text-red-500">*</span></label>
                            <select id="cargo_id" name="cargo_id" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-sky-500 focus:ring-sky-500 p-2">
                                <option value="">Selecione um cargo...</option>
                                <?php if (!empty($cargos)): ?>
                                    <?php foreach ($cargos as $cargo): ?>
                                        <option value="<?php echo htmlspecialchars($cargo['cargo_id']); ?>"><?php echo htmlspecialchars($cargo['nome_cargo']); ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div>
                            <label for="perfil_id" class="block text-sm font-medium text-gray-700">Perfil de Acesso <span class="text-red-500">*</span></label>
                            <select id="perfil_id" name="perfil_id" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-sky-500 focus:ring-sky-500 p-2">
                                <option value="">Selecione um perfil...</option>
                                <?php if (!empty($perfis)): ?>
                                    <?php foreach ($perfis as $perfil): ?>
                                        <option value="<?php echo htmlspecialchars($perfil['perfil_id']); ?>"><?php echo htmlspecialchars($perfil['nome_perfil']); ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                        <select id="status" name="status" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-sky-500 focus:ring-sky-500 p-2">
                            <option value="Ativo" selected>Ativo</option>
                            <option value="Inativo">Inativo</option>
                        </select>
                    </div>

                    <p class="text-xs text-gray-500">Uma senha padrão ("Mudar@123") será gerada para o novo usuário.</p>

                    <div class="items-center px-4 py-3 flex flex-col-reverse sm:flex-row sm:justify-end sm:space-x-4">
                        <button id="closeModalBtn" type="button" class="mt-2 sm:mt-0 w-full sm:w-auto px-4 py-2 bg-gray-200 text-gray-800 text-base font-medium rounded-md shadow-sm hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-300">
                            Cancelar
                        </button>
                        <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-emerald-500 text-white text-base font-medium rounded-md shadow-sm hover:bg-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-300">
                            Salvar Usuário
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Editar Usuário -->
<div id="editarUsuarioModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg leading-6 font-medium text-gray-900 text-center">Editar Usuário</h3>
            <div class="mt-2 px-7 py-3">
                <form id="editUserForm" action="" method="post" class="text-left space-y-4">
                    <input type="hidden" name="id" id="edit_user_id">
                    <input type="hidden" name="csrf_token" id="edit_csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

                    <div>
                        <label for="edit_nome" class="block text-sm font-medium text-gray-700">Nome Completo <span class="text-red-500">*</span></label>
                        <input type="text" id="edit_nome" name="nome" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-sky-500 focus:ring-sky-500 p-2">
                    </div>

                    <div>
                        <label for="edit_email" class="block text-sm font-medium text-gray-700">E-mail <span class="text-red-500">*</span></label>
                        <input type="email" id="edit_email" name="email" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-sky-500 focus:ring-sky-500 p-2">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="edit_cargo_id" class="block text-sm font-medium text-gray-700">Cargo <span class="text-red-500">*</span></label>
                            <select id="edit_cargo_id" name="cargo_id" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-sky-500 focus:ring-sky-500 p-2">
                                <option value="">Selecione um cargo...</option>
                                <?php if (!empty($cargos)): ?>
                                    <?php foreach ($cargos as $cargo): ?>
                                        <option value="<?php echo htmlspecialchars($cargo['cargo_id']); ?>"><?php echo htmlspecialchars($cargo['nome_cargo']); ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div>
                            <label for="edit_perfil_id" class="block text-sm font-medium text-gray-700">Perfil de Acesso <span class="text-red-500">*</span></label>
                            <select id="edit_perfil_id" name="perfil_id" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-sky-500 focus:ring-sky-500 p-2">
                                <option value="">Selecione um perfil...</option>
                                <?php if (!empty($perfis)): ?>
                                    <?php foreach ($perfis as $perfil): ?>
                                        <option value="<?php echo htmlspecialchars($perfil['perfil_id']); ?>"><?php echo htmlspecialchars($perfil['nome_perfil']); ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label for="edit_status" class="block text-sm font-medium text-gray-700">Status</label>
                        <select id="edit_status" name="status" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-sky-500 focus:ring-sky-500 p-2">
                            <option value="Ativo">Ativo</option>
                            <option value="Inativo">Inativo</option>
                        </select>
                    </div>

                    <div class="items-center px-4 py-3 flex flex-col-reverse sm:flex-row sm:justify-end sm:space-x-4">
                        <button id="closeEditModalBtn" type="button" class="mt-2 sm:mt-0 w-full sm:w-auto px-4 py-2 bg-gray-200 text-gray-800 text-base font-medium rounded-md shadow-sm hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-300">Cancelar</button>
                        <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-indigo-600 text-white text-base font-medium rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-300">Atualizar Usuário</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('novoUsuarioModal');
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

        // Abre a modal se a variável showModal for true (quando acessado via /usuario/novo)
        <?php if (isset($showModal) && $showModal): ?>
            modal.style.display = "block";
        <?php endif; ?>

        // --- Lógica para a Modal de Edição ---
        const editModal = document.getElementById('editarUsuarioModal');
        const closeEditBtn = document.getElementById('closeEditModalBtn');
        const editUserButtons = document.querySelectorAll('.edit-user-btn');
        const editForm = document.getElementById('editUserForm');

        // Dados dos usuários passados pelo PHP
        const usuariosData = <?php echo $usuarios_json; ?>;

        editUserButtons.forEach(button => {
            button.addEventListener('click', function() {
                const userId = this.getAttribute('data-userid');
                const userData = usuariosData.find(user => user.id == userId);

                if (userData) {
                    // Preenche o formulário com os dados do usuário
                    document.getElementById('edit_user_id').value = userData.id;
                    document.getElementById('edit_nome').value = userData.nome;
                    document.getElementById('edit_email').value = userData.email; // Supondo que o email venha nos dados
                    document.getElementById('edit_cargo_id').value = userData.cargo_id;
                    document.getElementById('edit_perfil_id').value = userData.perfil_id;
                    document.getElementById('edit_status').value = userData.status;

                    // Define a action do formulário
                    editForm.action = `<?php echo BASE_URL; ?>/usuario/atualizar/${userData.id}`;

                    // O token CSRF já está no input hidden do formulário, carregado pelo PHP.

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