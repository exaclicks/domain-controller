<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Content;
use App\Models\Log;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Config;

class Rewriter extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'quote:rewriter';

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

        return 0 ;
        $contents = Content::where('status', 0)->limit(20)->get();

        foreach ($contents as $key => $content) {
            $request_content = preg_replace("/(<([^>]+)>)/", '', $content->first_content);

            $response = $this->get_new_sentence($request_content);

            if ($response) {
                $content->rewriter_content = $response;
                $content->status =  1;
                $content->save();
            } else {
                $log = new Log();
                $log->type = -1;
                $log->title = "Hata";
                $log->which_worker = "rewriter";
                $log->description = $content->name . " içerikleri yeniden yazarken hata meydana geldi.";
            }
        }
    }

    public function get_new_sentence($text)
    {
        $rewriterApiUrl = Config::get('values.REWRITER_API_URL');
        $rewriterApiToken = Config::get('values.REWRITER_API_TOKEN');

        $curl = curl_init();



        $text = preg_replace('/\s+/u', ' ', $text);

        curl_setopt_array($curl, [
            CURLOPT_URL => $rewriterApiUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 120,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "{
            \"language\": \"tr\",
            \"strength\": 3,
            \"text\": \"$text\"}",
            CURLOPT_HTTPHEADER => [
                "content-type: application/json",
                "x-rapidapi-host: rewriter-paraphraser-text-changer-multi-language.p.rapidapi.com",
                "x-rapidapi-key: $rewriterApiToken"
            ],
        ]);

        $response = curl_exec($curl);


        $json = json_decode($response);

        $err = curl_error($curl);
        curl_close($curl);
        try {
            return $json->rewrite;
        } catch (\Throwable $th) {
            return false;
        }
    }
}
