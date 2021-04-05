<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use League;

class CountryExistsRule implements Rule
{
    private string $value;
    /**
     * Create a new rule instance.
     *
     * @param League\ISO3166\ISO3166 $countries
     */
    public function __construct(private League\ISO3166\ISO3166 $countries){}

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        try{
            if ($value != null)
                $this->countries->name($value);
        }
        catch(\Exception $e){
            $this->value = $value;
            return false;
        }
        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Country '. $this->value . ' not found';
    }
}
