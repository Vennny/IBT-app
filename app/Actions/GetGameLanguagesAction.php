<?php

namespace App\Actions;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class GetGameLanguagesAction
{
    use AsAction;

    /**
     * @return Collection
     */
    public function handle(): Collection
    {
        return DB::table('lang')->orderBy('date_cr', 'ASC')->get();
    }
}
