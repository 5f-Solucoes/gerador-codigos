<x-app-layout>
    <x-slot name="title">
        Cadastrar Novo Usuário
    </x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Novo Usuário') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                
                @if ($errors->any())
                    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                        <ul class="list-disc list-inside text-sm">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('users.store') }}">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <x-input-label for="name" :value="__('Nome Completo')" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus 
                                          oninput="atualizarSigla(this.value)" />
                        </div>

                        <div>
                            <x-input-label for="username" :value="__('Usuário (Login WatchGuard)')" />
                            <x-text-input id="username" class="block mt-1 w-full" type="text" name="username" :value="old('username')" required placeholder="Ex: jsilva" />
                        </div>

                        <div>
                            <x-input-label for="sigla" :value="__('Sigla (Automática)')" />
                            <x-text-input id="sigla" class="block mt-1 w-full bg-gray-100 cursor-not-allowed" type="text" readonly />
                        </div>

                        <div>
                            <x-input-label for="email" :value="__('E-mail')" />
                            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required />
                        </div>

                        <div>
                            <x-input-label for="celular" :value="__('Celular')" />
                            <x-text-input id="celular" class="block mt-1 w-full" type="text" name="celular" :value="old('celular')" required />
                        </div>

                        <div>
                            <x-input-label for="perfil" :value="__('Perfil de Acesso')" />
                            <select name="perfil" class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full mt-1">
                                <option value="VENDEDOR">Vendedor</option>
                                <option value="GERENTE">Gerente</option>
                                <option value="ADMIN">Administrador</option>
                            </select>
                        </div>

                        <div>
                            <x-input-label for="status" :value="__('Status')" />
                            <select name="status" class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full mt-1">
                                <option value="ATIVO" selected>Ativo</option>
                                <option value="INATIVO">Inativo</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex items-center justify-end mt-6">
                        <a href="{{ route('users.index') }}" class="text-gray-600 mr-4">Cancelar</a>
                        <x-primary-button>
                            {{ __('Criar Usuário') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function atualizarSigla(nome) {
            const sigla = nome.split(' ')
                              .filter(word => word.length > 0)
                              .map(word => word[0])
                              .join('')
                              .toUpperCase()
                              .substring(0, 5);
            document.getElementById('sigla').value = sigla;
        }
    </script>
</x-app-layout>