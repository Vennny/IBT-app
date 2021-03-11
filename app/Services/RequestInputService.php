<?php


namespace App\Services;

use App\Constants\QueryConstants;
use App\Rules\CountryExists;
use Illuminate\Http\Request;
use League;

class RequestInputService
{
    public function __construct(private Request $request){}

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function getInputValue(string $input): mixed
    {
        match ($input) {
            QueryConstants::GRAPH_TYPE => $this->request->validate([QueryConstants::GRAPH_TYPE => 'required|string']),
            QueryConstants::COUNT_TABLE => $this->request->validate([QueryConstants::COUNT_TABLE => 'required|string']),
            QueryConstants::LANGUAGE => $this->request->validate([QueryConstants::LANGUAGE => 'required|string']),
            QueryConstants::LETTER => $this->request->validate([QueryConstants::LETTER => 'nullable|string|max:1']),
            QueryConstants::LIMIT => $this->request->validate([QueryConstants::LIMIT => 'required|integer|min:1']),
            QueryConstants::PERCENTAGE => $this->request->validate([QueryConstants::PERCENTAGE => 'nullable']),
            QueryConstants::WORD => $this->request->validate([
                QueryConstants::WORD => 'required|array',
                QueryConstants::WORD.'.0' => 'required|string',
                QueryConstants::WORD.'.*' => 'nullable|string'
            ]),
            QueryConstants::OPERATOR => $this->request->validate([
                QueryConstants::OPERATOR => 'required|array',
                QueryConstants::OPERATOR.'.*' => 'required|string'
            ]),
            QueryConstants::CATEGORY => $this->request->validate([
                QueryConstants::CATEGORY => 'array',
                QueryConstants::CATEGORY.'.*' => 'nullable|string'
            ]),
            QueryConstants::COUNTRY => $this->request->validate([
                QueryConstants::COUNTRY => 'array',
                QueryConstants::COUNTRY.'.*' => ['nullable', 'string', new CountryExists]
            ]),
        };

        return match ($input) {
            QueryConstants::GRAPH_TYPE => $this->request->input(QueryConstants::GRAPH_TYPE),
            QueryConstants::COUNT_TABLE => $this->request->input(QueryConstants::COUNT_TABLE),
            QueryConstants::LANGUAGE => $this->request->input(QueryConstants::LANGUAGE),
            QueryConstants::LETTER => $this->request->input(QueryConstants::LETTER),
            QueryConstants::LIMIT => intval($this->request->input(QueryConstants::LIMIT)),
            QueryConstants::PERCENTAGE => $this->request->input(QueryConstants::PERCENTAGE),
            QueryConstants::COUNTRY => array_filter($this->request->input(QueryConstants::COUNTRY)),
            QueryConstants::CATEGORY =>  array_map('strtolower', array_filter($this->request->input(QueryConstants::CATEGORY))),
            QueryConstants::OPERATOR => array_filter($this->request->input(QueryConstants::OPERATOR)),
            QueryConstants::WORD => array_map('strtolower', array_filter($this->request->input(QueryConstants::WORD))),
        };
    }

    public function getCountryCode(string $name): string
    {
        $country = (new League\ISO3166\ISO3166)->name($name);
        return $country['alpha2'];
    }

    public function escapeSingleQuotesInInputs(): void
    {
        foreach ($this->request as $key => $input) {
            if (is_string($input)){
                $this->request[$key] = preg_replace("/'/", "''", $input);
            }
        }
    }

}
