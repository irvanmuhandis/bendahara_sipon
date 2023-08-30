<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cookie;

class ApplicationController extends Controller
{
    public function __invoke()
    {
        return view('admin.layouts.app');
    }


    public function logout(Request $request)
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        $request->session()->forget('sipon_session');
        Cookie::queue(Cookie::make('sipon_session', null, -1));
        return redirect('login');
    }

    function getOperator(Request $request)
    {
        $cookie = Cookie::get('sipon_session');;
        return $cookie;
    }

    // function setCookie() {
    //     $token = $request->user()->createToken('sipontoken')->plainTextToken;

    //     $data = [
    //         'nis' => $user->nis_santri,
    //         'token' => $token
    //     ];

    //     $token = json_encode({

    //     })
    //     Cookie::queue(Cookie::make('sipon_session', $token,10));
    // }

    function getToken()
    {
    }
}
