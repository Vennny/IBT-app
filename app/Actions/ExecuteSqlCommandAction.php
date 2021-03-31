<?php

namespace App\Actions;

use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class ExecuteSqlCommandAction
{
    use AsAction;

    /**
     * @param String $query
     *
     * @return array<int, array<string, int>>
     */
    public function handle(String $query): array
    {
        return DB::select(DB::raw($query));
    }
}
