<?php

namespace App\Http\Controllers;

use App\Models\Lang;
use App\Models\Main;
use App\Rules\CountryExists;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use League;


class MainController extends Controller
{
    private function getCountryCode(string $name){

            $country = (new League\ISO3166\ISO3166)->name($name);
            return $country['alpha2'];
    }

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

    private function buildCategoryPopularityQuery($request): string
    {
        $language = $request->input('language');
        $limit = intval($request->input('limit'));

        $query =
            "SELECT " .
                "name, " .
                "games_played AS amount " .
            "FROM " .
                "category ";

        if ($language) {
            $query .=
                "WHERE " .
                    "id_lang = '" . $language . "' ";
        }

        $query .=
            "ORDER BY " .
                "amount DESC " .
            "LIMIT " .
                $limit;

        return $query;
    }

    private function getWordTableName($language): string
    {
        switch ($language){
            case "cs":
               return "word_cs";
            case "de":
               return "word_de";
            case "en":
               return "word_en";
            case "es":
               return "word_es";
            case "fr":
               return "word_fr";
            case "it":
               return "word_it";
            case "pl":
               return "word_pl";
            case "pt":
               return "word_pt";
            case "sk":
               return "word_sk";
            default:
               return "word_rest";
        }
    }

    private function buildWordPopularityQuery($request): string
    {
        $language = $request->input('language');
        $limit = $request->input('limit');
        $country = $request->input('country');
        $category = strtolower($request->input('category'));
        $letter = strtolower($request->input('letter'));

        $table = $this->getWordTableName($language);

        $query =
            "SELECT " .
                "LOWER(value) AS word, " .
                "COUNT(*) AS amount " .
            "FROM " .
                $table . " ";


        if ($country || $category || $letter || $table === "word_rest") {
            $where_query =
                "WHERE ";

            if ($country) {
                $where_query .=
                    "country_code = '".$this->getCountryCode($country)."' ";
            }

            if ($letter) {
                $where_query = $where_query .
                    "LOWER(value) LIKE '".$letter."%' ";

            }

            if ($table === "word_rest") {
                $where_query = $where_query .
                    "id_lang = '".$language."' ";
            }

            if ($category) {
                //categories can contain an apostrophe => needs to be escaped in query
                $category = preg_replace("/'/", "''", $category);

                $where_query = $where_query .
                    "LOWER(category_name) = '".$category."' ";
            }


            // replace each space between WHERE conditions to "AND"
            $where_query = preg_replace("/(?<=')[\s](?!$)/", " AND ", $where_query);

            $query .= $where_query;
        }

        $query .=
            "GROUP BY ".
                "word " .
            "ORDER BY " .
                "amount DESC " .
            "LIMIT ".$limit;

        return $query;
    }

    private function buildPopularityQuery(Request $request): string
    {
        $table = $request->input('count');

        if ($table === "category") {
            return $this->buildCategoryPopularityQuery($request);
        } elseif ($table === "word") {
            return $this->buildWordPopularityQuery($request);
        } else {
            return redirect()->back()->withErrors(['Query build was not successful.']);
        }
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Foundation\Application|Factory|View|\Illuminate\Http\Response
     */
    public function buildQuery(Request $request)
    {
        $this->validate($request, [
            'chart_type' => 'required',
            'count' => 'required',
            'country' => [new CountryExists],
            'language' => 'required',
            'limit' => 'required|integer|min:1'
        ]);

        if($request->input('chart_type') === "popular"){
            $query = $this->buildPopularityQuery($request);
        }


        print_r($query);

        $results = DB::select( DB::raw($query));

        //$results = DB::table('game')->select(DB::raw('id_lang, count(*) as games'))->groupBy('id_lang')->orderBy('games', 'desc')->limit(10)->get();

        return view('main.graph', [ 'results' => $results]);
    }

}
