<div class="flex justify-between items-center mb-6">
    <h3 class="text-xl font-semibold text-gray-800">CM – Controle de Mapas</h3>
    <button id="open-mapa-modal-btn" class="bg-violet-600 text-white px-4 py-2 rounded-md hover:bg-violet-700 font-medium shadow-sm">
        + Novo Mapa
    </button>
</div>

<!-- Tabela de Mapas -->
<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nome do Mapa</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Categoria</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Versão</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ações</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            <?php if (!empty($mapas)): ?>
                <?php foreach ($mapas as $mapa): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($mapa['nome_mapa']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600"><?php echo htmlspecialchars($mapa['categoria_mapa']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">v<?php echo htmlspecialchars($mapa['versao']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                <?php echo strtoupper(htmlspecialchars($mapa['tipo_arquivo'])); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="<?php echo BASE_URL . '/uploads/mapas/' . $mapa['mapa_path']; ?>" target="_blank" class="text-blue-600 hover:text-blue-900">Visualizar/Baixar</a>
                            <a href="<?php echo BASE_URL . '/projetos/excluirMapa/' . $mapa['id'] . '/' . $projeto['id']; ?>" onclick="return confirm('Tem certeza que deseja excluir este mapa?')" class="ml-4 text-red-600 hover:text-red-900">Excluir</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">Nenhum mapa cadastrado para este projeto.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal para Adicionar/Editar Mapa -->
<div id="mapa-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-10 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center pb-3 border-b">
            <p class="text-2xl font-bold">Adicionar Mapa</p>
            <div id="close-mapa-modal" class="cursor-pointer z-50">
                <svg class="fill-current text-black" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                    <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z" />
                </svg>
            </div>
        </div>
        <div class="mt-5">
            <form action="<?php echo BASE_URL; ?>/projetos/salvarMapa" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id" id="mapa_id">
                <input type="hidden" name="projeto_id" value="<?php echo $projeto['id']; ?>">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label for="nome_mapa" class="block text-sm font-medium text-gray-700">Nome do Mapa <span class="text-red-500">*</span></label>
                        <input type="text" name="nome_mapa" id="nome_mapa" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm p-2">
                    </div>
                    <div>
                        <label for="categoria_mapa" class="block text-sm font-medium text-gray-700">Categoria <span class="text-red-500">*</span></label>
                        <select name="categoria_mapa" id="categoria_mapa" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm p-2">
                            <option value="Mapa de Supressão">Mapa de Supressão</option>
                            <option value="Delimitação de Área">Delimitação de Área</option>
                            <option value="Mapa Topográfico">Mapa Topográfico</option>
                            <option value="Mapa Georreferenciado">Mapa Georreferenciado</option>
                            <option value="Mapa de APP / Vegetação">Mapa de APP / Vegetação</option>
                            <option value="Croqui">Croqui</option>
                            <option value="Planta">Planta</option>
                            <option value="Outro">Outro</option>
                        </select>
                    </div>
                    <div>
                        <label for="versao" class="block text-sm font-medium text-gray-700">Versão</label>
                        <input type="number" name="versao" id="versao" value="1" min="1" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm p-2">
                    </div>
                    <div class="md:col-span-2">
                        <label for="mapa_arquivo" class="block text-sm font-medium text-gray-700">Anexar Arquivo (.pdf, .shp, .kmz, .geojson) <span class="text-red-500">*</span></label>
                        <input type="file" name="mapa_arquivo" id="mapa_arquivo" required accept=".pdf,.shp,.shx,.dbf,.prj,.kmz,.kml,.geojson" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-violet-50 file:text-violet-700 hover:file:bg-violet-100">
                    </div>
                    <div class="md:col-span-2">
                        <label for="observacoes" class="block text-sm font-medium text-gray-700">Observações</label>
                        <textarea name="observacoes" id="observacoes" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm p-2"></textarea>
                    </div>
                </div>

                <div class="flex items-center justify-end pt-4 mt-4 border-t">
                    <button type="button" id="cancel-mapa-modal" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline mr-2">Cancelar</button>
                    <button type="submit" class="bg-violet-600 hover:bg-violet-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Salvar Mapa</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const modal = document.getElementById('mapa-modal');
        const openBtn = document.getElementById('open-mapa-modal-btn');
        const closeBtn = document.getElementById('close-mapa-modal');
        const cancelBtn = document.getElementById('cancel-mapa-modal');
        const form = modal.querySelector('form');

        const resetForm = () => {
            form.reset();
            document.getElementById('mapa_id').value = '';
            document.getElementById('mapa_arquivo').required = true;
        };

        const openModal = () => modal.classList.remove('hidden');
        const closeModal = () => modal.classList.add('hidden');

        openBtn.addEventListener('click', () => {
            resetForm();
            openModal();
        });

        closeBtn.addEventListener('click', closeModal);
        cancelBtn.addEventListener('click', closeModal);
        modal.addEventListener('click', (event) => {
            if (event.target === modal) closeModal();
        });
    });
</script>