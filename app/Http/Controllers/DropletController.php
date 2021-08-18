<?php

namespace App\Http\Controllers;

use App\Models\Domain;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Config;
use DigitalOceanV2\Client;


class DropletController extends Controller
{


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function add_new_droplet(Request $request)
    {
        $request->validate([
            'new_domain_name' => 'required',
        ]);

        $newDomainName = $request->get('new_domain_name');
        $dropletId = 0;
        $snapshotsDropletId = Config::get('values.SNAPSHOT_ID');
        $location = 'fra1';
        $device = 's-1vcpu-1gb';
        $token = Config::get('values.DIGITALOCEAN_ACCESS_TOKEN');
        $client = new Client();
        $client->authenticate($token);
        $droplet = $client->droplet();
        $sshKeys = [];

        try {
            // GET SNAPSHOT_ID WİTH DROPLET ID
            $images = $droplet->getSnapshots($snapshotsDropletId);
            $snapId = $images[count($images) - 1]->id;

            // GET SSH_KEYS
            $key = $client->key();
            $keys = $key->getAll();
            foreach ($keys as $key => $value) {
                array_push($sshKeys, $value->id);
            }

            $created = $droplet->create($newDomainName, $location, $device, $snapId, false, false, false, $sshKeys);
            $dropletId = $created->id;
        } catch (\Throwable $th) {
            return $dropletId;
        }

        return $dropletId;
    }

}
