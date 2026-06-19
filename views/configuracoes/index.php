<div class="bg-white p-6 rounded-lg shadow-lg">
    <h2 class="text-2xl font-bold mb-6 text-gray-800 border-b pb-4">Painel de Configurações</h2>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php if (!empty($menus)): ?>
            <?php foreach ($menus as $menu): ?>
                <a href="<?= $menu['url'] ?>" class="block p-6 bg-gray-50 rounded-xl border border-gray-200 hover:bg-white hover:border-sky-300 hover:shadow-xl transition-all duration-300 group">
                    <div class="flex items-center mb-3">
                        <div class="<?= $menu['bg'] ?> <?= $menu['cor'] ?> p-4 rounded-xl mr-4 group-hover:rotate-6 transition-transform">
                            <i class='<?= $menu['icone'] ?> text-3xl'></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-800"><?= $menu['titulo'] ?></h3>
                    </div>
                    <p class="text-gray-500 text-sm leading-relaxed"><?= $menu['descricao'] ?></p>
                </a>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>