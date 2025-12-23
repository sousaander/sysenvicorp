<div class="flex justify-between items-center mb-6">
    <div>
        <h2 class="text-2xl font-bold"><?php echo htmlspecialchars($pageTitle); ?></h2>
        <p class="text-gray-600">Edite ou remova categorias e segmentos existentes.</p>
    </div>
</div>

<div class="bg-white p-6 rounded-lg shadow-md">
    <div class="space-y-6">
        <?php if (empty($categorias)) : ?>
            <p class="text-center text-gray-500">Nenhuma categoria encontrada. Adicione uma nova através do formulário de clientes.</p>
        <?php else : ?>
            <?php foreach ($categorias as $categoria) : ?>
                <div class="border rounded-lg p-4 bg-gray-50">
                    <!-- Categoria Principal -->
                    <div class="flex justify-between items-center">
                        <h3 class="text-lg font-bold text-gray-800" data-id="<?php echo $categoria['id']; ?>"><?php echo htmlspecialchars($categoria['nome']); ?></h3>
                        <div class="flex items-center gap-3">
                            <button class="edit-btn text-blue-600 hover:text-blue-800" data-type="categoria" data-id="<?php echo $categoria['id']; ?>" data-name="<?php echo htmlspecialchars($categoria['nome']); ?>">Editar</button>
                            <a href="<?php echo BASE_URL; ?>/categorias/excluirCategoria/<?php echo $categoria['id']; ?>" class="delete-btn text-red-600 hover:text-red-800" onclick="return confirm('Atenção! Excluir esta categoria também removerá todos os seus segmentos. Clientes associados a ela serão desvinculados. Deseja continuar?');">Excluir</a>
                        </div>
                    </div>

                    <!-- Lista de Segmentos -->
                    <div class="mt-4 pl-6 border-l-2 border-gray-200 space-y-2">
                        <?php if (empty($categoria['segmentos'])) : ?>
                            <p class="text-sm text-gray-500">Nenhum segmento nesta categoria.</p>
                        <?php else : ?>
                            <?php foreach ($categoria['segmentos'] as $segmento) : ?>
                                <div class="flex justify-between items-center">
                                    <p class="text-gray-700" data-id="<?php echo $segmento['id']; ?>">- <?php echo htmlspecialchars($segmento['nome']); ?></p>
                                    <div class="flex items-center gap-3 text-sm">
                                        <button class="edit-btn text-blue-500 hover:text-blue-700" data-type="segmento" data-id="<?php echo $segmento['id']; ?>" data-name="<?php echo htmlspecialchars($segmento['nome']); ?>">Editar</button>
                                        <a href="<?php echo BASE_URL; ?>/categorias/excluirSegmento/<?php echo $segmento['id']; ?>" class="delete-btn text-red-500 hover:text-red-700" onclick="return confirm('Tem certeza que deseja excluir este segmento?');">Excluir</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Modal de Edição -->
<div id="edit-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Editar Nome</h3>
            <div class="mt-2 px-7 py-3">
                <input type="text" id="edit-input" class="w-full border-gray-300 rounded-lg shadow-sm p-2 text-sm">
                <input type="hidden" id="edit-id">
                <input type="hidden" id="edit-type">
            </div>
            <div class="items-center px-4 py-3">
                <button id="cancel-edit-btn" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 mr-2">Cancelar</button>
                <button id="save-edit-btn" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">Salvar</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const editModal = document.getElementById('edit-modal');
    const modalTitle = document.getElementById('modal-title');
    const editInput = document.getElementById('edit-input');
    const editIdInput = document.getElementById('edit-id');
    const editTypeInput = document.getElementById('edit-type');
    const saveBtn = document.getElementById('save-edit-btn');
    const cancelBtn = document.getElementById('cancel-edit-btn');

    document.querySelectorAll('.edit-btn').forEach(button => {
        button.addEventListener('click', function() {
            const type = this.dataset.type;
            const id = this.dataset.id;
            const name = this.dataset.name;

            modalTitle.textContent = `Editar ${type === 'categoria' ? 'Categoria' : 'Segmento'}`;
            editInput.value = name;
            editIdInput.value = id;
            editTypeInput.value = type;
            
            editModal.classList.remove('hidden');
            editInput.focus();
        });
    });

    function closeModal() {
        editModal.classList.add('hidden');
    }

    cancelBtn.addEventListener('click', closeModal);
    editModal.addEventListener('click', function(e) {
        if (e.target === editModal) {
            closeModal();
        }
    });

    saveBtn.addEventListener('click', async function() {
        const id = editIdInput.value;
        const type = editTypeInput.value;
        const newName = editInput.value.trim();

        if (!newName) {
            alert('O nome não pode ser vazio.');
            return;
        }

        const url = type === 'categoria' 
            ? '<?php echo BASE_URL; ?>/categorias/salvarCategoria' 
            : '<?php echo BASE_URL; ?>/categorias/salvarSegmento';

        const formData = new FormData();
        formData.append('id', id);
        formData.append('nome', newName);

        try {
            const response = await fetch(url, {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const result = await response.json();

            if (result.success) {
                // Atualiza o nome na tela dinamicamente
                const elementToUpdate = document.querySelector(`[data-id='${id}']`);
                if (elementToUpdate) {
                    elementToUpdate.textContent = type === 'segmento' ? `- ${newName}` : newName;
                    // Atualiza o data-name no botão de editar para futuras edições
                    const editButton = document.querySelector(`.edit-btn[data-id='${id}']`);
                    if(editButton) editButton.dataset.name = newName;
                }
                closeModal();
            } else {
                alert('Erro: ' + result.message);
            }
        } catch (error) {
            alert('Ocorreu um erro de comunicação.');
        }
    });
});
</script>