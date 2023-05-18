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
        Schema::create('bills', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('bill_amount');
            $table->bigInteger('bill_remainder');
            $table->string('due_date');
            $table->foreignId('user_id');
            $table->foreignId('account_id');
            $table->bigInteger('operator_id')->unsigned();
            $table->foreign('operator_id')->references('id')->on('users');
            $table->integer('payment_status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bills');
    }
};
