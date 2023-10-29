<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Enums\AccountType;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AccountController extends Controller
{
    public function index()
    {
        $searchQuery = request('query');
        return Account::latest()
            ->where('account_name', 'like', "%{$searchQuery}%")->paginate(10);
    }

    public function list()
    {
        $acc =  Account::latest()->orderBy('id', 'asc')->get();

        $data = $acc->map(function ($item, $key) {
            $acc_type = AccountType::from($item->account_type);
            $item->account_type = $acc_type->name();
            return $item;
        });
        return $data;
    }

    public function only(Request $request)
    {
        $queryParams = $request->query();
        $type = $queryParams['type'];

        $acc =  Account::where('account_type', '=', $type)->orderBy('id', 'asc')->get();
        return $acc;
    }


    public function bulkDelete()
    {
        $log = Account::whereIn('id', request('ids'))->delete();
        return response($log);
    }


    public function periodic()
    {
        $data = Account::where('account_type', '=', '2')->get();
        $size =  $data->count();

        // return response()->json([
        //     'data'=>$data,
        //     'size'=>$size
        // ]);
        return $data;
    }

    public function store()
    {
        return Account::create([
            'account_name' => request('name'),
            'account_type' => request('type'),
            'deletable' => 1
        ]);
    }
    public function update(Account $account)
    {
         $account->update([
            'account_name' => request('name'),
            'account_type' => request('type'),
            'deletable' => request('delete')==true?1:0
        ]);
        return $account;
    }

    public function changeType(Account $account)
    {
        $account->update([
            'account_type' => request('account_type'),
        ]);

        return response()->json(['success' => true]);
    }
}
