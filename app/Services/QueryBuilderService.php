<?php


namespace App\Services;

use App\Constants\QueryConstants;
use Illuminate\Http\Request;
use App\Rules\CountryExists;
use JetBrains\PhpStorm\Pure;
use League;

class QueryBuilderService
{
    /**
     * QueryBuilderService constructor.
     * @param $request
     */
    public function __construct(private Request $request){}

    public function getRequest(): Request
    {
        return $this->request;
    }

    private function getInputValue(string $input): mixed
    {
        return match ($input) {
            QueryConstants::GRAPH_TYPE => $this->request->input(QueryConstants::GRAPH_TYPE),
            QueryConstants::COUNT => $this->request->input(QueryConstants::COUNT),
            QueryConstants::COUNTRY => array_filter($this->request->input(QueryConstants::COUNTRY)),
            QueryConstants::CATEGORY =>  strtolower($this->request->input(QueryConstants::CATEGORY)),
            QueryConstants::LANGUAGE => $this->request->input(QueryConstants::LANGUAGE),
            QueryConstants::OPERATOR => array_filter($this->request->input(QueryConstants::OPERATOR)),
            QueryConstants::WORD => array_map('strtolower', array_filter($this->request->input(QueryConstants::WORD))),
            QueryConstants::LETTER => $this->request->input(QueryConstants::LETTER),
            QueryConstants::LIMIT => intval($this->request->input(QueryConstants::LIMIT))
        };
    }

    private function getCountryCode(string $name): string
    {
        $country = (new League\ISO3166\ISO3166)->name($name);
        return $country['alpha2'];
    }


    private function validate(array $inputs): void
    {
        foreach ($inputs as $input) {
            match ($input) {
                QueryConstants::GRAPH_TYPE => $this->request->validate([QueryConstants::GRAPH_TYPE => 'required|string']),
                QueryConstants::COUNT => $this->request->validate([QueryConstants::COUNT => 'required']),
                QueryConstants::CATEGORY => $this->request->validate([QueryConstants::CATEGORY => 'required']),
                QueryConstants::LANGUAGE => $this->request->validate([QueryConstants::LANGUAGE => 'required']),
                QueryConstants::OPERATOR => $this->request->validate([QueryConstants::OPERATOR => 'required']),
                QueryConstants::WORD => $this->request->validate([QueryConstants::WORD => 'required']),
                QueryConstants::LETTER => $this->request->validate([QueryConstants::LETTER => 'required']),
                QueryConstants::LIMIT => $this->request->validate([QueryConstants::LIMIT => 'required']),

                QueryConstants::COUNTRY => $this->request->validate([
                                                    QueryConstants::COUNTRY => 'required|array',
                                                    QueryConstants::COUNTRY.'.*' => [new CountryExists]
                                                ]),
            };
        }

    }

    private function getWordTableName(string $language): string
    {
        return match ($language) {
            "cs" => "word_cs",
            "de" => "word_de",
            "en" => "word_en",
            "es" => "word_es",
            "fr" => "word_fr",
            "it" => "word_it",
            "pl" => "word_pl",
            "pt" => "word_pt",
            "sk" => "word_sk",
            "all" => "all",
            default => "word_rest",
        };
    }

    private function specifyLanguage($language, $wordTable): bool
    {
        if (
            ($language && $language !== QueryConstants::ALL_LANGUAGES)
            && (! $wordTable || $wordTable === QueryConstants::WORD_TABLE_REST)
        ){
            return true;
        }

        return false;
    }

    private function buildWhereSubQuery(
        string|null $language,
        array $countries = null,
        string|null $category = null,
        string|null $letter = null
    ) :string {
        $wordTable = $this->getWordTableName($language);

        if (
            $this->specifyLanguage($language, $wordTable)
            || !empty($countries)
            || $category
            || $letter
        ) {
            $whereQuery = " WHERE ";

            if ($this->specifyLanguage($language, $wordTable)){
                $whereQuery .= "id_lang = '".$language."' ";
            }

            if (!empty($countries)) {
                $countryQuery = "";
                foreach ($countries as $country){
                    $countryQuery .=
                        "country_code = '".$this->getCountryCode($country)."' ";
                }

                $whereQuery .= preg_replace("/(?<=')[\s](?!$)/", " OR ", $countryQuery);
            }

            if ($category) {
                $whereQuery .= "LOWER(category_name) = '".$category."' ";
            }

            if ($letter) {
                $whereQuery .= "LOWER(value) LIKE '".strtolower ($letter)."%' ";

            }

            // replace each space between WHERE conditions to "AND" unless "OR" is already there
            return preg_replace("/(?<=')[\s](?!$|OR)/", " AND ", $whereQuery);
        }

        return "";
    }

    private function buildFromSubQuery(string|null $language): string
    {
        $language_tables = ["word_cs", "word_pt", "word_de", "word_en", "word_fr", "word_es", "word_it", "word_pl", "word_sk"];
        $wordTable = $this->getWordTableName($language);

        $fromQuery = " FROM ";

        if ($language === QueryConstants::ALL_LANGUAGES){
            $fromQuery .= "(";

            foreach ($language_tables as $table) {
                $fromQuery .= "SELECT * FROM " . $table . "
                            UNION ALL ";
            }

            $fromQuery .= "SELECT id, value, date_cr, category_name, id_category, country_code
                            FROM word_rest
                        ) AS words_tables ";
        } else {
            $fromQuery .= $wordTable . " ";
        }

        return $fromQuery;
    }

    private function buildCategoryCountQuery(): string
    {
        $language = $this->getInputValue(QueryConstants::LANGUAGE);
        $limit = $this->getInputValue(QueryConstants::LIMIT);

        $query =
            "SELECT " .
            "name, " .
            "games_played AS amount " .
            "FROM " .
            "category ";

        $query .= $this->buildWhereSubQuery($language);

        $query .=
            "ORDER BY " .
            "amount DESC " .
            "LIMIT " .
            $limit;

        return $query;
    }

    private function buildAnswerCountQuery(bool $totalWords = null): string
    {
        $this->request->validate([
            'country.*' => [new CountryExists]
        ]);

        $countries = $this->getInputValue(QueryConstants::COUNTRY);
        $category = $this->getInputValue(QueryConstants::CATEGORY);
        $language = $this->getInputValue(QueryConstants::LANGUAGE);
        $letter = $this->getInputValue(QueryConstants::LETTER);
        $limit = $this->getInputValue(QueryConstants::LIMIT);

        $query = "SELECT ";

        if ($totalWords) {
            $query.= "LOWER(category_name) AS category_name, ";
        } else {
            $query.= "LOWER(value) AS word, ";
        }

        $query .=  "COUNT(*) AS ". QueryConstants::COUNT_COLUMN_NAME . " ";

        $query .= $this->buildFromSubQuery($language);

        $query .= $this->buildWhereSubQuery($language, $countries, $category, $letter);

        if ($totalWords) {
            $query .=
                "GROUP BY ".
                "category_name ";
        } else {
            $query .=
                "GROUP BY ".
                "word ";
        }

        $query .=
            "ORDER BY " .
            "amount DESC " .
            "LIMIT ".$limit;

        return $query;
    }

    private function buildWordComparisonSubQuery(array $words, array $operators): string
    {
        $wordQuery = "SUM(case when ";

        foreach ($words as $key => $word) {
            $pattern = $word;

            if ($operators[$key] === QueryConstants::OPERATOR_STARTS_WITH) {
                $pattern = $pattern . '%';
            } elseif ($operators[$key] === QueryConstants::OPERATOR_ENDS_WITH) {
                $pattern = '%' . $pattern;
            } elseif ($operators[$key] === QueryConstants::OPERATOR_CONTAINS) {
                $pattern = '%' . $pattern . '%';
            }

            $wordQuery .= " LOWER(value) LIKE '" . $pattern ."' ";
        }


        // replace each space conditions to "OR"
        $wordQuery = preg_replace("/(?<=')[\s](?!$)/", " OR ", $wordQuery);

        return $wordQuery . " then 1 else 0 end) AS amount ";
    }

    private function buildAnswersInTimeQuery(): string
    {
        $this->request->validate([
            'country.*' => [new CountryExists]
        ]);

        $operators = $this->getInputValue(QueryConstants::OPERATOR);
        $words = $this->getInputValue(QueryConstants::WORD);
        $countries = $this->getInputValue(QueryConstants::COUNTRY);
        $category = $this->getInputValue(QueryConstants::CATEGORY);
        $letter = $this->getInputValue(QueryConstants::LETTER);
        $language = $this->getInputValue(QueryConstants::LANGUAGE);

        $query = "SELECT
                    DATE(date_cr) AS day,";

        $query .= $this->buildWordComparisonSubQuery($words, $operators);

        $query .= $this->buildFromSubQuery($language);

        $query .= $this->buildWhereSubQuery($language, $countries, $category, $letter);

        $query .= "
                GROUP BY
                    day
                ORDER BY
                    day ASC;";

        return $query;
    }

    private function buildPopularityQuery(): string
    {
        $table = $this->getInputValue(QueryConstants::COUNT);

        if ($table === QueryConstants::COUNT_CATEGORIES) {
            return $this->buildCategoryCountQuery();
        } elseif ($table === QueryConstants::COUNT_ANSWERS) {
            return $this->buildAnswerCountQuery();
        } else {
            //TODO redirect in service does not work
            return redirect()->back()->withErrors(['Query build was not successful.']);
        }
    }

    public function buildTotalAnswersInTimeQuery(): string
    {
        $language = $this->getInputValue(QueryConstants::LANGUAGE);

        $wordTable = $this->getWordTableName($language);

        $query = "SELECT
                        DATE(date_cr) AS day,
                        COUNT(*) AS amount ";

        $query .= $this->buildFromSubQuery($language);

        $query .= $this->buildWhereSubQuery($language);

        $query .= "GROUP BY
                        day
                    ORDER BY
                        day ASC;";

        return $query;
    }

    public function buildTotalAnswersQuery(): string
    {
        return $this->buildAnswerCountQuery(true);
    }

    private function escapeSingleQuotesInInputs(): void
    {
        foreach ($this->request as $key => $input) {
            if (is_string($input)){
                $this->request[$key] = preg_replace("/'/", "''", $input);
            }
        }
    }

    public function build(): string
    {
        //TODO validation

        $this->escapeSingleQuotesInInputs();

        $type = $this->getInputValue(QueryConstants::GRAPH_TYPE);

        $query = "";
        if ($type === QueryConstants::POPULARITY_GRAPH) {
            $query = $this->buildPopularityQuery();
        } elseif ($type === QueryConstants::TOTAL_AMOUNT_GRAPH) {
            $query = $this->buildTotalAnswersQuery();
        } elseif ($type === QueryConstants::TIME_GRAPH) {
            $query = $this->buildAnswersInTimeQuery();
        }

        return $query;
    }
}
