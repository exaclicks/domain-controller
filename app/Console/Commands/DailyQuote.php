<?php

namespace App\Console\Commands;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendMail;
use App\Models\Domain;

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
        $this->info('Successfully sent daily quote to everyone.');
       
        $domains = Domain::all();

        foreach ($domains as $domain) {

            $html = file_get_contents($domain->name);

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