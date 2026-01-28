<x-app-layout>
    <x-slot name="title">
        Editar Usuário
    </x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Editar Usuário') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                
                <form method="POST" action="{{ route('users.update', $user->id) }}">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <x-input-label for="name" :value="__('Nome Completo')" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $user->name)" required />
                        </div>

                        <div>
                            <x-input-label for="username" :value="__('Usuário (Login WatchGuard)')" />
                            <x-text-input id="username" class="block mt-1 w-full" type="text" name="username" :value="old('username', $user->username)" required />
                        </div>

                        <div>
                            <x-input-label for="sigla" :value="__('Sigla')" />
                            <x-text-input id="sigla" class="block mt-1 w-full uppercase" type="text" name="sigla" :value="old('sigla', $user->sigla)" required />
                        </div>

                        <div>
                            <x-input-label for="email" :value="__('E-mail')" />
                            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email', $user->email)" required />
                        </div>

                        <div>
                            <x-input-label for="celular" :value="__('Celular')" />
                            <x-text-input id="celular" class="block mt-1 w-full" type="text" name="celular" :value="old('celular', $user->celular)" required />
                        </div>

                        <div>
                            <x-input-label for="perfil" :value="__('Perfil')" />
                            <select name="perfil" class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full mt-1">
                                <option value="VENDEDOR" {{ $user->perfil == 'VENDEDOR' ? 'selected' : '' }}>Vendedor</option>
                                <option value="GERENTE" {{ $user->perfil == 'GERENTE' ? 'selected' : '' }}>Gerente</option>
                                <option value="ADMIN" {{ $user->perfil == 'ADMIN' ? 'selected' : '' }}>Administrador</option>
                            </select>
                        </div>

                        <div>
                            <x-input-label for="status" :value="__('Status')" />
                            <select name="status" class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full mt-1">
                                <option value="ATIVO" {{ $user->status == 'ATIVO' ? 'selected' : '' }}>Ativo</option>
                                <option value="INATIVO" {{ $user->status == 'INATIVO' ? 'selected' : '' }}>Inativo</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex items-center justify-end mt-6">
                        <a href="{{ route('users.index') }}" class="text-gray-600 mr-4">Cancelar</a>
                        <x-primary-button>
                            {{ __('Salvar Alterações') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>