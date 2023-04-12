<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PayController;
use App\Http\Controllers\BillController;
use App\Http\Controllers\DebtsController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\TransController;
use App\Http\Controllers\DispenController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\BigBookController;
use App\Http\Controllers\PeriodicController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\Attr\DebtStatusController;
use App\Http\Controllers\Status\PayStatusController;
use App\Http\Controllers\Admin\AppointStatusController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});


Route::get('/api/debt_status', [DebtStatusController::class, 'getStatusWithCount']);
Route::get('/api/debt/search',[DebtsController::class,'search']);
Route::delete('/api/debt',[DebtsController::class,'bulkDelete']);

Route::get('/api/pay/search',[PayController::class,'search']);
Route::get('/api/paydebt',[PayController::class,'indexDebt']);
Route::get('/api/paybill',[PayController::class,'indexBill']);
Route::delete('/api/pay',[PayController::class,'bulkDelete']);
Route::get('/api/pay/status',[PayStatusController::class,'status']);

Route::get('/api/bill/search',[BillController::class,'search']);
Route::delete('/api/bill',[BillController::class,'bulkDelete']);
Route::post('/api/bill_s',[BillController::class,'store_s']);

Route::get('/api/group/search',[GroupController::class,'search']);
Route::delete('/api/group',[GroupController::class,'bulkDelete']);

Route::get('/api/debt/search',[DebtsController::class,'search']);
Route::delete('/api/debt',[DebtsController::class,'bulkDelete']);

Route::get('/api/appoint_status', [AppointStatusController::class, 'getStatusWithCount']);
Route::get('/api/appointments', [AppointmentController::class, 'index']);
Route::post('/api/appointments/create', [AppointmentController::class, 'store']);

Route::get('/api/userlist', [UserController::class, 'list']);
Route::get('/api/user/bill/{id}', [UserController::class, 'bill']);

Route::get('/api/users/search', [UserController::class, 'search']);
Route::patch('/api/users/{user}/change-role', [UserController::class, 'changeRole']);
Route::delete('/api/users', [UserController::class, 'bulkDelete']);

Route::get('/api/dispens/search',[DispenController::class,'search']);
Route::delete('/api/dispens',[DispenController::class,'bulkDelete']);

Route::get('/api/periodiclist', [PeriodicController::class, 'list']);


Route::resource('/api/account',AccountController::class)
->only(['index','store','update','destroy']);
Route::resource('/api/trans',TransController::class)
->only(['index','store','update','destroy']);
Route::resource('/api/bill',BillController::class)
->only(['index','store','update','destroy']);
Route::resource('/api/bigbook',BigBookController::class)
->only(['index','store','update','destroy']);
Route::resource('/api/wallet',WalletController::class)
->only(['index','store','update','destroy']);
Route::resource('/api/dispens',DispenController::class)
->only(['index','store','update','destroy']);
Route::resource('/api/debt',DebtsController::class)
->only(['index','store','update','destroy']);;
Route::resource('/api/group',GroupController::class)
->only(['index','store','update','destroy']);
Route::resource('/api/bill',BillController::class)
->only(['index','store','update','destroy']);;
Route::resource('/api/pay',PayController::class)
->only(['index','store','show','update','destroy']);
Route::resource('/api/users',UserController::class)
->only(['index','store','update','destroy']);

Route::get('{view}', ApplicationController::class)->where('view', '(.*)');



