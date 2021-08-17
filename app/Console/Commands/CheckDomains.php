<?php

namespace App\Console\Commands;

use App\Models\Domain;
use Illuminate\Console\Command;
use Illuminate\Http\Request as HttpRequest;

class CheckDomains extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'quete:checkDomains';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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


        $bannedDomains = Domain::where('domain_status',1)->where('used',1)->where('status',3)->get();
        $unUsedDomains= Domain::where('domain_status',0)->where('used',0)->where('status',0)->get();
        foreach ($bannedDomains as $key => $bannedDomain) {
           $oldDomainName = $bannedDomain->name;
           $newDomainName = $unUsedDomains->first()->name;
          
          
          
           $responseMoveToDomain = HttpRequest::create('/moveToNewDomain/'.$oldDomainName.'/'.$newDomainName , 'GET');
           $responseNewDomainNewApacheConfigForRedirect = HttpRequest::create('/newDomainNewApacheConfigForRedirect/'.$oldDomainName .'/'.$newDomainName, 'GET');
           $responseMoveToRedirectServer = HttpRequest::create('/moveToRedirectServer/'.$oldDomainName, 'GET'); 
           $responseOldDomainNewApacheConfigForRedirect = HttpRequest::create('/oldDomainNewApacheConfigForRedirect/'.$oldDomainName .'/'.$newDomainName, 'GET');
  
          
         
           
        }
        return 0;
    }
}
