<?php

namespace App\Http\Controllers;

use App\Models\Trans;
use App\Models\Ledger;
use App\Models\Wallet;
use App\Models\BigBook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Http;

class TransController extends Controller
{
    public function index()
    {
        $trans = Trans::with(['account', 'wallet', 'operator'])->get();

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
 $nis = json_decode(Cookie::get('sipon_session'))->nis;
        $token = json_decode(Cookie::get('sipon_session'))->token;
        $response = Http::withHeaders([
                'Accept' => 'aplication/json',
                'Authorization' => 'Bearer ' . $token,
            ])->get('https://sipon.kyaigalangsewu.net/api/v1/user/'.$nis);
        $operator=$response->json()['data'];

        if (request()->has('in')) {
            $wallet = Wallet::create([
                'wallet_type' => request('wallet')['wallet_type'],
                'wallet_name' => request('wallet')['wallet_name'],
                'debit' => request('in'),
                'credit' => 0,

            ]);
        } else {
            $wallet = Wallet::create([
                'wallet_type' => request('wallet')['wallet_type'],
                'wallet_name' => request('wallet')['wallet_name'],
                'debit' => 0,
                'credit' => request('out'),

            ]);
        }

        // insert transaksi
        $trans = Trans::create([
            'wallet_id' => $wallet->id,
            'desc' => request('desc'),
            'operator_id' => $operator['id'],
            'account_id' => request('account')['id'],
            'debit' => request()->has('in') ? request('in') : 0,
            'credit' => request()->has('out') ? request('out') : 0,
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
        $log = [];
        $tans =  Trans::where('id', '=', request('id'))->first();
        $wall =  Wallet::where('id', '=', request('wallet')['id'])->first();
        $data = $tans->update([
            'desc' => request('desc'),
            'debit' => request('type') == 'debit' ? request('price') : $tans->debit,
            'credit' => request('type') != 'debit' ? request('price') : $tans->credit,
        ]);
        $walls = $wall->update([
            'debit' => request('type') == 'debit' ? request('price') : $tans->debit,
            'credit' => request('type') != 'debit' ? request('price') : $tans->credit,
        ]);
        array_push($log, $data);
        array_push($log, $walls);
        return $log;
    }

    public function bulkDelete()
    {
        // dd(request());
        foreach (request('datas') as $data) {

            Trans::find($data['id'])->delete();
            Ledger::where('ledgerable_type', '=', Trans::class)
                ->where('ledgerable_id', $data['id'])
                ->first()
                ->delete();
            Wallet::find($data['wallet_id'])
                ->delete();

        }
        return response()->json(['message' => 'Transaksi eksternal berhasil dihapus!']);
    }
}
