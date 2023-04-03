<?php

namespace App\Models;

use App\Models\Pay;
use App\Models\Group;
use App\Models\Account;
use App\Models\Periodic;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Bill extends Model
{
    use HasFactory;

    public function pay()
    {
        return $this->morphMany(Pay::class, 'payable');
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function periodic()
    {
        return $this->belongsTo(Periodic::class,'periodic_id','id');
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

}
