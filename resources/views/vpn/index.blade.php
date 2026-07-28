<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VPN Management System - MikroTik CHR v7</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-gray-100 font-sans p-6 min-h-screen flex flex-col justify-between">
    <div class="max-w-7xl mx-auto w-full">
        
        <!-- HEADER & INFO USER LOGIN -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4 border-b border-gray-800 pb-4">
            <h1 class="text-2xl font-bold text-cyan-400">🌐 VPN Management System (IP Publik: 163.61.244.246)</h1>
            <div class="flex items-center gap-3 bg-gray-800 px-4 py-2 rounded-lg border border-gray-700 text-sm shadow">
                <span>👤 <strong>{{ auth()->user()->name }}</strong> (<span class="text-yellow-400 uppercase font-bold">{{ auth()->user()->role }}</span>)</span>
                <form action="{{ route('logout') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="bg-red-600 hover:bg-red-500 text-white px-3 py-1 rounded text-xs font-medium transition">Logout</button>
                </form>
            </div>
        </div>

        <!-- Notifikasi -->
        @if(session('success'))
            <div class="bg-green-600 text-white p-4 rounded mb-4">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="bg-red-600 text-white p-4 rounded mb-4">{{ session('error') }}</div>
        @endif

        <!-- FORM TAMBAH VPN (HANYA UNTUK ADMIN) -->
        @if(auth()->user()->role === 'admin')
        <div class="bg-gray-800 p-6 rounded-lg shadow-md mb-8 border border-gray-700">
            <h2 class="text-lg font-semibold mb-4 text-emerald-400">➕ Tambah VPN Secret & Auto NAT</h2>
            <form action="{{ route('vpn.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                @csrf
                <div>
                    <label class="block text-sm mb-1 text-gray-300">Username:</label>
                    <input type="text" name="username" required class="w-full bg-gray-700 border border-gray-600 rounded p-2 text-white focus:outline-none focus:border-cyan-400">
                </div>
                <div>
                    <label class="block text-sm mb-1 text-gray-300">Password:</label>
                    <input type="text" name="password" required class="w-full bg-gray-700 border border-gray-600 rounded p-2 text-white focus:outline-none focus:border-cyan-400">
                </div>
                <div>
                    <label class="block text-sm mb-1 text-gray-300">Profile:</label>
                    <input type="text" name="profile" value="Limit-Remote" required class="w-full bg-gray-700 border border-gray-600 rounded p-2 text-white focus:outline-none focus:border-cyan-400">
                </div>
                <div>
                    <label class="block text-sm mb-1 text-gray-300">Service:</label>
                    <select name="service" class="w-full bg-gray-700 border border-gray-600 rounded p-2 text-white focus:outline-none focus:border-cyan-400">
                        <option value="any">any</option>
                    </select>
                </div>
                <div class="md:col-span-4 flex justify-end">
                    <button type="submit" class="bg-cyan-600 hover:bg-cyan-500 text-white px-6 py-2 rounded font-medium transition">Simpan & Generate ke MikroTik</button>
                </div>
            </form>
        </div>
        @endif

        <!-- Kolom Pencarian (Search Bar) -->
        <div class="bg-gray-800 p-4 rounded-lg shadow-md mb-6 flex justify-between items-center border border-gray-700">
            <form action="{{ route('vpn.index') }}" method="GET" class="flex w-full max-w-md gap-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari berdasarkan Username / IP..." class="w-full bg-gray-700 border border-gray-600 rounded p-2 text-white text-sm focus:outline-none focus:border-cyan-400">
                <button type="submit" class="bg-gray-600 hover:bg-gray-500 px-4 py-2 rounded text-sm font-medium">Cari</button>
                @if(request('search'))
                    <a href="{{ route('vpn.index') }}" class="bg-red-600 hover:bg-red-500 px-3 py-2 rounded text-sm flex items-center">Reset</a>
                @endif
            </form>
            <div class="text-sm text-gray-400">Total Secret: {{ $secrets->total() }}</div>
        </div>

        <!-- 1. TABEL MANAJEMEN PPP SECRET -->
        <div class="bg-gray-800 p-6 rounded-lg shadow-md border border-gray-700 mb-8">
            <h2 class="text-lg font-semibold mb-4 text-cyan-300">📋 Daftar PPP Secret (Per 10 Data + Scroll & Page)</h2>
            
            <div class="max-h-[500px] overflow-y-auto overflow-x-auto pr-2 mb-4">
                <table class="w-full text-left border-collapse">
                    <thead class="sticky top-0 bg-gray-800 z-10 border-b border-gray-700">
                        <tr class="text-gray-400 text-sm">
                            <th class="p-3">No</th>
                            <th class="p-3">Username</th>
                            <th class="p-3">Password</th>
                            <th class="p-3">Profile</th>
                            <th class="p-3">Remote IP</th>
                            <th class="p-3">Service</th>
                            <th class="p-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $localVpns = \App\Models\VpnAllocation::all(); @endphp
                        @forelse($secrets as $index => $vpn)
                            @php 
                                $local = $localVpns->where('username', $vpn['name'] ?? '')->first();
                            @endphp
                            <tr class="border-b border-gray-700/50 hover:bg-gray-700/30 text-sm">
                                <td class="p-3 text-gray-400">{{ $secrets->firstItem() + $index }}</td>
                                <td class="p-3 font-medium text-cyan-300">{{ $vpn['name'] ?? '-' }}</td>
                                <td class="p-3 text-gray-300">{{ $vpn['password'] ?? '-' }}</td>
                                <td class="p-3">{{ $vpn['profile'] ?? '-' }}</td>
                                <td class="p-3 text-emerald-400 font-mono">{{ $vpn['remote-address'] ?? '-' }}</td>
                                <td class="p-3">{{ $vpn['service'] ?? '-' }}</td>
                                <td class="p-3 text-center flex justify-center gap-2">
                                    @if(auth()->user()->role === 'admin')
                                        <!-- Tombol Edit Secret -->
                                        <button onclick="openEditModal('{{ $vpn['.id'] ?? '' }}', '{{ $vpn['name'] ?? '' }}', '{{ $vpn['password'] ?? '' }}', '{{ $vpn['profile'] ?? '' }}')" class="bg-yellow-600 hover:bg-yellow-500 text-white px-3 py-1 rounded text-xs font-medium">Edit</button>
                                        
                                        @if($local)
                                            <form action="{{ route('vpn.destroy', $local->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus data VPN ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="bg-red-600 hover:bg-red-500 text-white px-3 py-1 rounded text-xs font-medium">Hapus</button>
                                            </form>
                                        @endif
                                    @else
                                        <span class="text-xs text-gray-500 italic">View Only</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="p-6 text-center text-gray-500">Tidak ada data Secret ditemukan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div>
                {{ $secrets->links() }}
            </div>
        </div>

        <!-- 2. TABEL MANAJEMEN DATA DST-NAT -->
        <div class="bg-gray-800 p-6 rounded-lg shadow-md border border-gray-700 mb-8">
            <h2 class="text-lg font-semibold mb-4 text-amber-300">🔥 Daftar Rule DST-NAT Aktif dari MikroTik</h2>
            
            <div class="max-h-[500px] overflow-y-auto overflow-x-auto pr-2">
                <table class="w-full text-left border-collapse">
                    <thead class="sticky top-0 bg-gray-800 z-10 border-b border-gray-700">
                        <tr class="text-gray-400 text-sm">
                            <th class="p-3">No</th>
                            <th class="p-3">Nama User/Secret</th>
                            <th class="p-3">IP Address (To Address)</th>
                            <th class="p-3">Port WWW</th>
                            <th class="p-3">Port Winbox</th>
                            <th class="p-3">Port API</th>
                            <th class="p-3 text-center">Aksi Bagikan & Salin</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php 
                            $filterNats = collect($natRules)->filter(function($item) {
                                return isset($item['chain']) && $item['chain'] === 'dstnat' && isset($item['to-addresses']);
                            })->groupBy('to-addresses');
                            
                            $no = 1;
                        @endphp

                        @forelse($filterNats as $ipAddress => $rules)
                            @php
                                $wwwRule = $rules->first(fn($r) => ($r['to-ports'] ?? '') == '80' || ($r['dst-port'] ?? '') >= 8102);
                                $winboxRule = $rules->first(fn($r) => ($r['to-ports'] ?? '') == '8291' || ($r['dst-port'] ?? '') >= 7102);
                                $apiRule = $rules->first(fn($r) => ($r['to-ports'] ?? '') == '8728' || ($r['dst-port'] ?? '') >= 7203);
                                
                                $pWww = $wwwRule['dst-port'] ?? '-';
                                $pWinbox = $winboxRule['dst-port'] ?? '-';
                                $pApi = $apiRule['dst-port'] ?? '-';
                                
                                $comment = $wwwRule['comment'] ?? ($winboxRule['comment'] ?? '-');
                                $username = str_replace(['WWW-', 'Winbox-', 'API-'], '', $comment);
                                if($username == '-' || empty($username)) {
                                    $matchedSecret = collect($secrets)->firstWhere('remote-address', $ipAddress);
                                    $username = $matchedSecret['name'] ?? 'Unknown / Manual';
                                }

                                $localRef = $localVpns->where('username', $username)->first();
                            @endphp
                            <tr class="border-b border-gray-700/50 hover:bg-gray-700/30 text-sm">
                                <td class="p-3 text-gray-400">{{ $no++ }}</td>
                                <td class="p-3 font-medium text-cyan-300">{{ $username }}</td>
                                <td class="p-3 text-emerald-400 font-mono">{{ $ipAddress }}</td>
                                <td class="p-3 font-mono">{{ $pWww }}</td>
                                <td class="p-3 font-mono">{{ $pWinbox }}</td>
                                <td class="p-3 font-mono">{{ $pApi }}</td>
                                <td class="p-3 text-center">
                                    <div class="flex justify-center items-center gap-1.5 flex-wrap">
                                        <!-- Tombol WhatsApp -->
                                        <button onclick="shareToWhatsApp('{{ $username }}', '{{ $ipAddress }}', '{{ $pWww }}', '{{ $pWinbox }}', '{{ $pApi }}')" class="bg-green-600 hover:bg-green-500 text-white px-2.5 py-1 rounded text-xs font-medium flex items-center gap-1">
                                            💬 WA
                                        </button>

                                        <!-- Tombol Telegram -->
                                        <button onclick="shareToTelegram('{{ $username }}', '{{ $ipAddress }}', '{{ $pWww }}', '{{ $pWinbox }}', '{{ $pApi }}')" class="bg-sky-600 hover:bg-sky-500 text-white px-2.5 py-1 rounded text-xs font-medium flex items-center gap-1">
                                            ✈️ TG
                                        </button>

                                        <!-- Tombol Copy Text -->
                                        <button onclick="copyDetailText('{{ $username }}', '{{ $ipAddress }}', '{{ $pWww }}', '{{ $pWinbox }}', '{{ $pApi }}')" class="bg-gray-600 hover:bg-gray-500 text-white px-2.5 py-1 rounded text-xs font-medium flex items-center gap-1">
                                            📋 Copy
                                        </button>

                                        @if(auth()->user()->role === 'admin' && $localRef)
                                            <form action="{{ route('vpn.destroy', $localRef->id) }}" method="POST" onsubmit="return confirm('Hapus data ini?');" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="bg-red-600 hover:bg-red-500 text-white px-2.5 py-1 rounded text-xs font-medium">Hapus</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="p-6 text-center text-gray-500">Belum ada rule DST-NAT aktif.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 3. TAB MENU: SCRIPT GENERATOR CLIENT -->
        <div class="bg-gray-800 p-6 rounded-lg shadow-md border border-gray-700 mb-8">
            <h2 class="text-lg font-semibold mb-4 text-yellow-400">📜 Tab Menu: Client Script Generator</h2>
            <p class="text-sm text-gray-400 mb-4">Pilih user terdaftar di bawah untuk menghasilkan script konfigurasi instan yang siap dipasang di router client.</p>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm mb-1 text-gray-300">Pilih User Secret:</label>
                    <select id="selectUser" class="w-full bg-gray-700 border border-gray-600 rounded p-2 text-white focus:outline-none focus:border-cyan-400" onchange="generateScript()">
                        <option value="">-- Pilih Username --</option>
                        @foreach($secrets as $vpn)
                            <option value="{{ $vpn['name'] ?? '' }}" data-pass="{{ $vpn['password'] ?? '' }}">
                                {{ $vpn['name'] ?? '' }} (IP: {{ $vpn['remote-address'] ?? '' }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm mb-1 text-gray-300">Pilih Tipe VPN Client:</label>
                    <select id="selectType" class="w-full bg-gray-700 border border-gray-600 rounded p-2 text-white focus:outline-none focus:border-cyan-400" onchange="generateScript()">
                        <option value="l2tp">L2TP Client</option>
                        <option value="ovpn">OpenVPN (OVPN) Client</option>
                        <option value="sstp">SSTP Client</option>
                    </select>
                </div>
            </div>

            <div class="relative">
                <div class="flex justify-between items-center mb-1">
                    <label class="block text-sm text-gray-300">Generated CLI Script (Copy & Paste ke Terminal Client):</label>
                    <button type="button" onclick="copyGeneratedScript()" class="bg-cyan-600 hover:bg-cyan-500 text-white px-3 py-1 rounded text-xs font-medium flex items-center gap-1 transition">
                        📋 Copy Script
                    </button>
                </div>
                <textarea id="outputScript" readonly rows="5" class="w-full bg-gray-900 border border-gray-700 rounded p-3 font-mono text-emerald-400 text-sm focus:outline-none">Pilih user dan tipe VPN di atas untuk menghasilkan script...</textarea>
            </div>
        </div>

    </div>

    <!-- COPYRIGHT / FOOTER -->
    <footer class="text-center py-6 text-gray-400 text-xs border-t border-gray-800 mt-auto">
        &copy; 2026 VPN Management System. Developed With Sandi Futura Eng Ing Ong. All rights reserved.
    </footer>

    <!-- MODAL EDIT SECRET (HANYA UNTUK ADMIN) -->
    @if(auth()->user()->role === 'admin')
    <div id="editModal" class="hidden fixed inset-0 bg-black/70 flex items-center justify-center p-4">
        <div class="bg-gray-800 p-6 rounded-lg w-full max-w-md border border-gray-700">
            <h3 class="text-lg font-semibold mb-4 text-yellow-400">✏️ Edit PPP Secret</h3>
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="mikrotik_id" id="editMikrotikId">
                <div class="mb-3">
                    <label class="block text-sm mb-1 text-gray-300">Username:</label>
                    <input type="text" name="username" id="editUsername" readonly class="w-full bg-gray-900 border border-gray-700 rounded p-2 text-gray-400">
                </div>
                <div class="mb-3">
                    <label class="block text-sm mb-1 text-gray-300">Password Baru:</label>
                    <input type="text" name="password" id="editPassword" required class="w-full bg-gray-700 border border-gray-600 rounded p-2 text-white">
                </div>
                <div class="mb-4">
                    <label class="block text-sm mb-1 text-gray-300">Profile:</label>
                    <input type="text" name="profile" id="editProfile" required class="w-full bg-gray-700 border border-gray-600 rounded p-2 text-white">
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" onclick="closeEditModal()" class="bg-gray-600 hover:bg-gray-500 px-4 py-2 rounded text-sm">Batal</button>
                    <button type="submit" class="bg-cyan-600 hover:bg-cyan-500 px-4 py-2 rounded text-sm">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- JavaScript Handler -->
    <script>
        @if(auth()->user()->role === 'admin')
        function openEditModal(id, username, password, profile) {
            document.getElementById('editModal').classList.remove('hidden');
            document.getElementById('editMikrotikId').value = id;
            document.getElementById('editUsername').value = username;
            document.getElementById('editPassword').value = password;
            document.getElementById('editProfile').value = profile;
            document.getElementById('editForm').action = "/vpn/" + id;
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.add('hidden');
        }
        @endif

        function generateScript() {
            const selectUser = document.getElementById('selectUser');
            const selectType = document.getElementById('selectType').value;
            const outputBox = document.getElementById('outputScript');

            const selectedOption = selectUser.options[selectUser.selectedIndex];
            const username = selectedOption.value;
            const password = selectedOption.getAttribute('data-pass');

            if (!username) {
                outputBox.value = "Silakan pilih username terlebih dahulu.";
                return;
            }

            let script = "";
            const serverIp = "163.61.244.246";

            if (selectType === 'l2tp') {
                script = `/interface l2tp-client\nadd connect-to=${serverIp} disabled=no name=l2tp-${username} user="${username}" password="${password}" use-peer-dns=no`;
            } else if (selectType === 'sstp') {
                script = `/interface sstp-client\nadd connect-to=${serverIp} disabled=no name=sstp-${username} user="${username}" password="${password}" verify-server-identity=no`;
            } else if (selectType === 'ovpn') {
                script = `/interface ovpn-client\nadd connect-to=${serverIp} disabled=no name=ovpn-${username} user="${username}" password="${password}" port=1194 mode=ip`;
            }

            outputBox.value = script;
        }

        function copyGeneratedScript() {
            const outputBox = document.getElementById('outputScript');
            if (!outputBox.value || outputBox.value.startsWith("Pilih user")) {
                alert("Belum ada script yang digenerate!");
                return;
            }

            const textArea = document.createElement("textarea");
            textArea.value = outputBox.value;
            textArea.style.position = "fixed";
            textArea.style.top = "0";
            textArea.style.left = "0";
            textArea.style.opacity = "0";
            
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();

            try {
                document.execCommand('copy');
                alert('CLI Script berhasil disalin ke clipboard!');
            } catch (err) {
                alert('Gagal menyalin script.');
            }
            document.body.removeChild(textArea);
        }

        function generateMessageText(username, ip, portWww, portWinbox, portApi) {
            const publicIp = "163.61.244.246";
            return `Halo, berikut adalah informasi akses VPN & Port Forwarding Anda:\n\n` +
                   `👤 Username: ${username}\n` +
                   `🌐 IP Publik Server: ${publicIp}\n` +
                   `📌 IP Remote Client: ${ip}\n\n` +
                   `🔌 Mapping Port:\n` +
                   `- Port WWW (Web): ${publicIp}:${portWww}\n` +
                   `- Port Winbox: ${publicIp}:${portWinbox}\n` +
                   `- Port API: ${publicIp}:${portApi}\n\n` +
                   `Silakan disimpan dengan baik. Terima kasih!`;
        }

        function shareToWhatsApp(username, ip, portWww, portWinbox, portApi) {
            const message = generateMessageText(username, ip, portWww, portWinbox, portApi);
            window.open(`https://api.whatsapp.com/send?text=${encodeURIComponent(message)}`, '_blank');
        }

        function shareToTelegram(username, ip, portWww, portWinbox, portApi) {
            const message = generateMessageText(username, ip, portWww, portWinbox, portApi);
            window.open(`https://t.me/share/url?url=&text=${encodeURIComponent(message)}`, '_blank');
        }

        function copyDetailText(username, ip, portWww, portWinbox, portApi) {
            const message = generateMessageText(username, ip, portWww, portWinbox, portApi);
            const textArea = document.createElement("textarea");
            textArea.value = message;
            textArea.style.position = "fixed";
            textArea.style.top = "0";
            textArea.style.left = "0";
            textArea.style.opacity = "0";
            
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();

            try {
                document.execCommand('copy');
                alert('Detail port berhasil disalin ke clipboard!');
            } catch (err) {
                alert('Browser tidak mendukung penyalinan otomatis.');
            }
            document.body.removeChild(textArea);
        }
    </script>
</body>
</html>
