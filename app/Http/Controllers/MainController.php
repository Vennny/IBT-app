<?php

/**
 * Jméno, Město Analyzer
 * Bachelor's Thesis
 * @author Václav Trampeška
 */

namespace App\Http\Controllers;

use App\Actions\GetGameLanguagesAction;
use App\Actions\GetIsoCountriesAction;
use App\Services\RequestHandlerService;
use App\Services\RequestInputService;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use App\Services\QueryBuilderService;


class MainController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return View
     */
    public function index(): View
    {
        $countries = GetIsoCountriesAction::run();
        $languages = GetGameLanguagesAction::run();

        return view('main.index', [
            'countries' => $countries,
            'languages' => $languages
        ]);
    }

    /**
     * Pass request and get query results.
     *
     * @param Request $request
     * @return  View
     */
    public function handleRequest(Request $request): View
    {
        $requestInputService = new RequestInputService($request);
        $requestHandlerService = new RequestHandlerService(new QueryBuilderService($requestInputService), $requestInputService);

        $queryResult = $requestHandlerService->handle();
        $query = $requestHandlerService->getQuery();
        $filteredRequest = $requestHandlerService->getFilteredRequest();

        return view('main.graph', [
            'data' => $queryResult,
            'request' => $filteredRequest,
            'query' => $query
        ]);
    }
}
