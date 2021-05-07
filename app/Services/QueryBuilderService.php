<?php

/**
 * Jméno, Město Analyzer
 * Bachelor's Thesis
 * @author Václav Trampeška
 */

namespace App\Services;

use App\Actions\ExecuteSqlCommandAction;
use App\Constants\QueryConstants;

class QueryBuilderService
{
    /**
     * QueryBuilderService constructor.
     *
     * @param RequestInputService $requestInputService
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
     * @param string|null $language
     * @param string|null $wordTable
     *
     * @return bool
     */
    private function specifyLanguage(?string $language, ?string $wordTable): bool
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
     * Finds identificators of inputted category names
     *
     * @param array<int, string> $categories
     * @param string $language
     *
     * @return array<int>
     */
    private function findCategoryIds(array $categories, string $language): array
    {
        $subQuery = "SELECT id FROM category WHERE (";

        foreach ($categories as $category) {
            $subQuery .= " lower(name) LIKE '". $category ."'";
        }

        $subQuery .= ")";

        // replace each space between conditions to "OR"
        $subQuery = preg_replace("/(?<=')[\s](?!$)/", " OR ", $subQuery);

        if ($language !== QueryConstants::ALL_LANGUAGES) {
            $subQuery .= " AND id_lang = '" . $language. "' ";
        }

        $categoryIds = ExecuteSqlCommandAction::run($subQuery);

        //get only Id fields from array
        $categoryIds = array_column($categoryIds, QueryConstants::ID_COLUMN_NAME);

        return empty($categoryIds) ? [-1] : $categoryIds;
    }


    /**
     * Builds the WHERE part of a query.
     *
     * @param string $language
     * @param string|null $wordTable
     * @param array<int, string>|null $countries
     * @param array<int, string>|null $categories
     * @param string|null $letter
     *
     * @return string
     */
    private function buildWhereSubQuery(
        string $language,
        string $wordTable = null,
        array $countries = null,
        array $categories = null,
        ?string $letter = null
    ) :string {
        $categoryIds = null;
        if (! empty($categories)) {
            $categoryIds = $this->findCategoryIds($categories, $language);
        }

        if (
            $this->specifyLanguage($language, $wordTable)
            || !empty($countries)
            || $categoryIds
            || $letter
        ) {
            $whereQuery = " WHERE ";

            if ($this->specifyLanguage($language, $wordTable)){
                $whereQuery .= "id_lang = '" . $language. "' ";
            }

            if (!empty($countries)) {
                $countryQuery = "(";
                foreach ($countries as $country){
                    $countryQuery .=" country_code = '" . $country . "'";
                }
                $countryQuery .= ") ";

                $whereQuery .= preg_replace("/(?<=')[\s](?!$)/", " OR ", $countryQuery);
            }

            if ($categoryIds) {
                $categoryQuery = "(";
                foreach ($categoryIds as $categoryId){
                    $categoryQuery .= " id_category = '" . $categoryId . "'";
                }
                $categoryQuery .= ") ";

                $whereQuery .= preg_replace("/(?<=')[\s](?!$)/", " OR ", $categoryQuery);
            }

            if ($letter) {
                $whereQuery .= "letter = '" . $letter . "' ";
            }

            // replace each space between WHERE conditions to "AND" unless "OR" is already there
            $whereQuery = preg_replace("/(?<='|'\))[\s](?!$|OR)/", " AND ", $whereQuery);

            return $whereQuery ?? "";
        }

        return "";
    }

    /**
     * builds the FROM part of the query.
     *
     * @param string $language
     *
     * @return string
     */
    private function buildFromSubQuery(string $language): string
    {
        $languageTables = ["word_cs", "word_pt", "word_de", "word_en", "word_fr", "word_es", "word_it", "word_pl", "word_sk"];
        $wordTable = $this->getWordTableName($language);

        $fromQuery = " FROM ";

        if ($language === QueryConstants::ALL_LANGUAGES){
            $fromQuery .= "(";

            foreach ($languageTables as $table) {
                $fromQuery .= "SELECT value, date_cr, id_category, letter, country_code " .
                    "FROM " . $table . " " .
                    "UNION ALL "
                    ;
            }

            $fromQuery .= "SELECT value, date_cr, id_category, letter, country_code " .
                            "FROM word_rest " .
                ") AS word_tables"
            ;
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
        $language = $this->requestInputService->getInputValue(QueryConstants::LANGUAGE_KEY);

        $query =
            "SELECT " .
            "name, " .
            "games_played AS " . QueryConstants::AMOUNT_COLUMN_NAME . " " .
            "FROM " .
            "category ";

        $query .= $this->buildWhereSubQuery($language);

        $query .=
            "ORDER BY " .
            "amount DESC " .
            "LIMIT " . QueryConstants::LIMIT_NUMBER;

        return $query;
    }

    /**
     * Builds a query that returns the most popular answers.
     *
     * @param bool $totalWords
     *
     * @return string
     */
    private function buildAnswerCountQuery(bool $totalWords = false): string
    {
        $countries = $this->requestInputService->getInputValue(QueryConstants::COUNTRY_KEY);
        $categories = $this->requestInputService->getInputValue(QueryConstants::CATEGORY_KEY);
        $language = $this->requestInputService->getInputValue(QueryConstants::LANGUAGE_KEY);
        $letter = $this->requestInputService->getInputValue(QueryConstants::LETTER_KEY);

        $query = "SELECT ";

        if ($totalWords) {
            $query .= "LOWER(category_name) AS category_name, ";
        } else {
            $query .= "LOWER(value) AS word, ";
        }

        $query .=  "COUNT(*) AS " . QueryConstants::AMOUNT_COLUMN_NAME . " ";

        $query .= $this->buildFromSubQuery($language);

        $query .= $this->buildWhereSubQuery(
            $language,
            $this->getWordTableName($language),
            $countries,
            $categories,
            $letter
        );

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
            QueryConstants::AMOUNT_COLUMN_NAME . " DESC";

        return $query;
    }

    /**
     * Builds the SUM part of a time query that counts the amount of specified submitted answers for each day.
     *
     * @param array<int, string> $words
     * @param array<int, string> $operators
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

        return $wordQuery . " then 1 else 0 end) AS " . QueryConstants::AMOUNT_COLUMN_NAME . " ";
    }

    /**
     * Builds a query that returns the amount of specified answers for each day.
     *
     * @return string
     */
    private function buildAnswersInTimeQuery(): string
    {
        $operators = $this->requestInputService->getInputValue(QueryConstants::OPERATOR_KEY);
        $words = $this->requestInputService->getInputValue(QueryConstants::WORD_KEY);
        $countries = $this->requestInputService->getInputValue(QueryConstants::COUNTRY_KEY);
        $categories = $this->requestInputService->getInputValue(QueryConstants::CATEGORY_KEY);
        $letter = $this->requestInputService->getInputValue(QueryConstants::LETTER_KEY);
        $language = $this->requestInputService->getInputValue(QueryConstants::LANGUAGE_KEY);
        $percentage = $this->requestInputService->getInputValue(QueryConstants::PERCENTAGE_KEY);

        $query = "SELECT
                    date_cr AS day, ";

        if ($percentage) {
            $query .= "COUNT(*) AS " . QueryConstants::TOTAL_ANSWERS_COLUMN_NAME . ", ";
        }

        $query .= $this->buildWordComparisonSubQuery($words, $operators);

        $query .= $this->buildFromSubQuery($language);

        $query .= $this->buildWhereSubQuery(
            $language,
            $this->getWordTableName($language),
            $countries,
            $categories,
            $letter
        );

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
        $table = $this->requestInputService->getInputValue(QueryConstants::COUNT_TABLE_KEY);

        if ($table === QueryConstants::COUNT_CATEGORIES) {
            $query = $this->buildCategoryCountQuery();
        } elseif ($table === QueryConstants::COUNT_ANSWERS) {
            $query = $this->buildAnswerCountQuery();
        }

        return $query ?? "";
    }

    /**
     * Builds a query that returns the total amount of answers.
     *
     * @return string
     */
    private function buildTotalAnswersQuery(): string
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

        $type = $this->requestInputService->getInputValue(QueryConstants::GRAPH_TYPE_KEY);

        if ($type === QueryConstants::POPULARITY_GRAPH) {
            $query = $this->buildPopularityQuery();
        } elseif ($type === QueryConstants::TOTAL_AMOUNT_GRAPH) {
            $query = $this->buildTotalAnswersQuery();
        } elseif ($type === QueryConstants::TIME_GRAPH) {
            $query = $this->buildAnswersInTimeQuery();
        }

        return $query ?? "";
    }
}
