<?php

namespace App\Http\Controllers;

use App\Services\RequestHandlerService;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use App\Services\QueryBuilderService;
use League;


class MainController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return View
     */
    public function index(): View
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
     * @param Request $request
     * @return View
     */
    public function handleRequest(Request $request): View
    {
        $requestHandlerService = (new RequestHandlerService(new QueryBuilderService($request)));

        $results = $requestHandlerService->handle();
        $query = $requestHandlerService->getQuery();
        $filteredRequest = $requestHandlerService->getFilteredRequest();

        return view('main.graph', [
            'results' => $results,
            'request' => $filteredRequest,
            'query' => $query,
            'percentage' => array_key_exists('percentage', $filteredRequest)
        ]);
    }

}
