<?php


namespace App\Services;

use App\Constants\QueryConstants;
use App\Rules\CountryExistsRule;
use Illuminate\Http\Request;
use League;

class RequestInputService
{
    /**
     * @var League\ISO3166\ISO3166
     */
    private League\ISO3166\ISO3166 $isoCountries;

    /**
     * RequestInputService constructor.
     *
     * @param Request $request
     */
    public function __construct(private Request $request){
        $this->isoCountries = (new League\ISO3166\ISO3166);
    }

    /**
     * @return Request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * Validates input and returns its value.
     *
     * @param string $input
     *
     * @return mixed
     */
    public function getInputValue(string $input): mixed
    {
        match ($input) {
            QueryConstants::GRAPH_TYPE_KEY => $this->request->validate([QueryConstants::GRAPH_TYPE_KEY => 'required|string']),
            QueryConstants::COUNT_TABLE_KEY => $this->request->validate([QueryConstants::COUNT_TABLE_KEY => 'required|string']),
            QueryConstants::LANGUAGE_KEY => $this->request->validate([QueryConstants::LANGUAGE_KEY => 'required|string']),
            QueryConstants::LETTER_KEY => $this->request->validate([QueryConstants::LETTER_KEY => 'nullable|string|max:1']),
            QueryConstants::LIMIT_KEY => $this->request->validate([QueryConstants::LIMIT_KEY => 'required|integer|min:1|max:' . QueryConstants::LIMIT_NUMBER]),
            QueryConstants::PERCENTAGE_KEY => $this->request->validate([QueryConstants::PERCENTAGE_KEY => 'nullable']),
            QueryConstants::WORD_KEY => $this->request->validate([
                QueryConstants::WORD_KEY => 'required|array',
                QueryConstants::WORD_KEY.'.0' => 'required|string',
                QueryConstants::WORD_KEY.'.*' => 'nullable|string'
            ]),
            QueryConstants::OPERATOR_KEY => $this->request->validate([
                QueryConstants::OPERATOR_KEY => 'required|array',
                QueryConstants::OPERATOR_KEY.'.*' => 'required|string'
            ]),
            QueryConstants::CATEGORY_KEY => $this->request->validate([
                QueryConstants::CATEGORY_KEY => 'array',
                QueryConstants::CATEGORY_KEY.'.*' => 'nullable|string'
            ]),
            QueryConstants::COUNTRY_KEY => $this->request->validate([
                QueryConstants::COUNTRY_KEY => 'array',
                QueryConstants::COUNTRY_KEY.'.*' => ['nullable', 'string', new CountryExistsRule($this->isoCountries)]
            ]),
        };

        return match ($input) {
            QueryConstants::GRAPH_TYPE_KEY => $this->request->input(QueryConstants::GRAPH_TYPE_KEY),
            QueryConstants::COUNT_TABLE_KEY => $this->request->input(QueryConstants::COUNT_TABLE_KEY),
            QueryConstants::LANGUAGE_KEY => $this->request->input(QueryConstants::LANGUAGE_KEY),
            QueryConstants::LETTER_KEY => $this->request->input(QueryConstants::LETTER_KEY),
            QueryConstants::LIMIT_KEY => intval($this->request->input(QueryConstants::LIMIT_KEY)),
            QueryConstants::PERCENTAGE_KEY => $this->request->input(QueryConstants::PERCENTAGE_KEY),
            QueryConstants::COUNTRY_KEY => array_filter($this->request->input(QueryConstants::COUNTRY_KEY)),
            QueryConstants::CATEGORY_KEY =>  array_map('strtolower', array_filter($this->request->input(QueryConstants::CATEGORY_KEY))),
            QueryConstants::OPERATOR_KEY => array_filter($this->request->input(QueryConstants::OPERATOR_KEY)),
            QueryConstants::WORD_KEY => array_map('strtolower', array_filter($this->request->input(QueryConstants::WORD_KEY))),
        };
    }

    /**
     * Finds country code of a country from its name.
     *
     * @param string $name
     *
     * @return string
     */
    public function getCountryCode(string $name): string
    {
        $country = $this->isoCountries->name($name);
        return $country['alpha2'];
    }

    /**
     *  Escapes single quote character "'" by doubling it. Prevents SQL injection.
     */
    public function escapeSingleQuotesInInputs(): void
    {
        foreach ($this->request->all() as $inputKey => $input) {
            if ($inputKey === QueryConstants::COUNTRY_KEY){
                //countries are validated separately, escaping would break that validation
                continue;
            }

            if (is_string($input) || is_array($input)) {
                //modify request with new input
                $this->request->merge([$inputKey => preg_replace("/'/", "''", $input)]);
            }
        }
    }
}
