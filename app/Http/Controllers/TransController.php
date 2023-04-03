<?php

namespace App\Http\Controllers;

use App\Models\Trans;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class TransController extends Controller
{
    public function index()
    {
        $trans = DB::table('trans')
            ->join('accounts','accounts.id','=','trans.account_id')
            ->join('wallets', 'trans.wallet_id', '=', 'wallets.id')
            ->join('users', 'users.id', '=', 'trans.user_id')
            ->select('trans.id','users.name','users.name','trans.title','trans.in','trans.out','accounts.account_name','wallets.wallet_name','wallets.saldo','wallets.prev_saldo','trans.created_at')
            ->get();

        return $trans;
    }
}
