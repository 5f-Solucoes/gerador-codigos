<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Cadastrar Produto') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="{
        selectedPartner: '',
        allProducts: {{ $produtos->map(fn($p) => ['id' => $p->id, 'nome' => $p->nome, 'sigla' => $p->sigla, 'parceiro_id' => $p->parceiro_id])->toJson() }},
        get partnerProducts() {
            if (!this.selectedPartner) return [];
            return this.allProducts.filter(p => p.parceiro_id == this.selectedPartner);
        }
    }">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
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

                    <form method="POST" action="{{ route('produtos.store') }}">
                        @csrf

                        <div class="mb-4">
                            <x-input-label for="parceiro_id" :value="__('Parceiro')" />
                            <select id="parceiro_id" name="parceiro_id" x-model="selectedPartner" required
                                    class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full mt-1">
                                <option value="">Selecione um parceiro</option>
                                @foreach($parceiros as $p)
                                    <option value="{{ $p->id }}">{{ $p->nome }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-4">
                            <x-input-label for="nome" :value="__('Nome do Produto (Sem espaços)')" />
                            <x-text-input id="nome" class="block mt-1 w-full uppercase" type="text" name="nome" :value="old('nome')" required placeholder="EX: WINDOWS_11" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="sigla" :value="__('Sigla')" />
                            <x-text-input id="sigla" class="block mt-1 w-full uppercase" type="text" name="sigla" :value="old('sigla')" placeholder="EX: W11" />
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('admin.index', ['tab' => 'produtos']) }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 mr-4">Cancelar</a>
                            <x-primary-button>
                                {{ __('Salvar Produto') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Produtos do Parceiro</h3>
                    
                    <div class="border border-gray-200 dark:border-gray-700 rounded-md overflow-hidden">
                        <ul class="divide-y divide-gray-200 dark:divide-gray-700 bg-gray-50 dark:bg-gray-900 h-64 overflow-y-auto">
                            
                            <template x-if="!selectedPartner">
                                <li class="p-4 text-center text-gray-500 italic text-sm">
                                    Selecione um parceiro ao lado para ver a lista.
                                </li>
                            </template>

                            <template x-if="selectedPartner && partnerProducts.length === 0">
                                <li class="p-4 text-center text-gray-500 italic text-sm">
                                    Nenhum produto cadastrado para este parceiro.
                                </li>
                            </template>

                            <template x-for="prod in partnerProducts" :key="prod.id">
                                <li class="p-3 hover:bg-gray-100 dark:hover:bg-gray-800 flex justify-between items-center transition">
                                    <span class="font-medium text-gray-800 dark:text-gray-200 text-sm" x-text="prod.nome"></span>
                                    <span class="text-xs text-gray-500 bg-gray-200 dark:bg-gray-700 px-2 py-1 rounded" x-text="prod.sigla || '-'"></span>
                                </li>
                            </template>
                        </ul>
                    </div>
                    <p class="text-xs text-gray-400 mt-2 text-center">Lista apenas para consulta visual.</p>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>