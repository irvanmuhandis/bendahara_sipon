<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dispen extends Model
{
    use HasFactory;
    protected $fillable = ['pay_at', 'dispen_periode', 'dispen_desc','user_id'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
