<?php

namespace App\Actions;

use Lorisleiva\Actions\Concerns\AsAction;
use League;

class GetIsoCountriesAction
{
    use AsAction;

    public function handle()
    {
        return (new League\ISO3166\ISO3166);
    }
}
