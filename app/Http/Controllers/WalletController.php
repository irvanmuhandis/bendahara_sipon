<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Console\View\Components\Warn;

class WalletController extends Controller
{

    public function index()
    {
        $wallets = Wallet::latest()->paginate(10);
        return $wallets;
    }

    public function list()
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
            'wallet_type' => (Wallet::orderByDesc('wallet_type')->first()->wallet_type) + 1,
            'wallet_name' => request('name'),
            'prev_saldo' => request('saldo'),
            'saldo' => request('saldo'),
        ]);
        return $dispen;
    }

    public function update($id)
    {
        //     request()->validate([
        //         'name' => 'required',
        //         'peiod' => 'required|unique:dispens,email,' . $dispen->id,
        //         'password' => 'sometimes|min:8',
        //     ]);
        $wal =  Wallet::where('id', '=', request('id'))->first();
        $data = $wal->update([

            'prev_saldo' => $wal->saldo,
            'wallet_name' => request('name'),
            'saldo' => request('saldo'),
        ]);

        return $data;
    }
    public function bulkDelete()
    {
        Wallet::whereIn('id', request('ids'))->delete();

        return response()->json(['message' => 'Wallets deleted successfully!']);
    }
    public function destroy($id)
    {
        $data = Wallet::where('id', '=', $id)->delete();

        return $data;
    }

    public function search()
    {
        $searchQuery = request('query');
        $wallets = DB::table('wallets')
            ->whereIn('id', function ($query) {
                $query->selectRaw('MAX(id)')
                    ->from('wallets')
                    ->groupBy('wallet_type');
            })
            ->where('wallet_name', 'like', "%{$searchQuery}%")
            ->paginate(10);
        return $wallets;
    }
}
