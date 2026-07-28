<?php

namespace App\Services;

use RouterOS\Client;
use RouterOS\Query;

class MikrotikService
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client([
            'host'     => env('MIKROTIK_HOST', 'ganti ip publik chr'),
            'user'     => env('MIKROTIK_USER', 'admin'),
            'pass'     => env('MIKROTIK_PASS', '12344'),
            'port'     => (int) env('MIKROTIK_PORT', 8728),
            'timeout'  => 10,
        ]);
    }

    // Mengambil daftar PPP Secret dari MikroTik
    public function getSecrets()
    {
        $query = new Query('/ppp/secret/print');
        return $this->client->query($query)->read();
    }

    // Mengambil daftar Rule Firewall NAT dari MikroTik
    public function getNatRules()
    {
        $query = new Query('/ip/firewall/nat/print');
        return $this->client->query($query)->read();
    }

    // Menambahkan PPP Secret baru ke MikroTik
    public function addSecret($name, $password, $profile, $service, $remoteAddress, $localAddress)
    {
        $query = (new Query('/ppp/secret/add'))
            ->equal('name', $name)
            ->equal('password', $password)
            ->equal('profile', $profile)
            ->equal('service', $service)
            ->equal('remote-address', $remoteAddress)
            ->equal('local-address', $localAddress);

        return $this->client->query($query)->read();
    }

    // Menambahkan Rule DST-NAT baru ke MikroTik (dengan format komentar: username - Layanan)
    public function addNatRule($chain, $dstAddress, $dstPort, $protocol, $toPorts, $toAddresses, $comment)
    {
        $query = (new Query('/ip/firewall/nat/add'))
            ->equal('chain', $chain)
            ->equal('dst-address', $dstAddress)
            ->equal('dst-port', $dstPort)
            ->equal('protocol', $protocol)
            ->equal('to-ports', $toPorts)
            ->equal('to-addresses', $toAddresses)
            ->equal('comment', $comment);

        return $this->client->query($query)->read();
    }

    // Menghapus PPP Secret berdasarkan ID MikroTik (.id)
    public function removeSecret($id)
    {
        $query = (new Query('/ppp/secret/remove'))
            ->equal('.id', $id);
        return $this->client->query($query)->read();
    }

    // Menghapus Rule DST-NAT berdasarkan ID MikroTik (.id)
    public function removeNatRule($id)
    {
        $query = (new Query('/ip/firewall/nat/remove'))
            ->equal('.id', $id);
        return $this->client->query($query)->read();
    }
}
