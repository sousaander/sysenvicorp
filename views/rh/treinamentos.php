<?php
// Verifica a permissão para gerenciar treinamentos
$canManage = $this->session->hasPermission('rh_treinamentos_manage');
?>

<div class="flex justify-between items-center mb-6">
    <div>
        <h2 class="text-2xl font-bold text-gray-800">Gestão de Treinamentos</h2>
        <p class="text-gray-600">Cadastre, edite e acompanhe os treinamentos e capacitações da equipe.</p>
    </div>
    <?php if ($canManage): ?>
        <button onclick="openModal()" class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition shadow-md flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Novo Treinamento
        </button>
    <?php endif; ?>
</div>

<div class="bg-white rounded-lg shadow-md overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Treinamento</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data Prevista</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Instrutor</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Local</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <?php if ($canManage): ?>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            <?php if (!empty($treinamentos)): ?>
                <?php foreach ($treinamentos as $t): ?>
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">
                            <?php echo htmlspecialchars($t['nome_treinamento']); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php echo date('d/m/Y H:i', strtotime($t['data_prevista'])); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php echo htmlspecialchars($t['instrutor'] ?? '-'); ?>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate" title="<?php echo htmlspecialchars($t['local'] ?? ''); ?>">
                            <?php echo htmlspecialchars($t['local'] ?? '-'); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                <?php
                                $status = $t['status'] ?? 'Agendado';
                                if ($status === 'Agendado') {
                                    echo 'bg-blue-100 text-blue-800';
                                } elseif ($status === 'Realizado') {
                                    echo 'bg-green-100 text-green-800';
                                } else {
                                    echo 'bg-red-100 text-red-800';
                                }
                                ?>">
                                <?php echo htmlspecialchars($status); ?>
                            </span>
                        </td>
                        <?php if ($canManage): ?>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button
                                    onclick="openModal(this)"
                                    data-id="<?php echo $t['id']; ?>"
                                    data-nome="<?php echo htmlspecialchars($t['nome_treinamento']); ?>"
                                    data-data="<?php echo date('Y-m-d\TH:i', strtotime($t['data_prevista'])); ?>"
                                    data-instrutor="<?php echo htmlspecialchars($t['instrutor'] ?? ''); ?>"
                                    data-local="<?php echo htmlspecialchars($t['local'] ?? ''); ?>"
                                    data-status="<?php echo htmlspecialchars($t['status'] ?? 'Agendado'); ?>"
                                    data-descricao="<?php echo htmlspecialchars($t['descricao'] ?? ''); ?>"
                                    class="text-indigo-600 hover:text-indigo-900 mr-3">Editar</button>
                                <a href="<?php echo BASE_URL; ?>/rh/excluirTreinamento/<?php echo $t['id']; ?>" class="text-red-600 hover:text-red-900" onclick="return confirm('Tem certeza que deseja excluir este treinamento?')">Excluir</a>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="<?php echo $canManage ? 6 : 5; ?>" class="px-6 py-10 text-center text-gray-500">
                        Nenhum treinamento agendado no momento.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Paginação -->
<?php if ($totalPaginas > 1): ?>
    <div class="mt-4 flex justify-center items-center">
        <nav class="flex items-center justify-center space-x-2">
            <a href="<?php echo BASE_URL; ?>/rh/treinamentos?page=<?php echo $paginaAtual - 1; ?>"
                class="<?php echo ($paginaAtual <= 1) ? 'pointer-events-none text-gray-400' : 'text-gray-600 hover:text-indigo-600'; ?> px-3 py-1 rounded-md text-sm font-medium">
                Anterior
            </a>
            <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                <a href="<?php echo BASE_URL; ?>/rh/treinamentos?page=<?php echo $i; ?>"
                    class="<?php echo ($i == $paginaAtual) ? 'bg-indigo-500 text-white' : 'bg-white text-gray-600 hover:bg-gray-100'; ?> px-3 py-1 rounded-md text-sm font-medium border">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
            <a href="<?php echo BASE_URL; ?>/rh/treinamentos?page=<?php echo $paginaAtual + 1; ?>"
                class="<?php echo ($paginaAtual >= $totalPaginas) ? 'pointer-events-none text-gray-400' : 'text-gray-600 hover:text-indigo-600'; ?> px-3 py-1 rounded-md text-sm font-medium">
                Próxima
            </a>
        </nav>
    </div>
<?php endif; ?>

<!-- Modal de Cadastro/Edição -->
<div id="modal-treinamento" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium text-gray-900">Agendar Novo Treinamento</h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-500">&times;</button>
        </div>
        <form action="<?php echo BASE_URL; ?>/rh/salvarTreinamento" method="POST">
            <input type="hidden" name="id" id="treinamento_id">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Nome do Treinamento <span class="text-red-500">*</span></label>
                    <input type="text" name="nome_treinamento" id="nome_treinamento" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm p-2 border">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Data Prevista <span class="text-red-500">*</span></label>
                        <input type="datetime-local" name="data_prevista" id="data_prevista" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm p-2 border">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Status</label>
                        <select name="status" id="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm p-2 border">
                            <option value="Agendado">Agendado</option>
                            <option value="Realizado">Realizado</option>
                            <option value="Cancelado">Cancelado</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Instrutor/Responsável</label>
                        <input type="text" name="instrutor" id="instrutor" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm p-2 border">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Local</label>
                        <input type="text" name="local" id="local" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm p-2 border">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Descrição</label>
                    <textarea name="descricao" id="descricao" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm p-2 border"></textarea>
                </div>
            </div>
            <div class="mt-6 flex justify-end gap-3">
                <button type="button" onclick="closeModal()" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">Cancelar</button>
                <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-md hover:bg-emerald-700">Salvar</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openModal(btn = null) {
        const modal = document.getElementById('modal-treinamento');
        const title = modal.querySelector('h3');

        if (btn) {
            // Modo Edição
            title.textContent = 'Editar Treinamento';
            document.getElementById('treinamento_id').value = btn.dataset.id;
            document.getElementById('nome_treinamento').value = btn.dataset.nome;
            document.getElementById('data_prevista').value = btn.dataset.data;
            document.getElementById('instrutor').value = btn.dataset.instrutor;
            document.getElementById('local').value = btn.dataset.local;
            document.getElementById('status').value = btn.dataset.status;
            document.getElementById('descricao').value = btn.dataset.descricao;
        } else {
            // Modo Novo
            title.textContent = 'Agendar Novo Treinamento';
            modal.querySelector('form').reset();
            document.getElementById('treinamento_id').value = '';
        }
        document.getElementById('modal-treinamento').classList.remove('hidden');
    }

    function closeModal() {
        document.getElementById('modal-treinamento').classList.add('hidden');
    }
</script>