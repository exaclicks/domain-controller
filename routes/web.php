<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DomainController;
use GuzzleHttp\Client;
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




Route::get('/yeniicerik', function () {

    $text = "Firma'nın yeni üyelere özel olarak düzenlediği ilk üyelik bonusu kampanyası oldukça cazip. Bonustan üye olduktan sonra ilk para yatırma işleminde yararlanılıyor. Bu nedenle yatırım yaparken bonus alacaksanız eğer maksimum tutarda almanızı tavsiye ederiz.

    Yeni üyelere özel olan ilk üyelik bonusu tam olarak 1.000 TL ve yatırılan paranın %200'ü oranında veriliyor. Yani 500 TL yatırım yaptığınızda 1.000 TL bonus alabiliyorsunuz. En az 10 Tl yatırarak da bu bonustan yararlanabiliyorsunuz.. Alınan bonus spor bahislerinde, casino oyunlarında ve sanal bahislerde kullanılabiliyor.
    
    İlk kez yatırım yapanlara özel bir başka bonus ise hoşgeldin bonusu paketidir. Bu pakette ise 1.500 Euro ve 150 Freespin alma imkanı var. En az 10 Euro yatırarak bu kampanyadan yararlanmak mümkün. İlk dört yatırıma özel olarak verilmektedir. Düzenli yatırım yapan ve yüksek tutarda bonus almak isteyenler için kaçırılmayacak bir fırsat!";

    $client = new Client();

    $level = "good"; // new sentence;
    $level2 = "one"; // fixgrammer;
    $dest = "tr";


    $api = "https://aiarticlespinner.co/frontend/rewritenow";
    $response = $client->request('POST',   $api, [
        'user-agent'             => "Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.164 Mobile Safari/537.36",
        'origin'          => "https://aiarticlespinner.co",
        'referer'         => "https://aiarticlespinner.co/",
        'protocols'       => ['http', 'https'],
        "content-type" =>"application/x-www-form-urlencoded; charset=UTF-8",
       
        "cookie" =>'G_ENABLED_IDPS=google; __gads=ID=1241ef94ed69b331-22a90a199bc900f5:T=1628531273:RT=1628531273:S=ALNI_MaODW7PCPWdtyvVcpf28oXcLAmrDw; G_AUTHUSER_H=0; ci_session=a:9:{s:10:"session_id";s:32:"f4b32b053a61a36a262c0120f2852e4f";s:10:"ip_address";s:14:"195.158.85.225";s:10:"user_agent";s:120:"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.164 Safari/537.3";s:13:"last_activity";i:1628536719;s:9:"user_data";s:0:"";s:6:"userid";s:3:"252";s:8:"username";s:14:"Muzaffer Bulut";s:5:"email";s:17:"miksmtr@gmail.com";s:10:"is_premium";s:1:"0";}e0992d630b58a458f267f4dbcd5aaa48; FCCDCF=[null,null,["[[],[],[],[],null,null,true]",1628536741290],null,null]',
        'form_params' => [
            'text' => $text,
            'level' => $level,
            'dest' => $dest
        ]
    ]);
    print_r($response); // OK




});



Route::get('/', function () {


   


  

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

return view('welcome');
exit();




    if ($html != '') {
        $isMoved = strpos($html, $moved_text);
        if ($isMoved!='') {
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
