<?php

namespace App\Models;

use App\Models\Pay;
use App\Models\User;
use App\Models\Account;
use App\Enums\DebtStatus;
use Illuminate\Database\Eloquent\Model;
use App\Http\Controllers\Attr\DebtStatusController;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Debts extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $casts = [
        'status' => DebtStatus::class
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function pay()
    {
        return $this->morphMany(Pay::class, 'payable');
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}
