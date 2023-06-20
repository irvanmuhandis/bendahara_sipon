<?php
namespace App\Enums;

use App\Models\Debts;
use App\Models\Pay;

enum PayStatus:int{
    case BELUM_LUNAS = 1;
    case SEBAGIAN_LUNAS = 2;
    case LUNAS = 3;

    public function color():string{
        return match($this){
            PayStatus::BELUM_LUNAS=>'danger',
            PayStatus::SEBAGIAN_LUNAS=>'warning',
            PayStatus::LUNAS=>'success',
        };
    }

    public function names():string{
        return match($this){
            PayStatus::BELUM_LUNAS=>'BELUM BAYAR',
            PayStatus::SEBAGIAN_LUNAS=>'BELUM LUNAS',
            PayStatus::LUNAS=>'LUNAS',
        };
    }
}
