<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendMail;
use App\Models\Domain;
use phpseclib3\Net\SSH2;

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




       // status -1 ise sorun var kodda sorun var.

       // status 0 ise çalışıyor

       // status 1 ise banlandı

       // status 2 ise taşındı.

        $ssh = new SSH2('5.2.82.44');
        if (!$ssh->login('root', 'g#bpyOrvjt')) {
            $this->info('ssh connection failed');
        }

        $moved_text = "The document has moved ";
        $isMoved = "";


        $domains = Domain::all();

        foreach ($domains as $domain) {
            $status = -1;
            $link = $domain->name;
            $html = $ssh->exec('curl -s -H "Proxy-Connection: keep-alive"  -H "Cache-Control: max-age=0"   -H "Upgrade-Insecure-Requests: 1" -H "User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/92.0.4515.131 Safari/537.36" -H "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9"  -H "Accept-Language: tr-TR,tr;q=0.9,tr;q=0.8" ' . $link);


            if ($html != '') {
                $isMoved = strpos($html, $moved_text);
                if ($isMoved!='') {
                    $status = 2;
                } else {
                    $status = 0;
                }
            } else {
                $status = 1;
            }
            if ($domain->status != 1) {
                $domain->status = $status;
                $domain->save();


                if($status == 1){
                    Mail::raw($domain->name . " engellendi. <br> " . $html, function ($mail) use ($domain) {
                        $mail->from('ex@exaclicks.com');
                        $mail->to("mrbulut@exaclicks.com")
                            ->subject($domain->name);
                    });
                }
            
            }
        }


    }
}
