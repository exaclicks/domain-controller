<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendMail;
use App\Models\BannedList;
use App\Models\Content;
use App\Models\Domain;
use App\Models\Log;
use App\Models\Website;
use phpseclib3\Net\SSH2;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;

class WebsitePicker extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'quote:websitePicker';

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
        $websites = Website::where('status', 0)->get();
        $part = "/wp-json/wp/v2/posts/";
        $category_part = "/wp-json/wp/v2/categories";
        foreach ($websites as $key => $website) {
            $log = new Log();
            $log->type = 0;
            $log->title = "Başarılı";
            $log->which_worker = "websitePicker";
            $log->description = $website->link . " içerikleri çekilmeye başlandı.";

            $rest_api_link_category_part = $website->link . $category_part;
            $categories = null;

            //KATEGORİLER ÇEKİLDİ
            $curlSession = curl_init();
            curl_setopt($curlSession, CURLOPT_URL, $rest_api_link_category_part);
            curl_setopt($curlSession, CURLOPT_RETURNTRANSFER, true);
            $jsonData = json_decode(curl_exec($curlSession));
            curl_close($curlSession);
            if (!isset($jsonData->data->status))
                $categories = $jsonData;
            ////


            try {
                for ($i = 1; $i < 1500; $i++) {
                    $post_id = $i;
                    $rest_api_link = $website->link . $part . $post_id;
                    $curlSession = curl_init();
                    curl_setopt($curlSession, CURLOPT_URL, $rest_api_link);
                    curl_setopt($curlSession, CURLOPT_RETURNTRANSFER, true);
                    $jsonData = json_decode(curl_exec($curlSession));

                    curl_close($curlSession);
                    if (!isset($jsonData->data->status)) {
                        $link = $jsonData->slug;
                        $title = $jsonData->title->rendered;
                        $wp_content = $jsonData->content->rendered;
                        $description = $jsonData->excerpt->rendered;
                        $category  = '';
                        if (isset($jsonData->categories[0])) {
                            $cat_id = $jsonData->categories[0];
                            foreach ($categories as $key => $value) {
                                if ($value->id == $cat_id)
                                    $category = $value->name;
                            }
                        }
                        $content = new Content();
                        $content->first_link = $link;
                        $content->first_title = $title;
                        $content->first_description = $description;
                        $content->first_content = $wp_content;
                        $content->first_category = $category;
                        $content->rewriter_title = $title;
                        $content->rewriter_description =  $description;
                        $content->save();
                    }
                }

                $log = new Log();
                $log->type = 0;
                $log->title = "Başarılı";
                $log->which_worker = "websitePicker";
                $log->description = $website->link . " içerikleri çekildi işlem tamamlandı.";
                $website->status = 1;
                $website->save();
            } catch (\Throwable $th) {

                $log = new Log();
                $log->type = -1;
                $log->title = "Hata";
                $log->which_worker = "websitePicker";
                $log->description = $website->link . " içerikleri çekerken hata meydana geldi.";
                $website->status = -1;
                $website->save();
                $log->save();
                //Eski kayıtlarıda siliver 
                Content::where("website_id",$website->id)->delete();

            }
        }
    }
}
