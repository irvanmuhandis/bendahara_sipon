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

        $wallets = Wallet::where('wallet_name', 'like', "%{$searchQuery}%")
            ->select('created_at', 'id', 'wallet_name', 'wallet_type', 'debit', 'credit', DB::raw('(SELECT SUM(debit) - SUM(credit) FROM acc_wallets AS w2 WHERE w2.id <= acc_wallets.id AND w2.wallet_type = acc_wallets.wallet_type) AS saldo'))
            ->orderBy($fil, $req)
            ->paginate(10);

        return $wallets;
    }

    public function list()
    {
        $wallets = Wallet::whereIn('id', function ($query) {
            $query->selectRaw('MAX(id)')
                ->from('acc_wallets')
                ->groupBy('wallet_type');
        })
            ->get();

        $sums = Wallet::selectRaw("SUM(debit) as total_debit")
            ->selectRaw("SUM(credit) as total_credit")
            ->selectRaw("SUM(debit)-SUM(credit) as saldo")
            ->selectRaw("wallet_type")
            ->groupBy('wallet_type')
            ->get();

        foreach ($sums as $sum) {
            foreach ($wallets as $wallet) {
                if ($wallet->wallet_type == $sum->wallet_type) {
                    $wallet['sum'] = $sum;
                }
            }
        }
        return $wallets;
    }


    public function store()
    {
        // request()->validate([
        //     'name' => 'required',
        //     'email' => 'required|unique:dispens,email',
        //     'password' => 'required|min:8',
        // ]);
$type = Wallet::orderByDesc('wallet_type')->first();
        $wallet = Wallet::create([
            'wallet_type' => $type==null?1:$type->wallet_type+1,
            'wallet_name' => request('name'),
            'debit' => request('debit'),
            'credit' => 0,
        ]);
        return $wallet;
    }

    public function delType()
    {
        return  Wallet::where('wallet_type', request('wallet_type'))->delete();;
    }

    public function update($id)
    {
        //     request()->validate([
        //         'name' => 'required',
        //         'peiod' => 'required|unique:dispens,email,' . $wallet->id,
        //         'password' => 'sometimes|min:8',
        //     ]);
        $wal =  Wallet::where('id', '=', request('id'))->first();
        $data = $wal->update([

            'debit' => request('debit'),
            'credit' => request('credit'),
            'wallet_name' => request('name')
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
}
