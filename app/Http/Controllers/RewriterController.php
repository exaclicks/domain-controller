<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class RewriterController extends Controller
{
 
   

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function get_new_sentence(Request $request)
    {
        $rewriterApiUrl = Config::get('values.REWRITER_API_URL');
        $rewriterApiToken = Config::get('values.REWRITER_API_TOKEN');

        
        $request->validate([
            'text' => 'required',
        ]);

        $text= $request->get('text');

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $rewriterApiUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
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
        $err = curl_error($curl);
        
        curl_close($curl);
        
        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            echo $response;
        }

        
        return 0;
    }

    
  
}
