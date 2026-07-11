<?php
$isEdit = isset($banco) && $banco !== null;
$actionUrl = BASE_URL . '/banco/salvar';

// Lista de bancos brasileiros mais comuns (código + nome) para o campo "Banco"
$bancosComuns = [
    '001' => 'Banco do Brasil',
    '033' => 'Santander',
    '041' => 'Banrisul',
    '403' => 'Banco Cora',
    '104' => 'Caixa Econômica Federal',
    '197' => 'Stone',
    '237' => 'Bradesco',
    '260' => 'Nubank',
    '290' => 'PagSeguro',
    '336' => 'C6 Bank',
    '341' => 'Itaú',
    '380' => 'PicPay',
    '422' => 'Banco Safra',
    '748' => 'Sicredi',
    '756' => 'Sicoob',
    '077' => 'Banco Inter',
];
?>

<style>
    /* ===== Design tokens — módulo Financeiro ===== */
    :root {
        --bk-bg: #f8fafc;
        --bk-panel: #ffffff;
        --bk-panel-2: #f1f5f9;
        --bk-border: #e2e8f0;
        --bk-border-soft: #d1d5db;
        --bk-text: #0f172a;
        --bk-text-dim: #475569;
        --bk-text-faint: #64748b;
        --bk-accent: #10b981;
        --bk-accent-dim: #0d9668;
        --bk-accent-soft: rgba(16, 185, 129, 0.12);
        --bk-danger: #ef4444;
        --bk-radius: 12px;
    }

    .dark-theme .bk-wrap {
        --bk-bg: #0f1623;
        --bk-panel: #161f30;
        --bk-panel-2: #1c2538;
        --bk-border: #2a3650;
        --bk-border-soft: #233048;
        --bk-text: #e7ecf5;
        --bk-text-dim: #93a0b8;
        --bk-text-faint: #5d6b85;
        --bk-accent: #10b981;
        --bk-accent-dim: #0d9668;
        --bk-accent-soft: rgba(16, 185, 129, 0.12);
        --bk-danger: #f87171;
    }

    .bk-wrap {
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
        color: var(--bk-text);
    }

    .bk-shell {
        max-width: 880px;
        margin: 0 auto;
    }

    .bk-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1.75rem;
        padding-bottom: 1.25rem;
        border-bottom: 1px solid var(--bk-border-soft);
    }

    .bk-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        font-size: .7rem;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: var(--bk-accent);
        background: var(--bk-accent-soft);
        border: 1px solid rgba(16,185,129,.25);
        padding: .3rem .65rem;
        border-radius: 999px;
        margin-bottom: .65rem;
    }

    .bk-title {
        font-size: 1.5rem;
        font-weight: 700;
        letter-spacing: -0.01em;
        color: var(--bk-text);
        margin: 0;
    }

    .bk-subtitle {
        color: var(--bk-text-dim);
        font-size: .875rem;
        margin-top: .35rem;
    }

    .bk-status-pill {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        font-size: .75rem;
        font-weight: 600;
        padding: .4rem .75rem;
        border-radius: 999px;
        background: var(--bk-panel-2);
        border: 1px solid var(--bk-border);
        color: var(--bk-text-dim);
        white-space: nowrap;
    }
    .bk-status-pill.is-active { color: #34d399; border-color: rgba(52,211,153,.3); background: rgba(52,211,153,.08); }
    .bk-status-pill .dot { width: 6px; height: 6px; border-radius: 50%; background: currentColor; }

    /* ===== Form sections ===== */
    .bk-form { display: flex; flex-direction: column; gap: 1.25rem; }

    .bk-section {
        background: var(--bk-panel);
        border: 1px solid var(--bk-border-soft);
        border-radius: var(--bk-radius);
        padding: 1.5rem;
    }

    .bk-section-head {
        display: flex;
        align-items: center;
        gap: .6rem;
        margin-bottom: 1.25rem;
    }

    .bk-section-icon {
        width: 30px;
        height: 30px;
        border-radius: 8px;
        background: var(--bk-accent-soft);
        color: var(--bk-accent);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 1rem;
    }

    .bk-section-title {
        font-size: .95rem;
        font-weight: 600;
        color: var(--bk-text);
    }

    .bk-section-desc {
        font-size: .78rem;
        color: var(--bk-text-faint);
        margin-top: .1rem;
    }

    .bk-grid { display: grid; gap: 1rem; grid-template-columns: repeat(12, 1fr); }
    .bk-col-3 { grid-column: span 3; }
    .bk-col-4 { grid-column: span 4; }
    .bk-col-6 { grid-column: span 6; }
    .bk-col-8 { grid-column: span 8; }
    .bk-col-12 { grid-column: span 12; }

    @media (max-width: 720px) {
        .bk-col-3, .bk-col-4, .bk-col-6, .bk-col-8 { grid-column: span 12; }
    }

    .bk-field label {
        display: block;
        font-size: .78rem;
        font-weight: 600;
        color: var(--bk-text-dim);
        margin-bottom: .4rem;
    }
    .bk-field label .req { color: var(--bk-danger); margin-left: .15rem; }
    .bk-field label .opt { color: var(--bk-text-faint); font-weight: 500; text-transform: none; letter-spacing: 0; margin-left: .3rem; }

    .bk-input, .bk-select, .bk-textarea {
        width: 100%;
        background: var(--bk-panel-2);
        border: 1px solid var(--bk-border);
        color: var(--bk-text);
        border-radius: 8px;
        padding: .65rem .8rem;
        font-size: .875rem;
        transition: border-color .15s, box-shadow .15s, background .15s;
    }
    .bk-input::placeholder, .bk-textarea::placeholder { color: var(--bk-text-faint); }
    .bk-input:focus, .bk-select:focus, .bk-textarea:focus {
        outline: none;
        border-color: var(--bk-accent);
        box-shadow: 0 0 0 3px var(--bk-accent-soft);
        background: var(--bk-panel-2);
    }
    .bk-textarea { resize: vertical; min-height: 80px; }

    .bk-input-prefix {
        position: relative;
    }
    .bk-input-prefix span {
        position: absolute;
        left: .8rem;
        top: 50%;
        transform: translateY(-50%);
        color: var(--bk-text-faint);
        font-size: .875rem;
        pointer-events: none;
    }
    .bk-input-prefix input { padding-left: 2.1rem; }

    .bk-help {
        font-size: .72rem;
        color: var(--bk-text-faint);
        margin-top: .35rem;
    }

    /* Toggle de tipo de conta */
    .bk-toggle-group {
        display: flex;
        background: var(--bk-panel-2);
        border: 1px solid var(--bk-border);
        border-radius: 8px;
        padding: 3px;
        gap: 3px;
    }
    .bk-toggle-group input { display: none; }
    .bk-toggle-group label {
        flex: 1;
        text-align: center;
        font-size: .8rem;
        font-weight: 600;
        color: var(--bk-text-dim);
        padding: .55rem .5rem;
        border-radius: 6px;
        cursor: pointer;
        margin: 0;
        transition: all .15s;
    }
    .bk-toggle-group input:checked + label {
        background: var(--bk-accent);
        color: #06281c;
    }

    /* Switch ativo/inativo */
    .bk-switch-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: var(--bk-panel-2);
        border: 1px solid var(--bk-border);
        border-radius: 8px;
        padding: .9rem 1rem;
    }
    .bk-switch-row .label-block strong { font-size: .85rem; color: var(--bk-text); display: block; }
    .bk-switch-row .label-block span { font-size: .75rem; color: var(--bk-text-faint); }

    .bk-switch {
        position: relative;
        width: 42px;
        height: 24px;
        flex-shrink: 0;
    }
    .bk-switch input { opacity: 0; width: 0; height: 0; }
    .bk-switch .slider {
        position: absolute; inset: 0;
        background: var(--bk-border);
        border-radius: 999px;
        cursor: pointer;
        transition: background .2s;
    }
    .bk-switch .slider::before {
        content: "";
        position: absolute;
        width: 18px; height: 18px;
        left: 3px; top: 3px;
        background: var(--bk-text-dim);
        border-radius: 50%;
        transition: transform .2s, background .2s;
    }
    .bk-switch input:checked + .slider { background: var(--bk-accent-dim); }
    .bk-switch input:checked + .slider::before { transform: translateX(18px); background: #fff; }

    /* Saldo preview card */
    .bk-balance-preview {
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: linear-gradient(135deg, rgba(16,185,129,.1), rgba(16,185,129,.02));
        border: 1px solid rgba(16,185,129,.25);
        border-radius: 10px;
        padding: 1rem 1.1rem;
        margin-top: .9rem;
    }
    .bk-balance-preview .lbl { font-size: .72rem; color: var(--bk-text-dim); text-transform: uppercase; letter-spacing: .04em; }
    .bk-balance-preview .val { font-size: 1.4rem; font-weight: 700; color: #34d399; margin-top: .2rem; }

    /* Cor identificadora */
    .bk-color-swatches { display: flex; gap: .55rem; flex-wrap: wrap; margin-top: .5rem; }
    .bk-color-swatches input { display: none; }
    .bk-color-swatches label {
        width: 30px; height: 30px;
        border-radius: 50%;
        cursor: pointer;
        border: 2px solid transparent;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: transform .15s, border-color .15s;
    }
    .bk-color-swatches label:hover { transform: scale(1.1); }
    .bk-color-swatches input:checked + label { border-color: #fff; box-shadow: 0 0 0 2px var(--bk-bg), 0 0 0 3px currentColor; }

    /* Footer */
    .bk-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        margin-top: .5rem;
        padding-top: 1.25rem;
        border-top: 1px solid var(--bk-border-soft);
    }
    .bk-footer-hint { font-size: .75rem; color: var(--bk-text-faint); }

    .bk-btn {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        font-size: .85rem;
        font-weight: 600;
        padding: .65rem 1.3rem;
        border-radius: 8px;
        cursor: pointer;
        border: 1px solid transparent;
        transition: all .15s;
        text-decoration: none;
    }
    .bk-btn-ghost {
        background: transparent;
        border-color: var(--bk-border);
        color: var(--bk-text-dim);
    }
    .bk-btn-ghost:hover { background: var(--bk-panel-2); color: var(--bk-text); }

    .bk-btn-primary {
        background: var(--bk-accent);
        color: #06281c;
        box-shadow: 0 4px 14px rgba(16,185,129,.25);
    }
    .bk-btn-primary:hover { background: #14c98e; box-shadow: 0 6px 18px rgba(16,185,129,.35); }

    .bk-actions-right { display: flex; gap: .7rem; }
</style>

<div class="bk-wrap">
    <div class="bk-shell">

        <!-- Header -->
        <div class="bk-header">
            <div>
                <span class="bk-eyebrow">
                    <i class="bx bx-buildings"></i> Módulo Financeiro
                </span>
                <h2 class="bk-title"><?php echo htmlspecialchars($pageTitle ?? ($isEdit ? 'Editar Conta Bancária' : 'Nova Conta Bancária')); ?></h2>
                <p class="bk-subtitle">Cadastre os dados completos da conta, agência e configurações de uso.</p>
            </div>
            <?php if ($isEdit): ?>
                <span class="bk-status-pill <?php echo (!isset($banco['ativo']) || $banco['ativo']) ? 'is-active' : ''; ?>">
                    <span class="dot"></span>
                    <?php echo (!isset($banco['ativo']) || $banco['ativo']) ? 'Conta Ativa' : 'Conta Inativa'; ?>
                </span>
            <?php endif; ?>
        </div>

        <form action="<?php echo $actionUrl; ?>" method="POST" class="bk-form" autocomplete="off" enctype="multipart/form-data">

            <?php if (function_exists('csrf_token')): ?>
                <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
            <?php endif; ?>
            <input type="hidden" name="id" value="<?php echo $isEdit ? htmlspecialchars($banco['id']) : ''; ?>">

            <!-- ===== Seção 1: Identificação ===== -->
            <div class="bk-section">
                <div class="bk-section-head">
                    <div class="bk-section-icon"><i class="bx bx-id-card"></i></div>
                    <div>
                        <div class="bk-section-title">Identificação</div>
                        <div class="bk-section-desc">Nome interno e instituição financeira vinculada</div>
                    </div>
                </div>

                <div class="bk-grid">
                    <div class="bk-col-6 bk-field">
                        <label for="nome">Nome do Banco/Conta<span class="req">*</span></label>
                        <input type="text" id="nome" name="nome" required maxlength="100"
                            placeholder="Ex: Conta Corrente Principal"
                            value="<?php echo $isEdit ? htmlspecialchars($banco['nome']) : ''; ?>"
                            class="bk-input">
                        <p class="bk-help">Como esta conta aparecerá nos relatórios e telas do sistema.</p>
                    </div>

                    <div class="bk-col-6 bk-field">
                        <label for="nome_titular">Titular da Conta<span class="opt">(opcional)</span></label>
                        <input type="text" id="nome_titular" name="nome_titular" maxlength="100"
                            placeholder="Ex: João da Silva"
                            value="<?php echo $isEdit ? htmlspecialchars($banco['nome_titular'] ?? '') : ''; ?>"
                            class="bk-input">
                        <p class="bk-help">Nome do titular da conta (pessoa física ou jurídica).</p>
                    </div>

                    <div class="bk-col-6 bk-field">
                        <label for="banco_codigo">Instituição Financeira<span class="opt">(opcional)</span></label>
                        <select id="banco_codigo" name="banco_codigo" class="bk-select">
                            <option value="">Selecione ou deixe em branco</option>
                            <?php foreach ($bancosComuns as $codigo => $nomeBanco): ?>
                                <option value="<?php echo $codigo; ?>"
                                    <?php echo ($isEdit && isset($banco['banco_codigo']) && $banco['banco_codigo'] === $codigo) ? 'selected' : ''; ?>>
                                    <?php echo $codigo . ' - ' . $nomeBanco; ?>
                                </option>
                            <?php endforeach; ?>
                            <option value="outro" <?php echo ($isEdit && isset($banco['banco_codigo']) && $banco['banco_codigo'] === 'outro') ? 'selected' : ''; ?>>Outro / Não listado</option>
                        </select>
                    </div>

                    <div class="bk-col-12 bk-field">
                        <label>Tipo de Conta</label>
                        <div class="bk-toggle-group">
                            <?php
                            $tipoAtual = $isEdit ? ($banco['tipo'] ?? 'corrente') : 'corrente';
                            $tipos = [
                                'corrente' => 'Conta Corrente',
                                'poupanca' => 'Poupança',
                                'caixa'    => 'Caixa Físico',
                                'digital'  => 'Conta Digital',
                            ];
                            foreach ($tipos as $valor => $rotulo):
                            ?>
                                <input type="radio" id="tipo_<?php echo $valor; ?>" name="tipo" value="<?php echo $valor; ?>"
                                    <?php echo ($tipoAtual === $valor) ? 'checked' : ''; ?>>
                                <label for="tipo_<?php echo $valor; ?>"><?php echo $rotulo; ?></label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ===== Seção 2: Dados Bancários ===== -->
            <div class="bk-section">
                <div class="bk-section-head">
                    <div class="bk-section-icon"><i class="bx bx-credit-card-front"></i></div>
                    <div>
                        <div class="bk-section-title">Dados Bancários</div>
                        <div class="bk-section-desc">Agência, número da conta e chave PIX</div>
                    </div>
                </div>

                <div class="bk-grid">
                    <div class="bk-col-3 bk-field">
                        <label for="agencia">Agência</label>
                        <input type="text" id="agencia" name="agencia" maxlength="10" placeholder="0000"
                            value="<?php echo $isEdit ? htmlspecialchars($banco['agencia'] ?? '') : ''; ?>"
                            class="bk-input">
                    </div>

                    <div class="bk-col-3 bk-field">
                        <label for="agencia_dv">Dígito Ag.</label>
                        <input type="text" id="agencia_dv" name="agencia_dv" maxlength="2" placeholder="0"
                            value="<?php echo $isEdit ? htmlspecialchars($banco['agencia_dv'] ?? '') : ''; ?>"
                            class="bk-input">
                    </div>

                    <div class="bk-col-3 bk-field">
                        <label for="conta">Número da Conta</label>
                        <input type="text" id="conta" name="conta" maxlength="20" placeholder="00000-0"
                            value="<?php echo $isEdit ? htmlspecialchars($banco['conta'] ?? '') : ''; ?>"
                            class="bk-input">
                    </div>

                    <div class="bk-col-3 bk-field">
                        <label for="conta_dv">Dígito Cta.</label>
                        <input type="text" id="conta_dv" name="conta_dv" maxlength="2" placeholder="0"
                            value="<?php echo $isEdit ? htmlspecialchars($banco['conta_dv'] ?? '') : ''; ?>"
                            class="bk-input">
                    </div>

                    <div class="bk-col-4 bk-field">
                        <label for="pix_tipo">Tipo de Chave PIX<span class="opt">(opcional)</span></label>
                        <select id="pix_tipo" name="pix_tipo" class="bk-select">
                            <?php
                            $pixOptions = ['' => 'Nenhuma', 'cpf_cnpj' => 'CPF/CNPJ', 'email' => 'E-mail', 'telefone' => 'Telefone', 'aleatoria' => 'Chave Aleatória'];
                            $pixAtual = $isEdit ? ($banco['pix_tipo'] ?? '') : '';
                            foreach ($pixOptions as $valor => $rotulo):
                            ?>
                                <option value="<?php echo $valor; ?>" <?php echo ($pixAtual === $valor) ? 'selected' : ''; ?>><?php echo $rotulo; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="bk-col-8 bk-field">
                        <label for="pix_chave">Chave PIX</label>
                        <input type="text" id="pix_chave" name="pix_chave" maxlength="140" placeholder="Chave PIX correspondente ao tipo selecionado"
                            value="<?php echo $isEdit ? htmlspecialchars($banco['pix_chave'] ?? '') : ''; ?>"
                            class="bk-input">
                    </div>
                </div>
            </div>

            <!-- ===== Seção 3: Saldo e Limites ===== -->
            <div class="bk-section">
                <div class="bk-section-head">
                    <div class="bk-section-icon"><i class="bx bx-wallet"></i></div>
                    <div>
                        <div class="bk-section-title">Saldo e Limites</div>
                        <div class="bk-section-desc">Valores que entram no fluxo de caixa do sistema</div>
                    </div>
                </div>

                <div class="bk-grid">
                    <div class="bk-col-6 bk-field">
                        <label for="saldo_inicial">Saldo Inicial<span class="req">*</span></label>
                        <div class="bk-input-prefix">
                            <span>R$</span>
                            <input type="number" id="saldo_inicial" name="saldo_inicial" required step="0.01"
                                value="<?php echo $isEdit ? htmlspecialchars($banco['saldo_inicial']) : '0.00'; ?>"
                                class="bk-input">
                        </div>
                        <p class="bk-help">Saldo da conta na data de início de uso no sistema.</p>
                    </div>

                    <div class="bk-col-6 bk-field">
                        <label for="limite_credito">Limite de Crédito/Cheque Especial<span class="opt">(opcional)</span></label>
                        <div class="bk-input-prefix">
                            <span>R$</span>
                            <input type="number" id="limite_credito" name="limite_credito" step="0.01"
                                value="<?php echo $isEdit ? htmlspecialchars($banco['limite_credito'] ?? '0.00') : '0.00'; ?>"
                                class="bk-input">
                        </div>
                    </div>
                </div>

                <div class="bk-balance-preview" id="saldoPreview">
                    <div>
                        <div class="lbl">Saldo disponível total estimado</div>
                        <div class="val" id="saldoPreviewValor">R$ 0,00</div>
                    </div>
                    <i class="bx bx-trending-up" style="font-size:1.8rem; color:#34d399;"></i>
                </div>
            </div>

            <!-- ===== Seção 4: Configurações ===== -->
            <div class="bk-section">
                <div class="bk-section-head">
                    <div class="bk-section-icon"><i class="bx bx-cog"></i></div>
                    <div>
                        <div class="bk-section-title">Configurações</div>
                        <div class="bk-section-desc">Cor de identificação, logo do banco e status da conta</div>
                    </div>
                </div>

                <div class="bk-grid">
                    <div class="bk-col-6 bk-field">
                        <label>Cor de Identificação</label>
                        <div class="bk-color-swatches">
                            <?php
                            $cores = ['#10b981' => 'Verde', '#3b82f6' => 'Azul', '#f59e0b' => 'Âmbar', '#ef4444' => 'Vermelho', '#a855f7' => 'Roxo', '#06b6d4' => 'Ciano', '#ec4899' => 'Rosa', '#64748b' => 'Cinza'];
                            $corAtual = $isEdit ? ($banco['cor'] ?? '#10b981') : '#10b981';
                            $i = 0;
                            foreach ($cores as $hex => $nomeCor):
                                $i++;
                            ?>
                                <input type="radio" id="cor_<?php echo $i; ?>" name="cor" value="<?php echo $hex; ?>"
                                    <?php echo ($corAtual === $hex) ? 'checked' : ''; ?>>
                                <label for="cor_<?php echo $i; ?>" style="background:<?php echo $hex; ?>; color:<?php echo $hex; ?>" title="<?php echo $nomeCor; ?>"></label>
                            <?php endforeach; ?>
                        </div>
                        <p class="bk-help">Usada para destacar esta conta em gráficos e listagens.</p>
                    </div>

                    <div class="bk-col-6 bk-field">
                        <label for="logo">Logo do Banco<span class="opt">(opcional)</span></label>
                        <?php if ($isEdit && !empty($banco['logo'])): ?>
                            <div style="margin-bottom: 0.75rem; padding: 0.75rem; background: var(--bk-panel-2); border: 1px solid var(--bk-border); border-radius: 8px; display: flex; align-items: center; gap: 0.75rem;">
                                <img src="<?php echo BASE_URL; ?>/public/uploads/bancos/<?php echo htmlspecialchars($banco['logo']); ?>" 
                                    alt="Logo atual" style="max-width: 50px; max-height: 50px; object-fit: contain;">
                                <div style="font-size: 0.78rem; color: var(--bk-text-faint);">
                                    Logo atual: <strong><?php echo htmlspecialchars($banco['logo']); ?></strong>
                                </div>
                            </div>
                        <?php endif; ?>
                        <input type="file" id="logo" name="logo" accept="image/png,image/jpeg,image/gif,image/webp" 
                            class="bk-input" style="padding: 0.5rem; cursor: pointer;">
                        <p class="bk-help">Formatos aceitos: PNG, JPG, GIF, WebP. Máx 5MB. Se deixar em branco, mantém a logo atual (em edição).</p>
                    </div>

                    <div class="bk-col-12 bk-field">
                        <label>Status</label>
                        <div class="bk-switch-row">
                            <div class="label-block">
                                <strong>Conta ativa</strong>
                                <span>Contas inativas não aparecem para lançamentos novos</span>
                            </div>
                            <label class="bk-switch">
                                <input type="checkbox" name="ativo" value="1"
                                    <?php echo (!$isEdit || ($banco['ativo'] ?? 1)) ? 'checked' : ''; ?>>
                                <span class="slider"></span>
                            </label>
                        </div>
                    </div>

                    <div class="bk-col-12 bk-field">
                        <label for="observacoes">Observações<span class="opt">(opcional)</span></label>
                        <textarea id="observacoes" name="observacoes" maxlength="500"
                            placeholder="Informações adicionais sobre esta conta, uso interno, restrições etc."
                            class="bk-textarea"><?php echo $isEdit ? htmlspecialchars($banco['observacoes'] ?? '') : ''; ?></textarea>
                    </div>
                </div>
            </div>

            <!-- ===== Footer ===== -->
            <div class="bk-footer">
                <span class="bk-footer-hint"><span class="req" style="color:#f87171">*</span> Campos obrigatórios</span>
                <div class="bk-actions-right">
                    <a href="<?php echo BASE_URL; ?>/banco" class="bk-btn bk-btn-ghost">
                        <i class="bx bx-x"></i> Cancelar
                    </a>
                    <button type="submit" class="bk-btn bk-btn-primary">
                        <i class="bx bx-check"></i>
                        <?php echo $isEdit ? 'Salvar Alterações' : 'Cadastrar Banco'; ?>
                    </button>
                </div>
            </div>

        </form>
    </div>
</div>

<script>
(function() {
    const saldoInicial = document.getElementById('saldo_inicial');
    const limiteCredito = document.getElementById('limite_credito');
    const previewValor = document.getElementById('saldoPreviewValor');

    function formatarMoeda(valor) {
        return valor.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
    }

    function atualizarPreview() {
        const saldo = parseFloat(saldoInicial.value) || 0;
        const limite = parseFloat(limiteCredito.value) || 0;
        previewValor.textContent = formatarMoeda(saldo + limite);
    }

    if (saldoInicial && limiteCredito && previewValor) {
        saldoInicial.addEventListener('input', atualizarPreview);
        limiteCredito.addEventListener('input', atualizarPreview);
        atualizarPreview();
    }

    // Máscara simples de agência/conta (somente números e traço/x)
    document.querySelectorAll('#agencia, #conta').forEach(function(el) {
        el.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    });
    document.querySelectorAll('#agencia_dv, #conta_dv').forEach(function(el) {
        el.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9xX]/g, '');
        });
    });
})();
</script>
