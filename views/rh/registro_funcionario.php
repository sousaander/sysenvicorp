<?php
// Inicia a verificação dos dados do formulário
$formData = $_SESSION['form_data'] ?? null;

// Se houver dados de formulário na sessão (vindo de um erro de validação),
// mescla esses dados com a variável $funcionario. Isso repreenche o formulário.
if ($formData) {
    // Se $funcionario já existe (modo de edição), mescla os dados. Senão, apenas usa os dados do formulário.
    $funcionario = isset($funcionario) ? array_merge($funcionario, $formData) : $formData;
    unset($_SESSION['form_data']); // Limpa os dados da sessão após o uso para não repreencher em futuras visitas.
}

$isEdit = isset($funcionario) && !empty($funcionario['id']);
?>

<h2 class="text-2xl font-bold mb-4"><?php echo $isEdit ? 'Editar Funcionário' : 'Cadastro de Novo Funcionário'; ?></h2>
<p class="mb-6 text-gray-600">Preencha os dados abaixo para <?php echo $isEdit ? 'atualizar o colaborador' : 'registrar um novo colaborador no sistema'; ?>.</p>

<div class="bg-white p-8 rounded-lg shadow-xl max-w-6xl mx-auto">
    <form action="<?php echo BASE_URL; ?>/rh/salvar" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
        <!-- Campo oculto para enviar o ID durante a edição -->
        <?php if ($isEdit): ?>
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($funcionario['id']); ?>">
        <?php endif; ?>

        <!-- Navegação por Abas -->
        <div class="border-b border-gray-200 mb-6">
            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                <button type="button" class="tab-button border-indigo-500 text-indigo-600 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm" data-tab="pessoal">Dados Pessoais</button>
                <button type="button" class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm" data-tab="dependentes">Dependentes</button>
                <button type="button" class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm" data-tab="profissional">Dados Profissionais</button>
                <button type="button" class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm" data-tab="bancarios">Dados Bancários</button>
            </nav>
        </div>

        <!-- Conteúdo das Abas -->
        <div id="tab-content">
            <!-- Aba: Dados Pessoais -->
            <div id="pessoal" class="tab-pane">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div class="md:col-span-1">
                        <label class="block text-sm font-medium text-gray-700">Foto 3x4</label>
                        <div class="mt-1 flex items-center">
                            <span class="inline-block h-24 w-24 rounded-md overflow-hidden bg-gray-100">
                                <img id="foto-preview" src="<?php echo ($isEdit && !empty($funcionario['foto_url'])) ? htmlspecialchars($funcionario['foto_url']) : 'https://placehold.co/96x96/E2E8F0/4A5568?text=Foto'; ?>" alt="Preview da foto" class="h-full w-full object-cover">
                            </span>
                            <input type="file" name="foto" id="foto" class="hidden" accept="image/*">
                            <label for="foto" class="ml-5 bg-white py-2 px-3 border border-gray-300 rounded-md shadow-sm text-sm leading-4 font-medium text-gray-700 hover:bg-gray-50 cursor-pointer">
                                Alterar
                            </label>
                        </div>
                    </div>
                    <div class="md:col-span-3 grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="md:col-span-2"><label for="nome" class="block text-sm font-medium text-gray-700">Nome Completo</label><input type="text" id="nome" name="nome" required value="<?php echo $isEdit ? htmlspecialchars($funcionario['nome']) : ''; ?>" class="input-form"></div>
                        <div><label for="data_nascimento" class="block text-sm font-medium text-gray-700">Data de Nascimento</label><input type="date" id="data_nascimento" name="data_nascimento" value="<?php echo $isEdit ? htmlspecialchars($funcionario['data_nascimento'] ?? '') : ''; ?>" class="input-form"></div>
                        <div class="md:col-span-3"><label for="nome_mae" class="block text-sm font-medium text-gray-700">Nome da Mãe</label><input type="text" id="nome_mae" name="nome_mae" value="<?php echo $isEdit ? htmlspecialchars($funcionario['nome_mae'] ?? '') : ''; ?>" class="input-form"></div>

                        <div><label for="cpf" class="block text-sm font-medium text-gray-700">CPF</label><input type="text" id="cpf" name="cpf" required value="<?php echo $isEdit ? htmlspecialchars($funcionario['cpf'] ?? '') : ''; ?>" class="input-form" inputmode="numeric" pattern="[0-9]{11}" minlength="11" maxlength="11" title="O CPF deve conter exatamente 11 dígitos."></div>
                        <div>
                            <label for="estado_civil" class="block text-sm font-medium text-gray-700">Estado Civil</label>
                            <select id="estado_civil" name="estado_civil" class="input-form">
                                <option value="">Selecione...</option>
                                <?php
                                $estadosCivis = ['Solteiro(a)', 'Casado(a)', 'Divorciado(a)', 'Viúvo(a)', 'União Estável'];
                                $estadoCivilSalvo = $isEdit ? ($funcionario['estado_civil'] ?? '') : '';
                                foreach ($estadosCivis as $ec): ?>
                                    <option value="<?php echo $ec; ?>" <?php echo ($estadoCivilSalvo == $ec) ? 'selected' : ''; ?>><?php echo $ec; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label for="tipo_sanguineo" class="block text-sm font-medium text-gray-700">Tipagem Sanguínea</label>
                            <select id="tipo_sanguineo" name="tipo_sanguineo" class="input-form">
                                <option value="">Selecione...</option>
                                <?php
                                $tiposSanguineos = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
                                $tipoSanguineoSalvo = $isEdit ? ($funcionario['tipo_sanguineo'] ?? '') : '';
                                foreach ($tiposSanguineos as $ts): ?>
                                    <option value="<?php echo $ts; ?>" <?php echo ($tipoSanguineoSalvo == $ts) ? 'selected' : ''; ?>><?php echo $ts; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div><label for="celular" class="block text-sm font-medium text-gray-700">Celular</label><input type="text" id="celular" name="celular" value="<?php echo $isEdit ? htmlspecialchars($funcionario['celular'] ?? '') : ''; ?>" class="input-form"></div>

                        <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="rg" class="block text-sm font-medium text-gray-700">RG</label>
                                <div class="mt-1 flex rounded-md shadow-sm">
                                    <input type="text" id="rg" name="rg" value="<?php echo $isEdit ? htmlspecialchars($funcionario['rg'] ?? '') : ''; ?>" class="input-form flex-1 rounded-none rounded-l-md !mt-0" placeholder="Número do RG" inputmode="numeric" pattern="[0-9]*">
                                    <label for="anexo_rg" class="relative -ml-px inline-flex items-center space-x-2 px-3 py-2 border border-gray-300 text-sm font-medium rounded-r-md text-gray-700 bg-gray-50 hover:bg-gray-100 cursor-pointer" title="Anexar documento">
                                        <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M15.621 4.379a3 3 0 00-4.242 0l-7 7a3 3 0 004.241 4.243h.001l.497-.497a.75.75 0 011.06 1.06l-.497.497a4.5 4.5 0 11-6.364-6.364l7-7a4.5 4.5 0 016.364 6.364l-3 3a.75.75 0 01-1.06-1.06l3-3a3 3 0 00-4.242-4.242z" clip-rule="evenodd" />
                                        </svg>
                                        <span>Anexar</span>
                                    </label>
                                    <input id="anexo_rg" name="anexo_rg" type="file" class="hidden">
                                </div>
                                <?php if ($isEdit && !empty($funcionario['anexo_rg_path'])): ?>
                                    <div class="mt-2 flex items-center gap-2">
                                        <span class="text-xs text-gray-500">Arquivo atual:</span>
                                        <a href="<?php echo BASE_URL . '/storage/documentos_pessoais/' . htmlspecialchars($funcionario['anexo_rg_path']); ?>" 
                                           target="_blank" 
                                           class="inline-flex items-center px-2 py-1 bg-gray-100 text-indigo-700 text-xs font-medium rounded border border-indigo-200 hover:bg-indigo-50 transition">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            Visualizar RG
                                        </a>
                                    </div>
                                <?php endif; ?>
                                <div id="anexo_rg_filename" class="text-xs text-gray-500 mt-1"></div>
                                <div id="preview_anexo_rg"></div>
                            </div>
                            <div>
                                <label for="rg_emissor" class="block text-sm font-medium text-gray-700">Órgão Emissor</label>
                                <input type="text" id="rg_emissor" name="rg_emissor" value="<?php echo $isEdit ? htmlspecialchars($funcionario['rg_emissor'] ?? '') : ''; ?>" class="input-form" placeholder="Ex: SSP/SP">
                            </div>
                        </div>

                        <div>
                            <label for="reservista" class="block text-sm font-medium text-gray-700">Reservista</label>
                            <div class="mt-1 flex rounded-md shadow-sm">
                                <input type="text" id="reservista" name="reservista" value="<?php echo $isEdit ? htmlspecialchars($funcionario['reservista'] ?? '') : ''; ?>" class="input-form flex-1 rounded-none rounded-l-md !mt-0" placeholder="Número da Reservista" inputmode="numeric" pattern="[0-9]{12}" minlength="12" maxlength="12" title="A reservista deve conter 12 dígitos.">
                                <label for="anexo_reservista" class="relative -ml-px inline-flex items-center space-x-2 px-3 py-2 border border-gray-300 text-sm font-medium rounded-r-md text-gray-700 bg-gray-50 hover:bg-gray-100 cursor-pointer" title="Anexar documento">
                                    <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M15.621 4.379a3 3 0 00-4.242 0l-7 7a3 3 0 004.241 4.243h.001l.497-.497a.75.75 0 011.06 1.06l-.497.497a4.5 4.5 0 11-6.364-6.364l7-7a4.5 4.5 0 016.364 6.364l-3 3a.75.75 0 01-1.06-1.06l3-3a3 3 0 00-4.242-4.242z" clip-rule="evenodd" />
                                    </svg>
                                    <span>Anexar</span>
                                </label>
                                <input id="anexo_reservista" name="anexo_reservista" type="file" class="hidden">
                            </div>
                            <?php if ($isEdit && !empty($funcionario['anexo_reservista_path'])): ?>
                                <div class="mt-2 flex items-center gap-2">
                                    <span class="text-xs text-gray-500">Arquivo atual:</span>
                                    <a href="<?php echo BASE_URL . '/storage/documentos_pessoais/' . htmlspecialchars($funcionario['anexo_reservista_path']); ?>" 
                                       target="_blank" 
                                       class="inline-flex items-center px-2 py-1 bg-gray-100 text-indigo-700 text-xs font-medium rounded border border-indigo-200 hover:bg-indigo-50 transition">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        Visualizar Reservista
                                    </a>
                                </div>
                            <?php endif; ?>
                            <div id="anexo_reservista_filename" class="text-xs text-gray-500 mt-1"></div>
                            <div id="preview_anexo_reservista"></div>
                        </div>

                        <!-- Campo CNH com Categorias -->
                        <div>
                            <label for="cnh" class="block text-sm font-medium text-gray-700">CNH</label>
                            <div class="flex items-center mt-1 rounded-md shadow-sm">
                                <input type="text" id="cnh" name="cnh" placeholder="Número da CNH" value="<?php echo $isEdit ? htmlspecialchars($funcionario['cnh'] ?? '') : ''; ?>" class="input-form flex-grow rounded-none rounded-l-md !mt-0" inputmode="numeric" pattern="[0-9]{11}" minlength="11" maxlength="11" title="A CNH deve conter 11 dígitos.">
                                <select name="cnh_categoria" class="input-form rounded-none border-l-0 border-r-0 !mt-0" style="max-width: 80px;">
                                    <option value="">Cat.</option>
                                    <?php
                                    $categoriasCNH = ['A', 'B', 'C', 'D', 'E', 'AB', 'AC', 'AD', 'AE'];
                                    $categoriaSalva = $isEdit ? ($funcionario['cnh_categoria'] ?? '') : '';
                                    foreach ($categoriasCNH as $cat): ?>
                                        <option value="<?php echo $cat; ?>" <?php echo ($categoriaSalva == $cat) ? 'selected' : ''; ?>><?php echo $cat; ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <label for="anexo_cnh" class="relative -ml-px inline-flex items-center space-x-2 px-3 py-2 border border-gray-300 text-sm font-medium rounded-r-md text-gray-700 bg-gray-50 hover:bg-gray-100 cursor-pointer" title="Anexar documento">
                                    <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M15.621 4.379a3 3 0 00-4.242 0l-7 7a3 3 0 004.241 4.243h.001l.497-.497a.75.75 0 011.06 1.06l-.497.497a4.5 4.5 0 11-6.364-6.364l7-7a4.5 4.5 0 016.364 6.364l-3 3a.75.75 0 01-1.06-1.06l3-3a3 3 0 00-4.242-4.242z" clip-rule="evenodd" />
                                    </svg>
                                    <span>Anexar</span>
                                </label>
                                <input id="anexo_cnh" name="anexo_cnh" type="file" class="hidden">
                            </div>
                            <?php if ($isEdit && !empty($funcionario['anexo_cnh_path'])): ?>
                                <div class="mt-2 flex items-center gap-2">
                                    <span class="text-xs text-gray-500">Arquivo atual:</span>
                                    <a href="<?php echo BASE_URL . '/storage/documentos_pessoais/' . htmlspecialchars($funcionario['anexo_cnh_path']); ?>" 
                                       target="_blank" 
                                       class="inline-flex items-center px-2 py-1 bg-gray-100 text-indigo-700 text-xs font-medium rounded border border-indigo-200 hover:bg-indigo-50 transition">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        Visualizar CNH
                                    </a>
                                </div>
                            <?php endif; ?>
                            <div id="anexo_cnh_filename" class="text-xs text-gray-500 mt-1"></div>
                            <div id="preview_anexo_cnh"></div>
                        </div>

                        <div>
                            <label for="titulo_eleitor" class="block text-sm font-medium text-gray-700">Título de Eleitor</label>
                            <div class="mt-1 flex rounded-md shadow-sm">
                                <input type="text" id="titulo_eleitor" name="titulo_eleitor" value="<?php echo $isEdit ? htmlspecialchars($funcionario['titulo_eleitor'] ?? '') : ''; ?>" class="input-form flex-1 rounded-none rounded-l-md !mt-0" placeholder="Número do Título" inputmode="numeric" pattern="[0-9]{12}" minlength="12" maxlength="12" title="O Título de Eleitor deve conter 12 dígitos.">
                                <label for="anexo_titulo" class="relative -ml-px inline-flex items-center space-x-2 px-3 py-2 border border-gray-300 text-sm font-medium rounded-r-md text-gray-700 bg-gray-50 hover:bg-gray-100 cursor-pointer" title="Anexar documento">
                                    <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M15.621 4.379a3 3 0 00-4.242 0l-7 7a3 3 0 004.241 4.243h.001l.497-.497a.75.75 0 011.06 1.06l-.497.497a4.5 4.5 0 11-6.364-6.364l7-7a4.5 4.5 0 016.364 6.364l-3 3a.75.75 0 01-1.06-1.06l3-3a3 3 0 00-4.242-4.242z" clip-rule="evenodd" />
                                    </svg>
                                    <span>Anexar</span>
                                </label>
                                <input id="anexo_titulo" name="anexo_titulo" type="file" class="hidden">
                            </div>
                            <?php if ($isEdit && !empty($funcionario['anexo_titulo_path'])): ?>
                                <div class="mt-2 flex items-center gap-2">
                                    <span class="text-xs text-gray-500">Arquivo atual:</span>
                                    <a href="<?php echo BASE_URL . '/storage/documentos_pessoais/' . htmlspecialchars($funcionario['anexo_titulo_path']); ?>" 
                                       target="_blank" 
                                       class="inline-flex items-center px-2 py-1 bg-gray-100 text-indigo-700 text-xs font-medium rounded border border-indigo-200 hover:bg-indigo-50 transition">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        Visualizar Título
                                    </a>
                                </div>
                            <?php endif; ?>
                            <div id="anexo_titulo_filename" class="text-xs text-gray-500 mt-1"></div>
                            <div id="preview_anexo_titulo"></div>
                        </div>
                        <div><label for="ctps" class="block text-sm font-medium text-gray-700">CTPS (nº, série, UF)</label><input type="text" id="ctps" name="ctps" value="<?php echo $isEdit ? htmlspecialchars($funcionario['ctps'] ?? '') : ''; ?>" class="input-form" placeholder="Ex: 1234567 0001 SP"></div>
                        <div><label for="pis" class="block text-sm font-medium text-gray-700">PIS</label><input type="text" id="pis" name="pis" value="<?php echo $isEdit ? htmlspecialchars($funcionario['pis'] ?? '') : ''; ?>" class="input-form" inputmode="numeric" pattern="[0-9]{11}" minlength="11" maxlength="11" title="O PIS deve conter exatamente 11 dígitos."></div>
                        <div class="md:col-span-3"><label for="email" class="block text-sm font-medium text-gray-700">E-mail Pessoal</label><input type="email" id="email" name="email" required value="<?php echo $isEdit ? htmlspecialchars($funcionario['email']) : ''; ?>" class="input-form"></div>
                    </div>
                </div>
                <h3 class="text-lg font-medium leading-6 text-gray-900 border-t pt-6 mt-6">Endereço</h3>
                <div class="grid grid-cols-1 md:grid-cols-6 gap-6 mt-4">
                    <div class="md:col-span-4"><label for="endereco" class="block text-sm font-medium text-gray-700">Endereço (Rua, Nº)</label><input type="text" id="endereco" name="endereco" value="<?php echo $isEdit ? htmlspecialchars($funcionario['endereco'] ?? '') : ''; ?>" class="input-form"></div>
                    <div class="md:col-span-2"><label for="bairro" class="block text-sm font-medium text-gray-700">Bairro</label><input type="text" id="bairro" name="bairro" value="<?php echo $isEdit ? htmlspecialchars($funcionario['bairro'] ?? '') : ''; ?>" class="input-form"></div>
                    <div class="md:col-span-2">
                        <label for="cep" class="block text-sm font-medium text-gray-700">CEP</label>
                        <div class="flex rounded-md shadow-sm">
                            <input type="text" id="cep" name="cep" value="<?php echo $isEdit ? htmlspecialchars($funcionario['cep'] ?? '') : ''; ?>" class="input-form flex-1 rounded-r-none" placeholder="Digite o CEP">
                            <button type="button" id="buscar-cep-btn" class="inline-flex items-center px-3 rounded-l-none rounded-r-md border border-l-0 border-gray-300 bg-gray-50 text-gray-500 text-sm hover:bg-gray-100">
                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div class="md:col-span-2"><label for="cidade" class="block text-sm font-medium text-gray-700">Cidade</label><input type="text" id="cidade" name="cidade" value="<?php echo $isEdit ? htmlspecialchars($funcionario['cidade'] ?? '') : ''; ?>" class="input-form"></div>
                    <div><label for="uf" class="block text-sm font-medium text-gray-700">UF</label><input type="text" id="uf" name="uf" value="<?php echo $isEdit ? htmlspecialchars($funcionario['uf'] ?? '') : ''; ?>" class="input-form"></div>
                </div>
            </div>

            <!-- Aba: Dependentes -->
            <div id="dependentes" class="tab-pane hidden">
                <div id="dependentes-container" class="space-y-6">
                    <!-- Blocos de dependentes serão inseridos aqui via JS -->
                </div>

                <div class="mt-6">
                    <button type="button" id="add-dependente-btn" class="flex items-center text-indigo-600 hover:text-indigo-800 font-medium text-sm">
                        <svg class="h-5 w-5 mr-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg>
                        Adicionar Dependente
                    </button>
                </div>
            </div>

            <!-- Template para um novo dependente -->
            <template id="dependente-template">
                <div class="dependente-bloco grid grid-cols-1 md:grid-cols-4 gap-6 pt-6 border-t first:border-t-0 first:pt-0">
                    <div>
                        <label for="dep_nome___INDEX__" class="block text-sm font-medium text-gray-700">Nome do Dependente</label>
                        <input type="text" id="dep_nome___INDEX__" name="dependentes[__INDEX__][nome]" class="input-form">
                    </div>
                    <div>
                        <label for="dep_parentesco___INDEX__" class="block text-sm font-medium text-gray-700">Grau de Parentesco</label>
                        <select id="dep_parentesco___INDEX__" name="dependentes[__INDEX__][parentesco]" class="input-form">
                            <option value="">Selecione...</option>
                            <option value="Filho(a)">Filho(a)</option>
                            <option value="Cônjuge">Cônjuge</option>
                            <option value="Pai">Pai</option>
                            <option value="Mãe">Mãe</option>
                            <option value="Outro">Outro</option>
                        </select>
                    </div>
                    <div>
                        <label for="dep_nasc___INDEX__" class="block text-sm font-medium text-gray-700">Data de Nascimento</label>
                        <input type="date" id="dep_nasc___INDEX__" name="dependentes[__INDEX__][nascimento]" class="input-form">
                    </div>
                    <div class="flex items-end">
                        <div class="flex-grow">
                            <label for="dep_cpf___INDEX__" class="block text-sm font-medium text-gray-700">CPF</label>
                            <input type="text" id="dep_cpf___INDEX__" name="dependentes[__INDEX__][cpf]" class="input-form">
                        </div>
                        <button type="button" class="remover-dependente-btn ml-2 p-2 text-red-500 hover:text-red-700">
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </template>
            <!-- Aba: Dados Profissionais -->
            <div id="profissional" class="tab-pane hidden">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div><label for="cargo" class="block text-sm font-medium text-gray-700">Função (Cargo)</label><input type="text" id="cargo" name="cargo" required value="<?php echo $isEdit ? htmlspecialchars($funcionario['cargo']) : ''; ?>" class="input-form"></div>
                    <div><label for="setor" class="block text-sm font-medium text-gray-700">Setor</label><input type="text" id="setor" name="setor" required value="<?php echo $isEdit ? htmlspecialchars($funcionario['setor']) : ''; ?>" class="input-form"></div>
                    <div><label for="data_admissao" class="block text-sm font-medium text-gray-700">Data de Admissão</label><input type="date" id="data_admissao" name="data_admissao" required value="<?php echo $isEdit ? htmlspecialchars($funcionario['data_admissao']) : ''; ?>" class="input-form"></div>
                    <div><label for="salario" class="block text-sm font-medium text-gray-700">Salário (R$)</label><input type="number" step="0.01" id="salario" name="salario" value="<?php echo $isEdit ? htmlspecialchars($funcionario['salario'] ?? '') : ''; ?>" class="input-form"></div>
                    <div><label for="carga_horaria" class="block text-sm font-medium text-gray-700">Carga Horária (semanal)</label><input type="number" id="carga_horaria" name="carga_horaria" value="<?php echo $isEdit ? htmlspecialchars($funcionario['carga_horaria'] ?? '') : '44'; ?>" class="input-form"></div>
                </div>
                <?php if ($isEdit): ?>
                    <div class="mt-6 border-t pt-6">
                        <label for="status" class="block text-sm font-medium text-gray-700">Status do Funcionário</label>
                        <select id="status" name="status" class="input-form max-w-xs mt-1">
                            <option value="Ativo" <?php echo (isset($funcionario['status']) && strtolower($funcionario['status']) === 'ativo') ? 'selected' : ''; ?>>Ativo</option>
                            <option value="Inativo" <?php echo (isset($funcionario['status']) && strtolower($funcionario['status']) === 'inativo') ? 'selected' : ''; ?>>Inativo</option>
                        </select>
                    </div>
                <?php endif; ?>
                <div class="mt-6">
                    <label for="habilidades" class="block text-sm font-medium text-gray-700">Habilidades Profissionais</label>
                    <textarea id="habilidades" name="habilidades" rows="4" class="input-form" placeholder="Ex: Pacote Office, Power BI, Comunicação Interpessoal, Liderança, etc."><?php echo $isEdit ? htmlspecialchars($funcionario['habilidades'] ?? '') : ''; ?></textarea>
                </div>
            </div>

            <!-- Aba: Dados Bancários -->
            <div id="bancarios" class="tab-pane hidden">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label for="banco" class="block text-sm font-medium text-gray-700">Banco</label>
                        <select id="banco" name="banco" class="input-form">
                            <option value="">Selecione o banco...</option>
                            <?php
                            $bancos = [
                                '001' => '001 - Banco do Brasil S.A.',
                                '104' => '104 - Caixa Econômica Federal',
                                '237' => '237 - Banco Bradesco S.A.',
                                '341' => '341 - Itaú Unibanco S.A.',
                                '033' => '033 - Banco Santander (Brasil) S.A.',
                                '745' => '745 - Banco Citibank S.A.',
                                '260' => '260 - Nu Pagamentos S.A. (Nubank)',
                                '077' => '077 - Banco Inter S.A.',
                                '336' => '336 - Banco C6 S.A.'
                                // Adicione outros bancos se necessário
                            ];
                            $bancoSalvo = $isEdit ? ($funcionario['banco'] ?? '') : '';
                            foreach ($bancos as $codigo => $nome): ?>
                                <option value="<?php echo $nome; ?>" <?php echo ($bancoSalvo == $nome) ? 'selected' : ''; ?>>
                                    <?php echo $nome; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div><label for="agencia" class="block text-sm font-medium text-gray-700">Agência</label><input type="text" id="agencia" name="agencia" value="<?php echo $isEdit ? htmlspecialchars($funcionario['agencia'] ?? '') : ''; ?>" class="input-form"></div>
                    <div><label for="conta" class="block text-sm font-medium text-gray-700">Conta</label><input type="text" id="conta" name="conta" value="<?php echo $isEdit ? htmlspecialchars($funcionario['conta'] ?? '') : ''; ?>" class="input-form"></div>
                    <div>
                        <label for="tipo_conta" class="block text-sm font-medium text-gray-700">Tipo de Conta</label>
                        <select id="tipo_conta" name="tipo_conta" class="input-form">
                            <?php
                            $tiposConta = ['corrente' => 'Corrente', 'poupanca' => 'Poupança'];
                            $tipoContaSalvo = $isEdit ? ($funcionario['tipo_conta'] ?? 'corrente') : 'corrente';
                            foreach ($tiposConta as $valor => $texto): ?>
                                <option value="<?php echo $valor; ?>" <?php echo ($tipoContaSalvo == $valor) ? 'selected' : ''; ?>><?php echo $texto; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label for="tipo_chave_pix" class="block text-sm font-medium text-gray-700">Tipo de Chave PIX</label>
                        <select id="tipo_chave_pix" name="tipo_chave_pix" class="input-form">
                            <?php
                            $tiposChavePix = [
                                '' => 'Nenhuma',
                                'cpf' => 'CPF',
                                'email' => 'E-mail',
                                'celular' => 'Celular',
                                'aleatoria' => 'Aleatória'
                            ];
                            $tipoChavePixSalvo = $isEdit ? ($funcionario['tipo_chave_pix'] ?? '') : '';
                            foreach ($tiposChavePix as $valor => $texto): ?>
                                <option value="<?php echo $valor; ?>" <?php echo ($tipoChavePixSalvo == $valor) ? 'selected' : ''; ?>><?php echo $texto; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div><label for="chave_pix" class="block text-sm font-medium text-gray-700">Chave PIX</label><input type="text" id="chave_pix" name="chave_pix" value="<?php echo $isEdit ? htmlspecialchars($funcionario['chave_pix'] ?? '') : ''; ?>" class="input-form"></div>
                </div>
            </div>
        </div>

        <!-- Botões de Ação -->
        <div class="mt-8 pt-6 border-t border-gray-200 flex justify-end">
            <a href="<?php echo BASE_URL; ?>/rh" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-md mr-3 hover:bg-gray-300">Cancelar</a>
            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md shadow-sm hover:bg-indigo-700"><?php echo $isEdit ? 'Salvar Alterações' : 'Salvar Funcionário'; ?></button>
        </div>
    </form>
</div>

<style>
    .input-form {
        margin-top: 0.25rem;
        display: block;
        width: 100%;
        padding: 0.5rem 0.75rem;
        background-color: var(--db-surface, white);
        border: 1px solid var(--db-border, #D1D5DB);
        color: var(--db-text, inherit);
        /* border-gray-300 */
        border-radius: 0.375rem;
        /* rounded-md */
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        /* shadow-sm */
    }

    .input-form:focus {
        outline: none;
        --tw-ring-color: #4F46E5;
        /* ring-indigo-500 */
        --tw-ring-offset-shadow: var(--tw-ring-inset) 0 0 0 var(--tw-ring-offset-width) var(--tw-ring-offset-color);
        --tw-ring-shadow: var(--tw-ring-inset) 0 0 0 calc(1px + var(--tw-ring-offset-width)) var(--tw-ring-color);
        box-shadow: var(--tw-ring-offset-shadow), var(--tw-ring-shadow), var(--tw-shadow, 0 0 #0000);
        border-color: #6366F1;
        /* border-indigo-500 */
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const tabButtons = document.querySelectorAll('.tab-button');
        const tabPanes = document.querySelectorAll('.tab-pane');

        tabButtons.forEach(button => {
            button.addEventListener('click', () => {
                // Desativa todos os botões
                tabButtons.forEach(btn => {
                    btn.classList.remove('border-indigo-500', 'text-indigo-600');
                    btn.classList.add('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
                });

                // Ativa o botão clicado
                button.classList.add('border-indigo-500', 'text-indigo-600');
                button.classList.remove('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');

                // Esconde todos os painéis
                tabPanes.forEach(pane => {
                    pane.classList.add('hidden');
                });

                // Mostra o painel correspondente
                const tabId = button.getAttribute('data-tab');
                document.getElementById(tabId).classList.remove('hidden');
            });
        });

        // Lógica para preview da foto
        const fotoInput = document.getElementById('foto');
        const fotoPreview = document.getElementById('foto-preview');
        fotoInput.addEventListener('change', function(event) {
            if (event.target.files && event.target.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    fotoPreview.src = e.target.result;
                }
                reader.readAsDataURL(event.target.files[0]);
            }
        });

        // Lógica para busca de endereço via CEP
        const cepInput = document.getElementById('cep');
        const enderecoInput = document.getElementById('endereco');
        const bairroInput = document.getElementById('bairro');
        const cidadeInput = document.getElementById('cidade');
        const ufInput = document.getElementById('uf');
        const buscarCepBtn = document.getElementById('buscar-cep-btn');

        const buscarEnderecoPorCep = () => {
            const cep = cepInput.value.replace(/\D/g, ''); // Remove caracteres não numéricos

            if (cep.length !== 8) {
                return; // Sai se o CEP não tiver 8 dígitos
            }

            // Mostra um feedback de carregamento nos campos
            enderecoInput.value = 'Buscando...';
            bairroInput.value = 'Buscando...';
            cidadeInput.value = 'Buscando...';
            ufInput.value = '...';

            fetch(`https://viacep.com.br/ws/${cep}/json/`)
                .then(response => response.json())
                .then(data => {
                    if (data.erro) {
                        // Limpa os campos se o CEP não for encontrado
                        enderecoInput.value = '';
                        bairroInput.value = '';
                        cidadeInput.value = '';
                        ufInput.value = '';
                        console.warn('CEP não encontrado.');
                    } else {
                        // Preenche os campos com os dados retornados
                        enderecoInput.value = data.logradouro;
                        bairroInput.value = data.bairro;
                        cidadeInput.value = data.localidade;
                        ufInput.value = data.uf;
                    }
                })
                .catch(error => {
                    console.error('Erro ao buscar o CEP:', error);
                    // Limpa os campos em caso de erro na requisição
                    enderecoInput.value = '';
                    bairroInput.value = '';
                    cidadeInput.value = '';
                    ufInput.value = '';
                });
        };

        cepInput.addEventListener('blur', buscarEnderecoPorCep);
        buscarCepBtn.addEventListener('click', buscarEnderecoPorCep);

        // --- Lógica para Dependentes ---
        const dependentesContainer = document.getElementById('dependentes-container');
        const addDependenteBtn = document.getElementById('add-dependente-btn');
        const dependenteTemplate = document.getElementById('dependente-template');
        let dependenteIndex = 0;

        function addNewDependente(dependente = null) {
            const templateContent = dependenteTemplate.content.cloneNode(true);
            const newBlock = templateContent.querySelector('.dependente-bloco');

            // Atualiza os atributos 'for', 'id' e 'name' com o novo índice
            newBlock.innerHTML = newBlock.innerHTML.replaceAll('__INDEX__', dependenteIndex);

            // Se dados do dependente foram passados (modo de edição), preenche os campos
            if (dependente) {
                newBlock.querySelector(`input[name="dependentes[${dependenteIndex}][nome]"]`).value = dependente.nome || '';
                newBlock.querySelector(`select[name="dependentes[${dependenteIndex}][parentesco]"]`).value = dependente.parentesco || '';
                newBlock.querySelector(`input[name="dependentes[${dependenteIndex}][nascimento]"]`).value = dependente.nascimento || '';
                newBlock.querySelector(`input[name="dependentes[${dependenteIndex}][cpf]"]`).value = dependente.cpf || '';
            }

            dependentesContainer.appendChild(newBlock);
            dependenteIndex++;
        }

        // Adicionar novo dependente
        addDependenteBtn.addEventListener('click', addNewDependente);

        // Delegação de evento para remover dependente
        dependentesContainer.addEventListener('click', function(e) {
            const removeButton = e.target.closest('.remover-dependente-btn');
            if (removeButton) {
                const blockToRemove = removeButton.closest('.dependente-bloco');
                if (blockToRemove) {
                    blockToRemove.remove();
                }
            }
        });

        // Carrega os dependentes existentes se estiver no modo de edição
        const existingDependents = <?php echo isset($funcionario['dependentes']) && is_array($funcionario['dependentes']) ? json_encode(array_values($funcionario['dependentes'])) : '[]'; ?>;

        if (existingDependents.length > 0) {
            existingDependents.forEach(dep => {
                // Garante que não estamos processando um dependente "vazio" que pode ter sido enviado pelo formulário
                if (dep && dep.nome && dep.nome.trim() !== '') {
                    addNewDependente(dep);
                }
            });
        } else {
            // Se não houver dependentes, adiciona um bloco vazio para começar
            addNewDependente();
        }

        // --- Lógica para Anexos de Documentos ---
        function setupAttachmentListener(inputId, filenameId) {
            const fileInput = document.getElementById(inputId);
            const filenameDisplay = document.getElementById(filenameId);
            if (fileInput && filenameDisplay) {
                fileInput.addEventListener('change', function() {
                    if (this.files && this.files.length > 0) {
                        filenameDisplay.textContent = 'Novo: ' + this.files[0].name;
                    } else {
                        filenameDisplay.textContent = '';
                    }
                });
            }
        }
        setupAttachmentListener('anexo_rg', 'anexo_rg_filename');
        setupAttachmentListener('anexo_titulo', 'anexo_titulo_filename');
        setupAttachmentListener('anexo_reservista', 'anexo_reservista_filename');
        setupAttachmentListener('anexo_cnh', 'anexo_cnh_filename');

        // --- Lógica para restringir campos a apenas números ---
        function restrictToNumbers(inputElement) {
            if (inputElement) {
                inputElement.addEventListener('input', function(e) {
                    // Remove qualquer caractere que não seja um dígito
                    e.target.value = e.target.value.replace(/\D/g, '');
                });
            }
        }

        const numericFields = ['cpf', 'rg', 'cnh', 'reservista', 'titulo_eleitor', 'pis'];
        numericFields.forEach(id => {
            restrictToNumbers(document.getElementById(id));
        });

        // Máscara para o campo Celular: (XX)XXXXX-XXXX
        const celularInput = document.getElementById('celular');
        if (celularInput) {
            const aplicarMascara = (valor) => {
                let v = valor.replace(/\D/g, "");
                v = v.substring(0, 11); // Limita a 11 dígitos

                if (v.length > 10) {
                    v = v.replace(/^(\d{2})(\d{5})(\d{4})/, "($1)$2-$3");
                } else if (v.length > 5) {
                    v = v.replace(/^(\d{2})(\d{4})(\d{0,4})/, "($1)$2-$3");
                } else if (v.length > 2) {
                    v = v.replace(/^(\d{2})(\d{0,5})/, "($1)$2");
                }
                return v;
            };

            celularInput.addEventListener('input', function(e) {
                e.target.value = aplicarMascara(e.target.value);
            });

            // Aplica a máscara ao carregar a página (para edição)
            if (celularInput.value) {
                celularInput.value = aplicarMascara(celularInput.value);
            }
        }
    });
</script>
