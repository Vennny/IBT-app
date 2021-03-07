<?php


namespace App\Services;

use App\Constants\QueryConstants;
use Illuminate\Http\Request;
use App\Rules\CountryExists;
use League;

class QueryBuilderService
{
    private Request $request;

    /**
     * QueryService constructor.
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    private function getChartTypeInput(): string
    {
        return $this->request->input(QueryConstants::CHART_TYPE);
    }

    private function getCountTableInput(): string
    {
        return $this->request->input(QueryConstants::COUNT);
    }

    private function getCountriesInput(): array
    {
        return array_filter($this->request->input(QueryConstants::COUNTRY));
    }

    private function getCategoryInput(): string|null
    {
        return strtolower($this->request->input(QueryConstants::CATEGORY));
    }

    private function getLanguageInput(): string
    {
        return $this->request->input(QueryConstants::LANGUAGE);
    }

    private function getOperatorInput(): string
    {
        return $this->request->input(QueryConstants::OPERATOR);
    }

    private function getWordInput(): string
    {
        return strtolower($this->request->input(QueryConstants::WORD));
    }

    private function getLetterInput(): string|null
    {
        return $this->request->input(QueryConstants::LETTER);
    }

    private function getLimitInput(): int
    {
        return intval($this->request->input(QueryConstants::LIMIT));
    }

    private function getCountryCode(string $name): string
    {
        $country = (new League\ISO3166\ISO3166)->name($name);
        return $country['alpha2'];
    }

    private function validate(array $inputs): void
    {

    }

    private function getWordTableName(string $language): string
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
        $language = $this->getLanguageInput();
        $limit = $this->getLimitInput();

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

    private function buildWhereSubQuery(
        string $word_table,
        string $language,
        array $countries,
        string|null $category,
        string|null $letter
    ) :string
    {
        if (
            !empty($countries)
            || $category
            || $letter
            || $word_table === QueryConstants::WORD_TABLE_REST
        ) {
            $whereQuery =
                "WHERE ";

            if (!empty($countries)) {
                $countryQuery = "";
                foreach ($countries as $country){
                    //prevent SQL injection
                    $country = preg_replace("/'/", "''", $country);
                    $countryQuery .=
                        "country_code = '".$this->getCountryCode($country)."' ";
                }

                $whereQuery .= preg_replace("/(?<=')[\s](?!$)/", " OR ", $countryQuery);

            }

            if ($letter) {
                //prevent SQL injection
                $letter = preg_replace("/'/", "''", $letter);
                $whereQuery = $whereQuery .
                    "LOWER(value) LIKE '".$letter."%' ";

            }

            if ($word_table === QueryConstants::WORD_TABLE_REST) {
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
            $whereQuery = preg_replace("/(?<=')[\s](?!$|OR)/", " AND ", $whereQuery);

            return $whereQuery;
        }

        return "";
    }


    private function buildAnswerCountQuery(bool $type_popularity): string
    {
        $this->request->validate([
            'country.*' => [new CountryExists]
        ]);

        $countries = $this->getCountriesInput();
        $category = $this->getCategoryInput();
        $language = $this->getLanguageInput();
        $letter = $this->getLetterInput();
        $limit = $this->getLimitInput();

        $word_table = $this->getWordTableName($language);

        $query = "SELECT ";

        if ($type_popularity) {
            $query.= "LOWER(value) AS word, ";
        } else {
            $query.= "LOWER(category_name) AS category_name, ";
        }

        $query .=
            "COUNT(*) AS ". QueryConstants::COUNT_COLUMN_NAME . " ".
            "FROM " .
            $word_table . " ";

        $query .= $this->buildWhereSubQuery($word_table, $language, $countries, $category, $letter);

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
            'country.*' => [new CountryExists]
        ]);

        $operator = $this->getOperatorInput();
        $word = $this->getWordInput();
        $countries = $this->getCountriesInput();
        $category = $this->getCategoryInput();
        $letter = $this->getLetterInput();
        $language = $this->getLanguageInput();

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

        $query .= $this->buildWhereSubQuery($word_table, $language, $countries, $category, $letter);

        $query .= "
                GROUP BY
                    day
                ORDER BY
                    day ASC;";

        return $query;
    }

    private function buildPopularityQuery(): string
    {
        $table = $this->getCountTableInput();

        if ($table === QueryConstants::COUNT_CATEGORIES) {
            return $this->buildCategoryCountQuery();
        } elseif ($table === QueryConstants::COUNT_ANSWERS) {
            return $this->buildAnswerCountQuery(true);
        } else {
            //TODO redirect in service does not work
            return redirect()->back()->withErrors(['Query build was not successful.']);
        }
    }

    public function buildTotalAnswersQuery(): string
    {
        return $this->buildAnswerCountQuery(false);
    }

    public function build(): string
    {
        //TODO validation
        $type = $this->getChartTypeInput();

        $query = "";
        if ($type === QueryConstants::POPULARITY_GRAPH) {
            $query = $this->buildPopularityQuery();
        } else if($type === QueryConstants::TOTAL_AMOUNT_GRAPH) {
            $query = $this->buildTotalAnswersQuery();
        } else if($type === QueryConstants::TIME_GRAPH) {
            $query = $this->buildTimeQuery();
        }

        return $query;
    }
}
