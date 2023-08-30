<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    use HasFactory;
    
    protected $table = 'acc_wallets';
    protected $guarded = [];

    public function ledgertrans()
    {
        return $this->
        hasOneThrough(Ledger::class, Trans::class,'wallet_id','ledgerable_id','id','id');
    }

    public function ledgerbill()
    {
        return $this->
        hasOneThrough(Ledger::class, Bill::class,'wallet_id','ledgerable_id','id','id');
    }

    public function ledgerdebt()
    {
        return $this->
        hasOneThrough(Ledger::class, Debt::class,'wallet_id','ledgerable_id','id','id');
    }
}
