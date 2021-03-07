<?php


namespace App\Services;


use App\Constants\QueryConstants;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RequestHandlerService
{
    private QueryBuilderService $queryBuilderService;

    private string $query;

    private array $filteredRequest;

    /**
     * QueryService constructor.
     * @param $queryBuilderService
     */
    public function __construct($queryBuilderService)
    {
        $this->queryBuilderService = $queryBuilderService;
    }

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

    private function changeResultToPercentage($result): array
    {
        $total = $this->execute($this->queryBuilderService->buildTotalAnswersQuery());
        $totalAmount = $total[0][QueryConstants::COUNT_COLUMN_NAME];

        foreach ($result as $i => $item) {
            $result[$i][QueryConstants::COUNT_COLUMN_NAME] /= $totalAmount ;
        }

        return $result;
    }

    private function isResultAlternationNeeded(Request $request): bool
    {
        if ($request->input(QueryConstants::CHART_TYPE) === QueryConstants::POPULARITY_GRAPH
            && $request->input(QueryConstants::COUNT) === QueryConstants::COUNT_ANSWERS
            && $request->input(QueryConstants::CATEGORY)
            && $request->input(QueryConstants::PERCENTAGE)
        ) {
            return true;
        }

        return false;
    }

    private function filterRequest(Request $request): array
    {
        //remove first input and all empty inputs
        $filteredRequest = array_filter(array_slice((array)$request->all(),1));

        if (array_key_exists('country', $filteredRequest)){
            array_filter($filteredRequest['country']);
        }

        return $filteredRequest;
    }
    public function handle(): array
    {
        $this->query = $this->queryBuilderService->build();

        $request = $this->queryBuilderService->getRequest();

        $this->filteredRequest = $this->filterRequest($request);

        $result = $this->execute($this->query);

        if ($this->isResultAlternationNeeded($request)){
            $result = $this->changeResultToPercentage($result);
        }

        return $result;
    }

}
