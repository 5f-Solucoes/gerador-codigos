<x-app-layout>
    <x-slot name="title">
        Editar Parceiro
    </x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Editar Parceiro') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
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

                <form method="POST" action="{{ route('parceiros.update', $parceiro->id) }}">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <x-input-label for="nome" :value="__('Nome do Parceiro')" />
                            <x-text-input id="nome" class="block mt-1 w-full uppercase" type="text" name="nome" :value="old('nome', $parceiro->nome)" required />
                        </div>

                        <div>
                            <x-input-label for="sigla" :value="__('Sigla')" />
                            <x-text-input id="sigla" class="block mt-1 w-full uppercase" type="text" name="sigla" :value="old('sigla', $parceiro->sigla)" />
                        </div>
                    </div>

                    <div class="flex items-center justify-end mt-4">
                        <a href="{{ route('admin.index', ['tab' => 'parceiros']) }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 mr-4">Cancelar</a>
                        <x-primary-button>
                            {{ __('Salvar Alterações') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>