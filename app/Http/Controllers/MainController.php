<?php

namespace App\Http\Controllers;

use App\Models\Lang;
use App\Models\Main;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;

class MainController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|Factory|View|\Illuminate\Http\Response
     */
    public function index()
    {
        return view('main.index', ['langs' => lang::orderBy('show_name')->get()]);
    }

    /**
     * Parse user query and execute it
     *
     * @return \Illuminate\Http\Response
     */

    public function buildQuery(Request $request)
    {
        //
    }
}
