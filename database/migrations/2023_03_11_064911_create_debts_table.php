<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('acc_debts', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('title');
            $table->bigInteger('amount');
            $table->bigInteger('remainder');
            $table->integer('account_id');
            $table->string('nis');
            $table->bigInteger('wallet_id')->unsigned();
            $table->foreign('wallet_id')
                ->references('id')
                ->on('acc_wallets')
                ->onDelete('cascade');
            $table->foreign('nis')->references('nis')->on('santris');
            $table->bigInteger('operator_id')->unsigned();
            $table->foreign('operator_id')->references('id')->on('users');
            $table->tinyInteger('payment_status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('accidentals');
    }
};
