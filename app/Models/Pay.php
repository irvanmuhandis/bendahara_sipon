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

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function wallet()
    {
        return $this->belongsTo(Wallet::class, 'wallet_id', 'id');
    }

    public function payable()
    {
        return $this->morphTo();
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function operator()
    {
        return $this->belongsTo(User::class, 'operator_id', 'id');
    }
}
