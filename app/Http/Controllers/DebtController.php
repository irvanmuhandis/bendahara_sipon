<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Debt;
use App\Enums\PayStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;


class DebtController extends Controller
{

    function test(Request $request)
    {
        return $request->user();
    }

    public function index()
    {
        $debt = DB::table('debts')
            ->join('users', 'users.id', '=', 'debts.user_id')
            ->join('users as operator', 'operator.id', '=', 'debts.user_id')
            ->select('debts.created_at','debts.updated_at','debts.id','operator.name as operator', 'users.name as user', 'debts.amount as debt', 'debts.payment_status', 'debts.title', 'debts.remainder')
            ->orderBy('debts.id', 'desc')->paginate(20);

        return $debt;
    }

    public function store()
    {
        // request()->validate([
        //     'name' => 'required',
        //     'email' => 'required|unique:dispens,email',
        //     'password' => 'required|min:8',
        // ]);
        foreach (request('user') as $user) {
            $bill = Debt::create([
                'account_id' => 1,
                'user_id' => $user,
                'operator_id' => rand(1,3),
                'amount' => request('price'),
                'remainder' => request('price'),
                'payment_status' =>  1,
                'title' => request('title'),
            ]);
        }
        return request();
    }

    public function search()
    {
        $debtorName = request('query');
        $status = request('status');

        $debts = Debt::query()
            ->with('user:id,name')
            ->when($debtorName, function ($query) use ($debtorName) {
                return $query->whereHas('user', function ($q) use ($debtorName) {
                    $q->where('name', 'LIKE', "%$debtorName%");
                });
            })
            ->when($status, function ($query) use ($status) {
                return $query->where('payment_status', PayStatus::from($status));
            })
            ->paginate();

        return response()->json($debts);
    }




    public function bulkDelete()
    {
        Debt::whereIn('id', request('ids'))->delete();

        return response()->json(['message' => 'Debt deleted successfully!']);
    }

    public function destroy($debt)
    {
        Debt::where('id', request('id'))->delete();

        return response()->noContent();
    }

    public function update(Debt $debt)
    {
        //     request()->validate([
        //         'name' => 'required',
        //         'peiod' => 'required|unique:dispens,email,' . $debt->id,
        //         'password' => 'sometimes|min:8',
        //     ]);


        $debt->update([
            'user_id' => request('user_id'),
            'debt' => request('debt'),
            'remainder' => request('remainder'),
            'title' => request('title'),
            'status' => request('status')
        ]);

        return $debt;
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

        return response()->json(['message' => `Last `+request('type')+` rows deleted successfully`]);
    }


}
