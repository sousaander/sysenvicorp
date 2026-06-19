<div class="flex justify-between items-center mb-6">
    <div>
        <h2 class="text-2xl font-bold"><?php echo htmlspecialchars($pageTitle); ?></h2>
        <p class="text-gray-600">Exporte ou importe dados do sistema.</p>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-8">
    <!-- Seção de Exportação -->
    <div class="bg-white p-6 rounded-lg shadow-md">
        <h3 class="text-xl font-bold text-gray-800 mb-4">Exportar Dados</h3>
        <p class="text-gray-600 mb-6 text-sm">
            Gere um arquivo de backup dos dados do sistema. A opção "Completo" gera um arquivo SQL com todos os dados. A opção por módulo gera um arquivo CSV.
        </p>

        <form action="<?php echo BASE_URL; ?>/configuracoes/exportar" method="POST">
            <div class="space-y-4">
                <div>
                    <label for="tipo_exportacao" class="block text-sm font-medium text-gray-700">Tipo de Backup</label>
                    <select id="tipo_exportacao" name="tipo_exportacao" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                        <option value="completo">Backup Completo (SQL)</option>
                        <option value="financeiro">Módulo Financeiro (CSV)</option>
                        <option value="rh">Módulo RH (CSV)</option>
                        <option value="projetos">Módulo Projetos (CSV)</option>
                        <option value="contratos">Módulo Contratos (CSV)</option>
                    </select>
                </div>
                <div>
                    <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                        Exportar Backup
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Seção de Importação -->
    <div class="bg-white p-6 rounded-lg shadow-md">
        <h3 class="text-xl font-bold text-gray-800 mb-4">Importar Dados (Módulo)</h3>
        <p class="text-gray-600 mb-6 text-sm">
            Restaure os dados de um módulo a partir de um arquivo CSV. Escolha o modo de importação: "Atualizar/Inserir" para sincronizar dados sem perdas, ou "Substituir" para apagar os dados atuais do módulo e inserir os do arquivo.
        </p>

        <form action="<?php echo BASE_URL; ?>/configuracoes/importar" method="POST" enctype="multipart/form-data">
            <div class="space-y-4">
                <div>
                    <label for="arquivo_csv" class="block text-sm font-medium text-gray-700">Arquivo CSV do Módulo</label>
                    <input type="file" id="arquivo_csv" name="arquivo_csv" accept=".csv" required class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                </div>

                <!-- NOVO: Opções de Modo de Importação -->
                <div class="pt-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Modo de Importação</label>
                    <div class="flex flex-col sm:flex-row gap-4">
                        <div class="flex-1 flex items-center p-3 border rounded-lg has-[:checked]:bg-sky-50 has-[:checked]:border-sky-300 transition-all duration-200">
                            <input id="import-mode-upsert" type="radio" name="import_mode" value="upsert" checked class="focus:ring-sky-500 h-4 w-4 text-sky-600 border-gray-300">
                            <label for="import-mode-upsert" class="ml-3 block text-sm text-gray-700 cursor-pointer">
                                <span class="font-bold">Atualizar / Inserir</span>
                                <span class="block text-xs text-gray-500">Atualiza registros existentes e insere novos. Nenhum dado é apagado. (Recomendado)</span>
                            </label>
                        </div>
                        <div class="flex-1 flex items-center p-3 border rounded-lg has-[:checked]:bg-red-50 has-[:checked]:border-red-300 transition-all duration-200">
                            <input id="import-mode-replace" type="radio" name="import_mode" value="replace" class="focus:ring-red-500 h-4 w-4 text-red-600 border-gray-300">
                            <label for="import-mode-replace" class="ml-3 block text-sm text-gray-700 cursor-pointer">
                                <span class="font-bold">Substituir</span>
                                <span class="block text-xs text-gray-500">Apaga TODOS os dados do módulo e insere os do arquivo. Use com cuidado.</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div>
                    <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM6.293 6.707a1 1 0 010-1.414l3-3a1 1 0 011.414 0l3 3a1 1 0 01-1.414 1.414L11 5.414V13a1 1 0 11-2 0V5.414L7.707 6.707a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                        </svg>
                        Importar Dados
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Seção de Restauração Completa (SQL) -->
    <div class="md:col-span-2 bg-white p-6 rounded-lg shadow-md border-l-4 border-red-500 mt-4">
        <h3 class="text-xl font-bold text-red-700 mb-4">Zona de Perigo: Restaurar Backup Completo (SQL)</h3>
        <p class="text-gray-600 mb-6 text-sm">
            Restaure o banco de dados inteiro a partir de um arquivo <code>.sql</code> gerado anteriormente.
            <br>
            <strong class="text-red-600">ATENÇÃO CRÍTICA:</strong> Esta ação irá <strong>APAGAR TODOS</strong> os dados atuais do sistema e substituí-los pelos dados do backup. Certifique-se de ter um backup recente antes de prosseguir.
        </p>

        <form action="<?php echo BASE_URL; ?>/configuracoes/restaurar" method="POST" enctype="multipart/form-data" onsubmit="return confirm('TEM CERTEZA ABSOLUTA? Todos os dados atuais serão perdidos e substituídos pelo backup. Esta ação é irreversível.');">
            <div class="flex flex-col md:flex-row items-end gap-4">
                <div class="flex-1 w-full">
                    <label for="arquivo_sql" class="block text-sm font-medium text-gray-700">Arquivo SQL ou ZIP de Backup</label>
                    <input type="file" id="arquivo_sql" name="arquivo_sql" accept=".sql,.zip" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-red-50 file:text-red-700 hover:file:bg-red-100" required>
                </div>
                <button type="submit" class="w-full md:w-auto flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a1 1 0 01-1.5.866l-2.5-1.5a1 1 0 010-1.732l2.5-1.5A1 1 0 014 2zm1 4a1 1 0 11-2 0 1 1 0 012 0zm5-4a1 1 0 011 1v2.101a1 1 0 01-1.5.866l-2.5-1.5a1 1 0 010-1.732l2.5-1.5A1 1 0 0110 2zm1 4a1 1 0 11-2 0 1 1 0 012 0zM5 11a1 1 0 011-1h8a1 1 0 110 2H6a1 1 0 01-1-1zm0 4a1 1 0 011-1h8a1 1 0 110 2H6a1 1 0 01-1-1z" clip-rule="evenodd" />
                    </svg>
                    Restaurar Sistema
                </button>
            </div>
        </form>
    </div>

    <!-- Seção de Reset de Fábrica -->
    <div class="md:col-span-2 bg-red-50 p-6 rounded-lg shadow-md border-2 border-red-600 mt-4">
        <h3 class="text-xl font-bold text-red-800 mb-4 flex items-center">
            <i class='bx bxs-bomb text-2xl mr-2'></i> Reset de Fábrica (Apagar Tudo)
        </h3>
        <p class="text-red-700 mb-6 text-sm">
            Esta ação irá <strong>APAGAR TODOS OS DADOS</strong> do sistema (clientes, financeiro, projetos, etc.) e restaurar as configurações originais.
            <br>
            O sistema voltará ao estado inicial com apenas o usuário <strong>admin@sysenvicorp.com</strong> (Senha: <strong>admin123</strong>).
            <br>
            <span class="font-bold">Isso não apaga os arquivos de backup salvos na pasta storage.</span>
        </p>

        <form action="<?php echo BASE_URL; ?>/configuracoes/resetarSistema" method="POST" onsubmit="return prompt('ATENÇÃO MÁXIMA: Isso apagará tudo! Para confirmar, digite RESETAR na caixa abaixo:') === 'RESETAR'">
            <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-bold text-black bg-red-700 hover:bg-red-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 uppercase tracking-wider">
                CONFIRMAR RESET DE FÁBRICA
            </button>
        </form>
    </div>

    <!-- Seção de Backups Automáticos -->
    <div class="md:col-span-2 bg-white p-6 rounded-lg shadow-md mt-8">
        <h3 class="text-xl font-bold text-gray-800 mb-4">Backups Automáticos Disponíveis</h3>

        <?php if (empty($backupFiles)) : ?>
            <p class="text-gray-500 text-center py-4">Nenhum backup automático encontrado na pasta <code>storage/backups/</code>.</p>
        <?php else : ?>
            <div class="overflow-x-auto border rounded-lg">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Arquivo</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tamanho</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                            <th scope="col" class="relative px-6 py-3"><span class="sr-only">Ações</span></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($backupFiles as $file) : ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    <i class='bx bxs-file-zip text-gray-500 mr-2'></i>
                                    <?php echo htmlspecialchars($file['name']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo number_format($file['size'] / 1024 / 1024, 2); ?> MB
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo date('d/m/Y H:i:s', $file['date']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-4">
                                    <a href="<?php echo BASE_URL; ?>/configuracoes/downloadBackup?file=<?php echo urlencode($file['name']); ?>" class="text-indigo-600 hover:text-indigo-900" title="Baixar Backup">
                                        <i class='bx bxs-download text-xl'></i>
                                    </a>
                                    <form action="<?php echo BASE_URL; ?>/configuracoes/restaurarFromPath" method="POST" class="inline" onsubmit="return confirm('TEM CERTEZA ABSOLUTA? Esta ação irá restaurar o sistema para o estado deste backup. Todos os dados atuais serão perdidos e esta ação é IRREVERSÍVEL.');">
                                        <input type="hidden" name="filename" value="<?php echo htmlspecialchars($file['name']); ?>">
                                        <button type="submit" class="text-red-600 hover:text-red-900" title="Restaurar a partir deste arquivo">
                                            <i class='bx bx-history text-xl'></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

</div>