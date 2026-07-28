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
            <h1 class="text-2xl font-bold text-cyan-400">🌐 VPN Management System (IP Publik : )</h1>
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

        <!-- FORM TAMBAH VPN / GENERATE SCRIPT (HANYA UNTUK ADMIN) -->
@if(in_array(strtolower(auth()->user()->role), ['admin', 'operator']))
        <div class="bg-gray-800 p-6 rounded-lg shadow-md mb-8 border border-gray-700">
            <h2 class="text-lg font-semibold mb-4 text-emerald-400">➕ Tambah VPN Secret & Auto NAT (Local IP: 10.163.61.1)</h2>
            <form action="{{ route('vpn.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                @csrf
                <div>
                    <label class="block text-sm mb-1 text-gray-300">Username:</label>
                    <input type="text" name="username" required class="w-full bg-gray-700 border border-gray-600 rounded p-2 text-white focus:outline-none focus:border-cyan-400" placeholder="contoh: user-baru">
                </div>
                <div>
                    <label class="block text-sm mb-1 text-gray-300">Password:</label>
                    <input type="text" name="password" required class="w-full bg-gray-700 border border-gray-600 rounded p-2 text-white focus:outline-none focus:border-cyan-400" placeholder="password">
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

        <!-- GENERATOR SCRIPT KLIEN (OVPN, SSTP, L2TP) -->
        <div class="bg-gray-800 p-6 rounded-lg shadow-md mb-8 border border-gray-700">
            <h2 class="text-lg font-semibold mb-4 text-indigo-400">🛠️ Generator Script Client (OVPN, SSTP, L2TP)</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <div>
                    <label class="block text-sm mb-1 text-gray-300">Pilih Protokol:</label>
                    <select id="clientProtocol" class="w-full bg-gray-700 border border-gray-600 rounded p-2 text-white focus:outline-none focus:border-indigo-400">
                        <option value="ovpn">OpenVPN (OVPN Client)</option>
                        <option value="sstp">SSTP Client</option>
                        <option value="l2tp">L2TP Client</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm mb-1 text-gray-300">Pilih Username dari Secret:</label>
                    <select id="clientUserSelect" onchange="fillClientPassword(this)" class="w-full bg-gray-700 border border-gray-600 rounded p-2 text-white focus:outline-none focus:border-indigo-400">
                        <option value="" data-password="">-- Pilih Username --</option>
                        @foreach($secrets as $sec)
                            <option value="{{ $sec['name'] ?? '' }}" data-password="{{ $sec['password'] ?? '' }}">
                                {{ $sec['name'] ?? '' }} ({{ $sec['remote-address'] ?? '' }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm mb-1 text-gray-300">Password Client (Auto):</label>
                    <input type="text" id="clientPass" readonly class="w-full bg-gray-900 border border-gray-600 rounded p-2 text-emerald-400 font-mono focus:outline-none" placeholder="Otomatis terisi...">
                </div>
            </div>
            <div class="mb-4">
                <button type="button" onclick="generateClientScript()" class="bg-indigo-600 hover:bg-indigo-500 text-white px-4 py-2 rounded text-sm font-medium transition">Buat Script Terminal</button>
            </div>
            <div>
                <label class="block text-sm mb-1 text-gray-300">Hasil Script Terminal MikroTik:</label>
                <textarea id="resultClientScript" rows="4" readonly class="w-full bg-gray-900 border border-gray-700 rounded p-3 text-emerald-400 font-mono text-sm focus:outline-none" placeholder="Script akan muncul otomatis di sini..."></textarea>
                <button type="button" onclick="copyClientScript()" class="mt-2 bg-gray-700 hover:bg-gray-600 text-white px-3 py-1.5 rounded text-xs font-medium transition">📋 Salin Script</button>
            </div>
        </div>
        @endif

        <!-- 1. TABEL MANAJEMEN DATA DST-NAT (DI ATAS) -->
        <div class="mb-4 flex flex-col md:flex-row items-start md:items-center justify-between gap-3">
            <input type="text" id="searchNatInput" onkeyup="filterNatTable()" placeholder="Cari berdasarkan Nama User atau IP Address..." class="w-full md:w-1/3 bg-gray-700 border border-gray-600 rounded p-2 text-white text-sm focus:outline-none focus:border-cyan-400">
            
            <div class="flex items-center gap-4 w-full md:w-auto justify-between">
                <div class="text-sm text-gray-400">Total Rule NAT: <span id="totalNatCount">0</span></div>
                <a href="{{ request()->fullUrl() }}" class="bg-amber-600 hover:bg-amber-500 text-white px-4 py-2 rounded-lg text-sm font-medium transition flex items-center gap-1.5 shadow">🔄 Refresh API MikroTik</a>
            </div>
        </div>

        <div class="bg-gray-800 p-6 rounded-lg shadow-md border border-gray-700 mb-8">
            <h2 class="text-lg font-semibold mb-4 text-amber-300">🔥 Daftar Rule DST-NAT Aktif dari MikroTik</h2>
            <div class="max-h-[500px] overflow-y-auto overflow-x-auto pr-2">
                <table id="natTable" class="w-full text-left border-collapse">
                    <thead class="sticky top-0 bg-gray-800 z-10 border-b border-gray-700">
                        <tr class="text-gray-400 text-sm">
                            <th class="p-3">No</th>
                            <th class="p-3">Nama User/Secret</th>
                            <th class="p-3">IP Address (To Address)</th>
                            <th class="p-3">Port WWW</th>
                            <th class="p-3">Port Winbox</th>
                            <th class="p-3">Port API</th>
                            <th class="p-3 text-center">Aksi Bagikan & Kelola</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php 
                            $filterNats = collect($natRules ?? [])->filter(function($item) {
                                return isset($item['chain']) && $item['chain'] === 'dstnat' && isset($item['to-addresses']); 
                            })->groupBy('to-addresses'); 
                            $no = 1; 
                        @endphp 
                        @forelse($filterNats as $ipAddress => $rules) 
                        @php 
                            $pWww = '-';
                            $pWinbox = '-';
                            $pApi = '-';
                            $comment = '';

                            foreach($rules as $r) {
                                $comment = $r['comment'] ?? $comment;
                                $dstPort = $r['dst-port'] ?? '';
                                $toPort = $r['to-ports'] ?? '';

                                if ($toPort == '80' || $dstPort >= 8102) {
                                    $pWww = $dstPort;
                                }
                                if ($toPort == '8291' || ($dstPort >= 7102 && $dstPort < 7200)) {
                                    $pWinbox = $dstPort;
                                }
                                if ($toPort == '8728' || $dstPort >= 7203) {
                                    $pApi = $dstPort;
                                }
                                if ($pWww == '-' && !empty($dstPort)) $pWww = $dstPort;
                                elseif ($pWinbox == '-' && !empty($dstPort)) $pWinbox = $dstPort;
                            }

                            $username = str_replace([' - WWW', ' - Winbox', ' - API', 'WWW-', 'Winbox-', 'API-', ' ( WINBOX )', ' ( OLT )', ' ( GRAPHS )', ' ( Winbox )'], '', $comment);
                            
                            if(empty(trim($username)) || $username == '-') {
                                $matchedSecret = collect($secrets)->firstWhere('remote-address', $ipAddress); 
                                $username = $matchedSecret['name'] ?? 'Unknown / Manual'; 
                            } 
                        @endphp 
                        <tr class="border-b border-gray-700/50 hover:bg-gray-700/30 text-sm"> 
                            <td class="p-3 text-gray-400">{{ $no++ }}</td> 
                            <td class="p-3 font-medium text-cyan-300">{{ trim($username) }}</td> 
                            <td class="p-3 text-emerald-400 font-mono">{{ $ipAddress }}</td> 
                            <td class="p-3 font-mono">{{ $pWww }}</td> 
                            <td class="p-3 font-mono">{{ $pWinbox }}</td> 
                            <td class="p-3 font-mono">{{ $pApi }}</td> 
                            <td class="p-3 text-center"> 
                                <div class="flex justify-center items-center gap-1.5 flex-wrap"> 
                                    <button onclick="shareToWhatsApp('{{ trim($username) }}', '{{ $ipAddress }}', '{{ $pWww }}', '{{ $pWinbox }}', '{{ $pApi }}')" class="bg-green-600 hover:bg-green-500 text-white px-2.5 py-1 rounded text-xs font-medium flex items-center gap-1">💬 WA</button> 
                                    <button onclick="shareToTelegram('{{ trim($username) }}', '{{ $ipAddress }}', '{{ $pWww }}', '{{ $pWinbox }}', '{{ $pApi }}')" class="bg-sky-600 hover:bg-sky-500 text-white px-2.5 py-1 rounded text-xs font-medium flex items-center gap-1">✈️ TG</button> 
                                    <button onclick="copyDetailText('{{ trim($username) }}', '{{ $ipAddress }}', '{{ $pWww }}', '{{ $pWinbox }}', '{{ $pApi }}')" class="bg-gray-600 hover:bg-gray-500 text-white px-2.5 py-1 rounded text-xs font-medium flex items-center gap-1">📋 Copy</button> 
                                    
                                    @if(strtolower(auth()->user()->role) === 'admin')
                                    <form action="{{ route('vpn.destroy', 0) }}" method="POST" onsubmit="return confirm('Hapus seluruh rule NAT untuk user {{ trim($username) }} ini dari MikroTik?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="direct_name" value="{{ trim($username) }}">
                                        <button type="submit" class="bg-red-600 hover:bg-red-500 text-white px-2.5 py-1 rounded text-xs font-medium">Hapus</button>
                                    </form>
                                    @endif
                                </div> 
                            </td> 
                        </tr> 
                        @empty 
                        <tr> 
                            <td colspan="7" class="p-6 text-center text-gray-500">Tidak ada rule DST-NAT aktif ditemukan.</td> 
                        </tr> 
                        @endforelse 
                    </tbody> 
                </table> 
            </div> 
        </div>

        <!-- 2. TABEL MANAJEMEN PPP SECRET (DI BAWAH) -->
        <div class="mb-4 flex items-center justify-between">
            <input type="text" id="searchPppInput" onkeyup="filterPppTable()" placeholder="Cari berdasarkan Username atau IP Address..." class="w-full md:w-1/3 bg-gray-700 border border-gray-600 rounded p-2 text-white text-sm focus:outline-none focus:border-cyan-400">
            <div class="text-sm text-gray-400">Total Secret: {{ is_countable($secrets) ? count($secrets) : 0 }}</div>
        </div>

        <div class="bg-gray-800 p-6 rounded-lg shadow-md border border-gray-700 mb-8">
            <h2 class="text-lg font-semibold mb-4 text-cyan-300">📋 Daftar PPP Secret</h2>
            <div class="max-h-[500px] overflow-y-auto overflow-x-auto pr-2 mb-4">
                <table id="pppTable" class="w-full text-left border-collapse">
                    <thead class="sticky top-0 bg-gray-800 z-10 border-b border-gray-700">
                        <tr class="text-gray-400 text-sm">
                            <th class="p-3">No</th>
                            <th class="p-3">Username</th>
                            <th class="p-3">Remote Address</th>
                            <th class="p-3">Password</th>
                            <th class="p-3">Comment</th>
                            <th class="p-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $i = 1; @endphp
                        @forelse($secrets as $secret)
                        <tr class="border-b border-gray-700/50 hover:bg-gray-700/30 text-sm">
                            <td class="p-3 text-gray-400">{{ $i++ }}</td>
                            <td class="p-3 font-medium text-cyan-300">{{ $secret['name'] ?? '-' }}</td>
                            <td class="p-3 text-emerald-400 font-mono">{{ $secret['remote-address'] ?? '-' }}</td>
                            <td class="p-3 text-gray-400 font-mono">{{ $secret['password'] ?? '-' }}</td>
                            <td class="p-3 text-gray-300">{{ $secret['comment'] ?? '-' }}</td>
                            <td class="p-3 text-center">
                                @if(strtolower(auth()->user()->role) === 'admin')
                                <form action="{{ route('vpn.destroy', 0) }}" method="POST" onsubmit="return confirm('Hapus secret ini langsung dari MikroTik?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="direct_name" value="{{ $secret['name'] ?? '' }}">
                                    <button type="submit" class="bg-red-600 hover:bg-red-500 text-white px-2.5 py-1 rounded text-xs font-medium">Hapus</button>
                                </form>
                                @else
                                <span class="text-xs text-gray-500 italic">View Only</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="p-6 text-center text-gray-500">Tidak ada data Secret ditemukan.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>

            </div>
</div>
<!-- TABEL LOG AKTIVITAS (BERBASIS FILE) -->
<div class="bg-gray-800 p-6 rounded-lg shadow-md border border-gray-700 mb-8 mt-8">
    <h2 class="text-lg font-semibold mb-4 text-purple-300">📜 Log Aktivitas Sistem (Action History)</h2>
    <div class="max-h-[300px] overflow-y-auto overflow-x-auto pr-2">
        <table class="w-full text-left border-collapse text-sm">
            <thead class="sticky top-0 bg-gray-800 z-10 border-b border-gray-700 text-gray-400">
                <tr>
                    <th class="p-3">Riwayat Log Aktivitas</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs ?? [] as $logLine)
                <tr class="border-b border-gray-700/50 hover:bg-gray-700/30 font-mono text-xs">
                    <td class="p-3 text-gray-300">{{ $logLine }}</td>
                </tr>
                @empty
                <tr>
                    <td class="p-6 text-center text-gray-500 font-sans">Belum ada aktivitas terekam.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
        </div>
    </div>
    <!-- Script JavaScript -->
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        var natTable = document.getElementById("natTable");
        if (natTable) {
            var tbody = natTable.getElementsByTagName("tbody")[0];
            var rowCount = tbody ? tbody.rows.length : 0;
            if (rowCount === 1 && tbody.rows[0].cells.length === 1) {
                rowCount = 0;
            }
            var countEl = document.getElementById("totalNatCount");
            if (countEl) {
                countEl.innerText = rowCount;
            }
        }
    });

    function fillClientPassword(selectObj) {
        var selectedOption = selectObj.options[selectObj.selectedIndex];
        var password = selectedOption.getAttribute('data-password') || '';
        document.getElementById('clientPass').value = password;
    }

    function filterPppTable() {
        var input = document.getElementById("searchPppInput").value.toLowerCase();
        var table = document.getElementById("pppTable");
        if (!table) return;
        var tr = table.getElementsByTagName("tr");

        for (var i = 1; i < tr.length; i++) {
            var tdName = tr[i].getElementsByTagName("td")[1];
            var tdIp = tr[i].getElementsByTagName("td")[2];
            
            if (tdName || tdIp) {
                var nameValue = tdName ? tdName.textContent || tdName.innerText : "";
                var ipValue = tdIp ? tdIp.textContent || tdIp.innerText : "";
                
                if (nameValue.toLowerCase().indexOf(input) > -1 || ipValue.toLowerCase().indexOf(input) > -1) {
                    tr[i].style.display = "";
                } else {
                    tr[i].style.display = "none";
                }
            }
        }
    }

    function filterNatTable() {
        var input = document.getElementById("searchNatInput").value.toLowerCase();
        var table = document.getElementById("natTable");
        if (!table) return;
        var tr = table.getElementsByTagName("tr");

        for (var i = 1; i < tr.length; i++) {
            var tdName = tr[i].getElementsByTagName("td")[1];
            var tdIp = tr[i].getElementsByTagName("td")[2];
            
            if (tdName || tdIp) {
                var nameValue = tdName ? tdName.textContent || tdName.innerText : "";
                var ipValue = tdIp ? tdIp.textContent || tdIp.innerText : "";
                
                if (nameValue.toLowerCase().indexOf(input) > -1 || ipValue.toLowerCase().indexOf(input) > -1) {
                    tr[i].style.display = "";
                } else {
                    tr[i].style.display = "none";
                }
            }
        }
    }

    function generateClientScript() {
        var proto = document.getElementById("clientProtocol").value;
        var user = document.getElementById("clientUserSelect").value.trim();
        var pass = document.getElementById("clientPass").value.trim();
        var pubIp = "163.61.244.246";

        if (!user) {
            alert("Harap pilih Username client dari daftar terlebih dahulu!");
            return;
        }

        var script = "";
        if (proto === "ovpn") {
            script = `/interface ovpn-client\nadd name="ovpn-client-${user}" connect-to="${pubIp}" port=1194 user="${user}" password="${pass}" profile=default use-compression=no use-encryption=yes certificate=none`;
        } else if (proto === "sstp") {
            script = `/interface sstp-client\nadd name="sstp-client-${user}" connect-to="${pubIp}" user="${user}" password="${pass}" profile=default verify-server=no`;
        } else if (proto === "l2tp") {
            script = `/interface l2tp-client\nadd name="l2tp-client-${user}" connect-to="${pubIp}" user="${user}" password="${pass}" profile=default use-ipsec=yes ipsec-secret="rahasia"`;
        }

        document.getElementById("resultClientScript").value = script;
    }

    function copyClientScript() {
        var textarea = document.getElementById("resultClientScript");
        if (!textarea.value) {
            alert("Belum ada script yang di-generate!");
            return;
        }
        textarea.select();
        navigator.clipboard.writeText(textarea.value).then(function() {
            alert("Script berhasil disalin ke clipboard!");
        });
    }

function copyDetailText(user, ip, www, winbox, api) {
        var text = `Halo, berikut adalah informasi akses VPN & Port Forwarding Anda:\n\n` +
                   `👤 Username: ${user}\n` +
                   `🌐 IP Publik Server: 163.61.244.246\n` +
                   `📌 IP Remote Client: ${ip}\n\n` +
                   `🔌 Mapping Port:\n` +
                   `- Port WWW (Web): 163.61.244.246:${www}\n` +
                   `- Port Winbox: 163.61.244.246:${winbox}\n` +
                   `- Port API: 163.61.244.246:${api}\n\n` +
                   `Silakan disimpan dengan baik. Terima kasih!`;

        navigator.clipboard.writeText(text).then(function() {
            alert('Detail informasi berhasil disalin ke clipboard!');
        });
    }

function shareToWhatsApp(user, ip, www, winbox, api) {
        var text = `Halo, berikut adalah informasi akses VPN & Port Forwarding Anda:\n\n` +
                   `👤 Username: ${user}\n` +
                   `🌐 IP Publik Server: 163.61.244.246\n` +
                   `📌 IP Remote Client: ${ip}\n\n` +
                   `🔌 Mapping Port:\n` +
                   `- Port WWW (Web): 163.61.244.246:${www}\n` +
                   `- Port Winbox: 163.61.244.246:${winbox}\n` +
                   `- Port API: 163.61.244.246:${api}\n\n` +
                   `Silakan disimpan dengan baik. Terima kasih!`;
                   
        window.open('https://api.whatsapp.com/send?text=' + encodeURIComponent(text), '_blank');
    }

    function shareToTelegram(user, ip, www, winbox, api) {
        var text = `Halo, berikut adalah informasi akses VPN & Port Forwarding Anda:\n\n` +
                   `👤 Username: ${user}\n` +
                   `🌐 IP Publik Server: 163.61.244.246\n` +
                   `📌 IP Remote Client: ${ip}\n\n` +
                   `🔌 Mapping Port:\n` +
                   `- Port WWW (Web): 163.61.244.246:${www}\n` +
                   `- Port Winbox: 163.61.244.246:${winbox}\n` +
                   `- Port API: 163.61.244.246:${api}\n\n` +
                   `Silakan disimpan dengan baik. Terima kasih!`;
                   
        window.open('https://t.me/share/url?url=' + encodeURIComponent(window.location.href) + '&text=' + encodeURIComponent(text), '_blank');
    }
    </script>
</body>
</html>
