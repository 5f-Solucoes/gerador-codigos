```mermaid
sequenceDiagram
    autonumber
    actor User as Usuário (Vendedor/Gerente/Admin)
    participant Browser as Navegador (View)
    participant Route as Rota (Laravel)
    participant Ctrl as PropostaController
    participant Models as Models (Cliente/Parceiro/Produto)
    participant CodigoModel as CodigoDeProposta (Model)
    participant DB as Banco de Dados

    %% FLUXO 1: Carregar a Tela
    Note over User, DB: 1. Carregamento do Formulário
    User->>Browser: Acessa "Gerar Código"
    Browser->>Route: GET /propostas/create
    Route->>Ctrl: create()
    
    Ctrl->>Models: Clientes::all(), Parceiros::all(), Produtos::all()
    Models->>DB: SELECT * FROM Clientes, Parceiros, Produtos
    DB-->>Models: Retorna Listas
    Models-->>Ctrl: Collections de Dados
    
    Ctrl-->>Browser: Retorna View com Dropdowns preenchidos

    %% FLUXO 2: Enviar Dados
    Note over User, DB: 2. Geração do Código
    User->>Browser: Seleciona Cliente, Parceiro, Produto
    User->>Browser: Clica em "Gerar"
    Browser->>Route: POST /propostas
    Route->>Ctrl: store(Request $request)

    activate Ctrl
    Ctrl->>Ctrl: Valida Inputs (required, exists)
    
    %% Lógica de Negócio (Exemplo)
    Ctrl->>Ctrl: Monta String do Código (SiglaUser + Data + ID...)
    
    Ctrl->>CodigoModel: create([codigo, cliente_id, parceiro_id, produto_id])
    activate CodigoModel
    CodigoModel->>DB: INSERT INTO codigo_de_propostas
    DB-->>CodigoModel: Sucesso (ID criado)
    deactivate CodigoModel
    
    Ctrl-->>Browser: Redirect com 'success'
    deactivate Ctrl

    Browser-->>User: Exibe Tabela com novo código e alerta de sucesso
    ```