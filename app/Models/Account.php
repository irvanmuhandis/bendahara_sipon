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
    protected $guarded = [];

    public function bill()
    {
        return $this->hasMany(Bill::class);
    }

    public function paybill()
    {
        return $this->hasManyThrough(
            Pay::class,
            Bill::class,
            'account_id', // Foreign key on the environments table...
            'payable_id', // Foreign key on the deployments table...
            'id', // Local key on the projects table...
            'id' // Local key on the environments table...
        );
    }

    public function paydebt()
    {
        return $this->hasManyThrough(
            Pay::class,
            Debt::class,
            'account_id', // Foreign key on the environments table...
            'payable_id', // Foreign key on the deployments table...
            'id', // Local key on the projects table...
            'id' // Local key on the environments table...
        );
    }

    public function debt()
    {
        return $this->hasMany(Debt::class);
    }

    public function trans()
    {
        return $this->hasMany(Trans::class);
    }

    public static function boot()
    {
        parent::boot();
        self::deleting(function ($account) { // before delete() method call this
            $account->debt()->each(function ($debt) {
                $debt->wallet()->each(function ($wallet) {
                    $wallet->delete();
                }); // <-- direct deletion
            });
            $account->trans()->each(function ($trans) {
                $trans->wallet()->each(function ($wallet) {
                    $wallet->delete();
                }); // <-- direct deletion
            });
            $account->paydebt()->each(function ($debt) {
                $debt->wallet()->each(function ($wallet) {
                    $wallet->delete();
                }); // <-- direct deletion
            });
            $account->paybill()->each(function ($paybill) {
                $paybill->wallet()->each(function ($wallet) {
                    $wallet->delete();
                }); // <-- direct deletion
            });
            // do the rest of the cleanup...
        });
    }
}
