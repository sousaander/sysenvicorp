<div class="flex justify-between items-center mb-6">
    <h3 class="text-xl font-semibold text-gray-800">Controle de ART / RRT</h3>
    <button id="open-art-modal-btn" class="bg-violet-600 text-white px-4 py-2 rounded-md hover:bg-violet-700 font-medium shadow-sm">
        + Adicionar Registro
    </button>
</div>

<!-- Tabela de Registros -->
<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Número</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Responsável Técnico</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Data Emissão</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ações</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            <?php if (!empty($arts)): ?>
                <?php foreach ($arts as $art): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($art['tipo']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600"><?php echo htmlspecialchars($art['numero']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600"><?php echo htmlspecialchars($art['responsavel_tecnico']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo date('d/m/Y', strtotime($art['data_emissao'])); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                <?php if ($art['status'] === 'Ativa' || $art['status'] === 'Paga') echo 'bg-green-100 text-green-800';
                                elseif ($art['status'] === 'Encerrada') echo 'bg-gray-100 text-gray-800';
                                else echo 'bg-yellow-100 text-yellow-800'; ?>">
                                <?php echo $art['status']; ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="#"
                                class="edit-art-btn ml-4 text-indigo-600 hover:text-indigo-900"
                                data-item='<?php echo json_encode($art, JSON_HEX_APOS | JSON_HEX_QUOT); ?>'
                                aria-label="Editar registro <?php echo htmlspecialchars($art['numero']); ?>">
                                Editar
                            </a>
                            <?php if ($art['documento_path']): ?>
                                <a href="<?php echo BASE_URL . '/uploads/art/' . $art['documento_path']; ?>" target="_blank" class="ml-4 text-blue-600 hover:text-blue-900">Ver ART</a>
                            <?php endif; ?>
                            <?php if ($art['boleto_path']): ?>
                                <a href="<?php echo BASE_URL . '/uploads/art/' . $art['boleto_path']; ?>" target="_blank" class="ml-4 text-green-600 hover:text-green-900">Ver Boleto</a>
                            <?php endif; ?>
                            <?php if ($art['comprovante_pgto_path']): ?>
                                <a href="<?php echo BASE_URL . '/uploads/art/' . $art['comprovante_pgto_path']; ?>" target="_blank" class="ml-4 text-purple-600 hover:text-purple-900">Ver Comprovante</a>
                            <?php endif; ?>

                            <a href="<?php echo BASE_URL . '/projetos/excluirArt/' . $art['id'] . '/' . $projeto['id']; ?>" onclick="return confirm('Tem certeza que deseja excluir este registro?')" class="ml-4 text-red-600 hover:text-red-900">Excluir</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">Nenhum registro de ART/RRT cadastrado para este projeto.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal para Adicionar/Editar ART -->
<div id="art-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-10 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center pb-3 border-b">
            <p class="text-2xl font-bold">Adicionar Registro de ART/RRT</p>
            <div id="close-art-modal" class="cursor-pointer z-50">
                <svg class="fill-current text-black" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                    <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z" />
                </svg>
            </div>
        </div>
        <div class="mt-5">
            <form action="<?php echo BASE_URL; ?>/projetos/salvarArt" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id" id="art_id">
                <input type="hidden" name="projeto_id" value="<?php echo $projeto['id']; ?>">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="tipo" class="block text-sm font-medium text-gray-700">Tipo</label>
                        <select name="tipo" id="tipo" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm p-2">
                            <option value="ART">ART (CREA)</option>
                            <option value="RRT">RRT (CAU)</option>
                        </select>
                    </div>
                    <div>
                        <label for="numero" class="block text-sm font-medium text-gray-700">Número do Registro</label>
                        <input type="text" name="numero" id="numero" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm p-2">
                    </div>
                    <div class="md:col-span-2">
                        <label for="responsavel_tecnico" class="block text-sm font-medium text-gray-700">Responsável Técnico</label>
                        <input type="text" name="responsavel_tecnico" id="responsavel_tecnico" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm p-2">
                    </div>
                    <div>
                        <label for="data_emissao" class="block text-sm font-medium text-gray-700">Data de Emissão</label>
                        <input type="date" name="data_emissao" id="data_emissao" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm p-2">
                    </div>
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                        <select name="status" id="status" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm p-2">
                            <option value="Emitida">Emitida</option>
                            <option value="Paga">Paga</option>
                            <option value="Ativa">Ativa</option>
                            <option value="Encerrada">Encerrada</option>
                        </select>
                    </div>
                    <div>
                        <label for="valor" class="block text-sm font-medium text-gray-700">Valor (R$)</label>
                        <input type="number" step="0.01" name="valor" id="valor" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm p-2">
                    </div>
                    <div>
                        <label for="documento_art" class="block text-sm font-medium text-gray-700">Anexar ART/RRT (.pdf)</label>
                        <input type="file" name="documento_art" id="documento_art" accept=".pdf" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-violet-50 file:text-violet-700 hover:file:bg-violet-100">
                    </div>
                    <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="boleto" class="block text-sm font-medium text-gray-700">Anexar Boleto (.pdf)</label>
                            <input type="file" name="boleto" id="boleto" accept=".pdf" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100">
                        </div>
                        <div>
                            <label for="comprovante_pgto" class="block text-sm font-medium text-gray-700">Anexar Comprovante Pgto. (.pdf)</label>
                            <input type="file" name="comprovante_pgto" id="comprovante_pgto" accept=".pdf" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100">
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end pt-4 mt-4 border-t">
                    <button type="button" id="cancel-art-modal" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline mr-2">Cancelar</button>
                    <button type="submit" class="bg-violet-600 hover:bg-violet-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Salvar Registro</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const modal = document.getElementById('art-modal');
        const openBtn = document.getElementById('open-art-modal-btn');
        const closeBtn = document.getElementById('close-art-modal');
        const cancelBtn = document.getElementById('cancel-art-modal');
        const editBtns = document.querySelectorAll('.edit-art-btn');
        const modalTitle = modal.querySelector('.text-2xl');
        const form = modal.querySelector('form');

        const resetForm = () => {
            form.reset();
            document.getElementById('art_id').value = '';
            modalTitle.textContent = 'Adicionar Registro de ART/RRT';
        };

        const openModal = () => {
            modal.classList.remove('hidden');
        };

        const closeModal = () => modal.classList.add('hidden');

        openBtn.addEventListener('click', () => {
            resetForm();
            openModal();
        });

        editBtns.forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                resetForm();
                const itemData = JSON.parse(btn.getAttribute('data-item'));

                document.getElementById('art_id').value = itemData.id;
                document.getElementById('tipo').value = itemData.tipo;
                document.getElementById('numero').value = itemData.numero;
                document.getElementById('responsavel_tecnico').value = itemData.responsavel_tecnico;
                document.getElementById('data_emissao').value = itemData.data_emissao;
                document.getElementById('status').value = itemData.status;
                document.getElementById('valor').value = itemData.valor;
                modalTitle.textContent = 'Editar Registro de ART/RRT';
                openModal();
            });
        });

        closeBtn.addEventListener('click', closeModal);
        cancelBtn.addEventListener('click', closeModal);
        modal.addEventListener('click', (event) => {
            if (event.target === modal) closeModal();
        });
    });
</script>