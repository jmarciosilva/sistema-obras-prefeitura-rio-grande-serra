<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Login - Sistema de Obras</title>

    <!-- Importação dos assets (Tailwind + JS via Vite) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="h-screen w-screen">

    <!-- Container principal ocupando toda a tela -->
    <div class="flex h-screen">

        <!-- ============================= -->
        <!-- LADO ESQUERDO (IMAGEM)       -->
        <!-- ============================= -->
      <div class="hidden md:flex w-1/2 bg-contain bg-no-repeat bg-center bg-blue-900 relative"
            style="background-image: url('/images/Capa-site.png');">

            <!-- Overlay azul leve (permite ver o brasão ao fundo) -->
            <div class="absolute inset-0 bg-blue-100/20"></div>

        </div>

        <!-- ============================= -->
        <!-- LADO DIREITO (LOGIN)          -->
        <!-- ============================= -->
        <div class="w-full md:w-1/2 flex flex-col items-center justify-center bg-gray-100 relative">

            <!-- TÍTULO INSTITUCIONAL (fora do card) -->
            <div class="absolute top-16 text-center">
                <h1 class="text-3xl font-bold text-blue-900">
                    Sistema de Obras Públicas
                </h1>
                <p class="text-gray-600">
                    Prefeitura de Rio Grande da Serra
                </p>
            </div>

            <!-- CARD DE LOGIN -->
            <div class="w-full max-w-md bg-white p-8 rounded-2xl shadow-xl mt-24">

                <!-- Título do formulário -->
                <h2 class="text-xl font-semibold text-gray-800 mb-6 text-center">
                    Acessar o Sistema
                </h2>

                <!-- Mensagem de status (ex: senha redefinida) -->
                <x-auth-session-status class="mb-4" :status="session('status')" />

                <!-- Formulário de login -->
                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <!-- ============================= -->
                    <!-- CAMPO: EMAIL                  -->
                    <!-- ============================= -->
                    <div>
                        <x-input-label for="email" value="Email" />

                        <x-text-input id="email" class="block mt-1 w-full rounded-lg" type="email" name="email"
                            :value="old('email')" required autofocus />

                        <!-- Exibição de erros -->
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <!-- ============================= -->
                    <!-- CAMPO: SENHA                  -->
                    <!-- ============================= -->
                    <div class="mt-4">
                        <x-input-label for="password" value="Senha" />

                        <x-text-input id="password" class="block mt-1 w-full rounded-lg" type="password"
                            name="password" required />

                        <!-- Exibição de erros -->
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <!-- ============================= -->
                    <!-- OPÇÕES (LEMBRAR + ESQUECI)    -->
                    <!-- ============================= -->
                    <div class="flex items-center justify-between mt-4 text-sm">

                        <!-- Checkbox lembrar -->
                        <label class="flex items-center">
                            <input type="checkbox" name="remember" class="rounded border-gray-300">
                            <span class="ml-2 text-gray-600">
                                Lembrar-me
                            </span>
                        </label>

                        <!-- Link recuperar senha -->
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="text-blue-600 hover:underline">
                                Esqueceu a senha?
                            </a>
                        @endif
                    </div>

                    <!-- ============================= -->
                    <!-- BOTÃO DE LOGIN                -->
                    <!-- ============================= -->
                    <button
                        class="w-full mt-6 bg-blue-700 hover:bg-blue-800 text-white font-semibold py-2.5 rounded-lg transition">
                        Entrar no Sistema
                    </button>

                </form>
            </div>

        </div>

    </div>

</body>

</html>
