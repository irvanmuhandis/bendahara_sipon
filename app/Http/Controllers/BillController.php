<?php

namespace App\Http\Controllers;

use DateTime;
use Carbon\Carbon;
use App\Models\Bill;
use App\Models\User;
use App\Models\Santri;
use App\Models\Account;
use App\Enums\PayStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cookie;


class BillController extends Controller
{

    public function santri()
    {
        $searchQuery = request('search');

        $bill = Santri::where('fullname', 'like', "%{$searchQuery}%")
            ->where('option', 1)
            ->whereHas('bill', function ($query) {
                $query->where('payment_status', '<', 3)
                    ->where('month', null);
            })
            ->with(['bill' => function ($query) {
                $query->where('payment_status', '<', 3)
                    ->where('month', null)
                    ->orderBy('title', 'asc');
            }])
            ->withSum('bill as sum_amount', 'remainder')
            ->orderBy('nis', 'asc')
            ->get();

        $sum = $bill->sum('sum_amount');

        return response()->json([
            'data' => $bill,
            'sum' => $sum
        ]);
    }


    public function index()
    {
        $fil = request('filter');
        $req = request('value');
        $searchQuery = request('query');
        $mode = request('mode');

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
        $bill = Bill::whereHas('santri', function ($query) use ($searchQuery) {
            $query->where('fullname', 'like', "%{$searchQuery}%")
                ->where('option', 1);
        })
            ->when($mode == 'period', function ($query) {
                $query->where('title', null)
                    ->orWhere('title', "");
            })
            ->when($mode == 'nonperiod', function ($query) {
                $query->where('month', null)
                    ->orWhere('month', null);
            })
            ->with(['santri', 'operator', 'account'])
            ->orderBy($fil, $req)
            ->paginate(25);

        return $bill;
    }

    public function store_single()
    {
        // request()->validate([
        //     'name' => 'required',
        //     'email' => 'required|unique:dispens,email',
        //     'password' => 'required|min:8',
        // ]);
        $nis = json_decode(Cookie::get('sipon_session'))->nis;
        $token = json_decode(Cookie::get('sipon_session'))->token;
        $response = Http::withHeaders([
            'Accept' => 'aplication/json',
            'Authorization' => 'Bearer ' . $token,
        ])->get('https://sipon.kyaigalangsewu.net/api/v1/user/' . $nis);
        $operator = $response->json()['data'];
        $log = [];
        if (request('account')) {
            foreach (request('santri') as $user) {
                $bill = Bill::create([
                    'account_id' => request('account')['id'],
                    'nis' => $user['nis'],

                    'operator_id' => $operator['id'],
                    'amount' => request('price'),
                    'remainder' => request('price'),
                    'payment_status' =>  1,
                    'month' => request('period'),
                ]);
                array_push($log, $bill);
            }
        } else {
            foreach (request('periodic') as $account) {
                if ($account['value'] != '') {
                    foreach (request('santri') as $user) {
                        $bill = Bill::create([
                            'account_id' => $account['id'],
                            'nis' => $user['nis'],

                            'operator_id' => $operator['id'],
                            'amount' => $account['value'],
                            'remainder' => $account['value'],
                            'payment_status' =>  1,
                            'month' => request('period'),
                        ]);
                        array_push($log, $bill);
                    }
                }
            }
        }
        return $log;
    }

    public function store_singleRange()
    {
        // request()->validate([
        //     'name' => 'required',
        //     'email' => 'required|unique:dispens,email',
        //     'password' => 'required|min:8',
        // ]);
        $nis = json_decode(Cookie::get('sipon_session'))->nis;
        $token = json_decode(Cookie::get('sipon_session'))->token;
        $response = Http::withHeaders([
            'Accept' => 'aplication/json',
            'Authorization' => 'Bearer ' . $token,
        ])->get('https://sipon.kyaigalangsewu.net/api/v1/user/' . $nis);
        $operator = $response->json()['data'];
        $period_start = request('period_start');
        $period_end = request('period_end');
        $log = [];
        if (request('account')) {
            foreach (request('santri') as $user) {
                for ($month = Carbon::parse($period_start); $month->lte(Carbon::parse($period_end)); $month->addMonth()) {
                    $bill = Bill::create([
                        'account_id' => request('account')['id'],
                        'nis' => $user['nis'],
                        'operator_id' => $operator['id'],
                        'amount' => request('price'),
                        'remainder' => request('price'),
                        'payment_status' =>  1,
                        'month' => $month->format('Y-m'),
                    ]);
                    array_push($log, $bill);
                }
            }
        } else {
            foreach (request('periodic') as $account) {
                if ($account['value'] != '') {
                    foreach (request('santri') as $user) {
                        for ($month = Carbon::parse($period_start); $month->lte(Carbon::parse($period_end)); $month->addMonth()) {
                            $bill = Bill::create([
                                'account_id' => $account['id'],
                                'nis' => $user['nis'],
                                'operator_id' => $operator['id'],
                                'amount' => $account['value'],
                                'remainder' => $account['value'],
                                'payment_status' =>  1,
                                'month' => $month->format('Y-m'),
                            ]);
                            array_push($log, $bill);
                        }
                    }
                }
            }
        }

        return $log;
    }

    public function store_nonperiod()
    {
        // request()->validate([
        //     'name' => 'required',
        //     'email' => 'required|unique:dispens,email',
        //     'password' => 'required|min:8',
        // ]);
        $nis = json_decode(Cookie::get('sipon_session'))->nis;
        $token = json_decode(Cookie::get('sipon_session'))->token;
        $response = Http::withHeaders([
            'Accept' => 'aplication/json',
            'Authorization' => 'Bearer ' . $token,
        ])->get('https://sipon.kyaigalangsewu.net/api/v1/user/' . $nis);
        $operator = $response->json()['data'];
        $log = [];
        foreach (request('santri') as $user) {
            $bill = Bill::create([
                'account_id' => request('account')['id'],
                'nis' => $user['nis'],

                'operator_id' => $operator['id'],
                'amount' => request('price'),
                'remainder' => request('price'),
                'payment_status' =>  1,
                'month' => null,
                'title' => request('title')
            ]);
            array_push($log, $bill);
        }

        return $log;
    }

    public function update()
    {
        //     request()->validate([
        //         'name' => 'required',
        //         'peiod' => 'required|unique:dispens,email,' . $dispen->id,
        //         'password' => 'sometimes|min:8',
        //     ]);

        $nis = json_decode(Cookie::get('sipon_session'))->nis;
        $token = json_decode(Cookie::get('sipon_session'))->token;
        $response = Http::withHeaders([
            'Accept' => 'aplication/json',
            'Authorization' => 'Bearer ' . $token,
        ])->get('https://sipon.kyaigalangsewu.net/api/v1/user/' . $nis);
        $user = $response->json()['data'];
        $bill = Bill::where('id', '=', request('id'))->first();
        // dd(request());
        $bill->update([
            'operator_id' => $user['id'],
            'account_id' => request('account')['id'],
            'amount' => request('price'),
            'remainder' => request('remain'),
            'nis' => request('santri')['nis'],
            'month' => request('period'),
        ]);

        return $bill;
    }
    public function bulkDelete()
    {
        $a = Bill::whereIn('id', request('ids'))->delete();
        return response()->json(['message' => $a . ' tagihan terhapus ']);
    }

    public function deleteDay()
    {
        $DaysAgo = Carbon::now()->subDays(request('type'));

        $del = Bill::whereBetween('created_at', [$DaysAgo, Carbon::now()])
            ->delete();


        return response()->json(['message' => $del]);
    }

    public function deleteHour()
    {

        $Ago = Carbon::now()->subHours(request('type'));

        $del = Bill::whereBetween('created_at', [$Ago, Carbon::now()])
            ->delete();

        return response()->json(['message' => $del]);
    }

    public function deleteMany()
    {
        $data = Bill::orderBy('id', 'desc')->take(request('type'))->get();

        foreach ($data as $row) {
            $row->delete();
        }

        return response()->json(['message' => `Last ` + request('type') + ` rows deleted successfully`]);
    }

    public function destroy(Bill $dispen)
    {
        $dispen->delete();

        return response()->noContent();
    }
}
