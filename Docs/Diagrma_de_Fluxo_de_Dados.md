
```mermaid
---
config:
  look: neo
  layout: elk
---

graph TD
    %% Entidades Externas
    User[Usuário: Admin/Gerente/Vendedor]
    

    %% Processos
    P1(1.0 Autenticação & Controle)
    P2(2.0 Gerenciar Cadastros)
    P3(3.0 Gerar Código de Proposta)
    P4(4.0 Gerar Relatórios / CSV)

    %% Depósitos de Dados (Data Stores)
    D1[(D1 - Tabela Users)]
    D2[(D2 - Tabelas Clientes)]
    D3[(D3 - Tabela Parceiros)]
    D4[(D4 - Tabela Produtos)]
    D5[(D5 - Tabela Propostas)]

    %% Fluxo de Autenticação
    User -->|Login| P1
    P1 -->|Valida| D1
    D1 -->|Retorna Status| P1
    P1 -->|Sessão OK| User

    %% Fluxo de Gestão
    User -->|Dados| P2
    P2 -->|Grava| D2
    P2 -->|Grava| D3
    P2 -->|Grava| D4

    %% Fluxo Principal: Gerar Código
    User -->|Seleciona Opções| P3
    P3 -->|Lê Sigla| D1
    P3 -->|Lê Cliente| D2
    P3 -->|Lê Parceiro| D3
    P3 -->|Lê Produto| D4
    P3 -->|Grava Código| D5
    P3 -->|Exibe Código| User

    %% Fluxo de Relatórios (A PARTE QUE DEU ERRO CORRIGIDA)
    User -->|Filtros| P4
    P4 -->|Consulta Histórico| D5
    D5 -->|Retorna Dados| P4
    P4 -.->|Join Nomes| D1
    P4 -.->|Join Nomes| D2
    P4 -->|Gera CSV| User
    
    %% Saída final
    ```