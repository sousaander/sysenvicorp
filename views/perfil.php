<div class="page-wrapper">
    <div class="page-content">
        <div class="page-header mb-3">
            <h1 class="text-2xl font-semibold">Meu Perfil</h1>
        </div>
        <!--end header-->

        <div class="container">
            <div class="main-body">
                <div class="row">
                    <div class="col-lg-8 mx-auto">
                        <div class="card">
                            <div class="card-body">
                                <form action="<?php echo BASE_URL; ?>/usuario/salvarPerfil" method="POST" enctype="multipart/form-data">
                                    <!-- Adiciona o campo oculto para o token CSRF -->
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token ?? ''); ?>">

                                    <div class="row mb-3">
                                        <div class="col-sm-3">
                                            <h6 class="mb-0">Foto</h6>
                                        </div>
                                        <div class="col-sm-9 text-secondary">
                                            <div class="flex items-center space-x-4">
                                                <div class="w-24 h-24 rounded-full overflow-hidden bg-gray-100 border" style="min-width:96px">
                                                    <img id="foto-preview" src="<?php echo htmlspecialchars($usuario['foto_url'] ?? 'https://ui-avatars.com/api/?name=' . urlencode($usuario['nome'] ?? 'Usuário') . '&background=38bdf8&color=fff&size=96'); ?>" alt="Foto de Perfil" class="h-full w-full object-cover">
                                                </div>
                                                <div>
                                                    <input type="file" name="foto" id="foto" accept="image/*" class="hidden">
                                                    <label for="foto" class="inline-flex items-center px-3 py-2 border rounded-md bg-white text-sm cursor-pointer">Escolher foto</label>
                                                    <br>
                                                    <label class="inline-flex items-center mt-2 text-sm"><input type="checkbox" name="remover_foto" value="1" class="mr-2">Remover foto</label>
                                                    <p class="text-xs text-gray-400 mt-1">JPEG, PNG, GIF ou WEBP. Máx 2MB.</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-sm-3">
                                            <h6 class="mb-0">Nome Completo</h6>
                                        </div>
                                        <div class="col-sm-9 text-secondary">
                                            <input type="text" class="form-control" name="nome" value="<?php echo htmlspecialchars($usuario['nome'] ?? ''); ?>" required>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-sm-3">
                                            <h6 class="mb-0">Email</h6>
                                        </div>
                                        <div class="col-sm-9 text-secondary">
                                            <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($usuario['email'] ?? ''); ?>" required>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="row mb-3">
                                        <div class="col-sm-12">
                                            <h6 class="mb-2 text-secondary">Alterar Senha (opcional)</h6>
                                            <p class="text-muted font-size-sm">Deixe os campos abaixo em branco se não desejar alterar sua senha.</p>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-sm-3">
                                            <h6 class="mb-0">Nova Senha</h6>
                                        </div>
                                        <div class="col-sm-9 text-secondary">
                                            <input type="password" class="form-control" name="senha" placeholder="Mínimo de 6 caracteres">
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-sm-3">
                                            <h6 class="mb-0">Confirmar Senha</h6>
                                        </div>
                                        <div class="col-sm-9 text-secondary">
                                            <input type="password" class="form-control" name="confirmar_senha" placeholder="Confirme a nova senha">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-3"></div>
                                        <div class="col-sm-9 text-secondary">
                                            <button type="submit" id="save-profile-btn" class="w-full sm:w-auto inline-flex items-center justify-center bg-sky-600 hover:bg-sky-700 focus:bg-sky-700 text-white font-semibold py-2 px-4 sm:px-6 rounded-md shadow-md transition-colors disabled:opacity-50" aria-label="Salvar Alterações">
                                                <span id="save-spinner" class="hidden animate-spin h-4 w-4 mr-2 border-2 border-white border-t-transparent rounded-full inline-block" aria-hidden="true"></span>
                                                <svg id="save-icon" class="h-4 w-4 mr-2 hidden sm:inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                                <span id="save-text">Salvar Alterações</span>
                                            </button>
                                        </div>
                                    </div>
                                </form>
                                <script>
                                    document.addEventListener("DOMContentLoaded", function() {
                                        var form = document.querySelector('form[action$="/usuario/salvarPerfil"]');
                                        var btn = document.getElementById("save-profile-btn");

                                        // Submit feedback
                                        if (form && btn) {
                                            form.addEventListener("submit", function() {
                                                btn.disabled = true;
                                                btn.classList.add("opacity-75", "cursor-not-allowed");
                                                var spinner = document.getElementById("save-spinner");
                                                var icon = document.getElementById("save-icon");
                                                var text = document.getElementById("save-text");
                                                if (spinner) spinner.classList.remove("hidden");
                                                if (icon) icon.classList.add("hidden");
                                                if (text) text.textContent = "Salvando...";
                                            });
                                        }

                                        // Preview da foto
                                        var fotoInput = document.getElementById('foto');
                                        var fotoPreview = document.getElementById('foto-preview');
                                        if (fotoInput && fotoPreview) {
                                            fotoInput.addEventListener('change', function(e) {
                                                var file = e.target.files[0];
                                                if (!file) return;
                                                if (!file.type.startsWith('image/')) return;
                                                var reader = new FileReader();
                                                reader.onload = function(ev) {
                                                    fotoPreview.src = ev.target.result;
                                                };
                                                reader.readAsDataURL(file);
                                            });
                                        }
                                    });
                                </script>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>