<?php

namespace App\Http\Controllers;

use App\Models\Trans;
use App\Models\Ledger;
use App\Models\Wallet;
use App\Models\BigBook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class TransController extends Controller
{
    public function index()
    {
        $trans = DB::table('trans')
            ->join('accounts', 'accounts.id', '=', 'trans.account_id')
            ->join('wallets', 'trans.wallet_id', '=', 'wallets.id')
            ->join('users', 'users.id', '=', 'trans.user_id')
            ->select('trans.id', 'users.name', 'users.name', 'trans.title', 'trans.in', 'trans.out', 'accounts.account_name', 'wallets.wallet_name', 'wallets.saldo', 'wallets.prev_saldo', 'trans.created_at')
            ->get();

        return $trans;
    }


    public function store()
    {
        // request()->validate([
        //     'name' => 'required',
        //     'email' => 'required|unique:dispens,email',
        //     'password' => 'required|min:8',
        // ]);
        $data = [];

        //insert dompet
        $k = DB::table('wallets')->where('id', '=', request('wallet'))->first();

        if( request()->has('in')){
            $wallet = Wallet::create([
                'wallet_type' => $k->wallet_type,
                'wallet_name' => $k->wallet_name,
                'prev_saldo' => $k->saldo,
                'saldo' => $k->saldo + request('in'),
            ]);
        }
        else{
            $wallet = Wallet::create([
                'wallet_type' => $k->wallet_type,
                'wallet_name' => $k->wallet_name,
                'prev_saldo' => $k->saldo,
                'saldo' => $k->saldo - request('out'),
            ]);
        }

        // insert transaksi
        $trans = Trans::create([
            'wallet_id' => $wallet->id,
            'desc' => request('desc'),
            'operator_id' => request('operator'),
            'account_id' => request('account'),
            'in' => request()->has('in') ? request('in') : 0,
            'out' => request()->has('out') ? request('out') : 0,
        ]);
        //insert buku besar
        $big = Ledger::create([
            'ledgerable_id' => $trans->id,
            'ledgerable_type' => Trans::class,
        ]);
        array_push($data, $trans);
        array_push($data, $big);
        array_push($data, $wallet);
        return $data;
    }

    public function update($id)
    {
        //     request()->validate([
        //         'name' => 'required',
        //         'peiod' => 'required|unique:dispens,email,' . $dispen->id,
        //         'password' => 'sometimes|min:8',
        //     ]);
        $wal =  Trans::where('id', '=', request('id'))->first();
        $data = $wal->update([

            'prev_saldo' => $wal->saldo,
            'wallet_name' => request('name'),
            'saldo' => request('saldo'),
        ]);

        return $data;
    }
    public function bulkDelete()
    {
        Trans::whereIn('id', request('ids'))->delete();

        return response()->json(['message' => 'Transs deleted successfully!']);
    }
}
