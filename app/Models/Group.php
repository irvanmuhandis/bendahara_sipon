<?php

namespace App\Models;

use App\Models\Bill;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Group extends Model
{
    use HasFactory;
    protected $table = 'groups';
    protected $guarded = [];
    // group user nanti ditampilin di setiap rekord pay , adi tau saat pembayaran dia di group apa

    // public function santri()
    // {
    //     return $this->hasMany(Santri::class);
    // }

    public function santri()
    {
        return $this->belongsToMany(Santri::class, 'group_santri', 'group_id', 'nis');
    }

    public function bill()
    {
        return $this->hasMany(Bill::class);
    }
}
