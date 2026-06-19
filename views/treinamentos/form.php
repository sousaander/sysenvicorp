<?php
$isEdit = isset($treinamento) && !empty($treinamento['id']);
$actionUrl = BASE_URL . '/treinamentos/salvar';
// $colaboradores deve ser um array de ['id','nome','departamento'] para o autocomplete
$colaboradoresJson = json_encode($colaboradores ?? []);
$participantesAtuais = $treinamento['participantes'] ?? [];
?>

<style>
.trein-form-section { background:#fff; border:0.5px solid #e5e7eb; border-radius:12px; margin-bottom:1rem; overflow:hidden; }
.trein-form-section-header { padding:1rem 1.25rem; border-bottom:0.5px solid #f3f4f6; display:flex; align-items:center; gap:10px; }
.trein-form-section-icon { width:30px;height:30px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:15px; }
.trein-form-section-title { font-size:14px;font-weight:600;color:#111827; }
.trein-form-section-body { padding:1.25rem; }
.trein-form-label { display:block;font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:5px; }
.trein-form-label .req { color:#ef4444;margin-left:2px; }
.trein-form-input {
    width:100%;padding:8px 10px;border-radius:8px;border:1px solid #d1d5db;
    background:#fff;color:#111827;font-size:13px;font-family:inherit;
    transition:border-color .15s,box-shadow .15s;
}
.trein-form-input:focus { outline:none;border-color:#0ea5e9;box-shadow:0 0 0 3px #e0f2fe; }
.trein-part-search-wrap { position:relative; }
.trein-part-search-icon { position:absolute;right:12px;top:50%;transform:translateY(-50%);color:#9ca3af;pointer-events:none; }
.trein-part-search-input { padding-right:40px !important; padding-left: 12px !important; }
.trein-suggestions { position:absolute;z-index:50;left:0;right:0;top:calc(100% + 4px); background:#fff;border:1px solid #e5e7eb;border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,0.08);display:none;overflow:hidden;max-height:240px;overflow-y:auto; }
.trein-suggestions.open { display:block; }
.trein-sugg-item { padding:8px 12px;display:flex;align-items:center;gap:10px;cursor:pointer;font-size:13px;border-bottom:1px solid #f9fafb;transition:background .1s; }
.trein-sugg-item:last-child { border-bottom:none; }
.trein-sugg-item:hover { background:#f0f9ff; }
.trein-sugg-name { font-weight:600;color:#111827; }
.trein-sugg-dept { font-size:11px;color:#9ca3af; }
.trein-avatar-sm { width:30px;height:30px;border-radius:50%;background:#bfdbfe;color:#1e40af;font-size:10px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0; }
.trein-participant-list { margin-top:1rem;display:flex;flex-direction:column;gap:6px; }
.trein-participant-row { display:flex;align-items:center;gap:10px;padding:8px 12px;background:#f9fafb;border-radius:8px;border:1px solid #f3f4f6; }
.trein-part-info { flex:1; }
.trein-part-name { font-size:13px;font-weight:600;color:#111827; }
.trein-part-dept { font-size:11px;color:#9ca3af; }
.trein-remove-btn { width:26px;height:26px;border-radius:50%;border:none;background:transparent;color:#9ca3af;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:16px;transition:all .15s; }
.trein-remove-btn:hover { background:#fee2e2;color:#991b1b; }
.trein-count-badge { display:inline-flex;align-items:center;justify-content:center;background:#dbeafe;color:#1e40af;border-radius:20px;font-size:11px;font-weight:700;padding:1px 8px;margin-left:8px; }
.trein-empty-state { text-align:center;padding:2rem;color:#9ca3af;font-size:13px;border:1px dashed #e5e7eb;border-radius:8px; }
.trein-form-footer { display:flex;justify-content:space-between;align-items:center;padding:1rem 1.25rem;background:#f9fafb;border-top:1px solid #f3f4f6; }
.trein-btn { display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;border:1px solid #d1d5db;background:#fff;color:#374151;text-decoration:none;transition:background .15s; }
.trein-btn:hover { background:#f3f4f6; }
.trein-btn-primary { background:#0284c7;color:#fff;border-color:#0284c7; }
.trein-btn-primary:hover { background:#0369a1; }
</style>

<div class="flex justify-between items-start mb-6">
    <div>
        <h2 class="text-2xl font-bold flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-sky-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" /></svg>
            <?php echo htmlspecialchars($pageTitle); ?>
        </h2>
        <p class="text-gray-500 text-sm mt-1">Preencha os dados para <?php echo $isEdit ? 'atualizar o' : 'agendar um novo'; ?> treinamento.</p>
    </div>
    <a href="<?php echo BASE_URL; ?>/treinamentos" class="trein-btn">&larr; Voltar para a Lista</a>
</div>

<form action="<?php echo $actionUrl; ?>" method="POST" id="trein-form">
    <?php if ($isEdit) : ?>
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($treinamento['id']); ?>">
    <?php endif; ?>
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">

    <!-- Seção 1: Informações Gerais -->
    <div class="trein-form-section">
        <div class="trein-form-section-header">
            <div class="trein-form-section-icon" style="background:#e0f2fe;color:#0284c7">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" /></svg>
            </div>
            <span class="trein-form-section-title">Informações Gerais</span>
        </div>
        <div class="trein-form-section-body">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="md:col-span-2">
                    <label for="nome_treinamento" class="trein-form-label">Nome do Treinamento<span class="req">*</span></label>
                    <input type="text" id="nome_treinamento" name="nome_treinamento" required
                           value="<?php echo htmlspecialchars($treinamento['nome_treinamento'] ?? ''); ?>"
                           placeholder="Ex.: NR-35 – Trabalho em Altura"
                           class="trein-form-input">
                </div>
                <div>
                    <label for="data_prevista" class="trein-form-label">Data e Hora Prevista<span class="req">*</span></label>
                    <input type="datetime-local" id="data_prevista" name="data_prevista" required
                           value="<?php echo $isEdit ? date('Y-m-d\TH:i', strtotime($treinamento['data_prevista'])) : ''; ?>"
                           class="trein-form-input">
                </div>
                <div>
                    <label for="status" class="trein-form-label">Status</label>
                    <select id="status" name="status" class="trein-form-input">
                        <?php
                        $statusOptions = ['Agendado', 'Realizado', 'Cancelado'];
                        $statusSalvo = $treinamento['status'] ?? 'Agendado';
                        foreach ($statusOptions as $opt) : ?>
                            <option value="<?php echo $opt; ?>" <?php echo ($opt === $statusSalvo) ? 'selected' : ''; ?>><?php echo $opt; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="instrutor" class="trein-form-label">Instrutor / Responsável</label>
                    <input type="text" id="instrutor" name="instrutor"
                           value="<?php echo htmlspecialchars($treinamento['instrutor'] ?? ''); ?>"
                           placeholder="Nome do instrutor"
                           class="trein-form-input">
                </div>
                <div>
                    <label for="local" class="trein-form-label">Local</label>
                    <input type="text" id="local" name="local"
                           value="<?php echo htmlspecialchars($treinamento['local'] ?? ''); ?>"
                           placeholder="Ex.: Sala A – Sede / Online – Google Meet"
                           class="trein-form-input">
                </div>
                <div class="md:col-span-2">
                    <label for="descricao" class="trein-form-label">Descrição</label>
                    <textarea id="descricao" name="descricao" rows="3"
                              placeholder="Descreva os objetivos e conteúdo do treinamento..."
                              class="trein-form-input"><?php echo htmlspecialchars($treinamento['descricao'] ?? ''); ?></textarea>
                </div>
            </div>
        </div>
    </div>

    <!-- Seção 2: Participantes -->
    <div class="trein-form-section">
        <div class="trein-form-section-header">
            <div class="trein-form-section-icon" style="background:#dcfce7;color:#166534">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" /></svg>
            </div>
            <span class="trein-form-section-title">
                Participantes
                <span class="trein-count-badge" id="trein-part-count-badge"><?php echo count($participantesAtuais); ?></span>
            </span>
        </div>
        <div class="trein-form-section-body">
            <div style="position:relative">
                <label class="trein-form-label">Adicionar Colaboradores</label>
                <div class="trein-part-search-wrap">
                    <svg class="trein-part-search-icon" xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
                    <input type="text" id="trein-search" autocomplete="off"
                           class="trein-form-input trein-part-search-input"
                           placeholder="Pesquise por nome ou departamento para adicionar...">
                </div>
                <div class="trein-suggestions" id="trein-suggestions"></div>
            </div>

            <div class="trein-participant-list" id="trein-participant-list">
                <?php if (empty($participantesAtuais)) : ?>
                    <div class="trein-empty-state" id="trein-empty-msg">
                        Nenhum participante adicionado ainda.
                    </div>
                <?php else : ?>
                    <?php foreach ($participantesAtuais as $p) :
                        $initials = mb_strtoupper(mb_substr($p['nome'], 0, 1)) . (isset(explode(' ', $p['nome'])[1]) ? mb_strtoupper(mb_substr(explode(' ', $p['nome'])[1], 0, 1)) : '');
                    ?>
                    <div class="trein-participant-row" data-id="<?php echo $p['id']; ?>">
                        <div class="trein-avatar-sm"><?php echo htmlspecialchars($initials); ?></div>
                        <div class="trein-part-info">
                            <div class="trein-part-name"><?php echo htmlspecialchars($p['nome']); ?></div>
                            <div class="trein-part-dept"><?php echo htmlspecialchars($p['departamento'] ?? ''); ?></div>
                        </div>
                        <input type="hidden" name="participantes[]" value="<?php echo $p['id']; ?>">
                        <button type="button" class="trein-remove-btn btn-remove-part" title="Remover">✕</button>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <p id="trein-part-count-text" style="font-size:12px;color:#9ca3af;margin-top:8px">
                <?php echo count($participantesAtuais); ?> participante(s) adicionado(s)
            </p>
        </div>
    </div>

    <!-- Rodapé -->
    <div class="trein-form-section">
        <div class="trein-form-footer">
            <span style="font-size:12px;color:#9ca3af">Campos com <span style="color:#ef4444">*</span> são obrigatórios</span>
            <div class="flex gap-2">
                <a href="<?php echo BASE_URL; ?>/treinamentos" class="trein-btn">Cancelar</a>
                <button type="submit" class="trein-btn trein-btn-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 3.75H6.912a2.25 2.25 0 0 0-2.15 1.588L2.35 13.177a2.25 2.25 0 0 0-.1.661V18a2 2 0 0 0 2 2h15.5a2 2 0 0 0 2-2v-4.162c0-.224-.035-.447-.1-.661L19.24 5.338a2.25 2.25 0 0 0-2.15-1.588H15M2.25 13.5h3.86a2.251 2.251 0 0 1 2.012 1.244l.256.512a2.251 2.251 0 0 0 2.013 1.244h3.218a2.251 2.251 0 0 0 2.013-1.244l.256-.512a2.251 2.251 0 0 1 2.013-1.244h3.859M12 3v8.25m0 0-3-3m3 3 3-3" /></svg>
                    Salvar Treinamento
                </button>
            </div>
        </div>
    </div>
</form>

<script>
(function() {
    const colaboradores = <?php echo $colaboradoresJson; ?>;
    const adicionados = new Set(<?php echo json_encode(array_column($participantesAtuais, 'id')); ?>);

    const searchEl = document.getElementById('trein-search');
    const suggestionsEl = document.getElementById('trein-suggestions');
    const listEl = document.getElementById('trein-participant-list');

    function getInitials(nome) {
        const parts = nome.trim().split(' ');
        return (parts[0][0] + (parts[1] ? parts[1][0] : '')).toUpperCase();
    }

    function updateCounters() {
        const n = listEl.querySelectorAll('.trein-participant-row').length;
        document.getElementById('trein-part-count-badge').textContent = n;
        document.getElementById('trein-part-count-text').textContent = n + ' participante(s) adicionado(s)';
        const empty = document.getElementById('trein-empty-msg');
        if (n === 0 && !empty) {
            const d = document.createElement('div');
            d.id = 'trein-empty-msg';
            d.className = 'trein-empty-state';
            d.textContent = 'Nenhum participante adicionado ainda.';
            listEl.appendChild(d);
        } else if (n > 0 && empty) {
            empty.remove();
        }
    }

    function removeParticipant(id, element) {
        adicionados.delete(id.toString());
        adicionados.delete(parseInt(id));
        element.closest('.trein-participant-row').remove();
        updateCounters();
    }

    function addParticipant(col) {
        if (adicionados.has(col.id)) return;
        adicionados.add(col.id);

        const div = document.createElement('div');
        div.className = 'trein-participant-row';
        div.dataset.id = col.id;
        div.innerHTML = `
            <div class="trein-avatar-sm">${getInitials(col.nome)}</div>
            <div class="trein-part-info">
                <div class="trein-part-name">${col.nome}</div>
                <div class="trein-part-dept">${col.departamento ?? ''}</div>
            </div>
            <input type="hidden" name="participantes[]" value="${col.id}">
            <button type="button" class="trein-remove-btn btn-remove-part" title="Remover">✕</button>
        `;
        
        div.querySelector('.btn-remove-part').addEventListener('click', function() {
            removeParticipant(col.id, this);
        });

        listEl.appendChild(div);
        searchEl.value = '';
        suggestionsEl.classList.remove('open');
        suggestionsEl.innerHTML = '';
        updateCounters();
    }

    searchEl.addEventListener('input', function() {
        const q = this.value.trim().toLowerCase();
        if (!q) { suggestionsEl.classList.remove('open'); suggestionsEl.innerHTML = ''; return; }

        const results = colaboradores.filter(c =>
            !adicionados.has(c.id) &&
            (c.nome.toLowerCase().includes(q) || (c.departamento || '').toLowerCase().includes(q))
        ).slice(0, 6);

        if (!results.length) { suggestionsEl.classList.remove('open'); suggestionsEl.innerHTML = ''; return; }

        suggestionsEl.innerHTML = results.map(c => `
            <div class="trein-sugg-item" data-id="${c.id}">
                <div class="trein-avatar-sm">${getInitials(c.nome)}</div>
                <div>
                    <div class="trein-sugg-name">${c.nome}</div>
                    <div class="trein-sugg-dept">${c.departamento ?? ''}</div>
                </div>
                <span style="margin-left:auto;font-size:18px;color:#9ca3af">+</span>
            </div>
        `).join('');

        suggestionsEl.querySelectorAll('.trein-sugg-item').forEach((el, i) => {
            el.addEventListener('click', () => addParticipant(results[i]));
        });

        suggestionsEl.classList.add('open');
    });

    document.addEventListener('click', function(e) {
        if (!e.target.closest('#trein-search') && !e.target.closest('#trein-suggestions')) {
            suggestionsEl.classList.remove('open');
        }
    });

    // Inicializa listeners para participantes já existentes (edição)
    document.querySelectorAll('.btn-remove-part').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.closest('.trein-participant-row').dataset.id;
            removeParticipant(id, this);
        });
    });
})();
</script>
