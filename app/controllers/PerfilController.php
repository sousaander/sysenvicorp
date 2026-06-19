<?php

namespace App\Controllers;

use App\Core\Connection;
use App\Models\PerfilModel;

class PerfilController extends BaseController
{
    private $perfilModel;

    /**
     * Lista de todas as permissões granulares disponíveis no sistema.
     */
    public const PERMISSOES_SISTEMA = [
        // Dashboard
        'dashboard_view' => 'Visualizar Dashboard',

        // CRM (Clientes)
        'clientes_view' => 'Visualizar Clientes e Leads',
        'clientes_create' => 'Criar Novos Clientes/Leads',
        'clientes_edit' => 'Editar Clientes/Leads',
        'clientes_delete' => 'Excluir Clientes/Leads',
        'clientes_manage' => 'Gerenciar Cadastro de Clientes (Geral)',
        'clientes_interacoes_manage' => 'Registrar e Ver Interações',
        'clientes_reports_view' => 'Visualizar Relatórios de CRM',

        // Projetos
        'projetos_view' => 'Visualizar Projetos',
        'projetos_create' => 'Criar Novos Projetos',
        'projetos_edit' => 'Editar Dados Gerais de Projetos',
        'projetos_delete' => 'Excluir Projetos',
        'projetos_tarefas_manage' => 'Gerenciar Tarefas e Cronograma',
        'projetos_orcamento_manage' => 'Gerenciar Orçamento de Projetos',
        'projetos_docs_manage' => 'Gerenciar Documentos (CDT, Mapas, Arquivos)',
        'projetos_art_manage' => 'Gerenciar ART/RRT de Projetos',
        'projetos_reports_view' => 'Visualizar Relatórios de Projetos',

        // Gestão Técnica (POPs, PRAD, Licenças)
        'pops_view' => 'Visualizar POPs (Procedimentos Operacionais)',
        'pops_manage' => 'Gerenciar POPs (Criar, Editar, Excluir)',
        'prad_view' => 'Visualizar PRADs (Planos de Recuperação)',
        'prad_manage' => 'Gerenciar PRADs (Criar, Editar, Excluir)',
        'licencas_view' => 'Visualizar Licenças de Operação',
        'licencas_manage' => 'Gerenciar Licenças de Operação (Criar, Editar, Excluir)',
        'licencas_nc_view' => 'Visualizar Relatórios de Não Conformidade (Licenças)',
        'licencas_nc_manage' => 'Registrar e Gerenciar Não Conformidades (Licenças)',

        // Comercial
        'comercial_propostas_view' => 'Visualizar Propostas e Orçamentos',
        'orcamento_send' => 'Enviar Propostas por E-mail',
        'orcamento_view' => 'Acessar Menu Comercial / Propostas',
        'comercial_licitacoes_view' => 'Visualizar Licitações',
        'comercial_reports_view' => 'Visualizar Relatórios Comerciais',

        // Fornecedores
        'fornecedores_view' => 'Visualizar Fornecedores',
        'fornecedores_create' => 'Criar Fornecedores',
        'fornecedores_edit' => 'Editar Fornecedores',
        'fornecedores_delete' => 'Excluir Fornecedores',
        'fornecedores_reports_view' => 'Visualizar Relatórios de Fornecedores',

        // Financeiro
        'financeiro_dashboard_view' => 'Visualizar Dashboard Financeiro',
        'financeiro_lancamentos_view' => 'Visualizar Lançamentos (Pagar/Receber)',
        'financeiro_lancamentos_create' => 'Criar Lançamentos',
        'financeiro_lancamentos_edit' => 'Editar Lançamentos',
        'financeiro_lancamentos_delete' => 'Excluir Lançamentos',
        'financeiro_import_manage' => 'Importar Extratos/CSV',
        'financeiro_transferencias_create' => 'Realizar Transferências entre Contas',
        'financeiro_reports_view' => 'Gerar e Visualizar Relatórios Financeiros',        

        // Financeiro (Prestação de Contas)
        'financeiro_prestacao_contas_view' => 'Acessar Menu de Prestação de Contas',
        'financeiro_prestacao_contas_view_own' => 'Visualizar Suas Próprias Prestações de Contas',
        'financeiro_prestacao_contas_submit' => 'Enviar Nova Prestação de Contas para Aprovação',
        'financeiro_prestacao_contas_edit_own' => 'Editar Suas Próprias Prestações de Contas (antes da aprovação)',
        'financeiro_prestacao_contas_delete_own' => 'Excluir Suas Próprias Prestações de Contas (antes da aprovação)',
        'financeiro_prestacao_contas_approve' => 'Aprovar/Reprovar Prestações de Contas',
        'financeiro_prestacao_contas_view_all' => 'Visualizar Todas as Prestações de Contas (Aprovadas)',

        // RH (Recursos Humanos)
        'rh_dashboard_view' => 'Visualizar Dashboard de RH',
        'rh_funcionarios_view' => 'Visualizar Lista de Funcionários',
        'rh_funcionarios_manage' => 'Gerenciar Cadastro de Funcionários',
        'rh_folha_pagamento_manage' => 'Gerenciar Folha de Pagamento (Calcular, Gerar Holerites)',
        'rh_ferias_manage' => 'Gerenciar Férias (Calcular, Agendar)',
        'rh_rescisao_manage' => 'Gerenciar Rescisões',
        'rh_treinamentos_view' => 'Visualizar Treinamentos',
        'rh_treinamentos_manage' => 'Gerenciar Treinamentos (Criar, Editar, Excluir)',
        'rh_reports_view' => 'Gerar e Visualizar Relatórios de RH',

        // Contratos
        'contratos_view' => 'Visualizar Contratos',
        'contratos_create' => 'Criar Novos Contratos',
        'contratos_edit' => 'Editar Contratos e Aditivos',
        'contratos_delete' => 'Excluir Contratos e Aditivos',
        'contratos_compliance_manage' => 'Gerenciar Compliance e Jurídico',
        'contratos_financeiro_manage' => 'Gerenciar Financeiro (Parcelas)',
        'contratos_obrigacoes_manage' => 'Gerenciar Obrigações Contratuais',

        // Patrimônio
        'patrimonio_view' => 'Visualizar Bens e Ativos',
        'patrimonio_create' => 'Cadastrar Novos Bens',
        'patrimonio_edit' => 'Editar Bens',
        'patrimonio_delete' => 'Excluir Bens',
        'patrimonio_movimentacoes_manage' => 'Gerenciar Movimentações (Transferência, Baixa)',
        'patrimonio_inventario_run' => 'Realizar Inventário Físico',

        // Jurídico (Novo Módulo)
        'juridico_dashboard_view' => 'Visualizar Dashboard Jurídico',
        'juridico_processos_view' => 'Visualizar Processos Judiciais/Administrativos',
        'juridico_processos_manage' => 'Gerenciar Processos Judiciais/Administrativos',
        'juridico_documentos_manage' => 'Gerenciar Documentos Jurídicos',
        'juridico_agenda_manage' => 'Gerenciar Agenda e Prazos Jurídicos',

        // Organograma
        'organograma_view' => 'Visualizar Organograma e KPIs',
        'organograma_structure_manage' => 'Gerenciar Estrutura (Cargos/Departamentos)',
        'organograma_kpis_manage' => 'Gerenciar Atividades e Metas (KPIs)',

        // Configurações
        'config_empresa_manage' => 'Gerenciar Dados da Empresa',
        'config_usuarios_view' => 'Visualizar Usuários',
        'config_usuarios_manage' => 'Gerenciar Usuários (Criar, Editar, Status, Resetar Senha)',
        'config_usuarios_delete' => 'Excluir Usuários',
        'config_perfis_manage' => 'Gerenciar Perfis e Permissões',
        'config_audit_view' => 'Visualizar Logs de Auditoria',
        'config_financeiro_manage' => 'Gerenciar Configurações Financeiras (Bancos, Categorias, Centros de Custo)',
        'config_clientes_manage' => 'Gerenciar Configurações de Clientes (Categorias, Segmentos)',
    ];

    /**
     * Mapeia ações para as permissões necessárias.
     * O BaseController usará este mapa para verificar o acesso.
     * @var array
     */
    protected $requiredPermissions = [
        'index' => 'config_perfis_manage',
        'form' => 'config_perfis_manage',
        'editar' => 'config_perfis_manage',
        'salvar' => 'config_perfis_manage',
        'excluir' => 'config_perfis_manage',
    ];

    public function __construct()
    {
        parent::__construct();
        $this->perfilModel = new PerfilModel();
    }

    /**
     * Exibe a lista de perfis de acesso cadastrados.
     */
    public function index()
    {
        $perfis = $this->perfilModel->getAll();

        // Processa as permissões para exibir um resumo na view
        foreach ($perfis as &$perfil) { // Usa referência (&) para modificar o array diretamente
            if (!empty($perfil['permissoes'])) {
                $permissoesArray = json_decode($perfil['permissoes'], true);
                // Adiciona a contagem de permissões ao array do perfil
                $perfil['permissoes_count'] = is_array($permissoesArray) ? count($permissoesArray) : 0;
            } else {
                $perfil['permissoes_count'] = 0;
            }
        }
        unset($perfil); // Desfaz a referência após o loop

        // Gera um token CSRF para os formulários da página
        $csrf_token = bin2hex(random_bytes(32));
        $this->session->set('csrf_token', $csrf_token);

        $data = [
            'pageTitle' => 'Gerenciar Perfis de Acesso',
            'perfis' => $perfis,
            'perfis_json' => json_encode($perfis), // Envia os dados em JSON para o JavaScript
            'csrf_token' => $csrf_token, // Envia o token para a view
        ];

        $this->renderView('perfil/index', $data);
    }

    /**
     * Exibe o formulário para um novo perfil ou para edição de um existente.
     * @param int|null $id O ID do perfil para edição.
     */
    public function form($id = null)
    {
        $perfil = null;
        // Valida o ID para garantir que é um inteiro positivo.
        // Isso torna o método mais robusto, caso o roteador passe um valor não numérico ou zero.
        $id = filter_var($id, FILTER_VALIDATE_INT) ?: null;

        if ($id && $id > 0) {
            $perfil = $this->perfilModel->getById($id);
            if (!$perfil) {
                $this->setFlashMessage('error', 'Perfil não encontrado.');
                header('Location: ' . BASE_URL . '/perfil');
                exit();
            }
            // CORREÇÃO: Decodifica a string JSON de permissões para um array.
            // Isso garante que a view possa iterar sobre as permissões e marcar os checkboxes corretos.
            if (!empty($perfil['permissoes']) && is_string($perfil['permissoes'])) {
                $permissoesArray = json_decode($perfil['permissoes'], true);
                $perfil['permissoes'] = is_array($permissoesArray) ? $permissoesArray : [];
            } else {
                $perfil['permissoes'] = [];
            }
        }

        // Agrupar permissões por módulo para a view
        $permissoesAgrupadas = [];
        foreach (self::PERMISSOES_SISTEMA as $chave => $descricao) {
            $prefixo = explode('_', $chave)[0];
            
            // Força o agrupamento de contratos dentro do Jurídico
            if ($prefixo === 'contratos') {
                $nomeModulo = 'Jurídico (Contratos)';
            } else {
                $nomeModulo = ucfirst($prefixo);
            }

            if (!isset($permissoesAgrupadas[$nomeModulo])) {
                $permissoesAgrupadas[$nomeModulo] = [];
            }
            $permissoesAgrupadas[$nomeModulo][$chave] = $descricao;
        }

        $csrf_token = $this->session->get('csrf_token');
        if (empty($csrf_token)) {
            $csrf_token = bin2hex(random_bytes(32));
            $this->session->set('csrf_token', $csrf_token);
        }

        $data = [
            'pageTitle' => ($id && $id > 0) ? 'Editar Perfil de Acesso' : 'Novo Perfil de Acesso',
            'perfil' => $perfil,
            'permissoes_agrupadas' => $permissoesAgrupadas,
            'permissoes_disponiveis' => self::PERMISSOES_SISTEMA,
            'csrf_token' => $csrf_token,
        ];

        $this->renderView('perfil/form', $data);
    }

    /**
     * Alias para o método form, permitindo a rota /perfil/editar/{id}.
     * @param int $id O ID do perfil para edição.
     */
    public function editar($id = null)
    {
        // Se não houver ID, ou se o ID não for um inteiro positivo, redireciona com erro.
        // Usar filter_var com min_range é a forma mais robusta de validar um ID de banco de dados.
        if (filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) === false) {
            $this->setFlashMessage('error', 'ID do perfil inválido para edição.');
            header('Location: ' . BASE_URL . '/perfil');
            exit();
        }
        $this->form($id);
    }

    /**
     * Processa o formulário de cadastro/edição.
     */
    public function salvar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/perfil');
            exit();
        }

        // Validação de CSRF
        if (!isset($_POST['csrf_token']) || !$this->validateCsrfToken($_POST['csrf_token'])) {
            $this->setFlashMessage('error', 'Erro de validação de segurança (CSRF).');
            header('Location: ' . BASE_URL . '/perfil');
            exit();
        }

        $dados = [
            'id' => filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: null,
            'nome_perfil' => filter_input(INPUT_POST, 'nome_perfil', FILTER_SANITIZE_SPECIAL_CHARS),
            // CORREÇÃO: Usar htmlspecialchars manualmente para preservar quebras de linha.
            // filter_input com FILTER_SANITIZE_*_CHARS converte as quebras de linha, o que é indesejado.
            // htmlspecialchars é mais seguro e não afeta \r\n.
            'descricao' => isset($_POST['descricao']) ? htmlspecialchars($_POST['descricao'], ENT_QUOTES, 'UTF-8') : null,
            // Pega o array de permissões do formulário
            'permissoes' => $_POST['permissoes'] ?? [],
        ];

        if ($this->perfilModel->salvar($dados)) {
            $this->setFlashMessage('success', 'Perfil salvo com sucesso!');
        } else {
            $erro = $this->perfilModel->getLastError() ?? 'Erro desconhecido ao salvar o perfil.';
            $this->setFlashMessage('error', $erro);
        }

        header('Location: ' . BASE_URL . '/perfil');
        exit();
    }

    /**
     * Exclui um perfil (apenas via POST com CSRF token).
     */
    public function excluir(int $id)
    {
        // Somente POST para exclusão
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->setFlashMessage('error', 'Operação inválida. Use o botão de exclusão.');
            header('Location: ' . BASE_URL . '/perfil');
            exit();
        }

        // Valida CSRF
        $token = $_POST['csrf_token'] ?? '';
        if (empty($token) || !$this->validateCsrfToken($token)) {
            $this->setFlashMessage('error', 'Token CSRF inválido.');
            header('Location: ' . BASE_URL . '/perfil');
            exit();
        }

        // Se o ID não veio pela rota (argumento zerado), tenta pegar do POST
        if ($id <= 0) {
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: 0;
        }

        if ($id <= 0) {
            $this->setFlashMessage('error', 'ID inválido.');
            header('Location: ' . BASE_URL . '/perfil');
            exit();
        }

        // Busca o perfil para verificar se é o 'Admin'
        $perfil = $this->perfilModel->getById($id);
        if ($perfil && strtolower($perfil['nome_perfil']) === 'admin') {
            $this->setFlashMessage('error', 'O perfil "Admin" não pode ser excluído.');
            header('Location: ' . BASE_URL . '/perfil');
            exit();
        }
        // Fim da verificação

        if ($this->perfilModel->excluir($id)) {
            $this->setFlashMessage('success', 'Perfil excluído com sucesso.');
        } else {
            $erro = $this->perfilModel->getLastError() ?? 'Erro ao excluir o perfil. Verifique se existem usuários vinculados.';
            $this->setFlashMessage('error', $erro);
        }
        header('Location: ' . BASE_URL . '/perfil');
        exit();
    }
}
