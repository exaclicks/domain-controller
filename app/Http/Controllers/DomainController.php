<?php

namespace App\Http\Controllers;

use App\Models\Domain;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Config;
use DigitalOceanV2\Client;
use App\Models\BannedList;
use phpseclib3\Net\SSH2;
use Illuminate\Support\Facades\Mail;


class DomainController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $domains = Domain::orderByDesc('status')->where('used', 1)->get();

        return view('domains.index', compact('domains'))
            ->with('i', (request()->input('page', 1) - 1) * 5);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function un_used_domain_index()
    {
        $domains = Domain::orderByDesc('status')->where('used', 0)->get();
        return view('domains.un_used_domain_index', compact('domains'))
            ->with('i', (request()->input('page', 1) - 1) * 5);
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('domains.create');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function un_used_domain_create()
    {
        return view('domains.un_used_domain_create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'hosting' => 'required',
        ]);

        $domain = Domain::create([
            'name' => $request->get('name'),
            'hosting' => $request->get('hosting'),
            'start_time' => $request->get('start_time'),
            'finish_time' => $request->get('finish_time'),
            'status' => 0,
            'used' => 1,
        ]);

        $domain->save();

        return redirect()->route('domains.index')
            ->with('success', 'Domain oluşturma başarıyla tamamlandı');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function un_used_domain_store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'hosting' => 'required',
        ]);

        $domain = Domain::create([
            'name' => $request->get('name'),
            'hosting' => $request->get('hosting'),
            'start_time' => $request->get('start_time'),
            'finish_time' => $request->get('finish_time'),
            'status' => 0,
            'used' => 0,
        ]);

        $domain->save();

        return redirect()->route('un_used_domain_index')
            ->with('success', 'Domain oluşturma başarıyla tamamlandı');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Domain  $domain
     * @return \Illuminate\Http\Response
     */
    public function show(Domain $domain)
    {
        return view('domains.show', compact('domain'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Domain  $domain
     * @return \Illuminate\Http\Response
     */
    public function edit(Domain $domain)
    {
        return view('domains.edit', compact('domain'));
    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Domain  $domain
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Domain $domain)
    {
        $request->validate([
            'name' => 'required',
            'hosting' => 'required',
        ]);


        $domain->update($request->all());

        return redirect()->route('domains.index')
            ->with('success', 'Domain güncellemesi başarıyla tamamlandı');
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Domain  $domain
     * @return \Illuminate\Http\Response
     */
    public function destroy(Domain $domain)
    {
        $domain->delete();

        return redirect()->route('domains.index')
            ->with('success', 'Domain kaldırma başarıyla tamamlandı');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Domain  $domain
     * @return \Illuminate\Http\Response
     */
    public function un_used_destroy(Request $request, Domain $domain)
    {
        echo $domain->id . "<br>";
        echo $request;
        exit();
        $domain->delete();

        return redirect()->route('un_used_domain_index')
            ->with('success', 'Domain kaldırma başarıyla tamamlandı');
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function banlanmalogu(Request $request)
    {
        $bannedItem =  BannedList::all();
        foreach ($bannedItem  as $key => $value) {
            echo $value->id . "--" . $value->domain_id . "---" . $value->how_many_times . "---" . $value->banned_time . "<br>";
        }
    }



    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function add_new_domain_server_records(Request $request)
    {
        $request->validate([
            'old_domain_name' => 'required',
            'new_domain_name' => 'required',
        ]);
        $newDomainName = $request->get('new_domain_name');
        $oldDomainName = $request->get('old_domain_name');
        $token = Config::get('values.DIGITALOCEAN_ACCESS_TOKEN');
        $client = new Client();
        $client->authenticate($token);
        $domainRecord = $client->domainRecord();
        $dropletClient = $client->droplet();
        $WHICH_MAIL_FOR_SSH_CONNECT_PROBLEM = Config::get('values.WHICH_MAIL_FOR_SSH_CONNECT_PROBLEM');
        $droplet = $client->droplet();
        $droplets = $droplet->getAll();

        try {

            ////////////////STEP 1 CREATE NEW DROPLET
            $addNewDropletRequest = Request::create('/add_new_droplet?new_domain_name=' . $newDomainName, 'GET');
            $dropletId = Route::dispatch($addNewDropletRequest)->getOriginalContent();
            ////////////////




            //////////////STEP 2 CREATE NEW DOMAİN DNS RECORDS 
            sleep(10);
            comeBack:
            $droplet123 = $dropletClient->getById($dropletId);
            if (count($droplet123->networks) == 0) {
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
            $created = $domainRecord->create($newDomainName, 'A', "ns1", $digitalocean_nameservers_ipies[0], null, null, null, null, null, 3600);
            $created = $domainRecord->create($newDomainName, 'A', "ns2", $digitalocean_nameservers_ipies[1], null, null, null, null, null, 3600);
            $created = $domainRecord->create($newDomainName, 'A', "ns3", $digitalocean_nameservers_ipies[2], null, null, null, null, null, 3600);
            //////////////////            

            //STEP 3 CREATE NEW APACHE CONFİGS
   
            $newServerIp = 1;
            foreach ($droplets as  $droplet) {
                if ($droplet->name == $newDomainName)
                    $newServerIp = $droplet->networks[1]->ipAddress;
            }

            $ssh = new SSH2($newServerIp);
            if (!$ssh->getServerPublicHostKey()) {
                Mail::raw(" this server don't connect to " . $newServerIp, function ($mail) use ($WHICH_MAIL_FOR_SSH_CONNECT_PROBLEM, $newServerIp) {
                    $mail->from('ex@exaclicks.com');
                    $mail->to($WHICH_MAIL_FOR_SSH_CONNECT_PROBLEM)
                        ->subject(" this server don't connect to " . $newServerIp);
                });
                exit();
            }


            $document_root ='1xbet-html-page';
            $execute_code = 'echo "<VirtualHost *:80>
            ServerAdmin webmaster@localhost
            ServerName ' . $newDomainName . '
            ServerAlias www.' . $newDomainName . '
            DocumentRoot /var/www/'.$document_root.'
            ErrorLog ${APACHE_LOG_DIR}/error.log
            CustomLog ${APACHE_LOG_DIR}/access.log combined
        </VirtualHost>" >> /etc/apache2/sites-available/' . $newDomainName . '.conf';
    
            $ssh->exec($execute_code);
            $ssh->exec('a2ensite ' . $newDomainName . '.conf');
            $ssh->exec('systemctl restart apache2');

            //SSL CONFİG
            $ssh->exec('certbot --apache -d ' . $newDomainName . ' -d www.' . $oldDomainName);
            sleep(15);
            $ssh->exec('1');
            //

            return true;
        } catch (\Throwable $th) {
            return false;
        }
    }





    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function old_domain_move_redirect_server(Request $request)
    {
        $oldDomainName = $request->get('old_domain_name');
        $newDomainName = $request->get('new_domain_name');
        $response = false;
        $token = Config::get('values.DIGITALOCEAN_ACCESS_TOKEN');
        $redirectServerIp = Config::get('values.REDİRECT_SERVER_IP');
        $redirectServerDefaultPassword = Config::get('values.REDİRECT_SERVER_DEFAULT_PASSWORD');
        $WHICH_MAIL_FOR_SSH_CONNECT_PROBLEM = Config::get('values.WHICH_MAIL_FOR_SSH_CONNECT_PROBLEM');
        $digitalocean_nameservers_ipies = ["173.245.58.51", "173.245.59.41", "198.41.222.173"];
        $new_nameservers = ['ns1.' . $oldDomainName, 'ns2.' . $oldDomainName, 'ns3.' . $oldDomainName];
        $client = new Client();
        $client->authenticate($token);
        $domainRecord = $client->domainRecord();
        $domainRecordInfos = $domainRecord->getAll($oldDomainName);


        $execute_code = 'echo "<VirtualHost *:80>
        ServerAdmin webmaster@localhost
        ServerName ' . $oldDomainName . '
        ServerAlias www.' . $oldDomainName . '
        DocumentRoot /var/www/1xbet-html-page
        Redirect / https://' . $newDomainName . '/               
        ErrorLog ${APACHE_LOG_DIR}/error.log
        CustomLog ${APACHE_LOG_DIR}/access.log combined
    </VirtualHost>" >> /etc/apache2/sites-available/' . $oldDomainName . '.conf';




        // ADD OLD DOMAİN RECORDS

        //Delete old dns
        foreach ($domainRecordInfos as $value) {
            if ($value->type != "SOA") {
                $domainRecord->remove($oldDomainName, $value->id);
            }
        }

        //create new dns and nameservers;
        $created = $domainRecord->create($oldDomainName, 'A', '@', $redirectServerIp, null, null, null, null, null, 3600);
        $created = $domainRecord->create($oldDomainName, 'A', 'www', $redirectServerIp, null, null, null, null, null, 3600);
        $created = $domainRecord->create($oldDomainName, 'NS', '@', $new_nameservers[1] . ".", null, null, null, null, null, 86400);
        $created = $domainRecord->create($oldDomainName, 'NS', '@', $new_nameservers[1] . ".", null, null, null, null, null, 86400);
        $created = $domainRecord->create($oldDomainName, 'NS', '@', $new_nameservers[2] . ".", null, null, null, null, null, 86400);
        $created = $domainRecord->create($oldDomainName, 'A', "ns1", $digitalocean_nameservers_ipies[0], null, null, null, null, null, 3600);
        $created = $domainRecord->create($oldDomainName, 'A', "ns2", $digitalocean_nameservers_ipies[1], null, null, null, null, null, 3600);
        $created = $domainRecord->create($oldDomainName, 'A', "ns3", $digitalocean_nameservers_ipies[2], null, null, null, null, null, 3600);
        ///




        // ADD NEW DOMAİN APACHE CONFİG
        $ssh = new SSH2($redirectServerIp);
        if (!$ssh->getServerPublicHostKey()) {
            Mail::raw(" this server don't connect to " . $redirectServerIp, function ($mail) use ($WHICH_MAIL_FOR_SSH_CONNECT_PROBLEM, $redirectServerIp) {
                $mail->from('ex@exaclicks.com');
                $mail->to($WHICH_MAIL_FOR_SSH_CONNECT_PROBLEM)
                    ->subject(" this server don't connect to " . $redirectServerIp);
            });
            exit();
        }

        $ssh->exec($execute_code);
        $ssh->exec('a2ensite ' . $oldDomainName . '.conf');
        $ssh->exec('systemctl restart apache2');

        //SSL CONFİG
        $ssh->exec('certbot --apache -d ' . $oldDomainName . ' -d www.' . $oldDomainName);
        sleep(15);
        $ssh->exec('1');
        ///
        return $response;
    }
}
