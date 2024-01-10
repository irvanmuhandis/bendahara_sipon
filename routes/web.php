<?php

use App\Models\Debt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Cookie;
use App\Http\Controllers\PayController;
use App\Http\Controllers\BillController;
use App\Http\Controllers\DebtController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\TransController;
use App\Http\Controllers\DispenController;
use App\Http\Controllers\LedgerController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\Status\PayStatusController;
use App\Http\Controllers\Admin\AppointStatusController;
use App\Http\Controllers\MasterController;
use App\Http\Controllers\PdfController;
use App\Http\Controllers\SantriController;
use App\Models\Ledger;
use App\Models\Santri;
use App\Models\Trans;

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

// Route::get('/', function () {
//     return view('welcome');
// });


Route::middleware('sipon')->group(function () {

    Route::get('/api/debt/search', [DebtController::class, 'search']);
    Route::delete('/api/debt', [DebtController::class, 'bulkDelete']);
    Route::delete('/api/debt/delHour', [DebtController::class, 'deleteHour']);
    Route::delete('/api/debt/delDay', [DebtController::class, 'deleteDay']);
    Route::delete('/api/debt/del', [DebtController::class, 'deleteMany']);

    Route::get('/api/pay/search', [PayController::class, 'search']);
    Route::get('/api/paydebt', [PayController::class, 'indexDebt']);
    Route::get('/api/paybill', [PayController::class, 'indexBill']);
    Route::delete('/api/pay', [PayController::class, 'bulkDelete']);
    Route::post('/api/pay/bill', [PayController::class, 'store_bill']);
    Route::post('/api/pay/debt', [PayController::class, 'store_debt']);
    Route::get('/api/pay/status', [PayStatusController::class, 'status']);

    Route::delete('/api/bill', [BillController::class, 'bulkDelete']);
    Route::delete('/api/bill/delHour', [BillController::class, 'deleteHour']);
    Route::delete('/api/bill/delDay', [BillController::class, 'deleteDay']);
    Route::delete('/api/bill/del', [BillController::class, 'deleteMany']);

    Route::post('/api/bill-single', [BillController::class, 'store_single']);
    Route::post('/api/bill-singlerange', [BillController::class, 'store_singleRange']);
    Route::post('/api/bill-nonperiod', [BillController::class, 'store_nonperiod']);
    Route::get('/api/bill/santri', [BillController::class, 'santri']);

    Route::post('/api/bill-group', [BillController::class, 'store_group']);
    Route::post('/api/bill-grouprange', [BillController::class, 'store_groupRange']);
    Route::post('/api/bill-group-mult', [BillController::class, 'store_groupMult']);
    Route::post('/api/bill-grouprange-mult', [BillController::class, 'store_groupRangeMult']);

    Route::get('/api/wallet/list', [WalletController::class, 'list']);
    Route::post('/api/wallet/delete', [WalletController::class, 'delType']);

    Route::get('/api/group/search', [GroupController::class, 'search']);
    Route::delete('/api/group', [GroupController::class, 'bulkDelete']);
    Route::get('/api/group/list', [GroupController::class, 'list']);
    Route::get('/api/group/santri', [GroupController::class, 'santri']);
    Route::get('/api/group/santri/form', [GroupController::class, 'form']);
    Route::get('/api/group/user/search', [GroupController::class, 'santri_search']);
    Route::put('/api/group/link', [GroupController::class, 'link']);

    Route::get('/api/debt/search', [DebtController::class, 'search']);
    Route::get('/api/debt/santri', [DebtController::class, 'santri']);
    Route::delete('/api/debt', [DebtController::class, 'bulkDelete']);

    Route::get('/api/santrilist', [SantriController::class, 'list']);
    Route::get('/api/santri/group/{id}', [SantriController::class, 'group']);
    Route::get('/api/santri/bill/{id}', [SantriController::class, 'bill']);
    Route::get('/api/santri/debt/{id}', [SantriController::class, 'debt']);


    Route::get('/api/dispens/search', [DispenController::class, 'search']);
    Route::delete('/api/dispens', [DispenController::class, 'bulkDelete']);

    Route::get('/api/ledger', [MasterController::class, 'index']);
    Route::get('/api/ledger/circul', [MasterController::class, 'circulation']);
    Route::get('/api/ledger/{id}', [MasterController::class, 'show']);
    Route::get('/api/ledger/billing/search', [MasterController::class, 'billing']);
    Route::get('/api/inout', [MasterController::class, 'inout']);
    Route::get('/api/accsum', [MasterController::class, 'accountSum']);
    Route::get('/api/stat', [MasterController::class, 'statistic']);

    Route::get('/api/pdf/santri', [MasterController::class, 'billingPdf']);

    Route::get('/api/account/only', [AccountController::class, 'only']);
    Route::get('/api/account/list', [AccountController::class, 'list']);
    Route::get('/api/account/period', [AccountController::class, 'periodic']);
    Route::delete('/api/account', [AccountController::class, 'bulkDelete']);

    Route::delete('/api/trans', [TransController::class, 'bulkDelete']);


    Route::get('/logout', [ApplicationController::class, 'logout'])->name('logout');
    Route::get('/token', [ApplicationController::class, 'getToken']);
    Route::get('/api/operator', [ApplicationController::class, 'getOperator']);

    Route::resource('/api/account', AccountController::class)
        ->only(['index', 'store', 'update']);
    Route::resource('/api/trans', TransController::class)
        ->only(['index', 'store', 'update']);
    Route::resource('/api/bill', BillController::class)
        ->only(['index', 'update', 'destroy']);
    Route::resource('/api/wallet', WalletController::class)
        ->only(['index', 'store', 'update', 'destroy']);
    Route::resource('/api/dispens', DispenController::class)
        ->only(['index', 'store', 'update', 'destroy']);
    Route::resource('/api/debt', DebtController::class)
        ->only(['index', 'store', 'update', 'destroy']);;
    Route::resource('/api/group', GroupController::class)
        ->only(['index', 'store', 'update', 'destroy']);
    Route::resource('/api/pay', PayController::class)
        ->only(['index', 'store', 'show', 'update', 'destroy']);

    Route::get('/{view}', ApplicationController::class)->where('view', '(.*)');
});
