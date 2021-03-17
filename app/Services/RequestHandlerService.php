<?php


namespace App\Services;

use App\Constants\QueryConstants;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
     * @param string $query
     *
     * @return array<int, array>
     */
    private function execute(string $query): array
    {
        if ($query){
            $result = DB::select(DB::raw($query));

            $json = json_encode($result);
            return $json ? json_decode($json, true) : array();
        }

        return array();
    }

    /**
     * Changes results to percentage out of all related answers.
     *
     * @param array<int, array> $result
     *
     * @return array<int, array>
     */
    private function changeResultToPercentage(array $result): array
    {
        if (
            $this->requestInputService->getInputValue(QueryConstants::GRAPH_TYPE) === QueryConstants::POPULARITY_GRAPH
            && $this->requestInputService->getInputValue(QueryConstants::COUNT_TABLE) === QueryConstants::COUNT_ANSWERS
        ) {
            $sum = 0;
            array_walk_recursive($result, function($value) use (&$sum) {
                if (is_numeric($value)) $sum += $value;
            });

            $limit = $this->requestInputService->getInputValue(QueryConstants::LIMIT);

            array_splice($result, $limit);

            foreach ($result as $i => $item) {
                $result[$i][QueryConstants::COUNT_COLUMN_NAME] /= $sum;
            }

        } elseif ($this->requestInputService->getInputValue(QueryConstants::GRAPH_TYPE) === QueryConstants::TIME_GRAPH) {
            $words = $this->execute($this->queryBuilderService->buildTotalAnswersInTimeQuery());

            foreach ($result as $i => $item) {
                $result[$i][QueryConstants::COUNT_COLUMN_NAME] /= $words[$i][QueryConstants::COUNT_COLUMN_NAME];
            }
        }

        return $result;
    }

    /**
     * Filters empty and unnecessary items in request to show.
     *
     * @param Request $request
     *
     * @return array<string, mixed>
     */
    private function filterRequest(Request $request): array
    {
        //remove first input(token) and all empty inputs
        $filteredRequest = array_filter(array_slice((array)$request->all(), 1));

        if (array_key_exists('operator', $filteredRequest)){
            array_pop($filteredRequest['operator']);
        }

        return $filteredRequest;
    }

    /**
     * shortens the array by input limit and changes to percentage if necessary
     *
     * @param array<int, array> $result
     *
     * @return array<int, array>
     */
    private function prepareResult(array $result): array
    {
        if ($this->requestInputService->getInputValue(QueryConstants::PERCENTAGE)) {
            return $this->changeResultToPercentage($result);
        }

        if ($this->requestInputService->getInputValue(QueryConstants::GRAPH_TYPE) !== QueryConstants::TIME_GRAPH) {
            $limit = $this->requestInputService->getInputValue(QueryConstants::LIMIT);
            return array_splice($result, $limit);
        }

        return $result;
    }

    /**
     * Handles request and returns query results.
     *
     * @return array<int, array>
     */
    public function handle(): array
    {
        $this->query = $this->queryBuilderService->build();

        $request = $this->requestInputService->getRequest();

        $this->filteredRequest = $this->filterRequest($request);

        $queryResult = $this->execute($this->query);

        return $this->prepareResult($queryResult);
    }
}
