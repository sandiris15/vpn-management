<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\MikrotikService;
use Illuminate\Support\Facades\File;

class VpnController extends Controller
{
    protected $mikrotik;

    public function __construct(MikrotikService $mikrotik)
    {
        $this->mikrotik = $mikrotik;
    }

    // Fungsi helper untuk menulis log ke file teks
    private function writeLog($action, $description)
    {
        $logPath = storage_path('logs/vpn_activity.log');
        $username = auth()->user()->name ?? 'System';
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[$timestamp] | $username | $action | $description\n";
        
        File::append($logPath, $logEntry);
    }

    public function index(Request $request)
    {
        $search = $request->input('search');
        $natRules = [];
        $secrets = [];

        try {
            $secrets = array_reverse($this->mikrotik->getSecrets());
            $natRules = array_reverse($this->mikrotik->getNatRules());
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal terhubung ke MikroTik: ' . $e->getMessage());
        }

        if ($search) {
            $secrets = array_filter($secrets, function($item) use ($search) {
                return stripos($item['name'] ?? '', $search) !== false || 
                       stripos($item['remote-address'] ?? '', $search) !== false;
            });
        }

        // Baca log dari file teks
        $logs = [];
        $logPath = storage_path('logs/vpn_activity.log');
        if (File::exists($logPath)) {
            $lines = file($logPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $logs = array_reverse(array_slice($lines, -50)); // Ambil 50 log terakhir
        }

        return view('vpn.index', [
            'secrets' => $secrets,
            'natRules' => $natRules,
            'logs' => $logs,
            'search' => $search,
            'localVpns' => collect()
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:255',
            'password' => 'required|string|max:255',
            'profile'  => 'required|string|max:255',
            'service'  => 'required|string|max:255',
        ]);

        try {
            $localAddress = '10.163.61.1';
            
            $secrets = $this->mikrotik->getSecrets();
            $nextId = count($secrets) + 1;
            $nextIp = '10.163.61.' . ($nextId + 1);

            $natRules = $this->mikrotik->getNatRules();
            $offset = count($natRules) > 0 ? count($natRules) / 3 : 0;
            
            $portWww = 8102 + (int)$offset;
            $portWinbox = 7102 + (int)$offset;
            $portApi = 7203 + (int)$offset;

            $this->mikrotik->addSecret($request->username, $request->password, $request->profile, $request->service, $nextIp, $localAddress);

            $this->mikrotik->addNatRule('dstnat', '163.61.244.246', $portWww, 'tcp', '80', $nextIp, $request->username . ' - WWW');
            $this->mikrotik->addNatRule('dstnat', '163.61.244.246', $portWinbox, 'tcp', '8291', $nextIp, $request->username . ' - Winbox');
            $this->mikrotik->addNatRule('dstnat', '163.61.244.246', $portApi, 'tcp', '8728', $nextIp, $request->username . ' - API');

            // Catat log ke file
            $this->writeLog('TAMBAH VPN', 'Berhasil menambahkan user ' . $request->username . ' dengan IP ' . $nextIp);

            return redirect()->route('vpn.index')->with('success', 'VPN Secret & Auto NAT berhasil ditambahkan langsung ke MikroTik!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menyimpan: ' . $e->getMessage())->withInput();
        }
    }

public function destroy(Request $request, $id)
    {
        // Hanya admin yang diizinkan menghapus
        if (strtolower(auth()->user()->role) !== 'admin') {
            return redirect()->route('vpn.index')->with('error', 'Akses ditolak! Operator tidak diizinkan menghapus data.');
        }

        try {
            $directName = $request->input('direct_name');

            $secrets = $this->mikrotik->getSecrets();
            foreach ($secrets as $secret) {
                if (($secret['name'] ?? '') === $directName || ($secret['id'] ?? '') === $id || (isset($secret['.id']) && $secret['.id'] === $id)) {
                    $this->mikrotik->removeSecret($secret['.id'] ?? $id);
                }
            }

            $natRules = $this->mikrotik->getNatRules();
            foreach ($natRules as $rule) {
                $comment = $rule['comment'] ?? '';
                if (!empty($directName) && stripos($comment, $directName) !== false) {
                    $this->mikrotik->removeNatRule($rule['.id']);
                }
            }

            // Catat log ke file
            $this->writeLog('HAPUS VPN', 'Menghapus user/rule terkait: ' . ($directName ?? $id));

            return redirect()->route('vpn.index')->with('success', 'Data berhasil dihapus dari MikroTik.');
        } catch (\Exception $e) {
            return redirect()->route('vpn.index')->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }
}
