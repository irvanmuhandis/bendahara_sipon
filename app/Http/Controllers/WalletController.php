<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class WalletController extends Controller
{

    public function index()
    {
        $wallets = DB::table('wallets')
            ->whereIn('id', function ($query) {
                $query->selectRaw('MAX(id)')
                    ->from('wallets')
                    ->groupBy('wallet_type');
            })
            ->get();
            return $wallets;
    }


    public function store()
    {
        // request()->validate([
        //     'name' => 'required',
        //     'email' => 'required|unique:dispens,email',
        //     'password' => 'required|min:8',
        // ]);
        $dispen = Wallet::create([
            'name' => request('name'),
            'prev_saldo' => request('prev_saldo'),
            'saldo' => request('saldo'),
            'created_at' => request('created_at'),
            'updated_at' => request('updated_at')
        ]);
        return $dispen;
    }

    public function update(Wallet $dispen)
    {
        //     request()->validate([
        //         'name' => 'required',
        //         'peiod' => 'required|unique:dispens,email,' . $dispen->id,
        //         'password' => 'sometimes|min:8',
        //     ]);

        $dispen->update([

            'name' => request('name'),
            'prev_saldo' => request('prev_saldo'),
            'saldo' => request('saldo'),
            'created_at' => request('created_at'),
            'updated_at' => request('updated_at')
        ]);

        return $dispen;
    }
    public function bulkDelete()
    {
        Wallet::whereIn('id', request('ids'))->delete();

        return response()->json(['message' => 'Wallets deleted successfully!']);
    }
    public function destroy(Wallet $dispen)
    {
        $dispen->delete();

        return response()->noContent();
    }

    public function search()
    {
        $searchQuery = request('query');

        $group = Wallet::where('name', 'like', "%{$searchQuery}%")->latest()->paginate(3);
        return response()->json($group);
    }
}
