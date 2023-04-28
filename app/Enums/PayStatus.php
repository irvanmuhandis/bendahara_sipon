<?php
namespace App\Enums;

use App\Models\Debts;
use App\Models\Pay;

enum PayStatus:int{
    case BELUM_LUNAS = 1;
    case SEBAGIAN_LUNAS = 2;
    case LUNAS = 3;
    case TDK_SESUAI = 4;

    public function color():string{
        return match($this){
            PayStatus::BELUM_LUNAS=>'danger',
            PayStatus::SEBAGIAN_LUNAS=>'warning',
            PayStatus::LUNAS=>'success',
            PayStatus::TDK_SESUAI=>'primary',
        };
    }

    public function names():string{
        return match($this){
            PayStatus::BELUM_LUNAS=>'BELUM LUNAS',
            PayStatus::SEBAGIAN_LUNAS=>'SEBAGIAN LUNAS',
            PayStatus::LUNAS=>'LUNAS SESUAI',
            PayStatus::TDK_SESUAI=>'BILL BEDA'
        };
    }
}
