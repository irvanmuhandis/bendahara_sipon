<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ledger extends Model
{
    use HasFactory;

    protected $table = 'acc_ledgers';
    protected $guarded = [];


    public function ledgerable()
    {
        return $this->morphTo();
    }

    public function wallettrans()
    {
        return $this->hasOneThrough(
            Wallet::class,
            Trans::class,
            'id', // refers to id column on trans table
            'id', // refers to id column on wallet table
            'ledgerable_id', // refers to trans table
            'wallet_id' // refers to wallet table
        );
    }

    public function walletpay()
    {
        return $this->hasOneThrough(
            Wallet::class,
            Pay::class,
            'id',
            'id',
            'ledgerable_id',
            'wallet_id'
        );
    }

    public function walletdebt()
    {
        return $this->hasOneThrough(
            Wallet::class,
            Debt::class,
            'id',
            'id',
            'ledgerable_id',
            'wallet_id'
        );
    }
}
