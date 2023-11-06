<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Debt;
use App\Models\Trans;
use App\Models\Ledger;
use App\Models\Santri;
use App\Models\Wallet;
use App\Enums\PayStatus;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cookie;

class DebtController extends Controller
{

    public function santri()
    {

        $searchQuery = request('search');
        $debt = Santri::where('fullname', 'like', "%{$searchQuery}%")
            ->whereHas('debt', function ($query) {
                $query->where('payment_status', '<', 3)
                ->where('option',1);
            })
            ->with(['debt' => function ($query) {
                $query->where('payment_status', '<', 3);
            }])
            ->withSum('debt as sum_amount', 'remainder')
            ->orderBy('nis', 'desc')->get();

        $sum = $debt->sum('sum_amount');

        return response()->json([
            'data' => $debt,
            'sum' => $sum
        ]);
    }

    public function store()
    {
        // request()->validate([
        //     'name' => 'required',
        //     'email' => 'required|unique:dispens,email',
        //     'password' => 'required|min:8',
        // ]);
        $log = [];
         $nis = json_decode(Cookie::get('sipon_session'))->nis;
        $token = json_decode(Cookie::get('sipon_session'))->token;
        $response = Http::withHeaders([
                'Accept' => 'aplication/json',
                'Authorization' => 'Bearer ' . $token,
            ])->get('https://sipon.kyaigalangsewu.net/api/v1/user/'.$nis);
        $operator=$response->json()['data'];
        foreach (request('santri') as $santri) {


            $wallet = Wallet::create([
                'wallet_type' => request('wallet')['wallet_type'],
                'wallet_name' => request('wallet')['wallet_name'],
                'debit' => 0,
                'credit' => request('price'),
            ]);

            $debt = Debt::create([
                'account_id' => request('account')['id'],
                'wallet_id' => $wallet->id,
                'nis' => $santri['nis'],
                'operator_id' => $operator['id'],
                'amount' => request('price'),
                'remainder' => request('price'),
                'payment_status' =>  1,
                'title' => request('title'),
            ]);


            $ledger = Ledger::create([
                'ledgerable_id' => $debt->id,
                'ledgerable_type' => Debt::class,
            ]);

            array_push($log, $debt);
            array_push($log, $wallet);
            array_push($log, $ledger);
        }


        return $log;
    }

    public function search()
    {
        $debtorName = request('query');
        $status = request('status');
        $fil = request('filter');
        $req = request('value');

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


        $debts = Debt::query()
            ->with(['santri', 'operator', 'account'])
            ->when($debtorName, function ($query) use ($debtorName) {
                return $query->whereHas('santri', function ($q) use ($debtorName) {
                    $q->where('fullname', 'LIKE', "%$debtorName%")
                    ->where('option',1);
                });
            })
            ->when($status, function ($query) use ($status) {
                return $query->where('payment_status', PayStatus::from($status));
            })
            ->orderBy($fil, $req)
            ->paginate();

        return response()->json($debts);
    }




    public function bulkDelete()
    {
        Debt::whereIn('id', request('ids'))->delete();
        Ledger::where('ledgerable_type', '=', Debt::class)
        ->whereIn('ledgerable_id', request('ids'))
        ->delete();
        Wallet::whereIn('id', request('wall_ids'))
        ->delete();
        return response()->json(['message' => 'Hutang berhasil dihapus!']);
    }

    public function destroy($debt)
    {
        Debt::where('id', request('id'))->delete();
        Wallet::where('id', request('wallet_id'))->delete();
        Ledger::where('ledgerable_id', '=', request('id'))
            ->where('ledgerable_type', '=', Debt::class)
            ->delete();

        return response()->noContent();
    }

    public function update(Debt $debt)
    {
        //     request()->validate([
        //         'name' => 'required',
        //         'peiod' => 'required|unique:dispens,email,' . $debt->id,
        //         'password' => 'sometimes|min:8',
        //     ]);

        $log = [];
 $nis = json_decode(Cookie::get('sipon_session'))->nis;
        $token = json_decode(Cookie::get('sipon_session'))->token;
        $response = Http::withHeaders([
                'Accept' => 'aplication/json',
                'Authorization' => 'Bearer ' . $token,
            ])->get('https://sipon.kyaigalangsewu.net/api/v1/user/'.$nis);
        $operator=$response->json()['data'];
        $debt = Debt::where('id', '=', request('id'))->first();
        $wallet = Wallet::where('id', '=', request('wallet_id'))->first();
        // dd(request());

        $debt->update([
            'operator_id' => $operator['id'],
            'nis' => request('santri')['nis'],
            'amount' => request('price'),
            'remainder' => request('remain'),
            'title' => request('title'),
            'account_id' => request('account')['id'],
        ]);
        if (request('price') == request('remain')) {
            $debt->update([
                'payment_status' => 1
            ]);
        }

        if (request('price') != request('remain')) {
            $debt->update([
                'payment_status' => 2
            ]);
        }
        $wallet->update([
            'debit' => 0,
            'credit' => request('price'),
        ]);

        array_push($log, $debt);
        array_push($log, $wallet);
        return $log;
    }

    public function deleteDay()
    {
        $DaysAgo = Carbon::now()->subDays(request('type'));

        $del = Debt::whereBetween('created_at', [$DaysAgo, Carbon::now()])
            ->delete();


        return response()->json(['message' => $del]);
    }

    public function deleteHour()
    {

        $Ago = Carbon::now()->subHours(request('type'));

        $del = Debt::whereBetween('created_at', [$Ago, Carbon::now()])
            ->delete();

        return response()->json(['message' => $del]);
    }

    public function deleteMany()
    {
        $data = Debt::orderBy('id', 'desc')->take(request('type'))->get();

        foreach ($data as $row) {
            $row->delete();
        }

        return response()->json(['message' => `Last ` + request('type') + ` rows deleted successfully`]);
    }
}
