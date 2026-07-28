<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - VPN Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-gray-100 font-sans flex flex-col justify-between min-h-screen">
    <div class="flex-grow flex items-center justify-center p-4">
        <div class="bg-gray-800 p-8 rounded-lg shadow-lg w-full max-w-md border border-gray-700">
            <h1 class="text-2xl font-bold text-cyan-400 mb-2 text-center">🌐 VPN Management PT NAT JAKARTA</h1>
            <p class="text-sm text-gray-400 mb-6 text-center">Silakan masuk untuk mengakses sistem</p>

            @if($errors->any())
                <div class="bg-red-600/30 border border-red-500 text-red-200 p-3 rounded mb-4 text-sm">
                    {{ $errors->first() }}
                </div>
            @endif

            @if(session('success'))
                <div class="bg-green-600/30 border border-green-500 text-green-200 p-3 rounded mb-4 text-sm">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('status'))
                <div class="bg-green-600/30 border border-green-500 text-green-200 p-3 rounded mb-4 text-sm">
                    {{ session('status') }}
                </div>
            @endif

            <form action="{{ route('login.post') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm mb-1 text-gray-300">Username:</label>
                    <input type="text" name="username" value="{{ old('username') }}" required autofocus class="w-full bg-gray-700 border border-gray-600 rounded p-2.5 text-white focus:outline-none focus:border-cyan-400 text-sm">
                </div>
                <div class="mb-2">
                    <label class="block text-sm mb-1 text-gray-300">Password:</label>
                    <input type="password" name="password" required class="w-full bg-gray-700 border border-gray-600 rounded p-2.5 text-white focus:outline-none focus:border-cyan-400 text-sm">
                </div>
                <div class="flex justify-end mb-6">
                    <a href="{{ route('password.request') }}" class="text-xs text-cyan-400 hover:underline">Lupa Password?</a>
                </div>
                <button type="submit" class="w-full bg-cyan-600 hover:bg-cyan-500 text-white font-medium p-2.5 rounded transition text-sm shadow">Masuk</button>
            </form>
        </div>
    </div>

    <!-- COPYRIGHT / FOOTER -->
    <footer class="text-center py-6 text-gray-400 text-xs border-t border-gray-800">
        &copy; 2026 VPN Management System. Developed With Sandi Futura EIO. All rights reserved.
    </footer>
</body>
</html>
