<?php

namespace App\Http\Controllers\Status;

use App\Enums\PayStatus;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Status\PayStatus as StatusPayStatus;

class PayStatusController extends Controller
{
    public function status()
    {

        $cases = PayStatus::cases();
        // dd($cases);
        return collect($cases)->map(function ($status) {
            return [
                'name' => PayStatus::from($status->value)->names(),
                'value' => $status->value,
                'color' => PayStatus::from($status->value)->color(),
            ];
        });
    }
}
