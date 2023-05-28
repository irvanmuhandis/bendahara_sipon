<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\BigBook;
use App\Models\Pay;
use App\Models\Bill;
use App\Models\Debt;
use App\Models\User;
use App\Models\Dispen;
use App\Models\Expense;
use App\Models\Group;
use App\Models\GroupHistory;
use App\Models\Periodic;
use App\Models\statusColor;
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
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'group_name' => 'Asatidz',
                'group_desc' => 'Syahriah putra, wifi',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'group_name' => 'Beasiswa',
                'group_desc' => 'Syahriah putra, wifi',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'group_name' => 'Putri Makwo',
                'group_desc' => 'Syahriah putri makwo, wifi, madin',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'group_name' => 'Putri Ndalem',
                'group_desc' => 'Syahriah putri ndalem, wifi, madin',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'group_name' => 'Putri Pondok Baru',
                'group_desc' => 'Syahriah putri ponbar, wifi, madin',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'group_name' => 'Asatidz Putri Makwo',
                'group_desc' => 'Syahriah putri makwo, wifi',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'group_name' => 'Asatidz Putri Ndalem',
                'group_desc' => 'Syahriah putri ndalem, wifi',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'group_name' => 'Asatidz Putri Pondok Baru',
                'group_desc' => 'Syahriah putri ponbar, wifi', 'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'group_name' => 'Beasiswa Putri',
                'group_desc' => 'Syahriah putri, wifi',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'group_name' => 'Putra Anti Dunia',
                'group_desc' => 'Syahriah putra, madin',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'group_name' => 'Putri Anti dunia',
                'group_desc' => 'Syahriah putri, wifi',
                'created_at' => now(),
                'updated_at' => now()
            ],

        ];
        DB::table('accounts')->insert([
            [
                'account_name' => 'Utang',
                'account_type' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'account_name' => 'Syahriah',
                'account_type' => 2,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'account_name' => 'Wifi',
                'account_type' => 2,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'account_name' => 'Madin',
                'account_type' => 2,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'account_name' => 'Kebutuhan',
                'account_type' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
        User::factory(25)->create();
        DB::table('groups')->insert($grup);
        Bill::factory(200)->create();
        Pay::factory(40)->create();
        Debt::factory(400)->create();
        Dispen::factory(20)->create();
    }
}
