<?php
/**
 * Patrimônio - Formulário (form.php)
 * Tela de cadastro e edição de bem patrimonial (página dedicada).
 *
 * Variáveis esperadas do controller:
 *   $pageTitle  string
 *   $bem        array|null  (preenchido na edição; vazio no cadastro)
 */
$isEdit    = isset($bem) && !empty($bem['id']);
$actionUrl = BASE_URL . '/patrimonio/salvar';

$tipos = [
    ['val' => 'Equipamento de TI',    'label' => 'Equip. TI',  'icon' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 0 1-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0 1 15 18.257V17.25m6-12V15a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 15V5.25m18 0A2.25 2.25 0 0 0 18.75 3H5.25A2.25 2.25 0 0 0 3 5.25m18 0H3"/></svg>'],
    ['val' => 'Veículo',              'label' => 'Veículo',    'icon' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/></svg>'],
    ['val' => 'Imóvel',               'label' => 'Imóvel',     'icon' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75"/></svg>'],
    ['val' => 'Mobiliário',           'label' => 'Mobiliário', 'icon' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/></svg>'],
    ['val' => 'Máquina / Ferramenta', 'label' => 'Máquina',   'icon' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l5.653-4.655m5.8-7.425a3 3 0 1 1-4.243 4.243"/></svg>'],
    ['val' => 'Software / Licença',   'label' => 'Software',  'icon' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 7.5l3 2.25-3 2.25m4.5 0h3m-9 8.25h13.5A2.25 2.25 0 0 0 21 18V6a2.25 2.25 0 0 0-2.25-2.25H5.25A2.25 2.25 0 0 0 3 6v12a2.25 2.25 0 0 0 2.25 2.25z"/></svg>'],
    ['val' => 'Outro',                'label' => 'Outro',     'icon' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.75a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5zM12 12.75a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5zM12 18.75a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5z"/></svg>'],
];

$currentTipo = $bem['classificacao'] ?? '';
$csrf_token  = $csrf_token ?? ''; // Garantido pelo BaseController::renderView
?>

<style>
.pf-wrap{font-family: 'Plus Jakarta Sans', sans-serif; padding: 0.5rem 0 2.5rem}
.pf-topbar{display:flex;justify-content:space-between;align-items:center;margin-bottom:2rem;gap:1rem;flex-wrap:wrap}
.pf-topbar h1{font-size:24px;font-weight:700;color:var(--sys-text-main);margin-bottom:2px;letter-spacing:-0.02em}
.pf-topbar p{font-size:14px;color:var(--sys-text-muted)}

.pf-btn-back{display:inline-flex;align-items:center;gap:8px;border:1px solid var(--sys-border);background:var(--sys-surface);border-radius:12px;padding:8px 16px;font-size:13px;font-weight:600;color:var(--sys-text-muted);cursor:pointer;text-decoration:none;white-space:nowrap;transition:all 0.2s}
.pf-btn-back:hover{background:var(--sys-surface-alt);color:var(--sys-text-main);transform:translateX(-2px)}
.pf-btn-back svg{width:15px;height:15px}

/* Progress */
.pf-progress{display:flex;align-items:center;margin-bottom:2rem;background:var(--sys-surface);padding:1rem;border-radius:16px;border:1px solid var(--sys-border);box-shadow:0 2px 4px rgba(0,0,0,0.02)}
.pf-prog-step{display:flex;align-items:center;gap:10px;font-size:14px;color:var(--sys-text-muted);font-weight:600}
.pf-prog-step.active{color:var(--mod-sky)}
.pf-prog-dot{width:32px;height:32px;border-radius:10px;border:2px solid var(--sys-border);display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:800;background:var(--sys-surface-alt);color:var(--sys-text-muted);flex-shrink:0;transition:all 0.3s}
.pf-prog-dot.active{background:var(--mod-sky);border-color:var(--mod-sky);color:#fff;box-shadow:0 4px 10px rgba(var(--mod-sky-rgb), 0.3)}
.pf-prog-dot.done{background:#16a34a;border-color:#16a34a;color:#fff}
.pf-prog-line{flex:1;height:2px;background:var(--sys-border);margin:0 12px;min-width:20px;border-radius:2px}

/* Cards de seção */
.pf-card{background:var(--sys-surface);border:1px solid var(--sys-border);border-radius:20px;padding:1.5rem;margin-bottom:1.5rem;box-shadow:0 4px 6px -1px rgba(0,0,0,0.02),0 2px 4px -1px rgba(0,0,0,0.02)}
.pf-section-header{display:flex;align-items:center;gap:12px;margin-bottom:1.5rem;padding-bottom:12px;border-bottom:1px solid var(--sys-border)}
.pf-section-icon{width:56px;height:52px;border-radius:14px;display:flex;align-items:center;justify-content:center;flex-shrink:0;box-shadow:0 4px 8px rgba(0,0,0,0.08)}
.pf-section-icon svg{width:36px;height:36px}
.pf-section-icon.blue{background:var(--sys-blue-soft);color:var(--mod-blue)}
.pf-section-icon.green{background:var(--sys-green-soft);color:var(--sys-green)}
.pf-section-icon.amber{background:var(--sys-orange-soft);color:var(--sys-orange)}
.pf-section-title{font-size:16px;font-weight:700;color:var(--sys-text-main);letter-spacing:-0.01em}
.pf-section-sub{font-size:12px;color:var(--sys-text-muted);margin-top:1px}

/* Grid de campos */
.pf-grid{display:grid;grid-template-columns:1fr 1fr;gap:1rem}
.pf-full{grid-column:1/-1}
.pf-field{display:flex;flex-direction:column;gap:5px}
.pf-field label{font-size:12px;font-weight:600;color:var(--sys-text-muted)}
.pf-field input,.pf-field select,.pf-field textarea{width:100%;background:var(--sys-surface-alt);border:1px solid var(--sys-border);border-radius:10px;padding:12px 14px;font-size:14px;color:var(--sys-text-main);font-family:inherit;transition:all 0.2s}
.pf-field input::placeholder,.pf-field textarea::placeholder{color:var(--sys-text-light)}
.pf-field input:focus,.pf-field select:focus,.pf-field textarea:focus{outline:none;border-color:var(--mod-sky);box-shadow:0 0 0 4px rgba(var(--mod-sky-rgb), 0.1);background:var(--sys-surface)}
.pf-hint{font-size:11px;color:var(--sys-text-muted);font-style:italic}
.pf-req{color:#dc2626}

/* Tipo buttons */
.pf-tipo-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(100px,1fr));gap:10px}
.pf-tipo-btn{display:flex;flex-direction:column;align-items:center;gap:6px;padding:12px 8px;border:1px solid var(--sys-border);border-radius:12px;background:var(--sys-surface);cursor:pointer;font-size:12px;font-weight:600;color:var(--sys-text-muted);text-align:center;transition:all 0.2s}
.pf-tipo-btn svg{width:56px;height:56px}
.pf-tipo-btn:hover{background:var(--sys-surface-alt);border-color:var(--sys-text-light);color:var(--sys-text-main)}
.pf-tipo-btn.selected{border-color:var(--mod-sky);background:var(--sys-blue-soft);color:var(--mod-sky);box-shadow:0 4px 12px rgba(var(--mod-sky-rgb), 0.15)}

/* Footer */
.pf-footer{display:flex;justify-content:flex-end;align-items:center;gap:10px;padding-top:.5rem}
.pf-btn-cancel{border:1px solid var(--sys-border);background:transparent;border-radius:12px;padding:10px 24px;font-size:14px;font-weight:600;color:var(--sys-text-muted);cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;transition:all 0.2s}
.pf-btn-cancel:hover{background:var(--sys-surface-alt);color:var(--sys-text-main)}
.pf-btn-save{background:var(--mod-sky);color:#fff;border:none;border-radius:12px;padding:11px 28px;font-size:14px;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;gap:8px;box-shadow:0 4px 12px rgba(var(--mod-sky-rgb), 0.3);transition:all 0.2s}
.pf-btn-save:hover{background:var(--mod-sky-dark);transform:translateY(-1px);box-shadow:0 6px 15px rgba(var(--mod-sky-rgb), 0.4)}
.pf-btn-save svg{width:20px;height:20px}

@media(max-width:600px){
  .pf-grid{grid-template-columns:1fr}
  .pf-full{grid-column:1}
}
</style>

<div class="pf-wrap">

    <!-- Topbar -->
    <div class="pf-topbar">
        <div>
            <h1><?php echo htmlspecialchars($pageTitle ?? ($isEdit ? 'Editar Bem Patrimonial' : 'Cadastro de Bem Patrimonial')); ?></h1>
            <p><?php echo $isEdit ? 'Atualize as informações do bem abaixo.' : 'Preencha as informações abaixo para registrar um novo bem.'; ?></p>
        </div>
        <a href="<?php echo BASE_URL; ?>/patrimonio" class="pf-btn-back">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
            Voltar ao dashboard
        </a>
    </div>

    <!-- Barra de progresso -->
    <div class="pf-progress">
        <div class="pf-prog-step active">
            <div class="pf-prog-dot active">1</div>
            <span>Identificação</span>
        </div>
        <div class="pf-prog-line"></div>
        <div class="pf-prog-step">
            <div class="pf-prog-dot">2</div>
            <span>Localização</span>
        </div>
        <div class="pf-prog-line"></div>
        <div class="pf-prog-step">
            <div class="pf-prog-dot">3</div>
            <span>Dados contábeis</span>
        </div>
    </div>

    <form action="<?php echo $actionUrl; ?>" method="POST">
        <?php if ($isEdit): ?>
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($bem['id']); ?>">
        <?php endif; ?>
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
        <input type="hidden" id="pf-classificacao" name="classificacao" value="<?php echo htmlspecialchars($currentTipo); ?>">

        <!-- SEÇÃO 1: Identificação -->
        <div class="pf-card">
            <div class="pf-section-header">
                <div class="pf-section-icon blue">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z"/>
                    </svg>
                </div>
                <div>
                    <div class="pf-section-title">Identificação do bem</div>
                    <div class="pf-section-sub">Informações básicas e classificação do ativo</div>
                </div>
            </div>

            <div class="pf-grid">
                <div class="pf-field pf-full">
                    <label for="pf-nome">Nome / Descrição do bem <span class="pf-req">*</span></label>
                    <input type="text" id="pf-nome" name="nome" required
                           value="<?php echo htmlspecialchars($bem['nome'] ?? ''); ?>"
                           placeholder="Ex: Notebook Dell Latitude 5540">
                </div>

                <div class="pf-field">
                    <label for="pf-num">Nº de patrimônio / Plaqueta</label>
                    <input type="text" id="pf-num" name="numero_patrimonio"
                           value="<?php echo htmlspecialchars($bem['numero_patrimonio'] ?? ''); ?>"
                           placeholder="Ex: PAT-00124">
                    <span class="pf-hint">Deixe em branco para gerar automaticamente</span>
                </div>

                <div class="pf-field">
                    <label for="pf-serie">Número de série / IMEI</label>
                    <input type="text" id="pf-serie" name="numero_serie" 
                           value="<?php echo htmlspecialchars($bem['numero_serie'] ?? ''); ?>"
                           placeholder="Opcional">
                </div>

                <!-- Tipo -->
                <div class="pf-full" style="display:flex;flex-direction:column;gap:8px;">
                    <label style="font-size:12px;font-weight:600;color:var(--sys-text-muted);">
                        Classificação / Tipo <span class="pf-req">*</span> 
                    </label>
                    <div class="pf-tipo-grid">
                        <?php foreach ($tipos as $t): ?>
                            <button type="button"
                                    class="pf-tipo-btn <?php echo ($currentTipo === $t['val']) ? 'selected' : ''; ?>"
                                    data-val="<?php echo htmlspecialchars($t['val']); ?>">
                                <?php echo $t['icon']; ?>
                                <?php echo htmlspecialchars($t['label']); ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- SEÇÃO 2: Localização e Responsável -->
        <div class="pf-card">
            <div class="pf-section-header">
                <div class="pf-section-icon green">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0z"/>
                    </svg>
                </div>
                <div>
                    <div class="pf-section-title">Localização e responsável</div>
                    <div class="pf-section-sub">Onde o bem está e quem é o guardião</div>
                </div>
            </div>

            <div class="pf-grid">
                <div class="pf-field">
                    <label for="pf-localizacao">Localização / Setor <span class="pf-req">*</span></label>
                    <input type="text" id="pf-localizacao" name="localizacao" required
                           value="<?php echo htmlspecialchars($bem['localizacao'] ?? ''); ?>"
                           placeholder="Ex: Sala de TI, Administrativo, Campo">
                </div>

                <div class="pf-field">
                    <label for="pf-responsavel">Responsável pelo bem</label>
                    <input type="text" id="pf-responsavel" name="responsavel"
                           value="<?php echo htmlspecialchars($bem['responsavel'] ?? ''); ?>"
                           placeholder="Nome do colaborador">
                </div>

                <div class="pf-field pf-full">
                    <label for="pf-observacoes">Observações</label>
                    <textarea id="pf-observacoes" name="observacoes" rows="3"
                              placeholder="Estado de conservação, detalhes relevantes..."><?php echo htmlspecialchars($bem['observacoes'] ?? ''); ?></textarea>
                </div>
            </div>
        </div>

        <!-- SEÇÃO 3: Dados Contábeis -->
        <div class="pf-card">
            <div class="pf-section-header">
                <div class="pf-section-icon amber">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941"/>
                    </svg>
                </div>
                <div>
                    <div class="pf-section-title">Dados contábeis e depreciação</div>
                    <div class="pf-section-sub">Valores financeiros e vida útil estimada do ativo</div>
                </div>
            </div>

            <div class="pf-grid">
                <div class="pf-field">
                    <label for="pf-data_aquisicao">Data de aquisição</label>
                    <input type="date" id="pf-data_aquisicao" name="data_aquisicao"
                           value="<?php echo htmlspecialchars($bem['data_aquisicao'] ?? ''); ?>">
                </div>

                <div class="pf-field">
                    <label for="pf-valor_aquisicao">Valor de aquisição (R$)</label>
                    <input type="text" id="pf-valor_aquisicao" name="valor_aquisicao" class="money-mask"
                           value="<?php echo htmlspecialchars($bem['valor_aquisicao'] ?? ''); ?>"
                           placeholder="1.500,00">
                </div>

                <div class="pf-field">
                    <label for="pf-vida_util">Vida útil (meses)</label>
                    <input type="number" id="pf-vida_util" name="vida_util_meses" min="1" 
                           value="<?php echo htmlspecialchars($bem['vida_util_meses'] ?? ''); ?>"
                           placeholder="60">
                    <span class="pf-hint">Usado no cálculo de depreciação linear</span>
                </div>

                <div class="pf-field">
                    <label for="pf-centro_custo">Centro de custo / Conta</label>
                    <input type="text" id="pf-centro_custo" name="centro_custo"
                           value="<?php echo htmlspecialchars($bem['centro_custo'] ?? ''); ?>"
                           placeholder="Ex: TI, Administrativo">
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="pf-footer">
            <a href="<?php echo BASE_URL; ?>/patrimonio" class="pf-btn-cancel">Cancelar</a>
            <button type="submit" class="pf-btn-save">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                <?php echo $isEdit ? 'Atualizar bem' : 'Salvar bem'; ?>
            </button>
        </div>

    </form>
</div>

<script>
(function () {
    const classInput = document.getElementById('pf-classificacao');

    document.querySelectorAll('.pf-tipo-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.pf-tipo-btn').forEach(b => b.classList.remove('selected'));
            btn.classList.add('selected');
            classInput.value = btn.dataset.val;
        });
    });

    // Validação simples de classificação antes do envio
    document.querySelector('form').addEventListener('submit', function (e) {
        if (!classInput.value) {
            e.preventDefault();
            alert('Por favor, selecione a classificação do bem.');
            classInput.closest('.pf-card') && classInput.closest('.pf-card').scrollIntoView({ behavior: 'smooth' });
        }
    });

    // Máscara monetária simples
    const moneyInput = document.querySelector('.money-mask');
    if(moneyInput) {
        moneyInput.addEventListener('input', (e) => {
            let v = e.target.value.replace(/\D/g, '');
            e.target.value = (parseInt(v || 0) / 100).toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        }
        );
    }
})();
</script>
