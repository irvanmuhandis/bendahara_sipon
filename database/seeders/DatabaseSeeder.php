<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\BigBook;
use App\Models\Pay;
use App\Models\Bill;
use App\Models\User;
use App\Models\Dispen;
use App\Models\Expense;
use App\Models\Group;
use App\Models\GroupHistory;
use App\Models\Periodic;
use App\Models\Trans;
use App\Models\Wallet;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
        $walet = [
            [
                'wallet_name' => 'BRI Muhan',
                'saldo' => 10000,
                'wallet_type' => 1,
                'prev_saldo' => 2000
            ],
            [
                'wallet_name' => 'Cash',
                'wallet_type' => 2,
                'saldo' => 20000,
                'prev_saldo' => 3000
            ],
            [
                'wallet_name' => 'BNI Muhan',
                'saldo' => 105000,
                'wallet_type' => 3,
                'prev_saldo' => 20000
            ]
        ];
        DB::table('wallets')->insert($walet);
        $grup = [
            [
                'group_name' => 'Putra',
                'group_desc' => 'syahriah putra, wifi, madin',
            ],
            [
                'group_name' => 'Asatidz',
                'group_desc' => 'Syahriah putra, wifi',
            ],
            [
                'group_name' => 'Beasiswa',
                'group_desc' => 'Syahriah putra, wifi',
            ],
            [
                'group_name' => 'Putri Makwo',
                'group_desc' => 'Syahriah putri makwo, wifi, madin',
            ],
            [
                'group_name' => 'Putri Ndalem',
                'group_desc' => 'Syahriah putri ndalem, wifi, madin',
            ],
            [
                'group_name' => 'Putri Pondok Baru',
                'group_desc' => 'Syahriah putri ponbar, wifi, madin',
            ],
            [
                'group_name' => 'Asatidz Putri Makwo',
                'group_desc' => 'Syahriah putri makwo, wifi',
            ],
            [
                'group_name' => 'Asatidz Putri Ndalem',
                'group_desc' => 'Syahriah putri ndalem, wifi',
            ],
            [
                'group_name' => 'Asatidz Putri Pondok Baru',
                'group_desc' => 'Syahriah putri ponbar, wifi',
            ],
            [
                'group_name' => 'Beasiswa Putri',
                'group_desc' => 'Syahriah putri, wifi',
            ],
            [
                'group_name' => 'Putra Anti Dunia',
                'group_desc' => 'Syahriah putra, madin',
            ],
            [
                'group_name' => 'Putri Anti dunia',
                'group_desc' => 'Syahriah putri, wifi',
            ],

        ];
        DB::table('accounts')->insert([
            [
                'account_name' => 'Utang',
                'account_type' => 1
            ],
            [
                'account_name' => 'Syahriah',
                'account_type' => 2
            ],
            [
                'account_name' => 'Wifi',
                'account_type' => 2
            ],
            [
                'account_name' => 'Madin',
                'account_type' => 2
            ],
            [
                'account_name' => 'Kebutuhan',
                'account_type' => 1
            ]
        ]);
        User::factory(10)->create();
        DB::table('groups')->insert($grup);
        Bill::factory(20)->create();
        Pay::factory(40)->create();
        Dispen::factory(20)->create();
        Trans::factory(20)->create();
        $pays = Pay::all();
        foreach ($pays as $pay) {
            BigBook::create([
                'wallet_id' => $pay->wallet_id,
                'bookable_id' => $pay->id,
                'bookable_type'=>Pay::class,
                'out' => 0,
                'account_id' => $pay->payable->account->id,
                'in' => $pay->payment,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            $wallet = DB::table('wallets')->where('wallet_type', $pay->wallet->wallet_type)->latest('id')->first();
            Wallet::create(
                [
                    'wallet_type' => $wallet->wallet_type,
                    'wallet_name' => $wallet->wallet_name,
                    'prev_saldo' => $wallet->saldo,
                    'saldo' => $wallet->saldo + $pay->payment,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );
        }

        $exps = Trans::all();
        foreach ($exps as $exp) {
            BigBook::create([
                'wallet_id' => $exp->wallet_id,
                'bookable_id' => $exp->id,
                'bookable_type'=>Trans::class,
                'out' => $exp->out,
                'account_id' => $exp->account_id,
                'in' => $exp->in,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            $wallet = DB::table('wallets')->where('wallet_type', $exp->wallet->wallet_type)->latest('id')->first();

            Wallet::create(
                [
                    'wallet_type' => $wallet->wallet_type,
                    'wallet_name' => $wallet->wallet_name,
                    'prev_saldo' => $wallet->saldo,
                    'saldo' => $wallet->saldo + $pay->payment,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );
        }
    }
}
