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
        Schema::create('acc_pays', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('payment');
            $table->foreignId('wallet_id');
            $table->timestamps();
            $table->string('nis');
            $table->foreign('nis')->references('nis')->on('santris');
             $table->bigInteger('operator_id')->unsigned();
            $table->foreign('operator_id')->references('id')->on('users');
            $table->integer("payable_id")->nullable();
            $table->string("payable_type")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pays');
    }
};
