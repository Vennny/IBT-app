<?php


namespace App\Services;

use Illuminate\Http\Request;
use App\Rules\CountryExists;
use League;


class QueryService
{

    public $request;

    public function __construct($request)
    {
        $this->request = $request;
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

    private function buildWordCountQuery(bool $type_popularity): string
    {
        $this->request->validate([
            'country' => [new CountryExists]
        ]);

        $language = $this->request->input('language');
        $limit = $this->request->input('limit');
        $country = $this->request->input('country');
        $category = strtolower($this->request->input('category'));
        $letter = strtolower($this->request->input('letter'));

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
            $where_query =
                "WHERE ";

            if ($country) {
                //prevent SQL injection
                $country = preg_replace("/'/", "''", $country);
                $where_query .=
                    "country_code = '".$this->getCountryCode($country)."' ";
            }

            if ($letter) {
                //prevent SQL injection
                $letter = preg_replace("/'/", "''", $letter);
                $where_query = $where_query .
                    "LOWER(value) LIKE '".$letter."%' ";

            }

            if ($word_table === "word_rest") {
                //prevent SQL injection
                $language = preg_replace("/'/", "''", $language);
                $where_query = $where_query .
                    "id_lang = '".$language."' ";
            }

            if ($category) {
                //categories can contain an apostrophe => needs to be escaped in query, also prevents SQL injection
                $category = preg_replace("/'/", "''", $category);

                $where_query = $where_query .
                    "LOWER(category_name) = '".$category."' ";
            }

            // replace each space between WHERE conditions to "AND"
            $where_query = preg_replace("/(?<=')[\s](?!$)/", " AND ", $where_query);

            $query .= $where_query;
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
        } elseif ($table === "word") {
            return $this->buildWordCountQuery(true);
        } else {
            return redirect()->back()->withErrors(['Query build was not successful.']);
        }
    }


    public function buildQuery(): string
    {
        //TODO validation
        $type = $this->request->input('chart_type');

        if ($type === "popular") {
            return $this->buildPopularityQuery();
        } else if($type === "total") {
            return $this->buildWordCountQuery(false);
        }

        return "";
    }
}
