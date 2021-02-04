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

        return view('main.index', ['countries' => $countries]);
    }

    private function buildPopularityQuery(Request $request): string
    {
        $count = explode(",",$request->input('count')); //two values are received from the form
        $country = $request->input('country') ?: "all";
        $language = $request->input('language');
        $limit = intval($request->input('limit'));
        $category = intval($request->input('category'));

        //get table name and column name from an array of two values
        $table = $count[0];
        $column_name = $count[1];

        if($table === "category"){
            $query =
                "SELECT
                ".$column_name.", ".
                "games_played AS amount " .
                "FROM " .
                $table." ";
        } else {
            $query =
                "SELECT
                ".$column_name.", ".
                "COUNT(*) AS amount " .
                "FROM " .
                $table." ";
        }


        if($country !== "all" || $language !== "all" || $category){
            $where_q = "WHERE ";

            if($language !== "all"){
                $where_q = $where_q .
                    "id_lang = '".$language."' ";
            }

            if($table === "word_detailed"){
                if($country !== "all"){
                    $where_q = $where_q .
                        "country_code = '".$this->getCountryCode($country)."' ";
                }

                if($category){
                    $where_q = $where_q .
                        "category_name = '".$category."' ";
                }
            }

            $where_q = preg_replace("/(?<=')[\s](?!$)/", " AND ", $where_q);

            $query = $query . $where_q;
        }

        if($table !== "category"){
            $query = $query .
                "GROUP BY
                ". $column_name. " ";
        }

        $query = $query .
            "ORDER BY
                amount DESC ".
            "LIMIT ".$limit;

        return $query;
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Foundation\Application|Factory|View|\Illuminate\Http\Response
     */
    public function store(Request $request)
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
     /*   $results = DB::select( DB::raw("
            SELECT
                id_lang,
                COUNT(*) AS '$column_name'
            FROM
                '$table'
            GROUP BY
                id_lang
            ORDER BY '$column_name' DESC
            LIMIT '$limit'
        "));*/

      /*  $results = DB::select( DB::raw("
            SELECT
                id_lang,
                COUNT(*) AS games
            FROM
                game
            GROUP BY
                id_lang
            ORDER BY games DESC
            LIMIT 10
        "));*/

        /*     if ($type == '0'){

                $results = DB::select( DB::raw("
                SELECT
                    id_lang,
                    COUNT(*) AS games
                FROM
                    '$search_table'
                GROUP BY
                    id_lang
                ORDER BY games DESC
                LIMIT '$limit'
            "));
            }
            else{
                $results = 0;
            }*/

        //$results = DB::table('game')->select(DB::raw('id_lang, count(*) as games'))->groupBy('id_lang')->orderBy('games', 'desc')->limit(10)->get();

        return view('main.graph', [ 'results' => $results]);
    }

}
