<?php

namespace App\Models;

use App\Models\Pay;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Account;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BigBook extends Model
{
    use HasFactory;

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }



    public function user()
    {
        return $this->belongsTo(User::class);
    }



   

    public function account(){

        return $this->hasManyThrough(Pay::class, Account::class);
    }

    public function bookable()
    {
        return $this->morphTo();
    }
}
