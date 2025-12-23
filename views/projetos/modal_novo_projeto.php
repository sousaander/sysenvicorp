<!-- Modal para Novo Projeto -->
<div id="modalNovoProjeto" class="fixed inset-0 bg-gray-800 bg-opacity-50 hidden z-50 flex items-center justify-center modal-transition">
    <div class="bg-white rounded-lg shadow-2xl max-h-[90vh] overflow-y-auto w-full max-w-4xl modal-transition">
        <!-- Header da Modal -->
        <div class="sticky top-0 bg-gradient-to-r from-violet-600 to-violet-700 px-8 py-6 flex justify-between items-center border-b-4 border-violet-800">
            <h2 class="text-2xl font-bold text-white">Novo Projeto</h2>
            <button id="closeModalBtn" type="button" class="text-white hover:text-gray-200 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Conteúdo da Modal -->
        <div class="px-8 py-6">
            <form id="formNovoProjeto" action="<?php echo BASE_URL; ?>/projetos/salvar" method="POST">
                <input type="hidden" name="id" value="">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- ID do Orçamento -->
                    <div>
                        <label for="modal_orcamento_id" class="block text-sm font-medium text-gray-700 mb-2">ID do Orçamento</label>
                        <input type="text" id="modal_orcamento_id" name="orcamento_id" class="w-full border border-gray-300 rounded-lg shadow-sm p-3 focus:ring-2 focus:ring-violet-500 focus:border-transparent transition-all" placeholder="Ex: ORC-2024-001">
                    </div>

                    <!-- Nome do Projeto -->
                    <div>
                        <label for="modal_nome" class="block text-sm font-medium text-gray-700 mb-2">Nome do Projeto <span class="text-red-500">*</span></label>
                        <input type="text" id="modal_nome" name="nome" required class="w-full border border-gray-300 rounded-lg shadow-sm p-3 focus:ring-2 focus:ring-violet-500 focus:border-transparent transition-all" placeholder="Ex: Estudo de Impacto Ambiental">
                    </div>

                    <!-- Tipo de Serviço -->
                    <div>
                        <label for="modal_tipo_servico" class="block text-sm font-medium text-gray-700 mb-2">Tipo do Serviço</label>
                        <input type="text" id="modal_tipo_servico" name="tipo_servico" class="w-full border border-gray-300 rounded-lg shadow-sm p-3 focus:ring-2 focus:ring-violet-500 focus:border-transparent transition-all" placeholder="Ex: Licenciamento Ambiental">
                    </div>

                    <!-- Cliente -->
                    <div>
                        <label for="modal_cliente_id" class="block text-sm font-medium text-gray-700 mb-2">Cliente <span class="text-red-500">*</span></label>
                        <select id="modal_cliente_id" name="cliente_id" required class="w-full border border-gray-300 rounded-lg shadow-sm p-3 focus:ring-2 focus:ring-violet-500 focus:border-transparent transition-all">
                            <option value="">Selecione um cliente</option>
                            <?php if (!empty($clientes)): ?>
                                <?php foreach ($clientes as $cliente): ?>
                                    <option value="<?php echo $cliente['id']; ?>">
                                        <?php echo htmlspecialchars($cliente['nome']); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <!-- Empreendimento -->
                    <div>
                        <label for="modal_empreendimento" class="block text-sm font-medium text-gray-700 mb-2">Empreendimento</label>
                        <input type="text" id="modal_empreendimento" name="empreendimento" class="w-full border border-gray-300 rounded-lg shadow-sm p-3 focus:ring-2 focus:ring-violet-500 focus:border-transparent transition-all" placeholder="Ex: Parque Industrial Zona Leste">
                    </div>

                    <!-- Data Inicial -->
                    <div>
                        <label for="modal_data_inicial" class="block text-sm font-medium text-gray-700 mb-2">Data Inicial</label>
                        <input type="date" id="modal_data_inicial" name="data_inicial" class="w-full border border-gray-300 rounded-lg shadow-sm p-3 focus:ring-2 focus:ring-violet-500 focus:border-transparent transition-all">
                    </div>

                    <!-- Data Fim Prevista -->
                    <div>
                        <label for="modal_data_fim_prevista" class="block text-sm font-medium text-gray-700 mb-2">Data de Fim Prevista</label>
                        <input type="date" id="modal_data_fim_prevista" name="data_fim_prevista" class="w-full border border-gray-300 rounded-lg shadow-sm p-3 focus:ring-2 focus:ring-violet-500 focus:border-transparent transition-all">
                    </div>

                    <!-- Orçamento -->
                    <div>
                        <label for="modal_orcamento" class="block text-sm font-medium text-gray-700 mb-2">Orçamento (R$)</label>
                        <input type="number" id="modal_orcamento" name="orcamento" step="0.01" class="w-full border border-gray-300 rounded-lg shadow-sm p-3 focus:ring-2 focus:ring-violet-500 focus:border-transparent transition-all" placeholder="0.00">
                    </div>

                    <!-- Tamanho em Hectares -->
                    <div>
                        <label for="modal_tamanho_ha" class="block text-sm font-medium text-gray-700 mb-2">Tamanho (ha)</label>
                        <input type="number" id="modal_tamanho_ha" name="tamanho_ha" step="0.01" class="w-full border border-gray-300 rounded-lg shadow-sm p-3 focus:ring-2 focus:ring-violet-500 focus:border-transparent transition-all" placeholder="0.00">
                    </div>

                    <!-- Area ID -->
                    <div>
                        <label for="modal_area_id" class="block text-sm font-medium text-gray-700 mb-2">ID da Área</label>
                        <input type="text" id="modal_area_id" name="area_id" class="w-full border border-gray-300 rounded-lg shadow-sm p-3 focus:ring-2 focus:ring-violet-500 focus:border-transparent transition-all" placeholder="Ex: AREA-001">
                    </div>

                    <!-- Produto Entregue -->
                    <div>
                        <label for="modal_produto_entregue" class="block text-sm font-medium text-gray-700 mb-2">Produto Entregue</label>
                        <input type="text" id="modal_produto_entregue" name="produto_entregue" class="w-full border border-gray-300 rounded-lg shadow-sm p-3 focus:ring-2 focus:ring-violet-500 focus:border-transparent transition-all" placeholder="Ex: Relatório Técnico">
                    </div>

                    <!-- Responsável Elaboração -->
                    <div>
                        <label for="modal_responsavel_elaboracao" class="block text-sm font-medium text-gray-700 mb-2">Responsável Elaboração</label>
                        <input type="text" id="modal_responsavel_elaboracao" name="responsavel_elaboracao" class="w-full border border-gray-300 rounded-lg shadow-sm p-3 focus:ring-2 focus:ring-violet-500 focus:border-transparent transition-all" placeholder="Nome">
                    </div>

                    <!-- Responsável (Técnico) -->
                    <div>
                        <label for="modal_responsavel" class="block text-sm font-medium text-gray-700 mb-2">Responsável Técnico</label>
                        <input type="text" id="modal_responsavel" name="responsavel" class="w-full border border-gray-300 rounded-lg shadow-sm p-3 focus:ring-2 focus:ring-violet-500 focus:border-transparent transition-all" placeholder="Nome">
                    </div>

                    <!-- Responsável Execução -->
                    <div>
                        <label for="modal_responsavel_execucao" class="block text-sm font-medium text-gray-700 mb-2">Responsável Execução</label>
                        <input type="text" id="modal_responsavel_execucao" name="responsavel_execucao" class="w-full border border-gray-300 rounded-lg shadow-sm p-3 focus:ring-2 focus:ring-violet-500 focus:border-transparent transition-all" placeholder="Nome">
                    </div>

                    <!-- Status -->
                    <div>
                        <label for="modal_status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select id="modal_status" name="status" class="w-full border border-gray-300 rounded-lg shadow-sm p-3 focus:ring-2 focus:ring-violet-500 focus:border-transparent transition-all">
                            <option value="Planejado" selected>Planejado</option>
                            <option value="Em Andamento">Em Andamento</option>
                            <option value="Concluído">Concluído</option>
                            <option value="Cancelado">Cancelado</option>
                        </select>
                    </div>

                    <!-- Observações (Full Width) -->
                    <div class="md:col-span-2">
                        <label for="modal_observacoes" class="block text-sm font-medium text-gray-700 mb-2">Observações</label>
                        <textarea id="modal_observacoes" name="observacoes" rows="4" class="w-full border border-gray-300 rounded-lg shadow-sm p-3 focus:ring-2 focus:ring-violet-500 focus:border-transparent transition-all" placeholder="Observações gerais sobre o projeto..."></textarea>
                    </div>
                </div>

                <!-- Botões de Ação -->
                <div class="flex justify-end gap-4 mt-8 pt-6 border-t">
                    <button type="button" id="cancelModalBtn" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50 transition-colors">
                        Cancelar
                    </button>
                    <button type="submit" class="px-6 py-2 bg-violet-600 text-white font-medium rounded-lg hover:bg-violet-700 transition-colors flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Criar Projeto
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Gerenciar Modal
    document.addEventListener('DOMContentLoaded', function() {
        const modalBtn = document.getElementById('btnNovoProjeto');
        const modal = document.getElementById('modalNovoProjeto');
        const closeModalBtn = document.getElementById('closeModalBtn');
        const cancelModalBtn = document.getElementById('cancelModalBtn');
        const form = document.getElementById('formNovoProjeto');

        // Abrir modal
        if (modalBtn) {
            modalBtn.addEventListener('click', function() {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                setTimeout(() => {
                    modal.querySelector('div:nth-child(2)').classList.add('modal-visible');
                }, 10);
                form.reset();
            });
        }

        // Fechar modal - botão X
        if (closeModalBtn) {
            closeModalBtn.addEventListener('click', closeModal);
        }

        // Fechar modal - botão Cancelar
        if (cancelModalBtn) {
            cancelModalBtn.addEventListener('click', closeModal);
        }

        // Fechar modal - clicando fora
        if (modal) {
            modal.addEventListener('click', function(event) {
                if (event.target === modal) {
                    closeModal();
                }
            });
        }

        function closeModal() {
            const content = modal.querySelector('div:nth-child(2)');
            content.classList.remove('modal-visible');
            setTimeout(() => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }, 300);
        }

        // Fechar modal com ESC
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
                closeModal();
            }
        });
    });
</script>