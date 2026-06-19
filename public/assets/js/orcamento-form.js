/**
 * orcamento-form.js
 * Lógica completa para manipulação de itens e cálculos dinâmicos.
 */

document.addEventListener('DOMContentLoaded', function() {
    const tbody = document.getElementById('tbody-itens');
    const btnAddItem = document.getElementById('btn-add-item');
    const form = document.getElementById('form-orcamento');

    if (!tbody || !btnAddItem) return;

    // Template de nova linha otimizado para Ghost Inputs
    const rowTemplate = `
        <tr class="hover:bg-gray-50/50 transition opacity-0 translate-y-2 animate-fade-in">
            <td class="px-4 py-3">
                <input type="text" class="form-control form-control-sm" name="item_descricao[]" required>
                <input type="text" class="form-control form-control-sm mt-1" name="item_detalhes[]" placeholder="Detalhes (opcional)">
            </td>
            <td class="px-4 py-3">
                <input type="text" class="form-control form-control-sm" name="item_unidade[]" style="width:70px" value="un">
            </td>
            <td class="px-4 py-3">
                <input type="number" class="form-control form-control-sm item-qty" name="item_quantidade[]" value="1" min="0.001" step="any">
            </td>
            <td class="px-4 py-3">
                <input type="text" class="form-control form-control-sm item-vunit currency-input" name="item_valor[]" value="0,00">
            </td>
            <td class="px-4 py-3">
                <input type="number" class="form-control form-control-sm item-desc" name="item_desconto[]" value="0" min="0" max="100" step="0.01">
            </td>
            <td class="px-4 py-3 text-end font-bold text-gray-700 item-total">
                R$ 0,00
            </td>
            <td class="px-4 py-3 text-center">
                <button type="button" class="text-gray-300 hover:text-red-500 transition btn-remover-item">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </td>
        </tr>
    `;

    const formatCurrency = (val) => {
        return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(val);
    };

    const parseBRL = (str) => {
        if (!str) return 0;
        return parseFloat(str.replace(/\./g, '').replace(',', '.')) || 0;
    };

    // Lógica centralizada de cálculo (usando delegação de eventos para suportar novos itens)
    const calculateTotals = () => {
        let subtotal = 0;
        const rows = tbody.querySelectorAll('tr');

        rows.forEach(row => {
            const qty = parseFloat(row.querySelector('.item-qty').value) || 0;
            const vunit = parseBRL(row.querySelector('.item-vunit').value);
            const desc = parseFloat(row.querySelector('.item-desc').value) || 0;

            const totalItem = qty * vunit * (1 - desc / 100);
            row.querySelector('.item-total').textContent = formatCurrency(totalItem);
            subtotal += totalItem;
        });

        // Desconto global e Impostos
        const descGlobalVal = parseFloat(document.getElementById('desconto_valor').value) || 0;
        const descGlobalTipo = document.getElementById('desconto_tipo').value;
        let totalDesconto = descGlobalTipo === 'percentual' ? subtotal * (descGlobalVal / 100) : descGlobalVal;

        const impPerc = parseFloat(document.getElementById('impostos_perc').value) || 0;
        const baseCalculoImp = subtotal - totalDesconto;
        const totalImpostos = baseCalculoImp * (impPerc / 100);

        const totalGeral = baseCalculoImp + totalImpostos;

        // Atualiza a visualização no resumo lateral
        if(document.getElementById('resumo-subtotal')) document.getElementById('resumo-subtotal').textContent = formatCurrency(subtotal);
        if(document.getElementById('resumo-desconto')) document.getElementById('resumo-desconto').textContent = `- ${formatCurrency(totalDesconto)}`;
        if(document.getElementById('resumo-impostos')) document.getElementById('resumo-impostos').textContent = formatCurrency(totalImpostos);
        if(document.getElementById('resumo-total')) document.getElementById('resumo-total').textContent = formatCurrency(totalGeral);

        // Sincroniza com os campos ocultos para o envio do formulário
        if(document.getElementById('hidden-total-servicos')) document.getElementById('hidden-total-servicos').value = subtotal.toFixed(2);
        if(document.getElementById('hidden-descontos-valor')) document.getElementById('hidden-descontos-valor').value = totalDesconto.toFixed(2);
        if(document.getElementById('hidden-impostos-valor')) document.getElementById('hidden-impostos-valor').value = totalImpostos.toFixed(2);
        if(document.getElementById('hidden-valor-total')) document.getElementById('hidden-valor-total').value = totalGeral.toFixed(2);
    };

    // Prevenção de clique duplo no envio
    if (form) {
        form.addEventListener('submit', function() {
            const submitButton = form.querySelector('button[type="submit"]');
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Salvando...';
            }
        });
    }

    // Adicionar novo item
    btnAddItem.addEventListener('click', () => {
        tbody.insertAdjacentHTML('beforeend', rowTemplate);
        calculateTotals();
    });

    // Remover item (usando delegação de eventos para eficiência)
    tbody.addEventListener('click', (e) => {
        if (e.target.closest('.btn-remover-item')) {
            const row = e.target.closest('tr');
            row.classList.add('opacity-0', 'scale-95'); // Efeito de saída
            setTimeout(() => {
                row.remove();
                calculateTotals();
            }, 200);
        }
    });

    // Escuta mudanças nos inputs para recalcular
    document.addEventListener('input', (e) => {
        const classes = ['item-qty', 'item-vunit', 'item-desc'];
        const ids = ['desconto_valor', 'desconto_tipo', 'impostos_perc'];
        
        if (classes.some(c => e.target.classList.contains(c)) || ids.includes(e.target.id)) {
            calculateTotals();
        }
    });

    // Máscara simples para moeda ao sair do campo
    document.addEventListener('blur', (e) => {
        if (e.target.classList.contains('currency-input')) {
            const val = parseBRL(e.target.value);
            e.target.value = val.toLocaleString('pt-BR', { 
                minimumFractionDigits: 2, 
                maximumFractionDigits: 2 
            });
        }
    }, true);

    // ========== LÓGICA DE PROJETO VINCULADO ==========
    const hasProjetoCheckbox = document.getElementById('has-projeto-checkbox');
    const sectionProjeto = document.getElementById('section-projeto');
    const projetoSelect = document.getElementById('projeto_id');
    const projectDetailsContainer = document.getElementById('project-details-container');

    console.log('Elementos encontrados:', {
        checkbox: !!hasProjetoCheckbox,
        section: !!sectionProjeto,
        select: !!projetoSelect,
        container: !!projectDetailsContainer
    });

    // Função para atualizar detalhes do projeto
    async function updateProjectDetails() {
        const projetoId = projetoSelect?.value;
        if (!projetoId) {
            if (projectDetailsContainer) {
                projectDetailsContainer.classList.add('hidden');
            }
            return;
        }

        try {
            // Tenta buscar os dados do projeto via controller
            const response = await fetch(BASE_URL + '/projetos/getProjetoDados/' + projetoId);
            if (!response.ok) throw new Error('Erro ao buscar projeto');
            
            const result = await response.json();
            if (result.success && result.data) {
                const projeto = result.data;
                
                // Preenche os detalhes
                if (projectDetailsContainer) {
                    const clienteEl = document.getElementById('detail-cliente');
                    const responsavelEl = document.getElementById('detail-responsavel');
                    const tipoEl = document.getElementById('detail-tipo');
                    const idEl = document.getElementById('detail-id');
                    
                    if (clienteEl) clienteEl.textContent = projeto.cliente_nome || '—';
                    if (responsavelEl) responsavelEl.textContent = projeto.responsavel_nome || '—';
                    if (tipoEl) tipoEl.textContent = projeto.tipo_servico || '—';
                    if (idEl) idEl.textContent = '#' + projeto.id;
                    
                    projectDetailsContainer.classList.remove('hidden');
                }
            }
        } catch (error) {
            console.warn('Não foi possível buscar detalhes do projeto:', error);
            // Se a API não estiver disponível, apenas mostra o container com dados básicos
            if (projectDetailsContainer) {
                projectDetailsContainer.classList.remove('hidden');
            }
        }
    }

    // Controlar visibilidade da seção de projeto
    if (hasProjetoCheckbox && sectionProjeto) {
        hasProjetoCheckbox.addEventListener('change', function() {
            console.log('Checkbox mudou:', this.checked);
            if (this.checked) {
                sectionProjeto.classList.remove('hidden');
                console.log('Seção de projeto mostrada');
                // Se já há um projeto selecionado, carrega os detalhes
                if (projetoSelect && projetoSelect.value) {
                    updateProjectDetails();
                }
            } else {
                sectionProjeto.classList.add('hidden');
                console.log('Seção de projeto escondida');
                if (projectDetailsContainer) {
                    projectDetailsContainer.classList.add('hidden');
                }
                // Limpa a seleção do projeto
                if (projetoSelect) {
                    projetoSelect.value = '';
                }
            }
        });
    }

    // Atualizar detalhes quando o projeto é alterado
    if (projetoSelect) {
        projetoSelect.addEventListener('change', updateProjectDetails);
        
        // Se há um projeto pré-selecionado (edição), carrega os detalhes
        if (projetoSelect.value) {
            updateProjectDetails();
        }
    }

    calculateTotals();
});

// Estilo da animação injetado dinamicamente
const style = document.createElement('style');
style.innerHTML = `
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-in {
        animation: fadeIn 0.3s ease forwards;
    }
`;
document.head.appendChild(style);