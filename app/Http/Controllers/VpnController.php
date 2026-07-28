<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\VpnAllocation;
use App\Services\MikrotikService;

class VpnController extends Controller
{
    protected $mikrotik;

    public function __construct(MikrotikService $mikrotik)
    {
        $this->mikrotik = $mikrotik;
    }

    // Menampilkan Halaman Utama (Secret & DST-NAT dengan Paginasi per 10 data)
    public function index(Request $request)
    {
        $search = $request->input('search');
        $secrets = [];
        $natRules = [];

        try {
            $secrets = $this->mikrotik->getSecrets();
            $natRules = $this->mikrotik->getNatRules();
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal terhubung ke MikroTik: ' . $e->getMessage());
        }

        // Filter Pencarian Secret Real-time
        if ($search) {
            $secrets = array_filter($secrets, function ($item) use ($search) {
                return stripos($item['name'] ?? '', $search) !== false || 
                       stripos($item['remote-address'] ?? '', $search) !== false;
            });
        }

        // Paginasi Manual array MikroTik per 10 data untuk Secret
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 10;
        $currentItems = array_slice($secrets, ($currentPage - 1) * $perPage, $perPage);
        $paginatedSecrets = new LengthAwarePaginator($currentItems, count($secrets), $perPage, $currentPage, [
            'path' => LengthAwarePaginator::resolveCurrentPath(),
            'query' => $request->query(),
        ]);

        return view('vpn.index', [
            'secrets' => $paginatedSecrets,
            'natRules' => $natRules,
            'search' => $search
        ]);
    }

    // Proses Simpan Secret & Auto NAT
    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required|string|unique:vpn_allocations,username',
            'password' => 'required|string',
            'profile'  => 'required|string',
            'service'  => 'required|string',
        ]);

        try {
            $lastVpn = VpnAllocation::latest()->first();
            $nextIp = $lastVpn ? long2ip(ip2long($lastVpn->remote_address) + 1) : '10.163.61.2';

            $portWww = $lastVpn ? $lastVpn->port_www + 1 : 8102;
            $portWinbox = $lastVpn ? $lastVpn->port_winbox + 1 : 7102;
            $portApi = $lastVpn ? $lastVpn->port_api + 1 : 7203;

            // Eksekusi ke MikroTik CHR
            $this->mikrotik->addSecret($request->username, $request->password, $request->profile, $request->service, $nextIp);
            $this->mikrotik->addNatRule($portWww, 80, $nextIp, "WWW-{$request->username}");
            $this->mikrotik->addNatRule($portWinbox, 8291, $nextIp, "Winbox-{$request->username}");
            $this->mikrotik->addNatRule($portApi, 8728, $nextIp, "API-{$request->username}");

            VpnAllocation::create([
                'username'       => $request->username,
                'password'       => $request->password,
                'profile'        => $request->profile,
                'service'        => $request->service,
                'remote_address' => $nextIp,
                'port_www'       => $portWww,
                'port_winbox'    => $portWinbox,
                'port_api'       => $portApi,
            ]);

            return redirect()->route('vpn.index')->with('success', 'VPN Secret & NAT berhasil digenerate ke MikroTik!');
        } catch (\Exception $e) {
            return redirect()->route('vpn.index')->with('error', 'Gagal memproses ke MikroTik: ' . $e->getMessage());
        }
    }

    // Proses Update Secret
    public function update(Request $request, $id)
    {
        try {
            $this->mikrotik->updateSecret($request->mikrotik_id, $request->password, $request->profile);
            
            $local = VpnAllocation::where('username', $request->username)->first();
            if ($local) {
                $local->update([
                    'password' => $request->password,
                    'profile'  => $request->profile,
                ]);
            }

            return redirect()->route('vpn.index')->with('success', 'Data PPP Secret berhasil diperbarui!');
        } catch (\Exception $e) {
            return redirect()->route('vpn.index')->with('error', 'Gagal memperbarui secret: ' . $e->getMessage());
        }
    }

    // Hapus Data
    public function destroy($id)
    {
        $vpn = VpnAllocation::findOrFail($id);
        $vpn->delete();

        return redirect()->route('vpn.index')->with('success', 'Data VPN berhasil dihapus.');
    }
}
