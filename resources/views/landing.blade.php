<!DOCTYPE html>
<html lang="es">

<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>iRacing Team Manager</title>

@vite(['resources/css/app.css'])

</head>

<body class="bg-gray-900 text-gray-200 min-h-screen flex flex-col">

<header class="h-16 bg-gray-800 border-b border-gray-700 flex items-center justify-between px-6">

<img src="{{ asset('storage/images/irteammanager.png') }}" class="h-10">

<div class="space-x-4">

<a href="{{ route('login') }}"
class="px-4 py-2 text-gray-300 hover:text-white">
Login
</a>

<a href="{{ route('register') }}"
class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 rounded-lg text-white">
Register
</a>

</div>

</header>

<main class="flex flex-1 items-center justify-center text-center">

<div class="max-w-2xl">

<h1 class="text-4xl font-bold mb-6">
iRacing Team Manager
</h1>

<p class="text-gray-400 mb-8">
Gestión avanzada de equipos, análisis de stints y estrategia para iRacing.
</p>

<div class="space-x-4">

<a href="{{ route('register') }}"
class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 rounded-lg text-white">

Crear cuenta

</a>

<a href="{{ route('login') }}"
class="px-6 py-3 border border-gray-600 hover:border-gray-400 rounded-lg">

Entrar

</a>

</div>

</div>

</main>

</body>
</html>
