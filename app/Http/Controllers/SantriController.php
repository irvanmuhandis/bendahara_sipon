<?php

namespace App\Http\Controllers;


use Exception;
use App\Models\Bill;
use App\Models\Debt;
use App\Models\Santri;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cookie;

class SantriController extends Controller
{
    var $billTable = "acc_bills";
    var $debtTable = "acc_debts";

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
        $db = Bill::where("{$this->billTable}.nis", '=', $id)
            ->where("{$this->billTable}.payment_status", '<', 3)
            ->whereHas('santri', function ($query) {
                $query->where('option', 1);
            })
            ->with(['santri', 'account'])
            ->orderBy("{$this->billTable}.month", 'desc')
            ->latest()->paginate(9);


        return $db;
    }

    public function debt($id)
    {
        $db = Debt::where("{$this->debtTable}.nis", '=', $id)
            ->where("{$this->debtTable}.payment_status", '<', 3)
            ->whereHas('santri', function ($query) {
                $query->where('option', 1);
            })
            ->with(['santri', 'account'])
            ->orderBy("{$this->debtTable}.created_at", 'desc')
            ->latest()->paginate(7);

        return response()->json($db);
    }




    public function list()
    {
        // return Santri::select('nis','fullname','nickname')->where('option',2)->where('status',1)->orderBy('nis', 'desc')
        //     ->get();

        $data = Http::withHeaders([
            'Authorization' => 'Bearer ' . json_decode(Cookie::get('sipon_session'))->token,
            'Accept' => 'application/json'
        ])->get('https://sipon.kyaigalangsewu.net/api/v1/santri/pa');

        return $data['data'];
    }
}
