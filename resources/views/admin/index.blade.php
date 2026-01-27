<x-app-layout>
    <x-slot name="title">
        Área Administrativa
    </x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Área Administrativa') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="{ 
        tab: 'clientes',
        searchCli: '',
        searchPar: '',
        searchProd: '' 
    }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded-r shadow-sm">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-green-700">{{ session('success') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <div class="border-b border-gray-200 dark:border-gray-700 mb-6">
                <nav class="-mb-px flex space-x-8 overflow-x-auto" aria-label="Tabs">
                    <button @click="tab = 'clientes'"
                        :class="tab === 'clientes'
                            ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400'
                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        Clientes
                    </button>

                    @if(in_array(Auth::user()->perfil, ['GERENTE','ADMIN']))
                    <button @click="tab = 'parceiros'"
                        :class="tab === 'parceiros'
                            ? 'border-purple-500 text-purple-600 dark:text-purple-400'
                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                        Parceiros
                    </button>

                    <button @click="tab = 'produtos'"
                        :class="tab === 'produtos'
                            ? 'border-red-500 text-red-600 dark:text-red-400'
                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                        Produtos
                    </button>

                    <button @click="tab = 'relatorios'"
                        :class="tab === 'relatorios'
                            ? 'border-green-500 text-green-600 dark:text-green-400'
                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        Relatórios
                    </button>
                    @endif
                </nav>
            </div>

            <div x-show="tab === 'clientes'" x-transition.opacity.duration.300ms>
                
                <div class="flex justify-end items-center mb-4 gap-2">
                    <input type="text" x-model="searchCli" 
                           class="w-64 py-2 px-4 text-sm bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-indigo-500 focus:border-indigo-500 text-gray-900 dark:text-white placeholder-gray-500" 
                           placeholder="Pesquisar cliente...">

                    <a href="{{ route('clientes.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-full font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 transition ease-in-out duration-150 h-[38px]">
                        + Novo Cliente
                    </a>
                </div>
                
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-1/4">Nome Fantasia</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-1/5">CNPJs</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Localidades / Site</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-32">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($clientes as $c)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition"
                                x-show="$el.textContent.toLowerCase().includes(searchCli.toLowerCase())">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $c->nome_fantasia }}</div>
                                    <div class="text-xs text-gray-500">{{ $c->razao_social }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                    @if($c->filiais->count() > 0)
                                        @foreach($c->filiais as $f)
                                            <div class="text-xs font-mono block mb-1">{{ $f->cnpj }} <span class="text-gray-400">({{ $f->localidade }})</span></div>
                                        @endforeach
                                    @else
                                        <span class="text-red-400 text-xs">Sem CNPJ</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-300">
                                    @if($c->filiais->count() > 0)
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($c->filiais as $f)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300">
                                                    {{ $f->localidade }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @else
                                        <a href="{{ $c->site }}" target="_blank" class="text-indigo-600 hover:underline">{{ $c->site ?? 'N/A' }}</a>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="{{ route('clientes.edit', $c->id) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">Editar</a>
                                    @if(Auth::user()->perfil === 'ADMIN')
                                    <form action="{{ route('clientes.destroy', $c->id) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir o cliente {{ $c->nome_fantasia }}?');" class="inline-block">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900 font-bold text-xs">Excluir</button>
                                    </form>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-6 py-10 text-center text-gray-500">
                                    Nenhum cliente cadastrado ainda.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div x-show="tab === 'parceiros'" x-transition.opacity.duration.300ms style="display: none;">
                
                <div class="flex justify-end items-center mb-4 gap-2">
                    <input type="text" x-model="searchPar" 
                           class="w-64 py-2 px-4 text-sm bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-purple-500 focus:border-purple-500 text-gray-900 dark:text-white placeholder-gray-500" 
                           placeholder="Pesquisar parceiro...">

                    <a href="{{ route('parceiros.create') }}" class="inline-flex items-center px-4 py-2 bg-purple-600 border border-transparent rounded-full font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-700 focus:bg-purple-700 transition ease-in-out duration-150 h-[38px]">
                        + Novo Parceiro
                    </a>
                </div>

                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Nome</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Criado em</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Sigla</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-32">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($parceiros as $p)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition"
                                x-show="$el.textContent.toLowerCase().includes(searchPar.toLowerCase())">
                                <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900 dark:text-white">{{ $p->nome }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $p->created_at->format('d/m/Y') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $p->sigla ?? '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="{{ route('parceiros.edit', $p->id) }}" class="text-indigo-600 hover:text-indigo-900 mr-3 font-bold text-xs">Editar</a>
                                    @if(Auth::user()->perfil === 'ADMIN')
                                        <form action="{{ route('parceiros.destroy', $p->id) }}" method="POST" onsubmit="return confirm('Tem certeza?');" class="inline-block">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900 font-bold text-xs">Excluir</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="px-6 py-4 text-center text-gray-500">Nenhum parceiro encontrado.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div x-show="tab === 'produtos'" x-transition.opacity.duration.300ms style="display: none;">
                
                <div class="flex justify-end items-center mb-4 gap-2">
                    <input type="text" x-model="searchProd" 
                           class="w-64 py-2 px-4 text-sm bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-red-500 focus:border-red-500 text-gray-900 dark:text-white placeholder-gray-500" 
                           placeholder="Pesquisar produto...">

                    <a href="{{ route('produtos.create') }}" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-full font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 transition ease-in-out duration-150 h-[38px]">
                        + Novo Produto
                    </a>
                </div>

                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produto</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Parceiro</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sigla</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider w-32">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($produtos as $prod)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition"
                                x-show="$el.textContent.toLowerCase().includes(searchProd.toLowerCase())">
                                <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900 dark:text-white">{{ $prod->nome }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">
                                        {{ $prod->parceiro ? $prod->parceiro->nome : 'N/D' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $prod->sigla }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="{{ route('produtos.edit', $prod->id) }}" class="text-indigo-600 hover:text-indigo-900 mr-3 font-bold text-xs">Editar</a>
                                    @if(Auth::user()->perfil === 'ADMIN')
                                        <form action="{{ route('produtos.destroy', $prod->id) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir o produto {{ $prod->nome }}?');" class="inline-block">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900 font-bold text-xs">Excluir</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="px-6 py-4 text-center text-gray-500">Nenhum produto cadastrado.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div x-show="tab === 'relatorios'" x-transition.opacity.duration.300ms style="display: none;">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="lg:col-span-2 bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg border border-gray-200 dark:border-gray-700">
                        <div class="p-6 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white flex items-center">
                                <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                                Relatório Personalizado
                            </h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Selecione os filtros abaixo para gerar um CSV específico.</p>
                        </div>
                        <div class="p-6">
                            <form action="{{ route('admin.export.csv') }}" method="GET">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="filter_user" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Responsável / Sigla</label>
                                        <select id="filter_user" name="user_id" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                            <option value="">Todos os Responsáveis</option>
                                            @foreach($users as $u)
                                                <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->sigla ?? 'N/A' }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label for="filter_parceiro" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Parceiro (Fabricante)</label>
                                        <select id="filter_parceiro" name="parceiro_id" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                            <option value="">Todos os Parceiros</option>
                                            @foreach($parceiros as $par)
                                                <option value="{{ $par->id }}">{{ $par->nome }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="md:col-span-2">
                                        <label for="filter_cliente" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Cliente</label>
                                        <select id="filter_cliente" name="cliente_id" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                            <option value="">Todos os Clientes</option>
                                            @foreach($clientes as $cli)
                                                <option value="{{ $cli->id }}">{{ $cli->nome_fantasia }} - {{ $cli->razao_social }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label for="data_inicio" class="block text-sm font-medium text-gray-700 dark:text-gray-300">De:</label>
                                        <input type="date" name="data_inicio" id="data_inicio" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    </div>
                                    <div>
                                        <label for="data_fim" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Até:</label>
                                        <input type="date" name="data_fim" id="data_fim" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    </div>
                                </div>
                                <div class="mt-6">
                                    <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150">
                                        <svg class="w-5 h-5 mr-2 -ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                        Gerar Relatório Filtrado
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="space-y-6">
                        <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg border border-green-200 dark:border-green-900 p-6 flex flex-col items-center text-center">
                            <div class="p-3 bg-green-100 text-green-600 rounded-full mb-4">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Exportação Completa</h3>
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                Baixe todo o histórico de códigos sem nenhum filtro aplicado.
                            </p>
                            <a href="{{ route('admin.export.csv') }}" class="mt-4 w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-green-700 bg-green-100 hover:bg-green-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition">
                                Baixar Tudo (.csv)
                            </a>
                        </div>
                        
                    </div>
                </div>
            </div>
    </div>
</x-app-layout>