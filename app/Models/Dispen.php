<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dispen extends Model
{
    use HasFactory;
    protected $fillable = ['pay_at', 'periode', 'desc','user_id','user_name'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
