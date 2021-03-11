<?php


namespace App\Services;

use App\Constants\QueryConstants;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RequestHandlerService
{
    private string $query;

    private array $filteredRequest;

    /**
     * QueryService constructor.
     * @param QueryBuilderService $queryBuilderService
     * @param RequestInputService $requestInputService
     */
    public function __construct(
        private QueryBuilderService $queryBuilderService,
        private RequestInputService $requestInputService
    ){}

    public function getQuery(): string
    {
        return $this->query;
    }

    public function getFilteredRequest(): array
    {
        return $this->filteredRequest;
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

    private function changeResultToPercentage(array $result): array
    {
        if (
            $this->requestInputService->getInputValue(QueryConstants::GRAPH_TYPE) === QueryConstants::POPULARITY_GRAPH
            && $this->requestInputService->getInputValue(QueryConstants::COUNT_TABLE) === QueryConstants::COUNT_ANSWERS
            && $this->requestInputService->getInputValue(QueryConstants::CATEGORY)
        ) {
            $total = $this->execute($this->queryBuilderService->buildTotalAnswersQuery());
            $totalAmount = $total[0][QueryConstants::COUNT_COLUMN_NAME] ?? 1;

            foreach ($result as $i => $item) {
                $result[$i][QueryConstants::COUNT_COLUMN_NAME] /= $totalAmount;
            }

        } elseif ($this->requestInputService->getInputValue(QueryConstants::GRAPH_TYPE) === QueryConstants::TIME_GRAPH) {
            $words = $this->execute($this->queryBuilderService->buildTotalAnswersInTimeQuery());

            foreach ($result as $i => $item) {
                $result[$i][QueryConstants::COUNT_COLUMN_NAME] /= $words[$i][QueryConstants::COUNT_COLUMN_NAME];
            }
        }

        return $result;
    }

    private function filterRequest(Request $request): array
    {
        //remove first input(token) and all empty inputs
        $filteredRequest = array_filter(array_slice((array)$request->all(),1));

        if (array_key_exists('operator', $filteredRequest)){
            array_pop($filteredRequest['operator']);
        }

        return $filteredRequest;
    }

    public function handle(): array
    {
        $this->query = $this->queryBuilderService->build();

        $request = $this->requestInputService->getRequest();

        $this->filteredRequest = $this->filterRequest($request);

        $queryResult = $this->execute($this->query);

        if ($this->requestInputService->getInputValue(QueryConstants::PERCENTAGE)) {
            $queryResult = $this->changeResultToPercentage($queryResult);
        }

        return $queryResult;
    }

}
