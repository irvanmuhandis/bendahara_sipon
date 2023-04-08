<?php

namespace App\Http\Controllers;

use App\Models\Bill;
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
            ->select('bills.id', 'bills.payment_status', 'bills.bill_amount', 'bills.bill_remainder', 'bills.due_date', 'users.name', 'accounts.account_name')
            ->get();

        $bill = $bill->map(function ($item, $key) {
            $paymentStatus = PayStatus::from($item->payment_status);
            $item->payment_status = $paymentStatus->names();
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
        $dispen = Bill::create([
            'name' => request('name'),
            'prev_saldo' => request('prev_saldo'),
            'saldo' => request('saldo'),
            'created_at' => request('created_at'),
            'updated_at' => request('updated_at')
        ]);
        return $dispen;
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
