<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DomainController;


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

Route::get('/', function () {


    return view('welcome');
    exit();

    $moved_text = "The document has moved ";

    $ssh = new SSH2('5.2.82.44');
    if (!$ssh->login('root', 'g#bpyOrvjt')) {
        $this->info('ssh connection failed');
    }


    //$link = "https://alittihad.ae"; //türkiyeye banlanmış yönlendirilmemiş site;
    $link = "http://www.atletismogalego.com/"; //türkiyeye banlanmış yönlendirilmiş site;
    $link = "https://ulams.com/1xbet.html"; // türkiyeye açık site;
    $html = $ssh->exec('curl -s -H "Proxy-Connection: keep-alive"  -H "Cache-Control: max-age=0"   -H "Upgrade-Insecure-Requests: 1" -H "User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/92.0.4515.131 Safari/537.36" -H "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9"  -H "Accept-Language: tr-TR,tr;q=0.9,tr;q=0.8" ' . $link);







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
