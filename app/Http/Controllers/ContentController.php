<?php

namespace App\Http\Controllers;

use App\Models\BetCompany;
use App\Models\Category;
use App\Models\Content;
use App\Models\Log;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Yajra\DataTables\Facades\DataTables;

class ContentController extends Controller
{


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Content::select('*');
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    return '  <a href="/contents/' . $row->id . '/edit">
                            <i class="fa fa-pencil fa-fw "></i>
                        </a>';
                })
                ->addColumn('status_text', function ($row) {
                    if ($row->status == 0) {
                        return '<b >Not Rewritered<</b>';
                    } else if ($row->status == 1) {
                        return '<b>Draft</b>';
                    } else if ($row->status == 2) {
                        return '<b>Published</b>';
                    }
                })
                ->filter(function ($instance) use ($request) {
                    if (!empty($request->get('search'))) {
                        $instance->where(function ($w) use ($request) {
                            $search = $request->get('search');
                            $w->orWhere('first_title', 'LIKE', "%$search%")
                                ->orWhere('rewriter_title', 'LIKE', "%$search%")
                                ->orWhere('last_title', 'LIKE', "%$search%");
                        });
                    }
                })

                ->make(true);
        }

        return view('contents.index');
    }




    /*      public function index()
    {
        $contents = Content::all();
        return view('contents.index', compact('contents'))
            ->with('i', (request()->input('page', 1) - 1) * 5);
    }
    **/


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('contents.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {


        $content = Content::create([
            'first_link' => $request->get('first_link'),
            'first_title' => $request->get('first_title'),
            'first_description' => $request->get('first_description'),
            'first_content' => $request->get('first_content'),
            'status' => 0
        ]);

        $content->save();

        return redirect()->route('contents.index')
            ->with('success', 'Content oluşturma başarıyla tamamlandı');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Content  $content
     * @return \Illuminate\Http\Response
     */
    public function show(Content $content)
    {
        return view('contents.show', compact('content'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Content  $content
     * @return \Illuminate\Http\Response
     */
    public function edit(Content $content)
    {
        $bet_companies = BetCompany::all();
        $categories = Category::all();
        $category = Category::where('id', $content->category_id)->get()->first();
        $bet_company = BetCompany::where('id', $content->bet_company_id)->get()->first();

        return view('contents.edit', compact('content', 'categories', 'bet_companies', 'category', 'bet_company'));
    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Content  $content
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Content $content)
    {

        $request->validate([
            'last_link' => 'required',
            'last_title' => 'required',
            'last_description' => 'required',
            'last_content' => 'required',

        ]);
        $content->status = 2;
        $content->save();
        $content->update($request->all());

        return redirect()->route('contents.index')
            ->with('success', 'Content güncellemesi başarıyla tamamlandı');
    }



     /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Content  $content
     * @return \Illuminate\Http\Response
     */
    public function rewriter(Request $request, Content $content)
    {

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


        $bet_companies = BetCompany::all();
        $categories = Category::all();
        $category = Category::where('id', $content->category_id)->get()->first();
        $bet_company = BetCompany::where('id', $content->bet_company_id)->get()->first();

        return view('contents.edit', compact('content', 'categories', 'bet_companies', 'category', 'bet_company'));
    

    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Content  $content
     * @return \Illuminate\Http\Response
     */
    public function destroy(Content $content)
    {
        $content->delete();

        return redirect()->route('contents.index')
            ->with('success', 'Content kaldırma başarıyla tamamlandı');
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
