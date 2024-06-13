<?php

namespace App\Models;

use App\Models\Bill;
use App\Models\User;
use App\Models\Debts;
use App\Models\Wallet;
use App\Models\Account;
use App\Models\BigBook;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Pay extends Model
{
    use HasFactory;
    protected $table = 'acc_pays';
    protected $guarded = [];

    public function santri()
    {
        return $this->belongsTo(Santri::class, 'nis', 'nis');
    }

    public function wallet()
    {
        return $this->belongsTo(Wallet::class, 'wallet_id', 'id');
    }

    public function payable()
    {
        return $this->morphTo();
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
        );
    }

    public function accountbill()
    {
        return $this->hasOneThrough(
            Account::class,
            Bill::class,
            'id', // refers to id column on trans table
            'id', // refers to id column on wallet table
            'payable_id', // refers to trans table
            'account_id' // refers to wallet table
        );;
    }

    public function accountdebt()
    {
        return $this->hasOneThrough(
            Account::class,
            Debt::class,
            'id', // refers to id column on trans table
            'id', // refers to id column on wallet table
            'payable_id', // refers to trans table
            'account_id' // refers to wallet table
        );;
    }

    public function santribill()
    {
        return $this->hasOneThrough(
            Santri::class,
            Bill::class,
            'id', // refers to id column on trans table
            'nis', // refers to id column on wallet table
            'payable_id', // refers to trans table
            'nis' // refers to wallet table
        );;
    }

    public function santridebt()
    {
        return $this->hasOneThrough(
            Santri::class,
            Debt::class,
            'id', // refers to id column on trans table
            'nis', // refers to id column on wallet table
            'payable_id', // refers to trans table
            'nis' // refers to wallet table
        );;
    }

    public function ledger()
    {
        return $this->morphOne(Ledger::class, 'ledgerable');
    }
}
