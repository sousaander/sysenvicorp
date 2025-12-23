<!-- Modal de Registro de Interação -->
<div id="interaction-modal" class="fixed z-10 inset-0 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Fundo do Modal -->
        <div id="modal-bg" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

        <!-- Conteúdo do Modal -->
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form action="<?php echo BASE_URL; ?>/clientes/registrarInteracao" method="POST">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                        Registrar Nova Interação
                    </h3>
                    <div class="mt-4 space-y-4">
                        <div>
                            <label for="cliente_id" class="block text-sm font-medium text-gray-700">Cliente <span class="text-red-500">*</span></label>
                            <select id="cliente_id" name="cliente_id" required class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-teal-500 focus:border-teal-500 sm:text-sm rounded-md">
                                <option value="">Selecione um cliente...</option>
                                <?php foreach ($todosClientes as $c) : ?>
                                    <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['nome']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label for="tipo_interacao" class="block text-sm font-medium text-gray-700">Tipo de Interação <span class="text-red-500">*</span></label>
                            <select id="tipo_interacao" name="tipo_interacao" required class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-teal-500 focus:border-teal-500 sm:text-sm rounded-md">
                                <option value="Ligação">Ligação</option>
                                <option value="E-mail">E-mail</option>
                                <option value="Reunião">Reunião</option>
                                <option value="Visita">Visita</option>
                                <option value="Outro">Outro</option>
                            </select>
                        </div>
                        <div>
                            <label for="descricao" class="block text-sm font-medium text-gray-700">Descrição / Resumo <span class="text-red-500">*</span></label>
                            <textarea id="descricao" name="descricao" rows="4" required class="shadow-sm focus:ring-teal-500 focus:border-teal-500 mt-1 block w-full sm:text-sm border border-gray-300 rounded-md p-2"></textarea>
                        </div>
                        <input type="hidden" name="data_interacao" value="<?php echo date('Y-m-d H:i:s'); ?>">
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-teal-600 text-base font-medium text-white hover:bg-teal-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                        Salvar Interação
                    </button>
                    <button type="button" id="close-interaction-modal-btn" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:w-auto sm:text-sm">
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>