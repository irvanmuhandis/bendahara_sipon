<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Debt extends Model
{
    use HasFactory;
    protected $table = 'acc_debts';
    protected $guarded = [];

    public function santri()
    {
        return $this->belongsTo(Santri::class,'nis','nis');
    }

    public function pay()
    {
        return $this->morphMany(Pay::class, 'payable');
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function operator()
    {
        return $this->belongsTo(User::class,'operator_id','id');
    }

    public function wallet(){
        return $this->belongsTo(Wallet::class);
    }

    public function ledger()
    {
        return $this->morphOne(Ledger::class, 'ledgerable');
    }
}
