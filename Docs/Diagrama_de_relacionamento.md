```mermaid
erDiagram
    USERS ||--o{ CODIGO_DE_PROPOSTAS : "gera (user_id)"
    CLIENTES ||--o{ CODIGO_DE_PROPOSTAS : "recebe (cliente_id)"
    PARCEIROS ||--o{ CODIGO_DE_PROPOSTAS : "fornece (parceiro_id)"
    PRODUTOS ||--o{ CODIGO_DE_PROPOSTAS : "item (produto_id)"
    CLIENTES ||--|{ FILIAIS : "possui"
    PARCEIROS ||--|{ PRODUTOS : "fabrica"

    CODIGO_DE_PROPOSTAS {
        bigint id PK
        string codigo_proposta "O Código Final Gerado"
        datetime data "Data de Emissão"
        text descricao "Detalhes opcionais"
        string filial_nome "Nome da filial do cliente"
        bigint user_id FK "Vendedor responsável"
        bigint cliente_id FK "Cliente destinatário"
        bigint parceiro_id FK "Fabricante"
        bigint produto_id FK "Produto ofertado"
        timestamp created_at
        timestamp updated_at
    }

    USERS {
        bigint id PK
        string name
        string username "Login WatchGuard"
        string email
        string sigla "Iniciais para o código"
        string perfil "ADMIN, GERENTE, VENDEDOR"
        string status "ATIVO, INATIVO"
    }

    CLIENTES {
        bigint id PK
        string nome_fantasia
        string razao_social
    }

    FILIAIS {
        bigint id PK
        bigint cliente_id FK
        string cnpj
        string localidade
    }

    PARCEIROS {
        bigint id PK
        string nome
        string sigla "Sigla do Fabricante"
    }

    PRODUTOS {
        bigint id PK
        bigint parceiro_id FK
        string nome
        string sigla "Sigla do Produto"
    }
    ```