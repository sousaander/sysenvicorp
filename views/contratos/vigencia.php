<h2 class="text-2xl font-bold mb-4"><?php echo htmlspecialchars($pageTitle); ?></h2>
<p class="mb-6 text-gray-600">Acompanhe os prazos e gerencie as renovações dos seus contratos de forma proativa.</p>

<?php
// Função auxiliar para renderizar uma tabela de contratos
function render_contratos_table($contratos, $title, $bgColor, $textColor)
{
    if (empty($contratos)) {
        return; // Não renderiza a tabela se não houver contratos
    }
?>
    <div class="bg-white p-6 rounded-lg shadow-md mb-8">
        <h3 class="text-lg font-semibold mb-4 border-b pb-2 flex items-center">
            <span class="w-4 h-4 rounded-full mr-3 <?php echo $bgColor; ?>"></span>
            <?php echo $title; ?> <span class="ml-2 text-sm font-normal text-gray-500">(<?php echo count($contratos); ?>)</span>
        </h3>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contratada</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Início</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valor</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vencimento</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Prazo</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($contratos as $contrato) : ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                <?php echo htmlspecialchars($contrato['parteContratada'] ?? 'N/A'); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo $contrato['data_inicio'] ? date('d/m/Y', strtotime($contrato['data_inicio'])) : 'N/A'; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($contrato['tipo']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">R$ <?php echo number_format($contrato['valor'], 2, ',', '.'); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold <?php echo $textColor; ?>">
                                <?php echo $contrato['vencimento'] ? date('d/m/Y', strtotime($contrato['vencimento'])) : 'N/A'; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm <?php echo $textColor; ?>">
                                <?php
                                if ($contrato['dias_para_vencer'] < 0) {
                                    echo "Vencido há " . abs($contrato['dias_para_vencer']) . " dias";
                                } else {
                                    echo $contrato['dias_para_vencer'] . " dias";
                                }
                                ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="<?php echo BASE_URL; ?>/contratos/detalhe/<?php echo $contrato['id']; ?>" class="text-indigo-600 hover:text-indigo-900 mr-3">
                                    Ver / Renovar
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php
}
?>

<!-- Renderiza as seções de contratos -->
<?php
render_contratos_table($vencidos, 'Contratos Vencidos', 'bg-red-500', 'text-red-600');
render_contratos_table($vencendo30, 'Vencendo nos Próximos 30 Dias', 'bg-orange-500', 'text-orange-600');
render_contratos_table($vencendo60, 'Vencendo entre 31 e 60 Dias', 'bg-yellow-500', 'text-yellow-600');
render_contratos_table($vencendo90, 'Vencendo entre 61 e 90 Dias', 'bg-sky-500', 'text-sky-600');
render_contratos_table($vigenciaLonga, 'Vigência Superior a 90 Dias', 'bg-green-500', 'text-green-600');


// Mensagem se nenhuma categoria tiver contratos
$totalContratos = count($vencidos) + count($vencendo30) + count($vencendo60) + count($vencendo90) + count($vigenciaLonga);
if ($totalContratos === 0) :
?>
    <div class="bg-white p-10 rounded-lg shadow-md text-center">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
            <path vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhum contrato "Em Vigência" encontrado</h3>
        <p class="mt-1 text-sm text-gray-500">
            Não há contratos ativos para exibir na gestão de vigência.
        </p>
        <div class="mt-6">
            <a href="<?php echo BASE_URL; ?>/contratos" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                </svg>
                Cadastrar Novo Contrato
            </a>
        </div>
    </div>
<?php endif; ?>