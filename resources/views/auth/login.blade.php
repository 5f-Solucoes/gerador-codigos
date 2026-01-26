<x-guest-layout>
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <div class="mb-4 text-center">
        <img src="{{ asset('assets/images/logop.png') }}" alt="Logo" class="h-10 mx-auto mb-2">
        <h2 class="text-xl font-bold text-gray-800">Login</h2>
    </div>

    <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-6" role="alert">
        <p class="font-bold"><i class="fas fa-info-circle"></i> Versão de Teste</p>
        <p>Versão de dev do Gerador de proposta</p>
    </div>

    <div id="status-container" class="mb-4 text-sm font-medium"></div>

    <form id="loginFormMFA">
        @csrf

        <div>
            <x-input-label for="login_identifier" :value="__('Usuário ou E-mail')" />
            <x-text-input id="login_identifier" class="block mt-1 w-full" type="text" name="login_identifier" required autofocus autocomplete="username" />
        </div>

        <div class="mt-4">
            <x-input-label for="senha" :value="__('Senha')" />
            <x-text-input id="senha" class="block mt-1 w-full" type="password" name="senha" required autocomplete="current-password" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button id="btnSubmitMFA" class="w-full justify-center">
                {{ __('Entrar') }}
            </x-primary-button>
        </div>
        
        <div id="spinner" class="mt-4 text-center text-gray-600 hidden">
            <svg class="animate-spin h-5 w-5 mr-3 inline-block text-gray-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Aguardando aprovação do Push... ⏳
        </div>
    </form>

    <div class="mt-4 text-center">
        <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('register') }}">
            {{ __('Registrar minha conta') }}
        </a>
    </div>

    <script>
        const form = document.getElementById('loginFormMFA');
        const statusDiv = document.getElementById('status-container');
        const spinner = document.getElementById('spinner');
        const btnSubmit = document.getElementById('btnSubmitMFA');
        let pollInterval = null;

        // Pegando o token CSRF do meta tag que o Laravel Breeze já coloca no layout
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            statusDiv.innerHTML = '';
            statusDiv.className = 'mb-4 text-sm font-medium text-gray-600'; // Reset style
            spinner.classList.add('hidden');
            btnSubmit.disabled = true;

            const username = document.getElementById('login_identifier').value.trim();
            const password = document.getElementById('senha').value;

            if (!username || !password) {
                showError('Preencha usuário e senha.');
                btnSubmit.disabled = false;
                return;
            }

            statusDiv.innerText = 'Verificando credenciais e enviando Push...';

            try {
                // Chama a rota POST /login do Laravel
                const resp = await fetch('{{ route("login") }}', {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken 
                    },
                    body: JSON.stringify({ login_identifier: username, senha: password })
                });

                const data = await resp.json();

                if (!resp.ok) {
                    showError(data.message || 'Erro ao tentar logar.');
                    btnSubmit.disabled = false;
                    return;
                }

                // Sucesso na senha, agora temos o TransactionID
                if (data.transactionId) {
                    statusDiv.className = 'mb-4 text-sm font-medium text-green-600';
                    statusDiv.innerText = 'Push enviado! Verifique seu app AuthPoint.';
                    spinner.classList.remove('hidden');
                    startPolling(data.transactionId);
                    return;
                }

                showError('Resposta inesperada do servidor.');
                btnSubmit.disabled = false;

            } catch (err) {
                console.error(err);
                showError('Falha na comunicação com o servidor.');
                btnSubmit.disabled = false;
            }
        });

        function showError(msg) {
            statusDiv.className = 'mb-4 text-sm font-medium text-red-600';
            statusDiv.innerText = msg;
        }

        function startPolling(transactionId) {
            if (pollInterval) clearInterval(pollInterval);
            let pollCount = 0;
            const POLL_MAX = 60; // 2 minutos
            const POLL_DELAY_MS = 2000;

            pollInterval = setInterval(async () => {
                pollCount++;
                if (pollCount >= POLL_MAX) {
                    clearInterval(pollInterval);
                    spinner.classList.add('hidden');
                    showError('Tempo esgotado. Nenhuma resposta do push.');
                    btnSubmit.disabled = false;
                    return;
                }

                try {
                    // Chama a rota de verificação de status (MfaController)
                    // Nota: A rota no Laravel espera /auth/mfa/status/{id}
                    const url = `/auth/mfa/status/${encodeURIComponent(transactionId)}`;
                    
                    const resp = await fetch(url, {
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        }
                    });
                    
                    const j = await resp.json();

                    console.log('Polling:', j);

                    if (j.status === 'AUTHORIZED') {
                        clearInterval(pollInterval);
                        statusDiv.className = 'mb-4 text-sm font-medium text-green-600';
                        statusDiv.innerText = 'Aprovado! Redirecionando...';
                        
                        // O MfaController pode retornar para onde redirecionar
                        window.location.href = j.redirect || '/dashboard';
                        return;
                    }

                    if (j.status === 'DENIED' || j.status === 'ERROR') {
                        clearInterval(pollInterval);
                        spinner.classList.add('hidden');
                        showError('Autenticação foi negada ou falhou.');
                        btnSubmit.disabled = false;
                        return;
                    }
                    // Se for PENDING, continua...

                } catch (e) {
                    console.error(e);
                    clearInterval(pollInterval);
                    spinner.classList.add('hidden');
                    showError('Erro de rede ao verificar MFA.');
                    btnSubmit.disabled = false;
                }
            }, POLL_DELAY_MS);
        }
    </script>
</x-guest-layout>