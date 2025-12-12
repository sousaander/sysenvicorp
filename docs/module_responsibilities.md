# Mapeamento de Responsabilidades por Módulo

Este documento sumariza a responsabilidade principal de cada controller e os models que normalmente utiliza.

> Observação: é um resumo rápido com base nas assinaturas dos controllers e importações encontradas no código.

---

## FinanceiroController

- Propósito: Gerenciar movimentações financeiras (contas a pagar/receber), relatórios e exportação em PDF.
- Ações principais: `index`, `relatorio`, `exportarRelatorioPdf`, `novo`, `detalhe`, `salvar`, `excluir`, `pagar`, `receber`, `addClassificacao`, `addCentroCusto`.
- Models usados: `FinancialModel`.

## ContratosController

- Propósito: Gerenciar contratos, aditivos, obrigações, financeiro relacionado a contratos e uploads de documentos.
- Ações principais: `index`, `novo`, `salvar`, `vigencia`, `obrigacoes`, `financeiro`, `compliance`, `gerenciarCompliance`, `salvarCompliance`, `relatorios`, `exportarRelatorioVigenciaPdf`, `detalhe`, `excluir`, `salvarAditivo`, `excluirAditivo`, `gerenciarObrigacoes`, `salvarObrigacao`, `lancarParcela`, `enviarAlerta`, `uploadDocumento`, `download`, `removerDocumento`.
- Models usados: `ContratosModel`, `ClientesModel`, `FornecedoresModel`, `ProjetosModel`, `FinancialModel`.

## RhController

- Propósito: Gestão de recursos humanos (funcionários, folha de pagamento, cálculos, férias, relatórios, etc.).
- Ações principais: `index`, `registroFuncionario`, `salvar`, `detalhe`, `editar`, `excluir`, `fichaCadastral`, `folhaDePagamento`, `calcularFolha`, `verFolha`, `holerite`, `relatorios`, `lancamentos`, `salvarLancamentos`, `encargos`, `exportarFolhaContabil`, `calculoRescisao`, `calculoFerias`, `processarCalculoFerias`, `gerarAvisoFeriasPdf`, `gerarRelatorioFeriasPdf`, `historicoFerias`.
- Models usados: `RhModel`.

## ProjetosController

- Propósito: Gerenciar projetos, itens do orçamento, artefatos, mapos, arquivos e visualização por submenu.
- Ações principais: `index`, `detalhe`, `novo`, `salvarItemOrcamento`, `salvarArt`, `excluirArt`, `salvarCDT`, `excluirCDT`, `salvarMapa`, `excluirMapa`, `salvarArquivo`, `excluirArquivo`, `salvar`, `getFormulario`, `arquivados`.
- Models usados: `ProjetosModel`, `ClientesModel`.

## OrcamentoController

- Propósito: Gerenciar propostas, orçamentos, geração de PDF, histórico e envio por e-mail.
- Ações principais: `proposta`, `propostas`, `novaProposta`, `clonarProposta`, `salvarProposta`, `verProposta`, `pdfProposta`, `historicoProposta`, `verHistoricoDetalhe`, `enviarEmailProposta`, `getOrcamentosAjax`, `getProjectDetailsAjax`, `comercial`.
- Models usados: `ClientesModel`, `UsuarioModel`.

## FornecedoresController

- Propósito: Gestão de fornecedores e ocorrências relacionadas (cadastro, edição, histórico).
- Ações principais: `index`, `novo`, `detalhe`, `salvar`, `salvarOcorrencia`, `getFormForNew`.
- Models usados: `FornecedoresModel`, `ContratosModel`, `FinancialModel`.

## ClientesController

- Propósito: Gestão de clientes (cadastro, detalhe, categorias, segmentos, interações, consultas CNPJ).
- Ações principais: `index`, `detalhe`, `novo`, `getFormForEdit`, `getFormForNew`, `salvar`, `addCategoria`, `getSegmentosAjax`, `addSegmentoAjax`, `excluir`, `registrarInteracao`, `consultarCnpj`.
- Models usados: `ClientesModel`.

## Patrimonio / BensAtivosController

- Propósito: Gestão de bens patrimoniais, movimentações, inventário, depreciação, reavaliação e relatórios.
- Ações principais: `index`, `cadastro`, `salvar`, `getBemJson`, `excluir`, `movimentacoes`, `salvarMovimentacao`, `depreciacao`, `salvarReavaliacao`, `inventario`, `conciliarInventario`, `relatorios`, `detalheAtivo` (BensAtivosController).
- Models usados: `PatrimonioModel`, `BensAtivosModel`.

## NotaFiscalController

- Propósito: Emissão e controle de notas fiscais.
- Ações principais: `index`, `form`, `salvar`, `excluir`.
- Models usados: `NotaFiscalModel`, `ClientesModel`.

## DashboardController

- Propósito: Painel principal (agregação de dados: financeiro, projetos, licenças, clientes, contratos).
- Ações principais: `index`.
- Models usados: `FinancialModel`, `ProjetosModel`, `LicencasOperacaoModel`, `ClientesModel`, `ContratosModel`.

## AuthController / UsuarioController

- Propósito: Autenticação, gerenciamento de usuários e perfis.
- Ações principais (Auth): login/logout e endpoints de autenticação. (UsuarioController): `index`, `form`, `salvar`, `excluir` etc.
- Models usados: `UsuarioModel`, `RhModel` (em alguns fluxos de usuários/recursos humanos).

## Contratos (já descrito acima)

## Outros módulos importantes

- `ContratosController` — contrato e compliance (detalhado acima).
- `LicencasOperacaoController` — gestão de licenças de operação (`LicencasOperacaoModel`).
- `LicitacoesController` — licitações (`LicitacoesModel`).
- `TreinamentosController` — treinamentos (`TreinamentosModel`).
- `PerfilController` — gerenciamento de perfis (`PerfilModel`).
- `OrganogramaController` — cargos e atividades (`OrganogramaModel`).
- `PopsController` — procedimentos operacionais padrão (`PopsModel`).
- `PradController` — PRADs (`PradModel`).
- `BancoController` — bancos (`BancoModel`).
- `ClassificacaoController` / `CategoriasController` — classificação e categorias (vinculados ao financeiro, clientes, etc.).
- `ConfiguracoesController` — configurações do sistema.

---

## Observações

- Em muitos controllers há endpoints AJAX (`get*Ajax`, `add*`, etc.) que retornam JSON.
- Controllers usam `BaseController::renderView()` para renderizar views e `renderPartial()` para respostas parciais/AJAX.
- O `App\Core\Connection` é o provedor de acesso ao banco de dados usado pelos models.

Se quiser, eu posso:

- Gerar um arquivo por módulo com as ações listadas e os locais de views correspondentes.
- Extrair automaticamente todos os métodos e gerar um CSV/JSON para facilitar revisão.

Qual prefere como próximo passo? (gerar arquivos por módulo, ou extrair tudo em CSV/JSON para análise)?
