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
     * @param $queryBuilderService
     */
    public function __construct(private QueryBuilderService $queryBuilderService){}

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

    private function changeResultToPercentage(Request $request, array $result): array
    {
        if ($request->input(QueryConstants::GRAPH_TYPE) === QueryConstants::POPULARITY_GRAPH
            && $request->input(QueryConstants::COUNT) === QueryConstants::COUNT_ANSWERS
            && $request->input(QueryConstants::CATEGORY)
        ) {
            $total = $this->execute($this->queryBuilderService->buildTotalAnswersQuery());
            $totalAmount = $total[0][QueryConstants::COUNT_COLUMN_NAME] ?? 1;

            foreach ($result as $i => $item) {
                $result[$i][QueryConstants::COUNT_COLUMN_NAME] /= $totalAmount;
            }

        } elseif ($request->input(QueryConstants::GRAPH_TYPE) === QueryConstants::TIME_GRAPH) {
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

        $request = $this->queryBuilderService->getRequest();

        $this->filteredRequest = $this->filterRequest($request);

        $result = $this->execute($this->query);

        if ($request->input(QueryConstants::PERCENTAGE)) {
            $result = $this->changeResultToPercentage($request, $result);
        }

        return $result;
    }

}
