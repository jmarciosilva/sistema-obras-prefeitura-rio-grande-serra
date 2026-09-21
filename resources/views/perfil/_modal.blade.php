{{--
| Modal "Meu perfil" — autoatendimento do usuário logado (todos os perfis).
| Aberta pelo botão no header: $dispatch('open-modal', 'meu-perfil').
| Reabre automaticamente na aba certa quando há erro de validação
| (error bags 'perfilDados' / 'perfilSenha') ou quando /perfil redireciona.
--}}
@php
    $abaInicial = $errors->perfilSenha->any() ? 'senha' : (session('abrir_perfil') ?: 'dados');
    $abrir = $errors->perfilDados->any() || $errors->perfilSenha->any() || session('abrir_perfil');
    $usuario = auth()->user();
@endphp

<x-modal name="meu-perfil" :show="(bool) $abrir" maxWidth="lg" focusable>
    <div x-data="{ aba: '{{ $abaInicial }}' }">

        {{-- Cabeçalho --}}
        <div class="flex items-center justify-between px-6 py-4 border-b">
            <div>
                <h2 class="text-base font-semibold text-slate-800">👤 Meu perfil</h2>
                <p class="text-xs text-slate-500">{{ ucfirst($usuario->perfil) }} · {{ $usuario->email }}</p>
            </div>
            <button type="button" x-on:click="$dispatch('close')" class="text-slate-400 hover:text-slate-600">✕</button>
        </div>

        {{-- Abas --}}
        <div class="flex gap-1 px-6 pt-4 text-sm">
            <button type="button" x-on:click="aba = 'dados'"
                class="px-3 py-1.5 rounded-md"
                :class="aba === 'dados' ? 'bg-blue-600 text-white' : 'text-slate-600 hover:bg-slate-100'">
                Dados pessoais
            </button>
            <button type="button" x-on:click="aba = 'senha'"
                class="px-3 py-1.5 rounded-md"
                :class="aba === 'senha' ? 'bg-blue-600 text-white' : 'text-slate-600 hover:bg-slate-100'">
                Alterar senha
            </button>
        </div>

        {{-- Aba: nome e e-mail --}}
        <form x-show="aba === 'dados'" method="POST" action="{{ route('perfil.update') }}" class="px-6 py-4 space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label for="perfil_name" class="block text-sm font-medium text-slate-700">Nome</label>
                <input id="perfil_name" name="name" type="text" required maxlength="255"
                    value="{{ $errors->perfilDados->any() ? old('name') : $usuario->name }}"
                    class="mt-1 w-full rounded-md border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                @foreach ($errors->perfilDados->get('name') as $msg)
                    <p class="mt-1 text-xs text-red-600">{{ $msg }}</p>
                @endforeach
            </div>

            <div>
                <label for="perfil_email" class="block text-sm font-medium text-slate-700">E-mail</label>
                <input id="perfil_email" name="email" type="email" required maxlength="255"
                    value="{{ $errors->perfilDados->any() ? old('email') : $usuario->email }}"
                    class="mt-1 w-full rounded-md border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                <p class="mt-1 text-xs text-slate-500">Este é o e-mail usado para entrar no sistema.</p>
                @foreach ($errors->perfilDados->get('email') as $msg)
                    <p class="mt-1 text-xs text-red-600">{{ $msg }}</p>
                @endforeach
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <button type="button" x-on:click="$dispatch('close')"
                    class="px-4 py-2 text-sm rounded-md border border-slate-300 text-slate-600 hover:bg-slate-50">
                    Cancelar
                </button>
                <button type="submit" class="px-4 py-2 text-sm rounded-md bg-blue-600 text-white hover:bg-blue-700">
                    Salvar dados
                </button>
            </div>
        </form>

        {{-- Aba: senha --}}
        <form x-show="aba === 'senha'" x-cloak method="POST" action="{{ route('perfil.update-senha') }}" class="px-6 py-4 space-y-4">
            @csrf
            @method('PUT')

            @foreach ([
                'senha_atual'           => ['Senha atual', 'current-password'],
                'password'              => ['Nova senha', 'new-password'],
                'password_confirmation' => ['Confirmar nova senha', 'new-password'],
            ] as $campo => [$rotulo, $autocomplete])
                <div>
                    <label for="perfil_{{ $campo }}" class="block text-sm font-medium text-slate-700">{{ $rotulo }}</label>
                    <input id="perfil_{{ $campo }}" name="{{ $campo }}" type="password" required
                        autocomplete="{{ $autocomplete }}"
                        class="mt-1 w-full rounded-md border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                    @if ($campo === 'password')
                        <p class="mt-1 text-xs text-slate-500">Mínimo de 8 caracteres.</p>
                    @endif
                    @foreach ($errors->perfilSenha->get($campo) as $msg)
                        <p class="mt-1 text-xs text-red-600">{{ $msg }}</p>
                    @endforeach
                </div>
            @endforeach

            <div class="flex justify-end gap-2 pt-2">
                <button type="button" x-on:click="$dispatch('close')"
                    class="px-4 py-2 text-sm rounded-md border border-slate-300 text-slate-600 hover:bg-slate-50">
                    Cancelar
                </button>
                <button type="submit" class="px-4 py-2 text-sm rounded-md bg-blue-600 text-white hover:bg-blue-700">
                    Alterar senha
                </button>
            </div>
        </form>
    </div>
</x-modal>
