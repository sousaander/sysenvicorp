<div class="max-w-5xl mx-auto">

    <!-- Cabeçalho -->
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center text-indigo-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <div>
                <h2 class="text-xl font-bold text-gray-800"><?php echo $pageTitle; ?></h2>
                <p class="text-sm text-gray-500">Preencha todas as informações da licença</p>
            </div>
        </div>
        <?php
        $status = $licenca['status'] ?? 'Vigente';
        $statusColors = [
            'Vigente'           => 'bg-green-100 text-green-800',
            'Pendente Renovação'=> 'bg-orange-100 text-orange-800',
            'Vencida'           => 'bg-red-100 text-red-800',
            'Em Análise'        => 'bg-blue-100 text-blue-800',
        ];
        $color = $statusColors[$status] ?? 'bg-gray-100 text-gray-800';
        ?>
        <span class="px-3 py-1 rounded-full text-sm font-semibold <?php echo $color; ?>">
            <?php echo htmlspecialchars($status); ?>
        </span>
    </div>

    <form action="<?php echo BASE_URL; ?>/licencasOperacao/salvar" method="POST" id="formLicenca">
        <input type="hidden" name="id" value="<?php echo $licenca['id'] ?? ''; ?>">
        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? '' ?>">

        <!-- Abas de navegação -->
        <div class="border-b border-gray-200 mb-6">
            <nav class="-mb-px flex gap-1" id="tabNav">
                <button type="button" onclick="switchTab('geral')" class="tab-btn active-tab px-4 py-2.5 text-sm font-medium border-b-2 border-indigo-500 text-indigo-600 transition">
                    Informações Gerais
                </button>
                <button type="button" onclick="switchTab('licenca')" class="tab-btn px-4 py-2.5 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 transition">
                    Licença & Validade
                </button>
                <button type="button" onclick="switchTab('custos')" class="tab-btn px-4 py-2.5 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 transition">
                    Custos & Contrato
                </button>
                <button type="button" onclick="switchTab('alertas')" class="tab-btn px-4 py-2.5 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 transition">
                    Alertas & Conformidade
                </button>
            </nav>
        </div>

        <!-- ===================== ABA: INFORMAÇÕES GERAIS ===================== -->
        <div id="tab-geral" class="tab-panel">

            <!-- Identificação -->
            <div class="bg-white border border-gray-200 rounded-lg p-6 mb-4">
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Identificação
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nome da Licença <span class="text-red-500">*</span></label>
                        <input type="text" name="nome" value="<?php echo htmlspecialchars($licenca['nome'] ?? ''); ?>" required
                            class="w-full rounded-md border-gray-300 border shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm p-2.5">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Número / Chave da Licença</label>
                        <input type="text" name="numero_licenca" value="<?php echo htmlspecialchars($licenca['numero_licenca'] ?? ''); ?>"
                            class="w-full rounded-md border-gray-300 border shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm p-2.5"
                            placeholder="ex: XXXXX-XXXXX-XXXXX">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Licença <span class="text-red-500">*</span></label>
                        <select name="tipo_licenca" required class="w-full rounded-md border-gray-300 border shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm p-2.5">
                            <option value="">Selecione...</option>
                            <?php
                            $tipos = ['Software / SaaS','Open Source','Hardware / OEM','API / Plataforma',
                                      'Patente','Marca Registrada','Direito Autoral','Franquia',
                                      'Alvará / Operação','Ambiental / Sanitária','Profissional','Importação / Exportação','Outro'];
                            foreach ($tipos as $t):
                                $sel = ($licenca['tipo_licenca'] ?? '') === $t ? 'selected' : '';
                            ?>
                                <option value="<?php echo $t; ?>" <?php echo $sel; ?>><?php echo $t; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Categoria</label>
                        <select name="categoria" class="w-full rounded-md border-gray-300 border shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm p-2.5">
                            <option value="">Selecione...</option>
                            <?php
                            $cats = ['Produtividade','Segurança','Infraestrutura','Design / Criativo','Financeiro','RH / Jurídico','Comercial / Regulatório'];
                            foreach ($cats as $c):
                                $sel = ($licenca['categoria'] ?? '') === $c ? 'selected' : '';
                            ?>
                                <option value="<?php echo $c; ?>" <?php echo $sel; ?>><?php echo $c; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Órgão Emissor / Fornecedor <span class="text-red-500">*</span></label>
                        <input type="text" name="orgao_emissor" value="<?php echo htmlspecialchars($licenca['orgao_emissor'] ?? ''); ?>" required
                            class="w-full rounded-md border-gray-300 border shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm p-2.5"
                            placeholder="ex: Microsoft, ANVISA, OAB...">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Produto / Serviço Vinculado</label>
                        <input type="text" name="produto_servico" value="<?php echo htmlspecialchars($licenca['produto_servico'] ?? ''); ?>"
                            class="w-full rounded-md border-gray-300 border shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm p-2.5"
                            placeholder="ex: Suite Office, ERP XYZ">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Departamento Responsável</label>
                        <select name="departamento" class="w-full rounded-md border-gray-300 border shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm p-2.5">
                            <option value="">Selecione...</option>
                            <?php
                            $deptos = ['TI','Jurídico','Financeiro','RH','Operações','Marketing','Diretoria'];
                            foreach ($deptos as $d):
                                $sel = ($licenca['departamento'] ?? '') === $d ? 'selected' : '';
                            ?>
                                <option value="<?php echo $d; ?>" <?php echo $sel; ?>><?php echo $d; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Número de Série / Registro</label>
                        <input type="text" name="numero_serie" value="<?php echo htmlspecialchars($licenca['numero_serie'] ?? ''); ?>"
                            class="w-full rounded-md border-gray-300 border shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm p-2.5"
                            placeholder="Nº de registro no órgão emissor">
                    </div>
                </div>
            </div>

            <!-- Responsáveis -->
            <div class="bg-white border border-gray-200 rounded-lg p-6 mb-4">
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    Responsáveis
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Gestor Responsável <span class="text-red-500">*</span></label>
                        <input type="text" name="gestor_responsavel" value="<?php echo htmlspecialchars($licenca['gestor_responsavel'] ?? ''); ?>" required
                            class="w-full rounded-md border-gray-300 border shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm p-2.5">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">E-mail do Responsável</label>
                        <input type="email" name="email_responsavel" value="<?php echo htmlspecialchars($licenca['email_responsavel'] ?? ''); ?>"
                            class="w-full rounded-md border-gray-300 border shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm p-2.5"
                            placeholder="gestor@empresa.com">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Usuário Principal / Beneficiário</label>
                        <input type="text" name="usuario_principal" value="<?php echo htmlspecialchars($licenca['usuario_principal'] ?? ''); ?>"
                            class="w-full rounded-md border-gray-300 border shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm p-2.5">
                    </div>
                </div>
            </div>

            <!-- Descrição -->
            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-4">Descrição & Observações</h3>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Descrição da Licença</label>
                    <textarea name="observacoes" rows="4"
                        class="w-full rounded-md border-gray-300 border shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm p-2.5"
                        placeholder="Descreva o escopo, finalidade e abrangência desta licença..."><?php echo htmlspecialchars($licenca['observacoes'] ?? ''); ?></textarea>
                </div>
            </div>
        </div>

        <!-- ===================== ABA: LICENÇA & VALIDADE ===================== -->
        <div id="tab-licenca" class="tab-panel hidden">

            <!-- Validade -->
            <div class="bg-white border border-gray-200 rounded-lg p-6 mb-4">
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    Validade & Vigência
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Data de Emissão</label>
                        <input type="date" name="data_emissao" value="<?php echo $licenca['data_emissao'] ?? ''; ?>"
                            class="w-full rounded-md border-gray-300 border shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm p-2.5">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Início de Vigência</label>
                        <input type="date" name="data_inicio_vigencia" value="<?php echo $licenca['data_inicio_vigencia'] ?? ''; ?>"
                            class="w-full rounded-md border-gray-300 border shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm p-2.5">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Data de Vencimento <span class="text-red-500">*</span></label>
                        <input type="date" name="data_vencimento" value="<?php echo $licenca['data_vencimento'] ?? ''; ?>" required
                            class="w-full rounded-md border-gray-300 border shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm p-2.5">
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Período de Renovação</label>
                        <select name="periodo_renovacao" class="w-full rounded-md border-gray-300 border shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm p-2.5">
                            <option value="">Selecione...</option>
                            <?php
                            $periodos = ['Mensal','Trimestral','Semestral','Anual','Bienal','Plurianual','Permanente'];
                            foreach ($periodos as $p):
                                $sel = ($licenca['periodo_renovacao'] ?? '') === $p ? 'selected' : '';
                            ?>
                                <option value="<?php echo $p; ?>" <?php echo $sel; ?>><?php echo $p; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Data da Última Renovação</label>
                        <input type="date" name="data_ultima_renovacao" value="<?php echo $licenca['data_ultima_renovacao'] ?? ''; ?>"
                            class="w-full rounded-md border-gray-300 border shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm p-2.5">
                    </div>
                </div>
            </div>

            <!-- Escopo de uso -->
            <div class="bg-white border border-gray-200 rounded-lg p-6 mb-4">
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Escopo & Restrições de Uso
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Modelo de Licença</label>
                        <select name="modelo_licenca" class="w-full rounded-md border-gray-300 border shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm p-2.5">
                            <option value="">Selecione...</option>
                            <?php
                            $modelos = ['Por usuário (named)','Por dispositivo','Concorrente / flutuante',
                                        'Site license','Corporativa ilimitada','Por CPU / núcleo',
                                        'Por consumo / uso','Freemium','Perpétua com manutenção'];
                            foreach ($modelos as $m):
                                $sel = ($licenca['modelo_licenca'] ?? '') === $m ? 'selected' : '';
                            ?>
                                <option value="<?php echo $m; ?>" <?php echo $sel; ?>><?php echo $m; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Quantidade de Licenças</label>
                        <input type="number" name="quantidade_licencas" min="1" value="<?php echo $licenca['quantidade_licencas'] ?? ''; ?>"
                            class="w-full rounded-md border-gray-300 border shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm p-2.5"
                            placeholder="ex: 25">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Licenças em Uso</label>
                        <input type="number" name="licencas_em_uso" min="0" value="<?php echo $licenca['licencas_em_uso'] ?? ''; ?>"
                            class="w-full rounded-md border-gray-300 border shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm p-2.5"
                            placeholder="ex: 18">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Abrangência Geográfica</label>
                    <select name="abrangencia" class="w-full md:w-1/2 rounded-md border-gray-300 border shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm p-2.5">
                        <option value="">Selecione...</option>
                        <?php
                        $abrangencias = ['Local (município)','Estadual','Nacional','Regional (América Latina)','Global'];
                        foreach ($abrangencias as $a):
                            $sel = ($licenca['abrangencia'] ?? '') === $a ? 'selected' : '';
                        ?>
                            <option value="<?php echo $a; ?>" <?php echo $sel; ?>><?php echo $a; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Status -->
            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-4">Status Atual</h3>
                <div class="flex flex-wrap gap-3">
                    <?php
                    $statusOpts = [
                        'Vigente'            => ['ring-green-500',  'bg-green-50',  'text-green-700'],
                        'Pendente Renovação' => ['ring-orange-500', 'bg-orange-50', 'text-orange-700'],
                        'Em Análise'         => ['ring-blue-500',   'bg-blue-50',   'text-blue-700'],
                        'Vencida'            => ['ring-red-500',    'bg-red-50',    'text-red-700'],
                    ];
                    foreach ($statusOpts as $val => [$ring, $bg, $txt]):
                        $checked = ($licenca['status'] ?? 'Vigente') === $val;
                    ?>
                        <label class="flex items-center gap-2 px-4 py-2 rounded-lg border cursor-pointer transition
                            <?php echo $checked ? "$bg border-transparent ring-2 $ring $txt font-semibold" : 'border-gray-200 text-gray-600 hover:bg-gray-50'; ?>">
                            <input type="radio" name="status" value="<?php echo $val; ?>" <?php echo $checked ? 'checked' : ''; ?>
                                class="accent-indigo-600" onchange="this.closest('.flex').querySelectorAll('label').forEach(l=>l.className=l.className.replace(/bg-\S+|text-\S+|ring-\S+|border-transparent|font-semibold/g,'').trim()); this.parentElement.classList.add('<?php echo "$bg border-transparent ring-2 $ring $txt"; ?>', 'font-semibold')">
                            <?php echo $val; ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- ===================== ABA: CUSTOS & CONTRATO ===================== -->
        <div id="tab-custos" class="tab-panel hidden">

            <!-- Valores -->
            <div class="bg-white border border-gray-200 rounded-lg p-6 mb-4">
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Valores & Pagamento
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Valor da Licença <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">R$</span>
                            <input type="text" name="valor_licenca" value="<?php echo htmlspecialchars($licenca['valor_licenca'] ?? ''); ?>"
                                class="w-full rounded-md border-gray-300 border shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm p-2.5 pl-12"
                                placeholder="0,00">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Moeda</label>
                        <select name="moeda" class="w-full rounded-md border-gray-300 border shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm p-2.5">
                            <?php
                            $moedas = ['BRL — Real','USD — Dólar','EUR — Euro','GBP — Libra'];
                            foreach ($moedas as $m):
                                $sel = ($licenca['moeda'] ?? 'BRL — Real') === $m ? 'selected' : '';
                            ?>
                                <option value="<?php echo $m; ?>" <?php echo $sel; ?>><?php echo $m; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Frequência de Pagamento</label>
                        <select name="frequencia_pagamento" class="w-full rounded-md border-gray-300 border shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm p-2.5">
                            <?php
                            $freqs = ['Mensal','Trimestral','Anual','Único'];
                            foreach ($freqs as $f):
                                $sel = ($licenca['frequencia_pagamento'] ?? '') === $f ? 'selected' : '';
                            ?>
                                <option value="<?php echo $f; ?>" <?php echo $sel; ?>><?php echo $f; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Centro de Custo</label>
                        <input type="text" name="centro_custo" value="<?php echo htmlspecialchars($licenca['centro_custo'] ?? ''); ?>"
                            class="w-full rounded-md border-gray-300 border shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm p-2.5"
                            placeholder="ex: CC-0042 / Tecnologia">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Forma de Pagamento</label>
                        <select name="forma_pagamento" class="w-full rounded-md border-gray-300 border shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm p-2.5">
                            <option value="">Selecione...</option>
                            <?php
                            $formas = ['Boleto bancário','Cartão corporativo','Transferência / PIX','Nota de empenho'];
                            foreach ($formas as $f):
                                $sel = ($licenca['forma_pagamento'] ?? '') === $f ? 'selected' : '';
                            ?>
                                <option value="<?php echo $f; ?>" <?php echo $sel; ?>><?php echo $f; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Contrato -->
            <div class="bg-white border border-gray-200 rounded-lg p-6 mb-4">
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    Contrato & Fornecedor
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nº do Contrato</label>
                        <input type="text" name="numero_contrato" value="<?php echo htmlspecialchars($licenca['numero_contrato'] ?? ''); ?>"
                            class="w-full rounded-md border-gray-300 border shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm p-2.5"
                            placeholder="ex: CTR-2024-00187">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Data de Assinatura</label>
                        <input type="date" name="data_assinatura_contrato" value="<?php echo $licenca['data_assinatura_contrato'] ?? ''; ?>"
                            class="w-full rounded-md border-gray-300 border shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm p-2.5">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Vigência do Contrato</label>
                        <input type="date" name="data_vigencia_contrato" value="<?php echo $licenca['data_vigencia_contrato'] ?? ''; ?>"
                            class="w-full rounded-md border-gray-300 border shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm p-2.5">
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">CNPJ / CPF do Fornecedor</label>
                        <input type="text" name="cnpj_fornecedor" id="cnpj_fornecedor" value="<?php echo htmlspecialchars($licenca['cnpj_fornecedor'] ?? ''); ?>"
                            class="w-full rounded-md border-gray-300 border shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm p-2.5"
                            placeholder="00.000.000/0001-00">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Contato Comercial</label>
                        <input type="text" name="contato_comercial" value="<?php echo htmlspecialchars($licenca['contato_comercial'] ?? ''); ?>"
                            class="w-full rounded-md border-gray-300 border shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm p-2.5"
                            placeholder="Nome e e-mail do representante">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Link do Contrato / Portal do Fornecedor</label>
                    <input type="url" name="link_contrato" value="<?php echo htmlspecialchars($licenca['link_contrato'] ?? ''); ?>"
                        class="w-full rounded-md border-gray-300 border shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm p-2.5"
                        placeholder="https://...">
                </div>
            </div>

            <!-- Tags -->
            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-4">Tags & Classificação</h3>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tags</label>
                    <div id="tagContainer" class="flex flex-wrap gap-2 p-2.5 border border-gray-300 rounded-md min-h-[42px] cursor-text" onclick="document.getElementById('tagInput').focus()">
                        <!-- Tags serão inseridas aqui pelo JS -->
                        <input id="tagInput" type="text" class="outline-none text-sm flex-1 min-w-[120px] bg-transparent" placeholder="Adicionar tag e pressionar Enter...">
                    </div>
                    <input type="hidden" name="tags" id="tagsHidden" value="<?php echo htmlspecialchars($licenca['tags'] ?? ''); ?>">
                    <p class="text-xs text-gray-400 mt-1">Pressione Enter para adicionar uma tag</p>
                </div>
            </div>
        </div>

        <!-- ===================== ABA: ALERTAS & CONFORMIDADE ===================== -->
        <div id="tab-alertas" class="tab-panel hidden">

            <!-- Alertas -->
            <div class="bg-white border border-gray-200 rounded-lg p-6 mb-4">
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    Alertas de Vencimento
                </h3>
                <?php
                $alertas = [
                    ['campo' => 'alerta_90_dias', 'label' => 'Alerta 90 dias antes',  'sub' => 'Notificação inicial de renovação',    'default' => 1],
                    ['campo' => 'alerta_30_dias', 'label' => 'Alerta 30 dias antes',  'sub' => 'Lembrete crítico de vencimento',       'default' => 1],
                    ['campo' => 'alerta_7_dias',  'label' => 'Alerta 7 dias antes',   'sub' => 'Aviso urgente de expiração',           'default' => 1],
                    ['campo' => 'alerta_no_dia',  'label' => 'Alerta no dia do vencimento', 'sub' => 'Notificação no dia de expiração', 'default' => 0],
                ];
                foreach ($alertas as $al):
                    $checked = ($licenca[$al['campo']] ?? $al['default']) ? 'checked' : '';
                ?>
                    <div class="flex items-center justify-between py-3 border-b border-gray-100 last:border-0">
                        <div>
                            <p class="text-sm font-medium text-gray-800"><?php echo $al['label']; ?></p>
                            <p class="text-xs text-gray-500"><?php echo $al['sub']; ?></p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="<?php echo $al['campo']; ?>" value="1" <?php echo $checked; ?> class="sr-only peer">
                            <div class="w-9 h-5 bg-gray-200 peer-focus:ring-2 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-indigo-600"></div>
                        </label>
                    </div>
                <?php endforeach; ?>
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">E-mails adicionais para notificação</label>
                    <input type="text" name="emails_notificacao" value="<?php echo htmlspecialchars($licenca['emails_notificacao'] ?? ''); ?>"
                        class="w-full rounded-md border-gray-300 border shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm p-2.5"
                        placeholder="e1@empresa.com, e2@empresa.com">
                </div>
            </div>

            <!-- Conformidade -->
            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    Conformidade & Auditoria
                </h3>
                <?php
                $conformidades = [
                    ['campo' => 'auditoria_ativa',         'label' => 'Auditoria de uso ativa',         'sub' => 'Registrar acessos e utilização da licença',              'default' => 1],
                    ['campo' => 'requer_aprovacao',        'label' => 'Requer aprovação para renovação', 'sub' => 'Solicitar aprovação do gestor antes de renovar',          'default' => 0],
                    ['campo' => 'licenca_regulatoria',     'label' => 'Licença regulatória / obrigatória','sub' => 'Licença exigida por lei ou órgão regulador',            'default' => 0],
                    ['campo' => 'inclui_sla',              'label' => 'Inclui SLA do fornecedor',        'sub' => 'Contrato possui acordo de nível de serviço',             'default' => 1],
                ];
                foreach ($conformidades as $cf):
                    $checked = ($licenca[$cf['campo']] ?? $cf['default']) ? 'checked' : '';
                ?>
                    <div class="flex items-center justify-between py-3 border-b border-gray-100 last:border-0">
                        <div>
                            <p class="text-sm font-medium text-gray-800"><?php echo $cf['label']; ?></p>
                            <p class="text-xs text-gray-500"><?php echo $cf['sub']; ?></p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="<?php echo $cf['campo']; ?>" value="1" <?php echo $checked; ?> class="sr-only peer">
                            <div class="w-9 h-5 bg-gray-200 peer-focus:ring-2 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-indigo-600"></div>
                        </label>
                    </div>
                <?php endforeach; ?>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mt-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Órgão Regulador / Fiscalizador</label>
                        <input type="text" name="orgao_regulador" value="<?php echo htmlspecialchars($licenca['orgao_regulador'] ?? ''); ?>"
                            class="w-full rounded-md border-gray-300 border shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm p-2.5"
                            placeholder="ex: ANVISA, ANATEL, OAB, CRM...">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Norma / Lei Aplicável</label>
                        <input type="text" name="norma_aplicavel" value="<?php echo htmlspecialchars($licenca['norma_aplicavel'] ?? ''); ?>"
                            class="w-full rounded-md border-gray-300 border shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm p-2.5"
                            placeholder="ex: LGPD, ISO 27001, RDC 216...">
                    </div>
                </div>
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notas de Conformidade</label>
                    <textarea name="notas_conformidade" rows="3"
                        class="w-full rounded-md border-gray-300 border shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm p-2.5"
                        placeholder="Requisitos legais, normas aplicáveis, restrições de uso..."><?php echo htmlspecialchars($licenca['notas_conformidade'] ?? ''); ?></textarea>
                </div>
            </div>
        </div>

        <!-- Ações -->
        <div class="flex items-center justify-between mt-6 pt-5 border-t border-gray-200">
            <a href="<?php echo BASE_URL; ?>/licencasOperacao"
               class="px-4 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 transition">
                Cancelar
            </a>
            <div class="flex gap-3">
                <button type="button" onclick="saveDraft()"
                    class="px-4 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 transition flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                    Salvar rascunho
                </button>
                <button type="submit"
                    class="px-5 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-md hover:bg-indigo-700 transition shadow-sm flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Salvar licença
                </button>
            </div>
        </div>

    </form>
</div>

<script>
// Navegação por abas
function switchTab(tabId) {
    document.querySelectorAll('.tab-panel').forEach(p => p.classList.add('hidden'));
    document.querySelectorAll('.tab-btn').forEach(b => {
        b.classList.remove('active-tab', 'border-indigo-500', 'text-indigo-600');
        b.classList.add('border-transparent', 'text-gray-500');
    });
    document.getElementById('tab-' + tabId).classList.remove('hidden');
    const activeBtn = [...document.querySelectorAll('.tab-btn')].find(b => b.getAttribute('onclick').includes("'" + tabId + "'"));
    if (activeBtn) {
        activeBtn.classList.add('active-tab', 'border-indigo-500', 'text-indigo-600');
        activeBtn.classList.remove('border-transparent', 'text-gray-500');
    }
}

// Sistema de tags
const tagContainer = document.getElementById('tagContainer');
const tagInput = document.getElementById('tagInput');
const tagsHidden = document.getElementById('tagsHidden');
let tags = tagsHidden.value ? tagsHidden.value.split(',').map(t => t.trim()).filter(Boolean) : [];

function renderTags() {
    tagContainer.querySelectorAll('.tag-chip').forEach(t => t.remove());
    tags.forEach(tag => {
        const chip = document.createElement('span');
        chip.className = 'tag-chip flex items-center gap-1 bg-indigo-100 text-indigo-700 text-xs font-medium px-2.5 py-1 rounded-full';
        chip.innerHTML = `${tag} <button type="button" onclick="removeTag('${tag}')" class="text-indigo-400 hover:text-indigo-700 font-bold leading-none">&times;</button>`;
        tagContainer.insertBefore(chip, tagInput);
    });
    tagsHidden.value = tags.join(', ');
}
function removeTag(tag) { tags = tags.filter(t => t !== tag); renderTags(); }
tagInput.addEventListener('keydown', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        const val = this.value.trim();
        if (val && !tags.includes(val)) { tags.push(val); renderTags(); }
        this.value = '';
    }
});
renderTags();

// Rascunho
function saveDraft() {
    const input = document.createElement('input');
    input.type = 'hidden'; input.name = 'draft'; input.value = '1';
    document.getElementById('formLicenca').appendChild(input);
    document.getElementById('formLicenca').submit();
}
</script>
