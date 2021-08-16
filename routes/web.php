<?php

use App\Http\Controllers\CodeController;
use App\Http\Controllers\BetCompanyController;

use Illuminate\Support\Facades\Mail;

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DomainController;
use App\Models\BannedList;
use App\Models\Domain;
use App\Models\GitDomain;
use Carbon\Carbon;
use DigitalOceanV2\Client;
use DigitalOceanV2\ResultPager;
use Illuminate\Http\Request as HttpRequest;
use phpseclib3\Net\SSH2;
use phpseclib3\Crypt\RSA;


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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;


Route::get('/testDomainChecker', function () {

    // status -1 ise sorun var kodda sorun var.

    // status 0 ise çalışıyor

    // status 1 ise banlandı

    // status 2 ise taşındı.

    // status 3 ise banlandıgı hakkında email gönderildi.
    $TR_SERVER_IP = Config::get('values.TR_SERVER_IP');
    $ACTION_TYPE = Config::get('values.ACTION_TYPE');
    $TR_SERVER_SSH_USERNAME = Config::get('values.TR_SERVER_SSH_USERNAME');
    $TR_SERVER_PASSWORD = Config::get('values.TR_SERVER_PASSWORD');
    $WHICH_MAIL_FOR_BANNED = Config::get('values.WHICH_MAIL_FOR_BANNED');
    $WHICH_MAIL_FOR_SSH_CONNECT_PROBLEM = Config::get('values.WHICH_MAIL_FOR_SSH_CONNECT_PROBLEM');

    $ssh = new SSH2($TR_SERVER_IP);
    if (!$ssh->login($TR_SERVER_SSH_USERNAME, $TR_SERVER_PASSWORD)) {

        Mail::raw(" this server don't connect to " . $TR_SERVER_IP, function ($mail) use ($WHICH_MAIL_FOR_SSH_CONNECT_PROBLEM, $TR_SERVER_IP) {
            $mail->from('ex@exaclicks.com');
            $mail->to($WHICH_MAIL_FOR_SSH_CONNECT_PROBLEM)
                ->subject(" this server don't connect to " . $TR_SERVER_IP);
        });

        exit();
    }

    $moved_text = "The document has moved ";
    $isMoved = "";


    $domains = Domain::all();
    foreach ($domains as $domain) {
        $status = -1;
        $link = $domain->name;
        $command = 'curl -s -H "Proxy-Connection: keep-alive"  -H "Cache-Control: max-age=0"   -H "Upgrade-Insecure-Requests: 1" -H "User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/92.0.4515.131 Safari/537.36" -H "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9"  -H "Accept-Language: tr-TR,tr;q=0.9,tr;q=0.8" ' . $link;
        $html = $ssh->exec($command);



        if ($html != '') {
            $isMoved = strpos($html, $moved_text);
            if ($isMoved != '') {
                $status = 2;
            } else {
                $status = 0;
            }
        } else {
            $status = 1;
        }


        // $domain->status = $status;


        if ($domain->status != 1 && $domain->status != 3) {



            if ($status == 1) {

                $bannedItem = new BannedList();
                $getBannedItem = BannedList::where('domain_id', $domain->id);

                $todayDate = Carbon::now();
                $bannedItem->how_many_times = 1;

                if ($getBannedItem->first()) {
                    $bannedItem  = $getBannedItem->first();
                    $bannedItem->how_many_times = $bannedItem->how_many_times + 1;
                    $diff = $todayDate->diffInMinutes($bannedItem->banned_time);
                    if ($diff >= 60) {
                        $bannedItem->how_many_times = 1;
                    }

                    $bannedItem->banned_time = $todayDate;
                    $bannedItem->save();
                } else {
                    $bannedItem->domain_id = $domain->id;
                    $bannedItem->banned_time = $todayDate;

                    $bannedItem->save();
                }




                if ($bannedItem->how_many_times > 4) {

                    if ($ACTION_TYPE == 0) {
                        $domain->save();

                        /*           Mail::raw($domain->name . " engellendi. <br> " . $html, function ($mail) use ($domain, $WHICH_MAIL_FOR_BANNED) {
                            $mail->from('ex@exaclicks.com'); // DONT CHANGE
                            $mail->to($WHICH_MAIL_FOR_BANNED)
                                ->subject($domain->name);
                        }); */
                        $domain->domain_status = 1; //  1 taşınması gerekiyor. 2 taşındı.
                        $domain->status = 3;
                        $domain->save();
                    }
                }
            }
        }
    }
});



Route::get('/banlanmalogu', function () {
    // BannedList::truncate();

    $bannedItem =  BannedList::all();
    foreach ($bannedItem  as $key => $value) {
        echo $value->id . "--" . $value->domain_id . "---" . $value->how_many_times . "---" . $value->banned_time . "<br>";
    }
});

use phpseclib3\Crypt\PublicKeyLoader;

Route::get('/testercode', function () {

    $redirectServerIp = Config::get('values.REDİRECT_SERVER_IP');
    $key_directory = '~/.ssh/id_rsa.pub';
    $connection = ssh2_connect("138.197.191.231", 22, array('hostkey' => 'ssh-rsa'));
    if (ssh2_auth_pubkey_file(
        $connection,
        'username',
        $key_directory,
        $key_directory,
        'secret'
    )) {
        echo "Public Key Authentication Successful\n";
    } else {
        die('Public Key Authentication Failed');
    }
    exit();

    $ssh = new SSH2($redirectServerIp);

    if (!$ssh->getServerPublicHostKey()) {
        echo "girmedi";
    }
    $ssh->login('root');

    $oldDomainName = "TESTTT.com";
    $execute_code = 'echo "<VirtualHost *:80>

        ServerAdmin webmaster@localhost
    
        ServerName ' . $oldDomainName . '
    
        ServerAlias www.' . $oldDomainName . '
    
        DocumentRoot /var/www/1xbet-html-page
    
        Redirect / https://' . $oldDomainName . '/               
    
        ErrorLog ${APACHE_LOG_DIR}/error.log
    
        CustomLog ${APACHE_LOG_DIR}/access.log combined
    
    </VirtualHost>" >> /etc/apache2/sites-available/' . $oldDomainName . '.conf';



    $ssh->exec($execute_code);
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

Route::resource('codes', CodeController::class);


Route::resource('bet_companies', BetCompanyController::class);


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
| Middleware options can be located in `app/Http/Kernel.php`
|
*/

// Homepage Route
Route::group(['middleware' => ['web', 'checkblocked']], function () {
    Route::get('/moveToNewDomain/{oldDomainName}/{newDomainName}', function ($oldDomainName, $newDomainName) {
        $progressIsSuccess = false;
        $addNewDropletRequest = HttpRequest::create('/addNewDroplet/' . $newDomainName, 'GET');
        $dropletId = Route::dispatch($addNewDropletRequest)->getOriginalContent();

        $addNewDomainRecordsRequest = HttpRequest::create('/addNewDomainRecords/' . $newDomainName . "/" . $dropletId, 'GET');
        $progressIsSuccess = Route::dispatch($addNewDomainRecordsRequest)->getOriginalContent();


        return $progressIsSuccess;
    });


    Route::get('/addNewDomainRecords/{newDomainName}/{dropletId}', function ($newDomainName, $dropletId) {


        try {

            $token = Config::get('values.DIGITALOCEAN_ACCESS_TOKEN');
            $client = new Client();
            $client->authenticate($token);
            $domainRecord = $client->domainRecord();
            $droplet = $client->droplet();
            sleep(10);
            comeBack:
            $droplet123 = $droplet->getById($dropletId);
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


            return true;
        } catch (\Throwable $th) {
            return false;
        }
    });

    Route::get('/moveToRedirectServer/{oldDomainName}', function ($oldDomainName) {


        try {

            $token = Config::get('values.DIGITALOCEAN_ACCESS_TOKEN');
            $client = new Client();
            $client->authenticate($token);
            $domainRecord = $client->domainRecord();
            $redirectServerIp = Config::get('values.REDİRECT_SERVER_IP');

            $digitalocean_nameservers_ipies = ["173.245.58.51", "173.245.59.41", "198.41.222.173"];
            $new_nameservers = ['ns1.' . $oldDomainName, 'ns2.' . $oldDomainName, 'ns3.' . $oldDomainName];
            $domainRecordInfos = $domainRecord->getAll($oldDomainName);
            //CREATE NEW DOMAİN RECORDS
            $domainRecordInfos = $domainRecord->getAll($oldDomainName);

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
            return true;
        } catch (\Throwable $th) {
            return false;
        }
    });

    Route::get('/addNewDroplet/{newDomainName}', function ($newDomainName) {
        $dropletId = 0;
        try {
            $snapshotsDropletId = Config::get('values.SNAPSHOT_ID');
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

    Route::get('/oldDomainNewApacheConfigForRedirect/{oldDomainName}/{newDomainName}', function ($oldDomainName, $newDomainName) {
        $response = false;
        $redirectServerIp = Config::get('values.REDİRECT_SERVER_IP');
        $redirectServerDefaultPassword = Config::get('values.REDİRECT_REDİRECT_SERVER_DEFAULT_PASSWORD');
        $WHICH_MAIL_FOR_SSH_CONNECT_PROBLEM = Config::get('values.WHICH_MAIL_FOR_SSH_CONNECT_PROBLEM');



        $execute_code = 'echo "<VirtualHost *:80>

        ServerAdmin webmaster@localhost
    
        ServerName ' . $oldDomainName . '
    
        ServerAlias www.' . $oldDomainName . '
    
        DocumentRoot /var/www/1xbet-html-page
    
        Redirect / https://' . $newDomainName . '/               
    
        ErrorLog ${APACHE_LOG_DIR}/error.log
    
        CustomLog ${APACHE_LOG_DIR}/access.log combined
    
    </VirtualHost>" >> /etc/apache2/sites-available/' . $oldDomainName . '.conf';



        $ssh = new SSH2($redirectServerIp);
        if (!$ssh->login('root', $redirectServerDefaultPassword)) {
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



        return $response;
    });


    Route::get('/newDomainNewApacheConfigForRedirect/{newDomainName}', function ($newDomainName) {
        $response = false;
        $redirectServerDefaultPassword = Config::get('values.REDİRECT_REDİRECT_SERVER_DEFAULT_PASSWORD');
        $WHICH_MAIL_FOR_SSH_CONNECT_PROBLEM = Config::get('values.WHICH_MAIL_FOR_SSH_CONNECT_PROBLEM');
        $token = Config::get('values.DIGITALOCEAN_ACCESS_TOKEN');
        $client = new Client();
        $client->authenticate($token);
        $droplet = $client->droplet();
        $droplets = $droplet->getAll();
        $newServerIp = 1;
        foreach ($droplets as  $droplet) {
            if ($droplet->name == $newDomainName)
                $newServerIp = $droplet->networks[1]->ipAddress;
        }
        if ($newServerIp == 1)
            exit();





        $execute_code = 'echo "<VirtualHost *:80>

        ServerAdmin webmaster@localhost
    
        ServerName ' . $oldDomainName . '
    
        ServerAlias www.' . $oldDomainName . '
    
        DocumentRoot /var/www/1xbet-html-page
    
        Redirect / https://' . $newDomainName . '/               
    
        ErrorLog ${APACHE_LOG_DIR}/error.log
    
        CustomLog ${APACHE_LOG_DIR}/access.log combined
    
    </VirtualHost>" >> /etc/apache2/sites-available/' . $oldDomainName . '.conf';



        $ssh = new SSH2($newServerIp);
        if (!$ssh->login('root', $redirectServerDefaultPassword)) {
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



        return $response;
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
});



Route::get('/', 'App\Http\Controllers\WelcomeController@welcome')->name('welcome');
Route::get('/terms', 'App\Http\Controllers\TermsController@terms')->name('terms');
Route::get('/un_used_domain_create', 'App\Http\Controllers\DomainController@un_used_domain_create')->name('un_used_domain_create');
Route::post('/un_used_domain_delete', 'App\Http\Controllers\DomainController@un_used_destroy')->name('un_used_domain_delete');
Route::get('/un_used_domain_index', 'App\Http\Controllers\DomainController@un_used_domain_index')->name('un_used_domain_index');
Route::post('/un_used_domain_store', 'App\Http\Controllers\DomainController@un_used_domain_store')->name('un_used_domain_store');


// Authentication Routes
Auth::routes();

// Public Routes
Route::group(['middleware' => ['web', 'activity', 'checkblocked']], function () {

    // Activation Routes
    Route::get('/activate', ['as' => 'activate', 'uses' => 'App\Http\Controllers\Auth\ActivateController@initial']);

    Route::get('/activate/{token}', ['as' => 'authenticated.activate', 'uses' => 'App\Http\Controllers\Auth\ActivateController@activate']);
    Route::get('/activation', ['as' => 'authenticated.activation-resend', 'uses' => 'App\Http\Controllers\Auth\ActivateController@resend']);
    Route::get('/exceeded', ['as' => 'exceeded', 'uses' => 'App\Http\Controllers\Auth\ActivateController@exceeded']);

    // Socialite Register Routes
    Route::get('/social/redirect/{provider}', ['as' => 'social.redirect', 'uses' => 'App\Http\Controllers\Auth\SocialController@getSocialRedirect']);
    Route::get('/social/handle/{provider}', ['as' => 'social.handle', 'uses' => 'App\Http\Controllers\Auth\SocialController@getSocialHandle']);

    // Route to for user to reactivate their user deleted account.
    Route::get('/re-activate/{token}', ['as' => 'user.reactivate', 'uses' => 'App\Http\Controllers\RestoreUserController@userReActivate']);
});

// Registered and Activated User Routes
Route::group(['middleware' => ['auth', 'activated', 'activity', 'checkblocked']], function () {

    // Activation Routes
    Route::get('/activation-required', ['uses' => 'App\Http\Controllers\Auth\ActivateController@activationRequired'])->name('activation-required');
    Route::get('/logout', ['uses' => 'App\Http\Controllers\Auth\LoginController@logout'])->name('logout');
});

// Registered and Activated User Routes
Route::group(['middleware' => ['auth', 'activated', 'activity', 'twostep', 'checkblocked']], function () {

    //  Homepage Route - Redirect based on user role is in controller.
    Route::get('/home', ['as' => 'public.home',   'uses' => 'App\Http\Controllers\UserController@index']);





    // Show users profile - viewable by other users.
    Route::get('profile/{username}', [
        'as'   => '{username}',
        'uses' => 'App\Http\Controllers\ProfilesController@show',
    ]);
});

// Registered, activated, and is current user routes.
Route::group(['middleware' => ['auth', 'activated', 'currentUser', 'activity', 'twostep', 'checkblocked']], function () {



    // User Profile and Account Routes
    Route::resource(
        'profile',
        \App\Http\Controllers\ProfilesController::class,
        [
            'only' => [
                'show',
                'edit',
                'update',
                'create',
            ],
        ]
    );
    Route::put('profile/{username}/updateUserAccount', [
        'as'   => '{username}',
        'uses' => 'App\Http\Controllers\ProfilesController@updateUserAccount',
    ]);
    Route::put('profile/{username}/updateUserPassword', [
        'as'   => '{username}',
        'uses' => 'App\Http\Controllers\ProfilesController@updateUserPassword',
    ]);
    Route::delete('profile/{username}/deleteUserAccount', [
        'as'   => '{username}',
        'uses' => 'App\Http\Controllers\ProfilesController@deleteUserAccount',
    ]);

    // Route to show user avatar
    Route::get('images/profile/{id}/avatar/{image}', [
        'uses' => 'App\Http\Controllers\ProfilesController@userProfileAvatar',
    ]);

    // Route to upload user avatar.
    Route::post('avatar/upload', ['as' => 'avatar.upload', 'uses' => 'App\Http\Controllers\ProfilesController@upload']);
});

// Registered, activated, and is admin routes.
Route::group(['middleware' => ['auth', 'activated', 'role:admin', 'activity', 'twostep', 'checkblocked']], function () {
    Route::resource('/users/deleted', \App\Http\Controllers\SoftDeletesController::class, [
        'only' => [
            'index', 'show', 'update', 'destroy',
        ],
    ]);

    Route::resource('users', \App\Http\Controllers\UsersManagementController::class, [
        'names' => [
            'index'   => 'users',
            'destroy' => 'user.destroy',
        ],
        'except' => [
            'deleted',
        ],
    ]);
    Route::post('search-users', 'App\Http\Controllers\UsersManagementController@search')->name('search-users');

    Route::resource('themes', \App\Http\Controllers\ThemesManagementController::class, [
        'names' => [
            'index'   => 'themes',
            'destroy' => 'themes.destroy',
        ],
    ]);

    Route::get('logs', '\Rap2hpoutre\LaravelLogViewer\LogViewerController@index');
    Route::get('routes', 'App\Http\Controllers\AdminDetailsController@listRoutes');
    Route::get('active-users', 'App\Http\Controllers\AdminDetailsController@activeUsers');
});

Route::redirect('/php', '/phpinfo', 301);
