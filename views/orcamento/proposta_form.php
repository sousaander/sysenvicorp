<?php
$id = $proposta['id'] ?? null;
$projeto_id = $proposta['projeto_id'] ?? null;
$numero_proposta = $proposta['numero_proposta'] ?? '';
$titulo = $proposta['nome_proposta'] ?? '';
$descricao_geral = $proposta['descricao'] ?? '';
$objetivo = $proposta['objetivo'] ?? '';
$total_servicos = $proposta['total_servicos'] ?? 0;
$total_materiais = $proposta['total_materiais'] ?? 0;
$impostos_valor = $proposta['impostos_valor'] ?? 0;
$descontos_valor = $proposta['descontos_valor'] ?? 0;
$valor_total = $proposta['total_final'] ?? 0;
$condicoes = $proposta['condicoes'] ?? '';
$forma_pagamento = $proposta['forma_pagamento'] ?? '';
$prazo_execucao = $proposta['prazo_execucao'] ?? '';
$garantias = $proposta['garantias'] ?? '';

$servicos_json = json_encode($proposta['servicos'] ?? []);
$materiais_json = json_encode($proposta['materiais'] ?? []);
$status = $proposta['status'] ?? 'Rascunho';
$validade = $proposta['validade_proposta'] ?? '30';
$data_proposta = $proposta['data_proposta'] ?? date('Y-m-d');
$responsavel_interno_id = $proposta['responsavel_interno_id'] ?? null;

// Assinatura (Contratada)
$assinatura_tipo = $proposta['assinatura_tipo'] ?? 'imagem';
$assinatura_elaborador_responsavel = $proposta['assinatura_elaborador_responsavel'] ?? 0;
$assinatura_imagem_base64 = $proposta['assinatura_imagem'] ?? null;
$assinatura_certificado_nome = $proposta['assinatura_certificado_nome'] ?? '';
$assinatura_certificado_cpf = $proposta['assinatura_certificado_cpf'] ?? '';

// Assinatura do Elaborador (Responsável Técnico)
$elab_assinatura_tipo = $proposta['assinatura_elaborador_tipo'] ?? 'imagem';
$elab_assinatura_imagem = $proposta['assinatura_elaborador_imagem'] ?? null;
$elab_certificado_nome = $proposta['assinatura_elaborador_certificado_nome'] ?? '';
$elab_certificado_cpf = $proposta['assinatura_elaborador_certificado_cpf'] ?? '';
?>

<div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-200">
    <!-- Header da Modal/Página -->
    <div class="bg-gray-50 border-b border-gray-200 px-6 py-4 flex justify-between items-center">
        <div class="flex items-center gap-3">
            <div class="p-2 bg-sky-100 text-sky-600 rounded-lg">
                <i class="fas fa-file-invoice-dollar text-xl"></i>
            </div>
            <div>
                <h2 class="text-xl font-bold text-gray-800"><?php echo (!empty($proposta['id'])) ? 'Editar Proposta Comercial' : 'Nova Proposta Comercial'; ?></h2>
                <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold"><?php echo $numero_proposta ?: 'Rascunho'; ?></p>
            </div>
        </div>
        <button type="button" onclick="closePropostaModal()" class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 p-2 rounded-full transition">
            <i class="fas fa-times text-lg"></i>
        </button>
    </div>

    <form id="proposta-form" method="post" action="<?php echo BASE_URL; ?>/orcamento/salvar">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token ?? ''); ?>" />
        <?php if ($id): ?><input type="hidden" name="id" value="<?php echo $id; ?>" /><?php endif; ?>
        <?php if ($numero_proposta): ?><input type="hidden" name="numero_proposta" value="<?php echo $numero_proposta; ?>" /><?php endif; ?>

        <div class="p-6 space-y-8">
            
            <!-- Seção 1: Origem da Proposta -->
            <div class="bg-gray-50 p-5 rounded-xl border border-gray-200">
                <h3 class="text-sm font-bold text-sky-700 uppercase tracking-wider mb-4 flex items-center gap-2">
                    <i class="fas fa-sitemap"></i> 1. Origem e Vínculo
                </h3>
                <div class="flex flex-wrap gap-6 mb-6">
                    <label class="flex items-center gap-2 cursor-pointer group">
                        <input type="radio" name="creation_type" value="from_scratch" class="w-4 h-4 text-sky-600 border-gray-300 focus:ring-sky-500" <?php echo ($projeto_id ? '' : 'checked'); ?>>
                        <span class="text-sm font-medium text-gray-700 group-hover:text-sky-600 transition">Criar proposta do zero</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer group">
                        <input type="radio" name="creation_type" value="from_project" class="w-4 h-4 text-sky-600 border-gray-300 focus:ring-sky-500" <?php echo ($projeto_id ? 'checked' : ''); ?>>
                        <span class="text-sm font-medium text-gray-700 group-hover:text-sky-600 transition">Vincular a um projeto existente</span>
                    </label>
                </div>

                <div id="section_from_scratch" class="<?php echo ($projeto_id ? 'hidden' : ''); ?> grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Cliente</label>
                        <select name="cliente_id_scratch" class="w-full bg-white border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none transition">
                            <option value="">Selecione um cliente...</option>
                            <?php foreach ($clientes as $cliente): ?>
                                <option value="<?php echo $cliente['id']; ?>" <?php echo (($proposta['cliente_id'] ?? '') == $cliente['id'] ? 'selected' : ''); ?>><?php echo htmlspecialchars($cliente['nome']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Nome do Projeto/Serviço</label>
                        <input type="text" name="nome_projeto_scratch" value="<?php echo htmlspecialchars($proposta['nome_projeto_scratch'] ?? ''); ?>" class="w-full bg-white border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none transition" placeholder="Ex: Inventário Florestal Fazenda X">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">CNPJ/CPF do Cliente (Opcional)</label>
                        <input type="text" name="cliente_documento_scratch" value="<?php echo htmlspecialchars($proposta['cliente_documento'] ?? ''); ?>" class="w-full bg-white border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none transition cpf-cnpj" placeholder="00.000.000/0000-00">
                    </div>
                </div>

                <div id="section_from_project" class="<?php echo ($projeto_id ? '' : 'hidden'); ?> space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Projeto Vinculado</label>
                        <select id="projeto_id" name="projeto_id" class="w-full bg-white border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none transition">
                            <option value="">Selecione o projeto...</option>
                            <?php foreach ($projetos as $p): ?>
                                <option value="<?php echo $p['id']; ?>" <?php echo ($p['id'] == $projeto_id ? 'selected' : ''); ?>>[#<?php echo $p['id']; ?>] <?php echo htmlspecialchars($p['nome']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div id="project_details_container" class="hidden grid grid-cols-2 md:grid-cols-4 gap-4 p-4 bg-white rounded-lg border border-gray-200 shadow-sm text-xs">
                        <div><span class="text-gray-400 block mb-1 uppercase font-bold">Cliente</span><span id="detail_cliente" class="font-semibold text-gray-700"></span></div>
                        <div><span class="text-gray-400 block mb-1 uppercase font-bold">Responsável</span><span id="detail_responsavel" class="font-semibold text-gray-700"></span></div>
                        <div><span class="text-gray-400 block mb-1 uppercase font-bold">Área</span><span id="detail_tipo_servico" class="font-semibold text-gray-700"></span></div>
                        <div><span class="text-gray-400 block mb-1 uppercase font-bold">ID</span><span id="detail_id" class="font-semibold text-gray-700"></span></div>
                    </div>
                </div>
            </div>

            <!-- Seção 2: Informações Principais -->
            <div>
                <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wider mb-4 flex items-center gap-2">
                    <i class="fas fa-info-circle text-sky-500"></i> 2. Detalhes da Proposta
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Título da Proposta <span class="text-red-500">*</span></label>
                        <input type="text" name="titulo" value="<?php echo htmlspecialchars($titulo); ?>" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-sky-500 outline-none transition" placeholder="Ex: Proposta Técnica e Comercial - Inventário Florestal">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Status</label>
                        <select name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-sky-500 outline-none transition bg-white">
                            <option value="Rascunho" <?php echo ($status == 'Rascunho' ? 'selected' : ''); ?>>Rascunho</option>
                            <option value="Enviada" <?php echo ($status == 'Enviada' ? 'selected' : ''); ?>>Enviada ao Cliente</option>
                            <option value="Aprovada" <?php echo ($status == 'Aprovada' ? 'selected' : ''); ?>>Aprovada</option>
                            <option value="Rejeitada" <?php echo ($status == 'Rejeitada' ? 'selected' : ''); ?>>Rejeitada</option>
                        </select>
                    </div>
                    <div class="md:col-span-3">
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Descrição Geral</label>
                        <textarea name="descricao_geral" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-sky-500 outline-none transition" placeholder="Breve resumo do escopo da proposta..."><?php echo htmlspecialchars($descricao_geral); ?></textarea>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 md:col-span-3">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Data</label>
                            <input type="date" id="data_proposta" name="data_proposta" value="<?php echo $data_proposta; ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-sky-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Validade (Dias)</label>
                            <input type="number" id="validade_proposta" name="validade_proposta" value="<?php echo $validade; ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-sky-500 outline-none">
                            <p class="text-[10px] text-gray-400 mt-1">Expira em: <span id="validade_preview" class="font-bold text-sky-600">--/--/----</span></p>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Responsável Interno</label>
                            <select name="responsavel_interno_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-sky-500 outline-none bg-white">
                                <option value="">Selecione...</option>
                                <?php foreach ($usuarios as $u): ?>
                                    <option value="<?php echo $u['id']; ?>" <?php echo ($responsavel_interno_id == $u['id'] ? 'selected' : ''); ?>><?php echo htmlspecialchars($u['nome']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Seção 3: Itens e Custos -->
            <div class="space-y-6">
                <!-- Serviços -->
                <div class="border border-gray-200 rounded-xl overflow-hidden">
                    <div class="bg-gray-50 px-4 py-3 border-b border-gray-200 flex justify-between items-center">
                        <h4 class="text-xs font-bold text-gray-600 uppercase tracking-widest flex items-center gap-2">
                            <i class="fas fa-tools text-sky-500"></i> Serviços
                        </h4>
                        <button type="button" id="add-service-btn" class="text-xs bg-sky-600 hover:bg-sky-700 text-white px-3 py-1.5 rounded-lg font-bold transition flex items-center gap-1">
                            <i class="fas fa-plus"></i> Adicionar
                        </button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-100 text-gray-500 font-bold text-left uppercase text-[10px]">
                                <tr>
                                    <th class="px-4 py-2 w-1/3">Serviço</th>
                                    <th class="px-4 py-2">Descrição</th>
                                    <th class="px-4 py-2 w-20 text-center">Qtd</th>
                                    <th class="px-4 py-2 w-24">Valor Unit.</th>
                                    <th class="px-4 py-2 w-24">Subtotal</th>
                                    <th class="px-4 py-2 w-10"></th>
                                </tr>
                            </thead>
                            <tbody id="services-container" class="divide-y divide-gray-100">
                                <!-- Inserido via JS -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Resumo Financeiro e Taxas -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-4">
                        <h4 class="text-xs font-bold text-gray-600 uppercase tracking-widest flex items-center gap-2">
                            <i class="fas fa-percentage text-sky-500"></i> Taxas e Ajustes
                        </h4>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Impostos (R$)</label>
                                <input type="text" id="impostos" name="impostos_valor" value="<?php echo number_format($impostos_valor, 2, ',', '.'); ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm money focus:ring-sky-500 outline-none">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Descontos (R$)</label>
                                <input type="text" id="descontos" name="descontos_valor" value="<?php echo number_format($descontos_valor, 2, ',', '.'); ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm money focus:ring-sky-500 outline-none">
                            </div>
                        </div>
                    </div>

                    <div class="bg-sky-50 p-6 rounded-2xl border border-sky-100 space-y-3">
                        <div class="flex justify-between text-sm text-sky-700">
                            <span>Total Serviços</span>
                            <span id="total-servicos-display" class="font-bold">R$ 0,00</span>
                            <input type="hidden" name="total_servicos" id="total-servicos-hidden">
                        </div>
                        <input type="hidden" name="total_materiais" id="total-materiais-hidden">
                        <div class="flex justify-between text-sm text-sky-700">
                            <span>Impostos (+)</span>
                            <span id="total-impostos-display" class="font-bold">R$ 0,00</span>
                        </div>
                        <div class="flex justify-between text-sm text-red-600 border-b border-sky-200 pb-3">
                            <span>Descontos (-)</span>
                            <span id="total-descontos-display" class="font-bold">R$ 0,00</span>
                        </div>
                        <div class="flex justify-between items-center pt-2">
                            <span class="text-sky-900 font-bold uppercase tracking-tighter">Valor Final</span>
                            <div class="text-right">
                                <span id="valor_total_display" class="text-2xl font-black text-sky-700">R$ 0,00</span>
                                <input type="hidden" name="valor_total" id="valor_total_hidden">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Seção 4: Termos e Condições -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t border-gray-100">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Forma de Pagamento</label>
                    <textarea name="forma_pagamento" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-sky-500 outline-none transition" placeholder="Ex: 50% entrada e 50% na entrega dos produtos técnicos."><?php echo htmlspecialchars($forma_pagamento); ?></textarea>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Prazo de Execução</label>
                    <textarea name="prazo_execucao" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-sky-500 outline-none transition" placeholder="Ex: 30 dias úteis após assinatura do contrato."><?php echo htmlspecialchars($prazo_execucao); ?></textarea>
                </div>
            </div>

            <!-- Seção 5: Assinaturas do PDF -->
            <div class="bg-gray-50 p-5 rounded-xl border border-gray-200">
                <h3 class="text-sm font-bold text-sky-700 uppercase tracking-wider mb-4 flex items-center gap-2">
                    <i class="fas fa-file-signature"></i> 5. Assinaturas do PDF
                </h3>

                <!-- Contratada -->
                <div class="mb-4 p-4 bg-white rounded-lg border border-gray-200">
                    <h4 class="text-xs font-bold text-gray-600 uppercase tracking-wider mb-3 flex items-center gap-2">
                        <i class="fas fa-building text-sky-500"></i> Assinatura da Contratada
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Tipo de Assinatura</label>
                            <select name="assinatura_tipo" id="assinatura_tipo" class="w-full bg-white border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-sky-500 outline-none transition">
                                <option value="imagem" <?php echo $assinatura_tipo === 'imagem' ? 'selected' : ''; ?>>Assinatura por Imagem</option>
                                <option value="certificado" <?php echo $assinatura_tipo === 'certificado' ? 'selected' : ''; ?>>Assinatura via Certificado Digital</option>
                                <option value="nenhum" <?php echo $assinatura_tipo === 'nenhum' ? 'selected' : ''; ?>>Nenhum (linha em branco)</option>
                            </select>
                        </div>
                        <div></div>
                    </div>

                    <div id="ctd_imagem_container" class="mt-3 <?php echo $assinatura_tipo !== 'imagem' ? 'hidden' : ''; ?>">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Upload de Imagem</label>
                                <input type="file" class="sig-img-input" data-preview="ctd_imagem_preview" data-hidden="ctd_imagem_hidden" data-remover="ctd_btn_limpar" data-remover-field="ctd_imagem_remover" accept="image/png,image/jpeg" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-bold file:bg-sky-50 file:text-sky-700 hover:file:bg-sky-100">
                                <button type="button" class="btn-limpar-sig text-xs text-red-500 hover:text-red-700 font-bold mt-1 <?php echo !$assinatura_imagem_base64 ? 'hidden' : ''; ?>" data-hidden="ctd_imagem_hidden" data-preview="ctd_imagem_preview" data-remover-field="ctd_imagem_remover" data-input=".sig-img-input[data-hidden='ctd_imagem_hidden']"><i class="fas fa-trash-alt"></i> Remover</button>
                                <p class="text-[10px] text-gray-400 mt-1">PNG ou JPG, máx. 500KB.</p>
                                <input type="hidden" name="assinatura_imagem" id="ctd_imagem_hidden" value="<?php echo htmlspecialchars($assinatura_imagem_base64 ?? ''); ?>">
                                <input type="hidden" name="assinatura_imagem_remover" id="ctd_imagem_remover" value="0">
                            </div>
                            <div class="flex items-center justify-center">
                                <div class="text-center">
                                    <p class="text-[10px] font-bold text-gray-400 uppercase mb-2">Preview</p>
                                    <div id="ctd_imagem_preview" class="border-2 border-dashed border-gray-300 rounded-lg p-4 min-h-[60px] flex items-center justify-center bg-gray-50">
                                        <?php if ($assinatura_imagem_base64): ?>
                                            <img src="data:image/png;base64,<?php echo $assinatura_imagem_base64; ?>" style="max-height:50px; max-width:200px; object-fit:contain;">
                                        <?php else: ?>
                                            <span class="text-xs text-gray-400">Nenhuma</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="ctd_certificado_container" class="mt-3 <?php echo $assinatura_tipo !== 'certificado' ? 'hidden' : ''; ?>">
                        <div class="p-4 bg-amber-50 rounded-lg border border-amber-200 cert-container">
                            <div class="flex items-center justify-between mb-2">
                                <label class="block text-xs font-bold text-gray-500 uppercase">Certificado Digital A1 (ICP-Brasil)</label>
                                <span id="ctd_cert_verified" class="hidden text-xs text-emerald-600 font-bold bg-emerald-50 px-2 py-1 rounded-full border border-emerald-200">
                                    <i class="fas fa-lock"></i> Senha verificada
                                </span>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Arquivo (.pfx / .p12)</label>
                                    <input type="file" class="cert-file-input block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-bold file:bg-sky-50 file:text-sky-700 hover:file:bg-sky-100" accept=".pfx,.p12" data-target="ctd">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Senha do Certificado</label>
                                    <input type="password" class="cert-password-input w-full border border-gray-300 rounded-lg px-3 py-2 text-sm outline-none" data-target="ctd" placeholder="Senha do .pfx">
                                    <a href="#" id="ctd_cert_change" class="hidden text-[10px] text-sky-600 hover:text-sky-800 font-bold mt-1 inline-block"><i class="fas fa-sync-alt"></i> Alterar certificado</a>
                                </div>
                            </div>
                            <div id="ctd_cert_status" class="mt-3"></div>
                            <div id="ctd_cert_info" class="mt-3 hidden">
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-xs bg-white p-3 rounded border border-gray-200">
                                    <div><span class="text-gray-400 block">Titular</span><span id="ctd_cert_nome" class="font-semibold text-gray-700">-</span></div>
                                    <div><span class="text-gray-400 block">CPF/CNPJ</span><span id="ctd_cert_doc" class="font-semibold text-gray-700">-</span></div>
                                    <div><span class="text-gray-400 block">Validade</span><span id="ctd_cert_validade" class="font-semibold text-gray-700">-</span></div>
                                    <div><span class="text-gray-400 block">ICP-Brasil</span><span id="ctd_cert_icp" class="font-semibold text-gray-700">-</span></div>
                                </div>
                            </div>
                            <input type="hidden" name="assinatura_certificado_nome" id="ctd_cert_nome_hidden" value="<?php echo htmlspecialchars($assinatura_certificado_nome); ?>">
                            <input type="hidden" name="assinatura_certificado_cpf" id="ctd_cert_cpf_hidden" value="<?php echo htmlspecialchars($assinatura_certificado_cpf); ?>">
                            <input type="hidden" name="assinatura_certificado_path" id="ctd_cert_path_hidden" value="">
                            <input type="hidden" name="assinatura_certificado_senha" id="ctd_cert_senha_hidden" value="">
                            <input type="hidden" name="assinatura_certificado_validade" id="ctd_cert_validade_hidden" value="">
                        </div>
                    </div>
                </div>

                <!-- Elaborador como Responsável Técnico -->
                <div class="p-4 bg-white rounded-lg border border-gray-200">
                    <label class="flex items-center gap-3 cursor-pointer group mb-3">
                        <input type="checkbox" name="assinatura_elaborador_responsavel" value="1" id="elab_checkbox" <?php echo $assinatura_elaborador_responsavel ? 'checked' : ''; ?> class="w-4 h-4 text-sky-600 border-gray-300 rounded focus:ring-sky-500">
                        <span class="text-sm font-medium text-gray-700 group-hover:text-sky-600 transition">
                            <i class="fas fa-user-check text-sky-500 mr-1"></i>
                            Elaborador também assina como Responsável Técnico
                        </span>
                    </label>

                    <div id="elab_signature_fields" class="<?php echo !$assinatura_elaborador_responsavel ? 'hidden' : ''; ?> space-y-3">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Tipo de Assinatura do Elaborador</label>
                                <select name="assinatura_elaborador_tipo" id="elab_assinatura_tipo" class="w-full bg-white border border-gray-300 rounded-lg px-3 py-2 text-sm outline-none">
                                    <option value="imagem" <?php echo $elab_assinatura_tipo === 'imagem' ? 'selected' : ''; ?>>Assinatura por Imagem</option>
                                    <option value="certificado" <?php echo $elab_assinatura_tipo === 'certificado' ? 'selected' : ''; ?>>Assinatura via Certificado Digital</option>
                                    <option value="nenhum" <?php echo $elab_assinatura_tipo === 'nenhum' ? 'selected' : ''; ?>>Nenhum (linha em branco)</option>
                                </select>
                            </div>
                            <div></div>
                        </div>

                        <div id="elab_imagem_container" class="<?php echo $elab_assinatura_tipo !== 'imagem' ? 'hidden' : ''; ?>">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Imagem de Assinatura</label>
                                    <input type="file" class="sig-img-input" data-preview="elab_imagem_preview" data-hidden="elab_imagem_hidden" data-remover="elab_btn_limpar" data-remover-field="elab_imagem_remover" accept="image/png,image/jpeg" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-bold file:bg-sky-50 file:text-sky-700 hover:file:bg-sky-100">
                                    <button type="button" class="btn-limpar-sig text-xs text-red-500 hover:text-red-700 font-bold mt-1 <?php echo !$elab_assinatura_imagem ? 'hidden' : ''; ?>" data-hidden="elab_imagem_hidden" data-preview="elab_imagem_preview" data-remover-field="elab_imagem_remover" data-input=".sig-img-input[data-hidden='elab_imagem_hidden']"><i class="fas fa-trash-alt"></i> Remover</button>
                                    <p class="text-[10px] text-gray-400 mt-1">PNG ou JPG, máx. 500KB.</p>
                                    <input type="hidden" name="assinatura_elaborador_imagem" id="elab_imagem_hidden" value="<?php echo htmlspecialchars($elab_assinatura_imagem ?? ''); ?>">
                                    <input type="hidden" name="assinatura_elaborador_imagem_remover" id="elab_imagem_remover" value="0">
                                </div>
                                <div class="flex items-center justify-center">
                                    <div class="text-center">
                                        <p class="text-[10px] font-bold text-gray-400 uppercase mb-2">Preview</p>
                                        <div id="elab_imagem_preview" class="border-2 border-dashed border-gray-300 rounded-lg p-4 min-h-[60px] flex items-center justify-center bg-gray-50">
                                            <?php if ($elab_assinatura_imagem): ?>
                                                <img src="data:image/png;base64,<?php echo $elab_assinatura_imagem; ?>" style="max-height:50px; max-width:200px; object-fit:contain;">
                                            <?php else: ?>
                                                <span class="text-xs text-gray-400">Nenhuma</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="elab_certificado_container" class="<?php echo $elab_assinatura_tipo !== 'certificado' ? 'hidden' : ''; ?>">
                            <div class="p-4 bg-amber-50 rounded-lg border border-amber-200 cert-container">
                                <div class="flex items-center justify-between mb-2">
                                    <label class="block text-xs font-bold text-gray-500 uppercase">Certificado Digital A1 do Elaborador</label>
                                    <span id="elab_cert_verified" class="hidden text-xs text-emerald-600 font-bold bg-emerald-50 px-2 py-1 rounded-full border border-emerald-200">
                                        <i class="fas fa-lock"></i> Senha verificada
                                    </span>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Arquivo (.pfx / .p12)</label>
                                        <input type="file" class="cert-file-input block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-bold file:bg-sky-50 file:text-sky-700 hover:file:bg-sky-100" accept=".pfx,.p12" data-target="elab">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Senha do Certificado</label>
                                        <input type="password" class="cert-password-input w-full border border-gray-300 rounded-lg px-3 py-2 text-sm outline-none" data-target="elab" placeholder="Senha do .pfx">
                                        <a href="#" id="elab_cert_change" class="hidden text-[10px] text-sky-600 hover:text-sky-800 font-bold mt-1 inline-block"><i class="fas fa-sync-alt"></i> Alterar certificado</a>
                                    </div>
                                </div>
                                <div id="elab_cert_status" class="mt-3"></div>
                                <div id="elab_cert_info" class="mt-3 hidden">
                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-xs bg-white p-3 rounded border border-gray-200">
                                        <div><span class="text-gray-400 block">Titular</span><span id="elab_cert_nome" class="font-semibold text-gray-700">-</span></div>
                                        <div><span class="text-gray-400 block">CPF/CNPJ</span><span id="elab_cert_doc" class="font-semibold text-gray-700">-</span></div>
                                        <div><span class="text-gray-400 block">Validade</span><span id="elab_cert_validade" class="font-semibold text-gray-700">-</span></div>
                                        <div><span class="text-gray-400 block">ICP-Brasil</span><span id="elab_cert_icp" class="font-semibold text-gray-700">-</span></div>
                                    </div>
                                </div>
                                <input type="hidden" name="assinatura_elaborador_certificado_nome" id="elab_cert_nome_hidden" value="<?php echo htmlspecialchars($elab_certificado_nome); ?>">
                                <input type="hidden" name="assinatura_elaborador_certificado_cpf" id="elab_cert_cpf_hidden" value="<?php echo htmlspecialchars($elab_certificado_cpf); ?>">
                                <input type="hidden" name="assinatura_elaborador_certificado_path" id="elab_cert_path_hidden" value="">
                                <input type="hidden" name="assinatura_elaborador_certificado_senha" id="elab_cert_senha_hidden" value="">
                                <input type="hidden" name="assinatura_elaborador_certificado_validade" id="elab_cert_validade_hidden" value="">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer de Ações -->
        <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 flex justify-between items-center">
            <button type="button" onclick="closePropostaModal()" class="px-4 py-2 text-sm font-bold text-gray-500 hover:text-gray-700 transition">
                Descartar Alterações
            </button>
            <div class="flex gap-3">
                <button type="submit" class="bg-sky-600 hover:bg-sky-700 text-white px-6 py-2 rounded-lg font-bold shadow-md shadow-sky-200 transition flex items-center gap-2">
                    <i class="fas fa-save"></i> Salvar Proposta
                </button>
            </div>
        </div>
        <input type="hidden" name="motivo_alteracao" value="Alteração via formulário visual">
    </form>
</div>

<script>
    const BASE_URL = '<?php echo BASE_URL; ?>';
</script>
<script src="<?php echo BASE_URL; ?>/assets/js/orcamento-form.js"></script>
<script>
    const initialExtraCosts = <?php echo json_encode($proposta['custos_extras'] ?? []); ?>;
    const initialImpostosValor = <?php echo json_encode(number_format($impostos_valor, 2, ',', '.')); ?>;
    const initialDescontosValor = <?php echo json_encode(number_format($descontos_valor, 2, ',', '.')); ?>;
    const initialValorTotal = <?php echo json_encode(number_format($valor_total, 2, ',', '.')); ?>;
</script>