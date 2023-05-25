<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PayController;
use App\Http\Controllers\BillController;
use App\Http\Controllers\DebtController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\TransController;
use App\Http\Controllers\DispenController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\BigBookController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\AppointmentController;
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


Route::get('/api/debt/search',[DebtController::class,'search']);
Route::delete('/api/debt',[DebtController::class,'bulkDelete']);
Route::delete('/api/debt/delHour',[DebtController::class,'deleteHour']);
Route::delete('/api/debt/delDay',[DebtController::class,'deleteDay']);
Route::delete('/api/debt/del',[DebtController::class,'deleteMany']);

Route::get('/api/pay/search',[PayController::class,'search']);
Route::get('/api/paydebt',[PayController::class,'indexDebt']);
Route::get('/api/paybill',[PayController::class,'indexBill']);
Route::delete('/api/pay',[PayController::class,'bulkDelete']);
Route::post('/api/pay/bill',[PayController::class,'store_bill']);
Route::post('/api/pay/debt',[PayController::class,'store_debt']);
Route::get('/api/pay/status',[PayStatusController::class,'status']);

Route::get('/api/bill/search',[BillController::class,'search']);
Route::delete('/api/bill',[BillController::class,'bulkDelete']);
Route::delete('/api/bill/delHour',[BillController::class,'deleteHour']);
Route::delete('/api/bill/delDay',[BillController::class,'deleteDay']);
Route::delete('/api/bill/del',[BillController::class,'deleteMany']);

Route::post('/api/bill-single',[BillController::class,'store_single']);
Route::post('/api/bill-singlerange',[BillController::class,'store_singleRange']);
Route::post('/api/bill-group',[BillController::class,'store_group']);
Route::post('/api/bill-grouprange',[BillController::class,'store_groupRange']);
Route::post('/api/bill-group-mult',[BillController::class,'store_groupMult']);
Route::post('/api/bill-grouprange-mult',[BillController::class,'store_groupRangeMult']);

Route::get('/api/wallet/search',[WalletController::class,'search']);
Route::get('/api/wallet/list',[WalletController::class,'list']);

Route::get('/api/group/search',[GroupController::class,'search']);
Route::delete('/api/group',[GroupController::class,'bulkDelete']);
Route::get('/api/group/list', [GroupController::class, 'list']);
Route::get('/api/group/user', [GroupController::class, 'user']);
Route::get('/api/group/user/search', [GroupController::class, 'user_search']);
Route::put('/api/group/link', [GroupController::class, 'link']);

Route::get('/api/debt/search',[DebtController::class,'search']);
Route::delete('/api/debt',[DebtController::class,'bulkDelete']);

Route::get('/api/appoint_status', [AppointStatusController::class, 'getStatusWithCount']);
Route::get('/api/appointments', [AppointmentController::class, 'index']);
Route::post('/api/appointments/create', [AppointmentController::class, 'store']);

Route::get('/api/userlist', [UserController::class, 'list']);
Route::get('/api/user/group', [UserController::class, 'group']);
Route::get('/api/user/bill/{id}', [UserController::class, 'bill']);
Route::get('/api/user/debt/{id}', [UserController::class, 'debt']);
Route::get('/api/users/search', [UserController::class, 'search']);
Route::patch('/api/users/{user}/change-role', [UserController::class, 'changeRole']);
Route::delete('/api/users', [UserController::class, 'bulkDelete']);


Route::get('/api/dispens/search',[DispenController::class,'search']);
Route::delete('/api/dispens',[DispenController::class,'bulkDelete']);


Route::get('/api/account/except', [AccountController::class, 'allExcept']);
Route::get('/api/account/list', [AccountController::class, 'list']);

Route::resource('/api/account',AccountController::class)
->only(['index','store','update','destroy']);

Route::resource('/api/trans',TransController::class)
->only(['index','store','update','destroy']);
Route::resource('/api/bill',BillController::class)
->only(['index','update','destroy']);
Route::resource('/api/bigbook',BigBookController::class)
->only(['index','store','update','destroy']);
Route::resource('/api/wallet',WalletController::class)
->only(['index','store','update','destroy']);
Route::resource('/api/dispens',DispenController::class)
->only(['index','store','update','destroy']);
Route::resource('/api/debt',DebtController::class)
->only(['index','store','update','destroy']);;
Route::resource('/api/group',GroupController::class)
->only(['index','store','update','destroy']);
Route::resource('/api/bill',BillController::class)
->only(['index','store','update','destroy']);;
Route::resource('/api/pay',PayController::class)
->only(['index','store','show','update','destroy']);
// Route::resource('/api/users',UserController::class)
// ->only(['index','store','update','destroy']);

Route::get('{view}', ApplicationController::class)->where('view', '(.*)');



