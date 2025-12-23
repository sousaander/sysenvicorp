<?php
// Função auxiliar para renderizar a estrutura de forma recursiva
function renderMultipleNodes($nodes, $class = 'flex justify-center space-x-10')
{
    $html = "<div class=\"{$class}\">";
    foreach ($nodes as $node) {
        $html .= renderNode($node);
    }
    $html .= '</div>';
    return $html;
}

function renderConnectorLines($childrenCount)
{
    $html = '';
    // Linha vertical para baixo
    $html .= '<div class="h-8 w-px bg-gray-400"></div>';
    // Linha horizontal que conecta os filhos
    $html .= '<div class="relative">';
    if ($childrenCount > 1) {
        $html .= '<div class="absolute top-0 left-1/2 -translate-x-1/2 h-px bg-gray-400" style="width: calc(100% - 18rem); min-width: 0;"></div>';
    }
    $html .= '</div>';
    return $html;
}

function renderNode($node)
{
    // Define a cor da barra de progresso baseada no valor
    $progressoGeral = $node['progresso_geral'] ?? 0;
    $progressColor = 'bg-green-500';
    if ($node['progresso_geral'] < 50) {
        $progressColor = 'bg-red-500';
    } elseif ($node['progresso_geral'] < 85) {
        $progressColor = 'bg-yellow-500';
    }

    $nodeHtml = '
        <div class="flex flex-col items-center">
            <div class="node-card bg-white p-4 rounded-xl shadow-lg border-2 border-gray-300 w-64 max-w-full hover:shadow-xl transition duration-300" 
                 data-node-id="' . ($node['id'] ?? '0') . '" 
                 onclick="showNodeDetails(this)">
                <div class="text-sm font-bold text-gray-800">' . htmlspecialchars($node['cargo']) . '</div>
                <div class="text-xs text-gray-500 mt-1">Resp: ' . htmlspecialchars($node['responsavel']) . '</div>
                <div class="mt-3">
                    <p class="text-xs font-semibold text-gray-700 mb-1">Progresso Geral:</p>
                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                        <div class="' . $progressColor . ' h-2.5 rounded-full" style="width: ' . $progressoGeral . '%"></div>
                    </div>
                    <p class="text-xs font-bold mt-1 text-right ' . (str_contains($progressColor, 'red') ? 'text-red-600' : (str_contains($progressColor, 'yellow') ? 'text-yellow-600' : 'text-green-600')) . '">' . $progressoGeral . '%</p>
                </div>
            </div>
    ';

    if (!empty($node['children'])) {
        $nodeHtml .= renderConnectorLines(count($node['children']));

        $nodeHtml .= '<div class="flex justify-center space-x-10">';
        foreach ($node['children'] as $child) {
            $nodeHtml .= renderNode($child);
        }
        $nodeHtml .= '</div>';
    }
    $nodeHtml .= '</div>';

    return $nodeHtml;
}

?>

<h2 class="text-2xl font-bold mb-4">Módulo Organograma e Gestão de Metas</h2>
<p class="mb-6 text-gray-600">Visualize a estrutura da empresa e monitore o progresso das atividades estratégicas de cada cargo e departamento.</p>

<div class="flex justify-end mb-4">
    <button onclick="openAddCargoModal()" class="bg-violet-500 text-white px-4 py-2 rounded-lg shadow-md hover:bg-violet-600 transition">
        + Adicionar Novo Cargo
    </button>
</div>

<!-- Container do Organograma -->
<div class="bg-white p-8 rounded-lg shadow-xl overflow-x-auto min-h-[500px]">
    <div class="inline-block min-w-full text-center">
        <?php
        // Renderiza todos os nós raiz (cargos sem superior)
        if (!empty($hierarquia)) {
            foreach ($hierarquia as $rootNode) {
                echo renderNode($rootNode);
            }
        } else {
            echo '<div class="w-full text-center text-gray-500"><p>Nenhuma estrutura definida. Adicione um cargo de diretoria para começar.</p></div>';
        }
        ?>
    </div>
</div>

<!-- Modal para Edição e Monitoramento de Atividades -->
<div id="details-modal" class="fixed inset-0 bg-gray-900 bg-opacity-75 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-2xl p-0 w-11/12 max-w-2xl transform scale-95 transition-all">
        <form id="edit-cargo-form" action="<?php echo BASE_URL; ?>/organograma/atualizarCargo" method="POST">
            <input type="hidden" id="edit-node-id" name="id">

            <div class="p-6">
                <div class="flex justify-between items-center border-b pb-3 mb-4">
                    <h3 class="text-xl font-bold text-gray-800" id="modal-title">Detalhes do Cargo</h3>
                    <button type="button" onclick="closeModal()" class="text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
                </div>

                <div id="modal-content" class="space-y-4">
                    <!-- Conteúdo dinâmico será injetado aqui pelo JavaScript -->
                </div>

            </div>

            <div class="bg-gray-50 px-6 py-4 border-t flex justify-between items-center">
                <a id="delete-button" href="#" onclick="return confirm('Atenção! Excluir este cargo fará com que seus subordinados diretos subam para o nível da diretoria. As atividades associadas a este cargo serão perdidas. Deseja continuar?');" class="text-red-600 hover:text-red-800 text-sm font-medium">
                    Excluir Cargo
                </a>
                <div class="space-x-3">
                    <button type="button" onclick="closeModal()" class="bg-gray-300 text-gray-800 px-5 py-2 rounded-lg hover:bg-gray-400 transition">Cancelar</button>
                    <button type="submit" class="bg-green-600 text-white px-5 py-2 rounded-lg hover:bg-green-700 transition">Salvar Alterações</button>
                </div>
            </div>
        </form>

        <!-- Formulário para adicionar nova atividade (movido para fora do form principal) -->
        <div id="add-activity-form-container" class="hidden p-6 pt-0">
            <div class="mt-4 pt-4 border-t">
                <h4 class="text-md font-bold mb-2">Nova Atividade</h4>
                <div id="add-activity-form" class="space-y-3">
                    <input type="hidden" id="add-activity-node-id" name="estrutura_id">
                    <div>
                        <label for="new-activity-name" class="block text-sm font-medium text-gray-700">Nome da Atividade</label>
                        <input type="text" id="new-activity-name" name="nome" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-violet-500 focus:ring-violet-500">
                    </div>
                    <div>
                        <label for="new-activity-meta" class="block text-sm font-medium text-gray-700">Meta</label>
                        <input type="text" id="new-activity-meta" name="meta" placeholder="Ex: 100%, Concluído, Aprovado" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-violet-500 focus:ring-violet-500">
                    </div>
                    <div class="mt-4 flex justify-end space-x-2">
                        <button type="button" onclick="toggleAddActivityForm(false)" class="bg-gray-200 text-gray-700 px-4 py-1.5 rounded-md text-sm hover:bg-gray-300">Cancelar</button>
                        <button type="button" onclick="submitNewActivity()" class="bg-violet-600 text-white px-4 py-1.5 rounded-md text-sm hover:bg-violet-700">Salvar Atividade</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Adicionar Novo Cargo -->
<div id="add-cargo-modal" class="fixed inset-0 bg-gray-900 bg-opacity-75 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-2xl p-6 w-11/12 max-w-lg transform scale-95 transition-all">
        <form action="<?php echo BASE_URL; ?>/organograma/adicionarCargo" method="POST">
            <div class="flex justify-between items-center border-b pb-3 mb-4">
                <h3 class="text-xl font-bold text-gray-800">Adicionar Novo Cargo/Departamento</h3>
                <button type="button" onclick="closeAddCargoModal()" class="text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
            </div>

            <div class="space-y-4">
                <div>
                    <label for="cargo" class="block text-sm font-medium text-gray-700">Nome do Cargo <span class="text-red-500">*</span></label>
                    <input type="text" id="cargo" name="cargo" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-violet-500 focus:ring-violet-500">
                </div>
                <div>
                    <label for="responsavel" class="block text-sm font-medium text-gray-700">Responsável <span class="text-red-500">*</span></label>
                    <input type="text" id="responsavel" name="responsavel" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-violet-500 focus:ring-violet-500">
                </div>
                <div>
                    <label for="parent_id" class="block text-sm font-medium text-gray-700">Superior Imediato</label>
                    <select id="parent_id" name="parent_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-violet-500 focus:ring-violet-500">
                        <option value="">Nenhum (Nível de Diretoria)</option>
                        <option value="0">Diretoria (Subordinado a todos os Diretores)</option>
                        <?php foreach ($estrutura as $cargo): ?>
                            <option value="<?php echo $cargo['id']; ?>"><?php echo htmlspecialchars($cargo['cargo']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="mt-6 pt-4 border-t flex justify-end space-x-3">
                <button type="button" onclick="closeAddCargoModal()" class="bg-gray-300 text-gray-800 px-5 py-2 rounded-lg hover:bg-gray-400 transition">
                    Cancelar
                </button>
                <button type="submit" class="bg-violet-600 text-white px-5 py-2 rounded-lg hover:bg-violet-700 transition">
                    Adicionar Cargo
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const estruturaData = <?php echo json_encode($estrutura); ?>; // 'estrutura' agora vem do controller
    const modal = document.getElementById('details-modal');
    const modalTitle = document.getElementById('modal-title');
    const modalContent = document.getElementById('modal-content');

    function showNodeDetails(element) {
        const nodeId = element.dataset.nodeId;
        const node = estruturaData.find(item => item.id == nodeId);

        if (!node) return;

        modalTitle.textContent = `Detalhes e Metas: ${node.cargo}`;

        let contentHtml = `
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-gray-50 p-4 rounded-lg">
                <div>
                    <label for="edit-cargo-nome" class="block text-sm font-medium text-gray-700">Nome do Cargo</label>
                    <input type="text" id="edit-cargo-nome" name="cargo" value="${node.cargo}" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-violet-500 focus:ring-violet-500">
                </div>
                <div>
                    <label for="edit-responsavel" class="block text-sm font-medium text-gray-700">Responsável</label>
                    <input type="text" id="edit-responsavel" name="responsavel" value="${node.responsavel}" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-violet-500 focus:ring-violet-500">
                </div>
            </div>
            <h4 class="text-lg font-bold mt-4 mb-2">Metas e Atividades Chave (${node.cargo}):</h4>
            <div class="space-y-3">
        `;

        // A edição de atividades será implementada em um próximo passo
        node.atividades.forEach((atividade, index) => {
            let activityProgressColor = 'bg-green-500';
            if (atividade.progresso < 50) {
                activityProgressColor = 'bg-red-500';
            } else if (atividade.progresso < 85) {
                activityProgressColor = 'bg-yellow-500';
            }

            contentHtml += `
                <div id="activity-container-${atividade.id}" class="p-3 border border-gray-200 rounded-lg">
                    <div class="flex justify-between items-start">
                        <div class="flex-grow">
                            <label for="progresso-${atividade.id}" class="block text-sm font-medium text-gray-700">${atividade.nome} (Meta: ${atividade.meta})</label>
                            <input type="range" id="progresso-${atividade.id}" data-activity-id="${atividade.id}" min="0" max="100" value="${atividade.progresso}" class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer range-lg mt-2" oninput="updateProgressLabel('label-${atividade.id}', this.value)">
                            <div class="flex justify-between text-xs mt-1">
                                <span>0%</span>
                                <span id="label-${atividade.id}" class="font-bold text-sm ${atividade.progresso === 100 ? 'text-green-600' : 'text-gray-600'}">${atividade.progresso}%</span>
                                <span>100%</span>
                            </div>
                        </div>
                        <div class="flex-shrink-0 ml-4 space-x-2">
                            <button type="button" onclick="saveActivityChanges(${atividade.id})" title="Salvar Progresso" class="text-green-500 hover:text-green-700">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                            </button>
                            <button type="button" onclick="deleteActivity(${atividade.id})" title="Excluir Atividade" class="text-red-500 hover:text-red-700">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm4 0a1 1 0 012 0v6a1 1 0 11-2 0V8z" clip-rule="evenodd" /></svg>
                            </button>
                        </div>
                    </div>
                </div>
            `;
        });

        contentHtml += `</div>
            <button id="add-activity-btn" type="button" onclick="toggleAddActivityForm(true)" class="mt-4 text-sm text-violet-600 hover:text-violet-800 font-medium">
                + Adicionar Nova Atividade (Editável)
            </button> 
        `;

        // Atualiza o ID no formulário e o link de exclusão
        document.getElementById('edit-node-id').value = nodeId;
        document.getElementById('delete-button').href = `<?php echo BASE_URL; ?>/organograma/excluirCargo/${nodeId}`;

        modalContent.innerHTML = contentHtml;
        toggleAddActivityForm(false); // Garante que o form de nova atividade esteja oculto
        modal.classList.remove('hidden');
    }

    function updateProgressLabel(labelId, value) {
        const label = document.getElementById(labelId);
        label.textContent = `${value}%`;
        if (value == 100) {
            label.classList.remove('text-gray-600');
            label.classList.add('text-green-600');
        } else {
            label.classList.add('text-gray-600');
            label.classList.remove('text-green-600');
        }
    }

    function closeModal() {
        modal.classList.add('hidden');
        toggleAddActivityForm(false); // Oculta o form ao fechar o modal
    }

    // Funções para o modal de adicionar cargo
    const addCargoModal = document.getElementById('add-cargo-modal');

    function openAddCargoModal() {
        addCargoModal.classList.remove('hidden');
    }

    function closeAddCargoModal() {
        addCargoModal.classList.add('hidden');
    }

    // Funções para o formulário de adicionar atividade
    const addActivityContainer = document.getElementById('add-activity-form-container');
    const addActivityForm = document.getElementById('add-activity-form');

    function toggleAddActivityForm(show) {
        if (show) {
            // Popula o ID do nó no formulário de atividade
            const nodeId = document.getElementById('edit-node-id').value;
            document.getElementById('add-activity-node-id').value = nodeId;
            addActivityContainer.classList.remove('hidden');
        } else {
            addActivityContainer.classList.add('hidden');
            // Limpa os campos de input manualmente, pois reset() pode não funcionar em um div
            document.getElementById('new-activity-name').value = '';
            document.getElementById('new-activity-meta').value = '';
        }
    }

    function submitNewActivity() {
        const formData = new FormData();
        formData.append('estrutura_id', document.getElementById('add-activity-node-id').value);
        formData.append('nome', document.getElementById('new-activity-name').value);
        formData.append('meta', document.getElementById('new-activity-meta').value);

        fetch('<?php echo BASE_URL; ?>/organograma/adicionarAtividade', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Atualiza a UI dinamicamente
                    const newActivity = data.data;
                    const activitiesContainer = modalContent.querySelector('.space-y-3');

                    const newActivityHtml = `
                    <div id="activity-container-${newActivity.id}" class="p-3 border border-gray-200 rounded-lg">
                        <div class="flex justify-between items-start">
                            <div class="flex-grow">
                                <label for="progresso-${newActivity.id}" class="block text-sm font-medium text-gray-700">${newActivity.nome} (Meta: ${newActivity.meta})</label>
                                <input type="range" id="progresso-${newActivity.id}" data-activity-id="${newActivity.id}" min="0" max="100" value="0" class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer range-lg mt-2" oninput="updateProgressLabel('label-${newActivity.id}', this.value)">
                                <div class="flex justify-between text-xs mt-1">
                                    <span>0%</span><span id="label-${newActivity.id}" class="font-bold text-sm text-gray-600">0%</span><span>100%</span>
                                </div>
                            </div>
                            <div class="flex-shrink-0 ml-4 space-x-2">
                                <button type="button" onclick="saveActivityChanges(${newActivity.id})" title="Salvar Progresso" class="text-green-500 hover:text-green-700"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg></button>
                                <button type="button" onclick="deleteActivity(${newActivity.id})" title="Excluir Atividade" class="text-red-500 hover:text-red-700"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm4 0a1 1 0 012 0v6a1 1 0 11-2 0V8z" clip-rule="evenodd" /></svg></button>
                            </div>
                        </div>
                    </div>
                `;
                    activitiesContainer.insertAdjacentHTML('beforeend', newActivityHtml);

                    // Atualiza o objeto de dados global para refletir a nova atividade
                    const nodeId = formData.get('estrutura_id');
                    const node = estruturaData.find(item => item.id == nodeId);
                    if (node) {
                        node.atividades.push(newActivity);
                    }

                    toggleAddActivityForm(false); // Esconde o formulário
                } else {
                    alert('Erro: ' + data.message);
                }
            })
            .catch(error => console.error('Erro na requisição:', error));
    }

    function saveActivityChanges(activityId) {
        const container = document.getElementById(`activity-container-${activityId}`);
        const label = container.querySelector('label').innerText;
        const progress = container.querySelector('input[type=range]').value;

        // Extrair nome e meta do texto do label
        const nameMatch = label.match(/(.*) \(Meta:/);
        const metaMatch = label.match(/Meta: (.*)\)/);
        const nome = nameMatch ? nameMatch[1].trim() : '';
        const meta = metaMatch ? metaMatch[1].trim() : '';

        const formData = new FormData();
        formData.append('id', activityId);
        formData.append('nome', nome);
        formData.append('meta', meta);
        formData.append('progresso', progress);

        fetch('<?php echo BASE_URL; ?>/organograma/atualizarAtividade', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Feedback visual de sucesso (ex: piscar o botão de salvar)
                    const saveButton = container.querySelector('button[title="Salvar Progresso"]');
                    saveButton.classList.add('text-blue-500');
                    setTimeout(() => saveButton.classList.remove('text-blue-500'), 1500);
                } else {
                    alert('Erro ao salvar: ' + data.message);
                }
            })
            .catch(error => console.error('Erro na requisição:', error));
    }

    function deleteActivity(activityId) {
        if (!confirm('Tem certeza que deseja excluir esta atividade?')) {
            return;
        }

        fetch(`<?php echo BASE_URL; ?>/organograma/excluirAtividade/${activityId}`, {
                method: 'GET' // Ou POST, dependendo da sua preferência de API REST
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById(`activity-container-${activityId}`).remove();
                } else {
                    alert('Erro ao excluir: ' + data.message);
                }
            })
            .catch(error => console.error('Erro na requisição:', error));
    }
</script>