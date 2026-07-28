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
            'host'     => env('MIKROTIK_HOST', '163.61.244.246'),
            'user'     => env('MIKROTIK_USER', 'sandi'),
            'pass'     => env('MIKROTIK_PASS', '88'),
            'port'     => (int) env('MIKROTIK_PORT', 8728),
        ]);
    }

    // Ambil data PPP Secret langsung dari MikroTik
    public function getSecrets()
    {
        $query = new Query('/ppp/secret/print');
        return $this->client->query($query)->read();
    }

    // Ambil data Firewall NAT langsung dari MikroTik
    public function getNatRules()
    {
        $query = new Query('/ip/firewall/nat/print');
        return $this->client->query($query)->read();
    }

    // Tambah PPP Secret ke MikroTik
    public function addSecret($username, $password, $profile, $service, $remoteAddress)
    {
        $query = (new Query('/ppp/secret/add'))
            ->equal('name', $username)
            ->equal('password', $password)
            ->equal('profile', $profile)
            ->equal('service', $service)
            ->equal('remote-address', $remoteAddress);

        return $this->client->query($query)->read();
    }

    // Update PPP Secret di MikroTik berdasarkan .id
    public function updateSecret($id, $password, $profile)
    {
        $query = (new Query('/ppp/secret/set'))
            ->equal('.id', $id)
            ->equal('password', $password)
            ->equal('profile', $profile);

        return $this->client->query($query)->read();
    }

    // Tambah Rule DST-NAT dengan Dst-Address Public 163.61.244.246
    public function addNatRule($dstPort, $toPort, $toAddress, $comment)
    {
        $query = (new Query('/ip/firewall/nat/add'))
            ->equal('chain', 'dstnat')
            ->equal('protocol', 'tcp')
            ->equal('dst-address', '163.61.244.246')
            ->equal('dst-port', $dstPort)
            ->equal('action', 'dst-nat')
            ->equal('to-addresses', $toAddress)
            ->equal('to-ports', $toPort)
            ->equal('comment', $comment);

        return $this->client->query($query)->read();
    }
}
