<?php

namespace App\Http\Controllers;

use App\Models\Lang;
use App\Models\Main;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;

class MainController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|Factory|View|\Illuminate\Http\Response
     */
    public function index()
    {
        return view('main.index');
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Foundation\Application|Factory|View|\Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $results = DB::select( DB::raw("
            SELECT
                id_lang,
                COUNT(*) AS games
            FROM
                game
            GROUP BY
                id_lang
            ORDER BY games DESC
        "));




        return view('main.graph', [ 'results' => $results]);
    }

}
