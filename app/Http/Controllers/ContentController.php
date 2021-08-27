<?php
namespace App\Http\Controllers;

use App\Models\BetCompany;
use App\Models\Category;
use App\Models\Content;
use Illuminate\Http\Request;

class ContentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $contents = Content::limit(50)->orderBy('created_at')->get();
        return view('contents.index', compact('contents'))
            ->with('i', (request()->input('page', 1) - 1) * 5);
    }

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
            'status'=> 0
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
        $test = 5;
        $bet_companies = BetCompany::all();
        $categories = Category::all();
        $category = Category::where('id',$content->category_id)->get()->first();
        $bet_company = BetCompany::where('id',$content->bet_company_id)->get()->first();

        return view('contents.edit', compact('content','categories','bet_companies','category','bet_company'));
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
        $content->status=2;
        $content->save();        
        $content->update($request->all());

        return redirect()->route('contents.index')
            ->with('success', 'Content güncellemesi başarıyla tamamlandı');
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
}
