<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dispen extends Model
{
    use HasFactory;
    protected $table = 'acc_dispens';
    protected $guarded = [];
    public function santri()
    {
        return $this->belongsTo(Santri::class, 'nis', 'nis');
    }
}
