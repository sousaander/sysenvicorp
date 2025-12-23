<div class="max-w-5xl mx-auto">
    <!-- Header com gradiente e estilo moderno -->
    <div class="bg-white rounded-t-lg shadow-lg p-6 flex justify-between items-center border-b border-gray-200">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-violet-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                </svg>
                <?php echo isset($projeto['id']) ? 'Editar Projeto' : 'Novo Projeto'; ?>
            </h2>
            <p class="text-gray-500 mt-1 text-sm">Preencha as informações abaixo para <?php echo isset($projeto['id']) ? 'atualizar o' : 'cadastrar um novo'; ?> projeto.</p>
        </div>
        <?php if (isset($projeto['id'])): ?>
            <span class="bg-gray-100 text-gray-600 px-3 py-1 rounded-full text-sm font-medium border border-gray-200">
                ID: #<?php echo $projeto['id']; ?>
            </span>
        <?php endif; ?>
    </div>

    <form action="<?php echo BASE_URL; ?>/projetos/salvar" method="POST" class="bg-white shadow-xl rounded-b-lg overflow-hidden">
        <input type="hidden" name="id" value="<?php echo $projeto['id'] ?? ''; ?>">

        <div class="p-8 space-y-8">
            <!-- Seção 1: Informações Principais -->
            <div>
                <h3 class="text-lg font-semibold text-gray-800 border-b border-gray-200 pb-2 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-violet-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Informações Principais
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Nome -->
                    <div class="md:col-span-2">
                        <label for="nome" class="block text-sm font-medium text-gray-700 mb-1">Nome do Projeto <span class="text-red-500">*</span></label>
                        <input type="text" id="nome" name="nome" required value="<?php echo htmlspecialchars($projeto['nome'] ?? ''); ?>" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-violet-500 focus:border-violet-500 transition-colors py-2.5" placeholder="Ex: Estudo de Impacto Ambiental (EIA)">
                    </div>

                    <!-- Cliente -->
                    <div>
                        <label for="cliente_id" class="block text-sm font-medium text-gray-700 mb-1">Cliente <span class="text-red-500">*</span></label>
                        <select id="cliente_id" name="cliente_id" required class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-violet-500 focus:border-violet-500 py-2.5 bg-white">
                            <option value="">Selecione um cliente</option>
                            <?php if (!empty($clientes)): ?>
                                <?php foreach ($clientes as $cliente): ?>
                                    <?php $selected = (isset($projeto['cliente_id']) && $projeto['cliente_id'] == $cliente['id']) ? 'selected' : ''; ?>
                                    <option value="<?php echo $cliente['id']; ?>" <?php echo $selected; ?>>
                                        <?php echo htmlspecialchars($cliente['nome']); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <!-- Status -->
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status Atual</label>
                        <select id="status" name="status" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-violet-500 focus:border-violet-500 py-2.5 bg-white">
                            <option value="Planejado">Planejado / Aprovado</option>
                            <option value="Em Execução">Em Execução</option>
                            <option value="Aguardando Cliente">Aguardando Cliente</option>
                            <option value="Concluído">Concluído</option>
                            <option value="Atrasado">Atrasado</option>
                            <option value="Cancelado">Cancelado</option>
                        </select>
                    </div>

                    <!-- Tipo Serviço -->
                    <div>
                        <label for="tipo_servico" class="block text-sm font-medium text-gray-700 mb-1">Tipo do Serviço</label>
                        <input type="text" id="tipo_servico" name="tipo_servico" value="<?php echo htmlspecialchars($projeto['tipo_servico'] ?? ''); ?>" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-violet-500 focus:border-violet-500 py-2.5" placeholder="Ex: Licenciamento Ambiental">
                    </div>

                    <!-- Empreendimento -->
                    <div>
                        <label for="empreendimento" class="block text-sm font-medium text-gray-700 mb-1">Empreendimento</label>
                        <input type="text" id="empreendimento" name="empreendimento" value="<?php echo htmlspecialchars($projeto['empreendimento'] ?? ''); ?>" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-violet-500 focus:border-violet-500 py-2.5" placeholder="Ex: Usina Hidrelétrica">
                    </div>
                </div>
            </div>

            <!-- Seção 2: Prazos e Custos -->
            <div>
                <h3 class="text-lg font-semibold text-gray-800 border-b border-gray-200 pb-2 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-violet-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    Prazos e Custos
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Data Inicial -->
                    <div>
                        <label for="data_inicial" class="block text-sm font-medium text-gray-700 mb-1">Data de Início</label>
                        <input type="date" id="data_inicial" name="data_inicial" required value="<?php echo htmlspecialchars($projeto['data_inicial'] ?? ''); ?>" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-violet-500 focus:border-violet-500 py-2.5">
                    </div>

                    <!-- Data Fim -->
                    <div>
                        <label for="data_fim_prevista" class="block text-sm font-medium text-gray-700 mb-1">Previsão de Término</label>
                        <input type="date" id="data_fim_prevista" name="data_fim_prevista" value="<?php echo htmlspecialchars($projeto['data_fim_prevista'] ?? ''); ?>" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-violet-500 focus:border-violet-500 py-2.5">
                    </div>

                    <!-- Orçamento -->
                    <div>
                        <label for="orcamento" class="block text-sm font-medium text-gray-700 mb-1">Custo Operacional (R$)</label>
                        <div class="relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 sm:text-sm">R$</span>
                            </div>
                            <input type="number" id="orcamento" name="orcamento" step="0.01" value="<?php echo htmlspecialchars($projeto['orcamento'] ?? ''); ?>" class="w-full border-gray-300 rounded-lg pl-10 focus:ring-violet-500 focus:border-violet-500 py-2.5" placeholder="0.00">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Seção 3: Detalhes Técnicos -->
            <div>
                <h3 class="text-lg font-semibold text-gray-800 border-b border-gray-200 pb-2 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-violet-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Detalhes Técnicos
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <!-- ID Orçamento -->
                    <div>
                        <label for="orcamento_id" class="block text-sm font-medium text-gray-700 mb-1">ID Orçamento</label>
                        <input type="text" id="orcamento_id" name="orcamento_id" value="<?php echo htmlspecialchars($projeto['orcamento_id'] ?? ''); ?>" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-violet-500 focus:border-violet-500 py-2.5" placeholder="Ex: ORC-001">
                    </div>

                    <!-- ID Área -->
                    <div>
                        <label for="area_id" class="block text-sm font-medium text-gray-700 mb-1">ID Área</label>
                        <input type="text" id="area_id" name="area_id" value="<?php echo htmlspecialchars($projeto['area_id'] ?? ''); ?>" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-violet-500 focus:border-violet-500 py-2.5" placeholder="Ex: AREA-12">
                    </div>

                    <!-- Tamanho -->
                    <div>
                        <label for="tamanho_ha" class="block text-sm font-medium text-gray-700 mb-1">Tamanho (ha)</label>
                        <input type="number" step="0.01" id="tamanho_ha" name="tamanho_ha" value="<?php echo htmlspecialchars($projeto['tamanho_ha'] ?? ''); ?>" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-violet-500 focus:border-violet-500 py-2.5" placeholder="0.00">
                    </div>

                    <!-- Produto -->
                    <div class="lg:col-span-1">
                        <label for="produto_entregue" class="block text-sm font-medium text-gray-700 mb-1">Produto Final</label>
                        <input type="text" id="produto_entregue" name="produto_entregue" value="<?php echo htmlspecialchars($projeto['produto_entregue'] ?? ''); ?>" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-violet-500 focus:border-violet-500 py-2.5" placeholder="Ex: RIMA">
                    </div>
                </div>
            </div>

            <!-- Seção 4: Equipe Responsável -->
            <div>
                <h3 class="text-lg font-semibold text-gray-800 border-b border-gray-200 pb-2 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-violet-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                    Equipe Responsável
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label for="responsavel" class="block text-sm font-medium text-gray-700 mb-1">Responsável Técnico <span class="text-red-500">*</span></label>
                        <input type="text" id="responsavel" name="responsavel" required value="<?php echo htmlspecialchars($projeto['responsavel'] ?? ''); ?>" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-violet-500 focus:border-violet-500 py-2.5" placeholder="Nome do RT">
                    </div>
                    <div>
                        <label for="responsavel_elaboracao" class="block text-sm font-medium text-gray-700 mb-1">Resp. Elaboração</label>
                        <input type="text" id="responsavel_elaboracao" name="responsavel_elaboracao" value="<?php echo htmlspecialchars($projeto['responsavel_elaboracao'] ?? ''); ?>" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-violet-500 focus:border-violet-500 py-2.5" placeholder="Nome">
                    </div>
                    <div>
                        <label for="responsavel_execucao" class="block text-sm font-medium text-gray-700 mb-1">Resp. Execução</label>
                        <input type="text" id="responsavel_execucao" name="responsavel_execucao" value="<?php echo htmlspecialchars($projeto['responsavel_execucao'] ?? ''); ?>" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-violet-500 focus:border-violet-500 py-2.5" placeholder="Nome">
                    </div>
                </div>
            </div>

            <!-- Seção 5: Observações -->
            <div>
                <label for="observacoes" class="block text-sm font-medium text-gray-700 mb-1">Observações Gerais</label>
                <textarea id="observacoes" name="observacoes" rows="4" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-violet-500 focus:border-violet-500 py-2.5" placeholder="Insira detalhes adicionais sobre o projeto..."><?php echo htmlspecialchars($projeto['observacoes'] ?? ''); ?></textarea>
            </div>

        </div>

        <!-- Footer com Ações -->
        <div class="bg-gray-50 px-8 py-5 border-t border-gray-200 flex items-center justify-end gap-3">
            <?php
            // Determina a URL de retorno com base no status do projeto
            $urlVoltar = (isset($projeto['status']) && $projeto['status'] === 'Concluído')
                ? BASE_URL . '/projetos/arquivados'
                : BASE_URL . '/projetos';
            ?>
            <a href="<?php echo $urlVoltar; ?>" class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-violet-500 transition-colors">
                Cancelar
            </a>
            <button type="submit" class="px-5 py-2.5 text-sm font-medium text-white bg-violet-600 rounded-lg shadow-md hover:bg-violet-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-violet-500 transition-all transform hover:scale-105 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
                Salvar Projeto
            </button>
        </div>
    </form>

    <script>
        // Script para definir o valor selecionado do status
        document.addEventListener('DOMContentLoaded', function() {
            const statusSelect = document.getElementById('status');
            const statusValue = "<?php echo $projeto['status'] ?? 'Planejado'; ?>";
            if (statusSelect) {
                statusSelect.value = statusValue;
            }
        });
    </script>
</div>