<?php
// Função para formatar valores monetários
function formatCurrency($value)
{
    return 'R$ ' . number_format($value ?? 0, 2, ',', '.');
}

// Calcula os totais gerais e a variância
$saldo_previsto = ($summary['receita_prevista'] ?? 0) - ($summary['despesa_prevista'] ?? 0);
$saldo_real = ($summary['receita_real'] ?? 0) - ($summary['despesa_real'] ?? 0);
$variancia = $saldo_real - $saldo_previsto;
$variancia_color_class = $variancia >= 0 ? 'text-green-700' : 'text-red-700';
?>

<div class="flex justify-between items-center mb-6">
    <h3 class="text-xl font-semibold text-gray-800">Controle de Orçamento</h3>
    <button id="open-orcamento-modal-btn" class="bg-violet-600 text-white px-4 py-2 rounded-md hover:bg-violet-700 font-medium shadow-sm">
        + Adicionar Item
    </button>
</div>

<!-- Cards de Resumo -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
    <div class="bg-green-50 p-4 rounded-lg shadow border-l-4 border-green-500">
        <h4 class="font-semibold text-gray-500 text-sm">Receita Prevista</h4>
        <p class="text-2xl font-bold text-green-700"><?php echo formatCurrency($summary['receita_prevista']); ?></p>
    </div>
    <div class="bg-green-100 p-4 rounded-lg shadow border-l-4 border-green-600">
        <h4 class="font-semibold text-gray-500 text-sm">Receita Realizada</h4>
        <p class="text-2xl font-bold text-green-800"><?php echo formatCurrency($summary['receita_real']); ?></p>
    </div>
    <div class="bg-red-50 p-4 rounded-lg shadow border-l-4 border-red-500">
        <h4 class="font-semibold text-gray-500 text-sm">Despesa Prevista</h4>
        <p class="text-2xl font-bold text-red-700"><?php echo formatCurrency($summary['despesa_prevista']); ?></p>
    </div>
    <div class="bg-red-100 p-4 rounded-lg shadow border-l-4 border-red-600">
        <h4 class="font-semibold text-gray-500 text-sm">Despesa Realizada</h4>
        <p class="text-2xl font-bold text-red-800"><?php echo formatCurrency($summary['despesa_real']); ?></p>
    </div>
</div>

<!-- Cards de Totais Gerais -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
    <div class="bg-blue-50 p-4 rounded-lg shadow border-l-4 border-blue-500">
        <h4 class="font-semibold text-gray-500 text-sm">Saldo Previsto</h4>
        <p class="text-2xl font-bold text-blue-700"><?php echo formatCurrency($saldo_previsto); ?></p>
    </div>
    <div class="bg-blue-100 p-4 rounded-lg shadow border-l-4 border-blue-600">
        <h4 class="font-semibold text-gray-500 text-sm">Saldo Realizado</h4>
        <p class="text-2xl font-bold text-blue-800"><?php echo formatCurrency($saldo_real); ?></p>
    </div>
    <div class="bg-gray-50 p-4 rounded-lg shadow border-l-4 <?php echo $variancia >= 0 ? 'border-green-500' : 'border-red-500'; ?>">
        <h4 class="font-semibold text-gray-500 text-sm">Variância (Real vs. Previsto)</h4>
        <p class="text-2xl font-bold <?php echo $variancia_color_class; ?>">
            <?php echo ($variancia >= 0 ? '+' : '') . formatCurrency($variancia); ?>
        </p>
    </div>
</div>

<!-- Tabela de Itens do Orçamento -->
<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Descrição</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Categoria</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Valor Previsto</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Valor Real</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ações</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            <?php if (!empty($itens_orcamento)): ?>
                <?php foreach ($itens_orcamento as $item): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($item['descricao']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm <?php echo $item['tipo'] === 'Receita' ? 'text-green-600' : 'text-red-600'; ?>"><?php echo $item['tipo']; ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($item['categoria']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo formatCurrency($item['valor_previsto']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 font-semibold"><?php echo formatCurrency($item['valor_real']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                <?php if ($item['status'] === 'Aprovado') echo 'bg-green-100 text-green-800';
                                elseif ($item['status'] === 'Rejeitado') echo 'bg-red-100 text-red-800';
                                else echo 'bg-yellow-100 text-yellow-800'; ?>">
                                <?php echo $item['status']; ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="#"
                                class="edit-orcamento-btn text-indigo-600 hover:text-indigo-900"
                                data-item='<?php echo json_encode($item, JSON_HEX_APOS | JSON_HEX_QUOT); ?>'
                                aria-label="Editar item <?php echo htmlspecialchars($item['descricao']); ?>">
                                Editar
                            </a>

                            <?php if ($item['comprovante_path']): ?>
                                <a href="<?php echo BASE_URL . '/uploads/comprovantes/' . $item['comprovante_path']; ?>" target="_blank" class="ml-4 text-blue-600 hover:text-blue-900">Ver Comprovante</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">Nenhum item de orçamento cadastrado para este projeto.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal para Adicionar/Editar Item do Orçamento -->
<div id="orcamento-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-10 mx-auto p-5 border w-full max-w-3xl shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center pb-3 border-b">
            <p id="modal-title" class="text-2xl font-bold">Adicionar Item ao Orçamento</p>
            <div id="close-orcamento-modal" class="cursor-pointer z-50">
                <svg class="fill-current text-black" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                    <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z" />
                </svg>
            </div>
        </div>
        <div class="mt-5">
            <form action="<?php echo BASE_URL; ?>/projetos/salvarItemOrcamento" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id" id="orcamento_id">
                <input type="hidden" name="projeto_id" value="<?php echo $projeto['id']; ?>">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label for="descricao" class="block text-sm font-medium text-gray-700">Descrição</label>
                        <input type="text" name="descricao" id="descricao" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm p-2">
                    </div>
                    <div>
                        <label for="tipo" class="block text-sm font-medium text-gray-700">Tipo</label>
                        <select name="tipo" id="tipo" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm p-2">
                            <option value="Despesa">Despesa</option>
                            <option value="Receita">Receita</option>
                        </select>
                    </div>
                    <div>
                        <label for="categoria" class="block text-sm font-medium text-gray-700">Categoria</label>
                        <select name="categoria" id="categoria" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm p-2">
                            <option value="Equipamentos">Equipamentos</option>
                            <option value="Mão de obra">Mão de obra</option>
                            <option value="Insumos">Insumos</option>
                            <option value="Serviços">Serviços</option>
                            <option value="Taxas">Taxas</option>
                            <option value="Outros">Outros</option>
                        </select>
                    </div>
                    <div>
                        <label for="valor_previsto" class="block text-sm font-medium text-gray-700">Valor Previsto</label>
                        <input type="number" step="0.01" name="valor_previsto" id="valor_previsto" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm p-2">
                    </div>
                    <div>
                        <label for="data_prevista" class="block text-sm font-medium text-gray-700">Data Prevista</label>
                        <input type="date" name="data_prevista" id="data_prevista" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm p-2">
                    </div>
                    <div class="md:col-span-2">
                        <label for="comprovante" class="block text-sm font-medium text-gray-700">Anexar Comprovante (Opcional)</label>
                        <input type="file" name="comprovante" id="comprovante" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-violet-50 file:text-violet-700 hover:file:bg-violet-100">
                    </div>
                    <div class="md:col-span-2">
                        <label for="observacoes" class="block text-sm font-medium text-gray-700">Observações</label>
                        <textarea name="observacoes" id="observacoes" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm p-2"></textarea>
                    </div>
                </div>

                <div class="flex items-center justify-end pt-4 mt-4 border-t">
                    <button type="button" id="cancel-orcamento-modal" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline mr-2">
                        Cancelar
                    </button>
                    <button type="submit" class="bg-violet-600 hover:bg-violet-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        Salvar Item
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const modal = document.getElementById('orcamento-modal');
        const openBtn = document.getElementById('open-orcamento-modal-btn');
        const closeBtn = document.getElementById('close-orcamento-modal');
        const cancelBtn = document.getElementById('cancel-orcamento-modal');
        const editBtns = document.querySelectorAll('.edit-orcamento-btn');
        const modalTitle = document.getElementById('modal-title');
        const form = modal.querySelector('form');

        // Função para limpar e resetar o formulário
        const resetForm = () => {
            form.reset();
            document.getElementById('orcamento_id').value = '';
            modalTitle.textContent = 'Adicionar Item ao Orçamento';
            // Adicionar aqui a lógica para mostrar campos que são escondidos na edição, se houver
        };

        // Função para abrir o modal
        const openModal = () => {
            modal.classList.remove('hidden');
        };

        // Função para fechar o modal
        const closeModal = () => modal.classList.add('hidden');

        // Evento para o botão de Adicionar
        openBtn.addEventListener('click', () => {
            resetForm();
            openModal();
        });

        // Eventos para os botões de Editar
        editBtns.forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                resetForm(); // Limpa o formulário antes de preencher

                const itemData = JSON.parse(btn.getAttribute('data-item'));

                // Preenche o formulário com os dados do item
                document.getElementById('orcamento_id').value = itemData.id;
                document.getElementById('descricao').value = itemData.descricao;
                document.getElementById('tipo').value = itemData.tipo;
                document.getElementById('categoria').value = itemData.categoria;
                document.getElementById('valor_previsto').value = itemData.valor_previsto;
                document.getElementById('data_prevista').value = itemData.data_prevista;
                document.getElementById('observacoes').value = itemData.observacoes || '';
                modalTitle.textContent = 'Editar Item do Orçamento';
                openModal();
            });
        });

        closeBtn.addEventListener('click', closeModal);
        cancelBtn.addEventListener('click', closeModal);
        modal.addEventListener('click', (event) => {
            if (event.target === modal) {
                closeModal();
            }
        });
    });
</script>

<!-- Gráfico de Orçamento -->
<div class="bg-white p-6 rounded-lg shadow-md mt-8">
    <h3 class="text-lg font-semibold mb-4">Análise Gráfica: Previsto vs. Realizado</h3>
    <div class="relative h-80 w-full">
        <canvas id="orcamentoChart"></canvas>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const ctx = document.getElementById('orcamentoChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Receitas', 'Despesas', 'Saldo Final'],
                datasets: [{
                    label: 'Previsto',
                    data: [
                        <?php echo $summary['receita_prevista'] ?? 0; ?>,
                        <?php echo $summary['despesa_prevista'] ?? 0; ?>,
                        <?php echo $saldo_previsto; ?>
                    ],
                    backgroundColor: 'rgba(156, 163, 175, 0.5)', // gray-400
                    borderColor: 'rgba(107, 114, 128, 1)', // gray-500
                    borderWidth: 1
                }, {
                    label: 'Realizado',
                    data: [
                        <?php echo $summary['receita_real'] ?? 0; ?>,
                        <?php echo $summary['despesa_real'] ?? 0; ?>,
                        <?php echo $saldo_real; ?>
                    ],
                    backgroundColor: 'rgba(139, 92, 246, 0.6)', // violet-500
                    borderColor: 'rgba(124, 58, 237, 1)', // violet-600
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                // Esta opção é crucial para o gráfico preencher o container sem distorcer
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value, index, values) {
                                return 'R$ ' + value.toLocaleString('pt-BR', {
                                    minimumFractionDigits: 2
                                });
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    label += new Intl.NumberFormat('pt-BR', {
                                        style: 'currency',
                                        currency: 'BRL'
                                    }).format(context.parsed.y);
                                }
                                return label;
                            }
                        }
                    }
                }
            }
        });
    });
</script>