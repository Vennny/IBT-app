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
        $queryService = new queryService($request);

        $results = $queryService->getResult();

        $query = $queryService->getQuery();

        //remove first input and all empty inputs
        $requestValues = array_filter(array_slice($request->all(),1));

        return view('main.graph', [
            'results' => $results,
            'request' => $requestValues,
            'query' => $query
        ]);
    }

}
