<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Santri extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $primaryKey = 'nis';

    function bill(){
        return $this->hasMany(Bill::class,'nis','nis');
    }

    public function debt()
    {
        return $this->hasMany(Debt::class,'nis','nis');
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }
}
