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
        $fil = request('filter');
        $req = request('value');
        $searchQuery = request('query');

        if ($fil == '') {
            $fil = 'id';
            $req = 'desc';
        } else {
            if ($req == 0) {
                $req = 'asc';
            } else {
                $req = 'desc';
            }
        }
        $bill = Dispen::whereHas('santri', function ($query) use ($searchQuery) {
            $query->where('fullname', 'like', "%{$searchQuery}%")
            ->where('option',1);
        })
            ->with(['santri'])
            ->orderBy($fil, $req)
            ->paginate(10);

        return $bill;
    }

    public function store()
    {
        // request()->validate([
        //     'name' => 'required',
        //     'email' => 'required|unique:dispens,email',
        //     'password' => 'required|min:8',
        // ]);

        $dispen = Dispen::create([
            'nis' => request('santri'),
            'dispen_periode' => request('periode'),
            'pay_at' => request('pay_at'),
            'dispen_desc' => request('desc'),
            'status' => 1
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
            'nis' => request('santri')['nis'],
            'dispen_periode' => request('periode'),
            'pay_at' => request('pay_at'),
            'dispen_desc' => request('desc'),
            'status' => request('status')
        ]);

        return $data;
    }
    public function bulkDelete()
    {
        Dispen::whereIn('id', request('ids'))->delete();

        return response()->json(['message' => 'Dispensasi berhasil dihapus!']);
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
        $dispens = DB::table('acc_dispens')->join('users', 'users.id', '=', 'dispens.user_id')->where('users.name', 'like', "%{$searchQuery}%")->paginate(5);

        return $dispens;
    }
}
