<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Content;

class Cleaner extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'quote:Cleaner';

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
        /*
        bütün sistemde bul değiştir kodu yazılmalı.
        */
        // temizle
        // h ve p lere parçala 
        // desc'i ve içeriği yeniden yazdır.
        // last'a kaydet ve hazır.





        $startId = 0;
        $finishId = 10;
        $contents = Content::where('id', ">=", $startId)->where('id', "<=", $finishId)->where('status', 0)->get();

        foreach ($contents as $key => $value) {
            $last_content = '';
            $content_array =  $this->seperator($value);
            // $responseDescription= $this->writeAgain($$value->first_description);

            foreach ($content_array as $key2 => $value2) {

                $response_content = $this->cleanContentTag($value2[1], "a", false);
                $response_content = strip_tags($response_content);
                $response_content =  $this->cleanSomething($response_content);
                
                $response_content = $this->writeAgain($response_content);




                $responseTitle = $value2[0];
                $last_content = $last_content . $responseTitle . $response_content;
              
            }

            $value->last_content = $last_content;
            $value->save();
        }
    }



    public function seperator($content)
    {
        $content_array = array();
        $new_content = $this->cleanContent($content);
        $new_content =  html_entity_decode($new_content);

        $content->first_content = $new_content;


        $response_array = $this->control_tag_h1($new_content, $content->first_title);
        if ($response_array) {
            array_push($content_array, array($response_array[1], $response_array[2]));
            $new_content = $response_array[0];
        }

        for ($i = 0; $i < 10; $i++) {
            $response_array = $this->control_tag_h_all($new_content, "h2");
            if ($response_array) {
                $new_content = $response_array[0];
                array_push($content_array, array($response_array[1], $response_array[2]));
            }
            $response_array = $this->control_tag_h_all($new_content, "h3");
            if ($response_array) {
                $new_content = $response_array[0];
                array_push($content_array, array($response_array[1], $response_array[2]));
            }

            $response_array = $this->control_tag_h_all($new_content, "h4");

            if ($response_array) {
                $new_content = $response_array[0];
                array_push($content_array, array($response_array[1], $response_array[2]));
            }

            $response_array = $this->control_tag_h_all($new_content, "h5");

            if ($response_array) {
                $new_content = $response_array[0];
                array_push($content_array, array($response_array[1], $response_array[2]));
            }
            $response_array = $this->control_tag_h_all($new_content, "h6");
            if ($response_array) {
                $new_content = $response_array[0];
                array_push($content_array, array($response_array[1], $response_array[2]));
            }
        }
        $last_content = '';

        return $content_array;
    }
    public function writeAgain($content)
    {

        $this->browse(function ($browser) use ($content) {
            dd(5);

            $timerOne = 15000;
            $timerTwo = 25000;

            a:
            $browser->visit('https://aiarticlespinner.co')
                ->pause(3000);
            // write again
            $browser->script('document.getElementById("1").checked = true;');
            $tr_value = "<option value='tr' selected='selected'>Turkish</option>";
            $browser->script('document.getElementById("select-state").innerHTML = "' . $tr_value . '";');
            $browser->value('#inp', $content)
                ->pause(2000);

            //click
            $browser->script('document.getElementById("refase").click();');
            $browser->pause($timerOne);
            $new_content = $browser->value('#out');
            $browser->pause(1000);
            $browser->value('#out', "");
            $browser->pause(2000);
            // remove par
            $browser->script('document.getElementById("3").checked = true;');
            $browser->pause(1000);
            $browser->value('#inp', $new_content)
                ->pause(2000);

            $browser->script('document.getElementById("refase").click();');
            $browser->pause($timerTwo);
            $response  = $browser->value('#out')->pause(2000);
            if ($response == "") {
                if($timerOne==2500){
                    $timerOne = 35000;
                    $timerTwo = 45000;
                }else{
                    $timerOne = 25000;
                    $timerTwo = 35000;
                }

                if($timerOne==35000){
                    return false;
                }
                goto a;
            }

            dd($response);

            return $response;
        });
    }


    public function cleanContent($content)
    {


        $response_content = $this->cleanContentTag($content->first_content, "img", true);
        $response_content = $this->cleanContentTag($response_content, "table", true);
        $response_content = $this->cleanContentTag($response_content, "tbody", true);

        return $response_content;
    }

    public function seperate_content($content, $wp_content)
    {
        $content_array = array();
        $title = $content->first_title;

        $control_h1 = strpos($wp_content, '<h1>');
        $control_h2 = strpos($wp_content, '<h2>');
        $control_h3 = strpos($wp_content, '<h3>');
        $control_h4 = strpos($wp_content, '<h4>');
        $control_h5 = strpos($wp_content, '<h5>');


        // Eger h1,h2,.. hiçbiri yokta bütün sayfa içerik olarak alınıyor 

        array_push($content_array, $this->control_tag_h1($wp_content, $title));






        return $content_array;
    }

    public function control_tag_h1($wp_content, $title = '')
    {
        $hLimit = strpos($wp_content, '<h');
        $control_h1 = strpos($wp_content, '<h1>');
        $h1Limit = strpos($wp_content, '</h1>');
        $response_content = '';
        $response_title = '';
        if ($control_h1) {
            $hLimit  =  strpos($wp_content, '<h', strpos($wp_content, '<h') + 1);
            if ($control_h1 > 100) {
                $response_title = $title;
                if ($hLimit) {
                    $response_content =  substr($wp_content, 0, ($hLimit - 2));
                } else {
                    $response_title = substr($wp_content, $control_h1, $h1Limit);
                    $response_content =  substr($wp_content, $h1Limit, strlen($wp_content));
                }
            } else {
                $response_title = substr($wp_content, $control_h1, $h1Limit);
                $response_content =  substr($wp_content, $control_h1, ($hLimit - 2));
            }
        } else {


            $response_title = $title;
            if ($hLimit) {
                $response_content =  substr($wp_content, 0, ($hLimit - 2));
            } else {
                $response_content =  substr($wp_content, 0, strlen($wp_content));
            }
        }


        $wp_content =    substr($wp_content, strlen($response_content), strlen($wp_content));
        $responseArray = array(
            $wp_content,
            $response_title,
            $response_content
        );

        return $responseArray;
    }

    public function control_tag_h_all($wp_content, $h)
    {


        $control_h1 = strpos($wp_content, '<' . $h . '>');
        $h1Limit = strpos($wp_content, '</' . $h . '>');
        $response_content = '';
        $response_title = '';



        if (!$control_h1 == false) {
            $hLimit  =  strpos($wp_content, '<h', strpos($wp_content, '<h') + 1);
            $response_title = substr($wp_content, $control_h1 + 4, $h1Limit - 4);
            $response_content =  substr($wp_content, $h1Limit + 4, ($hLimit - 2));
        } else {
            return false;
        }
        $wp_content =    substr($wp_content, strlen($response_content), strlen($wp_content));
        $responseArray = array(
            $wp_content,
            $response_title,
            $response_content
        );



        return $responseArray;
    }

    public function cleanContentTag($text, $tag, $type)
    {

        $response_content = $text;
        $tagStart = "<$tag";
        if (!$type)
            $tagEnd = "</$tag>";
        else
            $tagEnd = "/>";
        for ($i = 0; $i <= 10; $i++) {
            $tagStartPos = strpos($response_content, $tagStart);
            $tagEndtPos = strpos($response_content, $tagEnd);
            $endPosition = strlen($response_content);
            if ($tagStartPos) {

                $start_text = substr($response_content, 0, $tagStartPos);
                $tagLenght = 5;
                if ($type)
                    $tagLenght = 2;
                $end_text = substr($response_content, $tagEndtPos + $tagLenght, $endPosition);
                $response_content = $start_text . $end_text;
            }
        }






        return $response_content;
    }

    public function cleanSomething($text)
    {
        $responseText = $text;

        $asd = array(
            'span',
            'h1',
            'h2',
            'h3',
            'h4',
            'h5',
            'h6',
            'h7',
            'h8',
            'strong',
            'p',
            '\n',
            '"'


        );
        foreach ($asd as $key => $value) {
            $responseText = trim($responseText, "/" . $value . ">");
            $responseText = trim($responseText, $value . ">");
            $responseText = trim($responseText, "/" . $value);
            $responseText = trim($responseText, $value);

            $responseText = trim($responseText, "<" . $value);
            $responseText = trim($responseText, "< " . $value);
            $responseText = trim($responseText, $value);
        }



        return $responseText;
    }
}
