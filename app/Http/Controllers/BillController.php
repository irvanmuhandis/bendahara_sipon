<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\User;
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
            ->join('accounts', 'accounts.id', '=', 'bills.account_id')
            ->select('bills.updated_at', 'bills.created_at', 'bills.id', 'bills.payment_status', 'bills.bill_amount', 'bills.bill_remainder', 'bills.due_date', 'users.name', 'accounts.account_name')
            ->get();

        $bill = $bill->map(function ($item, $key) {
            $paymentStatus = PayStatus::from($item->payment_status);
            $item->payment_status = $paymentStatus->names();
            $item->status_color = $paymentStatus->color();
            return $item;
        });
        return $bill;
    }

    public function store()
    {
        // request()->validate([
        //     'name' => 'required',
        //     'email' => 'required|unique:dispens,email',
        //     'password' => 'required|min:8',
        // ]);

        $users = User::where('group_id', '=', request('group'))->get();

        foreach ($users as $user) {

            Bill::create([
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

    public function store_s()
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
