<?php

namespace App\Console\Commands;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendMail;
use App\Models\Domain;
use Dapphp\TorUtils\ControlClient;
use Dapphp\TorUtils\TorCurlWrapper;

class DailyQuote extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'quote:everyMinute';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Respectively send an exclusive quote to everyone daily via email.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }  

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {


// list of country codes to use
$countries = array('tr');

// get new control client for connecting to Tor's control port
$tc = new ControlClient();

$tc->connect(); // connect
$html='';
foreach($countries as $country) {
    $country = '{' . $country . '}'; // e.g. {US}

    $tc->setConf(array('ExitNodes' => $country)); // set config to use exit node from country

    // get new curl wrapped through Tor SOCKS5 proxy
    $curl = new TorCurlWrapper();
    $curl->setopt(CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:41.0) Gecko/20100101 Firefox 41.0');

    // make request - should go through exit node from specified country
    if ($curl->httpGet('http://iensta.com')) {
        $html= $curl->getResponseBody();
    }
}


    Mail::raw($html, function ($mail) {
        $mail->from('ex@exaclicks.com');
        $mail->to("mrbulut@exaclicks.com")
            ->subject("test");
    });


    exit();
        $this->info('Successfully sent daily quote to everyone.');
       
        $domains = Domain::all();

        foreach ($domains as $domain) {
            $opts = [
                "http" => [
                    "method" => "GET",
                    "header" => "Accept-language: tr\r\n" .
                        "Cookie: foo=bar\r\n"
                ]
            ];
            
            // DOCS: https://www.php.net/manual/en/function.stream-context-create.php
            $context = stream_context_create($opts);
            $html = file_get_contents($domain->name, false, $context);


            $search_word= "The requested URL could not be retrieved";
            $isBanned = strpos($html, $search_word);

            if($domain->status==0){
                Mail::raw($html, function ($mail) use ($domain) {
                    $mail->from('ex@exaclicks.com');
                    $mail->to("mrbulut@exaclicks.com")
                        ->subject($domain->name);
                });
            }
           

         /*     if(isset($isBanned) && $domain->status==0){

                $domain->status = 1;
                $domain->save();

                Mail::raw($domain->name ." engellendi.", function ($mail) use ($domain) {
                    $mail->from('digamber@positronx.com');
                    $mail->to("muzaffer652@gmail.com")
                        ->subject($domain->name);
                });
            }  */


          
        }

      
        $this->info('Successfully sent daily quote to everyone.');
    }
}