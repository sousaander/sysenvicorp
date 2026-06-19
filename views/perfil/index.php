<div class="flex justify-between items-center mb-6">
    <div>
        <h2 class="text-2xl font-bold"><?php echo htmlspecialchars($pageTitle); ?></h2>
        <p class="text-gray-600">Adicione, edite ou remova os perfis de acesso do sistema.</p>
    </div>
    <div class="flex items-center gap-4">
        <a href="<?php echo BASE_URL; ?>/usuario" class="px-4 py-2 text-sm font-semibold text-gray-700 bg-gray-200 rounded-lg shadow-md hover:bg-gray-300 transition">
            &larr; Voltar
        </a>
        <a href="<?php echo BASE_URL; ?>/perfil/form" class="px-4 py-2 text-sm font-semibold text-white bg-sky-600 rounded-lg shadow-md hover:bg-sky-700 transition">
            + Novo Perfil
        </a>
    </div>
</div>

<div class="bg-white p-6 rounded-lg shadow-md">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nome do Perfil</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descrição</th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Permissões</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            <?php if (!empty($perfis)): ?>
                <?php foreach ($perfis as $perfil): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            <?php echo htmlspecialchars($perfil['nome_perfil']); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <!-- CORREÇÃO: Remover htmlspecialchars daqui, pois já é aplicado ao salvar. -->
                            <?php echo nl2br($perfil['descricao'] ?? ''); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                            <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full bg-sky-100 text-sky-800">
                                <?php echo $perfil['permissoes_count'] ?? 0; ?> permissões
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <?php
                            // CORREÇÃO: Verifica se o ID do perfil é válido (> 0) antes de gerar os links.
                            // Isso previne erros para perfis com ID 0, que são inválidos.
                            $perfilId = $perfil['perfil_id'] ?? $perfil['id'] ?? 0;
                            $isInvalidId = ($perfilId <= 0);
                            $isAdminProfile = (strtolower($perfil['nome_perfil']) === 'admin');
                            ?>

                            <?php if ($isInvalidId): ?>
                                <span class="text-gray-400 cursor-not-allowed mr-4" title="Este perfil possui um ID inválido (0) e não pode ser editado.">Editar</span>
                            <?php else: ?>
                                <a href="<?php echo BASE_URL; ?>/perfil/editar/<?php echo $perfilId; ?>" class="text-indigo-600 hover:text-indigo-900 mr-4 font-medium">Editar</a>
                            <?php endif; ?>

                            <?php if ($isAdminProfile || $isInvalidId): ?>
                                <span class="text-gray-400 cursor-not-allowed" title="<?php echo $isAdminProfile ? 'O perfil Admin não pode ser excluído.' : 'Este perfil possui um ID inválido e não pode ser excluído.'; ?>">
                                    Excluir
                                </span>
                            <?php else: ?>
                                <form action="<?php echo BASE_URL; ?>/perfil/excluir/<?php echo $perfilId; ?>" method="post" class="inline">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                                    <button type="submit" class="text-red-600 hover:text-red-900 bg-transparent border-0 p-0 m-0" onclick="return confirm('Tem certeza que deseja excluir este perfil? Esta ação não pode ser desfeita.');">Excluir</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" class="px-6 py-4 text-center text-gray-500">Nenhum perfil cadastrado.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>