<?php

namespace App\Http\Controllers;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AppointmentController extends Controller
{
    public function index()
    {
        return Appointment::query()->with('client:id,first_name,last_name')
            ->when(request('status'), function ($query) {
                return $query->where('status', AppointmentStatus::from(request(('status'))));
            })
            ->latest()
            ->paginate()
            ->through(fn ($app) => [
                'id' => $app->id,
                'start_time' => $app->start_time->format('Y-m-d'),
                'end_time' => $app->end_time->format('Y-m-d h:i A'),
                'status' => [
                    'name' => $app->status->name,
                    'color' => $app->status->color(),
                ],
                'client' => $app->client
            ]);
    }

    public function store()
    {
        request()->validate([
            'title' => 'required',
            'desc' => 'required'
        ]);
        Appointment::create([
            'title' => request('title'),
            'client_id' => 1,
            'start_time' => now(),
            'end_time' => now(),
            'desc' => request('desc'),
            'status' => AppointmentStatus::SCHEDULED,
        ]);
        return response()->json(['message' => 'succes']);
    }
}
