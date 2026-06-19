<style>
    :root {
        --profile-primary: #0ea5e9;
        --profile-primary-hover: #0284c7;
        --profile-bg: #f8fafc;
        --profile-card: #ffffff;
        --profile-border: #e2e8f0;
        --profile-text-main: #1e293b;
        --profile-text-muted: #64748b;
        --profile-input-bg: #f1f5f9;
    }

    .profile-container {
        max-width: 1000px;
        margin: 0 auto;
        padding: 2rem 1rem;
    }

    .profile-grid {
        display: grid;
        grid-template-columns: 300px 1fr;
        gap: 2rem;
    }

    @media (max-width: 768px) {
        .profile-grid { grid-template-columns: 1fr; }
    }

    .card {
        background: var(--profile-card);
        border: 1px solid var(--profile-border);
        border-radius: 16px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }

    .banner {
        height: 120px;
        background: linear-gradient(135deg, var(--profile-primary) 0%, #0369a1 100%);
    }

    .form-group { margin-bottom: 1.25rem; }
    .form-label {
        display: block;
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--profile-text-main);
        margin-bottom: 0.5rem;
    }

    .form-control {
        width: 100%;
        padding: 0.625rem 0.75rem;
        border: 1px solid var(--profile-border);
        border-radius: 8px;
        background-color: var(--profile-input-bg);
        color: var(--profile-text-main);
        transition: all 0.2s;
    }

    .form-control:focus {
        outline: none;
        border-color: var(--profile-primary);
        background-color: #fff;
        box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.1);
    }

    .section-title {
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--profile-text-muted);
        margin: 2rem 0 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 1px solid var(--profile-border);
    }

    .avatar-option {
        width: 54px;
        height: 54px;
        border-radius: 50%;
        cursor: pointer;
        border: 2px solid transparent;
        transition: all 0.2s;
        position: relative;
    }
    .avatar-option:hover { transform: scale(1.1); }

    .btn-save {
        background-color: var(--profile-primary);
        color: white;
        padding: 0.625rem 1.5rem;
        border-radius: 8px;
        font-weight: 600;
        border: none;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: background 0.2s;
    }
    .btn-save:hover { background-color: var(--profile-primary-hover); }

    .btn-back {
        background-color: #e2e8f0; /* Light gray */
        color: #475569; /* Darker text */
        padding: 0.625rem 1.5rem;
        border-radius: 8px;
        font-weight: 600;
        border: 1px solid var(--profile-border);
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        transition: all 0.2s;
    }
    .btn-back:hover { background-color: #cbd5e1; color: #1e293b; }

    .btn-discard {
        background-color: #f1f5f9;
        color: #64748b;
        padding: 0.625rem 1.5rem;
        border-radius: 8px;
        font-weight: 600;
        border: 1px solid var(--profile-border);
        cursor: pointer;
        transition: all 0.2s;
    }
    .btn-discard:hover { background-color: #e2e8f0; color: var(--profile-text-main); }
</style>

<div class="profile-container">
    <!-- Header -->
    <div style="margin-bottom: 2rem;">
        <h1 style="font-size: 1.875rem; font-weight: 700; color: var(--profile-text-main); margin: 0;">Meu Perfil</h1>
        <p style="color: var(--profile-text-muted); margin-top: 0.25rem;">Atualize seus dados cadastrais e gerencie sua segurança.</p>
    </div>

    <div class="profile-grid">
        <!-- Sidebar Lateral (Overview) -->
        <aside>
            <div class="card" style="padding: 1.5rem; text-align: center;">
                <div style="position: relative; width: 120px; height: 120px; margin: 0 auto 1rem;">
                    <div style="width: 100%; height: 100%; border-radius: 50%; overflow: hidden; border: 4px solid #fff; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                        <img id="foto-preview"
                             src="<?php echo htmlspecialchars($usuario['foto_url'] ?? 'https://ui-avatars.com/api/?name=' . urlencode($usuario['nome'] ?? 'Usuário') . '&background=0ea5e9&color=fff&size=128'); ?>"
                             alt="Avatar" style="width: 100%; height: 100%; object-fit: cover;">
                    </div>
                    <label for="foto" style="position: absolute; bottom: 0; right: 0; background: var(--profile-primary); width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; cursor: pointer; border: 2px solid #fff;">
                        <i class='bx bx-camera'></i>
                    </label>
                </div>
                <h2 style="font-size: 1.125rem; font-weight: 700; margin: 0; color: var(--profile-text-main);"><?php echo htmlspecialchars($usuario['nome']); ?></h2>
                <p style="font-size: 0.875rem; color: var(--profile-text-muted); margin-bottom: 1.5rem;"><?php echo htmlspecialchars($usuario['email']); ?></p>
                
                <div style="text-align: left; background: var(--profile-input-bg); padding: 1rem; border-radius: 12px; font-size: 0.8rem;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                        <span style="color: var(--profile-text-muted);">Status</span>
                        <span style="color: #16a34a; font-weight: 600;">Ativo</span>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: var(--profile-text-muted);">ID do Usuário</span>
                        <span style="font-weight: 600;">#<?php echo $usuario['id']; ?></span>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Formulário Principal -->
        <main class="card" style="padding: 2rem;">
            <form action="<?php echo BASE_URL; ?>/usuario/salvarPerfil" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token ?? ''); ?>">
                <input type="file" name="foto" id="foto" accept="image/*" style="display: none;">
                <input type="hidden" name="selected_avatar" id="selected_avatar" value="<?php echo htmlspecialchars($usuario['avatar_filename'] ?? ''); ?>">

                <h3 style="margin-top: 0;" class="section-title">Dados Pessoais</h3>
                
                <div class="form-group">
                    <label class="form-label" for="nome">Nome Completo</label>
                    <input type="text" name="nome" id="nome" class="form-control" value="<?php echo htmlspecialchars($usuario['nome']); ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="email">E-mail Corporativo</label>
                    <input type="email" name="email" id="email" class="form-control" value="<?php echo htmlspecialchars($usuario['email']); ?>" required>
                </div>

                <div style="margin-top: 1.5rem; padding: 1rem; border: 1px dashed var(--profile-border); border-radius: 12px;">
                    <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem; cursor: pointer; color: #ef4444;">
                        <input type="checkbox" name="remover_foto" id="remover_foto" value="1" style="width: 16px; height: 16px;">
                        Remover foto de perfil personalizada
                    </label>
                </div>

                <h3 class="section-title">Galeria de Avatares</h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(54px, 1fr)); gap: 1rem;">
                    <?php if (!empty($availableAvatars)): ?>
                        <?php foreach ($availableAvatars as $avatar): ?>
                            <div class="avatar-option" onclick="selectAvatar('<?php echo $avatar; ?>', this)" 
                                 style="<?php echo (isset($usuario['avatar_filename']) && $usuario['avatar_filename'] === $avatar) ? 'border-color: var(--profile-primary); box-shadow: 0 0 0 4px rgba(14,165,233,0.1);' : ''; ?>">
                                <img src="<?php echo BASE_URL . '/public/assets/avatars/' . $avatar; ?>" alt="Avatar" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">
                                <?php if (isset($usuario['avatar_filename']) && $usuario['avatar_filename'] === $avatar): ?>
                                    <div class="selected-overlay" style="position: absolute; inset: 0; background: rgba(14,165,233,0.1); display: flex; align-items: center; justify-content: center; border-radius: 50%;">
                                        <i class='bx bx-check-circle' style="color: var(--profile-primary); font-size: 1.25rem;"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <h3 class="section-title">Segurança e Senha</h3>
                <p style="font-size: 0.8rem; color: var(--profile-text-muted); margin-bottom: 1.25rem;">Preencha apenas se desejar alterar a senha atual.</p>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                    <div class="form-group" style="position: relative;">
                        <label class="form-label" for="senha-input">Nova Senha</label>
                        <input type="password" name="senha" id="senha-input" class="form-control" placeholder="Mín. 6 caracteres" autocomplete="new-password">
                        <button type="button" onclick="togglePass('senha-input','eye1')" style="position: absolute; right: 10px; top: 38px; background: none; border: none; cursor: pointer; color: var(--profile-text-muted);">
                            <i id="eye1" class='bx bx-show'></i>
                        </button>
                        <!-- Strength bar -->
                        <div id="pass-strength-bar" style="margin-top: 8px; height: 4px; border-radius: 4px; background: #e2e8f0;">
                            <div id="pass-strength-fill" style="height: 100%; width: 0%; border-radius: 4px; transition: all 0.3s;"></div>
                        </div>
                    </div>

                    <div class="form-group" style="position: relative;">
                        <label class="form-label" for="confirmar-input">Confirmar Senha</label>
                        <input type="password" name="confirmar_senha" id="confirmar-input" class="form-control" placeholder="Repita a nova senha" autocomplete="new-password">
                        <button type="button" onclick="togglePass('confirmar-input','eye2')" style="position: absolute; right: 10px; top: 38px; background: none; border: none; cursor: pointer; color: var(--profile-text-muted);">
                            <i id="eye2" class='bx bx-show'></i>
                        </button>
                        <p id="confirm-match" style="margin: 4px 0 0; font-size: 0.75rem; min-height: 1em;"></p>
                    </div>
                </div>

                <div style="margin-top: 3rem; display: flex; justify-content: flex-end; gap: 1rem;">
                    <a href="<?php echo BASE_URL; ?>/" class="btn-back">Voltar</a>
                    <button type="reset" class="btn-discard">Descartar</button>
                    <button type="submit" id="save-profile-btn" class="btn-save">
                        <i class='bx bx-check-double'></i>
                        <span id="save-text">Salvar Alterações</span>
                    </button>
                </div>
            </form>
        </main>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    // Preview de foto
    var fotoInput = document.getElementById('foto');
    var fotoPreview = document.getElementById('foto-preview');
    var removerFotoCheckbox = document.getElementById('remover_foto');
    var selectedAvatarInput = document.getElementById('selected_avatar');
    var form = document.querySelector('form');
    var originalPhoto = fotoPreview ? fotoPreview.src : '';

    if (fotoInput && fotoPreview) {
        fotoInput.addEventListener('change', function (e) {
            var file = e.target.files[0];
            if (!file) return;
            var reader = new FileReader();
            reader.onload = function (ev) { 
                fotoPreview.src = ev.target.result; 
                if(removerFotoCheckbox) removerFotoCheckbox.checked = false;
                if(selectedAvatarInput) selectedAvatarInput.value = '';
                // Limpa destaque da galeria
                document.querySelectorAll('.avatar-option').forEach(el => {
                    el.style.borderColor = 'transparent';
                    el.style.boxShadow = 'none';
                    var overlay = el.querySelector('.selected-overlay');
                    if(overlay) overlay.remove();
                });
            };
            reader.readAsDataURL(file);
        });
    }

    // Função global para selecionar avatar
    window.selectAvatar = function(filename, element) {
        if(selectedAvatarInput) selectedAvatarInput.value = filename;
        if(fotoPreview) fotoPreview.src = '<?php echo BASE_URL; ?>/public/assets/avatars/' + filename;
        if(removerFotoCheckbox) removerFotoCheckbox.checked = false;
        if(fotoInput) fotoInput.value = '';

        document.querySelectorAll('.avatar-option').forEach(el => {
            el.style.borderColor = 'transparent';
            el.style.boxShadow = 'none';
            const overlay = el.querySelector('.selected-overlay');
            if(overlay) overlay.remove();
        });

        element.style.borderColor = '#0ea5e9';
        element.style.boxShadow = '0 0 0 2px rgba(14,165,233,0.2)';
        var checkDiv = document.createElement('div');
        checkDiv.className = 'selected-overlay';
        checkDiv.style = "position: absolute; inset: 0; background: rgba(14,165,233,0.1); display: flex; align-items: center; justify-content: center;";
        checkDiv.innerHTML = "<i class='bx bx-check-circle' style='color: #0ea5e9; font-size: 1.5rem;'></i>";
        element.appendChild(checkDiv);
    };

    // Força da senha
    var senhaInput = document.getElementById('senha-input');
    var fill = document.getElementById('pass-strength-fill');
    var label = document.getElementById('pass-strength-label');
    if (senhaInput) {
        senhaInput.addEventListener('input', function () {
            var v = this.value;
            var score = 0;
            if (v.length >= 6) score++;
            if (v.length >= 10) score++;
            if (/[A-Z]/.test(v) && /[a-z]/.test(v)) score++;
            if (/[0-9]/.test(v)) score++;
            if (/[^A-Za-z0-9]/.test(v)) score++;
            var configs = [
                { pct: '0%', color: '#e2e8f0', text: '' },
                { pct: '25%', color: '#ef4444', text: 'Muito fraca' },
                { pct: '50%', color: '#f97316', text: 'Fraca' },
                { pct: '75%', color: '#eab308', text: 'Razoável' },
                { pct: '90%', color: '#22c55e', text: 'Forte' },
                { pct: '100%', color: '#16a34a', text: 'Muito forte' }
            ];
            var c = v.length === 0 ? configs[0] : configs[Math.min(score, 5)];
            fill.style.width = c.pct;
            fill.style.background = c.color;
        });
    }

    // Verificar confirmação de senha
    var confirmarInput = document.getElementById('confirmar-input');
    var confirmMsg = document.getElementById('confirm-match');
    var saveBtn = document.getElementById('save-profile-btn');

    if (confirmarInput && senhaInput && saveBtn) {
        function checkMatch() {
            const pass = senhaInput.value.trim();
            const conf = confirmarInput.value.trim();

            // Se ambos estão vazios ou se os valores coincidem, o botão é liberado
            if (pass === conf || (!pass && !conf)) {
                confirmMsg.textContent = '✓ Senhas conferem';
                confirmMsg.style.color = '#22c55e';
                if (!pass && !conf) confirmMsg.textContent = ''; // Limpa texto se estiver tudo vazio
                
                saveBtn.disabled = false; saveBtn.style.opacity = '1'; saveBtn.style.cursor = 'pointer';
            } else {
                // Só bloqueia se houver divergência real entre o que foi digitado
                confirmMsg.textContent = '✗ Senhas não conferem';
                confirmMsg.style.color = '#ef4444';
                saveBtn.disabled = true; saveBtn.style.opacity = '0.6'; saveBtn.style.cursor = 'not-allowed';
            }
        }
        confirmarInput.addEventListener('input', checkMatch);
        senhaInput.addEventListener('input', checkMatch);
        
        // Executa uma vez no carregamento para lidar com preenchimentos automáticos do navegador
        checkMatch();
    }

    // Lógica para o botão Descartar (Reset)
    if (form) {
        form.addEventListener('reset', function() {
            // Pequeno delay para garantir que o reset nativo ocorra antes de manipularmos o DOM
            setTimeout(function() {
                // Restaurar imagem original
                if (fotoPreview) fotoPreview.src = originalPhoto;
                
                // Resetar barra de força da senha
                if (fill) {
                    fill.style.width = '0%';
                    fill.style.background = '#e2e8f0';
                }
                
                // Limpar mensagem de confirmação
                if (confirmMsg) confirmMsg.textContent = '';
                
                // Reabilitar botão salvar
                if (saveBtn) { saveBtn.disabled = false; saveBtn.style.opacity = '1'; saveBtn.style.cursor = 'pointer'; }

                // Nota: O reset nativo já cuidará dos inputs de texto, checkbox 
                // e do valor original do campo oculto 'selected_avatar'.
            }, 10);
        });
    }
});

// Toggle visibilidade de senha
function togglePass(inputId, eyeId) {
    var input = document.getElementById(inputId);
    var eye = document.getElementById(eyeId);
    if (!input) return;
    var isText = input.type === 'text';
    input.type = isText ? 'password' : 'text';
    eye.className = isText ? 'bx bx-hide' : 'bx bx-show';
}
</script>
