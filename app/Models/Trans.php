<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Trans extends Model
{
    use HasFactory;
    protected $table = 'acc_trans';
    protected $guarded = [];

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }

    public function source()
    {
        return $this->morphTo();
    }

    public function operator()
    {
        return $this->belongsTo(User::class, 'operator_id', 'id');
    }

    public function santri()
    {
        return $this->hasOneThrough(
            Santri::class,
            User::class,
            'id', // refers to id column on user table
            'nis', // refers to id column on santri table
            'operator_id', // refers to user table
            'nis_santri' // refers to santri table
        );
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function ledger()
    {
        return $this->morphOne(Ledger::class, 'ledgerable');
    }
}
