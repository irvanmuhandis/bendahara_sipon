<?php

namespace App\Models;

use App\Enums\AppointmentStatus;
use App\Models\Client;
use DateTime;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Appointment extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'start_time' => "datetime",
        'end_time' => 'datetime',
        'status' => AppointmentStatus::class
    ];


    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id', 'id');
    }
}
