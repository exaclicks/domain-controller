<?php
namespace App\Http\Controllers;

use App\Models\Domain;
use Illuminate\Http\Request;

class DomainController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $domains = Domain::orderByDesc('status')->where('used',1)->get();
       
        return view('domains.index', compact('domains'))
            ->with('i', (request()->input('page', 1) - 1) * 5);
    }

        /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function un_used_domain_index()
    {
        $domains = Domain::orderByDesc('status')->where('used',0)->get();
        return view('domains.un_used_domain_index', compact('domains'))
            ->with('i', (request()->input('page', 1) - 1) * 5);
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('domains.create');
    }

      /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function un_used_domain_create()
    {
        return view('domains.un_used_domain_create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'hosting' => 'required',
        ]);

        $domain = Domain::create([
            'name' => $request->get('name'),
            'hosting' => $request->get('hosting'),
            'start_time' => $request->get('start_time'),
            'finish_time' => $request->get('finish_time'),
            'status' => 0,  
            'used' => 1,
        ]);

        $domain->save();

        return redirect()->route('domains.index')
            ->with('success', 'Domain oluşturma başarıyla tamamlandı');
    }

        /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function un_used_domain_store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'hosting' => 'required',
        ]);

        $domain = Domain::create([
            'name' => $request->get('name'),
            'hosting' => $request->get('hosting'),
            'start_time' => $request->get('start_time'),
            'finish_time' => $request->get('finish_time'),
            'status' => 0,  
            'used' => 0,
        ]);

        $domain->save();

        return redirect()->route('un_used_domain_index')
            ->with('success', 'Domain oluşturma başarıyla tamamlandı');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Domain  $domain
     * @return \Illuminate\Http\Response
     */
    public function show(Domain $domain)
    {
        return view('domains.show', compact('domain'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Domain  $domain
     * @return \Illuminate\Http\Response
     */
    public function edit(Domain $domain)
    {
        return view('domains.edit', compact('domain'));
    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Domain  $domain
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Domain $domain)
    {
        $request->validate([
            'name' => 'required',
            'hosting' => 'required',
        ]);


        $domain->update($request->all());

        return redirect()->route('domains.index')
            ->with('success', 'Domain güncellemesi başarıyla tamamlandı');
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Domain  $domain
     * @return \Illuminate\Http\Response
     */
    public function destroy(Domain $domain)
    {
        $domain->delete();

        return redirect()->route('domains.index')
            ->with('success', 'Domain kaldırma başarıyla tamamlandı');
    }

  /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Domain  $domain
     * @return \Illuminate\Http\Response
     */
    public function un_used_destroy(Request $request,Domain $domain)
    {
        echo $domain->id."<br>";
        echo $request;
        exit();
        $domain->delete();

        return redirect()->route('un_used_domain_index')
            ->with('success', 'Domain kaldırma başarıyla tamamlandı');
    }
}
