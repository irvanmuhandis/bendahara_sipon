<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AppointmentStatus;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\Request;

class AppointStatusController extends Controller
{
    public function getStatusWithCount()
    {

        $cases = AppointmentStatus::cases();
        // dd($cases);
        return collect($cases)->map(function ($status){
            return [
                'name'=>$status->name,
                'value'=>$status->value,
                'count'=>Appointment::where('status',$status->value)->count(),
                'color'=>AppointmentStatus::from($status->value)->color(),
            ];
        });

        // return [
        //     ['name' => 'scheduled'],
        //     ['name' => 'success'],
        //     ['name' => 'cancelled']
        // ];
    }
}
