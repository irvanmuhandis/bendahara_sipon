<?php

namespace App\Http\Controllers;

use DateTime;
use App\Models\Bill;
use App\Models\User;
use App\Enums\PayStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Account;

class BillController extends Controller
{
    public function index()
    {
        $bill = DB::table('bills')
            ->join('users', 'users.id', '=', 'bills.user_id')
            ->join('accounts', 'accounts.id', '=', 'bills.account_id')
            ->select('bills.updated_at', 'bills.created_at', 'bills.id', 'bills.payment_status', 'bills.bill_amount', 'bills.bill_remainder', 'bills.due_date', 'users.name', 'accounts.account_name')
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
        $period_start = new DateTime(request('period_start'));
        $period_end = new DateTime(request('period_end'));

        $period_end->modify('+1 month');

        foreach ($users as $user) {
            for ($date = $period_start; $date < $period_end; $date->modify('+1 month')) {
                $bill = Bill::create([
                    'account_id' =>  request('account'),
                    'user_id' => $user->id,
                    'bill_amount' => request('price'),
                    'bill_remainder' => request('price'),
                    'payment_status' =>  1,
                    'due_date' => $date,
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
        $period_start = new DateTime(request('period_start'));
        $period_end = new DateTime(request('period_end'));

        $period_end->modify('+1 month');

        foreach ($users as $user) {
            for ($date = $period_start; $date < $period_end; $date->modify('+1 month')) {
                $bill = Bill::create([
                    'account_id' => Account::where('account_name', '=', 'Madin')->first()->id,
                    'user_id' => $user->id,
                    'bill_amount' => request('madin'),
                    'bill_remainder' => request('madin'),
                    'payment_status' =>  1,
                    'due_date' => $date,
                ]);
            }
        }

        foreach ($users as $user) {
            for ($date = $period_start; $date < $period_end; $date->modify('+1 month')) {

                $bill = Bill::create([
                    'account_id' =>  Account::where('account_name', '=', 'Syahriah')->first()->id,
                    'user_id' => $user->id,
                    'bill_amount' => request('syah'),
                    'bill_remainder' => request('syah'),
                    'payment_status' =>  1,
                    'due_date' => $date,
                ]);
            }
        }

        foreach ($users as $user) {
            for ($date = $period_start; $date < $period_end; $date->modify('+1 month')) {
                $bill = Bill::create([
                    'account_id' =>  Account::where('account_name', '=', 'Wifi')->first()->id,
                    'user_id' => $user->id,
                    'bill_amount' => request('wifi'),
                    'bill_remainder' => request('wifi'),
                    'payment_status' =>  1,
                    'due_date' => $date,
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

        $bill = Bill::create([
            'account_id' => request('account'),
            'user_id' => request('user'),
            'bill_amount' => request('price'),
            'bill_remainder' => request('price'),
            'payment_status' =>  1,
            'due_date' => request('period'),
        ]);

        return $bill;
    }

    public function store_singleRange()
    {
        // request()->validate([
        //     'name' => 'required',
        //     'email' => 'required|unique:dispens,email',
        //     'password' => 'required|min:8',
        // ]);
        $period_start = new DateTime(request('period_start'));
        $period_end = new DateTime(request('period_end'));

        $period_end->modify('+1 month');
        for ($date = $period_start; $date < $period_end; $date->modify('+1 month')) {
            $bill = Bill::create([
                'account_id' => request('account'),
                'user_id' => request('user'),
                'bill_amount' => request('price'),
                'bill_remainder' => request('price'),
                'payment_status' =>  1,
                'due_date' => $date,
            ]);
        }
        return $bill;
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
