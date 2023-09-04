<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Http;

class ApplicationController extends Controller
{
    public function __invoke()
    {
        $nis = json_decode(Cookie::get('sipon_session'))->nis;
        $token = json_decode(Cookie::get('sipon_session'))->token;
        $response = Http::withHeaders([
                'Accept' => 'aplication/json',
                'Authorization' => 'Bearer ' . $token,
            ])->get('https://sipon.kyaigalangsewu.net/api/v1/santri/'.$nis);
        $santri=$response->json()['data'];
        return view('admin.layouts.app',['operator'=>$santri]);
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
        $cookie = json_decode(Cookie::get('sipon_session'))->user;
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
