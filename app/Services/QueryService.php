<?php


namespace App\Services;

use Illuminate\Http\Request;
use App\Rules\CountryExists;
use Illuminate\Support\Facades\DB;
use League;

class QueryService
{
    private const CHART_TYPE = 'chart_type';
    private const COUNT = 'count';
    private const COUNTRY = 'country';
    private const CATEGORY = 'category';
    private const LETTER = 'letter';
    private const LANGUAGE = 'language';
    private const LIMIT = 'limit';
    private const PERCENTAGE = 'percentage';
    private const WORD = 'word';
    private const OPERATOR = 'operator';

    private const POPULARITY_GRAPH = 'popular';
    private const TOTAL_AMOUNT_GRAPH = 'total';
    private const TIME_GRAPH = 'time';

    private const COUNT_ANSWERS = 'answer';
    private const COUNT_CATEGORIES = 'category';

    private const COUNT_COLUMN_NAME = 'amount';

    private const WORD_TABLE_REST = 'word_rest';

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
        $language = $this->request->input(self::LANGUAGE);
        $limit = intval($this->request->input(self::LIMIT));

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

    private function buildWhereSubQuery(string $word_table, string $language, $country, $category, $letter) :string
    {
        if ($country || $category || $letter || $word_table === self::WORD_TABLE_REST) {
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

            if ($word_table === self::WORD_TABLE_REST) {
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

            return $whereQuery;
        }

        return "";
    }


    private function buildAnswerCountQuery(bool $type_popularity): string
    {
        $this->request->validate([
            'country.*' => [new CountryExists]
        ]);

        $language = $this->request->input(self::LANGUAGE);
        $limit = $this->request->input(self::LIMIT);
        $country = $this->request->input(self::COUNTRY);
        $category = strtolower($this->request->input(self::CATEGORY));
        $letter = strtolower($this->request->input(self::LETTER));

        print_r($country);
        $word_table = $this->getWordTableName($language);

        $query = "SELECT ";

        if ($type_popularity) {
            $query.= "LOWER(value) AS word, ";
        } else {
            $query.= "LOWER(category_name) AS category_name, ";
        }

        $query .=
                "COUNT(*) AS ". self::COUNT_COLUMN_NAME . " ".
            "FROM " .
                $word_table . " ";

        $query .= $this->buildWhereSubQuery($word_table, $language, $country, $category, $letter);

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


    private function buildTimeQuery() : string
    {
        $this->request->validate([
            'country' => [new CountryExists]
        ]);

        $language = $this->request->input(self::LANGUAGE);
        $country = $this->request->input(self::COUNTRY);
        $category = strtolower($this->request->input(self::CATEGORY));
        $letter = strtolower($this->request->input(self::LETTER));
        $word = strtolower($this->request->input(self::WORD));
        $operator = $this->request->input(self::OPERATOR);

        $word_table = $this->getWordTableName($language);

        $pattern = $word;
        if ($operator == 'starts') {
            $pattern = '%' . $pattern;
        }
        else if ($operator == 'both') {
            $pattern = '%' . $pattern . '%';
        }

        $query = "
                SELECT
                    DATE(date_cr) AS day,
                    SUM(case when
                        value ILIKE '". $pattern ."'
                        then 1 else 0 end) AS occurrences
                FROM " .
                    $word_table . " ";

        $query .= $this->buildWhereSubQuery($word_table, $language, $country, $category, $letter);

        $query .= "
                GROUP BY
                    day
                ORDER BY
                    day ASC;";

        return $query;
    }

    private function buildPopularityQuery(): string
    {
        $table = $this->request->input(self::COUNT);

        if ($table === self::COUNT_CATEGORIES) {
            return $this->buildCategoryCountQuery();
        } elseif ($table === self::COUNT_ANSWERS) {
            return $this->buildAnswerCountQuery(true);
        } else {
            //TODO redirect in service does not work
            return redirect()->back()->withErrors(['Query build was not successful.']);
        }
    }

    private function build(): string
    {
        //TODO validation
        $type = $this->request->input(self::CHART_TYPE);

        if ($type === self::POPULARITY_GRAPH) {
            return $this->buildPopularityQuery();
        } else if($type === self::TOTAL_AMOUNT_GRAPH) {
            return $this->buildAnswerCountQuery(false);
        } else if($type === self::TIME_GRAPH) {
            return $this->buildTimeQuery();
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
        $totalAmount = $total[0][self::COUNT_COLUMN_NAME];

        foreach ($result as $i => $item) {
            $result[$i][self::COUNT_COLUMN_NAME] /= $totalAmount ;
        }

        return $result;
    }

    public function getResult(): array
    {
        $query = $this->build();
        $this->query = $query;

        $result = $this->execute($query);

        if ($this->request->input(self::CHART_TYPE) === self::POPULARITY_GRAPH
            && $this->request->input(self::COUNT) === self::COUNT_ANSWERS
            && $this->request->input(self::PERCENTAGE)
            && $this->request->input(self::CATEGORY)
        ) {
            $result = $this->changeResultToPercentage($result);
        }

        return $result;
    }

}
