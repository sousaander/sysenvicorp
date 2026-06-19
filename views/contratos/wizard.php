<?php
/**
 * Wizard de Contratos — Design Unificado
 */

// Helper para preencher value nos inputs
if (!function_exists('val')) {
    function val($data, $key, $default = '') {
        return htmlspecialchars($data[$key] ?? $default);
    }
}

// Helper para selected em <select>
if (!function_exists('sel')) {
    function sel($data, $key, $value) {
        return (($data[$key] ?? '') === $value) ? 'selected' : '';
    }
}

// Helper para checked em <input type="checkbox">
if (!function_exists('chk')) {
    function chk($data, $key, $default = false) {
        return (isset($data[$key]) ? (bool)$data[$key] : $default) ? 'checked' : '';
    }
}
$isEdit = (isset($isEdit) && $isEdit === true);
$contratoData = $contrato ?? null;
?>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;500;600&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">

<style>
/* ================================================================
   DESIGN SYSTEM UNIFICADO
================================================================ */
:root {
    --c-bg:          #F7F8FA;
    --c-surface:     #FFFFFF;
    --c-border:      #E2E6ED;
    --c-border-md:   #C8D0DC;
    --c-text:        #1A2233;
    --c-text-2:      #4A5568;
    --c-text-3:      #8896A9;
    --c-blue:        #1E4D8C;
    --c-blue-light:  #EBF2FB;
    --c-blue-mid:    #3A6DB5;
    --c-blue-hover:  #163D70;
    --c-gold:        #A67C00;
    --c-gold-light:  #FFF8E1;
    --c-gold-border: #E8C84A;
    --c-green:       #1A6B45;
    --c-green-light: #E8F5EE;
    --c-green-border:#6BBF8A;
    --radius-lg:     12px;
    --radius-xl:     16px;
    --shadow-sm:     0 1px 3px rgba(0,0,0,.07);
    --font-display:  'Lora', Georgia, serif;
    --font-body:     'DM Sans', system-ui, sans-serif;
}

/* Ajustes para Modo Escuro (Dark Mode) */
.dark-theme .wizard-container {
    --c-bg:          var(--db-bg, #0d1117);
    --c-surface:     var(--db-surface, #161b22);
    --c-border:      var(--db-border, #30363d);
    --c-border-md:   #475569;
    --c-text:        var(--db-text, #e6edf3);
    --c-text-2:      var(--db-text2, #8b949e);
    --c-text-3:      var(--db-text3, #6e7681);
    --c-blue-light:  #1E293B;
    --c-green-light: rgba(22, 163, 74, 0.15);
    --c-gold-light:  rgba(234, 179, 8, 0.15);
    --c-green-border: #2D4A3A;
}

/* Overrides para classes utilitárias do Tailwind no Wizard */
.dark-theme .bg-white { background-color: var(--c-surface) !important; color: var(--c-text); }
.dark-theme .bg-gray-50 { background-color: var(--c-bg) !important; }
.dark-theme .border-gray-200, .dark-theme .border-gray-300 { border-color: var(--c-border) !important; }
.dark-theme .text-gray-800, 
.dark-theme .text-gray-700, 
.dark-theme .text-gray-600 { color: var(--c-text) !important; }
.dark-theme .text-gray-400, 
.dark-theme .text-gray-500 { color: var(--c-text-3) !important; }

.dark-theme input:not(.btn-next), .dark-theme select, .dark-theme textarea {
    background-color: var(--c-bg) !important;
    color: var(--c-text) !important;
    border-color: var(--c-border) !important;
}

/* Mantém a "folha de papel" clara na prévia para simular o documento real */
.dark-theme #document-mockup { background-color: #FFFFFF !important; color: #1A2233 !important; }
.dark-theme #document-mockup * { color: #1A2233 !important; }
.dark-theme #preview-container { background-color: #111827 !important; border-color: #374151 !important; }

.wizard-container { font-family: var(--font-body); color: var(--c-text); }

/* ---- Cabeçalho Profissional ---- */
.ctr-header {
    background: var(--c-surface);
    border: 1px solid var(--c-border);
    border-radius: var(--radius-xl);
    padding: 24px 28px;
    margin-bottom: 25px;
    display: flex; align-items: center; justify-content: space-between;
    box-shadow: var(--shadow-sm);
}
.ctr-header-icon {
    width: 46px; height: 46px; background: var(--c-blue);
    border-radius: 8px; display: flex; align-items: center; justify-content: center;
    color: #fff; font-size: 18px; margin-right: 15px;
}
.ctr-title { font-family: var(--font-display); font-size: 22px; font-weight: 600; color: var(--c-text); }
.ctr-subtitle { font-size: 13px; color: var(--c-text-3); margin-top: 4px; }

/* ---- Stepper Unificado (CTR-Progress) ---- */
.ctr-progress {
    display: flex; margin-bottom: 25px;
    background: var(--c-surface); border: 1px solid var(--c-border);
    border-radius: var(--radius-lg); overflow: hidden; box-shadow: var(--shadow-sm);
}
.ctr-step {
    flex: 1; padding: 14px; text-align: center; font-size: 12px; font-weight: 600;
    color: var(--c-text-3); border-right: 1px solid var(--c-border);
    display: flex; align-items: center; justify-content: center; gap: 8px;
}
.ctr-step.active { background: var(--c-blue); color: #fff; }
.ctr-step.done { background: var(--c-green-light); color: var(--c-green); }
.ctr-step:last-child { border-right: none; }

/* ---- Seções e Inputs ---- */
.wizard-card {
    background: var(--c-surface); border: 1px solid var(--c-border);
    border-radius: var(--radius-xl); box-shadow: var(--shadow-sm);
}

/* Animação de transição das etapas */
.wizard-step { animation: fadeIn 0.4s ease-out forwards; }
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.ctr-label { font-size: 12px; font-weight: 600; color: var(--c-text-2); margin-bottom: 5px; display: block; }
.ctr-input, .ctr-select, .ctr-textarea {
    width: 100%; font-family: var(--font-body); font-size: 14px;
    padding: 10px 12px; background: var(--c-bg); border: 1px solid var(--c-border);
    border-radius: 8px; outline: none; transition: all .15s;
}
.ctr-input:focus, .ctr-select:focus { border-color: var(--c-blue-mid); background: #fff; box-shadow: 0 0 0 3px rgba(30,77,140,.1); }

.group-title {
    font-family: var(--font-display); font-size: 15px; font-weight: 600;
    color: var(--c-blue); margin-bottom: 15px; display: flex; align-items: center; gap: 8px;
    border-bottom: 1px solid var(--c-border); padding-bottom: 8px;
}

/* Botões */
.btn-next { background: var(--c-blue); color: #fff; font-weight: 600; padding: 10px 24px; border-radius: 8px; transition: background .2s; }
.btn-next:hover { background: var(--c-blue-hover); }
.btn-prev { color: var(--c-text-3); font-weight: 600; padding: 10px 20px; }
</style>

<div class="wizard-container">

<!-- Cabeçalho -->
<div class="ctr-header">
    <div class="flex items-center">
        <div class="ctr-header-icon"><i class="fas fa-magic"></i></div>
        <div>
            <div class="ctr-title"><?php echo $pageTitle; ?></div>
            <div class="ctr-subtitle">Fluxo assistido para criação e redação técnica de instrumentos jurídicos</div>
        </div>
    </div>
    <?php if ($isEdit): ?>
        <div class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider">
            Modo Edição #<?php echo $contrato['id']; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Stepper Unificado -->
<div class="ctr-progress">
    <div class="ctr-step active" data-step="1">
        <span class="w-6 h-6 rounded-full border-2 border-current flex items-center justify-center text-[10px] mr-2">1</span>
        Dados Estruturais
    </div>
    <div class="ctr-step" data-step="2">
        <span class="w-6 h-6 rounded-full border-2 border-current flex items-center justify-center text-[10px] mr-2">2</span>
        Redação Técnica
    </div>
    <div class="ctr-step" data-step="3">
        <span class="w-6 h-6 rounded-full border-2 border-current flex items-center justify-center text-[10px] mr-2">3</span>
        Revisão Final
    </div>
</div>

<div class="wizard-card overflow-hidden">
    <form id="contract-wizard-form" action="<?php echo BASE_URL; ?>/contratos/salvar" method="POST" enctype="multipart/form-data">
        <?php if ($isEdit) : ?>
            <input type="hidden" name="id" value="<?php echo $contrato['id']; ?>">
        <?php endif; ?>

        <!-- ETAPA 1: Formulário de Dados de Contrato -->
        <div class="p-7 wizard-step" id="step-1">
            <div class="space-y-10">
                <!-- 1.1 Identificação -->
                <div>
                    <h4 class="group-title"><i class="fas fa-tag"></i> Identificação do Contrato</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div class="md:col-span-2">
                            <label class="ctr-label">Título do Contrato *</label>
                            <input type="text" name="titulo" value="<?php echo $isEdit ? htmlspecialchars($contrato['titulo'] ?? '') : ''; ?>" required class="ctr-input" placeholder="Ex: Contrato de Prestação de Serviços de TI">
                        </div>
                        <div>
                            <label class="ctr-label">Tipo de Contrato *</label>
                            <select name="tipo" id="tipo_wizard" required class="ctr-select">
                                <option value="">Selecione...</option>
                                <option value="Prestação de Serviço" <?php echo ($isEdit && ($contrato['tipo'] ?? '') === 'Prestação de Serviço') ? 'selected' : ''; ?>>Prestação de Serviço</option>
                                <option value="Compra" <?php echo ($isEdit && ($contrato['tipo'] ?? '') === 'Compra') ? 'selected' : ''; ?>>Compra / Fornecimento</option>
                                <option value="Parceria" <?php echo ($isEdit && ($contrato['tipo'] ?? '') === 'Parceria') ? 'selected' : ''; ?>>Parceria</option>
                                <option value="Locação" <?php echo ($isEdit && ($contrato['tipo'] ?? '') === 'Locação') ? 'selected' : ''; ?>>Locação</option>
                                <option value="Outro" <?php echo ($isEdit && ($contrato['tipo'] ?? '') === 'Outro') ? 'selected' : ''; ?>>Outro</option>
                            </select>
                        </div>
                        <div>
                            <label class="ctr-label">Status</label>
                            <select name="status" required class="ctr-select">
                                <option value="Rascunho" <?php echo ($isEdit && ($contrato['status'] ?? '') === 'Rascunho') ? 'selected' : ''; ?>>Rascunho</option>
                                <option value="Em Vigência" <?php echo ($isEdit && ($contrato['status'] ?? '') === 'Em Vigência') ? 'selected' : ''; ?>>Em Vigência</option>
                                <option value="Pendência Assinatura" <?php echo ($isEdit && ($contrato['status'] ?? '') === 'Pendência Assinatura') ? 'selected' : ''; ?>>Pendência Assinatura</option>
                            </select>
                        </div>
                    </div>
                </div>
...

                <!-- 1.2 Contratante -->
                <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
                    <h4 class="text-md font-bold text-gray-800 mb-4 flex items-center gap-2">
                        <i class="fas fa-user-tie text-indigo-500"></i> Contratante
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nome / Razão Social *</label>
                            <input type="text" name="contratante_nome" value="<?php echo $isEdit ? htmlspecialchars($contrato['contratante_nome'] ?? '') : ''; ?>" required class="w-full border-gray-300 rounded-lg shadow-sm p-2" placeholder="Nome completo ou razão social">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">CPF / CNPJ</label>
                            <div class="flex gap-2">
                                <input type="text" name="contratante_documento" id="contratante_documento" value="<?php echo $isEdit ? htmlspecialchars($contrato['contratante_documento'] ?? '') : ''; ?>" class="w-full border-gray-300 rounded-lg shadow-sm p-2" placeholder="000.000.000-00">
                                <button type="button" onclick="executarBuscaCnpj('contratante')" class="bg-indigo-600 text-white px-4 rounded-lg hover:bg-indigo-700 transition-colors shadow-sm" title="Buscar dados pelo CNPJ">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Endereço Completo</label>
                            <input type="text" name="contratante_endereco" value="<?php echo $isEdit ? htmlspecialchars($contrato['contratante_endereco'] ?? '') : ''; ?>" class="w-full border-gray-300 rounded-lg shadow-sm p-2" placeholder="Rua, número, cidade - UF">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">E-mail</label>
                            <input type="email" name="contratante_email" value="<?php echo $isEdit ? htmlspecialchars($contrato['contratante_email'] ?? '') : ''; ?>" class="w-full border-gray-300 rounded-lg shadow-sm p-2" placeholder="email@exemplo.com">
                        </div>
                    </div>
                </div>

                <!-- 1.3 Contratado -->
                <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
                    <h4 class="text-md font-bold text-gray-800 mb-4 flex items-center gap-2">
                        <i class="fas fa-user-cog text-indigo-500"></i> Contratado
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nome / Razão Social *</label>
                            <input type="text" name="contratado_nome" value="<?php echo $isEdit ? htmlspecialchars($contrato['contratado_nome'] ?? '') : ''; ?>" required class="w-full border-gray-300 rounded-lg shadow-sm p-2" placeholder="Nome completo ou razão social">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">CPF / CNPJ</label>
                            <div class="flex gap-2">
                                <input type="text" name="contratado_documento" id="contratado_documento" value="<?php echo $isEdit ? htmlspecialchars($contrato['contratado_documento'] ?? '') : ''; ?>" class="w-full border-gray-300 rounded-lg shadow-sm p-2" placeholder="000.000.000-00">
                                <button type="button" onclick="executarBuscaCnpj('contratado')" class="bg-indigo-600 text-white px-4 rounded-lg hover:bg-indigo-700 transition-colors shadow-sm" title="Buscar dados pelo CNPJ">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Endereço Completo</label>
                            <input type="text" name="contratado_endereco" value="<?php echo $isEdit ? htmlspecialchars($contrato['contratado_endereco'] ?? '') : ''; ?>" class="w-full border-gray-300 rounded-lg shadow-sm p-2" placeholder="Rua, número, cidade - UF">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">E-mail</label>
                            <input type="email" name="contratado_email" value="<?php echo $isEdit ? htmlspecialchars($contrato['contratado_email'] ?? '') : ''; ?>" class="w-full border-gray-300 rounded-lg shadow-sm p-2" placeholder="email@exemplo.com">
                        </div>
                    </div>
                </div>

                <!-- 1.4 Financeiro & Prazo -->
                <div class="bg-gray-50 p-5 rounded-xl border border-gray-200">
                    <h4 class="text-md font-bold text-indigo-700 mb-4 flex items-center gap-2">
                        <i class="fas fa-coins"></i> Financeiro & Prazo
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Valor Total (R$)</label>
                            <input type="text" name="valor" value="<?php echo $isEdit ? number_format($contrato['valor'] ?? 0, 2, ',', '.') : ''; ?>" class="w-full border-gray-300 rounded-lg shadow-sm p-2" placeholder="0,00">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Chave PIX</label>
                            <select name="pix_tipo_chave" class="w-full border-gray-300 rounded-lg shadow-sm p-2">
                                <option value="">Nenhum / Não informado</option>
                                <?php foreach (['CPF','CNPJ','E-mail','Celular','Chave Aleatória'] as $tk): ?>
                                    <option value="<?= $tk ?>" <?php echo ($isEdit && ($contrato['pix_tipo_chave'] ?? '') === $tk) ? 'selected' : ''; ?>><?= $tk ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Forma de Pagamento</label>
                            <input type="text" name="forma_pagamento" value="<?php echo $isEdit ? htmlspecialchars($contrato['forma_pagamento'] ?? '') : ''; ?>" class="w-full border-gray-300 rounded-lg shadow-sm p-2" placeholder="Ex: Mensal, à vista, parcelado...">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Data de Início</label>
                            <input type="date" name="data_inicio" value="<?php echo $isEdit ? htmlspecialchars($contrato['data_inicio'] ?? '') : ''; ?>" class="w-full border-gray-300 rounded-lg shadow-sm p-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Data de Término</label>
                            <input type="date" name="vencimento" value="<?php echo $isEdit ? htmlspecialchars($contrato['vencimento'] ?? '') : ''; ?>" class="w-full border-gray-300 rounded-lg shadow-sm p-2">
                        </div>
                        <div class="md:col-span-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Observações</label>
                            <textarea name="observacoes" rows="2" class="w-full border-gray-300 rounded-lg shadow-sm p-2" placeholder="Observações adicionais..."><?php echo $isEdit ? htmlspecialchars($contrato['observacoes'] ?? '') : ''; ?></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ETAPA 2: Editora de Documentos de Contratos -->
        <div class="p-8 wizard-step hidden" id="step-2">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-bold text-gray-700 flex items-center gap-2">
                    <i class="fas fa-edit text-indigo-500"></i> Editora de Documentos de Contratos
                </h3>
                <button type="button" id="btn-load-modelo" class="text-sm text-indigo-600 hover:underline font-medium">
                    <i class="fas fa-sync mr-1"></i> Resetar para Modelo Padrão
                </button>
            </div>
            
            <div class="mb-4">
                <label for="objeto_wizard" class="block text-sm font-medium text-gray-600 mb-2">Objeto e Cláusulas Editáveis:</label>
                <textarea id="objeto_wizard" name="objeto" class="w-full border-gray-300 rounded-lg p-4 font-mono text-sm leading-relaxed focus:ring-indigo-500 focus:border-indigo-500" rows="20"><?php echo $isEdit ? htmlspecialchars($contrato['objeto']) : ''; ?></textarea>
            </div>
            <p class="text-xs text-gray-400 italic">O conteúdo acima será utilizado para gerar a versão final do documento PDF.</p>
        </div>

        <!-- ETAPA 3: Prévia de Contratos -->
        <div class="p-8 wizard-step hidden" id="step-3">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-bold text-gray-700 flex items-center gap-2">
                    <i class="fas fa-eye text-indigo-500"></i> Prévia de Contratos
                </h3>
                <button type="button" id="btn-download-pdf" class="bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-bold shadow hover:bg-red-700 transition-all flex items-center gap-2">
                    <i class="fas fa-file-pdf"></i> Baixar PDF da Prévia
                </button>
            </div>

            <div id="preview-container" class="bg-gray-50 border-2 border-dashed border-gray-200 rounded-lg p-10 min-h-[400px]">
                <div class="max-w-2xl mx-auto bg-white shadow-sm border p-8 text-gray-800 space-y-4" id="document-mockup">
                    <div class="text-center border-b pb-4 mb-6">
                        <h4 class="font-bold text-xl uppercase tracking-widest">Contrato de Prestação</h4>
                        <p class="text-sm">Instrumento Particular de Acordo</p>
                    </div>
                    <div id="preview-text" class="whitespace-pre-line text-sm leading-relaxed"></div>
                </div>
            </div>

            <div class="mt-6 flex items-center gap-4 bg-blue-50 p-4 rounded-lg">
                <i class="fas fa-info-circle text-blue-500 text-xl"></i>
                <p class="text-sm text-blue-700">Revise todos os dados. Ao confirmar, o contrato será salvo no sistema.</p>
            </div>
        </div>

        <!-- Footer de Navegação -->
        <div class="bg-gray-50 px-8 py-4 flex justify-between items-center border-t border-gray-100">
            <button type="button" id="btn-prev" class="px-6 py-2 text-sm font-bold text-gray-500 hover:text-gray-700 invisible">
                <i class="fas fa-arrow-left mr-2"></i> Voltar
            </button>
            
            <div class="flex gap-3">
                <a href="<?php echo BASE_URL; ?>/contratos" class="px-6 py-2 text-sm font-bold text-gray-400 hover:text-gray-600">Cancelar</a>
                <button type="button" id="btn-next" class="px-8 py-2 bg-indigo-600 text-white rounded-lg font-bold shadow-md hover:bg-indigo-700 transition-all">
                    Próximo <i class="fas fa-arrow-right ml-2"></i>
                </button>
                <button type="submit" id="btn-save" class="px-8 py-2 bg-green-600 text-white rounded-lg font-bold shadow-md hover:bg-green-700 transition-all hidden">
                    <i class="fas fa-check-circle mr-2"></i> Finalizar e Salvar
                </button>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentStep = 1;
    const totalSteps = 3;
    const form = document.getElementById('contract-wizard-form');
    const btnNext = document.getElementById('btn-next');
    const btnPrev = document.getElementById('btn-prev');
    const btnSave = document.getElementById('btn-save');
    const editor = document.getElementById('objeto_wizard');

    function updateUI() {
        document.querySelectorAll('.wizard-step').forEach(step => step.classList.add('hidden'));

        document.getElementById(`step-${currentStep}`).classList.remove('hidden');

        document.querySelectorAll('.ctr-step').forEach(item => {
            const stepNum = parseInt(item.dataset.step);
            const span = item.querySelector('span');

            if (stepNum < currentStep) {
                item.className = "ctr-step done";
                span.innerHTML = '<i class="fas fa-check"></i>';
            } else if (stepNum === currentStep) {
                item.className = "ctr-step active";
                span.innerHTML = stepNum;
            } else {
                item.className = "ctr-step";
                span.innerHTML = stepNum;
            }
        });

        btnPrev.classList.toggle('invisible', currentStep === 1);
        
        if (currentStep === totalSteps) {
            btnNext.classList.add('hidden');
            btnSave.classList.remove('hidden');
            document.getElementById('preview-text').textContent = editor.value;
        } else {
            btnNext.classList.remove('hidden');
            btnSave.classList.add('hidden');
        }

        if (currentStep === 2 && editor.value.trim() === '') {
            loadModelo();
        }
    }

    // Prevenção de duplicidade no envio do formulário
    form.addEventListener('submit', function() {
        if (btnSave) {
            btnSave.disabled = true;
            btnSave.classList.add('opacity-50', 'cursor-not-allowed');
            btnSave.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Processando...';
        }
    });

    // Tornar as seções do 'stepper' clicáveis
    document.querySelectorAll('.ctr-step').forEach(item => {
        item.style.cursor = 'pointer';
        item.addEventListener('click', function() {
            const stepNum = parseInt(this.dataset.step);

            // Se for avançar para a etapa 2 ou 3 a partir da 1, garantir validação
            if (currentStep === 1 && stepNum > 1) {
                const titulo = form.querySelector('[name="titulo"]').value;
                const tipo = form.querySelector('[name="tipo"]').value;
                const contratante = form.querySelector('[name="contratante_nome"]').value;
                const contratado = form.querySelector('[name="contratado_nome"]').value;

                if (!titulo || !tipo || !contratante || !contratado) {
                    alert('Por favor, preencha o Título, Tipo e os Nomes das Partes (Contratante/Contratado) antes de avançar.');
                    return;
                }
            }

            currentStep = stepNum;
            updateUI();
        });
    });

    async function loadModelo() {
        try {
            const response = await fetch('<?php echo BASE_URL; ?>/contratos/getModeloPadrao');
            const data = await response.json();
            if (data.modelo) {
                let text = data.modelo;
                
                // Mapeamento de campos para substituição inteligente
                const replacements = {
                    '{{TITULO}}': form.querySelector('[name="titulo"]').value,
                    '{{FORO}}': form.querySelector('[name="foro_eleicao"]').value,
                    '{{CONTRATANTE_NOME}}': form.querySelector('[name="contratante_nome"]').value,
                    '{{CONTRATANTE_DOC}}': form.querySelector('[name="contratante_documento"]').value,
                    '{{CONTRATANTE_END}}': form.querySelector('[name="contratante_endereco"]').value,
                    '{{CONTRATANTE_EMAIL}}': form.querySelector('[name="contratante_email"]').value,
                    '{{CONTRATADO_NOME}}': form.querySelector('[name="contratado_nome"]').value,
                    '{{CONTRATADO_DOC}}': form.querySelector('[name="contratado_documento"]').value,
                    '{{CONTRATADO_END}}': form.querySelector('[name="contratado_endereco"]').value,
                    '{{CONTRATADO_EMAIL}}': form.querySelector('[name="contratado_email"]').value,
                    '{{VALOR}}': form.querySelector('[name="valor"]').value,
                    '{{FORMA_PAGAMENTO}}': form.querySelector('[name="forma_pagamento"]').value,
                    '{{DATA_INICIO}}': form.querySelector('[name="data_inicio"]').value,
                    '{{DATA_FIM}}': form.querySelector('[name="vencimento"]').value
                };

                for (const [tag, value] of Object.entries(replacements)) {
                    text = text.replaceAll(tag, value || '__________');
                }
                
                editor.value = text;
            }
        } catch (e) { console.error("Erro ao carregar modelo"); }
    }

    btnNext.addEventListener('click', () => {
        if (currentStep === 1) {
            const titulo = form.querySelector('[name="titulo"]').value;
            const tipo = form.querySelector('[name="tipo"]').value;
            const contratante = form.querySelector('[name="contratante_nome"]').value;
            const contratado = form.querySelector('[name="contratado_nome"]').value;

            if (!titulo || !tipo || !contratante || !contratado) {
                alert('Por favor, preencha o Título, Tipo e os Nomes das Partes (Contratante/Contratado).');
                return;
            }
        }
        currentStep++;
        updateUI();
    });

    btnPrev.addEventListener('click', () => {
        currentStep--;
        updateUI();
    });

    document.getElementById('btn-load-modelo').addEventListener('click', () => {
        if (confirm('Isso substituirá todo o texto atual pelo modelo padrão. Deseja continuar?')) {
            loadModelo();
        }
    });

    // Lógica para Download do PDF da Prévia
    document.getElementById('btn-download-pdf').addEventListener('click', () => {
        // Cria um formulário temporário para enviar o conteúdo via POST e abrir em nova aba
        const tempForm = document.createElement('form');
        tempForm.method = 'POST';
        tempForm.action = '<?php echo BASE_URL; ?>/contratos/gerarPdfWizard';
        tempForm.target = '_blank';

        // Clone o conteúdo do objeto (editor)
        const inputObjeto = document.createElement('input');
        inputObjeto.type = 'hidden';
        inputObjeto.name = 'objeto';
        inputObjeto.value = editor.value;
        tempForm.appendChild(inputObjeto);

        const inputTipo = document.createElement('input');
        inputTipo.type = 'hidden';
        inputTipo.name = 'tipo';
        inputTipo.value = form.querySelector('[name="tipo"]').value;
        tempForm.appendChild(inputTipo);

        // Envia dados das partes para gerar o bloco de assinaturas no PDF da prévia
        const fields = ['contratante_nome', 'contratante_documento', 'contratante_endereco', 'contratante_email', 'contratado_nome', 'contratado_documento', 'contratado_endereco', 'contratado_email', 'local_assinatura'];
        fields.forEach(f => {
            const val = form.querySelector(`[name="${f}"]`)?.value || '';
            const inp = document.createElement('input');
            inp.type = 'hidden';
            inp.name = f;
            inp.value = val;
            tempForm.appendChild(inp);
        });

        document.body.appendChild(tempForm);
        tempForm.submit();
        document.body.removeChild(tempForm);
    });

    // Lógica de Busca Automática por CNPJ
    window.executarBuscaCnpj = async function(prefix) {
        const docInput = document.getElementById(`${prefix}_documento`);
        const cnpj = docInput.value.replace(/\D/g, '');

        if (cnpj.length !== 14) {
            alert('Por favor, insira um CNPJ válido (14 dígitos) para realizar a busca.');
            return;
        }

        const btn = event.currentTarget;
        const originalIcon = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        btn.disabled = true;

        try {
            // 1. Verifica se já existe no banco de dados local
            const checkResp = await fetch(`<?php echo BASE_URL; ?>/contratos/verificarDocumentoExistente/${cnpj}`);
            const checkResult = await checkResp.json();
            
            if (checkResult.exists && checkResult.entidade) {
                const ent = checkResult.entidade;
                if (confirm(`Registro encontrado no banco local (${ent.tipo_entidade}: ${ent.nome_entidade}). Deseja carregar os dados cadastrados?`)) {
                    const nomeInput = document.querySelector(`[name="${prefix}_nome"]`);
                    const addrInput = document.querySelector(`[name="${prefix}_endereco"]`);
                    const emailInput = document.querySelector(`[name="${prefix}_email"]`);
                    
                    if (nomeInput) nomeInput.value = ent.nome_entidade || '';
                    if (addrInput) addrInput.value = ent.endereco || '';
                    if (emailInput) emailInput.value = ent.email || '';
                    return; // Finaliza aqui, não precisa buscar na API externa
                }
            }

            // 2. Busca na API Externa
            const response = await fetch(`<?php echo BASE_URL; ?>/contratos/buscarCnpjAjax/${cnpj}`);
            const result = await response.json();

            if (result.success) {
                const data = result.data;
                const nomeInput = document.querySelector(`[name="${prefix}_nome"]`);
                if (nomeInput) nomeInput.value = data.razao_social || data.nome_fantasia || '';
                
                // Construção robusta e completa do Endereço
                const addrPieces = [];
                
                // Tipo + Logradouro + Número
                let logradouro = [data.descricao_tipo_de_logradouro, data.logradouro].filter(Boolean).join(' ');
                if (logradouro) {
                    if (data.numero && data.numero !== 'S/N') {
                        logradouro += `, ${data.numero}`;
                    }
                    addrPieces.push(logradouro);
                } else if (data.numero && data.numero !== 'S/N') {
                    addrPieces.push(data.numero);
                }

                if (data.complemento) addrPieces.push(data.complemento);
                if (data.bairro) addrPieces.push(data.bairro);

                let cityState = data.municipio || '';
                if (data.uf) cityState += cityState ? ` - ${data.uf}` : data.uf;
                if (cityState) addrPieces.push(cityState);

                if (data.cep) {
                    const cepFormatado = data.cep.replace(/\D/g, '').replace(/^(\d{5})(\d{3})$/, "$1-$2");
                    addrPieces.push(`CEP: ${cepFormatado}`);
                }

                const addrInput = document.querySelector(`[name="${prefix}_endereco"]`);
                if (addrInput) addrInput.value = addrPieces.join(', ');
                
                // Email (se disponível)
                if (data.email) {
                    form.querySelector(`[name="${prefix}_email"]`).value = data.email;
                }
            } else {
                alert(result.message || 'CNPJ não encontrado.');
            }
        } catch (error) {
            alert('Erro ao consultar o serviço. Verifique sua conexão.');
        } finally {
            btn.innerHTML = originalIcon;
            btn.disabled = false;
        }
    };

    // Máscara automática para CPF/CNPJ durante a digitação
    form.querySelectorAll('[name$="_documento"]').forEach(input => {
        input.addEventListener('input', (e) => {
            let v = e.target.value.replace(/\D/g, '');
            if (v.length <= 11) { // Formata como CPF
                v = v.replace(/(\d{3})(\d)/, '$1.$2').replace(/(\d{3})(\d)/, '$1.$2').replace(/(\d{3})(\d{1,2})$/, '$1-$2');
            } else { // Formata como CNPJ
                v = v.replace(/^(\d{2})(\d)/, '$1.$2').replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3').replace(/\.(\d{3})(\d)/, '.$1/$2').replace(/(\d{4})(\d)/, '$1-$2');
            }
            e.target.value = v.substring(0, 18);
        });
    });

    const valorInput = form.querySelector('[name="valor"]');
    valorInput.addEventListener('input', (e) => {
        let value = e.target.value.replace(/\D/g, '');
        value = (value / 100).toLocaleString('pt-BR', { minimumFractionDigits: 2 });
        e.target.value = value === '0,00' ? '' : value;
    });

    updateUI();
});
</script>

<style>
.step-item { transition: all 0.3s ease; }
.connector { transition: background-color 0.3s ease; align-self: center; }
</style>