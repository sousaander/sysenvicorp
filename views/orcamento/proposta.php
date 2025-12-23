<div class="flex justify-between items-center mb-6">
    <div>
        <h2 class="text-2xl font-bold">Propostas Comerciais</h2>
        <p class="text-gray-600">Crie e gerencie suas propostas.</p>
    </div>
    <button onclick="openPropostaModal('<?php echo BASE_URL; ?>/orcamento/novaProposta')" class="px-4 py-2 text-sm font-semibold text-white bg-sky-600 rounded-lg shadow-md hover:bg-sky-700 transition">
        + Nova Proposta
    </button>
</div>

<div class="bg-white p-6 rounded-lg shadow-md">
    <?php if (empty($propostas)): ?>
        <p class="text-gray-600">Nenhuma proposta encontrada.</p>
    <?php else: ?>
        <table class="w-full table-auto">
            <thead>
                <tr>
                    <th class="text-left p-2">ID</th>
                    <th class="text-left p-2">Título</th>
                    <th class="text-left p-2">Valor</th>
                    <th class="text-left p-2">Status</th>
                    <th class="text-left p-2">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($propostas as $p): ?>
                    <tr class="border-t">
                        <td class="p-2"><?php echo $p['id']; ?></td>
                        <td class="p-2"><?php echo htmlspecialchars($p['titulo']); ?></td>
                        <td class="p-2"><?php echo !empty($p['valor_total']) ? 'R$ ' . number_format($p['valor_total'], 2, ',', '.') : '-'; ?></td>
                        <td class="p-2"><?php echo htmlspecialchars($p['status']); ?></td>
                        <td class="p-2">
                            <a href="<?php echo BASE_URL; ?>/orcamento/verProposta/<?php echo $p['id']; ?>" class="text-sky-600 mr-2">Ver</a>
                            <a href="<?php echo BASE_URL; ?>/orcamento/pdfProposta/<?php echo $p['id']; ?>" target="_blank" class="text-rose-600 mr-2">PDF</a>
                            <a href="#" onclick="openPropostaModal('<?php echo BASE_URL; ?>/orcamento/novaProposta?id=<?php echo $p['id']; ?>')" class="text-yellow-600 mr-2">Editar</a>
                            <a href="#" onclick="openPropostaModal('<?php echo BASE_URL; ?>/orcamento/clonarProposta/<?php echo $p['id']; ?>')" class="text-green-600">Clonar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<!-- Estrutura da Modal -->
<div id="propostaModal" class="fixed inset-0 bg-gray-800 bg-opacity-75 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-3xl max-h-[90vh] overflow-y-auto">
        <!-- Conteúdo da modal será carregado aqui -->
        <div id="propostaModalContent" class="p-6">
            <p class="text-center">Carregando formulário...</p>
        </div>
    </div>
</div>

<script>
    const modal = document.getElementById('propostaModal');
    const modalContent = document.getElementById('propostaModalContent');

    function openPropostaModal(url) {
        modal.classList.remove('hidden');
        modalContent.innerHTML = '<p class="text-center p-8">Carregando formulário...</p>';

        // Adiciona um parâmetro para o controller saber que é uma requisição de modal
        const ajaxUrl = url.includes('?') ? `${url}&ajax=1` : `${url}?ajax=1`;

        fetch(ajaxUrl)
            .then(response => response.text())
            .then(html => {
                modalContent.innerHTML = html;
            })
            .catch(error => {
                modalContent.innerHTML = '<p class="text-center text-red-500 p-8">Erro ao carregar o formulário.</p>';
                console.error('Error loading form:', error);
            });
    }

    function closePropostaModal() {
        modal.classList.add('hidden');
        modalContent.innerHTML = '';
    }
</script>