<?php

namespace App\Models;

use App\Models\Pay;
use App\Models\Group;
use App\Models\Account;
use App\Models\User;
use App\Models\Santri;
use App\Models\Periodic;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Bill extends Model
{
    use HasFactory;
    protected $table = 'acc_bills';
    protected $guarded = [];


    public function pay()
    {
        return $this->morphMany(Pay::class, 'payable');
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function santri()
    {
        return $this->belongsTo(Santri::class,'nis','nis');
    }

    public function operator()
    {
            return $this->hasOneThrough(
            Santri::class,
            User::class,
            'id', 
            'nis', 
            'operator_id', 
            'nis_santri' 
        );;
    }

}
