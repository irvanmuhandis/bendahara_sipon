<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Pay;
use App\Models\Bill;
use App\Models\Debt;
use App\Models\Trans;
use App\Models\Ledger;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Http;

class PayController extends Controller

{
    public function index()
    {
        return  Pay::with(['wallet', 'user'])
            ->get();
    }


    public function show($id)
    {
        return Pay::where('id', '=', $id)->get();
    }

    public function indexDebt()
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
        $data = Pay::where('acc_pays.payable_type', '=', Debt::class)
            ->whereHas('santridebt', function ($query) use ($searchQuery) {
                $query->where('fullname', 'like', "%{$searchQuery}%")
                ->where('option',1);
            })
            ->with(['payable.account', 'payable.santri', 'wallet', 'operator'])
            ->orderBy($fil, $req)
            ->paginate(20);

        return $data;
    }

    public function indexBill()
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
        $data = Pay::where('acc_pays.payable_type', '=', Bill::class)
            ->whereHas('santribill', function ($query) use ($searchQuery) {
                $query->where('fullname', 'like', "%{$searchQuery}%")
                ->where('option',1);
            })
            ->with(['payable.account', 'payable.santri', 'wallet', 'operator'])
            ->orderBy($fil, $req)
            ->paginate(20);

        return $data;
    }

    public function store_bill()
    {
        // request()->validate([
        //     'user' => 'required',
        //     'email' => 'required|unique:dispens,email',
        //     'password' => 'required|min:8',
        //     ''
        // ]);

        $nis = json_decode(Cookie::get('sipon_session'))->nis;
        $token = json_decode(Cookie::get('sipon_session'))->token;
        $response = Http::withHeaders([
            'Accept' => 'aplication/json',
            'Authorization' => 'Bearer ' . $token,
        ])->get('https://sipon.kyaigalangsewu.net/api/v1/user/' . $nis);
        $operator = $response->json()['data'];

        $bills = request('bill');
        $pay = request('payment');
        $remainder = request('remainder');
        $k = request('wallet');


        $pay_bll = 0;
        $status = 0;
        $bill_rem = 0;

        $map = collect($remainder)->reduce(function ($carry, $item, $index) use ($bills) {
            $carry[] = ['remainder' => $item, 'bill' => $bills[$index]];
            return $carry;
        }, []);

        usort($map, function ($a, $b) {
            return $a['remainder'] <=> $b['remainder'];
        });

        $log = [];

        if ($map[0]['remainder'] > $pay) {
            if ($pay >= $map[0]['remainder']) {
                $pay = $pay - $map[0]['remainder'];
                $pay_bll = $map[0]['remainder'];
                $status = 3;
                $bill_rem = 0;
            } else {
                $pay_bll = $pay;
                $status = 2;
                $bill_rem = $map[0]['remainder'] - $pay;
            }

            Bill::where('id', '=', $map[0]['bill'])
                ->update(
                    [
                        'payment_status' => $status,
                        'remainder' => $bill_rem
                    ]
                );

            $p = Bill::where('id', '=', $map[0]['bill'])->first();

            //insert ke dompet



            $wallet = Wallet::create([
                'wallet_type' => $k['wallet_type'],
                'wallet_name' => $k['wallet_name'],
                'debit' => $pay_bll,
                'credit' => 0
            ]);

            // insert ke pay
            $i =  Pay::create([
                'nis' => request('santri')['nis'],
                'payment' => $pay_bll,
                'wallet_id' => $wallet->id,
                'payable_id' =>  $map[0]['bill'],
                'payable_type' => Bill::class,
                'operator_id' => $operator['id'],
            ]);
            //inser ke buku besar

            $ledger = Ledger::create([
                'ledgerable_id' => $i->id,

                'ledgerable_type' => Pay::class
            ]);
            array_push($log, $i);
            array_push($log, $p);
            array_push($log, $ledger);
            array_push($log, $wallet);
        } else {
            foreach ($map as $item) {
                //update bill
                if ($pay >= $item['remainder']) {
                    $pay = $pay - $item['remainder'];
                    $pay_bll = $item['remainder'];
                    $status = 3;
                    $bill_rem = 0;
                } else {
                    $pay_bll = $pay;
                    $status = 2;
                    $bill_rem = $item['remainder'] - $pay;
                    $pay = 0;
                }
                Bill::where('id', '=', $item['bill'])
                    ->update(
                        [
                            'payment_status' => $status,
                            'remainder' => $bill_rem
                        ]
                    );
                $p = Bill::where('id', '=', $item['bill'])->first();

                //insert ke dompet


                $wallet = Wallet::create([
                    'wallet_type' => $k['wallet_type'],
                    'wallet_name' => $k['wallet_name'],
                    'debit' => $pay_bll,
                    'credit' => 0
                ]);

                // insert ke pay
                $i =  Pay::create([
                    'nis' => request('santri')['nis'],
                    'payment' => $pay_bll,
                    'wallet_id' => $wallet->id,
                    'payable_id' =>  $item['bill'],
                    'payable_type' => Bill::class,
                    'operator_id' => $operator['id'],
                ]);
                //inser ke buku besar

                $ledger = Ledger::create([
                    'ledgerable_id' => $i->id,

                    'ledgerable_type' => Pay::class
                ]);

                array_push($log, $i);
                array_push($log, $p);
                array_push($log, $ledger);
                array_push($log, $wallet);
            }
        }
        return $log;
    }

    public function store_debt()
    {
        // request()->validate([
        //     'user' => 'required',
        //     'email' => 'required|unique:dispens,email',
        //     'password' => 'required|min:8',
        //     ''
        // ]);
        $debts = request('debt');
        $pay = request('payment');
        $remainder = request('remainder');
        $k = request('wallet');
        // $cookieValue = request()->cookie('sipon_session');
        // $cookie = json_decode($cookieValue);
        // $id_user = $cookie->id;

        $nis = json_decode(Cookie::get('sipon_session'))->nis;
        $token = json_decode(Cookie::get('sipon_session'))->token;
        $response = Http::withHeaders([
            'Accept' => 'aplication/json',
            'Authorization' => 'Bearer ' . $token,
        ])->get('https://sipon.kyaigalangsewu.net/api/v1/user/' . $nis);
        $operator = $response->json()['data'];

        $log = [];
        $pay_bll = 0;
        $status = 0;
        $debt_rem = 0;

        $map = collect($remainder)->reduce(function ($carry, $item, $index) use ($debts) {
            $carry[] = ['remainder' => $item, 'debt' => $debts[$index]];
            return $carry;
        }, []);

        usort($map, function ($a, $b) {
            return $a['remainder'] <=> $b['remainder'];
        });
        if ($map[0]['remainder'] > $pay) {
            if ($pay >= $map[0]['remainder']) {
                $pay = $pay - $map[0]['remainder'];
                $pay_bll = $map[0]['remainder'];
                $status = 3;
                $debt_rem = 0;
            } else {
                $pay_bll = $pay;
                $status = 2;
                $debt_rem = $map[0]['remainder'] - $pay;
            }

            Debt::where('id', '=', $map[0]['debt'])
                ->update(
                    [
                        'payment_status' => $status,
                        'remainder' => $debt_rem
                    ]
                );

            $p = Debt::where('id', '=', $map[0]['debt'])->first();

            //insert ke dompet

            // $k = DB::table('wallets')->where('wallet_type', '=', Wallet::find(request('wallet'))->wallet_type)->orderBy('id', 'desc')->first();



            $wallet = Wallet::create([
                'wallet_type' => $k['wallet_type'],
                'wallet_name' => $k['wallet_name'],
                'debit' => $pay_bll,
                'credit' => 0,
            ]);

            // insert ke pay
            $i =  Pay::create([
                'nis' => request('santri')['nis'],
                'payment' => $pay_bll,
                'wallet_id' => $wallet->id,
                'payable_id' =>  $map[0]['debt'],
                'payable_type' => Debt::class,
                'operator_id' => $operator['id'],
            ]);
            //inser ke buku besar

            $ledger = Ledger::create([
                'ledgerable_id' => $i->id,
                'ledgerable_type' => Pay::class
            ]);
            array_push($log, $i);
            array_push($log, $p);
            array_push($log, $ledger);
            array_push($log, $wallet);
        } else {
            foreach ($map as $item) {
                //update debt
                if ($pay >= $item['remainder']) {
                    $pay = $pay - $item['remainder'];
                    $pay_bll = $item['remainder'];
                    $status = 3;
                    $debt_rem = 0;
                } else {
                    $pay_bll = $pay;
                    $status = 2;
                    $debt_rem = $item['remainder'] - $pay;
                }
                Debt::where('id', '=', $item['debt'])
                    ->update(
                        [
                            'payment_status' => $status,
                            'remainder' => $debt_rem
                        ]
                    );
                $p = Debt::where('id', '=', $item['debt'])->first();


                $wallet = Wallet::create([
                    'wallet_type' => request('wallet')['wallet_type'],
                    'wallet_name' => request('wallet')['wallet_name'],
                    'debit' => $pay_bll,
                    'credit' => 0
                ]);

                //insert pay
                $q = Pay::create([
                    'nis' => request('santri')['nis'],
                    'payment' => $pay_bll,
                    'wallet_id' => $wallet->id,
                    'payable_id' =>  $item['debt'],
                    'payable_type' => Debt::class,
                    'operator_id' => $operator['id']
                ]);
                //insert buku besar

                $big = Ledger::create([
                    'ledgerable_id' => $q->id,
                    'ledgerable_type' => Pay::class,
                ]);

                array_push($log, $q);
                array_push($log, $p);
                array_push($log, $big);
                array_push($log, $wallet);
            }
        }

        array_push($log, $map);
        return $log;
    }

    public function update(Pay $dispen)
    {
        //     request()->validate([
        //         'name' => 'required',
        //         'peiod' => 'required|unique:dispens,email,' . $dispen->id,
        //         'password' => 'sometimes|min:8',
        //     ]);
        // dd(request());
        $log = [];
        $nis = json_decode(Cookie::get('sipon_session'))->nis;
        $token = json_decode(Cookie::get('sipon_session'))->token;
        $response = Http::withHeaders([
            'Accept' => 'aplication/json',
            'Authorization' => 'Bearer ' . $token,
        ])->get('https://sipon.kyaigalangsewu.net/api/v1/user/' . $nis);
        $operator = $response->json()['data'];


        $data = '';
        $pay = Pay::where('id', '=', request('id'))->first();
        $wallet = Wallet::where('id', '=', request('wallet')['id'])->first();
        $ledger = Ledger::where('ledgerable_id', request('id'))->first();
        if (request('type') == Bill::class) {
            $data = Bill::where('id', '=', request('bill')['id'])->first();
        } else {
            $data = Debt::where('id', '=', request('debt')['id'])->first();
        }
        if (request('remain') != 0) {
            if ($data->amount == request('remain')) {
                $status = 1;
            } else {
                $status = 2;
            }
        } else {
            $status = 3;
        }
        $data->update([
            'remainder' => request('remain'),
            'payment_status' => $status
        ]);
        $pay->update([
            'operator_id' => $operator['id'],
            'created_at' => request('date'),
            'updated_at' => request('date'),
            'payment' => request('paymentAft'),
        ]);
        $ledger->update([
            'created_at' => request('date'),
            'updated_at' => request('date')
        ]);
        $wallet->update([
            'debit' => request('paymentAft'),
            'credit' => 0,
        ]);
        array_push($log, $pay);
        array_push($log, $data);
        array_push($log, $wallet);
        array_push($log, $ledger);
        return $log;
    }
    public function bulkDelete()
    {
        $log = [];

        foreach (request('pay') as $update) {
            $data = '';
            if ($update['payable_type'] == Bill::class) {
                $data = Bill::where('id', '=', $update['payable_id'])->first();
            } else {
                $data = Debt::where('id', '=', $update['payable_id'])->first();
            }
            $data->update([
                'remainder' => $data['remainder'] + $update['payment'],
            ]);


            $pay = Pay::where('id', $update['id'])->delete();
            $ledger = Ledger::where('ledgerable_type', '=', Pay::class)
                ->where('ledgerable_id', $update['id'])
                ->delete();
            $wallet = Wallet::where('id', request('wall_ids'))
                ->delete();
            array_push($log, $data);
            array_push($log, $pay);
            array_push($log, $ledger);
            array_push($log, $wallet);
        }

        return $log;
    }
    public function destroy($id)
    {
        $r = Pay::where('id', '=', $id)->delete();

        return response()->json();
    }

    public function search()
    {
        $searchQuery = request('query');

        $group = Pay::where('name', 'like', "%{$searchQuery}%")->latest()->paginate(3);
        return response()->json($group);
    }
}
