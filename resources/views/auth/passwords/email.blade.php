<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - VPN Management System PT NAT</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-gray-100 font-sans flex flex-col justify-between min-h-screen">
    <div class="flex-grow flex items-center justify-center p-4">
        <div class="bg-gray-800 p-8 rounded-lg shadow-lg w-full max-w-md border border-gray-700">
            <h1 class="text-2xl font-bold text-cyan-400 mb-2 text-center">🔒 Lupa Password</h1>
            <p class="text-sm text-gray-400 mb-6 text-center">Masukkan email Anda untuk menerima instruksi reset password.</p>

            @if(session('status'))
                <div class="bg-green-600/30 border border-green-500 text-green-200 p-3 rounded mb-4 text-sm">
                    {{ session('status') }}
                </div>
            @endif

            <form action="{{ route('password.email') }}" method="POST">
                @csrf
                <div class="mb-6">
                    <label class="block text-sm mb-1 text-gray-300">Email:</label>
                    <input type="email" name="email" required autofocus class="w-full bg-gray-700 border border-gray-600 rounded p-2.5 text-white focus:outline-none focus:border-cyan-400 text-sm">
                </div>
                <button type="submit" class="w-full bg-cyan-600 hover:bg-cyan-500 text-white font-medium p-2.5 rounded transition text-sm shadow mb-4">Kirim Link Reset</button>
                <div class="text-center">
                    <a href="{{ route('login') }}" class="text-xs text-gray-400 hover:text-cyan-400">← Kembali ke Halaman Login</a>
                </div>
            </form>
        </div>
    </div>

    <!-- COPYRIGHT / FOOTER -->
    <footer class="text-center py-6 text-gray-400 text-xs border-t border-gray-800">
        &copy; 2026 VPN Management System. Developed With Sandi Futura EIO. All rights reserved.
    </footer>
</body>
</html>
