<div class="flex justify-between items-center mb-6">
    <div>
        <h2 class="text-2xl font-bold"><?php echo htmlspecialchars($pageTitle); ?></h2>
        <p class="text-gray-600">Revisões para: "<?php echo htmlspecialchars($proposta['titulo']); ?>"</p>
    </div>
    <a href="<?php echo BASE_URL; ?>/orcamento/verProposta/<?php echo $proposta['id']; ?>" class="px-4 py-2 text-sm font-semibold text-gray-700 bg-gray-200 rounded-lg shadow-md hover:bg-gray-300 transition">
        &larr; Voltar para Proposta
    </a>
</div>

<div class="bg-white p-6 rounded-lg shadow-md">
    <?php if (empty($historico)): ?>
        <p class="text-gray-600">Nenhum histórico de revisão encontrado para esta proposta.</p>
    <?php else: ?>
        <table class="w-full table-auto">
            <thead>
                <tr>
                    <th class="text-left p-2">Versão</th>
                    <th class="text-left p-2">Data da Revisão</th>
                    <th class="text-left p-2">Revisado por</th>
                    <th class="text-left p-2">Motivo da Alteração</th>
                    <th class="text-left p-2">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($historico as $h): ?>
                    <tr class="border-t">
                        <td class="p-2 font-semibold">v<?php echo $h['versao']; ?></td>
                        <td class="p-2"><?php echo date('d/m/Y H:i', strtotime($h['data_revisao'])); ?></td>
                        <td class="p-2"><?php echo htmlspecialchars($h['usuario_nome'] ?? 'Sistema'); ?></td>
                        <td class="p-2 text-gray-600 italic"><?php echo htmlspecialchars($h['motivo_alteracao'] ?? 'N/A'); ?></td>
                        <td class="p-2">
                            <a href="<?php echo BASE_URL; ?>/orcamento/verHistoricoDetalhe/<?php echo $h['id']; ?>" class="text-sky-600">Ver Detalhes</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>