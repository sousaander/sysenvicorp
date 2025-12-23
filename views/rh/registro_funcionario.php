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
                                <img id="foto-preview" src="https://placehold.co/96x96/E2E8F0/4A5568?text=Foto" alt="Preview da foto" class="h-full w-full object-cover">
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
                        <div><label for="rg" class="block text-sm font-medium text-gray-700">RG</label><input type="text" id="rg" name="rg" value="<?php echo $isEdit ? htmlspecialchars($funcionario['rg'] ?? '') : ''; ?>" class="input-form"></div>
                        <div><label for="cpf" class="block text-sm font-medium text-gray-700">CPF</label><input type="text" id="cpf" name="cpf" value="<?php echo $isEdit ? htmlspecialchars($funcionario['cpf'] ?? '') : ''; ?>" class="input-form"></div>
                        <div><label for="titulo_eleitor" class="block text-sm font-medium text-gray-700">Título de Eleitor</label><input type="text" id="titulo_eleitor" name="titulo_eleitor" value="<?php echo $isEdit ? htmlspecialchars($funcionario['titulo_eleitor'] ?? '') : ''; ?>" class="input-form"></div>
                        <div><label for="reservista" class="block text-sm font-medium text-gray-700">Reservista</label><input type="text" id="reservista" name="reservista" value="<?php echo $isEdit ? htmlspecialchars($funcionario['reservista'] ?? '') : ''; ?>" class="input-form"></div>

                        <!-- Campo CNH com Categorias -->
                        <div class="md:col-span-2">
                            <label for="cnh" class="block text-sm font-medium text-gray-700">CNH</label>
                            <div class="flex items-center mt-1">
                                <input type="text" id="cnh" name="cnh" placeholder="Número da CNH" value="<?php echo $isEdit ? htmlspecialchars($funcionario['cnh'] ?? '') : ''; ?>" class="input-form w-full mr-2">
                                <select name="cnh_categoria" class="input-form w-28">
                                    <option value="">Categoria</option>
                                    <?php
                                    $categoriasCNH = ['A', 'B', 'C', 'D', 'E', 'AB', 'AC', 'AD', 'AE'];
                                    $categoriaSalva = $isEdit ? ($funcionario['cnh_categoria'] ?? '') : '';
                                    foreach ($categoriasCNH as $cat): ?>
                                        <option value="<?php echo $cat; ?>" <?php echo ($categoriaSalva == $cat) ? 'selected' : ''; ?>><?php echo $cat; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div><label for="ctps" class="block text-sm font-medium text-gray-700">Carteira de Trabalho</label><input type="text" id="ctps" name="ctps" value="<?php echo $isEdit ? htmlspecialchars($funcionario['ctps'] ?? '') : ''; ?>" class="input-form"></div>
                        <div><label for="pis" class="block text-sm font-medium text-gray-700">PIS</label><input type="text" id="pis" name="pis" value="<?php echo $isEdit ? htmlspecialchars($funcionario['pis'] ?? '') : ''; ?>" class="input-form"></div>
                        <div class="md:col-span-2"><label for="email" class="block text-sm font-medium text-gray-700">E-mail Pessoal</label><input type="email" id="email" name="email" required value="<?php echo $isEdit ? htmlspecialchars($funcionario['email']) : ''; ?>" class="input-form"></div>
                        <div><label for="celular" class="block text-sm font-medium text-gray-700">Celular</label><input type="text" id="celular" name="celular" value="<?php echo $isEdit ? htmlspecialchars($funcionario['celular'] ?? '') : ''; ?>" class="input-form"></div>
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
                <?php for ($i = 1; $i <= 3; $i++): ?>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 <?php echo $i > 1 ? 'mt-6 pt-6 border-t' : ''; ?>">
                        <div><label for="dep_nome_<?php echo $i; ?>" class="block text-sm font-medium text-gray-700">Dependente <?php echo $i; ?>: Nome</label><input type="text" id="dep_nome_<?php echo $i; ?>" name="dependentes[<?php echo $i; ?>][nome]" class="input-form"></div>
                        <div>
                            <label for="dep_parentesco_<?php echo $i; ?>" class="block text-sm font-medium text-gray-700">Grau de Parentesco</label>
                            <select id="dep_parentesco_<?php echo $i; ?>" name="dependentes[<?php echo $i; ?>][parentesco]" class="input-form">
                                <option value="">Selecione...</option>
                                <option value="Filho(a)">Filho(a)</option>
                                <option value="Cônjuge">Cônjuge</option>
                                <option value="Pai">Pai</option>
                                <option value="Mãe">Mãe</option>
                                <option value="Outro">Outro</option>
                            </select>
                        </div>
                        <div><label for="dep_nasc_<?php echo $i; ?>" class="block text-sm font-medium text-gray-700">Data de Nascimento</label><input type="date" id="dep_nasc_<?php echo $i; ?>" name="dependentes[<?php echo $i; ?>][nascimento]" class="input-form"></div>
                        <div><label for="dep_cpf_<?php echo $i; ?>" class="block text-sm font-medium text-gray-700">CPF</label><input type="text" id="dep_cpf_<?php echo $i; ?>" name="dependentes[<?php echo $i; ?>][cpf]" class="input-form"></div>
                    </div>
                <?php endfor; ?>
            </div>

            <!-- Aba: Dados Profissionais -->
            <div id="profissional" class="tab-pane hidden">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div><label for="cargo" class="block text-sm font-medium text-gray-700">Função (Cargo)</label><input type="text" id="cargo" name="cargo" required value="<?php echo $isEdit ? htmlspecialchars($funcionario['cargo']) : ''; ?>" class="input-form"></div>
                    <div><label for="setor" class="block text-sm font-medium text-gray-700">Setor</label><input type="text" id="setor" name="setor" required value="<?php echo $isEdit ? htmlspecialchars($funcionario['setor']) : ''; ?>" class="input-form"></div>
                    <div><label for="data_admissao" class="block text-sm font-medium text-gray-700">Data de Admissão</label><input type="date" id="data_admissao" name="data_admissao" required value="<?php echo $isEdit ? htmlspecialchars($funcionario['data_admissao']) : ''; ?>" class="input-form"></div>
                    <div><label for="salario" class="block text-sm font-medium text-gray-700">Salário (R$)</label><input type="number" step="0.01" id="salario" name="salario" value="<?php echo $isEdit ? htmlspecialchars($funcionario['salario'] ?? '') : ''; ?>" class="input-form"></div>
                    <div><label for="carga_horaria" class="block text-sm font-medium text-gray-700">Carga Horária (semanal)</label><input type="number" id="carga_horaria" name="carga_horaria" value="<?php echo $isEdit ? htmlspecialchars($funcionario['carga_horaria'] ?? '') : '44'; ?>" class="input-form"></div>
                </div>
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
                            <option value="corrente">Corrente</option>
                            <option value="poupanca">Poupança</option>
                        </select>
                    </div>
                    <div>
                        <label for="tipo_chave_pix" class="block text-sm font-medium text-gray-700">Tipo de Chave PIX</label>
                        <select id="tipo_chave_pix" name="tipo_chave_pix" class="input-form">
                            <option value="">Nenhuma</option>
                            <option value="cpf">CPF</option>
                            <option value="email">E-mail</option>
                            <option value="celular">Celular</option>
                            <option value="aleatoria">Aleatória</option>
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
        background-color: white;
        border: 1px solid #D1D5DB;
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
    });
</script>