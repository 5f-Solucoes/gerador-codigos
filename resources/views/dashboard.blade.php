<x-app-layout>
    <x-slot name="title">
        Dashboard
    </x-slot>
    <div x-data="{
    showToast: false,
    toastMessage: '',
    copyToClipboard(text) {
        if (!text) return;

        // Tenta o método moderno (só funciona em HTTPS ou localhost)
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(text).then(() => {
                this.triggerToast();
            }).catch(err => {
                this.fallbackCopy(text);
            });
        } else {
            // Usa o método antigo (funciona no seu IP 10.1.x.x)
            this.fallbackCopy(text);
        }
    },
    fallbackCopy(text) {
        const textArea = document.createElement('textarea');
        textArea.value = text;
        
        // Garante que o elemento não seja visível na tela
        textArea.style.position = 'fixed';
        textArea.style.left = '-9999px';
        textArea.style.top = '0';
        
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();

        try {
            document.execCommand('copy');
            this.triggerToast();
        } catch (err) {
            console.error('Erro ao copiar', err);
            alert('Não foi possível copiar automaticamente.');
        }

        document.body.removeChild(textArea);
    },
    triggerToast() {
        this.toastMessage = 'Código copiado!';
        this.showToast = true;
        setTimeout(() => this.showToast = false, 3000);
    }
}">

    <x-slot name="header">
        <div class="flex justify-between items-center h-10">
            
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight flex items-center gap-2">
                {{ __('Códigos Gerados') }}
            </h2>
            
            <form action="{{ route('dashboard') }}" method="GET" class="w-64">
                <div class="relative w-full">
                    <input type="text" name="search" 
                           class="w-full py-2 px-4 text-sm bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:bg-white focus:ring-2 focus:ring-indigo-500 text-gray-900 dark:text-white placeholder-gray-500" 
                           placeholder="Pesquisar..." 
                           autocomplete="off"
                           value="{{ request('search') }}">
                </div>
            </form>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @if(session('success'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)" 
                     class="mb-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded shadow-sm relative">
                    {{ session('success') }}
                    <button @click="show = false" class="absolute top-0 bottom-0 right-0 px-4">&times;</button>
                </div>
            @endif

            <div class="flex justify-end mb-4">
                <a href="{{ route('propostas.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-full font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 active:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Novo Código
                </a>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg border border-gray-100 dark:border-gray-700">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400 border-b">
                                <tr>
                                    <th scope="col" class="px-6 py-3 w-20">ID</th> 
                                    <th scope="col" class="px-6 py-3 w-40">Data</th> 
                                    <th scope="col" class="px-6 py-3 w-48">Código</th>
                                    <th scope="col" class="px-6 py-3">Responsável</th>
                                    <th scope="col" class="px-6 py-3 w-32 text-right">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($projetos as $p)
                                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                    <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                                        #{{ $p->id }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ \Carbon\Carbon::parse($p->data)->format('d/m/Y') }}
                                        <span class="text-xs text-gray-400 block">{{ \Carbon\Carbon::parse($p->data)->format('H:i') }}</span>
                                    </td>
                                    <td class="px-6 py-4 font-mono text-indigo-600 dark:text-indigo-400">
                                        {{ $p->codigo_proposta }}
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <span>{{ $p->user ? explode(' ', $p->user->name)[0] : 'N/D' }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end space-x-3">
                                            <button @click="copyToClipboard('{{ $p->codigo_proposta }}')" class="text-black-400 hover:text-green-600" title="Copiar">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                            </button>

                                            @if(in_array(Auth::user()->perfil, ['GERENTE','ADMIN']) || $p->user_id == Auth::user()->id)
                                                <a href="{{ route('propostas.edit', $p->id) }}" class="text-black-400 hover:text-blue-600" title="Editar">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                                </a>
                                            @endif

                                            @if(in_array(Auth::user()->perfil, ['GERENTE','ADMIN']))
                                                <form action="{{ route('propostas.destroy', $p->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Excluir permanentemente?');">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="text-black-400 hover:text-red-600" title="Excluir">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                        Nenhum código encontrado.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">{{ $projetos->links() }}</div>
                </div>
            </div>
        </div>
    </div>

    <div x-show="showToast" 
         x-transition:enter="transform ease-out duration-300 transition"
         x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
         x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed bottom-5 right-5 z-50 bg-indigo-900 text-white px-4 py-2 rounded shadow-lg flex items-center gap-2">
        <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
        <span x-text="toastMessage"></span>
    </div>
    </div>
</x-app-layout>