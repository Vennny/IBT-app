<?php


namespace App\Services;

use App\Constants\QueryConstants;

class QueryBuilderService
{
    /**
     * QueryBuilderService constructor.
     *
     * @param $requestInputService
     */
    public function __construct(private RequestInputService $requestInputService){}

    /**
     * Finds a matching word table name for a language code.
     *
     * @param string $languageCode
     *
     * @return string
     */
    private function getWordTableName(string $languageCode): string
    {
        return match ($languageCode) {
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

    /**
     * Checks whether a WHERE subquery should specify a language setting.
     *
     * @param $language
     * @param $wordTable
     *
     * @return bool
     */
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

    /**
     * Builds the WHERE part of a query.
     *
     * @param string|null $language
     * @param array|null $countries
     * @param array|null $categories
     * @param string|null $letter
     *
     * @return string
     */
    private function buildWhereSubQuery(
        string|null $language,
        array $countries = null,
        array $categories = null,
        string|null $letter = null
    ) :string {
        $wordTable = $this->getWordTableName($language);

        if (
            $this->specifyLanguage($language, $wordTable)
            || !empty($countries)
            || $categories
            || $letter
        ) {
            $whereQuery = " WHERE ";

            if ($this->specifyLanguage($language, $wordTable)){
                $whereQuery .= "id_lang = '" . $language. "' ";
            }

            if (!empty($countries)) {
                $countryQuery = "(";
                foreach ($countries as $country){
                    $countryQuery .=" country_code = '" . $this->requestInputService->getCountryCode($country) . "'";
                }
                $countryQuery .= ") ";

                $whereQuery .= preg_replace("/(?<=')[\s](?!$)/", " OR ", $countryQuery);
            }

            if (!empty($categories)) {
                $categoryQuery = "(";
                foreach ($categories as $category){
                    $categoryQuery .= " LOWER(category_name) = '" . $category . "'";
                }
                $categoryQuery .= ") ";

                $whereQuery .= preg_replace("/(?<=')[\s](?!$)/", " OR ", $categoryQuery);
            }

            if ($letter) {
                $whereQuery .= "LOWER(value) LIKE '" . strtolower ($letter). "%' ";

            }

            // replace each space between WHERE conditions to "AND" unless "OR" is already there
            return preg_replace("/(?<='|'\))[\s](?!$|OR)/", " AND ", $whereQuery);
        }

        return "";
    }

    /**
     * builds the FROM part of the query.
     *
     * @param string|null $language
     * @return string
     */
    private function buildFromSubQuery(string|null $language): string
    {
        $languageTables = ["word_cs", "word_pt", "word_de", "word_en", "word_fr", "word_es", "word_it", "word_pl", "word_sk"];
        $wordTable = $this->getWordTableName($language);

        $fromQuery = " FROM ";

        if ($language === QueryConstants::ALL_LANGUAGES){
            $fromQuery .= "(";

            foreach ($languageTables as $table) {
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

    /**
     * Builds a query that returns the most played categories.
     *
     * @return string
     */
    private function buildCategoryCountQuery(): string
    {
        $language = $this->requestInputService->getInputValue(QueryConstants::LANGUAGE);
        $limit = $this->requestInputService->getInputValue(QueryConstants::LIMIT);

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

    /**
     * Builds a query that returns the most popular answers.
     *
     * @param bool|null $totalWords
     *
     * @return string
     */
    private function buildAnswerCountQuery(bool $totalWords = null): string
    {
        $countries = $this->requestInputService->getInputValue(QueryConstants::COUNTRY);
        $category = $this->requestInputService->getInputValue(QueryConstants::CATEGORY);
        $language = $this->requestInputService->getInputValue(QueryConstants::LANGUAGE);
        $letter = $this->requestInputService->getInputValue(QueryConstants::LETTER);
        $limit = $this->requestInputService->getInputValue(QueryConstants::LIMIT);

        $query = "SELECT ";

        if ($totalWords) {
            $query .= "LOWER(category_name) AS category_name, ";
        } else {
            $query .= "LOWER(value) AS word, ";
        }

        $query .=  "COUNT(*) AS " . QueryConstants::COUNT_COLUMN_NAME . " ";

        $query .= $this->buildFromSubQuery($language);

        $query .= $this->buildWhereSubQuery($language, $countries, $category, $letter);

        if ($totalWords) {
            $query .=
                "GROUP BY " .
                "category_name ";
        } else {
            $query .=
                "GROUP BY " .
                "word ";
        }

        $query .=
            "ORDER BY " .
            "amount DESC " .
            "LIMIT " . $limit;

        return $query;
    }

    /**
     * Builds the SUM part of a time query that counts the amount of specified submitted answers for each day.
     *
     * @param array $words
     * @param array $operators
     * @return string
     */
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

            $wordQuery .= " LOWER(value) LIKE '" . $pattern . "' ";
        }


        // replace each space conditions to "OR"
        $wordQuery = preg_replace("/(?<=')[\s](?!$)/", " OR ", $wordQuery);

        return $wordQuery . " then 1 else 0 end) AS amount ";
    }

    /**
     * Builds a query that returns the amount of specified answers for each day.
     *
     * @return string
     */
    private function buildAnswersInTimeQuery(): string
    {
        $operators = $this->requestInputService->getInputValue(QueryConstants::OPERATOR);
        $words = $this->requestInputService->getInputValue(QueryConstants::WORD);
        $countries = $this->requestInputService->getInputValue(QueryConstants::COUNTRY);
        $category = $this->requestInputService->getInputValue(QueryConstants::CATEGORY);
        $letter = $this->requestInputService->getInputValue(QueryConstants::LETTER);
        $language = $this->requestInputService->getInputValue(QueryConstants::LANGUAGE);

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

    /**
     * Builds a category or answer popularity query.
     *
     * @return string
     */
    private function buildPopularityQuery(): string
    {
        $table = $this->requestInputService->getInputValue(QueryConstants::COUNT_TABLE);

        if ($table === QueryConstants::COUNT_CATEGORIES) {
            return $this->buildCategoryCountQuery();
        } elseif ($table === QueryConstants::COUNT_ANSWERS) {
            return $this->buildAnswerCountQuery();
        } else {
            return "";
        }
    }

    /**
     * Builds a query that returns the total amount of answers for each day.
     *
     * @return string
     */
    public function buildTotalAnswersInTimeQuery(): string
    {
        $language = $this->requestInputService->getInputValue(QueryConstants::LANGUAGE);
        $countries = $this->requestInputService->getInputValue(QueryConstants::COUNTRY);
        $categories = $this->requestInputService->getInputValue(QueryConstants::CATEGORY);
        $letter = $this->requestInputService->getInputValue(QueryConstants::LETTER);

        $query = "SELECT
                        DATE(date_cr) AS day,
                        COUNT(*) AS amount ";

        $query .= $this->buildFromSubQuery($language);

        $query .= $this->buildWhereSubQuery($language, $countries, $categories, $letter);

        $query .= "GROUP BY
                        day
                    ORDER BY
                        day ASC;";

        return $query;
    }

    /**
     * Builds a query that returns the total amount of answers.
     *
     * @return string
     */
    public function buildTotalAnswersQuery(): string
    {
        return $this->buildAnswerCountQuery(true);
    }

    /**
     * Builds a query.
     *
     * @return string
     */
    public function build(): string
    {
        $this->requestInputService->escapeSingleQuotesInInputs();

        $type = $this->requestInputService->getInputValue(QueryConstants::GRAPH_TYPE);

        if ($type === QueryConstants::POPULARITY_GRAPH) {
            $query = $this->buildPopularityQuery();
        } elseif ($type === QueryConstants::TOTAL_AMOUNT_GRAPH) {
            $query = $this->buildTotalAnswersQuery();
        } elseif ($type === QueryConstants::TIME_GRAPH) {
            $query = $this->buildAnswersInTimeQuery();
        } else {
            $query = "";
        }

        return $query;
    }
}
