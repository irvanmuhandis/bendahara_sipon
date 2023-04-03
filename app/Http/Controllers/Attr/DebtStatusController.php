<?php

namespace App\Http\Controllers\Attr;

use App\Enums\DebtStatus;
use App\Http\Controllers\Controller;
use App\Models\Debts;

class DebtStatusController extends Controller
{
    public function getStatusWithCount()
    {

        $cases = DebtStatus::cases();
        // dd($cases);
        return collect($cases)->map(function ($status){
            return [
                'name'=>$status->name,
                'value'=>$status->value,
                'count'=>Debts::where('status',$status->value)->count(),
                'color'=>DebtStatus::from($status->value)->color(),
            ];
        });
    }
}
