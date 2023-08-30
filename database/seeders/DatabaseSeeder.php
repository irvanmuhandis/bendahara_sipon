<?php

namespace Database\Seeders;

use Faker\Factory;
use App\Models\Pay;
use App\Models\Bill;
use App\Models\Debt;
use App\Models\User;
use App\Models\Group;
use App\Models\Trans;
use App\Models\Dispen;
use App\Models\Ledger;
use App\Models\Santri;
use App\Models\Wallet;
use App\Models\Account;
use App\Models\BigBook;
use App\Models\Expense;
use App\Models\Periodic;
use App\Models\statusColor;
use App\Models\GroupHistory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

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
                'wallet_type' => 1,
                'debit' => 20000,
                'credit' => 0,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'wallet_name' => 'Cash',
                'wallet_type' => 2,
                'debit' => 100000,
                'credit' => 0,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'wallet_name' => 'BNI Muhan',
                'wallet_type' => 3,
                'debit' => 60000,
                'credit' => 0,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];
        DB::table('acc_wallets')->insert($walet);
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
        DB::table('acc_accounts')->insert([
            [
                'account_name' => 'Utang',
                'account_type' => 1,
                'deletable' => 0,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'account_name' => 'Syahriah',
                'account_type' => 2,
                'deletable' => 0,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'account_name' => 'Wifi',
                'account_type' => 2,
                'deletable' => 0,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'account_name' => 'Madin',
                'account_type' => 2,
                'deletable' => 0,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'account_name' => 'Kebutuhan',
                'account_type' => 3,
                'deletable' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'account_name' => 'Iuran Santri Baru',
                'account_type' => 1,
                'deletable' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'account_name' => 'Perkap',
                'account_type' => 3,
                'deletable' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
        DB::table('acc_groups')->insert($grup);


        Santri::create([
            'nis' => '1000223000',
            'fullname' => 'Santika Hamilah',
            'program' => 'Kitab',
            'option' => '2',
            'status' => '1',
            'sbc' => '1',
        ]);
        // User::create([
        //     'nis_santri' => '1000223000',
        //     'password' => bcrypt('1234')
        // ]);

        Santri::create([
            'nis' => '1000223001',
            'fullname' => 'Andre Hamilah',
            'program' => 'Kitab',
            'option' => '2',
            'status' => '1',
            'sbc' => '1',
        ]);

        // User::create([
        //     'nis_santri' => '1000223001',
        //     'password' => bcrypt('1234')
        // ]);

        Santri::create([
            'nis' => '1000223002',
            'fullname' => 'Iskandar Hamilah',
            'program' => 'Kitab',
            'option' => '2',
            'status' => '1',
            'sbc' => '1',
        ]);

        // User::create([
        //     'nis_santri' => '1000223002',
        //     'password' => bcrypt('1234')
        // ]);
        // Bill::factory(2)->create();
        // Debt::factory(5)->create();
        // Dispen::factory(5)->create();


        function wallet($debit, $credit)
        {
            return Wallet::create([
                'wallet_name' => 'BRI Muhan',
                'wallet_type' => 1,
                'debit' => $debit,
                'credit' => $credit,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
        $faker = Factory::create();
        // for ($i = 0; $i < 50; $i++) {
        //     $end = rand(1000, 9999);
        //     $remain = rand(0, $end);
        //     $status = 1;
        //     switch ($end) {
        //         case $remain:
        //             $status = 1;
        //             break;
        //         case 0:
        //             $status = 3;
        //             break;
        //         default:
        //             $status = 2;
        //             break;
        //     }

        //     $santri = Santri::inRandomOrder()->first();



        //     $bill = Bill::create([
        //         'amount' => $end,
        //         'remainder' => $end - $remain,
        //         'due_date' => fake()->dateTime('+ 1 month')->format('Y-m'),
        //         'nis' => $santri->nis,
        //         'account_id' => rand(2, 5),
        //         'operator_id' => rand(1, 3),
        //         'payment_status' => 1
        //     ]);
        //     $trans = Trans::create([
        //         'wallet_id' => wallet($remain, $end)->id,
        //         'desc' => fake()->text(),
        //         'operator_id' => User::first()->id,
        //         'account_id' => $faker->randomElement([5,6]),
        //         'debit' =>  $remain,
        //         'credit' => $end,
        //     ]);

        //     $debt =  Debt::create([
        //         'account_id' => Account::where('account_name', '=', 'Utang')->first()->id,
        //         'payment_status' => $status,
        //         'title' => 'Utang',
        //         'wallet_id' => wallet(0, $end)->id,
        //         'operator_id' => rand(1, 3),
        //         'amount' => $end,
        //         'nis' => $santri->nis,
        //         'remainder' => $remain,
        //     ]);



        //     $pay = Pay::create([
        //         'payment' => $remain,
        //         'nis' => $santri->nis,
        //         'operator_id' => rand(1, 3),
        //         'wallet_id' => wallet($remain, 0)->id,
        //         'payable_id' => $bill->id,
        //         'payable_type' => Bill::class
        //     ]);

        //     $type = [[
        //         'name' => Pay::class,
        //         'value' => $pay,
        //         'time' => $faker->randomElement([
        //             now(), now()->addDay(1), now()->addDay(2), now()->addDay(5), now()->addDay(7), now()->addDay(11), now()->addDay(15), now()->addDay(20), now()->addDay(25), now()->addDay(28), now()->addDay(29)
        //         ])
        //     ], [
        //         'name' => Trans::class,
        //         'value' => $trans,
        //         'time' => $faker->randomElement([
        //             now(), now()->addDay(1), now()->addDay(2), now()->addDay(5), now()->addDay(7), now()->addDay(11), now()->addDay(15), now()->addDay(20), now()->addDay(25), now()->addDay(28), now()->addDay(29)
        //         ])
        //     ], [
        //         'name' => Debt::class,
        //         'value' => $debt,
        //         'time' => $faker->randomElement([
        //             now(), now()->addDay(1), now()->addDay(2), now()->addDay(5), now()->addDay(7), now()->addDay(11), now()->addDay(15), now()->addDay(20), now()->addDay(25), now()->addDay(28), now()->addDay(29)
        //         ])
        //     ]];
        //     $ledger = $faker->randomElement($type);

        //     Ledger::create([
        //         'ledgerable_type' => $ledger['name'],
        //         'ledgerable_id' => $ledger['value']->id,
        //         'created_at' => $ledger['time']
        //     ]);
        // }
    }
}
