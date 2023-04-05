<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Pay;
use App\Models\Bill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class PayController extends Controller

{
    public function index()
    {
        return  DB::table('pays')
            ->join('wallets', 'pays.wallet_id', '=', 'wallets.id')
            ->join('users', 'users.id', '=', 'pays.user_id')
            ->select('pays.id', 'pays.wallet_id', 'users.name', 'pays.user_id', 'pays.payment', 'pays.payable_type', 'pays.payable_id', 'wallets.wallet_name', 'wallets.saldo', 'wallets.prev_saldo', 'pays.created_at','pays.updated_at')
            ->get();
    }


    public function show($id)
    {
        return DB::table('pays')->where('id', '=', $id)->get();
    }

    public function indexDebt()
    {
        $pay = DB::table('pays')
            ->join('debts', 'debts.id', '=', 'pays.payable_id')
            ->join('accounts', 'accounts.id', '=', 'debts.account_id')
            ->join('wallets', 'pays.wallet_id', '=', 'wallets.id')
            ->join('users', 'users.id', '=', 'debts.user_id')
            ->select('pays.id', 'users.name', 'pays.payment', 'debts.debt_status', 'accounts.account_name', 'debts.debt', 'debts.debt_remainder', 'wallets.wallet_name', 'wallets.saldo', 'wallets.prev_saldo')
            ->get();

        return $pay;
    }

    public function indexBill()
    {
        $pay = DB::table('pays')
            ->join('bills', 'bills.id', '=', 'pays.payable_id')
            ->join('wallets', 'pays.wallet_id', '=', 'wallets.id')
            ->join('accounts', 'accounts.id', '=', 'bills.account_id')
            ->join('users', 'users.id', '=', 'bills.user_id')
            ->select('pays.id', 'users.name', 'pays.payment', 'bills.payment_status', 'accounts.account_name', 'bills.bill_amount', 'bills.bill_remainder', 'wallets.wallet_name', 'wallets.saldo', 'wallets.prev_saldo')
            ->get();

        return $pay;
    }

    public function store()
    {
        $dates = request('date');
        $date = Carbon::createFromFormat('Y-m-d', $dates);
        // request()->validate([
        //     'user' => 'required',
        //     'email' => 'required|unique:dispens,email',
        //     'password' => 'required|min:8',
        //     ''
        // ]);
        $bills = request('bill_id');
        $pay = request('payment');
        $remainder = request('remainder');

        $pay_bll = 0;
        $status = 0;
        $bill_rem = 0;

        $map = collect($remainder)->reduce(function ($carry, $item, $index) use ($bills) {
            $carry[] = ['remainder' => $item, 'bill_id' => $bills[$index]];
            return $carry;
        }, []);

        usort($map, function ($a, $b) {
            return $a['remainder'] <=> $b['remainder'];
        });

        foreach ($map as $item) {

            if ($pay > $item['remainder']) {
                $pay = $pay - $item['remainder'];
                $pay_bll = $item['remainder'];
                $status = 3;
                $bill_rem = 0;
            } else {
                $pay_bll = $pay;
                $status = 2;
                $bill_rem = $item['remainder'] - $pay;
            }
            DB::table('bills')->where('id', '=', $item['bill_id'])
                ->update(
                    [
                        'payment_status' => $status,
                        'bill_remainder' => $bill_rem
                    ]
                );

            Pay::create([
                'user_id' => request('user_id'),
                'payment' => $pay_bll,
                'wallet_id' => request('wallet_id'),
                'payable_id' =>  $item['bill_id'],
                'payable_type' => Bill::class,
                'created_at' => $date,
                'updated_at' => $date
            ]);
        }

        return request();
    }
    public function update(Pay $dispen)
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
        Pay::whereIn('id', request('ids'))->delete();

        return response()->json(['message' => 'Pays deleted successfully!']);
    }
    public function destroy($id)
    {
        $r = DB::table('pays')->where('id', '=', $id)->delete();

        return response()->json();
    }

    public function search()
    {
        $searchQuery = request('query');

        $group = Pay::where('name', 'like', "%{$searchQuery}%")->latest()->paginate(3);
        return response()->json($group);
    }
}
