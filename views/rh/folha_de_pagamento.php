<?php
// Define o locale para português para formatar os nomes dos meses corretamente.
setlocale(LC_TIME, 'pt_BR', 'pt_BR.utf-8', 'portuguese');
?>
<div class="flex justify-between items-start mb-6">
    <div>
        <h2 class="text-2xl font-bold">Folha de Pagamento</h2>
        <p class="text-gray-600">Gestão completa do ciclo de pagamento dos colaboradores.</p>
    </div>
    <a href="<?php echo BASE_URL; ?>/rh" class="px-4 py-2 text-sm font-semibold text-gray-700 bg-gray-200 rounded-lg shadow-md hover:bg-gray-300 transition">
        &larr; Voltar para RH
    </a>
</div>
<!-- Ações Principais -->
<div class="mb-6 flex flex-wrap gap-4 items-center">
    <form method="POST" class="p-4 bg-gray-50 rounded-lg border">
        <div>
            <label for="mes_folha" class="text-sm font-medium text-gray-700">Competência:</label>
            <div class="flex gap-2 mt-1">
                <select id="mes_folha" name="mes" class="rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <?php for ($m = 1; $m <= 12; $m++): ?>
                        <option value="<?php echo $m; ?>" <?php echo ($m == date('m')) ? 'selected' : ''; ?>>
                            <?php
                            // Solução segura e sem dependências para exibir os meses.
                            $meses = [
                                1 => 'Jan',
                                2 => 'Fev',
                                3 => 'Mar',
                                4 => 'Abr',
                                5 => 'Mai',
                                6 => 'Jun',
                                7 => 'Jul',
                                8 => 'Ago',
                                9 => 'Set',
                                10 => 'Out',
                                11 => 'Nov',
                                12 => 'Dez'
                            ];
                            echo $meses[$m];
                            ?>
                        </option>
                    <?php endfor; ?>
                </select>
                <select id="ano_folha" name="ano" class="rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <?php
                    $anoAtual = date('Y');
                    $anoFinal = max($anoAtual, 2025) + 3; // Garante que o ano atual esteja na lista e adiciona 3 anos futuros
                    for ($a = 2025; $a <= $anoFinal; $a++): ?>
                        <option value="<?php echo $a; ?>" <?php echo ($a == $anoAtual) ? 'selected' : ''; ?>>
                            <?php echo $a; ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>
        </div>
        <div class="mt-4 flex gap-2">
            <button type="submit" formaction="<?php echo BASE_URL; ?>/rh/lancamentos" class="px-4 py-2 font-semibold text-white bg-purple-600 rounded-lg shadow-md hover:bg-purple-700 transition">Lançar Eventos</button>
            <button type="submit" formaction="<?php echo BASE_URL; ?>/rh/calcularFolha" class="px-4 py-2 font-semibold text-white bg-blue-600 rounded-lg shadow-md hover:bg-blue-700 transition">
                Calcular Folha
            </button>
            <button type="submit" formaction="<?php echo BASE_URL; ?>/rh/encargos" class="px-4 py-2 font-semibold text-white bg-gray-600 rounded-lg shadow-md hover:bg-gray-700 transition">
                Ver Encargos
            </button>
        </div>
    </form>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- Coluna 1: Funcionalidades -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Cálculo Automático -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h3 class="text-lg font-semibold mb-4 border-b pb-2">Cálculo Automático</h3>
            <p class="text-gray-600">
                O sistema realizará o cálculo automático de salários, benefícios (vale-transporte, alimentação), descontos (faltas, adiantamentos) e horas extras.
            </p>
            <ul class="list-disc list-inside mt-4 text-gray-700 space-y-1">
                <li>Cálculo de salário base e adicionais.</li>
                <li>Gestão de benefícios e descontos variáveis.</li>
                <li>Apuração de ponto e horas extras.</li>
            </ul>
        </div>

        <!-- Geração de Documentos -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h3 class="text-lg font-semibold mb-4 border-b pb-2">Geração de Documentos</h3>
            <p class="text-gray-600">
                Gere holerites individuais ou em lote e prepare os informes de rendimentos para a declaração de imposto de renda.
            </p>
            <ul class="list-disc list-inside mt-4 text-gray-700 space-y-1">
                <li>Emissão de holerites (contracheques) em PDF.</li>
                <li>Geração do Informe de Rendimentos anual.</li>
                <li>Portal do funcionário para consulta de documentos (futuro).</li>
            </ul>
        </div>
    </div>

    <!-- Coluna 2: Integrações e Encargos -->
    <div class="lg:col-span-1 space-y-6">
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h3 class="text-lg font-semibold mb-4 border-b pb-2">Encargos Sociais</h3>
            <p class="text-gray-600">
                Cálculo e geração de guias para os principais encargos sociais.
            </p>
            <ul class="list-disc list-inside mt-4 text-gray-700 space-y-1">
                <li>Guia de INSS (GPS).</li>
                <li>Guia de FGTS (GRF).</li>
                <li>Guia de IRRF (DARF).</li>
            </ul>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h3 class="text-lg font-semibold mb-4 border-b pb-2">Integração</h3>
            <p class="text-gray-600">
                Exporte os dados da folha de pagamento em formatos compatíveis com os módulos de Contabilidade e Financeiro para conciliação automática.
            </p>
        </div>
    </div>
</div>