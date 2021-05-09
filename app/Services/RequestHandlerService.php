<?php

/**
 * Jméno, Město Analyzer
 * Bachelor's Thesis
 * @author Václav Trampeška
 */

namespace App\Services;

use App\Actions\ExecuteSqlCommandAction;
use App\Constants\QueryConstants;
use Illuminate\Http\Request;

class RequestHandlerService
{
    /**
     * @var string
     */
    private string $query;

    /**
     * @var array<string, mixed>
     */
    private array $filteredRequest;

    /**
     * QueryService constructor.
     *
     * @param QueryBuilderService $queryBuilderService
     * @param RequestInputService $requestInputService
     */
    public function __construct(
        private QueryBuilderService $queryBuilderService,
        private RequestInputService $requestInputService
    ){}

    /**
     * @return string
     */
    public function getQuery(): string
    {
        return $this->query;
    }

    /**
     * @return array<string, mixed>
     */
    public function getFilteredRequest(): array
    {
        return $this->filteredRequest;
    }

    /**
     * Executes a query.
     *
     * @return array<int, array<string, int>>
     */
    private function executeQuery(): array
    {
        if ($this->query){
            $result = ExecuteSqlCommandAction::run($this->query);

            $json = json_encode($result);
            return $json ? json_decode($json, true) : array();
        }

        return array();
    }

    private function changeCategoryIdsToNames(array $results): array
    {
        foreach ($results as $key => $result) {
            $query = "SELECT name FROM category WHERE id='" . $result[QueryConstants::CATEGORY_COLUMN_NAME] . "'";
            $queryResult = ExecuteSqlCommandAction::run($query);
            $results[$key][QueryConstants::CATEGORY_COLUMN_NAME] = $queryResult[0]->name;
        }

        return $results;
    }

    /**
     * Changes results to percentage out of all related answers.
     *
     * @param array<int, array<string, mixed>> $result
     *
     * @return array<int, array<string, mixed>>
     */
    private function changeResultToPercentage(array $result): array
    {
        if (
            $this->requestInputService->getInputValue(QueryConstants::GRAPH_TYPE_KEY) === QueryConstants::POPULARITY_GRAPH
            && $this->requestInputService->getInputValue(QueryConstants::COUNT_TABLE_KEY) === QueryConstants::COUNT_ANSWERS
        ) {
            $sum = 0;
            array_walk_recursive($result, function($value) use (&$sum) {
                if (is_numeric($value)){
                    $sum += $value;
                }
            });

            $limit = $this->requestInputService->getInputValue(QueryConstants::LIMIT_KEY);

            array_splice($result, $limit);

            foreach ($result as $i => $item) {
                $result[$i][QueryConstants::AMOUNT_COLUMN_NAME] /= $sum;
            }
        } elseif ($this->requestInputService->getInputValue(QueryConstants::GRAPH_TYPE_KEY) === QueryConstants::TIME_GRAPH) {
            foreach ($result as $i => $item) {
                $result[$i][QueryConstants::AMOUNT_COLUMN_NAME] /= $result[$i][QueryConstants::TOTAL_ANSWERS_COLUMN_NAME];
                unset($result[$i][QueryConstants::TOTAL_ANSWERS_COLUMN_NAME]);
            }
        }

        return $result;
    }

    /**
     * Filters empty and unnecessary items in request to show.
     *
     * @param Request $request
     *
     */
    private function filterRequest(Request $request)
    {
        //remove first input(token) and all empty inputs
        $filteredRequest = array_filter(array_slice((array)$request->all(), 1));

        if (array_key_exists(QueryConstants::OPERATOR_KEY, $filteredRequest)){
            array_pop($filteredRequest[QueryConstants::OPERATOR_KEY]);
        }

        $this->filteredRequest = $filteredRequest;
    }

    /**
     * shortens the array by inputted limit and changes to percentage if necessary
     *
     * @param array<int, array<string, mixed>> $result
     *
     * @return array<int, array<string, mixed>>
     */
    private function prepareResults(array $result): array
    {
        if ($this->requestInputService->getInputValue(QueryConstants::PERCENTAGE_KEY)) {
            return $this->changeResultToPercentage($result);
        }

        $graphType = $this->requestInputService->getInputValue(QueryConstants::GRAPH_TYPE_KEY);
        if ($graphType !== QueryConstants::TIME_GRAPH) {
            $limit = $this->requestInputService->getInputValue(QueryConstants::LIMIT_KEY);
            array_splice($result, $limit);

            if ($graphType === QueryConstants::TOTAL_AMOUNT_GRAPH) {
                $result = $this->changeCategoryIdsToNames($result);
            }
        }

        return $result;
    }

    /**
     * Handles request and returns query results.
     *
     * @return array<int, array<string, int>>
     */
    public function handle(): array
    {
        $request = $this->requestInputService->getRequest();
        $this->filterRequest($request);

        $this->query = $this->queryBuilderService->build();
        $queryResult = $this->executeQuery();

        return $this->prepareResults($queryResult);
    }
}
