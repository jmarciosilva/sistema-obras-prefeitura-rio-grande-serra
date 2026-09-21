<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <title>@yield('title', 'Sistema de Obras') · Prefeitura de Rio Grande da Serra</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-100" x-data="{
    sidebarOpen: true,
    openAdmin: {{ request()->routeIs('admin.*') ? 'true' : 'false' }}
}">

    <div class="flex min-h-screen">

        {{-- ================================================================
    | SIDEBAR
    ================================================================ --}}
        <aside class="bg-gradient-to-b from-slate-900 to-slate-800 text-white flex flex-col transition-all duration-300"
            :class="sidebarOpen ? 'w-64' : 'w-20'">

            {{-- Logo --}}
            <div class="h-16 flex items-center px-4 font-bold border-b border-slate-700">
                <span class="bg-blue-600 w-9 h-9 rounded-lg flex items-center justify-center mr-2 shrink-0 text-sm">
                    RGS
                </span>
                <span x-show="sidebarOpen" x-transition class="leading-tight text-sm">
                    Sistema de Obras
                </span>
            </div>

            {{-- Menu --}}
            <nav class="flex-1 px-3 py-6 space-y-1 text-sm overflow-y-auto">

                {{-- Dashboard --}}
                <a href="{{ route('dashboard') }}"
                    class="flex items-center gap-3 px-3 py-2 rounded-md
                      {{ request()->routeIs('dashboard') ? 'bg-blue-600' : 'hover:bg-slate-700' }}">
                    📊 <span x-show="sidebarOpen">Visão Geral</span>
                </a>

                {{-- Obras --}}
                <a href="{{ route('obras.index') }}"
                    class="flex items-center gap-3 px-3 py-2 rounded-md
                      {{ request()->routeIs('obras.*') ? 'bg-blue-600' : 'hover:bg-slate-700' }}">
                    🏗️ <span x-show="sidebarOpen">Obras</span>
                </a>

                {{-- Convênios --}}
                <a href="{{ route('convenios.index') }}"
                    class="flex items-center gap-3 px-3 py-2 rounded-md
                      {{ request()->routeIs('convenios.*') ? 'bg-blue-600' : 'hover:bg-slate-700' }}">
                    📄 <span x-show="sidebarOpen">Convênios</span>
                </a>

                {{-- Contratos --}}
                <a href="{{ route('contratos.index') }}"
                    class="flex items-center gap-3 px-3 py-2 rounded-md
                      {{ request()->routeIs('contratos.*') ? 'bg-blue-600' : 'hover:bg-slate-700' }}">
                    📋 <span x-show="sidebarOpen">Contratos</span>
                </a>

                {{-- Processos Administrativos --}}
                <a href="{{ route('processos.index') }}"
                    class="flex items-center gap-3 px-3 py-2 rounded-md
                      {{ request()->routeIs('processos.*') ? 'bg-blue-600' : 'hover:bg-slate-700' }}">
                    🗂️ <span x-show="sidebarOpen">Processos</span>
                </a>

                @if (in_array(auth()->user()->perfil, ['admin', 'secretario', 'tecnico']))
                    <a href="{{ route('relatorios.index') }}"
                        class="flex items-center gap-3 px-3 py-2 rounded-md
                    {{ request()->routeIs('relatorios.*') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-700 hover:text-white' }}">
                        📊 <span x-show="sidebarOpen">Relatórios</span>
                    </a>
                @endif

                <a href="{{ route('manual') }}"
                    class="flex items-center gap-3 px-3 py-2 rounded-md
                    {{ request()->routeIs('manual') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-700 hover:text-white' }}">
                    📘 <span x-show="sidebarOpen">Manual</span>
                </a>

                <div class="border-t border-slate-700/60 my-3"></div>

                {{-- Admin (submenu recolhível) --}}
                @auth
                    @if (auth()->user()->perfil === 'admin')
                        <div>
                            <button @click="openAdmin = !openAdmin"
                                class="flex items-center justify-between w-full px-3 py-2 rounded-md hover:bg-slate-700 transition">
                                <span class="flex items-center gap-3">
                                    ⚙️ <span x-show="sidebarOpen">Administração</span>
                                </span>
                                <span x-show="sidebarOpen" x-text="openAdmin ? '▾' : '▸'"
                                    class="text-xs text-slate-400"></span>
                            </button>

                            <div x-show="openAdmin && sidebarOpen" x-transition:enter="transition ease-out duration-150"
                                x-transition:enter-start="opacity-0 -translate-y-1"
                                x-transition:enter-end="opacity-100 translate-y-0" x-cloak
                                class="ml-5 mt-1 space-y-0.5 border-l border-slate-700 pl-3">

                                <a href="{{ route('admin.usuarios.index') }}"
                                    class="flex items-center gap-2 px-3 py-2 rounded-md text-sm
                              {{ request()->routeIs('admin.usuarios.*') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-700 hover:text-white' }}">
                                    👤 Usuários
                                </a>

                                <a href="{{ route('admin.empresas.index') }}"
                                    class="flex items-center gap-2 px-3 py-2 rounded-md text-sm
                              {{ request()->routeIs('admin.empresas.*') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-700 hover:text-white' }}">
                                    🏢 Empresas
                                </a>

                                <a href="{{ route('admin.status-obras.index') }}"
                                    class="flex items-center gap-2 px-3 py-2 rounded-md text-sm
                              {{ request()->routeIs('admin.status-obras.*') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-700 hover:text-white' }}">
                                    🏷️ Status de Obras
                                </a>

                                <a href="{{ route('admin.categorias-convenio.index') }}"
                                    class="flex items-center gap-2 px-3 py-2 rounded-md text-sm
                              {{ request()->routeIs('admin.categorias-convenio.*') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-700 hover:text-white' }}">
                                    📁 Categorias Convênios
                                </a>

                                <a href="{{ route('admin.orgaos-financiadores.index') }}"
                                    class="flex items-center gap-2 px-3 py-2 rounded-md text-sm
                              {{ request()->routeIs('admin.orgaos-financiadores.*') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-700 hover:text-white' }}">
                                    🏦 Órgãos Financiadores
                                </a>

                                <a href="{{ route('admin.demandas-propostas.index') }}"
                                    class="flex items-center gap-2 px-3 py-2 rounded-md text-sm
                              {{ request()->routeIs('admin.demandas-propostas.*') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-700 hover:text-white' }}">
                                    📝 Demandas
                                </a>

                                <a href="{{ route('admin.responsaveis-tecnicos.index') }}"
                                    class="flex items-center gap-2 px-3 py-2 rounded-md text-sm
                              {{ request()->routeIs('admin.responsaveis-tecnicos.*') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-700 hover:text-white' }}">
                                    👷 Responsáveis Técnicos
                                </a>

                            </div>
                        </div>
                    @endif
                @endauth

            </nav>

        </aside>

        {{-- ================================================================
    | CONTEÚDO PRINCIPAL
    ================================================================ --}}
        <div class="flex-1 flex flex-col min-w-0">

            {{-- Header --}}
            <header class="bg-white border-b px-6 py-3">
                <div class="flex items-center justify-between">

                    {{-- Esquerda: toggle + título --}}
                    <div class="flex items-center gap-3">
                        <button @click="sidebarOpen = !sidebarOpen" class="p-2 rounded hover:bg-slate-100">
                            ☰
                        </button>
                        <div class="leading-tight">
                            <h1 class="text-lg font-semibold">@yield('title')</h1>
                            <p class="text-xs text-slate-500">@yield('subtitle')</p>
                        </div>
                    </div>

                    {{-- Direita: perfil + logout --}}
                    <div class="flex items-center gap-4">

                        @auth
                            {{-- Badge de perfil --}}
                            <span
                                class="text-xs px-2 py-1 rounded
                        {{ auth()->user()->perfil === 'admin'
                            ? 'bg-red-100 text-red-700'
                            : (auth()->user()->perfil === 'tecnico'
                                ? 'bg-yellow-100 text-yellow-700'
                                : 'bg-slate-100 text-slate-600') }}">
                                {{ ucfirst(auth()->user()->perfil) }}
                            </span>

                            {{-- Nome do usuário — abre a modal "Meu perfil" --}}
                            <button type="button" x-on:click="$dispatch('open-modal', 'meu-perfil')"
                                title="Alterar nome, e-mail ou senha"
                                class="inline-flex items-center gap-1.5 px-2 py-1 rounded-lg text-xs font-medium
                                       text-slate-700 hover:bg-slate-100 transition">
                                👤 {{ auth()->user()->name }}
                                <span class="text-slate-400">✎</span>
                            </button>

                            {{-- Logout --}}
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button
                                    class="inline-flex items-center gap-1 px-3 py-1.5
                                       text-xs font-medium rounded-lg
                                       text-red-700 bg-red-50 border border-red-200
                                       hover:bg-red-100 transition">
                                    🚪 Sair
                                </button>
                            </form>
                        @endauth

                    </div>
                </div>
            </header>

            {{-- Flash messages --}}
            <div class="px-6 pt-4">
                @if (session('sucesso'))
                    <div x-data="{ show: true }" x-show="show" x-transition
                        class="mb-4 flex items-center justify-between bg-green-100 border border-green-300 text-green-700 px-4 py-3 rounded-lg text-sm">
                        <span>✔ {{ session('sucesso') }}</span>
                        <button @click="show = false" class="text-green-500 hover:text-green-700 ml-4">✕</button>
                    </div>
                @endif

                @if (session('erro'))
                    <div x-data="{ show: true }" x-show="show" x-transition
                        class="mb-4 flex items-center justify-between bg-red-100 border border-red-300 text-red-700 px-4 py-3 rounded-lg text-sm">
                        <span>❌ {{ session('erro') }}</span>
                        <button @click="show = false" class="text-red-500 hover:text-red-700 ml-4">✕</button>
                    </div>
                @endif

                @if (session('aviso'))
                    <div x-data="{ show: true }" x-show="show" x-transition
                        class="mb-4 flex items-center justify-between bg-yellow-100 border border-yellow-300 text-yellow-700 px-4 py-3 rounded-lg text-sm">
                        <span>⚠️ {{ session('aviso') }}</span>
                        <button @click="show = false" class="text-yellow-500 hover:text-yellow-700 ml-4">✕</button>
                    </div>
                @endif
            </div>

            {{-- Conteúdo da página --}}
            <main class="p-6 flex-1">
                @yield('content')
            </main>

            {{-- Footer --}}
            <footer class="text-center text-xs text-slate-400 py-4 border-t border-slate-200">
                Prefeitura Municipal de Rio Grande da Serra &mdash; Sistema de Acompanhamento de Obras &copy;
                {{ date('Y') }}
            </footer>

        </div>
    </div>

    @auth
        @include('perfil._modal')
    @endauth

    @stack('scripts')
</body>

</html>
