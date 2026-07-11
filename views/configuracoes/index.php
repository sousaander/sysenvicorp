<?php
/**
 * Painel de Configurações
 * Espera receber $menus = [
 *   [
 *     'url'        => string,
 *     'icone'      => string (classe Tabler/Boxicons, ex: 'ti ti-settings'),
 *     'titulo'     => string,
 *     'descricao'  => string,
 *     'cor'        => string (hex, ex: '#0ea5e9')  -- cor de destaque do módulo
 *     'badge'      => string|null (opcional, ex: 'Novo', '3 pendências')
 *   ],
 *   ...
 * ]
 */
?>
<div class="settings-panel">

    <div class="settings-header">
        <div class="settings-header__text">
            <span class="settings-eyebrow">Administração</span>
            <h2 class="settings-title">Painel de Configurações</h2>
            <p class="settings-subtitle">Gerencie módulos, permissões e parâmetros gerais do sistema.</p>
        </div>

        <div class="settings-search">
            <i class="ti ti-search"></i>
            <input type="text" id="settingsSearch" placeholder="Buscar configuração..." autocomplete="off">
        </div>
    </div>

    <div class="settings-grid" id="settingsGrid">
        <?php if (!empty($menus)): ?>
            <?php foreach ($menus as $menu):
                $cor    = $menu['cor']   ?? '#0ea5e9';
                $icone  = $menu['icone'] ?? 'ti ti-settings';
                $badge  = $menu['badge'] ?? null;
            ?>
                <a href="<?= $menu['url'] ?>"
                   class="settings-card"
                   data-search="<?= mb_strtolower($menu['titulo'] . ' ' . $menu['descricao']) ?>"
                   style="--accent: <?= $cor ?>;">

                    <div class="settings-card__top">
                        <div class="settings-card__icon">
                            <i class="<?= $icone ?>"></i>
                        </div>
                        <?php if ($badge): ?>
                            <span class="settings-card__badge"><?= $badge ?></span>
                        <?php endif; ?>
                        <i class="ti ti-arrow-up-right settings-card__arrow"></i>
                    </div>

                    <h3 class="settings-card__title"><?= $menu['titulo'] ?></h3>
                    <p class="settings-card__desc"><?= $menu['descricao'] ?></p>

                    <span class="settings-card__bar"></span>
                </a>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="settings-empty">
                <i class="ti ti-settings-off"></i>
                <p>Nenhuma configuração disponível para o seu perfil de acesso.</p>
            </div>
        <?php endif; ?>
    </div>

    <p class="settings-empty-search" id="settingsEmptySearch" hidden>
        <i class="ti ti-mood-empty"></i> Nenhum item encontrado para a busca informada.
    </p>
</div>

<style>
:root {
    --settings-panel-bg: #ffffff;
    --settings-panel-border: #e9ecf1;
    --settings-header-border: #eef0f4;
    --settings-eyebrow: #6b7eff;
    --settings-title-color: #1a1d29;
    --settings-subtitle-color: #8a8f9c;
    --settings-search-icon: #a4a9b5;
    --settings-search-bg: #f8f9fb;
    --settings-search-border: #e3e5eb;
    --settings-search-focus-bg: #ffffff;
    --settings-search-placeholder: #adb1bc;
    --settings-card-bg: #fbfbfd;
    --settings-card-border: #edeef2;
    --settings-card-hover-bg: #ffffff;
    --settings-card-arrow: #c7cad3;
    --settings-card-title: #1a1d29;
    --settings-card-desc: #8a8f9c;
    --settings-empty-text: #adb1bc;
    --settings-empty-icon: #d6d9e0;
    --settings-icon-bg: color-mix(in srgb, var(--accent) 14%, #ffffff);
    --settings-badge-bg: color-mix(in srgb, var(--accent) 14%, #ffffff);
}

body.dark-theme .settings-panel {
    background: var(--db-surface);
    border-color: var(--db-border);
}

body.dark-theme .settings-header {
    border-color: var(--db-border);
}

body.dark-theme .settings-title,
body.dark-theme .settings-card__title {
    color: var(--db-text);
}

body.dark-theme .settings-subtitle,
body.dark-theme .settings-card__desc,
body.dark-theme .settings-empty,
body.dark-theme .settings-empty-search {
    color: var(--db-text2);
}

body.dark-theme .settings-search i {
    color: var(--db-text2);
}

body.dark-theme .settings-search input {
    background: var(--db-surface2);
    border-color: var(--db-border);
    color: var(--db-text);
}

body.dark-theme .settings-search input::placeholder {
    color: var(--db-text3);
}

body.dark-theme .settings-card {
    background: var(--db-surface2);
    border-color: var(--db-border);
}

body.dark-theme .settings-card:hover {
    background: var(--db-surface);
}

body.dark-theme .settings-card__icon,
body.dark-theme .settings-card__badge {
    background: color-mix(in srgb, var(--accent) 14%, var(--db-surface2));
}

body.dark-theme .settings-card__arrow {
    color: var(--db-text3);
}

:root {
    --settings-panel-padding: 28px 32px 32px;
}

.settings-panel {
    background: var(--settings-panel-bg);
    border: 1px solid var(--settings-panel-border);
    border-radius: 18px;
    padding: var(--settings-panel-padding);
    box-shadow: 0 1px 2px rgba(16, 24, 40, .04), 0 10px 30px -12px rgba(16, 24, 40, .06);
}

/* ---------- Header ---------- */
.settings-header {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 24px;
    flex-wrap: wrap;
    padding-bottom: 22px;
    margin-bottom: 26px;
    border-bottom: 1px solid var(--settings-header-border);
}

.settings-eyebrow {
    display: block;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: var(--settings-eyebrow);
    margin-bottom: 6px;
}

.settings-title {
    font-size: 24px;
    font-weight: 800;
    color: var(--settings-title-color);
    letter-spacing: -.02em;
    margin: 0 0 4px;
}

.settings-subtitle {
    font-size: 14px;
    color: var(--settings-subtitle-color);
    margin: 0;
}

.settings-search {
    position: relative;
    width: 280px;
    max-width: 100%;
}

.settings-search i {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--settings-search-icon);
    font-size: 16px;
}

.settings-search input {
    width: 100%;
    padding: 10px 14px 10px 38px;
    border-radius: 10px;
    border: 1px solid var(--settings-search-border);
    background: var(--settings-search-bg);
    font-size: 14px;
    color: var(--settings-title-color);
    outline: none;
    transition: border-color .2s, background .2s, box-shadow .2s;
}

.settings-search input::placeholder { color: var(--settings-search-placeholder); }

.settings-search input:focus {
    background: var(--settings-search-focus-bg);
    border-color: #6b7eff;
    box-shadow: 0 0 0 3px rgba(107, 126, 255, .12);
}

/* ---------- Grid ---------- */
.settings-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(255px, 1fr));
    gap: 18px;
}

/* ---------- Card ---------- */
.settings-card {
    position: relative;
    display: flex;
    flex-direction: column;
    padding: 22px 20px 20px;
    background: var(--settings-card-bg);
    border: 1px solid var(--settings-card-border);
    border-radius: 16px;
    text-decoration: none;
    overflow: hidden;
    transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease, background .25s ease;
}

.settings-card:hover {
    transform: translateY(-3px);
    background: var(--settings-card-hover-bg);
    border-color: color-mix(in srgb, var(--accent) 35%, var(--settings-card-border));
    box-shadow: 0 16px 32px -16px color-mix(in srgb, var(--accent) 45%, transparent);
}

.settings-card__bar {
    position: absolute;
    left: 0; top: 0; bottom: 0;
    width: 3px;
    background: var(--accent);
    transform: scaleY(0);
    transform-origin: bottom;
    transition: transform .3s ease;
}

.settings-card:hover .settings-card__bar { transform: scaleY(1); }

.settings-card__top {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 16px;
}

.settings-card__icon {
    width: 46px;
    height: 46px;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
    font-size: 21px;
    color: var(--accent);
    background: var(--settings-icon-bg);
    transition: transform .3s ease, background .3s ease;
}

.settings-card:hover .settings-card__icon {
    background: var(--accent);
    color: #fff;
    transform: scale(1.06) rotate(-4deg);
}

.settings-card__badge {
    font-size: 11px;
    font-weight: 700;
    padding: 3px 9px;
    border-radius: 999px;
    color: var(--accent);
    background: var(--settings-badge-bg);
    white-space: nowrap;
}

.settings-card__arrow {
    margin-left: auto;
    color: var(--settings-card-arrow);
    font-size: 16px;
    opacity: 0;
    transform: translate(-4px, 4px);
    transition: opacity .25s ease, transform .25s ease, color .25s ease;
}

.settings-card:hover .settings-card__arrow {
    opacity: 1;
    transform: translate(0, 0);
    color: var(--accent);
}

.settings-card__title {
    font-size: 15.5px;
    font-weight: 700;
    color: var(--settings-card-title);
    margin: 0 0 6px;
    letter-spacing: -.01em;
}

.settings-card__desc {
    font-size: 13px;
    line-height: 1.55;
    color: var(--settings-card-desc);
    margin: 0;
}

/* ---------- Empty states ---------- */
.settings-empty,
.settings-empty-search {
    grid-column: 1 / -1;
    text-align: center;
    padding: 48px 16px;
    color: var(--settings-empty-text);
    font-size: 14px;
}

.settings-empty i,
.settings-empty-search i { display: block; font-size: 30px; margin-bottom: 10px; color: var(--settings-empty-icon); }

/* ---------- Responsive ---------- */
@media (max-width: 640px) {
    .settings-panel { padding: 20px; }
    .settings-header { align-items: stretch; }
    .settings-search { width: 100%; }
}
</style>

<script>
(function () {
    const input = document.getElementById('settingsSearch');
    const grid  = document.getElementById('settingsGrid');
    const empty = document.getElementById('settingsEmptySearch');
    if (!input || !grid) return;

    input.addEventListener('input', function () {
        const term = this.value.trim().toLowerCase();
        const cards = grid.querySelectorAll('.settings-card');
        let visibleCount = 0;

        cards.forEach(card => {
            const match = card.dataset.search.includes(term);
            card.style.display = match ? '' : 'none';
            if (match) visibleCount++;
        });

        if (empty) empty.hidden = visibleCount !== 0;
    });
})();
</script>
