<x-app-layout>
    <x-slot name="title">
        Cadastrar Novo Código
    </x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Cadastrar Novo Código') }}
        </h2>
    </x-slot>

    <div class="py-12" 
         x-data="codigoGenerator({
             clientes: {{ $clientes->toJson() }},
             filiaisDB: {{ $filiais->toJson() }},
             produtosDB: {{ $produtos->toJson() }},
             parceirosDB: {{ $parceiros->toJson() }}, 
             siglaUsuario: '{{ strtoupper(Auth::user()->sigla) }}'
         })">
        
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                
                <div class="mb-6 flex justify-between items-center">
                    <a href="{{ route('dashboard') }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 flex items-center">
                        &larr; Voltar ao Dashboard
                    </a>
                </div>

                <form method="POST" action="{{ route('propostas.store') }}" class="space-y-6">
                    @if ($errors->any())
                        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                            <strong class="font-bold">Ops! Algo deu errado:</strong>
                            <ul class="mt-2 list-disc list-inside text-sm">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    @csrf

                    <div>
                        <x-input-label for="vendedor_sigla" :value="__('Sigla do Responsável')" />
                        <select id="vendedor_sigla" name="vendedor_sigla" x-model="form.vendedorSigla" @change="gerarPreview()"
                                class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm block w-full mt-1">
                            @foreach ($vendedores as $v)
                                <option value="{{ strtoupper($v->sigla) }}" {{ strtoupper($v->sigla) == strtoupper(Auth::user()->sigla) ? 'selected' : '' }}>
                                    {{ $v->name }} - {{ strtoupper($v->sigla) }}
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-sm text-gray-500">Aparecerá no início do código.</p>
                    </div>

                    <div>
                        <x-input-label for="cliente_id" :value="__('Cliente')" />
                        <select id="cliente_id" name="cliente_id" x-model="form.clienteId" @change="onClienteChange()"
                                class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm block w-full mt-1" required>
                            <option value="">Selecione um cliente</option>
                            <template x-for="c in data.clientes" :key="c.id">
                                <option :value="c.id" x-text="c.nome_fantasia"></option>
                            </template>
                        </select>
                    </div>

                    <div>
                        <x-input-label for="filial" :value="__('Localidade (Matriz/Filial)')" />
                        <select id="filial" name="filial" x-model="form.filial" @change="gerarPreview()" :disabled="!form.clienteId"
                                class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm block w-full mt-1 disabled:opacity-50" required>
                            <option value="">Selecione a localidade</option>
                            <template x-for="loc in filteredFiliais" :key="loc">
                                <option :value="loc" x-text="loc"></option>
                            </template>
                        </select>
                        <p x-show="form.clienteId && filteredFiliais.length === 0" class="text-red-500 text-sm mt-1">Nenhuma localidade encontrada.</p>
                    </div>

                    <div>
                        <x-input-label for="parceiro_id" :value="__('Parceiro')" />
                        <select id="parceiro_id" name="parceiro_id" x-model="form.parceiroId" @change="onParceiroChange()"
                                class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm block w-full mt-1" required>
                            <option value="">Selecione um parceiro</option>
                            @foreach($parceiros as $p)
                                <option value="{{ $p->id }}">{{ $p->nome }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <x-input-label for="produto_id" :value="__('Produto')" />
                        <select id="produto_id" name="produto_id" x-model="form.produtoId" @change="gerarPreview()" :disabled="!form.parceiroId"
                                class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm block w-full mt-1 disabled:opacity-50" required>
                            <option value="">Selecione um produto</option>
                            <template x-for="p in filteredProdutos" :key="p.id">
                                <option :value="p.id" x-text="p.nome"></option>
                            </template>
                        </select>
                    </div>

                    <div>
                        <x-input-label for="descricao" :value="__('Descrição (Texto Livre)')" />
                        <x-text-input id="descricao" name="descricao" x-model="form.descricao" @input="gerarPreview()"
                                      class="block mt-1 w-full" placeholder="Ex: projeto piloto" />
                    </div>

                    <div class="p-4 bg-gray-100 dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Prévia do Código</label>
                        <div class="flex">
                            <input type="text" name="codigo_preview" x-model="previewCode" readonly
                                   class="block w-full text-lg font-mono font-bold text-indigo-600 bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-0 focus:border-gray-300">
                        </div>
                        <p class="text-xs text-gray-500 mt-2">Este código será salvo no banco de dados.</p>
                    </div>

                    <div class="flex justify-end pt-4">
                        <x-primary-button class="ml-4">
                            {{ __('Gerar Código') }}
                        </x-primary-button>
                    </div>

                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('codigoGenerator', (initialData) => ({
                data: initialData,
                
                form: {
                    vendedorSigla: initialData.siglaUsuario || '',
                    clienteId: '',
                    filial: '',
                    parceiroId: '',
                    produtoId: '',
                    descricao: ''
                },

                filteredFiliais: [],
                filteredProdutos: [],
                previewCode: '',

                init() {
                    this.gerarPreview();
                },

                onClienteChange() {
                    this.form.filial = '';
                    this.filteredFiliais = [];
                    if (!this.form.clienteId) return;

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
                    this.gerarPreview();
                },

                onParceiroChange() {
                    this.form.produtoId = '';
                    this.filteredProdutos = [];
                    if (this.form.parceiroId) {
                        this.filteredProdutos = this.data.produtosDB
                            .filter(p => p.parceiro_id == this.form.parceiroId);
                    }
                    this.gerarPreview();
                },

                gerarPreview() {
                   //Gera o Prefixo de Data 
                    const hoje = new Date();
                    const ano = hoje.getFullYear().toString().slice(-2);
                    const mes = String(hoje.getMonth() + 1).padStart(2, '0');
                    const dia = String(hoje.getDate()).padStart(2, '0');
                    const prefixoBase = `5F${ano}${mes}${dia}Xv1`; 

                    //Helpers de limpeza
                    const clean = (str) => (str || '').toString().replace(/\s+/g, '').toUpperCase();
                    const cleanDesc = (str) => (str || '').toString().trim().toUpperCase().replace(/\s+/g, '_');
                    const cleanFilial = (str) => (str || '').toString().toUpperCase().replace(/\s+/g, '_');

                    //Captura os textos
                    const vSig = clean(this.form.vendedorSigla);
                    
                    const cliObj = this.data.clientes.find(c => c.id == this.form.clienteId);
                    const cliText = cliObj ? clean(cliObj.nome_fantasia) : '';

                    const filial = cleanFilial(this.form.filial);
                    
                    const parcObj = this.data.parceirosDB?.find(p => p.id == this.form.parceiroId); 
                    const parTextElem = document.getElementById('parceiro_id');
                    const prodTextElem = document.getElementById('produto_id');
                    
                    const parText = parTextElem && parTextElem.selectedIndex > 0 ? parTextElem.options[parTextElem.selectedIndex].text : '';
                    const prodText = prodTextElem && prodTextElem.selectedIndex > 0 ? prodTextElem.options[prodTextElem.selectedIndex].text : '';

                    const pClean = clean(parText);
                    const prodClean = clean(prodText);
                    const desc = cleanDesc(this.form.descricao);

                    // Montagem Final
                    const parts = [vSig, cliText, filial];
                    
                    let suffix = parts.filter(p => p !== '').join('-');
                    
                    if (pClean && prodClean) {
                        suffix += `-${pClean}_${prodClean}`;
                    } else if (pClean) {
                         suffix += `-${pClean}`;
                    }

                    if (desc) {
                        suffix += `-${desc}`;
                    }

                    this.previewCode = `${prefixoBase}-${suffix}`;
                }
            }));
        });
    </script>
</x-app-layout>