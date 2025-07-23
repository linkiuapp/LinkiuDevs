<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SuperLinkiu - Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center">
        <div class="max-w-md w-full space-y-8 p-8 bg-white-50 rounded-lg shadow">
            <div>
                <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                    SuperLinkiu
                </h2>
                <p class="mt-2 text-center text-sm text-gray-600">
                    Panel de Administración
                </p>
            </div>
            <form class="mt-8 space-y-6" action="{{ route('superlinkiu.login.submit') }}" method="POST">
                @csrf
                <div class="rounded-md shadow-sm -space-y-px">
                    <div>
                        <label for="email" class="sr-only">Email</label>
                        <input id="email" name="email" type="email" required 
                            class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm" 
                            placeholder="Email">
                    </div>
                    <div>
                        <label for="password" class="sr-only">Contraseña</label>
                        <input id="password" name="password" type="password" required 
                            class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm" 
                            placeholder="Contraseña">
                    </div>
                </div>

                @if ($errors->any())
                    <div class="rounded-md bg-red-50 p-4">
                        <div class="flex">
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-red-800">
                                    Hubo un error con tu inicio de sesión
                                </h3>
                                <div class="mt-2 text-sm text-red-700">
                                    <ul class="list-disc pl-5 space-y-1">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <div>
                    <button type="submit" 
                        class="btn-primary group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md">
                        Iniciar Sesión
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html> 