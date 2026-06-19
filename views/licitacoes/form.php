<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500&family=Sora:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.x/dist/tabler-icons.min.css">

<style>
:root {
    --bg:       #f4f5f7;
    --surface:  #ffffff;
    --border:   #e3e6eb;
    --border-strong: #c9cdd6;
    --text:     #111827;
    --muted:    #6b7280;
    --faint:    #9ca3af;
    --accent:   #1d5fcc;
    --accent-bg:#e8f0fd;
    --accent-hover:#1749a8;
    --green:    #16a34a;
    --green-bg: #f0fdf4;
    --amber:    #b45309;
    --amber-bg: #fffbeb;
    --red:      #dc2626;
    --font:     'Sora', sans-serif;
    --mono:     'IBM Plex Mono', monospace;
    --r:        10px;
    --r-sm:     6px;
    --shadow-sm: 0 1px 3px rgba(0,0,0,.07), 0 1px 2px rgba(0,0,0,.04);
    --shadow:   0 4px 12px rgba(0,0,0,.07), 0 1px 3px rgba(0,0,0,.04);
}

body.dark-theme {
    --bg:       #0d1117;
    --surface:  #161b22;
    --border:   #30363d;
    --border-strong: #484f58;
    --text:     #e6edf3;
    --muted:    #8b949e;
    --faint:    #6e7681;
    --accent:   #58a6ff;
    --accent-bg:#1f3a5f;
    --accent-hover:#79b8ff;
    --green:    #3fb950;
    --green-bg: #0d2318;
    --amber:    #d29922;
    --amber-bg: #2d1f09;
    --shadow-sm: 0 1px 3px rgba(0,0,0,.3);
    --shadow:   0 4px 12px rgba(0,0,0,.3);
}

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

.lic-wrap {
    background: var(--bg);
    font-family: var(--font);
    color: var(--text);
    min-height: 100vh;
}

/* ── Topbar ── */
.lic-topbar {
    position: sticky;
    top: 0;
    z-index: 100;
    background: var(--surface);
    border-bottom: 1px solid var(--border);
    padding: 0 32px;
    height: 54px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    box-shadow: var(--shadow-sm);
}
.lic-topbar-left {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
}
.lic-topbar-left .dot {
    width: 7px; height: 7px;
    background: var(--green);
    border-radius: 50%;
    flex-shrink: 0;
}
.lic-topbar-left .sep { color: var(--border-strong); }
.lic-topbar-left .module { color: var(--muted); }
.lic-topbar-left .title { font-weight: 600; color: var(--text); }
.lic-topbar-right { display: flex; gap: 8px; align-items: center; }

/* ── Botões ── */
.btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 7px 16px;
    border-radius: var(--r-sm);
    font-family: var(--font);
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    transition: all .15s ease;
    border: 1px solid transparent;
    text-decoration: none;
    white-space: nowrap;
}
.btn i { font-size: 15px; }
.btn-ghost {
    background: transparent;
    border-color: var(--border);
    color: var(--muted);
}
.btn-ghost:hover { background: var(--bg); color: var(--text); border-color: var(--border-strong); }
.btn-outline {
    background: transparent;
    border-color: var(--border);
    color: var(--text);
}
.btn-outline:hover { background: var(--bg); }
.btn-primary {
    background: var(--accent);
    border-color: var(--accent);
    color: #fff;
    box-shadow: 0 1px 3px rgba(29,95,204,.25);
}
.btn-primary:hover { background: var(--accent-hover); border-color: var(--accent-hover); transform: translateY(-1px); box-shadow: 0 3px 8px rgba(29,95,204,.3); }
.btn-save-draft { background: transparent; border-color: var(--border); color: var(--muted); }
.btn-save-draft:hover { background: var(--bg); color: var(--text); }

/* ── Page layout ── */
.lic-page { max-width: 1080px; margin: 0 auto; padding: 28px 32px 60px; }

.lic-header { margin-bottom: 24px; }
.lic-breadcrumb {
    display: flex; align-items: center; gap: 5px;
    font-size: 12px; color: var(--muted);
    margin-bottom: 10px;
}
.lic-breadcrumb i { font-size: 12px; }
.lic-breadcrumb a { color: var(--muted); text-decoration: none; }
.lic-breadcrumb a:hover { color: var(--accent); }
.lic-headline { font-size: 21px; font-weight: 600; color: var(--text); letter-spacing: -.3px; }
.lic-sub { font-size: 13px; color: var(--muted); margin-top: 3px; }

.lic-layout { display: grid; grid-template-columns: 1fr 308px; gap: 20px; align-items: start; }

/* ── Cards ── */
.lic-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--r);
    box-shadow: var(--shadow-sm);
    overflow: hidden;
    margin-bottom: 16px;
}
.lic-card:last-child { margin-bottom: 0; }
.lic-card-header {
    padding: 14px 20px;
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    gap: 10px;
}
.lic-card-icon {
    width: 32px; height: 32px;
    border-radius: var(--r-sm);
    display: flex; align-items: center; justify-content: center;
    font-size: 16px;
    flex-shrink: 0;
}
.icon-blue  { background: var(--accent-bg); color: var(--accent); }
.icon-green { background: var(--green-bg); color: var(--green); }
.icon-amber { background: var(--amber-bg); color: var(--amber); }
.icon-gray  { background: var(--bg); color: var(--muted); }

.lic-card-header-text h3 {
    font-size: 13px; font-weight: 600; color: var(--text);
}
.lic-card-header-text p {
    font-size: 12px; color: var(--muted);
}
.lic-card-body { padding: 20px; }

/* ── Formulário ── */
.f-row { display: grid; gap: 14px; margin-bottom: 14px; }
.f-row:last-child { margin-bottom: 0; }
.f-2 { grid-template-columns: 1fr 1fr; }
.f-3 { grid-template-columns: 1fr 1fr 1fr; }
.f-1 { grid-template-columns: 1fr; }

.f-field {}
.f-field label {
    display: block;
    font-size: 11px;
    font-weight: 600;
    color: var(--muted);
    text-transform: uppercase;
    letter-spacing: .06em;
    margin-bottom: 6px;
}
.f-field label .req { color: var(--red); margin-left: 2px; }

.f-field input,
.f-field select,
.f-field textarea {
    width: 100%;
    background: var(--bg);
    border: 1px solid var(--border);
    border-radius: var(--r-sm);
    color: var(--text);
    font-family: var(--font);
    font-size: 13px;
    padding: 9px 12px;
    outline: none;
    transition: border-color .15s, box-shadow .15s;
    -webkit-appearance: none;
}
.f-field input::placeholder,
.f-field textarea::placeholder { color: var(--faint); }
.f-field input:focus,
.f-field select:focus,
.f-field textarea:focus {
    border-color: var(--accent);
    box-shadow: 0 0 0 3px rgba(29,95,204,.12);
    background: var(--surface);
}
.f-field textarea { resize: vertical; line-height: 1.6; }
.f-field .hint { font-size: 11px; color: var(--faint); margin-top: 5px; }
.f-mono { font-family: var(--mono) !important; }

/* ── Status selector ── */
.status-opts { display: flex; flex-direction: column; gap: 8px; }
.status-opt {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 12px;
    border: 1px solid var(--border);
    border-radius: var(--r-sm);
    cursor: pointer;
    transition: all .15s;
}
.status-opt:hover { background: var(--bg); border-color: var(--border-strong); }
.status-opt.active { border-color: var(--accent); background: var(--accent-bg); }
.status-opt .radio {
    width: 16px; height: 16px;
    border: 1.5px solid var(--border-strong);
    border-radius: 50%;
    flex-shrink: 0;
    transition: all .15s;
}
.status-opt.active .radio {
    border-color: var(--accent);
    background: var(--accent);
    box-shadow: inset 0 0 0 3px var(--accent-bg);
}
.status-opt .s-label { font-size: 13px; font-weight: 500; color: var(--text); }
.status-opt.active .s-label { color: var(--accent); }
.status-opt .s-sub { font-size: 11px; color: var(--muted); margin-top: 1px; }

/* ── Divider ── */
.divider { height: 1px; background: var(--border); margin: 18px 0; }

/* ── Tags ── */
.sec-label {
    font-size: 11px; font-weight: 600;
    color: var(--muted); text-transform: uppercase; letter-spacing: .06em;
    margin-bottom: 10px;
}
.tags { display: flex; flex-wrap: wrap; gap: 6px; }
.tag {
    padding: 4px 12px;
    border: 1px solid var(--border);
    border-radius: 20px;
    font-size: 12px;
    color: var(--muted);
    cursor: pointer;
    transition: all .15s;
    font-family: var(--font);
    background: transparent;
}
.tag:hover { border-color: var(--border-strong); color: var(--text); }
.tag.on { background: var(--accent-bg); border-color: var(--accent); color: var(--accent); font-weight: 500; }

/* ── Valor display ── */
.valor-big {
    font-family: var(--mono);
    font-size: 22px;
    font-weight: 500;
    color: var(--faint);
    margin-top: 8px;
    transition: color .2s;
}
.valor-big.has-val { color: var(--text); }

/* ── Summary box ── */
.summary-box {
    background: var(--bg);
    border: 1px solid var(--border);
    border-radius: var(--r-sm);
    padding: 14px;
}
.s-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 5px 0;
}
.s-row + .s-row { border-top: 1px solid var(--border); }
.s-key { font-size: 12px; color: var(--muted); }
.s-val { font-size: 12px; font-weight: 500; color: var(--text); }
.s-val.accent { color: var(--accent); }
.s-val.green  { color: var(--green); font-family: var(--mono); }

/* ── Footer actions ── */
.lic-footer {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--r);
    margin-top: 20px;
    padding: 14px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    box-shadow: var(--shadow-sm);
}
.lic-footer-left { font-size: 12px; color: var(--muted); display: flex; align-items: center; gap: 6px; }
.lic-footer-left i { font-size: 14px; }
.lic-footer-right { display: flex; gap: 8px; }

/* ── Responsive ── */
@media (max-width: 860px) {
    .lic-layout { grid-template-columns: 1fr; }
    .lic-page { padding: 20px 16px 48px; }
    .lic-topbar { padding: 0 16px; }
    .f-2, .f-3 { grid-template-columns: 1fr; }
}
</style>

<div class="lic-wrap">

    <!-- Topbar -->
    <div class="lic-topbar">
        <div class="lic-topbar-left">
            <div class="dot"></div>
            <span class="module">Licitações</span>
            <span class="sep">/</span>
            <span class="title">
                <?= isset($lic['id']) ? 'Editar Protocolo #' . htmlspecialchars($lic['numero'] ?? $lic['id']) : 'Novo Protocolo' ?>
            </span>
        </div>
        <div class="lic-topbar-right">
            <a href="<?= BASE_URL ?>/licitacoes" class="btn btn-ghost">
                <i class="ti ti-arrow-left"></i> Voltar
            </a>
        </div>
    </div>

    <div class="lic-page">

        <!-- Header -->
        <div class="lic-header">
            <div class="lic-breadcrumb">
                <a href="<?= BASE_URL ?>"><i class="ti ti-home-2"></i></a>
                <i class="ti ti-chevron-right"></i>
                <a href="<?= BASE_URL ?>/licitacoes">Licitações</a>
                <i class="ti ti-chevron-right"></i>
                <span><?= isset($lic['id']) ? 'Editar' : 'Novo registro' ?></span>
            </div>
            <div class="lic-headline">
                <?= isset($lic['id']) ? 'Editar Protocolo' : 'Cadastro de Licitação' ?>
            </div>
            <div class="lic-sub">Preencha os dados do edital e anexe o arquivo PDF para <?= isset($lic['id']) ? 'atualizar o protocolo no' : 'criar um novo protocolo no' ?> sistema.</div>
        </div>

        <form action="<?= BASE_URL ?>/licitacoes/salvar" method="POST" id="formLicitacao" enctype="multipart/form-data">
            <?php if (isset($lic['id'])): ?>
                <input type="hidden" name="id" value="<?= htmlspecialchars($lic['id']) ?>">
            <?php endif; ?>
            <input type="hidden" name="status" id="status_input" value="<?= htmlspecialchars($lic['status'] ?? 'rascunho') ?>">
            <input type="hidden" name="valor_estimado" id="valor_estimado_db">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            <input type="hidden" name="categorias" id="categorias_input" value="<?= htmlspecialchars($lic['categorias'] ?? '') ?>">

            <div class="lic-layout">

                <!-- Coluna principal -->
                <div>

                    <!-- Card: Dados do Edital -->
                    <div class="lic-card">
                        <div class="lic-card-header">
                            <div class="lic-card-icon icon-blue"><i class="ti ti-file-description"></i></div>
                            <div class="lic-card-header-text">
                                <h3>Dados do Edital</h3>
                                <p>Informações principais do processo licitatório</p>
                            </div>
                        </div>
                        <div class="lic-card-body">
                            <div class="f-row f-3">
                                <div class="f-field">
                                    <label>Número do Protocolo <span class="req">*</span></label>
                                    <input type="text" name="numero" class="f-mono" placeholder="Ex: PE-001/2026"
                                        value="<?= htmlspecialchars($lic['numero'] ?? '') ?>" required>
                                </div>
                                <div class="f-field">
                                    <label>Arquivo do Edital (PDF)</label>
                                    <input type="file" name="edital_arquivo" accept="application/pdf">
                                    <div class="hint">Tamanho máximo permitido: 5MB.</div>
                                    <?php if (!empty($lic['edital_path'])): ?>
                                        <div class="hint">
                                            <i class="ti ti-file-check"></i> <a href="<?= BASE_URL ?>/storage/licitacoes/<?= $lic['edital_path'] ?>" target="_blank" style="color: var(--accent); font-weight: 500;">Ver edital atual</a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="f-field">
                                    <label>Modalidade <span class="req">*</span></label>
                                    <select name="modalidade" required>
                                        <option value="" disabled <?= !isset($lic['modalidade']) ? 'selected' : '' ?>>Selecione...</option>
                                        <option value="pregao_eletronico"  <?= ($lic['modalidade'] ?? '') == 'pregao_eletronico'  ? 'selected' : '' ?>>Pregão Eletrônico</option>
                                        <option value="pregao_presencial"  <?= ($lic['modalidade'] ?? '') == 'pregao_presencial'  ? 'selected' : '' ?>>Pregão Presencial</option>
                                        <option value="concorrencia"       <?= ($lic['modalidade'] ?? '') == 'concorrencia'       ? 'selected' : '' ?>>Concorrência</option>
                                        <option value="tomada_precos"      <?= ($lic['modalidade'] ?? '') == 'tomada_precos'      ? 'selected' : '' ?>>Tomada de Preços</option>
                                        <option value="convite"            <?= ($lic['modalidade'] ?? '') == 'convite'            ? 'selected' : '' ?>>Convite</option>
                                        <option value="dispensa"           <?= ($lic['modalidade'] ?? '') == 'dispensa'           ? 'selected' : '' ?>>Dispensa</option>
                                        <option value="inexigibilidade"    <?= ($lic['modalidade'] ?? '') == 'inexigibilidade'    ? 'selected' : '' ?>>Inexigibilidade</option>
                                    </select>
                                </div>
                            </div>
                            <div class="f-row f-1">
                                <div class="f-field">
                                    <label>Órgão Público Contratante <span class="req">*</span></label>
                                    <input type="text" name="orgao" placeholder="Ex: Secretaria Municipal de Saúde"
                                        value="<?= htmlspecialchars($lic['orgao'] ?? '') ?>" required>
                                </div>
                            </div>
                            <div class="f-row f-1">
                                <div class="f-field">
                                    <label>Objeto da Licitação <span class="req">*</span></label>
                                    <textarea name="objeto" rows="4" placeholder="Descreva de forma clara e objetiva o item ou serviço a ser contratado..." required><?= htmlspecialchars($lic['objeto'] ?? '') ?></textarea>
                                    <div class="hint">Mínimo 50 caracteres. Descreva o escopo completo do objeto licitado.</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card: Responsáveis -->
                    <div class="lic-card">
                        <div class="lic-card-header">
                            <div class="lic-card-icon icon-green"><i class="ti ti-users"></i></div>
                            <div class="lic-card-header-text">
                                <h3>Responsáveis</h3>
                                <p>Equipe técnica envolvida no processo</p>
                            </div>
                        </div>
                        <div class="lic-card-body">
                            <div class="f-row f-2">
                                <div class="f-field">
                                    <label>Responsável Técnico <span class="req">*</span></label>
                                    <input type="text" name="responsavel" placeholder="Nome completo"
                                        value="<?= htmlspecialchars($lic['responsavel'] ?? '') ?>" required>
                                </div>
                                <div class="f-field">
                                    <label>Setor / Área</label>
                                    <input type="text" name="setor" placeholder="Ex: Departamento de Compras"
                                        value="<?= htmlspecialchars($lic['setor'] ?? '') ?>">
                                </div>
                            </div>
                            <div class="f-row f-2">
                                <div class="f-field">
                                    <label>E-mail de Contato</label>
                                    <input type="email" name="email_contato" placeholder="email@orgao.gov.br"
                                        value="<?= htmlspecialchars($lic['email_contato'] ?? '') ?>">
                                </div>
                                <div class="f-field">
                                    <label>Ramal / Telefone</label>
                                    <input type="text" name="telefone" placeholder="(00) 00000-0000"
                                        value="<?= htmlspecialchars($lic['telefone'] ?? '') ?>">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card: Cronograma -->
                    <div class="lic-card">
                        <div class="lic-card-header">
                            <div class="lic-card-icon icon-amber"><i class="ti ti-calendar-event"></i></div>
                            <div class="lic-card-header-text">
                                <h3>Cronograma</h3>
                                <p>Datas e prazos do processo licitatório</p>
                            </div>
                        </div>
                        <div class="lic-card-body">
                            <div class="f-row f-3">
                                <div class="f-field">
                                    <label>Abertura do Edital</label>
                                    <input type="date" name="dt_abertura" value="<?= htmlspecialchars($lic['dt_abertura'] ?? '') ?>">
                                </div>
                                <div class="f-field">
                                    <label>Data da Sessão <span class="req">*</span></label>
                                    <input type="date" name="dt_sessao" value="<?= htmlspecialchars($lic['dt_sessao'] ?? '') ?>" required>
                                </div>
                                <div class="f-field">
                                    <label>Prazo de Entrega</label>
                                    <input type="date" name="dt_entrega" value="<?= htmlspecialchars($lic['dt_entrega'] ?? '') ?>">
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Coluna lateral -->
                <div>

                    <!-- Card: Status -->
                    <div class="lic-card">
                        <div class="lic-card-header">
                            <div class="lic-card-icon icon-gray"><i class="ti ti-adjustments-horizontal"></i></div>
                            <div class="lic-card-header-text">
                                <h3>Status do Processo</h3>
                            </div>
                        </div>
                        <div class="lic-card-body">
                            <div class="status-opts" id="statusOpts">
                                <div class="status-opt <?= ($lic['status'] ?? 'rascunho') == 'rascunho' ? 'active' : '' ?>" data-val="rascunho" onclick="setStatus(this)">
                                    <div class="radio"></div>
                                    <div>
                                        <div class="s-label">Rascunho</div>
                                        <div class="s-sub">Processo em elaboração interna</div>
                                    </div>
                                </div>
                                <div class="status-opt <?= ($lic['status'] ?? '') == 'publicada' ? 'active' : '' ?>" data-val="publicada" onclick="setStatus(this)">
                                    <div class="radio"></div>
                                    <div>
                                        <div class="s-label">Publicada</div>
                                        <div class="s-sub">Edital disponível ao público</div>
                                    </div>
                                </div>
                                <div class="status-opt <?= ($lic['status'] ?? '') == 'aberta' ? 'active' : '' ?>" data-val="aberta" onclick="setStatus(this)">
                                    <div class="radio"></div>
                                    <div>
                                        <div class="s-label">Aberta</div>
                                        <div class="s-sub">Recebendo propostas</div>
                                    </div>
                                </div>
                                <div class="status-opt <?= ($lic['status'] ?? '') == 'homologada' ? 'active' : '' ?>" data-val="homologada" onclick="setStatus(this)">
                                    <div class="radio"></div>
                                    <div>
                                        <div class="s-label">Homologada</div>
                                        <div class="s-sub">Resultado homologado</div>
                                    </div>
                                </div>
                                <div class="status-opt <?= ($lic['status'] ?? '') == 'concluida' ? 'active' : '' ?>" data-val="concluida" onclick="setStatus(this)">
                                    <div class="radio"></div>
                                    <div>
                                        <div class="s-label">Concluída</div>
                                        <div class="s-sub">Processo finalizado e encerrado</div>
                                    </div>
                                </div>
                                <div class="status-opt <?= ($lic['status'] ?? '') == 'suspensa' ? 'active' : '' ?>" data-val="suspensa" onclick="setStatus(this)">
                                    <div class="radio"></div>
                                    <div>
                                        <div class="s-label">Suspensa</div>
                                        <div class="s-sub">Processo paralisado temporariamente</div>
                                    </div>
                                </div>
                                <div class="status-opt <?= ($lic['status'] ?? '') == 'cancelada' ? 'active' : '' ?>" data-val="cancelada" onclick="setStatus(this)">
                                    <div class="radio"></div>
                                    <div>
                                        <div class="s-label">Cancelada</div>
                                        <div class="s-sub">Processo anulado ou revogado</div>
                                    </div>
                                </div>
                            </div>

                            <div class="divider"></div>

                            <div class="sec-label">Valor Estimado (R$)</div>
                            <div class="f-field">
                                <input type="text" id="valor_visual" class="f-mono"
                                    placeholder="0,00"
                                    value="<?= isset($lic['valor_estimado']) ? number_format($lic['valor_estimado'], 2, ',', '.') : '' ?>"
                                    oninput="formatarMoeda(this)">
                            </div>
                            <div class="valor-big <?= isset($lic['valor_estimado']) ? 'has-val' : '' ?>" id="valor_display">
                                <?= isset($lic['valor_estimado']) ? 'R$ ' . number_format($lic['valor_estimado'], 2, ',', '.') : 'R$ —' ?>
                            </div>
                        </div>
                    </div>

                    <!-- Card: Classificação -->
                    <div class="lic-card">
                        <div class="lic-card-header">
                            <div class="lic-card-icon icon-gray"><i class="ti ti-tag"></i></div>
                            <div class="lic-card-header-text">
                                <h3>Classificação</h3>
                            </div>
                        </div>
                        <div class="lic-card-body">
                            <div class="sec-label">Categoria</div>
                            <div class="tags" id="tags">
                                <?php
                                $cats = ['Obras','Serviços','Materiais','TI','Saúde','Educação','Infraestrutura','Transporte'];
                                $ativas = isset($lic['categorias']) ? explode(',', $lic['categorias']) : [];
                                foreach($cats as $c):
                                    $on = in_array($c, $ativas) ? 'on' : '';
                                ?>
                                <button type="button" class="tag <?= $on ?>" onclick="toggleTag(this)"><?= $c ?></button>
                                <?php endforeach; ?>
                            </div>

                            <div class="divider"></div>

                            <div class="f-field">
                                <label>Nível de Sigilo</label>
                                <select name="sigilo">
                                    <option value="publico"      <?= ($lic['sigilo'] ?? '') == 'publico'      ? 'selected' : '' ?>>Público</option>
                                    <option value="restrito"     <?= ($lic['sigilo'] ?? '') == 'restrito'     ? 'selected' : '' ?>>Restrito</option>
                                    <option value="confidencial" <?= ($lic['sigilo'] ?? '') == 'confidencial' ? 'selected' : '' ?>>Confidencial</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Card: Resumo -->
                    <div class="lic-card">
                        <div class="lic-card-header">
                            <div class="lic-card-icon icon-gray"><i class="ti ti-info-circle"></i></div>
                            <div class="lic-card-header-text">
                                <h3>Resumo do Registro</h3>
                            </div>
                        </div>
                        <div class="lic-card-body">
                            <div class="summary-box">
                                <div class="s-row">
                                    <span class="s-key">Status</span>
                                    <span class="s-val accent" id="sum_status">
                                        <?= ucfirst($lic['status'] ?? 'Rascunho') ?>
                                    </span>
                                </div>
                                <div class="s-row">
                                    <span class="s-key">Data da sessão</span>
                                    <span class="s-val" id="sum_sessao">
                                        <?= isset($lic['dt_sessao']) ? date('d/m/Y', strtotime($lic['dt_sessao'])) : '—' ?>
                                    </span>
                                </div>
                                <div class="s-row">
                                    <span class="s-key" style="font-weight:600">Valor estimado</span>
                                    <span class="s-val green" id="sum_valor">
                                        <?= isset($lic['valor_estimado']) ? 'R$ ' . number_format($lic['valor_estimado'], 2, ',', '.') : 'R$ 0,00' ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Footer de ações -->
            <div class="lic-footer">
                <div class="lic-footer-left">
                    <i class="ti ti-clock"></i>
                    <?= isset($lic['updated_at']) ? 'Atualizado em ' . date('d/m/Y \à\s H:i', strtotime($lic['updated_at'])) : 'Ainda não salvo' ?>
                </div>
                <div class="lic-footer-right">
                    <a href="<?= BASE_URL ?>/licitacoes" class="btn btn-ghost">Cancelar</a>
                    <?php if (isset($lic['id'])): ?>
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-refresh"></i> Atualizar Dados
                        </button>
                    <?php else: ?>
                        <button type="submit" name="acao" value="rascunho" class="btn btn-outline">
                            <i class="ti ti-device-floppy"></i> Salvar rascunho
                        </button>
                        <button type="submit" name="acao" value="publicar" class="btn btn-primary">
                            <i class="ti ti-send"></i> Publicar protocolo
                        </button>
                    <?php endif; ?>
                </div>
            </div>

        </form>
    </div>
</div>

<script>
function setStatus(el) {
    document.querySelectorAll('.status-opt').forEach(o => o.classList.remove('active'));
    el.classList.add('active');
    const val = el.dataset.val;
    document.getElementById('status_input').value = val;
    document.getElementById('sum_status').textContent = val.charAt(0).toUpperCase() + val.slice(1);
    
    // Muda a cor da borda do resumo conforme o status
    const summary = document.querySelector('.summary-box');
    summary.style.borderLeft = '4px solid transparent';
    if(val === 'concluida') summary.style.borderLeftColor = 'var(--green)';
    else if(val === 'suspensa') summary.style.borderLeftColor = 'var(--amber)';
    else if(val === 'cancelada') summary.style.borderLeftColor = 'var(--red)';
}

function toggleTag(el) {
    el.classList.toggle('on');
    const on = [...document.querySelectorAll('#tags .tag.on')].map(t => t.textContent);
    document.getElementById('categorias_input').value = on.join(',');
}

function formatarMoeda(i) {
    let v = i.value.replace(/\D/g, '');
    if (!v) {
        document.getElementById('valor_display').textContent = 'R$ —';
        document.getElementById('valor_display').classList.remove('has-val');
        document.getElementById('valor_estimado_db').value = '';
        document.getElementById('sum_valor').textContent = 'R$ 0,00';
        return;
    }
    let num = (parseInt(v) / 100).toFixed(2);
    let fmt = num.replace('.', ',').replace(/(\d)(?=(\d{3})+(?=,))/g, '$1.');
    i.value = fmt;
    let display = 'R$ ' + fmt;
    document.getElementById('valor_display').textContent = display;
    document.getElementById('valor_display').classList.add('has-val');
    document.getElementById('valor_estimado_db').value = num;
    document.getElementById('sum_valor').textContent = display;
}

document.addEventListener('DOMContentLoaded', function () {
    const vInput = document.getElementById('valor_visual');
    if (vInput && vInput.value !== '') {
        formatarMoeda(vInput);
    }
    const dtSessao = document.querySelector('[name="dt_sessao"]');
    if (dtSessao) {
        dtSessao.addEventListener('change', function () {
            const el = document.getElementById('sum_sessao');
            if (this.value) {
                const [y, m, d] = this.value.split('-');
                el.textContent = d + '/' + m + '/' + y;
            } else {
                el.textContent = '—';
            }
        });
    }
});
</script>
