<?php

namespace App\Models;

use App\Models\Pay;
use App\Models\Bill;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Account extends Model
{
    use HasFactory;

    protected $table = 'acc_accounts';

    public function bill ()
    {
        return $this->hasMany(Bill::class);
    }

    public function debt ()
    {
        return $this->hasMany(Debt::class);
    }

    public function trans ()
    {
        return $this->hasMany(Trans::class);
    }
}
