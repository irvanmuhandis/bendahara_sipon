<?php

namespace App\Http\Controllers;

use DateTime;
use Carbon\Carbon;
use App\Models\Bill;
use App\Models\User;
use App\Models\Account;
use App\Enums\PayStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class BillController extends Controller
{
    public function index()
    {
        $bill = DB::table('bills')
            ->join('users', 'users.id', '=', 'bills.user_id')
            ->join('users as operator', 'operator.id', '=', 'bills.operator_id')
            ->join('accounts', 'accounts.id', '=', 'bills.account_id')
            ->select('operator.name as operator','bills.updated_at', 'bills.created_at', 'bills.id', 'bills.payment_status', 'bills.bill_amount', 'bills.due_date', 'bills.bill_remainder', 'bills.due_date', 'users.name', 'accounts.account_name')
            ->orderBy('bills.id', 'desc')->get();

        $bill = $bill->map(function ($item, $key) {
            $paymentStatus = PayStatus::from($item->payment_status);
            $item->payment_status = $paymentStatus->names();
            $item->status_color = $paymentStatus->color();
            return $item;
        });
        return $bill;
    }

    public function store_group()
    {
        // request()->validate([
        //     'name' => 'required',
        //     'email' => 'required|unique:dispens,email',
        //     'password' => 'required|min:8',
        // ]);

        $users = User::where('group_id', '=', request('group'))->get();

        foreach ($users as $user) {
            $bill = Bill::create([
                'account_id' => request('account'),
                'user_id' => $user->id,
                'bill_amount' => request('price'),
                'bill_remainder' => request('price'),
                'payment_status' =>  1,
                'due_date' => request('period'),
            ]);
        }


        return request();
    }

    public function store_groupRange()
    {
        // request()->validate([
        //     'name' => 'required',
        //     'email' => 'required|unique:dispens,email',
        //     'password' => 'required|min:8',
        // ]);
        $users = User::where('group_id', '=', request('group'))->get();
        $period_start = request('period_start');
        $period_end = request('period_end');

        foreach ($users as $user) {
            for ($month = Carbon::parse($period_start); $month->lte(Carbon::parse($period_end)); $month->addMonth()) {
                $bill = Bill::create([
                    'account_id' =>  request('account'),
                    'user_id' => $user->id,
                    'bill_amount' => request('price'),
                    'bill_remainder' => request('price'),
                    'payment_status' =>  1,
                    'due_date' => $month->format('Y-m'),
                ]);
            }
        }

        return request();
    }


    public function store_groupMult()
    {
        // request()->validate([
        //     'name' => 'required',
        //     'email' => 'required|unique:dispens,email',
        //     'password' => 'required|min:8',
        // ]);

        $users = User::where('group_id', '=', request('group'))->get();

        foreach ($users as $user) {
            Bill::create([
                'account_id' => Account::where('account_name', '=', 'Syahriah')->first()->id,
                'user_id' => $user->id,
                'bill_amount' => request('syah'),
                'bill_remainder' => request('syah'),
                'payment_status' =>  1,
                'due_date' => request('period'),
            ]);
        }

        foreach ($users as $user) {
            Bill::create([
                'account_id' => Account::where('account_name', '=', 'Wifi')->first()->id,
                'user_id' => $user->id,
                'bill_amount' => request('wifi'),
                'bill_remainder' => request('wifi'),
                'payment_status' =>  1,
                'due_date' => request('period'),
            ]);
        }

        foreach ($users as $user) {
            Bill::create([
                'account_id' => Account::where('account_name', '=', 'Madin')->first()->id,
                'user_id' => $user->id,
                'bill_amount' => request('madin'),
                'bill_remainder' => request('madin'),
                'payment_status' =>  1,
                'due_date' => request('period'),
            ]);
        }

        return request();
    }

    public function store_groupRangeMult()
    {
        // request()->validate([
        //     'name' => 'required',
        //     'email' => 'required|unique:dispens,email',
        //     'password' => 'required|min:8',
        // ]);
        $users = User::where('group_id', '=', request('group'))->get();
        $period_start = request('period_start');
        $period_end = request('period_end');


        foreach ($users as $user) {
            for ($month = Carbon::parse($period_start); $month->lte(Carbon::parse($period_end)); $month->addMonth()) {
                $bill = Bill::create([
                    'account_id' => Account::where('account_name', '=', 'Madin')->first()->id,
                    'user_id' => $user->id,
                    'bill_amount' => request('madin'),
                    'bill_remainder' => request('madin'),
                    'payment_status' =>  1,
                    'due_date' => $month->format('Y-m'),
                ]);
            }
        }

        foreach ($users as $user) {
            for ($month = Carbon::parse($period_start); $month->lte(Carbon::parse($period_end)); $month->addMonth()) {

                $bill = Bill::create([
                    'account_id' =>  Account::where('account_name', '=', 'Syahriah')->first()->id,
                    'user_id' => $user->id,
                    'bill_amount' => request('syah'),
                    'bill_remainder' => request('syah'),
                    'payment_status' =>  1,
                    'due_date' => $month->format('Y-m'),
                ]);
            }
        }

        foreach ($users as $user) {
            for ($month = Carbon::parse($period_start); $month->lte(Carbon::parse($period_end)); $month->addMonth()) {
                $bill = Bill::create([
                    'account_id' =>  Account::where('account_name', '=', 'Wifi')->first()->id,
                    'user_id' => $user->id,
                    'bill_amount' => request('wifi'),
                    'bill_remainder' => request('wifi'),
                    'payment_status' =>  1,
                    'due_date' => $month->format('Y-m'),
                ]);
            }
        }

        return request();
    }

    public function store_single()
    {
        // request()->validate([
        //     'name' => 'required',
        //     'email' => 'required|unique:dispens,email',
        //     'password' => 'required|min:8',
        // ]);
        foreach (request('user') as $user) {
            $bill = Bill::create([
                'account_id' => request('account'),
                'operator_id' => request('operator'),
                'user_id' => $user,
                'bill_amount' => request('price'),
                'bill_remainder' => request('price'),
                'payment_status' =>  1,
                'due_date' => request('period'),
            ]);
        }
        return request();
    }

    public function store_singleRange()
    {
        // request()->validate([
        //     'name' => 'required',
        //     'email' => 'required|unique:dispens,email',
        //     'password' => 'required|min:8',
        // ]);
        $period_start = request('period_start');
        $period_end = request('period_end');
        foreach (request('user') as $user) {
            for ($month = Carbon::parse($period_start); $month->lte(Carbon::parse($period_end)); $month->addMonth()) {
                $bill = Bill::create([
                    'account_id' => request('account'),
                    'user_id' => $user,
                    'operator_id' => request('operator'),
                    'bill_amount' => request('price'),
                    'bill_remainder' => request('price'),
                    'payment_status' =>  1,
                    'due_date' => $month->format('Y-m'),
                ]);
            }
        }
        return request();
    }

    public function update(Bill $dispen)
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
        Bill::whereIn('id', request('ids'))->delete();

        return response()->json(['message' => 'Bills deleted successfully!']);
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

        return response()->json(['message' => `Last `+request('type')+` rows deleted successfully`]);
    }

    public function destroy(Bill $dispen)
    {
        $dispen->delete();

        return response()->noContent();
    }

    public function search()
    {
        $searchQuery = request('query');

        $group = Bill::where('name', 'like', "%{$searchQuery}%")->latest()->paginate(3);
        return response()->json($group);
    }
}
