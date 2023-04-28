<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Enums\AccountType;
use App\Http\Controllers\Controller;

class AccountController extends Controller
{
    public function index()
    {
        $acc =  Account::latest()->orderBy('id', 'asc')->get();

        $data = $acc->map(function ($item, $key) {
            $acc_type = AccountType::from($item->account_type);
            $item->account_type = $acc_type->name();
            return $item;
        });
        return $data;
    }

    public function allExcept()
    {
        $id = request('except');
        $acc =  Account::where('id','!=',$id)->orderBy('id', 'asc')->get();

        $data = $acc->map(function ($item, $key) {
            $acc_type = AccountType::from($item->account_type);
            $item->account_type = $acc_type->name();
            return $item;
        });
        return $data;
    }
}
