<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Cadastrar Cliente') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">

                @if ($errors->any())
                    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                        <strong class="font-bold">Verifique os erros:</strong>
                        <ul class="mt-2 list-disc list-inside text-sm">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('clientes.store') }}">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <x-input-label for="nome_fantasia" :value="__('Nome Fantasia (Sem espaços)')" />
                            <x-text-input id="nome_fantasia" class="block mt-1 w-full uppercase" type="text" name="nome_fantasia" :value="old('nome_fantasia')" required autofocus />
                            <p class="text-xs text-gray-500 mt-1">Usado na geração do código. Ex: MICROSOFT</p>
                        </div>

                        <div>
                            <x-input-label for="razao_social" :value="__('Razão Social (Opcional)')" />
                            <x-text-input id="razao_social" class="block mt-1 w-full uppercase" type="text" name="razao_social" :value="old('razao_social')" />
                        </div>
                    </div>

                    <hr class="border-gray-200 dark:border-gray-700 mb-6">

                    <div x-data="{
                        filiais: [{cnpj: '', localidade: ''}],
                        add() {
                            this.filiais.push({cnpj: '', localidade: ''});
                        },
                        remove(index) {
                            if(this.filiais.length > 1) {
                                this.filiais.splice(index, 1);
                            }
                        }
                    }">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Filiais / Localidades</h3>
                            <button type="button" @click="add()" class="text-sm bg-indigo-100 text-indigo-700 px-3 py-1 rounded hover:bg-indigo-200 transition">
                                + Adicionar Filial
                            </button>
                        </div>

                        <div class="space-y-3">
                            <template x-for="(filial, index) in filiais" :key="index">
                                <div class="flex gap-4 items-start">
                                    <div class="w-1/2">
                                        <x-input-label :value="__('CNPJ')" />
                                        <input type="text" :name="'filiais['+index+'][cnpj]'" x-model="filial.cnpj" placeholder="00.000.000/0001-00" required
                                               class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    </div>
                                    <div class="w-1/2">
                                        <x-input-label :value="__('Localidade (Ex: SP, Apenas a sigla)')" />
                                        <input type="text" :name="'filiais['+index+'][localidade]'" x-model="filial.localidade" placeholder="SAO_PAULO" required
                                               class="w-full uppercase border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    </div>
                                    <div class="pt-6">
                                        <button type="button" @click="remove(index)" class="text-red-500 hover:text-red-700" title="Remover">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>
                        
                        <p class="text-xs text-gray-500 mt-2">Dica: Se o cliente não tem filial, cadastre como "MATRIZ" ou "UNICA".</p>
                    </div>

                    <div class="flex items-center justify-end mt-8">
                        <a href="{{ route('admin.index') }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 mr-4">Cancelar</a>
                        <x-primary-button>
                            {{ __('Salvar Cliente') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>