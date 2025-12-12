# Arquitetura do Projeto (Resumo e Diagrama)

Este documento descreve a arquitetura do projeto como um sistema web PHP com padrão MVC e domínio de ERP/Sistema de Gestão.

---

## Visão Geral

- Padrão arquitetural: **MVC (Model-View-Controller)** — implementação customizada.
- Domínio: **ERP / Sistema de Gestão** com módulos como Financeiro, RH, Projetos, Contratos, Fornecedores, Patrimônio, etc.

---

## Diagrama (Mermaid)

Cole o trecho abaixo em qualquer renderizador Mermaid (VS Code + extensão, GitLab, GitHub, mermaid.live) para visualizar.

```mermaid
flowchart TD
  Browser["Navegador (Usuário)"] -->|HTTP request| PublicIndex["public/index.php\n(Front Controller / Router)"]

  subgraph App [App]
    direction TB
    Controllers["Controllers\n(e.g., FinanceiroController, ContratosController, RhController, UsuarioController, ...) "]
    Models["Models\n(e.g., FinancialModel, EmpresaModel, UsuarioModel, ...) "]
    Views["Views (Server-side)\n(views/layouts/main_template.php, partials, modules)"]
    Core["Core\n(Connection, Model, SessionManager)"]
  end

  PublicIndex --> Controllers
  Controllers --> Models
  Controllers --> Views
  Models --> Core
  Core --> DB[(Banco de Dados - MySQL/Postgres)]
  Controllers -->|usa| Vendor["vendor/ (Composer)\nEx: Dompdf, libs 3rd-party"]
  Controllers -->|usa sessão| Core
  Controllers --> Storage["storage/ (arquivos, uploads, contratos)"]

  style App fill:#f9f,stroke:#333,stroke-width:1px

  classDef infra fill:#fafafa,stroke:#999,stroke-width:1px
  class DB,Vendor,Storage infra

  note right of Controllers
    Padrão: Controller cria/usa Models,
    prepara dados e chama Views.
  end

  click PublicIndex "./public/index.php" "Abrir arquivo"
  click Controllers "./app/controllers" "Abrir pasta de controllers"
  click Models "./models" "Abrir pasta de models"
  click Views "./views" "Abrir pasta de views"
```

---

## Componentes principais (quick map)

- Front Controller: `public/index.php` — roteamento simples: URL -> Controller -> action
- Controllers: `app/controllers/*.php` — módulos de negócio (Financeiro, Contratos, RH, etc.)
- Models: `models/*` — acesso a dados via `App\Core\Connection` e consultas SQL
- Views: `views/*` — templates e partials (server-side rendering)
- Core: `app/core/Connection.php`, `app/core/Model.php`, `app/core/SessionManager.php`
- Vendor: `vendor/` (Composer), ex.: `dompdf` para gerar PDFs
- Storage: `storage/` — arquivos e JSON de configuração (ex.: `storage/config/empresa.json`)

---

## Observações e próximos passos

- Posso gerar também uma versão em PlantUML (`.puml`) e, se quiser, gerar um PNG/SVG do diagrama automaticamente (requer utilitário/serviço de renderização).
- Posso detalhar cada controller com as ações principais e os models usados por cada um.

Quer que eu gere a versão PlantUML e um arquivo PNG/SVG pronto para visualização no repositório? (Posso tentar renderizar localmente se instalar uma ferramenta ou usar um serviço online.)

---

## PlantUML

Criei um arquivo PlantUML simplificado em `docs/architecture.puml`.

Como renderizar:

- Usando a extensão **PlantUML** no VS Code: abra `architecture.puml` e use a pré-visualização (Alt+D / botão preview).
- Usando Docker (gera PNG):

```powershell
# Exemplo: gera output em docs/architecture.png
docker run --rm -v ${PWD}:/workspace plantuml/plantuml -tpng /workspace/docs/architecture.puml -o /workspace/docs
```

- Usando o servidor PlantUML online (mermaid.live / plantuml server): copie o conteúdo e cole no serviço de sua preferência.

> Observação: para gerar imagens automaticamente no CI, você pode adicionar um job que execute o container PlantUML e publique os arquivos gerados como artifacts.

---

## Diagramas Renderizados

Imagens geradas a partir do `architecture.puml` e colocadas em `docs/`:

- `docs/architecture.png`
- `docs/architecture.svg`

Abra-as diretamente no repositório ou no seu explorador de arquivos para visualização rápida.
