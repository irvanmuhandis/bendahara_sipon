<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Http;
use Illuminate\Contracts\Encryption\DecryptException;

class CheckToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (Cookie::get('sipon_session') !== null) {

            if ($request->data) {


                $data = Crypt::decryptString($request->data);
                $data_array = json_decode($data);
                $token = $data_array->token;

                Cookie::queue('sipon_session', $token, 10080);

                // setcookie('sipon_session', $token, 10080);


                //https://psb.kyaigalangsewu.net/
                return redirect('/admin/dashboard');
            }
            $token = Cookie::get('sipon_session');
// dd($token);
            $response = Http::withHeaders([
                'Accept' => 'aplication/json',
                'Authorization' => 'Bearer ' . json_decode($token)->token,
            ])->get('http://127.0.0.1:8000/api/token');

            dd($response);

            if ($response->status() != 200) {

                setcookie('sipon_session', '', time() - 1);

                //https://sipon.kyaigalangsewu.net/logout
                return redirect('/logout');
            }

            return $next($request);
        } else {
            if ($request->data) {
                $data = Crypt::decryptString($request->data);
                $data_array = json_decode($data);
                $token = $data_array->token;

                Cookie::queue('sipon_session', $token, 10080);


                //  setcookie('sipon_session', $token, 10080);

                //  dd($token);
                //https://psb.kyaigalangsewu.net/
                return redirect('/admin/dashboard');
            } else {
                //https://sipon.kyaigalangsewu.net/
                return redirect('/login');
            }
        }
    }
}
