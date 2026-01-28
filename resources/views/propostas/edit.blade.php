<x-app-layout>
    <x-slot name="title">
        Editar Código
    </x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Editar Código') }} <span class="text-gray-500 text-sm ml-2">#{{ $proposta->id }}</span>
        </h2>
    </x-slot>

    <div class="py-12" 
         x-data="editForm({
             clientes: {{ $clientes->toJson() }},
             filiaisDB: {{ $filiais->toJson() }},
             produtosDB: {{ $produtos->toJson() }},
             // Dados atuais do registro para pré-preencher
             current: {
                 clienteId: {{ $proposta->cliente_id }},
                 filial: '{{ $proposta->filial_nome }}',
                 parceiroId: {{ $proposta->parceiro_id }},
                 produtoId: {{ $proposta->produto_id }}
             }
         })">
        
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                
                <div class="mb-6">
                    <a href="{{ route('dashboard') }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400">&larr; Voltar</a>
                </div>

                <div class="mb-6 p-4 bg-blue-50 dark:bg-gray-700 border-l-4 border-blue-500 text-blue-700 dark:text-blue-200">
                    <p class="font-bold text-xs uppercase tracking-wide">Código Atual:</p>
                    <p class="font-mono text-lg">{{ $proposta->codigo_proposta }}</p>
                    <p class="text-xs mt-1 opacity-75">Ao salvar, a estrutura (Cliente, Filial, etc) será atualizada, mas a data e a sequência inicial ({{ explode('-', $proposta->codigo_proposta)[0] }}) serão mantidas.</p>
                </div>

                <form method="POST" action="{{ route('propostas.update', $proposta->id) }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div>
                        <x-input-label for="vendedor_sigla" :value="__('Sigla do Responsável')" />
                        <select id="vendedor_sigla" name="vendedor_sigla"
                                class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full mt-1">
                            <option value="">{{ $siglaNoCodigo }} (Manter Atual)</option>
                            @foreach ($siglasAtivas as $s)
                                <option value="{{ $s }}">{{ $s }}</option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Selecione para transferir o código para outra sigla.</p>
                    </div>

                    <div>
                        <x-input-label for="cliente_id" :value="__('Cliente')" />
                        <select id="cliente_id" name="cliente_id" x-model="form.clienteId" @change="onClienteChange()"
                                class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full mt-1" required>
                            <option value="">Selecione um cliente</option>
                            <template x-for="c in data.clientes" :key="c.id">
                                <option :value="c.id" x-text="c.nome_fantasia" :selected="c.id == form.clienteId"></option>
                            </template>
                        </select>
                    </div>

                    <div>
                        <x-input-label for="filial" :value="__('Localidade')" />
                        <div x-show="filteredFiliais.length > 1">
                            <select id="filial_select" name="filial" x-model="form.filial" 
                                    class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full mt-1">
                                <template x-for="loc in filteredFiliais" :key="loc">
                                    <option :value="loc" x-text="loc" :selected="loc == form.filial"></option>
                                </template>
                            </select>
                        </div>

                        <div x-show="filteredFiliais.length <= 1">
                            <input type="text" name="filial" x-model="form.filial" readonly
                                   class="bg-gray-100 dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-500 dark:text-gray-400 rounded-md shadow-sm block w-full mt-1 cursor-not-allowed">
                        </div>
                    </div>

                    <div>
                        <x-input-label for="parceiro_id" :value="__('Parceiro')" />
                        <select id="parceiro_id" name="parceiro_id" x-model="form.parceiroId" @change="onParceiroChange()"
                                class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full mt-1" required>
                            <option value="">Selecione</option>
                            @foreach($parceiros as $p)
                                <option value="{{ $p->id }}">{{ $p->nome }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <x-input-label for="produto_id" :value="__('Produto')" />
                        <select id="produto_id" name="produto_id" x-model="form.produtoId"
                                class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full mt-1" required>
                            <option value="">Selecione</option>
                            <template x-for="p in filteredProdutos" :key="p.id">
                                <option :value="p.id" x-text="p.nome" :selected="p.id == form.produtoId"></option>
                            </template>
                        </select>
                    </div>

                    <div>
                        <x-input-label for="descricao" :value="__('Descrição')" />
                        <x-text-input id="descricao" name="descricao" value="{{ $proposta->descricao }}"
                                      class="block mt-1 w-full" />
                    </div>

                    <div class="flex justify-end pt-4">
                        <x-primary-button>
                            {{ __('Salvar Alterações') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('editForm', (initialData) => ({
                data: initialData,
                form: {
                    clienteId: initialData.current.clienteId,
                    filial: initialData.current.filial,
                    parceiroId: initialData.current.parceiroId,
                    produtoId: initialData.current.produtoId
                },
                filteredFiliais: [],
                filteredProdutos: [],

                init() {
                    // Carrega listas iniciais baseadas no banco
                    this.onClienteChange(true); 
                    this.onParceiroChange(true); 
                },

                onClienteChange(isInit = false) {
                    if (!isInit) this.form.filial = ''; 
                    this.filteredFiliais = [];
                    
                    if (!this.form.clienteId) return;

                    // Lógica de Filiais 
                    const filiaisDoBanco = this.data.filiaisDB
                        .filter(f => f.cliente_id == this.form.clienteId)
                        .map(f => f.localidade);

                    if (filiaisDoBanco.length === 0) {
                        const clienteSelecionado = this.data.clientes.find(c => c.id == this.form.clienteId);
                        if (clienteSelecionado && clienteSelecionado.site) {
                            const sites = clienteSelecionado.site.split(',').map(s => s.trim()).filter(s => s);
                            filiaisDoBanco.push(...sites);
                        }
                    }
                    this.filteredFiliais = filiaisDoBanco;

                    if (!isInit && this.filteredFiliais.length === 1) {
                        this.form.filial = this.filteredFiliais[0];
                    }
                    if (isInit && this.form.filial && !this.filteredFiliais.includes(this.form.filial)) {
                        this.filteredFiliais.push(this.form.filial);
                    }
                },

                onParceiroChange(isInit = false) {
                    if (!isInit) this.form.produtoId = '';
                    this.filteredProdutos = [];

                    if (this.form.parceiroId) {
                        this.filteredProdutos = this.data.produtosDB
                            .filter(p => p.parceiro_id == this.form.parceiroId);
                    }
                }
            }));
        });
    </script>
</x-app-layout>