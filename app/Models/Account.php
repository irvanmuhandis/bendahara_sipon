<?php

namespace App\Models;

use App\Models\Pay;
use App\Models\Bill;
use App\Models\Debts;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Account extends Model
{
    use HasFactory;

    public function bill ()
    {
        return $this->hasMany(Bill::class);
    }

    public function debt ()
    {
        return $this->hasMany(Debts::class);
    }
}
