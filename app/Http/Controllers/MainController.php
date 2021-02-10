<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use App\Services\QueryService;
use League;


class MainController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|Factory|View|\Illuminate\Http\Response
     */
    public function index()
    {
        $countries = (new League\ISO3166\ISO3166);
        $languages = DB::table('lang')->orderBy('date_cr', 'ASC')->get();

        return view('main.index', [
            'countries' => $countries,
            'languages' => $languages
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Foundation\Application|Factory|View|\Illuminate\Http\Response
     */
    public function handleRequest(Request $request)
    {
        $query = (new queryService($request))->buildQuery();

        print_r($query);

        $results = DB::select( DB::raw($query));

        //$results = DB::table('game')->select(DB::raw('id_lang, count(*) as games'))->groupBy('id_lang')->orderBy('games', 'desc')->limit(10)->get();

        return view('main.graph', [ 'results' => $results]);
    }

}
