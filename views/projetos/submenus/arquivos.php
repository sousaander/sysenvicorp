<div class="flex justify-between items-center mb-6">
    <h3 class="text-xl font-semibold text-gray-800">🗂️ Arquivos Gerais do Projeto</h3>
    <button id="open-arquivo-modal-btn" class="bg-violet-600 text-white px-4 py-2 rounded-md hover:bg-violet-700 font-medium shadow-sm">
        + Novo Arquivo
    </button>
</div>

<!-- Tabela de Arquivos -->
<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nome do Arquivo</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Categoria</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Versão</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Enviado por</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Data</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ações</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            <?php if (!empty($arquivos)): ?>
                <?php foreach ($arquivos as $arq): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($arq['nome_arquivo']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600"><?php echo htmlspecialchars($arq['categoria']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">v<?php echo htmlspecialchars($arq['versao']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600"><?php echo htmlspecialchars($arq['usuario_nome'] ?? 'N/D'); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo date('d/m/Y', strtotime($arq['data_upload'])); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="<?php echo BASE_URL . '/uploads/projetos_arquivos/' . $arq['arquivo_path']; ?>" target="_blank" class="text-blue-600 hover:text-blue-900">Visualizar/Baixar</a>
                            <a href="<?php echo BASE_URL . '/projetos/excluirArquivo/' . $arq['id'] . '/' . $projeto['id']; ?>" onclick="return confirm('Tem certeza que deseja excluir este arquivo?')" class="ml-4 text-red-600 hover:text-red-900">Excluir</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">Nenhum arquivo cadastrado para este projeto.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal para Adicionar Arquivo -->
<div id="arquivo-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-10 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center pb-3 border-b">
            <p class="text-2xl font-bold">Adicionar Arquivo ao Projeto</p>
            <div id="close-arquivo-modal" class="cursor-pointer z-50">
                <svg class="fill-current text-black" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                    <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z" />
                </svg>
            </div>
        </div>
        <div class="mt-5">
            <form action="<?php echo BASE_URL; ?>/projetos/salvarArquivo" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id" id="arquivo_id">
                <input type="hidden" name="projeto_id" value="<?php echo $projeto['id']; ?>">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label for="nome_arquivo" class="block text-sm font-medium text-gray-700">Nome do Arquivo <span class="text-red-500">*</span></label>
                        <input type="text" name="nome_arquivo" id="nome_arquivo" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm p-2">
                    </div>
                    <div>
                        <label for="categoria" class="block text-sm font-medium text-gray-700">Categoria <span class="text-red-500">*</span></label>
                        <select name="categoria" id="categoria" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm p-2">
                            <option value="Fotos de Campo">Fotos de Campo</option>
                            <option value="Contratos">Contratos</option>
                            <option value="Planilhas">Planilhas</option>
                            <option value="Relatórios Parciais">Relatórios Parciais</option>
                            <option value="Comunicação / Correspondência">Comunicação / Correspondência</option>
                            <option value="Documentos Administrativos">Documentos Administrativos</option>
                            <option value="Arquivos Diversos">Arquivos Diversos</option>
                            <option value="ZIP/Compactados">ZIP/Compactados</option>
                            <option value="Modelos / Templates">Modelos / Templates</option>
                        </select>
                    </div>
                    <div>
                        <label for="versao" class="block text-sm font-medium text-gray-700">Versão</label>
                        <input type="text" name="versao" id="versao" value="1.0" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm p-2">
                    </div>
                    <div class="md:col-span-2">
                        <label for="arquivo" class="block text-sm font-medium text-gray-700">Anexar Arquivo <span class="text-red-500">*</span></label>
                        <input type="file" name="arquivo" id="arquivo" required class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-violet-50 file:text-violet-700 hover:file:bg-violet-100">
                    </div>
                    <div class="md:col-span-2">
                        <label for="descricao" class="block text-sm font-medium text-gray-700">Descrição</label>
                        <textarea name="descricao" id="descricao" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm p-2"></textarea>
                    </div>
                </div>

                <div class="flex items-center justify-end pt-4 mt-4 border-t">
                    <button type="button" id="cancel-arquivo-modal" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline mr-2">Cancelar</button>
                    <button type="submit" class="bg-violet-600 hover:bg-violet-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Salvar Arquivo</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const modal = document.getElementById('arquivo-modal');
        const openBtn = document.getElementById('open-arquivo-modal-btn');
        const closeBtn = document.getElementById('close-arquivo-modal');
        const cancelBtn = document.getElementById('cancel-arquivo-modal');
        const form = modal.querySelector('form');

        const resetForm = () => {
            form.reset();
            document.getElementById('arquivo_id').value = '';
            document.getElementById('arquivo').required = true;
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