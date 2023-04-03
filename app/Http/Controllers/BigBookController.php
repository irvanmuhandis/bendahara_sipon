<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class BigBookController extends Controller
{
    public function index(){
        $pay = DB::table('big_books')
            ->join('pays','pays.id','=','big_books.pay_id')
            ->join('wallets', 'pays.wallet_id', '=', 'wallets.id')
            ->join('accounts','accounts.id','=','big_books.account_id')
            ->select('pays.id','big_books.in','accounts.account_name','big_books.out','wallets.wallet_name','wallets.saldo','big_books.created_at')
            ->get();

        return $pay;
    }
}
