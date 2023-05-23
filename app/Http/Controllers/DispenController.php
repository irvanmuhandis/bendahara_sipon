<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Dispen;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class DispenController extends Controller
{
    public function index()
    {
        return Dispen::with('user')->paginate(5);
    }

    public function store()
    {
        // request()->validate([
        //     'name' => 'required',
        //     'email' => 'required|unique:dispens,email',
        //     'password' => 'required|min:8',
        // ]);

        $dispen = Dispen::create([
            'user_id' => request('userId'),
            'dispen_periode' => request('periode'),
            'pay_at' => request('pay_at'),
            'dispen_desc' => request('desc')
        ]);
        return $dispen;
    }

    public function update()
    {
        //     request()->validate([
        //         'name' => 'required',
        //         'peiod' => 'required|unique:dispens,email,' . $dispen->id,
        //         'password' => 'sometimes|min:8',
        //     ]);

        $data = Dispen::where('id', '=', request('id'))->first();

        $data->update([
            'user_id' => request('userId'),
            'dispen_periode' => request('periode'),
            'pay_at' => request('pay_at'),
            'dispen_desc' => request('desc')
        ]);

        return $data;
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
        $dispens = DB::table('dispens')->join('users', 'users.id', '=', 'dispens.user_id')->where('users.name', 'like', "%{$searchQuery}%")->paginate(5);

        return $dispens;
    }
}
