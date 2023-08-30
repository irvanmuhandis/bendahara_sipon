<?php
namespace App\Enums;


enum AccountType:int{
    case EXTERNAL = 3;
    case PERIODIC = 2;
    case INCIDENTAL = 1;

    public function color():string{
        return match($this){

            AccountType::EXTERNAL=>'warning',
            AccountType::PERIODIC=>'primary',
            AccountType::INCIDENTAL=>'success',
        };
    }
    public function name():string{
        return match($this){
            AccountType::PERIODIC=>'PERIODIK',
            AccountType::EXTERNAL=>'EKSTERNAL',
            AccountType::INCIDENTAL=>'INSIDENTAL',
        };
    }
}
