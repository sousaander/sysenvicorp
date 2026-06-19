<?php
// Lista de códigos de afastamento para o eSocial/CAGED para facilitar a seleção.
$codigos_afastamento = [
    '' => 'Selecione um código...',
    'SJ2' => 'SJ2 - Despedida sem justa causa, pelo empregador',
    'RA1' => 'RA1 - Rescisão com justa causa, por iniciativa do empregador',
    'PD0' => 'PD0 - Rescisão contratual a pedido do empregado',
    'RA2' => 'RA2 - Rescisão por término do contrato a termo',
    'RC1' => 'RC1 - Rescisão por acordo entre as partes (Art. 484-A da CLT)',
    'FE1' => 'FE1 - Término de contrato de trabalho temporário',
    'FT1' => 'FT1 - Rescisão do contrato de trabalho por falecimento do empregado',
];
// Lista de categorias de trabalhador para o eSocial.
$categorias_trabalhador = [
    '101' => '101 - Empregado - Geral',
    '102' => '102 - Empregado - Trabalhador Rural por Pequeno Prazo',
    '103' => '103 - Empregado - Aprendiz',
    '104' => '104 - Empregado - Doméstico',
    '111' => '111 - Empregado - Contrato Verde e Amarelo',
    '721' => '721 - Contribuinte individual - Diretor não empregado, com FGTS',
    '901' => '901 - Estagiário',
];
?>
<div class="flex justify-between items-start mb-6">
    <div>
        <h2 class="text-2xl font-bold"><?php echo htmlspecialchars($pageTitle); ?></h2>
        <p class="text-gray-600">Calcule os valores devidos na rescisão de contrato de um colaborador.</p>
    </div>
    <a href="<?php echo BASE_URL; ?>/rh" class="px-4 py-2 text-sm font-semibold text-gray-700 bg-gray-200 rounded-lg shadow-md hover:bg-gray-300 transition">
        &larr; Voltar para RH
    </a>
</div>

<div class="bg-white p-6 rounded-lg shadow-md max-w-4xl mx-auto">
    <form action="<?php echo BASE_URL; ?>/rh/processarCalculoRescisao" method="POST">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Seleção de Funcionário -->
            <div class="md:col-span-3">
                <label for="funcionario_id" class="block text-sm font-medium text-gray-700 mb-1">Funcionário <span class="text-red-500">*</span></label>
                <select id="funcionario_id" name="funcionario_id" required class="w-full border-gray-300 rounded-lg shadow-sm focus:border-sky-500 focus:ring-sky-500 p-2">
                    <option value="">Selecione um funcionário...</option>
                    <?php if (!empty($funcionarios)) : ?>
                        <?php foreach ($funcionarios as $funcionario) : ?>
                            <option value="<?php echo htmlspecialchars($funcionario['id']); ?>" data-admissao="<?php echo htmlspecialchars($funcionario['data_admissao'] ?? ''); ?>">
                                <?php echo htmlspecialchars($funcionario['nome']); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <!-- Data de Admissão (preenchido via JS) -->
            <div>
                <label for="data_admissao" class="block text-sm font-medium text-gray-700 mb-1">Data de Admissão</label>
                <input type="date" id="data_admissao" name="data_admissao" readonly class="w-full border-gray-300 rounded-lg shadow-sm p-2 bg-gray-100 cursor-not-allowed">
            </div>

            <!-- Data de Desligamento -->
            <div>
                <label for="data_desligamento" class="block text-sm font-medium text-gray-700 mb-1">Data do Desligamento <span class="text-red-500">*</span></label>
                <input type="date" id="data_desligamento" name="data_desligamento" required class="w-full border-gray-300 rounded-lg shadow-sm focus:border-sky-500 focus:ring-sky-500 p-2">
            </div>

            <!-- Data do Aviso Prévio -->
            <div>
                <label for="data_aviso_previo" class="block text-sm font-medium text-gray-700 mb-1">Data do Aviso Prévio</label>
                <input type="date" id="data_aviso_previo" name="data_aviso_previo" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-sky-500 focus:ring-sky-500 p-2" title="Data em que o aviso prévio foi comunicado. Deixe em branco se não aplicável.">
            </div>

            <!-- Tipo de Contrato -->
            <div>
                <label for="tipo_contrato" class="block text-sm font-medium text-gray-700 mb-1">Tipo de Contrato</label>
                <select id="tipo_contrato" name="tipo_contrato" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-sky-500 focus:ring-sky-500 p-2">
                    <option value="Indeterminado">Prazo Indeterminado</option>
                    <option value="Determinado">Prazo Determinado</option>
                    <option value="Temporario">Temporário</option>
                </select>
            </div>

            <!-- Data Final do Contrato (para prazo determinado) -->
            <div id="campo_data_fim_contrato" class="hidden">
                <label for="data_fim_contrato" class="block text-sm font-medium text-gray-700 mb-1">Data Final do Contrato <span class="text-red-500">*</span></label>
                <input type="date" id="data_fim_contrato" name="data_fim_contrato" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-sky-500 focus:ring-sky-500 p-2">
            </div>

            <!-- Cód. Afastamento -->
            <div>
                <label for="cod_afastamento" class="block text-sm font-medium text-gray-700 mb-1">Cód. Afastamento</label>
                <select id="cod_afastamento" name="cod_afastamento" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-sky-500 focus:ring-sky-500 p-2">
                    <?php foreach ($codigos_afastamento as $codigo => $descricao) : ?>
                        <option value="<?php echo htmlspecialchars($codigo); ?>"><?php echo htmlspecialchars($descricao); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Motivo da Rescisão -->
            <div>
                <label for="motivo_rescisao" class="block text-sm font-medium text-gray-700 mb-1">Motivo da Rescisão <span class="text-red-500">*</span></label>
                <select id="motivo_rescisao" name="motivo_rescisao" required class="w-full border-gray-300 rounded-lg shadow-sm focus:border-sky-500 focus:ring-sky-500 p-2">
                    <option value="demissao_sem_justa_causa">Demissão sem Justa Causa</option>
                    <option value="pedido_demissao">Pedido de Demissão</option>
                    <option value="demissao_com_justa_causa">Demissão com Justa Causa</option>
                    <option value="termino_contrato">Término de Contrato de Experiência</option>
                    <option value="acordo_partes">Acordo entre as Partes (Art. 484-A CLT)</option>
                </select>
            </div>

            <!-- Aviso Prévio -->
            <div>
                <label for="aviso_previo" class="block text-sm font-medium text-gray-700 mb-1">Aviso Prévio <span class="text-red-500">*</span></label>
                <select id="aviso_previo" name="aviso_previo" required class="w-full border-gray-300 rounded-lg shadow-sm focus:border-sky-500 focus:ring-sky-500 p-2">
                    <option value="indenizado">Indenizado</option>
                    <option value="trabalhado">Trabalhado</option>
                    <option value="dispensado">Dispensado pelo Empregador</option>
                    <option value="nao_cumprido_empregado">Não Cumprido pelo Empregado (descontar)</option>
                    <option value="nao_se_aplica">Não se Aplica</option>
                </select>
            </div>

            <!-- Férias Vencidas -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Possui Férias Vencidas?</label>
                <div class="mt-2 space-x-4">
                    <label class="inline-flex items-center">
                        <input type="radio" name="ferias_vencidas" value="sim" class="form-radio h-4 w-4 text-indigo-600">
                        <span class="ml-2 text-sm text-gray-700">Sim</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="radio" name="ferias_vencidas" value="nao" class="form-radio h-4 w-4 text-indigo-600" checked>
                        <span class="ml-2 text-sm text-gray-700">Não</span>
                    </label>
                </div>
            </div>
        </div>

        <!-- SEÇÃO DE VALORES VARIÁVEIS -->
        <h3 class="text-lg font-medium text-gray-800 mt-8 border-t pt-6 mb-4">Valores Variáveis (Proventos e Descontos)</h3>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <!-- Horas Extras 50% -->
            <div>
                <label for="horas_extras_50_qtd" class="block text-sm font-medium text-gray-700 mb-1">Horas Extras 50% (Qtd)</label>
                <input type="number" step="0.01" id="horas_extras_50_qtd" name="horas_extras_50_qtd" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-sky-500 focus:ring-sky-500 p-2" placeholder="Ex: 10.5">
            </div>
            <!-- Horas Extras 100% -->
            <div>
                <label for="horas_extras_100_qtd" class="block text-sm font-medium text-gray-700 mb-1">Horas Extras 100% (Qtd)</label>
                <input type="number" step="0.01" id="horas_extras_100_qtd" name="horas_extras_100_qtd" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-sky-500 focus:ring-sky-500 p-2" placeholder="Ex: 4">
            </div>
            <!-- Comissões -->
            <div>
                <label for="comissoes" class="block text-sm font-medium text-gray-700 mb-1">Comissões (R$)</label>
                <input type="text" id="comissoes" name="comissoes" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-sky-500 focus:ring-sky-500 p-2" placeholder="Ex: 450,00">
            </div>
            <!-- Gratificações -->
            <div>
                <label for="gratificacoes" class="block text-sm font-medium text-gray-700 mb-1">Gratificações (R$)</label>
                <input type="text" id="gratificacoes" name="gratificacoes" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-sky-500 focus:ring-sky-500 p-2" placeholder="Ex: 100,00">
            </div>
            <!-- Adicional Noturno -->
            <div>
                <label for="adicional_noturno" class="block text-sm font-medium text-gray-700 mb-1">Adicional Noturno (R$)</label>
                <input type="text" id="adicional_noturno" name="adicional_noturno" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-sky-500 focus:ring-sky-500 p-2" placeholder="Ex: 180,50">
            </div>
            <!-- Adicional de Periculosidade -->
            <div>
                <label for="adicional_periculosidade" class="block text-sm font-medium text-gray-700 mb-1">Adic. Periculosidade (R$)</label>
                <input type="text" id="adicional_periculosidade" name="adicional_periculosidade" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-sky-500 focus:ring-sky-500 p-2" placeholder="Ex: 423,60">
            </div>
            <!-- DSR -->
            <div>
                <label for="dsr" class="block text-sm font-medium text-gray-700 mb-1">DSR (R$)</label>
                <input type="text" id="dsr" name="dsr" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-sky-500 focus:ring-sky-500 p-2" placeholder="Ex: 95,50">
            </div>
            <!-- Ajuste Saldo Devedor -->
            <div>
                <label for="ajuste_saldo_devedor" class="block text-sm font-medium text-gray-700 mb-1">Ajuste Saldo Devedor (R$)</label>
                <input type="text" id="ajuste_saldo_devedor" name="ajuste_saldo_devedor" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-sky-500 focus:ring-sky-500 p-2" placeholder="Ex: 25,00">
            </div>
            <!-- Adiantamento Salarial -->
            <div>
                <label for="adiantamento_salarial" class="block text-sm font-medium text-gray-700 mb-1">Adiant. Salarial (R$)</label>
                <input type="text" id="adiantamento_salarial" name="adiantamento_salarial" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-sky-500 focus:ring-sky-500 p-2" placeholder="Ex: 800,00">
            </div>
            <!-- Adiantamento 13º -->
            <div>
                <label for="adiantamento_13" class="block text-sm font-medium text-gray-700 mb-1">Adiant. 13º Salário (R$)</label>
                <input type="text" id="adiantamento_13" name="adiantamento_13" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-sky-500 focus:ring-sky-500 p-2" placeholder="Ex: 1250,00">
            </div>
            <!-- Outros Descontos -->
            <div>
                <label for="outros_descontos" class="block text-sm font-medium text-gray-700 mb-1">Outros Descontos (R$)</label>
                <input type="text" id="outros_descontos" name="outros_descontos" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-sky-500 focus:ring-sky-500 p-2" placeholder="Ex: 50,00">
            </div>
        </div>

        <!-- SEÇÃO DADOS CONTÁBEIS -->
        <h3 class="text-lg font-medium text-gray-800 mt-8 border-t pt-6 mb-4">Informações Adicionais para TRCT</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Remuneração Mês Anterior -->
            <div>
                <label for="remuneracao_mes_anterior" class="block text-sm font-medium text-gray-700 mb-1">Remuneração Mês Ant. (R$)</label>
                <input type="text" id="remuneracao_mes_anterior" name="remuneracao_mes_anterior" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-sky-500 focus:ring-sky-500 p-2" placeholder="Ex: 2500,00">
            </div>

            <!-- Pensão Alimentícia (%) TRCT -->
            <div title="Percentual a ser aplicado sobre o valor líquido da rescisão.">
                <label for="pensao_trct_percent" class="block text-sm font-medium text-gray-700 mb-1">Pensão Alim. (%) TRCT</label>
                <input type="number" step="0.01" id="pensao_trct_percent" name="pensao_trct_percent" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-sky-500 focus:ring-sky-500 p-2" placeholder="Ex: 10.5">
            </div>

            <!-- Pensão Alimentícia (%) FGTS -->
            <div title="Percentual a ser aplicado sobre o saldo do FGTS a ser sacado.">
                <label for="pensao_fgts_percent" class="block text-sm font-medium text-gray-700 mb-1">Pensão Alim. (%) FGTS</label>
                <input type="number" step="0.01" id="pensao_fgts_percent" name="pensao_fgts_percent" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-sky-500 focus:ring-sky-500 p-2" placeholder="Ex: 10.5">
            </div>

            <!-- Pensão Alimentícia (Valor Fixo) -->
            <div title="Valor fixo de pensão a ser descontado no TRCT. Se preenchido, o percentual (%) será ignorado.">
                <label for="pensao_alimenticia_valor" class="block text-sm font-medium text-gray-700 mb-1">Pensão Alim. (Valor Fixo R$)</label>
                <input type="text" id="pensao_alimenticia_valor" name="pensao_alimenticia_valor" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-sky-500 focus:ring-sky-500 p-2" placeholder="Ex: 350,00">
            </div>

            <!-- Dependentes para IRRF -->
            <div title="Número de dependentes para dedução na base de cálculo do Imposto de Renda.">
                <label for="dependentes" class="block text-sm font-medium text-gray-700 mb-1">Nº Dependentes (IRRF)</label>
                <input type="number" id="dependentes" name="dependentes" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-sky-500 focus:ring-sky-500 p-2" placeholder="0" value="0" min="0">
            </div>

            <!-- Categoria do Trabalhador -->
            <div>
                <label for="categoria_trabalhador" class="block text-sm font-medium text-gray-700 mb-1">Categoria do Trabalhador</label>
                <select id="categoria_trabalhador" name="categoria_trabalhador" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-sky-500 focus:ring-sky-500 p-2">
                    <option value="">Selecione a categoria...</option>
                    <?php foreach ($categorias_trabalhador as $codigo => $descricao) : ?>
                        <option value="<?php echo htmlspecialchars($codigo); ?>"><?php echo htmlspecialchars($descricao); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Código Sindical -->
            <div>
                <label for="codigo_sindical" class="block text-sm font-medium text-gray-700 mb-1">Código Sindical</label>
                <input type="text" id="codigo_sindical" name="codigo_sindical" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-sky-500 focus:ring-sky-500 p-2">
            </div>

            <!-- CNPJ Sindicato -->
            <div>
                <label for="cnpj_sindicato" class="block text-sm font-medium text-gray-700 mb-1">CNPJ Entidade Sindical</label>
                <div class="flex rounded-md shadow-sm">
                    <input type="text" id="cnpj_sindicato" name="cnpj_sindicato" class="w-full border-gray-300 rounded-l-lg shadow-sm focus:border-sky-500 focus:ring-sky-500 p-2 flex-1" placeholder="Digite o CNPJ">
                    <button type="button" id="buscar-cnpj-sindicato-btn" class="inline-flex items-center px-3 rounded-r-lg border border-l-0 border-gray-300 bg-gray-50 text-gray-500 text-sm hover:bg-gray-100">
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Nome Sindicato -->
            <div class="md:col-span-2">
                <label for="nome_sindicato" class="block text-sm font-medium text-gray-700 mb-1">Nome Entidade Sindical</label>
                <input type="text" id="nome_sindicato" name="nome_sindicato" class="w-full border-gray-300 rounded-lg shadow-sm p-2 bg-gray-100 cursor-not-allowed" readonly>
            </div>
        </div>

        <div class="mt-8 pt-4 border-t border-gray-200 flex justify-end">
            <button type="submit" class="px-6 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg shadow-md hover:bg-blue-700 transition">Calcular Rescisão</button>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const funcionarioSelect = document.getElementById('funcionario_id');
        const dataAdmissaoInput = document.getElementById('data_admissao');
        const motivoRescisaoSelect = document.getElementById('motivo_rescisao');
        const codAfastamentoSelect = document.getElementById('cod_afastamento');
        const tipoContratoSelect = document.getElementById('tipo_contrato');
        const campoDataFimContrato = document.getElementById('campo_data_fim_contrato');
        const dataFimContratoInput = document.getElementById('data_fim_contrato');

        funcionarioSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const dataAdmissao = selectedOption.getAttribute('data-admissao');
            dataAdmissaoInput.value = dataAdmissao || '';

            // Busca a remuneração do mês anterior automaticamente
            const funcionarioId = this.value;
            const remuneracaoInput = document.getElementById('remuneracao_mes_anterior');

            if (funcionarioId) {
                remuneracaoInput.value = 'Buscando...';
                fetch(`<?php echo BASE_URL; ?>/rh/getRemuneracaoAjax?id=${funcionarioId}`)
                    .then(response => response.json())
                    .then(data => {
                        remuneracaoInput.value = data.valor || '';
                    })
                    .catch(error => {
                        console.error('Erro ao buscar remuneração:', error);
                        remuneracaoInput.value = '';
                    });
            } else {
                remuneracaoInput.value = '';
            }
        });

        // Mapeia o motivo da rescisão para um código de afastamento sugerido.
        const motivoParaCodigoMap = {
            'demissao_sem_justa_causa': 'SJ2',
            'pedido_demissao': 'PD0',
            'demissao_com_justa_causa': 'RA1',
            'termino_contrato': 'RA2',
            'acordo_partes': 'RC1'
        };

        // Atualiza o código de afastamento automaticamente ao mudar o motivo.
        motivoRescisaoSelect.addEventListener('change', function() {
            const motivoSelecionado = this.value;
            const codigoSugerido = motivoParaCodigoMap[motivoSelecionado] || '';
            codAfastamentoSelect.value = codigoSugerido;
        });

        // Mostra/oculta o campo de data final do contrato.
        tipoContratoSelect.addEventListener('change', function() {
            if (this.value === 'Determinado' || this.value === 'Temporario') {
                campoDataFimContrato.classList.remove('hidden');
                dataFimContratoInput.required = true;
            } else {
                campoDataFimContrato.classList.add('hidden');
                dataFimContratoInput.required = false;
                dataFimContratoInput.value = '';
            }
        });

        // --- Lógica para busca de Sindicato por CNPJ ---
        const cnpjSindicatoInput = document.getElementById('cnpj_sindicato');
        const nomeSindicatoInput = document.getElementById('nome_sindicato');
        const buscarCnpjBtn = document.getElementById('buscar-cnpj-sindicato-btn');

        const buscarSindicatoPorCnpj = async () => {
            const cnpj = cnpjSindicatoInput.value.replace(/\D/g, ''); // Remove non-numeric characters

            if (cnpj.length !== 14) {
                alert('Por favor, digite um CNPJ válido com 14 dígitos.');
                return;
            }

            nomeSindicatoInput.value = 'Buscando...';
            buscarCnpjBtn.disabled = true;

            try {
                // Usa o endpoint proxy do sistema para evitar CORS
                const response = await fetch(`<?php echo BASE_URL; ?>/clientes/consultarCnpj/${cnpj}`);
                const data = await response.json();

                if (response.ok && data.razao_social) {
                    nomeSindicatoInput.value = data.razao_social;
                    // Formata o CNPJ no input
                    cnpjSindicatoInput.value = data.cnpj.replace(/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/, "$1.$2.$3/$4-$5");
                } else {
                    nomeSindicatoInput.value = '';
                    alert(data.message || 'CNPJ não encontrado ou inválido.');
                }
            } catch (error) {
                console.error('Erro ao buscar CNPJ do sindicato:', error);
                nomeSindicatoInput.value = '';
                alert('Ocorreu um erro ao tentar consultar o CNPJ.');
            } finally {
                buscarCnpjBtn.disabled = false;
            }
        };

        buscarCnpjBtn.addEventListener('click', buscarSindicatoPorCnpj);
    });
</script>