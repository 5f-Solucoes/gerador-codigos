================================================================================
GERADOR DE CÓDIGOS - ESTRUTURA E GUIA DE EXECUÇÃO
================================================================================

## DESCRIÇÃO DO PROJETO

Aplicação web desenvolvida com Laravel 12 e Tailwind CSS para geração de códigos
de proposta. O projeto utiliza Vite como build tool e inclui suporte para banco de
dados com migrations automáticas.

================================================================================
## ESTRUTURA DE PASTAS
================================================================================

app/
  ├── Console/Commands/          - Comandos Artisan customizados
  ├── Http/
  │   ├── Controllers/           - Controladores da aplicação
  │   └── Requests/              - Form requests (validação)
  ├── Models/                    - Modelos Eloquent
  │   ├── Cliente.php            - Modelo de clientes
  │   ├── CodigoDeProposta.php    - Modelo de códigos de proposta
  │   ├── Filial.php             - Modelo de filiais
  │   ├── Parceiro.php           - Modelo de parceiros
  │   ├── Produto.php            - Modelo de produtos
  │   └── User.php               - Modelo de usuários
  ├── Services/                  - Serviços de negócio
  │   └── WatchGuardService.php   - Serviço de integração WatchGuard
  └── Providers/                 - Service Providers

bootstrap/
  ├── app.php                    - Bootstrap da aplicação
  └── providers.php              - Registro de providers

config/
  ├── app.php                    - Configurações gerais
  ├── auth.php                   - Configurações de autenticação
  ├── cache.php                  - Configurações de cache
  ├── database.php               - Configurações de banco de dados
  ├── filesystems.php            - Configurações de armazenamento
  ├── logging.php                - Configurações de logging
  ├── mail.php                   - Configurações de email
  ├── queue.php                  - Configurações de fila
  ├── services.php               - Configurações de serviços
  └── session.php                - Configurações de sessão

database/
  ├── factories/                 - Model Factories para testes
  ├── migrations/                - Scripts de migração do banco
  └── seeders/                   - Seeders para popular banco

resources/
  ├── css/
  │   └── app.css               - Estilos CSS (Tailwind)
  ├── js/
  │   └── app.js                - Scripts JavaScript
  └── views/                     - Templates Blade

routes/
  ├── auth.php                  - Rotas de autenticação
  ├── console.php               - Rotas console/commands
  └── web.php                   - Rotas da web

storage/
  ├── app/                      - Arquivos da aplicação
  ├── framework/                - Cache e dados do framework
  └── logs/                     - Arquivos de log

tests/
  ├── Feature/                  - Testes de funcionalidade
  └── Unit/                     - Testes unitários

public/
  ├── index.php                 - Ponto de entrada da aplicação
  └── build/                    - Assets compilados

vendor/                          - Dependências do Composer

Docs/
  ├── Arquitetura.md            - Documentação de arquitetura
  ├── Arquitetura_aplicação.md  - Detalhes de arquitetura
  ├── Codigo.md                 - Documentação de código
  ├── Diagrama_de_Sequencia.md  - Diagramas de sequência
  ├── Diagrama_de_relacionamento.md - Diagrama ER
  ├── Diagrma_de_Fluxo_de_Dados.md - Fluxo de dados
  ├── Login.md                  - Documentação de login
  └── Readme.txt                - Este arquivo

================================================================================
## PRÉ-REQUISITOS
================================================================================

- PHP 8.2 ou superior
- Composer (gerenciador de dependências PHP)
- Node.js 18+ e npm
- Git
- Um banco de dados suportado (PostgreSQL, MySQL, SQLite, etc.)

================================================================================
## INSTALAÇÃO E CONFIGURAÇÃO
================================================================================

### 1. Clonar o Repositório
git clone <url-do-repositorio>
cd gerador-codigos

### 2. Configuração Rápida (Recomendado)
Execute o script de setup que instala tudo automaticamente:
composer run-script setup

Este comando executa:
- composer install
- Cria arquivo .env a partir de .env.example
- Gera chave de aplicação
- Executa migrations do banco
- npm install
- npm run build (Vite)

### 3. Configuração Manual
Se preferir configurar passo a passo:

#### 3.1 Instalar dependências PHP
composer install



#### 3.2 Configurar ambiente
cp .env.example .env
php artisan key:generate

#### 3.3 Configurar banco de dados
Edite o arquivo .env e atualize as variáveis de banco:
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=gerador_codigos
DB_USERNAME=root
DB_PASSWORD=

#### 3.4 Executar migrations
php artisan migrate

#### 3.5 Instalar dependências JavaScript
npm install

#### 3.6 Build de assets (Vite)
npm run build

================================================================================
## EXECUTANDO A APLICAÇÃO
================================================================================

### Modo Desenvolvimento (Recomendado)
Use o script de desenvolvimento que executa tudo necessário simultaneamente:
composer run-script dev

Este comando executa em paralelo:
- Servidor Laravel (php artisan serve) na porta 8000
- Laravel Queue Worker
- Laravel Pail (logs em tempo real)
- Vite Dev Server (hot reload para assets)

A aplicação estará acessível em: http://localhost:8000

### Execução Manual
Se preferir executar os serviços individualmente em terminais separados:

Terminal 1 - Servidor Laravel:
php artisan serve

Terminal 2 - Dev Server (Assets com hot reload):
npm run dev

Terminal 3 (opcional) - Fila de jobs:
php artisan queue:listen

Terminal 4 (opcional) - Logs em tempo real:
php artisan pail

================================================================================
## COMANDOS ÚTEIS
================================================================================

# Gerar nova migration
php artisan make:migration create_table_name

# Executar migrations
php artisan migrate

# Desfazer última migration
php artisan migrate:rollback

# Gerar model com migration e controller
php artisan make:model NomeModel -mrc

# Gerar controller
php artisan make:controller NomeController

# Limpar cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Listar todas as rotas
php artisan route:list

# Acessar console interativo (Tinker)
php artisan tinker

================================================================================
## TESTES
================================================================================

Executar todos os testes:
composer run-script test

Executar testes com cobertura de código:
php artisan test --coverage

================================================================================
## BUILD PARA PRODUÇÃO
================================================================================

npm run build

Isto compilará os assets (CSS e JavaScript) para produção em public/build/.

================================================================================
## ESTRUTURA DE BANCO DE DADOS
================================================================================

A aplicação utiliza os seguintes modelos:

- Users: Usuários do sistema
- Parceiros: Parceiros comerciais
- Clientes: Dados dos clientes
- Produtos: Catálogo de produtos
- Filiais: Filiais da empresa
- CodigoDeProposta: Códigos de proposta gerados

As migrations estão localizadas em database/migrations/

================================================================================
## CONFIGURAÇÕES IMPORTANTES
================================================================================

### .env (Arquivo de Ambiente)
Arquivo responsável pelas configurações específicas da instância:
- Credenciais de banco de dados
- Chave de aplicação
- Modo debug
- Serviços de email, cache, etc.

NUNCA faça commit do arquivo .env em repositórios públicos!

### Variáveis de Ambiente Essenciais
APP_NAME=Gerador Codigos
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000
DB_CONNECTION=mysql
DB_DATABASE=gerador_codigos

================================================================================
## SUPORTE E DOCUMENTAÇÃO ADICIONAL
================================================================================

Para mais detalhes sobre a arquitetura e implementação, consulte:

- Docs/Arquitetura.md - Visão geral da arquitetura
- Docs/Arquitetura_aplicação.md - Detalhes técnicos
- Docs/Diagrama_de_relacionamento.md - Modelo de dados
- Docs/Diagrama_de_Sequencia.md - Fluxos de negócio

Laravel Documentation: https://laravel.com/docs
Tailwind CSS: https://tailwindcss.com/docs
Vite: https://vitejs.dev/

================================================================================
