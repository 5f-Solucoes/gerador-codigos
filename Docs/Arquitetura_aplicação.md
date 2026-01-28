```mermaid
graph LR
    %% Atores
    User((Usuário))

    %% Camada de Apresentação
    subgraph ViewLayer [Views e Templates]
        Blade[Blade Templates]
    end

    %% Camada de Controle
    subgraph ControllerLayer [Http Controllers]
        AdminCtrl[AdminController]
        AuthMid[Middleware Auth]
    end

    %% Camada de Modelo
    subgraph ModelLayer [Eloquent Models]
        PropMod[CodigoDeProposta]
        AuxMods[Cliente, Parceiro, Produto]
    end

    %% Fluxo (SEM PARENTESES NO TEXTO)
    User -->|1. Acessa URL| AuthMid
    AuthMid -->|2. Autorizado| AdminCtrl
    
    %% Exemplo: Gerar Relatório
    AdminCtrl -->|3. Solicita Dados| PropMod
    PropMod -->|4. Join User e Cliente| AuxMods
    AuxMods -->|5. Retorna Dados| PropMod
    PropMod -->|6. Retorna Collection| AdminCtrl
    
    AdminCtrl -->|7. Renderiza| Blade
    Blade -->|8. Resposta HTML e CSV| User
    ```