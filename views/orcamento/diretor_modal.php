<div class="space-y-6">
    <div class="flex items-center justify-between border-b pb-4">
        <div>
            <h3 class="text-xl font-bold text-gray-800 dark:text-white">
                <i class="fas fa-user-tie text-blue-500 mr-2"></i>Aprovação do Diretor
            </h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                Revise os detalhes da proposta antes de aprovar ou rejeitar.
            </p>
        </div>
        <button type="button" onclick="closeDiretorModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition text-2xl leading-none">&times;</button>
    </div>

    <div class="grid grid-cols-2 gap-4 text-sm">
        <div>
            <span class="text-gray-500 dark:text-gray-400 block text-xs font-bold uppercase tracking-wide">Proposta</span>
            <span class="font-bold text-gray-800 dark:text-white">#<?= htmlspecialchars($proposta['numero_proposta'] ?? $proposta['id']) ?></span>
        </div>
        <div>
            <span class="text-gray-500 dark:text-gray-400 block text-xs font-bold uppercase tracking-wide">Valor Total</span>
            <span class="font-bold text-gray-800 dark:text-white">R$ <?= number_format($proposta['total_final'] ?? 0, 2, ',', '.') ?></span>
        </div>
        <div>
            <span class="text-gray-500 dark:text-gray-400 block text-xs font-bold uppercase tracking-wide">Cliente</span>
            <span class="font-semibold text-gray-800 dark:text-white"><?= htmlspecialchars($proposta['cliente_nome'] ?? 'N/A') ?></span>
        </div>
        <div>
            <span class="text-gray-500 dark:text-gray-400 block text-xs font-bold uppercase tracking-wide">Título</span>
            <span class="font-semibold text-gray-800 dark:text-white"><?= htmlspecialchars($proposta['nome_proposta']) ?></span>
        </div>
        <div>
            <span class="text-gray-500 dark:text-gray-400 block text-xs font-bold uppercase tracking-wide">Responsável</span>
            <span class="font-semibold text-gray-800 dark:text-white"><?= htmlspecialchars($proposta['responsavel_nome'] ?? 'N/A') ?></span>
        </div>
        <div>
            <span class="text-gray-500 dark:text-gray-400 block text-xs font-bold uppercase tracking-wide">Data</span>
            <span class="font-semibold text-gray-800 dark:text-white"><?= date('d/m/Y', strtotime($proposta['data_proposta'])) ?></span>
        </div>
    </div>

    <?php if (!empty($proposta['descricao'])): ?>
    <div>
        <span class="text-gray-500 dark:text-gray-400 block text-xs font-bold uppercase tracking-wide mb-1">Descrição</span>
        <p class="text-sm text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-900/50 p-3 rounded-lg"><?= nl2br(htmlspecialchars($proposta['descricao'])) ?></p>
    </div>
    <?php endif; ?>

    <div class="flex justify-end gap-3 pt-4 border-t">
        <button type="button" onclick="openPropostaView(<?= $proposta['id'] ?>)" class="px-4 py-2 text-sm font-bold text-sky-600 hover:text-sky-800 dark:text-sky-400 dark:hover:text-sky-300 bg-sky-50 dark:bg-sky-900/20 hover:bg-sky-100 dark:hover:bg-sky-900/40 rounded-lg transition">
            <i class="fas fa-eye mr-1"></i> Visualizar Completo
        </button>
        <button type="button" onclick="rejeitarDiretor(<?= $proposta['id'] ?>)" class="px-4 py-2 text-sm font-bold text-rose-600 hover:text-white bg-rose-50 dark:bg-rose-900/20 hover:bg-rose-600 dark:hover:bg-rose-600 border border-rose-200 dark:border-rose-800 rounded-lg transition">
            <i class="fas fa-times mr-1"></i> Rejeitar
        </button>
        <button type="button" onclick="aprovarDiretor(<?= $proposta['id'] ?>, '<?= addslashes($cliente_email) ?>', '<?= addslashes($proposta['nome_proposta']) ?>', '<?= addslashes($cliente_telefone ?? '') ?>')" class="px-4 py-2 text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition shadow-lg shadow-emerald-200 dark:shadow-none">
            <i class="fas fa-check mr-1"></i> Aprovar
        </button>
    </div>
</div>
