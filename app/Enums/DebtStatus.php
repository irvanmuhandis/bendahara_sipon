<?php
namespace App\Enums;

use App\Models\Debts;

enum DebtStatus:int{
    case BELUM_LUNAS = 1;
    case LUNAS = 2;
    case LEBIH = 3;

    public function color():string{
        return match($this){
            DebtStatus::BELUM_LUNAS=>'danger',
            DebtStatus::LUNAS=>'success',
            DebtStatus::LEBIH=>'primary',
        };
    }
}
