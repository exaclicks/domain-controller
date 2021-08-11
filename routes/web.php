<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DomainController;
use App\Models\BannedList;
use App\Models\Domain;
use Carbon\Carbon;
use DigitalOceanV2\Client;
use DigitalOceanV2\ResultPager;
use GuzzleHttp\Psr7\Request;
use Illuminate\Http\Request as HttpRequest;
use phpseclib3\Net\SSH2;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;

Route::get('/moveToNewDomain/{oldDomainName}/{newDomainName}', function ($oldDomainName, $newDomainName) {
    $progressIsSuccess = false;
    $addNewDropletRequest = HttpRequest::create('/addNewDroplet/' . $newDomainName, 'GET');
    $dropletId = Route::dispatch($addNewDropletRequest)->getOriginalContent();

    $addNewDomainRecordsRequest = HttpRequest::create('/addNewDomainRecords/' . $newDomainName . "/" . $dropletId, 'GET');
    $progressIsSuccess = Route::dispatch($addNewDomainRecordsRequest)->getOriginalContent();
    if($progressIsSuccess){
        return "Taşıma başarılı.";
    }else {
        return "Taşıma Başarısız.";
    }
});


Route::get('/addNewDomainRecords/{newDomainName}/{dropletId}', function ($newDomainName, $dropletId) {

    //259223638
    try {

        $token = Config::get('values.DIGITALOCEAN_ACCESS_TOKEN');
        $client = new Client();
        $client->authenticate($token);
        $domainRecord = $client->domainRecord();
        $droplet = $client->droplet();
        sleep(10);
        comeBack:
        $droplet123 = $droplet->getById($dropletId);
        if(count($droplet123->networks) == 0){
            sleep(5);
            goto comeBack;
        }

        $dropletIpAdress = $droplet123->networks[1]->ipAddress;
        $hostingIp = $dropletIpAdress;
        $digitalocean_nameservers_ipies = ["173.245.58.51", "173.245.59.41", "198.41.222.173"];
        $new_nameservers = ['ns1.' . $newDomainName, 'ns2.' . $newDomainName, 'ns3.' . $newDomainName];
        $domainRecordInfos = $domainRecord->getAll($newDomainName);
        //CREATE NEW DOMAİN RECORDS
        $domainRecordInfos = $domainRecord->getAll($newDomainName);

        //Delete old dns
        foreach ($domainRecordInfos as $value) {
            if ($value->type != "SOA") {
                $domainRecord->remove($newDomainName, $value->id);
            }
        }

        //create new dns and nameservers;
        $created = $domainRecord->create($newDomainName, 'A', '@', $hostingIp, null, null, null, null, null, 3600);
        $created = $domainRecord->create($newDomainName, 'A', 'www', $hostingIp, null, null, null, null, null, 3600);
        $created = $domainRecord->create($newDomainName, 'NS', '@', $new_nameservers[1] . ".", null, null, null, null, null, 86400);
        $created = $domainRecord->create($newDomainName, 'NS', '@', $new_nameservers[1] . ".", null, null, null, null, null, 86400);
        $created = $domainRecord->create($newDomainName, 'NS', '@', $new_nameservers[2] . ".", null, null, null, null, null, 86400);
        $created = $domainRecord->create($newDomainName, 'A', $new_nameservers[0], $digitalocean_nameservers_ipies[0], null, null, null, null, null, 3600);
        $created = $domainRecord->create($newDomainName, 'A', $new_nameservers[1], $digitalocean_nameservers_ipies[1], null, null, null, null, null, 3600);
        $created = $domainRecord->create($newDomainName, 'A', $new_nameservers[2], $digitalocean_nameservers_ipies[2], null, null, null, null, null, 3600);


        return true;
    } catch (\Throwable $th) {
        return false;
    }
});


Route::get('/addNewDroplet/{newDomainName}', function ($newDomainName) {
    $dropletId = 0;
    try {

        $snapshotsDropletId = '89112810';
        $location = 'fra1';
        $device = 's-1vcpu-1gb';
        $token = Config::get('values.DIGITALOCEAN_ACCESS_TOKEN');
        $client = new Client();
        $client->authenticate($token);
        $droplet = $client->droplet();




        $sshKeysRequest = HttpRequest::create('/checkSshKeys', 'GET');
        $sshKeys = Route::dispatch($sshKeysRequest)->getOriginalContent();

        $snapIdRequest = HttpRequest::create('/checkSnapshots/' . $snapshotsDropletId, 'GET');
        $snapId = Route::dispatch($snapIdRequest)->getOriginalContent();


        $created = $droplet->create($newDomainName, $location, $device, $snapId, false, false, false, $sshKeys);
        $dropletId = $created->id;
    } catch (\Throwable $th) {
        return $dropletId;
    }


    return $dropletId;
});

Route::get('/checkSnapshots/{dropletId}', function ($dropletId) {
    $token = Config::get('values.DIGITALOCEAN_ACCESS_TOKEN');
    $client = new Client();
    $droplet = $client->droplet();
    $client->authenticate($token);
    $images = $droplet->getSnapshots($dropletId);
    // print_r($images[count($images)-1]);
    return $images[count($images) - 1]->id;
});

Route::get('/checkSshKeys', function () {
    $token = Config::get('values.DIGITALOCEAN_ACCESS_TOKEN');
    $client = new Client();
    $client->authenticate($token);
    $keyIds = [];

    // return the key api
    $key = $client->key();

    // return a collection of Key entity
    $keys = $key->getAll();

    foreach ($keys as $key => $value) {
        array_push($keyIds, $value->id);
    }
    // print_r($images[count($images)-1]);
    return $keyIds;
});



Route::get('/banlanmalogu', function () {
    // BannedList::truncate();

    $bannedItem =  BannedList::all();
    foreach ($bannedItem  as $key => $value) {
        echo $value->id . "--" . $value->domain_id . "---" . $value->how_many_times . "---" . $value->banned_time . "<br>";
    }
});



Route::get('/', function () {

    /*     $getBannedItem = BannedList::where('domain_id', 1);
    $getBannedItem = $getBannedItem->first();
    $day = Carbon::now();
    $date = Carbon::createFromFormat('Y-m-d H:i:s', $day);
    $date->subDay(); // Subtracts 1 
    $getBannedItem->banned_time =$date;
    $getBannedItem->save();
 */

    // BannedList::truncate();

    /*    $bannedItem =  BannedList::all();
   foreach ($bannedItem  as $key => $value) {
      echo $value->id."--".$value->domain_id."---".$value->how_many_times."---".$value->banned_time."<br>";
   }  */
    return view('welcome');
    exit();


    $moved_text = "The document has moved ";

    $ssh = new SSH2('5.2.82.44');
    if (!$ssh->login('root', 'g#bpyOrvjt')) {
        $this->info('ssh connection failed');
    }


    $link = "https://alittihad.ae"; //türkiyeye banlanmış yönlendirilmemiş site;
    //$link = "http://www.atletismogalego.com/"; //türkiyeye banlanmış yönlendirilmiş site;
    //$link = "https://hakgg.com/anasayfa/"; // türkiyeye açık site;
    $html = $ssh->exec('curl -v  -H "Cache-Control: max-age=0"   -H "User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/92.0.4515.131 Safari/537.36" -H "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9"  -H "Accept-Language: tr-TR,tr;q=0.9,tr;q=0.8" ' . $link);


    echo $html;
    exit();






    if ($html != '') {
        $isMoved = strpos($html, $moved_text);
        if ($isMoved != '') {
            $status = "moved";
        } else {
            $status = "working";
        }
    } else {
        $status = "banned";
    }

    echo $status . "<br><br>";

    echo $html;
});


Route::resource('domains', DomainController::class);
