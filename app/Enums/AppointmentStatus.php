<?php
namespace App\Enums;

use App\Models\Appointment;

enum AppointmentStatus:int{
    case SCHEDULED = 1;
    case CONFIRMED = 2;
    case CANCELLED = 3;

    public function color():string{
        return match($this){
            AppointmentStatus::SCHEDULED=>'primary',
            AppointmentStatus::CANCELLED=>'danger',
            AppointmentStatus::CONFIRMED=>'success',
        };
    }
}
