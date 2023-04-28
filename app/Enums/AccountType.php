<?php
namespace App\Enums;


enum AccountType:int{
    case PERIODIC = 2;
    case INCIDENTAL = 1;

    public function color():string{
        return match($this){
            AccountType::PERIODIC=>'primary',
            AccountType::INCIDENTAL=>'success',
        };
    }
    public function name():string{
        return match($this){
            AccountType::PERIODIC=>'PERIODIK',
            AccountType::INCIDENTAL=>'INSIDENTAL',
        };
    }
}
