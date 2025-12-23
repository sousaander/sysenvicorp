<h2 class="text-2xl font-bold mb-4"><?php echo (!empty($proposta['id'])) ? 'Editar Proposta' : 'Nova Proposta'; ?></h2>

<?php
$id = $proposta['id'] ?? null;
$projeto_id = $proposta['projeto_id'] ?? null;
$titulo = $proposta['titulo'] ?? '';
$descricao = $proposta['descricao_tecnica'] ?? '';
$objetivo = $proposta['objetivo'] ?? '';
$valor = $proposta['valor_total'] ?? null;
$condicoes = $proposta['condicoes'] ?? '';
$status = $proposta['status'] ?? 'Rascunho';
$validade = $proposta['validade_proposta'] ?? '30';
$data_proposta = $proposta['data_proposta'] ?? date('Y-m-d');
$responsavel_interno_id = $proposta['responsavel_interno_id'] ?? null;
?>

<div class="bg-white p-6 rounded-lg shadow-md">
    <form method="post" action="<?php echo BASE_URL; ?>/orcamento/salvarProposta">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token ?? ''); ?>" />
        <?php if ($id): ?><input type="hidden" name="id" value="<?php echo $id; ?>" /><?php endif; ?>

        <!-- Seção 1: Tipo de Criação -->
        <div class="mb-6 border-b pb-4">
            <h3 class="text-lg font-semibold text-gray-800 mb-2">1. Como deseja criar a proposta?</h3>
            <div class="flex items-center space-x-6">
                <label class="flex items-center space-x-2 cursor-pointer">
                    <input type="radio" name="creation_type" value="from_scratch" class="form-radio text-violet-600" checked>
                    <span class="text-gray-700">Criar proposta do zero</span>
                </label>
                <label class="flex items-center space-x-2 cursor-pointer">
                    <input type="radio" name="creation_type" value="from_project" class="form-radio text-violet-600">
                    <span class="text-gray-700">Criar proposta vinculada a um projeto</span>
                </label>
            </div>
        </div>

        <!-- Seção 2: Criar do Zero (visível por padrão) -->
        <div id="section_from_scratch" class="space-y-4">
            <h3 class="text-lg font-semibold text-gray-800 mb-2">2. Dados da Nova Proposta</h3>
            <div class="mb-4">
                <label for="cliente_id_scratch" class="block text-sm font-medium text-gray-700">Cliente</label>
                <select id="cliente_id_scratch" name="cliente_id_scratch" class="w-full border rounded p-2">
                    <option value="">Selecione um cliente</option>
                    <?php if (!empty($clientes)): ?>
                        <?php foreach ($clientes as $cliente): ?>
                            <option value="<?php echo $cliente['id']; ?>" <?php echo (isset($proposta['cliente_id']) && $proposta['cliente_id'] == $cliente['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cliente['nome']); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <div class="mb-4">
                <label for="nome_projeto_scratch" class="block text-sm font-medium text-gray-700">Nome do Projeto (texto livre)</label>
                <input type="text" id="nome_projeto_scratch" name="nome_projeto_scratch" class="w-full border rounded p-2" placeholder="Ex: Estudo de Viabilidade Técnica">
            </div>
        </div>

        <!-- Seção 2: Vincular a Projeto (oculto por padrão) -->
        <div id="section_from_project" class="hidden space-y-4">
            <h3 class="text-lg font-semibold text-gray-800 mb-2">2. Dados do Projeto Vinculado</h3>
            <div class="mb-4">
                <label for="projeto_id" class="block text-sm font-medium text-gray-700">Projeto Vinculado</label>
                <select id="projeto_id" name="projeto_id" class="w-full border rounded p-2">
                    <option value="">-- Selecione um projeto --</option>
                    <?php if (!empty($projetos)): ?>
                        <?php foreach ($projetos as $projeto): ?>
                            <option value="<?php echo $projeto['id']; ?>" <?php echo ($projeto['id'] == $projeto_id) ? 'selected' : ''; ?>>
                                [#<?php echo $projeto['id']; ?>] <?php echo htmlspecialchars($projeto['nome']); ?> — Cliente: <?php echo htmlspecialchars($projeto['cliente_nome'] ?? 'N/A'); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <div id="project_details_container" class="bg-gray-50 p-4 rounded-lg border border-gray-200 space-y-3 hidden">
                <p class="text-sm"><strong class="font-medium text-gray-600">ID do Projeto:</strong> <span id="detail_id"></span></p>
                <p class="text-sm"><strong class="font-medium text-gray-600">Nome do Projeto:</strong> <span id="detail_nome"></span></p>
                <p class="text-sm"><strong class="font-medium text-gray-600">Cliente:</strong> <span id="detail_cliente"></span></p>
                <p class="text-sm"><strong class="font-medium text-gray-600">Responsável Técnico:</strong> <span id="detail_responsavel"></span></p>
                <p class="text-sm"><strong class="font-medium text-gray-600">Área Técnica / Setor:</strong> <span id="detail_tipo_servico"></span></p>
            </div>
            <div class="mb-4">
                <label for="observacoes_projeto" class="block text-sm font-medium text-gray-700">Observações sobre o projeto vinculado</label>
                <textarea id="observacoes_projeto" name="observacoes_projeto" rows="4" class="w-full border rounded p-2"></textarea>
            </div>
        </div>

        <!-- Seção 3: Informações Comerciais (Sempre visível) -->
        <div class="mt-6 pt-4 border-t">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">3. Informações Comerciais da Proposta</h3>
            <div class="space-y-4">
                <div class="mb-4">
                    <label for="titulo" class="block text-sm font-medium text-gray-700">Título da Proposta</label>
                    <input type="text" id="titulo" name="titulo" value="<?php echo htmlspecialchars($titulo); ?>" class="w-full border rounded p-2" required />
                </div>
                <div class="mb-4">
                    <label for="descricao_tecnica" class="block text-sm font-medium text-gray-700">Descrição Geral da Proposta</label>
                    <textarea id="descricao_tecnica" name="descricao_tecnica" rows="4" class="w-full border rounded p-2"><?php echo htmlspecialchars($descricao); ?></textarea>
                </div>
                <div class="mb-4">
                    <label for="objetivo" class="block text-sm font-medium text-gray-700">Objetivo do Projeto / Serviço</label>
                    <textarea id="objetivo" name="objetivo" rows="4" class="w-full border rounded p-2"><?php echo htmlspecialchars($objetivo); ?></textarea>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700">Situação</label>
                        <select id="status" name="status" class="w-full border rounded p-2">
                            <option value="Rascunho" <?php echo ($status === 'Rascunho') ? 'selected' : ''; ?>>Rascunho</option>
                            <option value="Enviada" <?php echo ($status === 'Enviada') ? 'selected' : ''; ?>>Enviada</option>
                            <option value="Aprovada" <?php echo ($status === 'Aprovada') ? 'selected' : ''; ?>>Aprovada</option>
                            <option value="Rejeitada" <?php echo ($status === 'Rejeitada') ? 'selected' : ''; ?>>Rejeitada</option>
                            <option value="Cancelada" <?php echo ($status === 'Cancelada') ? 'selected' : ''; ?>>Cancelada</option>
                        </select>
                    </div>
                    <div>
                        <label for="validade_proposta" class="block text-sm font-medium text-gray-700">Validade da Proposta</label>
                        <select id="validade_proposta" name="validade_proposta" class="w-full border rounded p-2">
                            <option value="30" <?php echo ($validade == '30') ? 'selected' : ''; ?>>30 dias</option>
                            <option value="60" <?php echo ($validade == '60') ? 'selected' : ''; ?>>60 dias</option>
                            <option value="90" <?php echo ($validade == '90') ? 'selected' : ''; ?>>90 dias</option>
                        </select>
                    </div>
                    <div>
                        <label for="data_proposta" class="block text-sm font-medium text-gray-700">Data da Proposta</label>
                        <input type="date" id="data_proposta" name="data_proposta" value="<?php echo htmlspecialchars($data_proposta); ?>" class="w-full border rounded p-2" />
                    </div>
                    <div>
                        <label for="responsavel_interno_id" class="block text-sm font-medium text-gray-700">Responsável Interno</label>
                        <select id="responsavel_interno_id" name="responsavel_interno_id" class="w-full border rounded p-2">
                            <option value="">Selecione um responsável</option>
                            <?php if (!empty($usuarios)): ?>
                                <?php foreach ($usuarios as $usuario): ?>
                                    <option value="<?php echo $usuario['id']; ?>" <?php echo ($responsavel_interno_id == $usuario['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($usuario['nome']); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                </div>
            </div>
        </div>

        <!-- Seção 4: Custos Operacionais -->
        <div class="mt-6 pt-4 border-t">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">4. Custos Operacionais</h3>

            <!-- Custo de Serviços -->
            <div class="mb-6">
                <div class="flex justify-between items-center mb-2">
                    <h4 class="font-semibold text-gray-700">Custo de Serviços</h4>
                    <button type="button" id="add-service-btn" class="px-3 py-1 bg-green-500 text-white text-sm rounded hover:bg-green-600">Adicionar Serviço</button>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-gray-100 text-sm">
                            <tr>
                                <th class="p-2 text-left w-1/4">Nome do Serviço</th>
                                <th class="p-2 text-left w-2/4">Descrição</th>
                                <th class="p-2 text-left">Qtd.</th>
                                <th class="p-2 text-left">Un.</th>
                                <th class="p-2 text-left">Vlr. Unit.</th>
                                <th class="p-2 text-left">Subtotal</th>
                                <th class="p-2 text-center">Ação</th>
                            </tr>
                        </thead>
                        <tbody id="services-container">
                            <!-- Linhas de serviço serão adicionadas aqui -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Custo de Materiais -->
            <div class="mb-6">
                <div class="flex justify-between items-center mb-2">
                    <h4 class="font-semibold text-gray-700">Custo de Materiais (Opcional)</h4>
                    <button type="button" id="add-material-btn" class="px-3 py-1 bg-green-500 text-white text-sm rounded hover:bg-green-600">Adicionar Material</button>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-gray-100 text-sm">
                            <tr>
                                <th class="p-2 text-left w-3/5">Nome do Material</th>
                                <th class="p-2 text-left">Qtd.</th>
                                <th class="p-2 text-left">Vlr. Unit.</th>
                                <th class="p-2 text-left">Subtotal</th>
                                <th class="p-2 text-center">Ação</th>
                            </tr>
                        </thead>
                        <tbody id="materials-container">
                            <!-- Linhas de material serão adicionadas aqui -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Custos Extras -->
            <div class="mt-6 border-t pt-4">
                <h4 class="font-semibold text-gray-700 mb-3">Custos Extras</h4>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                    <div>
                        <label for="custo_transporte" class="block text-sm font-medium text-gray-600">Transporte</label>
                        <input type="text" name="custo_transporte" id="custo_transporte" class="w-full border rounded p-1 text-sm extra-cost-input money">
                    </div>
                    <div>
                        <label for="custo_deslocamentos" class="block text-sm font-medium text-gray-600">Deslocamentos</label>
                        <input type="text" name="custo_deslocamentos" id="custo_deslocamentos" class="w-full border rounded p-1 text-sm extra-cost-input money">
                    </div>
                    <div>
                        <label for="custo_diarias" class="block text-sm font-medium text-gray-600">Diárias</label>
                        <input type="text" name="custo_diarias" id="custo_diarias" class="w-full border rounded p-1 text-sm extra-cost-input money">
                    </div>
                    <div>
                        <label for="custo_impostos" class="block text-sm font-medium text-gray-600">Impostos</label>
                        <input type="text" name="custo_impostos" id="custo_impostos" class="w-full border rounded p-1 text-sm extra-cost-input money">
                    </div>
                    <div>
                        <label for="custo_taxas_admin" class="block text-sm font-medium text-gray-600">Taxas Admin.</label>
                        <input type="text" name="custo_taxas_admin" id="custo_taxas_admin" class="w-full border rounded p-1 text-sm extra-cost-input money">
                    </div>
                    <div>
                        <label for="custo_outros" class="block text-sm font-medium text-gray-600">Outros Custos</label>
                        <input type="text" name="custo_outros" id="custo_outros" class="w-full border rounded p-1 text-sm extra-cost-input money">
                    </div>
                </div>
            </div>

        </div>

        <!-- Seção 5: Totais -->
        <div class="mt-6 pt-4 border-t">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">5. Totais da Proposta</h3>
            <div class="bg-gray-50 p-4 rounded-lg space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Subtotal Serviços:</span>
                    <span id="total-servicos" class="font-semibold text-gray-800">R$ 0,00</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Subtotal Materiais:</span>
                    <span id="total-materiais" class="font-semibold text-gray-800">R$ 0,00</span>
                </div>
                <div class="flex justify-between items-center border-b pb-3">
                    <span class="text-gray-600">Total Custos Extras:</span>
                    <span id="total-extras" class="font-semibold text-gray-800">R$ 0,00</span>
                </div>
                <div class="flex justify-between items-center font-bold text-lg pt-2">
                    <span class="text-gray-700">Total de Custos Operacionais:</span>
                    <span id="total-operacional" class="text-gray-900">R$ 0,00</span>
                </div>
                <div class="grid grid-cols-2 gap-4 pt-4">
                    <div>
                        <label for="impostos" class="block text-sm font-medium text-gray-600">Impostos (% ou R$)</label>
                        <input type="text" name="impostos" id="impostos" class="w-full border rounded p-1 text-sm extra-cost-input money">
                    </div>
                    <div>
                        <label for="descontos" class="block text-sm font-medium text-gray-600">Descontos (R$)</label>
                        <input type="text" name="descontos" id="descontos" class="w-full border rounded p-1 text-sm extra-cost-input money">
                    </div>
                </div>
                <div class="flex justify-between items-center font-bold text-xl pt-4 border-t mt-4">
                    <span class="text-blue-600">Valor Final Apresentado ao Cliente:</span>
                    <span id="valor_total_display" class="text-blue-700">R$ 0,00</span>
                    <input type="hidden" name="valor_total" id="valor_total_hidden">
                </div>
            </div>
        </div>

        <!-- Seção 6: Condições Comerciais -->
        <div class="mt-6 pt-4 border-t">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">6. Condições Comerciais</h3>
            <div class="space-y-4">
                <div>
                    <label for="forma_pagamento" class="block text-sm font-medium text-gray-700">Forma de Pagamento</label>
                    <textarea id="forma_pagamento" name="forma_pagamento" rows="3" class="w-full border rounded p-2"></textarea>
                </div>
                <div>
                    <label for="prazo_execucao" class="block text-sm font-medium text-gray-700">Prazo de Execução</label>
                    <input type="text" id="prazo_execucao" name="prazo_execucao" class="w-full border rounded p-2">
                </div>
                <div>
                    <label for="garantias" class="block text-sm font-medium text-gray-700">Garantias</label>
                    <textarea id="garantias" name="garantias" rows="3" class="w-full border rounded p-2"></textarea>
                </div>
                <div class="md:col-span-2">
                    <label for="condicoes" class="block text-sm font-medium text-gray-700">Regras Gerais / Outras Condições</label>
                    <textarea name="condicoes" id="condicoes" rows="4" class="w-full border rounded p-2"><?php echo htmlspecialchars($condicoes); ?></textarea>
                </div>
            </div>
        </div>

        <!-- Seção 7: Documentos -->
        <div class="mt-6 pt-4 border-t">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">7. Anexos (Opcional)</h3>
            <div>
                <label for="anexos" class="block text-sm font-medium text-gray-700">Anexar Arquivos (PDF, DOCX, XLSX, Imagens)</label>
                <input type="file" id="anexos" name="anexos[]" multiple class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-violet-50 file:text-violet-700 hover:file:bg-violet-100">
            </div>
        </div>

        <!-- Seção 8: Ações -->
        <div class="flex gap-4 mt-8 pt-6 border-t">
            <button type="submit" name="action" value="salvar_rascunho" class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">Salvar Rascunho</button>
            <button type="submit" name="action" value="gerar_pdf" class="px-4 py-2 bg-rose-600 text-white rounded hover:bg-rose-700">Gerar Proposta em PDF</button>
            <button type="submit" name="action" value="enviar_cliente" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Enviar para o Cliente</button>
            <button type="button" onclick="closePropostaModal()" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400 ml-auto">Cancelar</button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const creationTypeRadios = document.querySelectorAll('input[name="creation_type"]');
        const fromScratchSection = document.getElementById('section_from_scratch');
        const fromProjectSection = document.getElementById('section_from_project');
        const projectSelect = document.getElementById('projeto_id');
        const detailsContainer = document.getElementById('project_details_container');

        // Função para alternar a visibilidade das seções
        function toggleSections() {
            if (document.querySelector('input[name="creation_type"]:checked').value === 'from_project') {
                fromScratchSection.classList.add('hidden');
                fromProjectSection.classList.remove('hidden');
            } else {
                fromScratchSection.classList.remove('hidden');
                fromProjectSection.classList.add('hidden');
            }
        }

        // Adiciona listeners aos botões de rádio
        creationTypeRadios.forEach(radio => {
            radio.addEventListener('change', toggleSections);
        });

        // Função para buscar e exibir detalhes do projeto
        function fetchProjectDetails() {
            const projectId = projectSelect.value;
            if (!projectId) {
                detailsContainer.classList.add('hidden');
                return;
            }

            fetch(`<?php echo BASE_URL; ?>/orcamento/getProjectDetailsAjax/${projectId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data) {
                        const project = data.data;
                        document.getElementById('detail_id').textContent = project.id || 'N/A';
                        document.getElementById('detail_nome').textContent = project.nome || 'N/A';
                        document.getElementById('detail_cliente').textContent = project.cliente_nome || 'N/A';
                        document.getElementById('detail_responsavel').textContent = project.responsavel || 'N/A';
                        document.getElementById('detail_tipo_servico').textContent = project.tipo_servico || 'N/A';
                        detailsContainer.classList.remove('hidden');
                    } else {
                        detailsContainer.classList.add('hidden');
                    }
                })
                .catch(error => {
                    console.error('Error fetching project details:', error);
                    detailsContainer.classList.add('hidden');
                });
        }

        // Adiciona listener ao select de projeto
        projectSelect.addEventListener('change', fetchProjectDetails);

        // Verifica o estado inicial ao carregar (para edição)
        <?php if ($id && $projeto_id): ?>
            // Se for uma edição e tiver um projeto vinculado, marca a opção correta e busca os detalhes
            document.querySelector('input[name="creation_type"][value="from_project"]').checked = true;
            toggleSections();
            fetchProjectDetails();
        <?php else: ?>
            // Garante que a seção correta esteja visível no carregamento inicial para um novo formulário
            toggleSections();
        <?php endif; ?>

        // --- LÓGICA PARA CUSTOS OPERACIONAIS ---

        const servicesContainer = document.getElementById('services-container');
        const materialsContainer = document.getElementById('materials-container');
        const addServiceBtn = document.getElementById('add-service-btn');
        const addMaterialBtn = document.getElementById('add-material-btn');

        let serviceIndex = 0;
        let materialIndex = 0;

        function formatCurrency(value) {
            return parseFloat(value).toLocaleString('pt-BR', {
                style: 'currency',
                currency: 'BRL'
            });
        }

        function parseCurrency(value) {
            return parseFloat(value.replace(/\./g, '').replace(',', '.')) || 0;
        }

        function addServiceRow() {
            const html = `
            <tr class="border-t service-row">
                <td class="p-1"><input type="text" name="servicos[${serviceIndex}][nome]" class="w-full border rounded p-1 text-sm"></td>
                <td class="p-1"><input type="text" name="servicos[${serviceIndex}][descricao]" class="w-full border rounded p-1 text-sm"></td>
                <td class="p-1"><input type="number" name="servicos[${serviceIndex}][quantidade]" class="w-20 border rounded p-1 text-sm service-qty" value="1" step="any"></td>
                <td class="p-1">
                    <select name="servicos[${serviceIndex}][unidade]" class="w-full border rounded p-1 text-sm">
                        <option>h</option><option>dia</option><option>mês</option><option selected>un</option>
                    </select>
                </td>
                <td class="p-1"><input type="text" name="servicos[${serviceIndex}][valor_unitario]" class="w-28 border rounded p-1 text-sm service-price money"></td>
                <td class="p-1"><input type="text" class="w-28 border rounded p-1 text-sm bg-gray-100 service-subtotal" readonly></td>
                <td class="p-1 text-center"><button type="button" class="text-red-500 hover:text-red-700 remove-row-btn">✖</button></td>
            </tr>
        `;
            servicesContainer.insertAdjacentHTML('beforeend', html);
            serviceIndex++;
        }

        function addMaterialRow() {
            const html = `
            <tr class="border-t material-row">
                <td class="p-1"><input type="text" name="materiais[${materialIndex}][nome]" class="w-full border rounded p-1 text-sm"></td>
                <td class="p-1"><input type="number" name="materiais[${materialIndex}][quantidade]" class="w-20 border rounded p-1 text-sm material-qty" value="1" step="any"></td>
                <td class="p-1"><input type="text" name="materiais[${materialIndex}][valor_unitario]" class="w-28 border rounded p-1 text-sm material-price money"></td>
                <td class="p-1"><input type="text" class="w-28 border rounded p-1 text-sm bg-gray-100 material-subtotal" readonly></td>
                <td class="p-1 text-center"><button type="button" class="text-red-500 hover:text-red-700 remove-row-btn">✖</button></td>
            </tr>
        `;
            materialsContainer.insertAdjacentHTML('beforeend', html);
            materialIndex++;
        }

        function updateRowSubtotal(row) {
            const qtyInput = row.querySelector('.service-qty, .material-qty');
            const priceInput = row.querySelector('.service-price, .material-price');
            const subtotalInput = row.querySelector('.service-subtotal, .material-subtotal');

            const qty = parseFloat(qtyInput.value) || 0;
            const price = parseCurrency(priceInput.value);
            const subtotal = qty * price;

            subtotalInput.value = subtotal.toLocaleString('pt-BR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
            updateTotals();
        }

        function updateTotals() {
            let totalServicos = 0;
            document.querySelectorAll('.service-row').forEach(row => {
                const subtotalInput = row.querySelector('.service-subtotal');
                totalServicos += parseCurrency(subtotalInput.value);
            });

            let totalMateriais = 0;
            document.querySelectorAll('.material-row').forEach(row => {
                const subtotalInput = row.querySelector('.material-subtotal');
                totalMateriais += parseCurrency(subtotalInput.value);
            });

        let totalExtras = 0; // Inclui os novos campos de impostos e descontos
        document.querySelectorAll('.extra-cost-input').forEach(input => {
            if (input.id !== 'descontos') { // Soma tudo, exceto o desconto
                 totalExtras += parseCurrency(input.value);
            }
        });

        const totalOperacional = totalServicos + totalMateriais + totalExtras;
        const impostosInput = document.getElementById('impostos');
        const descontos = parseCurrency(document.getElementById('descontos').value);

        let valorImpostos = 0;
        if (impostosInput.value.includes('%')) {
            const percent = parseFloat(impostosInput.value.replace('%', '')) || 0;
            valorImpostos = totalOperacional * (percent / 100);
        } else {
            valorImpostos = parseCurrency(impostosInput.value);
        }

            document.getElementById('total-servicos').textContent = formatCurrency(totalServicos);
            document.getElementById('total-materiais').textContent = formatCurrency(totalMateriais);
        document.getElementById('total-extras').textContent = formatCurrency(totalExtras - valorImpostos); // Mostra extras sem impostos
        document.getElementById('total-operacional').textContent = formatCurrency(totalOperacional);

        const grandTotal = totalOperacional + valorImpostos - descontos;

        document.getElementById('valor_total_display').textContent = formatCurrency(grandTotal);
        document.getElementById('valor_total_hidden').value = grandTotal.toFixed(2);
        }

        addServiceBtn.addEventListener('click', addServiceRow);
        addMaterialBtn.addEventListener('click', addMaterialRow);

        document.body.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-row-btn')) {
                e.target.closest('tr').remove();
                updateTotals();
            }
        });

        document.body.addEventListener('input', function(e) {
            if (e.target.matches('.service-qty, .service-price, .material-qty, .material-price')) {
                updateRowSubtotal(e.target.closest('tr')); // Atualiza subtotal da linha
            } else if (e.target.matches('.extra-cost-input')) {
                updateTotals(); // Apenas recalcula os totais gerais
            }
        });

        // Inicializa os totais ao carregar a página
        updateTotals();
    });
</script>