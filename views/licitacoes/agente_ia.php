<style>
    :root {
        --bg-base:      #f0f4fa;
        --bg-surface:   #ffffff;
        --bg-elevated:  #f8fafc;
        --border-soft:  rgba(0,0,0,0.06);
        --border-accent:rgba(37,99,235,0.2);
        --text-primary:  #1e293b;
        --text-secondary:#475569;
        --text-tertiary: #94a3b8;
        --accent:        #2563eb;
        --accent-dim:    rgba(37,99,235,0.05);
        --purple:        #8b5cf6;
        --purple-bg:     rgba(139,92,246,0.1);
        --green:         #10b981;
        --amber:         #f59e0b;
        --radius-md:     10px;
        --radius-lg:     14px;
        --font-ui:      'Sora', sans-serif;
        --font-mono:    'JetBrains Mono', monospace;
        --transition:   all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    }

    body.dark-theme {
        --bg-base:      #0b0f1a;
        --bg-surface:   #111827;
        --bg-elevated:  #1a2235;
        --border-soft:  rgba(255,255,255,0.06);
        --border-accent:rgba(56,189,248,0.35);
        --text-primary:  #f0f6ff;
        --text-secondary:#8fa3c0;
        --text-tertiary: #4d6282;
        --accent:        #38bdf8;
        --accent-dim:    rgba(56,189,248,0.08);
        --purple:        #a78bfa;
        --purple-bg:     rgba(167,139,250,0.1);
        --green:         #34d399;
        --amber:         #fbbf24;
    }

    body { background: var(--bg-base); color: var(--text-primary); font-family: var(--font-ui); }

    .lic-container { padding: 32px; max-width: 1400px; margin: 0 auto; }

    /* --- Header Styles --- */
    .breadcrumb { display: flex; align-items: center; gap: 8px; font-size: 12px; margin-bottom: 8px; }
    .breadcrumb a { color: var(--accent); text-decoration: none; }
    .breadcrumb-sep { color: var(--text-tertiary); }

    .header-title { font-size: 24px; font-weight: 700; display: flex; align-items: center; gap: 12px; }
    .header-title i { color: var(--accent); }
    .version-badge { font-size: 10px; background: var(--accent-dim); color: var(--accent); padding: 2px 8px; border-radius: 99px; border: 1px solid var(--border-accent); }

    /* --- AI Control Card --- */
    .ai-hero {
        background: linear-gradient(135deg, var(--bg-surface) 0%, #151c2e 100%);
        border: 1px solid var(--border-accent);
        border-radius: var(--radius-lg);
        padding: 28px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 10px 30px -10px rgba(0,0,0,0.5);
        position: relative;
        overflow: hidden;
    }
    .ai-hero::after {
        content: ''; position: absolute; top: 0; right: 0; width: 150px; height: 150px;
        background: radial-gradient(circle, rgba(56,189,248,0.1) 0%, transparent 70%);
    }

    .ai-status-group { display: flex; align-items: center; gap: 16px; }
    .ai-icon-large {
        width: 56px; height: 56px; background: var(--accent-dim); border: 1px solid var(--border-accent);
        border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center;
        font-size: 24px; color: var(--accent);
    }

    /* --- Custom Switch --- */
    .switch-container { display: flex; align-items: center; gap: 12px; }
    .custom-switch { position: relative; display: inline-block; width: 50px; height: 26px; }
    .custom-switch input { opacity: 0; width: 0; height: 0; }
    .slider {
        position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0;
        background-color: var(--bg-elevated); transition: .4s; border-radius: 34px;
        border: 1px solid var(--border-soft);
    }
    .slider:before {
        position: absolute; content: ""; height: 18px; width: 18px; left: 4px; bottom: 3px;
        background-color: var(--text-secondary); transition: .4s; border-radius: 50%;
    }
    input:checked + .slider { background-color: var(--accent-dim); border-color: var(--accent); }
    input:checked + .slider:before { transform: translateX(24px); background-color: var(--accent); }

    /* --- Cards Genéricos --- */
    .config-card {
        background: var(--bg-surface);
        border: 1px solid var(--border-soft);
        border-radius: var(--radius-lg);
        height: 100%;
        transition: var(--transition);
    }
    .config-card:hover { border-color: var(--text-tertiary); }
    .card-header { padding: 20px 24px; border-bottom: 1px solid var(--border-soft); display: flex; align-items: center; gap: 10px; }
    .card-header i { color: var(--accent); font-size: 16px; }
    .card-header h6 { margin: 0; font-size: 14px; font-weight: 600; color: var(--text-primary); }
    .card-body { padding: 24px; }

    /* --- Portals Selection --- */
    .portal-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 12px; }
    .portal-checkbox { display: none; }
    .portal-label {
        display: flex; align-items: center; gap: 12px; padding: 14px;
        background: var(--bg-elevated); border: 1px solid var(--border-soft);
        border-radius: var(--radius-md); cursor: pointer; transition: var(--transition);
    }
    .portal-label:hover { background: rgba(255,255,255,0.03); border-color: var(--text-tertiary); }
    .portal-checkbox:checked + .portal-label {
        border-color: var(--accent); background: var(--accent-dim);
        box-shadow: 0 0 15px rgba(56,189,248,0.05);
    }
    .portal-label i { color: var(--text-tertiary); font-size: 14px; }
    .portal-checkbox:checked + .portal-label i { color: var(--accent); }
    .portal-name { font-size: 12px; font-weight: 500; color: var(--text-secondary); }
    .portal-checkbox:checked + .portal-label .portal-name { color: var(--text-primary); }

    /* --- Terminal Input --- */
    .terminal-wrapper {
        background: #05070a; border: 1px solid var(--border-soft);
        border-radius: var(--radius-md); overflow: hidden;
    }
    .terminal-header {
        background: #1a202c; padding: 8px 16px; display: flex; gap: 6px;
        border-bottom: 1px solid var(--border-soft);
    }
    .t-dot { width: 8px; height: 8px; border-radius: 50%; }
    .t-red { background: #ff5f56; } .t-amb { background: #ffbd2e; } .t-gre { background: #27c93f; }
    
    .matrix-input {
        background: transparent !important; border: none !important;
        color: var(--accent) !important; font-family: var(--font-mono) !important;
        font-size: 13px !important; resize: none; padding: 16px; width: 100%;
        outline: none; line-height: 1.6;
    }

    /* --- Roadmap Section --- */
    .roadmap-item {
        padding: 16px; border-radius: var(--radius-md);
        background: rgba(255,255,255,0.02); border: 1px solid var(--border-soft);
        margin-bottom: 12px; transition: var(--transition);
    }
    .roadmap-item:hover { transform: translateX(4px); background: rgba(255,255,255,0.04); }
    .status-badge {
        font-size: 9px; font-weight: 700; text-transform: uppercase;
        padding: 2px 8px; border-radius: 4px; letter-spacing: 0.5px;
    }
    .badge-ready { background: rgba(52,211,153,0.1); color: var(--green); border: 1px solid rgba(52,211,153,0.2); }
    .badge-queue { background: rgba(251,191,36,0.1); color: var(--amber); border: 1px solid rgba(251,191,36,0.2); }
    .badge-dev   { background: rgba(56,189,248,0.1); color: var(--accent); border: 1px solid rgba(56,189,248,0.2); }

    .feature-title { font-size: 13px; font-weight: 600; color: var(--text-primary); display: block; margin-top: 8px; }
    .feature-desc { font-size: 11px; color: var(--text-tertiary); line-height: 1.5; margin-top: 4px; }

    /* --- Buttons --- */
    .btn-sync {
        background: var(--accent); color: #0b0f1a; font-family: var(--font-ui);
        font-weight: 700; font-size: 13px; padding: 12px 32px;
        border-radius: 99px; border: none; transition: var(--transition);
        box-shadow: 0 4px 15px rgba(56,189,248,0.3);
    }
    .btn-sync:hover { background: #7dd3fc; transform: translateY(-2px); box-shadow: 0 6px 20px rgba(56,189,248,0.4); }

    .data-mono { font-family: var(--font-mono); font-size: 11px; color: var(--text-tertiary); }
    .status-ping { width: 8px; height: 8px; background: var(--green); border-radius: 50%; display: inline-block; box-shadow: 0 0 8px var(--green); animation: pulse 2s infinite; }
    @keyframes pulse { 0% { opacity: 1; } 50% { opacity: 0.3; } 100% { opacity: 1; } }
</style>

<div class="lic-container">
    <!-- Header -->
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 32px;">
        <div>
            <div class="breadcrumb">
                <a href="<?= BASE_URL ?>/licitacoes">Licitações</a>
                <span class="breadcrumb-sep">/</span>
                <span style="color: var(--text-secondary);">Agente IA</span>
            </div>
            <h1 class="header-title">
                <i class="fas fa-robot"></i> Terminal de Inteligência Artificial
                <span class="version-badge">v1.0.0-stable</span>
            </h1>
        </div>
        <a href="<?= BASE_URL ?>/licitacoes" style="text-decoration: none; font-size: 12px; color: var(--text-secondary); border: 1px solid var(--border-soft); padding: 8px 16px; border-radius: var(--radius-md); transition: var(--transition);" onmouseover="this.style.borderColor='var(--accent)'; this.style.color='var(--accent)'" onmouseout="this.style.borderColor='var(--border-soft)'; this.style.color='var(--text-secondary)'">
            <i class="fas fa-arrow-left" style="margin-right: 8px;"></i> Voltar ao Painel
        </a>
    </div>

    <form action="<?= BASE_URL ?>/licitacoes/salvarConfigIA" method="POST">
        <div class="row">
            <div class="col-lg-8">
                <!-- Status do Agente -->
                <div class="ai-hero mb-4">
                    <div class="ai-status-group">
                        <div id="ativo-icon-box" class="ai-icon-large" style="background: <?= ($config['ativo'] ?? 1) ? 'var(--accent-dim)' : 'var(--red-bg)' ?>; color: <?= ($config['ativo'] ?? 1) ? 'var(--accent)' : 'var(--red)' ?>;"><i class="fas fa-brain"></i></div>
                        <div>
                            <h5 style="margin: 0; font-size: 18px; font-weight: 700;">Protocolo de Automação</h5>
                            <div style="margin-top: 4px;">
                                <span class="status-ping"></span>
                                <span id="ativo-status-label" class="data-mono" style="color: <?= ($config['ativo'] ?? 1) ? 'var(--accent)' : 'var(--text-tertiary)' ?>"><?= ($config['ativo'] ?? 1) ? 'EXECUTANDO' : 'PAUSADO' ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="switch-container">
                        <label class="custom-switch">
                            <input type="checkbox" name="ativo" id="ativo-toggle" <?= ($config['ativo'] ?? 1) ? 'checked' : '' ?>>
                            <span class="slider"></span>
                        </label>
                    </div>
                </div>
                
                <!-- Alertas Sonoros -->
                <div class="ai-hero mb-4" style="background: var(--bg-surface); border-color: var(--border-soft);">
                    <div class="ai-status-group">
                        <div id="sound-icon-box" class="ai-icon-large" style="background: <?= ($config['sound_alerts_enabled'] ?? 1) ? 'var(--green-bg)' : 'var(--red-bg)' ?>; border-color: <?= ($config['sound_alerts_enabled'] ?? 1) ? 'rgba(52,211,153,0.25)' : 'rgba(239,68,68,0.2)' ?>; color: <?= ($config['sound_alerts_enabled'] ?? 1) ? 'var(--green)' : 'var(--red)' ?>;">
                            <i class="fas <?= ($config['sound_alerts_enabled'] ?? 1) ? 'fa-volume-up' : 'fa-volume-mute' ?>"></i>
                        </div>
                        <div>
                            <h5 style="margin: 0; font-size: 18px; font-weight: 700;">Alertas Sonoros</h5>
                            <div style="margin-top: 4px;">
                                <span id="sound-status-label" class="data-mono" style="color: <?= ($config['sound_alerts_enabled'] ?? 1) ? 'var(--green)' : 'var(--text-tertiary)' ?>">
                                    <?= ($config['sound_alerts_enabled'] ?? 1) ? 'SISTEMA DE ÁUDIO: ATIVO' : 'SISTEMA DE ÁUDIO: MUDO' ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="switch-container">
                        <div style="margin-right: 15px;">
                            <select name="notification_sound" id="notification-sound-select" class="form-input" style="height: 30px; font-size: 11px; padding: 0 10px; width: 120px;">
                                <option value="ping" <?= ($config['notification_sound'] ?? 'ping') == 'ping' ? 'selected' : '' ?>>Som: Ping</option>
                                <option value="chime" <?= ($config['notification_sound'] ?? 'ping') == 'chime' ? 'selected' : '' ?>>Som: Chime</option>
                                <option value="bell" <?= ($config['notification_sound'] ?? 'ping') == 'bell' ? 'selected' : '' ?>>Som: Bell</option>
                            </select>
                        </div>
                        <button type="button" id="btn-test-sound" class="btn btn-ghost btn-sm" style="height: 26px; border-radius: 6px; padding: 0 10px; font-size: 11px; margin-right: 8px; border-color: var(--border-soft);">
                            <i class="fas fa-play" style="font-size: 10px; margin-right: 4px;"></i> TESTAR SOM
                        </button>
                        <label class="custom-switch">
                            <input type="checkbox" name="sound_alerts_enabled" id="sound-toggle" <?= ($config['sound_alerts_enabled'] ?? 1) ? 'checked' : '' ?>>
                            <span class="slider"></span>
                        </label>
                    </div>
                </div>

                <!-- Resumo Diário por E-mail -->
                <div class="ai-hero mb-4" style="background: var(--bg-surface); border-color: var(--border-soft);">
                    <div class="ai-status-group">
                        <div id="email-icon-box" class="ai-icon-large" style="background: <?= ($config['daily_email_summary_enabled'] ?? 0) ? 'var(--purple-bg)' : 'var(--bg-elevated)' ?>; border-color: rgba(139,92,246,0.25); color: var(--purple);">
                            <i class="fas fa-envelope-open-text"></i>
                        </div>
                        <div>
                            <h5 style="margin: 0; font-size: 18px; font-weight: 700;">Resumo Diário</h5>
                            <div style="margin-top: 4px;">
                                <span id="email-status-label" class="data-mono" style="color: <?= ($config['daily_email_summary_enabled'] ?? 1) ? 'var(--purple)' : 'var(--text-tertiary)' ?>">
                                    <?= ($config['daily_email_summary_enabled'] ?? 1) ? 'RELATÓRIO POR E-MAIL: ATIVADO' : 'RELATÓRIO POR E-MAIL: DESATIVADO' ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="switch-container">
                        <label class="custom-switch">
                            <input type="checkbox" name="daily_email_summary_enabled" id="email-toggle" <?= ($config['daily_email_summary_enabled'] ?? 1) ? 'checked' : '' ?>>
                            <span class="slider"></span>
                        </label>
                    </div>
                </div>

                <!-- Portais -->
                <div class="config-card mb-4">
                    <div class="card-header">
                        <i class="fas fa-network-wired"></i>
                        <h6>Fontes de Dados (Data Mining)</h6>
                    </div>
                    <div class="card-body">
                        <div class="portal-grid">
                            <?php 
                            $portais = [
                                'pncp' => 'PNCP Nacional',
                                'comprasnet' => 'ComprasNet / SIASG',
                                'bec' => 'BEC São Paulo',
                                'bll' => 'BLL Integrado',
                                'licitacoese' => 'Licitações-e BB'
                            ];
                            foreach ($portais as $slug => $nome): 
                            ?>
                            <div>
                                <input type="checkbox" name="portais[]" value="<?= $slug ?>" id="p_<?= $slug ?>" class="portal-checkbox" <?= in_array($slug, $portaisAtivos) ? 'checked' : '' ?>>
                                <label for="p_<?= $slug ?>" class="portal-label">
                                    <i class="fas fa-microchip"></i>
                                    <span class="portal-name"><?= $nome ?></span>
                                </label>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Palavras-chave -->
                <div class="config-card mb-4">
                    <div class="card-header">
                        <i class="fas fa-code"></i>
                        <h6>Matriz de NLP (Palavras-Chave)</h6>
                    </div>
                    <div class="card-body">
                        <p style="font-size: 12px; color: var(--text-tertiary); margin-bottom: 16px;">
                            Defina os termos técnicos para a varredura semântica. O Agente ignora stop-words e foca em relevância estatística.
                        </p>
                        <div class="terminal-wrapper">
                            <div class="terminal-header">
                                <div class="t-dot t-red"></div>
                                <div class="t-dot t-amb"></div>
                                <div class="t-dot t-gre"></div>
                                <span style="margin-left: auto; font-family: var(--font-mono); font-size: 10px; color: var(--text-tertiary);">KEYWORDS_CONFIG.JSON</span>
                            </div>
                            <textarea name="palavras_chave" class="matrix-input" rows="6" placeholder="Ex: Gestão Ambiental, PGRS, Monitoramento Ar, Consultoria ESG..."><?= $config['palavras_chave'] ?? '' ?></textarea>
                        </div>
                        <div style="margin-top: 12px; display: flex; align-items: center; gap: 8px;">
                            <span class="status-ping"></span>
                            <span class="data-mono" style="font-size: 10px; color: var(--text-secondary);">AGENTE_PRONTO_PARA_SINCRONIA</span>
                        </div>
                    </div>
                </div>

                <div style="display: flex; justify-content: flex-end; margin-bottom: 48px;">
                    <button type="submit" class="btn-sync">
                        <i class="fas fa-sync-alt" style="margin-right: 10px;"></i> ATUALIZAR PARÂMETROS
                    </button>
                </div>
            </div>

            <div class="col-lg-4">
                <!-- Roadmap IA -->
                <div class="config-card">
                    <div class="card-header" style="border-bottom: 1px solid var(--border-soft);">
                        <i class="fas fa-rocket"></i>
                        <h6>Roadmap de Evolução</h6>
                    </div>
                    <div class="card-body">
                        <?php
                        $features = [
                            ['title' => 'Varredura 24/7', 'status' => 'Operacional', 'badge' => 'badge-ready', 'icon' => 'fa-clock', 'desc' => 'Monitoramento ativo em tempo real nos portais oficiais.'],
                            ['title' => 'Classificação NLP', 'status' => 'Em Fila', 'badge' => 'badge-queue', 'icon' => 'fa-tags', 'desc' => 'Separação automática por área (Resíduos, Obras, etc).'],
                            ['title' => 'Alertas Instantâneos', 'status' => 'Em Dev', 'badge' => 'badge-dev', 'icon' => 'fa-bolt', 'desc' => 'Notificações via Push e WhatsApp para editais urgentes.'],
                            ['title' => 'Scoring de Vitória', 'status' => 'P&D', 'badge' => 'badge-dev', 'icon' => 'fa-chart-line', 'desc' => 'Cálculo de probabilidade baseado em histórico de licitantes.'],
                        ];
                        foreach ($features as $f):
                        ?>
                        <div class="roadmap-item">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                <span class="status-badge <?= $f['badge'] ?>"><?= $f['status'] ?></span>
                                <i class="fas <?= $f['icon'] ?>" style="font-size: 12px; color: var(--text-tertiary);"></i>
                            </div>
                            <span class="feature-title"><?= $f['title'] ?></span>
                            <p class="feature-desc"><?= $f['desc'] ?></p>
                        </div>
                        <?php endforeach; ?>
                        
                        <div style="background: var(--accent-dim); padding: 16px; border-radius: var(--radius-md); border: 1px dashed var(--border-accent); margin-top: 24px;">
                            <p style="font-size: 11px; color: var(--accent); margin-bottom: 0; line-height: 1.5; font-weight: 500;">
                                <i class="fas fa-info-circle" style="margin-right: 6px;"></i>
                                O Agente IA aprende com os seus filtros. Quanto mais específico o terminal, mais precisa será a captação.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    // Pequena animação de feedback ao clicar no botão
    document.querySelector('form').addEventListener('submit', function(e) {
        const btn = document.querySelector('.btn-sync');
        btn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> PROCESSANDO...';
        btn.style.opacity = '0.7';
    });

    // Lógica para feedback visual do Protocolo de Automação
    const ativoToggle = document.getElementById('ativo-toggle');
    const ativoLabel = document.getElementById('ativo-status-label');
    const ativoIconBox = document.getElementById('ativo-icon-box');

    if (ativoToggle) {
        ativoToggle.addEventListener('change', function() {
            if (this.checked) {
                ativoLabel.textContent = 'EXECUTANDO';
                ativoLabel.style.color = 'var(--accent)';
                ativoIconBox.style.background = 'var(--accent-dim)';
                ativoIconBox.style.color = 'var(--accent)';
            } else {
                ativoLabel.textContent = 'PAUSADO';
                ativoLabel.style.color = 'var(--text-tertiary)';
                ativoIconBox.style.background = 'var(--red-bg)';
                ativoIconBox.style.color = 'var(--red)';
            }
        });
    }

    // Lógica para feedback visual do botão mudo
    const soundToggle = document.getElementById('sound-toggle');
    const soundLabel = document.getElementById('sound-status-label');
    const soundIconBox = document.getElementById('sound-icon-box');

    if (soundToggle) {
        soundToggle.addEventListener('change', function() {
            if (this.checked) {
                soundLabel.textContent = 'SISTEMA DE ÁUDIO: ATIVO';
                soundLabel.style.color = 'var(--green)';
                soundIconBox.style.background = 'var(--green-bg)';
                soundIconBox.style.color = 'var(--green)';
                soundIconBox.innerHTML = '<i class="fas fa-volume-up"></i>';
            } else {
                soundLabel.textContent = 'SISTEMA DE ÁUDIO: MUDO';
                soundLabel.style.color = 'var(--text-tertiary)';
                soundIconBox.style.background = 'var(--red-bg)';
                soundIconBox.style.color = 'var(--red)';
                soundIconBox.innerHTML = '<i class="fas fa-volume-mute"></i>';
            }
        });
    }

    // Mapeamento de sons para URLs
    const SOUND_MAP = {
        'ping': 'https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3',
        'chime': 'https://assets.mixkit.co/active_storage/sfx/2358/2358-preview.mp3',
        'bell': 'https://assets.mixkit.co/active_storage/sfx/2568/2568-preview.mp3'
    };

    // Lógica para testar o som de notificação
    const btnTestSound = document.getElementById('btn-test-sound');
    const soundSelect = document.getElementById('notification-sound-select');

    if (btnTestSound) {
        btnTestSound.addEventListener('click', function() {
            const soundKey = soundSelect.value || 'ping';
            const audio = new Audio(SOUND_MAP[soundKey]);
            audio.volume = 0.4;
            const icon = this.querySelector('i');
            
            // Feedback visual no ícone durante a reprodução
            icon.className = 'fas fa-volume-up fa-beat';
            
            audio.play().finally(() => {
                setTimeout(() => { icon.className = 'fas fa-play'; }, 1200);
            }).catch(e => console.warn("Erro ao reproduzir áudio de teste:", e));
        });
    }

    // Lógica para feedback visual do resumo diário
    const emailToggle = document.getElementById('email-toggle');
    const emailLabel = document.getElementById('email-status-label');
    const emailIconBox = document.getElementById('email-icon-box');

    if (emailToggle) {
        emailToggle.addEventListener('change', function() {
            if (this.checked) {
                emailLabel.textContent = 'RELATÓRIO POR E-MAIL: ATIVADO';
                emailLabel.style.color = 'var(--purple)';
                emailIconBox.style.background = 'var(--purple-bg)';
            } else {
                emailLabel.textContent = 'RELATÓRIO POR E-MAIL: DESATIVADO';
                emailLabel.style.color = 'var(--text-tertiary)';
                emailIconBox.style.background = 'var(--bg-elevated)';
            }
        });
    }

    
// Adicione ao final do script existente

// Sincroniza com o agente Python em tempo real
async function syncWithPython() {
    const config = {
        ativo: document.getElementById('ativo-toggle').checked,
        portais: Array.from(document.querySelectorAll('input[name="portais[]"]:checked')).map(cb => cb.value),
        palavras_chave: document.querySelector('textarea[name="palavras_chave"]').value,
        sound_alerts_enabled: document.getElementById('sound-toggle').checked,
        notification_sound: document.getElementById('notification-sound-select').value,
        daily_email_summary_enabled: document.getElementById('email-toggle').checked
    };
    
    try {
        const response = await fetch('<?= BASE_URL ?>/licitacoes/agenteIA', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(config)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showStatusMessage('Configuração sincronizada com o Agente IA', 'success');
        } else {
            showStatusMessage('Erro ao sincronizar', 'error');
        }
    } catch (e) {
        showStatusMessage('Erro de comunicação com o Agente IA', 'error');
    }
}

// Substitui o evento de submit do form
document.querySelector('form').addEventListener('submit', async (e) => {
    e.preventDefault();
    await syncWithPython();
});

// Mostra status do agente Python
async function checkPythonStatus() {
    try {
        const response = await fetch('<?= BASE_URL ?>/licitacoes/api/ia-status');
        const status = await response.json();
        
        const statusDot = document.querySelector('.status-ping');
        const statusLabel = document.getElementById('ativo-status-label');
        
        if (status.ativo) {
            statusDot.style.background = 'var(--green)';
            statusLabel.textContent = 'CONECTADO';
            statusLabel.style.color = 'var(--green)';
        } else {
            statusDot.style.background = 'var(--red)';
            statusLabel.textContent = 'DESCONECTADO';
            statusLabel.style.color = 'var(--red)';
        }
    } catch (e) {
        console.log('Python agent offline');
    }
}

// Verifica status periodicamente
setInterval(checkPythonStatus, 30000);
checkPythonStatus();

</script>

