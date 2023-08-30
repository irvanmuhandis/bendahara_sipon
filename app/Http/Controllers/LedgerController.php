<?php

namespace App\Http\Controllers;

use App\Models\Pay;
use App\Models\Bill;
use App\Models\User;
use App\Models\Trans;
use App\Models\Ledger;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Debt;
use App\Models\Dispen;
use App\Models\Santri;
use App\Models\Wallet;
use Carbon\Carbon;
use DateTime;
use Illuminate\Pagination\LengthAwarePaginator;

use function Database\Seeders\wallet;

class LedgerController extends Controller
{

    public function index()
    {
        $fil = request('filter');
        $req = request('value');
        $mode = request('mode');
        $debit = request('debit');
        $searchQuery = request('query');
        if ($mode == Trans::class) {

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
            if ($debit == 1) {
                $data = Trans::where('desc', 'like', "%{$searchQuery}%")
                    ->where('debit', '>', 0)
                    ->with(['wallet', 'account', 'operator'])
                    ->orderBy($fil, $req)->paginate(25);
                return $data;
            } else {
                $data = Trans::where('desc', 'like', "%{$searchQuery}%")
                    ->where('credit', '>', 0)
                    ->with(['wallet', 'account', 'operator'])
                    ->orderBy($fil, $req)->paginate(25);
                return $data;
            }
        } else {

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
            $data = Ledger::where('ledgerable_type', '!=', Trans::class)
                ->with(['ledgerable.wallet', 'ledgerable.operator'])
                ->orderBy($fil, $req)->paginate(25);
            return $data;
        }
    }

    public function circulation()
    {
        // dd('sas');
        $start = '';
        $end = '';
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

        if (request('start') == '' || request('start') == '') {
            $start = Carbon::now()->subDays(30);
            $end = Carbon::now();
        } else {
            $start = Carbon::parse(request('start'));
            $start->format('Y-m-d 00:00:00');
            $end = Carbon::parse(request('end'));
            $end->setTime(23, 59, 59);
            $end->format('Y-m-d 23:59:59');
        }
        // dd(Ledger::first()->created_at);
        // dd($start, $end);
        // dd(request('start'));
        return Ledger::with(['ledgerable', 'ledgerable.santri'])
            ->with(['ledgerable.wallet' => function ($query) {
                $query
                    ->select(['created_at', 'id', 'wallet_name', 'wallet_type', 'debit', 'credit'])
                    ->selectRaw('(SELECT SUM(debit) - SUM(credit) FROM wallets AS w2 WHERE w2.id <= wallets.id AND w2.wallet_type = wallets.wallet_type) AS saldo');
            }])
            ->whereBetween('created_at', [$start, $end])
            ->orderBy($fil, $req)
            ->paginate(10);
    }

    public function show($id)
    {
        $data = Ledger::find($id)->ledgerable_type;
        if ($data == "App\\Models\\Pay") {
            $ledger = Ledger::with(['ledgerable.wallet', 'ledgerable.operator', 'ledgerable.payable.account', 'ledgerable.santri'])->findOrFail($id);
        } else {
            $ledger = Ledger::with(['ledgerable.wallet', 'ledgerable.operator', 'ledgerable.account'])->findOrFail($id);
        }

        return $ledger;
    }


    public function billing()
    {
        $searchQuery = request('search');
        $account = json_decode(request('account'), true);


        $query = Santri::where('fullname', 'like', "%{$searchQuery}%")
            ->with('bill.account')
            ->withCount(['bill as bill_count' => function ($bill) use ($account) {
                $bill
                    ->select(DB::raw('count(distinct(due_date))'))
                    ->whereBetween('due_date', [request('start'), request('end')])
                    ->whereIn('account_id', $account)
                    ->where('payment_status', '<', 3);
            }])
            ->with(['bill' => function ($query) use ($account) {
                $query->selectRaw('due_date,nis,sum(amount) as sum_amount,sum(remainder) as sum_remain,count(id) as count')
                    ->whereBetween('due_date', [request('start'), request('end')])
                    ->whereIn('account_id', $account)
                    ->where('payment_status', '<', 3)
                    ->groupBy('due_date')
                    ->groupBy('nis')
                    ->orderBy('due_date');
            }])
            ->withSum(['bill as sum_remain' => function ($bill) use ($account) {
                $bill
                    ->whereBetween('due_date', [request('start'), request('end')])
                    ->whereIn('account_id', $account)
                    ->where('payment_status', '<', 3);
            }], 'remainder')
            ->withSum(['bill as sum_amount' => function ($bill) use ($account) {
                $bill
                    ->whereBetween('due_date', [request('start'), request('end')])
                    ->whereIn('account_id', $account)
                    ->where('payment_status', '<', 3);
            }], 'amount')
            ->havingRaw('bill_count >= ?', [request('length')])
            ->get();

        return $query;
    }


    public function accountSum(Request $request)
    {
        $queryParams = $request->query();
        $start = $queryParams['start'];
        $end = $queryParams['end'];
        $today = Carbon::now();
        $today2 = Carbon::now();
        if ($start >= 0) {
            $strt =  $today->copy()->addMonth($start)->startOfMonth();
        } else {
            $strt = $today->copy()->subMonth(-$start)->startOfMonth();
        }
        if ($end >= 0) {
            $ends = $today2->copy()->addMonth($end)->lastOfMonth();
        } else {
            $ends = $today2->copy()->subMonth(-$end)->lastOfMonth();
        }
        // dd($strt);
        $bill = Account::where('account_type', '=', 2)
            ->withSum(['bill' => function ($query) use ($strt, $ends) {
                $query->whereBetween('created_at', [$strt, $ends]);
            }], 'amount')
            ->withSum(['bill' => function ($query) use ($strt, $ends) {
                $query->whereBetween('created_at', [$strt, $ends]);
            }], 'remainder')
            ->get();

        $debt =    Account::where('account_type', '=', 1)
            ->withSum(['debt' => function ($query) use ($strt, $ends) {
                $query->whereBetween('created_at', [$strt, $ends]);
            }], 'amount')
            ->withSum(['debt' => function ($query) use ($strt, $ends) {
                $query->whereBetween('created_at', [$strt, $ends]);
            }], 'remainder')->get();

        $other = Account::where('account_type', '=', 3)
            ->withSum(['trans' => function ($query) use ($strt, $ends) {
                $query->whereBetween('created_at', [$strt, $ends]);
            }], 'debit')
            ->withSum(['trans' => function ($query) use ($strt, $ends) {
                $query->whereBetween('created_at', [$strt, $ends]);
            }], 'credit')->get();
        return response()->json([
            'bill' => $bill,
            'debt' => $debt,
            'other' => $other
        ]);
    }

    public function walletSum()
    {
        $bill = Wallet::where('account_type', '=', 2)
            ->withSum('bill', 'amount')
            ->withSum('bill', 'remainder')
            ->get();

        $debt =    Account::where('account_type', '=', 1)
            ->withSum('debt', 'amount')
            ->withSum('debt', 'remainder')
            ->get();

        $other = Account::where('account_type', '=', 3)
            ->withSum('trans', 'debit')
            ->withSum('trans', 'credit')
            ->get();
        return response()->json([
            'bill' => $bill,
            'debt' => $debt,
            'other' => $other
        ]);
    }

    public function statistic()
    {
        $debt = Debt::where('payment_status', '<', '3')->count();
        $bill = Bill::where('payment_status', '<', '3')->count();
        $santri = Santri::count();
        $dispen = Dispen::where('status', '=', '1')->count();
        return response()->json([
            'debt' => $debt,
            'bill' => $bill,
            'santri' => $santri,
            'dispen' => $dispen
        ]);
    }


    public function inout(Request $request)
    {
        DB::enableQueryLog();
        // query
        // DB::getQueryLog();
        $queryParams = $request->query();
        // or $queryParams = $request->all();

        // Access and use the query parameters
        $start = $queryParams['start'];
        $end = $queryParams['end'];

        if ($start == '') {
            $start = Carbon::now()->startOfMonth();
        }
        if ($end == '') {
            $end = Carbon::now()->lastOfMonth();
        }

        $dateStart = explode('-', $start);
        $dateEnd = explode('-', $end);

        // dd($dateStart);
        $strt = Carbon::createFromDate($dateStart[0], $dateStart[1], 1);
        $ends = Carbon::createFromDate($dateEnd[0], $dateEnd[1], 1)->endOfMonth();

        // dd($strt, $ends);
        $pay =  Ledger::query()
            ->where('ledgerable_type', '=', Pay::class)
            ->join('acc_pays', 'acc_pays.id', '=', 'acc_ledgers.ledgerable_id')
            ->whereBetween('acc_pays.created_at', [$strt, $ends])
            ->select(
                DB::raw('sum(payment) as `sum`'),
                DB::raw("DATE_FORMAT(acc_pays.created_at, '%Y-%m') as date")
            )
            ->groupByRaw('date')
            ->orderBy('date')
            ->get();
        // dd(DB::getQueryLog());

        $trans =  Ledger::query()
            ->where('ledgerable_type', '=', Trans::class)
            ->join('trans', 'trans.id', '=', 'acc_ledgers.ledgerable_id')
            ->whereBetween('trans.created_at', [$strt, $ends])
            ->select(
                DB::raw('sum(debit) as `sum_debit`'),
                DB::raw('sum(credit) as `sum_credit`'),
                DB::raw("DATE_FORMAT(trans.created_at, '%Y-%m') as date")
            )
            ->groupByRaw('date')
            ->orderBy('date')
            ->get();

        $debt =  Ledger::query()
            ->where('ledgerable_type', '=', Debt::class)
            ->join('debts', 'debts.id', '=', 'acc_ledgers.ledgerable_id')
            ->whereBetween('debts.created_at', [$strt, $ends])
            ->select(
                DB::raw('sum(amount) as `debt`'),
                DB::raw("DATE_FORMAT(debts.created_at, '%Y-%m') as date")
            )
            ->groupByRaw('date')
            ->orderBy('date')
            ->get();

        return response()->json([
            'debt' => $debt,
            'trans' => $trans,
            'pay' => $pay
        ]);
    }
}
