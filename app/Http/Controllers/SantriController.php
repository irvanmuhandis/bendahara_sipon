<?php

namespace App\Http\Controllers;


use Exception;
use App\Models\Bill;
use App\Models\Debt;
use App\Models\Santri;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SantriController extends Controller
{
    // public function index()
    // {
    //     $users = Santri::latest()->paginate(20);
    //     return $users;
    // }

    public function group($id)
    {
        try {
            $group_id = $id;

            if ($group_id) {
                // If group_id is specified, filter accounts by group_id
                $santri = Santri::where('id', '=', $group_id)->with('group')->get();
                return $santri;
            }
        } catch (Exception $e) {
            logger($e);
            return response()->json(['message' => 'Internal server error'], 500);
        }
    }


    public function bill($id)
    {
        $db = Bill::
            where('acc_bills.nis', '=', $id)
            ->where('acc_bills.payment_status', '<', 3)
            ->with(['santri', 'account'])
            ->orderBy('acc_bills.due_date', 'desc')
            ->latest()->paginate(9);


        return $db;
    }

    public function debt($id)
    {
        $db = Debt::
          where('acc_debts.nis', '=', $id)
            ->where('acc_debts.payment_status', '<', 3)
            ->with(['santri', 'account'])
            ->orderBy('acc_debts.created_at', 'desc')
            ->latest()->paginate(7);

        return response()->json($db);
    }

    public function list()
    {
        return Santri::orderBy('fullname', 'asc')
            ->get();
    }
}
