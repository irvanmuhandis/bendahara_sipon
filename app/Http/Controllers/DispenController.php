<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Dispen;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DispenController extends Controller
{
    public function index()
    {
        return Dispen::query()->with('user:id')
            ->latest()
            ->paginate(3)
            ->through(fn ($app) => [
                'id' => $app->id,
                'desc' => $app->desc,
                'pay_at' => $app->pay_at,
                'periode' => $app->periode,
                'user_id' => $app->user_id,
                'updated_at' => $app->updated_at,
                'created_at' => $app->created_at,
                'user_name' => $app->user_name
            ]);
    }

    public function store()
    {
        // request()->validate([
        //     'name' => 'required',
        //     'email' => 'required|unique:dispens,email',
        //     'password' => 'required|min:8',
        // ]);
        $user = User::where('id', '=', request('userId'))->first();
        $dispen = Dispen::create([
            'user_name' => $user->name,
            'user_id' => request('userId'),
            'periode' => request('periode'),
            'pay_at' => request('pay_at'),
            'desc' => request('desc')
        ]);
        return $dispen;
    }

    public function update(Dispen $dispen)
    {
        //     request()->validate([
        //         'name' => 'required',
        //         'peiod' => 'required|unique:dispens,email,' . $dispen->id,
        //         'password' => 'sometimes|min:8',
        //     ]);

        $name = User::where('id', '=', request('userId'))->first()->name;

        $dispen->update([
            'user_name' => $name,
            'user_id' => request('userId'),
            'periode' => request('periode'),
            'pay_at' => request('pay_at'),
            'desc' => request('desc')
        ]);

        return $dispen;
    }
    public function bulkDelete()
    {
        Dispen::whereIn('id', request('ids'))->delete();

        return response()->json(['message' => 'Dispens deleted successfully!']);
    }
    public function destroy(Dispen $dispen)
    {
        $dispen->delete();

        return response()->noContent();
    }

    public function changeRole(Dispen $dispen)
    {
        $dispen->update([
            'role' => request('role'),
        ]);

        return response()->json(['success' => true]);
    }

    public function search()
    {
        $searchQuery = request('query');
        $dispens = Dispen::where('user_name', 'like', "%{$searchQuery}%")->latest()->paginate(3);

        return response()->json($dispens);
    }


}
