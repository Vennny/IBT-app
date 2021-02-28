<?php


namespace App\Services;

use Illuminate\Http\Request;
use App\Rules\CountryExists;
use Illuminate\Support\Facades\DB;
use League;


class QueryService
{

    private $request;

    private $query;

    /**
     * QueryService constructor.
     * @param $request
     */
    public function __construct($request)
    {
        $this->request = $request;
    }

    public function getQuery(): string
    {
        return $this->query;
    }

    private function getCountryCode(string $name): string
    {
        $country = (new League\ISO3166\ISO3166)->name($name);
        return $country['alpha2'];
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

    private function buildCategoryCountQuery(): string
    {
        $language = $this->request->input('language');
        $limit = intval($this->request->input('limit'));

        $query =
            "SELECT " .
            "name, " .
            "games_played AS amount " .
            "FROM " .
            "category ";

        if ($language) {
            //prevent SQL injection
            $language = preg_replace("/'/", "''", $language);
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

    private function buildAnswerCountQuery(bool $type_popularity): string
    {
        $this->request->validate([
            'country' => [new CountryExists]
        ]);

        $language = $this->request->input('language');
        $limit = $this->request->input('limit');
        $country = $this->request->input('country');
        $category = strtolower($this->request->input('category'));
        $letter = strtolower($this->request->input('letter'));
        $percentage = $this->request->input('percentage');

        $word_table = $this->getWordTableName($language);

        $query = "SELECT ";

        if ($type_popularity) {
            $query.= "LOWER(value) AS word, ";
        } else {
            $query.= "LOWER(category_name) AS category_name, ";
        }

        $query .=
                "COUNT(*) AS amount " .
            "FROM " .
                $word_table . " ";

        if ($country || $category || $letter || $word_table === "word_rest") {
            $whereQuery =
                "WHERE ";

            if ($country) {
                //prevent SQL injection
                $country = preg_replace("/'/", "''", $country);
                $whereQuery .=
                    "country_code = '".$this->getCountryCode($country)."' ";
            }

            if ($letter) {
                //prevent SQL injection
                $letter = preg_replace("/'/", "''", $letter);
                $whereQuery = $whereQuery .
                    "LOWER(value) LIKE '".$letter."%' ";

            }

            if ($word_table === "word_rest") {
                //prevent SQL injection
                $language = preg_replace("/'/", "''", $language);
                $whereQuery = $whereQuery .
                    "id_lang = '".$language."' ";
            }

            if ($category) {
                //categories can contain an apostrophe => needs to be escaped in query, also prevents SQL injection
                $category = preg_replace("/'/", "''", $category);

                $whereQuery = $whereQuery .
                    "LOWER(category_name) = '".$category."' ";
            }

            // replace each space between WHERE conditions to "AND"
            $whereQuery = preg_replace("/(?<=')[\s](?!$)/", " AND ", $whereQuery);

            $query .= $whereQuery;
        }

        if ($type_popularity) {
            $query .=
                "GROUP BY ".
                    "word ";
        } else {
            $query .=
                "GROUP BY ".
                    "category_name ";
        }

        $query .=
            "ORDER BY " .
                "amount DESC " .
            "LIMIT ".$limit;

        return $query;
    }

    private function buildPopularityQuery(): string
    {
        $table = $this->request->input('count');

        if ($table === "category") {
            return $this->buildCategoryCountQuery();
        } elseif ($table === "answer") {
            return $this->buildAnswerCountQuery(true);
        } else {
            //TODO redirect in service does not work
            return redirect()->back()->withErrors(['Query build was not successful.']);
        }
    }


    private function build(): string
    {
        //TODO validation
        $type = $this->request->input('chart_type');

        if ($type === "popular") {
            return $this->buildPopularityQuery();
        } else if($type === "total") {
            return $this->buildAnswerCountQuery(false);
        }

        return "";
    }

    private function execute($query): array
    {
        if ($query){
            $result = DB::select( DB::raw($query));
            return json_decode(json_encode($result), true);
        } else {
            return array();
        }
    }

    private function changeResultToPercentage($result): array
    {
        $total = $this->execute($this->buildAnswerCountQuery(false));
        $totalAmount = $total[0]["amount"];

        foreach ($result as $i => $item) {
            $result[$i]["amount"] /= $totalAmount ;
        }

        return $result;
    }

    public function getResult(): array
    {
        $query = $this->build();
        $this->query = $query;

        $result = $this->execute($query);

        if ($this->request->input('chart_type') === "popular"
            && $this->request->input('count') === "answer"
            && $this->request->input('percentage')
            && $this->request->input('category')
        ) {
            $result = $this->changeResultToPercentage($result);
        }

        return $result;
    }

}
