```mermaid
graph TD
    subgraph ClientSide [Cliente - Navegador]
        Browser[Navegador Web]
        Alpine[Alpine.js Interativo]
    end

    subgraph ServerSide [Servidor Web]
        WebServer[Web ServerApache]
        PHP[PHP 8.4 FPM]
        
        subgraph LaravelApp [Aplicação Laravel 12]
            Router[Roteamento web.php]
            Controllers[Controllers]
            Artisan[Console Artisan]
        end
    end

    subgraph DataLayer [Camada de Dados MySQL]
        DevDB[(systech_dev_db)]
        LegacyDB[(systech_legado)]
    end

    %% Fluxos
    Browser -->|HTTPS Request| WebServer
    WebServer -->|Passa Requisição| PHP
    PHP -->|Executa| LaravelApp
    
    %% Conexões de Banco (SEM PARENTESES NO TEXTO)
    Controllers -->|Eloquent ORM Leitura e Escrita| DevDB
    Artisan -->|Comando migrar Leitura| LegacyDB
    Artisan -->|Comando migrar Escrita| DevDB

    %% Frontend
    Alpine -.->|Manipula DOM| Browser
    ```